<?php
namespace MeuSegredo\Models;

use MeuSegredo\Core\Model;
use MeuSegredo\Helpers\Pseudonym;
use MeuSegredo\Core\Security;

class Secret extends Model {
    protected $table = 'secrets';

    public function createSecret($title, $content, $category, $pseudonym = null, $city = null, $age = null) {
        $ip = Security::getClientIP();

        if (empty($pseudonym)) {
            $pseudonym = Pseudonym::generate();
        }

        $data = [
            'title' => Security::sanitize($title),
            'content' => Security::sanitize($content),
            'category' => Security::sanitize($category),
            'pseudonym' => Security::sanitize($pseudonym),
            'city' => $city ? Security::sanitize($city) : null,
            'age' => $age ? (int)$age : null,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        return $this->create($data);
    }

    public function getWithComments($id) {
        $secret = $this->findById($id);
        if (!$secret) return null;

        $this->incrementViews($id);

        $commentModel = new Comment();
        $comments = $commentModel->getBySecret($id);

        return ['secret' => $secret, 'comments' => $comments];
    }

    public function getTrending($hours = 24, $limit = 20) {
        $db = $this->getDB();
        $sql = "SELECT s.*,
                (s.likes + (SELECT COUNT(*) FROM comments WHERE secret_id = s.id AND status = 'active')) as score
                FROM {$this->table} s
                WHERE s.status = 'active'
                AND s.created_at > DATE_SUB(NOW(), INTERVAL :hours HOUR)
                ORDER BY score DESC, s.views DESC
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':hours', (int)$hours, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRanking($limit = 10) {
        $db = $this->getDB();
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY likes DESC LIMIT :limit";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByCategory($category, $limit = 20, $offset = 0) {
        return $this->findAll(
            ['category' => $category, 'status' => 'active'],
            $limit,
            $offset,
            'created_at DESC'
        );
    }

    public function getActive($limit = 20, $offset = 0) {
        return $this->findAll(
            ['status' => 'active'],
            $limit,
            $offset,
            'created_at DESC'
        );
    }

    public function incrementViews($id) {
        $db = $this->getDB();
        $ip = Security::getClientIP();

        $stmt = $db->prepare("SELECT id FROM views WHERE secret_id = :secret_id AND ip = :ip LIMIT 1");
        $stmt->execute([':secret_id' => $id, ':ip' => $ip]);

        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO views (secret_id, ip) VALUES (:secret_id, :ip)");
            $stmt->execute([':secret_id' => $id, ':ip' => $ip]);

            $stmt = $db->prepare("UPDATE secrets SET views = views + 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }

    public function handleLikeDislike($id, $type) {
        $db = $this->getDB();
        $ip = Security::getClientIP();

        $stmt = $db->prepare("SELECT id, type FROM likes WHERE secret_id = :secret_id AND ip = :ip LIMIT 1");
        $stmt->execute([':secret_id' => $id, ':ip' => $ip]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['type'] === $type) {
                return false;
            }
            $stmt = $db->prepare("UPDATE likes SET type = :type WHERE id = :id");
            $stmt->execute([':type' => $type, ':id' => $existing['id']]);

            if ($type === 'like') {
                $db->prepare("UPDATE secrets SET likes = likes + 1, dislikes = GREATEST(0, dislikes - 1) WHERE id = :id")->execute([':id' => $id]);
            } else {
                $db->prepare("UPDATE secrets SET dislikes = dislikes + 1, likes = GREATEST(0, likes - 1) WHERE id = :id")->execute([':id' => $id]);
            }
            return true;
        }

        $stmt = $db->prepare("INSERT INTO likes (secret_id, ip, type) VALUES (:secret_id, :ip, :type)");
        $stmt->execute([':secret_id' => $id, ':ip' => $ip, ':type' => $type]);

        if ($type === 'like') {
            $db->prepare("UPDATE secrets SET likes = likes + 1 WHERE id = :id")->execute([':id' => $id]);
        } else {
            $db->prepare("UPDATE secrets SET dislikes = dislikes + 1 WHERE id = :id")->execute([':id' => $id]);
        }
        return true;
    }
}

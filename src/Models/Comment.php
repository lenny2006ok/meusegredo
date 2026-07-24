<?php
namespace MeuSegredo\Models;

use MeuSegredo\Core\Model;
use MeuSegredo\Core\Security;
use MeuSegredo\Helpers\Pseudonym;

class Comment extends Model {
    protected $table = 'comments';

    public function getBySecret($secretId) {
        $db = $this->getDB();
        $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE secret_id = :secret_id AND status = 'active' ORDER BY created_at ASC");
        $stmt->execute([':secret_id' => $secretId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createComment($secretId, $parentId, $content, $pseudonym = null) {
        $ip = Security::getClientIP();
        if (empty($pseudonym)) {
            $pseudonym = Pseudonym::generate();
        }

        $data = [
            'secret_id' => (int)$secretId,
            'parent_id' => $parentId ? (int)$parentId : null,
            'content' => Security::sanitize($content),
            'pseudonym' => Security::sanitize($pseudonym),
            'ip' => $ip
        ];

        return $this->create($data);
    }

    public function handleLikeDislike($id, $type) {
        $db = $this->getDB();
        $ip = Security::getClientIP();

        $stmt = $db->prepare("SELECT id, type FROM likes WHERE comment_id = :comment_id AND ip = :ip LIMIT 1");
        $stmt->execute([':comment_id' => $id, ':ip' => $ip]);
        $existing = $stmt->fetch();

        if ($existing) {
            if ($existing['type'] === $type) {
                return false;
            }
            $stmt = $db->prepare("UPDATE likes SET type = :type WHERE id = :id");
            $stmt->execute([':type' => $type, ':id' => $existing['id']]);

            if ($type === 'like') {
                $db->prepare("UPDATE comments SET likes = likes + 1, dislikes = GREATEST(0, dislikes - 1) WHERE id = :id")->execute([':id' => $id]);
            } else {
                $db->prepare("UPDATE comments SET dislikes = dislikes + 1, likes = GREATEST(0, likes - 1) WHERE id = :id")->execute([':id' => $id]);
            }
            return true;
        }

        $stmt = $db->prepare("INSERT INTO likes (comment_id, ip, type) VALUES (:comment_id, :ip, :type)");
        $stmt->execute([':comment_id' => $id, ':ip' => $ip, ':type' => $type]);

        if ($type === 'like') {
            $db->prepare("UPDATE comments SET likes = likes + 1 WHERE id = :id")->execute([':id' => $id]);
        } else {
            $db->prepare("UPDATE comments SET dislikes = dislikes + 1 WHERE id = :id")->execute([':id' => $id]);
        }
        return true;
    }
}

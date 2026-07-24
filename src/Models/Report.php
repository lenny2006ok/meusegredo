<?php
namespace MeuSegredo\Models;

use MeuSegredo\Core\Model;
use MeuSegredo\Core\Security;

class Report extends Model {
    protected $table = 'reports';

    public function reportSecret($secretId, $reason, $details = '') {
        $ip = Security::getClientIP();

        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE secrets SET reported = TRUE WHERE id = :id");
        $stmt->execute([':id' => $secretId]);

        return $this->create([
            'secret_id' => (int)$secretId,
            'comment_id' => null,
            'reason' => Security::sanitize($reason),
            'details' => Security::sanitize($details),
            'ip' => $ip
        ]);
    }

    public function reportComment($commentId, $reason, $details = '') {
        $ip = Security::getClientIP();

        $db = $this->getDB();
        $stmt = $db->prepare("UPDATE comments SET reported = TRUE WHERE id = :id");
        $stmt->execute([':id' => $commentId]);

        return $this->create([
            'secret_id' => null,
            'comment_id' => (int)$commentId,
            'reason' => Security::sanitize($reason),
            'details' => Security::sanitize($details),
            'ip' => $ip
        ]);
    }

    public function getPendingReports() {
        $db = $this->getDB();
        $sql = "SELECT r.*,
                s.title as secret_title, s.content as secret_content,
                c.content as comment_content
                FROM {$this->table} r
                LEFT JOIN secrets s ON s.id = r.secret_id
                LEFT JOIN comments c ON c.id = r.comment_id
                WHERE r.status = 'pending'
                ORDER BY r.created_at DESC";
        return $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}

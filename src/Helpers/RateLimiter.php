<?php
namespace MeuSegredo\Helpers;

use MeuSegredo\Core\Model;
use MeuSegredo\Core\Security;

class RateLimiter extends Model {
    protected $table = 'rate_limits';

    public function canPerform($action, $maxPerDay) {
        $ip = Security::getClientIP();
        $db = $this->getDB();

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM rate_limits
                              WHERE ip = :ip AND action = :action
                              AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $stmt->execute([':ip' => $ip, ':action' => $action]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return ($result['total'] < $maxPerDay);
    }

    public function logAction($action) {
        $ip = Security::getClientIP();
        return $this->create([
            'ip' => $ip,
            'action' => $action
        ]);
    }
}

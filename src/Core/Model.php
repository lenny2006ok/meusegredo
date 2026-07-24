<?php
namespace MeuSegredo\Core;

class Model {
    protected static $db = null;
    protected $table;

    public function __construct() {
        if (self::$db === null) {
            $config = require __DIR__ . '/../../config/database.php';
            self::$db = $config['pdo'];
        }
    }

    public function getDB() {
        return self::$db;
    }

    public function findAll($conditions = [], $limit = 50, $offset = 0, $orderBy = 'created_at DESC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        $stmt = self::$db->prepare($sql);

        // Bind parameters explicitly to ensure types are correct for limit and offset
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = self::$db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = self::$db->prepare($sql);
        $stmt->execute($data);
        return self::$db->lastInsertId();
    }

    public function update($id, $data) {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = self::$db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = self::$db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }
}

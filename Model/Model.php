<?php
namespace Model;

use PDO;

class Model
{
    private $db;

    public function __construct()
    {
        $this->db = new PDO('sqlite:' . DATABASE_NAME . '.db');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS users_data (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            user_ip TEXT,
                            user_ip_proxy TEXT,
                            username TEXT, 
                            user_input INTEGER, 
                            fibonacci_num TEXT, 
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                        )" // fibonacci_num is a string due to intenger overflow

        );
    }

    public function create($user_ip, $user_ip_proxy, $username, $user_input, $fibonacci_num)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users_data (
                        user_ip, 
                        user_ip_proxy, 
                        username, 
                        user_input, 
                        fibonacci_num
                    ) VALUES (
                        :user_ip, 
                        :user_ip_proxy, 
                        :username, 
                        :user_input, 
                        :fibonacci_num

                    )"
        );
        $stmt->bindParam(':user_ip', $user_ip, PDO::PARAM_STR);
        $stmt->bindParam(':user_ip_proxy', $user_ip_proxy, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':user_input', $user_input, PDO::PARAM_INT);
        $stmt->bindParam(

            ':fibonacci_num',
            $fibonacci_num,
            PDO::PARAM_STR // string due to intenger overflow
        );
        $stmt->execute();
    }

    public function get($start, $length, $search, $order)
    {
        $query = "SELECT * FROM users_data";
        $params = [];

        if (!empty($search)) {
            $query .= " WHERE username LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        // Handle ordering
        if (!empty($order)) {
            $columnIndex = intval($order[0]['column']);
            $columns = ['id', 'username', 'user_input', 'fibonacci_num', 'created_at'];
            $columnName = $columns[$columnIndex] ?? 'created_at';
            $direction = $order[0]['dir'] === 'desc' ? 'DESC' : 'ASC';
            $query .= " ORDER BY $columnName $direction";
        }

        $query .= " LIMIT :start, :length";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':length', $length, PDO::PARAM_INT);

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalRecords()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users_data");
        return $stmt->fetchColumn();
    }

    public function getFilteredRecords($search)
    {
        $query = "SELECT COUNT(*) FROM users_data";
        if (!empty($search)) {
            $query .= " WHERE username LIKE :search";
        }
        $stmt = $this->db->prepare($query);

        if (!empty($search)) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }

        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

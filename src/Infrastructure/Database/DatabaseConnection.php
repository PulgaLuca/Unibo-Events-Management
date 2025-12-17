<?php
class DatabaseConnection {

    public static function getConnection(): mysqli {
        $conn = new mysqli('localhost', 'root', 'root','unibo_matchskills_db');

        if ($conn->connect_error) {
            throw new Exception('DB Connection failed');
        }

        return $conn;
    }
}
?>
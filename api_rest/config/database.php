<?php
// rest_api/config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "eaglesuite";
    private $username = "saliou";
    private $password = "tine"; 
    /* private $host = "109.234.167.76";
    private $db_name = "tisa9300_eaglesuite";
    private $username = "tisa9300_eaglesuite";
    private $password = "Eaglesuite@MDBT"; */
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo json_encode([
                "success" => false,
                "message" => "Erreur de connexion à la base de données: " . $exception->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}

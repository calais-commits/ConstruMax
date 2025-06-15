<?php
class Database {
    private $host = "localhost";
    private $db_name = "ferreteria_construmax";
    private $username = "root"; // Cambia por tu usuario
    private $password = "";     // Cambia por tu contraseña
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            die("Error al conectar con la base de datos. Por favor, intente más tarde.");
        }
        
        return $this->conn;
    }
}
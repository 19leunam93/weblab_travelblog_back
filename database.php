<?php

class Database{
 
    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "spuurch_weblab03";
    private $username = "spuurch_weblab";
    private $password = "password";
    public $sql;
 
    // get the database connection
    public function getConnection(){
 
        $this->sql = null;
 
        try{
            $this->sql = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->sql->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
        return $this->sql;
    }
}
?>

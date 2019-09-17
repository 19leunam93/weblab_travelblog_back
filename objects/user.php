<?php
class User{
 
    // database connection and table name
    private $db;
    private $table_name = "users";
 
    // object properties
    public $name;
    public $username;
    public $password;
 
    // constructor with $db as database connection
    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
    }

    function toArray($string) {
        $string = str_replace(array("[","]"), "", $string);
        $array = explode(",", $string);
        return $array;
    }

    function readUser($user_name) {
        // create query
        $query = "SELECT * FROM " . $this->table_name;
        $query .= " WHERE (username = ".$user_name.")";       
        // prepare query statement
        $stmt = $this->db->prepare($query);
        // count number of records
        $num = $stmt->execute();
        if ($num>0) {
            // fetch data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);
            $user = array(
                "name" => $name,
                "username" => $username,
                "password" => $password         
            );
        }
        return $user;
    }
}
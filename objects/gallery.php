<?php
class Gallery{
 
    // database connection and table name
    private $db;
    private $table_name = "galleries";
 
    // object properties
    public $id;
    public $title;
    public $images;
 
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

    function read($id) {
        // create query
        $query = "SELECT * FROM " . $this->table_name;
        $query .= " WHERE (id = ".$id.")";       
        // prepare query statement
        $stmt = $this->db->prepare($query);
        // count number of records
        $num = $stmt->execute();
        if ($num>0) {
            // fetch data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);
            $gallery = array(
                "id" => $id,
                "title" => $title,
                "images" => $this->toArray($images)         
            );
        }
        return $gallery;
    }
}
<?php
class Blog{
 
    // database connection and table name
    private $db;
    private $table_name = "blogs";
 
    // object properties
    public $id;
    public $title;
    public $destination;
    public $date;
    public $duration;
    public $description;
    public $img;
 
    // constructor with $db as database connection
    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
    }

    function read($id) {
        // create query
        $query = "SELECT
                    title, destination, date, duration, description, img
                  FROM "
                    . $this->table_name;
        if ($id != 0) {
        $query .= " WERE (id=".$id.")";
        }
        $query .= " ORDER BY
                    date DESC";
        // prepare query statement
        $stmt = $this->db->prepare($query);
        // count number of records
        $num = $stmt->execute();

        if ($num>0) {
            $blogs=array();
            $blogs["records"]=array();
            // fetch data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $item = array(
                    "title" => $title,
                    "destination" => $destination,
                    "date" => date("d.m.Y",strtotime($date)),
                    "duration" => $duration,
                    "description" => $description,
                    "img" => $img
                );
                array_push($blogs["records"], $item);

            }
            // set response code - 200 OK
            http_response_code(200);

            // send records in json format
            echo json_encode($blogs);
        }
    }

    function create($id) {
        http_response_code(201);
        echo('not yet implemented');
    }

    function write($id) {
        http_response_code(201);
        echo('not yet implemented');
    }

    function delete($id) {
        http_response_code(201);
        echo('not yet implemented');
    }
}
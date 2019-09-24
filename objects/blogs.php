<?php
class BlogList{
 
    // database connection and table name
    private $db;
    private $table_name = "blogs";
 
    // object properties
    public $id;
    public $alias;
    public $title;
    public $destination;
    public $date;
    public $duration;
    public $description;
    public $img_intro;
    public $img_titel;
 
    // constructor with $db as database connection
    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
    }

    function read($auth) {
        // create query
        $query = "SELECT * FROM " . $this->table_name;
        $query .= " ORDER BY date DESC";
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
                    "id" => $id,
                    "alias" => $alias,
                    "title" => $title,
                    "destination" => $destination,
                    "date" => date("d.m.Y",strtotime($date)),
                    "duration" => $duration,
                    "description" => $description,
                    "img_intro" => $img_intro,
                    "img_titel" => $img_titel
                );
                array_push($blogs["records"], $item);

            }
            $blogs["authorization"] = array(
                "logged_in_as_user" => $auth->username,
                "logged_in_as_usergroup" => $auth->usergroup,
                "requested_view" => $auth->requested_view,
                "authorized_current_action" => $auth->authorized,
                "authorized_actions" => $auth->authorized_to,
                "token_is_valid_for" => (string)$auth->token_time_to_expire
            );
            // set response code - 200 OK
            http_response_code(200);

            // send records in json format
            echo json_encode($blogs);
            return;
        }
    }
}
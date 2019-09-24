<?php
class Blog{
 
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

    function read($id, $selection, $auth) {
        
        if (is_numeric($id)) {           
            // create query
            if ($selection != 'all') {
                $query = "SELECT ". $selection ." FROM " . $this->table_name;
            } else {
                $query = "SELECT * FROM " . $this->table_name;
            }         
            if ($id != 0) {
            $query .= " WHERE (id=".$id.")";
            }
            $query .= " ORDER BY date DESC";
            // prepare query statement
            $stmt = $this->db->prepare($query);
            // count number of records
            $num = $stmt->execute();
            if ($num>0) {
                // fetch data
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
                    "img_titel" => $img_titel,
                );
                // grab corresponding posts
                $postObj = new Post();
                $item["posts"] = $postObj->read(0,'all',$auth, $id,false,false);

                $blogs["records"] = $item;
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
        http_response_code(204);
        echo('{"message": "no data found"}');
    }

    function create($id) {
        http_response_code(501);
        echo('{"message": "not yet implemented"}');
    }

    function write($id) {
        http_response_code(501);
        echo('{"message": "not yet implemented"}');
    }

    function delete($id) {
        http_response_code(501);
        echo('{"message": "not yet implemented"}');
    }
}
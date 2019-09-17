<?php
class Post{
 
    // database connection and table name
    private $db;
    private $table_name = "posts";
 
    // object properties
    public $id;
    public $user_id;
    public $title;
    public $date;
    public $txt_intro;
    public $txt_content;
    public $likes;
    public $img;
    public $galery_id;
 
    // constructor with $db as database connection
    public function __construct(){
        $database = new Database();
        $this->db = $database->getConnection();
    }

    function read($id, $selection, $auth, $blog_id=false, $user_id=false, $http=true) {
        // create query
        if ($selection == 'all') {
            $query = "SELECT * FROM " . $this->table_name;
        } else {
            $query = "SELECT ". $selection ." FROM " . $this->table_name;
        }
        
        if ($blog_id && !$user_id && $id == 0) {
            $query .= " WHERE (state = 1) and (blog_id = ".$blog_id.")";
        } elseif ($user_id && !$blog_id && $id == 0) {
            $query .= " WHERE (user_id = ".$user_id.")";
        } elseif ($id != 0) {
            $query .= " WHERE (id = ".$id.")";
        } elseif ($blog_id && $user_id) {
            $query .= " WHERE (blog_id = ".$blog_id.")";
        } 
        $query .= " ORDER BY date DESC";
        // prepare query statement
        $stmt = $this->db->prepare($query);
        // count number of records
        $num = $stmt->execute();
        if ($num>0) {
            $posts=array();
            $posts["records"]=array();
            // fetch data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $item = array(
                    "id" => $id,
                    "user_id" => $user_id,
                    "title" => $title,
                    "date" => date("d.m.Y",strtotime($date)),
                    "txt_intro" => $txt_intro,
                    "txt_content" => $txt_content,
                    "img" => $img,
                    "likes" => $likes          
                );
                // grab corresponding gallery
                if ($galery_id>0) {
                    $galleryObj = new Gallery();
                    $item["gallery"] = $galleryObj->read($galery_id);
                }                
                array_push($posts["records"], $item);

            }
            

            if (!$http) {
                return $posts;
            } else {
                $posts["authorization"] = array(
                "logged_in_as_user" => $auth->username,
                "logged_in_as_usergroup" => $auth->usergroup,
                "requested_view" => $auth->requested_view,
                "authorized_current_action" => $auth->authorized,
                "authorized_actions" => $auth->authorized_to
            );
                // set response code - 200 OK
                http_response_code(200);

                // send records in json format
                echo json_encode($posts);
                return;
            }         
            
        }
    }


    function write($id, $selection, $data) {
        if ($selection == 'likes') {
            // create query
            $query = "SELECT ". $selection ." FROM " . $this->table_name;
            $query .= " WHERE (id = ".$id.")";
            // prepare query statement
            $stmt = $this->db->prepare($query);
            // count number of records
            $num = $stmt->execute();
            if ($num>0) {
                $num_likes = $stmt->fetch(PDO::FETCH_ASSOC);
                extract($num_likes);
                $num_likes = $num_likes['likes'];
            }
            $data['likes'] = (integer)$num_likes + 1;
            // write data to DB
            $query = "UPDATE " . $this->table_name;
            $query .= " SET " . $selection . " = " . $data['likes'];
            $query .= " WHERE id = " . $id;
            $stmt = $this->db->prepare($query);
            // count number of records
            $stmt->execute();

            // read values from DB
            $likes = $this->read($id, $selection, false, false, false);

            // set response code - 200 OK
            http_response_code(200);

            // send records in json format
            echo json_encode($likes);
            return;
        }
        http_response_code(501);
        echo('not yet implemented');
    }

    function create($id) {
        http_response_code(501);
        echo('not yet implemented');
    }

    function delete($id) {
        http_response_code(501);
        echo('not yet implemented');
    }
}
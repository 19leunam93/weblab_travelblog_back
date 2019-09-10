<?php
class Post{
 
    // database connection and table name
    private $db;
    private $table_name = "posts";
 
    // object properties
    public $id;
    public $blogid;
    public $title;
    public $date;
    public $intro;
    public $content;
    public $img_intro;
    public $img_content;
    public $author;
    public $likes;
 
    // constructor with $db as database connection
    public function __construct($db){
        $database = new Database();
        $this->db = $database->getConnection();
    }

    function read($id) {
        http_response_code(201);
        echo('not yet implemented');
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
<?php
class Authentication{
 
    // object properties
    public $username = '';
    public $name = '';
    public $usergroup = 'public';
    public $session_token;
    public $authorized_to = array();
    public $authorized = false;

    private $jwt;
    private $jwt_key;
    private $jwt_payload;
    private $acl;

    function __construct($jwtObj, $jwt_key) { 
        $this->jwt = $jwtObj;
        $this->jwt_key = $jwt_key;
        $database = new Database();
        $this->db = $database->getConnection();

        $this->acl = array(
            'admin' => array('blogs' => array('visit','create','edit','delete'),
                             'login' => array('visit','create','edit','delete'),
                             'blog' => array('visit','create','edit','delete'),
                             'post' => array('visit'),
                             'edit' => array('visit','create','edit','delete'),
                             'editBlog' => array('visit','create','edit','delete'),
                             'editPost' => array('visit','create','edit','delete')
                         ),
            'blogger' => array('blogs' => array('visit'),
                             'login' => array('visit'),
                             'blog' => array('visit','create','edit'),
                             'post' => array('visit'),
                             'edit' => array('visit'),
                             'editBlog' => array('visit','create','edit'),
                             'editPost' => array('visit','create','edit')
                         ),
            'public' => array('blogs' => array('visit'),
                             'login' => array('visit'),
                             'blog' => array('visit'),
                             'post' => array('visit'),
                             'edit' => array(),
                             'editBlog' => array(),
                             'editPost' => array()
                         )
        );
    }

    function getPayload($sub, $time=false) {
        if ($time == false) {
            $time = time();
        }
        $payload = array(
            "iss" => "weblab.spuur.ch",
            "sub" => $sub,
            "iat" => $time
        );
        return $payload;
    }

    function readUser($user_name) {
        // create query
        $query = "SELECT * FROM users";
        $query .= " WHERE (username = '".$user_name."')";       
        // prepare query statement
        $stmt = $this->db->prepare($query);
        // count number of records
        $num = $stmt->execute();
        if ($num>0) {
            // fetch data
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($row)) {
                extract($row);
                $user = array(
                    "name" => $name,
                    "username" => $username,
                    "password" => $password,
                    "usergroup" => $usergroup  
                );
            } else {
                $user = array(
                    "name" => '',
                    "username" => $user_name,
                    "password" => '',
                    "usergroup" => 'public'  
                );
            }
            
        }        
        return $user;
    }



    function getToken($bn,$pw) {
        $user = $this->readUser($bn);
        if (!empty($bn) && !empty($pw) && !empty($user['password']) && $user['password'] == $pw) {
            $payload = $this->getPayload($user['username']);
            $token = $this->jwt->encode($payload, $this->jwt_key);
            $this->username = $user['username'];
            $this->name = $user['name'];
            return $token;
        } else {
            return false;
        }
    }

    function validateToken($token) {
        $decoded = $this->jwt->decode($token, $this->jwt_key, array('HS256'));
        $decoded_array = (array) $decoded;
        // username corresponds to username in token
        if ($decoded_array['sub'] == $this->username) {
            $t = time() - $decoded_array['iat'];
            // timestamp (iat) has to be less than 86'400s (24h)
            if ($t < 86400) {
                $validation_payload = $this->getPayload($this->username,$decoded_array['iat']);
                $validation_token = $this->jwt->encode($validation_payload, $this->jwt_key);
                // new calculated token corresponds to received token 
                if ($token === $validation_token) {
                    return true;
                } 
            }
            
        }
        return false;
    }

    function authoriseView($view, $action) {
        // if there is no username-cookie, set username to 'no_login'
        if (empty($this->username)) {
            $this->username = 'no_login';
        }

        $user = $this->readUser($this->username);
        
        // if token exist, do token authentication
        // username- and token-cookie have to be set
        if (!empty($this->username) && !empty($this->session_token)) {            
            // token has to be valid
            if ($this->validateToken($this->session_token)) {
                // check, if this user is permitted to do the requested action
                if (in_array($action,$this->acl[$user['username']][$view])) {
                    $this->authorized = true;
                    $this->authorized_to = $this->acl[$user['username']][$view];
                    $this->username = $user['username'];
                    $this->name = $user['name'];
                    $this->usergroup = $user['usergroup'];
                    return;
                }
            }
        } // check, if the action is a public action
        elseif (in_array($action,$this->acl['public'][$view])) {
            $this->authorized = true;
            $this->authorized_to = $this->acl['public'][$view];
            return;
        } else {
            $this->authorized_to = $this->acl['public'][$view];
            return;
        }
    }
}
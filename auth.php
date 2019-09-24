<?php 
class Authentication{
 
    // object properties
    public $username = '';
    public $name = '';
    public $usergroup = 'public';
    public $secure_token;
    public $token_time_to_expire = 'no_token';
    public $authorized_to = array();
    public $requested_view = '';
    public $authorized = false;

    private $jwt;
    private $jwt_key;
    private $jwt_payload;
    private $acl;
    private $token_validtime = 86400; // token is valid for 86'400s (24h);

    function __construct($jwtObj, $jwt_key) { 
        $this->jwt = $jwtObj;
        $this->jwt_key = $jwt_key;
        $database = new Database();
        $this->db = $database->getConnection();

        $this->acl = array(
            'admin' => array('blogs' => array('visit','create','edit','delete'),
                             'login' => array('visit','create','edit','delete'),
                             'logout' => array('visit','create','edit','delete'),
                             'blog' => array('visit','create','edit','delete'),
                             'post' => array('visit'),
                             'edit' => array('visit','create','edit','delete'),
                             'editBlog' => array('visit','create','edit','delete'),
                             'editPost' => array('visit','create','edit','delete')
                         ),
            'blogger' => array('blogs' => array('visit'),
                             'login' => array('visit'),
                             'logout' => array('visit'),
                             'blog' => array('visit','create','edit'),
                             'post' => array('visit'),
                             'edit' => array('visit'),
                             'editBlog' => array('visit','create','edit'),
                             'editPost' => array('visit','create','edit')
                         ),
            'public' => array('blogs' => array('visit'),
                             'login' => array('visit'),
                             'logout' => array('visit'),
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
        $result = array(false, '');
        try {
            $decoded = $this->jwt->decode($token, $this->jwt_key, array('HS256'));
        } catch (Exception $e) {
            $this->token_time_to_expire = -100;
            return $result;
        }        
        $decoded_array = (array) $decoded;
        $t = time() - $decoded_array['iat'];
        $this->token_time_to_expire = $this->token_validtime - $t;
        // timestamp (iat) has to be less than 86'400s (24h)
        if ($t < 86400) {
                $result[0] = true;
                $result[1] = $decoded_array['sub'];
                return $result;
        } else {
            return $result;
        }        
    }

    function authoriseView($view, $action) {
        $this->requested_view = $view;
        // if token exist, do token authentication
        if (!empty($this->secure_token)) {       
            // token has to be valid
            $res = $this->validateToken($this->secure_token);
            $validation = $res[0];
            $username = $res[1];
            if ($validation) {
                // read user information from db
                $user = $this->readUser($username);
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
            $this->username = 'no_login';
            $this->authorized = true;
            $this->authorized_to = $this->acl['public'][$view];
            return;
        } else {
            $this->username = 'no_login';
            $this->authorized_to = $this->acl['public'][$view];
            return;
        }
    }
}
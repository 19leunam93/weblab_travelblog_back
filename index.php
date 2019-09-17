<?php
//require headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    parse_str(file_get_contents("php://input"), $put);
    foreach ($put as $key => $value) {
        unset($put[$key]);
        $put[str_replace('amp;', '', $key)] = $value;
    }
    define('$_PUT', $put);
    $_REQUEST = array_merge($_REQUEST, constant('$_PUT'));    
}


//include files
include_once 'jwt/BeforeValidException.php';
include_once 'jwt/ExpiredException.php';
include_once 'jwt/SignatureInvalidException.php';
include_once 'jwt/JWT.php';

use \Firebase\JWT\JWT;

include_once 'database.php';
include_once 'objects/gallery.php';
include_once 'objects/post.php';
include_once 'objects/blog.php';
include_once 'objects/blogs.php';
include_once 'objects/login.php';
include_once 'auth.php';

function getUrl() { 
    $route = array('method' => '','site' => '','id' => 0, 'selection' => 'all');
    $url = explode('/', $_SERVER['REQUEST_URI']);
    $route['method'] = $_SERVER['REQUEST_METHOD'];
    $route['site'] = $url[1];
    if (!empty($url[2])) {
        $route['id'] = $url[2];
    }
    if (!empty($url[3])) {
        $route['selection'] = $url[3];
    }    
    return $route;
}

function getData() {
    $data = array();
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $data = constant('$_PUT');
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = $_POST;
    } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $data = $_GET;
    }
    return $data;
}


$route = getUrl();
$request_data = getData();

$jwtObj = new JWT;
$jwt_key = 'example_key';
$auth = new Authentication($jwtObj, $jwt_key);
$auth->session_token = $_COOKIE['secure_token'];
$auth->username = $_COOKIE['secure_username'];


switch ($route['site']) {
    case 'blog':
        $blog = new Blog();
        switch ($route['method']) {
            case 'GET':
                $auth->authoriseView($route['site'],'visit');
                $blog->read($route['id'],$route['selection'], $auth);
                break;
            case 'POST':
                $blog->create($route['id']);
                break;
            case 'PUT':
                $blog->write($route['id']);
                break;
            case 'DELETE':
                $blog->delete($route['id']);
                break;
            default:
                $auth->authoriseView($route['site'],'visit');
                $blog->read($route['id'],$route['selection'], $auth);
                break;
        }        
        break;

    case 'blogs':
        $blog = new BlogList();
        $auth->authoriseView($route['site'],'visit');
        $blog->read($auth);
        break;

    case 'post':
        $post = new Post();
        switch ($route['method']) {
            case 'GET':
                $auth->authoriseView($route['site'],'visit');
                $post->read($route['id'], $route['selection'], $auth);
                break;
            case 'POST':
                $post->create($route['id']);
                break;
            case 'PUT':
                //$auth->authoriseView($route['site'],'visit');
                $post->write($route['id'], $route['selection'], $request_data);
                break;
            case 'DELETE':
                $post->delete($route['id']);
                break;
            default:
                $auth->authoriseView($route['site'],'visit');
                $post->read($route['id'], $route['selection'], $auth);
                break;
        }
        break;

    case 'login':
        $login = new Login();
        $auth->authoriseView($route['site'],'visit');
        $login->read($auth);
        break;

    case 'token':
        $token = $auth->getToken($request_data['username'],$request_data['password']);
        if ($token !== false) {
            // token sucessfully created
            setcookie('secure_username',$auth->username,0,'/','.weblab.spuur.ch',false,true);
            setcookie('secure_token',$token,0,'/','.weblab.spuur.ch',false,true);
            http_response_code(200);
            echo('{"token_generated": "true"}');
        } else {
            // authentication to be done
            http_response_code(403);
            echo('Invalid Username or Password');
        }
        break;
    
    default:
        http_response_code(500);
        echo('route not supported');
        break;
}
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
include_once 'database.php';
include_once 'objects/gallery.php';
include_once 'objects/post.php';
include_once 'objects/blog.php';
include_once 'objects/blogs.php';

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
    }
    return $data;
}


$route = getUrl();
$request_data = getData();


switch ($route['site']) {
    case 'blog':
        $blog = new Blog();
        switch ($route['method']) {
            case 'GET':
                $blog->read($route['id'],$route['selection']);
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
                $blog->read($route['id']);
                break;
        }        
        break;

    case 'blogs':
        $blog = new BlogList();
        $blog->read();
        break;

    case 'post':
        $post = new Post();
        switch ($route['method']) {
            case 'GET':
                $post->read($route['id'], $route['selection']);
                break;
            case 'POST':
                //$post->create();
                break;
            case 'PUT':
                $post->write($route['id'], $route['selection'], $request_data);
                break;
            case 'DELETE':
                $post->delete($route['id']);
                break;
            default:
                $post->read($route['id']);
                break;
        }
        break;

    case 'login':
        http_response_code(500);
        echo('route not supported');
        break;
    
    default:
        http_response_code(500);
        echo('route not supported');
        break;
}
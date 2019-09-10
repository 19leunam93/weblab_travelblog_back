<?php
//require headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

//include files
include_once 'database.php';
include_once 'objects/blog.php';
include_once 'objects/post.php';

function getUrl() { 
    $route = array('method' => '','site' => '','id' => 0);  
    $url = explode('/', $_SERVER['REQUEST_URI']);
    $route['method'] = $_SERVER['REQUEST_METHOD'];
    $route['site'] = $url[1];
    $route['id'] = $url[2];
    return $route;
}

$route = getUrl();



switch ($route['site']) {
    case 'blog':
        $blog = new Blog();
        switch ($route['method']) {
            case 'GET':
                $blog->read($route['id']);
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
        $blog = new Blog();
        $blog->read(0);
        break;

    case 'post':
        $post = new Post();
        switch ($route['method']) {
            case 'GET':
                $post->read($route['id']);
                break;
            case 'POST':
                $post->create($route['id']);
                break;
            case 'PUT':
                $post->write($route['id']);
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
        http_response_code(201);
        echo('route not supported');
        break;
    
    default:
        http_response_code(201);
        echo('route not supported');
        break;
}
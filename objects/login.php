<?php
class Login{

    function read($auth) {
        $login = array();
        $login["records"] = array(
                "name" => $auth->name,
                "username" => $auth->username   
            );
        $login["authorization"] = array(
                "logged_in_as_user" => $auth->username,
                "logged_in_as_usergroup" => $auth->usergroup,
                "authorized_current_action" => $auth->authorized,
                "authorized_actions" => $auth->authorized_to
            );

        // set response code - 200 OK
        http_response_code(200);
        // send records in json format
        echo json_encode($login);
        return;
    }
}
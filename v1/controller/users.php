<?php

require_once 'db.php';
require_once '../model/response.php';

try{
    $writeDB = DB::connectWriteDB();
}
catch(PDOException $ex){
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage('Database connection error.');
    $response->send();
    exit;    
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage('Request Method Not Allowed.');
    $response->send();
    exit;    
}


if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage('Content type Not Allowed.');
    $response->send();
    exit;    
}


$raw_post_data = file_get_contents('php"//input');

if(!$json_data = json_decode($raw_post_data)){
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage('Request body is not valid.');
    $response->send();
    exit;    
}

if(!isset($json_data->fullname) || !isset($json_data->username) || !isset($json_data->password)){

    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (!isset($json_data->fullname) ? $response->addMessage('Full name not supplied.') : false);
    (!isset($json_data->username) ? $response->addMessage('User name not supplied.') : false);
    (!isset($json_data->password) ? $response->addMessage('Password not supplied.') : false);
    $response->send();
    exit;
}


if(strlen($json_data->fullname) < 1 || strlen($json_data->fullname) > 255 || strlen($json_data->username) < 1 || strlen($json_data->username) > 255  ||  strlen($json_data->password) < 1 || strlen($json_data->password) > 255){
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (strlen($json_data->fullname) < 1 ? $response->addMessage('Full name cannot be blank.') : false);
    (strlen($json_data->fullname) > 255 ? $response->addMessage('Full name cannot be over 255.') : false);
    (strlen($json_data->username) < 1 ? $response->addMessage('User name cannot be blank.') : false);
    (strlen($json_data->username) > 255 ? $response->addMessage('User name cannot be over 255.') : false);
    (strlen($json_data->password) < 1 ? $response->addMessage('Password cannot be blank.') : false);
    (strlen($json_data->password) > 255 ? $response->addMessage('Password cannot be over 255.') : false);
    $response->send();
    exit;
}


$fullname = trim($json_data->fullname);
$username = trim($json_data->username);
$password = $json_data->password;

try{

    $query = $writeDB->prepare('SELECT user_id FROM users WHERE user_name = :username');
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();

    $row_count = $query->rowCount();

    if($row_count !== 0){
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Couldn\'t create user.');
        $response->send();
        exit;
    }

}
catch(PDOException $ex){
    error_log('Database Query Error -' . $ex, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage('Couldn\'t create user.');
    $response->send();
    exit;
}













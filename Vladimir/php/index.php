<?php

require_once 'src/Session.php';
require_once 'src/DB.php';
require_once 'src/AbstractAction.php';
require_once 'src/LoginAction.php';
require_once 'src/LogoutAction.php';
require_once 'src/UsersListAction.php';
require_once 'src/NotFoundAction.php';

$session = new Session();

$config = require 'config.php';
$dbConfig = $config['db'];

$db = new DB($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

$action = new NotFoundAction($session, $db);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

if ($path === '/auth') {
    if ($method === 'POST') {
        $action = new LoginAction($session, $db);
    }
    if ($method === 'DELETE') {
        $action = new LogoutAction($session, $db);
    }
}

if ($path === '/users') {
    if ($method === 'GET') {
        $action = new UsersListAction($session, $db);
    }
}

$result = $action->execute();

header('Content-Type: application/json');
http_response_code($result['status']);
echo json_encode($result['content'], JSON_THROW_ON_ERROR);

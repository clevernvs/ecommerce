<?php

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

// Rota p/ o Site
$app->get('/', function() {

    $page = new Page();

    $page->setTpl("index");

});

// Rota p/ o Administrador
$app->get('/admin', function() {

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});

// Rota p/ Login
$app->get('/admin/login', function() {

    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);

    $page->setTpl("login");

});

// Rota Validar Login
$app->post('/admin/login', function() {

    User::login($_POST["login"], $POST["password"]);

    header("Location: /admin");
    exit;
});

// Rota p/ Logout
$app->get('/admin/logout', function() {

    User::logout();

    header("Location: /admin/login");
    exit;
    
});

$app->run();

 ?>

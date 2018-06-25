<?php

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

// Rota p/ o SITE
$app->get('/', function() {

    $page = new Page();
    $page->setTpl("index");

});

// Rota p/ o ADMINISTRADOR
$app->get('/admin', function() {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("index");

});

// Rota p/ FAZER LOGIN
$app->get('/admin/login', function() {

    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("login");

});

// Rota p/ VALIDAR LOGIN
$app->post('/admin/login', function() {

    User::login($_POST["login"], $POST["password"]);

    header("Location: /admin");
    exit;
});

// Rota p/ FAZER LOGOUT
$app->get('/admin/logout', function() {

    User::logout();

    header("Location: /admin/login");
    exit;

});

// Rota p/ LISTAR todos os usuários
$app->get('/admin/users', function() {

    User::verifyLogin();

    $user = User::listAll();

    $page = new PageAdmin();
    $page->setTpl("users", array(
        "users"=>$users,
    ));

});

// Rota p/ CRIAR USUÁRIO
$app->get('/admin/users/create', function() {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("users-create");

});

// Rota p/ EXCLUIR USUÁRIO
$app->get("/admin/users/:iduser/delete", function($iduser){

    User::verifyLogin();

    $user = new User();
    $user=>get((int)$iduser);
    $user->delete();

    header("Location: /admin/users");
    exit;
    
});

// Rota p/ ATUALIZAR USUÁRIO
$app->get('/admin/users/:iduser', function($iduser) {

    User::verifyLogin();

    $user = new User();
    $user=>get((int)$iduser);

    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues();
    ));

});

// Rota p/ CRIAR USUÁRIO via POST
$app->post("/admin/users/create", function(){

    User::verifyLogin();

    $user = new User();
    $_POST["inadmin"] = (isset($POST["inadmin"])) ? 1 : 0;
    $user->setData($_POST);
    $user->save();

    header("Location: /admin/users");
    exit;

});

// Rota p/ SALVAR EDIÇÃO
$app->post("/admin/users/:iduser", function($iduser){

    User::verifyLogin();

    $user = new User();
    $_POST["inadmin"] = (isset($POST["inadmin"])) ? 1 : 0;
    $user->get((int)$iduser);
    $user->setData($_POST);
    $user->update();

    header("Location: /admin/users");
    exit;

});


$app->run();

 ?>

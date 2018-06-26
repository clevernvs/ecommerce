<?php

// Arquivo de config de Rotas

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
    $user->get((int)$iduser);
    $user->delete();

    header("Location: /admin/users");
    exit;

});

// Rota p/ ATUALIZAR USUÁRIO
$app->get('/admin/users/:iduser', function($iduser) {

    User::verifyLogin();

    $user = new User();
    $user->get((int)$iduser);

    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
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

// Rota p/ PERDEU A SENHA
$app->get("/admin/forgot", function(){

    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot");

});

// Rota p/ VERIFICAÇÃO DE EMAIL DA RECUPERAÇÃO DE SENHA
$app->post("/admin/forgot", function(){

    $user = User::getForgot($_POST["email"]);

    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent", function (){

    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]
    ));

});

$app->get("/admin/forgot/reset", function (){

    $forgot = User::validForgotDecrypt($_POST["code"]);

    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();
    $user->get((int)$fogot["iduser"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT,[
        "cost"=>12
    ]);

    $user->setPassword($password);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset-sucess");

});



$app->run();

 ?>

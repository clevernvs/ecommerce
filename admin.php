<?php

use \Hcode\PageAdmin;
use \Hcode\User;

// Rota p/ o ADMINISTRADOR
    $app->get('/admin', function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("index");

});

// Rota p/ FAZER LOGIN
$app->get('/admin/login', function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("login");

});

// Rota p/ VALIDAR LOGIN
$app->post('/admin/login', function () {

    User::login($_POST["login"], $POST["password"]);

    header("Location: /admin");
    exit;
});

// Rota p/ FAZER LOGOUT
$app->get('/admin/logout', function () {

    User::logout();

    header("Location: /admin/login");
    exit;

});

// Rota p/ PERDEU A SENHA
$app->get("/admin/forgot", function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot");

});

// Rota p/ VERIFICAÇÃO DE EMAIL DA RECUPERAÇÃO DE SENHA
$app->post("/admin/forgot", function () {

    $user = User::getForgot($_POST["email"]);

    header("Location: /admin/forgot/sent");
    exit;
});

$app->get("/admin/forgot/sent", function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function () {

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));

});

$app->post("/admin/forgot/reset", function () {

    $forgot = User::validForgotDecrypt($_POST["code"]);

    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();
    $user->get((int)$fogot["iduser"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost" => 12
    ]);

    $user->setPassword($password);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset-sucess");

});

?>
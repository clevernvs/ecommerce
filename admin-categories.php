<?php

use \Hcode\PageAdmin;
use \Hcode\User;
use \Hcode\Model\Category;

// Rota p/ CATEGORIAS
$app->get("/admin/categories", function () {

    User::verifyLogin();

    $categories = Category::listAll();

    $page = new PageAdmin();
    $page->setTpl("categories", [
        'categories' => $categories,
    ]);

});

// Rota p/ CRIAR CATEGORIAS
$app->get("/admin/categories/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("categories-create");

});

// Rota p/ CRIAR CATEGORIAS
$app->post("/admin/categories/create", function () {

    User::verifyLogin();

    $category = new Category();
    $category->setData($_POST);
    $category->save();

    header('Location: /admin/categories');
    exit;
});

// Rota p/ DELETAR CATEGORIAS
$app->get("/admin/categories/:idcategory/delete", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);
    $category->delete();

    header('Location: /admin/categories');
    exit;
});

// Rota p / ATUALIZAR CATEGORIAS
$app->get("/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", [
        'category' => $category->getValues()
    ]);

});

// Rota p / ATUALIZAR CATEGORIAS
$app->post("/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();

    header('Location: /admin/categories');
    exit;
});

// Rota p/ CATEGORIAS
$app->get("/categories/:idcategory", function ($idcategory) {

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new Page();
    $page->setTpl("category", [
        'category' => $category->getValue(),
        'products' => []
    ]);
});


?>
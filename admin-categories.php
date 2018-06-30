<?php

use \Hcode\PageAdmin;
use \Hcode\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

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

// ATUALIZAR CATEGORIAS via GET
$app->get("/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", [
        'category' => $category->getValues()
    ]);

});

// ATUALIZAR CATEGORIAS via POST
$app->post("/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    $category->save();

    header('Location: /admin/categories');
    exit;
});


$app->get("/admin/categories/:idcategory/products", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-products", [
        'category'=>$category->getValue(),
        'productsRelated'=>$category->getProducts(),
        'productsNotRelated'=>$category->getProducts(false)
    ]);

});


// ADD
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $product = new Product();
    $product->get((int)$idproduct);

    $category->addProduct($product);
    
    header("Location: /admin/categories/".$idcategory."/products");
    exit;

});

// REMOVER
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct) {

    ser::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $product = new Product();
    $product->get((int)$idproduct);

    $category->removeProduct($product);

    header("Location: /admin/categories/" . $idcategory . "/products");
    exit;

});

?>
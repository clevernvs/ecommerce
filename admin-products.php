<?php

use \Hcode\PageAdmin;
use \Hcode\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function () {

    User::verifyLogin();   

    $products = Product::listAll();

    $page = new PageAdmin();
    $page->setTpl("products", [
        "products"=>$products
    ]);
});

// CRIAR PRODUTO via GET
$app->get("/admin/products/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("products-create");
});

// CRIAR PRODUTO via POST
$app->post("/admin/products/create", function () {

    User::verifyLogin();

    $product = new Product();
    $product->setData($_POST);
    $product->save();

    header("Location: /admin/products");
    exit;
});

// EDITAR PRODUTO via GET
$app->get("/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $page = new PageAdmin();
    $page->setTpl("products-update", [
        'product'=>$product->getValues()
    ]);
});

// EDITAR PRODUTO via POST
$app->post("/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);
    $product->setData($_POST);
    $product->save();
    $product->setPhoto($_FILES["file"]);

    header('Location: /admin/products');
    exit;
});

// EXCLUIR PRODUTO via GET
$app->get("/admin/products/:idproduct/delete", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);
    $product->delete();

    header('Location: /admin/products');
    exit;
});

?>
<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

// Rota p/ o SITE
$app->get('/', function () {

    $products = new Product();   

    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($products)
    ]);
});

// Rota p/ CATEGORIAS
$app->get("/categories/:idcategory", function ($idcategory) {

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new Page();
    $page->setTpl("category", [
        'category' => $category->getValue(),
        'products' => Product::checkList($category->getProducts())
    ]);
});

?>
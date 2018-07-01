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

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $category = new Category();
    $category->get((int)$idcategory);
    
    $pagination = $category->getProductsPage($page);

    $pages = [];
    for ($i=1; $i <= $pagination['pages']; $i++) { 
        array_push($pages, [
            'link' => '/categories/' . $category->getidcategory() . '?page=' . $i,
            'pages' => $i
        ]);
    }

    $page = new Page();
    $page->setTpl("category", [
        'category' => $category->getValue(),
        'products' => $pagination["data"],
        'pages' => $pages
    ]);
});

$app->get("/products/:desurl", function ($desurl) {

    $product = new Product();
    $product->getFromURL($desurl);

    $page = new Page();
    $page->setTpl("product-detail", [
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ]);
});



?>
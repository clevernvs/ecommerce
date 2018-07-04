<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;


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


// CARRINHO
$app->get("/cart", function () {

    $cart = Cart::getFromSession();

    $page = new Page();
    $page->setTpl("cart", [
        'cart' =>$cart->getValues(),
        'product' =>$cart->getProducts()
    ]);
});

// ADICIONAR PRODUTO AO CARRINHO
$app->get("/cart/:idproduct/add", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $qtd = (isset($_GET['qtd'])) ? ()$_GET['qtd'] : 1;
    for ($i=0; $i < $qtd; $i++) { 
        $cart->addProduct($product);        
    }

    header("Location: /cart");
    exit;
});

// REMOVER UM PRODUTO DO CARRINHO
$app->get("/cart/:idproduct/minus", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product);

    header("Location: /cart");
    exit;
});

// REMOVER TODOS OS PRODUTOS DO CARRINHO
$app->get("/cart/:idproduct/remove", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);

    header("Location: /cart");
    exit;
});

?>
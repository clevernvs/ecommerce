<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;


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
        'cart' => $cart->getValues(),
        'product' => $cart->getProducts(),
        'error' => Cart::getMsgError()
    ]);
});

// ADICIONAR PRODUTO AO CARRINHO
$app->get("/cart/:idproduct/add", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $qtd = (isset($_GET['qtd'])) ? ($_GET['qtd']) : 1;    
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

// 
$app->post("/cart/freight", function ($idproduct) {

    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['zipcode']);

    // Redirecionar o usuário para...
    header("Location: /cart");
    exit;
});

$app->get("/checkout", function () {
    // Verficiar se o usuários está logado
    User::verifyLogin(false);

    $cart = Cart::getFromSession();

    $address = new Address();

    $page = new Page();
    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues()
    ]);
    
});

// LOGIN PARA O SITE
$app->get("/login", function () {
    
    $page = new Page();
    $page->setTpl("login", [
        'error' => User::setError()
    ]);

});

// LOGIN PARA O SITE via POST
$app->post("/login", function () {
    
    try {

        // Verficiar o login
        User::login($_POST['login'], $_POST['password']);

    } catch(Exception $e) {
        // Em caso de erro, exibir mensagem
        User::setError($e->getMessage());
    }

    // Redirecionar o usuário para...
    header("Location: /checkout");
    exit;
});

// LOGOUT
$app->get("/logout", function () {

    // Fazer logout
    User::logout();

    // Redirecionar o usuário para...
    header("Location: /login");
    exit;
});

?>
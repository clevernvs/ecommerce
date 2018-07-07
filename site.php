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

    // Redirecionar para...
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
        'error' => User::setError(),
        'errorRegister' => User::getErrorRegister(),
        'registerValues' => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email' => '', 'phone' => '', ]
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

    // Redirecionar para...
    header("Location: /checkout");
    exit;
});

// LOGOUT
$app->get("/logout", function () {

    // Fazer logout
    User::logout();

    // Redirecionar para...
    header("Location: /login");
    exit;
});

// REGISTRAR
$app->post("/register", function () {

    $_SESSION['registerValues'] = $_POST;

    // Verificar se o nome foi definido
    if (!isset($_POST['name']) || $_POST ['name'] == '') {
        
        User::setErrorRegister("Por favor, preencha o seu nome.");
        // Redirecionar para...
        header("Location: /login");
        exit;
    }

    // Verificar se o email foi definido
    if (!isset($_POST['email']) || $_POST['email'] == '') {

        User::setErrorRegister("Por favor, preencha o seu e-mail.");
        // Redirecionar para...
        header("Location: /login");
        exit;
    }
    
    // Verificar se a senha foi definida
    if (!isset($_POST['password']) || $_POST['password'] == '') {

        User::setErrorRegister("Por favor, digite a sua senha.");
        // Redirecionar para...
        header("Location: /login");
        exit;
    }

    // Verificar se o e-mail já está sendo usado em outra conta 
    if (User::checkLoginExist($_POST['email']) === true) {

        User::setErrorRegister("Esse e-mail já está sendo usado por outro usuário.");
        // Redirecionar para...
        header("Location: /login");
        exit;
    }

    $user = new User();
    $user->setData([
        'inadmin' => 0,
        'deslogin' => $_POST['email'],
        'desperson' => $_POST['name'],
        'desemail' => $_POST['email'],
        'despassoword' => $_POST['password'],
        'nrphone' => $_POST['phone']
    ]);
    $user->save();

    // Autenticar o usuário
    User::login($_POST['email'], $_POST['password']);
    
    // Redirecionar para...
    header("Location: /checkout");
    exit;
});

// ================

// PERDEU A SENHA
$app->get("/forgot", function () {
    
    // Redirecionar para...
    $page = new Page();
    $page->setTpl("forgot");

});

// VERIFICAÇÃO DE E-MAIL DA RECUPERAÇÃO DE SENHA via POST
$app->post("/forgot", function () {

    // Enviar e-mail para o usuário
    $user = User::getForgot($_POST["email"], false);

    // Redirecionar para...
    header("Location: /forgot/sent");
    exit;
});

// 
$app->get("/forgot/sent", function () {
    // Redirecionar para...
    $page = new Page();
    $page->setTpl("forgot-sent");

});

// REDEFINIR A SENHA
$app->get("/forgot/reset", function () {

    $user = User::validForgotDecrypt($_GET["code"]);
    
    // Redirecionar para...
    $page = new Page();
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));

});


// REDEFINIR A SENHA via POST
$app->post("/forgot/reset", function () {

    $forgot = User::validForgotDecrypt($_POST["code"]);

    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();
    $user->get((int)$fogot["iduser"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost" => 12
    ]);

    $user->setPassword($password);

    // Redirecionar para...
    $page = new Page();
    $page->setTpl("forgot-reset-sucess");

});


?>
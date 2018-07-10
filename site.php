<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

/*

ROTAS REFERENTES AO SITE DO E-COMMERCE

*/


// SITE
$app->get('/', function () {

    $products = new Product();   
    
    // Direcionar para template com as informações
    $page = new Page();
    $page->setTpl("index", [
        'products' => Product::checkList($products)
    ]);
});

// CATEGORIAS
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

    // Direcionar para template com as informações
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

    // Direcionar para template com as informações
    $page = new Page();
    $page->setTpl("product-detail", [
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ]);
});


// CARRINHO
$app->get("/cart", function () {

    $cart = Cart::getFromSession();

    // Direcionar para template com as informações
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

    // Redirecionar para...
    header("Location: /cart");
    exit;
});

// REMOVER UM PRODUTO DO CARRINHO
$app->get("/cart/:idproduct/minus", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product);

    // Redirecionar para...
    header("Location: /cart");
    exit;
});

// REMOVER TODOS OS PRODUTOS DO CARRINHO
$app->get("/cart/:idproduct/remove", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);

    // Redirecionar para...
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
    
    $address = new Address();

    $cart = Cart::getFromSession();

    if (isset($_GET['zipcode'])) {
        $_GET['zipcode'] = $cart->getzipcode();
    }

    if (isset($_GET['zipcode'])) {        
    
        $address->loadFromCEP($_GET['zipcode']);
        
        $cart->setdeszipcode($_GET['zipcode']);
        $cart->save();
        $cart->getCalculateTotal();   
    }

    if (!$address->getdesaddress()) $address->setdesaddress('');
    if (!$address->getdescomplement()) $address->setdescomplement('');
    if (!$address->getdesdistrict()) $address->setdesdistrict('');
    if (!$address->getdescity()) $address->setdescity('');
    if (!$address->getdesstate()) $address->setdesstate('');
    if (!$address->getdescountry()) $address->setdescountry('');
    if (!$address->getdeszipcode()) $address->setdeszipcode('');




    // Direcionar para template com as informações
    $page = new Page();
    $page->setTpl("checkout", [
        'cart' => $cart->getValues(),
        'address' => $address->getValues(),
        'products'=> $cart->getProducts(),
        'error' => Address::getMsgError()
    ]);
    
});


$app->post("/checkout", function () {
    
    // Verficiar se o usuários está logado
    User::verifyLogin(false);

    // [INÍCIO] Validação dos campos: CEP, Endereço, Bairro, Cidade, Estado, País
    if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe o CEP.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }

    if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe o Endereço.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }

    if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe o Bairro.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }

    if (!isset($_POST['descity']) || $_POST['descity'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe a Cidade.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }

    if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe o Estado.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }

    if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
        // Msg de Erro
        Address::setMsgError("Informe o País.");
        // Redirecionar para...
        header("Location: /checkout");
        exit;
    }
    // [FIM] Validação dos campos: CEP, Endereço, Bairro, Cidade, Estado, País

    // Capturar o usuário
    $user = User::getFromSession();

    $address = new Address();

    $_POST['deszipcode'] = $_POST['zipcode'];
    $_POST['idperson'] = $user->getidperson();

    $address->setData($_POST);
    $address->save();

    // Capturar o carrinho
    $cart = Cart::getFromSession();
        
    $totals = $cart->getCalculateTotal();

    $order = new Order();
    $order->setData([
        'idcart' => $cart->getidcart(),
        'address' => $address->getidaddress(),
        'iduser' => $user->getiduser(),
        'idstatus' => OrderStatus::EM_ABERTO,
        'vltotal' => $totals['vlprice'] + $cart->getvlfreight() 
    ]);
    $order->save();

    // Redirecionar para...
    header("Location: /order/".$order->getidorder());
    exit;

});


// LOGIN PARA O SITE
$app->get("/login", function () {

    // Direcionar para template com as informações
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

// PROFILE
$app->get("/profile", function () {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Capturar sessão do usuário
    $user = User::getFromSession();

    // Direcionar para...
    $page = new Page();
    $page->setTpl("profile",[
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    ]);
});

// SALVAR EDIÇÃO DOS DADOS
$app->post("/profile", function () {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Verificação do nome
    if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
        
        User::setError("Preencha o seu nome.");
        // Redirecionar para... 
        header("Location: /profile");
        exit;
    }
    // Verificação do e-mail
    if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
       
        User::setError("Preencha o seu email.");
        // Redirecionar para... 
        header("Location: /profile");
        exit;
    }
    
    // Capturar sessão do usuário
    $user = User::getFromSession();

    // Verificação se o e-mail já está sendo usado
    if ($_POST['desemail'] !== $user->getdesemail()) {        
        if (User::checkLoginExists($_POST['desemail']) === true) {
            
            User::setError("Esse e-mail já está cadastrado.");
            // Redirecionar para... 
            header("Location: /profile");
            exit;
        }          
    }

    $_POST['inadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getdespassword();
    $_POST['deslogin'] = $_POST['desemail'];

    $user->setData($_POST);
    $user->save();

    User::setSuccess("Seus dados foram salvos com sucesso.");

    // Redirecionar para... 
    header("Location: /profile");
    exit;
});

// 
$app->get("/order/:idorder", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Carregar a Order
    $order = new Order();
    $order->get((int)$idorder);

    // Direcionar para...
    $page = new Page();
    $page->setTpl("payment", [
        'order' => $order->getValues()        
    ]);
});

$app->get("/boleto/:idorder", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Carregar a Order
    $order = new Order();
    $order->get((int)$idorder);

// DADOS DO BOLETO PARA O SEU CLIENTE
    $dias_de_prazo_para_pagamento = 10;
    $taxa_boleto = 5.00;
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
    $valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace(",", ".", $valor_cobrado);
    $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

    $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
    $dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
    $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
    $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
    $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
    $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
    $dadosboleto["sacado"] = $order->getdesperson();
    $dadosboleto["endereco1"] = $order->getdesaddress() . " " . $order->getdesdistrict() ;
    $dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " - CEP:" . $order->getdeszipcode();

// INFORMACOES PARA O CLIENTE
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
    $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - ITAÚ
    $dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
    $dadosboleto["conta"] = "48781";	// Num da conta, sem digito
    $dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

// DADOS PERSONALIZADOS - ITAÚ
    $dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

// SEUS DADOS
    $dadosboleto["identificacao"] = "Hcode Treinamentos";
    $dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
    $dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
    $dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
    $dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

// NÃO ALTERAR!
    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;
    require_once($path . "funcoes_itau.php");
    require_once($path . "layout_itau.php");   

});

?>
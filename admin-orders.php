<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use Hcode\Model\OrderStatus;

$app->get("/admin/orders/:idorder/status", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Carregar o pedido   
    $order = new Order();
    $order->get((int)$idorder);

    // Direcionar com as informações para...
    $page = new Page();
    $page->setTpl("order-status", [
        'order' => $order->getValues(),
        'status' => OrderStatus::listAll(),
        'msgSuccess' => Order::getSuccess(),
        'msgError' => Order::getError()
    ]);

});

$app->post("/admin/orders/:idorder/status", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
       
        Order::setError("Informe o status atual.");
        
        // Redirecionar para...
        header("Location: /admin/orders/".$idorder."/status");
        exit;
    }

    // Carregar o pedido   
    $order = new Order();
    $order->get((int)$idorder);

    $order->setidstatus((int)$_POST['idstatus']);
    $order->save();

    Order::getSuccess("Status atualizado.");

    // Redirecionar para...
    header("Location: /admin/orders/" . $idorder . "/status");
    exit;
});

$app->get("/admin/orders/:idorder/delete", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Carregar o pedido
    // e deletar
    $order = new Order();
    $order->get((int)$idorder);
    $order->delete();

    // Redirecionar para...
    header("Location: /admin/orders");
    exit;

});

$app->get("/admin/orders/:idorder", function ($idorder) {

    // Verificar login do usuário
    User::verifyLogin(false);

    // Carregar o pedido    
    $order = new Order();
    $order->get((int)$idorder);

    // Carregar carrinho
    $cart = $order->getCart();

    // Direcionar com as informações para...
    $page = new Page();
    $page->setTpl("order", [
        'order' => $order->getValues(),
        'cart' => $cart->getValues,
        'products' => $cart->getProducts()
    ]);

});

$app->get("/admin/orders", function(){

    // Verificar login do usuário
    User::verifyLogin(false);

    // Direcionar com as informações para...
    $page = new Page();
    $page->setTpl("orders", [
        'orders' => Order::listAll()        
    ]);

});



?>
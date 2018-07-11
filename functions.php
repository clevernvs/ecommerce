<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;

/*

formatPrice()
checkLogin()
getUserName()

*/

// Formatar o preço para valor 0,00
function formatPrice ($vlprice) 
{   
    if (!$vlprice > 0) {
        $vlprice = 0; 
    }

    return number_format($vlprice, 2, ",", ".");
}

// Checar o login
function checkLogin($inadmin = true)
{
    return User::checkLogin($inadmin);
}

// Capturar o nome do usuário
function getUserName()
{
    // Capiturar a sessão do usuário
    $user = User::getFromSession();

    // Retornar o nome
    return $user->getdesperson();
}

function getCartNrQtd()
{
    // Carregar o carrinho
    $cart = Cart::getFromSession();
    // Carregar o Total dos Produtos
    $totals = $cart->getProductsTotal();
    
    // Retornar o valor total com o frete
    return $totals['nrqtd'];
}

function getCartVlSubTotal()
{
    // Carregar o carrinho
    $cart = Cart::getFromSession();
    // Carregar o Total dos Produtos
    $totals = $cart->getProductsTotal();
    
    // Retornar o sem o frete
    return formatPrice($totals['vlprice']);
}

?>
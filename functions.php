<?php

use \Hcode\Model\User;

/*

formatPrice()
checkLogin()
getUserName()

*/

// Formatar preço para valor 0,00
function formatPrice ($vlprice) 
{   

    if (!$vlprice > 0) {
        $vlprice = 0; 
    }

    return number_format($vlprice, 2, ",", ".");

}

// Chegar login
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

?>
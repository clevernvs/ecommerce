<?php

use \Hcode\Page;

// Rota p/ o SITE
$app->get('/', function () {

    $page = new Page();
    $page->setTpl("index");

});




?>
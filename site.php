<?php

use \Hcode\Page;
use Hcode\Model\Product;

// Rota p/ o SITE
$app->get('/', function () {

    $products = new Product();   

    $page = new Page();
    $page->setTpl("index", [
        'products'=>Product::checkList($products)
    ]);
});

?>
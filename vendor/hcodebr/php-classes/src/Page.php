<?php

namespace Hcode;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $defaults = [
        "data" =>[]
    ];

    public function __construct($opts = array())
    {
        $this->options = array_merge($this->defaults, $opts) ;

        // config Rain
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        // Criar um objeto Tpl
    	$this->tpl = new Tpl;
        $this->setData($this->options["data"]);
        // Criar o template do cabeçalho
        $this->tpl->draw("header");

    }

    private function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->tpl->assing($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct(argument)
    {
        // Criar o template do rodapé
        $this->tpl->draw("footer");
    }
}


?>

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class KontrakJual
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->kontrakjual = load_class('Kontrak_jual', $this->pathIndex);
    }
    function data()
    {
        $data =  $this->kontrakjual->getData();
        return $data;
    }
}



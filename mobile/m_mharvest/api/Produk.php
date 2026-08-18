<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Produk
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->dataproduk = load_class('Master_brg', $this->pathIndex);
    }
    function dataproduk()
    {
        $data =  $this->dataproduk->getproduk();
        // $data = $this->user;
        return $data;
    }
    function dataprodukmobile()
    {
        $data =  $this->dataproduk->getMstBarangM();
        return $data;
    }
}



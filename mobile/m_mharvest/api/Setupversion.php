<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupversion
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Mversion', $this->pathIndex);
    }

    function setupversion(){
        $data =  $this->qClass->getData($this->user);
        return $data;
    }

}

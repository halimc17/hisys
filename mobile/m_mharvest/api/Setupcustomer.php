<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupCustomer
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->datacustomer = load_class('Setup_customer', $this->pathIndex);
    }
    function datacustomer()
    {
        $data =  $this->datacustomer->getData();
        return $data;
    }
}

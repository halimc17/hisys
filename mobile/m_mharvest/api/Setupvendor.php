<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupVendor
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->datasupplier = load_class('Supplier', $this->pathIndex);
    }
    function datasupplier()
    {
        $data =  $this->datasupplier->getData();
        return $data;
    }
}

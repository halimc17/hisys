<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setuporganisasi
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_org', $this->pathIndex);
    }

    function setupdata()
    {
        $data =  $this->qClass->getData();
        return $data;
    }

    function setuporgmobile()
    {
        $data =  $this->qClass->getDataMobile();
        return $data;
    }

}

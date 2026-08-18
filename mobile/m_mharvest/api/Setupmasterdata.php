<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setupmasterdata
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_masterdata', $this->pathIndex);
    }

    function getmasterdata()
    {
        $data =  $this->qClass->getdata($this->user);
        return $data;
    }

    function getmasterdatafinger()
    {
        $data =  $this->qClass->getdatafinger($this->user);
        return $data;
    }
}

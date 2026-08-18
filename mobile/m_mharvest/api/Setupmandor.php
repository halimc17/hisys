<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupMandor
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_mandor', $this->pathIndex);
    }
    function setupkebunmandor()
    {
        // $data =  $this->qClass->getdata("where unit = '" . $this->user['lokasitugas'] . "' and statusaktif = '1'");
        $data =  $this->qClass->getdata($this->user);
        return $data;
    }
}

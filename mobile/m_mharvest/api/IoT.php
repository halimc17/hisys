<?php
defined('BASEPATH') or exit('No direct script access allowed');
class IoT
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Miot', $this->pathIndex);
    }

    function check_token()
    {
        if (!$check = $this->qClass->init()) {
            return $this->qClass->checkToken($this->user);
        } else {
            return $check;
        }

    }
}

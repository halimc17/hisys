<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupApp
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->lMethod = load_class('Setup_app', $this->pathIndex);
    }

    function paramapp()
    {
        $data =  $this->lMethod->getParamAppM($this->user);
        return $data;
    }
}

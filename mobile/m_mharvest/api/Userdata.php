<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Userdata
{
    private static $instance;
    function __construct(){}

    function dataprofile()
    {
        $data = $this->user;
        return $data;
    }
}

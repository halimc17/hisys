<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mluas
{
    protected $pathIndex = "mharvest/";
    // protected $luas;
    // protected $user;
    private static $instance;
    function __construct()
    {
        $this->luas = load_class('Mluaspanen', $this->pathIndex);
    }

    // TODO Status Done Dev -> Waiting QA
    function luasHeader()
    {
        if (!$check = $this->luas->init()) {
            $data =  $this->luas->addHeader($this->user);
            return $data;
        } else {
            return $check;
        }
    }

    // TODO Status Done Dev -> Waiting QA
    function luasDetail()
    {
        $data =  $this->luas->addDetail();
        return $data;
    }

    // TODO Status Done Dev -> Waiting QA
    function checkTrx()
    {
        $data =  $this->luas->checkdatarow();
        return $data;
    }

    function erpHeader()
    {
        return $this->luas->erpheader($this->user, "header");
    }

    function erpDetail()
    {
        return $this->luas->erpheader($this->user,"");
    }
}

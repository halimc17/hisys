<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Kehadiranumum
{
    protected $pathIndex = "mharvest/";
    protected $ku;
    function __construct()
    {
        $this->ku = load_class('MKehadiranUmum', $this->pathIndex);
    }

    function dataHeader()
    {
        if (!$check = $this->ku->init()) {
            return $this->ku->addHeader($this->user,'ABS');
        } else {
            return $check;
        }
    }

    function dataDetail()
    {
        return $this->ku->addDetail($this->user);
    }

    function checkTrx()
    {
        return $this->ku->checkdatarow();
    }

    function getHeader(){
        return $this->ku->getHeaders($this->user,'ABS');
    }

    function getDetail(){
        return $this->ku->getDetails();
    }

    function postERP(){
        return $this->ku->updateFlag();
    }
}

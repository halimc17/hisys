<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Ktaksasi
{
    protected $pathIndex = "mharvest/";

    private static $instance;
    function __construct()
    {
        $this->mtaksasi = load_class('Mtaksasi', $this->pathIndex);
    }

    function header()
    {
        if (!$check = $this->mtaksasi->init()) {
            $data =  $this->mtaksasi->addHeader($this->user, 'TKS');
            return $data;
        } else {
            return $check;
        }
    }

    function detail()
    {
        $data =  $this->mtaksasi->addDetail($this->user);
        return $data;
    }

    function check()
    {
        $data =  $this->mtaksasi->checkdatarow($this->user);
        return $data;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Makp
{
    protected $pathIndex = "mharvest/";

    private static $instance;
    function __construct()
    {
        $this->makp = load_class('Kerapatan_panen', $this->pathIndex);
    }

    function header()
    {
        if (!$check = $this->makp->init()) {
            $data =  $this->makp->addHeader($this->user, 'AKP');
            return $data;
        } else {
            return $check;
        }
    }

    function akp()
    {
        $data = $this->makp->addAkp($this->user);
        return $data;
    }

    function detail()
    {
        $data =  $this->makp->addDetail($this->user);
        return $data;
    }

    function check()
    {
        $data =  $this->makp->checkdatarow($this->user);
        return $data;
    }
}

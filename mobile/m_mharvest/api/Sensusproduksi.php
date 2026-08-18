<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sensusproduksi
{
    protected $pathIndex = "mharvest/";
    protected $sp;
    function __construct()
    {
        $this->sp = load_class('MSensusProduksi', $this->pathIndex);
    }

    function dataheader()
    {
        return $this->sp->init() ?: $this->sp->addHeader($this->user, 'BBC');
    }

    function datadetail()
    {
        return $this->sp->addDetail($this->user);
    }

    function barispokok()
    {
        return $this->sp->addDetail($this->user);
    }

    function check()
    {
        return $this->sp->checkdatarow();
    }
}

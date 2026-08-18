<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setuptujuanspb
{
    private $qClass;
    function __construct()
    {
        $this->qClass = load_class('Mtujuanspb', 'mharvest/');
    }

    function gettujuan()
    {
        if (!$this->qClass->init()) {
            return $this->qClass->getTujuanSPB();
        } else {
            return $this->qClass->init();
        }
    }
}

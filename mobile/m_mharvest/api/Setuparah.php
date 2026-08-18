<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupArah
{
    protected $pathIndex = "mharvest/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_arah', $this->pathIndex);
    }

    function getsetuparah()
    {
        $data = $this->qClass->getArahMobile();
        return $data;
    }
}

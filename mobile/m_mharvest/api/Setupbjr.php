<?php
defined('BASEPATH') or exit('No direct script access allowed');
class SetupBjr
{
    protected $pathIndex = "setup/";
    private static $instance;
    function __construct()
    {
        $this->databjr = load_class('Bjr', $this->pathIndex);
    }
    function databjr()
    {
        // $where = "WHERE kebun_5bjr.kodeorg like '".$this->user['lokasitugas']."%' and kebun_5bjr.periode = DATE_FORMAT(CURRENT_DATE, '%Y-%m')";
        // $data =  $this->databjr->getDataBjr($where);
        $data =  $this->databjr->getDataBjrV2($this->user);
        return $data;
    }
}

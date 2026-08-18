<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setuphama
{
    protected $pathIndex = "kebun/";
    private static $instance;
    function __construct()
    {
        $this->qClass = load_class('Setup_hama', $this->pathIndex);
    }

    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".kebun_5jenishama " . $limitPage;
       
        $data = $this->query($q, 'ASSOC');
        return $data;
    }
    function selectdata(array $pageLimit = array())
    {
        $result = array();
        $data = $this->selectQuery($pageLimit);
        if ($data and $data->rowCount() > 0) {
            $result = $this->fetch($data);
        }
        return $result;
    }

    function setuphamadata()
    {
        // $w = "where karyawanid = '" . $this->user['userid'] . "'";
        // WHERE kodeorg like '".substr($user['lokasitugas'],0,4)."%' ".$kodeorgTujuanAsistensi."";
        $data =  $this->qClass->getHama();
        return $data;
    }
}

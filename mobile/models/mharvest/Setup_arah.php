<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_arah extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_5arah");
        $d['key'] = array("username");
        $this->prepareDB = $d;
    }

    function init()
    {
        $result = false;
        foreach ($this->prepareDB['table'] as $tbl) {
            if (!$this->table_exists($tbl)) {
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "Tabel " . $tbl . " belum tersedia!";
                $result = $this->response;
                break;
            }
        }
        return $result;
    }

    function getArah($where = '')
    {

        $q = "select * from " . $this->db->dbname . ".kebun_5arah";
        $r = $this->fetchdata($q);
        $data = [];
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $d['id'] = $v['id'];
                $d['kode'] = $v['kode'];
                $d['desc'] = $v['desc'];
                $d[$v['kode']] = $v['desc'];
                $data = $d;
            }
        }
        return  $data;
    }

    function getArahMobile($where = '')
    {

        $q = "select * from " . $this->db->dbname . ".kebun_5arah";
        $r = $this->fetchdata($q);
        $data = [];
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $d['id'] = $v['id'];
                $d['kode'] = $v['kode'];
                $d['desc'] = $v['desc'];
                $data[] = $d;
            }
        }
        return  $data;
    }
}

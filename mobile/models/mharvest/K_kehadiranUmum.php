<?php
defined('BASEPATH') or exit('No direct script access allowed');
class K_kehadiranUmum extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "sdm_absensi");
        $d['key'] = array("notransaksi");
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
    function selectHeader($where){
        $data = array();
        $q = "select * from " . $this->db->dbname . ".kebun_aktifitas_mobile " . $where." order by tanggal desc;";
        $data = $this->query($q);
        return $data;
    }
    function selectDetail($where){
        $data = array();
        $q = "select * from " . $this->db->dbname . ".sdm_absensi " . $where.";";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }
   
    private function responseError($message, $status)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }
    private function responseSuccess($message)
    {
        return [
            'status' => 200,
            'error' => false,
            'message' => $message
        ];
    }
}

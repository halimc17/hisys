<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_mdm extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("kebun_aktifitas_mobile", "kebun_prestasi_mobile", "kebun_pakaimaterial_mobile", "sdm_absensi");
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
    private function responseError($message, $status)
    {
        return [
            'status' => $status,
            'error' => true,
            'message' => $message
        ];
    }

    private function responseSuccess($message, $data = [])
    {
        $response = array_merge([
            'status' => 200,
            'error' => false,
            'message' => $message,
        ]);
        return array_merge($data, $response);
    }

    function getAdmin($where=""){
        $data = array();
        $data['Admin'] = array();
        $q = "select * from " . $this->db->dbname . ".admin_mdm {$where}";
        $data['Admin'] = $this->fetchdata($q);
        return $data;
    }
    function getAllowedPackage($where=""){
        $data = array();
        $data['allowedPacakges'] = array();
        $q = "select * from " . $this->db->dbname . ".mdm_allowedpackages {$where}";
        $data['allowedPacakges'] = $this->fetchdata($q);
        return $data;
    }
}

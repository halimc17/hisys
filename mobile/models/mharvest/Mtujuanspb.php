<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Mtujuanspb extends OWL_Model
{
    function __construct()
    {
        $this->prepareDB = [
            'table' => ["setup_tujuanspb"]
        ];
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

    function getTujuanSPB()
    {
        $qry = "SELECT id, nama FROM {$this->db->dbname}.setup_tujuanspb";
        $data = $this->fetchdata($qry);
        foreach ($data as $key => $value) {
            $response[] = [
                'id' => $value['id'],
                'nama' => $value['nama']
            ];
        }
        return $response;
    }
}

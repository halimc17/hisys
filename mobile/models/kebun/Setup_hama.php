<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_hama extends OWL_Model
{
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
    
    function getHama()
    {
        $data = array();
        $q = "select kodehama,namahama,satuan from " . $this->db->dbname . ".kebun_5jenishama";
        // echo $q;
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            $data = $r;
        }
        return $data;
    }

    // private function getAsistensi()
    // {
    //     $qAsistensi = "SELECT kodeorgasal, kodeorgtujuan FROM " . $this->db->dbname . ".kebun_5asistensi    
    //     WHERE tanggalsampai >= CURDATE() and kodeorgasal = '" . $this->user['lokasitugas'] . "' and posting = '1'";
    // }
}

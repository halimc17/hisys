<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Master_brg extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".log_5masterbarang 
		ORDER BY kodebarang ASC " . $limitPage;
        // echo $q;
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
    function getproduk() {
        $data = array();
        $q = "select * from ".$this->db->dbname.".log_5masterbarang where kodebarang like '311%' or  kodebarang like '312%' or kodebarang like '351%' ORDER BY kodebarang ASC";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
    function getMstBarangM()
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".log_5masterbarang where kodebarang like '311%' or kodebarang like '312%' or kodebarang like '351%' ORDER BY kodebarang ASC ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = array(
                    'kodebarang' => $v['kodebarang'],
                    'namabarang' => $v['namabarang'],
                    'satuan' => $v['satuan']
                );
            }
        }
        return $data;
    }
}

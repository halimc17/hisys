<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kontrak_jual extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT * FROM " . $this->db->dbname . ".pmn_kontrakjual
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
    function getData()
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".pmn_kontrakjual
		ORDER BY kodebarang ASC ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kontrak_beli extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT a.koderekanan, a.nokontrak, a.bsart, a.tanggalawal, a.tanggalakhir FROM " . $this->db->dbname . ".pmn_kontrakbeli a
		ORDER BY a.nokontrak ASC " . $limitPage;
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
        $q = "SELECT a.koderekanan, a.nokontrak, a.bsart, a.tanggalawal, a.tanggalakhir FROM " . $this->db->dbname . ".pmn_kontrakbeli a
		ORDER BY a.nokontrak ASC ";
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

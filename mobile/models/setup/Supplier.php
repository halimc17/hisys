<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT a.namasupplier, a.supplierid, a.createdate, case when a.status = 0 then 'tidak aktif' when a.status = 1 then 'aktif' when a.status = 2 then 
        'belum disetujui' when a.status = 3 then 'eksternal' end as 'status' FROM " . $this->db->dbname . ".log_5supplier a
		ORDER BY a.supplierid ASC " . $limitPage;
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
        $q = "SELECT a.namasupplier, a.supplierid, a.createdate, case when a.status = 0 then 'tidak aktif' when a.status = 1 then 'aktif' when a.status = 2 then 
        'belum disetujui' when a.status = 3 then 'eksternal' end as 'status' FROM " . $this->db->dbname . ".log_5supplier a
		ORDER BY a.supplierid ASC ";
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

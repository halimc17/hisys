<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_customer extends OWL_Model
{
    function selectQuery(array $pageLimit = array())
    {
        $limitPage = "";
        if (count($pageLimit) > 0) {
            $limitPage = "LIMIT " . implode(",", $pageLimit);
        }
        $q = "SELECT kodecustomer, namacustomer FROM " . $this->db->dbname . ".pmn_4customer 
		ORDER BY kodecustomer ASC " . $limitPage;
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
        $q = "SELECT kodecustomer, namacustomer FROM " . $this->db->dbname . ".pmn_4customer 
		ORDER BY kodecustomer ASC ";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            // $data = $r;
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }

    function getCustomer($select = '*', $where = '')
    {
        $data = array();
        $q = "SELECT {$select} FROM {$this->db->dbname}.pmn_4customer {$where}";
        $r = $this->fetchdata($q);
        if (count($r) > 0) {
            foreach ($r as $k => $v) {
                $data[] = $v;
            }
        }
        return $data;
    }
}

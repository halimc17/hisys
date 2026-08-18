<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_attribute extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("setup_attribute");
        $d['key'] = array("id");
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

    function selectQuery($where = '')
    {
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_attribute {$where}";
        $data = $this->query($q,'ASSOC');
        return $data;
    }
    function marker_name(){
        $result = array();
        $data = array();
        $where = "where isactive = '1'";
        $dataRow = $this->selectQuery($where);
        if($dataRow and $dataRow->rowCount()>0){
            $data = $this->fetch($dataRow);
        }
        if(count($data) > 0){
            $result = array(""=>"Choose Item");
            foreach($data as $k=>$v){
                $result[$v['code']] = $v['name'];
            }
        }
        return $result;
    }
    

    
}

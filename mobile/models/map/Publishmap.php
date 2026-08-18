<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Publishmap extends OWL_Model
{
    function __construct()
    {
		$this->load->library('GeoJson');
        $d['table'] = array("featurecollection");
        $d['key'] = array("id");
        $this->prepareDB = $d;
    }
    function init(){
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
    function selectQuery($where = ''){
        $q = "SELECT * FROM " . $this->db->dbname . ".featurecollection {$where}";
        return $this->query($q,'ASSOC');
    }
    function loadGeoJson(){
        $result = array();
        $where ="where type = 'geojson' ";
        if($dataRow = $this->selectQuery($where)){
            if($dataRow->rowCount()>0){
                $data = $this->fetch($dataRow);
                foreach($data as $k=>$v){
                    $d = array();
                    $v['src'] = str_replace('m_fileDocuments/','',$v['src']);
                    $d['id'] = $v['id'];
                    $d['name'] = $v['name'];
                    $d['type'] = $v['type'];
                    $d['version'] = $v['version'];
                    $d['src'] = $this->base_url($v['src'],'m_fileDocuments');
                    $result[] = $d;
                }
            }
        }
        return $result;
    }   
}

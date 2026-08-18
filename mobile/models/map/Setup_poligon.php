<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_poligon extends OWL_Model
{
    function __construct()
    {
        $d['table'] = array("fileupload");
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

    //SETUP POLIGON
    function selectQuery($where = '')
    {
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_poligon_color {$where}";
        $data = $this->query($q,'ASSOC');
        return $data;
    }
    
    function selectDataPoligon($pageLimit){
        $data = array();
        $where = " ORDER BY ID ASC ";
        if (count($pageLimit) > 0) {
            $where .= "  LIMIT " . implode(",", $pageLimit);
        }
        $dataRow = $this->selectQuery($where);
        if($dataRow and $dataRow->rowCount()>0){
            $data = $this->fetch($dataRow);
        }
        return $data;
    }
    function saveData($datas){
        $insert['TITLE']=$datas[0];
        $insert['FILLCOLOR']=$datas[1];
        $insert['STROKECOLOR']=$datas[2];

        $cekAdaYangSame=$this->selectQuery(' WHERE FILLCOLOR="'.$insert['FILLCOLOR'].'" AND STROKECOLOR="'.$insert['STROKECOLOR'].'"');
        $dataa=$this->fetch($cekAdaYangSame);
        if(count($dataa)>0){
            exit("GAGAL! Warna Sudah Tersedia");
        }else{
            $qexec = $this->insert($insert, $this->db->dbname . ".setup_poligon_color");
            if($qexec){
                // echo "<meta http-equiv='refresh' content='0'>";
                $this->selectQuery();
            }

        }
    }

    //QUALITY TEMA
    function selectQueryQuality($where = '')
    {
        $data = array();
        $q = "SELECT * FROM " . $this->db->dbname . ".setup_quality_tema {$where}";
        $data = $this->query($q,'ASSOC');
        return $data;
    }
    function selectDataQuality($pageLimit){
        $data = array();
        $where = "WHERE PID = '0' ORDER BY ID ASC ";
        if (count($pageLimit) > 0) {
            $where .= "  LIMIT " . implode(",", $pageLimit);
        }
        $dataRow = $this->selectQueryQuality($where);
        if($dataRow and $dataRow->rowCount()>0){
            $data = $this->fetch($dataRow);
        }
        return $data;
    }
    
    function saveDataQualityTema($datas){
        $insert['title']=$datas[0];
        $insert['color']=$datas[1];
        $insert['ket']=$datas[2];
    
        $cekAdaYangSame=$this->selectQueryQuality(' WHERE COLOR="'.$insert['color'].'"');
        $dataa=$this->fetch($cekAdaYangSame);
        if(count($dataa)>0){
            exit("GAGAL! Warna Sudah Tersedia");
        }else{
            print_r($this->insert($insert, $this->db->dbname . ".setup_quality_tema"));
            $qexec = $this->insert($insert, $this->db->dbname . ".setup_quality_tema");
            if($qexec){
                // echo "<meta http-equiv='refresh' content='0'>";
                $this->selectQuery();
            }
    
        }
    }
}

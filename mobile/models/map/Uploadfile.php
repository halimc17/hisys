<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Uploadfile extends OWL_Model
{
    function __construct()
    {
		$this->load->library('GeoJson');
        $d['table'] = array("fileupload");
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
        $q = "SELECT * FROM " . $this->db->dbname . ".fileupload {$where}";
        return $this->query($q,'ASSOC');
    }
    function listfile($pageLimit){
        $data = array();
        $where = "WHERE isactive=1 and layer =0 ORDER BY createtime";
        if (count($pageLimit) > 0) {
            $where .= " DESC LIMIT " . implode(",", $pageLimit);
        }
        if($dataRow = $this->selectQuery($where)){
            if($dataRow->rowCount()>0){
                $data = $this->fetch($dataRow);
            }
        }
        return $data;
    }
    function selectbyId($id){
        $data = array();
        if($id!=""){
            $where = "where (id = '".$id."' and layer='0') or layer = '".$id."' order by idx ASC";
            $dataRow = $this->selectQuery($where);
            if($dataRow and $dataRow->rowCount()>0){
                $data = $this->fetch($dataRow);
            }
        }
        return $data;
    }
    function getIdxById($id){
        $data = 0;
        if($id!=""){
            $where = "where (id = '".$id."' and layer='0') or layer = '".$id."'";
            $q = "SELECT max(idx) as idx FROM " . $this->db->dbname . ".fileupload {$where}";
            $dataRow = $this->query($q,'ASSOC');
            if($dataRow and $dataRow->rowCount()>0){
                $_temp = $dataRow->fetch();
                $data = $_temp['idx'];
            }
        }
        return $data;
    }
    function insertData($data){
        $exec = false;
        $where = "where name = '".$data['name']."'";
        $q = $this->selectQuery($where);
        if($q and $q->rowCount() > 0 ){
            //update
            $exec = $this->update($data,$this->db->dbname . ".fileupload","name = '".$data['name']."'");
        }else{
            //insert
            $exec = $this->insert($data,$this->db->dbname.".fileupload");

        }
        return $exec;
    }
    function selectStyle($id,$styleid = '0'){
        $data = array();
        $addWhere = "";
        if($id!=""){
            if($styleid == '0'){
                $addWhere = " and style_id='".$styleid."'";
            }else{
                $addWhere = " and style_id!='0'";
            }
            $where = "where file_id = '".$id."'".$addWhere." and isactive = '1'";
            $q = "SELECT * FROM ".$this->db->dbname.".properties {$where}";
            $dataRow = $this->query($q,'ASSOC');
            if($dataRow and $dataRow->rowCount()>0){
                $data = $this->fetch($dataRow);
            }
        }
        return $data;
    }
    function set_style($data){
        $exec = false;
        $where = "where file_id = '".$data['file_id']."' and style_id = '".$data['style_id']."' and name = '".$data['name']."'";
        $q = "SELECT * FROM ".$this->db->dbname.".properties {$where} limit 1";
        $dataRow = $this->query($q,'ASSOC');
        if($dataRow and $dataRow->rowCount() > 0 ){
            //update
            $dataUpdate = array('setvalue'=>$data['setvalue'],'style_name'=>$data['style_name']);
            $exec = $this->update($dataUpdate,$this->db->dbname . ".properties","file_id = '".$data['file_id']."' and style_id = '".$data['style_id']."' and name = '".$data['name']."'");
        }else{
            //insert
            $exec = $this->insert($data,$this->db->dbname.".properties");
        }
        $this->publishFile($data['file_id'],2,1);
        return $data;
    }
    function set_name($data){
        $dUpdae = array(
            'name'=>$data['name']
        );
        $this->update($dUpdae,$this->db->dbname . ".fileupload","id = '".$data['id']."'");
        return $this->response;
    }
    function publishFile($id,$stat=1,$version=''){
        $dUpdae['publish'] = $stat;
        if($version !=''){
            $data = $this->getData($id);
            if(!empty($data)){
                $version = ((int)$data[0]['version']+(int)$version);
                $dUpdae['version'] = $version;
            }
        }
        $execPublish = $this->update($dUpdae,$this->db->dbname . ".fileupload","id = '".$id."' or layer = '".$id."'");
        return $this->response;
    }
    function unpublish($id){
        $data = $this->getData($id);
        if(!empty($data)){
            $this->publishFile($id,0);
            $d['isactive'] = 0;
            $exec = $this->update($d,$this->db->dbname . ".featurecollection","fileid = '".$id."'");
        }else{
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Sorry, Data not found.";
        }
        return $this->response;
    }
    function deleteData($id){
        $datAll = $this->getDataPerfile($id);
        if(!empty($datAll)){
            foreach($datAll as $data){
                $info = pathinfo($data['src']);
                $nameFile = $info['basename'];
                $location = FCPATH.$data['src'];
                if (file_exists($location)){
                    unlink($location);
                    $this->delete($this->db->dbname.".fileupload","id = '".$data['id']."'");
                    if($data['layer'] == '0'){
                        $q_temp = $this->selectTemp("where fileid = '".$data['id']."' limit 1");
                        if($q_temp and $q_temp->rowCount() > 0 ){
                            $f_temp = $q_temp->fetch();
                            if (file_exists(FCPATH.$f_temp['src'])){
                                unlink(FCPATH.$f_temp['src']);
                                $this->delete($this->db->dbname.".featurecollection","fileid = '".$data['id']."'");
                            }
                        }
                    }else{
                        $this->publishFile($data['layer'],2,1);
                    }
                }else{
                    $exec = $this->delete($this->db->dbname.".fileupload","id = '".$data['id']."'");
                    if($data['layer'] != '0'){
                        $this->publishFile($data['layer'],2,1);
                    }
                }
            }
        }else{
            $this->response['status'] = 400;
            $this->response['error'] = true;
            $this->response['message'] = "Sorry, Data not found.";
        }
        return $this->response;
    }
    function selectTemp($where = ''){
        $q = "SELECT * FROM " . $this->db->dbname . ".featurecollection {$where}";
        return $this->query($q,'ASSOC');
    }
    function upload_geojson(){
        $this->response['status'] = 400;
        $this->response['error'] = true;
        $this->response['message'] = "Sorry, there was an error your file geojson.";
        if (isset($_FILES["file_temp"])){
            $location = 'm_fileDocuments';
            $link = 'map_publish/'.date('Y').'/';
            $path =  $location . '/' . $link;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
                chmod($path, 0777);
            } //create Folder if not Exists
            if (!is_writable($path)) {
                $this->response['status'] = 400;
                $this->response['error'] = true;
                $this->response['message'] = "tidak memiliki izin untuk membuat atau mengunggah file ke folder " . $path;
            } else {
                $nameFile = str_replace(" ","_",$_FILES["file_temp"]['name']);
                $pathFile = $path.$nameFile;
                $info = pathinfo($pathFile);
                if($_FILES['file_temp']['size'] > 250000000){
                    $this->response['status'] = 400;
                    $this->response['error'] = true;
                    $this->response['message'] = "Ukuran file upload Max 250kb! ";;
                }else{
                    $file_tmpname = $_FILES['file_temp']['tmp_name'];
                    if (move_uploaded_file($_FILES["file_temp"]["tmp_name"], $pathFile)) {
                        $q_temp = $this->selectTemp("where fileid = '".$_GET['id']."' limit 1");
                        if($q_temp and $q_temp->rowCount() > 0 ){
                            $f_temp = $q_temp->fetch();
                        }
                        $where = "where id = '".$_GET['id']."' limit 1";
                        $q = $this->selectQuery($where);
                        if($q and $q->rowCount() > 0 ){
                            $fileUtama = $q->fetch();
                            $d['fileid'] = $fileUtama['id'];
                            $d['name'] = $fileUtama['name'];
                            $d['version'] = $fileUtama['version'];
                            $d['type'] = $info['extension'];
                            $d['src'] = $pathFile;
                            if(empty($f_temp)){
                                $d['isactive'] = 1;
                                $d['createby'] = $_SESSION['standard']['userid'];
                                $d['createtime'] = date('Y-m-d H:i:s');
                                $exec = $this->insert($d,$this->db->dbname.".featurecollection");
                            }else{
                                if($f_temp['version'] != $fileUtama['version']){
                                    $d['isactive'] = 0;
                                }else{
                                    $d['isactive'] = 1;
                                }
                                $exec = $this->update($d,$this->db->dbname . ".featurecollection","fileid = '".$d['fileid']."'");
                            }
                            if($exec){
                                $dUpdae['publish'] = 1;
                                $execPublish = $this->update($dUpdae,$this->db->dbname . ".fileupload","id = '".$d['fileid']."' or layer = '".$d['fileid']."'");
                                $this->response['result']['src'] = $pathFile;
                                $this->response['result']['id'] = $fileUtama['id'];
                                $this->response['message'] = "The file ". htmlspecialchars(basename($nameFile)). " has been uploaded.";
                            }else{
                                $this->response['status'] = 400;
                                $this->response['error'] = true;
                                $this->response['message'] = "Sorry, there was an error insert Data into Database.";
                            }
                        }else{
                            $this->response['status'] = 400;
                            $this->response['error'] = true;
                            $this->response['message'] = "Sorry, there was an error process your file.";
                        }
                    } else {
                        $this->response['status'] = 400;
                        $this->response['error'] = true;
                        $this->response['message'] = "Sorry, there was an error uploading your file.";
                    }
                }
                // $this->response['message'] = $_GET['id'];
            }
        }else{
            $this->response['message'] = "Missing file_temp ".$_GET['id'];
        }
        return $this->response;
    }
    function upload($isUpdate=true){
        $this->response['status'] = 400;
        $this->response['error'] = true;
        $this->response['message'] = "Sorry, there was an error uploading your file.";
        if (isset($_FILES["fileupload"])){
            // try {
                $location = 'm_fileDocuments';
                $link = 'map/'.date('Y').'/';
                $path =  $location . '/' . $link;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                    chmod($path, 0777);
                } //create Folder if not Exists
                if (!is_writable($path)) {
                    $this->response['status'] = 400;
                    $this->response['error'] = true;
                    $this->response['message'] = "tidak memiliki izin untuk membuat atau mengunggah file ke folder " . $path;
                } else {
                    $nameFile = str_replace(" ","_",$_FILES["fileupload"]['name']);
                    $pathFile = $path.$nameFile;
                    if (file_exists($pathFile) and $isUpdate == false) {
                        $this->response['status'] = 400;
                        $this->response['error'] = true;
                        $this->response['message'] = "File Memiliki Nama yang sama " . $pathFile;
                    }else{
                        $info = pathinfo($pathFile);
                        if($_FILES['fileupload']['size'] > 250000000){
                            $this->response['status'] = 400;
                            $this->response['error'] = true;
                            $this->response['message'] = "Ukuran file upload Max 250kb! ";;
                        }else{
                            $file_tmpname = $_FILES['fileupload']['tmp_name'];
                            $idx = $this->getIdxById((int)$this->post('layer'));
                            if($info['extension'] == 'kmz'){
                                $d['name'] = $nameFile;
                                $d['layer'] = $this->post('layer');
                                $d['idx'] = ($idx+1);
                                $d['namefile'] = $info['filename'];
                                $d['mimes'] = $info['extension'];
                                $d['type'] = $_FILES["fileupload"]['type'];
                                $d['size'] = $_FILES["fileupload"]['size'];
                                $d['src'] = $pathFile;
                                $d['createby'] = $_SESSION['standard']['userid'];
                                $d['createtime'] = date("Y-m-d H:i:s");
                                if (move_uploaded_file($_FILES["fileupload"]["tmp_name"], $pathFile)) {
                                    if($res = $this->insertData($d)){
                                        $this->response['result']['src'] = $pathFile;
                                        $this->response['result']['id'] = $res->lastInsertId();
                                        $this->response['message'] = "The file ". htmlspecialchars(basename($nameFile)). " has been uploaded.";
                                    }else{
                                        $this->response['status'] = 400;
                                        $this->response['error'] = true;
                                        $this->response['message'] = "Sorry, there was an error insert Data into Database.";
                                    }
                                } else {
                                    $this->response['status'] = 400;
                                    $this->response['error'] = true;
                                    $this->response['message'] = "Sorry, there was an error uploading your file.";
                                }
                            }else{
                                $this->response['status'] = 400;
                                $this->response['error'] = true;
                                $this->response['message'] = "Sorry, there was an error uploading your file (".$info['extension'].")";
                            }
                        }
                    }
                }
        }
        return $this->response;
    }
    function getData($id){
        $result = false;
        $data = $this->selectbyId($id);
        if(count($data)> 0){
            $result = $data;
        }
        return $result;
    }
    function getDataPerfile($id){
        $result = false;
        if($id!=""){
            $where = "where id = '".$id."' or layer = '".$id."'";
            $dataRow = $this->selectQuery($where);
            if($dataRow and $dataRow->rowCount()>0){
                $result = $this->fetch($dataRow);
            }
        }
        return $result;
    }
    
    function loadFile($file_tmpname,$nameFile){
        $file = $this->library->GeoJson->init($file_tmpname,$nameFile);
        return $file;
    }
    function getfileUri($src){
        $info = pathinfo($src);
        $nameFile = $info['basename'];
        $location = FCPATH.$src;
        if (file_exists($location)){
            $file = $this->loadFile($location,$nameFile);
        }
        return $file;
    }
    function getfile(){
        $file = Null;
        $nameFile = str_replace(" ","_",$_FILES["fileupload"]['name']);
        if (isset($_FILES["fileupload"])){
            $file_tmpname = $_FILES['fileupload']['tmp_name'];
            return $this->loadFile($file_tmpname,$nameFile);
        }       
    }
}

<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Taskassignment extends OWL_Model{
    function init(){
        // IF TABLE NOT EXISTS
        if($this->table_exists('sdm_taskassignment')){
            $crteate = "CREATE TABLE `sdm_taskassignment` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `iddoc` int(11) NOT NULL COMMENT 'id dari sdm_taskdocument',
                `regisfrom` int(11) NOT NULL,
                `subject` varchar(250) NOT NULL,
                `assignto_code` varchar(10) NOT NULL,
                `assignto_desc` varchar(50) NOT NULL,
                `startdate` datetime NOT NULL,
                `targetdate` datetime NOT NULL,
                `needs` char(5) NOT NULL,
                `status` tinyint(1) unsigned NOT NULL COMMENT '0=close/1=open/2=inprogress/3=complete',
                `progress` tinyint(1) unsigned NOT NULL COMMENT '1=Done/2=QC/3=Confirmation/4=QA/5=complete',
                `createby` int(10) unsigned zerofill NOT NULL COMMENT 'pembuat task',
                `createdate` datetime NOT NULL,
                `closedate` datetime NOT NULL,
                `description` longblob NOT NULL,
                PRIMARY KEY (`id`),
                KEY `id_closedate` (`id`,`closedate`),
                KEY `assignto_code_assignto_desc_status_closedate` (`assignto_code`,`assignto_desc`,`status`,`closedate`),
                KEY `iddoc_closedate` (`iddoc`,`closedate`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
            $this->exec($crteate);
        }
    }
    function selectQuery($id="",$bagian="",array $pageLimit = array()){
        $seleftID = false;
        $load = true;
        $priv = $this->model('Privilege');
        if($bagian == "WHEREID"){
            $where = "a.id = '".$id."'";
        }elseif($bagian == "WHEREIDARRAY"){
            if(is_array($id) and count($id) > 0){
                $where = "a.id in ('".implode("','",$id)."')";
            }else{
                $load = false;
            }
        }elseif($bagian == "WHEREUSERID"){
            $where = "(e.karyawanid = '{$id}' or LPAD(a.createby,10,'0') = '{$id}') and a.assignto_desc != ''";
        }elseif(($id=="" and $bagian=="") or $priv->is_admin($_SESSION['standard']['userid'])){
            $where = "a.assignto_desc != ''";
        }else{
            $where = "(e.karyawanid = '{$id}' or LPAD(a.createby,10,'0') = '{$id}') and a.assignto_desc != ''";
        }
        $data = array();
        if($load){
            $limitPage = "";
            if(is_null($pageLimit) or count($pageLimit) > 0){
                $limitPage = "LIMIT ".implode(",",$pageLimit);
            }
           
            $q = "select a.id,a.iddoc,a.regisfrom,a.subject,a.assignto_code,a.assignto_desc,a.startdate,a.targetdate,
                a.status,a.progress,a.createby,a.closedate,a.needs,a.description,d.description as assignto_by,a.createby,
                group_concat(DISTINCT(c.createby) SEPARATOR ',') as partisipan,max(c.askstatus) as progress_ask,count(c.idtask) as jlmpart,
                FLOOR(SUM(IF(ifnull(c.endtime,'0000-00-00 00:00:00')='0000-00-00 00:00:00',0,TIMESTAMPDIFF(SECOND,c.starttime,c.endtime)))/(3600*24)) as hari,
                FLOOR(MOD(SUM(IF(ifnull(c.endtime,'0000-00-00 00:00:00')='0000-00-00 00:00:00',0,TIMESTAMPDIFF(SECOND,c.starttime,c.endtime))),(3600*24))/3600) as jam,
                FLOOR(MOD(SUM(IF(ifnull(c.endtime,'0000-00-00 00:00:00')='0000-00-00 00:00:00',0,TIMESTAMPDIFF(SECOND,c.starttime,c.endtime))),3600)/60) as menit,
                MOD(MOD(SUM(IF(ifnull(c.endtime,'0000-00-00 00:00:00')='0000-00-00 00:00:00',0,TIMESTAMPDIFF(SECOND,c.starttime,c.endtime))),3600),60) as detik,
                CASE
                    WHEN d.column = 'bagian' THEN (select nama from ".$this->db->dbname.".sdm_5departemen where kode=a.assignto_desc)
                    WHEN d.column = 'karyawanid' THEN (select namakaryawan as nama from ".$this->db->dbname.".datakaryawan where karyawanid = a.assignto_desc)
                END as assignto,
                @find:=group_concat(DISTINCT(ifnull(e.karyawanid,LPAD(a.createby,10,'0'))) SEPARATOR ',') as ditujukan
                from ".$this->db->dbname.".sdm_taskassignment a
                left join ".$this->db->dbname.".sdm_taskdocument b on a.iddoc=b.id
                left join ".$this->db->dbname.".sdm_taskassignmentpresent e on a.id=e.idassign
                left join ".$this->db->dbname.".sdm_taskassgmentlog c on a.id=c.idtask
                left join ".$this->db->dbname.".sdm_5bycolumn d on a.assignto_code=d.id and d.tablename = 'datakaryawan' and d.isactive ='1'
                where {$where} and a.closedate = '0000-00-00 00:00:00' group by a.id order by a.createdate DESC,a.progress ASC ".$limitPage;
                
            $data = $this->query($q,'ASSOC');
            
        }
        return $data;
    }
    function selectdata($id="",$bagian="",array $pageLimit = array()){
        $result = array();
        $data = $this->selectQuery($id,$bagian,$pageLimit);
        if($data and $data->rowCount() > 0){
			$result = $this->fetch($data);
        }
        return $result;
    }
    function getdataDetail($id){
        $data = array();
        $r = $this->selectdata($id,'WHEREID');
        $log = "SELECT createby as karyawanid,`description`,askstatus FROM ".$this->db->dbname.".`sdm_taskassgmentlog` where idtask = '".$id."' and ROUND(askstatus) = askstatus";
        $rLog = $this->fetchdata($log);
        $karyawanid = array();
        if(count($rLog) > 0){
            foreach($rLog as $iLog=>$sLog){
                $karyawanid[$sLog['askstatus']]['name'] = $sLog['karyawanid'];
                $karyawanid[$sLog['askstatus']]['desc'] = $sLog['description'];
            }
        }
        $status = $this->statusDef();
        if(count($r) > 0){
            foreach($r as $k=>$v){
                foreach($status['flag'] as $i=>$s){
                    if($i == 0){
                        $r[$k]['statuslog'][$i]['name'] = $r[$k]['createby'];
                        $r[$k]['statuslog'][$i]['desc'] = "";
                    }else{
                        $r[$k]['statuslog'][$i]['name'] = @$karyawanid[$i]['name'];
                        $r[$k]['statuslog'][$i]['desc'] = @$karyawanid[$i]['desc'];
                    }
                }
                $data[] = $r[$k];
            }
        }
        
        return $data;
    }
    function getdata($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_taskassignment {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[] = $v;
            }
        }
        return $data;
    }
    function selectOpt($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_taskdocument {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v['subject'];
            }
        }
        return $data;
    }
    function getdataDocument($where=""){
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_taskdocument {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[] = $v;
            }
        }
        return $data;
    }
    function getDevProgress($in_idtask=array()){
        $data = array();
        if(count($in_idtask)>0){
            $q = "select t1.* from 
            ".$this->db->dbname.".`sdm_taskassgmentlog` as t1 
            left join (SELECT max(askstatus) as status,idtask
            FROM ".$this->db->dbname.".`sdm_taskassgmentlog` as t1
            where askstatus > 0 and askstatus <= 1 and idtask in ('".implode("','",$in_idtask)."') group by idtask) 
            as t2 on t1.idtask = t2.idtask and t1.askstatus = t2.status
            where t2.idtask is Not Null and t2.status is Not Null group by t1.idtask";
            $r = $this->fetchdata($q);
           //echo $q;
            if(count($r) > 0){
                foreach($r as $k=>$v){
                    $data[$v['createby']][] = $v;
                }
            }
        }
        return $data;
    }
    function getUserlogTask(){
        $data = array();
        $q = "SELECT max(askstatus) as status,idtask
        FROM ".$this->db->dbname.".`sdm_taskassgmentlog` group by idtask";
        $r = $this->fetchdata($q);
        // echo $q;
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['idtask']] = $v['status'];
            }
        }
        return $data;
    }
    function getUserTask($tahun = ""){
        if($tahun == ""){
            $tahun = date('Y');
        }
        $data = array();
        $q = "SELECT id,assignto_code,assignto_desc,progress,`status`,`needs` FROM ".$this->db->dbname.".`sdm_taskassignment` where assignto_code = '2'";// and startdate like '".$tahun."%'";
        $r = $this->fetchdata($q);
        // echo $q;
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v;
            }
        }
        return $data;
    }
    function getDepartTask($tahun = ""){
        if($tahun == ""){
            $tahun = date('Y');
        }
        $data = array();
        $q = "SELECT id,assignto_code,assignto_desc,progress,`status`,`needs` FROM ".$this->db->dbname.".`sdm_taskassignment` where assignto_code = '1'";// and startdate like '".$tahun."%'";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v;
            }
        }
        return $data;
    }
    function getTaskList($order_by = ""){
        if($order_by == ""){
            $order_by = "order by a.regisfrom ASC,b.subject ASC,a.progress ASC";
        }
        $data = array();
        $q = "SELECT a.id,c.nama as client,a.subject,b.subject as document,a.regisfrom,a.assignto_code,a.assignto_desc,a.progress,a.`status`,a.`needs`,a.`startdate`,a.`targetdate`,a.`closedate` FROM ".$this->db->dbname.".`sdm_taskassignment` a
        left join ".$this->db->dbname.".`sdm_taskdocument` b on a.iddoc = b.id
        left join ".$this->db->dbname.".`project_5regist` c on a.regisfrom = c.id
        where a.status = '1' and c.tipe != '0' ".$order_by;
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['id']] = $v;
            }
        }
        return $data;
    }
    function statusDef(){
        // Create -> Open discuss-> Progress -> QC -> QA -> complagted
        $result['flag'] = array('Created','Done Progress','Done QC','Confirmed','Done QA','Completed');
        $result['load'] = array('On Progress','QC','Confirmation','QA','Completed');
        return $result;
    }
    function selectChat($where){
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_taskassignmentdt {$where}";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[] = $v;
            }
        }
        return $data;
    }
    function checkLastime($idtask,$createdate){
        $data = false;
        $q = "select createdate from ".$this->db->dbname.".sdm_taskassignmentdt where parentid='".$idtask."' and createdate > '".$createdate."' order by createdate DESC limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $v = array_shift($r);
            $data = $v['createdate'];
        }
        return $data;
    }
    function last_logtask($karyawanid,$idtask){
        $data = array();
        $q = "select starttime,endtime,askstatus from ".$this->db->dbname.".sdm_taskassgmentlog where createby = '".$karyawanid."' and idtask = '".$idtask."'  order by starttime DESC limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            if(!empty($r[0]['starttime'])){
                $data = $r[0];
            }
        }
        return $data;
    }
    function is_active_userlogtask($karyawanid,$idtask){
        $data = false;
        $q = "select idtask from ".$this->db->dbname.".sdm_taskassgmentlog where createby = '".$karyawanid."' and idtask != '".$idtask."' and endtime = '0000-00-00 00:00:00' order by starttime DESC limit 1";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            $data = $r[0]['idtask'];
        }
        return $data;
    }
    function actived_logtask($karyawanid = ""){
        $priv = $this->model('Privilege');
        $data = false;
        $where = "";
        if($karyawanid != ""){
            $where = " and createby = '".$karyawanid."' ";
        }else{
            if(!$priv->is_admin($_SESSION['standard']['userid'])){
                $where = " and createby = '".$_SESSION['standard']['userid']."' ";
            }
        }
        $q = "select createby as karyawanid,idtask,starttime from ".$this->db->dbname.".sdm_taskassgmentlog where endtime = '0000-00-00 00:00:00' ".$where." order by createby ASC,starttime DESC";
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            foreach($r as $k=>$v){
                $data[$v['karyawanid']]['id'][] = $v['idtask'];
                $data[$v['karyawanid']][$v['idtask']] = $v['starttime'];
            }
        }
        return $data;
    }
    
    function catch_logtask($id){
        //echo PRODUCT_KEY;
        $data = array();
        $q = "select * from ".$this->db->dbname.".sdm_taskassgmentlog where md5(concat(idtask,createby,'".PRODUCT_KEY."')) = '".$id."' order by starttime DESC limit 1";
        //echo $q;
        $r = $this->fetchdata($q);
        if(count($r) > 0){
            if(!empty($r[0]['starttime'])){
                $data = $r[0];
            }
        }
        return $data;
    }
    function encryptionId($param=""){
        return md5($param.PRODUCT_KEY); 
    }
}
?>
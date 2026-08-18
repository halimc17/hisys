<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$num = 0;
$mayor = $_POST['mayor'];
$subkelompokbarang = $_POST['subkelompokbarang'];
$method = $_POST['method'];

$hargasatuan=checkPostGet('hargasatuan','');
$nhargasatuan=checkPostGet('nhargasatuan','');
$kodebarang=checkPostGet('kodebarang','');

switch ($method) {
    case 'getSubKlBarang':
        $str = "select kode,namasubkelompok from " . $dbname . ".log_5subklbarang 
			where kelompok = '" . $mayor . "' and status='1' order by kode asc";
        $op = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $op.="<option value='" . $bar->kode . "'>" . $bar->kode . " - " . $bar->namasubkelompok . "</option>";
        }
        echo $op;
        exit();
        break;

    case 'getKodeMaterial':
        $str = "select * from " . $dbname . ".log_5masterbarang where
			  kodebarang like '" . $subkelompokbarang . "%' order by kodebarang desc limit 1";
        // echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
            $num = $bar->kodebarang;
        }
        if ($subkelompokbarang == '') {
            $num = '';
        } else {
            if ($num == '') {
                $num = $subkelompokbarang . '0001';
            } else {
                $num+=1;
            }
        }
        echo $num;
        break;
		
	case'clearseasion':
		$_SESSION['thargasatuan']=array();
	break;
		
	case'chooseTarget':
		if($hargasatuan!=''){
			$newdata = array('kodeorganisasi'=>$hargasatuan,'namaorganisasi'=>$nhargasatuan);
			array_push($_SESSION['thargasatuan'],$newdata);
		}
	break;
	
	case'loadhargasatuan':
		$tab="";
		foreach($_SESSION['thargasatuan'] as $key=>$val){
			$tab.="<div class='choosed noselect' onclick=\"deletehargasatuan('".$val['kodeorganisasi']."')\">".$val['namaorganisasi']."</div>";
		}
		
		echo $tab;
	break;
	
	case'deletehargasatuan':
		foreach($_SESSION['thargasatuan'] as $key=>$val){
			if($val['kodeorganisasi'] == $hargasatuan){
				unset($_SESSION['thargasatuan'][$key]);
			}
		}
	break;
	
	case'getseasion':
		$_SESSION['thargasatuan']=array();
		$str="select hargasatuan from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
		$res=fetchdata($str);
		$hargasatuan=$res[0]['hargasatuan'];
		
		if($hargasatuan!=''){
			$optnamaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)='4'");
			$exphargasatuan=explode(',',$hargasatuan);
			foreach($exphargasatuan as $val){
				$newdata = array('kodeorganisasi'=>$val,'namaorganisasi'=>$optnamaorg[$val]);
				array_push($_SESSION['thargasatuan'],$newdata);				
			}
		}
	break;
	
	case'loadopthargasatuan':
		$nourut=0;
		$optnewsc="";
		foreach($_SESSION['thargasatuan'] as $key=>$val){
			if($nourut==0){
				$optnewsc .= "'".$val['kodeorganisasi']."'";
			}else{
				$optnewsc .= ",'".$val['kodeorganisasi']."'";
			}
			$nourut++;
		}
		
		$arrnewsc="";
		if($optnewsc!=""){
			$arrnewsc=" and kodeorganisasi not in (".$optnewsc.")";
		}
	
		## GET JENIS USAHA
		if($nourut==0){
			$optjenisusaha.="<option value=''>Global</option>";			
		}else{
			$optjenisusaha.="<option value=''></option>";			
		}
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' ".$arrnewsc."";
		$res=fetchdata($str);
		foreach($res as $val){
			$optjenisusaha.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
		}
		
		echo $optjenisusaha;
	break;

    default:
        break;
}
?>

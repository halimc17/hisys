<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/cekakun.php');
// exit("Error:A");
require_once('dompdf/autoload.inc.php');
include_once('lib/terbilang.php');
use Dompdf\Dompdf;
// error_reporting(0);
$table='keu_adjustmentlaporankeuangan';
$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}
// $kodept = checkPostGet('kodept','');

// exit("Error:$method");
switch ($method) {
	
	case'saveht':
	
		if($param['kodeunit']==''){
			exit("Warning:Nomor Unit kosong");
		}
		if($param['jenis']==''){
			exit("Warning:Nomor Jenis masih kosong");
		}
		if($param['code']==''){
			exit("Warning:Nomor Kode masih kosong");
		}
		if($param['periode']==''){
			exit("Warning:Nomor Periode masih kosong");
		}
		$param['jumlah']=str_replace(',', '',$param['jumlah']);
		if($param['notransaksi']==''){
			#= insert
			$str = "insert into ".$dbname.".".$table." (kodeunit,jenis,code,periode,jumlah,keterangan,createby,updateby,createtime) values 
			('".$param['kodeunit']."','".$param['jenis']."','".$param['code']."','".$param['periode']."','".$param['jumlah']."','".$param['keterangan']."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			// exit("Error:$str");
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}else{
			#= update
			$str = "update ".$dbname.".".$table." set kodeunit='".$param['kodeunit']."',jenis='".$param['jenis']."',code='".$param['code']."',periode='".$param['periode']."',jumlah='".$param['jumlah']."',keterangan='".$param['keterangan']."',updateby='".$_SESSION['standard']['userid']."',updatetime='".date('Y-m-d H:i:s')."' where notransaksi='".$param['notransaksi']."'";
			// exit("Error:$str");
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
			
		}
	break;

	
	case'loaddata':
		#= untuk unit ht
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  kodeunit in ('".implode("','",$dtunit)."') ";
		
		if($param['kodeunit']!=''){
			$where.=" and kodeunit = '".$param['kodeunit']."'";
		}
		if($param['jenis']!=''){
			$where.=" and jenis='".$param['jenis']."'";
		}
		if($param['periode']!=''){
			$where.=" and periode='".$param['periode']."'";
		}
		if($param['keterangan']!=''){
			$where.=" and keterangan='".$param['keterangan']."'";
		}
		if($param['jumlah']!=''){
			$where.=" and jumlah='".$param['jumlah']."'";
		}
		if($param['code']!=''){
			$where.=" and code='".$param['code']."'";
		}
		// print_r($param);
	
		
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=30;
	
		$offset = $page * $limit;
		
		
		// echo $limit._.$page._.$maxdisplay._.$offset;
		// $str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi  ";
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
		

		$no = 0;
		$no=$maxdisplay;
		$statusapp = '';
		$str = "select * from ".$dbname.".".$table." where ".$where." order by periode desc,jenis desc,code desc limit " . $offset . "," . $limit . " ";
		$res=fetchdata($str);
		foreach($res as $bar){
			
			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['updateby']."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}
			
			$no++;
			$tab.="<tr ".$bgcolor." ".$style." class=rowcontent >";
				$tab.="<td align=center valign=top>".$no."</td>";
				$tab.="<td valign=top>".$bar['kodeunit']."</td>";
				$tab.="<td valign=top>".$bar['jenis']."</td>";
				$tab.="<td valign=top>".$bar['code']."</td>";
				$tab.="<td valign=top>".$bar['periode']."</td>";
				$tab.="<td align=right valign=top>".hidezerodecimal($bar['jumlah'],2)."</td>";
				$tab.="<td valign=top>".nl2br($bar['keterangan'])."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['createby']]."</td>";
				$tab.="<td valign=top>".updatetimedata($bar['createtime'])."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['updateby']]."</td>";
				$tab.="<td valign=top>".updatetimedata($bar['updatetime'])."</td>";
				$tab.="<td align=center valign=top  style=\"width:20px;\">";
				$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."');\"></td>";
				$tab.="<td align=center valign=top  style=\"width:20px;\">";
				$tab.="<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."');\"></td>";		
			$tab.="</tr>";
        }
		$tab2=createpaging($jumrow,$limit,$page,$colspan,'loaddata','getpage');
		//$tab.="</table>";
        echo $tab."####".$tab2;
	break;
	
	
	
	case'deleteht':
		try{
			$owlPDO->beginTransaction();
			##Delete kas/bank HT
			$str = "delete from ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' ";
			$owlPDO->exec($str); 
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
		
		
		
	break;
	
	case'geteditht':
	
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		$res[0]['noakun'];

		echo 
		$res[0]['kodeunit']."###".
		$res[0]['periode']."###".
		$res[0]['jenis']."###".
		$res[0]['code']."###".
		number_format($res[0]['jumlah'],2)."###".
		$res[0]['keterangan'];
	break;
	
    default:
	break;
}
?>

<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
$karyawan=checkPostGet('karyawan','');
$method=checkPostGet('method','');
$arrstat=array('0'=>'Tahap Persetujuan','1'=>'Disetujui','2'=>'Ditolak');
switch($method){
	case 'excel':
		$whr='';
		if($kodeorg!=''){
			$whr=" and a.kodeorg='".$kodeorg."'";
		}
		$hariini = date("Y-m-d");
		$str1="select a.*,b.namakaryawan,b.tanggalmasuk, b.nik
	       from ".$dbname.".sdm_dayoff_dt_vw a
		   left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
	       where 1=1 ".$whr." 
		   and a.tanggaldayoff like '%".$periode."%' 
		   and a.karyawanid like '%".$karyawan."%'
		   and b.tipekaryawan in(0,1,2,3,7,8,9,12)
		   and (tanggalkeluar='0000-00-00' or tanggalkeluar>='".$hariini."') order by b.namakaryawan"; 
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res1);
		
		if($numrows <= 0){
			echo $_SESSION['lang']['datanotfound'];
		}else{
			$stream.="Rekap ".$_SESSION['lang']['cuti'].":".$periode."
				 <table border=1>
				 <thead>
				 <tr>
					<td bgcolor='#dedede'>No</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['kodeorganisasi']."</td>		 
					<td bgcolor='#dedede'>".$_SESSION['lang']['nik2']."</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['namakaryawan']."</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['tanggalmasuk']."</td>			
					<td bgcolor='#dedede'>".$_SESSION['lang']['periode']."</td>			
					<td bgcolor='#dedede'>".$_SESSION['lang']['dari']."</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['tanggalsampai']."</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['hakcuti']."</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['diambil']."</td>
					<td bgcolor='#dedede'>Akan Diambil/Tahap Persetujuan Cuti</td>
					<td bgcolor='#dedede'>".$_SESSION['lang']['sisa']."</td>
					<td bgcolor='#dedede'>Notransaksi Cuti</td>
					<td bgcolor='#dedede'>Status</td>
					</tr>
				 </thead>
				 <tbody>"; 
			$no=0;	 
			while($bar1=$res1->fetch())
			{
				$no+=1;
				$arrnotrans=explode('/', $bar1->notransaksi);
				
				$stream.="<tr>
						   <td>".$no."</td>
						   <td>".$arrnotrans[3]."</td>
						   <td>".$bar1->nik."</td>
						   <td>".$bar1->namakaryawan."</td>
						   <td>".tanggalnormal($bar1->tanggalmasuk)."</td>
						   <td>".$periode."</td>				   
						   <td>".tanggalnormal($bar1->tanggaldayoff)."</td>
						   <td>".tanggalnormal($bar1->tanggalberlakusampai)."</td>
						   <td>".$bar1->jumlahharidayoff."</td>
						   <td>".$bar1->diambil."</td>
						   <td>".$bar1->akandiambil."</td>
						   <td>".($bar1->jumlahharidayoff-$bar1->diambil-$bar1->akandiambil)."</td>
						   <td>".$bar1->notransaksicuti."</td>
						   <td>".$arrstat[$bar1->status]."</td>
					</tr>	   
						   ";
			}	 
			$stream.="	 
				 </tbody>
				 <tfoot>
				 </tfoot>
				 </table>";
			
			$nop_="Rekap_Cuti_Periode".$periode;
			if(strlen($stream)>0)
			{
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
			}
			fclose($handle);
			}	
		}
	break;
	
	default:
	break;
}
?>
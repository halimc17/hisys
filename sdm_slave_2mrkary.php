<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$periode=checkPostGet('periode','');

 

switch($method){
	case'getUnit':
		$optunit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optunit;
	break;
	
	case'preview': 
		
		## AMBIL KARYAWAN MANAGER
		$str = "select kodejabatan,tipekaryawan, count(karyawanid) as jlhkar  from ".$dbname.".datakaryawan where tipekaryawan = '0' and kodeorganisasi ='".$pt."' and kodejabatan in (SELECT kodejabatan FROM `sdm_5jabatan` WHERE `namajabatan` LIKE '%manager%' AND `namajabatan` NOT LIKE '%asisten%' AND `namajabatan` NOT LIKE '%Sekretaris%') group by kodejabatan,tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $val){
			$karymanager[$val['kodejabatan']][$val['tipekaryawan']] = $val['jlhkar'];
			$ttlkarymanager += $val['jlhkar'];

		} 

		## AMBIL KARYAWAN STAFF
		$str = "select kodejabatan,tipekaryawan, count(karyawanid) as jlhkar  from ".$dbname.".datakaryawan where tipekaryawan = '0' and kodeorganisasi ='".$pt."' group by kodejabatan,tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $val){
			$karystaff[$val['kodejabatan']][$val['tipekaryawan']] = $val['jlhkar'];
			$ttlkarystaff += $val['jlhkar'];


		} 
		## AMBIL KARYAWAN NON STAFF
		$str = "select kodejabatan,tipekaryawan, count(karyawanid) as jlhkar  from ".$dbname.".datakaryawan where tipekaryawan = '1' and kodeorganisasi ='".$pt."' group by kodejabatan,tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $val){
			$karynonstaff[$val['kodejabatan']][$val['tipekaryawan']] = $val['jlhkar'];
			$ttlkarynonstaff += $val['jlhkar'];
		} 
			
		## AMBIL KARYAWAN KHL
		$str = "select kodejabatan,tipekaryawan, count(karyawanid) as jlhkar  from ".$dbname.".datakaryawan where tipekaryawan = '4' and kodeorganisasi ='".$pt."' group by kodejabatan,tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $val){
			$karykhl[$val['kodejabatan']][$val['tipekaryawan']] = $val['jlhkar'];
			$ttlkarykhl += $val['jlhkar'];
		} 
			
		$str = "select * from ".$dbname.".sdm_5tipekaryawan where aktif = '1' and id in(0,1,4) order by id";
		$res = fetchdata($str);
		$cols=count($res)+2;
	

		$tab="";

 
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th> 
				<th rowspan='2'>".$_SESSION['lang']['uraian']."</th>
				<th colspan='".$cols."'>INTI</th>
				<th colspan='".$cols."'>PLASMA</th>
				<th colspan='".$cols."'>TOTAL</th>
				";
		$tab.="</tr>"; 
		$tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";

		for ($i=0; $i < 3; $i++) {  
			$tab.="<th>MGR</th>";
			foreach ($res as $key => $val) {
				$tab.="<th>".$val['tipe']."</th>";
			}
				$tab.="<th>JML</th>";
		}
		
				
		$tab.="</tr>";
 
		$tab.="</thead><tbody>";
 
		$tab.="<tr class='rowcontent'>";
			$tab.="<td><b> A.</b></td>";
			$tab.="<td><b>  MANAGER & STAFF</b></td>";
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
		$tab.="</tr>";

 			foreach($karymanager as $key => $kdjab){
				foreach ($kdjab as $key2 => $value) {
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td >".getNamaJabatan($key)."</td>";
					$tab.="<td align='right'>".$value."</td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$value."</b></td>";	
					 
				}


				$tab.="</tr>";
			}
 			foreach($karystaff as $key => $kdjab){
				foreach ($kdjab as $key2 => $value) {
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td >".getNamaJabatan($key)."</td>";
					$tab.="<td ></td>";
					$tab.="<td align='right'>".$value."</td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";   
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$value."</b></td>";	
					 
				}  
				$tab.="</tr>";
			}
				$ttlallstaff=$ttlkarymanager+$ttlkarystaff;
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td ><b>TOTAL MANAGER & STAFF</b></td>";
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlallstaff."</b></td>";	 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlallstaff."</b></td>";	 
					$tab.="</tr>";
			#########
		
				$tab.="<tr class='rowcontent'>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="</tr>";
			 
		$tab.="<tr class='rowcontent'>";
			$tab.="<td><b> B.</b></td>";
			$tab.="<td><b>  NON STAFF KEBUN</b></td>";
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
		$tab.="</tr>";

 			foreach($karynonstaff as $key => $kdjab){
				foreach ($kdjab as $key2 => $value) {
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td >".getNamaJabatan($key)."</td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td align='right'>".$value."</td>";	
					$tab.="<td ></td>"; 
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$value."</b></td>";	
					 
				}
				$tab.="</tr>";
			} 
			
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td ><b>TOTAL NON-STAFF</b></td>";
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	  
					$tab.="<td align='right'><b>".$ttlkarynonstaff." </b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff." </b></td>";	 
					$tab.="</tr>";
			#########
			#########
		
				$tab.="<tr class='rowcontent'>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="<td colspan='".$cols."'></td>";
		$tab.="</tr>";
			 
		$tab.="<tr class='rowcontent'>";
			$tab.="<td><b> C.</b></td>";
			$tab.="<td><b>  KHL </b></td>";
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
			$tab.="<td colspan='".$cols."'></td>"; 
		$tab.="</tr>";

 			foreach($karykhl as $key => $kdjab){
				foreach ($kdjab as $key2 => $value) {
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td >".getNamaJabatan($key)."</td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td align='right'>".$value."</td>";	
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td ></td>";
					$tab.="<td ></td>";
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$value."</b></td>";	
					$tab.="<td align='right'><b>".$value."</b></td>";	
					 
				}
				$tab.="</tr>";
			} 
			
					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td ><b>TOTAL KHL</b></td>";
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	  
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl." </b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl." </b></td>";	 
					$tab.="</tr>";

					$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td ><b>GRAND TOTAL</b></td>"; 
					
					$tab.="<td colspan='".$cols."'></td>"; 
					$tab.="<td colspan='".$cols."'></td>"; 
					$tab.="<td colspan='".$cols."'></td>"; 
					$tab.="</tr>";
					
					###grand total
					$tab.="<tr class='rowcontent'>";
					$tab.="<td >1.</td>";
					$tab.="<td ><b>MANAGER & STAFF</b></td>";
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlallstaff."</b></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlallstaff."</b></td>";  
					$tab.="</tr>";

					$tab.="<tr class='rowcontent'>";
					$tab.="<td >2.</td>";
					$tab.="<td ><b>NON STAFF</b></td>";
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	  
					$tab.="<td align='right'><b>".$ttlkarynonstaff." </b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff." </b></td>";	 
					$tab.="</tr>";

					$tab.="<tr class='rowcontent'>";
					$tab.="<td >3.</td>";
					$tab.="<td ><b>KHL</b></td>";
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b></b></td>";	  
					$tab.="<td align='right'><b></b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl." </b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl." </b></td>";	 
					$tab.="</tr>";

					#GRAND TOTAL SEMUA#
					$tab.="<tr class='rowcontent'>"; 
					$tab.="<td align='center' colspan='2'><b>GRAND TOTAL</b></td>";
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td align='right'><b>".($ttlkarymanager+$ttlkarystaff+$ttlkarynonstaff+$ttlkarykhl)."</b></td>";

					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>"; 
					$tab.="<td ></td>";   
					
					$tab.="<td align='right'><b>".$ttlkarymanager."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarystaff."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarynonstaff."</b></td>";	 
					$tab.="<td align='right'><b>".$ttlkarykhl."</b></td>";	 
					$tab.="<td align='right'><b>".($ttlkarymanager+$ttlkarystaff+$ttlkarynonstaff+$ttlkarykhl)."</b></td>";
					$tab.="</tr>";

		
		$tab.="</tbody></table>";
	




		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_LuasanArae_".$pt."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;
}


?>
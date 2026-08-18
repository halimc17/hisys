<?php
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
// require_once('lib/zDatatables.php');
?>
<script>
function exportTableToExcel(tableID, filename = ''){
	param  = "";
    param += "&tipe=excel";
	printnopopup("absensiowl.php" + "?" + param);
}

function printnopopup(url) {
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}

</script>
<style>

.button-14 {
  background-image: linear-gradient(#f7f8fa ,#e7e9ec);
  border-color: #adb1b8 #a2a6ac #8d9096;
  border-style: solid;
  border-width: 1px;
  border-radius: 3px;
  box-shadow: rgba(255,255,255,.6) 0 1px 0 inset;
  box-sizing: border-box;
  color: #0f1111;
  cursor: pointer;
  display: inline-block;
  font-family: "Amazon Ember",Arial,sans-serif;
  font-size: 14px;
  height: 29px;
  font-size: 13px;
  outline: 0;
  overflow: hidden;
  padding: 0 11px;
  text-align: center;
  text-decoration: none;
  text-overflow: ellipsis;
  user-select: none;
  -webkit-user-select: none;
  touch-action: manipulation;
  white-space: nowrap;
}

.button-14:active {
  border-bottom-color: #a2a6ac;
}

.button-14:active:hover {
  border-bottom-color: #a2a6ac;
}

.button-14:hover {
  border-color: #a2a6ac #979aa1 #82858a;
}

.button-14:focus {
  border-color: #e77600;
  box-shadow: rgba(228, 121, 17, .5) 0 0 3px 2px;
  outline: 0;
}
</style>
<?

$dbserver = 'localhost';
$dbport   = '3306';
$dbname   = 'jurnal';
$uname	  = 'root';
$passwd	  = 'root';

$dbserver = '182.23.67.40';
$dbport   = '3306';
$dbname   = 'officePDO';
$uname	  = 'office';
$passwd	  = '!0987654321';

try{
$owlPDO = new PDO('mysql:host='.$dbserver.';dbname='.$dbname, $uname, $passwd, array(PDO::ATTR_PERSISTENT => false));
$owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e) {
   print " Gagal, could not connect\n";	
   print "Error!: " . $e->getMessage() . "<br/>";
   die();
}

$param     = $_POST;
if(empty($param)){$param=$_GET;}

if($param['periode']==''){
	$tglawal =date("Y-m")."-01";
	$tglawal=tglkemarin(tglkemarin(date("Y-m-d")));	
	$tglakhir=date("Y-m-d");
	$param['periode']=date("Y-m");
}else{
	$tglawal =$param['periode']."-01";
	$tglakhir=tglakhir($tglawal);	
}
$tanggal=rangeTanggalarr($tglawal,$tglakhir);

$str = "select * from ".$dbname.".datakaryawan a
where (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."') order by namakaryawan";
$res = fetchData($str);
foreach($res as $bar){
	$nama[$bar['karyawanid']]=$bar['namakaryawan'];
	$tmk[$bar['karyawanid']]=$bar['tanggalmasuk'];
}
if($param['tipe']!='excel'){	
	$tab="<button onclick=\"exportTableToExcel('output')\" class=\"button-14\" >Excel</button>";
}
$tab.="<table id=output cellspacing=0 cellpadding=5 border=1 class=sortable>
		<thead>";
		$tab.="<tr class=rowheader>";
			$tab.="<th align=center rowspan=2>No</th>
				<th align=center rowspan=2>Nama</th>
				<th align=center rowspan=2>TMK</th>
				<th align=center colspan=".(count($tanggal)*2).">Tanggal</th>
				<th align=center colspan=5>Total</th>
			</tr>
			<tr class=rowheader>";
			foreach($tanggal as $tgl){
				$hari = date('D', strtotime($tgl));
				$tab.="<th align=center colspan=2>".substr($tgl,-2)."<br>".$hari."</th>";
			}
			$tab.="<th align=center>HKE</th>";			
			$tab.="<th align=center>LIBUR</th>";			
			$tab.="<th align=center>HADIR</th>";			
			$tab.="<th align=center>TELAT</th>";			
			$tab.="<th align=center>TDK-ABS</th>";	
		
		// $tab.="<th align=center>No</th>";
		// $tab.="<th align=center>Nama</th>";
		// foreach($tanggal as $tgl){
			// $hari = date('D', strtotime($tgl));
			// $tab.="<th align=center>".substr($tgl,-2)."</th>";
			// $tab.="<th align=center>".$hari."</th>";
		// }
		// $tab.="<th align=center>HKE</th>";			
		// $tab.="<th align=center>LIBUR</th>";			
		// $tab.="<th align=center>HADIR</th>";			
		// $tab.="<th align=center>TELAT</th>";			
		// $tab.="<th align=center>TDK-ABS</th>";	
		$tab.="</tr>
		</thead>
	<tbody>";
	
	$telat=$hadir=$harikerja=[];
	foreach($nama as $nik => $nmkary){
		$no++;
		$tab.="<tr class=rowconten>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=left nowrap>".$nmkary."</td>";
		$tab.="<td align=left nowrap>".$tmk[$nik]."</td>";
		foreach($tanggal as $tgl){				
			$str = "select max(scan_date) as keluar, min(scan_date) as masuk from ".$dbname.".att_log where scan_date like '".$tgl."%' and pin ='".$nik."'";
			$res = fetchData($str);
			$keluar=$masuk="";
			foreach($res as $bar){
				if(substr($bar['masuk'],-8)<="12:00:00"){
					$masuk=substr($bar['masuk'],-8);
				}
				if(substr($bar['keluar'],-8)>"12:00:00"){					
					$keluar=substr($bar['keluar'],-8);
				}
			}
			$warna=$warnak=""; 
			if($masuk>='09:16:00'){
				$warna="style=color:red;font-weight:bold; title='Terlambat !'";
				$telat[$nik]+=1;
			}
			
			if($keluar<'16:00:00'){
				$warnak="style=color:red;font-weight:bold; title='Pulang Cepat !'";
			}
			if($masuk!=''){
				$hadir[$nik]+=1;
			}
			
			if($masuk=='' and $keluar!=''){
				$warna="style=background-color:yellow; title='Tidak Absen Masuk !'";
			}
			if($masuk!='' and $keluar==''){
				$warnak="style=background-color:orange; title='Tidak Absen Pulang !'";
			}
			$hari = date('D', strtotime($tgl));
			if($hari!='Sat' and $hari!='Sun'){
				$harikerja[$nik]+=1;
				if($masuk=='' and $keluar==''){
					$warna="style=background-color:#d366fa; title='Tidak Absen !'";
					$warnak="style=background-color:#d366fa; title='Tidak Absen !'";
					$masuk="?";
					$keluar="?";
				}
			}else{
				$harilibur[$nik]+=1;
			}
			
			
			$tab.="<td align=center ".$warna.">".$masuk."</td>";
			$tab.="<td align=center ".$warnak.">".$keluar."</td>";
		}
		$tab.="<td align=center>".$harikerja[$nik]."</td>";
		$tab.="<td align=center>".$harilibur[$nik]."</td>";
		$tab.="<td align=center>".$hadir[$nik]."</td>";
		$tab.="<td align=center>".$telat[$nik]."</td>";
		$mangkir[$nik]=$harikerja[$nik]-$hadir[$nik];
		$tab.="<td align=center>".$mangkir[$nik]."</td>";
		
		
		$tab.="</tr>";
	}
	
	$tab.="</tbody>";
	$tab.="</table>";
	if($param['tipe']=='excel'){
		if(strlen($tab)>0){
			$nop_="absen";
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$tab)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}
	}else{			
		echo $tab;
	}
?>
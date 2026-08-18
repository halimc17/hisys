<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$proses        =checkPostGet('proses','');
$unit          =checkPostGet('unitlist','');
$afd           =checkPostGet('afdlist','');
$jabatan       =checkPostGet('jabatanlist','');
$prd           =checkPostGet('prdlist','');
$namakarylist  =checkPostGet('namakarylist','');
$kontananlist  =checkPostGet('kontananlist','');
$tglmulailist  = tanggalsystemn(checkPostGet('tglmulailist',''));
$tglselesailist= tanggalsystemn(checkPostGet('tglselesailist',''));


// if($unit=='' || $prd==''){
	// exit("Warning : Lengkapi Filter Pengisian");
// }

if($_SESSION['empl']['subbagian']!='' and $afd==''){
	#exit("Warning : Filter Divisi Wajib di Isi");
}

$stream='';	
$arrJab=array(
	"mandorpnn"   =>"Mandor Panen",
	"kranipnn"    =>"Kerani Panen",
	"mandor1pnn"  =>"Mandor 1 Panen",
	"mandortus"   =>"Mandor TUS",
	"mandortup"   =>"Mandor TUP",
	"kranikirim"  =>"Kerani Kirim",
	"mandortraksi"=>"Mandor Traksi",
	"mandor1tus"  =>"Mandor 1 TUS",
	"mandor1tup"  =>"Mandor 1 TUP"
);

$arrJab=getEnum($dbname,'kebun_premikemandoran','jabatan');

$wh = "";
if ($_SESSION['empl']['subbagian']!=''){
	$wh.=" and divisi like '".$_SESSION['empl']['subbagian']."%'";
}
$where.= "and kodeorg in (".getOrgDetail(2).")";
if($jabatan!=''){
	$where.=" and jabatan='".$jabatan."'";
}
if($prd!=''){
	$where.=" and periode = '".$prd."'";
}
if($unit!=''){
	$where.=" and kodeorg='".$unit."'";
}
if($param['tahaplist']!=''){
	$where.=" and tahap='".$param['tahaplist']."'";
}
if($namakarylist!=''){
	$wh.=" and namakaryawan like '%".$namakarylist."%'";
}
if($afd!=''){
	$wh.=" and divisi like '%".$afd."%'";
}


$limit = 15;
$page = 0;
$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
if (isset($_POST['page'])) {
	$page = intval($_POST['page']);
	if ($page < 0)
		$page = 0;
}

$offset = $page * $limit;
$maxdisplay = ($page * $limit);
$nokar = 0;
$nokar = $maxdisplay;

$sql="select count(distinct(kodeorg)) from ".$dbname.".kebun_premikemandoran where 1=1 ".$where." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where 1=1 ".$wh." order by subbagian asc ) group by karyawanid,periode,tahap,kontanan,jabatan order by periode desc, jabatan asc";
$res = fetchData($sql);
$jlhbrs = count($res);
if($jlhbrs==0){
	$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=15 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
	$stream.="</tr>";
}		

$jumlahkaryhist=0;
$str = "select count(karyawanid) as jlh from ".$dbname.".datakaryawan_hist where 5=5 and version_type='B' and lokasitugas='".$unit."' and periodegaji='".$prd."' "; 
$res = fetchdata($str);
$jumlahkaryhist=$res[0]['jlh'];

$opttahap=array('1'=>'Pertama','2'=>'Kedua');
$optjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)<=6");

# Cek Datakaryawan History
if($jumlahkaryhist > 0) {
	$str="select idmandor,divisi,tahap,posting,kodeorg,karyawanid,periode,kontanan,jabatan, sum(premiinput) as premiinput,sum(premisumber) as premisumber, sum(premikomputer) as premikomputer, sum(denda) as denda from ".$dbname.".kebun_premikemandoran where 1=1 ".$where." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan_hist where 1=1 ".$wh." and version_type='B' order by subbagian asc ) group by karyawanid,periode,tahap,kontanan,jabatan order by periode desc,divisi asc, jabatan asc, karyawanid asc, tanggal desc limit " . $offset . "," . $limit . "";
} else {
	$str="select idmandor,divisi,tahap,posting,kodeorg,karyawanid,periode,kontanan,jabatan, sum(premiinput) as premiinput,sum(premisumber) as premisumber, sum(premikomputer) as premikomputer, sum(denda) as denda from ".$dbname.".kebun_premikemandoran where 1=1 ".$where." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where 1=1 ".$wh." order by subbagian asc ) group by karyawanid,periode,tahap,kontanan,jabatan order by periode desc,divisi asc, jabatan asc, karyawanid asc, tanggal desc limit " . $offset . "," . $limit . "";
}
	
	
$res=fetchdata($str);
foreach($res as $bar){	
	$nokar++;
	$clr="";
	if($bar['tahap']=='1'){
		$clr="style=background-color:#FFF9F0;";
	}
	$stream.="<tr class=rowcontent>";

	if($jumlahkaryhist > 0) {
		$nmkar    =makeOption($dbname,'datakaryawan_hist','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
		$nikkar   =makeOption($dbname,'datakaryawan_hist','karyawanid,nik',"karyawanid='".$bar['karyawanid']."'");
		$optdivisi=makeOption($dbname,'datakaryawan_hist','karyawanid,subbagian',"karyawanid='".$bar['karyawanid']."'");
		$optunit  =makeOption($dbname,'datakaryawan_hist','karyawanid,lokasitugas',"karyawanid='".$bar['karyawanid']."'");
		$jabkar   =makeOption($dbname,'datakaryawan_hist','karyawanid,kodejabatan',"karyawanid='".$bar['karyawanid']."'");
	} else {
		$nmkar    =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
		$nikkar   =makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$bar['karyawanid']."'");
		$optdivisi=makeOption($dbname,'datakaryawan','karyawanid,subbagian',"karyawanid='".$bar['karyawanid']."'");
		$optunit  =makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$bar['karyawanid']."'");
		$jabkar   =makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$bar['karyawanid']."'");
	}

	$prdgj    =makeOption($dbname,'sdm_5periodegaji','kodeorg,sudahproses',"periode = '".$bar['periode']."' and kodeorg='".$bar['kodeorg']."' and jenisgaji!='S'");


		$stream.="<td align=center>".$nokar."</td>";
		$stream.="<td>".$bar['kodeorg']." - ".$optorg[$bar['kodeorg']]."</td>";					
		$stream.="<td>".$bar['divisi']." - ".$optorg[$bar['divisi']]."</td>";					
		$stream.="<td align=center>".$bar['periode']."</td>";

		$tglawal = tanggalnormal($bar['periode']."-01");
		$tglakhir= tanggalnormal(tglakhir($bar['periode']."-01"));
		
		$stream.="<td align=center ".$clr.">".$tglawal." - ".$tglakhir."</td>";
		#$stream.="<td>".$bar['kontanan']."</td>";
		$stream.="<td>".$nikkar[$bar['karyawanid']]."</td>";
		$stream.="<td>".$nmkar[$bar['karyawanid']]."</td>";
		$stream.="<td>".$optjabatan[$jabkar[$bar['karyawanid']]]."</td>";
		$stream.="<td>".$arrJab[$bar['jabatan']]."</td>";
		#$stream.="<td align=right>".number_format($bar['premisumber'])."</td>";
		$stream.="<td align=right>".number_format($bar['premisumber'])."</td>";
		$stream.="<td align=right>".number_format($bar['denda'])."</td>";
		$stream.="<td align=right>".number_format($bar['premiinput'])."</td>";
		
	if ($proses == 'excel') {
		$stream.="<td width=20px></td>";
	} else 	{
		if($prdgj[$bar['kodeorg']]=='0'){
			$stream.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['kodeorg']."','".$bar['divisi']."','".$bar['periode']."','".$bar['tahap']."','".($tglawal)."','".($tglakhir)."','".$bar['jabatan']."');\" ></td>";
			
			$stream.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['periode']."','".$bar['karyawanid']."','".$bar['jabatan']."','".$bar['kodeorg']."','".$tglawal."','".$tglakhir."','".$bar['tahap']."');\" ></td>";
		}else{
			$stream.="<td width=20px></td>";
			$stream.="<td width=20px></td>";
		}
		$stream.="<td align=center width=20px>
			<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"previewdetail('".$bar['periode']."','".$bar['karyawanid']."','".$bar['jabatan']."','".$bar['kodeorg']."','".$tglawal."','".$tglakhir."','".$bar['tahap']."','".$bar['kontanan']."');\" ></td>";
	}
	$stream.="</tr>";
}

$totrows = ceil($jlhbrs / $limit);
if ($totrows == 0) {
	$totrows = 1;
}
$isiRow = '';
for ($er = 1; $er <= $totrows; $er++) {
	$sel = ($page == $er - 1) ? 'selected' : '';
	$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
}
$stream.="</tr><tr><td colspan=15 align=center>";
if ($page == '0') {
	$stream.="<button class=mybutton disabled=true>Prev</button>";
} else {
	$stream.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
}
$stream.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

if (($page + 1) == $totrows) {
	$stream.="<button class=mybutton disabled=true>Next</button>";
} else {
	$stream.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
}
$stream.="</td>
	</tr>";

switch($proses){    
	case'preview':
         echo $stream;
	break;
	
    
    ######EXCEL	
	case 'excel':
		//$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
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
		break;
                
	default:
}



?>
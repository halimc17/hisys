<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unitlist','');
$afd=checkPostGet('afdlist','');
$jabatan=checkPostGet('jabatanlist','');
$prd=checkPostGet('prdlist','');


$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$optjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$optdivisi=makeOption($dbname,'datakaryawan','karyawanid,subbagian');
$optunit=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');
$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jabkar=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan');


// if($unit=='' || $prd==''){
	// exit("Warning : Lengkapi Filter Pengisian");
// }

if($_SESSION['empl']['subbagian']!='' and $afd==''){
	exit("Warning : Filter Divisi Wajib di Isi");
}

$stream='';


if ($proses == 'excel') {
	$stream.="<table class=sortable cellspacing=1 border=1>";
} else 	{
	$stream.="<table class=sortable cellspacing=1>";
}

	$stream.="<thead>";
	$stream.="<tr class=rowheader>";
	$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['divisi']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['periode']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['tahap']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['nik2']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['jabatan']." ".$_SESSION['lang']['karyawan']."</td>";
		$stream.="<td align=center>".$_SESSION['lang']['jenispremi']."</td>";
		// $stream.="<td align=center>Kerja / Kontanan</td>";
		// $stream.="<td align=center>Tanggal</td>";
		// $stream.="<td align=center>Total Premi<br>(Kary / Mdr)</td>";
		$stream.="<td align=center>Premi Kotor</td>";
		$stream.="<td align=center>Denda</td>";
		$stream.="<td align=center>Premi Bersih</td>";
		$stream.="<td align=center>Action</td>";
	$stream.="</tr>";
	$stream.="</thead>";

	
$arrJab=array("MANDORPANEN"=>"Mandor Panen","MANDOR1"=>"Mandor 1","KERANIPANEN"=>"Kerani Panen","MANDORTRAKSI"=>"Mandor Traksi");

if($jabatan!=''){
	$where.=" and jabatan='".$jabatan."'";
}
if($prd!=''){
	$where.=" and periode = '".$prd."'";
}
if($unit!=''){
	$where.=" and kodeorg='".$unit."'";
}

$limit = 20;
$page = 0;
$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
if (isset($_POST['page'])) {
	$page = floatval($_POST['page']);
	if ($page < 0)
		$page = 0;
}

$offset = floatval($page) * $limit;
$maxdisplay = ($page * $limit);
$nokar = 0;
$nokar = $maxdisplay;

$sql="select * from ".$dbname.".kebun_premikemandoran where 1=1 
			  ".$where." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and subbagian like '%".$afd."%' order by subbagian asc ) order by periode desc, jabatan asc";
$res = fetchData($sql);
$jlhbrs = count($res);
if($jlhbrs==0){
	$stream.="<tr class=rowcontent>";
	$stream.="<td colspan=12 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
	$stream.="</tr>";
}		
$opttahap=array('1'=>'Pertama','2'=>'Kedua');


$str="select * from ".$dbname.".kebun_premikemandoran where 1=1 
			  ".$where." and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas like '".$_SESSION['empl']['lokasitugas']."%' and subbagian like '%".$afd."%'  order by subbagian asc ) order by periode desc, jabatan asc limit " . $offset . "," . $limit . "";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){	
	$stream.="<tr class=rowcontent>";
	@$nokar+=1;
		$stream.="<td align=center>".$nokar."</td>";
		if($optdivisi[$bar['karyawanid']]==''){
			$stream.="<td>".$optunit[$bar['karyawanid']]." - ".$optorg[$optunit[$bar['karyawanid']]]."</td>";
		}else{
			$stream.="<td>".$optdivisi[$bar['karyawanid']]." - ".$optorg[$optdivisi[$bar['karyawanid']]]."</td>";			
		}
		
		$stream.="<td>".$bar['periode']."</td>";
		$stream.="<td>".$opttahap[$bar['tahap']]."</td>";
		$stream.="<td>".$nikkar[$bar['karyawanid']]."</td>";
		$stream.="<td>".$nmkar[$bar['karyawanid']]."</td>";
		$stream.="<td>".$optjabatan[$jabkar[$bar['karyawanid']]]."</td>";
		$stream.="<td>".$arrJab[$bar['jabatan']]."</td>";
		
		// $stream.="<td>".$bar['kontanan']."</td>";
		// if($bar['tanggalkontanan']=='0000-00-00'){
			// $stream.="<td></td>";
		// }else{
			// $stream.="<td>".$bar['tanggalkontanan']."</td>";
		// }
		// $stream.="<td align=right>".number_format($bar['premisumber'])."</td>";
		$stream.="<td align=right>".number_format($bar['premikomputer'])."</td>";
		$stream.="<td align=right>".number_format($bar['denda'])."</td>";
		$stream.="<td align=right>".number_format($bar['premiinput'])."</td>";
		
	if ($proses == 'excel') {
		$stream.="<td></td>";
	} else 	{
		if($bar['posting']=='0')
		{
		$stream.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete'
                    onclick=\"del('".$bar['periode']."','".$bar['karyawanid']."','".$bar['jabatan']."','".$bar['kodeorg']."','".$bar['tanggalkontanan']."');\" ></td>";
		}
		else
		{
			
		$stream.="<td></td>";
		}
	}
	
	$stream.="</tr>";
	@$tpremisumber+=$bar['premisumber'];
	@$tpremihitung+=$bar['premikomputer'];
	@$tdenda+=$bar['denda'];
	@$tpremidapat+=$bar['premiinput'];
}
$stream.="<tr class=rowcontent>";
		$stream.="<td colspan=8 align=center><b>T O T A L</b></td>";
		// $stream.="<td align=right><b>".number_format($tpremisumber)."</b></td>";
		$stream.="<td align=right><b>".number_format($tpremihitung)."</b></td>";
		$stream.="<td align=right><b>".number_format($tdenda)."</b></td>";
		$stream.="<td align=right><b>".number_format($tpremidapat)."</b></td>";
		$stream.="<td align=right></td>";
	$stream.="</tr>";

$totrows = ceil($jlhbrs / $limit);
if ($totrows == 0) {
	$totrows = 1;
}
$isiRow = '';
for ($er = 1; $er <= $totrows; $er++) {
	$sel = ($page == $er - 1) ? 'selected' : '';
	$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
}
$stream.="</tr>
			 <tr><td colspan=12 align=center>";

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
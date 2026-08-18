<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zPivot.php');
require_once('lib/zSelect2Lite.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_pivot').'</span><br>');
?>
<script language=javascript src='js/sdm_pivot.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/report_pivottable.js?v=<?php echo time(); ?>'></script>

<script type='text/javascript' src='DataTables/js/jquery.dataTables.min.js'></script>
<link rel='stylesheet' type='text/css' href='DataTables/css/jquery.dataTables.min.css'>

<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>

<?
// <style>
	// /* styles for responsive pivot UI */
	// table.pvtUi {
		// table-layout: fixed;
		// width: 100%;
	// }

	// table.pvtUi > tbody > tr:first-child > td:first-child {
		// width: 200px;
	// }
	// table.pvtUi > tbody > tr:first-child > td:nth-child(2) {
		// width: 210px;
	// }

	// .pvtTableRendererHolder {
		// max-height: 900px;
	// }

	// .pvtRendererArea > div {
		// overflow: auto;
	// }
// </style>

$str="select DISTINCT periode as prd from ".$dbname.".sdm_5periodegaji order by periode desc";
$res = fetchData($str);
foreach($res as $key => $val){
	$data[substr($val['prd'],0,4)][$val['prd']]=$val['prd'];
}
$no=0;
$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($data as $thn => $vprd){
	$d=$thn;
	if($d!=$n){			
		$optprd.="<optgroup label='".$d."'>";
	}
	foreach($vprd as $prd){
		$optprd.="<option value=".$prd.">".$prd."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optprd.="</optgroup>";
	}
}
$optorg="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}


$stat=array('0'=>'NON AKTIF','1'=>'AKTIF');
$wh="";
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
	$wh="and id in ('4')";
}
$opttipekary="<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "select * from ".$dbname.".sdm_5tipekaryawan where 1=1 ".$wh." order by aktif desc, no asc";
$res = fetchData($str);
foreach($res as $bar){
	$d=$bar['aktif'];
	if($d!=$n){			
		$opttipekary.="<optgroup label='".$stat[$d]."'>";
	}
    $opttipekary.="<option value='".$bar['id']."'>".$bar['tipe']."</option>";
	$n=$d;
	if($d!=$n){			
		$opttipekary.="</optgroup>";
	}
}
$arrtipe=array(
	'aktual'=>'Detail Gaji (Aktual)',
	'payroll'=>'Detail Gaji (Payroll)',
	'detpayroll'=>'Detail Absensi (Aktual)',
	'source'=>'Sumber Transaksi Gaji (Aktual)',
	'alokasi'=>'Daftar Alokasi Gaji (Aktual)',
	'hkdankehadiran'=>'Daftar HK dan Kehadiran (Aktual)'
);
$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrtipe as $key => $val){
	$opttipe.="<option value=".$key.">".$val."</option>";			
}


$path   = $_SERVER['SCRIPT_NAME'];
$path   = explode('/',$path);
$rowfile= count($path)-1;
$file   = $path[$rowfile];
$file   = str_replace(".php","",$file);
$idmenu = makeOption($dbname,'menu','action,id');


$optlap="<option value=''>&nbsp;</option>";
$str = "select a.*, b.karyawanid, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' order by b.karyawanid ,a.id asc";
$res = fetchdata($str);
$fav = count($res);
foreach ($res as $bar){
	$d=$bar['karyawanid'];
	if($d!=$n){			
		$optlap.="<optgroup label='".getNamaKaryawan($d)."'>";
	}
	$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	$n=$d;
	if($d!=$n){			
		$optlap.="</optgroup>";
	}
}

$wh=" and lokasitugas in (".getOrgDetail(2).")";	
if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
	$wh.=" and tipekaryawan='4'";
}
$wh.=" and (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."')";

$optkary="<option value=''>&nbsp;</option>";
$str = "select * from ".$dbname.".datakaryawan where 1=1 ".$wh." order by lokasitugas, namakaryawan";
$res = fetchdata($str);
foreach ($res as $val) {
	$d=$val['lokasitugas'];
	if($d!=$n){			
		$optkary.="<optgroup label='".getNamaOrg($d)."'>";
	}
	$optkary.="<option value=".$val['karyawanid'].">".$val['nik']." - ".$val['namakaryawan']."</option>";
	$n=$d;
	if($d!=$n){			
		$optkary.="</optgroup>";
	}
}

if($fav==0){
	$st="style=display:none";
}
echo"<table border=0 id=tableheader><td style=vertical-align:top>";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
				<tr ".$st.">
					<td>Tampilkan dari</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=fromfavorit onchange=getfromfav(); style=\"width:200px;\">".$optlap."</select></td>
                </tr>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td colspan=3><select class='select2' onchange=getkary(); id=gudang style='width:200px;'>".$optorg."</select></td><td></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' onchange=getkary(); id=tipekaryawan style=\"width:200px;\">".$opttipekary."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['periode'] . "</td> 
					<td>:</td>
					<td><select id=periode class='select2' onchange=getper2(this.value) style=\"width:88px;\">".$optprd."</select></td>
					<td>s/d</td>
					<td><select id=periode2 class='select2' style=\"width:88px;\">".$optprd."</select></td>
                </tr>
				<tr>
					<td>" . $_SESSION['lang']['jenis'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=tipe onchange=sumber(this.value); style=\"width:200px;\">".$opttipe."</select></td>
                </tr>
                <tr id=rowkary style=display:none>
					<td>" . $_SESSION['lang']['namakaryawan'] . "</td> 
					<td>:</td>
					<td colspan=3><select class='select2' id=karyawanid style=\"width:200px;\">".$optkary."</select></td>
                </tr>
				<tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=pivot(); class=mybutton>Pivot</button>
					<a href='fileupload/Panduan_Pivot_Table.doc' download><button class=mybutton>Help</button></a>
					<button hidden onclick=\"data()\" class=mybutton>Data</button>
                    </td>
                </tr>
            </table>
</fieldset>";
echo"</td><td style=vertical-align:top>";
echo"<div id=info></div>";
echo"</td></table>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"dt-button buttons-print\" id=showhead>Show</button>
			<button onclick=\"exportTableToExcel('pvtTable')\" class=\"dt-button buttons-print\" >Excel</button>
			<button onclick='ExportPdf()' class=\"dt-button buttons-print\">PDF</button>
			<button id=download onclick='pdftosave()' class=\"dt-button buttons-print\">Send</button>
			<div hidden id=namamenu>".getMenu('sdm_pivot','path')."</div>
			<button id=hidetotal onclick=\"hidetotal('r',1)\" class=\"dt-button buttons-print\">Hide Total Row</button>
			<button id=hidetotalcol onclick=\"hidetotal('c',1)\" class=\"dt-button buttons-print\">Hide Total Col</button>
			<button id=formfav onclick=\"formfav()\" class=\"dt-button buttons-print\">Add Favorit</button>
			
			<input hidden id=temphidetotal value='1'>
			<input hidden id=temphidetotalcol value='1'>
		</td><td>			
			<fieldset >
					<label>Show Favorit</label>
					<select id=optfavorit style=\"width:150px;\"></select>
			</fieldset>
		</td></tr>
	</table>
	</div>";
	
echo"<div id='output' style=min-height:400px></div>";
CLOSE_BOX();
echo close_body();
?>
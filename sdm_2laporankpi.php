<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporankpi').'</span><br>');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/sdm_2laporankpi.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['all']."</option>";
$tahun="<option value=''>".$_SESSION['lang']['all']."</option>";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
	$wh = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
    $wh = " and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
    $wh = " and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'";
}



$str = "select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah='' and kodeorganisasi in (".getOrgDetail(4).") ".$wh."";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select * from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and kodeorganisasi in (".getOrgDetail(2).") ".$where." order by tipe";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select distinct(tahun) as tahun from ".$dbname.".sdm_kpi order by tahun desc limit 25";
$res = fetchdata($str);
foreach($res as $bar){
    $tahun.="<option value=" . $bar['tahun'] . ">" . $bar['tahun'] . "</option>";
}

$optnilai = "<option value=''>".$_SESSION['lang']['all']."</option>";
$optnilai.="<option value='Q1'>Q1 - Quartal Pertama</option>";
$optnilai.="<option value='Q2'>Q2 - Quartal Kedua</option>";
$optnilai.="<option value='Q3'>Q3 - Quartal Ketiga</option>";
$optnilai.="<option value='Q4'>Q4 - Quartal Keempat</option>";

$optdata = "<option value=''>&nbsp;</option>";
$optdata.="<option value='notinput'>Belum diinput</option>";
$optdata.="<option value='input'>Sudah diinput</option>";
$optdata.="<option value='notpost'>Belum diposting</option>";
$optdata.="<option value='post'>Sudah diposting</option>";

$optmm = "<option value=''>&nbsp;</option>";
$optmm.="<option value='Y'>YA</option>";
$optmm.="<option value='N'>TIDAK</option>";

$optgol = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5golongan where aktif='1' ORDER BY namagolongan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$nmgol[$val['kodegolongan']]=$val['namagolongan'];
	$optgol .= "<option value='".$val['kodegolongan']."'>".$val['namagolongan']."</option>";
}

$optdept = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $val){
	$optdept .= "<option value='".$val['kode']."'>".$val['nama']."</option>";
}

$optjab = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5jabatan";
$res = fetchdata($str);
foreach($res as $val){
	$optjab .= "<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";
}

$tipekaryawan = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5tipekaryawan where id in ('0','1') and aktif=1";
$res = fetchdata($str);
foreach($res as $val){
	$tipekaryawan .= "<option value='".$val['id']."'>".$val['tipe']."</option>";
}


echo"<fieldset style='float:left;' id=tableheader>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=pt onchange=getkodeorg()  style=\"max-width:150px;\">" .$optPT . "</select></td>
               
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kodeorg style=\"max-width:150px;\">" . $optorg . "</select></td>
					
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td>:</td>
					<td><input class=myinputtext id=karyawanid style='max-width:150px;' onkeypress='enterkey(event,loaddata)'></td>
					
					<td>".$_SESSION['lang']['departemen']."</td>
					<td>:</td>
					<td><select id='departemen' class='select2' style='max-width:150px;'>".$optdept."</select></td>
					
                </tr>
                <tr>
					<td>".$_SESSION['lang']['kodegolongan']."</td>
					<td>:</td>
					<td><select id='kodegolongan' class='select2' style='max-width:150px;'>".$optgol."</select></td>
					
					<td>".$_SESSION['lang']['jabatan']."</td>
					<td>:</td>
					<td><select id='jabatan' class='select2' style='max-width:150px;'>".$optjab."</select></td>
					
					<td>Quartal</td>
                    <td>:</td>
                    <td><select id='penilaian' class='select2' style='max-width:150px;'>".$optnilai."</select></td>
					
					<td>" . $_SESSION['lang']['tahun'] . "</td>
                    <td>:</td>
                    <td><select id='tahun' class='select2' style='max-width:150px;'>".$tahun."</select></td>
                </tr>
                <tr>
					<td>KPI</td>
                    <td>:</td>
                    <td><select id='kpi' class='select2' style='max-width:150px;'>".$optdata."</select></td>
					
					<td>Core Value</td>
                    <td>:</td>
                    <td><select id='cv' class='select2' style='max-width:150px;'>".$optdata."</select></td>
					
					<td>Man Management</td>
                    <td>:</td>
                    <td><select id='mm' class='select2' style='max-width:150px;'>".$optdata."</select></td>
					
					<td>PAS</td>
                    <td>:</td>
                    <td><select id='pas' class='select2' style='max-width:150px;'>".$optdata."</select></td>
				</tr>
                <tr>	
					<td>Status MM</td>
                    <td>:</td>
                    <td><select id='statusmm' class='select2' style='max-width:150px;'>".$optmm."</select></td>
					
					<td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
                    <td>:</td>
                    <td><select id='tipekaryawan' class='select2' style='max-width:150px;'>".$tipekaryawan."</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=preview(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=excel(event,'kebun_slave_2analisabyytm.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' class='table-scroll' style=height:73vh></div>";

CLOSE_BOX();
echo close_body();
?>
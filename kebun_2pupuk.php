<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('kebun_2pupuk').'</span><br>');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>
function getdetail(kdblok,kdbrg,bln){
	
	param = 'tampil=getdetail';
	param += '&kdblok=' + kdblok;
	param += '&kdbrg=' + kdbrg;
	param += '&bln=' + bln;
	
	tujuan = 'kebun_slave_2pupuk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','70%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

</script>

<?
$optorg=$optper='';
$optorg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optPT="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optDiv="<option value=''>".$_SESSION['lang']['all']."</option>";
$pupuk="<option value=''>".$_SESSION['lang']['all']."</option>";
$optTt="<option value=''>".$_SESSION['lang']['all']."</option>";

$str="select * from ".$dbname.".organisasi where tipe='PT' and kodesejarah=''";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['kodeorganisasi']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optPT.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}

$str="select * from ".$dbname.".organisasi where tipe='KEBUN' ".$where."";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select * from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$_SESSION['empl']['lokasitugas']."'";
$res = fetchdata($str);
foreach($res as $bar){
	$s="";
	if($_SESSION['empl']['subbagian']==$bar['kodeorganisasi']){
		$s="selected";
	}
    $optDiv.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str="select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' order by tahuntanam asc";
$res = fetchdata($str);
foreach($res as $bar){
    $optTt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 25";
$res = fetchdata($str);
foreach($res as $bar){
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

$arrIP=array("renc"=>"Rekomendasi","real"=>"Realisasi");
#$tampil="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrIP as $res => $bar){
	$tampil.="<option value=".$res.">".$bar."</option>";
}

$str="select distinct(statusblok) as statusblok from ".$dbname.".setup_blok order by statusblok desc";
$res = fetchdata($str);
$optjns="<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($res as $bar){
    $optjns.="<option value=" . $bar['statusblok'] . ">" . $bar['statusblok'] . "</option>";
}

$sql = "SELECT * FROM " . $dbname . ".log_5masterbarang where kelompokbarang='311' and inactive='0'";
$res = fetchdata($sql);
foreach($res as $bar){
    $pupuk.="<option value=" . $bar['kodebarang'] . " ".$s.">" . $bar['kodebarang'] . " - " . $bar['namabarang'] . "</option>";
}

$str="select distinct substr(periodepemupukan,1,4) as tahun from ".$dbname.".kebun_rekomendasipupuk order by tahun desc";
$res = fetchdata($str);
foreach($res as $bar){
	$datath[$bar['tahun']]=$bar['tahun'];
}
$str="select distinct substr(notransaksi,1,4) as tahun from ".$dbname.".kebun_pakaimaterial order by tahun desc";
$res = fetchdata($str);
foreach($res as $bar){
	$datath[$bar['tahun']]=$bar['tahun'];
}

$tahun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($datath as $var => $bar){
    $tahun.="<option value=" . $bar. ">" . $bar. "</option>";
}

$arr1 = "##pt##kdorg##divisi##tt##status##pupuk##tahun##tampil";
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr >
                    <td>" . $_SESSION['lang']['pt'] . "</td>
                    <td>:</td>
                    <td><select id=pt onchange=getUnitThnTnm(this,'kdorg,tt','divisi','".$_SESSION['lang']['all']."')  style=\"width:164px;\">" .$optPT . "</select></td>
                
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select onchange=getAfdThnTnm(this,'divisi,tt','".$_SESSION['lang']['all']."') id=kdorg style=\"width:164px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select id=divisi onchange=getThnTnm(this,'tt','".$_SESSION['lang']['all']."') style=\"width:164px;\">" . $optDiv . "</select></td>
                
                    <td>" . $_SESSION['lang']['tahuntanam'] . "</td>
                    <td>:</td>
                    <td><select id=tt style=\"width:164px;\">" . $optTt . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['status'] . "</td>
                    <td>:</td>
                    <td><select id=status style=\"width:164px;\">" . $optjns . "</select></td>
               
                    <td>" . $_SESSION['lang']['jenisPupuk'] . "</td>
                    <td>:</td>
                    <td><select id=pupuk style=\"width:164px;\">" . $pupuk . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['tahun'] . "</td>
                    <td>:</td>
                    <td><select id=tahun style=\"width:164px;\">" . $tahun . "</select></td>
               
                    <td>" . $_SESSION['lang']['tampilkan'] . "</td>
                    <td>:</td>
                    <td><select id=tampil style=\"width:164px;\">" . $tampil . "</select></td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td colspan=4>
                    <button onclick=zPreview('kebun_slave_2pupuk','" . $arr1 . "','printContainer');showdetail(); class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'kebun_slave_2pupuk.php','" . $arr1 . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
                
				
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='printContainer' style='min-height:380px;' class='table-scroll'></div>";

echo"<div id='getdetail' style=display:none></div>";
CLOSE_BOX();
echo close_body();
?>
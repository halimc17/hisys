<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');


$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
        $arr="##kdOrg##periode##afdId##premi##premibasis##premitetap##upah##upahpenalty##hk##dendahk##dendarp";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','PABRIK','KANWIL','TRAKSI') order by namaorganisasi asc ";	
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
        $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getSub()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
        $arr="##kdOrg##periode##afdId##premi##premibasis##premitetap##upah##upahpenalty##hk##dendahk##dendarp";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' or tipe in ('KEBUN','PABRIK','KANWIL') order by kodeorganisasi asc";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji order by periode desc";
        $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\" onchange='getSub()'><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}
else
{
        $arr="##kdOrg##periode##premi##premibasis##premitetap##upah##upahpenalty##hk##dendahk##dendarp";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
	$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
        $optOrg="<select id=\"kdOrg\" name=\"kdOrg\" style=\"width:150px\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
}
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
$optPeriode="";
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}

$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
function getSub() {
	afd = document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	param = 'kdOrg=' + afd + '&proses=getSubUnit';
	tujuan = 'sdm_slave_2laporanPremiPerHari.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					//                                                    alert(con.responseText);
					document.getElementById('afdId').innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showpopup(karyawanid, tanggal, ev) {
	param = 'karyawanid=' + karyawanid + '&tanggal=' + tanggal;
	tujuan = 'sdm_slave_2laporanPremiPerHari_showpopup.php' + "?" + param;
	width = '450';
	height = '150';

	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1('No Transaksi Premi ' + karyawanid + ' ' + tanggal, content, width, height, ev);

}

function cekcek(apa) {
	if (apa.checked)
		apa.value = "1";
	else
		apa.value = "0";
}

function Clear1() {
	document.getElementById('kdOrg').selectedIndex = 0;
	document.getElementById('periode').selectedIndex = 0;
	document.getElementById('premi').checked = true;
	document.getElementById('premibasis').checked = true;
	document.getElementById('premitetap').checked = true;
	document.getElementById('upah').checked = true;
	document.getElementById('upahpenalty').checked = true;
	document.getElementById('hk').checked = true;
	document.getElementById('dendahk').checked = true;
	document.getElementById('dendarp').checked = true;
	document.getElementById('printContainer').innerHTML = "";
	getSub();
}
</script>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanPremiPerhari').'</span>');
?>
<div>
<fieldset>
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr>
	<td><label><?php echo $_SESSION['lang']['unit'];?></label></td>
	<td><?php echo $optOrg;?>
	</select></td>
</tr>
<?php if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){ ?>
<tr>
	<td><label><?php echo $_SESSION['lang']['subunit'];?></label></td>
	<td><select id="afdId" name="afdId" style="width:150px"><?php echo $optAfd;?>
	</select></td>
</tr>
<?php } ?>
<tr>
	<td><label><?php echo $_SESSION['lang']['periode'];?></label></td>
	<td><select id="periode" name="periode" style="width:150px">
		<!--<option value=""></option>--><?php echo $optPeriode;?>
	</select></td>
</tr>

<tr> 
    <td></td>
	<td colspan="2">
        <input type="checkbox" id="premi" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['premi']; ?>
        <input type="checkbox" id="premibasis" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['premibasis']; ?>
        <input type="checkbox" id="premitetap" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['premitetap']; ?>
        <input type="checkbox" id="upah" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['upah']; ?>
        <input type="checkbox" id="upahpenalty" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['upahpenalty2']; ?>
        <input type="checkbox" id="hk" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['hk']; ?>
        <input type="checkbox" id="dendahk" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['penaltykehadiran']; ?>
        <input type="checkbox" id="dendarp" value="1" onclick="cekcek(this);" checked><? echo $_SESSION['lang']['rupiahpenalty']; ?>
        
    </td>
</tr>

<tr><td></td><td colspan="2">
	<button onclick="zPreview('sdm_slave_2laporanPremiPerHari','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
	<!--<button onclick="zPdf('sdm_slave_2laporanPremiPerHari','<?php echo $arr;?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
	<button onclick="zExcel(event,'sdm_slave_2laporanPremiPerHari.php','<?php echo $arr;?>')" class="mybutton" name="preview" id="preview">Excel</button>

	<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button>
</td></tr>
</table>
</fieldset>
</div>

<?
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
 
<div id='printContainer' style='overflow:auto;height:430px;max-width:100%'>

</div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>
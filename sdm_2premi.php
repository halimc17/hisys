<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/sdm_2premi.js?v=<?php echo time();?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>


<?

$listorg=array();
$listorg = getOrgDetail(10);
ksort($listorg);

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi in ('".implode("','",$listorg)."') order by namaorganisasi asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	#$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$where = "";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $where = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
} else {
	$where = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc ";
$res=fetchdata($str);
foreach($res as $bar){
	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}



$optPer="";
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch()){
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}

$optTp="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTp="select * from ".$dbname.".sdm_ho_component order by name asc";
$nTp=$owlPDO->query($iTp) or die(print " Gagal: ".PDOException::getMessage());
$nTp->setFetchMode(PDO::FETCH_ASSOC);
while($dTp=$nTp->fetch())
{
	$optTp.="<option value=".$dTp['id'].">".$dTp['name']."</option>";
}

$optTipe="";

$optJab="<option value=''>".$_SESSION['lang']['all']."</option>";
$iJab="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$nJab=$owlPDO->query($iJab) or die(print " Gagal: ".PDOException::getMessage());
$nJab->setFetchMode(PDO::FETCH_ASSOC);
while($dJab=$nJab->fetch())
{
    $optJab.="<option value=".$dJab['kodejabatan'].">".$dJab['namajabatan']."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2premi').'</span>');


$optdivisi='';
echo"<br>";
$arr="##unit##tgl1##tgl2##tipekar##divisi";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit class=select2 onchange=getdivisitipe() style=\"width:168px;\">".$optOrg."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['divisi']."</td>
                    <td>:</td>
                    <td><select id=divisi class=select2 style=\"width:168px;\">".$optdivisi."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
                    <td nowrap>
						<input type=text class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' readonly/>
						<input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;' style=\"width:78px;\" maxlength='10' readonly/>
					</td>	
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select class=select2 id=tipekar style=\"width:168px;\">".$optTipe."</select></td>
                </tr>

                <tr>
                    <td><td><td>
					<button onclick=loaddata() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2premi.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    <button class=mybutton onclick=printpdf()>".$_SESSION['lang']['pdf']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<div id='printContainer' class='table-scroll'></div>";
CLOSE_BOX();
echo close_body();








?>
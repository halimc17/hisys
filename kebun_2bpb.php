<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/kebun_2bpb.js?v=<? echo time()?>'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?
$optDiv="";
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optper="";
// $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='KEBUN' order by induk, namaorganisasi asc ";

// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// $optorg.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// while($bar=$res->fetch()){
// 	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
// 	$d=$induk[$bar['kodeorganisasi']];
// 	if($d!=$n){			
// 		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
// 	}
//     $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
// 	$n=$d;
// 	if($d!=$n){			
// 		$optorg.="</optgroup>";
// 	}
// }
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optorg.="<option value='".$key."'>".$key." - ".$val."</option>";			
	
    $n=$d;
	if($d!=$n){
		$optorg.="</optgroup>";
	}
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $optper.="<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}
if($_SESSION['language']=='EN'){
    OPEN_BOX('','<span class=judul>'.getMenu('kebun_2bpb').'</span><br>');
}else{
    OPEN_BOX('','<span class=judul>'.getMenu('kebun_2bpb').'</span><br>');
}


echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=kdorg onchange=getdivisi() style=\"width:159px;\">" . $optorg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=divisi style=\"width:159px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td>
                        <select class=select2 id=per2 style=\"width:159px;\">" . $optper . "</select>
                    </td>
                </tr> 
                <tr>
				<td colspan=2></td>
                <td>
                    <button id=tomboldetail class=mybutton onclick=html1()>".$_SESSION['lang']['preview']."</button>
                    <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "

	<div id=atas style='height:510px;width:100%;display:block;'>
    <div style=clear:both></div>

    <div id='both_report'>
    </div>
        <div id='html1'  style=display:none></div></div>
        <div id='html2'  style=display:none></div>
		<div id='html3'  style=display:none></div>
		<div id='html4'  style=display:none></div>
	</div>
    "; // 
CLOSE_BOX();
echo close_body();
?>
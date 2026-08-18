<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript>
function getdivisi()
{ 	unit= document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param='unit='+unit+'&proses=getdivisi';	
	tujuan='sdm_slave_2gajiharianOpt.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					document.getElementById('divisi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}
</script>

<?
$optOrg="<option value=''></option>";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$vOrg = "";
}
else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    $vOrg = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
}

else{
	$vOrg = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' order by namaorganisasi asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$optPer="";
$iPer="select distinct periode as periode from ".$dbname.".sdm_5periodegaji order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
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

$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$iTipe="select * from ".$dbname.".sdm_5tipekaryawan where id in(0,7,8,9,10) ";
$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
$nTipe->setFetchMode(PDO::FETCH_ASSOC);
while($dTipe=$nTipe->fetch())
{
    $optTipe.="<option value=".$dTipe['id'].">".$dTipe['tipe']."</option>";
}

$optJab="<option value=''>".$_SESSION['lang']['all']."</option>";
$iJab="select * from ".$dbname.".sdm_5jabatan order by namajabatan asc";
$nJab=$owlPDO->query($iJab) or die(print " Gagal: ".PDOException::getMessage());
$nJab->setFetchMode(PDO::FETCH_ASSOC);
while($dJab=$nJab->fetch())
{
    $optJab.="<option value=".$dJab['kodejabatan'].">".$dJab['namajabatan']."</option>";
}

if($_SESSION['language']=='ID'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Monitoring Gaji perhari staff HO').'</span>');
        }else{
    OPEN_BOX('','<span class=judul>'.strtoupper('daily salary').'</span>');
        }


$optdivisi='';
echo"<br>";
$arr="##unit##periode##tipekar";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit  style=\"width:168px;\">".$optOrg."</select></td>
                </tr>
				
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                
                    <td><select id=periode style=\"width:168px;\">".$optPer."</select></td>
						
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekar style=\"width:168px;\">".$optTipe."</select></td>
                </tr>

                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_slave_2gajiharianstaff','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'sdm_slave_2gajiharianstaff.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"<legend><b>".$_SESSION['lang']['printArea']."</b></legend>
	<div id='both_report'>
	<div id='head_tableboth' style='height:30px;'>
	<a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' style='float:right;margin-right:10px'>
		<img title='Full Screen' class=resicon src='images/full-screen.png'>
	</a>
	<a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' style='float:right;margin-right:10px;' >
		<img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
	</a>
	<!--<a class='clearfixheadbtn' table='sortable' style='float:right;margin-right:10px'>
		<img title='fix-header' class=resicon src=images/remove-fix-heder.gif>
	</a>-->
	</div>
	<div id='printContainer' style='overflow:auto;height:400px;max-width:100%';></div>
	</div>";
CLOSE_BOX();
echo close_body();








?>
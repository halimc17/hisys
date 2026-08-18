<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src="js/sdm_2fpdanba.js?v=<?php echo time() ?> "></script>
<?php
OPEN_BOX("","<span class=judul>".getMenu('sdm_2fpdanba')."</span><br>");

$sistemGaji = ['harian'=>'Harian'];

$optOrg=$optPer=$optdivisi=$optKar=$optTipe=$optSistemGaji="<option value='#'>".$_SESSION['lang']['pilihdata']."</option>";
foreach ($sistemGaji as $key=>$val){
    $optSistemGaji .= "<option value='".$key."'>".$val."</option>";
}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$vOrg = "";
}else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $vOrg = " and induk = '".$_SESSION['empl']['kodeorganisasi']."'";
}else{
	$vOrg = " and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ".$vOrg." and kodeorganisasi in(select kodeorg from sdm_5periodegaji) order by namaorganisasi asc ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	#$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

foreach(getOrgDetail(1) as $key => $val){
	$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$nminduk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optOrg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){
		$optOrg.="</optgroup>";
	}
}

$arr="##unit##divisi##periode##tipekar##sumber";	

$arrFilTipeKaryawan = "##unit##divisi##periode";
echo"<table id=tableheader><td valign=top>
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=unit onchange=getdivisitipe() class=select2 style=\"width:168px;\">".$optOrg."</select></td>
                
                    <td>".$_SESSION['lang']['divisi']."</td>
                    <td>:</td>
                    <td><select id=divisi style=\"width:168px;\" class=select2 onchange=filPeriode()>".$optdivisi."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode style=\"width:168px;\" class=select2>".$optPer."</select>	</td>	
				
                    <td>".$_SESSION['lang']['tipekaryawan']."</td>
                    <td>:</td>
                    <td><select id=tipekar style=\"width:168px;\" class=select2 onchange='filKaryawan()'>".$optTipe."</select></td>
				</tr>
				<tr>
                    <td>".$_SESSION['lang']['sumber']."</td>
                    <td>:</td>
                    <td><input class=myinputtext readonly placeholder='Seluruhnya' id=sumber style=\"width:164px;\" onclick='popupsumber()'></td>
				</tr>
				<tr>
					<td><td><td>
					<button onclick=preview('".$arr."','html');showheader(); class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=printexcel1(event,'".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
	</fieldset>
</td>
<td valign=top hidden>
	<fieldset style='float:left;'>
        <legend>Informasi</legend>
		<li></li>
	</fieldset>
</td>
</table>
";
CLOSE_BOX();

OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer' class='table-scroll' style='overflow:auto;height:73vh;'></div>";
CLOSE_BOX();

echo close_body();
?>
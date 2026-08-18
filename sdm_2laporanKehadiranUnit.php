<?
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}
$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTipe="select id,tipe from ".$dbname.".sdm_5tipekaryawan order by tipe asc";
$qTipe=$owlPDO->query($sTipe) or die(print " Gagal: ".PDOException::getMessage());
$qTipe->setFetchMode(PDO::FETCH_ASSOC);
while($rTipe=$qTipe->fetch())
{
	$optTipe.="<option value=".$rTipe['id'].">".$rTipe['tipe']."</option>";
}
$optGaji="<option value='All'>".$_SESSION['lang']['pilihdata']."</option>";
//		$optsisgaji='';
		$arrsgaj=getEnum($dbname,'datakaryawan','sistemgaji');
		foreach($arrsgaj as $kei=>$fal)
		{
			$optGaji.="<option value='".$kei."'>".$fal."</option>";
		}  
$arr="##kdOrg##periode##tgl1##tgl2##tipeKary##sistemGaji";
$arrThn="##kdeOrg2##periodThn##periodThnSmp##sistemGaji3##tipeKary2";
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
        $arrThn="";
        $arr="";
        $arr="##kdOrg##periode##tgl1##tgl2##tipeKary##sistemGaji##afdId";
        $arrThn="##kdeOrg2##periodThn##periodThnSmp##sistemGaji3##tipeKary2##nilaiMax";
	$optOrg="<select id=kdOrg name=kdOrg onchange=getPeriode() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optOrg2="<select id=kdeOrg name=kdeOrg onchange=getKry() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optOrg3="<select id=kdeOrg2 name=kdeOrg2 onchange=getPeriodeGaji5() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KEBUN','PABRIK','KANWIL','RND') order by kodeorganisasi asc ";	
}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
        $arr="##kdOrg##periode##tgl1##tgl2##tipeKary##sistemGaji##afdId";
        $optOrg="<select id=kdOrg name=kdOrg onchange=getPeriode() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optOrg2="<select id=kdeOrg name=kdeOrg onchange=getKry() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optOrg3="<select id=kdeOrg2 name=kdeOrg onchange=getKry() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (
               tipe in ('KEBUN','PABRIK','KANWIL','RND') or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' and induk='".$_SESSION['empl']['kodeorganisasi']."' ) order by kodeorganisasi asc ";	
}else{
	$optOrg="<select id=kdOrg name=kdOrg style=\"width:150px;\"><option value=''>".$_SESSION['lang']['all']."</option>";
	$optOrg2="<select id=kdeOrg name=kdeOrg style=\"width:150px;\" onchange=getKry()><option value=''>".$_SESSION['lang']['all']."</option>";
    $optOrg3="<select id=kdeOrg2 name=kdeOrg onchange=getKry() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$whr='';
	if($_SESSION['empl']['subbagian']=="KSBW14"){
        $whr='';
	}else if($_SESSION['empl']['subbagian']!=''){
		$whr=" and kodeorganisasi='".$_SESSION['empl']['subbagian']."'";
    }
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ) ".$whr." and tipe in ('KEBUN','BIBITAN', 'AFDELING','PABRIK','KANWIL','TRAKSI','GUDANG','WORKSHOP','STATION','RND') order by kodeorganisasi asc";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
	$optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
    $optOrg3.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$optOrg.="</select>";
$optOrg2.="</select>";
$optOrg3.="</select>";

$arrKry="##kdeOrg##period##idKry##tgl_1##tgl_2";

$optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/sdm_2rekapabsen.js'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2laporanKehadiranUnit').'</span>');
?>
<div>
<fieldset style="float: left; height:230px">
<legend><b><?php echo $_SESSION['lang']['rkpAbsen']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><?php echo $optOrg?></td></tr>
<?php  if($_SESSION['empl']['tipelokasitugas']=='KANWIL'||$_SESSION['empl']['tipelokasitugas']=='HOLDING') { ?>
<tr><td><label><?php echo $_SESSION['lang']['subunit']?></label></td><td><select id='afdId' style="width:150px;"><?php echo $optAfd?></select></td></tr>
<?php } ?>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode" name="periode" style="width:150px" onchange="getTgl()"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td><select id="sistemGaji" name="sistemGaji" style="width:150px" onchange="getPeriodeGaji()"><?php echo $optGaji?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggalmulai']?></label></td><td><input type="text" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggalsampai']?></label></td><td><input type="text" class="myinputtext" id="tgl2" name="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tipeKary" name="tipeKary" style="width:150px"><?php echo $optTipe?></select></td></tr>


<tr><td colspan="2"  align='center'><button onclick="zPreview('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button hidden onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>
<?php if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){?>
<div>
    <fieldset style="float: left; height:230px">
<legend><b><?php echo $_SESSION['lang']['rkpAbsen']?> / <?php echo $_SESSION['lang']['tahun']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><?php echo $optOrg3?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['dari']." ".$_SESSION['lang']['periode']?></label></td><td><select id="periodThn" name="periodThn" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sampai']." ".$_SESSION['lang']['periode']?></label></td><td><select id="periodThnSmp" name="periodThnSmp" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td><select id="sistemGaji3" name="sistemGaji3" style="width:150px"><?php echo $optGaji?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tipeKary2" name="tipeKary2" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr><td><label>Min Kehadiran</label></td><td><input type="text" class="myinputtextnumber" maxlength="2" id="nilaiMax" style="width:150px" onkeypress="return angka_doang(event)" /></td></tr>


<tr><td colspan="2">
        <button onclick="zPreview('sdm_slave_2daftarhadir','<?php echo $arrThn?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <button onclick="zExcel(event,'sdm_slave_2daftarhadir.php','<?php echo $arrThn?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
      <? } ?>
<? if($_SESSION['empl']['tipelokasitugas']!='HOLDING')
{
?>
<div >
<fieldset style="float: left; height:230px">
<legend><b><?php echo $_SESSION['lang']['rkpAbsen'].' / '.$_SESSION['lang']['karyawan'];?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><?php echo $optOrg2?></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="period" name="period" style="width:150px" onchange="getTgl2()"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td><select id="sistemGaji2" name="sistemGaji2" style="width:150px" onchange="getPeriodeGaji2()"><?php echo $optGaji?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggalmulai']?></label></td><td><input type="text" class="myinputtext" id="tgl_1" name="tgl_1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggalsampai']?></label></td><td><input type="text" class="myinputtext" id="tgl_2" name="tgl_2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:145px;" readonly/></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['namakaryawan']?></label></td><td><select id="idKry" name="idKry" style="width:150px"><option value=""><? echo $_SESSION['lang']['pilihdata']?></option></select>
<img id='idKry' onclick=z.elSearch('idKry',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
</td></tr>


<tr><td colspan="2" align='center'><button onclick="zPreview('sdm_slave_2rekapabsen_kary','<?php echo $arrKry?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button hidden onclick="zPdf('sdm_slave_2rekapabsen_kary','<?php echo $arrKry?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen_kary.php','<?php echo $arrKry?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear2()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>
</table>
</fieldset>
</div>
      <div >
<fieldset style="float: left; height:230px">
<legend><b><?php echo $_SESSION['lang']['rkpAbsen']?> / <?php echo $_SESSION['lang']['tahun']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><?php echo $optOrg3?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['dari']." ".$_SESSION['lang']['periode']?></label></td><td><select id="periodThn" name="periodThn" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sampai']." ".$_SESSION['lang']['periode']?></label></td><td><select id="periodThnSmp" name="periodThnSmp" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td><select id="sistemGaji3" name="sistemGaji3" style="width:150px"><?php echo $optGaji?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tipeKary2" name="tipeKary2" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr height="20"><td colspan="2">&nbsp;</td></tr>

<tr><td colspan="2"  align='center'><button onclick="zPreview('sdm_slave_2rekapabsen_thn','<?php echo $arrThn?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button hidden onclick="zPdf('sdm_slave_2rekapabsen_thn','<?php echo $arrThn?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen_thn.php','<?php echo $arrThn?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear3()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>
</table>
</fieldset>
</div>

<? }
$sAbsen="select * from ".$dbname.".sdm_5absensi order by kodeabsen asc ";
$rAbsen=fetchData($sAbsen);

echo"
<fieldset style=width:260px;>
<legend><b>".$_SESSION['lang']['info']."</b></legend> 
<div style=height:210px;float:left;overflow:auto>
<table class=sortable cellpadding=1 cellspacing=1 border=0>
<thead>
<tr><td>".$_SESSION['lang']['kodeabsen']."</td>
<td>".$_SESSION['lang']['keterangan']."</td>
</thead><tbody>";
if(count($rAbsen)>0){
    foreach ($rAbsen as $key => $val) {
        echo"<tr class=rowcontent>";
        echo "<td>".$val['kodeabsen']."</td>";
        echo "<td>".$val['keterangan']."</td>";
        echo"</tr>";
    }
}


echo"</tbody></table></div></fieldset>
";
CLOSE_BOX();
OPEN_BOX();

echo"
    <fieldset style=float:left;display:none>
        <legend>".$_SESSION['lang']['keterangan']." & Legend</legend>
            <table border=0 cellspacing=1 class=sortable>
                <tr class=rowcontent>
                
                    <td bgcolor=#275370 style='color:#D7EBFA'>".$_SESSION['lang']['keterangan']."</td>
                 ";
            $iKet="select * from ".$dbname.".sdm_5absensi ";
			$nKet=$owlPDO->query($iKet) or die(print " Gagal: ".PDOException::getMessage());
			$nKet->setFetchMode(PDO::FETCH_ASSOC);
            while($dKet=  $nKet->fetch())
            {
                echo"
                    <td valign=top  align=center>".$dKet['keterangan']."<br></td>
                ";
            }
            echo"</tr>";
			
			 echo"
                <tr class=rowcontent>
                    <td bgcolor=#275370 style='color:#D7EBFA'>Kode</td>
                 ";           
            $iHk="select * from ".$dbname.".sdm_5absensi ";
			$nHk=$owlPDO->query($iHk) or die(print " Gagal: ".PDOException::getMessage());
			$nHk->setFetchMode(PDO::FETCH_ASSOC);
            while($dHk=  $nHk->fetch())
            {
                echo"
                    <td align=center>".$dHk['kodeabsen']."</td>
                ";
            }
            echo"</tr>";
			
            echo"
                <tr class=rowcontent>
                    <td bgcolor=#275370 style='color:#D7EBFA'>HK</td>
                 ";           
            $iHk="select * from ".$dbname.".sdm_5absensi ";
			$nHk=$owlPDO->query($iHk) or die(print " Gagal: ".PDOException::getMessage());
			$nHk->setFetchMode(PDO::FETCH_ASSOC);
            while($dHk=  $nHk->fetch())
            {
                echo"
                    <td align=center>".$dHk['nilaihk']."</td>
                ";
            }
            echo"</tr>";
			
            
echo"</table>
    <ol style=display:none>
        <li>Backgroud Kuning : Tidak dapat catu beras</li>
        <li>Backgroud Pink dgn keterangan HK0 : Karyawan tsb tidak dapat HK, tetapi mendapat premi</li>
    </ol>
</fieldset>
    


    ";





?>

<div id='printContainer' class='table-scroll' style='overflow:auto;min-height:350px;max-width:100%'>
<?php
//echo"<pre>";
//print_r($_SESSION);
//echo"</pre>";
?>
</div>

<?php

CLOSE_BOX();
echo close_body();
?>
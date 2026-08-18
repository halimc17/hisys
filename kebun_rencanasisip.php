<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript1.2 src='js/kebun_rencanasisip.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_rencanasisip').'</span><br>');

$sKebun="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi 
    where kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."' and tipe='KEBUN' order by kodeorganisasi asc";
$qKebun=$owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
$qKebun->setFetchMode(PDO::FETCH_ASSOC);
$optKebun='';
while($rKebun=$qKebun->fetch())
{
    $kamusKebun[$rKebun['kodeorganisasi']]=$rKebun['namaorganisasi'];
    $optKebun.="<option value='".$rKebun['kodeorganisasi']."'>".$rKebun['namaorganisasi']."</option>";
}

$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sBlok="select kodeorg, statusblok, tahuntanam from ".$dbname.".setup_blok 
    where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and luasareaproduktif>0 order by kodeorg asc";
$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
$qBlok->setFetchMode(PDO::FETCH_ASSOC);
while($rBlok=$qBlok->fetch())
{
    $optBlok.="<option value='".$rBlok['kodeorg']."'>".$rBlok['kodeorg']." - ".$rBlok['statusblok']." - ".$rBlok['tahuntanam']."</option>";
}

$sAlsRncaSisip="select kodealasanrencanasisip, deskripsi from ".$dbname.".kebun_5alasanrencanasisip order by deskripsi asc";
$qAlsRncaSisip=$owlPDO->query($sAlsRncaSisip) or die(print " Gagal: ".PDOException::getMessage());
$qAlsRncaSisip->setFetchMode(PDO::FETCH_ASSOC);
$optKeterangan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while($rAlsRncaSisip=$qAlsRncaSisip->fetch())
{
	$optKeterangan.="<option value='".$rAlsRncaSisip['kodealasanrencanasisip']."'>".$rAlsRncaSisip['deskripsi']."</option>";
}

$tahun=date("Y");
// $optPeriode="";
// for ($i = 1; $i <= 12; $i++) {
    // if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
    // $optPeriode.="<option value='".$tahun."-".$ii."'>".$tahun."-".$ii."</option>";
// }
$optPeriode = "";
    for ($x = 0; $x <= 12; $x++) {
        $dte = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
        $optPeriode.="<option value=" . date("m-Y", $dte) . ">" . date("m-Y", $dte) . "</option>";
    }

$optPeriode2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPeriode="select distinct periode from ".$dbname.".kebun_rencanasisip order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    //if($rPeriode['periode']==($tahun.'-01'))$pilih=' selected'; else $pilih='';
    $optPeriode2.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}

echo"<fieldset style='float:left;'><legend>Form</legend>
    <table>
    <tr>
        <td>".$_SESSION['lang']['kebun']."</td><td>:</td>
        <td><select style=width:150px id=kebun>".$optKebun."</select></td>
		
		
		
    </tr>
    <tr>
        <td>".$_SESSION['lang']['blok']."</td><td>:</td>
        <td><select style=width:150px id=blok onchange=gantiblok();>".$optBlok."</select>
		<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>	
		
		<td>".$_SESSION['lang']['luas']." ".$_SESSION['lang']['ha']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=luas onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
		
    </tr>
    <tr>
        <td>".$_SESSION['lang']['periode']."</td><td>:</td>
        <td><select style=width:75px id=periode>".$optPeriode."</select></td>
		
		<td>".$_SESSION['lang']['pokokmati']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=pokokmati onkeypress=\"return angka_doang(event);\" size=10 maxlength=10></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['pokok']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=pokok onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
		
		<td>".$_SESSION['lang']['rencanasisip']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber onchange=hitungsph() id=rencanasisip onkeypress=\"return angka_doang(event);\" size=10 maxlength=10></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['sph']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=sph onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
		
		<td>".$_SESSION['lang']['pokok']." setelah ".$_SESSION['lang']['sisip']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=pokoksetelahsisip onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
		
		
    </tr>
    
    <tr>
        <td>".$_SESSION['lang']['alasanrencanasisip']."</td><td>:</td>
        <td><select style=width:150px id=keterangan>".$optKeterangan."</select></td>
		
		<td>".$_SESSION['lang']['sph']." setelah ".$_SESSION['lang']['sisip']."</td><td>:</td>
        <td><input style=width:70px type=text class=myinputtextnumber id=sphssp onkeypress=\"return tanpa_kutip(event);\" size=10 maxlength=10 disabled></td>
    </tr>
    <tr><td><td><td>
    <input type=hidden id=method value='insert'>
    <input type=hidden id=matrixid value=''>    
    <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
    <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
    </td></td></td></tr></table>
	</fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo open_theme($_SESSION['lang']['list']);
echo "<div id=container>";
echo "<table><tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td><select id=periode2 onchange=pilihperiode()>".$optPeriode2."</select></td>
    </tr></table>";

$where = "";
if(substr($_SESSION['empl']['lokasitugas'],2,2) != "HO") {
	$where = " WHERE t1.blok like '".$_SESSION['empl']['lokasitugas']."%' ";
}
$str1="select t1.*, t2.deskripsi from ".$dbname.".kebun_rencanasisip t1 
	left join ".$dbname.".kebun_5alasanrencanasisip t2
	on t1.keterangan=t2.kodealasanrencanasisip ".$where."
	order by t1.periode desc, t1.blok";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:100%;'>
     <thead>
     <tr class=rowheader>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>".$_SESSION['lang']['blok']."</td>
        <td>".$_SESSION['lang']['pokok']."</td>
        <td>".$_SESSION['lang']['sph']."</td>
        <td>".$_SESSION['lang']['pokokmati']."</td>
        <td>".$_SESSION['lang']['rencanasisip']."</td>
        <td>".$_SESSION['lang']['alasanrencanasisip']."</td>
        <td>".$_SESSION['lang']['action']."</td>
     </tr></thead>
     <tbody>";
$no=0;
while($bar1=$res1->fetch())
{ 

		$sBlok="select luasareaproduktif from ".$dbname.".setup_blok 
            where kodeorg like '".$bar1->blok."%'";
		$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
		$qBlok->setFetchMode(PDO::FETCH_ASSOC);
        $rBlok=$qBlok->fetch();
			$luas=$rBlok['luasareaproduktif'];
			
			
    $no+=1;
    echo"<tr class=rowcontent>
        <td>".$bar1->periode."</td>
        <td>".$bar1->blok."</td>
        <td align=right>".number_format($bar1->pokok)."</td>
        <td align=right>".number_format($bar1->sph,2)."</td>
        <td align=right>".number_format($bar1->pokokmati)."</td>
        <td align=right>".number_format($bar1->rencanasisip)."</td>
        <td>".$bar1->deskripsi."</td>
        <td align=center>";
            if($bar1->posting=='0'){ // belum posting
                echo"<img src=images/application/application_edit.png class=resicon  caption='Edit' 
                onclick=\"fillField('".$bar1->periode."','".$bar1->blok."','".$bar1->pokok."','".$bar1->sph."','".$bar1->pokokmati."','".$bar1->rencanasisip."','".$bar1->keterangan."','".$luas."');\">
                <img src=images/application/application_delete.png class=resicon  caption='Edit' onclick=\"hapus('".$bar1->periode."','".$bar1->blok."');\">";
                echo"&nbsp;<img src=images/skyblue/posting.png class=resicon caption='Posting' onclick=\"posting('".$bar1->periode."','".$bar1->blok."',event);\">";                    
            }else{ // sudah postng
                echo"&nbsp;<img src=images/skyblue/posted.png>";                    
            }
        echo"</td>
    </tr>";
}	 
echo"</tbody>
    <tfoot>
    </tfoot>
    </table>";
echo "</div>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>
<?
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/kebun_generatenobkm.js'></script>
<script language=javascript1.2 src='js/option.js'></script>
<script language="javascript" src="js/zMaster.js"></script>

<?php
$optPeriode="";
for($x=0;$x<=13;$x++){
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("Y-m",$dt)."</option>";
}

$optPeriode2="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPt="select distinct(periode) as periode from ".$dbname.".kebun_nobkm order by periode desc limit 12 ";
$nPt=$owlPDO->query($iPt) or die(print " Gagal: ".PDOException::getMessage());
$nPt->setFetchMode(PDO::FETCH_ASSOC);
while($dPt=  $nPt->fetch()){
    @$optPeriode2.="<option value='".$dPt['periode']."'>".$dPt['periode']."</option>";
}


$optCariPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iPt="select * from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ";
$nPt=$owlPDO->query($iPt) or die(print " Gagal: ".PDOException::getMessage());
$nPt->setFetchMode(PDO::FETCH_ASSOC);
while($dPt=  $nPt->fetch()){
    @$optunit.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['kodeorganisasi']." - ".$dPt['namaorganisasi']."</option>";
    @$optCariPt.="<option value='".$dPt['kodeorganisasi']."'>".$dPt['kodeorganisasi']." - ".$dPt['namaorganisasi']."</option>";
}
OPEN_BOX('','<span class=judul>'.getMenu('kebun_generatenobkm').'</span></br>');
echo"<fieldset>";
    echo"<legend>Form</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdUnit onchange=getdivisi2() style=\"width:204px;\">".$optunit."</select></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['divisi']."</td>
                    <td>:</td>
                    <td><select id=divisi onchange=getnoawal() style=\"width:204px;\"></select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td> 
                    <td>:</td>
                    <td><select id=periode onchange=getnoawal() style=\"width:204px;\">".$optPeriode."</select></td>
                </tr>
				<tr>
                    <td>Nomor BKM Awal</td> 
                    <td>:</td>
                    <td><input type=text id=noawal nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength='19' disabled></td>
                </tr>
				<tr>
                    <td>Nomor BKM Akhir</td> 
                    <td>:</td>
                    <td><input type=text id=noakhir nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength='19'  disabled></td>
                </tr>
				<tr>
                    <td>".$_SESSION['lang']['jumlah']."</td> 
                    <td>:</td>
                    <td><input type=jumlah onkeyup=getnoakhir() id=jumlah nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber style=\"width:75px;\" maxlength='15'></td>
                </tr>
                <tr><td colspan=2></td>
                        <td colspan=3>
                                <button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                        </td>
                </tr>

        </table></fieldset>
		<input type=hidden id=method value='insert'>";
		echo"<input type=hidden id=subbagian value='".$_SESSION['empl']['subbagian']."'>";
CLOSE_BOX();
OPEN_BOX();
#ISI UNTUK DAFTAR 
echo "<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <div>
					<table>
						<tr>
						<td>".$_SESSION['lang']['divisi']."</td>
						<td><select id=cariPt onchange=cariBast()>".$optCariPt."</select></td>
						<td>".$_SESSION['lang']['periode']."</td>
						<td><select id=cariStatus onchange=cariBast()>".$optPeriode2."</select>
						</td>
						<td><button class=mybutton onclick=cariBast()>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=resetcari()>".$_SESSION['lang']['cancel']."</button>
						</td>
						</tr>
					</table>
                </div>
                <div id=container> 
                        <script>loadData()</script>
                </div>
        </fieldset>";
CLOSE_BOX();
echo close_body();					
?>
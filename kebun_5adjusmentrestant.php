<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/kebun_5adjusmentrestant.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

//user holding dapat menempatkan dimana saja
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING'){
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION','PABRIK','KANWIL','HOLDING') 
              and length(kodeorganisasi)=4 order by kodeorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
	$optAll='';
    while($bar=$res->fetch()){
        $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }
}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL'){
    $wheredt="regional='".$_SESSION['empl']['regional']."'";
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION','PABRIK','KANWIL')
                   and length(kodeorganisasi)=4
          and (kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where ".$wheredt.")
          or induk in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where ".$wheredt.")) 
          order by kodeorganisasi asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		$optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
	}
}else if(trim($_SESSION['empl']['tipelokasitugas'])=='KEBUN'){
	//user unit hanya dapat menempatkan pada unitnya dan anak unitnya

    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
        and kodeorganisasi  like '".$_SESSION['empl']['lokasitugas']."%' order by kodeorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $optorg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";	
    }
}

?>


<?php

OPEN_BOX('','<span class=judul>'.getMenu('kebun_5adjusmentrestant').'</span>');
echo "<br>";
$optdivisi=$optblok="";
echo"<fieldset style=float:left;height:180px>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kodeorg']."</td>
					<td>:</td>
					<td><select id=kodeorg onchange=getdivisi() style=\"width:175px;\" >".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><select id=divisi onchange=getblok() style=\"width:175px;\" >".$optdivisi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>:</td>
					<td><select id=blok onchange=getrestan() style=\"width:175px;\" >".$optblok."</select>
					<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					</td>
				
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text style=\"width:100px;\" class=myinputtext id=tgl onchange=getrestan() onmousemove=setCalendar(this.id) onkeypress=return false; value=".date("d-m-Y")." size=10 maxlength=10 /></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['restan']." (Jjg)</td>
					<td>:</td>
					<td><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=restan disabled style=\"width:100px;\" ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['Penyesuaian']." (Jjg)</td>
					<td>:</td>
					<td><input class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" id=adjust style=\"width:100px;\" ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td><input class=myinputtext id=ket style=\"width:170px;\" ></td>
				</tr>
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='update'>";
		
echo"<fieldset style=height:180px>
		<legend>".$_SESSION['lang']['info']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>01 . </td>
				<td>Janjang penyesuaian akan dimasukkan ke kolom afkir pada menu : Kebun - Transaksi - Rekap Panen Perblok</td>
			</tr>
			</table></fieldset>";
			
CLOSE_BOX();
OPEN_BOX();
echo"<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
			<fieldset style=float:left>
				<legend>".$_SESSION['lang']['find']."</legend>
				<table>
				<tr>
					<td>".$_SESSION['lang']['blok']."</td>
					<td>:</td>
					<td><input class=myinputtext id=bloksrc style=\"width:100px;\" ></td>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text style=\"width:100px;\" class=myinputtext id=tglsrc onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10/></td>
					<td><button class=mybutton onclick=loadData(0)>Find</button>
						<button class=mybutton onclick=loadDataAll(0)>Clear</button></td>
				</tr>
				</table>
			</fieldset>
		<div style=clear:both></div>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>
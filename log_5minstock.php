<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5minstock.js?v=1.1'></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('log_5minstock').'</span><br>');

$optpt=$optgudang=$optklbarang=$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optcrpt=$optcrgudang=$optcrklbarang=$optcrbarang="<option value=''>".$_SESSION['lang']['all']."</option>";

##GET PT
$str="select * from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$optpt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$optcrpt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

##GET KELOMPOK BARANG
$str="select * from ".$dbname.".log_5klbarang where status='1' order by kelompok asc";
$res=fetchdata($str);
foreach($res as $val){
	$optklbarang.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['kelompok']."</option>";
	$optcrklbarang.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['kelompok']."</option>";
}

##FORM
echo"<fieldset>
	<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table border=0 style='display: inline-block;vertical-align:top'>
		<tr>
			<td class=bintang>".$_SESSION['lang']['pt']."</td>
			<td>:</td>
			<td>
				<select id=pt onchange='getgudang()'>".$optpt."</select>
				<img id='pt' onclick=z.elSearch('pt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td class=bintang>".$_SESSION['lang']['gudang']."</td> 
			<td>:</td>
			<td>
				<select id=gudang>".$optgudang."</select>
				<img id='gudang' onclick=z.elSearch('gudang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td class=bintang>".$_SESSION['lang']['kelompokbarang']."</td> 
			<td>:</td>
			<td>
				<select id=kelompokbarang onchange='getbarang()'>".$optklbarang."</select>
				<img id='kelompokbarang' onclick=z.elSearch('kelompokbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td class=bintang>".$_SESSION['lang']['barang']."</td> 
			<td>:</td>
			<td>
				<select id=barang onchange='getsatuan()'>".$optbarang."</select>
				<img id='barang' onclick=z.elSearch('barang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['minstok']."</td> 
			<td>:</td>
			<td>
				<input type=text class=myinputtextnumber id=minstok onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder='0' />&nbsp;
				<input type=text class=myinputtext id=satuan style=\"width:80px;\" disabled placeholder='satuan' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['maxstok']."</td> 
			<td>:</td>
			<td>
				<input type=text class=myinputtextnumber id=maxstok onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder='0' />
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=myid value=''>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX ();

OPEN_BOX ();
echo"<fieldset style=''>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top;padding-bottom:10px;'>
		<tr>
			<td>
				<fieldset style=float:left><legend><b>".$_SESSION['lang']['find']."</b></legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pt']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crpt onchange='getcrgudang()'>".$optcrpt."</select>
							<img id='crpt' onclick=z.elSearch('crpt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						</td>
						
						<td style='padding-left:20px;'>".$_SESSION['lang']['kelompokbarang']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crklbarang onchange='getcrbarang()'>".$optcrklbarang."</select>
							<img id='crklbarang' onclick=z.elSearch('crklbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['gudang']."</td> 
						<td>:</td>
						<td>
							<select style=width:200px id=crgudang onchange='loaddata(0)'>".$optcrgudang."</select>
							<img id='crgudang' onclick=z.elSearch('crgudang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						</td>
						
						<td style='padding-left:20px;'>".$_SESSION['lang']['barang']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crbarang onchange='loaddata(0)'>".$optcrbarang."</select>
							<img id='crbarang' onclick=z.elSearch('crbarang',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
						</td>
					</tr>
					<tr>
						<td colspan=6 style='text-align:center'>
							<button class=mybutton onclick='batalcari();'>".$_SESSION['lang']['resetvariableoutput']."</button>
						</td>
					</tr>
				 </table>
				 </fieldset>
			</td> 
		</tr>
	</table>
	
	<div id='container'><script>loaddata(0)</script></div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>
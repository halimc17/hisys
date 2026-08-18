<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2tbserpvtimbang').'</span>');
?>

<script type="text/javascript" src="js/pabrik_2tbserpvtimbang.js" /></script>

<?php

$kdPbrk = $_SESSION['empl']['lokasitugas'];
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where (`kelompokbarang`='400' or `kelompokbarang`='351') order by kelompokbarang desc";

$optBrg="";

$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
	if($rBrg['kodebarang']=='40000001' || $rBrg['kodebarang']=='40000002')
	{
		$optBrg2.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
	}
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$sPbrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";

$optPabrik="";
$qPabrik=$owlPDO->query($sPbrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	if($rPabrik['kodeorganisasi']!='BP9M')
	{
		$optPabrik2.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>";
	}
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>";
}

echo"<table>
	<tr>
		<td style='vertical-align:top'>
			<fieldset style='display:none'>
				<legend>".$_SESSION['lang']['all']."</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pabrik']."</td>
						<td>:</td>
						<td>
							<select id=kdpabrik1 style=width:150px>".$optPabrik."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>:</td>
						<td>
							<select id=kdbrg1 style=width:150px><option value=''>All</option>".$optBrg."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tgltrans1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
							<input type=text class=myinputtext id=tgltrans2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=preview1('html')>".$_SESSION['lang']['preview']."</button>
							<button class=mybutton onclick=printexcel1('excel')>".$_SESSION['lang']['excel']."</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<td style='vertical-align:top'>
			<fieldset>
				<legend>PMKS v BULKING</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pabrik']."</td>
						<td>:</td>
						<td>
							<select id=kdpabrik2 style=width:175px>".$optPabrik2."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tglawal1 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px maxlength=10 value='".date('d-m-Y')."' readonly />
							<input type=text class=myinputtext id=tglawal2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px maxlength=10 value='".date('d-m-Y')."' readonly />
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=preview2('html')>".$_SESSION['lang']['preview']."</button>
							<button class=mybutton onclick=printexcel2('excel')>".$_SESSION['lang']['excel']."</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<td style='vertical-align:top;display:none'>
			<fieldset>
				<legend>".$_SESSION['lang']['rekapterimatbs']."</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pabrik']."</td>
						<td>:</td>
						<td>
							<select id=kdpabrik2 style=width:150px>".$optPabrik."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tgltrans2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=preview2('html')>".$_SESSION['lang']['preview']."</button>
							<button class=mybutton onclick=printexcel2('excel')>".$_SESSION['lang']['excel']."</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
	</tr>
</table>";

// echo"<table>
     // <tr valign=moiddle>
		 // <td>
			// <fieldset><legend>".$_SESSION['lang']['pilihdata']."</legend>"; 
			// echo $_SESSION['lang']['namabarang'].":<select id=kdBrg name=kdBrg style=width:200px><option value=0>All</option>".$optBrg."</select>&nbsp;"; 
			// echo $_SESSION['lang']['pabrik'].":<select id=kdPbrk name=kdPbrk style=width:100px>".$optPabrik."</select>&nbsp;";
			// echo $_SESSION['lang']['tanggal'].":<input type=text class=myinputtext id=tglTrans name=tglTrans onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />";
			// echo"<button class=mybutton onclick=savePil()>".$_SESSION['lang']['save']."</button>
			     // <button class=mybutton onclick=gantiPil()>".$_SESSION['lang']['ganti']."</button>";
// echo"</fieldset></td>
     // </tr>
	 // </table> "; 

CLOSE_BOX();
OPEN_BOX();

echo "<fieldset>
	<legend>".$_SESSION['lang']['result']."</legend>
	<div id='contain' style='min-height:10px;'>
	</div>
</fieldset>";

CLOSE_BOX();
echo close_body();
?>
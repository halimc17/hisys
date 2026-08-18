<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanPabrikTimbangan']).'</span>');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2timbanganv2').'</span>');
?>

<script language="javascript" src="js/zSelect2.js?ver=<?= time(); ?>"></script>
<script language=javascript src='js/pabrik_2timbanganv2.js?v=<?php echo time(); ?>'></script>

<?php

$kdPbrk = $_SESSION['empl']['lokasitugas'];
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where (`kelompokbarang`='400' or `kelompokbarang`='401') order by kelompokbarang asc";

$optCust=$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";

$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
	if($rBrg['kodebarang']=='400000001' || $rBrg['kodebarang']=='400000002')
	{
		@$optBrg2.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
	}
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}
$sPbrik="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING')";

$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qPabrik=$owlPDO->query($sPbrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch()){
	if($rPabrik['kodeorganisasi']!='KSBW')
	{
		@$optPabrik2.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>";
	}
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi']." ".($rPabrik['kodeorganisasi']==$kdPbrk?'selected':'').">".$rPabrik['namaorganisasi']."</option>";
}

# Vendor
$whrtipe = "SELECT supplierid FROM {$dbname}.log_5supkelompok WHERE tipe LIKE '%TBS%'";
$sql = selectQuery($dbname,"log_5supplier","*","supplierid IN (".$whrtipe.")");
$res = fetchData($sql);
$optCust .= "<optgroup label='VENDOR TBS'>";
foreach($res as $row):
	$optCust .= "<option value='".$row['supplierid']."'>".$row['namasupplier']."</option>";
endforeach;
$optCust .= "</optgroup>";

# Customer
$sql = selectQuery($dbname,"pmn_4customer","*");
$res = fetchData($sql);
$optCust .= "<optgroup label='PELANGGAN'>";
foreach($res as $row):
	$optCust .= "<option value='".$row['kodecustomer']."'>".$row['namacustomer']."</option>";
endforeach;
$optCust .= "</optgroup>";

# Organisasi Inti
$sql = selectQuery($dbname,"organisasi","*","inti=1 and length(kodeorganisasi)=4 and tipe='KEBUN'");
$res = fetchData($sql);
$optCust .= "<optgroup label='KEBUN INTI'>";
foreach($res as $row):
	$optCust .= "<option value='".$row['kodeorganisasi']."'>".$row['namaorganisasi']."</option>";
endforeach;
$optCust .= "</optgroup>";

# Organisasi Plasma
$sql = selectQuery($dbname,"organisasi","*","inti=0 and length(kodeorganisasi)=4 and tipe='KEBUN'");
$res = fetchData($sql);
$optCust .= "<optgroup label='KEBUN PLASMA'>";
foreach($res as $row):
	$optCust .= "<option value='".$row['kodeorganisasi']."'>".$row['namaorganisasi']."</option>";
endforeach;
$optCust .= "</optgroup>";

# Tipe
$optTipe .= "<option value='int'>INTERNAL</option>";
$optTipe .= "<option value='ext'>EXTERNAL</option>";
$optTipe .= "<option value='pls'>PLASMA</option>";

echo"<table>
	<tr>
		<td style='vertical-align:top'>
			<fieldset>
				<legend>".$_SESSION['lang']['all']."</legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['pabrik']."</td>
						<td>:</td>
						<td>
							<select class=select2 id=kdpabrik1 style=width:150px>".$optPabrik."</select>
						</td>
						<td>".$_SESSION['lang']['NoKontrak']."</td>
						<td>:</td>
						<td>
							<input type=text id=nokontrak class=myinputtext style=\"width:150px;\">
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>:</td>
						<td>
							<select class=select2 id=kdbrg1 style=width:150px>".$optBrg."</select>
						</td>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tgltrans1 onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:70px size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
							<input type=text class=myinputtext id=tgltrans2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px size=10 maxlength=10 value='".date('d-m-Y')."' readonly />
						</td>
					</tr>
						<td>".$_SESSION['lang']['customer']."</td>
						<td>:</td>
						<td>
							<select class=select2 id=cust1 style=width:150px>".$optCust."</select>
						</td>
					<tr>

					</tr>
					
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=preview1('html')>".$_SESSION['lang']['preview']."</button>
							<button class=mybutton onclick=printexcel1('excel')>".$_SESSION['lang']['excel']."</button>
							<button class=mybutton onclick=printexcel1('pdf')>".$_SESSION['lang']['pdf']."</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<td style='vertical-align:top' hidden>
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
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>:</td>
						<td>
							<select id=kdbrg2 style=width:175px>".$optBrg2."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['periode']."</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext id=tglawal2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px maxlength=10 value='".date('01-m-Y')."' readonly /> s/d 
							<input type=text class=myinputtext id=tglakhir2 onmousemove=setCalendar(this.id) onkeypress=return false; style=width:70px maxlength=10 value='".date('d-m-Y')."' readonly />
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
				<legend>".@$_SESSION['lang']['rekapterimatbs']."</legend>
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

echo "
	<div id='contain' style='height:60vh!important;'>
	</div>";

CLOSE_BOX();
echo close_body();
?>
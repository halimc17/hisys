<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zLib.php');
?>
<script language=javascript1.2 src='js/sdm_sp.js?v=<?php echo time(); ?>'></script>

<?php

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['sutarperingatan']).'</span>');

## Get JENIS SP
$opts = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select * from " . $dbname . ".sdm_5jenissp order by kode";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $opts.="<option value='" . $bar->kode . "'>" . $bar->keterangan . "</option>";
}

## Get UNI
$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

## GET TiPEKARYAWaN
$optTipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 ";
$res=fetchdata($str);
foreach($res as $val){
	$optTipe.="<option value='".$val['id']."'>".$val['tipe']."</option>";
}

$optkar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".datakaryawan 
	where (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."') order by namakaryawan";
$res = fetchData($str);
foreach($res as $bar){
	$optkar.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
}


#=========================
echo "<script>loadList(0)</script>";
$frm[0] = "
<fieldset>
	<legend>" . $_SESSION['lang']['form'] . "</legend>
	<table cellpadding=3 cellspacing=1 border=0>
		<tr> 	 
			<td>
				<input type=hidden value='insert' id=method>
				<input type=hidden value='' id=nosp>
				" . $_SESSION['lang']['memotype'] . "
			</td><td>:</td>
			<td><select id=jenissp style='width:250px;' onchange=\"memotypeChange()\">" . $opts . "</select></td>
		</tr>
		<tr> 	 
			<td>" . $_SESSION['lang']['lokasitugas'] . "</td><td>:</td>
			<td><select  style='width:250px;' id=lokasitugas >" . $optUnit . "</select></td>
		</tr>
		<tr> 	 
			<td>" . $_SESSION['lang']['tipekaryawan'] . "</td><td>:</td>
			<td><select  style='width:250px;' id=tipekaryawan onchange=changeDatakaryawan() >" . $optTipe . "</select></td>
		</tr>          
		<tr> 	 
			<td>" . $_SESSION['lang']['karyawan'] . "</td><td>:</td>
			<td><select  style='width:250px;' id=karyawanid >" . $optkar . "</select>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
		</tr>
		<tr> 	 
			<td>" . $_SESSION['lang']['tanggalsurat'] . "</td><td>:</td>
			<td><input type=text  style='width:245px;' id=tanggalsp size=10 maxlength=10 class=myinputtext onkeypress=\"return false;\" onmouseover=setCalendar(this)></td>
		</tr>
		<tr>
			<td colspan=3><hr></td> 
		</tr>
		<tr>
			<td style='vertical-align:top'><label id='txt1'>Paragraf 1 :</label></td><td></td><td><textarea placeholder='Kronologis Perkara/Kasus : <br> untuk enter' id=paragraf1 onkeypress=\"return tanpa_kutip(event);\" cols=98 rows=3></textarea></td>
		</tr>
		<tr>
			<td style='vertical-align:top'><label id='txt2'>Paragraf 2 :</label></td><td></td><td><textarea placeholder='Saran dari Atasan/Personalia : <br> untuk enter' id=paragraf2 onkeypress=\"return tanpa_kutip(event);\" cols=98 rows=3></textarea></td>
		</tr>	
		<tr>
			<td style='vertical-align:top'><label id='txt3'>Paragraf 3 :</label></td><td></td><td><textarea id=paragraf3 onkeypress=\"return tanpa_kutip(event);\" cols=98 rows=3></textarea></td>
		</tr>	
		<tr>
			<td style='vertical-align:top'><label id='txt4'>Paragraf 4 :</label></td><td></td><td><textarea id=paragraf4 onkeypress=\"return tanpa_kutip(event);\" cols=98 rows=3></textarea></td>
		</tr>
		<tr>
			<td colspan=3><hr></td> 
		</tr>
	</table>
	<table cellpadding=3 cellspacing=1>
		<tr>
			<td><label id=label_disetujui>" . $_SESSION['lang']['disetujui'] . "</label></td><td>:</td>
			<td><input autocomplete=off type=text class=myinputtext id=penandatangan size=30 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>

			<td><label id=label_diketahui>" . $_SESSION['lang']['diketahuioleh'] . "</label></td>
			<td><input autocomplete=off type=text class=myinputtext id=verifikasi size=30 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>      

			<td><label id=id=label_dibuat>" . $_SESSION['lang']['dibuat'] . "</label></td>
			<td><input autocomplete=off type=text class=myinputtext id=dibuat size=30 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td>           
		</tr>
		<tr>
			<td id='tdDistujui3'>" . $_SESSION['lang']['functionname'] . "</td><td>:</td>
			<td id='tdDistujui4'><input autocomplete=off type=text class=myinputtext id=jabatan size=30 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td> 

			<td id='tdDiketahui3'>" . $_SESSION['lang']['functionname'] . "</td>
			<td id='tdDiketahui4'><input autocomplete=off type=text class=myinputtext id=jabatan1 size=30 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td>

			<td id='tdDibuat3'>" . $_SESSION['lang']['functionname'] . "</td>
			<td id='tdDibuat4'><input autocomplete=off type=text class=myinputtext id=jabatan2 size=30 maxlength=50 onkeypress=\"return tanpa_kutip(event);\"></td>        
		</tr>
	</table>
	<br>
	<table border=0>
		<tr>
			<td colspan=3><hr></td> 
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tembusan'] . "(i)</td><td>:</td>
			<td><input type=text  style='width:245px;' autocomplete=off  class=myinputtext id=tembusan1 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tembusan'] . "(ii)</td><td>:</td>
			<td><input type=text style='width:245px;' autocomplete=off  class=myinputtext id=tembusan2 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tembusan'] . "(iii)</td><td>:</td>
			<td><input type=text style='width:245px;' autocomplete=off  class=myinputtext id=tembusan3 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['tembusan'] . "(iiii)</td><td>:</td>
			<td><input type=text style='width:245px;' autocomplete=off class=myinputtext id=tembusan4 size=25 maxlength=35 onkeypress=\"return tanpa_kutip(event);\"></td> 
		</tr>	 	 	 	 
	</table>
	 
	<center>
		<button class=mybutton onclick=saveSP()>" . $_SESSION['lang']['save'] . "</button>
		<button class=mybutton onclick=batal()>" . $_SESSION['lang']['new'] . "</button>
	</center>
 </fieldset>";
$frm[1] = "<fieldset>
           
          <fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
          " . $_SESSION['lang']['caripadanama'] . " : 
          <input type=text id=txtbabp size=25 class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=9>
          <button class=mybutton onclick=cariSP(0)>" . $_SESSION['lang']['find'] . "</button>
          </fieldset>
		  <fieldset>
		  <legend>" . $_SESSION['lang']['list'] . "</legend>
          <table class=sortable cellspacing=1 border=0 width=100%>
      		<thead>
          		<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['nomorsk'] . "</td>
					<td align=center>" . $_SESSION['lang']['karyawan'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggalsurat'] . "</td>
					<td align=center>" . $_SESSION['lang']['tanggalsampai'] . "</td>
					<td align=center>" . $_SESSION['lang']['tipetransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['dbuat_oleh'] . "</td>
					<td align=center>Action</td>
          		</tr>
          	</head>
           		<tbody id=containerlist>
           		</tbody>
           </table>
         </fieldset>
         </fieldset>";

$hfrm[0] = $_SESSION['lang']['form'];
$hfrm[1] = $_SESSION['lang']['list'];

drawTab('FRM', $hfrm, $frm, 100, 900);
CLOSE_BOX();
echo close_body('');
?>
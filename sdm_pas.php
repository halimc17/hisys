<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_pas.js?v=<?php echo time(); ?>'></script>
<?


$optlok = $optjab = $opttahun = $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdept = "<option value=''>&nbsp;</option>";

$dept=getKary($_SESSION['standard']['userid'],'bagian');

$str = "SELECT * FROM ".$dbname.".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $val){
	$s="";
	if($dept==$val['kode'] and empty($_SESSION['approval']['pas'])){
		$s="selected";
	}
	$optdept .= "<option value='".$val['kode']."' ".$s.">".$val['nama']."</option>";
}

$optunit = "<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' and inti='1' ORDER BY induk";
$res = fetchdata($str);
foreach($res as $val){
	$d=getNamaOrg($val['kodeorganisasi'],'induk');
	if($d!=$n){			
		$optunit.="<optgroup label='".getNamaOrg($d)."'>";
	}
	$optunit .= "<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){
		$optunit.="</optgroup>";
	}
}

$optgol = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5golongan where aktif='1' ORDER BY namagolongan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$nmgol[$val['kodegolongan']]=$val['namagolongan'];
	$optgol .= "<option value='".$val['kodegolongan']."'>".$val['namagolongan']."</option>";
}

$str = "SELECT DISTINCT left(periode,4) as tahun FROM ".$dbname.".setup_periodeakuntansi ORDER BY periode DESC";
$res = fetchdata($str);
foreach($res as $val){
	$opttahun .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
}
$opttahunx= "<option value='".date('Y')."'>".date('Y')."</option>";

OPEN_BOX('','<span class=judul>'.getMenu('sdm_pas').'</span>');
$optnilai.="<option value='Q1'>Q1</option>";
$optnilai.="<option value='Q2'>Q2</option>";
$optnilai.="<option value='Q3'>Q3</option>";
$optnilai.="<option value='Q4'>Q4</option>";

echo "<table>
   		<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;display:none;' onclick=newdata()>
	  			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
	  		</td>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
	  			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
	  		</td>
	 		<td>

			<fieldset>
 			<legend id=legend>Find</legend>
	 			<table>
	 				<tr>
						<td>".$_SESSION['lang']['namakaryawan']."</td>
					  	<td>:</td>
					  	<td><input class=myinputtext id=scnama style='width:150px;'></td>

	 					<td>".$_SESSION['lang']['departemen']."</td>
	 					<td>:</td>
					  	<td>
					  		<select id='scdept' class='select2' style='width:150px;'>
					  			".$optdept."
					  		</select>&nbsp;
					  	</td>

						<td>".$_SESSION['lang']['tahun']."</td>
					  	<td>:</td>
					  	<td>
					  		<select id='scthn' class='select2' style='width:150px;'>
					  			".$opttahunx."
					  		</select>&nbsp;
					  	</td>
						<td>".$_SESSION['lang']['penilaian']."</td>
					  	<td>:</td>
					  	<td>
							<select onchange=loaddata(); id='scpenilaian' class='select2' style='width:150px;'>
							<option value=''>".$_SESSION['lang']['all']."</option>
					  			".$optnilai."
					  		</select>&nbsp;
					  	</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['lokasitugas']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scunit' class='select2' style='width:155px;'>
					  			".$optunit."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['kodegolongan']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scgol' class='select2' style='width:150px;'>
					  			".$optgol."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['posting']."</td>
	 					<td>:</td>
					  	<td>
							<select onchange=loaddata(); id='scpost' class='select2' style='width:150px;'>
					  			<option value=''>".$_SESSION['lang']['all']."</option>
					  			<option value='0'>Belum Posting</option>
					  			<option value='1'>Posting</option>
					  			<option value='2'>Approve</option>
					  		</select>
					  	</td>
						<td>".$_SESSION['lang']['atasan']."</td>
					  	<td>:</td>
					  	<td><input class=myinputtext id=scatasan style='width:145px;' onkeypress='enterkey(event,loaddata)'></td>
						
						
					</tr>
					<tr>	
					  	<td></td>
					  	<td></td>
				  		<td colspan=20>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
				  		";
						if(!empty($_SESSION['approval']['pas'])){
							echo"<button class=mybutton style=color:red;border-color:blue;><a href=\"javascript:do_load('log_approval')\" title='Approval'>Kembali ke Menu Approval</a></button>";
						}
				  	echo"</td>
					</tr>
				</table>
			</fieldset>

			</td>
		</tr>
	 </table>"; 

CLOSE_BOX();

echo"<div id=listkriteria style=display:none>";
OPEN_BOX('','<span class=judul></span>');
echo "<div id=container class=freezetbl></div>";
CLOSE_BOX();
echo"</div>";

#= buat data tersimpan
echo"<div id=loadpreview>";
OPEN_BOX('','<span class=judul></span>');

echo "<div id=listdata class=freezetblload><script>loaddata(0);</script></div>";
echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>
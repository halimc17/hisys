<?php
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_disiplin.js?v=<?php echo time(); ?>'></script>
<?


$optlok = $optjab = $opttahun = $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdept = "<option value=''>&nbsp;</option>";

$str = "SELECT * FROM ".$dbname.".sdm_5departemen";
$res = fetchdata($str);
foreach($res as $val){
	$optdept .= "<option value='".$val['kode']."'>".$val['nama']."</option>";
}

$str = "SELECT * FROM ".$dbname.".sdm_5jabatan";
$res = fetchdata($str);
foreach($res as $val){
	$optjab .= "<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";
}

$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res = fetchdata($str);
foreach($res as $val){
	$optlok.= "<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}

// $optlok .= orgDetailuser($_SESSION['standard']['username'], 1);


$str = "SELECT DISTINCT left(periode,4) as tahun FROM ".$dbname.".setup_periodeakuntansi ORDER BY periode DESC";
$res = fetchdata($str);
foreach($res as $val){
	$opttahun .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
	$opttahunx .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
}

//$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABGM'";
$bar=fetchdata($str)[0];
$jab=explode(',',$bar['nilai']);
foreach($jab as $list => $isi){
	$arrjabGM[$isi]=$isi;
}

		//echo $_SESSION['empl']['tipelokasitugas'];
$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
//bukan orang HO / RO
if ($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL') {
	if (in_array($_SESSION['empl']['kodejabatan'], $arrjabGM)) {
		$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";
	}else{
		$whereKary.= " and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe not in ('HOLDING','KANWIL'))";
	}
	
}else{
//orang HO dan RO
	if (in_array($_SESSION['empl']['kodejabatan'], $arrjabGM)) {
		$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";
	}else{
		// sesuai departemen
		$whereKary.= " and kodegolongan in (select kodegolongan from ".$dbname.".sdm_5golongan where namagolongan < '".$nmgol[$_SESSION['empl']['kodegolongan']]."') and bagian='".$_SESSION['empl']['bagian']."' and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where  tipe  in ('HOLDING','KANWIL'))";
	}
}


# ini untuk orang HCM, orang HCM punya akses bisa untuk melihat semua KPI, jika ini di komen maka saat edit datanya jadi errorr
if($_SESSION['empl']['bagian']=='HCM' and $_SESSION['empl']['tipekaryawan']=='0'){
	if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$whereKary= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas not like '%HO')";
	}
}

# ini untuk atasan karyawan
$whereKary.=" or karyawanid in (select karyawanid from ".$dbname.".sdm_kpi where namaatasan='".$_SESSION['standard']['userid']."')";

$whereKary.= " and tipekaryawan in ('1','0')";
/*echo $_SESSION['empl']['kodeorganisasi'];*/
$str = "SELECT * FROM ".$dbname.".datakaryawan where 1=1 ".$whereKary." ORDER BY lokasitugas, tipekaryawan, namakaryawan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$d=$val['lokasitugas'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optkar.="<optgroup label='".$nmorg[$d]."'>";
	}
	$optkar .= "<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']."</option>";
	$n=$d;
	if($d!=$n){
		$optkar.="</optgroup>";
	}
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_disiplin').'</span>');
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
// $optunit .= orgDetailuser($_SESSION['standard']['username'], 1);

$optnilai.="<option value='Q1'>Q1</option>";
$optnilai.="<option value='Q2'>Q2</option>";
$optnilai.="<option value='Q3'>Q3</option>";
$optnilai.="<option value='Q4'>Q4</option>";

$optgol = "<option value=''>&nbsp;</option>";
$str = "SELECT * FROM ".$dbname.".sdm_5golongan where aktif='1' ORDER BY namagolongan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$nmgol[$val['kodegolongan']]=$val['namagolongan'];
	$optgol .= "<option value='".$val['kodegolongan']."'>".$val['namagolongan']."</option>";
}

echo "<table>
   		<tr valign=middle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=newdata()>
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
	 					
						<td>".$_SESSION['lang']['tahun']."</td>
					  	<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scthn' class='select2' style='width:150px;'>
							<option value=''>".$_SESSION['lang']['all']."</option>
					  			".$opttahunx."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['unit']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scunit' class='select2' style='width:155px;'>
					  			".$optunit."
					  		</select>&nbsp;
					  	</td>
					</tr>
					<tr>
						
						<td>".$_SESSION['lang']['posting']."</td>
	 					<td>:</td>
					  	<td>
							<select onchange=loaddata(); id='scpost' class='select2' style='width:150px;'>
					  			<option value=''>".$_SESSION['lang']['all']."</option>
					  			<option value='0'>Belum Posting</option>
					  			<option value='1'>Posting</option>
					  		</select>
					  	</td>
						<td ></td>
					  	<td ></td>
					  	<td ></td>
					</tr>
					
					<tr>
				  		<td></td><td></td>
				  		<td colspan=20>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
				  		";
							
				  	echo"</td>
					</tr>
				</table>
			</fieldset>

			</td>
		</tr>
	 </table>"; 

CLOSE_BOX();

$optnilai = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optnilai.="<option value='Q1'>Q1</option>";
$optnilai.="<option value='Q2'>Q2</option>";
$optnilai.="<option value='Q3'>Q3</option>";
$optnilai.="<option value='Q4'>Q4</option>";

$bln = range(1,12);

foreach($bln as $bulan){
	$bulan = addZero($bulan,2);
	
	$optperiode.= "<option value='".$bulan."'>".numToMonth($bulan,"I","long")."</option>";
}

$manmg="<option value='Y'>YA</option>";
$manmg.="<option value='N'>TIDAK</option>";


echo "<div id=entry style=display:none>";
OPEN_BOX();

echo "<table style=margin-top:10px>";
	echo "<tr>";
		echo "<td valign=top>";
			echo "<fieldset><legend>Form</legend>";
			echo "<table border=0>";
			echo "<tr>
					<td>".$_SESSION['lang']['unit']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='unit' class='select2' style='width:400px;' onchange='ambilkary()'>".$optlok."</select>&nbsp;
				  	</td>
					
					
				</tr><tr>
					
					<td>".$_SESSION['lang']['tahun']."</td>
				  	<td>:</td>
				  	<td>
				  		<select id='thnnilai' class='select2' style='width:90px;' onchange='ambilkary()'>".$opttahun."</select>&nbsp;
				  	</td>
					
					
				</tr><tr>
					
					<td>".$_SESSION['lang']['karyawan']."</td>
				  	<td>:</td>
				  	<td>
				  		<select id='karyawanid' class='select2' style='width:400px;'></select>&nbsp;
				  	</td>
					
					
				</tr><tr>
					
					<td>Tanggal</td>
				  	<td>:</td>
				  	<td><input type=text id=tglnilai class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:87px; value=\"".date("d-m-Y")."\" disabled>&nbsp;</td>
				</tr>";
			echo "<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=method value=insert>
						<input type=hidden id=noid value=''>
						<button id=simpanheader class=mybutton onclick=\"simpan();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"reset();\">".$_SESSION['lang']['cancel']."</button>
						<!--<button class=mybutton onclick=\"loaddatadetail();\">Refresh</button>-->
					</td>
				</tr>";
			echo "</table>";
			echo "</fieldset>";
		echo "</td>";
	echo "</tr>";
echo "</table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=listkriteria style=display:none>";
OPEN_BOX('','<span class=judul></span>');

echo "<div id=container class=freezetbl></div>";
// echo"<br>";

CLOSE_BOX();
echo"</div>";

#= buat data tersimpan
echo"<div id=loadpreview>";
OPEN_BOX('','<span class=judul></span>');

echo "<div id=listdata class=freezetblload  style=height:65vh><script>loaddata(0);</script></div>";
echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>
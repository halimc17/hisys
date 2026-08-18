<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src=js/formTable.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/sdm_coreman.js?v=<?php echo time(); ?>></script>

<style>
	.freezetbl {
		position: relative;
		max-height: 350px;
	}
	.freezetbl thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 2;
	}

	.freezetblload {
		position: relative;
		max-height: 550px;
	}
	.freezetblload thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 2;
	}

	.detailfix {
		position: relative;
		max-height: 550px;
	}
	.detailfix thead {
	  position: -webkit-sticky;
	  position: sticky;
	  top: 0;
	  z-index: 1;
	}

	.select {
		color: red !important;
	}

	.unselect {
		color: black !important;
	}
</style>

<?

$opttahun = $optkar = $atasan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdept = "<option value=''>&nbsp;</option>";

$arrnilai=array('Q1','Q2','Q3','Q4');
foreach($arrnilai as $nilai){	
	$sel="";
	if($_SESSION['kpi']['penilaian']==$nilai){
		$sel="selected";
	}
	$optnilai.="<option value='".$nilai."' ".$sel.">".$nilai."</option>";
}

$str = "SELECT DISTINCT kode, nama FROM ".$dbname.".sdm_5departemen ORDER BY nama ASC";
$res = fetchdata($str);
foreach($res as $val){
	$sel="";
	if($_SESSION['kpi']['dept']==$val['kode']){
		$sel="selected";
	}
	$optdept .= "<option value='".$val['kode']."' ".$sel.">".$val['nama']."</option>";
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
	$sel="";
	if($_SESSION['kpi']['tahun']==$val['tahun']){
		$sel="selected";
	}
	$opttahun .= "<option value='".$val['tahun']."' ".$sel.">".$val['tahun']."</option>";
}

$opttahuncari= "<option value='".date('Y')."'>".date('Y')."</option>";
$golongan = $nmgol[$_SESSION['empl']['kodegolongan']];

//$whereKary.= " and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
/*$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";*/
$whereKary.= " and a.tipekaryawan in ('1','0')";

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABGM'";
$bar=fetchdata($str)[0];
$jab=explode(',',$bar['nilai']);
foreach($jab as $list => $isi){
	$arrjabGM[$isi]=$isi;
}

//bukan orang HO / RO
if ($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL') {
	if (in_array($_SESSION['empl']['kodejabatan'], $arrjabGM)) {
		$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";
	}else{
		$whereKary.= " and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe not in ('HOLDING','KANWIL'))";
		$whereKary.= " and lokasitugas = '".$_SESSION['empl']['lokasitugas']."'";
	}
}else{
//orang HO dan RO
	if (in_array($_SESSION['empl']['kodejabatan'], $arrjabGM)) {
		$whereKary.= " and lokasitugas in (".getOrgDetail(2).")";
	}elseif($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$whereKary.= " and (a.kodegolongan in (select kodegolongan from ".$dbname.".sdm_5golongan where namagolongan < '".$nmgol[$_SESSION['empl']['kodegolongan']]."'))";
	}else{
		// sesuai departemen
		$whereKary.= " and a.kodegolongan in (select kodegolongan from ".$dbname.".sdm_5golongan where namagolongan < '".$nmgol[$_SESSION['empl']['kodegolongan']]."') and bagian='".$_SESSION['empl']['bagian']."'";
	}
	if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){		
		$whereKary.= " and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe not in ('HOLDING'))";
	}
}


# ini untuk orang HCM, orang HCM punya akses bisa untuk melihat semua KPI, jika ini di komen maka saat edit datanya jadi errorr
$userhcm=[];
$str = "select * from ".$dbname.".setup_parameterappl where kodeparameter='KPI'";
$req = fetchdata($str);
foreach($req as $val){
	$arrusertemp=explode(",",$val['nilai']);				
	foreach($arrusertemp as $uname){					
		$userhcm[$uname]=$uname;
	}
}
if($userhcm[$_SESSION['standard']['userid']]!=''){
	if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$whereKary= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas not like '%HO')";
	}
}

# pastikan hanya golongan dibawahnya saja yang muncul
$whereKary.= " and a.kodegolongan in (select kodegolongan from ".$dbname.".sdm_5golongan where namagolongan < '".$nmgol[$_SESSION['empl']['kodegolongan']]."')";

# ini untuk atasan karyawan
$whereKary.=" and (1=1 or karyawanid in (select karyawanid from ".$dbname.".sdm_corevalueandmanmanagement where namaatasan='".$_SESSION['standard']['userid']."'))";

$sel="";
if($_SESSION['kpi']['karyawanid']==$_SESSION['standard']['userid']){
	$sel="selected";
}

	
$optkar.="<optgroup label='".getNamaOrg(getKary($_SESSION['standard']['userid'],'kodeorganisasi'))."'>";
$optkar.="<option value='".$_SESSION['standard']['userid']."' ".$sel.">".getKary($_SESSION['standard']['userid'],'nik')." - ".getKary($_SESSION['standard']['userid'],'namakaryawan')." (".$nmgol[getKary($_SESSION['standard']['userid'],'kodegolongan')].")</option>";
$optkar.="</optgroup>";
//jika karyawan non staff hanya dirinya sendiri

if($_SESSION['kpi']['karyawanid']!=''){
	$whereKary.=" AND (1=1 or karyawanid = '".$_SESSION['kpi']['karyawanid']."')";
}

if(!empty($_SESSION['approval']['cvmm'])){
	foreach($_SESSION['approval']['cvmm'] as $key => $value){
		$karyapproval[$value['karyawanid']]=$value['karyawanid'];
	}
	$whereKary = "and karyawanid in ('".implode("','",$karyapproval)."')";
}

if(getKary($_SESSION['standard']['userid'],'tipekaryawan')=='0'){	
	$str = "SELECT karyawanid,nik,namakaryawan, namagolongan,lokasitugas FROM ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5golongan b on a.kodegolongan=b.kodegolongan where 1=1 ".$whereKary." ORDER BY lokasitugas, namakaryawan ASC";
	$res = fetchdata($str);
	foreach($res as $val){
		$d=$val['lokasitugas'];
		if($d!=$n){			
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			$optkar.="<optgroup label='".$nmorg[$d]."'>";
		}
		$sel="";
		if($_SESSION['kpi']['karyawanid']==$val['karyawanid']){
			$sel="selected";
		}
		$optkar .= "<option value='".$val['karyawanid']."' ".$sel.">".$val['nik']." - ".$val['namakaryawan']." (".$val['namagolongan'].")</option>";
		$n=$d;
		if($d!=$n){
			$optkar.="</optgroup>";
		}
	}
}


$where = " and a.tipekaryawan in ('1','0') and statuskaryawan!='Keluar' and aktif='1' and b.tipekaryawan in ('0','1')";
$str = "SELECT karyawanid,nik,namakaryawan, namagolongan FROM ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5golongan b on a.kodegolongan=b.kodegolongan where 1=1 ".$where." and namagolongan >= '".$golongan."' ORDER BY lokasitugas, namakaryawan ASC";
$res = fetchdata($str);
foreach($res as $val){
	$d=$val['lokasitugas'];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$atasan.="<optgroup label='".$nmorg[$d]."'>";
	}
	$atasan .= "<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']."</option>";
	$n=$d;
	if($d!=$n){
		$atasan.="</optgroup>";
	}
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

OPEN_BOX('','<span class=judul>'.getMenu('sdm_coreman').'</span>');

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
	 					<td>".$_SESSION['lang']['jenis']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scjenis' class='select2' style='width:150px;'>
					  			<option value=''>".$_SESSION['lang']['pilihdata']."</option>
					  			<option value='corevalue'>Core Values</option>
					  			<option value='manmanagement'>Man Management</option>
					  		</select>
					  	</td>

						<td>".$_SESSION['lang']['namakaryawan']."</td>
					  	<td>:</td>
					  	<td colspan=4>
					  		<input id='scnama' class='myinputtext' style='width:150px;' onkeypress='enterkey(event,loaddata)' value=\"".getKary($_SESSION['kpi']['karyawanid'])."\">
					  	</td>

	 					<td>".$_SESSION['lang']['departemen']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scdept' class='select2' style='width:150px;'>
					  			".$optdept."
					  		</select>&nbsp;
					  	</td>

	 					<td>".$_SESSION['lang']['posting']."</td>
	 					<td>:</td>
					  	<td>
							<select onchange=loaddata(); id='scpost' class='select2' style='width:150px;'>
					  			<option value=''>".$_SESSION['lang']['all']."</option>
					  			<option value='0'>Belum Posting</option>
					  			<option value='1'>Posting</option>
					  		</select>
					  	</td>

					</tr>
					
					<tr>
	 					<td>".$_SESSION['lang']['lokasitugas']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scunit' class='select2' style='width:150px;'>
					  			".$optunit."
					  		</select>&nbsp;
					  	</td>

						<td>".$_SESSION['lang']['penilaian']."</td>
					  	<td>:</td>
					  	<td colspan=4>
							<select onchange=loaddata(); id='scpenilaian' class='select2' style='width:155px;'>
							<option value=''>".$_SESSION['lang']['all']."</option>
					  			".$optnilai."
					  		</select>&nbsp;
					  	</td>
						<td>".$_SESSION['lang']['tahun']."</td>
					  	<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scthn' class='select2' style='width:150px;'>".$opttahuncari."
					  		</select>&nbsp;
					  	</td>
						<td>".$_SESSION['lang']['kodegolongan']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scgol' class='select2' style='width:150px;'>
					  			".$optgol."
					  		</select>&nbsp;
					  	</td>
					</tr>
					<tr>
						<td></td><td></td>
				  		<td colspan=20>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
				  		";
						if(!empty($_SESSION['approval']['cvmm'])){
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

echo "<div id=entry style=display:none>";
OPEN_BOX('','<span class=judul>'.$_SESSION['lang']['entryForm'].'</span>');

echo "<table style=margin-top:10px>";
	echo "<tr>";
		echo "<td valign=top>";
			echo "<fieldset>";
			echo "<table border=0>";
			echo "<tr>

					<td>".$_SESSION['lang']['tahun']." Penilaian</td>
				  	<td>:</td>
				  	<td>
				  		<select id='thnnilai' class='select2' style='width:85px;'>
				  			".$opttahun."
				  		</select>&nbsp;
				  	</td>

					<td>Tanggal</td>
				  	<td>:</td>
				  	<td><input type=text id=tglnilai class=myinputtext onmousemove=setCalendar(this.id); maxlength=10 style=width:105px; value=\"".date("d-m-Y")."\" disabled>&nbsp;</td>

					
					<td>".$_SESSION['lang']['nama']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='nama' class='select2' style='width:272px;' onchange=\"getDept(this.value, 'dept');\">".$optkar."</select>&nbsp;
				  	</td>
				</tr>";
			echo "<tr>

					<td>Penilaian</td>
				  	<td>:</td>
				  	<td>
				  		<select id='penilaian' class='select2' style='width:85px;'>".$optnilai."</select>
				  	</td>

 					<td>".$_SESSION['lang']['jenis']."</td>
				  	<td>:</td>
				  	<td>
				  		<select id='tipe' class='select2' style='width:108px;' onchange=loadbytipe('','jenis')>
				  			<option id=cv value='corevalue'>Core Values</option>
				  			<option id=mm value='manmanagement'>Man Management</option>
				  		</select>
				  	</td>
					
					<td>".$_SESSION['lang']['departemen']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='dept' class='select2' style='width:272px;' disabled>
				  			".$optdept."
				  		</select>&nbsp;
				  	</td>
					
				</tr>";
			echo "<tr>
					<td valign=top>Kekuatan</td>
				  	<td valign=top>:</td>
				  	<td colspan=4>
				  		<textarea id=kekuatan rows=6 cols=30></textarea>&nbsp;
				  	</td>

					<td valign=top>Kelemahan</td>
				  	<td valign=top>:</td>
				  	<td colspan=4>
				  		<textarea id=kelemahan rows=6 cols=32></textarea>&nbsp;
				  	</td>
				</tr>";
			echo "<tr>
					<td colspan=2></td>
					<td colspan=4>
						<input type=hidden id=method value=insert>
						<input type=hidden id=id value=''>
						<button class=mybutton id=tombolsimpan onclick=\"simpan();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"reset();\">".$_SESSION['lang']['cancel']."</button>
					</td>
					
					<td>Atasan Penilai</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='atasan' class='select2' style='width:272px;'>
				  			".$atasan."
				  		</select>&nbsp;
				  	</td>
					
				</tr>";
			echo "</table>";
			echo "</fieldset>";
		echo "</td>";

		echo "<td valign=top>";
			echo "<fieldset style=height:100%>";
			echo "<table id=kriteria>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";
echo "</table>";

CLOSE_BOX();
echo "</div>";

echo"<div id=listkriteria style=display:none>";
OPEN_BOX('','<span class=judul></span>');

//echo "<div id=container class=freezetbl></div>";
echo "<div id=container></div>";
echo"<br>";

CLOSE_BOX();
echo"</div>";

// echo"<pre>";
// print_r($_SESSION['kpi']);
// echo"</pre>";
#= buat data tersimpan
echo"<div id=loadpreview>";
OPEN_BOX('','<span class=judul></span>');
if(!empty($_SESSION['kpi'])){
	$str = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE tahun='".$_SESSION['kpi']['tahun']."' and karyawanid='".$_SESSION['kpi']['karyawanid']."' and jenis='corevalue' and penilaian='".$_SESSION['kpi']['penilaian']."'";
	$res = fetchdata($str);
	if(!empty($res)){
		$clickdata="<script>loaddata(0);</script>";
	}else{		
		$clickdata="<script>newdata('fromkpi');</script>";
	}
}else{	
	$clickdata="<script>loaddata(0);</script>";
}
echo "<div id=listdata class=freezetblload>".$clickdata."</div>";
echo"<br>";

CLOSE_BOX();
echo"</div>";
#= tutup data tersimpan

echo close_body();
?>
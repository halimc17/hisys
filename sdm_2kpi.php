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
<script language=javascript src='js/sdm_2kpi.js?v=<?php echo time(); ?>'></script>
<?


$optlok = $optjab = $opttahun = $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optdept = "<option value=''>&nbsp;</option>";

$str = "SELECT * FROM ".$dbname.".sdm_5departemen";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']!='SDM'){
// 	$str = "SELECT * FROM ".$dbname.".sdm_5departemen where kode='".$_SESSION['empl']['bagian']."'";	
// }

$res = fetchdata($str);
foreach($res as $val){
	$optdept .= "<option value='".$val['kode']."'>".$val['nama']."</option>";
}

$str = "SELECT * FROM ".$dbname.".sdm_5jabatan";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']!='SDM'){
// 	$str = "SELECT * FROM ".$dbname.".sdm_5jabatan where kodejabatan='".$_SESSION['empl']['jabatan']."'";	
// }

$res = fetchdata($str);
foreach($res as $val){
	$optjab .= "<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";
}


// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']!='SDM'){
// 	$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";	
// }else
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4'";	
}else{
	$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
}

$res = fetchdata($str);
foreach($res as $val){
	$optlok.= "<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}


$str = "SELECT DISTINCT left(periode,4) as tahun FROM ".$dbname.".setup_periodeakuntansi ORDER BY periode DESC";
$res = fetchdata($str);
foreach($res as $val){
	$opttahun .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
	$opttahunx .= "<option value='".$val['tahun']."'>".$val['tahun']."</option>";
}

// $whereKary.= " ";

$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABGM'";
$bar=fetchdata($str)[0];
$jab=explode(',',$bar['nilai']);
foreach($jab as $list => $isi){
	$arrjabGM[$isi]=$isi;
}

		//echo $_SESSION['empl']['tipelokasitugas'];
$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
//bukan orang HO / RO
// if ($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL') {
// 	$whereKary.= " and lokasitugas ='".$_SESSION['empl']['lokasitugas']."'  ";
	
// }elseif ($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']!='SDM') {
// 	$whereKary.= " and karyawanid ='".$_SESSION['standard']['userid']."'";
	
// }else{
// //orang HO dan RO
// 	if($_SESSION['empl']['bagian']=='SDM'){
		$whereKary= " ";
// 	}else{
// 		$whereKary.= "  and lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where  tipe  in ('HOLDING','KANWIL'))";

// 	}
// }


$whereKary.= " and tipekaryawan in ('1','0') and (tanggalkeluar='0000-00-00' or tanggalkeluar='') and lokasitugas in (".getOrgDetail(2).")";
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

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2kpi').'</span>');
$optunit = "<option value=''>".$_SESSION['lang']['all']."</option>";

if($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']!='SDM'){
	$str = "SELECT * FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY induk";	
}elseif($_SESSION['empl']['tipelokasitugas']=='HOLDING' and $_SESSION['empl']['bagian']=='SDM'){
	$str = "SELECT * FROM ".$dbname.".organisasi where length(kodeorganisasi)='4' ORDER BY induk";	
}else{
	$str = "SELECT * FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY induk";
}

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


$optnilai.="<option value='1'>Januari</option>";
$optnilai.="<option value='2'>Febuari</option>";
$optnilai.="<option value='3'>Maret</option>";
$optnilai.="<option value='4'>April</option>";
$optnilai.="<option value='5'>Mei</option>";
$optnilai.="<option value='6'>Juni</option>";
$optnilai.="<option value='7'>Juli</option>";
$optnilai.="<option value='8'>Agustus</option>";
$optnilai.="<option value='9'>September</option>";
$optnilai.="<option value='10'>Oktober</option>";
$optnilai.="<option value='11'>November</option>";
$optnilai.="<option value='12'>Desember</option>";

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
						<td>".$_SESSION['lang']['namakaryawan']."</td>
					  	<td>:</td>
					  	<td><input class=myinputtext id=scnama style='width:150px;' onkeypress='enterkey(event,loaddata)'></td>

	 					<td>".$_SESSION['lang']['departemen']."</td>
	 					<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scdept' class='select2' style='width:150px;'>
					  			".$optdept."
					  		</select>&nbsp;
					  	</td>
						
						<td>".$_SESSION['lang']['tahun']."</td>
					  	<td>:</td>
					  	<td>
					  		<select onchange=loaddata(); id='scthn' class='select2' style='width:150px;'>
							<option value=''>".$_SESSION['lang']['all']."</option>
					  			".$opttahunx."
					  		</select>&nbsp;
					  	</td>
						
						<td >".$_SESSION['lang']['penilaian']."</td>
					  	<td >:</td>
					  	<td >
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
					  		</select>
					  	</td>
						<td hidden>".$_SESSION['lang']['atasan']."</td>
					  	<td hidden>:</td>
					  	<td hidden><input class=myinputtext id=scatasan style='width:145px;' onkeypress='enterkey(event,loaddata)'></td>
					</tr>
					
					<tr>
				  		<td></td><td></td>
				  		<td colspan=20>
				  			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
				  		";
							if(!empty($_SESSION['approval']['kpi'])){
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

$optnilai = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optnilai.="<option value='1'>Januari</option>";
$optnilai.="<option value='2'>Febuari</option>";
$optnilai.="<option value='3'>Maret</option>";
$optnilai.="<option value='4'>April</option>";
$optnilai.="<option value='5'>Mei</option>";
$optnilai.="<option value='6'>Juni</option>";
$optnilai.="<option value='7'>Juli</option>";
$optnilai.="<option value='8'>Agustus</option>";
$optnilai.="<option value='9'>September</option>";
$optnilai.="<option value='10'>Oktober</option>";
$optnilai.="<option value='11'>November</option>";
$optnilai.="<option value='12'>Desember</option>";

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
					<td>".$_SESSION['lang']['nama']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='nama' class='select2' style='width:258px;' onchange=\"getDept(this.value, 'dept');\">
				  			".$optkar."
				  		</select>&nbsp;
				  	</td>
					
					<td>".$_SESSION['lang']['jabatan']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='jabatan' class='select2' style='width:258px;' disabled>".$optjab."</select>&nbsp;
				  	</td>
				</tr>";
			echo "<tr>
					<td>".$_SESSION['lang']['lokasitugas']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='lokasitugas' class='select2' style='width:258px;' disabled>".$optlok."</select>&nbsp;
				  	</td>
					
					
					<td>".$_SESSION['lang']['departemen']."</td>
				  	<td>:</td>
				  	<td colspan=4>
				  		<select id='dept' class='select2' style='width:258px;' disabled>".$optdept."</select>&nbsp;
				  	</td>
				</tr><tr>
					<td >Penilaian</td>
				  	<td >:</td>
				  	<td >
				  		<select id='penilaian' class='select2' style='width:100px;' >".$optnilai."</select>
				  	</td>
					
					<td>".$_SESSION['lang']['tahun']."</td>
				  	<td>:</td>
				  	<td>
				  		<select id='thnnilai' class='select2' style='width:90px;'>".$opttahun."</select>&nbsp;
				  	</td>
					<td></td>
				  	<td></td>
				  	<td>
				  	</td>
					<td hidden>".$_SESSION['lang']['periode']."</td>
				  	<td hidden>:</td>
				  	<td hidden>
				  		<select id='bulandr' class='select2' disabled style='width:110px;'>".$optperiode."</select>
				  	</td>
				  	<td hidden>s / d</td>
				  	<td hidden>
				  		<select id='bulansd' class='select2' disabled style='width:110px;'>".$optperiode."</select>
				  	</td>
					
				</tr><tr>
					<td hidden>Man Management</td>
				  	<td hidden>:</td>
				  	<td hidden>
				  		<select id='manmanagement' class='select2' style='width:100px;'>".$manmg."</select>&nbsp;
				  	</td>
					<td>Tanggal</td>
				  	<td>:</td>
				  	<td><input type=text id=tglnilai class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:87px; value=\"".date("d-m-Y")."\" disabled>&nbsp;</td>
				</tr>";
			// echo "<tr>
					// <td valign=top>Kekuatan</td>
				  	// <td valign=top>:</td>
				  	// <td>
				  		// <textarea id=kekuatan rows=6 cols=30></textarea>&nbsp;
				  	// </td>

					// <td valign=top>Kelemahan</td>
				  	// <td valign=top>:</td>
				  	// <td colspan=4>
				  		// <textarea id=kelemahan rows=6 cols=32></textarea>&nbsp;
				  	// </td>
				// </tr>";
			echo "<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=method value=insert>
						<input type=hidden id=id value=''>
						<button class=mybutton onclick=\"simpan();\">".$_SESSION['lang']['save']."</button>
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
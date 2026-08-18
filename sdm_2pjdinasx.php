<?
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language=javascript src='js/sdm_2pjdinasx.js?v=<?php echo time(); ?>'></script>
<?php
$_SESSION['rute']=array();

OPEN_BOX('','<span class=judul>'.getMenu('sdm_2pjdinasx').'</span><br>');

$optkary="<option value=''>".$_SESSION['lang']['all']."</option>";
@$wh.=" and karyawanid in (select karyawanid from ".$dbname.".sdm_pjdinasht)";
$str="select * from ".$dbname.".datakaryawan where 1=1 ".$wh." order by namakaryawan asc";
$res=fetchdata($str);
foreach($res as $bar){
	$optkary.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']."</option>";
}


if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	#tidak ada apa apa disini, alias munculkan semua
	$where="";
} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	#hanya ro ke bawah
	$where=" and kodeorganisasi in (select kodeorganisasi from ".$dbname.".organisasi where tipe!='HOLDING' and length(kodeorganisasi)='4')";
} else {
	$where=" and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
}			


$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' ".$where."";
$res=fetchdata($str);
// echo "<pre>"; print_r($res); exit;
foreach($res as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorganisasi']."'");
	$d=$induk[$val['kodeorganisasi']];
	$n='';
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}
// echo "<pre>"; print_r($optunit); exit;


# get jenis biaya 
$sJenisBiaya = selectQuery($dbname, 'sdm_5jenisbiayapjdinas', '*', "keterangan NOT LIKE 'UANG MAKAN%'", 'id ASC');
$rJenisBiaya = fetchData($sJenisBiaya);

// count jenis biaya rows
$countJenisBiaya = count($rJenisBiaya) + 1;
$headJenisBiaya = '';
foreach ($rJenisBiaya as $value) {
	$headJenisBiaya .= "<th align=center>".$value['keterangan']."</th>";
}
//Umar
$headJenisBiaya .= "<th align=center>PREMI DRIVER</th>";
//End Umar

# option departemen
$optDepartemen = "<option value=''>".$_SESSION['lang']['all']."</option>";
$qDepartemen = selectQuery($dbname, 'sdm_5departemen', '*', "aktif = '1' ORDER BY nama ASC");
$resDepartemen = fetchData($qDepartemen);
foreach ($resDepartemen as $value) {
	$optDepartemen .= "<option value='".$value['kode']."'>".$value['kode']." - ".$value['nama']."</option>";
}

# option jabatan
$optJabatan = "<option value=''>".$_SESSION['lang']['all']."</option>";
$qJabatan = selectQuery($dbname, 'sdm_5jabatan', '*', "aktif = '1' ORDER BY namajabatan ASC");
$resJabatan = fetchData($qJabatan);
foreach ($resJabatan as $value) {
	$optJabatan .= "<option value='".$value['kodejabatan']."'>".$value['namajabatan']."</option>";
}

echo"<fieldset style=float:left><legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td><select class=select2 id=unit style='width:180px;'>".$optunit."</select>
			</td>
			
		</tr>
		<tr>
			<td>".$_SESSION['lang']['departemen']."</td>
			<td>:</td>
			<td><select class=select2 id=departemen style='width:180px'>".$optDepartemen."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jabatan']."</td>
			<td>:</td>
			<td><select class=select2 id=jabatan style='width:180px'>".$optJabatan."</select></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input type=text class=myinputtext placeholder=".$_SESSION['lang']['all']." id=notransaksilist nkeypress=\"return_tanpa_kutip(event);\" style=\"width:175px;\"/>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td><td>:</td>
			
			<td><select class=select2 id=namakarylist style='width:180px;'>".$optkary."</select>
				<img id='namakarylist' onclick=z.elSearch('namakarylist',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>:</td>
			<td><input type='text' class=myinputtext id=tanggaldari onmousemove=setCalendar(this.id) onkeypress=return false; style='width:75px' readonly /> s/d <input type='text' class=myinputtext id=tanggalsampai onmousemove=setCalendar(this.id) onkeypress=return false; style='width:75px' readonly /></td>
		</tr>
		
		";

echo"<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=excel()>".$_SESSION['lang']['excel']."</button>
			<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>";

echo"</fieldset><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();

echo"
	<div class='table-scroll'>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable>
			<thead>
				<tr class=rowheader>
					<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['jenis']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
					<th align=center colspan=3>Real ".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan=2>Unit ".$_SESSION['lang']['tujuan']."</th>
					<th align=center rowspan=2>Uang Muka</th>
					<th align=center colspan=".$countJenisBiaya.">Reimburse / Klaim</th>
					<th align=center rowspan=2>".$_SESSION['lang']['total']." Reimburse</th>
					<th align=center colspan=".$countJenisBiaya.">Verifikasi (dibayarkan)</th>
					<th align=center rowspan=2>".$_SESSION['lang']['total']." Verifikasi</th>
					<th align=center rowspan=2>Net Off Uang Muka</th>
					<th align=center rowspan=2>".$_SESSION['lang']['keterangan']."</th>
					<th align=center rowspan=2>Tiket Pesawat</th>
					<th align=center rowspan=2>Status Pengajuan</th>
					<th align=center rowspan=2>Status Pertanggungjawaban</th>
					<th align=center rowspan=2 colspan=3>Action</th>
				</tr>
				<tr>
					<th align=center>Dari</th>
					<th align=center>Sampai</th>
					<th align=right>Jumlah</th>
					".$headJenisBiaya."
					".$headJenisBiaya."

				</tr>
				
			</thead>
			<tbody id=contain> 
				
			</tbody>
			<tfoot id=footData>
			</tfoot>
		</table>
	</div>
		 
</div></fieldset>";
CLOSE_BOX();
echo"</div>";
echo close_body();
?>
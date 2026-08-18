<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_bafinger').'</span>');
require_once('lib/zSelect2.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language="javascript" src="js/sdm_bafinger.js?v=<?php echo time(); ?>"></script>
<?php

## Tipe BA
$optStatus="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrtipePersetujuan=array('0'=>'Belum Diajukan','1'=>'Disetujui','2'=>'Ditolak');
foreach($arrtipePersetujuan as $val => $value){
	$optStatus.="<option value='".$val."'>".$value."</option>";
}

##PENCARIAN
echo "<div id='action_list'>
	<table>
		<tr valign=middle>
			<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
				<img class=delliconBig src=images/newfile.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
			<td align=center style='width:100px;cursor:pointer;' onclick=showalllist(0)>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['find']."</legend>
					<table>
						<tr>
							<td>No. BA</td>
							<td>:</td>
							<td>
								<input type=text id=scnoba size=25 maxlength=30 class=myinputtext>
							</td>
							
							<td style='padding-left:10px'>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td>
								<input type='text' class='myinputtext' id='sctanggal' value='' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center' />
							</td>
							
							<td style='padding-left:10px'>".$_SESSION['lang']['namakaryawan']."</td>
							<td>:</td>
							<td>
								<input type=text id=scnama size=25 maxlength=30 class=myinputtext>
							</td>

							<td style='padding-left:10px'>".$_SESSION['lang']['status']."</td>
							<td>:</td>
							<td>
								<select class=select2 style=width:100px id='scstatusper'>".$optStatus."</select>
							</td>
							<td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button></td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table> 
</div>";

CLOSE_BOX();

echo "<div id=\"list_ba\">";
OPEN_BOX();

echo"
	
	<div style='overflow:auto;'>
	<table class='sortable' cellspacing='1' cellpadding=5 border='0' style='width:100%;'>
		<thead>
		<tr class=rowheader>
			<th align='center'>No.</th>
			<th align='center'>No. BA</th>
			<th align='center'>".$_SESSION['lang']['unit']."</th>
			<th align='center'>".$_SESSION['lang']['tanggal']."</th>
			<th align='center'>".$_SESSION['lang']['namakaryawan']."</th>
			<th align='center'>".$_SESSION['lang']['divisi']."</th>
			<th align='center'>".$_SESSION['lang']['tipe']."</th>
			<th align='center'>".$_SESSION['lang']['absensi']."</th>
			<th align='center'>".$_SESSION['lang']['keterangan']."</th>
			<th align='center'>".$_SESSION['lang']['jam']." <br> Masuk</th>
			<th hidden align='center'>".$_SESSION['lang']['jamistirahatdari']."</th>
			<th hidden align='center'>".$_SESSION['lang']['jamistirahatsampai']."</th>
			<th align='center'>".$_SESSION['lang']['jam']." <br> Pulang</th>
			<th align='center'>".$_SESSION['lang']['dbuat_oleh']." Oleh</th>
			<th align='center'>".$_SESSION['lang']['status']." Approval</th>
			<th align='center'>".$_SESSION['lang']['tanggal']." <br> Upload</th>
			<th align='center' colspan=5>Action</th>
		</tr>
		</thead>
		<tbody id='contain'>
			<script>loaddata(0)</script>
		</tbody>
	 </table>
	</div>
";

CLOSE_BOX();
echo"</div>";


echo"<div id='form_ba' style='display:none;'>";
OPEN_BOX();

##GET UNIT
$optunit='';
$arrorgdet = getOrgDetail(1);
$no=0;
foreach($arrorgdet as $key=>$val){
	$no++;
	if($no==1){
		$wunit=$key;
	}
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";	
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

##Tanggal
$as=date("Y-m-d");

$d="";
##GET KARYAWAN
$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select karyawanid,namakaryawan,nik, subbagian, lokasitugas from  ".$dbname.".datakaryawan where lokasitugas='".$wunit."' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') order by subbagian asc, namakaryawan asc ";
$res=fetchdata($str);
foreach($res as $val){
	if($val['subbagian']==''){
		$val['subbagian']=$val['lokasitugas'];
	}
	$d=$val['subbagian'];
	if($d!=$n){			
		$optkaryawan.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." [".$val['nik']."]</option>";
	$n=$d;
	if($d!=$n){			
		$optkaryawan.="</optgroup>";
	}
}

##GET ABSEN
$optabsen="";
$str="select kodeabsen,keterangan from ".$dbname.".sdm_5absensi where kodeabsen='H' order by keterangan asc";
$res=fetchdata($str);
foreach($res as $val){
	if($val['kodeabsen']=='H'){
		$optabsen.="<option value='".$val['kodeabsen']."' selected>".$val['keterangan']."</option>";
	}else{
		$optabsen.="<option value='".$val['kodeabsen']."'>".$val['keterangan']."</option>";
	}
}

##GET JAM
$optjam="";
for($t=0;$t<24;$t++){
	$optjam.="<option value='".addZero($t,2)."'>".addZero($t,2)."</option>";
}

##GET MENIT
$optmenit="";
for($t=0;$t<60;$t++){
	$optmenit.="<option value='".addZero($t,2)."'>".addZero($t,2)."</option>";
}

## Tipe BA
$arrtipeBA=array('1'=>'Jam Masuk Dan Keluar','2'=>'Jam Masuk','3'=>'Jam Keluar');
foreach($arrtipeBA as $val => $value){
	if($val=='1'){
		$optBA.="<option value='".$val."' selected>".$value."</option>";
	}else{
		$optBA.="<option value='".$val."'>".$value."</option>";
	}
}



echo"<fieldset>
	<legend>".$_SESSION['lang']['header']." <label id=lblmethod>(New)</lbl></legend>
	<table cellspacing='1' cellpadding=2 border='0'>
		<tr>
			<td style='vertical-align:top'>No. BA</td>
			<td style='vertical-align:top'>:</td>
			<td>
				<input type=text class=myinputtext disabled id='noba' style=width:195px; onkeypress=\"return tanpa_kutip(event)\" onkeydown=\"upperCaseF(this)\">
			</td>

			<td style='padding-left:20px;vertical-align:top' rowspan=5>
					<fieldset>
					<legend>UPLOAD FILE BA</legend>
					<table cellspacing='1' border='0'>
						<tr>
							<td colspan=3>=> Data karyawan yang terbaca ada data yang NIK upload = NIK yang ada di data karyawan</b>
								<br>=> Data karyawan yang terbaca ada data yang Lokasi Tugas sesuai dengan unit yang dipilih</b>
								<br>=> <b>Form Template dapat di download disini </b>&nbsp;<a href='fileupload/upload_ba_absensi.xlsx'  title='upload_ba_absensi.xlsx'>Klik disini untuk mendapatkan contoh file</a>
							</td>
						</tr>
						<tr>
				
						</tr>
						<tr hidden>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td>
								<input type='text' class='myinputtext' id='tanggalfile'  onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center'/>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['unit']."</td>
							<td>:</td>
							<td>
								<select class=select2 style=width:233px id='unitfile'>".$optunit."</select><button class=mybutton onclick=\"getkaryawanid()\">Get Karyawan</button>
							</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['file']." (.xlsx)</td>
							<td>:</td>
							<td>
								<input name='filex' type='file' id='filex' size='25' class='mybutton'>
							</td>
						</tr>
						<tr>
							<td colspan=2></td>
							<td>
								<input type='hidden' id='methods' value='insertfile' />
								<button class=mybutton onclick=\"simpanfile()\">".$_SESSION['lang']['save']."</button>
								<button class=mybutton onclick=\"batal()\">".$_SESSION['lang']['cancel']."</button>
							</td>
						</tr>
					</table>
					</fieldset>
				</td>

		</tr>
		<tr>
			<td>".$_SESSION['lang']['tanggal']." Absensi</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggal' value='".tanggalnormal($as)."' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:200px id='unit' onchange=\"getkaryawan()\">".$optunit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:200px id='karyawan' onchange=\"getShift()\">".$optkaryawan."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td>
				<input class=myinputtext id=keterangan style='width:195px;'>
			</td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['absensi']."</td>
			<td>:</td>
			<td>
				<select style=width:112px class=select2 id='absen'>".$optabsen."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jamMsk']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggaljammasuk' value='".tanggalnormal($as)."' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center' />
				<select class=select2 style=width:50px id='jam'>".$optjam."</select> : <select style=width:50px class=select2 id='mnt'>".$optmenit."</select>
			</td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['jamistirahatdari']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:50px id='jam2'>".$optjam."</select> : <select style=width:50px class=select2 id='mnt2'>".$optmenit."</select>
			</td>
		</tr>
		<tr hidden>
			<td>".$_SESSION['lang']['jamistirahatsampai']."</td>
			<td>:</td>
			<td>
				<select style=width:50px class=select2 id='jam3'>".$optjam."</select> : <select style=width:50px class=select2 id='mnt3'>".$optmenit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jamPlg']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tanggaljamkeluar' value='".tanggalnormal($as)."' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return tanpa_kutip(event)\" readonly style='width:80px;text-align:center' />
				<select style=width:50px class=select2 id='jam4'>".$optjam."</select> : <select style=width:50px class=select2 id='mnt4'>".$optmenit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select style=width:200px class=select2 id='tipeba'>".$optBA."</select></select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert' />
				<button class=mybutton onclick=\"simpan()\">".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=\"batal()\">".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo"</div>";

##div persetujuan##
echo"<div id='persetujuan' style='display:none;'>";
OPEN_BOX();
echo"<div id='persetujuandata'></div>";

CLOSE_BOX();
echo "</div>";
echo close_body();

?>
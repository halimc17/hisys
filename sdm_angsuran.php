<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

?>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/zMaster.js></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script language=javascript1.2 src=js/formTable.js></script>
<script language=javascript src=js/sdm_angsuran.js?v=<?php echo time(); ?>></script>
<script>
	getSelect2();
</script>

<?php

	OPEN_BOX('','<span class=judul>'.getMenu('sdm_angsuran').'</span>');
	echo"<div>";
	$optlokasitugas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$arrorgdet  = getOrgDetail(2);

	// $optjenisangsuran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select distinct lokasitugas from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and lokasitugas in (".$arrorgdet.") and statuskaryawan != 'Keluar'  order by namakaryawan asc";
	$rese=fetchData($str);
	foreach ($rese as $vale) {
		$optlokasitugas.="<option value=".$vale['lokasitugas'].">".$vale['lokasitugas']." - ".getNamaOrg($vale['lokasitugas'],'namaorganisasi')."</option>";
	} 

	
	$str="select komponengaji,jenisangsuran from ".$dbname.".sdm_angsuran_komponen where status='1'";
	$res=fetchdata($str);
	foreach($res as $val){
		$optjenisangsuran.="<option value='".$val['komponengaji']."'>".$val['jenisangsuran']."</option>";
	}
	echo   "<table cellspacing=1 border=0>
				<tbody>
					<tr valign=middle>
						<td style=width:100px;cursor:pointer; onclick=createNew() align=center>
							<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>
							".$_SESSION['lang']['new']."
						</td>
						<td style=width:100px;cursor:pointer; onclick=displayList() align=center>
							<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>
							".$_SESSION['lang']['list']."
							<td>
						</td>
						<td>
							<fieldset style='width:auto;'>
								<legend>".$_SESSION['lang']['find']."</legend>
								<table>
									<tr>
										<td align=left>".$_SESSION['lang']['notransaksi']."</td>
										<td>:</td>
										<td><input type=text id=notransaksisch onkeyup=loadData(); maxlength=20 class=myinputtext size=26 style=\"width:200px;\"></td>

										<td align=left>".$_SESSION['lang']['nama']."</td>
										<td>:</td>
										<td><input type=text id=namasch onkeyup=loadData(); maxlength=30 class=myinputtext size=26 style=\"width:200px;\"></td>

										<td align=left>".$_SESSION['lang']['lokasitugas']."</td>
										<td>:</td>
										<td>
										<select style='width:100px;' id='lokasitugassch' onchange='loadData()'>".$optlokasitugas."</select></td>

										<td align=left>".$_SESSION['lang']['jenis']." Angsuran</td>
										<td>:</td>
										<td>
										<select style='width:100px;' id='jenisangsuransch' onchange='loadData()'>".$optjenisangsuran."</select></td>

										<td></td>
										<td></td>
										<td>
											<button class=mybutton onclick=loadData()>".$_SESSION['lang']['find']."</button>
											<button class=mybutton onclick=displayList()>".$_SESSION['lang']['cancel']."</button>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>";
	echo"</div>";
	CLOSE_BOX();
?>


<?php

##Option Hours & Minute
$optbulan = '';
for ($z = -240; $z <= 24; $z++) {
    $da = mktime(0, 0, 0, date('m') - $z, '1', date('Y'));
	
	if(date('Y-m', $da)==date('Y-m')){
		$optbulan.="<option value='" . date('Y-m', $da) . "' selected>" . date('m-Y', $da) . "</option>";
	}else{
		$optbulan.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
	}
}

// $dakarbulanan=0;
// $strdkar = "select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan_hist where
// 		(tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and tipekaryawan in ('1','2','3','6') 
// 		and lokasitugas='".$_SESSION['empl']['lokasitugas']."'  and approval_status='8' and version_type='B'  and periodegaji='".substr($tgAbsn, 0,6)."'  order by namakaryawan asc"; 
// $resdkar = fetchdata($strdkar);
// if(count($resdkar)>0)
// { 
//   $dakarbulanan=1;
// }
$optkaryawan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrorgdet = getOrgDetail(2);
$str="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where
		(tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')  and statuskaryawan != 'Keluar' and tipekaryawan != 0 and lokasitugas in (".$arrorgdet.")  order by namakaryawan asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optkaryawan.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
}


$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


// $optjenisangsuran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// $opttipeangsuran="<option value=''>".$_SESSION['lang']['pilihdata']."</option>"; 
$opttipeangsuran.="<option value='angsuranjangkawaktu'>Angsuran Jangka Waktu</option>";  
$opttipeangsuran.="<option value='angsurannominal'>Angsuran Nominal</option>";  

echo "<div id=addNew style=display:none>";
	OPEN_BOX();
echo"<input type='hidden' id='method' name='method' value='insert'  />
<div id='headher'>
<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['form']." Header</legend>
<table cellspacing='1' cellpadding='3' border='0'>
		<tr>
		<td>".$_SESSION['lang']['notransaksi']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<input type='text' class='myinputtext' style='width:240px;' id='notransaksi' name='notransaksi' onkeypress='return tanpa_kutip(event);' style='width:150px;' disabled/>
		</td>

		<td>".$_SESSION['lang']['karyawan']." <font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<select style='width:245px;' class='select2' id='karyawanid' onchange='loadjenisangsuran();'>".$optkaryawan."</select>
			<img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
		</tr>
		<tr>
		<td>".$_SESSION['lang']['jenis']." Angsuran<font size=2px style=color:red><b>*</b></font></td>
		<td>:</td>
		<td>
			<select style='width:245px;' class='select2' id='jenisangsuran' >".$optjenisangsuran."</select>
		</td>

		<td>".$_SESSION['lang']['tipe']." Angsuran<font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<select style='width:245px;' class='select2' id='tipeangsuran' onchange='disableinputan()'>".$opttipeangsuran."</select>
			</td>
		</tr>
		<tr>
			
			<td>".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['awal']." <font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<select id='bulandari' name='bulandari' class='select2'  style='width:245px;'  onchange='cekdataubah()'>".$optbulan."</select>
			</td>

			<td>".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['akhir']." <font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<select id='bulansampai' name='bulansampai' class='select2'  style='width:245px;'  onchange='cekdataubah()'>".$optbulan."</select>
			</td>
		</tr>

		<tr>
			
			<td>".$_SESSION['lang']['total']." ".$_SESSION['lang']['hutang']." <font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<input type=text id=tothutang style='width:240px;'  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)>
			</td>

			<td>".$_SESSION['lang']['rupiah']."/".$_SESSION['lang']['bulan']." <font size=2px style=color:red><b>*</b></font></td>
			<td>:</td>
			<td>
				<input type=text id=rpbulan  style='width:240px;'  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this) onkeyup='hitungbulan()'>
			</td>
		</tr>

		
	
		<tr>
			<td valign=top>".$_SESSION['lang']['keterangan']."</td>
			<td valign=top>:</td>
			<td colspan = 4>
				<textarea id='ket'  style='width:590px;'  onkeypress='return tanpa_kutip(event);'></textarea>
			</td>	
		</tr>
	
	<tr>
		<td colspan=6 id='tmblHeader' style='text-align:center'>
			
		</td>
	</tr>
	<tr>
		<td><i><b><font size=1px style=color:red;><b>*</b></font>) Kolom yang wajib terisi.</b></i></td>
	</tr>
</fieldset>
</table>
<input type='hidden' id='datasblm' name='datasblm' />
</div>";

// <button class=mybutton id=dtlForm onclick=kalkulasidetail()>Kalkulasi</button><br>
echo"
<div id='detaildata'>
<fieldset style='float:left;'>
<legend>".$_SESSION['lang']['form']." ".$_SESSION['lang']['detail']." </legend>
<table cellspacing='1' border='0' id='datadetail'></table>
</fieldset>
</div>";
CLOSE_BOX();

echo '</div>';
echo"<div id='listData'>";
OPEN_BOX();
echo"<fieldset style='width:auto;'>
<legend>" . $_SESSION['lang']['list'] . "</legend>
				<div>
					<table class=sortable cellspacing=1 cellpadding = 7 style='width:100%;' border=0>
						<thead>
							<tr class=rowheader>
								<td align=center>".$_SESSION['lang']['notransaksi']."</td>
								<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
								<td align=center>".$_SESSION['lang']['jenis']." Angsuran</td>
								<td align=center>".$_SESSION['lang']['tipe']." Angsuran</td>
								<td align=center>".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['awal']."</td>
								<td align=center>".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['akhir']."</td>
								<td align=center>".$_SESSION['lang']['keterangan']."</td>
								<td align=center>".$_SESSION['lang']['status']."</td>
								<td align=center colspan=5 >Action</td>
							</tr>
						</thead>
						<tbody id=container>
							<script>loadData(0)</script>
						</tbody>
						<tfoot id=footData>
						</tfoot>
					</table>
				</div>
</fieldset>";
CLOSE_BOX();
echo"</div>";

echo close_body();
?>
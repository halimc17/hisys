<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_verifikasiPp').'</span>');
?>
<script>semua="<?php echo $_SESSION['lang'] ['all']; ?>";</script>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/log_verivikasi.js?v=<?php echo time() ?>"></script>
<script type="text/javascript" src="js/log_pp.js?v=<?php echo time() ?>"></script>
<script language=javascript src='js/vhc_detailkmhm.js?v=<?php echo time() ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true,
			width:'100%'
		});
		//daftar pilihan select2 harus tampil di atas popup/dialog
		$('<style>.select2-container--open .select2-dropdown{z-index:100000;}</style>').appendTo('head');
		//dropdown baris tidak boleh menimpa header tabel saat daftar di-scroll; baris yang sudah PO berteks abu-abu
		$('<style>#contload thead th{z-index:10;} #contload .select2-container{z-index:0;} #contload tr.rowterkunci td{color:#777;}</style>').appendTo('head');
	});
</script>
<div id="action_list">
<?php

#LIST JENIS BARANG
$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjenis.="<option value='slow'>Slow</option>";
$optjenis.="<option value='fast'>Fast</option>";
$optjenis.="<option value='non'>Non</option>";

##sesuai detail akses
$whrdetailakses = " and left(kodeorganisasi,4) in (".getOrgDetail(2).")";

#LIST PURCHASER
$optPur="<option value=''>".$_SESSION['lang']['all']."</option>";
// $sPur="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where (bagian='PUR'or kodejabatan='17') and kodejabatan!='5' and (tanggalkeluar>='".date('Y-m-d')."' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
$sPur="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where bagian='PRO'  and tanggalkeluar='0000-00-00' order by namakaryawan asc";
$qPur=fetchData($sPur);
foreach($qPur as $brsKary)
{
	$optPur.="<option value=".$brsKary['karyawanid'].">".$brsKary['namakaryawan']."</option>";
}

$optListUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$str = "select distinct kodeorganisasi,namaorganisasi from ".$dbname.".`organisasi` where tipe in (select tipeorg from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."') and kodeorganisasi in (select distinct substr(nopp,16,4) as kodeunit from ".$dbname.".log_prapoht where close='2') ".$whrdetailakses." order by induk";
$res = fetchData($str);
if(count($res)>0){	
	foreach($res as $bar){
		$d=getNamaOrg($bar['kodeorganisasi'],'induk');
		if($d!=$n){			
			$optListUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
		$optListUnit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		$n=$d;
		if($d!=$n){			
			$optListUnit.="</optgroup>";
		}
	}
}else{
	#LIST UNIT
	$sListUnit="select distinct substr(nopp,16,4) as kodeunit from ".$dbname.".log_prapoht where close='2' and unit in (".getOrgDetail(2).")";
	$qListUnit=$owlPDO->query($sListUnit) or die(print " Gagal: ".PDOException::getMessage());
	$qListUnit->setFetchMode(PDO::FETCH_ASSOC);
	while($rListUnit=$qListUnit->fetch()){
		if(strlen($rListUnit['kodeunit'])==4){		
			$optListUnit.="<option value='".$rListUnit['kodeunit']."'>".$rListUnit['kodeunit']." - ".getNamaOrg($rListUnit['kodeunit'])."</option>";
		}
	}
}


#LIST KELOMPOK BARANG
$optKelompokBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optBrgCari="<option value=''>".$_SESSION['lang']['all']."</option>";
$sKelompok="select distinct kode,kelompok from ".$dbname.".log_5klbarang order by kode asc";
$qKelompok=$owlPDO->query($sKelompok) or die(print " Gagal: ".PDOException::getMessage());
$qKelompok->setFetchMode(PDO::FETCH_ASSOC);
while($rKelompok=$qKelompok->fetch())
{
	$optKelompokBrg.="<option value='".$rKelompok['kode']."'>".$rKelompok['kode']." - ".$rKelompok['kelompok']."</option>";
}

#LIST PERIODE
$optPeriodeCari="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPeriodeCari="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_prapoht order by substr(tanggal,1,7) desc";
$qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriodeCari=$qPeriodeCari->fetch())
{
	$optPeriodeCari.="<option value='".$rPeriodeCari['periode']."'>".$rPeriodeCari['periode']."</option>";
}

$optkat="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPeriodeCari="select * from ".$dbname.".log_5kategoribarang order by id";
$qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriodeCari=$qPeriodeCari->fetch()){
	$optkat.="<option value='".$rPeriodeCari['id']."'>".$rPeriodeCari['jenis']."</option>";
}

#LIST STATUS PO
$optStatusPP="<option value=''>".$_SESSION['lang']['all']."</option>";
$stataPP=array("0"=>$_SESSION['lang']['blmAlokasi'],"1"=>$_SESSION['lang']['sdhPO']);
foreach($stataPP as $dataIni=>$listNama)
{
	$optStatusPP.="<option value='".$dataIni."'>".$listNama."</option>";
}

## STRATEGIES
$optstrategis.="<option value=''>".$_SESSION['lang']['all']."</option>";
$optstrategis.="<option value='1'>Ya</option>";
$optstrategis.="<option value='0'>Tidak</option>";

#tahun PR/SR: default tahun sekarang, "Semua Tahun" untuk seluruh tahun
$tahunSkrg=date('Y');
$arrTahun=array($tahunSkrg=>$tahunSkrg);
$resTahun=fetchData("select distinct year(tanggal) as thn from ".$dbname.".log_prapoht where tanggal>'0000-00-00'");
foreach($resTahun as $val){
	$arrTahun[$val['thn']]=$val['thn'];
}
krsort($arrTahun);
$optTahun="<option value=''>Semua Tahun</option>";
foreach($arrTahun as $thn){
	$optTahun.="<option value='".$thn."'".($thn==$tahunSkrg?" selected":"").">".$thn."</option>";
}

echo"<table>
	<tr valign=middle>
		<td onclick=displaySummary() align=center style='width:55px;cursor:pointer;display:none'>
			<img class=delliconBig src=images/book_icon.gif title='Summary'><br>Summary
		</td>
		<td onclick=displayTools() align=center style='width:55px;cursor:pointer;display:none'>
			<img class=delliconBig src=images/gear_64.png title='Tools'><br>Tools
		</td>
		<td align=center style='width:55px;cursor:pointer;' onclick=showalllist2(0)>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
		</td>
		<td>
			<fieldset>
				<legend>".$_SESSION['lang']['find']."</legend>
				<table border=0 cellpadding=1 cellspacing=1>
					<tr>
						<td>Tahun</td>
						<td>:</td>
						<td style='width:220px'>
							<select id=filterTahun class=select2 style='width:100%' onchange='loaddata(0)'>".$optTahun."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['periode']."</td>
						<td>:</td>
						<td style='width:150px'>
							<select id=tgl_cari class=select2 style='width:100%' onchange='ubahPeriodeVP()'>".$optPeriodeCari."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['carinopp']."</td>
						<td>:</td>
						<td style='width:150px'>
							<input type=text id=txtsearch style='width:100%;box-sizing:border-box' maxlength=30 class=myinputtext onkeyup=\"enterkey(event,function(){loaddata(0)})\">
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['unit']."</td>
						<td>:</td>
						<td style='width:200px'>
							<select id=unitIdCr class=select2 style='width:100%' onchange='loaddata(0)'>".$optListUnit."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['purchaser']."</td>
						<td>:</td>
						<td style='width:170px'>
							<select id=purId class=select2 style='width:100%' onchange='loaddata(0)'>".$optPur."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kelompokbarang']."</td>
						<td>:</td>
						<td>
							<select id=klmpkBrg class=select2 style='width:100%' onchange='loaddata(0)'>".$optKelompokBrg."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['namabarang']."</td>
						<td>:</td>
						<td>
							<input type=text id=kdBarangCari style='width:100%;box-sizing:border-box' placeholder=\"".$_SESSION['lang']['all']."\" class=myinputtext onkeyup=\"enterkey(event,function(){loaddata(0)})\">
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['status']."</td>
						<td>:</td>
						<td>
							<select id=statPP class=select2 style='width:100%' onchange='loaddata(0)'>".$optStatusPP."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['jenis']."</td>
						<td>:</td>
						<td>
							<select id=jenis class=select2 style='width:100%' onchange='loaddata(0)'>".$optjenis."</select>
						</td>
						<td style='padding-left:15px'>Strategies</td>
						<td>:</td>
						<td>
							<select id=crstrategis class=select2 style='width:100%' onchange='loaddata(0)'>".$optstrategis."</select>
						</td>
					</tr>
					<tr>
						<td>Kategori</td>
						<td>:</td>
						<td>
							<select id=crkategori class=select2 style='width:100%' onchange='loaddata(0)'>".$optkat."</select>
						</td>
						<td style='padding-left:15px'>".$_SESSION['lang']['kontrak']."</td>
						<td>:</td>
						<td>
							<select id=kontrakIdCr class=select2 style='width:100%' onchange='loaddata(0)'>".$optstrategis."</select>
						</td>
						<td style='padding-left:15px'>No ".$_SESSION['lang']['kontrak']."</td>
						<td>:</td>
						<td>
							<input type=text id=kontrakaIdCr style='width:100%;box-sizing:border-box' placeholder=\"".$_SESSION['lang']['all']."\" class=myinputtext onkeyup=\"enterkey(event,function(){loaddata(0)})\">
						</td>
						<td colspan=6 style='padding-left:15px'>
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=exportExcelVP()>".$_SESSION['lang']['excel']."</button>
							<button class=mybutton onclick=exportPdfVP()>PDF</button>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
	</tr>
</table>";

?>

</div>
<?php
CLOSE_BOX(); //1 C
echo "<div id=\"list_pp_verication\">";
OPEN_BOX(); //2 O
?>
  <input type='hidden' id='method' name='method' />


<img style='display:none' onclick=dataKeExcel(event) src=images/excel.jpg class=resicon title='MS.Excel'> 
<div style='display:none;' id='containtool'></div>
<div style='padding:2px 4px 6px 4px;color:#555'>Keterangan: tombol <b style='color:#b71c1c'>Drop</b> menolak semua item pada nomor PR/SR tersebut yang belum dialokasi ke purchaser (item hilang dari daftar dan pembuat PR/SR mendapat notifikasi). Tombol hanya muncul pada PR/SR yang belum punya purchaser.</div>
<div style="overflow:auto;height:65vh;width:100%" class='table-scroll' id='contload'>

<?php
echo"<table class=\"sortable\" cellspacing=\"1\" border=\"0\" style=\"width:100%\">
	<thead>
	<tr class=rowheader>
		<th align='center' style='min-width:85px'>No.</th>
		<th align='center' style='display:none'>".$_SESSION['lang']['kodeorg']."</th>
		<th align='center'>No PR/SR</th>
		<th align='center'>".$_SESSION['lang']['kodebarang']."</th>
		<th align='center'>".$_SESSION['lang']['kelompokbarang']."</th>
		<th align='center'>Kategori</th>
		<th align='center'>Strategies</th>
		<th align='center'>".$_SESSION['lang']['namabarang']."</th>
		<th align='center'>".$_SESSION['lang']['tipe']."</th>
		<th align='center' style='min-width:70px'>Prioritas</th>
		<th align='center' style='width:50px;display:none'>Advance Action</th>
		<th align='center'>".$_SESSION['lang']['chat']."</th>
		<th align='center' style='min-width:70px'>".$_SESSION['lang']['tanggal']." PR/SR</th>
		<th align='center' width=50px>".$_SESSION['lang']['jmlhDiminta']."</th>
		<th align='center'>".$_SESSION['lang']['jmlh_disetujui']."</th>
		<th align='center' width=30px>".$_SESSION['lang']['satuan']."</th>
		<th align='center' style='display:none'>O.sth</th>
		<th align='center'>".$_SESSION['lang']['purchaser']."</th>
		<th align='center'>Kontrak</th>
		<th align='center' style='min-width:110px'>Status</th>
		<th align='center' width=60px style='display:none'>".$_SESSION['lang']['lokasitugas']."</th>
		<th align=\"center\" style='display:none'>View</th>
	</tr>
	</thead>
	<tbody id=contain >
		<script>loaddata(0)</script>
	</tbody>
	</table>";
?>
</div>


<?php
echo"</div>";
CLOSE_BOX();
echo close_body();
?>
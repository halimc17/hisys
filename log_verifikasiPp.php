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
			dropdownAutoWidth:true
		});
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
						<td>".$_SESSION['lang']['carinopp']."</td>
						<td>:</td>
						<td>
							<!--<input type=text id=txtsearch style=width:150px size=25 maxlength=30 class=myinputtext onkeypress='enterkey(event,loaddata(0))'>-->
							<input type=text id=txtsearch style=width:150px size=25 maxlength=30 class=myinputtext>
						</td>
						
						<td>".$_SESSION['lang']['periode']."</td>
						<td>:</td>
						<td>
							<select id=tgl_cari onchange='enterkey(event,loaddata(0))' style=width:100px>".$optPeriodeCari."</select>
						</td>
						
						<td>".$_SESSION['lang']['purchaser']."</td>
						<td>:</td>
						<td>
							<select style=width:100px onchange='enterkey(event,loaddata(0))' id=purId name=purId>".$optPur."</select>
						</td>
						
						<td>".$_SESSION['lang']['unit']."</td>
						<td>:</td>
						<td>
							<select style=width:100px id=unitIdCr onchange='enterkey(event,loaddata(0))' name=unitIdCr>".$optListUnit."</select>
						</td>
						<td>".$_SESSION['lang']['kontrak']."</td>
						<td>:</td>
						<td>
							<select style=width:100px id=kontrakIdCr onchange='enterkey(event,loaddata(0))'>".$optstrategis."</select>
						</td>
						
						
						<td>No ".$_SESSION['lang']['kontrak']."</td>
						<td>:</td>
						<td>
							<input type=text id=kontrakaIdCr onkeyup='enterkey(event,loaddata(0))' style=width:96px placeholder=".$_SESSION['lang']['all']." class=myinputtext>
						</td>
						
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kelompokbarang']."</td>
						<td>:</td>
						<td>
							<select id=klmpkBrg onchange='enterkey(event,loaddata(0))' style=width:155px onchange=getBarangCari()>".$optKelompokBrg."</select>
						</td>
						
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>:</td>
						<td>
							<!--<input type=text id=kdBarangCari style=width:96px placeholder=".$_SESSION['lang']['all']." class=myinputtext onkeypress='enterkey(event,loaddata(0))'>-->
							<input type=text id=kdBarangCari style=width:96px placeholder=".$_SESSION['lang']['all']." class=myinputtext>
						</td>
						
						<td>".$_SESSION['lang']['status']."</td>
						<td>:</td>
						<td>
							<select style=width:100px onchange='enterkey(event,loaddata(0))' id='statPP' name='statPP'>".$optStatusPP."</select>
						</td>
						
						<td>".$_SESSION['lang']['jenis']."</td>
						<td>:</td>
						<td>
							<select style=width:100px onchange='enterkey(event,loaddata(0))' id='jenis' name='jenis'>".$optjenis."</select>
						</td>
						
						<td>Strategies</td>
						<td>:</td>
						<td>
							<select style=width:100px onchange='enterkey(event,loaddata(0))' id='crstrategis' name='crstrategis'>".$optstrategis."</select>
						</td>

						<td>Kategori</td>
						<td>:</td>
						<td>
							<select style=width:100px onchange='enterkey(event,loaddata(0))' id='crkategori' name='crkategori'>".$optkat."</select>
						</td>
						
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td align=left>
								<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
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
<div style="overflow:auto;height:65vh" class='table-scroll' id='contload'>

<?php
echo"<table class=\"sortable\" cellspacing=\"1\" border=\"0\">
	<thead>
	<tr class=rowheader>
		<th align='center'>No.</th>
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
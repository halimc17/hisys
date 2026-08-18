<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/log_laporan.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
	
	$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
		$(this).closest(".select2-container").siblings('select:enabled').select2('open');
	});
</script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2persediaanFisik').'</span>');
$whr="namaorganisasi!=''";
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',$whr);
//get existing period


$str="select distinct periode from ".$dbname.".log_5saldobulanan
      order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
//$optper="<option value=''>".$_SESSION['lang']['sekarang']."</option>";
while($bar=$res->fetch()){
	$optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

//=================ambil PT; 
$listOrg = getOrgDetail(4);
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){ 
    $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT' and kodeorganisasi in (".$listOrg.") order by induk";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar->kodeorganisasi."'");
		$d=$induk[$bar->kodeorganisasi];
		if($d!=$n){			
			$optpt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
		$n=$d;
		if($d!=$n){			
			$optpt.="</optgroup>";
		}
    }
}else if($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
	$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
	 where tipe='PT' and kodeorganisasi in (".$listOrg.") and kodeorganisasi in (select induk from ".$dbname.".bgt_regional_assignment a left join ".$dbname.".organisasi b on a.kodeunit = b.kodeorganisasi where a.regional='".$_SESSION['empl']['regional']."')
	order by namaorganisasi";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar->kodeorganisasi."'");
		$d=$induk[$bar->kodeorganisasi];
		if($d!=$n){			
			$optpt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		}
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
		$n=$d;
		if($d!=$n){			
			$optpt.="</optgroup>";
		}
    }
}else{
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['org']['namaorganisasi']."</option>";
} 

//=================ambil gudang;
$str="select distinct a.kodeorg,b.namaorganisasi from ".$dbname.".setup_periodeakuntansi a
      left join ".$dbname.".organisasi b
	  on a.kodeorg=b.kodeorganisasi
      where b.tipe='GUDANG'
	  order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch()){
	$optgudang.="<option value='".$bar->kodeorg."'>".$bar->kodeorg." - ".$bar->namaorganisasi."</option>";
}

$str="select distinct a.kodeorg,b.namaorganisasi from ".$dbname.".setup_periodeakuntansi a
      left join ".$dbname.".organisasi b
	  on a.kodeorg=b.kodeorganisasi
      where b.tipe='GUDANG'
	  order by namaorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optgudang2="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$res->fetch())
{
	$optgudang2.="<option value='".$bar->kodeorg."'>".$bar->namaorganisasi."</option>";

}

$optUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optUnit2=$optGdng=$optUnit;
$unitDetailAkses = getOrgDetail(2);
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$whereUnit = "and induk in (".$unitDetailAkses.")";
}else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$whereUnit = "and induk in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') and induk in (".$unitDetailAkses.")";
}else{

	$unitDetailAkses = orgDetailuser($_SESSION['standard']['username'],'2');
	if(count($unitDetailAkses) > 0){
		$whereUnit=" and induk IN (".$unitDetailAkses.") ";
	}else{
		$whereUnit = " and induk = '".$_SESSION['empl']['lokasitugas']."' ";
	}

}

$sUnit="select distinct substr(kodeorganisasi,1,4) as kodeorganisasi from ".$dbname.".organisasi where tipe like 'GUDANG%' ".$whereUnit." order by induk asc";
print_r($sUnit);
$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit=$qUnit->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$rUnit['kodeorganisasi']."'");
	$d=$induk[$rUnit['kodeorganisasi']];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optUnit.="<option value='".$rUnit['kodeorganisasi']."'>".$rUnit['kodeorganisasi']." - ".$optNmOrg[$rUnit['kodeorganisasi']]."</option>";
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

$sUnit2="select distinct substr(kodeorganisasi,1,4) as kodeorganisasi from ".$dbname.".organisasi
        where tipe like 'GUDANG%' ".$whereUnit." and namaorganisasi!='' order by induk asc";
$qUnit2=$owlPDO->query($sUnit2) or die(print " Gagal: ".PDOException::getMessage());
$qUnit2->setFetchMode(PDO::FETCH_ASSOC);
while($rUnit2=$qUnit2->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$rUnit2['kodeorganisasi']."'");
	$d=$induk[$rUnit2['kodeorganisasi']];
	if($d!=$n){			
		$optUnit2.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
    $optUnit2.="<option value='".$rUnit2['kodeorganisasi']."'>".$rUnit2['kodeorganisasi']." - ".$optNmOrg[$rUnit2['kodeorganisasi']]."</option>";
	$n=$d;
	if($d!=$n){			
		$optUnit2.="</optgroup>";
	}
}
$optPeriode2=$optPeriode="";
for($x=0;$x<13;$x++){
	$dt=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPeriode.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
    $optPeriode2.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
}


echo"<br />
    <fieldset style=float:left;>
     <legend>".$_SESSION['lang']['persediaanfisik'].' Per '.$_SESSION['lang']['sloc']."</legend>
         <table cellpadding=1 cellspacing=1 border=0>
         <tr><td>
	 ".$_SESSION['lang']['unit']."</td><td><select class='select2' id=unitDt style='width:150px;' onchange=getGudangDt()>".$optUnit."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['sloc']."</td><td><select class='select2' id=gudang2 style='width:150px;' onchange=hideById('printPanel2')>".$optGdng."</select></td></tr>
	 <tr><td>".$_SESSION['lang']['periode']."</td><td><select class='select2' style='width:150px;' id=periode2 onchange=hideById('printPanel2')>".$optper."</select></td></tr>
	 <tr><td><td><button class=mybutton onclick=getLaporanFisik2nonpost()>".$_SESSION['lang']['proses']."</button></td></tr></table>
	 </fieldset>
      
      <fieldset style=float:left;>
     <legend>".$_SESSION['lang']['info']."</legend>

	 	<table>
			<tr>
				<td colspan=3 style='font-weight:bold'>".$_SESSION['lang']['persediaanfisik'].' Per '.$_SESSION['lang']['sloc']."</td>
			</tr>
			<tr>
				<td> <span style='font-weight:bold'>Excel</span> </td>
				<td>:</td>
				<td>
					Klik di kolom nilai saldo akhir</td>
				</td>
			</tr>
			<tr>
				<td> <span style='font-weight:bold'>PDF</span> </td>
				<td>:</td>
				<td>
					Klik di kolom barang</td>
				</td>
			</tr>
		 </table>
        
	 </fieldset>
	 ";

CLOSE_BOX();
OPEN_BOX();
// echo"<legend>".$_SESSION['lang']['printArea']."</legend>";
echo"

<div id=printPanel style='display:none;'>
     <img onclick=fisikKeExcel(event,'log_laporanPersediaanFisik_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDF(event,'log_laporanPersediaanFisik_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </div>   
	<div id=printPanel2 style='display:none;width:100%'>
		<table style='width:100%'>
			<tr>
				<td>
					 <img style='display:none' onclick=fisikKeExcel2(event,'log_slaveLaporanPersediaanFisikUnitnonpost.php') src=images/excel.jpg class=resicon title='MS.Excel'>

					 <img onclick=fisikKeExcel2opname(event,'log_slaveLaporanPersediaanFisikUnitopname.php') src=images/excel.jpg class=resicon title='Form Excel Stock Opname'>
					 <img onclick=fisikKePDF2(event,'log_slaveLaporanPersediaanFisikUnitnonpost.php') title='PDF' class=resicon src=images/pdf.jpg>
				</td>
				<td align=right>
					Search: <input class=myinputtext id=textcari style=width:200px onkeyup=getLaporanFisik2();>
				</td>
			</tr>
		</table>

	</div>
         <div id=printPanel3 style='display:none;'>
     <img onclick=fisikKeExcel3(event,'log_slaveLaporanPersediaanFisikUnit2nonpost.php') src=images/excel.jpg class=resicon title='MS.Excel'>
	 <img onclick=fisikKePDF3(event,'log_slaveLaporanPersediaanFisikUnit2nonpost.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </div>
	<input type='hidden' id='fieldsetno'>

	  <div id='printContainer' class='table-scroll' style=height:65vh>
        <table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>
			<tr>
			  <th rowspan=2 align=center style='width:50px;'>No.</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['pt']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['sloc']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['periode']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['kodebarang']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['namabarang']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['jenis']."</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['minstok']."</th>
			  <th align=center>
				<input type='checkbox' id='chksaldoawal' checked onclick=\"nolvalue()\">
				= 0</th>
			  <th align=center>
				<input type='checkbox' id='chkmasuk' checked onclick=\"nolvalue()\">
				= 0</th>
			  <th align=center> 
				<input type='checkbox' id='chkkeluar' checked onclick=\"nolvalue()\">
				= 0</th>
			  <th align=center> 
				<input type='checkbox' id='chksaldo' checked onclick=\"nolvalue()\">
				= 0</th>
			  <th rowspan=2 align=center>".$_SESSION['lang']['nilaisaldokurang']."</th>
			</tr>
		    <tr>
			  <th align=center>".$_SESSION['lang']['saldoawal']."</th>
			  <th align=center>".$_SESSION['lang']['masuk']."</th>
			  <th align=center>".$_SESSION['lang']['keluar']."</th>
			  <th align=center>".$_SESSION['lang']['saldo']."</th>
			  
			</tr>  
		 </thead>
		<tbody id=container>
		</tbody>
	</table>
</div>
</div>";
CLOSE_BOX();
close_body();
?>
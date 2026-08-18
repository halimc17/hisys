<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src='js/zTools.js'></script>
<script language="javascript">
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});

var nmTmblDone='<?php echo $_SESSION['lang']['done']?>';
var nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
var nmTmblSave='<?php echo $_SESSION['lang']['save']?>';
var nmTmblCancel='<?php echo $_SESSION['lang']['cancel']?>';
var optIsi='<?php 
$kodeOrg= isset($_SESSION['temp']['nSpb'])? substr($_SESSION['temp']['nSpb'],8,6): '';
$optBlok="<option value=></option>";
$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where `induk`='".$kodeOrg."' and tipe='BLOK' ORDER BY `namaorganisasi` ASC";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){
	$optBlok.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>"; 
}
echo $optBlok;?>';
</script>
<script language="javascript" src="js/kebun_spbv2.js?v=<?php echo time(); ?>"></script>
<input type="hidden" id="proses" name="proses" value="insert"  />

<?php 
OPEN_BOX('','<span class=judul>'.getMenu('kebun_spbv2').'</span>');
?>
<div id="action_list">
<?php
$optPeriode="";
$sql="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_spbht where kodeorg IN (".getOrgDetail(2).") order by periode desc";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
	$optPeriode.="<option value=".$res['periode'].">".$res['periode']."</option>"; 
}

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and induk IN (".getOrgDetail(2).") ";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optorg.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}


$sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi IN (".getOrgDetail(2).")";
$optOrg="";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while($res=$query->fetch()){	
	$optOrg.="<option value=".$res['kodeorganisasi'].">".$res['namaorganisasi']."</option>"; 
}


## Tipe Status
$optStatus= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrtipeStatus=array('0'=>'Internal','1'=>'Alfiasi','3'=>'Eksternal','4'=>'TPH Besar');
foreach($arrtipeStatus as $val => $value){
	$optStatus.="<option value='".$val."'>".$value."</option>";
}

$optStatusXX= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrtipeStatusXX=array('0'=>'Unposted','1'=>'Posted');
foreach($arrtipeStatusXX as $val => $value){
	$optStatusXX.="<option value='".$val."'>".$value."</option>";
}

echo"<table cellspacing=1 border=0>
     <tr valign=moiddle>
        <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
           <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
        </td>
        <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
           <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
        </td>
        <td align=center style='width:100px;cursor:pointer;' onclick=donwloadlist()>
           <img class=delliconBig src=images/download.png title='Download Data Mobile'><br>Mobile Data
        </td>
        <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
		echo $_SESSION['lang']['nospb']."   : <input type=text id=txtsearch size=18 style=width:125px; maxlength=30 class=myinputtext onkeypress='enterkey(event,loadData)'>&nbsp;";
                echo $_SESSION['lang']['noreferensi']."   : <input type=text id=referensisearch size=18 style=width:125px; maxlength=30 class=myinputtext onkeypress='enterkey(event,loadData)'>&nbsp;";

		echo $_SESSION['lang']['divisi']."  : <select class=select2 id=divsch style=width:125px; onchange=\"loadData(0)\">" . $optorg . "</select>&nbsp;";
		echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=9 maxlength=10 readonly/>&nbsp;";
		echo $_SESSION['lang']['status']."  : <select class=select2 id=status_spb style=width:125px; onchange=\"loadData(0)\">" . $optStatus . "</select>&nbsp;";
		echo $_SESSION['lang']['posting']."  : <select class=select2 id=status_posting style=width:125px; onchange=\"loadData(0)\">" . $optStatusXX . "</select>&nbsp;";

		echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
         <td><fieldset><legend>".$_SESSION['lang']['exportData']."</legend>"; 
		echo $_SESSION['lang']['periode']." : <select class=select2 id=periode nama=periode>".$optPeriode."</select>&nbsp;";
		echo $_SESSION['lang']['kodeorg']." : <select class=select2 id=unitOrg name=unitOrg>".$optOrg."</select>";
		echo"&nbsp;<img onclick=dataKeExcel(event,'kebun_sbp_excel.php') src=images/excel.jpg class=zImgBtn title='MS.Excel'> 
         <img onclick=dataKePDF(event) title='PDF' class=zImgBtn src=images/pdf.jpg>";
echo"</fieldset></td>
         </tr>
         </table> "; 
?>
</div>
<?php
CLOSE_BOX();
?>
<div id="listSpb">
<?php OPEN_BOX()?>

<table cellspacing="1" cellpadding='5' border="0" class="sortable" width="100%">
<thead>
<tr class="rowheader">
<th align=center style=width:30px >No</th>
<th align=center style=max-width:180px ><?php echo $_SESSION['lang']['nospb']?></th>
<th align=center style=max-width:180px ><?php echo $_SESSION['lang']['noreferensi']?></th>
<th align=center style=max-width:80px ><?php echo $_SESSION['lang']['status']?></th>
<th align=center style=max-width:100px ><?php echo $_SESSION['lang']['tanggal']?></th>
<th align=center style=max-width:80px ><?php echo $_SESSION['lang']['kodeorg']?></th>
<th align=center><?php echo $_SESSION['lang']['divisi']?></th>
<th align=center><?php echo $_SESSION['lang']['nopol']?></th>
<th align=center><?php echo $_SESSION['lang']['sopir']?></th>
<th align=center style='width:80px;' hidden ><?php echo 'KONTAN'?></th>
<th align=center style=max-width:80px ><?php echo $_SESSION['lang']['janjang']?></th>
<th align=center style=max-width:80px ><?php echo $_SESSION['lang']['kgwb']." (Sebelum Sortasi)" ?></th>
<th align=center style=max-width:80px ><?php echo "Kg Sortasi" ?></th>
<th align=center style=max-width:80px ><?php echo $_SESSION['lang']['kgwb']." (Setelah Sortasi)" ?></th>
<th align=center ><?php echo $_SESSION['lang']['tkbm']?></th>
<th align=center ><?php echo $_SESSION['lang']['updateby']?></th>
<th align=center colspan=8>Action</th>
</tr>
</thead>
<tbody id="contain">
<script>loadData()</script>
</tbody>
<tfoot id='footer'>
</tfoot>
</table>

<?php CLOSE_BOX()?>
</div>



<div id="headher" style="display:none">
<?php
OPEN_BOX();

$sORg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$optOrg2="";	
$qOrg=$owlPDO->query($sORg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg=$owlPDO->query($sORg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){	
    $optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";	
}
$optPrd="";
for($x=0;$x<=12;$x++){
	$dte=mktime(0,0,0,date('m')-$x,15,date('Y'));
	$optPrd.="<option value=".date("Y-m",$dte).">".date("Y-m",$dte)."</option>";
}
?>
<fieldset >
<legend><?php echo $_SESSION['lang']['header']?></legend>
<table cellspacing="1" border="0">
<tr>
<td><?php echo $_SESSION['lang']['kodeorg']?></td>
<td>:</td>
<td>
<select id="kodeOrg" name="kodeOrg" style="width:160px;" onchange="getDiv(0)"><option value=""></option><?php echo $optOrg2;?></select></td>

<td><?php echo $_SESSION['lang']['divisi']?></td>
<td>:</td>
<td>
<select id="kodeDiv" name="kodeDiv" style="width:160px;" ></select>
</td>
</tr>
<tr>
<td><?php echo $_SESSION['lang']['periode']?></td>
<td>:</td>
<td>
<select id="period" name="period" style="width:160px;" ><?php echo $optPrd;?></select></td>

<td><?php echo $_SESSION['lang']['nourut']?></td>
<td>:</td>
<td>
<input type="text" id="nourut" name="nourut" class="myinputtextnumber" style="width:155px;" maxlength="4" onkeypress="return angka_doang(event)"  onblur="fillZero()"/>
<input type="hidden"  id="noSpb" name="noSpb" class="myinputtext" style="width:160px;" disabled="disabled" /></td>
</tr>

<tr>
<td><?php echo $_SESSION['lang']['tanggal']?></td>
<td>:</td>
<td><input type="text" class="myinputtext" id="tgl_ganti" name="tgl_ganti" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" style="width:155px;" readonly/></td>

<?php

$optIntex="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optIntex.="<option value='3'>External</option>";
$optIntex.="<option value='0'>Internal</option>";
$optIntex.="<option value='1'>Afiliasi</option>";
$optIntex.="<option value='4'>TPH Besar</option>";

$namaisi = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$harga = makeOption($dbname,'kebun_5premibkm','kodekegiatan,premilebihbasis',"unit='".$_SESSION['empl']['lokasitugas']."'");
$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BMTBS' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
$nama=explode(',',$bar['nilai']);
$optkar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($nama as $list => $isi){
	@$optkar.="<option value=".$isi.">".$namaisi[$isi]."</option>";
}



$optPks='';
@$optkontan.="<option value='KERJA'>Kerja</option>";
@$optkontan.="<option value='KONTAN'>Kontanan</option>";
	echo"<td>".$_SESSION['lang']['status']."</td>
        <td>:</td>
        <td><select id=intex name=intex onchange=getPks() style=width:160px;>".$optIntex."</select></td>
    </tr>
    
    <tr>
        <td>".$_SESSION['lang']['pabrik']."/".$_SESSION['lang']['notph']." Besar</td>
        <td>:</td>
        <td><select id=pks name=pks style=width:160px;>".$optPks."</select></td>
		
		<td>".$_SESSION['lang']['tahuntanam']."</td>
        <td>:</td>
        <td><input type='text' id='tahuntanam' name='tahuntanam' class='myinputtextnumber' style='width:155px;' maxlength='4' onkeypress='return angka_doang(event)'  onblur='fillZero()'/></select>
		
        <td hidden>".$_SESSION['lang']['jenis']."</td>
        <td	hidden>:</td>
        <td hidden><select id=kerani name=kerani style=width:160px;>".$optkar."</select>
		</td>
    </tr>
	
	<tr >
        <td hidden>Kerja / Kontanan</td>
        <td hidden>:</td>
        <td hidden><select id=kontanan name=kontanan style=width:160px;>".$optkontan."</select></td>

        <td>Referensi Mobile</td>
        <td>:</td>
        <td><input type='text' id='referensimb' name='referensimb' class='myinputtextnumber' style='width:155px;' ></select>
    </tr>";
?>


<tr>
<td><td><td id="tmbLheader">
<tr>
<td><td><td id="ublLheader">
</tr>
</td>
</td>
</td>
</tr>
</table>
</fieldset>

<?php
CLOSE_BOX();
?>
</div>
<div id="detailSpb" style="display:none">
<?php 
OPEN_BOX();
?>

<fieldset>
<legend><?php echo $_SESSION['lang']['detail']?></legend>
<table cellspacing="1" border="0">
<tbody id="detailIsi">
	<tr>
		<td colspan='3'> 
		<fieldset style='float:left'>
			<? echo $_SESSION['lang']['nospb']?></b> : <input type="text" id="detail_kode" name="detail_kode" disabled="disabled" class="myinputtext" style="width:150px;" />
		</fieldset>
		<fieldset style='float:left'>
			<input type="checkbox" id="mnculSma" onclick="getBlokSma(1)" />Blok Seluruhnya
			<input hidden type="checkbox" id="blokplasma" onclick="getBlokSma(1)" />
		</fieldset>
	</tr>
	<tr><td colspan='3'> 
		<table id='ppDetailTable' cellpadding="2" cellspacing="1" border="0" class="sortable" width='100%'></table>
	</tr>
</tbody>
<tr>
<td id="tombol" align='right'></td>
<td width='100px' align='center'><button onclick='showtkbm(event)' class='mybutton' id='btnbm'>Add TK Muat</button></td>
<td  hidden width='90px' align='center'><button onclick='showpetani(event)' class='mybutton' id='btnbm'>Add Petani</button></td>
</tr>
</table>
</fieldset>
<div style='clear:both'></div>
<div style="overflow:auto; height:max-300px;">

<fieldset>
<legend><?php echo $_SESSION['lang']['datatersimpan']?></legend>
<table cellspacing="1" cellpadding='5' border="0"  class="sortable" style='min-width:590px'>
<thead>
<tr class="rowheader">
        <th align=center style=width:40px >No</th>
		<th align=center ><?php echo $_SESSION['lang']['noreferensi'] ?></th>
		<th align=center style=max-width:80px ><?php echo 'Tanggal Panen' ?></th>
		<th align=center ><?php echo $_SESSION['lang']['blok'] ?></th>
		<th align=center ><?php echo $_SESSION['lang']['tph'] ?></th>
		<th align=center >Sesi</th>
        <th align=center ><?php echo $_SESSION['lang']['bjr'] ?></th>
        <th align=center ><?php echo $_SESSION['lang']['janjang'] ?></th>
		<th align=center ><?php echo $_SESSION['lang']['brondolan'] ?></th>
		<th align=center style='width:85px;display:none;'><?php echo $_SESSION['lang']['kegiatan'] ?></th>
		<th align=center style='width:85px;display:none;' ><?php echo $_SESSION['lang']['kgwb'] ?></th>
        <th align=center style='width:50px;display:none' ><?php echo $_SESSION['lang']['mentah'] ?></th>
        <th align=center style='width:50px;display:none' ><?php echo $_SESSION['lang']['busuk'] ?></th>
        <th align=center style='width:50px;display:none' ><?php echo $_SESSION['lang']['matang'] ?></th>
        <th align=center style='width:50px;display:none' ><?php echo $_SESSION['lang']['lewatmatang'] ?></th>
    <th align=center  colspan=2>Action</th>
    </tr>
</thead>
<tbody id="contentDetail">
</tbody>
</table>

</fieldset></div>

<div style="overflow:auto; height:max-300px; display:none;" id='conttkbm'>
<fieldset >
<legend ><?php echo 'List TK BM'?></legend>
<table cellspacing="1" border="0"  class="sortable" style='min-width:590px' cellpadding='5'>
<thead>
 <tr class="rowheader">
        <th align=center style=width:40px >No</th>
		<th align=center style=width:100px ><?php echo $_SESSION['lang']['nik2'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['nama'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['kegiatan'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['tanggal'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['sesi'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['jjg'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['brondol'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['kontanan'] ?></th>
    <th align=center  style=width:30px >Action</th>
    </tr>
</thead>
<tbody id="contenttkbm">
</tbody>
</table>

</fieldset>
</div>

<div style="overflow:auto;height:max-300px;display:none;" id='contpetani'>

<fieldset >
<legend><?php echo 'List Petani'?></legend>
<table cellspacing="1" border="0"  class="sortable" style='min-width:590px' cellpadding='5'>
<thead>
 <tr class="rowheader">
        <th align=center style=width:40px >No</th> 
        <th align=center style=width:100px >Hamparan</th>
        <th align=center style=width:100px >Kavling</th>
        <th align=center><?php echo $_SESSION['lang']['nama'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['jjg'] ?></th>
        <th align=center><?php echo $_SESSION['lang']['brondolan'] ?></th> 
    <th align=center  style=width:30px >Action</th>
    </tr>
</thead>
<tbody id="contentpetani">
</tbody>
</table>

</fieldset></div>

<?php
CLOSE_BOX();
?>
</div>
<?php 
echo close_body();
?>
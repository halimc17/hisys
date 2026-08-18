<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
require_once('lib/zPivot.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_3premipemanenv4_spb.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optunit=$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit2=$optafd2=$optprd2="<option value=''>".$_SESSION['lang']['all']."</option>";
$optafd="<option value='%%'>".$_SESSION['lang']['pilihdata']."</option>";

## GET UNIT
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optunit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
		$optunit2.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optunit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$optunit2.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optunit.="<option value='".$key."'>".$key." - ".$val."</option>";			
		$optunit2.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
		$optunit2.="</optgroup>";
	}
}

$str="select distinct(substr(tanggal,1,7)) as periode from ".$dbname.".kebun_aktifitas order by periode desc limit 13";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$no =0;
while($bar=$res->fetch()){
	$optprd.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$no++;
	if($no=='1'){
		$optprd2.="<option value=".$bar['periode']." selected>".$bar['periode']."</option>";
	}else{		
		$optprd2.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	}
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='AFDELING' ".$whr."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optafd2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

#$opttahap="<option value='0'>Sebulan</option>";
$opttahap="<option value='1'>Pertama</option>";
$opttahap.="<option value='2'>Kedua</option>";

$optbrd="<option value='1'>Kg Sebelum Potong Brondolan</option>";
$optbrd.="<option value='2'>Kg Setelah Potong Brondolan</option>";


$optpot="<option value='1'>Pot Kg dengan biaya brondolan</option>";
$optpot.="<option value='3'>Pot Kg dengan tanpa brondolan</option>";
$optpot.="<option value='2'>Pot Rp Denda</option>";


OPEN_BOX('','<span class=judul>'.getMenu('kebun_3premipemanenv4_spb').'</span><br>');
$arrlist="##tgl1list##tgl2list##unitlist##afdlist";
$arrLoad = "##prdlist##unitlist##afdlist";

echo"<table cellpadding=3 cellspacing = 1>
     <tr valign=middle>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=add_new_data()>
	   <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
	 
	 <td align=center style='width:100px;cursor:pointer;' onclick=displayList()>
	   <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
	 <td>
         
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=prdlist  style='width:153px;'>".$optprd2."</select></td>
			
				<td>".$_SESSION['lang']['unitkerja']."</td>
				<td>:</td>
				<td><select id=unitlist onchange=getdivisiList() style='width:153px;'>".$optunit2."</select></td>
			
				<td>".$_SESSION['lang']['afdeling']."</td>
				<td>:</td>
				<td><select id=afdlist  style='width:153px;'>".$optafd2."</select></td>
			</tr>
		";

echo "<tr>";
echo "<td></td>";
echo "<td></td>";
echo "<td>
	<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
	<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
</td>";
echo "</tr></td></tr></table>";

echo"</fieldset></table><div style=clear:both></div>";
CLOSE_BOX();
echo"<div id=listData style=display:block>";
OPEN_BOX();
echo"<div class='table-scroll' id=contloaddata>    
		<table cellpading=5 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
			<tr class=rowheader>
			<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['periode']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['unitkerja']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
			<th align=center colspan=3>Hasil Kerja</th>

			<th align=center colspan=2>".$_SESSION['lang']['brondol']."</th>
			<th align=center colspan=3>".$_SESSION['lang']['premi']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['denda']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>
			<th align=center rowspan=2>".$_SESSION['lang']['updateby'] . "</th>
			<th align=center rowspan=2>".$_SESSION['lang']['status'] . "</th>
			<th align=center rowspan=2 colspan=5>" . $_SESSION['lang']['action'] . "</th>
		</tr>
		<tr>
			<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>
			<th align=center rowspan=2>Total Kg</th>
			<th align=center rowspan=2>".$_SESSION['lang']['rp']."</th>

			<th align=center>".$_SESSION['lang']['kg']."</th>
			<th align=center>".$_SESSION['lang']['rp']."</th>

			<th align=center>Premi Lebih Basis</th>
			<th align=center>Premi Kehadiran</th>
			<th align=center>Premi Kesulitan</th>
		</tr>
			
		</thead>
		 <tbody id=printContainerlist> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
		 ";
echo"<div id=contpivot style=display:none></div>";		 
CLOSE_BOX();
echo"</div>";

echo"<div id=detail style=display:none>";
OPEN_BOX();
$arr="##prd##unit##afd##tahap##tgl1##tgl2##kgbrondol##perpot";
echo"<fieldset style=float:left;><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td colspan=3><select id=prd onchange=gettanggal2(); style='width:173px;'>".$optprd."</select>
		</td>
	</tr>
	<tr hidden>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select id=tahap onchange=gettanggal2(); style='width:173px;'>".$opttahap."</select>
		</td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' onchange=gettanggal2(); class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' style='width:170px;' readonly></td><td><input hidden type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10' readonly></td>
    </tr>
	
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td colspan=3><select id=unit  style='width:173px;' onchange=getdivisi()>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td colspan=3><select id=afd  style='width:173px;'>".$optafd."</select></td>
    </tr>";
echo"<tr>
		<td colspan = 2 align=right>
		<button onclick=previewdataBaru() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";
$font="style=font-size:10px;";
echo"<br><table border=0 class=sortable cellspacing=1 cellpadding=3 hidden>
	<tr class=rowheader>
		<td colspan=5><b>Info</b></td>
	</tr>
	<tr class=rowcontent>
		<td  colspan=5 >Pastikan semua transaksi pada menu Kebun - Transaksi - Kegiatan Panen dan Kebun - Transaksi - Surat Pengantar Buah sudah di posting semuanya.<br>
	   </td>
	</tr>
	
	<tr ".$font."  class=rowcontent>
		<td width=75px rowspan=2 ><b>KG WB</b></td><td >-></td><td>Kg Sebelum Pot Brondol</td><td>:</td><td>Kg PKS yang ditampilkan adalah Kg sebelum / tidak dipotong Kg Brondol</td>
	</tr>
	<tr ".$font."  class=rowcontent>
		<td>-></td><td>Kg Setelah Pot Brondol</td><td>:</td><td>Kg PKS yang ditampilkan adalah Kg setelah dipotong Kg Brondol</td>
	</tr>
	
	<tr ".$font." class=rowcontent>
		<td rowspan=3><b>Perhitungan Potongan</b></td><td >-></td><td >Pot Kg dengan biaya Brondol</td><td >:</td><td >Ketika kolom <b>Pot Brondol (Kg)</b> terisi maka <b>Kg TBS</b> akan di <b>Potong</b> dan <b>Rupiah Brondol</b> akan terisi sebesar <b>Kg Brondol x Harga Brondol</b></td>
	</tr>
	<tr ".$font." class=rowcontent>
		<td >-></td><td >Pot Kg dengan tanpa Brondol</td><td >:</td><td >Ketika kolom <b>Pot Brondol (Kg)</b> terisi maka <b>Kg TBS</b> akan di <b>Potong</b></td>
	</tr>
	<tr ".$font." class=rowcontent>
		<td >-></td><td>Pot Rp Denda</td><td>:</td><td>Ketika kolom <b>Pot Brondol (Kg)</b> terisi maka kolom denda akan terisi sebesar <b>Kg Brondol x Harga Brondol</b></td>
	</tr>

	
</table>
";
CLOSE_BOX();
OPEN_BOX();

$_SESSION['temppnn']=array();
//echo"<fieldset  style=min-height:400px ><legend><b>".$_SESSION['lang']['list']."</b></legend>
echo"<div style=min-height:900px id='printContainer'></div>";
//echo"</fieldset>";
echo"</div>";

CLOSE_BOX();
echo"</div>";
echo close_body();
?>
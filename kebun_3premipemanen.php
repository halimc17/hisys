<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_3premipemanen.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optunit=$optafd=$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit2=$optafd2=$optprd2="<option value=''>".$_SESSION['lang']['all']."</option>";


$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
	$optunit2.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optprd.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
	$optprd2.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}
$whr='';
if($_SESSION['empl']['subbagian']!=''){
	$whr=" and kodeorganisasi='".$_SESSION['empl']['subbagian']."'";
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


OPEN_BOX('','<span class=judul>'.getMenu('kebun_3premipemanen').'</span><br>');
$arr="##prd##unit##afd##tahap##tgl1##tgl2";
$frm[0].= "<fieldset style=float:left;height:150px><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td colspan=3><select id=prd  style='width:173px;'>".$optprd."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tahap']."</td>
		<td>:</td>
		<td colspan=3><select id=tahap  style='width:173px;'>".$opttahap."</select>
		</td>
    </tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td><input type='text' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'></td><td>s/d</td><td>
			<input type='text' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='8' maxlength='10'>
		</td>
    </tr>
	
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td colspan=3><select id=unit  style='width:173px;'>".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td colspan=3><select id=afd  style='width:173px;'>".$optafd."</select></td>
    </tr> ";
$frm[0].= "<tr>
		<td colspan=6 align=right>
		<button onclick=zPreview('kebun_slave_3premipemanen','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_3premipemanen.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[0].= "<fieldset  style=height:150px><legend><b>Info</b></legend>
<table>
	<tr>
		<td>Pastikan semua transaksi pada menu Kebun - Transaksi - Kegiatan Panen sudah di posting semuanya.<br>
	   </td>
		
	</tr>
</table>
</fieldset>";

$frm[0].= "<div style=clear:both></div>
<hr><fieldset  style=min-height:400px ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";

$arrlist="##tgl1list##tgl2list##unitlist##afdlist";
$frm[1].= "<fieldset style=float:left><legend><b>Find</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prdlist  style='width:153px;'>".$optprd2."</select></td>
	</tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unitlist  style='width:153px;'>".$optunit2."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['afdeling']."</td>
        <td>:</td>
        <td><select id=afdlist  style='width:153px;'>".$optafd2."</select></td>
    </tr>";
    

$frm[1].= "<tr>
		<td></td><td></td><td>
		<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
		<button onclick=batallist() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset><div style=clear:both></div><hr>";

$frm[1].= "
<fieldset style=min-height:400px><legend><b>".$_SESSION['lang']['list']."</b></legend>
	<div>    
		<table cellpading=1 cellspacing=1 border=0 class=sortable >
		<thead>
			<tr class=rowheader>
			<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['periode']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['unitkerja']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['divisi']."</td>
			<td align=center rowspan=2 width=50px>".$_SESSION['lang']['hk2']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
			<td align=center rowspan=2>Total Kg</td>
			<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>
			<td align=center colspan=2>".$_SESSION['lang']['premi']."</td>
			<td align=center colspan=2>".$_SESSION['lang']['brondol']."</td>
			<td align=center rowspan=2>Kehadiran</td>
			<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['updateby'] . "</td>
			<td align=center rowspan=2>".$_SESSION['lang']['status'] . " ".$_SESSION['lang']['jurnal'] . "</td>
			<td align=center rowspan=2>".$_SESSION['lang']['status'] . " Keg Panen</td>
			<td align=center rowspan=2 colspan=3>" . $_SESSION['lang']['action'] . "</td>
		</tr>
		<tr>
			<td align=center>".$_SESSION['lang']['kg']."</td>
			<td align=center>".$_SESSION['lang']['rp']."</td>
			<td align=center>".$_SESSION['lang']['kg']."</td>
			<td align=center>".$_SESSION['lang']['rp']."</td>
		</tr>
			
		</thead>
		 <tbody id=printContainerlist> 
			<script>loaddata(0)</script>
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
		 
</div></fieldset>"; 

$hfrm[0]=$_SESSION['lang']['premipemanen'];
$hfrm[1]=$_SESSION['lang']['list'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');	

CLOSE_BOX();
echo close_body();
?>
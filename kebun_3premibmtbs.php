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
<script language=javascript src='js/kebun_3premibmtbs.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optunit=$optafd=$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optunit2=$optafd2=$optprd2="<option value=''>".$_SESSION['lang']['all']."</option>";

// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi IN (".getOrgDetail(2).") and tipe='KEBUN'";
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
	// $optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	$optafd2.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

@$optkontan.="<option value='KERJA'>Kerja</option>";
@$optkontan.="<option value='KONTAN'>Kontanan</option>";

OPEN_BOX('','<span class=judul>'.getMenu('kebun_3premibmtbs').'</span><br>');
$arr="##prd##unit##afd##kontanan##tglkontan";
$frm[0].= "<fieldset style=float:left;height:140px><legend><b>Form</b></legend>
<table>
	<tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=prd  style='width:153px;'>".$optprd."</select>
		</td>
	</tr>
    <tr>
        <td>".$_SESSION['lang']['unitkerja']."</td>
        <td>:</td>
        <td><select id=unit  style='width:153px;' onchange=\"getAfd();\">".$optunit."</select></td>
    </tr>
    <tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afd style='width:153px;'>".$optafd."</select></td>
    </tr> 
	<tr hidden>
        <td>Kerja / Kontanan</td>
        <td>:</td>
        <td><select id=kontanan  style='width:153px;' onchange=gettglkontan(this.value,'tanggaltrk')>".$optkontan."</select></td>
    </tr>
	<tr id=tanggaltrk style=display:none>
        <td>".$_SESSION['lang']['tanggal']."</td>
        <td>:</td>
        <td><input type='text' style='width:148px;' class='myinputtext' id='tglkontan' onmousemove='setCalendar(this.id)' onkeypress='return false'>
		
		</td>
	</tr> 	
	";
$frm[0].= "<tr>
		<td colspan=3 align=right>
		<button onclick=zPreview('kebun_slave_3premibmtbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=zExcel(event,'kebun_slave_3premibmtbs.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[0].= "<fieldset  style=height:140px><legend><b>Info</b></legend>
<table>
	<tr>
		<td>Pastikan semua transaksi pada menu Kebun - Transaksi - Surat Pengantar Buah sudah di posting semuanya.<br>
	   </td>
		
	</tr>
</table>
</fieldset>";

$frm[0].= "
<hr><fieldset  style=min-height:350px ><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'>
</div></fieldset>";

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
        <td><select id=unitlist style='width:153px;' onchange=\"getAfd2();\">".$optunit2."</select></td>
    </tr>
	<tr>
        <td>".$_SESSION['lang']['divisi']."</td>
        <td>:</td>
        <td><select id=afdlist  style='width:153px;'>".$optafd2."</select></td>
    </tr>
	<tr hidden>
        <td>Kontanan</td>
        <td>:</td>
        <td><select id=kontlist  style='width:153px;'>".$optkontan."</select></td>
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
<fieldset style=min-height:350px><legend><b>".$_SESSION['lang']['list']."</b></legend>
	<div>    
		<table cellpading=5 cellspacing=1 width=100% border=0 class=sortable >
		<thead>
			<tr class=rowheader>
			<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['periode']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['unitkerja']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['divisi']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['jenis']."</td>
			<td align=center rowspan=2>Kerja / Kontanan</td>
			<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['kgwb']."</td>
			<td align=center rowspan=2 width=30px>".$_SESSION['lang']['jumlahhk']."</td>
			<td align=center rowspan=2 width=30px>".$_SESSION['lang']['upah']." (Rp)</td>
			<td align=center rowspan=2 width=75px>".$_SESSION['lang']['premi']." (Rp)</td>
			<td align=center rowspan=2>".$_SESSION['lang']['updateby']."</td>
			<td align=center rowspan=2>".$_SESSION['lang']['status']."</td>
			<td align=center rowspan=2 colspan=4>" . $_SESSION['lang']['action'] . "</td>
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

$hfrm[0]='Premi BM TBS';
$hfrm[1]=$_SESSION['lang']['list'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');	

CLOSE_BOX();
echo close_body();
?>
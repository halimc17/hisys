<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('keu_leasing')."</span>");
?>

<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script type="text/javascript" src="js/keu_leasing.js" /></script>

<?php
$arr="##notransaksi##notadebet##unit##rekening##namaasuransi##kontrakasuransi##namavendor##method";
$arr.="##kontrakvendor##tglefektif##tgllunas##statuskontrak##kuantitas##nopol##hargabarang##uangmuka##utangpokok";
$arr.="##sukubunga##bunga##tenor##totalkredit##angsuran##metbayar##pembayaran##administrasi##survey##asuransi##fidusia";
$arr.="##provisi##notaris##denda";

echo"<table>
     <tr valign=moiddle>
        <td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
           <img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."</td>
        <td align=center style='width:100px;cursor:pointer;' onclick=displaylist(0)>
           <img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."</td>
        <td><fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
echo $_SESSION['lang']['notransaksi']." : <input type=text id=nocr class=myinputtext style=width:150px;>";
echo"<button class=mybutton onclick=loadData(0)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
     </table>"; 
CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpading=1 cellspacing=1 border=0 class=sortable >";
echo"<thead>";
echo"<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
echo"<td>".$_SESSION['lang']['notransaksi']."</td>";
echo"<td>".$_SESSION['lang']['unit']."</td>";
echo"<td>".$_SESSION['lang']['rekening']."</td>";
echo"<td>".$_SESSION['lang']['tanggal']." Efektif</td>";
echo"<td>".$_SESSION['lang']['tanggal']." Pelunasan</td>";
echo"<td>".$_SESSION['lang']['status']."</td>";
echo"<td colspan=4>".$_SESSION['lang']['action']."</td>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=method value=insertht />";

$optmetbayar=$optnopol=$optstat=$optsupas=$optbank=$optunit=$optsupls="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
#nama unit
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
	if(($kei!='')&&(substr($kei,0,5)!='Pilih')){
		$optunit.="<option value='".$kei."'>".$kei."-".$fal."</option>";	
	}
}

#supplier asuransi
$str=$owlPDO->query("select a.supplierid,b.namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.tipe='ASURANSI' and b.status=1 order by b.namasupplier asc");
$str->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$str->fetch()){
    $optsupas.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}

#supplier vendor
$str=$owlPDO->query("select a.supplierid,b.namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.tipe='LEASING' and b.status=1 order by b.namasupplier asc");
$str->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$str->fetch()){
    $optsupls.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']."</option>";
}

#status kontrak
$arrstatus = array('1' =>$_SESSION['lang']['aktif'],'2' =>$_SESSION['lang']['lunas']);
foreach ($arrstatus as $key => $val) {
	$optstat.="<option value='".$key."'>".$val."</option>";
}

$arrmetbayar = getEnum($dbname,'keu_kasbankht','cgttu');
foreach ($arrmetbayar as $key => $val) {

	if ($val=='Cash') {
		continue;
	}

	$optmetbayar.="<option value='".$key."'>".$val."</option>";
}

echo"<div id=formInput style=display:none;>";
echo"<fieldset  ><legend>".$_SESSION['lang']['form']."</legend><table border=0>";
echo"<tr>
		<td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
		<td><input type=text id=notransaksi class=myinputtext style=width:150px; disabled readonly></td>
		
		<td>".$_SESSION['lang']['lokasi']."</td><td>:</td>
		<td><select id=unit style=width:150px; onchange='getreknopol()'>".$optunit."</select>
		<img id=unit onclick=z.elSearch('unit',event) class=resicon src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>
		</td>
		
		<td>".$_SESSION['lang']['rekening']."</td><td>:</td>
		<td><select id=rekening style=width:150px;>".$optbank."</select></td>
	</tr>

	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>
	<tr>
		<td colspan=10 style='font-weight: bold;'><i><b>".$_SESSION['lang']['vendorasuransi']."</i></b></td>
	</tr>
	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>

	<tr>
		<td>".$_SESSION['lang']['namaasuransi']."</td><td>:</td>
		<td><input type=text id=namaasuransi class=myinputtext style=width:150px; /></td>

		<td>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['asuransi']."</td><td>:</td>
		<td><input type=text id=kontrakasuransi class=myinputtext style=width:150px; /></td>
	</tr>

	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>
	<tr>
		<td colspan=10 style='font-weight: bold;'><i><b>".$_SESSION['lang']['kontrakleasing']."</i></b></td>
	</tr>
	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>

	<tr>
		<td>".$_SESSION['lang']['no_notadebet']."</td><td>:</td>
		<td>
			<input type=text id=notadebet class=myinputtext style=width:150px; />
			<img src=\"images/onebit_02.png\" style='float:right;' id=tombnota style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."' onclick=\"searchnotadebet('".$_SESSION['lang']['find']."','<div id=formPencariandata></div>',event)\">
		</td>

		<td>".$_SESSION['lang']['namavendor']."</td><td>:</td>
		<td><select id=namavendor style=width:150px;>".$optsupls."</select></td>

		<td>".$_SESSION['lang']['NoKontrak']." Lessor</td><td>:</td>
		<td><input type=text id=kontrakvendor class=myinputtext style=width:150px; /></td>
	</tr>

	<tr>
		<td>".$_SESSION['lang']['statuskontrak']."</td><td>:</td>
		<td><select id=statuskontrak  style=width:155px;>".$optstat."</select></td>

		<td>".$_SESSION['lang']['kuantitas']."</td><td>:</td>
		<td><input type=text id=kuantitas class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' /></td>
		
		<td>".$_SESSION['lang']['nopol']."</td><td>:</td>
		<td><select id=nopol  style=width:153px;>".$optnopol."</select></td>
	</tr>
	
	<tr>
		<td>".$_SESSION['lang']['tanggal']." Efektif</td><td>:</td>
		<td><input type=text class=myinputtext id=tglefektif onmousemove=setCalendar(this.id) onchange='getBulan()'  style=width:150px;  maxlength=10 readonly/></td>

		<td>".$_SESSION['lang']['tanggal']." Pelunasan</td><td>:</td>
		<td><input type=text class=myinputtext id=tgllunas onmousemove=setCalendar(this.id) onchange='getBulan()'  style=width:150px; maxlength=10 readonly/></td>

	</tr>

	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>
	<tr>
		<td colspan=10 style='font-weight: bold;'><i><b>Nilai Kontrak</i></b></td>
	</tr>
	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>

	<tr>
		<td>".$_SESSION['lang']['hargabarang']."</td><td>:</td>
		<td><input type=text id=hargabarang class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('hargabarang',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>

		<td>".$_SESSION['lang']['uangmuka']."</td><td>:</td>
		<td><input type=text id=uangmuka class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('uangmuka',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>
		
		<td>".$_SESSION['lang']['utangpokok']."</td><td>:</td>
		<td><input type=text id=utangpokok class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 disabled /></td>
	</tr>

	<tr>
		<td>Tingkat ".$_SESSION['lang']['sukubunga']." (per Tahun)</td><td>:</td>
		<td><input type=text id=sukubunga class=myinputtextnumber  style=width:150px; onkeyup='getnilai()' onkeypress='return angka_doang(event)' value=0 />%</td>

		<td>Bunga (per Tahun)</td><td>:</td>
		<td><input type=text id=bunga class=myinputtextnumber style=width:150px;  onkeypress='return angka_doang(event)' disabled value=0/></td>
		
		<td>".$_SESSION['lang']['jumlahbulan']."</td><td>:</td>
		<td><input type=text id=tenor class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' disabled value=0/></td>	
	</tr>

	<tr>
		<td>Total Kredit (per Tahun)</td><td>:</td>
		<td><input type=text id=totalkredit class=myinputtextnumber style=width:150px; disabled onkeypress='return angka_doang(event)' value=0 /></td>

		<td>".$_SESSION['lang']['angsuran']."</td><td>:</td>
		<td><input type=text id=angsuran class=myinputtextnumber style=width:150px; disabled onkeypress='return angka_doang(event)' value=0 /></td>
		
		<td>".$_SESSION['lang']['metodepembayaran']."</td><td>:</td>
		<td><select id=metbayar style=width:153px; >".$optmetbayar."</select></td>	
	</tr>

	<tr>
		<td>".$_SESSION['lang']['pembayaran']." I</td><td>:</td>
		<td><input type=text id=pembayaran class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('pembayaran',2)\" onkeypress='return angka_doang(event)' value=0 disabled /></td>
	</tr>

	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>
	<tr>
		<td colspan=10 style='font-weight: bold;'><i><b>Biaya - Biaya</i></b></td>
	</tr>
	<tr><td colspan=10 ><hr style='border-top: 1.5px solid #707070;'></td></tr>

	<tr>
		<td>(1) Administrasi</td><td>:</td>
		<td><input type=text id=administrasi class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('administrasi',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>

		<td>(2) Survey</td><td>:</td>
		<td><input type=text id=survey class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('survey',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>
		
		<td>(3) Asuransi</td><td>:</td>
		<td><input type=text id=asuransi class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('asuransi',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>	
	</tr>

	<tr>
		<td>(4) Fidusia</td><td>:</td>
		<td><input type=text id=fidusia class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('fidusia',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>

		<td>(5) Provisi</td><td>:</td>
		<td><input type=text id=provisi class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('provisi',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>
		
		<td>(6) Notaris</td><td>:</td>
		<td><input type=text id=notaris class=myinputtextnumber style=width:150px; onkeyup=\"z.numberFormat('notaris',2); return getnilai()\" onkeypress='return angka_doang(event)' value=0 /></td>	
	</tr>

	<tr>
		<td>(7) Denda Keterlambatan Pembayaran</td><td>:</td>
		<td><input type=text id=denda class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 />%</td>
	</tr>
	<tr>
		<td><td>
		<td><button class=mybutton onclick=saveData('keu_slave_leasing','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
        	<button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>";
echo"</table></fieldset>";

echo"</div>";
CLOSE_BOX();
echo close_body();
?>

<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
include('lib/rTable.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2.php');
?>

<script language=javascript src='js/pmn_batransportv2.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
$optunit = $optnodo = $optkomoditi = $opttransportir = $optspk = $optkontrak = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$indukex = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk'); 
$nmsupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

## GET KOMODITI
$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400' order by kodebarang limit 5";
$res = fetchdata($str);
foreach ($res as $val) {
	@$optkomoditi .= "<option value='" . $val['kodebarang'] . "'>" . $val['namabarang'] . "</option>";
}

## GET UNIT
$str = "select * from " . $dbname . ".organisasi where tipe='PABRIK' and namaorganisasi not like '%BULKING%'";
$res = fetchdata($str);
foreach ($res as $val) {
	@$optunit .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['namaorganisasi'] . "</option>";
}

## GET NKONTRAK
$str = "select nokontrak from " . $dbname . ".pmn_kontrakjual where posting='1' and close!='1' order by nokontrak";
$res = fetchdata($str);
foreach ($res as $val) {
	@$optkontrak .= "<option value='" . $val['nokontrak'] . "'>" . $val['nokontrak'] . "</option>";
}

## GET NODO
$str = "select nodo from " . $dbname . ".pmn_suratperintahpengiriman where posting='1' ";
$res = fetchdata($str);
foreach ($res as $val) {
	@$optnodo .= "<option value='" . $val['nodo'] . "'>" . $val['nodo'] . "</option>";
}

## GET TRANSPORTIR
$nmSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$str = "select distinct supplierid from " . $dbname . ".log_5supkelompok where tipe='TRANSPORTIR' and status='1' order by supplierid asc";
$res = fetchdata($str);
foreach ($res as $val) {
	$opttransportir .= "<option value='" . $val['supplierid'] . "'>" . $val['supplierid'] . " - " . $nmSupp[$val['supplierid']] . "</option>";
}

$arrOpt = array(
	0 => 'Normal',
	1 => 'Return'
);

// $optjenis = "<option value=''>Pilih Jenis</option>";
foreach ($arrOpt as $key => $val) {
	$optjenis .= "<option value='{$key}'>{$val}</option>";
}

?>
<!--HEADER UNTUK BUAT BARU SAMA LIST-->
<?php
echo "<div>"; //buka div
OPEN_BOX('', '<span class=judul>' . getMenu('pmn_batransportv2') . '</span>');
echo "<table>
	<tr valign=middle>
		<td align=center style='width:70px;cursor:pointer;' onclick=newdata()>
			<img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "
		</td>
		<td align=center style='width:70px;cursor:pointer;' onclick=displaylist()>
			<img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "
		</td>
		<td>
		<fieldset>
		<legend>" . $_SESSION['lang']['find'] . "</legend>
		<table>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>		
				<td>
					<input type=text id=notransaksisch size=50 class=myinputtext style=\"width:150px;\">
				</td>
				
				<td style='padding-left:10px'>" . $_SESSION['lang']['tanggal'] . "</td>
				<td>:</td>		
				<td>
					<input type=text class=myinputtext id=tanggalmulaisch name=tanggalmulaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:63px;/>
					s/d
					<input type=text class=myinputtext id=tanggalselesaisch name=tanggalselesaisch readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:63px;/>			
				</td>

				<td>" . $_SESSION['lang']['transportir'] . "</td>
				<td>:</td>		
				<td>
					<select class='select2' id='transportirsch' onchange=loaddata() style='width:150px;'>{$opttransportir}</select>
				</td>
				
				<td>" . $_SESSION['lang']['komoditi'] . "</td>
				<td>:</td>		
				<td>
					<select class='select2' id='komoditisch' onchange=loaddata() style='width:150px;'>{$optkomoditi}</select>
				</td>
				
				<td style='padding-left:10px' colspan=3>
					<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
				</td>
			</tr>
		</table>
		</fieldset>
		</td>
	</tr>
</table> ";
CLOSE_BOX();
echo "</div>"; //tutup div



#=<!--UNTUK LIST DATA,, PADA SAAT MASUK MENU TAMPILIN INI YG ADA SETELAH HEADER-->
echo "<div id=listdata style=display:block>"; //buka list data
OPEN_BOX();
echo "<div class=table-scroll style='height:70vh'>
<table cellpadding=5 cellspacing=1 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
		<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
		<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th> 
		<th align=center>" . $_SESSION['lang']['transportir'] . "</th>
		<th align=center>" . $_SESSION['lang']['noinvoice'] . "</th>
		<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
		<th align=center>" . $_SESSION['lang']['unit'] . "<br>" . $_SESSION['lang']['pabrik'] . "</th>
		<th align=center>" . $_SESSION['lang']['beratBersih'] . "<br>" . $_SESSION['lang']['kirim'] . "</th>
 		<th align=center>" . $_SESSION['lang']['jumlahrp'] . "</th>
 		<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
		<th align=center>" . $_SESSION['lang']['createby'] . "</th>
		<th align=center>" . $_SESSION['lang']['status'] . " Approval</th>
		<th align=center colspan=10>" . $_SESSION['lang']['action'] . "</th>  
	</tr>
	</thead>
    <tbody id=contain><script>loaddata(0)</script></tbody>
	<tfoot id=footData></tfoot>
</table>
</div>";
CLOSE_BOX();
echo "</div>"; //tutup list data


#= <!--UNTUK BUAT FORM INPUT HEADER-->

echo "<div id=header style=display:none>";
OPEN_BOX('', '<span class=judul>' . $_SESSION['lang']['header'] . '</span><br>');
echo "<fieldset>
<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>
<table cellspacing=1 border=0>
	<tr>
		<td>" . $_SESSION['lang']['notransaksi'] . "</td>
		<td>:</td>		
		<td><input type=text id=notransaksi size=20 disabled class=myinputtext style=\"width:200px;\"></td>		
		<td style='padding-left:10px;'>" . $_SESSION['lang']['komoditi'] . " </td>
		<td>:</td>		
		<td>
			<select class='select2' id='komoditi'  style=\"width:205px;\">" . $optkomoditi . "</select>
		</td>
		<td style='padding-left:10px;'>No. Kontrak</td>
		<td>:</td>		
		<td>
			<select class='select2' id='nokontrak'  style=\"width:205px;\">" . $optkontrak . "</select>
		</td>
		<td style='padding-left:10px;'>No.DO</td>
		<td>:</td>		
		<td>
			<select class='select2' id='nodo'  style=\"width:205px;\">" . $optnodo . "</select>
		</td>
		 
		<td style='padding-left:10px;'>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['dokumen'] . "</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal placeholder='Tanggal Dokumen Berita Acara' name=tanggal name=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:170px;/>
		</td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>		
		<td>
			<select class='select2' id='unit'   style=\"width:205px;\">" . $optunit . "</select>
		</td>

		<td style='padding-left:10px;'>" . $_SESSION['lang']['transportir'] . "</td>
		<td>:</td>		
		<td>
			<select class='select2' id='tipe' onchange=\"getpphpersen()\" style=\"width:205px;\">" . $opttransportir . "</select>
		</td>
		
		<td hidden style='padding-left:10px'>" . $_SESSION['lang']['nospk'] . "</td>
		<td hidden>:</td>		
		<td hidden>
			<select class='select2' id='nospk' style=\"width:205px;\">" . $optspk . "</select>
		</td>
		
		<td style='padding-left:10px'>Tipe BA</td>
		<td>:</td>		
		<td>
			<select class='select2' id='jenisba' style=\"width:205px;\">" . $optjenis . "</select>
		</td>
		
		<td style='padding-left:10px;'>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['kirim'] . "</td>
		<td>:</td>	
		<td>
			<input type=text class=myinputtext placeholder='Tanggal Mulai Kirim' id=tanggalkirim1 name=tanggalkirim1 readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:70px;/>
			s/d <input type=text class=myinputtext placeholder='Tanggal Sampai Kirim' id=tanggalkirim2 name=tanggalkirim2 readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=width:72px;/>
		</td>

		<td style='padding-left:10px;'>Persen PPh </td>
		<td>:</td>		
		<td>
			<input type=text id=persenpph class=myinputtextnumber style=\"width:30px;\" value='0.25'>
	 		Persen PPn :
			<input type=text id=persenppn class=myinputtextnumber style=\"width:30px;\" value='11'>
		</td>
	</tr>
	<tr>
		<td valign=top>" . $_SESSION['lang']['noinvoice'] . "</td>	
		<td valign=top>:</td>
		<td valign=top>
			<input type=text id=noinvoice class=myinputtext style=\"width:205px;\" placeholder='noinvoice'>
		</td>
		<td style='padding-left:10px;'>Persen Toleransi </td> 
		<td valign=top>:</td>
		<td valign=top>	
			<input type=text id=persentlrsusut class=myinputtextnumber style=width:30px; onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('persentlrsusut')\" onkeypress='return tanpa_kutip(event)'/>  %
		</td>
	</tr>
	<tr>
		<td valign=top>" . $_SESSION['lang']['keterangan'] . "</td>	
		<td valign=top>:</td>
		<td valign=top>
			<textarea rows='2' id=keterangan placeholder='keterangan' type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:200px;\"></textarea>
		</td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td >
		<button id=saveht class=mybutton onclick=saveht()>" . $_SESSION['lang']['save'] . "</button><button id=cancelhtd class=mybutton onclick=cancelht()>" . $_SESSION['lang']['cancel'] . "</button></td>
	</tr>
	</table>
</fieldset>"; //<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button></td>


CLOSE_BOX();
echo "</div>";



$border = '0';
echo "<div id=detail style=display:none>";
OPEN_BOX('', '<span class=judul>' . $_SESSION['lang']['detail'] . '</span>');
echo "
 
	  <div class=table-scroll style='height:250px'>
      <table cellpadding=3 cellspacing=1 border=0 class=sortable width=100%>
      <thead>
        <tr class=rowheader>";
echo "<th align=center>" . $_SESSION['lang']['nourut'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['kirim'] . "</th>";
// echo "<th align=center>".$_SESSION['lang']['nodo']."</th>";
// echo "<th align=center>".$_SESSION['lang']['NoKontrak']."</th>";
echo "<th align=center>" . $_SESSION['lang']['komoditi'] . "</th>";
// echo "<th align=center>".$_SESSION['lang']['transportir']."</th>";
echo "<th align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['kirim'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['nopol'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['berat'] . " " . $_SESSION['lang']['masuk'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['berat'] . " " . $_SESSION['lang']['keluar'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>";

echo "<th align=center>Kg Selisih</th>";
echo "<th align=center>" . $_SESSION['lang']['rpperkg'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['kodecustomer'] . "</th>";
echo "<th align=center>" . $_SESSION['lang']['kode'] . " Supplier</th>";
echo "<th align=center>" . $_SESSION['lang']['jumlahrp'] . "</th>";
echo "<th align=center>Tiket Referensi</th>";
echo "<th align=center>Harga Potongan</th>";
echo "<th align=center>Total (Rp)</th>";

echo "<th hidden align=center>" . $_SESSION['lang']['noTiket'] . " " . $_SESSION['lang']['tujuan'] . "</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>";
echo "<th hidden align=center>Tonbag</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['selisih'] . "<br>(" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['diterima'] . "-" . $_SESSION['lang']['beratBersih'] . " " . $_SESSION['lang']['kirim'] . ")</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['toleransi'] . " (%)</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['toleransi'] . " (Kg)</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['klaim'] . "<br>(" . $_SESSION['lang']['selisih'] . "-" . $_SESSION['lang']['kg'] . "<br>" . $_SESSION['lang']['klaim'] . ")</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['rpperkg'] . " " . $_SESSION['lang']['klaim'] . "</th>";
echo "<th hidden align=center>" . $_SESSION['lang']['jumlahrp'] . " " . $_SESSION['lang']['klaim'] . "</th>";
echo "</tr> 
      </thead>
       <tbody id=listdatadt> 
       </tbody>
       </table>";

CLOSE_BOX();
echo "</div>";
?>

<script>
	getSelect2();
</script>

<?php
echo close_body();		////<input type=hidden id=method value='insert'>	
?>
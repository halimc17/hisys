<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<link rel=stylesheet type=text/css href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<!-- <script type="text/javascript" src="js/pmn_suratperintahpengiriman.js?ver=1.1" /></script> --> 
<script language=javascript src='js/pmn_suratperintahpengiriman.js?v=<?php echo time(); ?>'></script>

<?php
OPEN_BOX('','<span class=judul>'.getMenu('pmn_suratperintahpengiriman').'</span>');
$optBrgsch="<option value=''>".$_SESSION['lang']['all']."</option>";
$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'",'2');
foreach ($nmkomoditi as $key => $value) {
	$optBrgsch.="<option value='".$key."'>".explode('-',$value)[1]."</option>";
}
echo"<table>
    	<tr valign=moiddle>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=displayFormInput()>
	   			<img class=delliconBig src=images/skyblue/addbig.png title='".$_SESSION['lang']['new']."'><br>".$_SESSION['lang']['new']."
			</td>
	 		<td align=center style='width:100px;cursor:pointer;' onclick=loadData(0)>
	   			<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
	 		<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>"; 
				echo $_SESSION['lang']['NoKontrak']." : <input type=text onkeypress='enterkey(event,cariData)' id=txtsearchkontrak size=25 maxlength=50 class=myinputtext> &nbsp";
				echo $_SESSION['lang']['nodo']." : <input type=text onkeypress='enterkey(event,cariData)' id=txtsearch size=25 maxlength=30 class=myinputtext> &nbsp";
				echo $_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/> &nbsp";
				echo $_SESSION['lang']['produk']." : <select class='select2' id=produksch name=produksch style=\"width:155px;\">".$optBrgsch."</select>
				<button class=mybutton onclick=cariData(0)>".$_SESSION['lang']['find']."</button>
				<button class=mybutton onclick=clearSearch()>".$_SESSION['lang']['cancel']."</button>
				</fieldset>
			</td>
		</tr>
	 </table> "; 

CLOSE_BOX();

OPEN_BOX();
echo"<div id=listData>";
// echo"<fieldset><legend>".$_SESSION['lang']['data']."</legend>";
echo"<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>";
echo"<thead>";
echo"<tr><th align=center>".$_SESSION['lang']['nourut']."</th>";
echo"<th align=center>".$_SESSION['lang']['nodo']."</th>";
echo"<th align=center>".$_SESSION['lang']['tanggalsurat']."</th>";
echo"<th align=center>".$_SESSION['lang']['NoKontrak']."</th>";
echo"<th align=center>".$_SESSION['lang']['Pembeli']."</th>";
echo"<th align=center>".$_SESSION['lang']['komoditi']."</th>";
echo"<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
echo"<th align=center>".$_SESSION['lang']['toleransi']." %</th>"; 
echo"<th align=center>".$_SESSION['lang']['harga']."</th>";
echo"<th align=center>".$_SESSION['lang']['namakapal']."</th>";
echo"<th align=center>".$_SESSION['lang']['namaponton']."</th>";
echo"<th align=center>".$_SESSION['lang']['dibuatoleh']."</th>";
echo"<th colspan=5 style='text-align:center;'>".$_SESSION['lang']['action']."</th>";
echo"</tr></thead><tbody id=continerlist>";
echo"<script>loadData(0)</script>";
echo"</tbody>";
$skeupenagih="select count(*) as rowd from ".$dbname.".keu_penagihanht where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
$qkeupenagih=$owlPDO->query($skeupenagih) or die(print " Gagal: ".PDOException::getMessage());
$rkeupenagih=owlBaris($qkeupenagih);

$totrows=ceil($rkeupenagih/10);
if($totrows==0){
    $totrows=1;
}
$isiRow='';
for($er=1;$er<=$totrows;$er++){
    $isiRow.="<option value='".$er."'>".$er."</option>";
}
echo"<tfoot id=footData>";
echo"</tfoot></table></fieldset>";
echo"</div><input type=hidden id=proses value=insert />";

#byr ke
$whereJam=" kasbank=1 and detail=1 and (pemilik='".$_SESSION['empl']['tipelokasitugas']."' or pemilik='GLOBAL' or pemilik='".$_SESSION['empl']['lokasitugas']."')";
$sakun="select distinct noakun,namaakun from ".$dbname.".keu_5akun 
        where  ".$whereJam." order by namaakun asc";
$optAkun='';
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
{
    $optAkun.="<option value='".$rakun['noakun']."'>".$rakun['noakun']."-".$rakun['namaakun']."</option>";
}


$optKepada="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iKepada=" select * from ".$dbname.".pmn_5kepada order by kepada asc ";
$nKepada=$owlPDO->query($iKepada) or die(print " Gagal: ".PDOException::getMessage());
$nKepada->setFetchMode(PDO::FETCH_ASSOC);
while($dKepada=$nKepada->fetch())
{
    $optKepada.="<option value='".$dKepada['id']."'>".$dKepada['kepada']." - ".$dKepada['alamat']."</option>";
}

$optKarid = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optpph=$optttd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iTtd=" select * from ".$dbname.".pmn_5ttd order by nama asc ";
$nTtd=$owlPDO->query($iTtd) or die(print " Gagal: ".PDOException::getMessage());
$nTtd->setFetchMode(PDO::FETCH_ASSOC);
while($dTtd=$nTtd->fetch())
{
    $optttd.="<option value='".$dTtd['nama']."'>".$optKarid[$dTtd['nama']]." - ".$dTtd['jabatan']."</option>";
}

$optSerah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$iX="select * from  ".$dbname.".pmn_5franco ";
$nX=$owlPDO->query($iX) or die(print " Gagal: ".PDOException::getMessage());
$nX->setFetchMode(PDO::FETCH_ASSOC);
while($dX=$nX->fetch())
{
    $optSerah.="<option value='".$dX['id_franco']."'>".$dX['franco_name']."</option>";
}


#kodepelanggan
$optCust='';
$sakun="select distinct kodecustomer,namacustomer from ".$dbname.".pmn_4customer
        order by namacustomer asc";
$qakun=$owlPDO->query($sakun) or die(print " Gagal: ".PDOException::getMessage());
$qakun->setFetchMode(PDO::FETCH_ASSOC);
while($rakun=$qakun->fetch())
    {
    $optCust.="<option value='".$rakun['kodecustomer']."'>".$rakun['kodecustomer']."-".$rakun['namacustomer']."</option>";
}

$opttrans="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$nmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$str="select distinct supplierid from ".$dbname.".log_5supkelompok where tipe='TRANSPORTIR' and status='1'
        order by supplierid asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
    {
    $opttrans.="<option value='".$bar['supplierid']."'>".$nmSupp[$bar['supplierid']]."</option>";
}

$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'  order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
    {
    @$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$optkapal=$optponton="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// $str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton  where transportir='".$transportir."' and kode in ('".$namakapal."','".$namaponton."')";
	$str = "SELECT * FROM " . $dbname . ".pmn_5kapalponton";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar = $res->fetch()){
		if($bar['jenis']=='KPL'){
			$optkapal.="<option value=" . $bar['kode'] . " ".@$selected.">" . $bar['nama'] . "</option>";
		}
		
		if($bar['jenis']=='PNT'){
			$optponton.="<option value=" . $bar['kode'] . " ".@$selected.">" . $bar['nama'] . "</option>";
		}
	}


// $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where tipekaryawan in ('7','8') 
//         order by karyawanid asc";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch())
//     {
//     @$optttd.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
// }
$optspkpemuat="<option value=''>Pilih Data</option>";
$str="select distinct(nospk) as nospk from ".$dbname.".pmn_spk_sum 
        order by nospk asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
    {
    $optspkpemuat.="<option value='".$bar['nospk']."'>".$bar['nospk']."</option>";
}

$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' 
        order by kodeorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
    {
    @$optplbmuat.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

$optlokdo='';
$optlokdo.="<option value=''></option>";
$str="select distinct inisial,lokasi from ".$dbname.".pmn_5lokasikontrak where status='1'
        order by inisial asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
    {
    $optlokdo.="<option value='".$bar['inisial']."'>".$bar['inisial']."-".$bar['lokasi']."</option>";
}

$optDtstat=array("0"=>$_SESSION['lang']['tidak'],"1"=>$_SESSION['lang']['ya']);
foreach ($optDtstat as $key => $value) {
    $optpph.="<option value='".$key."'>".$value."</option>";
}
$optTimStat=array("0"=>"Timbangan Internal","1"=>"Timbangan Pembeli");
foreach ($optTimStat as $key => $value) {
    @$optTim.="<option value='".$key."'>".$value."</option>";
}

$arrpembayaran=getEnum($dbname,'pmn_suratperintahpengiriman','sistempenyerahan');
foreach($arrpembayaran as $kei=>$fal)
{
   
        @$optpenye.="<option value='".$kei."'>".$fal."</option>";
}

$str = "select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$optbarang.="<option selected value='".$bar['kodebarang']."'>".$bar['namabarang']."</option>";
}

$str = "select * from ".$dbname.".pmn_5franco order by franco_name asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$optfranco.="<option value='".$bar['id_franco']."'>".$bar['franco_name']."</option>";
}

$optnoakun = "<option value=>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE noakun LIKE '81101%' OR noakun LIKE '81102%'";
$res = fetchdata($str);
foreach($res as $key=>$val){
	$optnoakun .= "<option value=".$val['noakun'].">(".$val['noakun'].") ".$val['namaakun']."</option>";
}



$arr="##nokontrak##namaponton##nodo##kodecustomer##kepada##tanggalsurat##waktupenyerahan##tempatpenyerahan##dibuat##lain##jabatan##ttd1##ttd2##qty##harga##statpph##subsidi##statTimbangan##transportir##lokasido##penyerahan##nmkpl##tgltiba1##tgltiba2##plbbongkar##spkpmuat##agen##plbmuat##kondisi##pt##kodebarang##tglberangkat##toleransi##kgtoleransi##noakun";
echo"<div id=formInput style=display:none;>";
echo"<fieldset style=float:left;><legend>".$_SESSION['lang']['form']."</legend>
    <table style=width:100%;>";
echo"<tr><td>".$_SESSION['lang']['NoKontrak']."</td><td><input type=text id=nokontrak class=myinputtext style=width:150px; readonly onchange=\"getPage()\" onclick=\"searchKontrak('".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."','Eksternal','<div id=formPencariandata></div>',event)\" /></td>
<td>".$_SESSION['lang']['pt']."</td><td><select id=pt style=width:155px>".$optpt."</select></td>";

	
echo"</tr>";
echo"<tr>";
echo"<td>".$_SESSION['lang']['komoditi']."</td>	
	<td>
		<select id=kodebarang  style=\"width:155px;\">'".$optbarang."'</select>
	</td>
	<td hidden>" . $_SESSION['lang']['noakun'] . "</td>
	<td hidden>
		<select id=noakun style=\"width:155px;\">'" . $optnoakun . "'</select>
		<img id=noakun onclick=z.elSearch('noakun',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
	</td>
</tr>";



echo"<tr><td>".$_SESSION['lang']['lokasido']."</td><td><select id=lokasido style=width:155px>".$optlokdo."</select></td>";

echo"<td>".$_SESSION['lang']['transportir']."</td><td><select id=transportir style=width:155px>".$opttrans."</select>
<img id=transportir onclick=z.elSearch('transportir',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
</td>";

echo"</tr>";

echo"<tr><td>".$_SESSION['lang']['nodo']."</td><td><input disabled type=text id=nodo class=myinputtext style=width:150px;  readonly></td>";

echo"<td hidden>".$_SESSION['lang']['kepada']."</td>"
    . "<td hidden><select id=kepada style=width:155px>".$optKepada."</select>
	<img id=kepada onclick=z.elSearch('kepada',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>"
    . "</tr>";//

echo"<tr><td>".$_SESSION['lang']['Pembeli']."</td><td><select id=kodecustomer style=width:155px disabled=true>".$optCust."</select></td>";
echo"<td>".$_SESSION['lang']['tanggalsurat']."</td><td><input type=text class=myinputtext id=tanggalsurat readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:150px;  maxlength=10 /></td></tr>";


echo"<tr hidden><td>".$_SESSION['lang']['waktupenyerahan']."</td><td><input type=text id=waktupenyerahan class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' /></td>";
//echo"<td>".$_SESSION['lang']['tandatangan']."</td><td style='vertical-align:top;'><select id=ttd style=width:155px>".$optTtd."</select></td>";
echo"<td hidden>".$_SESSION['lang']['dibuat']."</td><td style='vertical-align:top;' hidden><input type=text id=dibuat class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)'  /></td></tr>";

echo"<tr><td>".$_SESSION['lang']['tempatpenyerahan']."</td><td style='vertical-align:top;'><select id=tempatpenyerahan style=width:155px>".$optSerah."</select>
<img id=tempatpenyerahan onclick=z.elSearch('tempatpenyerahan',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
</td>";
echo"<td style='vertical-align:top;' hidden>".$_SESSION['lang']['jabatan']."</td><td  hidden style='vertical-align:top;'><input type=text id=jabatan class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)'/>";
echo"<td style='vertical-align:top;'>QTY</td><td style='vertical-align:top;'><input type=text id=qty class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('qty',0)\" onkeypress='return tanpa_kutip(event)'/></tr>";

echo"<tr><td hidden>".$_SESSION['lang']['pphditanggung']."</td><td hidden><select id=statpph style=width:155px >".$optpph."</select></td>";
echo"<td hidden>Harga/Kg Transportir</td><td hidden><input type=text id=harga class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('harga',0)\" onkeypress='return tanpa_kutip(event)' value=0 maxlength='15' />
</td></tr>";

echo"<tr hidden >
	<td>".$_SESSION['lang']['persen']."</td>
	<td><input type=text id=subsidi class=myinputtextnumber style=width:150px; onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('harga',0)\" onkeypress='return tanpa_kutip(event)' value=2 maxlength='15' /><!--<select id=subsidi2 style=width:155px style='display:none'>".$optpph."</select>--></td>
	<td>SPK Pemuatan</td><td><select id=spkpmuat style=width:155px >".@$optspkpemuat."</select>
	<img id=spkpmuat onclick=z.elSearch('spkpmuat',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td></td>
</tr>";

echo"<tr><td hidden>Sistem Penyerahan</td><td hidden><select id=penyerahan style=width:155px >".$optpenye."</select></td>
			<td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['timbangan']."</td>
	<td><select id=statTimbangan style=width:155px >".$optTim."</select></td>
</tr>";

echo"<tr><td>TTD 1</td><td><select id=ttd1 style=width:155px >".$optttd."</select></td>
<td>TTD 2</td><td><select id=ttd2 style=width:155px >".$optttd."</select></td>
</tr>";

echo"<tr><td>".$_SESSION['lang']['namakapal']."</td>
<td><select id=nmkpl style=\"width:150px;\">'".$optkapal."'</select>
<img id=nmkpl onclick=z.elSearch('nmkpl',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
<td>".$_SESSION['lang']['namaponton']."</td>
<td><select id=namaponton style=\"width:150px;\">'".$optponton."'</select>
<img id=namaponton onclick=z.elSearch('namaponton',event) class=zImgBtn src=images/onebit_02.png style=position:relative;top:3px;left:3px;></td>
";
echo "</td>
<td hidden >Pelayaran / Agen Kapal</td><td hidden ><select id=agen style=width:155px >".$optttd."</select></td>
</tr>";

echo"<tr>
	<td>Pelabuhan Bongkar Kapal</td>
	<td><select id=plbbongkar style=width:155px >".$optfranco."</select></td>
	<td>Pelabuhan Muat Kapal</td>
		<td><select id=plbmuat style=width:155px >".$optfranco."</select></td>

</tr>";

echo"<tr><td>".$_SESSION['lang']['tanggal']." Tiba PMKS</td><td><input type=text class=myinputtext id=tgltiba1 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px;  maxlength=10 /> s/d <input type=text class=myinputtext id=tgltiba2 readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px;  maxlength=10 />";
echo "</td>
		<td>Kondisi Air</td><td><input type=text id=kondisi class=myinputtext style=width:150px; onkeypress='return tanpa_kutip(event)' /></td>
</tr>";

echo"<tr>
		<td>Toleransi</td>
		<td><input type=text id=toleransi onkeypress=empty1() class=myinputtextnumber style=\"width:150px;\" value=0> %</td>
		<td hidden>Toleransi</td>
		<td hidden><input type=text id=kgtoleransi onkeypress=empty2() class=myinputtextnumber style=\"width:150px;\" value=0> kg</td>
</tr>";

echo"<tr hidden>
		<td>".$_SESSION['lang']['tanggal']." Berangkat</td>
		<td colspan=4>
			<input type=text class=myinputtext id=tglberangkat readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px;  maxlength=10 />
		(untuk sip non sales)</td>

</tr>";



echo"<tr><td style='vertical-align:top;'>Lain-lain</td><td colspan=3><textarea id='lain' style=width:410px; onkeypress='return tanpa_kutip(event);'></textarea></td>

<td style='vertical-align:top;display:none;'>Harga/Kg Transportir</td>
<td style='vertical-align:top;display:none;'>
<input type=text id=harga class=myinputtextnumber style='width:150px;display:none;' onkeypress=\"return angka_doang(event)\" onblur=\"z.numberFormat('harga',0)\" onkeypress='return tanpa_kutip(event)' value=0 maxlength='15' />

";
echo"<tr><td colspan='3'></td></tr>";


echo"<tr><td></td><td colspan=3>
		 <input type=hidden id=proses value='insert'  />
		 <input type=hidden id=kdOrg value=''  />
		 <button class=mybutton onclick=saveData('pmn_slave_suratperintahpengiriman','".$arr."')>".$_SESSION['lang']['save']."</button>&nbsp;
         <button class=mybutton onclick=clearData()>".$_SESSION['lang']['cancel']."</button>
         </td></tr>";


echo"</table></fieldset>"; 
if($_SESSION['language']=='EN'){
    echo"<fieldset style=float:left;><legend>Note</legend>
		* Field Customner, Delivert Time and Franco automatically filled when Sales Contract has been created<br>
		* DO Number is Auto<br>
		* Departure Date can be filled for non sales
		</fieldset>
	</div>";
}else{
    echo"<fieldset style=float:left;><legend>Note</legend>
		* Field Pembeli, Waktu Penyerahan dan Kepada otomatis muncul jika No Kontrak sudah diinput<br>
		* Ketika pembuatan Form Baru No DO (Delivery Order) otomatis terbentuk jika melakukan aksi simpan<br>
		* Tanggal berangkan terisi untuk non sales
		</fieldset>
	</div>";
}

CLOSE_BOX();
echo close_body(); ?>

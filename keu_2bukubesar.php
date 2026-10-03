<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_2bukubesar.js?v=<?php echo time(); ?>'></script>

<?
//<script language=javascript1.2 src="js/keu_laporan.js"></script>
include('master_mainMenu.php');
echo "<script language=javascript src=js/zSelect2.js?ver=1></script>";
OPEN_BOX('','<span class=judul>'.getMenu('keu_2bukubesar').'</span><br>');

//get existing period
// $str=$owlPDO->query("select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc");
// $str->setFetchMode(PDO::FETCH_OBJ);
// $optper="";
// while($bar=$str->fetch())
// {
    // $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
// }

$str="SELECT distinct periode as periode FROM ".$dbname.".setup_periodeakuntansi order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	@$optper.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}

    #default ke Rekap - Rincian (pecah per unit) jauh lebih berat query-nya kalau Unit="Seluruhnya", jadi
    #Rekap yang lebih ringan dijadikan pilihan awal, Rincian tetap bisa dipilih manual kalau memang perlu
    $opttampilkan="<option value='0' selected>".$_SESSION['lang']['rekap']."</option>";
    $opttampilkan.="<option value='1'>".$_SESSION['lang']['detail']."</option>";

    $optrev="<option value='0'>0</option>";
    $optrev.="<option value='1'>1</option>";
    $optrev.="<option value='2'>2</option>";
    $optrev.="<option value='3'>3</option>";
    $optrev.="<option value='4'>4</option>";
    $optrev.="<option value='5'>5</option>";
//}

if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
    //=================ambil PT;
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $optpt="";
    $optpt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=$str->fetch())
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
        or tipe='HOLDING')  and induk!=''");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=$str->fetch())
    {
//        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else
if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
    //=================ambil PT;
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $optpt="";
    $optpt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=$str->fetch())
    {
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
    }

    //=================ambil gudang;
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL')  and induk!=''");
    $str->setFetchMode(PDO::FETCH_OBJ);
    $optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar=$str->fetch())
    {
//        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
    }
}
else
{
    $optpt="";
    $optpt.="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";
}


$str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun
where level = '5' and noakun!='".$CLM."' order by noakun");
$str->setFetchMode(PDO::FETCH_OBJ);
$optakun="<option value=''>".$_SESSION['lang']['all']."</option>";
while($bar=$str->fetch()){
$optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}
$arrTampilan=array("0"=>"Tampilkan Nol","1"=>"Tidak Tampilkan Nol");
foreach ($arrTampilan as $key => $value) {
    @$optTampilan.="<option value='".$key."'>".$value."</option>";
}
echo"<fieldset style=float:left>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=3>
	<colgroup><col style='width:105px'><col style='width:10px'><col style='width:220px'><col style='width:24px'><col style='width:105px'><col style='width:10px'><col style='width:220px'></colgroup>
	<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td><select class='select2' id=pt style='width:220px;' onchange=getReg()>".$optpt."</select></td>
		<td></td>
		<td>".$_SESSION['lang']['regional']."</td>
		<td>:</td>
		<td><select class='select2' id=regional style='width:220px;' onchange=getUnit()>".@$optReg."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['unit']."</td>
		<td>:</td>
		<td><select class='select2' id=gudang style='width:220px'>".$optgudang."</select></td>
		<td></td>
		<td>".$_SESSION['lang']['revisi']."</td>
		<td>:</td>
		<td><select class='select2' id=revisi style='width:220px' onchange=hideById('printPanel')>".$optrev."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select class='select2' id=periode style='width:220px' onchange=hideById('printPanel')>".$optper."</select></td>
		<td></td>
		<td>".$_SESSION['lang']['sd']."</td>
		<td>:</td>
		<td><select class='select2' id=periode1 style='width:220px' onchange=hideById('printPanel')>".$optper."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['noakun']."</td>
		<td>:</td>
		<td><select class='select2' id=akundari style='width:220px;' onchange=hideById('printPanel')>".$optakun."</select></td>
		<td></td>
		<td>".$_SESSION['lang']['noakunsampai']."</td>
		<td>:</td>
		<td><select class='select2' id=akunsampai style='width:220px;' onchange=hideById('printPanel')>".$optakun."</select></td>
	</tr><tr>
		<td>".$_SESSION['lang']['statussaldo']."</td>
		<td>:</td>
		<td><select class='select2' id=tampilanId style='width:220px' onchange=hideById('printPanel')>".$optTampilan."</select></td>
		<td></td>
		<td>".$_SESSION['lang']['tampilkan']."</td>
		<td>:</td>
		<td><select class='select2' id=tampilkan style='width:220px' onchange=hideById('printPanel')>".$opttampilkan."</select></td>
	</tr><tr>
		<td colspan=3 style='padding-top:6px'>
			<button class=mybutton onclick=getLaporanBukuBesar('html')>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=getLaporanBukuBesar('excel')>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=getLaporanBukuBesarPdf()>PDF</button>
			<button class=mybutton onclick=bbBatal()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
	</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX('','');
// echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
	// <div id='both_report'>
	// <div id='head_tableboth' style='height:20px;'>
		 // <span id=printPanel style='display:none;'>
			// <img hidden onclick=fisikKeExcel(event,'keu_laporanBukuBesar_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>
			// <img hidden onclick=fisikKePDF(event,'keu_laporanBukuBesar_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
			// <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='body_tableboth' table='sortable' style='float:right;margin-right:10px'>
				// <img title='Full Screen' class=resicon src='images/full-screen.png'>
			// </a>
			// <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='body_tableboth' shown='0' style='float:right;margin-right:10px;' >
				// <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
			// </a>
			// <!--<a class='clearfixheadbtn' table='sortable' style='float:right;margin-right:10px'>
				// <img title='fix-header' class=resicon src=images/remove-fix-heder.gif>
			// </a>-->
		// </span>
// <!--
    // <div style='width:100%;'>
    // <table class=sortable cellspacing=1 border=0 width='100%'>
    // <thead>
		// <tr>
			// <th align=center style='width:50px;'>".$_SESSION['lang']['nomor']."</th>
			// <th align=center style='width:50px;'>".$_SESSION['lang']['unit']."</th>
			// <th align=center style='width:80px;'>".$_SESSION['lang']['noakun']."</th>
			// <th align=center style='width:450px;'>".$_SESSION['lang']['namaakun']."</th>
			// <th align=center style='width:130px;'>".$_SESSION['lang']['saldoawal']."</th>
			// <th align=center style='width:130px;'>".$_SESSION['lang']['debet']."</th>
			// <th align=center style='width:130px;'>".$_SESSION['lang']['kredit']."</th>
			// <th align=center style='width:130px;'>".$_SESSION['lang']['saldoakhir']."</th>
		// </tr>
    // </thead>
    // <tbody>
    // </tbody>
    // <tfoot>
    // </tfoot>
    // </table>
    // </div>
// -->
	// </div>
	// <div id='body_tableboth' style='width:100%;height:365px;overflow:auto;'>
    // </div>
	// </div>
	// </fieldset>";
echo"<div id=printPanel style='display:none;'>
		<img hidden onclick=fisikKeExcel(event,'keu_laporanBukuBesar_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'>
		<img hidden onclick=fisikKePDF(event,'keu_laporanBukuBesar_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	</div>
	<div id=container class='table-scroll' style='min-height:340px;'></div>";
CLOSE_BOX();
close_body();
?>

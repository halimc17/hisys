<?PHP
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_verifikasirph').'</span>');
?>

<link rel="stylesheet" type="text/css" href="style/zTable.css">
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script type="text/javascript" src="js/log_verifikasirph.js?v=<?php echo time(); ?>" /></script>
<script type="text/javascript" src="js/log_link.js?v=<?php echo time(); ?>" /></script>
<script language=javascript src='js/vhc_detailkmhm.js?v=<?php echo time(); ?>'></script>

<?php
echo"<div id='action_list'>
	<table>
		<tr valign=moiddle>
			<td align=center style='width:100px;cursor:pointer;' onclick=displaylist()>
				<img class=delliconBig src=images/skyblue/list.png title='".$_SESSION['lang']['list']."'><br>".$_SESSION['lang']['list']."
			</td>
			<td>
				<fieldset><legend>".$_SESSION['lang']['find']."</legend>
				".$_SESSION['lang']['notransaksi']." : <input type=text id=txtsearch size=25 maxlength=30 class=myinputtext>
				".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tgl_cari onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/>
				<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
				</fieldset>
			</td>
			<td>
				<fieldset>
				<legend>List Job</legend><div id=notifikasiKerja></div>
				</fieldset>
			</td>
		</tr>
	</table>
</div>";

CLOSE_BOX();

OPEN_BOX();
echo"<div id='list_permintaan' name='list_permintaan'>
	
	<div id='dlm_list_permintaan' name='dlm_list_permintaan' class='table-scroll' style=height:70vh>
		<table class='sortable' cellspacing='1' border='0' cellpadding=5 style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<th>No.</th>
				<th>".$_SESSION['lang']['notransaksi']." DPH</th>
				<th>".$_SESSION['lang']['notransaksi']." RPH</th>
				<th>".$_SESSION['lang']['tanggal']."</th>
				<th>".$_SESSION['lang']['list']." ".$_SESSION['lang']['nopp']."</th>
				<th>".$_SESSION['lang']['chat']."</th>
				<th>Purchaser</th>
				<th>Verificator</th>
				<th>Status Persetujuan</th>
				<th>Nama Supplier Pemenang</th>
				<th>Tender</th>
				<th style='display:none' align='center'>Action</th>
			</tr>
			</thead>
			<tbody id='contain'>
			<script>loaddata(0);</script>
			</tbody>
		</table>
	</div>
	</fieldset>
</div>";

$arr="";

$optjenis="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjenis.="<option value='slow'>Slow</option>";
$optjenis.="<option value='fast'>Fast</option>";
$optjenis.="<option value='non'>Non</option>";
$optKelompokBrg=$optBrgCari=$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";

$sKelompok="select distinct kode,kelompok from ".$dbname.".log_5klbarang order by kode asc";
$qKelompok=$owlPDO->query($sKelompok) or die(print " Gagal: ".PDOException::getMessage());
$qKelompok->setFetchMode(PDO::FETCH_ASSOC);
while($rKelompok=$qKelompok->fetch())
{
	$optKelompokBrg.="<option value='".$rKelompok['kode']."'>".$rKelompok['kode']." - ".$rKelompok['kelompok']."</option>";
}
	
echo"<div id=formPP style=display:none>
	<input hidden  id=noUrut value='1' />
	<input type=hidden id=notransaksi value='' />";
    
	//tampilkan form mengambil get PP
    echo"<div id=listBrgPP  style=display:none>
		<fieldset><legend>".$_SESSION['lang']['find']."</legend>
		<table>
			<tr>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>:</td>
				<td>
					<input type=text id=schnopp style=width:150px size=25 maxlength=30 class=myinputtext>
				</td>

				<td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td>
					<select style=width:100px  id='schjenis' name='jenis'>".$optjenis."</select>
				</td>
				
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>
					<select style=width:100px  id='schunit' name='jenis'>".$optunit."</select>
				</td>
			
				<td><input type=text hidden id=schpt style=width:150px size=25 maxlength=30 class=myinputtext></td>		
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kelompokbarang']."</td>
				<td>:</td>
				<td> 
					<select id=schklbrg style=width:155px onchange=getBarangCari()>".$optKelompokBrg."</select>
				</td>
				
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>:</td>
				<td>
					<select id=schkdbrg style=width:100px>".$optBrgCari."</select>&nbsp;<img hidden src=\"images/search.png\" class=\"resicon\" title='".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."' onclick=\"searchBrgCari('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."','<fieldset style=min-width:93%>".$_SESSION['lang']['find']."&nbsp;<input type=text class=myinputtext id=nmBrg><button class=mybutton onclick=findBrg2()>".$_SESSION['lang']['find']."</button></fieldset><div id=containerBarang style=overflow=auto;max-height=300;max-width=400></div>',event);\">
				</td>
				
				<td></td>
				<td></td>
				<td align=center><button class=mybutton onclick=schgetDtPP()>".$_SESSION['lang']['find']."</button></td>
			</tr>
		</table>
		</fieldset>
		
		<fieldset><legend>".$_SESSION['lang']['daftarbarang']."</legend>
			<div>
			<table border=0 cellspacing=1 class=sortable style='width:100%'>
				<thead>
				<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>".$_SESSION['lang']['nopp']."</th>
					<th align=center>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>No. RPH</th>
					<th align=center><input type=checkbox onclick=clikcAll() id=dtSemua /></th>
				</tr>
				</thead>
                <tbody id=dataBarang>

                </tbody>
             </table>
			</div>
		</fieldset>
    </div>";
	
	//tampilkan form persyaratan permintaan
    $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $optStock=$optTermPay;
    $optKrm=$optTermPay;
    $arrOptTerm=array("1"=>"Tunai","2"=>"Kerdit 2 Minggu","3"=>"Kredit 1 Bulan","4"=>"Termin","5"=>"DP");
    foreach($arrOptTerm as $brsOptTerm =>$listTerm)
    {
		$optTermPay.="<option value='".$brsOptTerm."'>".$listTerm."</option>";
	}
	
	$sKrm="select id_franco,franco_name from ".$dbname.".setup_franco where status=0 order by franco_name asc";
    $qKrm=$owlPDO->query($sKrm) or die(print " Gagal: ".PDOException::getMessage());
	$qKrm->setFetchMode(PDO::FETCH_ASSOC);
	while($rKrm=$qKrm->fetch())
    {
		$optKrm.="<option value=".$rKrm['id_franco'].">".$rKrm['franco_name']."</option>";
	}
	
	$arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");   
    foreach($arrStock as $brsStock => $listStock)
    {
		$optStock.="<option value='".$brsStock."'>".$listStock."</option>";
	}
	
	$optMt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    $sMt="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
    $qMt=$owlPDO->query($sMt) or die(print " Gagal: ".PDOException::getMessage());
	$qMt->setFetchMode(PDO::FETCH_ASSOC);
	while($rMt=$qMt->fetch())
    {
		$optMt.="<option value=".$rMt['kode'].">".$rMt['kodeiso']."</option>";
	}
	echo"<br />
	
	<div id=listSupplier style=display:none>
	<fieldset style=width:450px;><legend>".$_SESSION['lang']['permintaan']."</legend>
	<table cellspacing=\"1\" border=\"0\">
		<tr>
			<td>".$_SESSION['lang']['matauang']."</td>
            <td>:</td>
            <td><select id=\"mtUang\" name=\"mtUang\" style=\"width:150px;\" >".$optMt."</select></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['kurs']."</td>
            <td>:</td>
            <td><input type=\"text\" class=\"myinputtext\" id=\"Kurs\" name=\"Kurs\" style=\"width:150px;\" onkeypress=\"return angka_doang(event)\"  /></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['syaratPem']."</td>
            <td>:</td>
            <td><select id='term_pay' name='term_pay' style=\"width:200px\">".$optTermPay."</select></td>
            <td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['almt_kirim']."</td>
            <td>:</td>
            <td><select id='tmpt_krm' name='tmpt_krm' style=\"width:200px;\">".$optKrm."</select></td>
            <td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>".substr($_SESSION['lang']['stockdetail'],0,5)."</td>
            <td>:</td>
            <td><select id='stockId' name='stockId' style=\"width:200px\">".$optStock."</select></td>
            <td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>". $_SESSION['lang']['keterangan']."</td>
            <td>:</td>
            <td><textarea id='ketUraian' name='ketUraian' onkeypress='return tanpa_kutip(event);'></textarea></td>
            <td>&nbsp;</td>
		</tr>
		
		<tr>
			<td colspan=3 align=center><button class=mybutton onclick='lanjutAdd2()'  >".$_SESSION['lang']['lanjut']."</button></td>
		</tr>
	</table>
	</fieldset>
	</div>";
    //end tampilkan form persyaratan
	
	$sql="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='SUPPLIER' and status=1) and status='1' order by namasupplier asc";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_ASSOC);
    $optSupplier='';
	$no = 0;
	$tempsup = "";
    while($res=$query->fetch())
    {
		if($no==0)
		{
			$tempsup = $res['supplierid'];
		}
		
		$optSupplier.="<option value='".$res['supplierid']."'>".$res['namasupplier']."</option>";
		$no++;
	}
	
	$optalamat = '';
	if($tempsup!='')
	{
		$str="select * from ".$dbname.".log_5supalamat where supplierid = '".$tempsup."' and status='1' order by alamat desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optalamat.="<option value='".$bar['id_alamat']."'>".$bar['alamat']." ".$bar['kota']."</option>";
		}
	}
	
	//form supplier
	echo"<div id=supplierForm style=display:none></div>
	
	<div id=listrph style=display:none>	
		<fieldset style=width:550px;><legend>".$_SESSION['lang']['data']."</legend>
		<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				<td>No.</td>
				<td>".$_SESSION['lang']['nopermintaan']."</td>
				<td>".$_SESSION['lang']['namasupplier']."</td>
				<td>".$_SESSION['lang']['alamat']."</td>
				<td>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody id=listHasilSave>
			</tbody>
		</table>
		</fieldset>
	</div>
</div>";

echo"<div id=formPP2  style=display:none>";

$optListNopp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sLnopp="select distinct nomor from ".$dbname.".log_perintaanhargaht where purchaser='".$_SESSION['standard']['userid']."' order by nomor desc";
$qLnopp=$owlPDO->query($sLnopp) or die(print " Gagal: ".PDOException::getMessage());
$qLnopp->setFetchMode(PDO::FETCH_ASSOC);
while($rLnopp=$qLnopp->fetch())
{
	$optListNopp.="<option value='".$rLnopp['nomor']."'>".$rLnopp['nomor']."</option>";
}

$arr="##nopp2##formPil";
echo"<br />
<fieldset style=width:350px;><legend>Form PP</legend>
<input type=hidden id='formPil' name='formPil' value='1' />
<table cellspacing=\"1\" border=\"0\" >
	<tr>
		<td><label>".$_SESSION['lang']['nopp']."</label></td>
		<td>
			<select id=\"nopp2\" name=\"nopp2\"  style=\"width:200px;\" >".$optListNopp."</select><img  src='images/search.png' class=dellicon title='".$_SESSION['lang']['find']." ".$_SESSION['lang']['nopp']."' onclick=\"searchNopp('".$_SESSION['lang']['find']." ".$_SESSION['lang']['nopp']."','<fieldset><legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['nopp']."</legend>".$_SESSION['lang']['find']."&nbsp;<input type=text class=myinputtext id=kdNopp><button class=mybutton onclick=findNopp2()>".$_SESSION['lang']['find']."</button></fieldset><div id=containerNopp style=overflow=auto;height=380;width=485></div>',event);\">
		</td>
	</tr>
    <tr height=\"20\">
		<td colspan=\"2\">&nbsp;</td>
	</tr>
    <tr>
		<td colspan=\"2\">
			<button onclick=\"zPreview('log_slave_2perbandingan_harga','". $arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
			<button onclick=\"zExcel(event,'log_slave_2perbandingan_harga.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>    
		</td>
	</tr>
</table>
</fieldset>";

echo"<div id=formEditData  style=display:none>
<fieldset style='clear:both'><legend><b>Edit Area</b></legend>
	<div id='printContainer'  style='overflow:auto;'></div>
</fieldset>
</div>
</div>";


echo"<div id='formEditData2'  style=display:none>
<fieldset style='clear:both'><legend><b>Edit Area</b></legend>
	<div id='printContainer2'  style='overflow:none;'></div>
</fieldset>
</div>";

CLOSE_BOX();

echo close_body(); 
?>
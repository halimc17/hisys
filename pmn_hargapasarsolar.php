<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/pmn_hargapasarsolar.js'></script>
<?
$arr="##unit##tglHarga##kdBarang##satuan##supplier##idMatauang##hrgPasar##status##proses";
include('master_mainMenu.php');
OPEN_BOX("","<span class=judul>".getMenu('pmn_hargapasarsolar')."</span>");
$optBrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optSupplier=$optunit=$optKodeSat=$optKode=$optPasar=$optBrg;

$sunit=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where char_length(kodeorganisasi)=4 order by namaorganisasi asc");
$sunit->setFetchMode(PDO::FETCH_ASSOC);
while($runit=$sunit->fetch()){
    $optunit.="<option value='".$runit['kodeorganisasi']."'>".$runit['namaorganisasi']."</option>";
}

$sBrng="select distinct kodebarang,namabarang from ".$dbname.".log_5masterbarang where left(kodebarang,5)='35101' order by namabarang asc";
$qBrng=$owlPDO->query($sBrng) or die(print " Gagal: ".PDOException::getMessage());
$qBrng->setFetchMode(PDO::FETCH_ASSOC);
while($rBarang=$qBrng->fetch())
{
    $optBrg.="<option value='".$rBarang['kodebarang']."'>".$rBarang['kodebarang']." - ".$rBarang['namabarang']."</option>";
}

$sData="select distinct kode  from ".$dbname.".setup_matauang order by kode asc";
$optKode.="<option value='IDR'>IDR</option>";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch())
{
    $optKode.="<option value='".$rData['kode']."'>".$rData['kode']."</option>";
}

$arrSatuan=array("LTR");
foreach($arrSatuan as $der)
{
    $optKodeSat.="<option value='".$der."'>".$der."</option>";
}

#ambil list supplier
$str="select distinct a.supplierid,namasupplier from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5supplier b 
	 on a.supplierid=b.supplierid where (tipe='BAHANBAKAR' or tipe='SUPPLIER') and a.status=1 and b.status=1 order by namasupplier";
$res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar=$res->fetch()) {
    $optSupplier.="<option value='".$bar['supplierid']."'>".$bar['namasupplier']." (".$bar['supplierid'].")</option>";
}

$optStatus="<option value='Best Bidder'>Best Bidder</option>";
$optStatus.="<option value='Traded'>Traded</option>";

echo"<fieldset style=width:650px>
     <legend>".$_SESSION['lang']['hargapasar']."</legend>
	 <table>
	 <tr>
	   <td>".$_SESSION['lang']['unit']."</td>
	   <td><select id=unit style=\"width:150px;\">".$optunit."</select>
	   	   <img id=unit onclick=z.elSearch('unit',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
	   </td>
	   <td>".$_SESSION['lang']['tanggal']."</td>
	   <td><input type=text class=myinputtext id=tglHarga onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:70px;\" /></td>
	 </tr>
	 <tr>
	   <td>".$_SESSION['lang']['namabarang']."</td>
	   <td><select id=kdBarang style=\"width:150px;\">".$optBrg."</select></td>
	   <td>".$_SESSION['lang']['satuan']."</td>
	   <td><select id=satuan style=\"width:75px;\">".$optKodeSat."</select></td>
	 </tr>
	 <tr>
       <td>".$_SESSION['lang']['supplier']."</td>
       <td><select id=supplier style=width:150px; onchange=getrek(this.value,0) >".$optSupplier."</select><img id=supplier onclick=z.elSearch('supplier',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'></td>
	   <td>".$_SESSION['lang']['matauang']."</td>
	   <td ><select id=idMatauang style=\"width:75px;\">".$optKode."</select></td>
	 </tr>
	  <tr>
	   <td>".$_SESSION['lang']['status']." ".$_SESSION['lang']['harga']."</td>
	   <td><select id=status style=\"width:150px;\">".$optStatus."</select></td>
	   <td>".$_SESSION['lang']['harga']."</td>
	   <td><input type=text class=myinputtextnumber id=hrgPasar onkeypress=\"return angka_doang(event);\" style=\"width:70px;\"  /> </td>
	 </tr>	
	 <tr>
	   <td><td colspan=4>
	   <input type=hidden value=insert id=proses>
	   <button class=mybutton onclick=saveFranco('pmn_slave_hargapasarsolar','".$arr."')>".$_SESSION['lang']['save']."</button>
	   <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
	   </td>
	 </tr>
     </table>
	 </fieldset><input type='hidden' id=idFranco name=idFranco />";
CLOSE_BOX();
OPEN_BOX();

echo"<fieldset ><legend>".$_SESSION['lang']['list']."</legend>";
echo"<table cellpadding=1 cellspacing=1 border=0><tr><td>".$_SESSION['lang']['tanggal']." : <input type=text class=myinputtext id=tglCri onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10  />";
echo"&nbsp;".$_SESSION['lang']['unit']." : <select id=unitCari style=\"width:150px;\">".$optunit."</select>
	<img id=unitCari onclick=z.elSearch('unitCari',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>";
echo"&nbsp;".$_SESSION['lang']['namabarang']." : <select id=kdBrgCari style=\"width:150px;\">".$optBrg."</select>";
echo"&nbsp;".$_SESSION['lang']['supplier']." : <select id=supplierCari style=\"width:150px;\">".$optSupplier."</select>
	<img id=supplierCari onclick=z.elSearch('supplierCari',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'>
<button class=mybutton onclick=cariTransaksi()>".$_SESSION['lang']['find']."</button></td></tr></table>";
echo"
    <table class=sortable cellspacing=1 border=0 style=width:100%>
     <thead>
	  <tr class=rowheader>
	   <td align=center>No</td>
	   <td align=center>".$_SESSION['lang']['unit']."</td>
	   <td align=center>".$_SESSION['lang']['tanggal']."</td>
	   <td align=center>".$_SESSION['lang']['namabarang']."</td>
	   <td align=center>".$_SESSION['lang']['satuan']."</td>
	   <td align=center>".$_SESSION['lang']['pasar']."</td>
	   <td align=center>".$_SESSION['lang']['matauang']."</td>
           <td align=center>".$_SESSION['lang']['harga']."</td>
           <td align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['harga']."</td>  
	   <td align=center>".$_SESSION['lang']['action']."</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";

echo"</tbody>
     <tfoot>
	 </tfoot>
	 </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>
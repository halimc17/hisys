<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/keu_laporan.js"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanbukubesar'].' '.$_SESSION['lang']['hutang']).'</span><br>');
//get existing period
$str="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc";	  
$optper="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}

//ambil PT;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi";
}else{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT' and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'
        order by namaorganisasi";
}
$optpt="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$optgudang="";

//ambil gudang;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='TRAKSI'
        or tipe='HOLDING')  and induk!=''
        ";
    $optgudang.="<option value=''>".$_SESSION['lang']['all']."</option>";
}
else if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where induk='".$_SESSION['empl']['kodeorganisasi']."' and length(kodeorganisasi)=4 and kodeorganisasi not like '%HO'";
}
else{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'  and induk!=''";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

//ambil akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun where level = '5' and noakun = '2111101' order by noakun";
$optakun='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
}

$qwe="01-".date("m-Y");
?>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><select id=pt style='width:200px;'  onchange=ambilAnakBB(this.options[this.selectedIndex].value)><?php echo $optpt; ?></select></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select id=gudang style='width:200px;' onchange=hideById('printPanel')><?php echo $optgudang; ?></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tgl1" name="tgl1" onchange="cekTanggal1(this.value);" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:82px;" value="<?php echo $qwe; ?>" />

<?php echo $_SESSION['lang']['sd']?> <input type="text" class="myinputtext" id="tgl2" name="tgl2" onchange="cekTanggal2(this.value);" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:82px;" /></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['noakun']?></label></td><td>:</td><td><select id=akundari style='width:200px;'><?php echo $optakun; ?></select></td></tr>
<!--<tr><td><label><?php echo $_SESSION['lang']['noakunsampai']?></label></td><td><select id=akunsampai style='width:200px;' onchange=hideById('printPanel')><option value=""></option></select></td></tr>-->

<!--<tr height="20"><td colspan="2">&nbsp;</td></tr>-->
<tr><td><td><td> <button class=mybutton onclick=getLaporanBukuBesarHutangv1()><?php echo $_SESSION['lang']['proses'] ?></button></td></tr>

<!--<tr><td colspan="2"><button onclick="zPreview('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>-->

</table>
</fieldset>
<? 
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>Result :</legend>
	 <span id=printPanel style='display:none;'>
     <img onclick=jurnalv1KeExcel(event,'keu_laporanBukuBesarHutangv1_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <!--<img onclick=jurnalv1KePDF(event,'keu_laporanBukuBesarv1_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>-->
	 </span>    
         <div style='width:1180px;display:fixed;'>
       <table class=sortable cellspacing=1 border=0 width=1160px>
	     <thead>
		    <tr>
			  <td align=center style='width:40px;'>".$_SESSION['lang']['nomor']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['nojurnal']."</td>
			  <td align=center style='width:80px;'>".$_SESSION['lang']['tanggal']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['noakun']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['kodesupplier']."</td>
			  <td align=center style='width:250px;'>".$_SESSION['lang']['keterangan']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['debet']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['kredit']."</td>
			  <td align=center style='width:100px;'>".$_SESSION['lang']['saldo']."</td>
			</tr>  
		 </thead>
		 <tbody>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div>
     <div style='width:auto;height:359px;overflow:auto;'>
           <table class=sortable cellspacing=1 border=0 width=1160px>
                 <thead>
                      <tr>
                     </tr>  
                     </thead>
                     <tbody id=container>
                     </tbody>
                     <tfoot>
                     </tfoot>		 
               </table>
         </div></fieldset>";
CLOSE_BOX();
close_body();
?>
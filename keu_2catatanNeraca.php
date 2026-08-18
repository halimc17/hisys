<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/keu_laporan.js"></script>
<?
include('master_mainMenu.php');


//get existing period
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt
      order by periode desc";
$optper="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}
//=================ambil PT;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
      where tipe='PT'
          order by namaorganisasi";
$optpt="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}

//=================ambil gudang;  
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL'
                or tipe='HOLDING')  and induk!=''
                ";
$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optgudang.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

}
$str="select noakun,namaakun from ".$dbname.".keu_5akun
                where level = '5'
                order by noakun
                ";
$optakun="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";

}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['catatanneraca']).'</span><br>');
?>
<fieldset style="float:left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id=periode style='width:200px;' onchange=hideById('printPanel')><?php echo $optper; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><select id=pt style='width:200px;'  onchange=ambilAnak(this.options[this.selectedIndex].value)><?php echo $optpt; ?></select></tr>

<tr><td><label><?php echo $_SESSION['lang']['noakundari']?></label></td><td>:</td><td><select id=akundari style='width:200px;' onchange=ambilAkun2(this.options[this.selectedIndex].value)><?php echo $optakun; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['noakunsampai']?></label></td><td>:</td><td><select id=akunsampai style='width:200px;' onchange=hideById('printPanel')><option value=""></option></select></td></tr>

<!--<tr height="20"><td colspan="2">&nbsp;</td></tr>-->
<tr><td><td><td> <button class=mybutton onclick=getLaporanCatatanNeraca()><?php echo $_SESSION['lang']['proses'] ?></button></td></tr>

<!--<tr><td colspan="2"><button onclick="zPreview('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2rekapabsen','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rekapabsen.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>-->

</table>
</fieldset>
<? 
CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset>
	 <legend>".$_SESSION['lang']['list']."</legend>
	 <span id=printPanel style='display:none;'>
     <img onclick=catatanNeracaKeExcel(event,'keu_laporancatatanNeraca_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
         <img onclick=catatanNeracaKePDF(event,'keu_laporancatatanNeraca_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
         </span>    
         <div style='width:1180;display:fixed;'>
       <table class=sortable cellspacing=1 border=0 width=1160px>
             <thead>
                    <tr>
                          <td align=center style='width:50px'>".$_SESSION['lang']['nomor']."</td>
                          <td align=center style='width:80px'>".$_SESSION['lang']['noakun']."</td>
                          <td align=center style='width:330px'>".$_SESSION['lang']['namaakun']."</td>
                          <td align=center style='width:100px'>".$_SESSION['lang']['kodeorg']."</td>
                          <td align=center style='width:150px'>".$_SESSION['lang']['saldoawal']."</td>
                          <td align=center style='width:150px'>".$_SESSION['lang']['debet']."</td>
                          <td align=center style='width:150px'>".$_SESSION['lang']['kredit']."</td>
                          <td align=center style='width:150px'>".$_SESSION['lang']['saldoakhir']."</td>
                        </tr>  
                 </thead>
                 <tbody>
                 </tbody>
                 <tfoot>
                 </tfoot>		 
           </table>
     </div>
<div style='width:1180px;height:359px;overflow:auto;'>
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
<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();

?>
<script language=javascript src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('keu_2bukubesar_v1').'</span><br>');

//get existing period
$str=$owlPDO->query("select distinct periode as periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc");
$str->setFetchMode(PDO::FETCH_OBJ);	  
$optper="";
while($bar=$str->fetch())
{
        $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}
 $optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optReg=$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";

//=================ambil PT;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
              where tipe='PT'
                  order by namaorganisasi");
    $str->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$str->fetch())
        {
                $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";

        }
}  
 else 
{
    $optpt="<option value='".$_SESSION['empl']['kodeorganisasi']."'>". $_SESSION['empl']['kodeorganisasi']."</option>";
    $optgudang="<option value='".$_SESSION['empl']['lokasitugas']."'>".$_SESSION['empl']['lokasitugas']."</option>";  
    $optReg="<option value='".$_SESSION['empl']['regional']."'>". $_SESSION['empl']['regional']."</option>";  
 }
 
 
 $str="select noakundebet from ".$dbname.".keu_5parameterjurnal
where kodeaplikasi = 'CLM'
	";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$clm=$bar->noakundebet;
}
 
        $str=$owlPDO->query("select noakun,namaakun from ".$dbname.".keu_5akun
                        where level = '5' and noakun!='".$clm."'
                        order by noakun");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $optakun="<option value=''></option>";
        while($bar=$str->fetch())
        {
                $optakun.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";

        }
$qwe="01-".date("m-Y");
?>
<fieldset style='float:left'>
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td>
	<td><select id=pt style='width:200px;'  onchange=getReg()><?php echo $optpt; ?></select>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['regional']?></label></td><td>:</td>
	<td><select id=regional style='width:200px;' onchange=getUnit()><?php echo $optReg; ?></select>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td>
	<td><select id=gudang style='width:200px;'><?php echo $optgudang; ?></select></td>
</tr>

<!--<tr><td><label><?php echo $_SESSION['lang']['tanggalmulai']?></label></td><td><input type="text" class="myinputtext" id="tgl1" name="tgl1" onchange="cekTanggal1(this.value);" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" value="<?php echo $qwe; ?>" /></td></tr>-->

<tr>
	<td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td>
	<td><input type="text" class="myinputtext" id="tgl1" name="tgl1"  onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:82px;" value="<?php echo $qwe; ?>" />

	
	<?php echo $_SESSION['lang']['sd']?> <input type="text" class="myinputtext" id="tgl2" name="tgl2" onchange="cekTanggal2(this.value);" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:82px;" /></td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['noakundari']?></label></td><td>:</td>
	<td><select id=akundari style='width:200px;' onchange=ambilAkun2(this.options[this.selectedIndex].value)><?php echo $optakun; ?></select>
	<img id='akundari' onclick=z.elSearch('akundari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	</td>
</tr>
<tr>
	<td><label><?php echo $_SESSION['lang']['noakunsampai']?></label></td><td>:</td>
	<td><select id=akunsampai style='width:200px;' onchange=hideById('printPanel')><option value=""></option></select>
	<img id='akundari' onclick=z.elSearch('akundari',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
	</td>
</tr>

<?
echo"
<tr>
	<td></td><td></td>
	
	<td>
		<button class=mybutton onclick=getLaporanBukuBesarv1('html')>".$_SESSION['lang']['preview']."</button>
		<button class=mybutton onclick=getLaporanBukuBesarv1('excel')>".$_SESSION['lang']['excel']."</button>
	</td>
</tr>
";

$arr='';
/*<button class=mybutton onclick="jurnalv1KeExcel(event,'keu_laporanBukuBesarv1_Excel.php')"><?php echo $_SESSION['lang']['excel'] ?></button>
*/
?>

</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
    	<span id=printPanel style='display:none;'>
        <img hidden onclick=jurnalv1KeExcel(event,'keu_laporanBukuBesarv1_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
        <img hidden onclick=jurnalv1KePDF(event,'keu_laporanBukuBesarv1_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
        </span>    
        <div style='width:100%;height:300px;overflow-y:auto;'>
		<div id=container></div>
        
        </div></fieldset>";
CLOSE_BOX();
close_body();
?>

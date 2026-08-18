<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zTools.js"></script>
<script language="javascript" src="js/zReport.js"></script>
<script language="javascript">
function loadjamDetail(kodevhc,tanggal,ev){
   param='kodevhc='+kodevhc+'&tanggal='+tanggal;
   // tujuan='vhc_slave_getDetailJam.php'+"?"+param;  
   // width='700';
   // height='400';
  
   // content="<fieldset style=width:98%;height:97%><iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe></fieldset>"
   // showDialog1('Detail Activity',content,width,height,ev); 
	
	alertify.popuppdf("Detail","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='vhc_slave_getDetailJam.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}
function qwe(){
    showById('printPanel');
    zPreview('vhc_slave_getLaporanJamKerja','##tgl1##tgl2##kodetraksi','printContainer');
}
function qweKeExcel(ev,tujuan){
	tgl1 =document.getElementById('tgl1');
	tgl2 =document.getElementById('tgl2');
	kodetraksi =document.getElementById('kodetraksi');
        tgl1V	=tgl1.value;
        tgl2V	=tgl2.value;
        kodetraksiV =kodetraksi.options[kodetraksi.selectedIndex].value;

	param='apa=excel'+'&tgl1='+tgl1V+'&tgl2='+tgl2V+'&kodetraksi='+kodetraksiV;
//alert(param);                
                
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev)	
}
function qweKePDF(ev,tujuan){
	tgl1 =document.getElementById('tgl1');
	tgl2 =document.getElementById('tgl2');
	kodetraksi =document.getElementById('kodetraksi');
        tgl1V	=tgl1.value;
        tgl2V	=tgl2.value;
        kodetraksiV =kodetraksi.options[kodetraksi.selectedIndex].value;

	param='apa=pdf'+'&tgl1='+tgl1V+'&tgl2='+tgl2V+'&kodetraksi='+kodetraksiV;
//alert(param);                
                
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev)	
}
function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>    
<?


//ambil tanggal traksi
$str="select distinct substr(tanggal,1,7) as periode from ".$dbname.".vhc_runht
      order by periode desc limit 24";
#$optper="<option value=''>".$_SESSION['lang']['sekarang']."</option>";
$optper="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optper.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}
$str="select distinct kodeorganisasi, namaorganisasi  from ".$dbname.".organisasi where tipe = 'TRAKSI'
      order by kodeorganisasi ";
$opttrx="";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $opttrx.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}

$opttrx="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$opttrx.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$opttrx.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$opttrx.="</optgroup>";
	}
}


OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['jmljamkerja']).'</span><br>');
?>
<fieldset style=float:left>
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >

<tr><td><label><?php echo $_SESSION['lang']['kodetraksi']?></label></td><td colspan=4><select class='select2' id=kodetraksi style='width:200px;' onchange=hideById('printPanel')><?php echo $opttrx; ?></select></td></tr>


<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td><input type="text" class="myinputtext" onchange=hideById('printPanel') id="tgl1" name="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:83px;" readonly/></td>
<td><label><?php echo $_SESSION['lang']['sd']?></label></td><td><input type="text" class="myinputtext" onchange=hideById('printPanel') id="tgl2" name="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:82px;" readonly/></td></tr>


<!--<tr height="20"><td colspan="2">&nbsp;</td></tr>-->
<tr ><td><td><?php echo "<button class=mybutton onclick=qwe()>".$_SESSION['lang']['preview']."</button>"; ?></td></tr>
</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX();
echo"
<span id=printPanel style='display:none;'>
     <img onclick=qweKeExcel(event,'vhc_slave_getLaporanJamKerja.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=qweKePDF(event,'vhc_slave_getLaporanJamKerja.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span><div id='printContainer' class='table-scroll' style=\"overflow: auto; height: 380px;\">

</div>";
CLOSE_BOX();
?>
<?php
echo close_body();
?>
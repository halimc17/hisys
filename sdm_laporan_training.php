<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<?php
$today = date('d-m-Y');
$emonth = '01'.date('-m-Y');

$str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','7','8') 
      and karyawanid <>".$_SESSION['standard']['userid']. " order by namakaryawan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKar="<option value=''></option>";
while($bar=$res->fetch())
{
	$optKar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."</option>";
}

$optJnsTraining='';
$qJnsTraining = selectQuery($dbname,'sdm_5jenistraining','kodetraining,jenistraining',"status='1'")."order by jenistraining";
$rJnsTraining = fetchData($qJnsTraining);
foreach($rJnsTraining as $val)
{  
	$optJnsTraining.="<option value='".$val['kodetraining']."'>".$val['jenistraining']."</option>";
} 	 

$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTipe="select kode,nama from ".$dbname.".sdm_5departemen order by nama asc";
$qTipe=$owlPDO->query($sTipe) or die(print " Gagal: ".PDOException::getMessage());
$qTipe->setFetchMode(PDO::FETCH_ASSOC);
while($rTipe=$qTipe->fetch())
{
        $optTipe.="<option value=".$rTipe['kode'].">".$rTipe['nama']."</option>";
}

$optOrg="<select id=kdOrg name=kdOrg style=\"width:160px;\" onchange=getKaryawan()><option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
        $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";

}
$optOrg.="</select>";



$arr="##kdOrg##bagId##karyawanId##jenistraining##tanggal1##tanggal2";


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script>
function getKaryawan()
{
    kdPt=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    param='kdOrg='+kdPt;
    tujuan='sdm_slave_2laporan_training.php';
	//alert('cek');
    post_response_text(tujuan+'?proses=getKaryawan', param, respog);
        function respog()
        {
                      if(con.readyState==4)
                      {
                                if (con.status == 200) {
                                                busy_off();
                                                if (!isSaveResponse(con.responseText)) {
                                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                                }
                                                else {
                                                  //	alert(con.responseText);
                                                        document.getElementById('karyawanId').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  
}
function getKaryawan2()
{
    kdPt=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
    bagian=document.getElementById('bagId').options[document.getElementById('bagId').selectedIndex].value;
    param='kdOrg='+kdPt+'&bagId='+bagian;
    tujuan='sdm_slave_2laporan_training.php';
	//alert('cek1');
    post_response_text(tujuan+'?proses=getKaryawan', param, respog);
        function respog()
        {
                      if(con.readyState==4)
                      {
                                if (con.status == 200) {
                                                busy_off();
                                                if (!isSaveResponse(con.responseText)) {
                                                        alert('ERROR TRANSACTION,\n' + con.responseText);
                                                }
                                                else {
                                                  //	alert(con.responseText);
                                                        document.getElementById('karyawanId').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }  
}
var opt="<option value=''><? echo $_SESSION['lang']['all']; ?></option>";
function Clear1()
{
	document.getElementById("kdOrg").selectedIndex = "0";
	document.getElementById("bagId").selectedIndex = "0";
	document.getElementById("karyawanId").selectedIndex = "0";
	document.getElementById("jenistraining").selectedIndex = "0";
    document.getElementById('printContainer').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
<fieldset style="float: left;">
<!--<legend><b><?php echo $_SESSION['lang']['LapTrain']?></b></legend> -->
<legend><b>Laporan Training Karyawan</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['perusahaan']?></label></td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td><select id="bagId" name="bagId" style="width:160px" onchange="getKaryawan2()"><?php echo $optTipe?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['namakaryawan']?></label></td><td><select id="karyawanId" name="karyawanId"  style="width:160px"><option value=''><?php echo $_SESSION['lang']['all']?></option>><?php echo $optKar?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['jeniskursus']?></label></td><td><select id="jenistraining" name="jenistraining"  style="width:160px"><option value=''><?php echo $_SESSION['lang']['all']?></option></option><?php echo $optJnsTraining?></select></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td>
<td> 
   <input type=text size="7px" class=myinputtext id=tanggal1 name=tanggal1 onmousemove=setCalendar(this.id) onkeypress="return false;" maxlength=10; value="<?php echo $emonth?>"  /> s/d
   <input type=text size="7px" class=myinputtext id=tanggal2 name=tanggal2 onmousemove=setCalendar(this.id) onkeypress="return false;" maxlength=10; value="<?php echo $today?>"  />
</td></tr>
<!--

<tr><td><label><?php echo $_SESSION['lang']['status']?></label></td><td><select id="stat" name="stat" style="width:150px"><option value=''><?php echo $_SESSION['lang']['all'] ?></option><?php echo $optStat?></select></td></tr>
-->
<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2"><button onclick="zPreview('sdm_slave_2laporan_training','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <button onclick="zPdf('sdm_slave_2laporan_training','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
        <button onclick="zExcel(event,'sdm_slave_2laporan_training.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>

<!--
-- 
You received this message because you are subscribed to the Google Groups "programmer-owlmedco" group.
To unsubscribe from this group and stop receiving emails from it, send an email to programmer-owlmedco+unsubscribe@googlegroups.com.
To post to this group, send email to programmer-owlmedco@googlegroups.com.
To view this discussion on the web visit https://groups.google.com/d/msgid/programmer-owlmedco/546eea63.2310460a.732e.ffffd599SMTPIN_ADDED_BROKEN%40gmr-mx.google.com.
For more options, visit https://groups.google.com/d/optout.
-->
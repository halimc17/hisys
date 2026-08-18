<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?> 
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 and kodeorganisasi like '%e' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{ 
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);

while($rThn=$qThn->fetch())
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}

$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$skegiatan="select kodekegiatan, namakegiatan from ".$dbname.".setup_kegiatan order by kodekegiatan";

$qkegiatan=$owlPDO->query($skegiatan) or die(print " Gagal: ".PDOException::getMessage());
$qkegiatan->setFetchMode(PDO::FETCH_ASSOC);

while($rkegiatan=$qkegiatan->fetch())
{
    $optkegiatan.="<option value='".$rkegiatan['kodekegiatan']."'>".$rkegiatan['kodekegiatan']." - ".$rkegiatan['namakegiatan']."</option>";
}

$arr="##tahun##kodeorg##kegiatan";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>

function summForm()
{
	//closeDialog();
	width='350';
	height='200';
	content="<div id=container style='overflow:auto;width:100%;height:190px;'></div>";
	ev='event';
	title="Detail Alokasi";
	showDialog1(title,content,width,height,ev);
}
//function getAlokasi(kdWS,kdkend,thnbdget)
function summForm2()
{
	//closeDialog();
	width='650';
	height='350';
	content="<div id=container2 style='overflow:auto;width:100%;height:330px;'></div>";
	ev='event';
	title="Detail Alokasi";
	showDialog2(title,content,width,height,ev);
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='200';
   height='150';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function Clear1()
{
    document.getElementById('tahun').value='';
    document.getElementById('kodeorg').value='';
    document.getElementById('kegiatan').value='';
    document.getElementById('printContainer').innerHTML='';
}

function cekkegiatan() {
    var tahun = document.getElementById('tahun').value;
    var kodeorg = document.getElementById('kodeorg').value;
    var param = "tahun="+tahun+"&kodeorg="+kodeorg;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    //=== Success Response
                    kegiatan.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bgt_slave_laporan_rekap.php?proses=cekkegiatan', param, respon);
}

</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rekap']." ".$_SESSION['lang']['budget']).'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['rekap']." ".$_SESSION['lang']['budget']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td><select id='tahun' style="width:150px;" onchange="cekkegiatan();"><?php echo $optThn?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kodeorg']?></label></td><td><select id='kodeorg'  style="width:150px;" onchange="cekkegiatan();"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['kegiatan']?></label></td><td><select id='kegiatan'  style="width:150px;"><?php echo $optkegiatan?></select></td></tr>
<tr height="20"><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2"><button onclick="zPreview('bgt_slave_laporan_rekap','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
                    <!--<button onclick="zPdf('bgt_slave_laporan_detail','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>
                    --><button onclick="zExcel(event,'bgt_slave_laporan_rekap.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
                    <!--<button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

--></table>
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
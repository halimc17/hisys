<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>

function lihatDetail(noakun,kodeorg,per,unit,tipe,ev){
   param='noakun='+noakun+'&kodeorg='+kodeorg+'&per='+per+'&unit='+unit+'&tipe='+tipe;
   tujuan='pabrik_slave_2biaya_detail.php'+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Detail Transaksi'+noakun,content,width,height,ev);     
}
    
    
</script>

<?
require_once('master_mainMenu.php');

$frm[0]='';
$frm[1]='';
$frm[2]='';

// OPEN_BOX('','<span class=judul>'.strtoupper('MILL COST').'</span><br>');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2biaya').'</span>');
$optOrg="<option value=''>KSP AGRO (". $_SESSION['lang']['all'].")</option>";
//optOrg=$optPer="";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING') order by namaorganisasi asc ";	
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$iPer="select distinct periode as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
	$optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
}


$arr="##kdorg##per";	
$frm[0].="
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorg style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=per style=\"width:150px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('pabrik_slave_2biaya','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'pabrik_slave_2biaya.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1175px'; >
</div></fieldset>";


##########tab2
/*
$arr="##kdorgt##tglt##pert";	
$frm[1].="
	<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id=kdorgt style=\"width:150px;\">".$optOrg."</select></td>
                </tr>
				
                <tr>
                    <td>".$_SESSION['lang']['tanggal']."</td>
                    <td>:</td>
					<td><input type=text class=myinputtext id=tglt  style=\"width:145px;\" onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
                    <td hidden><select id=pert style=\"width:150px;\">".$optPer."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('pabrik_slave_2biaya_t2_new','".$arr."','printContainer2') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'pabrik_slave_2biaya_t2_new.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[1].="
	<fieldset style='float:left;'>
        <legend>Info</legend>
        <li>Untuk laporan ini terkait setup yang terdapat di menu : Keuangan->Setup->Mesin Laporan, dengan judul laporan <b>Analisa Biaya Pabrik</b></li>
       
</fieldset>";

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer2' style='overflow:auto;height:350px;max-width:1150px'; >
</div></fieldset>";
*/

$hfrm[0]='Rekap';
// $hfrm[1]='Detail';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,100,1150);	

CLOSE_BOX();
echo close_body();
				
?>
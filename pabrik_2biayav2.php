<?//Ind
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

function lihatDetail(kdorg,thn,nourutlaporan,tipe,ev){
   param='kdorg='+kdorg+'&thn='+thn+'&nourutlaporan='+nourutlaporan+'&tipe='+tipe;
   tujuan='pabrik_slave_2biayav2_detail.php'+"?"+param;  
   width='800';
   height='400';

   content="<fieldset style=width:780px;height:385px><iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe></fieldset>"
   showDialog1('Detail Transaksi Mill Cost'+kdorg,content,width,height,ev);     
}
    
    
</script>

<?
require_once('master_mainMenu.php');
if($_SESSION['language']=='EN'){
  OPEN_BOX('','<span class=judul>'.strtoupper('MILL ACTUAL COST vs BUDGET').'</span><br>');
}else{
  OPEN_BOX('','<span class=judul>'.strtoupper('REKAP BUDGET vs REALISASI BIAYA MILL').'</span><br>');
}

$optOrg="";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK' order by namaorganisasi asc ";	
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$optThn="";
$iPer="select distinct substr(periode,1,4) as tahun from ".$dbname.".setup_periodeakuntansi "
        . " order by periode desc limit 12";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
	$optThn.="<option value=".$dPer['tahun'].">".$dPer['tahun']."</option>";
}


$arr="##kdorg##thn";	
echo"<fieldset style='float:left;'>
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
                    <td><select id=thn style=\"width:150px;\">".$optThn."</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('pabrik_slave_2biayav2','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
                    <button onclick=zExcel(event,'pabrik_slave_2biayav2.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "
<fieldset style='clear:both;max-width:1235px'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1235px'; >
</div></fieldset>";//<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'; >
//<div id='printContainer'>
CLOSE_BOX();
echo close_body();					
?>
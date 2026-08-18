<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zSearch.js></script>
<script languange=javascript1.2 src='js/formTable.js'></script>
<script language="JavaScript1.2" src="js/keu_2loansummary.js?ver=1.5"></script>
<?
require_once('master_mainMenu.php');

$frm[0]='';
$frm[1]='';
$frm[2]='';

OPEN_BOX('','<span class=judul>'.getMenu('keu_2loansummary2').'</span><br>');
$sBankDt="select b.namabank as namabank,a.noakun as noakun from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b 
              on a.namabank=b.kodebank";
$rBankDt=fetchData($sBankDt);
foreach ($rBankDt as $key => $val) {
    $nmBank[$val['noakun']]=$val['namabank'];
}
$optnotransaksi3=$optnotransaksi2=$optunit=$optnotransaksi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select notransaksi,noakun from ".$dbname.".keu_pmpeminjamanht where jenis='KISI' and tp_pokok=0 ";
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
   
    $optnotransaksi.="<option value='".$bar['notransaksi']."'>".$nmBank[$bar['noakun']]."-".$bar['notransaksi']."</option>";
}

$str = "select notransaksi,noakun from ".$dbname.".keu_pmpeminjamanht where jenis='KISI' and tp_pokok=1";
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optnotransaksi2.="<option value='".$bar['notransaksi']."'>".$nmBank[$bar['noakun']]."-".$bar['notransaksi']."</option>";
}
$str = "select notransaksi,noakun from ".$dbname.".keu_pmpeminjamanht where jenis='KRK' ";
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optnotransaksi3.="<option value='".$bar['notransaksi']."'>".$nmBank[$bar['noakun']]."-".$bar['notransaksi']."</option>";
}


$frm[0].="
    <fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>".$_SESSION['lang']['notransaksi']."</td>
                    <td>:</td>
                    <td><select  style=\"width:150px;\" id=notransaksi1 onchange=getperiode('1')>".$optnotransaksi."</select> </td>
                </tr>
                
                 <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode1 style=\"width:150px;\" >".$optunit."</select>&nbsp;&nbsp;<img id=periode1 onclick=z.elSearch('periode1',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> </td>
                </tr>
                
                 
                
                <tr>
                    <td><td><td>
                   <button id=preview class=mybutton onclick=preview('1')>".$_SESSION['lang']['preview']."</button>
                    <button id=excel class=mybutton onclick=excel('1','event')>".$_SESSION['lang']['excel']."</button>
                    <button id=excel class=mybutton onclick=pdf('1','event')>".$_SESSION['lang']['pdf']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[0].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer1' style='overflow:auto;height:350px;max-width:1000px'; >
</div></fieldset>";

$frm[1].="
    <fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
               <tr>
                    <td>".$_SESSION['lang']['notransaksi']."</td>
                    <td>:</td>
                    <td><select  style=\"width:150px;\" id=notransaksi2 onchange=getperiode('2')>".$optnotransaksi2."</select> </td>
                </tr>
                
                 <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode2 style=\"width:150px;\" >".$optunit."</select>&nbsp;&nbsp;<img id=periode2 onclick=z.elSearch('periode2',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> </td>
                </tr>
                <tr>
                    <td><td><td>
                   <button id=preview class=mybutton onclick=preview('2')>".$_SESSION['lang']['preview']."</button>
                    <button id=excel class=mybutton onclick=excel('2','event')>".$_SESSION['lang']['excel']."</button>
                    <button id=excel class=mybutton onclick=pdf('2','event')>".$_SESSION['lang']['pdf']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[1].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer2' style='overflow:auto;height:350px;max-width:1000px'; >
</div></fieldset>";

$frm[2].="
    <fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
               <tr>
                    <td>".$_SESSION['lang']['notransaksi']."</td>
                    <td>:</td>
                    <td><select  style=\"width:150px;\" id=notransaksi3  onchange=getperiode('3')>".$optnotransaksi3."</select> </td>
                </tr>
                
                 <tr>
                    <td>".$_SESSION['lang']['periode']."</td>
                    <td>:</td>
                    <td><select id=periode3 style=\"width:150px;\" >".$optunit."</select>&nbsp;&nbsp;<img id=periode3 onclick=z.elSearch('periode3',event) class=resicon src=images/onebit_02.png style='position:relative;top:3px;left:3px;'> </td>
                </tr>
                
                <tr>
                    <td><td><td>
                   <button id=preview class=mybutton onclick=preview('3')>".$_SESSION['lang']['preview']."</button>
                    <button id=excel class=mybutton onclick=excel('3','event')>".$_SESSION['lang']['excel']."</button>
                    <button id=excel class=mybutton onclick=pdf('3','event')>".$_SESSION['lang']['pdf']."</button>
                    </td>
                </tr>
            </table>
</fieldset>";

$frm[2].="
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer3' style='overflow:auto;height:350px;max-width:1000px'; >
</div></fieldset>";

$hfrm[0]="Kisi per Pencairan";
$hfrm[1]="Kisi per Total Pencairan";
$hfrm[2]="KRK";
drawTab('FRM',$hfrm,$frm,300,1150); 

CLOSE_BOX();
echo close_body();
                
?>
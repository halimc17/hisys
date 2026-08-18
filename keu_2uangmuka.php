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
<script language="JavaScript1.2" src="js/formReport.js"></script>
<script language=javascript src='js/keu_2uangmuka.js?v=<?php echo time(); ?>'></script>

<?
require_once('master_mainMenu.php');

$frm[0]='';
$frm[1]='';

OPEN_BOX('','<span class=judul>'.getMenu('keu_2uangmuka').'</span><br>');
$optakunkk=$optakun=$optPer=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

// $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by namaorganisasi asc "; 
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
//  $optunit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']."-".$bar['namaorganisasi']."</option>";
// }

$str="select namaorganisasi,induk,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc "; 
// exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
  $optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

$str="select * from ".$dbname.".keu_5akun where noakun like '%11801%' and detail=1";  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// exit($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
  $optakun.="<option value=".$bar['noakun'].">".$bar['noakun']." - ".$bar['namaakun']."</option>";
}


// $optposting=$optpembayaran=$opttipetransaksi="<option value=''>Seluruhnya</option>";
// $optposting.="<option value=0>Belum Diajukan</option>";
// $optposting.="<option value=1>Disetujui</option>";
// $optposting.="<option value=3>Ditolak</option>";
// $optposting.="<option value=9>Proses Persetujuan</option>";

// $opttipetransaksi.="<option value='M'>Masuk</option>";
// $opttipetransaksi.="<option value='K'>Keluar</option>";

// $arrtipe=array('0'=>'Belum Dibayar','1'=>'Sudah Dibayar');
// foreach($arrtipe as $key=>$data){
//  $optpembayaran.="<option value='".$key."'>".$data."</option>";
// }

// <td>".$_SESSION['lang']['nodok']."</td>
// <td>:</td>   
// <td>
//  <input type=text id=nodok size=50 class=myinputtext style=\"width:150px;\">
// </td>


echo"
  <fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
               
         <tr>
                    <td>".$_SESSION['lang']['pt']."</td>
                    <td>:</td>
                    <td><select id='pt' style='width:150px' onchange='getUnit()'>".$optpt."</select></td>
                </tr> 
        <tr>
                    <td>".$_SESSION['lang']['unit']."</td>
                    <td>:</td>
                    <td><select id='unit' style='width:150px'></select></td>
                </tr>
        
        <tr>
                    <td>".$_SESSION['lang']['noakun']."</td>
                    <td>:</td>
                    <td><select id=noakun style='width:150px'>".$optakun."</select></td>
                </tr>

                <tr>
          <td>".$_SESSION['lang']['tanggal']."</td>
          <td>:</td>
          <td><input type='text' class='myinputtext' id='tgl1'  style=\"width:60px;\" onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly>
          s/d
          <input type='text' class='myinputtext' id='tgl2'  style=\"width:60px;\" onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='7' maxlength='10' readonly></td>
        </tr>

        
                <tr>
                    <td><td><td>
                    <button class=mybutton onclick=preview('html')>".$_SESSION['lang']['preview']."</button>
                    <button class=mybutton onclick=preview('excel')>".$_SESSION['lang']['excel']."</button>
                    <button class=mybutton onclick=preview('pdf')>".$_SESSION['lang']['pdf']."</button>
                    <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
//  <button id=excel class=mybutton onclick=pdf('event')>".$_SESSION['lang']['pdf']."</button>
echo"
<fieldset style='clear:both;'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer'  >
</div ></fieldset>";


CLOSE_BOX();
echo close_body();
        
?>
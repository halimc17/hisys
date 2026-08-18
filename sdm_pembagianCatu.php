<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_pembagianCatu.js'></script>
<?php

include('master_mainMenu.php');

$opttipe=$optPeriode = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

//ambil periode penggajian
$str = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

while ($bar = $res->fetch()) {
    $optPeriode.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}

$str = "select * from " . $dbname . ".sdm_5tipekaryawan where id in ('1','2','3')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
//$opttipe = "<option value=''>All</option>";

while ($bar = $res->fetch()) {
    $opttipe.="<option value='" . $bar->id . "'>" . $bar->tipe . "</option>";
}/*
 
			 <fieldset style=height:125px><legend>Info</legend>
			<table border=0>
				<tr>
					<td>Jika karyawan PKWT maka diisikan rupiah perkehadirannya,<br>selain PKWT maka diisikan Harga Beras Rp/Kg</td>
				</tr>
			  </table>
			 </fieldset>

*/



OPEN_BOX('','<span class=judul>'.getMenu('sdm_pembagianCatu').'</span>');
$frm[0] = "<fieldset><legend>Form</legend>
			<table border=0>
		     <tr><td>" . $_SESSION['lang']['kodeorg'] . "<td>:<td><input style='width:75px' type=text id=kodeorg disabled class=myinputtext value='" . $_SESSION['empl']['lokasitugas'] . "'></td></tr>
             <tr><td>" . $_SESSION['lang']['periodegaji'] . "<td>:<td><select  style='width:80px' id=periode onchange=getrp()>" . $optPeriode . "</select></td></tr> 
			 <tr><td>" . $_SESSION['lang']['tipekaryawan'] . "<td>:<td><select  style='width:80px' id=tkar>" . $opttipe . "</select></td></tr> 	
			 <tr><td>" . $_SESSION['lang']['hargasatuan'] . " Catu<td>:<td><input style='width:75px' type=text class=myinputtextnumber disabled onkeypress=\"return angka_doang(event);\" id=harga size=10></td></tr>     
            
			 <tr><td><td><td>
             <button class=mybutton onclick=tampilkanCatu()>" . $_SESSION['lang']['preview'] . "</button>
             </table>
			 </fieldset>
			             <br>
			<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
             <div id=container style=height:350px;overflow:auto;'>
             </div></fieldset>";

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $str = "select sum(jumlahrupiah) as jumlah, hargacatu,kodeorg,periodegaji,sum(posting) as posting,sum(totalcatu) as totalcatu from " . $dbname . ".sdm_catu  
             group by kodeorg,periodegaji order by periodegaji desc  limit 40";
} else {
    $str = "select sum(jumlahrupiah) as jumlah,hargacatu,sum(posting) as posting, kodeorg,periodegaji,sum(totalcatu) as totalcatu from " . $dbname . ".sdm_catu 
            where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' group by kodeorg,periodegaji 
            order by periodegaji desc  limit 40";
}
//  <td align=center>" . $_SESSION['lang']['harga'] . "/Ltr</td>    
$frm[1] = "<fieldset><legend>" . $_SESSION['lang']['laporanCatu'] . "</legend>
              <div id=container style=height:460px;overflow:auto;'>
			  <table class=sortable cellspacing=1 border=0>
              <thead>
              <tr class=rowheader>
              <td align=center>" . $_SESSION['lang']['nomor'] . "</td>
              <td align=center>" . $_SESSION['lang']['unitkerja'] . "</td>
              <td align=center>" . $_SESSION['lang']['periode'] . "</td>
            
              <td align=center>" . $_SESSION['lang']['jumlah'] . " (Kg)</td>    
              <td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>    
              <td align=center>" . $_SESSION['lang']['action'] . "</td>
               </tr>
               <tbody id=containerlist>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;// <td align=right>" . number_format($bar->hargacatu, 0, '.', ',') . "</td>     
    $frm[1].="<tr class=rowcontent>
                  <td align=center>" . $no . "</td>
                    <td>" . $bar->kodeorg . "</td> 
                    <td>" . $bar->periodegaji . "</td>
                   
                    <td align=right>" . number_format($bar->totalcatu, 0, '.', ',') . "</td>    
                    <td align=right>" . number_format($bar->jumlah, 0, '.', ',') . "</td>    
                    <td align=center><img src='images/excel.jpg' class='resicon' title='Excel' onclick=getExcel(event,'sdm_slave_pembagianCatuExcel.php','" . $bar->kodeorg . "','" . $bar->periodegaji . "') > &nbsp &nbsp";
    if ($bar->posting > 0)
        $frm[1].="<img src='images/skyblue/posted.png'>";
    else
        $frm[1].="<img src='images/skyblue/posting.png'  class='resicon' title='Posting' onclick=postingCatu('" . $bar->kodeorg . "','" . $bar->periodegaji . "'," . $bar->jumlah . ")>";
    $frm[1].="</td>    
                  </tr>";
}
$frm[1].="</tbody>
              <tfoot>
              </tfoot>
              </table>";



//========================
$hfrm[0] = $_SESSION['lang']['form'];
$hfrm[1] = $_SESSION['lang']['list'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 300, '100%');
//===============================================
CLOSE_BOX();
echo close_body();
?>
<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $str = "select sum(jumlahrupiah) as jumlah,hargacatu, kodeorg,periodegaji,sum(totalcatu) as totalcatu from " . $dbname . ".sdm_catu  
             group by kodeorg,periodegaji order by periodegaji desc limit 40";
} else {
    $str = "select sum(jumlahrupiah) as jumlah,hargacatu,sum(posting) as posting, kodeorg,periodegaji,sum(totalcatu) as totalcatu from " . $dbname . ".sdm_catu 
            where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' group by kodeorg,periodegaji 
            order by periodegaji desc  limit 40";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$frm[0] = "";
$frm[1] = "";
$no = 0;// <td>" . number_format($bar->hargacatu, 0, '.', ',') . "</td>      
while ($bar = $res->fetch()) {
    $no+=1;
    $frm[1].="<tr class=rowcontent>
                  <td>" . $no . "</td>
                    <td>" . $bar->kodeorg . "</td> 
                    <td>" . $bar->periodegaji . "</td>
                   
                    <td align=right>" . number_format($bar->totalcatu, 0, '.', ',') . "</td>    
                    <td align=right>" . number_format($bar->jumlah, 0, '.', ',') . "</td>    
                    <td><img src='images/excel.jpg' class='resicon' title='Excel' onclick=getExcel(event,'sdm_slave_pembagianCatuExcel.php','" . $bar->kodeorg . "','" . $bar->periodegaji . "') > &nbsp &nbsp";
    if ($bar->posting > 0)
        $frm[1].="<img src='images/skyblue/posted.png'>";
    else
        $frm[1].="<img src='images/skyblue/posting.png'  class='resicon' title='Posting' onclick=postingCatu('" . $bar->kodeorg . "','" . $bar->periodegaji . "'," . $bar->jumlah . ")>";
    $frm[1].="</td>    
                  </tr>";
}
echo $frm[1];
?>

<?php // file creator: dhyaz aug 3, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$namauser=$_POST['namauser'];

//kamus nama unit
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
    where tipe in('KEBUN','PABRIK','GUDANG','GUDANGTEMP','TRAKSI','KANWIL') or (tipe='HOLDING' and length(kodeorganisasi)=4)
    order by kodeorganisasi";
$kamus=array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $kamus[$bar->kodeorganisasi]=$bar->namaorganisasi;
}

echo"<table class=sortable cellspacing=1 border=0>
    <thead>
    <tr>
        <td align=center>".$_SESSION['lang']['hari']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['waktu']."</td>";
    echo"</tr>  
    </thead>
    <tbody>";

//ambil login
$str="select lastupdate from ".$dbname.".login_history
    where lastuser = '".$namauser."' 
    order by lastupdate desc";
//echo $str;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    echo "<tr class=rowcontent>";
    echo"<td>".date("D", strtotime($bar->lastupdate))."</td>";
    echo"<td>".date("M j Y", strtotime($bar->lastupdate))."</td>";
    echo"<td>".date("H:i:s", strtotime($bar->lastupdate))."</td>";
    echo"</tr>";
}

    echo"</tbody>
    <tfoot>
    </tfoot>		 
    </table>
";
    
?>

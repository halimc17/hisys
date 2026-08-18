<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');

if($kdorg==''){
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}

######################################
############# prepare data ###########
######################################


#setup blok

#get tahun tanam blok
$str="select * from ".$dbname.".setup_blok where kodeorg like '".$kdorg."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kdblok[$bar['kodeorg']]=$bar['kodeorg'];
    $luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
    $tt[$bar['kodeorg']]=$bar['tahuntanam'];
    $pkk[$bar['kodeorg']]=$bar['jumlahpokok'];
}

$str="select distinct(substr(periode,1,4)) as tahun from ".$dbname.".setup_periodeakuntansi ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $tahun[$bar['tahun']]=$bar['tahun'];
}

$str="select sum(jumlah) as jumlah,kodeblok,left(periode,4) as tahun from ".$dbname.".keu_jurnaldt_vw where"
        . " (noakun like '1260%' or noakun like '1261%') and kodeorg='".$kdorg."' group by kodeblok,left(periode,4)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $jumlah[$bar['kodeblok']][$bar['tahun']]=$bar['jumlah'];
}


$str="select sum(totalkg) as totalkg,blok from ".$dbname.".kebun_spb_vw where kodeorg='".$kdorg."' group by blok ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kg[$bar['blok']]=$bar['totalkg']/1000;
}					

array_multisort($kdblok,SORT_ASC);

if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}//style=width:63%


$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center>" . $_SESSION['lang']['pokok'] . "</td>
        ";
    foreach($tahun as $thn)
    {
        $stream.="<td align=center>".$thn."</td>";
    }
    $stream.="<td align=center>".$_SESSION['lang']['total']."</td>";
    $stream.="<td align=center>Rp/Ha</td>";
    $stream.="<td align=center>Rp/Pkk</td>";
    $stream.="<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['produksi']." (TON)</td>";
$stream.="
        </tr>
    </thead>
 <tbody>";

foreach($kdblok as $blok)
{
    $stream.="
        <tr class=rowcontent>
            <td>".$blok."</td>
            <td align=right>".$luas[$blok]."</td>     
            <td align=right>".$tt[$blok]."</td> 
            <td align=right>".@number_format($pkk[$blok])."</td>     
    ";
    foreach($tahun as $thn)
    {
        $stream.="<td align=right  align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail('".$blok."','".$thn."','html',event)>".@number_format($jumlah[$blok][$thn])."</td>";
        @$subtotjumlah[$blok]+=$jumlah[$blok][$thn];
        @$gtjumlahthn[$thn]+=$jumlah[$blok][$thn];
    }
    $stream.="<td align=right>".@number_format($subtotjumlah[$blok])."</td>";
    $stream.="<td align=right>".@number_format($subtotjumlah[$blok]/$luas[$blok],2)."</td>";
    $stream.="<td align=right>".@number_format($subtotjumlah[$blok]/$pkk[$blok],2)."</td>";
    $stream.="<td align=right  align=right style=cursor:pointer; title='clickdetail' onclick=lihatdetail2('".$blok."','".$thn."','html',event)>".@number_format($kg[$blok],2)."</td>";
    $stream.="</tr>";
    
    @$gtjumlah+=$subtotjumlah[$blok];
    @$gtluas+=$luas[$blok];
    @$gtpkk+=$pkk[$blok];
    @$gtkg+=$kg[$blok];
    
}
$stream.="<tr class=rowcontent>";
$stream.="<td align=right>".$_SESSION['lang']['total']."</td>";
$stream.="<td align=right>".$gtluas."</td>";
$stream.="<td align=right></td>";
$stream.="<td align=right>".@number_format($gtpkk,2)."</td>";
    foreach($tahun as $thn)
    {
        $stream.="<td align=center>".@number_format($gtjumlahthn[$thn],2)."</td>";
    }
    
$stream.="<td align=right>".@number_format($gtjumlah,2)."</td>";
$stream.="<td align=right>".@number_format($gtjumlah/$gtluas,2)."</td>";
$stream.="<td align=right>".@number_format($gtjumlah/$gtpkk,2)."</td>";
$stream.="<td align=right>".@number_format($gtkg,2)."</td>";
$stream.="
 </tbody>
     </table>";



switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "laporan_rekap_panen_per_blok" . $kdorg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
}
?>
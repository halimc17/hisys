<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$per=checkPostGet('periode','');
$pt=checkPostGet('pt','');
$tipe = checkPostGet('tipe', '');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$optNmPt=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

switch($method)
{
 case 'loadLaporan':

$str="select * from ".$dbname.".bgt_regional_assignment";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $regional[$bar['subregional']]=$bar['subregional'];
}


$dtkommin=array();
$dtkomplus=array();
#gaji_vw
#yang bukan bulan berjalan ('32','33','40','45','62','63','64','65')
$str = "select a.*,b.lokasitugas,b.subbagian, c.id as id, c.name, c.plus, d.* from ".$dbname.".sdm_gaji a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
        left join ".$dbname.".sdm_ho_component c on c.id=a.idkomponen
        left join ".$dbname.".bgt_regional_assignment d on d.kodeunit=b.lokasitugas
        where lokasitugas like '%".$pt."%' and periodegaji ='".$per."' and idkomponen not in ('32','33','40','45','62','63','64','65') ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$rupiah[$bar['subregional']][$bar['idkomponen']]+=$bar['jumlah'];
    @$trupiah[$bar['idkomponen']]+=$bar['jumlah'];
    if($bar['plus']=='1'){
        if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
            @$dtkomplus[$bar['id']]=$bar['id'];
        }
    }else{
        @$dtkommin[$bar['id']]=$bar['id'];
    }
    $nmkom[$bar['id']]=$bar['name'];

    $cekdata[$bar['id']]+=1;

    $karyid[$bar['subregional']][$bar['karyawanid']]+=1;
}

// echo "<pre>";
// print_r($karyid);
// echo "</pre>";

if($cekdata<1){
    exit("Warning:Data Kosong");
}

$str = "select a.*,b.lokasitugas,b.subbagian, c.id as id, c.name, c.plus, d.* from ".$dbname.".sdm_gaji a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
        left join ".$dbname.".sdm_ho_component c on c.id=a.idkomponen
        left join ".$dbname.".bgt_regional_assignment d on d.kodeunit=b.lokasitugas
        where lokasitugas like '%".$pt."%' and periodegaji ='".$per."' and idkomponen  in ('32','33','40','45','62','63','64','65') ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$rupiah[$bar['subregional']][$bar['idkomponen']]+=$bar['jumlah'];
    @$trupiah[$bar['idkomponen']]+=$bar['jumlah'];
    if($bar['plus']=='1'){
        if($bar['id']!='70' and $bar['id']!='71' and $bar['id']!='72' and $bar['id']!='73' and $bar['id']!='80'){
            @$dtkomplus[$bar['id']]=$bar['id'];
        }
    }else{
        @$dtkommin[$bar['id']]=$bar['id'];
    }
    $nmkom[$bar['id']]=$bar['name'];
}

//ditambah 1 untuk total
@$tbrskomplus=count($dtkomplus);
@$tbrskommin=count($dtkommin);
$gross=$tbrskommin+$tbrskomplus+2;


array_multisort($dtafd,SORT_ASC);


if($tipe=='pdf'){
    $border=1;
    $cell=0;
    $font="style='font-size:12px'";

    $stream.="
    <table cellpading=1 cellspacing=0 border=0 class=sortable style=width:100%>
    <thead>
    <tr class=rowheader>
    <th align=left>".$optNmPt[$pt]."</th>
    </tr>
    </thead>
    <tr class=rowcontent>
    <td>REKAP GAJI</td>
    </tr>
    <tr class=rowcontent>
    <td>Periode : ".$per."</td>
    </tr>
    </table>";

} else {
    $border=0;
    $cell=1;
}


$stream.="<table cellpading=1 cellspacing=".$cell." border=".$border." class=sortable ".$font.">";
$stream.="
<thead>
<tr class=rowheader>
<th rowspan=2 align=center>Name</th>
<th rowspan=2 align=center>Karyawan</th>
<th colspan=".$tbrskomplus." align=center>".$_SESSION['lang']['penambah']."</th>
<th colspan=".$tbrskommin." align=center>".$_SESSION['lang']['pengurang']."</th>
</tr>";

$stream.="<tr>";
foreach (@$dtkomplus as $komplus){
    $stream.="<th align=center>".$nmkom[$komplus]."</th>";
}

foreach (@$dtkommin as $kommin){
    $stream.="<th align=center>".$nmkom[$kommin]."</th>";
}

$stream.="</tr></thead>
<tbody>
";

foreach ($regional as $reg) {
    $stream.="
    <tr class=rowcontent>
    <td>".$reg."</td>
    <td></td>
    ";

    foreach ($dtkomplus as $komplus){
        $stream.="<td align=right>".@number_format($rupiah[$reg][$komplus])."</td>";
    }
    foreach ($dtkommin as $kommin){
        $stream.="<td align=right>".@number_format($rupiah[$reg][$kommin])."</td>";
    }
}

$stream.="<tr class=rowcontent>
<td align=center>TOTAL</td>
<td></td>
";

foreach ($dtkomplus as $komplus){
    $stream.="<td align=right>".@number_format($trupiah[$komplus])."</td>";

}
foreach ($dtkommin as $kommin){
    $stream.="<td align=right>".@number_format($trupiah[$kommin])."</td>";

}

$stream.="<tr class=rowcontent>
<td colspan=".$gross." >Gross Income =</td>
</tr>";

$stream.="</tr></tr></tbody></table>";





if ($tipe=='pdf') {
    $stream.="<table border=0 width=50% style='font-size:12px'>
    <tr>
    <td>&nbsp;</td>
    </tr>
    <tr>
    <td>Jakarta, ".date("Y/m/d")."</td>
    </tr>
    <tr>
    <td width=200px align=center>Dibuat Oleh</td>
    <td width=200px align=center>Dicek Oleh</td>
    <td width=200px align=center>Disetujui Oleh</td>
    </tr>
    <tr>
    <td height=80px></td>
    <td></td>
    <td></td>
    </tr>
    <tr>
    <td align=center>(".$optNmKar[$_SESSION['standard']['userid']].")</td>
    <td align=center></td>
    <td align=center></td>
    </tr>
    </table>";
}

    
if($tipe=='pdf'){
    $dompdf = new Dompdf();
    $dompdf->loadHtml($stream);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("Laporan_Harian_TBS",array("Attachment"=>0)); 
}
else
{
    echo $stream;
}
          
        
    break;

    default:
    break;
}


?>
<?php 
// file creator: dhyaz aug 3, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/HtmlExcel.php');


$kodept = checkPostGet('kodept','');
$tipe = checkPostGet('tipe','');
$kodeunit = checkPostGet('kodeunit','');

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

//ambil anak-anak
$str="select kodeorganisasi from ".$dbname.".organisasi
    where induk = '".$kodeunit."' and tipe like 'gudang%'
    order by kodeorganisasi";
$anak=array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $anak[$bar->kodeorganisasi]=$bar->kodeorganisasi;
}

//ambil unit holding
$jumlahunit=0;
$str="select kodeorganisasi from ".$dbname.".organisasi 
    where induk='".$kodept."' and kodeorganisasi like '".$kodeunit."%' and tipe = 'HOLDING'
    order by tipe desc";
$unit=array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $unit[$bar->kodeorganisasi]=$bar->kodeorganisasi;
    $jumlahunit+=1;
}
$str="select kodeorganisasi from ".$dbname.".organisasi 
    where induk='".$kodept."' and kodeorganisasi like '".$kodeunit."%' and tipe != 'HOLDING'
    order by tipe desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $unit[$bar->kodeorganisasi]=$bar->kodeorganisasi;
    $jumlahunit+=1;
}

// ambil data
$arr=Array();
$str1="select * from ".$dbname.".keu_setup_watu_tutup order by periode desc, kodeorg";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch())
{
    $arr[$bar1->periode][$bar1->kodeorg]['username']=$bar1->username;
    $arr[$bar1->periode][$bar1->kodeorg]['waktu']=$bar1->waktu;
}

$no=1;
$str="select * from ".$dbname.".setup_periodeakuntansi order by periode desc, kodeorg";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
        @$periode[$baris->periode]=$baris->periode;    
        @$tutup[$baris->periode][$baris->kodeorg]=$baris->tutupbuku;
        @$waktu[$baris->periode][$baris->kodeorg]=$arr[$baris->periode][$baris->kodeorg]['waktu'];
        @$pelaku[$baris->periode][$baris->kodeorg]=$arr[$baris->periode][$baris->kodeorg]['username'];
}

// kasbank total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".keu_kasbankht group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $kasbank[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// kasbank total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".keu_kasbankht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $kasbankp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// bkm total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".kebun_aktifitas group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $bkm[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// bkm total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".kebun_aktifitas where jurnal = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $bkmp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// traksi running total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_runht group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $traksi[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// traksi running total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_runht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $traksip[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}

// traksi service total
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_penggantianht group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    if(!isset($traksi[$baris->periode][$baris->kodeorg])) $traksi[$baris->periode][$baris->kodeorg]=0;
        $traksi[$baris->periode][$baris->kodeorg]+=$baris->jumlah;    
}
// traksi service total posted
$str="select kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".vhc_penggantianht where posting = 1 group by kodeorg, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    if(!isset($traksip[$baris->periode][$baris->kodeorg])) $traksip[$baris->periode][$baris->kodeorg]=0;
    $traksip[$baris->periode][$baris->kodeorg]+=$baris->jumlah;    
}


// bapp total
$str="select substr(kodeblok,1,4) as kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_baspk group by substr(kodeblok,1,4), substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $bapp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}
// bapp total post
$str="select substr(kodeblok,1,4) as kodeorg, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_baspk where statusjurnal = 1 group by substr(kodeblok,1,4), substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $bappp[$baris->periode][$baris->kodeorg]=$baris->jumlah;    
}


// gudang total
$str="select kodegudang, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah,hasilpersetujuan1,tipetransaksi from ".$dbname.".log_transaksiht where hasilpersetujuan1!=2 group by kodegudang, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $gudang[$baris->periode][$baris->kodegudang]=$baris->jumlah;    
}
// gudang total post
$str="select kodegudang, substr(tanggal,1,7) as periode, count(notransaksi) as jumlah from ".$dbname.".log_transaksiht where post = 1 group by kodegudang, substr(tanggal,1,7) ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($baris=$res->fetch())
{
    $gudangp[$baris->periode][$baris->kodegudang]=$baris->jumlah;    
}
$stream='';
$stream.="<table class=sortable cellspacing=1 border=0 width=100%>
    <thead>
    <tr>
        <td align=center>".$_SESSION['lang']['periode']."</td>
        <td align=center>".$_SESSION['lang']['kodeorg']."</td>
        <td align=center>".$_SESSION['lang']['status']."</td>
        <td align=center>".$_SESSION['lang']['waktu']."</td>
        <td align=center>".$_SESSION['lang']['nama']."</td>  
        <td align=center>".$_SESSION['lang']['kasbank']." (posted)</td>  
        <td align=center>".$_SESSION['lang']['traksi']." (posted)</td>  
        <td align=center>BAPP (posted)</td>  
        <td align=center>BKM (posted)</td>";  
if(!empty($anak))foreach($anak as $data){
    $stream.="<td align=center colspan=2 title=\"".$kamus[$data]."\">".$data."</td>";
}
    $stream.="</tr>  
    </thead>
    <tbody>";


if(!empty($periode))foreach($periode as $per){
    $tamper=true;
    if(!empty($unit))foreach($unit as $uni){
        if($tamper){
            $tampil=$per;
        }else{
            $tampil='';
        }
        $tamtut='';
      
        if(isset($tutup[$per][$uni]) and $tutup[$per][$uni]=='1'){
			$tamtut='closed'; 
			  $stream.="<tr class=rowcontent>";
		}
        if(!isset($tutup[$per][$uni]) or $tutup[$per][$uni]=='0'){
			$tamtut='__active'; $stream.="<tr bgcolor=lightgreen>"; 
		}
        // echo $warna;
        if($tamper)$stream.="<td align=center rowspan=".$jumlahunit.">".$tampil."</td>";
        $stream.="<td>".$uni."</td>";
        $stream.="<td>".$tamtut."</td>";
        $stream.="<td>".(isset($waktu[$per][$uni])? $waktu[$per][$uni]: '')."</td>";
        $stream.="<td>".(isset($pelaku[$per][$uni])? $pelaku[$per][$uni]: '')."</td>";
        @$persen=$kasbankp[$per][$uni]*100/$kasbank[$per][$uni];
        $stream.="<td align=right nowrap>".(isset($kasbank[$per][$uni])? $kasbank[$per][$uni]: 0)." (".number_format(fixnan($persen))."%)</td>";
        @$persen=$traksip[$per][$uni]*100/$traksi[$per][$uni];
        $stream.="<td align=right nowrap>".(isset($traksi[$per][$uni])? $traksi[$per][$uni]: 0)." (".number_format(fixnan($persen))."%)</td>";
        @$persen=$bappp[$per][$uni]*100/$bapp[$per][$uni];
        $stream.="<td align=right nowrap>".(isset($bapp[$per][$uni])? $bapp[$per][$uni]: 0)." (".number_format(fixnan($persen))."%)</td>";
        @$persen=$bkmp[$per][$uni]*100/$bkm[$per][$uni];
        $stream.="<td align=right nowrap>".(isset($bkm[$per][$uni])? $bkm[$per][$uni]: 0)." (".number_format(fixnan($persen))."%)</td>";
if(!empty($anak))foreach($anak as $data){
//    $stream.="<td align=center>".$data."</td>";
        $tamtud='';
        if(@$tutup[$per][$data]=='1')$tamtud='closed';
        if(@$tutup[$per][$data]=='0')$tamtud='__active';
        $stream.="<td>".$tamtud."</td>";
        @$persen=$gudangp[$per][$data]*100/$gudang[$per][$data];
        $stream.="<td align=right nowrap>".@$gudang[$per][$data]." (".@number_format($persen)."%)</td>";
        
}
        $stream.="</tr>";
        $tamper=false;
    }        
}
    $stream.="</tbody>
    <tfoot>
    </tfoot>		 
    </table>
";

if($tipe=='excel'){
	$nop = "Periode_akuntansi_".$param['periode'].".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("excel", $stream);
	$xls->headers($nop);
	echo $xls->buildFile();
}else{
	echo $stream;
}


    
?>

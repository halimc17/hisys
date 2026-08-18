<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_GET['pt'];
$gudang=$_GET['gudang'];
$tanggal1=$_GET['tanggal1'];
$tanggal2=$_GET['tanggal2'];
$akundari=$_GET['akundari'];

//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];

//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namapt=strtoupper($bar->namaorganisasi);
}
//ambil namagudang
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."'";
$namagudang='';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namagudang=strtoupper($bar->namaorganisasi);
}

//ambil saldo awal
if($gudang==''){
    $str="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
    $wheregudang='';
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
	$wheregudang.="'".strtoupper($bar->kodeorganisasi)."',";
    }
    $wheregudang="and a.kodeorg in (".substr($wheregudang,0,-1).") ";
}else{
    $wheregudang="and a.kodeorg = '".$gudang."' ";
}
#ambil saldo awal supplier
$str="select sum(a.debet-a.kredit) as sawal,a.noakun, b.namaakun,a.kodesupplier,c.namasupplier from ".$dbname.".keu_jurnaldt_vw a
      left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
      left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
      where a.tanggal<'".$tanggal1."'  and a.noakun = '".$akundari."' ".$wheregudang." group by a.kodesupplier";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if(abs($bar->sawal)>10){        
        if($bar->kodesupplier==''){
            $saldoawal['lain']=$bar->sawal;
            $supplier['lain']='lain';        
            $qodes['lain']='lain';
        }else{
            $saldoawal[$bar->kodesupplier]=$bar->sawal;
            $supplier[$bar->kodesupplier]=$bar->namasupplier;        
            $qodes[$bar->kodesupplier]=$bar->kodesupplier;
        }
    }
}

// ambil data
$isidata=array();
$str="select a.*, c.namasupplier from ".$dbname.".keu_jurnaldt_vw a 
    left join ".$dbname.".log_5supplier c on a.kodesupplier = c.supplierid
    where a.tanggal >= '".$tanggal1."' and a.tanggal <= '".$tanggal2."' and a.noakun = '".$akundari."' ".$wheregudang." order by a.kodesupplier, a.tanggal";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $qwe=$bar->nojurnal.$bar->noakun.$bar->nourut;
    if($bar->kodesupplier==''){
        $supplier['lain']='lain';        
        $isidata[$qwe][kodes]='lain';              
        $qodes['lain']='lain';
    }else{
        $supplier[$bar->kodesupplier]=$bar->namasupplier;        
        $isidata[$qwe][kodes]=$bar->kodesupplier;
        $qodes[$bar->kodesupplier]=$bar->kodesupplier;
    }
    
    $isidata[$qwe][nojur]=$bar->nojurnal;
    $isidata[$qwe][tangg]=$bar->tanggal;
    $isidata[$qwe][noaku]=$bar->noakun;
    $isidata[$qwe][keter]=$bar->keterangan;
    $isidata[$qwe][debet]=$bar->debet;
    $isidata[$qwe][kredi]=$bar->kredit;
    $isidata[$qwe][noref]=$bar->noreferensi;
}
// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun where level = '5' and noakun = '2111101'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
}

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    $sort_kodes[] = $key['kodes'];
    $sort_tangg[] = $key['tangg'];
    $sort_debet[] = $key['debet'];
    $sort_nojur[] = $key['nojur'];
}

// sort
if(!empty($isidata))array_multisort($sort_kodes, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($qodes))asort($qodes);

$stream=strtoupper($_SESSION['lang']['laporanbukubesar']." ".$_SESSION['lang']['hutang'])." : ".$namapt." ".$namagudang."<br>".
        strtoupper($_SESSION['lang']['tanggal'])." : ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)."<br>
    <table border=1>
    <thead>
    <tr bgcolor='#dedede'>
        <td align=center>".$_SESSION['lang']['nomor']."</td>
        <td align=center>".$_SESSION['lang']['nojurnal']."</td>
        <td align=center>".$_SESSION['lang']['tanggal']."</td>
        <td align=center>".$_SESSION['lang']['noakun']."</td>
        <td align=center>".$_SESSION['lang']['kodesupplier']."</td>
        <td align=center>".$_SESSION['lang']['keterangan']."</td>
        <td align=center>".$_SESSION['lang']['debet']."</td>
        <td align=center>".$_SESSION['lang']['kredit']."</td>
        <td align=center>".$_SESSION['lang']['saldo']."</td>
        <td align=center>".$_SESSION['lang']['noreferensi']."</td>
    </tr>  
    </thead>
    <tbody id=container>";
 //tampil data
$no=0;
$grandsalwal=$grandtotaldebet=$grandtotalkredit=0;
// tampilin daftar akun
if(!empty($qodes))foreach($qodes as $kyodes){
    $subsalwal=$saldoawal[$kyodes];
    $totaldebet=0;
    $totalkredit=0;
    $subsalak=$subsalwal;
    $salwal=$subsalwal;
    $grandsalwal+=$subsalwal;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=4></td>";
        $stream.="<td>".$kyodes."</td>";
        $stream.="<td colspan=3>".$supplier[$kyodes]."</td>";
        $stream.="<td align=right>".number_format($salwal,2)."</td>";
        $stream.="<td></td>";
    $stream.="</tr>";
// tampilin jurnal daftar akun    
    if(!empty($isidata))foreach($isidata as $baris)
    {
        if($baris[kodes]==$kyodes){ 
            $no+=1;
            $stream.="<tr>";
            $stream.="<td>".$no."</td>";
            $stream.="<td>".substr($baris[nojur],14,8)."</td>";
//            $stream.="<td>".$baris[nojur]."</td>";
            $stream.="<td>".$baris[tangg]."</td>";
            $stream.="<td>".$baris[noaku]."</td>";
            $stream.="<td>".$baris[kodes]."</td>";
            $stream.="<td>".$baris[keter]."</td>";
            $stream.="<td align=right>".number_format($baris[debet],2)."</td>";
            $totaldebet+=$baris[debet];
            $grandtotaldebet+=$baris[debet];
            $stream.="<td align=right>".number_format($baris[kredi],2)."</td>";
            $totalkredit+=$baris[kredi];
            $grandtotalkredit+=$baris[kredi];
            $salwal=$salwal+($baris[debet])-($baris[kredi]);
            $stream.="<td align=right>".number_format($salwal,2)."</td>";
            $stream.="<td>".$baris[noref]."</td>";
            $stream.="</tr>";
            $subsalak=$salwal;
        }
    } 
// subtotal    
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=6>SubTotal</td>";
//        $stream.="<td align=right>".number_format($subsalwal)."</td>";
        $stream.="<td align=right>".number_format($totaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($totalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($subsalak,2)."</td>";
        $stream.="<td align=right></td>";
     $stream.="</tr>";
}

// total
    $grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
    $stream.="<tr bgcolor='#dedede'>";
        $stream.="<td align=right colspan=6>GrandTotal</td>";
//        $stream.="<td align=right>".number_format($grandsalwal)."</td>";
        $stream.="<td align=right>".number_format($grandtotaldebet,2)."</td>";
        $stream.="<td align=right>".number_format($grandtotalkredit,2)."</td>";
        $stream.="<td align=right>".number_format($grandsalak,2)."</td>";
        $stream.="<td align=right></td>";
     $stream.="</tr>";

$stream.="</tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>";
$stream.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
$qwe=date("YmdHms");
$nop_="Laporan_BukuBesar_Hutang_".$pt.$gudang." ".$qwe;
if(strlen($stream)>0)
{
     $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
     gzwrite($gztralala, $stream);
     gzclose($gztralala);
     echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
}    
?>
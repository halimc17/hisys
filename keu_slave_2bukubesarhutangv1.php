<?php
// file creator: dhyaz jan 15, 2014

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pt=$_POST['pt'];
$gudang=$_POST['gudang'];
$tanggal1=$_POST['tanggal1'];
$tanggal2=$_POST['tanggal2'];
$akundari=$_POST['akundari'];
//$akunsampai=$_POST['akunsampai'];

//check, one-two
if($tanggal1==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
if($tanggal2==''){
    echo "WARNING: silakan mengisi tanggal."; exit;
}
//balik tanggal
$qwe=explode("-",$tanggal1);
$tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
$qwe=explode("-",$tanggal2);
$tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];

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

// kamus nama akun
$str="select noakun,namaakun from ".$dbname.".keu_5akun where level = '5' and noakun = '2111101'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $namaakun[$bar->noakun]=$bar->namaakun;
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
        @$supplier['lain']='lain';        
        @$isidata[$qwe][kodes]='lain';              
        @$qodes['lain']='lain';
    }else{
        @$supplier[$bar->kodesupplier]=$bar->namasupplier;        
        @$isidata[$qwe][kodes]=$bar->kodesupplier;
        @$qodes[$bar->kodesupplier]=$bar->kodesupplier;
    }
    
    @$isidata[$qwe][nojur]=$bar->nojurnal;
    @$isidata[$qwe][tangg]=$bar->tanggal;
    @$isidata[$qwe][noaku]=$bar->noakun;
    @$isidata[$qwe][keter]=$bar->keterangan;
    @$isidata[$qwe][debet]=$bar->debet;
    @$isidata[$qwe][kredi]=$bar->kredit;
}

if(!empty($isidata)) foreach($isidata as $c=>$key) {
    @$sort_kodes[] = $key['kodes'];
    @$sort_tangg[] = $key['tangg'];
    @$sort_debet[] = $key['debet'];
    @$sort_nojur[] = $key['nojur'];
}

// sort
if(!empty($isidata))array_multisort($sort_kodes, SORT_ASC, $sort_tangg, SORT_ASC, $sort_debet, SORT_DESC, $sort_nojur, SORT_ASC, $isidata);
if(!empty($qodes))asort($qodes);

$no=0;
$grandsalwal=$grandtotaldebet=$grandtotalkredit=0;
// tampilin daftar akun
if(!empty($qodes))foreach($qodes as $kyodes){
    @$subsalwal=$saldoawal[$kyodes];
    @$totaldebet=0;
    @$totalkredit=0;
    @$subsalak=$subsalwal;
    @$salwal=$subsalwal;
    @$grandsalwal+=$subsalwal;
    echo"<tr class=rowcontent>";
        echo"<td align=right colspan=4></td>";
        echo"<td>".$kyodes."</td>";
        echo"<td colspan=3>".$supplier[$kyodes]."</td>";
        echo"<td align=right>".number_format($salwal,2)."</td>";
    echo"</tr>";
// tampilin jurnal daftar akun    
    if(!empty($isidata))foreach($isidata as $baris)
    {
        if(@$baris[kodes]==$kyodes){
            @$no+=1;
            echo"<tr class=rowcontent>"; 
            echo"<td style='width:40px;' align=center>".$no."</td>";
            echo"<td style='width:100px;'>".@substr($baris[nojur],14,8)."</td>";
            echo"<td style='width:80px;' align=center>".@tanggalnormal($baris[tangg])."</td>";
            echo"<td style='width:100px;' align=center>".@$baris[noaku]."</td>";
            echo"<td style='width:100px;'>".@$baris[kodes]."</td>";
            echo"<td style='width:250px;'>".@$baris[keter]."</td>";
            echo"<td align=right style='width:100px;'>".@number_format($baris[debet],2)."</td>";
            @$totaldebet+=$baris[debet];
            @$grandtotaldebet+=$baris[debet];
            echo"<td align=right style='width:100px;'>".@number_format($baris[kredi],2)."</td>";
            @$totalkredit+=$baris[kredi];
            @$grandtotalkredit+=$baris[kredi];
            @$salwal=$salwal+($baris[debet])-($baris[kredi]);
            echo"<td align=right style='width:100px;'>".@number_format($salwal,2)."</td>";
            echo"</tr>";
            @$subsalak=$salwal;
        }
    } 
// subtotal    
    echo"<tr class=rowtitle>";
        echo"<td align=right colspan=6><b>Sub Total</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($totaldebet,2)."</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($totalkredit,2)."</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($subsalak,2)."</b></td>";
     echo"</tr>";
}

// total
    @$grandsalak=$grandsalwal+$grandtotaldebet-$grandtotalkredit;
    echo"<tr class=rowtitle>";
        echo"<td align=right colspan=6><b>Grand Total</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($grandtotaldebet,2)."</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($grandtotalkredit,2)."</b></td>";
        echo"<td align=right style='width:100px;'><b>".@number_format($grandsalak,2)."</b></td>";
     echo"</tr>";
?>     
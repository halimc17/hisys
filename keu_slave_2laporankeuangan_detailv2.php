<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$pt=$_POST['pt'];
$unit=$_POST['unit']; //kebun
$periode=$_POST['periode'];
$nourut=$_POST['nourut'];
$tipe=$_POST['tipe'];
$darimana=$_POST['darimana'];
$kodelaporan='LABARUGI V2';

$qwe=explode('-',$periode);
$tahun=$qwe[0];
$bulan=$qwe[1];

//ambil namapt
$namaptw=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
if($pt==''){
        $optPt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
        $namapt=$namaptw[$optPt[$unit]];
}else{
        $namapt=$namaptw[$pt];
}

$akun=makeOption($dbname,'keu_5akun','noakun,namaakun');

## CLOSING STOCK
$arrtemp=array();
if($nourut=='2101'){
	$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut = '2103' order by nourut,noakun";
	$res=fetchdata($str);
	foreach($res as $val){
		$listakunx[$val['noakun']]=$val['noakun'];		
	}
	
	$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw where left(tanggal,4)='".$tahun."' and left(tanggal,7)<='".$periode."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in ('".implode("','",$listakunx)."')";
	$res=fetchdata($str);
	foreach($res as $val){
		$perx=substr($val['tanggal'],0,7);
		$arrtemp[$val['noakun']]['sd']+=$val['jumlah'];
		$arrtemp[$val['noakun']][$perx]=$arrtemp[$val['noakun']]['sd'];	
	}
}

// ambil akun
$str="select nourut, noakun from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' and nourut = '".$nourut."'
    order by nourut,noakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $listakun[$bar->noakun]=$bar->noakun;
}

// ambil transaksi
$str="select tanggal, noakun, jumlah, kodeorg from ".$dbname.".keu_jurnaldt_vw 
            where left(tanggal,4)='".$tahun."' and left(tanggal,7)<='".$periode."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi like '%".$unit."%') and noakun in ('".implode("','",$listakun)."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kali=1;
    if(substr($bar['noakun'],0,1)=='5'){
        $kali=(-1);
    }
    if($nourut=='3011'){ // sabinus: ini tanda nya harus nya minus pak
        $kali=(-1);
    }

    $perx=substr($bar['tanggal'],0,7);

    if(in_array($bar['noakun'], $listakun)){
        $data[$bar['noakun']][$perx]+=($kali*$bar['jumlah']);

        if($perx<=$periode){
            // echo "</br>".$perx." ".substr($bar['tanggal'],0,7);
            $data[$bar['noakun']]['sd']+=($kali*$bar['jumlah']);
        }

    }
	if($nourut=='2103'){
		$data[$bar['noakun']][$perx]=$data[$bar['noakun']]['sd'];
	}
}

if(count($arrtemp) > 0){
	foreach($arrtemp as $key=>$val){
		if(substr($periode,5,2)!='01'){
			if($key=='6610301'){
				$data['6610101'][$periode]=abs($val[periodelalu($periode)]);
			}
			if($key=='6610302'){
				$data['6610102'][$periode]=abs($val[periodelalu($periode)]);
			}
		}
	}
}

if($darimana=='lg1'){
$stream="<table class=sortable border=0 cellspacing=0 width=100%>";
if(!empty($listakun))foreach($listakun as $akunnya){
	if((round($data[$akunnya][$periode],2)==0)and(round($data[$akunnya]['sd'],2)==0))continue;
    $stream.="<tr class=rowheader>
        <td style='width:10px'></td>
        <td style='width:10px'></td>
        <td>".$akunnya." - ".$akun[$akunnya]."</td>
        ";
        $stream.="<td style='width:120px' align=right>".number_format($data[$akunnya][$periode])."</td>";                
    $stream.="</tr>";              
}
$stream.="</table>";   
}else{
$stream="<table class=sortable border=0 cellspacing=0 width=100%>";
if(!empty($listakun))foreach($listakun as $akunnya){
	if((round($data[$akunnya][$periode],2)==0)and(round($data[$akunnya]['sd'],2)==0))continue;
    $stream.="<tr class=rowcontent>
        <td style='width:10px'></td>
        <td style='width:10px'></td>
        <td>".$akunnya." - ".$akun[$akunnya]."</td>
        ";
        $stream.="<td style='width:120px' align=right>".number_format($data[$akunnya][$periode])."</td>";                
        $stream.="<td style='width:120px' align=right>".number_format($data[$akunnya]['sd'])."</td>
    </tr>";              
}
$stream.="</table>";   	
}

echo $stream;
?>
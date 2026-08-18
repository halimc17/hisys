<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$per=checkPostGet('per','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$spekbrg=makeOption($dbname,'log_5photobarang','kodebarang,spesifikasi');


$str="select * from ".$dbname.".pabrik_5criticalparts where unit='".$unit."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){ 
	$arrstation[$bar['station']]=$bar['station'];
	$arrbarang[$bar['kodebarang']]=$bar['kodebarang'];
	$databarang[$bar['station']][$bar['kodebarang']]=$bar['kodebarang'];
}

$str="select * from ".$dbname.".log_5saldobulanan where periode='".$per."' and kodegudang like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$saldoqty[$bar['kodebarang']]+=$bar['saldoakhirqty'];
	$harga[$bar['kodebarang']]=$bar['hargarata'];
}

$str="select * from ".$dbname.".log_5masterbarang ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmbrg[$bar['kodebarang']]=$bar['namabarang'];
	$satbrg[$bar['kodebarang']]=$bar['satuan'];
	$minstok[$bar['kodebarang']]=$bar['minstok'];
}

$border=' border=0';
if($proses=='excel'){
	$border=' border=1';
}

$stream="";
//  style=width:300%
$stream.="<table cellspacing=1 class=sortable cellpadding=1 ".$border.">";
$stream.="<thead>";

$stream.="<tr>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['station']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td>";
	$stream.="<td align=center colspan=2 align=center>".$_SESSION['lang']['jumlah']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['harga']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['total']."</td>";
	$stream.="<td align=center rowspan=2 align=center>".$_SESSION['lang']['persentase']."</td>";
$stream.="</tr>";
$stream.="<tr>";
	$stream.="<td align=center align=center>".$_SESSION['lang']['minstok']."</td>";
	$stream.="<td align=center align=center>".$_SESSION['lang']['stok']."</td>";
$stream.="</tr>";
$stream.="</thead>";

########################################################################################
#############tampilkan data
########################################################################################

	
foreach($arrstation as $station){
	@$no+=1;
	$stream.="<tr class=rowcontent>";	
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$nmorg[$station]."</td>";
		$stream.="<td align=left colspan=8></td>";
	$stream.="</tr>";	
	foreach($arrbarang as $barang){
		if(@$databarang[$station][$barang]!=''){
			@$nolist+=1;
			$bgcolor='';
			$title='';
			$class=' class=rowcontent';
			if(@$saldoqty[$barang]<$minstok[$barang]){
				$bgcolor=' bgcolor=red';
				$title=' title="Stok gudang dibawah minimum stok" ';
			}
			
			#bentuk persentase dari stok gudang/stok min
			@$persentase[$barang]=$saldoqty[$barang]/$minstok[$barang]*100;
			if($persentase[$barang]>=100){
				$persentase[$barang]=100;
			}
			
			
			$stream.="<tr  class=rowcontent>";	
				$stream.="<td align=left>".$nolist."</td>";
				$stream.="<td align=left></td>";
				$stream.="<td align=left>[".$barang."] ".$nmbrg[$barang]."</td>";
				$stream.="<td align=left>".@$spekbrg[$barang]."</td>";
				$stream.="<td align=right ".$bgcolor." ".$title.">".@number_format($minstok[$barang],2)."</td>";
				$stream.="<td align=right ".$bgcolor." ".$title.">".@number_format($saldoqty[$barang],2)."</td>";
				$stream.="<td align=left>".$satbrg[$barang]."</td>";
				$stream.="<td align=right>".@number_format($harga[$barang],2)."</td>";
					@$total[$barang]=$saldoqty[$barang]*$harga[$barang];
				$stream.="<td align=right>".@number_format($total[$barang],2)."</td>";
				
				$stream.="<td align=right>".@number_format($persentase[$barang],2)."</td>";
			$stream.="</tr>";	
			@$gtotal+=$total[$barang];
			@$gtotalpersentase+=$persentase[$barang];
		}
	}
}


$stream.="<tr  class=rowcontent>";	
	$stream.="<td align=right colspan=8><b>".$_SESSION['lang']['total']."</b></td>";
	$stream.="<td align=right><b>".@number_format($gtotal,2)."</b></td>";
	$stream.="<td align=right><b>".@number_format($gtotalpersentase/$nolist,2)."%</b></td>";
$stream.="</tr>";	
	

$stream.="</table>";
$stream.="<tbody></table>";
switch($proses){
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="CRITICAL_PARTS".$unit;
        if(strlen($stream)>0)
        {
                if ($handle = opendir('tempExcel')) {
                        while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                        }
                        }	
                        closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream))
                {
                        echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                        exit;
                }
                else
                {
                        echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                }
                fclose($handle);
        }     
        break;	
}
?>
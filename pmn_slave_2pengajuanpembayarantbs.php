<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php'); 

$proses = checkPostGet('proses','');
$pt = checkPostGet('pt','');
$tanggal = tanggalsystem(checkPostGet('tanggal',''));

$tgllk = substr($tanggal,0,4)."-".substr($tanggal,4,2)."-".substr($tanggal,6,2);

/*

//Get Supplier RAMP
$str = "select a.kodesupplier as koderamp, b.namasupplier, b.nodo, a.beratmasuk, a.beratkeluar, a.potongan, a.netto as terima, a.harga, a.beban_pajak, a.rupiahpajak, a.persenpajak from ".$dbname.".pmn_penerimaantbsramp a 
left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid
where a.kodeorg = '".$pt."' and a.datein like '".$tgllk."%'";
exit("Error:$str");
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $arrsup[$bar['koderamp']]['kode'] = $bar['koderamp'];
    $arrsup[$bar['koderamp']]['nama'] = $bar['namasupplier'];
    $arrsup[$bar['koderamp']]['bank'] = "";
    $arrsup[$bar['koderamp']]['rekening'] = "";
    $arrsup[$bar['koderamp']]['an'] = "";
    $arrsup[$bar['koderamp']]['nodo'] = $bar['nodo'];
    $arrsup[$bar['koderamp']]['rp'] = $bar['harga'];
	
	$rpnetto = ($bar['beratmasuk']-$bar['beratkeluar']-round($bar['potongan'])) * $bar['harga'];
	$rpgrossup = $rpnetto * (100/(100-$bar['persenpajak']));
	
	if($bar['beban_pajak']=='1')
	{
		$arrsup[$bar['koderamp']]['grossup'] += $rpgrossup;		
		$arrsup[$bar['koderamp']]['pph22'] += $rpgrossup - $rpnetto;		
		$arrsup[$bar['koderamp']]['total'] += ($rpgrossup - ($rpgrossup - $rpnetto));
	}
	else
	{		
		$arrsup[$bar['koderamp']]['grossup'] += 0;		
		$arrsup[$bar['koderamp']]['pph22'] += ($rpnetto * $bar['persenpajak'] / 100);		
		$arrsup[$bar['koderamp']]['total'] += ($rpnetto - (($rpnetto * $bar['persenpajak'] / 100)));
	}
    $arrsup[$bar['koderamp']]['jumlah'] += (($bar['beratmasuk']-$bar['beratkeluar']-round($bar['potongan']))*$bar['harga']);
    $arrsup[$bar['koderamp']]['terima'] += ($bar['beratmasuk']-$bar['beratkeluar']-round($bar['potongan']));
    $arrsup[$bar['koderamp']]['subsidiangkut'] += 0;
}
*/

//Get Supplier TBS
/*
$str = "select a.kodesupplier as kodesupplier, b.namasupplier, b.bank, b.rekening, b.an, b.nodo, a.total_terima as terima, 
a.harga_perkg, a.beban_pajak, a.rupiahpajak, a.persenpajak, a.subsidi from ".$dbname.".keu_persediaantbs_vw a 
left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid 
where a.kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and a.tanggal='".$tanggal."'";
*/

$str = "select a.kodesupplier as kodesupplier, b.namasupplier, a.total_terima as terima, 
a.harga_perkg, a.beban_pajak, a.rupiahpajak, a.persenpajak, a.subsidi from ".$dbname.".keu_persediaantbs_vw a 
left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid 
where a.kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and a.tanggal='".$tanggal."'";
// exit("Error:$str");
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
    $arrsup[$bar['kodesupplier']]['kode'] = $bar['kodesupplier'];
    $arrsup[$bar['kodesupplier']]['nama'] = $bar['namasupplier'];
    // $arrsup[$bar['kodesupplier']]['bank'] = $bar['bank'];
    // $arrsup[$bar['kodesupplier']]['rekening'] = $bar['rekening'];
    // $arrsup[$bar['kodesupplier']]['an'] = $bar['an'];
    // $arrsup[$bar['kodesupplier']]['nodo'] = $bar['nodo'];
    $arrsup[$bar['kodesupplier']]['rp'] = $bar['harga_perkg'];
	
	$rpsubsidi = $bar['subsidi'] * $bar['terima'];
	$rpnetto = ($bar['terima']*$bar['harga_perkg']) + $rpsubsidi;
	$rpgrossup = $rpnetto * (100/(100-$bar['persenpajak']));
	
	if($bar['beban_pajak']=='1')
	{
		$arrsup[$bar['kodesupplier']]['grossup'] += $rpgrossup;		
		$arrsup[$bar['kodesupplier']]['pph22'] += $rpgrossup - $rpnetto;		
		$arrsup[$bar['kodesupplier']]['total'] += ($rpgrossup - ($rpgrossup - $rpnetto));
	}
	else
	{		
		$arrsup[$bar['kodesupplier']]['grossup'] += 0;		
		$arrsup[$bar['kodesupplier']]['pph22'] += ($rpnetto * $bar['persenpajak'] / 100);
		$arrsup[$bar['kodesupplier']]['total'] += ($rpnetto - ($rpnetto * $bar['persenpajak'] / 100));
	}
    $arrsup[$bar['kodesupplier']]['jumlah'] += ($bar['terima']*$bar['harga_perkg']);
    $arrsup[$bar['kodesupplier']]['terima'] += $bar['terima'];
    $arrsup[$bar['kodesupplier']]['subsidiangkut'] += $rpsubsidi;
}

if($proses=='preview')
{
	$border = 0;
}
else
{
	$border = 1;
}

$stream.=" <table class=sortable cellspacing=1 cellpadding=3 border=".$border.">
    <thead>
	<tr class=rowheader>
		<td rowspan=2 style='text-align:center'>No.</td>
		<td rowspan=2 style='text-align:center'>".$_SESSION['lang']['tanggal']."</td>
		<td rowspan=2 style='text-align:center'>".$_SESSION['lang']['namasupplier']."</td>
		
             
		<td rowspan=2 style='text-align:center'>Netto</td>
		<td colspan=2 style='text-align:center'>Harga TBS</td>
		<td rowspan=2 style='text-align:center'>Subsidi Angkut</td>
		<td rowspan=2 style='text-align:center'>Gross Up</td>
		<td rowspan=2 style='text-align:center'>Pph 22</td>
		<td rowspan=2 style='text-align:center'>Total Pembayaran</td>
	</tr>
	
    </thead><tbody>";/*
			<td rowspan=2 style='text-align:center'>No. Do</td>   
	<td colspan=3 style='text-align:center'>Rekening Pembayaran</td>
	
	<tr class=rowheader>
		<td style='text-align:center'>Bank / Cabang</td>
		<td style='text-align:center'>No. Rek</td>
		<td style='text-align:center'>Atas Nama</td>
		<td style='text-align:center'>Rp/Kg</td>
		<td style='text-align:center'>Jumlah</td>
	</tr>
	*/

$no = 0;
if(isset($arrsup))
foreach($arrsup as $key=>$val)
{
	$rp = ($val['jumlah']/$val['terima']);
	$jumlah = $val['jumlah'];
	// $total = $jumlah + $val['pph22'];
	$no++;
	$stream.="<tr class=rowcontent>";
	$stream.="<td style='text-align:right'>".$no."</td>";
	$stream.="<td>".tanggalnormal($tanggal)."</td>";
	$stream.="<td>".$val['nama']."</td>";
	// $stream.="<td>".$val['bank']."</td>";
	// $stream.="<td>".$val['rekening']."</td>";
	// $stream.="<td>".$val['an']."</td>";
	// $stream.="<td>".$val['nodo']."</td>";
	$stream.="<td style='text-align:right'>".number_format($val['terima'])."</td>";
	$stream.="<td style='text-align:right'>".number_format($rp)."</td>";
	$stream.="<td style='text-align:right'>".number_format($jumlah)."</td>";
	$stream.="<td style='text-align:right'>".($val['subsidiangkut']==0?'-':number_format($val['subsidiangkut']))."</td>";
	$stream.="<td style='text-align:right'>".($val['grossup']==0?'-':number_format($val['grossup']))."</td>";
	$stream.="<td style='text-align:right'>".number_format($val['pph22'])."</td>";
	$stream.="<td style='text-align:right'>".number_format($val['total'])."</td>";
	$stream.="</tr>";
	
	$totnetto += $val['terima'];
	$tothargatbs += $jumlah;
	$totsubsidiangkut += $val['subsidiangkut'];
	$totgrossup += $val['grossup'];
	$totpph += $val['pph22'];
	$total += $val['total'];
}	

$stream.="<tr class=rowcontent>";
$stream.="<td style='text-align:center' colspan=3><b>TOTAL</b></td>";

$stream.="<td style='text-align:right'>".number_format($totnetto)."</td>";
$stream.="<td style='text-align:right'></td>";
$stream.="<td style='text-align:right'>".number_format($tothargatbs)."</td>";
$stream.="<td style='text-align:right'>".number_format($totsubsidiangkut)."</td>";
$stream.="<td style='text-align:right'>".number_format($totgrossup)."</td>";
$stream.="<td style='text-align:right'>".number_format($totpph)."</td>";
$stream.="<td style='text-align:right'>".number_format($total)."</td>";
$stream.="</tr>";
	
$stream.="</body>
</table>";
	
if($proses=='preview')
{
	echo $stream;
}
else
{
	
}

// // kamus harga
// $sOrg="select pabrik,tanggal,supplier,hargab,hargas,hargak from ".$dbname.".pmn_hargatbsharian
    // where pabrik like '".$pabrik0."%' and supplier like '".$supplier0."%' and tanggal between '".$tgl01."' and '".$tgl02."'
    // ";
// $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
// $qOrg->setFetchMode(PDO::FETCH_ASSOC);
// while($rOrg=$qOrg->fetch())
// {
    // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['']=0;
    // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['L']=$rOrg['hargab'];
    // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['M']=$rOrg['hargas'];
    // $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['S']=$rOrg['hargak'];
// }        

// //echo "<pre>";
// //print_r($kamusharga);
// //echo "</pre>";

// $ssupplier="select distinct kodetimbangan,namasupplier from ".$dbname.".log_5supplier 
    // where kodetimbangan IS NOT NULL and kodetimbangan like '1%' order by namasupplier";
// $qsupplier=$owlPDO->query($ssupplier) or die(print " Gagal: ".PDOException::getMessage());
// $qsupplier->setFetchMode(PDO::FETCH_ASSOC);
// while($rsupplier=$qsupplier->fetch())
// {
    // $supplier[$rsupplier['kodetimbangan']]=$rsupplier['namasupplier'];
// }

// $stream='';
// $border=0;
// if($proses=='excel'){
    // $border=1;
    // $stream.=$_SESSION['lang']['pabrik']." : ".$pabrik0."<br>";
    // $stream.=$_SESSION['lang']['supplier']." : ".$supplier[$supplier0]."<br>";
    // $stream.=$_SESSION['lang']['tanggal']." : ".tanggalnormal($tgl01)." - ".tanggalnormal($tgl02)."<br>";
// }
// $stream.=" <table class=sortable cellspacing=1 border=".$border.">
    // <thead>
        // <tr class=rowheader>
            // <td>No.</td>
            // <td>".$_SESSION['lang']['tanggal']."</td>
            // <td>".$_SESSION['lang']['namasupplier']."</td>
            // <td>".$_SESSION['lang']['noTiket']."</td>
            // <td>".$_SESSION['lang']['kendaraan']."</td>                
            // <td>".$_SESSION['lang']['beratBersih']."</td>
            // <td>".$_SESSION['lang']['potongankg']."</td>
            // <td>".$_SESSION['lang']['beratnormal']."</td>
            // <td>".$_SESSION['lang']['kriteria']."</td>
            // <td>".$_SESSION['lang']['harga']."/kg</td>
            // <td>".$_SESSION['lang']['tot_harga']."/kg</td>
	// </tr>
    // </thead><tbody>";
// $no=1;
// $total=0;
// $sql="select tanggal,kodecustomer,notransaksi,nokendaraan,beratbersih,kgpotsortasi,kriteriabuah,millcode from ".$dbname.".pabrik_timbangan 
    // where millcode like '".$pabrik0."%' and kodecustomer like '1%' and kodecustomer like '".$supplier0."%' 
        // and tanggal between '".$tgl01." 00:00:00' and '".$tgl02." 23:59:59' and kodeorg = ''
    // order by tanggal asc";
// $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
// $row=owlBaris($query);
// $query->setFetchMode(PDO::FETCH_ASSOC);
// if($row>0){
    // while($res=$query->fetch()){
        // $stream.="<tr class=rowcontent>";
            // $stream.="<td align=right>".$no."</td>";
                // $tanggal=substr($res['tanggal'],0,10);
            // if($proses=='preview')$stream.="<td>".tanggalnormal($tanggal)."</td>";
            // if($proses=='excel')$stream.="<td>".$tanggal."</td>";
            // $stream.="<td>".$supplier[$res['kodecustomer']]."</td>";
            // $stream.="<td>".$res['notransaksi']."</td>";
            // $stream.="<td>".$res['nokendaraan']."</td>";
            // $stream.="<td align=right>".number_format($res['beratbersih'],0)."</td>";
            // $stream.="<td align=right>".number_format($res['kgpotsortasi'],0)."</td>";
                // $beratnormal=$res['beratbersih']-$res['kgpotsortasi'];
            // $stream.="<td align=right>".number_format($beratnormal,0)."</td>";
            // $stream.="<td>".$res['kriteriabuah']."</td>";
                // $hargaperkg=$kamusharga[$res['millcode']][$tanggal][$res['kodecustomer']][$res['kriteriabuah']];
            // $stream.="<td align=right>".number_format($hargaperkg,0)."</td>";
                // $totalharga=$beratnormal*$hargaperkg;
            // $stream.="<td align=right>".number_format($totalharga,0)."</td>";
        // $stream.="</tr>";
        // $no+=1;
        // $totalbb+=$res['beratbersih'];
        // $totalpp+=$res['kgpotsortasi'];
        // $totalnn+=$beratnormal;
        // $totaltt+=$totalharga;
    // }
    // $stream.="<tr class=rowcontent>";
        // $stream.="<td align=center colspan=5>Total</td>";
        // $stream.="<td align=right>".number_format($totalbb,0)."</td>";
        // $stream.="<td align=right>".number_format($totalpp,0)."</td>";
        // $stream.="<td align=right>".number_format($totalnn,0)."</td>";
        // $stream.="<td colspan=2></td>";
        // $stream.="<td align=right>".number_format($totaltt,0)."</td>";
    // $stream.="</tr>";
    // $no+=1;
// }
// else
// {
    // $stream.="<tr class=rowcontent align=center><td colspan=11>Not Found</td></tr>";
// }
// $stream.="</tbody></table>";

// switch($proses)
// {
    // case'preview':
        // echo $stream;
    // break;
    // case'excel':

        // $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
        // $nop_="Pembelian TBS ".$pabrik0." ".$supplier0." ".$tgl01."-".$tgl02;
        // if(strlen($stream)>0)
        // {
            // if ($handle = opendir('tempExcel')) {
                // while (false !== ($file = readdir($handle))) {
                    // if ($file != "." && $file != "..") {
                        // @unlink('tempExcel/'.$file);
                    // }
                // }	
                // closedir($handle);
            // }
            // $handle=fopen("tempExcel/".$nop_.".xls",'w');
            // if(!fwrite($handle,$stream))
            // {
                // echo "<script language=javascript1.2>
                // parent.window.alert('Can't convert to excel format');
                // </script>";
                // exit;
            // }
            // else
            // {
                // echo "<script language=javascript1.2>
                // window.location='tempExcel/".$nop_.".xls';
                // </script>";
            // }
            // fclose($handle);
        // }
    // break;
    // default:
    // break;
// }

?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php'); 

$proses=$_GET['proses'];
$_POST['pabrik0']==''?$pabrik0=$_GET['pabrik0']:$pabrik0=$_POST['pabrik0'];
$_POST['supplier0']==''?$supplier0=$_GET['supplier0']:$supplier0=$_POST['supplier0'];
$_POST['tgl01']==''?$tgl01=$_GET['tgl01']:$tgl01=$_POST['tgl01'];
$_POST['tgl02']==''?$tgl02=$_GET['tgl02']:$tgl02=$_POST['tgl02'];
$tgl=explode('-',$tgl01);
$tgl01=$tgl[2].'-'.$tgl[1].'-'.$tgl[0];
$tgl=explode('-',$tgl02);
$tgl02=$tgl[2].'-'.$tgl[1].'-'.$tgl[0];

if($tgl01=='--'||$tgl02=='--'){
    echo"error: Please choose dates.";
    exit;
}

// kamus harga
$sOrg="select pabrik,tanggal,supplier,hargab,hargas,hargak from ".$dbname.".pmn_hargatbsharian
    where pabrik like '".$pabrik0."%' and supplier like '".$supplier0."%' and tanggal between '".$tgl01."' and '".$tgl02."'
    ";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
    $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['']=0;
    $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['L']=$rOrg['hargab'];
    $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['M']=$rOrg['hargas'];
    $kamusharga[$rOrg['pabrik']][$rOrg['tanggal']][$rOrg['supplier']]['S']=$rOrg['hargak'];
}        

//echo "<pre>";
//print_r($kamusharga);
//echo "</pre>";

$ssupplier="select distinct kodetimbangan,namasupplier from ".$dbname.".log_5supplier 
    where kodetimbangan IS NOT NULL and kodetimbangan like '1%' order by namasupplier";
$qsupplier=$owlPDO->query($ssupplier) or die(print " Gagal: ".PDOException::getMessage());
$qsupplier->setFetchMode(PDO::FETCH_ASSOC);
while($rsupplier=$qsupplier->fetch())
{
    $supplier[$rsupplier['kodetimbangan']]=$rsupplier['namasupplier'];
}

$stream='';
$border=0;
if($proses=='excel'){
    $border=1;
    $stream.=$_SESSION['lang']['pabrik']." : ".$pabrik0."<br>";
    $stream.=$_SESSION['lang']['supplier']." : ".$supplier[$supplier0]."<br>";
    $stream.=$_SESSION['lang']['tanggal']." : ".tanggalnormal($tgl01)." - ".tanggalnormal($tgl02)."<br>";
}
$stream.=" <table class=sortable cellspacing=1 border=".$border.">
    <thead>
        <tr class=rowheader>
            <td>No.</td>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>".$_SESSION['lang']['namasupplier']."</td>
            <td>".$_SESSION['lang']['noTiket']."</td>
            <td>".$_SESSION['lang']['kendaraan']."</td>                
            <td>".$_SESSION['lang']['beratBersih']."</td>
            <td>".$_SESSION['lang']['potongankg']."</td>
            <td>".$_SESSION['lang']['beratnormal']."</td>
            <td>".$_SESSION['lang']['kriteria']."</td>
            <td>".$_SESSION['lang']['harga']."/kg</td>
            <td>".$_SESSION['lang']['tot_harga']."/kg</td>
	</tr>
    </thead><tbody>";
$no=1;
$total=0;
$sql="select tanggal,kodecustomer,notransaksi,nokendaraan,beratbersih,kgpotsortasi,kriteriabuah,millcode from ".$dbname.".pabrik_timbangan 
    where millcode like '".$pabrik0."%' and kodecustomer like '1%' and kodecustomer like '".$supplier0."%' 
        and tanggal between '".$tgl01." 00:00:00' and '".$tgl02." 23:59:59' and kodeorg = ''
    order by tanggal asc";
$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($query);
$query->setFetchMode(PDO::FETCH_ASSOC);
if($row>0){
    while($res=$query->fetch()){
        $stream.="<tr class=rowcontent>";
            $stream.="<td align=right>".$no."</td>";
                $tanggal=substr($res['tanggal'],0,10);
            if($proses=='preview')$stream.="<td>".tanggalnormal($tanggal)."</td>";
            if($proses=='excel')$stream.="<td>".$tanggal."</td>";
            $stream.="<td>".$supplier[$res['kodecustomer']]."</td>";
            $stream.="<td>".$res['notransaksi']."</td>";
            $stream.="<td>".$res['nokendaraan']."</td>";
            $stream.="<td align=right>".number_format($res['beratbersih'],0)."</td>";
            $stream.="<td align=right>".number_format($res['kgpotsortasi'],0)."</td>";
                $beratnormal=$res['beratbersih']-$res['kgpotsortasi'];
            $stream.="<td align=right>".number_format($beratnormal,0)."</td>";
            $stream.="<td>".$res['kriteriabuah']."</td>";
                $hargaperkg=$kamusharga[$res['millcode']][$tanggal][$res['kodecustomer']][$res['kriteriabuah']];
            $stream.="<td align=right>".number_format($hargaperkg,0)."</td>";
                $totalharga=$beratnormal*$hargaperkg;
            $stream.="<td align=right>".number_format($totalharga,0)."</td>";
        $stream.="</tr>";
        $no+=1;
        $totalbb+=$res['beratbersih'];
        $totalpp+=$res['kgpotsortasi'];
        $totalnn+=$beratnormal;
        $totaltt+=$totalharga;
    }
    $stream.="<tr class=rowcontent>";
        $stream.="<td align=center colspan=5>Total</td>";
        $stream.="<td align=right>".number_format($totalbb,0)."</td>";
        $stream.="<td align=right>".number_format($totalpp,0)."</td>";
        $stream.="<td align=right>".number_format($totalnn,0)."</td>";
        $stream.="<td colspan=2></td>";
        $stream.="<td align=right>".number_format($totaltt,0)."</td>";
    $stream.="</tr>";
    $no+=1;
}
else
{
    $stream.="<tr class=rowcontent align=center><td colspan=11>Not Found</td></tr>";
}
$stream.="</tbody></table>";

switch($proses)
{
    case'preview':
        echo $stream;
    break;
    case'excel':

        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
        $nop_="Pembelian TBS ".$pabrik0." ".$supplier0." ".$tgl01."-".$tgl02;
        if(strlen($stream)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
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
    default:
    break;
}

?>
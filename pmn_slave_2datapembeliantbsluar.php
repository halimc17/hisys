<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php'); 

$proses = checkPostGet('proses','');
$pt = checkPostGet('pt','');
$periode = checkPostGet('periode','');

    $query = "select periode,tanggalmulai,tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' group by periode";
    $res=fetchData($query);
    $minggu=0;
    $listTgl=array();
    $batasAtas=intval(substr($res[0]['tanggalsampai'],-2,2));
    for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
        if($tglawal<10){
            $strTgl=$res[0]['periode']."-0".$tglawal;
        }else{
            $strTgl=$res[0]['periode']."-".$tglawal;
        }
        if(date('D', strtotime($strTgl))=='Sun'){

            $listTgl[]=date('d-m-Y', strtotime($strTgl))."<br> (Minggu)";
        }
        else{

            $listTgl[]=date('d-m-Y', strtotime($strTgl));

        }
    }

    $pabrik='';
    $query = "select kodeorganisasi from ".$dbname.".organisasi where tipe='Pabrik' and induk='".$pt."' ";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        if($pabrik=='')
        {
            $pabrik="'".$val['kodeorganisasi']."'";
        }
        else
        {
            $pabrik.=",'".$val['kodeorganisasi']."'";
        }
    }

    if($pabrik=='')
    {
        exit("Warning : This Company doesn't have mill, Please choose another one");
    }
    

    $arrharga=array();
    $query = "select harga,tanggal from ".$dbname.".pmn_hargapasar where pasar='Disbun Jambi' ";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        $arrharga[date('d-m-Y', strtotime($val['tanggal']))]=$val['harga'];
    }

    $arrdata=array();
    $arrdatax=array();
    $query = "select c.namasupplier,c.supplierid,sum(a.beratbersih) as beratbersih,sum(a.kgpotsortasi) as kgpotsortasi,a.tanggal,d.harga as hargabeli from ".$dbname.".pabrik_timbangan a 
              left join ".$dbname.".log_5suptimbangan b on a.kodecustomer = b.kodetimbangan 
              left join ".$dbname.".log_5supplier c on b.supplierid = c.supplierid  
              left join ".$dbname.".pmn_hargabelitbs d on b.supplierid = d.supplierid  and substr(a.tanggal,1,10)=d.tanggal 
              where a.tanggal like '".$periode."%' and millcode in (".$pabrik.") and intex=0 and kodebarang='40000003' group by substr(a.tanggal, 1,10),a.kodecustomer order by c.namasupplier,a.tanggal asc";
    $res=fetchData($query);
    //echo $query;
    foreach($res as $key=>$val)
    {
        if(!empty($val['supplierid']) and !empty($val['namasupplier'])){
        @$arrdata['do'][$val['supplierid']]= $val['namasupplier'];
        @$arrdatax[$val['supplierid']][date('d-m-Y', strtotime($val['tanggal']))]['net1']+= $val['beratbersih'];
        @$arrdatax[$val['supplierid']][date('d-m-Y', strtotime($val['tanggal']))]['net2']+= ($val['beratbersih']-$val['kgpotsortasi']);
        @$arrdatax[$val['supplierid']][date('d-m-Y', strtotime($val['tanggal']))]['sortasi']+= (($val['kgpotsortasi']/$val['beratbersih'])*100);
            if($val['hargabeli']!=0 or $val['hargabeli']!=''){
                $arrdatax[$val['supplierid']][date('d-m-Y', strtotime($val['tanggal']))]['hargabeli']= $val['hargabeli'];
            }
            else
            {
                $query = "select harga from ".$dbname.".pmn_hargabelitbs where supplierid='".$val['supplierid']."' and tanggal < '".$val['tanggal']."' and harga <> '0' ";
                $res=fetchData($query);
                $arrdatax[$val['supplierid']][date('d-m-Y', strtotime($val['tanggal']))]['hargabeli']= $res[0]['harga'];
            }
            // echo $hargabeli;
            // echo "<br>";
        }
    }

    if(empty($arrdata['do']))
    {
        exit("Data Empty");
    }

    $arrdatay=array();
    $query = "select oer,oerpk,tanggal,tbsdiolah,tbsdiolahnetto from ".$dbname.".pabrik_produksi where kodeorg in (".$pabrik.") ";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['oerpercent']=(($val['oer']/$val['tbsdiolah'])*100);
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['oerpercent2']=(($val['oer']/$val['tbsdiolahnetto'])*100);
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['kerpercent']=(($val['oerpk']/$val['tbsdiolah'])*100);
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['kerpercent2']=(($val['oerpk']/$val['tbsdiolahnetto'])*100);
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['tbsdiolah']=$val['tbsdiolah'];
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['oerkg']=$val['oer'];
        @$arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['oerpk']=$val['oerpk'];
    }


    $arrdatax2=array();
    $query = "select sum(beratbersih) as beratbersih,sum(kgpotsortasi) as kgpotsortasi,tanggal from ".$dbname.".pabrik_timbangan 
              where tanggal like '".$periode."%' and millcode in (".$pabrik.") and intex=1 and kodebarang='40000003' group by substr(tanggal,1,10) order by tanggal asc";
    $res=fetchData($query);
    //echo $query;
    foreach($res as $key=>$val)
    {
        @$arrdatax2[date('d-m-Y', strtotime($val['tanggal']))]['net1']= $val['beratbersih'];
        @$arrdatax2[date('d-m-Y', strtotime($val['tanggal']))]['net2']= ($val['beratbersih']-$val['kgpotsortasi']);
        @$arrdatax2[date('d-m-Y', strtotime($val['tanggal']))]['sortasi']= (($val['kgpotsortasi']/$val['beratbersih'])*100);
    }

    $query="select sum(kuantitas) as kuantitas, tanggal from ".$dbname.".pabrik_masukkeluartangki where kodeorg in (".$pabrik.")";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        $arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['kuantitascpo']=$val['kuantitas'];
    }

    $query="select sum(kernelquantity) as kuantitas, tanggal from ".$dbname.".pabrik_masukkeluartangki where kodeorg in (".$pabrik.")";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        $arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['kuantitaskernel']=$val['kuantitas'];
    }

    $query="select sum(beratbersih) as beratbersih,tanggal from ".$dbname.".pabrik_timbangan where millcode in (".$pabrik.") and kodebarang = '40000001' group by substr(tanggal, 1,10)";
    $res=fetchData($query);
    //echo $query;
    foreach($res as $key=>$val)
    {
        $arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['jualcpo']=$val['beratbersih'];
    }

    $query="select sum(beratbersih) as beratbersih,tanggal from ".$dbname.".pabrik_timbangan where millcode in (".$pabrik.") and kodebarang = '40000002' group by substr(tanggal, 1,10)";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        $arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['jualkernel']=$val['beratbersih'];
    }

    $query="select sisa, tanggal from ".$dbname.".pabrik_stokbarang where kodeorg in (".$pabrik.")";
    $res=fetchData($query);
    foreach($res as $key=>$val)
    {
        $arrdatay[date('d-m-Y', strtotime($val['tanggal']))]['sisacangkang']=$val['sisa'];
    }

if($proses=='preview')
{
	$border = 0;
}
else
{
	$border = 1;
}


$stream.=" <table class=sortable cellspacing=1 cellpadding=3 border=".$border." width=100%>
    <thead>
	<tr class=rowheader>
		<td rowspan=2 style='text-align:center'>Tanggal</td>
        <td rowspan=2 style='text-align:center'>Harga Disbun</td>";
foreach($arrdata['do'] as $key=>$val)
{
    $stream.="<td colspan=8 style='text-align:center'>".$val."</td>";
}

$stream.="<td style='text-align:center'>Total</td>";
$stream.="<td colspan=3 style='text-align:center'>TBS ".$pt." + MD</td>";
$stream.="<td rowspan=2 style='text-align:center'>Total Terima TBS ".$pt." + Luar</td>";
$stream.="<td rowspan=2 style='text-align:center'>Total TBS Diproses (Kg)</td>";
$stream.="<td colspan=2 style='text-align:center'>Produksi (Kg)</td>";
$stream.="<td colspan=2 style='text-align:center'>OER Total (%)</td>";
$stream.="<td colspan=2 style='text-align:center'>KER Total (%)</td>";
$stream.="<td colspan=2 style='text-align:center'>Nett Stock (Kg,Belum Dijual)</td>";
$stream.="<td colspan=2 style='text-align:center'>Penjualan (Kg)</td>";
$stream.="<td rowspan=2 style='text-align:center'>Nett Stock (Kg,Belum Dijual) Cangkang</td>";

$stream.="</tr>";
$stream.="<tr class=rowheader>";
for ($i=0; $i < count($arrdata['do']); $i++) { 
    $stream.="<td style='text-align:center'>Netto 1 (Kg)</td>";
    $stream.="<td style='text-align:center'>Netto 2 (Kg)</td>";
    $stream.="<td style='text-align:center'>Sortase %</td>";
    $stream.="<td style='text-align:center'>OER Luar %</td>";
    $stream.="<td style='text-align:center'>OER ".$pt." %</td>";
    $stream.="<td style='text-align:center'>BEP Actual</td>";
    $stream.="<td style='text-align:center'>Harga Beli</td>";
    $stream.="<td style='text-align:center'>Keterangan</td>";
}

$stream.="<td style='text-align:center'>TBS Luar (Kg)</td>"; 
$stream.="<td style='text-align:center'>Netto 1 (Kg)</td>"; 
$stream.="<td style='text-align:center'>Netto 2 (Kg)</td>"; 
$stream.="<td style='text-align:center'>Sortase %</td>"; 
$stream.="<td style='text-align:center'>CPO</td>"; 
$stream.="<td style='text-align:center'>PK</td>"; 
$stream.="<td style='text-align:center'>Netto 1 </td>"; 
$stream.="<td style='text-align:center'>Netto 2 </td>"; 
$stream.="<td style='text-align:center'>Netto 1 </td>"; 
$stream.="<td style='text-align:center'>Netto 2 </td>"; 
$stream.="<td style='text-align:center'>CPO</td>"; 
$stream.="<td style='text-align:center'>PK</td>"; 
$stream.="<td style='text-align:center'>CPO</td>"; 
$stream.="<td style='text-align:center'>PK</td>"; 
$stream.="</tr>";
$stream.="</thead><tbody>";/*
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


foreach($listTgl as $key=>$val)
{
    $totaltbsluar=0;
	$stream.="<tr class=rowcontent>";
	$stream.="<td style='text-align:center'>".$val."</td>";
    $stream.="<td style='text-align:right'>".$arrharga[$val]."</td>";
    foreach($arrdata['do'] as $key2=>$val2)
    {
        $totaltbsluar+=$arrdatax[$key2][$val]['net1'];
        $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax[$key2][$val]['net1'],2)."</td>";
        $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax[$key2][$val]['net2'],2)."</td>";
        $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax[$key2][$val]['sortasi'],2)."%</td>";
        $stream.="<td style='text-align:right'></td>";
        $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['oerpercent'],2)."%</td>";
        $stream.="<td style='text-align:right'></td>";
        $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax[$key2][$val]['hargabeli'],2)."</td>";
        $stream.="<td style='text-align:right'></td>";
    }
    $stream.="<td style='text-align:right'>".hidezerodecimal($totaltbsluar,2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax2[$val]['net1'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax2[$val]['net2'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatax2[$val]['sortasi'],2)."%</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal(($arrdatax2[$val]['net1']+$totaltbsluar),2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['tbsdiolah'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['oerkg'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['oerpk'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['oerpercent'],2)."%</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['oerpercent2'],2)."%</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['kerpercent'],2)."%</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['kerpercent2'],2)."%</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal(($arrdatay[$val]['oerkg']+$arrdatay[$val]['kuantitascpo']-$arrdatay[$val]['jualcpo']),2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal(($arrdatay[$val]['oerpk']+$arrdatay[$val]['kuantitaskernel']-$arrdatay[$val]['jualkernel']),2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['jualcpo'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['jualkernel'],2)."</td>";
    $stream.="<td style='text-align:right'>".hidezerodecimal($arrdatay[$val]['sisacangkang'],2)."</td>";
    $stream.="</tr>";
}	

/*$stream.="<tr class=rowcontent>";
$stream.="<td style='text-align:center' colspan=3><b>TOTAL</b></td>";

$stream.="<td style='text-align:right'>".number_format($totnetto)."</td>";
$stream.="<td style='text-align:right'></td>";
$stream.="<td style='text-align:right'>".number_format($tothargatbs)."</td>";
$stream.="<td style='text-align:right'>".number_format($totsubsidiangkut)."</td>";
$stream.="<td style='text-align:right'>".number_format($totgrossup)."</td>";
$stream.="<td style='text-align:right'>".number_format($totpph)."</td>";
$stream.="<td style='text-align:right'>".number_format($total)."</td>";
$stream.="</tr>";*/
	
$stream.="</body>
</table>";
	

switch($proses)
{
    case'preview':
    //print_r($arrdatax2);
        echo $stream;
    break;
    case'excel':

        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			
        $nop_="Data Pembelian TBS Luar ".$tgl01."-".$tgl02;
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
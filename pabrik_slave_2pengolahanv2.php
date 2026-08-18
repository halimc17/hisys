<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(!empty($_GET['kdPabrik']))
{
    $_POST=$_GET;
}

$proses = checkPostGet('proses','');
$kdPabrik = checkPostGet('kdPabrik','');
$periode = checkPostGet('periode','');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optSat=makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$whr="periode='".$periode."' and kodegudang like '".$kdPabrik."%'";
$optHrg=makeOption($dbname, 'log_5saldobulanan', 'kodebarang,hargarata',$whr);
$optNmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$brd=0;
$bg="";
if($proses=='excel')
{
    $brd=1;
    $bg="align=center bgcolor=#DEDEDE";
}
if($proses!='getPeriode')
{
$cols = "a.tanggal as tanggal,sum(jamstagnasi) as jamstag,sum(jamdinasbruto) as jmdinbruto,sum(jumlahlori) as jumlori,sum(a.tbsdiolah) as tbsdiolah,sum(oer) as oer,sum(oerpk) as oerpk,nopengolahan";

$where = "a.kodeorg='".$kdPabrik."' and left(a.tanggal,7)='".$periode."'";
// $query = selectQuery($dbname,'pabrik_pengolahan',$cols,$where)." group by tanggal";
$query="select distinct ".$cols." from ".$dbname.".pabrik_pengolahan a left join ".$dbname.".pabrik_produksi b 
         on (a.kodeorg=b.kodeorg and a.tanggal=b.tanggal) where ".$where." group by a.tanggal";
//exit("Error".$query);
$tmpRes = fetchData($query);
if(empty($tmpRes)) {
    echo 'Warning : Data empty';
    exit;
}


$dtKdBrg=$dtJmlh=$dtTgl=$jmlhRow=array(); 
//data oalh
foreach($tmpRes as $lstData =>$dtIsi)
{
    $dtTgl[$dtIsi['tanggal']]=isset($dtIsi['tanggal'])?$dtIsi['tanggal']:'';
    $dtJmstag[$dtIsi['tanggal']]=$dtIsi['jamstag'];
    $dtJmBruto[$dtIsi['tanggal']]=$dtIsi['jmdinbruto'];
    $dtJmLori[$dtIsi['tanggal']]=$dtIsi['jumlori'];
    $dtJmTbsDiolah[$dtIsi['tanggal']]=$dtIsi['tbsdiolah'];
    $dtJmoer[$dtIsi['tanggal']]=$dtIsi['oer'];
    $dtJmoerpk[$dtIsi['tanggal']]=$dtIsi['oerpk'];
}
//data mesin
$sData="select b.*,a.tanggal   from ".$dbname.".pabrik_pengolahanmesin b 
        left join ".$dbname.".pabrik_pengolahan a on b.nopengolahan=a.nopengolahan
        where ".$where."  ";
$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while($rData=$qData->fetch()){
    if(!isset($drer) or $drer!=$rData['nopengolahan'])
    {
        $drer=$rData['nopengolahan'];
        $derRow=1;
    }
    $dtStation[$rData['tanggal']][$derRow]=$rData['kodeorg'];
    $dtMesin[$rData['tanggal']][$derRow]=$rData['tahuntanam'];
    $dtJamOperasi[$rData['tanggal']][$derRow]=$rData['jammulai'];
    $dtJamSlsi[$rData['tanggal']][$derRow]=$rData['jamselesai'];
    $dtJamStag[$rData['tanggal']][$derRow]=$rData['jamstagnasi'];
    $dtKet[$rData['tanggal']][$derRow]=$rData['keterangan'];
    $dtprestasi[$rData['tanggal']][$derRow]=$rData['prestasi'];
    $jmlhRow[$rData['tanggal']]=$derRow;
    $derRow+=1;
}




//data barang
$sData2="select b.*,a.tanggal   from ".$dbname.".pabrik_pengolahan_barang b 
        left join ".$dbname.".pabrik_pengolahan a on b.nopengolahan=a.nopengolahan
        where ".$where."  ";
//exit("Error".$sData2);
//echo $sData2;
$qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
$qData2->setFetchMode(PDO::FETCH_ASSOC);

while($rData2=$qData2->fetch()){
    if($drer!=$rData2['tanggal'])
    {
        $drer=$rData2['tanggal'];
        $derRow=1;
    }
    $dtKdBrg[$rData2['tanggal']][$derRow]=isset($rData2['kodebarang'])?$rData2['kodebarang']:'';
    $dtJmlh[$rData2['tanggal']][$derRow]=$rData2['jumlah'];
    $jmlhRow2[$rData2['tanggal']]=$derRow;
    $derRow+=1;
}
    $tab.="<div class='table-scroll'><table cellpadding=1 border=".$brd." class=sortable>";
    $tab.="<thead><tr>";
    $tab.="<th colspan=7  align=center ".$bg.">Summary Processing</th><th colspan=7 align=center  ".$bg.">Detail Processing</th>";
    $tab.="<th colspan=5  align=center ".$bg.">Detail Material Usage</th></tr>";
    $tab.="<tr>";
    #1#
    $tab.="<th ".$bg." align=center width=80px>".$_SESSION['lang']['tanggal']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jamstagnasi']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jamoperasional']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jumlahlori']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['tbsdiolah']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['cpo']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['oerpk']."</th>";
    #7#
    #8#
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['station']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['mesin']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jammulai']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jamselesai']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jamstagnasi']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['keterangan']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['prestasi']."</th>";
    #14#
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['namabarang']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['jumlah']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['satuan']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['hargasatuan']."</th>";
    $tab.="<th ".$bg." align=center >".$_SESSION['lang']['total']."</th>";
    $tab.="</tr></thead><tbody>";
    
 
    foreach($dtTgl as $lstTgl=>$dataTgl)
    {
        $jmlhRow[$dataTgl]=isset($jmlhRow[$dataTgl])?$jmlhRow[$dataTgl]:'0';
      if($jmlhRow[$dataTgl]==1)
      {
          $aer=1;
            
            $tab.="<tr class=rowcontent>";
            #1#
            $tab.="<td align=center>".tanggalnormal($dataTgl)."</td>";
            $tab.="<td align=right>".@number_format($dtJmstag[$dataTgl],2)."</td>";
            $tab.="<td align=right>".@number_format($dtJmBruto[$dataTgl],2)."</td>";
            $tab.="<td align=right>".@number_format($dtJmLori[$dataTgl],2)."</td>";
            $tab.="<td align=right>".number_format($dtJmTbsDiolah[$dataTgl],0)."</td>";
            $tab.="<td align=right>".@number_format($dtJmoer[$dataTgl])."</td>";
            $tab.="<td align=right>".@number_format($dtJmoerpk[$dataTgl])."</td>";
            #7#
            #8#
            $tab.="<td>".$optNmorg[$dtStation[$dataTgl][$aer]]."</td>";
            $tab.="<td>".$optNmorg[$dtMesin[$dataTgl][$aer]]."</td>";
            $tab.="<td align=right>".$dtJamOperasi[$dataTgl][$aer]."</td>";
            $tab.="<td align=right>".$dtJamSlsi[$dataTgl][$aer]."</td>";
            $tab.="<td align=right>".$dtJamStag[$dataTgl][$aer]."</td>";
            $tab.="<td>".$dtKet[$dataTgl][$aer]."</td>";
            $tab.="<td>".$dtprestasi[$dataTgl][$aer]."</td>";
            #14#
            
			setIt($dtKdBrg[$dataTgl][$aer],'');
			setIt($optNmBrg[$dtKdBrg[$dataTgl][$aer]],'');
			setIt($optSat[$dtKdBrg[$dataTgl][$aer]],'');
			setIt($optHrg[$dtKdBrg[$dataTgl][$aer]],0);
			setIt($dtJmlh[$dataTgl][$aer],'');
            $tab.="<td>".$optNmBrg[$dtKdBrg[$dataTgl][$aer]]."</td>";
            $tab.="<td align=right>".$dtJmlh[$dataTgl][$aer]."</td>";
            $tab.="<td>".$optSat[$dtKdBrg[$dataTgl][$aer]]."</td>";
            $tab.="<td align=right>".number_format($optHrg[$dtKdBrg[$dataTgl][$aer]],2)."</td>";
            $totalHrg[$dtKdBrg[$dataTgl][$aer]]=$dtJmlh[$dataTgl][$aer]*$optHrg[$dtKdBrg[$dataTgl][$aer]];
            $tab.="<td align=right>".number_format($totalHrg[$dtKdBrg[$dataTgl][$aer]],2)."</td>";
            $tab.="</tr>";
      }
      else
      {
          for($aer=1;$aer<=$jmlhRow[$dataTgl];$aer++)
          {
            $tab.="<tr class=rowcontent>";
            #1#
            if($aer==1)
            {
                $tab.="<td>".$dataTgl."</td>";
                $tab.="<td align=right>".$dtJmstag[$dataTgl]."</td>";
                $tab.="<td align=right>".$dtJmBruto[$dataTgl]."</td>";
                $tab.="<td align=right>".$dtJmLori[$dataTgl]."</td>";
                $tab.="<td align=right>".number_format($dtJmTbsDiolah[$dataTgl],0)."</td>";
                $tab.="<td align=right>".$dtJmoer[$dataTgl]."</td>";
                $tab.="<td align=right>".$dtJmoerpk[$dataTgl]."</td>";
            }
            else if($aer==2)
            {
                $tab.="<td rowspan='".($jmlhRow[$dataTgl]-1)."' colspan=7>&nbsp;</td>";
            }
            #7#
            #8#
            $tab.="<td>".$optNmorg[$dtStation[$dataTgl][$aer]]."</td>";
            $tab.="<td>".$optNmorg[$dtMesin[$dataTgl][$aer]]."</td>";
            $tab.="<td align=right>".$dtJamOperasi[$dataTgl][$aer]."</td>";
            $tab.="<td align=right>".$dtJamSlsi[$dataTgl][$aer]."</td>";
            $tab.="<td align=right>".$dtJamStag[$dataTgl][$aer]."</td>";
            $tab.="<td>".$dtKet[$dataTgl][$aer]."</td>";
            $tab.="<td>".$dtprestasi[$dataTgl][$aer]."</td>";
            #14#
            $dtKdBrg[$dataTgl][$aer]=isset($dtKdBrg[$dataTgl][$aer])?$dtKdBrg[$dataTgl][$aer]:'';
            $dtJmlh[$dataTgl][$aer]=isset($dtJmlh[$dataTgl][$aer])?$dtJmlh[$dataTgl][$aer]:'';
            $optNmBrg[$dtKdBrg[$dataTgl][$aer]]=isset($optNmBrg[$dtKdBrg[$dataTgl][$aer]])?$optNmBrg[$dtKdBrg[$dataTgl][$aer]]:'';
            $optSat[$dtKdBrg[$dataTgl][$aer]]=isset($optSat[$dtKdBrg[$dataTgl][$aer]])?$optSat[$dtKdBrg[$dataTgl][$aer]]:'';
            $optHrg[$dtKdBrg[$dataTgl][$aer]]=isset($optHrg[$dtKdBrg[$dataTgl][$aer]])?$optHrg[$dtKdBrg[$dataTgl][$aer]]:0;
                $tab.="<td>".$optNmBrg[$dtKdBrg[$dataTgl][$aer]]."</td>";
                $tab.="<td align=right>".$dtJmlh[$dataTgl][$aer]."</td>";
                $tab.="<td>".$optSat[$dtKdBrg[$dataTgl][$aer]]."</td>";
                $tab.="<td align=right>".number_format($optHrg[$dtKdBrg[$dataTgl][$aer]],2)."</td>";
                $totalHrg[$dtKdBrg[$dataTgl][$aer]]=$dtJmlh[$dataTgl][$aer]*$optHrg[$dtKdBrg[$dataTgl][$aer]];
                $tab.="<td align=right>".number_format($totalHrg[$dtKdBrg[$dataTgl][$aer]],2)."</td>";
           
           
            $tab.="</tr>";
          }
      }
        
    }
}
switch($proses)
{
	case'preview':
        echo $tab;
	break;
	
	case'excel':
			
        $tab.="</tbody></table></div>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        $nop_="LaporanPengolahan";
        if(strlen($tab)>0)
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
        if(!fwrite($handle,$tab))
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
        case'getPeriode':
        $optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sPeriode="select distinct left(tanggal,7) as periode from ".$dbname.".pabrik_pengolahan 
                   where kodeorg='".$kdPabrik."' order by tanggal desc";
        $qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
        $qPeriode->setFetchMode(PDO::FETCH_ASSOC);
        while($rPeriode=$qPeriode->fetch())
        {
             $optPeriode.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
        }
        echo $optPeriode;
        break;
	default:
	break;
}
?>
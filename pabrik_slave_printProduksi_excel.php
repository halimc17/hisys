<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$tampil=$_GET['tampil'];
$pabrik=$_GET['pabrik'];

if(strlen($periode)==4)
{
	//tahunan
	$str="select sum(tbsmasuk) as tbsmasuk,
		  sum(tbsdiolah) as tbsdiolah,
		  sum(oer)  as oer,
		 
                  
                  sum(ffa*tbsdiolah) as ffa,
		  sum(kadarair*tbsdiolah) as kadarair,
                  sum(kadarkotoran*tbsdiolah) as kadarkotoran,
                    


		  sum(oerpk) as oerpk,
		  avg(ffapk) as ffapk,
		  avg(kadarairpk) as kadarairpk,
		  avg(kadarkotoranpk) as kadarkotoranpk,
		  sum(jumlahpk) as jumlahpk,
		  sum(jumlahck) as jumlahck,
		  sum(jumlahjakos) as jumlahjakos,
		  left(tanggal,7) as perio from ".$dbname.".pabrik_produksi
		  where kodeorg='".$pabrik."' and tanggal like '".$periode."%'
		  group by perio order by perio";  
	//ambil sisa tbs hari ini
        $sisa=0;
	$stsisa="select sisahariini from ".$dbname.".pabrik_produksi 
	          where tanggal like '".$periode."%' order by tanggal desc limit 1";
        $ressisa=$owlPDO->query($stsisa) or die(print " Gagal: ".PDOException::getMessage());
        $ressisa->setFetchMode(PDO::FETCH_OBJ);
        while($barsisa=$ressisa->fetch())
        {
		$sisa=$barsisa->sisahariini;
	}				  
			  
	//ambil tbs sisa sebelumnya
	$stsedia="select sisahariini from ".$dbname.".pabrik_produksi 
	          where tanggal like '".($periode-1)."%' order by tanggal desc limit 1";
        $tbskemarin=0;
        $ressedia=$owlPDO->query($stsedia) or die(print " Gagal: ".PDOException::getMessage());
        $ressedia->setFetchMode(PDO::FETCH_OBJ);
        while($barsedia=$ressedia->fetch())
        { 
		$tbskemarin=$barsedia->sisahariini;
	}
        
        $sum='';
        for($x=1;$x<=12;$x++)
        {
            if(strlen($x)<2)
            {
                $x='0'.$x;
            }
            else
            {
                $x=$x;
            }
            $sum.="sum(olah$x) as olah$x,";
            $sum.="sum(kgcpo$x) as kgcpo$x,";
            $sum.="sum(kgker$x) as kgker$x,";
        }
        
        $iBgtThn="select ".$sum." tahunbudget,sum(kgolah) as kgolah,avg(oerbunch) as oerbunch,avg(oerkernel) as oerkernel
                 from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".substr($periode,0,4)."' and millcode='".$pabrik."' ";
        //echo $iBgtThn;
        $nBgtThn=$owlPDO->query($iBgtThn) or die(print " Gagal: ".PDOException::getMessage());
        $nBgtThn->setFetchMode(PDO::FETCH_ASSOC);
        while($dBgtThn=$nBgtThn->fetch())
        {
            for($i=1;$i<=12;$i++)
            {
                if(strlen($i)<2)
                {
                    $i='0'.$i;
                }
                else
                {
                    $i=$i;
                }
                $olah[$dBgtThn['tahunbudget'].'-'.$i]=$dBgtThn['olah'.$i];
                $kgcpo[$dBgtThn['tahunbudget'].'-'.$i]=$dBgtThn['kgcpo'.$i];
                $kgker[$dBgtThn['tahunbudget'].'-'.$i]=$dBgtThn['kgker'.$i];
            }
        }
	
	$stream='';
        $stream.="
	  <table border=1>
	    <thead>
		  <tr>
		   <td rowspan=2 align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['bulan']."</td>
                   <td rowspan=2 align=center>".$_SESSION['lang']['budget']." TBS (Kg.)</td>      
		   <td rowspan=2 align=center>".$_SESSION['lang']['tersedia']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tbsdiolah']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['sisa']." (Kg.)</td>
                    <td colspan=7 align=center>".$_SESSION['lang']['cpo']."
		   </td>
		   <td colspan=7 align=center>".$_SESSION['lang']['kernel']."
		   </td>	  
		  </tr>  
		  <tr class=rowheader> 
                   <td align=center>".$_SESSION['lang']['budget']."  (Kg)</td>
                   <td align=center>".$_SESSION['lang']['budget']." OER  (Kg)</td>    
		   <td align=center>".$_SESSION['lang']['cpo']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa)(%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
		   
                   <td align=center>".$_SESSION['lang']['budget']."  (Kg)</td>
                   <td align=center>".$_SESSION['lang']['budget']." OER  (Kg)</td> 
		   <td align=center>".$_SESSION['lang']['kernel']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa) (%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
		  </tr>
		</thead>
		<tbody>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
		 $stream.="<tr>
		   <td>".$pabrik."</td>
                   <td>".$bar->perio."</td>   
		   <td align=right>".$olah[$bar->perio]."</td>
                     
		   <td align=right>".number_format($bar->tbsmasuk+$tbskemarin,0,'.',',')."</td>
		   <td align=right>".number_format($bar->tbsdiolah,0,'.',',.')."</td>
		   <td align=right>".number_format($bar->tbsmasuk+$tbskemarin-$bar->tbsdiolah,0,'.',',')."</td>	
                   
                    

                   <td align=right>".number_format($kgcpo[$bar->perio],2)."</td> 
                   <td align=right>".number_format($kgcpo[$bar->perio]/$olah[$bar->perio]*100,2)."</td> 
		   <td align=right>".number_format($bar->oer,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->ffa/$bar->tbsdiolah,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoran/$bar->tbsdiolah,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarair/$bar->tbsdiolah,2,'.',',')."</td>
		   
                   <td align=right>".number_format($kgker[$bar->perio],2)."</td> 
                   <td align=right>".number_format($kgker[$bar->perio]/$olah[$bar->perio]*100,2)."</td> 
                   
		   <td align=right>".number_format($bar->oerpk,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->ffapk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoranpk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarairpk,2,'.',',')."</td>
		  </tr>";
		  $tbskemarin=$bar->tbsmasuk+$tbskemarin-$bar->tbsdiolah;
         }	  
		
      $stream.="
		</tbody>
		<tfoot>
		</tfoot>
	  </table>
	  </fieldset>";
}
else
{
	//bulanan
	$str="select * from ".$dbname.".pabrik_produksi where tanggal like '".$periode."%'
	      and kodeorg='".$pabrik."'
		  order by tanggal desc";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$stream='';
        $stream.="
      <table border=1>
	    <thead>
		  <tr>
		   <td rowspan=2 align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tersedia']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['tbsdiolah']." (Kg.)</td>
		   <td rowspan=2 align=center>".$_SESSION['lang']['sisa']." (Kg.)</td>
		   <td colspan=5 align=center>".$_SESSION['lang']['cpo']."
		   </td>
		   <td colspan=5 align=center>".$_SESSION['lang']['kernel']."
		   </td>	  
		  </tr>  
		  <tr class=rowheader> 
		   <td align=center>".$_SESSION['lang']['cpo']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa)(%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
		   
		   <td align=center>".$_SESSION['lang']['kernel']." (Kg)</td>
		   <td align=center>".$_SESSION['lang']['oer']." (%)</td>
		   <td align=center>(FFa) (%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
		  </tr>
		</thead>
		<tbody>";
       while($bar=$res->fetch())
        {
		$stream.="<tr>
		   <td>".$bar->kodeorg."</td>
		   <td>".tanggalnormal($bar->tanggal)."</td>
		   <td align=right>".number_format($bar->tbsmasuk+$bar->sisatbskemarin,0)."</td>
		   <td align=right>".number_format($bar->tbsdiolah,0)."</td>
		   <td align=right>".number_format($bar->sisahariini,0)."</td>
		   
		   <td align=right>".number_format($bar->oer,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->ffa,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoran,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarair,2,'.',',')."</td>
		   
		   <td align=right>".number_format($bar->oerpk,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->ffapk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoranpk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarairpk,2,'.',',')."</td>
		  </tr>";	
         }	  
		
       $stream.="
		</tbody>
		<tfoot>
		</tfoot>
	  </table>
	  </fieldset>";
}
$nop_="Produksi_".$pabrik."_".$periode;

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
?>
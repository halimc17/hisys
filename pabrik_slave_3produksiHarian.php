<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$periode=$_POST['periode'];
$tampil=$_POST['tampil'];
$pabrik=$_POST['pabrik'];
/*Laporan Produksi pabrik yg bulanan diedit nama menu menjadi “Laporan Produksi Bulanan” dan ditambahkan kolom 
 * Budget Penerimaan TBS, 
 * Budget Prod CPO, 
 * Budget Prod Kernel, 
 * Budget OER CPO, 
 * Budget OER Kernel.*/
if(strlen($periode)==4)
{
	//tahunan
	$str="select sum(tbsmasuk) as tbsmasuk,
		  sum(tbsdiolah) as tbsdiolah,
		  sum(oer)  as oer,
		  sum(ffa*tbsdiolah) as jumkgair,
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
        
        /*$str="select sum(tbsmasuk) as tbsmasuk,
		  sum(tbsdiolah) as tbsdiolah,
		  sum(oer)  as oer,
		  avg(ffa) as ffa,
		  avg(kadarair) as kadarair,
		  avg(kadarkotoran) as kadarkotoran,
		  sum(oerpk) as oerpk,
		  avg(ffapk) as ffapk,
		  avg(kadarairpk) as kadarairpk,
		  avg(kadarkotoranpk) as kadarkotoranpk,
		  sum(jumlahpk) as jumlahpk,
		  sum(jumlahck) as jumlahck,
		  sum(jumlahjakos) as jumlahjakos,
		  left(tanggal,7) as perio from ".$dbname.".pabrik_produksi
		  where kodeorg='".$pabrik."' and tanggal like '".$periode."%'
		  group by perio order by perio";*/
        
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
        $tbskemarin=0;
	$stsedia="select sisahariini from ".$dbname.".pabrik_produksi 
	          where tanggal like '".($periode-1)."%' order by tanggal desc limit 1";
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
       //echo $sum;
        //echo $sumOlah._.$sumKgcpo.__.$sumKgker;
        
        $iBgtThn="select ".$sum." tahunbudget,sum(kgolah) as kgolah,avg(oerbunch) as oerbunch,avg(oerkernel) as oerkernel
                 from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".substr($periode,0,4)."' and millcode='".$pabrik."' ";
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
          
            /*$olah[$dBgtThn['tahunbudget'].'-01']=$dBgtThn['olah01'];
            $olah[$dBgtThn['tahunbudget'].'-02']=$dBgtThn['olah02'];
            $olah[$dBgtThn['tahunbudget'].'-03']=$dBgtThn['olah03'];
            $olah[$dBgtThn['tahunbudget'].'-04']=$dBgtThn['olah04'];
            $olah[$dBgtThn['tahunbudget'].'-05']=$dBgtThn['olah05'];
            $olah[$dBgtThn['tahunbudget'].'-06']=$dBgtThn['olah06'];
            $olah[$dBgtThn['tahunbudget'].'-07']=$dBgtThn['olah07'];
            $olah[$dBgtThn['tahunbudget'].'-08']=$dBgtThn['olah08'];
            $olah[$dBgtThn['tahunbudget'].'-09']=$dBgtThn['olah09'];
            $olah[$dBgtThn['tahunbudget'].'-10']=$dBgtThn['olah10'];
            $olah[$dBgtThn['tahunbudget'].'-11']=$dBgtThn['olah11'];
            $olah[$dBgtThn['tahunbudget'].'-12']=$dBgtThn['olah12'];*/
           
            
            $tbsThn=$dBgtThn['kgolah'];
            $oerCpoThn=$dBgtThn['oerbunch'];
            $oerKerThn=$dBgtThn['oerkernel'];
            //$cpoThn=$dBgtThn['kgcpo'];
            //$kerThn=$dBgtThn['kgkernel'];
         
        }
        
   
        
       
	
	echo "<fieldset><legend>".$_SESSION['lang']['list']."</legend>
	      Graph OER : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Grafics' onclick=grafikProduksi('".$periode."','".$tampil."','".$pabrik."',event)>
              <br>Graph FFA : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Graphics'  onclick=grafikProduksiFfa('".$periode."','".$tampil."','".$pabrik."',event)>
              <br>Graph FFB Processed and Remains : <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Graphics'  onclick=grafikTbs('".$periode."','".$tampil."','".$pabrik."',event)>
              <br>Print PDF : <img src='images/skyblue/pdf.jpg' class=resicon title='PDF' onclick=laporanPDF('".$periode."','".$tampil."','".$pabrik."',event)>
              <br>Print Excel :<img src='images/skyblue/excel.jpg' class=resicon title='Spreadsheet' onclick=laporanEXCEL('".$periode."','".$tampil."','".$pabrik."',event)>     
		
         
	  ".$_SESSION['lang']['periode'].":
	  <table class=sortable cellspacing=1 border=0>
	    <thead>
		  <tr class=rowheader>
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
		   <td align=center>".$_SESSION['lang']['oerpk']." (%)</td>
		   <td align=center>(FFa) (%)</td>
		   <td align=center>".$_SESSION['lang']['kotoran']." (%)</td>
		   <td align=center>".$_SESSION['lang']['kadarair']." (%)</td>
		   
		  </tr>
		</thead>
		<tbody>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {// 
                $olah[$bar->perio] = isset($olah[$bar->perio])? trim($olah[$bar->perio]): '';
                $kgcpo[$bar->perio] = isset($kgcpo[$bar->perio])? trim($kgcpo[$bar->perio]): '';
                $kgker[$bar->perio] = isset($kgker[$bar->perio])? trim($kgker[$bar->perio]): '';
                
		 echo"<tr class=rowcontent>
		   <td>".$pabrik."</td>
                   <td>".$bar->perio."</td>   
		  <td align=right>".number_format((float)$olah[$bar->perio])."</td>
                
		   <td align=right>".number_format($bar->tbsmasuk+$tbskemarin,0,'.',',')."</td>
		   <td align=right>".number_format($bar->tbsdiolah,0,'.',',.')."</td>
		   <td align=right>".number_format($bar->tbsmasuk+$tbskemarin-$bar->tbsdiolah,0,'.',',')."</td>	
                   
                    

                   <td align=right>".number_format((float)$kgcpo[$bar->perio],2)."</td> 
                   <td align=right>".@number_format($kgcpo[$bar->perio]/$olah[$bar->perio]*100,2)."</td> 
		   <td align=right>".number_format($bar->oer,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->jumkgair/$bar->tbsdiolah,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoran/$bar->tbsdiolah,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarair/$bar->tbsdiolah,2,'.',',')."</td>
		   
                   <td align=right>".number_format((float)$kgker[$bar->perio],2)."</td> 
                   <td align=right>".@number_format($kgker[$bar->perio]/$olah[$bar->perio]*100,2)."</td> 
                   
		   <td align=right>".number_format($bar->oerpk,2,'.',',')."</td>
		   <td align=right>".(@number_format($bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
		   <td align=right>".number_format($bar->ffapk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarkotoranpk,2,'.',',')."</td>
		   <td align=right>".number_format($bar->kadarairpk,2,'.',',')."</td>
		  </tr>";
		  $tbskemarin=$bar->tbsmasuk+$tbskemarin-$bar->tbsdiolah;
         }	  
		
       echo"	
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
        
    echo "<fieldset><legend>".$_SESSION['lang']['list']."
	     <img src='images/icons/Basic_set_Png/statistics_16.png' class=resicon title='Grafics'  onclick=grafikProduksi('".$periode."','".$tampil."','".$pabrik."',event)>
		 <img src='images/skyblue/pdf.jpg' class=resicon title='PDF' onclick=laporanPDF('".$periode."','".$tampil."','".$pabrik."',event)>
	    <img src='images/skyblue/excel.jpg' class=resicon title='Spreadsheet' onclick=laporanEXCEL('".$periode."','".$tampil."','".$pabrik."',event)>      
            </legend>
      <table class=sortable cellspacing=1 border=0>
	    <thead>
		  <tr class=rowheader>
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
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
    
                echo"<tr class=rowcontent>
                  <td>".$bar->kodeorg."</td>
                  <td>".tanggalnormal($bar->tanggal)."</td>
                  <td align=right>".number_format($bar->tbsmasuk+$bar->sisatbskemarin,0,'.',',')."</td>
                  <td align=right>".number_format($bar->tbsdiolah,0,'.',',.')."</td>
                  <td align=right>".number_format($bar->sisahariini,0,'.',',')."</td>

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
		
       echo"	
		</tbody>
		<tfoot>
		</tfoot>
	  </table>
	  </fieldset>";
}
?>
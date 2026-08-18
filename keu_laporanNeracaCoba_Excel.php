<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

	$pt=$_GET['pt'];
	$gudang=$_GET['gudang'];
	$periode=$_GET['periode'];
	$tahun1 = substr($periode,0,4);
	$bulan1 = substr($periode,5,2);
	$periode1 = $periode;
    $stream='';
	

	if($gudang=='')
	{
		$str="select a.*,substr(a.kodeorg,1,4) as bussunitcode,b.namaakun,c.induk from ".$dbname.".keu_jurnalsum_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		left join ".$dbname.".organisasi c
		on substr(a.kodeorg,1,4)=c.kodeorganisasi
		where c.induk = '".$pt."' and a.periode='".$periode1."'
		order by a.noakun, a.periode 
		";
	}
	else
	{
		$str="select a.*,substr(a.kodeorg,1,4) as bussunitcode,b.namaakun,c.induk from ".$dbname.".keu_jurnalsum_vw a
		left join ".$dbname.".keu_5akun b
		on a.noakun=b.noakun
		left join ".$dbname.".organisasi c
		on substr(a.kodeorg,1,4)=c.kodeorganisasi
		where c.induk = '".$pt."' and substr(a.kodeorg,1,4) = '".$gudang."' and a.periode='".$periode1."'
		order by a.noakun, a.periode 
		";
	}	
//=================================================

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$res4=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res4->setFetchMode(PDO::FETCH_OBJ);        
	$no=0;
	if(owlBaris($res)<1)
	{
		echo"<tr class=rowcontent><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
	}
	else
	{
		$stream.=$_SESSION['lang']['laporanneracacoba'].": ".$pt." ".$gudang." ".$periode."<br>
		<table border=1>
                    <tr>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
                        </tr>";

    while($bar=$res->fetch())
		{
			$no+=1;
      if($bar->noakun<4000000){

			$periode=date('d-m-Y H:i:s');
			$kodebarang=$bar->kodebarang;
			$namabarang=$bar->namabarang; 
			$kuantitas =$bar->kuan;
			$nojurnal	=$bar->nojurnal;
			$tanggal    =$bar->tanggal;
			$noakun		=$bar->noakun;
			$namaakun	=$bar->namaakun;
			$keterangan =$bar->keterangan;
$bussunitcode	=$bar->bussunitcode; 
                        
                        $sawal=0;
                        $strx="select awal".$bulan1." from ".$dbname.".keu_saldobulanan where 
                              noakun='".$noakun."' and kodeorg='".$bussunitcode."' 
                              and periode='".$tahun1.$bulan1."'";
                         $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                        $resx->setFetchMode(PDO::FETCH_NUM);
                        while($barx=$resx->fetch()){
                        $sawal=$barx[0];	
                        }
                        
                        $debet 		=$bar->debet;
			$kredit		=$bar->kredit;
			$sakhir		=$sawal + $debet - $kredit;      
                        
                        $stream.="<tr>
                          <td>".$noakun."</td>
                          <td>".$namaakun."</td>
                           <td align=right class=firsttd>".number_format($sawal,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($debet,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($kredit,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($sakhir,2,'.','')."</td>
                        </tr>";
                        $tawal+=$sawal;
                        $tdebet+=$debet;
                        $tkredit+=$kredit;   
                        $tsalak+=$sawal+$debet-$kredit;
		}
                        }
                        $stream.="<tr>
                          <td></td>
                          <td></td>
                           <td align=right class=firsttd>".number_format($tawal,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tdebet,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tkredit,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tsalak,2,'.','')."</td>
                        </tr>";                            
	  $stream.="</table>";	
          // atas: kepala 1-3
            $tawal=0;
            $tdebet=0;
            $tkredit=0;
            $tsalak=0;
          
          // bawah: kepala 4-9
		$stream.="<br>
		<table border=1>
                    <tr>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoawal']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['saldoakhir']."</td>
                        </tr>";

    while($bar=$res4->fetch())
		{
			$no+=1;
                        if($bar->noakun>3999999){

			$periode=date('d-m-Y H:i:s');
			$kodebarang=$bar->kodebarang;
			$namabarang=$bar->namabarang; 
			$kuantitas =$bar->kuan;
			$nojurnal	=$bar->nojurnal;
			$tanggal    =$bar->tanggal;
			$noakun		=$bar->noakun;
			$namaakun	=$bar->namaakun;
			$keterangan =$bar->keterangan;
$bussunitcode	=$bar->bussunitcode; 
                        
                        $sawal=0;
                        $strx="select awal".$bulan1." from ".$dbname.".keu_saldobulanan where 
                              noakun='".$noakun."' and kodeorg='".$bussunitcode."' 
                              and periode='".$tahun1.$bulan1."'";
                         $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                        $resx->setFetchMode(PDO::FETCH_NUM);
                        while($barx=$resx->fetch()){
                        $sawal=$barx[0];	
                        }
                        
                        $debet 		=$bar->debet;
			$kredit		=$bar->kredit;
			$sakhir		=$sawal + $debet - $kredit;      
                        
                        $stream.="<tr>
                          <td>".$noakun."</td>
                          <td>".$namaakun."</td>
                           <td align=right class=firsttd>".number_format($sawal,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($debet,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($kredit,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($sakhir,2,'.','')."</td>
                        </tr>";
                        $tawal+=$sawal;
                        $tdebet+=$debet;
                        $tkredit+=$kredit;   
                        $tsalak+=$sawal+$debet-$kredit;
		}
                        }
                        $stream.="<tr>
                          <td></td>
                          <td></td>
                           <td align=right class=firsttd>".number_format($tawal,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tdebet,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tkredit,2,'.','')."</td>
                           <td align=right class=firsttd>".number_format($tsalak,2,'.','')."</td>
                        </tr>";                            
	  $stream.="</table>";	
          }

$nop_="NeracaPercobaan".$gudang.$tahun1.$bulan1;
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
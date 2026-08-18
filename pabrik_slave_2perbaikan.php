<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


//$proses=$_GET['proses'];

$proses = checkPostGet('proses','');
$pabrik = checkPostGet('pabrik','');
$station = checkPostGet('station','');
$tgl1 = tanggalsystemn(checkPostGet('tgl1',''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2',''));
$menu = checkPostGet('menu','');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$stBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$arrPost=array("0"=>"Not Posted","1"=>"Posting");



if($tgl1=='--') {
    $tgl1='';
}
if($tgl2=='--') {
    $tgl2='';
}



if($proses=='excel') {
    $border="border=1";
} else {
    $border="border=0";
}

//bgcolor=#CCCCCC border='1'

  $stream="<div class='table-scroll'><table cellspacing='1' $border class='sortable'>";
      $stream.="<thead><tr class=rowheader>
       
            <th align=center>No</th>
            <th align=center>".$_SESSION['lang']['notransaksi']."</th>
            <th align=center>".$_SESSION['lang']['tanggal']."</th>
            <th align=center>".$_SESSION['lang']['uraiankerusakan']."</th>    
            <th align=center>".$_SESSION['lang']['pabrik']."</th>
            <th align=center>".$_SESSION['lang']['station']."</th>
            <th align=center>".$_SESSION['lang']['mesin']."(".$_SESSION['lang']['kode'].")</th>    
            <th align=center>".$_SESSION['lang']['mesin']."(".$_SESSION['lang']['nama'].")</th>    
            <th align=center>".$_SESSION['lang']['jammulai']."</th> 
            <th align=center>".$_SESSION['lang']['jamselesai']."</th> 
            <th align=center>".$_SESSION['lang']['jumlahjamperbaikan']."</th> 
            <th align=center>".$_SESSION['lang']['kodebarang']."</th> 
            <th align=center>Spareparts Replaced</th> 
            <th align=center>".$_SESSION['lang']['satuan']."</th> 
            <th align=center>".$_SESSION['lang']['jumlah']."</th>
            <th align=center>".$_SESSION['lang']['mekanik']."</th>
            <th align=center>".$_SESSION['lang']['tipeperbaikan']."</th>
            <th align=center>".$_SESSION['lang']['statusketuntasan']."</th>
            <th align=center>".$_SESSION['lang']['hasilkerjad']."</th>";
    if($proses!='excel')
    {
        $stream.="  
                <th align=center>".$_SESSION['lang']['action']."</th>";
    }
    $stream.="        
        </tr></thead>
      <tbody>";
//kgpotsortasi,kodecustomer,beratbersih as netto,substr(tanggal,1,10) as tanggal,(beratbersih/(jumlahtandan1+jumlahtandan2+jumlahtandan3)) as bjr


      
      
$stationTambah="";      
if($station!='')
{
    $stationTambah="and statasiun='".$station."'";
}
      
      
$iList="SELECT * FROM ".$dbname.".pabrik_rawatmesinht where tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah." ";
$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
$nList->setFetchMode(PDO::FETCH_ASSOC);
while($dList=$nList->fetch()){
    $notransaksi[$dList['notransaksi']]=$dList['notransaksi'];
    $kdorg[$dList['notransaksi']]=$dList['pabrik'];
    $tgl[$dList['notransaksi']]=$dList['tanggal'];
    $statasiun[$dList['notransaksi']]=$dList['statasiun'];
    $mesin[$dList['notransaksi']]=$dList['mesin'];
    $kegiatan[$dList['notransaksi']]=$dList['kegiatan'];
    $jammulai[$dList['notransaksi']]=$dList['jammulai'];
    $jamselesai[$dList['notransaksi']]=$dList['jamselesai'];
    $jumlahjamperbaikan[$dList['notransaksi']]=$dList['jumlahjamperbaikan'];
    $statusketuntasan[$dList['notransaksi']]=$dList['statusketuntasan'];
    $tipeperbaikan[$dList['notransaksi']]=$dList['tipeperbaikan'];
    $hasilkerja[$dList['notransaksi']]=$dList['hasilkerja'];
}

$iBarang="select * from ".$dbname.".pabrik_rawatmesindt "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,kodebarang";
$nBarang=$owlPDO->query($iBarang) or die(print " Gagal: ".PDOException::getMessage());
$nBarang->setFetchMode(PDO::FETCH_ASSOC);
while($dBarang=$nBarang->fetch())
{
    $listbarang[$dBarang['kodebarang']]=$dBarang['kodebarang'];
    $barang[$dBarang['notransaksi']][]=$dBarang['kodebarang'];
    $satuanbarang[$dBarang['notransaksi']][]=$dBarang['satuan'];
    $jumlahbarang[$dBarang['notransaksi']][]=$dBarang['jumlah'];
}

#karyawan
$iKar="select * from ".$dbname.".pabrik_rawatmesindt_karyawan "
        . " where notransaksi in (SELECT notransaksi FROM ".$dbname.".pabrik_rawatmesinht where "
        . " tanggal between '".$tgl1."' and '".$tgl2."' and"
        . " pabrik='".$pabrik."' ".$stationTambah.") group by notransaksi,karyawanid";
$nKar=$owlPDO->query($iKar) or die(print " Gagal: ".PDOException::getMessage());
$nKar->setFetchMode(PDO::FETCH_ASSOC);
while($dKar=$nKar->fetch())
{
    $listkar[$dKar['karyawanid']]=$dKar['karyawanid'];
    $kar[$dKar['notransaksi']][]=$dKar['karyawanid'];
}


$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nikKar=  makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$arrTipePerbaikan=array("prev"=>"Preventive Maintenance","kalibrasi"=>"Kalibrasi","project"=>"Project",
    "pabrikasi"=>"Pabrikasi","corrective"=>"Corrective Maintenance","service"=>"Service");

$noList=0;
if(isset($notransaksi)){
	foreach ($notransaksi as $notran)
	{
		$barang[$notran]=isset($barang[$notran])?$barang[$notran]:'';
                $kar[$notran]=isset($kar[$notran])?$kar[$notran]:'';
            $rowspanbarang=@count($barang[$notran]);
            $rowspankar=@count($kar[$notran]);

            if($rowspanbarang>=$rowspankar)
            {
                    $rowspan=$rowspanbarang;
            }
            else
            {
                    $rowspan=$rowspankar;
            }

		$noList+=1;
		$stream.="<tr class=rowcontent>";
                        $nmOrg[$mesin[$notran]]=isset($nmOrg[$mesin[$notran]])?$nmOrg[$mesin[$notran]]:'';
			$stream.="<td align=center valign=top rowspan=".$rowspan.">".$noList."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$notran."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".tanggalnormal($tgl[$notran])."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$kegiatan[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$kdorg[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$statasiun[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$mesin[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$nmOrg[$mesin[$notran]]."</td>";
			
			$stream.="<td valign=top rowspan=".$rowspan.">".$jammulai[$notran]."</td>";
			$stream.="<td valign=top rowspan=".$rowspan.">".$jamselesai[$notran]."</td>";
			$stream.="<td valign=top align=center rowspan=".$rowspan.">".$jumlahjamperbaikan[$notran]."</td>";
			
	  
		  
			if(empty($barang[$notran]) and empty($kar[$notran]))
			{
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				$stream.="<td valign=top  rowspan=".$rowspan."></td>";
				
				$stream.="<td valign=top  rowspan=".$rowspan.">".@$arrTipePerbaikan[$tipeperbaikan[$notran]]."</td>";
				$stream.="<td valign=top  rowspan=".$rowspan.">".$statusketuntasan[$notran]."</td>";
				$stream.="<td valign=top  rowspan=".$rowspan.">".$hasilkerja[$notran]."</td>";
			   
				if($proses!='excel')
				{
					$stream.="<td valign=top rowspan=".$rowspan.">
								<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=detailBarang('".$notran."',event)>
								<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$notran."','','pabrik_slave_perbaikan_pdf',event)\">
							</td>";  
				}
			}
			else
			{
				for($i=0;$i<$rowspan;$i++) 
				{
					if($i>0)
					{
                                            $stream.="<tr class=rowcontent>";
					}
                                        @$kar[$notran][$i]=isset($kar[$notran][$i])?$kar[$notran][$i]:'';
                                        @$nmKar[$kar[$notran][$i]]=isset($nmKar[$kar[$notran][$i]])?$nmKar[$kar[$notran][$i]]:'';
                                        @$barang[$notran][$i]=isset($barang[$notran][$i])?$barang[$notran][$i]:'';
                                        @$nmBrg[$barang[$notran][$i]]=isset($nmBrg[$barang[$notran][$i]])?$nmBrg[$barang[$notran][$i]]:'';
                                        @$satuanbarang[$notran][$i]=isset($satuanbarang[$notran][$i])?$satuanbarang[$notran][$i]:'';
                                        @$jumlahbarang[$notran][$i]=isset($jumlahbarang[$notran][$i])?$jumlahbarang[$notran][$i]:'';
                             
					$stream.="<td valign=top align=right>".$barang[$notran][$i]."</td>";
					$stream.="<td valign=top align=left>".$nmBrg[$barang[$notran][$i]]."</td>";
					$stream.="<td valign=top  align=left>".$satuanbarang[$notran][$i]."</td>";
					$stream.="<td valign=top  align=right>".$jumlahbarang[$notran][$i]."</td>";
					$stream.="<td valign=top  align=left>".$nmKar[$kar[$notran][$i]]."</td>";
					
					
					
					if($i==0)
					{
						$stream.="<td valign=top  rowspan=".$rowspan.">".$arrTipePerbaikan[$tipeperbaikan[$notran]]."</td>";
						$stream.="<td valign=top  rowspan=".$rowspan.">".$statusketuntasan[$notran]."</td>";
						 $stream.="<td valign=top  rowspan=".$rowspan.">".$hasilkerja[$notran]."</td>";
						if($proses!='excel')
						{
							$stream.="<td valign=top rowspan=".$rowspan.">
									<img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblCariNoGudang class=resicon onclick=detailBarang('".$notran."',event)>
									<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$notran."','','pabrik_slave_perbaikan_pdf',event)\">
								</td>"; 
							
						}
					}
				}
				
				 
			} 
		$stream.="</tr>";
	}
}



   		
	
$stream.="</tbody></table></div>";



  


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################
  
switch($proses)
{
    
    case'getListBarangLaporan':
        
        $isi="<fieldset><table cellspacing='1' class='sortable'  width=100%><thead class=rowheader>
                <thead><tr class=rowheader>
                    <td align=center>No</td>
                    <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                    <td align=center>".$_SESSION['lang']['namabarang']."</td>    
                    <td align=center>".$_SESSION['lang']['satuan']."</td>
                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td align=center>".$_SESSION['lang']['harga']."</td>
                    <td align=center>".$_SESSION['lang']['total']."</td>    
                </tr></thead>
              <tbody>";
        $iBrg="SELECT * FROM ".$dbname.".pabrik_rawatmesindt where notransaksi='".$_POST['nodok']."'";
        $nBrg=$owlPDO->query($iBrg) or die(print " Gagal: ".PDOException::getMessage());
        $nBrg->setFetchMode(PDO::FETCH_ASSOC);
        $noBrg=0;
        while($dBrg=$nBrg->fetch())
        {
            $noBrg+=1;
            $isi.="<tr class=rowcontent>
                <td align=center>".$noBrg."</td>
                <td align=center>".$dBrg['kodebarang']."</td>    
                <td align=left>".$nmBrg[$dBrg['kodebarang']]."</td>
                <td align=left>".$stBrg[$dBrg['kodebarang']]."</td>   
                <td align=right>".number_format($dBrg['jumlah'],2)."</td>
                <td align=right>".number_format($dBrg['harga'],2)."</td>    
                <td align=right>".number_format($dBrg['jumlah']*$dBrg['harga'],2)."</td>
				"; 
        }
		$isi.="</table></fieldset>";
        echo $isi;
        
    break;
    
    case'getStation':
        if($menu == 'pemeliharaan'){
            $optStation="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        }else{
            $optStation="<option value=''>".$_SESSION['lang']['all']."</option>";
        }
        $iStation="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where induk='".$pabrik."' and tipe in ('STATION')";   
        $nStation=$owlPDO->query($iStation) or die(print " Gagal: ".PDOException::getMessage());
        $nStation->setFetchMode(PDO::FETCH_ASSOC);
        while($dStation=$nStation->fetch())
        {
            $optStation.="<option value=".$dStation['kodeorganisasi'].">[".$dStation['kodeorganisasi']."] ".$dStation['namaorganisasi']."</option>";
        }  
        echo $optStation;
    break;
    
    
    
######HTML
	case 'preview':
            
            if($tgl1=='' || $tgl2=='' || $pabrik=='')
            {
                exit("Please Complate the form");
            }
            
		echo $stream;
    break;

######EXCEL	
	case 'excel':
            
                if($tgl1=='' || $tgl2=='' || $pabrik=='')
                {
                    exit("Please Complate the form");
                }
            
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="LAPORAN_PERAWATAN_MESIN_".$tglSkrg;
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
	
	
	
	default:
	break;
}

?>
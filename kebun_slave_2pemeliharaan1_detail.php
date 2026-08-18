<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $men='menu.css';
  $gen='generic.css';
}else if($theme=='red'){
  $men='menuRed.css';
  $gen='genericRed.css';  
}else{
  $men='menuGray.css';
  $gen='genericGray.css';  
}      

?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/kebun_2pemeliharaan.js"></script>
<?
echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";


$kodekegiatan=checkPostGet('kodekegiatan','');
$kodeorg=checkPostGet('kodeorg','');
$bulan=checkPostGet('bulan','');
$type=checkPostGet('type','');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

    // kamus kegiatan
    $str="select kodekegiatan, namakegiatan, satuan
        from ".$dbname.".setup_kegiatan where kodekegiatan='".$kodekegiatan."'
        ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $namaKeg=$bar->namakegiatan;
        $satuKeg=$bar->satuan;
    }

    // kamus barang
    $str="select kodebarang, namabarang, satuan
        from ".$dbname.".log_5masterbarang
        ";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $namabarang[$bar->kodebarang]=$bar->namabarang;
        $satuanbarang[$bar->kodebarang]=$bar->satuan;
    }
    

//=================================================
//echo"<fieldset><legend>Print Excel</legend>
//     <img onclick=\"detailExcel(event,'kebun_slave_2pemeliharaan1_detail.php?type=excel&tanggal=".$kodekegiatan."&kodeorg=".$kodeorg."&bulan=".$bulan."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
//     </fieldset>";

$stream= "";
$kodeJurnal = 'PNN02';
$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
$resParam = fetchData($queryParam);
$kegpanen = $resParam[0]['noakundebet']."02";

if($kodekegiatan!=$kegpanen){
  // Dari BKM Verifikasi 
  if($type!='excel')$stream.="<label>Verifikasi BKM</label><table class=sortable cellpadding=5 border=0 cellspacing=1>"; else
  $stream.="<table class=sortable border=1 cellspacing=1 cellpadding=5>";
    $stream.="
        <thead>
          <tr class=rowcontent>
            <th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['kodeblok']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['hasilkerja2']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['satuan']."</th>
            <th align=center rowspan=2>".$_SESSION['lang']['diverifikasioleh']."</th>
          </tr>
        </thead>
        <tbody>";

    $notem=''; $umr=$insentif=0;
    
    // $str="select a.notransaksi, sum(a.hasilkerja) as hasilkerja, sum(a.jumlahhk) as jumlahhk, a.tanggal, 
    // sum(a.jumlahhk) as jumlahhk, sum(a.hasilkerja) as hasilkerja, b.kodebarang, sum(b.kwantitas) as kwantitas, sum(c.umr) as umr, sum(c.insentif) as insentif
    // from ".$dbname.".kebun_perawatan_vw a
    // left join ".$dbname.".kebun_pakaimaterial b on a.notransaksi=b.notransaksi and a.kodekegiatan=b.kodekegiatan and a.kodeorg=b.kodeorg
    // left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi and a.nikpemel=c.nik and a.nourut=c.nourut
    // where a.kodekegiatan = '".$kodekegiatan."' and a.kodeorg = '".$kodeorg."' and a.tanggal like '".$bulan."%'
    // group by a.notransaksi,b.kodebarang";   

    $sPres = "SELECT notransaksi, SUM(hasilkerja) as hasilkerja FROM $dbname.`kebun_perawatan_vw`
    WHERE `kodekegiatan` = '".$kodekegiatan."' AND `kodeorg` = '".$kodeorg."' AND `tanggal` LIKE '".$bulan."%'
    GROUP BY notransaksi";
    $rPres = fetchData($sPres);
    foreach ($rPres as $pres) {
        $hasilpres[$pres['notransaksi']] = $pres['hasilkerja'];
    }
    
    $str="SELECT a.notransaksi,c.kodeorg,b.kodekegiatan,SUBSTR(a.updatetime,1,10) as tanggal,b.hasilkerja,b.status,a.verifiedby
    FROM $dbname.kebun_verifikasibkm a JOIN $dbname.kebun_statuskegiatan b on a.notransaksi = b.notransaksi
    JOIN $dbname.kebun_perawatan_vw c on a.notransaksi = c.notransaksi
    WHERE b.kodekegiatan='".$kodekegiatan."' and c.kodeorg = '".$kodeorg."' and c.tanggal like '".$bulan."%'
    GROUP BY a.notransaksi, b.kodekegiatan
    ORDER BY a.notransaksi asc";
    $rce = fetchData($str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ); 
    if (count($rce) > 0) {
      while($bar= $res->fetch()){
        $stream.="<tr class=rowcontent>
        <td>".$bar->notransaksi."</td>    
        <td>".$namaKeg."</td>     
        <td>".getIndukBlok($bar->kodeorg)."</td>     
        <td>".tanggalnormal($bar->tanggal)."</td>";
        if ($bar->status == 0) {
          $stream.="<td align=right>".hidezerodecimal(($hasilpres[$bar->notransaksi] - $bar->hasilkerja),2)."</td>"; 
        } else {
          $stream.="<td align=right>".hidezerodecimal($bar->hasilkerja,2)."</td>"; 
        }
        $stream.="<td align=right>".$satuKeg."</td>
        <td>".getNamaKaryawan($bar->verifiedby)."</td>";   
        $stream.="</tr>";
      } 
    } else {
      $stream.="<tr class=rowcontent>
      <td colspan=7 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";   
      $stream.="</tr>";
    }            
  $stream.="</tbody></table><br>";
}

// Dari BKM 
if($type!='excel')$stream.="<label>BKM</label><table class=sortable cellpadding=5 border=0 cellspacing=1>"; else
$stream.="<br><table class=sortable border=1 cellspacing=1 cellpadding=5>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['kodeblok']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['jhk']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['upah']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['premi']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['total']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['hasilkerjad']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['satuan']."</th>
          <th align=center colspan=2>Output</th>
          <th align=center rowspan=2>".$_SESSION['lang']['namabarang']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['jumlah']."</th>
          <th align=center rowspan=2>".$_SESSION['lang']['satuan']."</th>
		</tr>
        <tr class=rowcontent>
          <th align=center rowspan=2>Sat/HK</th>
          <th align=center rowspan=2>Rp/Sat</th>
		  
          ";
        $stream.="</tr>
      </thead>
      <tbody>";
	
    $notem=''; $umr=$insentif=0;
	$kodeJurnal = 'PNN02';
	$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
	$resParam = fetchData($queryParam);
	$kegpanen = $resParam[0]['noakundebet']."02";
	if($kodekegiatan==$kegpanen){		
		$str="select a.notransaksi, sum(a.kgwb) as hasilkerja, sum(a.hk) as jumlahhk, a.tanggalpanen as tanggal, sum((rplb1+rplb2+rpbrd+kehadiran+tambahan)-denda) as insentif
        from ".$dbname.".kebun_3premipemanen a
        where a.blok = '".$kodeorg."' and a.periode like '".$bulan."%'
		group by a.notransaksi, tanggalpanen";   
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ); 
		while($bar= $res->fetch()){
			$stream.="<tr class=rowcontent>
				<td>".$bar->notransaksi."</td>    
				<td>".$namaKeg."</td>     
				<td>".getIndukBlok($kodeorg)."</td>    
				<td nowra>".tanggalnormal($bar->tanggal)."</td>    
				<td align=right>".hidezerodecimal($bar->jumlahhk,2)."</td>    
				<td align=right>".hidezerodecimal($bar->umr,2)."</td>    
				<td align=right>".hidezerodecimal($bar->insentif,2)."</td>    
				<td align=right>".hidezerodecimal($bar->umr+$bar->insentif,2)."</td>    
				<td align=right>".hidezerodecimal($bar->hasilkerja,2)."</td>    
				<td align=center>".$satuKeg."</td>    
				<td align=right>".hidezerodecimal($oput,2)."</td> 
				<td align=right>".hidezerodecimal(fixnan(($bar->umr+$bar->insentif)/$bar->hasilkerja),2)."</td>    					
				<td>".$namabarang[$bar->kodebarang]."</td>    
				<td align=right>".$qwebar."</td>    
				<td>".$satuanbarang[$bar->kodebarang]."</td>";
			
			setIt($jumlahhk,0);
			setIt($hasilkerja,0);
			setIt($bar->jumlahhk,0);
			setIt($bar->hasilkerja,0);
			$jumlahhk+=$bar->jumlahhk;
			$hasilkerja+=$bar->hasilkerja;
			$umr+=$bar->umr;
			$insentif+=$bar->insentif;
		}
		
	}
    $str="select a.notransaksi, sum(a.hasilkerja) as hasilkerja, sum(a.jumlahhk) as jumlahhk, a.tanggal, 
		sum(a.jumlahhk) as jumlahhk, sum(a.hasilkerja) as hasilkerja, b.kodebarang, sum(b.kwantitas) as kwantitas, sum(c.umr) as umr, sum(c.insentif) as insentif
        from ".$dbname.".kebun_perawatan_vw a
        left join ".$dbname.".kebun_pakaimaterial b on a.notransaksi=b.notransaksi and a.kodekegiatan=b.kodekegiatan and a.kodeorg=b.kodeorg
		left join ".$dbname.".kebun_kehadiran c on a.notransaksi=c.notransaksi and a.nikpemel=c.nik and a.nourut=c.nourut
        where a.kodekegiatan = '".$kodekegiatan."' and a.kodeorg = '".$kodeorg."' and a.tanggal like '".$bulan."%'
		group by a.notransaksi,b.kodebarang 
		";   
	// echo $str;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ); 
    while($bar= $res->fetch()){
        $qwebar='';
        if($bar->kwantitas)$qwebar=hidezerodecimal($bar->kwantitas,3);
		if($bar->kodebarang==''){
			$stream.="<tr class=rowcontent>
				<td>".$bar->notransaksi."</td>    
				<td>".$namaKeg."</td>     
				<td>".getIndukBlok($kodeorg)."</td>    
				<td nowra>".tanggalnormal($bar->tanggal)."</td>    
				<td align=right>".hidezerodecimal($bar->jumlahhk,2)."</td>    
				<td align=right>".hidezerodecimal($bar->umr,2)."</td>    
				<td align=right>".hidezerodecimal($bar->insentif,2)."</td>    
				<td align=right>".hidezerodecimal($bar->umr+$bar->insentif,2)."</td>    
				<td align=right>".hidezerodecimal($bar->hasilkerja,2)."</td>    
				<td align=center>".$satuKeg."</td>    
				<td align=right>".hidezerodecimal($oput,2)."</td> 
				<td align=right>".hidezerodecimal(fixnan(($bar->umr+$bar->insentif)/$bar->hasilkerja),2)."</td>    					
				<td>".$namabarang[$bar->kodebarang]."</td>    
				<td align=right>".$qwebar."</td>    
				<td>".$satuanbarang[$bar->kodebarang]."</td>";
			
			setIt($jumlahhk,0);
			setIt($hasilkerja,0);
			setIt($bar->jumlahhk,0);
			setIt($bar->hasilkerja,0);
			$jumlahhk+=$bar->jumlahhk;
			$hasilkerja+=$bar->hasilkerja;
			$umr+=$bar->umr;
			$insentif+=$bar->insentif;
		}else{			
			if($notem!=$bar->notransaksi){
				$notem=$bar->notransaksi;
				@$oput=fixnan($bar->hasilkerja/$bar->jumlahhk);
				setIt($namabarang[$bar->kodebarang],'');
				setIt($satuanbarang[$bar->kodebarang],'');
				$stream.="<tr class=rowcontent>
					<td>".$bar->notransaksi."</td>    
					<td>".$namaKeg."</td>     
					<td>".$namaOrg[$kodeorg]."</td>    
					<td nowrap>".tanggalnormal($bar->tanggal)."</td>    
					<td align=right>".hidezerodecimal($bar->jumlahhk,2)."</td>   
					<td align=right>".hidezerodecimal($bar->umr,2)."</td>    
					<td align=right>".hidezerodecimal($bar->insentif,2)."</td>    
					<td align=right>".hidezerodecimal($bar->umr+$bar->insentif,2)."</td>    					
					<td align=right>".hidezerodecimal($bar->hasilkerja,2)."</td>    
					<td align=center>".$satuKeg."</td>    
					<td align=right>".hidezerodecimal($oput,2)."</td> 
					<td align=right>".hidezerodecimal(fixnan(($bar->umr+$bar->insentif)/$bar->hasilkerja),2)."</td>    					
					<td>".$namabarang[$bar->kodebarang]."</td>    
					<td align=right>".$qwebar."</td>    
					<td>".$satuanbarang[$bar->kodebarang]."</td>";
				
				setIt($jumlahhk,0);
				setIt($hasilkerja,0);
				setIt($bar->jumlahhk,0);
				setIt($bar->hasilkerja,0);
				$jumlahhk+=$bar->jumlahhk;
				$hasilkerja+=$bar->hasilkerja;
				$umr+=$bar->umr;
				$insentif+=$bar->insentif;
			}else{
				$stream.="<tr class=rowcontent>
					<td></td>    
					<td></td>    
					<td></td>    
					<td></td>    
					<td></td>    
					<td></td>    
					<td></td>    
					<td align=right></td>    
					<td align=right></td>    
					<td></td>    
					<td></td>    
					<td align=right></td>
					<td>".$namabarang[$bar->kodebarang]."</td>    
					<td align=right>".$qwebar."</td>    
					<td>".$satuanbarang[$bar->kodebarang]."</td>";             
			}
		}
        $stream.="</tr>";        
    } 
    
    @$oput=fixnan($hasilkerja/$jumlahhk);
    $stream.="<tr class=rowcontent>
            <td colspan=4 align=center>Total</td>    
            <td align=right>".hidezerodecimal($jumlahhk,2)."</td>    
            <td align=right>".hidezerodecimal($umr,2)."</td>    
            <td align=right>".hidezerodecimal($insentif,2)."</td>    
            <td align=right>".hidezerodecimal($umr+$insentif,2)."</td>    
            <td align=right>".hidezerodecimal($hasilkerja,2)."</td>    
            <td align=center>".$satuKeg."</td>    
            <td align=right>".hidezerodecimal($oput,2)."</td>    
            <td align=right>".hidezerodecimal(fixnan(($umr+$insentif)/$hasilkerja),2)."</td>    
            <td colspan=3></td>    
        </tr>";
    
$stream.="</tbody></table>";



##dari BA   
$stream.="<br><label>BA SPK</label>";   
if($type!='excel')$stream.="<table class=sortable border=0 cellpadding=5 cellspacing=1>"; else
$stream.="<br><table class=sortable cellpadding=5 border=1 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <th rowspan=2 align=center>".$_SESSION['lang']['notransaksi']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['kodeblok']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['jhk']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['rupiah']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['hasilkerjad']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
          <th colspan=2 align=center>Output</th>
		</tr>  
		<tr class=rowcontent>
          <th align=center rowspan=2>Sat/HK</th>
          <th align=center rowspan=2>Rp/Sat</th>
		  
          ";
        $stream.="</tr>
      </thead>
      <tbody>";
    ##dari Ba spk
    $iBa="select * from ".$dbname.".log_baspk  where "
            . " kodekegiatan = '".$kodekegiatan."' "
            . " and kodeblok = '".$kodeorg."' "
            . " and tanggal like '".$bulan."%' ";
    $res=$owlPDO->query($iBa) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($dBa=  $res->fetch())
    {
     $stream.="<tr class=rowcontent>
        <td>".$dBa['notransaksi']."</td> 
        <td>".$namaKeg."</td>  
        <td>".$dBa['kodeblok']."</td>     
        <td>".tanggalnormal($dBa['tanggal'])."</td> 
        <td align=right>".hidezerodecimal($dBa['hkrealisasi'],2)."</td> 
        <td align=right>".hidezerodecimal($dBa['jumlahrealisasi'],2)."</td> 
        <td align=right>".hidezerodecimal($dBa['hasilkerjarealisasi'],2)."</td> 
        <td>".$satuKeg."</td> 
		<td align=right>".hidezerodecimal(fixnan($dBa['hasilkerjarealisasi']/$dBa['hkrealisasi']),2)."</td>   
		<td align=right>".hidezerodecimal(fixnan($dBa['jumlahrealisasi']/$dBa['hasilkerjarealisasi']),2)."</td>   
        ";
        $totHk+=$dBa['hkrealisasi'];
        $totHsl+=$dBa['hasilkerjarealisasi'];
        $totrp+=$dBa['jumlahrealisasi'];
        $totOut+=$dBa['hasilkerjarealisasi']/$dBa['hkrealisasi'];
    }
    $stream.="<tr class=rowcontent>
                <td colspan=4 align=center>Total</td>
                <td  align=right>".hidezerodecimal($totHk,2)."</td>
                <td  align=right>".hidezerodecimal($totrp,2)."</td>
                <td  align=right>".hidezerodecimal($totHsl,2)."</td>
                <td>".$satuKeg."</td>
                <td  align=right>".hidezerodecimal(fixnan($totOut),2)."</td>
                <td  align=right>".hidezerodecimal(fixnan($totrp/$totHsl),2)."</td>
    ";
    $stream.="</tbody></table>";
    
    
$stream.="<br><label>Grand Total</label>";   
if($type!='excel')$stream.="<table class=sortable cellpadding=5 border=0 cellspacing=1>"; else
$stream.="<br><table class=sortable border=1 cellpadding=5 cellspacing=1>";
$stream.="
      <thead>
        <tr class=rowcontent>
          <th rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['jhk']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['rupiah']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['hasilkerjad']."</th>
          <th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
          <th colspan=2 align=center>Output</th>
		 </tr>  
		<tr class=rowcontent>
          <th align=center rowspan=2>Sat/HK</th>
          <th align=center rowspan=2>Rp/Sat</th>
		   
		  ";
        $stream.="</tr>
      </thead>
      <tbody>";
$stream.="<tr class=rowcontent>
        <td>".$namaKeg."</td>
        <td  align=right>".hidezerodecimal($totHk+$jumlahhk,2)."</td>
        <td  align=right>".hidezerodecimal($totrp+$umr+$insentif,2)."</td>
        <td  align=right>".hidezerodecimal($totHsl+$hasilkerja,2)."</td>
        <td>".$satuKeg."</td>
        <td  align=right>".hidezerodecimal(fixnan(($totHsl+$hasilkerja)/($totHk+$jumlahhk)),2)."</td>
        <td  align=right>".hidezerodecimal(fixnan(($totrp+$umr+$insentif)/($totHsl+$hasilkerja)),2)."</td>
    ";
   
$stream.="</tr></tbody></table>";
   if($type=='excel')
   {
$nop_="Detail_pemeliharaan1_".$kodekegiatan."_".$namaOrg[$kodeorg]."_".$bulan;
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
   }
   else
   {
       echo $stream;
   }    
       
?>
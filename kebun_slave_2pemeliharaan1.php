<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$kdOrg1=checkPostGet('kdOrg1','');
$kdAfd1=checkPostGet('kdAfd1','');
$tahun1=checkPostGet('tahun1','');
$periode=checkPostGet('periode','');
$kegiatan1=checkPostGet('kegiatan1','');

if($periode!=''){
	$bulan=intval(substr($periode,-2));
}else{
}
	$bulan=12;


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

if(($proses=='excel')or($proses=='pdf')){
	$kdOrg1=$_GET['kdOrg1'];
	$kdAfd1=$_GET['kdAfd1'];
	$tahun1=$_GET['tahun1'];
	$kegiatan1=$_GET['kegiatan1'];
}
if($kdAfd1=='')
    $kdAfd1=$kdOrg1;

if(($proses=='preview')or($proses=='excel')or($proses=='pdf')){
    if($kdOrg1==''){
            //echo"Error: Estate code and afdeling code required."; exit;
    }
    if($tahun1==''){
            echo"Error: year is reqired."; exit;
    }
	
}


if ($proses=='excel' or $proses=='preview') 
{
    
     if($kdOrg1=='')
    {
        exit("Warning:Kebun masih kosong");
    }
    
    
    // kamus kegiatan
if($_SESSION['language']=='EN'){
    $zz='namakegiatan1 as namakegiatan';
}else{
    $zz='namakegiatan';
}    
    $str="select kodekegiatan, ".$zz.", satuan, pilihanluas
        from ".$dbname.".setup_kegiatan
        ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $kamusKeg[$bar->kodekegiatan]['nama']=$bar->namakegiatan;
        $kamusKeg[$bar->kodekegiatan]['satu']=$bar->satuan;
        $kamusKeg[$bar->kodekegiatan]['pilihanluas']=$bar->pilihanluas;
    }
    
    if($kdAfd1!=''){
        $sCarTahun = "SELECT MAX(`tahun`) as tahun
        FROM ".$dbname.".`setup_blok_tahunan` where kodeorg like '".$kdAfd1."%' and tahun like '".$tahun1."%'";
    }
    else
    {
        $sCarTahun = "SELECT MAX(`tahun`) as tahun
        FROM ".$dbname.".`setup_blok_tahunan` where kodeorg like '".$kdOrg1."%' and tahun like '".$tahun1."%'";
    }
    $qCarTahun=$owlPDO->query($sCarTahun) or die(print " Gagal: ".PDOException::getMessage());
    $qCarTahun->setFetchMode(PDO::FETCH_ASSOC);
    $rCarTahun=$qCarTahun->fetch();

    // kamus kegiatan
    $sKeg = "SELECT * FROM $dbname.setup_kegiatan WHERE kodekegiatan like '%".$kegiatan1."%'";
    $rKeg = $owlPDO->query($sKeg) or die(print " Gagal: ".PDOException::getMessage());
    $rKeg->setFetchMode(PDO::FETCH_OBJ);
    while ($bKeg = $rKeg->fetch()) {
        $pilluas[$bKeg->kodekegiatan] = $bKeg->pilihanluas;
        $stblok[$bKeg->kelompok]      = $bKeg->kelompok;
    }
    
    // kamus blok
    $str="select indukblok, SUM(jumlahpokok) AS jmlpokok, SUM(luasareaproduktif) AS luasareaproduktif, SUM(luasbloking) AS luasbloking, SUM(lc) AS lc, statusblok
        from ".$dbname.".setup_blok_tahunan where tahun='".$rCarTahun['tahun']."'
        group by indukblok, statusblok";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $numrows=owlbaris($res);
    if($numrows==0){
        $str="select indukblok, SUM(jumlahpokok) AS jmlpokok, SUM(luasareaproduktif) AS luasareaproduktif, SUM(luasbloking) AS luasbloking, SUM(lc) AS lc, statusblok
        from ".$dbname.".setup_blok
        group by indukblok, statusblok";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
    }
    while($bar=$res->fetch())
    {
        $kamusOrg[$bar->indukblok][$bar->statusblok]['luas3']=$bar->luasareaproduktif;
        $kamusOrg[$bar->indukblok][$bar->statusblok]['luas2']=($bar->lc);
        $kamusOrg[$bar->indukblok][$bar->statusblok]['luas1']=($bar->luasbloking - $bar->lc);
        $kamusOrg[$bar->indukblok][$bar->statusblok]['tata']=$bar->statusblok;
        $kamusOrg[$bar->indukblok][$bar->statusblok]['jmlpokok']=$bar->jmlpokok;
    }
    // echo "<pre>";
    // print_r($kamusOrg);
    // echo "</pre>";
    
    // ambil data kegiatan/blok
    if($kdOrg1==''){
    $str="select kodekegiatan, kodeorg, hasilkerja, jumlahhk, tanggal 
        from ".$dbname.".kebun_perawatan_vw
        where kodeorg like '".$kdAfd1."%' and tanggal like '".$tahun1."%' and kodekegiatan like '%".$kegiatan1."%' 
        and jurnal=1
        ";
    }
    else{
        $where='';
        if($kdOrg1 != $_SESSION['empl']['lokasitugas']){                
            $where=" and jurnal=1";
        }
        $str="select kodekegiatan, kodeorg, hasilkerja, jumlahhk, tanggal 
        from ".$dbname.".kebun_perawatan_vw
        where kodeorg like '".$kdAfd1."%' and tanggal like '".$tahun1."%' and kodekegiatan like '%".$kegiatan1."%' 
        ".$where." ";
    }
    // echo $str;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $dzKeg[$bar->kodekegiatan]=$bar->kodekegiatan;
        $dzOrg[$bar->kodeorg]=$bar->kodeorg;

        $bulan=substr($bar->tanggal,5,2);
		setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja'],0);
		setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk'],0);
        $dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja']+=$bar->hasilkerja;
        $dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk']+=$bar->jumlahhk;
        
        // cari jumlah baris untuk tiap kegiatan
        if(!isset($barisKeg[$bar->kodekegiatan][$bar->kodeorg])){
			setIt($barizKeg[$bar->kodekegiatan],0);
            $barisKeg[$bar->kodekegiatan][$bar->kodeorg]=$bar->kodekegiatan.$bar->kodeorg;            
            $barizKeg[$bar->kodekegiatan]+=1;
        }
    }
    
    // echo "<pre>";
    // print_r($pilluas);
    // echo "</pre>";

	$kodeJurnal = 'PNN02';
	$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit,noakundebet',"kodeaplikasi='PNN' and jurnalid='".$kodeJurnal."'");
	$resParam = fetchData($queryParam);
	$kegpanen = $resParam[0]['noakundebet']."02";
	if($kegiatan1==$kegpanen){		
		$str="select blok as kodeorg, blok,periode, sum(kgwb) as hasilkerja, sum(hk) as jumlahhk, tanggalpanen  as tanggal 
		from ".$dbname.".kebun_3premipemanen where blok like '".$kdAfd1."%' and tanggalpanen like '".$tahun1."%' and posting=1
		group by blok, periode";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$bar->kodekegiatan=$kegpanen;
			$dzKeg[$bar->kodekegiatan]=$bar->kodekegiatan;
			$dzOrg[$bar->kodeorg]=$bar->kodeorg;

			$bulan=substr($bar->tanggal,5,2);
			setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja'],0);
			setIt($dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk'],0);
			$dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['hasilkerja']+=round($bar->hasilkerja);
			$dzArr[$bar->kodekegiatan][$bar->kodeorg][$bulan]['jumlahhk']+=$bar->jumlahhk;
			
			// cari jumlah baris untuk tiap kegiatan
			if(!isset($barisKeg[$bar->kodekegiatan][$bar->kodeorg])){
				setIt($barizKeg[$bar->kodekegiatan],0);
				$barisKeg[$bar->kodekegiatan][$bar->kodeorg]=$bar->kodekegiatan.$bar->kodeorg;            
				$barizKeg[$bar->kodekegiatan]+=1;
			}
		}
	}			
		

    if($kdAfd1!='')
    {
        $sortOrg=" and kodeblok like '".$kdAfd1."%' ";
    }
    else
    {
        $sortOrg=" and kodeblok like '".$kdOrg1."%' ";   
    }
   
    if($kegiatan1!='')
    {
        $sortKeg=" and kodekegiatan='".$kegiatan1."'";
    }
    else
    {
        
        $sortKeg=" and kodekegiatan in "
                . " (select kodekegiatan from ".$dbname.".setup_kegiatan where "
                . " kelompok in ('BBT','TM','TB','TBM','PNN')) ";
    }
    
    $iBa="select * from ".$dbname.".log_baspk where "
            . " tanggal like '".$tahun1."%' "
            . " and statusjurnal=1 ".$sortOrg." ".$sortKeg." ";
	$nBa=$owlPDO->query($iBa) or die(print " Gagal: ".PDOException::getMessage());
	$nBa->setFetchMode(PDO::FETCH_ASSOC);
	while($dBa=$nBa->fetch())
    {
        $dzKeg[$dBa['kodekegiatan']]=$dBa['kodekegiatan'];
        $dzOrg[$dBa['kodeblok']]=$dBa['kodeblok'];
        $bulan=substr($dBa['tanggal'],5,2);
        
        
       setIt($dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['hasilkerja'], 0);
       setIt($dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['jumlahhk'], 0);
        
        $dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['hasilkerja']+=$dBa['hasilkerjarealisasi'];
        $dzArr[$dBa['kodekegiatan']][$dBa['kodeblok']][$bulan]['jumlahhk']+=$dBa['hkrealisasi'];
        
        if(!isset($barisKeg[$dBa['kodekegiatan']][$dBa['kodeblok']])){
            $barisKeg[$dBa['kodekegiatan']][$dBa['kodeblok']]=$dBa['kodekegiatan'].$dBa['kodeblok'];            
            @$barizKeg[$dBa['kodekegiatan']]+=1;
        }
    }

	
    if(!empty($dzKeg))asort($dzKeg);
    if(!empty($dzOrg))asort($dzOrg);
    $jumlahKeg = count($dzKeg);
    $jumlahOrg = count($dzOrg);

    $border=0;
    if($proses=='excel'){
		$border=1;
		$stream="<table>
					<tr>
						<td colspan=16 style='text-align:left; font-weight:bold'>".$_SESSION['lang']['laporanRotasiPemeliharaan']."</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</table>";
	}else{
		$border=0;
		$stream="";
	}
	$bulan=12;
	//$stream.="<div class='table-scroll' style='height:400px;'>";
    $stream.="<table cellspacing='1' cellpadding=5 border='".$border."' class='sortable'>
    <thead>
    <tr class=rowheader>
        <th rowspan=2 align=center>".$_SESSION['lang']['kodekegiatan']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</th>
        <th rowspan=2 align=center>".$_SESSION['lang']['satuan']."</th>
        <th colspan=5 align=center>".$_SESSION['lang']['blok']."</th>";   
    for ($i = 1; $i <= $bulan; $i++) {
        $stream.="<th colspan=3 align=center>".numToMonth($i)."</th>";   
    }    
        $stream.="<th colspan=3 align=center>".$_SESSION['lang']['semester']." I</th>
        <th colspan=3 align=center>".$_SESSION['lang']['semester']." II</th>
        <th colspan=3 align=center>".$_SESSION['lang']['total']."</th>
    </tr>
    <tr class=rowheader>
        <th align=center>".$_SESSION['lang']['kode']."</th>    
        <th align=center>".$_SESSION['lang']['luas']."</th>
        <th align=center>".$_SESSION['lang']['statusblok']."</th>
        <th align=center>".$_SESSION['lang']['jmlpkk']."</th>
        <th align=center>".$_SESSION['lang']['sph']."</th>";
    // tiap bulan
    for ($i = 1; $i <= $bulan; $i++) {
        $stream.="<th align=center>".$_SESSION['lang']['jhk']."</th>
        <th align=center>".$_SESSION['lang']['hasilkerjad']."</th>
        <th align=center>(".$_SESSION['lang']['hasil']." / Hk)</th>";   
    }    
        $stream.="<th align=center>".$_SESSION['lang']['jhk']."</th>
        <th align=center>".$_SESSION['lang']['hasilkerjad']."</th>
        <th align=center>(".$_SESSION['lang']['hasil']." / Hk)</th>
        <th align=center>".$_SESSION['lang']['jhk']."</th>
        <th align=center>".$_SESSION['lang']['hasilkerjad']."</th>
        <th align=center>(".$_SESSION['lang']['hasil']." / Hk)</th>
        <th align=center>".$_SESSION['lang']['jhk']."</th>
        <th align=center>".$_SESSION['lang']['hasilkerjad']."</th>
        <th align=center>(".$_SESSION['lang']['hasil']." / Hk)</th>
    </tr></thead>
    <tbody>";
    // tiap kegiatan    
    if(!empty($dzKeg))foreach($dzKeg as $rKeg){
        $bariskegiatan=true;
        $stream.="<tr class=rowcontent>
            <td rowspan=".$barizKeg[$rKeg].">".$rKeg."</td>
            <td rowspan=".$barizKeg[$rKeg].">".$kamusKeg[$rKeg]['nama']."</td>
            <td rowspan=".$barizKeg[$rKeg].">".$kamusKeg[$rKeg]['satu']."</td>";
        // tiap blok    
        if(!empty($dzOrg))foreach($dzOrg as $rOrg){
            
            $adadata=false;
            for ($i = 1; $i <= $bulan; $i++) {
                if(strlen($i)==1)$ii='0'.$i; else $ii=$i;    
                
                if(!empty($dzArr[$rKeg][$rOrg][$ii]['hasilkerja']))$adadata=true;
                if(!empty($dzArr[$rKeg][$rOrg][$ii]['jumlahhk']))$adadata=true;
            }
            
            if($adadata){
                if(!$bariskegiatan)$stream.="<tr class=rowcontent>";
                    $stream.="<td>".getIndukBlok($rOrg)."</td>";        
                    if($kamusKeg[$rKeg]['pilihanluas'] == 1 && getNamaKeg($rKeg,'kelompok') != 'PNN'){
                        $stream.="<td align=right>".$kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas1']."</td>";        
                    }elseif($kamusKeg[$rKeg]['pilihanluas'] == 2 && getNamaKeg($rKeg,'kelompok') != 'PNN'){
                        $stream.="<td align=right>".$kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas2']."</td>";        
                    }else{
                        if (getNamaKeg($rKeg,'kelompok') == "PNN") {
                            $stream.="<td align=right>".$kamusOrg[$rOrg]["TM"]['luas3']."</td>";
                        } else {
                            $stream.="<td align=right>".$kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas3']."</td>";        
                        }
                    }
                    // $stream.="<td align=right>".$kamusOrg[$rOrg]['tata']."</td>";
                    if (getNamaKeg($rKeg,'kelompok') == "PNN") {
                        $stream.="<td align=left>TM</td>";     
                        $stream.="<td align=right>".number_format($kamusOrg[$rOrg]['TM']['jmlpokok'],2)."</td>";
                        $stream.="<td align=right>".number_format(fixnan($kamusOrg[$rOrg]['TM']['jmlpokok'] / $kamusOrg[$rOrg]['TM']['luas3']),2)."</td>";
                    } else {
                        $stream.="<td align=left>".getNamaKeg($rKeg,'kelompok')."</td>";     
                        $stream.="<td align=right>".number_format($kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['jmlpokok'],2)."</td>";
                        if($kamusKeg[$rKeg]['pilihanluas'] == 1){
                            $stream.="<td align=right>".number_format(fixnan($kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['jmlpokok'] / $kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas1']),2)."</td>";
                        } elseif($kamusKeg[$rKeg]['pilihanluas'] == 2){
                            $stream.="<td align=right>".number_format(fixnan($kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['jmlpokok'] / $kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas2']),2)."</td>";
                        } else {
                            $stream.="<td align=right>".number_format(fixnan($kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['jmlpokok'] / $kamusOrg[$rOrg][getNamaKeg($rKeg,'kelompok')]['luas3']),2)."</td>";
                        }
                    }

                    $jumlahhk1=0;
                    $jumlahhk2=0;
                    $hasilkerja1=0;
                    $hasilkerja2=0;
                for ($i = 1; $i <= $bulan; $i++) {
                    if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
                    setIt($dzArr[$rKeg][$rOrg][$ii]['jumlahhk'],0);
                    setIt($dzArr[$rKeg][$rOrg][$ii]['hasilkerja'],0);
                    $haka=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                    $hasi=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                    
                    
                
                    
                    $oput=0;
                    @$oput=fixnan($hasi/$haka);
                    if($haka==0)
                    {
                        $haka='';
                    }
                    else
                    {
                        $haka=hidezerodecimal($haka,2);
                    }
                    
                    if($hasi==0)
                    {
                        $hasi='';
                    }
                    else
                    {
                        $hasi=hidezerodecimal($hasi,2);
                    }
                    
                    
                    if($oput==0)
                    {
                        $oput='';
                    }
                    else
                    {
                        $oput=hidezerodecimal($oput,2);
                    }
                    
                    $bisadiklik=" style='cursor:pointer;color:blue;' onclick=\"viewDetail1('".$rKeg."','".$rOrg."','".$tahun1."-".$ii."',event);\" title=\"Click untuk melihat detail\" ";
                

                    $stream.="<td align=right ".$bisadiklik.">".$haka."</td>
                    <td align=right ".$bisadiklik.">".$hasi."</td>
                    <td align=right ".$bisadiklik.">".$oput."</td>";
                    
                    setIt($dzArr[$rKeg][$rOrg][$ii]['jumlahhk'],0);
                    setIt($dzArr[$rKeg][$rOrg][$ii]['hasilkerja'],0);
                    if($i<8){ // semester 1
                        $jumlahhk1+=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                        $hasilkerja1+=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                    }else{ // semester 2
                        $jumlahhk2+=$dzArr[$rKeg][$rOrg][$ii]['jumlahhk'];
                        $hasilkerja2+=$dzArr[$rKeg][$rOrg][$ii]['hasilkerja'];
                    }           
                }
                // semester 1
                $oput=0;
                $haka=0;
                $hasi=0;

                $haka=$jumlahhk1;
                $hasi=$hasilkerja1;
                @$oput=fixnan($hasi/$haka);
                if(($haka==0)&&($hasi==0)){
                    $haka='';
                    $hasi='';
                    $oput='';
                }else{
                    $haka=hidezerodecimal($haka,2);
                    $hasi=hidezerodecimal($hasi,2);                
                    $oput=hidezerodecimal($oput,2);
                }

                $stream.="<td align=right>".$haka."</td>
                <td align=right>".$hasi."</td>
                <td align=right>".$oput."</td>";

                // semester 2
                $oput=0;
                $haka=0;
                $hasi=0;

                $haka=$jumlahhk2;
                $hasi=$hasilkerja2;
                @$oput=fixnan($hasi/$haka);
                if(($haka==0)&&($hasi==0)){
                    $haka='';
                    $hasi='';
                    $oput='';
                }else{
                    $haka=hidezerodecimal($haka,2);
                    $hasi=hidezerodecimal($hasi,2);                
                    $oput=hidezerodecimal($oput,2);
                }

                $stream.="<td align=right>".$haka."</td>
                <td align=right>".$hasi."</td>
                <td align=right>".$oput."</td>";

                // total
                $oput=0;
                $haka=0;
                $hasi=0;

                $haka=$jumlahhk1+$jumlahhk2;
                $hasi=$hasilkerja1+$hasilkerja2;
                @$oput=fixnan($hasi/$haka);
                
                if(($haka==0)&&($hasi==0)){
                    $haka='';
                    $hasi='';
                    $oput='';
                }else{
                    $haka=hidezerodecimal($haka,2);
                    $hasi=hidezerodecimal($hasi,2);                
                    $oput=hidezerodecimal($oput,2);
                }            

                $stream.="<td align=right>".$haka."</td>
                <td align=right>".$hasi."</td>
                <td align=right>".$oput."</td>";

                $stream.="</tr>";
                $bariskegiatan=false;            
            } // end of adadata

        } //  end of tiap blok
    } //  end of tiap kegiatan    
    $stream.="</tbody></table>";
     
}  
switch($proses)
{
    case'preview':
        echo $stream;    
    break;
    case 'excel':
        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
        $dte=date("YmdHms");
        $nop_="Pusingan_Perawatan_".$kdAfd1."_".$tahun1."_".$kegiatan1."_".date('YmdHis');
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                }
            }	
            closedir($handle);
        }
        $handle=fopen("tempExcel/".$nop_.".xls",'w');
        if(!fwrite($handle,$stream)){
            echo "<script language=javascript1.2>
                parent.window.alert('Cant convert to excel format');
                </script>";
            exit;
        }
        else
        {
            echo "<script language=javascript1.2>
            window.location='tempExcel/".$nop_.".xls';
            </script>";
        }
        closedir($handle);
        // $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
        // gzwrite($gztralala, $stream);
        // gzclose($gztralala);
        // echo "<script language=javascript1.2>
        //     window.location='tempExcel/".$nop_.".xls.gz';
        //     </script>";            
    break;    
}

?>
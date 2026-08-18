<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$periode=checkPostGet('periode0','');


if($proses=='preview'||$proses=='excel'){
    if($periode==''){
        exit("Error: All field required");
    }
    
    #ambil data jurnal
    $sQuery="SELECT a.nojurnal, a.kodekegiatan, a.kodeblok, a.jumlah
        FROM ".$dbname.".`keu_jurnaldt_vw` a
        WHERE a.`tanggal` LIKE '".$periode."%'
        AND a.kodekegiatan != ''
        AND length( a.kodeblok ) =10
        AND a.jumlah >0
        ";
    $rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch())
        {
        $listKegiatan[$bQuery->kodekegiatan]=$bQuery->kodekegiatan;
        $listBlok[$bQuery->kodeblok]=$bQuery->kodeblok;
        @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['total']+=$bQuery->jumlah;
        if(substr($bQuery->nojurnal,13,4)=='/M0/'){ // HK Perawatan
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/M0/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,7)=='/INVK1/'){ // Material
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/INVK1/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/PNN'){ // Panen
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/PNN']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/KK'){ // Kas Keluar
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/BK'){ // Bank Keluar, digabung ke Kas Keluar
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/KK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/SPK'){ // SPK
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/SPK']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/CT'){ // Catu Beras
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/CT']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,4)=='/VHC'){ // Transit Kendaraan
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/VHC']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,3)=='/M/'){ // Memorial
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/M/']+=$bQuery->jumlah;
        }else if(substr($bQuery->nojurnal,13,5)=='/IDC/'){ // IDC
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['/IDC/']+=$bQuery->jumlah;
        }else{ // lainnya, harusnya nol
            @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['none']+=$bQuery->jumlah;
        }        
    }
    
    if(!empty($listKegiatan))sort($listKegiatan);
    if(!empty($listBlok))sort($listBlok);

    #ambil HK dan prestasi perawatan    
    $sQuery="SELECT b.kodekegiatan, b.kodeorg, a.jhk
        FROM ".$dbname.".`kebun_kehadiran` a
        LEFT JOIN ".$dbname.".`kebun_prestasi` b on a.notransaksi=b.notransaksi
        LEFT JOIN ".$dbname.".`kebun_aktifitas` c on b.notransaksi=c.notransaksi
        WHERE c.tanggal LIKE '".$periode."%'
        AND c.jurnal =1
        AND b.nik = '-'
        ";  
$rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch()){
    @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeorg]['hk/M0/']+=$bQuery->jhk;
    }    
    
    #ambil prestasi perawatan    
    $sQuery="SELECT b.kodekegiatan, b.kodeorg, b.hasilkerja
        FROM ".$dbname.".`kebun_prestasi` b
        LEFT JOIN ".$dbname.".`kebun_aktifitas` c on b.notransaksi=c.notransaksi
        WHERE c.tanggal LIKE '".$periode."%'
        AND c.jurnal =1
        AND b.nik = '-'
        ";  
    $rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch()){
    @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeorg]['prestasi/M0/']+=$bQuery->hasilkerja;            
    }    
    
    #ambil hk dan prestasi panen    
    $sQuery="SELECT b.kodeorg, b.hasilkerja
        FROM ".$dbname.".`kebun_prestasi_vw` b
        WHERE b.tanggal LIKE '".$periode."%'
        AND b.jurnal =1
        ";  

    $rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch()){
    @$dzArr['611010101'][$bQuery->kodeorg]['hk/PNN']+=1;            
        @$dzArr['611010101'][$bQuery->kodeorg]['prestasi/PNN']+=$bQuery->hasilkerja;            
    }    
    
    #ambil prestasi spk    
    $sQuery="SELECT b.kodekegiatan, b.kodeblok, b.hasilkerjarealisasi
        FROM ".$dbname.".`log_baspk` b
        WHERE b.tanggal LIKE '".$periode."%'
        AND b.statusjurnal =1
        ";  

    $rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch()){
    @$dzArr[$bQuery->kodekegiatan][$bQuery->kodeblok]['prestasi/SPK']+=$bQuery->hasilkerjarealisasi;            
    }    
    
    #ambil kegiatan
    $sQuery="select kodekegiatan, namakegiatan,satuan from ".$dbname.".setup_kegiatan
        where 1";  

    $rQuery=$owlPDO->query($sQuery) or die(print " Gagal: ".PDOException::getMessage());
$rQuery->setFetchMode(PDO::FETCH_OBJ);
while($bQuery=$rQuery->fetch()){
    $dicKegiatan[$bQuery->kodekegiatan]=$bQuery->namakegiatan;
        $dicSatuanKegiatan[$bQuery->kodekegiatan]=$bQuery->satuan;
    }    
$bgcoloraja="";
    $brd=0;
    if($proses!='excel'){
        // ga ngapa-ngapain
    }else{
        $brd=1;
        $bgcoloraja="bgcolor=#DEDEDE align=center";
        $tab.= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['costelement']."<br>Periode: ".$periode."";
    } 
    $tab.="
    <table width=100% cellspacing=1 class=sortable border=".$brd." >
    <thead>
    <tr>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kodekegiatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kodeblok']."</td>
        <td ".$bgcoloraja." align=center colspan=3>".$_SESSION['lang']['perawatan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['material']."</td>
        <td ".$bgcoloraja." align=center colspan=3>".$_SESSION['lang']['panen']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kasbank']."</td>
        <td ".$bgcoloraja." align=center colspan=2>".$_SESSION['lang']['spk']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['pembagiancatu']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['kendaraan']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['jurnal']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['alokasiidc']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['lain']."</td>
        <td ".$bgcoloraja." align=center rowspan=2>".$_SESSION['lang']['jumlah']."</td>
    </tr>
    <tr>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['jhk']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['jhk']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['fisik']."</td>
        <td ".$bgcoloraja." align=center>".$_SESSION['lang']['nilai']."</td>
    </tr>

    </thead>
    <tbody>";
    
    if(!empty($listBlok))foreach($listBlok as $lBlok){
        if(!empty($listKegiatan))foreach($listKegiatan as $lKegiatan){
            $dzArr[$lKegiatan][$lBlok]['total']=isset($dzArr[$lKegiatan][$lBlok]['total'])?$dzArr[$lKegiatan][$lBlok]['total']:'0';
            if($dzArr[$lKegiatan][$lBlok]['total']>0){
                $tab.="
                <tr class=rowcontent>
                <td align=center>".$lKegiatan."</td>
                <td>".$dicKegiatan[$lKegiatan]."</td>
                <td>".$dicSatuanKegiatan[$lKegiatan]."</td>
                <td align=center>".$lBlok."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['hk/M0/'],2)."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['prestasi/M0/'],2)."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/M0/'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/INVK1/'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['hk/PNN'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['prestasi/PNN'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/PNN'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/KK'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['prestasi/SPK'],2)."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/SPK'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/CT'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/VHC'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/M/'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['/IDC/'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['none'])."</td>
                <td align=right>".@number_format($dzArr[$lKegiatan][$lBlok]['total'])."</td>
                </tr>";
            }
        }
    }
    
    $tab.="</tbody></table>";

}	
switch($proses)
{
    case'preview':
        echo $tab;
    break;

    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="CostElement_".$periode;
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
          //  closedir($handle);
        }
    break;
    
    default:
    break;
}
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
    $proses=$_POST['proses'];
}
else            {
    $proses=$_GET['proses'];
}
$_POST['kdAfd']==''?$kodeblok=$_GET['kdAfd']:$kodeblok=$_POST['kdAfd'];
$_POST['periode']==''?$periode=$_GET['periode']:$periode=$_POST['periode'];

##cek rencana sebelum periode
    $s_cek="select blok,sum(rencanasisip) as rencana
            from ".$dbname.".kebun_rencanasisip
            where blok like '%".$kodeblok."%'
            and periode < '".$periode."' and posting=1
            group by blok";
    $q_cek=$owlPDO->query($s_cek) or die(print " Gagal: ".PDOException::getMessage());
    $q_cek->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cek=$q_cek->fetch())
    {
        $cek_blok[$r_cek['blok']]=$r_cek['blok'];
        $cek_rencana[$r_cek['blok']]=$r_cek['rencana'];  
    }
    ##cek realisasi sebelum periode
   $s_cekreal="select a.kodeorg as blok,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
               from ".$dbname.".kebun_prestasi a
               left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
               where substr(b.tanggal,1,7) < '".$periode."' and b.jurnal=1
               and a.kodeorg like '%".$kodeblok."%'
               and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
               group by a.kodeorg";
    $q_cekreal=$owlPDO->query($s_cekreal) or die(print " Gagal: ".PDOException::getMessage());
    $q_cekreal->setFetchMode(PDO::FETCH_ASSOC);
    while($r_cekreal=$q_cekreal->fetch())
    {
        $cek_blokr[$r_cekreal['blok']]=$r_cekreal['blok'];
        $cek_sisip[$r_cekreal['blok']]=$r_cekreal['sudahsisip'];
    }
    
    ##rencana sisip
    $str1="select sum(rencanasisip) as rencana,blok,periode,pokok,sph,pokokmati,keterangan
            from ".$dbname.".kebun_rencanasisip 
            where blok like '%".$kodeblok."%' 
            and periode like '".$periode."%' and posting=1
            group by blok";
    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_ASSOC);
    while($bar1=$res1->fetch())
    {
        $blok[$bar1['blok']]=$bar1['blok'];
        $pokok[$bar1['blok']]=$bar1['pokok'];
        $sph[$bar1['blok']]=$bar1['sph'];
        $pokokmati[$bar1['blok']]=$bar1['pokokmati'];
        $rencana[$bar1['blok']]=$bar1['rencana'];
        $ket[$bar1['blok']]=$bar1['keterangan'];
    }
    
    $str2="select a.kodeorg as blok,a.kodekegiatan as kodekegiatan,sum(a.hasilkerja) as sudahsisip 
           from ".$dbname.".kebun_prestasi a
           left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
           where b.tanggal like '".$periode."%' and b.jurnal=1
           and a.kodeorg like '%".$kodeblok."%'
           and a.kodekegiatan in (SELECT nilai FROM ".$dbname.".`setup_parameterappl` WHERE `kodeaplikasi` LIKE 'tn' AND `kodeparameter` LIKE 'sisip%')
           group by a.kodeorg";
    $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
    $res2->setFetchMode(PDO::FETCH_ASSOC);
    while($bar2=$res2->fetch()){
        $sudahsisip[$bar2['kodeorg']]=$bar2['sudahsisip'];
    }
$brdr=0;
$bgcoloraja='';
if($proses=='excelgetDetail2')
{
    //exit("error:".$arrPilMode[$pilMode]."__".$pilMode);
    $bgcoloraja="bgcolor=#DEDEDE align=center";
    $brdr=1;
    $stream.="
    <table>
    <tr><td colspan=15 align=left><b><font size=5>Detail Rencana Sisip</font></b></td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['kebun']." : ".substr($kodeblok,0,4)."</td></tr>
    <tr><td colspan=15 align=left>".$_SESSION['lang']['periode']." : ".$periode."</td></tr>
    </table>";
}
$stream.="<table class=sortable cellspacing=1 border=$brdr style='width:500px;'>
      <thead class=rowheader>
        <tr>
        <td align=center>No.</td>
        <td align=center>".$_SESSION['lang']['blok']."</td>
        <td align=center>".$_SESSION['lang']['pokok']."</td>
        <td align=center>".$_SESSION['lang']['sph']."</td>
        <td align=center>".$_SESSION['lang']['pokokmati']."</td>
        <td align=center>Sisa ".$_SESSION['lang']['rencanasisip']."</td>
        <td align=center>".$_SESSION['lang']['rencanasisip']."</td>
        <td align=center>".$_SESSION['lang']['keterangan']."</td>
        <td align=center>Sudah Sisip</td>
     </tr></thead>";

$stream.="<tbody>";
$no=0;
if(!empty($blok)) 
   {
    foreach($blok as $blk)
    {
        $no++;
        $rencanalalu[$blk]=$cek_rencana[$blk]-$cek_sisip[$blk];
        $totalrencana[$blk]=$rencana[$blk]+$rencanalalu[$blk];
            
        $stream.="<tr class=rowcontent onclick=detail('".$blk."','".$periode."')>
                    <td align=center>".$no."</td>
                    <td align=center>".$blk."</td>
                    <td align=right>".number_format($pokok[$blk],0)."</td>
                    <td align=right>".number_format($sph[$blk],0)."</td>
                    <td align=right>".number_format($pokokmati[$blk],0)."</td>
                    <td align=right>".number_format($rencanalalu[$blk],0)."</td>
                    <td align=right>".number_format($rencana[$blk],0)."</td>
                    <td>".$ket[$blk]."</td>
                    <td align=right>".number_format($sudahsisip[$blk],0)."</td>    
                    </tr>";
    }
    
   }
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";
 if($proses=='detailsisip'){
    $stream.= "<button class=mybutton onclick=kembali(1)>".$_SESSION['lang']['back']."</button>";
    $stream.= "<button class=mybutton onclick=zExcelDt(event,'kebun_slave_2rencanasisip_detail.php','".$_POST['kdAfd']."','".$_POST['periode']."')>".$_SESSION['lang']['excel']."</button>";
 }
switch($proses)
{
    case 'detailsisip':
        echo $stream;
        break;
    case'excelgetDetail2':
//      echo $stream;
        $stream.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="detailRencanaSisip";
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
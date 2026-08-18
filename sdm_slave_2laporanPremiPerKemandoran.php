<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

// get post =========================================================================

$proses = checkPostGet('proses', '');
$periode = checkPostGet('periode', '');
$kdOrg = checkPostGet('kdOrg', '');

$lksiTgs=$_SESSION['empl']['lokasitugas'];
if($kdOrg=='')$kdOrg=$_GET['kdOrg'];
if($kdOrg=='')$kdOrg=$_SESSION['empl']['lokasitugas'];

#ambil tanggal periode gaji
    $lok=substr($kdOrg,0,4); 
    $sDatez = "select tanggalmulai,tanggalsampai from ".$dbname.".sdm_5periodegaji where periode = '".$periode."' and kodeorg= '".$lok."'";
	$qDatez=$owlPDO->query($sDatez) or die(print " Gagal: ".PDOException::getMessage());
	$qDatez->setFetchMode(PDO::FETCH_ASSOC);
	while($rDatez=$qDatez->fetch()){	
		$tanggalMulai=$rDatez['tanggalmulai'];
		$tanggalSampai=$rDatez['tanggalsampai'];
    }
#ambil semua nama karyawan unit bersangkuran
 $str="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$lok."'";
 $nama=array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){	 
     $nama[$bar->karyawanid]=$bar->namakaryawan;
 }
if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')||($_SESSION['empl']['tipelokasitugas']=='KANWIL'))
{
    $str="select a.notransaksi,b.tanggal,sum(a.upahpremi) as premi, sum(a.hasilkerja) as jjg, sum(a.rupiahpenalty)as penalty,
    sum(hasilkerjakg) as kg, b.nikmandor as mandor,b.nikmandor1 as mandor1, b.nikasisten as kraniproduksi, b.keranimuat,
    sum(a.premibasis) as premibasis
    FROM ".$dbname.".kebun_prestasi a
    left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
    where a.notransaksi like '%PNN%' and b.tanggal between '".$tanggalMulai."' and '".$tanggalSampai."' 
    and a.notransaksi like '%".$lok."%'
    group by a.notransaksi";    
}
else{
	if(strlen($kdOrg)==4){
		$sortdiv="";
	}
	else{
		$sortdiv="and subbagian='".$kdOrg."' ";
	}
    $str="select a.notransaksi,b.tanggal,sum(a.upahpremi) as premi, sum(a.hasilkerja) as jjg, sum(a.rupiahpenalty)as penalty,
    sum(hasilkerjakg) as kg,b.nikmandor as mandor,b.nikmandor1 as mandor1, b.nikasisten as kraniproduksi, b.keranimuat,
    sum(a.premibasis) as premibasis
    FROM ".$dbname.".kebun_prestasi a
    left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
    where a.notransaksi like '%PNN%' and b.tanggal between '".$tanggalMulai."' and '".$tanggalSampai."' 
    and a.notransaksi like '%".$lok."%' and a.nik in(
        select karyawanid from ".$dbname.".datakaryawan where 1=1 ".$sortdiv." and lokasitugas='".$lok."'
        )
    group by a.notransaksi";
}    
$brd=0;
$bg="";
if($proses=='exce')
{
  $brd=1;  
   $bg=" bgcolor=#DEDEDE";
}
#generate header
$stream="Premi per No.Transaksi:";
$stream.="<table class=sortable cellspacing=1 border=".$brd.">
          <thead>
          <tr class=rowheader>
            <td ".$bg.">".$_SESSION['lang']['nomor']."</td>
            <td ".$bg.">".$_SESSION['lang']['notransaksi']."</td>    
            <td ".$bg.">".$_SESSION['lang']['tanggal']."</td>
            <td ".$bg.">".$_SESSION['lang']['mandor']."</td>  
            <td ".$bg.">".$_SESSION['lang']['nikmandor1']."</td> 
            <td ".$bg.">".$_SESSION['lang']['keraniafdeling']."</td>
            <td ".$bg.">".$_SESSION['lang']['keranimuat']."</td>   
            <td ".$bg.">".$_SESSION['lang']['jmlhTandan']."</td>
            <td ".$bg.">".$_SESSION['lang']['upahpremi']."</td>
            <td ".$bg.">".$_SESSION['lang']['premibasis']." (Rp)</td>    
            <td ".$bg.">".$_SESSION['lang']['rupiahpenalty']."</td>   
            <td ".$bg.">".$_SESSION['lang']['hasilkerjakg']."</td>    
          </tr>
          </thead>
          <tbody>
          ";
$no=0;
$ttandan=0;
$tpremi=0;
$tpenalty=0;
$tkg=0;

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){	
    $no+=1;
    $stream.="  <tr class=rowcontent>
                <td>".$no."</td>
                <td>".$bar->notransaksi."</td>    
                <td>".tanggalnormal($bar->tanggal)."</td>
                <td>".@$nama[$bar->mandor]."</td>  
                <td>".@$nama[$bar->mandor1]."</td> 
                <td>".@$nama[$bar->kraniproduksi]."</td>
                <td>".@$nama[$bar->keranimuat]."</td>   
                <td align=right>".$bar->jjg."</td> 
                <td align=right>".number_format($bar->premi)."</td>
                <td align=right>".number_format($bar->premibasis)."</td>
                <td align=right>".number_format($bar->penalty)."</td>   
                <td align=right>".number_format($bar->kg)."</td>    
              </tr>"; 
    @$ttandan+=$bar->jjg;
    @$tpremi +=$bar->premi;
    @$tpremibasis +=$bar->premibasis;
    @$tpenalty+=$bar->penalty;
    @$tkg+=$bar->kg;
}  
$stream.="</tbody>
          <tfoot>
          <tr class=rowcontent>
             <td colspan=7>Total</td>
             <td align=right>".$ttandan."</td>
             <td align=right>".number_format($tpremi)."</td>
             <td align=right>".number_format($tpremibasis)."</td>    
             <td align=right>".number_format($tpenalty)."</td>   
             <td align=right>".number_format($tkg)."</td>     
          </tr>
          </tfoot>
          </table>";

switch($proses)
{
	case'preview':
          echo $stream;
	break;
	case 'excel':
            $nop_="Laporan_premi_per_kemandoran_".$kdOrg."_".$periode;
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
	case'pdf':
        echo"belum tersedia"    ;
        
        break;
}    
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

//	$pt=$_POST['pt'];
        $unit=$_GET['unit'];
        $periode=$_GET['periode'];

if($periode==''){
        echo "Warning: silakan mengisi periode"; exit;
}
$str="select tanggalmulai, tanggalsampai from ".$dbname.".setup_periodeakuntansi
      where kodeorg ='".$unit."' and periode='".$periode."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $tanggalmulai=$bar->tanggalmulai;
        $tanggalsampai=$bar->tanggalsampai;
}

if($_SESSION['language']=='EN'){
    $zz=' b.namaakun1';
}
else{
    $zz='b.namaakun';
}
        $str="select a.nojurnal as nojurnal, a.tanggal as tanggal, a.keterangan as keterangan, a.noakun as noakun, ".$zz." as namaakun, a.debet as debet, a.kredit as kredit, a.kodeblok as kodeorg, a.kodevhc as kodevhc  
                  from ".$dbname.".keu_jurnaldt_vw a
                  left join ".$dbname.".keu_5akun b on a.noakun = b.noakun
                  where a.tanggal>='".$tanggalmulai."' and a.tanggal<='".$tanggalsampai."' and a.noreferensi in ('ALK_KERJA_AB') and a.kodeorg = '".$unit."' 
                  order by a.tanggal";

//echo"str :".$str;
//=================================================
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$no=0;
if($numrows<1)
{
                echo"<tr class=rowcontent><td colspan=10>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
        }
        else
        {
                $stream=$_SESSION['lang']['penerimaanalokasitraksi'].": ".$unit." : ".$periode."<br>
                <table border=1>
                                    <tr>
                          <td bgcolor=#DEDEDE align=center>No.</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['nojurnal']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['debet']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kredit']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeblok']."</td>
                          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodevhc']."</td>
                                        </tr>";
while($bar=$res->fetch())
{
                $no+=1; $total=0;
                $stream.="<tr>
                                  <td align=right>".$no."</td>
                                  <td>".$bar->nojurnal."</td>
                                  <td align=right>".$bar->tanggal."</td>
                                  <td nowrap>".$bar->keterangan."</td>
                                  <td align=right>".$bar->noakun."</td>
                                  <td nowrap>".$bar->namaakun."</td>
                                  <td align=right>".number_format($bar->debet,2)."</td>
                                  <td align=right>".number_format($bar->kredit,2)."</td>
                                  <td>".$bar->kodeorg."</td>
                                  <td>".$bar->kodevhc."</td>
                        </tr>"; 	
        }

        $stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
  }

$nop_="PenerimaanAlokasiTraksi_".$unit."_".$periode;
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
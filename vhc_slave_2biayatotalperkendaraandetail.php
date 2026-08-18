<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
?>
<script language=javascript1.2 src="js/generic.js"></script>
<script language=javascript1.2 src="js/vhc_2biayatotalperkendaraan.js?v=<?php echo time(); ?>"></script>
<?
    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  

  echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 

$param = $_POST;
if(count($param)==0){
	$param = $_GET;	
}
   
$kodevhc=$param['kodevhc'];
$tanggalmulai=$param['tanggalmulai'];
$tanggalsampai=$param['tanggalsampai'];
$unit=$param['unit'];

$periode=checkPostGet('periode','');
$noakunawal=$param['noakunawal'];
$noakunakhir=$param['noakunakhir'];

$type=checkPostGet('type','');

//=================================================

$stream="";
if($type!='excel'){
    echo"<fieldset style=float:left><label>Vehicle Cost  : ".$kodevhc." ".$_SESSION['lang']['tanggal']." : ".$tanggalmulai." s.d ".$tanggalsampai."</label>
     <img onclick=\"detailExcel(event)\" src=images/excel.jpg class=resicon title='MS.Excel'>
     <input type=hidden id=kodevhc value='".$kodevhc."' />
    <input type=hidden id=tanggalmulai value='".$tanggalmulai."' />
    <input type=hidden id=tanggalsampai value='".$tanggalsampai."' />
    <input type=hidden id=unit value='".$unit."' />
    <input type=hidden id=noakunawal value='".$noakunawal."' />
    <input type=hidden id=noakunakhir value='".$noakunakhir."' />
	</fieldset>";
    $stream.="<table class=sortable border=0 cellpadding=5 width=100% cellspacing=1>"; 
    
}else{
    $stream.="<table class=sortable border=1 cellspacing=1>";
}
$stream.="
      <thead>
        <tr class=rowcontent>
          <td bgcolor=#DEDEDE align=center>No.</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namaakun']."</th>    
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodekegiatan']."</th>    
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakegiatan']."</th>    
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</th>      
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['namakaryawan']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodeblok']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodejurnal']."</th>
          ";
        $stream.="</tr>
      </thead>
      <tbody>";
	  
	// $str="select * from ".$dbname.".datakaryawan where 
			// karyawanid in (select distinct(nik) from ".$dbname.".keu_jurnaldt_vw 
               // where tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalsampai."' 
              // and (noakun between '".$noakunawal."' and '".$noakunakhir."')
              // and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL))";
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    // $res->setFetchMode(PDO::FETCH_OBJ);
    // while($bar=$res->fetch()){
		// $namakaryawan[$bar->karyawanid]=$bar->namakaryawan;
		// $nikkaryawan[$bar->karyawanid]=$bar->nik;
	// }		
	
	$str="select * from ".$dbname.".keu_5akun";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
		$namaakun[$bar->noakun]=$bar->namaakun;
	}	
	  
	// $str="select c.satuan,a.tanggal, a.noakun, a.keterangan, a.debet as jumlah, a.kodevhc,a.nik,a.kodeblok,a.noreferensi,c.kodekegiatan,c.namakegiatan 
	$str="select a.kodejurnal,c.satuan,a.tanggal, a.noakun, a.keterangan, a.jumlah as jumlah, a.kodevhc,a.nik,a.kodeblok,a.noreferensi,c.kodekegiatan,c.namakegiatan 
              from ".$dbname.".keu_jurnaldt_vw a 
			  left join ".$dbname.".setup_kegiatan c
			  on a.kodekegiatan=c.kodekegiatan
              where kodevhc = '".$kodevhc."'
              and tanggal>='".$tanggalmulai."' and tanggal<='".$tanggalsampai."' 
              and (a.noakun between '".$noakunawal."' and '".$noakunakhir."')
              and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL)";
    $no=0;
    $total=0;
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch())
    {
        $no+=1;
        // if($bar->jumlah>0){
              $stream.="<tr class=rowcontent>
              <td align=center>".$no."</td>
              <td>".$bar->tanggal."</td>
              <td align=right>".$bar->noakun."</td>
              <td>".$namaakun[$bar->noakun]."</td>    
              <td>".$bar->kodekegiatan."</td>
              <td>".$bar->namakegiatan."</td>
                  <td>".$bar->satuan."</td>
              <td>".$bar->keterangan."</td>
              <td>".getNik($bar->nik)." - ".getNamaKaryawan($bar->nik)."</td>
              <td align=right>".number_format($bar->jumlah,2)."</td>
              <td>".$bar->kodeblok."</td>
              <td>".$bar->noreferensi."</td>
              <td>".$bar->kodejurnal."</td>";
             $stream.="</tr>";
         $total+=$bar->jumlah;
        // }          
    } 
    $stream.="<tr class=rowcontent>
              <td colspan=8 align=right>TOTAL :</td><td></td>
              <td align=right>".number_format($total,2)."</td>
              <td></td><td></td><td></td>";
         $stream.="</tr>";

   $stream.="</tbody></table>";
   if($type=='excel')
   {
$nop_="Detail_BiayaPerKendaraan_".$kodevhc."_";
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
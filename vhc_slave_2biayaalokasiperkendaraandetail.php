<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
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

$nmkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
$akunkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,noakun');
$akun=makeOption($dbname,'keu_5akun','noakun,namaakun');

setIt($param['type'],'');

$str = "select sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg from ".$dbname.".keu_jurnaldt_vw where tanggal>='".tanggalsystem($param['tglAwal'])."' and tanggal<='".tanggalsystem($param['tglAkhir'])."' and noakun='4110299' and kodevhc = '".$param['kodevhc']."' group by kodevhc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$teralokasi=$bar['jumlah']*-1;
}

##daftar memorial
$strmemo = "select nojurnal,tanggal,noakun,sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg,keterangan from ".$dbname.".keu_jurnaldt_vw where tanggal>='".tanggalsystem($param['tglAwal'])."' and tanggal<='".tanggalsystem($param['tglAkhir'])."' and noakun='4110299' and kodevhc = '".$param['kodevhc']."' and kodejurnal='M' group by kodevhc";
$resmemo=$owlPDO->query($strmemo) or die(print " Gagal: ".PDOException::getMessage());
$resmemo->setFetchMode(PDO::FETCH_OBJ);
   

$str="select a.jenispekerjaan , a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah,b.tanggal from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where kodevhc='".$param['kodevhc']."' and tanggal between '".tanggalsystem($param['tglAwal'])."' and '".tanggalsystem($param['tglAkhir'])."'";
// $res=fetchdata($str);
// $jumbaris=count($res);	
		
echo"<fieldset style=float:left><label>Detail Activity : ".$param['kodevhc']." ".$_SESSION['lang']['tanggal']." : ".$param['tglAwal']." - ".$param['tglAkhir']."</label>
     <img onclick=\"detailData(event,'vhc_slave_2biayaalokasiperkendaraandetail.php?type=excel&kodevhc=".$param['kodevhc']."&tglAwal=".$param['tglAwal']."&tglAkhir=".$param['tglAkhir']."&hrgaSatuan=".$param['hrgaSatuan']."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
     </fieldset>";
if($param['type']!='excel')
$stream="<table class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>";
else
$stream="Detail Activity :".$param['kodevhc']." ".$_SESSION['lang']['tanggal'].":".$param['tglAwal']." - ".$param['tglAkhir'].";
      <table class=sortable cellspacing=1 border=1>";
$stream.="<thead>
      <tr class=rowheader><th bgcolor=#DEDEDE align=center>No</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kegiatan']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['alokasibiaya']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</th>
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."(HM/KM)</th> 
          <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['harga']."</th>
      </tr>
      </thead>
      <tbody>";
	  
	  
$hargapersatuan=floor($teralokasi/$param['totalhm']);

$floorteralokasi=$hargapersatuan*$param['totalhm']; 
$selisihpembulatan=$teralokasi-$floorteralokasi;

$no=0;
$ttl=$total=0;

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
   //$hrg=$param['hrgaSatuan']*$bar->jumlah;
   // $hrg=floor(($teralokasi/$param['totalhm'])*$bar->jumlah);
   @$no+=1;
   
   if($no>1){
	   $selisihpembulatan=0;
   }
   
    $stream.="<tr class=rowcontent>
          <td align=center>".$no."</td>
          <td>".tanggalnormal($bar->tanggal)."</td>   
          <td>".$bar->notransaksi."</td>
          <td>".$akunkeg[$bar->jenispekerjaan]." - ".$akun[$akunkeg[$bar->jenispekerjaan]]."</td>
          <td>".$nmkeg[$bar->jenispekerjaan]."</td>";
    if(getNamaOrg($bar->alokasibiaya)!=''){		
		$stream.="<td>".getNamaOrg($bar->alokasibiaya)."</td>";
	}else{
		$stream.="<td>".$bar->alokasibiaya."</td>";
	}
	
    $stream.="<td>".$bar->keterangan."</td>    
	<td align=right>".number_format($bar->jumlah,2)."</td>
          <td align=right>".number_format(($bar->jumlah*$hargapersatuan)+$selisihpembulatan,2)."</td>
 
      </tr>";  
    $ttl+=$bar->jumlah;
    $total+=($bar->jumlah*$hargapersatuan)+$selisihpembulatan;
}
while($barm=$resmemo->fetch()){
   @$no+=1;
    $stream.="<tr class=rowcontent>
          <td align=center>".$no."</td>
          <td>".tanggalnormal($barm->tanggal)."</td>   
          <td>".$barm->nojurnal."</td>
          <td>".$barm->noakun." - ".getNamaAkun($barm->noakun)."</td>
          <td>Jurnal Memorial</td>";
    if(getNamaOrg($barm->kodeorg)!=''){		
		$stream.="<td>".getNamaOrg($barm->kodeorg)."</td>";
	}else{
		$stream.="<td>".$barm->kodeorg."</td>";
	}
	
    $stream.="<td>".$barm->keterangan."</td>    
	    <td align=right></td>
        <td align=right>".number_format($barm->jumlah,2)."</td>
      </tr>";  
    $total+=$barm->jumlah;
}//<td align=right>".number_format($hrg,2)."</td>
    $stream.="<tr class=rowcontent>
          <td colspan=7 align=center>Total</td> 
          <td align=right>".$ttl."</td>
          <td align=right>".number_format($total,2)."</td>
      </tr>"; 
$stream.="</tbody><tfoot></tfoot></table>";
 if($param['type']=='excel')
   {
$nop_="Detail_BiayaAlokasi_".$param['kodevhc'];
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

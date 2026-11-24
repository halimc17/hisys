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

$str = "select sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg from ".$dbname.".keu_jurnaldt_vw where tanggal>='".tanggalsystem($param['tglAwal'])."' and tanggal<='".tanggalsystem($param['tglAkhir'])."' and noakun='4110299' and kodevhc = '".$param['kodevhc']."' and kodejurnal!='M' group by kodevhc ";
#jurnal memorial tidak ikut dibagi ke kolom Harga karena sudah ditampilkan sebagai baris sendiri (sebelumnya terhitung dua kali)
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$teralokasi=$bar['jumlah']*-1;
}

##daftar memorial
$strmemo = "select nojurnal,tanggal,noakun,sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg,keterangan from ".$dbname.".keu_jurnaldt_vw where tanggal>='".tanggalsystem($param['tglAwal'])."' and tanggal<='".tanggalsystem($param['tglAkhir'])."' and noakun='4110299' and kodevhc = '".$param['kodevhc']."' and kodejurnal='M' group by kodevhc";
$resmemo=$owlPDO->query($strmemo) or die(print " Gagal: ".PDOException::getMessage());
$resmemo->setFetchMode(PDO::FETCH_OBJ);
   

$str="select a.jenispekerjaan , a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah,b.tanggal from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where kodevhc='".addslashes($param['kodevhc'])."' and tanggal between '".tanggalsystem($param['tglAwal'])."' and '".tanggalsystem($param['tglAkhir'])."' order by b.tanggal asc,a.notransaksi asc";
// $res=fetchdata($str);
// $jumbaris=count($res);

##Rp Jurnal: nilai jurnal Proses Alokasi Traksi (ALK_KERJA_AB; periode lama juga alokasi gaji traksi ALK_TRK_GYMH) per baris, untuk dibandingkan dengan kolom Harga
##dicocokkan per no transaksi + kegiatan jurnal (setupkegiatan, kosong untuk project AK/pabrikasi PB) + alokasi (blok kecil dikembalikan ke blok induk)
##jurnal lama tanpa no transaksi dicocokkan per kegiatan + alokasi untuk seluruh periode; bila satu kunci punya beberapa baris, dibagi menurut jam
$dataInput=fetchData($str,'OBJECT');
$setupkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,setupkegiatan');
$tgAwalAlk=tanggalsystem($param['tglAwal']);
$tgAkhirAlk=tanggalsystem($param['tglAkhir']);
$kodevhcAlk=addslashes($param['kodevhc']);
$strj="select nodok,kodekegiatan,kodeblok,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where noreferensi in ('ALK_KERJA_AB','ALK_TRK_GYMH') and kodevhc='".$kodevhcAlk."' and tanggal>='".$tgAwalAlk."' and tanggal<='".$tgAkhirAlk."' and jumlah>0 and kodeblok!='' group by nodok,kodekegiatan,kodeblok";
$dataJurnal=fetchData($strj);
$perPeriode=false;
foreach($dataJurnal as $j){
	if(trim($j['nodok'])==''){
		$perPeriode=true;
	}
}
$indukAlk=array();
$strb="select kodeorg,indukblok from ".$dbname.".setup_blok where indukblok!='' and indukblok in (select a.alokasibiaya from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where b.kodevhc='".$kodevhcAlk."' and b.tanggal between '".$tgAwalAlk."' and '".$tgAkhirAlk."')";
foreach(fetchData($strb) as $b){
	$indukAlk[$b['kodeorg']]=$b['indukblok'];
}
function kunciJurnalAlk($notransaksi,$jenispekerjaan,$alokasi,$perPeriode,$setupkeg){
	$jp=trim($jenispekerjaan);
	$keg=(substr($alokasi,0,2)=='AK' || substr($alokasi,0,2)=='PB') ? '' : (isset($setupkeg[$jp]) ? trim($setupkeg[$jp]) : '');
	return ($perPeriode ? '' : $notransaksi).'|'.$keg.'|'.$alokasi;
}
$alokasiAlk=array();
$jamKunciAlk=array();
foreach($dataInput as $bar){
	$alokasiAlk[$perPeriode ? '' : $bar->notransaksi][$bar->alokasibiaya]=1;
	$k=kunciJurnalAlk($bar->notransaksi,$bar->jenispekerjaan,$bar->alokasibiaya,$perPeriode,$setupkeg);
	$jamKunciAlk[$k]=(isset($jamKunciAlk[$k]) ? $jamKunciAlk[$k] : 0)+$bar->jumlah;
}
$rpJurnalAlk=array();
foreach($dataJurnal as $j){
	$nt=$perPeriode ? '' : trim($j['nodok']);
	$alok=$j['kodeblok'];
	if(!isset($alokasiAlk[$nt][$alok]) && isset($indukAlk[$alok]) && isset($alokasiAlk[$nt][$indukAlk[$alok]])){
		$alok=$indukAlk[$alok];
	}
	$k=$nt.'|'.trim($j['kodekegiatan']).'|'.$alok;
	$rpJurnalAlk[$k]=(isset($rpJurnalAlk[$k]) ? $rpJurnalAlk[$k] : 0)+$j['jumlah'];
}

echo"<fieldset style=float:left><label>Detail Activity : ".$param['kodevhc']." ".$_SESSION['lang']['tanggal']." : ".$param['tglAwal']." - ".$param['tglAkhir']."</label>
     <img onclick=\"detailData(event,'vhc_slave_2biayaalokasiperkendaraandetail.php?type=excel&kodevhc=".$param['kodevhc']."&tglAwal=".$param['tglAwal']."&tglAkhir=".$param['tglAkhir']."&hrgaSatuan=".$param['hrgaSatuan']."&totalhm=".(isset($param['totalhm'])?(float)$param['totalhm']:0)."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
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
          <th bgcolor=#DEDEDE align=center>Rp Jurnal</th>
          <th bgcolor=#DEDEDE align=center>Selisih</th>
      </tr>
      </thead>
      <tbody>";
	  
	  
#Harga dihitung dengan cara yang sama dengan Proses Alokasi Traksi (ALKJAM), supaya selisih dengan jurnal hanya karena jam/kegiatan:
#- jam yang ikut dialokasikan: ada alokasi, ada kegiatan, jam > 0; total jam dari baris-baris itu
#- per no transaksi + kegiatan + alokasi: biaya = bulat ke bawah (jam / total jam x teralokasi); sisa pembulatan masuk ke kelompok terakhir
#- kelompok yang berisi beberapa baris dibagi menurut jam
$teralokasi=isset($teralokasi)?$teralokasi:0;
$totalhm=0;
$jamKelompokAlk=array();
foreach($dataInput as $bar){
	if($bar->jumlah>0 && $bar->alokasibiaya!='' && trim($bar->jenispekerjaan)!=''){
		$kk=$bar->notransaksi.'|'.trim($bar->jenispekerjaan).'|'.$bar->alokasibiaya;
		$jamKelompokAlk[$kk]=(isset($jamKelompokAlk[$kk]) ? $jamKelompokAlk[$kk] : 0)+$bar->jumlah;
		$totalhm+=$bar->jumlah;
	}
}
$hargaKelompokAlk=array();
$sisaAlk=$teralokasi;
$urutAlk=0;
foreach($jamKelompokAlk as $kk=>$jamK){
	$urutAlk++;
	$hk=($totalhm>0) ? floor(($jamK/$totalhm)*$teralokasi) : 0;
	if($urutAlk==count($jamKelompokAlk) || ($sisaAlk-$hk)<=0){
		$hk=$sisaAlk;
	}
	$hargaKelompokAlk[$kk]=$hk;
	$sisaAlk-=$hk;
}

$no=0;
$ttl=$total=$totalJurnal=$totalSelisih=0;

foreach($dataInput as $bar){
   //$hrg=$param['hrgaSatuan']*$bar->jumlah;
   // $hrg=floor(($teralokasi/$param['totalhm'])*$bar->jumlah);
   @$no+=1;

   $kk=$bar->notransaksi.'|'.trim($bar->jenispekerjaan).'|'.$bar->alokasibiaya;
   $harga=(isset($hargaKelompokAlk[$kk]) && $jamKelompokAlk[$kk]>0 && $bar->jumlah>0) ? $hargaKelompokAlk[$kk]*$bar->jumlah/$jamKelompokAlk[$kk] : 0;
   $k=kunciJurnalAlk($bar->notransaksi,$bar->jenispekerjaan,$bar->alokasibiaya,$perPeriode,$setupkeg);
   $rpJurnal=(isset($rpJurnalAlk[$k]) && $jamKunciAlk[$k]>0) ? $rpJurnalAlk[$k]*$bar->jumlah/$jamKunciAlk[$k] : 0;
   $selisih=$rpJurnal-$harga;

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
          <td align=right>".number_format($harga,2)."</td>
          <td align=right>".number_format($rpJurnal,2)."</td>
          <td align=right>".number_format($selisih,2)."</td>

      </tr>";
    $ttl+=$bar->jumlah;
    $total+=$harga;
    $totalJurnal+=$rpJurnal;
    $totalSelisih+=$selisih;
}
##jurnal yang tidak punya pasangan di input (mis. kode kegiatan di jurnal berbeda dengan setup kegiatan sekarang)
$sisaJurnal=0;
foreach($rpJurnalAlk as $k=>$rp){
	if(!isset($jamKunciAlk[$k]) || $jamKunciAlk[$k]<=0){
		$sisaJurnal+=$rp;
	}
}
if(abs($sisaJurnal)>=0.005){
	@$no+=1;
	$stream.="<tr class=rowcontent>
          <td align=center>".$no."</td>
          <td colspan=8>Jurnal Alokasi Traksi yang tidak ada pasangannya di input (kode kegiatan/alokasi berbeda)</td>
          <td align=right>".number_format($sisaJurnal,2)."</td>
          <td align=right>".number_format($sisaJurnal,2)."</td>
      </tr>";
	$totalJurnal+=$sisaJurnal;
	$totalSelisih+=$sisaJurnal;
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
        <td align=right>".number_format($barm->jumlah,2)."</td>
        <td align=right>0.00</td>
      </tr>";
    $total+=$barm->jumlah;
    $totalJurnal+=$barm->jumlah;
}//<td align=right>".number_format($hrg,2)."</td>
    $stream.="<tr class=rowcontent>
          <td colspan=7 align=center>Total</td>
          <td align=right>".$ttl."</td>
          <td align=right>".number_format($total,2)."</td>
          <td align=right>".number_format($totalJurnal,2)."</td>
          <td align=right>".number_format($totalSelisih,2)."</td>
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

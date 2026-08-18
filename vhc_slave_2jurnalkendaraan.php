<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
// error_reporting(0);

$unit           = checkPostGet('unit','');
$tglAwal        = tanggalsystem(checkPostGet('tglAwal',''));
$tglAkhir       = tanggalsystem(checkPostGet('tglAkhir',''));
$periode        = checkPostGet('periode','');
$jenis          = checkPostGet('jenis','');
$kodevhc        = trim(checkPostGet('kodevhc',''));
$periode        = substr(tanggalsystemn(checkPostGet('tglAwal','')),0,7);
$param          =$_POST;if(count($param)==0){$param = $_GET;}
$jenisVhc       =  makeOption($dbname, 'vhc_5master', 'kodevhc,jenisvhc');
$nopol          =  makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');
$detail         =  makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');
$tahunperolehan =  makeOption($dbname, 'vhc_5master', 'kodevhc,tahunperolehan');
$akunkeg        =  makeOption($dbname,'vhc_kegiatan','kodekegiatan,noakun');




if($unit==''){
    echo"Warningsystem : Unit Kerja harus dipilih";exit();
}
if($tglAwal==''&&$tglAkhir==''){
	echo"Warningsystem : Tanggal harus dipilih"; exit;
}


#and kodeorg='".substr($unit,0,4)."'
$str = "select sum(debet)-sum(kredit) as jumlah,kodevhc,kodeorg from ".$dbname.".keu_jurnaldt_vw where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and noakun='4110299' and (kodevhc in (select kodevhc from ".$dbname.".vhc_5master_hist where  kodetraksi = '".$unit."' and periode='".$periode."') or kodevhc='') group by kodevhc ";   
//echo $str;  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$teralokasi[$bar['kodevhc']]=$bar['jumlah']*-1;
}


#=========================================================
#4.5 ambilnoakun biaya kendaraan
$akunkdari='';
$akunksampai='';
$strh="select distinct noakundebet,sampaidebet  from ".$dbname.".keu_5parameterjurnal where  jurnalid='LPVHC'";
$resh=$owlPDO->query($strh) or die(print " Gagal: ".PDOException::getMessage());
$resh->setFetchMode(PDO::FETCH_OBJ);
while($barh=$resh->fetch()){
	$akunkdari=$barh->noakundebet;
	$akunksampai=$barh->sampaidebet;
}
if($akunkdari=='' or $akunksampai==''){
	exit("Error: Journal parameter for LPVHC(vehicle cost) not exist");
}
  
if($jenis=='excel'){
	$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=1>
	     <thead>
		    <tr>
			  <th align=center>No.</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['jenisvch']."</th>
			  <th align=center>".$_SESSION['lang']['kodevhc']."</th>
			  <th align=center>".$_SESSION['lang']['nopol']."</th>
			  <th align=center>".$_SESSION['lang']['detail']."</th>
			  <th align=center style='width:50px;'>".$_SESSION['lang']['tahunperolehan']."</th>   
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jumlah']."</th>
			  <th align=center style='width:100px;'>".$_SESSION['lang']['jmljamkerja']."</th>  
			  <th align=center style='width:100px;'>Price / Unit</th>    
			  <th align=center>".$_SESSION['lang']['alokasirp']."</th>
			  <th align=center>".$_SESSION['lang']['blmAlokasi']."<br>(Rp)</th>
			</tr>  
		 </thead>
		 <tbody id=container>
		 ";
}  

if($unit != ''){
    $whrunit = "and b.kodeorg = '".substr($unit,0,4)."'";
}else{
    $whrunit = '';
}
  
if($kodevhc != ''){
    $whrvhc = "and b.kodevhc = '".$kodevhc."'";
}else{
    $whrvhc = '';
}
  
if($param['alokasi'] != ''){
    $whralk = "and a.alokasibiaya = '".$param['alokasi']."'";
}else{
    $whralk = '';
}
  
if($param['kegiatan'] != ''){
    $whrkeg = "and a.jenispekerjaan = '".$param['kegiatan']."'";
}else{
    $whrkeg = '';
}
$str="select a.jenispekerjaan , a.notransaksi,a.alokasibiaya,a.keterangan,a.jumlah,b.tanggal,b.kodevhc from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where 1=1 ".$whrvhc." ".$whralk." ".$whrkeg." ".$whrunit." and tanggal between '".tanggalsystem($param['tglAwal'])."' and '".tanggalsystem($param['tglAkhir'])."'";

// echo"<fieldset style=float:left><label>Detail Activity : ".$param['kodevhc']." ".$_SESSION['lang']['tanggal']." : ".$param['tglAwal']." - ".$param['tglAkhir']."</label>
// <img onclick=\"detailData(event,'vhc_slave_2biayaalokasiperkendaraandetail.php?type=excel&kodevhc=".$param['kodevhc']."&tglAwal=".$param['tglAwal']."&tglAkhir=".$param['tglAkhir']."&hrgaSatuan=".$param['hrgaSatuan']."')\" src=images/excel.jpg class=resicon title='MS.Excel'>
// </fieldset>";
if($jenis!='excel')
$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>";
else
$tab="Detail Activity :".$param['kodevhc']." ".$_SESSION['lang']['tanggal'].":".$param['tglAwal']." - ".$param['tglAkhir'].";
 <table class=sortable cellspacing=1 border=1>";
$tab.="<thead>
 <tr class=rowheader><th bgcolor=#DEDEDE align=center>No</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['tanggal']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['notransaksi']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['noakun']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kegiatan']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['kodevhc']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['alokasibiaya']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['keterangan']."</th>
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['jumlah']."(HM/KM)</th> 
     <th bgcolor=#DEDEDE align=center>".$_SESSION['lang']['harga']."</th>
 </tr>
 </thead>
 <tbody>";

if(count(fetchData($str))==0){
     exit("Warningsystem :  ".$_SESSION['lang']['errdatanotexist']);
}else{
    #ambil jumlah jam per kendaraan
    $str1="select sum(jumlah) as jumlah,kodevhc from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where tanggal>='".$tglAwal."' and tanggal<='".$tglAkhir."' and b.kodeorg='".substr($unit,0,4)."' ".$whrvhc." group by kodevhc";
    $jumlahjam=Array();
    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$res1->fetch()){
        @$jumlahjam[$bar1->kodevhc] += $bar1->jumlah;
    }
    
    $no=0;
    $ttl=$total=0;

    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
    //$hrg=$param['hrgaSatuan']*$bar->jumlah;
    // $hrg=floor(($teralokasi/$param['totalhm'])*$bar->jumlah);
    @$no+=1;
    $hargapersatuan=@fixnan(floor($teralokasi[$kodevhc]/$jumlahjam[$bar->kodevhc]));
    $floorteralokasi=$hargapersatuan*$jumlahjam[$bar->kodevhc]; 
    $selisihpembulatan=$teralokasi[$kodevhc]-$floorteralokasi;


    if($no>1){
    $selisihpembulatan=0;
    }

    $tab.="<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td>".tanggalnormal($bar->tanggal)."</td>   
        <td>".$bar->notransaksi."</td>
        <td>".$akunkeg[$bar->jenispekerjaan]." - ".getNamaAkun($akunkeg[$bar->jenispekerjaan])."</td>
        <td>".$bar->jenispekerjaan." - ".getNamaKegVhc($bar->jenispekerjaan)."</td>
        <td>".$bar->kodevhc." - ".getVhc($bar->kodevhc,'detailvhc')."</td>";
    if(getNamaOrg($bar->alokasibiaya)!=''){		
    $tab.="<td>".getNamaOrg($bar->alokasibiaya)."</td>";
    }else{
    $tab.="<td>".$bar->alokasibiaya."</td>";
    }

    $tab.="<td>".$bar->keterangan."</td>    
    <td align=right>".number_format($bar->jumlah,2)."</td>
        <td align=right>".number_format(($bar->jumlah*$hargapersatuan)+$selisihpembulatan,2)."</td>

    </tr>";  
    $ttl+=$bar->jumlah;
    $total+=($bar->jumlah*$hargapersatuan)+$selisihpembulatan;
    }//<td align=right>".number_format($hrg,2)."</td>
    $tab.="<tr class=rowcontent style='background-color:#ccc'>
        <td colspan=8 align=center><b>".$_SESSION['lang']['total']."</b></td> 
        <td align=right><b>".number_format($ttl,2)."</b></td>
        <td align=right><b>".number_format($total,2)."</b></td>
    </tr>"; 
    $tab.="</tbody><tfoot></tfoot></table>";
    if($jenis=='excel'){
        $tab.="</tbody>
            <tfoot>
            </tfoot>		 
        </table>";
        $tab = $tab;
        $nop_ = "Laporan_Jurnal_Kendaraan_" . $tglAwal ."_s.d_".$tglAkhir;
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
                echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            closedir($handle);
        }   
        
    }else{
        echo $tab;
    }
}
?>
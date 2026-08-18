<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$bayar=checkPostGet('bayar','');
$veriv=checkPostGet('veriv','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));


if($tgl1=='--' || $tgl2=='--'){
	exit("Warning:Lengkapi data");
}
$where='';
if($unit!=''){
	$where.=" and kodeorg='".$unit."' ";
}
if($veriv=='0'){
	$where.=" and posting='0' ";
}else{
	$where.=" and posting='1' ";
}

if($bayar=='0'){
	$where.=" and jumlahkasbank='0' ";
}else{
	$where.=" and jumlahkasbank > '0' ";
}






$nmkar=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmrs=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$arrklaim=array("0"=>"Karyawan","1"=>"Rumah Sakit","2"=>"Internal Clinic");
$namaBiaya = makeOption($dbname, 'sdm_5jenisbiayapengobatan', 'kode,nama');


if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1 >";
} else {
    $stream.= "<table class=sortable cellspacing=1  width=150%>";
}


$stream.="<thead><tr class=rowcontent>
	<td align=center>".$_SESSION['lang']['nomor']."</td>
	<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
	<td align=center>" . $_SESSION['lang']['periode'] . "</td>
	<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
	<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
	<td align=center>" . $_SESSION['lang']['rumahsakit'] . "</td>
	<td align=center>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
	<td align=center>" . $_SESSION['lang']['klaim'] . "</td>
	<td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['perusahaan'] . "</td>    
	<td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['karyawan'] . "</td>
	<td align=center>" . $_SESSION['lang']['beban'] . " " . $_SESSION['lang']['jms'] . "</td>    
	<td align=center>" . $_SESSION['lang']['total'] . "</td>
	<td align=center>Verifikasi HRD</td>
	<td align=center>" . $_SESSION['lang']['tanggal'] . " Ver HRD</td>
	<td align=center>Diinput Oleh</td>	   
	<td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
	<td align=center>" . $_SESSION['lang']['tanggalbayar'] . "</td>
	<td align=center>" . $_SESSION['lang']['notransaksi'] . "<br>" . $_SESSION['lang']['kasbank'] . "</td>
	
</tr></thead>";

$str="select * from ".$dbname.".sdm_pengobatanht where tanggal between '".$tgl1."' and '".$tgl2."' ".$where." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$no++;
	$stream.="<tr class=rowcontent>";
		$stream.="<td align=center>".$no."</td>";
		$stream.="<td align=left>".$bar['notransaksi']."</td>";
		$stream.="<td align=left>".$bar['periode']."</td>";
		$stream.="<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
		$stream.="<td align=left>".$nmkar[$bar['karyawanid']]."</td>";
		$stream.="<td align=left>".$nmrs[$bar['rs']]."</td>";
		$stream.="<td align=left>".$namaBiaya[$bar['kodebiaya']]."</td>";
		$stream.="<td align=left>".$arrklaim[$bar['klaimoleh']]."</td>";
		$stream.="<td align=right>".number_format($bar['bebanperusahaan'])."</td>";
		$stream.="<td align=right>".number_format($bar['bebankaryawan'])."</td>";
		$stream.="<td align=right>".number_format($bar['bebanjamsostek'])."</td>";
		$stream.="<td align=right>".number_format($bar['totalklaim'])."</td>";
		$stream.="<td align=right>".number_format($bar['jlhbayar'])."</td>";
		$stream.="<td align=left>".tanggalnormal($bar['tanggalbayar'])."</td>";
		$stream.="<td align=left>".$nmkar[$bar['updateby']]."</td>";
		$stream.="<td align=right>".$bar['jumlahkasbank']."</td>";
		$stream.="<td align=left>".tanggalnormal($bar['tanggalkasbank'])."</td>";
		$stream.="<td align=left>".$bar['kasbank']."</td>";
		
	$stream.="</tr>";
	$tperusahaan+=$bar['bebanperusahaan'];
	$tkary+=$bar['bebankaryawan'];
	$tjms+=$bar['bebanjamsostek'];
	$tklaim+=$bar['totalklaim'];
	$tver+=$bar['jlhbayar'];
	$tkas+=$bar['jumlahkasbank'];
}	

$stream.="<tr class=rowcontent>";
		$stream.="<td align=center colspan=8>" . $_SESSION['lang']['total'] . "</td>";
		
		$stream.="<td align=right>".number_format($tperusahaan)."</td>";
		$stream.="<td align=right>".number_format($tkary)."</td>";
		$stream.="<td align=right>".number_format($tjms)."</td>";
		$stream.="<td align=right>".number_format($tklaim)."</td>";
		$stream.="<td align=right>".number_format($tver)."</td>";
		$stream.="<td align=left colspan=2></td>";
		$stream.="<td align=right>".$tkas."</td>";
		$stream.="<td align=left colspan=2></td>";
	$stream.="</tr>";
				
	


$stream.="<tbody></table>";
switch($proses){
	
######PREVIEW
    case 'preview':
        echo $stream;
    break;

######EXCEL	
    case 'excel':
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg=date("Ymd");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="laporan_rekap_pengobatan";
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
}
?>
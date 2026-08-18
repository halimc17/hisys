<?php 
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

error_reporting(0);


$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$divisi=checkPostGet('divisi','');
$tipekar=checkPostGet('tipekar','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

$nmtipekar=  makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');


$jbtn= makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');


$arrtgl = rangeTanggalarr($tgl1, $tgl2);
$tarrtgl=count($arrtgl);



$tgl1_=substr($tgl1,0,7);
$tgl2_=substr($tgl2,0,7);

if ($tipekar!='') {
	$tpkary=" and tipekaryawan ='".$tipekar."' ";
}else{
	$tpkary=" and tipekaryawan in ('1','2','3','4','5','6') ";
}

if ($divisi!='') {
	$div=" and subbagian ='".$divisi."' ";
}else{
	$div="";
}

if ($tgl1_ != $tgl2_) {
	echo"Error: Tanggal harus di bulan yang sama"; exit;
}


// Check datakaryawan dan datakaryawan_bulanan
// $str1="select * from ".$dbname.".datakaryawan where lokasitugas = '".$unit."' ".$div." ".$tpkary." order by subbagian,namakaryawan asc ";
// $res1=fetchData($str1);

// $str="select * from ".$dbname.".datakaryawan_bulanan where lokasitugas = '".$unit."' ".$div." ".$tpkary." and periode='".$tgl1_."' order by subbagian,namakaryawan asc ";
// $res=fetchData($str);

#= query datakaryawan
//$str="select * from ".$dbname.".datakaryawan where lokasitugas = '".$unit."' ".$div." ".$tpkary." order by subbagian,namakaryawan asc ";	

$str="select * from ".$dbname.".datakaryawan_hist where lokasitugas = '".$unit."' ".$div." ".$tpkary." and periodegaji='".$tgl1_."' and version_type='B' and approval_status='8' order by subbagian,namakaryawan asc ";	
$resx=fetchData($str);

if(count($resx) > 0) {
	$str="select * from ".$dbname.".datakaryawan_hist where lokasitugas = '".$unit."' ".$div." ".$tpkary." and periodegaji='".$tgl1_."' and version_type='B' and approval_status='8' order by subbagian,namakaryawan asc ";	
	$res=fetchData($str);
} else {
	$str="select * from ".$dbname.".datakaryawan where lokasitugas = '".$unit."' ".$div." ".$tpkary." order by subbagian,namakaryawan asc ";	
	$res=fetchData($str);
}

foreach($res as $bar){
	$arrkarid[$bar['karyawanid']]=$bar['karyawanid'];
	$nikkary[$bar['karyawanid']]=$bar['nik'];
	$nmkary[$bar['karyawanid']]=$bar['namakaryawan'];
	$subbag[$bar['karyawanid']]=$bar['subbagian'];
	$kdjbtn[$bar['karyawanid']]=$bar['kodejabatan'];
	$tipekaryawan[$bar['karyawanid']]=$bar['tipekaryawan'];
	// @$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['premi'];
	// $trppremi[$bar['karyawanid']]+=$bar['premi'];
	// @$tallrppremi+=$bar['premi'];

}

#= sdm_absensidt_vw
$str="select * from ".$dbname.".sdm_absensidt_vw where karyawanid in ('".implode("','",$arrkarid)."')
	and tanggal between '".$tgl1."' and '".$tgl2."'  ";	
$res=fetchData($str);
foreach($res as $bar){
	if($bar['premi']+ $bar['insentif'] + $bar['insentiflibur']>0){		
		$arrkaridx[$bar['karyawanid']]=$bar['karyawanid'];
	}
	@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['premi']+ $bar['insentif'] + $bar['insentiflibur'];
	@$arrjumlahabsensi[$bar['karyawanid']][$bar['tanggal']] += $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
}




#= kebun_kehadiran_vw
$str = "select * from " . $dbname . ".kebun_kehadiran_vw where karyawanid in ('".implode("','",$arrkarid)."')
			and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['insentif']>0){		
		$arrkaridx[$bar['karyawanid']]=$bar['karyawanid'];
	}
	@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['insentif'];
	@$arrjumlahbkm[$bar['karyawanid']][$bar['tanggal']] += $bar['insentif'];

}
	

#= kebun_prestasi_vs_hk
$str = "select * from " . $dbname . ".kebun_prestasi_vs_hk where karyawanid in ('".implode("','",$arrkarid)."')
			and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['tpremi']>0){		
		$arrkaridx[$bar['karyawanid']]=$bar['karyawanid'];
	}
	@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['tpremi'];
	@$arrjumlahpanen[$bar['karyawanid']][$bar['tanggal']] += $bar['upahpremi'] + $bar['premibasis'] + $bar['upahpremilebihbasis'];

}

#= vhc_runhk_vw
$str = "select * from " . $dbname . ".vhc_runhk_vw where idkaryawan in ('".implode("','",$arrkarid)."')
			and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['premi']>0){		
		$arrkaridx[$bar['idkaryawan']]=$bar['idkaryawan'];
	}
	@$rppremi[$bar['idkaryawan']][$bar['tanggal']]+=$bar['premi'];
	@$arrjumlahtraksi[$bar['idkaryawan']][$bar['tanggal']] += $bar['premi'];
}

#= kebun_premikemandoran
$str = "select * from " . $dbname . ".kebun_premikemandoran where karyawanid in ('".implode("','",$arrkarid)."') and periode like '".substr($tgl1,0,7)."%'";	
$res=fetchData($str);
foreach($res as $bar){
	if($bar['premiinput']>0){		
		$arrkaridx[$bar['karyawanid']]=$bar['karyawanid'];
	}
	@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['premiinput'];
	@$arrjumlahpmandor[$bar['karyawanid']][$bar['tanggal']] += $bar['premiinput']; 
}

#= vhc_spl_kehadiran_vw
$str = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where nik in ('".implode("','",$arrkarid)."')
			and tanggal between '".$tgl1."' and '".$tgl2."'  ";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['premi']>0){
		$arrkaridx[$bar['nik']]=$bar['nik'];
	}
	@$rppremi[$bar['nik']][$bar['tanggal']]+=$bar['premi'];
	@$arrjumlahbkm[$bar['nik']][$bar['tanggal']] += $bar['premi'];
}

#= sdm_uangmakandanextrafooding(Extra Fooding, UM)
$str = "select * from " . $dbname . ".sdm_uangmakandanextrafooding
	WHERE karyawanid in ('".implode("','",$arrkarid)."') AND tanggal between '".$tgl1."' and '".$tgl2."'";
$res=fetchData($str);
foreach($res as $bar){
	if($bar['jumlah']>0){		
		$arrkaridx[$bar['karyawanid']]=$bar['karyawanid'];
	}
	@$rppremi[$bar['karyawanid']][$bar['tanggal']]+=$bar['jumlah'];
	@$arrjumlahUMF[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlah'];
}






// array_multisort($kddivisi,SORT_ASC);

$stream = "";
if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
}else if($proses == 'printpdf'){
	$optdivisi 	=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$divisi."'");
	$arrHead 	=setheadreport(substr($unit,0,4));
	$str = "SELECT tipe FROM " . $dbname . ".sdm_5tipekaryawan WHERE id='".$tipekar."' ";
	$res=fetchData($str);
	foreach($res as $bar){
	@$arrtipekar=$bar['tipe'];
}

	// if($tipekar == '1'){
	// 	$tipekar = 'PEGAWAI BULANAN (PB)';
	// }elseif ($tipekar == '2') {
	// 	$tipekar = 'PEGAWAI BULANAN ISTIMEWA (PBI)';
	// }elseif ($tipekar == '3') {
	// 	$tipekar = 'KHT/ SKU';
	// }elseif ($tipekar == '4') {
	// 	$tipekar = 'KHL/ BHL';
	// }else{
	// 	$tipekar = 'Seluruh Tipe Karyawan';
	// }
	$stream.= "<table class=sortable cellspacing=0 style=font-size:14px; border=0>";
		$stream.= "<tr>";
			$stream.= "<td rowspan=4><img style=width:80px; src=images/ksp.jpg></td>";
			$stream.= "<td style=width:30px;> </td>";
			$stream.= "<td><b>".$arrHead['nama']." - ".$unit."</b></td>";
		$stream.= "</tr>";
		$stream.= "<tr>";
			$stream.= "<td></td>";
			if($divisi == ''){
				$stream.="<td>Seluruh Divisi</td>";
			}else{
				$stream.= "<td>".$optdivisi[$divisi]."</td>";
			}
		$stream.= "</tr>";
		$stream.= "<tr>";
			$stream.= "<td></td>";
			$stream.= "<td>".$tipekar."</td>";
		$stream.= "</tr>";
		$stream.= "<tr>";
			$stream.= "<td></td>";
			$stream.= "<td>".date('d F Y', strtotime($tgl1))." s/d ".date('d F Y', strtotime($tgl2))."</td>";
		$stream.= "</tr>";
	$stream.= "</table>";
	$stream.= "<h3 align=center><b><u>Laporan Premi</u></b></h3>";
	$stream.= "<table class=sortable cellspacing=0 border=1 style=font-size:12px;>";
} else {
    $stream.= "<table class='sortable' cellspacing='1' cellpadding=5>";
}

$stream.="<thead><tr style=cursor:pointer;  title='click untuk melihat detail'class=rowcontent>";
	  $stream.="
        <th align=center >No</th>
        <th align=center >".$_SESSION['lang']['subbagian']."</th>
        <th align=center >".$_SESSION['lang']['nik']."</th>
		<th align=center >".$_SESSION['lang']['nama']."</th>
        <th align=center >".$_SESSION['lang']['tipekaryawan']."</th>
        <th align=center >".$_SESSION['lang']['jabatan']."</th>
        <th align=center >".$_SESSION['lang']['sumber']."</th>";
        foreach($arrtgl as $ar => $isi){
        	$qwe=date('D', strtotime($isi));
        	$stream.="<th width=5px align=center>";
        	if($qwe=='Sun') $stream.="<font color=red>".substr($isi,8,2)."</font>"; else  $stream.=" ".(substr($isi,8,2))." " ; 
        	$stream.="</th>";
        }
        $stream.="<th align=center><b>".$_SESSION['lang']['jumlah']."</b></th>";
$stream.="</tr>
</thead>
<tbody>";

$gtpremi=0;
$no=1;
$gttanggal=array();
foreach ($arrkaridx as $karyid) {
	$stream.="
	<tr style=cursor:pointer title='click untuk melihat detail' onclick=loadDetail('".$nikkary[$karyid]."') class='rowcontent'>
	<td  align=center>".$no++."</td>
	<td nowrap>".$subbag[$karyid]."</td>
	<td nowrap>".$nikkary[$karyid]."</td>
	<td nowrap>".$nmkary[$karyid]."</td>
	<td nowrap>".$nmtipekar[$tipekaryawan[$karyid]]."</td>
	<td nowrap>".$jbtn[$kdjbtn[$karyid]]."</td>
	<td nowrap>".$_SESSION['lang']['total']."/ ".$_SESSION['lang']['hari']." </td>";
	foreach($arrtgl as $ar => $isi){	
		$stream.="<td align=right>".number_format($rppremi[$karyid][$isi])."</td>"; 
		@$trppremi[$karyid]+=$rppremi[$karyid][$isi];
		$gttanggal[$isi]+=$rppremi[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trppremi[$karyid])."</b></td>";
	$gtpremi+=$trppremi[$karyid];
	$stream.="</tr>";

#=RINCIAN DATA TOTAL
//Data BKM
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>BKM</td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahbkm[$karyid][$isi])."</td>"; 
		@$trppbkm[$karyid]+=$arrjumlahbkm[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trppbkm[$karyid])."</b></td>";
	$stream.="</tr>";

//Data Panen
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>Panen</td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahpanen[$karyid][$isi])."</td>"; 
		@$trpppanen[$karyid]+=$arrjumlahpanen[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trpppanen[$karyid])."</b></td>";
	$stream.="</tr>";

//Data Traksi
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>Traksi</td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahtraksi[$karyid][$isi])."</td>"; 
		@$trpptraksi[$karyid]+=$arrjumlahtraksi[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trpptraksi[$karyid])."</b></td>";
	$stream.="</tr>";

//Data SDM - Absensi
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>SDM - Absensi </td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahabsensi[$karyid][$isi])."</td>"; 
		@$trppabsensi[$karyid]+=$arrjumlahabsensi[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trppabsensi[$karyid])."</b></td>";
	
	$stream.="</tr>";

//Extra Fooding, UM
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>Extra Fooding, UM </td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahUMF[$karyid][$isi])."</td>"; 
		@$trppefood[$karyid]+=$arrjumlahUMF[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trppefood[$karyid])."</b></td>";
	
	$stream.="</tr>";

//Premi Mandor
	$stream.="
	<tr style='display:none' class='rowcontent ".$nikkary[$karyid]."'>
	<td align=center></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>";
	$stream.="<td>Premi Mandor</td>";
	foreach($arrtgl as $ar => $isi){	
		// $col++;
		$stream.="<td align=right>".number_format($arrjumlahpmandor[$karyid][$isi])."</td>"; 
		@$trpppmandor[$karyid]+=$arrjumlahpmandor[$karyid][$isi];
	}
	$stream.="<td align=right><b>".number_format($trpppmandor[$karyid])."</b></td>";
	$stream.="</tr>";
}

//Total Keseluruhan
$colspan=$tarrtgl+7;
$stream.="
<tr class=rowcontent>
<td colspan=7>".$_SESSION['lang']['total']."</td>";
foreach($arrtgl as $ar => $isi){	
	$stream.="<td align=right>".number_format($gttanggal[$isi])."</td>"; 
}
	
$stream.="<td align=right><b>".number_format($gtpremi)."</b></td>
</tr>";
// echo $colspan;
	
$stream.="</tbody></table>";


switch($proses){
	case 'getdivisitipe':

		$tpkry="";
		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		if(strlen($_SESSION['empl']['subbagian'])==''){
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";
		$optdivisi.="<option value='".$unit."'>".$_SESSION['lang']['kantor']." / ".$_SESSION['lang']['umum']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}else{
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$_SESSION['empl']['subbagian']."' 
		and tipe in ('AFDELING','BIBITAN','KEBUN','STATION','SIPIL','PABRIK','TRAKSI','WORKSHOP','MAINTENANCE') and length(kodeorganisasi)=6
		order by kodeorganisasi asc";	
		$optdivisi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}
		}

		$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		//$iTipe="select distinct tipekaryawan,tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id where lokasitugas='".$unit."' and alokasi=0 and tipekaryawan<>0 ".$tpkry." ";

		$iTipe="select distinct tipekaryawan,tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id where lokasitugas='".$unit."' and aktif=1 ".$tpkry." ";

		$nTipe=$owlPDO->query($iTipe) or die(print " Gagal: ".PDOException::getMessage());
		$nTipe->setFetchMode(PDO::FETCH_ASSOC);
		while($dTipe=$nTipe->fetch())
		{
		    $optTipe.="<option value=".$dTipe['tipekaryawan'].">".$dTipe['tipe']."</option>";
		}

			echo $optdivisi."#####".$optTipe;
		break;
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$unit."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optdivisi;
			break;
    case 'preview':
        echo $stream;
    break;
    case 'excel':
        $tglSkrg=date("Ymd");
        $nop_="laporan_premi_harian";
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
	case 'printpdf':
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($stream);
		$dompdf->setPaper('A2', 'landscape');
		$dompdf->render();
		$dompdf->stream('Laporan Premi',array("Attachment"=>0));
	break;
}
?>
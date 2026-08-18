<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$tipe		=checkPostGet('tipe','');
$unit		=checkPostGet('unit','');
$divisi		=checkPostGet('divisi','');
$periode	=checkPostGet('periode','');
$periode2	=checkPostGet('periode2','');

switch($method){

    case 'getorg':

		$wh= "";
		if($tipe != 'Seluruhnya'){
			if($tipe == 'Inti'){
				$wh= "and inti = '1' ";
				
			}else{
				$wh= "and inti = '0' ";
			}
		}
		

        $optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
		// $str="select kodeorganisasi,namaorganisasi,inti from ".$dbname.".organisasi where tipe = 'KEBUN' ".$wh." order by kodeorganisasi";
		// $res=fetchdata($str);
		// foreach($res as $val){
		// 	$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		// }

		
		foreach(getOrgDetail(23) as $key => $val){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optUnit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optUnit.="<option value=".$key.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optUnit.="</optgroup>";
			}
		}
		echo $optUnit;
        
    break;

	case 'getdivisi':

		$wh= "";
		if($unit != ''){
			$wh= "and induk = '".$unit."'";
		}

		$optdivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' ".$wh." order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optdivisi.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}

		echo $optdivisi;

	break;
	case'preview':

		$gettglawal=$periode2."-01";
		$gettglakhir=tglakhir($periode2);
		$bulan=tanggalbulan($periode2);
		$exptglakhir=explode('-',$gettglakhir);
		$tglawal='01';
		$tglakhir=$exptglakhir[2];

		$rangetgl = rangeTanggalarr($gettglawal,$gettglakhir);
		

		$tab="";				
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}
		
		$wh= "";
		if($tipe != 'Seluruhnya'){
			if($tipe == 'Inti'){
				$wh= "and inti = '1' ";
				
			}else{
				$wh= "and inti = '0' ";
			}
		}

		if($unit != ''){
			$wh.= "and induk = '".$unit."'";
		}

		if($divisi != ''){
			$wh.= "and kodeorganisasi = '".$divisi."'";
			$whspb = "and divisi = '".$divisi."'";
		}

		## Organisasi
		$str="select kodeorganisasi,namaorganisasi,inti from ".$dbname.".organisasi where tipe = 'AFDELING' ".$wh." order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
            $kodeOrganisasi[$val['inti']][$val['kodeorganisasi']] = $val['namaorganisasi'];
            $intorplasma[$val['kodeorganisasi']] = $val['inti'];
        }

		## HI
		$str="select * from ".$dbname.".kebun_spb_detail_vw where  tanggalpanen like '%".$periode2."%' ".$whspb." order by tanggalpanen asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$beratnetto_hi[$val['divisi']][$val['tanggalpanen']] +=  $val['kgwbnetto'];
		}

		## BI
		$str="select *  from ".$dbname.".kebun_spb_detail_vw where  SUBSTR(tanggalpanen, 1, 4) = SUBSTR('".$periode2."', 1, 4) and tanggalpanen not like '%".$periode2."%' ".$whspb." order by tanggalpanen asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$beratnetto_sdbi[$val['divisi']] +=  $val['kgwbnetto'];
		}

		## SD BL
		$str="select *  from ".$dbname.".kebun_spb_detail_vw where  SUBSTR(tanggalpanen, 1, 4) = SUBSTR('".$periode2."', 1, 4) and tanggalpanen not like '%".$periode2."%' ".$whspb." order by tanggalpanen asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$beratnetto_sdbl[$val['divisi']] +=  $val['kgwbnetto'];
		}

		$colspn=$tglakhir;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='2'> No </th>
				<th rowspan='2'> Divisi </th>
				<th rowspan='2'> </th>
				<th colspan='".$colspn."'>Produksi Panen ( KG ) Harian <br> Periode ".$periode2."</th>";
		$tab.="</tr>";
            
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
			for($i=$tglawal;$i<=$tglakhir;$i++){
				$dayOfWeek = date('w', strtotime($periode2."-".$i));
				if ($dayOfWeek == 0) {
					$tab.="<th style='color:red'>".addZero($i,2)."</th>";
				}else{
					$tab.="<th>".addZero($i,2)."</th>";
				}
			}
		$tab.="</tr>";
		$tab.="</thead><tbody>";


		krsort($kodeOrganisasi);
		$no = 0 ;
		foreach($kodeOrganisasi as $tipeOrg => $arr1){    
			foreach($arr1 as $kodeOrg => $namaOrg){    
				$no++;
				// Baris pertama untuk nomor dan organisasi dengan rowspan 4
				$tab .= "<tr class='rowcontent'>";
					$tab .= "<td align='center' rowspan='4'>" . $no . " </td>";  // rowspan 4 untuk nomor
					$tab .= "<td rowspan='4'>" . $kodeOrg . " </td>";            // rowspan 4 untuk kode organisasi
					$tab .= "<td><b>SD BL </b></td>"; // Kolom pertama 
					
					foreach($rangetgl as $tgl){	
						// Cek apakah $tgl adalah tanggal pertama (1)
						if (date('d', strtotime($tgl)) == 1) {
							// Jika tanggalnya 1, tampilkan beratnetto_sdbl[$kodeOrg]
							$tab .= "<td>".number_format($beratnetto_sdbl[$kodeOrg], 2)."</td>"; // Kolom keempat
						} else {
							// Jika bukan tanggal pertama, tampilkan 0
							$tab .= "<td></td>"; // Kolom keempat
						}				
						
						$totalsdbl[$intorplasma[$kodeOrg]] += $beratnetto_sdbl[$kodeOrg];
					}
				$tab .= "</tr>";
	
				// Baris kedua 
				$tab .= "<tr class='rowcontent'>";
					$tab .= "<td><b>HI</b></td>"; 

					foreach($rangetgl as $tgl){	
						$tab .= "<td>".number_format($beratnetto_hi[$kodeOrg][$tgl],2)."</td>";// Kolom Kedua

						$totalhi[$intorplasma[$kodeOrg]][$tgl] += $beratnetto_hi[$kodeOrg][$tgl];
					}
				$tab .= "</tr>";
		
				// Baris ketiga 
				$tab .= "<tr class='rowcontent'>";
					$tab .= "<td><b>SD HI</b></td>"; 

					// Inisialisasi total akumulasi per organisasi
					$total_per_org = [];
					$total_berat = 0; // Total akumulasi berat untuk setiap organisasi

					foreach ($rangetgl as $tgl) {
						// Menghitung akumulasi berat netto hingga tanggal ini
						if (isset($beratnetto_hi[$kodeOrg][$tgl])) {
							$total_berat += $beratnetto_hi[$kodeOrg][$tgl]; // Tambahkan berat dari tanggal ini
						}
						
						// Menyimpan total akumulasi per tanggal untuk organisasi
						$total_per_org[$kodeOrg][$tgl] = $total_berat;
						
						$totalsdhi[$intorplasma[$kodeOrg]][$tgl] += $total_berat;

						// Menambahkan ke tabel
						$tab .= "<td>".number_format($total_berat, 2)."</td>"; // Kolom Ketiga
					}
				$tab .= "</tr>";
		
				// Baris keempat 
				$tab .= "<tr class='rowcontent'>";
					$tab .= "<td><b>SD BI</b	></td>"; 

					foreach($rangetgl as $tgl){	
						$tab .= "<td>".number_format($beratnetto_sdbi[$kodeOrg] + $total_per_org[$kodeOrg][$tgl] , 2)."</td>"; // Kolom keempat

						$totalsdbi[$intorplasma[$kodeOrg]][$tgl] += $beratnetto_sdbi[$kodeOrg] + $total_per_org[$kodeOrg][$tgl];
					}
				$tab .= "</tr>";
			}

			if($tipeOrg == '1' ){
				$namaTipe = "INTI";
			}else{
				$namaTipe = "PLSMA";

			}
			$tab .= "<tr class='rowcontent' style='background-color:cyan;'>";
				$tab .= "<td colspan =2 rowspan = 4><b>TOTAL (".$namaTipe.") </b></td>"; 
				$tab .= "<td><b>SD BL </b></td>"; // Kolom pertama 

				foreach($rangetgl as $tgl){	
						// Cek apakah $tgl adalah tanggal pertama (1)
						if (date('d', strtotime($tgl)) == 1) {
							// Jika tanggalnya 1, tampilkan beratnetto_sdbl[$kodeOrg]
							$tab .= "<td>".number_format($totalsdbl[$tipeOrg], 2)."</td>"; // Kolom pertama
						} else {
							// Jika bukan tanggal pertama, tampilkan 0
							$tab .= "<td></td>"; // Kolom pertama
						}		
				}
			$tab .= "</tr>";

			// Baris kedua 
			$tab .= "<tr class='rowcontent' style='background-color:cyan;'>";
				$tab .= "<td><b>HI</b></td>"; 

				foreach($rangetgl as $tgl){	
					$tab .= "<td>".number_format($totalhi[$tipeOrg][$tgl], 2)."</td>"; // Kolom kedua
				}
			$tab .= "</tr>";

			// Baris Ketiga
			$tab .= "<tr class='rowcontent' style='background-color:cyan;'>";
				$tab .= "<td><b>SD HI</b></td>"; 

				foreach($rangetgl as $tgl){	
					$tab .= "<td>".number_format($totalsdhi[$tipeOrg][$tgl], 2)."</td>"; // Kolom ketiga
				}
			$tab .= "</tr>";

			// Baris Keempat
			$tab .= "<tr class='rowcontent' style='background-color:cyan;'>";
				$tab .= "<td><b>SD BI</b></td>"; 

				foreach($rangetgl as $tgl){	
					$tab .= "<td>".number_format($totalsdbi[$tipeOrg][$tgl], 2)."</td>"; // Kolom ketiga
				}
			$tab .= "</tr>";
		}


				
		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_produksi".$unit."_".$periode;
			if(strlen($tab)>0){
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
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;
}


?>
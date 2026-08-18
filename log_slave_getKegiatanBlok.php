<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
//=============================================
if(isTransactionPeriod()){
	//check if transaction period is normal
    $statusblok=$_POST['statusblok'];
    $kodebarang=$_POST['kodebarang'];
	$sbUnit = checkPostGet('subunit','');

	if($statusblok != ''){
		$whereStatusBlok = "and kelompok = '".$statusblok."' ";
	}

    $blok=$_POST['blok'];
	$dataspk=makeOption($dbname,'lgl_pengajuanspkht','notransaksi,jenissupplier',"notransaksi='".$blok."'");
	$datasuppli=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$sbUnit."'");
    $untukunit=$_POST['untukunit'];
	if($blok!=''){ 	
		$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$blok."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		
		$tipe=$_POST['jenis'];//Default untuk traksi
		if($dataspk[$blok]!=''){
			$tipe='SPK';
		}
		if($blok==$sbUnit and $datasuppli[$sbUnit]!=''){
			$tipe='piutangsuppl';
		}

		while($bar=$res->fetch()){
		 	$tipe=$bar->tipe;
		}

		// cek apakah hanya status blok
		$getEnumStatusBlok = getEnum($dbname, 'setup_blok', 'statusblok');
		if (in_array($blok, $getEnumStatusBlok)) {
			// Masuk kondisi
			// cek apakah terdaftar barang tanpa status blok
			$sData_a="select * from ".$dbname.".setup_barangstatusblok where kodebarang='".$kodebarang."' and status='1' ";
			$rData_a=fetchdata($sData_a);
			if(count($rData_a)>0){
				if($statusblok != ""){
					if($statusblok == 'TM'){
						$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan where (kelompok='".$statusblok."' or kelompok='PNN')  and status = '1' order by noakun, kelompok,kodekegiatan"; 
					}else{
						$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by noakun, kelompok,kodekegiatan"; 
					}
					$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
					$resf->setFetchMode(PDO::FETCH_OBJ);
					$n='';
					while($barf=$resf->fetch()){
						//$optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
						
						$d=$barf->noakun2;
						if($d!==$n && $n!==""){			
							$optKegiatan.="</optgroup>";
						}
						if($d!=$n){			
							$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
						}
						$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
						$n=$d;
						if($d!=$n){			
							$optKegiatan.="</optgroup>";
						}
					} 
				}
			}
		} 
		$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".substr($blok,0,4)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
		 	$tipeunit=$bar->tipe;
		}
		
		if($tipe=='STENGINE' or $tipe=='STATION' or $tipe=='MAINTENANCE'){
			$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$untukunit."'";
			$res=fetchdata($str);
			$tipeorg = $res[0]['tipe'];
			
			$wh="";
			if($tipeorg=='BULKING'){
				$wh=" and substr(noakun,1,2) in ('81')";
			}
			
			$optKegiatan="<option value=''></option>";
			if($tipeunit=='BULKING'){
				$wh.=" and substr(kodekegiatan,1,3) not in ('127','126')";
				$strf="select kodekegiatan,kelompok,namakegiatan,substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan 
			       where kelompok='KNT' and status = '1' ".$wh."  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";
				// exit("error".$strf);   
				$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
				$resf->setFetchMode(PDO::FETCH_OBJ);
				$n='';
				while($barf=$resf->fetch()){
					//$optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
					
					$d=$barf->noakun2;
					if($d!==$n && $n!==""){			
						$optKegiatan.="</optgroup>";
					}
					if($d!=$n){			
						$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
					}
					$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
					$n=$d;
					if($d!=$n){			
						$optKegiatan.="</optgroup>";
					}
				} 
			}else{
				$strf="select kodekegiatan,kelompok,namakegiatan,substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan 
			       where kelompok='MIL' and status = '1'  ".$whereStatusBlok." order by noakun,kelompok,namakegiatan";
				$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
				$resf->setFetchMode(PDO::FETCH_OBJ);
				$n='';
				while($barf=$resf->fetch()){
					// $optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
					
					$d=$barf->noakun2;
					if($d!==$n && $n!==""){			
						$optKegiatan.="</optgroup>";
					}
					if($d!=$n){			
						$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
					}
					$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
					$n=$d;
					if($d!=$n){			
						$optKegiatan.="</optgroup>";
					}
				} 
			}
			echo $optKegiatan;
		}else if($tipe=='BLOK'){
			$sbUnit = checkPostGet('subunit','');
			$optVTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$sbUnit."'");
			
			$blehh="<option value=''></option>";
			if($optVTipe[$sbUnit] == 'SIPIL'){
				$strf="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan 
			       where (kelompok='SPL' or kelompok='KNT') and status = '1'  ".$whereStatusBlok." order by noakun, kelompok,kodekegiatan";
				   $resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
				$resf->setFetchMode(PDO::FETCH_OBJ);
				$n='';
				while($barf=$resf->fetch()){
					$d=$barf->noakun;
					if($d!==$n && $n!==""){			
						$blehh.="</optgroup>";
					}
					if($d!=$n){			
						$blehh.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
					}
					$blehh.="<option value='".$barf->kodekegiatan."'>".substr($barf->kodekegiatan,-2)." - ".$barf->namakegiatan."</option>";
					$n=$d;
					if($d!=$n){			
						$blehh.="</optgroup>";
					}
				}
			}else{
				// exit("warning : ".$blok." ");
				if($statusblok == ''){
					$blehh.=getKegiatanBlok('option',$blok);	   
				}else{
					$blehh.=getKegiatanBlok('option',$blok,$statusblok);	   
				}
			}
			echo $blehh;
		}else if($tipe=='WORKSHOP'){
			$optKegiatan="<option value=''></option>";
			#or kelompok='KNT'
			$strf="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1'  ".$whereStatusBlok." order by  noakun, kelompok,kodekegiatan";
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
			$n='';
			while($barf=$resf->fetch()){
				$d=$barf->kelompok;
				if($d!==$n && $n!==""){			
					$optKegiatan.="</optgroup>";
				}
				if($d!=$n){			
					$optKegiatan.="<optgroup label='".$d."'>";
				}
				$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->kelompok." - ".$barf->namakegiatan."</option>";
				$n=$d;
				if($d!=$n){			
					$optKegiatan.="</optgroup>";
				}
			}
			echo $optKegiatan;			
		}else if($tipe=='SIPIL'){
			$optKegiatan="<option value=''></option>";
			$strf="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan 
			       where (kelompok='SPL' or kelompok='KNT') and status = '1'  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";	   
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
			$n='';
			while($barf=$resf->fetch()){
				$d=$barf->noakun;
				if($d!==$n && $n!==""){			
					$optKegiatan.="</optgroup>";
				}
				if($d!=$n){			
					$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				}
				$optKegiatan.="<option value='".$barf->kodekegiatan."'>".substr($barf->kodekegiatan,-2)." - ".$barf->namakegiatan."</option>";
				$n=$d;
				if($d!=$n){			
					$optKegiatan.="</optgroup>";
				}
				
				//$optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
			} 
			echo $optKegiatan;			
		}else if($tipe=='TRAKSI'){
			
			$sbUnit = checkPostGet('subunit','');
			$optVTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$sbUnit."'");
			
			$optKegiatan="<option value=''></option>";
			if($optVTipe[$sbUnit] == 'SIPIL'){
				$strf="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan 
			       where (kelompok='SPL' or kelompok='KNT') and status = '1'  ".$whereStatusBlok." order by kelompok,namakegiatan";
			}else{
				#or kelompok='KNT'
				$strf="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan 
			       where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1'  ".$whereStatusBlok." order by kelompok desc,namakegiatan";	   
			}
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
			while($barf=$resf->fetch()){
				 $optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
			} 
			echo $optKegiatan;			
		}else if($tipe=='BIBITAN'){
			$optKegiatan="<option value=''></option>";
			$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun from ".$dbname.".setup_kegiatan 
			       where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127','126','129','116','121') and  substr(kodekegiatan,1,1) not in ('7','8','9') and status = '1'  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";	   
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
			$n='';
			while($barf=$resf->fetch()){
				$d=$barf->noakun;
				if($d!==$n && $n!==""){			
					$optKegiatan.="</optgroup>";
				}
				if($d!=$n){			
					$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				}
				$optKegiatan.="<option value='".$barf->kodekegiatan."'>".substr($barf->kodekegiatan,0,7)." - ".$barf->namakegiatan."</option>";
				$n=$d;
				if($d!=$n){			
					$optKegiatan.="</optgroup>";
				}
			} 
			
			echo $optKegiatan;			
		}else if(substr($blok,0,2)=='AK' or substr($blok,0,2)=='PB'){
			if(substr($blok,0,2)=='AK'){
				$tipeasset=substr($blok,3,2);
				// $tipeasset=  str_replace("0","", $tipeasset);
				$str="select akunak,namatipe from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."'";
				$resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$resf->setFetchMode(PDO::FETCH_OBJ);
				if(owlBaris($resf)>0){
					while($barf=$resf->fetch()){
						$optKegiatan.="<option value='".$barf->akunak."01'>[PROJECT] - ".$barf->namatipe."</option>";
					} 
	                echo $optKegiatan; 
			// exit("error".$str);
				}else{
					exit(" Error: Akun aktiva dalam kontruksi belum ditentukan untuk kode ".$tipeasset);
				}   	
			}else{

			    $sData="select kodekegiatan,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='PBR' and noakun in ('6340102','6340103')  ".$whereStatusBlok."";
			    $rData=fetchdata($sData);
			    if(count($rData)>0){
					foreach ($rData as $isiPabrikasi) {
			        	$optKegiatan.="<option value=" . $isiPabrikasi['kodekegiatan'] . ">" . $isiPabrikasi['namakegiatan'] . " </option>";
			    	}
			    	echo $optKegiatan; 
				}else{
					exit(" Error: Akun Kegiatan Pabrikasi Belum ditentukan");
				}
			    
			}
			 
		}else if($tipe=='SPK'){
				// $tipeasset=  str_replace("0","", $tipeasset);
				// $str="select kode,noakun from ".$dbname.".log_5klsupplier where tipe='".$dataspk[$blok]."'";
				 $str="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan 
				   where kelompok='KNT' and status = '1' and substr(kodekegiatan,1,7) in ('2111201')  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";
			 	$optKegiatan='';
			 	//exit("error".$str);
				 $resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				 $resf->setFetchMode(PDO::FETCH_OBJ);
				while($barf=$resf->fetch()){
					$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
				} 
				echo $optKegiatan;   
			 
		}else if($tipe=='piutangsuppl'){
				// $tipeasset=  str_replace("0","", $tipeasset);
				// $str="select kode,noakun from ".$dbname.".log_5klsupplier where tipe='".$dataspk[$blok]."'";
				 $str="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan 
				   where kelompok='KNT' and status = '1' and substr(kodekegiatan,1,7) in ('1140108')  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";
			 	$optKegiatan='';
			 	//exit("error".$str);
				 $resf=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				 $resf->setFetchMode(PDO::FETCH_OBJ);
				while($barf=$resf->fetch()){
					$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
				} 
				echo $optKegiatan;   
			 
		}else{
			$untukunit=$_POST['untukunit'];
			$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$untukunit."'";
			$res=fetchdata($str);
			$tipeorg = $res[0]['tipe'];
			$wh="";
			$next_a="";
			// cek apakah terdaftar barang tanpa status blok
			$sData_a="select * from ".$dbname.".setup_barangstatusblok where kodebarang='".$kodebarang."' and status='1' ";
			$rData_a=fetchdata($sData_a);
			if(count($rData_a)>0){
				if($statusblok != ""){
					$next_a = "ada";
					$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by noakun, kelompok,kodekegiatan"; 
				}else{
					$next_a="";
				}
			}else{
				$next_a="";
			}

			if($next_a != "ada"){
				if($tipeorg=='KEBUN'){
					$wh=" and substr(noakun,1,1) in ('7') and substr(noakun,1,2) not in ('72')";
				}
				
				if($tipeorg=='HOLDING'){
					$wh=" and substr(noakun,1,3) in ('812','821')";
				}
				if($tipeorg=='BULKING'){
					$wh=" and substr(noakun,1,2) in ('81')";
				}
				
				if($tipeorg=='RND'){
					$wh=" and substr(noakun,1,2) in ('82')";
				}
				if($tipeorg=='TC'){
					$wh=" and substr(noakun,1,2) in ('82')";
				}
				
				$wh.=" or substr(noakun,1,3) in ('116','121')";
				$optKegiatan="<option value=''></option>";
				$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') ".$wh." and status = '1'  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";
			}
			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$resf->setFetchMode(PDO::FETCH_OBJ);
			$n='';
			while($barf=$resf->fetch()){
				$d=$barf->noakun2;
				if($d!==$n && $n!==""){			
					$optKegiatan.="</optgroup>";
				}
				if($d!=$n){			
					$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				}
				$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
				$n=$d;
				if($d!=$n){			
					$optKegiatan.="</optgroup>";
				}
			} 
			echo $optKegiatan;   
		}                
	}else{
		$sbUnit = checkPostGet('subunit','');
		$optVTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$sbUnit."'");
		if($optVTipe[$sbUnit] == 'SIPIL'){
			$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan 
			   where kelompok='SPL' and status = '1'  ".$whereStatusBlok." order by noakun, kelompok,namakegiatan";
		}else{
			$strf="select kodekegiatan,kelompok,namakegiatan, substr(noakun,1,5) as noakun2, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT'
			and substr(kodekegiatan,1,3) not in ('127',126)
			and status = '1'  ".$whereStatusBlok." order by noakun,kelompok,namakegiatan";
		}
		$optKegiatan="<option value=''></option>";       
		$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
		$resf->setFetchMode(PDO::FETCH_OBJ);
		$n='';
        while($barf=$resf->fetch()){
			//$optKegiatan.="<option value='".$barf->kodekegiatan."'>[".$barf->kelompok."]-".$barf->namakegiatan."</option>";
			
			$d=$barf->noakun2;
			if($d!==$n && $n!==""){			
				$optKegiatan.="</optgroup>";
			}
			if($d!=$n){			
				$optKegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$optKegiatan.="<option value='".$barf->kodekegiatan."'>".$barf->noakun." - ".$barf->namakegiatan."</option>";
			$n=$d;
			if($d!=$n){			
				$optKegiatan.="</optgroup>";
			}
		} 
        echo $optKegiatan;		
		
		//exit("error".$strf);
	}
}else{
	echo " Error: Transaction Period missing";
}
?>
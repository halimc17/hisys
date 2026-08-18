<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kelompokvhc     =checkPostGet('kelompokvhc','');
$jenisvhc        =checkPostGet('jenisvhc','');
$kodeorg         =checkPostGet('kodeorg','');
$method          =checkPostGet('method','');
$kodevhc         =str_replace(" ","",checkPostGet('kodevhc',''));
$tahunperolehan  =checkPostGet('tahunperolehan','');
$noakun          =checkPostGet('noakun','');
$beratkosong     =checkPostGet('beratkosong','');
$nomorrangka     =checkPostGet('nomorrangka','');
$nomormesin      =checkPostGet('nomormesin','');
$detailvhc       =checkPostGet('detailvhc','');
$kodebarang      =checkPostGet('kodebarang','');
$kepemilikan     =checkPostGet('kepemilikan','');
$kodetraksi      =checkPostGet('kodetraksi','');
$nobpkb          =checkPostGet('nobpkb','');
$kodeasset       =checkPostGet('kodeasset','NULL');
$tglakhirstnk    =tanggalsystemn(checkPostGet('tglakhirstnk','00-00-0000'));
$tglakhirkir     =tanggalsystemn(checkPostGet('tglakhirkir','00-00-0000'));
$tglakhirijinbm  =tanggalsystemn(checkPostGet('tglakhirijinbm','00-00-0000'));
$tglakhirijinang =tanggalsystemn(checkPostGet('tglakhirijinang','00-00-0000'));

$nopol           =checkPostGet('nopol','');
$tahunproduksi   =checkPostGet('tahunproduksi','');
$warna           =checkPostGet('warna','');
$tglakhirleasing =tanggalsystemn(checkPostGet('tglakhirleasing','00-00-0000'));
$tglakhirasuransi=tanggalsystemn(checkPostGet('tglakhirasuransi','00-00-0000'));


if($kodeasset!='NULL') {
	$kodeasset = "'".$kodeasset."'";
}

if($kodeasset=="''"){
	$kodeasset = 'null';
}
else
{
	$kodeasset = $kodeasset;
}

if($beratkosong=='') $beratkosong=0;
$strx="";

switch($method){
	
	case 'getNotransaksi':
		//bentuk nomor transaksi
        $str="select max(right(kodevhc,4)) as nomorurut from ".$dbname.".vhc_5master where kodeorg = '".$kodeorg."' and jenisvhc='".$jenisvhc."' order by right(kodevhc,4) desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=$kodeorg.$jenisvhc.addZero($noawal,4);
		echo $notran;
		// exit('error'.$notran);
	
	break;
	
    case 'delete':
        $strx="delete from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
			try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
    case 'update':
		$strx="update ".$dbname.".vhc_5master set jenisvhc='".$jenisvhc."',
			kelompokvhc='".$kelompokvhc."', 
			kodeorg='".$kodeorg."', tahunperolehan='".$tahunperolehan."',
			beratkosong='".$beratkosong."', nomorrangka='".$nomorrangka."' , nobpkb='".$nobpkb."' ,
			nomormesin='".$nomormesin."',detailvhc='".$detailvhc."',
			kodebarang='".$kodebarang."',kepemilikan=".$kepemilikan.",
			kodetraksi='".$kodetraksi."', tglakhirstnk='".$tglakhirstnk."',
			tglakhirkir='".$tglakhirkir."',tglakhirijinbm='".$tglakhirijinbm."',
			tglakhirijinang='".$tglakhirijinang."',kodeasset=".$kodeasset.",
			nopol='".$nopol."',tahunproduksi='".$tahunproduksi."',warna='".$warna."',
			tglakhirleasing='".$tglakhirleasing."',tglakhirasuransi='".$tglakhirasuransi."',updateby='" . $_SESSION['standard']['userid'] . "'
			
		where kodevhc='".$kodevhc."'";
			try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		
		
		break;	
    case 'insert':
		//bentuk nomor transaksi
        $str="select count(kodevhc) as nomorurut from ".$dbname.".vhc_5master where kodeorg = '".$kodeorg."' and jenisvhc='".$jenisvhc."' order by right(kodevhc,4) desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $kodevhc=$kodeorg.$jenisvhc.addZero($noawal,4);
		
	
		$strx="insert into ".$dbname.".vhc_5master(
			kodevhc,kelompokvhc,kodeorg,jenisvhc,
			tahunperolehan,beratkosong,nomorrangka,nobpkb,
			nomormesin,detailvhc,kodebarang,kepemilikan,kodetraksi,
			tglakhirstnk,tglakhirkir,tglakhirijinbm,tglakhirijinang,kodeasset,
			nopol,tahunproduksi,warna,tglakhirleasing,tglakhirasuransi,createdby,createdtime,status)
		values('".$kodevhc."','".$kelompokvhc."',
			'".$kodeorg."','".$jenisvhc."',".$tahunperolehan.",
			".$beratkosong.",'".$nomorrangka."','".$nobpkb."','".$nomormesin."',
			'".$detailvhc."','".$kodebarang."',".$kepemilikan.",
			'".$kodetraksi."','".$tglakhirstnk."','".$tglakhirkir."',
			'".$tglakhirijinbm."','".$tglakhirijinang."',".$kodeasset.",
			'".$nopol."','".$tahunproduksi."','".$warna."','".$tglakhirleasing."','".$tglakhirasuransi."',
			'" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','1')";
			
			try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
		
    case'deactive':
        if($_POST['status']==1){
            $_POST['status']=0;
        }else{
            $_POST['status']=1;
        }
          $strx="update ".$dbname.".vhc_5master set status='".$_POST['status']."' 
                 where kodevhc='".$_POST['kodevhc']."'";
		try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
    
	case'loaddata':
	function ilanginenter($tulisan){
		$buffer = str_replace(array("\r", "\n"), '', $tulisan);
		return $buffer;
	}
			$where='1=1';
		if($kodeorg!='')
		   $where.=" and kodeorg='".$kodeorg."' ";
		if($kelompokvhc!='')
		   $where.=" and kelompokvhc='".$kelompokvhc."' ";   
		if($jenisvhc!='')
		   $where.=" and jenisvhc='".$jenisvhc."' ";
		   
		if(($_SESSION['empl']['tipelokasitugas']=='HOLDING')or($_SESSION['empl']['tipelokasitugas']=='KANWIL')){
			$str="select * from ".$dbname.".vhc_5master where ".$where." 
				order by status desc,kodeorg,kodevhc asc";
		} else{
			$str="select * from ".$dbname.".vhc_5master where kodetraksi like '".$_SESSION['empl']['lokasitugas']."%' and ".$where." 
				order by status desc,kodeorg,kodevhc asc";
		}


		$no=0;
		$listAsset = array();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res->fetch())
		{
			$no+=1;
			$str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar1->kodebarang."'";
			$namabarang='';
				$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res1->fetch())
				{
					$namabarang=$bar->namabarang;
			}
			
			if($bar1->kepemilikan==1) {
			  $dptk=$_SESSION['lang']['miliksendiri'];	
			} else {
				$dptk=$_SESSION['lang']['sewa'];
			}
			$sttd="";
			$sttd="Deactivate";
			$bgcrcolor="class=rowcontent";
			if($bar1->status==0){
				$bgcrcolor="bgcolor=orange";
				$sttd="";
				$sttd="Actived";
			}
			$clidt=" style='cursor:pointer' title='".$sttd." ".$bar1->kodevhc."' onclick=deAktif('".$bar1->kodevhc."','".$bar1->status."')";
			echo"<tr ".$bgcrcolor.">
				<td align=center ".$clidt." >".$no."</td>
				<td align=center  ".$clidt." >".$bar1->kodeorg."</td>
				<td align=center  ".$clidt." >".$bar1->kelompokvhc."</td>				 
				<td align=center  ".$clidt." >".$bar1->jenisvhc."</td>			 		
				<td ".$clidt." >".$bar1->kodevhc."</td>
				<td  ".$clidt."  >".$bar1->nopol."</td>
				<td  ".$clidt."  >".$bar1->kodeasset."</td>
				<td ".$clidt." >".$namabarang."</td>
				<td align=center  ".$clidt." >".$bar1->tahunperolehan."</td>
				<input type=hidden value=".$bar1->beratkosong.">
				<input type=hidden value=".$bar1->nomorrangka.">
				<td ".$clidt." >".$bar1->nomormesin."</td> 
				<td ".$clidt." >".$bar1->detailvhc."</td> 	
				<td ".$clidt." >".$dptk."</td> 
				<td align=center  ".$clidt." >".$bar1->kodetraksi."</td>
				<td align=center  ".$clidt." >".getNamaKaryawan($bar1->updateby)."</td>
				<td align=center >
					<img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"fillMasterField('".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."','".$bar1->beratkosong."',
						'".$bar1->nomorrangka."','".$bar1->nobpkb."','".$bar1->nomormesin."','".$bar1->tahunperolehan."','".$bar1->kodebarang."','".$bar1->kepemilikan."','".$bar1->kodetraksi."','".tanggalnormal($bar1->tglakhirstnk)."','".tanggalnormal($bar1->tglakhirkir)."',
						'".tanggalnormal($bar1->tglakhirijinbm)."','".tanggalnormal($bar1->tglakhirijinang)."','".$bar1->kodeasset."','".ilanginenter($bar1->detailvhc)."','".$bar1->nopol."','".$bar1->tahunproduksi."','".$bar1->warna."','".$bar1->tglakhirleasing."','".$bar1->tglakhirasuransi."');\">
					<!--<img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"deleteMasterVhc('".$bar1->kodeorg."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$bar1->kodevhc."');\">-->
				</td></tr>";
			
			if($bar1->kodeasset!=str_replace("'",'',$kodeasset)) {
				$listAsset[] = $bar1->kodeasset;
			}
		}

	break;
	
	case'getList':
		// Get Kode Asset
		if(!empty($kodeorg)) {
			$whereAsset = "kodeorg='".$kodeorg."' and kodeasset not in 
			(SELECT kodeasset FROM ".$dbname.".vhc_5master where kodeasset !='') and tipeasset='".$kelompokvhc."'";
									   
		$optAsset="<option value=''></option>";
        $str="select * from ".$dbname.".sdm_daftarasset where 1=1 and ".$whereAsset." ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$optAsset.="<option value=".$bar['kodeasset'].">".$bar['kodeasset']." - ".$bar['namasset']."</option>";
			}

			if ($kodeasset!='NULL') {
				$kodeasset=str_replace("'","",$kodeasset);
				$whrKar2="kodeasset='".$kodeasset."'";
                $optjenis=makeOption($dbname,'sdm_daftarasset','kodeasset,namasset',$whrKar2);
				$optAsset.="<option value=".$kodeasset." selected>".$kodeasset." - ".$optjenis[$kodeasset]."</option>";
			}

			echo $optAsset; 
		}
	break;
	
	case'getimagevhc':
		$path = "fileupload/jenis_vhc/";
		$str="select * from ".$dbname.".vhc_5jenisvhc where jenisvhc='".$jenisvhc."'";
		$res=fetchData($str);
		
		$tab="";
		if($res[0]['file']!='')
		{
			$tab.="<img src='".$path.$res[0]['file']."' style='width:120px;height:120px;'>";
		}
		else
		{
			$tab.="<img src='images/question.png' style='width:120px;height:120px;'>";
		}
		echo $tab;
	break;
	
	default:
    break;
}



?>
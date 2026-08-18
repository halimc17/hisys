<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$per=checkPostGet('per','');
$unit=checkPostGet('unit','');
$noakun=checkPostGet('noakun','');
$nojurnal=checkPostGet('nojurnal','');
$nodok=checkPostGet('nodok','');
$jumlah=checkPostGet('jumlah','');

$nourutdt=checkPostGet('nourutdt','');
$nojurnaldt=checkPostGet('nojurnaldt','');
$noakundt=checkPostGet('noakundt','');
$keterangandt=checkPostGet('keterangandt','');

$jumlahdt=checkPostGet('jumlahdt','');
$kodekegiatandt=checkPostGet('kodekegiatandt','');
$kodebarangdt=checkPostGet('kodebarangdt','');
$nikdt=checkPostGet('nikdt','');

$kodesupplierdt=checkPostGet('kodesupplierdt','');
$kodevhcdt=checkPostGet('kodevhcdt','');
$kodeblokdt=checkPostGet('kodeblokdt','');
$nojurnalp=checkPostGet('nojurnalp','');




switch($proses){

	case 'getakun':
		$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select a.akunpiutang,b.namaakun from ".$dbname.".keu_5caco a left join ".$dbname.".keu_5akun b on a.akunpiutang=b.noakun where a.kodeorg='".substr($unit,0,4)."' and a.jenis='intra' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optakun.="<option value=".$bar['akunpiutang'].">".$bar['akunpiutang']." ".$bar['namaakun']."</option>";
		}

		$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="SELECT distinct periode FROM ".$dbname.".setup_periodeakuntansi where tutupbuku=0 and kodeorg='".substr($unit,0,4)."' order by periode desc limit 10";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optper.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
		}

		echo $optakun."####".$optper;
	break;


    case'preview':
	
	if($unit=='' || $per=='' || $noakun==''){
		exit("Warning:Lengkapi Pengisian");
	}
	
	
	#cek penguncian tutup buku 
	$str="SELECT tutupbuku FROM ".$dbname.".setup_periodeakuntansi where 
			periode='".$per."' and kodeorg='".substr($unit,0,4)."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($bar['tutupbuku']==1){
		exit("Warning:Periode akuntansi sudah ditutup");
	}
	
	#bentuk nodokumen
	$nodok=$unit.'/'.str_replace('-','',$per);
	
	//get kodeorg
    $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$unit."'";
    $ressup=$owlPDO->query($sqlkd);
    $ressup->setFetchMode(PDO::FETCH_ASSOC);
    $barsup=$ressup->fetch();
    $induk=$barsup['induk'];

	#bentuk nojurnal
	$whereNo = "kodekelompok='M' and kodeorg='".$induk."'";
	$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNo);
	$noKon = fetchData($query);
	$tmpC = $noKon[0]['nokounter'];
	$tmpC++;
	$counter = addZero($tmpC,3);
	$nojurnal= str_replace('-','',$per)."28/".substr($unit,0,4)."/M/".$counter;
	
	if ($proses == 'excel') {
		$stream = "<table class=sortable cellspacing=1 border=1>";
	} else  {
		$stream = "<table class=sortable cellspacing=1>";
	}
	$stream.="<thead><tr class=rowheader>";
	$stream.="<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nourut']."</td>";  
	$stream.="       
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nojurnal']."</td>    
		<td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['tanggal']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['noakun']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['keterangan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['jumlah']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodekegiatan']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodebarang']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['nik']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodesupplier']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['noreferensi']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodevhc']."</td>
        <td bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kodeblok']."</td>
    </tr>";
$stream.="</thead>";
	
		$str="select * from ".$dbname.".keu_jurnaldt_vw where 
		nodok!='".$nodok."' and jumlah>0 and kodeblok like  '".$unit."%' and periode='".$per."' ";
		// exit('Warning :  '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
				@$nourut+=2;
				$stream.="<tr class=rowcontent id=row".$no.">";
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td hidden id=nourutdt".$no." align=center>".$nourut."</td>";
				$stream.="<td id=nojurnaldt".$no.">".$bar['nojurnal']."</td>";
				$stream.="<td>".$bar['tanggal']."</td>";
				$stream.="<td id=noakundt".$no.">".$bar['noakun']."</td>";
				$stream.="<td id=keterangandt".$no.">".$bar['keterangan']."</td>";
				$stream.="<td id=jumlahdt".$no.">".$bar['jumlah']."</td>";
				$stream.="<td id=kodekegiatandt".$no.">".$bar['kodekegiatan']."</td>";
				$stream.="<td id=kodebarangdt".$no.">".$bar['kodebarang']."</td>";
				$stream.="<td id=nikdt".$no.">".$bar['nik']."</td>";
				$stream.="<td id=kodesupplierdt".$no.">".$bar['kodesupplier']."</td>";
				$stream.="<td>".$bar['noreferensi']."</td>";
				$stream.="<td id=kodevhcdt".$no.">".$bar['kodevhc']."</td>";
				$stream.="<td id=kodeblokdt".$no.">".$bar['kodeblok']."</td>";
			$stream.="</tr>";
			@$jumlah+=$bar['jumlah'];
			$kodeblok=$bar['kodeblok'];
		}
		
		//Jurnal Plasma
		$kodejurnal='M';

		//get kodeblokplasma
	    $sqlkd="select kodeblokplasma from ".$dbname.".kebun_5kud where left(kodeblok,4)='".substr($kodeblok,0,4)."'";
	    $ressup=$owlPDO->query($sqlkd);
	    $ressup->setFetchMode(PDO::FETCH_ASSOC);
	    $barsup=$ressup->fetch();
	    $kodeblokplasma=$barsup['kodeblokplasma'];

		//get kodeorg
	    $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".substr($kodeblokplasma,0,4)."'";
	    $ressup=$owlPDO->query($sqlkd);
	    $ressup->setFetchMode(PDO::FETCH_ASSOC);
	    $barsup=$ressup->fetch();	
	    $induk=$barsup['induk'];
		
		#bentuk nojurnal
		$whereNo = "kodekelompok='".$kodejurnal."' and kodeorg='".$induk."'";
		$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNo);
		$noKon = fetchData($query);
		$tmpC = $noKon[0]['nokounter'];
		$tmpC++;
		$counter = addZero($tmpC,3);
		$nojurnalp= str_replace('-','',$per)."28/".substr($kodeblokplasma,0,4)."/M/".$counter;
		
		$stream.="<button class=mybutton onclick=del(".$no.");>".$_SESSION['lang']['proses']."</button>";	
		$stream.="<input type=text hidden id=nojurnal value='".$nojurnal."' size=10 class=myinputtext style=\"width:100px;\">";
		$stream.="<input type=text hidden id=nojurnalp value='".$nojurnalp."' size=10 class=myinputtext style=\"width:100px;\">";
		$stream.="<input type=text hidden id=nodok value='".$nodok."' size=10 class=myinputtext style=\"width:100px;\">";
		$stream.="<input type=text hidden id=jumlah value='".$jumlah."' onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:100px;\">";

		echo $stream;
    break;


    case'delete':
        $str="delete from ".$dbname.".keu_jurnalht where noreferensi='".$nodok."'";
        try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	
		#insert ht
		$str="INSERT INTO `keu_jurnalht` (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`,
											`posting`, `totaldebet`, `totalkredit`, `amountkoreksi`,
											`noreferensi`, `autojurnal`, `matauang`,
											`kurs`, `revisi`)
		VALUES ('".$nojurnal."', 'M', '".$per."-28', '".date('Y-m-d')."',
				'0','".$jumlah."','".($jumlah*-1)."','0',
				'".$nodok."', '1','IDR',
				'1', '0')";
		try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !:  " .$str."". $e->getMessage() . "\n"; 
            die(); 
        }
		
		
		$tmpC=explode('/',$nojurnal);
		$tmpC=$tmpC[3];
		$whereNo = "kodekelompok='M' and kodeorg='HAL'";
		$updData = array('nokounter'=>$tmpC);
        $query2 = updateQuery($dbname,'keu_5kelompokjurnal',$updData,$whereNo);
        try{$owlPDO->exec($query2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }


        #insert ht jurnal balik plasma
		$str="INSERT INTO `keu_jurnalht` (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`,
											`posting`, `totaldebet`, `totalkredit`, `amountkoreksi`,
											`noreferensi`, `autojurnal`, `matauang`,
											`kurs`, `revisi`)
		VALUES ('".$nojurnalp."', 'M', '".$per."-28', '".date('Y-m-d')."',
				'0','".$jumlah."','".($jumlah*-1)."','0',
				'".$nodok."', '1','IDR',
				'1', '0')";
		try{$owlPDO->exec($str); }
        catch (PDOException $e) {
            print " Gagal  !: " .$str."". $e->getMessage() . "\n"; 
            die(); 
        }
		
		
		$tmpC=explode('/',$nojurnalp);
		$unitp=$tmpC[1];
		$tmpC=$tmpC[3];

		//get kodeorg
	    $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$unitp."'";
	    $ressup=$owlPDO->query($sqlkd);
	    $ressup->setFetchMode(PDO::FETCH_ASSOC);
	    $barsup=$ressup->fetch();
	    $induk=$barsup['induk'];

		$whereNo = "kodekelompok='M' and kodeorg='".$induk."'";
		$updData = array('nokounter'=>$tmpC);
        $query2 = updateQuery($dbname,'keu_5kelompokjurnal',$updData,$whereNo);
        try{$owlPDO->exec($query2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		
    break;
    
    
    case'savedt':
	
		#bentuk DB
		$str="insert into ".$dbname.".keu_jurnaldt 
				(`nojurnal`,`tanggal`,`nourut`,`noakun`,
				`keterangan`,`jumlah`,`matauang`,`kurs`,
				`kodeorg`,`kodekegiatan`,`kodebarang`,`nik`,
				`kodesupplier`,`noreferensi`,`kodevhc`,`kodeblok`,`nodok`)
		values ('".$nojurnal."','".$per."-28','".($nourutdt-1)."','".$noakun."',
				'".'Jurnal Balik Plasma '.$keterangandt."','".$jumlahdt."','IDR','1',
				'".substr($unit,0,4)."','".$kodekegiatandt."','".$kodebarangdt."','".$nikdt."',
				'".$kodesupplierdt."','".$nojurnaldt."','".$kodevhcdt."','".$kodeblokdt."','".$nodok."')
				,
				('".$nojurnal."','".$per."-28','".($nourutdt)."','".$noakundt."',
				'".'Jurnal Balik Plasma '.$keterangandt."','".($jumlahdt*-1)."','IDR','1',
				'".substr($unit,0,4)."','".$kodekegiatandt."','".$kodebarangdt."','".$nikdt."',
				'".$kodesupplierdt."','".$nojurnaldt."','".$kodevhcdt."','".$kodeblokdt."','".$nodok."')
				";
				//exit("Error:$str");
		try{$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}


		//Jurnal Plasma
		$kodejurnal='M';

		//get kodeblokplasma
	    $sqlkd="select kodeblokplasma from ".$dbname.".kebun_5kud where kodeblok='".$kodeblokdt."'";
	    $ressup=$owlPDO->query($sqlkd);
	    $ressup->setFetchMode(PDO::FETCH_ASSOC);
	    $barsup=$ressup->fetch();
	    $kodeblokplasma=$barsup['kodeblokplasma'];

		//get noakun
	    $sqlkd="select akunhutang from ".$dbname.".keu_5caco where kodeorg='".substr($unit,0,4)."' and jenis='intra'";
	    $ressup=$owlPDO->query($sqlkd);
	    $ressup->setFetchMode(PDO::FETCH_ASSOC);
	    $barsup=$ressup->fetch();
	    $akunhutang=$barsup['akunhutang'];

        if ($akunhutang=='') {
            exit("Warning : Account intraco or interco not available for ".substr($unit,0,4).". Please setting on menu Finance > setup > COA for Intra/Interco.");
        }

		$str="insert into ".$dbname.".keu_jurnaldt 
				(`nojurnal`,`tanggal`,`nourut`,`noakun`,
				`keterangan`,`jumlah`,`matauang`,`kurs`,
				`kodeorg`,`kodekegiatan`,`kodebarang`,`nik`,
				`kodesupplier`,`noreferensi`,`kodevhc`,`kodeblok`,`nodok`)
		values ('".$nojurnalp."','".$per."-28','".($nourutdt-1)."','".$noakundt."',
				'".'Jurnal Balik Plasma '.$keterangandt."','".$jumlahdt."','IDR','1',
				'".substr($kodeblokplasma,0,4)."','".$kodekegiatandt."','".$kodebarangdt."','".$nikdt."',
				'".$kodesupplierdt."','".$nojurnaldt."','".$kodevhcdt."','".$kodeblokplasma."','".$nodok."')
				,
				('".$nojurnalp."','".$per."-28','".($nourutdt)."','".$akunhutang."',
				'".'Jurnal Balik Plasma '.$keterangandt."','".($jumlahdt*-1)."','IDR','1',
				'".substr($kodeblokplasma,0,4)."','".$kodekegiatandt."','".$kodebarangdt."','".$nikdt."',
				'".$kodesupplierdt."','".$nojurnaldt."','".$kodevhcdt."','".$kodeblokplasma."','".$nodok."')
				";

		try{$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}

    break;
	
  
    default;	
	
	
}

?>
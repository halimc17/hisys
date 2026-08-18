<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

// $proses = $_GET['proses'];
$proses	= checkPostGet('proses','');
$param 	= $_POST;

switch($proses) {
    # Daftar Header
    case 'showHeadList':
		$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        if(isset($param['where'])) {
	    	$arrWhere = json_decode(str_replace("\\","",$param['where']),true);
			if(!empty($arrWhere)) {
				foreach($arrWhere as $key=>$r1) {
					if ($r1[0] == 'tanggal') {
						if ($r1[1] != '') {
							$where .= "and tanggal ='".tanggalsystemn($r1[1])."' ";
						}
					}
					if ($r1[0] == 'komoditi') {
						if ($r1[1] != '') {
							$where .= " and kodetangki in (SELECT kodetangki FROM ".$dbname.".pabrik_5tangki WHERE komoditi='".$r1[1]."')";
						}
					}
					if ($r1[0] == 'kodetangki') {
						if ($r1[1] != '') {
							$where .= "and kodetangki ='".$r1[1]."' ";
						}
					}
				}
			} else {
				$where .= null;
			}
		} else {
			$where .= null;
		}
	
		# Header
		$header = array(
			$_SESSION['lang']['nomor'],$_SESSION['lang']['tanggal'],$_SESSION['lang']['pabrik'],$_SESSION['lang']['kodetangki'], $_SESSION['lang']['jumlah'].' CPO','FFA CPO','Moist CPO','Dirt CPO',$_SESSION['lang']['jumlah'].' PK','FFA PK','Moist PK','Dirt PK'
		);
		
		$nmtangki = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodeorg='".$_SESSION['empl']['lokasitugas']."'");

		# Content
		$cols = "notransaksi,tanggal,kodeorg,kodetangki,kuantitas,cpoffa,cpokdair,cpokdkot,kernelquantity,kernelffa,kernelkdair,kernelkdkot";
		$query = selectQuery($dbname,'pabrik_masukkeluartangki',$cols,$where,"notransaksi desc",false,$param['shows'],$param['page']);
		// exit("error: ".$query);
		$data = fetchData($query);
		$totalRow = getTotalRow($dbname,'pabrik_masukkeluartangki',$where);

		foreach($data as $key=>$row) {
			$data[$key]['kodetangki'] = $nmtangki[$row['kodetangki']];
			$data[$key]['kodeorg'] = getNamaOrg($row['kodeorg']);
			$data[$key]['tanggal'] = tanggalnormal($row['tanggal']);
			$data[$key]['kuantitas'] = hidezerodecimal(floatval($row['kuantitas']),3);
			$data[$key]['kernelquantity'] = number_format(floatval($row['kernelquantity']),0);
		}
		
		# Make Table
		$tHeader = new rTable('headTable','headTableBody',$header,$data);
		$tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
		$tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
		//$tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
		$tHeader->_actions[1]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
		$tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
		if(isset($param['where'])) {
			$tHeader->setWhere($arrWhere);
		}
		
		# View
		$tHeader->renderTable();
	break;
    # Form Add Header
    case 'showAdd':
		// View
		echo formHeader('add',array());
		echo "<div id='detailField' style='clear:both'></div>";
	break;
    # Form Edit Header
    case 'showEdit':
		$query = selectQuery($dbname,'pabrik_masukkeluartangki',"*","notransaksi='".$param['notransaksi']."'");
		$tmpData = fetchData($query);
		$data = $tmpData[0];
		$data['tanggal'] = tanggalnormal($data['tanggal']);
		echo formHeader('edit',$data);
		echo "<div id='detailField' style='clear:both'></div>";
	break;
    # Proses Add Header
    case 'add':
		$data = $_POST;
		
		// Error Trap
		$warning = "";
		if($data['notransaksi']=='') {$warning .= "No Transaksi harus diisi\n";}
		if($data['tanggal']=='') {$warning .= "Tanggal harus diisi\n";}
		if($warning!=''){echo "Warning :\n".$warning;exit;}
		
		$data['tanggal'] = tanggalsystem($data['tanggal']);
			$data['kuantitas'] = str_replace(',','',$data['kuantitas']);
		$data['kernelquantity'] = str_replace(',','',$data['kernelquantity']);
		$data['createby'] = $_SESSION['standard']['userid'];
		$data['createtime'] = date('Y-m-d H:i:s');
		unset($data['notransaksi']);
		//	$cols = array('tanggal','kodeorg','kodetangki','kuantitas','suhu',
		//	'cporendemen','cpoffa','cpokdair','cpokdkot',
		//	'kernelquantity','kernelrendemen','kernelkdair','kernelkdkot','kernelffa');
		$cols = array('tanggal','kodeorg','kodetangki','kuantitas','suhu','tinggi',
			'cpoffa','cpokdair','cpokdkot','dobi',
			'kernelquantity','kernelkdair','kernelkdkot','kernelffa','tangkicleanoil','createby','createtime');
		$query = insertQuery($dbname,'pabrik_masukkeluartangki',$data,$cols);
		try
        {
            $owlPDO->exec($query);
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
    # Proses Edit Header
    case 'edit':
		$data = $_POST;
		$where = "notransaksi='".$data['notransaksi']."'";
		unset($data['notransaksi']);
		$data['tanggal'] = tanggalsystem($data['tanggal']);
		$data['kuantitas'] = str_replace(',','',$data['kuantitas']);
		$data['kernelquantity'] = str_replace(',','',$data['kernelquantity']);
		$data['updateby'] = $_SESSION['standard']['userid'];
		$query = updateQuery($dbname,'pabrik_masukkeluartangki',$data,$where);
        try{
            $owlPDO->exec($query);
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
    case 'delete':
		$where = "notransaksi='".$param['notransaksi']."'";
		$query = "delete from `".$dbname."`.`pabrik_masukkeluartangki` where ".$where;
		try
        {
            $owlPDO->exec($query);
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
	case'getVol':
		#= perhitungan memakai faktor koreksi
		#= memakai meja ukur juga
		#= tidak memakai standart suhu kalibrasi
		$str=" select komoditi,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$komoditi=$bar['komoditi'];

		switch ($komoditi) {
			case 'CPO':
				# RUMUS PERHITUNGAN SOUNDING
				if ($param['kodetangki']=='ST03') {//KHUSUS DAILY TANK
					/* DAILY TANK CANDI ARTHA					
						a.	Diameter Tabung			=	4.75	m
						b.	Jari jari Tabung		=	2.375 	m
						c.	Tinggi Tabung			=	4.90	m
						d.	Diameter Sum Tabung		=	0.277	m
						e.	Jari - jari Sum Tabung	=	0.1385	m
						f.	Tinggi Sum Tabung		=	0.18	m 
						g. 	Volume Pipa				=	0.102	m
					*/
						
					#= Vol. Tabung 
					#= rumus vol.tabung = pHi x r2 x h
					#= vol.tabung = 3.14 x 2.375 kuadrat
					$vtab = (3.14 * (237.5/100) * (237.5/100)) * ($param['tinggi']/100);
					#= Vol. Sum 
					#= rumus vol.sum = pHi x r2 x h
					#= vol.sum = 3.14 x 0.1385 kuadrat x 0.18
					$vsum = (1.1/100) * (18/100); 
					#= rumus Vol.Total = Vol. Tabung + Vol. Sum - V. Pipa
					$vtotal = $vtab	+ $vsum - 0.102;
					//Ambil density dari suhu yang diinput
					$suhu=explode(".",$param['suhu']);
					$sSh="select berat_jenis,varian from ".$dbname.".pabrik_5suhu where millcode='".$param['kodeorg']."' 
						and kodetangki='".$param['kodetangki']."' and suhu='".$suhu[0]."'";
					$qSh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
					$qSh->setFetchMode(PDO::FETCH_ASSOC);
					$rSh=$qSh->fetch();
				
					$density 	=$rSh['berat_jenis'];
					@$volTangki	=$vtotal*$density;
					$volTangkiAll=number_format($volTangki,3);
						
					if($volTangkiAll<0){
						$volTangkiAll=0;
					}

				}else{
					// ###sounding untuk bpjm & ksbw & ksbw
					// $str="select nilai from ".$dbname.".pabrik_5mejaukur where 
					// 		kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
					// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					// $res->setFetchMode(PDO::FETCH_ASSOC);
					// $bar=$res->fetch();
					// 	$mejaukur=$bar['nilai'];
					// if($param['tinggi']>=$mejaukur){
					// 	$volTangki=$volTing=0;
					// 	$param['tinggi']=$param['tinggi']+$mejaukur;
					// 	$ting=explode(".",$param['tinggi']);
					// 	$suhu=explode(".",$param['suhu']);
					// 	$sSh="select berat_jenis,varian from ".$dbname.".pabrik_5suhu where millcode='".$param['kodeorg']."' 
					// 		and kodetangki='".$param['kodetangki']."' and suhu='".$suhu[0]."'";
					// 	$qSh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
					// 	$qSh->setFetchMode(PDO::FETCH_ASSOC);
					// 	$rSh=$qSh->fetch();
						
					// 	$sTng="select volume,beda from ".$dbname.".pabrik_5tinggitangki where millcode='".$param['kodeorg']."' 
					// 		and kodetangki='".$param['kodetangki']."' and tinggi='".$ting[0]."'";
					// 	$qTng=$owlPDO->query($sTng) or die(print " Gagal: ".PDOException::getMessage());
					// 	$qTng->setFetchMode(PDO::FETCH_ASSOC);
					// 	$rTng=$qTng->fetch();
						
					// 	$sSuhuKalibrasi="select suhu_kalibrasi from ".$dbname.".pabrik_5standardsuhu_kalibrasi 
					// 					where millcode='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' 
					// 					order by periode desc limit 1";
					// 	$qSuhuKalibrasi=$owlPDO->query($sSuhuKalibrasi) or die(print " Gagal: ".PDOException::getMessage());
					// 	$qSuhuKalibrasi->setFetchMode(PDO::FETCH_ASSOC);
					// 	$rSuhuKalibrasi=$qSuhuKalibrasi->fetch();
						
					// 	#== ambil data faktor koreksi
					// 	// $strfk="select nilai,nilaiangka from ".$dbname.".pabrik_5faktorkoreksi where millcode='".$param['kodeorg']."'  and kodetangki='".$param['kodetangki']."'  ";
					// 	// $resfk=$owlPDO->query($strfk) or die(print " Gagal: ".PDOException::getMessage());
					// 	// $resfk->setFetchMode(PDO::FETCH_ASSOC);
					// 	// $barfk=$resfk->fetch();
					// 	// $nilaikoreksi=$barfk['nilai'];

						
					// 	// @$volTing=$rTng['volume']+round((floatval("0.".$ting[1])*$rTng['beda']));
					// 	@$volTing=$rTng['volume']+(floatval("0.".$ting[0])*$rTng['beda']);
					// 	@$volTangki=$volTing*$rSh['berat_jenis'];
					// 	if($barfk['nilaiangka']>0){
					// 		$nilaikoreksi=$barfk['nilaiangka']+($barfk['nilai']*($suhu[0]-$rSuhuKalibrasi['suhu_kalibrasi']));
					// 	}
					// 	$volTangkiAll=round($volTangki);

					// 	// ##cetakrumus
					// 	// echo "volTing=".$rTng['volume']."+(".floatval("0.".$ting[1]).")*".$rTng['beda'].");";
					// 	// echo "volTangki=".$volTing."*".$rSh['berat_jenis'].";";
					// 	// echo "nilaikoreksi=".$barfk['nilaiangka']."+(".$barfk['nilai']."*(".$suhu[0]."-".$rSuhuKalibrasi['suhu_kalibrasi']."));";
					// 	// echo "volTangkiAll="."round(".$volTangki."*".$nilaikoreksi.");";
					// 	// exit('error');
					// 	// ##cetakrumus
						
					// 	if($volTangkiAll<0){
					// 		$volTangkiAll=0;
					// 	}
					// }else{
					// 	$volTangkiAll=0;
					// }
					// ###sounding untuk bpjm & ksbw
					###sounding untuk bpjm & ksbw & ksbw
					$str="select nilai from ".$dbname.".pabrik_5mejaukur where 
							kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						// $mejaukur=$bar['nilai'];
					// if($param['tinggi']>=$mejaukur){
						$volTangki=$volTing=0;
						$param['tinggi']=$param['tinggi']+$mejaukur;
						$ting=explode(".",$param['tinggi']);
						$suhu=explode(".",$param['suhu']);
						$sSh="select berat_jenis,varian from ".$dbname.".pabrik_5suhu where millcode='".$param['kodeorg']."' 
							and kodetangki='".$param['kodetangki']."' and suhu='".$suhu[0]."'";
						$qSh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
						$qSh->setFetchMode(PDO::FETCH_ASSOC);
						$rSh=$qSh->fetch();
						//Ambil volume saja
						$sTng="select volume from ".$dbname.".pabrik_5tinggitangki where millcode='".$param['kodeorg']."' 
							and kodetangki='".$param['kodetangki']."' and tinggi='".$ting[0]."'";
						$qTng=$owlPDO->query($sTng) or die(print " Gagal: ".PDOException::getMessage());
						$qTng->setFetchMode(PDO::FETCH_ASSOC);
						$rTng=$qTng->fetch();
						//Ambil beda saja
						$sTng2="select beda from ".$dbname.".pabrik_5tinggitangki where millcode='".$param['kodeorg']."' 
							and kodetangki='".$param['kodetangki']."' and tinggi='".round($param['tinggi'])."'";
						$qTng2=$owlPDO->query($sTng2) or die(print " Gagal: ".PDOException::getMessage());
						$qTng2->setFetchMode(PDO::FETCH_ASSOC);
						$rTng2=$qTng2->fetch();
						
						$sSuhuKalibrasi="select suhu_kalibrasi from ".$dbname.".pabrik_5standardsuhu_kalibrasi 
										where millcode='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' 
										order by periode desc limit 1";
						$qSuhuKalibrasi=$owlPDO->query($sSuhuKalibrasi) or die(print " Gagal: ".PDOException::getMessage());
						$qSuhuKalibrasi->setFetchMode(PDO::FETCH_ASSOC);
						$rSuhuKalibrasi=$qSuhuKalibrasi->fetch();
						
						#== ambil data faktor koreksi
						// $strfk="select nilai,nilaiangka from ".$dbname.".pabrik_5faktorkoreksi where millcode='".$param['kodeorg']."'  and kodetangki='".$param['kodetangki']."'  ";
						// $resfk=$owlPDO->query($strfk) or die(print " Gagal: ".PDOException::getMessage());
						// $resfk->setFetchMode(PDO::FETCH_ASSOC);
						// $barfk=$resfk->fetch();
						// $nilaikoreksi=$barfk['nilai'];

						
						// @$volTing=$rTng['volume']+round((floatval("0.".$ting[1])*$rTng['beda']));
						$density 	=$rSh['berat_jenis'];
						$volume		=$rTng['volume'];
						@$volTing	=$volume+(floatval("0.".$ting[1])*$rTng2['beda']);
						@$volTangki	=$volTing*$density;
						// if($barfk['nilaiangka']>0){
						// 	$nilaikoreksi=$barfk['nilaiangka']+($barfk['nilai']*($suhu[0]-$rSuhuKalibrasi['suhu_kalibrasi']));
						// }
						$volTangkiAll=number_format($volTangki,2);

						// ##cetakrumus
						// echo "volTing=".$rTng['volume']."+(".floatval("0.".$ting[1]).")*".$rTng['beda'].");";
						// echo "volTangki=".$volTing."*".$rSh['berat_jenis'].";";
						// echo "nilaikoreksi=".$barfk['nilaiangka']."+(".$barfk['nilai']."*(".$suhu[0]."-".$rSuhuKalibrasi['suhu_kalibrasi']."));";
						// echo "volTangkiAll="."round(".$volTangki."*".$nilaikoreksi.");";
						// exit('error');
						// ##cetakrumus
						
						if($volTangkiAll<0){
							$volTangkiAll=0;
						}
					// }else{
					// 	$volTangkiAll=0;
					// }
					###sounding untuk bpjm & ksbw
				}
				break;
			
			case 'KER':
				# RUMUS PERHITUNGAN SOUNDING CANDI ARTHA MILL (DMA GROUP)
				#= cukup input tinggi (cm) akan mendapatkan volume dengan rumus dibawah.
				#= rumus kernel = Vol. Tabung + Vol.Kerucut
				#= rumus vol.tabung = pHi x r2 x h
				$phi		= 3.14;
				$query  	= selectQuery($dbname,'setup_parameterappl','nilai',"kodeparameter='DIAKERNEL'");
				$hasil  	= fetchData($query);
				if(count($hasil)==0){
					exit("Warningsystem : Diameter Kernel belum ditambahkan, silahkan tambahkan pada menu SETUP > PARAMETER APLIKASI dengan kode parameter DIAKERNEL.");
				}
				$diameter	= $hasil[0]['nilai'];
				$r			= $diameter / 2;
				$r2			= pow($r, 2);
				$query2  	= selectQuery($dbname,'setup_parameterappl','nilai',"kodeparameter='TTABUNG'");
				$hasil2  	= fetchData($query2);
				if(count($hasil2)==0){
					exit("Warningsystem : Tinggi Tabung belum ditambahkan, silahkan tambahkan pada menu SETUP > PARAMETER APLIKASI dengan kode parameter TTABUNG.");
				}
				$tingtabung	= $hasil2[0]['nilai'];
				$h			= $tingtabung - $param['tinggi'];
				$vtbcm3		= $phi * $r2 * $h;
				$vtbm3		= $vtbcm3 / 1000;
				$voltabung	= (($vtbm3 * 700)); //Density Palm Kernel PT CAR (700 kg/m³)
				#= rumus vol.kerucut = 1/3  pHi x r2 x h
				$query3  	= selectQuery($dbname,'setup_parameterappl','nilai',"kodeparameter='TKERUCUT'");
				$hasil3  	= fetchData($query3);
				if(count($hasil3)==0){
					exit("Warningsystem : Tinggi Tabung belum ditambahkan, silahkan tambahkan pada menu SETUP > PARAMETER APLIKASI dengan kode parameter TKERUCUT.");
				}
				$tingkerucut= $hasil3[0]['nilai'];
				$h			= $tingkerucut;
				$vtcutcm3	= (1 * $phi * $r2 * $h) / 3 ;
				$vtcutm3	= $vtcutcm3 / 1000 ;
				$volkerucut	= (($vtcutm3 * 700)); //Density Palm Kernel PT CAR (700 kg/m³)
				//Volume Kerucutnya
				$volTangkiAll= number_format(($voltabung + $volkerucut)/1000);
				break;
			
			default:
				# APABILA KOMODITI SUDAH ADA DI SETUP pabrik_5tangki TAPI BELUM DIBERIKAN PERHITUNGANNYA
				exit("Warningsystem : Perhitungan sounding di tangki ".$bar['keterangan']." belum ada. "); 
				break;
		}
		echo $volTangkiAll."##".$komoditi;
	break;
	case'getsuhuker':
		$sSuhuKalibrasi="select distinct suhu_kalibrasi from ".$dbname.".pabrik_5standardsuhu_kalibrasi where 
			millcode='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' order by periode desc limit 1";
		$qSuhuKalibrasi=$owlPDO->query($sSuhuKalibrasi) or die(print " Gagal: ".PDOException::getMessage());
		$qSuhuKalibrasi->setFetchMode(PDO::FETCH_ASSOC);
		$rSuhuKalibrasi=$qSuhuKalibrasi->fetch();
			echo $rSuhuKalibrasi['suhu_kalibrasi'];
	break;
	//Umar
	case'getSearchTanki':
		$str 	= "SELECT distinct(kodetangki) FROM ".$dbname.".pabrik_5tangki WHERE komoditi='".$param['komoditi']."' ";
		$q 		= $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$q->setFetchMode(PDO::FETCH_ASSOC);
		$optT 	= '<option value="">Pilih Data</option>';
		while ($r = $q->fetch()) {
			$optT .= '<option value="'.$r['kodetangki'].'">'.$r['kodetangki'].'</option>';
		}

		echo $optT;
	break;
	case'getLock':
		$sCek="select komoditi from ".$dbname.".pabrik_5tangki 
		       where kodetangki='".$param['kodetangki']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$rCek=fetchData($sCek);
		
		// #cek ke apakah tangki tersebut
		// $str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PK' and kodeparameter='PKSTK'";
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bar=$res->fetch();
		// 	$kerlantai=$bar['nilai'];
	
			
		// if($param['kodetangki']==$kerlantai){
		// 	$rCek[0]['komoditi']="KER2";
		// }else{
		// 	$rCek[0]['komoditi']=$rCek[0]['komoditi'];
		// }
		
		echo $rCek[0]['komoditi'];
	break;
    default:
	break;
}

function formHeader($mode,$data) {
    global $dbname;
    
    # Default Value
    if(empty($data)) {
		$data['notransaksi'] = '0';
		$data['kodeorg'] = '';
		$data['tanggal'] = '';
		$data['kodetangki'] = '';
		$data['kuantitas'] = '0';
		$data['suhu'] = '0';
		$data['tinggi'] = '0';
		//$data['cporendemen'] = '0';
		$data['cpoffa'] = '0';$data['cpokdair'] = '0';
		$data['cpokdkot'] = '0';$data['kernelquantity'] = '0';
		$data['dobi'] = '0';
		//$data['kernelrendemen'] = '0';
		$data['kernelkdair'] = '0';$data['kernelkdkot'] = '0';$data['kernelffa'] = '0';
		
		$data['tangkicleanoil'] = '0';
    }
    
    # Disabled Primary
    if($mode=='edit') {
		$disabled = 'disabled';
    } else {
		$disabled = '';
    }
    $optTangki=array("0"=>$_SESSION['lang']['pilihdata']);
    # Options
    $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	"(tipe='PABRIK' or tipe='BULKING') and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
    //$optTangki = makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodeorg='".$_SESSION['empl']['lokasitugas']."'",'2');
    $qShift = selectQuery($dbname,'pabrik_5tangki','kodetangki,keterangan,komoditi',"kodeorg='".$_SESSION['empl']['lokasitugas']."'",'komoditi');
    $tmpShift = fetchData($qShift);
    foreach($tmpShift as $key=>$row) {
		$optTangki[$row['kodetangki']] = $row['keterangan'].' - '.$row['komoditi'];
	}	
    $els = array();
    $els[] = array(
	makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
	makeElement('notransaksi','text',$data['notransaksi'],
	    array('style'=>'width:100px','maxlength'=>'12','disabled'=>'disabled'))
    );
    $els[] = array(
	makeElement('tanggal','label',$_SESSION['lang']['tanggal']),
	makeElement('tanggal','text',$data['tanggal'],array('style'=>'width:100px;text-align:center',
	'readonly'=>'readonly','onmousemove'=>'setCalendar(this.id)'))
    );
    $els[] = array(
	makeElement('suhu','label',$_SESSION['lang']['suhu']." / ".$_SESSION['lang']['beratjenis']),
	makeElement('suhu','textnum',$data['suhu'],array('style'=>'width:80px','onblur'=>'getVol()','onclick'=>'this.select()')) ."&nbsp; &deg;C"
    );
	$els[] = array(
	makeElement('dobi','label',$_SESSION['lang']['dobi'],array('style'=>'visibility:hidden')),
	makeElement('dobi','textnum',$data['dobi'],array('style'=>'width:100px;visibility:hidden','onclick'=>'this.select()')).""
    );
    $els[] = array(
	makeElement('kuantitas','label',$_SESSION['lang']['cpokuantitas']),
	makeElement('kuantitas','textnum',number_format($data['kuantitas']),array('style'=>'width:100px','disabled'=>'disabled'))." Kg"
    );
	//$els[] = array(
	//makeElement('cporendemen','label',$_SESSION['lang']['cporendemen']),
	//makeElement('cporendemen','textnum',$data['cporendemen'],array('style'=>'width:100px'))."%"
	//);
    $els[] = array(
	makeElement('cpoffa','label',$_SESSION['lang']['cpoffa']),
	makeElement('cpoffa','textnum',$data['cpoffa'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
    $els[] = array(
	makeElement('cpokdair','label',$_SESSION['lang']['cpokdair']),
	makeElement('cpokdair','textnum',$data['cpokdair'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
    $els[] = array(
	makeElement('cpokdkot','label',$_SESSION['lang']['cpokdkot']),
	makeElement('cpokdkot','textnum',$data['cpokdkot'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
	$els[] = array(
	makeElement('dobi2','label',$_SESSION['lang']['dobi'],array('style'=>'visibility:hidden')),
	makeElement('dobi2','textnum',$data['dobi'],array('style'=>'width:100px;visibility:hidden','onclick'=>'this.select()')).""
    );
    $els[] = array(
	makeElement('kodeorg','label',$_SESSION['lang']['pabrik']),
	makeElement('kodeorg','select',$data['kodeorg'],
	    array('style'=>'width:120px'),$optOrg)
    );
    $els[] = array(
	makeElement('kodetangki','label',$_SESSION['lang']['kodetangki']),
	makeElement('kodetangki','select',$data['kodetangki'],
	    array('style'=>'width:120px','onchange'=>'getLock()'),$optTangki)
    );
	$els[] = array(
	makeElement('tinggi','label',$_SESSION['lang']['tinggi']),
	makeElement('tinggi','textnum',$data['tinggi'],array('style'=>'width:95px','onblur'=>'getVol()','onclick'=>'this.select()'))." cm"
    );
	$els[] = array(
		makeElement('kernelrendemen','label',$_SESSION['lang']['kernelrendemen'],array('style'=>'visibility:hidden')),
		makeElement('kernelrendemen','textnum',$data['kernelrendemen'],array('style'=>'width:100px;visibility:hidden')).""
	);
	$els[] = array(
	makeElement('kernelquantity','label',$_SESSION['lang']['kernelquantity']),
	makeElement('kernelquantity','textnum',number_format($data['kernelquantity']),array('style'=>'width:100px','onclick'=>'this.select()'))." Kg"
	);
    $els[] = array(
	makeElement('kernelffa','label',$_SESSION['lang']['cpoffa']),
	makeElement('kernelffa','textnum',$data['kernelffa'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
    $els[] = array(
	makeElement('kernelkdair','label',$_SESSION['lang']['cpokdair']),
	makeElement('kernelkdair','textnum',$data['kernelkdair'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
    $els[] = array(
	makeElement('kernelkdkot','label',$_SESSION['lang']['kernelkdkot']),
	makeElement('kernelkdkot','textnum',$data['kernelkdkot'],array('style'=>'width:100px','onclick'=>'this.select()'))." %"
    );
	
	$els[] = array(
	makeElement('tangkicleanoil','label',$_SESSION['lang']['tangkicleanoil'],array('style'=>'visibility:hidden')),
	makeElement('tangkicleanoil','textnum',$data['tangkicleanoil'],array('style'=>'width:100px;visibility:hidden','disabled'=>'disabled'))
    );
	
    if($mode=='add') {
	$els['btn'] = array(
	    makeElement('addHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"addDataTable()",'style'=>'margin: 0 auto;'))
	);
    } elseif($mode=='edit') {
	$els['btn'] = array(
	    makeElement('editHead','btn',$_SESSION['lang']['save'],
		array('onclick'=>"editDataTable()"))
	);
    }
    
    if($mode=='add') {
	return genElementMultiDim($_SESSION['lang']['addheader']." (Data sounding)",$els,2,7);
    } elseif($mode=='edit') {
	return genElementMultiDim($_SESSION['lang']['editheader']." (Data sounding)",$els,2,7);
    }
}
?>
<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	require_once('pmn_spk_nospk_slave.php');
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}

require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$table='pmn_batransport';
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}
$urlefil=checkPostGet('urlefil','0');
$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");

$str = "select * from ".$dbname.".organisasi  where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $bar){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
	if($bar['tipe']=='KANWIL'){
		$kodero[$bar['induk']]=$bar['kodeorganisasi'];
	}
}

$str="select * from ".$dbname.".pmn_5kapalponton";
$res=fetchdata($str);
foreach($res as $bar){
	$namakapalponton[$bar['kode']]=$bar['nama'];
}

//Umar
$tab = '';
$namatransportir = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$namaorganisasi  = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// End Umar




switch ($method) {
	
	case'posting':
	
		try {
			$owlPDO->beginTransaction();
			
			/*
			D:biaya transpor sales
			D:hutang transpor (claim)
			K:hutang transpor
			K:Gain loss In transit CPO / PK
			*/
			
			
			
			
			#=
			$str="select sum(rpjumlah) as rpjumlah,sum(rpclaim) as rpclaim,notransaksi,transportir,tanggal,rounit,noakundebet,nospk,kodebarang,tipe from ".$dbname.".pmn_batransport where  notransaksi='".$param['notransaksi']."'"; 
			$res=fetchdata($str);
			foreach($res as $bar){
				$rpjumlah=$bar['rpjumlah'];
				$rpclaim=$bar['rpclaim'];
				$transportir=$bar['transportir'];
				$tanggal=$bar['tanggal'];
				$periode=substr($bar['tanggal'],0,7);
				$unit=$bar['rounit'];
				$noakundebet=$bar['noakundebet'];
				$nospk=$bar['nospk'];
				$kodebarang=$bar['kodebarang'];
				$tipe=$bar['tipe'];
			}
			
			#= coa transportir
			$str="select * from ".$dbname.".log_5supkelompok where  supplierid='".$transportir."' and ((tipe like '%KONTRAKTOR%') or (tipe like '%TRANSPORTIR%')) "; 
			$res=fetchdata($str);
			foreach($res as $bar){
				$noakunkredit=$noakundebetclaim=$bar['noakun'];
			}
			if($noakunkredit==''){
				throw new PDOException("Warning:Noakun kredit masih kosong, silahkan daftarkan di master supplier dengan tipe kontraktor atau transportir");
			}
			if($noakundebet==''){
				throw new PDOException("Warning:Noakun debet masih kosong");
			}
			
			#= akun claim
			if($kodebarang=='40000001'){
				$str="select * from ".$dbname.".setup_parameterappl where  kodeaplikasi='GI' and kodeparameter='GITCPO' "; 
				$res=fetchdata($str);
				foreach($res as $bar){
					$noakunkreditclaim=$bar['nilai'];
				}
			}
			if($kodebarang=='40000002'){
				$str="select * from ".$dbname.".setup_parameterappl where  kodeaplikasi='GI' and kodeparameter='GITPK' "; 
				$res=fetchdata($str);
				foreach($res as $bar){
					$noakunkreditclaim=$bar['nilai'];
				}
			}
			
			
			#= cek periode akuntansi
			$sPeriode="select * from ".$dbname.".setup_periodeakuntansi 
			           where kodeorg='".$unit."' and tutupbuku=0 order by periode desc";
			$rPeriode=fetchdata($sPeriode);
			if($rPeriode[0]['tanggalmulai']>$tanggal){
				throw new PDOException("Tanggal ".$tanggal." diluar periode ".$rPeriode[0]['tanggalmulai']." aktif  unit ".$unit." ");
			}
			
		
			$kodejurnal='BATR';
			$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodekelompok='".$kodejurnal."' and kodeunit='".$unit."' and periode='".$periode."'");
			$tmpKonter = fetchData($query);
			$konter = addZero($tmpKonter[0]['nokounter']+1,3);
			# Prep No Jurnal
			$nojurnal = str_replace('-','',$tanggal)."/".$unit."/".$kodejurnal."/".$konter;
	
			
			$dataRes['header'][] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnal,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$param['notransaksi'],
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);
			$noUrut=1;
			
			if($tipe=='ipkd'){
				#= debet
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakundebet,
					'keterangan'=>'Jurnal BA Transport : '.$param['notransaksi'].' SPK '.$nospk,
					'jumlah'=>$rpjumlah,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$param['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
				
				#= kredit
				
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakunkredit,
					'keterangan'=>'Jurnal BA Transport : '.$param['notransaksi'].' SPK '.$nospk,
					'jumlah'=>$rpjumlah*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$param['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
			
			}
			
			#= claim
			if($rpclaim<0){
				#= debet
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakundebetclaim,
					'keterangan'=>'Jurnal Claim BA Transpor : '.$param['notransaksi'].' SPK '.$nospk,
					'jumlah'=>$rpclaim*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$param['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
				
				#= kredit
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakunkreditclaim,
					'keterangan'=>'Jurnal Claim BA Transpor : '.$param['notransaksi'].' SPK '.$nospk,
					'jumlah'=>$rpclaim,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$param['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
			}
			
		
			
			#= kredit
			
			#= update counter jurnal
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeunit='".$unit."' and kodekelompok='".$kodejurnal."' and periode='".$periode."' ";	
			$owlPDO->exec($str);
			
			$str = "update ".$dbname.".pmn_batransport set posting=1,postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			#= jurnalht
			if($dataRes['header']!=''){
				$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				$owlPDO->exec($queryH);
			}
			
			
			#= jurnaldt
			if($dataRes['detail']!=''){
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
				$owlPDO->exec($queryD);
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
			
			$owlPDO->rollback();
			echo "Warning Posting Gagal \n" . addslashes($e->getMessage());

		}
	
	break;
	
	case'getnospk':
		// print_r($param);exit("Error:A");
		$optspk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($param['tipe']!='' and $param['unit']!=''){
			if($param['tipe']=='sip'){
				$table="pmn_suratperintahpengiriman";
				$str = "select * from ".$dbname.".".$table."  where pt='".$kodept[$param['unit']]."' order by tanggaldo desc";
				$res=fetchdata($str);
				foreach($res as $bar){
					if($param['nospk']==$bar['nodo']){
						$optspk.="<option value='".$bar['nodo']."' selected>".$bar['nodo']."</option>";
					}else{
						$optspk.="<option value='".$bar['nodo']."'>".$bar['nodo']."</option>";
					}
				}
			}else{
				$table="pmn_spk_".$param['tipe'];
				echo $str = "select * from ".$dbname.".".$table."  where kodept='".$kodept[$param['unit']]."' order by tanggal desc";
				// exit('Error');
				$res=fetchdata($str);
				foreach($res as $bar){
					if($param['nospk']==$bar['nospk']){
						$optspk.="<option value='".$bar['nospk']."' selected>".$bar['nospk']."</option>";
					}else{
						$optspk.="<option value='".$bar['nospk']."'>".$bar['nospk']."</option>";
					}
				}
			}
		
			
		}
		// exit("Error:$optspk");
		echo $optspk;
		// exit("Error:A");
	break;
	
	
	case'saveht':
	
		
		
		#bentuk tanggal between
		$arrtanggal=rangeTanggalarr($param['tanggalkirim1'],$param['tanggalkirim2']);
		
		#= validasi
		$texterror='';
		foreach($arrtanggal as $tglcek){
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_batransport where  unit='".$param['unit']."' and tanggalkirim1='".$tglcek."' and nospk='".$param['nospk']."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['jumlah']>0){
				$texterror.="sudah ada data ditanggal  ".tanggalnormal($tglcek)." dengan nomor transaksi ".$bar['notransaksi']."\n ";
			}
			
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pmn_batransport where  unit='".$param['unit']."' and tanggalkirim2='".$tglcek."' and nospk='".$param['nospk']."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['jumlah']>0){
				$texterror.="sudah ada data ditanggal  ".tanggalnormal($tglcek)." dengan nomor transaksi ".$bar['notransaksi']."\n ";
			}
		}
		
		if($texterror!=''){
			echo $texterror;
			exit("Warning:Gagal Proses");
		}
		
		$unit=$param['unit'];
		$tipe=$param['tipe'];
		$tanggal=tanggalsystemn($param['tanggal']);
		$notransaksi = generatenobatransportir();	
		
		
		
		echo $notransaksi;
	break;
	
	case'loaddatadt':

		if($param['print'] == 'pdf'){
			$tab .= "<table cellpading=1 cellspacing=1 border=1 class=sortable width=100% style='font-size:10px'>
  						<thead>
   							<tr class=rowheader>
								<th align=center>".$_SESSION['lang']['nourut']."</th>
								<th align=center>".$_SESSION['lang']['nospk']."</th>
								<th align=center>".$_SESSION['lang']['komoditi']."</th>
								<th align=center>".$_SESSION['lang']['NoKontrak']."</th>
								<th align=center>".$_SESSION['lang']['transportir']."</th>
								<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['nopol']."</th>
								<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['tujuan']."</th>
								<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>
								<th align=center>Tonbag</th>
								<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>
								<th align=center>".$_SESSION['lang']['selisih']."<br>(".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."-".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim'].")</th>
								<th align=center>".$_SESSION['lang']['rpperkg']."</th>
								
								<th align=center>".$_SESSION['lang']['jumlahrp']."</th>
								<th align=center>".$_SESSION['lang']['toleransi']." (%)</th>
								<th align=center>".$_SESSION['lang']['toleransi']." (Kg)</th>
								<th align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['klaim']."<br>(".$_SESSION['lang']['selisih']."-".$_SESSION['lang']['kg']."<br>".$_SESSION['lang']['klaim'].")</th>
								<th align=center>".$_SESSION['lang']['rpperkg']." ".$_SESSION['lang']['klaim']."</th>
								<th align=center>".$_SESSION['lang']['jumlahrp']." ".$_SESSION['lang']['klaim']."</th>
								<th align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['debet']."</th>
   							</tr> 
 						</thead>
   					<tbody id=listdatadt>";
		}
	
		switch($param['tipe']){
			
			case'sip':	
				// exit("Error:A");
				//tanggalbongkar1	tanggalbongkar2
				
				$str = "select * from ".$dbname.".pabrik_bamutasi  where nosip='".$param['nospk']."' and unit='".$param['unit']."' and substr(tanggalbongkar1,1,10) >= '".tanggalsystemn($param['tanggalkirim1'])."'  and substr(tanggalbongkar2,1,10) <= '".tanggalsystemn($param['tanggalkirim2'])."'";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notransaksi']]=$bar['notransaksi'];
					$dttanggalkirimpks[$bar['notransaksi']]=$bar['tanggal'];
					@$dtkgkirim[$bar['notransaksi']]+=$bar['jumlah'];
				}
				
				if(@count($arrnotiketkirim)<1){
					exit("Warning:Nomor SIP untuk ".$param['nospk']." ditanggal ".tanggalsystemn($param['tanggalkirim1'])." s/d ".tanggalsystemn($param['tanggalkirim2'])." belum dibuatkan BA Pengirimannya atau salah pemilihan tanggal, cocokan data transaksi pengiriman dengan tanggal pembuat BA transpor ini");
				}
				
				#= ambil data penerimaannya berasarkan nomor sip dan noreferensi= nomor ba pengirman
				$str = "select * from ".$dbname.".pabrik_bamutasi  where nosip='".$param['nospk']."' and   noreferensi in ('".implode("','",$arrnotiketkirim)."')";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnotiketterima[$bar['noreferensi']]=$bar['notransaksi'];
					@$dtkgterima[$bar['noreferensi']]+=$bar['jumlah'];
					@$dtkgterimaawal[$bar['noreferensi']]+=$bar['jumlah'];
				}
				// if($_SESSION['standard']['username']=='tim.owl3'){
					// echo $str;
				// }
				
				#= ambil data BA untuk rpkg, toleransi, transportir
				$str = "select * from ".$dbname.".pmn_suratperintahpengiriman  where nodo='".$param['nospk']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nodo']]=$bar['harga'];
					$dttransportir[$bar['nodo']]=$bar['transportir'];
					$dtpersentoleransi[$bar['nodo']]=$bar['toleransi'];
					$dtkgtoleransi[$bar['nodo']]=$bar['kgtoleransi'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dtnokontrak[$bar['nodo']]=$bar['nokontrak'];
					$dtnoakundebet[$bar['nodo']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nodo']]=$bar['kodebarang'];
					
				}
				
				#= data lama untuk ambil rp/kg claim
				$str = "select count(*) as jumrow from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					@$datatransaksi=$bar['jumrow'];
				}
			
				$dtrpkgclaim=array();
				if($datatransaksi==0){
					
					#= harga claim ambil dari kontrak
					if(@count($arrnokontrak)>0){
						$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
							// exit("Error:$str");
						$res=fetchdata($str);
						foreach($res as $bar){
							$dtrpkgclaim[$bar['nokontrak']]=$bar['hargasatuan'];
						}
					}
				}else{
					$str = "select * from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						$dtrpkgclaim[$bar['notiket']]=$bar['rpkgclaim'];
						@$dtkgtonbag[$bar['notiket']]=$bar['kgtonbag'];
						if($bar['kgterimaawal']==0){
							@$dtkgterimaawal[$bar['notiket']]=$bar['kgterima'];
						}else{
							@$dtkgterimaawal[$bar['notiket']]=$bar['kgterimaawal'];
						}
						@$dtkgterima[$bar['notiket']]=$bar['kgterima'];
						@$dtkgclaim[$bar['notiket']]=$bar['kgclaim'];
					}
				}
				
				if(@count($arrnotiketkirim)>0){
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						$bgcolor="style=background-color:lightblue;";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center id=nospk".$no." align=left>".$param['nospk']."</td>";
						$tab.="<td align=center id=kodebarang".$no." align=left>".$dtkodebarang[$param['nospk']]."</td>";
						$tab.="<td align=center id=nokontrak".$no." align=left>".$dtnokontrak[$param['nospk']]."</td>";
						$tab.="<td align=center id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center id=notiket".$no." align=left>".$dtnotiketkirim."</td>";
						$tab.="<td align=center id=tanggalkirimpks".$no." align=left>".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=nokendaraan".$no." align=left></td>";
						$tab.="<td align=center id=kgkirim".$no." align=right>".number_format($dtkgkirim[$dtnotiketkirim])."</td>";
						$tab.="<td align=center>".$dtnotiketterima[$dtnotiketkirim]."</td>";
						$tab.="<td align=center id=kgterimaawal".$no.">".$dtkgterimaawal[$dtnotiketkirim]."</td>";
						#= tonbag
						
						$tab.="<td align=center><input type=text id=kgtonbag".$no." style=width:50px onblur=getkgterima(".$no.")  class=myinputtextnumber onkeyup=z.numberFormat('kgtonbag".$no."',2); value='".$dtkgtonbag[$dtnotiketkirim]."' onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>";
					
						$tab.="<td align=center id=kgterima".$no.">".$dtkgterima[$dtnotiketkirim]."</td>";
						#= selisih
							$dtkgselisih[$dtnotiketkirim]=($dtkgterima[$dtnotiketkirim]-$dtkgkirim[$dtnotiketkirim]);
						$tab.="<td align=center id=kgselisih".$no.">".number_format($dtkgselisih[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=rpkg".$no." align=right>".number_format($dtrpkg[$param['nospk']],2)."</td>";
						#= total rp
							$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
						$tab.="<td align=center id=rpjumlah".$no." align=right>".number_format($dttotalrp[$dtnotiketkirim])."</td>";
						
						#= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
						if($dtpersentoleransi[$param['nospk']]>0){
							$tab.="<td align=center id=persentoleransi".$no.">".$dtpersentoleransi[$param['nospk']]."</td>";
							#= toleransi kg-nya
								$dtkgtoleransi[$dtnotiketkirim]=round($dtpersentoleransi[$param['nospk']]/100*$dtkgkirim[$dtnotiketkirim]*-1);
							$tab.="<td align=center id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
						}else{
							$tab.="<td align=center id=persentoleransi".$no.">0</td>";
							#= toleransi kg
								$dtkgtoleransi[$dtnotiketkirim]=$dtkgtoleransi[$param['nospk']]*-1;
							$tab.="<td align=center id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
						}
						
						#= kg claim (kg toleransi - kg selisih)
							$disabledrpkgclaim="";
							$dtkgclaim[$dtnotiketkirim]=$dtkgselisih[$dtnotiketkirim]-$dtkgtoleransi[$dtnotiketkirim];
							if($dtkgclaim[$dtnotiketkirim]>=0){
								$dtkgclaim[$dtnotiketkirim]=0;
								$disabledrpkgclaim="disabled";
							}												
						$tab.="<td align=center id=kgclaim".$no.">".$dtkgclaim[$dtnotiketkirim]."</td>";
						
						if($datatransaksi==0){
							$tab.="<td align=center><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnokontrak[$param['nospk']]]."' id=rpkgclaim".$no." style=width:50px class=myinputtext></td>";
							#= rpclaim = rpkgclaim * kgclaim
								@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnokontrak[$param['nospk']]];
							$tab.="<td align=center id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
						}else{
							$tab.="<td align=center><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnotiketkirim]."' id=rpkgclaim".$no." style=width:50px  class=myinputtext></td>";
							#= rpclaim = rpkgclaim * kgclaim
								@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnotiketkirim];
							$tab.="<td align=center id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
						}
						$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
					$tab.="</tr>";	
				}
				if($param['print'] != 'pdf'){
					$tab.="<tr>";
							$tab.="<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";
				}	
				}
				
			break;
			
			
			case'ipkd':
				$no=0;	
				$arrnotiketkirim=array();
				$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and millcode='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggalkirim1'])."' and '".tanggalsystemn($param['tanggalkirim2'])."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notiket']]=$bar['notiket'];
					$dtnokendaraan[$bar['notiket']]=$bar['nokendaraan'];
					$dtnokontrak[$bar['notiket']]=$bar['nokontrak'];
					$dtkgkirim[$bar['notiket']]=$bar['beratbersih'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dttanggalkirimpks[$bar['notiket']]=$bar['tanggal'];
				}
				
				
				$str = "select * from ".$dbname.".pmn_spk_ipkd  where nospk='".$param['nospk']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nospk']]=$bar['rpkg'];
					$dttransportir[$bar['nospk']]=$bar['transportirdarat'];
					$dtnoakundebet[$bar['nospk']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nospk']]=$bar['kodebarang'];
				}
				
				#= harga claim ambil dari kontrak
				$rpkgclaim=array();
				$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkgclaim[$bar['nokontrak']]=$bar['hargasatuan'];
				}
				
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						$bgcolor="style=background-color:lightblue;";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center id=nospk".$no." align=left>".$param['nospk']."</td>";
						$tab.="<td align=center id=kodebarang".$no." align=left>".$dtkodebarang[$param['nospk']]."</td>";
						$tab.="<td align=center id=nokontrak".$no." align=left>".$dtnokontrak[$dtnotiketkirim]."</td>";
						$tab.="<td align=center id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center id=notiket".$no." align=left>".$dtnotiketkirim."</td>";
						$tab.="<td align=center id=tanggalkirimpks".$no." align=left>".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=nokendaraan".$no." align=left>".$dtnokendaraan[$dtnotiketkirim]."</td>";
						$tab.="<td align=center id=kgkirim".$no." align=right>".number_format($dtkgkirim[$dtnotiketkirim])."</td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center id=kgterimaawal".$no.">".$dtkgterimaawal[$dtnotiketkirim]."</td>";
						#= tonbag
						$tab.="<td align=center><input type=text id=kgtonbag".$no." style=width:50px disabled class=myinputtextnumber></td>";
						$tab.="<td align=center id=kgterima".$no."></td>";
						$tab.="<td align=center id=kgselisih".$no."></td>";
						$tab.="<td align=center id=rpkg".$no." align=right>".number_format($dtrpkg[$param['nospk']],2)."</td>";
						$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
						$tab.="<td align=center id=rpjumlah".$no." align=right>".number_format($dttotalrp[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=persentoleransi".$no."></td>";
						$tab.="<td align=center id=kgtoleransi".$no."></td>";
						$tab.="<td align=center id=kgclaim".$no."></td>";
						$tab.="<td align=center><input type=text  id=rpkgclaim".$no." disabled style=width:50px class=myinputtext></td>";
						$tab.="<td align=center id=rpclaim".$no."></td>";
						$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
				}		
				if($param['print'] != 'pdf'){	
					$tab.="<tr>";
						$tab.="<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";	
				}
			break;
			
			case'etc':
				$arrnotiketkirim=array();
				$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and millcode='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggalkirim1'])."' and '".tanggalsystemn($param['tanggalkirim2'])."'";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notiket']]=$bar['notiket'];
					// $dtnosipb[$bar['notiket']]=$bar['nosipb'];
					$dtnokontrak[$bar['notiket']]=$bar['nokontrak'];
					$dtkgkirim[$bar['notiket']]=$bar['beratbersih'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dttanggalkirimpks[$bar['notiket']]=$bar['tanggal'];
					$dtnokendaraan[$bar['notiket']]=$bar['nokendaraan'];
					$countnotiketterima[$bar['notiket']]=0;
				}
				
				// print_r($arrnotiketkirim);
				// exit("Error:".count($arrnotiketkirim));
				if(count($arrnotiketkirim)>0){
					$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and norefrensi in ('".implode("','",$arrnotiketkirim)."')";
					// echo $str;exit();
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrnotiketterima[$bar['notiket']]=$bar['notiket'];
						$listnotiketterima[$bar['norefrensi']][$bar['notiket']]=$bar['notiket'];
						@$countnotiketterima[$bar['norefrensi']]+=1;
						@$rowspan[$bar['norefrensi']]+=1;
						$dtkgterimadt[$bar['norefrensi']][$bar['notiket']]=$bar['beratbersih'];
						@$dtkgterima[$bar['norefrensi']]+=$bar['beratbersih'];
					}
				}
				
				
				
				
				$str = "select * from ".$dbname.".pmn_spk_etc  where kodept='".$kodept[$param['unit']]."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nospk']]=$bar['rpkg'];
					$dtpersentoleransi[$bar['nospk']]=$bar['toleransi'];
					$dtkgtoleransi[$bar['nospk']]=$bar['kgtoleransi'];
					$dttransportir[$bar['nospk']]=$bar['transportirdarat'];
					$dtnoakundebet[$bar['nospk']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nospk']]=$bar['kodebarang'];
				}
			
				
				#= data lama untuk ambil rp/kg claim
				$str = "select count(*) as jumrow from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					@$datatransaksi=$bar['jumrow'];
				}
			
				
				$dtrpkgclaim=array();
				if($datatransaksi==0){
					#= harga claim ambil dari kontrak
					if(@count($arrnokontrak)>0){
						$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
							// exit("Error:$str");
						$res=fetchdata($str);
						foreach($res as $bar){
							$dtrpkgclaim[$bar['nokontrak']]=$bar['nokontrak'];
							$dtvalidasikontrak[$bar['nokontrak']]=$bar['nokontrak'];
						}
					}
				}else{
					$str = "select * from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						$dtrpkgclaim[$bar['notiket']]=$bar['rpkgclaim'];
					}
				}
				
				// echo"<pre>";
				// print_r($arrnotiketkirim);
				// echo"</pre>";
				// exit('Error');
					
				$counter=$nouruttiket=0;	
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						$bgcolor="style=background-color:lightblue;";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'>".$no."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nospk".$no.">".$param['nospk']."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kodebarang".$no.">".$dtkodebarang[$param['nospk']]."</td>";
						if($dtvalidasikontrak[$dtnokontrak[$dtnotiketkirim]]==$dtnokontrak[$dtnotiketkirim]){
							$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nokontrak".$no.">".$dtnokontrak[$dtnotiketkirim]."</td>";
						}else{
							$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nokontrak".$no."></td>";
						}
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=notiket".$no.">".$dtnotiketkirim."</td>";
						$tab.="<td align=center  rowspan='".$rowspan[$dtnotiketkirim]."' id=tanggalkirimpks".$no.">".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center  rowspan='".$rowspan[$dtnotiketkirim]."' id=nokendaraan".$no.">".$dtnokendaraan[$dtnotiketkirim]."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgkirim".$no.">".$dtkgkirim[$dtnotiketkirim]."</td>";
						$nokirim=0;
						if($countnotiketterima[$dtnotiketkirim]>0){
							foreach($arrnotiketterima as $dtnotiketterima){
								if(@$listnotiketterima[$dtnotiketkirim][$dtnotiketterima]!=''){
									$nokirim++;
									if($nokirim==1){
											$tab.="<td align=center>".$dtnotiketterima."</td>";
											$tab.="<td align=center  id=kgterimaawal".$no.">".$dtkgterimadt[$dtnotiketkirim][$dtnotiketterima]."</td>";
											
											#= tonbag
											$tab.="<td align=center><input type=text id=kgtonbag".$no." style=width:50px disabled class=myinputtextnumber></td>";
											
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgterima".$no.">".$dtkgterima[$dtnotiketkirim]."</td>";
											
											#= selisih
												$dtkgselisih[$dtnotiketkirim]=($dtkgterima[$dtnotiketkirim]-$dtkgkirim[$dtnotiketkirim]);
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgselisih".$no.">".$dtkgselisih[$dtnotiketkirim]."</td>";
											
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpkg".$no.">".$dtrpkg[$param['nospk']]."</td>";
											#= totalrp
												$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpjumlah".$no.">".$dttotalrp[$dtnotiketkirim]."</td>";
											
											
											#= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
											if($dtpersentoleransi[$param['nospk']]>0){
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=persentoleransi".$no.">".$dtpersentoleransi[$param['nospk']]."</td>";
											
												#= toleransi kg-nya
													$dtkgtoleransi[$dtnotiketkirim]=round($dtpersentoleransi[$param['nospk']]/100*$dtkgkirim[$dtnotiketkirim]*-1);
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
												
											}else{
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=persentoleransi".$no.">0</td>";
												#= toleransi kg
													$dtkgtoleransi[$dtnotiketkirim]=$dtkgtoleransi[$param['nospk']]*-1;
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
											}
											
											#= kg claim (kg toleransi - kg selisih)
												$disabledrpkgclaim="";
												$dtkgclaim[$dtnotiketkirim]=$dtkgselisih[$dtnotiketkirim]-$dtkgtoleransi[$dtnotiketkirim];
												if($dtkgclaim[$dtnotiketkirim]>=0){
													$dtkgclaim[$dtnotiketkirim]=0;
													$disabledrpkgclaim="disabled";
												}												
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgclaim".$no.">".$dtkgclaim[$dtnotiketkirim]."</td>";
											
											if($datatransaksi==0){
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]]."' id=rpkgclaim".$no." style=width:50px  class=myinputtext></td>";
												#= rpclaim = rpkgclaim * kgclaim
													@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]];
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
											}else{
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnotiketkirim]."' id=rpkgclaim".$no." style=width:50px  class=myinputtext></td>";
												#= rpclaim = rpkgclaim * kgclaim
													@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnotiketkirim];
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
											}
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
											
										$tab.="</tr>";
									}else{
										$tab.="<tr  ".$bgcolor." class=rowcontent>";
											$tab.="<td align=center>".$dtnotiketterima."</td>";
											$tab.="<td align=center>".$dtkgterimadt[$dtnotiketkirim][$dtnotiketterima]."</td>";
										$tab.="</tr>";
									}
								}
							}
						} else {
							$tab.="<td align=center></td>";
							$tab.="<td align=center id=kgterimaawal".$no."></td>";
							$tab.="<td align=center><input type=text id=kgtonbag".$no." style=width:50px disabled class=myinputtextnumber></td>";
							$tab.="<td align=center id=kgterima".$no."></td>";
							$tab.="<td align=center id=kgselisih".$no."></td>";
							$tab.="<td align=center id=rpkg".$no.">".$dtrpkg[$param['nospk']]."</td>";
								$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
							$tab.="<td align=center id=rpjumlah".$no.">".$dttotalrp[$dtnotiketkirim]."</td>";
							$tab.="<td align=center id=persentoleransi".$no."></td>";
							$tab.="<td align=center id=kgtoleransi".$no."></td>";
							$tab.="<td align=center id=kgclaim".$no."></td>";
							$tab.="<td align=center><input type=text  id=rpkgclaim".$no." disabled onblur=getrpclaim(".$no.") id=rpkgclaim".$no." style=width:50px class=myinputtext></td>";
							$tab.="<td align=center id=rpclaim".$no."></td>";
							$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
						}
				}		
				if($param['print'] != 'pdf'){	
					$tab.="<tr>";
						$tab.="<td align=center colspan=22><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";	
				}
			break;
		}

		if($param['print'] == 'pdf') {
			$tab .= "</tbody></table>";
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("BA TRANSPORT", array("Attachment" => false));
		} else {
			echo $tab;
		}
		
	break;
	
	case'savedt':
	// exit("Error:A");
	
		if($param['currRow']=='1'){
			#= delete 1st
			$str = "delete from ".$dbname.".".$table." where 
				notransaksi='".$param['notransaksi']."'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}	
		}
	
		$param['kgkirim']=str_replace(',', '',$param['kgkirim']);
		$param['kgtonbag']=str_replace(',', '',$param['kgtonbag']);
		$param['kgterima']=str_replace(',', '',$param['kgterima']);
		$param['kgselisih']=str_replace(',', '',$param['kgselisih']);
		$param['rpkg']=str_replace(',', '',$param['rpkg']);
		$param['rpjumlah']=str_replace(',', '',$param['rpjumlah']);
		$param['persentoleransi']=str_replace(',', '',$param['persentoleransi']);
		$param['kgtoleransi']=str_replace(',', '',$param['kgtoleransi']);
		$param['kgclaim']=str_replace(',', '',$param['kgclaim']);
		$param['rpkgclaim']=str_replace(',', '',$param['rpkgclaim']);
		$param['rpclaim']=str_replace(',', '',$param['rpclaim']);
		$param['kgterimaawal']=str_replace(',', '',$param['kgterimaawal']);
		
		
		// echo"<pre>";
		// print_r($param);
		// exit("Error:A");
		#= cari ROnya
		
		$str = "insert into ".$dbname.".".$table."
		(`notransaksi`, `unit`, `tanggal`, `tanggalkirim1`, `tanggalkirim2`, `keterangan`, `tipe`, `nospk`, `nokontrak`, `notiket`, `kgkirim`, `kgtonbag`, `kgterimaawal`, `kgterima`, `kgselisih`, `rpkg`, `rpjumlah`, `persentoleransi`, `kgtoleransi`, `kgclaim`, `rpkgclaim`, `rpclaim`, `createby`, `createtime`, `updateby`,`rounit`,`nokendaraan`,`tanggalkirimpks`,`transportir`,`noakundebet`,`kodebarang`)
		values 
		('".$param['notransaksi']."','".$param['unit']."','".tanggalsystemn($param['tanggal'])."','".tanggalsystemn($param['tanggalkirim1'])."','".tanggalsystemn($param['tanggalkirim2'])."','".$param['keterangan']."','".$param['tipe']."','".$param['nospk']."','".$param['nokontrak']."','".$param['notiket']."','".$param['kgkirim']."','".$param['kgtonbag']."','".$param['kgterimaawal']."','".$param['kgterima']."','".$param['kgselisih']."','".$param['rpkg']."','".$param['rpjumlah']."','".$param['persentoleransi']."','".$param['kgtoleransi']."','".$param['kgclaim']."','".$param['rpkgclaim']."','".$param['rpclaim']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".$kodero[$kodept[$param['unit']]]."','".$param['nokendaraan']."','".tanggalsystemn($param['tanggalkirimpks'])."','".$param['transportir']."','".$param['noakundebet']."','".$param['kodebarang']."')";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

	break;
	
	case'deleteht':
		$str="delete from  ".$dbname.".".$table." where notransaksi='".$param['notransaksi']."' ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	
	
	case'loaddata':
		
		
		$where="1=1";
		
		if($param['tanggalmulai']!='' and $param['tanggalselesai']!=''){
			$where.=" and tanggal between '".tanggalsystemn($param['tanggalmulai'])."' and '".tanggalsystemn($param['tanggalselesai'])."'";
		}

		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		// echo $where;
		
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=16;
	
		$offset = $page * $limit;
		
		$str = "select count(distinct(notransaksi)) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
		
		$no = 0;
		$no=$maxdisplay;
		$statusapp = '';
		$str = "select sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir from ".$dbname.".".$table." where ".$where." group by notransaksi order by tanggal desc limit " . $offset . "," . $limit . " ";
		$res=fetchdata($str);
		foreach($res as $bar){

			#=datakaryawan
			$strdt="select namakaryawan,karyawanid from ".$dbname.".datakaryawan where karyawanid in ('".$bar['createby']."','".$bar['updateby']."') ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namakaryawan[$bardt['karyawanid']]=$bardt['namakaryawan'];
			}
			
			#=supplier/transportir
			$strdt="select * from ".$dbname.".log_5supplier where supplierid='".$bar['transportir']."' ";
			$resdt=fetchdata($strdt);
			foreach($resdt as $bardt){
				$namatransportir[$bardt['transportir']]=$bardt['namasupplier'];
			}
			
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center valign=top>".$no."</td>";
				$tab.="<td valign=top nowrap>".$bar['notransaksi']."</td>";
				$tab.="<td align=center valign=top>".$bar['tipe']."</td>";
				$tab.="<td align=center valign=top>".$bar['nospk']."</td>";
				$tab.="<td align=center valign=top>".$bar['nokontrak']."</td>";
				$tab.="<td align=center valign=top>".$namatransportir[$bar['transportir']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".$bar['unit']."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['kgkirim'],2)."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['kgterima'],2)."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['kgselisih'],2)."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['rpjumlah'],2)."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['kgclaim'],2)."</td>";
				$tab.="<td align=right valign=top>".number_format($bar['rpclaim'],2)."</td>";
				$tab.="<td valign=top>".nl2br($bar['keterangan'])."</td>";
				$tab.="<td valign=top>".$namakaryawan[$bar['createby']]."</td>";
				
				
				if($bar['posting']==0){
					$tab.="<td valign=top><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' caption='Edit' onclick=\"editht('".$bar['notransaksi']."');\"></td>";
					$tab.="<td valign=top><img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' onclick=\"deleteht('".$bar['notransaksi']."');\"></td>";	
					$tab.="<td valign=top><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."','".$page."');\"></td>";					
				}else{
					$tab.="<td></td><td></td><td></td>";
				}
				$tab.="<td valign=top><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."');\"></td>";		
				if($bar['kgclaim']!=0){
					$tab.="<td valign=top><img src=images/pdf.jpg class=zImgBtn  caption='PDF Claim'  title='Print PDF Claim ".$bar['notransaksi']."' onclick=\"pdfclaim('".$bar['notransaksi']."');\"></td>";		
				}else{
					$tab.="<td></td>";
				}
				
			$tab.="</tr>";
        }
		
		
		$str = "select count(distinct(notransaksi)) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $bar = owlBaris($res);
        $totrows = ceil($bar / $limit);
		
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=21 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
	break;
		
		
	case'geteditht':
	
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$param['notransaksi']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$notransaksi=$bar['notransaksi'];
			$unit=$bar['unit'];
			$tipe=$bar['tipe'];
			$tanggal=$bar['tanggal'];
			$tanggalkirim1=$bar['tanggalkirim1'];
			$tanggalkirim2=$bar['tanggalkirim2'];
			$keterangan=$bar['keterangan'];
			$nospk=$bar['nospk'];
	
		echo $notransaksi."###".$unit."###".$tipe."###".$nospk."###".tanggalnormal($tanggal)."###".tanggalnormal($tanggalkirim1)."###".tanggalnormal($tanggalkirim2)."###".$keterangan;
		// exit("Error:a");
	break;	

	// Umar
	case 'pdfbaru':
		error_reporting(0);
		$tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}

			body {
				font-family: Serif, Times-New-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
		</style>";

		$str  = "select * from ".$dbname.".pmn_batransport where notransaksi='".$param['notransaksi']."'";
		$res  = fetchdata($str);
		foreach($res as $bar){
			$notransaksi 	 = $bar['notransaksi'];
			$rounit 		 = $bar['rounit'];
			$pabrik 		 = $bar['unit'];
			$tanggal 		 = $bar['tanggal'];
			$transportir 	 = $bar['transportir'];
			$kodebarang 	 = $bar['kodebarang'];
			$nospk 			 = $bar['nospk'];
			$kgtoleransi 	 = abs($bar['kgtoleransi']);
			$persentoleransi = abs($bar['persentoleransi']);
			$nokontrak 		 = $bar['nokontrak'];
			$tipetransaksi 	 = $bar['tipe'];

			if($bar['kgclaim'] != 0){
				$noclaim++;

				$kgselisih 	+= abs($bar['kgselisih']);
				$kgclaim 	+= abs($bar['kgclaim']);
				$rpkgclaim  =  abs($bar['rpkgclaim']);
				$rpclaim 	+= abs($bar['rpclaim']);
			}
		}
		
		#=supplier/transportir
		$strdt="select * from ".$dbname.".log_5supplier where supplierid='".$transportir."' ";
		$resdt=fetchdata($strdt);
		foreach($resdt as $bardt){
			$namatransportir[$bardt['transportir']]=$bardt['namasupplier'];
		}

		$str1 = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $rounit . "'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
            @$alamatpt = $bar1->alamat . ", " . $bar1->wilayahkota;
            @$telp = $bar1->telepon;
        }

		$arrkodept  	= setheadreport($rounit,$kodept[$rounit]);
		$cellpadding 	= 1;
		$cellspacing 	= 1;
		$sizefont 		= '14';
	
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<div style='position:absolute;right:0;bottom:0;text-align:right'>".tglnmbln(date('Y-m-d'),'i','l')." ".date('H:i:s')."<br><font style='font-size:7px'>Generated By OWL System</font></div>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border='0'>";//logoheight logowidth
			$tab.="<tr>";
				$tab.="<td style='width:20%;' align=center><img src=".$arrkodept['logo']." style='width:150px;height:150px'></td>"; 
				$tab.="<td style='width:80%;vertical-align:top;font-size:".($sizefont+24)."px;line-height: 80%'>";
					$tab.="<br><font>".$arrkodept['nama']."</font><br>";
					$tab.="<i><font style='font-size:20px;font-weight:italic'>".$alamatpt."</font></i><br>";
					$tab.="<i><font style='font-size:20px;font-weight:italic'>".$telp."</font></i>";
				$tab.="</td>";
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		$tab.="<hr>";
		
		$tab.="<br>";

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;line-height:80%'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:center;vertical-align:center;font-weight:bold;font-size:24px'>BA TRANSPORT</td>"; 
			$tab.="</tr>";		
			$tab.="<tr>";
					$tab.="<td style='text-align:center;vertical-align:bottom;font-size:12px'><i>".$param['notransaksi']."</i> (".$tipetransaksi.") / ".$tanggal."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";

		$tab.="<br>";
		
			

		$tab.="<table width='100%' class='sortable' style='font-size:".$sizefont."px;padding-left:50px'>";
			$tab.="<tr>";
					$tab.="<td style='width:20%;text-align:left;vertical-align:center;'>NO SPK</td>"; 
					$tab.="<td style='width:2%'>:</td>"; 
					$tab.="<td style='text-align:left;vertical-align:center;'>".$nospk."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='width:20%;text-align:left;vertical-align:center;'>TRANSPORTIR</td>"; 
					$tab.="<td style='width:2%'>:</td>"; 
					$tab.="<td style='text-align:left;vertical-align:center;'>".$namatransportir[$transportir]."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='width:20%;text-align:left;vertical-align:center;'>TANGGAL</td>"; 
					$tab.="<td style='width:2%'>:</td>"; 
					$tab.="<td style='text-align:left;vertical-align:center;'>".tglnmbln($tanggal, 'i', 'l')."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='width:20%;text-align:left;vertical-align:center;'>UNIT PABRIK</td>"; 
					$tab.="<td style='width:2%'>:</td>"; 
					$tab.="<td style='text-align:left;vertical-align:center;'>".$namaorganisasi[$pabrik]."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='width:20%;text-align:left;vertical-align:center;'>NO KONTRAK</td>"; 
					$tab.="<td style='width:2%'>:</td>"; 
					$tab.="<td style='text-align:left;vertical-align:center;'>".(($nokontrak != '') ? $nokontrak : "-")."</td>";
			$tab.="</tr>";
		$tab.="</table>";

		$tab.="<br>";

		$data = array();
		$str  = "select sum(kgkirim) as kgkirim,sum(kgterima) as kgterima,sum(kgselisih) as kgselisih,sum(rpjumlah) as rpjumlah,sum(kgclaim) as kgclaim,sum(rpclaim) as rpclaim,createby,updateby,notransaksi,tanggal,unit,keterangan,tipe,posting,nokontrak,nospk,transportir from ".$dbname.".".$table." where notransaksi = '".$param['notransaksi']."'";
		$res  = fetchdata($str);
		foreach ($res as $bar) {
			$data = $bar;
		}

		$tab.="<table width='100%' class='sortable' style='font-size:".$sizefont."px;' border=1 cellpadding=1 cellspacing=0>";
			// $tab.="<tr class='rowheader' style='background-color:lightblue;'>";
			$tab.="<tr class='rowheader'>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Berat Bersih Kirim</th>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Berat Bersih Diterima</th>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Selisih</th>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Jumlah (Rp)</th>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Berat Bersih Klaim</th>";
				$tab.="<th style='text-align:center;vertical-align:center;'>Jumlah Klaim (Rp)</th>";
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['kgkirim'],2)."</td>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['kgterima'],2)."</td>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['kgselisih'],2)."</td>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['rpjumlah'],2)."</td>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['kgclaim'],2)."</td>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:5px'>".number_format($data['rpclaim'],2)."</td>";
			$tab.="</tr>";
			// $tab.="<tr class='rowheader' style='background-color:lightblue;'>";
			$tab.="<tr class='rowheader'>";
				$tab.="<th style='text-align:center;vertical-align:center;' colspan='6'>Keterangan</th>";
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center;vertical-align:center;padding:10px' colspan='6'>".$data['keterangan']."</td>";
			$tab.="</tr>";
		$tab.="</table>";

		$tab.="<div style='page-break-after: always;'></div>";

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;line-height:80%'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:center;vertical-align:center;font-weight:bold;font-size:24px'>LIST DATA</td>"; 
			$tab.="</tr>";
		$tab.="</table>";

		$tab.="<hr>";
		$tab.="<br>";
		if($param['print'] == 'pdf'){
			$tab .= "<table cellpadding=1 cellspacing=0 border='1' class=sortable width=100% style='font-size:10px'>";			
		}else{
			$tab .= "<table cellpadding=1 cellspacing=1 border='0' class=sortable width=100% style='font-size:10px'>";			
		}
  					$tab .= "<thead>
   							<tr class=rowheader>
   								<th align=center>".$_SESSION['lang']['nourut']."</th>
								<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['nopol']."</th>
								<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim']."</th>
								<th align=center>".$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['tujuan']."</th>
								<th align=center>".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>
								<th align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."</th>
								<th align=center>".$_SESSION['lang']['selisih']."<br>(".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['diterima']."-".$_SESSION['lang']['beratBersih']." ".$_SESSION['lang']['kirim'].")</th>
								<th align=center>".$_SESSION['lang']['rpperkg']."</th>
								<th align=center>".$_SESSION['lang']['jumlahrp']."</th>
								<th align=center>".$_SESSION['lang']['toleransi']." (%)</th>
								<th align=center>".$_SESSION['lang']['toleransi']." (Kg)</th>
								<th align=center>".$_SESSION['lang']['kg']." ".$_SESSION['lang']['klaim']."<br>(".$_SESSION['lang']['selisih']."-".$_SESSION['lang']['kg']."<br>".$_SESSION['lang']['klaim'].")</th>
								<th align=center>".$_SESSION['lang']['rpperkg']." ".$_SESSION['lang']['klaim']."</th>
								<th align=center>".$_SESSION['lang']['jumlahrp']." ".$_SESSION['lang']['klaim']."</th>
								<th align=center>".$_SESSION['lang']['noakun']." ".$_SESSION['lang']['debet']."</th>
   							</tr> 
 						</thead>
   					<tbody id=listdatadt>";

   		switch($param['tipe']){
			case'sip':	
				// exit("Error:A");
				$str = "select * from ".$dbname.".pabrik_bamutasi  where nosip='".$param['nospk']."' and unit='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggalkirim1'])."' and '".tanggalsystemn($param['tanggalkirim2'])."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notransaksi']]=$bar['notransaksi'];
					$dttanggalkirimpks[$bar['notransaksi']]=$bar['tanggal'];
					@$dtkgkirim[$bar['notransaksi']]+=$bar['jumlah'];
				}
				
				if(@count($arrnotiketkirim)<1){
					exit("Warning:Nomor SIP untuk ".$param['nospk']." belum dibuatkan BA Pengirimannya");
				}
				
				#= ambil data penerimaannya berasarkan nomor sip dan noreferensi= nomor ba pengirman
				$str = "select * from ".$dbname.".pabrik_bamutasi  where nosip='".$param['nospk']."' and   noreferensi in ('".implode("','",$arrnotiketkirim)."')";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtnotiketterima[$bar['noreferensi']]=$bar['notransaksi'];
					@$dtkgterima[$bar['noreferensi']]+=$bar['jumlah'];
				}
				
				
				#= ambil data BA untuk rpkg, toleransi, transportir
				$str = "select * from ".$dbname.".pmn_suratperintahpengiriman  where nodo='".$param['nospk']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nodo']]=$bar['harga'];
					$dttransportir[$bar['nodo']]=$bar['transportir'];
					$dtpersentoleransi[$bar['nodo']]=$bar['toleransi'];
					$dtkgtoleransi[$bar['nodo']]=$bar['kgtoleransi'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dtnokontrak[$bar['nodo']]=$bar['nokontrak'];
					$dtnoakundebet[$bar['nodo']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nodo']]=$bar['kodebarang'];
					
				}
				
				#= data lama untuk ambil rp/kg claim
				$str = "select count(*) as jumrow from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					@$datatransaksi=$bar['jumrow'];
				}
			
				
				$dtrpkgclaim=array();
				if($datatransaksi==0){
					#= harga claim ambil dari kontrak
					if(@count($arrnokontrak)>0){
						$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
							// exit("Error:$str");
						$res=fetchdata($str);
						foreach($res as $bar){
							$dtrpkgclaim[$bar['nokontrak']]=$bar['hargasatuan'];
						}
					}
				}else{
					$str = "select * from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						$dtrpkgclaim[$bar['notiket']]=$bar['rpkgclaim'];
					}
				}
				
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						// $bgcolor="style=background-color:lightblue;";
						$bgcolor="";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center>".$no."</td>";
						// $tab.="<td align=center id=nospk".$no." align=left>".$param['nospk']."</td>";
						// $tab.="<td align=center id=kodebarang".$no." align=left>".$dtkodebarang[$param['nospk']]."</td>";
						// $tab.="<td align=center id=nokontrak".$no." align=left>".$dtnokontrak[$param['nospk']]."</td>";
						// $tab.="<td align=center id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center id=notiket".$no." align=left>".$dtnotiketkirim."</td>";
						$tab.="<td align=center id=tanggalkirimpks".$no." align=left>".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=nokendaraan".$no." align=left></td>";
						$tab.="<td align=center id=kgkirim".$no." align=right>".number_format($dtkgkirim[$dtnotiketkirim])."</td>";
						$tab.="<td align=center>".$dtnotiketterima[$dtnotiketkirim]."</td>";
						$tab.="<td align=center>".$dtkgterima[$dtnotiketkirim]."</td>";
						$tab.="<td align=center id=kgterima".$no.">".$dtkgterima[$dtnotiketkirim]."</td>";
						#= selisih
							$dtkgselisih[$dtnotiketkirim]=($dtkgterima[$dtnotiketkirim]-$dtkgkirim[$dtnotiketkirim]);
						$tab.="<td align=center id=kgselisih".$no.">".number_format($dtkgselisih[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=rpkg".$no." align=right>".number_format($dtrpkg[$param['nospk']],2)."</td>";
						#= total rp
							$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
						$tab.="<td align=center id=rpjumlah".$no." align=right>".number_format($dttotalrp[$dtnotiketkirim])."</td>";
						
						#= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
						if($dtpersentoleransi[$param['nospk']]>0){
							$tab.="<td align=center id=persentoleransi".$no.">".$dtpersentoleransi[$param['nospk']]."</td>";
							#= toleransi kg-nya
								$dtkgtoleransi[$dtnotiketkirim]=round($dtpersentoleransi[$param['nospk']]/100*$dtkgkirim[$dtnotiketkirim]*-1);
							$tab.="<td align=center id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
						}else{
							$tab.="<td align=center id=persentoleransi".$no.">0</td>";
							#= toleransi kg
								$dtkgtoleransi[$dtnotiketkirim]=$dtkgtoleransi[$param['nospk']]*-1;
							$tab.="<td align=center id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
						}
						
						#= kg claim (kg toleransi - kg selisih)
							$disabledrpkgclaim="";
							$dtkgclaim[$dtnotiketkirim]=$dtkgselisih[$dtnotiketkirim]-$dtkgtoleransi[$dtnotiketkirim];
							if($dtkgclaim[$dtnotiketkirim]>=0){
								$dtkgclaim[$dtnotiketkirim]=0;
								$disabledrpkgclaim="disabled";
							}												
						$tab.="<td align=center id=kgclaim".$no.">".$dtkgclaim[$dtnotiketkirim]."</td>";
						
						if($datatransaksi==0){
							if($param['print'] != 'pdf'){
								$tab.="<td align=center><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnokontrak[$param['nospk']]]."' id=rpkgclaim".$no." size=20  class=myinputtext></td>";								
							}else{
								$tab.="<td align=center>'".@$dtrpkgclaim[$dtnokontrak[$param['nospk']]]."'</td>";								
							}
							#= rpclaim = rpkgclaim * kgclaim
								@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnokontrak[$param['nospk']]];
							$tab.="<td align=center id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
						}else{
							if($param['print'] != 'pdf'){
								$tab.="<td align=center><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnotiketkirim]."' id=rpkgclaim".$no." size=20  class=myinputtext></td>";
							}else{
								$tab.="<td align=center>'".@$dtrpkgclaim[$dtnotiketkirim]."'</td>";
							}
							#= rpclaim = rpkgclaim * kgclaim
								@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnotiketkirim];
							$tab.="<td align=center id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
						}
						$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
					$tab.="</tr>";	
				}
				if($param['print'] != 'pdf'){
					$tab.="<tr>";
							$tab.="<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";
				}	
			break;
			
			case'ipkd':
				$no=0;	
				$arrnotiketkirim=array();
				$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and millcode='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggalkirim1'])."' and '".tanggalsystemn($param['tanggalkirim2'])."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notiket']]=$bar['notiket'];
					$dtnokendaraan[$bar['notiket']]=$bar['nokendaraan'];
					$dtnokontrak[$bar['notiket']]=$bar['nokontrak'];
					$dtkgkirim[$bar['notiket']]=$bar['beratbersih'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dttanggalkirimpks[$bar['notiket']]=$bar['tanggal'];
				}
				
				
				$str = "select * from ".$dbname.".pmn_spk_ipkd  where nospk='".$param['nospk']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nospk']]=$bar['rpkg'];
					$dttransportir[$bar['nospk']]=$bar['transportirdarat'];
					$dtnoakundebet[$bar['nospk']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nospk']]=$bar['kodebarang'];
				}
				
				#= harga claim ambil dari kontrak
				$rpkgclaim=array();
				$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkgclaim[$bar['nokontrak']]=$bar['hargasatuan'];
				}
				
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						// $bgcolor="style=background-color:lightblue;";
						$bgcolor="";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center>".$no."</td>";
						// $tab.="<td align=center id=nospk".$no." align=left>".$param['nospk']."</td>";
						// $tab.="<td align=center id=kodebarang".$no." align=left>".$dtkodebarang[$param['nospk']]."</td>";
						// $tab.="<td align=center id=nokontrak".$no." align=left>".$dtnokontrak[$dtnotiketkirim]."</td>";
						// $tab.="<td align=center id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center id=notiket".$no." align=left>".$dtnotiketkirim."</td>";
						$tab.="<td align=center id=tanggalkirimpks".$no." align=left>".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=nokendaraan".$no." align=left>".$dtnokendaraan[$dtnotiketkirim]."</td>";
						$tab.="<td align=center id=kgkirim".$no." align=right>".number_format($dtkgkirim[$dtnotiketkirim])."</td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						$tab.="<td align=center id=kgterima".$no."></td>";
						$tab.="<td align=center id=kgselisih".$no."></td>";
						$tab.="<td align=center id=rpkg".$no." align=right>".number_format($dtrpkg[$param['nospk']],2)."</td>";
						$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
						$tab.="<td align=center id=rpjumlah".$no." align=right>".number_format($dttotalrp[$dtnotiketkirim])."</td>";
						$tab.="<td align=center id=persentoleransi".$no."></td>";
						$tab.="<td align=center id=kgtoleransi".$no."></td>";
						$tab.="<td align=center id=kgclaim".$no."></td>";
						if($param['print'] != 'pdf'){							
							$tab.="<td align=center><input type=text  id=rpkgclaim".$no." disabled class=myinputtext></td>";
						}else{
							$tab.="<td align=center></td>";
						}
						$tab.="<td align=center id=rpclaim".$no."></td>";
						$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
				}		
				if($param['print'] != 'pdf'){	
					$tab.="<tr>";
						$tab.="<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";	
				}
			break;
			
			case'etc':
				$arrnotiketkirim=array();
				$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and millcode='".$param['unit']."' and tanggal between '".tanggalsystemn($param['tanggalkirim1'])."' and '".tanggalsystemn($param['tanggalkirim2'])."'";
				// echo $str;
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrnotiketkirim[$bar['notiket']]=$bar['notiket'];
					// $dtnosipb[$bar['notiket']]=$bar['nosipb'];
					$dtnokontrak[$bar['notiket']]=$bar['nokontrak'];
					$dtkgkirim[$bar['notiket']]=$bar['beratbersih'];
					$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
					$dttanggalkirimpks[$bar['notiket']]=$bar['tanggal'];
					$dtnokendaraan[$bar['notiket']]=$bar['nokendaraan'];
					$countnotiketterima[$bar['notiket']]=0;
				}
				
				// print_r($arrnotiketkirim);
				// exit("Error:".count($arrnotiketkirim));
				if(count($arrnotiketkirim)>0){
					$str = "select * from ".$dbname.".pabrik_timbangan_vw  where nosipb='".$param['nospk']."' and norefrensi in ('".implode("','",$arrnotiketkirim)."')";
					// echo $str;exit();
					$res=fetchdata($str);
					foreach($res as $bar){
						$arrnotiketterima[$bar['notiket']]=$bar['notiket'];
						$listnotiketterima[$bar['norefrensi']][$bar['notiket']]=$bar['notiket'];
						@$countnotiketterima[$bar['norefrensi']]+=1;
						@$rowspan[$bar['norefrensi']]+=1;
						$dtkgterimadt[$bar['norefrensi']][$bar['notiket']]=$bar['beratbersih'];
						@$dtkgterima[$bar['norefrensi']]+=$bar['beratbersih'];
					}
				}
				
				
				
				
				$str = "select * from ".$dbname.".pmn_spk_etc  where kodept='".$kodept[$param['unit']]."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpkg[$bar['nospk']]=$bar['rpkg'];
					$dtpersentoleransi[$bar['nospk']]=$bar['toleransi'];
					$dtkgtoleransi[$bar['nospk']]=$bar['kgtoleransi'];
					$dttransportir[$bar['nospk']]=$bar['transportirdarat'];
					$dtnoakundebet[$bar['nospk']]=$bar['noakundebet'];
					$dtkodebarang[$bar['nospk']]=$bar['kodebarang'];
				}
			
				
				#= data lama untuk ambil rp/kg claim
				$str = "select count(*) as jumrow from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					@$datatransaksi=$bar['jumrow'];
				}
			
				
				$dtrpkgclaim=array();
				if($datatransaksi==0){
					#= harga claim ambil dari kontrak
					if(@count($arrnokontrak)>0){
						$str = "select * from ".$dbname.".pmn_kontrakjual  where nokontrak in ('".implode("','",$arrnokontrak)."')";
							// exit("Error:$str");
						$res=fetchdata($str);
						foreach($res as $bar){
							$dtrpkgclaim[$bar['nokontrak']]=$bar['nokontrak'];
							$dtvalidasikontrak[$bar['nokontrak']]=$bar['nokontrak'];
						}
					}
				}else{
					$str = "select * from ".$dbname.".pmn_batransport  where notransaksi='".$param['notransaksi']."'";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						$dtrpkgclaim[$bar['notiket']]=$bar['rpkgclaim'];
					}
				}
				
				// echo"<pre>";
				// print_r($arrnotiketkirim);
				// echo"</pre>";
				// exit('Error');
					
				$counter=$nouruttiket=0;	
				foreach($arrnotiketkirim as $dtnotiketkirim){
					@$nouruttiket++;
					if($nouruttiket%2==0){
						// $bgcolor="style=background-color:lightblue;";
						$bgcolor="";
					}else{
						$bgcolor="";
					}
					@$no++;	
					$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'>".$no."</td>";
						// $tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nospk".$no.">".$param['nospk']."</td>";
						// $tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kodebarang".$no.">".$dtkodebarang[$param['nospk']]."</td>";
						// if($dtvalidasikontrak[$dtnokontrak[$dtnotiketkirim]]==$dtnokontrak[$dtnotiketkirim]){
						// 	$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nokontrak".$no.">".$dtnokontrak[$dtnotiketkirim]."</td>";
						// }else{
						// 	$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=nokontrak".$no."></td>";
						// }
						// $tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=transportir".$no.">".$dttransportir[$param['nospk']]."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=notiket".$no.">".$dtnotiketkirim."</td>";
						$tab.="<td align=center  rowspan='".$rowspan[$dtnotiketkirim]."' id=tanggalkirimpks".$no.">".tanggalnormal($dttanggalkirimpks[$dtnotiketkirim])."</td>";
						$tab.="<td align=center  rowspan='".$rowspan[$dtnotiketkirim]."' id=nokendaraan".$no.">".$dtnokendaraan[$dtnotiketkirim]."</td>";
						$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgkirim".$no.">".$dtkgkirim[$dtnotiketkirim]."</td>";
						$nokirim=0;
						if($countnotiketterima[$dtnotiketkirim]>0){
							foreach($arrnotiketterima as $dtnotiketterima){
								if(@$listnotiketterima[$dtnotiketkirim][$dtnotiketterima]!=''){
									$nokirim++;
									if($nokirim==1){
											$tab.="<td align=center>".$dtnotiketterima."</td>";
											$tab.="<td align=center>".$dtkgterimadt[$dtnotiketkirim][$dtnotiketterima]."</td>";
											
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgterima".$no.">".$dtkgterima[$dtnotiketkirim]."</td>";
											
											#= selisih
												$dtkgselisih[$dtnotiketkirim]=($dtkgterima[$dtnotiketkirim]-$dtkgkirim[$dtnotiketkirim]);
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgselisih".$no.">".$dtkgselisih[$dtnotiketkirim]."</td>";
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpkg".$no.">".$dtrpkg[$param['nospk']]."</td>";
											#= totalrp
												$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpjumlah".$no.">".$dttotalrp[$dtnotiketkirim]."</td>";
											
											
											#= toleransi, jika persen terisi maka hitung kg, jika kg terisi maka persen di 0-kan
											if($dtpersentoleransi[$param['nospk']]>0){
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=persentoleransi".$no.">".$dtpersentoleransi[$param['nospk']]."</td>";
											
												#= toleransi kg-nya
													$dtkgtoleransi[$dtnotiketkirim]=round($dtpersentoleransi[$param['nospk']]/100*$dtkgkirim[$dtnotiketkirim]*-1);
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
												
											}else{
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=persentoleransi".$no.">0</td>";
												#= toleransi kg
													$dtkgtoleransi[$dtnotiketkirim]=$dtkgtoleransi[$param['nospk']]*-1;
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgtoleransi".$no.">".$dtkgtoleransi[$dtnotiketkirim]."</td>";
											}
											
											#= kg claim (kg toleransi - kg selisih)
												$disabledrpkgclaim="";
												$dtkgclaim[$dtnotiketkirim]=$dtkgselisih[$dtnotiketkirim]-$dtkgtoleransi[$dtnotiketkirim];
												if($dtkgclaim[$dtnotiketkirim]>=0){
													$dtkgclaim[$dtnotiketkirim]=0;
													$disabledrpkgclaim="disabled";
												}												
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=kgclaim".$no.">".$dtkgclaim[$dtnotiketkirim]."</td>";
											
											if($datatransaksi==0){
												if($param['print'] != 'pdf'){
													$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]]."' id=rpkgclaim".$no." size=20  class=myinputtext></td>";													
												}else{
													$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'>'".@$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]]."'</td>";	
												}
												#= rpclaim = rpkgclaim * kgclaim
													@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]];
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
											}else{
												if($param['print'] != 'pdf'){												
													$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'><input type=text  id=rpkgclaim".$no." ".$disabledrpkgclaim." onblur=getrpclaim(".$no.") value='".@$dtrpkgclaim[$dtnotiketkirim]."' id=rpkgclaim".$no." size=20  class=myinputtext></td>";
												}else{
													$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."'>'".@$dtrpkgclaim[$dtnokontrak[$dtnotiketkirim]]."'</td>";	
												}
												
												#= rpclaim = rpkgclaim * kgclaim
													@$dtrpclaim[$dtnotiketkirim]=$dtkgclaim[$dtnotiketkirim]*$dtrpkgclaim[$dtnotiketkirim];
												$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=rpclaim".$no.">".$dtrpclaim[$dtnotiketkirim]."</td>";
											}
											$tab.="<td align=center rowspan='".$rowspan[$dtnotiketkirim]."' id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
											
										$tab.="</tr>";
									}else{
										$tab.="<tr  ".$bgcolor." class=rowcontent>";
											$tab.="<td align=center>".$dtnotiketterima."</td>";
											$tab.="<td align=center>".$dtkgterimadt[$dtnotiketkirim][$dtnotiketterima]."</td>";
										$tab.="</tr>";
									}
								}
							}
						} else {
							$tab.="<td align=center></td>";
							$tab.="<td align=center></td>";
							$tab.="<td align=center id=kgterima".$no."></td>";
							$tab.="<td align=center id=kgselisih".$no."></td>";
							$tab.="<td align=center id=rpkg".$no.">".$dtrpkg[$param['nospk']]."</td>";
								$dttotalrp[$dtnotiketkirim]=$dtkgkirim[$dtnotiketkirim]*$dtrpkg[$param['nospk']];
							$tab.="<td align=center id=rpjumlah".$no.">".$dttotalrp[$dtnotiketkirim]."</td>";
							$tab.="<td align=center id=persentoleransi".$no."></td>";
							$tab.="<td align=center id=kgtoleransi".$no."></td>";
							$tab.="<td align=center id=kgclaim".$no."></td>";
							$tab.="<td align=center><input type=text  id=rpkgclaim".$no." disabled onblur=getrpclaim(".$no.") id=rpkgclaim".$no." size=20  class=myinputtext></td>";
							$tab.="<td align=center id=rpclaim".$no."></td>";
							$tab.="<td align=center id=noakundebet".$no.">".$dtnoakundebet[$param['nospk']]."</td>";
						}
				}		
				if($param['print'] != 'pdf'){	
					$tab.="<tr>";
						$tab.="<td align=center colspan=21><button  id=save class=mybutton onclick=savedt(".$no.")>".$_SESSION['lang']['save']."</button>";	
					$tab.="</tr>";	
				}
			break;
		}

		$tab.="<div style='position:absolute;right:0;bottom:0;text-align:right'>".tglnmbln(date('Y-m-d'),'i','l')." ".date('H:i:s')."<br><font style='font-size:7px'>Generated By OWL System</font></div>";
		$tab.="<div style='position:absolute;left:0;bottom:0;text-align:right'><i>Total Record : ".$no." Data</i></div>";

		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream("BA TRANSPORT", array("Attachment" => false));
	break;
	// End Umar
		
	
	case'pdfclaim':
		$tab="<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";
		$noclaim=$kgselisih=$rpclaim=$kgclaim=0;
		$str="select * from ".$dbname.".pmn_batransport where notransaksi='".$param['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$rounit=$bar['rounit'];
			$tanggal=$bar['tanggal'];
			$notransaksi=$bar['notransaksi'];
			$transportir=$bar['transportir'];
			$kodebarang=$bar['kodebarang'];
			$nospk=$bar['nospk'];
			$kgtoleransi=abs($bar['kgtoleransi']);
			$persentoleransi=abs($bar['persentoleransi']);
			$nokontrak=$bar['nokontrak'];
			$tipe=$bar['tipe'];
			if($bar['kgclaim']!=0){
				$noclaim++;
				$kgselisih+=abs($bar['kgselisih']);
				$kgclaim+=abs($bar['kgclaim']);
				$rpkgclaim=abs($bar['rpkgclaim']);
				$rpclaim+=abs($bar['rpclaim']);
			}
		}
		// exit("Error".$kodebarang.___.$arrinisial[$kodebarang]);
		if($tipe=='sip'){
			$str="select * from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$nospk."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				// $namakapal=$bar['namakapal	namaponton']
				$texttransport='Kapal / Ponton ';
				$texttransport="Kapal ".$namakapalponton[$bar['namakapal']]." / Ponton ".$namakapalponton[$bar['namaponton']]." ";
				$texttransportdua='';
				$texttimbangansounding='Timbangan ';
				$pelabuhantujuan=$bar['pelabuhanbongkar'];
			}
		}else{
			$str="select * from ".$dbname.".pmn_spk_etc where nospk='".$nospk."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				// if($bar['transportir']!=''){
					// $texttransport=$texttransportdua='Kapal / Ponton ';
					// $texttimbangansounding='Sounding ';
				// }
				// if($bar['transportirdarat']!=''){
					$texttransport=$texttransportdua='Truck ';
					$texttimbangansounding='Timbangan ';
				// }
				$pelabuhantujuan=$bar['pelabuhantujuan'];
			}
		}
		// exit("Error:".$texttransport);
		$str="select * from ".$dbname.".pmn_5franco";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namafranco[$bar['id_franco']]=$bar['franco_name'];
		}
		
		if($tipe=='sip'){
			$texttoleransipersen=" ".hidezerodecimal($persentoleransi)." % ";
			$texttoleransi=" ".hidezerodecimal($kgtoleransi)." Kg per kapal / ponton dari angka timbangan";
		}else{
			if($persentoleransi!=0){
				$texttoleransi=" ".hidezerodecimal($persentoleransi)." % dari angka sounding";
			}
			
			if($kgtoleransi!=0){
				$texttoleransi=" ".hidezerodecimal($kgtoleransi)." Kg per truck dari angka timbangan";
			}
		}
		
		$str="select * from ".$dbname.".log_5supplier where supplierid='".$transportir."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasupplier=$bar['namasupplier'];
			$namapemiliksupplier=$bar['namapenanggungjawab'];
		}
		
		$str="select * from ".$dbname.".log_5supalamat where supplierid='".$transportir."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$alamatsupplier=$bar['alamat'];
		}
		
		
		$arrkodept = setheadreport($rounit,$kodept[$rounit]);
		$cellpadding=0;
		$cellspacing=1;
		$sizefont='14';
		// print_r($arrkodept);exit();
	
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
			$tab.="<tr>";
				$tab.="<td style='width:50px;' align=center><img src=".$arrkodept['logo']." style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'></td>"; 
				$tab.="<td style='width:350px;text-align:center;font-size:".($sizefont+14)."px'>".$arrkodept['nama']."</td>"; 
				$tab.="<td style='width:50px;'>&nbsp;</td>";
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$sizefont='12';
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Pontianak, ".tglnmbln($tanggal,'i','l')."</td>"; 
			$tab.="</tr>";		
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Ref No : ".$notransaksi."</td>"; 
			$tab.="</tr>";		
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Kepada Yth :</td>"; 
			$tab.="</tr>";		
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$namasupplier."</td>"; 
			$tab.="</tr>";		
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$alamatsupplier."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Atm : ".$namapemiliksupplier."</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Perihal : Claim Atas Kekurangan Penerimaan ".$arrinisial[$kodebarang]." ".$arrkodept['nama']."</td>"; 
			$tab.="</tr>";		
		$tab.="</table>";
		
		$tab.="<br>";
		
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Sesuai Surat Perintah kerja No. ".$nospk." khusus ketentuan angkutan ".$arrinisial[$kodebarang]." via ".$texttransport." bahwa toleransi susut /  penerimaan maksimum ".$texttoleransi." Pabrik ".$arrkodept['nama']." ke ".$texttimbangansounding." ".$namafranco[$pelabuhantujuan].".</td>"; 
			$tab.="</tr>";		
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Berdasarkan Data Hasil Penerimaan ".$texttransport." ".$arrinisial[$kodebarang]." di ".$namafranco[$pelabuhantujuan].", ada beberapa ".$texttransportdua." yang mengalami kekurangan muatan ".$texttransportdua." ".$arrinisial[$kodebarang]." yang diangkut sbb :</td>"; 
			$tab.="</tr>";		
		$tab.="</table>";
		
		$tab.="<br>";
		$no=0;
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$str="select * from ".$dbname.".pmn_batransport where notransaksi='".$param['notransaksi']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				if($bar['kgclaim']<0){
					$no++;
					if($tipe=='sip'){
						$tab.="<tr>";
							$tab.="<td style='text-align:left' valign=top>".$no.".</td>"; 
							$tab.="<td style='text-align:left;'>Quantity PMKS sebanyak ".hidezerodecimal($bar['kgkirim'])." Kg, Quantity di ".$namafranco[$pelabuhantujuan]." sebanyak ".hidezerodecimal($bar['kgterima'])." Kg, Kekurangan ".abs($bar['kgselisih'])." Kg -  ".abs($bar['kgtoleransi'])." Kg = <b>".abs($bar['kgclaim'])." Kg</b></td>"; 
							$tab.="<td style='text-align:left;'></td>"; 
						$tab.="</tr>";	
					}else{
						#= query ambil tanggal terima
						$strterima="select * from ".$dbname.".pabrik_timbangan_vw where norefrensi='".$bar['notiket']."'";
						$resterima=fetchdata($strterima);
						foreach($resterima as $barterima){
							$tanggalterima=$barterima['tanggal'];
							$notiketterima=$bar['notiket'];
							$nokendaraanterima=$bar['nokendaraan'];
							$beratbersihterima=$bar['beratbersih'];
							if($noclaim==$no){
								$texttanggalterima.="tanggal  ".tglnmbln($tanggalterima,'i','l')." ";
							}else{
								$texttanggalterima.="tanggal  ".tglnmbln($tanggalterima,'i','l').", ";
							}
						}
						$tab.="<tr>";
							$tab.="<td style='text-align:left' valign=top>".$no.".</td>"; 
							$tab.="<td style='text-align:left;'>Tgl. ".tglnmbln($tanggalterima,'i','l')." ".$nokendaraanterima." No. Tiket ".$notiketterima." Quantity PMKS sebanyak ".hidezerodecimal($bar['kgkirim'])." Kg, Quantity di ".$namafranco[$pelabuhantujuan]." sebanyak ".hidezerodecimal($bar['kgterima'])." Kg, Kekurangan ".abs($bar['kgselisih'])." Kg -  ".abs($bar['kgtoleransi'])." Kg = <b>".abs($bar['kgclaim'])." Kg</b></td>"; 
							$tab.="<td style='text-align:left;'></td>"; 
						$tab.="</tr>";	
					}
					
				}				
			}
		$tab.="</table>";
		
		$tab.="<br>";
		
		if($nokontrak!=''){
			$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
				$tab.="<tr>";
						$tab.="<td style='text-align:left;'>".$arrinisial[$kodebarang]." tersebut diangkut dari PMKS ".$arrkodept['nama']." ke ".$namafranco[$pelabuhantujuan]." untuk memenuhi kontrak No. : ".$nokontrak."</td>"; 
				$tab.="</tr>";		
			$tab.="</table>";
		}
		
		$tab.="<br>";	
	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;' colspan=3>Atas kekurangan penerimaan ".$arrinisial[$kodebarang]." tersebut akan kami claim ke transporter dengan perhitungan</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Total ".$arrinisial[$kodebarang]." yang susut ".$texttanggalterima."</td>"; 
				$tab.="<td style='text-align:left;width:10px' >:</td>"; 
				$tab.="<td style='text-align:left;'>".hidezerodecimal($kgselisih)." Kg</td>"; 
			$tab.="</tr>";			
			$tab.="<tr>";
				if($tipe=='sip'){
					$tab.="<td style='text-align:left;'>Toleransi ".hidezerodecimal($texttoleransipersen)." %</td>";
				}else{
					$tab.="<td style='text-align:left;'>Toleransi ".hidezerodecimal($kgtoleransi)." Kg ( ".$noclaim." Unit)</td>"; 
				}
				$tab.="<td style='text-align:left;width:10px' >:</td>"; 
				$tab.="<td style='text-align:left;'>".hidezerodecimal(($kgtoleransi)*($noclaim))." Kg</td>"; 
			$tab.="</tr>";			
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Total Claim</td>"; 
				$tab.="<td style='text-align:left;width:10px' >:</td>"; 
				$tab.="<td style='text-align:left;'>".hidezerodecimal($kgclaim)." Kg</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Harga ".$arrinisial[$kodebarang]."</td>"; 
				$tab.="<td style='text-align:left;width:10px' >:</td>"; 
				$tab.="<td style='text-align:left;'>Rp. ".hidezerodecimal($rpkgclaim)." / Kg</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Nilai Claim</td>"; 
				$tab.="<td style='text-align:left;width:10px' >:</td>"; 
				$tab.="<td style='text-align:left;'>Rp. ".hidezerodecimal($rpclaim)."</td>"; 
			$tab.="</tr>";				
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Nilai Claim sebesar <u><b>Rp. ".hidezerodecimal($rpclaim)."</b></u> tersebut akan langsung kami potong dari tagihan ".$namasupplier.".</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Demikian disampaikan.</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Terima Kasih,</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		$tab.="<br>";
		
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			for($i=0;$i<=2;$i++){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="</tr>";	
			}
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>______________________</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Manager ".$namafranco[$pelabuhantujuan]."</td>"; 
			$tab.="</tr>";			
		$tab.="</table>";
		
		$tab.="<br>";
		// echo $tab;exit();
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("Print_BA_".$notransaksi,array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}	
	break;
    default:
	break;
}
?>

<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param     = $_POST;
$kodeorg   = $param['kodeorg'];
$tahunbulan= implode("",explode('-',$param['periode']));
$proses    = $param['proses'];
$induk     = makeOption($dbname,'organisasi','kodeorganisasi,induk');

// print_r($param);

// exit("error");
try {
	$owlPDO->beginTransaction();

	#1. ambil periode akuntansi
	$tgmulai='';$tgsampai='';$periode='';
	$str = "select * from ".$dbname.".setup_periodeakuntansi where kodeorg ='".$kodeorg."' and tutupbuku=0 and periode='".$param['periode']."' ";
	$res = fetchdata($str);
	foreach($res as $bar){
		$tgsampai= $bar['tanggalsampai'];
		$tgmulai = $bar['tanggalmulai'];
		$periode = $bar['periode'];
		$tanggal = $bar['tanggalsampai'];
	}

	$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
		 <thead>
		   <tr class=rowheader style='text-align:center';>
		   <th>No</th>
		   <th>Batch</th>
		   <th>Kodeorg<br>(Dari)</th>
		   <th>Kodeorg<br>(Tujuan)</th>
		   <th>Tanggal</th>
		   <th>Jumlah</th>
		   </tr>
		 </thead>
		 <tbody>";




	$str="select * from ".$dbname.".bibitan_mutasi where kodeorg like '".$param['kodeorg']."%' and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and kodetransaksi='TPB'";
	$res=fetchdata($str);
	foreach($res as $bar){
		$no++;
		if($bar['post']=='0'){
			exit("Error : Ada transaksi yang belum diposting.");
		}else{
			$noblok[$bar['kodeorg']]=$bar['kodeorg'];
			$nobloktujuan[$bar['kodeorg']][$bar['tujuan']]=$bar['tujuan'];
			$nobatch[$bar['kodeorg']][$bar['tujuan']]=$bar['batch'];
			
			$tab.="<tr class=rowcontent id='row".$no."'>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$bar['batch']."</td>";
			$tab.="<td>".$bar['kodeorg']."</td>";
			$tab.="<td>".$bar['tujuan']."</td>";
			$tab.="<td>".$bar['tanggal']."</td>";
			$tab.="<td align=right>".abs($bar['jumlah'])."</td>";
			$tab.="</tr>";
			$total+=abs($bar['jumlah']);
			$subttl[$bar['kodeorg']][$bar['tujuan']]+=abs($bar['jumlah']);
		}
	}
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center colspan=5>TOTAL</td>";
	$tab.="<td align=right>".abs($total)."</td>";
	$tab.="</tr>";
	$tab.="</table><br>";
	
	
	// Get Parameter Jurnal
	$kodeTrans       = 'TPB';
	$kodeJurnal      = $kodeTrans;

	$sql = "SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM ".$dbname.".keu_5parameterjurnal WHERE kodeorg='".$induk[$induk[$param['kodeorg']]]."' and jurnalid='".$kodeJurnal."' and kodeaplikasi ='".$kodeTrans."'";
	$paramJurnal = fetchData($sql)[0];
	if (empty($paramJurnal)){
		throw new PDOException("Parameter Jurnal untuk " . $kodeJurnal . " belum ada");
	}

	$strAkun = "'" . $paramJurnal['noakundebet'] . "','".$paramJurnal['noakunkredit'] . "','".$paramJurnal['sampaidebet']."','".$paramJurnal['sampaikredit'] . "'";

	// Get Jurnal
	$sumX=array();
	$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname .".keu_jurnaldt WHERE LEFT(tanggal,7)<='".$param['periode']."' and noakun >= '".$paramJurnal['sampaidebet']."' and noakun <='".$paramJurnal['sampaikredit']."' and kodeorg='".$param['kodeorg']."' and kodeblok in ('".implode("','",$noblok)."') group by kodeblok";
	$resJurnal = fetchData($qJurnal);
	foreach($resJurnal as $bar){		
		$sumX[$bar['kodeblok']] += $bar['nilai'];
	}
	
	// if($_SESSION['standard']['userid']=='0000000007'){
		// echo $qJurnal;
	// }
	
	// Get Jurnal sudah dialokasi
	$sumH=array();
	$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname .".keu_jurnaldt WHERE LEFT(tanggal,7)<'".$param['periode']."' and noakun = '".$paramJurnal['noakunkredit']."' and kodeorg='".$param['kodeorg']."' and kodeblok in ('".implode("','",$noblok)."') group by kodeblok";
	$resJurnal = fetchData($qJurnal);
	foreach($resJurnal as $bar){		
		$sumH[$bar['kodeblok']] += abs($bar['nilai']);
	}

	// if($_SESSION['standard']['userid']=='0000000007'){
		// echo $qJurnal;
	// }

	// Get Saldo Bibit
	$sumM=array();
	$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname .".bibitan_mutasi WHERE kodeorg in ('".implode("','",$noblok)."') and LEFT(tanggal,7)<='".$param['periode']."' group by kodeorg";
	$resBibit = fetchData($qBibit);
	foreach($resBibit as $bar){		
		$sumM[$bar['kodeorg']] += $bar['nilai'];
	}
	
	// Get TPB bulan ini
	$sumN=array();
	$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname .".bibitan_mutasi WHERE kodeorg in ('".implode("','",$noblok)."') and kodetransaksi='TPB' and LEFT(tanggal,7)='".$param['periode']."' group by kodeorg";
	$resBibit = fetchData($qBibit);
	foreach($resBibit as $bar){		
		$sumN[$bar['kodeorg']] += abs($bar['nilai']);
	}
	
	
	
	// Harga Rata2
	$sumZ = array();
	$sumY=array();
	foreach($noblok as $blok){
		$sumY[$blok] = $sumM[$blok] + $sumN[$blok];
		$sumZ[$blok] = ($sumX[$blok]-$sumH[$blok]) / $sumY[$blok];
	}
	
	// echo"<pre>";
	// print_r($sumY);
	
	$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=0>
		 <thead>
		   <tr class=rowheader style='text-align:center';>
		   <th>No</th>
		   <th>Kodeorg<br>(Dari)</th>
		   <th>Kodeorg<br>(Tujuan)</th>
		   <th colspan=2>Debet</th>
		   <th colspan=2>Kredit</th>
		   <th>Jumlah<br>Biaya</th>
		   <th >Jumlah<br>Bibit</th>
		   <th>Jumlah<br>Transplanting</th>
		   <th>Rp/Sat</th>
		   <th>Rupiah</th>
		   </tr>
		 </thead>
		 <tbody>";
	
	$no=0;
	foreach($nobloktujuan as $blok => $v1){
		foreach($v1 as $tujuan){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td id='kodeorg".$no."'>".$blok."</td>";
			$tab.="<td id='tujuan".$no."'>".$tujuan."</td>";
			$tab.="<td id='debet".$no."'>".$paramJurnal['noakundebet']."</td>";
			$tab.="<td>".getNamaAkun($paramJurnal['noakundebet'])."</td>";
			$tab.="<td id='kredit".$no."'>".$paramJurnal['noakunkredit']."</td>";
			$tab.="<td>".getNamaAkun($paramJurnal['noakunkredit'])."</td>";
			$tab.="<td align=right>".number_format($sumX[$blok]-$sumH[$blok])."</td>";
			$tab.="<td align=right>".number_format($sumY[$blok])."</td>";
			$tab.="<td align=right id='jumlah".$no."'>".abs($subttl[$blok][$tujuan])."</td>";
			$tab.="<td align=right>".number_format($sumZ[$blok],2)."</td>";
			$tab.="<td align=right id='rupiah".$no."'>".number_format(abs($subttl[$blok][$tujuan])*$sumZ[$blok],0)."</td>";
			$tab.="</tr>";
			
			$biaya+=round(abs($subttl[$blok][$tujuan])*$sumZ[$blok]);
			$biayadt[$blok][$tujuan]+=round(abs($subttl[$blok][$tujuan])*$sumZ[$blok]);
		}
	}
	
	$tab.="</table>";
	$tab.="<br><button class=mybutton onclick=savemutasibibit()>Proses</button>";

	switch ($param['proses']) {
		default:
			echo $tab;
		break;
		case'prosesjurnal':
			include_once('lib/zJournal.php');
			$cJournal = new zJournal();
			
			
			// Get Kelompok Jurnal				
			$sql = "SELECT nokounter FROM ".$dbname.".keu_5kelompokjurnal WHERE kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' and kodekelompok ='".$kodeJurnal."'";
			$resJurnal = fetchData($sql);
			if(empty($resJurnal)){
				throw new PDOException("Counter Jurnal belum ada.");
			}
			$counter = $resJurnal[0]['nokounter'];
			$nojurnal = $cJournal->genNoJournal($tanggal, substr($param['kodeorg'], 0, 4), $kodeJurnal, $counter);
			
			$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
			
			$query = "delete from " . $dbname . ".keu_jurnalht where nojurnal like '" .substr($nojurnal,0,18). "%' and tanggal='".$tanggal."' and nojurnal like '%/".$param['kodeorg']."/%' and nojurnal like '%/".$kodeJurnal."/%' and kodejurnal='".$kodeJurnal."'";
			$owlPDO->exec($query); 
			
			// Jurnal Header
			$dataRes['header'] = array(
				'nojurnal'     => $nojurnal,
				'kodejurnal'   => $kodeJurnal,
				'tanggal'      => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting'      => '0',
				'totaldebet'   => $biaya,
				'totalkredit'  => $biaya,
				'amountkoreksi'=> '0',
				'noreferensi'  => $batchVar,
				'autojurnal'   => '1',
				'matauang'     => 'IDR',
				'kurs'         => '1',
				'revisi'       => '0'
			);
			
			
			$no=0;
			foreach($nobloktujuan as $blok => $v1){
				foreach($v1 as $tujuan){					
					#debet
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $paramJurnal['noakundebet'],
						'keterangan'  => 'Transplanting '.abs($subttl[$blok][$tujuan]).' bibit dari ' . $blok . " ke " . $tujuan,
						'jumlah'      => $biayadt[$blok][$tujuan],
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($blok, 0, 4),
						'kodekegiatan'=> '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $nobatch[$blok][$tujuan],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $nobatch[$blok][$tujuan],
						'kodeblok'    => $tujuan,
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
					
					#kredit
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $paramJurnal['noakunkredit'],
						'keterangan'  => 'Transplanting '.abs($subttl[$blok][$tujuan]).' bibit dari ' . $blok . " ke " . $tujuan,
						'jumlah'      => ($biayadt[$blok][$tujuan]) * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($blok, 0, 4),
						'kodekegiatan'=> '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $nobatch[$blok][$tujuan],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $nobatch[$blok][$tujuan],
						'kodeblok'    => $blok,
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
				}
			}
			
			$qHeader = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			$owlPDO->exec($qHeader); 
			
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				$owlPDO->exec($queryD);
			}
			
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1), "kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."'");
			$owlPDO->exec($queryKonter);
			
			$str="select * from ".$dbname.".bibitan_mutasi where kodeorg like '".$param['kodeorg']."%' and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and kodetransaksi='TPB'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$queryU = "update " . $dbname . ".bibitan_mutasi set keterangan='".$bar['keterangan'].", nojurnal : ".$nojurnal."' where kodetransaksi='" . $bar['kodetransaksi'] . "' and batch='" . $bar['batch'] . "' and kodeorg='" . $bar['kodeorg'] . "' and tujuan='" . $bar['tujuan'] . "' and post='1' and tanggal='".$bar['tanggal']."'";
				$owlPDO->exec($queryU);
			}
			
			echo $nojurnal;
		break;
	}
	#execute
	$owlPDO->commit();
} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
	
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
$proses    = checkPostGet('proses', '');

// $param['periode']='2022-09';
// print_r($param);
// exit("error");

	switch ($proses) {
		default:
			#1. ambil periode akuntansi
			$tgmulai='';$tgsampai='';$periode='';
			$str = "select * from ".$dbname.".setup_periodeakuntansi where kodeorg ='".$kodeorg."' and periode='".$param['periode']."' ";
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
				   <th rowspan=2>No</th>
				   <th rowspan=2>Batch</th>
				   <th rowspan=2>Kodeorg<br>(Dari)</th>
				   <th colspan=2>Tujuan</th>
				   <th rowspan=2>Jenis Tanam</th>
				   <th rowspan=2>Tanggal</th>
				   <th rowspan=2>Jumlah</th>
				   </tr>
				   <tr class=rowheader style='text-align:center';>
				   <th>KodeOrg</th>
				   <th>NamaOrg</th>
				   
				   </tr>
				 </thead>
				 <tbody>";

			$arrjenis=['TB'=>'Tanam Baru','SISIP'=>'Sisip'];


			$str="select * from ".$dbname.".bibitan_mutasi where kodeorg like '".$param['kodeorg']."%' and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and kodetransaksi='PNB'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$no++;
				if($bar['post']=='0'){
					exit("Error : Ada transaksi yang belum diposting.");
				}else{
					$noblok[$bar['kodeorg']]=$bar['kodeorg'];
					$nobloktujuan[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']]=$bar['jenistanam'];
					$nobatch[$bar['kodeorg']][$bar['afdeling']]=$bar['batch'];
					$jenistanam[$bar['jenistanam']]=$bar['jenistanam'];
					
					
					$tab.="<tr class=rowcontent id='row".$no."'>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$bar['batch']."</td>";
					$tab.="<td>".$bar['kodeorg']."</td>";
					$tab.="<td>".$bar['afdeling']."</td>";
					$tab.="<td>".getNamaOrg($bar['afdeling'])."</td>";
					$tab.="<td>".$arrjenis[$bar['jenistanam']]."</td>";
					$tab.="<td>".$bar['tanggal']."</td>";
					$tab.="<td align=right>".abs($bar['jumlah'])."</td>";
					$tab.="</tr>";
					$total+=abs($bar['jumlah']);
					$subttl[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']]+=abs($bar['jumlah']);
				}
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=7>TOTAL</td>";
			$tab.="<td align=right>".abs($total)."</td>";
			$tab.="</tr>";
			$tab.="</table><br>";
			
			$sumX=array();
			$sumH=array();
			$sumM=array();
			$sumN=array();
			$sumZ=array();
			$sumY=array();
			
			// Get Saldo Bibit
			$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname .".bibitan_mutasi WHERE kodeorg in ('".implode("','",$noblok)."') and LEFT(tanggal,7)<='".$param['periode']."' group by kodeorg";
			$resBibit = fetchData($qBibit);
			foreach($resBibit as $bar){		
				$sumM[$bar['kodeorg']] += $bar['nilai'];
			}
			
			// Get TPB bulan ini
			$qBibit = "SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname .".bibitan_mutasi WHERE kodeorg in ('".implode("','",$noblok)."') and kodetransaksi='PNB' and LEFT(tanggal,7)='".$param['periode']."' group by kodeorg";
			$resBibit = fetchData($qBibit);
			foreach($resBibit as $bar){		
				$sumN[$bar['kodeorg']] += abs($bar['nilai']);
			}
			
			foreach($jenistanam as $jenistnm){
				// Get Parameter Jurnal
				$kodeTrans       = $jenistnm;
				$kodeJurnal      = 'PNB';

				$sql = "SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM ".$dbname.".keu_5parameterjurnal WHERE kodeorg='".$induk[$induk[$param['kodeorg']]]."' and jurnalid='".$kodeJurnal."' and kodeaplikasi ='".$kodeTrans."'";
				$paramJurnalAkun[$jenistnm] = fetchData($sql)[0];
				$paramJurnal = fetchData($sql)[0];
				if (empty($paramJurnal)){
					exit("Error : Parameter Jurnal untuk " . $kodeJurnal . " belum ada");
				}
				
				
				$strAkun = "'" . $paramJurnal['noakundebet'] . "','".$paramJurnal['noakunkredit'] . "','".$paramJurnal['sampaidebet']."','".$paramJurnal['sampaikredit'] . "'";

				// Get Jurnal
				$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname .".keu_jurnaldt WHERE LEFT(tanggal,7)<='".$param['periode']."' and noakun >= '".$paramJurnal['sampaidebet']."' and noakun <='".$paramJurnal['sampaikredit']."' and kodeorg='".$param['kodeorg']."' and kodeblok in ('".implode("','",$noblok)."') group by kodeblok";
				$resJurnal = fetchData($qJurnal);
				foreach($resJurnal as $bar){		
					$sumX[$bar['kodeblok']][$jenistnm] += $bar['nilai'];
				}
				
				
				// echo $qJurnal.";<br>";
				// if($_SESSION['standard']['userid']=='0000000007'){
					// echo $qJurnal;
				// }
				
				// Get Jurnal sudah dialokasi
				$qJurnal = "SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname .".keu_jurnaldt WHERE LEFT(tanggal,7)<'".$param['periode']."' and noakun = '".$paramJurnal['noakunkredit']."' and kodeorg='".$param['kodeorg']."' and kodeblok in ('".implode("','",$noblok)."') group by kodeblok";
				$resJurnal = fetchData($qJurnal);
				foreach($resJurnal as $bar){		
					$sumH[$bar['kodeblok']][$jenistnm] += abs($bar['nilai']);
				}

				
				// if($_SESSION['standard']['userid']=='0000000007'){
					// echo $qJurnal;
				// }

				
				// Harga Rata2
				
				foreach($noblok as $blok){
					$sumY[$blok] = $sumM[$blok] + $sumN[$blok];
					foreach($jenistanam as $jenistnm){				
						$sumZ[$blok][$jenistnm] = ($sumX[$blok][$jenistnm]-$sumH[$blok][$jenistnm]) / $sumY[$blok];
					}
				}
			}
			
			// echo"<pre>";
			// print_r($sumY);
			
			$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=0>
				 <thead>
				   <tr class=rowheader style='text-align:center';>
				   <th>No</th>
				   <th>Kodeorg<br>(Dari)</th>
				   <th>Kodeorg<br>(Tujuan)</th>
				   <th>Jenis Tanam</th>
				   <th colspan=2>Kegiatan</th>
				   <th colspan=2>Debet</th>
				   <th colspan=2>Kredit</th>
				   <th>Jumlah<br>Biaya</th>
				   <th >Jumlah<br>Bibit</th>
				   <th>Jumlah<br>Tanam</th>
				   <th>Rp/Sat</th>
				   <th>Rupiah</th>
				   <th>Jurnal</th>
				   </tr>
				 </thead>
				 <tbody>";
			
			$no=0;
			$kelompokakun = array('126'=>'TBM','621'=>'TM');
			foreach($kelompokakun as $kodekel => $namakel){				
				$optakun[$namakel]="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			}
			$str = "select * from ".$dbname.".setup_kegiatan where namakegiatan not like '%NON AKTIF%' and (noakun like '621%' or noakun like '126%') order by kodekegiatan";
			$res = fetchdata($str);
			foreach($res as $bar){
				$d=substr($bar['kodekegiatan'],0,3);
				foreach($kelompokakun as $kodekel => $namakel){
					if($kodekel==$d){						
						if($d!=$n){			
							$optakun[$namakel].="<optgroup label='".getNamaAkun($d)."'>";
						}
						
						$e=substr($bar['kodekegiatan'],0,7);
						if($e!=$o){			
							$optakun[$namakel].="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
						}
						$optakun[$namakel].="<option value='".$bar['kodekegiatan']."'>".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
						$o=$e;
						if($e!=$o){			
							$optakun[$namakel].="</optgroup>";
						}
						$n=$d;
						if($d!=$n){			
							$optakun[$namakel].="</optgroup>";
						}
					}
				}
			}
			
			foreach($nobloktujuan as $blok => $v1){
				foreach($v1 as $tujuan => $v2){
					foreach($v2 as $jenis){
						$no++;
						$tab.="<tr class=rowcontent id=baris".$no.">";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td id='kodeorg".$no."'>".$blok."</td>";
						$tab.="<td id='tujuan".$no."'>".$tujuan."</td>";
						$tab.="<td id='jenis".$no."'>".$jenis."</td>";
						// if(substr($paramJurnalAkun[$jenis]['noakundebet'],0,7)!=''){
							// $tab.="<td><input hidden id='kegiatan".$no."' value=".$paramJurnalAkun[$jenis]['noakundebet'].">".$paramJurnalAkun[$jenis]['noakundebet']."</td>";
							// $tab.="<td>".getNamaKeg($paramJurnalAkun[$jenis]['noakundebet'])."</td>";
							// $tab.="<td><input hidden id='debet".$no."' value=".substr($paramJurnalAkun[$jenis]['noakundebet'],0,7).">".substr($paramJurnalAkun[$jenis]['noakundebet'],0,7)."</td>";
							// $tab.="<td>".getNamaAkun(substr($paramJurnalAkun[$jenis]['noakundebet'],0,7))."</td>";
						// }else{
							$stsblok=getBlok($tujuan,'statusblok');
							if($stsblok=='TB'){
								$stsblok='TBM';
							}
							$tab.="<td colspan=2><select style=width:250px onchange=getnoakun(".$no."); id='kegiatan".$no."'>".$optakun[getBlok($tujuan,'statusblok')]."</select></td>";
							$tab.="<td colspan=2 id=tempdebet".$no."></td>";
							$tab.="<input hidden id='debet".$no."'>";
						// }
						$tab.="<td id='kredit".$no."'>".$paramJurnalAkun[$jenis]['noakunkredit']."</td>";
						$tab.="<td>".getNamaAkun($paramJurnalAkun[$jenis]['noakunkredit'])."</td>";
						$tab.="<td align=right>".number_format($sumX[$blok][$jenis]-$sumH[$blok][$jenis])."</td>";
						$tab.="<td align=right>".number_format($sumY[$blok])."</td>";
						$tab.="<td align=right id='jumlah".$no."'>".abs($subttl[$blok][$tujuan][$jenis])."</td>";
						$tab.="<td align=right>".number_format($sumZ[$blok][$jenis],2)."</td>";
						$tab.="<td align=right id='rupiah".$no."'>".number_format(abs($subttl[$blok][$tujuan][$jenis])*$sumZ[$blok][$jenis],0)."</td>";
						$tab.="<td id='jurnal".$no."'></td>";
						$tab.="</tr>";
						
						$biaya+=round(abs($subttl[$blok][$tujuan][$jenis])*$sumZ[$blok][$jenis]);
						$biayadt[$blok][$tujuan]+=round(abs($subttl[$blok][$tujuan][$jenis])*$sumZ[$blok][$jenis]);
					}	
				}
			}
			
			$tab.="</table>";
			$tab.="<br><button class=mybutton onclick=savemutasibibittanam('1','".$no."')>Proses</button>";

			echo $tab;
		break;
		case'getnoakun':
			$str = "select * from ".$dbname.".setup_kegiatan where kodekegiatan='".$param['kegiatan']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$optakun=$bar['noakun'];
				$namaakun=$bar['noakun']." - ".getNamaAkun($bar['noakun']);
			}
			
			echo $optakun."####".$namaakun;
		break;
		case'prosesjurnal':
			try {
				$owlPDO->beginTransaction();
				$biaya = str_replace(",","",$param['rupiah']);
				$param['jumlah'] = str_replace(",","",$param['jumlah']);
				$kodeJurnal  = 'PNB';
				
				$noreff    = "ALK_".$kodeJurnal."_".$param['kodeorg']."_".str_replace("-","",$param['periode']);
				$bibitan   = substr(getNamaOrg($param['blokbbt'],'induk'),0,4);
				$kebun     = substr(getNamaOrg($param['bloktnm'],'induk'),0,4);
				$ptkebun   = getNamaOrg($kebun,'induk');
				$ptbibitan = getNamaOrg($bibitan,'induk');
				$segment   = '0000000001';
				$kodeorg = $kebun;
				$param['kodeorg'] = $kebun;
				

				$kodeTrans   = $param['jenis'];
				$tgmulai='';$tgsampai='';$periode='';
				$str = "select * from ".$dbname.".setup_periodeakuntansi where kodeorg ='".$kodeorg."' and periode='".$param['periode']."' ";
				$res = fetchdata($str);
				foreach($res as $bar){
					$tgsampai= $bar['tanggalsampai'];
					$tgmulai = $bar['tanggalmulai'];
					$periode = $bar['periode'];
					$tanggal = $bar['tanggalsampai'];
				}
				
				include_once('lib/zJournal.php');
				$cJournal = new zJournal();
				
				// Get Kelompok Jurnal				
				$sql = "SELECT nokounter FROM ".$dbname.".keu_5kelompokjurnal WHERE kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' and kodekelompok ='".$kodeJurnal."'";
				$resJurnal = fetchData($sql);
				if(empty($resJurnal)){
					throw new PDOException("Counter Jurnal ".$kodeJurnal." belum ada.");
				}
				$counter = $resJurnal[0]['nokounter'];
				$nojurnal = $cJournal->genNoJournal($tanggal, substr($param['kodeorg'], 0, 4), $kodeJurnal, $counter);
				
				$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
				
				
				$sql = "SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM ".$dbname.".keu_5parameterjurnal WHERE kodeorg='".$induk[$induk[$param['kodeorg']]]."' and jurnalid='".$kodeJurnal."' and kodeaplikasi ='".$kodeTrans."'";
				$paramJurnalAkun[$jenistnm] = fetchData($sql)[0];
				$paramJurnal = fetchData($sql)[0];
				
				if($param['bloktnm']==''){
					throw new PDOException("Blok tujuan tidak boleh kosong.");
				}
				
				if($kebun==''){
					throw new PDOException("Kode organisasi tujuan tidak boleh kosong.");
				}
				if($param['kredit']==''){
					throw new PDOException("Akun kredit tidak boleh kosong.");
				}
				if($param['debet']==''){
					throw new PDOException("Akun debet tidak boleh kosong.");
				}
				
				$kelakun = substr($param['debet'],0,3);
				$status  = getBlok($param['bloktnm'],'statusblok');
				if($status=='TB' and $kelakun!='126'){
					throw new PDOException("Status Blok TB, noakun harus 126xxxx.");
				}
				if($status=='TBM' and $kelakun!='126'){
					throw new PDOException("Status Blok TBM, noakun harus 126xxxx.");
				}
				if($status=='TM' and $kelakun!='621'){
					throw new PDOException("Status Blok TM, noakun harus 621xxxx.");
				}
				
				if($param['currRow']=='1'){					
					$query = "delete from " . $dbname . ".keu_jurnalht where noreferensi ='".$noreff."' and tanggal='".$tanggal."'";
					$owlPDO->exec($query); 	
				}
				
				
				
				if($kebun!=$bibitan){
					if($ptkebun!=$ptbibitan){
						$jenis="inter";
					}else if($ptkebun==$ptbibitan){
						if($kebun!=$bibitan){
							$jenis="intra";    
						}
					}
					
					$aknPt  = makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',"kodeorg='".$kebun."' and jenis='".$jenis."'");
					$aknHtg = makeOption($dbname,'keu_5caco','kodeorg,akunhutang',"kodeorg='".$bibitan."' and jenis='".$jenis."'");
					
					$dataRes['header'] = array(
						'nojurnal'     => $nojurnal,
						'kodejurnal'   => $kodeJurnal,
						'tanggal'      => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting'      => '0',
						'totaldebet'   => $biaya,
						'totalkredit'  => $biaya,
						'amountkoreksi'=> '0',
						'noreferensi'  => $noreff,
						'autojurnal'   => '1',
						'matauang'     => 'IDR',
						'kurs'         => '1',
						'revisi'       => '0'
					);
					$noUrut++;
					#debet disisi penerima bibit
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $noUrut,
						'noakun'      => $param['debet'],
						'keterangan'  => 'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      => $biaya,
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $kebun,
						'kodekegiatan'=> $param['kegiatan'],
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $noreff,
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $param['blokbbt'],
						'kodeblok'    => $param['bloktnm'],
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
						
					$noUrut++;
					# Detail (Kredit)  disisi penerima bibit
					$dataRes['detail'][] = array(
						'nojurnal'    =>$nojurnal,
						'tanggal'     =>$tanggal,
						'nourut'      =>$noUrut,
						'noakun'      =>$aknHtg[$bibitan],
						'keterangan'  =>'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      =>$biaya*(-1),
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$kebun,
						'kodekegiatan'=>'',
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$noreff,
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>'',
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					
				
					# Get Journal Counter
					$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$ptbibitan."' and kodekelompok='M' and kodeunit='".$bibitan."' and periode='".$param['periode']."'");
					$tmpKonter2 = fetchData($queryJ);
					if(count($tmpKonter2)==0){throw new PDOException("Kelompok Jurnal M untuk kodeorg ".$ptbibitan.", kodeunit : ".$bibitan.", periode : ".$param['periode']." silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");}
					@$konter    = addZero($tmpKonter2[0]['nokounter']+1,3);
					@$counterDt = intval($tmpKonter2[0]['nokounter'])+1;
					
					# Transform No Jurnal dari No Transaksi
					$tmpNoJurnal = str_replace('-','',$tanggal);
					$nojurnal2   = $tmpNoJurnal."/".$bibitan."/M/".$konter;
					
					#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
					$str = "SELECT * FROM ".$dbname.".`keu_jurnalht` WHERE nojurnal='".$nojurnal2."'";
					$res = fetchdata($str);
					if(count($res)>0){
						$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='M' and nojurnal like '%".$bibitan."%' and tanggal like '".$param['periode']."%'";
						$res = fetchdata($str);
						$konter = addZero($res[0]['nomor']+1,3);
						$nojurnal2 = $tmpNoJurnal."/".$bibitan."/M/".$konter;
					}
					
					
					
					#1. Data Header RK
					$dataResRk['header'] = array(
						'nojurnal'     =>$nojurnal2,
						'kodejurnal'   =>'M',
						'tanggal'      =>$tanggal,
						'tanggalentry' =>date('Ymd'),
						'posting'      =>'1',
						'totaldebet'   =>'0',
						'totalkredit'  =>'0',
						'amountkoreksi'=>'0',
						'noreferensi'  =>$noreff,
						'autojurnal'   =>'1',
						'matauang'     =>'IDR',
						'kurs'         =>'1',
						'revisi'       =>'0'
					);
					
					$noUrut2++;
					$dataResRk['detail'][] = array(
						'nojurnal'    =>$nojurnal2,
						'tanggal'     =>$tanggal,
						'nourut'      =>$noUrut2,
						'noakun'      =>$aknPt[$kebun],
						'keterangan'  =>'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      =>$biaya,
						'matauang'    =>'IDR',
						'kurs'        =>'1',
						'kodeorg'     =>$bibitan,
						'kodekegiatan'=>'',
						'kodeasset'   =>'',
						'kodebarang'  =>'',
						'nik'         =>'',
						'kodecustomer'=>'',
						'kodesupplier'=>'',
						'noreferensi' =>$noreff,
						'noaruskas'   =>'',
						'kodevhc'     =>'',
						'nodok'       =>'',
						'kodeblok'    =>$param['bloktnm'],
						'revisi'      =>'0',
						'kodesegment' => $segment
					);
					
					$noUrut2++;
					$dataResRk['detail'][] = array(
						'nojurnal'    => $nojurnal2,
						'tanggal'     => $tanggal,
						'nourut'      => $noUrut2,
						'noakun'      => $param['kredit'],
						'keterangan'  => 'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      => ($biaya) * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $bibitan,
						'kodekegiatan'=> '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $noreff,
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $param['bloktnm'],
						'kodeblok'    => $param['blokbbt'],
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
					
					$qHeader = insertQuery($dbname,'keu_jurnalht',$dataResRk['header']);
					$owlPDO->exec($qHeader); 
					
					foreach($dataResRk['detail'] as $key=>$dataDet) {
						$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
						$owlPDO->exec($queryD);
					}
				
					$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1), "kodeorg='".$ptbibitan."' and kodekelompok='M' and kodeunit='".$bibitan."' and periode='".$param['periode']."'");
					$owlPDO->exec($queryKonter);
				
				}else{
					
					#BUKAN RK
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
						'noreferensi'  => $noreff,
						'autojurnal'   => '1',
						'matauang'     => 'IDR',
						'kurs'         => '1',
						'revisi'       => '0'
					);
					
					
					$no=0;					
					#debet
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $param['debet'],
						'keterangan'  => 'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      => $biaya,
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $kebun,
						'kodekegiatan'=> $param['kegiatan'],
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $noreff,
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $param['blokbbt'],
						'kodeblok'    => $param['bloktnm'],
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);
					
					#kredit
					$no++;
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tanggal,
						'nourut'      => $no,
						'noakun'      => $param['kredit'],
						'keterangan'  => 'Alokasi '.abs($param['jumlah']).' bibit dari ' . $param['blokbbt'] . " ke " . $param['bloktnm'],
						'jumlah'      => ($biaya) * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $kebun,
						'kodekegiatan'=> '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer'=> '',
						'kodesupplier'=> '',
						'noreferensi' => $noreff,
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => $param['bloktnm'],
						'kodeblok'    => $param['blokbbt'],
						'revisi'      => '0',
						'kodesegment' => $defSegment
					);	
				} #tutup jika RK

				$qHeader = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				$owlPDO->exec($qHeader); 
				
				foreach($dataRes['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
				
				$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1), "kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."'");
				$owlPDO->exec($queryKonter);
				
				$str="select * from ".$dbname.".bibitan_mutasi where kodeorg like '".$param['kodeorg']."%' and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and kodetransaksi='PNB'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$queryU = "update " . $dbname . ".bibitan_mutasi set keterangan='".$bar['keterangan'].", nojurnal : ".$nojurnal."' where kodetransaksi='" . $bar['kodetransaksi'] . "' and batch='" . $bar['batch'] . "' and kodeorg='" . $bar['kodeorg'] . "' and tujuan='" . $bar['tujuan'] . "' and post='1' and tanggal='".$bar['tanggal']."'";
					//$owlPDO->exec($queryU);
				}
				if($nojurnal2!=''){
					echo $nojurnal2.", ".$nojurnal;
				}else{					
					echo $nojurnal;
				}
				$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
		break;
	}
	#execute
	
<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method= checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param= $_GET;}

$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmtt  = makeOption($dbname,'setup_blok','kodeorg,tahuntanam');

$path   = $_SERVER['HTTP_REFERER'];
$path   = explode('/',$path);
$rowfile= count($path)-1;
$file   = $path[$rowfile];
$file   = str_replace(".php","",$file);
$idmenu = makeOption($dbname,'menu','action,id');

$str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['method']."' order by id asc";
$res = fetchData($str);
$optlap="<option value=''>&nbsp;</option>";
foreach($res as $key => $bar){
	//$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	$judullap.=$bar['id']."$$$$";
	$datalap.=$bar['data']."$$$$";
	$listopt[$bar['id']]=$bar['label'];
}

$str = "select a.*, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['method']."' order by a.id asc";
$res = fetchdata($str);
foreach ($res as $bar){
	//$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
	
	$listopt[$bar['id']]=$bar['label'];
	$judullap.=$bar['id']."$$$$";
	$datalap.=$bar['data']."$$$$";
}

$str = "select distinct a.id, a.label from ".$dbname.".pivot_favorit a left join ".$dbname.".pivot_favoritdt b on a.id=b.id where 1=1 and ( a.karyawanid='".$_SESSION['standard']['userid']."' or b.karyawanid='".$_SESSION['standard']['userid']."') and a.idmenu='".$idmenu[$file]."' and a.jenis ='".$param['method']."' order by a.id asc";
$res = fetchdata($str);
foreach ($res as $bar){
	$optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
}


$datasort= array();
		
switch($method){
	case'sumber':
		$tab="<fieldset><legend>Info</legend><div>";
		switch($param['jenis']){
			case'prdbgt':
			case'prdbgtbln':
				$tab.="Sumber data :<li>Kebun - Transaksi - SPB</li>";
				$tab.="<li>Kebun - Transaksi - Rekap Panen Perblok</li>";
				$tab.="<li>Kebun - Transaksi - Sensus Produksi</li>";
				$tab.="<li>Anggaran - Transaksi - Budget Kebun - Budget Produksi</li>";
			break;
			case'prd':
				$tab.="Sumber data :<li>Kebun - Transaksi - SPB</li>";
				$tab.="<li>Kebun - Transaksi - Rekap Panen Perblok</li>";
			break;
			case'upahpanen':
				$tab.="Sumber data :<li>Kebun - Transaksi - Kegiatan Panen</li>";
				$tab.="<li>Kebun - Proses - Premi Pemanen</li>";
			break;
			case'prdpnn':
				$tab.="Sumber data :<li>Kebun - Transaksi - SPB</li>";
				$tab.="<li>Kebun - Transaksi - Rekap Panen Perblok</li>";
				$tab.="<li>data ditampilkan berdasarkan tanggal panen</li>";
			break;
			case'byy':
				$tab.="Sumber data :<li>Data Jurnal.</li>";
			break;
			case'bkm':
				$tab.="Sumber data :<li>Kebun - Transaksi - Buku Kegiatan Mandor.</li>";
			break;
			case'pnn':
				$tab.="Sumber data :<li>Kebun - Proses - Premi Pemanen.</li>";
			break;
			case'atbs':
				$tab.="Sumber data :<li>Kebun - Transaksi - Rekap Angkutan TBS.</li>";
			break;
			case'fee':
				$tab.="Sumber data :<li>Kebun - Proses - Fee Panen.</li>";
			break;
			case'aresta':
				$tab.="Sumber data :<li>Setup - Master Blok.</li>";
			break;
			case'bapp':
				$tab.="Sumber data :<li>Kontrak - Transaksi - BAPP Kontraktor.</li>";
			break;
			case'detpayroll':
				$tab.="Sumber data :<li>SDM - Transaksi - Admin Personalia - Absensi.</li>";
				$tab.="<li>Kebun - Transaksi - Buku Kegiatan Mandor [tab ABSENSI].</li>";
			break;
			case'tmb':
				$tab.="Sumber data :<li>Pabrik - Laporan - Timbangan.</li><li>Data juga bisa diakses melalui Telegram <a href=http://t.me/owlksp_robot target='_blank'>click here</a></li>";
			break;
		}
		$tab.="<li style=color:blue;font-weight:bold;>Jika pilihan Jenis berbeda dengan pilihan sebelumnya, pastikan anda Reload Frame terlebih dahulu.</li>";
		$tab.="</div></fieldset>";
		echo $tab; 
	break;
	case'formfav':
		$tab="<table>";
		$tab.="<tr>
					<td>Nama Favorit<td>
					<td>:<td>
					<td><input class=myinputtext id=namafav style=width:200px><td>
					<td><button onclick=favorit2(); class=mybutton>Save</button><td>
				</tr>";
		$tab.="</table>";
		
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable style=min-width:370px>
			<thead><tr class=rowheader>
				<td align=center>No</td>
				<td align=center>Nama Favorit</td>
				<td align=center>Dibuat Oleh</td>
				<td align=center colspan=2>Action</td>";
		$tab.="</tr>
			</thead>
			<tbody id=loadformfav>";
		
		$tab.="
		</tbody>
	</table>";
	echo $tab; 
	break;
	case'getfromfav':
		$str = "select a.*, b.label, b.karyawanid, b.filter, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where b.id='".$param['fromfavorit']."' order by a.id asc";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$filter=$bar['filter'];
		}
		
		// echo"<pre>";
		// // print_r(json_decode($filter));
		// foreach(json_decode($filter) as $key => $val){
			// echo $key.$val."<br>";
			
		// }
		
		// exit("error");
		echo $filter;
	break;
	case'loadformfav':
		
		$str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['tipe']."' order by id asc";
		
		$res = fetchdata($str);
		$no = 0;
		foreach ($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['label']."</td>";
			$tab.="<td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>";
			$tab.="<td align=center width=25px><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick=deletefield('".$bar['id']."','ht')></td>";
			$tab.="<td align=center width=25px><img src='images/orgicon.png' class='zImgBtn' title='Assign' onclick=setformuser('".$bar['id']."','".$bar['jenis']."')></td>";
			$tab.="</tr>";
		}
		
		$str = "select a.*, b.label, b.karyawanid, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['tipe']."' order by a.id asc";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['label']."</td>";
			$tab.="<td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>";
			$tab.="<td align=center width=25px colspan=2><img src='images/skyblue/delete.png' class='zImgBtn' title='Delete' onclick=deletefield('".$bar['id']."','dt','".$_SESSION['standard']['userid']."')></td>";
			//$tab.="<td align=center width=25px><img src='images/orgicon.png' class='zImgBtn' title='Assign' onclick='setformuser('".$bar['id']."','dt')'></td>";
			$tab.="</tr>";
		}
		
		$tab.="</tr>";
		echo $tab; 
	break;
	
	case'favorit':
		try {
			$owlPDO->beginTransaction();
				if($param['sumber']=='keu_pivot'){					
					$filter['pt']      = $param['pt'];
					$filter['regional']= $param['regional'];
					$filter['gudang']  = $param['kodeorg'];
					$filter['periode'] = $param['periode'];
					$filter['noakun']  = $param['noakun'];
					$filter['noakun2'] = $param['noakun2'];
					$filter['periode2']= $param['periode2'];
					$filter['tipe']    = $param['tipe'];
				}else{					
					$filter['kodeorg']= $param['kodeorg'];
					$filter['periode']= $param['periode'];
					$filter['tipe']   = $param['tipe'];
				}
				
				$data = array(
					'idmenu'    => $idmenu[$file],
					'jenis'     => $param['tipe'],
					'karyawanid'=> $_SESSION['standard']['userid'],
					'label'     => $param['namafav'],
					'filter'    => json_encode($filter),
					'data'      => $param['data']
				);
				$cols = array();
				foreach($data as $key=>$row) {
					$cols[] = $key;
				}
				$query = insertQuery($dbname,'pivot_favorit',$data,$cols);#exit("error".$query);
				$owlPDO->exec($query);
				
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
		
	break;
	case'mat':
		$str = "SELECT * from " . $dbname . ".log_5supplier";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		$str = "SELECT * from " . $dbname . ".organisasi";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}
		$str = "SELECT * from " . $dbname . ".log_5subklbarang";
		$res = fetchdata($str);
		foreach($res as $bar){
			$subkl[$bar['kode']]=$bar['namasubkelompok'];
		}
		
		$str = "SELECT * from " . $dbname . ".log_5klbarang";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kl[$bar['kode']]=$bar['kelompok'];
		}
	
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and kodegudang like '".$param['kodeorg']."%'";	
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
		}

		$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
		$datae[]=array('REGIONAL','NOTRANSAKSI','UNIT','GUDANG','KODE BARANG','KEL BARANG','SUB KEL BARANG','NAMA BARANG','SUB UNIT','KENDARAAN','NOPOL','DETAIL KEND','KEGIATAN','KETERANGAN','PERIODE','TANGGAL','SATUAN','DATA','JUMLAH');
		$numb=array(11);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row = array("UNIT");
		$col = array("PERIODE");
		$val = array("JUMLAH");
		$datasort = array("UNIT");
		
		$str = "SELECT * from " . $dbname . ".log_transaksi_vw where 1=1 ".$wh." and tipetransaksi='5'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$reg = $regional[substr($bar['kodegudang'],0,4)];
			
			$data[]=array(
				$reg,
				$bar['notransaksi'],
				substr($bar['kodegudang'],0,4),
				$bar['kodegudang']." - ".getNamaOrg($bar['kodegudang']),
				$bar['kodebarang'],
				$kl[substr($bar['kodebarang'],0,3)],
				$subkl[substr($bar['kodebarang'],0,5)],
				cleanSpecialChar(getNamaBrg($bar['kodebarang'])),
				$nmorg[$bar['kodeblok']],
				$bar['kodemesin'],
				getNopol($bar['kodemesin']),
				getNopol($bar['kodemesin'],'d'),
				getNamaKeg($bar['kodekegiatan']),
				cleanSpecialChar($bar['keterangan']),
				substr($bar['tanggal'],0,7),
				$bar['tanggal'],
				$bar['satuan'],
				"QTY",
				$bar['jumlah']
			);
			
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'upahpanen':
		$where=$wh="";
		if($param['kodeorg']!=''){
			$where.=" and kodeorg='".$param['kodeorg']."'";
			$wh.=" and unit='".$param['kodeorg']."'";
		}else{
			$where.=" and kodeorg in (".getOrgDetail(2).")";
			$wh.=" and unit in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$where.=" and periode like '".$param['periode']."%'";
			$wh.=" and tanggal like '".$param['periode']."%'";
		}

		$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
		
		// $str = "select a.*, b.tanggal  from " . $dbname . ".kebun_prestasi_detail a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where 1=1 ".$wh.""; 
		// $res = fetchdata($str);
		// $luaspanen=array();
		// foreach($res as $bar){
		// }
		
		$wh.=" and notransaksi like '%PNN%'";
		$str = "select *  from " . $dbname . ".kebun_prestasi_detail_vw where 1=1 ".$wh.""; #exit("error$str");
		$res = fetchdata($str);
		
		$ttlhadir=array();
		$tglhadir=array();
		foreach($res as $bar){
			$luaspanen[$bar['karyawanid']][$bar['tanggal']][$bar['kodeorg']]+=$bar['luaspanen'];
			$tglhadir[$bar['karyawanid']][$bar['tanggal']]=1;
			$ttlhadir[$bar['karyawanid']][$bar['tanggal']]+=1;
		}
		$data[]=array('NOTRANSAKSI','JURNAL','UNIT','DIVISI','PERIODE','TANGGAL','BLOK','TT','MANDOR','KERANI','NIK','NAMA','JENIS','DATA','JUMLAH');
		$datasort= array();
		$row     = array("UNIT","DIVISI","NIK","NAMA");
		$col     = array("TANGGAL","JENIS","DATA");
		$val     = array("JUMLAH");
		foreach($res as $bar){
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'LUAS',
				'HA',
				$luaspanen[$bar['karyawanid']][$bar['tanggal']][$bar['kodeorg']]
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'ABSENSI',
				'HADIR',
				$tglhadir[$bar['karyawanid']][$bar['tanggal']]/$ttlhadir[$bar['karyawanid']][$bar['tanggal']]
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'DENDA',
				($bar['upahpenalty']*(-1))
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'PREMI',
				$bar['upahpremilebihbasis']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'RP BRD',
				$bar['premibrondol']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'RP',
				'UPAH',
				($bar['upahkerja']-$bar['upahpenalty'])
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'KG',
				'KG',
				$bar['hasilkerjakg']
			);
			$data[]=array(
				$bar['notransaksi'],
				$bar['jurnal'],
				$bar['unit'],
				$bar['divisi'],
				$bar['periode'],
				$bar['tanggal'],
				$nmorg[$bar['kodeorg']],
				$bar['tahuntanam'],
				$nmkary[$bar['nikmandor']],
				$nmkary[$bar['kerani']],
				$nikkar[$bar['karyawanid']],
				$nmkary[$bar['karyawanid']],
				'JJG',
				'JJG',
				$bar['hasilkerja']
			);
			
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'bapp':
		$where=$wh=$whr="";
		if($param['kodeorg']!=''){
			$where.=" and substr(kodeblok,1,4)='".$param['kodeorg']."'";
			$wh.=" and substr(kodeorg,1,4)='".$param['kodeorg']."'";
			$whr.=" and substr(unit,1,4)='".$param['kodeorg']."'";
		}else{
			$where.=" and substr(kodeblok,1,4) in (".getOrgDetail(2).")";
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(unit,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$where.=" and tanggal like '".$param['periode']."%'";
		}
		$wh.=" and tanggal like '".substr($param['periode'],0,4)."%'";
		$whr.=" and tanggaldari like '".substr($param['periode'],0,4)."%'";

		$str = "select * from " . $dbname . ".log_spkht where 1=1 ".$wh.""; #exit("error$str");
		$res = fetchdata($str);
		foreach($res as $bar){
			$kontr[$bar['notransaksi']]=$bar['koderekanan'];
			$nopeng[$bar['notransaksi']]=$bar['nopengajuan'];
		}
		
		$str = "select * from " . $dbname . ".lgl_pengajuanspkht where 1=1 ".$whr.""; #exit("error$str");
		$res = fetchdata($str);
		foreach($res as $bar){
			$jenis[$bar['notransaksi']]=$bar['jenis'];
		}
		
		$str = "select * from " . $dbname . ".log_5supplier"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$namakont[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$ststjurnal=array("0"=>"Belum Posting","1"=>"Sudah Posting");
		
		
		$data[]=array('NOSPK','NAMA KONTRAKTOR','JENIS','NO BAPP','TERMIN','NO PENGAJUAN','POSTING','APPROVAL','KODEORG','DIVISI','PERIODE','TANGGAL','BLOK','TT','NOAKUN','NAMA AKUN','KODE KEGIATAN','NAMA KEGIATAN','SATUAN','DATA','JUMLAH');
		$datasort= array();
		$row     = array("NOSPK");
		$col     = array("PERIODE","DATA");
		$val     = array("JUMLAH");

		$str = "select *  from " . $dbname . ".log_baspk where 1=1 ".$where.""; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[]=array(
				$bar['notransaksi'],
				$namakont[$kontr[$bar['notransaksi']]],
				$jenis[$nopeng[$bar['notransaksi']]],
				$bar['keterangan'],
				$bar['termin'],
				$bar['nopengajuan'],
				$ststjurnal[$bar['statusjurnal']],
				$arrHsl[$bar['statuspengajuan']],
				substr($bar['kodeblok'],0,4),
				substr($bar['kodeblok'],0,6),
				substr($bar['tanggal'],0,7),
				$bar['tanggal'],
				getNamaOrg($bar['kodeblok']),
				getBlok($bar['kodeblok'],'tahuntanam'),
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				getNamaKeg($bar['kodekegiatan']),
				getNamaKeg($bar['kodekegiatan'],'satuan'),
				'PRES',
				$bar['hasilkerjarealisasi']
			);
			$data[]=array(
				$bar['notransaksi'],
				$namakont[$kontr[$bar['notransaksi']]],
				$jenis[$nopeng[$bar['notransaksi']]],
				$bar['keterangan'],
				$bar['termin'],
				$bar['nopengajuan'],
				$ststjurnal[$bar['statusjurnal']],
				$arrHsl[$bar['statuspengajuan']],
				substr($bar['kodeblok'],0,4),
				substr($bar['kodeblok'],0,6),
				substr($bar['tanggal'],0,7),
				$bar['tanggal'],
				getNamaOrg($bar['kodeblok']),
				getBlok($bar['kodeblok'],'tahuntanam'),
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				getNamaKeg($bar['kodekegiatan']),
				getNamaKeg($bar['kodekegiatan'],'satuan'),
				'RP',
				$bar['jumlahrealisasi']
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'aresta':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg like '".$param['kodeorg']."%'";
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']==''){
			exit("Warning: Periode harus diisi.");
		}

		$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
		$datae[]=array('PT','NAMA PT','REGIONAL','UNIT','NAMA UNIT','DIVISI','NAMA DIVISI','BLOK','INTI/PLASMA','STATUS','TT','JENIS BIBIT','TOPOGRAFI','KELAS TANAH','DATA','JUMLAH');
		$numb=array(10);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row     = array("REGIONAL","UNIT");
		$col     = array("DATA");
		$val     = array("JUMLAH");
		$datasort= array("HA","PKK");
		
		$str = "SELECT * from " . $dbname . ".setup_blok where 1=1 ".$wh." and status='A'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodeorg= substr($bar['kodeorg'],0,4);
			$divisi = substr($bar['kodeorg'],0,6);
			$reg    = $regional[$kodeorg];
			$nmpt   = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kodeorg."'");
			
			$data[]=array(
				$nmpt[$kodeorg],
				$nmorg[$nmpt[$kodeorg]],
				$reg,
				$kodeorg,
				$nmorg[$kodeorg],
				$divisi,
				$nmorg[$divisi],
				$nmorg[$bar['kodeorg']],
				$bar['intiplasma'],
				$bar['statusblok'],
				$bar['tahuntanam'],
				$bar['jenisbibit'],
				$bar['topografi'],
				$bar['klasifikasitanah'],
				"HA",
				$bar['luasareaproduktif']
			);
			$data[]=array(
				$nmpt[$kodeorg],
				$nmorg[$nmpt[$kodeorg]],
				$reg,
				$kodeorg,
				$nmorg[$kodeorg],
				$divisi,
				$nmorg[$divisi],
				$nmorg[$bar['kodeorg']],
				$bar['intiplasma'],
				$bar['statusblok'],
				$bar['tahuntanam'],
				$bar['jenisbibit'],
				$bar['topografi'],
				$bar['klasifikasitanah'],
				"PKK",
				$bar['jumlahpokok']
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'prdbgt':
		$wh=$whr=$whb="";
		if($param['kodeorg']=='' and strlen($param['periode'])==4){
			exit("Warning : Data terlalu banyak, untuk menampilkan data setahun silahkan gunakan Jenis : Produksi vs Budget (Bulanan)");
		}
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg like '".$param['kodeorg']."%'";	
			$whr.=" and divisi like '".$param['kodeorg']."%'";	
			$whb.=" and kodeunit = '".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(divisi,1,4) in (".getOrgDetail(2).")";
			$whb.=" and substr(kodeunit,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
			$whr.=" and tanggal like '".$param['periode']."%'";	
			$whb.=" and tahunbudget = '".substr($param['periode'],0,4)."'";	
		}		

		$str = "select tanggal,kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg from " . $dbname . ".kebun_spb_vw
		where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgk[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjg'];
			$kgwb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['kgwb'];			
		}

		$str = "select tanggal,substr(divisi,1,4) as kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(jjgpanen ) as jjgpanen, sum(luaspanen) as luaspanen from " . $dbname . ".kebun_rekappnn
		where 1=1 ".$whr." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgp[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjgpanen'];
			$luas[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['luaspanen'];
			
		}
		
		$str = "select tanggal, substr(kodeorg,1,4) as kodeorg, kodeblok, substr(tanggal,1,7) as prd, kodeorg as divisi, sum(jumlah) as jjg, sum(kgsensus) as kg from " . $dbname . ".kebun_rencanapanen
		where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, kodeblok, substr(tanggal,1,7), tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgs[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]+=$bar['jjg'];
			$kgs[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]+=$bar['kg'];
			
		}
		
		#ambil prd bgt
		if(strlen($param['periode'])==4){
			$bulan='12'; 
		}else{			
			$bulan=substr($param['periode'],-2);
		}
		$tahun=substr($param['periode'],0,4);
		
		$str=" select * from ".$dbname.".bgt_produksi_kebun a where 1=1 ".$whb." order by kodeblok";
		$res = fetchdata($str);
		foreach($res as $bar){
			$bar['divisi']=substr($bar['kodeblok'],0,6);
			if($bulan=='12'){
				for($i=1;$i<=intval($bulan);$i++){
					$r="kg".addZero($i,2);
					$j="jjg".addZero($i,2);
					
					$tglawal  = $tahun."-".addZero($i,2)."-01";
					$tglakhir = tglakhir($tglawal);
					$rangetgl = rangeTanggal($tglawal,$tglakhir);
					$hkbgt='0';
					foreach($rangetgl as $tanggal){
						$harilibur = getjenisharikerja(substr($bar['kodeunit'],0,4),$tanggal);
						if($harilibur!='LIBUR'){
							$hkbgt+=1;
						}
					}
					$no=$tempkgbgt=$tempjjgbgt=0;
					foreach($rangetgl as $tanggal){
						$harilibur = getjenisharikerja(substr($bar['kodeunit'],0,4),$tanggal);
						if($harilibur!='LIBUR'){
							$no++;
							if($no<$hkbgt){
								$kgbgt=round($bar[$r]/$hkbgt,0);
								$tempkgbgt+=$kgbgt;
								$jjgbgt=round($bar[$j]/$hkbgt,0);
								$tempjjgbgt+=$jjgbgt;
							}else{
								$kgbgt=$bar[$r]-$tempkgbgt;
								$jjgbgt=$bar[$j]-$tempjjgbgt;
							}
							$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tanggal]+=$kgbgt;
							$jjgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tanggal]+=$jjgbgt;
						}
						$dt[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tanggal]=$tanggal;
					}
				}
			}else{
				$r="kg".addZero($bulan,2);
				$j="jjg".addZero($bulan,2);
				
				$tglawal  = $tahun."-".addZero($bulan,2)."-01";
				$tglakhir = tglakhir($tglawal);
				$rangetgl = rangeTanggal($tglawal,$tglakhir);
				$hkbgt='0';
				foreach($rangetgl as $tanggal){
					$harilibur = getjenisharikerja(substr($bar['kodeunit'],0,4),$tanggal);
					if($harilibur!='LIBUR'){
						$hkbgt+=1;
					}
				}
				$no=$tempkgbgt=$tempjjgbgt=0;
				foreach($rangetgl as $tanggal){
					$harilibur = getjenisharikerja(substr($bar['kodeunit'],0,4),$tanggal);
					if($harilibur!='LIBUR'){
						$no++;
						if($no<$hkbgt){
							$kgbgt=round($bar[$r]/$hkbgt,2);
							$tempkgbgt+=$kgbgt;
							$jjgbgt=round($bar[$j]/$hkbgt,0);
							$tempjjgbgt+=$jjgbgt;
						}else{
							$kgbgt=$bar[$r]-$tempkgbgt;
							$jjgbgt=$bar[$j]-$tempjjgbgt;
						}
						$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tanggal]+=$kgbgt;
						$jjgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tanggal]+=$jjgbgt;
					}
					$dt[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tanggal]=$tanggal;
				}
				//$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tahun."-".addZero($bulan,2)."-01"]+=$bar[$r];
			}
		}
		
		$datae[]=array('UNIT','DIVISI','PERIODE','TAHUNTANAM','BLOK','TANGGAL','HARI','JENIS','DATA','JUMLAH');
		$numb=array(8);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","DIVISI");
		$col = array("PERIODE","JENIS","DATA");
		$val = array("JUMLAH");
		
		$datasort = array("HA","JJG","KG");
		
		foreach($dt as $kodeorg => $vdiv){
			foreach($vdiv as $divisi => $vblok){
				foreach($vblok as $blok => $vprd){
					foreach($vprd as $prd => $vtgl){
						foreach($vtgl as $tgl){
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'BUDGET','KG',
								$kgb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'BUDGET','JJG',
								$jjgb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'KIRIM','JJG',
								$jjgk[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'KIRIM','KG',
								$kgwb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'PANEN','JJG',
								$jjgp[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'PANEN','HA',
								$luas[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'SENSUS','JJG',
								$jjgs[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'SENSUS','KG',
								$kgs[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
						}
					}
				}
			}
		}
		
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'prdbgtbln':
		$wh=$whr=$whb="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg like '".$param['kodeorg']."%'";	
			$whr.=" and divisi like '".$param['kodeorg']."%'";	
			$whb.=" and kodeunit = '".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(divisi,1,4) in (".getOrgDetail(2).")";
			$whb.=" and substr(kodeunit,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
			$whr.=" and tanggal like '".$param['periode']."%'";	
			$whb.=" and tahunbudget = '".substr($param['periode'],0,4)."'";	
		}		

		$str = "select tanggal,kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg from " . $dbname . ".kebun_spb_vw
		where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgk[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjg'];
			$kgwb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['kgwb'];			
		}

		$str = "select tanggal,substr(divisi,1,4) as kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(jjgpanen ) as jjgpanen, sum(luaspanen) as luaspanen from " . $dbname . ".kebun_rekappnn
		where 1=1 ".$whr." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgp[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjgpanen'];
			$luas[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['luaspanen'];
			
		}
		
		$str = "select tanggal, substr(kodeorg,1,4) as kodeorg, kodeblok, substr(tanggal,1,7) as prd, kodeorg as divisi, sum(jumlah) as jjg, sum(kgsensus) as kg from " . $dbname . ".kebun_rencanapanen
		where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, kodeblok, substr(tanggal,1,7), tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgs[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]+=$bar['jjg'];
			$kgs[$bar['kodeorg']][$bar['divisi']][$bar['kodeblok']][$bar['prd']][$bar['tanggal']]+=$bar['kg'];
			
		}
		
		#ambil prd bgt
		if(strlen($param['periode'])==4){
			$bulan='12';
		}else{			
			$bulan=substr($param['periode'],-2);
		}
		$tahun=substr($param['periode'],0,4);
		
		$str=" select * from ".$dbname.".bgt_produksi_kebun a where 1=1 ".$whb." order by kodeblok";
		$res = fetchdata($str);
		foreach($res as $bar){
			$bar['divisi']=substr($bar['kodeblok'],0,6);
			if($bulan=='12'){
				for($i=1;$i<=intval($bulan);$i++){
					$r="kg".addZero($i,2);
					$j="jjg".addZero($i,2);
					
					$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tahun."-".addZero($i,2)."-01"]+=$bar[$r];
					$jjgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tahun."-".addZero($i,2)."-01"]+=$bar[$j];
					$dt[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($i,2)][$tahun."-".addZero($i,2)."-01"]=$tahun."-".addZero($i,2)."-01";
				}
			}else{
				$r="kg".addZero($bulan,2);
				$j="jjg".addZero($bulan,2);
				
				$kgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tahun."-".addZero($bulan,2)."-01"]+=$bar[$r];
				$jjgb[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tahun."-".addZero($bulan,2)."-01"]+=$bar[$j];
				$dt[$bar['kodeunit']][$bar['divisi']][$bar['kodeblok']][$tahun."-".addZero($bulan,2)][$tahun."-".addZero($bulan,2)."-01"]=$tahun."-".addZero($bulan,2)."-01";
			}
		}
		
		$datae[]=array('UNIT','DIVISI','PERIODE','TAHUNTANAM','BLOK','TANGGAL','HARI','JENIS','DATA','JUMLAH');
		$numb=array(8);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","DIVISI");
		$col = array("PERIODE","JENIS","DATA");
		$val = array("JUMLAH");
		
		$datasort = array("HA","JJG","KG");
		
		foreach($dt as $kodeorg => $vdiv){
			foreach($vdiv as $divisi => $vblok){
				foreach($vblok as $blok => $vprd){
					foreach($vprd as $prd => $vtgl){
						foreach($vtgl as $tgl){
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'BUDGET','KG',
								$kgb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'BUDGET','JJG',
								$jjgb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'KIRIM','JJG',
								$jjgk[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'KIRIM','KG',
								$kgwb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'PANEN','JJG',
								$jjgp[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'PANEN','HA',
								$luas[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'SENSUS','JJG',
								$jjgs[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								hari($tgl),
								'SENSUS','KG',
								$kgs[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
						}
					}
				}
			}
		}
		
		// echo"<pre>";
		// echo count($data);
		// echo"</pre>";
		// exit("error.$str");
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'tmb':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
		}

		$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
		$datae[]=array('TIKET','REGIONAL','UNIT','DIVISI','INTI/PLASMA','THN TNM','PKS','PRD','TANGGAL','NOSPB','NOPOL','SUPIR','DATA','JUMLAH');
		$numb=array(11);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row = array("PKS","UNIT");
		$col = array("PRD","DATA");
		$val = array("JUMLAH");
		$datasort = array("JJG","NETTO I","SORTASI","NETTO II");
		
		
		$str = "SELECT * from " . $dbname . ".pabrik_timbangan
		where 1=1 ".$wh." and kodebarang='40000003'";
		
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmsup=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier',"kodetimbangan='".$bar['kodecustomer']."'");
			if($bar['kodeorg']==''){
				$bar['kodeorg']="EXTN";
				if($nmsup[$bar['kodecustomer']]==''){
					$bar['divcode']=$bar['kodecustomer'];
				}else{					
					$bar['divcode']=$nmsup[$bar['kodecustomer']];
				}
			}
			
			if($regional[$bar['kodeorg']]!=''){
				$reg = $regional[$bar['kodeorg']];
				if($bar['intiplasma']==''){
					if(getNamaOrg($bar['kodeorg'],'inti')=='1'){
						$bar['intiplasma']='INTI';
					}else{
						$bar['intiplasma']='PLASMA';
					};
				}
			}else{
				$reg = $regional[$bar['millcode']];
				$bar['intiplasma']='EXTN';
			}
			if(getNamaOrg($bar['kodeorg'])==""){
				$bar['kodeorg']="External - Swadaya";
			}else{
				$bar['kodeorg']=getNamaOrg($bar['kodeorg']);
			}
			
			$data[]=array(
				$bar['notransaksi'],
				$reg,
				$bar['kodeorg'],
				$bar['divcode'],
				$bar['intiplasma'],
				$bar['thntm1'],
				$bar['millcode'],
				substr($bar['tanggal'],0,7),
				substr($bar['tanggal'],0,10),
				$bar['nospb'],
				$bar['nokendaraan'],
				$bar['supir'],
				"JJG",
				$bar['jumlahtandan1']
			);
			$data[]=array(
				$bar['notransaksi'],
				$reg,
				$bar['kodeorg'],
				$bar['divcode'],
				$bar['intiplasma'],
				$bar['thntm1'],
				$bar['millcode'],
				substr($bar['tanggal'],0,7),
				substr($bar['tanggal'],0,10),
				$bar['nospb'],
				$bar['nokendaraan'],
				$bar['supir'],
				"NETTO I",
				$bar['beratbersih']
			);
			$data[]=array(
				$bar['notransaksi'],
				$reg,
				$bar['kodeorg'],
				$bar['divcode'],
				$bar['intiplasma'],
				$bar['thntm1'],
				$bar['millcode'],
				substr($bar['tanggal'],0,7),
				substr($bar['tanggal'],0,10),
				$bar['nospb'],
				$bar['nokendaraan'],
				$bar['supir'],
				"SORTASI",
				$bar['kgpotsortasi']
			);
			$data[]=array(
				$bar['notransaksi'],
				$reg,
				$bar['kodeorg'],
				$bar['divcode'],
				$bar['intiplasma'],
				$bar['thntm1'],
				$bar['millcode'],
				substr($bar['tanggal'],0,7),
				substr($bar['tanggal'],0,10),
				$bar['nospb'],
				$bar['nokendaraan'],
				$bar['supir'],
				"NETTO II",
				$bar['beratbersih']-$bar['kgpotsortasi']
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'fee':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and periode like '".$param['periode']."%'";	
		}

		$datae[]=array('NOTRANSAKSI','POSTING','PENERIMA','UNIT','DIVISI','PRD BULAN','PRD BAYAR','TANGGAL','AKUN/KEGIATAN','JENIS KEND','JENIS FEE','BLOK','KG','RUPIAH');
		$numb=array(12,13);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row = array("UNIT","DIVISI");
		$col = array("PRD BULAN");
		$val = array("RUPIAH");
		
		$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmfee = makeOption($dbname,'kebun_5namafee','id,nama');
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		
		$post  = array('1'=>'Posted','0' =>'Not Posted');
		$prdbyr= array('0'=>'1 s/d 30','1'=>'1 s/d 15','2'=>'16 s/d 30');
		
		$str = "SELECT * from " . $dbname . ".kebun_rekapangkutantbsdtfee where 1=1 ".$wh."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($nmakun[$bar['noakun']]!=''){
				$akun=$nmakun[$bar['noakun']];
			}else{
				$akun=$nmkeg[$bar['noakun']];
			}
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$nmfee[$bar['id']],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$akun,
				$bar['jenis'],
				$bar['jenisfee'],
				$nmorg[$bar['blok']],
				$bar['kgtotal'],
				$bar['rupiah']
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'atbs':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and b.kodeorg='".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(b.kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and b.periode like '".$param['periode']."%'";	
		}

		$datae[]=array('NOSPB','POSTING','NOSPK','BAPP','KONTRAKTOR','UNIT','DIVISI','PRD BULAN','PRD BAYAR','TANGGAL','KEGIATAN','JENIS','TUJUAN','BLOK','DATA','JUMLAH');
		$numb=array(15);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row = array("UNIT","DIVISI");
		$col = array("PRD BULAN","DATA");
		$val = array("JUMLAH");
		$datasort = array("KG TOTAL","KG BRD","KG NETTO","RUPIAH","DENDA","RP NETTO");
		
		$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$post  = array('1'=>'Posted','0' =>'Not Posted');
		$prdbyr= array('0'=>'1 s/d 30','1'=>'1 s/d 15','2'=>'16 s/d 30');
		
		$str = "SELECT * from " . $dbname . ".kebun_rekapangkutantbsdt a 
		left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb 
		where 1=1 ".$wh." and a.rupiah>0";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kont=makeOption($dbname,'lgl_pengajuanspkht','notransaksi,koderekanan',"notransaksi='".$bar['spk']."'");
			$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kont[$bar['spk']]."'");
			
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"KG TOTAL",
				$bar['kgtotal']
			);
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"KG BRD",
				$bar['kgbrd']
			);
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"KG NETTO",
				$bar['kgwb']
			);
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"RUPIAH",
				$bar['rupiah']
			);
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"DENDA",
				$bar['potonganrp']
			);
			$data[]=array(
				$bar['nospb'],
				$post[$bar['posting']],
				$bar['spk'],
				$bar['nobapp'],
				$nmsup[$kont[$bar['spk']]],
				$bar['kodeorg'],
				$bar['divisi'],
				$bar['periode'],
				$prdbyr[$bar['periodebyr']],
				$bar['tanggal'],
				$nmkeg[$bar['jeniskegiatan']],
				$bar['jenis'],
				$bar['tujuan'],
				$nmorg[$bar['blok']],
				"RP NETTO",
				$bar['rupiah']-$bar['potonganrp']
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'prd':
		$wh=$whr="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
			$whr.=" and divisi like '".$param['kodeorg']."%'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(divisi,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
			$whr.=" and tanggal like '".$param['periode']."%'";	
		}		

		$str = "select penerimatbs,nospb,tanggal,kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, sum(brondolan) as brondolan from " . $dbname . ".kebun_spb_vw where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal, nospb";
		//exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$nspb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['nospb'];
			$pks[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['penerimatbs'];
			$jjgk[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjg'];
			$kgwb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['kgwb'];			
			$kgbrd[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['brondolan'];			
			$sort[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]+=$bar['kgwb']-$bar['kgwbnetto'];			
		}

		$str = "select tanggal,substr(divisi,1,4) as kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(jjgpanen ) as jjgpanen, sum(luaspanen) as luaspanen from " . $dbname . ".kebun_rekappnn
		where 1=1 ".$whr." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgp[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjgpanen'];
			$luas[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['luaspanen'];
			
		}
		
		$datae[]=array('UNIT','DIVISI','PERIODE','TAHUNTANAM','BLOK','TANGGAL','NOSPB','PKS','DATA','JUMLAH');
		$numb=array(9);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","DIVISI");
		$col = array("PERIODE","DATA");
		$val = array("JUMLAH");
		$datasort = array("HA PNN","JJG PNN","JJG KRM","KG WB","SORTASI","KG BRD");
		
		$nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
		
		foreach($dt as $kodeorg => $vdiv){
			foreach($vdiv as $divisi => $vblok){
				foreach($vblok as $blok => $vprd){
					foreach($vprd as $prd => $vtgl){
						foreach($vtgl as $tgl){
							if(getNamaOrg($pks[$kodeorg][$divisi][$blok][$prd][$tgl])!=''){
								$nmpks = getNamaOrg($pks[$kodeorg][$divisi][$blok][$prd][$tgl]);
							}else{
								$nmpks = $nmcust[$pks[$kodeorg][$divisi][$blok][$prd][$tgl]];
							}
							
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'SORTASI',
								$sort[$kodeorg][$divisi][$blok][$prd][$tgl]*(-1)
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'KG BRD',
								$kgbrd[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'JJG KRM',
								$jjgk[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'KG WB',
								$kgwb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'JJG PNN',
								$jjgp[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'HA PNN',
								$luas[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
						}
					}
				}
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
		
	break;
	case'prdpnn':
		$wh=$whr="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
			$whr.=" and divisi like '".$param['kodeorg']."%'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(divisi,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and tanggalpanen like '".$param['periode']."%'";	
			$whr.=" and tanggal like '".$param['periode']."%'";	
		}		

		$str = "select sum(brondolan) as brondolan, penerimatbs, nospb, tanggalpanen,kodeorg,blok,substr(tanggalpanen,1,7) as prd, divisi, tahuntanam, sum(kgwb) as kgwb, sum(jjg) as jjg, sum(kgwbnetto) as kgwbnetto from " . $dbname . ".kebun_spb_vw where 1=1 ".$wh." and posting='1' group by kodeorg, divisi, blok, substr(tanggalpanen,1,7),tanggalpanen, nospb";
		# exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
			$nspb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]=$bar['nospb'];
			$pks[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]=$bar['penerimatbs'];
			$jjgk[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]+=$bar['jjg'];
			$kgwb[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]+=$bar['kgwb'];
			$sort[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]+=$bar['kgwb']-$bar['kgwbnetto'];			
			$kgbrd[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggalpanen']]+=$bar['brondolan'];			
		}

		$str = "select tanggal,substr(divisi,1,4) as kodeorg,blok,substr(tanggal,1,7) as prd, divisi, tahuntanam, sum(jjgpanen ) as jjgpanen, sum(luaspanen) as luaspanen from " . $dbname . ".kebun_rekappnn
		where 1=1 ".$whr." and posting='1' group by kodeorg, divisi, blok, substr(tanggal,1,7),tanggal";
		$res = fetchdata($str);
		foreach($res as $bar){
			$dt[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]=$bar['tanggal'];
			$jjgp[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['jjgpanen'];
			$luas[$bar['kodeorg']][$bar['divisi']][$bar['blok']][$bar['prd']][$bar['tanggal']]+=$bar['luaspanen'];
			
		}
		
		$datae[]=array('UNIT','DIVISI','PERIODE','TAHUNTANAM','BLOK','TGL PNN','NOSPB','PKS','DATA','JUMLAH');
		$numb=array(9);
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("UNIT","DIVISI");
		$col = array("PERIODE","DATA");
		$val = array("JUMLAH");
		$datasort = array("HA PNN","JJG PNN","JJG KRM","KG WB","SORTASI","KG BRD");
		$nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
		
		foreach($dt as $kodeorg => $vdiv){
			foreach($vdiv as $divisi => $vblok){
				foreach($vblok as $blok => $vprd){
					foreach($vprd as $prd => $vtgl){
						foreach($vtgl as $tgl){
							if(getNamaOrg($pks[$kodeorg][$divisi][$blok][$prd][$tgl])!=''){
								$nmpks = getNamaOrg($pks[$kodeorg][$divisi][$blok][$prd][$tgl]);
							}else{
								$nmpks = $nmcust[$pks[$kodeorg][$divisi][$blok][$prd][$tgl]];
							}
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'SORTASI',
								$sort[$kodeorg][$divisi][$blok][$prd][$tgl]*(-1)
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'KG BRD',
								$kgbrd[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'JJG KRM',
								$jjgk[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'KG WB',
								$kgwb[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'JJG PNN',
								$jjgp[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
							$data[]=array(
								$kodeorg,
								$divisi,
								$prd,
								$nmtt[$blok],
								$nmorg[$blok],
								$tgl,
								$nspb[$kodeorg][$divisi][$blok][$prd][$tgl],
								$nmpks,
								'HA PNN',
								$luas[$kodeorg][$divisi][$blok][$prd][$tgl]
							);
						}
					}
				}
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
		
	break;
	case'byy':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and kodeorg='".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and periode like '".$param['periode']."%'";	
		}

	
		$datae[]=array('NOJURNAL','UNIT','PERIODE','TANGGAL','BLOK','DIVISI','KLP BIAYA','NOAKUN','NAMA AKUN','RUPIAH','KETERANGAN','KODE KEGIATAN','NAMA KEGIATAN','KODE BARANG','NAMA BARANG','NIK','No REFF','D/K','DATA');
		$numb=array(9);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row = array("KLP BIAYA","UNIT");
		$col = array("PERIODE","D/K","DATA");
		$val = array("RUPIAH");
		
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		
		#ajaran indra wibi harus dipatok
		$akunupahpnn =array('6110101','6110102');
		$akuntranspnn=array('6110103','6110104');
		$lbrpupuk    =array('621010302','621010305','621010308');
		$transpupuk  =array('621010323','621010324');
		
		$str = "select substr(noakun,1,3) as kelbyy,kodekegiatan,kodeorg,kodeblok as blok, substr(kodeblok,1,6) as divisi,periode as prd, noakun, jumlah,keu_jurnaldt_vw.*  from " . $dbname . ".keu_jurnaldt_vw where 1=1 ".$wh." and substr(noakun,1,3) in ('611','621','126','128')";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			/* $data[]=array(
				$bar['nojurnal'],
				$bar['kodeorg'],
				$bar['prd'],
				$bar['tanggal'],
				$nmorg[$bar['blok']],
				$bar['divisi'],
				$nmakun[$bar['kelbyy']],
				$bar['noakun'],
				$nmakun[$bar['noakun']],
				$bar['jumlah'],
				$bar['keterangan'],
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$bar['kodebarang'],
				$nmbrg[$bar['kodebarang']],
				getNamaKaryawan($bar['nik']),
				$bar['noreferensi'],
				($bar['jumlah']>=0?"Debet":"Kredit")
			);
			 */
			if(substr($bar['noakun'],0,3)=='611'){
				#biaya panen
				if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Labor'
					);
				}else if(substr($bar['kodejurnal'],0,3)=='INV'){
					#material
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Material'
					);
				}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
					#transport
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Transport'
					);
				}else{
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Other'
					);
				}
			}
			if(substr($bar['noakun'],0,3)=='621'){
				#TM
				if($bar['noakun']=='6210103'){
					#biaya pupuk
					if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
						#labor
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Labor'
						);
					}else if(substr($bar['kodejurnal'],0,3)=='INV'){
						#material
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Material'
						);
					}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
						#transport
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Transport'
						);
					}else{
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Other'
						);
					}
				}else{
					#biaya pemel
					if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC'){
						#labor
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Labor'
						);
					}else if(substr($bar['kodejurnal'],0,3)=='INV'){
						#material
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Material'
						);
					}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
						#transport
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Transport'
						);
					}else{
						$data[]=array(
							$bar['nojurnal'],
							$bar['kodeorg'],
							$bar['prd'],
							$bar['tanggal'],
							$nmorg[$bar['blok']],
							$bar['divisi'],
							$nmakun[$bar['kelbyy']],
							$bar['noakun'],
							$nmakun[$bar['noakun']],
							$bar['jumlah'],
							$bar['keterangan'],
							$bar['kodekegiatan'],
							$nmkeg[$bar['kodekegiatan']],
							$bar['kodebarang'],
							cleanSpecialChar($nmbrg[$bar['kodebarang']]),
							getNamaKaryawan($bar['nik']),
							$bar['noreferensi'],
							($bar['jumlah']>=0?"Debet":"Kredit"),
							'Other'
						);
					}
				}
			}
			
			if(substr($bar['noakun'],0,3)=='126'){
				#biaya tbm
				if(substr($bar['kodejurnal'],0,3)!='INV'){
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Labor'
					);
				}else if(substr($bar['kodejurnal'],0,3)=='INV'){
					#material
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Material'
					);
				}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
					#transport
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Transport'
					);
				}else{
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Other'
					);
				}
			}
			if(substr($bar['noakun'],0,3)=='128'){
				#biaya bbt
				if(substr($bar['kodejurnal'],0,3)!='INV'){
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Labor'
					);
				}else if(substr($bar['kodejurnal'],0,3)=='INV'){
					#material
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Material'
					);
				}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
					#transport
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Transport'
					);
				}else{
					$data[]=array(
						$bar['nojurnal'],
						$bar['kodeorg'],
						$bar['prd'],
						$bar['tanggal'],
						$nmorg[$bar['blok']],
						$bar['divisi'],
						$nmakun[$bar['kelbyy']],
						$bar['noakun'],
						$nmakun[$bar['noakun']],
						$bar['jumlah'],
						$bar['keterangan'],
						$bar['kodekegiatan'],
						$nmkeg[$bar['kodekegiatan']],
						$bar['kodebarang'],
						cleanSpecialChar($nmbrg[$bar['kodebarang']]),
						getNamaKaryawan($bar['nik']),
						$bar['noreferensi'],
						($bar['jumlah']>=0?"Debet":"Kredit"),
						'Other'
					);
				}
			}
		}
		
		
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'bkm':
		$wh=$whr="";
		if($param['kodeorg']!=''){
			$wh.=" and b.kodeorg='".$param['kodeorg']."'";	
			$whr.=" and kodeorg like '".$param['kodeorg']."%'";	
		}else{
			$wh.=" and substr(b.kodeorg,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and b.tanggal like '".$param['periode']."%'";	
			$whr.=" and notransaksi like '".str_replace("-","",$param['periode'])."%'";	
		}
		
		if($param['tgl1']!='' and $param['tgl2']!=''){
			$wh.=" and b.tanggal between '".tanggalsystemn($param['tgl1'])."' and '".tanggalsystemn($param['tgl2'])."'";	
			$whr.=" and substr(notransaksi,1,8) between '".str_replace("-","",tanggalsystemn($param['tgl1']))."' and '".str_replace("-","",tanggalsystemn($param['tgl2']))."'";	
		}



		$datae[]=array('UNIT','PERIODE','TAHAP','TANGGAL','DIVISI','NOTRANSAKSI','NOBKM','MANDOR','MANDOR 1','BLOK','NOAKUN','NAMA AKUN','KODE KEGIATAN','NAMA KEGIATAN','SAT KEG','MATERIAL','SAT MAT','KARYID','NIK','NAMA KARYAWAN','DATA','JUMLAH','POSTING');
		$numb=array(17);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		$row = array("UNIT","DIVISI");
		$col = array("PERIODE","TAHAP","DATA");
		$val = array("JUMLAH");
		$datasort = array("PREST","HK","UPAH","PREMI");
		
		
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg =makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$satkeg =makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
		$post = array('1'=>'Posted','0'=>'Not Posted');
		
		// $wh.=" and a.kodekegiatan like '6210107%'";	
		$str = "SELECT a.nobkm,a.notransaksi,a.kodeorg,a.kodekegiatan,a.nikpemel,a.hasilkerja, a.jumlahhk,upahkerja-upahpenalty as upah, a.upahpremi+upahpremilebihbasis+premibasis as premi, a.rupiahpenalty, luaspanen, brondolan, jjgpenalty, tipetransaksi, tanggal, b.kodeorg as unit,jurnal,nikmandor,nikmandor1,c.jhk,c.umr,c.insentif from " . $dbname . ".kebun_prestasi a 
		left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
		left join " . $dbname . ".kebun_kehadiran c on a.notransaksi=c.notransaksi and c.nik=a.nikpemel and c.nourut=a.nourut
		where 1=1 ".$wh." and tipetransaksi != 'PNN'"; 
		
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['tanggal'],-2)>15){
				$tahap=2;
			}else{
				$tahap=1;
			}
			
			$data[]=array(
				$bar['unit'],
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				substr($bar['kodeorg'],0,6),
				$bar['notransaksi'],
				$bar['nobkm'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['nikmandor1']),
				$nmorg[$bar['kodeorg']],
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$satkeg[$bar['kodekegiatan']],
				"","",
				$bar['nikpemel'],
				getKary($bar['nikpemel'],'nik'),
				getNamaKaryawan($bar['nikpemel']),
				"PREST",
				$bar['hasilkerja'],
				$post[$bar['jurnal']]
			);
			$data[]=array(
				$bar['unit'],
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				substr($bar['kodeorg'],0,6),
				$bar['notransaksi'],
				$bar['nobkm'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['nikmandor1']),
				$nmorg[$bar['kodeorg']],
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$satkeg[$bar['kodekegiatan']],
				"","",
				$bar['nikpemel'],
				getKary($bar['nikpemel'],'nik'),
				getNamaKaryawan($bar['nikpemel']),
				"HK",
				$bar['jhk'],
				$post[$bar['jurnal']]
			);
			$data[]=array(
				$bar['unit'],
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				substr($bar['kodeorg'],0,6),
				$bar['notransaksi'],
				$bar['nobkm'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['nikmandor1']),
				$nmorg[$bar['kodeorg']],
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$satkeg[$bar['kodekegiatan']],
				"","",
				$bar['nikpemel'],
				getKary($bar['nikpemel'],'nik'),
				getNamaKaryawan($bar['nikpemel']),
				"UPAH",
				$bar['umr'],
				$post[$bar['jurnal']]
			);
			$data[]=array(
				$bar['unit'],
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				substr($bar['kodeorg'],0,6),
				$bar['notransaksi'],
				$bar['nobkm'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['nikmandor1']),
				$nmorg[$bar['kodeorg']],
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$satkeg[$bar['kodekegiatan']],
				"","",
				$bar['nikpemel'],
				getKary($bar['nikpemel'],'nik'),
				getNamaKaryawan($bar['nikpemel']),
				"PREMI",
				$bar['insentif'],
				$post[$bar['jurnal']]
			);
			
			
			$optunit[$bar['notransaksi']]=$bar['unit'];
			$opttgl[$bar['notransaksi']]=$bar['tanggal'];
			$optnobkm[$bar['notransaksi']]=$bar['nobkm'];
			$optmdr[$bar['notransaksi']]=$bar['nikmandor'];
			$optmdr1[$bar['notransaksi']]=$bar['nikmandor1'];
			$optjur[$bar['notransaksi']]=$bar['jurnal'];
		}
		
		$str = "select *  from " . $dbname . ".kebun_pakaimaterial where 1=1 ".$whr."";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$satbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
			if(substr($opttgl[$bar['notransaksi']],-2)>15){
				$tahap=2;
			}else{
				$tahap=1;
			}
			$data[]=array(
				$optunit[$bar['notransaksi']],
				substr($opttgl[$bar['notransaksi']],0,7),
				$tahap,
				$opttgl[$bar['notransaksi']],
				substr($bar['kodeorg'],0,6),
				$bar['notransaksi'],
				$optnobkm[$bar['notransaksi']],
				getNamaKaryawan($optmdr[$bar['notransaksi']]),
				getNamaKaryawan($optmdr1[$bar['notransaksi']]),
				$nmorg[$bar['kodeorg']],
				substr($bar['kodekegiatan'],0,7),
				getNamaAkun(substr($bar['kodekegiatan'],0,7)),
				$bar['kodekegiatan'],
				$nmkeg[$bar['kodekegiatan']],
				$satkeg[$bar['kodekegiatan']],
				cleanSpecialChar($nmbrg[$bar['kodebarang']]),
				$satbrg[$bar['kodebarang']],
				"","","",
				"MAT",
				$bar['kwantitas'],
				$post[$optjur[$bar['notransaksi']]]
			);
		}
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'pnn':
		$wh="";
		if($param['kodeorg']!=''){
			$wh.=" and unit='".$param['kodeorg']."'";	
			$whr.=" and kodeorg='".$param['kodeorg']."'";	
		}else{
			$wh.=" and substr(unit,1,4) in (".getOrgDetail(2).")";
			$whr.=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		}
		if($param['periode']!=''){
			$wh.=" and tanggal like '".$param['periode']."%'";	
			$whr.=" and substr(tanggal,1,10) like '".$param['periode']."%'";	
		}

		$datae[]=array('NOTRANSAKSI','POSTING','REGIONAL','UNIT','DIVISI','PERIODE','TAHAP','TANGGAL','MANDOR','KERANI','TT','NIK','KARYAWAN','BLOK','DATA','JUMLAH');
		$numb=array(13);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		
		
		$str = "SELECT * from " . $dbname . ".kebun_5bjr where periode like '".substr($param['periode'],0,4)."%'  order by periode asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$bjr[$bar['kodeorg']]=$bar['bjr'];
		}
		
		$str = "SELECT * from " . $dbname . ".pabrik_timbangan where 1=1 ".$whr." and kodebarang='40000003'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(getNamaOrg($bar['kodeorg'],'inti')==1){				
				$kgpks[$bar['kodeorg']][substr($bar['tanggal'],0,10)]+=$bar['beratbersih'];
				$datapks[$bar['kodeorg']][substr($bar['tanggal'],0,10)]=substr($bar['tanggal'],0,10);
			}
		}	
		
		$row = array("REGIONAL","UNIT");
		$col = array("PERIODE","TAHAP","DATA");
		$val = array("JUMLAH");
		$datasort = array("JJG","KG","KGWB","HA");
		
		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		$nmkeg =makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
		$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
		
		$post = array('1'=>'Posted','0'=>'Not Posted');
		$str = "SELECT * from " . $dbname . ".kebun_prestasi_vw where 1=1 ".$wh."";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['tanggal'],-2)>15){
				$tahap=2;
			}else{
				$tahap=1;
			}
			
			$data[]=array(
				$bar['notransaksi'],
				$post[$bar['jurnal']],
				$regional[$bar['unit']],
				$bar['unit']." - ".getNamaOrg($bar['unit']),
				substr($bar['kodeorg'],0,6),
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['kerani']),
				$bar['tahuntanam'],
				getKary($bar['karyawanid'],'nik'),
				getNamaKaryawan($bar['karyawanid']),
				$nmorg[$bar['kodeorg']],
				"JJG",
				$bar['hasilkerja']
			);
			$data[]=array(
				$bar['notransaksi'],
				$post[$bar['jurnal']],
				$regional[$bar['unit']],
				$bar['unit']." - ".getNamaOrg($bar['unit']),
				substr($bar['kodeorg'],0,6),
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['kerani']),
				$bar['tahuntanam'],
				getKary($bar['karyawanid'],'nik'),
				getNamaKaryawan($bar['karyawanid']),
				$nmorg[$bar['kodeorg']],
				"KG",
				$bar['hasilkerja']*$bjr[$bar['kodeorg']]
			);
			
			$data[]=array(
				$bar['notransaksi'],
				$post[$bar['jurnal']],
				$regional[$bar['unit']],
				$bar['unit']." - ".getNamaOrg($bar['unit']),
				substr($bar['kodeorg'],0,6),
				substr($bar['tanggal'],0,7),
				$tahap,
				$bar['tanggal'],
				getNamaKaryawan($bar['nikmandor']),
				getNamaKaryawan($bar['kerani']),
				$bar['tahuntanam'],
				getKary($bar['karyawanid'],'nik'),
				getNamaKaryawan($bar['karyawanid']),
				$nmorg[$bar['kodeorg']],
				"HA",
				$bar['luaspanen']
			);
		}
		foreach($datapks as $unit => $val1){
			foreach($val1 as $tanggal){
				if(substr($tanggal,-2)>15){
					$tahap=2;
				}else{
					$tahap=1;
				}
				$data[]=array(
					'',
					'1',
					$regional[$unit],
					$unit." - ".getNamaOrg($unit),
					'',
					substr($tanggal,0,7),
					$tahap,
					$tanggal,
					'',
					'',
					'',
					'',
					'',
					$nmorg[$unit],
					"KGWB",
					$kgpks[$unit][$tanggal]
				);
			}
		}
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
	case'detpayroll':
		$wh=$whr="";
		$whr=" and tipekaryawan in ('4')";
		if($param['kodeorg']!=''){
			$whr.=" and lokasitugas='".$param['kodeorg']."'";	
		}else{
			$whr.=" and lokasitugas in (".getOrgDetail(2).")";	
		}
		
		if(strlen($param['periode'])=='4'){
			$tgl1      = $param['periode']."-01-01";
			$tgl2      = tglakhir($param['periode']."-12-31");
			$rangebulan= month_inbetween($param['periode']."-01",$param['periode']."-12");
		}else{			
			$tgl1      = $param['periode']."-01";
			$tgl2      = tglakhir($param['periode']."-01");
			$rangebulan= month_inbetween($param['periode'],$param['periode']);
		}
		$rangetgl= rangeTanggal($tgl1,$tgl2);
		
		foreach($rangebulan as $bulan){
			$thn = substr($bulan,0,4);
			$dttahun[$thn]=$thn;
		}
		foreach($dttahun as $tahun){
			$str = "select * from " . $dbname . ".sdm_5gajipokok where 1=1 and tahun='".$tahun."' and idkomponen='1'";
			$res = fetchData($str);
			foreach ($res as $val) {
				$gaji[$val['karyawanid']][$tahun]=$val['jumlah']/25;
			}
		}
		
		$datae[]=array('PERIODE','TAHAP','TANGGAL','PT','UNIT','TIPE ORG','DIVISI','KARYID','NIK','NAMA KARYAWAN','JABATAN','TIPE KARY','STATUS PAJAK','NPWP','BANK','REKENING','PEMILIK','SISTEM GAJI','TANGGAL MASUK','TANGGAL KELUAR','AGAMA','J/K','DEPT','GOLONGAN','STATUS KARY','NOAKUN','NAMA AKUN','KETERANGAN','NOREFERENSI','ABSENSI','KOMPONEN','JUMLAH');
			
		$numb=array(27);
		
		if($param['jenis']!='data'){
			$data=$datae;
		}
		$row     = array("UNIT","DIVISI");
		$col     = array("KOMPONEN");
		$val     = array('JUMLAH');
		$datasort= array("HK","GAJI POKOK","PREMI");
		
		
		
		
		$nourut = 0;
		foreach($rangebulan as $bulan){
			$sql = "select * from ".$dbname.".datakaryawan where 1=1  ".$whr."";
			$req = fetchdata($sql);
			foreach($req as $bar){
				if($bar['subbagian']==''){
					$bar['subbagian']="UMUM";
				}else{
					$bar['subbagian']=getNamaOrg($bar['subbagian']);
				}
				
				$datakary[$bar['karyawanid']]=$bar['karyawanid'];
				$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
				$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
				$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
			}
		
			
			$sql = "select * from ".$dbname.".datakaryawan_hist where 1=1  and periodegaji = '".$bulan."' and version_type='B' ".$whr."";
			$req = fetchdata($sql);
			foreach($req as $bar){
				if($bar['subbagian']==''){
					$bar['subbagian']="UMUM";
				}else{
					$bar['subbagian']=getNamaOrg($bar['subbagian']);
				}
				
				$datakary[$bar['karyawanid']]=$bar['karyawanid'];
				$dt[$bar['karyawanid']][$bulan]['nama']=$bar['namakaryawan'];
				$dt[$bar['karyawanid']][$bulan]['nik']=$bar['nik'];
				$dt[$bar['karyawanid']][$bulan]['jk']=$bar['jeniskelamin'];
				$dt[$bar['karyawanid']][$bulan]['agama']=$bar['agama'];
				$dt[$bar['karyawanid']][$bulan]['norek']=$bar['norekeningbank'];
				$dt[$bar['karyawanid']][$bulan]['bank']=$bar['namabank'];
				$dt[$bar['karyawanid']][$bulan]['pemilik']=$bar['pemilikrekening'];
				$dt[$bar['karyawanid']][$bulan]['sistemgaji']=$bar['sistemgaji'];
				$dt[$bar['karyawanid']][$bulan]['tanggalmasuk']=$bar['tanggalmasuk'];
				$dt[$bar['karyawanid']][$bulan]['tanggalkeluar']=$bar['tanggalkeluar'];
				$dt[$bar['karyawanid']][$bulan]['tipekaryawan']=$bar['tipekaryawan'];
				$dt[$bar['karyawanid']][$bulan]['statuspajak']=$bar['statuspajak'];
				$dt[$bar['karyawanid']][$bulan]['npwp']=$bar['npwp'];
				$dt[$bar['karyawanid']][$bulan]['pt']=$bar['kodeorganisasi'];
				$dt[$bar['karyawanid']][$bulan]['dept']=$bar['bagian'];
				$dt[$bar['karyawanid']][$bulan]['kodejabatan']=$bar['kodejabatan'];
				$dt[$bar['karyawanid']][$bulan]['kodegolongan']=$bar['kodegolongan'];
				$dt[$bar['karyawanid']][$bulan]['lokasitugas']=$bar['lokasitugas'];
				$dt[$bar['karyawanid']][$bulan]['alokasi']=$bar['alokasi'];
				$dt[$bar['karyawanid']][$bulan]['subbagian']=$bar['subbagian'];
				$dt[$bar['karyawanid']][$bulan]['statuskaryawan']=$bar['statuskaryawan'];
				$dt[$bar['karyawanid']][$bulan]['kppnpwp']=$bar['kppnpwp'];
				$dt[$bar['karyawanid']][$bulan]['bpjstk']=$bar['jms'];
				$dt[$bar['karyawanid']][$bulan]['bpjskes']=$bar['bpjs'];
				$dt[$bar['karyawanid']][$bulan]['pensiun']=$bar['pensiun'];
			}
		}
		
		$opttipe=makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		
		$where="";
		$where.=" and kodeorg like '".$param['kodeorg']."%'";
		$where.=" and tanggal between '".$tgl1."' and '".$tgl2."'";
		$str = "select * from " . $dbname . ".sdm_absensidt where 1=1 ".$where."";
		// exit("error".$str);
		$res = fetchData($str);
		foreach ($res as $bar){
			$bar['kodeorg']=substr($bar['kodeorg'],0,4);
			$bar['divisi']=$bar['kodeorg'];
			
			$whk="";$wh="";$whr="";
			if($opttipe[$bar['kodeorg']]=='KEBUN'){
				if(strlen($bar['divisi'])==6 and $opttipe[$bar['divisi']]=='BIBITAN'){				
					$kdjurnal="KBNL0";
				}elseif(strlen($bar['divisi'])==6 and $opttipe[$bar['divisi']]=='TRAKSI'){				
					$kdjurnal="VHCG0";
				}elseif(strlen($bar['divisi'])==6 and $opttipe[$bar['divisi']]=='WORKSHOP'){				
					$kdjurnal="WSG0";
				}else{				
					$kdjurnal="KBNB0";
				}
			}elseif($opttipe[$bar['kodeorg']]=='PABRIK'){
				if(strlen($bar['divisi'])==6 and $opttipe[$bar['divisi']]=='TRAKSI'){				
					$kdjurnal="VHCG0";
				}elseif(strlen($bar['divisi'])==6 and $opttipe[$bar['divisi']]=='WORKSHOP'){				
					$kdjurnal="WSG0";
				}else{				
					$kdjurnal="PKS01";
				}
			}elseif($opttipe[$bar['kodeorg']]=='BULKING'){
				$kdjurnal="BLK01";
			}elseif($opttipe[$bar['kodeorg']]=='RND' or $opttipe[$bar['kodeorg']]=='TC'){
				$kdjurnal="RNDB0";
			}elseif($opttipe[$bar['kodeorg']]=='HOLDING'){
				$kdjurnal="GJHO0";
				
			}
			
			$optakun=makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
			$akun=$optakun[$kdjurnal];
			
			if($bar['noakun']=='' or is_null($bar['noakun'])){
				$bar['noakun']=$akun;
			}else{
				$bar['noakun']=$bar['noakun'];
			}
			
			if(substr($bar['tanggal'],-2)<=15){
				$tahap='1';
			}else{
				$tahap='2';
			}
			
			$bar['periodegaji'] = substr($bar['tanggal'],0,7);
			if($datakary[$bar['karyawanid']]!=''){	
				$data[]=array(
					$bar['periodegaji'],
					$tahap,
					$bar['tanggal'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pt'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas'],
					$tporg[$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['subbagian'],
					$bar['karyawanid'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nama'],
					$nmjab[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$bar['karyawanid']][$bar['periodegaji']]['tipekaryawan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuspajak'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['npwp'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['bank'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['norek'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pemilik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['sistemgaji'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalmasuk'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalkeluar'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['agama'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['jk'],
					$nmdept[$dt[$bar['karyawanid']][$bar['periodegaji']]['dept']],
					$nmgol[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodegolongan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuskaryawan'],
					substr($bar['noakun'],0,7),
					getNamaAkun(substr($bar['noakun'],0,7)),
					$bar['penjelasan'],
					$bar['norefrensi'],
					$bar['absensi'],
					'Gaji Pokok',
					$bar['umr']-$bar['penaltykehadiran']
				);
				
				$data[]=array(
					$bar['periodegaji'],
					$tahap,
					$bar['tanggal'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pt'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas'],
					$tporg[$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['subbagian'],
					$bar['karyawanid'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nama'],
					$nmjab[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$bar['karyawanid']][$bar['periodegaji']]['tipekaryawan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuspajak'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['npwp'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['bank'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['norek'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pemilik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['sistemgaji'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalmasuk'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalkeluar'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['agama'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['jk'],
					$nmdept[$dt[$bar['karyawanid']][$bar['periodegaji']]['dept']],
					$nmgol[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodegolongan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuskaryawan'],
					substr($bar['noakun'],0,7),
					getNamaAkun(substr($bar['noakun'],0,7)),
					$bar['penjelasan'],
					$bar['norefrensi'],
					$bar['absensi'],
					'Premi',
					$bar['premi']+$bar['insentif']+$bar['insentiflibur']
				);
				
				$data[]=array(
					$bar['periodegaji'],
					$tahap,
					$bar['tanggal'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pt'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas'],
					$tporg[$dt[$bar['karyawanid']][$bar['periodegaji']]['lokasitugas']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['subbagian'],
					$bar['karyawanid'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['nama'],
					$nmjab[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodejabatan']],
					$tipekar[$dt[$bar['karyawanid']][$bar['periodegaji']]['tipekaryawan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuspajak'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['npwp'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['bank'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['norek'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['pemilik'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['sistemgaji'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalmasuk'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['tanggalkeluar'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['agama'],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['jk'],
					$nmdept[$dt[$bar['karyawanid']][$bar['periodegaji']]['dept']],
					$nmgol[$dt[$bar['karyawanid']][$bar['periodegaji']]['kodegolongan']],
					$dt[$bar['karyawanid']][$bar['periodegaji']]['statuskaryawan'],
					substr($bar['noakun'],0,7),
					getNamaAkun(substr($bar['noakun'],0,7)),
					$bar['penjelasan'],
					$bar['norefrensi'],
					$bar['absensi'],
					'HK',
					$bar['hk']
				);
			}
		}
		// echo"<pre>";
		// print_r($data);
		// exit("error");
		
		echo json_encode($data)."####".json_encode($row)."####".
		 json_encode($col)."####".json_encode($val)."####".
		 json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
	break;
}

function cleanSpecialChar($string) {
    // $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes special chars.
    // $string = preg_replace('/-+/',' ',$string);
    // $string = preg_replace('/\s+/', '-', trim($string)); 
    // return $string;
	
	$hasil='';
	$hasil=preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string); //remove non-ascii chars
	return $hasil;
}

// echo"<pre>";
// print_r($data);
// echo"</pre>";
// exit("error");

// if($param['jenis']=='data'){
	// // $tab="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable' width='100%' data-scroll-x='true' scroll-collapse='false'>
		// // <thead>
			// // <tr>";
			// // foreach($datae as $key => $var){
				// // foreach($var as $val){
					// // if($key==0){
						// // $tab.="<th>".$val."</th>";
						// // $jlhcolhead++;
					// // }
				// // }
			// // }
		
		// // $tab.="</tr>
			// // </thead><tbody>";
		// // $tab.="</tbody><tfoot>
			// // <tr>";
			// // foreach($datae as $key => $var){
				// // foreach($var as $val){
					// // if($key==0){					
						// // $tab.="<th>".$val."</th>";
					// // }
				// // }
			// // }
		
	// // $tab.="</tr></tfoot>";	
	// // $tab.="</table>";
	// // $tab.="<fieldset style=float:left;><legend>Show/Hide</legend><div>";
		// // $e=0;
		// // foreach($datae as $key => $var){
			// // foreach($var as $val){
				// // if($key==0){
					// // if($jlhcolhead>8){						
						// // $tab.="<button class=\"dt-button\" data-column=".$e.">".substr($val,0,4)."...</button>";
					// // }else{
						// // $tab.="<button class=\"dt-button\" data-column=".$e.">".$val."</button>";
					// // }
					// // $e++;
				// // }
			// // }
		// // }
	// // $tab.="</div></fieldset>";
	
	// // echo $tab."####".json_encode($data)."####".json_encode($numb);
// }elseif($method=='sumber' or $method=='formfav' or $method=='loadformfav'){
	// echo $tab; 
// }elseif($param['jenis']=='popupkirim'){
	// // $str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['tipe']."' order by id asc";
	// // $res = fetchData($str);
	// // $optlap="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// // foreach($res as $key => $bar){
		// // $optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
		// // $judullap.=$bar['id']."$$$$";
		// // $datalap.=$bar['data']."$$$$";
	// // }
	
	// // $str = "select a.*, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['tipe']."' order by a.id asc";
	// // $res = fetchdata($str);
	// // foreach ($res as $bar){
		// // $optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
		// // $judullap.=$bar['id']."$$$$";
		// // $datalap.=$bar['data']."$$$$";
	// // }
	
	// // echo $optlap."####".($judullap)."####".$datalap;;
	
// }else{
	// $str = "select * from ".$dbname.".pivot_favorit where karyawanid='".$_SESSION['standard']['userid']."' and idmenu='".$idmenu[$file]."' and jenis ='".$param['method']."' order by id asc";
	// $res = fetchData($str);
	// $optlap="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	// foreach($res as $key => $bar){
		// $optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
		// $judullap.=$bar['id']."$$$$";
		// $datalap.=$bar['data']."$$$$";
	// }
	
	// $str = "select a.*, b.label, b.data, a.id as id from ".$dbname.".pivot_favoritdt a left join ".$dbname.".pivot_favorit b on a.id=b.id where a.karyawanid='".$_SESSION['standard']['userid']."' and b.idmenu='".$idmenu[$file]."' and b.jenis ='".$param['method']."' order by a.id asc";
	// $res = fetchdata($str);
	// foreach ($res as $bar){
		// $optlap.="<option value=".$bar['id'].">".$bar['label']."</option>";
		// $judullap.=$bar['id']."$$$$";
		// $datalap.=$bar['data']."$$$$";
	// }
	
	// echo json_encode($data)."####".json_encode($row)."####".
		 // json_encode($col)."####".json_encode($val)."####".
		 // json_encode($datasort)."####".$optlap."####".($judullap)."####".$datalap;
// }

?>

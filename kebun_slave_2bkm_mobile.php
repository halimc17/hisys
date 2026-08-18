<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kodeorg= checkPostGet('kodeorg', '');
$divisi = checkPostGet('divisi', '');
$tgl1   = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2   = tanggalsystemn(checkPostGet('tgl2', ''));
$param  = $_POST;if(count($param)==0){$param= $_GET;}
$path   = "fileupload/bkm/";

switch($method){
	case'preview':
		$tab="<table id=mytable class='sortable' cellspacing=1 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan=2 >" . $_SESSION['lang']['tipetransaksi'] . "</th>
					<th align=center rowspan=2 >No BKM</th>
					<th align=center rowspan=2 >No BKM Mobile</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['unit'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['hari'] . "</th>
					<th align=center rowspan=2 width=100px>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['jhk'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['jjg'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['upah'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['premi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['mandor'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['mandor'] . " 1</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['kerani'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['nikasisten'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center colspan='2'>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				<tr class=rowheader>
					<th style=display:none></th>
					<th style=display:none></th>
				</tr>
			</thead>
		 <tbody>";

		if($kodeorg==""){
			exit("warning : Kode organisasi harus diisi.");
		}
		if($tgl1=="--"){
			exit("warning : Tanggal dari harus diisi.");
		}
		if($tgl1=="--"){
			exit("warning : Tanggal sampai harus diisi.");
		}

		$where="";
		if ($divisi != '') {
			$where.=" and a.divisi like '" . $divisi . "%' ";
		}
		if (($tgl1 != '') and ($tgl1 != '--')) {
			@$where.=" and a.tanggal >='" . $tgl1 . "' ";
			@$whsdm.=" and tanggal >='" . $tgl1 . "' ";
		}
		if (($tgl2 != '') and ($tgl2 != '--')) {
			$where.=" and a.tanggal <='" . $tgl2 . "' ";
			$whsdm.=" and tanggal <='" . $tgl2 . "' ";
		} 
		if ($kodeorg != '') {
			$where.=" and a.kodeorg like '%".$kodeorg."%'";
			$whsdm.=" and substr(kodeorg,1,4)='".$kodeorg."'";
			@$wh3.= "and a.kodeorg='".$kodeorg."'";
		} 

		@$wh3.=" and a.tanggal like '" .substr($tgl2,0,7). "%' ";

		$ttl=array();
		$strn = "select karyawanid, norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm, karyawanid"; 
		$resn = fetchdata($strn);

		foreach ($resn as $bar) {
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){				
				$umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
			}else{				
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['premi'];
			}
			// premi bkm jangan pake $resn[0]['premi']
			$hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
			$premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
		}

		$strx = "select sum(umr) as umr, sum(jhk) as jhk, sum(insentif) as insentif, notransaksi from ".$dbname.".kebun_kehadiran_mobile where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas_mobile a where 1=1 ".$wh3." and a.tipetransaksi not in ('PNN')) group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			@$umr[$bar['notransaksi']]+=$bar['umr'];
			@$hkp[$bar['notransaksi']]+=$bar['jhk'];
			@$premip[$bar['notransaksi']]+=$bar['insentif'];
			@$ttlrp2[$bar['notransaksi']]+=$bar['umr']+$bar['jhk']+$bar['insentif'];
		}

		$strx = "select kodeorg, sum(hasilkerja) as pres,sum(upahkerja) as umr, sum(jumlahhk) as jhk, sum(upahpremi+upahpremilebihbasis+upahpremilebihbasis2+premibasis+premibasis2) as insentif, notransaksi from ".$dbname.".kebun_prestasi_mobile where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas_mobile a where 1=1 ".$wh3." and a.tipetransaksi in ('PNN')) group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			@$umr[$bar['notransaksi']]+=$bar['umr'];
			@$hkp[$bar['notransaksi']]+=$bar['jhk'];
			@$premip[$bar['notransaksi']]+=$bar['insentif'];
			@$ttlrp2[$bar['notransaksi']]+=$bar['umr']+$bar['jhk']+$bar['insentif']+$bar['pres'];
			@$jjgpnn[$bar['notransaksi']]+=$bar['pres'];
			@$divisipres[$bar['notransaksi']]=substr($bar['kodeorg'],0,6);
		}

		$statusblok='BKM';

		$str = "SELECT * FROM " . $dbname . ".kebun_aktifitas_mobile a where 1=1 " . $where . " order by a.nobkm desc, a.notransaksi desc"; 
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if(@$ttlrp2[$bar['notransaksi']]=='0' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
				$cl=" style=background-color:red; title=\"Data detail belum ada.\"";
			}elseif(@$ttlrp2[$bar['notransaksi']]=='0' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
				$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
				$abs="absensi";
			}
			if($bar['nobkm']==''){
				$bar['nobkm']=$bar['noreferensi'];
			}
			if($bar['divisi']==''){
				$bar['divisi']=$divisipres[$bar['notransaksi']];
			}
			
			$hari=$c="";
			$hari = date('D', strtotime($bar['tanggal']));
			if($hari=='Sun'){
				$c="style=\"color:red\"";
			}
			if($hari=='Fri'){
				$c="style=\"color:blue\"";
			}
			
			$a=$a1=$b=$b1=$d=$d1="";
			if(getSubbagian($bar['nikmandor'])!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor'])."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['nikmandor1'])!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor1'])."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['keranimuat'])!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".getSubbagian($bar['keranimuat'])."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			if($bar['tipetransaksi']=='PNN' and $bar['noreferensi']!=""){
				$sumber="Proses Panen";
			}elseif($bar['tipetransaksi']=='PNN'){
				$sumber="BKM Panen";
			}elseif($bar['tipetransaksi']!='PNN'){
				$sumber="BKM Rawat";
			}
			
			
			@$tab.="<tr class=rowcontent ".$xx." ".$cl." id=tr_$no>";
			$tab.="<td align=center>".$sumber."</td>";
			$tab.="<td align=center>" . $bar['nobkm'] . "</td>";
			$tab.="<td align=center>" . $bar['noreferensi'] . "</td>";
			$tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
			$tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
			$tab.="<td align=center>" . $bar['divisi'] . "</td>";
			$tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
			$tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab.="<td align=center>" . @numb_format($hkp[$bar['notransaksi']]+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			$tab.="<td align=center>" . @numb_format($jjgpnn[$bar['notransaksi']]) . "</td>";
			$tab.="<td align=right>" . @numb_format($umr[$bar['notransaksi']]+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
			$tab.="<td align=right>" . @numb_format($premip[$bar['notransaksi']]+$premab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			$tab.="<td align=center ".$a1.">" . getKary($bar['nikmandor']). "".$a."</td>";
			$tab.="<td align=center ".$b1.">" . getKary($bar['nikmandor1']) . "".$b."</td>";
			$tab.="<td align=center ".$d1.">" . getKary($bar['keranimuat']) . "".$d."</td>";
			$tab.="<td align=center>" . getKary($bar['nikasisten']). "</td>";
			$tab.="<td align=center>" . getKary($bar['updateby']). "</td>";
			if($bar['tipetransaksi']!='PNN'){		
				$tab.="<td align=center style=width:20px><img src=images/download.png class=zImgBtn class=zImgBtn height='30'  title='Download' onclick=\"mobiletoerp('".$bar['nobkm']."');\" ></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','BKM','html');\" ></td>";
			}else{
				$tab.="<td align=center style=width:20px><img src=images/download.png class=zImgBtn class=zImgBtn height='30'  title='Download' onclick=\"mobiletoerp('".$bar['nobkm']."');\" ></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','PNN','html');\" ></td>";
			}
			
			
			$tab.="</tr>";
		} 
		echo $tab; 
	break;
	case'loadfiles':
		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center' width=50px>File Type</th>
					<th align='center'>Filename</th>
					<th align='center' width=30px colspan=2>Action</th>
				</tr>
				</thead>
				<tbody>";
				
				$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
				$res= fetchData($str);
				if(empty($res)){
					$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}else{
					foreach($res as $key=>$val){
						$no++;
						$tab.="<tr class=rowcontent>
								<td style='text-align:center'>".$no."</td>";
						$icon=seticonfile($val['formaticon']);
						$tab.="<td style='text-align:center'>
								<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
							</td>";
						$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
						$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
						$tab.="</tr>";
					}
				}
				
			$tab.="</tbody>
			</table>
		";
		echo $tab;
	case 'mobiletoerp':
		echo syncToErp($param['nobkm']);
	break;
	break;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}


function syncToErp($notransaksi){
	global $dbname,$owlPDO;
	$queryAktfe = fetchdata("SELECT * FROM {$dbname}.kebun_aktifitas WHERE notransaksi = '{$notransaksi}'");
	$queryPrstasie = fetchdata("SELECT * FROM {$dbname}.kebun_prestasi WHERE `notransaksi` = '{$notransaksi}'");
	if(count($queryAktfe) > 0 || count($queryPrstasie) > 0){
		exit("Warning, transaksi ini sudah di proses");
	}
	/* no transaksi harus baru ngecek ke utama
		kalo rawat ada premi, kalo panen nggk,
		dapatin premi dulu kalo rawat
		
		*/
	$queryAktf = "SELECT * FROM {$dbname}.kebun_aktifitas_mobile WHERE notransaksi = '{$notransaksi}'";
	$queryPM = "SELECT * FROM {$dbname}.kebun_pakaimaterial_mobile WHERE `notransaksi` = '{$notransaksi}'";
	$queryPrstasi = "SELECT * FROM {$dbname}.kebun_prestasi_mobile WHERE `notransaksi` = '{$notransaksi}'";
	$queryKehadiran = "SELECT a.*,b.karyawanid FROM {$dbname}.kebun_kehadiran_mobile a INNER JOIN datakaryawan b ON a.nik=b.nik WHERE `notransaksi` = '{$notransaksi}'";
	$dataMobileBkm = [
		'aktifitas' => fetchdata($queryAktf)[0],
		'prestasi'	=> fetchdata($queryPrstasi),
		'material'	=> fetchdata($queryPM),
		'kehadiran'	=> fetchdata($queryKehadiran)
	];
	// getupahharian(substr($dataMobileBkm['aktifitas']['tanggal'],0,7),)
	//untuk dapatin collumn kebun_prestasi
	$collumnPrestasi = '';
	$collNot = ['photo','photoakhir','latitudeakhir','logitudeakhir'];
	foreach(fetchdata($queryPrstasi) as $key => $val){
		if(!in_array($key,$collNot)){
			$collumnPrestasi = $collumnPrestasi == '' ? $key : $collumnPrestasi.=",{$key}";
		}
	}
	try{
		$owlPDO->exec("INSERT INTO {$dbname}.kebun_aktifitas ($queryAktf)");
		$owlPDO->exec("INSERT INTO {$dbname}.kebun_pakaimaterial ($queryPM)");
		insertPrestasi($dataMobileBkm['prestasi']);
		$owlPDO->exec("INSERT INTO {$dbname}.kebun_kehadiran ('{$dataMobileBkm['kehadiran']['notransaksi']}','{$dataMobileBkm['kehadiran']['nourut']}','{$dataMobileBkm['kehadiran']['nik']}','{$dataMobileBkm['kehadiran']['absensi']}','{$dataMobileBkm['kehadiran']['jhk']}','{$dataMobileBkm['kehadiran']['umr']}','{$dataMobileBkm['kehadiran']['insentif']}','{$dataMobileBkm['kehadiran']['hasilkerja']}','{$dataMobileBkm['kehadiran']['penalty']}')");
	}catch(PDOException $e){
		exit($e);
	}

}

function insertPrestasi(array $datas){
	global $owlPDO,$dbname;
	try{
		foreach($datas as $data){
			$coll = array();
			unset($data['photo']);
			unset($data['photoakhir']);
			unset($data['latitudeakhir']);
			unset($data['logitudeakhir']);
			foreach($data as $key => $val){
				$coll[] = $key;
			}
			$query = insertQuery($dbname,'kebun_prestasi',$data,$coll);
			$owlPDO->exec($query);
		}
	}catch(PDOException $e){
		exit($e);
	}
}
function getupahharian($periode,$karid){
	global $dbname;
	global $owlPDO;
	$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karid."' and tahun='".$periode."' and idkomponen in ('1')"; #
	$res=fetchdata($str);
	$umrHarian=$res[0]['nilai']/25;
	
	return $umrHarian;
}

function insertKehadiran(array $dataKehadiran){
	global $dbname,$owlPDO;
	try{
		foreach($dataKehadiran as $kehadiran){
			$coll = array();
			$tahun = substr($kehadiran['tanggal'],0,7);
			$umr = getupahharian($tahun,$kehadiran['nik']);
			if($umr == '' || $umr == '0'){
				exit("Warning : Gaji Pokok Karyawan belum ada.");
			}
			$kehadiran['umr'] = $umr;
			foreach($kehadiran as $key => $val){
				$coll[]  = $key;
			}
			$insert = insertQuery($dbname,'kebun_kehadiran',$kehadiran,$coll);
			$owlPDO->exec($insert);
		}
	}catch(PDOException $e){
		exit($e);
	}
}


function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	$thn=substr($tgl,0,4);
	$bln=substr($tgl,4,2);
	
	$periode = $thn."-".$bln;
	
	$tanggal=tanggalsystemn(checkPostGet('tgl', ''));
	
	#============== Validasi SESSION Status ==========+=========
	stsawal($param);
	#============ End Validasi SESSION Status ==================
	
	
	#cek HK perhari maksimal 1
	# Ambil nomor urut kary
	$str = "select * from " . $dbname . ".kebun_prestasi where notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodeorg='".@$param['blok']."' and kodekegiatan='".@$param['kegiatan']."'";
	$res=fetchData($str);
	@$nourut=$res[0]['nourut'];
			
	if($param['method']=='insert'){
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."'";
	} else if($param['method']=='update'){
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."' and nourut!='".$nourut."'";
	}else{
		$str = "select a.notransaksi,jhk from ".$dbname.".kebun_kehadiran a
				left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where nik='".$param['karyawanid']."' and tanggal='".$tgl."'";
	}
	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$jlhhk=0;
		$trans='';
		while($bar=$res->fetch()){ 
			$jlhhk+=$bar['jhk'];
			$trans.="No => ".$bar['notransaksi']." => ".$bar['jhk']." HK<br>";
		}
		
		if(floatval($param['jhk'])+$jlhhk>1){
			throw new PDOException("Jumlah HK karyawan lebih dari 1, HK yang sudah tersimpan sebesar = ".$jlhhk." HK<br><br> ".$trans."");
		}		
		
	#cek mandor
	$jumtrans = '';
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek mandor1
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek kerani
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	#cek nikasisten
	$str = "select count(*) as jumkar from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		
	if(@$jumtrans>0 ){
		throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani");
	}

	# Cek Perawatan
	# Jika sudah ada di perawatan tidak bisa input panen
	# Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_prestasi_vs_hk','karyawanid,sum(hkpanenperhari) as jhk',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jhkpanen = $resAbs[0]['jhk'];
	
	if(floatval($jhkpanen)!='0' and $param['jhk']>'0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan panen, silahkan kosongkan Jumlah HK untuk melanjutkan.");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	
	if(floatval($jmlhkvhc)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan traksi");
	}
	
	#cek di SDM
	if($param['method']=='updateabsensi'){
		$str = "select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and norefrensi!='".$param['notransaksi']."' ";
		$res = fetchData($str);
		if(count($res)>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM dengan nomor transaksi : ".$param['notransaksi'].".");
		}
	}else{
		$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
				"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
		$resAbs = fetchData($qAbs);
		$jmlhksdm = $resAbs[0]['jhk'];
		if(floatval($jmlhksdm)!='0' and $param['jhk']>'0') {
			throw new PDOException("Karyawan sudah terdaftar di absensi SDM.");
		}
	}
	
	
	$str = "select * from " . $dbname . ".setup_blok where kodeorg='".$param['blok']."'"; 
	$res = fetchData($str);
	foreach($res as $val){
		$luasttlblok =$val['luasareaproduktif']+$val['luasareanonproduktif'];
		$pokokttlblok=$val['jumlahpokok'];
	}
	
	$satsetup = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$param['kegiatan']."'");
	
	$hasilkerja=0;$notrhasil="";
	$hasilkerjaedit=0;
	if($param['method']=='update'){
		$str = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_prestasi where kodeorg='".$param['blok']."' and notransaksi='".$param['notransaksi']."' and nikpemel='".$param['karyawanid']."' and kodekegiatan='".$param['kegiatan']."'"; 
		$res = fetchData($str);
		foreach($res as $val){
			$hasilkerjaedit=number_format($val['hasilkerja'],2);
		}
	}
	
	$notrhasil.="\nTransaksi saat ini = ".@number_format($param['prestasi'],2)." ".$satsetup[$param['kegiatan']]."\n";
	$str = "select sum(hasilkerja) as hasilkerja,notransaksi,substr(notransaksi,1,8) from " . $dbname . ".kebun_prestasi where kodeorg='".$param['blok']."' and substr(notransaksi,1,8) between '".seminggulalu($tgl)."' and '".$tgl."' and kodekegiatan='".$param['kegiatan']."'  group by notransaksi  order by notransaksi desc"; 
	$res = fetchData($str);
	foreach($res as $val){
		$hasilkerja+=$val['hasilkerja'];
		if($val['notransaksi']==$param['notransaksi']){
			$notrhasil.=$val['notransaksi']." = ".number_format($val['hasilkerja']-$hasilkerjaedit,2)." ".$satsetup[$param['kegiatan']]."\n";
		}else{			
			$notrhasil.=$val['notransaksi']." = ".number_format($val['hasilkerja'],2)." ".$satsetup[$param['kegiatan']]."\n";
		}
	}

	if($param['kegiatan']!='621010302' and $param['kegiatan']!='126010802'){		
		if(strtolower($satsetup[$param['kegiatan']])=='ha'){
			$a=number_format(($hasilkerja+$param['prestasi'])-$hasilkerjaedit,2);
			$a=str_replace(",","",$a);
			$b=number_format($luasttlblok,2);
			$b=str_replace(",","",$b);
			if($a>$b){
				throw new PDOException("Luas dikerjakan sudah melebihi luas blok,<br>Luas blok : ".$b." HA<br>Luas dikerjakan : ".$a." HA<br>".$notrhasil."");
			}
		}elseif(strtolower($satsetup[$param['kegiatan']])=='pokok' or strtolower($satsetup[$param['kegiatan']])=='pkk' and substr($param['kegiatan'],0,3) !='126'){
			$a=number_format(($hasilkerja+$param['prestasi'])-$hasilkerjaedit,2);
			$a=str_replace(",","",$a);
			$b=number_format($pokokttlblok,2);
			$b=str_replace(",","",$b);
			if($a>$b){
				throw new PDOException("Pokok dikerjakan sudah melebihi jumlah pokok blok,<br>Pokok blok : ".$b." PKK<br>Pokok dikerjakan : ".$a." PKK<br>".$notrhasil."");
			}
		}
	}
}
?>
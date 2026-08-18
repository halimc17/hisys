<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method             = checkPostGet('method', '');
$snotrans           = checkPostGet('snotrans', '');
$notransaksi        = checkPostGet('notransaksi', '');
$nospk		        = checkPostGet('nospk', '');

$tipe               = checkPostGet('tipe', '');
$jenis              = checkPostGet('jenis', '');
$rupiah             = checkPostGet('rupiah', '');
$rupiah             = str_replace(',','',$rupiah);
$keterangan         = checkPostGet('keterangan', '');
$nourut             = checkPostGet('nourut', '');
$row                = checkPostGet('row', '');
$pt                 = checkPostGet('pt', '');
$unit               = checkPostGet('unit', '');
$kategori           = checkPostGet('kategori', '');
$tanggalsurat       = tanggalsystemn(checkPostGet('tanggalsurat', ''));
$tanggaldari        = tanggalsystemn(checkPostGet('tanggaldari', ''));
$tanggalsampai      = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$tanggal            = tanggalsystemn(checkPostGet('tanggal', ''));
$divisi             = checkPostGet('divisi', '');
$bagian             = checkPostGet('bagian', '');
$project            = checkPostGet('project', '');
$koderekanan        = checkPostGet('koderekanan', '');
$perjanjianinduk    = checkPostGet('perjanjianinduk', '');
$perjanjianperubahan= checkPostGet('perjanjianperubahan', '');
$retensi            = checkPostGet('retensi', '');
$denda              = checkPostGet('denda', '');
$jangkawaktu        = checkPostGet('jangkawaktu', '');
$garansi            = checkPostGet('garansi', '');
$namafile           = checkPostGet('namafile', '');
$divsch             = checkPostGet('divsch', '');
$jenissch           = checkPostGet('jenissch', '');
$nohaksch           = checkPostGet('nohaksch', '');
$unitsch            = checkPostGet('unitsch', '');
$projectsch         = checkPostGet('projectsch', '');
$subunit            = checkPostGet('subunit', '');
$kegiatan           = checkPostGet('kegiatan', '');
$total              = checkPostGet('total', '');
$volume             = checkPostGet('volume', '');
$satuan             = checkPostGet('satuan', '');
$kepada             = checkPostGet('kepada', '');
$numrow             = checkPostGet('numrow', '');
$volume             = str_replace(',','',$volume);
$total              = str_replace(',','',$total);

$path               = "fileupload/lgl_pengajuanspk/";
$today              = date('Y-m-d');
$todayhis           = date('Y-m-d H:i:s');
$spesifikasi        = trim(checkPostGet('spesifikasi', ''));
$spesifikasi        = replaceEnter($spesifikasi);
$dir='fileupload/lgl_pengajuanspk';

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmakun= makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

switch ($method) {
	case'carinopengajuan':
		$tab="";
		$tab.="<fieldset>
			<legend>Result</legend>
			<div style=\"overflow:auto; max-height:300px;\">
			<table class=sortable cellspacing=1 cellpadding=2  border=0>
				<thead>
				<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>".$_SESSION['lang']['perusahaan']."</td>
					<td align=center>".$_SESSION['lang']['unit']."</td>
					<td align=center>".$_SESSION['lang']['nomor']."</td>
					<td align=center>".$_SESSION['lang']['tanggal']."</td>
					<td align=center>".$_SESSION['lang']['koderekanan']."</td>
					<td align=center>".$_SESSION['lang']['project']."</td>
				</tr>
				</thead>
				<tbody>";
			
			$txt = checkPostGet('txtfind', '');
			$wh="";
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
				$wh=" and unit ='".$_SESSION['empl']['lokasitugas']."'";
			}
			
			$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi like '%".$txt."%' and posting='1' and statuspersetujuan in ('1') and notransaksi not in (select nopengajuan from ".$dbname.".log_spkht) and pendukung=0 and close=0 ".$wh."";
			$res=fetchData($str);
			if(count($res)<=0){
				$tab.="<tr>
					<td colspan=8 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
				</tr>";
			}else{
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					
					$optPt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['pt']."'");
					$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['unit']."'");
					$optRekanan = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['koderekanan']."'");
					
					$tab.="<tr class=rowcontent style='cursor:pointer' onclick=\"setnopengajuan('".$val['notransaksi']."')\">
						<td style='text-align:right'>".$no."</td>
						<td>".$optPt[$val['pt']]."</td>
						<td>".$optUnit[$val['unit']]."</td>
						<td>".$val['notransaksi']."</td>
						<td>".tanggalnormal($val['tanggal'])."</td>
						<td>".$optRekanan[$val['koderekanan']]."</td>
						<td>".$val['project']."</td>
					</tr>";
				}
			}
			
		$tab.="</tbody>
		</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'setnopengajuan':
		$tab="";
		
		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$jenis = $res[0]['jenis'];
		
		$optPt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res[0]['pt']."'");
		$optUnit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res[0]['unit']."'");
		$optBagian=makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$res[0]['bagian']."'");
		$optRekanan=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$res[0]['koderekanan']."'");
		
		if($jenis=='PROJECT'){
			$optDivisi=makeOption($dbname,'project','kode,nama',"kode='".$res[0]['divisi']."'");
		}else{
			$optDivisi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res[0]['divisi']."'");
		}
		
		$tab.="<div>
		<table>
			<tr>
				<td style='vertical-align:top'>
					<table cellpadding=3 cellspacing=1 border=0 class=sortable>
						<tr class=rowcontent>
							<td>Nomor</td> 
							<td>:</td>
							<td>".$notransaksi."</td>
						</tr>
						<tr class=rowcontent>
							<td>Kategori</td> 
							<td>:</td>
							<td>".$res[0]['kategori']."</td>
						</tr>
						<tr class=rowcontent>
							<td>Jenis</td> 
							<td>:</td>
							<td>".$res[0]['jenis']."</td>
						</tr>
						<tr class=rowcontent>
							<td>Tanggal</td> 
							<td>:</td>
							<td>".tanggalnormal($res[0]['tanggal'])."</td>
						</tr>
						<tr class=rowcontent>
							<td>".$_SESSION['lang']['pt']."</td> 
							<td>:</td>
							<td >".$optPt[$res[0]['pt']]."</td>
						</tr>
						<tr class=rowcontent>
							<td>".$_SESSION['lang']['unit']."</td> 
							<td>:</td>
							<td >".$optUnit[$res[0]['unit']]."</td>
						</tr><tr class=rowcontent>
							<td>".$_SESSION['lang']['divisi']."</td> 
							<td>:</td>
							<td >".$optDivisi[$res[0]['divisi']]."</td>
						</tr>
						<tr class=rowcontent>
							<td>".$_SESSION['lang']['bagian']."</td> 
							<td>:</td>
							<td >".$optBagian[$res[0]['bagian']]."</td>
						</tr>
						<tr class=rowcontent>
							<td>".$_SESSION['lang']['project']."</td> 
							<td>:</td>
							<td >".$res[0]['project']."</td>
						</tr>
						<tr class=rowcontent>
							<td>".$_SESSION['lang']['koderekanan']."</td> 
							<td>:</td>
							<td >".$optRekanan[$res[0]['koderekanan']]."</td>
						</tr>
						<tr class=rowcontent>
							<td>Perjanjian Induk</td> 
							<td>:</td>
							<td >".$res[0]['perjanjianinduk']."</td>
						</tr>
						<tr class=rowcontent>
							<td>Perjanjian Perubahan</td> 
							<td>:</td>
							<td >".$res[0]['perjanjianperubahan']."</td>
						</tr>
						<tr class=rowcontent>
							<td>Retensi (%)</td> 
							<td>:</td>
							<td >".(isset($res[0]['retensi'])?@number_format($res[0]['retensi'],2):'0')."</td>
						</tr>
						<tr class=rowcontent>
							<td>Denda</td> 
							<td>:</td>
							<td >".(isset($res[0]['denda'])?@number_format($res[0]['denda'],2):'0')."</td>
						</tr>
						<tr class=rowcontent>
							<td>Tanggal dari</td> 
							<td>:</td>
							<td >".tanggalnormal($res[0]['tanggaldari'])." s/d ".tanggalnormal($res[0]['tanggalsampai'])."</td>
						</tr>
						<tr class=rowcontent>
							<td>Jangka Waktu</td> 
							<td>:</td>
							<td >".$res[0]['jangkawaktu']."</td>
						</tr>
						<tr class=rowcontent>
							<td>Garansi</td> 
							<td>:</td>
							<td >".$res[0]['garansi']."</td>
						</tr>";
						
						$strx="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."'";
						$resx=fetchData($strx);
						$tab.="<tr class=rowcontent>
							<td style='vertical-align:top'>Nilai</td> 
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>";
							$tab.="<table  width=100%>";
							foreach($resx as $key => $val){
								if($val['tipe']=='rupiah'){
									$tab.="<tr>";
									$tab.="<td>".$val['nourut']."</td>";
									$tab.="<td> = </td>";
									$tab.="<td align=right>".hidezerodecimal($val['nilai'])."</td>";
									$tab.="</tr>";	
								}
							}
							$tab.="</table>";							
						$tab.="</td>
						</tr>
						<tr class=rowcontent>
							<td style='vertical-align:top'>Pajak (%)</td> 
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>";
							$tab.="<table width=100%>";
							foreach($resx as $key => $val){
								if($val['tipe']=='pajak'){
									$tab.="<tr>";
									$tab.="<td>".$val['nourut']." ".$nmakun[$val['nourut']]."</td>";
									$tab.="<td> = </td>";
									$tab.="<td align=right>".hidezerodecimal($val['nilai'])." %</td>";
									$tab.="</tr>";	
								}
							}
							$tab.="</table>";
						$tab.="</td>
						</tr>
						<tr class=rowcontent>
							<td style='vertical-align:top'>Termin (%)</td> 
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>";
						$tab.="<table width=100%>";
						foreach($resx as $key => $val){
							if($val['tipe']=='termin'){
								$tab.="<tr>";
								$tab.="<td>Termin ke ".$val['nourut']."</td>";
								$tab.="<td> = </td>";
								$tab.="<td align=right>".hidezerodecimal($val['nilai'])." %</td>";
								$tab.="</tr>";	
							}
						}
						$tab.="</table>";		
						$tab.="</td>
						</tr>
						<tr class=rowcontent>
							<td style='vertical-align:top'>Spesifikasi Pekerjaan</td> 
							<td style='vertical-align:top'>:</td>
							<td style='vertical-align:top'>".str_replace('####','<br>',$res[0]['spesifikasi'])."</td>
						</tr>
					</tr>
					</table>
				</td>
				<td style='vertical-align:top;padding-left:20px'>
					<table class='sortable' cellspacing='1' border='0' style=min-width:100%>
						<thead>
						<tr class=rowheader>
							<td align='center' width=50px>No.</td>
							<td align='center' width=50px>File Type</td>
							<td align='center'>Filename</td>
							<td align='center' width=50px colspan=2>Action</td>
						</tr>
						</thead>
						<tbody>";
					
					$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and status='1'";
					$res=fetchData($str);
					if(empty($res)){
						$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
					}
					else{
						foreach($res as $key=>$val){
							$no++;
							$tab.="<tr class=rowcontent>
									<td style='text-align:center'>".$no."</td>";
							$icon=seticonfile($val['formaticon']);
							$tab.="<td style='text-align:center'>
									<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
								</td>";
							$nfile='';
							if(strlen($val['namafile'])>10){
								$nfile = potongtext($val['namafile'],10).$val['formaticon'];
							}else{
								$nfile = $val['namafile'];
							}
							$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
								   <td align=center><img src=images/upload-2-xxl.png class=resicon  title='Upload' onclick=\"uploaddata('".$notransaksi."');\"></td>
								   <td align=center><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a></td>";
							$tab."</tr>";
						}
					}
						
					$tab.="</tbody>
					</table>
					<table class='sortable' cellspacing='1' border='0' style=min-width:100%>
						<tr class=rowcontent>
							<td>&nbsp;</td>
						</tr>
					</table>
					<table class='sortable' cellspacing='1' border='0' style=min-width:100%>
						<thead>
						<tr class=rowheader>
							<td align='center'>No.</td>
							<td align='center'>".$_SESSION['lang']['subunit']."</td>
							<td align='center'>".$_SESSION['lang']['kegiatan']."</td>
							<td align='center'>".$_SESSION['lang']['satuan']."</td>
							<td align='center'>".$_SESSION['lang']['volume']."</td>
							<td align='center'>".$_SESSION['lang']['hk']."</td>
							<td align='center'>Rp / Sat</td>
							<td align='center'>".$_SESSION['lang']['total']."</td>
						</tr>
						</thead>
						<tbody>";
						
						$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
						// echo $str;
						$res=fetchData($str);
						$no=0;
						foreach($res as $key=>$val){
							$no++;
							
							$hargasatuan = $val['total'] / $val['volume'];
							if($jenis=='PROJECT'){
								$optCapex = makeOption($dbname,'project','kode,kodecapex',"kode='".$val['subunit']."'");
								$kodecapex = $optCapex[$val['subunit']];
								if($kodecapex==''){
									$optKegiatan = makeOption($dbname,'project_dt','kegiatan,namakegiatan',"kegiatan='".addZero($val['kegiatan'],8)."'");
								}else{
									$optKegiatan = makeOption($dbname,'spl_capexbangunandt','kegiatan,namakegiatan',"kegiatan='".addZero($val['kegiatan'],8)."'");
								}
							}else{
								$optKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$val['kegiatan']."'");
							}
							
							$tab.="<tr class=rowcontent>
								<td>".$no."</td> 
								<td>".$val['subunit']."</td>
								<td>".$optKegiatan[$val['kegiatan']]."</td>
								<td style='text-align:center'>".$val['satuan']."</td>
								<td style='text-align:right'>".number_format($val['volume'],2)."</td>
								<td style='text-align:right'>".$val['hk']."</td>
								<td style='text-align:right'>".number_format($hargasatuan)."</td>
								<td style='text-align:right'>".number_format($val['total'])."</td>
							</tr>";
						}
						
					$tab.="</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan=2 style='text-align:center'>
					<button class='mybutton' onclick='savedetail()'>" . $_SESSION['lang']['save'] . "</button>
					<button class='mybutton' onclick='cleardetail()'>" . $_SESSION['lang']['cancel'] . "</button>
				</td>
			</tr>
		</table>
		</div>";
		
		echo $tab;
	break;

	case 'uploaddata':
        
        echo "
        <fieldset style=float:left>
            <legend>".$_SESSION['lang']['uploaddata']."</legend>
            <table>
                <tr>
                    <td><input name=fileupload1 type=file id=fileupload1 size=1 class=mybutton style=width:160px>
                    </td>
                    <td>
                        <button class=mybutton onclick=simpanupload('".$notransaksi."')>".$_SESSION['lang']['save']."</button>
                    </td>
                </tr>
            </table>
        </fieldset><br><br><br><br>";

    break;

    case 'simpanupload':

        $fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
        $fileupload = $fileupload;
        
        $filesize=$_FILES['fileup']['size'];

        if($filesize>= 512000)
        {
            exit("Warning : Besar ukuran file maksimal 512 KB. ");
        }
        $path = $dir."/".basename($_FILES['fileup']['name']);
        if(move_uploaded_file($_FILES['fileup']['tmp_name'], $path)){ 

            if ($tipe==1) {
                $set="filecek";
            }
            if ($tipe==2) {
                $set="filecekvoid";
            }
            
            $exektensi=explode('.',$_FILES['fileup']['name']);
    		$ektensi=".".$exektensi[1];
            $createTime=date("Y-m-d H:i:s");
            $str="insert into ".$dbname.".listfile_lgl_pengajuanspk (notransaksi,namafile,formaticon,status,createdby,createdtime)
                values ('".$notransaksi."','".basename($_FILES['fileup']['name'])."','".$ektensi."','1','".$_SESSION['standard']['userid']."','".$createTime."')";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }   
        
        }
        echo $_SESSION['lang']['datatersimpan'];

    break;
	
	case'savedetail':
		if($nospk==''){
			exit('Gagal, No. SPK harus diisi.');
		}
		
		$str="select * from ".$dbname.".log_spkht where notransaksi='".$nospk."'";
		$res=fetchData($str);
		if(count($res) > 0){
			exit("Gagal, No. SPK sudah terdaftar di sistem. Ganti dengan No. SPK yang lain.");
		}
		
		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$kodeorg = $res[0]['unit'];
		$jenis = $res[0]['jenis'];
		$tanggal = date('Y-m-d');
		
		#ANGKUTTBS detailnya nanti diisi pada saat proses di menu Rekap Angkutan TBS
		$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$resx=fetchData($str);
		if(count($resx)==0 and $jenis!='ANGKUTTBS'){
			exit("Warning : Detail Kegiatan Pengajuan SPK Tidak Ada !");
		}
		
		
		
		if(substr($res[0]['divisi'],0,3)=='AK-'){
			$divisi = 'PROJECT';
		}else{
			$divisi = $res[0]['divisi'];
		}
		$koderekanan = $res[0]['koderekanan'];
		$keterangan = $res[0]['project'];
		$dari = $res[0]['tanggaldari'];
		$sampai = $res[0]['tanggalsampai'];
		
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$nilaikontrak = 0;
		$arrpajak = array();
		foreach($res as $key => $val){
			if($val['tipe']=='rupiah'){
				$nilaikontrak = $nilaikontrak + $val['nilai'];
			}
			if($val['tipe']=='pajak'){
				$arrpajak[$val['nourut']] = $val['nilai']; 
			}
		}
		
		### HEADER ###
		$str="insert into ".$dbname.".log_spkht (kodeorg,notransaksi,tanggal,divisi,koderekanan,posting,nilaikontrak,keterangan,dari,sampai,matauang,nopengajuan) values ('".$kodeorg."','".$nospk."','".$tanggal."','".$divisi."','".$koderekanan."','0','".$nilaikontrak."','".$keterangan."','".$dari."','".$sampai."','IDR','".$notransaksi."')";
		
		try {
			$owlPDO->exec($str);
			
			### TAX ###
			foreach($arrpajak as $key=>$val){
				$nilaipajak = ($val * $nilaikontrak)/100;
				$str="insert into ".$dbname.".log_spk_tax (kodeorg,notransaksi,noakun,nilai) values ('".$kodeorg."','".$nospk."','".$key."','".$nilaipajak."')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}
			
			### DETAIL ###
			$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
			$res=fetchData($str);
			foreach($res as $key=>$val){
				$str="insert into ".$dbname.".log_spkdt (notransaksi,kodeblok,kodekegiatan,hk,hasilkerjajumlah,satuan,jumlahrp,rupiahpersatuan) values ('".$nospk."','".$val['subunit']."','".$val['kegiatan']."','".$val['hk']."','".$val['volume']."','".$val['satuan']."','".$val['total']."','".($val['total']/$val['volume'])."')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			}
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";die();
		}
		// exit("error : test");
	break;
	
	case'loaddata':
	$where = "";
	
	$lokasitugas = $_SESSION['empl']['tipelokasitugas'];
	if ($lokasitugas == 'HOLDING') {
		$where = " and length(kodeorg)=4";
	} else if ($lokasitugas == 'TRAKSI' or
		$_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
		$where = " and length(kodeorg)=4 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$kdOrganisasi."')";
	} else {
		$where = " and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
	}
	
	if($snotrans!=''){
		$where.=" and notransaksi like '%".$snotrans."%' ";
	}
	
	$limit = 20;
	$page = 0;
	$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
	if (isset($_POST['page'])) {
		$page = $_POST['page'];
		if ($page < 0)
		$page = 0;
	}
	$offset = $page * $limit;
	$maxdisplay = ($page * $limit);
	
	$str = "select * from ".$dbname.".log_spkht where 1=1 ".$where." order by tanggal desc";
	$res = fetchData($str);
	$jlhbrs = count($res);
	$no = 0;
	
	$str = "select * from ".$dbname.".log_spkht where 1=1 ".$where." order by tanggal desc limit ".$offset.",".$limit."";
	$tab = "";
	$no = $maxdisplay;
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=13 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		while ($bar = $res->fetch()) {
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				//$xx.=" style=background-color:#F5EEF8 ";
			}
			
			###
			$valtipe = ($bar['divisi']==""?"Project":($bar['divisi']=="S"?"Perumahan":$bar['divisi']));
			$optRkn = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
			
			$realisasi=0;
			$strx="select sum(jumlahrealisasi) as jumlahrealisasi from ".$dbname.".log_baspk where notransaksi='".$bar['notransaksi']."' and statusjurnal='1'";
			$resx=fetchData($strx);
			$realisasi= $resx[0]['jumlahrealisasi']; 
			
			$bapp=0;
			$strx="select count(*) as jumlah from ".$dbname.".log_baspk where notransaksi='".$bar['notransaksi']."'";
			#and statusjurnal='1'";
			$resx=fetchData($strx);
			$bapp=$resx[0]['jumlah'];
			###
			
			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center id=kodeorg_$no>".$bar['kodeorg']."</td>";
			$tab.="<td align=left id=notransaksi_$no>".$bar['notransaksi']."</td>";
			$tab.="<td align=left>".$bar['nopengajuan']."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=center>".$valtipe."</td>";
			$tab.="<td align=left>".$optRkn[$bar['koderekanan']]."</td>";
			$tab.="<td align=right>".number_format($bar['nilaikontrak'])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['dari'])."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['sampai'])."</td>";
			$tab.="<td align=right>".number_format($realisasi)."</td>";
			
			if($bar['posting']=='2'){
				$tab.="<td align=center>CLOSED</td>";
				$tab.="<td></td><td></td>";
			}else{
				$tab.="<td align=center>".($bapp>0?"BAPP":"")."</td>";
				if($bapp > 0){
					$tab.="<td></td>";
					$tab.="<td align=center><img class=resicon src=images/icons/book_previous.png onclick=\"closespk('".$bar['notransaksi']."');\" title='Closed'></td>";
				}else{
					$tab.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"deletedata('".$bar['notransaksi']."');\" title='Delete'></td>";
				
					$tab.="<td align=center><img class=resicon src=images/icons/book_previous.png onclick=\"closespk('".$bar['notransaksi']."');\" title='Closed'></td>";
				}
			}
					
			$tab.="<td align=center><img src='images/skyblue/pdf.jpg' class='zImgBtn' onclick=\"detailPDF('".$bar['notransaksi']."',event)\" title='Print Data Detail'></td>";
			
			$tab.="<td align=center><img src='images/download.png' class='zImgBtn' onclick=\"showfile('".$bar['notransaksi']."',event)\" title='List File'></td>";
			$tab.="</tr>";
		}
	}
	
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0)
	{
		$totrows=1;
	}
	
	$isiRow='';
	for($er=1;$er<=$totrows;$er++)
	{
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	
	$frompage = (($page*$limit)+1);
	if((($page+1)*$limit) > $jlhbrs)
	{
		$topage = $jlhbrs;
	}
	else
	{
		$topage = (($page+1)*$limit);
	}
	$tab.="</tr>
	<tr>
		<td colspan=15 align=center>
			".$frompage." to ".$topage." Of ".  $jlhbrs."
		</td>
	</tr>
	<tr>
		<td colspan=15 align=center>";
	
	if($page=='0')
	{
		$tab.="";
	}
	else
	{
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
	}
	
	$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
	
	if(($page+1) == $totrows)
	{
		$tab.="";
	}
	else
	{
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
	}
	$tab.="</td></tr>";
	
	echo $tab;
	break;
	
	case 'delete':
        $str="delete from ".$dbname.".log_spkht where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str); 
			
			$str="delete from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."'";
			#exit("error".$str);
			
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				exit;
			}
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			exit;
		}   
	break;
	
	case 'closespk':
        $str="update ".$dbname.".log_spkht set posting='2' where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			exit;
		}   
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case'jumlahhari':
	$date1 = $tanggaldari;
	$date2 = $tanggalsampai;
	$a = datediff($date1, $date2);
	echo @$a[years]." tahun, ".@$a[months]." bulan, ".@$a[days]." hari";
	break;
	case'getnotransaksi':
	#001/EXT/LGL/BOD/BJHO/IX/2017
	$tempPrd=explode('-',$tanggalsurat);
	$str=" select notransaksi from ".$dbname.".lgl_pengajuanspkht where pt='".$pt."' and unit='".$unit."' and tanggal like '".$tempPrd[0]."%' order by notransaksi desc limit 1 "; //exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$tempNo1=explode('/',$bar['notransaksi']);
	if(intval($bar['notransaksi'])==0 or intval($bar['notransaksi'])==999){
		$nomorsurat = "001";
	}else{
		$nomorsurat = addZero(intval($tempNo1[0])+1,3);
	}
	echo $nomorsurat."/EXT/LGL/".$pt."/".$unit."/".romawi($tempPrd[1])."/".$tempPrd[0];
	break;
	case'getunit':
	$where = $whp = '';
	if ($jenis != '') {
		if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] != 'KANWIL') {
			if ($jenis == 'PR' or $jenis == 'PROJECT') {
				$where.=" and tipe = '".$_SESSION['empl']['tipelokasitugas']."'";
			} else {
				$where.=" and tipe ='".$jenis."'";
			}
		} else {
			if ($jenis == 'PR' or $jenis == 'PROJECT') {
				$where.=" and tipe in ('PABRIK','KEBUN','KANWIL','HOLDING')";
			} else {
				$where.=" and tipe ='".$jenis."'";
			}
		}
	}
	$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc "; //exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optun;
	break;
	case'getdivisi':
	$optdiv="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PR'){
		$str="select * from ".$dbname.".log_prapo_vw where kodeorg='".$pt."' and nopp like '%".$unit."%' and close='2' and nopp not in (select distinct(nopp) from log_podt) order by nopp asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['nopp'].">".$bar['nopp']."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project where kodeorg='".$unit."' and posting='0' order by kode asc"; //exit('error'.$str);
		$count=fetchData($str);
		if(count($count)<=0){
			exit('Warning : Silahkan buat project terlebih dahulu !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
		}
	}else{
		$wh='';
		if($jenis=='KEBUN'){
			$wh.=" and tipe ='AFDELING' and induk='".$unit."'";
		}else if($jenis=='PABRIK'){
			$wh.=" and tipe ='STATION' and induk='".$unit."'";
		}else{
			$wh.=" and kodeorganisasi='".$unit."'";
		}
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." order by namaorganisasi asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
	}
	echo $optdiv;
	break;
	case'getunit':
	$where = $whp = '';
	if ($jenis != '') {
		if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] != 'KANWIL') {
			if ($jenis == 'PR' or $jenis == 'PROJECT') {
				$where.=" and tipe = '".$_SESSION['empl']['tipelokasitugas']."'";
			} else {
				$where.=" and tipe ='".$jenis."'";
			}
		} else {
			if ($jenis == 'PR' or $jenis == 'PROJECT') {
				$where.=" and tipe in ('PABRIK','KEBUN','KANWIL','HOLDING')";
			} else {
				$where.=" and tipe ='".$jenis."'";
			}
		}
	}
	$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc "; //exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$optun.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optun;
	break;
	case'getsubunit':
	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$optkeg="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PR'){
		$str="select * from ".$dbname.".log_prapo_vw where nopp='".$divisi."' order by nopp asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$opt.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".$nmbrg[$bar['kodebarang']]."</option>";
			$optkeg.="<option value=".$bar['keterangan'].">".$bar['keterangan']."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project_dt where kodeproject='".$divisi."'"; //exit('error'.$str);
		$count=fetchData($str);
		if(count($count)<=0){
			exit('Warning : Tidak ada detail project !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkeg.="<option value=".$bar['kegiatan'].">".$bar['namakegiatan']."</option>";
		}
		$nmpro=makeOption($dbname,'project','kode,nama',"kode='".$divisi."'");
		$opt.="<option value=".$divisi.">".$nmpro[$divisi]."</option>";
	}else{
		$wh='';
		if($jenis=='KEBUN'){
			$wh.=" and induk='".$divisi."'";
		}else if($jenis=='PABRIK'){
			$wh.=" and induk='".$divisi."'";
		}else{
			$wh.=" and kodeorganisasi='".$divisi."'";
		}
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." order by namaorganisasi asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$opt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		$whr='';
		if($jenis=='KEBUN'){
			$whr.=" and kelompok in ('BBT','PNN','TB','TBM','TM')";
		}else{
			$whr.=" and kelompok in ('MIL')";
		}
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
		}
	}
	echo $opt."####".$optkeg;
	break;
	
	case'getsatuan':
	$opt="";
	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PR'){
		$opt=makeOption($dbname,'log_5masterbarang','satuan,satuan',"kodebarang='".$subunit."'"); 
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";			
		}
	}elseif($jenis=='PROJECT'){
		$opt=makeOption($dbname,'project_dt','satuan,satuan',"kegiatan='".$kegiatan."'"); 
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";			
		}
	}else{
		$whr='';
		$whr.=" and kodekegiatan ='".$kegiatan."'";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
		}
	}
	echo $optsat;
	break;
	
	
	
	case'html':
	$tab= "<img src=images/excel.jpg class=resicon	title='Excel' onclick=\"viewexcel('".$notransaksi."','excel');\">";
	
	$countApprove = getCountApproval('SPK',$unit);
	$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
	$str=" select * from ".$dbname.".lgl_pengajuanspkht where  notransaksi='".$notransaksi."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	
	$tab.= "
		<table border=0 cellspacing=1 class=sortable width=100%>
		<thead>
		<tr style='font-weight:bold'>
			<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
			}				
	$tab.= "
		</tr>
		</thead>
		<tbody>";
		$tab.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".$bar['updatetime']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				$arrApp = detailApprove($i,$notransaksi,'SPK');
				
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=tanggalnormal($arrApp['tanggal']);
				}
				
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$tab.= "<td>".$arrApp['nama']."
						<br />".$arrHsl[$arrApp['status']]."
						<br>".$tngl."
					</td>";
				}else{
					$tab.= "<td>&nbsp;</td>";
				}
			}
			
		
		$tab.= "</tbody>
		</table><hr>";
			
	
	$no = 0;
	$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$rp='';
	$strx = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkdt	 where notransaksi='".$notransaksi."'";
	$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_ASSOC);
	$groupArr = array();
	while($barx = $resx->fetch()){
		$d['nourut']= $barx['nourut'];
		$d['tipe']  = $barx['tipe'];
		$d['nilai'] = $barx['nilai'];
		$groupArr[] = $d;
	}
	if($tipe=='html'){
		$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	} else{
		$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
	}
	$tab.= "<tr class=rowcontent>
			<td>Nomor</td> 
			<td>:</td>
			<td colspan=4>".$notransaksi."</td>
			<td>Kategori</td> 
			<td>:</td>
			<td>".$bar['kategori']."</td>
			<td>Jenis</td> 
			<td>:</td>
			<td>".$bar['jenis']."</td>
			<td>Tanggal</td> 
			<td>:</td>
			<td  colspan=4>".$bar['tanggal']."</td>
		</tr>
		<tr class=rowcontent>
			<td>" . $_SESSION['lang']['pt'] . "</td> 
			<td>:</td>
			<td colspan=4>".$bar['pt']."</td>
			<td>" . $_SESSION['lang']['unit'] . "</td> 
			<td>:</td>
			<td colspan=4>".$bar['unit']."</td>
			<td>" . $_SESSION['lang']['divisi'] . "</td> 
			<td>:</td>
			<td colspan=4>".$bar['divisi']."</td>
		</tr>
		<tr class=rowcontent>
			<td>" . $_SESSION['lang']['bagian'] . "</td> 
			<td>:</td>
			<td colspan=4>".$bar['bagian']."</td>
			<td>" . $_SESSION['lang']['project'] . "</td> 
			<td>:</td>
			<td colspan=4>".$bar['project']."</td>
			<td>" . $_SESSION['lang']['koderekanan'] . "</td> 
			<td>:</td>
			<td  colspan=4>".$bar['koderekanan']."</td>
		</tr>
		<tr class=rowcontent>	
			<td>Perjanjian Induk</td> 
			<td>:</td>
			<td colspan=4>".$bar['perjanjianinduk']."</td>
			<td>Perjanjian Perubahan</td> 
			<td>:</td>
			<td colspan=4>".$bar['perjanjianperubahan']."</td>
			<td>Retensi (%)</td> 
			<td>:</td>
			<td>".$bar['retensi']."</td>
			<td>Denda</td> 
			<td>:</td>
			<td>".$bar['denda']."</td>
		</tr>
		<tr class=rowcontent>
			<td>Tanggal dari</td> 
			<td>:</td>
			<td colspan=4>".$bar['tanggaldari']." s/d ".$bar['tanggalsampai']."</td>
			<td>Jangka Waktu</td> 
			<td>:</td>
			<td colspan=4>".$bar['jangkawaktu']."</td>
			<td>Garansi</td>
			<td>:</td>
			<td colspan=4>".$bar['garansi']."</td>
		</tr>
		<tr class=rowcontent>
			<td valign=top>Nilai</td>
			<td valign=top>:</td>
			<td colspan=4 valign=top>";
		$tab.="<table  width=100%>";
		foreach($groupArr as $key => $val){
			if($val['tipe']=='rupiah'){
				$tab.="<tr>";
				$tab.="<td>".$val['nourut']."</td>";
				$tab.="<td> = </td>";
				$tab.="<td align=right>".hidezerodecimal($val['nilai'])."</td>";
				$tab.="</tr>";	
			}
		}
		$tab.="</table>";
		$tab.="</td>
			<td valign=top>Pajak (%)</td>
			<td valign=top>:</td>
			<td valign=top colspan=4>";
		$tab.="<table width=100%>";
		foreach($groupArr as $key => $val){
			if($val['tipe']=='pajak'){
				$tab.="<tr>";
				$tab.="<td>".$val['nourut']." ".$nmakun[$val['nourut']]."</td>";
				$tab.="<td> = </td>";
				$tab.="<td align=right>".hidezerodecimal($val['nilai'])." %</td>";
				$tab.="</tr>";	
			}
		}
		$tab.="</table>";	
		$tab.="</td>
			<td valign=top>Termin (%)</td>
			<td valign=top>:</td>
			<td valign=top colspan=4>";
		$tab.="<table width=100%>";
		foreach($groupArr as $key => $val){
			if($val['tipe']=='termin'){
				$tab.="<tr>";
				$tab.="<td>Termin ke ".$val['nourut']."</td>";
				$tab.="<td> = </td>";
				$tab.="<td align=right>".hidezerodecimal($val['nilai'])." %</td>";
				$tab.="</tr>";	
			}
		}
		$tab.="</table>";	
		$tab.="</td>
		</tr>
		<tr class=rowcontent>
			<td valign=top>Spesifikasi<br>Pekerjaan</td> 
			<td valign=top>:</td>
			<td colspan=16>".str_replace('####','<br>',$bar['spesifikasi'])."</td>
		</tr>";
		$tab.="</table>";
	if($tipe=='html'){
		echo $tab;
		echo @$isi.="<hr><table class='sortable' cellspacing='1' border='0' style=min-width:100%>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>";

	}
	else {
		$stream = $tab;
		$nop_ = "pengajuan_spk";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						 @ unlink('tempExcel/'.$file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$nop_.".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			closedir($handle);
		}
	}
	break;
	case'viewlistfile':
	$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
	echo $tab;
	break;
	case'editdetail':
		$rupiah=$pajak=$termin=$no='';
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$rows=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			if($no==$rows){
				$rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan'];
			}else{
				$rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan']."#$#";
			}
		}
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='pajak'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$rows=owlBaris($res); $no='';
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			if($no==$rows){
				$pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai'];
			}else{
				$pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai']."#$$#";
			}
		}
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$rows=owlBaris($res); $no='';
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			if($no==$rows){
				$termin.=$bar['tipe']."######".$bar['nourut']."######".$bar['nilai']."######".$bar['keterangan'];
			}else{
				$termin.=$bar['tipe']."######".$bar['nourut']."######".$bar['nilai']."######".$bar['keterangan']."#$$$#";
			}
		}
		echo $rupiah."########".$pajak."########".$termin;
	break;
	case'insertdetail':
		$str = "insert into " . $dbname . ".lgl_pengajuanspk_keg (`notransaksi`,`subunit`,`kegiatan`,`satuan`,`volume`,`total`)
		values ('".$notransaksi."','".$subunit."','".$kegiatan."','".$satuan."','".$volume."','".$total."')";//exit('error'.$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'deldetail':
		$str = "delete from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."' and subunit='".$subunit."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'insertdt':
		# Delete dulu
		if($row==1){
			$str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='".$jenis."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		# Jika data sudah ada maka langsung Insert
		//exit('error'.$rupiah);
		if($rupiah!=''){
			$str = "insert into " . $dbname . ".lgl_pengajuanspkdt (`notransaksi`,`tipe`,`nourut`,`nilai`,`keterangan`)
			values ('".$notransaksi."','".$jenis."','".$nourut."','".$rupiah."','".$keterangan."')";// exit('error'.$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;
	case'insert':
		# Delete dulu
		$str = "delete from " . $dbname . ".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		# Jika data sudah ada maka langsung Insert	
		$str = "insert into " . $dbname . ".lgl_pengajuanspkht (`notransaksi`,`kategori`,`jenis`,`pt`,`unit`,`divisi`,`bagian`,`tanggal`,`koderekanan`,`perjanjianinduk`,`perjanjianperubahan`,`project`,`spesifikasi`,`jangkawaktu`,`tanggaldari`,`tanggalsampai`,`garansi`,`retensi`,`denda`,`statuspersetujuan`,`createby`,`createtime`,`updateby`)
		values ('".$notransaksi."','".$kategori."','".$jenis."','".$pt."','".$unit."','".$divisi."','".$bagian."','".$tanggal."','".$koderekanan."','".$perjanjianinduk."','".$perjanjianperubahan."','".$project."','".$spesifikasi."','".$jangkawaktu."','".$tanggaldari."','".$tanggalsampai."','".$garansi."','".$retensi."','".$denda."','0','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "')";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	
	
	case'form_ajukan';
		$kodeorg=$unit;
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SPK' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";// exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
    case'ajukan':
	
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".lgl_pengajuanspkht set posting='1' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','SPK','1','" . $kepada."','0','','','')";
		
		// exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
		
	case'loaddatadetail':
	$tab = "";
	$tab.= "<table  cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>";
	$tab.= "<thead>";
	$tab.= "<tr class=rowheader>";
	$tab.= "<td align=center>No</td>";
	$tab.= "<td align=center>" . $_SESSION['lang']['subunit'] . "</td>";
	$tab.= "<td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>";
	$tab.= "<td align=center>" . $_SESSION['lang']['satuan'] . "</td>";
	$tab.= "<td align=center>" . $_SESSION['lang']['volume'] . "</td>";
	$tab.= "<td align=center>Rp / Sat</td>";
	$tab.= "<td align=center>" . $_SESSION['lang']['total'] . "</td>";
	$tab.= "<td align=center width=50px>Action</td>";
	$tab.= "</tr>";
	$tab.= "</thead>";
	
	$no = 0;
	$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspk_keg	 where 1=1 and notransaksi='" . $notransaksi . "'"; //exit("error $str");
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		$nmkeg='';
		while ($bar = $res->fetch()) {
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				$xx.=" style=background-color:#F5EEF8";
			}
			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td>" . $bar['subunit'] . " - " . $nmorg[$bar['subunit']] . "</td>";
			$tab.="<td>" . $bar['kegiatan'] . " - " . $nmkeg[$bar['kegiatan']] . "</td>";
			$tab.="<td align=center>" . $bar['satuan'] . "</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total']/$bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total'])."</td>";
			$isi.="<td colspan=2 align=center><img class=resicon src=images/application/application_delete.png onclick=\"deldetail('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."');\" title='Delete'></td>";
			$tab.=$isi;
			$tab.="</tr>";
		}
	}
	$tab.= "</table>";
	
	echo $tab;
	break;
	
case 'submitfile':
	if($notransaksi==''){
		exit("Warning : Silahkan isikan detail transaksi terlebih dahulu !");
	}
	#cek data
	$sql = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
	$res=fetchData($sql);
	if(count($res)==0){
		exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu !');
	}
	$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."'";
	$res=fetchData($str);
	if(count($res)>=10){
		exit("Warning : Limit upload hanya 10 file.");
	}
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			//$filename = $pt."_".$tgl."".$filetype;
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				if($_FILES['file']['size'] <= 250000){
					$str = "insert into ".$dbname.".listfile_lgl_pengajuanspk values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	break;
case 'loadfiles':
	$no = 0;
	$tab = "";
	$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and status='1'";
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
			$icon=seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
				</td>";
			$nfile='';
			if(strlen($val['namafile'])>10){
				$nfile = potongtext($val['namafile'],10).$val['formaticon'];
			}else{
				$nfile = $val['namafile'];
			}
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
			$tab."	</td>
				</tr>";
		}
	}
	echo $tab;
	break;
	case'viewfile':
	$tab="";
	$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
	echo $tab;
	break;
case 'deletefile':
	$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
case'showfile':
	$tab.="
		<table class='sortable' cellspacing='1' border='0' style=min-width:100%>
			<thead>
			<tr class=rowheader>
				<td align='center' width=30px>No.</td>
				<td align='center' width=50px>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center' width=50px colspan=2>Action</td>
			</tr>
			</thead>
			<tbody>";
		
		$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and status='1'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
						$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
					</td>";
				$nfile='';
				if(strlen($val['namafile'])>20){
					$nfile = potongtext($val['namafile'],20).$val['formaticon'];
				}else{
					$nfile = $val['namafile'];
				}
				$tab.="<td style='text-align:left;'>".getcriterianame($val['kriteriaefil'])."</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."<td align=center><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a></td>";
				$tab."</tr>";
			}
		}
		$tab.="</table>";
	echo $tab;
break;
}
function encrypt( $q, $key='') {
	if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
}
function decrypt( $q, $key='') {
    if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}

?>
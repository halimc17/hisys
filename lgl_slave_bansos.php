<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$kodeorg = checkPostGet('kodeorg', '');
$kategori = checkPostGet('kategori', '');
$namapemesan = checkPostGet('namapemesan', '');
$lokasipemesan = checkPostGet('lokasipemesan', '');
$tujuan = checkPostGet('tujuan', '');
$satuan = checkPostGet('satuan', '');
$deskripsi = checkPostGet('deskripsi', '');
$rupiah = checkPostGet('rupiah', '');
$rekening = checkPostGet('rekening', '');
$tipe = checkPostGet('tipe', '');
$tipebayar = checkPostGet('tipebayar', '');
$atasnama = checkPostGet('atasnama', '');
$npwp = checkPostGet('npwp', '');
$ptsrc = checkPostGet('ptsrc', '');
$kasbanksrc = checkPostGet('kasbanksrc', '');
$katsrc = checkPostGet('katsrc', '');
$status = checkPostGet('status', '');
$tanggalmulai = tanggalsystemn(checkPostGet('tanggalmulai', ''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai', ''));

$numrow = checkPostGet('numrow', '');
$kepada = checkPostGet('kepada', '');
$rupiah=str_replace(',','',$rupiah);

$keterangan = checkPostGet('keterangan', '');
$namafile = checkPostGet('namafile', '');

$divsch = checkPostGet('divsch', '');
$namasrc = checkPostGet('namasrc', '');
$tanggalsch = checkPostGet('periodesch', '');
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkat = makeOption($dbname, 'lgl_kategoribansos', 'kode,nama');

$jenistrk = "BANSOS";
$jenisApp = "BANSOS";

$tmpTgl = explode('-',$tanggal);	
$path	= "fileupload/lgl_bansos/";
$todayhis=date('Y-m-d h:i:s');

$urlefil=checkPostGet('urlefil','0');
if($urlefil!='0'){
	$method = $_GET['method'];
	$notransaksi = $_GET['notransaksi'];
}

// APAKAH USER YG LOGIN SAAT INI BISA POSTING 
$sStr = selectQuery($dbname,"setup_posting","*", "kodeaplikasi='csr'");
$qStr = fetchData($sStr);
$kodejabatan = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$userPosting = 0;
foreach ($qStr as $key => $value) {
	if ($value['jabatan'] == $kodejabatan[$_SESSION['standard']['userid']]) {
		$userPosting = 1;
		break;
	}
}

switch ($method) {
    case'html':
		$tab= "";
		@$countApprove = getCountApproval($jenisApp,$kodeorg);
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);
		
		$rekbank = makeOption($dbname,'keu_5akunbank','noakun,rekening');
		$kodebank = makeOption($dbname,'keu_5akunbank','noakun,namabank');
		$bank = makeOption($dbname,'keu_5daftarbank','kodebank,namabank');
		
		$str=" select * from ".$dbname.".lgl_bansos where  notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$tab.= "<fieldset style=min-width:900px><legend><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"previewexcelbansos('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','excel');\" >
				</legend>";
		$tab.= "<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']." </td>";
				// for($i=1;$i<=$countApprove;$i++){
				// 	$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				// }
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".$bar['updatetime']."</td>";
		
	$tab.= "</tbody></table><hr>";
	$tab.= "<table cellspacing=1 cellpadding=5 border=0>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td>".$bar['notransaksi']."</td>
				
				<td>" . $_SESSION['lang']['kategori'] . "</td> 
				<td>:</td>
				<td>".$nmkat[$bar['kategori']]."</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				
				<td>Nama Pemesan</td> 
				<td>:</td>
				<td>".$bar['namapemesan']."</td>
			</tr> 
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td>".$bar['tanggal']."</td>
				
				<td>Lokasi Pemesan</td> 
				<td>:</td>
				<td>".$bar['lokasipemesan']." - ".$nmorg[$bar['lokasipemesan']]."</td>
			</tr>
			<tr>
				<td>Tipe Pembayaran</td> 
				<td>:</td>
				<td>".$bar['tipebayar']."</td>
				
				<td>Nomor Rekening</td> 
				<td>:</td>
				<td>".@$bank[$kodebank[$bar['rekening']]]." - ".@$rekbank[$bar['rekening']]."</td>
			</tr>
			<tr>
				<td></td> 
				<td></td>
				<td></td>
				
				<td>Atas Nama (Penerima)</td> 
				<td>:</td>
				<td>".$bar['atasnama']."</td>
			</tr>
			<tr>
				<td valign=top>Tujuan</td> 
				<td valign=top>:</td>
				<td colspan=4>".str_replace('####','<br>',$bar['tujuan'])."</td>
			</tr>";
	$tab.= "</table><hr>";
	$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=75px>" . $_SESSION['lang']['rupiah'] . "</td>
        </tr>
		</thead>";
		$no = 0;
        $str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr class=rowcontent ".$xx.">";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . $bar['deskripsi'] . "</td>";
			$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
			
			@$trupiah+=$bar['rupiah'];
		}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=2 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				</tr>";
		
        $tab.="</table><hr>";
		
		$tab.="<table class='sortable' cellpadding=5 cellspacing='1' border='0' style=width:100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='loadfilesdetail'>
				</tbody>
			</table>";
		$tab.= "</fieldset>";
		
		$stream = $tab;
		
		if($tipe!='excel'){
			echo $stream;
		} else{
			$nop_ = "bansos_" . date('Ymd_His');
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		
	break;
	case'getnotransaksi':
		#001/INT/LGL/BOD/BJHO/IX/2017
		/* - Nomor transaksi 13 digit
			2017 : tahun
			06          : bulan
			07          : tanggal
			003          : kode organisasi
			-1          : nomor urut transaksi */
			
		$tempPrd=str_replace('-','',$tanggal);
		$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$str=" select max(substr(notransaksi,-3)) as notransaksi from ".$dbname.".lgl_bansos where  tanggal = '".$tanggal."' order by notransaksi desc limit 1"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if(intval($bar['notransaksi'])==0){
			$noawal=1;
		}else{
			$noawal = intval($bar['notransaksi'])+1;
		}
		
		$strx=" select inisialisasiorganisasi from ".$dbname.".organisasi where  kodeorganisasi='".$kodeorg."'"; //exit('error'.$str);
		$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		$barx=$resx->fetch();
		if($barx['inisialisasiorganisasi']==''){
			exit('Warning : Inisial / Kode Organisasi belum ada, silahkan ditambah melalui menu : Administrator - Organization Chart');
		}
		
		$notranbaru=$tempPrd."/BNS/".addZero($barx['inisialisasiorganisasi'],3)."/".addZero($noawal,3);
        
		echo $notranbaru;
    break;
	case'getrekening':
		$str=" select a.*,b.namabank as namakodebank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where  a.pemilik='".$lokasipemesan."' and a.status ='1'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		while($bar=$res->fetch()){
			$optb.="<option value=" . $bar['noakun'] . ">" . $bar['namakodebank'] . " - " . $bar['rekening'] . "</option>";
		}
		echo $optb;
	break;
	case'getatasnama':
		$str=" select a.*,b.namabank as namakodebank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where  a.noakun='".$rekening."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		echo $bar['atasnama'];
	break;
    case'detail':
		#cek dulu sudah ada atau belum ? jika ada = update
		$str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
		$res=fetchData($str);
		if(count($res)>0){
			# update flag menjadi 1
			$str = "update " . $dbname . ".lgl_bansos set namapemesan='".$namapemesan."', tujuan='".$tujuan."', updateby='".$_SESSION['standard']['userid']."', rekening='".$rekening."' where notransaksi = '" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		// echo"<pre>";
		// print_r($str);
		// echo"</pre>"; exit('error');
		$query = selectQuery($dbname,'setup_satuan','*');
		$hasil = fetchData($query);
		$optsat = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach ($hasil as $bar) {
			$optsat.="<option value=" . $bar['satuan'] . ">" . $bar['satuan'] . "</option>";
		}
        OPEN_BOX();
	echo"<fieldset>
        <legend>" . $_SESSION['lang']['form'] . "</legend>
		<table><td valign=top>
		<fieldset style=height:75px>
        <legend>" . $_SESSION['lang']['input'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center width=350px>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=350px>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center rowspan=2 width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		
		</thead>
        <tr class=rowcontent>
            <td align=center>#</td>    
			<td><input id=deskripsi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:99%;\"></td>
            <td><select id=satuan style='width:100%;'>".$optsat."</select></td>
			<td><input id=rupiah style=\"width:97%;\" onkeyup=\"z.numberFormat('rupiah',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			
			<td align=center><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
                <img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=3 align=right>
			<td colspan=1 align=center>
			<img title='Refresh' class=resicon onclick=\"loaddatadetail('".$notransaksi."')\" src='images/refresh2.png'/>
			<img title='" . $_SESSION['lang']['selesai'] . "' class=resicon onclick=\"displayList()\" src='images/foldoq.png'/>
			</td>
        </tr></table></fieldset>
		</td><td  valign=top >
		<fieldset style=height:75px;min-width:310px><legend>Upload</legend>
			<table>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' >
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=\"submitfile()\">Submit</button>
					</td>
				</tr>
			</table>
		</fieldset>
		</td></table>
        </fieldset><hr>";
	echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
	CLOSE_BOX();
	break;

    case'insert':

        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_bansos where "
		. " notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and kategori='".$kategori."' and tujuan='" . $tujuan . "' and deskripsi='".$deskripsi."' and tanggal='".$tanggal."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
			exit("Error : Transaksi sudah ada.");
        }
		
		// cek notransaksi sudah ada atau tidak
		// if($notransaksi == ''){
		// 	$notransaksi = generateNoTrans($kodeorg, 'csr', $tanggal, '');
		// }
		// exit("warning " . $variable);

		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='".$notransaksi."'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}
		
		$str = "insert into " . $dbname . ".lgl_bansos (`notransaksi`,`kodeorg`,`tanggal`,`kategori`,`namapemesan`,`lokasipemesan`,`tujuan`,`deskripsi`,`rupiah`,`statuspersetujuan`,`createby`,`createtime`,`updateby`,`updatetime`,`rekening`,`tipebayar`,`atasnama`,`npwp`,`satuan`)
		values ('".$notransaksi."','".$kodeorg."','".$tanggal."','".$kategori."','".$namapemesan."','".$lokasipemesan."','".$tujuan."','".$deskripsi."','".$rupiah."','0','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "','".$todayhis."','".$rekening."','".$tipebayar."','".$atasnama."','".$npwp."','".$satuan."')";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		echo $notransaksi;
	break;
    case'delete':
		// cek apakah sudah diposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, "lgl_bansos", 'posting,postingdate', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 1) {
            exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qPos[0]['postingdate'])));
        }

        $str = "delete from " . $dbname . ".lgl_bansos where notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'"; //exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."'"; 
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar = $res->fetch()){
			$str="delete from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."' and namafile='".$bar['namafile']."'"; 
			try{
				$owlPDO->exec($str);
				$pathx = $path.$bar['namafile']; 
				unlink($pathx);
			}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		}
	break;

    case'deletedetail':
		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and kategori='".$kategori."' and tujuan='" . $tujuan . "' and deskripsi='".$deskripsi."' and tanggal='".$tanggal."'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}
		
		#cek data kalau terakhir hapus juga filenya
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
		#hapus data transaksinya
        $str = "delete from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and kategori='".$kategori."' and tujuan='" . $tujuan . "' and deskripsi='".$deskripsi."' and satuan='".$satuan."' and tanggal='".$tanggal."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		#hapus filenya
        if ($jlhbrs =='1') {
			$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$str="delete from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."' and namafile='".$bar['namafile']."'"; 
				try{
					$owlPDO->exec($str);
					$pathx = $path.$bar['namafile'];
					unlink($pathx);
				}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
			}
        }
		
	break;

	case'form_ajukan';

		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='".$jenisApp."' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['kepada']."</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
    case'ajukan':
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_bansos set posting='1', statuspersetujuan='0' ,postingdate='" . date('Y-m-d H:i:s') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					 values ('','".$notransaksi."','".$jenisApp."','1','" . $kepada."','0','','','')";
			try {$owlPDO->exec($str);
			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
        } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
	
	case'ajukandireksi':

		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_bansos set posting='1', statuspersetujuan='0' ,postingdate='" . date('Y-m-d H:i:s') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);

            $tglapp=date('y-m-d h:i:s');
            $strkry="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='7' and bagian='BOD'";
			$reskry=$owlPDO->query($strkry) or die(print " Gagal: ".PDOException::getMessage());
			$reskry->setFetchMode(PDO::FETCH_ASSOC);
			while($barkry=$reskry->fetch()){
				# insert ke table approval
				$strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						 values ('','".$notransaksi."','".$jenisApp."','1','".$barkry['karyawanid']."','0','','','".$tglapp."')";
				try {
					$owlPDO->exec($strapp);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";die();
				}	
			}
		
        } catch (PDOException $e) {
        	print " Gagal  !: " . $e->getMessage() . "\n";die();
        }
		
        
	break;

    case'loaddatadetail':
		$tab="<table><td valign=top>";
		$tab.="<fieldset><legend>Data</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:590px>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkode=$nmtantum=$nama=$nmalamat='';
			while ($bar = $res->fetch()) {
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==0){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$tab.="<tr class=rowcontent ".$xx.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . $bar['deskripsi'] . "</td>";
				$tab.="<td align=left>" . $bar['satuan'] . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
				$tab.="<td align=center>";
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['kategori'] . "','" . $bar['tujuan'] . "','" . $bar['deskripsi'] . "','" . tanggalnormal($bar['tanggal']) . "','".$bar['satuan']."');\" >
				</td>";
				@$trupiah+=$bar['rupiah'];
			}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=3 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				<td align=right></td>
				</tr>";
		}
        $tab.="</table>";
		$tab.="</fieldset></td><td valign=top>";
		$tab.="<fieldset><legend>File</legend>";
		$tab.="<table class='sortable' cellspacing='1' border='0' style=min-width:310px>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>";
		$tab.="</fieldset></td></table>";
		$tab.="</fieldset>";

        echo $tab;
	break;

    case'loaddata':

    $arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);

    $strkegiatan="select a.*,b.namakaryawan, b.karyawanid from ".$dbname.". approval a left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid ";
    $res=$owlPDO->query($strkegiatan) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($barr=$res->fetch())
    {
    	$nma[$barr['notransaksi']][$barr['level']]=$barr['namakaryawan'];
    	$stat[$barr['notransaksi']][$barr['level']]=$barr['status'];
    	$komentar[$barr['notransaksi']][$barr['level']]=$barr['komentar'];
    	$tgl[$barr['notransaksi']][$barr['level']]=$barr['tanggal'];

    }

    	

		
        $where = "";
        if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$where .= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
        if ($divsch != '') {
            $where.=" and a.kodeorg='" . $divsch . "' ";
        }

        if ($namasrc != '') {
            $where.=" and a.atasnama='" . $namasrc . "' ";
        }
        if ($tanggalmulai != '--' and $tanggalsampai=='--') {
            $where.=" and a.tanggal='" . $tanggalmulai . "' ";
        }elseif($tanggalmulai == '--' and $tanggalsampai!='--') {
            $where.=" and a.tanggal='" . $tanggalsampai . "' ";
        }elseif($tanggalmulai != '--' and $tanggalsampai!='--') {
            $where.=" and a.tanggal between '" . $tanggalmulai . "' and '" . $tanggalsampai . "'";
        }
		
		
		
		if ($ptsrc != '') {
            $where.=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$ptsrc."')";
        }
		 if ($kasbanksrc == '1') {
            $where.=" and b.nodok !='' ";
        }elseif($kasbanksrc == '0'){
			$where.=" and b.nodok is null ";
		}
		if ($katsrc != '') {
            $where.=" and a.kategori='" . $katsrc . "' ";
        }
		if ($status == 'x') {
            $where.=" and a.posting='0' and a.statuspersetujuan='0' ";
        }elseif($status != 'x' and $status !=''){
            $where.=" and a.posting='1' and a.statuspersetujuan='".$status."' ";
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

		$nmkategori=makeOption($dbname,'lgl_kategoribansos','kode,nama');
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_bansos a  where 1=1 " . $where . " group by a.notransaksi, a.kodeorg";
        #exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
		
		$countApp = getCountApproval($jenisApp,'');

       $str = "SELECT sum(a.rupiah) as biaya,a.notransaksi, a.kodeorg, a.tanggal, a.updateby, a.posting,a.kategori,a.namapemesan,a.lokasipemesan,a.tujuan,a.rekening,a.tipebayar,a.atasnama, a.statuspersetujuan,a.posting FROM " . $dbname . ".lgl_bansos  a  
		where 1=1 " . $where . " group by a.notransaksi, a.kodeorg order by a.notransaksi desc ";
		if ($tipe == 'html') {
			$str .= "limit " . $offset . "," . $limit . "";
		}
		
		$tab = "";
		if ($tipe == 'excel') {
			$tab .= "<table border=1>
								<tr class=rowheader>
									<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
									<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
									<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
									<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
									<td align=center>" . $_SESSION['lang']['kategori'] . "</td>
									<td align=center>" . $_SESSION['lang']['biaya'] . "</td>
									<td align=center>" . $_SESSION['lang']['kasbank'] . "</td>
								</tr>";
		}
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
			$a=$no%2;
			$xx='';
			if($a==1 && $tipe == 'html'){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			
			
            $tab.="<tr class=rowcontent ".$xx." id=tr_".$no.">";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=left>" . $nmkategori[$bar['kategori']] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['biaya'],2) . "</td>";
            $tab.="<td>" . $bar['kas'] . "</td>";

						if ($tipe == 'html') {
							$tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
						}

						if ($tipe == 'html') {
							if($bar['posting'] == 0) {
									$isi .= "<td align=center>
								<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"edit('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "','" . $bar['kategori'] . "','" . $bar['namapemesan'] . "','" . $bar['lokasipemesan'] . "','".str_replace('####','\n',$bar['tujuan'])."','".$bar['rekening']."','".$bar['tipebayar']."','".$bar['atasnama']."','".$bar['npwp']."');\" >
							</td>";
									$isi .= "<td align=center>
								<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "');\" >
							</td>";               
									$isi .= "<td align=center>
								<img src=images/red/posting.png class=resicon  title='Posting' onclick=\"postingin('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "')\">
							</td>";               
							}
				if($bar['posting'] == 1){
					// $isi .= "<td></td><td></td><td></td>";
					if($userPosting == 1){
						// KETIKA TERDAFTAR DI SETUP POSTING
						$isi     .= "<td></td><td></td><td align=center> <img src=images/icons/04/16/04.png class=resicon  title='Posted' onclick=\"unposting('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "');\"> </td>";
					}else{
						// KETIKA TIDAK TERDAFTAR DI SETUP POSTING
						$isi     .= "<td></td><td></td><td align=center> <img src=images/icons/04/16/02.png class=resicon  title='Posted'> </td>";
					}
				}
	
				
							$isi .= "<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"html('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
				
				$isi .= "<td align=center><img src=images/pdf.jpg class=resicon class=zImgBtn height='30'  title='View PDF' onclick=\"pdf('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
							
							$tab .= $isi;
						}

            $tab .= "</tr>";
        }

				if ($tipe == 'html') {
					$totrows = ceil($jlhbrs / $limit);
					if ($totrows == 0) {
							$totrows = 1;
					}
					$isiRow = '';
					for ($er = 1; $er <= $totrows; $er++) {
							$sel = ($page == $er - 1) ? 'selected' : '';
							$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
					}
					$footd = "";
					$footd.="</tr>
											 <tr><td colspan=17 align=center>";
	
					if ($page == '0') {
							$footd.="<button class=mybutton disabled=true>Prev</button>";
					} else {
							$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
					}
	
					$footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
	
					if (($page + 1) == $totrows) {
							$footd.="<button class=mybutton disabled=true>Next</button>";
					} else {
							$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
					}
					$footd.="</td>
							</tr>";
	
					echo $tab . "#####" . $footd;
				}
				if ($tipe == 'excel') {
					$tab .= "</table>";

					$tglNow = date('d-m-Y');
					$nop_ 	= "Report Dokumen Keluar_".$tglNow."";
					if (strlen($tab) > 0) {
						if ($handle = opendir('tempExcel')) {
							while (false !== ($file = readdir($handle))) {
								if ($file != "." && $file != ".." && $file != "index.html") {
									@unlink('tempExcel/' . $file);
								}
							}
							closedir($handle);
						}
						$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
						if (!fwrite($handle, $tab)) {
							echo "<script language=javascript1.2>
									parent.window.alert('Cant convert to excel format');
								</script>";
							exit;
						} else {
							echo "<script language=javascript1.2>
									window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
						}
						closedir($handle);
					}
				}

    break;
	
	case 'submitfile':
		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='" . $notransaksi . "'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}
		
		#cek data
        $sql = "select * from " . $dbname . ".lgl_bansos where notransaksi='" . $notransaksi . "'";
		$res=fetchData($sql);
		if(count($res)==0){
			exit('Warning : Silahkan isikan dan save detail bansos terlebih dahulu !');
		}
		
		$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi = '".$notransaksi."' and status='1'";
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
				$namafile1=explode('.', $_FILES['file']['name']);
				$filename = $namafile1[0]."_".$tgl."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 1000000){
						$str = "insert into ".$dbname.".listfile_lgl_bansos values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
						exit("warning : Ukuran file upload maksimal 10 MB");
					}
				}else{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
			}
		}
	break;
	
	case 'loadfiles':
		$no = 0;
		$tab = $posting = "";	
		$str="select * from ".$dbname.".lgl_bansos where notransaksi = '".$notransaksi."'";
		$res=fetchData($str);
		@$posting=$res[0]['posting'];
		
		$str="select * from ".$dbname.".listfile_lgl_bansos where notransaksi = '".$notransaksi."' and status='1'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
				}
				
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
		$sStr = selectQuery($dbname,"lgl_bansos","posting,postingdate", "notransaksi='" . $notransaksi . "'");
		$qStr = fetchData($sStr);
		if ($qStr[0]['posting'] == 1) {
			exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qStr[0]['postingdate'])));
		}

		$str="delete from ".$dbname.".listfile_lgl_bansos where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'pdf':

	$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);

    $strkegiatan="select a.*,b.namakaryawan, b.karyawanid from ".$dbname.". approval a left join ".$dbname.".datakaryawan b on b.karyawanid=a.karyawanid ";
    $res=$owlPDO->query($strkegiatan) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($barr=$res->fetch())
    {
    	$nma[$barr['notransaksi']][$barr['level']]=$barr['namakaryawan'];
    	$karyid[$barr['notransaksi']][$barr['level']]=$barr['karyawanid'];
    	$stat[$barr['notransaksi']][$barr['level']]=$barr['status'];
    	$komentar[$barr['notransaksi']][$barr['level']]=$barr['komentar'];
    	$tglarr[$barr['notransaksi']][$barr['level']]=$barr['tanggal'];

    }


    	




		$str=" select * from ".$dbname.".lgl_bansos where  notransaksi='".$notransaksi."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$barx=$res->fetch();
		
		$tab='';
		$tab.="<table width=100% border=0>";
		$tab.="<tr><td align=center><font size=5><b><u>FORM BANTUAN SOSIAL (BANSOS)</u></b></font></td></tr>";
		$tab.="<tr><td>&nbsp;</td></tr>";
		$tab.="<tr><td>&nbsp;</td></tr>";
		$tab.="<tr><td align=right>Nomor : <b><u>".$barx['notransaksi']."</u></b></td></tr>";
		$tab.="<tr><td align=left style=border-bottom:1px>Nama Pemesan : <b>".($barx['namapemesan'])."</b></td></tr>";
		$tab.="<tr><td align=left>Lokasi Pemesan : <b>".($barx['lokasipemesan'])."</b></td></tr>";
		$tab.="<tr><td align=left>Kategori Bansos : <b>".($barx['kategori'])."</b></td></tr>";
		$tab.="<tr><td>";
			$tab.="<table width=100% cellspacing=0 border=1>";
			$tab.="<tr style=background-color:#DCDCDC>
						<td align=center width=30px>No</td>
						<td align=center>Deskripsi</td>
						<td align=center width=150px>Total</td>
					</tr>";
			$no='';
			$resz=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$resz->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$resz->fetch()){
				$no++;
				$tab.="<tr><td align=center>".$no."</td>";
				$tab.="<td align=left>".$bar['deskripsi']."</td>";
				$tab.="<td align=right>".@number_format($bar['rupiah'])."</td></tr>";
				@$total+=$bar['rupiah'];
			}
			$tab.="<tr style=background-color:#DCDCDC>
						<td align=center colspan=2>Total</td>
						<td align=right width=150px>".@number_format($total)."</td>
					</tr>";
			$tab.="</table>";
		$tab.="</td></tr>";
		$tab.="<tr><td>&nbsp;</td></tr>";
		$tab.="<tr><td>Tujuan :</td></tr>";
		$tab.="<tr><td>";
			$tab.="<table width=100% cellspacing=0 border=1>";
			$tab.="<tr>
						<td>".str_replace("####","<br>",$barx['tujuan'])."</td>
					</tr>";
			$tab.="</table>";
		$tab.="</td></tr>";
		$tab.="<tr><td>&nbsp;</td></tr>";
		$tab.="<tr><td>";

		if($karyid[$barx['notransaksi']]['2']==''){
			$cols1='2';
			$hide="style=display:none;";
			$hide1="";
		

		}else if ($karyid[$barx['notransaksi']]['3']=='') {
			$cols1='2';
			$hide="style=display:none;";
			$hide1="";
			

		}else{
			$cols1='2';
			$hide="style=display:none;";
			$hide1="style=display:none;";
	
			
		}
			$tab.="<table width=100% border=0>";
			$tab.="<tr><td align=center colspan=2></td><td align=right colspan=2>Tanggal : <u>".$barx['createtime']."</u></td></tr>";
			$tab.="<tr>
						<td align=center>Diminta Oleh,</td>
					</tr>";
			$tab.="<tr><td colspan=5>&nbsp;</td></tr>";
			$tab.="<tr><td colspan=5>&nbsp;</td></tr>";
			$tab.="<tr><td colspan=5>&nbsp;</td></tr>";
			$tab.="<tr>
			<td align=center><u>".getNamaKaryawan($barx['updateby'])."</u>
			</td>
			</tr>";
			$tab.="<tr>
			<td align=center >".getJabatan($barx['updateby'])."</td></tr>";
			$tab.="</table>";
		$tab.="</td></tr>";
		$tab.="</table>";
		
		 // echo $tab;
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		
		## Print Out
		if($urlefil=='0'){
			$dompdf->stream("bansos",array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;

	case 'posting':
        // cek apakah sudah diposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, 'lgl_bansos', 'posting,postingdate', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 1) {
            exit("warning : Sudah diposting " . date('d-m-Y H:i:s',strtotime($qPos[0]['postingdate'])));
        }

        $data = array();
        $data['posting'] = 1;
        $data['postingby'] = $_SESSION['standard']['userid'];
        $data['postingdate'] = date("YmdHis");
        
        try {
            $sPost = updateQuery($dbname, 'lgl_bansos', $data, "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
            $owlPDO->exec($sPost);
        } catch (Throwable $e) {
            echo "DB Error : " . $e->getMessage();
            die();
        }    
    break;

	case 'unposting':
        // cek apakah sudah unposting antisipasi jika membuka 2 tab
        $sPos = selectQuery($dbname, "lgl_bansos", 'posting', "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
        $qPos = fetchData($sPos);
        if ($qPos[0]['posting'] == 0) {
            exit("warning : Sudah diunposting");
        }

        // cek siapa yang posting
        if($userPosting != 1){
            exit("warning : Anda tidak diizinkan untuk unposting!");
        }

        $data = array();
        $data['posting'] = 0;
        $data['postingby'] = $_SESSION['standard']['userid'];
        $data['postingdate'] = '0000-00-00 00:00:00';

        try {
            $sUnPost = updateQuery($dbname, "lgl_bansos", $data, "notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'");
            $owlPDO->exec($sUnPost);
        } catch (Throwable $e) {
            echo "DB Error : " . $e->getMessage();
            die();
        }
    break;
}

# Fungsi di pakai pada saat menyimpan hasil dari <textarea></textarea>
# $a => value yang akan di rubah
# $x => akan di replace menggunakan ??, default = ####
/* function replaceEnter($a, $x="####"){
	$a = nl2br($a);
	$i = explode('<br />',$a);
	$no =''; $t='';
	foreach($i as $r => $e){
		$no+=1;
		if($no < count($i)){
			$t.=trim($e).$x;
		}else{
			$t.=trim($e);
		}
	}
	return $t;
}
 */
// function getJabatan($karyawanid){
	// global $dbname;
	// global $owlPDO;
	// $i = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karyawanid."'");
	// $x = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$i[$karyawanid]."'");
	// return $x[$i[$karyawanid]];
// }

function tambahspasi($a, $x=20){
	$q=strlen($a);
	$t='';
	$s=($x-$q);
	if($q<$x){
		for($i=1;$i<$s;$i++){
			$t.="x";
		}
	}
	return $a.$t;
}
?>	
<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param               = $_POST;
$method              = checkPostGet('method', '');
$jns_id              = checkPostGet('jns_id', '');
$jenis_vhc              = checkPostGet('jenis_vhc', '');
$traksi_id              = checkPostGet('traksi_id', '');
$kode_vhc            = checkPostGet('kode_vhc', '');
$tglKerja            = tanggalsystemn(checkPostGet('tglKerja', ''));
$tanggal            = tanggalsystemn(checkPostGet('tanggal', ''));
$kodeOrg             = checkPostGet('kodeOrg', '');
$kdOrg             = checkPostGet('kdOrg', '');
$jnsBbm              = checkPostGet('jnsBbm', '');
$jumlah              = checkPostGet('jumlah', '');
$no_trans            = checkPostGet('no_trans', '');
$jnsPekerjaan            = checkPostGet('jnsPekerjaan', '');
$Blok            = checkPostGet('Blok', '');
$lokKerja            = checkPostGet('locationKerja', '');
$kelompok            = checkPostGet('kelompok', '');

$notrans     =checkPostGet('notrans','');
$notransaksi     =checkPostGet('noOptrans','');
$proses          =checkPostGet('proses','');
$lokasi          =checkPostGet('empl','');
$jnsPekerjaan    =checkPostGet('jnsPekerjaan','');
$lokKerja        =checkPostGet('locationKerja','');
$muatan          =checkPostGet('muatan','');
$brtMuatan       =checkPostGet('brtmuatan','');
$jmlhRit         =checkPostGet('jmlhRit','');
$ket             =checkPostGet('ket','');
$posisi          =checkPostGet('posisi','');
$kdKry           =checkPostGet('kdKry','');
$oldjnsPekerjaan =checkPostGet('oldjnsPekerjaan','');
$uphOprt         =checkPostGet('uphOprt','');
$prmiOprt        =checkPostGet('prmiOprt','');
$pnltyOprt       =checkPostGet('pnltyOprt','');
$ketOprt         =checkPostGet('ketOprt','');
$tglTrans        =checkPostGet('tglTrans','');
$thnKntrk        =checkPostGet('thnKntrk','');
$noKntrak        =checkPostGet('noKntrak','');
$biaya           =checkPostGet('biaya','');
$Blok            =checkPostGet('Blok','');
$segment         =checkPostGet('kodesegment','');
$oldSegment      =checkPostGet('oldSegment','');
$oldBlok         =checkPostGet('oldBlok','');
$old_lokKerja    =checkPostGet('old_lokKerja','');
$kmhmAwal        =checkPostGet('kmhmAwal','');
$kmhmAkhir       =checkPostGet('kmhmAkhir','');
$satuan          =checkPostGet('satuan','');
$tipe          =checkPostGet('tipe','');

$jnsPekerjaan = trim($param['jns_kerja']);

$beratmuatan= checkPostGet('beratmuatan','');
$oldbrt_muatan= checkPostGet('oldbrt_muatan','');
$jenisvhc= checkPostGet('jenisvhc','');
$kodetraksi= checkPostGet('kodetraksi','');
$kar= checkPostGet('kar','');
$txtCari=	checkPostGet('txtCari','');
$statData=	checkPostGet('statData','');
$kodevhc_cari=	checkPostGet('kodevhc_cari','');
$mode=	checkPostGet('mode','');
$kode_jns = $jns_id;
switch ($method) {
	case'posting':
		$queryD = selectQuery($dbname,'vhc_rundt',"*","notransaksi='".$no_trans."'");
		$dataD = fetchData($queryD);
		
		$error1="";
		if(count($dataD)==0) {
			$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
		}
		if($error1!='') {
			echo "Data Error :\n".$error1;
			exit;
		}
		
		$strupd=" update ".$dbname.".vhc_runht set posting='1', postingby='".$_SESSION['standard']['userid']."',postedtime='".date('Y-m-d H:i:s')."' where notransaksi='".$no_trans."'";
		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case'preview':
	$str = "select * from " . $dbname . ".vhc_runht where notransaksi = '" . $no_trans . "'";
	$res = fetchData($str);
	$tanggalht = $res[0]['tanggal'];
	
	$optjnsvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc',"jenisvhc='".$res[0]['jenisvhc']."'");
	$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$res[0]['jenisbbm']."'");
	
	$tab= "<table>";
	$tab.= "<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td>
			<td>".$res[0]['notransaksi']."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td>
			<td>".$res[0]['tanggal']."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['jenisvch']."</td><td>:</td>
			<td>".$optjnsvhc[$res[0]['jenisvhc']]."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['kodeorg']."</td><td>:</td>
			<td>".$res[0]['kodeorg']."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['kodevhc']."</td><td>:</td>
			<td>".$res[0]['kodevhc']."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['vhc_jenis_bbm']."</td><td>:</td>
			<td>".$optnmbrg[$res[0]['jenisbbm']]."</td></tr>";
	$tab.= "<tr><td>".$_SESSION['lang']['vhc_jumlah_bbm']."</td><td>:</td>
			<td>".$res[0]['jlhbbm']."</td></tr>";
	
	$tab.= "</table>";
	$tab.= "<hr>";
	$tab.= "<span>".$_SESSION['lang']['vhc_detail_pekerjaan']."</span>";
	
	
	$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pekerjaan'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['vhc_kmhm_awal'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['vhc_kmhm_akhir'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['vhc_berat_muatan'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jumlahrit'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['keterangan'] . "</td>
        </tr>
        </thead>";
        $no = 0;
        $str="select * from ".$dbname.".vhc_rundt   where notransaksi='".$no_trans."' order by kmhmawal asc"; //echo $str;exit();
		$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$res1->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . $res['jenispekerjaan'] . "</td>";
			$tab.="<td align=left>" . $res['alokasibiaya'] . "</td>";	
			$tab.="<td align=right>" . number_format($res['kmhmawal'],2) . "</td>";
			$tab.="<td align=right>" . number_format($res['kmhmakhir'],2) . "</td>";
			$tab.="<td align=right>" . number_format($res['kmhmakhir']-$res['kmhmawal'],2) . "</td>";
			$tab.="<td align=right>" . number_format($res['beratmuatan'],2) . "</td>";
			$tab.="<td align=right>" . number_format($res['jumlahrit'],2) . "</td>";
			$tab.="<td align=left>" . $res['keterangan'] . "</td>";
			
		}
		
        $tab.="</tr>";
        $tab.="</table>";
		
		$tab.= "<hr>";
		$tab.= "<span>".$_SESSION['lang']['vhc_detail_operator']."</span>";
		
		
		$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable  style=width:100%>
				<thead><tr class=rowheader>
				<td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center rowspan='2'>" . $_SESSION['lang']['namakaryawan'] . "</td>
				<td align=center rowspan='2'>" . $_SESSION['lang']['vhc_posisi'] . "</td>
				<td align=center rowspan='2'>" . $_SESSION['lang']['premi'] . " ".$_SESSION['lang']['hi']."</td>
				<td align=center rowspan='2'>" . $_SESSION['lang']['premi'] . " ".$_SESSION['lang']['sdhi']."</td>
			</tr>
			</thead>";
			$no = 0;
			 $arrPos=array("Operator","Helper");
			$str="select * from ".$dbname.".vhc_runhk  where notransaksi='".$no_trans."'"; //echo $str;exit();
			$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			while($res=$res1->fetch()){
				$strd="select sum(premi) as premisdi from ".$dbname.". vhc_runhk_vw  where idkaryawan='".$res['idkaryawan']."' and tanggal between '".substr($tanggalht,0,7)."-01' and '".$tanggalht."' ";
				$res3=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
				$res3->setFetchMode(PDO::FETCH_ASSOC); 
				$rstrd=$res3->fetch();
				
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . getNamaKaryawan($res['idkaryawan']) . "</td>";
				$tab.="<td align=left>" . $arrPos[$res['posisi']] . "</td>";	
				$tab.="<td align=right>" . number_format($res['premi'],0) . "</td>";
				$tab.="<td align=right>" . number_format($rstrd['premisdi'],0) . "</td>";
				
			}
			
			$tab.="</tr>";
			$tab.="</table>";
        echo $tab;
	break;
	case'loaddata':
		
		$where = "";
		if($_POST['txtTgl']!=''){
			$txtTgl=tanggalsystem($_POST['txtTgl']);
			$txt_tgl_a=substr($txtTgl,0,4);
			$txt_tgl_b=substr($txtTgl,4,2);
			$txt_tgl_c=substr($txtTgl,6,2);
			$txtTgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
			$where.=" and a.tanggal='".$txtTgl."'";
		}if($txtCari!=''){
			$where.=" and a.notransaksi like '%".trim($txtCari)."%'";
		}
		if($statData!=''){
			$where.=" and a.posting='".$statData."'";
		}
		if($kodevhc_cari!=''){
			$where.=" and (a.kodevhc like '%".trim($kodevhc_cari)."%' or b.detailvhc like '%".trim($kodevhc_cari)."%')";
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
        $no = 0;
		$tab = "";
        $no = $maxdisplay;

		$ql2="select count(*) as jmlhrow from ".$dbname.".vhc_runht a left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc where a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$where." order by a.tanggal desc";
		$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$res->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		
		$sjvch="select * from ".$dbname.".vhc_5master order by kodevhc";
		$resx=$owlPDO->query($sjvch) or die(print " Gagal: ".PDOException::getMessage());
		$resx->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$resx->fetch()){
			$nopol[$res['kodevhc']]=$res['nopol'];
			$detvhc[$res['kodevhc']]=$res['detailvhc'];
		}
		
		$sql="select a.* from ".$dbname.".vhc_runht a left join ".$dbname.".vhc_5master b on a.kodevhc=b.kodevhc where a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ".$where." order by a.tanggal desc limit ".$offset.",".$limit."";
		$res7=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$res7->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$res7->fetch()){
			$sSpk="select tanggal from ".$dbname.".log_spkht where notransaksi='".$res['notransaksi']."'";
			$res1=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);                
			$rSpk=$res1->fetch();
			@$thn=substr($rSpk['tanggal'],0,4);

			$sbrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['jenisbbm']."'";
			$res1=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);                
			$rbrg=$res1->fetch();                
			$rbrg['namabarang'];
			$no+=1;
			$optjnsvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc',"jenisvhc='".$res['jenisvhc']."'");
			
			echo"
			<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$res['notransaksi']."</td>
			<td align=center>".$optjnsvhc[$res['jenisvhc']]."</td>
			<td align=left>".$res['kodevhc']." ".($nopol[$res['kodevhc']]!=''?"- ".$nopol[$res['kodevhc']]:'')." ".($detvhc[$res['kodevhc']]!=''?"- ".$detvhc[$res['kodevhc']]:'')."</td>
			<td align=center>".tanggalnormal($res['tanggal'])."</td>
			<td align=center>".$rbrg['namabarang']."</td>
			<td align=center>".$res['jlhbbm']."</td>
			";
			
			$awal=$akhir=0;
			$strx="select min(kmhmawal) as kmhmawal, max(kmhmakhir) as kmhmakhir from ".$dbname.".vhc_rundt where notransaksi='".$res['notransaksi']."'";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			while($barx=$resx->fetch()){
				$awal=$barx['kmhmawal'];
				$akhir=$barx['kmhmakhir'];
			}

			echo"<td align=right width=50px>".number_format($awal,0)."</td>";
			echo"<td align=right width=50px>".number_format($akhir,0)."</td>";
			echo"<td align=right width=50px>".number_format($akhir-$awal,0)."</td>";
			echo"<td align=center>".getNamaKaryawan($res['updateby'])."</td>";
			
			if($res['posting']==1){
				$icon="images/icons/04/16/02.png";
				$title="Posted";
				$unpost='';
				
				$isi="<td></td><td></td>";
                $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
				
				echo $isi;
			}else{
				$sTraksi="select distinct kodetraksi from ".$dbname.".vhc_5master where kodevhc='".$res['kodevhc']."'";
				$res1=$owlPDO->query($sTraksi) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_ASSOC);           
				$rTraksi=$res1->fetch();

				echo"
				<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$res['notransaksi']."','".$res['jenisvhc']."','".tanggalnormal($res['tanggal'])."','".$res['jenisbbm']."','".$res['jlhbbm']."','".$res['kodeorg']."','".$rTraksi['kodetraksi']."','".$res['kodevhc']."');\"></td>
				<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('". $res['notransaksi']."');\" >	</td>
				
				<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" .$res['notransaksi']. "','" . tanggalnormal($res['tanggal']) . "','" . $no . "');\" ></td>
				
				";
			}
			echo"<td align=center><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notransaksi'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">
				</td>";
			echo"<td align=center><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"preview('".$res['notransaksi']."','". $res['kodevhc']."','html');\">
				</td>";
		}
            

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
                     <tr><td colspan=16 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

	break;
	
	case'deleteKrj':
	$optKdVhc=makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc',"notransaksi = '".$no_trans."'");
	$delKrj="delete from ".$dbname.".vhc_rundt
					where notransaksi='".$no_trans."' and
					jenispekerjaan='".$jnsPekerjaan."' and
					alokasibiaya='".$Blok."' and
					kodesegment='".$segment."' and 
					beratmuatan='".$beratmuatan."'";
	try{$owlPDO->exec($delKrj); 
		updateKmHm($optKdVhc[$no_trans]);
	}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }    

	break;
	case'update_kerja':
	try {
		$owlPDO->beginTransaction();
		
		$optKdVhc=makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc',"notransaksi = '".$no_trans."'");
		
		$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a left join ".$dbname.".setup_kegiatan b on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') where a.kodekegiatan='".$jnsPekerjaan."'";
		$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC); 
		$rAlokasi = $res->fetch();
		if(($brtMuatan=='')||($jmlhRit=='')){
			throw new PDOException("Please Complete The Form");
		}
		if($rAlokasi['countkelompok'] != 0){
			if($Blok == ''){
				throw new PDOException("Blok harus dipilih.");
			}
		}

		if($Blok!=''){
			$lokKerja=$Blok;
			if(!empty($oldBlok) and $lokKerja!=$oldBlok){
				$where.=" and alokasibiaya='".$oldBlok."'";
			}else{
				if($old_lokKerja!=$lokKerja){
					$where.=" and alokasibiaya='".$old_lokKerja."'";
				} else {
					$where.=" and alokasibiaya='".$lokKerja."'";
				}
			}
		}else{
			if($old_lokKerja!=$lokKerja){
				$where.=" and alokasibiaya='".$old_lokKerja."'";
			}else{
				$where.=" and alokasibiaya='".$lokKerja."'";
			}
		}
		if($oldjnsPekerjaan!=''){
			if($jnsPekerjaan!=$oldjnsPekerjaan){
				$where.="  and jenispekerjaan='".$oldjnsPekerjaan."'";
			}else{
				$where.="  and jenispekerjaan='".$jnsPekerjaan."'";
			}
		}
		if($oldbrt_muatan!=''){
			$where.="  and beratmuatan='".$oldbrt_muatan."'";		
		}
		if(!empty($segment)) {
			$where.="  and kodesegment='".$oldSegment."'";
		}
		if($kmhmAwal>=$kmhmAkhir){
			throw new PDOException($_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']);
		}

		// Get Prev Data
		$qData = selectQuery($dbname,'vhc_rundt','*', "notransaksi='".$no_trans."' ".$where);
		$resData = fetchData($qData);

		// All Detail in Transaksi
		$qKm = selectQuery($dbname,'vhc_rundt','max(kmhmakhir) as kmakhir',"notransaksi='".$no_trans."'");
		$resKm = fetchData($qKm);
		if($resKm[0]['kmakhir']>$resData[0]['kmhmakhir'] and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
			throw new PDOException("Transaksi yang bukan terakhir tidak boleh diubah KM / HM Akhir");
		}

		// Get Header
		$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',"notransaksi = '".$no_trans."'");
		$resHead = fetchData($qHead);
		if(empty($resHead)) throw new PDOException("Data Kendaraan tidak ada");
		$resHead = $resHead[0];

		// Cek apakah kodevhc sudah ada di tanggal > tanggal input
		$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl', "kodevhc = '".$resHead['kodevhc']."' and tanggal > '".$resHead['tanggal']."'");
		$resCek = fetchData($qCek);
		if(!empty($resCek[0]['tgl']) and $kmhmAkhir!=$resData[0]['kmhmakhir']) {
				throw new PDOException("Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
						 "\nPerubahan KM / HM Akhir tidak bisa dilakukan");
		}
		if($brtMuatan==''){
			throw new PDOException("Jumlah Prestasi harus diisi");
		}
		if($jmlhRit==''){
			throw new PDOException("Jumlah Rit harus diisi.");
		}
		
		$jumlah=$kmhmAkhir-$kmhmAwal;
		$sup="update ".$dbname.".vhc_rundt set jenispekerjaan='".$jnsPekerjaan."',alokasibiaya='".$lokKerja."',beratmuatan='".$brtMuatan."',jumlahrit='".$jmlhRit."',keterangan='".$ket."',biaya='".$biaya."',kmhmawal='".$kmhmAwal."',kmhmakhir='".$kmhmAkhir."',jumlah='".$jumlah."',satuan='".$satuan."',kodesegment='".$segment."' where notransaksi='".$no_trans."' ".$where.""; #exit("error");
		$owlPDO->exec($sup); 

		$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$no_trans]."'";
		$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rKm=$res->fetch();
		updateKmHm($optKdVhc[$no_trans]);
		echo trim(intval($rKm['kmhmakhir']));
		
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Warning, " . addslashes($e->getMessage());
		die();
	}

	#exit("error");
	
	
	break;
		
	case 'getKmAkhir':
	// Get Data
	$qKm = selectQuery($dbname,'vhc_kmhm_track','*',"kodevhc='".$_POST['kodevhc']."'");
	$resKm = fetchData($qKm);
	if(empty($resKm)){
		echo 0;
	}else{
		echo trim($resKm[0]['kmhmakhir']);
	}
	
	break;
	case'insert_pekerjaan':
	try {
		$owlPDO->beginTransaction();
		
		$optKdVhc=makeOption($dbname, 'vhc_runht', 'notransaksi,kodevhc',"notransaksi = '".$no_trans."'");
		
		#cek tipe kode unit
		$dTip=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$lokKerja."'");
		
		# Get Header
		$qHead = selectQuery($dbname,'vhc_runht','tanggal,kodevhc',"notransaksi = '".$no_trans."'");
		$resHead = fetchData($qHead); #exit("error".$qHead);
		if(empty($resHead)) throw new PDOException("Data Kendaraan tidak ada");
		$resHead = $resHead[0];

		#Cek Jenis kegiatan
		$sAlokasi = "select count(b.kelompok) as countkelompok from ".$dbname.".vhc_kegiatan a left join ".$dbname.".setup_kegiatan b on a.noakun = b.noakun and b.kelompok in ('BBT', 'PNN', 'TB', 'TBM', 'TM') where a.kodekegiatan='".$jnsPekerjaan."'";
		$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rAlokasi = $res->fetch();

		if($no_trans==''){
			throw new PDOException("Notransaksi wajib terisi");
		}
		if($jnsPekerjaan==''){
			throw new PDOException("Activity required");
		}
		if($lokKerja==''){
			throw new PDOException("Cost allocation (block) required");
		}
		if($rAlokasi['countkelompok'] != 0){
			if($Blok == ''){
				throw new PDOException("Blok harus dipilih.");
			}
		}
		
		if($kmhmAwal>=$kmhmAkhir){
			throw new PDOException($_SESSION['lang']['vhc_kmhm_awal']." must lower then ".$_SESSION['lang']['vhc_kmhm_akhir']);
		}
		
		$jumlah=$kmhmAkhir-$kmhmAwal;
		$sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$no_trans."'";
		$res=$owlPDO->query($sCekHt) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);                        
		$rCekHt=owlBaris($res);
		if($rCekHt<1){
			throw new PDOException("Data Kendaraan tidak ada");
		}

		if($Blok!=''){
			if($dTip[$_POST['locationKerja']]=='KEBUN'){
				if(strlen($Blok)<10){
					throw new PDOException("Block required");
				}
			}
			$lokKerja=$Blok; 
		}

		if($biaya==''){
			$biaya=0;
		}
		if($brtMuatan==''){
			throw new PDOException("Jumlah Prestasi harus diisi");
		}
		if($jmlhRit==''){
			throw new PDOException("Jumlah Rit harus diisi.");
		}
		
		$sins="insert into ".$dbname.".vhc_rundt (`notransaksi`,`jenispekerjaan`,`alokasibiaya`,`beratmuatan`,`jumlahrit`,`keterangan`,`biaya`,`kmhmawal`,
		`kmhmakhir`,`jumlah`,`satuan`,`kodesegment`) 
		values ('".$no_trans."','".$jnsPekerjaan."','".$lokKerja."','".$brtMuatan."','".$jmlhRit."','".$ket."'
				,'".$biaya."','".$kmhmAwal."','".$kmhmAkhir."','".$jumlah."','".$satuan."','".$segment."')";
		$owlPDO->exec($sins); 

		$sKm="select distinct kmhmakhir from ".$dbname.".vhc_kmhmakhir_vw where kodevhc='".$optKdVhc[$no_trans]."'";
		$res=$owlPDO->query($sKm) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rKm=$res->fetch();
		updateKmHm($optKdVhc[$no_trans]);
		echo trim(intval($rKm['kmhmakhir']));

		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Warning, " . addslashes($e->getMessage());
		die();
	}
	
	break;
	case'getkegiatan':
		$optkdvhc=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc');
		$kelvhc=$optkdvhc[$kode_vhc];
		$wh="";
		if($kelompok!='ALL'){
			$wh=" and kelompok='".$kelompok."'";
		}
		
		$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') and (jenisvhc='".$jenis_vhc."' or jenisvhc='GLOBAL') ".$wh." order by noakun asc"; 
		$optJnsKerja="<option value=''></option>";
		$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rjnskrj=$res->fetch()){
			$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['noakun']." - ".$rjnskrj['namakegiatan']."</option>";
		}
		echo $optJnsKerja;
	break;
	case'getBlok':
		$sAlokasi = "select kelompok from ".$dbname.".vhc_kegiatan where kodekegiatan='".$jnsPekerjaan."' and tipe='traksi'";
		$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rAlokasi = $res->fetch();        
		//cek tipe
		if($rAlokasi['kelompok']=='PNN'){
			$statusblok = " and statusblok = 'TM'";
		}else if($rAlokasi['kelompok']=='TB'){
			$statusblok = " and statusblok IN ('LC','TB','TBM')";
		}else{
			$statusblok = " and statusblok = '".$rAlokasi['kelompok']."'";
		}
		
		$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		if($rAlokasi['kelompok']=='MIL'){
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk like '%".$lokKerja."%' and tipe='STATION' order by tipe desc, kodeorganisasi asc";
		}else{
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk like '%".$lokKerja."%' and (tipe='BLOK' OR tipe='BIBITAN')
			and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$lokKerja."' and luasareaproduktif>0 ".$statusblok.") 
			order by tipe desc, kodeorganisasi asc";
		}
        $res=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($rBlok=$res->fetch()){
			if($Blok!=""){
				$optBlok.="<option value=".$rBlok['kodeorganisasi']." ".($rBlok['kodeorganisasi']==$Blok?"selected":"").">".$rBlok['namaorganisasi']."</option>";
			}else{
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['kodeorganisasi']." - ".$rBlok['namaorganisasi']."</option>";
			}
        }
		$optBlok.="<option value=''>=============== PROJECT ===============</option>";
		#khusus Project:
		$str="select kode,nama from  ".$dbname.".project where kodeorg='".$lokKerja."' and posting=0";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);              
		while($bar=$res->fetch()){
			$optBlok.="<option value=".$bar->kode.">".$bar->nama."</option>";
		}

	echo $optBlok;
	// exit("Error:asd");
	break;
	case'getSatuan':
		$arrTipe=array('BBT'=>'KEBUN','KNT'=>'','MIL'=>'PABRIK','PNN'=>'KEBUN','TB'=>'KEBUN','TBM'=>'KEBUN','TM'=>'KEBUN');
		$strSat="select satuan,kelompok from ".$dbname.".`vhc_kegiatan` where  kodekegiatan='".$jnsPekerjaan."' and tipe='traksi'";
		$res=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$resSat=$res->fetch();
		$tipeOrg=" and tipe='".$arrTipe[$resSat['kelompok']]."'";
		if($arrTipe[$resSat['kelompok']]==''){
			$tipeOrg="";
		}
		$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe in ('SUPPLIERTBS') order by a.namasupplier asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		if($resSat['kelompok']=='EXT') {
			$sOrg="select * from ".$dbname.".kebun_5namakud where status='1' "; 	
			$rOrg=fetchData($sOrg);			
			foreach($rOrg as $row=>$lsDt){
				$optOrg.="<option value='".$lsDt['kodesupplier']."'>".$nmsupplier[$lsDt['kodesupplier']]."</option>";					                    
			}					
		}else{
			$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' "; 
			$rOrg=fetchData($sOrg);
			foreach($rOrg as $row=>$lsDt){
				if(substr($lsDt['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
					$optOrg.="<option value='".$lsDt['kodeorganisasi']."' selected>".$lsDt['kodeorganisasi']." - ".$lsDt['namaorganisasi']."</option>";
				}else{						
					$optOrg.="<option value='".$lsDt['kodeorganisasi']."'>".$lsDt['kodeorganisasi']." - ".$lsDt['namaorganisasi']."</option>";
				}	                    
			}					
		}
		// exit("Error:A");
		echo $resSat['satuan']."####".$optOrg;
	break;
	case'get_no_transaksi':
			$tgl=  date('Ymd');
			$bln = substr($tgl,4,2);
			$thn = substr($tgl,0,4);
			$notransaksi=$kdOrg."/RUN/".date('Y')."/".date('m')."/";
			$ql="select `notransaksi` from ".$dbname.".`vhc_runht` where notransaksi like '%".$notransaksi."%' order by `notransaksi` desc limit 0,1";
			$res=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$rp=$res->fetch();
			if(!isset($rp->notransaksi)) {
					$awal=1;
			} else {
				$awal=substr($rp->notransaksi,-4,4);
				$awal=intval($awal);

				$cekbln=substr($rp->notransaksi,-7,2);
				$cekthn=substr($rp->notransaksi,-12,4);
				if($thn!=$cekthn) {
						$awal=1;
				} else {
						$awal++;
				}
			}
		$counter=addZero($awal,4);
		$notransaksi=$kdOrg."/RUN/".$thn."/".$bln."/".$counter;
		
		$optjns="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$skary="select distinct(a.jenisvhc),b.namajenisvhc from ".$dbname.".vhc_5master a left join ".$dbname.".vhc_5jenisvhc b on a.jenisvhc=b.jenisvhc where a.status='1' and a.kodeorg='".$kdOrg."' ";#echo $skary;
		$res=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rkary=$res->fetch()){       
			$optjns.="<option value=".$rkary['jenisvhc'].">".$rkary['jenisvhc']." - ".$rkary['namajenisvhc']."</option>";
		}
		
		if($kdOrg=='')$notransaksi = '';
		echo $notransaksi."####".$optjns;
		#exit("error");
	break;
	case'getKodeVhc':
		$optKdvhc="";
		/* if($no_trans=='') {
			$sql="select kodevhc,kodetraksi,nopol,detailvhc from ".$dbname.".vhc_5master 
			  where jenisvhc='".$kode_jns."' 
			  and kodetraksi like '%".$traksi_id."%' and status=1"; //echo "warning:".$sql;
		} elseif($no_trans!='') {
			$sVhc="select jenisvhc,kodevhc from ".$dbname.".vhc_runht where notransaksi='".$no_trans."'";
			$res=$owlPDO->query($sVhc) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rVhc=$res->fetch();
			$kdVhc=$rVhc['kodevhc'];
			$sql="select kodevhc,kodetraksi,nopol,detailvhc from ".$dbname.".vhc_5master where jenisvhc='".$rVhc['jenisvhc']."' ";  //echo "warning:".$sql;
		} */
		$sql="select kodevhc,kodetraksi,nopol,detailvhc from ".$dbname.".vhc_5master where jenisvhc='".$kode_jns."' and kodetraksi like '%".$traksi_id."%' and status=1"; #echo "warning:".$sql;
		$bar=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$bar->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$bar->fetch()){
			$optKdvhc.="<option value='".$res['kodevhc']."' ".($res['kodevhc']==$kdVhc?'selected=selected':'').">".$res['kodevhc']." ".($res['nopol']!=''?"- ".$res['nopol']:'')." - ".$res['kodetraksi']." ".($res['detailvhc']!=''?"- ".$res['detailvhc']:'')."</option>";
		}
		echo $optKdvhc;
	break;
    case'insert_header':
		try {
			$owlPDO->beginTransaction();
			
			$etgl=explode("-",$tglKerja);
			$periode=$etgl[0]."-".$etgl[1];
			
			#validasi
			if($no_trans=='' or $jns_id=='' or $kode_vhc=='' or $tglKerja=='--' or $kodeOrg=='' or $jnsBbm==''){
				throw new PDOException("Semua field wajib terisi.");
			}
			
			
			#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
			if(str_replace("-","",$tglKerja)<$_SESSION['org']['period']['start']){
				throw new PDOException("Date out or range.");
			}
			
			$str="select * from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$kodeOrg."' and tutupbuku=1";
			$res = count(fetchdata($str));
			if($res>0){
				throw new PDOException("Periode sudah tutup buku.");
			}
			
			if($mode=='baru'){
				$str="select * from ".$dbname.".vhc_runht where kodevhc = '".$kode_vhc."' and tanggal = '".$tglKerja."'"; 
				if(count(fetchdata($str))>0){
					throw new PDOException("Kendaraan ".$kode_vhc." pada tanggal ".tanggalnormal($tglKerja)." sudah diinput, silahkan cari di list data dan lakukan Edit !");
				}
				
				$sqlCek="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$no_trans."'"; 
				if(count(fetchdata($sqlCek))>0){
					throw new PDOException("Nomor transaksi sudah ada");
				}
				
				#Cek apakah kodevhc sudah ada di tanggal > tanggal input
				$qCek = selectQuery($dbname,'vhc_runht','max(tanggal) as tgl',"kodevhc = '".$kode_vhc."' and tanggal > '".$tglKerja."'");
				$resCek = fetchData($qCek);
				if(!empty($resCek[0]['tgl'])) {
					throw new PDOException("Kendaraan sudah ada transaksi di tanggal yang lebih besar.".
					"\nTanggal transaksi terakhir ".tanggalnormal($resCek[0]['tgl']));
				}
				
				#=== insert header ===
				$data = array();
				$data = array(
					'notransaksi' => $no_trans,
					'kodeorg' => $kodeOrg,
					'jenisvhc' => $jns_id,
					'kodevhc' => $kode_vhc,
					'tanggal' => $tglKerja,
					'jenisbbm' => $jnsBbm,
					'jlhbbm' => $jumlah,
					'updateby' => $_SESSION['standard']['userid'],
					'createdtime' => date('Y-m-d H:i:s')
				);
				
				$cols = array();
				foreach($data as $key=>$row) {
						$cols[] = $key;
				}
				$str = insertQuery($dbname,'vhc_runht',$data,$cols);
				$owlPDO->exec($str);
				
			}
			

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
		
		
		$optkdvhc=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc');
		$kelvhc=$optkdvhc[$kode_vhc];
		
		$sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' and (kelompokvhc='".$kelvhc."' or kelompokvhc='GLOBAL') and (jenisvhc='".$kode_jns."' or jenisvhc='GLOBAL') order by noakun asc";
		$optJnsKerja="<option value=''></option>";
		$res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rjnskrj=$res->fetch()){
			$optJnsKerja.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['noakun']." - ".$rjnskrj['namakegiatan']."</option>";
		}
		
		$kel = getEnum($dbname,'vhc_kegiatan','kelompok');
		$optkelompok="<option value='ALL'>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($kel as $key){
			$optkelompok.="<option value=".$key.">".$key."</option>";
		}
		
		$arrOpt=array("KM","HM");
		$optSatuanvhc='';
		foreach($arrOpt as $brs => $isi){
			$optSatuanvhc.="<option value=".$isi.">".$isi."</option>";
		}
		
		$optLokTugas='';
		$slokTgs="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi 
		IN (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$_SESSION['empl']['regional']."' )
		AND `tipe` NOT IN ('PT', 'BLOK', 'STATION', 'STENGINE','AFDELING')";

		$res=$owlPDO->query($slokTgs) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rlokTgs=$res->fetch()){
			if(substr($rlokTgs['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
				$optLokTugas.="<option value=".$rlokTgs['kodeorganisasi']." selected>".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
			}else{
				$optLokTugas.="<option value=".$rlokTgs['kodeorganisasi'].">".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
			}	
		}
		
		$frm[0]="";
		
		$frm[0].="<fieldset><legend>".$_SESSION['lang']['vhc_detail_pekerjaan']."</legend>";
		$frm[0].="<table cellspacing=1 border=0>
		
		<tr>
			<td>Kelompok</td>
			<td>:</td>
			<td colspan=22><select id=kelompok style=width:232px; onchange=\"getkegiatan(this.value)\">".$optkelompok."</select></td>
		</tr>
		
		<tr>
			<td>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
			<td>:</td>
			<td colspan=22><select id=jns_kerja name=jns_kerja onchange=getSatuan(this.value) style=width:232px;>".$optJnsKerja."</select><input type=hidden name=old_jnskerja id=old_jnskerja />
			<img id='jns_kerja' onclick=z.elSearch('jns_kerja',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
			</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['lokasi']."</td>
			<td>:</td>
			<td colspan=22><select id=lokasi_kerja name=lokasi_kerja  style=width:232px; onchange=\"getBlok('','')\"><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optLokTugas."</select> <input type=hidden name=old_lokkerja id=old_lokkerja /></td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['blok']."</td>
			<td>:</td>
			<td colspan=22><select id=blok name=blok style=width:232px; ><option value=''></option></select>&nbsp;<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
			<td>*Jika pekerjaan dilakukan di Kebun (Obligatory if activity location on estate)<td>
			<td> <input type=hidden name=old_blok id=old_blok /></td>
		</tr>


		<tr style='display:none'>
			<td>".$_SESSION['lang']['segment']."</td>
			<td>:</td>
			<td colspan=500><input type=hidden name=oldSegment id=oldSegment />".makeElement('kodesegment','searchSegment')."</td>
		</tr>


		<tr>
			<td>".$_SESSION['lang']['jumlahrit']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber id=jmlh_rit name=jmlh_rit maxlength=6 onclick=\"this.select();getKmAkhir();\" onkeypress=\"return angka_doang(event);\" style=width:85px; /></td>
			
			<td>".$_SESSION['lang']['prestasi']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber id=brt_muatan name=brt_muatan maxlength=6 onkeypress=\"return angka_doang(event);\" onclick=\"this.select();getKmAkhir();\" style=width:80px; />&nbsp;<span id='satuan'></span>
			<input hidden type=text class=myinputtextnumber id=oldbrt_muatan name=oldbrt_muatan maxlength=6 onkeypress=\"return angka_doang(event);\" style=width:80px; />
			</td>
			<td colspan=2>
				<span id='satuan'>
			</td>
		</tr>


		<tr>
			<td>".$_SESSION['lang']['vhc_kmhm_awal']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber id=kmhm_awal name=kmhm_awal maxlength=8 onkeypress=\"return angka_doang(event);\" style=width:85px; /></td>

			<td>".$_SESSION['lang']['akhir']."</td>
			<td>:</td>
			<td><input type=text class=myinputtextnumber id=kmhm_akhir name=kmhm_akhir maxlength=8  onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" style=width:80px; /></td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['satuan']."</td>
			<td>:</td>
			<td><select id=stn name=stn style=width:89px;>".$optSatuanvhc."</select></td>

			<td style='display:none'>".$_SESSION['lang']['biaya']." Rp</td>
			<td style='display:none'>:</td>
			<td style='display:none'><input type=text class=myinputtextnumber id=biaya name=biaya maxlength=45 onkeypress=\"return angka_doang(event);\" style=width:80px; /></td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td colspan=22><input type=text class=myinputtext id=ket name=ket maxlength=45 onkeypress=\"return tanpa_kutip(event);\" style=width:228px; /></td>
		</tr>


		<tr>
			<td><td><td colspan=6>	
				<button class=mybutton onclick=save_pekerjaan() >".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=bersih_form_pekerjaan() >".$_SESSION['lang']['cancel']."</button>
				<button class=mybutton title=\"Refresh Data Tersimpan\" onclick=load_data_pekerjaan() >Refresh</button>
				<input type=hidden id=proses_pekerjaan name=proses_pekerjaan value=insert_pekerjaan />

		</table>";

		$frm[0].="</fieldset>";
		
		#=== List data tersimpan input detail ===	
        $frm[0].="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['vhc_detail_pekerjaan'] . "</legend>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
		
		
		//karyawan
		$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$arrPos=array("Operator","Helper","Sopir");
		$optPosition='';
		foreach($arrPos as $brs => $isi){
			$optPosition.="<option value=".$brs.">".$isi."</option>";
		}
		
		$skary="select distinct(a.karyawanid),a.nama,b.nik from ".$dbname.".vhc_5operator a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.aktif='1' and b.lokasitugas='".$kodeOrg."' ";//echo $skary;
		$res=$owlPDO->query($skary) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rkary=$res->fetch()){       
			$optKary.="<option value=".$rkary['karyawanid'].">".$rkary['nama']."&nbsp;[".$rkary['nik']."]</option>";
		}
		
		$frm[1]="<fieldset><legend>".$_SESSION['lang']['vhc_detail_operator']."</legend>";
		$frm[1].="<table cellspacing=1 border=0>

		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td><input type=text id=no_trans_opt name=no_trans_opt disabled=disabled class=myinputtext style=width:150px; /></td>
			
		</tr>

		<tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>:</td>
			<td><select id=kode_karyawan name=kode_karyawan style=width:154px; onchange=getUmr();>".$optKary."</select></select>
			<img id='kode_karyawan' onclick=z.elSearch('kode_karyawan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['vhc_posisi']."</td>
			<td>:</td>
			<td><select id=posisi name=posisi onchange=getPremi(); style=width:154px;>".$optPosition."</select> &nbsp; => Pengisian <b>Operator</b> dan <b>Helper</b> mempengaruhi <b>nilai Premi</b></td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['upahkerja']."</td>
			<td>:</td>
			<td><input type=text id=uphOprt name=uphOprt  class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' /> &nbsp; => Harus di isi untuk karyawan internal (Obligatory if internal operator used)</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['premi']."</td>
			<td>:</td>
			<td><input type=text id=prmiOprt onfocus=getPremi();  name=prmiOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)'  value=0 /> &nbsp; => Jika Hari Libur, maka nilai Premi = (Upah + Premi)</td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['rupiahpenalty']."</td>
			<td>:</td>
			<td><input type=text id=pnltyOprt name=pnltyOprt class=myinputtextnumber style=width:150px; onkeypress='return angka_doang(event)' value=0 /></td>
		</tr>

		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=ketOprt name=ket maxlength=45 onkeypress=\"return tanpa_kutip(event);\" style=width:150px; />
			</td>
		</tr>

		<tr><td><td><td>
			<button class=mybutton onclick=save_operator() >".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=clear_operator() >".$_SESSION['lang']['cancel']."</button>
			<button class=mybutton id=tomboldetailpremi onclick=detailpremi() >".$_SESSION['lang']['detail']."</button>
			<input type=hidden name=prosesOpt id=prosesOpt value=insert_operator />

		</td></tr>
		</table>";

		$frm[1].="</fieldset>";
		$frm[1].="<fieldset id=contdetailpremi style=display:none>	
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table cellspacing=1 border=0 class=sortable >
				<thead>
				<tr class=\"rowheader\">
				<td align=center rowspan=2>No.</td>
				<td align=center rowspan=2>".$_SESSION['lang']['kegiatan']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['satuan']."</td>
				<td align=center rowspan=2>".$_SESSION['lang']['prestasi']."</td>
				<td align=center rowspan=2>HM/KM</td>
				<td align=center colspan=2>Rp / Sat</td>
				<td align=center colspan=2>Rupiah</td>
				<td align=center rowspan=2>Total Rupiah</td>
				</tr><tr class=\"rowheader\">
				<td align=center >Pres</td>
				<td align=center >HM/KM</td>
				<td align=center >Pres</td>
				<td align=center >HM/KM</td>
				</tr></thead><tbody id=containDetailOperator>
				";
		$frm[1].="</tbody></table></fieldset>";

		$frm[1].="<fieldset><legend>".$_SESSION['lang']['datatersimpan']."</legend><table cellspacing=1 border=0 class=sortable >
				<thead>
				<tr class=\"rowheader\">
				<td align=center >No.</td>
				<td align=center >".$_SESSION['lang']['notransaksi']."</td>
				<td align=center >".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center >".$_SESSION['lang']['vhc_posisi']."</td>
				<td align=center >".$_SESSION['lang']['upahkerja']."</td>
				<td align=center >".$_SESSION['lang']['upahpremi']."</td>
				<td align=center >".$_SESSION['lang']['rupiahpenalty']."</td>
				<td align=center >".$_SESSION['lang']['keterangan']."</td>
				<td align=center >Action</td>
				</tr></thead><tbody id=containOperator>
				<script>//load_data_operator()</script>
				";
		$frm[1].="</tbody></table></fieldset>";
		
		$hfrm[0]=$_SESSION['lang']['vhc_detail_pekerjaan'];
		$hfrm[1]=$_SESSION['lang']['vhc_detail_operator'];
		drawTab('FRM',$hfrm,$frm,200,'100%');
	
		// #=== insert header ===
        // // $tab=OPEN_BOX();
		// #==== Form Judul Detail ====
		// $tab="";
        // $tab.="<fieldset><legend>" . $_SESSION['lang']['kegiatan'] . "</legend>
			// <table border=0 cellpadding=1 cellspacing=1 class=sortable >
			// <thead><tr class=rowheader>";
		// $tab.="<td align=center width=20px>No</td>
			// <td align=center>xxxx</td>";
			
				
		// $tab.="</tr>
			// </thead>";
		// #==== Form Judul Detail ====
		
		// // #=== Isi input detail ===
		// // $tab.="<tbody id=inputdetail>
				// // <script>inputdetail()</script>
			// // </tbody></table></fieldset>";
		
		// #=== List data tersimpan input detail ===	
        // $tab.="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			// <div id=loaddatadetail>
				// <script>loaddatadetail()</script>
			// </div></fieldset>";
        // // $tab.=CLOSE_BOX();
		//echo $tab;	
		
	break;
	
	case'insert_operator':
        $sCekHt="select notransaksi from ".$dbname.".vhc_runht where notransaksi='".$notrans."'";
        $res=$owlPDO->query($sCekHt) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);            
        $rCekHt=owlBaris($res);
        if($rCekHt<1){
                echo"warning: Header required";
                exit();
        }

        $sPeriode="select periode from ".$dbname.".sdm_5periodegaji where kodeorg='".substr($rKode['kodeorg'],0,4)."' and periode='".substr($tglTrans,0,4)."-".substr($tglTrans,4,2)."'";# tanggalmulai<".$tglTrans." and tanggalsampai>=".$tglTrans;
        $res=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rPeriode=$res->fetch();
        if($rPeriode['periode']=='')
        {
        echo"warning: Transaction date out of range";
        exit();
        }
		
		/*
		$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($kdKry != $bar['karyawanid']){
			$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$kdKry."'");
			$optNIK = makeOption($dbname,"datakaryawan",'karyawanid,nik',"karyawanid='".$kdKry."'");
			
			echo "Warning : Absen fingerprint untuk karyawan dg NIK : \n".$optNIK[$kdKry]." = ".$optNamaKaryawan[$kdKry]."\nbelum diupload.";
			exit;
		}
		*/
		
		$sKd="select lokasitugas,subbagian from ".$dbname.".datakaryawan where karyawanid='".$kdKry."'";
		$res=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);                
		$rKd=$res->fetch();
		$lokasiTugas=$rKd['lokasitugas'];
		if(!is_null($rKd['subbagian'])||$rKd['subbagian']!=0||$rKd['subbagian']!=''){
		   $lokasiTugas=$rKd['subbagian'];
		}

		#cek absensi umum
		$str = "select count(*) as jumabs from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumabs=$bar['jumabs'];
			
		if($jumabs>0){
			exit("Warning:Karyawan ditanggal ".tanggalnormal($tglTrans)."  sudah terdaftar di absensi umum, silahkan dihapus dahulu absensi umumnya");
		}

		#cek di BKM
		$str = "select notransaksi, sum(jhk) as jhk, sum(umr) as umr from ".$dbname.".kebun_kehadiran_vw where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jmlhkbkm=$bar['jhk'];
			$jmlumrbkm=$bar['umr'];
			$notransbkm=$bar['notransaksi'];
			
		if(($jmlhkbkm>0||$jmlumrbkm>0) && $uphOprt>0){
			exit("Warning: Karyawan sudah terdaftar pada Keg BKM dengan no transaksi ".$notransbkm."");
		}
		
		#cek nilai umr
		$str = "select * from ".$dbname.".sdm_5gajipokok where karyawanid='".$kdKry."' and tahun='".substr($tglTrans,0,4)."' and idkomponen='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$gajipokok=$bar['jumlah']/25;
		
		if($uphOprt>$gajipokok){
			exit("Warning : Nilah upah lebih besar dari nilai UMR / Hari, maksimal nilai upah = Rp. ".$gajipokok."");
		}
		
		#cek di panen
		$str = "select count(*) as kegpanen, notransaksi from ".$dbname.".kebun_prestasi_vw where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jmlhkkegpnn=$bar['kegpanen'];
			$notranskegpnn=$bar['notransaksi'];
			
		if($jmlhkkegpnn>0 && $uphOprt>0){
			exit("Warning: Karyawan sudah terdaftar pada Keg Panen dengan no transaksi ".$notranskegpnn."");
		}
				
		#cek jika hari itu sudah ada upah dihari itu
		$str = "select count(*) as jumkar, notransaksi from ".$dbname.".vhc_runhk_vw where idkaryawan='".$kdKry."' and tanggal='".$tglTrans."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumtrans=$bar['jumkar'];
			$notr=$bar['notransaksi'];
			
		if($jumtrans>0 and $uphOprt>0){
			exit("Warning : Upah karyawan sudah terdaftar ditransaksi lain dengan nomor ".$notr.", anda hanya diperbolehkan menginput premi dengan umr 0");
		}			
		
		
		$day = date('D', strtotime($tglTrans));
		if($day=='Sun')$libur=true; else $libur=false;
		// kamus hari libur
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tglTrans."' and (kebun='GLOBAL' or kebun='".substr($notransaksi_head,0,4)."')";
		
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		while($roworg=$queorg->fetch()){
			if($roworg['keterangan']=='libur')$libur=true;
			if($roworg['keterangan']=='masuk')$libur=false;
		}
		
		if($libur==true and $uphOprt>0){
			exit("Warning:Jika Hari libur/minggu maka nilai upah harus 0, upah ditambahkan ke premi");
		}
		#======================= cek premi apakah lebih besar dari perhitungan ==================
		$param=$_POST;
		#$totalpremi = hitungpremi($param);
		$totalpremi = 10000000;
		
		$str="select sum(premi) as premi from ".$dbname.".vhc_runhk where notransaksi='".$_POST['notrans']."' and posisi='".$posisi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
		while($bar=$res->fetch()){
			$cekpremi=$bar['premi'];
		}
			$cekpremi=$cekpremi+$_POST['prmiOprt'];		
		
		if($cekpremi > $totalpremi and $libur==false){
			exit('Error : Total nilai premi yg di input : '.number_format($cekpremi).' lebih besar dari total premi yg seharusnya di dapat : '.number_format($totalpremi));
		}
		
		#======================= cek premi apakah lebih besar dari perhitungan ==================
		
		#insert vhc_runhk
		$str="insert into ".$dbname.".vhc_runhk (`notransaksi`,`idkaryawan`,`posisi`,`tanggal`,`statuskaryawan`,`upah`,`premi`,`penalty`,`keterangan`)
				values ('".$notransaksi_head."','".$kdKry."','".$posisi."','".$tglTrans."','','".$uphOprt."','".$prmiOprt."','".$pnltyOprt."','".$ketOprt."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Gagal Tersimpan:" . $e->getMessage() . "\n".$str;
		}   

		#hapus dahulu jika ada diabsensi umum	
		$str="delete from ".$dbname.".sdm_absensidt where karyawanid='".$kdKry."' and tanggal='".$tglTrans."' ";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			 $errorDB .= " Gagal Menghapus absensi umum :" . $e->getMessage() . "\n".$str;
		}
		
		
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}
    break;
        
        case 'update_operator':
		$str = "select karyawanid from ".$dbname.".upload_absensi where karyawanid='".$kdKry."' and tanggalabsen='".tanggalsystem($tglTrans)."' limit 1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($kdKry != $bar['karyawanid']){
			$optNamaKaryawan = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$kdKry."'");
			echo "Warning : Absen untuk nik : \n".$kdKry." = ".$optNamaKaryawan[$kdKry]."\nbelum diupload.";
			exit;
		}
		
        if($posisi==1)
        {
				$str="select idkaryawan from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
				$idkaryawanlama = $bar['idkaryawan'];
			
                $sCek="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='1'";
                $res=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=$res->fetch();
        }
        elseif($posisi==0)
        {
				$str="select idkaryawan from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
				$idkaryawanlama = $bar['idkaryawan'];
			
                $sCekSop="select count(posisi) as jmlh from ".$dbname.".vhc_runhk where notransaksi='".$notransaksi_head."' and posisi='0'";
                $res=$owlPDO->query($sCekSop) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $rCekSop=$res->fetch();
        }
        if($rCek['jmlh']>4)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        if($rCekSop['jmlh']>1)
        {
                echo"warning: Can`t complete transaction, Operator maximum limit exeed";
                exit();
        }
        $skry="select a.`alokasi`,b.tipe from ".$dbname.".datakaryawan a inner join ".$dbname.".sdm_5tipekaryawan b on 
        a.tipekaryawan=b.id where karyawanid='".$kdKry."'"; 
        $res=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $rkry=$res->fetch();


        $sup_op="update ".$dbname.".vhc_runhk set posisi='".$posisi."',tanggal='".$tglTrans."',statuskaryawan='".$rkry['tipe']."',upah='".$uphOprt."',premi='".$prmiOprt."',penalty='".$pnltyOprt."' where notransaksi='".$notransaksi_head."' and idkaryawan='".$kdKry."'";
        try{$owlPDO->exec($sup_op); echo"";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 0 where karyawanid='".$idkaryawanlama."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}		
		
		$strAbs = "update ".$dbname.".upload_absensi set flag = 1 where karyawanid='".$kdKry."' and tanggalabsen='".$tglTrans."'";
		try{
			$owlPDO->exec($strAbs); 
		}
		catch (PDOException $e) 
		{
			 $errorDB .= " Error update flag absensi gagal:" . $e->getMessage() . "\n".$strAbs;
		}
        break;
		
	case'getUmr':
		
            if($_POST['tahun']!='')
                    $tahun=$_POST['tahun'];
            else {
                    $tahun=date('Y');
            }
			
		
		if($kdKry!=''){
			$sUmr="select sum(jumlah) as jumlah from ".$dbname.".sdm_5gajipokok 
				where karyawanid='".$kdKry."' and tahun=".$tahun."  and idkomponen ='1'";
			$res=$owlPDO->query($sUmr) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rUmr=$res->fetch();
			$umr=$rUmr['jumlah']/25;
			
			if($rUmr['jumlah']==''){
				exit("Error : Gaji Pokok untuk tahun ".$tahun." belum ada !");
			}
		}
		
			#hari minggu
			$day = date('D', strtotime($tglTrans));
			if($day=='Sun')$libur=true; else $libur=false;
			// kamus hari libur
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tglTrans."' and 
				(kebun='GLOBAL' or kebun='".substr($kodetraksi,0,4)."')";
			$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
			$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
			while($roworg=$queorg->fetch()){
				if($roworg['keterangan']=='libur')$libur=true;
				if($roworg['keterangan']=='masuk')$libur=false;
			}
		
			if($libur==true){
				@$umr=0;
			}else{
				@$umr=$umr;
			}
		
		
		
        echo $umr;
        break;
	case 'load_data_kerjaan':
		$optkdorg=makeOption($dbname,'vhc_runht','notransaksi,kodeorg',"notransaksi='".$no_trans."'");
		$nmpek = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
		echo "<table cellspacing=1 border=0  class=sortable>
				<thead>
				<tr class=\"rowheader\">
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['vhc_jenis_pekerjaan']."</td>
				<td align=center>".$_SESSION['lang']['alokasibiaya']."</td>
				<td align=center style='display:none'>".$_SESSION['lang']['segment']."</td>
				<td align=center>".$_SESSION['lang']['jumlahrit']."</td>
				<td align=center>".$_SESSION['lang']['prestasi']."</td>
				<td align=center>".$_SESSION['lang']['vhc_kmhm_awal']."</td>
				<td align=center>".$_SESSION['lang']['vhc_kmhm_akhir']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center style='display:none'>".$_SESSION['lang']['biaya']." (Rp.)</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>Action</td>
				</tr></thead><tbody>";
		
        $sql="select a.*,b.namasegment from ".$dbname.".vhc_rundt a left join ".$dbname.".keu_5segment b on a.kodesegment=b.kodesegment where substring(notransaksi,1,4)='".$optkdorg[$no_trans]."' and notransaksi='".$no_trans."' order by kmhmawal asc"; //echo $sql;
        $no=0;
        $res1=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);        
        while($res=$res1->fetch()){
			$no+=1;
			echo"
			<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$res['notransaksi']."</td>
			<td>".$res['jenispekerjaan']."-".$nmpek[$res['jenispekerjaan']]."</td>
			<td>".$res['alokasibiaya']."</td>
			<td style='display:none'>".$res['namasegment']."</td>
			<td align=right>".number_format($res['jumlahrit'],2)."</td>
			<td align=right>".number_format($res['beratmuatan'],2)."</td>
			<td align=right>".number_format($res['kmhmawal'],2)."</td>
			<td align=right>".number_format($res['kmhmakhir'],2)."</td>
			<td align=center>".$res['satuan']."</td>
			<td align=right style='display:none'>".number_format($res['biaya'],2)."</td>
			<td>".$res['keterangan']."</td>
			<td><img src=images/application/application_edit.png class=resicon  title='Edit' 
			onclick=\"fillFieldKrj('".$res['jenispekerjaan']."','".$res['alokasibiaya']."','". $res['beratmuatan']."','". $res['jumlahrit']."','". $res['keterangan']."','". $res['biaya']."','". $res['kmhmawal']."','". $res['kmhmakhir']."','". $res['satuan']."','".$res['kodesegment']."','".$res['namasegment']."');\">
			<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDataKrj('". $res['notransaksi']."','". $res['jenispekerjaan']."','".$res['alokasibiaya']."','".$res['kodesegment']."','".$res['beratmuatan']."');\" >	
			</td>
			</tr>
			";
        }
		echo "</tbody></table>";
	break;
	#====================================================================================================================


	
	case'inputdetail':
	
	
	echo"<tr class=rowcontent id=row>";
		echo"<td id=no align=center>1</td>
			<td><select style=width:150px onchange=getDataDetail() id=karyawanid>".$optKary."</select></td>
			<td style=width:20px><img id='karyawanid' onclick=z.elSearch('karyawanid',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			
			<td><select style=width:95px onchange=getDataDetail() id=blok>".$optBlok."</select></td>
			<td style=width:20px><img id='blok' onclick=z.elSearch('blok',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
			</td>
			<td><input id=tt disabled class=myinputtextnumber style=\"width:35px;\">
				<input id=bjr style=display:none></td>
			<td><input id=hapanen onkeyup=\"z.numberFormat('hapanen',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:40px;\"></td>
			<td><input id=jjgpanen onkeyup=\"getDataDetail()\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			<td><input id=brdpanen nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
			<td><input id=kgpanen disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=upah disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=basis onkeyup=\"z.numberFormat('basis',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>
			<td hidden><input id=lbasis onkeyup=\"z.numberFormat('lbasis',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>";
			
			#denda detail input
			foreach($dendapanen as $iddenda => $kddenda){
				echo"<td style=display:none id=pd".$iddenda."><input ".$tp[$iddenda]." id=penalti".$iddenda." onkeyup=getHitungDenda(0,this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
			}
			
			echo"<td><input id=denda_rp disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
			
			<td align=center><input type=hidden id=method value='insert'>
				
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
			</td>
        </tr><tr>
			<td colspan=10>
			<td id=pfot colspan=".count($dendapanen).">
			<td colspan=2 align=right>
			<input id=jlhbrs style=display:none>
			<img title='Refresh' style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
			<img id=done title='" . $_SESSION['lang']['selesai']."' style=vertical-align:center;width:13px;height:13px;cursor:pointer onclick=\"displayList()\" src='images/foldoq.png'/>
        </tr>";
	break;
	
	
	case'getdata':
		echo $optKary."######".$optBlok;
	break;
	
	case'getdatamandor':
	$whereKary='';
	$whereKary.= " and tipekaryawan in (1,2,3,4,6) and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tanggalsystemn($tgl)."')";
	
	$str = "select a.karyawanid,b.namakaryawan,b.nik, b.subbagian from ".$dbname.".kebun_5mandor a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where statusaktif='1' and mandorid='".$mandor."' ".$whereKary." order by b.namakaryawan asc";
	$count=fetchData($str);
	$tab='';
	if(count($count)==0){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=23>Pastikan kolom <b>Mandor</b> pada Header terisi, dan atau daftarkan terlebih dahulu nama karyawan per kemandoran melalui menu : <b>Kebun - Setup - Mandor</b></td>";
		$tab.="</tr>";
	}
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no='';
	while($bar=$res->fetch()){
		$no++;
		$tab.="<tr class=rowcontent id=row".$no.">";
		$tab.="	<td align=center>".$no."</td>
		<td style=display:none><input id=karyawanid".$no." value=".$bar['karyawanid']."></td>
		<td id=kary".$no." colspan=2>".$bar['nik']." - ".$bar['namakaryawan']."</td>
		
		<td style=width:95px><select style=width:95px onchange=\"getDataDetail(".$no.")\" id=blok".$no.">".@$optBlok."</select></td>
		<td style=width:20px><img id='blok".$no."' onclick=z.elSearch('blok".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td><input id=tt".$no." disabled class=myinputtextnumber style=\"width:35px;\">
			<input id=bjr".$no." style=display:none></td>
		<td><input id=hapanen".$no." onkeyup=\"z.numberFormat('hapanen".$no."',2)\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		<td><input id=jjgpanen".$no." onkeyup=\"getDataDetail(".$no.")\" nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		<td><input id=brdpanen".$no." nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:50px;\"></td>
		<td><input id=kgpanen".$no." disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=upah".$no." disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=basis".$no." onkeyup=\"z.numberFormat('basis".$no."',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>
		<td hidden><input id=lbasis".$no." onkeyup=\"z.numberFormat('lbasis".$no."',2)\" disabled class=myinputtextnumber style=\"width:50px;\"></td>";
		
		#denda detail input
		foreach($dendapanen as $iddenda => $kddenda){
			$tab.="<td style=display:none id=pd".$iddenda."".$no."><input ".$tp[$iddenda]." id=penalti".$iddenda."".$no." onkeyup=getHitungDenda(".$no.",this) nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:30px;\"></td>";
		}
		
		$tab.="<td><input id=denda_rp".$no." disabled nkeypress=\"return_tanpa_kutip(event);\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" style=\"width:45px;\"></td>
				
		<td align=center><input type=hidden id=method value='insert'>
					
			<img title='" . $_SESSION['lang']['save']."' class=zImgBtn onclick=\"savedetail(".$no.")\" src='images/save.png'/>
			<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail(".$no.")\" src='images/clear.png'/>
		</td>
	</tr>";
	}
	$tab.="<tr>
		
		<td colspan=10>
		<td id=pfot colspan=".count($dendapanen).">
		<td colspan=2 align=right>
		<input id=jlhbrs  style=display:none value=".$no.">
		<img title='Refresh' style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
		<img title='" . $_SESSION['lang']['saveall']."' class=zImgBtn onclick=\"saveAll(".$no.")\" src='images/save.png'/>
		<img title='" . $_SESSION['lang']['selesai']."' class=zImgBtn onclick=displayList() src=\"images/foldoq.png\"/>
		</td>
	</tr>";
	
	echo $tab."######".$no;
	break;
	
	case'loaddatadetail':
	
	$rows="rowspan=2";	
	$tab="<table id=tabledt cellpadding=1 cellspacing=1 border=0 class=sortable >
			<thead><tr class=rowheader>
			<td align=center ".$rows." width=20px>No</td>
			<td align=center ".$rows.">".$_SESSION['lang']['nik2']." - ".$_SESSION['lang']['divisi']." - ".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center ".$rows.">".$_SESSION['lang']['blok'] . "</td>
			<td align=center ".$rows." width=30px>".$_SESSION['lang']['tahuntanam'] . "</td>
			<td hidden align=center ".$rows." width=30px>Kontanan</td>
			<td align=center colspan=4>".$_SESSION['lang']['hasilkerja2'] . "</td>
			<td hidden align=center ".$rows.">".$_SESSION['lang']['upah']."</td>
			<td hidden align=center colspan=2>".$_SESSION['lang']['premilebihbasis']."</td>
			<td align=center colspan=".count($dendapanen)." style=display:none id=pheaddt title='Click to Hide' onclick=hidedendadt('".count($dendapanen)."') ><font color=Orange><b><u>".$_SESSION['lang']['denda']."</font></b></u></td>
			<td align=center ".$rows." title='Click to Unhide' onclick=unhidedendadt('".count($dendapanen)."') width=50px><font color=Orange><u><b>".$_SESSION['lang']['denda']." Rp</b></u></font></td>
			
			<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
		</tr>
		<tr>
			<td align=center>".$_SESSION['lang']['ha'] . "</td>
			<td align=center>".$_SESSION['lang']['jjg'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['brondol'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['kg'] . "</td>
			<td hidden align=center>".$_SESSION['lang']['basic'] . "</td>
			<td hidden align=center width=50px>".$_SESSION['lang']['lebihbasis'] . "</td>";
			
			#denda header list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<td align=center ".$tp[$iddenda]." width=30px style=display:none id=pdt##".$iddenda.">".$kddenda."</td>";
			}
			
		$tab.="</tr>
		</thead>";
		
        $no = 0;
        $str = "select a.*,b.namakaryawan,b.nik as nik2, b.subbagian from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' order by b.namakaryawan asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$bgcolor=$title=$color='';
				$strx = "select count(nik) as jmlkary, nik from " . $dbname . ".kebun_prestasi where notransaksi='".$bar['notransaksi']."' and nik='".$bar['nik']."' group by nik";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$barx = $resx->fetch();
				if(($bar['nik']==$barx['nik']) and ($barx['jmlkary']>1)){
					$bgcolor="style=background-color:orange;";
					$title=" title = 'Karyawan Panen lebih dari 1 blok !'";
				}
				if($bar['subbagian']!=substr($bar['kodeorg'],0,6)){
					$color="style=background-color:red;cursor:pointer; title='Info : Karyawan berbeda divisi, tapi jika ada Asistensi maka abaikan pesan ini !' ";
				}
				$no+=1;
				$align=" align=right ";
				$nn=" style=display:none ";
				$tab.="<tr class=rowcontent ".$bgcolor."".$title.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left ".$color.">" . $bar['nik2'] . " - " . $bar['subbagian']. " - " . $bar['namakaryawan'] . "</td>";
				$tab.="<td align=center>" . $nmorg[$bar['kodeorg']]. "</td>";
				$tab.="<td align=center>" . $bar['tahuntanam'] . "</td>";
				$tab.="<td hidden align=center>" . $bar['keterangan'] . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['luaspanen'],2) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['hasilkerja']) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['brondolan']) . "</td>";
				$tab.="<td align=right>" . @hidezerodecimal($bar['hasilkerjakg']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahkerja']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahpremi']) . "</td>";
				$tab.="<td hidden align=right>" . @hidezerodecimal($bar['upahpremilebihbasis']) . "</td>";
				
				#denda list data
				$edit="";
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td ".$align." ".$nn." ".$tplistdata[$iddenda]."\nRp => ".$bar['penalti'.$iddenda]." x ".$harga[$iddenda]." = ".@hidezerodecimal($bar['penalti'.$iddenda]*$harga[$iddenda])." \" width=30px id=pddt##".$iddenda."##".$no.">".@hidezerodecimal($bar['penalti'.$iddenda])."</td>";

					@$ttlp[$iddenda]+=$bar['penalti'.$iddenda];
					$edit.="####".$bar['penalti'.$iddenda];
				}
				
				$jlhdenda=count($dendapanen);
				$tab.="<td align=right>" . @hidezerodecimal($bar['rupiahpenalty']) . "</td>";
				
				@$tluas+=$bar['luaspanen'];
				@$tjjg+=$bar['hasilkerja'];
				@$tbrd+=$bar['brondolan'];
				@$tkg+=$bar['hasilkerjakg'];
				@$tupah+=$bar['upahkerja'];
				@$tpbss+=$bar['upahpremi'];
				@$tplb+=$bar['upahpremilebihbasis'];
				@$trrp+=$bar['rupiahpenalty'];
				
			$tab.="<td align=center>";
			$tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
					onclick=\"editdetail('".$bar['notransaksi']."','".$bar['nik']."','".$bar['kodeorg']."','".$bar['tahuntanam']."','".$bar['luaspanen']."','".$bar['hasilkerja']."','".$bar['brondolan']."','".$bar['hasilkerjakg']."','".$bar['upahkerja']."','".$bar['upahpremi']."','".$bar['upahpremilebihbasis']."','".$bar['rupiahpenalty']."','".$bar['keterangan']."','".$no."','".$jlhdenda."','".$edit."');\" >
					
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['nik'] . "','" . $bar['kodeorg'] . "');\" >
					
					</td>";
			}
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=4 bgcolor=cyan align=center>
				   <input value=".$no." style=display:none id=jlhbrsdt><b>TOTAL</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@hidezerodecimal($tluas,2)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_Format($tjjg)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_Format($tbrd)."</b></td>";
			$tab.="<td bgcolor=cyan align=right><b>".@number_Format($tkg)."</b></td>";
			$tab.="<td hidden bgcolor=cyan align=right><b>".@number_Format($tupah)."</b></td>";
			$tab.="<td hidden bgcolor=cyan align=right><b>".@number_Format($tpbss)."</b></td>";
			$tab.="<td hidden bgcolor=cyan align=right><b>".@number_Format($tplb)."</b></td>";
			#ttl denda list data
			foreach($dendapanen as $iddenda => $kddenda){
				$tab.="<td bgcolor=cyan ".$align." ".$nn." ".$tp[$iddenda]." width=30px id=tpddt##".$iddenda."><b>".@hidezerodecimal($ttlp[$iddenda])."</b></td>";
			}
			
			$tab.="<td bgcolor=cyan align=right><b>".@number_Format($trrp)."</b></td>";
			$tab.="<td bgcolor=cyan align=right></td>";
			$tab.="</tr>";
			
			
			$str = "select a.kodeorg,a.tahuntanam,sum(a.hasilkerja) as hasilkerja, sum(a.hasilkerjakg) as kg, sum(a.jumlahhk) as hk, sum(a.norma) as norma, sum(a.upahkerja) as upah, sum(a.upahpenalty) as penalty, sum(a.upahpremi) as bss, sum(a.upahpremilebihbasis) as lbbss, sum(a.premibasis) as pbss, sum(a.brondolan) as brd, sum(a.luaspanen) as ha ,sum(a.penalti1) as penalti1,sum(a.penalti2) as penalti2,sum(a.penalti3) as penalti3,sum(a.penalti4) as penalti4,sum(a.penalti5) as penalti5,sum(a.penalti6) as penalti6,sum(a.penalti7) as penalti7,sum(a.penalti8) as penalti8,sum(a.penalti9) as penalti9,sum(a.penalti10) as penalti10, sum(a.rupiahpenalty) as rupiahpenalty 
			from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid  where a.notransaksi='" . $notransaksi . "' group by kodeorg order by a.kodeorg asc";
			$row=fetchData($str);
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$nox='';
			while ($bar = $res->fetch()) {
				$nox++;
				$nn=" style=display:none ";
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $nox . "</td>";
				if($nox==1){
					$tab.="<td align=center><b>".strtoupper($_SESSION['lang']['rekap']) . " ".strtoupper($_SESSION['lang']['blok']) . " ==></b></td>";
				}else{
					$tab.="<td></td>";
				}
				$tab.="<td align=center>" . $bar['kodeorg']. "</td>";
				$tab.="<td align=center>" . $bar['tahuntanam'] . "</td>";
				$tab.="<td hidden align=center></td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['ha'],2) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['hasilkerja']) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['brd']) . "</td>";
				$tab.="<td align=right>" . number_Format($bar['kg']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['upah']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['bss']) . "</td>";
				$tab.="<td hidden align=right>" . number_Format($bar['lbbss']+$bar['pbss']) . "</td>";
				
				#denda list data
				foreach($dendapanen as $iddenda => $kddenda){
					$tab.="<td align=right ".$nn." ".$tp[$iddenda]." width=30px id=rtpddt##".$iddenda."##".$nox.">".@hidezerodecimal($bar['penalti'.$iddenda])."</td>";
				}
				
				$tab.="<td align=right>" . number_Format($bar['rupiahpenalty']) . "</td>";
				$tab.="<td align=right></td>";

			}
			$tab.="<input value=".$nox." style=display:none id=jlhbrsdtrekap>";
			
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
	break;
	
	case'getDataDetail':
	#============================= BJR Per Blok ===========================
		$perLalu=substr(tanggalsystemn($tgl),0,7);
		$tahun=substr(tanggalsystemn($tgl),0,4);
		$str = "select * from ".$dbname.".kebun_5bjr where periode='".$perLalu."' and kodeorg='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$bjr=$bar['bjr'];
		
	#============================= BJR Per Blok ===========================
	#=============================== Get UMR ==============================
		$str = "select sum(jumlah) as nilai from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid."' and tahun=".$tahun." and idkomponen in (1)"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$Umr=$res->fetch();
			$umrHarian=$Umr['nilai']/25;
		
	#=============================== Get UMR ==============================
	#============================== Tipe Kary =============================
		$str = "select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$tipe=$res->fetch();
			$tipeKary=$tipe['tipekaryawan'];
		
	#============================== Tipe Kary =============================
	#=============================== Get HL ===============================
	/*
		$tanggalx=tanggalsystemn($tgl);
		$day = date('D', strtotime($tanggalx));
		
		$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tanggalx."' and (kebun='GLOBAL' or kebun='".$kodeorg."')";
		$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
		$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
		$roworg=$queorg->fetch();
			if($roworg['keterangan']=='libur'){
				$libur=true;
			} else if ($day=='Sun'){
				$libur=true;
			} else if($roworg['keterangan']=='masuk'){
				$libur=false;
			} else {
				$libur=false;
			}
		
	# Jika hari libur maka upah KHT = 0
	# 0 => Staff, 1 => PB, 2 => PKWT, 3 => KHT, 4 => KHL, 5 => Magang, 6 => Kontrak, 7 => Direksi, 8 => Komisaris
		if(($tipeKary=='4' || $tipeKary=='5') and $libur=true){
			$umrHarian=$umrHarian;
		} elseif ($libur=true){
			$umrHarian=0;
		} else {
			$umrHarian=$umrHarian;
		}
	*/
	#=============================== Get HL ===============================
	#=============================== Get TT ===============================
		$str = "select * from ".$dbname.".setup_blok where kodeorg='".$blok."'"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tt=$bar['tahuntanam'];
	#=============================== Get TT ===============================
	#================== buat cek apakah ada di rekappnn ===================
		$jumlah='0';
		$str="select count(*) as jumlah from ".$dbname.".kebun_rekappnn_vw where "." blok='".$blok."' and tanggal='".tanggalsystemn($tgl)."' and posting=1 ";
		$qDetail=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qDetail->setFetchMode(PDO::FETCH_ASSOC);
		while($rDetail=$qDetail->fetch()){
			$jumlah=$rDetail['jumlah'];  
		}
	   
		if($jumlah=='0'){
			$rpnn="x";
		} else {
			$rpnn="y";
		}
		
	#================== buat cek apakah ada di rekappnn ===================
	
	#===================== ambil rp/kg di blok 
	$rpsatuan=0;
	if($kontan=='KONTAN'){
		$str = "select * from ".$dbname.".kebun_rkh_vw where kodeblok='".$blok."' and 
				kodekegiatan='611010101' and kontan='".$kontan."' and tanggal='".tanggalsystemn($tgl)."' and 
				statuspersetujuan=1"; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$rpsatuan=$bar['rpsatuan'];
	}
	#=======================================
		
		#tidak pakai upah, semua karyawan borongan
		$umrHarian=0;
		
		echo $bjr."######".$umrHarian."######".$tt."######".$rpnn."######".$rpsatuan;
		// exit("Error:A");
	break;
	
	case'getHitungDenda':
	# === Get Denda ===
		$param=$_POST;
		if($param['karyawanid']=='' || $param['blok']=='' || $param['jjgpanen']==''){
			exit('Error : Silahkan isi Karyawan, Blok dan Jjg Panen terlebih dahulu.');
		}
		
		$qDenda = "select * from ".$dbname.".kebun_5dendapanen a left join ".$dbname.".kebun_5kodedendapanen b on a.kodedenda=b.kodedenda where 1=1 and a.kodeorg='".$param['kodeorg']."'";
		$resDenda = fetchData($qDenda);
		$optDenda = array();
		foreach($resDenda as $row) {
			$optDenda[$row['id']] = array(
				'jenis' => $row['jenisdenda'],
				'nilai' => $row['denda']
			);
		}
		
		$denda = array(
				'jjg' => 0,
				'rp' => 0
			);
			
		if(is_array($param['penalti'])){
			foreach($param['penalti'] as $kode=>$val) {
				if(isset($optDenda[$kode])) {
					$denda['rp'] += $val * $optDenda[$kode]['nilai'];
					
				}
			}
		}
			
		if($denda['rp']<0){
			$denda['rp']=0;
		}

		echo $denda['rp'];
	# === Get Denda ===
	
	break;

	
    case'insert':
		# Jika ada datanya maka exe, jika tidak maka lewatkan
		if($param['karyawanid']!='' and $param['blok']!='' and ($param['hapanen']!='' or $param['hapanen']!='0') and ($param['jjgpanen']!='' or $param['jjgpanen']!='0')){
			
		try {
			$owlPDO->beginTransaction();
	
			# Hapus dulu data yang lama
			$str = "delete from " . $dbname . ".kebun_prestasi where notransaksi='".$notransaksi."' and nik='".$param['karyawanid']."' and kodeorg='".$param['blok']."'";
			$owlPDO->exec($str);
			#try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			
			# Validasi penginputan
			cekPrestasi($param);
			
			$data = array(
					'notransaksi'         => $param['notransaksi'],
					'nik'                 => $param['karyawanid'],
					'kodeorg'             => $param['blok'],
					'luaspanen'           => $param['hapanen'],
					'hasilkerja'          => $param['jjgpanen'],
					'brondolan'           => $param['brdpanen'],
					'hasilkerjakg'        => $param['kgpanen'],
					'upahkerja'           => $param['upah'],
					'upahpremi'           => $param['basis'],
					'upahpremilebihbasis' => $param['lbasis'],
					'rupiahpenalty'       => $param['denda_rp'],
					'tahuntanam'          => $param['tt'],
					'bjr'                 => $param['bjr'],
					'pekerjaanpremi'      => $param['sts'],
					'keterangan'          => $param['kontan']
			);
			
			for($i=1;$i<=$jlhdenda;$i++){
				$data['penalti'.$i] = $param['penalti'.$i];
			}
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert
			$query = insertQuery($dbname,'kebun_prestasi',$data,$cols);
			#exit('error :'. $query);
			$owlPDO->exec($query);
			#try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; exit(); }
			
			# Exe jika bukan Kontanan, di KSP tidak di exe karena tidak pakai upah tetapi borongan
			if($param['kontan']!='KONTAN'){
				#proporsiUpah($param);
			}
			
			# Jika Kontanan Maka Nama Mandor Dkk di Header di kosongkan dan mandor bisa di inputkan di detail
			if($param['kontan']=='KONTAN'){
				$str = "update " . $dbname . ".kebun_aktifitas set `nikmandor`='', `nikmandor1`='',`nikasisten`='' where `notransaksi`='".$param['notransaksi']."'";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		}
	break;
    case'delete':
	try {
		$owlPDO->beginTransaction();
		$str="select * from ".$dbname.".kebun_aktifitas where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['noreferensi']!=''){
			throw new PDOException('Warning : Ini adalah transaksi yang terbentuk otomatis pada saat Posting pada proses Premi Pemanen, untuk menghapus silahkan unposting pada transaksi Proses Premi Pemanen.');
		}
		
        $str = "delete from " . $dbname . ".kebun_aktifitas where notransaksi='".$notransaksi."'";
        $owlPDO->exec($str);
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
		// try {
            // $owlPDO->exec($str);
        // } catch (PDOException $e) {
            // print " Gagal  !: " . $e->getMessage() . "\n";
            // die();
        // }

        break;

    case'deletedetail':

        $str = "delete from " . $dbname . ".kebun_prestasi where notransaksi ='".$notransaksi."' and kodeorg='" . $blok . "' and nik='" . $karyawanid . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		# Exe jika bukan Kontanan
		if($param['kontan']!='KONTAN'){
			proporsiUpah($param);
		}
		
    break;

    
		
	case'getnotransaksi':
		$data = $_POST;
		# Data Capture & Reform
		$data['tipetransaksi'] = 'PNN';
		$data['tgl'] = tanggalsystem($data['tgl']);
		
		#=== Generate No Transaksi
		# Get Existing Data
		$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg'].
			"' and tipetransaksi='".$data['tipetransaksi']."'";
		$fQuery = selectQuery($dbname,'kebun_aktifitas','notransaksi',$fWhere);
		$tmpNo = fetchData($fQuery);
		
		# Generate No Transaksi
		if(count($tmpNo)==0) {
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/001";
		} else {
			# Get Max No Urut
			$maxNo = 1;
			foreach($tmpNo as $row) {
			$tmpRow = explode('/',$row['notransaksi']);
			$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+1,3);
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
		}
	
    break;
	
	case'getbasispnn':
		$tab='';
		$tab.="<fieldset>";
		$tab.="<table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
					<tr class=rowheader>
						<td align=center rowspan=2>No</td>
						<td align=center rowspan=2>".$_SESSION['lang']['unit']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahun']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['jenis']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['norma']."</td>
						<td align=center rowspan=2>".$_SESSION['lang']['premlebihbasis']." Rp/Kg</td>
						<td align=center rowspan=2>".$_SESSION['lang']['topografi']."</td>
						<td align=center rowspan=2>Premi Kehadiran</td>
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['brondol']." Rp/Kg</td>
					</tr>

				</thead>
		<tbody>";
		
		$optJenis = array(
						'0' => 'Normal',
						'1' => 'Banjir'
					);
		$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
		
		$tahun = substr($notransaksi,0,4);
		$str = "SELECT *  FROM " . $dbname . ".kebun_5basispanen2 where 1=1 and kodeorg='".$kodeorg."' and tahun='".$tahun."' order by tahuntanam asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;	
			$optPT = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi in ('".$bar['kodeorg']."')");
			$tab.="<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$bar['kodeorg']." - ".$optPT[$bar['kodeorg']]."</td>
			<td style='text-align:center'>".$bar['tahun']."</td>
			<td style='text-align:center'>".$bar['tahuntanam']."</td>
			<td style='text-align:center'>".$optJenis[$bar['jenispremi']]."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['basis'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premilebihbasis'],2)."</td>
			<td style='text-align:left'>".($optTopografi[$bar['topografi']])."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premitopografi'],2)."</td>
			<td style='text-align:right'>".@hidezerodecimal($bar['premibrondolan'],2)."</td>";
			
		}
		
		$tab.="</tr></tbody>";
		$tab.="</table>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	case'posting':
		$queryH = selectQuery($dbname,'kebun_aktifitas',"*","notransaksi='".
			$param['notransaksi']."'");
			
		$dataH = fetchData($queryH);


		$queryD = selectQuery($dbname,'kebun_prestasi',"*","notransaksi='".
			$param['notransaksi']."'");
		$dataD = fetchData($queryD);
		
		$error1="";
		if(count($dataH)==0) {
			$error1 .= $_SESSION['lang']['errheadernotexist']."\n";
		}
		if(count($dataD)==0) {
			$error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
		}
		if($error1!='') {
			echo "Data Error :\n".$error1;
			exit;
		}

		$strupd=" update ".$dbname.".kebun_aktifitas set jurnal='1' where notransaksi='".$param['notransaksi']."'";
		try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
}

function cekPrestasi($param) {
	global $dbname;
	global $owlPDO;
		
	$tgl=explode('/',$param['notransaksi']);
	$tgl=$tgl[0];
	$notrx='';
	#cek mandor
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek mandor1
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikmandor1='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek kerani
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where keranimuat='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	#cek nikasisten
	$str = "select count(*) as jumkar, notransaksi from ".$dbname.".kebun_aktifitas where nikasisten='".$param['karyawanid']."' and tanggal='".$tgl."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		@$jumtrans+=$bar['jumkar'];
		$notrx.=$bar['notransaksi'];
	
	# Jika Kontanan Maka Nama Mandor Dkk di Header di kosongkan dan mandor bisa di inputkan di detail
	if($param['kontan']!='KONTAN'){
		if(@$jumtrans>0){
			throw new PDOException("Upah karyawan sudah terdaftar sebagai mandor/mandor1/kerani, notransaksi : ".$notrx."");
		}
	}
			

	# Cek Perawatan
	# Jika sudah ada di perawatan tidak bisa input panen
	# Jika karyawan ada pekerjaan panen dan perawatan, maka harus malekukan input panen terlebih dahulu
	$qAbs = selectQuery($dbname,'kebun_kehadiran_vw','karyawanid,sum(jhk) as jhk, sum(umr) as umr,notransaksi',
								"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jhkrawat = $resAbs[0]['jhk'];
	$umrrawat = $resAbs[0]['umr'];
	$notr	  = $resAbs[0]['notransaksi'];
	
	if(intval($jhkrawat)!='0' || intval($umrrawat)!='0') {
		// throw new PDOException("Karyawan sudah terdaftar di kegiatan perawatan, notransaksi : ".$notr."");
	}
	
	#cek di vhc - kegiatan traksi
	$qAbs = selectQuery($dbname,'vhc_runhk','sum(upah) as jhk,notransaksi',
			"idkaryawan='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhkvhc = $resAbs[0]['jhk'];
	$notrtr = $resAbs[0]['notransaksi'];
	
	if(intval($jmlhkvhc)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di kegiatan traksi, notransaksi : ".$notrtr."");
	}
	
	#cek di SDM
	$qAbs = selectQuery($dbname,'sdm_absensidt_vw','sum(nilaihk) as jhk',
			"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'");
	$resAbs = fetchData($qAbs);
	$jmlhksdm = $resAbs[0]['jhk'];
	
	if(intval($jmlhksdm)!='0') {
		throw new PDOException("Karyawan sudah terdaftar di absensi SDM, tanggal : ".$tgl."");
	}
	
	# Cek Panen hanya di 1 blok
	$qPnn = selectQuery($dbname,'kebun_prestasi_vw','karyawanid,notransaksi',
			"karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."' and notransaksi!='".$param['notransaksi']."'");
	
	$resPnn = fetchData($qPnn);
	if(!empty($resPnn)){
		throw new PDOException("Pemanen dapat memanen diblok berbeda hanya dalam 1 nomor BKM,\nPemanen sudah terdaftar pada transaksi : ".$resPnn[0]['notransaksi']."");
	}
	
	# Cek dan ricek kalau data kosong
	if($param['blok']==''){
		$warning="Blok";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	if($param['karyawanid']==''){
		$warning="Karyawan";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	if($param['jjgpanen']==0||$param['jjgpanen']==''){
		$warning="Hasil Kerja (Jjg)";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['hapanen']==0 ||$param['hapanen']==''){
		$warning="Luas Panen(Ha)";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['bjr']==0 || $param['bjr']==''){
		$warning="BJR melalui Kebun - Setup - BJR";
		throw new PDOException("Silakan mengisi ".$warning.".");
	}
	
	if($param['kgpanen']==0 || $param['kgpanen']==''){
		$warning="Kg Panen";
		throw new PDOException("".$warning." tidak boleh kosong !!!");
	}
	
	if($param['kontan']=='KONTAN'){
		if(($param['basis']==0 || $param['basis']=='') and ($param['lbasis']==0 || $param['lbasis']=='')){
			$warning="Premi Karyawan";
			throw new PDOException("Silakan mengisi ".$warning.".");
		}		
	}else{
		if($param['upah']==0 || $param['upah']==''){
			#$warning="Gaji Pokok Karyawan";
			#throw new PDOException("Silakan mengisi ".$warning.".");
		}		
	}
	
	# periksa luas panen hari ini apakah sudah melebihi setup blok
	# cari luas blok
	$query = "SELECT luasareaproduktif FROM ".$dbname.".`setup_blok`
		WHERE `kodeorg` = '".$param['blok']."'";
	$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
	$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
	while($rDetail=$qDetail->fetch()){
		$luasbloknya=$rDetail['luasareaproduktif'];
	}
	
	# cari tanggal
	$query = "SELECT distinct tanggal FROM ".$dbname.".`kebun_prestasi_vw`
			  WHERE `notransaksi` = '".$param['notransaksi']."'";
	$tanggalnya = '';
	$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
	$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
	while($rDetail=$qDetail->fetch()){
		$tanggalnya=$rDetail['tanggal'];
	}
	if($tanggalnya==''){
		$tanggalnya= $tgl;
	}
	
	# cari luas panen yang sudah diinput ditambah inputan
	$query = "SELECT sum(luaspanen) as luaspanen, sum(hasilkerja) as jjg FROM ".$dbname.".`kebun_prestasi_vw` WHERE `tanggal` = '".$tanggalnya."' and `kodeorg` ='".$param['blok']."' and karyawanid!='".$param['karyawanid']."'";
	$qDetail=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
	$qDetail->setFetchMode(PDO::FETCH_ASSOC);     
	while($rDetail=$qDetail->fetch()){   
		$luaspanennya=$rDetail['luaspanen'];
		$jjgpnnnya=$rDetail['jjg'];
		
	}
	$luaspanennya+=$param['hapanen'];
	$jjgpnnnya+=$param['jjgpanen'];
	
	if($luaspanennya>$luasbloknya){
		$warning="Tota Luas Panen ".$luaspanennya." (Ha), melebihi Luas Blok ".$luasbloknya." (Ha)";
		throw new PDOException("".$warning.".");
	}

	# cek apakah jumlah luas, jjg tidak boleh lebih dari rekap panen
	# 01. Ambil data dari rekap panen
	$str = "select sum(jjgpanen) as jjgpanen, sum(luaspanen) as hapnn from ".$dbname.".kebun_rekappnn_vw where blok='".$param['blok']."' and tanggal='".$tgl."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$jjgrkppnn=$bar['jjgpanen'];
		$harkppnn=$bar['hapnn'];
	}
	
	if($luaspanennya>$harkppnn){
		throw new PDOException('Luas Panen Blok '.$param['blok'].' = '.$luaspanennya.' (Ha), melebihi Luas di Rekap Panen = '.$harkppnn.' (Ha)');
	}
	if($jjgpnnnya>$jjgrkppnn){
		throw new PDOException('Jumlah Jjg Blok '.$param['blok'].' = '.$jjgpnnnya.', melebihi Jjg di Rekap Panen = '.$jjgrkppnn);
	}
}

function proporsiUpah($param) {
        global $dbname;
        global $conn;
        global $owlPDO;
		
        # Get Tahun
		$tmpTgl = explode('/',$param['notransaksi']);
        $tahun = substr($tmpTgl[0],0,4);
		$tgl=$tmpTgl[0];
		
        # Get UMR
        $qUMR = selectQuery($dbname,'sdm_5gajipokok','sum(jumlah) as nilai', "karyawanid=".$param['karyawanid']." and tahun=".$tahun." and idkomponen in (1)");
        $Umr = fetchData($qUMR);
        $upahharian=round($Umr[0]['nilai']/25);
		
		
		# Bentuk data
		$str="select sum(luaspanen) as luaspanen,sum(hasilkerja) as hasilkerja,count(*) as jumblok from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tluas=$bar['luaspanen'];
			$tjjg=$bar['hasilkerja'];
			$jumblok=$bar['jumblok'];
			@$upahpro=$upahharian/$jumblok;
		
		# Bentuk data
		$str="select * from ".$dbname.".kebun_prestasi_vs_hk where karyawanid='".$param['karyawanid']."' and tanggal='".$tgl."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			# Query update yang baru
			$strupd=" update ".$dbname.".kebun_prestasi set upahkerja='".$upahpro."' where notransaksi='".$param['notransaksi']."' and nik='".$param['karyawanid']."' and kodesegment='0000000001'";
			$owlPDO->exec($strupd);
			#try{$owlPDO->exec($strupd);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
}



function updateKmHm($kodevhc) {
	global $dbname;
	global $owlPDO;
	// Get KM/HM Akhir
	$qKm = selectQuery($dbname,'vhc_kmhmakhir_vw','*',"kodevhc='".$kodevhc."'");
	$resKm = fetchData($qKm);
	$kmhmAkhir = (empty($resKm))? 0: $resKm[0]['kmhmakhir'];

	$dataIns = array($kodevhc,$kmhmAkhir);
	$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
	 try{$owlPDO->exec($qIns); }
        catch (PDOException $e) {
                $dataUpd = array('kmhmakhir'=>$kmhmAkhir);
                $qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,"kodevhc='".$kodevhc."'");
                try{$owlPDO->exec($qUpd); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }         
        }
	
}

?>	
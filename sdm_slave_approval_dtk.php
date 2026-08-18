<?php
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	// $validasiGetMobile = explode(" ", isset($_GET['par']));
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
	$session_id = $_SESSION['standard']['userid'];
}
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$karyawanid=checkPostGet('karyawanid', $session_id);
$method = checkPostGet('method', '');
$jenispersetujuan = checkPostGet('jenispersetujuan', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$userid = checkPostGet('userid', '');
$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
$arrkategori = array('C' => 'update data', 'N' => 'Create New');
//echo 'xxxxsaaxxx'.$proses;
switch ($method) {
case 'getdetail':
	switch ($proses) {
	case'DTK1':
		

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['lokasi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('DTK1');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";

		$countApp = getCountApproval('DTK1');

		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
        $no = $maxdisplay;

        $sql = "select count(distinct a.notransaksi) as notr from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK1' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
		$res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];

		$str = "select a.*,b.namakaryawan,b.periodegaji,b.version_type,b.lokasitugas,b.karyawanid as idkary from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK1' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc limit " . $offset . "," . $limit . "";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['lokasitugas']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['namakaryawan']."</td>
				<td align=left>".$bar['periodegaji']."</td>
				<td align=left>".$bar['lokasitugas']." - ".$optNmOrg[$bar['lokasitugas']]."</td>
				<td align=left>".$arrkategori[$bar['version_type']]."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewKaryawanhist('".$bar['notransaksi']."','".$bar['idkary']."','".$bar['namakaryawan']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				// $arrDetail = detailApprove($i, $bar['notransaksi'], 'SPK');
				// if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					// $level = $arrDetail['level'];
					// $showaction = 1;
					// if ($i >= 2) {
						// $countubahjumlah = 1;
					// }
				// }
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$karyawanid){
						if($valx['status']=='' || $valx['status']==0)
						{
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$karyawanid && $valx['status']==0)
					{
						$level = $valx['level'];
						$xxx = "conte";
						break;
					}
				}
				
				if($xxx=="conte"){
					break;
				}
			}
			
			if ($showaction!=$level || $level==1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatadtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakdtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'DTK1');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='DTK1' and kodeunit='".$bar['lokasitugas']."' and level='".$i."'";
				$respo=fetchdata($strpo);
				$tipeapp = $respo[0]['tipe'];
				$departemenapp = $respo[0]['departemen'];
				$tipekaryawanapp = $respo[0]['tipekaryawan'];
				$jabatanapp = $respo[0]['jabatan'];
				$golonganapp = $respo[0]['golongan'];

				if($tipeapp=='1'){
					if($arrDetail['komentar']==''){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
						if($golonganapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$golonganapp."'");
							$arrDetail['nama'] = $opttipe[$golonganapp];
						}
					}
				}
				
				if($arrDetail['nama']!='')
				{
					$tab.="<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
						Status : ".$arrDetail['namastatus']."<br>
						".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>-</td>";
				}
				
				// if ($arrDetail['nama'] != '') {
					// $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				// } else {
					// $tab.="<td style='text-align:center'>-</td>";
				// }
			}
			$tab.="</tr>";
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
		$tab.="</tbody><tfoot>";

        $tab.="</tr><tr><td colspan=21 align=center>";
        if ($page == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail(('DTK1'," . ($page - 1) . ");>Prev</button>";
        }
        $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage('DTK1')\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail(('DTK1'," . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td></tr>";

		$tab.="</tfoot>
			</table>
			</fieldset>";
		break;
		case'DTK2':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['lokasi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('DTK2');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('DTK2');

		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
        $no = $maxdisplay;

        $sql = "select count(distinct a.notransaksi) as notr from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK2' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
		$res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];

		$str = "select a.*,b.namakaryawan,b.periodegaji,b.version_type,b.lokasitugas,b.karyawanid as idkary from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK2' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc limit " . $offset . "," . $limit . "";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['lokasitugas']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['namakaryawan']."</td>
				<td align=left>".$bar['periodegaji']."</td>
				<td align=left>".$bar['lokasitugas']." - ".$optNmOrg[$bar['lokasitugas']]."</td>
				<td align=left>".$arrkategori[$bar['version_type']]."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewKaryawanhist('".$bar['notransaksi']."','".$bar['idkary']."','".$bar['namakaryawan']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				// $arrDetail = detailApprove($i, $bar['notransaksi'], 'SPK');
				// if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					// $level = $arrDetail['level'];
					// $showaction = 1;
					// if ($i >= 2) {
						// $countubahjumlah = 1;
					// }
				// }
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$karyawanid){
						if($valx['status']=='' || $valx['status']==0)
						{
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$karyawanid && $valx['status']==0)
					{
						$level = $valx['level'];
						$xxx = "conte";
						break;
					}
				}
				
				if($xxx=="conte"){
					break;
				}
			}
			
			if ($showaction!=$level || $level==1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatadtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakdtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'DTK2');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='DTK2' and kodeunit='".$bar['lokasitugas']."' and level='".$i."'";
				$respo=fetchdata($strpo);
				$tipeapp = $respo[0]['tipe'];
				$departemenapp = $respo[0]['departemen'];
				$tipekaryawanapp = $respo[0]['tipekaryawan'];
				$jabatanapp = $respo[0]['jabatan'];
				$golonganapp = $respo[0]['golongan'];
				
				if($tipeapp=='1'){
					if($arrDetail['komentar']==''){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}

						if($golonganapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$golonganapp."'");
							$arrDetail['nama'] = $opttipe[$golonganapp];
						}
					}
				}
				
				if($arrDetail['nama']!='')
				{
					$tab.="<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
						Status : ".$arrDetail['namastatus']."<br>
						".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>-</td>";
				}
				
				// if ($arrDetail['nama'] != '') {
					// $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				// } else {
					// $tab.="<td style='text-align:center'>-</td>";
				// }
			}
			$tab.="</tr>";
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
		$tab.="</tbody><tfoot>";

        $tab.="<tr><td colspan=21 align=center>";
        if ($page == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail('DTK2'," . ($page - 1) . ");>Prev</button>";
        }
        $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage('DTK2')\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail('DTK2'," . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td></tr>";

		$tab.="</tfoot>
			</table>
			</fieldset>";
		break;
		case'DTK3':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
			<td align=center>".$_SESSION['lang']['periode']."</td>
			<td align=center>".$_SESSION['lang']['kodeorganisasi']."</td>
			<td align=center>".$_SESSION['lang']['lokasi']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
			<td colspan='2' align='center'>Verification</td>";
		$countApp = getCountApproval('DTK3');
		for ($i = 1; $i <= $countApp; $i++) {
			$tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
		}
		$tab.="</tr>
			</thead>
			<tbody>";
		$countApp = getCountApproval('DTK3');

		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
        $no = $maxdisplay;

        $sql = "select count(distinct a.notransaksi) as notr from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK3' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
		$res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];

		$str = "select a.*,b.namakaryawan,b.periodegaji,b.version_type,b.lokasitugas,b.karyawanid as idkary from ".$dbname.".approval a
			left join ".$dbname.".datakaryawan_hist b on a.notransaksi = b.nourut
			where a.jenispersetujuan='DTK3' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc limit " . $offset . "," . $limit . "";
		//exit('error'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			
			$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['lokasitugas']."'");
			$no++;
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$bar['notransaksi']."</td>
				<td align=left>".$bar['namakaryawan']."</td>
				<td align=left>".$bar['periodegaji']."</td>
				<td align=left>".$bar['lokasitugas']." - ".$optNmOrg[$bar['lokasitugas']]."</td>
				<td align=left>".$arrkategori[$bar['version_type']]."</td>
				<td align=center>
				<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"previewKaryawanhist('".$bar['notransaksi']."','".$bar['idkary']."','".$bar['namakaryawan']."','html');\">
				</td>";
			$showaction = 0;
			$countubahjumlah = 0;
			$level = 1;
			$xxx = "";
			for ($i = 1; $i <= $countApp; $i++) {
				// $arrDetail = detailApprove($i, $bar['notransaksi'], 'SPK');
				// if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
					// $level = $arrDetail['level'];
					// $showaction = 1;
					// if ($i >= 2) {
						// $countubahjumlah = 1;
					// }
				// }
				$strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					if($valx['karyawanid']==$karyawanid){
						if($valx['status']=='' || $valx['status']==0)
						{
							$showaction = $showaction + 1;
						}
					}
					
					if($valx['karyawanid']==$karyawanid && $valx['status']==0)
					{
						$level = $valx['level'];
						$xxx = "conte";
						break;
					}
				}
				
				if($xxx=="conte"){
					break;
				}
			}
			
			if ($showaction!=$level || $level==1) {
				$tab.="<td style='text-align:center'>
					<button class=mybutton onclick=\"getdatadtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['approve']."</button>
					</td>
					<td style='text-align:center'>
					<button class=mybutton onclick=\"tolakdtk('".$bar['notransaksi']."','".$level."','".$proses."')\">".$_SESSION['lang']['ditolak']."</button>
					</td>";
			} else {
				$tab.="<td colspan=2>&nbsp;</td>";
			}
			for ($i = 1; $i <= $countApp; $i++) {
				$arrDetail = detailApprove($i, $bar['notransaksi'], 'DTK3');
				
				$strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='DTK3' and kodeunit='".$bar['lokasitugas']."' and level='".$i."'";
				$respo=fetchdata($strpo);
				$tipeapp = $respo[0]['tipe'];
				$departemenapp = $respo[0]['departemen'];
				$tipekaryawanapp = $respo[0]['tipekaryawan'];
				$jabatanapp = $respo[0]['jabatan'];
				$golonganapp = $respo[0]['golongan'];
				
				if($tipeapp=='1'){
					if($arrDetail['komentar']==''){
						if($departemenapp!=''){
							$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}
						
						if($tipekaryawanapp!=''){
							$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}
						
						if($jabatanapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
						if($golonganapp!='0'){
							$opttipe = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$golonganapp."'");
							$arrDetail['nama'] = $opttipe[$golonganapp];
						}
					}
				}
				
				if($arrDetail['nama']!='')
				{
					$tab.="<td style='vertical-align:top;text-align:center'>
						<label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
						Status : ".$arrDetail['namastatus']."<br>
						".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>-</td>";
				}
				
				// if ($arrDetail['nama'] != '') {
					// $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
				// } else {
					// $tab.="<td style='text-align:center'>-</td>";
				// }
			}
			$tab.="</tr>";
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
		$tab.="</tbody><tfoot>";

        $tab.="</tr><tr><td colspan=21 align=center>";
        if ($page == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail(('DTK3'," . ($page - 1) . ");>Prev</button>";
        }
        $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage('DTK3')\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=getdetail(('DTK3'," . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td></tr>";

		$tab.="</tfoot>
			</table>
			</fieldset>";
		break;
	}
	break;
	case'get_form_approvaldtk':
		$tab="";
		$str="select lokasitugas from ".$dbname.".datakaryawan_hist where nourut='".$notransaksi."'";
		$res=fetchdata($str);
		$kodeorg = $res[0]['lokasitugas'];
		
		$countApp = getCountApproval($jenispersetujuan,$kodeorg);
		for($i=1;$i<=$countApp;$i++){
			$strx="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$i."' group by notransaksi";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				if($karyawanid==$valx['karyawanid']){
					if($i == $countApp){
						$tab.="<div id=approve>
							<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Approved</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
									</td>
								</tr>
								<tr>
									<td colspan=3 align=center>
										<button id=Ajukan class=mybutton onclick=nextapprovaldtk('approved','".$jenispersetujuan."') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						$level = $i+1;
						$arrListApp = listApprove($level,$jenispersetujuan,$kodeorg);
						foreach($arrListApp as $key=>$val){
							$optKry.="<option value='".$val['karyawanid']."'>".$val['nama']." [".$val['lokasitugas']."]</option>";
						}
						$tab.="<div id=test style=display:block>
							<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kolom value=".$_POST['kolom']."  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Submit to the next approval :</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['namakaryawan']."</td>
									<td>:</td>
									<td valign=top>
										<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
									</td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
									</td>
								</tr>
									<td colspan=2></td>
									<td>
										<button class=mybutton onclick=nextapprovaldtk('','".$jenispersetujuan."') title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
									</td>
								</tr>
							</table>
							<input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						</div>";
					}
				}				
			}
        }
		echo $tab;
	break;
	case 'insert_nextapproval':
		try {
		$owlPDO->beginTransaction();
		
		$jenisApp = $jenispersetujuan;
		if($userid==''){
			$user_id = $karyawanid;
		}else{
			$user_id = $userid;
		}
		
		$str="select lokasitugas from ".$dbname.".datakaryawan_hist where nourut='".$notransaksi."'";
		$res=fetchdata($str);
		$kodeorg = $res[0]['lokasitugas'];
		
		
		$countApp = getCountApproval($jenispersetujuan, $kodeorg);
		$tglskrng = date("Y-m-d H:i:s");
		$str = "select * from ".$dbname.".datakaryawan_hist where `nourut`='".$notransaksi."'"; #exit('error sasas'.$str);
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		// echo $countApp;
		// throw new Exception("Error Processing Request", 1);
		if ($bar['approval_status'] == 1) {
			throw new PDOException("Sudah di Approved");
		}else if($bar['approval_status'] == 9) {
			$arrDetail = detailApprove($kolom, $notransaksi, $jenisApp);
			$level = $kolom + 1;
			if ($kolom != $countApp) {
				if ($user_id == $arrDetail['karyawanid']) {
					throw new PDOException(getNamaKaryawan($user_id)." Sudah di gunakan");
				}else if($user_id == $bar['diajukan']) {
					throw new PDOException(getNamaKaryawan($user_id)." Pembuat Transaksi");
				} else {
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$level."' and kodeunit='".$koderorg."'";
					$res=fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];
					$golonganapp = $res[0]['golongan'];
					
					if($tipeapp=='1'){
						if($departemenapp!=''){
							$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($tipekaryawanapp!=''){
							$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($jabatanapp!='0'){
							$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($golonganapp!='0'){
							$str="select * from ".$dbname.".datakaryawan where golongan='".$golonganapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
					}else{
						$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','".$jenispersetujuan."','".$level."','".$user_id."','0','','','')";
						$owlPDO->exec($str);
					}
					
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}
			} else {
				
				# ========================= end update datakaryawan ================================
				
				$qData = selectQuery($dbname,'datakaryawan_hist','*',"nourut='".$notransaksi."'");
				$resData = fetchData($qData);
				$dakar = $resData[0];
				
				if($dakar['version_type']=='C'){
					$uptext='';
					$arr1x=explode('###', $dakar['datachange']);
				  	foreach ($arr1x as $key => $val) {
				        if($val!='')
				        {
				        	if($uptext=='')
						    {
						    	$uptext="`".$val."`='".$dakar[$val]."' ";
						    }
						    else
						    {
				        		$uptext.=",`".$val."`='".$dakar[$val]."' ";
						    }
				        }
				    }
				//echo $uptext;
				$strx1 = "update ".$dbname.".datakaryawan set  ".$uptext." where `karyawanid`='".$dakar['karyawanid']."'";

				}else{
					$str="delete from ".$dbname.".datakaryawan where karyawanid='".$dakar['karyawanid']."'";
					$owlPDO->exec($str);

					$strxcol="SHOW COLUMNS FROM ".$dbname.".datakaryawan";
					$resxcol=$owlPDO->query($strxcol) or die(print " Gagal: ".PDOException::getMessage());
					$resxcol->setFetchMode(PDO::FETCH_OBJ);
					$fieldlist='';
					$vallist='';
					while($barxcol=$resxcol->fetch())
					{	
						if($barxcol->Field=='statusapproval'){
							$dakar[$barxcol->Field]=1;
						}
						if($fieldlist==''){
							$fieldlist="`".$barxcol->Field."`";
							$vallist="'".$dakar[$barxcol->Field]."'";
						}
						else
						{
							$fieldlist.=",`".$barxcol->Field."`";
							$vallist.=",'".$dakar[$barxcol->Field]."'";
						}
					}
	
					$strx1 = "insert into ".$dbname.".datakaryawan (".$fieldlist.") values (".$vallist.")";	
				}

				$owlPDO->exec($strx1);
				# ========================= end insert ke table dtk ================================
				$qDatax = selectQuery($dbname,'datakaryawan_hist','version',"karyawanid='".$dakar['karyawanid']."'",'version desc');
				$resDatax = fetchData($qDatax);
				$versionnum = intval($resDatax[0]['version'])+1;

				$strx2 = "update ".$dbname.".datakaryawan_hist set approval_status='1', version='".$versionnum."' where `nourut`='".$notransaksi."'";
				$owlPDO->exec($strx2);

				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);
					
			}
		}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	case 'tolak':
		 echo"<div id=rejected_form>
		<input hidden id=notransaksi value=".$_POST['notransaksi']."  />
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		 Rejection</td></tr>
		<tr>
		<tr><td colspan=3><hr></td></tr>
		<td>".$_SESSION['lang']['note']."</td>
		<td>:</td>
		<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
		</tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=\"inserttolakspk('".$_POST['kolom']."','".$jenispersetujuan."')\" >".$_SESSION['lang']['ditolak']."</button>
		</td></tr></table>
		</div>";
	break;
	case 'inserttolak':
		$ardt=0;

		$str="select lokasitugas from ".$dbname.".datakaryawan_hist where nourut='".$notransaksi."'";
		$res=fetchdata($str);
		$kodeorg = $res[0]['lokasitugas'];

		$countApp = getCountApproval($jenispersetujuan,$kodeorg);
		$arrDetail = detailApprove($kolom,$notransaksi,$jenispersetujuan);
		$tglskrng=date("Y-m-d H:i:s");
		$str="update ".$dbname.".datakaryawan_hist set approval_status='2' where nourut='".$notransaksi."'" ;
		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='3', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>
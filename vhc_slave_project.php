<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$path = "fileupload/cip/";
$method = checkPostGet('method', '');
$kode = checkPostGet('kode', '');
$crcapex = checkPostGet('crcapex', '');

$kodecapex = checkPostGet('kodecapex', '');
$unit = checkPostGet('unit', '');
$aset = checkPostGet('aset', '');
$jenis = checkPostGet('jenis', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$nama = checkPostGet('nama', '');
$namaaset = checkPostGet('namaaset', '');
$namacr = checkPostGet('namacr', '');
$unitcr = checkPostGet('unitcr', '');
$kodecr = checkPostGet('kodecr', '');
$tipebg = checkPostGet('tipebg', '');
$pekerjaan = checkPostGet('pekerjaan', '');
$tanggalmulai = tanggalsystem(checkPostGet('tanggalmulai', ''));
$tanggalselesai = tanggalsystem(checkPostGet('tanggalselesai', ''));
$kelompok = checkPostGet('kelompok', '');
$nilai = checkPostGet('nilai', '');
$optLokasi = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$kegiatan = checkPostGet('kegiatan', '');
$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$satBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optNmKegBrg = makeOption($dbname, 'project_dt', 'kegiatan,namakegiatan');

$kodeproject = checkPostGet('kodeproject', '');
$kodekegiatan = checkPostGet('kodekegiatan', '');
$kodeBarangForm = checkPostGet('kodeBarangForm', ''); //buat insert
$kodebarang = checkPostGet('kodebarang', ''); //buat delete
$jumlahBarangForm = checkPostGet('jumlahBarangForm', '');

$namaBarangCari = checkPostGet('namaBarangCari', '');

$deskripsi = checkPostGet('deskripsi', '');
$satKeg = checkPostGet('satKeg', '');
$volKeg = checkPostGet('volKeg', '');
$bobotKeg = checkPostGet('bobotKeg', '');
$satuan = checkPostGet('satuan', '');
$jumlah = checkPostGet('jumlah', '');

$posisiasset = checkPostGet('posisiasset', '');
$tipelokasiasset = checkPostGet('tipelokasiasset', '');
$nomesin = checkPostGet('nomesin', '');
$norangka = checkPostGet('norangka', '');
$tipemodel = checkPostGet('tipemodel', '');
$keterangan = checkPostGet('keterangan', '');
$dgnapprvl = checkPostGet('dgnapprvl', '');

$aprv1 = checkPostGet('aprv1', '');
$aprv2 = checkPostGet('aprv2', '');
$aprv3 = checkPostGet('aprv3', '');
$aprv4 = checkPostGet('aprv4', '');

$kryApr1 = checkPostGet('kryApr1', '');
$kryApr2 = checkPostGet('kryApr2', '');
$kryApr3 = checkPostGet('kryApr3', '');
$kryApr4 = checkPostGet('kryApr4', '');

if (count($_POST) > 0) {
    $param = $_POST;
} else {
    $param = $_GET;
}

$sub = checkPostGet('sub', '');

$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

# Posting --> Jabatan
$postJabatan = getPostingJabatan('project');

switch ($method) {
    case 'showupload':
        $tab = "";
        $tab .= "
		<table border=0 >
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td>:</td>
				<td id='notranupload'>" . $param['notransaksi'] . "</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td style=vertical-align:top>Status</td>
				<td style=vertical-align:top>:</td>
				<td>
					<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
					<p id='status'></p>
					<p id='loaded_n_total'></p>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('" . $param['notransaksi'] . "')\">Submit</button>
				</td>
			</tr>
		</table>
		";
        // $str = "select * from ".$dbname.".project where notransaksi='".$notransaksi."'";
        $str = "select a.posting,b.* from " . $dbname . ".project a left join " . $dbname . ".project_dt b ON a.kode=b.kodeproject where b.kegiatan = '" . substr($param['notransaksi'], 12, 7) . "'";
        $res = fetchData($str);
        if ($res[0]['posting'] == 1) {
            $tab = "<b>List File Upload<br></b>";
        }
        $tab .= "
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=30px colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

        echo $tab;
        break;

    case 'submitfile':
        try {
            $owlPDO->beginTransaction();
            $data = $_POST;
            if (count($data) == 0) {
                $data = $_GET;
            }

            // Mengecek apakah folder sudah ada
            if (!file_exists($path)) {
                // Membuat folder jika belum ada
                if (mkdir($path, 0777, true)) {
                    // echo "Folder berhasil dibuat: $path";
                } else {
                    echo "<label hidden>Warning</label>Gagal membuat folder.";
                }
            }
            // else {
            // 	exit("<label hidden>Warning</label> Folder sudah ada: $path");
            // }

            $newPermissions = 0755; // Ganti dengan nilai izin yang diinginkan

            // Menggunakan chmod untuk mengatur izin folder
            // chmod($path, $newPermissions);

            if ($data['fileupload'] != '') {
                if ($_FILES['file']['error'] == 0) {
                    $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                    $filename = $_FILES['file']['name'];
                    $filename = $param['notransaksi'] . "_" . date("YmdHis") . $filetype;
                    $kriteria = "CIP";
                    #cek duplikasi nama file
                    $str = "select * from " . $dbname . ".listfileupload where namafile = '" . $filename . "'";
                    $res = fetchData($str);
                    if (count($res) > 0) {
                        throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
                    }
                    $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
                    if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                        $str = "insert into " . $dbname . ".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('" . $param['notransaksi'] . "','" . $filename . "','" . $filetype . "','" . $kriteria . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
                        $owlPDO->exec($str);
                        file_put_contents($path . $filename, $file_tmpname);
                    } else {
                        throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
                    }
                    if (!file_exists($path . $filename)) {
                        throw new PDOException("Upload file gagal.");
                    }
                }
            } else {
                throw new PDOException("Upload file gagal.");
            }
            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;
    case 'loadfiles':
        // $str= "select * from ".$dbname.".project where kegiatan = '".substr($param['notransaksi'],12,7)."'";
        $str = "select a.posting,b.* from " . $dbname . ".project a left join " . $dbname . ".project_dt b ON a.kode=b.kodeproject where b.kegiatan = '" . substr($param['notransaksi'], 12, 7) . "'";
        $res = fetchData($str);
        $posting = $res[0]['posting'];

        $no = 0;
        $tab = "";
        $str = "select * from " . $dbname . ".listfileupload where notransaksi = '" . $param['notransaksi'] . "' and status='1'";
        $res = fetchData($str);
        if (empty($res)) {
            $tab .= "<tr class=rowcontent><td colspan=5 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            foreach ($res as $key => $val) {
                $no++;
                $tab .= "<tr class=rowcontent>
						<td style='text-align:center'>" . $no . "</td>";
                $icon = seticonfile($val['formaticon']);
                $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=zImgBtn></a>
					</td>";
                $tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfile('" . $val['id'] . "')\">" . $val['namafile'] . "</td>";
                if ($posting == 0) {
                    $tab .= "<td align=center width=30px><a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";

                    $tab .= "<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('" . $val['notransaksi'] . $val['kegiatan'] . "','" . $val['namafile'] . "');\" ></td>";
                } else {
                    $tab .= "<td align=center width=30px colspan=2><a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
                }
                $tab .= "</tr>";
            }
        }
        echo $tab;
        break;
    case 'deletefile':
        $str = "delete from " . $dbname . ".listfileupload where notransaksi='" . $param['notransaksi'] . "' and namafile='" . $param['namafile'] . "'";
        try {
            $owlPDO->exec($str);
            $pathx = $path . $param['namafile'];
            #sementara tidak boleh ada unlink
            //unlink($pathx);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case 'viewfile':
        $tab = "";
        $str = "select * from " . $dbname . ".listfileupload where id = '" . $param['idfile'] . "'";
        $res = fetchData($str);
        if ($res[0]['formaticon'] == '.xls' or $res[0]['formaticon'] == '.xlsx' or $res[0]['formaticon'] == '.doc' or $res[0]['formaticon'] == '.docx') {
            exit("Warning: Tidak bisa ditampilkan, silahkan download.");
        }

        if ($res[0]['formaticon'] == '.pdf') {
            $tab .= "<embed src='" . $path . $res[0]['namafile'] . "' style='width:100%;height:97%;' type='application/pdf'>";
        } else {
            $tab .= "<img src='" . $path . $res[0]['namafile'] . "'>";
        }

        echo $tab;
        break;

    case 'viewbiaya':
        $shead = "select * from " . $dbname . ".project
                   where kode='" . $kode . "'";
        $qhead = $owlPDO->query($shead) or die(print " Gagal: " . PDOException::getMessage());
        $qhead->setFetchMode(PDO::FETCH_ASSOC);
        $rhead = $qhead->fetch();
        $tab .= "<link rel=stylesheet type=text/css href=style/generic.css><table cellpadding=5 cellspacing=1 border=0 class=sortable><thead><tr>";

        $tab .= "<th align=center>No.</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['nojurnal'] . "</th>";
        $tab .= "<th align=center>Akun " . $_SESSION['lang']['biaya'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['namaakun'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['noreferensi'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['debet'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['kredit'] . "</th>";
        $tab .= "<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th></tr></thead><tbody>";

        // $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc
        //  from " . $dbname . ".keu_jurnaldt_vw where kodeasset='" . $kode . "' and noakun like '129%'";
        $sDetail = "select keterangan,noreferensi,nojurnal,tanggal,debet,kredit,noakun,kodevhc
                     from " . $dbname . ".keu_jurnaldt_vw where kodeasset='" . $kode . "' and noakun like '129%'";
        $qDetail = $owlPDO->query($sDetail) or die(print " Gagal: " . PDOException::getMessage());
        $row = owlBaris($qDetail);

        if ($row != 0) {
            $qDetail->setFetchMode(PDO::FETCH_ASSOC);
            $nor = $tdb = $tkr = 0;
            while ($rDetail = $qDetail->fetch()) {
                $nor += 1;

                $svhc = "select jenispekerjaan from " . $dbname . ".vhc_rundt_vw
                   where kodevhc='" . $rDetail['kodevhc'] . "' and alokasibiaya='" . $kode . "'";
                $qvhc = $owlPDO->query($svhc) or die(print " Gagal: " . PDOException::getMessage());
                $qvhc->setFetchMode(PDO::FETCH_ASSOC);
                $rvhc = $qvhc->fetch();

                $whrak = "noakun='" . $rvhc['jenispekerjaan'] . "'";
                $optakun = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', $whrak);
                $nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $rDetail['noakun'] . "'");

                $tab .= "<tr class=rowcontent>";
                $tab .= "<td align=center>" . $nor . "</td>";
                $tab .= "<td align=left>" . $rDetail['tanggal'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['nojurnal'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['noakun'] . "</td>";
                $tab .= "<td align=center>" . $nmAkun[$rDetail['noakun']] . "</td>";
                $tab .= "<td align=left>" . $rDetail['keterangan'] . "</td>";
                $tab .= "<td align=left>" . $rDetail['noreferensi'] . "</td>";
                $tab .= "<td align=right>" . number_format($rDetail['debet'], 2) . "</td>";
                $tab .= "<td align=right>" . number_format($rDetail['kredit'], 2) . "</td>";
                $tab .= "<td align=left>" . $rvhc['jenispekerjaan'] . " - " . $optakun[$rvhc['jenispekerjaan']] . "</td></tr>";
                $tdb += $rDetail['debet'];
                $tkr += $rDetail['kredit'];
            }
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td colspan=7 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
            $tab .= "<td align=right><b>" . number_format($tdb, 2) . "</td>";
            $tab .= "<td align=right><b>" . number_format($tkr, 2) . "</td>";
            $tab .= "<td align=right><b></td>";
            $tab .= "</tr>";
        } else {
            $tab .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab .= "</tbody></table>";

        // echo '<pre>';
        // print_r($rhead);
        // echo '</pre>';

        $tab .= "<br><br><table cellspacing=1 cellpadding=3 border=0 class='sortable'>
                <thead>
                    <tr class='rowheader'>
                        <th colspan='3'> Data </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['kodecapex'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['kode'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['unitkerja'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['kodeorg'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['posisiasset'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['aset'] . "</td>
                        <td>:</td>
                        <td>" . substr($rhead['kode'], 3, 2) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Sub " . $_SESSION['lang']['aset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['subtipe'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Tipe Lokasi " . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['tipelokasi'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['jenisbiaya'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['jenisbiaya'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>No Mesin</td>
                        <td>:</td>
                        <td>" . $rhead['nomesin'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tipe'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['tipe'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['pekerjaan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['pekerjaan'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>No Rangka</td>
                        <td>:</td>
                        <td>" . $rhead['norangka'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['nama'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['nama'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['satuan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['satuan'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['jumlah'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['jumlah'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['posisiasset'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['posisiasset'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tanggal'] . " Mulai</td>
                        <td>:</td>
                        <td>" . tanggalnormal($rhead['tanggalmulai']) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['tanggal'] . " Selesai</td>
                        <td>:</td>
                        <td>" . tanggalnormal($rhead['tanggalselesai']) . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>Tipe Model</td>
                        <td>:</td>
                        <td>" . $rhead['tipemodel'] . "</td>
                    </tr>
                    <tr class='rowcontent'>
                        <td>" . $_SESSION['lang']['keterangan'] . "</td>
                        <td>:</td>
                        <td>" . $rhead['keterangan'] . "</td>
                    </tr>
                </tbody>
    </table>";

        echo $tab;
        break;


    case 'viewasset':
        $tab .= "<link rel=stylesheet type=text/css href=style/generic.css>
         <div>
         <table class=sortable  border=0 cellspacing=1>
         <thead>
           <tr class=rowheader>
              <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
              <td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
              <td align=center>" . $_SESSION['lang']['posisiasset'] . "</td>
              <td align=center>" . $_SESSION['lang']['namakelompok'] . "</td>
              <td align=center>" . $_SESSION['lang']['kodeasset'] . "</td>
              <td align=center>" . $_SESSION['lang']['namaaset'] . "</td>
              <td align=center>" . $_SESSION['lang']['tanggalperolehan'] . "</td>
              <td align=center>" . $_SESSION['lang']['status'] . "</td>
              <td align=center>" . $_SESSION['lang']['hargaperolehan'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['jumlahbulanpenyusutan'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['persendecline'] . "</td>
              <td width=20px align=center>" . $_SESSION['lang']['awalpenyusutan'] . "</td>
              <td align=center>" . $_SESSION['lang']['tanggaldisposal'] . "</td>
              <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
              <td align=center>Leasing</td>
              <td align=center>" . $_SESSION['lang']['project'] . "</td>
            </tr>
          </thead>         
          <tbody id=containeraset>";

        if ($_SESSION['language'] == 'EN') {
            $ads = "b.namatipe1 as namatipe";
        } else {
            $ads = "b.namatipe as namatipe";
        }
        $str = "select a.*," . $ads . ",a.namasset as namassett from " . $dbname . ".sdm_daftarasset a
              left join  " . $dbname . ".sdm_5tipeasset b
              on a.tipeasset=.b.kodetipe where kodeproject='" . $kode . "'";
        // echo $str;

        $no = $offset;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $whr = "id='" . $bar->status . "'";
            $optjns = makeOption($dbname, 'keu_5jenisdisposalasset', 'id,keterangan', $whr);
            $no += 1;
            $tab .= " <tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td width=10 align=center>" . $bar->kodeorg . "</td>
                <td>" . $bar->posisiasset . "</td>
                <td>" . $bar->namatipe . "</td>
                <td width=70 align=center>" . $bar->kodeasset . "</td>
                <td>" . $bar->namassett . "</td>
                <td width=20 align=center>" . tanggalnormal($bar->tanggalperolehan) . "</td>
                <td width=20 align=center>" . $optjns[$bar->status] . "</td>
                <td width=100 align=right>" . number_format($bar->hargaperolehan, 2, '.', ',') . "</td>
                <td width=20 align=right>" . $bar->jlhblnpenyusutan . "</td>
                <td align=right>" . $bar->persendecline . "</td>
                <td align=center>" . ($bar->awalpenyusutan) . "</td>
            
                <td align=center>" . tanggalnormal($bar->tanggaldisposal) . "</td>
                <td>" . $bar->keterangan . "</td>
                <td>" . $kamusleasing[$bar->leasing] . "</td>
                <td>" . $bar->kodeproject . "</td>
            </tr>";
        }
        $tab .= "
                     </tbody>
                     <tfoot>
                     </tfoot>
                     </table>
                     </div>";

        $stream .= "<link rel=stylesheet type=text/css href=style/generic.css><table class='sortable' cellspacing='1' border='0'>
	 			<thead>
					<tr>
						<th align=center >" . $_SESSION['lang']['nourut'] . "</th>
						<th align=center >" . $_SESSION['lang']['nojurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodejurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['namajurnal'] . "</th>
						<th align=center >" . $_SESSION['lang']['tipe'] . "</th>
						<th align=center >" . $_SESSION['lang']['novoucher'] . "</th>
						<th align=center >" . $_SESSION['lang']['tanggal'] . "</th>
						<th align=center >" . $_SESSION['lang']['unit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noakun'] . "</th>
						<th align=center >" . $_SESSION['lang']['namaakun'] . "</th>
						<th align=center >Tipe Pembayaran</th>
						<th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
						<th align=center >" . $_SESSION['lang']['debet'] . "</th>
						<th align=center >" . $_SESSION['lang']['kredit'] . "</th>
						<th align=center >" . $_SESSION['lang']['noreferensi'] . "</th>    
						<th align=center >" . $_SESSION['lang']['kodeblok'] . "</th>
						<th align=center >" . $_SESSION['lang']['tahuntanam'] . "</th>
						<th align=center >" . $_SESSION['lang']['kodekegiatan'] . "</th>
						<th align=center >" . $_SESSION['lang']['namakegiatan'] . "</th>
						<th align=center >" . $_SESSION['lang']['revisi'] . "</th>
					</tr>  
				</thead>
				<tbody>";
        $tdebet = $tkredit = 0;
        $str = "select a.*,b.namaakun,c.novoucher,c.cgttu from " . $dbname . ".keu_jurnaldt_vw a
				left join " . $dbname . ".keu_5akun b
				on a.noakun=b.noakun
				left join " . $dbname . ".keu_kasbankht c on a.noreferensi=c.notransaksi 
				where a.nodok='" . $kode . "' and a.nojurnal NOT LIKE '%CLSM%' and a.revisi<='0' 
				order by a.nojurnal, a.nourut";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no += 1;
            $debet = 0;
            $kredit = 0;
            if ($bar->jumlah > 0)
                $debet = $bar->jumlah;
            else
                $kredit = $bar->jumlah * -1;

            $stream .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td>" . $bar->nojurnal . "</td>
						<td>" . $bar->kodejurnal . "</td>
						<td>" . $namajurnal[$bar->kodejurnal] . "</td>";

            $stream .= "<td>" . $nmauto[$bar->autojurnal] . "</td>";


            $stream .= "   <td>" . $bar->novoucher . "</td>
						<td >" . tanggalnormal($bar->tanggal) . "</td>
						<td align=center >" . $bar->kodeorg . "</td>
						<td>" . $bar->noakun . "</td>
						<td>" . $bar->namaakun . "</td>
						<td>" . $bar->cgttu . "</td>
						<td>" . $bar->keterangan . "</td>
						<td align=right  >" . number_format($debet, 2) . "</td>
						<td align=right  >" . number_format($kredit, 2) . "</td>
						<td align=center>" . $bar->noreferensi . "</td>    
						<td align=center>" . $bar->kodeblok . "</td>
						<td align=center>" . (isset($tahuntanam[$bar->kodeblok]) ? $tahuntanam[$bar->kodeblok] : '') . "</td>
						<td align=center>" . $bar->kodekegiatan . "</td>
						<td align=center>" . @$namakegiatan[$bar->kodekegiatan] . "</td>
						<td align=center >" . $bar->revisi . "</td>
						</tr>";
            $tdebet += $debet;
            $tkredit += $kredit;
        }
        $stream .= "<tr class=rowcontent>
					<td align=center colspan=12>Total</td>
					<td align=right >" . number_format($tdebet, 2) . "</td>
					<td align=right >" . number_format($tkredit, 2) . "</td>
					<td align=center colspan=6></td>
					</tr>";
        $stream .= "</tbody>
			<tfoot>
			</tfoot>		 
		</table>";

        $str = "select * from " . $dbname . ".sdm_daftarasset where kodeproject='" . $kode . "'";
        $res = fetchdata($str);
        if (count($res) > 0) {
            echo $tab;
        } else {
            echo $stream;
        }
        break;
    case 'searchnocapex':
        $tab = "<table>
            <tr>
                <td>Cari Capex</td>
                <td>:</td>
                <td>
                    <input type=text id=crcapex size=25 style=width:100px class=myinputtext>
                </td>
                <td>
                    <button onclick=caricapex() class=mybutton>" . $_SESSION['lang']['find'] . "</button>
                </td>
            </tr>
        </table><hr>";

        $tab .= "<div id='listnorequest'><table class=sortable border=0 cellspacing=1 cellpadding=5>
        <thead>
        <tr>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>Kode Capex</td>
            <td align=center>Nama Capex</td>
        </tr>
        </thead>
        <tbody>";

        $str = "select * from " . $dbname . ".spl_capexbangunan where posting='1' and kode not in (select kodecapex from " . $dbname . ".project)";
        $res = fetchData($str);
        $no = 0;
        foreach ($res as $key => $val) {
            $expsub = explode('-', $val['kode']);
            $subtipeaset = substr($expsub[1], 0, 2);
            $no++;
            $tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['kode'] . "','" . $val['kodeorg'] . "','" . $subtipeaset . "','" . $val['subtipe'] . "','" . $val['jenis_biaya'] . "','" . $val['tipebg'] . "','" . $val['pekerjaan'] . "','" . $val['nama'] . "','" . tanggalnormal($val['tanggalmulai']) . "','" . tanggalnormal($val['tanggalselesai']) . "')\">";
            $tab .= "<td style='text-align:right'>" . $no . "</td>";
            $tab .= "<td>" . $val['kode'] . "</td>";
            $tab .= "<td>" . $val['nama'] . "</td>";
            $tab .= "</tr>";
        }
        $tab .= "</tbody>
        </table>
        </div>";
        echo $tab;
        break;

    case 'caricapex':
        $tab .= "<table class=sortable border=0 cellspacing=1 cellpadding=5>
        <thead>
        <tr>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>Kode Capex</td>
            <td align=center>Nama Capex</td>
        </tr>
        </thead>
        <tbody>";

        $str = "select * from " . $dbname . ".spl_capexbangunan where posting='1' and (kode like '%" . $crcapex . "%' or nama like '%" . $crcapex . "%') and kode not in (select kodecapex from " . $dbname . ".project)";
        $res = fetchData($str);
        $no = 0;
        if (count($res) <= 0) {
            $tab .= "<tr class='rowcontent'>";
            $tab .= "<td colspan=3 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td>";
            $tab .= "</tr>";
        } else {
            foreach ($res as $key => $val) {
                $expsub = explode('-', $val['kode']);
                $subtipeaset = substr($expsub[1], 0, 2);
                $no++;
                $tab .= "<tr class='rowcontent' style='cursor:pointer;' title='Show Detail' onclick=\"showdetail('" . $val['kode'] . "','" . $val['kodeorg'] . "','" . $subtipeaset . "','" . $val['subtipe'] . "','" . $val['jenis_biaya'] . "','" . $val['tipebg'] . "','" . $val['pekerjaan'] . "','" . $val['nama'] . "','" . tanggalnormal($val['tanggalmulai']) . "','" . tanggalnormal($val['tanggalselesai']) . "')\">";
                $tab .= "<td style='text-align:right'>" . $no . "</td>";
                $tab .= "<td>" . $val['kode'] . "</td>";
                $tab .= "<td>" . $val['nama'] . "</td>";
                $tab .= "</tr>";
            }
        }
        $tab .= "</tbody>
        </table>";
        echo $tab;
        break;

    case 'getSub':

        $optSub = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $iSub = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='" . $aset . "' ";
        $res = $owlPDO->query($iSub) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dSub =  $res->fetch()) {
            if ($_POST['sub'] == $dSub['kodesub']) {
                $select = "selected=selected";
            } else {
                $select = "";
            }
            $optSub .= "<option " . $select . " value='" . $dSub['kodesub'] . "'>" . $dSub['namasub'] . "</option>";
        }



        echo $optSub;



        break;

    case 'getjbiaya':
        $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $optjb .= "<option value='2' selected=selected>Biaya Tidak Langsung</option>";
        // $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        // $arjb = getEnum($dbname, 'project', 'jenis_biaya');
        // foreach ($arjb as $kei => $fal) {
        // if ((substr($unit,2,2)=='HO')&&($fal!=3)){
        // continue;
        // }

        // if ((substr($unit,2,2)!='HO')&&($fal==3)){
        // continue;
        // }

        // if ($fal==1){
        // $capt="Biaya Langsung";
        // }
        // if ($fal==2){
        // $capt="Biaya Tidak Langsung";
        // }
        // if ($fal==3){
        // $capt="Operasi";
        // }

        // if($jenisbiaya==$fal)
        // {
        // $optjb.="<option value='" . $kei . "' selected=selected>" . $capt . "</option>";
        // }
        // else{
        // $optjb.="<option value='" . $kei . "'>" . $capt . "</option>";
        // }
        // }
        echo $optjb;

        break;


    case 'update':
        // exit('error'.$aprv2);
        $str = "update " . $dbname . ".project set nama='" . $nama . "',
          tanggalmulai='" . $tanggalmulai . "',tanggalselesai='" . $tanggalselesai . "',
          updateby='" . $_SESSION['standard']['userid'] . "',subtipe='" . $sub . "',jenis_biaya='" . $jenisbiaya . "', jumlah='" . $jumlah . "',
          posisiasset='" . $posisiasset . "',tipelokasi='" . $tipelokasiasset . "',nomesin='" . $nomesin . "',norangka='" . $norangka . "',
          keterangan='" . $keterangan . "', tipemodel='" . $tipemodel . "',dgnapproval='" . $dgnapprvl . "'
          where kode='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        for ($i = 1; $i <= 4; $i++) {
            $aprv = checkPostGet('aprv' . $i, '');
            $strU = "update " . $dbname . ".project_approval set karyawanid='" . $aprv . "'where kode='" . $kode . "' and level='" . $i . "'";
            try {
                $owlPDO->exec($strU);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;

    case 'insert':
        // String Kode
        $kode = $jenis . '-' . $aset . $sub;

        // cari nomor terakhir
        $str = "select kode from " . $dbname . ".project where kode like '" . $kode . "%' order by substring(kode, -5) desc  limit 1";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $belakangnya = intval(substr($bar->kode, -5));
        }
        $belakangnya += 1;

        $belakangnya = addZero($belakangnya, 10 - strlen($aset . $sub));
        $kode = $jenis . "-" . $aset . $sub . $belakangnya;
        $str = "insert into " . $dbname . ".project (kode, nama, tipe, tipebg,pekerjaan,kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,jenis_biaya,kodecapex,satuan,jumlah,keterangan,tipemodel,norangka,nomesin,posisiasset,tipelokasi,dgnapproval)
              values('" . $kode . "','" . $nama . "','" . $jenis . "','" . $tipebg . "','" . $pekerjaan . "','" . $unit . "','" . $tanggalmulai . "','" . $tanggalselesai . "'," . $_SESSION['standard']['userid'] . ",'" . $sub . "','" . $jenisbiaya . "','" . $kodecapex . "','" . $satuan . "','" . $jumlah . "','" . $keterangan . "','" . $tipemodel . "','" . $norangka . "','" . $nomesin . "','" . $posisiasset . "','" . $tipelokasiasset . "','" . $dgnapprvl . "')";
        try {
            $owlPDO->exec($str);
            if ($kodecapex != '') {
                $str = "select * from " . $dbname . ".spl_capexbangunandt where kodeproject='" . $kodecapex . "'";
                $res = fetchData($str);
                foreach ($res as $key => $val) {
                    $str2 = "select kegiatan from " . $dbname . ".project_dt order by kegiatan desc limit 1";
                    $res2 = fetchData($str2);
                    $newkode = addZero(($res2[0]['kegiatan'] + 1), 8);

                    $str3 = "insert into " . $dbname . ".project_dt (kegiatan,kodeproject,deskripsi,namakegiatan,tanggalmulai,tanggalselesai,satuan,volume,bobot) values ('" . $newkode . "','" . $kode . "','" . $val['deskripsikegiatan'] . "','" . $val['namakegiatan'] . "','" . $val['tanggalmulai'] . "','" . $val['tanggalselesai'] . "','" . $val['satuan'] . "','" . $val['volume'] . "','" . $val['bobot'] . "')";
                    try {
                        $owlPDO->exec($str3);

                        $str2 = "select * from " . $dbname . ".spl_capexbangunanmaterial where kodeproject='" . $kodecapex . "' and kodekegiatan='" . $val['kegiatan'] . "'";
                        $res2 = fetchData($str2);
                        foreach ($res2 as $key2 => $val2) {
                            $str3 = "insert into " . $dbname . ".project_material (kodeproject,kodekegiatan,kodebarang,jumlah,updateby) values ('" . $kode . "','" . $newkode . "','" . $val2['kodebarang'] . "','" . $val['jumlah'] . "','" . $_SESSION['standard']['userid'] . "')";
                            try {
                                $owlPDO->exec($str3);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        if ($dgnapprvl == '1') {
            for ($i = 1; $i <= 4; $i++) {

                $aprv = checkPostGet('aprv' . $i, '');
                if (@$aprv . $i != '') {
                    $stra = "INSERT INTO " . $dbname . ".`project_approval` (`kode`,`level`,`karyawanid`,`createby`)
                    values('" . $kode . "','" . $i . "','" . $aprv . "','" . $_SESSION['standard']['userid'] . "')";
                    try {
                        $owlPDO->exec($stra);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                    // for ($i=1; $i <5 ; $i++) {  

                    ##SIMPAN APPROVAL PROJECT
                    // $strapr="INSERT INTO ".$dbname.".`approval` (`notransaksi`,`jenispersetujuan`,`level`, `karyawanid`)
                    // values('".$kode."','PROJ','".$i."','".$aprv."')";
                    // try{$owlPDO->exec($strapr); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

                    // } 
                }
                // exit('error '.$aprv.$i);
            }
            $str = "update " . $dbname . ".project set statuspersetujuan='9' where kode='" . $kode . "'";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;

    case 'delete':
        $sGudang = "select * from " . $dbname . ".log_transaksi_vw where kodeblok = '" . $kode . "'";
        $res = $owlPDO->query($sGudang) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cGudang = $numrows;

        $sKasBank = "select * from " . $dbname . ".keu_kasbankdt where kodeasset = '" . $kode . "'";
        $res = $owlPDO->query($sKasBank) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cKasBank = $numrows;

        $sBaSpk = "select * from " . $dbname . ".log_baspk where kodeblok = '" . $kode . "'";
        $res = $owlPDO->query($sBaSpk) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cBaSpk = $numrows;

        $sJurnal = "select * from " . $dbname . ".keu_jurnaldt_vw where kodeasset = '" . $kode . "'";
        $res = $owlPDO->query($sJurnal) or die(print " Gagal: " . PDOException::getMessage());
        $numrows = owlBaris($res);
        $cJurnal = $numrows;

        if ($cGudang > 0 || $cKasBank > 0 || $cBaSpk > 0 || $cJurnal > 0) {
            exit("Gagal: Item ini tidak dapat dihapus, sudah ada transaksi.");
        }

        $str = "delete from " . $dbname . ".project_material where kodeproject='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "delete from " . $dbname . ".project_dt where kodeproject='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "delete from " . $dbname . ".project where kode='" . $kode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'loadData':
        // exit('warning : ');
        $where = "";
        // $where = " dibuat_oleh ='".$_SESSION['standard']['userid']."'";
        $whrunit="";
        $whrunit = " and kodeorg in (".getOrgDetail(2).")";
        if ($kodecr != '') {
            $where .= " and a.kode like '%" . $kodecr . "%' ";
        }
        if ($namacr != '') {
            $where .= " and a.nama like '%" . $namacr . "%' ";
        }
        if ($unitcr != '') {
            $where .= " and a.kodeorg like '%" . $unitcr . "%' ";
        }
        // if ($tglcr != '') {
        //     $where.=" and tanggal='" . $tglcr . "' ";
        // }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0 or !is_numeric($page))
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $str = "select a.*,b.namakaryawan from " . $dbname . ".project a left join " . $dbname . ".datakaryawan b on a.updateby=b.karyawanid where 1=1 " . $where . $whrunit;
        // exit ('warning : '.$str);
        $res = fetchdata($str);
        $jlhbrs = count($res);
        if ($jlhbrs == 0) {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td colspan=13 align=center>" . $_SESSION['lang']['dataempty'] . "</td>";
            $tab .= "</tr>";
        } else {
            $no = $maxdisplay;
            $str = "select a.*,b.namakaryawan,substr(kode,4,2) as aset from " . $dbname . ".project a left join " . $dbname . ".datakaryawan b on a.updateby=b.karyawanid  where 1=1 " . $where . $whrunit . " order by substring(kode, -7) desc limit " . $offset . "," . $limit . "";
            $tab = "";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar1 = $res->fetch()) {
                $kdAst = substr($bar1->kode, 3, 2);
                $iSubAst = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='" . $kdAst . "' ";
                $resx = $owlPDO->query($iSubAst) or die(print " Gagal: " . PDOException::getMessage());
                $resx->setFetchMode(PDO::FETCH_ASSOC);
                $dSubAst = $resx->fetch();


                #= cek apakah jadi asset / jadi biaya
                $strdt = "select kodeasset from " . $dbname . ".sdm_daftarasset  where kodeproject='" . $bar1->kode . "'";
                $resdt = fetchdata($strdt);
                @$kodeasset = $resdt[0]['kodeasset'];



                $stra = "select max(level) as level from " . $dbname . ".approval where  jenispersetujuan='PROJ' and karyawanid!='0000000000'";
                $resx = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
                $resx->setFetchMode(PDO::FETCH_ASSOC);
                $barx = $resx->fetch();
                $countAppJ = $barx['level'];

                #= cek APPROVAL  




                #= ambil data nilai
                //  $sBiaya = "select distinct sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt
                //    where kodeasset='" . $bar1->kode . "' and noakun like '1299%'";
                $sBiaya = "select distinct sum(jumlah) as biaya from " . $dbname . ".keu_jurnaldt
                   where kodeasset='" . $bar1->kode . "' and noakun like '12903%'";
                $qBiaya = $owlPDO->query($sBiaya) or die(print " Gagal: " . PDOException::getMessage());
                $qBiaya->setFetchMode(PDO::FETCH_ASSOC);
                $rBiaya = $qBiaya->fetch();
                $tab .= "<tr class=rowcontent title='Show Detail Data'>
                <td onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->kode . "</td>
                <td onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->kodeorg . "</td>
                <td onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->tipe . "</td>
                <td onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->nama . "</td>
                <td onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->pekerjaan . "</td>
                <td align=center  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->satuan . "</td>
                <td align=right  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . @number_format($bar1->jumlah) . "</td>

                <td align=center  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . tanggalnormal($bar1->tanggalmulai) . "</td>
                <td align=center  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . tanggalnormal($bar1->tanggalselesai) . "</td>
                 <td  align=right  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . number_format($rBiaya['biaya'], 2) . "</td>
                <td  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $kodeasset . "</td>
                <td  onclick=\"viewbiaya('" . $bar1->kode . "');\" style='cursor:pointer;' title='Show Detail Biaya' >" . $bar1->namakaryawan . "</td>
                ";
                $namasts = array(1 => 'Approved', 0 => 'Waiting', 2 => 'Ditolak');

                // for ($i=1; $i <= 4; $i++) {  
                //     $stra = "select * from ".$dbname.".approval  where notransaksi='".$bar1->kode."' and level='".$i."' ";
                //     $resa=fetchdata($stra);
                //     $cekdata=count($resa);
                //     @$kryApr = $resa[0]['karyawanid'];  
                //     @$stsApr = $resa[0]['status'];  
                //     @$komentarApr = $resa[0]['komentar'];  
                //     @$tglApr = $resa[0]['tanggal'];  
                //     if ($kryApr!=0) { 
                //         $tab.="<td width=100px align=center> <b>".$nmKar[$kryApr]."</b><br/> <br/>".$tglApr."</td>";  
                //     } else {
                //         $tab.="<td width=50px align=center> <b> - </b> </td>"; 
                //     }
                // }  

                // for ($i=1; $i <= 4; $i++) {  
                $strb = "select * from " . $dbname . ".project_approval  where kode='" . $bar1->kode . "' and level='1' ";
                $resa = fetchdata($strb);
                @$kryApr1 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . $bar1->kode . "' and level='2' ";
                $resa = fetchdata($strb);
                @$kryApr2 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . $bar1->kode . "' and level='3' ";
                $resa = fetchdata($strb);
                @$kryApr3 = $resa[0]['karyawanid'];

                $strb = "select * from " . $dbname . ".project_approval  where kode='" . $bar1->kode . "' and level='4' ";
                $resa = fetchdata($strb);
                @$kryApr4 = $resa[0]['karyawanid'];

                // }   
                // echo $kryApr;
                //if(($bar1->posting==0 and $bar1->updateby==$_SESSION['standard']['userid']) || ($bar1->posting==0 and in_array($_SESSION['empl']['kodejabatan'],$postJabatan))){
                if ($bar1->posting == 0) {
                    $tab .= "<td width=25px align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $bar1->kodeorg . "','" . $bar1->aset . "','" . $bar1->tipe . "','" . $bar1->nama . "','" . tanggalnormal($bar1->tanggalmulai) . "','" . tanggalnormal($bar1->tanggalselesai) . "','update','" . $bar1->kode . "','" . $bar1->subtipe . "','" . $bar1->jenis_biaya . "','" . $bar1->tipebg . "','" . $bar1->pekerjaan . "','" . $bar1->kodecapex . "','" . strtoupper($bar1->satuan) . "','" . $bar1->jumlah . "','" . $bar1->keterangan . "','" . $bar1->posisiasset . "','" . $bar1->tipelokasi . "','" . $bar1->nomesin . "','" . $bar1->norangka . "','" . $bar1->tipemodel . "','" . $bar1->dgnapproval . "','" . $bar1->statuspersetujuan . "','" . $kryApr1 . "','" . $kryApr2 . "','" . $kryApr3 . "','" . $kryApr4 . "');\"></td>
                    <td width=25px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"hapus('" . $bar1->kode . "');\"></td>";

                    $tab .= "<td width=25px align=center> <img src=images/skyblue/posting.png class=zImgBtn  title='posting data " . $bar1->nama . "' onclick=\"postIni('" . $bar1->kode . "','" . $bar1->kodeorg . "','" . $bar1->jenis_biaya . "','" . $bar1->posisiasset . "');\"></td>";

                    $tab .= "<td width=25px align=center> <img src=images/nxbtn.png class=zImgBtn  title='Detail' onclick=\"detailForm('" . $bar1->kodeorg . "','" . $aset . "','" . $bar1->tipe . "','" . $bar1->nama . "','" . tanggalnormal($bar1->tanggalmulai) . "','" . tanggalnormal($bar1->tanggalselesai) . "','detail','" . $bar1->kode . "','" . $bar1->subtipe . "','" . $bar1->jenis_biaya . "','" . $bar1->tipebg . "','" . $bar1->pekerjaan . "','" . $bar1->kodecapex . "','" . $bar1->satuan . "','" . $bar1->jumlah . "','" . $bar1->keterangan . "','" . $bar1->posisiasset . "','" . $bar1->tipelokasi . "','" . $bar1->nomesin . "','" . $bar1->norangka . "');\"></td>";
                } else {
                    // $tab.="<td width=25px align=center> <img src=images/zoom.png class=zImgBtn  title='View Detail Asset ".$bar1->nama."' onclick=\"viewasset('".$bar1->kode."');\"></td>";   
                    if ($bar1->posting == 1) {
                        $tab .= "<td width=25px align=center> </td>";
                        $tab .= "<td width=25px align=center><img src=images/zoom.png class=zImgBtn  title='View Detail Asset " . $bar1->nama . "' onclick=\"viewasset('" . $bar1->kode . "');\"></td>";
                        $tab .= "<td width=25px align=center> <img src=images/skyblue/posted.png class=zImgBtn></td>";
                        $tab .= "<td width=25px align=center> </td>";
                    }
                    // $tab."<td width=25px align=center> <img src=images/skyblue/posting.png class=zImgBtn></td>"; 
                }
                // if (($bar1->statuspersetujuan==0)||($bar1->statuspersetujuan==2)) { 

                if ($bar1->dgnapproval == '1') {
                    $tab .= "<td width=25px align=center><img onclick=approval('" . $bar1->kode . "','" . $bar1->kodeorg . "','" . $bar1->jenis_biaya . "') src=images/approve.png class=zImgBtn title='Approval'></td>";
                } else {
                    $tab .= "<td width=25px align=center> </td>";
                }
                // } else if ($bar1->posting==1){ 
                //     $tab.="<td width=25px align=center>  </td>";
                // }
                $tab .= "
                    <td width=25px align=center>
                        <img onclick=\"masterPDF('project','" . $bar1->kode . "," . $bar1->updateby . "','','vhc_slave_project_pdf',event);\" title=\"Print\" class=\"zImgBtn\" src=\"images/pdf.jpg\"></td>
					<td width=25px align=center><img onclick=excelMaterial(event,'" . $bar1->kode . "') src=images/excel.jpg class=zImgBtn title='MS.Excel Material'></td>
					<td width=25px align=center><img onclick=timeFrame(event,'" . $bar1->kode . "') src=images/excel.jpg class=zImgBtn title='MS.Excel Time Frame Project'>
            </td></tr>";
            }
            $totrows = ceil($jlhbrs / $limit);

            if ($totrows == 0) {
                $totrows = 1;
            }
            $isiRow = '';
            for ($er = 1; $er <= $totrows; $er++) {
                $sel = ($page == $er - 1) ? 'selected' : '';
                $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
            }
            $footd = "
                <tr><td colspan=30 align=center>
                <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
                <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        }
        echo $tab . "####" . $footd;
        break;
    case 'detail':
        $sDet = "select distinct * from " . $dbname . ".project_dt  where kodeproject='" . $kode . "'";
        $resx = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_ASSOC);
        $numrows = owlBaris($resx);
        $frmdt = "";
        if ($numrows == 0) {
            $frmdt .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            while ($rDet = $resx->fetch()) {
                $frmdt .= "<tr class=rowcontent><td>" . $rDet['kodeproject'] . "</td>";
                $frmdt .= "<td>" . $rDet['deskripsi'] . "</td>";
                $frmdt .= "<td>" . $rDet['namakegiatan'] . "</td>";
                $frmdt .= "<td>" . $rDet['satuan'] . "</td>";
                $frmdt .= "<td align=right>" . $rDet['volume'] . "</td>";
                $frmdt .= "<td align=right>" . $rDet['bobot'] . "</td>";
                $frmdt .= "<td>" . tanggalnormal($rDet['tanggalmulai']) . "</td>";
                $frmdt .= "<td>" . tanggalnormal($rDet['tanggalselesai']) . "</td>";
                $frmdt .= "<td>
                <img src=images/zoom.png title='" . $_SESSION['lang']['find'] . "' id=tmblCariNoGudang class=zImgBtn onclick=tambahBarang('" . $rDet['kegiatan'] . "','" . $rDet['kodeproject'] . "','" . $_SESSION['lang']['find'] . "',event)>
                <img src=images/application/application_edit.png class=zImgBtn name='excapex2' title='Edit' onclick=\"editDet('" . tanggalnormal($rDet['tanggalmulai']) . "','" . tanggalnormal($rDet['tanggalselesai']) . "','updatedet','" . $rDet['kodeproject'] . "','" . $rDet['kegiatan'] . "','" . $rDet['deskripsi'] . "','" . $rDet['namakegiatan'] . "','" . $rDet['satuan'] . "','" . $rDet['volume'] . "','" . $rDet['bobot'] . "');\">
                <img src=images/application/application_delete.png class=zImgBtn name='excapex3' title='Delete' onclick=\"hapusData('" . $rDet['kegiatan'] . "');\">

                <img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30' title='Upload' onclick=\"showupload('" . $rDet['kodeproject'] . $rDet['kegiatan'] . "');\"></td>
                </tr>";
            }
        }
        echo $frmdt;

        break;

    case 'detailAK':
        $sDet = "select distinct * from " . $dbname . ".project_dt  where kodeproject='" . $kode . "'";
        $resx = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_ASSOC);
        $numrows = owlBaris($resx);
        $frmdt = "";
        if ($numrows == 0) {
            $frmdt .= "<tr class=rowcontent><td colspan=9>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        } else {
            while ($rDet =  $resx->fetch()) {
                $frmdt .= "<tr class=rowcontent><td>" . $rDet['kodeproject'] . "</td>";
                $frmdt .= "<td>" . $rDet['no_mesin'] . "</td>";
                $frmdt .= "<td>" . $rDet['no_rangka'] . "</td>";
                $frmdt .= "<td>" . $rDet['tahun_produksi'] . "</td>";
                $frmdt .= "<td>" . $rDet['tahun_prolehan'] . "</td>";
                $frmdt .= "<td>
                <img src=images/application/application_edit.png class=zImgBtn name='excapex2' title='Edit' onclick=\"editDetAK('" . $rDet['no_mesin'] . "','" . $rDet['no_rangka'] . "','updatedetAK','" . $rDet['kodeproject'] . "','" . $rDet['tahun_produksi'] . "','" . $rDet['tahun_prolehan'] . "','" . $rDet['kegiatan'] . "');\">
                <img src=images/application/application_delete.png class=zImgBtn name='excapex3' title='Delete' onclick=\"hapusDataAK('" . $rDet['kegiatan'] . "');\">
                </td></tr>";
            }
        }
        echo $frmdt;

        break;


    case 'insertDetail':
        $tglMul = tanggalsystem(checkPostGet('tglMul', ''));
        $tglakh = tanggalsystem(checkPostGet('tglSmp', ''));

        $sCek = "SELECT datediff('" . $tglakh . "', '" . $tglMul . "') as selisih";
        $resx = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_NUM);
        $data = $resx->fetch();
        if ($data['selisih'] < 0) {
            exit("Error:Tanggal Selesai Lebih Besar dari Tanggal Mulai");
        }
        $sInser = "insert into " . $dbname . ".project_dt (kodeproject,deskripsi, namakegiatan, tanggalmulai, tanggalselesai,satuan,volume,bobot)
             values ('" . $kode . "','" . $deskripsi . "','" . $_POST['nmKeg'] . "','" . tanggalsystem($_POST['tglMul']) . "','" . tanggalsystem($_POST['tglSmp']) . "','" . $satKeg . "','" . $volKeg . "','" . $bobotKeg . "')";
        //exit($sInser.'Error');
        try {
            $owlPDO->exec($sInser);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'insertDetailAK':
        $norangka = checkPostGet('norangka', '');
        $nomesin = checkPostGet('nomesin', '');
        $tahunproduksi = checkPostGet('tahunproduksi', '');
        $tahunprolehan = checkPostGet('tahunprolehan', '');


        $sInser = "insert into " . $dbname . ".project_dt (kodeproject,no_mesin, no_rangka, tahun_produksi, tahun_prolehan)
             values ('" . $kode . "','" . $nomesin . "','" . $norangka . "','" . $tahunproduksi . "','" . $tahunprolehan . "')";
        try {
            $owlPDO->exec($sInser);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'updatedetAK':
        $norangka = checkPostGet('norangka', '');
        $nomesin = checkPostGet('nomesin', '');
        $tahunproduksi = checkPostGet('tahunproduksi', '');
        $tahunprolehan = checkPostGet('tahunprolehan', '');
        $kegiatanx = checkPostGet('kegiatanx', '');


        $sUpdate = "update " . $dbname . ".project_dt set no_mesin='" . $nomesin . "', no_rangka='" . $norangka . "',
              tahun_produksi='" . $tahunproduksi . "', tahun_prolehan='" . $tahunprolehan . "' where kegiatan='" . $kegiatanx . "'";
        try {
            $owlPDO->exec($sUpdate);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'updatedet':
        $tglMul = tanggalsystem($_POST['tglMul']);
        $tglakh = tanggalsystem($_POST['tglSmp']);

        $sCek = "SELECT datediff('" . $tglakh . "', '" . $tglMul . "') as selisih";
        $resx = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $resx->setFetchMode(PDO::FETCH_NUM);
        $data = $resx->fetch();
        if ($data['selisih'] < 0) {
            exit("Error:Tanggal Selesai Lebih Kecil dari Tanggal Mulai");
        }
        $sUpdate = "update " . $dbname . ".project_dt set deskripsi='" . $deskripsi . "', namakegiatan='" . $_POST['nmKeg'] . "',
              tanggalmulai='" . tanggalsystem($_POST['tglMul']) . "', tanggalselesai='" . tanggalsystem($_POST['tglSmp']) . "',
              satuan='" . $satKeg . "',volume='" . $volKeg . "',bobot='" . $bobotKeg . "' where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sUpdate);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;




    case 'postingData':
        try {
            $owlPDO->beginTransaction();

            if ($param['alokasi'] == 'biaya' and $param['noakun'] == '') {
                throw new PDOException("Untuk alokasi biaya, nomor akun wajib terisi.");
            }

            #= cek periode akuntansi
            $sCek = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . substr(tanggalsystemn($param['tglpost']), 0, 7) . "' and tutupbuku=1";
            $rCek = fetchData($sCek);
            if (count($rCek) == 1) {
                // exit('warning: '.$_SESSION['lang']['unit'].' '.$_SESSION['lang']['tutup']);
                throw new PDOException("Untuk " . $unit . " periode " . substr(tanggalsystemn($param['tglpost']), 0, 7) . " sudah tutup ");
            }


            #= update cek apakah dibuatkan spk
            #= buat perbandingan nilai total sum spkdt dengan sum baspk
            #= jika nilai baspk < spkdt maka exit error

            $str = "SELECT sum(hasilkerjajumlah) as jumspk from " . $dbname . ".log_spkdt where kodeblok='" . $kode . "'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            @$jumspk = $bar['jumspk'];

            $str = "SELECT sum(hasilkerjarealisasi) as jumba from " . $dbname . ".log_baspk where kodeblok='" . $kode . "' and statusjurnal=1";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar = $res->fetch();
            @$jumba = $bar['jumba'];

            // Parse Kode
            $detailKode = explode('-', $kode);
            $tipe = substr($detailKode[1], 0, 2);
            $sPt = "select induk,tipe from " . $dbname . ".organisasi where kodeorganisasi='" . $unit . "'"; #ambil PT sesuai dengan unit yang dipilih
            $rPt = fetchData($sPt);
            $kodeorg = $rPt[0]['induk'];
            $tipeOrganissi = $rPt[0]['tipe'];

            // Get Parameter Jurnal
            $qParam = selectQuery($dbname, 'keu_5parameterjurnal', "noakundebet, noakunkredit", "kodeaplikasi='PRJ' and jurnalid='PRJ" . $tipe . "'");
            $resParam = fetchData($qParam);
            // Get Header Project
            $qH = selectQuery($dbname, 'project', "*", "kode='" . $kode . "'");
            $resH = fetchData($qH);
            $subtipe = $resH[0]['subtipe'];
            $jenis_biaya = $resH[0]['jenis_biaya'];
            $jumlahasset = $resH[0]['jumlah'];

            require_once('lib/cekakun.php');
            if ($param['noakun'] != '') {
                cekakunkb($param['noakun'], '', '', $param['karyawanid'], '', '', '', '', '', '');
            }

            // echo"<pre>";
            // print_r($param);
            // throw new PDOException("xxxxx");
            // $str="select * from ".$dbname.".project_dt where kodeproject='".$kode."'";
            // // echo $str;exit("Error:A");
            // $res=fetchData($str);
            // $noz = 0;
            // $nomesin=array();
            // $norangka=array();
            // foreach($res as $key=>$val){   
            // if($val['no_mesin']!=''){
            // $noz+=1;
            // @$nomesin[$noz]=trim($val['no_mesin']);
            // @$norangka[$noz]=trim($val['no_rangka']);
            // }
            // }


            // if(@count($nomesin)=='0'){
            // $nomesin='';
            // }
            // if(@count($norangka)=='0'){
            // $norangka='';
            // }

            #cek periode akutansi lalu
            #jika project diposting di atas dari periode akuntasi maka dibuatkan jurnal akumulasinya secara otomatis
            #contoh : asset mobil di unit A seharusnya diakui pada tanggal 2017-08-15, tetapi pada kenyataannya terlupa sehingga baru akan diinput pada periode akuntasi bulan 12
            #maka terbentuk jurnal akumulasi diperiode berjalan dilihat dari bulan awal penyusutannya dikurangi 1 bulan periode aktif
            $sPrdLalu = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and tutupbuku=0 order by periode desc";
            $rPrdLalu = fetchData($sPrdLalu);
            $periodeAktif = $rPrdLalu[0]['periode'];
            $cekTglPost = explode("-", $_POST['tglpost']);
            $periodePost = $cekTglPost[2] . $cekTglPost[1];
            if (empty($resParam)) {
                throw new PDOException("Parameter Jurnal 'PRJ" . $tipe . "' belum ada\nSilahkan hubungi pihak IT");
            }
            if ($tipe != 'TM') {
                // Get Nilai Project
                $qNilai = "SELECT SUM(jumlah) as jumlah FROM " . $dbname . ".keu_jurnaldt
							WHERE kodeasset='" . $kode . "' and noakun='" . $resParam[0]['noakunkredit'] . "' and nojurnal not like '%PRJ%'";
                $resNilai = fetchData($qNilai);


                $qNilai2 = "SELECT SUM(jumlah) as jumlah FROM " . $dbname . ".keu_jurnaldt
							WHERE kodeasset='" . $kode . "' and noakun='" . $resParam[0]['noakundebet'] . "' and nojurnal like '%PRJ%' having sum(jumlah)>0";
                $resNilai2 = fetchData($qNilai2);
                if (count($resNilai2) > 0) {
                    throw new PDOException("Sudah ada pengakuan asset");
                }

                if (empty($resNilai)) {
                    throw new PDOException("Project belum direalisasi. Data tidak dapat di posting");
                } elseif ($resNilai[0]['jumlah'] == 0) {
                    throw new PDOException("Nilai Project tidak ada. Data tidak dapat di posting");
                }
                $nilaitotal = $resNilai[0]['jumlah'];
                if ($jumlahasset > 1) {
                    $nilai = floor($nilaitotal / $jumlahasset);
                    // $nilaimurni = $nilaitotal/$jumlahasset;
                }

                $nilaitotalfloor = $nilai * $jumlahasset;
                $selisih = $nilaitotal - $nilaitotalfloor;

                // echo $nilai._.$nilaitotal._.$nilaitotalfloor._.$selisih;exit("Error:A");

                for ($i = 1; $i <= $jumlahasset; $i++) {

                    if ($i == $jumlahasset) {
                        $nilai = $nilai + $selisih;
                    }

                    // Default Segment
                    $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

                    /**
                     * Pendaftaran Asset
                     */
                    // Get Jumlah Bulan
                    $qSubTipe = selectQuery($dbname, 'sdm_5subtipeasset', "umurpenyusutan", "kodetipe='" . $tipe . "' and kodesub='" . $subtipe . "' and kodeorg='" . $unit . "'");
                    $resSubTipe = fetchData($qSubTipe);

                    if (empty($resSubTipe)) {
                        throw new PDOException("Sub Tipe " . $subtipe . " dari tipe asset " . $tipe . " belum terdaftar\nSilahkan hubungi IT");
                    }

                    // Kode Asset
                    $kodeAsset = $kodeorg . "-" . $tipe . $subtipe;
                    $qAsset = selectQuery(
                        $dbname,
                        'sdm_daftarasset',
                        "kodeasset",
                        "kodeasset like '" . $kodeAsset . "%' order by kodeasset desc limit 1"
                    );
                    $resAsset = fetchData($qAsset);
                    if (empty($resAsset)) {
                        $counterAsset = 1;
                    } else {
                        $counterAsset = substr($resAsset[0]['kodeasset'], 8, 6) + 1;
                    }
                    $kodeAsset .= str_pad($counterAsset, 6, '0', STR_PAD_LEFT);
                    /*
						0=tgl
						1=bulan
						2=tahun
						*/
                    #======================================================================#
                    # Jika Tgl < 15 maka bulan ini,
                    # Jika Tgl >= 15 maka bulan depan
                    #======================================================================#
                    // if(intval($cekTglPost[0])<15){
                    // 	$prdAwalPenyusutan=$cekTglPost[2]."-".$cekTglPost[1];
                    // }else{
                    // 	if(intval($cekTglPost[1])<10){
                    // 		$blnPnystan="0".(intval($cekTglPost[1])+1);
                    // 	}else{
                    // 		$blnPnystan=(intval($cekTglPost[1])+1);
                    // 	}
                    // 	if($cekTglPost[1]=='12'){
                    // 			$thnPnystan=(intval($cekTglPost[2])+1);
                    // 			$prdAwalPenyusutan=$thnPnystan."-01";
                    // 	}else{
                    // 		$prdAwalPenyusutan=$cekTglPost[2]."-".$blnPnystan;    
                    // 	}
                    // }
                    #=========================================================================#
                    # END
                    #=========================================================================#

                    #==========================================================================================#
                    # Kondisi Di Palma sesuai MOM
                    # Pemelihan menjadi naik asset tanggal 1-31 tetap bulan yang sama untuk penyusutannya
                    #==========================================================================================#
                    $prdAwalPenyusutan = $cekTglPost[2] . "-" . $cekTglPost[1];

                    $bulananpenyusutan = floor($nilai / $resSubTipe[0]['umurpenyusutan']);
                    if ($bulananpenyusutan == '') {
                        exit("Warning:Nilai bulanan salah");
                    }
                    // Data
                    $dataAsset = array(
                        'kodeorg'         => $unit,
                        'kodeasset'       => $kodeAsset,
                        'tipeasset'       => $tipe,
                        'tanggalperolehan' => tanggalsystemn($_POST['tglpost']),
                        'namasset'        => $namaaset,
                        'hargaperolehan'  => $nilai,
                        'jlhblnpenyusutan' => $resSubTipe[0]['umurpenyusutan'],
                        'keterangan'      => $resH[0]['keterangan'],
                        'awalpenyusutan'  => $prdAwalPenyusutan,
                        'user'            => $_SESSION['standard']['userid'],
                        'bulanan'         => $bulananpenyusutan,
                        'penambah'        => 0,
                        'pengurang'       => 0,
                        'nomesin'         => $param['nomesin'],
                        'norangka'        => $param['norangka'],
                        'kodeproject'     => $kode,
                        'subtipe'         => $subtipe,
                        'jenis_biaya'     => '2',
                        'posisiasset'     => $resH[0]['posisiasset'],
                        'tipelokasi'      => $resH[0]['tipelokasi'],
                        'tipemodel'       => $param['tipemodel']
                    );
                    // print_r($dataAsset);
                    // exit("Error:".$dataAsset);
                    $cols = array();
                    foreach ($dataAsset as $key => $row) {
                        $cols[] = $key;
                    }
                    $qIns = insertQuery($dbname, 'sdm_daftarasset', $dataAsset, $cols);
                    if ($param['alokasi'] == 'asset') {
                        #jika alokasi asset insert ke daftar asset, jika biaya abaikan
                        $owlPDO->exec($qIns);
                    }

                    /**
                     * Jurnal
                     */
                    # Get Journal Counter
                    $prd = substr(tanggalsystemn($param['tglpost']), 0, 7);
                    $queryJ = selectQuery(
                        $dbname,
                        'keu_5kelompokjurnal',
                        'nokounter',
                        "kodeunit='" . $param['unit'] . "' and " .
                            "kodekelompok='PRJ" . $tipe . "' and periode='" . $prd . "'"
                    );

                    $tmpKonter = fetchData($queryJ);
                    if (empty($tmpKonter)) {
                        // Jika Kelompok Jurnal belum ada, insert
                        $dataKel = array(
                            'kodeorg'     => $kodeorg,
                            'kodeunit'    => $param['unit'],
                            'kodekelompok' => "PRJ" . $tipe,
                            'periode'     => $prd,
                            'keterangan'  => "Project " . $tipe,
                            'nokounter'   => 0
                        );

                        $qKel = insertQuery($dbname, 'keu_5kelompokjurnal', $dataKel);
                        $owlPDO->exec($qKel);
                        $konter = '001';
                    } else {
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
                    }


                    #Prep tgl jurnal
                    #= revisi, jika periode pengakuan >= periode aktif maka prdposting = periode pengakuan
                    #= else, periode posting=periode aktif, dan akan terbentuk jurnal akumulasi

                    $tglpengakuan = tanggalsystemn($_POST['tglpost']);
                    $prdpengakuan = substr($tglpengakuan, 0, 7);
                    if ($prdpengakuan >= $periodeAktif) {
                        $prdposting = $prdpengakuan;
                    } else {
                        $prdposting = $periodeAktif;
                    }
                    $tglJurnal = $prdposting . "-" . substr($_POST['tglpost'], 0, 2);
                    if (intval(substr($_POST['tglpost'], 0, 2)) > 29) {
                        $tglJurnal = tglakhir($prdposting . "-29");
                    }
                    // $tanggal = date('Ymd');
                    $tanggal = $prdposting . substr($tglpengakuan, 7, 3);
                    $tanggal = str_replace('-', '', $tanggal);
                    # Prep No Jurnal
                    $nojurnal = $tanggal . "/" . $unit . "/PRJ" .
                        $tipe . "/" . $konter;

                    if ($param['alokasi'] == 'asset') {
                        $keterangan = "Jurnal aset dari WIP/CIP ke Fix Asset Project Kode:" . $kode . "; Nama:" . $resH[0]['nama'];
                    } elseif ($param['alokasi'] == 'biaya') {
                        $keterangan = "Jurnal Project ke Biaya, Project Kode:" . $kode . "; Nama:" . $resH[0]['nama'];
                        $resParam[0]['noakundebet'] = $param['noakun'];
                    } else {
                        throw new PDOException("Jenis alokasi salah.");
                    }

                    # Prep Header
                    $dataRes['header'] = array(
                        'nojurnal'     => $nojurnal,
                        'kodejurnal'   => 'PRJ' . $tipe,
                        'tanggal'      => $tglJurnal,
                        'tanggalentry' => date('Ymd'),
                        'posting'      => '0',
                        'totaldebet'   => '0',
                        'totalkredit'  => '0',
                        'amountkoreksi' => '0',
                        'noreferensi'  => $kode,
                        'autojurnal'   => '1',
                        'matauang'     => 'IDR',
                        'kurs'         => '1',
                        'revisi'       => '0'
                    );

                    $dataRes['detail'] = array();
                    $dataRes['detail'][0] = array(
                        'nojurnal'    => $nojurnal,
                        'tanggal'     => $tglJurnal,
                        'nourut'      => 1,
                        'noakun'      => $resParam[0]['noakundebet'],
                        'keterangan'  => $keterangan,
                        'jumlah'      => $nilai,
                        'matauang'    => 'IDR',
                        'kurs'        => '1',
                        'kodeorg'     => $unit,
                        'kodekegiatan' => '',
                        'kodeasset'   => $kode,
                        'kodebarang'  => '',
                        'nik'         => $param['karyawanid'],
                        'kodecustomer' => '',
                        'kodesupplier' => '',
                        'noreferensi' => $kode,
                        'noaruskas'   => '',
                        'kodevhc'     => '',
                        'nodok'       => $kode,
                        'kodeblok'    => '',
                        'revisi'      => '0',
                        'kodesegment' => $defSegment
                    );
                    $dataRes['detail'][1] = array(
                        'nojurnal'    => $nojurnal,
                        'tanggal'     => $tglJurnal,
                        'nourut'      => 2,
                        'noakun'      => $resParam[0]['noakunkredit'],
                        'keterangan'  => $keterangan,
                        'jumlah'      => (-1) * $nilai,
                        'matauang'    => 'IDR',
                        'kurs'        => '1',
                        'kodeorg'     => $unit,
                        'kodekegiatan' => '',
                        'kodeasset'   => $kode,
                        'kodebarang'  => '',
                        'nik'         => $param['karyawanid'],
                        'kodecustomer' => '',
                        'kodesupplier' => '',
                        'noreferensi' => $kode,
                        'noaruskas'   => '',
                        'kodevhc'     => '',
                        'nodok'       => $kode,
                        'kodeblok'    => '',
                        'revisi'      => '0',
                        'kodesegment' => $defSegment
                    );
                    #munculkan akumulasi penyusutan jika pengakuannya telat
                    if ($periodeAktif > $prdAwalPenyusutan and $param['alokasi'] == 'asset') {
                        #ambil kode penyusutan
                        if ($tipeOrganissi != 'HOLDING') {
                            $sAkun = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='DEP" . $tipe . "'";
                            $rAkun = fetchData($sAkun);
                        } else {
                            $sAkun = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='DPH" . $tipe . "'";
                            $rAkun = fetchData($sAkun);
                        }
                        $noaAkunDebet = $rAkun[0]['noakundebet'];
                        $noaAkunKredit = $rAkun[0]['noakunkredit'];
                        #jika biaya langsung dan pabrik ambil noaku dari parameter aplikasi
                        if ($tipeOrganissi == 'PABRIK') {
                            if ($_POST['jnsbiaya'] == '1') {
                                $sNoakun = "select * from " . $dbname . ".setup_parameterappl where kodeparameter='" . $tipe . "1'";
                                $rNoakun = fetchData($sNoakun);
                                $noaAkunDebet = $rNoakun[0]['nilai'];
                            }
                        }
                        $selisihBulan = datediff($prdAwalPenyusutan . "-01", $periodeAktif . "-01");
                        $dataRes['detail'][3] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tglJurnal,
                            'nourut' => 3,
                            'noakun' => $noaAkunDebet,
                            'keterangan' => "Akumulasi Penyusutan DEP" . $tipe . " dari bulan  " . $prdAwalPenyusutan . ";Unit :" . $unit,
                            'jumlah' => (($nilai / $resSubTipe[0]['umurpenyusutan']) * $selisihBulan['months']),
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => $kode,
                            'kodebarang' => '',
                            'nik' => $param['karyawanid'],
                            'kodecustomer' => '',
                            'kodesupplier' => '',
                            'noreferensi' => $kode,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $kode,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $defSegment
                        );
                        $dataRes['detail'][4] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tglJurnal,
                            'nourut' => 4,
                            'noakun' => $noaAkunKredit,
                            'keterangan' => "Akumulasi Penyusutan DEP" . $tipe . " dari bulan  " . $prdAwalPenyusutan . ";Unit :" . $unit,
                            'jumlah' => (-1) * (($nilai / $resSubTipe[0]['umurpenyusutan']) * $selisihBulan['months']),
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => $kode,
                            'kodebarang' => '',
                            'nik' => $param['karyawanid'],
                            'kodecustomer' => '',
                            'kodesupplier' => '',
                            'noreferensi' => $kode,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $kode,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $defSegment
                        );
                    }

                    $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                    $owlPDO->exec($queryH);
                    foreach ($dataRes['detail'] as $key => $dataDet) {
                        $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                        $owlPDO->exec($queryD);
                    }
                    #update konter nomor jurnal
                    $nokounterbaru = $tmpKonter[0]['nokounter'] + 1;
                    $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $nokounterbaru . "' "
                        . " where kodeorg='" . $kodeorg . "' and kodeunit='" . $param['unit'] . "' and periode='" . $prd . "' and kodekelompok='PRJ" . $tipe . "' ";
                    $owlPDO->exec($str);
                } #tutup for
            } else {
                throw new PDOException("Parameter Jurnal 'DEP" . $tipe . "' belum ada\n" .
                    "Silahkan hubungi pihak IT");
            }

            $sPost = "update " . $dbname . ".project set updateby='" . $_SESSION['standard']['userid'] . "',posting='1',tanggalposting='" . tanggalsystemn($_POST['tglpost']) . "' where kode='" . $_POST['kode'] . "'";
            $owlPDO->exec($sPost);


            $nokounterbaru = $tmpKonter[0]['nokounter'] + 1;
            $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $nokounterbaru . "' "
                . " where kodeorg='" . $kodeorg . "' and kodeunit='" . $param['unit'] . "' and periode='" . $prd . "' and kodekelompok='PRJ" . $tipe . "' ";
            $owlPDO->exec($str);

            #execute
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
            die();
        }
        break;

    case 'saveApproval':

        $str = "update " . $dbname . ".project set statuspersetujuan='1' where kode='" . $kodeproject . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    // case 'saveApproval' : 

    //     $sdel="delete from ".$dbname.".approval where notransaksi='".$kodeproject."'";
    //     try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 

    //     for ($i=1; $i <5 ; $i++) { 

    //         $aprv=checkPostGet('aprv'.$i,'');
    //         if (@$aprv.$i!='')
    //         {
    //             $stra="INSERT INTO ".$dbname.".`approval` (`notransaksi`,`jenispersetujuan`,`level`, `karyawanid`)
    //             values('".$kodeproject."','PROJ','".$i."','".$aprv."')";
    //             try{$owlPDO->exec($stra); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    //         }
    //     }

    //     $str="update ".$dbname.".project set statuspersetujuan='9' where kode='".$kodeproject."'"; 
    //     try{$owlPDO->exec($str); }
    //     catch (PDOException $e) {
    //         print " Gagal  !: " . $e->getMessage() . "\n";
    //         die();
    //     } 
    // break;

    case 'editApproval':

        for ($i = 1; $i <= 4; $i++) {

            $aprv = checkPostGet('aprv' . $i, '');

            $str = "update " . $dbname . ".project_approval set karyawanid='" . $aprv . "' where kode='" . $kodeproject . "' and level='" . $i . "'";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }


        break;

    case 'timeFrame':
        $iHead = "select * from " . $dbname . ".project where kode='" . $kode . "'";
        $res = $owlPDO->query($iHead) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $dHead = $res->fetch();

        $tgl1 =
            $stream = "PROJECT TIMEFRAME<table border=0>
                    <tr>
                            <td colspan=2>" . $_SESSION['lang']['unit'] . "</td>
                            <td><u>" . $optNmOrg[$dHead['kodeorg']] . "</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['project'] . "</td>
                            <td><u>" . $dHead['nama'] . "</u></td>
                    </tr>
                    <tr>
                            <td colspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['mulai'] . "</td>
                            <td><u>" . tanggalnormal($dHead['tanggalmulai']) . "</u></td>
                            <td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['selesai'] . "</td>
                            <td><u>" . tanggalnormal($dHead['tanggalselesai']) . "</u></td>
                    </tr>
                </table>"; //NO  Kodebarang  Namabarang  Satuan  JLH RAB DIPAKAI SELISIH
        $arrTgl = rangeTanggal($dHead['tanggalmulai'], $dHead['tanggalselesai']);
        //  print_r($arrTgl);
        $stream .= "<br /><table class=sortable border=1 cellspacing=1>
                         <thead>
                            <tr>
                                <td align=center bgcolor=#CCCCCC>Tahapan</td>";
        if (!empty($arrTgl)) foreach ($arrTgl as $lstTgl => $tgl) {
            $stream .= "<td align=center bgcolor=#CCCCCC>" . tanggalnormal($tgl) . "</td>";
        }
        $stream .= "</tr>";
        $iTahap = "select * from " . $dbname . ".project_dt where kodeproject='" . $kode . "' ";
        $res = $owlPDO->query($iTahap) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dTahap = $res->fetch()) {
            //$i+=1;
            //$listkodeect[$dTahap['kodeproject']]=$dTahap['kodeproject'];
            $tahapan[$dTahap['namakegiatan']] = $dTahap['namakegiatan'];
            $tglMulai[$dTahap['namakegiatan']] = $dTahap['tanggalmulai'];
            $tglSelesai[$dTahap['namakegiatan']] = $dTahap['tanggalselesai'];
        }
        //echo $i;
        //$tglMulai[$dTahap['namakegiatan'].$dTahap['tanggalmulai']]
        //$arrTgl=rangeTanggal($dHead['tanggalmulai'],$dHead['tanggalselesai']);

        if (!empty($tahapan)) foreach ($tahapan as $listTahapan) {
            $arrTglData = rangeTanggal($tglMulai[$listTahapan], $tglSelesai[$listTahapan]);
            $listTersimpan = false;
            $dert = false;
            $stream .= "<tr>
                        <td>" . $tahapan[$listTahapan] . "</td>";
            $isi = "";
            if (!empty($arrTgl)) foreach ($arrTgl as $listTgl) {
                if ($dert == false) {
                    if ($tglSelesai[$listTahapan] == $listTgl) {
                        $isi = "bgcolor=blue"; //$isi="bgcolor=red";
                        $listTersimpan = false;
                        //$tglSelesai[$listTahapan]="";
                        $dert = true;
                    } else {
                        if ($listTersimpan == false) {
                            if ($tglMulai[$listTahapan] == $listTgl) {
                                $isi = "bgcolor=blue";
                                $listTersimpan = true;
                            }
                        }
                    }
                } else {
                    $isi = "";
                    $dert = false;
                }
                //$isi="";//exit("Error:HAHA");
                $stream .= "<td " . $isi . "></td>";  //".$tglSelesai[$listTahapan]."
            }
        }
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
        $tglSkrg = date("Ymd");
        $nop_ = "Laporan_Progres_Project" . $dHead['kode'];
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
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }

        break;

    case 'hpsDetailAK':
        $sdel = "delete from " . $dbname . ".project_dt where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'excelMaterial':

        $iHead = "select * from " . $dbname . ".project where kode='" . $kode . "'";
        $res = $owlPDO->query($iHead) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $dHead = $res->fetch();
        $stream = "MATERIAL USAGE<table border=0>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['unitkerja'] . "</td>
                                            <td><u>" . $optNmOrg[$dHead['kodeorg']] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td ></td >
                                            <td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['project'] . "</td>
                                            <td><u>" . $dHead['nama'] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['namakelompok'] . " " . $_SESSION['lang']['project'] . "</td>
                                            <td><u>" . $dHead['tipe'] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['mulai'] . "</td>
                                            <td><u>" . tanggalnormal($dHead['tanggalmulai']) . "</u></td>
                                            <td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['selesai'] . "</td>
                                            <td><u>" . tanggalnormal($dHead['tanggalselesai']) . "</u></td>
                                    </tr>
                            </table>"; //NO  Kodebarang  Namabarang  Satuan  JLH RAB DIPAKAI SELISIH

        $stream .= "<br /><table class=sortable border=1 cellspacing=1>
                                     <thead>
                                            <tr>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nourut'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['namabarang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['satuan'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['penggunaan'] . " " . $_SESSION['lang']['project'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['jumlahkeluargudang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['selisih'] . "</td>
                                            </tr>";

        $iPro = "select * from " . $dbname . ".project_material where kodeproject='" . $kode . "' ";
        $res = $owlPDO->query($iPro) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dPro = $res->fetch()) {
            $listKdBrg[$dPro['kodebarang']] = $dPro['kodebarang'];
            @$listJumlahRab[$dPro['kodebarang']] += $dPro['jumlah'];
        }
        $iGud = "select * from " . $dbname . ".log_transaksi_vw where kodeblok='" . $kode . "' and post='1' ";
        $res = $owlPDO->query($iGud) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dGud = $res->fetch()) {
            $listKdBrg[$dGud['kodebarang']] = $dGud['kodebarang'];
            @$listJumlahPakai[$dGud['kodebarang']] += $dGud['jumlah'];
        }
        if (!empty($listKdBrg)) foreach ($listKdBrg as $kdBarang) {
            $no += 1;
            setIt($listJumlahRab[$kdBarang], 0);
            setIt($listJumlahPakai[$kdBarang], 0);
            $selisih[$kdBarang] = $listJumlahRab[$kdBarang] - $listJumlahPakai[$kdBarang];
            $stream .= "<tr>
                                            <td>" . $no . "</td>
                                            <td>" . $kdBarang . "</td>
                                            <td>" . $nmBrg[$kdBarang] . "</td>
                                            <td>" . $satBrg[$kdBarang] . "</td>
                                            <td>" . $listJumlahRab[$kdBarang] . "</td>
                                            <td>" . $listJumlahPakai[$kdBarang] . "</td>
                                            <td>" . $selisih[$kdBarang] . "</td>
                                    </tr>";
        }
        $nop_ = "Laporan_Material_" . $dHead['kode'];
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
                            parent.window.alert('Can't convert to excel format');
                            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            fclose($handle);
        }
        break;

    case 'hpsDetail':
        $sdel = "delete from " . $dbname . ".project_dt where kegiatan='" . $_POST['index'] . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'excelMaterial':

        $iHead = "select * from " . $dbname . ".project where kode='" . $kode . "'";
        $res = $owlPDO->query($iHead) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $dHead = $res->fetch();
        $stream = "MATERIAL USAGE<table border=0>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['unitkerja'] . "</td>
                                            <td><u>" . $optNmOrg[$dHead['kodeorg']] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td ></td >
                                            <td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['project'] . "</td>
                                            <td><u>" . $dHead['nama'] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['namakelompok'] . " " . $_SESSION['lang']['project'] . "</td>
                                            <td><u>" . $dHead['tipe'] . "</u></td>
                                    </tr>
                                    <tr>
                                            <td></td>
                                            <td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['mulai'] . "</td>
                                            <td><u>" . tanggalnormal($dHead['tanggalmulai']) . "</u></td>
                                            <td>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['selesai'] . "</td>
                                            <td><u>" . tanggalnormal($dHead['tanggalselesai']) . "</u></td>
                                    </tr>
                            </table>"; //NO  Kodebarang  Namabarang  Satuan  JLH RAB DIPAKAI SELISIH

        $stream .= "<br /><table class=sortable border=1 cellspacing=1>
                                     <thead>
                                            <tr>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nourut'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['namabarang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['satuan'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['penggunaan'] . " " . $_SESSION['lang']['project'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['jumlahkeluargudang'] . "</td>
                                                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['selisih'] . "</td>
                                            </tr>";

        $iPro = "select * from " . $dbname . ".project_material where kodeproject='" . $kode . "' ";
        $res = $owlPDO->query($iPro) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dPro = $res->fetch()) {
            $listKdBrg[$dPro['kodebarang']] = $dPro['kodebarang'];
            @$listJumlahRab[$dPro['kodebarang']] += $dPro['jumlah'];
        }
        $iGud = "select * from " . $dbname . ".log_transaksi_vw where kodeblok='" . $kode . "' and post='1' ";
        $res = $owlPDO->query($iGud) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dGud = $res->fetch()) {
            $listKdBrg[$dGud['kodebarang']] = $dGud['kodebarang'];
            @$listJumlahPakai[$dGud['kodebarang']] += $dGud['jumlah'];
        }
        if (!empty($listKdBrg)) foreach ($listKdBrg as $kdBarang) {
            $no += 1;
            setIt($listJumlahRab[$kdBarang], 0);
            setIt($listJumlahPakai[$kdBarang], 0);
            $selisih[$kdBarang] = $listJumlahRab[$kdBarang] - $listJumlahPakai[$kdBarang];
            $stream .= "<tr>
                                            <td>" . $no . "</td>
                                            <td>" . $kdBarang . "</td>
                                            <td>" . $nmBrg[$kdBarang] . "</td>
                                            <td>" . $satBrg[$kdBarang] . "</td>
                                            <td>" . $listJumlahRab[$kdBarang] . "</td>
                                            <td>" . $listJumlahPakai[$kdBarang] . "</td>
                                            <td>" . $selisih[$kdBarang] . "</td>
                                    </tr>";
        }
        $nop_ = "Laporan_Material_" . $dHead['kode'];
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
                            parent.window.alert('Can't convert to excel format');
                            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            fclose($handle);
        }
        break;

    case 'saveFormBarang':
        $i = "INSERT INTO " . $dbname . ".`project_material` (`kodeproject`, `kodekegiatan`, `kodebarang`, `jumlah`, `updateby`)
                    values('" . $kodeproject . "','" . $kodekegiatan . "','" . $kodeBarangForm . "','" . $jumlahBarangForm . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;


    case 'deleteMaterial':
        //exit("Error:hahaha");
        $i = "DELETE FROM " . $dbname . ".`project_material` WHERE `kodeproject` = '" . $kodeproject . "' AND `kodekegiatan` = '" . $kegiatan . "' AND `kodebarang`= '" . $kodebarang . "'";
        try {
            $owlPDO->exec($i);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'getListBarang':
        $str = "select kodecapex from " . $dbname . ".project where kode='" . $kodeproject . "'";
        $res = fetchData($str);
        $kodecapex = $res[0]['kodecapex'];
        if ($kodecapex == '') {
            $showhide = '';
        } else {
            $showhide = 'none';
        }
        echo "
                    <fieldset style='display:" . $showhide . "'>
                    <legend>" . $_SESSION['lang']['form'] . "</legend>
                            <fieldset style='float:left;display:" . $showhide . "' >
                                    <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "</legend>
                                            <table cellspacing=1 border=0 class=data>

                                                    <tr>
                                                            <td colspan=2>" . $_SESSION['lang']['namabarang'] . "</td>

                                                            <td colspan=5>:
                                                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                                                    <button class=mybutton onclick=cariListBarang('" . $kegiatan . "','" . $kodeproject . "')>" . $_SESSION['lang']['find'] . "</button>
                                                            <td>
                                                    <tr>
                                                    </table>

                                                    <table id=listCariBarang >
                                                    <thead>
                                                    <tr class=rowheader>
                                                            <td>No</td>
                                                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                                                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                                                    </tr></thead>";

        if ($namaBarangCari == '') {
        } else {
            $i = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where namabarang like '%" . $namaBarangCari . "%'";
            $resw = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
            $resw->setFetchMode(PDO::FETCH_ASSOC);
            while ($d = $resw->fetch()) {
                $no += 1;
                echo "
                                                    <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('" . $d['kodebarang'] . "','" . $nmBrg[$d['kodebarang']] . "','" . $satBrg[$d['kodebarang']] . "');\">
                                                            <td>" . $no . "</td>
                                                            <td>" . $d['kodebarang'] . "</td>
                                                            <td>" . $nmBrg[$d['kodebarang']] . "</td>
                                                            <td>" . $satBrg[$d['kodebarang']] . "</td>
                                                    </tr>";
            }
        }
        echo "</table>
                                    </fieldset>


                                    <fieldset style='display:" . $showhide . "'>
                                    <legend>" . $_SESSION['lang']['form'] . "</legend>
                                            <table cellspacing=1 border=0>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['project'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=kodeproject disabled value='" . $kodeproject . "' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['kodekegiatan'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=kodekegiatan disabled value='" . $kegiatan . "' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                                                            <td>:</td>
                                                            <td>
                                                                    <input type=text id=kodeBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                                            </td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=namaBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=satuanBarangForm disabled class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>
                                                    <tr>
                                                            <td>" . $_SESSION['lang']['jumlah'] . "</td>
                                                            <td>:</td>
                                                            <td><input type=text id=jumlahBarangForm class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'></td>
                                                    </tr>

                                                    <tr>
                                                            <td>
                                                                    <button class=mybutton onclick=saveFormBarang('" . $kegiatan . "','" . $kodeproject . "','" . $_SESSION['lang']['find'] . "',event)>" . $_SESSION['lang']['save'] . "</button>
                                                                    <button class=mybutton onclick=cancelFormBarang('" . $kegiatan . "','" . $kodeproject . "','" . $_SESSION['lang']['find'] . "',event)>" . $_SESSION['lang']['delete'] . "</button>
                                                                    <button class=mybutton onclick=closeDialog()>" . $_SESSION['lang']['selesai'] . "</button>
                                                            </td>
                                                    </tr>
                                            </table>
                                    </fieldset>
                            </fieldset>

            <fieldset>
            <legend>" . $_SESSION['lang']['datatersimpan'] . "</legend>
            <table cellspacing=1 border=0 class=data>
            <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>" . $_SESSION['lang']['project'] . "</td>
                            <td>" . $_SESSION['lang']['namakegiatan'] . "</td>
                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                            <td>" . $_SESSION['lang']['jumlah'] . "</td>
                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                            <td>" . $_SESSION['lang']['dibuat'] . "</td>
                            <td>" . $_SESSION['lang']['action'] . "</td>
                    </tr>
            </thead>
            </tbody>";

        $i = "select * from " . $dbname . ".project_material where kodekegiatan='" . $kegiatan . "'";
        $res1 = $owlPDO->query($i) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $noData = 0;
        while ($d = $res1->fetch()) {
            $noData += 1;
            echo "
                    <tr class=rowcontent>
                            <td>" . $noData . "</td>
                            <td>" . $d['kodeproject'] . "</td>
                            <td>" . $optNmKegBrg[$d['kodekegiatan']] . "</td>
                            <td>" . $d['kodebarang'] . "</td>
                            <td>" . $nmBrg[$d['kodebarang']] . "</td>
                            <td align=right>" . $d['jumlah'] . "</td>
                            <td>" . $satBrg[$d['kodebarang']] . "</td>
                            <td>" . $nmKar[$d['updateby']] . "</td>

                            <td>
                                    <img src=images/application/application_delete.png class=zImgBtn style='display:" . $showhide . "' caption='Delete'
                                    onclick=\"delMaterial('" . $d['kodeproject'] . "','" . $d['kodekegiatan'] . "','" . $d['kodebarang'] . "');\">
                            </td>
                    </tr>";
        }
        echo "</table></fieldset>";

        break;
    case 'showformApproval':

        // $str="SELECT a.*,b.* FROM ".$dbname.".datakaryawan a LEFT JOIN ".$dbname.".sdm_5jabatan b ON a.kodejabatan=b.kodejabatan where a.lokasitugas in(select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional=subregional ) 
        // and b.namajabatan like '%manager%' and tanggalkeluar='0000-00-00' ";
        $str = "SELECT karyawanid,namakaryawan from " . $dbname . ".datakaryawan where tipekaryawan='0' and (tanggalkeluar!= '0000-00-00' or tanggalkeluar < " . date('Y-m-d') . ") and statuskaryawan!='kontrak' and statuskaryawan!='keluar' and namakaryawan not like 'ADMINISTRATOR%' ";
        $optPersetujuan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($rstr = $res->fetch()) {
            $optPersetujuan .= "<option value=" . $rstr['karyawanid'] . ">" . $rstr['namakaryawan'] . "</option>";
        }

        $str = "select * from " . $dbname . ".project where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchData($str);
        $stspersetujuan = $res[0]['statuspersetujuan'];

        // if ($res[0]['statuspersetujuan']=='0') {
        // $tab="<table>";
        // $tab.="<tr>
        //         <td>".$_SESSION['lang']['kode']."</td>
        //         <td>:</td>
        //         <td><input type=text id=kode onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='".$_POST['kodeproject']."' disabled=disabled /></td>
        //     </tr>";

        // for ($i=1; $i <=4 ; $i++) {  
        //     $tab.="<tr>
        //     <td>".$_SESSION['lang']['persetujuan']." ".$i."</td>
        //     <td>:</td>
        //     <td><select id=aprv1 style=\"width:155px;\">" . $optPersetujuan . "</select></td>
        //     </tr>";
        // }
        // // <td><img id='aprv".$i." onclick=z.elSearch('aprv1',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td> 


        // $tab.="<tr>
        //         <td colspan=2>&nbsp;</td>
        //         <td><button class=mybutton onclick=editApproval('".$kodeproject."','aprv1')>".$_SESSION['lang']['edit']."</button></td>
        //     </tr>
        //     <tr>
        //         <td colspan=3><hr></td>
        //     </tr>

        // </table>";
        // }else{
        $tab .= "<h4>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['persetujuan'] . "</h4>";
        $tab .= "<table class=sortable cellspacing=1 border=0>";
        $tab .= "<thead><tr class=rowheader>";
        $tab .= "<th>" . $_SESSION['lang']['nourut'] . "</th>";
        $tab .= "<th>" . $_SESSION['lang']['namakaryawan'] . "</th>";
        // $tab.="<th>".$_SESSION['lang']['status']."</th>";
        // $tab.="<th>Komentar</th>";
        $tab .= "</tr></thead><tbody>";
        $arrkomen = array('0' => 'Menunggu Persetujuan', '1' => $_SESSION['lang']['disetujui'], '2' => $_SESSION['lang']['ditolak']);
        $str = "select * from " . $dbname . ".project_approval where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchData($str);
        foreach ($res as $bar) {
            @$no++;
            $nmorang = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['karyawanid'] . "'");
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=left>" . @$nmorang[$bar['karyawanid']] . "</td>";
            // $tab.="<td align=center>".$arrkomen[$bar['status']]."</td>";
            // $tab.="<td align=center>".$bar['komentar']."</td>";
            $tab .= "</tr>";
        }
        if ($stspersetujuan != 1) {
            $tab .= "<tr>
              
                <td><button class=mybutton onclick=saveApproval('" . $kodeproject . "')>" . $_SESSION['lang']['save'] . " Approval</button></td>
                </tr>";
        }
        $tab .= "</table>";
        // }

        echo $tab;
        break;

    case 'showform':
        $res = array('asset' => 'Naikkan Menjadi Asset', 'biaya' => 'Alokasi Sebagai Biaya');
        $optjenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        foreach ($res as $key => $val) {
            $optjenis .= "<option value=" . $key . ">" . $val . "</option>";
        }
        $opttipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $param['kodeorg'] . "'");

        $wh = "";
        if ($opttipe[$param['kodeorg']] == 'KEBUN' or $opttipe[$param['kodeorg']] == 'PABRIK') {
            $wh = " and (substr(noakun,1,1) in ('7')";
        } elseif ($opttipe[$param['kodeorg']] == 'BULKING') {
            $wh = " and (substr(noakun,1,2) in ('81')";
        } else {
            $wh = " and (substr(noakun,1,2) in ('82')";
        }


        $wh .= " or substr(noakun,1,5) in ('12911') or substr(noakun,1,5) in ('12902') or substr(noakun,1,5) in ('12101') or noakun in ('1160103')) and detail=1 and length(noakun)='7'";

        #PABRIK => %63%, 7%
        #KANWIL => 82%
        #RND => 82%
        #TC => 82%
        #BULKING => 81%

        // $str="select * from ".$dbname.".project where kode='".$_POST['kodeproject']."'";
        // $res=fetchData($str);
        // if ($res[0]['dgnapproval']=='1') { 
        //     if ($res[0]['statuspersetujuan']!='1') {  
        //         exit("<b>Maaf, Kode Project ini masih belum diajukan atau dalam tahap pengajuan </b>"); 
        //     }
        // }
        $sjnskrj = "select * from " . $dbname . ".keu_5akun where length(noakun) in ('7','8') and namaakun not like '%NON AKTIF%' " . $wh . " order by noakun asc";
        $optJnsKerja = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $res = $owlPDO->query($sjnskrj) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($rjnskrj = $res->fetch()) {
            $d = substr($rjnskrj['noakun'], 0, 5);
            if ($d != $n) {
                $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun='" . $d . "'");
                $optJnsKerja .= "<optgroup label='" . $nmorg[$d] . "'>";
            }
            $optJnsKerja .= "<option value=" . $rjnskrj['noakun'] . ">" . $rjnskrj['noakun'] . " - " . $rjnskrj['namaakun'] . "</option>";
            $n = $d;
            if ($d != $n) {
                $optJnsKerja .= "</optgroup>";
            }
        }


        $whereKary = "";
        $whereKary = " and tipekaryawan in ('0','1','4') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date('Y-m-d') . "')";
        $str = "select * from " . $dbname . ".datakaryawan where lokasitugas='" . $param['kodeorg'] . "' " . $whereKary . " order by namakaryawan asc";
        $res = fetchData($str);
        $optkary = "<option value=''>&nbsp;</option>";
        foreach ($res as $val) {
            $optkary .= "<option value=" . $val['karyawanid'] . ">" . $val['nik'] . " - " . $val['namakaryawan'] . "</option>";
        }


        #= ambil nama default
        $str = "select * from " . $dbname . ".project where kode='" . $_POST['kodeproject'] . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $namaaset = $bar['nama'];
            $norangka = $bar['norangka'];
            $nomesin = $bar['nomesin'];
            $posisiasset = $bar['posisiasset'];
            $tipelokasi = $bar['tipelokasi'];
            $tipemodel = $bar['tipemodel'];
        }


        $tab = "<table>";
        $tab .= "<tr>
                <td>" . $_SESSION['lang']['kode'] . "</td>
                <td>:</td>
                <td><input type=text id=kode onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='" . $_POST['kodeproject'] . "' disabled=disabled /></td>
            </tr>
			<tr>
                <td>" . $_SESSION['lang']['nama'] . "</td>
                <td>:</td>
                <td><input type=text id=namaasetposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:500px;\" value='" . $namaaset . "' /></td>
            </tr>
			<tr>
                <td>" . $_SESSION['lang']['norangka'] . "</td>
                <td>:</td>
                <td><input type=text id=norangkaposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:500px;\" value='" . $norangka . "' /></td>
            </tr>
			<tr>
                <td>" . $_SESSION['lang']['nomesin'] . "</td>
                <td>:</td>
                <td><input type=text id=nomesinposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:500px;\" value='" . $nomesin . "' /></td>
            </tr>
			<tr>
                <td>" . $_SESSION['lang']['tipemodel'] . "</td>
                <td>:</td>
                <td><input type=text id=tipemodelposting onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:500px;\" value='" . $tipemodel . "' /></td>
            </tr>
			
            <tr>
                <td>" . $_SESSION['lang']['tanggal'] . "</td>
                <td>:</td>
                <td><input type=text class=myinputtext readonly  id=tglpost onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:500px;\" value='" . $tglPosting . "'/></td>
            </tr>
			<tr>
                <td>" . $_SESSION['lang']['alokasi'] . "</td>
                <td>:</td>
                <td><select id=alokasi onchange=showhide(this.value); style=\"width:505px;\">" . $optjenis . "</select></td>
            </tr>
			<tr id=rowakun style=display:none>
                <td>" . $_SESSION['lang']['noakun'] . "</td>
                <td>:</td>
                <td><select id=noakun class=select2 style=\"width:505px;\">" . $optJnsKerja . "</select>
					</td>
            </tr>
			<tr id=rowkary style=display:none>
                <td>" . $_SESSION['lang']['namakaryawan'] . "</td>
                 <td>:</td>
				<td><select id=karyawanid class=select2 style=\"width:505px;\">" . $optkary . "</select>
					</td>
            </tr>
            <tr>
				<td colspan=2>&nbsp;</td>
				<td><button class=mybutton onclick=savePosting('" . $_POST['kodeproject'] . "','" . $_POST['kodeorg'] . "','" . $_POST['kodeorg'] . "')>" . $_SESSION['lang']['save'] . "</button></td>
			</tr>
			<tr>
				<td colspan=3><hr></td>
			</tr>
            <tr>
                <td colspan=3>Pemilihan naik menjadi asset, Tanggal 1 s/d 30 maka periode penyusutan masuk ke bulan yg sama.</td>
            </tr>
            <!--
                <tr>
                    <td colspan=3>Pemilihan naik menjadi asset, Jika tanggal > 15 maka periode penyusutan masuk ke bulan yg sama.</td>
                </tr>
                <tr>
                    <td colspan=3>Pemilihan naik menjadi asset, Jika tanggal >= 15 maka periode penyusutan masuk ke bulan depan.</td>
                </tr>
            -->
        </table>";

        echo $tab;
        break;

    case 'changetipelokasi':
        $orgarr = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='" . $_POST['posisiasset'] . "'");
        $optSub = "<option value=''>" . $_SESSION['empl']['pilihdata'] . "</option>";
        $tipelokasi = $orgarr[$_POST['posisiasset']];

        $optSub = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $iSub = "select * from " . $dbname . ".keu_5tipelokasiasset where tipelokasi='" . $tipelokasi . "' ";
        $res = $owlPDO->query($iSub) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dSub = $res->fetch()) {
            if ($_POST['lokasi'] == $dSub['kodelokasi']) {
                $select = "selected=selected";
            } else {
                $select = "";
            }
            $optSub .= "<option " . $select . " value='" . $dSub['kodelokasi'] . "'>" . $dSub['namalokasi'] . "</option>";
        }
        echo $optSub;
        break;

    default:
        break;
}

<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('lib/fpdf.php');


$method = checkPostGet('method', '');
$idlahan = checkPostGet('idlahan', '');
$mid = checkPostGet('mid', '');
$unit = checkPostGet('unit', '');
$pemilik = checkPostGet('pemilik', '');
$lokasi = checkPostGet('lokasi', '');
$luasinti = checkPostGet('luasinti', '');
$luasplasma = checkPostGet('luasplasma', '');
$luaslahan = checkPostGet('luaslahan', '');
$shm = checkPostGet('shm', '');
$jmlsppt = checkPostGet('jmlsppt', '');
$alamat = checkPostGet('alamat', '');
$page = checkPostGet('page', '');
$idcari = checkPostGet('idcari', '');
$pemilikcari = checkPostGet('pemilikcari', '');
$persilcari = checkPostGet('persilcari', '');
$spptcari = checkPostGet('spptcari', '');	
$noid = checkPostGet('noid', '');	

$bisaditanam = checkPostGet('bisaditanam', '');
$blok = checkPostGet('blok', '');
$batastimur = checkPostGet('batastimur', '');
$batasbarat = checkPostGet('batasbarat', '');
$batasutara = checkPostGet('batasutara', '');
$statuskawasan = checkPostGet('statuskawasan', '');
$batasselatan = checkPostGet('batasselatan', '0');

$koordinatulx = checkPostGet('koordinatulx', '0');
$koordinatuly = checkPostGet('koordinatuly', '0');
$koordinatlrx = checkPostGet('koordinatlrx', '0');
$koordinatlry = checkPostGet('koordinatlry', '0');
$ukurbatasbpn = checkPostGet('ukurbatasbpn', '0');


$tglpembebasan = checkPostGet('tglpembebasan', '');
$penyelesaian = checkPostGet('penyelesaian', '');
$tglsengketa = checkPostGet('tglsengketa', '');
$deskripsi = checkPostGet('deskripsi', '');
$kategori = checkPostGet('kategori', '');
$catatan = checkPostGet('catatan', '');



$pt = checkPostGet('pt', '');
$jenisupload = checkPostGet('jenisupload', '');
$xxx = checkPostGet('xxx', '');
$yyy = checkPostGet('yyy', '');
$iii = checkPostGet('iii', '');
$namafile = checkPostGet('namafile', '');
if ($iii == 'undefined') {
    $iii = '';
}

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmmasyarakat = makeOption($dbname, 'pad_5masyarakat', 'padid,nama');
$keterangan = checkPostGet('keterangan', '');
$path = "fileupload/lgl_GRLTT/";
//exit("error :".$jmlsppt);

$statuspermintaandana=0;
$statuspermbayaran=0;
$statuskades=0;
$statuscamat=0;
$nosurat=0;

$kriteria = checkPostGet('kriteria', '');
$kriteriax = checkPostGet('kriteriax', '');
$id = checkPostGet('id', '');
$emodul = 'DGF';

switch ($method) {
    case 'pdf':

        $str1 = "select padid, nama from " . $dbname . ".pad_5masyarakat";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());

        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $kamuspemilik[$bar1->padid] = $bar1->nama;
        }

        class PDF extends FPDF {

            function Header() {

                global $idlahan;
                global $pemilik;
                global $kamuspemilik;
                global $owlPDO;
                global $namapt;

                $this->SetFont('Arial', 'B', 9);
                $this->Cell(20, 3, $namapt, '', 1, 'L');
                $this->SetFont('Arial', 'B', 12);
                $this->Cell(190, 3, strtoupper($_SESSION['lang']['pembebasan'] . " " . $_SESSION['lang']['lahan']), 0, 1, 'C');
                $this->SetFont('Arial', '', 7);
                $this->Cell(150, 3, ' ', '', 0, 'R');
                $this->Cell(15, 3, $_SESSION['lang']['tanggal'], '', 0, 'L');
                $this->Cell(2, 3, ':', '', 0, 'L');
                $this->Cell(35, 3, date('d-m-Y H:i'), 0, 1, 'L');

                $this->Cell(28, 3, $_SESSION['lang']['id'], '', 0, 'L');
                $this->Cell(2, 3, ':', '', 0, 'L');
                $this->Cell(120, 3, $idlahan, 0, 0, 'L');

                $this->Cell(15, 3, $_SESSION['lang']['page'], '', 0, 'L');
                $this->Cell(2, 3, ':', '', 0, 'L');
                $this->Cell(35, 3, $this->PageNo(), '', 1, 'L');

                $this->Cell(28, 3, $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['namapemilik'] . " " . $_SESSION['lang']['lahan'], '', 0, 'L');
                $this->Cell(2, 3, ':', '', 0, 'L');
                $this->Cell(120, 3, $kamuspemilik[$pemilik], 0, 0, 'L');

                $this->Cell(15, 3, 'User', '', 0, 'L');
                $this->Cell(2, 3, ':', '', 0, 'L');
                $this->Cell(35, 3, $_SESSION['standard']['username'], '', 1, 'L');
                $this->Ln();

                $this->Ln();
            }

        }

        //================================
        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->AddPage();

        $str1 = "select a.*,b.nama,b.alamat,b.desa,c.namakaryawan from " . $dbname . ".pad_lahan a
            left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid 
            left join " . $dbname . ".datakaryawan c on a.updateby=c.karyawanid    
            where idlahan = '" . $idlahan . "'";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $stdana = $bar1->statuspermintaandana == 1 ? tanggalnormal($bar1->tanggalpengajuan) : "";

            if ($bar1->statuspermbayaran == 1) {
                $stbayar = tanggalnormal($bar1->tanggalbayar) . " Belum Lunas";
            } else if ($bar1->statuspermbayaran == 0) {
                $stbayar = 'Belum Bayar';
            } else if ($bar1->statuspermbayaran == 2) {
                $stbayar = tanggalnormal($bar1->tanggalbayar) . " Lunas";
            }
            $stkades = $bar1->statuskades == 1 ? tanggalnormal($bar1->tanggalkades) : "";
            $stcamat = $bar1->statuscamat == 1 ? tanggalnormal($bar1->tanggalcamat) : "";
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(100, 5, "1." . $_SESSION['lang']['namapemilik'] . " " . $_SESSION['lang']['lahan'], 0, 0, 'L');
            $pdf->Cell(100, 5, "2." . $_SESSION['lang']['biaya'] . "-" . $_SESSION['lang']['biaya'] . " dan " . $_SESSION['lang']['status'] . "-" . $_SESSION['lang']['dokumen'], 0, 0, 'L');
            $pdf->Ln();

            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(35, 5, $_SESSION['lang']['id'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->idlahan, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['tanamtumbuh'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, number_format($bar1->rptanaman, 0), 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['kebun'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->unit, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['gantilahan'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, number_format($bar1->rptanah, 0), 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['namapemilik'] . " " . $_SESSION['lang']['lahan'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->nama, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['kepala'] . " " . $_SESSION['lang']['desa'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, number_format($bar1->biayakades, 0), 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['keterangan'] . " " . $_SESSION['lang']['lokasi'] . "(No.Persil)", 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->lokasi, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['camat'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, number_format($bar1->biayacamat, 0), 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['luas'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(23, 5, $bar1->luas . ' Ha.', 0, 0, 'R');
            $pdf->Cell(40, 5, '', 0, 0, 'R');
            $pdf->Cell(35, 5, $_SESSION['lang']['biaya'] . " Matrai", 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, number_format($bar1->biayamatrai, 0), 0, 0, 'R');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['luas'] . " " . $_SESSION['lang']['bisaditanam'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(23, 5, $bar1->luasdapatditanam . ' Ha.', 0, 0, 'R');
            $pdf->Cell(40, 5, '', 0, 0, 'R');
            $pdf->Cell(35, 5, $_SESSION['lang']['status'] . " " . $_SESSION['lang']['permintaandana'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(6333, 5, $stdana, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['lokasi'] . " " . $_SESSION['lang']['kodeblok'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->kodeblok, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['status'] . " " . $_SESSION['lang']['pembayaran'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, $stbayar, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['batastimur'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->batastimur, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['status'] . " " . $_SESSION['lang']['kepala'] . " " . $_SESSION['lang']['desa'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, $stkades, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['batasbarat'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->batasbarat, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['status'] . " " . $_SESSION['lang']['camat'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, $stcamat, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['batasutara'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->batasutara, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['nomor'] . " " . $_SESSION['lang']['dokumen'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, $bar1->nosurat, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Cell(35, 5, $_SESSION['lang']['batasselatan'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(63, 5, $bar1->batasselatan, 0, 0, 'L');
            $pdf->Cell(35, 5, $_SESSION['lang']['keterangan'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(33, 5, $bar1->keterangan, 0, 0, 'L');
            $pdf->Ln();
            $pdf->Ln();
        }
        $str1 = "select * from " . $dbname . ".pad_photo
            where idlahan = '" . $idlahan . "'";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $pdf->Cell(13, 5, $_SESSION['lang']['photo'], 0, 0, 'L');
            $pdf->Cell(2, 5, ":", 0, 0, 'L');
            $pdf->Cell(73, 5, $bar1->filename, 0, 0, 'L');
            $pdf->Ln();
            $yey = $pdf->GetY();
            $path = 'filepad/' . $bar1->filename;
            $pdf->Image($path, 25, $yey, 70);
            $pdf->SetY($yey + 80);
            $pdf->Ln();
        }
        $pdf->Output();

        exit;
        break;
    case 'update': 
		$str = "update " . $dbname . ".pad_lahan 
        set 
		`pemilik`='" . $pemilik . "',
		`unit`='" . $unit . "',
		`lokasi`='" . $lokasi . "',
		`luas`='" . $luaslahan . "',
        `luasinti`='" . $luasinti . "',
        `luasplasma`='" . $luasplasma . "',
		`luasdapatditanam`='" . $bisaditanam . "', 								 
		`statuspermintaandana`='" . $statuspermintaandana . "', 
		`statuspermbayaran`='" . $statuspermbayaran . "',
		`kodeblok`='" . $blok . "',
		`keterangan`='" . $keterangan . "',
		`nosurat`='" . $nosurat . "',
		`batastimur`='" . $batastimur . "',
		`batasbarat`='" . $batasbarat . "', 
		`batasutara`='" . $batasutara . "',
		`batasselatan`='" . $batasselatan . "',
		`shm`='" . $shm . "',
		`jmlsppt`='" . $jmlsppt . "',
		`koordinatulx`='" . $koordinatulx . "', 
		`koordinatuly`='" . $koordinatuly . "', 
		`koordinatlrx`='" . $koordinatlrx . "', 
		`koordinatlry`='" . $koordinatlry . "', 
		`alamat`='" . $alamat . "', 
		`updateby`='" . $_SESSION['standard']['userid'] . "', 
		`lastupdate`='".date('Y-m-d')."'
         where idlahan='" . $mid . "';";
		//exit("error".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert':

	$str = "select * from ".$dbname.".pad_lahan where shm = '".$shm."' ";
    $res = fetchData($str);
    if (empty($res)) {
    	$str = "INSERT INTO `pad_lahan` (`pemilik`, `unit`, `lokasi`, `luas`, `luasinti`, `luasplasma`, `luasdapatditanam`, `statuspermintaandana`, `statuspermbayaran`, `kodeblok`, `posting`, `keterangan`, `nosurat`, `batastimur`, `batasbarat`, `batasutara`, `batasselatan`, `shm`, `jmlsppt`, `koordinatulx`, `koordinatuly`, `koordinatlrx`, `koordinatlry`, `updateby`, `lastupdate`,`alamat`,`statuskawasan`)VALUES 
    ('" . $pemilik . "', '" . $unit . "', '" . $lokasi . "', '" . $luaslahan . "','" . $luasinti . "','" . $luasplasma . "', '" . $bisaditanam . "', '" . $statuspermintaandana . "',
    '" . $statuspermbayaran . "', '" . $blok . "', '" . $posting . "', '" . $keterangan . "', '" . $nosurat . "', '" . $batastimur . "', '" . $batasbarat . "', '" . $batasutara . "', '" . $batasselatan . "', '" . $shm . "', '" . $jmlsppt . "', 
    '" . $koordinatulx . "', '" . $koordinatuly . "', '" . $koordinatlrx . "', '" . $koordinatlry . "', '', '', '" . $alamat . "','".$statuskawasan."')";
    	
    	
    			#exit("error".$str);
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
    }
    else
    {
        exit("Warning : NO SPPT Already Exist");
    }
    break;
    case 'delete':
        $str = "delete from " . $dbname . ".pad_lahan
        where idlahan='" . $mid . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'getPemilik':
		//exit("error:".$_POST['unit']);
        $str = "select padid,nama,desa from " . $dbname . ".pad_5masyarakat";
        /*echo $str;
        exit();*/
        $optpemilik = '';
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if($pemilik==$bar->padid)
            {
            $optpemilik.="<option value='" . $bar->padid . "' selected>" . $bar->nama . "-" . $bar->desa . "</option>";
            }
            else
            {
            $optpemilik.="<option value='" . $bar->padid . "'>" . $bar->nama . "-" . $bar->desa . "</option>";
            }
        }
        if ($optpemilik != '') {
            echo $optpemilik;
        } else {
            echo "Error: Masyarakat pemilik belum ada, silahkan daftar dari menu setup";
            //exit(); //jangan dihapus exit ini
        }
        break;

        case 'getBlok':
        $str = "select kodeorganisasi,namaorganisasi  from " . $dbname . ".organisasi 
        where tipe='BLOK' and kodeorganisasi like '" . $_POST['unit'] . "%'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $optblok = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        while ($bar = $res->fetch()) {
            $optblok.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
        }
        echo $optblok;
        //exit(); //jangan dihapus exit ini
        
        break;
    case 'showupload':
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
	
        $tab = "";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        switch ($jenisupload) {
        case 'statuslahan':
             @$lxxx.="".$_SESSION['lang']['id']." ".$_SESSION['lang']['lahan']."";
             @$lyyy.="".$_SESSION['lang']['nama']."  ".$_SESSION['lang']['pemilik']."";
             @$liii.="".$_SESSION['lang']['kode']."  ".$_SESSION['lang']['blok']."";
            break;
        }
        $tab.="<tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td>
        <label id='ptupload' style='display:none'>".$pt."</label>
        <label style='font-weight:bold'>".$nmorg[$pt]."</label>
        </td>
        </tr>
        <tr>
        <td>".$lxxx."</td>
        <td>:</td>
        <td>
        <label id='xxx' style='font-weight:bold'>".$xxx."</label>
        </td>
        </tr>
        <tr>
        <td>".$lyyy."</td>
        <td>:</td>
        <td>
        <label id='yyy' style='display:none'>".$yyy."</label>
        <label id='yyyxx' style='font-weight:bold'>".$nmmasyarakat[$yyy]."</label>
        </td>
        </tr>";
        if ($iii != '') {
            $tab.="<tr>
                <td>".$liii."</td>
                <td>:</td>
                <td>
                <label id='iii' style='font-weight:bold'>".$nmorg[$iii]."</label>
                </td>
                </tr>";
        }
        $tab.="<tr><td colspan=4><hr></td></tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteria'>". $optkriteria."</select>
				</td>
            </tr>
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
            <button class=mybutton onclick=\"submitfile('".$jenisupload."')\">Submit</button>
            </td>
            </tr>
            </table>
            <p />";
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
            <thead>
            <tr class=rowheader>
            <td align='center' width=50px>No.</td>
            <td align='center'>Kriteria</td>
            <td align='center' width=50px>File Type</td>
            <td align='center'>Filename</td>
            <td align='center' width=50px>Action</td>
            </tr>
            </thead>
            <tbody id='listfiles'>
            </tbody>
            </table>
            </fieldset> ";
        echo $tab;
    break;
    case 'submitfile':
    $tgl = date("YmdHis");
    $his = date("His");
    $data = $_POST;
    if ($data['fileupload'] != '') {
        if ($_FILES['file']['error'] == 0) {
            $filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
            $filename = $pt."_".$xxx."_".$his."_".$_FILES['file']['name'];
            $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
            if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                if ($_FILES['file']['size'] <= 1000000) {
                    $str = "insert into ".$dbname.".listfile_lgl_grltt values ('','".$pt."','".$jenisupload."','".$xxx."','".$yyy."','".$iii."','".$filename."','".$kriteria."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                    try {
                        $owlPDO->exec($str);
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        file_put_contents($path.$filename, $file_tmpname);
                    } catch (PDOException $e) {
                        echo " Gagal,".addslashes($e->getMessage());
                    }
                } else {
                    exit("warning : Ukuran file upload maksimal 10 MB");
                }
            } else {
                exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
            }
        }
    }
    break;
    case 'loadfiles':
    $no = 0;
    $tab = $icon = "";
    $str = "select * from ".$dbname.".listfile_lgl_grltt where kodept = '".$pt."' and status='1' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."'";
    //exit('error'.$str);
    $res = fetchData($str);
    if (empty($res)) {
        $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    } else {
        foreach($res as $key => $val) {
			$optkriteria="";
			$arrmodul = getmodulefil($emodul);
			foreach($arrmodul as $keyx=>$valx){
				if($keyx==$val['kriteriaefil']){
					$optkriteria.="<option value='".$keyx."' selected>".$valx['kriteria']."</option>";
				}else{
					$optkriteria.="<option value='".$keyx."'>".$valx['kriteria']."</option>";
				}
			}
			
            $no++;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
			$tab.="<td style='text-align:center'>
                <label style='display:none'>".getcriterianame($val['kriteriaefil'])."</label>
				<select id='kriteriax_".$val['id']."' onchange=\"changekriteria('".$val['id']."')\">". $optkriteria."</select>
                </td>";
            $icon = seticonfile($val['formaticon']);
            $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";
            $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
            $strp = "select posting from ".$dbname.".pad_lahan where idlahan='".$xxx."'";
            $resp = fetchData($strp);
            if($resp[0]['posting']==0){
            $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['jenis']."','".$val['kodept']."','".$val['field1']."','".$val['field2']."','".$val['field3']."','".$val['namafile']."');\" >";}
            $tab."  </td>
            </tr>";
        }
    }
    echo $tab;
    break;
	
	case'changekriteria':
		$str="update ".$dbname.".listfile_lgl_grltt set kriteriaefil='".$kriteriax."' where id='".$id."'";
		$owlPDO->exec($str);
	break;
    
    case 'viewfile':
    $tab = "";
    $tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
    echo $tab;
    break;
    case 'deletefile':
    $str = "delete from ".$dbname.".listfile_lgl_grltt where kodept='".$pt."' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."' and field3='".$iii."' and namafile='".$namafile."'"; //exit('error'.$str);
    try {
        $owlPDO->exec($str);
        $pathx = $path.$namafile;
        unlink($pathx);
    } catch (PDOException $e) {
        echo " Gagal,".addslashes($e->getMessage());
    }
    break;
    case 'loaddata':
	$limit = 10;
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
	$tab='';
	$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=0 style='width:100%;'>
         <thead>
                <tr class=rowheader>
					<td rowspan=2 align=center>No</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['id'] . "</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['unit'] . "</td>                     
					<td rowspan=2 align=center>" . $_SESSION['lang']['pemilik'] . "</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['lokasi'] . " / (No.Persil)</td>                       
					<td rowspan=2 align=center>SPPT</td>
					<td rowspan=2 align=center>Jlh SPPT</td>
					<td rowspan=2 align=center hidden>" . $_SESSION['lang']['desa'] . "</td>               
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Inti</td>             
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Plasma</td>             
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Total</td>    
					<td rowspan=2 align=center>" . $_SESSION['lang']['bisaditanam'] . "</td> 
					<td rowspan=2 align=center>" . $_SESSION['lang']['blok'] . "</td>    
					<td colspan=4 align=center>" . $_SESSION['lang']['batas'] . "</td> 
					<td colspan=4 align=center>Koordinat</td> 
					<td rowspan=2 align=center>Alamat</td>
					<td rowspan=2 align=center>Status Kawasan</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['keterangan'] . "</td> 
					<td hidden align=center rowspan=2 align=center>" . $_SESSION['lang']['status'] . "</td>      
					<td rowspan=2 align=center>" . $_SESSION['lang']['updateby'] . "</td>   
					<td rowspan=2  align=center > File </td> 
				    <td style='width:75px;'  align=center rowspan=2>Action</td>                
                </tr><tr class=rowheader>   
					<td align=center>" . $_SESSION['lang']['batastimur'] . "</td>                      
					<td align=center>" . $_SESSION['lang']['batasbarat'] . "</td>  
					<td align=center>" . $_SESSION['lang']['batasutara'] . "</td>
					<td align=center>" . $_SESSION['lang']['batasselatan'] . "</td> 
					<td align=center>UL_X</td> 
					<td align=center>UL_Y</td> 
					<td align=center>LR_X</td> 
					<td align=center>LR_Y</td> 
                </tr></thead>
                <tbody>";
		$where="";
        if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
			$where= " and a.unit = '".$_SESSION['empl']['lokasitugas']."'";
		}
        if ($idcari != '') {
            $where.=" and a.idlahan like '%" . $idcari . "%' ";
        }
		if ($pemilikcari != '') {
            $where.=" and a.pemilik in (select padid from " . $dbname . ".pad_5masyarakat where nama like '%" . $pemilikcari . "%')";
        }
		if ($persilcari != '') {
            $where.=" and a.lokasi like '%" . $persilcari . "%' ";
        }
		if ($spptcari != '') {
            $where.=" and a.shm like '%" . $spptcari . "%' ";
        }

		$strjlh = "select a.*,b.nama,b.alamat,b.desa,c.namakaryawan from " . $dbname . ".pad_lahan a
		left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid 
		left join " . $dbname . ".datakaryawan c on a.updateby=c.karyawanid where 1=1 ".$where."";
		$res = fetchData($strjlh);
		$jlhbrs = count($res);
				
		$str1 = "select a.*,b.nama,b.alamat as alamatb,b.desa,c.namakaryawan from " . $dbname . ".pad_lahan a
				left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid 
				left join " . $dbname . ".datakaryawan c on a.updateby=c.karyawanid  where 1=1 ".$where."   
				order by a.idlahan desc, b.nama asc ,b.desa asc limit " . $offset . "," . $limit . "";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
			if($bar1->statuspermintaandana == 0){
				$stdana = "Belum Disetujui";}
			if($bar1->statuspermintaandana == 1){
				$stdana = "Sudah Disetujui";
			}
			$no++;
			@$stkades = $bar1->statuskades == 1 ? tanggalnormal($bar1->tanggalkades) : "";
			@$stcamat = $bar1->statuscamat == 1 ? tanggalnormal($bar1->tanggalcamat) : "";
			$nmdesa=makeOption($dbname,'desa','iddes,desa',"iddes='".$bar1->desa."'");
			$tab.="<tr class=rowcontent> ";
			$tab.="<td align=center>" . $no . "</td>
				   <td align=center>" . $bar1->idlahan. "</td>
				   <td>" . $bar1->unit . "</td>
				   <td>" . $bar1->nama . "</td>
				   <td>" . $bar1->lokasi . "</td>                                 
				   <td>" . $bar1->shm . "</td>                                 
				   <td align=right>" . $bar1->jmlsppt . "</td>                                 
				   <td hidden>" . $nmdesa[$bar1->desa] . "</td>
				   <td align=right>" . $bar1->luasinti . "</td>  
				   <td align=right>" . $bar1->luasplasma . "</td>  
				   <td align=right>" . $bar1->luas . "</td>  
				   <td align=right>" . $bar1->luasdapatditanam  . "</td>
				   <td>" . $bar1->kodeblok . "</td>    
				   <td>" . $bar1->batastimur . "</td>
				   <td>" . $bar1->batasbarat . "</td>
				   <td>" . $bar1->batasutara . "</td>
				   <td>" . $bar1->batasselatan . "</td>
				   <td>" . $bar1->koordinatulx . "</td>
				   <td>" . $bar1->koordinatuly . "</td>
				   <td>" . $bar1->koordinatlrx . "</td>
				   <td>" . $bar1->koordinatlry . "</td>
				   <td>" . $bar1->alamat . "</td>  
				   <td>" . $bar1->statuskawasan . "</td>  
				   <td>" . $bar1->keterangan . "</td> 
				   <td hidden>" . $stdana . "</td>
				   <td>" . $bar1->namakaryawan . "</td>";
				   
			$str = "select * from ".$dbname.".listfile_lgl_grltt where kodept = '".$bar1->unit."' and status='1' and jenis='statuslahan' and field1='".$bar1->idlahan."' and field2='".$bar1->pemilik."'";

			$res = fetchData($str);
			if (empty($res)) {
				$tab.="<td style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>"; 
			} else {
				$namafile='';
				$nox=0;
				foreach($res as $key => $val) {
					$nox++;
					if($namafile==''){
						$namafile=$nox.".".$val['namafile'];
					}else{
						$namafile.="<br>".$nox.".".$val['namafile'];
					}
				}
			$tab.= "<td style='text-align:left'>".$namafile."</td>";
			}                               
		  $tab.="</td>";
		  if($bar1->posting==0){                
			$tab.="<td align=center>
					<img src='images/upload-2-xxl.png' class=zImgBtn title='Upload Document' onclick=showupload(event,'statuslahan','".$bar1->unit."','" . $bar1->kodeblok . "','" . $bar1->idlahan . "','" . $bar1->pemilik . "')>
					<img src='images/skyblue/edit.png' class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->idlahan . "','" . $bar1->pemilik . "','" . $bar1->unit . "','" . $bar1->lokasi . "','" . $bar1->luas . "','" . $bar1->luasinti . "','" . $bar1->luasplasma . "','" . $bar1->batastimur . "','" . $bar1->batasbarat . "','" . $bar1->batasutara . "','" . $bar1->batasselatan . "','" . $bar1->luasdapatditanam . "','" . $bar1->koordinatulx . "','" . $bar1->koordinatuly . "','" . $bar1->koordinatlrx . "','" . $bar1->koordinatlry . "','" . $bar1->kodeblok . "','" . $bar1->keterangan . "','" . $bar1->shm . "','" . $bar1->jmlsppt . "','" . $bar1->alamat . "');\">
					<img src='images/skyblue/posting.png' class='resicon' onclick=\"postingData('" . $bar1->idlahan . "','" . $bar1->unit . "')\" title='Posting'>
					<img src='images/skyblue/delete.png' class='resicon' onclick=\"deleteData('" . $bar1->idlahan . "','" . $bar1->unit . "');\" title='Delete'>
				</td>";
			}else{
                
			$tab.="<td align=center>

					<img src='images/upload-2-xxl.png' class=zImgBtn title='Upload Document' onclick=showupload(event,'statuslahan','".$bar1->unit."','" . $bar1->kodeblok . "','" . $bar1->idlahan . "','" . $bar1->pemilik . "')>

					<img src=images/zoom.png class=resicon  title='View' onclick=showDetail('".$bar1->unit."','" . $bar1->idlahan . "','" . $bar1->pemilik . "')>";
					
			$str = "select * from ".$dbname.".lgl_pembebasanlahan where kodeorg = '".$bar1->unit."' and nosppt='".$bar1->shm."' and nama='".$bar1->pemilik."'";		
			$res = fetchData($str);
			if(count($res)>0){
				$tab.="<img src='images/skyblue/posted.png' class='resicon'  title='Posting'>";				
			}else{
				$tab.="<img src='images/icons/04/16/04.png' class='resicon'  title='UnPosting' onclick=\"unpostingData('" . $bar1->idlahan . "','" . $bar1->unit . "')\">";				
			}
			$tab.="	</td>";
			}
		  $tab.="</tr>";
		}

		$tab.="<tr>";
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
        $tab.="</tr><tr><td colspan=27 align=center>";
        if ($page == '0') {
            $tab.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tab.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $tab.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $tab.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tab.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $tab.="</td>
            </tr>";
			
		
		$tab.="    
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
		echo $tab;
        break;
    case 'deletefileall':
    $str = "select * from ".$dbname.".listfile_lgl_grltt where kodept='".$pt."' and jenis='".$jenisupload."' and field1='".$xxx."' and field2='".$yyy."' and field3='".$iii."'";
    exit('error belom kelar scriptnya'.$str);
    $res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($bar = $res->fetch()) {
        $path2 = $path.$bar['namafile'];
        unlink($path2);
    }
    $str = "delete from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."'";
    try {
        $owlPDO->exec($str);
    } catch (PDOException $e) {
        echo " Gagal,".addslashes($e->getMessage());
    }
    break;
	case 'unposting':
        $str = "update " . $dbname . ".pad_lahan set posting=0 where idlahan=" . $mid;
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;

    case 'showDetail':
        $stream = "";
        $stream = "<table cellspacing=1 cellpadding=5>

                    <input hidden type=text value= '".$unit."'     id='unitx' class=myinputtext  style='width:145px'>
                    <input hidden type=text value= '".$idlahan."'  id='idlahanx' class=myinputtext  style='width:145px'>
                    <input hidden type=text value= '".$pemilik."'  id='pemilikx' class=myinputtext  style='width:145px'>

        
                    <tr>
                        <td> <b>PEMBEBASAN LAHAN</b> </td>
                    </tr>
                    <tr>
                        <td>Tanggal Pembebasan</td>
                        <td>:</td>
			            <td>
                            <input id='tglpembebasan' type='text' style='width:145px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
                        </td>
                    </tr>
                    <tr>
                        <td>Penyelesaian</td>
                        <td>:</td>
                        <td>
                            <input type=text  id=penyelesaian onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style='width:145px'>
                        </td>
                    </tr>
                    <tr>
                        <td> <b>SENGKETA LAHAN</b> </td>
                    </tr>
                    <tr>
                        <td>Tanggal Sengketa</td>
                        <td>:</td>
			            <td>
                            <input id='tglsengketa' type='text' style='width:145px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
                        </td>
                    </tr>
                    <tr>
                        <td>Deskripsi Sengketa</td>
                        <td>:</td>
                        <td>
                            <input type=text  id=deskripsi class=myinputtext  style='width:145px'>
                        </td>
                    </tr>
                    <tr>
                        <td>Kategori Sengketa</td>
                        <td>:</td>
                        <td>
                            <input type=text  id=kategori class=myinputtext  style='width:145px'>
                        </td>
                    </tr>
                    <tr>
                        <td>Catatan</td>
                        <td>:</td>
                        <td>
                            <input type=text  id=catatan class=myinputtext  style='width:145px'>
                        </td>
                    </tr>
                    <tr>
                         <td>
                            <button class=mybutton onclick=simpanDetail()>" . $_SESSION['lang']['save'] . "</button>
                         </td>
                    </tr>";

                $stream .= "</table>";

                
                $stream.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>";
                $stream.="<thead>";
                $stream.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
                    $stream.=" <th colspan=2>PEMBEBASAN LAHAN</th>";
                    $stream.=" <th colspan=4>SENGKETA LAHAN</th>";
                    $stream.=" <th rowspan=2>ACTION</th>";
                $stream.="</tr>";
                $stream.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
                    $stream.=" <th>TANGGAL PEMBEBASAN</th>";
                    $stream.=" <th>PENYELESAIAN</th>";
                    $stream.=" <th>TANGGAL SENGKETA</th>";
                    $stream.=" <th>DESKRIPSI SENGKETA</th>";
                    $stream.=" <th>KATEGORI SENGKETA</th>";
                    $stream.=" <th>CATATAN</th>";
                $stream.="</tr>";         
                $stream.="</thead><tbody>";

                $str = "select * from ".$dbname.".pad_pembebasanlahan where id = '".$idlahan."' and unit = '".$unit."' and pemilik = '".$pemilik."' ";
                $res = fetchData($str);
				foreach($res as $bar) {
                    $stream.="<tr class=rowcontent >";
                         $stream.=" <td>".$bar['tanggalpembebasan']."</td>";
                         $stream.=" <td>".$bar['penyelesaian']."</td>";
                         $stream.=" <td>".$bar['tanggalsengketa']."</td>";
                         $stream.=" <td>".$bar['deskripsisengketa']."</td>";
                         $stream.=" <td>".$bar['kategorisengketa']."</td>";
                         $stream.=" <td>".$bar['catatan']."</td>";
                         $stream.=" <td align=center>
                            <button class=mybutton onclick=hapusDetail('".$bar['noid']."','".$idlahan."','".$unit."','".$pemilik."')>" . $_SESSION['lang']['delete'] . "</button>
                         </td>";
                    $stream.="</tr>";         
                }
                $stream .= "</table>";
        echo $stream;

    break;

    case 'simpanDetail' :

        $str = "select * from ".$dbname.".pad_pembebasanlahan where id = '".$idlahan."' and unit = '".$unit."' and pemilik = '".$pemilik."' ";
        $res = fetchData($str);
        if (empty($res)) {

            $str = "INSERT INTO `pad_pembebasanlahan` (`id`, `unit`, `pemilik`, `tanggalpembebasan`, `penyelesaian`, `tanggalsengketa`, `deskripsisengketa`, `kategorisengketa`, `catatan`)VALUES 
            ('" . $idlahan . "', '" . $unit . "','".$pemilik."' ,'" . tanggalsystemn($tglpembebasan) . "', '" . $penyelesaian . "','" . tanggalsystemn($tglsengketa) . "','" . $deskripsi . "', '" . $kategori . "', '" . $catatan . "'  )";
    	
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        }else{
            exit("Warning : Data sudah ada");
        }

    break;

    case 'hapusDetail' :

        $str = "DELETE FROM " . $dbname . ".pad_pembebasanlahan WHERE noid = '".$noid."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'posting':
        $str = "update " . $dbname . ".pad_lahan set posting=1 where idlahan=" . $mid;
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    default:
        break;
}


//==============Free

?>

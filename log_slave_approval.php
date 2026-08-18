<?php
#ini_set('display_errors',0);
#error_reporting(0);
$mobileValid = false;
if (isset($_POST['par']) || isset($_GET['par'])) {
    $validasiPostMobile = explode(" ", $_POST['par']);
    $validasiGetMobile = explode(" ", @$_GET['par']);
    if ($validasiPostMobile[0] == "owlApp" or $validasiGetMobile[0] == "owlApp") {
        $mobileValid = true;
        $session_id = '';
    };
}

if ($mobileValid == false) { //untuk redirec dari mobile
    require_once 'master_validation.php';
    $session_id = $_SESSION['standard']['userid'];
}
require_once 'lib/nangkoelib.php';
require_once 'config/connection.php';
include_once 'lib/zLib.php';
require_once 'dompdf/autoload.inc.php';
include_once 'lib/rTable.php';

use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$pages = checkPostGet('page', '');

if (count($_POST) > 0) {
    $param = $_POST;
} else {
    $param = $_GET;
}

##SEARCH 1
$crjenispersetujuan = checkPostGet('crjenispersetujuan', '');

$nopoxz = checkPostGet('nopoxz', '');
$karyawanid = checkPostGet('karyawanid', $session_id);
$textpersetujuan = checkPostGet('textpersetujuan', '');
$notransaksi = checkPostGet('notransaksi', '');
$alasan = checkPostGet('alasan', '');
$hasilpersetujuan = checkPostGet('hasilpersetujuan', '');
$jenispersetujuan = checkPostGet('jenispersetujuan', '');
$fromdata = checkPostGet('fromdata', '');
$level = checkPostGet('level', '');
$user_id = checkPostGet('user_id', '');
$nextlevelapp = checkPostGet('nextlevelapp', '');
$tanggaldispo = tanggalsystemn(checkPostGet('tanggaldispo', ''));
$tglskrng = date("Y-m-d H:i:s");

$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'dikoreksi', '3' => 'ditolak');
$suppid = checkPostGet('suppid', '');

##Level Pilih Tender
$leveltender = 3;
$folder = checkPostGet('folder', '');
$table = checkPostGet('table', '');

$path = "fileupload/" . $folder . "/";

#= array kodesupplier
$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $nmsupplier[$bar['supplierid']] = $bar['namasupplier'];
    $kodesupplier[$bar['kodept']] = $bar['supplierid'];
}

#= ambil daftar unit didalam pt bentukan array
$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4 ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kodept[$bar['kodeorganisasi']] = $bar['induk'];
    $nmorg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}

#= HITUNG JUMLAH APPROVAL PADA APPROVAL PROJECT
$stra = "select max(level) as level from " . $dbname . ".approval where  jenispersetujuan='" . $proses . "' and karyawanid!='0000000000'";
$res = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$countAppJ = $bar['level'];

$str = "select * from " . $dbname . ".pmn_4customer";
$res = fetchdata($str);
foreach ($res as $bar) {
    $namacustomer[$bar['kodecustomer']] = $bar['namacustomer'];
}

switch ($method) {
    case 'pdf':
        if (strpos($notransaksi, 'TBSKUD') == true) {
            $table = "kebun_tbskud";
        } else if (strpos($notransaksi, 'TBSAFI') == true) {
            $table = "kebun_tbsafiliasi";
        } else if (strpos($notransaksi, 'TBSEXT') == true) {
            $table = "kebun_tbsexternal";
        }

        $tab = "<style>
				@page {
					margin-top: 50px;
					margin-left: 50px;
					margin-right: 50px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}

				footer {
					position: fixed;
					bottom: -20px;
					left: 0px;
					right: 0px;
					height: 50px;
				}

			</style>";

        $str = "select * from " . $dbname . "." . $table . "
			where notransaksi='" . $notransaksi . "' ";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();

        $supplier = $bar['supplier'];
        $periodetbs = $bar['supplier'];
        $tanggaltbs1 = $bar['tanggaltbs1'];
        $tanggaltbs2 = $bar['tanggaltbs2'];
        $tanggal = $bar['tanggal'];
        $unit = $bar['unit'];

        $cellpadding = 1;
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0 style='font-size:16px'>";
        $tab .= "<tr>";
        $tab .= "<td align=center><b>Pembayaran TBS</td>";
        $tab .= "</tr>";
        $tab .= "</table>";
        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0 style='font-size:12px'>";

        $tab .= "<tr>";
        $tab .= "<td align=left>" . $_SESSION['lang']['nodok'] . "</td>";
        $tab .= "<td align=left>:</td>";
        $tab .= "<td align=left>" . $notransaksi . " </td>";

        $tab .= "<td align=left>" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab .= "<td align=left>:</td>";
        $tab .= "<td align=left>" . tanggalnormal($tanggal) . " </td>";
        $tab .= "</tr>";

        $tab .= "<tr>";
        $tab .= "<td align=left>" . $_SESSION['lang']['pabrik'] . "</td>";
        $tab .= "<td align=left>:</td>";
        $tab .= "<td align=left>" . $nmorg[$unit] . " </td>";

        $tab .= "<td align=left>" . $_SESSION['lang']['supplier'] . "</td>";
        $tab .= "<td align=left>:</td>";
        $tab .= "<td align=left>" . $nmsupplier[$supplier] . " </td>";
        $tab .= "</tr>";

        $tab .= "<tr>";
        $tab .= "<td align=left>" . $_SESSION['lang']['periode'] . "</td>";
        $tab .= "<td align=left>:</td>";
        $tab .= "<td align=left>" . tanggalnormal($tanggaltbs1) . " s/d " . tanggalnormal($tanggaltbs2) . " </td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $cellpadding = 0;

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0.5 style='font-size:12px'>";
        $tab .= "<tr bgcolor=lightgray>";
        $tab .= "<td style='text-align:center;width:10px;'><b>" . $_SESSION['lang']['nourut'] . "</td>";
        $tab .= "<td style='text-align:center;'><b>" . $_SESSION['lang']['tanggal'] . "<br>SPB</td>";
        $tab .= "<td style='text-align:center;'><b>" . $_SESSION['lang']['tanggal'] . "<br>PKS</td>";
        $tab .= "<td style='text-align:center;'><b>" . $_SESSION['lang']['blok'] . "</td>";
        $tab .= "<td style='text-align:center;'><b>Bruto</td>";
        $tab .= "<td style='text-align:center;'><b>Potongan</td>";
        $tab .= "<td style='text-align:center;'><b>Netto</td>";
        $tab .= "<td style='text-align:center;'><b>Rp/Kg</td>";
        $tab .= "<td style='text-align:center;'><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab .= "</tr>";

        $str = "select * from " . $dbname . "." . $table . "
			where notransaksi='" . $notransaksi . "' ";
        // echo $str;exit();
        /*
        $str = "select
        sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
        notransaksi,unit,divisi,tanggal,posting,
        tanggaltbs1,tanggaltbs2     from ".$dbname.".".$table."
        where ".$where."  group by notransaksi limit " . $offset . "," . $limit . " ";
         */
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            @$no++;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=center>" . tanggalnormal($bar['tanggalspb']) . "</td>";
            $tab .= "<td align=center>" . tanggalnormal($bar['tanggalpks']) . "</td>";
            $tab .= "<td>" . $bar['blok'] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['kgbruto'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['kgpotongan'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['kgnetto'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['rpkg'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['totalrp'], 2) . "</td>";
            $tab .= "</tr>";
            @$tkgnetto += $bar['kgnetto'];
            @$tkgbruto += $bar['kgbruto'];
            @$tkgpotongan += $bar['kgpotongan'];
            @$ttotalrp += $bar['totalrp'];
        }
        #= total
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=left colspan=4><b>" . $_SESSION['lang']['total'] . "</b></td>";
        $tab .= "<td align=right><b>" . number_format($tkgbruto, 2) . "</b></td>";
        $tab .= "<td align=right><b>" . number_format($tkgpotongan, 2) . "</b></td>";
        $tab .= "<td align=right><b>" . number_format($tkgnetto, 2) . "</b></td>";
        $tab .= "<td align=right></td>";
        $tab .= "<td align=right><b>" . number_format($ttotalrp, 2) . "</b></td>";
        $tab .= "</tr>";
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=left colspan=8><b>" . $_SESSION['lang']['total'] . " Pembulatan</b></td>";
        $tab .= "<td align=right><b>" . number_format(floor($ttotalrp)) . "</b></td>";
        $tab .= "</tr>";

        $tab .= "</table>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream($table, array("Attachment" => 0));
        break;

    case 'pdf2':
        if (strpos($notransaksi, 'TBSKUD') == true) {
            $table = "kebun_tbskud";
        } else if (strpos($notransaksi, 'TBSAFI') == true) {
            $table = "kebun_tbsafiliasi";
        } else if (strpos($notransaksi, 'TBSEXT') == true) {
            $table = "kebun_tbsexternal";
        }
        $tab = "<style>
				@page {
					margin-top: 50px;
					margin-left: 50px;
					margin-right: 50px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}

				footer {
					position: fixed;
					bottom: -20px;
					left: 0px;
					right: 0px;
					height: 50px;
				}

			</style>";

        $str = "select d.namasupplier,c.namaorganisasi,c.alamat,c.induk,tanggaltbs1,tanggaltbs2
			from " . $dbname . "." . $table . " a
			left join " . $dbname . ".organisasi c on concat('SD',substr(a.divisi,2,1),'E')=c.kodeorganisasi
			left join " . $dbname . ".log_5supplier d on a.supplier=d.supplierid
			where notransaksi ='" . $notransaksi . "' group by a.supplier";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();

        $namasupplier = $bar['namasupplier'];
        $namaorgx = $bar['namaorganisasi'];
        $alamatorgx = $bar['alamat'];
        $indukorg = $bar['induk'];
        $tanggaltbs1 = $bar['tanggaltbs1'];
        $tanggaltbs2 = $bar['tanggaltbs2'];

        $cellpadding = 1;
        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0 style='font-size:12px'>";
        $tab .= "<tr>";
        $tab .= "<td align=left><b>" . $nmpt[$indukorg] . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td align=left><b>" . $namaorgx . "</td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td align=left><b>" . str_replace(',', '<br>', $alamatorgx) . "</td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "<br>";

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0 style='font-size:12px'>";
        $tab .= "<tr>";
        $tab .= "<td align=center><b>PEMBAYARAN TBS PETANI " . $namasupplier . " PERIODE TGL : " . tanggalnormal($tanggaltbs1) . "-" . tanggalnormal($tanggaltbs2) . " </td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        // $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";

        // $tab.="<tr>";
        //     $tab.="<td align=left>".$_SESSION['lang']['no']."</td>";
        //     $tab.="<td align=left>:</td>";
        //     $tab.="<td align=left>".$notransaksi." </td>";

        //     $tab.="<td align=left>".$_SESSION['lang']['tanggal']."</td>";
        //     $tab.="<td align=left>:</td>";
        //     $tab.="<td align=left>".tanggalnormal($tanggal)." </td>";
        // $tab.="</tr>";

        // $tab.="<tr>";
        //     $tab.="<td align=left>".$_SESSION['lang']['pabrik']."</td>";
        //     $tab.="<td align=left>:</td>";
        //     $tab.="<td align=left>".$nmorg[$unit]." </td>";

        //     $tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>";
        //     $tab.="<td align=left>:</td>";
        //     $tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
        // $tab.="</tr>";

        // $tab.="<tr>";
        //     $tab.="<td align=left>".$_SESSION['lang']['periode']."</td>";
        //     $tab.="<td align=left>:</td>";
        //     $tab.="<td align=left>".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>";
        // $tab.="</tr>";
        // $tab.="</table>";

        $tab .= "<br>";

        $cellpadding = 0;

        $tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=0 border=0.5 style='font-size:12px'>";
        $tab .= "<tr bgcolor=lightgray>";
        $tab .= "<td style='text-align:center;width:10px;'><b>" . $_SESSION['lang']['nourut'] . "</td>";
        $tab .= "<td style='text-align:center;'><b>GROUP HAMPARAN</td>";
        $tab .= "<td style='text-align:center;'><b>TAHUN TANAM</td>";
        $tab .= "<td style='text-align:center;'><b>JUMLAH HA</td>";
        $tab .= "<td style='text-align:center;'><b>TBS QTY (KG NETTO)</td>";
        $tab .= "<td style='text-align:center;'><b>HARGA TBS/KG (RP)</td>";
        $tab .= "<td style='text-align:center;'><b>" . $_SESSION['lang']['total'] . " (RP)</td>";
        $tab .= "</tr>";

        $str = "select a.supplier,a.blok,c.namaorganisasi,b.tahuntanam,b.luasareaproduktif as luas,sum(a.kgnetto) as kgtbs,a.rpkg, sum(a.totalrp) as rptot
			from " . $dbname . "." . $table . " a
			left join " . $dbname . ".setup_blok b on a.blok=b.kodeorg
			left join " . $dbname . ".organisasi c on a.blok=c.kodeorganisasi
			where notransaksi ='" . $notransaksi . "' group by a.blok,a.supplier";
        // echo $str;exit();
        /*
        $str = "select
        sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
        notransaksi,unit,divisi,tanggal,posting,
        tanggaltbs1,tanggaltbs2     from ".$dbname.".".$table."
        where ".$where."  group by notransaksi limit " . $offset . "," . $limit . " ";
         */
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $tkgnetto = 0;
        $ttotalrp = 0;
        $ttotluas = 0;

        while ($bar = $res->fetch()) {
            @$no++;
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td align=center>" . $no . "</td>";
            $tab .= "<td align=center>" . $bar['namaorganisasi'] . "</td>";
            $tab .= "<td align=center>" . $bar['tahuntanam'] . "</td>";
            $tab .= "<td align=right>" . number_format($bar['luas'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['kgtbs'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['rpkg'], 2) . "</td>";
            $tab .= "<td align=right>" . number_format($bar['rptot'], 2) . "</td>";
            $tab .= "</tr>";
            @$ttotluas += $bar['luas'];
            @$tkgnetto += $bar['kgtbs'];
            @$ttotalrp += $bar['rptot'];
        }
        #= total
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=left colspan=3><b>" . $_SESSION['lang']['total'] . "</b></td>";
        $tab .= "<td align=right><b>" . number_format($ttotluas, 2) . "</b></td>";
        $tab .= "<td align=right><b>" . number_format($tkgnetto, 2) . "</b></td>";
        $tab .= "<td align=right></td>";
        $tab .= "<td align=right><b>" . number_format($ttotalrp, 2) . "</b></td>";
        $tab .= "</tr>";
        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=left colspan=6><b>" . $_SESSION['lang']['total'] . " Pembulatan</b></td>";
        $tab .= "<td align=right><b>" . number_format(floor($ttotalrp)) . "</b></td>";
        $tab .= "</tr>";

        $tab .= "</table>";

        // $tab.="<br>";
        // $tab.="<br>";

        // $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
        // $tab.="<tr>";
        // $tab.="<td align=center>DITERIMA OLEH</td>";
        // $tab.="<td align=center>DISETUJUI OLEH</td>";
        // $tab.="<td align=center>DIKETAHUI OLEH</td>";
        // $tab.="<td align=center>DIBUAT OLEH</td>";
        // $tab.="</tr>";
        // $tab.="<tr>";
        // $tab.="<td height=150px align=center><b>________________</td>";
        // $tab.="<td height=150px align=center><b>________________</td>";
        // $tab.="<td height=150px align=center><b>________________</td>";
        // $tab.="<td height=150px align=center><b>________________</td>";
        // $tab.="</tr>";
        // $tab.="</table>";

        //exit($tab);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream($table, array("Attachment" => 0));
        break;

    case 'showimages':
        $tab = "";
        $tab .= "<fieldset>
			<legend>" . $_SESSION['lang']['list'] . "</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
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

    case 'loadfiles':

        //listfile_keu_kasbank jadi listfileupload
        $no = 0;
        $tab = "";

        $querynotransaksi = selectQuery($dbname, $table, "notransaksi", "notransaksi='" . $notransaksi . "'");
        $resnotransaksi = fetchData($querynotransaksi);

        foreach ($resnotransaksi as $notrans) {
            $notransaksinew = $notrans['notransaksi'];

            // get file
            $str = "select * from " . $dbname . "." . $table . " where notransaksi = '" . trim($notransaksinew) . "' and status='1'";

            $res = fetchData($str);
            if (empty($res)) {
                $tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
            } else {
                foreach ($res as $key => $val) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
							<td style='text-align:center'>" . $no . "</td>";
                    $icon = seticonfile($val['formaticon']);
                    $tab .= "<td style='text-align:center'>
							<a href='" . $path . $val['namafile'] . "' download><img src=" . $icon . " class=resicon></a>
						</td>";
                    $nfile = '';
                    $nfile = $val['namafile'];
                    $tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfilekasbank('','" . $val['namafile'] . "')\">" . $nfile . "</td>
						<td align=center>
							<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
                    $tab . "	</td>
						</tr>";
                }
            }
        }

        echo $tab;
        break;

    case 'loaddata':
        $where = "";
        if ($crjenispersetujuan != "") {
            $where .= " and jenis='" . $crjenispersetujuan . "'";
        }

        $tab = "";
        $arrJenisPersetujuan = array();
        $arrPersetujuan = array();

        ##List Jenis Approval
        $str = "select * from " . $dbname . ".setup_jenisapproval where status='1' " . $where . " order by nama asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $arrJenisPersetujuan[$bar['jenis']] = $bar['nama'];
        }

        ##Outstanding Approval
        $str = "select count(karyawanid) as jumlah, jenispersetujuan from " . $dbname . ".approval where status in ('0','9') and karyawanid='" . $karyawanid . "' group by jenispersetujuan";
        // echo $str;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $arrPersetujuan[$bar['jenispersetujuan']] = $bar['jumlah'];
        }

        if (count($arrJenisPersetujuan) <= 0) {
            $tab .= "<tr class=rowcontent>
				<td colspan=4 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td>
			</tr>";
        } else {
            $no = 0;
            foreach ($arrJenisPersetujuan as $key => $val) {

                $jumlah = 0;
                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $key . "' and tipe='1' and (tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "' or karyawanid='" . $karyawanid . "')";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                @$level = $bar['level'];

                // if ($level>1){
                //     $levelap=$level-1;
                //     // $str="select karyawanid,notransaksi,level,count(notransaksi) as jmlhnotrans,status from ".$dbname.".approval where  jenispersetujuan='".$key."' and status='1' group by notransaksi having max(level)='".$levelap."' and count(notransaksi)='1'";
                //     // $str="select karyawanid,notransaksi,level,count(notransaksi) as jmlhnotrans,status from ".$dbname.".approval where jenispersetujuan='".$key."' and status='1' and level='".$levelap."' and notransaksi not in (select distinct notransaksi from ".$dbname.".approval where jenispersetujuan='".$key."' and level='".$level."') group by notransaksi";
                //     $str="select a.karyawanid,a.notransaksi,a.level,count(a.notransaksi) as jmlhnotrans,status from ".$dbname.".approval a left join
                //           ".$dbname.".sdm_pjdinasht b on a.notransaksi=b.notransaksi where jenispersetujuan='".$key."' and status='1' and level='".$levelap."'
                //               and statuspersetujuan=0 group by notransaksi";
                //     $res=fetchData($str);
                //     $jumlah=count($res);
                // }

                $att = '';
                if (isset($arrPersetujuan[$key])) {
                    $value = $arrPersetujuan[$key] + $jumlah;
                    $attribut = "style='text-align:center;cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"getdetail('" . $key . "')\"";
                    $att = " style='background-color:#7FFFD4;'";
                } else {
                    $value = 0 + $jumlah;
                    if ($value == 0) {
                        $attribut = "style='text-align:center'";
                    } else {
                        $attribut = "style='text-align:center;cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"getdetail('" . $key . "')\"";
                        $att = " style='background-color:#7FFFD4;'";
                    }
                }
                if ($key == 'PRM') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PRM' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'MTS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='MTS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'DMS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='DMS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'PO') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PO' and karyawanid='" . $karyawanid . "' and status in ('0') group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'HBT') {
                    /*
                    $strpo="select * from ".$dbname.".approval where jenispersetujuan='HBT' and
                    karyawanid='".$karyawanid."'
                    and status='0' group by notransaksi";
                     */
                    $strpo = "select a.*, b.* from " . $dbname . ".approval a
					left join " . $dbname . ".pmn_hargabelitbs b on a.notransaksi = b.notransaksi
					where a.jenispersetujuan='HBT' and a.status='0'
					and a.karyawanid='" . $karyawanid . "'
					group by b.kodeorg,b.tanggal order by b.tanggal desc";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'HJT') {
                    $strpo = "select a.*, b.* from " . $dbname . ".approval a
					left join " . $dbname . ".pmn_hargajualtbs b on a.notransaksi = b.notransaksi
					where a.jenispersetujuan='HJT' and a.status='0'
					and a.karyawanid='" . $karyawanid . "'
					group by b.notransaksi order by b.notransaksi desc";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'BTBS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='BTBS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'SCR') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='SCR' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'CB') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='CB' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'CPX') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='CPX' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'SPK') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='SPK' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'BAA') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='BAA' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'KPI') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='KPI' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'IJS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='IJS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'IJNS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='IJNS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'IJNSC') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='IJNSC' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'IJSC') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='IJSC' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }

                if ($key == 'CBS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='CBS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'DTK1') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='DTK1' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'DTK2') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='DTK2' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'DTK3') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='DTK3' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'MB') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='MB' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }
                if ($key == 'RKB') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='RKB' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'BOR') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='BOR' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'BAPP') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='BAPP' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'RKH') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='RKH' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'BANSOS') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='BANSOS' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'PP') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PP' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'GRL') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='GRL' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'ADJ') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='ADJ' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'PJDTAMU') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PJDTAMU' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'SOP') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='SOP' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'PROJ') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PROJ' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'PDO') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PDO' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);

                    $value = count($respo);
                }

                if ($key == 'PKSMAINTENANCE') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PKSMAINTENANCE' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'PKSCUCITANGKI') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PKSCUCITANGKI' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'PKSBACUCITANGKI') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PKSBACUCITANGKI' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'ERF') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='ERF' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'GRNINO') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='GRNINO' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'GRNISO') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='GRNISO' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'GRNICO') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='GRNICO' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }
                if ($key == 'LBR') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='LBR' and karyawanid='" . $karyawanid . "' and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'SERVICE') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='SERVICE' and (karyawanid='" . $karyawanid . "') and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($key == 'PHP') {
                    $strpo = "select * from " . $dbname . ".approval where jenispersetujuan='PHP' and (karyawanid='" . $karyawanid . "') and status='0' group by notransaksi";
                    $respo = fetchData($strpo);
                    $value = count($respo);
                }

                if ($value == 0) {
                    $attribut = "style='text-align:center'";
                    $att = " style='display:none'";
                } else {
                    $attribut = "style='text-align:center;cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"getdetail('" . $key . "')\"";
                    $att = " style='background-color:#7FFFD4;'";
                    $no++;
                }

                $tab .= "<tr class=rowcontent " . $att . ">
					<td style='text-align:right'>" . $no . "</td>
					<td>" . $key . "</td>
					<td>" . $val . "</td>
					<td " . $attribut . ">" . $value . "</td>
				</tr>";
            }
        }

        echo $tab;
        break;

    case 'getdetail':
        $tab = "";

        $optText = makeOption($dbname, 'setup_jenisapproval', 'jenis,nama', "jenis='" . $proses . "'");
        $tab .= "<table>
			<tr>
				<td>
					<button class=mybutton onclick=loaddata()>" . $_SESSION['lang']['back'] . "</button><p>
				</td>
			</tr>
			<tr style='font-weight:bold'>
				<td>" . $_SESSION['lang']['jenispersetujuan'] . "</td>
				<td>:</td>
				<td>" . $optText[$proses] . "</td>
			</tr>
		</table><br>";

        switch ($proses) {

            case 'KTRKJUAL':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table cellspacing='1' cellpadding='5' border='0' class='sortable'>
						<thead>
						<tr class='rowheader'>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['detail'] . "</td>


							<td align=center>Action</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>";

                $str = "select a.*,
						b.*
						from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_kontrakjual b
						on a.notransaksi=b.nokontrak
						where a.jenispersetujuan='KTRKJUAL' and a.karyawanid='" . $karyawanid . "'and a.status='0'  ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $user_online = $karyawanid;
                while ($bar = $res->fetch()) {
                    $no += 1;
                    $tab .= "<tr class='rowcontent'>
						<td>" . $no . "</td>
						<td>" . $bar['notransaksi'] . "</td>
						<td>" . tanggalnormal($bar['tanggalkontrak']) . "</td>
                        <td align=center>
                                <img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewkontrak('" . $bar['notransaksi'] . "',event);\"> &nbsp
                             </td>";
                    // if($bar['status']==0){
                    // $tab.="<td style='text-align:center'>
                    // <button class=mybutton onclick=\"formalasan('".$proses."','".$proses."','".$bar['notransaksi']."','".$bar['level']."','1',event)\">".$_SESSION['lang']['approve']."</button>

                    // <button class=mybutton onclick=\"formalasan('".$proses."','".$proses."','".$bar['notransaksi']."','".$bar['level']."','2',event)\">".$_SESSION['lang']['ditolak']."</button>
                    // </td>";
                    // }else{
                    // $tab.="<td>".$bar['status']."</td>";
                    // }

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    // echo $showaction."<br>";
                    // echo $level."<br>";

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

									<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
								</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
								<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

								<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
							</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        $expnopo = explode('/', $bar['notransaksi']);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
								<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
								Status : " . $arrDetail['namastatus'] . "<br>
								" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
							</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }

                    $tab .= "</tr>";
                }

                $tab .= "</fieldset>";
                break;

            case 'BAST':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table cellspacing='1' cellpadding='5' border='0' class='sortable'>
						<thead>
						<tr class='rowheader'>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['detail'] . "</td>


							<td align=center>Action</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>";

                $str = "select a.*,
						b.*
						from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_bast b
						on a.notransaksi=b.notransaksi
						where a.jenispersetujuan='BAST' and a.karyawanid='" . $karyawanid . "'and a.status='0'  ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $user_online = $karyawanid;
                while ($bar = $res->fetch()) {
                    $no += 1;
                    $tab .= "<tr class='rowcontent'>
						<td>" . $no . "</td>
						<td>" . $bar['notransaksi'] . "</td>
						<td>" . tanggalnormal($bar['tanggal']) . "</td>
                        <td align=center>
                                <img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewbast('" . $bar['notransaksi'] . "',event);\"> &nbsp
                             </td>";
                    // if($bar['status']==0){
                    // $tab.="<td style='text-align:center'>
                    // <button class=mybutton onclick=\"formalasan('".$proses."','".$proses."','".$bar['notransaksi']."','".$bar['level']."','1',event)\">".$_SESSION['lang']['approve']."</button>

                    // <button class=mybutton onclick=\"formalasan('".$proses."','".$proses."','".$bar['notransaksi']."','".$bar['level']."','2',event)\">".$_SESSION['lang']['ditolak']."</button>
                    // </td>";
                    // }else{
                    // $tab.="<td>".$bar['status']."</td>";
                    // }

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    // echo $showaction."<br>";
                    // echo $level."<br>";

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

									<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
								</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
								<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

								<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
							</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        $expnopo = explode('/', $bar['notransaksi']);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
								<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
								Status : " . $arrDetail['namastatus'] . "<br>
								" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
							</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }

                    $tab .= "</tr>";
                }

                $tab .= "</fieldset>";
                break;

            case 'DO':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table cellspacing='1' cellpadding='5' border='0' class='sortable'>
						<thead>
						<tr class='rowheader'>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['detail'] . "</td>


							<td align=center>Action</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>";

                $str = "select a.*,
						b.*
						from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_suratperintahpengiriman b
						on a.notransaksi=b.nodo
						where a.jenispersetujuan='DO' and a.karyawanid='" . $karyawanid . "'and a.status='0'  ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $user_online = $karyawanid;
                while ($bar = $res->fetch()) {
                    $no += 1;
                    $tab .= "<tr class='rowcontent'>
						<td>" . $no . "</td>
						<td>" . $bar['notransaksi'] . "</td>
						<td>" . tanggalnormal($bar['tanggaldo']) . "</td>
                        <td align=center>
                                <img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewdo('" . $bar['notransaksi'] . "',event);\"> &nbsp
                             </td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    // echo $showaction."<br>";
                    // echo $level."<br>";

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

									<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
								</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
								<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

								<!--button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button --!>
							</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        $expnopo = explode('/', $bar['notransaksi']);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
								<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
								Status : " . $arrDetail['namastatus'] . "<br>
								" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
							</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }

                    $tab .= "</tr>";
                }

                $tab .= "</fieldset>";
                break;

            case 'PHP':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='PHP'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];


                $string = "SELECT a.*, b.* FROM $dbname.approval a
						LEFT JOIN $dbname.lgl_penawaranharga b ON a.notransaksi = b.notransaksi
						WHERE a.jenispersetujuan='$proses' AND a.status='0' AND (a.karyawanid='$karyawanid') GROUP BY b.notransaksi";
                $resing = $owlPDO->query($string) or die(print " Gagal: " . PDOException::getMessage());
                $resing->setFetchMode(PDO::FETCH_ASSOC);
                while ($bering = $resing->fetch()) {
                    # Keluarkan sampai sini
                    $tab .= "<fieldset>
                        <legend>" . $_SESSION['lang']['detail'] . " " . $bering['notransaksi'] . "</legend>
                        <table class='sortable' cellspacing='1' cellpadding='5' border='0'>
                            <thead>
                                <tr class=rowheader>
                                    <td align=center>No.</td>
                                    <td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                                    <td align=center>" . $_SESSION['lang']['nama'] . "</td>
                                    <td align='center'>Verification</td>";

                    for ($i = 1; $i <= $countApp; $i++) {
                        $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                    }
                    $tab .= "</tr>
                            </thead>
                            <tbody>";

                    $str = "SELECT a.*, b.* FROM $dbname.approval a
                            LEFT JOIN $dbname.lgl_penawaranharga b ON a.notransaksi = b.notransaksi
                            WHERE a.jenispersetujuan='$proses' AND a.status='0' AND (a.karyawanid='$karyawanid') AND a.notransaksi='{$bering['notransaksi']}' GROUP BY b.notransaksi";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $nmvhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', "kodevhc='" . $bar['kodevhc'] . "'");
                        $no++;
                        $tab .= "<tr class=rowcontent>
                                    <td align=center>" . $no . "</td>
                                    <td align=left>" . $bar['notransaksi'] . "</td>
                                    <td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
                                    <td align=center>" . $bar['keterangan'] . "</td>";

                        $showaction = 0;
                        $countubahjumlah = 0;
                        $level = 1;
                        $xxx = "";
                        for ($i = 1; $i <= $countApp; $i++) {
                            $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                            $resx = fetchdata($strx);
                            foreach ($resx as $keyx => $valx) {
                                if ($valx['karyawanid'] == $karyawanid) {
                                    if ($valx['status'] == '' || $valx['status'] == 0) {
                                        $showaction = $showaction + 1;
                                    }
                                }

                                if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                    $level = $valx['level'];
                                    $xxx = "conte";
                                    break;
                                }
                            }

                            if ($xxx == "conte") {
                                break;
                            }
                        }

                        if ($showaction != $level || $level == 1) {
                            if ($level > 1) {
                                @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                                if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                    $tab .= "<td style='text-align:center'>
                                                    <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
    
                                                    <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
    
                                                    <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
                                                </td>";
                                } else {
                                    $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                                }
                            } else {
                                $tab .= "<td style='text-align:center'>
                                                <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
    
                                                <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
    
                                                <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
                                            </td>";
                            }
                        } else {
                            $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                        }

                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                            $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                            $respo = fetchdata($strpo);
                            $tipeapp = $respo[0]['tipe'];
                            $departemenapp = $respo[0]['departemen'];
                            $tipekaryawanapp = $respo[0]['tipekaryawan'];
                            $jabatanapp = $respo[0]['jabatan'];

                            if ($tipeapp == '1') {
                                if ($arrDetail['komentar'] == '') {
                                    if ($departemenapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                        $arrDetail['nama'] = $opttipe[$departemenapp];
                                    }

                                    if ($tipekaryawanapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                    }

                                    if ($jabatanapp != '0') {
                                        $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$jabatanapp];
                                    }
                                }
                            }

                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='vertical-align:top;text-align:center'>
                                                <label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
                                                Status : " . $arrDetail['namastatus'] . "<br>
                                                " . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
                                            </td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                        $tab .= "</tr>";

                        //pilih tender yang mana
                        // $str = selectQuery($dbname, 'lgl_penawaranharga', '*', "notransaksi='" . $bar['notransaksi'] . "'");
                        // $rst = fetchData($str);

                        // $pajak = $rst[0]['tax'];
                        // $luas = $rst[0]['luas'];
                        // $rpsat = $rst[0]['rpsat'];
                        // $taxrpsat = $rpsat * $pajak / 100;

                        // $str2 = selectQuery($dbname, 'lgl_penawaranhargadt', '*', "notransaksi='" . $bar['notransaksi'] . "'");
                        // $rst2 = fetchData($str2);
                        // foreach ($rst2 as $v2) {
                        //     $data[$v2['nourut']] = $v2;

                        //     $rupiahtaxoff[$v2['nourut']] = ($v2['rppenawaran'] * $pajak/100);
                        //     $fixrupiahoff[$v2['nourut']] = ($v2['rppenawaran'] - $rupiahtaxoff[$v2['nourut']]);
                        //     $fixrpsatoff[$v2['nourut']] = $fixrupiahoff[$v2['nourut']] / $luas;

                        //     $rupiahtaxnego[$v2['nourut']] = ($v2['rpnegosiasi'] * $pajak/100);
                        //     $fixrupiahnego[$v2['nourut']] = ($v2['rpnegosiasi'] - $rupiahtaxnego[$v2['nourut']]);
                        //     $fixrpsatnego[$v2['nourut']] = $fixrupiahnego[$v2['nourut']] / $luas;
                        // }

                        // $tab .= "<tr>
                        // <table class=sortable cellspacing=1 border=0 width=100% style='margin-top:10px'>
                        //     <thead>
                        //         <tr class=rowheader>
                        //             <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['project'] . "</th>
                        //             <th align=center rowspan=3 style='width:2%'>Tax</th>
                        //             <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        // foreach ($rst as $v) {
                        //     $tab .= " <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        // }
                        // // $tab.=" <th align=center rowspan=1 colspan=4>Summary</th>";
                        // $tab .= "
                        //         </tr>
                        //         <tr class=rowheader>";
                        // for ($i = 1; $i <= count($rst); $i++) {
                        //     $tab .= "
                        //                 <th align=center colspan=2>Penawaran</th>
                        //                 <th align=center colspan=2>Negosiasi</th>
                        //                 <th align=center rowspan=2>Var -RP</th>";
                        // }
                        // $tab .= "
                        //         </tr>
                        //         <tr class=rowheader>
                        //             <th>RP / Sat</th>
                        //             <th style='width:5%'>Luas</th>
                        //             <th>Nominal</th>";
                        // for ($i = 1; $i <= count($rst) * 2; $i++) {
                        //     $tab .= " <th>RP / Sat</th>
                        //                 <th>Nominal</th>";
                        // }
                        // // $tab.=" <th>RP / Sat</th>
                        // //         <th>Nominal</th>
                        // //         <th>Var RP</th>
                        // //         <th>Var (%)</th>
                        // //     </tr>";
                        // $tab .= "
                        //     </thead>
                        //     <tbody>";
                        // $tab .= "
                        //         <tr class=rowcontent>
                        //             <td align=center>" . $rst[0]['keterangan'] . "</td>
                        //             <td align=center></td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prpsat class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['rpsat'] == '0' ? '' : number_format($rst[0]['rpsat'])) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text  maxlength=7 id=pluas class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['luas'] == '0' ? '' : $rst[0]['luas']) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prupiah class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['rupiah'] == '0' ? '' : number_format($rst[0]['rupiah'])) . "' disabled>
                        //             </td>";
                        // foreach ($rst as $v) {
                        //     @$max++;
                        //     $tab .= "
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prpsatoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpsatpenawaran']) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rppenawaran']) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prpsatnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpsatnegosiasi']) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=prupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpnegosiasi']) . "' disabled>
                        //             </td>
                        //             <td id=pvarrp_" . $v['nourut'] . " align=right>" . number_format($data[$v['nourut']]['rppenawaran'] - $data[$v['nourut']]['rpnegosiasi']) . "</td>";
                        // }
                        // $tab .= "
                        //     </tr>";
                        // $tab .= "
                        //     <tr class=rowcontent>
                        //         <td align=center>PPh Final</td>
                        //         <td align=center>
                        //             <input type=text maxlength=3 id=ptax class=myinputtextnumber style=\"width:85%;\" value='" . ($rst[0]['tax'] == 0 ? '' : $rst[0]['tax']) . "' disabled>
                        //         </td>
                        //         <td></td>
                        //         <td></td>
                        //         <td></td>";
                        // foreach ($rst as $v) {
                        //     $tab .= "
                        //             <td align=center></td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=ptaxrupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($rupiahtaxoff[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td align=center></td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=ptaxrupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($rupiahtaxnego[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td></td>";
                        // }
                        // $tab .= " </tr>";
                        // $tab .= "
                        //     <tr class=rowcontent>
                        //         <td align=center><b>Harga sebelum pajak</b></td>
                        //         <td align=center></td>
                        //         <td></td>
                        //         <td></td>
                        //         <td></td>";
                        // foreach ($rst as $v) {
                        //     $tab .= "
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=pfixrpsatoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrpsatoff[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=pfixrupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrupiahoff[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=pfixrpsatnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrpsatnego[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td align=center>
                        //                 <input type=text maxlength=12 id=pfixrupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrupiahnego[$v['nourut']]) . "' disabled>
                        //             </td>
                        //             <td id=pfixvarrp_" . $v['nourut'] . " align=right>" . number_format($fixrupiahoff[$v['nourut']] - $fixrupiahnego[$v['nourut']]) . "</td>";
                        // }
                        // $tab .= "
                        //     </tr>";
                        // $tab .= "
                        //     </tbody>
                        // </table></tr>";
                        $str = selectQuery($dbname, 'lgl_penawaranharga', '*', "notransaksi='" . $bering['notransaksi'] . "'");
                        $rst = fetchData($str);

                        $pajak = $rst[0]['tax'];
                        $luas = $rst[0]['luas'];
                        $rpsat = $rst[0]['rpsat'];

                        $str2 = selectQuery($dbname, 'lgl_penawaranhargadt', '*', "notransaksi='" . $bering['notransaksi'] . "'");
                        $rst2 = fetchData($str2);
                        foreach ($rst2 as $v2) {
                            $data[$v2['nourut']] = $v2;

                            $rupiahtaxoff[$v2['nourut']] = ($v2['rppenawaran'] * $pajak / 100);
                            $fixrupiahoff[$v2['nourut']] = ($v2['rppenawaran'] - $rupiahtaxoff[$v2['nourut']]);
                            $fixrpsatoff[$v2['nourut']] = $fixrupiahoff[$v2['nourut']] / $luas;

                            $rupiahtaxnego[$v2['nourut']] = ($v2['rpnegosiasi'] * $pajak / 100);
                            $fixrupiahnego[$v2['nourut']] = ($v2['rpnegosiasi'] - $rupiahtaxnego[$v2['nourut']]);
                            $fixrpsatnego[$v2['nourut']] = $fixrupiahnego[$v2['nourut']] / $luas;
                        }

                        $str3 = selectQuery($dbname, 'lgl_penawaranhargadt', 'rpnegosiasi,nourut,supplierid,rpsatnegosiasi', "notransaksi='" . $bering['notransaksi'] . "'", 'rpnegosiasi', '', '1');
                        $rst3 = fetchData($str3);
                        foreach ($rst3 as $v3) {
                            $datarpmin = $v3['rpnegosiasi'];
                            $datarpnegomin = $v3['rpsatnegosiasi'];
                        }

                        $tab .= "
                        <table class=sortable cellspacing=1 border=0 width=100%>
                            <thead>
                                <tr class=rowheader>
                                    <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['project'] . "</th>
                                    <th align=center rowspan=3 style='width:2%'>Tax</th>
                                    <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        foreach ($rst as $v) {
                            $tab .= " <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        }
                        $tab .= " <th align=center rowspan=2 colspan=4>Rekomendasi Pemenang</th>";
                        $tab .= " 
                                </tr>
                                <tr class=rowheader>";
                        for ($i = 1; $i <= count($rst); $i++) {
                            $tab .= "
                                        <th align=center colspan=2>Penawaran</th>
                                        <th align=center colspan=2>Negosiasi</th>
                                        <th align=center rowspan=2>Var -RP</th>";
                        }
                        $tab .= "
                                </tr>
                                <tr class=rowheader>
                                    <th>RP / Sat</th>
                                    <th style='width:1%'>Fisik</th>
                                    <th>Nominal</th>";
                        for ($i = 1; $i <= count($rst) * 2; $i++) {
                            $tab .= " <th>RP / Sat</th>
                                        <th>Nominal</th>";
                        }
                        $tab .= " <th>RP / Sat</th>
                                        <th>Nominal</th>
                                        <th>Var RP</th>
                                        <th>Var (%)</th>
                                    </tr>";
                        $tab .= "
                            </thead>
                            <tbody>";
                        $tab .= "
                                <tr class=rowcontent>
                                    <td align=center>" . $rst[0]['keterangan'] . " - " . $nmprjct[$rst[0]['keterangan']] . "</td>
                                    <td align=center></td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsat');hitungrat()\" maxlength=12 id=prpsat class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['rpsat'] == '0' ? '' : number_format($rst[0]['rpsat'])) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('luas');hitungrat()\"  maxlength=7 id=pluas class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['luas'] == '0' ? '' : $rst[0]['luas']) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiah class=myinputtextnumber style=\"width:95%;\" value='" . ($rst[0]['rupiah'] == '0' ? '' : number_format($rst[0]['rupiah'])) . "' disabled>
                                    </td>";
                        foreach ($rst as $v) {
                            @$max++;
                            $tab .= "
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatoff_" . $v['nourut'] . "');hitungoff('" . $v['nourut'] . "','" . getNamaSupplier($v['supplierid']) . "');hitungtax('" . count($rst) . "')\" maxlength=12 id=prpsatoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpsatpenawaran']) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rppenawaran']) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatnego_" . $v['nourut'] . "');hitungnego('" . $v['nourut'] . "','" . getNamaSupplier($v['supplierid']) . "');hitungtax('" . count($rst) . "')\" maxlength=12 id=prpsatnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpsatnegosiasi']) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($data[$v['nourut']]['rpnegosiasi']) . "' disabled>
                                    </td>
                                    <td id=pvarrp_" . $v['nourut'] . " align=right>" . number_format($data[$v['nourut']]['rppenawaran'] - $data[$v['nourut']]['rpnegosiasi']) . "</td>";
                        }
                        $tab .= "<td align=right>" . number_format($datarpnegomin) . "</td>";
                        $tab .= "<td align=right>" . number_format($datarpmin) . "</td>";
                        $tab .= "<td align=right>" . number_format(($datarpmin - $rst[0]['rupiah'])) . "</td>";
                        $tab .= "<td align=right>" . number_format(($datarpmin / $rst[0]['rupiah'] * 100)) . "</td>";
                        $tab .= "
                            </tr>";
                        $tab .= "
                            <tr class=rowcontent>
                                <td align=center>" . $_SESSION['lang']['pajak'] . "</td>
                                <td align=center>
                                    <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('tax')\" maxlength=3 onblur=\"hitungtax('" . $max . "')\" id=ptax class=myinputtextnumber style=\"width:85%;\" value='" . ($rst[0]['tax'] == 0 ? '' : number_format($rst[0]['tax'])) . "' disabled>
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>";
                        foreach ($rst as $v) {
                            $tab .= "
                                    <td align=center></td>
                                    <td align=center>
                                        <input type=text maxlength=12 id=ptaxrupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($rupiahtaxoff[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td align=center></td>
                                    <td align=center>
                                        <input type=text maxlength=12 id=ptaxrupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($rupiahtaxnego[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td></td>";
                        }
                        $tab .= "<td align=right></td>";
                        $tab .= "<td align=right>" . number_format($datarpmin * $pajak / 100) . "</td>";
                        $tab .= "<td align=right></td>";
                        $tab .= "<td align=right></td>";
                        $tab .= " </tr>";
                        $tab .= "
                            <tr class=rowcontent>
                                <td align=center><b>Harga setelah pajak</b></td>
                                <td align=center></td>
                                <td></td>
                                <td></td>
                                <td></td>";
                        foreach ($rst as $v) {
                            $tab .= "
                                    <td align=center>
                                        <input type=text maxlength=12 id=pfixrpsatoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrpsatoff[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text maxlength=12 id=pfixrupiahoff_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrupiahoff[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text maxlength=12 id=pfixrpsatnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrpsatnego[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td align=center>
                                        <input type=text maxlength=12 id=pfixrupiahnego_" . $v['nourut'] . " class=myinputtextnumber style=\"width:95%;\" value='" . number_format($fixrupiahnego[$v['nourut']]) . "' disabled>
                                    </td>
                                    <td id=pfixvarrp_" . $v['nourut'] . " align=right>" . number_format($fixrupiahoff[$v['nourut']] - $fixrupiahnego[$v['nourut']]) . "</td>";
                        }
                        $tab .= "<td align=right>" . number_format(($datarpmin - ($datarpmin * $rst[0]['tax'] / 100)) / $rst[0]['luas']) . "</td>";
                        $tab .= "<td align=right>" . number_format($datarpmin - ($datarpmin * $rst[0]['tax'] / 100)) . "</td>";
                        $tab .= "<td align=right>" . number_format(($datarpmin - $rst[0]['rupiah']) - ($datarpmin * $rst[0]['tax'] / 100)) . "</td>";
                        $tab .= "<td align=right>" . number_format((($datarpmin - ($datarpmin * $rst[0]['tax'] / 100)) / $rst[0]['rupiah'] * 100)) . "</td>";
                        $tab .= "
                            </tr>";
                        $tab .= "
                            </tbody>
                        </table><br>";
                    }
                    $tab .= "</tbody>
                            <tfoot> 
                            </tfoot>
                        </table>
                    </fieldset>";
                }

                break;
            case 'SERVICE':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='SERVICE'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
							<tr class=rowheader>
								<td align=center>No.</td>
								<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
								<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
								<td align=center>" . $_SESSION['lang']['tanggal'] . " Pengajuan</td>
								<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
								<td align=center>" . $_SESSION['lang']['downtime'] . "</td>
								<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".vhc_penggantianht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and (a.karyawanid='" . $karyawanid . "')";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $nmvhc = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc', "kodevhc='" . $bar['kodevhc'] . "'");
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"htmlsvc('" . $bar['notransaksi'] . "');\"></td>
								<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=center>" . $bar['kodevhc'] . "<br>" . $nmvhc[$bar['kodevhc']] . "</td>
								<td align=center>" . $bar['downtime'] . " Jam</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'UNPOST':
                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<div class='table-scroll' style=height:60vh>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center>" . $_SESSION['lang']['menu'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['detail'] . "</th>
					<th colspan='" . $col . "' align='center'>Verification</th>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<th align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</th>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select * from " . $dbname . ".menu where action!=''";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $action[$bar['id']] = $bar['action'];
                }

                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".owlhelp_ticket b on a.notransaksi = b.id
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by notransaksi asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = $bar['kodeorg'];
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . getNamaOrg(substr($bar['kodeorg'], 0, 4)) . "</td>";
                    $tab .= "<td align=left>" . getMenu($action[$bar['info_menu']], 'X') . "</td>";
                    $tab .= "<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn  title='Print' onclick=\"openConvhelppopup('" . $bar['id'] . "')\"></td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }
                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center' nowrap>
								<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
							</td>
							<td style='text-align:center' nowrap>
								<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
						</td>";
                    } else {
                        $tab .= "<td style='color:red' colspan=2>Menunggu Persetujuan Sebelumnya</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
								<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
								Status : " . $arrDetail['namastatus'] . "<br>
								" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
							</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</div>";
                break;
            case 'LBR':
                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<div class='table-scroll' style=height:60vh>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center>" . $_SESSION['lang']['unit'] . "</th>
					<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['detail'] . "</th>
					<th colspan='" . $col . "' align='center'>Verification</th>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<th align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</th>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".sdm_lemburht b on a.notransaksi = b.nopengajuan
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by b.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = $bar['kodeorg'];
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . getNamaOrg(substr($bar['kodeorg'], 0, 4)) . "</td>";
                    if (strlen($bar['kodeorg']) == 4) {
                        $tab .= "<td align=left>UMUM / KANTOR</td>";
                    } else {
                        $tab .= "<td align=left>" . getNamaOrg($bar['kodeorg']) . "</td>";
                    }
                    $tab .= "<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $bar['karyawanid']);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }
                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn  title='Print' onclick=\"previewlbr('" . $bar['kodeorg'] . "','" . $bar['tanggal'] . "','" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\"></td>";

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
							<button class=mybutton onclick=\"previewlbr('" . $bar['kodeorg'] . "','" . $bar['tanggal'] . "','" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>

							<!--<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>-->
							</td>

							<td style='text-align:center'>
							<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
							</td>";
                    } else {
                        $tab .= "<td colspan='" . $col . "'>&nbsp;</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</div>";
                break;

            case 'PNN':
                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['detail'] . "</td>
					<td colspan='" . $col . "' align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".kebun_5basispanen2 b on a.notransaksi = b.nopengajuan
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by a.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = $bar['kodeorg'];
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . getNamaOrg($bar['kodeorg']) . "</td>
						<td align=left>Perubahan harga upah panen TBS</td>
						";
                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"getdatapengajuanpnn('" . $bar['notransaksi'] . "','event','html');\" ></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $bar['karyawanid']);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
							<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>
							</td>

							<td style='text-align:center'>
							<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
							</td>";
                    } else {
                        $tab .= "<td colspan='" . $col . "'>&nbsp;</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and
						tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'  and level='" . $i . "'";
                        $resap = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                        $resap->setFetchMode(PDO::FETCH_ASSOC);
                        $barap = $resap->fetch();
                        $leveldireksi = $barap['level'];

                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($leveldireksi == '') {
                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        } else {
                            $strcount = "select count(level) as jumlahapp from " . $dbname . ".approval where jenispersetujuan='" . $proses . "' and level='" . $i . "' and notransaksi='" . $bar['notransaksi'] . "'";
                            $rescount = $owlPDO->query($strcount) or die(print " Gagal: " . PDOException::getMessage());
                            $rescount->setFetchMode(PDO::FETCH_ASSOC);
                            $barcount = $rescount->fetch();

                            if ($barcount['jumlahapp'] == 1) {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>DIREKSI</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</fieldset>";
                break;
            case 'PNNBR':
                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['detail'] . "</td>
					<td colspan='" . $col . "' align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".kebun_5premikutipbrondolansaja b on a.notransaksi = b.nopengajuan
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by a.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = $bar['kodeorg'];
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . getNamaOrg($bar['kodeorg']) . "</td>
						<td align=left>Perubahan harga upah kutip brondolan saja</td>
						";
                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"getdatapengajuanpnnbr('" . $bar['notransaksi'] . "','event','html');\" ></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $bar['karyawanid']);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
							<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>
							</td>

							<td style='text-align:center'>
							<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
							</td>";
                    } else {
                        $tab .= "<td colspan='" . $col . "'>&nbsp;</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and
						tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'  and level='" . $i . "'";
                        $resap = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                        $resap->setFetchMode(PDO::FETCH_ASSOC);
                        $barap = $resap->fetch();
                        $leveldireksi = $barap['level'];

                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($leveldireksi == '') {
                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        } else {
                            $strcount = "select count(level) as jumlahapp from " . $dbname . ".approval where jenispersetujuan='" . $proses . "' and level='" . $i . "' and notransaksi='" . $bar['notransaksi'] . "'";
                            $rescount = $owlPDO->query($strcount) or die(print " Gagal: " . PDOException::getMessage());
                            $rescount->setFetchMode(PDO::FETCH_ASSOC);
                            $barcount = $rescount->fetch();

                            if ($barcount['jumlahapp'] == 1) {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>DIREKSI</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</fieldset>";
                break;
            //Umar
            case 'GRNINO':
            case 'GRNISO':
            case 'GRNICO':
                $col = 2;
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['pt'] . "</td>
							<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
							<td align=center>" . $_SESSION['lang']['supplier'] . "</td>
							<td align=center>Detail</td>
							<td align='center' colspan=2>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.tanggal, b.unit, b.supplierid, b.nopo from " . $dbname . ".approval a left join " . $dbname . ".log_noninventory AS b on a.notransaksi = b.notransaksi
						where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
						group by a.notransaksi, a.level order by a.tanggal asc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar['unit'] . "'");
                    $optNmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['supplierid'] . "'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $bar['unit'] . "-" . $optNmOrg[$bar['unit']] . "</td>
								<td align=left>" . $bar['nopo'] . "</td>
								<td align=left>" . $optNmSup[$bar['supplierid']] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewgrni('" . $bar['notransaksi'] . "',event);\"> &nbsp
									<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"detailgrni('" . $bar['notransaksi'] . "',event,'');\">
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    // $tab.="<td colspan=2 style='color:red'>".$level."__".$showaction."__".$countApp."</td>";
                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $bar['unit'] . "')\">" . $_SESSION['lang']['disetujui'] . "</button>
									</td>

									<td style='text-align:center'>
									<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            // End Umar
            case 'GR':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['pt'] . "</td>
							<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
							<td align=center>" . $_SESSION['lang']['supplier'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.tanggal , b.kodept, b.idsupplier,b.nopo from " . $dbname . ".approval a
						left join " . $dbname . ".log_transaksiht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $bar['kodept'] . "'");
                    $optNmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['idsupplier'] . "'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $bar['kodept'] . "-" . $optNmOrg[$bar['kodept']] . "</td>
								<td align=left>" . $bar['nopo'] . "</td>
								<td align=left>" . $optNmSup[$bar['idsupplier']] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewgr('" . $bar['notransaksi'] . "',event);\"> &nbsp
									<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"detailgr('" . $bar['notransaksi'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    // $tab.="<td colspan=2 style='color:red'>".$level."__".$showaction."__".$countApp."</td>";
                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'EODBKM':
            case 'EODPNN':
            case 'EODRPNN':
            case 'EODTRK':
            case 'EODWS':
            case 'EODLOG':
            case 'EODKB':
            case 'EODKSR':
            case 'EODLBR':
            case 'EODGR':
            case 'EODSPB':
            case 'EODBKMPOST':
            case 'EODPNNPOST':
            case 'EODRPNNPOST':
            case 'EODSPBPOST':
            case 'EODTRKPOST':
            case 'EODWSPOST':
            case 'EODLOGPOST':
            case 'EODGRPOST':

                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['detail'] . "</td>
					<td colspan='" . $col . "' align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".setup_validasiinput_dt b on a.notransaksi = b.nopengajuan
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by a.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . substr($bar['kodeorg'], 0, 4) . "'");
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = substr($bar['kodeorg'], 0, 4);
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . $optNmOrg[substr($bar['kodeorg'], 0, 4)] . "</td>
						<td align=left>" . $bar['keterangan'] . "</td>
						";
                    //$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailpdfpjdinas('".$bar['notransaksi']."','event','pdf');\" ></td>";

                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"getdatapengajuaneod('" . $bar['notransaksi'] . "','event','html');\" ></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $bar['karyawanid']);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
							<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>
							</td>

							<td style='text-align:center'>
							<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
							</td>";
                    } else {
                        $tab .= "<td colspan='" . $col . "'>&nbsp;</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</fieldset>";
                break;
            case 'ATBS':
                $col = 2;
                $countApp = getCountApproval($proses);
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td align=center>" . $_SESSION['lang']['unit'] . "</td>
					<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
					<td align=center>" . $_SESSION['lang']['detail'] . "</td>
					<td colspan='" . $col . "' align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".kebun_5hargaangkut b on a.notransaksi = b.nopengajuan
					where  a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'
					group by a.notransaksi, a.level order by a.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . substr($bar['blok'], 0, 4) . "'");
                    $nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['kary'] . "'");
                    $optnik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $bar['kary'] . "'");
                    $kodeorg = substr($bar['blok'], 0, 4);
                    $no++;
                    $tab .= "<tr class=rowcontent>
						<td align=center>" . $no . "</td>
						<td align=left>" . $bar['notransaksi'] . "</td>
						<td align=left>" . $optNmOrg[substr($bar['blok'], 0, 4)] . "</td>
						<td align=left>" . $bar['komentar'] . "</td>
						";
                    //$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailpdfpjdinas('".$bar['notransaksi']."','event','pdf');\" ></td>";

                    $tab .= "<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"getdatapengajuan('" . $bar['notransaksi'] . "','event','html');\" ></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $bar['karyawanid']);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
							<button class=mybutton onclick=\"getdata_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "','" . $kodeorg . "')\">" . $_SESSION['lang']['disetujui'] . "</button>
							</td>

							<td style='text-align:center'>
							<button class=mybutton onclick=\"tolak_atbs('" . $bar['notransaksi'] . "','" . $level . "','" . $proses . "')\">" . $_SESSION['lang']['ditolak'] . "</button>
							</td>";
                    } else {
                        $tab .= "<td colspan='" . $col . "'>&nbsp;</td>";
                    }
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and
						tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'  and level='" . $i . "'";
                        $resap = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                        $resap->setFetchMode(PDO::FETCH_ASSOC);
                        $barap = $resap->fetch();
                        $leveldireksi = $barap['level'];

                        $arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($leveldireksi == '') {
                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        } else {
                            $strcount = "select count(level) as jumlahapp from " . $dbname . ".approval where jenispersetujuan='" . $proses . "' and level='" . $i . "' and notransaksi='" . $bar['notransaksi'] . "'";
                            $rescount = $owlPDO->query($strcount) or die(print " Gagal: " . PDOException::getMessage());
                            $rescount->setFetchMode(PDO::FETCH_ASSOC);
                            $barcount = $rescount->fetch();

                            if ($barcount['jumlahapp'] == 1) {
                                $tab .= "<td style='text-align:center'><a href=# onclick=prcek_status_pp('" . $arrDetail['status'] . "')>" . $arrDetail['nama'] . "</a></td>";
                            } else {
                                $tab .= "<td style='text-align:center'>DIREKSI</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }
                $tab .= "</tbody>
					<tfoot>
					</tfoot>
					</table>
					</fieldset>";
                break;
            case 'KASBANK':
                $notransaksi = checkPostGet('notransaksi', '');
                $tanggal1 = tanggalsystemn(checkPostGet('tanggal1', ''));
                $tanggal2 = tanggalsystemn(checkPostGet('tanggal2', ''));
                $noakun = checkPostGet('noakun', '');
                $tipetransaksi = checkPostGet('tipetransaksi', '');
                $supplier = checkPostGet('supplier', '');

                $optnoakun = $opttipe = $optpembayaran = $optsupplier = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
                $optnoakunhis = $opttipehis = $optpembayaranhis = $optsupplierhis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

                $str = "select * from " . $dbname . ".log_5supplier";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $select = '';
                    if ($supplier == $bar['supplierid']) {
                        $select = "selected";
                    }
                    $optsupplier .= "<option value='" . $bar['supplierid'] . "' " . $select . ">" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
                    $optsupplierhis .= "<option value='" . $bar['supplierid'] . "'>" . $bar['supplierid'] . " - " . $bar['namasupplier'] . "</option>";
                }

                $whereJam = " kasbank=1 and detail=1 and  (pemilik='" . $_SESSION['empl']['tipelokasitugas'] . "' or pemilik='GLOBAL' or pemilik='" . $_SESSION['empl']['lokasitugas'] . "')";
                $str = "select * from " . $dbname . ".keu_5akun where " . $whereJam . "";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $select = '';
                    if ($noakun == $bar['noakun']) {
                        $select = "selected";
                    }
                    $optnoakun .= "<option value='" . $bar['noakun'] . "' " . $select . ">" . $bar['namaakun'] . "</option>";
                    $optnoakunhis .= "<option value='" . $bar['noakun'] . "' " . $select . ">" . $bar['namaakun'] . "</option>";
                }

                $arrtipe = array('M' => 'Masuk', 'K' => 'Keluar');
                foreach ($arrtipe as $key => $data) {
                    $select = '';
                    if ($tipetransaksi == $key) {
                        $select = "selected";
                    }
                    $opttipe .= "<option value='" . $key . "' " . $select . ">" . $data . "</option>";
                    $opttipehis .= "<option value='" . $key . "' " . $select . ">" . $data . "</option>";
                }

                $tampiltanggal1 = '';
                if ($tanggal1 != '--') {
                    $tampiltanggal1 = tanggalnormal($tanggal1);
                }

                $tampiltanggal2 = '';
                if ($tanggal2 != '--') {
                    $tampiltanggal2 = tanggalnormal($tanggal2);
                }

                // echo $supplier;
                $limit = 20;
                $page = 0;
                if (isset($_POST['page'])) {
                    $page = $_POST['page'];
                    if ($page < 0) {
                        $page = 0;
                    }
                }
                $offset = @($page * $limit);
                $maxdisplay = @($page * $limit);

                $tab .= "<fieldset>
				<legend>" . $_SESSION['lang']['detail'] . "</legend>";
                $tab .= "<table>
						<tr>
						<td>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td>:</td>
						<td>
							<input type=text id=notransaksisch value='" . $notransaksi . "' size=50 class=myinputtext style=\"width:100px;\">
						</td>

						<td>" . $_SESSION['lang']['noakun'] . "</td>
						<td>:</td>
						<td>
							<select id=noakunsch style=\"width:100px;\">'" . $optnoakun . "'</select>
						</td>

						<td>" . $_SESSION['lang']['tipe'] . "</td>
						<td>:</td>
						<td>
							<select id=tipetransaksisch style=\"width:100px;\">'" . $opttipe . "'</select>
						</td>

						<td colspan=3><button class=mybutton onclick=getdetailsch('KASBANK') >" . $_SESSION['lang']['find'] . "</button>
						<button class=mybutton onclick=getdetail('KASBANK') >" . $_SESSION['lang']['cancel'] . "</button></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['tanggalmulai'] . "</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext value='" . $tampiltanggal1 . "' readonly id=tanggalsch1 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:100px;\">
							</td>

						<td>" . $_SESSION['lang']['tanggalselesai'] . "</td>
						<td>:</td>
						<td>
							<input type=text class=myinputtext value='" . $tampiltanggal2 . "' readonly id=tanggalsch2 size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:100px;\">
						</td>

						<td>" . $_SESSION['lang']['supplier'] . "</td>
						<td>:</td>
						<td>
							<select id=suppliersch style=\"width:100px;\">'" . $optsupplier . "'</select>
							<img id='suppliersch' onclick=z.elSearch('suppliersch',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
						</td>
					</tr> </table>";
                $tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0' width='100%'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>No. Kas</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['nodok'] . "</td>
							<td align=center>" . $_SESSION['lang']['bayarke'] . "</td>
							<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
							<td align=center>Kas Detail</td>
							<td align='center'>Verification</td>";
                $countApp = getCountApproval('KASBANK');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "<td align=center>" . $_SESSION['lang']['createby'] . "</td>
                        </tr>
						</thead>
						<tbody>";

                if ($notransaksi != '') {
                    $where .= " and b.notransaksi like '%" . $notransaksi . "%' ";
                }
                if ($noakun != '') {
                    $where .= " and b.noakun = '" . $noakun . "' ";
                }
                if ($tipetransaksi != '') {
                    $where .= " and b.tipetransaksi = '" . $tipetransaksi . "' ";
                }
                if ($tanggal1 != '--' and $tanggal2 != '--') {
                    $where .= " and b.tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' ";
                }
                if ($supplier != '') {
                    $where .= " and b.notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $supplier . "')";
                }

                $kolom = 9 + $countApp;
                $str = "select a.*,b.bayarkepada,b.jumlah,b.kodeorg,b.tanggal,b.tanggalinput,b.noakun,b.tipetransaksi,b.rekening,b.tanggalpengajuan from " . $dbname . ".approval a
						left join " . $dbname . ".keu_kasbankht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='KASBANK' and a.karyawanid='" . $karyawanid . "' and a.status='0'
						" . $where . "
						order by b.tanggalpengajuan,b.kodeorg asc";
                // echo $str;
                $res = fetchdata($str);
                $jlhbrs = count($res);
                if ($jlhbrs == 0) {
                    $tab .= "<tr class=rowcontent>";
                    $tab .= "<td colspan='" . $kolom . "'>" . $_SESSION['lang']['dataempty'] . "</td>";
                    $tab .= "</tr>";
                } else {

                    $str = "select a.*,b.bayarkepada, b.keterangan as keterangankasbank,b.jumlah,b.kodeorg,b.tanggal,b.tanggalinput,b.noakun,b.tipetransaksi,b.rekening,b.tanggalpengajuan, b.createby from " . $dbname . ".approval a
							left join " . $dbname . ".keu_kasbankht b on a.notransaksi = b.notransaksi
							where a.jenispersetujuan='KASBANK' and a.karyawanid='" . $karyawanid . "' and a.status='0'
							" . $where . "
							order by b.tanggalpengajuan,b.kodeorg asc limit " . $offset . "," . $limit . "";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {

                        $exnopo = explode('/', $bar['notransaksi']);
                        $kodeorg = $bar['kodeorg'];
                        $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                        //Show E-fill
                        $showefill = "";
                        $optefill = makeOption($dbname, 'filemanager', 'namafile,id', "namafile='" . $bar['notransaksi'] . "'");
                        @$idefill = $optefill[$bar['notransaksi']];

                        if ($idefill != '') {
                            $showefill = "<img src='images/efill.png' class='zImgBtn' onclick=\"viewefill('" . $bar['notransaksi'] . "','hide',event)\" title='E-Filling System'>";
                        }

                        # Get list keterangan detail
                        $qKasbankdt = selectQuery($dbname, 'keu_kasbankdt', '*', "notransaksi='" . $bar['notransaksi'] . "'");
                        $resKasbankdt = fetchdata($qKasbankdt);
                        $listKeterangandt = '';
                        foreach ($resKasbankdt as $ardt) {
                            $listKeterangandt .= "<li>" . $ardt['keterangan2'] . "</li>";
                        }

                        ## Get No refrensi
                        $nodok = '';
                        $strx = "select nodok,noakun from " . $dbname . ".keu_kasbankdt where notransaksi='" . $bar['notransaksi'] . "' and (nodok!='' and nodok!='0') limit 1";
                        $resx = fetchdata($strx);
                        if (count($resx) > 0) {
                            ##GRL
                            if ($resx[0]['noakun'] == '1263101') {
                                $strxx = "select * from " . $dbname . ".lgl_pembebasanlahan where notransaksi='" . $resx[0]['nodok'] . "'";
                                $resxx = fetchdata($strxx);
                                $nodok = "<label style='cursor:pointer;color:blue;' title='View Detail' onclick=\"htmlgrl('" . $resx[0]['nodok'] . "','" . $resxx[0]['kodeorg'] . "','" . $resxx[0]['periode'] . "');\">" . $resx[0]['nodok'] . "</label>";
                            }
                        }

                        $no++;
                        $tab .= "<tr class=rowcontent>
									<td align=center>" . $no . "</td>
									<td align=left>" . $bar['notransaksi'] . "</td>
									<td align=left>" . tanggalnormal($bar['tanggalpengajuan']) . "</td>
									<td align=left>" . $kodeorg . "-" . $optNmOrg[$kodeorg] . "</td>
									<td align=center>" . $nodok . "</td>
									<td align=center>" . $bar['bayarkepada'] . "</td>
									<td align=center>
                                        <ul style='padding-left:20px;'>" . $listKeterangandt . "</ul>
                                    </td>
									<td align=right>" . number_format($bar['jumlah'], 2) . "</td>
									<td align=center nowrap>
										<img hidden src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"pdfkasbank('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "',event);\">
										<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"viewdetailkasbank('" . $bar['notransaksi'] . "','0');\">&nbsp;
										<img src=images/uploader/dwnld8.png class=zImgBtn onclick=showimages('listfileupload','" . $bar['notransaksi'] . "','keu_kasbankx') title=view>
										" . $showefill . "
									</td>";

                        $showaction = 0;
                        $countubahjumlah = 0;
                        $level = 1;
                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                                $showaction = $showaction + 1;
                            }
                            if ($arrDetail['karyawanid'] == $karyawanid) {
                                $level = $arrDetail['level'];
                                break;
                            }
                        }
                        if ($showaction != $level || $level == 1) {
                            $tab .= "<td style='text-align:center' nowrap>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        } else {
                            $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                        }
                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                            if ($arrDetail['nama'] != '') {
                                // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                        $tab .= "<td style='text-align:center'>" . getNamaKaryawan($bar['createby']) . "</td>";
                        $tab .= "</tr>";
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
                    $tab .= "
				                <tr><td colspan=16 align=center>
				                <button class=mybutton onclick=getdetailsch('" . $proses . "','" . @($page - 1) . "');>" . $_SESSION['lang']['pref'] . "</button>
				                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage('" . $proses . "')\">" . $isiRow . "</select>
				                <button class=mybutton onclick=getdetailsch('" . $proses . "','" . @($page + 1) . "');>" . $_SESSION['lang']['lanjut'] . "</button>
				                </td>
				                </tr>";
                }
                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";

                $tab .= "<div style='clear:both'></div>";
                $tab .= "<button class=mybutton onclick=showhidehistorykasbank()>" . $_SESSION['lang']['lihat'] . " History</button>";

                $tab .= "<fieldset id=forminfohistorykasbank style=display:none;>
					<legend>History Approval</legend>";

                $tab .= "<table>
					<tr>
					<td>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td>:</td>
					<td>
						<input type=text id=notransaksihis size=50 class=myinputtext style=\"width:100px;\">
					</td>

					<td>" . $_SESSION['lang']['noakun'] . "</td>
					<td>:</td>
					<td>
						<select id=noakunhis style=\"width:100px;\">'" . $optnoakun . "'</select>
					</td>

					<td>" . $_SESSION['lang']['tipe'] . "</td>
					<td>:</td>
					<td>
						<select id=tipetransaksihis style=\"width:100px;\">'" . $opttipe . "'</select>
					</td>

					<td colspan=3><button class=mybutton onclick=historykasbank('KASBANK')>" . $_SESSION['lang']['find'] . "</button>
					<button class=mybutton onclick=cancelhistorykasbank('KASBANK') >" . $_SESSION['lang']['cancel'] . "</button></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['tanggalmulai'] . "</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext readonly id=tanggal1his size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:100px;\">
						</td>

					<td>" . $_SESSION['lang']['tanggalselesai'] . "</td>
					<td>:</td>
					<td>
						<input type=text class=myinputtext   readonly id=tanggal2his size=12 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\" style=\"width:100px;\">
					</td>

					<td>" . $_SESSION['lang']['supplier'] . "</td>
					<td>:</td>
					<td>
						<select id=supplierhis style=\"width:100px;\">'" . $optsupplier . "'</select>
						<img id='supplierhis' onclick=z.elSearch('supplierhis',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:3px;left:3px;'></td>
					</td>
				</tr> </table>";

                $tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0' width='100%'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>No. Kas</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['bayarke'] . "</td>
							<td align=center>Kas Detail</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody id='historykasbank'></tbody>
					</table>
				</fieldset>";
                break;
            case 'PTBS':
                # Get notransaksi di approval
                $query = "SELECT notransaksi FROM " . $dbname . ".approval WHERE jenispersetujuan = 'PTBS' AND karyawanid = '" . $karyawanid . "' AND status = '0'";
                $hasil = fetchData($query);
                foreach ($hasil as $bar) {
                    $arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
                    $explodenotransaksi = explode('/', $bar['notransaksi']);
                    $dttable[$bar['notransaksi']] = $explodenotransaksi[1];
                }
                // echo"<pre>";
                // print_r($arrnotransaksi);
                // print_r($dttable);

                $strapv = "SELECT MAX(level) as persetujuan FROM " . $dbname . ".approval
						WHERE jenispersetujuan = 'PTBS'";
                $resapv = fetchData($strapv);

                // $tab .= "<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
                $tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0'>";
                $tab .= "<thead><tr class=rowheader>
							<th align=center>No.</th>
							<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
							<th align=center>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['transaksi'] . "</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['pabrik'] . "</th>
							<th align=center>" . $_SESSION['lang']['supplier'] . "</th>
							<th align=center>" . $_SESSION['lang']['unit'] . " " . $_SESSION['lang']['induk'] . "</th>
							<th align=center>" . $_SESSION['lang']['sumber'] . " " . $_SESSION['lang']['supplier'] . "</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['kg'] . "</th>
							<th align=center>" . $_SESSION['lang']['total'] . "</th>
							<th align=center>Pembulatan</th>
							<th align=center>" . $_SESSION['lang']['pdf'] . "</th>
							<th align=center width=160px>" . $_SESSION['lang']['action'] . "</th>";
                for ($i = 1; $i <= $resapv[0]['persetujuan']; $i++) {
                    $tab .= "<th>Persetujuan " . $i . "</th>";
                }
                $tab .= "</tr></thead><tbody>";

                $no = 0;
                foreach ($arrnotransaksi as $dtnotransaksi) {

                    if (strpos($dtnotransaksi, 'TBSKUD') == true) {
                        $tbl = "kebun_tbskud";
                        $tipetbs = 'TBSKUD';
                        $tipesupplier = "Kud";
                        $fileslave = "kebun_tbskud_slave.php";
                    } else if (strpos($dtnotransaksi, 'TBSAFI') == true) {
                        $tbl = "kebun_tbsafiliasi";
                        $tipetbs = 'TBSAFI';
                        $tipesupplier = "Inti";
                        $fileslave = "kebun_tbsafiliasi_slave.php";
                    } else if (strpos($dtnotransaksi, 'TBSEXT') == true) {
                        $tbl = "kebun_tbsexternal";
                        $tipetbs = 'TBSEXT';
                        $tipesupplier = "External";
                        $fileslave = "kebun_tbsexternal_slave.php";
                    }

                    if ($tbl == 'kebun_tbsafiliasi') {
                        $str = "SELECT a.notransaksi,
							a.tanggal,
							a.unit,
							a.supplier,
							a.pemilik,
							a.tanggaltbs1,
							a.tanggaltbs2,
							sum(a.kgnetto) as kgnetto,
							sum(a.totalrp) as totalrp,
							b.jenispersetujuan,
							b.level,
							b.karyawanid,
							b.status,a.noreferensi
							FROM " . $dbname . "." . $tbl . " a
							LEFT JOIN " . $dbname . ".approval b
							on a.notransaksi = b.notransaksi
							WHERE b.jenispersetujuan = 'PTBS'
							AND b.karyawanid = '" . $karyawanid . "'
							AND a.notransaksi = '" . $dtnotransaksi . "'
							AND b.status = '0'";
                    } else {
                        $str = "SELECT a.notransaksi,
							a.tanggal,
							a.unit,
							a.supplier,
							a.pemilik,
							a.tanggaltbs1,
							a.tanggaltbs2,
							sum(a.kgnetto) as kgnetto,
							sum(a.totalrp) as totalrp,
							b.jenispersetujuan,
							b.level,
							b.karyawanid,
							b.status
							FROM " . $dbname . "." . $tbl . " a
							LEFT JOIN " . $dbname . ".approval b
							on a.notransaksi = b.notransaksi
							WHERE b.jenispersetujuan = 'PTBS'
							AND b.karyawanid = '" . $karyawanid . "'
							AND a.notransaksi = '" . $dtnotransaksi . "'
							AND b.status = '0'";
                    }

                    $res = fetchData($str);

                    foreach ($res as $key => $val) {
                        $no++;
                        $sts = 1;
                        $arrapv = array();

                        if ($val['level'] > 1) {
                            $strver = "SELECT level, status
									FROM " . $dbname . ".approval
									WHERE jenispersetujuan = 'PTBS'
									AND notransaksi = '" . $val['notransaksi'] . "'
									AND level = " . $val['level'] . " - 1";
                            $resver = fetchData($strver);

                            $sts = $resver[0]['status'];
                        }

                        if ($tbl == 'kebun_tbsafiliasi' and $val['noreferensi'] != '') {
                            $tipesupplier = "Kud";
                        }

                        $notransaksiprint = $val['notransaksi'];
                        $tableprint = $tbl;
                        if ($val['noreferensi'] != '') {
                            $notransaksiprint = $val['noreferensi'];
                            $tableprint = 'kebun_tbskud';
                        }

                        $tab .= "<tr class=rowcontent>
									<td align=center>" . $no . "</td>
									<td>" . $val['notransaksi'] . "</td>
									<td>" . $tipetbs . "</td>
									<td>" . tanggalnormal($val['tanggal']) . "</td>
									<td>" . $val['unit'] . "</td>
									<td>" . $nmsupplier[$val['supplier']] . "</td>
									<td>" . $val['pemilik'] . "</td>
									<td>" . $tipesupplier . "</td>
									<td>" . tanggalnormal($val['tanggaltbs1']) . " s/d " . tanggalnormal($val['tanggaltbs2']) . "</td>
									<td align=right>" . number_format($val['kgnetto'], 2) . "</td>
									<td align=right>" . number_format($val['totalrp'], 2) . "</td>
									<td align=right>" . number_format(floor($val['totalrp'])) . "</td>
									<td align=center>
										&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print " . $val['notransaksi'] . "' onclick=\"pdftbs('" . $val['notransaksi'] . "','" . $fileslave . "');\">
										&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print data timbangan " . $val['notransaksi'] . "' onclick=\"pdftimbangan('" . $notransaksiprint . "','" . $tableprint . "','" . $fileslave . "');\"></td>
									";

                        if ($val['status'] == 0 && $sts == 1) {
                            $tab .= "<td align=center>
										<button class=mybutton onclick=\"formalasan('PTBS','PTBS','" . $val['notransaksi'] . "','" . $val['level'] . "','1',event)\">" . $_SESSION['lang']['setuju'] . "</button>
										&nbsp;
										<button class=mybutton onclick=\"formalasan('PTBS','PTBS','" . $val['notransaksi'] . "','" . $val['level'] . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
									</td>";
                        } else if ($sts != 1) {
                            $tab .= "<td align=center style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                        } else if ($val['status'] == 1) {
                            $tab .= "<td align=center style='color:green'>Anda Telah Setuju</td>";
                        } else if ($val['status'] == 3) {
                            $tab .= "<td align=center style='color:red;font-weight:bold'>Persetujuan Dikoreksi</td>";
                        }

                        for ($i = 1; $i <= $resapv[0]['persetujuan']; $i++) {
                            @$arrDetail = detailApprove($i, $val['notransaksi'], 'PTBS');

                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td align=center>
											<b>" . $arrDetail['nama'] . "</b><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                        $tab .= "</tr>";
                    }
                }
                $tab .= "</tbody></table>";
                break;
            case 'DOF':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='DOF'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];
                //$countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Pengajuan</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Mulai</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Sampai</td>
							<td align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['hari'] . "</td>
							<td align=center>" . $_SESSION['lang']['verifikasi'] . "</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $lblpersetujuan = $_SESSION['lang']['persetujuan'] . $i;

                    $tab .= "<td align=center>" . $lblpersetujuan . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".sdm_dayoff a
						left join " . $dbname . ".approval b on a.notransaksi = b.notransaksi
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						where b.jenispersetujuan='DOF' and b.status='0' and b.karyawanid='" . $_SESSION['standard']['userid'] . "' group by a.notransaksi order by b.tanggal desc";
                // echo $str;

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['namakaryawan'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalpengajuan']) . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalmulai']) . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>
								<td align=left>" . $bar['jumlahharidayoff'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'DOFNS':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Pengajuan</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Mulai</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . " Sampai</td>
							<td align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['hari'] . "</td>
							<td align=center>" . $_SESSION['lang']['verifikasi'] . "</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $lblpersetujuan = $_SESSION['lang']['persetujuan'] . $i;
                    if ($i == 1) {
                        $lblpersetujuan = "Persetujuan 1";
                        // $lblpersetujuan = "Atasan";
                    }
                    if ($i == 2) {
                        $lblpersetujuan = "Persetujuan 2";
                        // $lblpersetujuan = "Head Dept";
                    }
                    if ($i == 3) {
                        $lblpersetujuan = "Persetujuan 3";
                        // $lblpersetujuan = "HRD";
                    }
                    $tab .= "<td align=center>" . $lblpersetujuan . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".sdm_dayoff a
						left join " . $dbname . ".approval b on a.notransaksi = b.notransaksi
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						where b.jenispersetujuan='" . $proses . "' and b.status='0' and b.karyawanid='" . $_SESSION['standard']['userid'] . "' group by a.notransaksi order by b.tanggal desc";
                // echo $str;

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['namakaryawan'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalpengajuan']) . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalmulai']) . "</td>
								<td align=left>" . tanggalnormal($bar['tanggalsampai']) . "</td>
								<td align=left>" . $bar['jumlahharidayoff'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'ERF':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='ERF'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align=center>" . $_SESSION['lang']['pekerjasekarang'] . "</td>
							<td align=center>" . $_SESSION['lang']['pekerjadibutuhkan'] . "</td>
							<td align=center>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, d.namajabatan, c.lokasitugas from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_req_employee b on a.notransaksi = b.notransaksi
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						left join " . $dbname . ".sdm_5jabatan d on c.kodejabatan = d.kodejabatan
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center><img src=images/zoom.png class=zImgBtn title=Permintaan Karyawan onclick=previewERF('" . $bar['notransaksi'] . "',event);></td>
								<td align=center>" . $bar['jumlahpekerjasekarang'] . "</td>
								<td align=center>" . $bar['jumlahpekerjadibutuhkan'] . "</td>
								<td align=center>" . $bar['statuskaryawan'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'PRM':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='PRM'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggalsurat'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipetransaksi'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_riwayatjabatan b on a.notransaksi = b.nomorsk
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center><img src=images/pdf.jpg class=resicon  title='" . $_SESSION['lang']['pdf'] . "' onclick=\"pengajuansk('" . $bar['nomorsk'] . "',event);\"></td>
								<td align=center>" . tanggalnormal($bar['tanggalsk']) . "</td>
								<td align=center>" . $bar['tipesk'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'MTS':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='MTS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggalsurat'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipetransaksi'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_riwayatjabatan b on a.notransaksi = b.nomorsk
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center><img src=images/pdf.jpg class=resicon  title='" . $_SESSION['lang']['pdf'] . "' onclick=\"pengajuansk('" . $bar['nomorsk'] . "',event);\"></td>
								<td align=center>" . tanggalnormal($bar['tanggalsk']) . "</td>
								<td align=center>" . $bar['tipesk'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'DMS':
                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where jenispersetujuan='DMS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggalsurat'] . "</td>
							<td align=center>" . $_SESSION['lang']['tipetransaksi'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_riwayatjabatan b on a.notransaksi = b.nomorsk
						left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center><img src=images/pdf.jpg class=resicon  title='" . $_SESSION['lang']['pdf'] . "' onclick=\"pengajuansk('" . $bar['nomorsk'] . "',event);\"></td>
								<td align=center>" . tanggalnormal($bar['tanggalsk']) . "</td>
								<td align=center>" . $bar['tipesk'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        if ($level > 1) {
                            @$arrDetail = detailApprove(($i - 1), $bar['notransaksi'], $proses);
                            if ($arrDetail['status'] == 1 || $arrDetail['status'] == '') {
                                $tab .= "<td style='text-align:center'>
												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

												<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
											</td>";
                            } else {
                                $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['lokasitugas'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'PR':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='1' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>No. PR/SR</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>PR/SR Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('PR');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('PR');

                // kamus urgensi PR: H: high, R: rutin, T: critical, U: urgent
                $kamuswarnaur['R'] = '[RUTIN]';
                $kamuswarnaur['H'] = '<span style="color:#0000FF">[HIGH]</span>';
                $kamuswarnaur['T'] = '<span style="color:#FF00FF">[CRITICAL]</span>';
                $kamuswarnaur['U'] = '<span style="color:#FF0000">[URGENT]</span>';
                $str = "SELECT nopp, prioritas from " . $dbname . ".log_prapodt where 1 order by FIELD(prioritas, 'R','H','T','U') ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kamusurgensi[$bar['nopp']] = $kamuswarnaur[$bar['prioritas']];
                }

                $str = "select a.*, b.nopp, b.tanggal, b.ket_balik, b.close from " . $dbname . ".approval a
						left join " . $dbname . ".log_prapoht b on a.notransaksi = b.nopp
						where a.jenispersetujuan='PR' and a.status='0' and b.close!='2' and a.karyawanid='" . $karyawanid . "' order by b.tanggal asc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kodeorg = substr($bar['nopp'], 15, 4);
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                    $bgcolor = '';
                    $kursor = '';
                    $title = '';
                    if ($bar['ket_balik'] != '') {
                        $bgcolor = 'bgcolor=orange';
                        $kursor = 'style=cursor:pointer';
                        $title = "title=\"PP telah di Return oleh dept Purchasing dg alasan : " . $bar['ket_balik'] . "\" ";
                    }

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left " . $bgcolor . "  " . $title . " " . $kursor . ">" . $bar['nopp'] . "</br>" . $kamusurgensi[$bar['nopp']] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $kodeorg . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_prapoht','" . $bar['nopp'] . "','','log_slave_print_log_pp_new',event);\"> &nbsp
									<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"prpreviewDetail('" . $bar['nopp'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['nopp'], 'PR');
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                            if ($i >= 2) {
                                $countubahjumlah = 1;
                            }
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button style='width:110px' class=mybutton onclick=\"prget_data_pp('" . $bar['nopp'] . "','" . $level . "')\">" . $_SESSION['lang']['approve'] . "</button>
										<button style='width:110px' class=mybutton onclick=\"prrejected_pp('" . $bar['nopp'] . "','" . $level . "')\">" . $_SESSION['lang']['ditolak'] . "</button> ";

                        if ($countubahjumlah == 1) {
                            $tab .= "<button style='width:110px;display:none' class=mybutton onclick=\"prrejected_some_proses('" . $bar['nopp'] . "','" . $level . "')\">" . $_SESSION['lang']['ditolak_some'] . "</button>
											<button style='width:110px;display:none' class=mybutton onclick=\"tambahBarang('" . $bar['nopp'] . "','" . $level . "','" . $_SESSION['lang']['find'] . "',event)\">Ubah Jumlah</button> ";
                        } else {
                            $tab .= "<button style='width:110px;display:none' class=mybutton onclick=\"prrejected_some_proses('" . $bar['nopp'] . "','" . $level . "')\">" . $_SESSION['lang']['ditolak_some'] . "</button>";
                        }
                        $tab .= "</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['nopp'], 'PR');

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'RFQ':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>No. DPH</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['purchaser'] . "</td>
							<td align=center>Verificator</td>
							<td align=center>" . $_SESSION['lang']['view'] . "</td>
							<td align=center>" . $_SESSION['lang']['action'] . "</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $str = "select a.*, b.nomor, b.tanggal, b.purchaser, b.picverifikasi, c.supplierid from " . $dbname . ".approval a
						left join " . $dbname . ".log_perintaanhargaht b on a.notransaksi = b.nomor
						left join " . $dbname . ".log_permintaanhargavw c on b.nomor=c.nomor
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and b.statusverifikasi='1' and a.karyawanid='" . $_SESSION['standard']['userid'] . "'
						and score='1'
						group by b.nomor order by b.tanggal asc";
                $res = fetchdata($str);
                foreach ($res as $val) {
                    $optpurchaser = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['purchaser'] . "'");
                    $optverificator = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['picverifikasi'] . "'");
                    $optNamamorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

                    $dt = explode("/", $val['nomor']);
                    $unit = $dt[4];

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td style='text-align:right;color:blue;cursor:pointer'>
									<label onclick=\"previewlinkpemenang('" . $val['nomor'] . "', '" . $val['supplierid'] . "', 'Detail PC' ,event)\" style='color:blue;cursor:pointer' title='Detail PC'>" . $val['nomor'] . "</label>
									<!--<label onclick=\"previewlink('" . $val['nomor'] . "', '', 'Detail PC' ,event)\" style='color:blue;cursor:pointer' title='Detail PC'>" . $val['nomor'] . "</label>-->
								</td>
								<td align=left>" . tanggalnormal($val['tanggal']) . "</td>
								<td align=left>" . $optNamamorg[$unit] . "</td>
								<td align=left>" . $optpurchaser[$val['purchaser']] . "</td>
								<td align=left>" . $optverificator[$val['picverifikasi']] . "</td>
								<td align=center>
									<!--<img src=images/pdf.jpg class=resicon title='Print' onclick=\"getlaporan(event,'pdf','" . $val['nomor'] . "');\"> &nbsp-->
									<img src=images/zoom.png class=resicon height='30' title='Detail Pemenang PC' onclick=\"previewlinkpemenang('" . $val['nomor'] . "', '" . $val['supplierid'] . "', 'Detail Pemenang PC' ,event);\">
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $val['nomor'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $_SESSION['standard']['userid']) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $_SESSION['standard']['userid'] && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['nomor'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['disetujui'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['nomor'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select count(notransaksi) as cnotran from " . $dbname . ".approval where notransaksi='" . $val['nomor'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        $cnotran = $resx[0]['cnotran'];
                        if ($cnotran > 0) {
                            @$arrDetail = detailApprove($i, $val['nomor'], $proses);

                            $strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $val['unit'] . "' and level='" . $i . "'";
                            $resx = fetchdata($strx);
                            $tipeapp = $resx[0]['tipe'];
                            $departemenapp = $resx[0]['departemen'];
                            $tipekaryawanapp = $resx[0]['tipekaryawan'];
                            $jabatanapp = $resx[0]['jabatan'];

                            if ($tipeapp == '1') {
                                if ($arrDetail['komentar'] == '') {
                                    if ($departemenapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                        $arrDetail['nama'] = $opttipe[$departemenapp];
                                    }

                                    if ($tipekaryawanapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                    }

                                    if ($jabatanapp != '0') {
                                        $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$jabatanapp];
                                    }
                                }
                            }

                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'></td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'ADJ':
                $countApp = getCountApproval($proses);
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>";
                $tab .= "<tr class='rowheader'>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>No.</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['notransaksi'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['gudang'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['namabarang'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['jenis'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['jumlah'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['hargasatuan'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['tanggal'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['noreferensi'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['keterangan'] . "</th>";
                $tab .= "<th style='text-align:center;vertical-align:middle' colspan='1'>Verification</th>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<th style='text-align:center;vertical-align:middle'>" . $_SESSION['lang']['persetujuan'] . " " . $i . "</th>";
                }
                $tab .= "</tr>";
                $tab .= "</thead>";
                $tab .= "<tbody>";

                $str = "SELECT * FROM $dbname.approval AS a LEFT JOIN $dbname.log_stopname_log_list AS b on b.notransaksi = a.notransaksi WHERE a.jenispersetujuan = '" . $proses . "' AND a.status = '0' and a.karyawanid = '" . $karyawanid . "' GROUP BY a.notransaksi, a.level ORDER BY b.tanggal ASC";
                $res = fetchdata($str);
                foreach ($res as $val) {
                    $optNamamorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
                    $optNamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');

                    $dt = explode("/", $val['notransaksi']);
                    $unit = $dt[4];

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=center>" . $val['notransaksi'] . "</td>
								<td align=center>" . $optNamamorg[$val['kodegudang']] . "</td>
								<td align=center>" . $optNamabarang[$val['kodebarang']] . "</td>
								<td align=center>" . $val['jenis'] . "</td>
								<td align=center>" . number_format($val['jumlah']) . "</td>
								<td align=center>" . number_format($val['hargasatuan']) . "</td>
								<td align=center>" . $val['tanggal'] . "</td>
								<td align=center>" . $val['notransaksi_ref'] . "</td>
								<td align=center>" . $val['keterangan'] . "</td>
								";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $val['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $_SESSION['standard']['userid']) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $_SESSION['standard']['userid'] && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['disetujui'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select count(notransaksi) as cnotran from " . $dbname . ".approval where notransaksi='" . $val['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        $cnotran = $resx[0]['cnotran'];
                        if ($cnotran > 0) {
                            @$arrDetail = detailApprove($i, $val['notransaksi'], $proses);

                            $strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $val['unit'] . "' and level='" . $i . "'";
                            $resx = fetchdata($strx);
                            $tipeapp = $resx[0]['tipe'];
                            $departemenapp = $resx[0]['departemen'];
                            $tipekaryawanapp = $resx[0]['tipekaryawan'];
                            $jabatanapp = $resx[0]['jabatan'];

                            if ($tipeapp == '1') {
                                if ($arrDetail['komentar'] == '') {
                                    if ($departemenapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                        $arrDetail['nama'] = $opttipe[$departemenapp];
                                    }

                                    if ($tipekaryawanapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                    }

                                    if ($jabatanapp != '0') {
                                        $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$jabatanapp];
                                    }
                                }
                            }

                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        } else {
                            $tab .= "<td style='text-align:center'></td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PO':
                $tab .= "<fieldset>
					<legend>Cari</legend>
					<table border='0'>
					<tbody>
					<tr>
					<td>No.PO/SO</td>
					<td><input type=text class='myinputtext' id='caripox' style='width:150px' value='" . $nopoxz . "'/></td>
					<td><button class=mybutton onclick=\"caripoxz()\">Cari</button></td>
					</tr>
					</tbody>
					</table></fieldset>";
                //exit('Error '.$nopoxz);
                $countApp = getCountApproval($proses);

                $limit = 20;
                $page = 0;
                if (isset($_POST['page'])) {
                    $page = $_POST['page'];
                    if ($page < 0) {
                        $page = 0;
                    }
                }
                $offset = @($page * $limit);
                $maxdisplay = @($page * $limit);
                $tab .= "<div >
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<th align=center>No.</th>
							<th align=center>No. PO/SO</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</th>
							<th align=center>Item Pekerjaan</th>
							<th align=center>PO/SO Detail</th>
							<th align=center>Chat</th>
							<th align='center'>Verification</th>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<th align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</th>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $kolom = 6 + $countApp;
                $str = "select a.*, b.tanggal , b.kodeorg, b.nodph, b.kodesupplier from " . $dbname . ".approval a
							left join " . $dbname . ".log_poht b on a.notransaksi = b.nopo
							where a.jenispersetujuan='" . $proses . "' and a.status in ('0') and a.karyawanid='" . $karyawanid . "' and a.notransaksi like '%" . $nopoxz . "%' group by a.notransaksi order by b.tanggal asc";
                $res = fetchdata($str);
                $jlhbrs = count($res);
                if ($jlhbrs == 0) {
                    $tab .= "<tr class=rowcontent>";
                    $tab .= "<td colspan='" . $kolom . "'>" . $_SESSION['lang']['dataempty'] . "</td>";
                    $tab .= "</tr>";
                } else {
                    // $str="select a.*, b.tanggal , b.kodeorg, b.nodph, b.kodesupplier from ".$dbname.".approval a
                    // left join ".$dbname.".log_poht b on a.notransaksi = b.nopo
                    // where a.jenispersetujuan='".$proses."' and a.status in ('0','9') and a.karyawanid='".$karyawanid."' and a.notransaksi like '%".$nopoxz."%'  group by a.notransaksi order by b.tanggal asc limit ".$offset.",".$limit."";

                    ## Di buat hanya bisa approval jika status pengajuan 0
                    ## 9 tidak boleh bisa approve
                    $str = "select a.*, b.tanggal , b.kodeorg, b.nodph, b.kodesupplier,b.nopo from " . $dbname . ".approval a
							left join " . $dbname . ".log_poht b on a.notransaksi = b.nopo
							where a.jenispersetujuan='" . $proses . "' and a.status in ('0') and a.karyawanid='" . $karyawanid . "' and a.notransaksi like '%" . $nopoxz . "%'  group by a.notransaksi order by b.tanggal asc limit " . $offset . "," . $limit . "";

                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $nomordph = "";
                        $nox = 0;
                        $listitem = "";
                        // $strx="select a.nomor,a.kodebarang from ".$dbname.".log_permintaanhargadt a left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor where a.norph='".$bar['nodph']."' and b.supplierid='".$bar['kodesupplier']."'";
                        $strx = "select a.kodebarang from " . $dbname . ".log_podt a left join " . $dbname . ".log_poht b on a.nopo=b.nopo where a.nopo='" . $bar['nopo'] . "'";
                        $resx = fetchData($strx);
                        foreach ($resx as $valx) {
                            $optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $valx['kodebarang'] . "'");
                            $nox++;
                            $nomordph = $valx['nomor'];
                            if ($nox == 1) {
                                $listitem .= "- " . $optNmBrg[$valx['kodebarang']];
                            } else {
                                $listitem .= "<br>- " . $optNmBrg[$valx['kodebarang']];
                            }
                        }

                        $exnopo = explode('/', $bar['notransaksi']);
                        $kodeorg = $exnopo[4];
                        $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                        #periksa chat
                        $strChat = "select *  from " . $dbname . ".log_po_chat where nopo='" . $bar['notransaksi'] . "'";
                        $resChat = $owlPDO->query($strChat) or die(print " Gagal: " . PDOException::getMessage());
                        if (owlBaris($resChat) > 0) {
                            $ingChat = "<img src='images/chat1.png' onclick=\"loadPOChat('" . $bar['notransaksi'] . "',event);\" class=resicon>";
                        } else {
                            $ingChat = "<img src='images/chat0.png'  onclick=\"loadPOChat('" . $bar['notransaksi'] . "',event);\" class=resicon>";
                        }

                        $no++;
                        $tab .= "<tr class=rowcontent>
									<td align=center>" . $no . "</td>
									<td align=left style='cursor:pointer;color:blue' onclick=\"previewlinkpemenang('" . $bar['nodph'] . "', '" . $bar['kodesupplier'] . "', 'Detail Riwayat Perbandingan Harga' ,event)\">" . $bar['notransaksi'] . "</td>
									<td align=center style='min-width:70px;'>" . tanggalnormal($bar['tanggal']) . "</td>
									<td align=left>" . $kodeorg . "-" . $optNmOrg[$kodeorg] . "</td>
									<td align=left>" . $listitem . "</td>
									<td align=center>
										<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF5('log_poht','" . $bar['notransaksi'] . "','','log_slave_print_detail_po',event);\"> &nbsp
										<img style='display:none' src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"prpreviewDetail('" . $bar['notransaksi'] . "',event);\">
									</td>
									<td " . $bgcolor . " align=center>" . $ingChat . "</td>
									";

                        $showaction = 0;
                        $countubahjumlah = 0;
                        $level = 1;
                        $xxx = "";
                        for ($i = 1; $i <= $countApp; $i++) {
                            // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                            $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                            $resx = fetchdata($strx);
                            foreach ($resx as $keyx => $valx) {
                                if ($valx['karyawanid'] == $karyawanid) {
                                    if ($valx['status'] == '' || $valx['status'] == 0) {
                                        $showaction = $showaction + 1;
                                    }
                                }

                                if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                    $level = $valx['level'];
                                    $xxx = "conte";
                                    break;
                                }
                            }

                            if ($xxx == "conte") {
                                break;
                            }
                        }

                        // $tab.="<td colspan=2 style='color:red'>".$level."__".$showaction."__".$countApp."</td>";
                        if ($showaction != $level || $level == 1) {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
										</td>";
                        } else {
                            $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                        }

                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                            $expnopo = explode('/', $bar['notransaksi']);

                            $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='PO' and kodeunit='" . $expnopo[4] . "' and level='" . $i . "'";
                            $respo = fetchdata($strpo);
                            $tipeapp = $respo[0]['tipe'];
                            $departemenapp = $respo[0]['departemen'];
                            $tipekaryawanapp = $respo[0]['tipekaryawan'];
                            $jabatanapp = $respo[0]['jabatan'];

                            if ($tipeapp == '1') {
                                if ($arrDetail['komentar'] == '') {
                                    if ($departemenapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                        $arrDetail['nama'] = $opttipe[$departemenapp];
                                    }

                                    if ($tipekaryawanapp != '') {
                                        $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                    }

                                    if ($jabatanapp != '0') {
                                        $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                        $arrDetail['nama'] = $opttipe[$jabatanapp];
                                    }
                                }
                            }

                            if ($arrDetail['nama'] != '') {
                                // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                        $tab .= "</tr>";
                    }
                    //$tab.=$str;
                    $totrows = ceil($jlhbrs / $limit);
                    if ($totrows == 0) {
                        $totrows = 1;
                    }
                    $isiRow = '';
                    for ($er = 1; $er <= $totrows; $er++) {
                        $sel = ($page == $er - 1) ? 'selected' : '';
                        $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
                    }
                    $tab .= "
				                <tr><td colspan=16 align=center>
				                <button class=mybutton onclick=getdetail('" . $proses . "','" . @($page - 1) . "');>" . $_SESSION['lang']['pref'] . "</button>
				                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage('" . $proses . "')\">" . $isiRow . "</select>
				                <button class=mybutton onclick=getdetail('" . $proses . "','" . @($page + 1) . "');>" . $_SESSION['lang']['lanjut'] . "</button>
				                </td>
				                </tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</div>";
                break;
            case 'CB':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>Sub Asset</td>
							<td align=center>" . $_SESSION['lang']['nama'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggalmulai'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggalselesai'] . "</td>
							<td align=center>File Pendukung</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                ## CEK DARI PARAMETER APLLIKASI YANG BISA EDIT CAPEX ##
                $str = "select * from " . $dbname . ".setup_parameterappl where kodeaplikasi='CB' and kodeparameter='CBAPP'";
                $res = fetchData($str);
                $depedit = $res[0]['nilai'];
                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".spl_capexbangunan b on a.notransaksi = b.kode
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by b.kode desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $optNmSubAsset = makeOption($dbname, 'sdm_5subtipeasset', 'kodesub,namasub', "kodesub='" . $bar['subtipe'] . "' and kodetipe='BG'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=left>" . $optNmSubAsset[$bar['subtipe']] . "</td>
								<td align=left>" . $bar['nama'] . "</td>
								<td align=center>" . tanggalnormal($bar['tanggalmulai']) . "</td>
								<td align=center>" . tanggalnormal($bar['tanggalselesai']) . "</td>
								<td style='vertical-align:top'>
									<table>";
                    $str2 = "select * from " . $dbname . ".listfileupload where notransaksi='" . $bar['notransaksi'] . "'";
                    $res2 = fetchData($str2);
                    $no2 = 0;
                    foreach ($res2 as $key2 => $val2) {
                        $no2++;
                        $tab .= "<tr>
											<td>" . $no2 . ".</td>
											<td>
												<a href='fileupload/capexbg/" . $val2['namafile'] . "' download>" . substr($val2['namafile'], 0, 30) . "...</a>
											</td>
										</tr>";
                    }
                    $tab .= "</table>
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }

                        // if($arrDetail['status']=='' || $arrDetail['status']==0)
                        // {
                        // $showaction = $showaction + 1;
                        // }

                        // if($arrDetail['karyawanid']==$karyawanid)
                        // {
                        // $level = $arrDetail['level'];
                        // break;
                        // }
                    }

                    $tab .= "<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewcb('" . $bar['notransaksi'] . "',event);\"> &nbsp;
									<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"appshowcapex('" . $bar['notransaksi'] . "','',event);\"> &nbsp";
                    if (strtolower($depedit) == strtolower($_SESSION['empl']['bagian']) && $_SESSION['empl']['tipelokasitugas'] == 'PABRIK') {
                        $tab .= "<img src=images/nxbtn.png class=resicon height='30' title='Edit' onclick=\"appeditcapex('" . $bar['notransaksi'] . "',event);\">";
                    }
                    if ($level > ($leveltender + 1) && $bar['pekerjaan'] == 'External') {
                        $optPemenang = makeOption($dbname, 'spl_capexbangunan', 'kode,kontraktor', "kode='" . $bar['notransaksi'] . "'");
                        $tab .= "<img src=images/nxbtn.png class=resicon height='30' title='Pemenang Tender' onclick=\"appshowcapex('" . $bar['notransaksi'] . "','" . $optPemenang[$bar['notransaksi']] . "',event);\">";
                    }
                    $tab .= "</td>";

                    // $tab.="<td colspan=2 style='color:red'>".$level."__".$showaction."__".$countApp."</td>";
                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>";
                        if ($level == $leveltender && $bar['pekerjaan'] == 'External') {
                            $tab .= "<button class=mybutton onclick=\"addtendercapex('" . $bar['notransaksi'] . "',event)\">Pilih Tender</button>";
                        } else if ($level == ($leveltender + 1) && $bar['pekerjaan'] == 'External') {
                            $tab .= "<button class=mybutton onclick=\"addtendercapex2('" . $bar['notransaksi'] . "',event)\">" . $_SESSION['lang']['approve'] . "</button>";
                            $tab .= "<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        } else {
                            $tab .= "<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>";
                            $tab .= "<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        }
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['kodeorg'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>";
                            $strx = "select count(kode) as countkode from " . $dbname . ".spl_tendercapex where kode='" . $bar['notransaksi'] . "'";
                            $resx = fetchdata($strx);
                            $countkode = $resx[0]['countkode'];
                            if ($i == $leveltender && $bar['pekerjaan'] == 'External') {
                                if ($countkode > 0) {
                                    $tab .= "Submiting Contractor";
                                } else {
                                    $tab .= "Status : " . $arrDetail['namastatus'] . "<br>";
                                    $tab .= ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']);
                                }
                            } else {
                                $tab .= "Status : " . $arrDetail['namastatus'] . "<br>";
                                $tab .= ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']);
                            }
                            $tab .= "</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'BAJS':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader style='text-align:center'>
							<td>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td>" . $_SESSION['lang']['perusahaan'] . "</td>
							<td>" . $_SESSION['lang']['unit'] . "</td>
							<td>No. " . $_SESSION['lang']['kontrak'] . "</td>
							<td>" . $_SESSION['lang']['tanggal'] . "</td>
							<td>" . $_SESSION['lang']['supplier'] . "</td>
							<td>" . $_SESSION['lang']['deskripsi'] . "</td>
							<td>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.*, c.supplierid, c.deskripsi, sum(b.jumlah) as jlhrealisasi from " . $dbname . ".approval a
						left join " . $dbname . ".log_bakontrakjasa b on a.notransaksi = b.notransaksi
						left join " . $dbname . ".log_kontrakjasa c on b.nokontrak = c.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by b.notransaksi desc";
                $res = fetchdata($str);
                $no = 0;
                foreach ($res as $val) {
                    $strhtg = "SELECT b.dgnapproval, max(c.level) as lvl, c.karyawanid  FROM " . $dbname . ".log_bakontrakjasa a
							LEFT JOIN " . $dbname . ".project b ON a.subunitdt=b.kode
							LEFT JOIN " . $dbname . ".project_approval c ON b.kode=c.kode where notransaksi='" . $val['notransaksi'] . "'";
                    $res = fetchdata($strhtg);
                    $maxhitung = $res[0]['lvl'];
                    $dgnapproval = $res[0]['dgnapproval'];
                    if ($dgnapproval == 1) {
                        $countApp = $maxhitung;
                    }

                    $no++;
                    $nmsupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $val['supplierid'] . "'");
                    $tab .= "<tr class=rowcontent>";
                    $tab .= "<td align=center>" . $no . "</td>";
                    $tab .= "<td align=left nowrap>" . $val['notransaksi'] . "</td>";
                    $tab .= "<td align=left>" . getNamaOrg($val['pt']) . "</td>";
                    $tab .= "<td align=left>" . getNamaOrg($val['unit']) . "</td>";
                    $tab .= "<td align=left nowrap>" . $val['nokontrak'] . "</td>";
                    $tab .= "<td align=left nowrap>" . tanggalnormal($val['tanggal']) . "</td>";
                    $tab .= "<td align=left>" . $nmsupplier[$val['supplierid']] . "</td>";
                    $tab .= "<td align=left>" . $val['deskripsi'] . "</td>";
                    $tab .= "<td align=right>" . hidezerodecimal($val['jlhrealisasi'], 2) . "</td>";
                    $tab .= "<td align=center>
								<img src='images/zoom.png' class='resicon' title='Detail Supplier' onclick=previewbajs('" . $val['nokontrak'] . "',event,'approval','" . $val['notransaksi'] . "');>
							</td>";

                    ## BEGIN APPROVAL ##
                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $val['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center;'>
									<button style=width:85px; class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>&nbsp;";

                        $tab .= "<button style=width:85px; class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>&nbsp;";

                        $tab .= "<button style=width:85px; class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $val['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
								</td>";
                    } else {
                        $tab .= "<td colspan=2 style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $val['notransaksi'], $proses);

                        $strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $val['unit'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        $tipeapp = $resx[0]['tipe'];
                        $departemenapp = $resx[0]['departemen'];
                        $tipekaryawanapp = $resx[0]['tipekaryawan'];
                        $jabatanapp = $resx[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
										<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
										Status : " . $arrDetail['namastatus'] . "<br>
										" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
									</td>";
                        } else {
                            $tab .= "<td style='text-align:center' colspan=" . (5 - $countApp) . ">-</td>";
                        }
                    }
                    ## END APPROVAL ##

                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'SCR':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_scr b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewscr('" . $bar['notransaksi'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td colspan=2 style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['kodeorg'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'KL':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>" . $_SESSION['lang']['materialgroupcode'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakelompok'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakelompok'] . " (EN)</td>
							<td align=center>" . $_SESSION['lang']['noakun'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $skonversi = array("0" => "No", "1" => "Yes");
                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".log_5klbarang b on a.notransaksi = b.kode
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.kode desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kelompok'] . "</td>
								<td align=left>" . $bar['kelompok1'] . "</td>
								<td align=left>" . $bar['noakun'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'SKL':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
							<td>" . $_SESSION['lang']['kodesubkelompokbarang'] . "</td>
							<td>" . $_SESSION['lang']['namasubkelompokbarang'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $skonversi = array("0" => "No", "1" => "Yes");
                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".log_5subklbarang b on a.notransaksi = b.kode
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.kode desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['kelompok'] . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['namasubkelompok'] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'MB':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>" . $_SESSION['lang']['kelompokbarang'] . "</td>
							<td align=center>" . $_SESSION['lang']['subkelompokbarang'] . "</td>
							<td align=center>" . $_SESSION['lang']['materialcode'] . "</td>
							<td align=center>" . $_SESSION['lang']['materialname'] . "</td>
							<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
							<td align=center>" . $_SESSION['lang']['jenis'] . "</td>
							<td align=center style=display:none>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center style=display:none>" . str_replace(" ", "<br>", $_SESSION['lang']['minstok']) . "</td>
						    <td align=center style=display:none>" . str_replace(" ", "<br>", $_SESSION['lang']['nokartubin']) . "</td>
						    <td align=center>" . $_SESSION['lang']['konversi'] . "</td>
						    <td align=center>" . $_SESSION['lang']['inisial'] . "</td>
							<td align=center>QR Code</td>
							<td align=center>Detail<br>" . $_SESSION['lang']['photo'] . "</td>
							<td align=center>Pembuat</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $skonversi = array("0" => "No", "1" => "Yes");
                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".log_5masterbarang b on a.notransaksi = b.kodebarang
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.kodebarang desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $optNmSubAsset = makeOption($dbname, 'sdm_5subtipeasset', 'kodesub,namasub', "kodesub='" . $bar['subtipe'] . "' and kodetipe='BG'");
                    $optnmkl = makeOption($dbname, 'log_5klbarang', 'kode,kelompok', "kode='" . $bar['kelompokbarang'] . "'");
                    $optnmsubkl = makeOption($dbname, 'log_5subklbarang', 'kode,namasubkelompok', "kode='" . substr($bar['notransaksi'], 0, 5) . "'");

                    $adx = "<img src=images/zoom.png class=resicon height=16px title='View detail'  onclick=viewDetailbarang('" . $bar['kodebarang'] . "',event)>";
                    $wherekaryawan = " karyawanid='" . $bar['createby'] . "'";
                    $arrNmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $wherekaryawan);
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['kelompokbarang'] . "-" . $optnmkl[$bar['kelompokbarang']] . "</td>
								<td align=left>" . substr($bar['notransaksi'], 0, 5) . "-" . $optnmsubkl[substr($bar['notransaksi'], 0, 5)] . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['namabarang'] . "</td>
								<td align=center>" . $bar['satuan'] . "</td>
								<td align=center>" . $bar['jenis'] . "</td>
								<td align=center>" . $skonversi[$bar['konversi']] . "</td>
								<td align=center>" . $bar['inisial'] . "</td>
								<td align=center><img src='images/qrcode/" . $bar['kodebarang'] . ".png'></td>
								<td align=center>" . $adx . "</td>
								<td align=center>" . @$arrNmkary[$bar['createby']] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'DS':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
							<td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
							<td align=center>" . $_SESSION['lang']['namapemilik'] . "</td>
							<td align=center>" . $_SESSION['lang']['detail'] . "</td>
							<td align=center>Pembuat</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $strnama = array("0" => "Tidak Aktif", "1" => "Aktif", "2" => "Belum Disetujui", "3" => "Register / Boleh Diupdate");
                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".log_5supplier b on a.notransaksi = b.supplierid
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.supplierid desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $wherekaryawan = " karyawanid='" . $bar['createby'] . "'";
                    $arrNmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $wherekaryawan);

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['namasupplier'] . "</td>
								<td align=center>" . $bar['namapemilik'] . "</td>
								<td align=center><img src='images/zoom.png' class='resicon' title='Detail Supplier' onclick=detailsupp('" . $bar['supplierid'] . "');></td>
								<td align=center>" . $arrNmkary[$bar['createby']] . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }

                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'HBT':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['view'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";
                /*
                $str="select a.*, b.* from ".$dbname.".approval a
                left join ".$dbname.".pmn_hargabelitbs b on a.notransaksi = b.notransaksi
                where a.jenispersetujuan='".$proses."' and a.status='0'
                and a.karyawanid='".$karyawanid."'
                group by a.notransaksi order by b.tanggal desc";
                 */

                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_hargabelitbs b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0'
						and a.karyawanid='" . $karyawanid . "'
						group by b.kodeorg,b.tanggal,b.tipe order by b.tanggal desc";

                // echo $str;
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . " " . substr($bar['tanggal'], 11, 8) . " s/d " . tanggalnormal($bar['tanggal2']) . " " . substr($bar['tanggal2'], 11, 8) . "</td>
		 
								<td align=center><img src='images/skyblue/zoom.png' class='resicon' title='Add Detail " . $bar['notransaksi'] . "' onclick=\"viewhargatbsperunit('" . $bar['kodeorg'] . "','" . $bar['tanggal'] . "','" . $bar['tipe'] . "')\"></td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }

                        // $strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
                        // $resx=fetchdata($strx);
                        // foreach($resx as $keyx=>$valx){
                        // if($valx['karyawanid']==$karyawanid){
                        // if($valx['status']=='' || $valx['status']==0)
                        // {
                        // $showaction = $showaction + 1;
                        // }
                        // }

                        // if($valx['karyawanid']==$karyawanid && $valx['status']==0)
                        // {
                        // $level = $valx['level'];
                        // $xxx = "conte";
                        // break;
                        // }
                        // }

                        // if($xxx=="conte"){
                        // break;
                        // }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['kodeorg'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'HJT':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['customer'] . "</td>
							<td align=center>" . $_SESSION['lang']['view'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".pmn_hargajualtbs b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0'
						and a.karyawanid='" . $karyawanid . "'
						group by b.notransaksi order by b.notransaksi desc";

                // echo $str;
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . " s/d " . tanggalnormal($bar['tanggal2']) . "</td>
								<td align=left>" . $namacustomer[$bar['kodecustomer']] . "</td>
								<td align=center><img src='images/skyblue/zoom.png' class='resicon' title='Add Detail " . $bar['notransaksi'] . "' onclick=\"formajukanhargajualtbs('" . $bar['notransaksi'] . "')\"></td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $karyawanid) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['kodeorg'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'BTBS':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['detail'] . "</td>
							<td align=center>" . $_SESSION['lang']['supplier'] . "</td>
							<td align=center>" . $_SESSION['lang']['bonus'] . "/kg</td>
							<td align=center>Total kg</td>
							<td align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['bonus'] . " </td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*,b.tanggal,b.kodeunit,b.kodesupplier,sum(total_terima) as totterima,bonus_perkg,
							sum(totalrupiahbonus) as totbonus, c.* from " . $dbname . ".approval a
							left join " . $dbname . ".keu_persediaantbs_vw b on a.notransaksi = b.notransaksi
							left join log_5supplier c on b.	kodesupplier=c.supplierid
							where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by b.tanggal desc, c.namasupplier asc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $optSupp = makeOption($dbname, "log_5suptimbangan", "supplierid,kodetimbangan", "supplierid='" . $bar['kodesupplier'] . "'");
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kodeunit'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=center>
									<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' onclick=\"detailbonustbs('" . $bar['notransaksi'] . "','" . $bar['kodeunit'] . "','" . $bar['tanggal'] . "','" . $optSupp[$bar['kodesupplier']] . "','event');\"></td>
								<td align=left>" . $bar['kodesupplier'] . " - " . $bar['namasupplier'] . "</td>
								<td align=center>" . number_format($bar['totterima'], 2) . "</td>
								<td align=right>" . number_format($bar['bonus_perkg'], 2) . "</td>
								<td align=right>" . number_format($bar['totbonus'], 2) . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['kodeunit'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'CPX':
                $countApp = getCountApproval($proses);

                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $str = "select a.*, b.* from " . $dbname . ".approval a
						left join " . $dbname . ".log_formcapex_ht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' group by a.notransaksi order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left style='cursor:pointer;color:blue' onclick=\"viewdetailcapex('" . $bar['notransaksi'] . "',event)\">" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['unit'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>";

                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    $status = '1';
                    for ($i = 1; $i <= $countApp; $i++) {
                        // @$arrDetail = detailApprove($i,$bar['notransaksi'],$proses);

                        $strx = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $i . "'";
                        $resx = fetchdata($strx);
                        $coutUser = count($resx);
                        foreach ($resx as $keyx => $valx) {
                            if ($valx['karyawanid'] == $karyawanid) {
                                if ($valx['status'] == '' || $valx['status'] == 0) {
                                    $showaction = $showaction + 1;
                                }
                            } else {
                                if ($coutUser == '1') {
                                    $status = $valx['status'];
                                }
                            }

                            if ($valx['karyawanid'] == $karyawanid && $valx['status'] == 0) {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }

                        if ($xxx == "conte") {
                            break;
                        }
                    }

                    if (($showaction != $level || $level == 1) and $status == '1') {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        $strpo = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and kodeunit='" . $bar['unit'] . "' and level='" . $i . "'";
                        $respo = fetchdata($strpo);
                        $tipeapp = $respo[0]['tipe'];
                        $departemenapp = $respo[0]['departemen'];
                        $tipekaryawanapp = $respo[0]['tipekaryawan'];
                        $jabatanapp = $respo[0]['jabatan'];

                        if ($tipeapp == '1') {
                            if ($arrDetail['komentar'] == '') {
                                if ($departemenapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
                                    $arrDetail['nama'] = $opttipe[$departemenapp];
                                }

                                if ($tipekaryawanapp != '') {
                                    $opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                                }

                                if ($jabatanapp != '0') {
                                    $opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
                                    $arrDetail['nama'] = $opttipe[$jabatanapp];
                                }
                            }
                        }

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'IOPS':
                $_GET['PERSETUJUAN'] = 'TRUE';
                include "vhc_slave_approval_byyijinops.php";
                break;
            case 'KONTAN':
                $_GET['PERSETUJUAN'] = 'TRUE';
                include "vhc_slave_approval_kontanan.php";
                break;
            case 'BANSOS':
                include "vhc_slave_approval_bansos.php";
                break;
            case 'PP':
                include "vhc_slave_approval_bansos.php";
                break;
            case 'FEE': #Legal - Pengajuan Pembayaran
                include "vhc_slave_approval_fee.php";
                break;
            case 'SPK':
                include "vhc_slave_approval_spk.php";
                break;
            case 'BAA':
                include "sdm_slave_approval_bafinger.php";
                break;
            case 'KPI':
                include "sdm_slave_approval_kpi.php";
                break;
            case 'IJNS':
                include "sdm_slave_approval_ijns.php";
                break;
            case 'IJS':
                include "sdm_slave_approval_ijs.php";
                break;
            case 'IJNSC':
                include "sdm_slave_approval_ijnsc.php";
                break;
            case 'IJSC':
                include "sdm_slave_approval_ijsc.php";
                break;
            case 'CBS':
                include "sdm_slave_approval_cbs.php";
                break;
            case 'DTK1':
                include "sdm_slave_approval_dtk.php";
                break;
            case 'DTK2':
                include "sdm_slave_approval_dtk.php";
                break;
            case 'DTK3':
                include "sdm_slave_approval_dtk.php";
                break;
            case 'RKB':
                include "kebun_slave_rkbx_approval.php";
                break;
            case 'BOR':
                include "kebun_slave_borongan_approval.php";
                break;
            case 'BAPP':
                include "log_slave_bapp_approval.php";
                break;
            case 'RKH':
                include "kebun_slave_rkh_approval.php";
                break;
            case 'SRV':
                include "gis_slave_approval_srv.php";
                break;
            case 'GRL':
                include "lgl_slave_approval_grl.php";
                break;
            #===============================================
            case 'PJDSTF':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PJDNSTF':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PJDMGR':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PJDPC':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PJDGM':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PJDBOD':
                include "log_slave_approval_perjalanan_dinas.php";
                break;
            case 'PTA':
                include "log_slave_approval_pta.php";
                break;

            #==============================================
            case 'PDO':
                include "keu_slave_approval_pdo.php";
                break;

            case 'PPT':
                //kembali ke file Program Training
                $_GET['proses'] = 'listofapprovement';
                include "sdm_slave_programtraining.php";
                break;
            case 'PPTdetail':
                //kembali ke file Program Training
                $_GET['proses'] = 'getdetail';
                include "sdm_slave_programtraining.php";
                break;

            case 'SPL':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('SPL');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('SPL');

                $str = "select a.*, b.tanggal , b.kodeorg from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_splemburht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='SPL' and a.status='0' and karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kodeorg = substr($bar['kodeorg'], 0, 4);
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                    $statussebelum = 0;
                    if ($bar['level'] > 1) {
                        @$levelsblm = $bar['level'] - 1;
                        $str1 = "select status from " . $dbname . ".approval where jenispersetujuan='SPL' and notransaksi='" . $bar['notransaksi'] . "' and level='" . $levelsblm . "'";
                        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_ASSOC);
                        $bar1 = $res1->fetch();
                        $statussebelum = $bar1['status'];
                    } else {
                        $statussebelum = 1;
                    }

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=center>" . $bar['notransaksi'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $optNmOrg[$kodeorg] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('sdm_splemburht','" . $bar['kodeorg'] . "," . tanggalnormal($bar['tanggal']) . "," . $bar['notransaksi'] . "','','sdm_slave_spllemburPdf',event);\">
								</td>
								";

                    // <td align=center>
                    //     <img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail' onclick=\"editfromapproval('".$bar['kodeorg']."','".tanggalnormal($bar['tanggal'])."');\" >
                    // </td>

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'SPL');
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0) && $statussebelum == 1) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">Setujui Semua</button>
										<button class=mybutton onclick=\"editfromapproval('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "','" . $level . "');\" >" . $_SESSION['lang']['ditolak_some'] . "</button>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">Tolak Semua</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'SPL');

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'JM':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['nojurnal'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $str = "select a.*, a.tanggal, SUBSTRING_INDEX(SUBSTRING_INDEX(a.notransaksi, '/', 2), '/', -1) as kodeorg from " . $dbname . ".approval a
						left join " . $dbname . ".keu_jurnalht b on a.notransaksi = b.nojurnal
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"detailPDFv2('" . $bar['notransaksi'] . "', '" . $bar['kodeorg'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<!-- <button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button> -->

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PKSMAINTENANCE':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>" . $_SESSION['lang']['station'] . "</td>
							<td align=center>" . $_SESSION['lang']['mesin'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('PKSMAINTENANCE');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('PKSMAINTENANCE');

                #=arr pabrik
                $str = "select * from " . $dbname . ".organisasi where tipe in ('PABRIK','STATION','STENGINE')";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $nmorg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
                }

                $str = "select a.*,b.tanggal,b.pabrik,b.statasiun,b.mesin  from " . $dbname . ".approval a
						left join " . $dbname . ".pabrik_rawatmesinht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                // echo $str;
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['pabrik'] . "</td>
								<td align=left>" . $nmorg[$bar['statasiun']] . "</td>
								<td align=left>" . $nmorg[$bar['mesin']] . "</td>
								<td align=center>
									<img src=images/zoom.png class=zImgBtn onclick=showimages('listfileupload','" . $bar['notransaksi'] . "','servicepabrik') title=view>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print'
									onclick=\"masterPDF('pabrik_rawatmesinht','" . $bar['notransaksi'] . "','','pabrik_slave_perbaikan_pdf',event)\">
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['koreksi'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";

                break;

            case 'PKSCUCITANGKI':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>" . $_SESSION['lang']['tangki'] . "</td>
							<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('PKSCUCITANGKI');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('PKSCUCITANGKI');

                $str = "select a.*, b.tanggal,b.keterangan,b.kodeorg,b.kodetangki from " . $dbname . ".approval a
						left join " . $dbname . ".pabrik_pembersihantangki b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $nmTangki = makeOption($dbname, 'pabrik_5tangki', 'kodetangki,keterangan', "kodetangki='" . $bar['kodetangki'] . "' and kodeorg='" . $bar['kodeorg'] . "'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=left>" . $nmTangki[$bar['kodetangki']] . "</td>
								<td align=left>" . $bar['keterangan'] . "</td>

								<td align=center>
									<img src=images/zoom.png class=zImgBtn onclick=showimages('listfileupload','" . $bar['notransaksi'] . "','cucitangki') title=view>
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['koreksi'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";

                break;

            case 'PKSBACUCITANGKI':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['unit'] . "</td>
							<td align=center>" . $_SESSION['lang']['tangki'] . "</td>
							<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center>" . $_SESSION['lang']['dokumen'] . "</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('PKSBACUCITANGKI');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('PKSBACUCITANGKI');

                $str = "select a.*, b.tanggal,b.keterangan,b.kodeorg,b.kodetangki from " . $dbname . ".approval a
						left join " . $dbname . ".pabrik_pembersihantangki b on a.notransaksi = b.noba
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and karyawanid='" . $karyawanid . "' order by b.tanggal desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $nmTangki = makeOption($dbname, 'pabrik_5tangki', 'kodetangki,keterangan', "kodetangki='" . $bar['kodetangki'] . "' and kodeorg='" . $bar['kodeorg'] . "'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['kodeorg'] . "</td>
								<td align=left>" . $nmTangki[$bar['kodetangki']] . "</td>
								<td align=left>" . $bar['keterangan'] . "</td>

								<td align=center>
									<img src=images/zoom.png class=zImgBtn onclick=showimages('listfileupload','" . $bar['notransaksi'] . "','cucitangki') title=view>
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['koreksi'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";

                break;

            case 'PJDINAS':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('PJDINAS');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('PJDINAS');
                #ambil  id karyawan
                $sApp = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='PJDINAS'";
                $rApp = fetchData($sApp);
                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $levelsblm = $bar['level'] - 1;

                $str = "select a.*, b.karyawanid as pengaju from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_pjdinasht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggalbuat desc";

                //exit($str);
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $karyawanid = $bar['pengaju'];
                    $whr = " karyawanid='" . $bar['pengaju'] . "'";
                    // if ($levelap!=''){
                    //     $karyawanid=$karyawanid;
                    //     $whr=" karyawanid='".$karyawanid."'";
                    // }
                    $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $optNm[$karyawanid] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewPJD('" . $bar['notransaksi'] . "',event);\">&nbsp;";
                    $tab .= "<img src=images/zoom.png class=resicon  title='" . $_SESSION['lang']['view'] . "' onclick=\"previewUMPJD('" . $bar['notransaksi'] . "',event);\">";
                    $tab .= "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'PJDINAS');
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        } else {
                            /*if($levelap!=''){
                        $level = $levelap;
                        $showaction = 1;
                        }
                         */
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td colspan=2>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'PJDINAS');

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            if ($levelap == $i) {
                                $tab .= "<td style='text-align:center'>Direksi</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }

                ##Pengecekan jika ada direksi yg menjadi penyetuju di level bukan global
                if ($rApp[0]['nilai'] == $karyawanid) {
                    $rCek = fetchData($str);
                    foreach ($rCek as $key => $val) {
                        $optBag = makeOption($dbname, "datakaryawan", "karyawanid,bagian", "karyawanid='" . $val['pengaju'] . "'");
                        if ($optBag[$val['pengaju']] == 'HRC') {
                            $levelap = "";
                        }
                    }
                }
                if ($levelap != '') {
                    $str = "select a.*, b.karyawanid as pengaju  from " . $dbname . ".approval a left join " . $dbname . ".sdm_pjdinasht b on a.notransaksi = b.notransaksi
								where a.jenispersetujuan='" . $proses . "' and a.status='1' and a.level='" . $levelsblm . "' and b.karyawanid!='" . $karyawanid . "' and b.statuspersetujuan='0' order by b.tanggalbuat desc";

                    // exit($str);
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while ($bar = $res->fetch()) {
                        $karyawanid = $bar['pengaju'];
                        $whr = " karyawanid='" . $bar['pengaju'] . "'";
                        // if ($levelap!=''){
                        //     $karyawanid=$karyawanid;
                        //     $whr=" karyawanid='".$karyawanid."'";
                        // }
                        $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

                        $no++;
                        $tab .= "<tr class=rowcontent>
									<td align=center>" . $no . "</td>
									<td align=left>" . $bar['notransaksi'] . "</td>
									<td align=left>" . $optNm[$karyawanid] . "</td>
									<td align=center>
										<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewPJD('" . $bar['notransaksi'] . "',event);\">&nbsp;";
                        $tab .= "<img src=images/zoom.png class=resicon  title='" . $_SESSION['lang']['view'] . "' onclick=\"previewUMPJD('" . $bar['notransaksi'] . "',event);\">";
                        $tab .= "</td>";

                        $showaction = 0;
                        $level = 1;
                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], 'PJDINAS');
                            if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                                $level = $arrDetail['level'];
                                $showaction = 1;
                            } else {
                                if ($levelap != '') {
                                    $level = $levelap;
                                    $showaction = 1;
                                }
                            }
                        }

                        if ($showaction == 1) {
                            $tab .= "<td style='text-align:center'>
											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

											<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
										</td>";
                        } else {
                            $tab .= "<td colspan=2>&nbsp;</td>";
                        }

                        for ($i = 1; $i <= $countApp; $i++) {
                            @$arrDetail = detailApprove($i, $bar['notransaksi'], 'PJDINAS');

                            if ($arrDetail['nama'] != '') {
                                // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                if ($levelap == $i) {
                                    $tab .= "<td style='text-align:center'>Direksi</td>";
                                } else {
                                    $tab .= "<td style='text-align:center'>-</td>";
                                }
                            }
                        }
                        $tab .= "</tr>";
                    }
                }
                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PJDTAMU':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";
                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $str = "select a.*, b.namatamu as pengaju from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_pjdinasht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggalbuat desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $bar['pengaju'] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewPJD('" . $bar['notransaksi'] . "',event);\">&nbsp;";
                    $tab .= "<img src=images/zoom.png class=resicon  title='" . $_SESSION['lang']['view'] . "' onclick=\"previewUMPJD('" . $bar['notransaksi'] . "',event);\">";
                    $tab .= "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $karyawanid);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td >&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($levelap == $i) {
                            $tab .= "<td style='text-align:center'>Direksi</td>";
                        } else {
                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'SOP':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align='center'>Verification</td>";
                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $str = "select * from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_sopht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggalefektif desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses, $karyawanid);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td >&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($levelap == $i) {
                            $tab .= "<td style='text-align:center'>Direksi</td>";
                        } else {
                            if ($arrDetail['nama'] != '') {
                                $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PROJ':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align='center'>Verification</td>";

                for ($i = 1; $i <= $countAppJ; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
					</thead>
					<tbody>";

                $strx = "select level from " . $dbname . ".approval where jenispersetujuan='" . $proses . "' and karyawanid!='0000000000'";
                $res = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $barx = $res->fetch();
                $levelap = $barx['level'];

                $strz = "select * from " . $dbname . ".approval a
						left join " . $dbname . ".project b on a.notransaksi = b.kode
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'";
                $res = $owlPDO->query($strz) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($barz = $res->fetch()) {
                    $notransproj = $barz['notransaksi'];
                }

                // $qstr="select level,status from ".$dbname.".approval where  notransaksi='".$notransproj."' and jenispersetujuan='".$proses."' and karyawanid='".$karyawanid."'";
                // $res=$owlPDO->query($qstr) or die(print " Gagal: ".PDOException::getMessage());
                // $res->setFetchMode(PDO::FETCH_ASSOC);
                // $qbar=$res->fetch();
                // $ceklvlap=$qbar['level'];
                // $lvlsebelum= $ceklvlap-1;

                $str = "select * from " . $dbname . ".approval a
						left join " . $dbname . ".project b on a.notransaksi = b.kode
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $lvlsebelum = $bar['level'] - 1;

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>";

                    ##CEK PERSETUJUAN SEBELUMNYA
                    $stra = "select level,status from " . $dbname . ".approval where  notransaksi ='" . $bar['notransaksi'] . "' and jenispersetujuan='" . $proses . "'  and level='" . $lvlsebelum . "' ";
                    $resa = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
                    $resa->setFetchMode(PDO::FETCH_ASSOC);
                    $bara = $resa->fetch();
                    $stsebelum = $bara['status'];

                    if ($stsebelum != 0 || $bar['level'] == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countAppJ; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        // if($levelap==$i){
                        //     // $tab.="<td style='text-align:center'>Direksi</td>";
                        // }else{
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
												<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
												Status : " . $arrDetail['namastatus'] . "<br>
												" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
											</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                        // }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PJDINASNS':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and karyawanid='" . $karyawanid . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $levelsblm = $bar['level'] - 1;
                if ($levelap > 1) {

                    $lstTrans = array();
                    $str = "select karyawanid,notransaksi,level,count(notransaksi) as jmlhnotrans,status from " . $dbname . ".approval where  jenispersetujuan='" . $proses . "' and status='1' and karyawanid='" . $karyawanid . "' group by status,level,notransaksi";
                    //echo $str;
                    $res = fetchData($str);
                    foreach ($res as $row => $data) {
                        if ($data['karyawanid'] == $karyawanid) {
                            if ($data['status'] == '1') {

                                $lstTrans[$data['notransaksi']] = $data['notransaksi'];
                            }
                        }
                    }
                }

                $str = "select a.*, b.karyawanid from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_pjdinasht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggalbuat desc";

                if ($levelap != '') {
                    $str = "select a.*, b.karyawanid from " . $dbname . ".approval a left join " . $dbname . ".sdm_pjdinasht b on a.notransaksi = b.notransaksi
							where a.jenispersetujuan='" . $proses . "' and a.status='1' and a.level='" . $levelsblm . "' and b.karyawanid!='" . $karyawanid . "' and b.statuspersetujuan='0' order by b.tanggalbuat desc";
                }

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    if (count($lstTrans[$bar['notransaksi']]) != 0) {
                        continue;
                    }
                    $karyawanid = $bar['karyawanid'];
                    $whr = " karyawanid='" . $bar['karyawanid'] . "'";
                    // if ($levelap!=''){
                    //     $karyawanid=$karyawanid;
                    //     $whr=" karyawanid='".$karyawanid."'";
                    // }
                    $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $optNm[$karyawanid] . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewPJD('" . $bar['notransaksi'] . "',event);\">
									<img src=images/zoom.png class=resicon  title='" . $_SESSION['lang']['view'] . "' onclick=\"previewUMPJD('" . $bar['notransaksi'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        } else {

                            if ($levelap != '') {
                                $level = $levelap;
                                $showaction = 1;
                            }
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            if ($levelap == $i) {
                                $tab .= "<td style='text-align:center'>Persetujuan" . $i . "</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'CU':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval('CU');
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval('CU');

                $str = "select a.*, b.lastupdate as tanggal , b.untukunit as kodeorg from " . $dbname . ".approval a
						left join " . $dbname . ".log_permintaanht b on a.notransaksi = b.notransaksi
						where a.jenispersetujuan='CU' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.lastupdate desc"; //exit('error'.$str);
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kodeorg = $bar['kodeorg'];
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                    $levelsblm = $bar['level'] - 1;
                    $level = $bar['level'];
                    // exit('error'.$level);
                    if ($levelsblm != '') {
                        $strv = "select * from " . $dbname . ".approval where notransaksi='" . $bar['notransaksi'] . "' and level='" . $levelsblm . "' and status='1'";
                        $resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
                        $resv->setFetchMode(PDO::FETCH_ASSOC);
                        $barv = $resv->fetch();

                        $setuju = '';
                        $tolak = '';
                        //exit('error'.$barv['notransaksi']);
                        if ($barv['notransaksi'] == $bar['notransaksi']) {
                            $setuju = "<button class=mybutton onclick=\"formalasan('CU','CU','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>";

                            $tolak = "<button class=mybutton onclick=\"formalasan('CU','CU','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>";
                        } else {
                            $setuju = "<button class=mybutton onclick=\"prcek_status_pp('0')\">" . $_SESSION['lang']['approve'] . "</button>";

                            $tolak = "<button class=mybutton onclick=\"prcek_status_pp('0')\">" . $_SESSION['lang']['ditolak'] . "</button>";
                        }
                    } else {
                        $setuju = "<button class=mybutton onclick=\"formalasan('CU','CU','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>";

                        $tolak = "<button class=mybutton onclick=\"formalasan('CU','CU','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>";
                    }

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=left>" . $kodeorg . " - " . $optNmOrg[$kodeorg] . "</td>
								<td align=center>
									<img src=images/skyblue/zoom.png class=resicon width='30' height='30' title='view' onclick=\"viewdetailcu('" . $bar['notransaksi'] . "',event);\">
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'CU');
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										" . $setuju . "
										" . $tolak . "
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], 'CU');

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'BKCK':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $str = "select * from " . $dbname . ".approval a
						where a.jenispersetujuan='BKCK' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kodeorg = substr($bar['notransaksi'], 0, 3);
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $optNmOrg[$kodeorg] . "</td>
								<td align=center>
									<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar['notransaksi'] . "',event,'2')\">
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'DISPO':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
							<td align=center>" . $_SESSION['lang']['kodeorganisasi'] . "</td>
							<td align=center>" . $_SESSION['lang']['namaasset'] . "</td>
							<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
							<td align=center>" . $_SESSION['lang']['catatan'] . "</td>
							<td align=center>Verification</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $arrstatus = array('1' => 'Disposal', '2' => 'Write-off');
                $str = "select * from " . $dbname . ".approval a left join " . $dbname . ".keu_disposalasset b on a.notransaksi=b.notransaksi
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by a.notransaksi desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kodeorg = substr($bar['notransaksi'], 0, 4);
                    $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");
                    $whras = "kodeasset='" . $bar['kodeasset'] . "'";
                    $nmasset = makeOption($dbname, 'sdm_daftarasset', 'kodeasset,namasset', $whras);

                    $strket = "select * from " . $dbname . ".keu_5jenisdisposalasset where id='" . $bar['jenisket'] . "'";
                    $resket = $owlPDO->query($strket) or die(print " Gagal: " . PDOException::getMessage());
                    $resket->setFetchMode(PDO::FETCH_ASSOC);
                    $barket = $resket->fetch();

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $optNmOrg[$kodeorg] . "</td>
								<td align=left>" . $nmasset[$bar['kodeasset']] . "</td>
								<td align=left>" . $barket['keterangan'] . " (" . $arrstatus[$barket['jenis']] . ")</td>
								<td align=left>" . $bar['catatan'] . "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'SP':
                $_GET['PERSETUJUAN'] = 'TRUE';
                include "vhc_slave_approval_sp.php";
                break;
            case 'SP_TIDAK_PAKAI':
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<td align=center>No.</td>
							<td align=center>" . $_SESSION['lang']['nopengajuan'] . "</td>
							<td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
							<td align=center>Detail</td>
							<td align='center'>Verification</td>";

                $countApp = getCountApproval($proses);
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $countApp = getCountApproval($proses);

                $str = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $levelsblm = $bar['level'] - 1;
                if ($levelap > 1) {

                    $lstTrans = array();
                    $str = "select karyawanid,notransaksi,level,count(notransaksi) as jmlhnotrans,status from " . $dbname . ".approval where  jenispersetujuan='" . $proses . "' and status='1' and karyawanid='" . $karyawanid . "' group by status,level,notransaksi";
                    //echo $str;
                    $res = fetchData($str);
                    foreach ($res as $row => $data) {
                        if ($data['karyawanid'] == $karyawanid) {
                            if ($data['status'] == '1') {

                                $lstTrans[$data['notransaksi']] = $data['notransaksi'];
                            }
                        }
                    }
                }

                $str = "select a.*, b.karyawanid from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_pengajuanspht b on a.notransaksi = b.nopengajuan
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $karyawanid . "' order by b.tanggalpengajuan desc";

                if ($levelap != '') {
                    $str = "select a.*, b.karyawanid from " . $dbname . ".approval a left join " . $dbname . ".sdm_pengajuanspht b on a.notransaksi = b.nopengajuan
							where a.jenispersetujuan='" . $proses . "' and a.status='1' and a.level='" . $levelsblm . "' and b.karyawanid!='" . $karyawanid . "' and b.statuspersetujuan='0' order by b.tanggalpengajuan desc";
                }

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    if (count($lstTrans[$bar['notransaksi']]) != 0) {
                        continue;
                    }
                    $karyawanid = $bar['karyawanid'];
                    $whr = " karyawanid='" . $bar['karyawanid'] . "'";
                    if ($levelap != '') {
                        $karyawanid = $karyawanid;
                        $whr = " karyawanid='" . $karyawanid . "'";
                    }
                    $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . $bar['notransaksi'] . "</td>
								<td align=left>" . $optNm[$karyawanid] . "</td>
								<td align=center>
									<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetailsp('" . $bar['notransaksi'] . "',event);\" >
								</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['karyawanid'] == $karyawanid && ($arrDetail['status'] == '' || $arrDetail['status'] == 0)) {
                            $level = $arrDetail['level'];
                            $showaction = 1;
                        } else {

                            if ($levelap != '') {
                                $level = $levelap;
                                $showaction = 1;
                            }
                        }
                    }

                    if ($showaction == 1) {
                        $tab .= "<td style='text-align:center'>
										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>

										<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
									</td>";
                    } else {
                        $tab .= "<td>&nbsp;</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                        if ($arrDetail['nama'] != '') {
                            // $tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
                            $tab .= "<td style='vertical-align:top;text-align:center'>
											<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
											Status : " . $arrDetail['namastatus'] . "<br>
											" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
										</td>";
                        } else {
                            if ($levelap == $i) {
                                $tab .= "<td style='text-align:center'>Persetujuan" . $i . "</td>";
                            } else {
                                $tab .= "<td style='text-align:center'>-</td>";
                            }
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;
            case 'CVMM':
                $_SESSION['approval']['cvmm'] = [];
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<th align=center>No.</th>
							<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
							<th align=center>" . $_SESSION['lang']['unit'] . "</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['view'] . "</th>
							<th align='center'>Verification</th>
						</tr>
						</thead>
						<tbody>";

                $str = "select * from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_corevalueandmanmanagement b on a.notransaksi = b.id
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $_SESSION['standard']['userid'] . "'
						order by b.id desc";
                // echo $str;exit();
                $res = fetchData($str);
                foreach ($res as $bar) {
                    $_SESSION['approval']['cvmm'][] = array(
                        'notransaksi' => $bar['notransaksi'],
                        'karyawanid' => $bar['karyawanid'],
                        'jenispersetujuan' => $bar['jenispersetujuan'],
                    );

                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . getKary($bar['karyawanid']) . "</td>
								<td align=left>" . getNamaOrg(getKary($bar['karyawanid'], 'lokasitugas')) . "</td>
								<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdfcvmm('" . $bar['id'] . "');\">

									<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detailcvmm('" . $bar['id'] . "');\">
								</td>";

                    $tab .= "<td style='text-align:center'>
									<button class=mybutton style=color:green;border-color:yellow;><a href=\"javascript:do_load('sdm_coreman')\" title='Click untuk melakukan verifikasi di menu HCM - Transaksi - Cove Value'>Verifikasi</a></button>
									<button class=mybutton style=color:green;border-color:green; onclick=\"formalasankpi('" . $proses . "','" . $bar['notransaksi'] . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton style=color:#fca219;border-color:#fca219; onclick=\"formalasankpi('" . $proses . "','" . $bar['notransaksi'] . "','2',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
								</td>";
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'PAS':
                $_SESSION['approval']['pas'] = [];
                $tab .= "<fieldset>
					<legend>" . $_SESSION['lang']['detail'] . "</legend>
					<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
							<th align=center>No.</th>
							<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
							<th align=center>" . $_SESSION['lang']['unit'] . "</th>
							<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
							<th align=center>" . $_SESSION['lang']['view'] . "</th>
							<th align='center'>Verification</th>
						</tr>
						</thead>
						<tbody>";

                $str = "select * from " . $dbname . ".approval a
						left join " . $dbname . ".sdm_pas b on a.notransaksi = b.id
						where a.jenispersetujuan='" . $proses . "' and a.status='0' and a.karyawanid='" . $_SESSION['standard']['userid'] . "'
						order by b.id desc";
                // echo $str;exit();
                $res = fetchData($str);
                foreach ($res as $bar) {
                    $_SESSION['approval']['pas'][] = array(
                        'notransaksi' => $bar['notransaksi'],
                        'karyawanid' => $bar['karyawanid'],
                        'jenispersetujuan' => $bar['jenispersetujuan'],
                    );
                    $no++;
                    $tab .= "<tr class=rowcontent>
								<td align=center>" . $no . "</td>
								<td align=left>" . getKary($bar['karyawanid']) . "</td>
								<td align=left>" . getNamaOrg(getKary($bar['karyawanid'], 'lokasitugas')) . "</td>
								<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
								<td align=center>
									<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdfpas('" . $bar['id'] . "');\">

									<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detailpas('" . $bar['id'] . "');\">
								</td>";

                    $tab .= "<td style='text-align:center'>
									<button class=mybutton style=color:green;border-color:yellow;><a href=\"javascript:do_load('sdm_pas')\" title='Click untuk melakukan verifikasi di menu HCM - Transaksi - Performance Appraisal Summary (PAS)	'>Verifikasi</a></button>

									<button class=mybutton style=color:green;border-color:green; onclick=\"formalasankpi('" . $proses . "','" . $bar['notransaksi'] . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton style=color:#fca219;border-color:#fca219; onclick=\"formalasankpi('" . $proses . "','" . $bar['notransaksi'] . "','2',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
								</td>";
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						<tfoot>
						</tfoot>
					</table>
				</fieldset>";
                break;

            case 'HFTBS':
                $countApp = getCountApproval('HFTBS');

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
						<td align=center>No.</td>
						<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center>" . $_SESSION['lang']['unit'] . "</td>
						<td align=center>" . $_SESSION['lang']['tipe'] . " TBS</td>
						<td align=center>" . $_SESSION['lang']['supplier'] . "</td>
						<td align=center>" . $_SESSION['lang']['harga'] . "</td>
						<td align=center>" . $_SESSION['lang']['rekening'] . "<br>" . $_SESSION['lang']['atasnama'] . "</td>
						<td align=center>" . $_SESSION['lang']['tanggalberlaku'] . "</td>
						<td align=center>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
						<td align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
                $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
                $nmRek = makeOption($dbname, 'log_5rekbank', 'rekening,an');
                $str = "SELECT a.*, b.tanggalpengajuan, b.kodeunit, b.tipetbs, b.kodesupplier, b.rpkg, b.rekening, b.tanggaldari
						FROM " . $dbname . ".approval a LEFT JOIN " . $dbname . ".pmn_5feetbs b ON a.notransaksi = b.notransaksi
						WHERE a.jenispersetujuan = '" . $proses . "' and a.status = '0' and a.karyawanid = '" . $_SESSION['standard']['userid'] . "'
						ORDER BY b.tanggalpengajuan DESC";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
							<td align=center>" . $no . "</td>
							<td align=left>" . $bar['notransaksi'] . "</td>
							<td align=left>" . $bar['kodeunit'] . "</td>
							<td align=left>" . $bar['tipetbs'] . "</td>
							<td align=left>" . $bar['kodesupplier'] . " - " . $nmSup[$bar['kodesupplier']] . "</td>
							<td align=right>" . number_format($bar['rpkg']) . "</td>
							<td align=center>" . $bar['rekening'] . "<br>a/n " . $nmRek[$bar['rekening']] . "</td>
							<td align=center>" . tanggalnormal($bar['tanggaldari']) . "</td>
							<td align=center>" . tanggalnormal($bar['tanggalpengajuan']) . "</td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid']) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
								</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
										<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
										Status : " . $arrDetail['namastatus'] . "<br>
										" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
									</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						</table>
						</fieldset>";
                break;

            case 'FTBS':
                $countApp = getCountApproval('FTBS');

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
						<td align=center>No.</td>
						<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center>" . $_SESSION['lang']['unit'] . "</td>
						<td align=center>" . $_SESSION['lang']['tipe'] . " TBS</td>
						<td align=center>" . $_SESSION['lang']['supplier'] . "</td>
						<td align=center>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
						<td align=center>" . $_SESSION['lang']['file'] . "</td>
						<td align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . ' ' . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
                $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
                // kamus
                $str = "SELECT * from " . $dbname . ".pmn_feetbs where 1";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kamusfeetbs[$bar['notransaksi']]['unit'] = $bar['unit'];
                    $kamusfeetbs[$bar['notransaksi']]['postingtime'] = $bar['postingtime'];
                    $kamusfeetbs[$bar['notransaksi']]['tipetbs'] = $bar['tipetbs'];
                    $kamusfeetbs[$bar['notransaksi']]['kodesupplier'] = $bar['kodesupplier'];
                }

                // echo "<pre>";
                // print_r($kamusfeetbs);
                // echo "</pre>";

                $str = "SELECT a.*
						FROM " . $dbname . ".approval a WHERE a.jenispersetujuan = '" . $proses . "' and a.status = '0' and a.karyawanid = '" . $_SESSION['standard']['userid'] . "' ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $tab .= "<tr class=rowcontent>
							<td align=center>" . $no . "</td>
							<td align=left>" . $bar['notransaksi'] . "</td>
							<td align=left>" . $kamusfeetbs[$bar['notransaksi']]['unit'] . "</td>
							<td align=left>" . $kamusfeetbs[$bar['notransaksi']]['tipetbs'] . "</td>
							<td align=left>" . $kamusfeetbs[$bar['notransaksi']]['kodesupplier'] . " - " . $nmSup[$kamusfeetbs[$bar['notransaksi']]['kodesupplier']] . "</td>
							<td align=center>" . tanggalnormal(substr($kamusfeetbs[$bar['notransaksi']]['postingtime'], 0, 10)) . "</td>
							<td align=center><img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdfFTBS('" . $bar['notransaksi'] . "');\"></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid']) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
								</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
										<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
										Status : " . $arrDetail['namastatus'] . "<br>
										" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
									</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						</table>
						</fieldset>";
                break;

            case 'SKAV':
                $countApp = getCountApproval('SKAV');

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
						<td align=center>No.</td>
						<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center>" . $_SESSION['lang']['unit'] . "</td>
						<td align=center>Nama KUD Organisasi</td>
						<td align=center>Hamparan</td>
						<td align=center>Kavling</td>
						<td align=center>" . $_SESSION['lang']['nama'] . "</td>
						<td align=center>" . $_SESSION['lang']['status'] . "</td>
						<td align=center>Lampiran</td>
						<td align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . ' ' . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
                $nmKud = makeOption($dbname, 'kebun_5namakud', 'afdeling,kodesupplier');
                $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
                // kamus
                $str = "SELECT * from " . $dbname . ".kebun_5kavling where 1";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kamusexistingkav[$bar['id']]['kodeunit'] = $bar['kodeunit'];
                    $kamusexistingkav[$bar['id']]['afdeling'] = $bar['afdeling'];
                    $kamusexistingkav[$bar['id']]['no_hamp'] = $bar['no_hamp'];
                    $kamusexistingkav[$bar['id']]['no_kavl'] = $bar['no_kavl'];
                    $kamusexistingkav[$bar['id']]['nama'] = $bar['nama'];
                    $kamusexistingkav[$bar['id']]['aktif'] = $bar['aktif'];
                }

                $str = "SELECT * from " . $dbname . ".kebun_5kavling_update where status = '9'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kamusproposalkav[$bar['notransaksi']]['id'] = $bar['id'];
                    $kamusproposalkav[$bar['notransaksi']]['no_hamp'] = $bar['no_hamp'];
                    $kamusproposalkav[$bar['notransaksi']]['no_kavl'] = $bar['no_kavl'];
                    $kamusproposalkav[$bar['notransaksi']]['nama'] = $bar['nama'];
                    $kamusproposalkav[$bar['notransaksi']]['aktif'] = $bar['aktif'];
                }

                $str = "SELECT * from " . $dbname . ".listfileupload where kriteriaefil = 'SKAV'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $kamusefil[$bar['notransaksi']]['namafile'] = $bar['namafile'];
                    $kamusefil[$bar['notransaksi']]['icon'] = seticonfile($bar['formaticon']);
                }

                $pathx = "fileupload/kavling/";

                $arrsts = array('1' => '<font color=green>Aktif</font>', '0' => '<font color=red>Non Aktif</font>');

                $str = "SELECT a.* FROM " . $dbname . ".approval a WHERE a.jenispersetujuan = '" . $proses . "' and a.status = '0' and a.karyawanid = '" . $_SESSION['standard']['userid'] . "' ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no++;
                    $aydi = $kamusproposalkav[$bar['notransaksi']]['id'];
                    $tab .= "<tr class=rowcontent>
							<td align=center>" . $no . "</td>
							<td align=left>" . $bar['notransaksi'] . "</td>
							<td align=left>" . $kamusexistingkav[$aydi]['kodeunit'] . "</td>
							<td align=left>" . $nmSup[$nmKud[$kamusexistingkav[$aydi]['afdeling']]] . "</td>
							<td align=left>" . $kamusexistingkav[$aydi]['no_hamp'] . " -> " . $kamusproposalkav[$bar['notransaksi']]['no_hamp'] . "</td>
							<td align=left>" . $kamusexistingkav[$aydi]['no_kavl'] . " -> " . $kamusproposalkav[$bar['notransaksi']]['no_kavl'] . "</td>
							<td align=left>" . $kamusexistingkav[$aydi]['nama'] . " -> " . $kamusproposalkav[$bar['notransaksi']]['nama'] . "</td>
							<td align=left>" . $arrsts[$kamusexistingkav[$aydi]['aktif']] . " -> " . $arrsts[$kamusproposalkav[$bar['notransaksi']]['aktif']] . "</td>
							<td align=center><a href='" . $pathx . $kamusefil[$bar['notransaksi']]['namafile'] . "' download><img src=" . $kamusefil[$bar['notransaksi']]['icon'] . " class=resicon></a></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid']) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
								</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
										<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
										Status : " . $arrDetail['namastatus'] . "<br>
										" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
									</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						</table>
						</fieldset>";
                break;

            case 'GDOKFIN':
                $countApp = getCountApproval('GDOKFIN');

                $tab .= "<fieldset>
						<legend>" . $_SESSION['lang']['detail'] . "</legend>
						<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
						<thead>
						<tr class=rowheader>
						<td align=center>No.</td>
						<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td align=center>" . $_SESSION['lang']['nodokumen'] . " Lama</td>
						<td align=center>" . $_SESSION['lang']['nodokumen'] . " Baru</td>
						<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
						<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
						<td align=center>" . $_SESSION['lang']['file'] . "</td>
						<td align='center'>Verification</td>";
                for ($i = 1; $i <= $countApp; $i++) {
                    $tab .= "<td align=center>" . $_SESSION['lang']['persetujuan'] . ' ' . $i . "</td>";
                }
                $tab .= "</tr>
						</thead>
						<tbody>";

                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

                $str = "SELECT a.*, b.* FROM " . $dbname . ".approval a JOIN " . $dbname . ".keu_gantidokumen b ON a.notransaksi=b.notransaksi
						WHERE a.jenispersetujuan = '" . $proses . "' and a.status = '0' and a.karyawanid = '" . $_SESSION['standard']['userid'] . "' ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no += 1;
                    $tab .= "<tr class=rowcontent>
							<td align=center>" . $no . "</td>
							<td align=left>" . $bar['notransaksi'] . "</td>
							<td align=left>" . $bar['nodokumenlama'] . "</td>
							<td align=left>" . $bar['nodokumenbaru'] . "</td>
							<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>
							<td align=left>" . nl2br($bar['keterangan']) . "</td>
							<td align=left><img src='images/zoom.png' class='resicon' title='Detail' onclick=\"viewdetailgantidokumen('" . $bar['notransaksi'] . "')\";></td>";

                    $showaction = 0;
                    $level = 1;
                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['status'] == '' || $arrDetail['status'] == 0) {
                            $showaction = $showaction + 1;
                        }
                        if ($arrDetail['karyawanid'] == $_SESSION['standard']['userid']) {
                            $level = $arrDetail['level'];
                            break;
                        }
                    }

                    if ($showaction != $level || $level == 1) {
                        $tab .= "<td style='text-align:center'>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','1',event)\">" . $_SESSION['lang']['approve'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','3',event)\">" . $_SESSION['lang']['koreksi'] . "</button>
									<button class=mybutton onclick=\"formalasan('" . $proses . "','" . $proses . "','" . $bar['notransaksi'] . "','" . $level . "','2',event)\">" . $_SESSION['lang']['ditolak'] . "</button>
								</td>";
                    } else {
                        $tab .= "<td style='color:red'>Menunggu Persetujuan Sebelumnya</td>";
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);
                        if ($arrDetail['nama'] != '') {
                            $tab .= "<td style='vertical-align:top;text-align:center'>
										<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
										Status : " . $arrDetail['namastatus'] . "<br>
										" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "
									</td>";
                        } else {
                            $tab .= "<td style='text-align:center'>-</td>";
                        }
                    }
                    $tab .= "</tr>";
                }

                $tab .= "</tbody>
						</table>
						</fieldset>";
                break;
        }

        echo $tab;
        break;

    case 'formalasan':
        switch ($proses) {
            case 'PHP':
                $dataphp = '';
                echo "
                <div id=test style=display:block>
                <fieldset>
                <legend>Notransaksi : &nbsp;<input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend><br><br>
                <table cellspacing=1 cellpadding=3 border=0>";
                if ($hasilpersetujuan == 1) {
                    echo "
                    <tr class=rowheader style='background-color:#275370;color:white'>
                    <td align=center>" . $_SESSION['lang']['supplier'] . "</td>
                    <td align=center>Pilih(&check;)</td>
                    </tr>";
                    $str = selectQuery($dbname, 'lgl_penawaranharga', '*', "notransaksi='" . $notransaksi . "'");
                    $rst = fetchData($str);
                    foreach ($rst as $v) {
                        @$n++;
                        echo "
                        <tr class=rowcontent>
                            <td>" . getNamaSupplier($v['supplierid']) . "</td>
                            <td align=center><input type=radio name=cek id=pilihpemenang" . $n . "></td>
                        </tr>";
                    }
                    $dataphp = count($rst);
                }
                echo "
	            <tr>
	                <td colspan=3><b>Catatan : </b></td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3 style=\"width: 290px; height: 196px;\"></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=\"approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "','','" . $dataphp . "');\">" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'SERVICE':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3 style=\"width: 290px; height: 196px;\"></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=\"approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "');\">" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'LBR':
                $kodeapproval = $proses;
                $param['kodeorg'] = substr($param['kodeorg'], 0, 4);
                $kodeorg = $param['kodeorg'];
                $tab = "";
                $countApp = getCountApproval($proses, $param['kodeorg']);
                for ($i = $n; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $str = "select distinct a.karyawanid,namakaryawan,lokasitugas  from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "'";
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            case 'PNN':
                $kodeapproval = $proses;
                $param['kodeorg'] = substr($param['kodeorg'], 0, 4);
                $kodeorg = $param['kodeorg'];
                // exit("warning: ".$kodeorg);
                $tab = "";
                $countApp = getCountApproval($proses, $param['kodeorg']);
                for ($i = $n; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<input hidden id=kodeorgx value='" . $kodeorg . "'  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "'";
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<input hidden id=kodeorgx value='" . $kodeorg . "'  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            case 'PNNBR':
                $kodeapproval = $proses;
                $param['kodeorg'] = substr($param['kodeorg'], 0, 4);
                $kodeorg = $param['kodeorg'];
                // exit("warning: ".$kodeorg);
                $tab = "";
                $countApp = getCountApproval($proses, $param['kodeorg']);
                for ($i = $n; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<input hidden id=kodeorgx value='" . $kodeorg . "'  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "'";
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<input hidden id=kodeorgx value='" . $kodeorg . "'  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            //Umar
            case 'GRNINO':
            case 'GRNISO':
            case 'GRNICO':
                $kodeapproval = $proses;
                $param['kodeorg'] = substr($param['kodeorg'], 0, 4);
                $kodeorg = $param['kodeorg'];
                $tab = "";
                $pembuat = makeOption($dbname, 'log_noninventory', 'notransaksi,createdby', "notransaksi = '" . $notransaksi . "'");
                $departemen = getKary($pembuat[$notransaksi], 'bagian');

                $countApp = getCountApproval($proses, $param['kodeorg'], $departemen);
                for ($i = 0; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $pembuat = makeOption($dbname, 'log_noninventory', 'notransaksi,createdby', "notransaksi = '" . $notransaksi . "'");
                            $departemen = getKary($pembuat[$notransaksi], 'bagian');

                            $noapv = 0;
                            $str = "select count(*) as jumlah from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "' and departemen='" . $departemen . "'";
                            $res = fetchdata($str);
                            foreach ($res as $bar) {
                                $noapv = $bar['jumlah'];
                            }
                            if ($noapv > 0) {
                                $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
								where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "' and departemen='" . $departemen . "'";
                            } else {
                                $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
								where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "' and departemen=''";
                            }
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            //End Umar
            case 'EODBKM':
            case 'EODPNN':
            case 'EODRPNN':
            case 'EODTRK':
            case 'EODWS':
            case 'EODLOG':
            case 'EODKB':
            case 'EODKSR':
            case 'EODLBR':
            case 'EODGR':
            case 'EODSPB':
            case 'EODBKMPOST':
            case 'EODPNNPOST':
            case 'EODRPNNPOST':
            case 'EODSPBPOST':
            case 'EODTRKPOST':
            case 'EODWSPOST':
            case 'EODLOGPOST':
            case 'EODGRPOST':

                $kodeapproval = $proses;
                $kodeorg = $param['kodeorg'];
                $tab = "";
                $countApp = getCountApproval($proses, $param['kodeorg']);
                // exit("warning: ".$countApp);
                for ($i = $n; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "'";
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            case 'ATBS':
                $kodeapproval = $proses;
                $kodeorg = $param['kodeorg'];
                $tab = "";
                $countApp = getCountApproval($proses, $param['kodeorg']);
                for ($i = $n; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $notransaksi, $kodeapproval, $karyawanid);
                    if ($karyawanid == $arrDetail['karyawanid']) {
                        if ($i == $countApp) {
                            #Approval terakhir
                            $tab .= "<div id=approve>
								<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
										</td>
									</tr>
									<tr>
										<td colspan=3 align=center>
											<button id=Ajukan class=mybutton onclick=nextapproval_atbs('approved') >Approved</button>
										</td>
									</tr>
								</table>
							</div>";
                        } else {
                            $level = $i + 1;

                            $str = "select * from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
							where jenispersetujuan='" . $kodeapproval . "' and a.level='" . $level . "' and kodeunit='" . $kodeorg . "'";
                            $arrListApp = fetchData($str);
                            foreach ($arrListApp as $key => $val) {
                                if ($val['lokasitugas'] != '') {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . " [" . $val['lokasitugas'] . "]</option>";
                                } else {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['namakaryawan'] . "</option>";
                                }
                            }

                            $tab .= "<div id=test style=display:block>
								<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=" . $_POST['notransaksi'] . "  />
								<input hidden id=kolom value=" . $_POST['kolom'] . "  />
								<input hidden id=kodeapproval value=" . $kodeapproval . "  />
								<table cellspacing=1 border=0>
									<tr>
										<td colspan=3>Submit to the next approval :</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['namakaryawan'] . "</td>
										<td>:</td>
										<td valign=top>
											<select id=user_id name=user_id  style=\"width:150px;\">" . $optKry . "</select>
										</td>
									</tr>
									<tr>
										<td>" . $_SESSION['lang']['note'] . "</td>
										<td>:</td>
										<td>
											<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
										</td>
									</tr>
										<td colspan=2></td>
										<td>
											<button class=mybutton onclick=nextapproval_atbs() title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
										</td>
									</tr>
								</table>
							</div>";
                        }
                    }
                }

                echo $tab;
                break;
            case 'DISPO':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>";

                if ($hasilpersetujuan == 1 && $level == 4) {
                    echo "<tr>
		            	<td>Tanggal Disposal/Write of</td>
		            	<td> : </td>
						<td><input type=text class='myinputtext' id='tanggaldispo' onmousemove='setCalendar(this.id)' onkeypress='return false;'  style='width:150px' maxlength='10' /></td>
		            </tr>";
                }

                echo "<tr>
	                <td style='vertical-align:top'>Catatan</td>
	                <td style='vertical-align:top'> : </td>
	                <td style='vertical-align:top'><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=15 rows=2></textarea></td>
	            </tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

						<button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
					</td>
				</tr>
				</table>
	            </fieldset></div>";
                break;
            case 'PJDINAS':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'PJDTAMU':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'SOP':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'PROJ':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'PRM':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=\"approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "');\">" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'MTS':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=\"approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "');\">" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'DMS':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=\"approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "');\">" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'PJDINASNS':
                echo "
	            <div id=test style=display:block>
	            <fieldset>
	            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
	            <table cellspacing=1 border=0>
	            <tr>
	                <td colspan=3>Catatan : </td>
	            </tr>
	            <tr>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
	            </tr>
	            <td>
	            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

	            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
	            </td></tr></table>
	            </fieldset></div>";
                break;
            case 'CPX':
                $str = "select * from " . $dbname . ".log_formcapex_ht where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str);
                $unit = $res[0]['unit'];

                $countApp = getCountApproval($proses, $unit);

                echo "<div id=test style=display:block>
					<fieldset>
						<legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
						<table cellspacing=1 border=0>";

                if ($countApp == $level) {
                    //jenis biaya
                    $arjb = getEnum($dbname, 'log_formcapex_assetcode', 'jenis_biaya');
                    foreach ($arjb as $kei => $fal) {
                        if ((substr($unit, 2, 2) == 'HO') && ($fal != 3)) {
                            continue;
                        }

                        if ((substr($unit, 2, 2) != 'HO') && ($fal == 3)) {
                            continue;
                        }

                        if ($fal == 1) {
                            $capt = "Biaya Langsung";
                        }
                        if ($fal == 2) {
                            $capt = "Biaya Tidak Langsung";
                        }
                        if ($fal == 3) {
                            $capt = "Operasi";
                        }
                        $optjb .= "<option value='" . $kei . "'>" . $capt . "</option>";
                    }
                    echo "<tr><td colspan=3>
							<table class=sortable cellspacing=1 border=0>
							<thead>
							<tr class=rowheader>
								<td align=center >" . $_SESSION['lang']['nourut'] . "</td>
								<td align=center >" . $_SESSION['lang']['namabarang'] . "</td>
								<td align=center >" . $_SESSION['lang']['subtipeasset'] . "</td>
								<td align=center >" . $_SESSION['lang']['namaasset'] . "</td>
								<td align=center >" . $_SESSION['lang']['jenisbiaya'] . "</td>
							</tr>
							</thead>";

                    $no = 0;
                    $no2 = 0;
                    $str = "SELECT * from " . $dbname . ".log_formcapex_dt where notransaksi='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    foreach ($res as $key => $val) {
                        $no++;
                        $optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
                        $skl = "SELECT * from " . $dbname . ".bgt_5capex where kelbarang='" . substr($val['kodebarang'], 0, 3) . "'";
                        $rkl = $owlPDO->query($skl) or die(print " Gagal: " . PDOException::getMessage());
                        $rkl->setFetchMode(PDO::FETCH_ASSOC);
                        $bkl = $rkl->fetch();

                        $iSub = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='" . $bkl['kodecapex'] . "' ";
                        $rsub = $owlPDO->query($iSub) or die(print " Gagal: " . PDOException::getMessage());
                        $rsub->setFetchMode(PDO::FETCH_ASSOC);
                        while ($dSub = $rsub->fetch()) {
                            $optSub .= "<option " . $select . " value='" . $dSub['kodesub'] . "'>" . $dSub['namasub'] . "</option>";
                        }
                        echo "<tr class=rowcontent>
									<td style='text-align:center;'>" . $no . "</td>
									<td>
										<input type=text id=nmbrg_" . $no2 . " class=myinputtext value='" . $optNmBrg[$val['kodebarang']] . "' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled></td>
										<input type=hidden id=kdbrg_" . $no2 . " class=myinputtext value='" . $val['kodebarang'] . "' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled>
										<input type=hidden id=kdasset_" . $no2 . " class=myinputtext value='" . $bkl['kodecapex'] . "' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled>
									<td>
										<select id=subasset_" . $no2 . ">" . $optSub . "</select>
									</td>
									<td>
										<input type=text id=nama_" . $no2 . " class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:195px;'>
									</td>
									<td>
										<select id=jbiaya_" . $no2 . ">" . $optjb . "</select>
									</td>
								</tr>";
                        $no2 += 1;
                    }
                    echo "</td></tr>";
                }

                echo "<tr>
								<td style='vertical-align:top'>Catatan</td>
								<td style='vertical-align:top'>:</td>
								<td>
									<textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=2></td>
								<td>
									<input type=hidden id=totrows value='" . $no2 . "' />
									<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>
									<button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
								</td>
							</tr>
						</table>
					</fieldset>
				</div>";
                break;
            case 'BAJS':
                $tab = "";

                if ($hasilpersetujuan == '1') {
                    $str = "SELECT  a.updateby, a.unit,a.subunitdt,a.subunit,b.kode,b.statuspersetujuan,b.dgnapproval,b.dgnapproval,c.kode as kodeapr, max(c.level) as lvl, c.karyawanid  FROM " . $dbname . ".log_bakontrakjasa a
					LEFT JOIN " . $dbname . ".project b ON a.subunitdt=b.kode
					LEFT JOIN " . $dbname . ".project_approval c ON b.kode=c.kode where notransaksi='" . $notransaksi . "' and status='9'";

                    $res = fetchdata($str);
                    $unit = $res[0]['unit'];
                    $jlhlevel = $res[0]['lvl'];
                    $dgnapprovalstatus = $res[0]['dgnapproval'];
                    $dept = getKary($res[0]['updateby'], 'bagian');

                    if ($dgnapprovalstatus == '1') {
                        $countApp = $jlhlevel;
                    } else {
                        $countApp = getCountApproval($proses, $unit, $dept);
                    }

                    for ($i = 1; $i <= $countApp; $i++) {
                        $arrDetail = detailApprove($i, $notransaksi, $proses, $_SESSION['standard']['userid']);
                        if ($_SESSION['standard']['userid'] == $arrDetail['karyawanid']) {

                            if ($i == $countApp) {
                                ## LAST APPROVAL

                                $tab .= "<div id=approve>
									<fieldset>
									<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 border=0>
										<tr>
											<td colspan=3>Submit to Posting BAPP.</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<input type='hidden' id='nextlevelapp' value=''>
												<input type='hidden' id='user_id' value='last'>
												<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
											</td>
										</tr>
										<tr>
											<td colspan=3 align=center>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['save'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            } else {
                                // $level = $i+1;
                                $nextlevelapp = ($i + 1);

                                // $optnamaapr=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
                                // $optlokt=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas');

                                if ($dgnapprovalstatus == '1') {
                                    $strapr = "SELECT a.unit,a.subunitdt,a.subunit,b.kode,b.statuspersetujuan,b.dgnapproval,b.dgnapproval,c.kode as kodeapr, max(c.level) as lvl, c.karyawanid  FROM " . $dbname . ".log_bakontrakjasa a  LEFT JOIN " . $dbname . ".project b ON a.subunitdt=b.kode
									LEFT JOIN " . $dbname . ".project_approval c ON b.kode=c.kode where notransaksi='" . $notransaksi . "' and level='" . $nextlevelapp . "'";
                                    $arrListApp = fetchdata($strapr);
                                } else {
                                    $arrListApp = listApprove($nextlevelapp, $proses, $unit, $dept, '');
                                }

                                foreach ($arrListApp as $key => $val) {
                                    // $optKry.="<option value='".$val['karyawanid']."'>".$val['karyawanid']."</option>";
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . getKary($val['karyawanid']) . " [" . getKary($val['karyawanid'], 'lokasitugas') . "]</option>";
                                }

                                $tab .= "<div id=test style=display:block>
									<fieldset>
									<legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 cellpadding=3 border=0>
										<tr>
											<td colspan=3 style='font-weight:bold'>Submit to the next approval</td>
										</tr>
										<tr>
											<td style='min-width:80px;'>Next Approval</td>
											<td>:</td>
											<td valign=top>
												<input type='hidden' id='nextlevelapp' value='" . $nextlevelapp . "'>
												<select id=user_id name=user_id>" . $optKry . "</select>
											</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<textarea id='alasan' onClick='return tanpa_kutip(event)'></textarea>
											</td>
										</tr>
											<td colspan=2></td>
											<td>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            }
                        }
                    }
                }

                if ($hasilpersetujuan == '2') {
                    $tab .= "<div id=approve>
						<fieldset>
						<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3><b>Reject</b></td>
							</tr>
							<tr>
								<td>" . $_SESSION['lang']['note'] . "</td>
								<td>:</td>
								<td>
									<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\"Click button to reject RFQ\" id=Ajukan >" . $_SESSION['lang']['ditolak'] . "</button>
									<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
								</td>
							</tr>
						</table>
						</fieldset>
					</div>";
                }

                if ($hasilpersetujuan == '3') {
                    $tab .= "<div id=approve>
						<fieldset>
						<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3><b>Koreksi</b></td>
							</tr>
							<tr>
								<td>" . $_SESSION['lang']['note'] . "</td>
								<td>:</td>
								<td>
									<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\"Click button to reject RFQ\" id=Submit >Koreksi</button>
									<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
								</td>
							</tr>
						</table>
						</fieldset>
					</div>";
                }

                echo $tab;
                break;

            case 'ADJ':
                if ($hasilpersetujuan == '1') {
                    $optKry = "";
                    $str = "select notransaksi,kodegudang from " . $dbname . ".log_stopname_log_list where notransaksi='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    $dt = explode("/", $res[0]['notransaksi']);
                    $unit = $dt[4];

                    $totalApproval = getCountApproval($proses, $unit);
                    $arrListApp = listNextApprove($level, $proses, $unit);
                    for ($i = 1; $i <= $totalApproval; $i++) {
                        $arrDetail = detailApprove($i, $notransaksi, $proses, $_SESSION['standard']['userid']);
                        if ($_SESSION['standard']['userid'] == $arrDetail['karyawanid']) {
                            if ($i == $totalApproval) {
                                $level = $i + 1;
                                $tab .= "<div id=approve>
									<fieldset>
									<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 border=0>
										<tr>
											<td colspan=3>Submit to Adjustment stock.</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<input type='hidden' id='nextlevelapp' value=''>
												<input type='hidden' id='user_id' value='last'>
												<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
											</td>
										</tr>
										<tr>
											<td colspan=3 align=center>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            } else {
                                $level = $i + 1;
                                // exit("warning : ".$level." - ".$notransaksi." - ".$proses." ");
                                // $arrListApp = detailApprove($i,$notransaksi,$proses,$_SESSION['standard']['userid']);
                                // $arrListApp = listApprove($level, $proses, $unit);
                                foreach ($arrListApp as $key => $val) {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . "</option>";
                                    $nextlevelapp = $val['level'];
                                }

                                $tab .= "<div id=test style=display:block>
									<fieldset>
									<legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 cellpadding=3 border=0>
										<tr>
											<td colspan=3 style='font-weight:bold'>Submit to the next approval</td>
										</tr>
										<tr>
											<td>Next Approval</td>
											<td>:</td>
											<td valign=top>
												<input type='hidden' id='nextlevelapp' value='" . $nextlevelapp . "'>
												<select id=user_id name=user_id>" . $optKry . "</select>
											</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<textarea id='alasan' onClick='return tanpa_kutip(event)'></textarea>
											</td>
										</tr>
											<td colspan=2></td>
											<td>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            }
                        }
                    }
                }

                if ($hasilpersetujuan == '2') {
                    $tab .= "<div id=approve>
						<fieldset>
						<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3><b>Reject</b></td>
							</tr>
							<tr>
								<td>" . $_SESSION['lang']['note'] . "</td>
								<td>:</td>
								<td>
									<input type='hidden' id='nextlevelapp' value=''>
									<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\"Click button to reject RFQ\" id=Ajukan >" . $_SESSION['lang']['ditolak'] . "</button>
									<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
								</td>
							</tr>
						</table>
						</fieldset>
					</div>";
                }

                echo $tab;
                break;

            case 'RFQ':
                if ($hasilpersetujuan == '1') {
                    $optKry = "";
                    $str = "select nomor,nilaipermintaan from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "' and statusverifikasi='1'";
                    $res = fetchdata($str);
                    // $unit=$res[0]['unit'];
                    $nilai = $res[0]['nilaipermintaan'];
                    $dt = explode("/", $res[0]['nomor']);
                    $unit = $dt[4];

                    // ambil count approval
                    // cek nilai
                    $str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NILAIVPOAP' ";
                    $res = fetchdata($str);
                    $nilai_appHO = $res[0]['nilai'];

                    // bandingkan dengan supptotal dari yg di jadikan pemenang
                    $nilai_subtotal_n = 0;
                    $strx = "select harga,jumlah from " . $dbname . ".log_permintaanhargadt where nomor = '" . $notransaksi . "' and score='1' ";
                    $resx = fetchData($strx);
                    foreach ($resx as $valx) {
                        $nilai_subtotal_n += $valx['harga'] * $valx['jumlah'];
                    }

                    if ($level > 1) {
                        $levelsebelum = $level - 1;
                    } else {
                        $levelsebelum = $level;
                    }

                    if ($nilai_subtotal_n >= $nilai_appHO and $unit == 'PPPE') {
                        $induk_unit = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
                        // ambil unit HO dari PT tersebut
                        $str = "select kodeorganisasi from " . $dbname . ".organisasi where tipe='HOLDING' and induk='" . $induk_unit[$unit] . "'  ";
                        $res = fetchdata($str);
                        $unit_HO = $res[0]['kodeorganisasi'];

                        $arrListApp = listNextApprove($levelsebelum, $proses, $unit_HO, $nilai_subtotal_n);

                        // exit("warning : ".$level." - ".$proses." - ".$unit_HO." - ".$nilai_subtotal_n." ");
                        $totalApproval = ceklastapproval($level, $unit_HO, $proses, $nilai_subtotal_n); //exit('error '. $totalApproval);
                    } else {
                        $arrListApp = listNextApprove($levelsebelum, $proses, $unit, $nilai_subtotal_n);
                        $totalApproval = ceklastapproval($level, $unit, $proses, $nilai_subtotal_n);
                    }

                    // $countApp = getCountApproval($proses,$unit);
                    // if ($_SESSION['standard']['userid'] == '0000003996') {
                    //     echo "<pre>";
                    //     print_r($unit);
                    //     exit("Warning: " . $nilai_subtotal_n . " >= " . $nilai_appHO);
                    // }
                    for ($i = 1; $i <= $totalApproval; $i++) {
                        $arrDetail = detailApprove($i, $notransaksi, $proses, $_SESSION['standard']['userid']);
                        if ($_SESSION['standard']['userid'] == $arrDetail['karyawanid']) {
                            // echo "<pre>";
                            // print_r($countApp);
                            // echo "</pre>";
                            if ($i == $totalApproval) {
                                $level = $i + 1;
                                $tab .= "<div id=approve>
									<fieldset>
									<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 border=0>
										<tr>
											<td colspan=3>Submit to Release PO/SO.</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<input type='hidden' id='nextlevelapp' value=''>
												<input type='hidden' id='user_id' value='last'>
												<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
											</td>
										</tr>
										<tr>
											<td colspan=3 align=center>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            } else {
                                $level = $i + 1;
                                // exit("warning : ".$level." - ".$notransaksi." - ".$proses." ");
                                // $arrListApp = detailApprove($i,$notransaksi,$proses,$_SESSION['standard']['userid']);
                                // $arrListApp = listApprove($level, $proses, $unit);
                                foreach ($arrListApp as $key => $val) {
                                    @$optKry .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . "</option>";
                                    $nextlevelapp = $val['level'];
                                }

                                $tab .= "<div id=test style=display:block>
									<fieldset>
									<legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
									<table cellspacing=1 cellpadding=3 border=0>
										<tr>
											<td colspan=3 style='font-weight:bold'>Submit to the next approval</td>
										</tr>
										<tr>
											<td>Next Approval</td>
											<td>:</td>
											<td valign=top>
												<input type='hidden' id='nextlevelapp' value='" . $nextlevelapp . "'>
												<select id=user_id name=user_id>" . $optKry . "</select>
											</td>
										</tr>
										<tr>
											<td>" . $_SESSION['lang']['note'] . "</td>
											<td>:</td>
											<td>
												<textarea id='alasan' onClick='return tanpa_kutip(event)'></textarea>
											</td>
										</tr>
											<td colspan=2></td>
											<td>
												<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\" Submit to the next level\" id=Ajukan >" . $_SESSION['lang']['diajukan'] . "</button>
												<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
											</td>
										</tr>
									</table>
									</fieldset>
								</div>";
                            }
                        }
                    }
                }

                if ($hasilpersetujuan == '2') {
                    $tab .= "<div id=approve>
						<fieldset>
						<legend><input class=myinputtext disabled type=text readonly=readonly name=rnopp id=rnopp value=" . $notransaksi . "  /></legend>
						<table cellspacing=1 border=0>
							<tr>
								<td colspan=3><b>Reject</b></td>
							</tr>
							<tr>
								<td>" . $_SESSION['lang']['note'] . "</td>
								<td>:</td>
								<td>
									<input type='hidden' id='nextlevelapp' value=''>
									<textarea id='alasan' onClick=\"return tanpa_kutip(event)\" ></textarea>
								</td>
							</tr>
							<tr>
								<td colspan=3 align=center>
									<button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "') title=\"Click button to reject RFQ\" id=Ajukan >" . $_SESSION['lang']['ditolak'] . "</button>
									<button class=mybutton onclick=closeDialogx() title=\" Close this form \">" . $_SESSION['lang']['cancel'] . "</button>
								</td>
							</tr>
						</table>
						</fieldset>
					</div>";
                }

                echo $tab;
                break;
            case 'CVMM':
            case 'PAS':
                echo "
		            <div id=test style=display:block>
		            <input hidden align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\">
		            <table cellspacing=1 border=0>
		            <tr>
		                <td colspan=3>Catatan : </td>
		            </tr>
		            <tr>
						<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=45 rows=8></textarea></td>
		            </tr>
		            <td align=center>
		            <button class=mybutton onclick=approvedkpi('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "','" . $fromdata . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>
		            </td></tr></table>
		            </div>";
                break;
            default:
                if ($level > 1) {
                    $levelsblm = $level - 1;
                    $strap = "select status from " . $dbname . ".approval where jenispersetujuan='" . $proses . "' and notransaksi='" . $notransaksi . "' and level='" . $levelsblm . "'";
                    $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar = $res->fetch();
                    $statussblm = $bar['status'];
                } else {
                    $statussblm = 1;
                }

                if ($statussblm == 0) {
                    echo "
					<div id=test style=display:block>
		            <fieldset>
		            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
		            <table cellspacing=1 border=0>
		            <tr>
						<td>Note : Harap menunggu persetujuan sebelumnya.</td>
					</tr>
					</table>
		            </fieldset></div>";
                } else {
                    echo "
		            <div id=test style=display:block>
		            <fieldset>
		            <legend><input align=center class=myinputtext disabled type=text readonly=readonly value=" . $notransaksi . " style=\"min-width:175px;\"  /></legend>
		            <table cellspacing=1 border=0>
		            <tr>
		                <td colspan=3>Catatan : </td>
		            </tr>
		            <tr>
						<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
		            </tr>
		            <td>
		            <button class=mybutton onclick=approved('" . $proses . "','" . $jenispersetujuan . "','" . $notransaksi . "','" . $level . "','" . $hasilpersetujuan . "','" . $fromdata . "') id=Submit >" . $_SESSION['lang']['save'] . "</button>

		            <button class=mybutton onclick=closeDialogx()>" . $_SESSION['lang']['cancel'] . "</button>
		            </td></tr></table>
		            </fieldset></div>";
                }
                break;
        }
        break;

    case 'approved':
        switch ($proses) {
            case 'KTRKJUAL':

                $str = "SELECT kodeorg FROM " . $dbname . ".pmn_kontrakjual WHERE nokontrak='" . $notransaksi . "'";
                $res = fetchData($str);
                $kodeorg = $res[0]['kodeorg'];

                $countApp = getCountApproval($proses, $kodeorg);

                try {
                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "' and jenispersetujuan='KTRKJUAL'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".pmn_kontrakjual set posting='1' where nokontrak='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    echo $proses . "####" . $notransaksi . "####";
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'BAST':

                $str = "SELECT kodept FROM " . $dbname . ".pmn_bast WHERE notransaksi='" . $notransaksi . "'";
                $res = fetchData($str);
                $kodept = $res[0]['kodept'];

                $sql = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi WHERE induk='" . $kodept . "' and tipe ='KANWIL'";
                $key = fetchData($sql);
                $kodeorg = $key[0]['kodeorganisasi'];

                $countApp = getCountApproval($proses, $kodeorg);

                try {
                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "' and jenispersetujuan='BAST'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".pmn_bast set posting='8' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    echo $proses . "####" . $notransaksi . "####";
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'DO':

                $str = "SELECT kodeorg FROM " . $dbname . ".pmn_suratperintahpengiriman a LEFT JOIN " . $dbname . ".pmn_kontrakjual b ON a.nokontrak=b.nokontrak WHERE a.   nodo='" . $notransaksi . "'";
                $res = fetchData($str);
                $kodeorg = $res[0]['kodeorg'];

                $countApp = getCountApproval($proses, $kodeorg);

                try {
                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "' and jenispersetujuan='DO'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".pmn_suratperintahpengiriman set posting='1' where nodo='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    echo $proses . "####" . $notransaksi . "####";
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'PHP':
                try {
                    $owlPDO->beginTransaction();

                    $expnotran = explode('/', $notransaksi);
                    $kodeorg = $expnotran[1];
                    $karyawanid = $_SESSION['standard']['userid'];

                    $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                    $res = fetchData($str);
                    $countApp = $res[0]['countApp'];

                    $tglskrng = date("Y-m-d H:i:s");
                    $str = "UPDATE $dbname.approval SET `status`='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    $counterappverror = 0;

                    #= cek kalau tanggal masih kosong
                    $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' and (karyawanid='" . $karyawanid . "')";
                    $res = fetchdata($str);

                    foreach ($res as $bar) {
                        if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                            #= roll back approval pertama
                            $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "' WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strroll);
                            $counterappverror++;
                        }
                    }

                    // exit("Error:$counterappverror");
                    if ($counterappverror > 0) {
                        exit("Warning:Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi . " ");
                    }

                    if ($level == $countApp) {
                        #= bentuk query data untuk posting
                        $str = selectQuery($dbname, 'lgl_penawaranharga', '*', "notransaksi='" . $notransaksi . "'");
                        $bar = fetchData($str)[0];

                        $str = "UPDATE $dbname.lgl_penawaranharga SET statuspersetujuan='1' WHERE notransaksi='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "UPDATE $dbname.lgl_penawaranhargadt SET flag='1' WHERE notransaksi='" . $notransaksi . "' and nourut='" . $param['pilihpemenang'] . "'";
                        $owlPDO->exec($str);
                    }
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;

            case 'SERVICE':
                // if($alasan==''){
                //     exit("warning : Komentar harus diisi.");
                // }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $karyawanid = $_SESSION['standard']['userid'];

                $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                $res = fetchData($str);
                $countApp = $res[0]['countApp'];

                $tglskrng = date("Y-m-d H:i:s");
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                $counterappverror = 0;

                #= cek kalau tanggal masih kosong
                $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' and (karyawanid='" . $karyawanid . "')";
                $res = fetchdata($str);

                foreach ($res as $bar) {
                    if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                        #= roll back approval pertama
                        $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "' WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($strroll);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                        $counterappverror++;
                    }
                }

                // exit("Error:$counterappverror");
                if ($counterappverror > 0) {
                    exit("Warning:Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi . " ");
                }

                if ($level == $countApp) {
                    #= bentuk query data untuk posting
                    $str = "select * from " . $dbname . ".vhc_penggantianht where notransaksi='" . $notransaksi . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar = $res->fetch();

                    $str = "update " . $dbname . ".vhc_penggantianht set statuspersetujuan='1',posting='1',postingby='" . $_SESSION['standard']['userid'] . "',postedtime='" . date("Y-m-d H:i:s") . "' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

                break;
            case 'UNPOST':
                try {
                    $owlPDO->beginTransaction();

                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }
                    $expnotran = explode('/', $notransaksi);
                    $kodeorg = $expnotran[1];
                    $countApp = getCountApproval($proses, $kodeorg);
                    $tglskrng = date("Y-m-d H:i:s");

                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "' AND jenispersetujuan='" . $proses . "'";
                    $owlPDO->exec($str);

                    $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "' AND level='" . ($level + 1) . "' AND jenispersetujuan='" . $proses . "'";
                    $res = fetchdata($str);
                    if (count($res) > 0) {
                    } else {
                        $str = "update " . $dbname . ".owlhelp_ticket set persetujuan='1' where id='" . $notransaksi . "'";
                        $owlPDO->exec($str);
                    }

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'LBR':
                try {
                    $owlPDO->beginTransaction();
                    $str = "select * from " . $dbname . ".sdm_lemburht where `nopengajuan`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kodeorg = substr($res[0]['kodeorg'], 0, 4);
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $countApp = getCountApproval($kodeapproval, $kodeorg);

                    if ($res[0]['posting'] == 9) {
                        $statuspengajuan = $res[0]['posting'];
                    }
                    $updateby = $res[0]['postingby'];

                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {
                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        if ($kolom != $countApp) {
                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            #update transaksi
                            $str = "update " . $dbname . ".sdm_lemburht set posting='1', approveby ='" . $user_id . "' where nopengajuan='" . $notransaksi . "'";
                            $owlPDO->exec($str);

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'PNN':
                try {
                    $owlPDO->beginTransaction();

                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kodeorg = $param['kodeorg'];
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $countApp = getCountApproval($kodeapproval, $kodeorg);

                    $str = "select * from " . $dbname . ".kebun_5basispanen2 where `nopengajuan`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    if ($res[0]['posting'] == 9) {
                        $statuspengajuan = $res[0]['posting'];
                    }
                    $updateby = $res[0]['updateby'];

                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {
                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        if ($kolom != $countApp) {
                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            #update transaksi
                            $str = "update " . $dbname . ".kebun_5basispanen2 set posting='1' where nopengajuan='" . $notransaksi . "'";
                            $owlPDO->exec($str);

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'PNNBR':
                try {
                    $owlPDO->beginTransaction();

                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kodeorg = $param['kodeorg'];
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $countApp = getCountApproval($kodeapproval, $kodeorg);

                    $str = "select * from " . $dbname . ".kebun_5premikutipbrondolansaja where `nopengajuan`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    if ($res[0]['posting'] == 9) {
                        $statuspengajuan = $res[0]['posting'];
                    }
                    $updateby = $res[0]['updateby'];

                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {
                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        if ($kolom != $countApp) {

                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            #update transaksi
                            $str = "update " . $dbname . ".kebun_5premikutipbrondolansaja set posting='1' where nopengajuan='" . $notransaksi . "'";
                            $owlPDO->exec($str);

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;

            //Umar
            case 'GRNINO':
            case 'GRNISO':
            case 'GRNICO':
                try {
                    $owlPDO->beginTransaction();

                    $str = "select * from " . $dbname . ".setup_kegiatan";
                    // exit("warning:".$str);
                    $res = fetchdata($str);
                    foreach ($res as $bar) {
                        $klkegiatan[$bar['kodekegiatan']] = $bar['kelompok'];
                    }

                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $str = "select * from " . $dbname . ".log_noninventory where `notransaksi`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    if ($res[0]['persetujuan'] == 9) {
                        $statuspengajuan = $res[0]['persetujuan'];
                    }
                    $updateby = $res[0]['updateby'];
                    $kodeorg = $res[0]['unit'];

                    $pembuat = makeOption($dbname, 'log_noninventory', 'notransaksi,createdby', "notransaksi = '" . $notransaksi . "'");
                    $departemen = getKary($pembuat[$notransaksi], 'bagian');

                    $countApp = getCountApproval($kodeapproval, $kodeorg, $departemen);

                    // exit("Error:".$statuspengajuan.___.$res[0]['persetujuan']);
                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {

                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        if ($kolom != $countApp) {
                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            #update transaksi

                            // if($tanggalselesai){
                            // $tglselesai = tanggalsystemn($tanggalselesai);
                            // }else{
                            // $tglselesai = '0000-00-00';
                            // }
                            $tglskrg = date("Y-m-d H:i:s");

                            ##UBAH FLAG Posting
                            $str = "update " . $dbname . ".log_noninventory set posting='1',persetujuan='1',postedby='" . $_SESSION['standard']['userid'] . "', postedtime='" . $tglskrg . "', tanggalselesai='" . $tglskrg . "' where notransaksi='" . $notransaksi . "'";
                            $owlPDO->exec($str);

                            // $str = "update ".$dbname.".log_noninventory set persetujuan='1',posting='1' where notransaksi='".$notransaksi."'" ;
                            // $owlPDO->exec($str);

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);

                            #= bentuk jurnal non inventory di approval terakhir

                            $str = "select * from " . $dbname . ".log_noninventory where notransaksi='" . $notransaksi . "'";
                            // exit("warning:".$str);
                            $res = fetchdata($str);
                            $unit = $res[0]['unit'];
                            $pt = $res[0]['pt'];
                            $tanggal = $res[0]['tanggal'];
                            $tanggal = $res[0]['tanggal'];
                            $tipe = $res[0]['tipe'];
                            $nopox = $res[0]['nopo'];
                            $supplierid = $res[0]['supplierid'];
                            $kodejurnal = "NOINV";

                            $jurnalfound = '';
                            // cek apakah sudah ada jurnal?
                            $str = "select nojurnal from " . $dbname . ".keu_jurnalht where noreferensi = '" . $notransaksi . "' ";
                            $res = fetchdata($str);
                            foreach ($res as $key => $val) {
                                $jurnalfound .= $val['nojurnal'] . ',';
                            }
                            if ($jurnalfound != '') {
                                // exit ("error : Sudah ada jurnal: ".$jurnalfound." silakan refresh.");
                                throw new PDOException("Sudah ada jurnal: " . $jurnalfound . " silakan refresh");
                            }

                            ## Prepare jurnal
                            ## Ambil noakun supplier

                            // ambil nopo
                            $str_n = "select nopo from " . $dbname . ".log_noninventory where notransaksi='" . $notransaksi . "' ";
                            $res_n = fetchdata($str_n);
                            $nopo = $res_n[0]['nopo'];

                            // cek apakah ada di klsup
                            $str0 = "select tipesub from " . $dbname . ".log_poht where nopo='" . $nopo . "' ";
                            $res0 = fetchData($str0);
                            $tipesub = $res0[0]['tipesub'];
                            if ($tipesub != '') {
                                $str1 = "select tipe,noakun from " . $dbname . ".log_5klsupplier where noakun!='' and tipe='" . $tipesub . "' ";
                                $res1 = fetchdata($str1);
                                if (count($res1) > 0) {
                                    $kodekl = $res1[0]['tipe'];
                                    $noakunkr = $res1[0]['noakun'];
                                } else {
                                    $kodekl = "SUPPLIER";
                                    $noakunkr = "2110101";
                                    if ($tipe == 'SO') {
                                        $kodekl = "JASA";
                                        $noakunkr = "2110301";
                                    }
                                }
                                //Ambil akun GR/IR
                                $str1="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='GR' and kodeparameter='AKUNGRIR'"; 
                                $res1=fetchdata($str1);
                                $noakungrir=$res1[0]['nilai'];
                                $noakunkr=$noakungrir;
                            } else {
                                exit("warning : Tipe supplier pada PO " . $nopo . " masih kosong... ");
                            }

                            // GRIR 2021
                            // $noakungrir='2110501';
                            // $noakunkr=$noakungrir;
                            // $str = "select kodeorganisasi from ".$dbname.".organisasi where induk in (select induk from ".$dbname.".organisasi where kodeorganisasi = '".$unit."' ) and tipe = 'KANWIL' and inti=1";
                            // $res=fetchdata($str);
                            // foreach($res as $key=>$val){
                            //     $ronya = $val['kodeorganisasi'];
                            // }

                            // $str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$ronya."' and jenis = 'intra'";
                            // $res=fetchdata($str);
                            // foreach($res as $key=>$val){
                            //     $akuncacoro=$val['akunpiutang'];
                            // }
                            // $str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis = 'intra'";
                            // $res=fetchdata($str);
                            // foreach($res as $key=>$val){
                            //     $akuncacoes=$val['akunhutang'];
                            // }

                            // cek apakah unit sudah closing
                            $str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $periode . "' and kodeorg='" . $unit . "'";
                            $res = fetchdata($str);
                            $close = $res[0]['tutupbuku'];
                            if ($close == '1') {
                                throw new PDOException($unit . " sudah tutup buku periode " . $periode . " ");
                            }

                            // cek apakah RO sudah closing
                            $str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $periode . "' and kodeorg='" . $ronya . "'";
                            $res = fetchdata($str);
                            $close = $res[0]['tutupbuku'];
                            if ($close == '1') {
                                throw new PDOException($ronya . " sudah tutup buku periode " . $periode . "");
                            }

                            if ($noakunkr == '') {
                                exit("Warning:No. Akun masih kosong kredit masih kosong, silahkan cek di setup kelompok supplier, jika memakai konsep GR/IR cek juga akun GR/IR disetup tersebut");
                            }

                            $queryJ = selectQuery(
                                $dbname,
                                'keu_5kelompokjurnal',
                                'nokounter',
                                "kodeorg='" . $pt . "' and kodekelompok='" . $kodejurnal . "'
									and kodeunit='" . $unit . "' and periode='" . substr($tanggal, 0, 7) . "'"
                            );
                            $tmpKonter = fetchData($queryJ);
                            $konter = $tmpKonter[0]['nokounter'];
                            // $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                            // GRIR 2021
                            $queryJro = selectQuery(
                                $dbname,
                                'keu_5kelompokjurnal',
                                'nokounter',
                                "kodeorg='" . $pt . "' and kodekelompok='" . $kodejurnal . "'
									and kodeunit='" . $ronya . "' and periode='" . substr($tanggal, 0, 7) . "'"
                            );
                            $tmpKonterro = fetchData($queryJro);
                            $konterro = $tmpKonterro[0]['nokounter'];

                            // Jika SO ongkos angkut tidak menjurnal
                            $strx = "select nopo from " . $dbname . ".log_sorefrensi where noso='" . $nopox . "'";
                            $resx = fetchdata($strx);
                            $nosopo = $resx[0]['nopo'];
		                    $sql="select * from ".$dbname.".log_somaterial where nopo='".$nopox."'";
                            $hsl=fetchdata($sql);
                            foreach ($hsl as $v) {
                                @$nilaisomaterial+=($v['jumlah'] * $v['harga']);
                            }
                            if ($nosopo == '') {

                                ##MAINKAN JURNAL NYA
                                // Default Segment
                                $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
                                $notemp = 0;
                                $notempro = 0;
                                $str = "select * from " . $dbname . ".log_noninventorydt_vw where notransaksi='" . $notransaksi . "'";
                                // exit("warning:".$str);
                                $res = fetchdata($str);
                                foreach ($res as $bar) {

                                    $optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid = '" . $bar['supplierid'] . "'");
                                    $namasupplier = $optsup[$bar['supplierid']];

                                    $kodeblok = '';
                                    $kodevhc = '';
                                    $kodeasset = '';

                                    #= cek data subunit
                                    if (
                                        $klkegiatan[$bar['kodekegiatan']] == 'TM' || $klkegiatan[$bar['kodekegiatan']] == 'TBM' ||
                                        $klkegiatan[$bar['kodekegiatan']] == 'PNN' || $klkegiatan[$bar['kodekegiatan']] == 'BBT' ||
                                        $klkegiatan[$bar['kodekegiatan']] == 'TB' || $klkegiatan[$bar['kodekegiatan']] == 'LC' || $klkegiatan[$bar['kodekegiatan']] == 'MIL'
                                    ) {
                                        $kodeblok = $bar['subunitdt'];
                                    }

                                    if ($klkegiatan[$bar['kodekegiatan']] == 'TRK') {
                                        $kodevhc = $bar['subunitdt'];
                                    }

                                    if ($klkegiatan[$bar['kodekegiatan']] == 'KNT' and substr($bar['subunitdt'], 0, 3) == 'AK-') {
                                        // if(substr($bar['subunitdt'],0,3)=='AK-'){
                                        $kodeasset = $bar['subunitdt'];
                                    }

                                    $data = array();
                                    $dataro = array();
                                    $noUrut = 1;
                                    $notemp++;
                                    $notempro++;
                                    // @$no+=1;
                                    // $konter = addZero($no,3);

                                    # Prep No Jurnal
                                    $nojurnal = str_replace('-', '', $tanggal) . "/" . $unit . "/" . $kodejurnal . "/" . addZero($konter + $notemp, 3);
                                    // GRIR 2021
                                    $nojurnalro = str_replace('-', '', $tanggal) . "/" . $ronya . "/" . $kodejurnal . "/" . addZero($konterro + $notempro, 3);

                                    #== header
                                    #= jurnal ht
                                    $data['header'] = array(
                                        'nojurnal' => $nojurnal,
                                        'kodejurnal' => $kodejurnal,
                                        'tanggal' => $bar['tanggal'],
                                        'tanggalentry' => date('Ymd'),
                                        'posting' => '0',
                                        'totaldebet' => '0',
                                        'totalkredit' => '0',
                                        'amountkoreksi' => '0',
                                        'noreferensi' => $bar['notransaksi'],
                                        'autojurnal' => '1',
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'revisi' => '0',
                                    );
                                    // GRIR 2021

                                    #== detail
                                    #= debet
                                    $data['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $bar['tanggal'],
                                        'nourut' => $noUrut,
                                        'noakun' => substr($bar['kodekegiatan'], 0, 7),
                                        'keterangan' => 'barang: ' . $bar['kodebarang'] . ', jumlah: ' . $bar['jumlah'] . ', PO/SO: ' . $bar['nopo'] . ', vendor: ' . $namasupplier . '',
                                        'jumlah' => (count($hsl)>0 ? ($nilaisomaterial+$bar['hartot']) : $bar['hartot']),
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $bar['unit'],
                                        'kodekegiatan' => $bar['kodekegiatan'],
                                        'kodeasset' => $kodeasset,
                                        'kodebarang' => $bar['kodebarang'],
                                        'nik' => $bar['penerima'],
                                        'kodecustomer' => '',
                                        'kodesupplier' => $bar['supplierid'],
                                        'noreferensi' => $bar['notransaksi'],
                                        'noaruskas' => '',
                                        'kodevhc' => $kodevhc,
                                        'nodok' => $bar['nopo'],
                                        'kodeblok' => $kodeblok,
                                        'revisi' => '0',
                                        'kodesegment' => $defSegment,
                                    );

                                    $noUrut++;

                                    #= kredit
                                    $data['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $bar['tanggal'],
                                        'nourut' => $noUrut,
                                        'noakun' => $noakunkr,
                                        'keterangan' => 'barang: ' . $bar['kodebarang'] . ', jumlah: ' . $bar['jumlah'] . ', PO/SO: ' . $bar['nopo'] . ', vendor: ' . $namasupplier . '',
                                        'jumlah' => (count($hsl)>0 ? ($nilaisomaterial+$bar['hartot']) : $bar['hartot']) * -1,
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $bar['unit'],
                                        'kodekegiatan' => $bar['kodekegiatan'],
                                        'kodeasset' => $kodeasset,
                                        'kodebarang' => $bar['kodebarang'],
                                        'nik' => $bar['penerima'],
                                        'kodecustomer' => '',
                                        'kodesupplier' => $bar['supplierid'],
                                        'noreferensi' => $bar['notransaksi'],
                                        'noaruskas' => '',
                                        'kodevhc' => $kodevhc,
                                        'nodok' => $bar['nopo'],
                                        'kodeblok' => $kodeblok,
                                        'revisi' => '0',
                                        'kodesegment' => $defSegment,
                                    );

                                    // echo "<pre>";
                                    // print_r($data);
                                    // print_r($dataro);
                                    // echo "</pre>";
                                    // exit("error!!!");

                                    $queryH = insertQuery($dbname, 'keu_jurnalht', $data['header']);
                                    $owlPDO->exec($queryH);

                                    foreach ($data['detail'] as $key => $dataDet) {
                                        $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                                        $owlPDO->exec($queryD);
                                    }
                                    // GRIR 2021
                                    // if($unit!=$ronya){
                                    // $queryHro = insertQuery($dbname,'keu_jurnalht',$dataro['header']);
                                    // $owlPDO->exec($queryHro);

                                    // foreach($dataro['detail'] as $key=>$dataDetro) {
                                    // $queryDro = insertQuery($dbname,'keu_jurnaldt',$dataDetro);
                                    // $owlPDO->exec($queryDro);
                                    // }
                                    // }

                                }

                                # Get Journal Counter
                                $str = updateQuery(
                                    $dbname,
                                    'keu_5kelompokjurnal',
                                    array('nokounter' => ($konter + $notemp)),
                                    "kodeorg='" . $pt . "' and kodeunit='" . $unit . "' and
													periode='" . substr($tanggal, 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
                                );
                                // exit("Error:".$queryJRB);
                                $owlPDO->exec($str);
                                // if($unit!=$ronya){
                                // $str = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konterro+$notempro)),
                                // "kodeorg='".$pt."' and kodeunit='".$ronya."' and
                                // periode='".substr($tanggal,0,7)."' and kodekelompok='".$kodejurnal."'");
                                // // exit("Error:".$queryJRB);
                                // $owlPDO->exec($str);
                                // }

                                #=====================================================
                            }
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;
            //End Umar
            case 'EODBKM':
            case 'EODPNN':
            case 'EODRPNN':
            case 'EODTRK':
            case 'EODWS':
            case 'EODLOG':
            case 'EODKB':
            case 'EODKSR':
            case 'EODLBR':
            case 'EODGR':
            case 'EODSPB':
            case 'EODBKMPOST':
            case 'EODPNNPOST':
            case 'EODRPNNPOST':
            case 'EODSPBPOST':
            case 'EODTRKPOST':
            case 'EODWSPOST':
            case 'EODLOGPOST':
            case 'EODGRPOST':
                try {
                    $owlPDO->beginTransaction();
                    $str = "select * from " . $dbname . ".setup_validasiinput_dt where `nopengajuan`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    $statuspengajuan = $res[0]['status'];
                    $updateby = $res[0]['updateby'];

                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kodeorg = $res[0]['kodeorg'];
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $countApp = getCountApproval($kodeapproval, $kodeorg);

                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {
                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        // exit("warning: ".$countApp." ".$kolom." ".$kodeapproval." ".$kodeorg);
                        if ($kolom != $countApp) {
                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            $str = "select * from " . $dbname . ".setup_validasiinput_dt where nopengajuan ='" . $notransaksi . "'";
                            $res = fetchdata($str);
                            if (count($res) > 0) {
                                #update transaksi
                                $str = "update " . $dbname . ".setup_validasiinput_dt set status='1' where nopengajuan='" . $notransaksi . "'";
                                $owlPDO->exec($str);
                            }

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'ATBS':
                try {
                    $owlPDO->beginTransaction();

                    $user_id = $param['userid'];
                    $kodeapproval = $proses;
                    $kodeorg = $param['kodeorg'];
                    $kolom = $param['kolom'];
                    $comment = $param['comment'];
                    $countApp = getCountApproval($kodeapproval, $kodeorg);

                    $str = "select * from " . $dbname . ".kebun_5hargaangkut where `nopengajuan`='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    if ($res[0]['posting'] == 9) {
                        $statuspengajuan = $res[0]['posting'];
                    } else {
                        $statuspengajuan = $res[0]['postingadd'];
                    }
                    $updateby = $res[0]['updateby'];

                    if ($statuspengajuan == 1) {
                        throw new PDOException("Sudah di Approved");
                    } else if ($statuspengajuan == 9) {
                        $arrDetail = detailApprove($kolom, $notransaksi, $kodeapproval);
                        $level = $kolom + 1;
                        if ($kolom != $countApp) {
                            if ($user_id == $arrDetail['karyawanid']) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Sudah di gunakan");
                            } else if ($user_id == $updateby) {
                                throw new PDOException(getNamaKaryawan($user_id) . " Pembuat Transaksi");
                            } else {
                                $strx = "insert into " . $dbname . ".approval values ('','" . $notransaksi . "','" . $kodeapproval . "','" . $level . "','" . $user_id . "','0','','','')";
                                $owlPDO->exec($strx);

                                $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                                $owlPDO->exec($strx);
                            }
                        } else {
                            $str = "select * from " . $dbname . ".kebun_5hargaangkut where nopengajuan ='" . $notransaksi . "' and posting='9'";
                            $res = fetchdata($str);
                            if (count($res) > 0) {
                                #update transaksi
                                $str = "update " . $dbname . ".kebun_5hargaangkut set posting='1' where nopengajuan='" . $notransaksi . "'";
                                $owlPDO->exec($str);
                            } else {
                                $str = "select * from " . $dbname . ".kebun_5hargaangkut where nopengajuan ='" . $notransaksi . "' and postingadd='9'";
                                $res = fetchdata($str);
                                if (count($res) > 0) {
                                    $str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='1' where nopengajuan='" . $notransaksi . "'";
                                    $owlPDO->exec($str);

                                    $s = "select * from " . $dbname . ".kebun_5hargaangkut_additional where nopengajuan ='" . $notransaksi . "'";
                                    $r = fetchdata($s);

                                    $r = "update " . $dbname . ".kebun_5hargaangkut_additional set posting='1' where nopengajuan='" . $notransaksi . "'";
                                    $owlPDO->exec($r);
                                }
                            }

                            $strx = "update " . $dbname . ".approval set status='1', komentar='" . $comment . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $kolom . "' and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($strx);
                        }
                    }

                    #exit("error");
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'BAJS':
                ## APPROVAL

                try {
                    $owlPDO->beginTransaction();

                    if ($user_id == '') {
                        throw new PDOException("Next Approval harus dipilih");
                    }
                    if ($alasan == '') {
                        throw new PDOException("Alasan/Komentar harus diisi");
                    }

                    $str = "select * from " . $dbname . ".setup_kegiatan";
                    $res = fetchdata($str);
                    foreach ($res as $bar) {
                        $klkegiatan[$bar['kodekegiatan']] = $bar['kelompok'];
                    }

                    // echo $str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$level."' and karyawanid!='".$_SESSION['standard']['userid']."'";
                    // $owlPDO->exec($str);
                    // exit('error');

                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    ## GET HEADER TR
                    $str = "select unit from " . $dbname . ".log_bakontrakjasa where notransaksi='" . $notransaksi . "' and status='9'";
                    $res = fetchdata($str);
                    $unit = $res[0]['unit'];

                    $tgldbskrg = date("Y-m-d");

                    if ($user_id == 'last') {
                        $str = "select * from " . $dbname . ".log_bakontrakjasa where notransaksi='" . $notransaksi . "'";
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $unit = $bar['unit'];
                            $pt = $bar['pt'];
                            $tanggal = $bar['tanggal'];
                            $nokontrak = $bar['nokontrak'];
                            if ($bar['noakun'] == 'material') {
                                @$nilaitotalbarang += $bar['jumlah'];
                            }
                            if ($bar['noakun'] == 'jasa') {
                                @$nilaitotaljasa += $bar['jumlah'];
                            }
                            @$nilaitotal += $bar['jumlah'];
                        }

                        #= cek periode aktif
                        $periodetutup = 0;
                        $sPeriode = "select count(*) as periodetutup from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . substr($tanggal, 0, 7) . "' and tutupbuku=1";
                        $rPeriode = fetchdata($sPeriode);
                        $periodetutup = $rPeriode[0]['periodetutup'];

                        if ($periodetutup > 0) {
                            exit("Warningsistem:Periode " . substr($tanggal, 0, 7) . " untuk " . $unit . " sudah ditutup ");
                        }

                        // $kodekegiatan=$res[0]['kodekegiatan'];
                        // $noakunkredit=$res[0]['noakun'];
                        // $noakundebet=substr($res[0]['kodekegiatan'],0,7);

                        $kodejurnal = "BAJS";

                        $str = "select * from " . $dbname . ".log_kontrakjasa where notransaksi='" . $nokontrak . "'";
                        $res = fetchdata($str);
                        $supplierid = $res[0]['supplierid'];

                        $noakunkr = "2110301";

                        $queryJ = selectQuery(
                            $dbname,
                            'keu_5kelompokjurnal',
                            'nokounter',
                            "kodeorg='" . $pt . "' and kodekelompok='" . $kodejurnal . "'
							and kodeunit='" . $unit . "' and periode='" . substr($tanggal, 0, 7) . "'"
                        );
                        $tmpKonter = fetchData($queryJ);
                        $konter = $tmpKonter[0]['nokounter'] + 1;

                        # Prep No Jurnal
                        $nojurnal = str_replace('-', '', $tanggal) . "/" . $unit . "/" . $kodejurnal . "/" . addZero($konter, 3);

                        $tglskrg = date("Y-m-d H:i:s");
                        $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');
                        $data = array();
                        $notemp = 0;
                        $noUrut = 1;

                        #== header
                        #= jurnal ht
                        $data['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $tanggal,
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => '0',
                            'totalkredit' => '0',
                            'amountkoreksi' => '0',
                            'noreferensi' => $bar['notransaksi'],
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );

                        $str = "select * from " . $dbname . ".log_bakontrakjasa where notransaksi='" . $notransaksi . "'";
                        $res = fetchdata($str);
                        foreach ($res as $bar) {

                            $kodeblok = '';
                            $kodevhc = '';
                            $kodeasset = '';

                            #= cek data subunit
                            if (
                                $klkegiatan[$bar['kodekegiatan']] == 'TM' || $klkegiatan[$bar['kodekegiatan']] == 'TBM' ||
                                $klkegiatan[$bar['kodekegiatan']] == 'PNN' || $klkegiatan[$bar['kodekegiatan']] == 'BBT' ||
                                $klkegiatan[$bar['kodekegiatan']] == 'TB' || $klkegiatan[$bar['kodekegiatan']] == 'LC'
                            ) {
                                $kodeblok = $bar['subunitdt'];
                            }

                            if ($klkegiatan[$bar['kodekegiatan']] == 'TRK') {
                                $kodevhc = $bar['subunitdt'];
                            }

                            if ($klkegiatan[$bar['kodekegiatan']] == 'KNT' and substr($bar['subunitdt'], 0, 3) == 'AK-') {
                                // if(substr($bar['subunitdt'],0,3)=='AK-'){
                                $kodeasset = $bar['subunitdt'];
                            }

                            #== detail
                            #= debet
                            $data['detail'][] = array(
                                'nojurnal' => $nojurnal,
                                'tanggal' => $tanggal,
                                'nourut' => $noUrut,
                                'noakun' => substr($bar['kodekegiatan'], 0, 7),
                                'keterangan' => 'Jurnal BA Jasa, Jasa: ' . $bar['kegiatan'] . ',No. Transaksi : ' . $bar['notransaksi'],
                                'jumlah' => $bar['jumlah'],
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'kodeorg' => $bar['unit'],
                                'kodekegiatan' => $bar['kodekegiatan'],
                                'kodeasset' => $kodeasset,
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => '',
                                'kodesupplier' => $supplierid,
                                'noreferensi' => $bar['notransaksi'],
                                'noaruskas' => '',
                                'kodevhc' => $kodevhc,
                                'nodok' => $bar['nokontrak'],
                                'kodeblok' => $kodeblok,
                                'revisi' => '0',
                                'kodesegment' => $defSegment,
                            );
                            $noUrut++;
                        }

                        #= jurnal kredit
                        #= kredit
                        $data['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $noakunkr,
                            'keterangan' => 'Jurnal BA Jasa, Jasa: ' . $bar['kegiatan'] . ',No. Transaksi : ' . $bar['notransaksi'],
                            'jumlah' => $nilaitotal * -1,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplierid,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $nokontrak,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => $defSegment,
                        );
                        $noUrut++;

                        /*
                        #===== insert buat pajak
                        #= jurnal PPN
                        #= jurnal PPH

                        #= coa ppn

                        //log_spk_tax

                        $str="select * from ".$dbname.".log_spk_tax where     notransaksi='".$nokontrak."'";
                        $res=fetchdata($str);
                        foreach($res as $bar){

                        $data['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$bar['noakun'],
                        'keterangan'=>'Jurnal Pajak BA Jasa, barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'],
                        'jumlah'=>$bar['hartot']*$bar['nilai']/100,
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$bar['unit'],
                        'kodekegiatan'=>$bar['kodekegiatan'],
                        'kodeasset'=>$kodeasset,
                        'kodebarang'=>$bar['kodebarang'],
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>$bar['supplierid'],
                        'noreferensi'=>$nokontrak,
                        'noaruskas'=>'',
                        'kodevhc'=>$kodevhc,
                        'nodok'=>$bar['nopo'],
                        'kodeblok'=>$kodeblok,
                        'revisi'=>'0',
                        'kodesegment' => $defSegment
                        );
                        $noUrut++;

                        }
                         */
                        // exit('error');
                        #= insert jurnalnya
                        $queryH = insertQuery($dbname, 'keu_jurnalht', $data['header']);
                        $owlPDO->exec($queryH);

                        foreach ($data['detail'] as $key => $dataDet) {
                            $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                            $owlPDO->exec($queryD);
                        }

                        # Get Journal Counter
                        $queryJRB = updateQuery(
                            $dbname,
                            'keu_5kelompokjurnal',
                            array('nokounter' => ($konter)),
                            "kodeorg='" . $pt . "' and kodeunit='" . $unit . "' and
										periode='" . substr($tanggal, 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
                        );
                        // exit("Error:".$queryJRB);
                        $owlPDO->exec($queryJRB);

                        #==== tutup jurnal

                        $str = "update " . $dbname . ".log_bakontrakjasa set status='1' where notransaksi='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        ##UPDATE STATUS PERSETUJUAN TERAKHIR = 1
                        $strApr = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                        $owlPDO->exec($strApr);
                    } else {
                        #CEK JIKA PROJECT MENGGUNAKAN APPROVAL **BARU
                        $stra = "SELECT a.unit,a.subunitdt,a.subunit,b.kode,b.statuspersetujuan,b.dgnapproval,b.dgnapproval,c.kode as kodeapr, c.level, c.karyawanid  FROM " . $dbname . ".log_bakontrakjasa a  LEFT JOIN " . $dbname . ".project b ON a.subunitdt=b.kode
						LEFT JOIN " . $dbname . ".project_approval c ON b.kode=c.kode where notransaksi='" . $notransaksi . "'";
                        $resa = fetchdata($stra);
                        $stsdgnapproval = $resa[0]['dgnapproval'];
                        $kodeprojectapr = $resa[0]['subunitdt'];

                        $str = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenispersetujuan . "' and level='" . $nextlevelapp . "' and kodeunit='" . $unit . "'";

                        if ($stsdgnapproval == '1') {
                            $str = "select * from " . $dbname . ".project_approval where level='" . $nextlevelapp . "' and kode='" . $kodeprojectapr . "'  ";
                        }
                        // exit('error: '.$str);
                        $res = fetchdata($str);
                        foreach ($res as $val) {
                            if ($val['tipe'] == '1') {
                                if ($val['departemen'] == $user_id) {
                                    $strx = "select karyawanid from " . $dbname . ".datakaryawan where bagian='" . $val['departemen'] . "'";
                                    $resx = fetchdata($strx);
                                    foreach ($resx as $valx) {
                                        $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $nextlevelapp . "','" . $valx['karyawanid'] . "')";
                                        $owlPDO->exec($strx);
                                    }
                                }
                                if ($val['tipekaryawan'] == $user_id) {
                                    $strx = "select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='" . $val['tipekaryawan'] . "'";
                                    $resx = fetchdata($strx);
                                    foreach ($resx as $valx) {
                                        $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $nextlevelapp . "','" . $valx['karyawanid'] . "')";
                                        $owlPDO->exec($strx);
                                    }
                                }
                                if ($val['jabatan'] == $user_id) {
                                    $strx = "select karyawanid from " . $dbname . ".datakaryawan where kodejabatan='" . $val['jabatan'] . "'";
                                    $resx = fetchdata($strx);
                                    foreach ($resx as $valx) {
                                        $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $nextlevelapp . "','" . $valx['karyawanid'] . "')";
                                        $owlPDO->exec($strx);
                                    }
                                }
                                #INSERT KE LEVEL APPROVAL BERIKUTNYA
                            } else {
                                // exit('error: '.$val['karyawanid']);
                                if ($val['karyawanid'] == $user_id || $stsdgnapproval == '1') {
                                    $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $nextlevelapp . "','" . $user_id . "')";
                                    $owlPDO->exec($strx);
                                    // exit('error '.$strx);
                                }
                                if ($stsdgnapproval == '1') {
                                    $stru = "update " . $dbname . ".approval set status='1' where notransaksi='" . $notransaksi . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                                    $owlPDO->exec($stru);
                                    // exit('error'.$stru );
                                }
                            }
                        }
                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warning \n" . addslashes($e->getMessage());
                }
                break;

            case 'KASBANK':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $countApp = getCountApproval($proses, $kodeorg);
                $tglskrng = date("Y-m-d H:i:s");
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                $counterappverror = 0;

                #= cek kalau tanggal masih kosong
                $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                $res = fetchdata($str);

                foreach ($res as $bar) {
                    if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                        #= roll back approval pertama
                        $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "' WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($strroll);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                        $counterappverror++;
                    }
                }

                // exit("Error:$counterappverror");
                if ($counterappverror > 0) {
                    exit("Warning:Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi . " ");
                }

                if ($level == $countApp) {
                    #= bentuk query data untuk posting
                    $str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $notransaksi . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar = $res->fetch();

                    $str = "update " . $dbname . ".keu_kasbankht set posting='1' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    echo "KASBANK####" . $notransaksi . "####" . $bar['kodeorg'] . "####" . $bar['noakun'] . "####" . $bar['tipetransaksi'] . "####" . $bar['novoucher'] . "####" . tanggalnormal($bar['tanggal']);
                }

                break;

            case 'PTBS':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                // if ($expnotran[1] == 'TBSAFI') {
                // $str = "SELECT notransaksi, noreferensi, unit, divisi
                // FROM ".$dbname.".kebun_tbsafiliasi
                // WHERE notransaksi = '".$notransaksi."'
                // LIMIT 1";
                // $res = fetchData($str);

                // if ($res[0]['noreferensi'] != '') {
                // $kodeorg = $res[0]['unit'];
                // } else {
                // $kodeorg = $res[0]['divisi'];
                // }
                // } else {
                // $kodeorg = $expnotran[2];
                // }

                // $countApp = getCountApproval($proses,$kodeorg);

                $str = "select count(*) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $countApp = $bar['jumlah'];
                }
                $tglskrng = date("Y-m-d H:i:s");

                $str = "UPDATE " . $dbname . ".approval SET status = '1', komentar = '" . $alasan . "', tanggal = '" . $tglskrng . "' WHERE notransaksi = '" . $notransaksi . "' AND level = '" . $level . "' AND karyawanid = '" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                $counterappverror = 0;

                #= cek kalau tanggal masih kosong
                $str = "select * from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                        #= roll back approval pertama
                        $strroll = "update " . $dbname . ".approval set status='0',komentar='',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($strroll);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                        $counterappverror++;
                    }
                }

                // exit("Error:$counterappverror");
                if ($counterappverror > 0) {
                    exit("Warning:Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dkumen " . $notransaksi . " ");
                }

                $lvl = $level + 1;
                $query = "SELECT karyawanid
						  FROM " . $dbname . ".approval
						  WHERE jenispersetujuan = 'PTBS'
						  AND notransaksi = '" . $notransaksi . "'
						  AND level = " . $lvl;
                $result = fetchData($query);

                if (strpos($notransaksi, 'TBSKUD') == true) {
                    $tbltbs = "kebun_tbskud";
                    $tipe = 'Petani';
                } else if (strpos($notransaksi, 'TBSAFI') == true) {
                    $tbltbs = "kebun_tbsafiliasi";
                    $tipe = 'Afiliasi';
                } else if (strpos($notransaksi, 'TBSEXT') == true) {
                    $tbltbs = "kebun_tbsexternal";
                    $tipe = 'External';
                }

                $strpengaju = "SELECT postingby
								FROM " . $dbname . "." . $tbltbs . "
								WHERE notransaksi = '" . $notransaksi . "'";
                $respengaju = fetchdata($strpengaju);

                if ($level < $countApp) {
                    // $to = getUserEmail($result[0]['karyawanid']);
                    $namakaryawan = getNamaKaryawan($respengaju[0]['postingby']);
                    $subject = "[Notifikasi]Persetujuan untuk transaksi proses Pembayaran TBS " . $tipe . " dengan nomor " . $notransaksi;
                    $body = "<html>
							 <head>
							 <body>
							   <dd>Dengan Hormat,</dd><br>
							   <br>
							   Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan persetujuan untuk transaksi proses Pembayaran TBS " . $tipe . " dengan nomor " . $notransaksi . "
							   <br>
							   <br>
							   Regards,<br>
							   Owl-Plantation System.
							 </body>
							 </head>
						   </html>";
                    // if ($to != '') {
                    //     $kirim = kirimEmail($to, '', $subject, $body);
                    // }
                }

                if ($level == $countApp) {

                    if (strpos($notransaksi, 'TBSKUD') == true) {

                        #= data transaksi
                        $str = "SELECT
								sum(kgnetto) as kgnetto,
								sum(totalrp) as totalrp,
								notransaksi,
								unit,
								divisi,
								tanggal,
								posting,
								supplier,
								tanggal,
								tanggaltbs1,
								tanggaltbs2,
								pemilik
								FROM " . $dbname . ".kebun_tbskud
								WHERE notransaksi = '" . $notransaksi . "'";
                        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        $bar = $res->fetch();

                        $tanggal = $bar['tanggal'];
                        $tanggaltb1 = $bar['tanggaltbs1'];
                        $tanggaltbs2 = $bar['tanggaltbs2'];
                        $unit = $bar['unit'];
                        $totalrp = floor($bar['totalrp']); // dz: difloorkan biar ga keriting
                        $supplier = $bar['supplier'];
                        $pemilik = $bar['pemilik'];

                        #= jika pt pemilik KUD sama dengan pt pabrik tujuan
                        if ($kodept[$unit] != $kodept[$pemilik]) {
                            $unit = $pemilik;
                        }

                        $optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid = '" . $supplier . "'");
                        $namasupplier = $optsup[$supplier];
                        #= prepare jurnal

                        #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
                        $kodejurnal = "INVTB";
                        $optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi, induk', "kodeorganisasi = '" . $unit . "'");
                        $whereNoindukph = "kodekelompok = '" . $kodejurnal . "' and kodeorg = '" . $kodept[$unit] . "' and kodeunit = '" . $unit . "' and periode = '" . substr($tanggal, 0, 7) . "'";
                        // exit("Error:".$whereNoindukph);
                        $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', $whereNoindukph);
                        $noKon = fetchData($query);
                        $tmpC = $noKon[0]['nokounter'];
                        $tmpC++;

                        $counterjurnal = addZero($tmpC, 3);
                        $nojurnal = str_replace("-", "", $tanggal) . "/" . $unit . "/" . $kodejurnal . "/" . $counterjurnal;

                        #akun debet serta krdit
                        $query2 = selectQuery($dbname, 'keu_5parameterjurnal', 'noakundebet,noakunkredit', "jurnalid='" . $kodejurnal . "' and aktif=1");
                        $dtnoakun = fetchData($query2);

                        #=== Transform Data ===
                        $dataRes['header'] = array();
                        $dataRes['detail'] = array();

                        # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $tanggal,
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => ($totalrp),
                            'totalkredit' => ($totalrp) * -1,
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );

                        #= debet
                        $noUrut = 1;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $dtnoakun[0]['noakundebet'],
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari supplier a/n ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => $totalrp,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        $noUrut++;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $dtnoakun[0]['noakunkredit'],
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari supplier a/n ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => ($totalrp) * -1,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        // echo "<pre>";
                        // print_r($dataRes);
                        // echo "</pre>";
                        // exit("error");

                        // delete dulu jurnal yang sudah terbentuk sebelumnya
                        $str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $notransaksi . "' and kodejurnal = '" . $kodejurnal . "' and tanggal='" . $tanggal . "' and totaldebet = '" . $totalrp . "' ";
                        // exit("error: ".$str);
                        $owlPDO->exec($str);

                        $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                        try {
                            $owlPDO->exec($queryH);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        foreach ($dataRes['detail'] as $key => $dataDet) {
                            $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                            try {
                                $owlPDO->exec($queryD);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }

                        $queryJ = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $tmpC), $whereNoindukph);
                        $errCounter = "";
                        try {
                            $owlPDO->exec($queryJ);
                        } catch (PDOException $e) {
                            $errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
                        }

                        $str = "update " . $dbname . ".kebun_tbskud set
								posting='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    } else if (strpos($notransaksi, 'TBSAFI') == true) {

                        #= data transaksi
                        $str = "SELECT
								sum(kgnetto) as kgnetto, sum(totalrp) as totalrp,
								notransaksi, unit, divisi, tanggal,
								posting, supplier, tanggal,
								tanggaltbs1, tanggaltbs2,
								rounit, ropemilik
								FROM " . $dbname . ".kebun_tbsafiliasi
								WHERE notransaksi = '" . $notransaksi . "'";
                        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        $bar = $res->fetch();

                        $tanggal = $bar['tanggal'];
                        $tanggaltb1 = $bar['tanggaltbs1'];
                        $tanggaltbs2 = $bar['tanggaltbs2'];
                        $unit = $bar['unit'];
                        // $totalrp=$bar['totalrp'];
                        $totalrp = floor($bar['totalrp']); // dz: difloorkan biar ga keriting
                        $supplier = $bar['supplier'];
                        $rounit = $bar['rounit'];
                        $ropemilik = $bar['ropemilik'];

                        #= prepare jurnal
                        $optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $supplier . "'");
                        $namasupplier = $optsup[$supplier];

                        #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
                        $kodejurnal = "INVTB";
                        $optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $rounit . "'");

                        $whereNoindukph = "kodekelompok='" . $kodejurnal . "' and kodeorg='" . $kodept[$rounit] . "' and kodeunit='" . $rounit . "' and periode='" . substr($tanggal, 0, 7) . "'";
                        $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', $whereNoindukph);
                        $noKon = fetchData($query);

                        $tmpC = $noKon[0]['nokounter'];
                        $tmpC++;

                        $counterjurnal = addZero($tmpC, 3);
                        $nojurnal = str_replace("-", "", $tanggal) . "/" . $rounit . "/" . $kodejurnal . "/" . $counterjurnal;

                        #akun debet serta krdit
                        $query2 = selectQuery($dbname, 'keu_5parameterjurnal', 'noakundebet,noakunkredit', "jurnalid='" . $kodejurnal . "' and aktif=1");
                        $dtnoakun = fetchData($query2);

                        #=== Transform Data ===
                        $dataRes['header'] = array();
                        $dataRes['detail'] = array();

                        # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $tanggal,
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => ($totalrp),
                            'totalkredit' => ($totalrp) * -1,
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );

                        #= debet
                        $noUrut = 1;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => '6410102',
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => $totalrp,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $rounit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        $noUrut++;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $dtnoakun[0]['noakunkredit'],
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => ($totalrp) * -1,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $rounit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        // delete dulu jurnal yang sudah terbentuk sebelumnya
                        $str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $notransaksi . "' and kodejurnal = '" . $kodejurnal . "' and tanggal='" . $tanggal . "' and totaldebet = '" . $totalrp . "' ";
                        // exit("error: ".$str);
                        $owlPDO->exec($str);

                        $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                        try {
                            $owlPDO->exec($queryH);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        foreach ($dataRes['detail'] as $key => $dataDet) {
                            $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                            try {
                                $owlPDO->exec($queryD);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }

                        $errCounter = "";
                        $queryJ = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $tmpC), $whereNoindukph);
                        try {
                            $owlPDO->exec($queryJ);
                        } catch (PDOException $e) {
                            $errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
                        }

                        $str = "update " . $dbname . ".kebun_tbsafiliasi set
								posting='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    } else if (strpos($notransaksi, 'TBSEXT') == true) {

                        #= data transaksi
                        $str = "SELECT
								sum(kgnetto) as kgnetto, sum(totalrp) as totalrp,
								notransaksi, unit, divisi, tanggal,
								posting, supplier, tanggal,
								tanggaltbs1, tanggaltbs2
								FROM " . $dbname . ".kebun_tbsexternal
								WHERE notransaksi ='" . $notransaksi . "'";

                        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        $bar = $res->fetch();

                        $tanggal = $bar['tanggal'];
                        $tanggaltbs1 = $bar['tanggaltbs1'];
                        $tanggaltbs2 = $bar['tanggaltbs2'];
                        $unit = $bar['unit'];
                        // $totalrp=$bar['totalrp'];
                        $totalrp = floor($bar['totalrp']); // dz: difloorkan biar ga keriting
                        $supplier = $bar['supplier'];

                        #= prepare jurnal
                        $optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $supplier . "'");
                        $namasupplier = $optsup[$supplier];

                        #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
                        $kodejurnal = "INVTB";
                        $optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");

                        $whereNoindukph = "kodekelompok='" . $kodejurnal . "' and kodeorg='" . $kodept[$unit] . "' and kodeunit='" . $unit . "' and periode='" . substr($tanggal, 0, 7) . "'";
                        $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', $whereNoindukph);
                        $noKon = fetchData($query);

                        $tmpC = $noKon[0]['nokounter'];
                        $tmpC++;
                        $counterjurnal = addZero($tmpC, 3);
                        $nojurnal = str_replace("-", "", $tanggal) . "/" . $unit . "/" . $kodejurnal . "/" . $counterjurnal;

                        #akun debet serta krdit
                        $query2 = selectQuery($dbname, 'keu_5parameterjurnal', 'noakundebet,noakunkredit', "jurnalid='" . $kodejurnal . "' and aktif=1");
                        $dtnoakun = fetchData($query2);

                        #=== Transform Data ===
                        $dataRes['header'] = array();
                        $dataRes['detail'] = array();

                        # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $tanggal,
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => ($totalrp),
                            'totalkredit' => ($totalrp) * -1,
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );

                        #= debet
                        $noUrut = 1;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => '6410103',
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => $totalrp,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        $noUrut++;
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $dtnoakun[0]['noakunkredit'],
                            'keterangan' => 'Penerimaan TBS unit ' . $unit . ' dari ' . $namasupplier . ' pada tanggal ' . $tanggaltbs1 . ' s/d tanggal ' . $tanggaltbs2,
                            'jumlah' => ($totalrp) * -1,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $unit,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => $notransaksi,
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );

                        // delete dulu jurnal yang sudah terbentuk sebelumnya
                        $str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $notransaksi . "' and kodejurnal = '" . $kodejurnal . "' and tanggal='" . $tanggal . "' and totaldebet = '" . $totalrp . "' ";
                        // exit("error: ".$str);
                        $owlPDO->exec($str);

                        $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                        try {
                            $owlPDO->exec($queryH);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        foreach ($dataRes['detail'] as $key => $dataDet) {
                            $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                            try {
                                $owlPDO->exec($queryD);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }

                        $queryJ = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $tmpC), $whereNoindukph);
                        $errCounter = "";
                        try {
                            $owlPDO->exec($queryJ);
                        } catch (PDOException $e) {
                            $errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
                        }

                        $str = "update " . $dbname . ".kebun_tbsexternal set
								posting='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }

                break;
            case 'ADJ':
                ## APPROVAL
                try {
                    $owlPDO->beginTransaction();
                    $user_id = checkPostGet('user_id', '');
                    $nextlevelapp = checkPostGet('nextlevelapp', '');
                    if ($user_id == '') {
                        throw new PDOException("Next Approval harus dipilih");
                    }
                    if ($alasan == '') {
                        throw new PDOException("Alasan/Komentar harus diisi");
                    }

                    if ($level > 1) {
                        $levelsebelum = $level - 1;
                    } else {
                        $levelsebelum = $level;
                    }

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    ## GET HEADER RFQ
                    $str = "select * from " . $dbname . ".log_stopname_log_list where notransaksi='" . $notransaksi . "' limit 1";
                    $res = fetchdata($str);
                    $pt = $res[0]['pt'];
                    // $unit=$res[0]['unit'];
                    $dt = explode("/", $notransaksi);
                    $unit = $dt[4];

                    $arrListApp = listNextApprove($levelsebelum, $proses, $unit);
                    $total_countApp = ceklastapproval($levelsebelum, $unit, $proses, $nilai_subtotal_n);
                    // exit("warning : ".$total_countApp." - ".$level." - ".$levelsebelum." ");
                    $tgldbskrg = date("Y-m-d");

                    if ($levelsebelum == $total_countApp) {
                        // if($level==$total_countApp){

                        $str = "select * from " . $dbname . ".approval where notransaksi ='" . $notransaksi . "' and status = '0' and level != '" . $levelsebelum . "' ";
                        $res = fetchdata($str);
                        if (count($res) > 0) {
                            exit("warning : Approval untuk transkasi " . $notransaksi . " belum selesai... ");
                        }

                        // hapus approval kalau ada 2 yg sama
                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                        $owlPDO->exec($str);
                        // update approval jadi setujui
                        $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "'";
                        $owlPDO->exec($str);
                    } else {
                        // hapus approval kalau ada 2 yg sama
                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                        $owlPDO->exec($str);
                        // update approval jadi setujui
                        $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "'";
                        $owlPDO->exec($str);

                        // insert approval level berikutnya
                        $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $user_id . "')";
                        $owlPDO->exec($strx);
                    }
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warning \n" . addslashes($e->getMessage());
                }
                break;

            case 'RFQ':
                ## APPROVAL
                try {
                    $owlPDO->beginTransaction();
                    $user_id = checkPostGet('user_id', '');
                    $nextlevelapp = checkPostGet('nextlevelapp', '');
                    if ($user_id == '') {
                        throw new PDOException("Next Approval harus dipilih");
                    }
                    if ($alasan == '') {
                        throw new PDOException("Alasan/Komentar harus diisi");
                    }

                    if ($level > 1) {
                        $levelsebelum = $level - 1;
                    } else {
                        $levelsebelum = $level;
                    }

                    // exit("warning : ".$level." ");

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    ## GET HEADER RFQ
                    $str = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $notransaksi . "' limit 1";
                    $res = fetchdata($str);
                    $pt = $res[0]['pt'];
                    // $unit=$res[0]['unit'];
                    $dt = explode("/", $notransaksi);
                    $unit = $dt[4];

                    // ambil count approval
                    // cek nilai
                    $str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NILAIVPOAP' ";
                    $res = fetchdata($str);
                    $nilai_appHO = $res[0]['nilai'];

                    // bandingkan dengan supptotal dari yg di jadikan pemenang
                    $nilai_subtotal_n = 0;
                    $strx = "select harga,jumlah from " . $dbname . ".log_permintaanhargadt where nomor = '" . $notransaksi . "' and score='1' ";
                    $resx = fetchData($strx);
                    foreach ($resx as $valx) {
                        $nilai_subtotal_n += $valx['harga'] * $valx['jumlah'];
                    }

                    if ($nilai_subtotal_n >= $nilai_appHO and $unit == 'PPPE') {
                        $induk_unit = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
                        // ambil unit HO dari PT tersebut
                        $str = "select kodeorganisasi from " . $dbname . ".organisasi where tipe='HOLDING' and induk='" . $induk_unit[$unit] . "'  ";
                        $res = fetchdata($str);
                        $unit_HO = $res[0]['kodeorganisasi'];

                        $arrListApp = listNextApprove($levelsebelum, $proses, $unit_HO, $nilai_subtotal_n);

                        $total_countApp = ceklastapproval($levelsebelum, $unit_HO, $proses, $nilai_subtotal_n);
                    } else {
                        $arrListApp = listNextApprove($levelsebelum, $proses, $unit, $nilai_subtotal_n);
                        $total_countApp = ceklastapproval($levelsebelum, $unit, $proses, $nilai_subtotal_n);
                    }

                    // exit("warning : ".$total_countApp." - ".$level." - ".$levelsebelum." ");
                    $tgldbskrg = date("Y-m-d");

                    if ($levelsebelum == $total_countApp) {
                        // if($level==$total_countApp){

                        $str = "select * from " . $dbname . ".approval where notransaksi ='" . $notransaksi . "' and status = '0' and level != '" . $levelsebelum . "' ";
                        // $str = "select * from ".$dbname.".approval where notransaksi ='".$notransaksi."' and status = '0' and level != '".$level."' ";
                        $res = fetchdata($str);
                        if (count($res) > 0) {
                            exit("warning : Approval untuk transkasi " . $notransaksi . " belum selesai... ");
                        }

                        $bln = date('m');
                        $thn = date('Y');
                        $no = $bln . "/" . $thn . "/RPH";
                        $str = "select norph from " . $dbname . ".log_permintaanhargadt where norph like '%" . $no . "%' order by norph desc limit 0,1";
                        $res = fetchdata($str);
                        $dt = explode("/", $res[0]['norph']);
                        $awal = $dt[0];
                        $awal = intval($awal);
                        $cekbln = $dt[1];
                        $cekthn = $dt[2];
                        if ($thn != $cekthn) {
                            $awal = 1;
                        } else {
                            $awal += 1;
                        }
                        $counter = addZero($awal, 3);
                        $no_permintaan = $counter . "/" . $bln . "/" . $thn . "/RPH";
                        $arrPO = array();
                        $arrOrgPP = array();
                        $arrCek2 = array();
                        $arrCek3 = array();
                        $arrSubTotal = array();
                        $arrNilaiDiskon = array();
                        $arrNilaiPO = array();

                        $str = "select * from " . $dbname . ".log_permintaanhargavw where nomor='" . $notransaksi . "'";
                        $res_rph = fetchdata($str);
                        $i = 0;
                        foreach ($res_rph as $bar) {
                            $i = $i + 1;

                            $purchaser = $bar['purchaser'];
                            $supplierid = $bar['supplierid'];
                            $nodph = $bar['nomor'];
                            $nourut = $bar['nourut'];
                            $kdbrg = $bar['kodebarang'];
                            $nopp = $bar['nopp'];
                            $orgpp = substr($nopp, 15, 4);
                            $jenispp = substr($nopp, 12, 2);

                            $str = "select tipepp from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
                            $res = fetchdata($str);
                            $tipepp = $res[0]['tipepp'];

                            if ($tipepp == 'SR') {
                                $tipepo = 'SO';
                            } else if ($tipepp == 'CP') {
                                $tipepo = 'CO';
                            } else if ($tipepp == 'NR') {
                                $tipepo = 'NO';
                            } else {
                                $tipepo = 'PO';
                            }

                            $str = "select a.deliverytime,a.ppn,a.pph,a.pph22,a.matauang, a.kurs, a.ongkir as totalongkir, a.pbbkb, a.pphfinal, b.harga, b.jumlah,a.id_franco,a.sisbayar2,a.diskonpersen,b.ongkir, b.merk, b.spec, a.nilaidiskon, a.ppnjasamaterial, a.penambahpph22 from " . $dbname . ".log_perintaanhargaht a
							left join " . $dbname . ".log_permintaanhargadt b on a.nomor=b.nomor and a.nourut=b.nourut
							where a.nomor='" . $nodph . "' and a.purchaser='" . $purchaser . "' and a.supplierid='" . $supplierid . "' and a.nourut='" . $nourut . "' and b.kodebarang='" . $kdbrg . "'";
                            $res = fetchdata($str);
                            $matauang = $res[0]['matauang'];
                            $kurs = $res[0]['kurs'];
                            $pbbkb = $res[0]['pbbkb'];
                            $pphfinal = $res[0]['pphfinal'];
                            $persenppn = $res[0]['ppn'];
                            $persenpph = $res[0]['pph'];
                            $persenpph22 = $res[0]['pph22'];
                            $harga = $res[0]['harga'];
                            $jumlah = $res[0]['jumlah'];
                            $ongkir = $res[0]['ongkir'];
                            $totalongkir = $res[0]['totalongkir'];
                            $id_franco = $res[0]['id_franco'];
                            $sisbayar2 = $res[0]['sisbayar2'];
                            $deliverytime = $res[0]['deliverytime'];
                            $diskonpersen = $res[0]['diskonpersen'];
                            $ppnjasamaterial = $res[0]['ppnjasamaterial'];
                            $penambahpph22 = $res[0]['penambahpph22'];
                            $merk = $res[0]['merk'];
                            $catatan = $res[0]['spec'];
                            $rpsubtotal = ($harga * $jumlah) + $totalongkir;
                            $rppersen = 0;
                            $hargastlhdiskon = $harga;
                            if ($diskonpersen > 0) {
                                // $rppersen = $res[0]['nilaidiskon'];
                                $rppersen = $rpsubtotal * ($diskonpersen / 100);

                                ##perhitungan harga setelah diskon
                                $hargadiskon = $harga * ($diskonpersen / 100);
                                $hargastlhdiskon = $harga - $hargadiskon;
                            }
                            // $rpsubtotal = $hargapersen * $jumlah;

                            $myCek = $supplierid . "####" . $purchaser . "####" . $orgpp . "####" . $matauang . "####" . $kurs . "####" . $id_franco . "####" . $sisbayar2 . "####" . $diskonpersen . "####" . $pbbkb . "####" . $pphfinal. "####" . $jenispp;
                            $arrPO[$i]['ceknopo'] = $myCek;

                            if (in_array($myCek, $arrOrgPP)) {
                                $arrCek[$myCek] = 1;
                            } else {
                                $arrCek[$myCek] = 0;
                            }
                            $arrOrgPP[$myCek] = $myCek;

                            $ceksup = 0;
                            for ($x = 0; $x < $i; $x++) {
                                if ($arrPO[$x]['ceknopo'] == $myCek) {
                                    $ceksup++;
                                }
                            }

                            if ($ceksup <= 0) {
                                $str = "select b.tipe from " . $dbname . ".datakaryawan a
								left join " . $dbname . ".organisasi b on a.lokasitugas=b.kodeorganisasi
								where karyawanid='" . $purchaser . "'";
                                $res = fetchdata($str);

                                $optPT = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $orgpp . "'");

                                if ($jenispp == 'SR') {
                                    $poso = "SO";
                                } else {
                                    $poso = "PO";
                                }

                                if ($res[0]['tipe'] == 'HOLDING') {
                                    $localpusat = 0;
                                    $nopo = "/" . date('Y') . "/" . $poso . "-HO/" . $orgpp . "/" . $optPT[$orgpp];
                                } else {
                                    $localpusat = 1;
                                    $nopo = "/" . date('Y') . "/" . $poso . "/" . $orgpp . "/" . $optPT[$orgpp];
                                }

                                $str = "select nopo from " . $dbname . ".log_poht_del where nopo like '%" . $nopo . "%' order by length(nopo) desc, nopo desc limit 0,1";
                                $res = fetchdata($str);
                                $eksplot = explode("/", $res[0]['nopo']);
                                $awal2 = $eksplot[0];
                                $cekbln2 = $eksplot[1];
                                $cekthn2 = $eksplot[2];

                                $str = "select nopo from " . $dbname . ".log_poht where nopo like '%" . $nopo . "%' order by length(nopo) desc, nopo desc limit 0,1";
                                $res = fetchdata($str);
                                $eksplot = explode("/", $res[0]['nopo']);
                                $awal = $eksplot[0];

                                $cekbln = $eksplot[1];
                                $cekthn = $eksplot[2];

                                if ($cekthn >= $cekthn2) {
                                    $cekthn = $cekthn;
                                } else {
                                    $cekthn = $cekthn2;
                                }

                                if ($awal >= $awal2) {
                                    $awal = $awal;
                                } else {
                                    $awal = $awal2;
                                }

                                if ($thn != $cekthn) {
                                    $awal = 1;
                                } else {
                                    $awal++;
                                }
                                $counterpo = $awal;
                                if ($awal < 1000) {
                                    $counterpo = addZero($awal, 3);
                                }

                                $nopo = $counterpo . "/" . $bln . "" . $nopo;
                                $arrNoPO[$myCek] = $nopo;

                                ## CEK NILAI PPN
                                // $str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$supplierid."' and noakun='1170111' limit 1";
                                // $res=fetchdata($str);
                                // $persenppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);

                                $arrSubTotal[$myCek] = $rpsubtotal;
                                $arrNilaiDiskon[$myCek] = $rppersen;
                                // $arrNilaiPPN[$myCek] = ($persenppn/100) * ($rpsubtotal - $rppersen + $pbbkb);
                                // $arrNilaiPPH[$myCek] = ($persenpph/100) * ($rpsubtotal - $rppersen + $pbbkb);
                                if ($poso == 'SO') {
                                    $total_nilaimaterial = 0;
                                    $strx_x = "select * from " . $dbname . ".log_somaterial_perbandingan where nodph='" . $nodph . "' and supplierid='" . $supplierid . "' ";
                                    $resx_x = fetchdata($strx_x);
                                    foreach ($resx_x as $valx) {
                                        $total_nilaimaterial += $valx['jumlah'] * $valx['harga'];
                                    }

                                    // if ($ppnjasamaterial == '1') {
                                        $arrNilaiPPN[$myCek] = ($persenppn / 100) * (($total_nilaimaterial + $rpsubtotal) - $rppersen);
                                    // } else {
                                    //     $arrNilaiPPN[$myCek] = ($persenppn / 100) * ($total_nilaimaterial - $rppersen);
                                    // }
                                } else {
                                    $arrNilaiPPN[$myCek] = ($persenppn / 100) * ($rpsubtotal - $rppersen);
                                }
                                $arrNilaiPPH[$myCek] = ($persenpph / 100) * ($rpsubtotal - $rppersen);
                                $arrNilaiPPH22[$myCek] = ($persenpph22 / 100) * ($rpsubtotal - $rppersen);

                                if ($penambahpph22 == '1') {
                                    $arrNilaiPO[$myCek] = $rpsubtotal - $rppersen + $pbbkb -  $pphfinal + $arrNilaiPPN[$myCek] + $arrNilaiPPH22[$myCek] - $arrNilaiPPH[$myCek];
                                } else {
                                    $arrNilaiPO[$myCek] = $rpsubtotal - $rppersen + $pbbkb - $pphfinal + $arrNilaiPPN[$myCek] - $arrNilaiPPH22[$myCek] - $arrNilaiPPH[$myCek];
                                }

                                $str = "delete from " . $dbname . ".approval where notransaksi='" . $nopo . "' and jenispersetujuan='PO'";
                                $owlPDO->exec($str);

                                if ($persenpph > 0) {
                                    $insert_persenpph = $persenpph;
                                } else {
                                    $insert_persenpph = $persenpph22;
                                }

                                $str = "insert into " . $dbname . ".log_poht (nopo, tanggal, tgledit, kodesupplier, subtotal, ongkosangkutan, pbbkb, pphfinal, kodeorg, kodeunit, purchaser, lokalpusat, statuspo, kurs, matauang,idFranco,syaratbayar,diskonpersen,nilaidiskon,nilaipo,nodph,ppn,persenppn,pph,persenpph,deliverytime,tipepo,pph22,penambahpph22,ppnjasamaterial) values ('" . $nopo . "','" . date('Y-m-d') . "','" . date('Y-m-d') . "','" . $supplierid . "','" . $arrSubTotal[$myCek] . "','" . $totalongkir . "','" . $pbbkb . "','" . $pphfinal . "','" . $optPT[$orgpp] . "','" . $orgpp . "','" . $purchaser . "','" . $localpusat . "','0','" . $kurs . "','" . $matauang . "','" . $id_franco . "','" . $sisbayar2 . "','" . $diskonpersen . "','" . $arrNilaiDiskon[$myCek] . "','" . $arrNilaiPO[$myCek] . "','" . $no_permintaan . "','" . $arrNilaiPPN[$myCek] . "','" . $persenppn . "','" . $arrNilaiPPH[$myCek] . "','" . $insert_persenpph . "','" . $deliverytime . "','" . $tipepo . "','" . $arrNilaiPPH22[$myCek] . "','" . $penambahpph22 . "','" . $ppnjasamaterial . "')";
                                $owlPDO->exec($str);

                                $optSup = makeOption($dbname, "log_5supplier", "supplierid,namasupplier", "supplierid='" . $supplierid . "'");
                                $msgdt = "Pemenang tender untuk no RFQ : " . $no_permintaan . " adalah supplier " . $optSup[$supplierid] . ", silahkan update PO/SO dengan nomor " . $nopo;
                                $str = "insert into " . $dbname . ".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('" . $nopo . "','NRPH','" . $msgdt . "','" . $purchaser . "','0','0','" . date('Y-m-d H:i:s') . "')";
                                $owlPDO->exec($str);
                            } else {
                                ## CEK NILAI PPN
                                // $str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$supplierid."' and noakun='1170111' limit 1";
                                // $res=fetchdata($str);
                                // $persenppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);

                                $nopo = $arrNoPO[$myCek];
                                $arrSubTotal[$myCek] = $arrSubTotal[$myCek] + $rpsubtotal;
                                $arrNilaiDiskon[$myCek] = $arrNilaiDiskon[$myCek] + $rppersen;
                                // $arrNilaiPPN[$myCek] = ($persenppn/100) * ($arrSubTotal[$myCek] - $arrNilaiDiskon[$myCek] + $pbbkb);
                                $arrNilaiPPN[$myCek] = ($persenppn / 100) * ($arrSubTotal[$myCek] - $arrNilaiDiskon[$myCek]);
                                $arrNilaiPO[$myCek] = $arrSubTotal[$myCek] - $arrNilaiDiskon[$myCek] + $pbbkb + $arrNilaiPPN[$myCek] - $pphfinal;
                                $str = "update " . $dbname . ".log_poht set subtotal='" . $arrSubTotal[$myCek] . "',nilaidiskon='" . $arrNilaiDiskon[$myCek] . "',nilaipo='" . $arrNilaiPO[$myCek] . "', ppn='" . $arrNilaiPPN[$myCek] . "',persenppn='" . $persenppn . "' where nopo='" . $nopo . "'";
                                $owlPDO->exec($str);
                            }

                            $optSatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $kdbrg . "'");
                            $strx = "select satuankonversi from " . $dbname . ".log_prapodt where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "'";
                            $resx = fetchdata($strx);
                            if ($resx[0]['satuankonversi'] == '' || is_null($resx[0]['satuankonversi'])) {
                                $mySatuan = $optSatuan[$kdbrg];
                            } else {
                                $mySatuan = $resx[0]['satuankonversi'];
                            }

                            $str = "insert into " . $dbname . ".log_podt (nopo, kodebarang, jumlahpesan, hargasatuan, ongkangkut, harganormal, nopp, matauang, hargasbldiskon,idmerk,satuan,catatan) values ('" . $nopo . "','" . $kdbrg . "','" . $jumlah . "','" . $hargastlhdiskon . "','" . $ongkir . "','" . $hargastlhdiskon . "','" . $nopp . "','" . $matauang . "','" . $harga . "','" . $merk . "','" . $mySatuan . "','" . $catatan . "')";
                            $owlPDO->exec($str);

                            $str = "update " . $dbname . ".log_prapodt set create_po=1 where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "'";
                            $owlPDO->exec($str);

                            $str = "update " . $dbname . ".log_listverifikasi set pemenang='2' where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "'";
                            $owlPDO->exec($str);

                            $str = "update " . $dbname . ".log_listverifikasi set pemenang='1' where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "' and karyawanid='" . $purchaser . "'";
                            $owlPDO->exec($str);

                            $str = "update " . $dbname . ".log_permintaanhargadt set flag='1',norph='" . $no_permintaan . "',verificator='" . $_SESSION['standard']['userid'] . "',tanggalverifikasi='" . date('Y-m-d') . "' where nopp='" . $nopp . "' and kodebarang='" . $kdbrg . "' and nourut='" . $nourut . "' and nomor='" . $nodph . "'";
                            $owlPDO->exec($str);

                            // cek apakah ada material
                            $strx = "select * from " . $dbname . ".log_somaterial_perbandingan where nodph='" . $nodph . "' and supplierid='" . $supplierid . "' ";
                            $resx = fetchdata($strx);
                            foreach ($resx as $valx) {
                                // insert ke somaterial
                                $str = "insert into " . $dbname . ".log_somaterial (nopo, namabarang, jumlah, harga) values ('" . $nopo . "','" . $valx['namabarang'] . "','" . $valx['jumlah'] . "','" . $valx['harga'] . "')";
                                $owlPDO->exec($str);
                            }
                            // update pemenang perbandingan so material
                            $str = "update " . $dbname . ".log_somaterial_perbandingan set nopo='" . $nopo . "' where nodph='" . $nodph . "' and supplierid='" . $supplierid . "' ";
                            $owlPDO->exec($str);

                            // hapus approval kalau ada 2 yg sama
                            $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                            // $str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$level."' and karyawanid!='".$_SESSION['standard']['userid']."'";
                            $owlPDO->exec($str);
                            // update approval jadi setujui
                            $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "'";
                            // $str="update ".$dbname.".approval set status='1',komentar='".$alasan."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$level."'";
                            $owlPDO->exec($str);
                        }
                    } else {
                        // hapus approval kalau ada 2 yg sama
                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                        // $str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$level."' and karyawanid!='".$_SESSION['standard']['userid']."'";
                        $owlPDO->exec($str);
                        // update approval jadi setujui
                        $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $levelsebelum . "'";
                        // $str="update ".$dbname.".approval set status='1',komentar='".$alasan."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$level."'";
                        $owlPDO->exec($str);

                        // insert approval level berikutnya
                        $strx = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid) values('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $user_id . "')";
                        $owlPDO->exec($strx);
                    }
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warning \n" . addslashes($e->getMessage());
                }
                break;

            case 'PRM':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='PRM'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];
                //$countApp = getCountApproval("PRM",$kodeorg);
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='PRM' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='PRM'";
                    $owlPDO->exec($str);
                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='1' where nomorsk='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
            case 'MTS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='MTS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];
                //$countApp = getCountApproval("PRM",$kodeorg);
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='MTS' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='MTS'";
                    $owlPDO->exec($str);
                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='1' where nomorsk='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
            case 'DMS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='DMS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];
                //$countApp = getCountApproval("PRM",$kodeorg);
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='DMS' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='DMS'";
                    $owlPDO->exec($str);
                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='1' where nomorsk='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'ERF':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $strk = "select * from " . $dbname . ".sdm_req_employee where notransaksi='" . $notransaksi . "'";
                $resk = $owlPDO->query($strk) or die(print " Gagal: " . PDOException::getMessage());
                $resk->setFetchMode(PDO::FETCH_ASSOC);
                $bark = $resk->fetch();
                $kodeorg = $bark['lokasikerja'];
                $departemen = $bark['departemen'];
                $golongan = $bark['golongan'];

                $countApp = getCountApproval('ERF', $kodeorg, $departemen, $golongan);


                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='ERF' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='ERF'";
                    $owlPDO->exec($str);
                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_req_employee set statuspersetujuan='1' where notransaksi='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'PPT':
                //kembali ke file Program Training
                $_GET['proses'] = 'approved';
                include "sdm_slave_programtraining.php";
                break;
            case 'PO':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('/', $notransaksi);
                $kodeorg = $exnopo[4];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_poht set statuspo='2', stat_release='1', useridreleasae='" . $karyawanid . "', tglrelease='" . date('Y-m-d') . "' where nopo='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);

                            $str = "select spk from " . $dbname . ".log_podt where nopo='" . $notransaksi . "'";
                            $res = fetchdata($str);
                            $incspk = '0';
                            foreach ($res as $key => $val) {
                                if ($val['spk'] == '1') {
                                    $incspk = '1';
                                }
                            }

                            if ($incspk == '1') {
                                #001/EXT/LGL/BOD/BJHO/IX/2017
                                $pt = $exnopo[5];
                                $unit = $kodeorg;
                                $tempPrd = explode('-', date("Y-m-d"));
                                $str = " select notransaksi from " . $dbname . ".lgl_pengajuanspkht where pt='" . $pt . "' and unit='" . $unit . "' and tanggal like '" . $tempPrd[0] . "%' order by notransaksi desc limit 1 ";
                                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                                $res->setFetchMode(PDO::FETCH_ASSOC);
                                $bar = $res->fetch();
                                $tempNo1 = explode('/', $bar['notransaksi']);
                                if (intval($bar['notransaksi']) == 0 or intval($bar['notransaksi']) == 999) {
                                    $nomorsurat = "001";
                                } else {
                                    $nomorsurat = addZero(intval($tempNo1[0]) + 1, 3);
                                }
                                $nosrt = $nomorsurat . "/EXT/LGL/" . $pt . "/" . $unit . "/" . romawi($tempPrd[1]) . "/" . $tempPrd[0];

                                $expjenis = explode('-', $exnopo[3]);
                                if ($expjenis[1] == '') {
                                    $ktgspk = "LOKAL";
                                } else {
                                    $ktgspk = "PUSAT";
                                }

                                $optkoderrekanan = makeOption($dbname, 'log_poht', 'nopo,kodesupplier', "nopo='" . $notransaksi . "'");

                                $str = "insert into " . $dbname . ".lgl_pengajuanspkht (notransaksi,kategori,jenis,pt,unit,divisi,tanggal,koderekanan) values ('" . $nosrt . "','" . $ktgspk . "','PO/SO','" . $pt . "','" . $unit . "','" . $notransaksi . "','" . date("Y-m-d") . "','" . $optkoderrekanan[$notransaksi] . "')";
                                $owlPDO->exec($str);
                            }

                            ##UPDATE HARGA SATUAN
                            $str = "select kodebarang,hargasatuan from " . $dbname . ".log_podt where nopo='" . $notransaksi . "'";
                            $res = fetchdata($str);
                            foreach ($res as $val) {
                                $mypt = '';
                                $myunit = '';
                                $opthgstn = makeOption($dbname, 'log_5masterbarang', 'kodebarang,hargasatuan', "kodebarang='" . $val['kodebarang'] . "'");
                                if ($opthgstn[$val['kodebarang']] != '') {
                                    $exphargasatuan = explode(',', $opthgstn[$val['kodebarang']]);
                                    if (in_array($unit, $exphargasatuan)) {
                                        $mypt = $exnopo[5];
                                        $myunit = $unit;
                                    }
                                }
                                $strx = "update " . $dbname . ".log_5hargaterakhir set status='0' where unit='" . $myunit . "' and kodebarang='" . $val['kodebarang'] . "'";
                                $owlPDO->exec($strx);
                                $timex = date('Y-m-d H:i:s');
                                $tglpo = makeOption($dbname, 'log_poht', 'nopo,tanggal', "nopo='" . $notransaksi . "'");
                                $strx = "insert into " . $dbname . ".log_5hargaterakhir (pt,unit,kodebarang,tanggal,hargasatuan,status,nopo,createdby,createtime,updateby,updatetime) values ('" . $mypt . "','" . $myunit . "','" . $val['kodebarang'] . "','" . $tglpo[$notransaksi] . "','" . $val['hargasatuan'] . "','1','" . $notransaksi . "','" . $karyawanid . "','" . $timex . "','" . $karyawanid . "','" . $timex . "')";
                                $owlPDO->exec($strx);
                            }

                            // notifemailpo($notransaksi,'2',$karyawanid);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    } else {
                        $str = "select karyawanid from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . ($level + 1) . "' and jenispersetujuan='PO'";
                        $res = fetchdata($str);
                        foreach ($res as $key => $val) {
                            // notifemailpo($notransaksi,'1',$val['karyawanid']);
                        }
                        // notifemailpo($notransaksi,'2',$karyawanid);
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'GR':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_transaksiht set hasilpersetujuan1='1' where notransaksi='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'CB':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".spl_capexbangunan set kontraktor='" . $suppid . "' where kode='" . $notransaksi . "'";
                    try {
                        if ($suppid != '') {
                            $owlPDO->exec($str);

                            $str = "update " . $dbname . ".approval set status='1',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . ($level - 1) . "'";
                            $owlPDO->exec($str);
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                /*if($level!=$countApp){
                $str="update ".$dbname.".approval set status='1',komentar='".$alasan."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$level."' and karyawanid='".$karyawanid."'";
                try
                {
                $owlPDO->exec($str);

                $str="update ".$dbname.".spl_capexbangunan set kontraktor='".$suppid."' where kode='".$notransaksi."'";
                try
                {
                if ($suppid!='') {
                $owlPDO->exec($str);
                }
                }
                catch (PDOException $e)
                {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
                }
                }
                catch (PDOException $e)
                {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
                }
                }else{
                $sData="select * from ".$dbname.".spl_capexbangunan where kode='".$notransaksi."'";
                $rData=fetchData($sData);
                ## CREATE PROJECT
                //get no.project

                $iskode=explode("-",substr($rData[0]['kode'],5,13));
                $kode =substr($iskode[1],0,5);

                $unit=$rData[0]['kodeorg'];
                $tanggalmulai=$rData[0]['tanggalmulai'];
                $tanggalselesai=$rData[0]['tanggalselesai'];

                // cari nomor terakhir
                $str="select kode from ".$dbname.".project where kode like '".$kode."%' order by substring(kode, -5) desc  limit 1";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch()){
                $belakangnya=intval(substr($bar->kode,-5));
                }

                $belakangnya+=1;

                $belakangnya=addZero($belakangnya,5);
                $kode="AK-".$kode.$belakangnya;

                //inse rt project
                $str="insert into ".$dbname.".project (kode, nama, tipe, kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,keterangan,jenis_biaya,jumlah,tipebg,pekerjaan,kodecapex)
                values('".$kode."','".$rData[0]['nama']."','AK','".$rData[0]['kodeorg']."','".$rData[0]['tanggalmulai']."','".$rData[0]['tanggalselesai']."',".$karyawanid.",'".$rData[0]['subtipe']."','".$notransaksi."','2','1','".$rData[0]['tipebg']."','".$rData[0]['pekerjaan']."','".$notransaksi."')";
                try{
                $owlPDO->exec($str);
                }catch (PDOException $e) {
                $sdet="delete from ".$dbname.".project where kode='".$kode."'";
                try{$owlPDO->exec($sdet);}catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
                }
                print " Gagal  !: " . $e->getMessage() . "\n".$str; die();
                }
                $sDetData="select * from ".$dbname.".spl_capexbangunandt where kodeproject='".$notransaksi."'";
                $rDetData=fetchData($sDetData);
                foreach ($rDetData as $key => $val) {
                $str2="insert into ".$dbname.".project_dt (kegiatan, kodeproject, deskripsi, namakegiatan,tanggalmulai,tanggalselesai,satuan,volume,bobot)
                values('".$val['kegiatan']."','".$kode."','".$val['deskripsikegiatan']."','".$val['namakegiatan']."','".$val['tanggalmulai']."','".$val['tanggalselesai']."','".$val['satuan']."','".$val['volume']."','".$val['bobot']."')";
                try{
                $owlPDO->exec($str2);
                }catch (PDOException $e) {
                $sdet="delete from ".$dbname.".project_dt where kode='".$kode."'";
                try{$owlPDO->exec($sdet);
                $sdet="delete from ".$dbname.".project where kode='".$kode."'";
                try{$owlPDO->exec($sdet);}catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
                }
                }catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
                }
                print " Gagal  !: " . $e->getMessage() . "\n".$str2; die();
                }
                }
                $str3="update ".$dbname.".approval set status='1',komentar='".$alasan."',tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$level."' and karyawanid='".$karyawanid."'";
                try{
                $owlPDO->exec($str3);
                $str5="update ".$dbname.".spl_capexbangunan set kontraktor='".$suppid."' where kode='".$notransaksi."'";
                try{
                $owlPDO->exec($str5);
                }
                catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n".$str5;
                die();
                }
                }
                catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n".$str3;
                die();
                }

                }*/
                break;

            case 'SCR':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select kodeorg from " . $dbname . ".pmn_scr where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['kodeorg'];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "' and level!='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".pmn_scr set status='1' where notransaksi='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'KL':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_5klbarang set status='1' where kode='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'SKL':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_5subklbarang set status='1' where kode='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'MB':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "' and level!='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_5masterbarang set inactive='0' where kodebarang='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'DS':
                function rand_passwd($length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
                {
                    return substr(str_shuffle($chars), 0, $length);
                }
                $newpas = rand_passwd(4);
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $notransaksi . "'";
                $res = fetchdata($str);
                $perubahan1 = $res[0]['perubahan'];
                $statusx1 = $res[0]['statusyangdiinginkan'];

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);
                    if ($level == $countApp) {
                        if ($perubahan1 == '') {
                            $str = "update " . $dbname . ".log_5supplier set status='" . $statusx1 . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } else {
                            $arrperub = explode('##', $perubahan1);
                            if ($arrperub[0] != '') {
                                $str = "update " . $dbname . ".log_5supplier set status='" . $statusx1 . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }
                        }
                        $input = "select * from " . $dbname . ".log_5rekbank where supplierid = '" . $notransaksi . "'";
                        $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                        $n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d = $n->fetch()) {
                            if ($d['perubahan'] == '') {
                                $str = "update " . $dbname . ".log_5rekbank set isactive='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "' and idbank='" . $d['idbank'] . "' and matauang='" . $d['matauang'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            } else {
                                $arrperub = explode('##', $d['perubahan']);
                                #= Cek Abdul
                                // echo "<pre>";
                                // print_r($arrperub);
                                // exit('warning 23');
                                #= End Abdul
                                if ($arrperub[0] != '') {
                                    #= Abdul
                                    #= Add where rekening
                                    $str = "update " . $dbname . ".log_5rekbank set isactive='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "' and idbank='" . $d['idbank'] . "' and matauang='" . $d['matauang'] . "' and rekening='" . $d['rekening'] . "'";
                                    try {
                                        $owlPDO->exec($str);
                                    } catch (PDOException $e) {
                                        print " Gagal  !: " . $e->getMessage() . "\n";
                                        die();
                                    }
                                }
                            }
                        }

                        $input = "select * from " . $dbname . ".log_5supnpwp where supplierid = '" . $notransaksi . "'";
                        $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                        $n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d = $n->fetch()) {
                            if ($d['perubahan'] == '') {
                                $str = "update " . $dbname . ".log_5supnpwp set active='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "' and npwp='" . $d['npwp'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            } else {
                                $arrperub = explode('##', $d['perubahan']);
                                if ($arrperub[0] != '') {
                                    $str = "update " . $dbname . ".log_5supnpwp set active='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##',statusyangdiinginkan='' where supplierid='" . $notransaksi . "' and npwp='" . $d['npwp'] . "'";
                                    try {
                                        $owlPDO->exec($str);
                                    } catch (PDOException $e) {
                                        print " Gagal  !: " . $e->getMessage() . "\n";
                                        die();
                                    }
                                }
                            }
                        }

                        $input = "select * from " . $dbname . ".log_5supalamat where supplierid = '" . $notransaksi . "'";
                        $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                        $n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d = $n->fetch()) {
                            if ($d['perubahan'] == '') {
                                $str = "update " . $dbname . ".log_5supalamat set status='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##' where supplierid='" . $notransaksi . "' and id_alamat='" . $d['id_alamat'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            } else {
                                $arrperub = explode('##', $d['perubahan']);
                                if ($arrperub[0] != '') {
                                    $str = "update " . $dbname . ".log_5supalamat set status='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##' where supplierid='" . $notransaksi . "' and id_alamat='" . $d['id_alamat'] . "'";
                                    try {
                                        $owlPDO->exec($str);
                                    } catch (PDOException $e) {
                                        print " Gagal  !: " . $e->getMessage() . "\n";
                                        die();
                                    }
                                }
                            }
                        }

                        $input = "select * from " . $dbname . ".log_5pphsup where supplierid = '" . $notransaksi . "'";
                        $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                        $n->setFetchMode(PDO::FETCH_ASSOC);
                        while ($d = $n->fetch()) {
                            if ($d['perubahan'] == '') {
                                $str = "update " . $dbname . ".log_5pphsup set status='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##' where supplierid='" . $notransaksi . "' and noakun='" . $d['noakun'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            } else {
                                $arrperub = explode('##', $d['perubahan']);
                                if ($arrperub[0] != '') {
                                    $str = "update " . $dbname . ".log_5pphsup set status='" . $d['statusyangdiinginkan'] . "',statuspersetujuan='1',perubahan='##' where supplierid='" . $notransaksi . "' and noakun='" . $d['noakun'] . "'";
                                    try {
                                        $owlPDO->exec($str);
                                    } catch (PDOException $e) {
                                        print " Gagal  !: " . $e->getMessage() . "\n";
                                        die();
                                    }
                                }
                            }
                        }

                        // if($perubahan!=''){
                        //       $arrperub=explode('##', $perubahan);

                        //     $str="update " . $dbname . ".log_5supplier set namasupplier='" . $arrperub[1] . "',badanusaha='" . $arrperub[2] . "',namapemilik='" . $arrperub[3] . "',namadirektur='" . $arrperub[4] . "',namapenanggungjawab='" . $arrperub[5] . "',jabatan='" . $arrperub[6] . "',status='".$arrperub[7]."' where supplierid='" . $notransaksi . "'";
                        // try{
                        //              $owlPDO->exec($str);
                        //          }
                        //          catch (PDOException $e)
                        //          {
                        //              print " Gagal  !: " . $e->getMessage() . "\n";
                        //              die();
                        //          }

                        //     $strdelsup="delete from ".$dbname.".log_5supuser where id_supplier='".$notransaksi."'";
                        //       $owlPDO->exec($strdelsup);
                        //         try{
                        //           $owlPDO->exec($strdelsup);
                        //         }catch(PDOException $e){
                        //           echo " Gagal," . addslashes($e->getMessage());
                        //         }
                        //         $log_5supuser = "insert into " . $dbname . ".log_5supuser (id_supplier,full_name,email,password,date_reg,isactive)
                        //           values ('" . $notransaksi . "','" . $arrperub[1] . "','" . $arrperub[8] . "',PASSWORD('" . $newpas . "'),'" . date("Y-m-d") . "','1')";
                        //         try{
                        //           $owlPDO->exec($log_5supuser);
                        //         }catch(PDOException $e){
                        //           echo " Gagal," . addslashes($e->getMessage());
                        //         }

                        //       $exec = array();
                        // $datajenisusaha = array();
                        // $val_jenisusaha = explode(',',$arrperub[9]);
                        // for($i=0; $i<count($val_jenisusaha); $i++){
                        //     $d['supplierid']    = $notransaksi;
                        //     $d['tipe']             = $val_jenisusaha[$i];
                        //     $d['updateby']        = $karyawanid;
                        //               $log_5klsupplier = selectQuery($dbname,"log_5klsupplier","noakun","tipe='".$val_jenisusaha[$i]."'");
                        //               $resultlog_5klsupplier = fetchData($log_5klsupplier);
                        //               $d['noakun'] = $resultlog_5klsupplier[0]['noakun'];
                        //     $datajenisusaha[]    = $d;
                        // }
                        // $log_5supkelompok = selectQuery($dbname,"log_5supkelompok","supplierid","supplierid='".$notransaksi."'");
                        // $result = fetchData($log_5supkelompok);
                        // if(count($result) > 0){
                        //     $exec[] = deleteQuery($dbname,'log_5supkelompok',"supplierid = '".$notransaksi."'");
                        // }
                        // for($i=0; $i<count($datajenisusaha); $i++){
                        //     $col = array();
                        //     $dat = array();
                        //     foreach($datajenisusaha[$i] as $k => $val){
                        //         $col[] = $k;
                        //         $dat[] = $val;
                        //     }
                        //     $exec[] = insertQuery($dbname,'log_5supkelompok',$dat,$col);
                        // }
                        // //print_r($exec);
                        // //exit("ERROR");
                        // for($i=0; $i<count($exec); $i++){
                        //     try{
                        //         $owlPDO->exec($exec[$i]);
                        //     }catch(PDOException $e){
                        //         print " Gagal  !: " . $e->getMessage() . "\n"; die();
                        //     }
                        // }
                        // }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'HJT':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi !");
                }

                // $exnopo = explode('-',$notransaksi);
                // $kodeorg = substr($exnopo[1],0,4);

                #= dari pernomotransaksi diubah menjadi perkodeorg dan pertanggal
                #= cari kodeorg dan tanggal

                $str = "select * from " . $dbname . ".pmn_hargajualtbs where notransaksi = '" . $notransaksi . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $kodeorg = $bar['kodeorg'];

                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
				where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "delete from " . $dbname . ".approval where notransaksi
				and karyawanid='" . $karyawanid . "' and level!='" . $level . "'";
                $owlPDO->exec($str);
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                if ($level == $countApp) {
                    $str = "update " . $dbname . ".pmn_hargajualtbs set posting='1' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }
                break;

            case 'HBT':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi !");
                }

                // $exnopo = explode('-',$notransaksi);
                // $kodeorg = substr($exnopo[1],0,4);
                // $kodeorg = substr($notransaksi,8,4);
                //
                #= dari pernomotransaksi diubah menjadi perkodeorg dan pertanggal
                #= cari kodeorg dan tanggal

                $str = "select * from " . $dbname . ".pmn_hargabelitbs where notransaksi = '" . $notransaksi . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $tanggaldata = $bar['tanggal'];
                $kodeorgdata = $bar['kodeorg'];
                $tipedata = $bar['tipe'];
                $kodeorg = $bar['kodeorg'];

                $countApp = getCountApproval($proses, $kodeorg);

                $str = "select * from " . $dbname . ".pmn_hargabelitbs where tanggal = '" . $tanggaldata . "' and  kodeorg='" . $kodeorgdata . "' and tipe='" . $tipedata . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
                }
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
				where notransaksi in ('" . implode("','", $arrnotransaksi) . "')
				and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi in ('" . implode("','", $arrnotransaksi) . "')
					and karyawanid='" . $karyawanid . "' and level!='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi in ('" . implode("','", $arrnotransaksi) . "')
					and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".pmn_hargabelitbs set posting='1' where notransaksi in ('" . implode("','", $arrnotransaksi) . "')";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'BTBS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[1], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    if ($countApp > 1 && $level < $countApp) {
                        $levelstlh = $level + 1;
                        $str = "update " . $dbname . ".approval set status='0' where notransaksi='" . $notransaksi . "' and level='" . $levelstlh . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }

                    if ($level == $countApp) {
                        $sData = "select * from " . $dbname . ".keu_persediaantbs_ht where notransaksi='" . $notransaksi . "'";
                        $dataH = fetchData($sData);

                        $bonusSupplier = array();
                        $lstKlasifika = array();
                        $lstSupp = array();
                        $totPersediaan3 = 0;
                        $bonuspesediaan = 0;
                        $suppId = '';
                        #nilai rupiah
                        $sRupiah = "select kodesupplier,klasifikasi,rupiahbayar as rpbayar,totalrupiah as totRupiah,totalrupiahbonus,beban_pajak,rupiahpajak as pajak,total_terima as kgtbs, subsidi,persenpajak, harga_perkg, bonus_perkg
						          from " . $dbname . ".keu_persediaantbs_vw where notransaksi='" . $notransaksi . "'";
                        $rpData = fetchdata($sRupiah);
                        $countbebanpajak = 0;
                        foreach ($rpData as $lstData) {

                            $totPersediaan3 += ($lstData['totalrupiahbonus']);
                            $bonuspesediaan = ($lstData['bonus_perkg'] * $lstData['kgtbs']);

                            if ($lstData['beban_pajak'] == 0) {
                                $bonusSupplier[$lstData['kodesupplier'] . $lstData['klasifikasi']] += round($bonuspesediaan);
                            } else {
                                $bonusSupplier[$lstData['kodesupplier'] . $lstData['klasifikasi']] += round($bonuspesediaan);
                            }

                            $lstSupp[$lstData['kodesupplier']] = $lstData['kodesupplier'];
                            $lstKlasifika[$lstData['klasifikasi']] = $lstData['klasifikasi'];
                            $suppId = $lstData['kodesupplier'];
                            $opttr = makeOption($dbname, 'pabrik_timbangan', 'notransaksi,pengirim', "notransaksi='" . $lstData['klasifikasi'] . "'");
                            $lsttr[$opttr[$lstData['klasifikasi']]] = $opttr[$lstData['klasifikasi']];
                        }

                        ##NAMA SUPPLIER##
                        $optsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $suppId . "' in (select supplierid from " . $dbname . ".log_5supkelompok where tipe='SUPPLIERTBS')");
                        $namasupplier = $optsup[$suppId];

                        #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
                        $kodejurnal = "INVTB";
                        $tgl = str_replace("-", "", $dataH[0]['tanggal']);
                        $optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $dataH[0]['kodeunit'] . "'");
                        $whereNoindukph = "kodekelompok='" . $kodejurnal . "' and kodeorg='" . $optInduk[$dataH[0]['kodeunit']] . "'";
                        $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', $whereNoindukph);
                        $noKon = fetchData($query);
                        $tmpC = $noKon[0]['nokounter'];
                        $tmpC++;
                        $counterjurnal = addZero($tmpC, 3);
                        $nojurnal = $tgl . "/" . $dataH[0]['kodeunit'] . "/" . $kodejurnal . "/" . $counterjurnal;

                        #akun debet serta krdit
                        $query2 = selectQuery($dbname, 'keu_5parameterjurnal', 'noakundebet,noakunkredit', "jurnalid='" . $kodejurnal . "' and aktif=1");
                        $dtnoakun = fetchData($query2);

                        #=== Transform Data ===
                        $dataRes['header'] = array();
                        $dataRes['detail'] = array();

                        # Prep Header
                        $dataRes['header'] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $dataH[0]['tanggal'],
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => $totPersediaan3,
                            'totalkredit' => $totPersediaan3 * (-1),
                            'amountkoreksi' => '0',
                            'noreferensi' => $dataH[0]['notransaksi'],
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );

                        if ($bonuspesediaan != 0) {
                            $noUrut = 1;
                            $dataRes['detail'][] = array(
                                'nojurnal' => $nojurnal,
                                'tanggal' => $dataH[0]['tanggal'],
                                'nourut' => $noUrut,
                                'noakun' => $dtnoakun[0]['noakundebet'],
                                'keterangan' => 'Bonus Penerimaan TBS dari supplier ' . $namasupplier . ',pada tanggal : ' . $dataH[0]['tanggal'] . ' No Transaksi :' . $dataH[0]['notransaksi'],
                                'jumlah' => $totPersediaan3,
                                'matauang' => 'IDR',
                                'kurs' => '1',
                                'kodeorg' => $dataH[0]['kodeunit'],
                                'kodekegiatan' => '',
                                'kodeasset' => '',
                                'kodebarang' => '',
                                'nik' => '',
                                'kodecustomer' => '',
                                'kodesupplier' => '',
                                'noreferensi' => $dataH[0]['notransaksi'],
                                'noaruskas' => '',
                                'kodevhc' => '',
                                'nodok' => '',
                                'kodeblok' => '',
                                'revisi' => '0',
                                'kodesegment' => '0000000001'
                            );
                        }

                        foreach ($lstSupp as $dtSupp) {
                            foreach ($lstKlasifika as $dtKlasifikasi) {
                                if ($bonusSupplier[$dtSupp . $dtKlasifikasi] != 0) {
                                    $noUrut++;
                                    $whr = "supplierid='" . $dtSupp . "'";
                                    $optSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', $whr);
                                    $dataRes['detail'][] = array(
                                        'nojurnal' => $nojurnal,
                                        'tanggal' => $dataH[0]['tanggal'],
                                        'nourut' => $noUrut,
                                        'noakun' => $dtnoakun[0]['noakunkredit'],
                                        'keterangan' => 'Bonus Penerimaan TBS dari supplier ' . $optSupp[$dtSupp] . ',pada tanggal : ' . $dataH[0]['tanggal'] . ' No Transaksi :' . $dataH[0]['notransaksi'],
                                        'jumlah' => $bonusSupplier[$dtSupp . $dtKlasifikasi] * -1,
                                        'matauang' => 'IDR',
                                        'kurs' => '1',
                                        'kodeorg' => $dataH[0]['kodeunit'],
                                        'kodekegiatan' => '',
                                        'kodeasset' => '',
                                        'kodebarang' => '',
                                        'nik' => '',
                                        'kodecustomer' => '',
                                        'kodesupplier' => $dtSupp,
                                        'noreferensi' => $dataH[0]['notransaksi'],
                                        'noaruskas' => '',
                                        'kodevhc' => '',
                                        'nodok' => '',
                                        'kodeblok' => '',
                                        'revisi' => '0',
                                        'kodesegment' => '0000000001',
                                    );
                                }
                            }
                        }

                        #=== Insert Data ===
                        $errorDB = "";
                        # Header
                        $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                        try {
                            $owlPDO->exec($queryH);
                        } catch (PDOException $e) {
                            $errorDB .= "Gagal :Header :" . $e->getMessage();
                        }
                        # Detail
                        if ($errorDB == '') {
                            foreach ($dataRes['detail'] as $key => $dataDet) {
                                $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
                                try {
                                    $owlPDO->exec($queryD);
                                } catch (PDOException $e) {
                                    $errorDB .= "Gagal :Detail: " . $key . " " . $e->getMessage() . "\n" . $queryD;
                                }
                            }
                        }
                        if ($errorDB != '') {
                            $sDel = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'";
                            try {
                                $owlPDO->exec($sDel);
                            } catch (PDOException $e) {
                                $errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
                            }
                            echo "DB Error :\n" . $errorDB;
                            exit();
                        }
                        #=== Switch Jurnal to 1 ===
                        $queryToJ = updateQuery($dbname, 'keu_persediaantbs_ht', array('pengajuanbonus' => 1), "notransaksi='" . $notransaksi . "'");
                        try {
                            $owlPDO->exec($queryToJ);
                            $queryJ = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $tmpC + 1), $whereNoindukph);
                            $errCounter = "";
                            try {
                                $owlPDO->exec($queryJ);
                            } catch (PDOException $e) {
                                $errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
                            }

                            if ($errCounter != "") {
                                $queryJRB = updateQuery(
                                    $dbname,
                                    'keu_5kelompokjurnal',
                                    array('nokounter' => $tmpKonter[0]['nokounter']),
                                    $whereNoindukph
                                );
                                $errCounter = "";
                                try {
                                    $owlPDO->exec($queryJRB);
                                } catch (PDOException $e) {
                                    $errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
                                }
                                echo "DB Error :\n" . $errorJRB;
                                exit;
                            }
                        } catch (PDOException $e) {
                            $errorDB .= "Posting Flag Error" . $e->getMessage();
                        }
                        ##tutup level terakhir
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'CPX':
                try {
                    $owlPDO->beginTransaction();
                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }

                    $str = "select * from " . $dbname . ".log_formcapex_ht where notransaksi='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    $unit = $res[0]['unit'];
                    $tanggalmulai = $res[0]['tanggal'];
                    $budget = $res[0]['budget'];
                    $dibuat_oleh = $res[0]['dibuat_oleh'];

                    $countApp = getCountApproval($proses, $unit);

                    if ($level == $countApp) {
                        for ($arDt = 0; $arDt < $_POST['totRow']; $arDt++) {
                            if ($_POST['nama'][$arDt] == '') {
                                throw new PDOException('Nama asset harus diisi.');
                            }
                        }

                        ## CREATE ASSET
                        $str = "update " . $dbname . ".log_formcapex_ht set stat_budget='1',tgl_budget='" . date('Y-m-d') . "' where notransaksi='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "insert into " . $dbname . ".log_formcapex_assetcode values ";
                        for ($arDt = 0; $arDt < $_POST['totRow']; $arDt++) {
                            if ($arDt == 0) {
                                $str .= " ('" . $notransaksi . "','" . $_POST['kdbrg'][$arDt] . "','" . $_POST['kdasset'][$arDt] . "','" . $_POST['subasset'][$arDt] . "','" . $_POST['nama'][$arDt] . "','" . $_POST['jbiaya'][$arDt] . "')";
                            } else {
                                $str .= ",('" . $notransaksi . "','" . $_POST['kdbrg'][$arDt] . "','" . $_POST['kdasset'][$arDt] . "','" . $_POST['subasset'][$arDt] . "','" . $_POST['nama'][$arDt] . "','" . $_POST['jbiaya'][$arDt] . "')";
                            }
                        }
                        $owlPDO->exec($str);

                        ## CREATE PROJECT
                        //select log_formcapex_assetcode
                        $sasset = "select * from " . $dbname . ".log_formcapex_assetcode where notransaksi ='" . $notransaksi . "'";
                        $qasset = $owlPDO->query($sasset) or die(print "Gagal : " . PDOException::getMessage());
                        $qasset->setFetchMode(PDO::FETCH_OBJ);
                        while ($rasset = $qasset->fetch()) {
                            ## GET Jumlah
                            $strx = "select * from " . $dbname . ".log_formcapex_dt where notransaksi='" . $notransaksi . "' and kodebarang='" . $rasset->kodebarang . "'";
                            $resx = fetchdata($strx);
                            $jumlahaset = $resx[0]['jumlah'];

                            //get no.project
                            $kode = 'AK-' . $rasset->kodeasset . $rasset->subtipeasset;

                            // cari nomor terakhir
                            $str = "select kode from " . $dbname . ".project where kode like '" . $kode . "%' order by substring(kode, -5) desc  limit 1";
                            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                            $res->setFetchMode(PDO::FETCH_OBJ);
                            while ($bar = $res->fetch()) {
                                $belakangnya = intval(substr($bar->kode, -5));
                            }
                            $belakangnya += 1;

                            $belakangnya = addZero($belakangnya, 10 - strlen($rasset->kodeasset . $rasset->subtipeasset));
                            $kode = 'AK-' . $rasset->kodeasset . $rasset->subtipeasset . $belakangnya;

                            //get tanggalselesai
                            $stgleta = "SELECT * from " . $dbname . ".log_formcapex_dt where notransaksi='" . $notransaksi . "' and kodebarang='" . $rasset->kodebarang . "'";
                            $rtgleta = $owlPDO->query($stgleta) or die(print " Gagal: " . PDOException::getMessage());
                            $rtgleta->setFetchMode(PDO::FETCH_ASSOC);
                            $btgleta = $rtgleta->fetch();

                            //insert project
                            $str = "insert into " . $dbname . ".project (kode, nama, tipe, kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,keterangan,jenis_biaya,jumlah) values('" . $kode . "','" . $rasset->namaasset . "','AK','" . $unit . "','" . $tanggalmulai . "','" . $btgleta['tanggal_eta'] . "'," . $dibuat_oleh . ",'" . $rasset->subtipeasset . "','" . $notransaksi . "','" . $rasset->jenis_biaya . "','" . $jumlahaset . "')";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }

                        ## GET NO PR
                        $tgl = date('Ymd');
                        $bln = substr($tgl, 4, 2);
                        $thn = substr($tgl, 0, 4);

                        $nopp = "/" . date('Y') . "/PR/" . $unit;

                        $ql = "select `nopp` from " . $dbname . ".`log_prapoht` where nopp like '%" . $nopp . "%' order by `nopp` desc limit 0,1";
                        $qr = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
                        $qr->setFetchMode(PDO::FETCH_OBJ);
                        $rp = $qr->fetch();

                        @$awal = substr($rp->nopp, 0, 3);
                        @$awal = intval($awal);
                        @$cekbln = substr($rp->nopp, 4, 2);
                        @$cekthn = substr($rp->nopp, 7, 4);

                        //if(($bln!=$cekbln)&&($thn!=$cekthn))
                        if ($thn != $cekthn) {
                            //echo $awal; exit();
                            $awal = 1;
                        } else {
                            $awal++;
                        }

                        $counter = addZero($awal, 3);
                        $nopp = $counter . "/" . $bln . "/" . $thn . "/PR/" . $unit;

                        $getPt = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");

                        //insert log_prapoht
                        $str = "insert into " . $dbname . ".log_prapoht (kodeorg, tipepp, nopp, tanggal, dibuat,keterangan,close)
						values('" . $getPt[$unit] . "','PR','" . $nopp . "','" . $tanggalmulai . "','" . $dibuat_oleh . "','" . $notransaksi . "','2')";
                        $owlPDO->exec($str);

                        //insert log_prapodt
                        $sdt = "SELECT * from " . $dbname . ".log_formcapex_dt where notransaksi='" . $notransaksi . "'";
                        $rdt = $owlPDO->query($sdt) or die(print " Gagal: " . PDOException::getMessage());
                        $rdt->setFetchMode(PDO::FETCH_ASSOC);
                        while ($bdt = $rdt->fetch()) {
                            $str = "insert into " . $dbname . ".log_prapodt (nopp, kodebarang, jumlah,hargasatuan,keterangan,tgl_sdt,updateby)
							values('" . $nopp . "','" . $bdt['kodebarang'] . "','" . $bdt['jumlah'] . "','" . $bdt['hargasatuan'] . "','" . $bdt['catatan'] . "','" . $bdt['tanggal_eta'] . "','" . $dibuat_oleh . "')";
                            $owlPDO->exec($str);
                        }
                    }

                    $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                    try {
                        $owlPDO->exec($str);

                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "' and level!='" . $level . "'";
                        $owlPDO->exec($str);

                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                        $owlPDO->exec($str);

                        if ($level == $countApp) {
                            $str = "update " . $dbname . ".log_formcapex_ht set status_pengajuan='1' where notransaksi='" . $notransaksi . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;

            case 'CU':

                $exnopo = explode('-', $notransaksi);
                $kodeorg = $exnopo[2];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $countApp) {
                        $str = "update " . $dbname . ".log_permintaanht set statuspersetujuan='2' where notransaksi='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'DOF':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".sdm_dayoff a
				left join " . $dbname . ".approval b on a.notransaksi = b.notransaksi
				left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
				where b.jenispersetujuan='" . $proses . "' and b.status='0' and b.karyawanid='" . $_SESSION['standard']['userid'] . "' and a.notransaksi='" . $notransaksi . "' group by a.notransaksi order by b.tanggal desc";

                $res = fetchdata($str);
                $notransaksi = $res[0]['notransaksi'];
                $namakaryawan = $res[0]['namakaryawan'];
                $tanggalpengajuan = $res[0]['tanggalpengajuan'];
                $tanggalmulai = $res[0]['tanggalmulai'];
                $tanggalsampai = $res[0]['tanggalsampai'];
                $jumlahharidayoff = $res[0]['jumlahharidayoff'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='DOF'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='DOF' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='DOF'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_dayoff set status='1' where notransaksi='" . $notransaksi . "' ";
                        // echo $strspl;
                        // exit('error');
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
            case 'DOFNS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select a.*, b.*, c.namakaryawan, c.lokasitugas from " . $dbname . ".sdm_dayoff a
				left join " . $dbname . ".approval b on a.notransaksi = b.notransaksi
				left join " . $dbname . ".datakaryawan c on a.karyawanid = c.karyawanid
				where b.jenispersetujuan='" . $proses . "' and b.status='0' and b.karyawanid='" . $_SESSION['standard']['userid'] . "' and a.notransaksi='" . $notransaksi . "' group by a.notransaksi order by b.tanggal desc";

                $res = fetchdata($str);
                $notransaksi = $res[0]['notransaksi'];
                $namakaryawan = $res[0]['namakaryawan'];
                $tanggalpengajuan = $res[0]['tanggalpengajuan'];
                $tanggalmulai = $res[0]['tanggalmulai'];
                $tanggalsampai = $res[0]['tanggalsampai'];
                $jumlahharidayoff = $res[0]['jumlahharidayoff'];

                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and jenispersetujuan='DOFNS' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and jenispersetujuan='DOFNS'";
                    $owlPDO->exec($str);

                    if ($level == $countApp) {

                        $strspl = "update " . $dbname . ".sdm_dayoff set status='1' where notransaksi='" . $notransaksi . "' ";
                        // echo $strspl;
                        // exit('error');
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'PJDINAS':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";

                $strorg = "select kodeorg from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                $resorg = fetchData($strorg);
                $barorg = $resorg[0];

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and karyawanid='" . $karyawanid . "' and kodeunit='" . $barorg['kodeorg'] . "' ";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];

                /*if($level==$levelakhir){
                $str = "insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
                ('".$notransaksi."','".$jenispersetujuan."','".$level."','".$karyawanid."','1','".$alasan."','".$tglskrng."')";
                }*/

                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        //data kodeorg
                        $str = "select kodeorg,uangmuka,karyawanid from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                        $res = fetchData($str);
                        $bar = $res[0];
                        $kodeorg = $bar['kodeorg'];
                        $uangmuka = $bar['uangmuka'];
                        $karyawanid = $bar['karyawanid'];

                        if ($uangmuka == 0) {
                            exit();
                        }

                        //data create tagihan
                        $strup = "select karyawanid from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='" . $jenispersetujuan . "' and level=3";
                        $resup = fetchData($strup);
                        $barup = $resup[0];
                        $create = $barup['karyawanid'];

                        //get induk
                        $sqlkd = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorg . "'";
                        $ressup = $owlPDO->query($sqlkd);
                        $ressup->setFetchMode(PDO::FETCH_ASSOC);
                        $barsup = $ressup->fetch();
                        $induk = $barsup['induk'];

                        $kodejurnal = 'UMPD';
                        //Parameter jurnal noakun debet dan kredit
                        $strpj = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "'";
                        $respj = fetchData($strpj);
                        $barpj = $respj[0];
                        $noakundebet = $barpj['noakundebet'];
                        $noakunkredit = $barpj['noakunkredit'];

                        //get noaruskas
                        $str1 = "select noaruskas from " . $dbname . ".keu_5aruskas_detail where noakun='" . $noakundebet . "'";
                        $qtr = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $qtr->setFetchMode(PDO::FETCH_ASSOC);
                        $rtr = $qtr->fetch();
                        $noaruskas = $rtr['noaruskas'];

                        //get keterangan
                        $str1 = "select id_ket from " . $dbname . ".keu_5keterangan where noaruskas='" . $noaruskas . "'";
                        $qtr = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $qtr->setFetchMode(PDO::FETCH_ASSOC);
                        $rtr = $qtr->fetch();
                        $keterangantemp = $rtr['id_ket'];

                        $noinvoice = date('Ymdhis');
                        $tipeinvoice = 'upd';
                        $keterangan = "Uang Muka Perjalanan Dinas berdasarkan notransaksi: " . $notransaksi . "";

                        $insht = "insert into " . $dbname . ".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values
		                ('" . $noinvoice . "','" . $tipeinvoice . "','" . date('Y-m-d') . "','" . $notransaksi . "','" . $karyawanid . "','" . $uangmuka . "','','" . $keterangan . "','" . $noakunkredit . "','IDR','1','1','" . $induk . "','" . $kodeorg . "','" . $create . "','" . $create . "')";
                        try {
                            $owlPDO->exec($insht);

                            $ins = "insert into " . $dbname . ".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values
		                      ('" . $noinvoice . "','" . $noakundebet . "','" . $uangmuka . "','','','" . $noaruskas . "','" . $keterangantemp . "')";
                            try {
                                $owlPDO->exec($ins);
                            } catch (PDOException $e) {
                                print " Gagal: " . $e->getMessage() . "\n";
                                die();
                            }
                        } catch (PDOException $e) {
                            print " Gagal: " . $e->getMessage() . "\n";
                            die();
                        }

                        //             $kodejurnal="TGH01";
                        //             $tgljurnal=date('Ymd');

                        //             # Get Journal Counter
                        //             $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        //                 "kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
                        //             $tmpKonter = fetchData($queryJ);
                        //             $konter = addZero($tmpKonter[0]['nokounter']+1,3);
                        //             # Prep No Jurnal
                        //             $notrans=$tgljurnal."/".$bar['kodeorg']."/".$kodejurnal."/".$konter;

                        //       //insert jurnalht
                        // $strht="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) values
                        //         ('".$notrans."','".$kodejurnal."','".$uangmuka."','".-($uangmuka)."','".$tgljurnal."','".date('Ymd')."','1','".$noinvoice."','IDR','1')";
                        // try
                        //          {
                        //              $owlPDO->exec($strht);

                        //     //insert jurnalht debet
                        //           $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,nik)
                        //           values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','Uang Muka Perjalanan Dinas berdasarkan noinvoice:".$noinvoice." dan notransaksi:".$notransaksi.";','".$uangmuka."','IDR','1','".$kodeorg."','".$noinvoice."','".$notransaksi."','".$karyawanid."')";
                        //           try
                        //           {
                        //               $owlPDO->exec($str);
                        //           }
                        //           catch (PDOException $e)
                        //           {
                        //               print " Gagal  !: " . $e->getMessage() . "\n";
                        //               die();
                        //           }

                        //           //insert jurnalht kredit
                        //           $str="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok,nik)
                        //           values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','Jurnal Otomatis Atas Uang Muka Perjalanan Dinas berdasarkan noinvoice:".$noinvoice." dan notransaksi:".$notransaksi.";','".-($uangmuka)."','IDR','1','".$kodeorg."','".$noinvoice."','".$notransaksi."','".$karyawanid."')";
                        //           try
                        //           {
                        //               $owlPDO->exec($str);
                        //           }
                        //           catch (PDOException $e)
                        //           {
                        //               print " Gagal  !: " . $e->getMessage() . "\n";
                        //               die();
                        //           }

                        //       }catch (PDOException $e){
                        //              print " Gagal  !: " . $e->getMessage() . "\n";
                        //              die();
                        //          }

                        //          $strht="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
                        //                try{
                        //                    $owlPDO->exec($strht);
                        //                }catch (PDOException $e){
                        //                    echo "Gagal : ".$e->getMessage();
                        //                    die();
                        //                }

                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'PJDTAMU':

                $levelakhir = getCountApproval($jenispersetujuan);
                $strorg = "select kodeorg from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                $resorg = fetchData($strorg);
                $barorg = $resorg[0];

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and kodeunit='" . $barorg['kodeorg'] . "' ";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $levelup = $level + 1;

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and status='0'";
                    try {
                        $owlPDO->exec($strx);
                        #mailCoy($user_id);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    if ($levelup == $levelap) {
                        $str = "insert into " . $dbname . ".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
						  ('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $karyawanid . "','1','" . $alasan . "','" . $tglskrng . "')";

                        $tglapp = date('y-m-d h:i:s');
                        $strkry = "select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='7' and bagian='BOD'";
                        $reskry = $owlPDO->query($strkry) or die(print " Gagal: " . PDOException::getMessage());
                        $reskry->setFetchMode(PDO::FETCH_ASSOC);
                        while ($barkry = $reskry->fetch()) {
                            # insert ke table approval
                            $strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
									`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
									 values ('','" . $notransaksi . "','" . $jenispersetujuan . "','" . $levelap . "','" . $barkry['karyawanid'] . "','0','','','" . $tglapp . "')";
                            try {
                                $owlPDO->exec($strapp);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    }

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'SOP':

                $levelakhir = getCountApproval($jenispersetujuan);
                $strorg = "select kodeorg from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                $resorg = fetchData($strorg);
                $barorg = $resorg[0];

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' ";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];
                $levelup = $level + 1;

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and status='0'";
                    try {
                        $owlPDO->exec($strx);
                        #mailCoy($user_id);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    if ($levelup == $levelap) {
                        $str = "insert into " . $dbname . ".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
						  ('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $karyawanid . "','1','" . $alasan . "','" . $tglskrng . "')";

                        $tglapp = date('y-m-d h:i:s');
                        $strkry = "select karyawanid from " . $dbname . ".datakaryawan where tipekaryawan='7' and bagian='BOD'";
                        $reskry = $owlPDO->query($strkry) or die(print " Gagal: " . PDOException::getMessage());
                        $reskry->setFetchMode(PDO::FETCH_ASSOC);
                        while ($barkry = $reskry->fetch()) {
                            # insert ke table approval
                            $strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
									`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
									 values ('','" . $notransaksi . "','" . $jenispersetujuan . "','" . $levelap . "','" . $barkry['karyawanid'] . "','0','','','" . $tglapp . "')";
                            try {
                                $owlPDO->exec($strapp);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    }

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".sdm_sopht set close='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'PROJ':

                $strb = "select level from " . $dbname . ".approval where  notransaksi='" . $notransaksi . "' and jenispersetujuan='" . $proses . "' and karyawanid='" . $karyawanid . "'";
                $resb = $owlPDO->query($strb) or die(print " Gagal: " . PDOException::getMessage());
                $resb->setFetchMode(PDO::FETCH_ASSOC);
                $barb = $resb->fetch();
                $level = $barb['level'];

                #= CARI PERSETUJUAN TERAKHIR
                $stra = "select max(level) as level from " . $dbname . ".approval where  notransaksi='" . $notransaksi . "' and jenispersetujuan='" . $proses . "' and karyawanid!='0000000000'";
                $res = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $lvlakhirPROJ = $bar['level'];

                // exit('error'.$lvlakhirPROJ);
                $levelakhir = $lvlakhirPROJ;
                $strorg = "select kodeorg from " . $dbname . ".project where kode='" . $notransaksi . "'";
                $resorg = fetchData($strorg);
                $barorg = $resorg[0];

                // $strap="select level from ".$dbname.".approval where jenispersetujuan='".$proses."' ";
                // $res=$owlPDO->query($strap) or die(print " Gagal: ".PDOException::getMessage());
                // $res->setFetchMode(PDO::FETCH_ASSOC);
                // $bar=$res->fetch();
                // $levelap=$bar['level'];
                // $levelup=$level+1;

                // echo $lvlakhirPROJ;
                // exit('error');
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";

                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' and status='0'";
                    try {
                        $owlPDO->exec($strx);
                        #mailCoy($user_id);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    // if($levelup==$levelap){
                    //     $str = "insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
                    //       ('".$notransaksi."','".$jenispersetujuan."','".$level."','".$karyawanid."','1','".$alasan."','".$tglskrng."')";

                    //     $tglapp=date('y-m-d h:i:s');
                    //     $strkry="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='7' and bagian='BOD'";
                    //     $reskry=$owlPDO->query($strkry) or die(print " Gagal: ".PDOException::getMessage());
                    //     $reskry->setFetchMode(PDO::FETCH_ASSOC);
                    //     while($barkry=$reskry->fetch()){
                    //         # insert ke table approval
                    //         $strapp = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
                    //                 `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
                    //                  values ('','".$notransaksi."','".$jenispersetujuan."','".$levelap."','".$barkry['karyawanid']."','0','','','".$tglapp."')";
                    //         try {
                    //             $owlPDO->exec($strapp);

                    //         } catch (PDOException $e) {
                    //             print " Gagal  !: " . $e->getMessage() . "\n";die();
                    //         }
                    //     }
                    // }

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".project set statuspersetujuan='1' where kode='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        #== INSERT KE TABLE APPROVAL PROJECT

                        $strApr = "SELECT * FROM approval WHERE notransaksi='" . $notransaksi . "' and karyawanid!='0000000000'";
                        $res = fetchdata($strApr);
                        foreach ($res as $key => $val) {
                            $kode = $val['notransaksi'];
                            $level = $val['level'];
                            $karyawanid = $val['karyawanid'];

                            $strapp = "INSERT INTO " . $dbname . ".project_approval (`kode`,`level`, `karyawanid`, `createby`, `createtime`)
									values ('" . $kode . "','" . $level . "','" . $karyawanid . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
                            try {
                                $owlPDO->exec($strapp);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    } else {
                        $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'PJDINASNS':

                $levelakhir = getCountApproval($jenispersetujuan);
                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                $levelup = $level + 1;

                $strorg = "select kodeorg from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                $resorg = fetchData($strorg);
                $barorg = $resorg[0];

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and karyawanid='" . $karyawanid . "' and kodeunit='" . $barorg['kodeorg'] . "' ";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];

                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {

                        $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='1' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        //data kodeorg
                        $str = "select kodeorg,uangmuka,karyawanid from " . $dbname . ".sdm_pjdinasht where notransaksi='" . $notransaksi . "'";
                        $res = fetchData($str);
                        $bar = $res[0];
                        $kodeorg = $bar['kodeorg'];
                        $uangmuka = $bar['uangmuka'];
                        $karyawanid = $bar['karyawanid'];

                        if ($uangmuka == 0) {
                            exit();
                        }

                        //data create tagihan
                        $strup = "select karyawanid from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='" . $jenispersetujuan . "' and level=3";
                        $resup = fetchData($strup);
                        $barup = $resup[0];
                        $create = $barup['karyawanid'];

                        //get induk
                        $sqlkd = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeorg . "'";
                        $ressup = $owlPDO->query($sqlkd);
                        $ressup->setFetchMode(PDO::FETCH_ASSOC);
                        $barsup = $ressup->fetch();
                        $induk = $barsup['induk'];

                        $kodejurnal = 'UMPD';
                        //Parameter jurnal noakun debet dan kredit
                        $strpj = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "'";
                        $respj = fetchData($strpj);
                        $barpj = $respj[0];
                        $noakundebet = $barpj['noakundebet'];
                        $noakunkredit = $barpj['noakunkredit'];

                        //get noaruskas
                        $str1 = "select noaruskas from " . $dbname . ".keu_5aruskas_detail where noakun='" . $noakundebet . "'";
                        $qtr = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $qtr->setFetchMode(PDO::FETCH_ASSOC);
                        $rtr = $qtr->fetch();
                        $noaruskas = $rtr['noaruskas'];

                        //get keterangan
                        $str1 = "select id_ket from " . $dbname . ".keu_5keterangan where noaruskas='" . $noaruskas . "'";
                        $qtr = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                        $qtr->setFetchMode(PDO::FETCH_ASSOC);
                        $rtr = $qtr->fetch();
                        $keterangantemp = $rtr['id_ket'];

                        $noinvoice = date('Ymdhis');
                        $tipeinvoice = 'upd';
                        $keterangan = "Uang Muka Perjalanan Dinas berdasarkan notransaksi: " . $notransaksi . "";

                        $insht = "insert into " . $dbname . ".keu_tagihanht(noinvoice, tipeinvoice, tanggal, nopo, kodesupplier, nilaiinvoice, keterangan, keterangan2, noakun, matauang, kurs, posting, kodeorg, unit, updateby, postingby) values
		                ('" . $noinvoice . "','" . $tipeinvoice . "','" . date('Y-m-d') . "','" . $notransaksi . "','" . $karyawanid . "','" . $uangmuka . "','','" . $keterangan . "','" . $noakunkredit . "','IDR','1','1','" . $induk . "','" . $kodeorg . "','" . $create . "','" . $create . "')";
                        try {
                            $owlPDO->exec($insht);

                            $ins = "insert into " . $dbname . ".keu_tagihandt(noinvoice, noakun, nilai, kodevhc, kodeasset,noaruskas,keterangan) values
		                      ('" . $noinvoice . "','" . $noakundebet . "','" . $uangmuka . "','','','" . $noaruskas . "','" . $keterangantemp . "')";
                            try {
                                $owlPDO->exec($ins);
                            } catch (PDOException $e) {
                                print " Gagal: " . $e->getMessage() . "\n";
                                die();
                            }
                        } catch (PDOException $e) {
                            print " Gagal: " . $e->getMessage() . "\n";
                            die();
                        }
                    } else {
                        $strspl = "update " . $dbname . ".approval set status='0' where notransaksi='" . $notransaksi . "' and level='" . $levelup . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'SP':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];

                if ($level == $levelap) {
                    $str = "insert into " . $dbname . ".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
					  ('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $karyawanid . "','1','" . $alasan . "','" . $tglskrng . "')";
                }

                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".sdm_pengajuanspht set statuspersetujuan='1' where nopengajuan='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'JM':

                # Get Kodeorg
                $sql = selectQuery($dbname, "keu_jurnalmemorial", "kodeorg", "nojurnal='" . $param['notransaksi'] . "'");
                $res = fetchData($sql);

                $levelakhir = getCountApproval($jenispersetujuan, $res[0]['kodeorg']);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                try {
                    $owlPDO->beginTransaction();

                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".keu_jurnalht set autojurnal='0' where nojurnal='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($strspl);

                            $strjm = "update " . $dbname . ".keu_jurnalmemorial set posting='1' where nojurnal='" . $notransaksi . "'";

                            try {
                                $owlPDO->exec($strjm);

                                try {
                                    $table = 'keu_jurnalmemorial';
                                    $tabledt = 'keu_jurnalmemorialdt';

                                    #= ht
                                    $str = "select * from " . $dbname . "." . $table . " where nojurnal='" . $notransaksi . "'";
                                    $res = fetchdata($str);
                                    foreach ($res as $bar) {
                                        $strins = "insert into " . $dbname . ".keu_jurnalht
										(nojurnal,kodejurnal,tanggal,tanggalentry,noreferensi,
										 matauang,kurs,autojurnal)
										values
										('" . $bar['nojurnal'] . "','M','" . $bar['tanggal'] . "','" . date('Ymd') . "','" . $bar['noreferensi'] . "',
										'" . $bar['matauang'] . "','" . $bar['kurs'] . "','1')";
                                        $owlPDO->exec($strins);
                                    }
                                    #= dt
                                    $str = "select * from " . $dbname . "." . $tabledt . " where nojurnal='" . $notransaksi . "'";
                                    $res = fetchdata($str);
                                    foreach ($res as $bar) {
                                        $strins = "insert into " . $dbname . ".keu_jurnaldt
										(nojurnal,tanggal,nourut,noakun,keterangan,
										 jumlah,matauang,kurs,kodeorg,kodekegiatan,
										 kodeasset,kodebarang,nik,kodecustomer,kodesupplier,
										 noreferensi,noaruskas,kodevhc,nodok,kodeblok,
										 revisi,kodesegment)
										values
										('" . $bar['nojurnal'] . "','" . $bar['tanggal'] . "','" . $bar['nourut'] . "','" . $bar['noakun'] . "','" . $bar['keterangan'] . "',
										'" . $bar['jumlah'] . "','" . $bar['matauang'] . "','" . $bar['kurs'] . "','" . $bar['kodeorg'] . "','" . $bar['kodekegiatan'] . "',
										'" . $bar['kodeasset'] . "','" . $bar['kodebarang'] . "','" . $bar['nik'] . "','" . $bar['kodecustomer'] . "','" . $bar['kodesupplier'] . "',
										'" . $bar['noreferensi'] . "','" . $bar['noaruskas'] . "','" . $bar['kodevhc'] . "','" . $bar['nodok'] . "','" . $bar['kodeblok'] . "',
										'" . $bar['revisi'] . "','" . $bar['kodesegment'] . "')";
                                        $owlPDO->exec($strins);
                                    }
                                } catch (PDOException $e) {

                                    $owlPDO->rollback();
                                    echo "Warning: Gagal melakukan approval \n" . addslashes($e->getMessage());
                                }
                            } catch (PDOException $e) {
                                print " Gagal Update JM !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'PKSCUCITANGKI':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                try {
                    $owlPDO->exec($str);
                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".pabrik_pembersihantangki set posting='1' where
		            	notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "select * from " . $dbname . ".pabrik_pembersihantangki where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str)[0];

                $subject = "Persetujuan Pengajuan Cuci Tangki";
                $body = "Dear Bapak / Ibu,
						<br>
						<br>
						Melalui email ini kami beritahukan bahwa pengajuan pencucian tangki sebagai berikut:<br><br>
						Notransaksi : " . $notransaksi . "<br><br>
						Pabrik : " . getNamaOrg($res['kodeorg']) . "<br><br>
						Tangki : " . $res['kodetangki'] . "<br><br>
						Keterangan : " . $res['keterangan'] . "<br><br>

						Demikian disampaikan, terima kasih.<br><br>
						";

                $str = "select * from " . $dbname . ".setup_notification_dt where kodejenis='PKSCTANGKI'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $to = getUserEmail($bar['karyawanid']);
                    $cc = "";
                    kirimEmail($to, $cc, $subject, $body);
                }

                break;

            case 'PKSBACUCITANGKI':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                try {
                    $owlPDO->exec($str);
                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".pabrik_pembersihantangki set postingba='1' where
		            	noba='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "select * from " . $dbname . ".pabrik_pembersihantangki where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str)[0];

                $subject = "Persetujuan Cuci Tangki";
                $body = "Dear Bapak / Ibu,
						<br>
						<br>
						Melalui email ini kami beritahukan bahwa pencucian tangki sebagai berikut:<br><br>
						Notransaksi : " . $notransaksi . "<br><br>
						Pabrik : " . getNamaOrg($res['kodeorg']) . "<br><br>
						Tangki : " . $res['kodetangki'] . "<br><br>
						Keterangan : " . $res['keteranganba'] . "<br><br>

						Demikian disampaikan, terima kasih.<br><br>
						";

                $str = "select * from " . $dbname . ".setup_notification_dt where kodejenis='PKSCTANGKI'";
                $res = fetchdata($str);
                foreach ($res as $bar) {
                    $to = getUserEmail($bar['karyawanid']);
                    $cc = "";
                    kirimEmail($to, $cc, $subject, $body);
                }

                break;

            case 'PKSMAINTENANCE':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".pabrik_rawatmesinht set statuspersetujuan='1' where
		            	notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'BKCK':

                $levelakhir = getCountApproval($jenispersetujuan);

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . ".keu_bukucekdt set status_cek='2' where notrans_cek='" . $notransaksi . "' and status_cek='0' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'DISPO':

                $levelakhir = getCountApproval($jenispersetujuan);

                $strap = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";

                try {
                    $owlPDO->exec($strap);

                    if ($level == $levelakhir) {

                        $strspl = "update " . $dbname . ".keu_disposalasset set statuspersetujuan='1',tanggalproses='" . $tanggaldispo . "' where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        //get data disposal asset
                        $str = "select * from " . $dbname . ".keu_disposalasset where notransaksi='" . $notransaksi . "'";
                        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        $bar = $res->fetch();
                        $nilaibuku = $bar['nilaibuku'];
                        $kodeasset = $bar['kodeasset'];
                        $akumulasipenyusutan = $bar['akumulasipenyusutan'];
                        $jenisket = $bar['jenisket'];

                        //get data asset
                        $strka = "select * from " . $dbname . ".sdm_daftarasset where kodeasset='" . $kodeasset . "'";
                        $reska = $owlPDO->query($strka) or die(print " Gagal: " . PDOException::getMessage());
                        $reska->setFetchMode(PDO::FETCH_ASSOC);
                        $barka = $reska->fetch();
                        $hargaperolehan = $barka['hargaperolehan'];

                        //get akun gain
                        $strpa = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='DISPOGAIN'";
                        $respa = $owlPDO->query($strpa) or die(print " Gagal: " . PDOException::getMessage());
                        $respa->setFetchMode(PDO::FETCH_ASSOC);
                        $barpa = $respa->fetch();
                        $akgain = $barpa['nilai'];

                        $tipejr = 0;
                        if ($nilaibuku == 0) {

                            if ($jenisket == 11) {
                                $tipejr = 1;
                                $jumlahgain = $nilaibuku;
                                $jumlahap = $nilaibuku;
                                $jumlahtot = $nilaibuku;
                                $nourutjurnal = 2;
                                $nourutjurnal2 = 1;
                            } else {
                                $tipejr = 2;
                                $jumlahtot = $akumulasipenyusutan;
                                $jumlahka = $akumulasipenyusutan;
                                $jumlahap = $akumulasipenyusutan;
                                $nourutjurnal = 2;
                            }
                        } else {

                            if ($jenisket == 11) {
                                $tipejr = 1;
                                $jumlahgain = $nilaibuku;
                                $jumlahap = $nilaibuku;
                                $jumlahtot = $nilaibuku;
                                $nourutjurnal = 2;
                                $nourutjurnal2 = 1;
                            } else {
                                if ($nilaibuku < $hargaperolehan) {
                                    $tipejr = 3;
                                    $jumlahka = $akumulasipenyusutan;
                                    $jumlahgain = $nilaibuku;
                                    $jumlahap = $hargaperolehan;
                                    $jumlahtot = $hargaperolehan;
                                    $nourutjurnal = 3;
                                    $nourutjurnal2 = 2;
                                }
                            }
                        }

                        //update daftar asset
                        $stras = "update " . $dbname . ".sdm_daftarasset set tanggaldisposal='" . $tanggaldispo . "',status='" . $bar['jenisket'] . "' where kodeasset='" . $kodeasset . "' ";
                        try {
                            $owlPDO->exec($stras);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

                        //get noakun debet kredit
                        $ressup = $owlPDO->query("select jurnalid,noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='DIS" . substr($kodeasset, 4, 2) . "'");
                        $ressup->setFetchMode(PDO::FETCH_ASSOC);
                        $barsup = $ressup->fetch();
                        $kodejurnal = $barsup['jurnalid'];
                        $akdebet = $barsup['noakundebet'];
                        $akkredit = $barsup['noakunkredit'];
                        $tgljurnal = str_replace('-', '', $tanggaldispo);
                        $bar['kodeorg'] = substr($notransaksi, 0, 4);
                        $induk = substr($kodeasset, 0, 3);
                        // $keterangan2='pengakuan hutang '.strtolower($bar['tipe_transaksi']).' atas '.$optsup[$bar['supplierid']].'/'.$bar['keterangan'];

                        # Get Journal Counter
                        $queryJ = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $induk . "' and kodekelompok='" . $kodejurnal . "'");
                        $tmpKonter = fetchData($queryJ);
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
                        # Prep No Jurnal
                        $notrans = $tgljurnal . "/" . $bar['kodeorg'] . "/" . $kodejurnal . "/" . $konter;

                        //insert jurnal
                        $i = "insert into " . $dbname . ".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs)
		                values ('" . $notrans . "','" . $kodejurnal . "','" . $jumlahtot . "','" . (-1) * ($jumlahtot) . "','" . $tgljurnal . "','" . date('Ymd') . "','1','" . $notransaksi . "','IDR','1')";
                        try {
                            $owlPDO->exec($i);

                            if ($tipejr == 2 || $tipejr == 3) {
                                $i = "insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi)
			                    values ('" . $notrans . "','" . $tgljurnal . "','1','" . $akdebet . "','" . $keterangan2 . "','" . $jumlahka . "','IDR','1','" . $bar['kodeorg'] . "','" . $notransaksi . "')";
                                try {
                                    $owlPDO->exec($i);
                                } catch (PDOException $e) {
                                    print " Gagal: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }

                            if ($tipejr == 1 || $tipejr == 3) {
                                $i = "insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi)
			                    values ('" . $notrans . "','" . $tgljurnal . "','" . $nourutjurnal2 . "','" . $akgain . "','" . $keterangan2 . "','" . $jumlahgain . "','IDR','1','" . $bar['kodeorg'] . "','" . $notransaksi . "')";
                                try {
                                    $owlPDO->exec($i);
                                } catch (PDOException $e) {
                                    print " Gagal: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }

                            $i = "insert into " . $dbname . ".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi)
		                    values ('" . $notrans . "','" . $tgljurnal . "','" . $nourutjurnal . "','" . $akkredit . "','" . $keterangan2 . "','" . (-1) * ($jumlahap) . "','IDR','1','" . $bar['kodeorg'] . "','" . $notransaksi . "')";
                            try {
                                $owlPDO->exec($i);
                            } catch (PDOException $e) {
                                print " Gagal: " . $e->getMessage() . "\n";
                                die();
                            }

                            $strht = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where kodeorg='" . $induk . "' and kodekelompok='" . $kodejurnal . "'";
                            try {
                                $owlPDO->exec($strht);
                            } catch (PDOException $e) {
                                echo "Gagal : " . $e->getMessage();
                                die();
                            }
                        } catch (PDOException $e) {
                            print " Gagal: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
            case 'CVMM':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $data = array(
                    'status' => '1',
                    'komentar' => $param['alasan'],
                    'tanggal' => date("Y-m-d H:i:s"),
                );
                $where = "notransaksi = '" . $param['notransaksi'] . "' and jenispersetujuan='CVMM' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                $query = updateQuery($dbname, 'approval', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                $data = array(
                    'approval' => '1',
                    'updatetime' => date("Y-m-d H:i:s"),
                    'updateby' => $_SESSION['standard']['userid'],
                );
                $where = "id = '" . $param['notransaksi'] . "'";
                $query = updateQuery($dbname, 'sdm_corevalueandmanmanagement', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                break;
            case 'PAS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $data = array(
                    'status' => '1',
                    'komentar' => $param['alasan'],
                    'tanggal' => date("Y-m-d H:i:s"),
                );
                $where = "notransaksi = '" . $param['notransaksi'] . "' and jenispersetujuan='PAS' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                $query = updateQuery($dbname, 'approval', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                $data = array(
                    'approval' => '1',
                    'updatetime' => date("Y-m-d H:i:s"),
                    'updateby' => $_SESSION['standard']['userid'],
                );
                $where = "id = '" . $param['notransaksi'] . "'";
                $query = updateQuery($dbname, 'sdm_corevalueandmanmanagement', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                break;

            case 'HFTBS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $karyawanid = $_SESSION['standard']['userid'];

                $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                $res = fetchData($str);
                $countApp = $res[0]['countApp'];

                $tglskrng = date("Y-m-d H:i:s");
                $str = "UPDATE " . $dbname . ".approval SET status='1', komentar='" . $alasan . "', tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                $counterappverror = 0;

                #= cek kalau tanggal masih kosong
                $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "'
						AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                $res = fetchdata($str);

                foreach ($res as $bar) {
                    if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                        #= roll back approval pertama
                        $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "'
									WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($strroll);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                        $counterappverror++;
                    }
                }

                // exit("Error:$counterappverror");
                if ($counterappverror > 0) {
                    exit("Warning: Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi);
                }

                if ($level == $countApp) {
                    #= bentuk query data untuk posting
                    $str = "SELECT * FROM " . $dbname . ".pmn_5feetbs WHERE notransaksi = '" . $notransaksi . "'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar = $res->fetch();

                    $str = "UPDATE " . $dbname . ".pmn_5feetbs SET posting = '1' WHERE notransaksi = '" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

                break;

            case 'FTBS':
                try {
                    $owlPDO->beginTransaction();

                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }

                    $expnotran = explode('/', $notransaksi);
                    $kodeorg = $expnotran[1];
                    $karyawanid = $_SESSION['standard']['userid'];

                    $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                    $res = fetchData($str);
                    $countApp = $res[0]['countApp'];

                    $tglskrng = date("Y-m-d H:i:s");
                    $str = "UPDATE " . $dbname . ".approval SET status='1', komentar='" . $alasan . "', tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                    // try {
                    $owlPDO->exec($str);
                    // }
                    // catch (PDOException $e) {
                    //     print " Gagal  !: " . $e->getMessage() . "\n";
                    //     die();
                    // }
                    $counterappverror = 0;

                    #= cek kalau tanggal masih kosong
                    $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "'
						AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                    $res = fetchdata($str);

                    foreach ($res as $bar) {
                        if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                            #= roll back approval pertama
                            $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "'
									WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                            // try {
                            $owlPDO->exec($strroll);
                            // }
                            // catch (PDOException $e) {
                            //     print " Gagal  !: " . $e->getMessage() . "\n";
                            //     die();
                            // }
                            $counterappverror++;
                        }
                    }

                    // exit("Error:$counterappverror");
                    if ($counterappverror > 0) {
                        throw new PDOException("Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi);
                    }

                    if ($level == $countApp) {
                        #= bentuk query data untuk posting
                        // $str = "SELECT * FROM ".$dbname.".pmn_feetbs WHERE notransaksi = '".$notransaksi."'";
                        // $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        // $res->setFetchMode(PDO::FETCH_ASSOC);
                        // $bar = $res->fetch();

                        #=
                        $str = "SELECT SUM(totalrp) as totalrp, unit, unitalokasi, notransaksi, kodesupplier, tanggal, tipetbs, noakundebet, noakunkredit FROM " . $dbname . ".pmn_feetbs WHERE notransaksi='" . $notransaksi . "'";
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $totalrp = $bar['totalrp'];
                            $kodeunit = $bar['unit'];
                            $alokasi = $bar['unitalokasi'];
                            $notransaksi = $bar['notransaksi'];
                            $tanggal = $bar['tanggal'];
                            $periode = substr($bar['tanggal'], 0, 7);
                            $tipetbs = $bar['tipetbs'];
                            $supplier = $bar['kodesupplier'];
                            $noakundebet = $bar['noakundebet'];
                            $noakunkredit = $bar['noakunkredit'];
                        }
                        // if($noakunkredit == ''){
                        //     exit("Warning: Noakun kredit masih kosong, silahkan daftarkan di master parameter jurnal");
                        // }
                        // if($noakundebet == ''){
                        //     exit("Warning: Noakun debet masih kosong, silahkan daftarkan di master parameter jurnal");
                        // }

                        $kodejurnal = 'INVTF';
                        $query = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodekelompok='" . $kodejurnal . "' and kodeunit='" . $alokasi . "' and periode='" . $periode . "'");
                        $tmpKonter = fetchData($query);
                        if ($tmpKonter[0]['nokounter'] == '') {
                            $str = "insert into " . $dbname . ".keu_5kelompokjurnal(kodeorg,kodeunit,kodekelompok,periode,keterangan,nokounter)
						values('" . $kodept[$alokasi] . "','" . $alokasi . "','" . $kodejurnal . "','" . $periode . "','',2)"; // mulai dari 2 aja
                            // try{
                            $owlPDO->exec($str);
                            $tmpKonter[0]['nokounter'] = 1;
                            // }catch (PDOException $e){
                            //     print " Gagal  !: " . $e->getMessage() . "\n";
                            //     die();
                            // }
                        }
                        $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
                        # Prep No Jurnal
                        $nojurnal = str_replace('-', '', $tanggal) . "/" . $alokasi . "/" . $kodejurnal . "/" . $konter;

                        $dataRes['header'][] = array(
                            'nojurnal' => $nojurnal,
                            'kodejurnal' => $kodejurnal,
                            'tanggal' => $tanggal,
                            'tanggalentry' => date('Ymd'),
                            'posting' => '0',
                            'totaldebet' => '0',
                            'totalkredit' => '0',
                            'amountkoreksi' => '0',
                            'noreferensi' => $notransaksi,
                            'autojurnal' => '1',
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'revisi' => '0',
                        );
                        $noUrut = 1;

                        #= debet
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $noakundebet,
                            'keterangan' => 'JURNAL BIAYA ADMINISTRASI TBS : ' . $notransaksi,
                            'jumlah' => $totalrp,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $alokasi,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '40000003',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => '',
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );
                        $noUrut++;

                        #= kredit
                        $dataRes['detail'][] = array(
                            'nojurnal' => $nojurnal,
                            'tanggal' => $tanggal,
                            'nourut' => $noUrut,
                            'noakun' => $noakunkredit,
                            'keterangan' => 'JURNAL BIAYA ADMINISTRASI TBS : ' . $notransaksi,
                            'jumlah' => $totalrp * -1,
                            'matauang' => 'IDR',
                            'kurs' => '1',
                            'kodeorg' => $alokasi,
                            'kodekegiatan' => '',
                            'kodeasset' => '',
                            'kodebarang' => '40000003',
                            'nik' => '',
                            'kodecustomer' => '',
                            'kodesupplier' => $supplier,
                            'noreferensi' => $notransaksi,
                            'noaruskas' => '',
                            'kodevhc' => '',
                            'nodok' => '',
                            'kodeblok' => '',
                            'revisi' => '0',
                            'kodesegment' => '0000000001',
                        );
                        $noUrut++;

                        // exit('Error');
                        #= update counter jurnal
                        $str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where kodeunit='" . $alokasi . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periode . "' ";
                        // try {
                        $owlPDO->exec($str);
                        // } catch (PDOException $e) {
                        //     print " Gagal !: " . $e->getMessage() . "\n";
                        //     die();
                        // }

                        $strup = "UPDATE " . $dbname . ".pmn_feetbs SET posting = '1' WHERE notransaksi = '" . $notransaksi . "'";
                        // try {
                        $owlPDO->exec($strup);
                        // } catch (PDOException $e) {
                        //     print " Gagal !: " . $e->getMessage() . "\n";
                        //     die();
                        // }

                        #= jurnalht
                        $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
                        // try {
                        $owlPDO->exec($queryH);
                        // } catch (PDOException $e) {
                        //     print " Gagal !: " . $e->getMessage() . "\n";
                        //     die();
                        // }

                        #= jurnaldt
                        $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
                        // try {
                        $owlPDO->exec($queryD);
                        // } catch (PDOException $e) {
                        //     print " Gagal !: " . $e->getMessage() . "\n";
                        //     die();
                        // }

                        // $owlPDO->commit();

                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Errorcode, " . addslashes($e->getMessage());
                    die();
                }

                break;

            case 'SKAV':
                try {
                    $owlPDO->beginTransaction();

                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }

                    $karyawanid = $_SESSION['standard']['userid'];

                    $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                    $res = fetchData($str);
                    $countApp = $res[0]['countApp'];

                    $tglskrng = date("Y-m-d H:i:s");
                    $str = "UPDATE " . $dbname . ".approval SET status='1', komentar='" . $alasan . "', tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                    // try {
                    $owlPDO->exec($str);
                    // }
                    // catch (PDOException $e) {
                    //     print " Gagal  !: " . $e->getMessage() . "\n";
                    //     die();
                    // }
                    $counterappverror = 0;

                    #= cek kalau tanggal masih kosong
                    $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "'
						AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                    $res = fetchdata($str);

                    foreach ($res as $bar) {
                        if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                            #= roll back approval pertama
                            $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "'
									WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                            // try {
                            $owlPDO->exec($strroll);
                            // }
                            // catch (PDOException $e) {
                            //     print " Gagal  !: " . $e->getMessage() . "\n";
                            //     die();
                            // }
                            $counterappverror++;
                        }
                    }

                    // exit("Error:$counterappverror");
                    if ($counterappverror > 0) {
                        throw new PDOException("Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi);
                    }

                    if ($level == $countApp) {
                        #=
                        $str = "SELECT * FROM " . $dbname . ".kebun_5kavling_update WHERE notransaksi='" . $notransaksi . "'";
                        $res = fetchdata($str);
                        foreach ($res as $bar) {
                            $kamusproposalkav['id'] = $bar['id'];
                            $kamusproposalkav['kodeunit'] = $bar['kodeunit'];
                            $kamusproposalkav['afdeling'] = $bar['afdeling'];
                            $kamusproposalkav['kodeblok'] = $bar['kodeblok'];
                            $kamusproposalkav['no_hamp'] = $bar['no_hamp'];
                            $kamusproposalkav['no_kavl'] = $bar['no_kavl'];
                            $kamusproposalkav['nama'] = $bar['nama'];
                            $kamusproposalkav['aktif'] = $bar['aktif'];
                        }

                        $strup = "UPDATE " . $dbname . ".kebun_5kavling_update SET status = '1' WHERE notransaksi = '" . $notransaksi . "'";
                        // try {
                        $owlPDO->exec($strup);
                        // } catch (PDOException $e) {
                        //     print " Gagal !: " . $e->getMessage() . "\n";
                        //     die();
                        // }
                        $strup2 = "UPDATE " . $dbname . ".kebun_5kavling SET kodeblok = '" . $kamusproposalkav['kodeblok'] . "', no_hamp = '" . $kamusproposalkav['no_hamp'] . "', no_kavl = '" . $kamusproposalkav['no_kavl'] . "', nama = '" . $kamusproposalkav['nama'] . "', aktif = '" . $kamusproposalkav['aktif'] . "' WHERE id = '" . $kamusproposalkav['id'] . "'";
                        $owlPDO->exec($strup2);
                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Errorcode, " . addslashes($e->getMessage());
                    die();
                }

                break;

            case 'GDOKFIN':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $karyawanid = $_SESSION['standard']['userid'];

                $str = "SELECT MAX(level) as countApp FROM " . $dbname . ".approval WHERE notransaksi = '" . $notransaksi . "'";
                $res = fetchData($str);
                $countApp = $res[0]['countApp'];

                $tglskrng = date("Y-m-d H:i:s");
                $str = "UPDATE " . $dbname . ".approval SET status='1', komentar='" . $alasan . "', tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                $counterappverror = 0;

                #= cek kalau tanggal masih kosong
                $str = "SELECT * FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "'
						AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                $res = fetchdata($str);

                foreach ($res as $bar) {
                    if ($bar['tanggal'] == '0000-00-00 00:00:00' and $bar['status'] == '1') {
                        #= roll back approval pertama
                        $strroll = "UPDATE " . $dbname . ".approval SET status='0', komentar='', tanggal='" . $tglskrng . "'
									WHERE notransaksi='" . $notransaksi . "' AND level='" . $level . "' AND karyawanid='" . $karyawanid . "'";
                        try {
                            $owlPDO->exec($strroll);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                        $counterappverror++;
                    }
                }

                if ($counterappverror > 0) {
                    exit("Warning: Persetujuan gagal, Silahkan lakukan proses approval/persetujuan ulang untuk dokumen " . $notransaksi);
                }

                if ($level == $countApp) {
                    $str = "UPDATE " . $dbname . ".keu_gantidokumen SET posting = '1' WHERE notransaksi = '" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                }

                break;

            default:
                $levelakhir = getCountApproval($jenispersetujuan, substr($notransaksi, 0, 4));
                if ($jenispersetujuan == 'SPL') {
                    $tabel = ".sdm_splemburht";
                    $set = "set statuspersetujuan='1'";
                }

                $str = "update " . $dbname . ".approval set status='1',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";

                try {
                    $owlPDO->exec($str);

                    if ($level == $levelakhir) {
                        $strspl = "update " . $dbname . $tabel . " " . $set . " where notransaksi='" . $notransaksi . "' ";
                        try {

                            $owlPDO->exec($strspl);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }

                    if ($jenispersetujuan == 'SPL' && $fromdata != 1) {
                        $strspldt = "update " . $dbname . ".sdm_splemburdt set statuslembur=1 where notransaksi='" . $notransaksi . "' ";
                        try {
                            $owlPDO->exec($strspldt);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
        }
        break;

    case 'rejected':
        switch ($proses) {

            case 'PHP':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                try {
                    $owlPDO->beginTransaction();

                    $expnotran = explode('/', $notransaksi);
                    $kodeorg = $expnotran[1];
                    $countApp = getCountApproval($proses, $kodeorg);

                    $str = "UPDATE $dbname.approval SET STATUS='" . $hasilpersetujuan . "',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "UPDATE $dbname.lgl_penawaranharga SET statuspersetujuan='" . $hasilpersetujuan . "' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #= delete persetujuan lanjutan
                    $str = "DELETE FROM $dbname.approval WHERE `level` >'" . $level . "' and notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #Pindahkan ke history approval apabila reconfirm
                    if ($hasilpersetujuan == '3') {
                        movetoappreturn($notransaksi);
                    }

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;
            case 'SERVICE':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                try {
                    $owlPDO->beginTransaction();

                    $expnotran = explode('/', $notransaksi);
                    $kodeorg = $expnotran[1];
                    $countApp = getCountApproval($proses, $kodeorg);

                    $str = "update " . $dbname . ".approval set status='" . $hasilpersetujuan . "',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".vhc_penggantianht set statuspersetujuan='" . $hasilpersetujuan . "' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #= delete persetujuan lanjutan
                    $str = "delete from " . $dbname . ".approval where level>'" . $level . "' and notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #Pindahkan ke history approval apabila reconfirm
                    if ($hasilpersetujuan == '3') {
                        movetoappreturn($notransaksi);
                    }

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;
            case 'UNPOST':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    #update transaksi
                    $str = "update " . $dbname . ".owlhelp_ticket set status='" . $hasilpersetujuan . "', persetujuan='" . $hasilpersetujuan . "' where id='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #update approval
                    $str = "update " . $dbname . ".approval set status='" . $hasilpersetujuan . "', komentar='" . $alasan . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    $where = " notransaksi='" . $notransaksi . "' and level>'" . $level . "' and jenispersetujuan='" . $proses . "'";
                    $str = "delete from " . $dbname . ".approval where " . $where . "";
                    $owlPDO->exec($str);

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'LBR':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    #update transaksi
                    $str = "update " . $dbname . ".sdm_lemburht set posting='2', approveby='" . $karyawanid . "' where nopengajuan='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'PNN':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    #update transaksi
                    $str = "update " . $dbname . ".kebun_5basispanen2 set posting='2' where nopengajuan='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'PNNBR':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    #update transaksi
                    $str = "update " . $dbname . ".kebun_5premikutipbrondolansaja set posting='2' where nopengajuan='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            //Umar
            case 'GRNINO':
            case 'GRNISO':
            case 'GRNICO':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    #update transaksi
                    $str = "update " . $dbname . ".log_noninventory set persetujuan='2' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;
            //End Umar
            case 'EODBKM':
            case 'EODPNN':
            case 'EODRPNN':
            case 'EODTRK':
            case 'EODWS':
            case 'EODLOG':
            case 'EODKB':
            case 'EODKSR':
            case 'EODLBR':
            case 'EODGR':
            case 'EODSPB':
            case 'EODBKMPOST':
            case 'EODPNNPOST':
            case 'EODRPNNPOST':
            case 'EODSPBPOST':
            case 'EODTRKPOST':
            case 'EODWSPOST':
            case 'EODLOGPOST':
            case 'EODGRPOST':

                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    $str = "select * from " . $dbname . ".setup_validasiinput_dt where nopengajuan ='" . $notransaksi . "'";
                    $res = fetchdata($str);
                    if (count($res) > 0) {
                        #update transaksi
                        $str = "update " . $dbname . ".setup_validasiinput_dt set status='2' where nopengajuan='" . $notransaksi . "'";
                        $owlPDO->exec($str);
                    }

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'ATBS':
                try {
                    $owlPDO->beginTransaction();

                    $tglskrng = date("Y-m-d H:i:s");

                    $str = "select * from " . $dbname . ".kebun_5hargaangkut where nopengajuan ='" . $notransaksi . "' and posting='9'";
                    $res = fetchdata($str);
                    if (count($res) > 0) {
                        #update transaksi
                        $str = "update " . $dbname . ".kebun_5hargaangkut set posting='2' where nopengajuan='" . $notransaksi . "'";
                        $owlPDO->exec($str);
                    } else {
                        $str = "select * from " . $dbname . ".kebun_5hargaangkut where nopengajuan ='" . $notransaksi . "' and postingadd='9'";
                        $res = fetchdata($str);
                        if (count($res) > 0) {
                            $str = "update " . $dbname . ".kebun_5hargaangkut set postingadd='2' where nopengajuan='" . $notransaksi . "'";
                            $owlPDO->exec($str);

                            $s = "select * from " . $dbname . ".kebun_5hargaangkut_additional where nopengajuan ='" . $notransaksi . "'";
                            $r = fetchdata($s);

                            $r = "update " . $dbname . ".kebun_5hargaangkut_additional set posting='2' where nopengajuan='" . $notransaksi . "'";
                            $owlPDO->exec($r);
                        }
                    }

                    #update approval
                    $str = "update " . $dbname . ".approval set status='2', komentar='" . $param['comment'] . "', tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $param['kolom'] . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

                break;
            case 'BAJS':
                try {
                    $owlPDO->beginTransaction();

                    $str = "update " . $dbname . ".approval set status='" . $hasilpersetujuan . "',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_bakontrakjasa set status='" . $hasilpersetujuan . "' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    if ($hasilpersetujuan == '3') {
                        movetoappreturn($notransaksi);
                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warning, " . addslashes($e->getMessage());
                }
                break;

            case 'KASBANK':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $countApp = getCountApproval($proses, $kodeorg);
                $tglskrng = date("Y-m-d H:i:s");
                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "update " . $dbname . ".keu_kasbankht set posting='3' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                #= delete persetujuan lanjutan
                $str = "delete from " . $dbname . ".approval where level>'" . $level . "' and
						notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    echo " Gagal," . addslashes($e->getMessage());
                }
                break;

            case 'RFQ':
                try {
                    $owlPDO->beginTransaction();

                    $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $_SESSION['standard']['userid'] . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_perintaanhargaht set tolakrph='2',statusverifikasi='0' where nomor='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_permintaanhargadt set score='0' where nomor='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    $str = "select * from " . $dbname . ".approval where notransaksi='" . $notransaksi . "'"; #exit("error".$str);
                    $res = fetchdata($str);
                    foreach ($res as $val) {
                        $str = "insert into " . $dbname . ".approval_return (notransaksi,jenispersetujuan,level,karyawanid,status,komentar,tanggal,nourut) values ('" . $val['notransaksi'] . "','" . $val['jenispersetujuan'] . "','" . $val['level'] . "','" . $val['karyawanid'] . "','" . $val['status'] . "','" . $val['komentar'] . "','" . $val['tanggal'] . "','1')";
                        $owlPDO->exec($str);
                    }

                    // movetohistory($notransaksi);
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warning, " . addslashes($e->getMessage());
                }
                break;

            case 'PTBS':
                try {
                    $owlPDO->beginTransaction();
                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }

                    # Update status di approval menjadi 3
                    // $expnotran = explode('/',$notransaksi);
                    // $kodeorg = $expnotran[1];
                    // $countApp = getCountApproval($proses,'SDKM');
                    $tglskrng = date("Y-m-d H:i:s");

                    $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $karyawanid . "'";
                    $owlPDO->exec($str);

                    # Get data dari approval untuk diinsert ke approval_return
                    $getapv = "SELECT notransaksi, jenispersetujuan,
	            			level, karyawanid,
	            			status, komentar,
	            			keterangan, tanggal
	            			FROM " . $dbname . ".approval
	            			WHERE jenispersetujuan = 'PTBS'
	            			AND notransaksi='" . $notransaksi . "'";
                    $resapv = fetchData($getapv);

                    # Insert ke approval_return
                    foreach ($resapv as $key => $val) {
                        $insapv = "INSERT INTO " . $dbname . ".approval_return VALUES (
	            				'" . $val['notransaksi'] . "',
	            				'" . $val['jenispersetujuan'] . "',
	            				'" . $val['level'] . "',
	            				'" . $val['karyawanid'] . "',
	            				'" . $val['status'] . "',
	            				'" . $val['komentar'] . "',
	            				'" . $val['keterangan'] . "',
	            				'" . $val['tanggal'] . "',
	            				'1'
	            			)";
                        $owlPDO->exec($insapv);
                    }

                    if (strpos($notransaksi, 'TBSKUD') == true) {
                        $tbl = "kebun_tbskud";
                    } else if (strpos($notransaksi, 'TBSAFI') == true) {
                        $tbl = "kebun_tbsafiliasi";
                    } else if (strpos($notransaksi, 'TBSEXT') == true) {
                        $tbl = "kebun_tbsexternal";
                    }

                    # Update status menjadi 3
                    $str = "update " . $dbname . "." . $tbl . " set posting='3' where notransaksi='" . $notransaksi . "'";
                    $owlPDO->exec($str);

                    #= delete persetujuan
                    $str = "delete from " . $dbname . ".approval where jenispersetujuan = 'PTBS' and
						notransaksi='" . $notransaksi . "'";
                    // exit("Error:$str");

                    $owlPDO->exec($str);

                    #= delete dari keu_jurnalht
                    $str = "delete from " . $dbname . ".keu_jurnalht where noreferensi='" . $notransaksi . "'";
                    // exit("Error:$str");

                    $owlPDO->exec($str);

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
                break;
            case 'PRM':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='PRM'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and jenispersetujuan='PRM'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "') and jenispersetujuan='PRM'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='2' where nomorsk='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'MTS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='MTS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and jenispersetujuan='MTS'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "') and jenispersetujuan='MTS'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='2' where nomorsk='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'DMS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select darikodeorg from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['darikodeorg'];

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='DMS'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and jenispersetujuan='DMS'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "') and jenispersetujuan='DMS'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".sdm_riwayatjabatan set statuspersetujuan='2' where nomorsk='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'ERF':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $countApp = getCountApproval($proses, substr($notransaksi, 11, 4));

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and jenispersetujuan='ERF'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "') and jenispersetujuan='ERF'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".sdm_req_employee set statuspersetujuan='2' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'PPT':
                //kembali ke file Program Training
                $_GET['proses'] = 'rejected';
                include "sdm_slave_programtraining.php";
                break;
            case 'PO':
                try {
                    $owlPDO->beginTransaction();

                    if ($alasan == '') {
                        throw new PDOException("Komentar harus diisi.");
                    }

                    $exnopo = explode('/', $notransaksi);
                    $kodeorg = $exnopo[4];
                    $countApp = getCountApproval($proses, $kodeorg);

                    ## CREATE NOTIFICATION
                    // notifemailpo($notransaksi,'3',$karyawanid);

                    #Pindahkan ke history approval apabila reconfirm
                    if ($hasilpersetujuan == '3') {
                        $str = "update " . $dbname . ".approval set status='" . $hasilpersetujuan . "',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "update " . $dbname . ".log_poht set statuspo='0' where nopo='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        movetoappreturn($notransaksi);
                    } else {
                        $str = "select a.*,b.nodph,b.kodesupplier,b.diskonpersen,b.purchaser from " . $dbname . ".log_podt a left join " . $dbname . ".log_poht b on a.nopo=b.nopo where a.nopo='" . $notransaksi . "'";
                        $arr = fetchData($str);
                        $no = $subtotal = 0;

                        $arrtemp = array();
                        foreach ($arr as $val) {
                            $str4 = "select nomor from " . $dbname . ".log_permintaanhargadt where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "' and norph='" . $val['nodph'] . "'";
                            $arr4 = fetchdata($str4);
                            $nmr = $arr4[0]['nomor'];

                            $str4 = "select * from " . $dbname . ".log_permintaanhargadt where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "' and nomor='" . $nmr . "'";
                            $arr4 = fetchdata($str4);
                            foreach ($arr4 as $val4) {
                                if ($arrtemp[$val4['nomor']]['nomor'] == $val4['nomor']) {
                                } else {
                                    $no++;
                                    $arrtemp[$val4['nomor']]['nomor'] = $val4['nomor'];

                                    // $optNoDph = makeOption($dbname,'log_permintaanhargadt','norph,nomor',"norph='".$val['nodph']."'");
                                    // $nodph = $optNoDph[$val['nodph']];
                                    $expno = explode('/', $val4['nomor']);
                                    $myno = "/" . $expno[2] . "/" . $expno[3] . "/" . $expno[4];
                                    $str2 = "select nomor from " . $dbname . ".log_perintaanhargaht where nomor like '%" . $myno . "%' order by nomor desc limit 0,1";
                                    $res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
                                    $res2->setFetchMode(PDO::FETCH_ASSOC);
                                    $bar2 = $res2->fetch();
                                    $dt = explode("/", $bar2['nomor']);
                                    $no_permintaan = addZero(($dt[0] + $no), 3) . "/" . $expno[1] . "" . $myno;
                                    $arrtemp[$val4['nomor']]['nopermintaan'] = $no_permintaan;
                                }

                                $str2 = "INSERT INTO " . $dbname . ".log_permintaanhargadt (nomor,nourut,kodebarang,hargaterakhir,harga,merk,spec,jumlah,nopp,flag,norph,verificator,tanggalverifikasi,score,factor) values
                                ('" . $arrtemp[$val4['nomor']]['nopermintaan'] . "','" . $val4['nourut'] . "','" . $val4['kodebarang'] . "','" . $val4['hargaterakhir'] . "','" . $val4['harga'] . "','" . $val4['merk'] . "','" . $val4['spec'] . "','" . $val4['jumlah'] . "','" . $val4['nopp'] . "','0','','','','" . $val4['score'] . "','" . $val['factor'] . "')";
                                $owlPDO->exec($str2);

                                $arrtemp[$val4['nomor']][$val4['nourut']]['subtotal'] += ($val4['harga'] * $val4['jumlah']);
                            }

                            $str2 = "INSERT INTO " . $dbname . ".log_listverifikasi (id,nopp,kodebarang,karyawanid,status,skip,pemenang,createdby,createdtime,updateby,updatetime) SELECT '',nopp,kodebarang,karyawanid,'1','0','0',createdby,createdtime,updateby,updatetime FROM " . $dbname . ".log_listverifikasi WHERE nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "' group by nopp, kodebarang,karyawanid";
                            $owlPDO->exec($str2);

                            $str2 = "update " . $dbname . ".log_prapodt set create_po='0' where nopp='" . $val['nopp'] . "' and kodebarang='" . $val['kodebarang'] . "'";
                            $owlPDO->exec($str2);
                        }

                        foreach ($arrtemp as $key => $val) {
                            $str2 = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $val['nomor'] . "'";
                            $res2 = fetchdata($str2);
                            foreach ($res2 as $key2 => $val2) {
                                $nilaidiskon = ($arrtemp[$val['nomor']][$val2['nourut']]['subtotal'] * $val2['diskonpersen']) / 100;
                                $nilaipermintaan = $arrtemp[$val['nomor']][$val2['nourut']]['subtotal'] - $nilaidiskon;
                                $str3 = "INSERT INTO " . $dbname . ".log_perintaanhargaht (nomor,tanggal,purchaser,supplierid,id_alamat_supplier,nourut,id_franco,stock,catatan,sisbayar,sisbayar2,ppn,subtotal,diskonpersen,nilaidiskon,nilaipermintaan,matauang,kurs,tgldari,tglsmp,flag,catatanmenang,po,pbbkb,pphfinal,tolakrph,nodphlama,keterangan,lokasikirim,statuskirim,durasipengiriman,durasipekerjaan,garansiproduk,posisistok,asuransi,komentar,nilai1s,nilai1f,nilai2s,nilai2f,nilai3s,nilai3f,nilai4s,nilai4f,nilai5s,nilai5f) values ('" . $val['nopermintaan'] . "','" . date('Y-m-d') . "','" . $val2['purchaser'] . "','" . $val2['supplierid'] . "','" . $val2['id_alamat_supplier'] . "','" . $val2['nourut'] . "','" . $val2['id_franco'] . "','" . $val2['stock'] . "','" . $val2['catatan'] . "','" . $val2['sisbayar'] . "','" . $val2['sisbayar2'] . "','" . $val2['ppn'] . "','" . $arrtemp[$val['nomor']][$val2['nourut']]['subtotal'] . "','" . $val2['diskonpersen'] . "','" . $nilaidiskon . "','" . $nilaipermintaan . "','" . $val2['matauang'] . "','" . $val2['kurs'] . "','" . $val2['tgldari'] . "','" . $val2['tglsmp'] . "','0','" . $val2['catatanmenang'] . "','" . $val2['po'] . "','" . $val2['pbbkb'] . "','" . $val2['pphfinal'] . "','0','" . $val['nomor'] . "','" . $val2['keterangan'] . "','" . $val2['lokasikirim'] . "','" . $val2['statuskirim'] . "','" . $val2['durasipengiriman'] . "','" . $val2['durasipekerjaan'] . "','" . $val2['garansiproduk'] . "','" . $val2['posisistok'] . "','" . $val2['asuransi'] . "','" . $val2['komentar'] . "','" . $val2['nilai1s'] . "','" . $val2['nilai1f'] . "','" . $val2['nilai2s'] . "','" . $val2['nilai2f'] . "','" . $val2['nilai3s'] . "','" . $val2['nilai3f'] . "','" . $val2['nilai4s'] . "','" . $val2['nilai4f'] . "','" . $val2['nilai5s'] . "','" . $val2['nilai5f'] . "')";
                                $owlPDO->exec($str3);
                                $purchaser = $val2['purchaser'];
                            }

                            $strup = "update " . $dbname . ".log_perintaanhargaht set tolakrph='1' where nomor='" . $val['nomor'] . "'";
                            $owlPDO->exec($strup);

                            $str2 = "select * from " . $dbname . ".log_permintaanhargafile where nomor='" . $val['nomor'] . "'";
                            $arr2 = fetchData($str2);
                            foreach ($arr2 as $val2) {
                                $sss = rand(10, 100);
                                $namafile = $sss . "" . $val2['namafile'];
                                $str3 = "INSERT INTO " . $dbname . ".log_permintaanhargafile (nomor,supplierid,namafile,formaticon,status,createdby,createdtime) SELECT '" . $val['nopermintaan'] . "',supplierid,'" . $namafile . "',formaticon,status,createdby,createdtime FROM " . $dbname . ".log_permintaanhargafile WHERE nomor='" . $val2['nomor'] . "' and supplierid='" . $val2['supplierid'] . "' and namafile='" . $val2['namafile'] . "'";
                                $owlPDO->exec($str3);

                                $file = "fileupload/rph/" . $val2['namafile'];
                                $newfile = "fileupload/rph/" . $namafile;
                                if (!copy($file, $newfile)) {
                                    // throw new PDOException("failed to copy $file...\n");
                                }
                            }
                        }

                        $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "'";
                        $owlPDO->exec($str);

                        $str = "INSERT INTO " . $dbname . ".log_poht_del SELECT * FROM " . $dbname . ".log_poht where nopo='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "INSERT INTO " . $dbname . ".log_podt_del SELECT * FROM " . $dbname . ".log_podt where nopo='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "delete from " . $dbname . ".log_poht where nopo='" . $notransaksi . "'";
                        $owlPDO->exec($str);

                        $str = "delete from " . $dbname . ".log_podt where nopo='" . $notransaksi . "'";
                        $owlPDO->exec($str);
                    }

                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                }
                break;

            case 'GR':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_transaksiht set hasilpersetujuan1='2' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'CB':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "')";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".spl_capexbangunan set posting='0' where kode='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                        $str2 = "update " . $dbname . ".approval set status='2',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid<>'" . $karyawanid . "' and status<>'1'";
                        try {
                            $owlPDO->exec($str2);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n" . $str2;
                            die();
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'SCR':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $str = "select kodeorg from " . $dbname . ".pmn_scr where notransaksi='" . $notransaksi . "'";
                $res = fetchdata($str);
                $kodeorg = $res[0]['kodeorg'];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "')";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".pmn_scr set status='3' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'KL':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_5klbarang set status='3' where kode='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'SKL':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_5subklbarang set status='3' where kode='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'MB':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and karyawanid='" . $karyawanid . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "')";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_5masterbarang set inactive='3' where kodebarang='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'DS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "select * from " . $dbname . ".log_5supplier where supplierid='" . $notransaksi . "'";
                $res = fetchdata($str);
                $perubahan1 = $res[0]['perubahan'];
                $statusx1 = $res[0]['statusyangdiinginkan'];

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' ";

                try {

                    $owlPDO->exec($str);
                    if ($perubahan1 == '') {
                        $str = "update " . $dbname . ".log_5supplier set status='0',statuspersetujuan='2'  where supplierid='" . $notransaksi . "'";
                        try {
                            $owlPDO->exec($str);
                        } catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }
                    } else {
                        $arrperub = explode('##', $perubahan1);
                        if ($arrperub[0] != '') {
                            $str = "update " . $dbname . ".log_5supplier set status='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        }
                    }

                    $input = "select * from " . $dbname . ".log_5rekbank where supplierid = '" . $notransaksi . "'";
                    $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                    $n->setFetchMode(PDO::FETCH_ASSOC);
                    while ($d = $n->fetch()) {
                        if ($d['perubahan'] == '') {
                            $str = "update " . $dbname . ".log_5rekbank set isactive='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and idbank='" . $d['idbank'] . "' and matauang='" . $d['matauang'] . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } else {
                            $arrperub = explode('##', $d['perubahan']);
                            if ($arrperub[0] != '') {
                                $str = "update " . $dbname . ".log_5rekbank set isactive='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and idbank='" . $d['idbank'] . "' and matauang='" . $d['matauang'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }
                        }
                    }

                    $input = "select * from " . $dbname . ".log_5supnpwp where supplierid = '" . $notransaksi . "'";
                    $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                    $n->setFetchMode(PDO::FETCH_ASSOC);
                    while ($d = $n->fetch()) {
                        if ($d['perubahan'] == '') {
                            $str = "update " . $dbname . ".log_5supnpwp set active='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and npwp='" . $d['npwp'] . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } else {
                            $arrperub = explode('##', $d['perubahan']);
                            if ($arrperub[0] != '') {
                                $str = "update " . $dbname . ".log_5supnpwp set active='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and npwp='" . $d['npwp'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }
                        }
                    }

                    $input = "select * from " . $dbname . ".log_5supalamat where supplierid = '" . $notransaksi . "'";
                    $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                    $n->setFetchMode(PDO::FETCH_ASSOC);
                    while ($d = $n->fetch()) {
                        if ($d['perubahan'] == '') {
                            $str = "update " . $dbname . ".log_5supalamat set status='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and id_alamat='" . $d['id_alamat'] . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } else {
                            $arrperub = explode('##', $d['perubahan']);
                            if ($arrperub[0] != '') {
                                $str = "update " . $dbname . ".log_5supalamat set status='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and id_alamat='" . $d['id_alamat'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }
                        }
                    }

                    $input = "select * from " . $dbname . ".log_5pphsup where supplierid = '" . $notransaksi . "'";
                    $n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
                    $n->setFetchMode(PDO::FETCH_ASSOC);
                    while ($d = $n->fetch()) {
                        if ($d['perubahan'] == '') {
                            $str = "update " . $dbname . ".log_5pphsup set status='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and noakun='" . $d['noakun'] . "'";
                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }
                        } else {
                            $arrperub = explode('##', $d['perubahan']);
                            if ($arrperub[0] != '') {
                                $str = "update " . $dbname . ".log_5pphsup set status='0',statuspersetujuan='2' where supplierid='" . $notransaksi . "' and noakun='" . $d['noakun'] . "'";
                                try {
                                    $owlPDO->exec($str);
                                } catch (PDOException $e) {
                                    print " Gagal  !: " . $e->getMessage() . "\n";
                                    die();
                                }
                            }
                        }
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'HBT':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                // $kodeorg = substr($notransaksi,8,4);

                $str = "select * from " . $dbname . ".pmn_hargabelitbs where notransaksi = '" . $notransaksi . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $tanggaldata = $bar['tanggal'];
                $kodeorgdata = $bar['kodeorg'];
                $kodeorg = $bar['kodeorg'];
                $tipedata = $bar['tipe'];

                $countApp = getCountApproval($proses, $kodeorg);

                $str = "select * from " . $dbname . ".pmn_hargabelitbs where tanggal = '" . $tanggaldata . "' and  kodeorg='" . $kodeorgdata . "' and tipe='" . $tipedata . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $arrnotransaksi[$bar['notransaksi']] = $bar['notransaksi'];
                }

                // exit("Error:".$notransaksi._.$kodeorg);
                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',
					tanggal='" . $tglskrng . "' where notransaksi in ('" . implode("','", $arrnotransaksi) . "')	";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi in ('" . implode("','", $arrnotransaksi) . "') and (level!='" . $level . "' or
						karyawanid!='" . $karyawanid . "')";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".pmn_hargabelitbs set posting='3' where notransaksi in ('" . implode("','", $arrnotransaksi) . "')	";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'HJT':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $kodeorg = substr($notransaksi, 8, 4);

                $str = "select * from " . $dbname . ".pmn_hargajualtbs where notransaksi = '" . $notransaksi . "'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $tanggaldata = $bar['tanggal'];
                $kodeorgdata = $bar['kodeorg'];
                $tipedata = $bar['tipe'];

                // exit("Error:".$notransaksi._.$kodeorg);
                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',
					tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or  karyawanid!='" . $karyawanid . "')";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "update " . $dbname . ".pmn_hargabelitbs set posting='3' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'BTBS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and karyawanid!='" . $karyawanid . "' and level='" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level>'" . $level . "'";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".keu_persediaantbs_ht set pengajuanbonus='3' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'CPX':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = substr($exnopo[2], 0, 4);
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and (level!='" . $level . "' or karyawanid!='" . $karyawanid . "')";
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_formcapex_ht set status_pengajuan='3' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'CU':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $exnopo = explode('-', $notransaksi);
                $kodeorg = $exnopo[2];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' and level='" . $level . "'";
                try {
                    $owlPDO->exec($str);

                    $str = "update " . $dbname . ".log_permintaanht set statuspersetujuan='3' where notransaksi='" . $notransaksi . "'";
                    try {
                        $owlPDO->exec($str);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'PJDINAS':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";

                if ($level == $levelakhir) {
                    $str = "insert into " . $dbname . ".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
					  ('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $karyawanid . "','3','" . $alasan . "','" . $tglskrng . "')";
                }

                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'PJDTAMU':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . "";
                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' ";
                    try {
                        $owlPDO->exec($strx);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'SOP':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . "";
                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' ";
                    try {
                        $owlPDO->exec($strx);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    $strspl = "update " . $dbname . ".sdm_sopht set close='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'PROJ':

                $levelakhir = $countAppJ;

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='2',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . "";
                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' ";
                    try {
                        $owlPDO->exec($strx);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    $strspl = "update " . $dbname . ".project set statuspersetujuan='2' where kode='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'PJDINASNS':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and karyawanid='" . $karyawanid . "'";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];

                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . ".sdm_pjdinasht set statuspersetujuan='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'SP':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";

                $strap = "select level from " . $dbname . ".setup_approval where jenispersetujuan='" . $proses . "' and tipe='1' and tipekaryawan='" . $_SESSION['empl']['tipekaryawan'] . "'";
                $res = $owlPDO->query($strap) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
                $levelap = $bar['level'];

                if ($level == $levelap) {
                    $str = "insert into " . $dbname . ".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`,status,komentar,tanggal) values
					  ('" . $notransaksi . "','" . $jenispersetujuan . "','" . $level . "','" . $karyawanid . "','3','" . $alasan . "','" . $tglskrng . "')";
                }

                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . ".sdm_pengajuanspht set statuspersetujuan='2' where nopengajuan='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;

            case 'JM':

                # Get Kodeorg
                $sql = selectQuery($dbname, "keu_jurnalmemorial", "kodeorg", "nojurnal='" . $param['notransaksi'] . "'");
                $res = fetchData($sql);

                $levelakhir = getCountApproval($jenispersetujuan, $res[0]['kodeorg']);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='" . $hasilpersetujuan . "',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";
                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . ".keu_jurnalht set autojurnal='" . $hasilpersetujuan . "' where nojurnal='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);

                        $strjm = "update " . $dbname . ".keu_jurnalmemorial set posting='2' where nojurnal='" . $notransaksi . "'";

                        try {
                            $owlPDO->exec($strjm);
                        } catch (PDOException $e) {
                            print " Gagal Update Tolak JM !: " . $e->getMessage() . "\n";
                            die();
                        }
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    movetoappreturn($notransaksi);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;


            case 'DOF':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                #ambil sisa persetujuan
                $strjlhper = "select max(level) as jumlah from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and jenispersetujuan='DOF'";
                $resjlhper = $owlPDO->query($strjlhper) or die(print " Gagal: " . PDOException::getMessage());
                $resjlhper->setFetchMode(PDO::FETCH_ASSOC);
                $barjlhper = $resjlhper->fetch();
                $countApp = $barjlhper['jumlah'];

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . "";
                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' ";
                    try {
                        $owlPDO->exec($strx);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    $strspl = "update " . $dbname . ".sdm_dayoff set status='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;
            case 'DOFNS':
                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . "";
                try {
                    $owlPDO->exec($str);

                    $strx = "delete from " . $dbname . ".approval where notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid!='" . $karyawanid . "' ";
                    try {
                        $owlPDO->exec($strx);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }

                    $strspl = "update " . $dbname . ".sdm_dayoff set status='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                break;

            case 'BKCK':

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";

                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . ".keu_bukucekdt set status_cek='1' where notrans_cek='" . $notransaksi . "' and status_cek='0' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
            case 'CVMM':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }
                $data = array(
                    'status' => '2',
                    'komentar' => $param['alasan'],
                    'tanggal' => date("Y-m-d H:i:s"),
                );
                $where = "notransaksi = '" . $param['notransaksi'] . "' and jenispersetujuan='CVMM' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                $query = updateQuery($dbname, 'approval', $data, $where); #exit("error".$query);
                $owlPDO->exec($query);

                $data = array(
                    'approval' => '2',
                    'posting' => '0',
                    'updatetime' => date("Y-m-d H:i:s"),
                    'updateby' => $_SESSION['standard']['userid'],
                );
                $where = "id = '" . $param['notransaksi'] . "'";
                $query = updateQuery($dbname, 'sdm_corevalueandmanmanagement', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                break;
            case 'PAS':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }
                $data = array(
                    'status' => '2',
                    'komentar' => $param['alasan'],
                    'tanggal' => date("Y-m-d H:i:s"),
                );
                $where = "notransaksi = '" . $param['notransaksi'] . "' and jenispersetujuan='PAS' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                $query = updateQuery($dbname, 'approval', $data, $where); #exit("error".$query);
                $owlPDO->exec($query);

                $data = array(
                    'approval' => '2',
                    'posting' => '0',
                    'updatetime' => date("Y-m-d H:i:s"),
                    'updateby' => $_SESSION['standard']['userid'],
                );
                $where = "id = '" . $param['notransaksi'] . "'";
                $query = updateQuery($dbname, 'sdm_corevalueandmanmanagement', $data, $where); //exit("error".$query);
                $owlPDO->exec($query);

                break;

            case 'HFTBS':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "UPDATE " . $dbname . ".approval SET status='3', komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "UPDATE " . $dbname . ".pmn_5feetbs SET posting='3' WHERE notransaksi = '" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                #= delete persetujuan lanjutan
                $str = "DELETE FROM " . $dbname . ".approval WHERE level > '" . $level . "' and notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    echo " Gagal," . addslashes($e->getMessage());
                }

                break;

            case 'FTBS':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $expnotran = explode('/', $notransaksi);
                $kodeorg = $expnotran[1];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "UPDATE " . $dbname . ".approval SET status='3', komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "UPDATE " . $dbname . ".pmn_feetbs SET posting='3' WHERE notransaksi = '" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                #= delete persetujuan lanjutan
                $str = "DELETE FROM " . $dbname . ".approval WHERE level > '" . $level . "' and notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    echo " Gagal," . addslashes($e->getMessage());
                }

                break;

            case 'SKAV':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                // $expnotran = explode('/',$notransaksi);
                // $kodeorg = $expnotran[1];
                $countApp = getCountApproval($proses, $kodeorg);

                $str = "UPDATE " . $dbname . ".approval SET status='3', komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "UPDATE " . $dbname . ".kebun_5kavling_update SET status='3' WHERE notransaksi = '" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                #= delete persetujuan lanjutan
                $str = "DELETE FROM " . $dbname . ".approval WHERE level > '" . $level . "' and notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    echo " Gagal," . addslashes($e->getMessage());
                }

                break;

            case 'GDOKFIN':

                if ($alasan == '') {
                    exit("warning : Komentar harus diisi.");
                }

                $countApp = getCountApproval($proses);

                $str = "UPDATE " . $dbname . ".approval SET status='3', komentar='" . $alasan . "',tanggal='" . $tglskrng . "'
						WHERE notransaksi='" . $notransaksi . "' and level='" . $level . "' and karyawanid='" . $_SESSION['standard']['userid'] . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                $str = "UPDATE " . $dbname . ".keu_gantidokumen SET posting='3' WHERE notransaksi = '" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }

                #= delete persetujuan lanjutan
                $str = "DELETE FROM " . $dbname . ".approval WHERE level > '" . $level . "' and notransaksi='" . $notransaksi . "'";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    echo " Gagal," . addslashes($e->getMessage());
                }

                break;

            default:

                $levelakhir = getCountApproval($jenispersetujuan);

                if ($level == $levelakhir) {
                    $whr = " and level='" . $level . "'";
                } else {
                    $whr = " and level>='" . $level . "'";
                }

                if ($jenispersetujuan == 'SPL') {
                    $tabel = ".sdm_splemburht";
                }

                if ($jenispersetujuan == 'DISPO') {
                    $tabel = ".keu_disposalasset";
                }

                $str = "update " . $dbname . ".approval set status='3',komentar='" . $alasan . "',tanggal='" . $tglskrng . "' where notransaksi='" . $notransaksi . "' " . $whr . " ";

                try {
                    $owlPDO->exec($str);

                    $strspl = "update " . $dbname . $tabel . " set statuspersetujuan='2' where notransaksi='" . $notransaksi . "' ";
                    try {
                        $owlPDO->exec($strspl);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                    }
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
                break;
        }
        break;
    case 'form_rejected':
        echo "<div id=rejected_form>
			<input hidden id=notransaksi value=" . $_POST['notransaksi'] . ">
			<input hidden id=kodeapproval value=" . $param['kodeapproval'] . ">
			<table cellspacing=1 border=0>
				<tr>
					<td colspan=3>Rejection</td>
				</tr>
				<tr>
					<td colspan=3><hr></td>
				</tr>
				<tr>
					<td>" . $_SESSION['lang']['note'] . "</td>
					<td>:</td>
					<td><input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
				</tr>
				<tr>
					<td colspan=3 align=center>
						<button class=mybutton onclick=\"inserttolak_atbs(" . $_POST['kolom'] . ")\" >" . $_SESSION['lang']['ditolak'] . "</button>
					</td>
				</tr>
			</table>
		</div>";
        break;
    case 'historykasbank':

        $notransaksihis = checkPostGet('notransaksihis', '');
        $tanggal1his = tanggalsystemn(checkPostGet('tanggal1his', ''));
        $tanggal2his = tanggalsystemn(checkPostGet('tanggal2his', ''));
        $noakunhis = checkPostGet('noakunhis', '');
        $tipetransaksihis = checkPostGet('tipetransaksihis', '');
        $supplierhis = checkPostGet('supplierhis', '');

        $optbuyerhis = $optnoakunhis = $opttipehis = $optpembayaranhis = $optsupplierhis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        $proses = "KASBANK";
        $countApp = getCountApproval('KASBANK');
        $limit = 10;
        $page = 0;
        if (isset($pages)) {
            $page = $pages;
            if ($page < 0) {
                $page = 0;
            }
        }
        $offset = @($page * $limit);
        $no = (@($page * $limit));

        if ($notransaksihis != '') {
            $where .= " and b.notransaksi like '%" . $notransaksihis . "%' ";
        }
        if ($noakunhis != '') {
            $where .= " and b.noakun = '" . $noakunhis . "' ";
        }
        if ($tipetransaksihis != '') {
            $where .= " and b.tipetransaksi = '" . $tipetransaksihis . "' ";
        }
        if ($tanggal1his != '--' and $tanggal2his != '--') {
            $where .= " and b.tanggal between '" . $tanggal1his . "' and '" . $tanggal2his . "' ";
        }
        if ($supplierhis != '') {
            $where .= " and b.notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $supplierhis . "')";
        }

        $str = "select count(a.notransaksi) as count from " . $dbname . ".approval a
		left join " . $dbname . ".keu_kasbankht b on a.notransaksi = b.notransaksi
		where a.jenispersetujuan='KASBANK' and a.karyawanid='" . $karyawanid . "' and a.status!='0' and b.kodeorg is not null
		" . $where . "
		order by b.tanggalpengajuan desc,b.kodeorg asc";
        $res = fetchdata($str);
        $jlhbrs = $res[0]['count'];

        $str = "select a.*,b.kodeorg,b.tanggal,b.tanggalinput,b.noakun,b.tipetransaksi,b.rekening,b.tanggalpengajuan,b.bayarkepada from " . $dbname . ".approval a
		left join " . $dbname . ".keu_kasbankht b on a.notransaksi = b.notransaksi
		where a.jenispersetujuan='KASBANK' and a.karyawanid='" . $karyawanid . "' and a.status!='0' and b.kodeorg is not null
		" . $where . "
		order by b.tanggalpengajuan desc,b.kodeorg asc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {

            $exnopo = explode('/', $bar['notransaksi']);
            $kodeorg = $bar['kodeorg'];
            $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kodeorg . "'");

            //Show E-fill
            $showefill = "";
            $optefill = makeOption($dbname, 'filemanager', 'namafile,id', "namafile='" . $bar['notransaksi'] . "'");
            @$idefill = $optefill[$bar['notransaksi']];

            if ($idefill != '') {
                $showefill = "<img src='images/efill.png' class='zImgBtn' onclick=\"viewefill('" . $bar['notransaksi'] . "','hide',event)\" title='E-Filling System'>";
            }

            $no++;
            $tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td align=left>" . $bar['notransaksi'] . "</td>
				<td align=left>" . tanggalnormal($bar['tanggalpengajuan']) . "</td>
				<td align=left>" . $kodeorg . "-" . $optNmOrg[$kodeorg] . "</td>
				<td align=left>" . $bar['bayarkepada'] . "</td>
				<td align=center>
					<img hidden src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"pdfkasbank('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['noakun'] . "','" . $bar['tipetransaksi'] . "',event);\"> &nbsp
					<img src=images/zoom.png class=resicon height='30' title='Preview' onclick=\"viewdetailkasbank('" . $bar['notransaksi'] . "','0');\"> &nbsp
					<img src=images/uploader/dwnld8.png class=zImgBtn onclick=showimages('listfileupload','" . $bar['notransaksi'] . "','keu_kasbankx') title=view>
					" . $showefill . "
				</td>";

            for ($i = 1; $i <= $countApp; $i++) {
                @$arrDetail = detailApprove($i, $bar['notransaksi'], $proses);

                if ($arrDetail['nama'] != '') {
                    // $tab.="<td style='text-align:center'>".$arrDetail['nama']."</td>";
                    $tab .= "<td style='vertical-align:top;text-align:center'>
							<label style='text-align:center;font-weight:bold'>" . $arrDetail['nama'] . "</label><br>
							Status : " . $arrDetail['namastatus'] . "<br>
							" . ($arrDetail['komentar'] == '' ? "" : "Comment : " . $arrDetail['komentar']) . "<br>
							" . tanggalnormal($arrDetail['tanggal']) . "
						</td>";
                } else {
                    $tab .= "<td style='text-align:center'>-</td>";
                }
            }
            $tab .= "</tr>";
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

        $frompage = (@($page * $limit) + 1);
        if ((@($page + 1) * $limit) > $jlhbrs) {
            $topage = $jlhbrs;
        } else {
            $topage = (@($page + 1) * $limit);
        }
        $tab .= "</tr>
		<tr>
			<td colspan=12 align=center>
				" . $frompage . " to " . $topage . " Of " . $jlhbrs . "
			</td>
		</tr>
		<tr>
			<td colspan=12 align=center>";

        if ($page == '0') {
            $tab .= "";
        } else {
            $tab .= "<button class=mybutton onclick=historykasbank(" . @($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }

        $tab .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPageHistoryKasbank()\">" . $isiRow . "</select>";

        if (@($page + 1) == $totrows) {
            $tab .= "";
        } else {
            $tab .= "<button class=mybutton onclick=historykasbank(" . @($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $tab .= "</td></tr>";

        echo $tab;
        break;
}

<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param          = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}

$kodekegiatan   = checkPostGet('kodekegiatan', '');
$namakegiatan   = checkPostGet('namakegiatan', '');
$kelompok       = checkPostGet('kelompok', '');
$satuan         = checkPostGet('satuan', '');
$noakun         = checkPostGet('noakun', '');
$method         = checkPostGet('method', '');
$methodx        = checkPostGet('methodx', '');
$kelvhc         = checkPostGet('kelvhc', '');
$jnsvhc         = checkPostGet('jnsvhc', '');
$statuskeg      = checkPostGet('statuskeg', '');

$arrakun = ['128', '126', '621', '611'];
$arrakunn = ['7'];
$whereAkun      = "";
$nmkeg = makeOption($dbname, 'setup_klpkegiatan', 'kodeklp,namakelompok');
$nmkeg['EXT'] = "EXTERNAL";

if ($_SESSION['language'] == 'EN') {
    $dd = 'namaakun1';
} else {
    $dd = 'namaakun';
}
$str = "select noakun," . $dd . " as namakegiatan from " . $dbname . ".keu_5akun where detail=1 and (substr(noakun,1,3) in ('116','126','128','621','611','631','632') or substr(noakun,1,1) in ('7','8','9')) and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7 ";
if (!empty($whereAkun)) {
    $str .= " and (" . $whereAkun . ")";
}

$str .= "order by noakun";
$optakun = $optkelompok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $d = substr($bar->noakun, 0, 3);
    if ($d != $n) {
        $optakun .= "<optgroup label='" . $d . " - " . getNamaAkun($d) . "'>";
    }
    $optakun .= "<option value='" . $bar->noakun . "'>" . $bar->noakun . " - " . $bar->namakegiatan . "</option>";
    $n = $d;
    if ($d != $n) {
        $optakun .= "</optgroup>";
    }
}

$optSatuan = "";
$strSat = "select satuan from " . $dbname . ".setup_satuan";
$resSat = $owlPDO->query($strSat) or die(print " Gagal: " . PDOException::getMessage());
$resSat->setFetchMode(PDO::FETCH_OBJ);
$optSatuan = $optkel = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($barSat = $resSat->fetch()) {
    if ($barSat->satuan == $satuan) {
        $sel = "selected";
    }
    $optSatuan .= "<option value='" . $barSat->satuan . "' " . $sel . ">" . $barSat->satuan . "</option>";
}
$arragama = getEnum($dbname, 'vhc_kegiatan', 'tipe');
foreach ($arragama as $kei => $fal) {
    @$optTipe .= "<option value='" . $kei . "'>" . $fal . "</option>";
}

$arragama = getEnum($dbname, 'vhc_kegiatan', 'kelompok');
foreach ($arragama as $kei => $fal) {
    if ($nmkeg[$fal] != '') {
        $optkel .= "<option value='" . $kei . "'>" . $fal . " - " . $nmkeg[$fal] . "</option>";
    }
}

$optkelvhc = "<option value='GLOBAL'>GLOBAL</option>";
$arrklp = array('AB' => 'Alat Berat', 'MS' => 'Mesin - Mesin', 'KD' => 'Kendaraan');
foreach ($arrklp as $kei => $fal) {
    $optkelvhc .= "<option value='" . $kei . "'>" . $kei . " - " . $fal . "</option>";
}

$optjnsvhc = "<option value='GLOBAL'>GLOBAL</option>";

$arrstatus = array("1" => "Aktif", "0" => "Tidak Aktif");
$optstatus = "";
foreach ($arrstatus as $key => $val) {
    $optstatus .= "<option value='".$key."'>".$val."</option>";
}

$sttskegkebun = makeOption($dbname,"setup_kegiatan","kodekegiatan,status");

switch ($method) {
    case 'loaddata':

        $adatrans = [];
        $str = "select distinct jenispekerjaan from " . $dbname . ".vhc_rundt";
        $res = fetchData($str);
        foreach ($res as $bar) {
            $adatrans[$bar['jenispekerjaan']] = $bar['jenispekerjaan'];
        }

        echo "<table id=mytable class='sortable' cellspacing='1' border='0' cellpadding='5' width=100%>
                    <thead>
                        <tr class=rowheader>
                            <th rowspan=2 style='text-align:center;'>No</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['namakegiatan'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['satuan'] . "</th>
                            <th rowspan=2 style='text-align:center;width:40px'>" . $_SESSION['lang']['kodekegiatan'] . " Kebun</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['namakegiatan'] . " Kebun</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['tipe'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['kelompok'] . "</th>
                            <th rowspan=2 style='text-align:center;'>Kelompok " . $_SESSION['lang']['kendaraan'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['jeniskend'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['status'] . "</th>
                            <th rowspan=2 style='text-align:center;'>" . $_SESSION['lang']['updateby'] . "</th>
                            <th colspan=2 style='text-align:center;'>" . $_SESSION['lang']['action'] . "</th>
                        </tr>
                        <tr class=rowheader>
                            <th style='display:none;'></th>
                        </tr>
                    </thead>
                    <tbody>";
        $optnm = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "detail=1 and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7");
        $arrklp = array('AB' => 'Alat Berat', 'MS' => 'Mesin - Mesin', 'KD' => 'Kendaraan', 'GLOBAL' => 'GLOBAL');
        $nmjnsvhc = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
        $nmjnsvhc['GLOBAL'] = 'GLOBAL';

        $str1 = "select * from " . $dbname . ".vhc_kegiatan order by noakun, kodekegiatan";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $e = "";
            if (@$optnm[$bar1->noakun] == '') {
                $e = " style=background-color:red title='Nomor Akun Salah atau Nomor akun dinonaktifkan!'";
            }
            $d = $bar1->noakun;
            if ($d != $n) {
                echo "<tr class=rowcontent style=font-style:italic;background-color:#d3f2eb;>
								<td align=center></td>
								<td align=left>" . $bar1->noakun . "</td>
								<td align=left>" . @$optnm[$bar1->noakun] . "</td>
								<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
							</tr>";
            }
            $nomor++;
            echo "<tr class=rowcontent " . $e . ">
                <td align=center>" . $nomor . "</td>
                <td align=left>" . $bar1->kodekegiatan . "</td>
                <td>" . $bar1->namakegiatan . "</td>
                <td align=center>" . $bar1->satuan . "</td>
                <td align=left>" . $bar1->setupkegiatan . "</td>
                <td align=left>" . getNamaKeg($bar1->setupkegiatan) . "</td>
                <td align=center>" . $bar1->tipe . "</td>
                <td align=left>" . @$bar1->kelompok . " - " . $nmkeg[$bar1->kelompok] . "</td>
                <td align=left>" . @$arrklp[$bar1->kelompokvhc] . "</td>
                <td align=left>" . @$nmjnsvhc[$bar1->jenisvhc] . "</td>
                <td align=left>" . $arrstatus[$bar1->status] . "</td>
                <td align=left>" . getNamaKaryawan($bar1->updateby) . "</td>";

                echo"<td style='text-align:center'>";
                if ($sttskegkebun[$bar1->setupkegiatan] == 1) {
                        echo "<img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"editdata('edit','" . $bar1->kodekegiatan . "','" . $bar1->namakegiatan . "','" . strtoupper($bar1->satuan)  . "','" . $bar1->noakun . "','" . $bar1->tipe . "','" . $bar1->kelompokvhc . "','" . $bar1->jenisvhc . "','" . $nmjnsvhc[$bar1->jenisvhc] . "','" . $bar1->kelompok . "','" . $bar1->setupkegiatan . "','" . $bar1->status . "');\">";

                        if (empty($adatrans[$bar1->kodekegiatan])) {
                            echo "<img src=images/application/application_delete.png class=zImgBtn style='padding-left:10px' title='Delete' onclick=\"del('" . $bar1->kodekegiatan . "');\">";
                        }
                } else {
                    echo "Setup Kegiatan Kebun Statusnya Non Aktif";
                }
                
                $n = $d;
                echo "</td>
            </tr>";
        }
        echo "
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>";
        break;
    case 'addnew':
        echo "
				<table border=0>
					<tr>
						<td style='width:140px;'>" . $_SESSION['lang']['kelompok'] . "</td>
						<td>:</td>
						<td><select class=select2 style='width:350px' id=kelompok onchange=getnoakun()>" . $optkel . "</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['noakun'] . "</td>
						<td>:</td>
						<td colspan=7><select class=select2 id=noakun onchange=getKode() style='width:350px;'>" . $optakun . "</select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['kegiatan'] . " Kebun</td>
						<td>:</td>
						<td colspan=7><select class=select2 id=kegiatankebun style='width:350px;'></select></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['kodekegiatan'] . "</td>
						<td>:</td>
						<td><input style='width:340px;height:28px;font-weight:bold;font-size:15px;padding-left:5px' disabled type=text  id=kodekegiatan class=myinputtext></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['namakegiatan'] . "</td>
						<td>:</td>
						<td  colspan=7><input onkeydown=\"upperCaseF(this)\" style='width:340px;height:28px;font-weight:bold;font-size:15px;padding-left:5px' type=text id=namakegiatan onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
					</tr>
					<tr>
						<td>" . $_SESSION['lang']['satuan'] . "</td>
						<td>:</td>
						<td><select class=select2 style='width:350px' id=satuan>" . $optSatuan . "</select></td>
					</tr>
					<tr>
						<td >" . $_SESSION['lang']['tipe'] . "</td>
						<td >:</td>
						<td ><select class=select2 style='width:350px' id=tipe>" . $optTipe . "</select></td>
					</tr>
					<tr>
						<td >Kelompok " . $_SESSION['lang']['kendaraan'] . "</td>
						<td >:</td>
						<td ><select class=select2 style='width:350px' onchange=getjenisvhc() id=kelvhc>" . $optkelvhc . "</select></td>
					</tr>
					<tr>
						<td >" . $_SESSION['lang']['jenisvch'] . "</td>
						<td >:</td>
						<td ><select class=select2 style='width:350px' id=jnsvhc>" . $optjnsvhc . "</select></td>
					</tr>
					<tr>
						<td >" . $_SESSION['lang']['status'] . "</td>
						<td >:</td>
						<td ><select class=select2 style='width:350px' id='statuskeg'>" . $optstatus . "</select></td>
					</tr>
					<tr>
						<td colspan=2>&nbsp;</td>
						<td colspan=3>
						<input type=hidden id=method value='insert'>
						<button class=mybutton style='width:120px;height:28px;' onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
						</td>
					</tr>
				</table>";
        break;
    case 'getjenisvhc':
        $optjnsvhc = "<option value='GLOBAL'>GLOBAL</option>";
        $str = "select * from " . $dbname . ".vhc_5jenisvhc where kelompokvhc='" . $kelvhc . "' order by jenisvhc asc";
        $res = fetchData($str);
        foreach ($res as $key => $val) {
            $optjnsvhc .= "<option value=" . $val['jenisvhc'] . ">" . $val['namajenisvhc'] . "</option>";
        }
        echo $optjnsvhc;
        break;
    case 'getKode':

        $nomor = [];
        $str = "select * from " . $dbname . ".vhc_kegiatan where noakun ='" . $noakun . "' order by kodekegiatan desc";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $nomor[intval(substr($bar['kodekegiatan'], 7, 9))] = intval(substr($bar['kodekegiatan'], 7, 9));
        }
        ksort($nomor);

        if (empty($nomor)) {
            $noawal = 1;
        } else {
            $noawal = max($nomor);
            $noawal = $noawal + 1;
        }

        $notran = $noakun . addZero($noawal, 2);

        $kegkebun = "";
        if (in_array(substr($noakun, 0, 3), $arrakun) || in_array(substr($noakun, 0, 1), $arrakunn)) {
            $kegkebun = "<option value=''>Pilih Data</option>";
            $str = "select * from " . $dbname . ".setup_kegiatan where noakun ='" . $noakun . "' and status='1' and noakun in (select noakun from " . $dbname . ".keu_5akun where detail='1' and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7) order by kodekegiatan asc";
            $res = fetchData($str);
            foreach ($res as $key => $val) {
                $kegkebun .= "<option value=" . $val['kodekegiatan'] . ">" . $val['kodekegiatan'] . " - " . $val['namakegiatan'] . "</option>";
            }
        }


        echo $notran . "####" . $kegkebun;
        break;
    case 'getnoakun':
        $n          = 0;
        $whrnoakun  = '';
        $arrklp = array();
        $optakun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($_SESSION['language'] == 'EN') {
            $dd = 'namaakun1';
        } else {
            $dd = 'namaakun';
        }
        //ambil kepala akun dari setup kelompok kegiatan
        $sql = "select noakun from " . $dbname . ".setup_klpkegiatan where kodeklp='" . $kelompok . "' ";
        $hsl = fetchData($sql);
        foreach ($hsl as $v) {
            $arrklp = explode(",", $v['noakun']);
        }
        foreach ($arrklp as $key => $w) {
            $n++;
            if ($n == 1) {
                $whrnoakun .= " and noakun LIKE '" . $w . "%' ";
            } else {
                $whrnoakun .= "OR noakun LIKE '" . $w . "%'";
            }
        }
        $str = "select noakun," . $dd . " as namakegiatan from " . $dbname . ".keu_5akun where detail='1' " . $whrnoakun . " and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7 ";
        $res = fetchData($str);
        foreach ($res as $val) {
            $d = substr($val['noakun'], 0, 5);
            if ($d != $n) {
                $optakun .= "<optgroup label='" . $d . " - " . getNamaAkun($d) . "'>";
            }
            $optakun .= "<option value='" . $val['noakun'] . "'>" . $val['noakun'] . " - " . $val['namakegiatan'] . "</option>";
            $n = $d;
            if ($d != $n) {
                $optakun .= "</optgroup>";
            }
        }
        echo $optakun;
        break;
    case 'insert':
        if (in_array(substr($noakun, 0, 3), $arrakun)) {
            if ($param['kegiatankebun'] == '') {
                exit("error: Kegiatan kebun wajib diisi.");
            }
        }

        if ($kelompok == '') {
            exit("error: Kelompok kegiatan wajib diisi.");
        }
        if ($noakun == '') {
            exit("error: No akun wajib diisi.");
        }
        if ($kodekegiatan == '') {
            exit("error: Kode kegiatan wajib diisi.");
        }
        if ($namakegiatan == '') {
            exit("error: Nama kegiatan wajib diisi.");
        }
        if ($satuan == '') {
            exit("error: Satuan wajib diisi.");
        }

        $sql = "select * from " . $dbname . ".vhc_kegiatan where kodekegiatan='" . $kodekegiatan . "' ";
        $hsl = fetchdata($sql);
        if (count($hsl) > 0) {
            exit("Warning : Sudah terdapat data tersebut dengan kode kegiatan " . $kodekegiatan . " !");
        } else {
            $str = "insert into " . $dbname . ".vhc_kegiatan (setupkegiatan,kodekegiatan,kelompok,namakegiatan,satuan,noakun,tipe,kelompokvhc,jenisvhc,status,createby,createtime,updateby)
                    values('" . $param['kegiatankebun'] . "','" . $kodekegiatan . "','" . $kelompok . "','" . $namakegiatan . "','" . $satuan . "','" . $noakun . "','" . $_POST['tipe'] . "','" . $kelvhc . "','" . $jnsvhc . "','" . $statuskeg . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;
    case 'update':
        if (in_array(substr($noakun, 0, 3), $arrakun)) {
            if ($param['kegiatankebun'] == '') {
                exit("error: Kegiatan kebun wajib diisi.");
            }
        }

        if ($kelompok == '') {
            exit("error: Kelompok kegiatan wajib diisi.");
        }
        if ($noakun == '') {
            exit("error: No akun wajib diisi.");
        }
        if ($kodekegiatan == '') {
            exit("error: Kode kegiatan wajib diisi.");
        }
        if ($namakegiatan == '') {
            exit("error: Nama kegiatan wajib diisi.");
        }
        if ($satuan == '') {
            exit("error: Satuan wajib diisi.");
        }


        $str = "update " . $dbname . ".vhc_kegiatan set setupkegiatan='" . $param['kegiatankebun'] . "',namakegiatan='" . $namakegiatan . "',kelompok='" . $kelompok . "', satuan='" . $satuan . "', noakun='" . $noakun . "', kelompokvhc='" . $kelvhc . "', jenisvhc='" . $jnsvhc . "', status='" . $statuskeg . "', updateby='" . $_SESSION['standard']['userid'] . "'
            where kodekegiatan='" . $kodekegiatan . "'"; #exit("error".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".vhc_kegiatan where kodekegiatan='" . $kodekegiatan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    default:
        break;
}

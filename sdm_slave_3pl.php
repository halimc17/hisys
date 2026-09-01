<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

require_once('dompdf/autoload.inc.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

require_once "mpdf/autoload.php";


$jab   = getPostingJabatan('pendapatanlain');

$method = checkPostGet('method', '');
$per = checkPostGet('per', '');
$kom = checkPostGet('kom', '');
$kar = checkPostGet('kar', '');
$jum = checkPostGet('jum', '');
$ket = checkPostGet('ket', '');
$org = checkPostGet('org', '');
$namafile         = checkPostGet('namafile', '');
$jabatan        = checkPostGet('jabatan', '');
$tipekar        = checkPostGet('tipekar', '');
$txtBarang      = checkPostGet('txtBarang', '');
$perSch = checkPostGet('perSch', '');
$komSch = checkPostGet('komSch', '');

$nmKom = makeOption($dbname, 'sdm_ho_component', 'id,name');
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optLok = makeOption($dbname, 'datakaryawan', 'karyawanid,subbagian', "karyawanid='" . $kar . "'");
$optLoknull = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas', "karyawanid='" . $kar . "'");

$path    = "fileupload/sdm_pendapatanlain/";

switch ($method) {
    case 'loadKar':

        $where = "";
        $where = " lokasitugas='" . $org . "' and (tanggalkeluar>='" . $_SESSION['org']['period']['start'] . "' or tanggalkeluar='0000-00-00')";


        $iKar = "select namakaryawan,karyawanid,nik,subbagian,lokasitugas from " . $dbname . ".datakaryawan where  " . $where . " and alokasi='0' and tipekaryawan not in ('0')  order by namakaryawan";
        $nKar = $owlPDO->query($iKar) or die(print " Gagal: " . PDOException::getMessage());
        $nKar->setFetchMode(PDO::FETCH_ASSOC);
        $optKar = "<option value=''></option>";
        while ($dKar = $nKar->fetch()) {
            $optKar .= "<option value='" . $dKar['karyawanid'] . "'>" . $dKar['namakaryawan'] . " [ " . $dKar['nik'] . " ] " . $dKar['subbagian'] . "</option>";
        }

        echo $optKar;
        break;

    case 'getPrd':
        $optPrd .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $org . "' and sudahproses=0 order by periode desc";
        $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);
        while ($rGet = $qGet->fetch()) {
            $optPrd .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
        }
        echo $optPrd;
        break;

    case 'getPrd2':
        $optPrd .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $org . "' and sudahproses=0 order by periode desc";
        $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);
        while ($rGet = $qGet->fetch()) {
            $optPrd .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
        }
        echo $optPrd;
        break;

    case 'cekHeader':
        if ($per == '' || $kom == '' || $org == '') {
            exit("Error : Kode Organisasi, Periode Gaji, Jenis wajib diisi !");
        }

        $sCek = "select * from " . $dbname . ".sdm_pendapatanlainht where periodegaji='" . $per . "' and idkomponen='" . $kom . "' and kodeorg='" . $org . "'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rCek = owlBaris($qCek);
        if ($rCek > 0) {
            echo "warning: Data sudah pernah di input, silahkan cek pada Tab List Data.";
            exit();
        }

        $sCek = "select * from " . $dbname . ".sdm_pendapatanlainht where periodegaji='" . $per . "' and idkomponen='" . $kom . "' and kodeorg='" . $org . "' and posting='1'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rCek = owlBaris($qCek);
        if ($rCek > 0) {
            echo "warning: Data sudah diposting.";
            exit();
        }
        break;

    case 'detail':

        $where = "";
        $where = " lokasitugas='" . $org . "' and (tanggalkeluar>='" . $_SESSION['org']['period']['start'] . "' or tanggalkeluar='0000-00-00')";


        if ($tipekar != '') {
            $where .= " and tipekaryawan='" . $tipekar . "' ";
        }

        if ($jabatan != '') {
            $where .= " and kodejabatan='" . $jabatan . "' ";
        }

        if ($tipekar == '') {
            $where .= " and tipekaryawan not in ('0')";
        }

        $iKar = "select namakaryawan,karyawanid,nik,subbagian,lokasitugas from " . $dbname . ".datakaryawan where  " . $where . "   order by namakaryawan";
        // exit('warning : '.$iKar);
        $res = fetchdata($iKar);
        $jlhbrs = count($res);
        if ($jlhbrs == 0) {
            if (($tipekar != '') && ($jabatan == '')) {
                exit('Warning : Karyawan pada ' . $nmOrg[$org] . ' dengan tipe karyawan ' . $opttipe[$tipekar] . ' tidak ada.');
            } else if (($tipekar == '') && ($jabatan != '')) {
                exit('Warning : Karyawan pada ' . $nmOrg[$org] . ' dengan jabatan ' . $optjabatan[$jabatan] . ' tidak ada.');
            } else if (($tipekar != '') && ($jabatan != '')) {
                exit('Warning : Karyawan pada ' . $nmOrg[$org] . ' dengan jabatan ' . $optjabatan[$jabatan] . ' dan tipe karyawan ' . $opttipe[$tipekar] . ' tidak ada.');
            } else {
                exit('Warning : Karyawan pada ' . $nmOrg[$org] . ' tidak ada.');
            }
        } else {

            echo "
            <fieldset><legend><b>" . $_SESSION['lang']['detail'] . "</b></legend>
            <table class=sortable cellspacing=1 cellpadding =5 border=0 >
                <thead><tr class=rowheader>
                    <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center>" . $_SESSION['lang']['nik'] . "</td>
                    <td align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
                    <td align=center>" . $_SESSION['lang']['divisi'] . "</td>
                    <td align=center> Golongan </td>
                    <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
                    <td align=center>" . $_SESSION['lang']['bagian'] . "</td>
                    <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
                    <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                </tr></thead>";

            $nmgolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
            $nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

            $no2 += 0;
            $iKar = "select namakaryawan,karyawanid,nik,kodegolongan,kodejabatan,bagian,lokasitugas,subbagian from " . $dbname . ".datakaryawan where  " . $where . "  order by namakaryawan";
            $nKar = $owlPDO->query($iKar) or die(print " Gagal: " . PDOException::getMessage());
            $nKar->setFetchMode(PDO::FETCH_ASSOC);
            $no = '';
            while ($dKar = $nKar->fetch()) {
                if ($dKar['subbagian'] == '') {
                    $subag = "KANTOR";
                } else {
                    $subag = getNamaOrg($dKar['subbagian'], 'namaorganisasi');
                }
                $no += 1;
                echo "<tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td>" . $dKar['nik'] . "</td>
					<td>" . $dKar['namakaryawan'] . "<input type=hidden id=kar_" . $no2 . " value='" . $dKar['karyawanid'] . "'></td>
                    <td align=center>" . $subag . "</td>
                    <td align=center>" . $nmgolongan[$dKar['kodegolongan']] . "</td>
                    <td>" . $nmjabatan[$dKar['kodejabatan']] . "</td>
                    <td align=center>" . $dKar['bagian'] . "</td>
                    <td align=center><input type=text maxlength=20 id=jum_" . $no2 . " onkeypress=\"return angka_doang(event);\"   class=myinputtextnumber style=\"width:150px;\"></td>
					<td align=center><input type=text maxlength=50 id=ket_" . $no2 . " class=myinputtext style=\"width:100%;\"></td>
                </tr>";
                $no2 += 1;
            }
            echo "<tr class=rowcontent><td colspan=9 align=center>
                        <button class=mybutton onclick=savedt()>" . $_SESSION['lang']['save'] . "</button></td>
                  </tr>
                  <input type=hidden id=totrows value='" . $no2 . "' />
                  </table></fieldset>";
        }

        break;

    case 'savedt':
        $str = "INSERT INTO " . $dbname . ".`sdm_pendapatanlainht` (`kodeorg`,`periodegaji`, `idkomponen`, `updateby`) values ('" . $org . "','" . $per . "','" . $kom . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($str);

            $awl = 0;
            $sDet = "insert into " . $dbname . ".sdm_pendapatanlaindt (`kodeorg`, `periodegaji`, `karyawanid`, `idkomponen`, `jumlah`, `pengali`,`keterangan`, `updateby`) values ";
            for ($arDt = 0; $arDt < $_POST['totRow']; $arDt++) {
                if (($_POST['jum'][$arDt] != '') && ($_POST['jum'][$arDt] != 0)) {
                    if ($awl == 0) {
                        $awl = 1;
                        $sDet .= " ('" . $org . "','" . $per . "','" . $_POST['kar'][$arDt] . "','" . $kom . "','" . $_POST['jum'][$arDt] . "','1','" . $_POST['ket'][$arDt] . "','" . $_SESSION['standard']['userid'] . "')";
                    } else {
                        $sDet .= ",('" . $org . "','" . $per . "','" . $_POST['kar'][$arDt] . "','" . $kom . "','" . $_POST['jum'][$arDt] . "','1','" . $_POST['ket'][$arDt] . "','" . $_SESSION['standard']['userid'] . "')";
                    }
                }
            }
            try {
                // exit('warning : '.$sDet);
                $owlPDO->exec($sDet);
            } catch (PDOException $e) {
                echo " Gagal " . addslashes($e->getMessage() . "__" . $sDet);
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'saveDetail':

        $str1 = "select count(*) as jumlah from " . $dbname . ".`sdm_pendapatanlaindt` 
        where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and karyawanid = '" . $kar . "'";
        // exit('warning : '.$str1);
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        $bar1 = $res1->fetch();
        $jlh = $bar1['jumlah'];
        if ($jlh > 0) {
            exit("Warning : Karyawan sudah pernah diinput.");
        } else {
            $str = "INSERT INTO " . $dbname . ".`sdm_pendapatanlaindt` (`kodeorg`, `periodegaji`, `karyawanid`, `idkomponen`, `jumlah`, `pengali`,`keterangan`, `updateby`)
            values ('" . $org . "','" . $per . "','" . $kar . "','" . $kom . "','" . $jum . "','1','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
            //exit("Error:$i"); 
            try {
                $owlPDO->exec($str);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }


        break;
    case 'updatedetail':
        $str = "update " . $dbname . ".sdm_pendapatanlaindt set jumlah='" . $jum . "', keterangan='" . $ket . "', updateby ='" . $_SESSION['standard']['userid'] . "' where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and karyawanid = '" . $kar . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    #####LOAD DETAIL DATA	
    case 'loadDetail';
        echo "<fieldset><legend>List Data</legend><table class=sortable cellspacing=1 cellpadding =5 border=0 style='width:100%;'> 
         <thead>
                 <tr class=rowcontent>
                        <th>" . $_SESSION['lang']['nourut'] . "</th>
                        <th align=center>" . $_SESSION['lang']['nik2'] . "</th>
                        <th align=center >" . $_SESSION['lang']['namakaryawan'] . "</th>
                        <td align=center> Golongan </td>
                        <td align=center>" . $_SESSION['lang']['jabatan'] . "</td>
                        <td align=center>" . $_SESSION['lang']['bagian'] . "</td>
                        <td align=center>" . $_SESSION['lang']['jenis'] . "</td>
                        <th align=center >" . $_SESSION['lang']['jumlah'] . "</th>
                        <th align=center >" . $_SESSION['lang']['keterangan'] . "</th>
                        <th align=center colspan=2>" . $_SESSION['lang']['action'] . "</th>
                 </tr>
        </thead>
        <tbody></fieldset>";
        $no = 0;

        $orgSort = "and kodeorg='" . $org . "' ";
        $a = "select * from " . $dbname . ".sdm_pendapatanlaindt where idkomponen='" . $kom . "' and periodegaji='" . $per . "' " . $orgSort . " ";
        $b = $owlPDO->query($a) or die(print " Gagal: " . PDOException::getMessage());
        $b->setFetchMode(PDO::FETCH_ASSOC);
        while ($c = $b->fetch()) {
            $optLokD = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas', "karyawanid='" . $c['karyawanid'] . "'");
            $nik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', "karyawanid='" . $c['karyawanid'] . "'");
            $golongan = makeOption($dbname, 'datakaryawan', 'karyawanid,kodegolongan', "karyawanid='" . $c['karyawanid'] . "'");
            $jabatan = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan', "karyawanid='" . $c['karyawanid'] . "'");
            $bagian = makeOption($dbname, 'datakaryawan', 'karyawanid,bagian', "karyawanid='" . $c['karyawanid'] . "'");

            $nmgolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
            $nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
            $optNmKomponen =  makeOption($dbname, 'sdm_ho_component', 'id,name');

            $no += 1;
            echo "<tr class=rowcontent>
                        <td align=center>" . $no . "</td>
                        <td align=center>" . $nik[$c['karyawanid']] . "</td>
                        <td>" . $nmKar[$c['karyawanid']] . "</td>
                        <td align=center>" . $nmgolongan[$golongan[$c['karyawanid']]] . "</td>
                        <td align=left>" . $nmjabatan[$jabatan[$c['karyawanid']]] . "</td>
                        <td align=center>" . $bagian[$c['karyawanid']] . "</td>
                        <td align=center>" . $optNmKomponen[$c['idkomponen']] . "</td>
                        <td align=right>" . number_format($c['jumlah']) . "</td>
                        <td align=left>" . $c['keterangan'] . "</td>
                        <td align=center width=25px>
                                <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('" . $c['karyawanid'] . "','" . $c['jumlah'] . "','" . $c['keterangan'] . "');\" >					
                        </td>
                        <td align=center width=25px>
                                <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"DelDetail('" . $c['periodegaji'] . "','" . $c['karyawanid'] . "','" . $c['idkomponen'] . "');\" >					
                        </td>
                    </tr>";
            @$tot += $c['jumlah'];
        }
        echo "
                <tr class=rowcontent>
                        <td colspan=7><b>" . $_SESSION['lang']['total'] . "</b></td>
                        <td align=right><b>" . @number_format($tot) . "</b></td>
                        <td></td><td></td><td></td>
                </tr>
                <tr>
                        <td colspan=12 align=center>
                            <button class=mybutton id=cancelDetail onclick=cancel()>" . $_SESSION['lang']['selesai'] . "</button>
                        </td>
                 </tr>";
        echo "</table></fieldset>";
        break;


    ## Display Upload
    case 'displayupload';

        $optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";


        $arrUnit = getOrgDetail(1);
        foreach ($arrUnit as $key => $val) {
            $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
            $d = $induk[$key];
            if ($d != $n) {
                $optOrg .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
            }


            $optOrg .= "<option value='" . $key . "'> " . $val . "</option>";

            $n = $d;
            if ($d != $n) {
                $optOrg .= "</optgroup>";
            }
        }


        $optOrg2 = getOrgDetail(1);
        $dtisi = 1;
        $lstorg = array();
        foreach ($optOrg2 as $key => $nmorg) {
            $sGaji = "select distinct * from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $key . "'";
            $rGaji = fetchData($sGaji);
            if (count($rGaji) > 0) {
                $lstorg[$key] = $key;
            }
        }

        $optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sGet = "select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg in ('" . implode("','", $lstorg) . "')
                 and sudahproses=0 and jenisgaji='H' order by periode desc";
        $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);
        while ($rGet = $qGet->fetch()) {
            $optPeriode .= "<option value=" . $rGet['periode'] . ">" . $rGet['periode'] . "</option>";
        }

        ##jenis komponen
        $optJns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $str = "select id,name from " . $dbname . ".sdm_ho_component where plus='1' and type='additional' and `lock`='0' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optJns .= "<option value='" . $bar['id'] . "'>" . $bar['name'] . "</option>";
        }
        $optJns .= "<option value='1'>Gaji Pokok KHL/PHL</option>";


        $tab = '';
        $tab .= "<fieldset><legend><b>Form</b></legend>
            <table border=0 style='display: inline-block;vertical-align:top'>
                <tr>
                    <td>" . $_SESSION['lang']['kodeorg'] . "</td> 
                    <td>:</td>
                    <td><select id=kodeorg2 style=\"width:235px;\" onchange=getPrd2()>" . $optOrg . "</select></td>
                </tr> 
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td> 
                    <td>:</td>
                    <td><select id=periode2 style=\"width:235px;\">" . $optPeriode . "</select></td>
                </tr> 
                <tr>
                    <td>" . $_SESSION['lang']['jenis'] . " Pendapatan</td> 
                    <td>:</td>
                    <td><select id=kom2 style=\"width:235px;\">" . $optJns . "</select></td>
                </tr> 
                <tr>
                    <td>Get Karyawan</td> 
                    <td>:</td>
                    <td><button class=mybutton style=\"width:235px;\" onclick=getkaryawanid()>Get Karyawan </button></td>
                </tr> 
                <tr>
                    <td>File (.xls / .xlsx)</td> 
                    <td>:</td>
                    <td><input name='filex' type='file' id='filex' size='25' class='mybutton'></td>
                </tr> 
                <tr>
                    <td> </td>
                    <td> </td>
                    <td >
                        <button class=mybutton  onclick=previewSaveFile()>" . $_SESSION['lang']['save'] . "</button>
                        <button class=mybutton  onclick=cancelHeader()>" . $_SESSION['lang']['cancel'] . "</button>	
                    </td>
                </tr> 
                ";
        $tab .= "</table>";
        $tab .= "</fieldset>";

        echo $tab;
        break;

    case 'getkaryawanid':

        if ($org == '') {
            exit("Warning : Kode Organisasi Wajib Diisi ");
        }
        if ($per == '') {
            exit("Warning : Periode Wajib Diisi ");
        }


        $strdkar = "select karyawanid from " . $dbname . ".datakaryawan_hist a where approval_status='8' and version_type='B' and periodegaji='" . $per . "' and  lokasitugas = '" . $org . "'";
        $resdkar = fetchdata($strdkar);
        if (count($resdkar) > 0) {
            $str = "select namakaryawan,nik,karyawanid,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan_hist where approval_status='8' and lokasitugas = '" . $org . "' and version_type='B' and periodegaji='" . $per . "' and (tanggalkeluar >= '" . $per . "-01' or tanggalkeluar = '0000-00-00') order by namakaryawan";
        } else {
            $str = "select * from " . $dbname . ".datakaryawan
            where (tanggalkeluar='0000-00-00' or tanggalkeluar>'" . date('Y-m-d') . "') and lokasitugas = '" . $org . "' and tipekaryawan != 0 order by namakaryawan";
        }

        $res = fetchData($str);

        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setCellValue('A1', $_SESSION['lang']['nik']);
        $sheet->setCellValue('B1', $_SESSION['lang']['nama']);
        $sheet->setCellValue('C1', $_SESSION['lang']['jabatan']);
        $sheet->setCellValue('D1', $_SESSION['lang']['lokasitugas']);
        $sheet->setCellValue('E1', $_SESSION['lang']['divisi']);
        $sheet->setCellValue('F1', $_SESSION['lang']['jumlah']);
        $sheet->setCellValue('G1', $_SESSION['lang']['keterangan']);

        $row = 2;
        foreach ($res as $bar) {

            if ($bar['subbagian'] == '') {
                $text = "KANTOR";
            } else {
                $text = getNamaOrg($bar['subbagian']);
            }

            $sheet->setCellValueExplicit('A' . $row, $bar['nik'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $row, $bar['namakaryawan']);
            $sheet->setCellValue('C' . $row, getJabatanKaryawan($bar['karyawanid']));
            $sheet->setCellValueExplicit('D' . $row, $bar['lokasitugas'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $text);
            $row++;
        }

        $nop_ = "Daftar_Karyawan_" . $org;
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/' . $file);
                }
            }
            closedir($handle);
        }

        try {
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save("tempExcel/" . $nop_ . ".xls");
            echo "<script language=javascript>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
        } catch (Exception $e) {
            echo "<script language=javascript>
					parent.window.alert('Can't convert to excel format');
					</script>";
            exit;
        }
        break;

    case 'saveFile':

        if ($org == '') {
            exit("Warning : Kodeorg Wajib Diisi ");
        }
        if ($per == '') {
            exit("Warning : Periode Wajib Diisi ");
        }
        if ($kom == '') {
            exit("Warning : Jenis Pendapatan Wajib Diisi ");
        }

        if ($_FILES['file']['error'] == 0) {

            $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
            $file = $_FILES['file']['tmp_name'];
            if (in_array($filetype, array('.xls', '.xlsx'))) {
                $load = PHPExcel_IOFactory::load($file);
                $sheets = $load->getActiveSheet()->toArray(null, true, false, true);
                $karyawan_id = makeOption($dbname, 'datakaryawan', 'nik,karyawanid');

                $firsturut1 = null;
                $maxScanHeader = min(10, count($sheets));
                for ($r = 1; $r <= $maxScanHeader; $r++) {
                    $nikCandidate = isset($sheets[$r]['A']) ? trim((string)$sheets[$r]['A']) : '';
                    if ($nikCandidate !== '' && isset($karyawan_id[$nikCandidate])) {
                        $firsturut1 = $r - 1;
                        break;
                    }
                }

                if ($firsturut1 === null) {
                    exit("Warning : Format file tidak valid atau NIK pada file tidak ditemukan di data karyawan. Pastikan file sesuai template.");
                }

                $previewOnly = (checkPostGet('previewonly', '') == '1');
                $i = 1;

                try {
                    $owlPDO->beginTransaction();

                    foreach ($sheets as $sheet) {
                        if ($i <= $firsturut1) {
                            $i++;
                            continue;
                        }

                        if ($sheet['F'] == null || trim((string)$sheet['F']) == '') {
                            $i++;
                            continue;
                        }

                        $jumlah = normalizeNumberExcel($sheet['F']);
                        if ($jumlah == null) {
                            throw new Exception("Format jumlah tidak valid pada baris " . $i . ". Nilai: " . $sheet['F']);
                        }

                        if ($sheet['D'] != $org) {
                            $i++;
                            continue;
                        }

                        $nikExcel = trim((string)$sheet['A']);
                        if (!isset($karyawan_id[$nikExcel]) || $karyawan_id[$nikExcel] == '') {
                            throw new Exception("NIK '" . $nikExcel . "' pada baris Excel " . $i . " tidak ditemukan di master karyawan.");
                        }

                        $kodeorgExcel = trim((string)$sheet['D']);
                        $karyawanid = $karyawan_id[$nikExcel];
                        $keterangan = isset($sheet['G']) ? addslashes($sheet['G']) : '';

                        if ((float)$jumlah == 0) {
                            $str = "delete from " . $dbname . ".sdm_pendapatanlaindt
                                where kodeorg='" . $kodeorgExcel . "'
                                and periodegaji='" . $per . "'
                                and idkomponen='" . $kom . "'
                                and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($str);

                            $i++;
                            continue;
                        }

                        $jumlahSql = number_format($jumlah, 10, '.', '');
                        $jumlahSql = rtrim(rtrim($jumlahSql, '0'), '.');

                        $strc = "select * from " . $dbname . ".sdm_pendapatanlainht
                            where kodeorg='" . $kodeorgExcel . "'
                            and periodegaji='" . $per . "'
                            and idkomponen='" . $kom . "'";
                        $resc = fetchdata($strc);

                        if (count($resc) == 0) {
                            $str = "insert into " . $dbname . ".sdm_pendapatanlainht
                                (periodegaji,idkomponen,kodeorg,updateby,updatetime)
                                values ('" . $per . "','" . $kom . "','" . $kodeorgExcel . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "')";
                            $owlPDO->exec($str);
                        } else {
                            $str = "update " . $dbname . ".sdm_pendapatanlainht set
                                updateby='" . $_SESSION['standard']['userid'] . "',
                                updatetime='" . date('Y-m-d H:i:s') . "'
                                where kodeorg='" . $kodeorgExcel . "'
                                and periodegaji='" . $per . "'
                                and idkomponen='" . $kom . "'";
                            $owlPDO->exec($str);
                        }

                        $strx = "select * from " . $dbname . ".sdm_pendapatanlaindt
                            where kodeorg='" . $kodeorgExcel . "'
                            and periodegaji='" . $per . "'
                            and idkomponen='" . $kom . "'
                            and karyawanid='" . $karyawanid . "'";
                        $resx = fetchdata($strx);

                        if (count($resx) > 0) {
                            $str = "update " . $dbname . ".sdm_pendapatanlaindt set
                                jumlah='" . $jumlahSql . "',
                                pengali='1',
                                keterangan='" . $keterangan . "',
                                updateby='" . $_SESSION['standard']['userid'] . "'
                                where kodeorg='" . $kodeorgExcel . "'
                                and periodegaji='" . $per . "'
                                and idkomponen='" . $kom . "'
                                and karyawanid='" . $karyawanid . "'";
                            $owlPDO->exec($str);
                        } else {
                            $str = "insert into " . $dbname . ".sdm_pendapatanlaindt
                                (kodeorg,periodegaji,karyawanid,idkomponen,jumlah,pengali,keterangan,updateby)
                                values ('" . $kodeorgExcel . "','" . $per . "','" . $karyawanid . "','" . $kom . "','" . $jumlahSql . "','1','" . $keterangan . "','" . $_SESSION['standard']['userid'] . "')";
                            $owlPDO->exec($str);
                        }

                        $i++;
                    }

                    if ($previewOnly) {
                        $strPrev = "select karyawanid, jumlah, keterangan from " . $dbname . ".sdm_pendapatanlaindt
                            where kodeorg='" . $org . "' and periodegaji='" . $per . "' and idkomponen='" . $kom . "'
                            order by karyawanid";
                        $resPrev = fetchdata($strPrev);
                        $owlPDO->rollback();

                        $nikKar = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');

                        $infoPrev = "<table cellpadding=2 cellspacing=0 border=0 style='margin-bottom:8px'>
                            <tr><td>" . $_SESSION['lang']['kodeorg'] . "</td><td>:</td><td><b>" . $org . " - " . $nmOrg[$org] . "</b></td></tr>
                            <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td><b>" . $per . "</b></td></tr>
                            <tr><td>" . $_SESSION['lang']['jenis'] . "</td><td>:</td><td><b>" . $nmKom[$kom] . "</b></td></tr>
                        </table>";

                        if (empty($resPrev)) {
                            echo $infoPrev . "<i>Tidak ada data yang akan disimpan untuk unit ini.</i>";
                            break;
                        }

                        $totPrev = 0;
                        $streamPrev = $infoPrev . "<table cellpadding=6 cellspacing=0 border=1 class=sortable style='width:100%;border-collapse:collapse;'>
                            <thead><tr class=rowheader>
                                <th align=center>No</th>
                                <th align=center>NIK</th>
                                <th align=center>Nama Karyawan</th>
                                <th align=center>Jumlah</th>
                                <th align=center>Keterangan</th>
                            </tr></thead><tbody>";
                        $noPrev = 0;
                        foreach ($resPrev as $pr) {
                            $noPrev++;
                            $totPrev += $pr['jumlah'];
                            $streamPrev .= "<tr class=rowcontent>
                                <td align=center>" . $noPrev . "</td>
                                <td align=center>" . $nikKar[$pr['karyawanid']] . "</td>
                                <td>" . $nmKar[$pr['karyawanid']] . "</td>
                                <td align=right>" . number_format($pr['jumlah'], 0) . "</td>
                                <td>" . $pr['keterangan'] . "</td>
                            </tr>";
                        }
                        $streamPrev .= "<tr class=rowcontent style='font-weight:bold;background-color:#F5F5F5;'>
                            <td colspan=3 align=center>Total</td>
                            <td align=right>" . number_format($totPrev, 0) . "</td>
                            <td></td>
                        </tr>";
                        $streamPrev .= "</tbody></table>";
                        echo $streamPrev;
                        break;
                    }

                    $owlPDO->commit();
                } catch (Exception $e) {
                    if ($owlPDO->inTransaction()) {
                        $owlPDO->rollback();
                    }
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }
            } else {
                exit("Warning : Format file upload harus .xls atau .xlsx");
            }
        }
        break;


    case 'loadData':

        $orgSort = " left(kodeorg,4) in (select kodeorganisasi from " . $dbname . ".organisasi where"
            . "  induk='" . $_SESSION['empl']['kodeorganisasi'] . "')";
        if ($perSch != '') {
            $perSch = "and periodegaji='" . $perSch . "'";
        } else {
            $perSch = "";
        }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2 = "select * from " . $dbname . ".sdm_pendapatanlainht where " . $orgSort . " " . $perSch . " ";
        $res = fetchdata($ql2);
        $jlhbrs = count($res);

        $no = $maxdisplay;

        $i = "select * from " . $dbname . ".sdm_pendapatanlainht where " . $orgSort . " " . $perSch . " order by periodegaji desc limit " . $offset . "," . $limit . "";
        $res = fetchdata($i);
        foreach ($res as $d) {
            $no += 1;
            $a = "select sum(jumlah) as jumlah from " . $dbname . ".sdm_pendapatanlaindt 
                    where idkomponen='" . $d['idkomponen'] . "' and periodegaji='" . $d['periodegaji'] . "' and kodeorg='" . $d['kodeorg'] . "' ";
            $b = $owlPDO->query($a) or die(print " Gagal: " . PDOException::getMessage());
            $b->setFetchMode(PDO::FETCH_ASSOC);
            $c = $b->fetch();

            echo "<tr class=rowcontent style=height:20px>";
            echo "<td align=center>" . $no . "</td>";
            echo "<td align=left>" . $d['kodeorg'] . " - " . $nmOrg[$d['kodeorg']] . "</td>";
            echo "<td align=center>" . $d['periodegaji'] . "</td>";
            echo "<td align=center>" . $nmKom[$d['idkomponen']] . "</td>";
            echo "<td align=right>" . number_format($c['jumlah']) . "</td>";
            echo "<td align=center align=center>" . $nmKar[$d['updateby']] . "</td>";
            echo "<td align=center align=center>" . $d['updatetime'] . "</td>";

            if ($d['posting'] == 0) {
                echo "<td align=center width=25px>
							<img src=images/application/application_edit.png  title='update' class=zImgBtn  caption='Edit' onclick=\"edit('" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "');\"></td>";
                echo "<td align=center width=25px>
							<img src=images/application/application_delete.png  title='delete' class=zImgBtn caption='Delete' onclick=\"delHead('" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "');\"></td>";

                echo "<td align=center width=25px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"postingData('" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "');\" ></td>";
            } elseif ($d['posting'] == 1) {
                if (in_array($_SESSION['empl']['jabatan'], $jab)) {
                    $icon = "images/icons/04/16/04.png";
                    $title = "Unposting";
                    $unpost = " onclick=\"unposting('" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "');\" ";
                } else {
                    $icon = "images/icons/04/16/02.png";
                    $title = "Posted";
                    $unpost = '';
                }

                echo "<td width=25px></td><td width=25px></td>";
                echo "<td align=center width=25px>
                            <img src=" . $icon . " class=zImgBtn class=zImgBtn height='30'  title='" . $title . "' " . $unpost . " >
                        </td>";
            } else {
                echo "<td width=25px></td><td width=25px></td><td width=25px></td>";
            }

            echo "<td align=center width=25px>
                    <img onclick=PDF(event,'" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "') src=images/pdf.jpg class=zImgBtn title='MS.Pdf'>
                </td>";
            echo "<td align=center width=25px>
						<img onclick=excel(event,'" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "') src=images/excel.jpg class=zImgBtn title='MS.Excel'>
                    </td>";
            echo "<td align=center width=25px>
                        <img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $d['periodegaji'] . "','" . $d['idkomponen'] . "','" . $d['kodeorg'] . "')\" src='images/upload-2-xxl.png'/>
                </td>";
            echo "</tr>";
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
        $footd = "";
        $footd .= "</tr><tr><td colspan=13 align=center>";
        if ($page == '0') {
            $footd .= "<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd .= "<button class=mybutton onclick=loadData(" . ($page - 1) . ");>Prev</button>";
        }
        $footd .= "<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd .= "<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd .= "<button class=mybutton onclick=loadData(" . ($page + 1) . ");>Next</button>";
        }
        $footd .= "</td></tr>";

        echo $tab . "####" . $footd;
        break;
    case 'PDF':
        $tab = "<style>
                body {
                    font-family: Serif, Times-Roman;
                    font-size: 11px; /* Mengatur ukuran font untuk keseluruhan dokumen */
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 0.5px solid black;
                    padding: 5px;
                    text-align: center;
                }
                .rowheader th {
                    font-weight: bold;
                }
                footer {
                    position: fixed; 
                    bottom: -40px; 
                    left: 0px; 
                    right: 0px;
                    height: 50px; 
                }
                </style>";

        $tab .= "<table>";
        $tab .= "<thead>";
        $tab .= "<tr bgcolor=#CCCCCC class='rowheader'>";
        $tab .= "<th>Kode Organisasi</th>";
        $tab .= "<th>Periode</th>";
        $tab .= "<th>Tipe Pendapatan Lain</th>";
        $tab .= "</tr>";
        $tab .= "</tr>";
        $tab .= "</thead>";
        $tab .= "<tbody>";
        $tab .= "<tr >
                        <td>" . getNamaOrg($org) . "</td>
                        <td>" . $per . "</td>
                        <td>" . getNamaKomponenGaji($kom) . "</td>
                    </tr>";
        $tab .= "</tbody>";
        $tab .= "</table>";

        $tab .= "<br>";
        $tab .= "<br>";

        $tab .= "<table>";
        $tab .= "<thead>";
        $tab .= "<tr bgcolor=#CCCCCC class='rowheader'>";
        $tab .= "<th>No</th>";
        $tab .= "<th>Nik</th>";
        $tab .= "<th>Nama Karyawan</th>";
        $tab .= "<th>Tipe Karyawan</th>";
        $tab .= "<th>Jabatan</th>";
        $tab .= "<th>Divisi </th>";
        $tab .= "<th>Jumlah</th>";
        $tab .= "<th>Keterangan</th>";
        $tab .= "</tr>";
        $tab .= "</thead>";
        $tab .= "<tbody>";

        $no = 0;
        $str = "select * from " . $dbname . ".sdm_pendapatanlaindt where kodeorg = '" . $org . "' and periodegaji = '" . $per . "' and idkomponen= '" . $kom . "'";
        $res = fetchdata($str);
        foreach ($res as $bar) {

            if (getSubbagian($bar['karyawanid']) == "") {
                $text = "UMUM/KANTOR";
            } else {
                $text = getSubbagian($bar['karyawanid']);
            }

            $no++;
            $tab .= "<tr>
                        <td>" . $no . "</td>
                        <td align=left>" . getNik($bar['karyawanid']) . "</td>
                        <td>" . getNamaKaryawan($bar['karyawanid']) . "</td>
                        <td>" . getNamaTipekaryawan($bar['karyawanid']) . "</td>
                        <td>" . getJabatanKaryawan($bar['karyawanid']) . "</td>
                        <td>" . $text . "</td>
                        <td align=right>" . number_format($bar['jumlah']) . "</td>
                        <td>" . $bar['keterangan'] . "</td>
                    </tr>";
            $ttl += $bar['jumlah'];
        }

        $tab .= "<tr>";
        $tab .= "<td colspan=6> <b>Total</b> </td>";
        $tab .= "<td> <b>" . number_format($ttl) . "</b> </td>";
        $tab .= "<td></td>";
        $tab .= "</tr>";



        // Tambahkan baris data di sini
        $tab .= "</tbody>";
        $tab .= "</table>";

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($tab);
        $file_name = "Pendapata_Lain_" . $per . "";
        $mpdf->SetTitle($file_name);
        $mpdf->Output($file_name, 'I');
        break;
    case 'posting':
        $str = "select * from " . $dbname . ".sdm_5periodegaji where 1=1 and kodeorg = '" . substr($org, 0, 4) . "' and periode ='" . $per . "' and sudahproses='1'";
        $res = fetchdata($str);
        if (count($res) > 0) {
            #exit("Error : Periode gaji sudah di tutup.");
        }

        $str = "update " . $dbname . ".sdm_pendapatanlaindt set posting='1', updateby ='" . $_SESSION['standard']['userid'] . "' where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and periodegaji = '" . $per . "'";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }


        $str = "update " . $dbname . ".sdm_pendapatanlainht set posting='1', updateby ='" . $_SESSION['standard']['userid'] . "' where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and periodegaji = '" . $per . "'";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case 'unposting':
        $str = "select * from " . $dbname . ".sdm_5periodegaji where 1=1 and kodeorg = '" . substr($org, 0, 4) . "' and periode ='" . $per . "' and sudahproses='1'";
        $res = fetchdata($str);
        if (count($res) > 0) {
            exit("Error : Periode gaji sudah di tutup.");
        }

        $str = "update " . $dbname . ".sdm_pendapatanlaindt set posting='0', updateby ='" . $_SESSION['standard']['userid'] . "' where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and periodegaji = '" . $per . "'";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $str = "update " . $dbname . ".sdm_pendapatanlainht set posting='0', updateby ='" . $_SESSION['standard']['userid'] . "' where periodegaji='" . $per . "' and idkomponen ='" . $kom . "' and kodeorg = '" . $org . "' and periodegaji = '" . $per . "'";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case 'delHead':

        $i = "delete from " . $dbname . ".sdm_pendapatanlainht where idkomponen='" . $kom . "' and periodegaji='" . $per . "' and kodeorg='" . $org . "'";
        try {
            $owlPDO->exec($i);
            $x = "delete from " . $dbname . ".sdm_pendapatanlaindt where idkomponen='" . $kom . "' and periodegaji='" . $per . "' and kodeorg='" . $org . "'";
            try {
                $owlPDO->exec($x);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $y = "delete from " . $dbname . ".listfile_sdm_pendapatanlain where idkomponen='" . $kom . "' and periode='" . $per . "' and kodeorg='" . $org . "'";
            try {
                $owlPDO->exec($y);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;

    case 'deleteDetail':
        // if(!empty($optLok[$kar]))
        // {
        // $str="delete from ".$dbname.".sdm_pendapatanlaindt where karyawanid='".$kar."' and kodeorg='".$optLok[$kar]."' and idkomponen='".$kom."' and periodegaji='".$per."'";

        // }
        // else
        // {
        $str = "delete from " . $dbname . ".sdm_pendapatanlaindt where karyawanid='" . $kar . "' and kodeorg='" . $optLoknull[$kar] . "' and idkomponen='" . $kom . "' and periodegaji='" . $per . "'";
        // }
        // exit("Error : ".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'showupload':
        $tab = "";
        $tab .= "<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab .= "<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td>:</td>
				<td>
					<label id='kodeorg' style='display:none'>" . $org . "</label>
					<label style='font-weight:bold'>" . $nmOrg[$org] . "</label>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['periode'] . "</td>
				<td>:</td>
				<td>
					<label id='periode' style='font-weight:bold'>" . $per . "</label>
				</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['jenis'] . "</td>
				<td>:</td>
				<td>
					<label id='komponen' style='display:none'>" . $kom . "</label>
					<label style='font-weight:bold'>" . $nmKom[$kom] . "</label>
				</td>
			</tr>";
        $tab .= "<tr><td colspan=4><hr></td></tr>
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
			<p />";

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

    case 'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;

        if ($data['fileupload'] != '') {
            if ($_FILES['file']['error'] == 0) {
                $filetype = strtolower('.' . substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
                $filename = $pt . "_" . $his . "" . $filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);

                if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                    if ($_FILES['file']['size'] <= 2500000) {
                        $str = "insert into " . $dbname . ".listfile_sdm_pendapatanlain values ('','" . $org . "','" . $per . "','" . $kom . "','" . $filename . "','" . $filetype . "','1','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i') . "')";
                        try {
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }
                            file_put_contents($path . $filename, $file_tmpname);
                        } catch (PDOException $e) {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    } else {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                } else {
                    exit("Warning : Format file upload harus .jpg atau .jpeg");
                }
            }
        }
        break;
    case 'loadfiles':
        $no = 0;
        $tab = "";
        $str = "select * from " . $dbname . ".listfile_sdm_pendapatanlain where kodeorg = '" . $org . "' and status='1' and periode='" . $per . "' and idkomponen='" . $kom . "'";
        $res = fetchData($str);
        if (empty($res)) {
            $tab .= "<tr class=rowcontent><td colspan=4 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
        } else {
            foreach ($res as $key => $val) {
                $no++;
                $tab .= "<tr class=rowcontent>
					<td style='text-align:center'>" . $no . "</td>";

                if ($val['formaticon'] == '.jpeg' || $val['formaticon'] == '.jpg') {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
                } elseif ($val['formaticon'] == '.png') {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
                } elseif ($val['formaticon'] == '.pdf') {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
                } elseif ($val['formaticon'] == '.xls' || $val['formaticon'] == '.xlsx') {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
                } elseif ($val['formaticon'] == '.doc' || $val['formaticon'] == '.docx') {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
                } else {
                    $tab .= "<td style='text-align:center'>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
                }

                $tab .= "<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','" . $val['namafile'] . "')\">" . $val['namafile'] . "</td>
					<td align=center>
						<a href='" . $path . $val['namafile'] . "' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";

                $tab .= "<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('" . $val['kodeorg'] . "','" . $val['periode'] . "','" . $val['idkomponen'] . "','" . $val['namafile'] . "');\" >";

                $tab . "	</td>
				</tr>";
            }
        }

        echo $tab;
        break;
    case 'deletefile':
        $str = "delete from " . $dbname . ".listfile_sdm_pendapatanlain where kodeorg='" . $org . "' and periode='" . $per . "' and idkomponen='" . $kom . "' and namafile='" . $namafile . "'";
        try {
            $owlPDO->exec($str);
            $pathx = $path . $namafile;
            unlink($pathx);
        } catch (PDOException $e) {
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;


    default;
}

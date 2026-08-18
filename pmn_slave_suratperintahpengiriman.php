<?
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
// include_once('lib/rTable.php');
require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$nodo = checkPostGet('nodo', '');
$param = $_POST;
$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$nmpt = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', 'tipe="PT"');
$nmkapalponton = makeOption($dbname, 'pmn_5kapalponton', 'kode,nama');
$optPt = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept');
$nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$str = "select * from " . $dbname . ".datakaryawan where tanggalkeluar='0000-00-00' and tipekaryawan in ('0','7','8','9') order by namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $nmkaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
    $kodejabatan[$bar['karyawanid']] = $bar['kodejabatan'];
}

// exit("Error:".$proses);
//exit('error'.$param['proses']);
//
switch ($proses) {

    case 'posting':


        #kirim Email dulu
        $str = "select * from " . $dbname . ".setup_parameterappl where kodeparameter='PMDO'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $daftaremail = explode(",", $bar['nilai']);
        $countemail = count($daftaremail);
        for ($i = 0; $i < 3; $i++) {
            #send an email to incharge person
            $to = $daftaremail[$i];
            $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
            $subject = "[Notifikasi]DO Pemasaran No. " . $param['nodo'];
            $body = "<html>
						 <head>
						 <body>
						   Dengan Hormat,<br>
						   <br>
						   Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " telah melakukan posting nomor DO 
						   dengan nomor <b>" . $param['nodo'] . "</b>, silahkan dicek melalui link dibawah.
						   <br>
						   <br>
						   <br>
						   Regards,<br>
						   Owl-Plantation System.
						 </body>
						 </head>
					   </html>
					   ";
            //$kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;	
        }

        #update flag

        $str = "update " . $dbname . ".pmn_suratperintahpengiriman set posting='1',postingdate='" . date('Y-m-d') . "',"
            . "postingby='" . $_SESSION['standard']['userid'] . "' where  nodo='" . $param['nodo'] . "' ";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;


    case 'loadData':
        if (!empty($param['nodo'])) {
            $where = " a.nodo like '%" . $param['nodo'] . "%'";
        }
        if (!empty($param['tanggalCr'])) {
            $tgrl = explode("-", $param['tanggalCr']);
            $ert = $tgrl[2] . "-" . $tgrl[1] . "-" . $tgrl[0];
            $where = " left(a.tanggaldo,10) = '" . $ert . "'";
        }
        if (!empty($param['produksch'])) {
            $wher = "and kodebarang = '" . $param['produksch'] . "'";
            $whera = "and a.kodebarang = '" . $param['produksch'] . "'";
        } else {
            $wher = '';
            $whera = '';
        }
        $limit = 15;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $maxdisplay = ($page * $limit);

        $param['nodo'] = isset($param['nodo']) ? $param['nodo'] : '';
        $param['tanggalCr'] = isset($param['tanggalCr']) ? $param['tanggalCr'] : '';
        $offset = $page * $limit;
        $sql = "select count(*) jmlhrow from " . $dbname . ".pmn_suratperintahpengiriman where nodo like '%" . $param['nodo'] . "%' and tanggaldo like '%" . tanggalsystem($param['tanggalCr']) . "%' " . $wher . " order by tanggaldo desc";
        $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $_POST['nokontrak'] = isset($_POST['nokontrak']) ? $_POST['nokontrak'] : '';

        if ($_POST['nokontrak'] != '') {
            $schkontrak = " and a.nokontrak like '%" . $_POST['nokontrak'] . "%'";
        }


        $schkontrak = isset($schkontrak) ? $schkontrak : '';
        $str = "select a.*,c.namacustomer,d.namabarang,b.kuantitaskontrak from " . $dbname . ".pmn_suratperintahpengiriman a
			left join " . $dbname . ".pmn_kontrakjual b
			on a.nokontrak = b.nokontrak
			left join " . $dbname . ".pmn_4customer c
			on b.koderekanan = c.kodecustomer
			left join " . $dbname . ".log_5masterbarang d
			on b.kodebarang = d.kodebarang 
			where a.nodo like '%" . $param['nodo'] . "%' "
            . " " . $schkontrak . " and tanggaldo like '%" . tanggalsystem($param['tanggalCr']) . "%' " . $whera . "
			order by a.tanggaldo desc
            limit " . $offset . "," . $limit . " ";

        $tab = '';
        $nor = 0;
        $nor = $maxdisplay;
        $qstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($rstr = $qstr->fetch()) {

            $hide = "";
            $post = "<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
                    onclick=\"posting('" . $rstr['nodo'] . "');\" >";
            if ($rstr['posting'] == 1) {
                $hide = "hidden";
                $post = "<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting');\" >";
            }

            $optDibuat = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $rstr['dibuatoleh'] . "'");

            $nor += 1;
            $tab .= "<tr class=rowcontent>
				<td align=center>" . $nor . "</td>
				<td>" . $rstr['nodo'] . "</td>
				<td align=center>" . tanggalnormal($rstr['tanggaldo']) . "</td>
				<td>" . $rstr['nokontrak'] . "</td>
				<td>" . $rstr['namacustomer'] . "</td>
				<td>" . $rstr['namabarang'] . "</td>
				<td align=right>" . number_format($rstr['qty'], 0) . "</td>
				<td align=right>" . number_format($rstr['toleransi'], 2) . "</td> 
                <td align=right>" . $rstr['harga'] . "</td>
                <td align=left>" . @$nmkapalponton[$rstr['namakapal']] . "</td>
                <td align=left>" . @$nmkapalponton[$rstr['namaponton']] . "</td>
				<td>" . $optDibuat[$rstr['dibuatoleh']] . "</td>";
            $tab .= "<td style='text-align:center;vertical-align:middle'><label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('" . $rstr['nodo'] . "',event)\">History Approval</label></td>";
            if ($rstr['posting'] == 0 || $rstr['posting'] == 3) {
				$tab .= "<td align=center> <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $rstr['nodo'] . "');\"> </td>";
				$tab .= "<td align=center> <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('" . $rstr['nodo'] . "');\" > </td>";
				$tab .= "<td align=center> <img src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick='form_ajukan(`" . $rstr['nodo'] . "`)'> </td>";
			} else if ($rstr['posting'] == 9) {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'> </td>";
			} else if ($rstr['posting'] == 2) {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'> </td>";
			} else {
				$tab .= "<td colspan=2></td>";
				$tab .= "<td align=center> <img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted'> </td>";
            } 
            
			$tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Detail " . $rstr['nodo'] . "' onclick=\"pdf('" . $rstr['nodo'] . "');\" ></td>";				
			$tab.="</tr>";
                
        }
        $skeupenagih = "select count(*) as rowd from " . $dbname . ".pmn_suratperintahpengiriman";
        $qkeupenagih = $owlPDO->query($skeupenagih) or die(print " Gagal: " . PDOException::getMessage());
        $rkeupenagih = owlBaris($qkeupenagih);
        $totrows = ceil($rkeupenagih / $limit);

        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        // $footd = "</tr>
        //     <tr><td colspan=17 align=center>

        //     <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
        //     <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
        //     <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
        //     </td>
        //     </tr>";
        $footd = createpaging($jlhbrs, $limit, $page, '17', 'loadData', 'getPage');
        echo $tab . "####" . $footd;
        break;

    case 'getFormNosipb':
        $optSupplierCr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sSuplier = "select distinct supplierid,namasupplier from " . $dbname . ".log_5supplier order by namasupplier asc";
        $qSupplier = $owlPDO->query($sSuplier) or die(print " Gagal: " . PDOException::getMessage());
        $qSupplier->setFetchMode(PDO::FETCH_ASSOC);
        while ($rSupplier = $qSupplier->fetch()) {
            $optSupplierCr .= "<option value='" . $rSupplier['supplierid'] . "'>" . $rSupplier['namasupplier'] . " [" . @$rSupplier['status'] . "]</option>";
        }
        $form = "<fieldset style=float: left;>
               <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['NoKontrak'] . "</legend>
               " . $_SESSION['lang']['NoKontrak'] . "&nbsp; <input type=text class=myinputtext id=nosipbcr />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=findNosipb('" . $param['status'] . "')>" . $_SESSION['lang']['find'] . "</button></fieldset>
               <fieldset><legend>" . $_SESSION['lang']['result'] . "</legend><div id=container2 style=overflow:auto;max-width:700px;max-height:430px;></fieldset></div>";
        echo $form;
        break;

    case 'getnosibp':
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab .= "<thead>";
        $tab .= "<tr><td align=center>" . $_SESSION['lang']['NoKontrak'] . "</td>";
        $tab .= "<td align=center width=50px>" . $_SESSION['lang']['kodecustomer'] . "</td>";
        $tab .= "<td align=center>" . $_SESSION['lang']['namacust'] . "</td>";
        $tab .= "<td style='text-align:center'>Qty Outstanding</td></tr></thead><tbody>";

        // if ($param['status'] == 'Eksternal') {
        // $whereStatus = " b.statusinteks = 'Eksternal'";
        // } else {
        // $whereStatus = " b.statusinteks = 'Internal' and a.koderekanan = 'API'";
        // }

        // $sdata = "select distinct a.franco,a.nokontrak,a.koderekanan,b.namacustomer,a.kodept,a.matauang,a.hargasatuan,a.kuantitaskontrak,a.kuantitaskirim,a.kodeorg,a.tanggalkirim,a.sdtanggal,a.lokasikontrak from " . $dbname . ".pmn_kontrakjual a
        // left join " . $dbname . ".pmn_4customer b on a.koderekanan=b.kodecustomer
        // where " . $whereStatus . " and a.nokontrak like '%" . $param['txtfind'] . "%'";

        $sdata = "select distinct a.franco,a.nokontrak,a.koderekanan,b.namacustomer,a.kodept,a.matauang,a.hargasatuan,a.kuantitaskontrak,a.kuantitaskirim,a.kodeorg,a.tanggalkirim,a.sdtanggal,a.lokasikontrak from " . $dbname . ".pmn_kontrakjual a
				left join " . $dbname . ".pmn_4customer b on a.koderekanan=b.kodecustomer
				where 1=1 " . $whereStatus . " and a.nokontrak like '%" . $param['txtfind'] . "%'";
        $sQty = selectQuery($dbname, 'pmn_suratperintahpengiriman', "SUM(qty) as QTY, nokontrak") . " group by nokontrak";
        $rQty = fetchData($sQty);
        $optQty = array();
        foreach ($rQty as $row) {
            $optQty[$row['nokontrak']] = $row['QTY'];
        }
        $qdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while ($rdata = $qdata->fetch()) {
            $optIns = makeOption($dbname, 'pmn_5lokasikontrak', 'id,inisial', "id='" . $rdata['lokasikontrak'] . "'");
            if (($rdata['kuantitaskontrak'] - @$optQty[$rdata['nokontrak']]) > 0) {
                $brt = "style=cursor:pointer; onclick=setData('" . $rdata['nokontrak'] . "','" . $rdata['koderekanan'] . "','" . $rdata['kodept'] . "','" . $rdata['kodeorg'] . "','" . tanggalnormal($rdata['tanggalkirim']) . "','" . tanggalnormal($rdata['sdtanggal']) . "','" . $rdata['kuantitaskontrak'] . "','" . $param['status'] . "','" . $rdata['franco'] . "','" . $optIns[$rdata['lokasikontrak']] . "')";
                $tab .= "<tr " . $brt . " class=rowcontent><td>" . $rdata['nokontrak'] . "</td>";
                $tab .= "<td align=center>" . $rdata['koderekanan'] . "</td>";
                $tab .= "<td>" . $optnmcust[$rdata['koderekanan']] . "</td>";
                $tab .= "<td style='text-align:right'>" . number_format(($rdata['kuantitaskontrak'] - @$optQty[$rdata['nokontrak']]), 2) . "</td></tr>";
            }
        }
        $tab .= "</tbody></table>";
        echo $tab;
        break;

    case 'insert':
        if ($param['tanggalsurat'] == '') {
            exit("error: Tanggal surat tidak boleh kosong");
        }
        // if ($param['waktupenyerahan'] == '') 
        // {
        // exit("error: Waktu Penyerahan tidak boleh kosong");
        // }
        if ($param['tempatpenyerahan'] == '') {
            exit("error: Tempat Penyerahan tidak boleh kosong");
        }
        if ($param['pt'] == '') {
            exit("error: PT  tidak boleh kosong");
        }
        if ($param['kodebarang'] == '') {
            exit("error: Barang tidak boleh kosong");
        }

        if ($param['subsidi'] == '') {
            $param['subsidi'] = 0;
        }

        if ($param['statpph'] == '') {
            $param['statpph'] = 1;
        }

        if ($param['subsidi'] == '') {
            $param['subsidi'] = 0;
        }

        // $iCekDo = " select count(*) as jumlah from " . $dbname . ".pmn_suratperintahpengiriman  where nodo='" . $param['nodo'] . "' ";
        // $nCekDo = $owlPDO->query($iCekDo) or die(print " Gagal: " . PDOException::getMessage());
        // $nCekDo->setFetchMode(PDO::FETCH_ASSOC);
        // while ($dCekDo = $nCekDo->fetch()){
        // if ($dCekDo['jumlah'] > 0){
        // $nodo = $param['nodo'];
        // $setJlh = 1;
        // }else{
        // $nodo =  ();  
        // $setJlh = 0;
        // }
        // }

        if ($param['nodo'] == '') {
            $nodo = generateNoDO($param['nokontrak']);
            $flag = "insert";
        } else {
            $nodo = $param['nodo'];
            $flag = "update";
        }
 

        $iCek = "select sum(qty) as qtysipb from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $param['nokontrak'] . "' ";
        $nCek = $owlPDO->query($iCek) or die(print " Gagal: " . PDOException::getMessage());
        $nCek->setFetchMode(PDO::FETCH_ASSOC);
        $dCek = $nCek->fetch();
        $qtysipb = $dCek['qtysipb'];

        if ($qtysipb == '' || $qtysipb == NULL) {
            $qtysipb = 0;
        }

        $iQty = "select kuantitaskontrak,kodebarang from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $param['nokontrak'] . "'";
        $nQty = $owlPDO->query($iQty) or die(print " Gagal: " . PDOException::getMessage());
        $nQty->setFetchMode(PDO::FETCH_ASSOC);
        $dQty = $nQty->fetch();
        $qtykontrak = $dQty['kuantitaskontrak'];
        $kdbrgkontrak = $dQty['kodebarang'];

        if ($flag == "insert") {
            $totalKeseluruhan = $qtysipb + $param['qty'];
        } else {
            $optEdit = makeOption($dbname, 'pmn_suratperintahpengiriman', 'nodo,qty', "nodo = '" . $param['nodo'] . "'");
            $totalKeseluruhan = $qtysipb - $optEdit[$param['nodo']] + $param['qty'];
        }

        $sisa = $qtykontrak - $totalKeseluruhan;

        // if ($totalKeseluruhan > $qtykontrak) {
        // exit("Error:QTY telah melebihi kontrak\n kontrak : $qtykontrak, total DO $totalKeseluruhan, sisa yang di perbolehkan : $sisa ");
        // }

        $time = date('Y-m-d H:i:s');
        if ($flag == "insert") {
            $str = "insert into " . $dbname . ".pmn_suratperintahpengiriman 
			(nodo,tanggaldo,nokontrak,spkpemuat,nokontrakinternal,tempatpenyerahan,
			waktupenyerahan,keterangan,dibuatoleh,jabatan,kepada,ttd1,ttd2,qty,harga,
			pphditanggung,subsidi,status_timbangan,transportir,lokasi,kodebarang,sistempenyerahan,
			namakapal,tanggaltiba1,tanggaltiba2,kualitas,pelabuhanmuat,
			agenkapal,pelabuhanbongkar,kondisiair,createtime,pt,namaponton,tglberangkat,toleransi,kgtoleransi,createby,noakundebet) values 
			('" . $nodo . "','" . tanggalsystem($param['tanggalsurat']) . "',"
                . "'" . $param['nokontrak'] . "','" . $param['spkpmuat'] . "','" . $param['nokontrakInternal'] . "','" . $param['tempatpenyerahan'] . "',"
                . "'" . $param['waktupenyerahan'] . "','" . $param['lain'] . "',"
                . "'" . $_SESSION['standard']['userid'] . "','" . $param['jabatan'] . "',"
                . "'" . $param['kepada'] . "','" . $param['ttd1'] . "','" . $param['ttd2'] . "','" . str_replace(',', '', $param['qty']) . "','" . str_replace(',', '', $param['harga']) . "',
				'" . $param['statpph'] . "','" . $param['subsidi'] . "','" . $param['statTimbangan'] . "','" . $param['transportir'] . "',
				'" . $param['lokasido'] . "','" . $param['kodebarang'] . "','" . $param['penyerahan'] . "','" . $param['nmkpl'] . "',
				'" . tanggalsystem($param['tgltiba1']) . "','" . tanggalsystem($param['tgltiba2']) . "','',
				'" . $param['plbmuat'] . "','" . $param['agen'] . "','" . $param['plbbongkar'] . "',
				'" . $param['kondisi'] . "','" . $time . "','" . $param['pt'] . "','" . $param['namaponton'] . "',
				'" . tanggalsystem($param['tglberangkat']) . "','" . $param['toleransi'] . "','" . $param['kgtoleransi'] . "',
                '" . $_SESSION['standard']['userid'] . "','" . $param['noakun'] . "')";
            //exit('error'.$str);
        } else {
            $dataUpd = array(
                'tanggaldo' => tanggalsystem($param['tanggalsurat']),
                'nokontrak' => $param['nokontrak'],
                'tempatpenyerahan' => $param['tempatpenyerahan'],
                'pphditanggung' => $param['statpph'],
                'subsidi' => $param['subsidi'],
                'sistempenyerahan' => $param['penyerahan'],
                'namakapal' => $param['nmkpl'],
                'tanggaltiba1' => tanggalsystem($param['tgltiba1']),
                'tanggaltiba2' => tanggalsystem($param['tgltiba2']),
                'spkpemuat' => $param['spkpmuat'],
                'kodebarang' => $param['kodebarang'],
                'pelabuhanbongkar' => $param['plbbongkar'],
                'keterangan' => $param['lain'],
                'transportir' => $param['transportir'],
                'kepada' => $param['kepada'],
                'qty' => str_replace(',', '', $param['qty']),
                'harga' => str_replace(',', '', $param['harga']),
                'status_timbangan' => $param['statTimbangan'],
                'ttd1' => $param['ttd1'],
                'ttd2' => $param['ttd2'],
                'agenkapal' => $param['agen'],
                'pelabuhanmuat' => $param['plbmuat'],
                'kondisiair' => $param['kondisi'],
                'waktupenyerahan' => $param['waktupenyerahan'],
                'dibuatoleh' => $_SESSION['standard']['userid'],
                'jabatan' => $param['jabatan'],
                'lokasi' => $param['lokasido'],
                'pt' => $param['pt'],
                'namaponton' => $param['namaponton'],
                'tglberangkat' => tanggalsystem($param['tglberangkat']),
                'toleransi' => $param['toleransi'],
                'kgtoleransi' => $param['kgtoleransi'],
                'updateby' => $_SESSION['standard']['userid'],
                'noakundebet' => $param['noakun']

            );
            $str = updateQuery($dbname, 'pmn_suratperintahpengiriman', $dataUpd, "nodo='" . $nodo . "'");
        }

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    case 'getData':
        $sdata = "select a.*,b.koderekanan,b.kodept from " . $dbname . ".pmn_suratperintahpengiriman a
				left join " . $dbname . ".pmn_kontrakjual b
				on a.nokontrak = b.nokontrak
				where a.nodo='" . $param['nodo'] . "'";
        $qdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        $rdata = $qdata->fetch();
        echo $rdata['nokontrak'] . "###" . $rdata['nodo'] . "###" . $rdata['koderekanan'] . "###" . $rdata['kodept'] . "###" .
            tanggalnormal($rdata['tanggaldo']) . "###" . $rdata['waktupenyerahan'] . "###" . $rdata['tempatpenyerahan'] . "###" .
            $rdata['dibuatoleh'] . "###" . $rdata['keterangan'] . "###" . $rdata['jabatan'] . "###" . $rdata['kepada'] .
            "###" . $rdata['ttd1'] . "###" . number_format($rdata['qty'], 2) . "###" . $rdata['nokontrakinternal'] .
            "###" . $rdata['pphditanggung'] . "###" . $rdata['subsidi'] . "###" . number_format($rdata['harga'], 2) .
            "###" . $rdata['status_timbangan'] . "###" . $rdata['transportir'] . "###" . $rdata['lokasi'] . "###" .

            $rdata['namakapal'] . "###" . tanggalnormal($rdata['tanggaltiba1']) . "###" . tanggalnormal($rdata['tanggaltiba2']) . "###" .
            $rdata['pelabuhanbongkar'] . "###" . $rdata['pelabuhanmuat'] . "###" .
            $rdata['kondisiair'] . "###" . $rdata['pt'] . "###" . $rdata['namaponton'] . "###" .
            $rdata['ttd2'] . "###" . $rdata['spkpemuat'] . "###" . $rdata['sistempenyerahan'] . "###" . tanggalnormal($rdata['tglberangkat']) . "###" .
            $rdata['toleransi'] . "###" . $rdata['kgtoleransi'] . "###" . $rdata['noakundebet'] . "###" . $rdata['kodebarang'];
        break;

    case 'getQty':
        $sQty = "SELECT SUM(qty) as QTY FROM " . $dbname . ".pmn_suratperintahpengiriman WHERE nokontrak = '" . $param['nokontrak'] . "'";
        $qQty = $owlPDO->query($sQty) or die(print " Gagal: " . PDOException::getMessage());
        $qQty->setFetchMode(PDO::FETCH_ASSOC);
        $rQty = $qQty->fetch();
        $vQty = $param['kuantitaskontrak'] - $rQty['QTY'];
        if ($vQty < 0) {
            $hQty = 0;
        } else {
            $hQty = $vQty;
        }
        echo number_format($hQty, 2);
        break;


    case 'delData':
        $sdel = "delete from " . $dbname . ".pmn_suratperintahpengiriman where nodo='" . $param['nodo'] . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

    
	case 'form_ajukan':
		$tab = ""; 
		$optKrylevel = array();
		$jenispersetujuanx = "DO";
		$lokasitugas = $_SESSION['empl']['lokasitugas'];

		$optper4 = $optper3 = $optper2 = $optper1 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$str = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenispersetujuanx . "' and kodeunit='" . $lokasitugas . "'  order by level asc";
		$res = fetchData($str);
		foreach ($res as $key => $bar) {
			$whr		= " karyawanid='" . $bar['karyawanid'] . "'";
			$optnama 	= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $whr);

			$optKryx[$bar['level']][$bar['karyawanid']] = "<option value=" . $bar['karyawanid'] . ">" . $optnama[$bar['karyawanid']] . "</option>";
			$optKrylevel[$bar['level']] = $bar['level'];
		}
		$tab .= "<div><b>Nomor : " . $param['nodo'] . "</b></div><br>";
		$tab .= "<table cellspacing=1 border=0>
		<tr class=rowcontent hidden> 
			<td id=notran_aju>" . $param['nodo'] . "</td>
		</tr>";

		$jumlahlevel = count($optKrylevel);
		if ($jumlahlevel > 0) {
			for ($i = 1; $i <= $jumlahlevel; $i++) {
				$optKry = '';
				foreach ($optKryx[$i] as $key2 => $val) {
					$optKry .= $val;
				}
				$tab .= "<tr class=rowcontent>
						<td>Approval ke-" . $i . "</td>
						<td width=5px>:</td>
						<td><select id=kepada" . $i . " style='width:200px;'>" . $optKry . "</select></td>
					</tr>";
			}
		} else {
			$jumlahlevel = 1;
			$tab .= "<tr class=rowcontent>
					<td>Approval ke-1</td>
					<td width=5px>:</td>
					<td><select id=kepada1 style='width:200px;'></select></td>
				</tr>";
		}
		$tab .= "<tr class=rowcontent>
					<td hidden><input id=jenispersetujuanx style=display:none value=" . $jenispersetujuanx . "></td><td><input id=numrow style=display:none value=" . $jumlahlevel . "></td>
					<td align=left></td>
					</tr>
				<tr>
					<td align=left></td>
					<td align=left></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

		echo $tab;
		break;

	case 'ajukan':
		$kepada = checkPostGet('kepada', '');
		$jenispersetujuanx = checkPostGet('jenispersetujuanx', '');
		$nodo = checkPostGet('nodo', '');

		if ($kepada == '') {
			throw new PDOException('Isikan nama penyetuju.');
		}

		try {
			// Update status kontrak menjadi 'diajukan'
			$str2 = "update " . $dbname . ".pmn_suratperintahpengiriman set posting='9' where nodo = '" . $nodo . "'";
			$owlPDO->exec($str2);

			// Insert ke tabel approval untuk setiap level
			$arrkepada = explode('###', $kepada);
			foreach ($arrkepada as $i => $karyawanid) {
				if (trim($karyawanid) != '') {
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
						`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
						values ('','" . $nodo . "','" . $jenispersetujuanx . "','" . ($i + 1) . "','" . $karyawanid . "','0','','','')";
					$owlPDO->exec($str);
				}
			}
			echo "OK";
		} catch (PDOException $e) {
			echo "DB Error : " . $e->getMessage();
		}

		break;


    case 'pdf':
        $optkpda = makeOption($dbname, 'pmn_5kepada', 'id,kepada');
        $optalamat = makeOption($dbname, 'pmn_5kepada', 'id,alamat');
        $tab = '';
        $sdata = "select distinct a.*,b.koderekanan,b.ffa,b.moist,b.dirt,b.mdani,b.kdtermin,b.tglpembayarpertama from " . $dbname . ".pmn_suratperintahpengiriman a
                left join " . $dbname . ".pmn_kontrakjual b
                on a.nokontrak = b.nokontrak
                where a.nodo='" . $nodo . "'";

        $qdata = $owlPDO->query($sdata) or die(print " Gagal: " . PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        $rdata = $qdata->fetch();

        $nokontrak = $rdata['nokontrak'];
        $tanggaldo = $rdata['tanggaldo'];
        $koderekanan = $rdata['koderekanan'];
        $kodebarang = $rdata['kodebarang'];
        $qty = $rdata['qty'];
        $kdtermin = $rdata['kdtermin'];
        $transportir = $rdata['transportir'];
        $tglpembayarpertama = $rdata['tglpembayarpertama'];
        $tanggaltibapmks = tanggalbulan($rdata['tanggaltiba1']) . " s/d " . tanggalbulan($rdata['tanggaltiba2']);

        $qcust = "select namacustomer,alamat from " . $dbname . ".pmn_4customer where kodecustomer='" . $koderekanan . "'";
        $qcust = $owlPDO->query($qcust) or die(print " Gagal: " . PDOException::getMessage());
        $qcust->setFetchMode(PDO::FETCH_ASSOC);
        $rcust = $qcust->fetch();
        $namacust = $rcust['namacustomer'];
        $alamatcust = $rcust['alamat'];

        $ster = "select * from " . $dbname . ".pmn_5terminbayar where kode='" . $kdtermin . "'";
        $ster = $owlPDO->query($ster) or die(print " Gagal: " . PDOException::getMessage());
        $ster->setFetchMode(PDO::FETCH_ASSOC);
        $rter = $ster->fetch();
        $termin1 = $rter['satu'];
        $termin2 = $rter['dua'];


        $jabatanTtd = makeOption($dbname, 'pmn_5ttd', 'nama,jabatan');
        $arrnmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kelompokbarang='400'");
        $nmSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $rdata['transportir'] . "'");
        $nmpemilikSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namapemilik', "supplierid='" . $rdata['transportir'] . "'");
        $nmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
        $serah = makeOption($dbname, 'pmn_5franco', 'id_franco,franco_name');

        $namasupplier = $nmSupp[$transportir];

        $logoPath = 'images/logo/CAR.jpg';


        $tab = "<style>
            body {
                font-family: Arial, sans-serif;
                font-size: 9pt;
                margin: 0;
                padding: 0;
            }
            .container {
                width: 160mm; /* A4 width (210mm) - margins (10mm left, 10mm right) = 190mm */
                margin: 10mm auto; /* Centered with 10mm top/bottom margin */
                border: 2px solid #000; /* Border around the entire document */
                padding: 10mm; /* Internal padding */
                box-sizing: border-box; /* Include padding in width calculation */
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            .header-pt-table, .header-address-table, .details-table, .item-table, .signatures-table, .footer-notes-table {
                width: 100%;
                margin-bottom: 5px; /* Reduced margin for tighter layout */
            }
            .header-pt-table td, .header-address-table td, .details-table td, .item-table th, .item-table td, .signatures-table td, .payment-instruction-div {
                padding: 3px; /* Slightly reduced padding */
                vertical-align: top;
            }

            /* --- Header PT. Candi Artha Section --- */
            .header-pt-table {
             
                text-align: center;
                border-bottom: 1px solid #000; /* Border after PT Candi Artha */
                padding-bottom: 0;
                margin-bottom: 0;
                border-spacing: 0;
            }
            .header-pt-table td.date-col {
                
                font-weight: normal;
                text-align: right;
                vertical-align: bottom; /* Align date to bottom of cell */
                white-space: nowrap; /* Prevent date from wrapping */
            }

            /* --- Header Address Section --- */
            .header-address-table {
                
                text-align: center;
                border-bottom: 1px solid #000; /* Border after address */
                padding-bottom: 5px;
                margin-bottom: 15px; /* More space before Delivery Order title */
            }
            .delivery-order-title {
                text-align: center;
                font-size: 15pt;

                font-weight: bold;
                margin: 10px 0 20px 0;
            }

            /* --- Details Section --- */
            .details-table td {
                padding-bottom: 2px;
            }
            .details-table td:nth-child(1), /* Nama, Alamat */
            .details-table td:nth-child(3) /* SYARAT PEMBAYARAN, TUNAI, KREDIT, CICILAN */ {
                width: 100px; /* Adjust label width */
            }
            .details-table .payment-options-col {
                width: 130px; /* Width for 'SYARAT PEMBAYARAN' column */
                white-space: nowrap;
            }
            .details-table .checkbox-col {
                 width: 20px;
                 text-align: center;
            }
            .details-table .payment-terms {
                
                vertical-align: top; /* Align text at top of cell */
                line-height: 1.3; /* Adjust line height for better spacing */
                padding-left: 5px; /* Padding from checkbox */
            }
            .details-table input[type=\"checkbox\"] {
                vertical-align: middle;
                margin-right: 0; /* Adjust if needed */
            }
            .item-table {
                width: 106.5%;
                border: 3;
                padding-top: -30px;
                padding-left: -37px; 
            }
            /* --- Item Table Section --- */
            .item-table {
                margin-top: 15px; /* Space after details table */
                
            }
            .item-table th, .item-table td {
                border: 1px solid #000;
                text-align: left;
            }
            .item-table th {
                background-color: #f2f2f2;
                text-align: center;
            }
            .item-table .qty {
                text-align: right;
            }
            /* Ensure enough height for blank rows */
            .item-table tr:nth-child(n+2) td { /* Apply to all rows except header and first data row */
                height: 20px; /* Minimum height for rows to appear spaced */
            }
            .item-table tr:last-child td {
                height: 50px; /* Ensure last row has enough height for blank space */
            }


            /* --- Payment Instruction Section --- */
            .payment-instruction-div {
                width: 118.5%;  
                padding-left: -37px; 
                
                margin-top: 0px;
                margin-right: 110px;
                border-bottom: 1px solid #000;  
            }

            /* --- Signatures Section --- */
            .signatures-table {
                margin-top: 30px; 
            }
            .signatures-table td {
                width: 33%;
                text-align: center;
                vertical-align: top;
                
            }
            .signatures-table .signature-line {
                /* border-bottom: 1px solid #000; */
                display: block;
                width: 80%;
                height: 10px;;
                margin: 50px auto 5px auto; /* Space for signature, center the line */
            }

            /* --- Footer Notes Section --- */
            .footer-notes-table {
                margin-top: 0px;
                margin-left: 10px;
                
            }
            .footer-notes-table td {
                padding: 0;
                line-height: 1.2;
            }
        </style>";

        // Kontainer utama dengan border di pinggir
        $tab .= "<div class=\"container\">";
        // Header PT. CANDI ARTHA dan Tanggal
        // $tab .= "<div><img src='".$logoPath."' style='height:100px;width:200px;'></div>";
        $tab .= "
        <table style='width: 106.5%; padding-top: -30px;padding-left: -37px;' border=0 >

            <tr>
                 <td width=100px; align=center rowspan='6'><img src='" . $logoPath . "' style='height:110px;width:200px;'> </td> 
            </tr>
            <tr>
                 <td style='font-size:20px;'><u><b>PKS PT. CANDI ARTHA </b></u> </td> 
            </tr>
            <tr>
                 <td style=''> DUSUN BATU BRAJANG RT 004 RW 002 </td> 
            </tr>
            <tr>
                 <td style=''>DESA TAJAU PECAH BATU AMPAR </td> 
            </tr>
            <tr>
                 <td style=''>(031) 8713545 / 8713546 </td> 
            </tr>
            <tr>
                 <td style=''> </td> 
            </tr>
            <tr>
                <td colspan='3' style='border-bottom:2px solid #000;'></td>
            </tr>

         </table>";

        $tab .= "<table border=0 cellpadding='0'>";
        $tab .= "<tr>";
        $tab .= "<td width='50%'></td>";
        $tab .= "<td>No DO</td>";
        $tab .= "<td>: <b>" . $nodo . "</b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td></td>";
        $tab .= "<td>No SPBJB</td>";
        $tab .= "<td>: <b>$nokontrak</b></td>";
        $tab .= "</tr>";
        $tab .= "<tr>";
        $tab .= "<td></td>";
        $tab .= "<td>Tanggal</td>";
        $tab .= "<td>: <b>" . tanggalbulan($tanggaldo) . "</b></td>";
        $tab .= "</tr>";
        $tab .= "</table>";

        $tab .= "
        <div class=\"delivery-order-title\">
            <u>DELIVERY ORDER</u>
        </div>";

        // Details (Nama, Alamat, dan SYARAT PEMBAYARAN)
        $tab .= "
        <table style='width: 106.5%; padding-top: -30px;padding-left: -37px; margin-top: 60px; margin-bottom: 30px;' border=0 >

            <tr>
            <td style=\"width: 30px;\" valign=top>Nama</td>
            <td style=\"width: 200px;\">: <b>" . $namacust . "</b></td>
            <td class=\"payment-options-col\" style=\"width: 130px;\" valign=top>SYARAT PEMBAYARAN :</td>
            <td style=\"width: 30px;\"></td>
            <td style=\"width: 250px;\"></td>
            </tr>
            <tr>
            <td style=\"width: 70px;\" valign=top>Alamat</td>
            <td style=\"width: 200px;\">: <b>" . $alamatcust . "</b></td>
            <td style=\"width: 70px; text-align: left;\" valign=top>TUNAI</td>
            <td class=\"checkbox-col\" style=\"width: 30px;\"><input type=\"checkbox\" style=\"vertical-align: middle;\"></td>
            <td style=\"width: 250px;\"></td>
            </tr>
            <tr>
                <td style=\"width: 70px;\">Transportir</td>
                <td style=\"width: 200px;\">: <b>" . $namasupplier . "</b></td>
                <td style=\"width: 70px;text-align: left;padding-bottom: 35px;\" valign=top>KREDIT</td>
                <td style=\"width: 30px;padding-bottom: 35px;\"><input type=\"checkbox\" checked style=\"vertical-align: middle;\"> </td>
                <td style=\"width: 160px;\">
                    1 DP $termin1% dibayar " . tanggalbulan($tglpembayarpertama) . "<br>
                    2 Pelunasan $termin2% Maximal 5 hari setelah invoice dan BAP Asli diterima Pembeli
                </td>
            </tr>
            <tr>
                <td style=\"width: 70px;\">Tanggal Tiba PMKS</td>
                <td style=\"width: 200px;\">: {$tanggaltibapmks}</td>
                
                <td style=\"width: 70px;text-align: left;\" valign=top>CICILAN</td>
                <td style=\"width: 30px;\"><input type=\"checkbox\" style=\"vertical-align: middle;\"> </td>
                <td style=\"width: 160px;\"></td>
            </tr>
            <tr>
                <td style=\"width: 70px;\"></td>
                <td style=\"width: 200px;\"></td>
                <td valign=top></td>
                <td style=\"width: 30px;padding-bottom: 35px;\"></td>
                <td style=\"width: 160px;\"></td>
            </tr>

        </table>";
        // Item Table
        $keteranganBarang = array(
            '40000001' => array(
                "FFA Max 5",
                "M & I Max 0,5",
                "DOBI  Min 2",
                ""
            ),
            '40000002' => array(
                "Kotoran 8% Max",
                "Air 8% Max",
                "FFA 5% Max",
                "Tidak Ada Batu"
            )
        );

        $ketArr = isset($keteranganBarang[$kodebarang]) ? $keteranganBarang[$kodebarang] : array("", "", "", "");

        $tab .= "
        <table class=\"item-table\">
            <thead>
            <tr>
                <th style=\"width: 5%;\">NO</th>
                <th style=\"width: 45%;\">Description</th>
                <th style=\"width: 20%;\">Banyaknya</th>
                <th style=\"width: 30%;\">Keterangan</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style=\"text-align: center;\">1</td>
                <td>" . getNamaBrg($kodebarang) . "</td>
                <td class=\"qty\">" . number_format($qty) . " Kg</td>
                <td></td>
            </tr>";
        foreach ($ketArr as $ket) {
            $tab .= "
            <tr>
                <td></td>
                <td>$ket</td>
                <td></td>
                <td></td>
            </tr>";
        }
        $tab .= "
            <tr>
                <td style=\"height: 50px;\"></td><td></td><td></td><td></td>
            </tr>
            </tbody>
        </table>";

        // Payment Instruction
        $tab .= "
        <table style='width: 106.5%; padding-top: -30px;padding-left: -37px; margin-top: 30px; margin-bottom: 30px;' border=0 >
            <tr>
                <td valign=top height=20px;'> Pembayaran dengan Cek/Giro baru dinyatakan lunas bila telah diterima oleh Bank.</td>
            </tr>
        </table>";

        $tab .= "
        <table  style='width: 106.5%; padding-bottom: -40px;padding-left: -38px; margin-top: 0px; margin-bottom: 0px;' border=1>
            <tr>
                <td align=center valign=top height=100px>Disetujui Oleh,</td>
                <td align=center valign=top height=100px>Pembayaran Diterima Oleh,</td>
                <td align=center valign=top height=100px></td>
             </tr>
            <tr>
                <td width=25%; align=center border=1>
                    <div class=\"signature-line\"></div>
                    Kabag Trading & Purchasing
                </td>
                <td width=25%; align=center border=1>
                    <div class=\"signature-line\"></div>
                    Kabag Keuangan
                </td>
                <td width=25%; align=center border=1>
                    <div class=\"signature-line\"></div>
                     
                </td>
              
            </tr>
        </table>";


        $tab .= "</div>"; // Tutup container
        // Footer Notes
        $tab .= "
        <table class=\"footer-notes-table\">
            <tr>
                <td>Keterangan :</td>
            </tr>
            <tr>
                <td>1 Lembar Pembeli</td>
            </tr>
            <tr>
                <td>1 Lembar PKS</td>
            </tr>
            <tr>
                <td>1 Lembar Keuangan & Acounting</td>
            </tr>
            <tr>
                <td>1 Lembar Trading & Logistik</td>
            </tr>
        </table>";

        // Sekarang Anda bisa memuat HTML ini ke Dompdf
        // Pastikan Anda sudah menginisialisasi $dompdf
        // Misalnya:
        // require_once 'path/to/dompdf/autoload.inc.php';
        // $dompdf = new Dompdf();

        // Uncomment baris-baris ini untuk menghasilkan PDF
        // $dompdf->loadHtml($tab);
        // $dompdf->setPaper('A4', 'portrait');
        // $dompdf->render();
        // $dompdf->stream("delivery_order.pdf", array("Attachment" => false));

        // Untuk tujuan demonstrasi, kita akan mencetak $tab
        // echo $tab;

        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream("Realisasi BAPP", array("Attachment" => 0));
        break;

    default:
        break;
}

function generateNoDO($nokontrak)
{
    global $dbname;
    global $conn;
    global $_POST;
    global $optPt;
    global $owlPDO;

    $resnodo = "";
    $param = $_POST;
    $tgl = tanggalSystemn($param['tanggalsurat']);
     $arrcust = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,koderekanan');
    $kodecust = $arrcust[$nokontrak];
     $inisialcustomer = getinisialcustomer($kodecust);
    $inisialpt = getinisialorg($param['pt']);
    $inisialbrg = getinisialbrg($param['kodebarang']);
    // 098/viii/ca-gmk/do-cpo/2025

		$tgl = explode("-", $param['tanggalsurat']); 
		$ceknodo =  romawi(intval($tgl[1])) . "/" . $inisialpt . "-" . $inisialcustomer . "/DO-" .$inisialbrg . "/" . substr($tgl[2],0, 4);

		$sCek = "
			SELECT MAX(CAST(SUBSTRING_INDEX(nodo, '/', 1) AS UNSIGNED)) AS lastNo
			FROM " . $dbname . ".pmn_suratperintahpengiriman
			WHERE pt='" . $param['pt'] . "'
			AND LEFT(tanggaldo,4)='" . $tgl[2] . "' 
		";
		$qCek = $owlPDO->query($sCek) or die(print " Gagal: " . $sCek);
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek = $qCek->fetch();

		if (!empty($rCek['lastNo'])) {
			$nourut = addZero(($rCek['lastNo'] + 1), 3);
		} else {
			$nourut = addZero(1, 3);
		}
        
        
        $resnodo = $nourut . "/" . $ceknodo; 

    return $resnodo;
}

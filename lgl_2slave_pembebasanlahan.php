<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if (isset($_POST['proses'])) {
    $proses = $_POST['proses'];
} else {
    $proses = $_GET['proses'];
}

$kdUnit = empty($_POST['kdUnit']) ? (isset($_GET['kdUnit']) ? $_GET['kdUnit'] : '') : $_POST['kdUnit'];
$periode = empty($_POST['periode']) ? (isset($_GET['periode']) ? $_GET['periode'] : '') : $_POST['periode'];
$filter = empty($_POST['filter']) ? (isset($_GET['filter']) ? $_GET['filter'] : '') : $_POST['filter'];


$thn = explode("-", $periode);
$unitId = $_SESSION['lang']['all'];

if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " tidak boleh kosong" . $periode);
}


if ($filter != '') {
    $where.=" and jenis='" . $filter . "'";
}
if ($kdUnit != '') {
    $where.=" and kodeorg='" . $kdUnit . "'";
    $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
}

$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
    $tab = "
    <table>
    <tr><td colspan=17 align=left><b>Realisasi Ganti Rugi Lahan</b></td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    </table>";
}
$bgcoloraja = "bgcolor=#DEDEDE";
$bgcolorajax = "bgcolor=#5a63ec";
$tab = "<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
     <tr>
    <td " . $bgcoloraja . " colspan=12 align=center>SUDAH TEREALISASI</td>
    </tr>
	<tr>
        <td " . $bgcoloraja . " align=center>TANGGAL REALISASI</td>
        <td " . $bgcoloraja . " align=center>KECAMATAN</td>
        <td " . $bgcoloraja . " align=center>DESA</td>
        <td " . $bgcoloraja . " align=center>HANDIL</td>
        <td " . $bgcoloraja . " align=center>NO.</td>
        <td " . $bgcoloraja . " align=center>PEMILIK LAHAN</td>
        <td " . $bgcoloraja . " align=center>NO SPPT</td>
        <td " . $bgcoloraja . " align=center>JUMLAH SPPT</td>
        <td " . $bgcoloraja . " align=center>LUAS</td>
        <td " . $bgcoloraja . " align=center>INTI</td>
        <td " . $bgcoloraja . " align=center>PLASMA</td>
        <td " . $bgcoloraja . " align=center>KETERANGAN</td>
        ";
$tab.="</tr>
   
    </thead>
	<tbody>";

$strrp = "select * from " . $dbname . ".lgl_pembebasanlahan_vw 
         where tanggalrealisasi!='' " . $where . " ";
//exit($strrp);
$data = array();
$resrp=$owlPDO->query($strrp) or die(print " Gagal: ".PDOException::getMessage());
$resrp->setFetchMode(PDO::FETCH_ASSOC);
while ($barrp = $resrp->fetch()) {

    foreach ($barrp as $key => $val) {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']][$key]=$val;
        if($key=='jmlsppt')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotsppt']+=intval($val);
        }
        if($key=='luas')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotluas']+=doubleval($val);
        }
        if($key=='luasinti')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotinti']+=doubleval($val);
        }
        if($key=='luasplasma')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotplasma']+=doubleval($val);
        }
    }
       
}

$strrp = "select jenis,tanggalrealisasi,keterangan from " . $dbname . ".lgl_pembebasanlahan_vw 
         where tanggalrealisasi!='' " . $where . " group by jenis,tanggalrealisasi,keterangan";
$resrp=$owlPDO->query($strrp) or die(print " Gagal: ".PDOException::getMessage());
$resrp->setFetchMode(PDO::FETCH_ASSOC);
$nox=0;
$jenis='';
$notrans='';
while ($barrp = $resrp->fetch()) {
        $tab.="<tr>
        <td " . $bgcoloraja . " align=center colspan=12>".$barrp['jenis']."</td>
        </tr>";
        if($jenis!=$barrp['jenis'] || $notrans!=$barrp['keterangan'])
        {
            $nox=0;
        }
        $jenis=$barrp['jenis'];
        $notrans=$barrp['keterangan'];
        $tab.="<tr class=rowcontent>";
        $nox++;
    foreach ($data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']] as $key => $value) {
        //$tab.=$nox;
        if($key=='kodeorg' ||$key=='subtotsppt' || $key=='subtotluas' || $key=='subtotinti' || $key=='subtotplasma' || $key=='jenis')
        {}
        else{
        if($barrp['jenis']=='GRLTT' || $barrp['jenis']=='SHM')
        {
            if($key=='namapembayaran')
            {}
            else if($key=='handil')
            {
                $tab.="<td>".$value."</td>";
                $tab.="<td>".$nox."</td>";
            }
            else
            {
                $tab.="<td>".$value."</td>";
            }
        }
        else
        {
            if($key=='namamasyarakat')
            {}
            else if($key=='handil')
            {
                $tab.="<td>".$value."</td>";
                $tab.="<td>".$nox."</td>";
            }
            else
            {
                $tab.="<td>".$value."</td>";
            }
        }
        }
    }
        
        $tab.="</tr>";
        $tab.="<tr>";
        $tab.="<td " . $bgcolorajax . " colspan=7 align=center>Subtotal</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotsppt']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotluas']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotinti']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotplasma']."</td>";
        $tab.="<td " . $bgcolorajax . " ></td>";
        $tab.="</tr>";
}

$tab.="</tbody>";

$tab .= "
    <thead class=rowheader>
     <tr>
    <td " . $bgcoloraja . " colspan=12 align=center>BELUM TEREALISASI</td>
    </tr>
    <tr>
        <td " . $bgcoloraja . " align=center>TANGGAL REALISASI</td>
        <td " . $bgcoloraja . " align=center>KECAMATAN</td>
        <td " . $bgcoloraja . " align=center>DESA</td>
        <td " . $bgcoloraja . " align=center>HANDIL</td>
        <td " . $bgcoloraja . " align=center>NO.</td>
        <td " . $bgcoloraja . " align=center>PEMILIK LAHAN</td>
        <td " . $bgcoloraja . " align=center>NO SPPT</td>
        <td " . $bgcoloraja . " align=center>JUMLAH SPPT</td>
        <td " . $bgcoloraja . " align=center>LUAS</td>
        <td " . $bgcoloraja . " align=center>INTI</td>
        <td " . $bgcoloraja . " align=center>PLASMA</td>
        <td " . $bgcoloraja . " align=center>KETERANGAN</td>
        ";
$tab.="</tr>
   
    </thead>
    <tbody>";

$strrp = "select * from " . $dbname . ".lgl_pembebasanlahan_vw 
         where tanggalrealisasi is null " . $where . " ";
//exit($strrp);
$data = array();
$resrp=$owlPDO->query($strrp) or die(print " Gagal: ".PDOException::getMessage());
$resrp->setFetchMode(PDO::FETCH_ASSOC);
while ($barrp = $resrp->fetch()) {
    foreach ($barrp as $key => $val) {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']][$key]=$val;
        if($key=='jmlsppt')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotsppt']+=intval($val);
        }
        if($key=='luas')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotluas']+=doubleval($val);
        }
        if($key=='luasinti')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotinti']+=doubleval($val);
        }
        if($key=='luasplasma')
        {
        $data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotplasma']+=doubleval($val);
        }
    }
       
}

$strrp = "select jenis,tanggalrealisasi,keterangan from " . $dbname . ".lgl_pembebasanlahan_vw 
         where tanggalrealisasi is null " . $where . " group by jenis,tanggalrealisasi,keterangan";
$resrp=$owlPDO->query($strrp) or die(print " Gagal: ".PDOException::getMessage());
$resrp->setFetchMode(PDO::FETCH_ASSOC);
$nox=0;
$jenis='';
$notrans='';
while ($barrp = $resrp->fetch()) {
        $tab.="<tr>
        <td " . $bgcoloraja . " align=center colspan=12>".$barrp['jenis']."</td>
        </tr>";
        if($jenis!=$barrp['jenis'] || $notrans!=$barrp['keterangan'])
        {
            $nox=0;
        }
        $jenis=$barrp['jenis'];
        $notrans=$barrp['keterangan'];
        $tab.="<tr class=rowcontent>";
        @$nox++;
    foreach ($data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']] as $key => $value) {
        if($key=='kodeorg' || $key=='subtotsppt' || $key=='subtotluas' || $key=='subtotinti' || $key=='subtotplasma' || $key=='jenis')
        {}
        else{
        if($barrp['jenis']=='GRLTT' || $barrp['jenis']=='SHM')
        {
            if($key=='namapembayaran')
            {}
            else if($key=='handil')
            {
                $tab.="<td>".$value."</td>";
                $tab.="<td>".$nox."</td>";
            }
            else
            {
                $tab.="<td>".$value."</td>";
            }
        }
        else
        {
            if($key=='namamasyarakat')
            {}
            else if($key=='handil')
            {
                $tab.="<td>".$value."</td>";
                $tab.="<td>".$no."</td>";
            }
            else
            {
                $tab.="<td>".$value."</td>";
            }
        }
        }
    }

        $tab.="</tr>";
        $tab.="<tr>";
        $tab.="<td  " . $bgcolorajax . " colspan=7 align=center>Subtotal</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotsppt']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotluas']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotinti']."</td>";
        $tab.="<td " . $bgcolorajax . " >".$data[$barrp['jenis']][$barrp['tanggalrealisasi']][$barrp['keterangan']]['subtotplasma']."</td>";
        $tab.="<td " . $bgcolorajax . " ></td>";
        $tab.="</tr>";
}

$tab.="</tbody></table>";

switch ($proses) {
    case'getKdorg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $kdPt . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
        }
        echo $optorg;
        break;
    case'preview':
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHms");
        $nop_ = "realisasiGRL_" . $purId . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;

    case'getDetail':
        if ($_GET['tglAwal'] != '' && $_GET['tglAkhir'] != '') {
            if ($_GET['tglAwal'] < 10) {
                $_GET['tglAwal'] = "0" . $_GET['tglAwal'];
            }

            if ($_GET['tglAkhir'] < 10) {
                $_GET['tglAkhir'] = "0" . $_GET['tglAkhir'];
            }
            $wheredy = " and a.tanggal between '" . $periode . "-" . $_GET['tglAwal'] . "' and '" . $periode . "-" . $_GET['tglAkhir'] . "'";
            $tglawal = $periode . "-" . $_GET['tglAwal'];
            $tglakhir = $periode . "-" . $_GET['tglAkhir'];
            $dttglaja = $_SESSION['lang']['tanggal'] . ":" . $tglawal . " s.d. " . $tglakhir;
        } else {
            $wheredy = " and substr(a.tanggal,1,7)='" . $periode . "'";
            $dttglaja = $_SESSION['lang']['periode'] . ":" . $_GET['periode'];
        }
        if ($kdUnit != '') {
            $wheredy.=" and c.kodeorg='" . $kdUnit . "'";
        }
        $tab2 = "<link rel=stylesheet type=text/css href=style/generic.css>
            <script language=javascript1.2 src='js/generic.js'></script>
            <script language=javascript1.2 src='js/log_2produktivitas.js'></script>";
        $tab2.="<fieldset style=height:100%><legend>" . $_SESSION['lang']['detail'] . "</legend>";
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.=$dttglaja . "<br />";
        $tab2.="<input type=hidden id=kdUnit value='" . $kdUnit . "' /><input type=hidden id=periode value='" . $periode . "' />";
        $tab2.="<br /><img onclick=fisikKeExcel2(event,'log_2slave_produktivitas.php','" . $_GET['tglAwal'] . "','" . $_GET['tglAkhir'] . "','" . $_GET['purchasing'] . "') src=images/excel.jpg class=resicon title='MS.Excel'> ";
        $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
                    c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
                    from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
                    left join " . $dbname . ".log_poht c on a.nopo=c.nopo
                    left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
                    where a.nopo!=''  " . $wheredy . " and e.status!='3' and c.purchaser='" . $_GET['purchasing'] . "' 
                    group by a.kodebarang,a.nopo order by a.nopo asc";
        //echo $sListData;
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
			<thead class=rowheader>
			<tr>
				<td align=center " . $bgcoloraja . " rowspan=2>No.</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>O.std</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
				<td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
		<tbody>";
		$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
		$qListData->setFetchMode(PDO::FETCH_ASSOC);
		$rAdaData=owlBaris($qListData);
        if ($rAdaData > 0) {
            $nopodtr = 0;
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';
                if (!isset($klmpkBarang) or $klmpkBarang != $rListData['nopo']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopo'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td align=center><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=26>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                $tanggalData = '';
                if (isset($statId) and $statId == '1') {
                    if ($rListData['nopo'] != '') {
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ($rListData['idFranco'] != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                } else {
                    $lokasi = $rListData['lokasipengiriman'];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                }

                if ($rListData['tgledit'] != '') {
                    $tglEdit = tanggalnormal($rListData['tgledit']);
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ($rListData['jumlahpesan'] != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }

                $month1 = substr($rListData['tglAlokasi'], 5, 2);
                $date1 = substr($rListData['tglAlokasi'], 8, 2);
                $year1 = substr($rListData['tglAlokasi'], 0, 4);

                $month2 = substr($rListData['tanggal'], 5, 2);
                $date2 = substr($rListData['tanggal'], 8, 2);
                $year2 = substr($rListData['tanggal'], 0, 4);


                $jd1 = GregorianToJD($month1, $date1, $year1);
                $jd2 = GregorianToJD($month2, $date2, $year2);
                $jmlHari = $jd2 - $jd1;
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td align=center>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format($rListData['jumlahpesan'], 0) . "</td>";
                $tab2.="<td>" . $rListData['matauang'] . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . $rListData['namasupplier'] . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . $tanggalData . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        } else {
            $tab2.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        $tab2.="</tbody></table>";
        $tab2.="</fieldset>";
        echo $tab2;
        break;
    case'excelDetail':
        $bgcoloraja = "bgcolor=#DEDEDE";
        $brdr = 1;
		$wheredy='';
        if ($_GET['tglAwal'] != '' && $_GET['tglAkhir'] != '') {
            if ($_GET['tglAwal'] < 10) {
                $_GET['tglAwal'] = "0" . $_GET['tglAwal'];
            }

            if ($_GET['tglAkhir'] < 10) {
                $_GET['tglAkhir'] = "0" . $_GET['tglAkhir'];
            }
            $wheredy.=" and a.tanggal between '" . $periode . "-" . $_GET['tglAwal'] . "' and '" . $periode . "-" . $_GET['tglAkhir'] . "'";
            $tglawal = $periode . "-" . $_GET['tglAwal'];
            $tglakhir = $periode . "-" . $_GET['tglAkhir'];
            $dttglaja = $_SESSION['lang']['tanggal'] . ":" . $tglawal . " s.d. " . $tglakhir;
        } else {
            $wheredy.=" and substr(a.tanggal,1,7)='" . $periode . "'";
            $dttglaja = $_SESSION['lang']['periode'] . ":" . $_GET['periode'];
        }
        if ($kdUnit != '') {
            $wheredy.=" and c.kodeorg='" . $kdUnit . "'";
        }
		$tab2='';
        $tab2.=$_SESSION['lang']['detail'];
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.=$dttglaja . "<br />";


        $sListData = "select distinct a.nopp,namabarang,a.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
                    c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
                    from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
                    left join " . $dbname . ".log_poht c on a.nopo=c.nopo
                    left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
                    where a.nopo!=''  " . $wheredy . " and e.status!='3' and c.purchaser='" . $_GET['purchasing'] . "' 
                    group by a.kodebarang,a.nopo order by a.nopo asc";
        // exit("Error".$sListData);
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " rowspan=2>No.</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>O.std</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
	<tbody>";
		$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
		$qListData->setFetchMode(PDO::FETCH_ASSOC);
		$rAdaData=owlBaris($qListData);
		$nopodtr=0;
        if ($rAdaData > 0) {
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';

                if ((isset($klmpkBarang) ? $klmpkBarang : '') != $rListData['nopo']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopo'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=25>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                if ((isset($statId) ? $statId : '') == '1') {
                    if ($rListData['nopo'] != '') {
                        $tanggalData = '';
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ($rListData['idFranco'] != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = substr($rListData['tanggalkirim'], 0, 10);
                } else {
                    $lokasi = $rListData['lokasipengiriman'];
                    $tglKirim = substr($rListData['tanggalkirim'], 0, 10);
                }

                if ($rListData['tgledit'] != '') {
                    $tglEdit = $rListData['tgledit'];
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ($rListData['jumlahpesan'] != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }

                $month1 = substr($rListData['tglAlokasi'], 5, 2);
                $date1 = substr($rListData['tglAlokasi'], 8, 2);
                $year1 = substr($rListData['tglAlokasi'], 0, 4);

                $month2 = substr($rListData['tanggal'], 5, 2);
                $date2 = substr($rListData['tanggal'], 8, 2);
                $year2 = substr($rListData['tanggal'], 0, 4);


                $jd1 = GregorianToJD($month1, $date1, $year1);
                $jd2 = GregorianToJD($month2, $date2, $year2);
                $jmlHari = $jd2 - $jd1;
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . $rListData['tglAlokasi'] . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format($rListData['jumlahpesan'], 0) . "</td>";
                $tab2.="<td>" . $rListData['matauang'] . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . $rListData['namasupplier'] . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . (isset($tanggalData) ? $tanggalData : '') . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        } else {
            $tab2.="<tr class=rowcontent><td colspan=31>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }


        $tab2.="</tbody>";
        $tab2.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];

        $nop_ = "detailProduktivitas_" . $optNmOrang[$_GET['purchasing']];
        if (strlen($tab2) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab2)) {
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
    case'getPP':
		$tab2="";
        $tab2.="<link rel=stylesheet type=text/css href=style/generic.css>
            <script language=javascript1.2 src='js/generic.js'></script>
            <script language=javascript1.2 src='js/log_2produktivitas.js'></script>";
        $tab2.="<fieldset style=height:100%><legend>" . $_SESSION['lang']['detail'] . "</legend>";
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . " : " . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.=isset($dttglaja) ? $dttglaja : '' . "";

        $tab2.="<br /><img onclick=dataPPexcel(event,'log_2slave_produktivitas.php','" . $_GET['purchasing'] . "','" . $_GET['statSql'] . "') src=images/excel.jpg class=resicon title='MS.Excel'> ";
        $tab2.="<input type=hidden id=kdUnit value='" . $kdUnit . "' /><input type=hidden id=periode value='" . $periode . "' />";
        //echo $sListData;
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td align=center " . $bgcoloraja . " rowspan=2>No.</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>O.std</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td align=center " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td align=center " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td align=center " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
	<tbody>";
        if ($_GET['statSql'] == 0) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' and purchaser='" . $_GET['purchasing'] . "' and status!=3  group by a.nopp";
        } else if ($_GET['statSql'] == 1) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' 
                 and purchaser='" . $_GET['purchasing'] . "' and create_po!=1 and status!=3  group by a.nopp";
        }
		
		$qNopp=$owlPDO->query($sNnopp) or die(print " Gagal: ".PDOException::getMessage());
		$qNopp->setFetchMode(PDO::FETCH_ASSOC);
        $nopodtr=0;
		while ($rNopp = $qNopp->fetch()) {
            if ($_GET['statSql'] == 0) {
                $sListData = "select distinct b.nopp,namabarang,e.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
        c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang 
        from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
        left join " . $dbname . ".log_poht c on a.nopo=c.nopo
        left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
        where a.nopp='" . $rNopp['nopp'] . "'
        group by a.kodebarang,a.nopo order by a.nopo asc";
            } else if ($_GET['statSql'] == 1) {
                $sListData = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
            left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
            where a.nopp='" . $rNopp['nopp'] . "'
            group by a.nopp,purchaser order by a.nopp asc";
            }
			$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
			$qListData->setFetchMode(PDO::FETCH_ASSOC);
			$baris=owlBaris($qListData);
            if ($baris == 0) {
                $sdata = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
         left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
         where a.nopp='" . $rNopp['nopp'] . "'
         group by a.nopp,purchaser order by a.nopp asc";
				$qListData=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
				$qListData->setFetchMode(PDO::FETCH_ASSOC);
            }
			$nopodtr = 0;
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';

                // if ((isset($dttglaja) ? $dttglaja : '') != $rListData['nopp']) {
                    // $brs = 1;
                // }
                // if ($brs == 1) {
                    // $no = 0;
                    // $nopodtr+=1;
                    // $klmpkBarang = $rListData['nopp'];
                    // $tab2.="<tr class='rowcontent'>";
                    // $tab2.="<td><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    // $tab2.="<td colspan=25>&nbsp;</td>";
                    // $tab2.="</tr>";
                    // $brs = 0;
                // }
								
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                if ((isset($statId) ? $statId : '') == '1') {
                    if ($rListData['nopo'] != '') {
                        $tanggalData = '';
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ((isset($rListData['idFranco']) ? $rListData['idFranco'] : '') != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                } else {
                    $lokasi = isset($rListData['lokasipengiriman']) ? $rListData['lokasipengiriman'] : '';
                    $tglKirim = tanggalnormal(substr((isset($rListData['tanggalkirim']) ? $rListData['tanggalkirim'] : ''), 0, 10));
                }

                if ((isset($rListData['tgledit']) ? $rListData['tgledit'] : '') != '') {
                    $tglEdit = tanggalnormal($rListData['tgledit']);
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : '') != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }
                $jmlHari = 0;
                if ((isset($rListData['close']) ? $rListData['close'] : '') == '') {
                    $month1 = substr($rListData['tglAlokasi'], 5, 2);
                    $date1 = substr($rListData['tglAlokasi'], 8, 2);
                    $year1 = substr($rListData['tglAlokasi'], 0, 4);

                    $month2 = substr($rListData['tanggal'], 5, 2);
                    $date2 = substr($rListData['tanggal'], 8, 2);
                    $year2 = substr($rListData['tanggal'], 0, 4);


                    $jd1 = GregorianToJD($month1, $date1, $year1);
                    $jd2 = GregorianToJD($month2, $date2, $year2);
                    $jmlHari = $jd2 - $jd1;
                }
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td align=center>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : 0), 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['matauang']) ? $rListData['matauang'] : '') . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['namasupplier']) ? $rListData['namasupplier'] : '') . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . (isset($tanggalData) ? $tanggalData : '') . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        }
        $tab2.="</tbody></table>";
        $tab2.="</fieldset>";
        echo $tab2;
        break;
    case'getPPExcel':
        $bgcoloraja = "bgcolor=#DEDEDE";
        $brdr = 1;
		$tab2='';
        $tab2.="" . $_SESSION['lang']['detail'] . "";
        $tab2.="" . $_SESSION['lang']['namakaryawan'] . ":" . $optNmOrang[$_GET['purchasing']] . "<br />";
        $tab2.= (isset($dttglaja) ? $dttglaja : '') . "<br />";

        //echo $sListData;
        $tab2.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " rowspan=2>No.</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopp'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PP</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namabarang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['satuan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['nopo'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " PO</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['purchaser'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['alokasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>O.std</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['jmlhPesan'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['matauang'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['totalharga'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['namasupplier'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['tandatangan'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=6 align=center>" . $_SESSION['lang']['pembayaran'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=5 align=center>" . $_SESSION['lang']['pengiriman'] . "</td>";
        $tab2.="<td " . $bgcoloraja . " colspan=4 align=center>" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="</tr>";
        $tab2.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['tipe'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['syaratPem'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['jatuhtempo'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['noinvoice'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>"; //tagihan
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tanggalbayar'] . "</td>"; //manual
        //pengiriman
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['franco'] . "</td>"; //dari franco tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tgl_kirim'] . "</td>"; //dari tgl kirim di po
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['tglterima'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['satuan'] . "</td>"; //manual
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['biaya'] . "</td>"; //manual
        //bapb
        $tab2.="<td " . $bgcoloraja . ">" . $_SESSION['lang']['bapb'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">" . $_SESSION['lang']['tanggal'] . "</td>";
        $tab2.="<td  " . $bgcoloraja . ">Copy</td>"; //manual
        $tab2.="<td  " . $bgcoloraja . ">Original</td>"; //manual

        $tab2.="</tr></thead>
	<tbody>";
        if ($_GET['statSql'] == 0) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' and purchaser='" . $_GET['purchasing'] . "' and status!=3  group by a.nopp";
        } else if ($_GET['statSql'] == 1) {
            $sNnopp = "select distinct a.nopp from " . $dbname . ".log_prapoht a 
                 left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
                 where substr(tanggal,1,7)='" . $periode . "' 
                 and purchaser='" . $_GET['purchasing'] . "' and create_po!=1 and status!=3  group by a.nopp";
        }
		
		$qNopp=$owlPDO->query($sNnopp) or die(print " Gagal: ".PDOException::getMessage());
		$qNopp->setFetchMode(PDO::FETCH_ASSOC);
		$nopodtr=0;
		while ($rNopp = $qNopp->fetch()) {
            if ($_GET['statSql'] == 0) {
                $sListData = "select distinct b.nopp,namabarang,e.kodebarang,satuan,a.hargasatuan,namasupplier,b.tanggal as tglpp,a.nopo,c.tgledit,a.tanggal,a.statuspo,c.tanggalkirim,
        c.idFranco,c.lokasipengiriman,c.purchaser,e.tglAlokasi ,a.jumlahpesan,a.matauang, b.close 
        from " . $dbname . ".log_po_vw a left join " . $dbname . ".log_prapoht b on a.nopp=b.nopp 
        left join " . $dbname . ".log_poht c on a.nopo=c.nopo
        left join " . $dbname . ".log_prapodt e on a.nopp=e.nopp
        where a.nopp='" . $rNopp['nopp'] . "'
        group by a.kodebarang,a.nopo order by a.nopo asc";
            } else if ($_GET['statSql'] == 1) {
                $sListData = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
            left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
            where a.nopp='" . $rNopp['nopp'] . "'
            group by a.nopp,purchaser order by a.nopp asc";
            }
			$qListData=$owlPDO->query($sListData) or die(print " Gagal: ".PDOException::getMessage());
			$qListData->setFetchMode(PDO::FETCH_ASSOC);
            $baris=owlBaris($qListData);
            if ($baris == 0) {
                $sdata = "select distinct a.*,b.*,tanggal as tglpp from " . $dbname . ".log_prapoht a
         left join " . $dbname . ".log_prapodt b on a.nopp=b.nopp
         where a.nopp='" . $rNopp['nopp'] . "'
         group by a.nopp,purchaser order by a.nopp asc";
				$qListData=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
				$qListData->setFetchMode(PDO::FETCH_ASSOC);
            }
            while ($rListData = $qListData->fetch()) {
                $tglTerima = '';
                $tglEdit = '';

                if ((isset($klmpkBarang) ? $klmpkBarang : '') != $rListData['nopp']) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $no = 0;
                    $nopodtr+=1;
                    $klmpkBarang = $rListData['nopp'];
                    $tab2.="<tr class='rowcontent'>";
                    $tab2.="<td><b>" . $nopodtr . "</b></td><td colspan=5><b>" . $klmpkBarang . "</b></td>";
                    $tab2.="<td colspan=25>&nbsp;</td>";
                    $tab2.="</tr>";
                    $brs = 0;
                }
                $sRealisasi = "select distinct realisasi from " . $dbname . ".log_prapodt where nopp='" . $rListData['nopp'] . "' and kodebarang='" . $rListData['kodebarang'] . "'";
				$qRealisai=$owlPDO->query($sRealisasi) or die(print " Gagal: ".PDOException::getMessage());
				$qRealisai->setFetchMode(PDO::FETCH_ASSOC);
                $rRealisasi = $qRealisai->fetch();
                if ((isset($statId) ? $statId : '') == '1') {
                    if ($rListData['nopo'] != '') {
                        $tanggalData = '';
                        $sTagihan = "select distinct noinvoice,tanggal from " . $dbname . ".keu_tagihanht where nopo='" . $rListData['nopo'] . "'";
						$qTagihan=$owlPDO->query($sTagihan) or die(print " Gagal: ".PDOException::getMessage());
						$qTagihan->setFetchMode(PDO::FETCH_ASSOC);
                        $rTagihan = $qTagihan->fetch();
                        $tglTerima = tanggalnormal($rTagihan['tglterima']);
                        if ($rTagihan['tanggal'] != '') {
                            $tanggalData = tanggalnormal($rTagihan['tanggal']);
                        }
                        $sTransaksi = "select distinct tanggal,notransaksi from " . $dbname . ".log_transaksiht where nopo='" . $rListData['nopo'] . "'";
						$qTransaksi=$owlPDO->query($sTransaksi) or die(print " Gagal: ".PDOException::getMessage());
						$qTransaksi->setFetchMode(PDO::FETCH_ASSOC);
                        $rTransaksi = $qTransaksi->fetch();
                        $tglTerima = tanggalnormal($rTransaksi['tanggal']);
                    }
                }
                if ((isset($rListData['idFranco']) ? $rListData['idFranco'] : '') != '') {
                    $lokasi = $optFranco[$rListData['idFranco']];
                    $tglKirim = tanggalnormal(substr($rListData['tanggalkirim'], 0, 10));
                } else {
                    $lokasi = (isset($rListData['lokasipengiriman']) ? $rListData['lokasipengiriman'] : '');
                    $tglKirim = tanggalnormal(substr((isset($rListData['tanggalkirim']) ? $rListData['tanggalkirim'] : ''), 0, 10));
                }

                if ((isset($rListData['tgledit']) ? $rListData['tgledit'] : '') != '') {
                    $tglEdit = tanggalnormal($rListData['tgledit']);
                }
                if (strlen($tglKirim) < 10) {
                    $tglKirim = '';
                }
                if (strlen($tglTerima) < 10) {
                    $tglTerima = '';
                }
                $no+=1;
                $hargaBarang = 0;
                if ((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : '') != '') {
                    $hargaBarang = $rListData['jumlahpesan'] * $rListData['hargasatuan'];
                }
                $jmlHari = 0;
                if ($rListData['close'] == '') {
                    $month1 = substr($rListData['tglAlokasi'], 5, 2);
                    $date1 = substr($rListData['tglAlokasi'], 8, 2);
                    $year1 = substr($rListData['tglAlokasi'], 0, 4);

                    $month2 = substr($rListData['tanggal'], 5, 2);
                    $date2 = substr($rListData['tanggal'], 8, 2);
                    $year2 = substr($rListData['tanggal'], 0, 4);


                    $jd1 = GregorianToJD($month1, $date1, $year1);
                    $jd2 = GregorianToJD($month2, $date2, $year2);
                    $jmlHari = $jd2 - $jd1;
                }
                $tab2.="<tr class='rowcontent'>";
                $tab2.="<td>" . $no . "</td>";
                $tab2.="<td>" . $rListData['nopp'] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglpp']) . "</td>";
                $tab2.="<td>" . $rListData['kodebarang'] . "</td>";
                $tab2.="<td>" . $optNmBarang[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $optSatuan[$rListData['kodebarang']] . "</td>";
                $tab2.="<td>" . $rListData['nopo'] . "</td>";
                $tab2.="<td>" . $rListData['tanggal'] . "</td>";
                $tab2.="<td>" . $optNmOrang[$rListData['purchaser']] . "</td>";
                $tab2.="<td>" . tanggalnormal($rListData['tglAlokasi']) . "</td>";
                $tab2.="<td align=right>" . $jmlHari . "</td>";
                $tab2.="<td align=right>" . number_format($rRealisasi['realisasi'], 0) . "</td>";
                $tab2.="<td align=right>" . number_format((isset($rListData['jumlahpesan']) ? $rListData['jumlahpesan'] : 0), 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['matauang']) ? $rListData['matauang'] : '') . "</td>";
                $tab2.="<td align=right>" . number_format($hargaBarang, 0) . "</td>";
                $tab2.="<td>" . (isset($rListData['namasupplier']) ? $rListData['namasupplier'] : '') . "</td>";
                $tab2.="<td>" . $tglEdit . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTagihan['noinvoice']) ? $rTagihan['noinvoice'] : '') . "</td>";
                $tab2.="<td>" . (isset($tanggalData) ? $tanggalData : '') . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . $lokasi . "</td>";
                $tab2.="<td>" . $tglKirim . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>" . (isset($rTransaksi['notransaksi']) ? $rTransaksi['notransaksi'] : '') . "</td>";
                $tab2.="<td>" . $tglTerima . "</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="<td>&nbsp;</td>";
                $tab2.="</tr>";
            }
        }

        $tab2.="</tbody>";
        $tab2.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];

        $nop_ = "detailProduktivitasPP_" . $optNmOrang[$_GET['purchasing']];
        if (strlen($tab2) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab2)) {
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
    default:
        break;
}
?>
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
    $where.=" and kodept='" . $kdUnit . "'";
    $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
}

$strprn="select a.*, b.nama as jenissertipikat from ".$dbname.".lgl_sertipikat a 
         left join ".$dbname.".lgl_5jenissertipikat b on a.jenis=b.kode where id!='' ".$where." and substr(masaberlaku,1,7)='".$periode."'";
$resprn = fetchData($strprn);



$no=0;
$data=array();
foreach ($resprn as $key => $val) {
    $data[$no]['id']=$val['id'];
    $data[$no]['jenissertipikat']=$val['jenissertipikat'];
    $data[$no]['nohak']=$val['nohak'];
    $data[$no]['lokasi']=$val['lokasi'];

    $strprn1="select a.*, b.luas as luastanah , b.masaberlaku from ".$dbname.".lgl_sertipikatdt a 
              left join ".$dbname.".lgl_sertipikat b on a.id=b.id where b.id='".$val['id']."'";
    $resprn1 = fetchData($strprn1);
    if(count($resprn1)==0)
    {
        $data[$no]['namapenjual']='';
        $data[$no]['namapembeli']='';
        $data[$no]['luastanah']='';
        $data[$no]['jenisakta']='';
        $data[$no]['noakta']='';
        $data[$no]['tanggalakta']='';
        $data[$no]['nilaiprolehan']='';
        $data[$no]['namapembuat']='';
        $data[$no]['masaberlaku']='';
        $data[$no]['keterangan']='';
    }

    foreach ($resprn1 as $key1 => $val1) {
        if($data[$key1]['id']=='')
        {
            $data[$key1]['id']='';
            $data[$key1]['jenissertipikat']='';
            $data[$key1]['nohak']='';
            $data[$key1]['lokasi']='';
            $no=$key1;
        }
        $data[$key1]['namapenjual']=$val1['nama'];
        $data[$key1]['namapembeli']=$val1['namapembeli'];
        $data[$key1]['luastanah']=$val1['luastanah'];
        $data[$key1]['jenisakta']=$val1['jenis'];
        $data[$key1]['noakta']=$val1['nomor'];
        $data[$key1]['tanggalakta']=$val1['tanggal'];
        $data[$key1]['nilaiprolehan']=$val1['nilai'];
        $data[$key1]['namapembuat']=$val1['namapembuat'];
        $data[$key1]['masaberlaku']=$val1['masaberlaku'];
        $data[$key1]['keterangan']=$val1['keterangan'];

        if($data[$key1]['nospptpbb']=='')
        {
            $data[$key1]['nospptpbb']='';
            $data[$key1]['namawp']='';
            $data[$key1]['letakobjekpajak']='';
            $data[$key1]['nilaitanah']='';
            $data[$key1]['nilaibangunan']='';
            $data[$key1]['nilainjoptanah']='';
            $data[$key1]['nilainjopbangunan']='';
            $data[$key1]['nilainjopbangunan']='';
        }
    }

    $strprn2="select * from ".$dbname.".lgl_sertipikat_pajak where idpajak='".$val['id']."'";
    $resprn2 = fetchData($strprn2);
    if(count($resprn2)==0)
    {
        $data[$no]['nospptpbb']='';
        $data[$no]['namawp']='';
        $data[$no]['letakobjekpajak']='';
        $data[$no]['nilaitanah']='';
        $data[$no]['nilaibangunan']='';
        $data[$no]['nilainjoptanah']='';
        $data[$no]['nilainjopbangunan']='';
        $data[$no]['nilainjopbangunan']='';
    }

    foreach ($resprn2 as $key2 => $val2) {
        if($data[$key2]['id']=='')
        {
            $data[$key2]['id']='';
            $data[$key2]['jenissertipikat']='';
            $data[$key2]['nohak']='';
            $data[$key2]['lokasi']='';
            if($no<=$key2)
            {
                $no=$key2;
            }
            
        }
        if($data[$key2]['jenisakta']=='')
        {
            $data[$key2]['namapenjual']='';
            $data[$key2]['namapembeli']='';
            $data[$key2]['luastanah']='';
            $data[$key2]['jenisakta']='';
            $data[$key2]['noakta']='';
            $data[$key2]['tanggalakta']='';
            $data[$key2]['nilaiprolehan']='';
            $data[$key2]['namapembuat']='';
            $data[$key2]['masaberlaku']='';
            $data[$key2]['keterangan']='';
        }
        $data[$key2]['nospptpbb']=$val2['nospptpbb'];
        $data[$key2]['namawp']=$val2['namawp'];
        $data[$key2]['letakobjekpajak']=$val2['letakobjekpajak'];
        $data[$key2]['nilaitanah']=$val2['nilaitanah'];
        $data[$key2]['nilaibangunan']=$val2['nilaibangunan'];
        $data[$key2]['nilainjoptanah']=$val2['nilainjoptanah'];
        $data[$key2]['nilainjopbangunan']=$val2['nilainjopbangunan'];
        $data[$key2]['nilainjopbangunan']=$val2['nilainjopbangunan'];
    }
    $no++;
}

$brdr = 0;
$bgcoloraja = '';

if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE";
    $brdr = 1;
    $tab = "
    <table>
    <tr><td colspan=17 align=left><b>Rekap Sertifikat</b></td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
    <tr><td colspan=17 align=left>" . $_SESSION['lang']['periode'] . " : " . $periode . "</td></tr>
    </table>";
}
$bgcoloraja = "bgcolor=#DEDEDE";
$bgcolorajax = "bgcolor=#5a63ec";
$tab = "<table cellspacing=1 border=" . $brdr . " class=sortable >
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " align=center rowspan=2>NO</td>
        <td " . $bgcoloraja . " align=center rowspan=2>JENIS STATUS HAK</td>
        <td " . $bgcoloraja . " align=center rowspan=2>NO. HAK</td>
        <td " . $bgcoloraja . " align=center rowspan=2>LETAK LOKASI</td>
        <td " . $bgcoloraja . " align=center colspan=2>NAMA HAK ATAS TANAH</td>
        <td " . $bgcoloraja . " align=center rowspan=2>LUAS TANAH</td>
        <td " . $bgcoloraja . " align=center rowspan=2>JENIS</td>
        <td " . $bgcoloraja . " align=center colspan=2>BUKTI LAHAN</td>
        <td " . $bgcoloraja . " align=center rowspan=2>NILAI PEROLEHAN</td>
        <td " . $bgcoloraja . " align=center rowspan=2>PEMBUATAN HAK</td>
        <td " . $bgcoloraja . " align=center rowspan=2>MASA BERLAKU</td>
        <td " . $bgcoloraja . " align=center rowspan=2>KETERANGAN</td>
        <td " . $bgcoloraja . " align=center rowspan=2>SPPT PBB</td>
        <td " . $bgcoloraja . " align=center rowspan=2>NAMA WP</td>
        <td " . $bgcoloraja . " align=center rowspan=2>LETAK OBJEK PAJAK</td>
        <td " . $bgcoloraja . " align=center colspan=2>LUASAN</td>
        <td " . $bgcoloraja . " align=center colspan=2>NJOP</td>
                ";
$tab.="</tr><tr>
        
        <td " . $bgcoloraja . " align=center>PIHAK PERTAMA</td>
        <td " . $bgcoloraja . " align=center>PIHAK KEDUA</td>
        <td " . $bgcoloraja . " align=center>NO</td>
        <td " . $bgcoloraja . " align=center>TANGGAL</td>
        <td " . $bgcoloraja . " align=center>TANAH</td>
        <td " . $bgcoloraja . " align=center>BANGUNAN</td>
        <td " . $bgcoloraja . " align=center>TANAH</td>
        <td " . $bgcoloraja . " align=center>BANGUNAN</td>


        </tr>";
$tab.="</thead>
	<tbody>";

foreach ($data as $row => $col) {
    $tab.= "<tr class='rowcontent'>";
    foreach ($col as $key => $value) {
        $tab.= "<td>".$value."</td>";
    }
    $tab.= "</tr>";
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
        $nop_ = "rekapsertifikat_" . $purId . "_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";

        break;

    
    default:
        break;
}
?>
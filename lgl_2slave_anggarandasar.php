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
$tahundari = empty($_POST['tahundari']) ? (isset($_GET['tahundari']) ? $_GET['tahundari'] : '') : $_POST['tahundari'];
$tahunsampai = empty($_POST['tahunsampai']) ? (isset($_GET['tahunsampai']) ? $_GET['tahunsampai'] : '') : $_POST['tahunsampai'];
$filter = empty($_POST['filter']) ? (isset($_GET['filter']) ? $_GET['filter'] : '') : $_POST['filter'];


if($proses!='gettahunakhir'){

    $unitId = $_SESSION['lang']['all'];

    if ($tahundari == '' || $tahunsampai == '') {
        exit("Error: " . $_SESSION['lang']['tahun'] . " tidak boleh kosong");
    }

    if ($kdUnit != '') {
        $where.=" and a.kodept='" . $kdUnit . "'";
        $unitId = isset($optNmOrg[$kdUnit]) ? $optNmOrg[$kdUnit] : '';
    }

    $strprn="select a.*, b.namaorganisasi, c.npwp from ".$dbname.".lgl_anggarandasarht a 
             left join ".$dbname.".organisasi b on a.kodept=b.kodeorganisasi 
             left join ".$dbname.".setup_org_npwp c on a.kodept=c.kodeorg where  substr(c.npwp, 1,2)=02 ".$where." group by a.kodept order by a.kodept asc";
    $resprn = fetchData($strprn);



    $no=0;
    $data=array();
    foreach ($resprn as $key => $val) {
        $data[$no]['no']=($key+1);
        $data[$no]['namaperusahaan']=$val['namaorganisasi']."</br></br>".$val['npwp'];
        $data[$no]['kodeperusahaan']=$val['kodept'];
        $data[$no]['jenisperusahaan']=$val['jenispt'];
        $data[$no]['noakta']='';
        $data[$no]['tanggalpendirianakta']='';
        $data[$no]['namanotaris']='';
        $data[$no]['noskkehakiman']='';
        $data[$no]['tanggalsk']='';
        $data[$no]['kedudukan']='';
        $data[$no]['alamat']='';
        $data[$no]['noaktabaru']='';
        $data[$no]['tanggalbaruakta']='';
        $data[$no]['namanotarisbaru']='';
        $data[$no]['noskkehakimanbaru']='';
        $data[$no]['tanggalskbaru']='';
        for($i=intval($tahundari);$i<=intval($tahunsampai);$i++){
                $data[$no]['namapemegang'.$i]='';
                $data[$no]['modaldasar'.$i]='';
                $data[$no]['modalsetor'.$i]='';
                $data[$no]['saham'.$i]='';
                $data[$no]['nilaisaham'.$i]='';
                $data[$no]['namakom'.$i]='';
                $data[$no]['jabatankom'.$i]='';
                $data[$no]['ketkom'.$i]='';
        }
        
        $strprn2="select a.*, b.namasupplier from ".$dbname.".lgl_anggarandasardt_akta a 
        left join ".$dbname.".log_5supplier b on a.namanotaris=b.supplierid 
         where kodept='".$val['kodept']."' and jenisakta='pendirian' order by tanggalakta, kodept asc";
        $resprn2 = fetchData($strprn2);
        $strprn3x="select * from ".$dbname.".lgl_anggarandasardt_akta where kodept='".$val['kodept']."' and jenisakta='perubahan' order by kodept,tanggalakta asc";
            $resprn3x = fetchData($strprn3x);
            $jlh3x = count($resprn3x);
        foreach ($resprn2 as $key2 => $val2) {
            $key2+=$no;
            if($data[$key2]['no']=='')
            {
                $data[$key2]['no']='';
                $data[$key2]['namaperusahaan']='';
                $data[$key2]['kodeperusahaan']='';
                $data[$key2]['jenisperusahaan']='';
            }
            $data[$key2]['noakta']=$val2['noakta'];
            $data[$key2]['tanggalpendirianakta']=$val2['tanggalakta'];
            $data[$key2]['namanotaris']=$val2['namasupplier'];
            $data[$key2]['noskkehakiman']=$val2['noskkehakiman'];
            $data[$key2]['tanggalsk']=$val2['tanggalsk'];
            $data[$key2]['kedudukan']=$val2['kedudukan'];
            $data[$key2]['alamat']=$val2['alamat'];
            $data[$key2]['noaktabaru']='';
            $data[$key2]['tanggalbaruakta']='';
            $data[$key2]['namanotarisbaru']='';
            $data[$key2]['noskkehakimanbaru']='';
            $data[$key2]['tanggalskbaru']='';
            
            
            if($no<$key2 && $jlh3x ==0){
            $no=$key2;
            }
        }
        $tre=$no;
        $strprn3="select a.*, b.namasupplier from ".$dbname.".lgl_anggarandasardt_akta a 
        left join ".$dbname.".log_5supplier b on a.namanotaris=b.supplierid 
         where kodept='".$val['kodept']."' and jenisakta='perubahan' order by kodept,tanggalakta asc";
        $resprn3 = fetchData($strprn3);
        foreach ($resprn3 as $key3 => $val3) {
            $key3+=$no;
            if($data[$key3]['noakta']=='')
            {
                $data[$key3]['noakta']='';
                $data[$key3]['tanggalpendirianakta']='';
                $data[$key3]['namanotaris']='';
                $data[$key3]['noskkehakiman']='';
                $data[$key3]['tanggalsk']='';
                $data[$key3]['kedudukan']='';
                $data[$key3]['alamat']='';
            }
            $data[$key3]['noaktabaru']=$val3['noakta'];
            $data[$key3]['tanggalbaruakta']=$val3['tanggalakta'];
            $data[$key3]['namanotarisbaru']=$val3['namasupplier'];
            $data[$key3]['noskkehakimanbaru']=$val3['noskkehakiman'];
            $data[$key3]['tanggalskbaru']=$val3['tanggalsk'];
            
            

            if($no<$key3){
            $no=$key3;
            }
        }
        for($i=intval($tahundari);$i<=intval($tahunsampai);$i++){

            $strprn4="select a.*, b.modaldasar, b.modalsetor from ".$dbname.".lgl_anggarandasardt_saham a  
                        left join ".$dbname.".lgl_anggarandasardt_akta b on a.noakta=b.noakta where a.kodept='".$val['kodept']."' and a.tahun='".$i."' ";
            $resprn4 = fetchData($strprn4);
            $strprn4x="select * from ".$dbname.".lgl_anggarandasardt_komisaris where kodept='".$val['kodept']."' and tahun='".$i."'";
            $resprn4x = fetchData($strprn4x);
            $jlh4x=count($resprn4x);
            foreach ($resprn4 as $key4 => $val4) {
            $key4+=$tre;
            if($data[$key4]['no']=='')
            {
                $data[$key4]['no']='';
                $data[$key4]['namaperusahaan']='';
                $data[$key4]['kodeperusahaan']='';
                $data[$key4]['jenisperusahaan']='';
            }
            if($data[$key4]['noakta']=='')
            {
                $data[$key4]['noakta']='';
                $data[$key4]['tanggalpendirianakta']='';
                $data[$key4]['namanotaris']='';
                $data[$key4]['noskkehakiman']='';
                $data[$key4]['tanggalsk']='';
                $data[$key4]['kedudukan']='';
                $data[$key4]['alamat']='';
            }
            if($data[$key4]['noaktabaru']=='')
            {
                $data[$key4]['noaktabaru']='';
                $data[$key4]['tanggalbaruakta']='';
                $data[$key4]['namanotarisbaru']='';
                $data[$key4]['noskkehakimanbaru']='';
                $data[$key4]['tanggalskbaru']='';
            }
                $data[$key4]['namapemegang'.$i]=$val4['nama'];
                $data[$key4]['modaldasar'.$i]=$val4['modaldasar'];
                $data[$key4]['modalsetor'.$i]=$val4['modalsetor'];
                $data[$key4]['saham'.$i]=$val4['saham'];
                $data[$key4]['nilaisaham'.$i]=$val4['nilaisaham'];
                if($no<$key4 && $jlh4x==0){
                $no=$key4;
                }
            }

            $strprn4="select * from ".$dbname.".lgl_anggarandasardt_komisaris where kodept='".$val['kodept']."' and tahun='".$i."'";
            $resprn4 = fetchData($strprn4);
            foreach ($resprn4 as $key4 => $val4) {
            $key4+=$tre;
            if($data[$key4]['no']=='')
            {
                $data[$key4]['no']='';
                $data[$key4]['namaperusahaan']='';
                $data[$key4]['kodeperusahaan']='';
                $data[$key4]['jenisperusahaan']='';
            }
            if($data[$key4]['noakta']=='')
            {
                $data[$key4]['noakta']='';
                $data[$key4]['tanggalpendirianakta']='';
                $data[$key4]['namanotaris']='';
                $data[$key4]['noskkehakiman']='';
                $data[$key4]['tanggalsk']='';
                $data[$key4]['kedudukan']='';
                $data[$key4]['alamat']='';
            }
            if($data[$key4]['noaktabaru']=='')
            {
                $data[$key4]['noaktabaru']='';
                $data[$key4]['tanggalbaruakta']='';
                $data[$key4]['namanotarisbaru']='';
                $data[$key4]['noskkehakimanbaru']='';
                $data[$key4]['tanggalskbaru']='';
            }

            for($x=intval($tahundari);$x<=$i;$x++){
                if($data[$key4]['namapemegang'.$x]=='')
                {
                    $data[$key4]['namapemegang'.$x]='';
                    $data[$key4]['modaldasar'.$x]='';
                    $data[$key4]['modalsetor'.$x]='';
                    $data[$key4]['saham'.$x]='';
                    $data[$key4]['nilaisaham'.$x]='';
                }
                if($data[$key4]['namakom'.$x]=='')
                {
                    $data[$key4]['namakom'.$x]='';
                    $data[$key4]['jabatankom'.$x]='';
                    $data[$key4]['ketkom'.$x]='';
                }
            }
                $data[$key4]['namakom'.$i]=$val4['nama'];
                $data[$key4]['jabatankom'.$i]=$val4['jabatan'];
                $data[$key4]['ketkom'.$i]=$val4['keterangan'];
                if($no<$key4){
                $no=$key4;
                }
            }
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
        <tr><td colspan=17 align=left><b>Rekap Pengajuan Pembayaran</b></td></tr>
        <tr><td colspan=17 align=left>" . $_SESSION['lang']['pt'] . " : " . $unitId . "</td></tr>
        </table>";
    }
    $bgcoloraja = "bgcolor=#DEDEDE";
    $bgcolorajax = "bgcolor=#5a63ec";
    $tab = "<table cellspacing=1 border=" . $brdr . " class=sortable >
    	<thead class=rowheader>
    	<tr>
            <td " . $bgcoloraja . " align=center rowspan=3>NO</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NAMA PERUSAHAAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>KODE PERUSAHAAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>JENIS PERUSAHAAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NO AKTA</td>
            <td " . $bgcoloraja . " align=center rowspan=3>TANGGAL AKTA PENDIRIAN PERSEROAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NAMA NOTARIS</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NO SK KEHAKIMAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>TANGGAL SK KEHAKIMAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>KEDUDUKAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>ALAMAT PERUSAHAAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NO AKTA PERUBAHAN TERBARU</td>
            <td " . $bgcoloraja . " align=center rowspan=3>TANGGAL AKTA PERUBAHAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NAMA NOTARIS</td>
            <td " . $bgcoloraja . " align=center rowspan=3>NO SK KEHAKIMAN</td>
            <td " . $bgcoloraja . " align=center rowspan=3>TANGGAL SK KEHAKIMAN</td>
                    ";
    for($i=intval($tahundari);$i<=intval($tahunsampai);$i++){
        $tab.= "
            <td " . $bgcoloraja . " align=center colspan=8>TAHUN ".$i."</td>
                    ";
    }
    for($i=intval($tahundari);$i<=intval($tahunsampai);$i++){
    if($i==$tahundari){ $tab.="</tr><tr>";}
    $tab.="<td " . $bgcoloraja . " align=center rowspan=2>NAMA PEMEGANG</td>
            <td " . $bgcoloraja . " align=center rowspan=2>Σ MODAL DASAR</td>
            <td " . $bgcoloraja . " align=center rowspan=2>Σ MODAL SETOR</td>
            <td " . $bgcoloraja . " align=center rowspan=2>Σ SAHAM</td>
            <td " . $bgcoloraja . " align=center rowspan=2>NILAI/SHS</td>
            <td " . $bgcoloraja . " align=center colspan=2>SUSUNAN PENGURUS KOMISARI</td>
            <td " . $bgcoloraja . " align=center rowspan=2>KETERANGAN</td>
            ";
    }
    for($i=intval($tahundari);$i<=intval($tahunsampai);$i++){
    if($i==$tahundari){ $tab.="</tr><tr>";}
    $tab.="<td " . $bgcoloraja . " align=center>NAMA</td>
            <td " . $bgcoloraja . " align=center>JABATAN</td>
            ";
    }
    $tab.="</tr></thead>
    	<tbody>";

    foreach ($data as $row => $col) {
        $tab.= "<tr class='rowcontent'>";
        foreach ($col as $key => $value) {
            $tab.= "<td>".$value."</td>";
        }
        $tab.= "</tr>";
    }

    $tab.="</tbody></table>";
}
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
    case'gettahunakhir':
        $optPeriodeCari = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sPeriodeCari = "select distinct tahun from " . $dbname . ".lgl_anggarandasardt_saham where tahun!='' and tahun>='".$tahundari."' order by tahun desc";
        $qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
        $qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPeriodeCari = $qPeriodeCari->fetch()) {
            $optPeriodeCari.="<option value='" . $rPeriodeCari['tahun'] . "'>" . $rPeriodeCari['tahun'] . "</option>";
        }
        echo $optPeriodeCari;
        break;
    case'preview':
        /*print_r($data['zonk1']);
        echo "</br><hr></br>";
        print_r($data['zonk2']);*/
        //print_r($data);
        echo $tab;
        break;

    case'excel':

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHms");
        $nop_ = "rekappengajuanpembayaran_" . $purId . "_" . $dte;
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
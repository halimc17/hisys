<?php

require_once('master_validation.php');
require_once('lib/zLib.php');
$prddari = checkPostGet('prddari', '');
$prdsampai = checkPostGet('prdsampai', '');
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$kdvhc = checkPostGet('kdvhc', '');
$kodevhc = array();

if ($kdorg == '') {
    echo"Warning: Unit tidak boleh kosong";
    exit;
}
if ($kdvhc == '') {
    echo"Warning: Kode Kendaraan tidak boleh kosong";
    exit;
}

if (($prddari == '')or ( $prdsampai == '')) {
    echo"Warning: Tanggal tidak boleh kosong";
    exit;
} else if ($prddari > $prdsampai) {
    echo"Warning: Periode pertama tidak boleh lebih besar dari periode kedua.";
    exit;
}
$rangeprd = month_inbetween($prddari, $prdsampai);

if (count($rangeprd) > 12) {
    echo"Warning: Periode maksimal 12 bulan.";
    exit;
}

######################################
############# prepare data ###########
######################################
$jnsvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');

$str = "select * from " . $dbname . ".vhc_5master where "
        . " kodevhc='".$kdvhc."'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kodevhc[$bar['kodevhc']] = $bar['kodevhc'];
	$kodejns = $bar['jenisvhc'];
}


$str = "select * from " . $dbname . ".vhc_5operator where "
        . "vhc='".$kdvhc."' and jabatan in ('0','2') and aktif='1'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $operator = $bar['nama'];
    
}

$str = "select sum(jumlah) as jumlah,sum(hargatotal) as rupiah, kodebarang, kodevhc, substr(tanggal,1,7) as prd,namabarang from " . $dbname . ".log_zbahan_kendaraan_vw where "
        . " kodevhc='" . $kdvhc . "' and substr(tanggal,1,7) between '" . $prddari . "' and '" . $prdsampai . "' "
        . " group by kodevhc,kodebarang, prd order by namabarang";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
while ($bar = $res->fetch()) {
    $kodebarang[$bar['kodebarang']] = $bar['kodebarang'];
    $namabarang[$bar['kodebarang']] = $bar['namabarang'];
    $listbarang[$bar['kodevhc']][$bar['kodebarang']] = $bar['kodebarang'];
	$jumlah[$bar['kodevhc']][$bar['prd']][$bar['kodebarang']] = $bar['jumlah'];
	$rupiah[$bar['kodevhc']][$bar['prd']][$bar['kodebarang']] = $bar['rupiah'];
	$listprd[$bar['kodevhc']][$bar['kodebarang']] = $bar['prd'];
}

// echo "<pre>";
// print_r($numrows);
// echo "</pre>";
// exit('error');

$span = count($rangeprd);

$nmken = makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$kdvhc."'");
$stream.="<table cellpadding=5>
		<tr>
			<td>".$_SESSION['lang']['sopir']." / ".$_SESSION['lang']['operator']."</td><td>:</td><td><b>".$operator."</b></td>
		</tr>	
		<tr>	
			<td>".$_SESSION['lang']['namajenisvhc']."</td><td>:</td><td><b>".$jnsvhc[$kodejns]."</b></td>
		</tr>	
		<tr>	
			<td>".$_SESSION['lang']['kodevhc']."</td><td>:</td><td><b>".$kdvhc." - ".$nmken[$kdvhc]."</b></td>
		</tr>	
		<tr>	
			<td>".$_SESSION['lang']['periode']."</td><td>:</td><td><b>".$prddari." s/d ".$prdsampai."</b></td>
		</tr>	
		</table>	
		";
		
if ($proses == 'excel') {
    $stream.="<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.="<table class=sortable cellspacing=1 cellpadding=5>";
}
$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center colspan=" . $span . ">" . $_SESSION['lang']['bulan'] . "</th> 
			<th align=center  rowspan='2'>" . $_SESSION['lang']['jumlah'] . "</th>
			<th align=center  rowspan='2'>" . $_SESSION['lang']['harga'] . "</th>
			<th align=center  rowspan='2'>" . $_SESSION['lang']['total'] . "</th>
        </tr>";
$stream.="<tr>";
foreach ($rangeprd as $listprd => $prd) {
        $stream.="<th align=center>".$prd."</th>";
}
$stream.="</tr>
    </thead>
 <tbody>";


    // array_multisort($kddivisi, SORT_ASC);
    // array_multisort($sksi, SORT_ASC);
    // array_multisort($tahuntanam, SORT_ASC);
    // array_multisort($kdblok, SORT_ASC);
if($numrows<=0){
	exit('warning : Data kosong.');
}
foreach ($kodevhc as $vhc) {
	foreach ($kodebarang as $barang) {
		if($listbarang[$vhc][$barang]!=''){
			$no+=1;
			$stream.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$barang."</td>
					<td align=left>".$namabarang[$barang]."</td>";
			foreach ($rangeprd as $listprd => $prd) {
				if(@$jumlah[$vhc][$prd][$barang]>0){
					$stream.="<td align=right style=cursor:pointer onclick=getdetailkmhm('".$vhc."','".$prd."','".$barang."')><font color=blue>".@number_format($jumlah[$vhc][$prd][$barang],2)."</font></td>";		
				}else{
					$stream.="<td align=right></td>";
				}
					@$jlhbarang[$barang]+=$jumlah[$vhc][$prd][$barang];
					@$jlhrp[$barang]+=$rupiah[$vhc][$prd][$barang];
					
			}
			$stream.="<td align=right>".@number_format($jlhbarang[$barang],2)."</td>";
			$stream.="<td align=right>".@number_format($jlhrp[$barang]/$jlhbarang[$barang],0)."</td>";
			$stream.="<td align=right>".@number_format($jlhrp[$barang])."</td>";
			@$gtbarang+=$jlhrp[$barang];
			$stream.="</tr>";
		}
	}
}

$stream.="
        <tr bgcolor=#00B366>
            <td align=left colspan=" . ($span +5). "><b>" . $_SESSION['lang']['grnd_total'] . " " . $kdvhc . "</b></td>
            <td align=right><b>" . number_format($gtbarang) . "</b></td>";

$stream.="</tr><thead>";
$stream.="</tbody>
		  </table><br><br>";

if ($proses == 'excel') {
    $stream.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class=sortable cellspacing=1 cellpadding=5>";
}
        
$stream.="
    <thead>
        <tr class=rowheader>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['nopo'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['nopp'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center  rowspan='2'>KM/HM</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['jumlah'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['harga'] . "</th>
            <th align=center  rowspan='2'>" . $_SESSION['lang']['total'] . "</th>
        </tr>
    </thead>
    <tbody>";

$gtsrvhc=0;
$no=0;
$str = "select a.nopp,b.nopo as nopo,b.tanggal as tanggalpo,kodevhc,b.kodebarang as kodebarang,b.namabarang, 
        b.kodesupplier, b.kodesupplier,b.namasupplier,b.nilaipo, b.jumlahpesan as jumlah, b.hargasatuan, a.kmhm   
        from ".$dbname.".log_prapo_vw a left join ".$dbname.".log_po_vw b on a.nopp=b.nopp and a.kodebarang=b.kodebarang 
        where create_po=1 and statuspo=2 and tipepp='SR' and kodevhc='".$kdvhc."' and substr(b.tanggal,1,7) between 
        '".$prddari."' and '".$prdsampai."' order by b.tanggal";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=owlBaris($res);
while ($bar = $res->fetch()) {
    $no++;
    $stream.="<tr class=rowcontent>
                <td align=center>".$no."</td>
                <td align=center>".$bar['tanggalpo']."</td>
                <td align=center>".$bar['nopo']."</td>
                <td align=center>".$bar['nopp']."</td>
                <td align=center>".$bar['kodebarang']."</td>
                <td align=left>".$bar['namabarang']."</td>
                <td align=center>".$bar['kmhm']."</td>
                <td align=center>".$bar['jumlah']."</td>
                <td align=right>".number_format($bar['hargasatuan'])."</td>
                <td align=right>".number_format($bar['nilaipo'])."</td>
              </tr>";
            $gtsrvhc+=$bar['nilaipo'];
}

$stream.="
        <tr bgcolor=#00B366>
            <td align=left colspan='9'><b>" . $_SESSION['lang']['grnd_total'] . " " . $kdvhc . "</b></td>
            <td align=right><b>" . number_format($gtsrvhc) . "</b></td>";

$stream.="</tr><thead>";
$stream.="</tbody>
          </table>";

switch ($proses) {
	case 'getdetailkmhm':
	break;
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "daftar_pemakaian_sparepart_" . $kdvhc;
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
}
?>
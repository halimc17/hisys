<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$theme = $_SESSION['theme'];
if ($theme == 'skyblue' || $theme == '') {
    $gen = 'generic.css';
} else if ($theme == 'red') {
    $gen = 'genericRed.css';
} else {
    $gen = 'genericGray.css';
}
$tgl = checkPostGet('tgl', '');
$blok = checkPostGet('blok', '');
$tipe = checkPostGet('tipe', '');
$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');

if ($proses == 'changediv') {
    $optDiv = "";
    $str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='AFDELING' and induk='" . $unit . "' and kodeorganisasi IN (" . getOrgDetail(26) . ") 
    order by namaorganisasi asc ";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $optDiv .= "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
    while ($bar = $res->fetch()) {
        $optDiv .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
    }

    echo $optDiv;
}

echo "
<link rel=stylesheet type=text/css href=style/" . $gen . ">	
";

if ($tipe == 'excel') {
    $border = "border=1";
} else {
    $border = "border=0";
}


echo " Print Excel : <img style=cursor:pointer; "
    . " onclick=\"parent.lihatdetail('" . $blok . "','" . $tgl . "','excel',event)\" src=images/excel.jpg  
    title='MS.Excel'>
   ";

$stream = "<table " . $border . " class=sortable cellpadding=5 cellspacing=1 style=width:100%>
             <thead>
                    <tr>
                          <td align=center>" . $_SESSION['lang']['nospb'] . "</td>    
                          <td align=center>No. Tiket Timbangan</td>
                          <td align=center>" . $_SESSION['lang']['supir'] . "</td>
                          <td align=center>" . $_SESSION['lang']['nokendaraan'] . "</td>
                          <td align=center>" . $_SESSION['lang']['blok'] . "</td> 
                          <td align=center>" . $_SESSION['lang']['jjg'] . "</td> 
                          <td align=center>" . $_SESSION['lang']['kg'] . "</td>  
                          <td align=center>" . $_SESSION['lang']['pabrik'] . " " . $_SESSION['lang']['tujuan'] . "</td> 
                        </tr>  
                 </thead>
                 <tbody id=container>";
//=================================================

$no = 0;

$str = "select * from " . $dbname . ".kebun_spb_vw where blok='" . $blok . "' and tanggal='" . $tgl . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $no += 1;
    $dataPabrik = fetchData(selectQuery($dbname, "pabrik_timbangan", "*", "nospb != '' AND nospb = '{$bar['nospb']}'"))[0];
    $notiket = $dataPabrik['notransaksi'] ?? '';
    $supir = $dataPabrik['supir'] ?? '';
    $nokendaraan = $dataPabrik['nokendaraan'] ?? '';

    $stream .= "<tr class=rowcontent>
              
              <td align=left>" . $bar['nospb'] . "</td>   
               <td align=left>" . $notiket . "</td>   
               <td align=left>" . $supir . "</td>   
                   <td align=left>" . $nokendaraan . "</td>   
                       <td align=left>" . $bar['blok'] . "</td>   
                           <td align=left>" . number_format($bar['jjg']) . "</td>   
                               <td align=right>" . number_format($bar['kgwb'], 2) . "</td>   
                                   <td align=left>" . $bar['penerimatbs'] . "</td>   
                                       
                                       
                    
             </tr>";
}


if ($tipe == 'excel') {
    //echo $stream;
    $stream .= "Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
    $nop_ = "detail_transaksi" . $kodeorg . _ . $noakun . _ . $per;
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
} else {
    echo $stream;
}

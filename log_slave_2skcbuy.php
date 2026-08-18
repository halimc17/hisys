<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$buy = checkPostGet('buy','');
$brg = checkPostGet('brg','');
$buysch='';
$brgsch='';
if (($proses == 'excel')or ( $proses == 'pdf')) {

    $buy = $_GET['buy'];
    $brg = $_GET['brg'];
}


if ($buy != '') {
    $buysch = "and kodecustomer='" . $buy . "'";
}


if ($brg != '') {
    $brgsch = "and kodecustomer in (select kodecustomer from " . $dbname . ".pmn_4komoditi where kodebarang='" . $brg . "')";
}


if ($proses == 'excel') {
    $stream = "<table cellspacing='1' border='1' class='sortable' bgcolor=#CCCCCC>";
} else {
    $stream.="<table cellspacing='1' cellpadding=5 border='0' class='sortable'>";
}
$stream.="<thead class=rowheader>
                <tr>
                    <th align=center>No</th>
                    <th align=center>" . $_SESSION['lang']['komoditi'] . "</th>
                    <th align=center>" . $_SESSION['lang']['kodecustomer'] . "</th>
                    <th align=center>" . $_SESSION['lang']['nmcust'] . "</th>
                    <th align=center>" . $_SESSION['lang']['alamat'] . "</th>
                    <th align=center>" . $_SESSION['lang']['kota'] . "</th>
                    <th align=center>" . $_SESSION['lang']['telepon'] . "</th>
                    <th align=center>" . $_SESSION['lang']['npwp'] . "</th>
                    <th align=center>" . $_SESSION['lang']['alamat'] . " " . $_SESSION['lang']['npwp'] . "</th>
                    <th align=center>" . $_SESSION['lang']['penandatangan'] . "</th>
                    <th align=center>" . $_SESSION['lang']['jabatan'] . "</th>    
                    <th align=center>" . $_SESSION['lang']['kntprson'] . " (" . $_SESSION['lang']['email'] . ")</th>    
                    <th align=center>" . $_SESSION['lang']['eksternal'] . "/" . $_SESSION['lang']['internal'] . "</th>    
                    <th align=center>" . $_SESSION['lang']['plafon'] . "</th>    
                    <th align=center>" . $_SESSION['lang']['nilaihutang'] . "</th>    
                    <th align=center>" . $_SESSION['lang']['toleransipenyusutan'] . "</th>        
                    <th align=center>" . $_SESSION['lang']['berikat'] . "</th>        
                    <th align=center>" . $_SESSION['lang']['statusberikat'] . "</th>               
                </tr>
            </thead>
  <tbody>";





$srt = "select * from " . $dbname . ".pmn_4customer where 1=1  " . $buysch . " " . $brgsch . " order by namacustomer asc ";
$rep=$owlPDO->query($srt) or die(print " Gagal: ".PDOException::getMessage());
$rep->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $rep->fetch()) {
	//get kelompok cust
	$sql = "select * from " . $dbname . ".pmn_4klcustomer where `kode`='" . $bar->klcustomer . "'";
	$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
	$query->setFetchMode(PDO::FETCH_OBJ);
	$res = $query->fetch();

	//get Komoditi
	$sKo = "select t1.*,t2.namabarang from " . $dbname . ".pmn_4komoditi t1
						left join " . $dbname . ".log_5masterbarang t2
						on t1.kodebarang = t2.kodebarang
						where `kodecustomer`='" . $bar->kodecustomer . "'";
	$qKo=$owlPDO->query($sKo) or die(print " Gagal: ".PDOException::getMessage());
	$qKo->setFetchMode(PDO::FETCH_OBJ);
	$hasilKomoditi = "";
	$hasilKomoditi2 = "";
	while ($rKo = $qKo->fetch()) {
		$hasilKomoditi.="," . $rKo->kodebarang;
		$hasilKomoditi2.=",<br>" . $rKo->namabarang;
	}

	//get Kontak Person
	$sPer = "select * from " . $dbname . ".pmn_4customercontact
						where `kodecustomer`='" . $bar->kodecustomer . "'";
	$qPer=$owlPDO->query($sPer) or die(print " Gagal: ".PDOException::getMessage());
	$qPer->setFetchMode(PDO::FETCH_OBJ);
	$hasilPerson = "";
	while ($rPer = $qPer->fetch()) {
		$hasilPerson.=",<br>" . $rPer->nama . " (" . $rPer->email . ")";
	}

	//get akun
	$spr = "select * from  " . $dbname . ".keu_5akun where `noakun`='" . $bar->akun . "'";
	$rej=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
	$rej->setFetchMode(PDO::FETCH_OBJ);
	$bas = $rej->fetch();
	$no++;
	$bar->alamat = clearInvalidChar($bar->alamat);
	$bar->telepon = clearInvalidChar($bar->telepon);
	$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
	$stream.="<tr class=rowcontent>
			  <td style='vertical-align:top;'>" . $no . "</td>
			  <td style='vertical-align:top;'>" . substr($hasilKomoditi2, 5) . "</td>
			  <td style='vertical-align:top;'>" . $bar->kodecustomer . "</td>
			  <td style='vertical-align:top;'>" . $bar->namacustomer . "</td>
			  <td style='vertical-align:top;'>" . $bar->alamat . "</td>
			  <td style='vertical-align:top;'>" . $bar->kota . "</td>
			  <td style='vertical-align:top;'>" . $bar->telepon . "</td>
			  <td style='vertical-align:top;'>" . $bar->npwp . "</td>
			  <td style='vertical-align:top;'>" . $bar->alamatnpwp . "</td>
			  <td style='vertical-align:top;'>" . $bar->penandatangan . "</td>
			  <td style='vertical-align:top;'>" . $bar->jabatan . "</td>
			  <td style='vertical-align:top;'>" . substr($hasilPerson, 5) . "</td>
			  <td style='vertical-align:top;'>" . $bar->statusinteks . "</td>
			  <td style='vertical-align:top; text-align:right;'>" . $bar->plafon . "</td>
			  <td style='vertical-align:top; text-align:right;'>" . $bar->nilaihutang . "</td>
			  <td style='vertical-align:top; text-align:right;'>" . $bar->toleransipenyusutan . "</td>
			  <td style='vertical-align:top; text-align:center;'>" . (($bar->statusberikat == '1') ? 'Y' : '') . "</td>
			  <td style='vertical-align:top;'>" . $bar->keteranganberikat . "</td>
			  </tr>";
}

$stream.="
	</tbody></table>";


#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch ($proses) {
######HTML
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        $stream.="Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
        $tglSkrg = date("Ymd");
        $nop_ = "LAPORAN_DATA_SUPPLIER_" . $tglSkrg;
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
            closedir($handle);
        }
        break;

    default:
        break;
}
?>
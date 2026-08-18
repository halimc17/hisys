<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$divisi = checkPostGet('divisi', '');
$barang = checkPostGet('barang', '');
$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));

$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$nmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$stBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');

$namaBarangCari = checkPostGet('namaBarangCari', '');
$urlefil = checkPostGet('urlefil', '0');

$param = $_POST;

if ($tgl1 == '--') {
    $tgl1 = '';
}
if ($tgl2 == '--') {
    $tgl2 = '';
}


if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream.= "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
}

$stream.="<thead><tr class=rowheader>
            <th align=center>No</th>
            <th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>   
            <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                
            <th align=center>" . $_SESSION['lang']['gudang'] . "</th>
            <th align=center>" . $_SESSION['lang']['alokasi'] . "</th>
            <th align=center>" . $_SESSION['lang']['kendaraan'] . "</th> 
            <th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th align=center>" . $_SESSION['lang']['namabarang'] . "</th>
            <th align=center>" . $_SESSION['lang']['jumlah'] . "</th>  
            
        </tr></thead>
      <tbody>";

$barangsort = "";
if ($barang != '') {
    $barangsort .= "and kodebarang='" . $barang . "'";
}
if ($divisi != '') {
    $barangsort .= "and left(kodeblok,6)='" . $divisi . "'";
}

$str = "SELECT * FROM " . $dbname . ".log_transaksi_vw where left(kodegudang,4)='" . $unit . "' "
        . " and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and tipetransaksi=5 " . $barangsort . " ";
// exit('warning: ' . $str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$totaljumlah = 0;
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;
    $stream.="<tr class=rowcontent>";
   
         $stream.="<td align=center>" . $no . "</td>";
         $stream.="<td align=center >" . $bar['notransaksi'] . "</td>";
         $stream.="<td align=center >" . tanggalnormal($bar['tanggal']) . "</td>";    
        
		if($nmOrg[$bar['kodeblok']]){
			$nmOrg[$bar['kodeblok']]=$nmOrg[$bar['kodeblok']];
		} else {
			$nmOrg[$bar['kodeblok']]=$nmsup[$bar['kodeblok']];
		}
		
         $stream.="<td align=center>" . $bar['kodegudang'] . " - ".$nmOrg[$bar['kodegudang']]."</td>";    
         $stream.="<td align=center>" . $bar['kodeblok']." - ".$nmOrg[$bar['kodeblok']] . "</td>";        
         $stream.="<td align=left>" . $bar['kodemesin'] . " - " . getNopol($bar['kodemesin']) . "</td>";         
         $stream.="<td align=center>" . $bar['kodebarang'] . "</td>";     
         $stream.="<td align=left>" . $nmBrg[$bar['kodebarang']] . "</td>";    
         $stream.="<td align=right>" . number_format($bar['jumlah'], 2) . "</td>"; 
         $stream.="</tr>";
    $totaljumlah+=$bar['jumlah'];
}
$stream.="<tr class=rowcontent style='font-weight:bold'>";
$stream.="<td colspan=8 style='text-align:center'>" . $_SESSION['lang']['total'] . "</td>";
$stream.="<td style='text-align:right'>" . number_format($totaljumlah, 2) . "</td>";

$stream.="</tr>";

$stream.="</tbody></table>";






#######################################################################
############PANGGGGGGGGGGGGGGGGGGILLLLLLLLLLLLLLLLLLLLLLLLLL###########   
#######################################################################

switch ($proses) {

    case'getdivisi':
		$optafd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['unit']."' and length(kodeorganisasi)='6' and tipe not like 'GUDANG%' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optafd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optafd;
	break;


    case'getListBarang':
        echo"<fieldset  style='float:left;' >
                <legend>" . $_SESSION['lang']['find'] . " " . $_SESSION['lang']['namabarang'] . "</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>" . $_SESSION['lang']['namabarang'] . "</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:150px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>
  
                    <table id=listCariBarang class=sortable width=100%>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>" . $_SESSION['lang']['kodebarang'] . "</td>
                            <td>" . $_SESSION['lang']['namabarang'] . "</td>
                            <td>" . $_SESSION['lang']['satuan'] . "</td>
                    </tr></thead>";

        if ($namaBarangCari == '') {
            
        } else {

            $i = "select kodebarang,namabarang,satuan from " . $dbname . ".log_5masterbarang where namabarang like '%" . $namaBarangCari . "%'";
			$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
			$n->setFetchMode(PDO::FETCH_ASSOC);
            while ($d = $n->fetch()) {
				$whBrg = "kodebarang='" . $d['kodebarang'] . "'";
                $no+=1;
                echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('" . $d['kodebarang'] . "','" . $nmBrg[$d['kodebarang']] . "','" . $d['satuan'] . "');\">
					<td>" . $no . "</td>
					<td>" . $d['kodebarang'] . "</td>
					<td>" . $nmBrg[$d['kodebarang']] . "</td>
					<td>" . $d['satuan'] . "</td>
					
				</tr>";
            }
        }
        echo"</table>
        </fieldset>";
        break;



######HTML
    case 'preview':

        if ($tgl1 == '' || $tgl2 == '' || $unit == '') {
            exit("Please Complate the form");
        }

        echo $stream;
        break;

######EXCEL	
    case 'excel':

        if ($tgl1 == '' || $tgl2 == '' || $unit == '') {
            exit("Please Complate the form");
        }

        $stream.="Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
        $tglSkrg = date("Ymd");
        $nop_ = "LAPORAN_PEMAKAIAN_BARANG_" . $tglSkrg;
        if (strlen($stream) > 0) {
			if($urlefil=='0'){
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
			}else{
				if($no > 0){
					$handle=fopen($urlefil, 'w');
					fwrite($handle, $stream);
					fclose($handle);
				}
			}
        }
        break;


    default:
        break;
}
?>
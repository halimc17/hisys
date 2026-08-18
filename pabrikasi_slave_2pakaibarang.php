<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$kdpab = checkPostGet('kdpab', '');

$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmpab=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');


if(($tgl1=='')or($tgl2==''))
{
    echo"Warning: Tanggal tidak boleh kosong"; 
    exit;
}

else if($tgl1>$tgl2)
{
    echo"Warning: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
    exit;
}


$where="";

if($kdorg!=''){
	$where.=" and kodept='".$kdorg."'";
}

if($kdpab!=''){
	$where.=" and kodeblok='".$kdpab."'";
}else{
	$where.=" and kodeblok like 'PB%' ";
}

######################################
############# prepare data ###########
######################################
if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<table class=sortable cellspacing=1>";
}
$stream.="
    <thead>
        <tr class=rowheader>
            <td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center>".$_SESSION['lang']['tanggal']."</td>
			<td align=center>Kode Pabrikasi</td>
			<td align=center>Nama Pabrikasi</td>
			<td align=center>".$_SESSION['lang']['notransaksi']."</td>
			<td align=center>".$_SESSION['lang']['pt']."</td>
			<td align=center>".$_SESSION['lang']['gudang']."</td>
			<td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center>".$_SESSION['lang']['jumlah']."</td>
			
        </tr>   
    </thead>
 <tbody>";



$str="select * from ".$dbname.".log_transaksi_vw where 1=1 and tipetransaksi='0' and tanggal between '".$tgl1."' and '".$tgl2."' ".$where." ";	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$no+=1;
    $stream.="<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td align=left>".tanggalnormal($bar['tanggal'])."</td>
		<td align=left>".$bar['kodeblok']."</td>
		<td align=left>".$nmpab[$bar['kodeblok']]."</td>
		<td align=left>".$bar['notransaksi']."</td>
		<td align=left>".$bar['kodept']."</td>
		<td align=left>".$bar['kodegudang']."</td>
		<td align=left>".$bar['kodebarang']."</td>
		<td align=left>".$nmbrg[$bar['kodebarang']]."</td>
		<td align=left>".$bar['satuan']."</td>
		<td align=right>".$bar['jumlah']."</td>
	</tr>";
}



                  
                       
           
                  
$stream.="
 </tbody>
     </table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        //exit("error:$stream");
        //$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
        $tglSkrg = date("Ymd");
        $nop_ = "Laporan_pemakaian_barang_pabrikasi" . $kdorg;
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
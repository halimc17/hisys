<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$tgl1=tanggalsystem(checkPostGet('tgl1',''));
$tgl2=tanggalsystem(checkPostGet('tgl2',''));
$proses = checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');

$rangetanggal = rangeTanggal($tgl1, $tgl2);

if($kdorg=='')
{
    echo"Warning: Unit tidak boleh kosong"; 
    exit;
}

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
			<td align=center>".$_SESSION['lang']['kodepabrikasi']."</td>
			<td align=center>".$_SESSION['lang']['namapabrikasi']."</td>
			<td align=center>".$_SESSION['lang']['kodesalesorder']."</td>
			<td align=center>".$_SESSION['lang']['tanggalmulai']."</td>
			<td align=center>".$_SESSION['lang']['tanggalselesai']."</td>
			<td align=center>".$_SESSION['lang']['detail']."</td>
        </tr>   
    </thead>
 <tbody>";


#kebun_spb_vw
#kebun_rekappnn_vw

#data pusingan
$str="select * from ".$dbname.".pabrikasi_cutoffht where  tanggalcutoff between '".$tgl1."' and '".$tgl2."' and
		kodepabrikasi in (select kodepabrikasi from ".$dbname.".pabrikasi_5masterht where kodeorg='".$kdorg."')";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kodepabrikasi[$bar['kodepabrikasi']]=$bar['kodepabrikasi'];
    $kdso[$bar['kodepabrikasi']]=$bar['kodeso'];
}

$str="select * from ".$dbname.".pabrikasi_5masterht where kodeorg='".$kdorg."' and 
		kodepabrikasi in (select kodepabrikasi from ".$dbname.".pabrikasi_cutoffht where  tanggalcutoff between '".$tgl1."' and '".$tgl2."')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kodepabrikasi[$bar['kodepabrikasi']]=$bar['kodepabrikasi'];
    $nmpab[$bar['kodepabrikasi']]=$bar['namapabrikasi'];
	$tglmulai[$bar['kodepabrikasi']]=$bar['tanggalmulai'];
	$tglselsai[$bar['kodepabrikasi']]=$bar['tanggalselesai'];
}
if(count($kodepabrikasi)!=0){
    foreach($kodepabrikasi as $kdpab){
        @$no+=1;
        $stream.="<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td align=left>".$kdpab."</td>
        <td align=left>".$nmpab[$kdpab]."</td>
        <td align=left>".$kdso[$kdpab]."</td>
        <td align=left>".tanggalnormal($tglmulai[$kdpab])."</td>
        <td align=left>".tanggalnormal($tglselsai[$kdpab])."</td>
        <td align=left><img src=images/zoom.png title='".$_SESSION['lang']['find']."' id=tmblkdso class=resicon onclick=lihatDetail('".$kdpab."','html',event)></td>
        </tr>";
    }    
}else{
    exit('warning:'.$_SESSION['lang']['dataempty']);
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
        $nop_ = "Laporan Crop Statement" . $kdorg;
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
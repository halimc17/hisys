<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kelbrg=$_GET['kelbrg'];
$gdg=$_GET['gdg'];
$txtfind=$_GET['txtcari'];
$subklbarang=$_GET['subklbarang'];
$stream="";

$str="select * from ".$dbname.".log_5masterbarang where (namabarang like '%".$txtfind."%' or 
        kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' and left(kodebarang,5) like '".$subklbarang."%' 
        order by kodebarang asc";
        
$strin="select min(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(1,3) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' group by kodebarang order by kodebarang asc";

$strout="select max(a.tanggal) as tgl,a.kodebarang from ".$dbname.".log_transaksi_vw a 
left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
where a.kodegudang ='".$gdg."' and tipetransaksi in(5,7) and (b.namabarang 
like '%".$txtfind."%' or a.kodebarang like '%".$txtfind."%') and kelompokbarang like '%".$kelbrg."%' 
group by kodebarang order by kodebarang asc";

$in = $out = array();
$resin=$owlPDO->query($strin) or die(print " Gagal: ".PDOException::getMessage());
$resin->setFetchMode(PDO::FETCH_OBJ);
while($barin=$resin->fetch()){
	$in[$barin->kodebarang]=tanggalnormal($barin->tgl);
}

$resout=$owlPDO->query($strout) or die(print " Gagal: ".PDOException::getMessage());
$resout->setFetchMode(PDO::FETCH_OBJ);
while($barout=$resout->fetch()){
   $out[$barout->kodebarang]=tanggalnormal($barout->tgl);
}

$str1 = "select * from ".$dbname.".log_5klbarang order by kelompok asc";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($val=$res1->fetch()){
    $nmkl[$val->kode]=$val->kelompok;
}

$str2 = "select * from ".$dbname.".log_5subklbarang order by namasubkelompok asc";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);
while($val=$res2->fetch()){
    $nmsubkl[$val->kode]=$val->namasubkelompok;
}


$no=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ); 
    $stream.="
    <table border=1>
    <thead>
    <tr><td bgcolor=#DEDEDE colspan=10>Master Barang</td></tr>
        <tr>
          <td bgcolor=#DEDEDE align=center>No.</td>
          <td bgcolor=#DEDEDE align=center>".str_replace(" ", "<br>", $_SESSION['lang']['kodekelompok'])."</td>
          <th bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namakelompok'] . "</th>
          <td bgcolor=#DEDEDE align=center>".str_replace(" ", "<br>", $_SESSION['lang']['subkelompokbarang'])."</td>
          <th bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namasubkelompokbarang']. "</th>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['materialcode']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['materialname']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['satuan']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['konversi']."</td>
          <td bgcolor=#DEDEDE align=center>".$_SESSION['lang']['status']."</td>
		</tr></thead><tbody>";
	// $stream="";
    while($bar=$res->fetch())
    {
        $no+=1;
        
        $stream.="<tr>
			 <td align='right'>" . $no . "</td>
			<td align='center'>" . $bar->kelompokbarang . "</td>
            <td align='center'>" . $nmkl[$bar->kelompokbarang] . "</td>
			<td align='center'>".substr($bar->kodebarang,0,5)."</td>
            <td align='center'>".$nmsubkl[substr($bar->kodebarang,0,5)]."</td>
			<td align='center'>" . $bar->kodebarang . "</td>
			<td>" . $bar->namabarang . "</td>
			<td align='center'>" . $bar->satuan . "</td>
			<td align='center'>".($bar->konversi=='0'?'Tidak':'Ya')."</td>    
			<td align='center'>".($bar->inactive=='0'?'Aktif':'Non-Aktif')."</td>
		</tr>";
    }
$stream.="</tbody></table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
$nop_="Daftar Barang";
/*if(strlen($stream)>0)
{
if ($handle = opendir('tempExcel')) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && $file != "index.html") {
            @unlink('tempExcel/'.$file);
        }
    }	
   closedir($handle);
}
 $handle=fopen("tempExcel/".$nop_.".xls.gz",'w');
 if(!fwrite($handle,$stream))
 {
  echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
   exit;
 }
 else
 {
  echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls.gz';
        </script>";
 }
closedir($handle);
}*/

// $titlelaporan="Rekaptulasi Kontraktor Angkutan TBS";
if($handle = opendir('tempExcel')){
	while(false !== ($file = readdir($handle))){
		if($file != "." && $file != ".." && $file != "index.html"){
			@unlink('tempExcel/' . $file);
		}
	}
	closedir($handle);
}
$handle = fopen("tempExcel/".$nop_.".xls",'w');
if(!fwrite($handle, $stream)){
	echo "<script language=javascript1.2>
		parent.window.alert('Cant convert to excel format');
	</script>";
	exit;
}else{
	echo "<script language=javascript1.2>
		window.location='tempExcel/".$nop_.".xls';
		</script>";
}
closedir($handle);

// if(strlen($stream)>0){
	// $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
	// gzwrite($gztralala, $stream);
	// gzclose($gztralala);
	// echo "<script language=javascript1.2>
	// window.location='tempExcel/".$nop_.".xls.gz';
	// </script>";
// }
?>

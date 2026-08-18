<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$kodeorg=$_GET['kodeorg'];
if($periode=='')$periode=date('Y');    

$str2 = "select a.karyawanid,count(a.karyawanid) as xberobat, sum(jlhbayar) as klaim, sum(totalklaim) as biaya, d.namakaryawan,d.lokasitugas,
             COALESCE(ROUND(DATEDIFF('" . date('Y-m-d') . "',d.tanggallahir)/365.25,1),0) as umur 
             from " . $dbname . ".sdm_pengobatanht a 
             left join " . $dbname . ".datakaryawan d on a.karyawanid=d.karyawanid 
              where a.periode like '" . $periode . "%'
              and d.lokasitugas like '" . $kodeorg . "%'
        group by a.karyawanid order by klaim desc, xberobat desc
    ";
$stream="Laporan Ranking Biaya / Karyawan ".$periode." ".$kodeorg."
<table border=1>
<thead>
<tr>
    <td bgcolor=#dedede align=center>Rank</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['namakaryawan'] . "</td>
	<td bgcolor=#dedede align=center width=50px>" . $_SESSION['lang']['umur'] ." ( " . $_SESSION['lang']['tahun'] ." )</td>
	<td bgcolor=#dedede align=center width=75px>" . $_SESSION['lang']['lokasitugas'] . "</td>
	<td bgcolor=#dedede align=center width=50px>" . $_SESSION['lang']['jumlah'] . " Berobat</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['dibayar'] . "</td>
	
</tr>
</thead>
<tbody>"; 
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ); 
$no=0;
while($bar2=$res2->fetch())
{
    $no+=1;
    $stream.="<tr class=rowcontent>
            <td>" . $no . "</td>
            <td>" . $bar2->namakaryawan . "</td>
            <td align=center>" . $bar2->umur . "</td>       
            <td align=center>" . $bar2->lokasitugas . "</td>
			<td align=right>" . $bar2->xberobat . "</td>
			<td align=right>" . number_format($bar2->biaya) . "</td>
		    <td align=right>" . number_format($bar2->klaim) . "</td>
    </tr>";	 
	$totalby+=$bar2->biaya;
    $total+=$bar2->klaim; 	

}   

$stream.="<tr class=rowcontent>
	<td></td>
   <td><b>" . $_SESSION['lang']['total'] . "</b></td>
   <td></td>
   <td></td>
   <td></td>
   <td align=right><b>" . number_format($totalby) . "</b></td>
   <td align=right><b>" . number_format($total) . "</b></td>
	</tr>";
	
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="LaporanRankingBiayaperKaryawan-".$periode.$kodeorg;
if(strlen($stream)>0)
{
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/'.$file);
            }
        }	
        closedir($handle);
    }
    $handle=fopen("tempExcel/".$nop_.".xls",'w');
    if(!fwrite($handle,$stream))
    {
    echo "<script language=javascript1.2>
        parent.window.alert('Cant convert to excel format');
        </script>";
    exit;
    }
    else
    {
    echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
    }
    fclose($handle);
}	        
   
	 
?>

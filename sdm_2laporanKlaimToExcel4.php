<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$kodeorg=$_GET['kodeorg'];
if($periode=='')$periode=date('Y');    

$str3 = "select sum(jasars) as rs, sum(jasadr) as dr, sum(jasalab) as lab, sum(byobat) as obat, sum(bypendaftaran) as administrasi, sum(totalklaim) as jumlah, sum(a.jlhbayar) as klaim,a.periode from " . $dbname . ".sdm_pengobatanht a 
        left join " . $dbname . ".datakaryawan c
        on a.karyawanid=c.karyawanid
              where a.periode like '" . $periode . "%'
              and c.lokasitugas like '" . $kodeorg . "%'
        group by periode order by periode
    ";


$stream="Trend Biaya Pengobatan ".$periode." ".$kodeorg."
<table border=1>
<thead>
<tr>
    <td bgcolor=#dedede>No</td>
    <td bgcolor=#dedede>Period</td>
    <td align=center>" . $_SESSION['lang']['biayars'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayadr'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayalab'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayaobat'] . "</td>
        <td align=center>" . $_SESSION['lang']['biayapendaftaran'] . "</td>
        <td align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td align=center>" . $_SESSION['lang']['dibayar'] . "</td>
</tr>
</thead>
<tbody>";  
$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
$res3->setFetchMode(PDO::FETCH_OBJ);
$no=0;
while($bar3=$res3->fetch())
{
    $no+=1;
    $stream.="<tr class=rowcontent>
             <td align=center>" . $no . "</td>
            <td>" . $bar3->periode . "</td>
            <td align=right>" . number_format($bar3->rs) . "</td>
            <td align=right>" . number_format($bar3->dr) . "</td>
            <td align=right>" . number_format($bar3->lab) . "</td>
            <td align=right>" . number_format($bar3->obat) . "</td>
            <td align=right>" . number_format($bar3->administrasi) . "</td>
            <td align=right>" . number_format($bar3->jumlah) . "</td>
            <td align=right>" . number_format($bar3->klaim) . "</td>
    </tr>";	 
			$trs+=$bar3->rs;
		$tdr+=$bar3->dr;
		$tlab+=$bar3->lab;
		$tobat+=$bar3->obat;
		$tadm+=$bar3->administrasi;
		$tjlh+=$bar3->jumlah;
		$tklaim+=$bar3->klaim;
}   

	$stream.="<tr class=rowcontent>
            <td colspan=2 align=center><b>TOTAL</b></td>
            <td align=right><b>" . number_format($trs) . "</b></td>
            <td align=right><b>" . number_format($tdr) . "</b></td>
            <td align=right><b>" . number_format($tlab) . "</b></td>
            <td align=right><b>" . number_format($tobat) . "</b></td>
            <td align=right><b>" . number_format($tadm) . "</b></td>
            <td align=right><b>" . number_format($tjlh) . "</b></td>
            <td align=right><b>" . number_format($tklaim) . "</b></td>
			</tr>";
			
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="TrendBiayaperDiagnosa-".$periode.$kodeorg;
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

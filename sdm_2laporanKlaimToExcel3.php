<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$kodeorg=$_GET['kodeorg'];
if($periode=='')$periode=date('Y');    

$str3 = "select a.diagnosa,  sum(jasars) as rs, sum(jasadr) as dr, sum(jasalab) as lab, sum(byobat) as obat, sum(bypendaftaran) as administrasi, a.periode, sum(a.jlhbayar) as bayar, sum(totalklaim) as klaim,d.diagnosa as ketdiag from " . $dbname . ".sdm_pengobatanht a 
	  left join " . $dbname . ".sdm_5diagnosa d
	  on a.diagnosa=d.id 
        left join " . $dbname . ".datakaryawan c
        on a.karyawanid=c.karyawanid
              where a.periode like '" . $periode . "%'
              and c.lokasitugas like '" . $kodeorg . "%'
        group by a.diagnosa order by klaim desc";


$stream="Laporan Ranking Biaya / Diagnosa ".$periode." ".$kodeorg."
<table border=1>
<thead>
<tr>
    <td bgcolor=#dedede>Rank</td>
    <td bgcolor=#dedede>Diagnose</td>
    <td bgcolor=#dedede align=center>" . $_SESSION['lang']['biayars'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['biayadr'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['biayalab'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['biayaobat'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['biayapendaftaran'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	<td bgcolor=#dedede align=center>" . $_SESSION['lang']['dibayar'] . "</td>
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
            <td>".$no."</td>
            <td>".$bar3->ketdiag."</td>
            <td align=right>" . number_format($bar3->rs) . "</td>
            <td align=right>" . number_format($bar3->dr) . "</td>
            <td align=right>" . number_format($bar3->lab) . "</td>
            <td align=right>" . number_format($bar3->obat) . "</td>
            <td align=right>" . number_format($bar3->administrasi) . "</td>
            <td align=right>" . number_format($bar3->klaim) . "</td>
            <td align=right>" . number_format($bar3->bayar) . "</td>
			</tr>";	  	
	$trs+=$bar3->rs;
	$tdr+=$bar3->dr;
	$tlab+=$bar3->lab;
	$tobat+=$bar3->obat;
	$tadm+=$bar3->administrasi;
	$ttl+=$bar3->klaim;
	$tbyr+=$bar3->bayar;

}

$stream.="<tr class=rowcontent>
            <td align=center colspan=2><b>TOTAL</b></td>
            <td align=right><b>" . number_format($trs) . "</b></td>
            <td align=right><b>" . number_format($tdr) . "</b></td>
            <td align=right><b>" . number_format($tlab) . "</b></td>
            <td align=right><b>" . number_format($tobat) . "</b></td>
            <td align=right><b>" . number_format($tadm) . "</b></td>
            <td align=right><b>" . number_format($ttl) . "</b></td>
            <td align=right><b>" . number_format($tbyr) . "</b></td>
			</tr>";
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";	 
//write exel   
$nop_="LaporanRankingBiayaperDiagnosa-".$periode.$kodeorg;
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

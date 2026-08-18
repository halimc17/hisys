<?php

//ind
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$optTipePot = makeOption($dbname, 'sdm_ho_component', 'id,name');
$method = checkPostGet('method', '');
$kodeorg = checkPostGet('kodeorg', '');
$periodegaji = checkPostGet('periodegaji', '');
$tipepotongan = checkPostGet('tipepotongan', '');
// $arrNmtp = array("0", "Staff", "1" => "BNS", "2" => "PKWT", "3" => "KHT", "4" => "KHT", "5" => "MAGANG");

switch ($method) {
    case'excel':

        $iHead = "select * from " . $dbname . ".sdm_potonganht 
		where kodeorg='" . $kodeorg . "' and periodegaji='" . $periodegaji . "' and tipepotongan='" . $tipepotongan . "'";
		$nHead=$owlPDO->query($iHead) or die(print " Gagal: ".PDOException::getMessage());
		$nHead->setFetchMode(PDO::FETCH_ASSOC);
        $dHead = $nHead->fetch();

        $stream = "Kode Organisasi : " . $kodeorg . "<br>";
        $stream.="Periode : " . $periodegaji . "<br>";
        $stream.="Tipe Potongan : " . $optTipePot[$tipepotongan] . "<br>";

        $stream.="<br /><table class=sortable border=1 cellspacing=1>
			 <thead>
				<tr>
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nourut'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nik'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['namakaryawan'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['lokasitugas'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['potongan'] . "</td> 
					<td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['keterangan'] . "</td>
				</tr>";


        
            $iDet = "select a.* from " . $dbname . ".sdm_potongandt a
					left join datakaryawan b on a.nik=b.karyawanid
					where periodegaji='" . $periodegaji . "' "
                    . "and kodeorg='" . $_SESSION['empl']['lokasitugas'] . "'
                      and tipepotongan='" . $tipepotongan . "'  order by b.namakaryawan asc";
        
		
		$nDet=$owlPDO->query($iDet) or die(print " Gagal: ".PDOException::getMessage());
		$nDet->setFetchMode(PDO::FETCH_ASSOC);
		$tot = 0;
        while ($dDet = $nDet->fetch()) {

            $wh = "karyawanid='" . $dDet['nik'] . "'";
            $optNik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', $wh);
            $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $wh);
            $optTp = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', $wh);

            $no+=1;

            $stream.="<tr>
						<td>" . $no . "</td>
						<td>" . $optNik[$dDet['nik']] . "</td>
						<td>" . $optNm[$dDet['nik']] . "</td>
						<td>" . getNamaTipekaryawan($dDet['nik'])."</td>
						<td>" . $dDet['kodeorg'] . "</td>
						<td>" . number_format($dDet['jumlahpotongan']) . "</td>
						<td>" . $dDet['keterangan'] . "</td>
					</tr>";
            $tot+=$dDet['jumlahpotongan'];
			
        }
        $stream.="<tr>
						<td colspan=5>Total</td>
						<td colspan=1>" . number_format($tot) . "</td>
					</tr></table>";
					

        $stream.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        setIt($dHead['kode'], '');
        $nop_ = "Laporan_Potongan_" . $dHead['kode'];
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/'.$file);
					}
				}	
			   closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript>
					parent.window.alert('Can't convert to excel format');
					</script>";
				exit;
			}else{
				echo "<script language=javascript>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
			}
			fclose($handle);
		}
        break;
}
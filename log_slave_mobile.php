<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method 	= checkPostGet('method', '');
$type   	= checkPostGet('type', '');
$pages 		= checkPostGet('pages', '');
$dari 		= checkPostGet('dariTanggal', '');
$sampai 	= checkPostGet('sampaiTanggal', '');
$karCari 	= checkPostGet('karyawanCari', '');

switch ($method) {
	case'loaddata':
		$tab 	= '';
		$tfoot 	= '';
		$where 	= "1=1";
		
		if($dari != ''){
			$where	.= " and created >= '".tanggalsystem($dari)."'";
		}
		
		if($sampai != ''){
			$where	.= " and created <= '".tanggalsystem($sampai)."'";
		}

		if($karCari != ''){
			$where	.= " and karyawanid = '".$karCari."'";
		}
	
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page = $pages;

			if($page<0) {
				$page=0;
			}
        }
		$offset	= $page*$limit;
		
		$no 	= (($page*$limit));
		
		$str 	= "select * from ".$dbname.".log_user_mobile where ".$where." limit ".$offset.",".$limit."";
		$res 	= fetchdata($str);
		$jlhbrs = count($res);

		if ($type == 'html') {
			$border = "border='0'";
		} else {
			$border = "border='1'";
		} 

		$tab .= "<table class='sortable' cellspacing='1' cellpadding=3 ".$border." style='min-width:700px;'>
			<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>".$_SESSION['lang']['pt']."</td>
					<td align='center'>".$_SESSION['lang']['unit']."</td>
					<td align='center'>".$_SESSION['lang']['karyawan']."</td>
					<td align='center'>".$_SESSION['lang']['kegiatan']."</td> 
					<td align='center'>".$_SESSION['lang']['tanggal']."</td>
				</tr>
			</thead>
			<tbody>";
				if($jlhbrs <= 0){
					$tab .= "<tr class=rowcontent><td colspan='7' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}else{			
					foreach ($res as $row) {
						$nmKaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', 'karyawanid='.$row['karyawanid']);
						$nmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
						$no = $no + 1;
						$tab .= '<tr class=rowcontent>';
							$tab .= '<td>'.$no.'. </td>';
							$tab .= '<td>['.$row['kodeorg'].'] '.$nmOrg[$row['kodeorg']].'</td>';
							$tab .= '<td>['.$row['unit'].'] '.$nmOrg[$row['unit']].'</td>';
							$tab .= '<td>['.$row['karyawanid'].'] '.$nmKaryawan[$row['karyawanid']].'</td>';
							$tab .= '<td>'.$row['aktifitas'].'</td>';
							$tab .= '<td>'.tglnmblnsec($row['created'], 'I', 'long').'</td>';
						$tab .= '</tr>';
					}
				}
		$tab .= "</tbody>
			<tfoot id='footerData'>
			</tfoot>
		</table>";
			## PAGING
			$tfoot .= createpaging($jlhbrs,$limit,$page,'10','loadData','getPage');
			$tfoot .= "</table>";
		if ($type == 'html') {
			echo $tab.'####'.$tfoot;
		} else if ($type == 'pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dompdf->stream("Rekaptulasi Log Mobile", array("Attachment" => false));
		} else if ($type == 'excel') {
			$titlelaporan = "Rekaptulasi Log Mobile";
			if($handle = opendir('tempExcel')){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "index.html"){
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
			if(!fwrite($handle, $tab)){
				echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$titlelaporan.".xls';
					</script>";
			}
			closedir($handle); 
		}
		
	break;
	
	default:
		# code...
	break;
}

?>
<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');

$proses = checkPostGet('proses','');
$unit = checkPostGet('unit','');
$periode = checkPostGet('periode','');
$idkaryawan = checkPostGet('idkaryawan','');

if($proses=='excel'){
	$brd = 1;
}else{
	$brd = 0;
}

$tab = "";
$tab .= "<table class=sortable cellspacing=1 cellpadding=3 border=".$brd.">
	<thead>
	<tr class=rowheader>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['nourut']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['kode']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['alokasi']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['tanggalterimadana']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['berangkat']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['kembali']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['divisi']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['karyawan']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['keterangan']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['sumberdana']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['uangmuka']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['realuangmuka']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['kelebihan']."/".$_SESSION['lang']['kekurangan']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['tanggalterima']." LPJ</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['tanggalselesai']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['nointernalmemoho']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['nointernalmemokebun']."</td>
		<td style='text-align:center' rowspan=3>".$_SESSION['lang']['status']."</td>";
$tab .= "</tr>";
$tab .= "</thead><tbody>";

$str = "select a.*, b.namakaryawan, c.namaorganisasi, d.keterangan2, d.tanggal as tanggalterima, d.jumlah as jumlahbayar, e.namaorganisasi as subbagian, f.nama as bagian from ".$dbname.".sdm_pjdinasht a 
	left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
	left join ".$dbname.".organisasi c on a.tujuan1=c.kodeorganisasi
	left join ".$dbname.".keu_kasbankdt d on a.notransaksi=d.nodok 
	left join ".$dbname.".organisasi e on b.subbagian=e.kodeorganisasi 
	left join ".$dbname.".sdm_5departemen f on b.bagian=f.kode 
	where a.kodeorg like '".$unit."%' and a.tanggalperjalanan like '".$periode."%' and a.karyawanid like '".$idkaryawan."%'  and a.karyawanid != '' order by a.tanggalperjalanan desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$no = 0;
while($bar = $res->fetch())
{
	$optSumber = makeOption($dbname,"keu_5akun","noakun,namaakun","noakun='".$bar['noakun2a']."'");
	$no++;
	$tab .= "<tr class=rowcontent>";
	$tab .= "<td style='text-align:right'>".$no."</td>";
	$tab .= "<td>UMPD</td>";
	$tab .= "<td>".$bar['tujuan1']." - ".$bar['namaorganisasi']."</td>";
	$tab .= "<td style='text-align:center'>".tanggalnormal($bar['tanggalterima'])."</td>";
	$tab .= "<td style='text-align:center'>".tanggalnormal($bar['tanggalperjalanan'])."</td>";
	$tab .= "<td style='text-align:center'>".tanggalnormal($bar['tanggalkembali'])."</td>";
	$tab .= "<td>".($bar['subbagian'] == '' ? $bar['bagian'] : $bar['subbagian'])."</td>";
	$tab .= "<td>".$bar['namakaryawan']." (".$bar['karyawanid'].")</td>";
	$tab .= "<td>".$bar['keterangan2']."</td>";
	$tab .= "<td>".$optSumber[$bar['noakun2a']]." ".$bar['noakun2a']."</td>";
	$tab .= "<td style='text-align:right'>".number_format($bar['uangmuka'])."</td>";
	$tab .= "<td style='text-align:right'>".number_format($bar['dibayar'])."</td>";
	$tab .= "<td style='text-align:right'>".number_format($bar['sisa'])."</td>";
	$tab .= "<td style='text-align:center'>".tanggalnormal($bar['tanggalterima'])."</td>";
	$tab .= "<td style='text-align:center'>".tanggalnormal($bar['tanggalterima'])."</td>";
	$tab .= "<td></td>";
	$tab .= "<td></td>";
	$tab .= "<td>".($bar['jumlahbayar'] <= $bar['uangmuka'] && $bar['jumlahbayar'] != 0 ? "Lunas" : "Belum Lunas")."</td>";
	$tab .= "</tr>";
	
}
$tab .= "</tbody>
</table>";

switch($proses){
	case 'preview':
		// if(count($arrHama) == 0){
			// echo $_SESSION['lang']['datanotfound'];
		// }else{
			// $arrGrap = array();
			// $arrJnsHama = array();
			// $no = -1;
			// foreach($arrHama as $keyHama){
				// $optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$keyHama."'");
				// $no++;
				// $arrJnsHama[$no] = $optNmHama[$keyHama];
				// @$arrGrap[$no] += $arrGr[$keyHama];
			// }
			
			// if(count($arrJnsHama)==1){
				// $widthGraph = 300;
			// }else{
				// $widthGraph = count($arrJnsHama) * 200;
			// }
			
			
			// $graph = new Graph($widthGraph,500);
			// $graph->img->SetMargin(80,40,40,80); 

			// $graph->img->SetAntiAliasing();
			// $graph->SetScale("textint");
			
			// $graph->SetShadow();
			
			// $bplot = new BarPlot($arrGrap);
			// $bplot->SetWidth(0.2);
			// $bplot->SetShadow();
			// $bplot->SetFillColor('blue');
			// $graph->Add($bplot);
			
			// // Setup the titles
			// $graph->title->Set($_SESSION['lang']['hpt']." ".$tahun);
			// $graph->xaxis->title->Set($_SESSION['lang']['jenishama']);
			// $graph->yaxis->title->Set($_SESSION['lang']['populasi']);
			 
			// $graph->xaxis->SetTickLabels($arrJnsHama);
			// $graph->title->SetFont(FF_DEFAULT,FS_BOLD);
			// $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
			// $graph->yaxis->title->SetMargin(25);
			// $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
			// // $graph->xaxis->scale->SetGrace(count($arrJnsHama));
			 
			// // Display the graph
			// $graph->StrokeCSIM();
		// }
	
		echo "####".$tab;
		
		break;
		
	case 'excel':
		$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];
		$stream = $tab;
		$nop_= $_SESSION['lang']['laporan']." ".$_SESSION['lang']['hpt']." ".$tahun;
		
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
				 gzwrite($gztralala, $tab);
				 gzclose($gztralala);
				 echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls.gz';
					</script>";
		break;
		
	default:	
		break;
}

?>
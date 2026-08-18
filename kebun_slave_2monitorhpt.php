<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');

$proses = checkPostGet('proses','');
$tahun = checkPostGet('tahun','');
$jenishama = checkPostGet('jenishama','');

if($tahun == ''){
	exit("warning : ".$_SESSION['lang']['tahun']." required");
}

$WHR = "";
if($jenishama != ''){
	$WHR = " and t1.kodehama='".$jenishama."'";
}

$periode = month_inbetween($tahun.'-01', $tahun.'-12');

//GET JENIS HAMA
$arrHama = array();
$arrTrSus = array();
$arrTrPng = array();
$arrVal = array();
$arrBlok = array();
$arrSum = array();
$arrGr = array();

$str = "select distinct(t1.kodehama) as kodehama, t1.nosensus, t1.jumlah, t2.blok from ".$dbname.".kebun_hpt_sensus_dt t1
left join ".$dbname.".kebun_hpt_sensus_ht t2 ON t1.nosensus = t2.nosensus
where LEFT(t1.nosensus,4) = '".$tahun."' ".$WHR."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$arrHama[$bar['kodehama']] = $bar['kodehama'];
	$arrTrSus[] = $bar['nosensus'];
	$arrBlok[$bar['blok']] = $bar['blok'];
	$arrVal[$bar['blok']][$bar['kodehama']][intval(substr($bar['nosensus'],4,2))]['sensus'] = $bar['jumlah'];
	@$arrGr[$bar['kodehama']] += $bar['jumlah'];
}

$str = "select distinct(t1.kodehama) as kodehama, t1.jumlah, t1.nopenanggulangan, t2.kodeorg from ".$dbname.".kebun_hpt_penanggulangan_dt t1 
left join ".$dbname.".kebun_hpt_penanggulangan_ht t2 ON t1.nopenanggulangan = t2.nopenanggulangan
where LEFT(t1.nopenanggulangan,4) = '".$tahun."' ".$WHR."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar = $res->fetch()){
	$arrHama[$bar['kodehama']] = $bar['kodehama'];
	$arrTrPng[] = $bar['nopenanggulangan'];
	$arrBlok[$bar['kodeorg']] = $bar['kodeorg'];
	$arrVal[$bar['kodeorg']][$bar['kodehama']][intval(substr($bar['nopenanggulangan'],4,2))]['penanggulangan'] = $bar['jumlah'];
}

ksort($arrBlok);

if($proses=='excel'){
	$brd = 1;
}else{
	$brd = 0;
}

$tab = "";
$tab .= "<table class=sortable cellspacing=1 cellpadding=3 border=".$brd.">
	<thead>
	<tr class=rowheader>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['nourut']."</th>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['kebun']."</th>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['divisi']."</th>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['blok']."</th>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['luas']." (HA)</th>
		<th style='text-align:center' rowspan=3>".$_SESSION['lang']['jmlhpokok']."</th>";
foreach($arrHama as $keyHama){
	$optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$keyHama."'");
	$optSatuanHama = makeOption($dbname,'kebun_5jenishama','kodehama,satuan',"kodehama='".$keyHama."'");
	$tab .= "<th style='text-align:center' colspan='".(count($periode) * 2)."'>".$optNmHama[$keyHama]." (".$optSatuanHama[$keyHama].")</th>";
}
$tab .= "</tr>";
$tab .= "<tr class=rowheader>";
foreach($arrHama as $keyHama){
	$tab .= "<th style='text-align:center' colspan='".(count($periode))."'>".$_SESSION['lang']['sensus']."</th>";
	$tab .= "<th style='text-align:center' colspan='".(count($periode))."'>".$_SESSION['lang']['penanggulangan']."</th>";
}
$tab .= "</tr>";
$tab .= "<tr class=rowheader>";
foreach($arrHama as $keyHama){
	$cBulan = 0;
	foreach($periode as $keyPeriode){
		$cBulan++;
		$tab .= "<th>".numToMonth($cBulan,'E','short')."</th>";
	}
	$cBulan = 0;
	foreach($periode as $keyPeriode){
		$cBulan++;
		$tab .= "<th>".numToMonth($cBulan,'E','short')."</th>";
	}
}
$tab .= "</tr>";
$tab .= "</thead><tbody>";

$no = 0;
foreach($arrBlok as $key){
	$optLuas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$key."'");
	$optPokok = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$key."'");
	$no++;
	$tab .= "<tr class=rowcontent>";
	$tab .= "<td style='text-align:right'>".$no."</td>";
	$tab .= "<td>".substr($key,0,4)."</td>";
	$tab .= "<td>".substr($key,0,6)."</td>";
	$tab .= "<td>".$key."</td>";
	$tab .= "<td style='text-align:right'>".$optLuas[$key]."</td>";
	$tab .= "<td style='text-align:right'>".number_format($optPokok[$key])."</td>";
	
	foreach($arrHama as $keyHama){
		$cBulan = 0;
		foreach($periode as $keyPeriode){
			$cBulan++;
			$tab .= "<td style='text-align:right'>".(isset($arrVal[$key][$keyHama][$cBulan]['sensus']) ? number_format($arrVal[$key][$keyHama][$cBulan]['sensus']) : "-")."</td>";
			@$arrSum[$keyHama][$cBulan]['sensus'] += $arrVal[$key][$keyHama][$cBulan]['sensus'];
		}
		$cBulan = 0;
		foreach($periode as $keyPeriode){
			$cBulan++;
			$tab .= "<td style='text-align:right'>".(isset($arrVal[$key][$keyHama][$cBulan]['penanggulangan']) ? number_format($arrVal[$key][$keyHama][$cBulan]['penanggulangan']) : "-")."</td>";
			@$arrSum[$keyHama][$cBulan]['penanggulangan'] += $arrVal[$key][$keyHama][$cBulan]['penanggulangan'];
		}
	}
	$tab .= "</tr>";
}

$tab .= "<tr class=rowcontent>";
$tab .= "<td colspan=6 style='text-align:center;font-weight:bold'>".$_SESSION['lang']['total']."</td>";
foreach($arrHama as $keyHama){
	$cBulan = 0;
	foreach($periode as $keyPeriode){
		$cBulan++;
		$tab .= "<td style='text-align:right'>".number_format($arrSum[$keyHama][$cBulan]['sensus'])."</td>";
	}
	
	$cBulan = 0;
	foreach($periode as $keyPeriode){
		$cBulan++;
		$tab .= "<td style='text-align:right'>".number_format($arrSum[$keyHama][$cBulan]['penanggulangan'])."</td>";
	}
}
$tab .= "</tr>";
$tab .= "</tbody>
</table>";

switch($proses){
	case 'preview':
		if(count($arrHama) == 0){
			echo $_SESSION['lang']['datanotfound'];
		}else{
			$arrGrap = array();
			$arrJnsHama = array();
			$no = -1;
			foreach($arrHama as $keyHama){
				$optNmHama = makeOption($dbname,'kebun_5jenishama','kodehama,namahama',"kodehama='".$keyHama."'");
				$no++;
				$arrJnsHama[$no] = $optNmHama[$keyHama];
				@$arrGrap[$no] += $arrGr[$keyHama];
			}
			
			if(count($arrJnsHama)==1){
				$widthGraph = 300;
			}else{
				$widthGraph = count($arrJnsHama) * 200;
			}
			
			
			$graph = new Graph($widthGraph,500);
			$graph->img->SetMargin(80,40,40,80); 

			$graph->img->SetAntiAliasing();
			$graph->SetScale("textint");
			
			$graph->SetShadow();
			
			$bplot = new BarPlot($arrGrap);
			$bplot->SetWidth(0.2);
			$bplot->SetShadow();
			$bplot->SetFillColor('blue');
			$graph->Add($bplot);
			
			// Setup the titles
			$graph->title->Set($_SESSION['lang']['hpt']." ".$tahun);
			$graph->xaxis->title->Set($_SESSION['lang']['jenishama']);
			$graph->yaxis->title->Set($_SESSION['lang']['populasi']);
			 
			$graph->xaxis->SetTickLabels($arrJnsHama);
			$graph->title->SetFont(FF_DEFAULT,FS_BOLD);
			$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
			$graph->yaxis->title->SetMargin(25);
			$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
			// $graph->xaxis->scale->SetGrace(count($arrJnsHama));
			 
			// Display the graph
			$graph->StrokeCSIM();
		}
	
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
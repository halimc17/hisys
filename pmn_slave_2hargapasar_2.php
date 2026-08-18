<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
require_once('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');

//$arr2="##psrId2##komodoti##periodePsr2";

$proses = checkPostGet('proses', '');
$psrId = checkPostGet('psrId2', '');
$periodePsr = checkPostGet('periodePsr2', '');
$komoditi = checkPostGet('komoditi2', '');
$idPasar = checkPostGet('idPasar', '');
$idMatauang = checkPostGet('idMatauang', '');
$hrgPasar = checkPostGet('hrgPasar', '');
$tglHarga = tanggalsystem(checkPostGet('tglHarga', ''));

$optNmBarang=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',$whr);

if($psrId!=''){
    $where.=" and pasar='".$psrId."'";
    $whr.=" and pasar='".$psrId."'";
}

if($periodePsr!=''){
    $where.=" and tanggal like '".$periodePsr."%'";
}else{
    exit("Warning : Periode Tidak Boleh Kosong");
}

if($komoditi != ''){
    $where.=" and kodeproduk='".$komoditi."'";
     $whr.=" and kodeproduk='".$komoditi."'";
}else{
	exit("Warning : Komoditi Tidak Boleh Kosong");
}

	switch($proses){
		
		case'preview':
	
		echo"<table class=sortable cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
			   <td>No</td>
			   <td>".$_SESSION['lang']['tanggal']."</td>
			   <td>".$_SESSION['lang']['komoditi']."</td>
			   <td>".$_SESSION['lang']['satuan']."</td>
			   <td>".$_SESSION['lang']['pasar']."</td>
			   <td>".$_SESSION['lang']['matauang']."</td>
				<td>".$_SESSION['lang']['harga']."</td></tr></thead><tbody>";
		
		$str="select * from ".$dbname.".pmn_hargapasar where tanggal!='' ".$where." order by `tanggal` desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $barisData = owlBaris($res);
			if($barisData>0){
                $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
					$no+=1;
					echo"<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".tanggalnormal($bar->tanggal)."</td>
                        <td>".$optNmBarang[$bar->kodeproduk]."</td>
                        <td>".$bar->satuan."</td>
                        <td>".$bar->pasar."</td>
                        <td>".$bar->matauang."</td>
                        <td align=right>".number_format($bar->harga,2)."</td>
                        </tr>";
				}	 	
                        
			}else{
			   echo"<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
			}
		echo"</tbody></table>";
		
		break;
		
		
		case'jpgraph':
			$bln=array();
			$num=date('t',mktime(0,0,0,substr($periodePsr,5,2),2,substr($periodePsr,0,4)));
			$labels=array();
			for($x=1;$x<=$num;$x++){
				array_push($labels, $x);
				if($x<10)
				  $y='0'.$x;
				else
				  $y=$x;  
				array_push($bln, $y);
			}
					
			$qPasar = selectQuery($dbname,'pmn_hargapasar',"distinct(pasar) as pasar");
			$resPasar = fetchData($qPasar);
			$optNamaPasar = array();
			foreach($resPasar as $row) {
				$optNamaPasar[$row['pasar']] = $row['pasar'];
			}
			
			$str = selectQuery($dbname,'pmn_hargapasar',"harga,kodeproduk,matauang,pasar,tanggal","tanggal like '".$periodePsr."%' ".$whr."");
			$res = fetchData($str);
			
			$optNilai = array();
			foreach($bln as $tgl=>$row){
				foreach($optNamaPasar as $row1){
					$optNilai[$row1][$tgl] = 0;
					foreach($res as $row2){
						if(($periodePsr."-".$row) == $row2['tanggal'] && $row1 == $row2['pasar']){
							$optNilai[$row1][$tgl] = $row2['harga'];
						}
					}
				}
			}
			//===========
					
			$graph = new Graph(850,550);
			$graph->img->SetMargin(40,40,40,80); 

			$graph->img->SetAntiAliasing();
			$graph->SetScale("textlin");
			
			$theme_class= new UniversalTheme;
			$graph->SetTheme($theme_class);
			
			$graph->yaxis->HideZeroLabel();
			
			$graph->SetShadow();
			$graph->title->Set($_SESSION['lang']['hargapasar']." - ".$optNmBarang[$komoditi]." (".$periodePsr.")");
			$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

			$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
			$graph->xaxis->SetTickLabels($labels);
			$graph->xaxis->SetLabelAngle(45);
			
			$optPasar = array();
			$no = 0;
			function randomColor() {
				$colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00','#00FFFF','#FF00FF','#98FB98','#CD5C5C','#ADD8E6','#E0FFFF','#FAFAD2','#3CB371','#FFDEAD','#FF4500','#B0E0E6','#D8BFD8');
				return $colorArray[array_rand($colorArray)];
			}
			
			foreach($resPasar as $row) {
				$no += 1;
				$resultColor = randomColor();
				if($row['pasar'] == $optNamaPasar[$row['pasar']]){
					$optPasar[$no] = new LinePlot($optNilai[$row['pasar']]);
					$optPasar[$no] -> SetColor($resultColor);
					$optPasar[$no] -> SetLegend($row['pasar']);
					$optPasar[$no] -> mark->SetType(MARK_FILLEDCIRCLE);
					$optPasar[$no] -> mark->SetFillColor($resultColor);
					$optPasar[$no] -> SetCenter();
				}
			}
			$graph->legend->SetFrameWeight(1);
			$graph->Add($optPasar);	
			$graph->Stroke();
			
		break;
			
					
		case'excel':
			$bgcoloraja="bgcolor=#DEDEDE align=center";
			$tab.="<table class=sortable cellspacing=1 border=1>
				<thead>
				<tr class=rowheader>
					<td ".$bgcoloraja.">No</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['tanggal']."</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['komoditi']."</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['satuan']."</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['pasar']."</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['matauang']."</td>
					<td ".$bgcoloraja.">".$_SESSION['lang']['harga']."</td></tr></thead><tbody>";
			
			$str="select * from ".$dbname.".pmn_hargapasar where tanggal!='' ".$where." order by `tanggal` desc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $barisData = owlBaris($res);
				if($barisData>0){
                     $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
						$no+=1;
						$tab.="<tr class=rowcontent>
							<td>".$no."</td>
							<td>".tanggalnormal($bar->tanggal)."</td>
							<td>".$optNmBarang[$bar->kodeproduk]."</td>
							<td>".$bar->satuan."</td>
							<td>".$bar->pasar."</td>
							<td>".$bar->matauang."</td>
							<td align=right>".number_format($bar->harga,2)."</td>
                        </tr>";
					}	 	
                }else{
					$tab.="<tr class=rowcontent><td colspan=8>".$_SESSION['lang']['dataempty']."</td></tr>";
				}
			$tab.="</tbody></table>";
			$tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
			$dte=date("Hms");
			$nop_="hargaPasar_".$dte;
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
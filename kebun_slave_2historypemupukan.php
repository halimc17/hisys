<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_bar.php');

$proses = checkPostGet('proses','');
$kebun = checkPostGet('kebun','');
$blok = checkPostGet('blok','');
$tahuntanam = checkPostGet('tahuntanam','');

if(strlen($blok)==4){
	$vTitle = $_SESSION['lang']['kebun']." - ".$blok;
}else if(strlen($blok)==6){
	$vTitle = $_SESSION['lang']['afdeling']." - ".$blok;
}else{
	$vTitle = $_SESSION['lang']['blok']." - ".$blok;
}

switch($proses){
	case 'getblok':
		//Get blok
		$optBlok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		
		if($kebun != ''){
			$sBlok = "select * from " . $dbname . ".organisasi where kodeorganisasi like '".$kebun."%' and tipe in('BLOK','AFDELING','KEBUN')";
			$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
			$qBlok->setFetchMode(PDO::FETCH_ASSOC);
			while ($rBlok = $qBlok->fetch()) {
				$optBlok.="<option value='" .$rBlok['kodeorganisasi'] . "'>".$rBlok['namaorganisasi']. "</option>";
			}
		}
		
		echo $optBlok;
		
		break;
		
	case 'getTahunTanam':
		//Get Tipe Organisasi
		$optOrganisasi = makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$blok."'");
		
		//Get Tahun Tanam
		$opttahuntanam = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		
		if($optOrganisasi[$blok] != 'BLOK'){
			$sTt = selectQuery($dbname, 'setup_blok', "distinct(tahuntanam) as tahuntanam", "kodeorg like '".$blok."%' ORDER BY tahuntanam ASC");
			$rTt = fetchData($sTt);
			foreach($rTt as $row){
				$opttahuntanam .= "<option value='".$row['tahuntanam']."'>".$row['tahuntanam']."</option>";
			}
		}
		
		echo $opttahuntanam."####".$optOrganisasi[$blok];
		break;
		
		case 'getGraph1':
			$optNamaPupuk = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
			$whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
		
			//Get Tahun
			$sthn = selectQuery($dbname, 'log_transaksi_vw', "distinct(substr(tanggal,1,4)) as tahun", "kodebarang like '311%' and tipetransaksi=5 and kodeblok in (".$whr.") group by substr(tanggal,1,4)");
			$rthn = fetchData($sthn);
			$arrthn = array();
			$arrthn2 = array();
			$not = -1;
			foreach ($rthn as $row) {
				$not++;
				$arrthn[$not] = $row['tahun'];
				$arrthn2[$not] = substr($row['tahun'],2,2);
			}
			
			if(count($arrthn) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				$sbrg = selectQuery($dbname, 'log_transaksi_vw', "kodebarang", "kodebarang like '311%' and tipetransaksi=5 and kodeblok in (".$whr.") group by kodebarang");
				$rbrg = fetchData($sbrg);
				$optPupuk = array();
				foreach ($rbrg as $row) {
					$optPupuk[$row['kodebarang']] = $row['kodebarang'];
				}
				
				$sval = selectQuery($dbname, 'log_transaksi_vw', "kodebarang, sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodebarang like '311%' and tipetransaksi=5 and kodeblok in (".$whr.")group by kodebarang, substr(tanggal,1,4)");
				$rval = fetchData($sval);
				
				$jumlah = array();
				foreach ($arrthn as $thn=>$valthn) {
					foreach ($optPupuk as $brg) {
						$jumlah[$brg][$thn] = 0;
						foreach ($rval as $val) {
							if ($val['tahun'] == $valthn && $val['kodebarang'] == $brg) {
								$jumlah[$brg][$thn] = (isset($val['jumlah']) ? $val['jumlah'] : 0);
							}
						}
					}
				}

				if(count($arrthn)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(80,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
								
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['pemupukan']."(".$_SESSION['lang']['aktual'].")".$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn2);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$graph->yaxis->title->Set('Kg/Ton');
				$graph->yaxis->title->SetMargin(25);
				
				function randomColor() {
					$colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00','#00FFFF','#FF00FF','#98FB98','#CD5C5C','#ADD8E6','#E0FFFF','#FAFAD2','#3CB371','#FFDEAD','#FF4500','#B0E0E6','#D8BFD8');
					return $colorArray[array_rand($colorArray)];
				}
				$no = 0;
				$optarrPupuk = array();
				foreach($rbrg as $row) {
					$no += 1;
					$resultColor = randomColor();
					if($row['kodebarang'] == $optPupuk[$row['kodebarang']]){
						$optarrPupuk[$no] = new LinePlot($jumlah[$row['kodebarang']]);
						$optarrPupuk[$no] -> SetColor($resultColor);
						$optarrPupuk[$no] -> SetLegend($optNamaPupuk[$row['kodebarang']]);
						$optarrPupuk[$no] -> mark->SetType(MARK_FILLEDCIRCLE);
						$optarrPupuk[$no] -> mark->SetFillColor($resultColor);
						$optarrPupuk[$no] -> SetCenter();
					}
				}
				$graph->legend->SetFrameWeight(1);
				$graph->Add($optarrPupuk);	
				$graph->StrokeCSIM();
				}
			
			break;
			
		case 'getGraph2':
			$whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
			$optNamaPupuk = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		
			//Get Tahun
			$sthn2 = selectQuery($dbname, 'kebun_rekomendasipupuk', "distinct(substr(periodepemupukan,1,4)) as tahun", "kodebarang like '311%' and blok in (".$whr.") group by substr(periodepemupukan,1,4)");
			$rthn2 = fetchData($sthn2);
			$arrthn2 = array();
			$arrthn2x = array();
			$not2 = -1;
			foreach ($rthn2 as $row) {
				$not2++;
				$arrthn2[$not2] = $row['tahun'];
				$arrthn2x[$not2] = substr($row['tahun'],2,2);
			}
			
			//Get Jumlah Pokok
			$sPokok = selectQuery($dbname, 'setup_blok_tahunan', "jumlahpokok as jumlahpokok, kodeorg, substr(tahun,1,4) as tahun", "kodeorg in (".$whr.") and tahun in (select max(tahun) from ".$dbname.".setup_blok_tahunan group by substr(tahun,1,4))");
			$rPokok = fetchData($sPokok);
			$optPokok = array();
			foreach ($rPokok as $row) {
				$optPokok[$row['tahun']] = $row['jumlahpokok'];
			}
			
			if(count($arrthn2) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				$sbrg2 = selectQuery($dbname, 'kebun_rekomendasipupuk', "kodebarang", "kodebarang like '311%' and blok in (".$whr.") group by kodebarang, substr(periodepemupukan,1,4)");
				$rbrg2 = fetchData($sbrg2);
				$optPupuk2 = array();
				foreach ($rbrg2 as $row) {
					$optPupuk2[$row['kodebarang']] = $row['kodebarang'];
				}
				
				$sval = selectQuery($dbname, 'kebun_rekomendasipupuk', "kodebarang, sum(dosis) as jumlah, substr(periodepemupukan,1,4) as tahun, blok", "kodebarang like '311%' and blok in (".$whr.") group by kodebarang, substr(periodepemupukan,1,4)");
				$rval = fetchData($sval);
				
				$jumlah2 = array();
				foreach ($arrthn2 as $thn=>$valthn) {
					foreach ($optPupuk2 as $brg) {
						if(!isset($jumlah2[$brg][$thn])){
							$jumlah2[$brg][$thn]=0;
						}
						foreach($rPokok as $valPokok){
							foreach ($rval as $val) {
								if($val['tahun'] == $valthn && $val['kodebarang'] == $brg && $valPokok['kodeorg'] == $val['blok']){
									$jumlah2[$brg][$thn] += ($val['jumlah'] * $valPokok['jumlahpokok']);
								}
							}
						}
					}
				}			
				
				if(count($arrthn2)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn2)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(55,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
				
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['pemupukan']."(".$_SESSION['lang']['rekomendasi'].")".$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn2x);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$graph->yaxis->scale->SetGrace(25);
				
				function randomColor() {
					$colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00','#00FFFF','#FF00FF','#98FB98','#CD5C5C','#ADD8E6','#E0FFFF','#FAFAD2','#3CB371','#FFDEAD','#FF4500','#B0E0E6','#D8BFD8');
					return $colorArray[array_rand($colorArray)];
				}
				$no = 0;
				$optarrPupuk = array();
				foreach($rbrg2 as $row) {
					$no += 1;
					$resultColor = randomColor();
					if($row['kodebarang'] == $optPupuk2[$row['kodebarang']]){
						$optarrPupuk[$no] = new LinePlot($jumlah2[$row['kodebarang']]);
						$optarrPupuk[$no] -> SetColor($resultColor);
						$optarrPupuk[$no] -> SetLegend($optNamaPupuk[$row['kodebarang']]);
						$optarrPupuk[$no] -> mark->SetType(MARK_FILLEDCIRCLE);
						$optarrPupuk[$no] -> mark->SetFillColor($resultColor);
						$optarrPupuk[$no] -> SetCenter();
					}
				}
				$graph->legend->SetFrameWeight(1);
				$graph->legend->SetPos(0.5,0.98,'center','bottom');
				$graph->Add($optarrPupuk);	
				$graph->StrokeCSIM();
			}
			
			break;
		
		case 'getGraph3':
			$whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
			$sthn3 = selectQuery($dbname, 'kebun_spb_vw', "distinct(substr(tanggal,1,4)) as tahun", "blok in (".$whr.") group by substr(tanggal,1,4)");
			$rthn3 = fetchData($sthn3);
			$arrthn3 = array();
			$arrthn3x = array();
			$not3 = -1;
			foreach ($rthn3 as $row) {
				$not3++;
				$arrthn3[$not3] = $row['tahun'];
				$arrthn3x[$not3] = substr($row['tahun'],2,2);
			}
			
			if(count($arrthn3) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				//Get Jumlah KgWb
				$sval = selectQuery($dbname, 'kebun_spb_vw', "blok, sum(kgwb) as jumlah, substr(tanggal,1,4) as tahun", "blok in (".$whr.") group by blok, substr(tanggal,1,4)");
				$rval = fetchData($sval);
				
				$jumlah3 = array();
				foreach ($arrthn3 as $thn=>$valthn) {
					if(isset($jumlah3[$thn])){
						$jumlah3[$thn] = 0;
					}
					foreach ($rval as $val) {
						if ($val['tahun'] == $valthn) {
							@$jumlah3[$thn] += (isset($val['jumlah']) ? $val['jumlah'] : 0) / 1000;
						}
					}
				}
				
				if(count($arrthn3)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn3)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(80,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
				
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['produksi'].$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn3x);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$graph->yaxis->title->Set('Kg (000)');
				$graph->yaxis->title->SetMargin(25);
				
				$lineplot = new LinePlot($jumlah3);

				$lineplot->SetColor('blue');

				#legend
				$graph->legend->SetPos(0.5, 0.87, 'left', 'bottom');
				$lineplot->SetLegend($blok);
				
				$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
				$lineplot->SetCenter();
				$graph->legend->SetShadow('gray@0.4', -10);
				$graph->Add($lineplot);	
				
				$lineplot->value->SetFormat('%d');
				$lineplot->value->Show();
				$lineplot->value->SetColor('#606060');
				
				$graph->StrokeCSIM();
				}
			
			break;
			
		case 'getGraph4':
			$whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
			//Get Tahun
			$sthn4 = selectQuery($dbname, 'kebun_rencanapanen_vw', "distinct(tahun) as tahun", "kodeblok in (".$whr.") group by tahun");
			$rthn4 = fetchData($sthn4);
			$arrthn4 = array();
			$arrthn4x = array();
			$not4 = -1;
			foreach ($rthn4 as $row) {
				$not4++;
				$arrthn4[$not4] = $row['tahun'];
				$arrthn4x[$not4] = substr($row['tahun'],2,2);
			}			
			
			if(count($arrthn4) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				//Get Jumlah Budget Produksi
				$sval = selectQuery($dbname, 'kebun_rencanapanen_vw', "kodeblok, sum(kgsensus) as jumlah, tahun as tahun", "kodeblok in (".$whr.") group by kodeblok, tahun");
				$rval = fetchData($sval);
				
				$jumlah4 = array();
				foreach ($arrthn4 as $thn=>$valthn) {
					if(!isset($jumlah4[$thn])){
						$jumlah4[$thn] = 0;
					}
					foreach ($rval as $val) {
						if ($val['tahun'] == $valthn) {
							$jumlah4[$thn] = (isset($val['jumlah']) ? $val['jumlah'] : 0);
						}
					}
				}
				
				if(count($arrthn4)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn4)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(80,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
				
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['produksi']."(".$_SESSION['lang']['sensus'].")".$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn4x);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$graph->yaxis->title->Set('Kg');
				$graph->yaxis->title->SetMargin(25);
				
				$lineplot = new LinePlot($jumlah4);

				$lineplot->SetColor('blue');

				#legend
				$graph->legend->SetPos(0.5, 0.87, 'left', 'bottom');
				$graph->legend->SetShadow('gray@0.4', -10);
				$lineplot->SetLegend($blok);

				$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
				$lineplot->SetCenter();
				$graph->Add($lineplot);	
				
				$lineplot->value->SetFormat('%d');
				$lineplot->value->Show();
				$lineplot->value->SetColor('#606060');
				
				$graph->StrokeCSIM();
				}
			
			break;
			
		case 'getGraph5':
			$whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
			
			//Get Tahun
			$sthn5 = selectQuery($dbname, 'bgt_produksi_kbn_vw', "distinct(tahunbudget) as tahun", "kodeblok in (".$whr.") group by tahunbudget");
			$rthn5 = fetchData($sthn5);
			$arrthn5 = array();
			$arrthn5x = array();
			$not5 = -1;
			foreach ($rthn5 as $row) {
				$not5++;
				$arrthn5[$not5] = $row['tahun'];
				$arrthn5x[$not5] = substr($row['tahun'],2,2);
			}

			if(count($arrthn5) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				//Get Jumlah Budget Produksi
				$sval = selectQuery($dbname, 'bgt_produksi_kbn_vw', "kodeblok, sum(jjgperpkk) as jumlah, tahunbudget as tahun", "kodeblok in (".$whr.") group by kodeblok, tahunbudget");
				$rval = fetchData($sval);
				
				$jumlah5 = array();
				foreach ($arrthn5 as $thn=>$valthn) {
					if(!isset($jumlah5[$thn])){
						$jumlah5[$thn] = 0;
					}
					foreach ($rval as $val) {
						if ($val['tahun'] == $valthn) {
							$jumlah5[$thn] = (isset($val['jumlah']) ? $val['jumlah'] : 0);
						}
					}
				}	
				
				if(count($arrthn5)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn5)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(55,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
								
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['produksi']."(".$_SESSION['lang']['budget'].")".$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn5x);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$lineplot = new LinePlot($jumlah5);

				$lineplot->SetColor('blue');

				#legend
				$graph->legend->SetPos(0.5, 0.87, 'left', 'bottom');
				$graph->legend->SetShadow('gray@0.4', -10);
				$lineplot->SetLegend($blok);

				$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
				$lineplot->SetCenter();
				$graph->Add($lineplot);	
				
				$lineplot->value->SetFormat('%d');
				$lineplot->value->Show();
				$lineplot->value->SetColor('#606060');
				
				$graph->StrokeCSIM();
				}
			
			break;
			
		case 'getGraph6':
			//Get Tahun
			$sthn6 = selectQuery($dbname, 'kebun_curahhujan', "distinct(substr(tanggal,1,4)) as tahun", "kodeorg like '".$blok."%' group by substr(tanggal,1,4)");
			$rthn6 = fetchData($sthn6);
			$arrthn6 = array();
			$arrthn6x = array();
			$not6 = -1;
			foreach ($rthn6 as $row) {
				$not6++;
				$arrthn6[$not6] = $row['tahun'];
				$arrthn6x[$not6] = substr($row['tahun'],2,2);
			}
			
			if(count($arrthn6) == 0){
				echo $_SESSION['lang']['datanotfound'];
			}else{
				//Get Jumlah Curah HUJAN
				$sval = selectQuery($dbname, 'kebun_curahhujan', "kodeorg, sum(pagi + sore + malam) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg like '".$blok."%' group by kodeorg, substr(tanggal,1,4)");
				$rval = fetchData($sval);
				
				$jumlah6 = array();
				foreach ($arrthn6 as $thn=>$valthn) {
					if(!isset($jumlah6[$thn])){
						$jumlah6[$thn] = 0;
					}
					foreach ($rval as $val) {
						if ($val['tahun'] == $valthn) {
							@$jumlah6[$thn] += (isset($val['jumlah']) ? $val['jumlah'] : 0);
						}
					}
				}
				
				if(count($arrthn6)<=5){
					$widthGraph = 540;
				}else{
					$widthGraph = 540 + ((count($arrthn6)-5)*108);
				}
				
				$graph = new Graph($widthGraph,500);
				$graph->img->SetMargin(80,40,40,80); 

				$graph->img->SetAntiAliasing();
				$graph->SetScale("textlin");
				
				$graph->SetShadow();
				$graph->title->Set($_SESSION['lang']['curahHujan'].$vTitle);
				$graph->title->SetFont(FF_DEFAULT,FS_NORMAL,14);

				$graph->xaxis->SetFont(FF_DEFAULT,FS_NORMAL,11);
				$graph->xaxis->SetTickLabels($arrthn6x);
				$graph->xaxis->SetLabelAngle(45);
				$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
				
				$graph->yaxis->title->Set('mm');
				$graph->yaxis->title->SetMargin(25);
				
				$lineplot = new LinePlot($jumlah6);

				$lineplot->SetColor('blue');

				#legend
				$graph->legend->SetPos(0.5, 0.87, 'left', 'bottom');
				$graph->legend->SetShadow('gray@0.4', -10);
				$lineplot->SetLegend($blok);

				$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
				$lineplot->SetCenter();
				$graph->Add($lineplot);	
				
				$lineplot->value->SetFormat('%d');
				$lineplot->value->Show();
				$lineplot->value->SetColor('#606060');
				
				$graph->StrokeCSIM();
				}
			
			break;
			
		
		
		
		
		
		
		
		
		
		
		
		
		
		// case 'loaddata':
		// /*==================
		// =-AKTUAL PEMUPUKAN-=
		// ==================*/
		// $optNamaPupuk = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
		// $whr = "select kodeorg from ".$dbname.".setup_blok where kodeorg like '".$blok."%' and tahuntanam like '%".$tahuntanam."%'";
		// //Get Tahun
		// $sthn = selectQuery($dbname, 'log_transaksi_vw', "distinct(substr(tanggal,1,4)) as tahun", "kodebarang like '311%' and tipetransaksi=5 and kodeblok like '".$blok."%' and kodeblok in (".$whr.") group by substr(tanggal,1,4)");
		// $rthn = fetchData($sthn);
		
		// $arrthn = array();
		// $arrthnx = array();
		// $not = -1;
		// foreach ($rthn as $row) {
            // $not++;
			// $arrthn[$not] = $row['tahun'];
			// $arrthnx[$not] = substr($row['tahun'],2,2);
        // }
		// $countData = count($arrthn);
		
		// // print_r($sthn);
		
		// if($countData <= 0){
			// $tab.=$_SESSION['lang']['datanotfound'];
		// }else{
			// $sbrg = selectQuery($dbname, 'log_transaksi_vw', "kodebarang", "kodebarang like '311%' and tipetransaksi=5 and kodeblok like '".$blok."%' and kodeblok in (".$whr.") group by kodebarang");
			// $rbrg = fetchData($sbrg);
			// $optPupuk = array();
			// foreach ($rbrg as $row) {
				// $optPupuk[$row['kodebarang']] = $row['kodebarang'];
			// }
			
			// $sval = selectQuery($dbname, 'log_transaksi_vw', "kodebarang, sum(jumlah) as jumlah, substr(tanggal,1,4) as tahun", "kodebarang like '311%' and tipetransaksi=5 and kodeblok like '".$blok."%' and kodeblok in (".$whr.") group by kodebarang, substr(tanggal,1,4)");
			// $rval = fetchData($sval);
			
			// $jumlah = array();
			// foreach ($arrthn as $thn=>$valthn) {
				// foreach ($optPupuk as $brg) {
					// $jumlah[$brg][$thn] = 0;
					// foreach ($rval as $val) {
						// if ($val['tahun'] == $valthn && $val['kodebarang'] == $brg) {
							// $jumlah[$brg][$thn] = $val['jumlah'];
						// }
					// }
				// }
			// }
			
			// //List table Aktual Pemupukan
			// $tab.="<table class=sortable cellspacing=1 border=0>
						// <thead>
						// <tr>
							// <td align=center>".$_SESSION['lang']['jenisPupuk']."</td>";
							// foreach($arrthn as $val){
								// $tab.="<td align=center>".$val."</td>";
							// }
						// $tab.="</tr>
						// </thead>
						// <tbody>";
			// foreach($rbrg as $row1){
				// $tab.="<tr class=rowcontent>
					// <td>".$optNamaPupuk[$row1['kodebarang']]."</td>";
					
					// foreach($arrthn as $thn=>$valthn){
						// $tab.="<td align=right>".number_format($jumlah[$row1['kodebarang']][$thn],2)."</td>";
					// }
					
				// $tab.="</tr>";
			// }

			// $tab.="</tbody>
					// </table>";
		// }
		
		// //======================================================================================//
		
		// /*===================
		// =-REKOMENDASI PUPUK-=
		// ===================*/
		// //Get Tahun
		// $sthn2 = selectQuery($dbname, 'kebun_rekomendasipupuk', "distinct(substr(periodepemupukan,1,4)) as tahun", "kodebarang like '311%' and blok in (".$whr.") group by substr(periodepemupukan,1,4)");
		// $rthn2 = fetchData($sthn2);
		// $arrthn2 = array();
		// $arrthn2x = array();
		// $not2 = -1;
		// foreach ($rthn2 as $row) {
            // $not2++;
			// $arrthn2[$not2] = $row['tahun'];
			// $arrthn2x[$not2] = substr($row['tahun'],2,2);
        // }
		// $countData2 = count($arrthn2);
		
		// //Get Jumlah Pokok
		// $sPokok = selectQuery($dbname, 'setup_blok_tahunan', "jumlahpokok as jumlahpokok, kodeorg, substr(tahun,1,4) as tahun", "kodeorg in (".$whr.") and tahun in (select max(tahun) from ".$dbname.".setup_blok_tahunan group by substr(tahun,1,4))");
		// $rPokok = fetchData($sPokok);
		// $optPokok = array();
		// foreach ($rPokok as $row) {
			// $optPokok[$row['tahun']][$row['kodeorg']] = $row['jumlahpokok'];
		// }
				
		// if($countData2 <= 0){
			// $tab2.=$_SESSION['lang']['datanotfound'];
		// }else{
			// $sbrg2 = selectQuery($dbname, 'log_5masterbarang', "kodebarang", "kodebarang like '311%' and inactive=0 group by kodebarang");
			// $rbrg2 = fetchData($sbrg2);
			// $optPupuk2 = array();
			// foreach ($rbrg2 as $row) {
				// $optPupuk2[$row['kodebarang']] = $row['kodebarang'];
			// }
			
			// $sval = selectQuery($dbname, 'kebun_rekomendasipupuk', "kodebarang, sum(dosis) as jumlah, substr(periodepemupukan,1,4) as tahun, blok", "kodebarang like '311%' and blok in (".$whr.") group by kodebarang, blok,substr(periodepemupukan,1,4)");
			// // print_r($sval);
			// $rval = fetchData($sval);

			// $jumlah2 = array();
			// foreach ($arrthn2 as $thn=>$valthn) {
				// foreach ($optPupuk2 as $brg) {
					// if(!isset($jumlah2[$brg][$thn])){
						// $jumlah2[$brg][$thn]=0;
					// }
					// foreach($rPokok as $valPokok){
						// foreach ($rval as $val) {
							// if($val['tahun'] == $valthn && $val['kodebarang'] == $brg && $valPokok['kodeorg'] == $val['blok']){
								// $jumlah2[$brg][$thn] += ($val['jumlah'] * $valPokok['jumlahpokok']);
							// }
						// }
					// }
				// }
			// }
			
			// //List table Aktual Pemupukan
			// $tab2.="<table class=sortable cellspacing=1 border=0>
						// <thead>
						// <tr>
							// <td align=center>".$_SESSION['lang']['jenisPupuk']."</td>";
							// foreach($arrthn2 as $val){
								// $tab2.="<td align=center>".$val."</td>";
							// }
						// $tab2.="</tr>
						// </thead>
						// <tbody>";
			// foreach($rbrg2 as $row1){
				// $tab2.="<tr class=rowcontent>
					// <td>".$optNamaPupuk[$row1['kodebarang']]."</td>";
					
					// foreach($arrthn2 as $thn=>$valthn){
						// $tab2.="<td align=right>".number_format($jumlah2[$row1['kodebarang']][$thn],2)."</td>";
					// }
					
				// $tab2.="</tr>";
			// }

			// $tab2.="</tbody>
					// </table>";
		// }
		
		// /*=================
		// =-AKTUAL PRODUKSI-=
		// =================*/
		// //Get Tahun
		// $sthn3 = selectQuery($dbname, 'kebun_spb_vw', "distinct(substr(tanggal,1,4)) as tahun", "blok in (".$whr.") group by substr(tanggal,1,4)");
		// $rthn3 = fetchData($sthn3);
		// $arrthn3 = array();
		// $arrthn3x = array();
		// $not3 = -1;
		// foreach ($rthn3 as $row) {
            // $not3++;
			// $arrthn3[$not3] = $row['tahun'];
			// $arrthn3x[$not3] = substr($row['tahun'],2,2);
        // }
		
		// $tab3="";
		// if(count($arrthn3) == 0){
			// $tab3.= $_SESSION['lang']['datanotfound'];
		// }else{
			// //Get Jumlah KgWb
			// $sval = selectQuery($dbname, 'kebun_spb_vw', "blok, sum(kgwb) as jumlah, substr(tanggal,1,4) as tahun", "blok in (".$whr.") group by blok, substr(tanggal,1,4)");
			// $rval = fetchData($sval);
			
			// $jumlah3 = array();
			// foreach ($arrthn3 as $thn=>$valthn) {
				// if(!isset($jumlah3[$thn])){
					// $jumlah3[$thn] = 0;
				// }
				// foreach ($rval as $val) {
					// if ($val['tahun'] == $valthn) {
						// $jumlah3[$thn] += $val['jumlah'];
					// }
				// }
			// }
			
			// $tab3="";
			// $tab3.="<table class=sortable cellspacing=1 border=0>
				// <thead>
				// <tr>
					// <td align=center>".$_SESSION['lang']['kodeorg']."</td>";
					// foreach($arrthn3 as $val){
						// $tab3.="<td align=center>".$val."</td>";
					// }
			// $tab3.="</tr></thead><tbody>";
			// $tab3.="<tr class=rowcontent>
				// <td>".$blok."</td>";
					
				// foreach($arrthn3 as $thn=>$valthn){
					// $tab3.="<td align=right>".number_format($jumlah3[$thn],2)."</td>";
				// }
			// $tab3.="</tr></tbody>";
		// }
		
		// /*=================
		// =-SENSUS PRODUKSI-=
		// =================*/
		// //Get Tahun
		// $sthn4 = selectQuery($dbname, 'kebun_rencanapanen_vw', "distinct(tahun) as tahun", "kodeblok in (".$whr.") group by tahun");
		// $rthn4 = fetchData($sthn4);
		// $arrthn4 = array();
		// $arrthn4x = array();
		// $not4 = -1;
		// foreach ($rthn4 as $row) {
            // $not4++;
			// $arrthn4[$not4] = $row['tahun'];
			// $arrthn4x[$not4] = substr($row['tahun'],2,2);
        // }
		
		// $tab4="";
		// if(count($arrthn4) == 0){
			// $tab4.=$_SESSION['lang']['datanotfound'];
		// }else{
			// //Get Jumlah Budget Produksi
			// $sval = selectQuery($dbname, 'kebun_rencanapanen_vw', "kodeblok, sum(kgsensus) as jumlah, tahun as tahun", "kodeblok in (".$whr.") group by kodeblok, tahun");
			// $rval = fetchData($sval);
			
			// $jumlah4 = array();
			// foreach ($arrthn4 as $thn=>$valthn) {
				// if(!isset($jumlah4[$thn])){
					// $jumlah4[$thn] = 0;
				// }
				// foreach ($rval as $val) {
					// if ($val['tahun'] == $valthn) {
						// $jumlah4[$thn] += $val['jumlah'];
					// }
				// }
			// }
			
			// $tab4.="<table class=sortable cellspacing=1 border=0>
				// <thead>
				// <tr>
					// <td align=center>".$_SESSION['lang']['kodeorg']."</td>";
					// foreach($arrthn4 as $val){
						// $tab4.="<td align=center>".$val."</td>";
					// }
			// $tab4.="</tr></thead><tbody>";
			// $tab4.="<tr class=rowcontent>
				// <td>".$blok."</td>";
					
				// foreach($arrthn4 as $thn=>$valthn){
					// $tab4.="<td align=right>".number_format($jumlah4[$thn],2)."</td>";
				// }
			// $tab4.="</tr></tbody>";
		// }
		
		
		// /*=================
		// =-BUDGET PRODUKSI-=
		// =================*/
		// //Get Tahun
		// $sthn5 = selectQuery($dbname, 'bgt_produksi_kbn_vw', "distinct(tahunbudget) as tahun", "kodeblok in (".$whr.") group by tahunbudget");
		// $rthn5 = fetchData($sthn5);
		// $arrthn5 = array();
		// $arrthn5x = array();
		// $not5 = -1;
		// foreach ($rthn5 as $row) {
            // $not5++;
			// $arrthn5[$not5] = $row['tahun'];
			// $arrthn5x[$not5] = substr($row['tahun'],2,2);
        // }
		
		// $tab5="";
		// if(count($arrthn5) == 0){
			// $tab5.=$_SESSION['lang']['datanotfound'];
		// }else{
			// //Get Jumlah Budget Produksi
			// $sval = selectQuery($dbname, 'bgt_produksi_kbn_vw', "kodeblok, sum(jjgperpkk) as jumlah, tahunbudget as tahun", "kodeblok in (".$whr.") group by kodeblok, tahunbudget");
			// $rval = fetchData($sval);
			
			// $jumlah5 = array();
			// foreach ($arrthn5 as $thn=>$valthn) {
				// if(!isset($jumlah5[$thn])){
					// $jumlah5[$blok][$thn] = 0;
				// }
				// foreach ($rval as $val) {
					// if ($val['tahun'] == $valthn) {
						// $jumlah5[$thn] = $val['jumlah'];
					// }
				// }
			// }
			
			// $tab5.="<table class=sortable cellspacing=1 border=0>
				// <thead>
				// <tr>
					// <td align=center>".$_SESSION['lang']['kodeorg']."</td>";
					// foreach($arrthn5 as $val){
						// $tab5.="<td align=center>".$val."</td>";
					// }
			// $tab5.="</tr></thead><tbody>";
			// $tab5.="<tr class=rowcontent>
				// <td>".$blok."</td>";
					
				// foreach($arrthn5 as $thn=>$valthn){
					// $tab5.="<td align=right>".number_format($jumlah5[$thn],2)."</td>";
				// }
			// $tab5.="</tr></tbody>";
		// }
		
		
		// /*=============
		// =-CURAH HUJAN-=
		// =============*/
		// //Get Tahun
		// $sthn6 = selectQuery($dbname, 'kebun_curahhujan', "distinct(substr(tanggal,1,4)) as tahun", "kodeorg like '".$blok."%' group by substr(tanggal,1,4)");
		// $rthn6 = fetchData($sthn6);
		// $arrthn6 = array();
		// $arrthn6x = array();
		// $not6 = -1;
		// foreach ($rthn6 as $row) {
            // $not6++;
			// $arrthn6[$not6] = $row['tahun'];
			// $arrthn6x[$not6] = substr($row['tahun'],2,2);
        // }
		
		// if(count($arrthn6) == 0){
			// $tab6.=$_SESSION['lang']['datanotfound'];
		// }else{
			// //Get Jumlah Curah HUJAN
			// $sval = selectQuery($dbname, 'kebun_curahhujan', "kodeorg, sum(pagi + sore + malam) as jumlah, substr(tanggal,1,4) as tahun", "kodeorg like '".$blok."%' group by kodeorg, substr(tanggal,1,4)");
			// $rval = fetchData($sval);
			
			// $jumlah6 = array();
			// foreach ($arrthn6 as $thn=>$valthn) {
				// if(!isset($jumlah6[$thn])){
					// $jumlah6[$thn] = 0;
				// }
				// foreach ($rval as $val) {
					// if ($val['tahun'] == $valthn) {
						// $jumlah6[$thn] += $val['jumlah'];
					// }
				// }
			// }
			
			// $tab6="";
			// $tab6.="<table class=sortable cellspacing=1 border=0>
				// <thead>
				// <tr>
					// <td align=center>".$_SESSION['lang']['kodeorg']."</td>";
					// foreach($arrthn6 as $val){
						// $tab6.="<td align=center>".$val."</td>";
					// }
			// $tab6.="</tr></thead><tbody>";
			// $tab6.="<tr class=rowcontent>
				// <td>".$blok."</td>";
					
				// foreach($arrthn6 as $thn=>$valthn){
					// $tab6.="<td align=right>".$jumlah6[$thn]."</td>";
				// }
			// $tab6.="</tr></tbody>";
		// }
		
		// //======================================================================================//
		// echo $tab."####".$tab2."####".$tab3."####".$tab4."####".$tab5."####".$tab6;
		// break;
}

?>
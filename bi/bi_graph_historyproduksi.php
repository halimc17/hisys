<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../lib/zLib.php');
include ('../jpgraph/jpgraph.php');
include ('../jpgraph/jpgraph_line.php'); 

 
$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$id = checkPostGet('id','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($method)
{
	case'detailgraph':
		// $stylehidden = "style='display:none'";	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select distinct(tahuntanam) as tahuntanam from ".$dbname.".kebun_spb_vw 
							where tanggal like '".$thn."%' order by tahuntanam asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$thntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			
		}
		if(empty($thntnm)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		@$jthntnm=count($thntnm);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['tahun']."</td>
						<td   align=center colspan=".($jthntnm*3).">".$_SESSION['lang']['tahuntanam']."</td>
					</tr><tr>";
					foreach($thntnm as $listthntnm){
						$form.="<td  align=center colspan=3>".$listthntnm."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=$jthntnm;$i++){
						$form.="
						<td align=center>".$_SESSION['lang']['bgt']."</td>
						<td align=center>".$_SESSION['lang']['real']."</td>
						<td align=center>".$_SESSION['lang']['penc']."</td>
						
						";
					}
					$form.="</tr>";
			$form.="</tr>
				</thead>
				";
		
		
		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumunit[$bar['induk']]+=1;
		}

		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where tipe='AFDELING' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumdivisi[$bar['induk']]+=1;
		}	
		
		
		#perpt
		$str="select (a.kgwb/1000) as kgwb,a.tanggal,b.induk,a.tahuntanam,a.kodeorg,a.divisi 
				from ".$dbname.".kebun_spb_vw a left join ".$dbname.".organisasi b 
				on a.kodeorg=b.kodeorganisasi where a.tanggal like '".$thn."%' order by divisi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$prodpt[$bar['induk']][$bar['tahuntanam']]+=$bar['kgwb'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['tahuntanam']]+=$bar['kgwb'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahuntanam']]+=$bar['kgwb'];
			$thntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$prodtot[$bar['tahuntanam']]+=$bar['kgwb'];
		}
				
		
		//print_r($jumunit);
		
		$str=" select (a.kgsetahun/1000) as kgsetahun,a.tahunbudget,a.thntnm,b.induk,a.kodeunit,a.divisi
						 from ".$dbname.".bgt_produksi_kbn_kg_vw a left join ".$dbname.".organisasi b
						 on a.kodeunit=b.kodeorganisasi where  tahunbudget='".$thn."'  order by divisi asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeunit']]=$bar['kodeunit'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeunit']]=$bar['kodeunit'];
			$listkddivisi[$bar['induk']][$bar['kodeunit']][$bar['divisi']]=$bar['divisi'];
			$thntanam[$bar['thntnm']]=$bar['thntnm'];
			@$bgtpt[$bar['induk']][$bar['thntnm']]+=$bar['kgsetahun'];
			@$bgtunit[$bar['induk']][$bar['kodeunit']][$bar['thntnm']]+=$bar['kgsetahun'];
			@$bgtdivisi[$bar['induk']][$bar['kodeunit']][$bar['divisi']][$bar['thntnm']]+=$bar['kgsetahun'];
			@$bgttot[$bar['thntnm']]+=$bar['kgsetahun'];
		}
			

		
			
				
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>
					<td align=center>".$thn."</td>";
			foreach($thntnm as $tt)
			{
				$form.="		
					<td align=right>".@number_format($bgtpt[$pt][$tt])."</td>
					<td align=right>".@number_format($prodpt[$pt][$tt])."</td>
					<td align=right>".@number_format($prodpt[$pt][$tt]/$bgtpt[$pt][$tt]*100)."</td>";
			}
			$form.="
				</tr>
			";
			$urutunitlist=0;
				foreach($kodeunit as $unit){
					if(@$listkodeunit[$pt][$unit]==$unit){
						@$urutunit+=1;
						$urutunitlist++;
						$form.="
						<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".$jumdivisi[$unit]."')\">
							<td>".$no.".".$urutunitlist."</td>
							<td>".$unit."</td>
						<td align=center>".$thn."</td>";
						foreach($thntnm as $tt){
							$form.="		
								<td align=right>".@number_format($bgtunit[$pt][$unit][$tt])."</td>
								<td align=right>".@number_format($produnit[$pt][$unit][$tt])."</td>
								<td align=right>".@number_format($produnit[$pt][$unit][$tt]/$bgtunit[$pt][$unit][$tt]*100)."</td>
								";
						}
					$form.="</tr>";	
					$urutdivisilist=0;
					foreach($kddivisi as $divisi){
						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
							$urutdivisilist++;
							$form.="
							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
								<td>".$no.".".$urutunitlist.".".$urutdivisilist."</td>
								<td align=right>".$divisi."</td>
								<td align=center>".$thn."</td>";
								foreach($thntnm as $tt){
									$form.="		
										<td align=right>".@number_format($bgtdivisi[$pt][$unit][$divisi][$tt])."</td>
										<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$tt])."</td>
										<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$tt]/$bgtdivisi[$pt][$unit][$divisi][$tt]*100)."</td>";
								}
							$form.="</tr>";	
							
						}
					}
				}
			}
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=3 align=center><b>Total</td>";
			foreach($thntnm as $tt)
			{
				$form.="		
					<td align=right><b>".@number_format($bgttot[$tt])."</td>
					<td align=right><b>".@number_format($prodtot[$tt])."</td>
					<td align=right><b>".@number_format($prodtot[$tt]/$bgttot[$tt]*100)."</td>";
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	case'detail2produksi':
		
		$arrthn = array();
		 $not = -1;
		$str="select distinct(tahuntanam) as tahuntanam from ".$dbname.".kebun_spb_vw 
				where tanggal like  '".$thn."%' and kodeorg='".$unit."'
				order by tahuntanam asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahuntanam'];
        }
		   
		$str="select distinct(divisi) as divisi from ".$dbname.".kebun_spb_vw 
				where tanggal like  '".$thn."%' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."') ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['divisi']] = $row['divisi'];
        }
		
		$str="select sum(kgwb/1000) as kgwb,tahuntanam,divisi from ".$dbname.".kebun_spb_vw 
				where tanggal like  '".$thn."%' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."')
				group by tahuntanam,divisi";		
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahuntanam'] && $row1 == $row2['divisi']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['produksi'].' '.$_SESSION['lang']['unit'].' '.$unit);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = 0;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
            $no += 1;
            $resultColor = randomColor();
            if ($row['divisi'] == $optunit[$row['divisi']]) {
                $list[$no] = new LinePlot($kgwb[$row['divisi']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['divisi']);
				$list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				
				
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";  
		
	break;
	
	
	
	
	case'detail2budget':
	
		$arrthn = array();
		$not=-1;
		$str="select distinct(thntnm) as thntnm from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit='".$unit."'
				order by thntnm asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['thntnm'];
        }
		   
		$str="select distinct(divisi) as divisi from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."') ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['divisi']] = $row['divisi'];
        }
		
		$str="select sum(kgsetahun/1000) as kgwb,thntnm,divisi from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."')
				group by thntnm,divisi";		
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['thntnm'] && $row1 == $row2['divisi']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['budget'].' '.$_SESSION['lang']['unit'].' '.$unit);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = 0;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
            $no += 1;
            $resultColor = randomColor();
            if ($row['divisi'] == $optunit[$row['divisi']]) {
                $list[$no] = new LinePlot($kgwb[$row['divisi']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['divisi']);
				$list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				
				
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";  
	
	break;
	
	
	
	case'detail1budget':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}
	
		$arrthn = array();
		$not=-1;
		$str="select distinct(thntnm) as thntnm from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				order by thntnm asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['thntnm'];
        }
		   
		$str="select distinct(kodeunit) as kodeunit from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeunit']] = $row['kodeunit'];
        }
		
		$str="select sum(kgsetahun/1000) as kgwb,thntnm,kodeunit from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by thntnm,kodeunit";		
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['thntnm'] && $row1 == $row2['kodeunit']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['budget'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
		
            $resultColor = randomColor();
            if ($row['kodeunit'] == $optunit[$row['kodeunit']]) {
					$no ++ ;
                $list[$no] = new LinePlot($kgwb[$row['kodeunit']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['kodeunit']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				
				$targ[$no]='?method=detail2budget&unit='.$row['kodeunit'].'&thn='.$thn;
				$alts[$no]='Click to Drill Divisi Budget '.$row['kodeunit'];
				$list[$no]->SetCSIMTargets($targ,$alts); 
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	case'detail1produksi':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}
	
		$arrthn = array();
		$str="select distinct(tahuntanam) as tahuntanam from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				order by tahuntanam asc";
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahuntanam'];
        }
		   
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(kgwb/1000) as kgwb,tahuntanam,kodeorg from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by tahuntanam,kodeorg";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahuntanam'] && $row1 == $row2['kodeorg']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		// echo"<pre>";
		// print_r($kgwb);
		// echo"</pre>";
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['produksi'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
            $no ++;
            $resultColor = randomColor();
            if ($row['kodeorg'] == $optunit[$row['kodeorg']]) {
                $list[$no] = new LinePlot($kgwb[$row['kodeorg']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['kodeorg']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				$targ[$no]='?method=detail2produksi&unit='.$row['kodeorg'].'&thn='.$thn;
				$alts[$no]='Click to Drill Divisi Produksi '.$row['kodeorg'];
				$list[$no]->SetCSIMTargets($targ,$alts); 
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	case'global':
		$sortprod=$sortbgt='';
		if($pt!='')
		{
			$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$sortbgt=" and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		}
		
	
		$no=$nobgt=-1;
		$str=" select sum(kgwb/1000) as kgwb,tahuntanam from ".$dbname.".kebun_spb_vw 
							where tanggal like '".$thn."%' ".$sortprod."  group by tahuntanam";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$arrthn[$no]=$bar['tahuntanam'];
			$ydata[$no]=$bar['kgwb'];
			$targ[$no]='?method=detail1produksi&pt='.$pt.'&thn='.$thn;
			$alts[$no]='Click to Drill Produksi';
			
		}
	
		
		$str=" select sum(kgsetahun/1000) as kgsetahun,tahunbudget,thntnm
						 from ".$dbname.".bgt_produksi_kbn_kg_vw 
						 where  tahunbudget='".$thn."' ".$sortbgt."  group by thntnm ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$ydata2[0]=0;
		}
		while($bar=$res->fetch()){
			$nobgt++;
			$arrthn[$nobgt]=$bar['thntnm'];
			$ydata2[$nobgt]=$bar['kgsetahun'];
			$targ2[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn;
			$alts2[$nobgt]='Click to Drill Budget';
		}

		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		// echo"<pre>";
		// print_r($ydata);
		// echo"</pre>";
		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		if($pt==''){
			$graph->title->Set($_SESSION['lang']['seluruhpt']);
		}
		else{
			$graph->title->Set($_SESSION['lang']['pt'].' '.$pt);
		}
		
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(45);
		 
		 
		$lineplot=new LinePlot($ydata);
		//$lineplot->SetColor( 'green' );
		$lineplot->SetWeight( 20 );   // Two pixel wide
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['produksi']);
		$lineplot->SetCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');

		$lineplot2=new LinePlot($ydata2);
		$lineplot2->SetWeight( 10 );   // Two pixel wide
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot2->mark->SetColor('red');
		$lineplot2->mark->SetFillColor('green');
		$lineplot2->SetLegend($_SESSION['lang']['budget']);
		$lineplot2->SetCenter();
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('red');
		 
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
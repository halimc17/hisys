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
$pks = checkPostGet('pks','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($method)
{
	case'detailgraph':
		// $stylehidden = "style='display:none'";	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select distinct(tahuntanam) as tahuntanam from ".$dbname.".setup_blok  order by tahuntanam asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$thntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			@$jthntnm+=1;
		}
		
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=2  align=center>No</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".$jthntnm.">".$_SESSION['lang']['tahuntanam']."</td>
					</tr><tr>";
					foreach($thntnm as $listthntnm){
						$form.="<td  align=center>".$listthntnm."</td>";
					}
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
		$str="select a.jumlahpokok,a.tahuntanam,b.induk,left(a.kodeorg,4) as kodeorg,left(a.kodeorg,6) as divisi 
				from ".$dbname.".setup_blok a left join ".$dbname.".organisasi b 
				on left(a.kodeorg,4)=b.kodeorganisasi order by divisi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$prodpt[$bar['induk']][$bar['tahuntanam']]+=$bar['jumlahpokok'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['tahuntanam']]+=$bar['jumlahpokok'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahuntanam']]+=$bar['jumlahpokok'];
			@$prodtot[$bar['tahuntanam']]+=$bar['jumlahpokok'];
		}
				
		
		//print_r($jumunit);
		
	
			


			
				
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".@$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>";
			foreach($thntnm as $tt){
				$form.="<td align=right>".@number_format($prodpt[$pt][$tt])."</td>";
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
						<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".@$urutunit."','".@$jumdivisi[$unit]."')\">
							<td>".$no.".".@$urutunitlist."</td>
							<td>".$unit."</td>
						";
						foreach($thntnm as $tt){
							$form.="<td align=right>".@number_format($produnit[$pt][$unit][$tt])."</td>";
						}
					$form.="</tr>";	
					$urutdivisilist=0;
					foreach($kddivisi as $divisi){
						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
							@$urutdivisi+=1;
							$urutdivisilist++;
							$form.="
							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
								<td>".$no.".".$urutunit.".".$urutdivisilist."</td>
								<td>".$divisi."</td>";
								foreach($thntnm as $tt){
									$form.="<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$tt])."</td>";
								}
							$form.="</tr>";	
							
						}
					}
				}
			}
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($thntnm as $tt){
				$form.="		
					<td align=right><b>".@number_format($prodtot[$tt])."</td>";
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	case'detail2produksi':
		
		$arrthn = array();
		$str="select distinct(tahuntanam) as tahuntanam from ".$dbname.".kebun_spb_vw 
				where tanggal like  '".$thn."%' and kodeorg='".$unit."'
				order by tahuntanam asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
        $not = -1;
        while ($bar =$res->fetch()) {
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

		
		$graph->title->Set('Produksi Divisi Unit '.$unit);
		$graph->subtitle->Set('Tahun '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("Tahun Tanam");
		$graph->yaxis->title->Set("TON");
		
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
		$str="select distinct(thntnm) as thntnm from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit='".$unit."'
				order by thntnm asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);        
        $not = -1;
        while ($bar = $res->fetch()) {
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

		
		$graph->title->Set('Budget Divisi Unit '.$unit);
		$graph->subtitle->Set('Tahun '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("Tahun Tanam");
		$graph->yaxis->title->Set("TON");
		
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
	
		// if($pt=='')
		// {
			// echo"Pilih salah satu PT";
			// echo"<br><br><a href=javascript:history.back(-1)>Back</a>"; 
			// exit();
		// }
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
		}
		else{
			$sortpt=" tipe='KEBUN' ";
		}
	
		$arrthn = array();
		$str="select distinct(thntnm) as thntnm from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				order by thntnm asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);         
        $not = -1;
        while ($bar = $res->fetch()) {
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

		
		$graph->title->Set('Budget Unit PT. '.$pt);
		$graph->subtitle->Set('Tahun '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("Tahun Tanam");
		$graph->yaxis->title->Set("TON");
		
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
	
		// if($pt=='')
		// {
			// echo"Pilih salah satu PT";
			// echo"<br><br><a href=javascript:history.back(-1)>Back</a>"; 
			// exit();
		// }
		
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
		}
		else{
			$sortpt=" tipe='KEBUN' ";
		}
	
		$arrthn = array();
		$str="select distinct(tahuntanam) as tahuntanam from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				order by tahuntanam asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);         
        $not = -1;
        while ($bar = $res->fetch()) {
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

		
		$graph->title->Set('Produksi Unit PT. '.$pt);
		$graph->subtitle->Set('Tahun '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("Tahun Tanam");
		$graph->yaxis->title->Set("TON");
		
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
		$sort='';
		if($pt!=''){
			$sort=" and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		/*$str=" select sum(kwantitas/1000) as kwantitas,left(tanggal,4) as tahun from ".$dbname.".kebun_pakai_material_vw 
							where kodebarang like '311%' and left(tanggal,4) <= '".$thn."' ".$sortppk."  group by tahun";	*/
		$noppk=$noprod=$nosen=-1;
		$str=" select sum(jumlahpokok/1000) as jumlahpokok,tahuntanam from ".$dbname.".setup_blok 
							where 1=1 and tahuntanam>0 and tahuntanam <= '".$thn."' ".$sort."  group by tahuntanam";							
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noppk++;
			$arrthn[$noppk]=$bar['tahuntanam'];
			$ydata[$noppk]=$bar['jumlahpokok'];
			$targ[$noppk]='?method=detail1pokok&pt='.$pt.'&thn='.$thn;
			$alts[$noppk]='Click to Drill Pokok';
		}
	
		if($ydata==''){
			$ydata[0]=0;
		}
		
		if($arrthn<1){
			echo $_SESSION['lang']['dataempty'];exit();
		}

		// echo"<pre>";
		// print_r($ydata);
		// echo"</pre>";
		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		
		
		$graph->title->Set($judul);
		
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(15);
		$graph->xaxis->title->SetMargin(10);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['jumlahpokok']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(60);


		 
		 
		$lineplot=new LinePlot($ydata);
		//$lineplot->SetColor( 'green' );
		$lineplot->SetWeight( 20 );   // Two pixel wide
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['pemakaianpupuk']);
		
		$lineplot->SetCenter();
		//$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		//$graph->legend->SetFrameWeight(1);
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
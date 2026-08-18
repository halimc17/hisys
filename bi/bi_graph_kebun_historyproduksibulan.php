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
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:200% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['tahun']."</td>
						<td align=center colspan=36>".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					for($i=1;$i<=12;$i++){
						$form.="<td  align=center colspan=3>".numToMonth($i,'I','long')."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=12;$i++){
						$form.="
						<td align=center>".$_SESSION['lang']['bgt']."</td>
						<td align=center>".$_SESSION['lang']['real']."</td>
						<td align=center>".$_SESSION['lang']['penc']."</td>
						";
					}
			$form.="</tr>
				</thead>
				";
				
				
		$str="select (a.kgwb/1000) as kgwb,substr(tanggal,1,7) as bulan,b.induk,a.tahuntanam,a.kodeorg,a.divisi 
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
			@$prodpt[$bar['induk']][$bar['bulan']]+=$bar['kgwb'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['bulan']]+=$bar['kgwb'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['bulan']]+=$bar['kgwb'];
			@$prodtot[$bar['bulan']]+=$bar['kgwb'];
		}
		
		
		
		// $str=" select (a.kgsetahun/1000) as kgsetahun,a.tahunbudget,a.thntnm,b.induk,a.kodeunit,a.divisi
				 // from ".$dbname.".bgt_produksi_kbn_kg_vw a left join ".$dbname.".organisasi b
				 // on a.kodeunit=b.kodeorganisasi where  tahunbudget='".$thn."'  order by divisi asc ";
		
		$str=" select (a.kg01/1000) as kg01,(a.kg02/1000) as kg02,(a.kg03/1000) as kg03,(a.kg04/1000) as kg04,
						(a.kg05/1000) as kg05,(a.kg06/1000) as kg06,(a.kg07/1000) as kg07,(a.kg08/1000) as kg08,
						(a.kg09/1000) as kg09,(a.kg10/1000) as kg10,(a.kg11/1000) as kg11,(a.kg12/1000) as kg12,
					  a.tahunbudget,a.kodeunit,a.divisi,b.induk
				from ".$dbname.".bgt_produksi_kbn_kg_vw a  left join ".$dbname.".organisasi b
				 on a.kodeunit=b.kodeorganisasi  where  a.tahunbudget='".$thn."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			for($i=1;$i<=12;$i++){
				if($i<10){
					@$bgtpt[$bar['induk']][$bar['tahunbudget'].'-0'.$i]+=$bar['kg0'.$i];
					@$bgtunit[$bar['induk']][$bar['kodeunit']][$bar['tahunbudget'].'-0'.$i]+=$bar['kg0'.$i];
					@$bgtdivisi[$bar['induk']][$bar['kodeunit']][$bar['divisi']][$bar['tahunbudget'].'-0'.$i]+=$bar['kg0'.$i];
					@$bgttot[$bar['tahunbudget'].'-0'.$i]+=$bar['kg0'.$i];
				}
				else{
					@$bgtpt[$bar['induk']][$bar['tahunbudget'].'-'.$i]+=$bar['kg'.$i];
					@$bgtunit[$bar['induk']][$bar['kodeunit']][$bar['tahunbudget'].'-'.$i]+=$bar['kg'.$i];
					@$bgtdivisi[$bar['induk']][$bar['kodeunit']][$bar['divisi']][$bar['tahunbudget'].'-'.$i]+=$bar['kg'.$i];
					@$bgttot[$bar['tahunbudget'].'-'.$i]+=$bar['kg'.$i];
				}
			}
		}
			 
		
		
		
		
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
			
		$arrlistbln=month_inbetween($thn.'-01',$thn.'-12');
		
		if(empty($kodept)){
			echo $_SESSION['lang']['dataempty'];exit();
		}

		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>
					<td align=center>".$thn."</td>";
					foreach($arrlistbln as $bln){
						$form.="		
						<td align=right>".@number_format($bgtpt[$pt][$bln])."</td>
						<td align=right>".@number_format($prodpt[$pt][$bln])."</td>
						<td align=right>".@number_format($prodpt[$pt][$bln]/$bgtpt[$pt][$bln]*100)."</td>";
					}
					$form.="</tr>";
					$urutunitlist=0;
					foreach($kodeunit as $unit){
						if(@$listkodeunit[$pt][$unit]==$unit){
							@$urutunit+=1;
							$urutunitlist++;
							$form.="
							<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".$jumdivisi[$unit]."')\">
								<td>".$no.".".$urutunitlist."</td>
								<td>".$unit."</td>
									<td align=center>".$thn."</td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
									foreach($arrlistbln as $bln){
										$form.="		
											<td align=right>".@number_format($bgtunit[$pt][$unit][$bln])."</td>
											<td align=right>".@number_format($produnit[$pt][$unit][$bln])."</td>
											<td align=right>".@number_format($produnit[$pt][$unit][$bln]/$bgtunit[$pt][$unit][$bln]*100)."</td>";
									}
							$form.="</tr>";	
							$urutdivisilist=0;
							foreach($kddivisi as $divisi){
								if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
								
									$urutdivisilist++;
									$form.="
									<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
										<td>".$no.".".$urutunitlist.".".$urutdivisilist."</td>
										<td>".$divisi."</td>
												<td align=center>".$thn."</td>";//<td>".$divisi." - ".$nmorg[$divisi]."</td>
												foreach($arrlistbln as $bln){
													$form.="		
														<td align=right>".@number_format($bgtdivisi[$pt][$unit][$divisi][$bln])."</td>
														<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$bln])."</td>
														<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$bln]/$bgtdivisi[$pt][$unit][$divisi][$bln]*100)."</td>";
												}
											$form.="</tr>";	
											
										}
							}
						
						}
					}
		}
		$form.="
			<tr class=rowcontent>
				<td colspan=3><b>Total</b></td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
				foreach($arrlistbln as $bln){
					$form.="		
						<td align=right><b>".@number_format($bgttot[$bln])."</td>
						<td align=right><b>".@number_format($prodtot[$bln])."</td>
					<td align=right><b>".@number_format($prodtot[$bln]/$bgttot[$bln]*100)."</td>";
				}
				$form.="</tr>";

		echo $form;
		
	break;
	
	
	
	
	
	case'detail1budget':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		
		$arrbln = array();
		$nobln=-1;
		for($i=1;$i<=12;$i++){
			$nobln++;
			if($i<10){
				$arrbln[$nobln] =$thn.'-0'.$i;
			}
			else{
				$arrbln[$nobln] =$thn.'-'.$i;
			}
		}
		

		
		$str="select distinct(kodeunit) as kodeunit from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeunit']] = $row['kodeunit'];
        }
		
				
		$str=" select tahunbudget,kodeunit,sum(kg01) as kg01,sum(kg02) as kg02,sum(kg03) as kg03,sum(kg04) as kg04,sum(kg05) as kg05,sum(kg06) as kg06,
				sum(kg07) as kg07,sum(kg08) as kg08,sum(kg09) as kg09,sum(kg10) as kg10,sum(kg11) as kg11,sum(kg12) as kg12
				 from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where  tahunbudget='".$thn."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") 
				group by kodeunit";	

		$res = fetchData($str);
        $kgwb = array();
 
            foreach ($optunit as $row1) {
              //  $kgwb[$row1][] = 0;
                foreach ($res as $row2) {
                    if ($row1 == $row2['kodeunit']) {
						for($i=1;$i<=12;$i++){
						    if($i<10){
								 $kgwb[$row1][$i-1] = $row2['kg0'.$i]/1000;
							}
							else {
								 $kgwb[$row1][$i-1] = $row2['kg'.$i]/1000;
							}
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

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);

		
		
		
		$graph->title->Set($_SESSION['lang']['budget'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
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
			$no ++ ;
            $resultColor = randomColor();
            if ($row['kodeunit'] == $optunit[$row['kodeunit']]) {	
                $list[$no] = new LinePlot($kgwb[$row['kodeunit']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['kodeunit']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				$targ[$no]='?method=detail2budget&unit='.$row['kodeunit'].'&thn='.$thn.'&pt='.$pt;
				$alts[$no]='Click to Drill Divisi Budget '.$row['kodeunit'];
				$list[$no]->SetCSIMTargets($targ,$alts); 
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	case'detail2budget':
	
		
	
	
		
		$arrbln = array();
		$nobln=-1;
		for($i=1;$i<=12;$i++){
			$nobln++;
			if($i<10){
				$arrbln[$nobln] =$thn.'-0'.$i;
			}
			else{
				$arrbln[$nobln] =$thn.'-'.$i;
			}
		}
		

		
		$str="select distinct(divisi) as divisi from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where tahunbudget = '".$thn."' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."' ) ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['divisi']] = $row['divisi'];
        }
		
	
				
		$str=" select tahunbudget,divisi,sum(kg01) as kg01,sum(kg02) as kg02,sum(kg03) as kg03,sum(kg04) as kg04,sum(kg05) as kg05,sum(kg06) as kg06,
				sum(kg07) as kg07,sum(kg08) as kg08,sum(kg09) as kg09,sum(kg10) as kg10,sum(kg11) as kg11,sum(kg12) as kg12
				 from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where  tahunbudget='".$thn."' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$unit."') 
				group by divisi";	

		$res = fetchData($str);
        $kgwb = array();
 
            foreach ($optunit as $row1) {
              //  $kgwb[$row1][] = 0;
                foreach ($res as $row2) {
                    if ($row1 == $row2['divisi']) {
						for($i=1;$i<=12;$i++){
						    if($i<10){
								 $kgwb[$row1][$i-1] = $row2['kg0'.$i]/1000;
							}
							else {
								 $kgwb[$row1][$i-1] = $row2['kg'.$i]/1000;
							}
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

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['budget'].' '.$_SESSION['lang']['unit'].' '.$unit);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
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
			$no ++ ;
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
	
	
	
	
	
	

	
	case'detail1produksi':
	
	
		$arrlistbln=month_inbetween($thn.'-01',$thn.'-12');
	
		
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		
		$arrbln = array();
		$nobln=-1;
		for($i=1;$i<=12;$i++){
			$nobln++;
			if($i<10){
				$arrbln[$nobln] =$thn.'-0'.$i;
			}
			else{
				$arrbln[$nobln] =$thn.'-'.$i;
			}
		}
		
		
		
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(kgwb/1000) as kgwb,kodeorg,substr(tanggal,1,7) as bulan from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by bulan,kodeorg";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrbln as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['bulan'] && $row1 == $row2['kodeorg']) {
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

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['produksi'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6',
								'#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
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
				$targ[$no]='?method=detail2produksi&unit='.$row['kodeorg'].'&thn='.$thn.'&pt='.$pt;
				$alts[$no]='Click to Drill Divisi Produksi '.$row['kodeorg'];
				$list[$no]->SetCSIMTargets($targ,$alts); 
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	case'detail2produksi':
		
		$arrbln = array();
		$nobln=-1;
		for($i=1;$i<=12;$i++){
			$nobln++;
			if($i<10){
				$arrbln[$nobln] =$thn.'-0'.$i;
			}
			else{
				$arrbln[$nobln] =$thn.'-'.$i;
			}
		}
		
		$str="select distinct(divisi) as divisi from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and divisi in (select divisi from ".$dbname.".organisasi where induk='".$unit."') ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['divisi']] = $row['divisi'];
        }
		
		$str="select sum(kgwb/1000) as kgwb,divisi,substr(tanggal,1,7) as bulan from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' and divisi in (select kodeorganisasi from ".$dbname.".organisasi where  induk='".$unit."')
				group by bulan,divisi";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrbln as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['bulan'] && $row1 == $row2['divisi']) {
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

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['produksi'].' '.$_SESSION['lang']['pt'].' '.$pt);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6',
								'#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
            $no ++;
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
	
	#############################################################
	#############################################################
	
	
	
	case'global':
		$sortprod=$sortbgt='';
		if($pt!=''){
			$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$sortbgt=" and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		$no=$nobgt=-1;
		
		$str=" select sum(kgwb/1000) as kgwb,substr(tanggal,1,7) as bulan from ".$dbname.".kebun_spb_vw 
				where tanggal like '".$thn."%' ".$sortprod." group by bulan order by bulan asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$ydata[$no]=$bar['kgwb'];
			$targ[$no]='?method=detail1produksi&pt='.$pt.'&thn='.$thn;
			$alts[$no]='Click to Drill Produksi';
		}
	
	
		
		$str=" select sum(kg01) as kg01,sum(kg02) as kg02,sum(kg03) as kg03,sum(kg04) as kg04,sum(kg05) as kg05,sum(kg06) as kg06,
				sum(kg07) as kg07,sum(kg08) as kg08,sum(kg09) as kg09,sum(kg10) as kg10,sum(kg11) as kg11,sum(kg12) as kg12,
				tahunbudget from ".$dbname.".bgt_produksi_kbn_kg_vw 
				where  tahunbudget='".$thn."' ".$sortbgt." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			for($i=1;$i<=12;$i++){
				$nobgt++;
				if($i<10){
					@$ydata2[$nobgt]=$bar['kg0'.$i]/1000;
					$targ2[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn;
					$alts2[$nobgt]='Click to Drill Budget';
				}
				else{
					@$ydata2[$nobgt]=$bar['kg'.$i]/1000;
					$targ2[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn;
					$alts2[$nobgt]='Click to Drill Budget';
				}
			}	
		}
	
		if($row==0){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		
		
		$graph = new Graph(590,240);   
		$graph->SetScale("intlin"); //$graph->SetScale('intlin');textlin
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
		// $graph->xaxis->SetTextLabelInterval(1);
		// $graph->xaxis->SetLabelAngle(90);
		

		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );


		$lineplot=new LinePlot($ydata);
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		//$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['produksi']);
		$lineplot->SetCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('red');
		 

		$lineplot2=new LinePlot($ydata2);
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		//$lineplot2->mark->SetColor('green');
		$lineplot2->mark->SetFillColor('green');
		$lineplot2->SetLegend($_SESSION['lang']['budget']);
		$lineplot2->SetCenter();
		//$lineplot2->SetStyle('dashed'); 
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
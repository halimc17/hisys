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
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['tahun']."</td>
						<td align=center colspan=36>".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					for($i=1;$i<=12;$i++)
					{
						$form.="<td  align=center colspan=3>".numToMonth($i,'I','long')."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=12;$i++)
					{
						$form.="
						<td align=center>".$_SESSION['lang']['bgt']." (".$_SESSION['lang']['Ton'].")</td>
						<td align=center>".$_SESSION['lang']['real']." (".$_SESSION['lang']['Ton'].")</td>
						<td align=center>".$_SESSION['lang']['penc']." (%)</td>
						";
					}
			$form.="</tr>
				</thead>
				";
				
				
		$str="select a.pkton,periode,b.induk,a.kodeorg
		from ".$dbname.".pabrik_produksi_vw a left join ".$dbname.".organisasi b 
		on a.kodeorg=b.kodeorganisasi where tahun = '".$thn."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$prodpt[$bar['induk']][$bar['periode']]+=$bar['pkton'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['periode']]+=$bar['pkton'];
			@$prodtot[$bar['periode']]+=$bar['pkton'];
		}
		

		$str=" select (a.kgker01/1000) as kgker01,(a.kgker02/1000) as kgker02,(a.kgker03/1000) as kgker03,(a.kgker04/1000) as kgker04,
						(a.kgker05/1000) as kgker05,(a.kgker06/1000) as kgker06,(a.kgker07/1000) as kgker07,(a.kgker08/1000) as kgker08,
						(a.kgker09/1000) as kgker09,(a.kgker10/1000) as kgker10,(a.kgker11/1000) as kgker11,(a.kgker12/1000) as kgker12,
					  a.tahunbudget,a.millcode,b.induk
				from ".$dbname.".bgt_produksi_pks_vw a  left join ".$dbname.".organisasi b
				 on a.millcode=b.kodeorganisasi  where  a.tahunbudget='".$thn."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			for($i=1;$i<=12;$i++){
				if($i<10){
					@$bgtpt[$bar['induk']][$bar['tahunbudget'].'-0'.$i]+=$bar['kgker0'.$i];
					@$bgtunit[$bar['induk']][$bar['millcode']][$bar['tahunbudget'].'-0'.$i]+=$bar['kgker0'.$i];
					@$bgttot[$bar['tahunbudget'].'-0'.$i]+=$bar['kgker0'.$i];
				}
				else{
					@$bgtpt[$bar['induk']][$bar['tahunbudget'].'-'.$i]+=$bar['kgker'.$i];
					@$bgtunit[$bar['induk']][$bar['millcode']][$bar['tahunbudget'].'-'.$i]+=$bar['kgker'.$i];
					@$bgttot[$bar['tahunbudget'].'-'.$i]+=$bar['kgker'.$i];
				}
			}
		}
			 
		$str="select distinct(a.kodeorg) as kodeorg,b.induk
				from ".$dbname.".pabrik_produksi_vw a left join ".$dbname.".organisasi b 
				on a.kodeorg=b.kodeorganisasi where a.tahun = '".$thn."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			@$jumunit[$bar['induk']]+=1;
		}	

			
		$arrlistbln=month_inbetween($thn.'-01',$thn.'-12');

		foreach($kodept as $pt){//<td>".$pt." - ".$nmorg[$pt]."</td>
			$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>
					<td>".$thn."</td>";
					foreach($arrlistbln as $bln){
						$form.="		
						<td align=right>".@number_format($bgtpt[$pt][$bln])."</td>
						<td align=right>".@number_format($prodpt[$pt][$bln])."</td>
						<td align=right>".@number_format($prodpt[$pt][$bln]/$bgtpt[$pt][$bln]*100)."</td>
						";
					}
					$form.="</tr>";
					$urutunit=0;
					foreach($kodeunit as $unit)
					{
						if(@$listkodeunit[$pt][$unit]==$unit)
						{
							$urutunit++;
							$form.="
							<tr class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunit.">
								<td>".$no.".".$urutunit."</td>
								<td>".$unit."</td>
								<td>".$thn."</td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
								foreach($arrlistbln as $bln){
									$form.="		
										<td align=right>".@number_format($bgtunit[$pt][$unit][$bln])."</td>
										<td align=right>".@number_format($produnit[$pt][$unit][$bln])."</td>
										<td align=right>".@number_format($produnit[$pt][$unit][$bln]/$bgtunit[$pt][$unit][$bln]*100)."</td>";
								}
							$form.="</tr>";	
						}
					}
		}
		$form.="
			<tr class=rowcontent>
				<td colspan=3><b>".$_SESSION['lang']['total']."</b></td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
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
			if($pks!=''){
				$sort=" and kodeorganisasi = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$sort=" and tipe = 'PABRIK'";
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
		

		
		$str="select distinct(millcode) as millcode from ".$dbname.".bgt_produksi_pks_vw 
				where tahunbudget = '".$thn."' and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['millcode']] = $row['millcode'];
        }
		
				
		$str=" select tahunbudget,millcode,sum(kgker01) as kgker01,sum(kgker02) as kgker02,sum(kgker03) as kgker03,sum(kgker04) as kgker04,sum(kgker05) as kgker05,sum(kgker06) as kgker06,
				sum(kgker07) as kgker07,sum(kgker08) as kgker08,sum(kgker09) as kgker09,sum(kgker10) as kgker10,sum(kgker11) as kgker11,sum(kgker12) as kgker12
				 from ".$dbname.".bgt_produksi_pks_vw 
				where  tahunbudget='".$thn."' and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") 
				group by millcode";	
		$res = fetchData($str);
        $kgwb = array();
            foreach ($optunit as $row1) {
              //  $kgwb[$row1][] = 0;
                foreach ($res as $row2) {
                    if ($row1 == $row2['millcode']) {
						for($i=1;$i<=12;$i++){
						    if($i<10){
								 $kgwb[$row1][$i-1] = $row2['kgker0'.$i]/1000;
							}
							else {
								 $kgwb[$row1][$i-1] = $row2['kgker'.$i]/1000;
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
            if ($row['millcode'] == $optunit[$row['millcode']]) {	
                $list[$no] = new LinePlot($kgwb[$row['millcode']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['millcode']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				$graph->Add($list[$no]);
				$list[$no]->value->SetFormat('%d');
				$list[$no]->value->Show();
				$list[$no]->value->SetColor($resultColor);
            }
        }

		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	
	

	
case'detail1produksi':
	
		$arrlistbln=month_inbetween($thn.'-01',$thn.'-12');
		
		if($pt!='')
		{
			if($pks!=''){
				$sort=" and kodeorganisasi = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and induk='".$pt."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
			$sort=" and tipe = 'PABRIK'";
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
		
		
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".pabrik_produksi_vw 
				where tahun = '".$thn."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(pkton) as pkton,kodeorg,periode from ".$dbname.".pabrik_produksi_vw 
				where tahun = '".$thn."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.")
				group by periode,kodeorg";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrbln as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['periode'] && $row1 == $row2['kodeorg']) {
						$kgwb[$row1][$thnlist] = $row2['pkton'];
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
		
		$graph->title->Set($_SESSION['lang']['kernel'].' '.$judul);
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
				$graph->Add($list[$no]);
				$list[$no]->value->SetFormat('%d');
				$list[$no]->value->Show();
				$list[$no]->value->SetColor($resultColor);
            }
        }

		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	#############################################################
	#############################################################
	
	
	
	
	
	
	
	
	
	
	case'global':
		$sortprod=$sortbgt=''; 
	
		if($pt!='')
		{
			if($pks!=''){
				$sortprod=" and kodeorg = '".$pks."'";
				$sortbgt=" and millcode = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$sortbgt=" and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
	
	
		$no=$nobgt=-1;
		
		$str=" select sum(pkton) as pkton from ".$dbname.".pabrik_produksi_vw 
				where tahun = '".$thn."' ".$sortprod." group by periode ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$ydata[$no]=$bar['pkton'];
			$targ[$no]='?method=detail1produksi&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts[$no]=$_SESSION['lang']['klikdetail'];
		}
	
		$str=" select sum(kgker01) as kgker01,sum(kgker02) as kgker02,sum(kgker03) as kgker03,sum(kgker04) as kgker04,
				sum(kgker05) as kgker05,sum(kgker06) as kgker06,
				sum(kgker07) as kgker07,sum(kgker08) as kgker08,
				sum(kgker09) as kgker09,sum(kgker10) as kgker10,
				sum(kgker11) as kgker11,sum(kgker12) as kgker12,
				tahunbudget from ".$dbname.".bgt_produksi_pks_vw 
				where  tahunbudget='".$thn."' ".$sortbgt." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			for($i=1;$i<=12;$i++){
				$nobgt++;
				if($i<10){
					@$ydata2[$nobgt]=$bar['kgker0'.$i]/1000;
					$targ2[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
					$alts2[$nobgt]=$_SESSION['lang']['klikdetail'];
				}
				else{
					@$ydata2[$nobgt]=$bar['kgker'.$i]/1000;
					$targ2[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
					$alts2[$nobgt]=$_SESSION['lang']['klikdetail'];
				}
			}
			
		}
		
		
		if(empty($ydata)){	
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		

		// echo"<pre>";
		// print_r($ydata2);
		// echo"</pre>";
		
		$graph = new Graph(590,240);   
		$graph->SetScale("intlin"); //$graph->SetScale('intlin');textlin
		$graph->SetShadow();
		$graph->img->SetMargin(80,20,20,50);
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);
		
		
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
		 
		//$graph->yaxis->HideZeroLabel(); 
		//$graph->xaxis->HideZeroLabel(); 
		 
	
		 
		 
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
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['budget']);
		$lineplot2->SetCenter();
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		

		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('blue');
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
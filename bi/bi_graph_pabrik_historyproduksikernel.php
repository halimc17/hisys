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

		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		
		$str="select distinct(tahun) as tahun from ".$dbname.".pabrik_produksi_vw";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$arrthn[$bar['tahun']]=$bar['tahun'];
		}
$str="select distinct(tahunbudget) as tahunbudget from ".$dbname.".bgt_produksi_pks_vw";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$arrthn[$bar['tahunbudget']]=$bar['tahunbudget'];
		}
		
		$jumthn=count($arrthn);

		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td align=center colspan=".($jumthn*3).">".$_SESSION['lang']['tahun']."</td>
					</tr>
					<tr>";
					foreach($arrthn as $thn){
						$form.="<td align=center colspan=3>".$thn."</td>";
					}
					$form.="</tr><tr>";
					
					for($i=1;$i<=$jumthn;$i++){
						$form.="
							<td align=center>".$_SESSION['lang']['budget']." TBS</td>
							<td align=center>".$_SESSION['lang']['produksi']." TBS</td>
							<td align=center>".$_SESSION['lang']['produksi']." PK</td>
						";
					}
					$form.="</tr></thead>";	
					
		$str="select distinct(a.kodeorg) as kodeorg,b.induk
				from ".$dbname.".pabrik_produksi_vw a left join ".$dbname.".organisasi b 
				on a.kodeorg=b.kodeorganisasi where a.tahun = '".$thn."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			@$jumunit[$bar['induk']]+=1;
		}				
					
		$str="select a.tbsdiolahton,a.tahun,b.induk,a.kodeorg,a.pkton 
		from ".$dbname.".pabrik_produksi_vw a left join ".$dbname.".organisasi b 
		on a.kodeorg=b.kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];

			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
		
			@$prodtbspt[$bar['induk']][$bar['tahun']]+=$bar['tbsdiolahton'];
			@$prodtbsunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['tbsdiolahton'];
			
			@$prodcpopt[$bar['induk']][$bar['tahun']]+=$bar['pkton'];
			@$prodcpounit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['pkton'];
			
			@$prodtbstot[$bar['tahun']]+=$bar['tbsdiolahton'];
			@$prodcpotot[$bar['tahun']]+=$bar['pkton'];
		}
		
		
		$str="select (a.kgolah/1000) as kgolah,a.tahunbudget,b.induk,a.millcode
		from ".$dbname.".bgt_produksi_pks_vw a left join ".$dbname.".organisasi b 
		on a.millcode=b.kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){	
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['millcode']]=$bar['millcode'];

			$listkodeunit[$bar['induk']][$bar['millcode']]=$bar['millcode'];
		
			@$bgttbspt[$bar['induk']][$bar['tahunbudget']]+=$bar['kgolah'];
			@$bgttbsunit[$bar['induk']][$bar['millcode']][$bar['tahunbudget']]+=$bar['kgolah'];
			

			@$bgttbstot[$bar['tahunbudget']]+=$bar['kgolah'];
			
		}


	
		foreach($kodept as $pt){//<td>".$pt." - ".$nmorg[$pt]."</td>
			$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>";
				foreach($arrthn as $thn){
						$form.="<td align=right>".number_format($bgttbspt[$pt][$thn])."</td>";
						$form.="<td align=right>".number_format($prodtbspt[$pt][$thn])."</td>";
						$form.="<td align=right>".number_format($prodcpopt[$pt][$thn])."</td>";
				}
				$form.="</tr>";
				
				
				$urutunit=0;
				foreach($kodeunit as $unit)
				{
					if(@$listkodeunit[$pt][$unit]==$unit)
					{
						$urutunit++;
						$form.="
						<tr   class=rowcontentdet style='cursor:pointer;display:none' id=unitlist".$no."".$urutunit.">
							<td>".$no.".".$urutunit."</td>
							<td>".$unit."</td>";
						foreach($arrthn as $thn){
							$form.="<td align=right>".number_format($bgttbsunit[$pt][$unit][$thn])."</td>";
							$form.="<td align=right>".number_format($prodtbsunit[$pt][$unit][$thn])."</td>";
							$form.="<td align=right>".number_format($prodcpounit[$pt][$unit][$thn])."</td>";
						}
						$form.="</tr>";							
							
					}
				}
				
		
		}
		$form.="<tr class=rowcontent>";
		$form.="<td colspan=2><b>".$_SESSION['lang']['total']."</b></td>";
		foreach($arrthn as $thn){
			$form.="<td align=right><b>".number_format($bgttbstot[$thn])."</b></td>";
			$form.="<td align=right><b>".number_format($prodtbstot[$thn])."</b></td>";
			$form.="<td align=right><b>".number_format($prodcpotot[$thn])."</b></td>";
		}
		$form.="</tr></table>";			
		
		echo $form;			
	break;
	
	
	
	
	
	
	
	case'detail1budget':
	
		$sort='';
		if($pt!=''){
			if($pks!=''){
				$sort=" and kodeorganisasi='".$pks."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and induk='".$pt."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$sort=" and tipe='PABRIK' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$nothn=-1;
		$str="select distinct(tahunbudget) as tahun from ".$dbname.".bgt_produksi_pks_vw 
				where 1=1 and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$nothn++;
			$arrthn[$nothn]=$bar['tahun'];
		}
	
		$str="select distinct(millcode) as kodeorg from ".$dbname.".bgt_produksi_pks_vw 
				where 1=1 and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";	
				
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		//millcode 
		$str=" select sum(kgolah/1000) as kgolah ,tahunbudget as tahun,millcode as kodeorg from ".$dbname.".bgt_produksi_pks_vw  where 1=1 
					and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.")  group by tahun,kodeorg ";
					
		$res = fetchData($str);
        $cpoton = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $cpoton[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
						$cpoton[$row1][$thnlist] = $row2['kgolah'];
                    }
                }
            }
        }

		if($arrthn<1){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(70,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
		$graph->yaxis->HideZeroLabel();
		$graph->xaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);
		$graph->title->Set($_SESSION['lang']['budget'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
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
            if ($row['kodeorg'] == $optunit[$row['kodeorg']]) {	
                $list[$no] = new LinePlot($cpoton[$row['kodeorg']]);
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
	

	
	
	case'detail1produksi':
	
		$sort='';
		if($pt!=''){
			if($pks!=''){
				$sort=" and kodeorganisasi='".$pks."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and induk='".$pt."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$sort=" and tipe='PABRIK' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		
	
		$nothn=-1;
		$str="select distinct(tahun) as tahun from ".$dbname.".pabrik_produksi_vw 
				where 1=1 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$nothn++;
			$arrthn[$nothn]=$bar['tahun'];
		}
	
	
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".pabrik_produksi_vw 
				where 1=1 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";	
				
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str=" select sum(tbsdiolahton) as tbsdiolahton ,tahun,kodeorg from ".$dbname.".pabrik_produksi_vw  where 1=1 
					and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where  1=1 ".$sort.")  group by tahun,kodeorg ";
					
		$res = fetchData($str);
        $cpoton = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $cpoton[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
						$cpoton[$row1][$thnlist] = $row2['tbsdiolahton'];
                    }
                }
            }
        }

		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     

		$graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);
		
		$graph->title->Set($_SESSION['lang']['tbs'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
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
            if ($row['kodeorg'] == $optunit[$row['kodeorg']]) {	
                $list[$no] = new LinePlot($cpoton[$row['kodeorg']]);
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
	
	
	
	
	
	
	case'detail1cpo'://ini sebenernya PK hanya casenya saja copy dr yg CPO
		$sort='';
		if($pt!=''){
			if($pks!=''){
				$sort=" and kodeorganisasi='".$pks."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and induk='".$pt."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$sort=" and tipe='PABRIK' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
	
		$nothn=-1;
		$str="select distinct(tahun) as tahun from ".$dbname.".pabrik_produksi_vw 
				where 1=1 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$nothn++;
			$arrthn[$nothn]=$bar['tahun'];
		}
		
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".pabrik_produksi_vw 
				where 1=1 and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str=" select sum(pkton) as pkton ,tahun,kodeorg from ".$dbname.".pabrik_produksi_vw  where 1=1 
					and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.")  group by tahun,kodeorg ";
		$res = fetchData($str);
        $cpoton = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $cpoton[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
						$cpoton[$row1][$thnlist] = $row2['pkton'];
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
		// $graph->yaxis->HideZeroLabel();
		// $graph->xaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($_SESSION['lang']['kernel'].' '.$judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
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
            if ($row['kodeorg'] == $optunit[$row['kodeorg']]) {	
                $list[$no] = new LinePlot($cpoton[$row['kodeorg']]);
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
	
	
	
	
	
	case'global':
		$sortprod=$sortbgt='';
		
		if($pt!=''){
			if($pks!=''){
				$sortprod=" and kodeorg = '".$pks."'";
				$sortbgt=" and kodeunit = '".$pks."'";
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
		
		$no=$nobgt=$nocpo=-1;
		$str=" select sum(pkton) as pkton,tahun from ".$dbname.".pabrik_produksi_vw  where 1=1 and tahun <= '".$thn."' ".$sortprod." group by tahun ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nocpo++;
			$arrthn[$nocpo]=$bar['tahun'];
			$cpoprod[$nocpo]=$bar['pkton'];
			$targ[$nocpo]='?method=detail1cpo&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts[$nocpo]=$_SESSION['lang']['klikdetail'];
		}
		
		$str=" select sum(tbsdiolahton) as tbsdiolahton ,tahun from ".$dbname.".pabrik_produksi_vw  where 1=1  and tahun <= '".$thn."' ".$sortprod." group by tahun ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$arrthn[$no]=$bar['tahun'];
			$tbsprod[$no]=$bar['tbsdiolahton'];
			$targ2[$no]='?method=detail1produksi&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts2[$no]=$_SESSION['lang']['klikdetail'];
		}
	
		$str=" select sum(kgolah/1000) as kgolah,tahunbudget from ".$dbname.".bgt_produksi_pks_vw  where 1=1 and tahunbudget <= '".$thn."' ".$sortbgt." group by tahunbudget  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tbsbgt[0]=0;
			$targ3[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts3[$nobgt]=$_SESSION['lang']['klikdetail'];
		}
		while($bar=$res->fetch()){
			$nobgt++;
			$tbsbgt[$nobgt]=$bar['kgolah'];
			$targ3[$nobgt]='?method=detail1budget&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts3[$nobgt]=$_SESSION['lang']['klikdetail'];
		}

		//$arrthn=array('0'=>'2012','1'=>'2013','2'=>'2014','3'=>'2015');
		
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		array_multisort($arrthn,SORT_ASC);
		
	
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); 
		$graph->SetY2Scale("lin");
		$graph->SetShadow();
		$graph->img->SetMargin(80,60,20,50);
		$graph->yaxis->scale->SetGrace(10);
		$graph->y2axis->scale->SetGrace(50);  
		
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(30);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(45);
		
		$txt = new Text($_SESSION['lang']['tbs'].'('.$_SESSION['lang']['Ton'].')');
		$txt->SetPos(0.02,0.09,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);

		$txt = new Text($_SESSION['lang']['kernel'].'('.$_SESSION['lang']['Ton'].')');
		$txt->SetPos(0.87,0.1,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);
		 
		$lineplot=new LinePlot($cpoprod);
		//$lineplot->SetColor( 'green' );
		$lineplot->SetWeight( 10 );   // Two pixel wide
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['kernel']);
		$lineplot->setCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->AddY2($lineplot); 
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');
		 

		$lineplot2=new LinePlot($tbsprod);
		$lineplot2->SetWeight( 10 );   // Two pixel wide
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot2->mark->SetColor('red');
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['tbs']);
		$lineplot2->setCenter();
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('red');
		
		$lineplot3=new LinePlot($tbsbgt);
		$lineplot3->SetWeight( 10 );   // Two pixel wide
		$lineplot3->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot3->mark->SetColor('black');
		$lineplot3->mark->SetFillColor('beige');
		$lineplot3->SetLegend($_SESSION['lang']['budget']);
		$lineplot3->setCenter();
		$lineplot3->SetCSIMTargets($targ3,$alts3);  
		$graph->Add($lineplot3);
		
		$lineplot3->value->SetFormat('%d');
		$lineplot3->value->Show();
		$lineplot3->value->SetColor('purple');
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
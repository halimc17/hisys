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
		
		$jumthn=count($arrthn);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td align=center colspan=".($jumthn*2).">".$_SESSION['lang']['tahun']."</td>
					</tr>
					<tr>";
					foreach($arrthn as $thn){
						$form.="<td align=center colspan=2>".$thn."</td>";
					}
					$form.="</tr><tr>";
					
					for($i=1;$i<=$jumthn;$i++){
						$form.="
							<td align=center>".$_SESSION['lang']['cpo']."</td>
							<td align=center>PK</td>
							
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
				
				
				
	

		// $str=" select (a.usbcpo+a.fruitineb+a.ebstalk+a.fibre+a.nut+a.effluent+a.soliddecanter) as losescpo,
					// (a.usbpk+a.fruitinebker+a.cyclone+a.ltds+a.claybath+a.hydrocyclone) as losespk,a.tbsdiolah,a.kodeorg,b.induk,left(a.tanggal,4) as tahun
				// from ".$dbname.".pabrik_produksi a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi";	
				
		$str="select a.losescpo,a.losespk,kodeorg,left(tanggal,4) as tahun,b.induk
			from ".$dbname.".pabrik_produksi_loses_vw a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			
			@$losescpo[$bar['tahun']]+=$bar['losescpo'];
			@$losescpopt[$bar['induk']][$bar['tahun']]+=$bar['losescpo'];
			@$losescpounit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['losescpo'];
			
			@$losespk[$bar['tahun']]+=$bar['losespk'];
			@$losespkpt[$bar['induk']][$bar['tahun']]+=$bar['losespk'];
			@$losespkunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['losespk'];
		}

				
		// echo"<pre>";
		// print_r($pkkgpt);
		// echo"</pre>";

	
		foreach($kodept as $pt){//<td>".$pt." - ".$nmorg[$pt]."</td>
			$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>";
					foreach($arrthn as $thn){
						$form.="<td align=right>".number_format($losescpopt[$pt][$thn])."</td>";
						$form.="<td align=right>".number_format($losespkpt[$pt][$thn])."</td>";
					}
				$form.="</tr>";
				
				
				$urutunit=0;
				foreach($kodeunit as $unit)
				{
					if(@$listkodeunit[$pt][$unit]==$unit)
					{
						$urutunit++;
						$form.="
						<tr class=rowcontentdet style='cursor:pointer;display:none' id=unitlist".$no."".$urutunit.">
							<td>".$no.".".$urutunit."</td>
							<td>".$unit."</td>";
						foreach($arrthn as $thn){
							$form.="<td align=right>".number_format($losescpounit[$pt][$unit][$thn])."</td>";
							$form.="<td align=right>".number_format($losespkunit[$pt][$unit][$thn])."</td>";
						}
						$form.="</tr>";							
							
					}
				}
		}
		$form.="<tr class=rowcontent>";
		$form.="<td colspan=2><b>".$_SESSION['lang']['total']."</b></td>";
		foreach($arrthn as $thn){
			$form.="<td align=right>".number_format($losescpo[$thn])."</td>";
			$form.="<td align=right>".number_format($losespk[$thn])."</td>";
		}
		$form.="</tr></table>";			
		
					
					
		echo $form;			
	break;
	
	
	
	
	
	
	case'global':
		$sortprod='';
		if($pt!='')
		{
			if($pks!=''){
				$sortprod=" and kodeorg = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$no=-1;
		// $str="  select left(tanggal,4) as tahun,sum((usbcpo+fruitineb+ebstalk+fibre+nut+effluent+soliddecanter)) as losescpo,
				// sum((usbpk+fruitinebker+cyclone+ltds+claybath+hydrocyclone)) as losespk,sum(tbsdiolah) as tbsdiolah 
				// from ".$dbname.".pabrik_produksi where 1=1  ".$sortprod." group by tahun";	
				
		$str="select sum(losescpo) as losescpo,sum(losespk) as losespk,kodeorg,left(tanggal,4) as tahun 
			from ".$dbname.".pabrik_produksi_loses_vw where 1=1 and left(tanggal,4) <= '".$thn."' ".$sortprod." group by tahun";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$arrthn[$no]=$bar['tahun'];
			// $losescpo[$no]=($bar['losescpo']/100*$bar['tbsdiolah'])/1000;
			// $losespk[$no]=($bar['losespk']/100*$bar['tbsdiolah'])/1000;
			$losescpo[$no]=$bar['losescpo'];
			$losespk[$no]=$bar['losespk'];
		}
		
		
		
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
			
		array_multisort($arrthn,SORT_ASC);
		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(80,20,20,50);
		$graph->yaxis->scale->SetGrace(50);
		
		
		$graph->title->Set($judul);
		
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(35);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
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
		 
		 
		$lineplot=new LinePlot($losescpo);
		//$lineplot->SetColor( 'green' );
		$lineplot->SetWeight( 10 );   // Two pixel wide
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetColor('red');
		$lineplot->mark->SetFillColor('blue');
		$lineplot->SetLegend($_SESSION['lang']['cpo']);
		$lineplot->setCenter();
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');
		 

		$lineplot2=new LinePlot($losespk);
		$lineplot2->SetWeight( 10 );   // Two pixel wide
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot2->mark->SetColor('blue');
		$lineplot2->mark->SetFillColor('red');
		$lineplot2->SetLegend($_SESSION['lang']['kernel']);
		$lineplot2->setCenter();
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
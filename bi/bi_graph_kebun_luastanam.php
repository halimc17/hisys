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
		$str="select a.luasareaproduktif,a.tahuntanam,b.induk,left(a.kodeorg,4) as kodeorg,left(a.kodeorg,6) as divisi 
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
			@$prodpt[$bar['induk']][$bar['tahuntanam']]+=$bar['luasareaproduktif'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['tahuntanam']]+=$bar['luasareaproduktif'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahuntanam']]+=$bar['luasareaproduktif'];
			@$prodtot[$bar['tahuntanam']]+=$bar['luasareaproduktif'];
		}
				
		
		//print_r($jumunit);
		
	
			


			
				
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".@$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt." - ".@$nmorg[$pt]."</td>";
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
							<td>".$unit." - ".@$nmorg[$unit]."</td>
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
								<td>".$divisi." - ".@$nmorg[$divisi]."</td>";
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

	
	case'global':
		$sort='';
		if($pt!=''){
			$sort=" and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		}
		
		/*$str=" select sum(kwantitas/1000) as kwantitas,left(tanggal,4) as tahun from ".$dbname.".kebun_pakai_material_vw 
							where kodebarang like '311%' and left(tanggal,4) <= '".$thn."' ".$sortppk."  group by tahun";	*/
		$noppk=$noprod=$nosen=-1;
		$str=" select sum(luasareaproduktif) as luasareaproduktif,tahuntanam from ".$dbname.".setup_blok 
							where 1=1 and tahuntanam>0 and tahuntanam <= '".$thn."' ".$sort."  group by tahuntanam";							
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noppk++;
			$arrthn[$noppk]=$bar['tahuntanam'];
			$ydata[$noppk]=$bar['luasareaproduktif'];
			$targ[$noppk]='?method=detail1luas&pt='.$pt.'&thn='.$thn;
			$alts[$noppk]='Click to Drill Luas';
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
		
		if($pt==''){
			$graph->title->Set($_SESSION['lang']['seluruhpt']);
		}
		else{
			$graph->title->Set($_SESSION['lang']['pt'].' '.$pt);
		}
		
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(15);
		$graph->xaxis->title->SetMargin(10);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahuntanam']);
		$graph->yaxis->title->Set($_SESSION['lang']['ha']);
		 
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
		$lineplot->SetLegend($_SESSION['lang']['pokok']);
		
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
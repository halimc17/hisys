<?php // content="text/plain; charset=utf-8"
include('master_validation.php');
include('../config/connection.php');
include('../lib/nangkoelib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_bar.php');
include('../lib/zLib.php');

$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$jenis = checkPostGet('jenis','');

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
						<td align=center>No</td>
						<td align=center>".$_SESSION['lang']['unit']."</td>
						<td align=center>".$_SESSION['lang']['tahun']."</td>
						<td align=center>".$_SESSION['lang']['budget']."<br>(ton)</td>
						<td align=center>".$_SESSION['lang']['realisasi']."<br>(ton)</td>
					</tr></thead>";
					
					
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
					
		$str="select (a.kgwb/1000) as kgwb,a.tanggal,b.induk,a.kodeorg,a.divisi 
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
			@$prodpt[$bar['induk']]+=$bar['kgwb'];
			@$produnit[$bar['induk']][$bar['kodeorg']]+=$bar['kgwb'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]+=$bar['kgwb'];
		}

		
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
			@$bgtpt[$bar['induk']]+=$bar['kgsetahun'];
			@$bgtunit[$bar['induk']][$bar['kodeunit']]+=$bar['kgsetahun'];
			@$bgtdivisi[$bar['induk']][$bar['kodeunit']][$bar['divisi']]+=$bar['kgsetahun'];			
		}
		
		// echo "<pre>";
		// print_r($bgtdivisi);
		// echo "</pre>";
		
		if(empty($kodept)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt." - ".$nmorg[$pt]."</td>
					<td>".$thn."</td>
					<td align=right>".@number_format($bgtpt[$pt])."</td>
					<td align=right>".@number_format($prodpt[$pt])."</td>
				</tr>";
				$urutunitlist=0;
				foreach($kodeunit as $unit)
				{
					if(@$listkodeunit[$pt][$unit]==$unit)
					{
						@$urutunit+=1;
						$urutunitlist++;
						$form.="
						<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".$jumdivisi[$unit]."')\">
							<td>".$no.".".$urutunitlist."</td>
							<td>".$unit." - ".$nmorg[$unit]."</td>
							<td>".$thn."</td>
							<td align=right>".@number_format($bgtunit[$pt][$unit])."</td>
							<td align=right>".@number_format($produnit[$pt][$unit])."</td>
						</tr>";
					}
					$urutdivisilist=0;
					foreach($kddivisi as $divisi)
					{
						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi)
						{
							@$urutdivisi+=1;
							$urutdivisilist++;
							$form.="
							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
								<td>".$no.".".$urutunit.".".$urutdivisilist."</td>
								<td>".$divisi." - ".$nmorg[$divisi]."</td>
								<td>".$thn."</td>
								<td align=right>".@number_format($bgtdivisi[$pt][$unit][$divisi])."</td>
								<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi])."</td>
							</tr>";
						}
					}
				}
		}
					
		echo $form;			
		
	break;	
	
	
	case'detail':
		$data1y=array();
		
		if($jenis=='produksi'){
			$no=-1;
			$str=" select sum(kgwb/1000) as kgwb,divisi from ".$dbname.".kebun_spb_vw 
							where tanggal like '".$thn."%' and divisi like '".$unit."%'  group by divisi";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$no++;
				$arrdiv[$no]=$bar['divisi'];
				$data1y[$no]=$bar['kgwb'];
				
			}
		}
		else
		{
			$nobgt=-1;
			$str=" select sum(kgsetahun/1000) as kgsetahun,tahunbudget,kodeunit,divisi
						 from ".$dbname.".bgt_produksi_kbn_kg_vw 
						 where  tahunbudget='".$thn."' and kodeunit='".$unit."'  group by divisi ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$row=$res->rowCount();
			$res->setFetchMode(PDO::FETCH_ASSOC);
			if($row==0){
				$data2y[0]=0;
			}
			while($bar=$res->fetch()){
				$nobgt++;
				$arrdiv[$nobgt]=$bar['divisi'];
				$data1y[$nobgt]=$bar['kgsetahun'];
			}
		}

		// Create the graph. These two calls are always required
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");

		 
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);



		$graph->xaxis->SetTickLabels($arrdiv);
		$graph->xaxis->SetLabelAngle(60);
		 
		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		//$b1plot->SetFillColor("orange");
		
		
		// Create the grouped bar plot
		$gbplot = new GroupBarPlot(array($b1plot));
		
		if($jenis=='produksi'){
			$b1plot->SetLegend($_SESSION['lang']['produksi']);
		}
		else{
			$b1plot->SetLegend($_SESSION['lang']['budget']);
		}
		
		// ...and add it to the graPH
		$graph->Add($gbplot);
		 
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['divisi']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);

		#legend
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		// Display the graph
		
		$graph->legend->SetPos(0.002,0.99,'left','bottom');
		$graph->legend->SetColumns(3);  
		
		$graph->StrokeCSIM();
	 echo"<br><a href=javascript:history.back(-1)>Back</a>";        
	break;
	
	
	
	
	case'global':
	
		// if($pt=='')
		// {
			// $pt=$_SESSION['empl']['kodeorganisasi'];
		// }
	
		$data1y=array();
		if($pt==''){
			$sortp="and kodeorg in (select kodeorganisasi  from ".$dbname.".organisasi where tipe='KEBUN') ";
			$sortb="and kodeunit in (select kodeorganisasi  from ".$dbname.".organisasi where tipe='KEBUN') ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortp="and kodeorg in (select kodeorganisasi  from ".$dbname.".organisasi where induk='".$pt."') ";
			$sortb="and kodeunit in (select kodeorganisasi  from ".$dbname.".organisasi where induk='".$pt."') ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}

		$no=-1;
		$str=" select sum(kgwb/1000) as kgwb,kodeorg from ".$dbname.".kebun_spb_vw 
						where tanggal like '".$thn."%' ".$sortp."  group by kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$arrkebun[$no]=$bar['kodeorg'];
			$data1y[$no]=$bar['kgwb'];
			$targ1[$no]='?method=detail&unit='.$bar['kodeorg'].'&thn='.$thn.'&jenis=produksi';
			$alts1[$no]='Click to Drill Produksi';
		}

		$nobgt=-1;
		$str=" select sum(kgsetahun/1000) as kgsetahun,tahunbudget,kodeunit
					 from ".$dbname.".bgt_produksi_kbn_kg_vw 
					 where  tahunbudget='".$thn."' ".$sortb." group by kodeunit ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$data2y[0]=0;
		}
		while($bar=$res->fetch()){
			$nobgt++;
			$arrkebun[$nobgt]=$bar['kodeunit'];
			$data2y[$nobgt]=$bar['kgsetahun'];
			$targ2[$nobgt]='?method=detail&unit='.$bar['kodeunit'].'&thn='.$thn.'&jenis=budget';
			$alts2[$nobgt]='Click to Drill Budget';
		}
		
		
		if(empty($arrkebun)){
			echo $_SESSION['lang']['dataempty'];exit();
		}


		$graph = new Graph(580,240);   
		$graph->SetScale("textlin");

		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);

		$graph->xaxis->SetTickLabels($arrkebun);
				$graph->xaxis->SetLabelAngle(45);

		$b1plot = new BarPlot($data1y);
		$b2plot = new BarPlot($data2y);
		
		
		
	
		$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
		 
		$b1plot->SetLegend($_SESSION['lang']['produksi']);
		$b2plot->SetLegend($_SESSION['lang']['budget']);


		$b1plot->SetCSIMTargets(@$targ1,@$alts1);    


		
		$b2plot->SetCSIMTargets(@$targ2,@$alts2);    	

		// ...and add it to the graPH
		$graph->Add($gbplot);
		
		
		
		 
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		
		$graph->xaxis->title->Set($_SESSION['lang']['unit']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
				
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		#legend

		 
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD );
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3);  
		 
		 
		// Display the graph
		$graph->StrokeCSIM();
		
	
	break;
}



?>
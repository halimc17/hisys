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
		
		$str=" select a.kodeorg,a.tahun,a.tbsdiolahkg,a.cpokg,a.pkkg,b.induk from ".$dbname.".pabrik_produksi_vw a 
				left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";					
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
				$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$tbspt[$bar['induk']][$bar['tahun']]+=$bar['tbsdiolahkg'];
			@$tbsunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['tbsdiolahkg'];
			@$tbstot[$bar['tahun']]+=$bar['tbsdiolahkg'];
				@$cpopt[$bar['induk']][$bar['tahun']]+=$bar['cpokg'];
				@$cpounit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['cpokg'];
				@$cpotot[$bar['tahun']]+=$bar['cpokg'];
			@$pkpt[$bar['induk']][$bar['tahun']]+=$bar['pkkg'];
			@$pkunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['pkkg'];
			@$pktot[$bar['tahun']]+=$bar['pkkg'];
		}
		

		@$jthn=count($tahun);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*2).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($tahun as $thnlist){
						$form.="<td  align=center colspan=2>".$thnlist."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=$jthn;$i++){
						$form.="
						<td align=center>".$_SESSION['lang']['cpo']."</td>
						<td align=center>".$_SESSION['lang']['kernel']."</td>
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
		
		
		####################################################################################
		####################################################################################
		
				
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt." - ".$nmorg[$pt]."</td>
					";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right>".@number_format($cpopt[$pt][$thnlist]/$tbspt[$pt][$thnlist]*100,2)."</td>
					<td align=right>".@number_format($pkpt[$pt][$thnlist]/$tbspt[$pt][$thnlist]*100,2)."</td>";
			}
			$form.="
				</tr>
			";
			//$urutunit=0;
			$urutunitlist=0;
			foreach($kodeunit as $unit)	{
				if(@$listkodeunit[$pt][$unit]==$unit){
					@$urutunit+=1;
					$urutunitlist++;
					$form.="
					<tr  class=rowcontentdet   style='display:none' id=unitlist".$no."".$urutunitlist.">
						<td>".$no.".".$urutunitlist."</td>
						<td>".$unit." - ".$nmorg[$unit]."</td>
						";
						foreach($tahun as $thnlist){
							$form.="		
								<td align=right>".@number_format($cpounit[$pt][$unit][$thnlist]/$tbsunit[$pt][$unit][$thnlist]*100,2)."</td>
								<td align=right>".@number_format($pkunit[$pt][$unit][$thnlist]/$tbsunit[$pt][$unit][$thnlist]*100,2)."</td>";
						}
					$form.="</tr>";	
				}
			}
		}		
		$form.="
				<tr class=rowcontent>
				<td colspan=2 align=center><b>Total</td>";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right><b>".@number_format($cpotot[$thnlist]/$tbstot[$thnlist]*100,2)."</td>
					<td align=right><b>".@number_format($pktot[$thnlist]/$tbstot[$thnlist]*100,2)."</td>";
			}
			$form.="
				</tr></table>
			";		
		echo $form;
		
	break;
	
	
	
	
	
	
	case'global':
	
	
	
		$sortprod=$sortbgt='';
		if($pt!=''){
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
		
		$str=" select tahun,(sum(cpokg)/sum(tbsdiolahkg)*100) as oercpo,(sum(pkkg)/sum(tbsdiolahkg)*100) as oerpk 
				from ".$dbname.".pabrik_produksi_vw where  1=1  and tahun <= '".$thn."' ".$sortprod." group by tahun "; 				
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$arrthn[$no]=$bar['tahun'];
			$oercpo[$no]=$bar['oercpo'];
			$oerpk[$no]=$bar['oerpk'];
		}
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
			
		array_multisort($arrthn,SORT_ASC);
		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(1);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set("%");
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(45);
		 
		 
				$lineplot=new LinePlot($oercpo);
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
		 

		$lineplot2=new LinePlot($oerpk);
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
<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../lib/zLib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_bar.php');
require_once ('../jpgraph/jpgraph_line.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_pie.php');
require_once ('../jpgraph/jpgraph_pie3d.php');
require_once ('../jpgraph/jpgraph_table.php');
require_once ('../jpgraph/jpgraph_canvas.php');
require_once ("../jpgraph/jpgraph_mgraph.php");

 
$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$id = checkPostGet('id','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($method){
	
		case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		
		$str="select (debet/1000000) as biaya,left(tanggal,4) as tahun,kodeorg,induk 
				from ".$dbname.".keu_jurnaldt_vw  a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi  where a.noakun like '41102%' 
				and kodevhc='' and left(tanggal,4)<='".$thn."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
				$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$bypt[$bar['induk']][$bar['tahun']]+=$bar['biaya'];
			@$byunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['biaya'];
			@$bytot[$bar['tahun']]+=$bar['biaya'];
		}
		
		$str="select (rupiah/1000000) as biaya,tahunbudget as tahun,left(kodeorg,4) as kodeorg 
					from ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b
					on left(a.kodeorg,4)=b.kodeorganisasi where tahunbudget<='".$thn."'
					and tipebudget='TRK'";
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
				$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$bgtpt[$bar['induk']][$bar['tahun']]+=$bar['biaya'];
			@$bgtunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['biaya'];
			@$bgttot[$bar['tahun']]+=$bar['biaya'];
		}
		

		@$jthn=count($tahun);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:200% border=0>
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
						<td align=center>".$_SESSION['lang']['budget']."</td>
						<td align=center>".$_SESSION['lang']['biaya']."</td>
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
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".@$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt." - ".@$nmorg[$pt]."</td>
					";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right>".@number_format($bgtpt[$pt][$thnlist])."</td>
					<td align=right>".@number_format($bypt[$pt][$thnlist])."</td>";
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
						<td>".$unit." - ".@$nmorg[$unit]."</td>
						";
						foreach($tahun as $thnlist){
							$form.="		
								<td align=right>".@number_format($bgtunit[$pt][$unit][$thnlist])."</td>
								<td align=right>".@number_format($byunit[$pt][$unit][$thnlist])."</td>";
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
					<td align=right><b>".@number_format($bgttot[$thnlist])."</td>
					<td align=right><b>".@number_format($bytot[$thnlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
		echo $form;
		
	break;
	
	
	
	
	
	
	
	case'global':
		$sortprod=$sortbgt='';
		if($pt!=''){
			$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";
		}else{
			$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','KANWIL','PABRIK'))";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
			$no=0;
			$str="select sum(debet) as biaya,left(tanggal,4) as tahun from ".$dbname.".keu_jurnaldt_vw where noakun like '41102%'
			      ".$sortprod." and kodevhc='' and left(tanggal,4)<='".$thn."' group by left(tanggal,4) order by left(tanggal,4) asc limit 10";
			//exit('warning :'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrthn[$no]=$bar['tahun'];
				$ydata[$no]=($bar['biaya']/1000000);
				$targ[$no]=$_SERVER['PHP_SELF'].'?method=detailunit&pt='.$pt.'&thn='.$thn.'bln='.@$bar['bulan'];
				$alts[$no]="";
				$no++;
			}
			$no=0;
			$str="select sum(rupiah) as biaya,tahunbudget as tahun from ".$dbname.".bgt_budget_detail where tahunbudget<='".$thn."'
			      ".$sortprod." and tipebudget='TRK' group by tahunbudget order by tahunbudget";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$row=owlBaris($res);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			if($row!=0){
				while($bar=$res->fetch()){
					if($bar['tahun']!=''){
						$arrthn[$nobgt]=$bar['tahun'];	
					}
					$ydata2[$nobgt]=($bar['biaya']/1000000);
					$targ2[$nobgt]=$_SERVER['PHP_SELF'].'?method=detailunit&pt='.$pt.'&thn='.$thn.'bln='.@$bar['bulan'];
					$alts2[$nobgt]="";
					$nobgt++;
				}	
			}
			
			
$graph = new Graph(580,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);		
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].' ('.$_SESSION['lang']['juta'].')');
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(45);
			
			$lineplot1=new LinePlot($ydata);
			$lineplot1->SetWeight( 20 );   // Two pixel wide
			$lineplot1->mark->SetType(MARK_FILLEDCIRCLE);
			$lineplot1->mark->SetColor('red');
			$lineplot1->mark->SetFillColor('blue');
			$lineplot1->SetLegend($_SESSION['lang']['biaya']);
			$lineplot1->setCenter();
		
			$graph->Add($lineplot1);
			$lineplot1->value->SetFormat('%d');
			$lineplot1->value->Show();
			$lineplot1->value->SetColor('blue');
			
			if(!empty($ydata2)){
				$lineplot2=new LinePlot($ydata2);
				$lineplot2->SetWeight( 20 );   // Two pixel wide
				$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);
				$lineplot2->mark->SetColor('blue');
				$lineplot2->mark->SetFillColor('red');
				$lineplot2->SetLegend($_SESSION['lang']['budget']);
				$lineplot2->setCenter();
				$graph->Add($lineplot2);
				$lineplot2->value->SetFormat('%d');
				$lineplot2->value->Show();
				$lineplot2->value->SetColor('red');
			}
			$graph->legend->SetPos(0.5,0.99,'center','bottom');
			$graph->legend->SetColumns(3); 
		
		$graph->StrokeCSIM();
	break;
	
}

?>
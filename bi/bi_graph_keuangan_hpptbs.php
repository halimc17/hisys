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
		
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+
						debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+
						debet11-kredit11+debet12-kredit12) as nilai, left(periode,4) as tahun,induk
						from ".$dbname.".keu_saldobulanan a left join ".$dbname.".organisasi b
						on a.kodeorg=b.kodeorganisasi where left(periode,4)<='2016' and left(periode,4)!=0 
						and left(a.noakun,2) in ('61','62') and tipe='KEBUN' group by left(periode,4),induk";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtRupiah[$bar['induk']][$bar['tahun']]=$bar['nilai'];
			$arrthn[$bar['tahun']]=$bar['tahun'];	
			$kodept[$bar['induk']]=$bar['induk'];
			@$tdtRupiah[$bar['tahun']]+=$bar['nilai'];
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+
				debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+
				debet11-kredit11+debet12-kredit12) as nilai,left(periode,4) as tahun,induk
				from ".$dbname.".keu_saldobulanan a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi  where left(periode,4)<='".$thn."' and left(periode,4)!=0 
				and left(a.noakun,1) in ('7') and tipe='KEBUN' group by left(periode,4),induk";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dtRupiahv[$bar['induk']][$bar['tahun']]+=$bar['nilai'];
			$arrthn[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];	
			@$tdtRupiahv[$bar['tahun']]+=$bar['nilai'];
		}			   
		
		$str="select sum(oer) as kgprod,left(tanggal,4) as thn,induk from ".$dbname.".pabrik_produksi a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi where left(tanggal,4)<='".$thn."' and tipe='PABRIK'  group by left(tanggal,4),induk";
				
		$str="select sum(hasilkerjakg) as kgprod,left(tanggal,4) as tahun,induk from ".$dbname.".kebun_prestasi_vw a 
				left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi
				where left(tanggal,4)<='".$thn."' and tipe='KEBUN' group by left(tanggal,4),induk";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtFisik[$bar['induk']][$bar['tahun']]=$bar['kgprod'];
			$arrthn[$bar['tahun']]=$bar['tahun'];	
			$kodept[$bar['induk']]=$bar['induk'];		
			@$tdtFisik[$bar['tahun']]+=$bar['kgprod'];
		}
		
	
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		} 
		 
		

		@$jthn=count($arrthn);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=2  align=center>No</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*2).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($arrthn as $thnlist){
						$form.="<td  align=center>".$thnlist."</td>";
					}
			$form.="</tr>
				</thead>
				";
		
		
		####################################################################################
		####################################################################################
		
			
		foreach($kodept as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$pt." - ".$nmorg[$pt]."</td>
					";
			foreach($arrthn as $thnlist){
				$form.="		
					<td align=right>".@number_format((@$dtRupiah[$pt][$thnlist]+@$dtRupiahv[$pt][$thnlist])/@$dtFisik[$pt][$thnlist])."</td>";
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
				<td colspan=2 align=center><b>Total</td>";
			foreach($arrthn as $thnlist){
				$form.="		
					<td align=right>".@number_format((@$tdtRupiah[$thnlist]+@$tdtRupiahv[$thnlist])/@$tdtFisik[$thnlist])."</td>";
			}
			$form.="
				</tr></table>
			";	
		echo $form;
		
	break;
	
	
	case'global':
		$sortprod=$sortprod2='';
		if($pt!=''){
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
			$sortprod2=" and unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";
		}else{
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN')";
			$sortprod2=" and unit in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN')";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
			   left(periode,4) as tahun from ".$dbname.".keu_saldobulanan where left(periode,4)<='".$thn."' and left(noakun,2) in ('62','61') ".$sortprod." group by left(periode,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtRupiah[$bar['tahun']]=$bar['nilai'];
			$arrthn[$bar['tahun']]=$bar['tahun'];	
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
				   left(periode,4) as tahun from ".$dbname.".keu_saldobulanan where left(periode,4)<='".$thn."' and left(noakun,1) in ('7') ".$sortprod." group by left(periode,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$dtRupiahv[$bar['tahun']]+=$bar['nilai'];
			$arrthn[$bar['tahun']]=$bar['tahun'];	
		}			   
		
		$str="select sum(hasilkerjakg) as kgprod,left(tanggal,4) as tahun from ".$dbname.".kebun_prestasi_vw where left(tanggal,4)<='".$thn."'
		   		".$sortprod2." group by left(tanggal,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtFisik[$bar['tahun']]=$bar['kgprod'];
			$arrthn[$bar['tahun']]=$bar['tahun'];	
		}
		 
		 
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		} 
		 
		$no=-1;
		foreach($arrthn as $thnlist){
			$no++;
			$arrthnlist[$no]=$thnlist;
			$ydata[$no]=(@$dtRupiah[$thnlist]+@$dtRupiahv[$thnlist])/@$dtFisik[$thnlist];
		}
		
		
		$graph = new Graph(580,240);   
		$graph->SetScale('textlin'); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		

		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].'/'.$_SESSION['lang']['kg']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($arrthnlist); 
		$graph->xaxis->SetLabelAngle(20);
	
		$lineplot1=new LinePlot($ydata);
		$lineplot1->SetWeight( 20 );   // Two pixel wide
		$lineplot1->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot1->mark->SetFillColor('red');
		$lineplot1->SetLegend($_SESSION['lang']['hpptbs']);
		$lineplot1->setCenter();
		$graph->Add($lineplot1);
		$lineplot1->value->SetFormat('%d');
		$lineplot1->value->Show();
		$lineplot1->value->SetColor('red');
		
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		
		$graph->StrokeCSIM();
		
		
	break;
}

?>
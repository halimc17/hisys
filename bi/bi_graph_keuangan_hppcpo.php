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
						and left(a.noakun,2) in ('63','64') and tipe='PABRIK' group by left(periode,4),induk";
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
				and left(a.noakun,2) in ('64') and tipe in ('HOLDING','KANWIL') group by left(periode,4),induk";
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
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtFisik[$bar['induk']][$bar['thn']]=$bar['kgprod'];
			$arrthn[$bar['thn']]=$bar['thn'];	
			$kodept[$bar['induk']]=$bar['induk'];		
			@$tdtFisik[$bar['thn']]+=$bar['kgprod'];
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
	
	
	
	case'detail1cpo':
		
		$sortprod=$sortbgt='';
		if($pt!=''){
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
			$sortprod3=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('HOLDING','KANWIL'))";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			$sort=" and kodept='".$pt."' ";
		}else{
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK')";
			$sortprod3=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL'))";
			$judul=$_SESSION['lang']['seluruhpt'];
			$sort="";
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+
					debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
					left(periode,4) as tahun, left(periode,7) as bulan from ".$dbname.".keu_saldobulanan where
					left(periode,4)='".$thn."'  and left(periode,4)!=0 and left(noakun,2) in ('63','64')  ".$sortprod." group by left(periode,6)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$rupiah[substr($bar['bulan'],0,4).'-'.substr($bar['bulan'],4,2)]=$bar['nilai'];
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+
				debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
			    left(periode,4) as tahun,left(periode,7) as bulan from ".$dbname.".keu_saldobulanan where
				left(periode,4)='".$thn."' and left(periode,4)!=0 and left(noakun,2) in ('64') ".$sortprod3." group by left(periode,6)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$rupiahv[substr($bar['bulan'],0,4).'-'.substr($bar['bulan'],4,2)]=$bar['nilai'];
		}			   
		
		$str="select sum(oer) as kgprod,left(tanggal,4) as thn,left(tanggal,7) as bulan from ".$dbname.".pabrik_produksi where left(tanggal,4)='".$thn."'
		   		".$sortprod." group by left(tanggal,7)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$fisik[$bar['bulan']]=$bar['kgprod'];
			//$arrthn[$bar['thn']]=$bar['thn'];	
		}
		
		
		$str=" select sum(kuantitaskontrak/1000) as jumlah,avg(hargasatuan) as harga,left(tanggalkontrak,4) as tahun,left(tanggalkontrak,7) as bulan 
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang='400000001'
				and left(tanggalkontrak,4) = '".$thn."' ".$sort." group by left(tanggalkontrak,7) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$hargacpo[$bar['bulan']]=$bar['harga'];
		}
		 
		
		if(empty($fisik)){
			echo $_SESSION['lang']['dataempty'];exit();
			 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
		} 
		 
		 
		$arrbln=month_inbetween($thn.'-01',$thn.'-12');  
		 
		$no=-1;
		foreach($arrbln as $bln){
			$no++;
			//$arrblnlist[$no]=$thnlist;
			@$data[$bln]=(@$rupiah[$bln]+@$rupiahv[$bln])/@$fisik[$bln];
			if(@$data[$bln]==''){
				$ydata[$no]=0;
			}else{
				$ydata[$no]=(@$rupiah[$bln]+@$rupiahv[$bln])/@$fisik[$bln];
			}
			if(@$hargacpo[$bln]==''){
				$ydata2[$no]=0;
			}
			else{
				$ydata2[$no]=$hargacpo[$bln];
			}
		}
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale('textlin'); //$graph->SetScale('intlin');
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,20,50);
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
		$graph->xaxis->SetLabelAngle(20);
	
		$lineplot1=new LinePlot($ydata);
		$lineplot1->SetWeight( 20 );   // Two pixel wide
		$lineplot1->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot1->mark->SetFillColor('red');
		$lineplot1->SetLegend($_SESSION['lang']['hppcpo']);
		$lineplot1->setCenter();
		$graph->Add($lineplot1);
		$lineplot1->value->SetFormat('%d');
		$lineplot1->value->Show();
		$lineplot1->value->SetColor('red');
		
		$lineplot2=new LinePlot($ydata2);
		$lineplot2->SetWeight( 10 );  
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['harga'].' '.$_SESSION['lang']['cpo']);
		$lineplot2->setCenter();
		//$lineplot2->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('blue');
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		
		$graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	
	case'global':
		$sortprod=$sortbgt='';
		if($pt!=''){
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='PABRIK')";
			$sortprod3=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe in ('HOLDING','KANWIL'))";
			$sort=" and kodept='".$pt."' ";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";
		}else{
			$sortprod=" and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK')";
			$sortprod3=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe in ('HOLDING','KANWIL'))";
			$sort=" ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
			   left(periode,4) as tahun from ".$dbname.".keu_saldobulanan where left(periode,4)<='".$thn."'  and left(periode,4)!=0 and left(noakun,2) in ('63','64')  ".$sortprod." group by left(periode,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tahun']!=''){
				$dtRupiah[$bar['tahun']]=$bar['nilai'];
				$arrthn[$bar['tahun']]=$bar['tahun'];	
			}
		}
		
		$str="select sum(debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12) as nilai,
			   left(periode,4) as tahun from ".$dbname.".keu_saldobulanan where left(periode,4)<='".$thn."' and left(periode,4)!=0 and left(noakun,2) in ('64') ".$sortprod3." group by left(periode,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['tahun']!=''){
				@$dtRupiahv[$bar['tahun']]+=$bar['nilai'];
				$arrthn[$bar['tahun']]=$bar['tahun'];	
			}
		}			   
		
		$str="select sum(oer) as kgprod,left(tanggal,4) as thn from ".$dbname.".pabrik_produksi where left(tanggal,4)<='".$thn."'
		   		".$sortprod." group by left(tanggal,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$dtFisik[$bar['thn']]=$bar['kgprod'];
			$arrthn[$bar['thn']]=$bar['thn'];	
		}
		 
		 
		$str=" select sum(kuantitaskontrak/1000) as jumlah,avg(hargasatuan) as harga,left(tanggalkontrak,4) as tahun 
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang='400000001'
				and left(tanggalkontrak,4) <= '".$thn."' ".$sort." group by left(tanggalkontrak,4) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$hargacpo[$bar['tahun']]=$bar['harga'];
		}
		 
		 
		if(empty($dtFisik)){
			echo $_SESSION['lang']['dataempty'];exit();
		} 
		 
		$no=-1;
		foreach($arrthn as $thnlist){
			$no++;
			$arrthnlist[$no]=$thnlist;
			$ydata[$no]=(@$dtRupiah[$thnlist]+@$dtRupiahv[$thnlist])/@$dtFisik[$thnlist];
			$ydata2[$no]=@$hargacpo[$thnlist];
			$targ[$no]='?method=detail1cpo&pt='.$pt.'&thn='.$thnlist;
			$alts[$no]='Click to Detail';
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
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah']);
		 
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
		$lineplot1->SetLegend($_SESSION['lang']['hppcpo']);
		$lineplot1->setCenter();
		$lineplot1->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot1);
		$lineplot1->value->SetFormat('%d');
		$lineplot1->value->Show();
		$lineplot1->value->SetColor('red');
		
		
		$lineplot2=new LinePlot($ydata2);
		$lineplot2->SetWeight( 10 );  
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['harga'].' '.$_SESSION['lang']['cpo']);
		$lineplot2->setCenter();
		$lineplot2->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('blue');
		
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		
		$graph->StrokeCSIM();
		
		
	break;
}

?>
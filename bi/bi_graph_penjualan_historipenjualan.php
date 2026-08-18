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
$whbrg="kelompokbarang='400'";
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);
switch($method)
{
	
	
	case'detailgraph':
		$tahun = array();
		$kodept = array();
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select sum(kuantitaskontrak/1000) as jumlah,avg(hargasatuan) as harga,left(tanggalkontrak,4) as tahun,kodept,kodebarang
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang in ('400000001','400000002')
				and left(tanggalkontrak,4) <= '".$thn."'  group by left(tanggalkontrak,4),kodebarang,kodept ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['kodept']]=$bar['kodept'];
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodebarang[$bar['kodebarang']]=$bar['kodebarang'];
				@$jumlah[$bar['kodept']][$bar['tahun']][$bar['kodebarang']]=$bar['jumlah'];
				@$harga[$bar['kodept']][$bar['tahun']][$bar['kodebarang']]=$bar['harga'];
				@$tjumlah[$bar['tahun']][$bar['kodebarang']]+=$bar['jumlah'];
				@$tharga[$bar['tahun']][$bar['kodebarang']]+=$bar['harga'];
		}
		
		@$jpt=count($kodept);
		@$jbrg=count($kodebarang);
		@$jthn=count($tahun);
		
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=4  align=center>No</td>
						<td rowspan=4  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*$jbrg*2).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($tahun as $thnlist){
						$form.="<td  align=center colspan=".($jbrg*2).">".$thnlist."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=$jbrg;$i++){
						foreach($kodebarang as $kdbrg){
							$form.="<td align=center colspan=2>".$nmbrg[$kdbrg]."</td>";
						}
					}
					$form.="</tr>";
					$form.="<tr>";
					for($i=1;$i<=$jbrg;$i++){
						foreach($kodebarang as $kdbrg){
							$form.="<td align=center>".$_SESSION['lang']['jumlah']." (".$_SESSION['lang']['Ton'].")</td>";
							$form.="<td align=center>".$_SESSION['lang']['harga']."</td>";
						}
					}	
					$form.="</tr>";
					
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
					<td>".$pt." - ".@$nmorg[$pt]."</td>
					";
			foreach($tahun as $thnlist){
				foreach($kodebarang as $kdbrg){
					$form.="		
						<td align=right>".@number_format($jumlah[$pt][$thnlist][$kdbrg])."</td>
						<td align=right>".@number_format($harga[$pt][$thnlist][$kdbrg])."</td>";
				}
				
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($tahun as $thnlist){
				foreach($kodebarang as $kdbrg){
					$form.="		
					<td align=right>".@number_format($tjumlah[$thnlist][$kdbrg])."</td>
					<td align=right>".@number_format($tharga[$thnlist][$kdbrg]/$jpt)."</td>";
				}
				
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	



	case'detail1cpo':
		
		if($pt!=''){
			$sort=" and kodept='".$pt."' ";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";	
		}
		else{
			$sort="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		//kodept
	
		$arrbln=month_inbetween($thn.'-01',$thn.'-12');
		
		$str=" select sum(kuantitaskontrak/1000) as jumlah,avg(hargasatuan) as harga,left(tanggalkontrak,7) as bulan 
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang='400000001'
				and left(tanggalkontrak,4) = '".$thn."' ".$sort." group by left(tanggalkontrak,7) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			//$arrbln[$bar['bulan']]=$bar['bulan'];
			$jumlahcpo[$bar['bulan']]=$bar['jumlah'];
			$hargacpo[$bar['bulan']]=$bar['harga'];
		}
		
	
		if(empty($arrbln)){
			echo $_SESSION['lang']['dataempty'];exit();
			 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
		} 
		 
		$no=-1;
		foreach($arrbln as $bln){
			$no++;
			if(@$jumlahcpo[$bln]==''){
				@$jcpo[$no]=0;
			}else{
				@$jcpo[$no]=@$jumlahcpo[$bln];
			}
			
			if(@$hargacpo[$bln]==''){
				@$hcpo[$no]=0;
			}else{
				@$hcpo[$no]=@$hargacpo[$bln];
			}
		}
		
		
		// $graph = new Graph(580,220);   
		// $graph->SetScale('textlin'); //$graph->SetScale('intlin');
		// $graph->SetShadow();
		// $graph->img->SetMargin(60,20,20,50);
		
		$graph = new Graph(590,220);   
		$graph->SetScale("textlin"); 
		$graph->SetY2Scale("lin");
		$graph->SetShadow();
		$graph->img->SetMargin(80,60,20,50);
		$graph->yaxis->scale->SetGrace(10);
		$graph->y2axis->scale->SetGrace(50);  
		
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		$graph->yaxis->HideZeroLabel(); 
		$graph->xaxis->HideZeroLabel(); 
		 
		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
		$graph->xaxis->SetLabelAngle(45);
		
		$txt = new Text($_SESSION['lang']['Ton'].' '.$_SESSION['lang']['cpo']);
		$txt->SetPos(0.02,0.09,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);

		$txt = new Text($_SESSION['lang']['harga'].'('.$_SESSION['lang']['rupiah'].')');
		$txt->SetPos(0.83,0.1,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);
		
		 
		$lineplot=new LinePlot($jcpo);
		$lineplot->SetWeight( 10 );  
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['cpo']);
		$lineplot->setCenter();
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('red');
		 

		$lineplot2=new LinePlot($hcpo);
		$lineplot2->SetWeight( 10 );  
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['harga'].' '.$_SESSION['lang']['cpo']);
		$lineplot2->setCenter();
		$graph->AddY2($lineplot2); 
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('blue');
		
		
	
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(4); 
		 
		// Display the graph
		$graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;	
	
	
	
	
	
	
	
	
	
	case'global':
		$sort='';
		if($pt!=''){
			$sort=" and kodept='".$pt."' ";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";	
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		//kodept
	
		$nocpo=$noker=-1;
		$str=" select sum(kuantitaskontrak/1000) as jumlah,avg(hargasatuan) as harga,left(tanggalkontrak,4) as tahun 
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang='400000001'
				and left(tanggalkontrak,4) <= '".$thn."' ".$sort." group by left(tanggalkontrak,4) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nocpo++;
			$arrthn[$nocpo]=$bar['tahun'];
			$jumlahcpo[$nocpo]=$bar['jumlah'];
			$hargacpo[$nocpo]=$bar['harga'];
			$targ[$nocpo]='?method=detail1cpo&pt='.$pt.'&thn='.$bar['tahun'].'&pks='.$pks;
			$alts[$nocpo]=$_SESSION['lang']['klikdetail'];
		}
		
		
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

		$graph->yaxis->title->SetMargin(28);
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
		
		$txt = new Text($_SESSION['lang']['Ton'].' '.$_SESSION['lang']['cpo']);
		$txt->SetPos(0.02,0.09,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);

		$txt = new Text($_SESSION['lang']['harga'].'('.$_SESSION['lang']['rupiah'].')');
		$txt->SetPos(0.83,0.1,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);
		
		 
		$lineplot=new LinePlot($jumlahcpo);
		$lineplot->SetWeight( 10 );  
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['cpo']);
		$lineplot->setCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot);
		//$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('red');
		 

		$lineplot2=new LinePlot($hargacpo);
		$lineplot2->SetWeight( 10 );  
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot2->mark->SetFillColor('blue');
		$lineplot2->SetLegend($_SESSION['lang']['harga'].' '.$_SESSION['lang']['cpo']);
		$lineplot2->setCenter();
		$lineplot2->SetCSIMTargets($targ,$alts); 
		$graph->AddY2($lineplot2); 
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('blue');
		
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(4); 
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
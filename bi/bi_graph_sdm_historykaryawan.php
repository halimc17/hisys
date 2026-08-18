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

switch($method){
	case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str="select count(karyawanid) as karymasuk,left(tanggalmasuk,4) as thndt,kodeorganisasi from ".$dbname.".datakaryawan
			  where  left(tanggalmasuk,4)!='0000' and tipekaryawan!='4' 
			  group by left(tanggalmasuk,4),kodeorganisasi order by left(tanggalmasuk,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$kodept[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
			$tahun[$bar['thndt']]=$bar['thndt'];
			$karin[$bar['kodeorganisasi']][$bar['thndt']]=$bar['karymasuk'];
			@$tkarin[$bar['thndt']]+=$bar['karymasuk'];
		
		}

		$str="select count(karyawanid) as karymasuk,left(tanggalkeluar,4) as thndt,kodeorganisasi from ".$dbname.".datakaryawan
			  where left(tanggalkeluar,4)!='0000' and tipekaryawan!='4'  
			  group by left(tanggalkeluar,4),kodeorganisasi order by left(tanggalkeluar,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$kodept[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
			$tahun[$bar['thndt']]=$bar['thndt'];
			$karout[$bar['kodeorganisasi']][$bar['thndt']]=$bar['karymasuk'];
			@$tkarout[$bar['thndt']]+=$bar['karymasuk'];
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
						<td align=center>".$_SESSION['lang']['masuk']."</td>
						<td align=center>".$_SESSION['lang']['keluar']."</td>
						";
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
					<td>".$pt." - ".$nmorg[$pt]."</td>
					";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right>".@number_format($karin[$pt][$thnlist])."</td>
					<td align=right>".@number_format($karout[$pt][$thnlist])."</td>";
			}
			$form.="
				</tr>
			";
			//$urutunit=0;
			
		}		
		$form.="
				<tr class=rowcontent>
				<td colspan=2 align=center><b>Total</td>";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right><b>".@number_format($tkarin[$thnlist])."</td>
					<td align=right><b>".@number_format($tkarout[$thnlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
		echo $form;
		
	break;
	
	
	
	case'global':
		$sortp="and kodeorganisasi in (select kodeorganisasi  from ".$dbname.".organisasi where tipe='PT') ";
		$nmpt=$_SESSION['lang']['all']."";
		if($pt!=''){
			$sortp="and kodeorganisasi='".$pt."'";			
			$nmpt="PT ". $pt;
		}
	
		$data1y=array();
		

		$str="select count(karyawanid) as karymasuk,left(tanggalmasuk,4) as thndt from ".$dbname.".datakaryawan
			  where left(tanggalmasuk,4)<= '".$thn."' ".$sortp." and left(tanggalmasuk,4)!='0000' and tipekaryawan!='4' 
			  group by left(tanggalmasuk,4) order by left(tanggalmasuk,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrthn[$bar['thndt']]=$bar['thndt'];
			$karin[$bar['thndt']]=$bar['karymasuk'];
		
		}

		$str="select count(karyawanid) as karymasuk,left(tanggalkeluar,4) as thndt from ".$dbname.".datakaryawan
			  where left(tanggalkeluar,4)<= '".$thn."' ".$sortp." and left(tanggalkeluar,4)!='0000' and tipekaryawan!='4'  
			  group by left(tanggalkeluar,4) order by left(tanggalkeluar,4)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$arrthn[$bar['thndt']]=$bar['thndt'];
			$karout[$bar['thndt']]=$bar['karymasuk'];
		}
		
		
		$no=-1;
		foreach($arrthn as $thnlist){
			$no++;
			$arrthnlist[$no]=$thnlist;
			if(@$karin[$thnlist]!=''){
				$data1y[$no]=$karin[$thnlist];
			}else{
				$data1y[$no]=0;
			}
			if(@$karout[$thnlist]!=''){
				$data2y[$no]=$karout[$thnlist];
			}else{
				$data2y[$no]=0;
			}
		}
	
		
		$graph = new Graph(580,240);   
		$graph->SetScale("textlin");

		 
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);


	//	array_multisort($arrthnlist,SORT_ASC)
		$graph->xaxis->SetTickLabels($arrthnlist);
		$graph->xaxis->SetLabelAngle(75); 
	
		$b1plot = new BarPlot($data1y);
		$b2plot = new BarPlot($data2y);
	

		$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
		
		 
		$b1plot->SetLegend($_SESSION['lang']['masuk']);
		$b2plot->SetLegend($_SESSION['lang']['keluar']);

		$graph->Add($gbplot);
		 
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(12);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['orang']);
				
		$graph->title->Set($nmpt);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);


		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 		 
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3);  
		 

		$graph->StrokeCSIM();
		
	
	break;
}



?>
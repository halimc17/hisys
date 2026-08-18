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
		
		$str="select count(karyawanid) as karymasuk,kodeorganisasi from ".$dbname.".datakaryawan
			  where  tipekaryawan!='4' and left(tanggalmasuk,4)='".$thn."'
			  group by kodeorganisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$kodept[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
						$tahun[$thn]=$thn;
			$karin[$bar['kodeorganisasi']][$thn]=$bar['karymasuk'];
			@$tkarin[$thn]+=$bar['karymasuk'];
		
		}

		$str="select count(karyawanid) as karymasuk,kodeorganisasi from ".$dbname.".datakaryawan
			  where tipekaryawan!='4'  and left(tanggalkeluar,4)='".$thn."'
			  group by kodeorganisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$kodept[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
			$tahun[$thn]=$thn;
			$karout[$bar['kodeorganisasi']][$thn]=$bar['karymasuk'];
			@$tkarout[$thn]+=$bar['karymasuk'];
		}
		
		if(empty($tahun)){
			echo $_SESSION['lang']['dataempty'];exit();
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
		
		
		$no=-1;
		$str="select count(karyawanid) as karymasuk,kodeorganisasi from ".$dbname.".datakaryawan
			  where left(tanggalmasuk,4)= '".$thn."' ".$sortp." and left(tanggalmasuk,4)!='0000' and tipekaryawan!='4' group by kodeorganisasi order by kodeorganisasi desc limit 10";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row<1){
			$no++;
			$data1y[0]=0;
		}else{
			while ($bar = $res->fetch()){
				$no++;
				$data1y[$no]=$bar['karymasuk'];
				$arrkebun[$no]=$bar['kodeorganisasi'];
				$targ1[$no]="";
				$alts1[$no]="";
				
			}
		}

		$nobgt=-1;
		$str="select count(karyawanid) as karymasuk,kodeorganisasi  from ".$dbname.".datakaryawan
			  where left(tanggalkeluar,4)= '".$thn."' ".$sortp." and left(tanggalkeluar,4)!='0000' and tipekaryawan!='4'  group by kodeorganisasi order by kodeorganisasi desc limit 10";				
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row<1){
			$nobgt++;
			$data2y[0]=0;
		}else{
				while ($bar = $res->fetch()){
				$nobgt++;
				$arrkebun[$nobgt]=$bar['kodeorganisasi'];
				$data2y[$nobgt]=$bar['karymasuk'];
				$targ2[$nobgt]="";
				$alts2[$nobgt]="";
				
			}
		}
		
		
		//ini untuk mengeluarkan data kosong
		if($no<$nobgt){
			for($i=$no;$i<=$nobgt;$i++){
				$data1y[$i]=0;
			}
		}else{
			for($i=$nobgt;$i<=$no;$i++){
				$data2y[$i]=0;
			}
		}
		
	
		
		if(empty($arrkebun)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		
	
		// Create the graph. These two calls are always required
		$graph = new Graph(580,240);   
		$graph->SetScale("textlin");

		 
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);



		$graph->xaxis->SetTickLabels($arrkebun);
		 
		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		//$b1plot->SetFillColor("orange");
		$b2plot = new BarPlot($data2y);
		$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
		
		
		//$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
		//$graph->xaxis->SetTickLabels(1,1,1,1,1);
		 
		$b1plot->SetLegend('Masuk');
		$b2plot->SetLegend('Keluar');


		$b1plot->SetCSIMTargets(@$targ1,@$alts1);    	
		$b2plot->SetCSIMTargets(@$targ2,@$alts2);    	

		// ...and add it to the graPH
		$graph->Add($gbplot);
		 
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set("PT");
		$graph->yaxis->title->Set("Personil");
				
		$graph->title->Set($nmpt);
		$graph->subtitle->Set('Tahun '.$thn);

		#legend

		 
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		// Display the graph
		$graph->StrokeCSIM();
		
	
	break;
}
?>
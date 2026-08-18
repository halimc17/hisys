<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../lib/zLib.php');
include ('../jpgraph/jpgraph.php');
include ('../jpgraph/jpgraph_pie.php');
include ('../jpgraph/jpgraph_pie3d.php');
 
$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$id = checkPostGet('id','');
$pks = checkPostGet('pks','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmsup=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier');

switch($method)
{
	case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select b.induk,a.millcode as kodeorg,(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw a
		left join ".$dbname.".organisasi b on a.millcode=b.kodeorganisasi
		where kodebarang='400000003' and millcode !='EXTM' and intex in ('1','2') and left(tanggal,4) <= '".$thn."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
				$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$tbsinpt[$bar['induk']][$bar['tahun']]+=$bar['beratbersih'];
			@$tbsinunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['beratbersih'];
			@$tbsintot[$bar['tahun']]+=$bar['beratbersih'];
		}
		
		$str=" select b.induk,a.millcode as kodeorg,(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw a
		left join ".$dbname.".organisasi b on a.millcode=b.kodeorganisasi
		where kodebarang='400000003'  and millcode !='EXTM' and intex in ('1','2') and left(tanggal,4) <= '".$thn."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
				$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$tbsexpt[$bar['induk']][$bar['tahun']]+=$bar['beratbersih'];
			@$tbsexunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['beratbersih'];
			@$tbsextot[$bar['tahun']]+=$bar['beratbersih'];
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
						<td align=center>".$_SESSION['lang']['internal']."</td>
						<td align=center>".$_SESSION['lang']['external']."</td>
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
					<td align=right>".@number_format($tbsinpt[$pt][$thnlist])."</td>
					<td align=right>".@number_format($tbsexpt[$pt][$thnlist])."</td>";
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
								<td align=right>".@number_format($tbsinunit[$pt][$unit][$thnlist])."</td>
								<td align=right>".@number_format($tbsexunit[$pt][$unit][$thnlist])."</td>";
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
					<td align=right><b>".@number_format($tbsintot[$thnlist])."</td>
					<td align=right><b>".@number_format($tbsextot[$thnlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
		echo $form;
		
	break;
	
	
	
	
	
	case'detail1external':
	
		$sort='';
		if($pt!='')
		{
			if($pks!=''){
				$sort=" and millcode = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$data = array();
		$legend=array();
		$str=" select sum(beratbersih/1000) as beratbersih,kodecustomer from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003'  and millcode !='EXTM' and intex=0 and left(tanggal,4) <= '".$thn."' ".$sort." group by kodecustomer order by beratbersih desc limit 10";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['beratbersih'];
			$legend[$no]=$nmsup[$bar['kodecustomer']];
		}

 
		// Create the Pie Graph.
		$graph = new PieGraph(590,220);
		$graph->SetShadow();
		 
		// Set A title for the plot
		$graph->title->Set($judul);
		$graph->subtitle->Set('Tahun '.$thn);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->legend->Pos(0.1,0.2);
		 
		 
		// Create pie plot
		$p1 = new PiePlot($data);
		$p1->SetCenter(0.5,0.9);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       
		

		$p1->SetLegends($legend);
		$graph->Add($p1);
		$graph->legend->SetPos(0.7,0.95,'right','bottom');
		$graph->legend->SetColumns(1);			
		 
		// Add and stroke
		//$graph->Add($p1);	
			
			

        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	

	
	case'detail1internal':
	
	
		$sort='';
		if($pt!=''){
			if($pks!=''){
				$sort=" and millcode = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
	
	
		$sort='';
		if($pt!='')
		{
			if($pks!=''){
				$sort=" and millcode = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$data = array();
		$legend=array();
		$str=" select sum(beratbersih/1000) as beratbersih,kodeorg from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003'  and millcode !='EXTM' and intex in ('1','2') and left(tanggal,4) <= '".$thn."' ".$sort." group by kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['beratbersih'];
			$legend[$no]=$bar['kodeorg'];
		}
	

		// $graph = new PieGraph(590,220);
		// $graph->SetShadow();
	
	
		// Create the Pie Graph.
		$graph = new PieGraph(590,220);
		$graph->SetShadow();
		 
		// Set A title for the plot
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->legend->Pos(0.1,0.2);
		 
		 
		// Create pie plot
		$p1 = new PiePlot($data);
		$p1->SetCenter(0.5,0.55);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       

		$p1->SetLegends($legend);
		$graph->Add($p1);
		$graph->legend->SetPos(0.75,0.95,'right','bottom');
		$graph->legend->SetColumns(2);			
		 
		// Add and stroke
		//$graph->Add($p1);	
			
			

        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	
	#############################################################
	#############################################################
	
	
	
	
	
	
	
	
	
	
	case'global':
		$sort='';
		if($pt!=''){
			if($pks!=''){
				$sort=" and millcode = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
	
		$str=" select sum(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003'  and millcode !='EXTM' and intex in ('1','2') and left(tanggal,4) <= '".$thn."' ".$sort."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data1=$bar['beratbersih'];
			$legend1=$_SESSION['lang']['internal'];
		}
	
		$str=" select sum(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003'  and millcode !='EXTM' and intex in ('0') and left(tanggal,4) <= '".$thn."' ".$sort."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data2=$bar['beratbersih'];
			$legend2=$_SESSION['lang']['external'];	
		}
	
	
	
		if($data1=='' || $data2==''){
			echo $_SESSION['lang']['dataempty'];exit();
		}
	
		$data = array($data1,$data2);
		 
		$graph = new PieGraph(590,240);
		$graph->SetShadow();
	
		 
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		
		$targ=array('?method=detail1internal&pt='.$pt.'&thn='.$thn.'&pks='.$pks,'?method=detail1external&pt='.$pt.'&thn='.$thn.'&pks='.$pks);
		$alts=array("Click to drill Internal","Click to drill External");
		

		$p1 = new PiePlot3D($data);
		$p1->SetAngle(30);
		$p1->SetSize(0.5);
		$p1->SetCenter(0.5);
	
		$p1->SetLegends(array($legend1,$legend2));
		$p1->SetCSIMTargets($targ,$alts);
		$graph->Add($p1);
				
				// Display the graph
		$graph->StrokeCSIM();
			
	break;
}

?>
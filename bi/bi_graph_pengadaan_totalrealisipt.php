<?php // content="text/plain; charset=utf-8"
include('master_validation.php');
include('../config/connection.php');
include('../lib/nangkoelib.php');
require_once ('../jpgraph/jpgraph.php');
require_once ('../jpgraph/jpgraph_bar.php');
include('../lib/zLib.php');
include ('../jpgraph/jpgraph_pie.php');
include ('../jpgraph/jpgraph_pie3d.php');

$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$pks = checkPostGet('pks','');
$kelbrg = checkPostGet('kelbrg','');



$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmkelbrg=makeOption($dbname,'log_5klbarang','kode,kelompok');
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmast=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
switch($method)
{
	
	case'detailgraph':

		// $stylehidden = "style='display:none'";	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
					
		$str="select ((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg,left(tanggal,4) as tahun from 
                 ".$dbname.".log_po_vw where left(kodebarang,1)!='8' and left(tanggal,4)<='".$thn."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumlah[$bar['kodeorg']][$bar['tahun']]+=$bar['total'];
			$arrpt[$bar['kodeorg']]=$bar['kodeorg'];
			$arrthn[$bar['tahun']]=$bar['tahun'];
		}
		
		
		
		
		////////////////////////////////
		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg,left(tanggal,4) as tahun from 
                 ".$dbname.".log_po_vw where  left(tanggal,4) <= '".$thn."' and left(kodebarang,1) not in ('8','9') 
				 group by kodeorg,left(tanggal,4)";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumlahkap[$bar['kodeorg']][$bar['tahun']]+=$bar['total'];
			$arrpt[$bar['kodeorg']]=$bar['kodeorg'];
			$arrthn[$bar['tahun']]=$bar['tahun'];
		}

				 
		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg,left(tanggal,4) as tahun from 
                 ".$dbname.".log_po_vw where left(tanggal,4)='".$thn."' and left(kodebarang,1) in ('9') and left(kodebarang,1) !='8' 
				 group by kodeorg,left(tanggal,4)";					 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumlahnonkap[$bar['kodeorg']][$bar['tahun']]+=$bar['total'];
			$arrpt[$bar['kodeorg']]=$bar['kodeorg'];
			$arrthn[$bar['tahun']]=$bar['tahun'];
		}
	
		///////////////////////////
		
		
		
		
		
		//nonkap
		$str="SELECT rupiah/1000000 as total,induk,left(a.kodeorg,4) as unit,tahunbudget FROM ".$dbname.".bgt_budget_detail a 
				left join ".$dbname.".organisasi b on left(a.kodeorg,4)=b.kodeorganisasi 
				WHERE substr(kodebudget,1,1)='M' and tahunbudget <= '".$thn."'";			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$bgtdet[$bar['induk']][$bar['tahunbudget']]+=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
			$arrthn[$bar['tahunbudget']]=$bar['tahunbudget'];
		}
     
		//kap
		$str="SELECT (harga/1000000) as total,induk,kodeunit,tahunbudget FROM ".$dbname.".bgt_kapital_vw a 
				left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi WHERE tahunbudget <= '".$thn."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthn[$bar['tahunbudget']]=$bar['tahunbudget'];
			@$bgtkap[$bar['induk']][$bar['tahunbudget']]+=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
		}		
		
		
		$jthn=count($arrthn);
		$spanthn=$jthn*2*3;
		$spanlistthn=$jthn*2;
		
		array_multisort($arrthn,SORT_ASC);
		
		if(empty($arrpt)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
	
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td rowspan=4 align=center>No</td>
						<td rowspan=4 align=center>".$_SESSION['lang']['unit']."</td>
						<td align=center colspan='".$spanthn."'>".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($arrthn as $thnlist){
						$form.="<td colspan='6' align=center>".$thnlist."</td>";
					}
					$form.="</tr><tr>";
					foreach($arrthn as $thnlist){
						$form.="
							<td align=center colspan=3>".$_SESSION['lang']['budget']."</td>
							<td align=center colspan=3>".$_SESSION['lang']['realisasi']."</td>";
					}
					$form.="</tr><tr>";
					for($i=1;$i<=$spanlistthn;$i++){
						$form.="
							<td>".$_SESSION['lang']['kapital']."</td>
							<td>".$_SESSION['lang']['nonkapital']."</td>
							<td>".$_SESSION['lang']['jumlah']."</td>";
					}
					$form.="	
					</thead>";
	
		foreach($arrpt as $pt){
			@$no+=1;
			$form.="
				<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$pt."</td>";
				foreach($arrthn as $thnlist){
					//bgt
					$form.="	
						<td align=right>".@number_format($bgtkap[$pt][$thnlist])."</td>
						<td align=right>".@number_format($bgtdet[$pt][$thnlist])."</td>					
						<td align=right>".@number_format($bgtkap[$pt][$thnlist]+$bgtdet[$pt][$thnlist])."</td>
					";
					
					//real
					$form.="	
					<td align=right>".@number_format($jumlahkap[$pt][$thnlist])."</td>
					<td align=right>".@number_format($jumlahnonkap[$pt][$thnlist])."</td>
					<td align=right>".@number_format($jumlahkap[$pt][$thnlist]+$jumlahnonkap[$pt][$thnlist])."</td>";
				}
				$form.="
				</tr>";
		}
					
		echo $form;	


	break;	
	
	
	case'beliperpt':
		
		$judul=$_SESSION['lang']['pembelianBarang'].' '.$_SESSION['lang']['pt'].' '.$pt;
	

		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan) as total,kodeorg from 
                 ".$dbname.".log_po_vw where kodeorg='".$pt."'
                 and left(tanggal,4)='".$thn."' and left(kodebarang,1) not in ('8','9')";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data1=$bar['total'];
			$legend1=$_SESSION['lang']['nonkapital'];
		}

				 
		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan) as total,kodeorg from 
                 ".$dbname.".log_po_vw where kodeorg='".$pt."'
                 and left(tanggal,4)='".$thn."' and left(kodebarang,1) in ('9') and left(kodebarang,1) !='8'";				 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data2=$bar['total'];
			$legend2=$_SESSION['lang']['kapital'];	
		}
	
	
	
		if($data1=='' || $data2==''){
			echo $_SESSION['lang']['dataempty'];exit();
		}
	
		$data = array($data1,$data2);
		 
		$graph = new PieGraph(590,220);
		$graph->SetShadow();
	
		 
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		
		$targ=array('?method=nonkapital&pt='.$pt.'&thn='.$thn,'?method=kapital&pt='.$pt.'&thn='.$thn);       
		$alts=array($_SESSION['lang']['nonkapital'],$_SESSION['lang']['kapital']);
		

		$p1 = new PiePlot3D($data);
		$p1->SetAngle(30);
		$p1->SetSize(0.5);
		$p1->SetCenter(0.5);
		$p1->SetLegends(array($legend1,$legend2));
		$p1->SetCSIMTargets($targ,$alts);
		$graph->Add($p1);
				
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3);  		
				// Display the graph
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	case'bgtnonkapital':
		
		$judul=$_SESSION['lang']['nonkapital'].' '.$_SESSION['lang']['pt'].' '.$pt;
	
		$data = array();
		$legend=array();
		$str="SELECT sum(rupiah/1000000) as total,left(kodebarang,3) as kelbrg FROM ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b on
				left(a.kodeorg,4)=b.kodeorganisasi WHERE substr(kodebudget,1,1)='M' and tahunbudget = '".$thn."' and induk='".$pt."'
				group by left(kodebarang,3) order by sum(rupiah/1000000) desc limit 0,10";	 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['total'];
			$legend[$no]=$bar['kelbrg'];
			$targ[$no]='?method=bgtkelbrg&pt='.$pt.'&kelbrg='.$bar['kelbrg'].'&thn='.$thn;
			$alts[$no]=$_SESSION['lang']['klikdetail'].' '.$nmkelbrg[$bar['kelbrg']];
		}
		
	
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
		$p1->SetCenter(0.5,0.1);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       
		$p1->SetCSIMTargets($targ,$alts);
		$p1->SetLegends($legend);
	
		$graph->Add($p1);
		$graph->legend->SetPos(0.05,0.95,'left','bottom');
		$graph->legend->SetColumns(1);			
		 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;

	
	case'bgtkapital':
		//indra
		$judul=$_SESSION['lang']['kapital'].' '.$_SESSION['lang']['pt'].' '.$pt;
	
		$data = array();
		$legend=array();
		
		$str="SELECT sum(harga/1000000) as total,jeniskapital FROM ".$dbname.".bgt_kapital_vw a 
				left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi
				WHERE tahunbudget = '".$thn."'  and induk='".$pt."' group by jeniskapital";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['total'];
			$legend[$no]=$bar['jeniskapital'];
			$targ[$no]='?method=bgtkelbrgkapital&pt='.$pt.'&kelbrg='.$bar['jeniskapital'].'&thn='.$thn;
			$alts[$no]=$bar['jeniskapital'];
			$alts[$no]=$_SESSION['lang']['klikdetail'].' '.$nmast[$bar['jeniskapital']];
		}
		
	
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
		$p1->SetCenter(0.5,0.1);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       
		$p1->SetCSIMTargets($targ,$alts);
		$p1->SetLegends($legend);
	
		$graph->Add($p1);
		$graph->legend->SetPos(0.05,0.95,'left','bottom');
		$graph->legend->SetColumns(1);			
		 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	case'bgtperpt':
		
		$str="SELECT sum(rupiah/1000000) as total,kodeorganisasi FROM ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b on
				left(a.kodeorg,4)=b.kodeorganisasi WHERE substr(kodebudget,1,1)='M' and tahunbudget = '".$thn."' and induk='".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data1=$bar['total'];
			$legend1=$_SESSION['lang']['nonkapital'];
		}
		
		$str="SELECT sum(harga/1000000) as total,kodeorganisasi FROM ".$dbname.".bgt_kapital_vw a 
				left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi
				WHERE tahunbudget = '".$thn."'  and induk='".$pt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$data2=$bar['total'];
			$legend2=$_SESSION['lang']['kapital'];
		}
     
		$judul=$_SESSION['lang']['budget'].' '.$_SESSION['lang']['pt'].' '.$pt;
			
		if($data1=='' && $data2==''){
			echo $_SESSION['lang']['dataempty'];exit();
		}
	
		$data = array($data1,$data2);
		 
		$graph = new PieGraph(590,220);
		$graph->SetShadow();
	
		 
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		
		$targ=array('?method=bgtnonkapital&pt='.$pt.'&thn='.$thn,'?method=bgtkapital&pt='.$pt.'&thn='.$thn);       
		$alts=array($_SESSION['lang']['nonkapital'],$_SESSION['lang']['kapital']);
		

		$p1 = new PiePlot3D($data);
		$p1->SetAngle(30);
		$p1->SetSize(0.5);
		$p1->SetCenter(0.5);
		$p1->SetLegends(array($legend1,$legend2));
		$p1->SetCSIMTargets($targ,$alts);
		$graph->Add($p1);
				
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3);  		
				// Display the graph
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	break;
	
	
	
	case'piegraphbudget':
		if($pt==''){
			$sortb="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortb=" and induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}

		
		$data = array();
		$legend=array();
		
		
		$str="SELECT sum(rupiah/1000000) as total,induk FROM ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b on
				left(a.kodeorg,4)=b.kodeorganisasi WHERE substr(kodebudget,1,1)='M' and tahunbudget = '".$thn."' ".$sortb."  group by induk ";
				
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bgtdet[$bar['induk']]=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
		}
     
			
		$str="SELECT sum(harga/1000000) as total,induk FROM ".$dbname.".bgt_kapital_vw a left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi
				WHERE tahunbudget = '".$thn."' ".$sortb."  group by induk";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bgtkap[$bar['induk']]=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
		}
		
		if(empty($arrpt)){
			echo $_SESSION['lang']['dataempty'];exit();
		}

		$no=-1;		
		foreach($arrpt as $listpt){
			$no++;
			$data[$no]=@$bgtdet[$listpt]+@$bgtkap[$listpt];
			$legend[$no]=$listpt;
		}		
		
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
	
	
	
	case'piegraphrealisasi':
			if($pt==''){
			$sortp="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortp="and kodeorg='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}

		
		$data = array();
		$legend=array();
		$str="select sum((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg from 
		".$dbname.".log_po_vw where left(kodebarang,1)!='8' and left(tanggal,4)='".$thn."' ".$sortp." group by kodeorg ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['total'];
			$legend[$no]=$bar['kodeorg'];
		}
		
	
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

        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	case'bgtkelbrgkapital'://indra
	
		$judul=$_SESSION['lang']['budget'].' '.$nmast[$kelbrg].' '.$_SESSION['lang']['pt'].' '.$pt;

		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td colspan=5 align=center>".$judul."</td>
					</tr>
					<tr>
						<td align=center>".$_SESSION['lang']['nourut']."</td>
						<td>".$_SESSION['lang']['unit']."</td>
						<td>".$_SESSION['lang']['nama']."</td>
						<td>".$_SESSION['lang']['harga']."</td>
					</tr>
				</thead><tbody>";


				
		$str="SELECT harga,namatipe,kodeunit FROM ".$dbname.".bgt_kapital_vw a 
				left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi
				WHERE tahunbudget = '".$thn."'  and induk='".$pt."' and jeniskapital='".$kelbrg."'";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$form.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$bar['kodeunit']."</td>
					<td>".$bar['namatipe']."</td> 
					<td align=right>".number_format($bar['harga'],0)."</td>
				</tr>";
			@$totjum+=$bar['harga'];
		}
		$form.="<tr class=rowcontent><td colspan=3><b>".$_SESSION['lang']['total']."</td>";
		$form.="<td align=right><b>".number_format($totjum,0)."</td></tr>";
		$form.="</tbody></table>";
        echo $form;
		echo"<br><a href=javascript:history.back(-1) style='font-size:16px'>Back</a>";   
	
	break;		
	
	
	
	case'bgtkelbrg':
	
		$judul=$_SESSION['lang']['budget'].' '.$nmkelbrg[$kelbrg].' '.$_SESSION['lang']['pt'].' '.$pt;

		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td colspan=5 align=center>".$judul."</td>
					</tr>
					<tr>
						<td align=center>No</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>".$_SESSION['lang']['rp']."</td>
					</tr>
				</thead><tbody>";

		
		
		$str="SELECT sum(rupiah) as total,sum(jumlah) as jumlah,kodebarang FROM ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b on
				left(a.kodeorg,4)=b.kodeorganisasi WHERE left(kodebarang,3)='".$kelbrg."' 
				and tahunbudget = '".$thn."' and induk='".$pt."' group by kodebarang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$form.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$bar['kodebarang']."</td>
					<td>".$nmbrg[$bar['kodebarang']]."</td> 
					<td align=right>".number_format($bar['jumlah'],0)."</td>
					<td align=right>".number_format($bar['total'],0)."</td>
				</tr>";
			@$totalDt+=$bar['total'];
			@$totjum+=$bar['jumlah'];
		}
		$form.="<tr class=rowcontent><td colspan=3><b>".$_SESSION['lang']['total']."</td>";
		$form.="<td align=right><b>".number_format($totjum,0)."</td><td align=right><b>".number_format($totalDt,0)."</td></tr>";
		$form.="</tbody></table>";
        echo $form;
		echo"<br><a href=javascript:history.back(-1) style='font-size:16px'>Back</a>";   
	
	break;	
	
	case'kelbrg':
	
		$judul=$nmkelbrg[$kelbrg].' '.$_SESSION['lang']['pt'].' '.$pt;

		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr>
						<td colspan=5 align=center>".$judul."</td>
					</tr>
					<tr>
						<td align=center>No</td>
						<td>".$_SESSION['lang']['kodebarang']."</td>
						<td>".$_SESSION['lang']['namabarang']."</td>
						<td>".$_SESSION['lang']['jumlah']."</td>
						<td>".$_SESSION['lang']['rp']."</td>
					</tr>
				</thead><tbody>";

		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan) as total,kodebarang from 
                     ".$dbname.".log_po_vw where left(tanggal,4) = '".$thn."' and kodeorg='".$pt."'
                     and left(kodebarang,3)='".$kelbrg."' group by kodebarang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$str1="select sum(jumlahpesan) as jumlahpesan from ".$dbname.".log_po_vw where kodebarang='".$bar['kodebarang']."'
				  and left(tanggal,4) = '".$thn."' and kodeorg='".$pt."'";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			$bar1=$res1->fetch();
			$form.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$bar['kodebarang']."</td>
					<td>".$nmbrg[$bar['kodebarang']]."</td> 
					<td align=right>".number_format($bar1['jumlahpesan'],0)."</td>
					<td align=right>".number_format($bar['total'],0)."</td>
				</tr>";
			@$totalDt+=$bar['total'];
			@$totjum+=$bar1['jumlahpesan'];
		}
		$form.="<tr class=rowcontent><td colspan=3><b>".$_SESSION['lang']['total']."</td>";
		$form.="<td align=right><b>".number_format($totjum,0)."</td><td align=right><b>".number_format($totalDt,0)."</td></tr>";
		$form.="</tbody></table>";
        echo $form;
		echo"<br><a href=javascript:history.back(-1) style='font-size:16px'>Back</a>";   
	
	break;
	
	
	
	
	
	
	
	
	
	case'kapital':
		
		$judul=$_SESSION['lang']['nonkapital'].' '.$_SESSION['lang']['pt'].' '.$pt;
	
		$data = array();
		$legend=array();
		$str="select distinct sum((hargasatuan*kurs)*jumlahpesan) as total,kodeorg,left(kodebarang,3) as kelbrg from 
                 ".$dbname.".log_po_vw where left(tanggal,4)='".$thn."' and kodeorg='".$pt."' and left(kodebarang,1) in ('9')
                 group by left(kodebarang,3) order by sum((hargasatuan*kurs)*jumlahpesan) desc limit 0,10";	 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['total'];
			$legend[$no]=$bar['kelbrg'];
			$targ[$no]='?method=kelbrg&pt='.$pt.'&kelbrg='.$bar['kelbrg'].'&thn='.$thn;
			$alts[$no]=$_SESSION['lang']['klikdetail'].' '.$nmkelbrg[$bar['kelbrg']];
		}
		
	
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
		$p1->SetCenter(0.5,0.1);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       
		$p1->SetCSIMTargets($targ,$alts);
		$p1->SetLegends($legend);
	
		$graph->Add($p1);
		$graph->legend->SetPos(0.05,0.95,'left','bottom');
		$graph->legend->SetColumns(1);			
		 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	case'nonkapital':
		
		$judul=$_SESSION['lang']['nonkapital'].' '.$_SESSION['lang']['pt'].' '.$pt;
	
		$data = array();
		$legend=array();
		// $str="select sum((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg from 
			// ".$dbname.".log_po_vw where left(kodebarang,1)!='8' and left(tanggal,4)='".$thn."' ".$sortp." group by kodeorg ";
		$str="select sum((hargasatuan*kurs)*jumlahpesan) as total,kodeorg,left(kodebarang,3) as kelbrg from 
                 ".$dbname.".log_po_vw where left(tanggal,4)='".$thn."' and kodeorg='".$pt."' and left(kodebarang,1) not in ('8','9')
                 group by left(kodebarang,3) order by sum((hargasatuan*kurs)*jumlahpesan) desc limit 0,10";	
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$no+=1;
			$data[$no]=$bar['total'];
			$legend[$no]=$bar['kelbrg'];
			$targ[$no]='?method=kelbrg&pt='.$pt.'&kelbrg='.$bar['kelbrg'].'&thn='.$thn;
			$alts[$no]=$_SESSION['lang']['klikdetail'].' '.$nmkelbrg[$bar['kelbrg']];
		}
		
	
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
		//$p1->SetCenter(0.5,0.9);
		$p1->SetSize(0.2);
		 
		// Enable and set policy for guide-lines. Make labels line up vertically
		$p1->SetGuideLines(true,false);
		$p1->SetGuideLinesAdjust(1.1);
		 
		// Setup the labels
		$p1->SetLabelType(PIE_VALUE_PER);    
		$p1->value->Show();            
		$p1->value->SetFont(FF_FONT1,FS_NORMAL,9);    
		$p1->value->SetFormat('%2.1f%%');       
		$p1->SetCSIMTargets($targ,$alts);
		$p1->SetLegends($legend);
	
		$graph->Add($p1);
		$graph->legend->SetPos(0.05,0.95,'left','bottom');
		$graph->legend->SetColumns(2);			
		 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	case'global':
	
		if($pt==''){
			$sortp="";
			$sortb="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
		else{
			$sortp="and kodeorg='".$pt."' ";
			$sortb=" and induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
		}

		$str="select sum((hargasatuan*kurs)*jumlahpesan/1000000) as total,kodeorg from 
                 ".$dbname.".log_po_vw where left(kodebarang,1)!='8' and left(tanggal,4)='".$thn."' ".$sortp." group by kodeorg ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jumlah[$bar['kodeorg']]=$bar['total'];
			$arrpt[$bar['kodeorg']]=$bar['kodeorg'];
		}
		
		//non kap
		$str="SELECT sum(rupiah/1000000) as total,induk FROM ".$dbname.".bgt_budget_detail a left join ".$dbname.".organisasi b on
				left(a.kodeorg,4)=b.kodeorganisasi WHERE substr(kodebudget,1,1)='M' and tahunbudget = '".$thn."' ".$sortb."  group by induk ";				
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bgtdet[$bar['induk']]=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
		}
     
		//kapi	
		$str="SELECT sum(harga/1000000) as total,induk FROM ".$dbname.".bgt_kapital_vw a left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi
				WHERE tahunbudget = '".$thn."' ".$sortb."  group by induk";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bgtkap[$bar['induk']]=$bar['total'];
			$arrpt[$bar['induk']]=$bar['induk'];
		}
		
		
		
		if(empty($arrpt)){
			echo $_SESSION['lang']['dataempty'];exit();
		}

		
		
		$no=-1;		
		foreach($arrpt as $listpt){
			$no++;
			$arrlistpt[$no]=$listpt;
			$data1y[$no]=@$bgtdet[$listpt]+@$bgtkap[$listpt];
			$data2y[$no]=@$jumlah[$listpt];
			
			$targ1[$no]='?method=bgtperpt&pt='.$listpt.'&thn='.$thn;
			$alts1[$no]='Click to Drill Budget';
			
				$targ2[$no]='?method=beliperpt&pt='.$listpt.'&thn='.$thn;
				$alts2[$no]='Click to Drill Realisasi';
			
		}		

		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");

		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);

		$graph->xaxis->SetTickLabels($arrlistpt);
		$graph->xaxis->SetLabelAngle(20);

		$b1plot = new BarPlot($data1y);
		$b2plot = new BarPlot($data2y);
		
		$b1plot->SetCSIMTargets(@$targ1,@$alts1);    
		$b2plot->SetCSIMTargets(@$targ2,@$alts2);
		
		$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
		 
		$b1plot->SetLegend($_SESSION['lang']['budget']);
		$b2plot->SetLegend($_SESSION['lang']['realisasi']);
		
		    	

		$graph->Add($gbplot);
		
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].' '.$_SESSION['lang']['juta']);
				
			//	echo $judul;exit();
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
		 echo"<br> <a href='".$_SERVER['PHP_SELF']."?method=piegraphbudget&pt=".$pt."&thn=".$thn."' title='Pie Realisasi' class=linkBi>".$_SESSION['lang']['budget']."</a>";       
		 echo"&nbsp &nbsp &nbsp<a href='".$_SERVER['PHP_SELF']."?method=piegraphrealisasi&pt=".$pt."&thn=".$thn."' title='Pie Budget' class=linkBi>".$_SESSION['lang']['realisasi']."</a>";       
		  
	
	break;
	default;
}



?>
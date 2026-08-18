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
		// $stylehidden = "style='display:none'";	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:200% border=0>
				<thead>
					<tr>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['tahun']."</td>
						<td align=center colspan=48>".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					for($i=1;$i<=12;$i++){
						$form.="<td  align=center colspan=4>".numToMonth($i,'I','long')."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=12;$i++){
						$form.="
						<td align=center>".$_SESSION['lang']['material']."</td>
						<td align=center>".$_SESSION['lang']['aset']."</td>
						<td align=center>".$_SESSION['lang']['jasa']."</td>
						<td align=center>".$_SESSION['lang']['karyawan']."</td>
						";
					}
			$form.="</tr>
				</thead>
				";
				

			 
		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumunit[$bar['induk']]+=1;
		}

		
		
		$str=" select (jumlah/1000000) as jumlah,periode,kodeorg,induk from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi where left(periode,4) = '".$thn."' and kodebarang LIKE '3%' and a.noakun like '115%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){		
		$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$matpt[$bar['induk']][$bar['periode']]+=abs($bar['jumlah']);
			@$matunit[$bar['induk']][$bar['kodeorg']][$bar['periode']]+=abs($bar['jumlah']);
			@$mattot[$bar['periode']]+=abs($bar['jumlah']);		
		}
		
		
		
		$str=" select (jumlah/1000000) as jumlah,periode,kodeorg,induk from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi where left(periode,4) = '".$thn."' and 
				(kodebarang LIKE '9%' and a.noakun like '115%') or
				(nojurnal LIKE '%SPK%' and (a.noakun like '127%' or a.noakun like '126%' or a.noakun like '128%')) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){		
		$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$asept[$bar['induk']][$bar['periode']]+=abs($bar['jumlah']);
			@$aseunit[$bar['induk']][$bar['kodeorg']][$bar['periode']]+=abs($bar['jumlah']);
			@$asetot[$bar['periode']]+=abs($bar['jumlah']);		
		}
		
		
		$str=" select (jumlah/1000000) as jumlah,periode,kodeorg,induk from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".organisasi b
				on a.kodeorg=b.kodeorganisasi where left(periode,4) = '".$thn."' and 
				nojurnal like '%SPK%' and (a.noakun like '6%' or a.noakun like '7%') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){		
		$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$jaspt[$bar['induk']][$bar['periode']]+=abs($bar['jumlah']);
			@$jasunit[$bar['induk']][$bar['kodeorg']][$bar['periode']]+=abs($bar['jumlah']);
			@$jastot[$bar['periode']]+=abs($bar['jumlah']);		
		}
		
		
		$str=" select (jumlah/1000000) as jumlah,periodegaji,kodeorg,induk from ".$dbname.".sdm_gajidetail_vw a 
				left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi 
				where left(periodegaji,4) = '".$thn."' and plus=1";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){		
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			@$karpt[$bar['induk']][$bar['periodegaji']]+=abs($bar['jumlah']);
			@$karunit[$bar['induk']][$bar['kodeorg']][$bar['periodegaji']]+=abs($bar['jumlah']);
			@$kartot[$bar['periodegaji']]+=abs($bar['jumlah']);		
		}
		
			
		$arrlistbln=month_inbetween($thn.'-01',$thn.'-12');

		foreach($kodept as $pt){//<td>".$pt." - ".$nmorg[$pt]."</td>
			$no+=1;
			$form.="
				<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".@$jumunit[$pt]."')\">
					<td>".$no."</td>
					<td>".$pt."</td>
					<td>".$thn."</td>";
					foreach($arrlistbln as $bln){
						$form.="		
						<td align=right>".@number_format($matpt[$pt][$bln])."</td>
						<td align=right>".@number_format($asept[$pt][$bln])."</td>
						<td align=right>".@number_format($jaspt[$pt][$bln])."</td>
						<td align=right>".@number_format($karpt[$pt][$bln])."</td>
						";
					}
					$form.="</tr>";
					$urutunit=0;
					foreach($kodeunit as $unit)
					{
						if(@$listkodeunit[$pt][$unit]==$unit)
						{
							$urutunit++;
							$form.="
							<tr class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunit.">
								<td>".$no.".".$urutunit."</td>
								<td>".$unit."</td>
								<td>".$thn."</td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
								foreach($arrlistbln as $bln){
									$form.="		
										<td align=right>".@number_format($matunit[$pt][$unit][$bln])."</td>
										<td align=right>".@number_format($aseunit[$pt][$unit][$bln])."</td>
										<td align=right>".@number_format($jasunit[$pt][$unit][$bln])."</td>
										<td align=right>".@number_format($karunit[$pt][$unit][$bln])."</td>
									";
								}
							$form.="</tr>";	
						}
					}
		}
		$form.="
			<tr class=rowcontent>
				<td colspan=3><b>Total</b></td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
				foreach($arrlistbln as $bln){
					$form.="		
						<td align=right><b>".@number_format($mattot[$bln])."</td>
						<td align=right><b>".@number_format($asetot[$bln])."</td>
						<td align=right><b>".@number_format($jastot[$bln])."</td>
						<td align=right><b>".@number_format($kartot[$bln])."</td>
					";
				}
				$form.="</tr>";

		echo $form;
		
	break;
	
	
	
	
	
	case'global':
		
		if($pt!=''){
			$sort=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$judul=$_SESSION['lang']['pt']." : ".$pt." ";
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
			$sort='';
		}
	
		$no1=$no2=$no3=$no4=-1;
		//material
		$str=" select sum(jumlah/1000000) as jumlah,periode from ".$dbname.".keu_jurnaldt_vw 
				where left(periode,4) = '".$thn."' and kodebarang LIKE '3%' and noakun like '115%'
				".$sort." group by periode";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no1++;
			$ydata[$no1]=abs($bar['jumlah']);
		}
		
		//aset
		$str=" select sum(jumlah/1000000) as jumlah,periode from ".$dbname.".keu_jurnaldt_vw 
				where left(periode,4) = '".$thn."' and 
				(kodebarang LIKE '9%' and noakun like '115%') or
				(nojurnal LIKE '%SPK%' and (noakun like '127%' or noakun like '126%' or noakun like '128%'))
				".$sort." group by periode ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no2++;
			$ydata2[$no2]=abs($bar['jumlah']);
		}
		
		//jasa
		$str=" select sum(jumlah/1000000) as jumlah,periode from ".$dbname.".keu_jurnaldt_vw 
				where left(periode,4) = '".$thn."' and 
				nojurnal like '%SPK%' and (noakun like '6%' or noakun like '7%')
				".$sort." group by periode ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$no3++;
			$ydata3[$no3]=abs($bar['jumlah']);
		}
		
		//karyawan
		$str=" select sum(jumlah/1000000) as jumlah,periodegaji from ".$dbname.".sdm_gajidetail_vw 
				where left(periodegaji,4) = '".$thn."' and plus=1
				".$sort." group by periodegaji ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
			$no4++;
			$ydata4[$no4]=abs($bar['jumlah']);
		}


		// echo"<pre>";
		// print_r($ydata2);
		// echo"</pre>";
		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');textlin
		$graph->SetShadow();
		$graph->img->SetMargin(80,20,20,50);
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        //$graph->xaxis->SetLabelAngle(20);
		
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].' ('.$_SESSION['lang']['juta'].')');
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		//$graph->yaxis->HideZeroLabel(); 
		//$graph->xaxis->HideZeroLabel(); 
		 
		if(empty($ydata) && empty($ydata2) && empty($ydata3) && empty($ydata4)){ 
			echo $_SESSION['lang']['dataempty'];exit();
		}		
		 
	
		 
		if(!empty($ydata)){ 
			$lineplot1=new LinePlot($ydata);
			$lineplot1->mark->SetType(MARK_FILLEDCIRCLE);
			//$lineplot->mark->SetColor('blue');
			$lineplot1->mark->SetFillColor('red');
			$lineplot1->SetLegend('Material');
			$lineplot1->SetCenter();
			$graph->Add($lineplot1);
		}
		
		//$lineplot->SetCSIMTargets($targ,$alts);   

		if(!empty($ydata2)){ 
			$lineplot2=new LinePlot($ydata2);
			$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
			$lineplot2->mark->SetFillColor('green');
			$lineplot2->SetLegend('Aset');
			$lineplot2->SetCenter();
			$graph->Add($lineplot2);
		}
		
		if(!empty($ydata3)){ 
			$lineplot3=new LinePlot($ydata3);
			$lineplot3->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
			$lineplot3->mark->SetFillColor('blue');
			$lineplot3->SetLegend('Jasa');
			$lineplot3->SetCenter();
			$graph->Add($lineplot3);
		}
		
		if(!empty($ydata4)){ 
			$lineplot4=new LinePlot($ydata4);
			$lineplot4->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
			$lineplot4->mark->SetFillColor('black');
			$lineplot4->SetLegend('Karyawan');
			$lineplot4->SetCenter();
			$graph->Add($lineplot4);
		}
		
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(4); 
		
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
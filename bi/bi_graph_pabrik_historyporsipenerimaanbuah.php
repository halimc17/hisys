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
	
		$str="select left(tanggal,4) as tahun,kodecustomer,beratbersih from ".$dbname.".pabrik_timbangan_vw 
				where intex=0 and kodebarang='400000003' and left(tanggal,4)<='".$thn."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthn[$bar['tahun']]=$bar['tahun'];
			$arrsup[$bar['kodecustomer']]=$bar['kodecustomer'];
			@$netto[$bar['kodecustomer']][$bar['tahun']]+=$bar['beratbersih'];
		}
	
		$spanthn=count($arrthn);
		
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:75% border=0>
				<thead>
					<tr>
						<td rowspan=2  align=center>No</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['kodecustomer']."</td>
						<td align=center colspan='".$spanthn."'>".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($arrthn as $thn){
						$form.="<td  align=center>".$thn." (TON)</td>";
					}
			$form.="</tr>
				</thead>
				";
				
		foreach($arrsup as $sup){
			@$no+=1;
			$form.="<tr class=rowcontent>";
			$form.="<td align=center>".$no."</td>";
			$form.="<td>".$nmsup[$sup]."</td>";
			foreach($arrthn as $thn){
				$form.="<td align=right>".@number_format($netto[$sup][$thn]/1000)."</td>";
			}
			$form.="</tr>";
		}
		$form.="</table>";
		
		echo $form;
		echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	

	
	case'detail1internal':
	
	
		
		if($pt!='')
		{
			if($pks!=''){
				$sort=" and kodeorganisasi = '".$pks."'";
				$judul=$_SESSION['lang']['pt']." : ".$pt.", ".$_SESSION['lang']['unit']." : ".$pks." ";
			}
			else{
				$sort=" and induk='".$pt."' ";
				$judul=$_SESSION['lang']['pt']." : ".$pt." ";
			}
		}
		else{
			$judul=$_SESSION['lang']['seluruhpt'];
			$sort=" and tipe = 'PABRIK'";
		}
		
		
		
		
		$nothn=-1;
		$str="select distinct(left(tanggal,4)) as tahun from ".$dbname.".pabrik_timbangan_vw 
				where 1=1 and intex in ('1','2')  and millcode  in (select kodeorganisasi from ".$dbname.".organisasi where 1=1  and left(tanggal,4) <= '".$thn."' ".$sort.") ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nothn++;
			$arrthn[$nothn]=$bar['tahun'];
		}
		
		
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".pabrik_timbangan_vw 
				where  left(tanggal,4) <= '".$thn."' and intex in ('1','2') and millcode in 
				(select kodeorganisasi from ".$dbname.".organisasi where 1=1 and left(tanggal,4) <= '".$thn."' ".$sort.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select distinct(left(tanggal,4)) as tahun,sum(beratbersih/1000) as beratbersih,kodeorg from ".$dbname.".pabrik_timbangan_vw 
				where  left(tanggal,4) <= '".$thn."' and intex in ('1','2') and millcode in (select kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$sort.")
				group by tahun,kodeorg";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
						$kgwb[$row1][$thnlist] = $row2['beratbersih'];
                    }
                }
            }
        }
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		
        $graph->img->SetAntiAliasing();
     
        // $theme_class = new UniversalTheme;
        // $graph->SetTheme($theme_class);
        $graph->yaxis->HideZeroLabel();

		$graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);

		$graph->yaxis->title->SetMargin(17);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6',
								'#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
            return $colorArray[array_rand($colorArray)];
        }
		
		foreach ($resunit as $row) {
            $no ++;
            $resultColor = randomColor();
            if ($row['kodeorg'] == $optunit[$row['kodeorg']]) {
                $list[$no] = new LinePlot($kgwb[$row['kodeorg']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['kodeorg']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
            }
        }
		//$graph->legend->SetFrameWeight(1);
        $graph->Add($list);
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
	
		$noin=$noex=-1;
		
		$str=" select sum(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003' and intex in ('1','2') and left(tanggal,4) <= '".$thn."' ".$sort." group by left(tanggal,4) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noin++;
			$arrthn[$noin]=$bar['tahun'];
			@$ydata[$noin]+=$bar['beratbersih'];
			$targ[$noin]='?method=detail1internal&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts[$noin]='Click to Drill '.$_SESSION['lang']['internal'];
		}
	
		$str=" select sum(beratbersih/1000) as beratbersih,left(tanggal,4) as tahun from ".$dbname.".pabrik_timbangan_vw 
				where kodebarang='400000003' and intex in ('0') and left(tanggal,4) <= '".$thn."' ".$sort." group by left(tanggal,4) ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($res);
		if($row<1){
			$ydata2[0]=0;
			$targ2[$noex]='?method=detail1external&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
			$alts2[$noex]='Click to Drill '.$_SESSION['lang']['tahun'];
		}
		else{
			while($bar=$res->fetch()){
				$noex++;
				$arrthn[$noex]=$bar['tahun'];
				@$ydata2[$noex]+=$bar['beratbersih'];
				$targ2[$noex]='?method=detail1external&pt='.$pt.'&thn='.$thn.'&pks='.$pks;
				$alts2[$noex]='Click to Drill '.$_SESSION['lang']['tahun'];
			}
		}
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
	
		
	//	@$ydata2[$nobgt]=$bar['kgcpo0'.$i]/1000;
		
	
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

		$graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(45);
		
		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(30);
		$graph->xaxis->title->SetMargin(1);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		//$graph->yaxis->HideZeroLabel(); 
		//$graph->xaxis->HideZeroLabel(); 
		 
	
		 
		 
		$lineplot=new LinePlot($ydata);
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		//$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['internal']);
		$lineplot->SetCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->Add($lineplot);
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');		 

		$lineplot2=new LinePlot($ydata2);
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		//$lineplot2->mark->SetColor('green');
		$lineplot2->mark->SetFillColor('green');
		$lineplot2->SetLegend($_SESSION['lang']['external']);
		$lineplot2->SetCenter();
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		$graph->Add($lineplot2);
		
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('purple');
		 
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3);  
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}
?>
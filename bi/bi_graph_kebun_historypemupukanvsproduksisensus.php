<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('lib/zLib.php');
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
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select (a.kwantitas/1000) as kwantitas,left(a.tanggal,4) as tahun,left(a.kodeorg,4) as kodeorg,left(a.kodeorg,6) as divisi,b.induk 
				from ".$dbname.".kebun_pakai_material_vw a left join ".$dbname.".organisasi b on left(a.kodeorg,4)=b.kodeorganisasi
				where a.kodebarang like '311%' and left(a.tanggal,4) <= '".$thn."' order by divisi asc";					
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$matpt[$bar['induk']][$bar['tahun']]+=$bar['kwantitas'];
			@$matunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['kwantitas'];
			@$matdivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahun']]+=$bar['kwantitas'];
			@$mattot[$bar['tahun']]+=$bar['kwantitas'];
		}
		
		
		$str="select (a.kgwb/1000) as kgwb,left(a.tanggal,4) as tahun,b.induk,a.kodeorg,a.divisi 
				from ".$dbname.".kebun_spb_vw a left join ".$dbname.".organisasi b 
				on a.kodeorg=b.kodeorganisasi where a.tanggal like '".$thn."%' order by divisi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$prodpt[$bar['induk']][$bar['tahun']]+=$bar['kgwb'];
			@$produnit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['kgwb'];
			@$proddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahun']]+=$bar['kgwb'];
			@$prodtot[$bar['tahun']]+=$bar['kgwb'];
		}

		
		$str="select (a.kgsensus/1000) as kgsensus,a.tahun,b.induk,a.kodeorg as divisi,left(a.kodeorg,4) as kodeorg 
				from ".$dbname.".kebun_rencanapanen a left join ".$dbname.".organisasi b 
				on left(a.kodeorg,4)=b.kodeorganisasi where tahun = '".$thn."' order by divisi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tahun[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			$kodeunit[$bar['kodeorg']]=$bar['kodeorg'];
			$kddivisi[$bar['divisi']]=$bar['divisi'];
			$listkodeunit[$bar['induk']][$bar['kodeorg']]=$bar['kodeorg'];
			$listkddivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
			@$senpt[$bar['induk']][$bar['tahun']]+=$bar['kgsensus'];
			@$senunit[$bar['induk']][$bar['kodeorg']][$bar['tahun']]+=$bar['kgsensus'];
			@$sendivisi[$bar['induk']][$bar['kodeorg']][$bar['divisi']][$bar['tahun']]+=$bar['kgsensus'];
			@$sentot[$bar['tahun']]+=$bar['kgsensus'];
		}
		
		
		// echo"<pre>";
		// print_r($listkddivisi);
		// echo"</pre>";
		
		@$jthn=count($tahun);
		
		$form="";
		$form.="<table class=sortable cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*3).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($tahun as $thnlist){
						$form.="<td  align=center colspan=3>".$thnlist."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					for($i=1;$i<=$jthn;$i++){
						$form.="
						<td align=center>".$_SESSION['lang']['pupuk']."</td>
						<td align=center>".$_SESSION['lang']['produksi']."</td>
						<td align=center>".$_SESSION['lang']['sensus']."</td>
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

		$str="select kodeorganisasi,induk from ".$dbname.".organisasi where tipe='AFDELING' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jumdivisi[$bar['induk']]+=1;
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
					<td align=right>".@number_format($matpt[$pt][$thnlist])."</td>
					<td align=right>".@number_format($prodpt[$pt][$thnlist])."</td>
					<td align=right>".@number_format($senpt[$pt][$thnlist])."</td>";
			}
			$form.="
				</tr>
			";
			//$urutunit=0;
			$urutunitlist=0;
			foreach($kodeunit as $unit){
				if(@$listkodeunit[$pt][$unit]==$unit){
					@$urutunit+=1;
					$urutunitlist++;
					$form.="
					<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".$jumdivisi[$unit]."')\">
						<td>".$no.".".$urutunitlist."</td>
						<td>".$unit." - ".$nmorg[$unit]."</td>
						";
						foreach($tahun as $thnlist){
							$form.="		
								<td align=right>".@number_format($matunit[$pt][$unit][$thnlist])."</td>
								<td align=right>".@number_format($produnit[$pt][$unit][$thnlist])."</td>
								<td align=right>".@number_format($senunit[$pt][$unit][$thnlist])."</td>";
						}
					$form.="</tr>";	
					$urutdivisilist=0;
					foreach($kddivisi as $divisi){
						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
							$urutdivisilist++;
							$form.="
							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
								<td>".$no.".".$urutunitlist.".".$urutdivisilist."</td>
								<td>".$divisi." - ".$nmorg[$divisi]."</td>
								";
								foreach($tahun as $thnlist){
									$form.="		
										<td align=right>".@number_format($matdivisi[$pt][$unit][$divisi][$thnlist])."</td>
										<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$thnlist])."</td>
										<td align=right>".@number_format($sendivisi[$pt][$unit][$divisi][$thnlist])."</td>";
								}
							$form.="</tr>";	
							
						}
					}
				}
			}
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($tahun as $thnlist){
				$form.="		
					<td align=right><b>".@number_format($mattot[$thnlist])."</td>
					<td align=right><b>".@number_format($prodtot[$thnlist])."</td>
					<td align=right><b>".@number_format($sentot[$thnlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	
	
	
	
	case'detail1sensus':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
			
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$arrthn = array();
		$str="select distinct(left(tanggal,4)) as tahun from ".$dbname.".kebun_rencanapanen 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")";
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahun'];
        }
		   
		$str="select distinct(left(kodeorg,4)) as kodeorg from ".$dbname.".kebun_rencanapanen 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(kgsensus/1000) as kgwb,left(tanggal,4) as tahun,left(kodeorg,4) as kodeorg from ".$dbname.".kebun_rencanapanen 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by  tahun,left(kodeorg,4)  ";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		$graph->yaxis->scale->SetGrace(50);
		
        $graph->img->SetAntiAliasing();
     
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['sensus'].' '.$_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
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
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	case'detail1pupuk':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
			
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$arrthn = array();
		$str="select distinct(left(tanggal,4)) as tahun from ".$dbname.".kebun_pakai_material_vw 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")";
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahun'];
        }
		   
		$str="select distinct(left(kodeorg,4)) as kodeorg from ".$dbname.".kebun_pakai_material_vw 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(kwantitas/1000) as kgwb,left(tanggal,4) as tahun,left(kodeorg,4) as kodeorg from ".$dbname.".kebun_pakai_material_vw 
				where left(tanggal,4) <= '".$thn."' and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by  tahun,left(kodeorg,4)  ";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		$graph->yaxis->scale->SetGrace(50);
		
        $graph->img->SetAntiAliasing();
     
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['pupuk'].' '.$_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
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
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	case'detail1produksi':
	
		if($pt!=''){
			$sortpt=" induk='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
			
		}
		else{
			$sortpt=" tipe='KEBUN' ";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	
		$arrthn = array();
		$str="select distinct(left(tanggal,4)) as tahun from ".$dbname.".kebun_spb_vw 
				where left(tanggal,4) <= '".$thn."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")";
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahun'];
        }
		   
		$str="select distinct(kodeorg) as kodeorg from ".$dbname.".kebun_spb_vw 
				where left(tanggal,4) <= '".$thn."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.") ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorg']] = $row['kodeorg'];
        }
		
		$str="select sum(kgwb/1000) as kgwb,left(tanggal,4) as tahun,kodeorg from ".$dbname.".kebun_spb_vw 
				where left(tanggal,4) <= '".$thn."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where ".$sortpt.")
				group by tahun,kodeorg";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['kodeorg']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		$graph->yaxis->scale->SetGrace(50);
		
        $graph->img->SetAntiAliasing();
     
        $graph->yaxis->HideZeroLabel();

        $graph->xaxis->SetTickLabels($arrthn);
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['produksi'].' '.$_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		 
		
        $list = array();
        $no = -1;

        function randomColor() {
            $colorArray = array('#00FFFF', '#F0FFFF', '#7FFF00', '#FF8C00', '#00FFFF', '#FF00FF', '#98FB98', '#CD5C5C', '#ADD8E6', '#E0FFFF', '#FAFAD2', '#3CB371', '#FFDEAD', '#FF4500', '#B0E0E6', '#D8BFD8');
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
		
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		 echo"<br><a href=javascript:history.back(-1)>Back</a>";   
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case'global':
		$sortprod=$sortppk=$sortsen='';
		if($pt!=''){
			$sortprod=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$sortppk=" and left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
			$sortsen=" and left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		}
		
		
		$noppk=$noprod=$nosen=-1;
		$str=" select sum(kwantitas/1000) as kwantitas,left(tanggal,4) as tahun from ".$dbname.".kebun_pakai_material_vw 
							where kodebarang like '311%' and left(tanggal,4) <= '".$thn."' ".$sortppk."  group by tahun";							
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noppk++;
			$arrthn[$noppk]=$bar['tahun'];
			$ydata[$noppk]=$bar['kwantitas'];
			$targ[$noppk]='?method=detail1pupuk&pt='.$pt.'&thn='.$thn;
			$alts[$noppk]=$_SESSION['lang']['klikdetail'];
			@$noll[$noppk]=0;
		}
	
		
		$str=" select sum(kgwb/1000) as kgwb,left(tanggal,4) as tahun from ".$dbname.".kebun_spb_vw 
							where left(tanggal,4) <= '".$thn."' ".$sortprod."  group by tahun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$noprod++;
			$arrthn[$noprod]=$bar['tahun'];
			$ydata2[$noprod]=$bar['kgwb'];
			$targ2[$noprod]='?method=detail1produksi&pt='.$pt.'&thn='.$thn;
			$alts2[$noprod]=$_SESSION['lang']['klikdetail'];
		}
		
		$str=" select sum(kgsensus/1000) as kgsensus,left(periode,4) as tahun from ".$dbname.".kebun_rencanapanen_bulanan_vw 
							where left(periode,4) <= '".$thn."' ".$sortsen."  group by tahun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nosen++;
			$arrthn[$nosen]=$bar['tahun'];
			$ydata3[$nosen]=$bar['kgsensus'];
			$targ3[$nosen]='?method=detail1sensus&pt='.$pt.'&thn='.$thn;
			$alts3[$nosen]=$_SESSION['lang']['klikdetail'];
		}
	
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}

		
		$graph = new Graph(590,240);   
		$graph->SetScale("textlin"); //$graph->SetScale('intlin');
		$graph->SetY2Scale("lin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,50,20,50);
		$graph->yaxis->scale->SetGrace(10);
		$graph->y2axis->scale->SetGrace(50);  
		
		if($pt==''){
			$graph->title->Set($_SESSION['lang']['seluruhpt']);
		}
		else{
			$graph->title->Set($_SESSION['lang']['pt'].' '.$pt);
		}
		
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);

		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(-10);
		//$graph->title->Set("Example 21");
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
		$graph->yaxis->title->Set($_SESSION['lang']['Ton']);
		 
		$graph->title->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->subtitle->SetFont( FF_FONT1 , FS_BOLD ); 
		$graph->yaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		$graph->xaxis->title->SetFont( FF_FONT1 , FS_BOLD );
		 
		 
		$graph->xaxis->SetTickLabels($arrthn); 
		$graph->xaxis->SetLabelAngle(45);
		 

		$txt = new Text($_SESSION['lang']['tbs'].'('.$_SESSION['lang']['Ton'].')');
		$txt->SetPos(0.02,0.09,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);

		$txt = new Text($_SESSION['lang']['pupuk'].'('.$_SESSION['lang']['Ton'].')');
		$txt->SetPos(0.87,0.1,'left','bottom');
		$txt->SetShadow();
		$graph->AddText($txt);
		 
		 
		$lineplot=new LinePlot($ydata);
		$lineplot->SetWeight( 20 );   // Two pixel wide
		$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
		$lineplot->mark->SetColor('blue');
		$lineplot->mark->SetFillColor('red');
		$lineplot->SetLegend($_SESSION['lang']['pemakaianpupuk']);
		$lineplot->SetCenter();
		$lineplot->SetCSIMTargets($targ,$alts);  
		$graph->AddY2($lineplot); 
		$lineplot->value->SetFormat('%d');
		$lineplot->value->Show();
		$lineplot->value->SetColor('blue');
		

		$lineplot2=new LinePlot($ydata2);
		$lineplot2->SetWeight( 10 );   // Two pixel wide
		$lineplot2->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot2->mark->SetColor('red');
		$lineplot2->mark->SetFillColor('green');
		$lineplot2->SetLegend($_SESSION['lang']['produksi']);
		$lineplot2->SetCenter();
		$lineplot2->SetCSIMTargets($targ2,$alts2);  
		$graph->Add($lineplot2);
		$lineplot2->value->SetFormat('%d');
		$lineplot2->value->Show();
		$lineplot2->value->SetColor('red');
		
		$lineplot3=new LinePlot($ydata3);
		$lineplot3->SetWeight( 10 );   // Two pixel wide
		$lineplot3->mark->SetType(MARK_FILLEDCIRCLE);//MARK_FILLEDCIRCLE //MARK_UTRIANGLE
		$lineplot3->mark->SetColor('black');
		$lineplot3->mark->SetFillColor('purple');
		$lineplot3->SetLegend($_SESSION['lang']['kgsensus']);
		$lineplot3->SetCenter();
		$lineplot3->SetCSIMTargets($targ3,$alts3);  
		$graph->Add($lineplot3);
		$lineplot3->value->SetFormat('%d');
		$lineplot3->value->Show();
		$lineplot3->value->SetColor('purple');
		 
		 
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(3); 
		 
		 
		// Display the graph
		$graph->StrokeCSIM();
		
		
	break;
}

?>
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
$optpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$whbrg="kelompokbarang='351'";
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);

switch($method)
{
	case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$bulan = array();
		$kodept = array();
		
		
		
		$str="select max(tanggal) as tanggal,kodeorg from ".$dbname.".pabrik_masukkeluartangki where kuantitas!=0  group by kodeorg,left(tanggal,7)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$str1="select sum(kuantitas/1000) as jumlah,kodeorg,left(tanggal,7) as bulan from ".$dbname.".pabrik_masukkeluartangki where 
					kodeorg='".$bar['kodeorg']."' and tanggal='".$bar['tanggal']."' ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			while($bar1=$res1->fetch()){
				$jumlah[$optpt[$bar1['kodeorg']]][$bar1['bulan']] = $bar1['jumlah'];
				@$tjumlah[$bar1['bulan']]+=$bar1['jumlah'];
				$kodept[$optpt[$bar1['kodeorg']]]=$optpt[$bar1['kodeorg']];
				$bulan[$bar1['bulan']]=$bar1['bulan'];
			}
		}
	
		@$jbulan=count($bulan);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=2  align=center>No</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".$jbulan.">".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					foreach($bulan as $bulanlist){
						$form.="<td  align=center>".$bulanlist."</td>";
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
					<td>".$pt." - ".@$nmorg[$pt]."</td>
					";
			foreach($bulan as $bulanlist){
					$form.="		
					<td align=right>".@number_format($jumlah[$pt][$bulanlist])."</td>";
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($bulan as $bulanlist){
					$form.="		
					<td align=right>".@number_format($tjumlah[$bulanlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	

	
	
	case'global':
	
		if($pt!=''){
			$sortpt=" and kodept='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
			
		}
		else{
			$sortpt="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	

		$arrthn = array();
		$nobln=-1;
		for($i=1;$i<=12;$i++){
			$nobln++;
			if($i<10){
				$arrthn[$nobln] =$thn.'-0'.$i;
			}
			else{
				$arrthn[$nobln] =$thn.'-'.$i;
			}
		}
		
		   
		// $str="select distinct(kodeorganisasi) as kodeorganisasi from ".$dbname.".organisasi 
				// where tipe='PT' ";	
		$str="select distinct(kodeorg) as kodeorg,induk from ".$dbname.".pabrik_masukkeluartangki a left join  ".$dbname.".organisasi b 
				on a.kodeorg=b.kodeorganisasi";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['induk']] = $row['induk'];
        }
		
	
		$str="select max(tanggal) as tanggal,kodeorg from ".$dbname.".pabrik_masukkeluartangki where kuantitas!=0  group by kodeorg,left(tanggal,7)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$str1="select sum(kuantitas/1000) as jumlah,kodeorg,left(tanggal,7) as bulan from ".$dbname.".pabrik_masukkeluartangki where 
					kodeorg='".$bar['kodeorg']."' and tanggal='".$bar['tanggal']."' ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_ASSOC);
			while($bar1=$res1->fetch()){
				$kgwb[$optpt[$bar1['kodeorg']]][] = $bar1['jumlah'];
			}
		}
		
		
		if(empty($kgwb)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		
		$graph = new Graph(580,220);   
		$graph->SetScale("textlin");
		$graph->SetShadow();
		$graph->img->SetMargin(60,20,10,0);
		$graph->yaxis->scale->SetGrace(50);
		
        $graph->img->SetAntiAliasing();
     
        $graph->yaxis->HideZeroLabel();

       $graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
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
		
		$no=-1;
		foreach ($resunit as $row) {
            $no ++;
            $resultColor = randomColor();
            if ($row['induk'] == $optunit[$row['induk']]) {
                $list[$no] = new LinePlot($kgwb[$row['induk']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['induk']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				// $targ[$no]='?method=detail2produksi&unit='.$row['kodebarang'].'&thn='.$thn.'&pt='.$pt;
				// $alts[$no]='Click to Drill Divisi Produksi '.$row['kodebarang'];
				// $list[$no]->SetCSIMTargets($targ,$alts); 
				$graph->Add($list[$no]);
				$list[$no]->value->SetFormat('%d');
				$list[$no]->value->Show();
				$list[$no]->value->SetColor($resultColor);
            }
        }
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		
	break;
	
	
}

?>
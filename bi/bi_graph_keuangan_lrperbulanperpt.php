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
		
		$str="select (jumlah/1000000) as jumlah,induk,periode from ".$dbname.".keu_jurnaldt_vw a left join 
				 ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where periode like '".$thn."%' and nojurnal like '%CLSM%'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bulan[$bar['periode']]=$bar['periode'];
			$kodept[$bar['induk']]=$bar['induk'];
			@$jumlah[$bar['induk']][$bar['periode']]+=$bar['jumlah'];
			@$tjumlah[$bar['periode']]+=$bar['jumlah'];
		}
		
		if(empty($kodept)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		@$jbulan=count($bulan);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".$jbulan.">".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					foreach($bulan as $bulanlist){
						$form.="<td  align=center>".$bulanlist."</td>";
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
			foreach($bulan as $bulanlist){
				$form.="<td align=right>".@number_format($jumlah[$pt][$bulanlist])."</td>";
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($bulan as $bulanlist){
				$form.="<td align=right>".@number_format($tjumlah[$bulanlist])."</td>";
			}
			$form.="
				</tr></table>
			";		
		echo $form;
		
	break;
	

	case'global':
		$sortptv='';
		if($pt!=''){
			$sortpt=" and induk='".$pt."' ";
			$sortptv=" and kodeorganisasi='".$pt."' ";
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
		
		   
		$str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$sortptv." ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorganisasi']] = $row['kodeorganisasi'];
        }
		
		$str="select sum(jumlah/1000000) as kgwb,induk,periode as bulan from ".$dbname.".keu_jurnaldt_vw a left join 
				 ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where periode like '".$thn."%' and nojurnal like '%CLSM%'
				 ".$sortpt." group by periode,induk";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$baris=owlBaris($res);
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['bulan'] && $row1 == $row2['induk']) {
                        $kgwb[$row1][$thnlist] = abs($row2['kgwb']);
                    }
                }
            }
        }
		
		
		if($baris<1){
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
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].' ('.$_SESSION['lang']['juta'].')');
		
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
            if ($row['kodeorganisasi'] == $optunit[$row['kodeorganisasi']]) {
                $list[$no] = new LinePlot($kgwb[$row['kodeorganisasi']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['kodeorganisasi']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
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
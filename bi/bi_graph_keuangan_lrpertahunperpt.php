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
$whbrg="kelompokbarang='351'";
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);

switch($method)
{
	case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str="select sum(jumlah/1000000) as jumlah,induk,left(periode,4) as tahun from ".$dbname.".keu_jurnaldt_vw a 
				left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where left(periode,4) 
				<= '".$thn."' and nojurnal like '%CLSM%'  group by left(periode,4),induk order by induk asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthn[$bar['tahun']]=$bar['tahun'];
			$kodept[$bar['induk']]=$bar['induk'];
			@$jumlah[$bar['induk']][$bar['tahun']]=$bar['jumlah'];
			@$tjumlah[$bar['periode']]+=$bar['jumlah'];
		}
		
		@$jthn=count($arrthn);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".$jthn.">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($arrthn as $thnlist){
						$form.="<td  align=center>".$thnlist."</td>";
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
					<td align=center>".$no."</td>
					<td>".$pt." - ".@$nmorg[$pt]."</td>
					";
			foreach($arrthn as $thnlist){
				$form.="<td align=right>".@number_format($jumlah[$pt][$thnlist])."</td>";
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($arrthn as $thnlist){
				$form.="<td align=right>".@number_format($tjumlah[$thnlist])."</td>";
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
	
		$not=-1;
		$arrthn = array();
		$str="select distinct(left(periode,4)) as tahun from ".$dbname.".keu_jurnaldt_vw 
				where nojurnal like '%CLSM%' and left(periode,4) <= '".$thn."'  order by tahun asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$not++;
			$arrthn[$not] = $bar['tahun'];
		}

		$str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$sortptv." ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorganisasi']] = $row['kodeorganisasi'];
        }
		
		$str="select sum(jumlah/1000000) as kgwb,induk,left(periode,4) as tahun from ".$dbname.".keu_jurnaldt_vw a left join 
				 ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where left(periode,4) <= '".$thn."' and nojurnal like '%CLSM%'
				 ".$sortpt." group by left(periode,4),induk";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['induk']) {
                        $kgwb[$row1][$thnlist] = abs($row2['kgwb']);
                    }
                }
            }
        }
		// echo"<pre>";
		// print_r($kgwb);
		// echo"</pre>";
		
		
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
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['tahun']);
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
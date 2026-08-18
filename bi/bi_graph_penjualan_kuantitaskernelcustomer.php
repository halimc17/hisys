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
$whbrg="kelompokbarang='400'";
$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whbrg);

switch($method)
{
	case'detailgraph':
		$tahun = array();
		$kodept = array();
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str=" select sum(kuantitaskontrak/1000) as jumlah,left(tanggalkontrak,4) as tahun,kodept,koderekanan
				from ".$dbname.".pmn_kontrakjual  where 1=1 and kodebarang='400000002'
				and left(tanggalkontrak,4) <= '".$thn."'  group by left(tanggalkontrak,4),kodept,koderekanan ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['kodept']]=$bar['kodept'];
			$tahun[$bar['tahun']]=$bar['tahun'];
			$koderekanan[$bar['koderekanan']]=$bar['koderekanan'];
				@$jumlah[$bar['kodept']][$bar['tahun']][$bar['koderekanan']]=$bar['jumlah'];
				@$tjumlah[$bar['tahun']][$bar['koderekanan']]+=$bar['jumlah'];
		}
		
		@$jpt=count($kodept);
		@$jcus=count($koderekanan);
		@$jthn=count($tahun);
		
		
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*$jcus).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($tahun as $thnlist){
						$form.="<td  align=center colspan=".($jcus).">".$thnlist."</td>";
					}
					$form.="	
					</tr>
					<tr>";
						foreach($tahun as $thnlist){
							foreach($koderekanan as $cus){
								$form.="<td align=center>".$cus."</td>";
							}
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
			foreach($tahun as $thnlist){
				foreach($koderekanan as $cus){
					$form.="		
						<td align=right>".@number_format($jumlah[$pt][$thnlist][$cus])."</td>";
				}
				
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($tahun as $thnlist){
				foreach($koderekanan as $cus){
					$form.="		
					<td align=right>".@number_format($tjumlah[$thnlist][$cus])."</td>";
				}
				
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
		$str="select distinct(left(tanggalkontrak,4)) as tahun from ".$dbname.".pmn_kontrakjual 
				where left(tanggalkontrak,4) <= '".$thn."' and kodebarang='400000002' ".$sortpt." order by left(tanggalkontrak,4) asc"; 
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahun'];
        }
		   
		$str="select distinct(koderekanan) as koderekanan from ".$dbname.".pmn_kontrakjual 
			where left(tanggalkontrak,4) <= '".$thn."' and kodebarang='400000002'  ".$sortpt."";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['koderekanan']] = $row['koderekanan'];
        }
		
		$str="select sum(kuantitaskontrak/1000) as kgwb,left(tanggalkontrak,4) as tahun,koderekanan from ".$dbname.".pmn_kontrakjual 
				where left(tanggalkontrak,4) <= '".$thn."' and kodebarang='400000002'  ".$sortpt."  group by left(tanggalkontrak,4),koderekanan";	
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['koderekanan']) {
                        $kgwb[$row1][$thnlist] = $row2['kgwb'];
                    }
                }
            }
        }
		
		
		if(empty($arrthn)){
			echo $_SESSION['lang']['dataempty'];exit();
		}
		
		array_multisort($arrthn,SORT_ASC);
		
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
            if ($row['koderekanan'] == $optunit[$row['koderekanan']]) {
                $list[$no] = new LinePlot($kgwb[$row['koderekanan']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($row['koderekanan']);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				$graph->Add($list[$no]);
				// $list[$no]->value->SetFormat('%d');
				// $list[$no]->value->Show();
				// $list[$no]->value->SetColor($resultColor);
            }
        }

		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(20); 
        $graph->StrokeCSIM();
		
	break;
	
	
}

?>
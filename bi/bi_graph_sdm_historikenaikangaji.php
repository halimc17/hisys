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
$nmtpkar=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

switch($method)
{
	case'detailgraph':
	
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		
		$str="SELECT avg(jumlah) as jumlah,b.tipekaryawan,a.tahun,b.kodeorganisasi as induk from ".$dbname.".sdm_5gajipokok a  
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
				where kodeorganisasi is not null and tahun!=0 group by tipekaryawan,tahun,kodeorganisasi";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kodept[$bar['induk']]=$bar['induk'];
			$arrthn[$bar['tahun']]=$bar['tahun'];
			$arrtpkar[$bar['tipekaryawan']]=$bar['tipekaryawan'];
			@$prodpt[$bar['induk']][$bar['tahun']][$bar['tipekaryawan']]=$bar['jumlah'];	
		}
		
		
		$str="SELECT avg(jumlah) as jumlah,b.tipekaryawan,a.tahun,b.kodeorganisasi as induk from ".$dbname.".sdm_5gajipokok a  
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
				where kodeorganisasi is not null and tahun!=0 group by tipekaryawan,tahun";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$tprodpt[$bar['tahun']][$bar['tipekaryawan']]=$bar['jumlah'];
		}
		
		
		@$jtpkar=count($arrtpkar);
		@$jthn=count($arrthn);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=3  align=center>No</td>
						<td rowspan=3  align=center>".$_SESSION['lang']['unit']."</td>
						<td   align=center colspan=".($jthn*$jtpkar).">".$_SESSION['lang']['tahun']."</td>
					</tr><tr>";
					foreach($arrthn as $thnlist){
						$form.="<td  align=center colspan=".$jtpkar.">".$thnlist."</td>";
					}
					$form.="	
					</tr>
					<tr>";
					foreach($arrthn as $thnlist){
						foreach($arrtpkar as $tpkar){
							$form.="<td align=center>".$nmtpkar[$tpkar]."</td>";
						}
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
			foreach($arrthn as $thnlist){
				foreach($arrtpkar as $tpkar){
					$form.="		
					<td align=right>".@number_format($prodpt[$pt][$thnlist][$tpkar])."</td>";
				}
				
			}
			$form.="
				</tr>
			";
		}		
		$form.="
				<tr class=rowcontent>
						<td colspan=2 align=center><b>Total</td>";
			foreach($arrthn as $thnlist){
				foreach($arrtpkar as $tpkar){
					$form.="		
					<td align=right>".@number_format($tprodpt[$thnlist][$tpkar])."</td>";
				}
				
			}
			$form.="
				</tr></table>
			";		
				
		
		echo $form;
		
	break;
	
	

	case'global':
		$sortptv='';
		if($pt!=''){
			//$sortpt=" and induk='".$pt."' ";
			$sortptv=" and kodeorganisasi='".$pt."' ";
			$judul=$_SESSION['lang']['pt'].' '.$pt;
			
		}
		else{
			$sortpt="";
			$judul=$_SESSION['lang']['seluruhpt'];
		}
	

		$arrthn = array();
		$str="select distinct(tahun) as tahun from ".$dbname.".sdm_5gajipokok where tahun!=0 ";
		$not=-1;		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){	
            $not++;
            $arrthn[$not] = $bar['tahun'];
        }

		/*
		$str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT' ".$sortptv." ";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['kodeorganisasi']] = $row['kodeorganisasi'];
        }
		*/
		
		// $str="select id from ".$dbname.".sdm_5tipekaryawan";	
		$str="SELECT distinct(tipekaryawan) as tipekaryawan from ".$dbname.".sdm_5gajipokok a  
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
				where kodeorganisasi is not null and tahun!=0 ".$sortptv." ";
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['tipekaryawan']] = $row['tipekaryawan'];
        }
		
		$str="SELECT avg(jumlah/1000) as kgwb,b.tipekaryawan,a.tahun from ".$dbname.".sdm_5gajipokok a  
				left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
				where kodeorganisasi is not null and tahun!=0 ".$sortptv." group by tipekaryawan,tahun";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['tahun'] && $row1 == $row2['tipekaryawan']) {
                        $kgwb[$row1][$thnlist] = abs($row2['kgwb']);
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
		$graph->subtitle->Set($_SESSION['lang']['sdthn'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['rupiah'].' (x1000)');
		
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
            if ($row['tipekaryawan'] == $optunit[$row['tipekaryawan']]) {
                $list[$no] = new LinePlot($kgwb[$row['tipekaryawan']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($nmtpkar[$row['tipekaryawan']]);
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
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		
	break;
	
	
}

?>
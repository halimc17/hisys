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

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi','char_length(kodeorganisasi)<6');

switch($method)
{
	case'detailgraph':
		$sortp="and induk in (select kodeorganisasi  from ".$dbname.".organisasi where tipe='PT') ";
		$judul=$_SESSION['lang']['seluruhpt']."";
		if($pt!=''){
			$sortp="and induk='".$pt."'";			
			$judul="PT ". $pt;
		}
		
		$semuakolomdb='awal01+debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';				
		$str="select sum(".$semuakolomdb.") as hutang,left(a.noakun,5) as noakun,periode as bulan,induk 
		      from ".$dbname.".keu_saldobulanan a
			  left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
			  where left(periode,4)= '".$thn."' ".$sortp." and left(a.noakun,5) in ('21111','21112')
			  group by left(a.noakun,5),periode,induk order by periode asc";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($res as $row2) {
            $rpperakun[$row2['noakun']][$row2['bulan']] += ($row2['hutang']*-1)/1000000;
            $kgwb[$row2['noakun'].$row2['induk']][$row2['bulan']] = ($row2['hutang']*-1)/1000000;
            $bulan[$row2['bulan']]=$row2['bulan'];
            $lstInduk[$row2['induk']]=$row2['induk'];
            $lstNoakun[$row2['noakun']]=$row2['noakun'];
        }
         
		echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		$jbulan=count($bulan);
		
		$form="";
		$form.="<table class=sortable cellpadding=1  cellspacing=1 style=width:100% border=0>
				<thead>
					<tr class=rowheader>
						<td rowspan=2  align=center>No</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['noakun']."</td>
						<td rowspan=2  align=center>".$_SESSION['lang']['namaakun']."</td>
						<td   align=center colspan=".$jbulan.">".$_SESSION['lang']['bulan']."</td>
					</tr><tr>";
					foreach($bulan as $bulanlist){
						$bln=substr($bulanlist,-2,2);
						$thn=substr($bulanlist,0,4);
						$prd=$bln."-".$thn;
						$form.="<td  align=center>".$prd."</td>";
					}
					$form.="</tr>";
			$form.="</tr>
				</thead>
				";

		
		####################################################################################
		####################################################################################
		
			foreach($lstNoakun as $akunDt){
				$no+=1;
				$nmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$akunDt."'");
				$form.="<tr class=rowcontent>";
				$form.="<td align=center><b>".$no."</b></td>";
				$form.="<td align=center><b>".$akunDt."</b></td>";
				$form.="<td><b>".$nmAkun[$akunDt]."</b></td>";
				foreach($bulan as $bulanlist){
					$form.="<td align=right><b>".number_format($rpperakun[$akunDt][$bulanlist],0)."</b></td>";
				}
				$form.="</tr>";
				foreach ($lstInduk as $dtPt) {
					$form.="<tr class=rowcontent>";
					$form.="<td></td>";
					$form.="<td colspan=2>".$nmorg[$dtPt]."</td>";
					foreach($bulan as $bulanlist){
						$form.="<td align=right>".number_format($kgwb[$akunDt.$dtPt][$bulanlist],0)."</td>";
					}
				}

			}
		
		$form.="</tbody></table>";
		$form.="*) ".$_SESSION['lang']['nilai']."/".$_SESSION['lang']['juta'];
		// foreach($kodept as $pt){
		// 	@$no+=1;
		// 	$form.="
		// 		<tr class=rowcontent style='cursor:pointer' title='click to show unit' onclick=\"detailpt('".$no."','".@$jumunit[$pt]."')\">
		// 			<td>".$no."</td>
		// 			<td>".$pt." - ".@$nmorg[$pt]."</td>
		// 			<td>".$thn."</td>";
		// 			foreach($bulan as $bln){
		// 				$form.="<td align=right>".@number_format($prodpt[$pt][$bln])."</td>";
		// 			}
		// 			$form.="</tr>";
		// 			$urutunitlist=0;
		// 			foreach($kodeunit as $unit){
		// 				if(@$listkodeunit[$pt][$unit]==$unit){
		// 					@$urutunit+=1;
		// 					$urutunitlist++;
		// 					$form.="
		// 					<tr  class=rowcontentdet   style='cursor:pointer;display:none' id=unitlist".$no."".$urutunitlist." onclick=\"detailunit('".$urutunit."','".@$jumdivisi[$unit]."')\">
		// 						<td>".$no.".".$urutunitlist."</td>
		// 						<td>".$unit." - ".$nmorg[$unit]."</td>
		// 							<td>".$thn."</td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
		// 							foreach($bulan as $bln){
		// 								$form.="<td align=right>".@number_format($produnit[$pt][$unit][$bln])."</td>";
		// 							}
		// 					$form.="</tr>";	
		// 					$urutdivisilist=0;
		// 					foreach($kddivisi as $divisi){
		// 						if(@$listkddivisi[$pt][$unit][$divisi]==$divisi){
								
		// 							$urutdivisilist++;
		// 							$form.="
		// 							<tr class=rowcontentdetail style='display:none'  id=divisilist".$urutunit."".$urutdivisilist.">
		// 								<td>".$no.".".$urutunitlist.".".$urutdivisilist."</td>
		// 								<td>".$divisi." - ".$nmorg[$divisi]."</td>
		// 										<td>".$thn."</td>";//<td>".$divisi." - ".$nmorg[$divisi]."</td>
		// 										foreach($bulan as $bln){
		// 											$form.="<td align=right>".@number_format($proddivisi[$pt][$unit][$divisi][$bln])."</td>";
		// 										}
		// 									$form.="</tr>";	
											
		// 								}
		// 					}
						
		// 				}
		// 			}
		// }
		// $form.="
		// 	<tr class=rowcontent>
		// 		<td colspan=3><b>Total</b></td>";//<td>".$unit." - ".$nmorg[$unit]."</td>
		// 		foreach($bulan as $bln){
		// 			$form.="<td align=right><b>".@number_format($prodtot[$bln])."</td>";
		// 		}
		// 		$form.="</tr>";

		echo $form;
		
	break;
	
	

	case'global':
		$sortp="and induk in (select kodeorganisasi  from ".$dbname.".organisasi where tipe='PT') ";
		$judul=$_SESSION['lang']['seluruhpt']."";
		if($pt!=''){
			$sortp="and induk='".$pt."'";			
			$judul="PT ". $pt;
		}
		
	
		$arrthn = array();
		$nobln=0;
		for($i=1;$i<=12;$i++){
			if($i<10){
				$arrthn[$nobln] =$thn.'0'.$i;
			}
			else{
				$arrthn[$nobln] =$thn.''.$i;
			}
			$nobln++;
		}

		   
		$str="select noakun from ".$dbname.".keu_5akun where noakun in ('21111','21112')";	
		$resunit = fetchData($str);
        $optunit = array();
        foreach ($resunit as $row) {
            $optunit[$row['noakun']] = $row['noakun'];
            $lstBhs[$row['noakun']]=$_SESSION['lang']['kontraktor'];
            if($row['noakun']=='21111'){
            	$lstBhs[$row['noakun']]=$_SESSION['lang']['supplier'];	
            }
            
        }
		 
		
		
		$semuakolomdb='awal01+debet01-kredit01+debet02-kredit02+debet03-kredit03+debet04-kredit04+debet05-kredit05+debet06-kredit06+debet07-kredit07+debet08-kredit08+debet09-kredit09+debet10-kredit10+debet11-kredit11+debet12-kredit12';				
		$str="select sum(".$semuakolomdb.") as hutang,left(a.noakun,5) as induk,periode as bulan from ".$dbname.".keu_saldobulanan a
			  left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
			  where left(periode,4)= '".$thn."' ".$sortp." and left(a.noakun,5) in ('21111','21112')
			  group by left(a.noakun,5),periode order by periode";
		$res = fetchData($str);
        $kgwb = array();
        foreach ($arrthn as $thnlist => $row) {
            foreach ($optunit as $row1) {
                $kgwb[$row1][$thnlist] = 0;
                foreach ($res as $row2) {
                    if ($row == $row2['bulan'] && $row1 == $row2['induk']) {
                        $kgwb[$row1][$thnlist] = ($row2['hutang']*-1)/1000000;
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

       $graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());
        $graph->xaxis->SetLabelAngle(20);

		
		$graph->title->Set($judul);
		$graph->subtitle->Set($_SESSION['lang']['tahun'].' '.$thn);
		$graph->yaxis->title->SetMargin(18);
		$graph->xaxis->title->SetMargin(1);
		$graph->xaxis->title->Set($_SESSION['lang']['bulan']);
		$graph->yaxis->title->Set($_SESSION['lang']['nilai'].'/'.$_SESSION['lang']['juta']);
		
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
		
		$no=0;
		foreach ($resunit as $row) {
            $resultColor = randomColor();
            if ($row['noakun'] == $optunit[$row['noakun']]) {
                $list[$no] = new LinePlot($kgwb[$row['noakun']]);
                $list[$no]->SetColor($resultColor);
                $list[$no]->SetLegend($lstBhs[$row['noakun']]);
                $list[$no]->mark->SetType(MARK_FILLEDCIRCLE);
                $list[$no]->mark->SetFillColor($resultColor);
                $list[$no]->SetCenter();
				$graph->Add($list[$no]);
				$list[$no]->value->SetFormat('%d');
				$list[$no]->value->Show();
				$list[$no]->value->SetColor($resultColor);
				$no ++;
            }
        }
		$graph->legend->SetPos(0.5,0.99,'center','bottom');
		$graph->legend->SetColumns(8); 
        $graph->StrokeCSIM();
		
	break;
	
	
}

?>
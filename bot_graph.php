<?php
ini_set('display_errors',0);
error_reporting(0);

if(count($_POST)==0){include('lib/nangkoelib.php');}
include('lib/zLib.php');
require_once('config/connection.php');
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_bar.php');
require_once('jpgraph/jpgraph_line.php');
require_once('jpgraph/jpgraph_table.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$folder = "imgbot/";
if (!file_exists($folder)){
	mkdir($folder, 0777, true);
}

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

	#param :
	#unit = REGIONAL / KODEORG
	#jenis = PDF / BLANK
	#tanggal = tanggal

	
	$param['tampil']="hide";

	$arrunit=array(
		'1' =>'KAPUAS',
		'2' =>'BONTI',
		'3' =>'SEKADAU',
		'4' =>'KALTENG',
		'5' =>'KSP-GROUP',
		'6' =>'BPJE',
		'7' =>'KBPE',
		'8' =>'KSPE',
		'9' =>'SD1E',
		'10' =>'SD2E',
		'11' =>'SD3E',
		'12' =>'SD4E',
		'13' =>'SNPE',
		'14' =>'AA1E',
		'15' =>'AA2E',
		'16' =>'GA1E'
	);
	
	if($param['unit']!=''){
		$param['unit']=$param['unit'];
	}else{		
		$rand = rand(1,16);
		$param['unit'] = $arrunit[$rand];
	}
	
	$allunit['BPJE']=array('BPJE','BPPE');
	$allunit['KBPE']=array('KBPE','KPPE');
	$allunit['KSPE']=array('KSPE','KPPE');
	$allunit['SD1E']=array('SD1E','S1PE');
	$allunit['SD2E']=array('SD2E','S2PE');
	$allunit['SD3E']=array('SD3E','S3PE');
	$allunit['SD4E']=array('SD4E','S4PE');
	$allunit['SNPE']=array('SNPE','SPPE');
	$allunit['AA1E']=array('AA1E');
	$allunit['AA2E']=array('AA2E');
	$allunit['GA1E']=array('GA1E');
	$unit_ip=array();
	foreach($allunit as $unit => $val){
		foreach($val as $key){
			if($unit==$param['unit']){				
				$unit_ip[$key]=$key;
			}
		}
	}

// $param['unit']='KSP-GROUP';
// echo $param['unit'];
// exit();


	if($param['tanggal']!=''){
		$param['tanggal']=tanggalsystemn($param['tanggal']);
		$tgldr = substr($param['tanggal'],0,7)."-01";
		$tglhi = tglakhir($tgldr);
	}else{		
		$tgldr = date("Y-m")."-01";
		$tglhi = tglakhir($tgldr);
	}
	// $tgldr = '2021-06-01';
	// $tglhi = '2021-06-30';
	
	
	$hari     = substr($tglhi,8,2);
	$bulan    = substr($tglhi,5,2);
	$tahun    = substr($tglhi,0,4);
	$periode  = substr($tglhi,0,7);
	
	$tahunini = substr($tglhi,0,4);
	$tahunlalu= substr($tglhi,0,4)-1;
	
	
	$rangetgl= rangeTanggal($tgldr,$tglhi);
	$optreg  = makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');
	$cekreg  = makeOption($dbname,'bgt_regional_assignment','subregional,subregional');
	$optorg  = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)<=4");
	$tipeorg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"length(kodeorganisasi)<=4");
	
	
	$style=$styletable="";
	if($param['jenis']=='PDF' or $param['tampil']=='hide'){
		$style="style=width:715px;height:200px";
		$styletable="style=width:700px;";
	}
	$stfieldset="style=\"border:#0094de solid 1px;-moz-border-radius: 5px;-webkit-border-radius: 5px;border-radius: 5px;padding: 5px 5px;background-color:#d4ecff;font-size:12px;font-weight:lighter;cursor:auto;text-decoration:none;text-shadow:none;\"";
	
	$tab="";
	if($param['unit']=='KSP-GROUP'){		
		$urut=0;
		$arrregion=array('KAPUAS','BONTI','SEKADAU','KALTENG','KSP-GROUP');
		foreach($arrregion as $param['unit']){
			$urut++;
			$fileName = $folder.$param['unit']."graph1.jpg";
			getGraph1();

			$param['fileName2bar'] = $folder.$param['unit']."graph2bar.jpg";
			getGraph2bar();

			$param['getGraphCPOdanPK'] = $folder.$param['unit']."graphcpopk.jpg";
			getGraphCPOdanPK();

			$param['getGraph2barcpodanpk'] = $folder.$param['unit']."graph2barcpopk.jpg";
			getGraph2barcpodanpk();

			$param['getGraphCpoPkThn'] = $folder.$param['unit']."graphcpopkthn.jpg";
			getGraphCpoPkThn();

			$param['getGraphOerKerFfa'] = $folder.$param['unit']."graphoerkerffa.jpg";
			getGraphOerKerFfa();

			$param['getGraphOerKerFfaTodate'] = $folder.$param['unit']."graphoerkerffatodate.jpg";
			getGraphOerKerFfaTodate();

			$fileName2 = $folder.$param['unit']."graph2.jpg";
			getGraph2();
			
			$tab.="<table cellspacing=0 border=0 ".$styletable.">";
			$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:12;text-align:center;font-family:sans-serif;>Daily Production Report ".ucwords(strtolower($param['unit']))."</td></tr>";
			$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:12;text-align:center;font-family:sans-serif;>".date("d")." ".numToMonth($bulan)." ".$tahun."</td></tr>";
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:9;font-family:sans-serif;>a. FFB Production</td></tr>";
			
			$tab.="<tr>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$fileName." style=width:600px;height:200px></fieldset></td>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$param['fileName2bar']." style=width:100px;height:200px></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$fileName2." ".$style."></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:9;font-family:sans-serif;>b. Mill Production (CPO & PK)</td></tr>";
			$tab.="<tr>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$param['getGraphCPOdanPK']." style=width:600px;height:200px></fieldset></td>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$param['getGraph2barcpodanpk']." style=width:100px;height:200px></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphCpoPkThn']." ".$style."></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphOerKerFfa']." ".$style."></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphOerKerFfaTodate']." ".$style."></fieldset></td>";
			$tab.="</tr>";
			$tab.="</table>";
			
			$tab.="<footer  style=\"position: fixed; bottom: 0cm; left: 0cm; right: 0cm;height:0cm;text-align:left;font-style:italic;font-size:8px\">auto generated by owl.ksp-agro.com on ".date("d-m-Y H:i:s")."</footer>";
			
			
			if(count($arrregion)==$urut){
			}else{				
				$tab.="<div style='page-break-before:always;'></div>";
			}
			
			if($param['tampil']!='hide'){		
				echo $tab;
			}else{
				$dompdf = new Dompdf();
				$dompdf->load_html($tab);
				$dompdf->setPaper('A4', 'potrait');
				$dompdf->render();
				$canvas = $dompdf->get_canvas();
				
				$filepdf=$folder."Daily_Prod_Report_".$param['unit'].".pdf";
				if (file_exists($filepdf)){
					unlink($filepdf);
				}
				file_put_contents($filepdf, $dompdf->output());
				
				
			}
			#file pdf harus di hapus karena jika tidak isinya tidak sesuai jika diambil 1 per 1
			if($param['unit']!='KSP-GROUP'){				
				unlink($filepdf);
			}
		}
		$files = glob('imgbot/*.jpg'); 
		foreach($files as $file) {
			if(is_file($file)){
				unlink($file);
			} 
		}
	}else{
		$fileName = $folder.$param['unit']."graph1.jpg";
		getGraph1();

		$param['fileName2bar'] = $folder.$param['unit']."graph2bar.jpg";
		getGraph2bar();

		$param['getGraphCPOdanPK'] = $folder.$param['unit']."graphcpopk.jpg";
		getGraphCPOdanPK();

		$param['getGraph2barcpodanpk'] = $folder.$param['unit']."graph2barcpopk.jpg";
		getGraph2barcpodanpk();

		$param['getGraphCpoPkThn'] = $folder.$param['unit']."graphcpopkthn.jpg";
		getGraphCpoPkThn();

		$param['getGraphOerKerFfa'] = $folder.$param['unit']."graphoerkerffa.jpg";
		getGraphOerKerFfa();

		$param['getGraphOerKerFfaTodate'] = $folder.$param['unit']."graphoerkerffatodate.jpg";
		getGraphOerKerFfaTodate();

		$fileName2 = $folder.$param['unit']."graph2.jpg";
		getGraph2();
		
		
		$tab.="<table cellspacing=0 border=0 ".$styletable.">";
		$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:12;text-align:center;font-family:sans-serif;>Daily Production Report ".ucwords(strtolower($param['unit']))."</td></tr>";
		$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:12;text-align:center;font-family:sans-serif;>".date("d")." ".numToMonth($bulan)." ".$tahun."</td></tr>";
		$tab.="<tr><td colspan=2></td></tr>";
		$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:9;font-family:sans-serif;>a. FFB Production</td></tr>";
		
		$tab.="<tr>";
		$tab.="<td><fieldset ".$stfieldset."><img src=".$fileName." style=width:600px;height:200px></fieldset></td>";
		$tab.="<td><fieldset ".$stfieldset."><img src=".$param['fileName2bar']." style=width:100px;height:200px></fieldset></td>";
		$tab.="</tr>";
		
		$tab.="<tr><td colspan=2></td></tr>";
		$tab.="<tr>";
		$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$fileName2." ".$style."></fieldset></td>";
		$tab.="</tr>";
		
		if($tipeorg[$param['unit']]!='KEBUN'){
			#khusus mill
			$tab.="<tr><td colspan=2 style=font-weight:bold;font-size:9;font-family:sans-serif;>b. Mill Production (CPO & PK)</td></tr>";
			$tab.="<tr>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$param['getGraphCPOdanPK']." style=width:600px;height:200px></fieldset></td>";
			$tab.="<td><fieldset ".$stfieldset."><img src=".$param['getGraph2barcpodanpk']." style=width:100px;height:200px></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphCpoPkThn']." ".$style."></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphOerKerFfa']." ".$style."></fieldset></td>";
			$tab.="</tr>";
			
			$tab.="<tr><td colspan=2></td></tr>";
			$tab.="<tr>";
			$tab.="<td colspan=2><fieldset ".$stfieldset."><img src=".$param['getGraphOerKerFfaTodate']." ".$style."></fieldset></td>";
			$tab.="</tr>";
		}
		
		$tab.="</table>";
		
		$tab.="<footer  style=\"position: fixed; bottom: 0cm; left: 0cm; right: 0cm;height:0cm;text-align:left;font-style:italic;font-size:8px\">auto generated by owl.ksp-agro.com on ".date("d-m-Y H:i:s")."</footer>";
		
		if($param['tampil']!='hide'){		
			echo $tab;
		}else{
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			
			$filepdf=$folder."Daily_Prod_Report_".$param['unit'].".pdf";
			if (file_exists($filepdf)){
				unlink($filepdf);
			}
			file_put_contents($filepdf, $dompdf->output());
			
			
			$files = glob('imgbot/*.jpg'); 
			foreach($files as $file) {
				if(is_file($file)){
					unlink($file);
				} 
			}
		}
	}

	
	
	
	
	function getGraph1(){
		global $folder;global $fileName;global $nmreg;global $bulan;global $tahun;global $rangetgl;global $tanggal;global $pot;
		global $potkud;global $kg;global $kgkud;global $tgldr;global $tglhi;global $cekreg;global $param;global $optorg;
		global $unit_ip; global $optreg;
		
		$str = "select intiplasma,subregional,substr(tanggal,1,10) as tanggal, sum(beratbersih)/1000 as kg, kodeorg, millcode, divcode, sum(kgpotsortasi)/1000 as pot from ".$dbname.".pabrik_timbangan a left join ".$dbname.".bgt_regional_assignment b on a.kodeorg=b.kodeunit where substr(tanggal,1,10) between '".$tgldr."' and '".$tglhi."' and kodebarang='40000003' group by kodeorg, substr(tanggal,1,10),millcode order by substr(tanggal,1,10) asc";
		$res=fetchdata($str);
		$kg=$pot=$kgkud=$potkud=array();
		$param['kgibi']=$param['kgpbi']=0;
		foreach($res as $bar){
			if($bar['subregional']==''){
				$bar['subregional']=$optreg[$bar['millcode']];
			}
			
			
			if($bar['kodeorg']==""){
				$bar['kodeorg']="EXTN";	
			}
			if($param['unit']=='KSP-GROUP'){
				$cekisi++;
				if($bar['intiplasma']=='INTI'){
					$kg[$bar['tanggal']]+=$bar['kg'];
					$pot[$bar['tanggal']]+=$bar['pot'];
					
					$param['kgibi']+=$bar['kg'];
				}else{
					$param['kgpbi']+=$bar['kg'];
					
					$kgkud[$bar['tanggal']]+=$bar['kg'];
					$potkud[$bar['tanggal']]+=$bar['pot'];
				}
				$nmreg="KSP Group";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$cekisi++;
					if($bar['intiplasma']=='INTI'){
						$kg[$bar['tanggal']]+=$bar['kg'];
						$pot[$bar['tanggal']]+=$bar['pot'];
						
						$param['kgibi']+=$bar['kg'];
					}else{
						$param['kgpbi']+=$bar['kg'];
						
						$kgkud[$bar['tanggal']]+=$bar['kg'];
						$potkud[$bar['tanggal']]+=$bar['pot'];
					}
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeorg'],$unit_ip)){
				#if($bar['kodeorg']==$param['unit']){
					$cekisi++;
					if($bar['intiplasma']=='INTI'){
						$kg[$bar['tanggal']]+=$bar['kg'];
						$pot[$bar['tanggal']]+=$bar['pot'];
						
						$param['kgibi']+=$bar['kg'];
					}else{
						$param['kgpbi']+=$bar['kg'];
						
						$kgkud[$bar['tanggal']]+=$bar['kg'];
						$potkud[$bar['tanggal']]+=$bar['pot'];
					}
				}
				$nmreg="Unit ".strtoupper(strtolower($param['unit']));
			}
			
			$reg[$bar['subregional']]=$bar['subregional'];
			$tanggal[$bar['tanggal']]=$bar['tanggal'];
		}
		
		// if($param['unit']=='SEKADAU'){
			// echo"<pre>";
			// print_r($kg);
			// print_r($kgkud);
			// print_r($reg);
			// print_r($mill);
		// }
		
		foreach($rangetgl as $tgl){					
			$datay[0][]=substr($tgl,8,2);
			$datat[0][0]="";
			$datat[0][]=substr($tgl,8,2);
			
			if($tanggal[$tgl]==''){
				$pot[$tgl]=0;
				$potkud[$tgl]=0;
			}
			if($pot[$tgl]==''){$pot[$tgl]=0;}
			if($potkud[$tgl]==''){$potkud[$tgl]=0;}
			
			$datay[1][]=$kg[$tgl];
			$datay[2][]=$kgkud[$tgl];
			
			
			#untuk table
			$datat[1][0]="Inti";
			$datat[1][]=round($kg[$tgl],0);
			$datat[2][0]="Plasma";
			$datat[2][]=round($kgkud[$tgl],0);
			
			$datat[3][0]="Total";
			$datat[3][]=round($kgkud[$tgl]+$kg[$tgl],0);
			
			$datat[4][0]="- Grdg Inti";
			$datat[4][]=@round(fixnan(($pot[$tgl]/$kg[$tgl])*100),2);
			
			$datat[5][0]="- Grdg Plasma";
			$datat[5][]=@round(fixnan(($potkud[$tgl]/$kgkud[$tgl])*100),2);
			
			$datay2[0][]=$pot[$tgl];
			$datay2[1][]=$potkud[$tgl];
		}
		
		// echo"<pre>";
		// print_r($datat);
		// echo"</pre>";
		// exit("error");
		
		
		
		
		$graph = new Graph(1275,600); 
		$graph->img->SetMargin(100,75,60,150);
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		
		$graph->title->Set("Daily Prod ".$nmreg." - ".numToMonth($bulan)." ".$tahun);
		$graph->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph->xaxis->title->Set('KEBUN');
		$graph->xaxis->HideLabels(true);
		
		$graph->yaxis->title->Set('In Ton');
		$graph->yaxis->title->SetMargin(10);
		$graph->yaxis->SetLabelMargin(10);
		$graph->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		#$bplot1 = new BarPlot($datay[3]);
		#$bplot1->SetFillColor('orange');
		$bplot2 = new BarPlot($datay[1]);
		$bplot3 = new BarPlot($datay[2]);
		$accbplot = new GroupBarPlot(array($bplot2,$bplot3));
		$graph->Add($accbplot);
		
		$bplot2->SetColor('#41612d');
		$bplot2->SetFillColor('#b2f288');
		$bplot3->SetColor('#858383');
		$bplot3->SetFillColor('#dedede');
		
		
		// Create Y2 scale data set 
		$graph->SetY2Scale("lin"); // Y2 axis
		$graph->SetY2OrderBack(false);
		$graph->y2axis->title->Set('In %');
		$graph->y2axis->title->SetMargin(15); // Some extra margin to clear labels
		$graph->y2axis->title->SetFont(FF_FONT1,FS_NORMAL,11);
		$graph->y2axis->SetLabelFormatCallback('toFahrenheit');
		$graph->y2axis->SetColor('navy');

		$l2plot = new LinePlot($datay2[0]);
		$graph->AddY2($l2plot);
		$l2plot->SetWeight(1);
		$l2plot->SetColor('darkgreen');
		$l2plot->SetBarCenter();
		$l2plot->mark->SetType(MARK_FILLEDCIRCLE,'',2.0);
		$l2plot->mark->SetWeight(2);
		$l2plot->mark->SetWidth(5);
		$l2plot->mark->setColor("darkgreen");
		$l2plot->mark->setFillColor("darkgreen");
		
		
		$l2plot = new LinePlot($datay2[1]);
		$graph->AddY2($l2plot);
		$l2plot->SetWeight(1);
		$l2plot->SetColor('black');
		$l2plot->SetBarCenter();
		$l2plot->mark->SetType(MARK_FILLEDCIRCLE,'',2.0);
		$l2plot->mark->SetWeight(2);
		$l2plot->mark->SetWidth(5);
		$l2plot->mark->setColor("red");
		$l2plot->mark->setFillColor("red");
		
		
		$table = new GTextTable();
		$table->Set($datat);
		$table->SetPos(10,455);
		$table->SetFont(FF_FONT1,FS_NORMAL,6);
		$table->SetAlign('center');
		$table->SetColAlign(0,'left');
		if(count($rangetgl)=='31'){$table->SetMinColWidth(35.5);}
		if(count($rangetgl)=='30'){$table->SetMinColWidth(36.7);}
		if(count($rangetgl)=='29'){$table->SetMinColWidth(38);}
		if(count($rangetgl)=='28'){$table->SetMinColWidth(39.4);}
		$table->SetNumberFormat('%0.0f');
		$table->SetCellFillColor(1,0,'#b2f288');
		$table->SetCellFillColor(2,0,'#dedede');
		//$table->SetCellFillColor(3,0,'#73c0ff');
		$table->SetRowNumberFormat(4,'%0.1f');
		$table->SetRowNumberFormat(5,'%0.1f');
		$table->SetCellColor(4,0,'darkgreen');
		$table->SetCellColor(5,0,'red');

		// Format table header row
		$table->SetRowFillColor(0,'#ded9d9');
		$table->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table->SetRowAlign(0,'center');

		// and add it to the graph
		$graph->Add($table);
		
		
		if (file_exists($fileName)){
			unlink($fileName);
		}
		$graph->Stroke($fileName);
		
	}
	
	function toFahrenheit($aVal) {
		return round($aVal);
	}
	
	
	
	function getGraph2(){
		global $folder;global $fileName2;global $nmreg;global $bulan;global $tahun;global $rangetgl;global $tanggal;
		global $tahunini;global $tahunlalu;global $owlPDO;global $cekbln;global $potiti;global $potpti;global $potitl;
		global $potptl;global $kgiti;global $kgitl;global $kgpti;global $kgptl;
		global $cekreg;global $param;global $optorg; global $unit_ip;global $optreg;
		
		
		$rangebln = range(1,12);
		$cekisi=0;
		$str = "select intiplasma,subregional,substr(tanggal,1,7) as periode, substr(tanggal,1,4) as tahun, sum(beratbersih)/1000 as kg, kodeorg, millcode, divcode, sum(kgpotsortasi)/1000 as pot from ".$dbname.".pabrik_timbangan a left join ".$dbname.".bgt_regional_assignment b on a.kodeorg=b.kodeunit where substr(tanggal,1,4) between '".$tahunlalu."' and '".$tahunini."' and kodebarang='40000003' group by kodeorg, substr(tanggal,1,7),millcode order by substr(tanggal,1,7) asc";
		$res=fetchdata($str);
		$kgiti=$potiti=$kgitl=$potitl=$kgpti=$potpti=$kgptl=$potptl=array();
		foreach($res as $bar){
			$month = substr($bar['periode'],5,2);
			
			if($bar['intiplasma']=='INTI'){$ip[$bar['intiplasma']]='INTI';}else{$ip[$bar['intiplasma']]='PLASMA';}
			if($bar['subregional']==''){
				$bar['subregional']=$optreg[$bar['millcode']];
			}
			if($bar['kodeorg']==""){
				$bar['kodeorg']="EXTN";	
			}
				
			if($param['unit']=='KSP-GROUP'){
				$cekisi++;
				if($bar['intiplasma']=='INTI'){
					if($bar['tahun']==$tahunini){
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$month){
								$kgiti[$bln]+=$bar['kg'];
								$potiti[$bln]+=$bar['pot'];
							}
						}
					}else{
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$month){
								$kgitl[$bln]+=$bar['kg'];
								$potitl[$bln]+=$bar['pot'];
							}
						}
					}
				}else{
					if($bar['tahun']==$tahunini){
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$month){
								$kgpti[$bln]+=$bar['kg'];
								$potpti[$bln]+=$bar['pot'];
							}
						}						
					}else{
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$month){
								$kgptl[$bln]+=$bar['kg'];
								$potptl[$bln]+=$bar['pot'];
							}
						}	
					}
					$nmreg="KSP Group - FFB";
					$judulpdf="KSP - Group";
				}
				$total[$bar['periode']][$ip[$bar['intiplasma']]]+=$bar['kg'];
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$cekisi++;
					if($bar['intiplasma']=='INTI'){
						if($bar['tahun']==$tahunini){
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgiti[$bln]+=$bar['kg'];
									$potiti[$bln]+=$bar['pot'];
								}
							}
						}else{
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgitl[$bln]+=$bar['kg'];
									$potitl[$bln]+=$bar['pot'];
								}
							}
						}
					}else{
						if($bar['tahun']==$tahunini){
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgpti[$bln]+=$bar['kg'];
									$potpti[$bln]+=$bar['pot'];
								}
							}						
						}else{
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgptl[$bln]+=$bar['kg'];
									$potptl[$bln]+=$bar['pot'];
								}
							}	
						}
					}
					$total[$bar['periode']][$ip[$bar['intiplasma']]]+=$bar['kg'];
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region - FFB";
					$judulpdf=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeorg'],$unit_ip)){
				#if($bar['kodeorg']==$param['unit']){
					$cekisi++;
					if($bar['intiplasma']=='INTI'){
						if($bar['tahun']==$tahunini){
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgiti[$bln]+=$bar['kg'];
									$potiti[$bln]+=$bar['pot'];
								}
							}
						}else{
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgitl[$bln]+=$bar['kg'];
									$potitl[$bln]+=$bar['pot'];
								}
							}
						}
					}else{
						if($bar['tahun']==$tahunini){
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgpti[$bln]+=$bar['kg'];
									$potpti[$bln]+=$bar['pot'];
								}
							}						
						}else{
							foreach($rangebln as $bln){
								$bln=addZero($bln,2);
								if($bln>=$month){
									$kgptl[$bln]+=$bar['kg'];
									$potptl[$bln]+=$bar['pot'];
								}
							}	
						}
					}
				}
				$nmreg="Unit ".strtoupper(strtolower($param['unit']))." - FFB";
				$judulpdf="Unit ".$optorg[$param['unit']];
			}
		
			$cekbln[$bar['tahun']][$month]=$month;
		}
		
		$e="(";
		for($i=1;$i<=intval($bulan);$i++){
			$r="kg".addZero($i,2);
			if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
		}
		$e.=")";
		
		#budget inti
		$str=" select tahunbudget, a.kodeunit, subregional,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a left join ".$dbname.".bgt_regional_assignment b on a.kodeunit=b.kodeunit where 1=1 and tahunbudget between '".$tahunlalu."' and '".$tahunini."' and a.kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and inti='1')";
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeunit']];
			if($param['unit']=='KSP-GROUP'){
				if($bar['tahunbudget']==$tahunini){
					$kgiti[13]+=$bar['kgsetahun']/1000;
				}else{
					$kgitl[13]+=$bar['kgsetahun']/1000;
				}
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){
						$kgiti[13]+=$bar['kgsetahun']/1000;
					}else{
						$kgitl[13]+=$bar['kgsetahun']/1000;
					}
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeunit'],$unit_ip)){
				#if($bar['kodeunit']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){
						$kgiti[13]+=$bar['kgsetahun']/1000;
					}else{
						$kgitl[13]+=$bar['kgsetahun']/1000;
					}
				}
			}
		}
		
		#budget plasma
		$str=" select tahunbudget, millcode , kodeunit, kgolah from ".$dbname.".bgt_produksi_pks where 1=1 and tahunbudget between '".$tahunlalu."' and '".$tahunini."'";
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeunit']];
			if($bar['subregional']==''){
				$bar['subregional']=$optreg[$bar['millcode']];
			}
			
			if($param['unit']=='KSP-GROUP'){
				if($bar['tahunbudget']==$tahunini){
					$kgpti[13]+=$bar['kgolah']/1000;
				}else{
					$kgptl[13]+=$bar['kgolah']/1000;
				}
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){
						$kgpti[13]+=$bar['kgolah']/1000;
					}else{
						$kgptl[13]+=$bar['kgolah']/1000;
					}
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeunit'],$unit_ip)){
				#if($bar['kodeunit']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){
						$kgpti[13]+=$bar['kgolah']/1000;
					}else{
						$kgptl[13]+=$bar['kgolah']/1000;
					}
				}
			}
		}
		
		
		$rangebln = range(1,13);
		foreach($rangebln as $bln){
			$bln=addZero($bln,2);
			if($cekbln[$bln]==''){$potiti[$bln]=0;$potpti[$bln]=0;$potitl[$bln]=0;$potptl[$bln]=0;}
			if($kgiti[$bln]==''){$kgiti[$bln]=0;}
			if($kgitl[$bln]==''){$kgitl[$bln]=0;}
			if($kgpti[$bln]==''){$kgpti[$bln]=0;}
			if($kgptl[$bln]==''){$kgptl[$bln]=0;}
			
			if($bln>$bulan and $bln!='13'){
				$kgiti[$bln]=0;
				$kgpti[$bln]=0;
			}
			
			# untuk graph bar
			$datay[0][]=$bln;
			$datay[1][]=round($kgiti[$bln],0);
			$datay[2][]=round($kgitl[$bln],0);
			
			$datay[3][]=round($kgpti[$bln],0);
			$datay[4][]=round($kgptl[$bln],0);
			
			#untuk table
			$datat[0][0]="";
			$datat[0][]=($bln=='13'?'Bgt':numToMonth($bln));
			
			$datat[1][0]=$tahunini." Inti"; #tahun ini inti
			$datat[1][] =number_format($kgiti[$bln],0);
			$datat[2][0]=$tahunlalu." Inti"; #tahun lalu inti
			$datat[2][] =number_format($kgitl[$bln],0);
			
			$datat[3][0]="- ".$tahunini." Plasma"; #tahun ini plasma
			$datat[3][] =number_format($kgpti[$bln],0);
			
			$datat[4][0]="- ".$tahunlalu." Plasma"; #tahun lalu plasma
			$datat[4][] =number_format($kgptl[$bln],0);
			
			#untuk graph line
			$datay2[0][]=round($kgpti[$bln],0);
			$datay2[1][]=round($kgptl[$bln],0);
		}
		
		
		
		// echo"<pre>";
		// print_r($datay);
		// echo"</pre>";
		// exit("error");
		
		
		$graph1 = new Graph(1275,600); 
		$graph1->img->SetMargin(100,75,60,150);
		$graph1->SetScale('textlin');
		$graph1->SetMarginColor('white');
		

		
		$graph1->title->Set("Todate Production Year on Year ".$nmreg."");
		$graph1->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph1->xaxis->title->Set('KEBUN');
		$graph1->xaxis->HideLabels(true);
		
		$graph1->yaxis->title->Set('Ton');
		$graph1->yaxis->title->SetMargin(10);
		$graph1->yaxis->SetLabelMargin(10);
		$graph1->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		$bplot10 = new BarPlot($datay[1]);
		$bplot11 = new BarPlot($datay[2]);
		#$bplot12 = new BarPlot($datay[3]);
		#$bplot13 = new BarPlot($datay[4]);
		$accbplot1 = new GroupBarPlot(array($bplot10,$bplot11));
		$graph1->Add($accbplot1);
		
		$bplot10->SetColor("#73c9ff");
		$bplot10->SetFillColor('#73c9ff');
		$bplot11->SetColor("#c7c5c5");
		$bplot11->SetFillColor('#c7c5c5');
		#$bplot12->SetColor("red");
		#$bplot12->SetFillColor('red');
		#$bplot13->SetColor("darkgreen");
		#$bplot13->SetFillColor('darkgreen');
		#$bplot10->SetLegend("Cliants");
		
		
		#Create Y2 scale data set 
		$graph1->SetY2Scale("lin");
		$graph1->SetY2OrderBack(false);
		$graph1->y2axis->title->Set('In Ton');
		$graph1->y2axis->title->SetMargin(15); // Some extra margin to clear labels
		$graph1->y2axis->title->SetFont(FF_FONT1,FS_NORMAL,11);
		#$graph1->y2axis->SetLabelFormatCallback('toFahrenheit');
		$graph1->y2axis->SetColor('navy');
		
		
		$l2plot1 = new LinePlot($datay2[0]);
		$graph1->AddY2($l2plot1);
		
		$l2plot1->SetWeight(1);
		$l2plot1->SetColor('blue');
		$l2plot1->SetBarCenter();
		$l2plot1->mark->SetType(MARK_FILLEDCIRCLE,'',5.0);
		$l2plot1->mark->SetWeight(2);
		$l2plot1->mark->SetWidth(5);
		$l2plot1->mark->setColor("blue");
		$l2plot1->mark->setFillColor("blue");
		
		
		$l3plot1 = new LinePlot($datay2[1]);
		$graph1->AddY2($l3plot1);
		
		$l3plot1->SetWeight(1);
		$l3plot1->SetColor('darkgreen');
		$l3plot1->SetBarCenter();
		$l3plot1->mark->SetType(MARK_FILLEDCIRCLE,'',5.0);
		$l3plot1->mark->SetWeight(2);
		$l3plot1->mark->SetWidth(5);
		$l3plot1->mark->setColor("darkgreen");
		$l3plot1->mark->setFillColor("darkgreen");

		
		$table1 = new GTextTable();
		$table1->Set($datat);
		$table1->SetPos(15,455);
		$table1->SetFont(FF_FONT1,FS_NORMAL,8);
		$table1->SetAlign('center');
		$table1->SetColAlign(0,'right');
		$table1->SetMinColWidth(84.8);
		$table1->SetNumberFormat('%0.0f');
		$table1->SetCellFillColor(1,0,'#73c9ff');
		$table1->SetCellFillColor(2,0,'#c7c5c5');
		#$table1->SetCellFillColor(3,0,'blue');
		#$table1->SetCellFillColor(4,0,'darkgreen');
		
		#$table1->SetRowNumberFormat(3,'%0.1f');
		#$table1->SetRowNumberFormat(4,'%0.1f');
		$table1->SetCellColor(3,0,'blue');
		$table1->SetCellColor(4,0,'darkgreen');

		// Format table header row
		$table1->SetRowFillColor(0,'#ded9d9');
		$table1->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table1->SetRowAlign(0,'center');

		# and add it to the graph
		$graph1->Add($table1);
		if (file_exists($fileName2)){
			unlink($fileName2);
		}
		$graph1->Stroke($fileName2);
		
		#$graph->img->Stream($fileName2);
	}
	
	function getGraph2bar(){
		global $folder;global $fileName2;global $param;global $nmreg;global $bulan;global $tahun;global $rangetgl;
		global $tanggal;global $tahunini;global $tahunlalu;global $owlPDO;global $cekreg;global $optreg;global $optorg;
		global $unit_ip;global $optreg;
		
		$e="(";
		for($i=1;$i<=intval($bulan);$i++){
			$r="kg".addZero($i,2);
			if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
		}
		$e.=")";
		
		#budget inti
		$str=" select a.kodeunit, subregional,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a left join ".$dbname.".bgt_regional_assignment b on a.kodeunit=b.kodeunit where 1=1 and tahunbudget = '".$tahun."' and a.kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' and inti='1')";
		$res = fetchdata($str); $bgtibi=0;
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeunit']];
				
			if($param['unit']=='KSP-GROUP'){
				$bgtibi+=$bar['bi']/1000;
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$bgtibi+=$bar['bi']/1000;
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeunit'],$unit_ip)){
				#if($bar['kodeunit']==$param['unit']){
					$bgtibi+=$bar['bi']/1000;
				}
			}
		}
		
		#budget plasma
		$str=" select millcode , kodeunit, olah".$bulan." as bi from ".$dbname.".bgt_produksi_pks where 1=1 and tahunbudget = '".$tahun."'";
		$res = fetchdata($str);$bgtpbi=0;
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeunit']];
			if($bar['subregional']==''){
				$bar['subregional']=$optreg[$bar['millcode']];
			}
			
			if($param['unit']=='KSP-GROUP'){
				$bgtpbi+=$bar['bi']/1000;
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$bgtpbi+=$bar['bi']/1000;
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if(in_array($bar['kodeunit'],$unit_ip)){
				#if($bar['kodeunit']==$param['unit']){
					$bgtpbi+=$bar['bi']/1000;
				}
			}
		}
		
		# untuk graph bar
		$datay[0][]=numToMonth($bulan);
		$datay[0][]="Bgt";
		$datay[1][]=round($param['kgibi'],0);
		$datay[1][]=round($bgtibi,0);
		$datay[2][]=round($param['kgpbi'],0);
		$datay[2][]=round($bgtpbi,0);
		
		#untuk table
		$datat[0][0]="";
		$datat[0][]=numToMonth($bulan);
		$datat[0][]="Bgt";
		
		$datat[1][0]="Inti Tday"; #INTI
		$datat[1][] =round($param['kgibi'],0);
		$datat[1][] =round($bgtibi,0);
		
		$datat[2][0]="Plasma Tday"; #PLASMA
		$datat[2][] =round($param['kgpbi'],0);
		$datat[2][] =round($bgtpbi,0);
		
		
		
		$graph = new Graph(300,600); 
		$graph->img->SetMargin(100,55,50,150);
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		
		#$graph->title->Set("Daily Prod ".$nmreg." - ".numToMonth($bulan)." ".$tahun);
		#$graph->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph->xaxis->title->Set('KEBUN');
		$graph->xaxis->HideLabels(true);
		
		$graph->yaxis->title->Set('In Ton');
		$graph->yaxis->title->SetMargin(20);
		$graph->yaxis->SetLabelMargin(10);
		$graph->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		#$bplot1 = new BarPlot($datay[3]);
		#$bplot1->SetFillColor('orange');
		$bplot2 = new BarPlot($datay[1]);
		$bplot3 = new BarPlot($datay[2]);
		$accbplot = new GroupBarPlot(array($bplot2,$bplot3));
		$graph->Add($accbplot);
		
		$bplot2->SetColor('#41612d');
		$bplot2->SetFillColor('#b2f288');
		$bplot3->SetColor('#858383');
		$bplot3->SetFillColor('#dedede');
		
		$table = new GTextTable();
		$table->Set($datat);
		$table->SetPos(23,455);
		$table->SetFont(FF_FONT1,FS_NORMAL,6);
		$table->SetAlign('center');
		$table->SetColAlign(0,'left');
		$table->SetMinColWidth(73);
		
		$table->SetNumberFormat('%0.0f');
		$table->SetCellFillColor(1,0,'#b2f288');
		$table->SetCellFillColor(2,0,'#dedede');
		
		$table->SetRowFillColor(0,'#ded9d9');
		$table->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table->SetRowAlign(0,'center');

		$graph->Add($table);
		
		
		if (file_exists($param['fileName2bar'])){
			unlink($param['fileName2bar']);
		}
		$graph->Stroke($param['fileName2bar']);
	}
	
	function getGraphCPOdanPK(){
		global $folder;global $param;global $periode;global $bulan;global $tahun;
		global $rangetgl;global $tanggal;global $tahunini;global $tahunlalu;
		global $owlPDO;global $cekreg;global $optreg;global $tgldr;global $tglhi;
		global $optorg;global $optreg;
		
		$str=" select * from ".$dbname.".pabrik_produksi  where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."'";
		$res = fetchdata($str); $tbs=$cpo=$pk=array();
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeorg']];
			if($param['unit']=='KSP-GROUP'){
				$tbs[$bar['tanggal']]+=$bar['tbsdiolah'];
				$cpo[$bar['tanggal']]+=$bar['oer']/1000;						
				$pk[$bar['tanggal']]+=$bar['oerpk']/1000;
				$nmreg="KSP Group - CPO & PK";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$tbs[$bar['tanggal']]+=$bar['tbsdiolah'];
					$cpo[$bar['tanggal']]+=$bar['oer']/1000;						
					$pk[$bar['tanggal']]+=$bar['oerpk']/1000;
					
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region - CPO & PK";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['kodeorg']==$param['unit']){
					$tbs[$bar['tanggal']]+=$bar['tbsdiolah'];
					$cpo[$bar['tanggal']]+=$bar['oer']/1000;						
					$pk[$bar['tanggal']]+=$bar['oerpk']/1000;
				}
				$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Mill - CPO & PK";
			}
		}
		
		foreach($rangetgl as $tgl){					
			$datay[0][]=substr($tgl,8,2);
			$datat[0][0]="";
			$datat[0][]=substr($tgl,8,2);
			
			if($tanggal[$tgl]==''){
				$cpo[$tgl]=0;
				$pk[$tgl]=0;
			}
			if($cpo[$tgl]==''){$cpo[$tgl]=0;}
			if($pk[$tgl]==''){$pk[$tgl]=0;}
			
			$datay[1][]=$cpo[$tgl];
			$datay[2][]=$pk[$tgl];
			
			
			#untuk table
			$datat[1][0]="CPO Tday";
			$datat[1][]=number_format($cpo[$tgl]);
			$datat[2][0]="PK Tday";
			$datat[2][]=number_format($pk[$tgl]);
		}
		
		
		
		$graph = new Graph(1275,600); 
		$graph->img->SetMargin(100,75,60,150);
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		
		$graph->title->Set("Daily Prod ".$nmreg." - ".numToMonth($bulan)." ".$tahun);
		$graph->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph->xaxis->title->Set('KEBUN');
		$graph->xaxis->HideLabels(true);
		
		$graph->yaxis->title->Set('In Ton');
		$graph->yaxis->title->SetMargin(20);
		$graph->yaxis->SetLabelMargin(10);
		$graph->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		#$bplot1 = new BarPlot($datay[3]);
		#$bplot1->SetFillColor('orange');
		$bplot2 = new BarPlot($datay[1]);
		$bplot3 = new BarPlot($datay[2]);
		$accbplot = new GroupBarPlot(array($bplot2,$bplot3));
		$graph->Add($accbplot);
		
		$bplot2->SetColor('#41612d');
		$bplot2->SetFillColor('#b2f288');
		$bplot3->SetColor('#858383');
		$bplot3->SetFillColor('#dedede');
		
		
		$table = new GTextTable();
		$table->Set($datat);
		$table->SetPos(40,455);
		$table->SetFont(FF_FONT1,FS_NORMAL,6);
		$table->SetAlign('center');
		$table->SetColAlign(0,'left');
		if(count($rangetgl)=='31'){$table->SetMinColWidth(35.5);}
		if(count($rangetgl)=='30'){$table->SetMinColWidth(36.7);}
		if(count($rangetgl)=='29'){$table->SetMinColWidth(38);}
		if(count($rangetgl)=='28'){$table->SetMinColWidth(39.4);}
		
		$table->SetNumberFormat('%0.0f');
		$table->SetCellFillColor(1,0,'#b2f288');
		$table->SetCellFillColor(2,0,'#dedede');
		
		$table->SetRowFillColor(0,'#ded9d9');
		$table->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table->SetRowAlign(0,'center');

		$graph->Add($table);
		
		
		if (file_exists($param['getGraphCPOdanPK'])){
			unlink($param['getGraphCPOdanPK']);
		}
		$graph->Stroke($param['getGraphCPOdanPK']);
		#$graph->Stroke();
	}
	
	function getGraph2barcpodanpk(){
		global $folder;global $param;global $periode;global $bulan;global $tahun;
		global $tgldr;global $tglhi;global $rangetgl;global $tanggal;global $tahunini;
		global $tahunlalu;global $owlPDO;global $cekreg;global $optreg; global $optorg;global $optreg;
		
		$cpo=$pk=$bgtcpo=$bgtpk=0;
		$str=" select * from ".$dbname.".pabrik_produksi  where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."'"; 
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeorg']];
			
			if($param['unit']=='KSP-GROUP'){
				$cpo+=$bar['oer']/1000;						
				$pk+=$bar['oerpk']/1000;
				$nmreg="KSP Group - CPO & PK";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$cpo+=$bar['oer']/1000;						
					$pk+=$bar['oerpk']/1000;
					
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region - CPO & PK";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['kodeorg']==$param['unit']){
					$cpo+=$bar['oer']/1000;						
					$pk+=$bar['oerpk']/1000;
				}
				$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Mill - CPO & PK";
			}
		}
		
		// print_r($cpo);
		// exit();
		
		#budget
		$str=" select millcode , kodeunit, kgcpo".$bulan." as bi, kgker".$bulan." as pk from ".$dbname.".bgt_produksi_pks_vw where 1=1 and tahunbudget = '".$tahun."'";
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['millcode']];
			
			if($param['unit']=='KSP-GROUP'){
				$bgtcpo+=$bar['bi']/1000;
				$bgtpk+=$bar['pk']/1000;
			}if($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					$bgtcpo+=$bar['bi']/1000;
					$bgtpk+=$bar['pk']/1000;
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['millcode']==$param['unit']){
					$bgtcpo+=$bar['bi']/1000;
					$bgtpk+=$bar['pk']/1000;
				}
			}
		}
		
		# untuk graph bar
		$datay[0][]=numToMonth($bulan);
		$datay[0][]="Bgt";
		$datay[1][]=round($cpo,0);
		$datay[1][]=round($bgtcpo,0);
		$datay[2][]=round($pk,0);
		$datay[2][]=round($bgtpk,0);
		
		#untuk table
		$datat[0][0]="";
		$datat[0][]=numToMonth($bulan);
		$datat[0][]="Bgt";
		
		$datat[1][0]="CPO Tday";
		$datat[1][] =round($cpo,0);
		$datat[1][] =round($bgtcpo,0);
		
		$datat[2][0]="PK Tday";
		$datat[2][] =round($pk,0);
		$datat[2][] =round($bgtpk,0);
		
		
		
		$graph = new Graph(300,600); 
		$graph->img->SetMargin(100,55,50,150);
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		
		#$graph->title->Set("Daily Prod ".$nmreg." - ".numToMonth($bulan)." ".$tahun);
		#$graph->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph->xaxis->title->Set('KEBUN');
		$graph->xaxis->HideLabels(true);
		
		$graph->yaxis->title->Set('In Ton');
		$graph->yaxis->title->SetMargin(20);
		$graph->yaxis->SetLabelMargin(10);
		$graph->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		#$bplot1 = new BarPlot($datay[3]);
		#$bplot1->SetFillColor('orange');
		$bplot2 = new BarPlot($datay[1]);
		$bplot3 = new BarPlot($datay[2]);
		$accbplot = new GroupBarPlot(array($bplot2,$bplot3));
		$graph->Add($accbplot);
		
		$bplot2->SetColor('#41612d');
		$bplot2->SetFillColor('#b2f288');
		$bplot3->SetColor('#858383');
		$bplot3->SetFillColor('#dedede');
		
		$table = new GTextTable();
		$table->Set($datat);
		$table->SetPos(23,455);
		$table->SetFont(FF_FONT1,FS_NORMAL,6);
		$table->SetAlign('center');
		$table->SetColAlign(0,'left');
		$table->SetMinColWidth(73);
		
		$table->SetNumberFormat('%0.0f');
		$table->SetCellFillColor(1,0,'#b2f288');
		$table->SetCellFillColor(2,0,'#dedede');
		
		$table->SetRowFillColor(0,'#ded9d9');
		$table->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table->SetRowAlign(0,'center');

		$graph->Add($table);
		
		
		if (file_exists($param['getGraph2barcpodanpk'])){
			unlink($param['getGraph2barcpodanpk']);
		}
		$graph->Stroke($param['getGraph2barcpodanpk']);
	}
	
	function getGraphCpoPkThn(){
		global $folder;global $param;global $periode;global $bulan;global $tahun;
		global $tgldr;global $tglhi;global $rangetgl;global $tanggal;global $tahunini;
		global $tahunlalu;global $owlPDO;global $cekreg;global $optreg; global $optorg;
		global $cekbln;global $optreg;
		
		$cpoti=$pkti=$cpotl=$pktl=array();
		
		$rangebln = range(1,12);
		$str=" select * from ".$dbname.".pabrik_produksi  where 1=1 and substr(tanggal,1,4) between '".$tahunlalu."' and '".$tahunini."'";
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeorg']];
			$bar['tahun']=substr($bar['tanggal'],0,4);
			$bar['periode']=substr($bar['tanggal'],5,2);
			
			if($param['unit']=='KSP-GROUP'){
				if($bar['tahun']==$tahunini){
					foreach($rangebln as $bln){
						$bln=addZero($bln,2);
						if($bln>=$bar['periode']){
							$cpoti[$bln]+=$bar['oer']/1000;
							$pkti[$bln]+=$bar['oerpk']/1000;						
						}
					}	
				}else{
					foreach($rangebln as $bln){
						$bln=addZero($bln,2);
						if($bln>=$bar['periode']){
							$cpotl[$bln]+=$bar['oer']/1000;
							$pktl[$bln]+=$bar['oerpk']/1000;						
						}
					}
				}
				$nmreg="KSP Group - CPO & PK";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tahun']==$tahunini){
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$bar['periode']){
								$cpoti[$bln]+=$bar['oer']/1000;
								$pkti[$bln]+=$bar['oerpk']/1000;						
							}
						}	
					}else{
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$bar['periode']){
								$cpotl[$bln]+=$bar['oer']/1000;
								$pktl[$bln]+=$bar['oerpk']/1000;						
							}
						}
					}
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region - CPO & PK";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['kodeorg']==$param['unit']){
					if($bar['tahun']==$tahunini){
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$bar['periode']){
								$cpoti[$bln]+=$bar['oer']/1000;
								$pkti[$bln]+=$bar['oerpk']/1000;						
							}
						}	
					}else{
						foreach($rangebln as $bln){
							$bln=addZero($bln,2);
							if($bln>=$bar['periode']){
								$cpotl[$bln]+=$bar['oer']/1000;
								$pktl[$bln]+=$bar['oerpk']/1000;						
							}
						}
					}
				}
				$nmreg=$param['unit']." Mill - CPO & PK";
			}
		}
		
		
		$str=" select tahunbudget, millcode , kodeunit, sum(kgcpo) as cpo, sum(kgkernel) as pk from ".$dbname.".bgt_produksi_pks_vw where 1=1 and tahunbudget between '".$tahunlalu."' and '".$tahunini."' group by tahunbudget,millcode,kodeunit";
		$res = fetchdata($str); 
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['millcode']];
			
			if($param['unit']=='KSP-GROUP'){
				if($bar['tahunbudget']==$tahunini){						
					$cpoti[13]+=$bar['cpo']/1000;
					$pkti[13]+=$bar['pk']/1000;
				}else{
					$cpotl[13]+=$bar['cpo']/1000;
					$pktl[13]+=$bar['pk']/1000;
				}
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){						
						$cpoti[13]+=$bar['cpo']/1000;
						$pkti[13]+=$bar['pk']/1000;
					}else{
						$cpotl[13]+=$bar['cpo']/1000;
						$pktl[13]+=$bar['pk']/1000;
					}
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['millcode']==$param['unit']){
					if($bar['tahunbudget']==$tahunini){						
						$cpoti[13]+=$bar['cpo']/1000;
						$pkti[13]+=$bar['pk']/1000;
					}else{
						$cpotl[13]+=$bar['cpo']/1000;
						$pktl[13]+=$bar['pk']/1000;
					}
				}
			}
		}
		// echo"<pre>";
		// print_r($cpoti);
		// print_r($isi);
		// echo"</pre>";
		// exit("error");
		
		$rangebln = range(1,13);
		foreach($rangebln as $bln){
			$bln=addZero($bln,2);
			if($cpoti[$bln]==''){$cpoti[$bln]=0;}
			if($pkti[$bln]==''){$pkti[$bln]=0;}
			if($cpotl[$bln]==''){$cpotl[$bln]=0;}
			if($pktl[$bln]==''){$pktl[$bln]=0;}
			
			if($bln>$bulan and $bln!='13'){
				$cpoti[$bln]=0;
				$pkti[$bln]=0;
			}
			
			# untuk graph bar
			$datay[0][]=$bln;
			$datay[1][]=round($cpoti[$bln],0);
			$datay[2][]=round($cpotl[$bln],0);
			
			$datay[3][]=round($pkti[$bln],0);
			$datay[4][]=round($pktl[$bln],0);
			
			#untuk table
			$datat[0][0]="";
			$datat[0][]=($bln=='13'?'Bgt':numToMonth($bln));
			
			$datat[1][0]=$tahunini." CPO"; #tahun ini cpo
			$datat[1][] =number_format($cpoti[$bln],0);
			$datat[2][0]=$tahunlalu." CPO"; #tahun lalu cpo
			$datat[2][] =number_format($cpotl[$bln],0);
			
			$datat[3][0]="- ".$tahunini." PK"; #tahun ini pk
			$datat[3][] =number_format($pkti[$bln],0);
			
			$datat[4][0]="- ".$tahunlalu." PK"; #tahun lalu pk
			$datat[4][] =number_format($pktl[$bln],0);
			
			#untuk graph line
			$datay2[0][]=round($pkti[$bln],0);
			$datay2[1][]=round($pktl[$bln],0);
		}
		
		
		// echo"<pre>";
		// print_r($bln);
		// echo"</pre>";
		// exit("error");
		
		
		$graph1 = new Graph(1275,600); 
		$graph1->img->SetMargin(100,75,60,150);
		$graph1->SetScale('textlin');
		$graph1->SetMarginColor('white');
		

		
		$graph1->title->Set("Todate Production Year on Year ".$nmreg."");
		$graph1->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph1->xaxis->title->Set('KEBUN');
		$graph1->xaxis->HideLabels(true);
		
		$graph1->yaxis->title->Set('In Ton');
		$graph1->yaxis->title->SetMargin(10);
		$graph1->yaxis->SetLabelMargin(10);
		$graph1->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		$bplot10 = new BarPlot($datay[1]);
		$bplot11 = new BarPlot($datay[2]);
		#$bplot12 = new BarPlot($datay[3]);
		#$bplot13 = new BarPlot($datay[4]);
		$accbplot1 = new GroupBarPlot(array($bplot10,$bplot11));
		$graph1->Add($accbplot1);
		
		$bplot10->SetColor("#73c9ff");
		$bplot10->SetFillColor('#73c9ff');
		$bplot11->SetColor("#c7c5c5");
		$bplot11->SetFillColor('#c7c5c5');
		#$bplot12->SetColor("red");
		#$bplot12->SetFillColor('red');
		#$bplot13->SetColor("darkgreen");
		#$bplot13->SetFillColor('darkgreen');
		#$bplot10->SetLegend("Cliants");
		
		
		#Create Y2 scale data set 
		$graph1->SetY2Scale("lin");
		$graph1->SetY2OrderBack(false);
		$graph1->y2axis->title->Set('In Ton');
		$graph1->y2axis->title->SetMargin(15); // Some extra margin to clear labels
		$graph1->y2axis->title->SetFont(FF_FONT1,FS_NORMAL,11);
		#$graph1->y2axis->SetLabelFormatCallback('toFahrenheit');
		$graph1->y2axis->SetColor('navy');
		
		
		$l2plot1 = new LinePlot($datay2[0]);
		$graph1->AddY2($l2plot1);
		
		$l2plot1->SetWeight(1);
		$l2plot1->SetColor('blue');
		$l2plot1->SetBarCenter();
		$l2plot1->mark->SetType(MARK_FILLEDCIRCLE,'',5.0);
		$l2plot1->mark->SetWeight(2);
		$l2plot1->mark->SetWidth(5);
		$l2plot1->mark->setColor("blue");
		$l2plot1->mark->setFillColor("blue");
		
		
		$l3plot1 = new LinePlot($datay2[1]);
		$graph1->AddY2($l3plot1);
		
		$l3plot1->SetWeight(1);
		$l3plot1->SetColor('darkgreen');
		$l3plot1->SetBarCenter();
		$l3plot1->mark->SetType(MARK_FILLEDCIRCLE,'',5.0);
		$l3plot1->mark->SetWeight(2);
		$l3plot1->mark->SetWidth(5);
		$l3plot1->mark->setColor("darkgreen");
		$l3plot1->mark->setFillColor("darkgreen");

		
		$table1 = new GTextTable();
		$table1->Set($datat);
		$table1->SetPos(15,455);
		$table1->SetFont(FF_FONT1,FS_NORMAL,8);
		$table1->SetAlign('center');
		$table1->SetColAlign(0,'right');
		$table1->SetMinColWidth(84.8);
		$table1->SetNumberFormat('%0.0f');
		$table1->SetCellFillColor(1,0,'#73c9ff');
		$table1->SetCellFillColor(2,0,'#c7c5c5');
		#$table1->SetCellFillColor(3,0,'blue');
		#$table1->SetCellFillColor(4,0,'darkgreen');
		
		#$table1->SetRowNumberFormat(3,'%0.1f');
		#$table1->SetRowNumberFormat(4,'%0.1f');
		$table1->SetCellColor(3,0,'blue');
		$table1->SetCellColor(4,0,'darkgreen');

		// Format table header row
		$table1->SetRowFillColor(0,'#ded9d9');
		$table1->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table1->SetRowAlign(0,'center');

		# and add it to the graph
		$graph1->Add($table1);
		if (file_exists($param['getGraphCpoPkThn'])){
			unlink($param['getGraphCpoPkThn']);
		}
		$graph1->Stroke($param['getGraphCpoPkThn']);
		
		#$graph->img->Stream($fileName2);
	}
	
	function getGraphOerKerFfa(){
		global $folder;global $param;global $periode;global $bulan;global $tahun;
		global $rangetgl;global $tanggal;global $tahunini;global $tahunlalu;
		global $owlPDO;global $cekreg;global $optreg;global $tgldr;global $tglhi;
		global $optorg;global $optreg;
		
		$cpo=$pk=$ffa=array();
		$str=" select * from ".$dbname.".pabrik_produksi  where 1=1 and tanggal between '".$tgldr."' and '".$tglhi."'";
		$res = fetchdata($str);
		foreach($res as $bar){	
			$bar['subregional']=$optreg[$bar['kodeorg']];
				
			if($param['unit']=='KSP-GROUP'){
				if($bar['tbsdiolah']>0){						
					$cpo[$bar['tanggal']]+=$bar['oer']/$bar['tbsdiolah']*100;
					$pk[$bar['tanggal']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
				}
				$ffa[$bar['tanggal']]+=$bar['ffa'];
				$nmreg="KSP Group";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tbsdiolah']>0){						
						$cpo[$bar['tanggal']]+=$bar['oer']/$bar['tbsdiolah']*100;
						$pk[$bar['tanggal']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
					}
					$ffa[$bar['tanggal']]+=$bar['ffa'];
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['kodeorg']==$param['unit']){
					if($bar['tbsdiolah']>0){						
						$cpo[$bar['tanggal']]+=$bar['oer']/$bar['tbsdiolah']*100;
						$pk[$bar['tanggal']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
					}
					$ffa[$bar['tanggal']]+=$bar['ffa'];
				}
				$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Mill";
			}
		}
		
		// echo"<pre>";
		// print_r($cpo);
		// print_r($pk);
		// echo"</pre>";
		
		foreach($rangetgl as $tgl){					
			$datay[0][]=substr($tgl,8,2);
			$datat[0][0]="";
			$datat[0][]=substr($tgl,8,2);
			
			if($tanggal[$tgl]==''){
				$cpo[$tgl]=0;
				$pk[$tgl]=0;
				$ffa[$tgl]=0;
			}
			if($cpo[$tgl]==''){$cpo[$tgl]=0;}
			if($pk[$tgl]==''){$pk[$tgl]=0;}
			if($ffa[$tgl]==''){$ffa[$tgl]=0;}
			
			$datay[1][]=$cpo[$tgl];
			$datay[2][]=$pk[$tgl];
			$datay[3][]=$ffa[$tgl];
			
			
			#untuk table
			$datat[1][0]="OER";
			$datat[1][]=number_format($cpo[$tgl],1);
			$datat[2][0]="KER";
			$datat[2][]=number_format($pk[$tgl],1);
			$datat[3][0]="FFA";
			$datat[3][]=number_format($ffa[$tgl],1);
		}
		
		
		
		$graph = new Graph(1275,600); 
		$graph->img->SetMargin(100,75,60,150);
		$graph->SetScale('textlin');
		$graph->SetMarginColor('white');
		
		$graph->title->Set("Daily Prod ".$nmreg." - ".numToMonth($bulan)." ".$tahun." - OER, KER & FFA");
		$graph->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph->xaxis->title->Set('KEBUN');
		$graph->xaxis->HideLabels(true);
		
		$graph->yaxis->title->Set('In %');
		$graph->yaxis->title->SetMargin(20);
		$graph->yaxis->SetLabelMargin(10);
		$graph->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		$bplot2 = new BarPlot($datay[1]);
		$bplot3 = new BarPlot($datay[2]);
		$bplot1 = new BarPlot($datay[3]);
		$accbplot = new GroupBarPlot(array($bplot2,$bplot3,$bplot1));
		$graph->Add($accbplot);
		
		$bplot2->SetColor('#41612d');
		$bplot2->SetFillColor('#b2f288');
		$bplot3->SetColor('#858383');
		$bplot3->SetFillColor('#3489f7');
		$bplot1->SetColor('#858383');
		$bplot1->SetFillColor('#dedede');
		
		
		$table = new GTextTable();
		$table->Set($datat);
		$table->SetPos(65,455);
		$table->SetFont(FF_FONT1,FS_NORMAL,6);
		$table->SetAlign('center');
		$table->SetColAlign(0,'left');
		if(count($rangetgl)=='31'){$table->SetMinColWidth(35.5);}
		if(count($rangetgl)=='30'){$table->SetMinColWidth(36.7);}
		if(count($rangetgl)=='29'){$table->SetMinColWidth(38);}
		if(count($rangetgl)=='28'){$table->SetMinColWidth(39.4);}
		
		$table->SetNumberFormat('%0.0f');
		$table->SetCellFillColor(1,0,'#b2f288');
		$table->SetCellFillColor(2,0,'#3489f7');
		$table->SetCellFillColor(3,0,'#dedede');
		$table->SetRowNumberFormat(1,'%0.1f');
		$table->SetRowNumberFormat(2,'%0.1f');
		$table->SetRowNumberFormat(3,'%0.1f');
		
		$table->SetRowFillColor(0,'#ded9d9');
		$table->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table->SetRowAlign(0,'center');

		$graph->Add($table);
		
		
		if (file_exists($param['getGraphOerKerFfa'])){
			unlink($param['getGraphOerKerFfa']);
		}
		$graph->Stroke($param['getGraphOerKerFfa']);
		#$graph->Stroke();
	}
	
	function getGraphOerKerFfaTodate(){
		global $folder;global $param;global $periode;global $bulan;global $tahun;
		global $rangetgl;global $tanggal;global $tahunini;global $tahunlalu;
		global $owlPDO;global $cekreg;global $optreg;global $tgldr;global $tglhi;
		global $optorg;global $optreg;
		
		$cpo=$pk=array();
		$str=" select kodeorg, substr(tanggal,1,4) as tahun, substr(tanggal,1,7) as periode, sum(oer) as oer, sum(oerpk) as oerpk, sum(tbsdiolah) as tbsdiolah from ".$dbname.".pabrik_produksi  where 1=1 and substr(tanggal,1,4) between '".$tahunlalu."' and '".$tahunini."' group by kodeorg, substr(tanggal,1,7)";
		$res = fetchdata($str); $cpotl=$cpoti=$pkti=$pktl=array();
		foreach($res as $bar){
			$bar['subregional']=$optreg[$bar['kodeorg']];
			$bar['periode']=substr($bar['periode'],5,2);
			
			if($param['unit']=='KSP-GROUP'){
				if($bar['tahun']==$tahunini){
					if($bar['tbsdiolah']>0){						
						$cpoti[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
						$pkti[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
					}
				}else{
					if($bar['tbsdiolah']>0){						
						$cpotl[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
						$pktl[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
					}
				}
				$nmreg="KSP Group - OER, KER";
			}elseif($cekreg[$param['unit']]!=''){
				#ini by region
				if($bar['subregional']==$param['unit']){
					if($bar['tahun']==$tahunini){
						if($bar['tbsdiolah']>0){						
							$cpoti[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
							$pkti[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
						}
					}else{
						if($bar['tbsdiolah']>0){						
							$cpotl[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
							$pktl[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
						}
					}
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Region - OER, KER";
				}
			}elseif($optorg[$param['unit']]!=''){
				#ini by unit
				if($bar['kodeorg']==$param['unit']){
					if($bar['tahun']==$tahunini){
						if($bar['tbsdiolah']>0){						
							$cpoti[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
							$pkti[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
						}
					}else{
						if($bar['tbsdiolah']>0){						
							$cpotl[$bar['periode']]+=$bar['oer']/$bar['tbsdiolah']*100;
							$pktl[$bar['periode']]+=$bar['oerpk']/$bar['tbsdiolah']*100;						
						}
					}
					$nmreg=ucfirst(strtolower($cekreg[$bar['subregional']]))." Mill - OER, KER";
				}
			}
		}
		
		// echo"<pre>";
		// print_r($cpoti);
		
		// echo"</pre>";
		// exit();
		
		$rangebln = range(1,12);
		foreach($rangebln as $bln){
			$bln=addZero($bln,2);
			if($cpoti[$bln]==''){$cpoti[$bln]=0;}
			if($pkti[$bln]==''){$pkti[$bln]=0;}
			if($cpotl[$bln]==''){$cpotl[$bln]=0;}
			if($pktl[$bln]==''){$pktl[$bln]=0;}
			
			# untuk graph bar
			$datay[0][]=$bln;
			$datay[1][]=round($cpoti[$bln],2);
			$datay[2][]=round($cpotl[$bln],2);
			
			$datay[3][]=round($pkti[$bln],2);
			$datay[4][]=round($pktl[$bln],2);
			
			#untuk table
			$datat[0][0]="";
			$datat[0][]=numToMonth($bln);
			
			$datat[1][0]=$tahunini." CPO"; #tahun ini cpo
			$datat[1][] =number_format($cpoti[$bln],2);
			$datat[2][0]=$tahunlalu." CPO"; #tahun lalu cpo
			$datat[2][] =number_format($cpotl[$bln],2);
			
			$datat[3][0]=$tahunini." PK"; #tahun ini pk
			$datat[3][] =number_format($pkti[$bln],2);
			
			$datat[4][0]=$tahunlalu." PK"; #tahun lalu pk
			$datat[4][] =number_format($pktl[$bln],2);
			
		}
		
		
		// echo"<pre>";
		// print_r($datay);
		// echo"</pre>";
		// exit("error");
		
		
		$graph1 = new Graph(1275,600); 
		$graph1->img->SetMargin(100,75,60,150);
		$graph1->SetScale('textlin');
		$graph1->SetMarginColor('white');
		

		
		$graph1->title->Set("Todate Production Year on Year ".$nmreg."");
		$graph1->title->SetFont(FF_FONT2,FS_BOLD,15);
		//$graph1->xaxis->title->Set('KEBUN');
		$graph1->xaxis->HideLabels(true);
		
		$graph1->yaxis->title->Set('In %');
		$graph1->yaxis->title->SetMargin(10);
		$graph1->yaxis->SetLabelMargin(10);
		$graph1->yaxis->SetLabelAlign('right','center');



		// Create the bars and the accbar plot
		$bplot10 = new BarPlot($datay[1]);
		$bplot11 = new BarPlot($datay[2]);
		$bplot12 = new BarPlot($datay[3]);
		$bplot13 = new BarPlot($datay[4]);
		$accbplot1 = new GroupBarPlot(array($bplot10,$bplot11,$bplot12,$bplot13));
		$graph1->Add($accbplot1);
		
		$bplot10->SetColor("#73c9ff");
		$bplot10->SetFillColor('#73c9ff');
		$bplot11->SetColor("#c7c5c5");
		$bplot11->SetFillColor('#c7c5c5');
		$bplot12->SetColor("#b2f288");
		$bplot12->SetFillColor('#b2f288');
		$bplot13->SetColor("#11bd7b");
		$bplot13->SetFillColor('#11bd7b');
		#$bplot10->SetLegend("Cliants");
		
		
		$table1 = new GTextTable();
		$table1->Set($datat);
		$table1->SetPos(7,455);
		$table1->SetFont(FF_FONT1,FS_NORMAL,8);
		$table1->SetAlign('center');
		$table1->SetColAlign(0,'right');
		$table1->SetMinColWidth(91.8);
		$table1->SetNumberFormat('%0.0f');
		$table1->SetCellFillColor(1,0,'#73c9ff');
		$table1->SetCellFillColor(2,0,'#c7c5c5');
		$table1->SetCellFillColor(3,0,'#b2f288');
		$table1->SetCellFillColor(4,0,'#11bd7b');
		
		$table1->SetRowNumberFormat(1,'%0.1f');
		$table1->SetRowNumberFormat(2,'%0.1f');
		$table1->SetRowNumberFormat(3,'%0.1f');
		$table1->SetRowNumberFormat(4,'%0.1f');
		#$table1->SetCellColor(3,0,'blue');
		#$table1->SetCellColor(4,0,'darkgreen');

		// Format table header row
		$table1->SetRowFillColor(0,'#ded9d9');
		$table1->SetRowFont(0,FF_FONT1,FS_BOLD,8);
		$table1->SetRowAlign(0,'center');

		$graph1->Add($table1);
		
		
		if (file_exists($param['getGraphOerKerFfaTodate'])){
			unlink($param['getGraphOerKerFfaTodate']);
		}
		$graph1->Stroke($param['getGraphOerKerFfaTodate']);
	}
	
?>
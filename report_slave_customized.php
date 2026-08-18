<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/fpdf.php');

if(isset($_POST) & count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;	
}
switch($param['action']){
	case 'load':
		$parameter=str_replace("\\","",$param['parameter']);
		$parameter=str_replace("::persen::","%",$parameter);
		if($parameter!=''){
			$parameter=" where ".$parameter;
		}
		$str="select * from ".$dbname.".tool_userdefinedreport where rnumber=".$param['rnumber'];
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$query=$bar->query;
			$kolomTampil=explode(",",$bar->kolomtampil);
			$group=explode(",",$bar->group);
			$subtotal=explode(",",$bar->subtotal);
			$judul=$bar->namalaporan;
		}
		$query=str_replace('#PARAMETER#',$parameter,$query);
		$tab="<fieldset><b>".strtoupper($judul)."</b><table class=sortable cellspacing=1 border=0>
		      <thead>
			  <tr class=rowheader><td>No</td>";
			  foreach($kolomTampil as $key=>$vall){
				$tab.="<td>".$vall."</td>";
				$arrHeader[]=$vall;
			  }
		$tab.="</tr></thead><tbody>";
		$total=Array();
		$subVal=Array();
		$prevVal=Array();
		$printSubtotal=Array();
		$avg=Array();
		$totalAvg=Array();
		$content=Array();
		$no=0;
		$contentIndex=-1;
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM);		
		while ($bar = $res->fetch()) {
			$no++;
			foreach($bar as $key1=>$val){
				if($subtotal[$key1]=='1'){
					if($prevVal[$key1]!=$bar[$key1] && $no!=0){
						$printSubtotal=$subVal;
						$subVal=Array();
						$subAvg=$avg;
						$avg=Array();
					}
					foreach($group as $kk=>$kval){
					  if($kval!='0'){
						if($kval=='sum'){
							$subVal[$kk]+=$bar[$kk];
						}
						 else{
							$avg[]=$bar[$kk];	
						 }
					  }
					}
				}	
			}
//print Subtotal
			if(count($printSubtotal)>0){
				$contentIndex++;
				$tab.="<tr class=rowcontent><td></td>";
				foreach($kolomTampil as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Subtotal</b></td>";
						$content[$contentIndex][$key1]='Subtotal';
					}else{
						if($group[$key1]=='sum'){
							if($printSubtotal[$key1]!=''){							
								$tab.="<td align=right><b>".number_format($printSubtotal[$key1],2)."</b></td>";
								$total[$key1]+=$printSubtotal[$key1];
								$content[$contentIndex][$key1]=number_format($printSubtotal[$key1],2);
							}else{
								$tab.="<td align=right></b></td>";
								$total[$key1]+='0';
								$content[$contentIndex][$key1]='';
							}  
						}else if($group[$key1]=='avg'){
							$tab.="<td align=right><b>".number_format(array_sum($subAvg)/count($subAvg),2)."</b></td>";
							$totalAvg[0]+=array_sum($subAvg);
							$totalAvg[1]+=count($subAvg);
							$content[$contentIndex][$key1]=number_format(array_sum($subAvg)/count($subAvg),2);
						}else{
							$tab.="<td align=right></b></td>";
							$total[$key1]+='0';
							$content[$contentIndex][$key1]='';
						}
					}
				}
				$tab.="</tr>";
				$printSubtotal=Array();
			}
			//print regular row
			$contentIndex++;
			$tab.="<tr class=rowcontent><td>".$no."</td>";
			foreach($kolomTampil as $key1=>$val){
				if($group[$key1]!='0' && isset($group[$key1]) && $group[$key1]!=''){
					$tab.="<td align=right>".number_format($bar[$key1],2)."</td>";
					$content[$contentIndex][$key1]=number_format($bar[$key1],2);
				}else{
					$tab.="<td>".$bar[$key1]."</td>";
					$content[$contentIndex][$key1]=$bar[$key1];
				}
				$prevVal[$key1]=$bar[$key1];
			}
			$tab.="</tr>";
		}#end loop
			$contentIndex++;
			//print last Subtotal
			if(count($subVal)>0){
				$tab.="<tr class=rowcontent><td></td>";
				foreach($group as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Subtotal</b></td>";
						$content[$contentIndex][$key1]='Subtotal';
					}else{
						if($val=='sum'){  
							if($subVal[$key1]!=''){							
								$tab.="<td align=right><b>".number_format($subVal[$key1],2)."</b></td>";
								$total[$key1]+=$subVal[$key1];
								$content[$contentIndex][$key1]=number_format($subVal[$key1],2);
							}else{
								$tab.="<td align=right></b></td>";
								$total[$key1]+='0';
								$content[$contentIndex][$key1]='';
							}
						}else if($val=='avg'){
								$tab.="<td align=right><b>".number_format(array_sum($avg)/count($avg),2)."</b></td>";
								$totalAvg[0]+=array_sum($avg);
								$totalAvg[1]+=count($avg);
								$content[$contentIndex][$key1]=number_format(array_sum($avg)/count($avg),2);
							
						}else{
							$tab.="<td align=right></b></td>";
							$total[$key1]+='0';
							$content[$contentIndex][$key1]='';
						}
					}
				}
				$tab.="</tr>";
				//print total
				$contentIndex++;
				$tab.="<tr class=rowcontent><td></td>";
				foreach($kolomTampil as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Grand Total</b></td>";
						$content[$contentIndex][$key1]='Grand Total';
					}else{
							if($group[$key1]=='sum'){				
								if($total[$key1]!=''){
									$tab.="<td align=right><b>".number_format($total[$key1],2)."</b></td>";
									$content[$contentIndex][$key1]=number_format($total[$key1],2);
								}else{
									$tab.="<td align=right></b></td>";
									$content[$contentIndex][$key1]='';
								}
							}else if($group[$key1]=='avg'){
								$tab.="<td align=right><b>".number_format(($totalAvg[0]/$totalAvg[1]),2)."</b></td>";
								$content[$contentIndex][$key1]=number_format(($totalAvg[0]/$totalAvg[1]),2);
							}else{
							$tab.="<td align=right></b></td>";
							$content[$contentIndex][$key1]='';
							}
						}
				}				
			}
		$tab.="</tr>";		
		$tab.="</tbody></table><tfoot></tfoot></fieldset>";
		if($param['tipe']=='html'){
			echo $tab;
		}else if($param['tipe']=='excel'){
			$fileName=str_replace(" ","_",$judul).date('His');
			header("Cache-Control: must-revalidate");
			header("Pragma: must-revalidate");
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: attachment; filename=".$fileName.".xlsx");
			echo $tab;
		}else if($param['tipe']=='pdf'){
			//create pdf here
			#WAJIB BACA===============
			//use parameter $content because the result has been formatted in variavle $content
			//$arrHeader = adalah array baris judul
			//$content  = adalah table yang tinggal di print
			#================	
			$colLength=190/count($arrHeader);
			$rowHeight=5;
			$fontSize='9';
			if($colLength>5){
				$fontSize='7';
				$rowHeight=4;
			}
			if($colLength>10){
				$fontSize='5';
				$rowHeight=3;
			}			
			class PDF extends FPDF{
				function Header(){
				 global $arrHeader;
				 global $rowHeight;
				 global $fontSize;
				 global $colLength;
				 global $judul;
				 $colLength=180/count($arrHeader);
				 $this->SetY(10);
				 $this->SetFont('Arial','B','8');
				 $this->Cell(190,5,$judul,'',1,'C');
					foreach($arrHeader as $key=>$val){
					   if($key==(count($arrHeader)-1)){
							$this->Cell($colLength,$rowHeight,$val,1,1,'C');
					   }else{
							$this->Cell($colLength,$rowHeight,$val,1,0,'C');
						}
					}
				}
				function Footer(){
						$this->SetY(-15);
						$this->SetFont('Arial','I',8);
						$this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
				}
			}
			$pdf=new PDF('P','mm','A4');
			$pdf->SetMargins(10,'',10);
			$pdf->AddPage();	
			foreach($content as $key=>$val){
				foreach($val as $k=>$v){
				   if($k==count($val)-1){
						$pdf->Cell($colLength,$rowHeight,$v,0,1,'L');	
					}else{
						$pdf->Cell($colLength,$rowHeight,$v,0,0,'L');
					}
				}
			}	
			$pdf->Output();
		}else{
			echo "No output format defined";
		}
	break;
	default:
	break;
}
?>
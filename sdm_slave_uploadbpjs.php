<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$str="select * from ".$dbname.".sdm_ho_component";
$res=fetchdata($str);
foreach($res as $bar){				
	$nmcomp[$bar['id']]=$bar['name'];
}

switch ($method) {
	case'delete':
		try{
		$owlPDO->beginTransaction();
			$wh="";
			$str="select kodeorg, karyawanid, periodegaji, idkomponen from ".$dbname.".sdm_gaji_vw where namakaryawan is not null and sumber='UPLOAD' and substr(kodeorg,1,4) = '".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekary']."' order by namakaryawan asc";
			$res=fetchdata($str);
			if(count($res)==0){
				$str="select b.lokasitugas as kodeorg, a.karyawanid, a.periodegaji, a.idkomponen from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.namakaryawan is null and a.sumber='UPLOAD' and substr(b.lokasitugas,1,4) = '".$param['kodeorg']."' and a.periodegaji='".$param['periode']."' and b.tipekaryawan='".$param['tipekary']."' order by b.namakaryawan asc ";
				$res=fetchdata($str);
			}
			foreach($res as $val){
				$str="delete from ".$dbname.".sdm_gaji  where kodeorg='".$val['kodeorg']."' and periodegaji='".$val['periodegaji']."' and karyawanid='".$val['karyawanid']."' and idkomponen='".$val['idkomponen']."'";
				$owlPDO->exec($str);				
			}
			
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
    case'loaddata':
        $where = "";
        $tab = "";
		$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 id=mytable width=100%>
			<thead>
			<tr class=rowheader>
				<th align=center width=30px>No.</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['periode']."</th>
				<th align=center>".$_SESSION['lang']['tipekaryawan']."</th>
				";
				
				$str="select distinct idkomponen from ".$dbname.".sdm_gaji where sumber='UPLOAD' ORDER BY idkomponen";
				$res=fetchdata($str);
				foreach($res as $bar){				
					$tab.="<th align=center>".$nmcomp[$bar['idkomponen']]."</th>";
					
					$compdt[$bar['idkomponen']]=$bar['idkomponen'];
				}
				
				$tab.="<th align=center></th>
						<th align=center></th>
			</tr>
		</thead><tbody>";
		
		$str="select distinct kodeorg, periode from ".$dbname.".sdm_5periodegaji where substr(kodeorg,1,4) in (".getOrgDetail(2).") and sudahproses='1'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$closing[$bar['kodeorg']][$bar['periode']]=$bar['periode'];
		}
		
		$data=array();
		$where1=$where2="";
		if($param['periode']!=''){
			$where .= " and periodegaji='".$param['periode']."'";
		}
		if($param['kodeorg']!=''){
			$where1.= " and substr(b.lokasitugas,1,4)='".$param['kodeorg']."'";
			$where2.= " and substr(kodeorg,1,4)='".$param['kodeorg']."'";
		}
		
		//$lmt="limit " . $offset . "," . $limit . "";
		$str="select b.lokasitugas, periodegaji, sum(jumlah) as jumlah, b.tipekaryawan,idkomponen from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.namakaryawan is null and a.sumber='UPLOAD' and substr(b.lokasitugas,1,4) in (".getOrgDetail(2).") ".$where." ".$where1." group by lokasitugas, periodegaji, b.tipekaryawan, a.idkomponen order by periodegaji desc ".$lmt."";
		$res=fetchdata($str);
		foreach($res as $val){
			$data[$val['lokasitugas']][$val['periodegaji']][$val['tipekaryawan']]=$val['tipekaryawan'];
			$jlh[$val['lokasitugas']][$val['periodegaji']][$val['tipekaryawan']][$val['idkomponen']]=$val['jumlah'];
		}
		
		$str="select kodeorg, periodegaji, sum(jumlah) as jumlah, tipekaryawan,idkomponen from ".$dbname.".sdm_gaji_vw where namakaryawan is not null and sumber='UPLOAD' and substr(kodeorg,1,4) in (".getOrgDetail(2).") ".$where." ".$where2." group by kodeorg, periodegaji, tipekaryawan, idkomponen order by periodegaji desc ".$lmt."";
		$res=fetchdata($str);
		foreach($res as $val){
			$data[$val['kodeorg']][$val['periodegaji']][$val['tipekaryawan']]=$val['tipekaryawan'];
			$jlh[$val['kodeorg']][$val['periodegaji']][$val['tipekaryawan']][$val['idkomponen']]=$val['jumlah'];
		}
		
		if(count($data) > 0){
			$rowspan="";
			foreach($data as $kodeorg => $v1){
				foreach($v1 as $periode => $v2){
					foreach($v2 as $tipekary){
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td ".$rowspan." style='text-align:center;'>".$no."</td>";
						$tab.="<td ".$rowspan." style='text-align:left;' nowrap>".getNamaOrg($kodeorg)."</td>";
						$tab.="<td ".$rowspan." style='text-align:center;'>".$periode."</td>";
						$tab.="<td ".$rowspan." style='text-align:left;'>".getNamaTipeKary($tipekary)."</td>";
						foreach($compdt as $idcomp){				
							$tab.="<td ".$rowspan." style='text-align:right;'>".hidezerodecimal($jlh[$kodeorg][$periode][$tipekary][$idcomp])."</td>";
						}
						if($closing[$kodeorg][$periode]!=''){
							$tab.="<td ".$rowspan." align=center width=25px></td>";
						}else{							
							$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$kodeorg."','".$periode."','".$tipekary."');\" title='Delete'></td>";
						}
						
						$tab.="<td ".$rowspan." align=center width=25px><img class=zImgBtn src=images/skyblue/zoom.png onclick=\"preview('".$kodeorg."','".$periode."','".$tipekary."');\" title='Preview'></td>";
						
						$tab.="</tr>";
						
					}					
				}
			}
		}else{
			//$tab.="<tr class='rowcontent'><td colspan=16 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		$tab.="</tbody></table>";
		
		if($param['jenis']=='excel'){
			$tab.="</tbody></table>";
			$nop = "bgt_prd_".$param['tahun'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;
		}
	break;
	case'formupload':
		if($param['periode']==''){
			exit("Warning : Periode wajib diisi.");
		}
		if($param['kodeorg']==''){
			//exit("Warning : Kode organisasi wajib diisi.");
		}
		if($param['tipekary']==''){
			exit("Warning : Tipe Karyawan wajib diisi.");
		}
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=formuploadbpjs.csv");
		
		$where = $wh = "";
		if($param['kodeorg']!=''){
			$where.=" and lokasitugas = '".$param['kodeorg']."'";
		}
		if($param['tipekary']!=''){
			$where.=" and tipekaryawan = '".$param['tipekary']."'";
		}
		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".tglakhir($param['periode']."-01")."')";
		
		$tab.="karyawanid,nikowl,nikabs,namakaryawan,nomorbpjstk,nomorbpjskes,nomorbpjspen,";
		$str="select * from ".$dbname.".sdm_ho_component where 1=1 and (`name` LIKE '%BPJS%' or name like '%pph%') ORDER BY `plus`, `id`";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tab.=$bar['id']."#".$bar['name'].",";
		}
		$tab.="\n";
		
		/* $str="select * from ".$dbname.".datakaryawan_hist where 1=1 ".$where." and periodegaji='".$param['periode']."' and version_type = 'B' order by namakaryawan asc";
		$res=fetchdata($str);
		if(count($res)==0){			
			$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where." order by namakaryawan asc";
			$res=fetchdata($str);
		}
		 */
		$str="select * from ".$dbname.".datakaryawan where 1=1 ".$where." order by namakaryawan asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$tab.=$bar['karyawanid'].",".$bar['nik'].",".$bar['namakaryawan2'].",".$bar['namakaryawan'].",".$bar['jms'].",".$bar['bpjs'].",".$bar['pensiun']."\n";
		}
		
		echo $tab;
	break;
	case'fileSelected':
		if($param['periode']==''){
			exit("Warning : Periode wajib diisi.");
		}
		if($param['kodeorg']==''){
			//exit("Warning : Kode organisasi wajib diisi.");
		}
		if($param['tipekary']==''){
			//exit("Warning : Tipe Karyawan wajib diisi.");
		}
			
		$data = $_POST;
		
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];		
			
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				
				$range = range('G','Z');
				
				foreach ($sheets as $noitem => $sheet){
					if(trim($sheet['A'])!=''){
						$lokasitugaskarya = getHistKary(addZero($sheet['A'],10),$param['periode'],'lokasitugas');
						if($lokasitugaskarya==''){
							$lokasitugaskarya = getKary(addZero($sheet['A'],10),'lokasitugas');
						}
						
						$nikall[addZero($sheet['A'],10)]=addZero($sheet['A'],10);
						$kodeorgall[$lokasitugaskarya] = $lokasitugaskarya;
					}
				}
				
				$dataada=array();
				if(!empty($nikall)){					
					$str = "select * from ".$dbname.".sdm_gaji where periodegaji='".$param['periode']."' and karyawanid in ('".implode("','",$nikall)."') and jumlah>'0'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$dataada[$bar['karyawanid']][$bar['idkomponen']]+=$bar['jumlah'];
					}
					
					$str = "select distinct kodeorg from ".$dbname.".sdm_5periodegaji where periode='".$param['periode']."' and kodeorg  in ('".implode("','",$kodeorgall)."') and sudahproses = '1'";
					$res = fetchdata($str);
					if(count($res)>0){
						$close="Periode penggajian untuk unit dibawah ini telah ditutup :<br>";
						foreach($res as $bar){
							$close.=getNamaOrg($bar['kodeorg'])."<br>";
						}
						echo $close;
						exit("Warningcode");
					}
				}
				
				// echo"<pre>";
				// print_r($range);
				// exit("error");
				$arritem=array();
				foreach ($sheets as $noitem => $sheet){
					if($noitem==1 and $sheet['A']>'0'){
						$str="select * from ".$dbname.".sdm_ho_component ORDER BY `plus`, `id`";
						$res=fetchdata($str);
						foreach($res as $bar){
							$kodebpjs[$bar['id']]=$bar['id'];
							$namabpjs[$bar['id']]=$bar['name'];
						}
						
						
						if($param['jenis']=='simpan'){
							foreach($range as $idrange => $nrange){
								$code = explode("#",$sheet[$nrange])[0];
								if($kodebpjs[$code]!=''){
									$listbpjs[$kodebpjs[$code]]=$nrange;
								}
							}
						}else{
							$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0 >
								<thead>
									<tr class=rowheader style=height:25px>
										<th align=center width=30px>No.</th>
										<th align=center>".$_SESSION['lang']['karyawanid']."</th>
										<th align=center>".$_SESSION['lang']['nik']."</th>
										<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
										<th align=center>".$_SESSION['lang']['nama']."</th>
										<th align=center>No BPJS TK</th>
										<th align=center>No BPJS KES</th>
										<th align=center>No BPJS PEN</th>";
										foreach($range as $idrange => $nrange){
											$code = explode("#",$sheet[$nrange])[0];
											if($namabpjs[$code]!=''){
												$tab.="<th align=center>".$namabpjs[$code]."</th>";
												$rangedt[$nrange]=$nrange;
												$listcode[$nrange]=$code;
											}
										}
							$tab.="</tr>
							</thead>";
						}
					}
					
					
					
					if($noitem>1 and $sheet['A']>'0'){
						if($param['jenis']=='simpan'){
							try {
							$owlPDO->beginTransaction();
								foreach($listbpjs as $komponengaji => $col){
									$lokasitugaskarya = getHistKary(addZero($sheet['A'],10),$param['periode'],'lokasitugas');
									if($lokasitugaskarya==''){
										$lokasitugaskarya = getKary(addZero($sheet['A'],10),'lokasitugas');
									}
									$param['kodeorg'] = $lokasitugaskarya;
									
									$str = "select * from ".$dbname.".sdm_gaji where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and karyawanid ='".addZero($sheet['A'],10)."' and idkomponen='".$komponengaji."'";
									$res = fetchdata($str);
									if(count($res)>0){
										$str = "delete from ".$dbname.".sdm_gaji where kodeorg='".$param['kodeorg']."' and periodegaji='".$param['periode']."' and karyawanid ='".addZero($sheet['A'],10)."' and idkomponen='".$komponengaji."'";
										$owlPDO->exec($str);
									}
									if($sheet[$col]==''){$sheet[$col]=0;}
									
									$data = array(
										'kodeorg'    => $param['kodeorg'],
										'periodegaji'=> $param['periode'],
										'karyawanid' => addZero($sheet['A'],10),
										'idkomponen' => $komponengaji,
										'jumlah'     => ($sheet[$col]),
										'sumber'     => 'UPLOAD'
									);									
									$str = insertQuery($dbname,'sdm_gaji',$data,array_keys($data));
									// exit("error".$str);
									if($sheet[$col]!=0){
										$owlPDO->exec($str);
									}
								}
							$owlPDO->commit();
							} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
						}else{
							$color="";
							if(getKary($sheet['A'],'lokasitugas')!=$param['kodeorg']){
								#$color.="style=background-color:red; title='Kode organisasi tidak sesuai.'";
							}
							if(getKary($sheet['A'],'tipekaryawan')!=$param['tipekary']){
								#$color.="style=background-color:red; title='Tipe karyawan tidak sesuai.'";
							}
							$lokasitugaskarya = getHistKary(addZero($sheet['A'],10),$param['periode'],'lokasitugas');
							if($lokasitugaskarya==''){
								$lokasitugaskarya = getKary(addZero($sheet['A'],10),'lokasitugas');
							}
							$param['kodeorg'] = $lokasitugaskarya;
							$no++;
							$tab.="<tr class=rowcontent ".$color.">";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center>".$sheet['A']."</td>";
							$tab.="<td align=left>".$sheet['B']."</td>";
							$tab.="<td align=left>".$param['kodeorg']."</td>";
							$tab.="<td align=left>".$sheet['D']."</td>";
							$tab.="<td align=left>".$sheet['E']."</td>";
							$tab.="<td align=left>".$sheet['F']."</td>";
							$tab.="<td align=left>".$sheet['G']."</td>";
							foreach($rangedt as $rngdt){
								$sudahadadt="";
								if($dataada[addZero($sheet['A'],10)][$listcode[$rngdt]]>0 and $sheet[$rngdt]>0){
									$sudahadadt="style=color:red; title=\"Data sudah ada sebesar Rp. ".number_format($dataada[addZero($sheet['A'],10)][$listcode[$rngdt]])." jika kolom ini tidak dikosongkan maka data lama akan di replace.\"";
								}
								$tab.="<td align=right ".$sudahadadt.">".$sheet[$rngdt]."</td>";
							}
							$tab.="</tr>";
							
							
						}
					}
				}
				if($param['jenis']!='simpan'){					
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=".(count($rangedt)+(8))." align=center><button id=btnsubmit class=mybutton onclick=\"fileSelected('simpan')\">SaveAll</button></td>";
					$tab.="</tr></table>";
				}
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
	case'preview':
		$tab.="<table class='sortable' cellspacing=1 cellpadding=5 border=0 >
			<thead>
				<tr class=rowheader style=height:25px>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['nik']."</th>
					<th align=center>".$_SESSION['lang']['nik']." ABS</th>
					<th align=center>".$_SESSION['lang']['nama']."</th>
					";
					$str="select distinct idkomponen from ".$dbname.".sdm_gaji where sumber='UPLOAD' ORDER BY idkomponen";
					$res=fetchdata($str);
					foreach($res as $bar){				
						$tab.="<th align=center>".$nmcomp[$bar['idkomponen']]."</th>";
					}
		$tab.="</tr>
		</thead>";
		
		
		$str="select * from ".$dbname.".sdm_gaji_vw where namakaryawan is not null and sumber='UPLOAD' and substr(kodeorg,1,4) = '".$param['kodeorg']."' and periodegaji='".$param['periode']."' and tipekaryawan='".$param['tipekary']."' order by namakaryawan asc";
		$res=fetchdata($str);
		if(count($res)==0){
			$str="select * from ".$dbname.".sdm_gaji_vw a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.namakaryawan is null and a.sumber='UPLOAD' and substr(b.lokasitugas,1,4) = '".$param['kodeorg']."' and a.periodegaji='".$param['periode']."' and b.tipekaryawan='".$param['tipekary']."' order by b.namakaryawan asc ";
			$res=fetchdata($str);
		}
		foreach($res as $val){
			$data[$val['karyawanid']]=$val['karyawanid'];
			$nilai[$val['karyawanid']][$val['idkomponen']]+=$val['jumlah'];
		}
		
		$strx="select distinct idkomponen from ".$dbname.".sdm_gaji where sumber='UPLOAD' ORDER BY idkomponen";
		$resx=fetchdata($strx);
		
		foreach($data as $karid){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".getKary($karid,'nik')."</td>";
			$tab.="<td align=left>".getKary($karid,'namakaryawan2')."</td>";
			$tab.="<td align=left>".getKary($karid,'namakaryawan')."</td>";
			foreach($resx as $bar){				
				$tab.="<td align=right>".hidezerodecimal($nilai[$karid][$bar['idkomponen']])."</td>";
			}
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		
		echo $tab;
	break;
}

?>	
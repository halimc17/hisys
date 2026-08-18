<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$method=checkPostGet('method','');
$thnbudget=checkPostGet('thnbudget','');
$kdpks=checkPostGet('kdpks','');
$ffacpo=checkPostGet('ffacpo','');
$ffaker=checkPostGet('ffaker','');
$kadarair=checkPostGet('kadarair','');
$loses=checkPostGet('loses','');
$thnbudgetHeader=checkPostGet('thnbudgetHeader','');


$optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$arrkriteria=
array("cpoffa"=>"FFA","cpokadarair"=>"Kadar Air","cpokadarkotoran"=>"Kadar Kotoran","cpofiberpress"=>"Fiber Press",
	"cponutpress"=>"Nut Press","cpoemptybunch"=>"Empty Bunch","cpousb"=>"USB","cposoliddecanter"=>"Solid Decanter",
	"cpoheavyphase"=>"Heavy Phase","cpofinaleffluent"=>"Final Effluent","cposterilizecondensat"=>"Sterilizer Condensat",
	"pkffa"=>"FFA","pkkadarair"=>"Kadar Air","pkkadarkotoran"=>"Kadar Kotoran","pkbroken"=>"Broken","pkusb"=>"USB",
	"pkfibercyclone"=>"Fiber Cyclone","pkltds1"=>"LTDS 1","pkltds2"=>"LTDS 2","pkclaybath"=>"Wet Shell/Claybath");

switch($method){
	case'loadData':
		$tab="";
		$totRowDlm=count($arrBln);
		$ql2="select count(*) as jmlhrow from ".$dbname.".bgt_pks_kualitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		
		if($jlhbrs<=0){
			$tab.= $_SESSION['lang']['errdatanotexist'];
		}else{
			$tab.="<table cellpadding=3 cellspacing=1 border=0 class=sortable>
				<thead style='text-align:center'>
				<tr>
					<td colspan=2 rowspan=2>Aksi</td>
					<td rowspan=2>".$_SESSION['lang']['budgetyear']."</td>
					<td rowspan=2>".$_SESSION['lang']['unit']."</td>
					<td rowspan=2 style='width:10px'>&nbsp</td>
					<td colspan=4>CPO</td>
					<td rowspan=2 style='width:10px'>&nbsp</td>
					<td colspan=5>PK</td>
				</tr>
				<tr>
					<td>FFA</td>
					<td>Kadar Air</td>
					<td>Kadar Kotoran</td>
					<td>Losses</td>
					
					<td>FFA</td>
					<td>Kadar Air</td>
					<td>Kadar Kotoran</td>
					<td>Broken</td>
					<td>Losses</td>
				</tr>
				</thead>
				
				<tbody>";
				
				$str = "select * from ".$dbname.".bgt_pks_kualitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."' order by tahunbudget desc";
				$res=fetchdata($str);
				foreach($res as $val){
					$tab.="<tr class=rowcontent>";
					
					## Get Budget Detail
					$strx="select * from ".$dbname.".bgt_pks_kualitasdt where kodeorg='".$val['kodeorg']."' and tahunbudget='".$val['tahunbudget']."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						if(substr($valx['kriteria'],0,3)=='cpo'){
							if($valx['kriteria']=='cpoffa'){
								$cpoffa=$valx['total'];								
							}else if($valx['kriteria']=='cpokadarair'){
								$cpokadarair=$valx['total'];								
							}else if($valx['kriteria']=='cpokadarkotoran'){
								$cpokadarkotoran=$valx['total'];								
							}else{
								$cpoloses+=$valx['total'];
								if($valx['kriteria']=='cpofiberpress'){$cpofiberpress=$valx['total'];};
								if($valx['kriteria']=='cponutpress'){$cponutpress=$valx['total'];};
								if($valx['kriteria']=='cpoemptybunch'){$cpoemptybunch=$valx['total'];};
								if($valx['kriteria']=='cpousb'){$cpousb=$valx['total'];};
								if($valx['kriteria']=='cposoliddecanter'){$cposoliddecanter=$valx['total'];};
								if($valx['kriteria']=='cpoheavyphase'){$cpoheavyphase=$valx['total'];};
								if($valx['kriteria']=='cpofinaleffluent'){$cpofinaleffluent=$valx['total'];};
								if($valx['kriteria']=='cposterilizecondensat'){$cposterilizecondensat=$valx['total'];};
							}
						}else{
							if($valx['kriteria']=='pkffa'){
								$pkffa=$valx['total'];								
							}else if($valx['kriteria']=='pkkadarair'){
								$pkkadarair=$valx['total'];								
							}else if($valx['kriteria']=='pkkadarkotoran'){
								$pkkadarkotoran=$valx['total'];								
							}else if($valx['kriteria']=='pkbroken'){
								$pkbroken=$valx['total'];								
							}else{
								$pkloses+=$valx['total'];								
								if($valx['kriteria']=='pkusb'){$pkusb=$valx['total'];};
								if($valx['kriteria']=='pkfibercyclone'){$pkfibercyclone=$valx['total'];};
								if($valx['kriteria']=='pkltds1'){$pkltds1=$valx['total'];};
								if($valx['kriteria']=='pkltds2'){$pkltds2=$valx['total'];};
								if($valx['kriteria']=='pkclaybath'){$pkclaybath=$valx['total'];};			
							}
						}
					}
					
					if($val['tutup']=='0'){
						$tab.="<td align=center>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillfield('".$val['tahunbudget']."','".$val['kodeorg']."','".$cpoffa."','".$cpokadarair."','".$cpokadarkotoran."','".$cpofiberpress."','".$cponutpress."','".$cpoemptybunch."','".$cpousb."','".$cposoliddecanter."','".$cpoheavyphase."','".$cpofinaleffluent."','".$cposterilizecondensat."','".$pkffa."','".$pkkadarair."','".$pkkadarkotoran."','".$pkbroken."','".$pkusb."','".$pkfibercyclone."','".$pkltds1."','".$pkltds2."','".$pkclaybath."','".$cpoloses."','".$pkloses."');\">
						</td>
						<td align=center>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$val['tahunbudget']."','".$val['kodeorg']."');\">
						</td>";
					}else{
						$tab.="<td align=center colspan=2></td>";
					}

					$tab.="<td align=center>".$val['tahunbudget']."</td>";
					$tab.="<td align=center>".$val['kodeorg']."</td>";
					
					$tab.="<td></td>";
					$klik=" title='View Detail' onclick=\"viewdetail('".$val['kodeorg']."','".$val['tahunbudget']."','html');\"";
					$tab.="<td style='text-align:right' ".$klik.">".$cpoffa."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$cpokadarair."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$cpokadarkotoran."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$cpoloses."</td>";
					
					$tab.="<td></td>";
					
					$tab.="<td style='text-align:right' ".$klik.">".$pkffa."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$pkkadarair."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$pkkadarkotoran."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$pkbroken."</td>";
					$tab.="<td style='text-align:right' ".$klik.">".$pkloses."</td>";

					$tab.="</tr>";
				}				
				
				$tab.="</tbody>
			</table>";
			// $tab.="<thead><tr class=rowheader>";
			// $tab.="<td align=center>".$_SESSION['lang']['budgetyear']."</td>";
			// $tab.="<td align=center>".$_SESSION['lang']['unit']."</td>"; 
			// $tab.="<td align=center>FFA(CPO)</td>";
			// $tab.="<td align=center>FFA(Kernel)</td>";
			// $tab.="<td align=center>KadarAir</td>";
			// $tab.="<td align=center>Loses</td>";
			
			// $tab.="</tr></thead><tbody>";	

			// $str = "select * from ".$dbname.".bgt_pks_kualitas where kodeorg = '".$_SESSION['empl']['lokasitugas']."' order by tahunbudget desc";
			// $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			// $qry->setFetchMode(PDO::FETCH_ASSOC);			
			// $no=0;
			// while($res = $qry->fetch()){
				// $no+=1;
				// $title = "title='Click to detail' style='cursor:pointer' onclick=\"detail('".$no."')\"";
				// $tab.="<tr class=rowcontent>";
				// if($res['tutup']=='1'){
					// $tab.="<td align=center colspan=2></td>";
				// }else{
					// $tab.="<td align=center>
						// <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillfield('".$res['tahunbudget']."','".$res['kodeorg']."','".$res['totalffacpo']."','".$res['totalffaker']."','".$res['totalkadarair']."','".$res['totalloses']."');\">
					// </td><td align=center>
						// <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$res['tahunbudget']."','".$res['kodeorg']."');\">
					// </td>";
				// }
				
				// $tab.="<td align=right ".$title.">".$res['tahunbudget']."</td>";
				// $tab.="<td ".$title.">".$res['kodeorg']."</td>";
				// $tab.="<td align=right ".$title.">".number_format($res['totalffacpo'],2)."</td>";
				// $tab.="<td align=right ".$title.">".number_format($res['totalffaker'],2)."</td>";
				// $tab.="<td align=right ".$title.">".number_format($res['totalkadarair'],2)."</td>";
				// $tab.="<td align=right ".$title.">".number_format($res['totalloses'],2)."</td>";
				// $tab.="</tr>";
				// foreach($arrBln as $key=>$val){
					// if(strlen($key)=='1'){
						// $b="0".$key;
					// }else{
						// $b=$key;
					// }
					// $tab.="<tr id='".$no."".$key."' class=rowcontent style='display:none'>";
					// $tab.="<td colspan=4 style='text-align:right'>".$val."</td>";
					// $tab.="<td style='text-align:right; min-width:75px;'>".number_format($res['ffacpo'.$b],2)."</td>";
					// $tab.="<td style='text-align:right; min-width:75px;'>".number_format($res['ffaker'.$b],2)."</td>";
					// $tab.="<td style='text-align:right; min-width:75px;'>".number_format($res['kadarair'.$b],2)."</td>";
					// $tab.="<td style='text-align:right; min-width:75px;'>".number_format($res['loses'.$b],2)."</td>";
					// $tab.="</tr>";
				// }
			// }
			// $tab.="</tbody></table>";
		}
		
		echo $tab;
		break;	
	
	case'insert':
		
		if(strlen($thnbudget)!=4){
			exit("Gagal : Tahun budget harus diisi");
		}
		
		if($kdpks==''){
			exit("Gagal : Unit harus dipilih.");
		}
		
		$str = "select * from ".$dbname.".bgt_pks_kualitas where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			exit("Gagal : Tahun dan Unit budget sudah pernah terdaftar");			
		}		
		
		##CPO
		$cpoffa=checkPostGet('cpoffa','');
		$cpokadarair=checkPostGet('cpokadarair','');
		$cpokadarkotoran=checkPostGet('cpokadarkotoran','');
		$cpofiberpress=checkPostGet('cpofiberpress','');
		$cponutpress=checkPostGet('cponutpress','');
		$cpoemptybunch=checkPostGet('cpoemptybunch','');
		$cpousb=checkPostGet('cpousb','');
		$cposoliddecanter=checkPostGet('cposoliddecanter','');
		$cpoheavyphase=checkPostGet('cpoheavyphase','');
		$cpofinaleffluent=checkPostGet('cpofinaleffluent','');
		$cposterilizecondensat=checkPostGet('cposterilizecondensat','');
		
		##PK
		$pkffa=checkPostGet('pkffa','');
		$pkkadarair=checkPostGet('pkkadarair','');
		$pkkadarkotoran=checkPostGet('pkkadarkotoran','');
		$pkbroken=checkPostGet('pkbroken','');
		$pkusb=checkPostGet('pkusb','');
		$pkfibercyclone=checkPostGet('pkfibercyclone','');
		$pkltds1=checkPostGet('pkltds1','');
		$pkltds2=checkPostGet('pkltds2','');
		$pkclaybath=checkPostGet('pkclaybath','');
		
		##CPO
		if($cpoffa==''){$cpoffa=0;}
		if($cpokadarair==''){$cpokadarair=0;}
		if($cpokadarkotoran==''){$cpokadarkotoran=0;}
		if($cpofiberpress==''){$cpofiberpress=0;}
		if($cponutpress==''){$cponutpress=0;}
		if($cpoemptybunch==''){$cpoemptybunch=0;}
		if($cpousb==''){$cpousb=0;}
		if($cposoliddecanter==''){$cposoliddecanter=0;}
		if($cpoheavyphase==''){$cpoheavyphase=0;}
		if($cpofinaleffluent==''){$cpofinaleffluent=0;}
		if($cposterilizecondensat==''){$cposterilizecondensat=0;}
		$arrcpo = array('cpoffa'=>$cpoffa,'cpokadarair'=>$cpokadarair,'cpokadarkotoran'=>$cpokadarkotoran,'cpofiberpress'=>$cpofiberpress,'cponutpress'=>$cponutpress,'cpoemptybunch'=>$cpoemptybunch,'cpousb'=>$cpousb,'cposoliddecanter'=>$cposoliddecanter,'cpoheavyphase'=>$cpoheavyphase,'cpofinaleffluent'=>$cpofinaleffluent,'cposterilizecondensat'=>$cposterilizecondensat);
		
		##PK
		if($pkffa==''){$pkffa=0;}
		if($pkkadarair==''){$pkkadarair=0;}
		if($pkkadarkotoran==''){$pkkadarkotoran=0;}
		if($pkbroken==''){$pkbroken=0;}
		if($pkusb==''){$pkusb=0;}
		if($pkfibercyclone==''){$pkfibercyclone=0;}
		if($pkltds1==''){$pkltds1=0;}
		if($pkltds2==''){$pkltds2=0;}
		if($pkclaybath==''){$pkclaybath=0;}
		$arrpk = array('pkffa'=>$pkffa,'pkkadarair'=>$pkkadarair,'pkkadarkotoran'=>$pkkadarkotoran,'pkbroken'=>$pkbroken,'pkusb'=>$pkusb,'pkfibercyclone'=>$pkfibercyclone,'pkltds1'=>$pkltds1,'pkltds2'=>$pkltds2,'pkclaybath'=>$pkclaybath);
		
		try{
			$owlPDO->beginTransaction();
			
			## Insert Header
			$str="insert into ".$dbname.".bgt_pks_kualitas (kodeorg,tahunbudget,tutup) values ('".$kdpks."','".$thnbudget."','0')";
			$owlPDO->exec($str);
			
			## Insert Detail
			## CPO
			foreach($arrcpo as $key=>$val){
				$str="insert into ".$dbname.".bgt_pks_kualitasdt (kodeorg,tahunbudget,komoditi,kriteria";
				for($i=1;$i<=12;$i++){
					$str.=",bulan".addZero($i,2)."";
				}
				$str.=",total) values ('".$kdpks."','".$thnbudget."','CPO','".$key."'";
				for($i=1;$i<=12;$i++){
					$valdt = $val;
					if($val!=0){
						$valdt = ($val/12);
					}
					$str.=",'".$valdt."'";
				}
				$str.=",'".$val."')";
			#exit("error".$str);
				$owlPDO->exec($str);
			}
			
			foreach($arrpk as $key=>$val){
				$str="insert into ".$dbname.".bgt_pks_kualitasdt (kodeorg,tahunbudget,komoditi,kriteria";
				for($i=1;$i<=12;$i++){
					$str.=",bulan".addZero($i,2)."";
				}
				$str.=",total) values ('".$kdpks."','".$thnbudget."','PK','".$key."'";
				for($i=1;$i<=12;$i++){
					$valdt = $val;
					if($val!=0){
						$valdt = ($val/12);
					}
					$str.=",'".$valdt."'";
				}
				$str.=",'".$val."')";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo $e."##<br>";
		}
		
		// echo"<pre>";
		// print_r($_POST);
		
		// echo"</pre>";
		// exit("error");
		
		break;
		
	case 'delete':
		try{
			$owlPDO->beginTransaction();
			
			## Delete Header
			$str = "delete from ".$dbname.".bgt_pks_kualitas where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'";
			$owlPDO->exec($str);
			
			## Delete Detail
			$str = "delete from ".$dbname.".bgt_pks_kualitasdt where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo $e."##<br>";
		}
		break;
	
	case 'update':
        ##CPO
		$cpoffa=checkPostGet('cpoffa','');
		$cpokadarair=checkPostGet('cpokadarair','');
		$cpokadarkotoran=checkPostGet('cpokadarkotoran','');
		$cpofiberpress=checkPostGet('cpofiberpress','');
		$cponutpress=checkPostGet('cponutpress','');
		$cpoemptybunch=checkPostGet('cpoemptybunch','');
		$cpousb=checkPostGet('cpousb','');
		$cposoliddecanter=checkPostGet('cposoliddecanter','');
		$cpoheavyphase=checkPostGet('cpoheavyphase','');
		$cpofinaleffluent=checkPostGet('cpofinaleffluent','');
		$cposterilizecondensat=checkPostGet('cposterilizecondensat','');
		
		##PK
		$pkffa=checkPostGet('pkffa','');
		$pkkadarair=checkPostGet('pkkadarair','');
		$pkkadarkotoran=checkPostGet('pkkadarkotoran','');
		$pkbroken=checkPostGet('pkbroken','');
		$pkusb=checkPostGet('pkusb','');
		$pkfibercyclone=checkPostGet('pkfibercyclone','');
		$pkltds1=checkPostGet('pkltds1','');
		$pkltds2=checkPostGet('pkltds2','');
		$pkclaybath=checkPostGet('pkclaybath','');
		
		##CPO
		if($cpoffa==''){$cpoffa=0;}
		if($cpokadarair==''){$cpokadarair=0;}
		if($cpokadarkotoran==''){$cpokadarkotoran=0;}
		if($cpofiberpress==''){$cpofiberpress=0;}
		if($cponutpress==''){$cponutpress=0;}
		if($cpoemptybunch==''){$cpoemptybunch=0;}
		if($cpousb==''){$cpousb=0;}
		if($cposoliddecanter==''){$cposoliddecanter=0;}
		if($cpoheavyphase==''){$cpoheavyphase=0;}
		if($cpofinaleffluent==''){$cpofinaleffluent=0;}
		if($cposterilizecondensat==''){$cposterilizecondensat=0;}
		$arrcpo = array('cpoffa'=>$cpoffa,'cpokadarair'=>$cpokadarair,'cpokadarkotoran'=>$cpokadarkotoran,'cpofiberpress'=>$cpofiberpress,'cponutpress'=>$cponutpress,'cpoemptybunch'=>$cpoemptybunch,'cpousb'=>$cpousb,'cposoliddecanter'=>$cposoliddecanter,'cpoheavyphase'=>$cpoheavyphase,'cpofinaleffluent'=>$cpofinaleffluent,'cposterilizecondensat'=>$cposterilizecondensat);
		
		##PK
		if($pkffa==''){$pkffa=0;}
		if($pkkadarair==''){$pkkadarair=0;}
		if($pkkadarkotoran==''){$pkkadarkotoran=0;}
		if($pkbroken==''){$pkbroken=0;}
		if($pkusb==''){$pkusb=0;}
		if($pkfibercyclone==''){$pkfibercyclone=0;}
		if($pkltds1==''){$pkltds1=0;}
		if($pkltds2==''){$pkltds2=0;}
		if($pkclaybath==''){$pkclaybath=0;}
		$arrpk = array('pkffa'=>$pkffa,'pkkadarair'=>$pkkadarair,'pkkadarkotoran'=>$pkkadarkotoran,'pkbroken'=>$pkbroken,'pkusb'=>$pkusb,'pkfibercyclone'=>$pkfibercyclone,'pkltds1'=>$pkltds1,'pkltds2'=>$pkltds2,'pkclaybath'=>$pkclaybath);
		
		try{
			$owlPDO->beginTransaction();
			
			## Delete Header
			$str = "delete from ".$dbname.".bgt_pks_kualitas where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'";
			$owlPDO->exec($str);
			
			## Delete Detail
			$str = "delete from ".$dbname.".bgt_pks_kualitasdt where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'";
			$owlPDO->exec($str);
			
			## Insert Header
			$str="insert into ".$dbname.".bgt_pks_kualitas (kodeorg,tahunbudget,tutup) values ('".$kdpks."','".$thnbudget."','0')";
			$owlPDO->exec($str);
			
			## Insert Detail
			## CPO
			foreach($arrcpo as $key=>$val){
				$str="insert into ".$dbname.".bgt_pks_kualitasdt (kodeorg,tahunbudget,komoditi,kriteria";
				for($i=1;$i<=12;$i++){
					$str.=",bulan".addZero($i,2)."";
				}
				$str.=",total) values ('".$kdpks."','".$thnbudget."','CPO','".$key."'";
				for($i=1;$i<=12;$i++){
					$valdt = $val;
					if($val!=0){
						$valdt = ($val/12);
					}
					$str.=",'".$valdt."'";
				}
				$str.=",'".$val."')";
				$owlPDO->exec($str);
			}
			
			foreach($arrpk as $key=>$val){
				$str="insert into ".$dbname.".bgt_pks_kualitasdt (kodeorg,tahunbudget,komoditi,kriteria";
				for($i=1;$i<=12;$i++){
					$str.=",bulan".addZero($i,2)."";
				}
				$str.=",total) values ('".$kdpks."','".$thnbudget."','PK','".$key."'";
				for($i=1;$i<=12;$i++){
					$valdt = $val;
					if($val!=0){
						$valdt = ($val/12);
					}
					$str.=",'".$valdt."'";
				}
				$str.=",'".$val."')";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo $e."##<br>";
		}
		break;
		
	case'closepks':
		$str = "select distinct tutup from ".$dbname.".bgt_pks_kualitas where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."' and tutup=1 ";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($qry);		
		if($row!=1){
			$str = "update ".$dbname.".bgt_pks_kualitas set tutup=1 where tahunbudget='".$thnbudget."' and kodeorg='".$kdpks."'  ";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}else{
			exit("Error: Data has been closed");
		}
	break;

	case 'viewdetail':

		$border="border='1'";
		if ($proses=='html') {
			$tab="<img class=\"zImgBtn\" src=\"images/skyblue/excel.jpg\" style=\"cursor:pointer;float:left;\" onclick=\"printFile('".$kdpks."','".$thnbudget."','bgt_slave_kualitas_pks.php',event)\" title=\"Print Excel\"><br>";
			$border="border='0'";
		}
        $tab.="<table cellpadding=3 cellspacing=1 ".$border."  class=sortable>
				<thead style='text-align:center'>
				<tr>
					<td align=left colspan='14'>".$_SESSION['lang']['budgetyear']." ".$thnbudget."</td>
				</tr>
				<tr>
					<td align=left colspan='14'>".$_SESSION['lang']['unit']." ".$kdpks."</td>
				</tr>
				<tr>
					<td colspan='2'>".$_SESSION['lang']['kriteria']."</td>";

				for ($i=1; $i <=12 ; $i++) { 
					$tab.="<td>".$_SESSION['lang']['bulan']." ".$i."</td>";	
				}	
			
		$tab.="</tr></thead><tbody>
			<tr class=rowcontent>
				<td colspan='14' align='center'>CPO</td>
			</tr>";

        $str="select * from ".$dbname.".bgt_pks_kualitasdt where kodeorg='".$kdpks."' and tahunbudget='".$thnbudget."' and komoditi='CPO' and kriteria in ('cpoffa','cpokadarair','cpokadarkotoran')";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
	        $tab.="<tr class=rowcontent>
	                <td colspan='2'><b>".$arrkriteria[$bar['kriteria']]."</b></td>";
            for ($i=1; $i <=12 ; $i++) { 
            	if ($i<10) {
            		$no="0".$i;
            	}
				$tab.="<td align=right><b>".number_format(($bar['bulan'.$no]*12),3)."</b></td>";	
			}    
	        $tab.="</tr>";
        }

		$cpoloses=0;
        $str="select * from ".$dbname.".bgt_pks_kualitasdt where kodeorg='".$kdpks."' and tahunbudget='".$thnbudget."' and komoditi='CPO' and kriteria not in ('cpoffa','cpokadarair','cpokadarkotoran')";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {

			$cpoloses+=$bar['total'];
	        $tab.="<tr class=rowcontent>
	                <td width:4px></td>
	                <td>".$arrkriteria[$bar['kriteria']]."</td>";
            for ($i=1; $i <=12 ; $i++) { 
            	if ($i<10) {
            		$no="0".$i;
            	}
				$tab.="<td align=right>".number_format(($bar['bulan'.$no]*12),3)."</td>";	
			}    
	        $tab.="</tr>";
        }

		$tab.="<tr class=rowcontent>
				<td colspan='2'><b>Losses</b></td>";
			for ($i=1; $i <=12 ; $i++) { 
	        	if ($i<10) {
	        		$no="0".$i;
	        	}
				$tab.="<td align='right'><b>".$cpoloses."<b></td>";	
			}    
		$tab.="</tr>";

        $tab.="<tr class=rowcontent>
					<td colspan='14' align='center'>PK</td>
				</tr>";

        $str="select * from ".$dbname.".bgt_pks_kualitasdt where kodeorg='".$kdpks."' and tahunbudget='".$thnbudget."' and komoditi='PK' and kriteria in ('pkffa','pkkadarair','pkkadarkotoran','pkbroken')";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {

	        $tab.="<tr class=rowcontent>
	                <td colspan=2><b>".$arrkriteria[$bar['kriteria']]."</b></td>";
            for ($i=1; $i <=12 ; $i++) { 
            	if ($i<10) {
            		$no="0".$i;
            	}
				$tab.="<td align=right><b>".number_format(($bar['bulan'.$no]*12),3)."</b></td>";	
			} 
	        $tab.="</tr>";
        }

		$pkloses=0;
        $str="select * from ".$dbname.".bgt_pks_kualitasdt where kodeorg='".$kdpks."' and tahunbudget='".$thnbudget."' and komoditi='PK' and kriteria not in ('pkffa','pkkadarair','pkkadarkotoran','pkbroken')";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {

			$pkloses+=$bar['total'];

			$tab.="<tr class=rowcontent>
	                <td width:4px></td>
	                <td>".$arrkriteria[$bar['kriteria']]."</td>";
            for ($i=1; $i <=12 ; $i++) { 
            	if ($i<10) {
            		$no="0".$i;
            	}
				$tab.="<td align=right>".number_format(($bar['bulan'.$no]*12),3)."</td>";	
			} 
	        $tab.="</tr>";
        }

		$tab.="<tr class=rowcontent>
				<td colspan='2'><b>Losses</b></td>";
			for ($i=1; $i <=12 ; $i++) { 
	        	if ($i<10) {
	        		$no="0".$i;
	        	}
				$tab.="<td align='right'><b>".$pkloses."</b></td>";	
			}    
		$tab.="</tr>";

        $tab.="</tbody>";
        $tab.="</table></fieldset>";

		if ($proses=='excel') { 
            $tglSkrg = date("Ymd");
            $nop_ = "Detail FFB Quality";
            if (strlen($tab) > 0) {
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $tab)) {
                    echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                    window.location='tempExcel/" . $nop_ . ".xls';
                    </script>";
                }
                fclose($handle);
            }
        }else{
            echo $tab;
        }
    break;

	default:
	 break;
}
?>
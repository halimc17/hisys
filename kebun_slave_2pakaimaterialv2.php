<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

if(isset($_POST['method'])){
	$param = $_POST;	
}
if(isset($_GET['method'])){
	$param = $_GET;
}

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');
$tanggal=tanggalsystemn (checkPostGet('tanggal',''));
$periode=substr(checkPostGet('periode',''),0,4);



//echo $kebun.' '.$divisi.' '.$tanggal;

switch ($param['method']) {
	case 'getperiode':
		$sData="select distinct left(kodeorg,6) as divisi,left(tanggal,7) as periode from ".$dbname.".kebun_perawatan_vw 
		        where unit='".$param['kebun']."' order by left(kodeorg,6) asc ";
		        //exit('error'.$sData);
		$rData=fetchData($sData);
		foreach($rData as $row=>$isiDt){
			$lstAfd[$isiDt['divisi']]=$isiDt['divisi'];
			$lstPeriode[$isiDt['periode']]=$isiDt['periode'];
		}
		// echo"<pre>";
		// print_r($lstAfd);
		// echo"</pre>";
		// exit('warning');
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach ($lstAfd as $key){
			$optNm=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$key."'");
			$optDivisi.="<option value='".$key."'>".$optNm[$key]."</option>";
		}
		array_multisort($lstPeriode,SORT_DESC);
		foreach ($lstPeriode as $key){
			$optPeriode.="<option value='".$key."'>".$key."</option>";
		}
		echo $optDivisi."####".$optPeriode;
	break;
	case'preview':
		if($param['periode']==''){
			exit('warning :'.$_SESSION['lang']['periode']." ".$_SESSION['lang']['kosong']);
		}
		$hari_ini=$param['periode']."-01";
		// Tanggal pertama pada bulan ini
		$tgl_pertama = date('Y-m-01', strtotime($hari_ini));

		// Tanggal terakhir pada bulan ini
		$tgl_terakhir = date('Y-m-t', strtotime($hari_ini));

		$isiDta=explode("-",$tgl_terakhir);
		//exit('warning'.$isiDta[2]);
		#display data
		#$arrTipe=array("0"=>"Daily","1"=>"Summary Of Detail","1"=>"Summary Of Detail","2"=>"SUMMARY CONSUMPTION","3"=>"Annual");
		if($param['tipe']==0){
			$whrGroup=" and left(kodeorg,4)='".$param['kebun']."'";
			if($param['divisi']!=''){
				$whrGroup=" and left(kodeorg,6)='".$param['divisi']."'";
			}
			$sData="select left(a.kodebarang,3) as klmpok,a.kodebarang,b.namabarang,b.satuan,sum(a.kwantitas) as qty,left(a.kodeorg,6) as divisi,
			a.tanggal as tanggal,
			IFNULL(c.satuankonversi,b.satuan) as satuankonversi,IFNULL(c.jumlah,'1') as jumlahkonversi
			from ".$dbname.".kebun_pakai_material_vw a
			left join ".$dbname.".log_5masterbarang b on a.kodebarang = b.kodebarang
			left join ".$dbname.".log_5stkonversi c on a.kodebarang = c.kodebarang
			where left(a.tanggal,7)='".$param['periode']."' ".$whrGroup." 
			group by left(a.kodeorg,6),a.tanggal,a.kodebarang order by a.kodebarang asc";
			$rData=fetchData($sData);
			//exit("ERROR: ".$sData);
			foreach ($rData as $key => $val) {
				if($val['satuankonversi']=="KG"){
					$val['jumlahkonversi']='0.001';
					$val['satuankonversi']="MT";
				}
				$dtBrg[$val['divisi']][$val['kodebarang']]['namabarang'] 					= $val['namabarang'];
				$dtBrg[$val['divisi']][$val['kodebarang']]['satuan'] 						= $val['satuankonversi'];
				$dtBrg[$val['divisi']][$val['kodebarang']][$val['tanggal']]['tanggal'] 		= $val['tanggal'];
				if(empty($dtBrg[$val['divisi']][$val['kodebarang']][$val['tanggal']]['qty'])){
					$dtBrg[$val['divisi']][$val['kodebarang']][$val['tanggal']]['qty'] 		= ($val['qty']*$val['jumlahkonversi']);
				}else{
					$dtBrg[$val['divisi']][$val['kodebarang']][$val['tanggal']]['qty'] 		+= ($val['qty']*$val['jumlahkonversi']);
				}
				# code...
			}
		}
		if($param['tipe']==1){
			$whrGroup=" left(kodeorg,4)='".$param['kebun']."'";
			$sListAfd="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kebun']."' and tipe='AFDELING' order by kodeorganisasi asc";
			$rListAfd=fetchData($sListAfd);
			$sData="select kodebarang,sum(kwantitas) as qty,left(kodeorg,6) as divisi from ".$dbname.".kebun_pakai_material_vw where left(tanggal,7)='".$param['periode']."' 
				         and ".$whrGroup." group by left(kodeorg,6),kodebarang order by kodebarang asc";
				           //exit('error'.$sData);
			$rData=fetchData($sData);
			foreach ($rData as $key => $val) {
				$lstBarang[$val['kodebarang']]=$val['kodebarang'];

				#namabarang
				$sNm="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$val['kodebarang']."'";
				$rNm=fetchData($sNm);
				$brgDt=$rNm[0];
				#konversi
				$sKon="select satuankonversi,jumlah from ".$dbname.".log_5stkonversi where kodebarang='".$val['kodebarang']."'";
				$rKon=fetchData($sKon);
				if($brgDt['satuan']=="KG"){
					$rKon[0]['jumlah']='0.001';
					$rKon[0]['satuankonversi']="MT";
				}
				$nmList[$val['kodebarang']]['namabarang']=$brgDt['namabarang'];
				$nmList[$val['kodebarang']]['satuan']=$brgDt['satuan'];
				$dtDta[$val['kodebarang'].$val['divisi']]['qty']=$val['qty'];
				if(count($rKon)!=0){
					$nmList[$val['kodebarang']]['satuan']=$rKon[0]['satuankonversi'];
					$dtDta[$val['kodebarang'].$val['divisi']]['qty']=($val['qty']*$rKon[0]['jumlah']);
				}
				
				
				
			}
		}

		if($param['tipe']==2){
			$whrGroup=" left(kodeorg,4)='".$param['kebun']."'";
			if($param['divisi']!=''){
				$whrGroup=" left(kodeorg,6)='".$param['divisi']."'";
				$whrdiv=" and kodeorganisasi='".$param['divisi']."'";
			}
			#namabarang
			$sNm="select a.kodebarang,a.namabarang,ifnull(b.satuankonversi,a.satuan) as satuan, ifnull(b.jumlah,'1') as jumlah from ".$dbname.".log_5masterbarang a 
			left join log_5stkonversi b on a.kodebarang=b.kodebarang";
			$rNm=fetchData($sNm);
			$brgDt= array();
			if(count($rNm)>0){
				for($i=0;$i<count($rNm);$i++)
				{
					$brgDt[$rNm[$i]['kodebarang']]=$rNm[$i];
					/* 
					$brgDt[71238503] = array(
							'kodebarang'=> 'isi',
							'namabarang'=> 'isi',
							'satuan'=> 'isi'
						);
					*/
			      }
			}
			$divisi= array();
			$sNm="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='AFDELING' and induk='".$param['kebun']."'".$whrdiv;
			$divisi=fetchData($sNm);
			/*$satuan=array();
			$sKon="select satuankonversi,jumlah from ".$dbname.".log_5stkonversi where kodebarang='".$val['kodebarang']."'";
				$rKon=fetchData($sKon);*/
		
					 

			$sData="select kodebarang as kodebarang ,sum(kwantitas) as qty,left(kodeorg,6) as divisi,left(tanggal,7) as periode,right(left(tanggal,7),2) as bulan 
			from ".$dbname.".kebun_pakai_material_vw where left(tanggal,4)='".$periode."'
			and ".$whrGroup." group by left(kodeorg,6),left(tanggal,7),kodebarang order by kodebarang asc";
			
			$rData=fetchData($sData);
			$dataAll = array();
			for($i=0;$i<count($rData);$i++){
				$data['kodebarang'] = $rData[$i]['kodebarang'];
				$data['qty'] = $rData[$i]['qty'];
				$data['divisi'] = $rData[$i]['divisi'];
				$data['periode'] = $rData[$i]['periode'];
				$data['bulan'] = $rData[$i]['bulan'];
				$namabarang = "";
				$satuan = "";
				$jumlah = 1;
				if(isset($brgDt[$rData[$i]['kodebarang']])){
					$namabarang = $brgDt[$rData[$i]['kodebarang']]['namabarang'];
					if($brgDt[$rData[$i]['kodebarang']]['satuan']=="KG"){
						$brgDt[$rData[$i]['kodebarang']]['jumlah']=$brgDt[$rData[$i]['kodebarang']]['jumlah']*0.001;
						$brgDt[$rData[$i]['kodebarang']]['satuan']="MT";
					}
					$satuan 	= $brgDt[$rData[$i]['kodebarang']]['satuan'];
					$jumlah 	= $brgDt[$rData[$i]['kodebarang']]['jumlah'];
				}

					

				$data['namabarang'] = $namabarang;
				$data['satuan'] 	= $satuan;
				$data['jumlah'] 	= $jumlah;

				/*
				#namabarang
				$sNm="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$val['kodebarang']."'";
				$rNm=fetchData($sNm);
				$brgDt=$rNm[0];
				#konversi
				$sKon="select satuankonversi,jumlah from ".$dbname.".log_5stkonversi where kodebarang='".$val['kodebarang']."'";
				$rKon=fetchData($sKon);
				$dtDta[$val['divisi'].$val['kodebarang']]['satuan']=$brgDt['satuan'];	
				$dtDta[$val['divisi'].$val['kodebarang'].$val['tanggal']]['qty']=$val['qty'];
				if(count($rKon)==1){
					$dtDta[$val['divisi'].$val['kodebarang']]['satuan']=$rKon[0]['satuankonversi'];	
					$dtDta[$val['divisi'].$val['kodebarang'].$val['tanggal']]['qty']=($val['qty']*$rKon[0]['jumlah']);
				}
				$lstData[$val['divisi']]=$val['divisi'];
				$dtBrg[$val['divisi']][]=$val['kodebarang'];
				$dtDta[$val['divisi'].$val['kodebarang']]['namabarang']=$brgDt['namabarang'];
				
				# code...
				*/
				//$dataAll[$rData[$i]['divisi']][(int)$rData[$i]['bulan']][] = $data;
				$dataAll[$rData[$i]['divisi']][$rData[$i]['kodebarang']]['unit'] = $satuan;
				$dataAll[$rData[$i]['divisi']][$rData[$i]['kodebarang']]['namabarang'] = $namabarang;
				$dataAll[$rData[$i]['divisi']][$rData[$i]['kodebarang']][(int)$rData[$i]['bulan']] = $data;
				$dataAll[$rData[$i]['divisi']][$rData[$i]['kodebarang']][(int)$rData[$i]['bulan']] = $data;
				//$dataAll[4][]
				//$dataAll[5][]
			}
			

		}
		$brd=0;
		$bgcl="";
		if($param['display']=='excel'){
			$brd=1;
			$bgcl=" bgcolor='#dedede'";
		}

		$tab.="<table cellspacing=1 cellpadding=1 border=".$brd." class=sortable><thead>";
		switch ($param['tipe']) {
			
			case '0':
				$listDivisi = array();
				foreach($dtBrg as $divisi => $v){
					array_push($listDivisi,$divisi);
				}
				//echo "<pre>";
				//print_r ($dtBrg);
				if(count($listDivisi)==0){
					exit('warning:'.$_SESSION['lang']['list']." ".$_SESSION['lang']['kosong'])	;
				}
				//echo "</pre>";
				$tab.="<tr align=center><td rowspan=2 ".$bgcl.">".$_SESSION['lang']['divisi']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['material']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['unit']."</td>";
				$tab.="<td colspan='".$isiDta[2]."' ".$bgcl.">".$_SESSION['lang']['hari']."</td>";
				$tab.="<td  rowspan=2 ".$bgcl.">".$_SESSION['lang']['total']."</td>";
				$tab.="</tr><tr>";
				$day = 0;
				for($i=date("Y-m-d", strtotime($param['periode']."-01")); $i<=date("Y-m-t", strtotime($param['periode']."-01"));$i++){
					$tab.="<td align=center ".$bgcl.">".(date("d", strtotime($i)))."</td>";
					$day++;
				}
				$tab.="</tr></thead><tbody>";
				//array_multisort($lstData);
				//contoh pembentukan bentuk Array dari atas
				//$dtBrg[$val['divisi']][$val['kodebarang']]['namabarang'] 	= $val['namabarang'];
				//$dtBrg[$val['divisi']][$val['kodebarang']][$val['tanggal']]['qty']
				$Divisi =array_unique($listDivisi);
				asort($Divisi);
				/*echo"<pre>";
				print_r(print_r($Divisi));
				print_r(print_r($Divisi2));
				echo"</pre>";
				exit();		*/
				foreach($Divisi as $divisi){
					$jmlKode = count(@$dtBrg[$divisi]);//jumlah barang di 1 divisi
					if( $jmlKode<=0)
					{ $jmlKode=1; }
					$tab.="<tr class=rowcontent>";
					$tab.="<td rowspan=".$jmlKode.">".$divisi."</td>";
					
					$no = 0;
					$grandTotal = 0;
					if(isset($dtBrg[$divisi])){
						foreach($dtBrg[$divisi] as $kodebarang => $var){
							if($no > 0){
								$tab.="<tr class=rowcontent>";
							}
							$tab.="<td>".$var['namabarang']."</td>";
							$tab.="<td>".$var['satuan']."</td>";
							$total = 0;
							for($i=date("Y-m-d", strtotime($param['periode']."-01"));$i<=date("Y-m-t", strtotime($param['periode']."-01"));$i++){
								$qty = 0;
								if(isset($var[$i]['qty'])){
									$qty = $var[$i]['qty'];
								}
								$color = '';
								if((double)$qty == 0){
									$color = 'style="color:#d4eaf9"';
								}
								$tab.="<td align=right ".$color.">".number_format($qty,2)."</td>";
								$total +=$qty;
							}
							$tab.="<td align=right>".number_format($total,2)."</td>";
							$grandTotal += $total;
							$no++;
							$tab.="</tr>";
						}
						
					}
				}
				//$tab.="<tr class=rowcontent>";
				//$tab.="<td colspan='".($day+3)."'>GRANT TOTAL</td>";
				//$tab.="<td align=right>".number_format($grandTotal,2)."</td>";
				//$tab.="</tr>";

				$tab.="</tbody></table>";
			break;
			case'1':
				$tab.="<tr align=center><td rowspan=2 ".$bgcl.">".$_SESSION['lang']['material']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['unit']."</td>";
				$tab.="<td colspan=".count($rListAfd)." ".$bgcl.">".$_SESSION['lang']['divisi']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['total']."</td>";
				$tab.="</tr><tr>";
				foreach ($rListAfd as $key => $val) {
					$tab.="<td ".$bgcl.">".$val['namaorganisasi']."</td>";
				}
				$tab.="</tr></thead><tbody>";
				foreach ($lstBarang as $isiDt) {
					$tab.="<tr class=rowcontent>";
					$tab.="<td>".$nmList[$isiDt]['namabarang']."</td>";
					$tab.="<td>".$nmList[$isiDt]['satuan']."</td>";
					foreach ($rListAfd as $key => $val){
						$tab.="<td align=right>".number_format($dtDta[$isiDt.$val['kodeorganisasi']]['qty'],2)."</td>";
						$totPerBrg[$isiDt]+=$dtDta[$isiDt.$val['kodeorganisasi']]['qty'];
					}
					$tab.="<td align=right>".number_format($totPerBrg[$isiDt],2)."</td>";
					$tab.="</tr>";
				}
				$tab.="</tbody></table>";
			break;

		case '2':
					  

		/*	echo"<pre>";
				 print_r($brgDt);
				 echo"</pre>";*/

				//if(count($lstData)==0){
					//exit('warning:'.$_SESSION['lang']['list']." ".$_SESSION['lang']['kosong'])	;
				//}
				$tab.="<tr align=center><td rowspan=2 ".$bgcl.">".$_SESSION['lang']['divisi']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['material']."</td>";
				$tab.="<td rowspan=2 ".$bgcl.">".$_SESSION['lang']['unit']."</td>";
				$tab.="<td colspan=12 ".$bgcl.">".$_SESSION['lang']['bulan']."</td>";
				$tab.="<td  rowspan=2 ".$bgcl.">".$_SESSION['lang']['total']."</td>";
				$tab.="</tr><tr>";
				for($awal=1;$awal<=12;$awal++){
					$tab.="<td align=center ".$bgcl.">".numToMonth ($awal)."</td>";
				}



				/*for($awal=1;$awal<=12;$awal++){
                      if($i<10){
                        $awal='0'.$awal;
                      }*/
                //$sumtotal = array();
				$tab.="</tr></thead><tbody>";
				for($i=0;$i<count($divisi);$i++){
					$dataperdivisi = array();
					if(isset($dataAll[$divisi[$i]['kodeorganisasi']])){
						$dataperdivisi =$dataAll[$divisi[$i]['kodeorganisasi']];
					}

					// echo"<pre>";
				 // print_r($dataperdivisi);
				 // echo"</pre>";
					$jmlKode = (count($dataperdivisi));
					if( $jmlKode<=0)
					{ $jmlKode=1; }
				
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=left rowspan='".$jmlKode."' >".$divisi[$i]['namaorganisasi']."</td>";
					
					$no = 0;
					if(count($dataperdivisi) > 0){
						foreach($dataperdivisi as $key => $val){
							if($no != 0){
								$tab.="<tr class=rowcontent>";
							}
							$tab.="<td align=left >".$val['namabarang']."</td>";
							
							$tab.="<td align=center >".$val['unit']."</td>";
							$total=0;
							for($awal=1;$awal<=12;$awal++){
								if(isset($val[$awal])){
									$dataqty =$dataAll[$divisi[$i]['kodeorganisasi']];
									if(empty($sumtotal[$val[$awal]['kodebarang']][$awal])){
										$sumtotal[$val[$awal]['kodebarang']][$awal] = $val[$awal]['qty']*$val[$awal]['jumlah'];
									}else{
										$sumtotal[$val[$awal]['kodebarang']][$awal] += $val[$awal]['qty']*$val[$awal]['jumlah'];
									}
								}
							    $color = '';
								if((double)@$val[$awal]['qty']*(double)@$val[$awal]['jumlah'] == 0){
									$color = 'style="color:#d4eaf9"';
								}
								$tab.="<td align=right ".$color.">".number_format((double)@$val[$awal]['qty']*(double)@$val[$awal]['jumlah'],2)."</td>";
								$total+=(double)@$val[$awal]['qty']*(double)@$val[$awal]['jumlah'];
							  		
								}

								$tab.="<td align=right >".number_format($total,2)."</td>";

							$tab.="</tr>";
							//mencari data barang di transaksi
							$sumtotalBarang[$key] = $val['namabarang'];
							$sumtotalUnit[$key] = $val['unit'];
							$no++;
						}
					}else{
						$tab.="<td align=center ></td>";
						$tab.="<td align=center ></td>";
						$tab.="<td align=center ></td>";
						for($awal=1;$awal<=12;$awal++){
								$tab.="<td align=center ></td>";
								}
						$tab.="</tr>";
					}


					//sumtottal
				
				}

				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center rowspan=".count($sumtotalBarang).">TOTAL</td>";
				$no =0;
				$sumaryotal=array();
				foreach ($sumtotalBarang as $key => $val) {
					if($no != 0){
						$tab.="<tr class=rowcontent>";
					}
					$tab.="<td align=center >".$val."</td>";
					$tab.="<td align=center >".$sumtotalUnit[$key]."</td>";
					for($awal=1;$awal<=12;$awal++){
					$color = '';
					if((double)@$sumtotal[$key][$awal] == 0){
						$color = 'style="color:#d4eaf9"';
					}
					$tab.="<td align=center ".$color.">".number_format((double)@$sumtotal[$key][$awal],2)."</td>";
						if(empty($sumaryotal)){
							$sumaryotal[$key]=(double)@$sumtotal[$key][$awal];
						}else{
							$sumaryotal[$key]+=(double)@$sumtotal[$key][$awal];
						}
					}
						
					$tab.="<td align=right >".number_format($sumaryotal[$key],2)."</td>";
					$tab.="</tr>";
					$no++;
				}


				$tab.="</tbody></table>";
					/*echo"<pre>";
				 print_r($sumtotal);
				 echo"</pre>";*/
			break;
			
		}
		if($param['display']=='preview'){
			echo $tab;	
		}
		if($param['display']=='excel'){
			
			$nop_ = "material_chem_ferti_".$param['kebun']."_". date('Ymd_His');
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
	                            parent.window.alert('Cant convert to excel format');
	                      </script>";
	              exit;
	            } else {
	                echo "<script language=javascript1.2>
	                            window.location='tempExcel/" . $nop_ . ".xls';
	                      </script>";
	            }
	            closedir($handle);
	        } 
		}
		
	break;
	 
}


















































// 		if($method=='preview'){
// 			$border=0;
// 			$header="";
// 		}else {
// 			$border=1;
// 			$header="<tr class=rowheader>
// 						<th align=center colspan=10>".$_SESSION['lang']['lapkerja']."</th>
// 					</tr>				
// 					<tr class=rowheader>
// 						<th align=left colspan=4>".$_SESSION['lang']['kebun']."  : ".$kebun."</th>
// 						<th align=left colspan=6>".$_SESSION['lang']['tanggal']." : ".$tanggal."</th>
// 					</tr>	
// 					<tr class=rowheader>
// 						<th align=left colspan=10>".$_SESSION['lang']['afdeling']." : ".$divisi."</th>
// 					</tr>";
			
// 			$ttd="
// 			<br>
// 			<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%'>
// 				<tr>
// 					<td align=left colspan=5>Reported By: </td>
// 					<td align=left colspan=5>Checked By: </td>
// 				</tr>
// 			</table>
// 			<br>
// 			<br>
// 			<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%'>
// 				<tr>
// 					<td align=left colspan=5>Nama</th>
// 					<td align=left colspan=5>Nama</th>
// 				</tr>
// 			</table>";
// 		}
		
//         $tab="<table class=sortable cellspacing=1 cellpadding=7 border='".$border."' style='width:100%'>
//               <thead>				
// 				".$header."
				
//                 <tr class=rowheader>
				
//                     <th align=center colspan=3 rowspan=2>".$_SESSION['lang']['jeniskerja']."</th>
// 					<th align=center rowspan=2>".$_SESSION['lang']['blok']."</th>              
//                     <th align=center rowspan=2>".$_SESSION['lang']['kerjatelahselesai']."</th>
// 					<th align=center rowspan=2>".$_SESSION['lang']['biayaunit']."</th>
// 					<th align=center colspan=3>".$_SESSION['lang']['sumberdaya']."</th>
//                     <th align=center rowspan=2>".$_SESSION['lang']['remark']."</th>
//                 </tr>	
// 				<tr class=rowcontent>
                    
//                         <td align=center>".$_SESSION['lang']['pekerja']."</td>
//                         <td align=center>".$_SESSION['lang']['alatmesin']."</td>	
//                         <td align=center>".$_SESSION['lang']['nomor']."</td>
                
// 				</tr>	
				
//               </thead>";

// 		//untuk menampilkan kiriman divisi dan kebun
//       	$where='';
// 		if ($kebun !=''){
// 			$where=" and a.kodeorg like '".$kebun."%'";
// 		}
// 		if ($divisi !=''){
// 			$where.=" and a.kodeorg like '".$divisi."%'";
// 		}
// 		if ($tanggal !=''){
// 			$where.=" and b.tanggal = '".$tanggal."'";
// 		}
		
// 		//query status blok, jenis kegiatan, satuan, work complete, jumlah pekerja 
// 		$str = "select b.tipetransaksi,a.kodekegiatan,
// 		IFNULL(e.namakegiatan,'') as namakegiatan,
// 		IFNULL(e.satuan,'') as satuan,
// 		a.kodeorg,a.jumlahhk as jumlahpekerja,b.tanggal,c.statusblok,a.hasilkerja
// 		from ".$dbname.".kebun_prestasi a 
// 		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
// 		left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg 
// 		left join ".$dbname.".setup_kegiatan e on e.kodekegiatan=a.kodekegiatan 
// 		where b.tipetransaksi in ('PNN','TM','TBM') ".$where."";
// 		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 		$data = array();
// 		$listnotransaksi = array();
// 		while ($bar=$res->fetch())
// 		{	
// 			if($bar['tipetransaksi'] == "PNN"){
// 				$bar['kodekegiatan'] = 'PNN';
// 				$bar['namakegiatan'] = 'Harvesting';
// 				$bar['satuan'] = 'Mt';
// 			}else{
// 				if ($bar['kodekegiatan'] =='' or $bar['kodekegiatan'] =='0'){
// 					$bar['kodekegiatan'] = '0';
// 					$bar['namakegiatan'] = '';
// 					$bar['satuan'] = '';
// 				}
// 			}
// 		/*
// 			$kodekegiatan[$bar['kodekegiatan']]=$bar['kodekegiatan'];
// 			$statusblok[$bar['statusblok']]=$bar['statusblok'];
// 			$kodeorg[$bar['kodeorg']]=$bar['kodeorg'];
// 			$listkeg[$bar['statusblok']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
// 			$liststblok[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]=$bar['kodeorg'];
// 			$prestasi[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['hasilkerja'];
// 			$jumlahpekerja[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['jumlahpekerja'];
// 			$namabahan[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['kodebarang'];
// 			$kwantitas[$bar['statusblok']][$bar['kodekegiatan']][$bar['kodeorg']]+=$bar['kwantitas'];
// 		*/
			
// 			$listnotransaksi[]																	= "'".$bar['notransaksi']."'";
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodekegiatan']						=$bar['kodekegiatan'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['namakegiatan']						=$bar['namakegiatan'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['tipetransaksi']					=$bar['tipetransaksi'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['satuan']							=$bar['satuan'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['statusblok']						=$bar['statusblok'];
// 			//hitung
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['kodeorg'][]						=$bar['kodeorg'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['blok'][]							=substr($bar['kodeorg'],6,10);
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['hasilkerja'][$bar['kodeorg']]		+=$bar['hasilkerja'];
// 			$data[$bar['statusblok']][$bar['kodekegiatan']]['jumlahpekerja'][$bar['kodeorg']]	+=$bar['jumlahpekerja'];
			
		
// 		}
		
// 		//query untuk cost per unit
// 		if(count($listnotransaksi)>0){
// 			$notrans = implode(",",array_unique($listnotransaksi));
// 			$str ="select a.kodebarang,a.kwantitas,a.kwantitasha,c.statusblok,b.namabarang,b.satuan from ".$dbname.".kebun_pakai_material_vw a
// 			left join ".$dbname.".log_5masterbarang b on a.kodebarang=b.kodebarang 
// 			left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg 
// 			where notransaksi in (".$notrans.")";
// 			$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
// 			$res->setFetchMode(PDO::FETCH_ASSOC);
// 			while ($bar=$res->fetch()){		
// 				$data[$bar['statusblok']][$bar['kodekegiatan']]['namabarang'][$bar['kodeorg']][$bar['kodebarang']]	= $bar['kodebarang'];
// 				$data[$bar['statusblok']][$bar['kodekegiatan']]['namabarang'][$bar['kodeorg']][$bar['kodebarang']]	= $bar['namabarang'];
// 				$data[$bar['statusblok']][$bar['kodekegiatan']]['kwantitas'][$bar['kodeorg']][$bar['kodebarang']]	+= $bar['kwantitas'];
// 				$data[$bar['statusblok']][$bar['kodekegiatan']]['satuanbarang'][$bar['kodeorg']][$bar['kodebarang']]= $bar['satuan'];
// 			}
// 		}
		
// 		//query untuk cost per unit
// 		$str ="select * from ".$dbname.".keu_jurnaldt_vw where substr(noakun,1,3) in ('126','621','611') and kodeorg in ('ETAE','ETBE','ETCE','ETDE')";
// 		//echo $str;
// 		$res = $owlPDO->query($str) or die(print "Gagal: ".PDOException::getMessage());
// 		$res->setFetchMode(PDO::FETCH_ASSOC);
// 		while ($bar=$res->fetch()){		
// 			$rupiah[$bar['kodekegiatan']][$bar['kodeblok']]+=$bar['jumlah'];
// 		}
// 	function getArrayByArray($array1,$arrayParam){
// 		$result = array();
// 		foreach($arrayParam as $v){
// 			if(isset($array1[$v])){
// 				$result[] = $array1[$v];
// 			}
// 		}
// 		return $result;
// 	}
	
	
// //Khusus TM,TBM
// $statusBlok = array('TM'=>'Mature','TBM'=>'Immature');
// foreach ($statusBlok as $s => $sv){
// 	$numb = 0;
// 	if(isset($data[$s])){
// 		foreach ($data[$s] as $k =>$v)
// 		{
// 			$hasilkerja = 0;
// 			if(!empty($v['hasilkerja'])){
// 				$hasilkerja = number_format(array_sum($v['hasilkerja']));
// 			}
// 			$jumlahpekerja = "";
// 			if(!empty($v['jumlahpekerja'])){
// 				$jumlahpekerja = number_format(array_sum($v['jumlahpekerja']));
// 			}
// 			$kodeorg = array();
// 			$blok = array();
// 			if(count($v['kodeorg'])>0){
// 				$kodeorg =array_unique($v['kodeorg']);
// 				$blok =array_unique($v['blok']);
// 			}
// 			$namabarang = "";
// 			if(count($v['namabarang'])>0){
// 				if($v['tipetransaksi'] == "PNN"){
// 					$namabarang = "Bunch";
// 				}else{
// 					$barang = getArrayByArray($v['namabarang'],$kodeorg);
// 					$namabarang = implode(",",$barang);
// 				}
// 			}
// 			$cost = "";
// 			if(!empty($rupiah[$v['kodekegiatan']])){
// 				$cost = number_format(array_sum($rupiah[$v['kodekegiatan']]));
// 			}
// 			$kwantitas = "";
// 			if(count($v['kwantitas'])>0){
// 				$kwa = getArrayByArray($v['kwantitas'],$kodeorg);
// 				$kwantitas = number_format(array_sum($kwa));
// 			}
// 			$remark = "";
// 			if(count($v['satuanbarang']) > 0){
// 				$sat = getArrayByArray($v['satuanbarang'],$kodeorg);
// 				$remark = array_sum($sat);
// 			}
// 			$tab.="<tr class=rowcontent>";
// 			if($numb == 0){
// 				$tab.="<td align=center rowspan='".count($data[$s])."'>".$sv."</td>";
// 				$numb = 1;
// 			}
// 			$tab.="<td align=left>".$v['namakegiatan']."</td>";
// 			$tab.="<td align=center>".$v['satuan']."</td>";
// 			$tab.="<td align=left>".implode(",",$blok)."</td>";
// 			$tab.="<td align=center>".$hasilkerja."</td>";
// 			$tab.="<td align=center>".$cost."</td>";
// 			$tab.="<td align=center>".$jumlahpekerja."</td>";
// 			$tab.="<td align=left>".$namabarang."</td>";
// 			$tab.="<td align=center>".$kwantitas."</td>";
// 			$tab.="<td align=left>".$remark."</td>";
// 			$tab.="</tr>";
// 		}
// 	}
// }
	
// 		/*
// 		 echo "<pre>";
// 		print_r($data);
// 		 echo "</pre>";
// 		*/
// 		/*
// 		tabel:keu_jurnaldt_vw
// 		where :tanggal
// 		where:kodeblok!='' or '0'
// 		*/
// 		// $whrblok='';
// 		// if ($divisi !=''){
// 			// $whrblok=" and left(kodeblok,6)='".$divisi."'";
// 		// }
		
		
		
// 	/*	
// 	foreach ($statusblok as $stblok)
// 	{
// 	   foreach ($kodekegiatan as $keg)
// 	   { 
// 			if ($listkeg[$stblok][$keg]!='')
// 			{ 
// 			   foreach ($kodeorg as $blok)
// 			   {
// 					if ($liststblok[$stblok][$keg][$blok]!='')
// 					{ 
// 						if ($prestasi[$stblok][$keg][$blok]!='')
// 						{
// 							//supaya memorinya ga terlalu banyak jadi makeOption nya dipake disini
// 							$satuan=makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan'," kodekegiatan='".$keg."'");
// 							$namakeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan'," kodekegiatan='".$keg."'");
// 							$namabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang'," kodebarang='".$namabahan[$stblok][$keg][$blok]."'");
// 							$remark=makeOption($dbname,'log_5masterbarang','kodebarang,satuan'," kodebarang='".$namabahan[$stblok][$keg][$blok]."'");
							
// 							$tab.="<tr class=rowcontent>";
// 							$tab.="<td align=center>".$stblok."</td>";
// 							$tab.="<td align=center>".$namakeg[$keg]."</td>";
// 							$tab.="<td align=center>".$satuan[$keg]."</td>";
// 							$tab.="<td align=center>".$blok."</td>";
// 							$tab.="<td align=center>".$prestasi[$stblok][$keg][$blok]."</td>";
// 							$tab.="<td align=center>".$rupiah[$keg][$blok]."</td>";
// 							$tab.="<td align=center>".$jumlahpekerja[$stblok][$keg][$blok]."</td>";
// 							$tab.="<td align=center>".$namabarang[$namabahan[$stblok][$keg][$blok]]."</td>";
// 							$tab.="<td align=center>".$kwantitas[$stblok][$keg][$blok]."</td>";
// 							$tab.="<td align=center>".$remark[$namabahan[$stblok][$keg][$blok]]."</td>";
// 							$tab.="</tr>";
// 						}
// 					}
// 				}		   
// 			}  
// 		}
// 	}
// 	*/
// 	$tab.="</table>";
// 	$tab.=$ttd;			
	
// 	if($method=='excel'){
// 		$nop_ = "Daily_Report_and_Programme" . date('Ymd_His');
//         if (strlen($tab) > 0) {
//             if ($handle = opendir('tempExcel')) {
//                 while (false !== ($file = readdir($handle))) {
//                     if ($file != "." && $file != ".." && $file != "index.html") {
//                         @unlink('tempExcel/' . $file);
//                     }
//                 }
//                 closedir($handle);
//             }
//             $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
//             if (!fwrite($handle, $tab)) {
//                 echo "<script language=javascript1.2>
//                             parent.window.alert('Cant convert to excel format');
//                       </script>";
//               exit;
//             } else {
//                 echo "<script language=javascript1.2>
//                             window.location='tempExcel/" . $nop_ . ".xls';
//                       </script>";
//             }
//             closedir($handle);
//         } 
// 	}else{
// 		echo $tab;
// 	}	
?>
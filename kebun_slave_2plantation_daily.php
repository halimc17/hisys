<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

if($_POST['method']!=''){
	$param = $_POST;	
}
if(@$_GET['method']!=''){
	$param = $_GET;
}

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$divisi=checkPostGet('divisi','');
$tanggal=tanggalsystemn (checkPostGet('tanggal',''));
$periode=substr(checkPostGet('periode',''),0,4);



//echo $kebun.' '.$divisi.' '.$tanggal;

switch ($param['method']) {

	case 'getkebun':
	//exit('error');
	$whrpr="1=1";
	if($param['pt']!=""){
		$whrpr="induk='".$param['pt']."'";
	}
		$sData="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
		        where ".$whrpr." and tipe='kebun'";
		        //exit('error'.$sData);
		$rData=fetchData($sData);
		foreach($rData as $row=>$isiDt){
			$lstAfd[$isiDt['kodeorganisasi']]=$isiDt['namaorganisasi'];
			//$optkebun.="<option value='".$lstAfd."'>".$lstAfd."</option>";
		}

		$optkebun="<option value=''>".$_SESSION['lang']['all']."</option>";
		if(count($lstAfd)!=0){
			foreach ($lstAfd as $key){
				$optNm1=makeOption($dbname,"organisasi","namaorganisasi,kodeorganisasi","namaorganisasi='".$key."'");
				@$optkebun.="<option value='".$optNm1[$key]."'>".$key."</option>";
			}	
		}
		
		echo $optkebun;
	break;

	case 'getperiode':
	$whrpr="unit<>''";
	if($param['kebun']!=""){
		$whrpr="unit='".$param['kebun']."'";
	}
	/*else {
		$whrpr="unit='".$param['kebun']."'";
	}*/
		$sData="select distinct left(kodeorg,6) as divisi from ".$dbname.".kebun_perawatan_vw 
		        where ".$whrpr." order by left(kodeorg,6) desc ";
		        //exit('error'.$sData);
		$rData=fetchData($sData);
		foreach($rData as $row=>$isiDt){
			$lstAfd[$isiDt['divisi']]=$isiDt['divisi'];
			//$lstPeriode[$isiDt['periode']]=$isiDt['periode'];
		}
		$sData="select distinct periode from ".$dbname.".kebun_perawatan_vw where ".$whrpr." order by periode desc ";
		        //exit('error'.$sData);
		$rData=fetchData($sData);
		foreach($rData as $row=>$isiDt){
			$lstPeriode[$isiDt['periode']]=$isiDt['periode'];	
		}
		 
		$optDivisi="<option value=''>".$_SESSION['lang']['all']."</option>";
		@$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if(count(@$lstAfd)!=0){
			foreach ($lstAfd as $key){
				$optNm=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$key."'");
				$optDivisi.="<option value='".$key."'>".$optNm[$key]."</option>";
			}	
		}
		if(count($lstPeriode)!=0){
			foreach ($lstPeriode as $key){
				$optPeriode.="<option value='".$key."'>".$key."</option>";
			}	
		}
		
		echo $optDivisi."####".$optPeriode;
	break;

	
	case'preview':
		if($param['tipe']==0){
			if($param['periode']==''){
				exit('warning :'.$_SESSION['lang']['periode']." ".$_SESSION['lang']['kosong']);
			}	
		}
		if($param['tipe']!=0){
			if($param['tglSmp']==''){
				exit('warning :'.$_SESSION['lang']['periode']." ".$_SESSION['lang']['kosong']);
			}	
		}
		$tglHrini=tanggalsystemn($param['tglSmp']);
		//exit('warning'.$param['groupId']);
		$hari_ini=$param['periode']."-01";
		// Tanggal pertama pada bulan ini
		$tgl_pertama = date('Y-m-01', strtotime($hari_ini));

		// Tanggal terakhir pada bulan ini
		$tgl_terakhir = date('Y-m-t', strtotime($hari_ini));

		$isiDta=explode("-",$tgl_terakhir);
		//exit('warning'.$isiDta[2]);
		#display data
		#$arrTipe=array("0"=>"Daily","1"=>"Summary Of Detail","1"=>"Summary Of Detail","2"=>"SUMMARY CONSUMPTION","3"=>"Annual");
		$namaLaporan="DAILYREPORT";
		$arrNourut=array();
		$arrDisplay=array();
		$sData="select * from ".$dbname.".keu_5mesinlaporandt where namaLaporan='".$namaLaporan."' order by nourut asc";
		$rData=fetchData($sData);
		foreach($rData as $rw=>$lst){
			$arrDisplay[$lst['nourut']]['nourut']=$lst['nourut'];
			$arrDisplay[$lst['nourut']]['tipe']=$lst['tipe'];
			$arrDisplay[$lst['nourut']]['satuan']=$lst['satuan'];
			$arrDisplay[$lst['nourut']]['keterangandisplay']=$lst['keterangandisplay'];
			$isiNoakunDisplay=array();
			$isiNoakunDisplay=explode("##",$lst['noakundisplay']);
			if($param['groupId']==''){
				$lstNoakunDisplay="";
				foreach ($isiNoakunDisplay as $key => $val) {
					if($key==0){
						$lstNoakunDisplay="'".$val."'";
					}else{
						$lstNoakunDisplay.=",'".$val."'";
					}
				}

			}else{
				//exit('warning'.$param['groupId']);
				switch ($param['groupId']) {##ambil kodekegiatan dengan explode ## 0=kegiatan TB,1=Kegiatan=TBM,2=Kegiatan TM
					case 'TB':
						$lstNoakunDisplay=$isiNoakunDisplay[0];
					break;
					case 'TBM':
						$lstNoakunDisplay=$isiNoakunDisplay[1];
					break;
					case 'TM':
						$lstNoakunDisplay=$isiNoakunDisplay[2];
					break;
				}
			}
			//exit('warning:'.$lstNoakunDisplay);
			//$arrDisplay[$lst['nourut']]['noakundisplay']=$lstNoakunDisplay;
			$wheredt="";
			#query ambil data
			if(($lstNoakunDisplay=='0')||($lstNoakunDisplay=='')){
				continue;
			}
			
			if($param['kebun']!=''){
				$wheredt=" and unit='".$param['kebun']."'";
			}
			$wheredt2 = $wheredt;
			$wheredt3 = $wheredt;
			if($param['divisi']!=''){
				//$wheredt=" and left(kodeorganisasi,6)='".$param['divisi']."'";
				$wheredt2 = $wheredt." and left(kodeorg,6)='".$param['divisi']."'";
				$wheredt3 = $wheredt." and left(kodeblok,6)='".$param['divisi']."'";
			}
			
			
			switch ($param['tipe']) {
				case '0':
				if($lst['nourut']=='100001'){
					#panen realisasi
					$sData="select tanggal as tgl,tanggal,sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi_vw where 
					        left(tanggal,7)='".$param['periode']."' ".$wheredt2." group by tanggal asc";
					$rData=fetchData($sData);
					foreach($rData as $row=>$val){
						$arrDisplay[$lst['nourut']][$val['tgl']]=$val['hasilkerja'];
					}					        
				}
				if($lst['nourut']=='100002'){
					#panen
					$sData="select tanggal as tgl,tanggal,sum(target) as hasilkerja,kodekegiatan from ".$dbname.".kebun_rkh_vw where 
					        left(kodekegiatan,3)='611' and left(tanggal,7)='".$param['periode']."' ".$wheredt3." group by tanggal asc";
					 //exit('warning'.$sData);
					$rData=fetchData($sData);
					foreach($rData as $row=>$val){
						$listDt[$val['tanggal']]+=$val['hasilkerja'];
						$listDtTgl[$val['tanggal']]=$val['tgl'];
						
					}	
					if(count($listDt)!=0){
						foreach($listDt as $row=>$isiData){
							$tglBesok=tglbesok($row);
							$arrDisplay[$lst['nourut']][$listDtTgl[$row]]+=$listDt[$tglBesok];	
						}							
					}
			        
				}
					
				if($lst['nourut']>'100002'){
					$sData="select tanggal as tgl,tanggal,sum(hasilkerja) as hasilkerja,kodekegiatan from ".$dbname.".kebun_perawatan_vw where 
					        kodekegiatan in (".$lstNoakunDisplay.") and left(tanggal,4)='".substr($param['periode'],0,4)."' ".$wheredt2." group by tanggal asc";
					//echo $sData;
					//exit('warning');
					$rData=fetchData($sData);
					foreach($rData as $row=>$val){
						$arrDisplay[$lst['nourut']][$val['tgl']]=$val['hasilkerja'];
					}
					
					
				}
				break;
				case'1':				
					$param['periode']=substr($tglHrini,0,7);
					if($lst['nourut']=='100001'){
							#panen realisasi
							$sData="select left(tanggal,7) as periode,tanggal,sum(hasilkerja) as hasilkerja from ".$dbname.".kebun_prestasi_vw where 
							        left(tanggal,4)='".substr($param['periode'],0,4)."' ".$wheredt2." group by tanggal asc";
							$rData=fetchData($sData);
							foreach($rData as $row=>$val){
								if($tglHrini==$val['tanggal']){
									$arrDisplay[$lst['nourut']][$tglHrini]=$val['hasilkerja'];	
								}
								if($val['periode']==$param['periode']){
									if($val['tanggal']<=$tglHrini){
										$arrDisplay[$lst['nourut']]['sdBln']+=$val['hasilkerja'];		
									}
								}
								if(substr($val['periode'],0,4)==substr($param['periode'],0,4)){
									if($val['tanggal']<=$tglHrini){
										$arrDisplay[$lst['nourut']]['dlmThn']+=$val['hasilkerja'];
									}
								}
							}					        
					}
					if($lst['nourut']=='100002'){
						#panen rencana harian
						$sData="select left(tanggal,7) as periode,tanggal,sum(target) as hasilkerja,kodekegiatan from ".$dbname.".kebun_rkh_vw where 
						        left(kodekegiatan,3)='611' and left(tanggal,4)='".substr($param['periode'],0,4)."' ".$wheredt3." group by tanggal asc";
						 //exit('warning'.$sData);
						$rData=fetchData($sData);
						foreach($rData as $row=>$val){
							$listDt[$val['tanggal']]+=$val['hasilkerja'];
							$listDtTgl[$val['tanggal']]=$val['tgl'];
							
						}	
						if(count($listDt)!=0){
							foreach($listDt as $row=>$isiData){
								$tglBesok=tglbesok($row);
								$tglbesokHrini=tglbesok($tglHrini);
								if($row==$tglbesokHrini){
									$arrDisplay[$lst['nourut']][$tglHrini]=$listDt[$tglbesokHrini];	
								}
								//$arrDisplay[$lst['nourut']][$listDtTgl[$row]]+=$listDt[$tglBesok];	
								if(substr($row,0,7)==$param['periode']){
									if($row<=$tglbesokHrini){
										$arrDisplay[$lst['nourut']]['sdBln']+=$listDt[$tglBesok];		
									}
								}
								if(substr($row,0,4)==substr($param['periode'],0,4)){
									if($row<=$tglHrini){
										$arrDisplay[$lst['nourut']]['dlmThn']+=$listDt[$tglBesok];	
									}
								}
							}							
						}
			        
					}
					//exit('warning'.$lstNoakunDisplay);
					$sData="select left(tanggal,7) as periode,tanggal,sum(hasilkerja) as hasilkerja,kodekegiatan from ".$dbname.".kebun_perawatan_vw  where  kodekegiatan in (".$lstNoakunDisplay.") and left(tanggal,7)<='".$param['periode']."' 
					        and left(tanggal,4)='".substr($param['periode'],0,4)."' ".$wheredt2." group by tanggal asc";
					//exit('warning:'.$sData);
					$rData=fetchData($sData);
					foreach($rData as $row=>$val){
						if($tglHrini==$val['tanggal']){
							$arrDisplay[$lst['nourut']][$tglHrini]=$val['hasilkerja'];	
						}
						if($val['periode']==$param['periode']){
							if($val['tanggal']<=$tglHrini){
								$arrDisplay[$lst['nourut']]['sdBln']+=$val['hasilkerja'];		
							}
						}
						if(substr($val['periode'],0,4)==substr($param['periode'],0,4)){
							if($val['tanggal']<=$tglHrini){
								$arrDisplay[$lst['nourut']]['dlmThn']+=$val['hasilkerja'];
							}
						}
						
					}
				break;
				
			}
			
			
		}
		if(count($arrDisplay)==0){
			exit('warning: Data Is Empty');
		}
		switch ($param['tipe']) {
			case '0':
				$tab="<table cellspacing=1 cellpadding=1 border=0 class=sortable width=100%><thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</td>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['unit']."</td>";
				$tab.="<td colspan=".$isiDta[2]." align=center>Days</td>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['total']."</td></tr>";
				$tab.="<tr class=rowheader>";
				for($aTgl=1;$aTgl<=$isiDta[2];$aTgl++){
					$tab.="<td align=center>".$aTgl."</td>";
				}
				$tab.="</tr></thead><tbody>";
				foreach ($arrDisplay as $key => $val) {
					if($val['tipe']=='Header'){
						$tab.="<tr class=rowcontent>";
						$tab.="<td><b>".$val['keterangandisplay']."</b></td>";
						$colTotal=($isiDta[2]+2);
						$tab.="<td colspan=".$colTotal.">&nbsp;</td>";
						$tab.="</tr>";	
					}
					if($val['tipe']=='Detail'){
						$tab.="<tr class=rowcontent>";
						$tab.="<td>".$val['keterangandisplay']."</td>";
						$tab.="<td>".$val['satuan']."</td>";
						for($aTgl=1;$aTgl<=$isiDta[2];$aTgl++){
							if($aTgl<10){
								$tglnya=$param['periode']."-0".$aTgl;
							}else{
								$tglnya=$param['periode']."-".$aTgl;
							}
							$tab.="<td align=right>".number_format($val[$tglnya])."</td>";
							$dataTotal[$key]+=$val[$tglnya];
						}
						$tab.="<td align=right>".number_format($dataTotal[$key])."</td>";
						$tab.="</tr>";	
					}
				}
				$tab.="</tbody></table>";
			break;
			case'1':
			//case'2':
				$tab="<table cellspacing=1 cellpadding=1 border=0 class=sortable width=100%><thead>";
				$tab.="<tr class=rowheader>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['namakegiatan']."</td>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['unit']."</td>";
				$tab.="<td align=center colspan=2>".tglnmbln(tanggalsystemn($param['tglSmp']),'E','short')."</td>";
				$tab.="<td align=center>01-".substr($param['tglSmp'],0,2)."/".numToMonth(substr($param['tglSmp'],3,2))."</td>";
				$tab.="<td align=center rowspan=2 >YTD (".substr($param['tglSmp'],-4,4).")</td>";
				$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['keterangan']."</td></tr>";
				$tab.="<tr class=rowheader>";
				$tab.="<td align=center>".$_SESSION['lang']['done']."</td>";
				$tab.="<td align=center>Wor./Mac.</td>";
				$tab.="<td align=center>".$_SESSION['lang']['done']."</td>";
				$tab.="</tr>";
				$tab.="</thead><tbody>";
				foreach ($arrDisplay as $key => $val) {
					if($val['tipe']=='Header'){
						$tab.="<tr class=rowcontent>";
						$tab.="<td><b>".$val['keterangandisplay']."</b></td>";
						$colTotal=($isiDta[2]+2);
						$tab.="<td colspan=".$colTotal.">&nbsp;</td>";
						$tab.="</tr>";	
					}
					if($val['tipe']=='Detail'){
						$tab.="<tr class=rowcontent>";
						$tab.="<td>".$val['keterangandisplay']."</td>";
						$tab.="<td>".$val['satuan']."</td>";
						$tab.="<td align=right>".number_format($val[$tglHrini],2)."</td>";
						$tab.="<td>&nbsp;</td>";
						$tab.="<td align=right>".number_format($val['sdBln'])."</td>";
						$tab.="<td align=right>".number_format($val['dlmThn'])."</td>";
						$tab.="<td>&nbsp;</td>";
						$tab.="</tr>";	
					}
				}
				$tab.="</tbody></table>";
			break;
			
			default:
				# code...
				break;
		}
		if($param['display']=='preview'){
			echo $tab;	
		}
		if($param['display']=='excel'){
			
			$nop_ = "plantation_daily_".$param['kebun']."_". date('Ymd_His');
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
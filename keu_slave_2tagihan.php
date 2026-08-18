<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
$stream='';

$proses = checkPostGet('proses','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}
$path   = "fileupload/keu_tagihan/";
$pathlama   = "filegis/";

switch ($proses) {
	
	
	
	case'preview':
		
		
	$nmorg	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$param['kdOrg']."'");
	if($param['tipe']=='excel'){
			$stream.="Laporan Daftar Tagihan<br>";
			if($param['kdOrg']==''){
				$unit 	= 'Seluruhnya';
				$stream.="".$unit."<br>";
			}else{
				$unit = $param['kdOrg'];
				$stream.="".$unit." - ".$nmorg[$unit]."<br>";
			}
			  $stream.="".$param['periode']." s/d ".$param['periode2']."<br><br>";
	}
			
			
			
	$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$optNmSupp=makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
	$optMatauang=makeOption($dbname, 'log_poht', 'nopo,matauang');


	if($param['tipe']=='excel'){
		$bg=" bgcolor=#DEDEDE";
		$brdr=1;
	}
	else{ 
		$bg="";
		$brdr=0;
	}
			
	$rowspDt='';   
			if($param['periode2']<$param['periode2']){
				exit('warning: '.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['periode']);
			}
			$where="left(tanggalinvoice,7) between '".$param['periode']."' and '".$param['periode2']."'";
			$where2.=" and left(tanggalinvoice,7) between '".$param['periode']."' and '".$param['periode2']."'";
			if($param['kdOrg']!=''){
				$where.=" and kodeorg='".$param['kdOrg']."'";
				$where2.=" and kodeorg='".$param['kdOrg']."'";
			}
			if($param['updateby']!=''){
				$where.=" and updateby='".$param['updateby']."'";
				$where2.=" and updateby='".$param['updateby']."'";
			}
			if($param['kodesupplier']!=''){
				$where.=" and kodesupplier='".$param['kodesupplier']."'";
				$where2.=" and kodesupplier='".$param['kodesupplier']."'";
			}
			if($param['statTagihan']=='0'){
				$where.=" and posting='".$param['statTagihan']."'";
				$where2.=" and posting='".$param['statTagihan']."'";
			}
			if($param['statTagihan']=='1'){
				$where.=" and posting='".$param['statTagihan']."'";
				$where2.=" and posting='".$param['statTagihan']."'";
			}
			if($param['noinv']!=''){
				$where.=" and noinvoice like '%".$param['noinv']."%'";   
				$where2.=" and a.noinvoice like '%".$param['noinv']."%'";   
			}
			if($param['noinvsupp']!=''){
				$where.=" and noinvoicesupplier like '%".$param['noinvsupp']."%'";   
				$where2.=" and noinvoicesupplier like '%".$param['noinvsupp']."%'";   
			}
			// if($param['nopodt']!=''){
			//     $where.=" and nopo like '%".$param['nopodt']."%'";   
			//     $where2.=" and nopo like '%".$param['nopodt']."%'";   
			// }
			
			if($param['nopodt']!=''){
				$where.=" and nopo like '%".$param['nopodt']."%'";   
				$where2.=" and b.nopo like '%".$param['nopodt']."%'";   
			}
			
			if($param['jenis']!=''){
				$where.=" and tipeinvoice = '".$param['jenis']."'";   
				$where2.=" and tipeinvoice = '".$param['jenis']."'";   
			}
			
			$optPt=makeOption($dbname,"organisasi","kodeorganisasi,induk","kodeorganisasi='".$param['kdOrg']."'");
			
			$dibyarkan=array();
			$sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,a.posting, a.tanggal,a.pembayaran,a.novoucher,a.tanggalinput from ".$dbname.".keu_kasbankdtht_vw 
					  a left join ".$dbname.".keu_tagihanht b on a.keterangan1=b.noinvoice 
					  where a.keterangan1<>'' and ((left(a.tanggal,7) between '".$param['periode']."' and '".$param['periode2']."') or (left(a.tanggalpengajuan,7) between '".$param['periode']."' and '".$param['periode2']."')) and left(a.noakun,3) in ('211','118','121','213')
					group by keterangan1,notransaksi";
			/*
			$sDetKas="select sum(jumlah) as jumlah,keterangan1,notransaksi,a.posting, a.tanggal,a.pembayaran,a.novoucher from ".$dbname.".keu_kasbankdtht_vw 
					  a left join ".$dbname.".keu_tagihanht b on a.keterangan1=b.noinvoice 
					  where a.keterangan1<>'' and ((left(a.tanggal,7) between '".$param['periode']."' and '".$param['periode2']."') or (left(a.tanggalpengajuan,7) between '".$param['periode']."' and '".$param['periode2']."')) and left(a.noakun,3) in ('211','118','121','213') and a.pembayaran = '1'
					group by keterangan1,notransaksi";
			*/		
			//exit('warning'.$sDetKas);
			$rDetKas=fetchData($sDetKas);
			foreach($rDetKas as $row=>$lst){
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['dibayar']+=$lst['jumlah'];
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['tanggal']=$lst['tanggal'];
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['tanggalinput']=$lst['tanggalinput'];
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['posting']=$lst['posting'];    
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['pembayaran']=$lst['pembayaran'];    
					$dibyarkan[$lst['keterangan1']][$lst['notransaksi']]['novoucher']=$lst['novoucher'];    
					$totByrKan[$lst['keterangan1']]+=$lst['jumlah'];
			}
		
			$rDet=array();
			$nilPPn=array();
			$nilUangMuka=array();
			$nilpph=array();
			$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
				   ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
				   and left(a.noakun,3) in ('117','118','213','711','116','115') group by a.noinvoice,a.noakun";
				   // echo $sDet;
		   
			$rDet=fetchdata($sDet);
			foreach($rDet as $row=>$lstData){
			   
				if($lstData['nilai']<0){
					if(substr($lstData['noakun'],0,3)=='118'){
						$nilUangMuka[$lstData['noinvoice']]+=$lstData['nilai'];
					} 
					if(substr($lstData['noakun'],0,3)=='213'){
						$nilpph[$lstData['noinvoice']]+=$lstData['nilai'];
					} 
					if((substr($lstData['noakun'],0,3)=='711')||(substr($lstData['noakun'],0,3)=='116')||(substr($lstData['noakun'],0,3)=='115')){
						$bylain[$lstData['noinvoice']]+=$lstData['nilai'];
					}   
				}
				if(substr($lstData['noakun'],0,3)=='117'){
					$nilPPn[$lstData['noinvoice']]+=$lstData['nilai'];
				} 
			}

			// echo"<pre>";
			// print_r($nilPPn);
			// echo"</pre>";

			$ayatsilang=array();
			$sDet="select a.noinvoice as noinvoice,sum(nilai) as nilai,a.noakun as noakun,b.postingby,b.tipeinvoice from ".$dbname.".keu_tagihandt a left join 
				   ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where 1=1 ".$where2." 
				   and nilai<0 and a.noakun='1110401' group by a.noinvoice,b.noakun";
			$rDet=fetchdata($sDet);
			foreach($rDet as $row=>$lstData){
				$ayatsilang[$lstData['noinvoice']]+=$lstData['nilai'];
			}
			
			
		

			$rData="";
			$sData="select * from ".$dbname.".keu_tagihanht where ".$where." and tipeinvoice in (select kode from ".$dbname.".keu_5jenistagihan) order by nopo,noinvoice asc";
			$rData=fetchdata($sData);

			$arrExcep=array("upd"=>"upd","pjd"=>"pjd","p22"=>"p22","p21"=>"p21","p23"=>"p23");
			$arrStatKasBank=array("0"=>"Blm Diajukan","9"=>"Pengajuan","1"=>"Disetujui","2"=>"Ditolak");
			$arrpembayaran=array("0"=>"Blm Dibayar","1"=>"Dibayar");
			$arrStatusInv=array("0"=>$_SESSION['lang']['belumposting'],"1"=>$_SESSION['lang']['post']);
			
			
			
			
		  
			if(count($rData)!=0){
				$stream.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
				$stream.="<tr class=rowheader>";
				$stream.="<th rowspan=2 align=center ".$bg.">No.</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['pt']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['unit']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['tipeinvoice']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['noinvoice']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['noinvoicesupplier']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['tanggaldokumen']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['tanggalinvoice']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['nopo']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['namasupplier']."</th>";
				$stream.="<th rowspan=2 align=center ".$bg.">DPP</th>";
				$stream.="<th rowspan=2 align=center ".$bg.">".$_SESSION['lang']['ppn']."</th>";
				$stream.="<th rowspan=2 align=center ".$bg.">".$_SESSION['lang']['pengurang']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['total']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['nofp']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['status']." ".$_SESSION['lang']['noinvoice']."</th>";
				$stream.="<th rowspan=2  align=center ".$bg.">".$_SESSION['lang']['dipostingoleh']."</th>";
				$stream.="<th colspan=4 align=center ".$bg.">".$_SESSION['lang']['kasbank']."</th>";
				$stream.="<th colspan=4 align=center ".$bg.">".$_SESSION['lang']['pembayaran']."</th>";
				$stream.="<th rowspan=2 align=center ".$bg.">".$_SESSION['lang']['selisih']."</th>";
				// if($param['tipe']!='excel'){
					// $stream.="<th rowspan=2  align=center ".$bg." colspan=2>".$_SESSION['lang']['action']."</th>";    
				// }
				
				$stream.="</tr>";    
				$stream.="<tr><th align=center ".$bg.">".$_SESSION['lang']['notransaksi']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['tanggal']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['total']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['status']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['pembayaran']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['novoucher']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['tanggal']."</th>";
				$stream.="<th align=center ".$bg.">".$_SESSION['lang']['dibayar']."</th>
					   </tr></thead><tbody>";   
				#tipe invoice 
				$optJenis=array();
				$sJenis="select * from ".$dbname.".keu_5jenistagihan where status=1";
				$rJenis=fetchData($sJenis);
				foreach($rJenis as $row=>$data){
					if($data['jurnal']==1){
						$optJenis[$data['kode']].="NVM : ".$data['namajenis']."";
					}
					else{
						$optJenis[$data['kode']].="VM : ".$data['namajenis']."";
					}
				}
				$totDpp=0;
				$totPPn=0;
				foreach ($rData as $key => $val){
					// if(substr($optJenis[$val['tipeinvoice']],0,3)=="NVM"){
						// $val['nilaiinvoice']=$val['nilaiinvoice']-$nilPPn[$val['noinvoice']];    
					// }
					$totByr=0;
					if(isset($dibyarkan[$val['noinvoice']])){
						foreach ($dibyarkan[$val['noinvoice']] as $key => $val2) {
							$totByr+=$val2['dibayar'];
						}    
					}

					if ($val['posting']==0) {
						if ($param['statTagihan']=='2' || $param['statTagihan']=='3') {
							continue;
						}
					}
					
					if ($val['posting']==1) {
						if($param['statTagihan']=='2'){##sudah terbayar
							if ($totByr>0) {
								if(number_format((($val['nilaidpp']+$nilPPn[$val['noinvoice']]+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']])-$totByr))>0){##jika selisih > 0 tidak ditampilkan
									continue;
								}
							}else{
								continue;
							}
						}

						if($param['statTagihan']=='3'){##outstanding
							if ($totByr>=0) {
								if(number_format((($val['nilaidpp']+$nilPPn[$val['noinvoice']]+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']])-$totByr))==0){##jika selisih == 0 tidak ditampilkan
									continue;
								}
							}else{
								continue;
							}
						}
					}

					if(!empty($arrExcep[$val['tipeinvoice']])){
						continue;
					}

					// if($val['tipeinvoice']=='ffb'){
						// if(strlen($val['noinvoice'])!=14){
							// if(($val['kodesupplier']=='S201801275') || ($val['kodesupplier']=='S201801460')){
								// $nilPPn[$val['noinvoice']]=0;    
							// }
						// }
					// }

					$cols="";
					if(count($dibyarkan[$val['noinvoice']])!=0){
						$cols="rowspan=".count($dibyarkan[$val['noinvoice']]);
					}

					$sDetnota="select sum(nilaiinvoice*-1) as nildebet from ".$dbname.".keu_notadebet_ht where noinvoice_referensi='".$val['noinvoice']."' group by noinvoice_referensi";
					$rDetnota=fetchdata($sDetnota);

					$pengurang='';
					$pengurang='';

					$no+=1;
				   
					$whr="supplierid='".$val['kodesupplier']."'";
					$optNmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
					$viewDetailData="onclick=viewDetailData2('".$val['noinvoice']."') style=cursor:pointer title='Detail ".$val['noinvoice']."'";
					$stream.="<tr class=rowcontent>";
					$stream.="<td vlign=top ".$cols." >".$no."</td>";
					$stream.="<td vlign=top ".$cols." id=kodeorg_".$key." value='".$val['kodeorg']."'>".$val['kodeorg']."</td>";
					$stream.="<td vlign=top ".$cols." id=unit_".$key." value='".$val['unit']."'>".$val['unit']."</td>";
					$stream.="<td vlign=top ".$cols." id=tipeinv_".$key." value='".$val['tipeinvoice']."'>".$optJenis[$val['tipeinvoice']]."</td>";
					
						$stream.="<td vlign=top ".$cols." id=noinvoice_".$key." ".$viewDetailData." value='".$val['noinvoice']."'>".$val['noinvoice']."</td>";
					// }
					
					$stream.="<td vlign=top ".$cols." id=noinvoicesupplier".$key."  ".$viewDetailData."  value='".$val['noinvoicesupplier']."'>".$val['noinvoicesupplier']."</td>";
					if($param['tipe']!='excel'){
						$stream.="<td vlign=top ".$cols." id=tanggal_".$key."  ".$viewDetailData."  value='".$val['tanggal']."'>".tanggalnormal($val['tanggal'])."</td>";
					}else{
						$stream.="<td vlign=top ".$cols." ".$rowspDt.">".$val['tanggal']."</td>";
					}
					if($param['tipe']!='excel'){
						$stream.="<td vlign=top ".$cols." id=tanggalinv_".$key."  ".$viewDetailData."  value='".$val['tanggalinvoice']."'>".tanggalnormal($val['tanggalinvoice'])."</td>";
					}else{
						$stream.="<td vlign=top ".$cols." ".$rowspDt.">".$val['tanggalinvoice']."</td>";
					}
					$stream.="<td vlign=top ".$cols." id=nopo_".$key."  ".$viewDetailData."  value='".$val['nopo']."'>".$val['nopo']."</td>";
					$stream.="<td vlign=top ".$cols." ".$viewDetailData." >".$optNmSupp[$val['kodesupplier']]."</td>";
					//$stream.="<td>".$val['keterangan']."</td>";".@$noTrans[$val['noinvoice']][0]."
					
					$stream.="<td align=right ".$cols." > ".@hidezerodecimal(($val['nilaidpp']),2)."</td>";
					$stream.="<td align=right ".$cols." > ".@hidezerodecimal($nilPPn[$val['noinvoice']],2)."</td>";
					$stream.="<td align=right ".$cols." > ".@hidezerodecimal(($ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']]),2)."</td>";
					$stream.="<td vlign=top align=right ".$cols." ".$rowspDt."  ".$viewDetailData." > ".@hidezerodecimal(($val['nilaidpp']+$nilPPn[$val['noinvoice']]+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']]),2)."</td>";
					$stream.="<td vlign=top ".$cols." ".$viewDetailData."  >".$val['nofp']."</td>";
					$stream.="<td vlign=top ".$cols." ".$viewDetailData."  >".$arrStatusInv[$val['posting']]."</td>";
					//$stream.="<td vlign=top ".$viewDetailData."  >".@$tglDt[$val['noinvoice']][0]."</td>";
					
					$optNmPosting=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$val['postingby']."'");
					$stream.="<td vlign=top ".$cols." ".$viewDetailData.">".$optNmPosting[$val['postingby']]."</td>";
					
					/*
					if($param['tipe']!='excel'){
						$dtpost="";
						if($val['posting']==0){
							$scek="select * from ".$dbname.".setup_posting where kodeaplikasi='keuangan' and jabatan='".$_SESSION['empl']['kodejabatan']."'";
							$rcek=fetchdata($scek);
							if(count($rcek)==1){
								$dtpost="onclick=postingDatalaporan('".$key."')";
							}   
							$imgdt="images/skyblue/posting.png";
						}else{
							$imgdt="images/skyblue/posted.png";
						}
						$stream.="<td vlign=top ".$cols." ><img src=".$imgdt." class=\"zImgBtn\" ".$dtpost."   title=\"Posting\"></td>";
						$stream.="<td vlign=top ".$cols." ><img src=\"images/skyblue/pdf.jpg\" class=\"zImgBtn\" onclick=\"detailPDF(".$key.",event)\" title=\"Print Data Detail\"></td>";
					}
					*/
					
					// $stream.="<td vlign=top ".$cols." ></td>";
					// $stream.="<td vlign=top ".$cols." ></td>";

					$totsb = 0;
					$jml = count($dibyarkan[$val['noinvoice']]);
					if($jml > 0){
						foreach ($dibyarkan[$val['noinvoice']] as $key => $val2) {
							if($val2['pembayaran']=='1')$totsb += $val2['dibayar'];
							$totByr+=$val2['dibayar'];
						}
						$trtable=0;
						foreach ($dibyarkan[$val['noinvoice']] as $key => $val2) {
							if($trtable > 0){
								$stream.="</tr>";
								$stream.="<tr class=rowcontent>";
							}
							$stream.="<td>".$key."</td>";
							$stream.="<td>".tanggalnormal($val2['tanggalinput'])."</td>";
							$stream.="<td align=right>".hidezerodecimal($val2['dibayar'],2)."</td>";
							$stream.="<td align=right>".$arrStatKasBank[$val2['posting']]."</td>";
							$stream.="<td align=right>".$arrpembayaran[$val2['pembayaran']]."</td>";
							$stream.="<td align=right>".$val2['novoucher']."</td>";
							if($val2['novoucher']!=''){
								$stream.="<td>".tanggalnormal($val2['tanggal'])."</td>";
							}else{
								$stream.="<td></td>";
							}

							if($trtable == 0){
								$stream.="<td rowspan=".$jml." align=right>".hidezerodecimal($totsb,2)."</td>";
							}
							$selisih=$val2['dibayar']-$totsb;
							$stream.="<td align=right>".hidezerodecimal($selisih,2)."</td>";
							$totKas2+=$val2['dibayar'];
							$totByr2+=$totsb;
							$totSel2+=$selisih;
							$trtable++;
						}
						$stream.="</tr>";
					} else{
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="<td>&nbsp;</td>";
						 $stream.="</tr>";
					}
					$totDpp+=($val['nilaidpp']+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$bylain[$val['noinvoice']]);
					$totPPn+=$nilPPn[$val['noinvoice']];
					$totpengurang+=($ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']]);
					$totHutang+=($val['nilaidpp']+$nilPPn[$val['noinvoice']]+$ayatsilang[$val['noinvoice']]+$nilUangMuka[$val['noinvoice']]+$nilpph[$val['noinvoice']]+$rDetnota[0]['nildebet']+$bylain[$val['noinvoice']]);
				}
				$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=10>".$_SESSION['lang']['grnd_total']."</td>";
				$stream.="<td align=right>".hidezerodecimal($totDpp,2)."</td>";
				$stream.="<td align=right>".hidezerodecimal($totPPn,2)."</td>";
				$stream.="<td align=right>".hidezerodecimal($totpengurang,2)."</td>";
				$stream.="<td align=right>".hidezerodecimal($totHutang,2)."</td>";
				$stream.="<td colspan=5>&nbsp;</td>";
				$stream.="<td align=right>".hidezerodecimal($totKas2,2)."</td>";
				$stream.="<td colspan=4>&nbsp;</td>";
				$stream.="<td align=right>".hidezerodecimal($totByr2,2)."</td>";
				$stream.="<td align=right>".hidezerodecimal($totSel2,2)."</td>";
				$stream.="</tr>";
				$stream.="</tbody>";
			} else {
					$stream.="<tr class=rowcontent align=center><td colspan=12>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
			}
			$stream.="</table>";

	
	
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "tagihan_".$param['periode']."_sampai_".$param['period2'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			case'pdf':
				$dompdf = new Dompdf();
				$dompdf->loadHtml($stream);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->tab("Stok",array("Attachment"=>0));
			break;
		}
		
	break;	
	case'getDetail':
		// exit("Error:MASUK");
        $_POST['noinvoice']=$_POST['noinv'];
        #ambil data header
        $sHeader="select * from ".$dbname.".keu_tagihanht where noinvoice='".$_POST['noinvoice']."'";
        $rHeader=fetchdata($sHeader);
        #ambil data detal
        $sDet="select * from ".$dbname.".keu_tagihandt where noinvoice='".$_POST['noinvoice']."'";
		
        $rDet=fetchdata($sDet);
        $optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rHeader[0]['kodesupplier']."'");
        $optSupp2=makeOption($dbname,'log_5supkelompok','supplierid,tipe',"supplierid='".$rHeader[0]['kodesupplier']."'");
        $optJur=makeOption($dbname,'keu_5jenistagihan','kode,jurnal');
        $optJenis=makeOption($dbname,'keu_5jenistagihan','kode,namajenis');
        $arrNoyes=array("0"=>"VM","1"=>"NVM");
        // $tab="<div style='overflow:auto'>";
        //$tab.="<fieldset><legend>".$_POST['noinvoice']."</legend>";
		
		$str = "SELECT * FROM " . $dbname . ".log_5supkelompok where 1=1 and supplierid ='".$rHeader[0]['kodesupplier']."' and tipe ='".$rHeader[0]['jenissupplier']."'"; 
        $res = fetchdata($str);
		$noakun = $res[0]['noakun'];
		
        
        $tab.="<table cellspacing=1 cellpadding=3 border=0>";
        $tab.="<tr><td>".$_SESSION['lang']['noinvoice']."</td><td>:</td><td>".$_POST['noinvoice']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['noinvoicesupplier']."</td><td>:</td><td>".$rHeader[0]['noinvoicesupplier']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['tanggalterima']."</td><td>:</td><td>".tanggalnormal($rHeader[0]['tanggal'])."</td></tr>";
       
		 $tab.="<tr><td>".$_SESSION['lang']['nilaidpp']."</td><td>:</td><td>".hidezerodecimal($rHeader[0]['nilaidpp'],0)."</td></tr>";
		 $tab.="<tr><td>".$_SESSION['lang']['nilaiinvoice']."</td><td>:</td><td>".hidezerodecimal($rHeader[0]['nilaiinvoice'],0)."</td></tr>";
     
         $tab.="<tr><td>".$_SESSION['lang']['nofp']."</td><td>:</td><td>".$rHeader[0]['nofp']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['nopo']."</td><td>:</td><td>".$rHeader[0]['nopo']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['namasupplier']."</td><td>:</td><td>".($optSupp[$rHeader[0]['kodesupplier']]==''?$optSupp2[$rHeader[0]['kodesupplier']]:$optSupp[$rHeader[0]['kodesupplier']])."</td></tr>";
		$tab.="<tr><td>Jenis Asignment</td><td>:</td><td><b>".$rHeader[0]['jenissupplier']."</b></td></tr>";
		$tab.="<tr><td>Nama Akun</td><td>:</td><td><b>".$noakun." - ".getNamaAkun($noakun)."</b></td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>".$arrNoyes[$optJur[$rHeader[0]['tipeinvoice']]]."-".$optJenis[$rHeader[0]['tipeinvoice']]."</td></tr>";
		if ($rHeader[0]['nosj'] != "" || $rHeader[0]['nosj'] != NULL) {
			$tab.="<tr><td>".$_SESSION['lang']['suratjalan']."</td><td>:</td><td>".$rHeader[0]['nosj']."</td></tr>";
		}
        $tab.="</table>";
       
            $tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable ><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th>".$_SESSION['lang']['notransaksi']."</th>";
			$tab.="<th>".$_SESSION['lang']['noaruskas']."</th>";
			$tab.="<th>".$_SESSION['lang']['noakun']."</th>";
            $tab.="<th>".$_SESSION['lang']['namaakun']."</th>";
            $tab.="<th>".$_SESSION['lang']['nilai']."</th>";
            $tab.="<th>".$_SESSION['lang']['kodevhc']."</th>";
            $tab.="<th>".$_SESSION['lang']['adkcip']."</th>";
            $tab.="</tr></thead><tbody>";
             $totDet=0;
             $totSma=0;
			 
			 
			#= ambil data uang muka yang akan ditambajhkan ke dpp detail baris pertama
			foreach($rDet as $row){
				if(substr($row['noakun'],0,3)=='118'){
					if($rHeader[0]['tipeinvoice']=='um'){
						$dtnilaium=$row['nilai'];
					}else{
						$dtnilaium=$row['nilai']*-1;
					}
				}
			}
			
			 $nourut = 0;
            foreach($rDet as $row=>$lstDt){
					$nourut+=1;
					if($nourut==1 and substr($lstDt['noakun'],0,3)!='118'){
					
						$lstDt['nilai']=$lstDt['nilai']+$dtnilaium;
					}
				// if($lstDt['nilai']!=0 and substr($lstDt['noakun'],0,3)!='118'){
					$optNmAkn=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$lstDt['noakun']."'");
					$tab.="<tr class=rowcontent>
					<td>".$lstDt['notransaksi']."</td>
					<td>".$lstDt['noaruskas']."</td>
					<td>".$lstDt['noakun']."</td>";
					$tab.="<td>".$optNmAkn[$lstDt['noakun']]."</td>";
					$tab.="<td align=right>".hidezerodecimal($lstDt['nilai'],0)."</td>";
					$tab.="<td>".$lstDt['kodevhc']."</td>";
					$tab.="<td>".$lstDt['kodeasset']."</td></tr>";
					@$totalinvoice+=$lstDt['nilai'];
				// }
            }
			$tab.="<tr class=rowcontent><td colspan=4>".$_SESSION['lang']['total']." ".$_SESSION['lang']['detail']."</td>";
            $tab.="<td align=right>".hidezerodecimal($totalinvoice,0)."</td>"; #mahe
            $tab.="<td colspan=2>&nbsp;</td></tr>";   
            $tab.="</tbody></table>";
     	$tab.="</fieldset><br>";
		


		$str="select * from ".$dbname.".listfileupload where notransaksi='".$_POST['noinvoice']."'";
		$res=fetchdata($str);
		//$tab.="<fieldset><legend>".$_SESSION['lang']['file']."</legend>";
		$tab.="<div style=clear:both><b>".$_SESSION['lang']['file']."</b></div>";
		$tab.="<table border=0 cellspacing=1 class=sortable cellpadding=5>
			<thead>
			<tr style='font-weight:bold'>
				<th align='center'>No.</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center'>Action</th>
			</tr>
			</thead>
			<tbody id='listfilesview'>";
			
				foreach($res as $key=>$val){
					$no++;
					$tab.="<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.png')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}
					elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}
					elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}
					else
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
					
					$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
						<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
							<a hidden href='".$pathlama.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
							&nbsp";
					$tab."	</td>
					</tr>";
				}	
				
				
		$tambahanfiletbspetani=''; $adapetani='';
		// <img src=images/pdf.jpg class=resicon  caption='PDF'  title='List Petani ".$bar['notransaksi']."' onclick=\"pdf3('".$bar['notransaksi']."');\">
				// cek apakah AP petani
		
		$sDet="select notransaksi from ".$dbname.".kebun_tbskud where notransaksi = '".$rHeader[0]['nopo']."'";
		$rDet=fetchdata($sDet);
		foreach($rDet as $row=>$lstData){
			$adapetani=$lstData['notransaksi'];
		}
		if($adapetani!=''){
			$no++;
			$tambahanfiletbspetani.="<tr class=rowcontent>
				<td align='center'>".$no."</td>
				<td align='center'></td>
				<td align='left'>List Petani</td>
				<td align='center'></td>
				<td align='center'><img src=images/pdf.jpg class=resicon  caption='PDF'  title='List Petani ".$rHeader[0]['nopo']."' onclick=\"pdf3('".$rHeader[0]['nopo']."');\"></td>
			</tr>";
		}		
				
		$tab.=$tambahanfiletbspetani;
		$tab.="</tbody>
		</table>";
		
        $tab.="</fieldset><br />";
		$dtInv=[];
        $sHed="select * from ".$dbname.".keu_tagihanht where nopo='".$rHeader[0]['nopo']."' and nopo!='' and noinvoice!='".$_POST['noinvoice']."'";
        $rHed=fetchdata($sHed);
        
            foreach ($rHed as $key => $val) {
                if($_POST['noinvoice']==$val['noinvoice']){
                    continue;
                }
                $dtInv[$val['noinvoice']]=$val['noinvoice'];
                $dtTgl[$val['noinvoice']]=$val['tanggal'];
                $dtnilaidpp[$val['noinvoice']]=$val['nilaidpp'];
                $dtnilaiinvoice[$val['noinvoice']]=$val['nilaiinvoice'];
                $dtInvSp[$val['noinvoice']]=$val['noinvoicesupplier'];
            }
       
       
            //$tab.="<fieldset><legend>".$_SESSION['lang']['list']." Lalu Invoice PO : ".$rHeader[0]['nopo']." </legend>";
			$tab.="<div style=clear:both><b>Invoice PO sebelumnya : ".$rHeader[0]['nopo']."</b></div>";
			$tab.="<table cellspacing=1 cellpadding=5 border=0 class=sortable>";
            $tab.="<thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<th>".$_SESSION['lang']['noinvoice']."</th>";
            $tab.="<th>".$_SESSION['lang']['noinvoicesupplier']."</th>";
            $tab.="<th>".$_SESSION['lang']['tanggalterima']."</th>";
            $tab.="<th>".$_SESSION['lang']['nilaidpp']."</th>";
            $tab.="<th>".$_SESSION['lang']['nilaiinvoice']."</th></tr></thead><tbody>";
            $totaldetail=0;
            if(count($dtInv)!=0){
                foreach($dtInv as $invDt){
                    $tab.="<tr class=rowcontent>";
						$tab.="<td>".$invDt."</td>";
						$tab.="<td>".$dtInvSp[$invDt]."</td>";
						$tab.="<td>".tanggalnormal($dtTgl[$invDt])."</td>";
						$tab.="<td align=right>".hidezerodecimal($dtnilaidpp[$invDt],0)."</td>";
						$tab.="<td align=right>".hidezerodecimal($dtnilaiinvoice[$invDt],0)."</td>";
					$tab.="</tr>";
                }
            }
            
            $tab."</tbody></table></fieldset>";
        echo $tab;
    break;
		

}



?>
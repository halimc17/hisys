<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$method = checkPostGet('method','');
$tipelaporan = checkPostGet('tipelaporan','');

$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');
$ketstatus = checkPostGet('ketstatus','');
$nopp = checkPostGet('nopp','');
$nopo = checkPostGet('nopo','');
$strategis = checkPostGet('strategis','');
$kodebarang = checkPostGet('kodebarang','');
$tipeperiode = checkPostGet('tipeperiode','');
$purchaser = checkPostGet('purchaser','');
$jenisApp = 'PO';




// echo $nopp._.$nopo;

switch($method){
	case'getunit':
		$optunit="<option value=''>".$_SESSION['lang']['all']."</option>";	
		if($pt!=''){
			if(getUnitByMgrPurc($_SESSION['standard']['userid'])!=''){
				$where=" and kodeorganisasi in (".getUnitByMgrPurc($_SESSION['standard']['userid']).")";
			}
			
			$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' ".$where." order by namaorganisasi";
			$res=fetchdata($str);
			foreach($res as $val){
				$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
			}
		}
		
		echo $optunit;
	break;
	
	case'getlaporan':
		if(tanggalsystem($tgl1) > tanggalsystem($tgl2)){
			exit("Warning : Range Periode masih salah, tanggal awal lebih besar dari pada tanggal akhir.");
		}
		
		$selisihbulan=selisihbulan((substr($tgl1,6,4)."-".substr($tgl1,3,2)),((substr($tgl2,6,4)."-".substr($tgl2,3,2))));
		if($selisihbulan > 5){
			exit("Warning : Range periode maksimal 6 bulan.");
		}
	
		$tab="";
		if($tipelaporan=='html'){
			$border=0;
			$vwidth="cellspacing=1 cellpadding=3";
		}elseif($tipelaporan=='pdf'){
			$border=1;
			$vwidth="width=100% cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		
		## GET COUNT APPROVAL PO/SO
		$countApp = getCountApproval($jenisApp,'');
		
		## GET LIST DATA
		$arrpp=array();
		$arrdata=array();
		$where=$wherepp=$wherepo=$whereprpo=$wherepurchaser="";
		if($unit!=''){
			$where.= " and unit='".$unit."'";
		}
		if($nopp!=''){
			$where.= " and a.nopp like '%".$nopp."%'";
		}
		
		if($pt!=''){
			$where.=" and b.pt='".$pt."'";
			$wherepo.=" and kodeorg='".$pt."'";
		}
		
		if($nopo!=''){
			$str="select nopo from ".$dbname.".log_poht where nopo like '%".$nopo."%' ".$wherepo."";
			$res=fetchdata($str);
			foreach($res as $val){
				$arrlistpo[$val['nopo']]=$val['nopo'];
			}
		}
		
		if($strategis!=''){
			$where.=" and hargalama='".$strategis."'";
		}
		
		if($tipeperiode=='pr'){
			$where.=" and (b.tanggal between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."')";
		}
		
		$where2="";
		if($tipeperiode=='po'){
			$where.=" and a.nopp in (select nopp from ".$dbname.".log_po_vw where (tgledit between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."'))";
			// $where2.=" and b.tglrelease between '".tanggaldb($tgl1)."' and '".tanggaldb($tgl2)."'";
		}
		
		if($purchaser!=''){
			$where.=" and a.nopp in (select nopp from ".$dbname.".log_listverifikasi where karyawanid='".$purchaser."')";
		}
		
		if(getUnitByMgrPurc($_SESSION['standard']['userid'])!=''){
			$where.=" and b.unit in (".getUnitByMgrPurc($_SESSION['standard']['userid']).")";
		}
		// echo $where;
		#= Ori 
		#= Update Abdul
		// $str="select a.nopp,a.kodebarang,b.unit,a.hargasatuan,a.jumlah,a.keteranganubah from ".$dbname.".log_prapodt a 
		// left join ".$dbname.".log_prapoht b on a.nopp=b.nopp where close='2' ".$where." order by b.tanggal desc";
		
		$str="select a.nopp,a.kodebarang,b.unit,a.hargasatuan,a.jumlah,a.keteranganubah,b.close from ".$dbname.".log_prapodt a 
		left join ".$dbname.".log_prapoht b on a.nopp=b.nopp where close='2' and a.status != '3' ".$where." order by b.tanggal desc";
		#end 
		
		$res=fetchdata($str);
		foreach($res as $val){
			$optnmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			$arrpp[$val['nopp']]=$val['nopp'];
			$arrdata[$val['nopp']][$val['kodebarang']]['unit']=$val['unit'];
			$arrdata[$val['nopp']][$val['kodebarang']]['statuspp']=$val['close'];
			$arrdata[$val['nopp']][$val['kodebarang']]['kodebarang']=$val['kodebarang'];
			$arrdata[$val['nopp']][$val['kodebarang']]['namabarang']=$optnmbrg[$val['kodebarang']];
			$arrdata[$val['nopp']][$val['kodebarang']]['hargapp']=($val['hargasatuan']*$val['jumlah']);
			$arrdata[$val['nopp']][$val['kodebarang']]['ketstatus']=$val['keteranganubah'];
		}

		$inarrpp = implode("','", $arrpp);
		$str="select nopp,kodebarang,createdtime,karyawanid from ".$dbname.".log_listverifikasi where nopp in ('".$inarrpp."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$optnmpur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			$arrdata[$val['nopp']][$val['kodebarang']]['tglvrf']=tanggalnormal($val['createdtime']);
			$arrdata[$val['nopp']][$val['kodebarang']]['purchaser']=$optnmpur[$val['karyawanid']];
		}
		
		$arrpo=array();
		if($purchaser != ''){
			$str="select a.nopo,a.nopp,a.kodebarang,a.jumlahpesan, a.hargasatuan, b.tgledit, b.waktucetak,b.kodesupplier, b.purchaser,b.nodph,b.statuspo,b.closed,b.keteranganclose,b.keterangan    
			from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where a.nopp in ('".$inarrpp."') and b.purchaser='".$purchaser."' ".$where2."";
		}else{
			$str="select a.nopo,a.nopp,a.kodebarang,a.jumlahpesan, a.hargasatuan, b.tgledit, b.waktucetak,b.kodesupplier, b.purchaser,b.nodph,b.statuspo,b.closed,b.keteranganclose,b.keterangan    
			from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where a.nopp in ('".$inarrpp."') ".$where2."";
		}
		$res=fetchdata($str);
		foreach($res as $val){
			$optnmsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['kodesupplier']."'");
			
			$arrpo[$val['nopo']]=$val['nopo'];
			$arrdata[$val['nopp']][$val['kodebarang']]['tglrls']=($val['tgledit']=='0000-00-00'?'':tanggalnormal($val['tgledit']));
			$arrdata[$val['nopp']][$val['kodebarang']]['nopo']=$val['nopo'];
			$arrdata[$val['nopp']][$val['kodebarang']]['statuspo']=$val['statuspo'];
			$arrdata[$val['nopp']][$val['kodebarang']]['closed']=$val['closed'];
			$arrdata[$val['nopp']][$val['kodebarang']]['keteranganclose']=$val['keteranganclose'];
			$arrdata[$val['nopp']][$val['kodebarang']]['keterangan']=$val['keterangan'];
			$arrdata[$val['nopp']][$val['kodebarang']]['nodph']=$val['nodph'];
			$arrdata[$val['nopp']][$val['kodebarang']]['hargapo']=($val['jumlahpesan']*$val['hargasatuan']);
			$arrdata[$val['nopp']][$val['kodebarang']]['tglctk']=($val['waktucetak']=='0000-00-00 00:00:00'?'':tanggalnormal($val['waktucetak']));
			$arrdata[$val['nopp']][$val['kodebarang']]['namasupplier']=$optnmsup[$val['kodesupplier']];
			$arrdata[$val['nopp']][$val['kodebarang']]['kodesupplier']=$val['kodesupplier'];
			if($arrdata[$val['nopp']][$val['kodebarang']]['purchaser']==''){
				$optnmpur = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['purchaser']."'");
				$arrdata[$val['nopp']][$val['kodebarang']]['purchaser']=$optnmpur[$val['purchaser']];
			}
		}
		
		$inarrpo = implode("','", $arrpo);
		$arrdatapo=array();
		$str="select notransaksi,level,tanggal from ".$dbname.".approval where notransaksi in ('".$inarrpo."') and jenispersetujuan='".$jenisApp."' and status='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdatapo[$val['notransaksi']][$val['level']]['tglapp']=($val['tanggal']=='0000-00-00 00:00:00'?'':tanggalnormal($val['tanggal']));
		}
		
		## CEK DARI TRANSAKSI DT
		$str="select a.nopp,a.kodebarang,b.tanggal from ".$dbname.".log_transaksidt a left join ".$dbname.".log_transaksiht b on a.notransaksi=b.notransaksi where a.nopo in ('".$inarrpo."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['nopp']][$val['kodebarang']]['tglterima']=($val['tanggal']=='0000-00-00 00:00:00'?'':tanggalnormal($val['tanggal']));
		}
		
		## CEK DARI BA SERVICE
		$str="select a.nopp,a.kodebarang,b.tanggal from ".$dbname.".log_podt a left join ".$dbname.". log_baservis b on a.nopo=b.noso where a.nopo in ('".$inarrpo."') and posting='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['nopp']][$val['kodebarang']]['tglterima']=($val['tanggal']==''?'':tanggalnormal($val['tanggal']));
		}
		
		## CEK DARI NON-INVENTORY
		$str="select a.nopp,a.kodebarang,b.tanggal from ".$dbname.".log_penerimaanpodt a left join ".$dbname.".log_penerimaanpoht b on a.notransaksi=b.notransaksi where a.nopo in ('".$inarrpo."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['nopp']][$val['kodebarang']]['tglterima']=($val['tanggal']=='0000-00-00 00:00:00'?'':tanggalnormal($val['tanggal']));
		}
		
		## CEK DARI NON-INVENTORY
		$str="select nopp,kodebarang,tanggal from ".$dbname.".log_noninventorydt_vw where nopo in ('".$inarrpo."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$arrdata[$val['nopp']][$val['kodebarang']]['tglterima']=($val['tanggal']=='0000-00-00 00:00:00'?'':tanggalnormal($val['tanggal']));
		}
		
		$tab.="<table class=sortable border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='2'>".$_SESSION['lang']['unit']."</th>
				<th rowspan='2'>".$_SESSION['lang']['deskripsi']."</th>
				<th rowspan='2'>".$_SESSION['lang']['nopp']."</th>
				<th rowspan='2'>Status PR/SR</th>
				<th rowspan='2'>Nilai PR/SR</th>
				<th colspan='".(3+$countApp)."'>Waktu Proses PO/SO</th>
				<th rowspan='2'>Outstanding (Hari) PR/SR -> PO/SO</th>
				<th rowspan='2'>".$_SESSION['lang']['nopo']."</th>
				<th rowspan='2'>".$_SESSION['lang']['status']." PO/SO</th>
				<th rowspan='2'>Nilai PO/SO</th>
				<th colspan='2'>Waktu Penerimaan</th>
				<th rowspan='2'>Outstanding (Hari) PO/SO -> GR</th>
				<!--<th rowspan='2'>Harga Efektif PR/SR -> PO/SO</th>-->
				<th rowspan='2'>".$_SESSION['lang']['vendor']."</th>
				<th rowspan='2'>".$_SESSION['lang']['purchaser']."</th>
				<!--<th rowspan='2'>".$_SESSION['lang']['status']."</th>-->
			</tr>
			<tr class=rowheader style='text-align:center'>
				<th>PR/SR Assigned</th>
				<th>PR/SR Proses (RPH)</th>
				<th>Pengajuan PO/SO</th>";
				
				for($i=1;$i<=$countApp;$i++){
					$tab.="<th>Approval ".$i."</th>";
				}
				
				$tab.="<th>PO/SO Release</th>
				<th>Penerimaan Gudang</th>
			<tr>
			</thead>
			<tbody>";
			
			$no=0;
			$tothargapp=0;
			$tothargapo=0;
			$ttprassg=0;
			$ttprajukan=0;
			$ttotpr=0;
			$ttpoapprv=0;
			$ttporeceive=0;
			$ttotpo=0;

			#= Array Status PR
			$arraypp=array('0' => 'Pembuatan PR','1' => 'Selesai Pembuatan PR', '2' => 'Disetujui', '3' => 'Ditolak');
			foreach($arrpp as $key){
				foreach($arrdata[$key] as $key2=>$val2){
					$postatus="";
					if($val2['statuspo']=='0'){
						$postatus="Unrelease";
					}else if($val2['statuspo']=='1'){
						$postatus="Unrelease";
					}else if($val2['statuspo']=='2'){
						if($val2['closed']=='0'){
							$postatus="Release";
						}else{
							if(strpos($val2['keteranganclose'], ",tanggal tutup : ")){
								$postatus = "Become Out Standing";
							}
							if(strpos($val2['keterangan'], ",tanggal tutup : ")){
								$postatus = "Close";
							}
							if(strpos($val2['keteranganclose'], "Tutup By System")){
								$postatus = "Close By System";
							}
						}
					}else if($val2['statuspo']=='3'){
						if($val2['closed']=='0'){
							$postatus="Release";
						}else{
							if(strpos($val2['keteranganclose'], ",tanggal tutup : ")){
								$postatus = "Become Out Standing";
							}
							if(strpos($val2['keterangan'], ",tanggal tutup : ")){
								$postatus = "Close";
							}
							if(strpos($val2['keteranganclose'], "Tutup By System")){
								$postatus = "Close By System";
							}
						}
					}else if($val2['statuspo']=='4'){
						$postatus = "Cancel";
					}

					//Umar
					$rph    = "";
					$query  = "SELECT b.tanggal FROM $dbname.log_permintaanhargadt AS a LEFT JOIN $dbname.log_perintaanhargaht AS b ON b.nomor = a.nomor WHERE a.nopp = '$key' ORDER BY a.nourut DESC LIMIT 1";
					$result = fetchData($query);
					foreach ($result as $keyx => $value) {
						$rph = $value['tanggal'];
					}
					//End Umar
					
					if($nopo!=''){
						if(isset($arrlistpo)){
							if(in_array($val2['nopo'],$arrlistpo)){
								if($tipeperiode=='po'){
									$hasil=check_in_range(tanggaldb($tgl1), tanggaldb($tgl2), tanggaldb($val2['tglctk']));
									if ($hasil=='0') {
										continue;
									}
								}
								$no++;
								$tab.="<tr class=rowcontent style='vertical-align:top'>";
								$tab.="<td align=center>".$no."</td>";
								$tab.="<td>".$val2['unit']."</td>";
								$tab.="<td>".$val2['namabarang']."</td>";
								// $tab.="<td>".$key."</td>";
								$tab.="<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$key."','".$val2['kodebarang']."',event)\">".$key."</td>";

								
								
								$tab.="<td align=right>".hidezerodecimal($val2['hargapp'],2)."</td>";
								$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglvrf']."</td>";
								$tab.="<td style='min-width:70px;text-align:center'>".tanggalnormal($rph)."</td>";
								$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglrls']."</td>";
								for($i=1;$i<=$countApp;$i++){
									$tab.="<td style='min-width:70px;text-align:center'>".$arrdatapo[$val2['nopo']][$i]['tglapp']."</td>";
									$out1 = "";
									if($i==$countApp && $arrdatapo[$val2['nopo']][$i]['tglapp']!=''){
										$out1 = selisitgl(tanggalsystem($arrdatapo[$val2['nopo']][$i]['tglapp']),tanggalsystem($val2['tglvrf']));
										if($out1 < 0){
											$out1 = "(".abs($out1).")";
										}
									}
								}
								$tab.="<td align=center>".$out1."</td>";
								// $tab.="<td align=center>".$val2['nopo']."</td>";
								$tab.="<td align=left style='cursor:pointer;color:blue' onclick=\"previewlinkpemenang('".$val2['nodph']."', '".$val2['kodesupplier']."', 'Detail Riwayat Perbandingan Harga' ,event)\">".$val2['nopo']."</td>";
								$tab.="<td align=center>".$postatus."</td>";
								$tab.="<td align=right>".hidezerodecimal($val2['hargapo'],2)."</td>";
								$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglctk']."</td>";
								$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglterima']."</td>";
								
								$out2="";
								if($val2['tglterima']!=''){
									$out2 = selisitgl(tanggalsystem($val2['tglterima']),tanggalsystem($val2['tglctk']));
									if($out2 < 0){
										$out2 = "(".abs($out2).")";
									}
								}
								$tab.="<td align=center>".$out2."</td>";
							
								$slhharga = $val2['hargapp'] - $val2['hargapo'];
								$slhharga2=$slhharga;
								if($slhharga < 0){
									$slhharga = "(".hidezerodecimal(abs($slhharga),2).")";
								}else{
									$slhharga = hidezerodecimal($slhharga,2);
								}
								$tab.="<td align=right>".$slhharga."</td>";
								$tab.="<td>".$val2['namasupplier']."</td>";
								$tab.="<td>".$val2['purchaser']."</td>";
								if($tipelaporan=='html'){
									// $tab.="<td>
									// 	<label id='tdketstatus_".$key."_".$val2['kodebarang']."'>".$val2['ketstatus']."</label>
									// 	<br>
									// 	<div contentEditable='true' style='width: 200px;'>
									// 		<textarea id='ketstatus_".$key."_".$val2['kodebarang']."'></textarea><br>
									// 	<center><img title='Simpan' class='zImgBtn' style='vertical-align:top;padding-top:10px' onclick=\"simpanstatus('".$key."','".$val2['kodebarang']."')\" src='images/save.png'></center>
									// 	</div>
									// </td>";
									
								}else{
									// $tab.="<td>".$val2['ketstatus']."</td>";							
								}
								$tab.="</tr>";
								
								## GET TOTAL 
								$tothargapp+=$val2['hargapp'];
								$tothargapo+=$val2['hargapo'];
								$totslhharga+=$slhharga2;
								if($val2['tglvrf']!=''){
									$ttprassg+=1;
								}
								if($val2['tglrls']!=''){
									$ttprajukan+=1;
								}
								if($val2['tglctk']!=''){
									$ttpoapprv+=1;
								}
								if($val2['tglterima']!=''){
									$ttporeceive+=1;
								}
							}
						}
					} else {
						if($tipeperiode=='po'){
							$hasil=check_in_range(tanggaldb($tgl1), tanggaldb($tgl2), tanggaldb($val2['tglctk']));
							if ($hasil=='0') {
								continue;
							}
						}
						$no++;
						$tab.="<tr class=rowcontent style='vertical-align:top'>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td>".$val2['unit']."</td>";
						$tab.="<td>".$val2['namabarang']."</td>";
						// $tab.="<td>".$key."</td>";
						$tab.="<td align=center style='color:blue;cursor:pointer' onclick=\"previewlinkdt('".$key."','".$val2['kodebarang']."',event)\">".$key."</td>";					
						$tab.="<td align=center>".$arraypp[$val2['statuspp']]."</td>";
						$tab.="<td align=right>".hidezerodecimal($val2['hargapp'],2)."</td>";
						$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglvrf']."</td>";
						$tab.="<td style='min-width:70px;text-align:center'>".tanggalnormal($rph)."</td>";
						$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglrls']."</td>";
						for($i=1;$i<=$countApp;$i++){
							$tab.="<td style='min-width:70px;text-align:center'>".$arrdatapo[$val2['nopo']][$i]['tglapp']."</td>";
							$out1 = "";
							if($i==$countApp && $arrdatapo[$val2['nopo']][$i]['tglapp']!=''){
								$out1 = selisitgl(tanggalsystem($arrdatapo[$val2['nopo']][$i]['tglapp']),tanggalsystem($val2['tglvrf']));
								if($out1 < 0){
									$out1 = "(".abs($out1).")";
								}
							}
						}
						$tab.="<td align=center>".$out1."</td>";
						// $tab.="<td align=center>".$val2['nopo']."</td>";
						$tab.="<td align=left style='cursor:pointer;color:blue' onclick=\"previewlinkpemenang('".$val2['nodph']."', '".$val2['kodesupplier']."', 'Detail Riwayat Perbandingan Harga' ,event)\">".$val2['nopo']."</td>";
						$tab.="<td align=center>".$postatus."</td>";
						$tab.="<td align=right>".hidezerodecimal($val2['hargapo'],2)."</td>";
						$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglctk']."</td>";
						$tab.="<td style='min-width:70px;text-align:center'>".$val2['tglterima']."</td>";
						
						$out2="";
						if($val2['tglterima']!=''){
							$out2 = selisitgl(tanggalsystem($val2['tglterima']),tanggalsystem($val2['tglctk']));
							if($out2 < 0){
								$out2 = "(".abs($out2).")";
							}
						}
						$tab.="<td align=center>".$out2."</td>";
					
						$slhharga = $val2['hargapp'] - $val2['hargapo'];
						$slhharga2 = $slhharga;
						if($slhharga < 0){
							$slhharga = "(".hidezerodecimal(abs($slhharga),2).")";
						}else{
							$slhharga = hidezerodecimal($slhharga,2);
						}
						// $tab.="<td align=right>".$slhharga."</td>";
						$tab.="<td>".$val2['namasupplier']."</td>";
						$tab.="<td>".$val2['purchaser']."</td>";
						if($tipelaporan=='html'){
							// $tab.="<td>
							// 	<label id='tdketstatus_".$key."_".$val2['kodebarang']."'>".$val2['ketstatus']."</label>
							// 	<div style=clear:both;></div>
							// 	<div contentEditable='true' style='width: 200px;'>
							// 		<textarea id='ketstatus_".$key."_".$val2['kodebarang']."'></textarea>
							// 		<div style=clear:both;></div>									
							// 		<center><img title='Simpan' class='zImgBtn' style='vertical-align:top;padding-top:10px' onclick=\"simpanstatus('".$key."','".$val2['kodebarang']."')\" src='images/save.png'></center>
							// 	</div>
							// </td>";
							
						}else{
							// $tab.="<td>".$val2['ketstatus']."</td>";							
						}
						$tab.="</tr>";
						
						## GET TOTAL 
						$tothargapp+=$val2['hargapp'];
						$tothargapo+=$val2['hargapo'];
						@$totslhharga+=@$slhharga2;
						
						if($val2['tglvrf']!=''){
							$ttprassg+=1;
						}
						if($val2['tglrls']!=''){
							$ttprajukan+=1;
						}
						if($val2['tglctk']!=''){
							$ttpoapprv+=1;
						}
						if($val2['tglterima']!=''){
							$ttporeceive+=1;
						}
					}
				}
			}
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=4 align=center></td>
				<td align=center>Grand Total Value</td>
				<td align=right>".hidezerodecimal($tothargapp,2)."</td>
				<td colspan='".(4+$countApp)."' align=center></td>
				<td align=center colspan=2>Grand Total Value</td>
				<td align=right>".hidezerodecimal($tothargapo,2)."</td>
				<td align=center></td>
				<!--<td colspan=2 align=center>Grand Total Value</td>
				<td align=right>".hidezerodecimal($totslhharga,2)."</td>-->
				<td colspan=4 align=center></td>
			</tr>";
			
		if($purchaser!=""){
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan='".(18+$countApp)."' height=10px align=center style='background-color:green'></td>
			</tr>";
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=3 align=center></td>
				<td align=center>PR/SR Assigned </td>
				<td align=right>".$ttprassg."</td>
				<td colspan='".(3+$countApp)."' align=center></td>
				<td align=center colspan=2>PO/SO Approved</td>
				<td align=right>".$ttpoapprv."</td>
				<td align=center></td>
				<td colspan=6 align=center></td>
			</tr>";
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=3 align=center></td>
				<td align=center>PO/SO Diajukan</td>
				<td align=right>".$ttprajukan."</td>
				<td colspan='".(3+$countApp)."' align=center></td>
				<td align=center colspan=2>PO/SO Received</td>
				<td align=right>".$ttporeceive."</td>
				<td align=center></td>
				<td colspan=6 align=center></td>
			</tr>";
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=3 align=center></td>
				<td align=center>Outstanding PR/SR</td>
				<td align=right>".($ttprassg - $ttprajukan)."</td>
				<td colspan='".(3+$countApp)."' align=center></td>
				<td align=center colspan=2>Outstanding PO/SO </td>
				<td align=right>".($ttpoapprv - $ttporeceive)."</td>
				<td align=center></td>
				<td colspan=6 align=center></td>
			</tr>";
		}
			
		$tab.="</tbody>
		</table>";
		
		if($tipelaporan=='html'){
			echo $tab;
		}elseif($tipelaporan=='pdf'){
			$arrHead = setheadreport('',$kebun);
			$path=$arrHead['logo'];
			$header="<div>
				<table cellspacing=0 border=0 width=100% align=center>
					<tr>
						<td rowspan=3 style='font-weight:bold;width:100px'><img src='".$path."' height='80' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table>
			<hr>
			<table cellspacing=0 border=0 width=100% style='text-align:center'>
				<tr>
					<td style=font-weight:bold;>REKAPITULASI KONTRAKTOR ANGKUTAN TBS</td>
				</tr>
				<tr>
					<td style=font-weight:bold;>MULAI TANGGAL : ".$tgl1." s/d ".$tgl2."</td>
				</tr>
			</table>";
			
			$footer="<br><table cellspacing=0 border=0 width=100% style='font-weight:bold;text-align:center'>
				<tr>
					<td>Disetujui Oleh</td>
					<td>Diketahui Oleh</td>
					<td>Diperiksa Oleh</td>
					<td>Dibuat Oleh</td>
				</tr>
				<tr>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
					<td style='padding-top:60px'><hr style='border:0.5px solid gray;width:100px'></td>
				</tr>
			</table>";
			
			$hasil=$header;
			$hasil.=$tab;
			$hasil.=$footer;
			$dompdf = new Dompdf();
			$dompdf->loadHtml($hasil);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$dompdf->stream("MonitoringPO-SO_".$tgl1."-".$tgl2."_".$pt."", array("Attachment" => false));
		}else{
			$titlelaporan="MonitoringPO-SO_".$tgl1."-".$tgl2."_".$pt;
			if($handle = opendir('tempExcel')){
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && $file != "index.html"){
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
			if(!fwrite($handle, $tab)){
				echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$titlelaporan.".xls';
					</script>";
			}
			closedir($handle); 
		}
	break;
	
	case'simpanstatus':
		try {
			$owlPDO->beginTransaction();
			
			if($ketstatus==''){
				throw new PDOException("Status harus diisi.");
			}
			
			$nama=$_SESSION['empl']['name'];
			$str="select keteranganubah from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
			$res=fetchdata($str);
			$hasil=$res[0]['keteranganubah'];
			
			if($hasil==''){
				$value="- ".$nama." : ".$ketstatus;
			}else{
				$value=$hasil."<br>- ".$nama." : ".$ketstatus;
			}
		
			$str="update ".$dbname.".log_prapodt set keteranganubah='".$value."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
			$owlPDO->exec($str);
			
			## CREATE NOTIFICATION
			$msgdt = "Penambahan status pada Laporan Monitoring PO/SO terkait PR/SR dengan No ".$nopp." dari ".$_SESSION['empl']['name']." => ".$ketstatus;
			createnotif($nopp,'MPO',$msgdt,$_SESSION['standard']['userid'],date('Y-m-d H:i:s'));
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning : ".addslashes($e->getMessage());
		}
	break;
	
	case'loadstatus':
		$hasil="";
		$str="select keteranganubah from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$res=fetchdata($str);
		$hasil=$res[0]['keteranganubah'];
		
		echo $hasil;
	break;
}

function check_in_range($start_date, $end_date, $date_from_user) {
	$hasil="";
	
	// Convert to timestamp
	$start = strtotime($start_date);
	$end = strtotime($end_date);
	$check = strtotime($date_from_user);
	
  
	if (($check >= $start) && ($check <= $end)){
		$hasil=1;
	}else{
		$hasil=0;
	}
	
	return $hasil;
}
?>

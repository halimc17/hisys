 <?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$type=checkPostGet('type','');

$unit=checkPostGet('unit','');
$afdeling=checkPostGet('afdeling','');
$tglawal=checkPostGet('tglawal','');
$tglakhir=checkPostGet('tglakhir','');
$nobkm=checkPostGet('nobkm','');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

switch($proses){
	case'getafdeling':
		if($unit==''){
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and 1=1";
			}else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN')";
				$whr2 = " and 1=1";
			}else{
				$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
				$whr2 = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
			}
			//GET AFDELING
			$str = "select * from ".$dbname.".organisasi where tipe = 'AFDELING' ".$whr2." order by namaorganisasi asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			} 
		}else{
			//GET AFDELING
			$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
			$str = "select * from ".$dbname.".organisasi where tipe = 'AFDELING' and induk='".$unit."' order by namaorganisasi asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			} 
		}
		
		echo $optAfd;
	break;
	case'preview':
		if($type=='html'){
			$border = 0;
		}else{
			$border = 1;
		}
	
		$tab.="<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>
			<thead class=rowheader>
			<tr>
				<th style='text-align:center'>No.</th>
				<th style='text-align:center'>No. BKM</th>
				<th style='text-align:center'>".$_SESSION['lang']['notransaksi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['tanggal']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['kodekegiatan']."</th>    
				<th style='text-align:center'>".$_SESSION['lang']['namakegiatan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['satuan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['blok']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['nik2']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</th>
				<th style='text-align:center'>Upah</th>
				<th style='text-align:center'>".$_SESSION['lang']['premi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['prestasi']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['materialcode']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['materialname']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['satuan']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['jumlah']."</th>
			</tr>
			</thead>
			<tbody>";
			
		if($unit=='')
		{
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
			{
				if($afdeling=='')
				{
					$whr = '';
				}
				else
				{
					$whr = "and b.kodeorg like '".$afdeling."%'";
				}
			}
			else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL')
			{
				if($afdeling=='')
				{
					$whr = "and left(b.kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN')";
					$whr = '';
				}
				else
				{
					$whr = "and b.kodeorg like '".$afdeling."%'";
				}
			}
			else
			{
				$whr = "and b.kodeorg like '".$afdeling."%'";
			}
		}
		else
		{
			if($afdeling=='')
			{
				$whr = "and left(b.kodeorg,4) = '".$unit."'";
			}
			else
			{
				$whr = "and b.kodeorg like '".$afdeling."%'";
			}
		}
		
		//GET KARYAWAN PEMELIHARAAN
		$str = "select a.*, b.nobkm from ".$dbname.".kebun_kehadiran_vw a 
		left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi
		where a.tanggal between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir)."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listkaryawan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['karyawanid'];
			$listupah[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] += $bar['umr'];
			$listhk[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] += $bar['umr'];
			$listpremi[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['insentif'];
			$hasilkerja[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['karyawanid']] = $bar['hasilkerja'];
		}
		
		$str = "select b.hasilkerja, a.nobkm, a.notransaksi, a.tanggal, b.kodekegiatan, b.kodeorg, b.nik, b.upahpremi, b.upahpremilebihbasis from ".$dbname.".kebun_aktifitas a 
				left join ".$dbname.".kebun_prestasi b on a.notransaksi = b.notransaksi
				where (a.nobkm like '%".trim($nobkm)."%' or a.notransaksi like '%".trim($nobkm)."%') and (a.tanggal between ".tanggalsystem($tglawal)." and ".tanggalsystem($tglakhir).") and b.kodeorg!='' ".$whr." order by a.tanggal asc, a.nobkm asc, a.tipetransaksi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$countNik=0;
		while($bar=$res->fetch()){
			$jenistrans[$bar['notransaksi']]=array('tipetransaksi'=>$bar['tipetransaksi'],'tipe'=>$bar['tipe']);
			
			//GET MATERIAL
			$str2 = "select * from ".$dbname.".kebun_pakaimaterial where notransaksi = '".$bar['notransaksi']."' and kodekegiatan='".$bar['kodekegiatan']."' and kodeorg='".$bar['kodeorg']."'";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			while($bar2=$res2->fetch()){
				$listmat[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar2['kodebarang']] = $bar2['kodebarang'];
				$listmatqty[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar2['kodebarang']] = $bar2['kwantitas'];
			}
			
			$listbkm[$bar['nobkm']]['nobkm'] = $bar['nobkm'];
			$listtrk[$bar['nobkm']][$bar['notransaksi']]=$bar['tanggal'];
			$listkegiatan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']] = $bar['kodekegiatan'];
			$listblok[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']] = $bar['kodeorg'];
						
			if($bar['nik']!='-'){
				$listkaryawan[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = $bar['nik'];
				$listhk[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = 0;
				$listpremi[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = ($bar['upahpremi'] + $bar['upahpremilebihbasis']);
				$hasilkerja[$bar['nobkm']][$bar['notransaksi']][$bar['kodekegiatan']][$bar['kodeorg']][$bar['nik']] = $bar['hasilkerja'];
			}
		}
		
			
			
		if(isset($listbkm))
		foreach($listbkm as $key=>$val){
			
			if(isset($listtrk[$key]))
			foreach($listtrk[$key] as $trk=>$tanggal){
				$arrplus[$key]+=1;
			
				if(isset($listkegiatan[$key][$trk]))
				foreach($listkegiatan[$key][$trk] as $kegiatan){
					
					if(isset($listblok[$key][$trk][$kegiatan]))
					foreach($listblok[$key][$trk][$kegiatan] as $blok){
						$jlhkaryawan=0;
						$jlhmaterial=0;
						
						if(isset($listkaryawan[$key][$trk][$kegiatan][$blok]))
						foreach($listkaryawan[$key][$trk][$kegiatan][$blok] as $karyawanid){
							$listkaryawan2[$key][$trk][$kegiatan][$blok][] = $karyawanid;
						}
						
						if(isset($listmat[$key][$trk][$kegiatan][$blok]))
						foreach($listmat[$key][$trk][$kegiatan][$blok] as $material){
							$listmaterial2[$key][$trk][$kegiatan][$blok][] = $material;
						}
						
						$jlhkaryawan=@count($listkaryawan[$key][$trk][$kegiatan][$blok]);
						$jlhmaterial=@count($listmat[$key][$trk][$kegiatan][$blok]);
						
						if($jlhkaryawan > $jlhmaterial){
							$arrs[$key]+=$jlhkaryawan;
							$arrs1[$key][$trk]+=$jlhkaryawan;
							$arrs2[$key][$trk][$kegiatan]+=$jlhkaryawan;
							$arrs3[$key][$trk][$kegiatan][$blok]+=$jlhkaryawan;
						}else{
							$arrs[$key]+=$jlhmaterial;
							$arrs1[$key][$trk]+=$jlhmaterial;
							$arrs2[$key][$trk][$kegiatan]+=$jlhmaterial;
							$arrs3[$key][$trk][$kegiatan][$blok]+=$jlhmaterial;
						}
					}
				}
			}
		}
		
		$no=0;
		if(isset($listbkm))
		foreach($listbkm as $key=>$val){
			$no++;
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td rowspan='".($arrs[$key]+$arrplus[$key])."' style='text-align:right;vertical-align:top'>".$no."</td>";
			$tab.="<td rowspan='".($arrs[$key]+$arrplus[$key])."' style='vertical-align:top'>".$key."</td>";
			
			$no1=0;
			foreach($listtrk[$key] as $trk=>$tanggal){
				$no1++;
				if($no1 > 1){
					$tab.="</tr><tr class=rowcontent>";
				}
				
				$click=" onclick=showupload('".$trk."')";
				$tab.="<td rowspan='".$arrs1[$key][$trk]."' style='vertical-align:top;cursor:pointer;color:blue;' ".$click.">".$trk."</td>";
				$tab.="<td rowspan='".$arrs1[$key][$trk]."' style='text-align:center;width:65px;vertical-align:top'>".tanggalnormal($tanggal)."</td>";
				
				if(isset($listkegiatan[$key][$trk]))
				$no2=0;
				foreach($listkegiatan[$key][$trk] as $kegiatan){
					$no2++;
					$kodekegiatan=($kegiatan==''||$kegiatan==0?'611010101':$kegiatan);
					$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$kodekegiatan."'");
					$optSatKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$kodekegiatan."'");
					if($no2 > 1){
						$tab.="</tr><tr class=rowcontent>";
					}
					$tab.="<td rowspan='".$arrs2[$key][$trk][$kegiatan]."' style='vertical-align:top'>".$kodekegiatan."</td>";					
					$tab.="<td rowspan='".$arrs2[$key][$trk][$kegiatan]."' style='vertical-align:top'>".$optNamaKegiatan[$kodekegiatan]."</td>";
					$tab.="<td rowspan='".$arrs2[$key][$trk][$kegiatan]."' style='vertical-align:top'>".$optSatKegiatan[$kodekegiatan]."</td>";
					
					if(isset($listblok[$key][$trk][$kegiatan]))
					$no3=0;
					foreach($listblok[$key][$trk][$kegiatan] as $blok){
						$no3++;
						if($no3 > 1){
							$tab.="</tr><tr class=rowcontent>";
						}
						$tab.="<td rowspan='".$arrs3[$key][$trk][$kegiatan][$blok]."' style='vertical-align:top'> ".getNamaOrg($blok)." </td>";
						
						$jlhmtr=@count($listmaterial2[$key][$trk][$kegiatan][$blok]);
						$jlhkry=@count($listkaryawan2[$key][$trk][$kegiatan][$blok]);
						
						if($jlhmtr > $jlhkry){
							if(isset($listmaterial2[$key][$trk][$kegiatan][$blok]))
							$no4=0;
							foreach($listmaterial2[$key][$trk][$kegiatan][$blok] as $keyx=>$valx){
								$no4++;
								$karyawanid=$listkaryawan2[$key][$trk][$kegiatan][$blok][$keyx];
								$hk=$listhk[$key][$trk][$kegiatan][$blok][$karyawanid];
								$premi=$listpremi[$key][$trk][$kegiatan][$blok][$karyawanid];
								$prestasi=$hasilkerja[$key][$trk][$kegiatan][$blok][$karyawanid];
								$kodebarang=$valx;
								$jlhqty=$listmatqty[$key][$trk][$kegiatan][$blok][$kodebarang];
								$optNik = makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karyawanid."'");
								$optNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
								$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
								$optSatuanBarang = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodebarang."'");
								if($no4 > 1){
									$tab.="</tr><tr class=rowcontent>";
								}
								$tab.="<td style='vertical-align:top'>".$optNik[$karyawanid]."</td>";
								$tab.="<td style='vertical-align:top'>".$optNama[$karyawanid]."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($hk==''?'':number_format($hk))."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($premi==''?'':number_format($premi))."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($prestasi==''?'':number_format($prestasi,2))."</td>";
								$tab.="<td style='vertical-align:top'>".$kodebarang."</td>";
								$tab.="<td style='vertical-align:top'>".$optNamaBarang[$kodebarang]."</td>";
								$tab.="<td style='vertical-align:top'>".$optSatuanBarang[$kodebarang]."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($jlhqty==''?'':hidezerodecimal($jlhqty,5))."</td>";
								
								$totalpremi[$key][$trk] += $premi;
								$totalhk[$key][$trk] += $hk;
								$totalprestasi[$key][$trk] += $prestasi;
							}
						}else{
							if(isset($listkaryawan2[$key][$trk][$kegiatan][$blok]))
							$no4=0;
							foreach($listkaryawan2[$key][$trk][$kegiatan][$blok] as $keyx=>$valx){
								$no4++;
								$karyawanid=$valx;
								$hk=$listhk[$key][$trk][$kegiatan][$blok][$karyawanid];
								$premi=$listpremi[$key][$trk][$kegiatan][$blok][$karyawanid];
								$prestasi=$hasilkerja[$key][$trk][$kegiatan][$blok][$karyawanid];
								$kodebarang=$listmaterial2[$key][$trk][$kegiatan][$blok][$keyx];
								$jlhqty=$listmatqty[$key][$trk][$kegiatan][$blok][$kodebarang];
								$optNik = makeOption($dbname,'datakaryawan','karyawanid,nik',"karyawanid='".$karyawanid."'");
								$optNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
								$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
								$optSatuanBarang = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodebarang."'");
								if($no4 > 1){
									$tab.="</tr><tr class=rowcontent>";
								}
								$tab.="<td style='vertical-align:top'>".$optNik[$karyawanid]."</td>";
								$tab.="<td style='vertical-align:top'>".$optNama[$karyawanid]."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($hk==''?'':number_format($hk))."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($premi==''?'':number_format($premi))."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($prestasi==''?'':number_format($prestasi,2))."</td>";
								$tab.="<td style='vertical-align:top'>".$kodebarang."</td>";
								$tab.="<td style='vertical-align:top'>".$optNamaBarang[$kodebarang]."</td>";
								$tab.="<td style='vertical-align:top'>".$optSatuanBarang[$kodebarang]."</td>";
								$tab.="<td style='vertical-align:top;text-align:right'>".($jlhqty==''?'':hidezerodecimal($jlhqty,5))."</td>";
								
								$totalpremi[$key][$trk] += $premi;
								$totalhk[$key][$trk] += $hk;
								$totalprestasi[$key][$trk] += $prestasi;
							}
						}					
					}
				}
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=8 style='vertical-align:top;font-weight:bold'>Total ".$trk."</td>";
				$tab.="<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalhk[$key][$trk])."</td>";
				$tab.="<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalpremi[$key][$trk])."</td>";
				$tab.="<td style='vertical-align:top;text-align:right;font-weight:bold'>".number_format($totalprestasi[$key][$trk],2)."</td>";
				$tab.="<td style='vertical-align:top'></td>";
				$tab.="<td style='vertical-align:top'></td>";
				$tab.="<td style='vertical-align:top'></td>";
				$tab.="<td style='vertical-align:top'></td>";
				$tab.="</tr>";
			}
		}
		
		$tab.="</tbody>
		</table>";
			
		if($type=='html')
		{
			echo $tab;
		}
		else
		{
			$tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="REPORT_BKM_".date('m-d-Y');
			
			if(strlen($tab)>0)
			{
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
				gzwrite($gztralala, $tab);
				gzclose($gztralala);
				echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls.gz';
					</script>"; 
			}
		}
	break;
}
?>
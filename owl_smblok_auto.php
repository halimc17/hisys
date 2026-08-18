<?
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

// try {
// $owlPDORPT->beginTransaction();
	
	function seminggulalu($tgl){
		#membuat tanggal kemarin dari parameter kiriman
		#$tgl format : 2015-12-25;
		$tgl=str_replace('-','',$tgl);
		$newdate = strtotime('- 10 day',strtotime($tgl));
		$newdate = date('Y-m-d', $newdate);
		return $newdate;
	}

	$tglhi = date("Y-m-d");
	$blnini = date("Y-m");
	$blnini = seminggulalu($tglhi);
	
	#$where=" and substr(tanggal,1,7)>='".$blnini."' and tanggal < '".$tglhi."'";
	$where=" and tanggal >='".$blnini."' and tanggal < '".$tglhi."'";
	$where.=" and substr(tanggal,1,7)>='2021-01'";
	
	##== INSERT TO OWL_SMBLOK ==##
	$str = "select * from ".$dbnamerpt.".setup_blok";
	$res = fetchdatarpt($str);
	foreach($res as $val){
		$data[$val['kodeorg']]=$val['kodeorg'];
	}
	
	$str = "select sum(jjgpanen) as jjgpanen,sum(luaspanen) as luaspanen, sum(jjgafkir) as jjgafkir, blok, substr(tanggal,1,7) as periode, tanggal from ".$dbnamerpt.".kebun_rekappnn where 1=1 ".$where." and posting='1' group by blok, tanggal";
	$res = fetchdatarpt($str);
	foreach($res as $val){
		$periode[$val['periode']][$val['tanggal']]=$val['tanggal'];
		$lstperiode[$val['blok']][$val['periode']][$val['tanggal']]=$val['tanggal'];
		$panen[$val['blok']][$val['tanggal']]+=$val['jjgpanen'];
		$hapanen[$val['blok']][$val['tanggal']]+=$val['luaspanen'];
		$afkir[$val['blok']][$val['tanggal']]+=$val['jjgafkir'];
	}
	
	$str = "select sum(jjg) as jjg, sum(kgwb) as kgwb, blok, substr(tanggal,1,7) as periode, tanggal from ".$dbnamerpt.".kebun_spb_vw where 1=1 ".$where." and posting='1' group by blok, tanggal";
	$res = fetchdatarpt($str);
	foreach($res as $val){
		$periode[$val['periode']][$val['tanggal']]=$val['tanggal'];
		$lstperiode[$val['blok']][$val['periode']][$val['tanggal']]=$val['tanggal'];
		$kirim[$val['blok']][$val['tanggal']]+=$val['jjg'];
		$kgpks[$val['blok']][$val['tanggal']]+=$val['kgwb'];
	}
	
	foreach($data as $blok){
		foreach($periode as $prd => $v1){
			$str = "delete from ".$dbname.".owl_smblok where blok='".$blok."' and periode='".$prd."'";
			$owlPDO->exec($str);
			foreach($v1 as $tgl){
				if($lstperiode[$blok][$prd][$tgl]!=''){
					$jjgkirim=$jjgpanen=$totalkgx=$jjgbgtx=$kgbgtx=$totaljjgx=0;
					$str = "select sum(jjgpanen-jjgafkir) as jjgpanen from ".$dbnamerpt.".kebun_rekappnn where blok='".$blok."' and substr(tanggal,1,7)<='".$prd."' and tanggal <= '".$tgl."' and substr(tanggal,1,7)>='2021-01'";
					$res = fetchdatarpt($str)[0];
					$jjgpanen = $res['jjgpanen'];
					
					$sql = "select sum(jjg) as jjgkirim from ".$dbnamerpt.".kebun_spb_vw where blok='".$blok."' and substr(tanggal,1,7)<='".$prd."' and tanggal <= '".$tgl."' and substr(tanggal,1,7)>='2021-01'";
					$req = fetchdatarpt($sql)[0];
					$jjgkirim = $req['jjgkirim'];
					
					$bulan = substr($prd,-2);
					$tahun = substr($prd,0,4);
					
					$sqe = "select sum(jjg".$bulan.") as jjgbgt, sum(kg".$bulan.") as kgbgt, sum(totaljjg) as totaljjg, sum(totalkg) as totalkg from ".$dbnamerpt.".bgt_produksi_kebun where kodeblok='".$blok."' and tahunbudget='".$tahun."'";
					$rer = fetchdatarpt($sqe)[0];
					$jjgbgtx  = $rer['jjgbgt'];
					$kgbgtx   = $rer['kgbgt'];
					$totaljjgx= $rer['totaljjg'];
					$totalkgx = $rer['totalkg'];
					
					if(is_null($jjgbgtx)){$jjgbgtx=0;}
					if(is_null($kgbgtx)){$kgbgtx=0;}
					if(is_null($totaljjgx)){$totaljjgx=0;}
					if(is_null($totalkgx)){$totalkgx=0;}
					if(is_null($kirim[$blok][$tgl])){$kirim[$blok][$tgl]=0;}
					if(is_null($hapanen[$blok][$tgl])){$hapanen[$blok][$tgl]=0;}
					if(is_null($panen[$blok][$tgl])){$panen[$blok][$tgl]=0;}
					if(is_null($afkir[$blok][$tgl])){$afkir[$blok][$tgl]=0;}
					if(is_null($kgpks[$blok][$tgl])){$kgpks[$blok][$tgl]=0;}
					$jjgrestan=$jjgpanen-$jjgkirim;
					if($jjgrestan<0){$jjgrestan=0;}
					
					$data = array(
						'kebun'        => substr($blok,0,4),
						'divisi'       => substr($blok,0,6),
						'blok'         => $blok,
						'namablok'     => getNamaOrg($blok),
						'status'       => getBlokHist($blok,$prd,'statusblok'),
						'luas'         => getBlokHist($blok,$prd,'luasareaproduktif'),
						'pokok'        => getBlokHist($blok,$prd,'jumlahpokok'),
						'topografi'    => getBlokHist($blok,$prd,'topografi'),
						'jenisbibit'   => getBlokHist($blok,$prd,'jenisbibit'),
						'tahuntanam'   => getBlokHist($blok,$prd,'tahuntanam'),
						'periode'      => $prd,
						'tanggal'      => $tgl,
						'luaspanen'    => $hapanen[$blok][$tgl],
						'jjgpanen'     => $panen[$blok][$tgl],
						'jjgafkir'     => $afkir[$blok][$tgl],
						'jjgkirim'     => $kirim[$blok][$tgl],
						'jjgrestan'    => $jjgrestan,
						'kgwb'         => $kgpks[$blok][$tgl],
						'jjgbgtbi'     => $jjgbgtx,
						'jjgbgtsetahun'=> $totaljjgx,
						'kgbgtbi'      => $kgbgtx,
						'kgbgtsetahun' => $totalkgx
					);
					
					$query = insertQuery($dbnamerpt,'owl_smblok',$data,array_keys($data));
					$owlPDORPT->exec($query);
				}
			}
		}
	}
	
	##== INSERT TO OWL_SMASSET ==##
	$kdAst     = date("Y-m");
	$bulanDt   = explode("-",$kdAst);
	$tahun     = $bulanDt[0];
	$where="";
	$where.=" and awalpenyusutan <='".$kdAst."' ";
	$where.=" and (substr(tanggaldisposal,1,7)>'".$kdAst."' or tanggaldisposal='0000-00-00') ";
	
	$optMetode2= makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
	$arrDt     = array("1"=>"Biaya Langsung","2"=>"Tidak Langsung","3"=>"Operasi");
	$arrstatus = array("0"=>'nonaktif',"1"=>'aktif',"2"=>'rusak',"4"=>'dijual');
	
	$str = "select * from ".$dbnamerpt.".sdm_daftarasset where 1=1 ".$where." order by tipeasset";
	$data = array();
	$res = fetchdatarpt($str);
	foreach($res as $bar){
		$selisih=array();
		$sisabln=0;
		
		$tgl1 = $bar['awalpenyusutan']."-01";
		$tgl2 = $kdAst."-02";
		$tahun1=substr($tgl1,0,4);
		$bulan1=substr($tgl1,5,2);
		$tahun2=substr($tgl2,0,4);
		$bulan2=substr($tgl2,5,2);
		if(substr($bar['awalpenyusutan'],0,4)==$bulanDt[0]){
			if($bar['awalpenyusutan']==$kdAst){
				$perkalibulanan=1;  
			}else{
				$perkalibulanan=($bulanDt[1]-intval(substr($bar['awalpenyusutan'],5,2))+1);
			}
		}else{
			$perkalibulanan=intval($bulanDt[1]);
		}
		$selisih['months_total']=($tahun2*12)+$bulan2 - (($tahun1*12)+$bulan1);
		$selisih['months_total']+=1;
		
		$tgl1=$bar['awalpenyusutan']."-01";
		# periksa siapa lebih besar
		if($tgl1>$tgl2){$selisih['months_total']=0;}
		
		# perhitungan bulanan
		$rupiahbulanan=$bar['bulanan'];
		$sisabln=$bar['jlhblnpenyusutan']-$selisih['months_total'];
		$sisblnCek=$sisabln;
		if($sisabln<0){$sisabln=0;}
		$akumulasiBulanan=($bar['bulanan']*$selisih['months_total'])+$bar['akumulasiadjust'];
		if(abs($akumulasiBulanan)>=abs($bar['hargaperolehan'])){$akumulasiBulanan=$bar['hargaperolehan'];}
		
		#= buat bulan terakhir penyusutan
		$akhirpenyusutan=periodelalu(jumlahbulandepan($bar['awalpenyusutan'],$bar['jlhblnpenyusutan']));
		
		#= selisih penyusutan
		#= untuk akumulasi dan perbulan juga
		#= variable nilaiselisih (untuk perbulan) dan nilaiselisihakumulasi (untuk nilai buku)
		$nilaiselisih=$nilaiselisihakumulasi=$bar['hargaperolehan']-($bar['bulanan']*$bar['jlhblnpenyusutan']);
		if($akhirpenyusutan!=$kdAst){$nilaiselisihakumulasi=0;}
		$nilai=$bar['hargaperolehan']-$akumulasiBulanan-$nilaiselisihakumulasi;
		
		#jika doubledeclining
		if($bar['persendecline']>'0'){
			$thnawal=substr($bar['awalpenyusutan'],0,4);
			$blnawal=substr($bar['awalpenyusutan'],5,2);
			$total=($thnawal*12)+$blnawal;
			
			$thnNow=substr($kdAst,0,4);
			$blnNow=substr($kdAst,5,2);
			
			$totalBulanAwal = 12-$blnawal+1;
			$totalTahun = $thnNow-$thnawal-1;
			
			$totalNow=($thnNow*12)+$blnNow+1;
			$selisihNow=$totalNow-$total;
			$sekarang=0;
			$out=0;
			$akumNow=0;
			
			// Depresiasi s/d akhir tahun
			$before = $sekarang = $bar['hargaperolehan'];
			if($totalTahun>-1) {
				$akumNow += $totalBulanAwal/12 * $bar['persendecline']/100 * $sekarang;
			}
			$sekarang -= $akumNow;
			
			// Depresiasi per Tahun
			if($totalTahun>0) {
				for($i=0;$i<$totalTahun;$i++) {
					$akumNow += $sekarang*$bar['persendecline']/100;
					$sekarang -= $sekarang*$bar['persendecline']/100;
				}
			}
			
			// Depresiasi per Bulan
			$out = $sekarang*($bar['persendecline']/100)/12;
			if($bar['jlhblnpenyusutan']<$selisihNow) {
				$akumNow += $sekarang;
				$sekarang = $out = 0;
			} else {
				if($totalTahun>-1) {
					if(intval($blnNow)>0) {
						$akumNow += (intval($blnNow)*$out);
						$sekarang -= (intval($blnNow)*$out);
					}
				} else {
					$akumNow += ($blnNow-$blnawal+1)*$out;
					$sekarang -= ($blnNow-$blnawal+1)*$out;
				}
			}
			
			$akumulasiBulanan=$akumNow;
			$nilai           =$sekarang;
			$bar['bulanan']  =$out;
		}
		if($nilai==0){
			if($sisblnCek<0){
				$bar['jlhblnpenyusutan'] = 0;
				$selisih['months_total'] = 0;
				$sisabln = 0;
				$akumulasiBulanan = $bar['hargaperolehan'];
				$bar['bulanan']=0;
				$nilai=0;   
			}
		}
		
		if($sisblnCek<0){
			if(($bar['status']!=0)&&($bar['status']!=1)){
				$bar['jlhblnpenyusutan'] = 0;
				$selisih['months_total'] = 0;
				$sisabln = 0;
				$akumulasiBulanan = 0;
				$bar['bulanan']=0;
				$nilai=0;   
			}
		}
		
		# bulanan
		$databulanan=$thnan=array();
		for($awalan=1;$awalan<=intval($bulanDt[1]);$awalan++){
			if($awalan<10){
				$prdcek=$bulanDt[0]."0".$awalan;
			}else{
				$prdcek=$bulanDt[0]."".$awalan;
			}
			if($bar['periodenonaktif']!="0000-00"){
				$isiPeriode  =str_replace("-","",$bar['periodenonaktif']);
				$cekperiodedt=@intval($isiPeriode-$prdcek);
				if($cekperiodedt<=0){$bar['bulanan']=0;}
			}
		
			#= jika akhir penyusutan=periode ini maka tambah selisih (jika ada selisih)
			if($prdcek==str_replace("-","",$akhirpenyusutan)){
				$datanilaiselisih=$nilaiselisih;
			}else{
				$datanilaiselisih=0;
			}
			if($prdcek>str_replace("-","",$akhirpenyusutan)){
				$rupiahbulanan=0;
			}
			$blnBerjalan=0;
			if($bulanDt[0]==substr($bar['awalpenyusutan'],0,4)){
				$blnawal=intval(substr($bar['awalpenyusutan'],5,2));
				if($awalan>=$blnawal){
					$blnBerjalan=$bar['bulanan'];
					$databulanan[addZero($awalan,2)]=$rupiahbulanan+$datanilaiselisih;
					//$data.="<td align=right>".hidezerodecimal($rupiahbulanan+$datanilaiselisih,2)."</td>";//ini yg 0
					$thnan[$bar['kodeasset']]+=$rupiahbulanan+$datanilaiselisih;
					$arrSubBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
					$totPerBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
				}else{
					$databulanan[addZero($awalan,2)]=0;
					//$data.="<td align=right>0</td>";//ini yg 0
				}   
			}else{
				$blnBerjalan=$bar['bulanan'];
				$databulanan[addZero($awalan,2)]=$rupiahbulanan+$datanilaiselisih;
				//$data.="<td align=right>".hidezerodecimal($rupiahbulanan+$datanilaiselisih,2)."</td>";//ini yg 0
				$thnan[$bar['kodeasset']]+=$rupiahbulanan+$datanilaiselisih;
				$arrSubBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
				$totPerBulan[$awalan]+=$rupiahbulanan+$datanilaiselisih;
			}
		}
		
		#disposal
		$strpa="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='DISPOGAIN'";
		$respa=fetchdatarpt($strpa);
		$dislabarugi=$respa[0]['nilai'];


		$ressup="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='DIS".substr($bar['kodeasset'],4,2)."'";
		$barsup=fetchdatarpt($ressup);
		$dispenyusutan=$barsup[0]['noakundebet'];
		$disaset      =$barsup[0]['noakunkredit'];


		$sDispoaset="select a.kodeasset, b.noakun, sum(b.jumlah) as nilai from ".$dbname.".keu_disposalasset a 
		left join ".$dbname.".keu_jurnaldt b on a.notransaksi=b.noreferensi where a.kodeasset='".$bar['kodeasset']."' 
		and b.noakun in ('".$dislabarugi."','".$dispenyusutan."','".$disaset."') group by a.kodeasset,b.noakun";
		$qDispoaset=fetchdatarpt($sDispoaset);
		$arrDisponilai=array();
		foreach($qDispoaset as $barDispoaset) {
			$arrDisponilai[$barDispoaset['noakun']]=$barDispoaset['nilai'];
		}
		
		$str = "delete from ".$dbname.".owl_smasset where tahun='".$tahun."' and kodeorg='".$bar['kodeorg']."' and kodeasset='".$bar['kodeasset']."'";
		$owlPDO->exec($str);
		
		$data = array(
			'tahun'                  => $tahun,
			'kodeorg'                => $bar['kodeorg'],
			'kodeasset'              => $bar['kodeasset'],
			'kodeassetlama'          => $bar['kodeassetlama'],
			'namakelasset'           => $optMetode2[$bar['tipeasset']],
			'induk'                  => $bar['induk'],
			'tglperolehan'           => $bar['tanggalperolehan'],
			'tgldisposal'            => $bar['tanggaldisposal'],
			'namaasset'              => $bar['namasset'],
			'tipemodel'              => $bar['tipemodel'],
			'nomorrangka_serial'     => $bar['norangka'],
			'namamesin'              => $bar['nomesin'],
			'jenisbiaya'             => $arrDt[$bar['jenis_biaya']],
			'status'                 => $arrstatus[$bar['status']],
			'perolehanrp'            => $bar['hargaperolehan'],
			'jlhblnpenyusutan'       => $bar['jlhblnpenyusutan'],
			'usiabulan'              => $selisih['months_total'],
			'sisabulan'              => $sisabln,
			'keterangan'             => $bar['keterangan'],
			'bulanawalpenyusutan'    => $bar['awalpenyusutan'],
			'bulanakhirpenyusutan'   => $akhirpenyusutan,
			'01'                     => $databulanan['01'],
			'02'                     => $databulanan['02'],
			'03'                     => $databulanan['03'],
			'04'                     => $databulanan['04'],
			'05'                     => $databulanan['05'],
			'06'                     => $databulanan['06'],
			'07'                     => $databulanan['07'],
			'08'                     => $databulanan['08'],
			'09'                     => $databulanan['09'],
			'10'                     => $databulanan['10'],
			'11'                     => $databulanan['11'],
			'12'                     => $databulanan['12'],
			'penyusutanthnini'       => $thnan[$bar['kodeasset']],
			'akumulasipenyusutan'    => $akumulasiBulanan+$datanilaiselisih,
			'nilaidisposalasset'     => (-1*($arrDisponilai[$disaset])),
			'nilaidisposalpenyusutan'=> $arrDisponilai[$dispenyusutan],
			'nilaidisposallabarugi'  => $arrDisponilai[$dislabarugi],
			'nilaibuku'              => $nilai,
			'periodenonaktif'        => $bar['periodenonaktif']
		);
		
		$query = insertQuery($dbnamerpt,'owl_smasset',$data,array_keys($data));
		$owlPDORPT->exec($query);
	}
	
	echo "Selesai";
	// #execute
	// $owlPDORPT->commit();
// } catch (PDOException $e) {
	// $owlPDORPT->rollback(); 
	// //kirimtelegram("1783000758","[pindahapproval] Error from ".$_SERVER['PHP_SELF']."\n".addslashes($e->getMessage()));
	// echo "Error, " . addslashes($e->getMessage());
	// die();
// }
?>
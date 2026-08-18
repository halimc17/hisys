<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$proses     = checkPostGet('proses', '');
$kdorg      = checkPostGet('kdorg', '');
$periode    = checkPostGet('periode', '');
$periodesd  = checkPostGet('periodesd', '');
$tipe       = checkPostGet('tipe', '');
$tampil     = checkPostGet('tampil', '');
$jenis      = checkPostGet('jenis', '');
// $kdpt       = checkPostGet('kdpt', '');
// $digit      = checkPostGet('digit', '');

if(count($_POST)>0){
	$param = $_POST;
}else{
	$param = $_GET;
}

switch ($proses) {
    case 'getunit':
		$optorg.="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where namaorganisasi not like '%NON AKTI%' and induk='".$kdpt."' and length(kodeorganisasi)='4' and kodeorganisasi in (".getOrgDetail(2).") order by induk";
		$res=fetchdata($str);
		foreach($res as $bar){
			$d=getNamaOrg($bar['kodeorganisasi'],'induk');
			if($d!=$n){
				$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optorg.="<option value=" . $bar['kodeorganisasi'] . " ".$s.">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
			$n=$d;
			if($d!=$n){
				$optorg.="</optgroup>";
			}
		}

		echo $optorg;

	break;
    case 'preview':
    case 'excel':
	// if($param['kdpt']==''){
	// 	exit("Warning: Kode PT harus diisi.");
	// }
	$rangeperiode = month_inbetween($periode,$periodesd);

	$akunhutanggaji = '2130101';
	$akunhutangbpjs = '2130104';
	$akunalktransit = '4110299';

	$where="";
	$where.=" and substr(tanggal,1,7) between '".$param['periode']."' and '".$param['periodesd']."'";
	if($param['kdorg']!=''){
		$where.=" and kodeorg = '".$param['kdorg']."'";
	}else{
		// $str="select * from ".$dbname.".organisasi where namaorganisasi not like '%NON AKTI%' and induk='".$kdpt."' and length(kodeorganisasi)='4' and kodeorganisasi in (".getOrgDetail(2).") order by induk";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		// }
		// $where.=" and kodeorg in ('".implode("','",$listkodeorg)."')";
		exit('Warning : Unit harus di pilih');
	}




	$tipekaryawan = array('1'=>'Non Staff','4'=>'BHL');

	$stream.="<table cellpadding=2 cellspacing=0 border=1>";
	$stream.="<tr>";
	$stream.="<th>No</th>";
	$stream.="<th>Nojurnal</th>";
	$stream.="<th>Kelompok</th>";
	$stream.="<th>Noakun</th>";
	$stream.="<th>Nama Akun</th>";
	$stream.="<th>Keterangan</th>";
	$stream.="<th>Tipe</th>";
	$stream.="<th>Periode</th>";
	$stream.="<th>Jumlah</th>";
	$stream.="</tr>";

	if($proses=='excel'){
		$tab="<table cellpadding=5 cellspacing=1 border=1 class=sortable>";
	}else{
		$tab="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
	}
	$tab.="<thead>";
	$tab.="<tr>";
	$tab.="<th rowspan=3>No</th>";
	$tab.="<th rowspan=3 colspan=4>Item</th>";
	foreach($rangeperiode as $period){
		$tab.="<th colspan=".(count($tipekaryawan)+1).">".$period."</th>";
	}
	$tab.="</tr>";
	$tab.="<tr>";
	foreach($rangeperiode as $period){
		$tab.="<th colspan=".count($tipekaryawan).">".$_SESSION['lang']['tipekaryawan']."</th>";
		$tab.="<th rowspan=2>TOTAL</th>";
	}
	$tab.="</tr>";
	$tab.="<tr>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipekar => $namatipe){
			$tab.="<th>".$namatipe."</th>";
		}
	}
	$tab.="</tr>";
	$tab.="</thead>";
	$tab.="</tbody>";


	$sql = "select * from ".$dbnamerpt.".sdm_ho_component where 1=1 and plus='1'";
	$req = fetchdata($sql);
	foreach($req as $val){
		$pattern = "/BPJS/i";
		$bpjs = preg_match($pattern, $val['name']);
		if($bpjs==1){
			$compbpjs[$val['id']]=$val['id'];
		}else{
			$compgaji[$val['id']]=$val['id'];
		}
		$namecomp[$val['id']]=$val['name'];
	}

	$datacomp=[];
	# ambil data gaji payroll
	if($param['kdorg']!=''){
		$whgaji=" and kodeorg = '".$param['kdorg']."'";
	}else{
		$whgaji=" and kodeorg in ('".implode("','",$listkodeorg)."')";
	}

	$sql = "select * from ".$dbnamerpt.".sdm_gaji_vw where 1=1 ".$whgaji." and periodegaji between '".$param['periode']."' and '".$param['periodesd']."'";
	$req = fetchdata($sql);
	foreach($req as $val){
		if(is_null($val['tipekaryawan'])){
			$query = "select tipekaryawan, karyawanid from ".$dbnamerpt.".datakaryawan_hist where periodegaji ='".$val['periodegaji']."' and version_type='B' and karyawanid='".$val['karyawanid']."' order by nourut desc limit 1";
			$res = fetchdata($query);
			$val['tipekaryawan']=$res[0]['tipekaryawan'];
		}
		if($compbpjs[$val['idkomponen']]!=''){
			$datacomp['bpjs'][$val['idkomponen']]=$val['idkomponen'];
			$datagaji[$val['periodegaji']]['bpjs'][$val['idkomponen']][$val['tipekaryawan']]+=$val['jumlah'];
			$subgaji[$val['periodegaji']]['bpjs'][$val['tipekaryawan']]+=$val['jumlah'];
			$gtgajipayroll[$val['periodegaji']][$val['tipekaryawan']]+=$val['jumlah'];
		}
		if($compgaji[$val['idkomponen']]!=''){
			$subgaji[$val['periodegaji']]['gaji'][$val['tipekaryawan']]+=$val['jumlah'];
			$datacomp['gaji'][$val['idkomponen']]=$val['idkomponen'];
			$datagaji[$val['periodegaji']]['gaji'][$val['idkomponen']][$val['tipekaryawan']]+=$val['jumlah'];
			$gtgajipayroll[$val['periodegaji']][$val['tipekaryawan']]+=$val['jumlah'];
		}
		$tipekary[$val['tipekaryawan']]=$val['tipekaryawan'];
	}

	$tab.="<tr class=rowcontent style=font-weight:bold;cursor:pointer; title=click onclick=showbaris('payroll');><td>1</td><td nowrap colspan=".(count($tipekaryawan)+2).">Total Gaji dan BPJS (Payroll)</td>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipe => $namatipe){
			$tab.="<td align=right>".nantozero($gtgajipayroll[$period][$tipe])."</td>";
			$gtgtgaji[$period]+=$gtgajipayroll[$period][$tipe];
		}
		$tab.="<td align=right>".nantozero($gtgtgaji[$period])."</td>";
	}
	$tab.="</tr>";
	foreach($datacomp as $kelcomp => $val1){
		$n++;
		$tab.="<tr class='rowcontent payroll' style='font-style:italic;font-weight:bold;cursor:pointer;' title=click onclick=\"showbaris('payroll ".$kelcomp."')\";>";
		$tab.="<td></td>";
		$tab.="<td>".$n."</td>";
		$tab.="<td nowrap colspan=".(count($tipekaryawan)+1).">TOTAL ".strtoupper($kelcomp)."</td>";
		foreach($rangeperiode as $period){
			foreach($tipekaryawan as $tipe => $namatipe){
				$tab.="<td align=right>".nantozero($subgaji[$period][$kelcomp][$tipe])."</td>";
				$gtsubgajir[$period][$kelcomp]+=$subgaji[$period][$kelcomp][$tipe];
			}
			$tab.="<td align=right>".nantozero($gtsubgajir[$period][$kelcomp])."</td>";
		}
		foreach($val1 as $idkomp){
			$tab.="<tr style=display:none class='rowcontent payroll ".$kelcomp."'>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td>".$idkomp."</td>";
			$tab.="<td nowrap colspan=2>".$namecomp[$idkomp]."</td>";
			foreach($rangeperiode as $period){
				foreach($tipekaryawan as $tipe => $namatipe){
					$tab.="<td align=right>".nantozero($datagaji[$period][$kelcomp][$idkomp][$tipe])."</td>";
					$ttlgjpayroll[$period][$kelcomp][$idkomp]+=$datagaji[$period][$kelcomp][$idkomp][$tipe];
				}
				$tab.="<td align=right>".nantozero($ttlgjpayroll[$period][$kelcomp][$idkomp])."</td>";
			}

			$tab.="</tr>";
		}
		$tab.="</tr>";
	}
	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#40E0D0><td colspan=".((count($tipekaryawan)+3)+(count($rangeperiode)*3))."></td></tr>";

	# ambil data alokasi gaji total
	$sql = "select distinct nojurnal, sum(jumlah) as jumlah, tipekaryawan, a.nik from ".$dbnamerpt.".keu_jurnaldt a left join ".$dbnamerpt.".datakaryawan_hist b on a.nik=b.karyawanid and substr(a.tanggal,1,7)=b.periodegaji and approval_status='8' and version_type='B' where 1=1 ".$where." and noakun ='".$akunhutanggaji."'  group by nojurnal, tipekaryawan,a.nik";
	$req = fetchdata($sql);
	foreach($req as $val){
		if(is_null($val['tipekaryawan'])){
			$val['tipekaryawan']=getKary($val['nik'],'tipekaryawan');
		}
		if($val['tipekaryawan']!=''){
			$nojurnal[$val['nojurnal']]=$val['nojurnal'];
			$pertipekary[$val['nojurnal']][$val['tipekaryawan']]+=$val['jumlah'];
			$totaljurnal[$val['nojurnal']]+=$val['jumlah'];
			$tipekary[$val['tipekaryawan']]=$val['tipekaryawan'];
			$ttltipekary[$val['nojurnal']][$val['tipekaryawan']]++;
		}
	}

	// echo "<pre>";
	// print_r($totaljurnal);
	// echo "</pre>";

	$gttransittemp=[];
	$jumlah=$gtgaji=[];
	$jumlahtransit=[];
	foreach($nojurnal as $jurnal){
		$str = "select *, substr(tanggal,1,7) as periode from ".$dbnamerpt.".keu_jurnaldt where nojurnal='".$jurnal."' and noakun !='".$akunhutanggaji."' and substr(noakun,1,3) in ('621','611','631','126','128','129','411','631','632','711','712','715','721','732','811','812','813','814','821','822','826') and jumlah>'0' and nojurnal not like '%/M/%' order by tanggal desc";
		$res = fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['noakun'],0,1)!='4'){
				$kelompok = substr($bar['noakun'],0,$digit);
			}else{
				if($digit>3){
					$kelompok = substr($bar['noakun'],0,3);
				}else{
					$kelompok = substr($bar['noakun'],0,$digit);
				}
			}
			$jumlahdetail = [];
			foreach($tipekaryawan as $tipe => $namatipe){
				# ambil yang hanya alokasi ke biaya transit
				if(substr($bar['noakun'],0,3)=='411'){
					if($ttltipekary[$bar['nojurnal']][$tipe]>1){
						if($bar['nik']!=''){
							$jumlahtransit[$bar['periode']][getKary($bar['nik'],'tipekaryawan')][$bar['noakun']]+=$bar['jumlah'];
						}else{
							$jumlahtransit[$bar['periode']][$tipe][$bar['noakun']]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						}
					}else{
						$jumlahtransit[$bar['periode']][$tipe][$bar['noakun']]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					}
					$dataalk[$tipe][$bar['noakun']]=$bar['noakun'];

				}

				# ambil yang all data alokasi ke biaya
				if($ttltipekary[$bar['nojurnal']][$tipe]>1){
					if($bar['nik']!=''){
						$gtgaji[$bar['periode']][getKary($bar['nik'],'tipekaryawan')]+=$bar['jumlah'];
						$jumlah[$bar['periode']][$kelompok][getKary($bar['nik'],'tipekaryawan')]+=$bar['jumlah'];
						$jumlahdetail[$kelompok][getKary($bar['nik'],'tipekaryawan')]=$bar['jumlah'];
					}else{
						$gtgaji[$bar['periode']][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						$jumlah[$bar['periode']][$kelompok][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						$jumlahdetail[$kelompok][$tipe]=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					}
				}else{
					$gtgaji[$bar['periode']][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					$jumlah[$bar['periode']][$kelompok][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					$jumlahdetail[$kelompok][$tipe]=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
				}
				//if(abs($jumlahdetail[$kelompok][$tipe])>'0'){
					$no++;
					$tabgaji.="<tr>";
					$tabgaji.="<td>".$no."</td>";
					$tabgaji.="<td>".$bar['nojurnal']."</td>";
					$tabgaji.="<td>".getNamaAkun($kelompok)."</td>";
					$tabgaji.="<td>".$bar['noakun']."</td>";
					$tabgaji.="<td>".getNamaAkun($bar['noakun'])."</td>";
					$tabgaji.="<td>".$bar['keterangan']."</td>";
					$tabgaji.="<td>".getNamaTipeKary($tipe)."</td>";
					$tabgaji.="<td>".$bar['periode']."</td>";
					$tabgaji.="<td align=right>".nantozero($jumlahdetail[$kelompok][$tipe])."</td>";
					$tabgaji.="</tr>";
				// }
				$gtgajittl+=$jumlahdetail[$kelompok][$tipe];

				$headalokgaji[$kelompok]=$kelompok;

			}
		}
	}

	$tabgaji.="<tr>";
	$tabgaji.="<td colspan=8>TOTAL</td>";
	$tabgaji.="<td align=right>".nantozero($gtgajittl)."</td>";
	$tabgaji.="</tr>";





	# ambil data alokasi gaji transit
	$nojurnal=[];
	$sql = "select sum(jumlah) as jumlah, substr(tanggal,1,7) as tanggal, nojurnal from ".$dbnamerpt.".keu_jurnaldt where 1=1 ".$where." and jumlah<'0' and noakun='".$akunalktransit."' and nojurnal not like '%/M/%' group by substr(tanggal,1,7), nojurnal";
	$req = fetchdata($sql);
	foreach($req as $bar){
		$bar['jumlah'] = abs($bar['jumlah']);
		$gtbyyalk[substr($bar['tanggal'],0,7)]+=$bar['jumlah'];
		$nojurnal[$bar['nojurnal']]=$bar['nojurnal'];
	}

	$rupiahtransit=[]; $no=0; $temptransit=[];
	foreach($nojurnal as $jurnal){
		$sql = "select *, substr(tanggal,1,7) as periode from ".$dbnamerpt.".keu_jurnaldt where nojurnal='".$jurnal."' and substr(noakun,1,3) not in ('411') and nojurnal not like '%/M/%'";
		$req = fetchdata($sql);
		foreach($req as $bar){
			if(substr($bar['noakun'],0,1)!='4'){
				$kelompok = substr($bar['noakun'],0,$digit);
			}else{
				if($digit>3){
					$kelompok = substr($bar['noakun'],0,3);
				}else{
					$kelompok = substr($bar['noakun'],0,$digit);
				}
			}
			$kelalktran[$kelompok]=$kelompok;
			foreach($dataalk as $tipe => $val1){
				foreach($val1 as $noakun){
					$bar['jumlah'] = abs($bar['jumlah']);
					$rupiahtransit[$bar['periode']][$kelompok][$tipe]+=($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun]);

					$cektotal=((($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun])));
					if($cektotal>0){
						$no++;
						$tabgajit.="<tr>";
						$tabgajit.="<td>".$no."</td>";
						$tabgajit.="<td>".$bar['nojurnal']."</td>";
						$tabgajit.="<td>".getNamaAkun($kelompok)."</td>";
						$tabgajit.="<td>".$bar['noakun']."</td>";
						$tabgajit.="<td>".getNamaAkun($bar['noakun'])."</td>";
						$tabgajit.="<td>".$noakun." (".getNamaAkun($noakun).")</td>";
						$tabgajit.="<td>".getNamaTipeKary($tipe)."</td>";
						$tabgajit.="<td>".$bar['periode']."</td>";
						$tabgajit.="<td align=right>".nantozero(($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun]))."</td>";
						$tabgajit.="</tr>";
					}
					$gtgajit+=($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun]);
				}
			}
		}
	}

	$tabgajit.="<tr>";
	$tabgajit.="<td colspan=8>TOTAL</td>";
	$tabgajit.="<td align=right>".nantozero($gtgajit)."</td>";
	$tabgajit.="</tr>";

	# ambil data alokasi gaji total end
	ksort($kelalktran);
	ksort($headalokgaji);
	$tab.="<tr class=rowcontent style=font-weight:bold;cursor:pointer; title=click onclick=showbaris('jurnal');><td>2</td><td nowrap colspan=".(count($tipekaryawan)+2).">Total Alokasi Gaji (Jurnal)</td>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipe => $namatipe){
			$tab.="<td align=right>".nantozero($gtgaji[$period][$tipe])."</td>";
			$gtgajir[$period]+=$gtgaji[$period][$tipe];
		}
		$tab.="<td align=right>".nantozero($gtgajir[$period])."</td>";
	}
	$tab.="</tr>";
	foreach($headalokgaji as $kel){
		$tab.="<tr class='rowcontent jurnal' ".(substr($kel,0,1)=='4'?"style=background-color:#A3E4D7;cursor:pointer;font-weight:bold;display:none title=click onclick=\"showbaris('jurnal transit')\";":"style=display:none").">";
		$tab.="<td></td>";
		$tab.="<td>".$kel."</td>";
		$tab.="<td colspan=3>".ucwords(strtolower(getNamaAkun($kel)))."</td>";
		foreach($rangeperiode as $period){
			foreach($tipekaryawan as $tipe => $namatipe){
				$tab.="<td align=right>".nantozero($jumlah[$period][$kel][$tipe])."</td>";
				$gtjumlahr[$period][$kel]+=$jumlah[$period][$kel][$tipe];
			}
			$tab.="<td align=right>".nantozero($gtjumlahr[$period][$kel])."</td>";
		}
		#jika transit breakdown lagi
		if(substr($kel,0,1)=='4'){
			foreach($kelalktran as $kel){
				$tab.="<tr class='rowcontent jurnal transit' style=font-style:italic;background-color:#D1F2EB;display:none>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td>".$kel."</td>";
				$tab.="<td nowrap colspan=2>".ucwords(strtolower(getNamaAkun($kel)))."</td>";
				foreach($rangeperiode as $period){
					foreach($tipekaryawan as $tipe => $namatipe){
						$tab.="<td align=right>".nantozero($rupiahtransit[$period][$kel][$tipe])."</td>";
						$gtrupiahtransitr[$period][$kel]+=$rupiahtransit[$period][$kel][$tipe];
					}
					$tab.="<td align=right>".nantozero($gtrupiahtransitr[$period][$kel])."</td>";
				}
				$tab.="</tr>";
			}
		}
		$tab.="</tr>";
	}


	# ambil data alokasi bpjs total script ini sama dengan atas, jika perubahan diatas maka rubah juga disini, yang membedakan cuma noakun
	$nojurnal=$pertipekary=$totaljurnal=$ttltipekary=[];
	$sql = "select nojurnal, sum(jumlah) as jumlah, tipekaryawan, a.nik from ".$dbnamerpt.".keu_jurnaldt a left join ".$dbnamerpt.".datakaryawan_hist b on a.nik=b.karyawanid and substr(a.tanggal,1,7)=b.periodegaji and approval_status='8' and version_type='B' where 1=1 ".$where." and noakun ='".$akunhutangbpjs."' and nojurnal not like '%/M/%' and noreferensi not like 'ALK_POT%' group by nojurnal, tipekaryawan,a.nik";
	$req = fetchdata($sql);
	foreach($req as $val){
		if(is_null($val['tipekaryawan'])){
			$val['tipekaryawan']=getKary($val['nik'],'tipekaryawan');
		}
		if($val['tipekaryawan']!=''){
			$nojurnal[$val['nojurnal']]=$val['nojurnal'];
			$pertipekary[$val['nojurnal']][$val['tipekaryawan']]+=$val['jumlah'];
			$totaljurnal[$val['nojurnal']]+=$val['jumlah'];
			$tipekary[$val['tipekaryawan']]=$val['tipekaryawan'];
			$ttltipekary[$val['nojurnal']][$val['tipekaryawan']]++;
		}
	}
	$no=0;
	$jumlah=$jumlahtransit=$dataalk=$headalokgaji=[]; $gt=0;
	foreach($nojurnal as $jurnal){
		$str = "select *, substr(tanggal,1,7) as periode from ".$dbnamerpt.".keu_jurnaldt where nojurnal='".$jurnal."' and noakun !='".$akunhutangbpjs."' and jumlah>'0' and substr(noakun,1,3) in ('621','611','631','126','128','129','411','631','632','711','712','715','721','732','811','812','813','814','821','822','826') and nojurnal not like '%/M/%'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$jumlahdetail=[];
			$length   = strpos($bar['keterangan'],'( )');
			$jnsbpjs  = substr($bar['keterangan'],0,$length);
			if($jnsbpjs!=''){
				$jnsbpjs = $jnsbpjs;
			}else{
				$jnsbpjs = trim(substr($bar['keterangan'],0,strlen($bar['keterangan'])-7));
			}
			if(substr($bar['noakun'],0,1)!='4'){
				$kelompok = substr($bar['noakun'],0,$digit);
			}else{
				if($digit>3){
					$kelompok = substr($bar['noakun'],0,3);
				}else{
					$kelompok = substr($bar['noakun'],0,$digit);
				}
			}
			foreach($tipekaryawan as $tipe => $namatipe){
				# ambil yang hanya alokasi ke biaya transit
				if(substr($bar['noakun'],0,3)=='411'){
					if($ttltipekary[$bar['nojurnal']][$tipe]>1){
						if($bar['nik']!=''){
							$jumlahtransit[$bar['periode']][getKary($bar['nik'],'tipekaryawan')][$bar['noakun']]+=$bar['jumlah'];
						}else{
							$jumlahtransit[$bar['periode']][$tipe][$bar['noakun']]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						}
					}else{
						$jumlahtransit[$bar['periode']][$tipe][$bar['noakun']]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					}

					$dataalk[$tipe][$bar['noakun']]=$bar['noakun'];
				}

				# ambil yang all data alokasi ke biaya
				if($ttltipekary[$bar['nojurnal']][$tipe]>1){
					if($bar['nik']!=''){
						$gtbpjs[$bar['periode']][getKary($bar['nik'],'tipekaryawan')]+=$bar['jumlah'];
						$ttlbpjs[$bar['periode']][$jnsbpjs][getKary($bar['nik'],'tipekaryawan')]+=$bar['jumlah'];
						$jumlah[$bar['periode']][$jnsbpjs][$kelompok][getKary($bar['nik'],'tipekaryawan')]+=$bar['jumlah'];
						$jumlahdetail[$kelompok][getKary($bar['nik'],'tipekaryawan')]=$bar['jumlah'];
					}else{
						$gtbpjs[$bar['periode']][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						$ttlbpjs[$bar['periode']][$jnsbpjs][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						$jumlah[$bar['periode']][$jnsbpjs][$kelompok][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
						$jumlahdetail[$kelompok][$tipe]=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					}
				}else{
					$gtbpjs[$bar['periode']][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					$ttlbpjs[$bar['periode']][$jnsbpjs][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					$jumlah[$bar['periode']][$jnsbpjs][$kelompok][$tipe]+=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
					$jumlahdetail[$kelompok][$tipe]=$pertipekary[$bar['nojurnal']][$tipe]/$totaljurnal[$bar['nojurnal']]*$bar['jumlah'];
				}
				if(abs($jumlahdetail[$kelompok][$tipe])>0){
					$no++;
					$tabbpjsalk.="<tr>";
					$tabbpjsalk.="<td>".$no."</td>";
					$tabbpjsalk.="<td>".$bar['nojurnal']."</td>";
					$tabbpjsalk.="<td>".getNamaAkun($kelompok)."</td>";
					$tabbpjsalk.="<td>".$bar['noakun']."</td>";
					$tabbpjsalk.="<td>".getNamaAkun($bar['noakun'])."</td>";
					$tabbpjsalk.="<td>".$jnsbpjs."</td>";
					$tabbpjsalk.="<td>".getNamaTipeKary($tipe)."</td>";
					$tabbpjsalk.="<td>".$bar['periode']."</td>";
					$tabbpjsalk.="<td align=right>".nantozero($jumlahdetail[$kelompok][$tipe])."</td>";
					$tabbpjsalk.="</tr>";
				}
				$gtbpjsalk+=$jumlahdetail[$kelompok][$tipe];

				$headalokgaji[$kelompok]=$kelompok;
				$jenisbpjsn[$jnsbpjs]=$jnsbpjs;
				//$jenisbpjs[$jnsbpjs]=$jnsbpjs;

			}
			//echo $bar['nojurnal']." = ".$bar['jlh']."<br>";
		}
	}
	foreach($jenisbpjsn as $code){
		$jenisbpjs[]=$code;
	}


	// echo"<pre>";
	// print_r($totaljurnal);
	// echo"</pre>";

	$tabbpjsalk.="<tr>";
	$tabbpjsalk.="<td colspan=8>TOTAL</td>";
	$tabbpjsalk.="<td align=right>".nantozero($gtbpjsalk)."</td>";
	$tabbpjsalk.="</tr>";

	# ambil data alokasi bpjs transit
	$nojurnal=$gtbyyalk=$kelalktran=[];
	$sql = "select * from ".$dbnamerpt.".keu_jurnaldt where 1=1 ".$where." and jumlah<'0' and noakun='".$akunalktransit."'  and nojurnal not like '%/M/%'";
	$req = fetchdata($sql);
	foreach($req as $bar){
		$bar['jumlah'] = abs($bar['jumlah']);
		$gtbyyalk[substr($bar['tanggal'],0,7)]+=$bar['jumlah'];
		$nojurnal[$bar['nojurnal']]=$bar['nojurnal'];
	}

	$rupiahtransit=[];
	$totaltransbpjs=[];$no=0;
	foreach($nojurnal as $jurnal){
		$sql = "select *, substr(tanggal,1,7) as periode from ".$dbnamerpt.".keu_jurnaldt where nojurnal='".$jurnal."' and substr(noakun,1,3) not in ('411') and nojurnal not like '%/M/%'";
		$req = fetchdata($sql);
		foreach($req as $bar){
			if(substr($bar['noakun'],0,1)!='4'){
				$kelompok = substr($bar['noakun'],0,$digit);
			}else{
				if($digit>3){
					$kelompok = substr($bar['noakun'],0,3);
				}else{
					$kelompok = substr($bar['noakun'],0,$digit);
				}
			}
			$kelalktran[$kelompok]=$kelompok;
			foreach($dataalk as $tipe => $val1){
				foreach($val1 as $noakun){
					$bar['jumlah'] = abs($bar['jumlah']);
					$rupiahtransit[$bar['periode']][$kelompok][$tipe]+=$bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun];
					$totaltransbpjs[$bar['periode']][$tipe]+=$bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun];

					if(abs($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun])>0){
						$no++;
						$tabbpjst.="<tr>";
						$tabbpjst.="<td>".$no."</td>";
						$tabbpjst.="<td>".$bar['nojurnal']."</td>";
						$tabbpjst.="<td>".getNamaAkun($kelompok)."</td>";
						$tabbpjst.="<td>".$bar['noakun']."</td>";
						$tabbpjst.="<td>".getNamaAkun($bar['noakun'])."</td>";
						$tabbpjst.="<td>".$noakun."</td>";
						$tabbpjst.="<td>".getNamaTipeKary($tipe)."</td>";
						$tabbpjst.="<td>".$bar['periode']."</td>";
						$tabbpjst.="<td align=right>".nantozero($bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun])."</td>";
						$tabbpjst.="</tr>";
					}
					$gtbpjst+=$bar['jumlah']/$gtbyyalk[substr($bar['tanggal'],0,7)]*$jumlahtransit[$bar['periode']][$tipe][$noakun];

				}
			}
			$headalokgajibpjs[$kelompok]=$kelompok;
			$lastheadalokgajibpjs=$kelompok;
		}
	}

	$tabbpjst.="<tr>";
	$tabbpjst.="<td colspan=8>TOTAL</td>";
	$tabbpjst.="<td align=right>".nantozero($gtbpjst)."</td>";
	$tabbpjst.="</tr>";

	// echo"<pre>";
	// print_r($headalokgajibpjs);
	// echo"</pre>";

	# ambil data alokasi bpjs total end

	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#40E0D0><td colspan=".((count($tipekaryawan)+3)+(count($rangeperiode)*3))."></td></tr>";

	ksort($headalokgaji); $n=0;
	$tab.="<tr class=rowcontent style=font-weight:bold;cursor:pointer; title=click onclick=\"showbaris('jurnalbpjs')\";><td>3</td><td colspan=".(count($tipekaryawan)+2).">Total Alokasi BPJS (Jurnal)</td>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipe => $namatipe){
			$tab.="<td align=right>".nantozero($gtbpjs[$period][$tipe])."</td>";
			$gtgtbpjst[$period]+=$gtbpjs[$period][$tipe];
		}
		$tab.="<td align=right>".nantozero($gtgtbpjst[$period])."</td>";
	}
	$tab.="</tr>";
	foreach($jenisbpjs as $keybpjs => $jnsbpjs){
		$n++;
		$tab.="<tr class='rowcontent jurnalbpjs' style=font-style:italic;font-weight:bold;cursor:pointer;display:none title=click onclick=\"showbaris('jurnalbpjs ".$keybpjs."')\";>";
		$tab.="<td></td>";
		$tab.="<td>".$n."</td>";
		$tab.="<td colspan=".(count($tipekaryawan)+1).">".$jnsbpjs."</td>";
		foreach($rangeperiode as $period){
			foreach($tipekaryawan as $tipe => $namatipe){
				$tab.="<td align=right>".nantozero($ttlbpjs[$period][$jnsbpjs][$tipe])."</td>";
				$gtttlbpjsr[$period][$jnsbpjs]+=$ttlbpjs[$period][$jnsbpjs][$tipe];
			}
			$tab.="<td align=right>".nantozero($gtttlbpjsr[$period][$jnsbpjs])."</td>";
		}
		foreach($headalokgaji as $kel){
			$tab.="<tr class='rowcontent jurnalbpjs ".$keybpjs."' ".(substr($kel,0,1)=='4'?"style=background-color:#A3E4D7;display:none":"style=display:none").">";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td>".$kel."</td>";
			$tab.="<td colspan=2>".ucwords(strtolower(getNamaAkun($kel)))."</td>";
			foreach($rangeperiode as $period){
				foreach($tipekaryawan as $tipe => $namatipe){
					$tab.="<td align=right>".nantozero($jumlah[$period][$jnsbpjs][$kel][$tipe])."</td>";
					$gtjlhbpjsr[$period][$jnsbpjs][$kel]+=$jumlah[$period][$jnsbpjs][$kel][$tipe];
				}
				$tab.="<td align=right>".nantozero($gtjlhbpjsr[$period][$jnsbpjs][$kel])."</td>";
			}
			$tab.="</tr>";
		}
		$tab.="</tr>";
		if($n==1){
			$kettransitbpjs.=$jnsbpjs;
		}else{
			$kettransitbpjs.=", ".$jnsbpjs;
		}
	}

	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#40E0D0><td colspan=".((count($tipekaryawan)+3)+(count($rangeperiode)*3))."></td></tr>";

	$tab.="<tr class=rowcontent style=font-weight:bold;cursor:pointer; title=click onclick=\"showbaris('transitbpjs')\";><td>4</td><td colspan=".(count($tipekaryawan)+2).">Total Alokasi Transit BPJS</td>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipe => $namatipe){
			$tab.="<td align=right>".nantozero($totaltransbpjs[$period][$tipe])."</td>";
			$gttransbpjs[$period]+=$totaltransbpjs[$period][$tipe];
		}
		$tab.="<td align=right>".nantozero($gttransbpjs[$period])."</td>";
	}
	$tab.="</tr>";


	ksort($headalokgajibpjs);
	foreach($headalokgajibpjs as $kel){
		$tab.="<tr style=display:none class='rowcontent transitbpjs'>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td>".$kel."</td>";
		$tab.="<td colspan=2>".ucwords(strtolower(getNamaAkun($kel)))."</td>";
		$gtrupiahtransitr=[];
		foreach($rangeperiode as $period){
			foreach($tipekaryawan as $tipe => $namatipe){
				// if($lastheadalokgajibpjs==$kel){
					// //$tab.="<td align=right>".nantozero($totaltransbpjs[$period][$tipe]-$tempjumlah[$period][$tipe])."</td>";

					// $gtrupiahtransitr[$period][$kel]+=$totaltransbpjs[$period][$tipe]-$tempjumlah[$period][$tipe];
				// }else{
					// $tempjumlah[$period][$tipe]+=floor($rupiahtransit[$period][$kel][$tipe]);
					// // $tab.="<td align=right>".nantozero(floor($rupiahtransit[$period][$kel][$tipe]))."</td>";

					// $gtrupiahtransitr[$period][$kel]+=floor($rupiahtransit[$period][$kel][$tipe]);
				// }
				$tab.="<td align=right>".nantozero($rupiahtransit[$period][$kel][$tipe])."</td>";
				$gtrupiahtransitr[$period][$kel]+=$rupiahtransit[$period][$kel][$tipe];
			}
			$tab.="<td align=right>".nantozero($gtrupiahtransitr[$period][$kel])."</td>";
		}
		$tab.="</tr>";
	}

	// echo"<pre>";
	// print_r($tempjumlah);
	// echo"</pre>";

	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#40E0D0><td colspan=".((count($tipekaryawan)+3)+(count($rangeperiode)*3))."></td></tr>";
	$tab.="<tr class=rowcontent style=font-weight:bold;><td>5</td><td nowrap colspan=".(count($tipekaryawan)+2).">Selisih (1. Payroll - (2. Alk Gaji + 3. Alk BPJS))</td>";
	foreach($rangeperiode as $period){
		foreach($tipekaryawan as $tipe => $namatipe){
			$tab.="<td align=right>".nantozero($gtgajipayroll[$period][$tipe]-($gtgaji[$period][$tipe]+$gtbpjs[$period][$tipe]))."</td>";
			$gtselisij[$period]+=$gtgajipayroll[$period][$tipe]-($gtgaji[$period][$tipe]+$gtbpjs[$period][$tipe]);
		}

		$tab.="<td align=right>".nantozero($gtselisij[$period])."</td>";
	}
	$tab.="</tr>";



	$tab.="</table>";
	$streamtutup.="</table>";

	if($proses=='excel'){
		$nop = "alokasigaji.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("rekap", $tab);
		$xls->addSheet("gaji", $stream.$tabgaji.$streamtutup);
		$xls->addSheet("gaji_transit", $stream.$tabgajit.$streamtutup);
		$xls->addSheet("bpjs", $stream.$tabbpjsalk.$streamtutup);
		$xls->addSheet("bpjs_transit", $stream.$tabbpjst.$streamtutup);
		$xls->headers($nop);
		echo $xls->buildFile();
	}else{
		echo $tab;
	}

	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e="";
	}else if(is_infinite($e)){
		$e="";
	}else{
		$e=$e;
	}
	$n = hidezerodecimal($e,$i);
	if($n==0 or $n==''){
		$n='';
	}

	return $n;
}



?>
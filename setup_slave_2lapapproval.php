<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$method= checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param= $_GET;}

switch ($method) {
    case 'preview':
		$str = "select *  from " . $dbname . ".sdm_ho_component";
		$res = fetchdata($str);
		foreach($res as $bar){
			$idcomp[$bar['id']]=$bar['name'];
			$plus[$bar['id']]=$bar['plus'];
		}

		$str = "select *  from " . $dbname . ".setup_jenisapproval";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmjenis[$bar['jenis']]=$bar['nama'];
		}

		$str = "select *  from " . $dbname . ".sdm_5jabatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmjab[$bar['kodejabatan']]=$bar['namajabatan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$tipekar[$bar['id']]=$bar['tipe'];
		}
		$str = "select *  from " . $dbname . ".sdm_5departemen";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmdept[$bar['kode']]=$bar['nama'];
		}
		$str = "select *  from " . $dbname . ".sdm_5golongan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmgol[$bar['kodegolongan']]=$bar['namagolongan'];
		}
		
		$str = "select *  from " . $dbname . ".sdm_5pendidikan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmpdd[$bar['levelpendidikan']]=$bar['pendidikan'];
		}
		$str = "select *  from " . $dbname . ".sdm_5suku";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmsuku[$bar['idsuku']]=$bar['namasuku'];
		}
		
		$tab.="<table id=mytable class='sortable nowrap' cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kodeorg']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['jenis']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['level']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['approve']." Oleh</th>
				<th style='text-align:center;'>".$_SESSION['lang']['approve']."</th>
				<th style='text-align:center;'>Notifikasi</th>
				<th style='text-align:center;'>".$_SESSION['lang']['departemen']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['jabatan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['tipekaryawan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kodegolongan']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</th>
			</tr>
		</thead>
		<tbody >";
		
		

		$where="";
		if($param['jenis']!=''){
			$where.=" and jenispersetujuan='".$param['jenis']."'";	
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeunit='".$param['kodeorg']."'";	
		}else{
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING' and $_SESSION['empl']['tipelokasitugas']!='KANWIL'){
				$where.=" and kodeunit ='".$_SESSION['empl']['lokasitugas']."'";
			}
		}
		
		$str = "select * from ".$dbname.".setup_approval where 1=1 ".$where." order by kodeunit, jenispersetujuan, level";
		$res = fetchdata($str);
		foreach($res as $bar){
			$sql = "select * from ".$dbname.".setup_approval_notif where kodeunit='".$bar['kodeunit']."' and jenispersetujuan='".$bar['jenispersetujuan']."' and level='".$bar['level']."' and karyawanid!='".$bar['karyawanid']."' and departemen='".$bar['departemen']."' and golongan='".$bar['golongan']."' and karyawaniduser='".$bar['karyawaniduser']."'";
			$req = fetchdata($sql);
			foreach($req as $val){				
				$dt[$val['kodeunit']][$val['jenispersetujuan']][$val['level']][$val['karyawanid']][$val['departemen']][$val['golongan']][$val['karyawaniduser']]="N";
			}
			
			$dt[$bar['kodeunit']][$bar['jenispersetujuan']][$bar['level']][$bar['karyawanid']][$bar['departemen']][$bar['golongan']][$bar['karyawaniduser']]="A";
			$tipekary[$bar['kodeunit']][$bar['jenispersetujuan']][$bar['level']][$bar['karyawanid']][$bar['departemen']][$bar['golongan']][$bar['karyawaniduser']]=$bar['tipekaryawan'];
			$jabat[$bar['kodeunit']][$bar['jenispersetujuan']][$bar['level']][$bar['karyawanid']][$bar['departemen']][$bar['golongan']][$bar['karyawaniduser']]=$bar['jabatan'];
		}

		foreach($dt as $unit => $v1){
			foreach($v1 as $jenis => $v2){
				foreach($v2 as $level => $v3){
					foreach($v3 as $kary => $v4){
						foreach($v4 as $dept => $v5){
							foreach($v5 as $gol => $v6){
								foreach($v6 as $user => $stat){
									$s=$n="";
									if($stat=='N'){
										$s="&#10003;";
									}
									if($stat=='A'){
										$n="&#10003;";
										$s="&#10003;";
									}
									
									$no++;
									$data[]=array(
										$no,
										$unit,
										$nmjenis[$jenis],
										"Level ".$level,
										getNamaKaryawan($kary),
										$n,
										$s,
										$nmdept[$dept],
										$nmjab[$jabat[$unit][$jenis][$level][$kary][$dept][$gol][$user]],
										$tipekar[$tipekary[$unit][$jenis][$level][$kary][$dept][$gol][$user]],
										$nmgol[$gol],
										getNamaKaryawan($user)
									);
								}
							}
						}
					}
				}
			}
		}


		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		
		echo $tab."####".json_encode($data);
	break;

    case 'excel':
        $nop = "csbm.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('csbm', $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>

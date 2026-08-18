<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}


@$param['nilai']  =str_replace(",","",$param['nilai']);

$jab  = getPostingJabatan('budget');

switch ($method) {
	case 'copy':
		$str = "SELECT * FROM " . $dbname . ".sdm_5shiftanggota where kodeorg ='".$param['kodeorg']."' and tanggal like '".$param['dari']."%'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "SELECT * FROM " . $dbname . ".sdm_5shiftanggota where kodeorg ='".$param['kodeorg']."' and tanggal like '".$param['ke']."%'";
			$res = fetchdata($str);
			if(count($res)>0){
				echo "x";
			}
		}else{
			exit("Warning : Data sumber tidak ditemukan.");
		}
	break;
	case 'prosescopy':
		try {
		$owlPDO->beginTransaction();
			$rangeTanggal = rangeTanggal($param['ke']."-01",tglakhir($param['ke']."-01"));
			

			// Ambil tanggal pertama dari bulan input
			$first_day_of_month = $param['dari'] . '-01';

			// Cari hari Senin, Jumat, Sabtu, dan Minggu di minggu pertama bulan input
			$first_monday = date('Y-m-d', strtotime('first monday of ' . $first_day_of_month));
			$first_friday = date('Y-m-d', strtotime('first friday of ' . $first_day_of_month));
			$first_saturday = date('Y-m-d', strtotime('first saturday of ' . $first_day_of_month));
			// $first_sunday = date('Y-m-d', strtotime('first sunday of ' . $first_day_of_month));
			$first_saturday_plus_one = date('Y-m-d', strtotime($first_saturday . ' +1 day'));

			// Cek apakah Jumat lebih kecil dari Minggu (Sabtu + 1 hari)
			if (strtotime($first_friday) > strtotime($first_saturday_plus_one)) {
				$first_saturday_plus_one = date('Y-m-d', strtotime('second sunday of ' . $first_day_of_month));
			}

			$sting="SELECT * FROM $dbname.sdm_5shiftanggota WHERE kodeorg='".$param['kodeorg']."' and tanggal between '".$first_friday."' and '".$first_saturday_plus_one."' ";
			$rest=fetchdata($sting);
			foreach ($rest as $va) {
				if(strtolower(date('D', strtotime($va['tanggal']))) == 'sat'){
					$nmsab[$va['karyawanid']]=$va['namashift'];
				}
				if(strtolower(date('D', strtotime($va['tanggal']))) == 'sun'){
					$nmsun[$va['karyawanid']]=$va['namashift'];
				}
				if(strtolower(date('D', strtotime($va['tanggal']))) == 'fri'){
					$nmfri[$va['karyawanid']]=$va['namashift'];
				}
			}

			$no=0;
			$str = "SELECT * FROM " . $dbname . ".sdm_5shiftanggota where kodeorg ='".$param['kodeorg']."' and tanggal = '".$first_monday."'";
			$res = fetchdata($str);
			if(count($res)>0){
				$no++;
				$str = "SELECT * FROM " . $dbname . ".sdm_5shiftanggota where kodeorg ='".$param['kodeorg']."' and tanggal like '".$param['ke']."%'";
				$req = fetchdata($str);
					if(count($req)>0 and $no==1){
						$str = "delete from ".$dbname.".sdm_5shiftanggota where kodeorg ='".$param['kodeorg']."' and tanggal like '".$param['ke']."%'";
						$owlPDO->exec($str);
					}

				foreach ($res as $bar){
					foreach ($rangeTanggal as $tgl){

						if(strtolower(date('D', strtotime($tgl)))=='sat'){
							$nmshift[$bar['karyawanid']]=$nmsab[$bar['karyawanid']];
						}elseif(strtolower(date('D', strtotime($tgl)))=='sun'){
							$nmshift[$bar['karyawanid']]=$nmsun[$bar['karyawanid']];
						}elseif(strtolower(date('D', strtotime($tgl)))=='fri'){
							$nmshift[$bar['karyawanid']]=$nmfri[$bar['karyawanid']];
						}else{
							$nmshift[$bar['karyawanid']]=$bar['namashift'];
						}
						
						$sql = "SELECT * FROM " . $dbname . ".sdm_5shift where kodeorg ='".$param['kodeorg']."' and shift = '".$bar['shift']."' and namashift='".$nmshift[$bar['karyawanid']]."'";
						$rel = fetchdata($sql);
						if(count($rel)>0){
							$idshift  = $rel[0]['id'];
							$shift    = $rel[0]['shift'];
							$namashift= $rel[0]['namashift'];
						}else{
							$idshift  = $bar['idshift'];
							$shift    = $bar['shift'];
							$namashift= $bar['namashift'];
						}

						$data = array(
							'kodeorg'   => $bar['kodeorg'],
							'subbagian' => $bar['subbagian'],
							'bagian'    => $bar['bagian'],
							'karyawanid'=> $bar['karyawanid'],
							'idshift'   => $idshift,
							'shift'     => $shift,
							'namashift' => $namashift,
							'tanggal'   => $tgl,
							'posting'   => '0',
							'createby'  => $_SESSION['standard']['userid'],
							'createtime'=> date("Y-m-d H:i:s"),
							'updateby'  => $_SESSION['standard']['userid'],
							'updatetime'=> date("Y-m-d H:i:s")
						);

						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}
						
						$query = insertQuery($dbname,'sdm_5shiftanggota',$data,$cols); 
						$owlPDO->exec($query);
					}
				}
			}else{
				exit("Warning : Daftar shift periode sebelumnya tidak ada !");
			}
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
    case'html':
		// echo"<pre>";
		// print_r($param);
		
        $tgl1 = $param['periode']."-01";
		$tgl2 = tglakhir($tgl1);
		$rangetgl = rangeTanggalarr($tgl1,$tgl2);
		if ($param['ev']=='excel') {
			$border='border=1px solid black';
		}

        $tab = "<br><br>
			<table cellpadding=3 cellspacing=1 $border class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nik2'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nama'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['jabatan'] . "</th>
            <th align=center colspan=".count($rangetgl).">" . $_SESSION['lang']['tanggal'] . "</th>
			</tr>
			<tr class=rowheader>
			";
			foreach($rangetgl as $tgl){
				$tab.="<th align=center>" . substr($tgl,-2). "</th>";
			}
		$tab.="</tr>";
		$tab.="<tr class=rowheader>";
			foreach($rangetgl as $tgl){
				$cl="style=color:white;";
				if(strtolower(date('D', strtotime($tgl)))=='sat'){
					$cl="style=color:yellow;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='sun'){
					$cl="style=color:red;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='fri'){
					$cl="style=color:#1ce809;";
				}
				$tab.="<th align=center ".$cl.">" . date('D', strtotime($tgl)). "</th>";
			}
		$tab.="</tr>";
		$tab.="</thead>";
		$where="";
		if($param['subbagian']=='UMUM'){
			$param['subbagian']="";
		}
		$where.=" and subbagian = '".$param['subbagian']."'";
		$where.=" and tanggal like '".$param['periode']."%'";
				
		$optnm = makeOption($dbname,'sdm_5mastershift','shift,namashift');
		$str = "select * from " . $dbname . ".sdm_5shift where kodeorg like '".$param['kodeorg']."%' order by namashift";
        $res = fetchdata($str);
        foreach($res as $bar){
			$dtshift[$bar['id']]=$bar['id'];
			$nmshift[$bar['id']]="<b>".$bar['shift']."</b>-".($bar['namashift']);
			$titlesh[$bar['id']]="".$bar['shift']."-".$optnm[$bar['namashift']];
			
			$shft[$bar['shift']][$bar['namashift']]=$bar['id'];
			
			$jamkerja[$bar['id']]="Masuk : ".$bar['masuk']."\nToleransi : ".$bar['toleransi']." Menit\nKeluar Ist : ".$bar['keluar_ist']."\nMasuk Ist : ".$bar['masuk_ist']."\nPulang : ".$bar['keluar'];
			
			
			
			
		}
		$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$data=array();
        $no = 0;
        $str = "select * from " . $dbname . ".sdm_5shiftanggota where kodeorg = '".$param['kodeorg']."' ".$where."";
        $res = fetchdata($str);
        foreach($res as $bar){
			$data[$bar['karyawanid']]=$bar['karyawanid'];
			$sht[$bar['karyawanid']][$bar['tanggal']]=$bar['idshift'];
			$kdsht[$bar['karyawanid']][$bar['tanggal']]=$bar['shift'];
			$nmsht[$bar['karyawanid']][$bar['tanggal']]=$bar['namashift'];
        }
		
		foreach($data as $nik){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . getKary($nik,'nik'). "</td>";
            $tab.="<td align=left>" . getKary($nik). "</td>";
            $tab.="<td align=left>" . $nmjab[getKary($nik,'kodejabatan')]. "</td>";
			$nx=0;
            foreach($rangetgl as $tgl){
				$cl="";
				if(strtolower(date('D', strtotime($tgl)))=='sat'){
					//$cl="style=color:yellow;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='sun'){
					$cl="style=color:red;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='fri'){
					//$cl="style=color:#1ce809;";
				}
				if ($param['ev']=='excel') {
					$tab.="<td align=center ".$cl." title=\"".$jamkerja[$sht[$nik][$tgl]]."\">".$kdsht[$nik][$tgl]."-".$nmsht[$nik][$tgl]."</td>";
				} else{
					$tab.="<td align=center ".$cl." title=\"".$jamkerja[$sht[$nik][$tgl]]."\">".$titlesh[$sht[$nik][$tgl]]."</td>";
				}

			}
		}

        $tab.="</tr>";
        $tab.="</table>";
		if ($param['ev']=='excel') {
			$dte = date("Hms"); 
			$nop_ = "Setup_ShiftKaryawan_".$param['kodeorg']."_".$param['periode'];
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
				}
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				} 
				fclose($handle);
			}		
		} else{
			echo $tab;
		}
	break;
    case'insert':
		try {
			$owlPDO->beginTransaction();
			$str = "select * from " . $dbname . ".sdm_5shift where kodeorg like '".$param['kodeorg']."%' order by namashift";
			$res = fetchdata($str);
			foreach($res as $bar){
				$shiftke[$bar['id']]=$bar['shift'];
				$nmshift[$bar['id']]=$bar['namashift'];			
			}
			
			foreach($param['tanggal'] as $key => $tgl){
				$sql = "select * from ".$dbname.".sdm_5shiftanggota where karyawanid='".$param['karyawanid']."' and  tanggal='".$tgl."'";
				$res = fetchdata($sql);
				if(count($res)>0){
					$str = "delete from " . $dbname . ".sdm_5shiftanggota where karyawanid='".$param['karyawanid']."' and  tanggal='".$tgl."'";
					$owlPDO->exec($str);
				}
				
				$data = array(
					'karyawanid'=> $param['karyawanid'],
					'kodeorg'   => getKary($param['karyawanid'],'lokasitugas'),
					'subbagian' => getKary($param['karyawanid'],'subbagian'),
					'bagian'    => getKary($param['karyawanid'],'bagian'),
					'idshift'   => $param['shift'][$key],
					'shift'     => $shiftke[$param['shift'][$key]],
					'namashift' => $nmshift[$param['shift'][$key]],
					'tanggal'   => $tgl,
					'createby'  => $_SESSION['standard']['userid'],
					'createtime'=> date('Y-m-d H:i:s'),
					'updateby'  => $_SESSION['standard']['userid'],
					'updatetime'=> date('Y-m-d H:i:s')
				);
				
				if($param['shift'][$key]!=''){					
					$query = insertQuery($dbname,'sdm_5shiftanggota',$data,array_keys($data));
					$owlPDO->exec($query);
				}
			}
			
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
	break;
    case'delete':

		if($param['subbagian'] == "UMUM"){
			$param['subbagian'] = "";
		}

		$sql = "select * from " . $dbname . ".sdm_5shiftanggota where kodeorg='" . $param['kodeorg'] . "' and subbagian ='" . $param['subbagian'] . "' and posting=1 and tanggal like '" . $param['periode'] . "%'";
		$res = fetchdata($sql);
		$jlhbrs = count($res);
		if ($jlhbrs > 0) {
			exit("Warning: Data sudah di posting / tutup.");
		}
		
        $str = "delete from " . $dbname . ".sdm_5shiftanggota where kodeorg='" . $param['kodeorg'] . "' and subbagian ='" . $param['subbagian'] . "' and tanggal like '" . $param['periode'] . "%'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    
    case'posting':
		$subbagian=$param['subbagian'];
    	if ($param['subbagian']=='UMUM') {
    		$subbagian='';
    	}
        $str = "update " . $dbname . ".sdm_5shiftanggota set posting='1' where kodeorg='" . $param['kodeorg'] . "' and subbagian ='" . $subbagian . "' and tanggal like '" . $param['periode'] . "%'";
        //exiterroradmin($str);
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'unposting':
		$str = "update " . $dbname . ".sdm_5shiftanggota set posting='0' where kodeorg='" . $param['kodeorg'] . "' and subbagian ='" . $param['subbagian'] . "' and tanggal like '" . $param['periode'] . "%'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'loaddatadetail':
		$tgl1 = $param['periode']."-01";
		$tgl2 = tglakhir($tgl1);
		$rangetgl = rangeTanggalarr($tgl1,$tgl2);
		
        $tab = "
			<table cellpadding=2 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nik2'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nama'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['jabatan'] . "</th>
            <th align=center colspan=".count($rangetgl).">" . $_SESSION['lang']['tanggal'] . "</th>
			</tr>
			<tr class=rowheader>
			";
			$nx=0;
			foreach($rangetgl as $tgl){
				$nx++;
				$tab.="<input hidden id=tgl_".$nx." name=tanggal[] value=".$tgl.">";
				$tab.="<th align=center>" . substr($tgl,-2). "</th>";
			}
		$tab.="</tr>";
		$tab.="<tr class=rowheader>";
			foreach($rangetgl as $tgl){
				$cl="style=color:white;";
				if(strtolower(date('D', strtotime($tgl)))=='sat'){
					$cl="style=color:yellow;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='sun'){
					$cl="style=color:red;";
				}elseif(strtolower(date('D', strtotime($tgl)))=='fri'){
					$cl="style=color:#1ce809;";
				}
				$tab.="<th align=center ".$cl.">" . date('D', strtotime($tgl)). "</th>";
			}
		$tab.="</tr>";
		$tab.="</thead>";
		$where="";
		if($param['subbagian']=='UMUM'){
			$where.=" and subbagian = ''";
		}elseif($param['subbagian']!=''){
			$where.=" and subbagian = '".$param['subbagian']."'";
		}
		if($param['departemen']!=''){
			$where.=" and bagian = '".$param['departemen']."'";
		}
		if($param['jabatan']!=''){
			$where1=" and kodejabatan = '".$param['jabatan']."'";
		}
		
		$str = "select * from " . $dbname . ".sdm_5shift where kodeorg like '".$param['kodeorg']."%' order by namashift";
        $res = fetchdata($str);
        foreach($res as $bar){
			$dtshift[$bar['id']]=$bar['id'];
			$nmshift[$bar['id']]=$bar['shift']." - ".$bar['namashift'];
			
			$shft[$bar['shift']][$bar['namashift']]=$bar['id'];
		}
		$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		
		if($param['sumber']=='upload'){
			$dt = json_decode($param['data']);
			$jlhrow = count($dt)-1;
			
			for($i=1;$i<=$jlhrow;$i++){				
				for($n=4;$n<=(count($rangetgl)+3);$n++){
					if(trim($dt[$i][1])!=''){
						$arrshift=explode("-",$dt[$i][$n]);
						$data[addzero($dt[$i][1],10)][$param['periode']."-".addzero($dt[0][$n],2)]=$shft[trim($arrshift[0])][trim($arrshift[1])];
					}
				}
			}
		}else{
			$sql = "select * from ".$dbname.".sdm_5shiftanggota where kodeorg = '".$param['kodeorg']."' ".$where." and tanggal like '".$param['periode']."%'";
			$res = fetchdata($sql);
			foreach($res as $bar){
				$data[$bar['karyawanid']][$bar['tanggal']]=$bar['idshift'];
			}
		}
		
        $no = 0;
		$where.= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl2."')";
		$where.= " and tanggalmasuk<='".$tgl2."'";
		$str = "select * from " . $dbname . ".datakaryawan where lokasitugas = '".$param['kodeorg']."' ".$where." ".$where1." order by namakaryawan asc";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px id=row".$no.">";
            $tab.="<td align=center><input hidden id=karyawanid_".$no." value=".$bar['karyawanid'].">" . $no . "</td>";
            $tab.="<td align=left>" . $bar['nik'] . "</td>";
            $tab.="<td align=left>" . $bar['namakaryawan'] . "</td>";
            $tab.="<td align=left>" . $nmjab[$bar['kodejabatan']] . "</td>";
			$nx=0;
            foreach($rangetgl as $tgl){
				$nx++;$s="";
				$optshift="<option value=''>&nbsp;</option>";
				foreach($dtshift as $shift){
					$s="";
					if($data[$bar['karyawanid']][$tgl]==$shift){
						$s="selected";
					}
					$optshift.="<option value=".$shift." ".$s.">".$nmshift[$shift]."</option>";
				}
				
				$tab.="<td align=center><select class='select2x' onchange=copyshift('".$no."','".$nx."',this.value) id=shift_".$no."_".$nx.">" . $optshift . "</select></td>";
			}
        }
        $tab.="</tr>";
        $tab.="<tr class=rowcontent><input hidden id=maxtanggal value=".abs(substr($tgl,-2)).">";
		$tab.="<td align=center colspan=".(count($rangetgl)+4)."><button onclick=simpanshift('".$no."','".$nx."'); class=mybutton>Simpan</button></td>";
        $tab.="</tr>";
        $tab.="</table>";
		
        echo $tab;
	break;
	case'formupload':
		if($param['kodeorg']==''){
			exit("Warning : Kode organisasi wajib diisi.");
		}
		if($param['periode']==''){
			exit("Warning : Periode budget wajib diisi.");
		}
		header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=formupload.csv");
		
		$where = "";
		
		$tgl1 = $param['periode']."-01";
		$tgl2 = tglakhir($tgl1);
		$rangetgl = rangeTanggalarr($tgl1,$tgl2);
		
		$tab="no,id,nik,nama,";
		foreach($rangetgl as $tgl){
			$tab.=substr($tgl,-2).",";
		}
		
		$tab.="\n";
		
		$where= " and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".$tgl2."')";
		$where.= " and tanggalmasuk<='".$tgl2."'";
		
		$where2 = "";
		if($param['subbagian']=='UMUM'){
			$where.=" and subbagian = ''";
			$where2.=" and subbagian = ''";
		}elseif($param['subbagian']!='' and $param['subbagian']!='UMUM'){
			$where.=" and subbagian = '".$param['subbagian']."'";
			$where2.=" and subbagian = '".$param['subbagian']."'";
		}
		if($param['departemen']=='UMUM'){
			$where.=" and bagian = ''";
			$where2.=" and bagian = ''";
		}elseif($param['departemen']!='' and $param['departemen']!='UMUM'){
			$where.=" and bagian = '".$param['departemen']."'";
			$where2.=" and bagian = '".$param['departemen']."'";
		}
		
		$datashift = array();
		$str2 = "select karyawanid, tanggal, shift, namashift from " . $dbname . ".sdm_5shiftanggota where kodeorg like '".$param['kodeorg']."%' ".$where2." and tanggal like '".$param['periode']."%'";
		$res2 = fetchdata($str2);
		foreach($res2 as $bar2){
			$datashift[$bar2['karyawanid']][$bar2['tanggal']] = $bar2['shift']."-".$bar2['namashift'];
		}
		
		$no = 0;
        $str = "select * from " . $dbname . ".datakaryawan where lokasitugas like '".$param['kodeorg']."%' ".$where." order by namakaryawan asc";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
			$tab.=$no.",".$bar['karyawanid'].",".$bar['nik'].",".$bar['namakaryawan']." - ".getNamaJabatan($bar['kodejabatan']);
			
			$date_str = "";
			$has_data = false;
			foreach($rangetgl as $tgl){
				$val = isset($datashift[$bar['karyawanid']][$tgl]) ? $datashift[$bar['karyawanid']][$tgl] : '';
				if($val != '') $has_data = true;
				$date_str .= ",".$val;
			}
			
			if($has_data){
				$tab .= rtrim($date_str, ',');
			}
			
			$tab.="\n";
        }
		
		echo $tab;
	break;
	case'getsubbagian':
		$optgol="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where=" and lokasitugas like '".$param['kodeorg']."%'";
		$str="select distinct subbagian from ".$dbname.".datakaryawan where 1=1 ".$where." order by subbagian asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="";
			if($param['subbagian']==$bar['subbagian']){
				//$s="selected";
			}
			if($bar['subbagian']==''){$bar['subbagian']="UMUM"; $nama='DIVISI UMUM';}else{$nama=getNamaOrg($bar['subbagian']);}
			$optgol.="<option value=".$bar['subbagian']." ".$s.">".$nama."</option>";
		}
		
		$xxx="<option value=''>".$_SESSION['lang']['all']."</option>";
		$where=" and lokasitugas like '".$param['kodeorg']."%'";
		if($param['subbagian']!=''){
			$where.=" and subbagian like '".$param['subbagian']."%'";			
		}
		$str="select distinct bagian from ".$dbname.".datakaryawan where 1=1 ".$where." order by bagian asc "; #exit("error".$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="";
			if($param['bagian']==$bar['bagian']){
				//$s="selected";
			}
			if($bar['bagian']==''){$bar['bagian']="UMUM"; $nama='DEPT UMUM';}else{$nama=getNamaDept($bar['bagian']);}
			$xxx.="<option value=".$bar['bagian']." ".$s.">".$nama."</option>";
		}
		
		echo $optgol."####".$xxx;
	break;
    case'loaddata':
        $where = "";
		$where = " and kodeorg in (".getOrgDetail(2).")";
				
		if($param['tahun']!=''){
			$where.=" and tanggal like '".$param['tahun']."%'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg = '".$param['kodeorg']."'";
		}
		
        $limit = 15;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = $_POST['page'];if ($page < 0){$page = 0;}}
		
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
        $sql = "select count(*) as jmlhrow from " . $dbname . ".sdm_5shiftanggota where 1=1 " . $where . " group by kodeorg,subbagian, substr(tanggal,1,7)";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
		
        $no = 0;
        $tab = "";
        $no = $maxdisplay;
		$colspan=10;
		
        $str = "SELECT * FROM " . $dbname . ".sdm_5shiftanggota where 1=1 " . $where . "  group by kodeorg, subbagian, substr(tanggal,1,7) order by substr(tanggal,1,7) desc, subbagian limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td>" . getNamaOrg($bar['kodeorg']). "</td>";
            $tab.="<td>" . getNamaOrg($bar['subbagian']). "</td>";
            $tab.="<td align=center>" . substr($bar['tanggal'],0,7) . "</td>";
            $tab.="<td>" . getKary($bar['updateby'],'namakaryawan') . "</td>";
			if($bar['subbagian']==''){$bar['subbagian']='UMUM';}
            if($bar['posting'] == 0) {
                $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ></td>";
                $tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ></td>";
				$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Close ???' onclick=\"posting('" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px></td><td align=center width=25px></td>";
                $tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $tab.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('preview','" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ></td>";
            $tab.="<td align=center width=25px><img  src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Download Excel' 
                    onclick=\"html('excel','" . $bar['kodeorg'] . "','" . $bar['subbagian'] . "','" . substr($bar['tanggal'],0,7) . "');\" ></td>";
            $tab.="</tr>";
        }
        
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
        break;
}
?>	
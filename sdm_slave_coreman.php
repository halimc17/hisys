<?php
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
include_once('lib/zFunction.php');
use Dompdf\Dompdf;

$param    = $_POST;
$id       = checkPostGet('id','');
$method   = checkPostGet('method','');
$tipe     = checkPostGet('tipe','');
$sumber   = checkPostGet('sumber','');
$nama     = checkPostGet('nama','');
$dept     = checkPostGet('dept','');
$tglnilai = tanggalsystemn(checkPostGet('tglnilai',''));
$thnnilai = checkPostGet('thnnilai','');
$kekuatan = checkPostGet('kekuatan','');
$kelemahan= checkPostGet('kelemahan','');
$tipeprint= checkPostGet('tipeprint','');
$penilaian= checkPostGet('penilaian','');
$jab      = getPostingJabatan('kpi'); 
$arrnilai = array(1=>"F", 2=>"A", 3=>"S", 4=>"T", 5=>"E", 6=>"R");

switch ($method) {
	case 'getDept':
		$str = "SELECT bagian FROM ".$dbname.".datakaryawan WHERE karyawanid='".$nama."'";
		$res = fetchdata($str);
		$bagian = $res[0]['bagian'];
		
		$str = "SELECT namaatasan FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE karyawanid='".$nama."' order by id desc limit 1";
		$res = fetchdata($str);
		$namaatasan = $res[0]['namaatasan'];
		
		if($namaatasan=='0000000000'){
			$str = "SELECT namaatasan FROM ".$dbname.".sdm_kpi WHERE karyawanid	='".$nama."' order by id desc limit 1";
			$res = fetchdata($str);
			$namaatasan = $res[0]['namaatasan'];
		}
		
		$str = "SELECT namaatasan FROM ".$dbname.".sdm_kpi WHERE karyawanid	='".$nama."' and tahun='".$thnnilai."' order by id desc limit 1";
		$res = fetchdata($str);
		$mm  = $res[0]['manmanagement'];	
		if($mm=='N'){
			$optjns="<option id=cv value='corevalue'>Core Values</option>";
		}else{
			$optjns="<option id=cv value='corevalue'>Core Values</option>";
			$optjns.="<option id=mm value='manmanagement'>Man Management</option>";
		}
		
		
		echo $bagian."##".$namaatasan."##".$optjns;
	break;

	case 'loadbytipe':
		if($sumber=='jenis' and $nama==''){
			exit("error : Silahkan pilih nama terlebih dahulu.");
		}
	
		if($nama!=''){
			$where = "karyawanid='".$nama."' and penilaian ='".$penilaian."' and tahun ='".$thnnilai."'";
			$str = "SELECT * FROM ".$dbname.".sdm_kpi WHERE ".$where;
			$res = fetchdata($str);
			if(count($res)==0){
				exit("error : KPI belum diinput.");
			}
			
			if($res[0]['manmanagement']=='N' and $tipe == 'manmanagement'){
				exit("error : Karyawan tidak menggunakan Man Management.");
			}
		}
		
		
		if ($tipe == 'corevalue'){
			$where = "jenis='core values'";
		} else if ($tipe == 'manmanagement'){
			$where = "jenis='man management'";
		}

		$str = "SELECT DISTINCT kode, kriteria, penilaian, namanilai, keterangan FROM ".$dbname.".sdm_5corevalues WHERE ".$where;
		$res = fetchdata($str);
		foreach($res as $val){
			$arrkriteria[$val['kode']] = $val['kriteria'];
			$arrnamanilai[$val['penilaian']] = $val['namanilai'];

			$arrketerangan[$val['kriteria']][$val['penilaian']] = $val['keterangan'];
		}
		
		// echo"<pre>";
		// print_r($arrkriteria);
		
		$str = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE id='".$param['id']."'";
		$res = fetchdata($str);
		
		if($_SESSION['standard']['userid']==$res[0]['namaatasan'] or ($_SESSION['empl']['bagian']=='HCM' and $_SESSION['empl']['tipekaryawan']=='0')){
			$penilai ='2';
			$penilaix='1';
			$disp    ="";
		}else{
			$disp    ="hidden";
			$penilaix='2';
			$penilai ='1';
		}
		$html = '<tr>
					<td colspan=2><b>Kriteria Penilaian</b></td>
					<td '.$disp.' align=center style=color:blue;><b>Nilai P'.$penilaix.'</b></td>
					<td align=center style=color:blue;><b>Nilai P'.$penilai.'</b></td>
				</tr>';
		foreach($arrkriteria as $key=>$val){
			@$no+=1;
			$html.= "<tr>
						<td>".$no.".</td>
						<td>".$val." &nbsp;</td>
						<td ".$disp."><input type=myinputtextnumber id=nilain".$key." disabled style='width:50px;text-align:right;' class='myinputtext nilain' value=0 readonly></td>
						<td><input type=myinputtextnumber id=nilai".$key." style='width:50px;text-align:right;' class='myinputtext nilai' value=0 readonly></td>
					</tr>";
		}
		$html.= "<tr>
					<td colspan=2><b>".$_SESSION['lang']['ratarata']." &nbsp;</b></td>
					<td ".$disp."><input type=myinputtextnumber id=rataratan style='width:50px;text-align:right;' class=myinputtext value=0 disabled></td>
					<td><input type=myinputtextnumber id=ratarata style='width:50px;text-align:right;' class=myinputtext value=0 disabled></td>
				</tr>";

		$tab = "<table border=0 cellpadding=5 cellspacing=1 style=width:100% class=sortable>
				<thead>
				<tr class=rowheader>
				<th align=center valign=middle rowspan=2>Kriteria Penilaian</th>
				<th align=center valign=middle colspan=5>Nilai</th>
				</tr>
				<tr class=rowheader>";

		foreach($arrnamanilai as $key=>$val){
			$tab .= "<th align=center>".$val."</th>";
		}

		$tab .= "</tr>
				</thead>
				<tbody>";

		foreach($arrkriteria as $kode=>$kriteria){
			$tab .= "<tr class=rowcontent>
						<td valign=top style='padding:10px'>".$kriteria."</td>";
			foreach($arrnamanilai as $nilai=>$namanilai){
				if($tipe == 'manmanagement'){
					$kd = $arrnilai[$kode];
				} else {
					$kd = $kode;
				}
				$tab .= "<td valign=top id='".$kd.$nilai."' class='".$kd."' name=detailkriteria[] style='padding:10px;text-align:justify;cursor:pointer;' onclick=\"fillnilai('".$kode."','".$nilai."','".count($arrkriteria)."','".$tipe."');\">".nl2br($arrketerangan[$kriteria][$nilai])."</td>";
			}
			$tab .= "</tr>";
		}

		$tab .= "</tbody>
				</table>";


		echo $html."###".$tab;
		// exit("error");
	break;

	case 'detail':
		$nmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$nmDept = makeOption($dbname,'sdm_5departemen','kode,nama');

		$str = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE id='".$id."'";
		$res = fetchdata($str);

		$str2 = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$id."'";
		$res2 = fetchdata($str2);
		foreach($res2 as $valx){
			$nilaiX[$valx['penilai']][$valx['idnilai']]=$valx['nilai'];
			$penilaiX[$valx['penilai']]=$valx['penilai'];
		}
		
		
		$nmNilai = makeOption($dbname,'sdm_5corevalues','kode,kriteria');
		$str2 = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$id."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$id."')";
		$res2 = fetchdata($str2);
		foreach($res2 as $val){
			@$no+=1;
			$td1 .= "<tr>
						<td valign=top>".$no.".</td>
						<td>".$nmNilai[$val['idnilai']]."</td>
					</tr>";
			foreach($penilaiX as $penilai){
				$td2[$penilai].= "<tr>";
				$td2[$penilai].= "<td align=center>".number_format($nilaiX[$penilai][$val['idnilai']],2)."</td>";
				$td2[$penilai].= "</tr>";
				$ratarata[$penilai]+= $nilaiX[$penilai][$val['idnilai']];
			}

			$arrColor[$val['idnilai']] = $val['idnilai'].$val['nilai'];
		}
		
		
		

		if ($res[0]['jenis'] == 'corevalue'){
			$where = "jenis='core values'";
		} else if ($res[0]['jenis'] == 'manmanagement'){
			$where = "jenis='man management'";
		}
		$query = "SELECT DISTINCT kode, kriteria, penilaian, namanilai, keterangan FROM ".$dbname.".sdm_5corevalues WHERE ".$where;
		$result = fetchdata($query);
		foreach($result as $val){
			$arrkriteria[$val['kode']] = $val['kriteria'];
			$arrnamanilai[$val['penilaian']] = $val['namanilai'];
			$arrketerangan[$val['kode']][$val['penilaian']] = $val['keterangan'];
		}

		if ($res[0]['jenis'] == "corevalue") {
			$title = "KSP Agro Core Values <br> (FASTER) Appraisal";
		} else {
			$title = "KSP Agro <br> Man Management Appraisal";
		}
		
		$arrapprov=['1'=>'Disetujui','2'=>'Ditolak','0'=>'Belum diajukan'];
		$tab = "<table style='width:100%; border: 1px solid black;' cellspacing=0 cellpadding=3>
					<tr>
						<td align=right rowspan=4 style='font-size:25px;padding-right:15px;width:40%'><b>".$title."</b></td>
						<td style='border: 1px solid black; border-width: 0 0 1px 1px; width: 150px'>".$_SESSION['lang']['nama']."</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0; width: 1px'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".$nmKar[$res[0]['karyawanid']]."</td>
						
						<td style='border: 1px solid black; border-width: 0 0 1px 1px; width: 100px'>".$_SESSION['lang']['posting']."</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0; width: 1px'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".($res[0]['posting']==1?'Posted':'Unpost')."</td>
						
					</tr>
					<tr>
						<td style='border: 1px solid black; border-width: 0 0 1px 1px;'>".$_SESSION['lang']['departemen']."</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".$nmDept[$res[0]['dept']]."</td>
						
						<td style='border: 1px solid black; border-width: 0 0 1px 1px; width: 100px'>".$_SESSION['lang']['atasan']."</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0; width: 1px'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".$nmKar[$res[0]['namaatasan']]."</td>
						
					</tr>
					<tr>
						<td style='border: 1px solid black; border-width: 0 0 1px 1px;'>Tanggal Penilaian</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".tanggalnormal($res[0]['tanggal'])."</td>
						
						<td style='border: 1px solid black; border-width: 0 0 1px 1px;'>Approval</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>:</td>
						<td style='border: 1px solid black; border-width: 0 0 1px 0;'>".$arrapprov[$res[0]['approval']]."</td>
					</tr>
					<tr>
						<td style='border: 1px solid black; border-width: 0 0 0 1px;'>Tahun Penilaian</td>
						<td>:</td>
						<td>".$res[0]['tahun']."</td>

						<td></td>
						<td></td>
						<td></td>
					</tr>
				</table>";

		$tab .= "<table border=1 style='width:100%; border: 0.5px solid black; border-collapse: collapse;' cellspacing=0 cellpadding=5>
		
					<thead>
						<tr>
							<th align=left style='width:30%'>Kriteria Penilaian</th>";
							foreach($penilaiX as $penilai){
								$tab .= "<th style='width:5%'>Nilai ".$penilai."</th>";
							}
							$tab .= "<th style='width:35%'>Kekuatan (Strength)</th>
							<th style='width:30%'>Kelemahan (Weakness)</th>
						</tr>
					</thead>
					<tbody>
						<td>
							<table>
								".$td1."
								<tr>
									<td colspan=2><b>".strtoupper($_SESSION['lang']['ratarata'])."</b></td>
								</tr>
							</table>
						</td>";
						foreach($penilaiX as $penilai){							
							$tab .= "<td align=center>
								<table>
									".$td2[$penilai]."
									<tr>
										<td align=center><b>".number_format($ratarata[$penilai]/count($res2),2)."</b></td>
									</tr>
								</table>
							</td>";
						}
						
						$tab .= "<td valign=top>".nl2br($res[0]['kekuatan'])."</td>
						<td valign=top>".nl2br($res[0]['kelemahan'])."</td>
					</tbody>
				</table>";

		$fontsize = '';
		if($tipeprint == 'pdf'){
			$fontsize = 'font-size: 10px';
		}

		$tab .= "<div class=detailfix>
				<table border=1 cellpadding=5 cellspacing=1 style='width:100%; border: 1px solid black; border-collapse: collapse;".$fontsize."'>
				<thead>
				<tr>
				<th align=center valign=middle rowspan=2>Kriteria Penilaian</th>
				<th align=center valign=middle colspan=5>Penilaian</th>
				</tr>
				<tr>";

		foreach($arrnamanilai as $key=>$val){
			$tab .= "<th align=center>".$val."</th>";
		}

		$tab .= "</tr>
				</thead>
				<tbody>";

		foreach($arrColor as $kode=>$val){
			$tab .= "<tr><td valign=top style='padding:10px'>".$arrkriteria[$kode]."</td>";
			foreach($arrnamanilai as $nilai=>$namanilai){
				if($kode.$nilai == $val){
					$tab .= "<td valign=top style='padding:10px;text-align:left;cursor:pointer;color:red;'>".nl2br($arrketerangan[$kode][$nilai])."</td>";
				} else {
					$tab .= "<td valign=top style='padding:10px;text-align:left;cursor:pointer;color:black'>".nl2br($arrketerangan[$kode][$nilai])."</td>";
				}
			}
			$tab .= "</tr>";
		}

		$tab .= "</tbody>
				</table>
				</div>";

		if ($tipeprint == 'pdf') {
	    	$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("LAPORAN CORE VALUES & MAN MANAGEMENT", array("Attachment" => false));
		} else {
			echo $tab;
		}
	break;

	case 'insert':
		try {
            $owlPDO->beginTransaction();
			
			$where = "karyawanid='".$nama."' and penilaian ='".$penilaian."' and tahun ='".$thnnilai."' and posting ='1'";
			$str = "SELECT * FROM ".$dbname.".sdm_kpi WHERE ".$where;
			$res = fetchdata($str);
			if(count($res)==0){
				throw new PDOException("KPI belum diinput atau diposting.");
			}
			
			
			$str = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE karyawanid='".$nama."' and penilaian='".$penilaian."' and tahun='".$thnnilai."' and jenis='".$tipe."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah ada, dan dibuat oleh ".getNamaKaryawan($res[0]['createby']).".");
			}
			if($_SESSION['standard']['userid']==$param['atasan']){
				throw new PDOException("Nama atasan tidak boleh sama dengan pembuat.");
			}
            $data = array(
				'id'        =>'',
				'tahun'     =>$thnnilai,
				'penilaian' =>$penilaian,
				'jenis'     =>$tipe,
				'karyawanid'=>$nama,
				'dept'      =>$dept,
				'tanggal'   =>$tglnilai,
				'kekuatan'  =>$kekuatan,
				'kelemahan' =>$kelemahan,
				'namaatasan' =>$param['atasan'],
				'createby'  =>$_SESSION['standard']['userid'],
				'createtime'=>date('Y-m-d H:i:s'),
				'updateby'  =>$_SESSION['standard']['userid'],
				'updatetime'=>date('Y-m-d H:i:s')
			);

           	$queryH = insertQuery($dbname,'sdm_corevalueandmanmanagement',$data,array_keys($data));
			
			$owlPDO->exec($queryH);

			$str = "SELECT id FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE jenis='".$tipe."' ORDER BY id DESC LIMIT 1";
			$res = fetchdata($str);
			$id = $res[0]['id'];
			
			if($_SESSION['standard']['userid']==$param['atasan']){
				$penilai='2';
			}else{
				$penilai='1';
			}
			
			foreach($arrnilai as $key=>$val){
				if ($tipe == 'corevalue') {
					$str = "INSERT INTO ".$dbname.".sdm_corevalueandmanmanagement_dt (id, idnilai, nilai, createby, createtime,penilai)
					VALUES ('".$id."', '".$val."', '".$param['nilai'][$val]."', '".$_SESSION['standard']['userid']."', '".date('Y-m-d')."','1')"; 
					$owlPDO->exec($str);
                	
					#insert pertama nilai p2 = nilai p1
					$str = "INSERT INTO ".$dbname.".sdm_corevalueandmanmanagement_dt (id, idnilai, nilai, createby, createtime,penilai)
					VALUES ('".$id."', '".$val."', '".$param['nilai'][$val]."', '".$_SESSION['standard']['userid']."', '".date('Y-m-d')."','2')"; 
                	$owlPDO->exec($str);
			
				} else {
					if($key > 5) {
						continue;
					}
					$str = "INSERT INTO ".$dbname.".sdm_corevalueandmanmanagement_dt (id, idnilai, nilai, createby, createtime,penilai)
							VALUES ('".$id."', '".$key."', '".$param['nilai'][$key]."', '".$_SESSION['standard']['userid']."', '".date('Y-m-d')."','1')"; 
                	$owlPDO->exec($str);
					
					$str = "INSERT INTO ".$dbname.".sdm_corevalueandmanmanagement_dt (id, idnilai, nilai, createby, createtime,penilai)
							VALUES ('".$id."', '".$key."', '".$param['nilai'][$key]."', '".$_SESSION['standard']['userid']."', '".date('Y-m-d')."','2')"; 
                	$owlPDO->exec($str);
				}
			}
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning: " . addslashes($e->getMessage());
        }
	break;

	case 'loaddata':
		$_SESSION['kpi']=[];
		$where = "";
		if ($tipe != '') {
			$where .= " AND jenis='".$tipe."'";
		}
		if ($nama != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".trim($nama)."%')";
		}
		if ($param['unit'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas like '%".trim($param['unit'])."%')";
		}
		if ($param['golongan'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where kodegolongan = '".trim($param['golongan'])."')";
		}
		if ($param['penilaian'] != '') {
			$where .= " AND penilaian='".$param['penilaian']."'";
		}
		if ($param['posting'] != '') {
			$where .= " AND posting='".$param['posting']."'";
		}
		if ($dept != '') {
			$where .= " AND dept='".$dept."'";
		}
		// if ($thnnilai != ''){
			// $where .= " AND tahun='".$thnnilai."'";
		// }
		
		#jika sumbernya dari approval kita by id yang harus disetujui saja
		if(!empty($_SESSION['approval']['cvmm'])){
			foreach($_SESSION['approval']['cvmm'] as $key => $value){
				$notr[$value['notransaksi']]=$value['notransaksi'];
			}
			$where .= "and id in ('".implode("','",$notr)."')";
		}else{			
			# jika dept HCM dan tipe kary staff dan lokasi tugas RO atau HO
			$userhcm=[];
			$str = "select * from ".$dbname.".setup_parameterappl where kodeparameter='KPI'";
			$req = fetchdata($str);
			foreach($req as $val){
				$arrusertemp=explode(",",$val['nilai']);				
				foreach($arrusertemp as $uname){					
					$userhcm[$uname]=$uname;
				}
			}
			if($userhcm[$_SESSION['standard']['userid']]!=''){
				if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				}elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
					$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas not like '%HO')";
				}else{
					$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas = '".$_SESSION['empl']['lokasitugas']."')";
				}
			}else{
				$where .= " AND (karyawanid='".$_SESSION['standard']['userid']."' or createby='".$_SESSION['standard']['userid']."' or (namaatasan='".$_SESSION['standard']['userid']."' and posting=1))";
				if ($thnnilai == ''){
					$where .= " AND tahun='".date('Y')."'";
				}	
			}
			if ($thnnilai != ''){
				$where .= " AND tahun='".$thnnilai."'";
			}
		}
		
		
		$tab = "<br>
				<table border=0 cellspacing=1 cellpadding=5 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['tahun']."</th>
							<th align=center>".$_SESSION['lang']['penilaian']."</th>
							<th align=center>".$_SESSION['lang']['jenis']."</th>
							<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
							<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
							<th align=center>".$_SESSION['lang']['jabatan']."</th>
							<th align=center>".$_SESSION['lang']['kodegolongan']."</th>
							<th align=center>".$_SESSION['lang']['departemen']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['atasan']."</th>
							<th align=center>".$_SESSION['lang']['status']."</th>
							<th align=center>Kekuatan</th>
							<th align=center>kelemahan</th>
							<th align=center>".$_SESSION['lang']['ratarata']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['updateby']."</th>
							<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
					<tbody>";

        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 19;
		
        $str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE 1=1 ".$where; 
        $res = fetchdata($str);
		$jlhbrs = $res['0']['jmlhrow'];
       
		$arrstatus=array('0'=>'','1'=>'Disetujui','2'=>'Ditolak');
		
		$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmjab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$nmDept= makeOption($dbname, 'sdm_5departemen', 'kode,nama');
		
		//$where.="and id='518'";
		
		$str = "SELECT * FROM ".$dbname.".sdm_corevalueandmanmanagement
				WHERE 1=1 ".$where."
				ORDER BY tahun desc, id desc
				LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);
		
		$arrdata=array(
			'corevalue'=>'Core Values',
			'manmanagement'=>'Man Management'
		);
		
        $no = $offset+1;
		foreach($res as $key=>$val){
			$query = "SELECT AVG(nilai) as nilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$val['id']."' and penilai in (SELECT max(penilai) as penilai FROM ".$dbname.".sdm_corevalueandmanmanagement_dt WHERE id='".$val['id']."') GROUP BY id";
			$rata2 = fetchdata($query);

			$tab .= "<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=center>".$val['tahun']."</td>
						<td align=center>".$val['penilaian']."</td>
						<td nowrap>".$arrdata[$val['jenis']]."</td>
						<td align=center>".getKary($val['karyawanid'],'lokasitugas')."</td>
						<td>".getKary($val['karyawanid'])."</td>
						<td>".$nmjab[getKary($val['karyawanid'],'kodejabatan')]."</td>
						<td align=center>".$nmgol[getKary($val['karyawanid'],'kodegolongan')]."</td>
						<td>".$nmDept[$val['dept']]."</td>
						<td nowrap align=center>".tanggalnormal($val['tanggal'])."</td>
						<td>".getKary($val['namaatasan'])."</td>
						<td style=color:blue;cursor:pointer; onclick=gethistoriapproval(".$val['id'].")>".$arrstatus[$val['approval']]."</td>
						<td>".nl2br($val['kekuatan'])."</td>
						<td>".nl2br($val['kelemahan'])."</td>
						<td align=right>".number_format($rata2[0]['nilai'],2)."</td>
						<td align=center style=font-size:10px;>".getNamaKaryawan($val['createby'])."<br>".tanggalnormald($val['createtime'])."</td>
						<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['updatetime'])."</td>";
						
						if($val['namaatasan']==$_SESSION['standard']['userid']){
							if($val['posting']=='1' and $val['approval']=='0'){							
								$tab.="<td align=center><button style=color:red;border-color:red; class=mybutton title='Verifikasi' onclick=\"fillField('".$val['id']."');\">Verify</button></td>";
								
								$tab.="<td align=center colspan=2><button style=color:green;border-color:green; class=mybutton title='Approve' onclick=\"approve('".$val['id']."');\">Approve</button></td>";
							}elseif($val['posting']=='0'){
								$tab.="<td align=center></td>";
								$tab.="<td align=center></td>";
								$tab.="<td align=center><img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' onclick='posting(".$val['id'].");'></td>";
							}else{
								$tab.="<td align=center></td>";
								$tab.="<td align=center></td>";
								$tab.="<td align=center></td>";
							}
						}elseif($val['posting']=='1'){
							$tab.="<td align=center></td>";
							$tab.="<td align=center></td>";
							if(in_array($_SESSION['empl']['jabatan'],$jab)){
								$icon="images/icons/04/16/04.png";
								$title="Unposting";
								$unpost=" onclick=\"unposting('".$val['id']."');\" ";
							}else {
								$icon="images/icons/04/16/02.png";
								$title="Posted";
								$unpost='';
							}
							$tab.="<td align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
						}else{						
							$tab.="<td align=center>
								<img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"fillField('".$val['id']."');\">
							</td>";
							$tab.="<td align=center>
								<img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['id']."');\">
							</td>";
							$tab.="<td align=center>
								<img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' onclick='posting(".$val['id'].");'>
							</td>";
						}
						
						$tab.="<td align=center>
							<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['id']."');\">
						</td>
						<td align=center>
							<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['id']."');\">
						</td>
					</tr>";
            $no += 1;
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'reject':
		$data = array(
			'status'   => '2',
			'komentar'   => $param['komentar'],
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['id']."' and jenispersetujuan='CVMM' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '2',
			'posting'   => '0',
			'updatetime'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_corevalueandmanmanagement',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'approve':
		$data = array(
			'status'   => '1',
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['id']."' and jenispersetujuan='CVMM' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '1',
			'updatetime'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_corevalueandmanmanagement',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'posting':
		$str = "delete from ".$dbname.".approval WHERE notransaksi = '".$param['id']."' and jenispersetujuan='CVMM'"; #exit("error".$str);
		$owlPDO->exec($str);
			
			
		$str = "SELECT namaatasan FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE id='".$param['id']."'";
		$res = fetchdata($str);
		$param['namaatasan']=$res[0]['namaatasan'];
		
		$data = array(
			'notransaksi'     => $param['id'],
			'jenispersetujuan'=> 'CVMM',
			'level'           => '1',
			'karyawanid'      => $param['namaatasan'],
			'status'          => '0'
		);

		$queryH = insertQuery($dbname,'approval',$data,array_keys($data)); #exit("error".$queryH);
		$owlPDO->exec($queryH);
		
		$data = array(
			'posting'   => '1',
			'approval'   => '0',
			'updatetime'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_corevalueandmanmanagement',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'unposting':
		$data = array(
			'posting'   => '0',
			'updatetime'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['id']."'";
		$query = updateQuery($dbname,'sdm_corevalueandmanmanagement',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'fillField':
		$str = "SELECT a.namaatasan, a.penilaian, a.id, a.jenis, a.karyawanid, a.dept, a.tahun, a.tanggal, a.kekuatan, a.kelemahan, b.idnilai, b.nilai, b.penilai
				FROM ".$dbname.".sdm_corevalueandmanmanagement a 
				JOIN ".$dbname.".sdm_corevalueandmanmanagement_dt b ON a.id=b.id
				WHERE a.id='".$id."' order by penilai asc";
		$res = fetchdata($str);
		if($_SESSION['standard']['userid']==$res[0]['namaatasan'] or ($_SESSION['empl']['bagian']=='HCM' and $_SESSION['empl']['tipekaryawan']=='0')){
			$penilai='2';
		}else{
			$penilai='1';
		}
		
		$fill = $res[0]['jenis']."###".$res[0]['karyawanid']."###".$res[0]['dept']."###".tanggalnormal($res[0]['tanggal'])."###".$res[0]['tahun']."###".$res[0]['kekuatan']."###".$res[0]['kelemahan']."###".$res[0]['penilaian']."###".$res[0]['namaatasan'];
		foreach($res as $val){
			if($penilai=='1'){
				if($val['penilai']=='1'){
					$fill .= "###".$val['idnilai'].$val['nilai'];
				}
			}else{				
				if($val['penilai']=='2'){
					$fill .= "###".$val['idnilai'].$val['nilai'];
				}elseif($val['penilai']=='1'){
					$fill .= "###".$val['idnilai'].$val['nilai'];
				}
			}
		}
		if($penilai=='1'){
			foreach($res as $val){
				if($val['penilai']=='1'){
					$fill .= "###".$val['idnilai'].$val['nilai'];
				}
			}	
		}
		echo $fill;
		// exit("error".$fill);
	break;

	case 'update':
	try {
		$owlPDO->beginTransaction();
		
		if($_SESSION['standard']['userid']==$param['atasan']){
			$penilai='2';
		}else{
			$penilai='1';
		}
		$str = "SELECT id FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE id='".$id."'";
		$res = fetchdata($str);
		if($res[0]['posting']=='1'){
			throw new PDOException("Data sudah disetujui.");
		}
		if($res[0]['posting']=='9' and $penilai!='2'){
			throw new PDOException("Data dalam proses persetujuan.");
		}
		
        $str = "UPDATE ".$dbname.".sdm_corevalueandmanmanagement SET 
        		karyawanid='".$nama."', dept='".$dept."', tanggal='".$tglnilai."', tahun='".$thnnilai."', kekuatan='".$kekuatan."', 
        		kelemahan='".$kelemahan."',penilaian='".$penilaian."', updateby='".$_SESSION['standard']['userid']."'
        		WHERE id='".$id."'";
		$owlPDO->exec($str);
		
		
		foreach($arrnilai as $key=>$val){
			if ($tipe == 'corevalue') {
				$str = "UPDATE ".$dbname.".sdm_corevalueandmanmanagement_dt SET 
						nilai='".$param['nilai'][$val]."', updateby='".$_SESSION['standard']['userid']."'
						WHERE id='".$id."' AND idnilai='".$val."' and penilai='".$penilai."'";
				$owlPDO->exec($str);
				
				if($penilai=='1'){
					$str = "UPDATE ".$dbname.".sdm_corevalueandmanmanagement_dt SET 
						nilai='".$param['nilai'][$val]."', updateby='".$_SESSION['standard']['userid']."'
						WHERE id='".$id."' AND idnilai='".$val."' and penilai='2'";
					$owlPDO->exec($str);
				}
			} else {
				if($key > 5) {
					continue;
				}
				$str = "UPDATE ".$dbname.".sdm_corevalueandmanmanagement_dt SET 
						nilai='".$param['nilai'][$key]."', updateby='".$_SESSION['standard']['userid']."'
						WHERE id='".$id."' AND idnilai='".$key."' and penilai='".$penilai."'";
				$owlPDO->exec($str);
				if($penilai=='1'){
					$str = "UPDATE ".$dbname.".sdm_corevalueandmanmanagement_dt SET 
						nilai='".$param['nilai'][$key]."', updateby='".$_SESSION['standard']['userid']."'
						WHERE id='".$id."' AND idnilai='".$key."' and penilai='2'";
					$owlPDO->exec($str);
				}
			}
		}
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;

	case 'hapus':
		$str = "DELETE FROM ".$dbname.".sdm_corevalueandmanmanagement WHERE id = '".$id."' ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
}

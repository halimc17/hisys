<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include('lib/zLib.php');

$method = checkPostGet('method','');
$myid = checkPostGet('myid','');
$pages = checkPostGet('page','');

$pt = checkPostGet('pt','');
$gudang = checkPostGet('gudang','');
$kelompokbarang = checkPostGet('kelompokbarang','');
$barang = checkPostGet('barang','');
$minstok = checkPostGet('minstok','');
$maxstok = checkPostGet('maxstok','');
$satuan = checkPostGet('satuan','');

$crpt = checkPostGet('crpt','');
$crgudang = checkPostGet('crgudang','');
$crklbarang = checkPostGet('crklbarang','');
$crbarang = checkPostGet('crbarang','');

#detail akses: hanya unit (dan gudang di bawahnya) yang boleh diakses user
$unitakses = getOrgDetail(2);
$gudangakses = "select kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANG' and induk in (".$unitakses.")";

#filter list (dipakai list & template excel), sudah dibatasi detail akses
$wherelist = "m.gudang in (".$gudangakses.")";
if($crpt!=""){
	$wherelist .= " and m.pt='".$crpt."'";
}
if($crgudang!=""){
	$wherelist .= " and m.gudang='".$crgudang."'";
}
if($crklbarang!=""){
	$wherelist .= " and m.kodekelompok='".$crklbarang."'";
}
if($crbarang!=""){
	$wherelist .= " and m.kodebarang='".$crbarang."'";
}

#validasi stok min/max (max 0 = tanpa batas maksimum)
function validasiStok(&$minstok,&$maxstok){
	if($minstok==''){ $minstok=0; }
	if($maxstok==''){ $maxstok=0; }
	if(!is_numeric($minstok) || !is_numeric($maxstok) || $minstok<0 || $maxstok<0){
		exit("Warning : Stok minimum dan maksimum harus berupa angka.");
	}
	if($maxstok>0 && $minstok>$maxstok){
		exit("Warning : Stok minimum tidak boleh lebih besar dari stok maksimum.");
	}
}

switch($method){
	case'getgudang':
		$optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".organisasi where tipe='GUDANG' and induk in (".$unitakses.") and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodeorganisasi']==$gudang){
				$optgudang.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
			}else{
				$optgudang.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
			}
		}
		echo $optgudang;
	break;

	case'getcrgudang':
		$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where tipe='GUDANG' and induk in (".$unitakses.") and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$crpt."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$optgudang.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		echo $optgudang;
	break;

	case'getbarang':
		$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='".$kelompokbarang."' and inactive='0'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodebarang']==$barang){
				$optbarang.="<option value='".$val['kodebarang']."' selected>".$val['kodebarang']." - ".$val['namabarang']."</option>";
			}else{
				$optbarang.="<option value='".$val['kodebarang']."'>".$val['kodebarang']." - ".$val['namabarang']."</option>";
			}
		}
		echo $optbarang;
	break;

	case'getcrbarang':
		$optbarang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='".$crklbarang."' and inactive='0'";
		$res=fetchdata($str);
		foreach($res as $val){
			$optbarang.="<option value='".$val['kodebarang']."'>".$val['kodebarang']." - ".$val['namabarang']."</option>";
		}
		echo $optbarang;
	break;

	case'getsatuan':
		$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$barang."'";
		$res=fetchdata($str);
		echo $res[0]['satuan'];
	break;

	case 'loaddata':
		$tab="";

		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0 style='margin-left:5px;width:1000px;table-layout:fixed;'>
			<thead>
			<tr class=rowheader style='font-weight:bold'>
				<td style='text-align:center;width:150px'>".$_SESSION['lang']['pt']."</td>
				<td style='text-align:center;width:180px'>".$_SESSION['lang']['gudang']."</td>
				<td style='text-align:center;width:150px'>".$_SESSION['lang']['kelompokbarang']."</td>
				<td style='text-align:center;width:250px'>".$_SESSION['lang']['barang']."</td>
				<td style='text-align:center;width:70px'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center;width:80px'>".$_SESSION['lang']['minstok']."</td>
				<td style='text-align:center;width:80px'>".$_SESSION['lang']['maxstok']."</td>
				<td style='text-align:center;width:40px'>Action</td></tr>
			 </thead>
			 <tbody>";

		$where = $wherelist;

		$limit=20;
		$page=0;
		if(isset($pages))
		{
			$page=$pages;
			if($page<0)
				$page=0;
		}
		$offset=$page*$limit;

		$str="select count(*) as jml from ".$dbname.".log_5minimunstok m where ".$where;
		$res=fetchdata($str);
		$jlhbrs=(int)$res[0]['jml'];
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$str="select m.*, o.namaorganisasi as namapt, g.namaorganisasi as namagudang, k.kelompok as namakelompok, b.namabarang
				from ".$dbname.".log_5minimunstok m
				left join ".$dbname.".organisasi o on o.kodeorganisasi=m.pt
				left join ".$dbname.".organisasi g on g.kodeorganisasi=m.gudang
				left join ".$dbname.".log_5klbarang k on k.kode=m.kodekelompok
				left join ".$dbname.".log_5masterbarang b on b.kodebarang=m.kodebarang
				where ".$where." order by m.pt asc, m.kodekelompok asc, m.kodebarang desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $key=>$val){
				$tab.="<tr class=rowcontent>
					<td style='text-align:center;word-wrap:break-word'>".$val['namapt']."</td>
					<td style='text-align:left;word-wrap:break-word'>".$val['namagudang']."</td>
					<td style='text-align:left;word-wrap:break-word'>".$val['namakelompok']."</td>
					<td style='text-align:left;word-wrap:break-word'>".$val['namabarang']."</td>
					<td style='text-align:center'>".$val['satuan']."</td>
					<td style='text-align:right'>".hidezerodecimal($val['stok'],2)."</td>
					<td style='text-align:right'>".hidezerodecimal($val['stokmax'],2)."</td>
					<td style='text-align:center'>
						<img src=images/skyblue/edit.png class=resicon caption='Edit' onclick=\"editfield('".$val['id']."','".$val['pt']."','".$val['gudang']."','".$val['kodekelompok']."','".$val['kodebarang']."','".$val['satuan']."','".$val['stok']."','".$val['stokmax']."');\">
					</td>
				</tr>";
			}

			@$totrows=ceil($jlhbrs/$limit);
			if($totrows==0){
				$totrows=1;
			}

			$isiRow='';
			for($er=1;$er<=$totrows;$er++){
				$sel = ($page==$er-1)? 'selected': '';
				$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
			}

			@$frompage = (($page*$limit)+1);
			if(@(($page+1)*$limit) > $jlhbrs){
				$topage = $jlhbrs;
			}else{
				$topage = @(($page+1)*$limit);
			}
			$tab.="<tr>
				<td colspan=8 align=center>
					".$frompage." to ".$topage." Of ".  $jlhbrs."
				</td>
			</tr>
			<tr>
				<td colspan=8 align=center>";

			if($page=='0'){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=loaddata(".@($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}

			$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";

			if(@($page+1) == $totrows){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=loaddata(".@($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
			$tab.="</td></tr>";
		}
		$tab.="</tbody>
		</table>";
		echo $tab;
	break;

	case'getkary':
		$where='';
		if($kodeunit!=''){
			$where.=" and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=''){
			$where.=" and jenispersetujuan='".$jenispersetujuan."'";
		}

		$whr='';
		if($departemen!=''){
			$whr.=" and bagian='".$departemen."'";
		}
		if($jabatan!=''){
			$whr.=" and kodejabatan='".$jabatan."'";
		}

		if($tipekaryawan!=''){
			if($tipekaryawan==0){
				$whr.=" and tipekaryawan=9";
			} else if($tipekaryawan==9){
				$whr.=" and tipekaryawan=10";
			} else if($tipekaryawan==10){
				$whr.=" and tipekaryawan in (7,8)";
			}else{
				$whr.=" and tipekaryawan not in ('1','4','5','6')";
			}
		}else{
			$whr.=" and tipekaryawan not in ('1','4','5','6')";
		}

		$str = "select karyawanid,namakaryawan, lokasitugas from  " . $dbname . ".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid not in (select karyawanid from  " . $dbname . ".setup_approval where 1=1 ".$where.") ".$whr." order by namakaryawan asc ";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optkar.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$optkar.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " - ".$bar['lokasitugas']."</option>";
		}

	echo $optkar;
	break;

	case 'downloadTemplate':
		require_once 'dompdf/PHPExcel.php';
		require_once 'dompdf/PHPExcel/IOFactory.php';
		$objPHPExcel = new PHPExcel();
		$ws = $objPHPExcel->setActiveSheetIndex(0);
		$judul = array('A'=>'Kode PT','B'=>'Kode Gudang','C'=>'Kode Barang','D'=>'Nama Barang','E'=>'Satuan','F'=>'Stok Minimum','G'=>'Stok Maksimum');
		$lebar = array('A'=>10,'B'=>14,'C'=>14,'D'=>45,'E'=>10,'F'=>14,'G'=>14);
		foreach($judul as $col=>$txt){
			$ws->setCellValue($col.'1',$txt);
			$ws->getColumnDimension($col)->setWidth($lebar[$col]);
		}
		$ws->getStyle('A1:G1')->getFont()->setBold(true);

		$str="select m.pt, m.gudang, m.kodebarang, m.stok, m.stokmax, b.namabarang, b.satuan
			from ".$dbname.".log_5minimunstok m
			left join ".$dbname.".log_5masterbarang b on b.kodebarang=m.kodebarang
			where ".$wherelist." order by m.pt asc, m.gudang asc, m.kodebarang asc";
		$res=fetchdata($str);
		$row=2;
		foreach($res as $val){
			$ws->setCellValueExplicit('A'.$row,$val['pt'],PHPExcel_Cell_DataType::TYPE_STRING);
			$ws->setCellValueExplicit('B'.$row,$val['gudang'],PHPExcel_Cell_DataType::TYPE_STRING);
			$ws->setCellValueExplicit('C'.$row,$val['kodebarang'],PHPExcel_Cell_DataType::TYPE_STRING);
			$ws->setCellValueExplicit('D'.$row,$val['namabarang'],PHPExcel_Cell_DataType::TYPE_STRING);
			$ws->setCellValueExplicit('E'.$row,$val['satuan'],PHPExcel_Cell_DataType::TYPE_STRING);
			$ws->setCellValueExplicit('F'.$row,$val['stok'],PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$ws->setCellValueExplicit('G'.$row,$val['stokmax'],PHPExcel_Cell_DataType::TYPE_NUMERIC);
			$row++;
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="template_minstok.xlsx"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
		$objWriter->save('php://output');
		exit;
	break;

	case 'uploadData':
		if(!isset($_FILES['file']) || $_FILES['file']['error']!=0){
			exit("Warning : Terjadi error pada file upload.");
		}
		if(strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION))!='xlsx'){
			exit("Warning : Format file upload harus .xlsx");
		}
		require_once 'dompdf/PHPExcel.php';
		require_once 'dompdf/PHPExcel/IOFactory.php';
		try{
			$load = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
		}catch(Exception $e){
			exit("Warning : File tidak dapat dibaca, pastikan file .xlsx dari template.");
		}
		$sheets = $load->getActiveSheet()->toArray(null,true,false,true);
		if(stripos((string)@$sheets[1]['B'],'gudang')===false || stripos((string)@$sheets[1]['C'],'barang')===false){
			exit("Warning : Format kolom tidak sesuai template. Gunakan tombol Download Template.");
		}

		#A:PT, D:Nama Barang, E:Satuan hanya informasi (PT, kelompok, satuan diambil dari master)
		$cachegudang = $cachebarang = $seen = $rows = $errors = array();
		foreach($sheets as $no=>$sheet){
			if($no==1){
				continue;
			}
			$g = trim((string)$sheet['B']);
			$kb = trim((string)$sheet['C']);
			$mn = trim((string)$sheet['F']);
			$mx = trim((string)$sheet['G']);
			if($g=='' && $kb=='' && $mn=='' && $mx==''){
				continue;
			}
			if($g=='' || $kb==''){
				$errors[] = "Baris ".$no.": Kode Gudang dan Kode Barang wajib diisi.";
				continue;
			}
			if(!isset($cachegudang[$g])){
				$rg = fetchdata("select g.kodeorganisasi, u.induk as pt from ".$dbname.".organisasi g join ".$dbname.".organisasi u on u.kodeorganisasi=g.induk where g.kodeorganisasi=".$owlPDO->quote($g)." and g.tipe='GUDANG' and g.induk in (".$unitakses.")");
				$cachegudang[$g] = empty($rg) ? false : $rg[0]['pt'];
			}
			if($cachegudang[$g]===false){
				$errors[] = "Baris ".$no.": Gudang ".$g." tidak ditemukan atau Anda tidak memiliki akses.";
				continue;
			}
			if(!isset($cachebarang[$kb])){
				$rb = fetchdata("select kodebarang, kelompokbarang, satuan from ".$dbname.".log_5masterbarang where kodebarang=".$owlPDO->quote($kb)." and inactive='0'");
				$cachebarang[$kb] = empty($rb) ? false : $rb[0];
			}
			if($cachebarang[$kb]===false){
				$errors[] = "Baris ".$no.": Barang ".$kb." tidak ditemukan atau tidak aktif.";
				continue;
			}
			if($mn==''){ $mn=0; }
			if($mx==''){ $mx=0; }
			if(!is_numeric($mn) || !is_numeric($mx) || $mn<0 || $mx<0){
				$errors[] = "Baris ".$no.": Stok minimum/maksimum harus berupa angka.";
				continue;
			}
			if($mx>0 && $mn>$mx){
				$errors[] = "Baris ".$no.": Stok minimum tidak boleh lebih besar dari stok maksimum.";
				continue;
			}
			if(isset($seen[$g."|".$kb])){
				$errors[] = "Baris ".$no.": Gudang ".$g." dan Barang ".$kb." dobel dengan baris ".$seen[$g."|".$kb].".";
				continue;
			}
			$seen[$g."|".$kb] = $no;
			$rows[] = array('gudang'=>$g,'pt'=>$cachegudang[$g],'kodebarang'=>$cachebarang[$kb]['kodebarang'],'kelompok'=>$cachebarang[$kb]['kelompokbarang'],'satuan'=>$cachebarang[$kb]['satuan'],'min'=>(float)$mn,'max'=>(float)$mx);
		}

		if(!empty($errors)){
			$msg = "Warning : Upload dibatalkan, tidak ada data yang disimpan. Perbaiki ".count($errors)." baris berikut:\n".implode("\n",array_slice($errors,0,15));
			if(count($errors)>15){
				$msg .= "\n... dan ".(count($errors)-15)." baris lainnya.";
			}
			exit($msg);
		}
		if(empty($rows)){
			exit("Warning : Tidak ada data pada file.");
		}

		$baru = $ubah = $tetap = 0;
		$now = date('Y-m-d H:i');
		$user = $_SESSION['standard']['userid'];
		try{
			$owlPDO->beginTransaction();
			foreach($rows as $r){
				$ada = fetchdata("select id, stok, stokmax from ".$dbname.".log_5minimunstok where gudang='".$r['gudang']."' and kodebarang='".$r['kodebarang']."'");
				if(empty($ada)){
					$owlPDO->exec("insert into ".$dbname.".log_5minimunstok (pt,gudang,kodekelompok,kodebarang,satuan,stok,stokmax,createby,createtime,updateby,updatetime) values ('".$r['pt']."','".$r['gudang']."','".$r['kelompok']."','".$r['kodebarang']."','".$r['satuan']."','".$r['min']."','".$r['max']."','".$user."','".$now."','".$user."','".$now."')");
					$baru++;
				}elseif((float)$ada[0]['stok']==$r['min'] && (float)$ada[0]['stokmax']==$r['max']){
					$tetap++;
				}else{
					$owlPDO->exec("update ".$dbname.".log_5minimunstok set stok='".$r['min']."',stokmax='".$r['max']."',updateby='".$user."',updatetime='".$now."' where id='".$ada[0]['id']."'");
					$ubah++;
				}
			}
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			exit("Gagal, ".addslashes($e->getMessage()));
		}
		echo "Berhasil upload: ".$baru." data baru, ".$ubah." data diperbarui, ".$tetap." data tidak berubah.";
	break;

	case 'update':
		$res=fetchdata("select gudang from ".$dbname.".log_5minimunstok where id='".$myid."' and gudang in (".$gudangakses.")");
		if(empty($res)){
			exit("Warning : Anda tidak memiliki akses ke data ini.");
		}
		validasiStok($minstok,$maxstok);
		$str="update ".$dbname.".log_5minimunstok set stok='".$minstok."',stokmax='".$maxstok."', updateby='".$_SESSION['standard']['userid']."' ,updatetime='".date('Y-m-d H:i')."' where id='".$myid."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	case 'insert':
		if($pt==''||$gudang==''||$kelompokbarang==''||$barang==''){
			exit("Warning : Lengkapi Pengisian.");
		}
		#gudang harus milik PT yang dipilih dan berada dalam akses user
		$res=fetchdata("select kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$gudang."' and tipe='GUDANG' and induk in (".$unitakses.") and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')");
		if(empty($res)){
			exit("Warning : Gudang tidak valid atau Anda tidak memiliki akses.");
		}
		validasiStok($minstok,$maxstok);
		$str="select * from ".$dbname.".log_5minimunstok where gudang='".$gudang."' and kodebarang='".$barang."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Gudang dan Barang sudah pernah terdaftar disistem");
		}else{
			$str="insert into ".$dbname.".log_5minimunstok (pt,gudang,kodekelompok,kodebarang,satuan,stok,stokmax,createby,createtime,updateby,updatetime) values ('".$pt."','".$gudang."','".$kelompokbarang."','".$barang."','".$satuan."','".$minstok."','".$maxstok."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
	break;

	default:
	break;
}

?>

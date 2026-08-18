<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$method= checkPostGet('method', '');
$tipe  = checkPostGet('tipe', '');
if(count($_POST)==0){
	$param= $_GET;
}else{ 
	$param= $_POST;
}
$notransaksi= checkPostGet('notransaksi', '');
$tipekary   = checkPostGet('tipekary', '');
$jabatan    = checkPostGet('jabatan', '');
$pilihan    = checkPostGet('pilihan', '');
$karyawan   = checkPostGet('karyawan', '');
$periode    = checkPostGet('periode', '');
$tahun      = checkPostGet('tahun', '');
$nourut      = checkPostGet('nourut', '');
$nmorg      = makeOption($dbname,'organisasi','indukblok,namaindukblok');

$param['tgl']   = tanggalsystemn($param['tgl']);
$param['jumlah']= str_replace(",","",$param['jumlah']);

// print_r($param);
// exit("error");

$jab = getPostingJabatan('kebunrkh');

switch ($method) {
	case'previewdata':
		$_SESSION['addbrg']=array();
		#notransaksi
		if($notransaksi==''){
			$str = "select * from " . $dbname . ".kebun_rkhht where divisi='" . $param['divisi'] . "' and tanggal = '".$param['tgl']."' and asisten ='".$param['asst']."' and mandor1='".$param['mandor1']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				#exit("Warning : Data sudah pernah diinput, silahkan lakukan edit.");
			}
			
			$str = "select max(convert(substring_index(notransaksi,'/',-1),unsigned integer)) as nomor from " . $dbname . ".kebun_rkhht where divisi='" . $param['divisi'] . "' and tanggal like '".substr($param['tgl'],0,7)."%'";
			$res = fetchdata($str);
			if($res[0]['nomor']==''){
				$notransaksi=str_replace("-","",$param['tgl'])."/RKH/".$param['divisi']."/001";
			}else{
				$notransaksi=str_replace("-","",$param['tgl'])."/RKH/".$param['divisi']."/".addZero($res[0]['nomor']+1,3);
			}
			#insert
			$data = array(
				'notransaksi'=> $notransaksi,
				'asisten'    => $param['asst'],
				'mandor1'    => $param['mandor1'],
				'tanggal'    => $param['tgl'],
				'divisi'     => $param['divisi'],
				'createby'   => $_SESSION['standard']['userid'],
				'createdtime'=> date("Y-m-d H:i:s")
			);
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'kebun_rkhht',$data,$cols);#exit("error".$str);
			try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}	
		}else{
			$data = array(
				'asisten'   => $param['asst'],
				'mandor1'   => $param['mandor1'],
				'tanggal'   => $param['tgl'],
				'divisi'    => $param['divisi'],
				'updateby'  => $_SESSION['standard']['userid'],
				'updatetime'=> date("Y-m-d H:i:s")
			);
			$where = "notransaksi='".$notransaksi."'";
			$query = updateQuery($dbname,'kebun_rkhht',$data,$where); #exit("error".$str);
			try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}	
		}
		
        $tab="<fieldset><legend>Form</legend>";
		$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable >";
		$tab.="<thead><tr class=rowheader>";
		$tab.=" <th align=center rowspan=3 width=20px>No</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['kegiatan']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['blok']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['luas']."</th>
				<th align=center rowspan=3 >Pkk</th>
				<th align=center rowspan=3 >Rot</th>
				<th align=center colspan=2 >".$_SESSION['lang']['prestasi']."</th>
				<th align=center colspan=5 >Tenaga Kerja</th>
				<th align=center colspan=5 >".$_SESSION['lang']['material']."</th>
				<th align=center colspan=3 >Produksi & Angk</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['mandor']."</th>
				<th align=center colspan=2 rowspan=3>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>";				
				$tab.="<th align=center rowspan=2>Sat</th>";
				$tab.="<th align=center rowspan=2>Jlh</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Non Staff'>NS</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Tetap'>KHT</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Lepas'>KHL</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Borongan'>BOR</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['kodebarang']."</th>";
				$tab.="<th align=center rowspan=2>Sat</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['dosis']."</th>";
				$tab.="<th align=center rowspan=2>Jlh</th>";
				$tab.="<th align=center rowspan=2>+</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['kg']."</th>";
				$tab.="<th align=center rowspan=2>Truk</th>";
			$tab.="</tr>
			</thead><tbody>";
			
			// $whereBlok='';
			// $whereBlok.=" and a.kodeorganisasi like '".$param['divisi']."%'";
			// $whereBlok.=" and b.statusblok in ('TB','TBM','TM','BBT')";
			$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			// $str = "select * from ".$dbname.".organisasi a left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg where a.tipe in('BLOK','BIBITAN') ".$whereBlok." and b.luasareaproduktif>0 order by a.kodeorganisasi asc";
			// $res=fetchdata($str);
			// foreach($res as $bar){
			// 	$d=$bar['induk'];
			// 	if($d!=$n){			
			// 		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
			// 		$optBlok.="<optgroup label='".$bar['induk']." - ".$nmorg[$bar['induk']]."'>";
			// 	}
			// 	$optBlok.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']." - ".$bar['statusblok']."</option>";
			// 	$n=$d;
			// 	if($d!=$n){
			// 		$optBlok.="</optgroup>";
			// 	}
			// }
			
			$optKeg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where 1=1 and status='1' order by kodekegiatan";
			$res = fetchdata($sql);
			foreach($res as $bar){
				$optKeg.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . " - " . $bar['kelompok'] . "</option>";
			}
			$optbrg="<option value=''></option>";
			$sql = "SELECT * FROM " . $dbname . ".log_5masterbarang where kelompokbarang in ('311','312','313','351','361') and inactive ='0' order by namabarang";
			$res = fetchdata($sql);
			foreach($res as $bar){
				$optbrg.="<option value=" . $bar['kodebarang'] . ">" . $bar['namabarang'] . "</option>";
			}

			$optMandor="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

			$str="select * from ".$dbname.".kebun_5pejabatbkm where kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tipe='RKH'";
			$res=fetchdata($str);
			foreach($res as $bar){
				if($bar['kolom']=='mandor'){
					$mdr=$bar['jabatan'];
				}
				if($bar['kolom']=='mandor1'){
					$mdr1=$bar['jabatan'];
				}
				if($bar['kolom']=='kerani'){
					$krn=$bar['jabatan'];
				}
				if($bar['kolom']=='asst'){
					$asst=$bar['jabatan'];
				}
			}

			if($mdr!=''){
				$whr=" and a.kodejabatan in (".$mdr.")";
			}else{
				$whr=" and b.namajabatan like '%mandor%' and b.namajabatan not like '%mandor%1%'";
			}


			$whereKary=" and (a.tanggalkeluar = '0000-00-00' or a.tanggalkeluar > ".$_SESSION['org']['period']['start'].")";
			$qMandor = "select a.karyawanid,a.namakaryawan,a.nik,b.namajabatan,a.subbagian,a.lokasitugas from ".$dbname.".datakaryawan a
			left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where 1=1 ".$whr." ".$whereKary." order by  a.namakaryawan asc";
			$res = fetchdata($qMandor);
			foreach($res as $row){
				if($row['subbagian']!=''){
					$div="[".$row['subbagian']."] ";
				}else{
					$div="[".$row['lokasitugas']."] ";
				}
				$optMandor.="<option value=".$row['karyawanid'].">".$row['namakaryawan']." [".$row['nik']."] ".$div."".$row['namajabatan']."</option>";
			}
			
			
			$tab.="<tr class=rowcontent style=vertical-align:top;>";
			$tab.="<td align=center rowspan=2>#<input hidden id=nourut></td>";
			$tab.="<td rowspan=2>
					<select class='select2'style=width:130px onchange=getdetailkeg(); onclick=hapuswarna(this.id); id=kegiatan>".@$optKeg."</select>
					</td>";
			$tab.="<td style=width:95px rowspan=2>
					<select class='select2'style=width:95px onclick=hapuswarna(this.id); onchange=getdata(); id=blok>".$optBlok."</select>
					</td>";
			
			$tab.="<td rowspan=2><input id=luas disabled class=myinputtextnumber style=\"width:30px;\"></td>";
			$tab.="<td rowspan=2><input id=pokok disabled class=myinputtextnumber style=\"width:30px;\"></td>";
			$tab.="<td rowspan=2><input style=\"width:30px;\" type=text id=rotasi class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input id=sat disabled class=myinputtext style=\"width:35px;\"></td>";
			$tab.="<td rowspan=2><input style=\"width:40px;\" onkeyup=cekpres(); onclick=hapuswarna(this.id); type=text id=pres class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:35px;\" onkeyup=totalhk(); type=text id=kbl class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:35px;\" disabled onkeyup=totalhk(); type=text id=kht class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:35px;\" onkeyup=totalhk(); type=text id=khl class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:35px;\" onkeyup=totalhk(); type=text id=bor class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input onclick=hapuswarna(this.id); id=ttlhk disabled class=myinputtextnumber style=\"width:40px;\"></td>";
			$tab.="<td rowspan=2>
					<select class='select2'style=width:100px onchange=getsatbarang() onclick=hapuswarna(this.id); id=barang>".$optbrg."</select>
					</td>";
			$tab.="<td rowspan=2><input id=satbrg disabled class=myinputtext onclick=hapuswarna(this.id); style=\"width:35px;\"></td>";			
			$tab.="<td rowspan=2><input style=\"width:35px;\" type=text id=dosis onkeyup=getjlhbrg('dss'); class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:35px;\" type=text id=jlhbarang onkeyup=getjlhbrg('jlh'); class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2 align=center width=20px><img class='zImgBtn' title='Tambah Material' id='tombolsimpanmaterial' src='images/plus.png' onclick='addbarang()'></td>";
			
			$tab.="<td rowspan=2><input id=bjr hidden><input style=\"width:35px;\" type=text id=jjg onkeyup=getkgtbs('jjg'); class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:40px;\" type=text id=kg onkeyup=getkgtbs('kg'); class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			$tab.="<td rowspan=2><input style=\"width:30px;\" type=text id=truk class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/></td>";
			
			$tab.="<td rowspan=2><select class='select2'style=width:100px onclick=hapuswarna(this.id); id=mandor>".$optMandor."</select></td>";
			$tab.="<td rowspan=2 align=center width=20px><img title='Simpan' class='zImgBtn' onclick='savedetail()' src='images/save.png'></td>";
			$tab.="<td rowspan=2 align=center width=20px><img title='Hapus' class='zImgBtn' onclick='canceldetail()' src='images/clear.png'></td>";
			
			$tab.="</tr><input hidden id=jlhbrg value=''>";
			// $tab.="<tr class=rowcontent>";
			// $tab.="<td colspan=6 id=contaddbarang></td>";
			// $tab.="</tr>";
			
		$tab.="</tbody>";
		$tab.="<tfoot colspan=6 id=contaddbarang></tfoot>";		
		$tab.="</table>";
		$tab.="</fieldset>";
		$tab.="<fieldset><legend>List Data</legend><div id=loaddatadetail></div>";
		$tab.="</fieldset>";
		
		echo $tab."####".trim($notransaksi);
		
	break;
	case'addbarang':
		if($param['blok']==''){
			exit("Warning : Kode blok wajib diisi.");
		}
		if($param['kegiatan']==''){
			exit("Warning : Kode kegiatan wajib diisi.");
		}
		if($param['barang']==''){
			exit("Warning : Kode barang wajib diisi.");
		}
		if($param['jlhbarang']<='0'){
			exit("Warning : Jumlah barang wajib diisi.");
		}
		
		$newdata = array();
		$newdata = array(
			'barang'   =>$param['barang'],
			'dosis'    =>$param['dosis'],
			'jlhbarang'=>$param['jlhbarang']
		);
		
		if($_SESSION['addbrg'] != array()){
			foreach($_SESSION['addbrg'] as $key=>$row){
				if($row['barang'] == $param['barang']){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['addbrg'],$newdata);
		}else{
			array_push($_SESSION['addbrg'],$newdata);
		}
		$tab="<table border=0 cellpadding=5 width=100%>";
		foreach($_SESSION['addbrg'] as $key=>$row){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$row['barang']."'");
			$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$row['barang']."'");
			$tab.="<tr class='rowcontent' height=25px>";
			$tab.="<td colspan=13></td>";
			if(strlen($nmbrg[$row['barang']])>17){
				$tab.="<td style='text-align:left;' title='".$nmbrg[$row['barang']]."'>".substr($nmbrg[$row['barang']],0,15)."...</td>";
			}else{				
				$tab.="<td style='text-align:left;'>".$nmbrg[$row['barang']]."</td>";
			}
			$tab.="<td style='text-align:left;'>".$nmsat[$row['barang']]."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['dosis'],2)."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['jlhbarang'],2)."</td>";
			$tab.="<td style='text-align:center' width=20px>
				<img title='Delete' class=zImgBtn onclick=\"delbrg('".$key."')\" src='images/delete_32.png'/>
			</td>";
			$tab.="<td colspan=6></td>";
			$tab.="</tr>";
		}
		$tab.="</table>";
		echo $tab;
		
	break;
	case'delbrg':
	
		unset($_SESSION['addbrg'][$param['key']]);
		$tab="<table border=0 cellpadding=5 width=100%>";
		foreach($_SESSION['addbrg'] as $key=>$row){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$row['barang']."'");
			$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$row['barang']."'");
			$tab.="<tr class='rowcontent' height=25px>";
			$tab.="<td colspan=13></td>";
			if(strlen($nmbrg[$row['barang']])>17){
				$tab.="<td style='text-align:left;' title='".$nmbrg[$row['barang']]."'>".substr($nmbrg[$row['barang']],0,15)."...</td>";
			}else{				
				$tab.="<td style='text-align:left;'>".$nmbrg[$row['barang']]."</td>";
			}
			$tab.="<td style='text-align:left;'>".$nmsat[$row['barang']]."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['dosis'],2)."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['jlhbarang'],2)."</td>";
			$tab.="<td style='text-align:center' width=20px>
				<img title='Delete' class=zImgBtn onclick=\"delbrg('".$key."')\" src='images/delete_32.png'/>
			</td>";
			$tab.="<td colspan=6></td>";
			$tab.="</tr>";
		}
		$tab.="</table>";

		echo $tab;
	break;
	case'getsatbarang':
		$sat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$param['barang']."'");
		
		echo trim($sat[$param['barang']]);
	break;
	case'getdetailkeg':
		if($param['kegiatan']!=''){			
			$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where kodekegiatan = '".$param['kegiatan']."'
			AND kelompok IN ('TM','TBM','PNN','TB')"; #exit("error".$sql);
			$res = fetchdata($sql);
			foreach($res as $bar){
				$sat=$bar['satuan'];
				$kel=$bar['kelompok'];
			}
			
			$sql = "SELECT * FROM " . $dbname . ".setup_kegiatannorma where kodekegiatan = '".$param['kegiatan']."' order by kodebarang";
			$res = fetchdata($sql); 
			$jlhbrg=count($res);
			if($jlhbrg>0){
				$keg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";				
			}else{
				$keg="<option value=''></option>";
			}
			foreach($res as $bar){
				$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
				$keg.="<option value=" . $bar['kodebarang'] . ">" . $nmbrg[$bar['kodebarang']] . "</option>";
			}

			$blok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

			if($kel=='TM' || $kel=='PNN'){
				$wh="and statusblok ='TM'";
			}else{
				$wh="and statusblok ='".$kel."'";
			}
			
			$nmblok=makeOption($dbname,'organisasi','indukblok,namaindukblok');
			$sql = "SELECT indukblok,statusblok FROM " . $dbname . ".setup_blok where 1=1 ".$wh." group by indukblok order by indukblok asc";
			$res = fetchdata($sql);
			foreach($res as $bar){
				// $d=substr($bar['kodekegiatan'],0,3);
				// if($d!=$n){			
				// 	$blok.="<optgroup label='".$nmakun[substr($bar['kodekegiatan'],0,3)]."'>";
				// }
				$blok.="<option value='" . $bar['indukblok'] . "'>" . $nmblok[$bar['indukblok']] . " - " . $bar['statusblok'] . "</option>";
				// $n=$d;
				// if($d!=$n){			
				// 	$blok.="</optgroup>";
				// }
			}
		}
		
		echo $sat."###".$keg."###".$jlhbrg."###".$blok;
	break;
	
	case'getdata':
		// $keg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$wh = " and kodekegiatan='".$param['kegiatan']."'";
		$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where 1=1 ".$wh." order by kelompok,kodekegiatan";
		$res = fetchdata($sql);
		foreach($res as $bar){
			$kel = $bar['kelompok'];
			$pilluas = $bar['pilihanluas'];
		}
		if ($pilluas == 0) {
			$str = "SELECT SUM(luasareaproduktif+luasareanonproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok 
			from " . $dbname . ".setup_blok where indukblok ='".$param['blok']."' AND statusblok='".$kel."'"; 
		} elseif ($pilluas == 1) {
			$str = "SELECT SUM(luasbloking) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok 
			from " . $dbname . ".setup_blok where indukblok ='".$param['blok']."' AND statusblok='".$kel."'"; 
		} elseif ($pilluas == 2) {
			$str = "SELECT SUM(lc) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok 
			from " . $dbname . ".setup_blok where indukblok ='".$param['blok']."' AND statusblok='".$kel."'"; 
		} else {
			$str = "SELECT SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok 
			from " . $dbname . ".setup_blok where indukblok ='".$param['blok']."' AND statusblok='".$kel."'"; 
		}
		#exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$luas=$bar['luasareaproduktif'];
			$pokok=$bar['jumlahpokok'];
			// $kel=$bar['statusblok'];
		}
		
		// if($kel=='TM'){
		// 	$wh="and kelompok in ('TM','PNN')";
		// }else{
		// 	$wh="and kelompok ='".$kel."'";
		// }
		
		// $nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		// $sql = "SELECT * FROM " . $dbname . ".setup_kegiatan where 1=1 ".$wh." and status='1' order by kelompok,kodekegiatan";
		// $res = fetchdata($sql);
		// foreach($res as $bar){
		// 	$d=substr($bar['kodekegiatan'],0,3);
		// 	if($d!=$n){			
		// 		$keg.="<optgroup label='".$nmakun[substr($bar['kodekegiatan'],0,3)]."'>";
		// 	}
		// 	$keg.="<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
		// 	$n=$d;
		// 	if($d!=$n){			
		// 		$keg.="</optgroup>";
		// 	}
		// }
		
		$bjr=0;
		// $str = "select * from ".$dbname.".kebun_5bjr where  kodeorg='".$param['blok']."' order by periode desc limit 1"; #exit("error".$str);
		// $bar=fetchdata($str);
		// $bjr=$bar[0]['bjr'];
			
		// echo $keg."###".$luas."###".$pokok."###".$bjr;
		echo $luas."###".$pokok."###".$bjr;
	break;
	
	case'insert':
		try {
		$owlPDO->beginTransaction();
			
			if($param['jlhbrg']>0 and count($_SESSION['addbrg'])==0 and $param['barang']!='' and $param['jlhbarang']>0){
				$newdata = array();
				$newdata = array(
					'barang'   =>$param['barang'],
					'dosis'    =>$param['dosis'],
					'jlhbarang'=>$param['jlhbarang']
				);
				
				if($_SESSION['addbrg'] != array()){
					foreach($_SESSION['addbrg'] as $key=>$row){
						if($row['barang'] != $param['barang']){
							array_push($_SESSION['addbrg'],$newdata);
						}
					}
				}else{
					array_push($_SESSION['addbrg'],$newdata);
				}
			}
			
			if($param['jlhbrg']>0 and count($_SESSION['addbrg'])==0){
				throw new PDOException("Kegiatan ini harus menggunakan material.");
			}
			
			if($param['jlhbrg']==0 and count($_SESSION['addbrg'])>0){
				throw new PDOException("Kegiatan ini tidak menggunakan material,\nsilahkan hapus material terlebih dahulu.");
			}
		
			$str = "select * from " . $dbname . ".kebun_rkh_dt where notransaksi='".$notransaksi."' and kodeblok='".$param['blok']."' and kodekegiatan='".$param['kegiatan']."' and mandor='".$param['mandor']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah pernah diinput, silahkan cek list data dibawah.");
			}
			
			$str = "select * from " . $dbname . ".kebun_rkhht where notransaksi='".$notransaksi."'";
			$res = fetchdata($str);
			if(count($res)==0){
				$data = array(
					'notransaksi'=> $notransaksi,
					'asisten'    => $param['asst'],
					'tanggal'    => $param['tgl'],
					'divisi'     => $param['divisi'],
					'createby'   => $_SESSION['standard']['userid'],
					'createdtime'=> date("Y-m-d H:i:s")
				);
				$cols = array();
				foreach($data as $key=>$row) {
					$cols[] = $key;
				}
				$query = insertQuery($dbname,'kebun_rkhht',$data,$cols);#exit("error".$str);
				$owlPDO->exec($query);
			}
			
			$str = "select max(nourut) as nourut from " . $dbname . ".kebun_rkh_dt where notransaksi='".$notransaksi."'";
			$res = fetchdata($str)[0];
			$nourut = $res['nourut']+1;
			// $stsblok = makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$param['blok']."'");
			$stsblok = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$param['kegiatan']."'");
			$data = array();
			$data = array(
				'notransaksi' => $notransaksi,
				'nourut'      => $nourut,
				'mandor'      => $param['mandor'],
				'kodeblok'    => $param['blok'],
				'statusblok'  => $stsblok[$param['kegiatan']],
				'kodekegiatan'=> $param['kegiatan'],
				'rotasi'      => $param['rotasi'],
				'target'      => $param['pres'],
				'hk_pb'       => $param['kbl'],
				'hk_kht'      => $param['kht'],
				'hk_khl'      => $param['khl'],
				'hk_bor'      => $param['bor'],
				'jmlh_tbs'    => $param['jjg'],
				'jmlh_kgtbs'  => $param['kg'],
				'angkutan'    => $param['truk'],
				'kontan'      => 'KERJA'
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'kebun_rkh_dt',$data,$cols);#exit("error".$str);
			$owlPDO->exec($query);
			
			if(count($_SESSION['addbrg'])>0){
				foreach($_SESSION['addbrg'] as $key => $row){
					$data = array();
					$data = array(
						'notransaksi'=> $notransaksi,
						'nourut'     => $nourut,
						'kodebarang' => $row['barang'],
						'jumlah'     => $row['jlhbarang']
					);
					
					$cols= array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,'kebun_rkh_dtmaterial',$data,$cols);
					$owlPDO->exec($str);
					
					#unset($_SESSION['addbrg'][$key]);
				}
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
		}
		
		$_SESSION['addbrg']=array();
	break;
	case'update':
	
		try {
		$owlPDO->beginTransaction();
			
			if($param['jlhbrg']>0 and count($_SESSION['addbrg'])==0 and $param['barang']!='' and $param['jlhbarang']>0){
				$newdata = array();
				$newdata = array(
					'barang'   =>$param['barang'],
					'dosis'    =>$param['dosis'],
					'jlhbarang'=>$param['jlhbarang']
				);
				
				if($_SESSION['addbrg'] != array()){
					foreach($_SESSION['addbrg'] as $key=>$row){
						if($row['barang'] != $param['barang']){
							array_push($_SESSION['addbrg'],$newdata);
						}
					}
				}else{
					array_push($_SESSION['addbrg'],$newdata);
				}
			}
			
			if($param['jlhbrg']>0 and count($_SESSION['addbrg'])==0){
				throw new PDOException("Kegiatan ini harus menggunakan material.");
			}
			
			if($param['jlhbrg']==0 and count($_SESSION['addbrg'])>0){
				throw new PDOException("Kegiatan ini tidak menggunakan material,\nsilahkan hapus material terlebih dahulu.");
			}
		
			// $stsblok = makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$param['blok']."'");
			$stsblok = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$param['kegiatan']."'");
			$data = array(
				'mandor'      => $param['mandor'],
				'kodeblok'    => $param['blok'],
				'statusblok'  => $stsblok[$param['kegiatan']],
				'kodekegiatan'=> $param['kegiatan'],
				'rotasi'      => $param['rotasi'],
				'target'      => $param['pres'],
				'hk_pb'       => $param['kbl'],
				'hk_kht'      => $param['kht'],
				'hk_khl'      => $param['khl'],
				'hk_bor'      => $param['bor'],
				'jmlh_tbs'    => $param['jjg'],
				'jmlh_kgtbs'  => $param['kg'],
				'angkutan'    => $param['truk'],
				'kontan'      => 'KERJA'
			);
			
			$where = "nourut='".$nourut."' and notransaksi='".$notransaksi."'";
			
			$query = updateQuery($dbname,'kebun_rkh_dt',$data,$where); 
			// exit("error".$query);
			$owlPDO->exec($query);
			
			
			$str = "delete from " . $dbname . ".kebun_rkh_dtmaterial where notransaksi ='".$notransaksi."' and nourut ='".$param['nourut']."'";
			// exit("error".$str);
			$owlPDO->exec($str);
		
			if(count($_SESSION['addbrg'])>0){
				foreach($_SESSION['addbrg'] as $key => $row){
					$data = array();
					$data = array(
						'notransaksi'=> $notransaksi,
						'nourut'     => $nourut,
						'kodebarang' => $row['barang'],
						'jumlah'     => $row['jlhbarang']
					);
					
					$cols= array();
					foreach($data as $key=>$row) {
							$cols[] = $key;
					}
					$str = insertQuery($dbname,'kebun_rkh_dtmaterial',$data,$cols);
					$owlPDO->exec($str);
					
					#unset($_SESSION['addbrg'][$key]);
				}
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
		}
		
		$_SESSION['addbrg']=array();
	break;
	case'loaddatadetail':
        $tab="";
        $tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable>";
		$tab.="<thead><tr class=rowheader>";
		$tab.=" <th align=center rowspan=3 width=20px>No</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['kegiatan']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['blok']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['luas']."</th>
				<th align=center rowspan=3 >Pkk</th>
				<th align=center rowspan=3 >Rot</th>
				<th align=center colspan=2 >".$_SESSION['lang']['prestasi']."</th>
				<th align=center colspan=5 >Tenaga Kerja</th>
				<th align=center colspan=4 >".$_SESSION['lang']['material']."</th>
				<th align=center colspan=3 >Produksi & Angk</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['mandor']."</th>
				<th align=center colspan=2 rowspan=3>" . $_SESSION['lang']['action'] . "</th>
			</tr>
			<tr class=rowheader>";				
				$tab.="<th align=center rowspan=2>Sat</th>";
				$tab.="<th align=center rowspan=2>Jlh</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Non Staff'>NS</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Tetap'>KHT</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Lepas'>KHL</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Borongan'>BOR</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
				$tab.="<th align=center rowspan=2 >".$_SESSION['lang']['kodebarang']."</th>";
				$tab.="<th align=center rowspan=2 >Sat</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['dosis']."</th>";
				$tab.="<th align=center rowspan=2>Jlh</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['kg']."</th>";
				$tab.="<th align=center rowspan=2>Truk</th>";
			$tab.="</tr>
			</thead><tbody>";
		$no = 0;
        $str = "SELECT * FROM " . $dbname . ".kebun_rkh_dt where notransaksi='".$notransaksi."'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nourut[$bar['nourut']]=$bar['nourut'];
			$blok[$bar['nourut']]=$bar['kodeblok'];
			$keg[$bar['nourut']]=$bar['kodekegiatan'];
			$rot[$bar['nourut']]=$bar['rotasi'];
			$pres[$bar['nourut']]=$bar['target'];
			$kbl[$bar['nourut']]=$bar['hk_pb'];
			$kht[$bar['nourut']]=$bar['hk_kht'];
			$khl[$bar['nourut']]=$bar['hk_khl'];
			$bor[$bar['nourut']]=$bar['hk_bor'];
			$jjg[$bar['nourut']]=$bar['jmlh_tbs'];
			$kg[$bar['nourut']]=$bar['jmlh_kgtbs'];
			$unit[$bar['nourut']]=$bar['angkutan'];
			$mandor[$bar['nourut']]=$bar['mandor'];
		}
		$data=$spn=array();
		$str = "SELECT a.*,b.kodebarang,b.jumlah FROM " . $dbname . ".kebun_rkh_dt a left join " . $dbname . ".kebun_rkh_dtmaterial b on a.notransaksi=b.notransaksi and a.nourut=b.nourut where a.notransaksi='".$notransaksi."' order by nourut desc";
        $res = fetchdata($str);
        foreach($res as $bar){
			$data[$bar['nourut']][$bar['kodebarang']]=$bar['kodebarang'];
			$jlhbrg[$bar['nourut']][$bar['kodebarang']]+=$bar['jumlah'];
			$spn[$bar['nourut']][$bar['kodebarang']]+=1;
		}
		
		if(count($data)>0){
			foreach($data as $urut => $vbrg){
				foreach($vbrg as $kdbrg){
					if($spn[$urut][$kdbrg]>0){
						$span[$urut]+=$spn[$urut][$kdbrg];
					}else{
						$span[$urut]=1;
					}
				}
			}
			foreach($data as $urut => $vbrg){
				if($span[$urut]==1){
					$row="";
				}else{					
					$row="rowspan=".$span[$urut]."";
				}
				
				$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$keg[$urut]."'");
				$pilluas = makeOption($dbname,'setup_kegiatan','kodekegiatan,pilihanluas',"kodekegiatan='".$keg[$urut]."'");
				$nmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor[$urut]."'");
				$nmsat = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$keg[$urut]."'");
				// $nmluas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok[$urut]."'");
				// $nmpkk = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok[$urut]."'");
				$stsblok = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$keg[$urut]."'");
				if ($pilluas[$keg[$urut]] == 0) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasareaproduktif+luasareanonproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} elseif ($pilluas[$keg[$urut]] == 1) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasbloking) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} elseif ($pilluas[$keg[$urut]] == 2) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(lc) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} else {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);

				}


				$rluas = fetchData($qluas);
				$nmluas[$blok[$urut]] = $rluas[0]['luasareaproduktif'];
				$nmpkk[$blok[$urut]]  = $rluas[0]['jumlahpokok'];
				
				$edit="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$notransaksi."','".$urut."');\" >";
				$del="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deldetail('".$notransaksi."','".$urut."');\" >";
				
				$no+=1;
				$tab.="<tr class=rowcontent style=vertical-align:top;>";
				$tab.="<td align=center ".$row.">".$no."</td>";
				$tab.="<td ".$row.">".$nmkeg[$keg[$urut]]."</td>";
				$tab.="<td ".$row.">".$nmorg[$blok[$urut]]."</td>";
				$tab.="<td align=right ".$row.">".numb_format($nmluas[$blok[$urut]],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($nmpkk[$blok[$urut]])."</td>";
				$tab.="<td align=center ".$row.">".$rot[$urut]."</td>";
				$tab.="<td ".$row.">".$nmsat[$keg[$urut]]."</td>";
				$tab.="<td align=right ".$row.">".numb_format($pres[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kbl[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kht[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($khl[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($bor[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kbl[$urut]+$kht[$urut]+$khl[$urut]+$bor[$urut],2)."</td>";
				
				$tkbl+=$kbl[$urut];
				$tkht+=$kht[$urut];
				$tkhl+=$khl[$urut];
				$tbor+=$bor[$urut];
				$tjjg+=$jjg[$urut];
				$tkg+=$kg[$urut];
				$tunit+=$unit[$urut];
				
				$nbrg=0;
				foreach($vbrg as $kdbrg){
					$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
					$nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbrg."'");
					if($span[$urut]>1){
						$nbrg+=1;
						if($nbrg>1){
							if($nbrg==2){							
								$tab.="<td align=right ".$row.">".numb_format($jjg[$urut])."</td>";
								$tab.="<td align=right ".$row.">".numb_format($kg[$urut])."</td>";
								$tab.="<td align=right ".$row.">".numb_format($unit[$urut])."</td>";
								$tab.="<td align=left ".$row.">".$nmkar[$mandor[$urut]]."</td>";
								$tab.="<td align=center ".$row." width=25px>".$edit."</td>";
								$tab.="<td align=center ".$row." width=25px>".$del."</td>";
							}
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
						}
						#jika barang lebih dari 1
						$tab.="<td align=left>".$nmbrg[$kdbrg]."</td>";
						$tab.="<td align=left>".$nmsat[$kdbrg]."</td>";
						if(is_nan($jlhbrg[$urut][$kdbrg]/$pres[$urut])){
							$tab.="<td align=right></td>";
						}else{							
							$tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg]/$pres[$urut],2)."</td>";
						}
						$tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg],2)."</td>";
					}else{				
						$tab.="<td align=left>".$nmbrg[$kdbrg]."</td>";
						$tab.="<td align=left>".$nmsat[$kdbrg]."</td>";
						if(is_nan($jlhbrg[$urut][$kdbrg]/$pres[$urut])){
							$tab.="<td align=right></td>";
						}else{							
							$tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg]/$pres[$urut],2)."</td>";
						}
						$tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg],2)."</td>";
					}
				}
				if($nbrg==0){
					$tab.="<td align=right ".$row.">".numb_format($jjg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($kg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($unit[$urut])."</td>";
					$tab.="<td align=left ".$row.">".$nmkar[$mandor[$urut]]."</td>";
					$tab.="<td align=center ".$row." width=25px>".$edit."</td>";
					$tab.="<td align=center ".$row." width=25px>".$del."</td>";
				}
			}
		}
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=3>T O T A L</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".numb_format($tkbl,2)."</td>";
		$tab.="<td align=right>".numb_format($tkht,2)."</td>";
		$tab.="<td align=right>".numb_format($tkhl,2)."</td>";
		$tab.="<td align=right>".numb_format($tbor,2)."</td>";
		$tab.="<td align=right>".numb_format($tkbl+$tkht+$tkhl+$tbor,2)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".numb_format($tjjg,2)."</td>";
		$tab.="<td align=right>".numb_format($tkg,2)."</td>";
		$tab.="<td align=right>".numb_format($tunit,2)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
        echo $tab;
	break;
	case'edit':
		$str = "SELECT * FROM " . $dbname . ".kebun_rkh_dt where notransaksi='".$notransaksi."' and nourut='".$param['nourut']."'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$blok  =$bar['kodeblok'];
			$keg   =$bar['kodekegiatan'];
			$rot   =$bar['rotasi'];
			$pres  =$bar['target'];
			$kbl   =$bar['hk_pb'];
			$kht   =$bar['hk_kht'];
			$khl   =$bar['hk_khl'];
			$bor   =$bar['hk_bor'];
			$jjg   =$bar['jmlh_tbs'];
			$kg    =$bar['jmlh_kgtbs'];
			$unit  =$bar['angkutan'];
			$mandor=$bar['mandor'];

			$stsblok = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$keg."'");
			$pilluas = makeOption($dbname,'setup_kegiatan','kodekegiatan,pilihanluas',"kodekegiatan='".$keg."'");
			// $nmluas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok."'");
			if ($pilluas[$keg] == 0) {
				$qluas = selectQuery(
					$dbname,
					"setup_blok",
					"SUM(luasareaproduktif+luasareanonproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
					"indukblok='".$blok."' AND statusblok='".$stsblok[$keg]."'"
				);
			} elseif ($pilluas[$keg] == 1) {
				$qluas = selectQuery(
					$dbname,
					"setup_blok",
					"SUM(luasbloking) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
					"indukblok='".$blok."' AND statusblok='".$stsblok[$keg]."'"
				);
			} elseif ($pilluas[$keg] == 2) {
				$qluas = selectQuery(
					$dbname,
					"setup_blok",
					"SUM(lc) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
					"indukblok='".$blok."' AND statusblok='".$stsblok[$keg]."'"
				);
			} else {
				$qluas = selectQuery(
					$dbname,
					"setup_blok",
					"SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
					"indukblok='".$blok."' AND statusblok='".$stsblok[$keg]."'"
				);
			}
			$rluas = fetchData($qluas);
			$nmluas[$blok] = $rluas[0]['luasareaproduktif'];
			$nmpkk[$blok]  = $rluas[0]['jumlahpokok'];
			$nmsatk = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$keg."'");
			// $nmpkk = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok."'");
		}

		$_SESSION['addbrg']=array();
		$str = "SELECT * FROM " . $dbname . ".kebun_rkh_dtmaterial where notransaksi='".$notransaksi."' and nourut='".$param['nourut']."'";
		$res = fetchdata($str);
        foreach($res as $bar){
			$_SESSION['addbrg'][]=array(
				'barang'   =>$bar['kodebarang'],
				'dosis'    =>$bar['jumlah']/$pres,
				'jlhbarang'=>$bar['jumlah']
			);
		}
		
		$tab="<table border=0 cellpadding=1 width=100%>";
		foreach($_SESSION['addbrg'] as $key => $row){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$row['barang']."'");
			$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$row['barang']."'");
			$tab.="<tr class='rowcontent' height=25px>";
			$tab.="<td colspan=13></td>";
			if(strlen($nmbrg[$row['barang']])>17){
				$tab.="<td style='text-align:left;' title='".$nmbrg[$row['barang']]."'>".substr($nmbrg[$row['barang']],0,15)."...</td>";
			}else{				
				$tab.="<td style='text-align:left;'>".$nmbrg[$row['barang']]."</td>";
			}
			$tab.="<td style='text-align:left;'>".$nmsat[$row['barang']]."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['dosis'],2)."</td>";
			$tab.="<td style='text-align:right;'>".numb_format($row['jlhbarang'],2)."</td>";
			$tab.="<td style='text-align:center' width=20px>
				<img title='Delete' class=zImgBtn onclick=\"delbrg('".$key."')\" src='images/delete_32.png'/>
			</td>";
			$tab.="<td colspan=6></td>";
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$sql = "SELECT * FROM " . $dbname . ".setup_kegiatannorma where kodekegiatan = ".$keg."";
		$res = fetchdata($sql); 
		$jlhbrg=count($res);
		
		echo $tab."##".$blok."##".$keg."##".$nmluas[$blok]."##".$nmpkk[$blok]."##".$rot."##".$nmsatk[$keg]."##".$pres."##".$kbl."##".$kht."##".$khl."##".$bor."##".$jjg."##".$kg."##".$unit."##".$mandor."##".$jlhbrg;
	break;
    case'deletedetail':
		$str = "delete from " . $dbname . ".kebun_rkh_dt where notransaksi ='".$param['notransaksi']."' and nourut ='".$param['nourut']."'";
		#exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		$str = "delete from " . $dbname . ".kebun_rkh_dtmaterial where notransaksi ='".$param['notransaksi']."' and nourut ='".$param['nourut']."'";
		#exit("error".$str);
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
	
    case'loaddata':

    	$dataarray=array();
		$dataarray=getOrgDetail(27);
		$datadivisi='';
		foreach ($dataarray as $key => $value) {
			if($datadivisi==''){
				$datadivisi="'".$value."'";
			}else{
				$datadivisi.=",'".$value."'";
			}
		}

		$where=" and divisi in (".$datadivisi.")";
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		if($param['tgl']!='--'){
			$where.=" and tanggal like '".$param['tgl']."%'";
		}
		
		
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = floatval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$sql = "select * from " . $dbname . ".kebun_rkhht where 1=1 " . $where . "";
        $jlhbrs = count(fetchdata($sql));
        
        $tab = "";
		$no = 0;
        $no = $maxdisplay;
		
        $str = "SELECT distinct notransaksi, divisi,tanggal,asisten,mandor1, status,createby FROM " . $dbname . ".kebun_rkhht where 1=1 " . $where . " order by notransaksi desc, divisi asc, divisi asc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
			$nocu = makeOption($dbname,'kebun_rkh_dtmaterial','notransaksi,cu',"notransaksi='".$bar['notransaksi']."'");
            $no+=1;$isi="";
            $tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['divisi'] . " - " . $nmorg[$bar['divisi']] . "</td>";
            $tab.="<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td>".$nmkar[$bar['asisten']]."</td>";
            $tab.="<td>".$nmkar[$bar['mandor1']]."</td>";
            $tab.="<td><a href=\"javascript:do_load('log_permintaanbarang')\" >".$nocu[$bar['notransaksi']]."</a></td>";
            $tab.="<td>" . getNamaKaryawan($bar['createby']) . "</td>";
			
			if ($bar['status']==0) {				
				$isi.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
					onclick=\"editht('".$bar['notransaksi']."','".$bar['divisi']."','".$bar['asisten']."','".$bar['mandor1']."','".tanggalnormal($bar['tanggal'])."');\" ></td>";
					
				$isi.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
					onclick=\"delht('".$bar['notransaksi']."');\" ></td>";
					
				$isi.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn height='30'  title='Posting' 
						onclick=\"posting('".$bar['notransaksi']."');\" ></td>";	
			}else{
				$isi.="<td align=center width=25px></td>";
				$isi.="<td align=center width=25px></td>";
				$isi.="<td align=center width=25px><img src=images/skyblue/posted.png class=zImgBtn class=zImgBtn height='30'  title='Posted' onclick=\"unposting('".$bar['notransaksi']."');\" ></td>";
			}
			$isi.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='HTML' onclick=\"detailData('".$bar['notransaksi']."','event','html');\" ></td>";
			$isi.="<td align=center width=25px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".$bar['notransaksi']."','event','excel');\" ></td>";		
				
			$tab.=$isi;
			$tab.="</tr>";
		
		}
		
        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr><tr><td colspan=12 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td></tr>";
        echo $tab . "####" . $footd;
	break;
	case'delete':
		$str = "delete from " . $dbname . ".kebun_rkhht where notransaksi ='".$param['notransaksi']."'";
		#exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		$str = "delete from " . $dbname . ".kebun_rkh_dt where notransaksi ='".$param['notransaksi']."'";
		#exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		$str = "delete from " . $dbname . ".kebun_rkh_dtmaterial where notransaksi ='".$param['notransaksi']."'";
		#exit("error".$str);
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
	case'posting':
	try {
	$owlPDO->beginTransaction();
	
		// $str = "select * from " . $dbname . ".kebun_rkhht where notransaksi='" . $param['notransaksi'] . "'";
		// $res = fetchdata($str);
		// foreach($res as $bar){
			// $tanggal=$bar['tanggal'];
			// $asisten=$bar['asisten'];
			// $unit=substr($bar['divisi'],0,4);
			// $divisi=$bar['divisi'];
		// }
		// $str = "select * from " . $dbname . ".kebun_rkh_dt where notransaksi='" . $param['notransaksi'] . "'";
		// $res = fetchdata($str);
		// if(count($res)==0){
			// throw new PDOException("Detail transaksi belum ada.");
		// }
		
		// #cek ada mat atau tidak
		// $str = "select * from " . $dbname . ".kebun_rkh_dtmaterial where notransaksi='" . $param['notransaksi'] . "'";
		// $resdt = fetchdata($str);
		// if(count($resdt)>0){
			// // if($tanggal<$_SESSION['org']['period']['start']){
				// // throw new PDOException("Tanggal transaksi tidak boleh lebih kecil dari tanggal awal periode aktif.");
			// // } 		
			
			// # bentuk nomor transaksi
			// $tmpTgl = explode('-',$tanggal);
			// $notran=$tmpTgl[0].$tmpTgl[1];
			// $str="select max(substr(notransaksi,7,5)) as nomorurut from ".$dbname.".log_permintaanht where substr(notransaksi,1,6) = '".$notran."' and untukunit='".$unit."' order by substr(notransaksi,1,6) desc";
			// $bar=fetchdata($str)[0];

			// if(intval($bar['nomorurut'])==0){
			  // $noawal = 1;
			// }else{
			  // $noawal = intval($bar['nomorurut'])+1;
			// }
			// $notran=$notran.addZero($noawal,5)."-CU-".$unit;
			// $notran = trim($notran);
			
			// $optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
			// $optgd = makeOption($dbname,'organisasi','induk,kodeorganisasi',"induk='".$unit."' and tipe='GUDANG'");
			
			// if($optgd[$unit]==''){
				// throw new PDOException("Gudang sentral tidak ditemukan.");
			// } 
			
			// $data = array(
				// 'notransaksi'  => $notran,
				// 'tanggal'      => $tanggal,
				// 'kodept'       => $optpt[$unit],
				// 'keterangan'   => $notransaksi,
				// 'kodegudang'   => $optgd[$unit],
				// 'namapenerima' => $asisten,
				// 'untukunit'    => $unit,
				// 'norefrensirkh'=> $param['notransaksi'],
				// 'createby'     => $_SESSION['standard']['userid'],
				// 'createdate'   => date("Y-m-d H:i:s")
			// );
			// $cols = array();
			// foreach($data as $key=>$row) {
				// $cols[] = $key;
			// }
			// $query = insertQuery($dbname,'log_permintaanht',$data,$cols);#exit("error".$str);
			// $owlPDO->exec($query);
			
			// foreach($resdt as $var){
				// $optsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$var['kodebarang']."'");
				// $optkeg = makeOption($dbname,'kebun_rkh_dt','notransaksi,kodekegiatan',"notransaksi='".$notransaksi."' and nourut='".$var['nourut']."'");
				// $optblok = makeOption($dbname,'kebun_rkh_dt','notransaksi,kodeblok',"notransaksi='".$notransaksi."' and nourut='".$var['nourut']."'");
				// $data = array(
					// 'notransaksi' => $notran,
					// 'kodebarang'  => $var['kodebarang'],
					// 'satuan'      => $optsat[$var['kodebarang']],
					// 'jumlah'      => $var['jumlah'],
					// 'subunit'     => $divisi,
					// 'kodeblok'    => $optblok[$notransaksi],
					// 'kodekegiatan'=> $optkeg[$notransaksi],
					// 'createtime'  => date("Y-m-d H:i:s")
				// );
				// $cols = array();
				// foreach($data as $key=>$row) {
					// $cols[] = $key;
				// }
				// $query = insertQuery($dbname,'log_permintaandt',$data,$cols);#exit("error".$str);
				// $owlPDO->exec($query);
				
			// }
			// $str = "update " . $dbname . ".kebun_rkh_dtmaterial set cu='".$notran."' where notransaksi ='".$param['notransaksi']."'";
			// $owlPDO->exec($str);
		// }
	
	
	
        $str = "update " . $dbname . ".kebun_rkhht set status='1' where notransaksi ='".$param['notransaksi']."'";
        $owlPDO->exec($str);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	if(count($resdt)>0){
		echo "Transaksi permintaan barang sudah di buat otomatis dengan nomor ".$notran." silahkan masuk ke menu Pengadaan - Transaksi - Administrasi Gudang - Permintaan Barang untuk mengajukan persetujuan.";
	}
	break;
	case'unposting':
		$str = "select * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periode'] . "' and kodeorg='".$param['kodeorg']."' and sudahproses='1'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Periode penggajian sudah ditutup.\nUnposting dibatalkan.");
		}
		
        $str = "update " . $dbname . ".kebun_rkhht set status='0' where notransaksi ='".$param['notransaksi']."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'preview':
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $men='menu.css';
		  $gen='generic.css';
		}else if($theme=='red'){
		  $men='menuRed.css';
		  $gen='genericRed.css';  
		}else{
		  $men='menuGray.css';
		  $gen='genericGray.css';  
		}               
		if($tipe=='excel'){
			$border="border=1";
		}else{
			$tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
			$border="border=0";
		}
		$tab="";
		if($tipe=='excel'){
			$tab.="<table border=1 cellpadding=1 cellspacing=1 class=sortable>";			
		}else{
			$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable>";			
		}
		$tab.="<thead><tr class=rowheader>";
		$tab.=" <th align=center rowspan=3 width=20px>No</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['kegiatan']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['blok']."</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['luas']."</th>
				<th align=center rowspan=3 >Pkk</th>
				<th align=center rowspan=3 >Rot</th>
				<th align=center colspan=2 >".$_SESSION['lang']['prestasi']."</th>
				<th align=center colspan=5 >Tenaga Kerja</th>
				<th align=center colspan=4 >".$_SESSION['lang']['material']."</th>
				<th align=center colspan=3 >Produksi & Angk</th>
				<th align=center rowspan=3 >".$_SESSION['lang']['mandor']."</th>
			</tr>
			<tr class=rowheader>";				
				$tab.="<th align=center rowspan=2>Sat</th>";
				$tab.="<th align=center rowspan=2>Jlh</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Non Staff'>NS</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Tetap'>KHT</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Harian Lepas'>KHL</th>";
				$tab.="<th align=center rowspan=2 title='Karyawan Borongan'>BOR</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
				$tab.="<th align=center rowspan=2 width=150px>".$_SESSION['lang']['kodebarang']."</th>";
				$tab.="<th align=center rowspan=2 width=50px >Sat</th>";
				$tab.="<th align=center rowspan=2 width=50px>".$_SESSION['lang']['dosis']."</th>";
				$tab.="<th align=center rowspan=2 width=50px>Jlh</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>";
				$tab.="<th align=center rowspan=2>".$_SESSION['lang']['kg']."</th>";
				$tab.="<th align=center rowspan=2>Truk</th>";
			$tab.="</tr>
			</thead><tbody>";
		$no = 0;
        $str = "SELECT * FROM " . $dbname . ".kebun_rkh_dt where notransaksi='".$notransaksi."'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nourut[$bar['nourut']]=$bar['nourut'];
			$blok[$bar['nourut']]=$bar['kodeblok'];
			$keg[$bar['nourut']]=$bar['kodekegiatan'];
			$rot[$bar['nourut']]=$bar['rotasi'];
			$pres[$bar['nourut']]=$bar['target'];
			$kbl[$bar['nourut']]=$bar['hk_pb'];
			$kht[$bar['nourut']]=$bar['hk_kht'];
			$khl[$bar['nourut']]=$bar['hk_khl'];
			$bor[$bar['nourut']]=$bar['hk_bor'];
			$jjg[$bar['nourut']]=$bar['jmlh_tbs'];
			$kg[$bar['nourut']]=$bar['jmlh_kgtbs'];
			$unit[$bar['nourut']]=$bar['angkutan'];
			$mandor[$bar['nourut']]=$bar['mandor'];
		}
		$data=array();
		$str = "SELECT a.*,b.kodebarang,b.jumlah FROM " . $dbname . ".kebun_rkh_dt a left join " . $dbname . ".kebun_rkh_dtmaterial b on a.notransaksi=b.notransaksi and a.nourut=b.nourut where a.notransaksi='".$notransaksi."' order by nourut desc";
        $res = fetchdata($str);
        foreach($res as $bar){
			$data[$bar['nourut']][$bar['kodebarang']]=$bar['kodebarang'];
			$jlhbrg[$bar['nourut']][$bar['kodebarang']]+=$bar['jumlah'];
			$spn[$bar['nourut']][$bar['kodebarang']]+=1;
		}
		
		if(count($data)>0){
			foreach($data as $urut => $vbrg){
				foreach($vbrg as $kdbrg){
					if($spn[$urut][$kdbrg]>0){
						$span[$urut]+=$spn[$urut][$kdbrg];
					}else{
						$span[$urut]=1;
					}
				}
			}
			$tab.="<tr class=rowcontent>";
			$tab.="</tr>";
			foreach($data as $urut => $vbrg){	
				$row="rowspan=".($span[$urut]-1)."";
				$row="";
				$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$keg[$urut]."'");
				$nmsat = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan',"kodekegiatan='".$keg[$urut]."'");
				$nmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$mandor[$urut]."'");
				$pilluas = makeOption($dbname,'setup_kegiatan','kodekegiatan,pilihanluas',"kodekegiatan='".$keg[$urut]."'");
				
				// $nmluas = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok[$urut]."'");
				// $nmpkk = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok[$urut]."'");

				$stsblok = makeOption($dbname,'setup_kegiatan','kodekegiatan,kelompok',"kodekegiatan='".$keg[$urut]."'");
				if ($pilluas[$keg] == 0) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasareaproduktif+luasareanonproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} elseif ($pilluas[$keg] == 1) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasbloking) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} elseif ($pilluas[$keg] == 2) {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(lc) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				} else {
					$qluas = selectQuery(
						$dbname,
						"setup_blok",
						"SUM(luasareaproduktif) AS luasareaproduktif, SUM(jumlahpokok) AS jumlahpokok",
						"indukblok='".$blok[$urut]."' AND statusblok='".$stsblok[$keg[$urut]]."'"
					);
				}

				$rluas = fetchData($qluas);
				$nmluas[$blok[$urut]] = $rluas[0]['luasareaproduktif'];
				$nmpkk[$blok[$urut]]  = $rluas[0]['jumlahpokok'];
				
				$no+=1;
				$tab.="<tr class=rowcontent style=vertical-align:top;>";
				$tab.="<td align=center ".$row.">".$no."</td>";
				$tab.="<td ".$row.">".$nmkeg[$keg[$urut]]."</td>";
				$tab.="<td ".$row.">".$nmorg[$blok[$urut]]."</td>";
				$tab.="<td align=right ".$row.">".numb_format($nmluas[$blok[$urut]],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($nmpkk[$blok[$urut]])."</td>";
				$tab.="<td align=center ".$row.">".$rot[$urut]."</td>";
				$tab.="<td ".$row.">".$nmsat[$keg[$urut]]."</td>";
				$tab.="<td align=right ".$row.">".numb_format($pres[$urut],2)."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kbl[$urut])."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kht[$urut])."</td>";
				$tab.="<td align=right ".$row.">".numb_format($khl[$urut])."</td>";
				$tab.="<td align=right ".$row.">".numb_format($bor[$urut])."</td>";
				$tab.="<td align=right ".$row.">".numb_format($kbl[$urut]+$kht[$urut]+$khl[$urut]+$bor[$urut])."</td>";
				
				
				$tkbl+=$kbl[$urut];
				$tkht+=$kht[$urut];
				$tkhl+=$khl[$urut];
				$tbor+=$bor[$urut];
				$tjjg+=$jjg[$urut];
				$tkg+=$kg[$urut];
				$tunit+=$unit[$urut];
				
				$tab.="<td colspan=4>";
				
				$nbrg=0;
				// foreach($vbrg as $kdbrg){
					// $nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
					// $nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbrg."'");
					// if($span[$urut]>1){
						// $nbrg+=1;
						// if($nbrg>1){
							// if($nbrg==2){							
								// $tab.="<td align=right ".$row.">".numb_format($jjg[$urut])."</td>";
								// $tab.="<td align=right ".$row.">".numb_format($kg[$urut])."</td>";
								// $tab.="<td align=right ".$row.">".numb_format($unit[$urut])."</td>";
								// $tab.="<td align=left ".$row.">".$nmkar[$mandor[$urut]]."</td>";
							// }
							// $tab.="</tr>";
							// $tab.="<tr class=rowcontent>";
						// }
						// #jika barang lebih dari 1
						// $tab.="<td align=left>".$nmbrg[$kdbrg]."</td>";
						// $tab.="<td align=left>".$nmsat[$kdbrg]."</td>";
						// if(is_nan($jlhbrg[$urut][$kdbrg]/$pres[$urut])){
							// $tab.="<td align=right></td>";
						// }else{							
							// $tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg]/$pres[$urut],2)."</td>";
						// }
						// $tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg],2)."</td>";
					// }else{				
						// $tab.="<td align=left>".$nmbrg[$kdbrg]."</td>";
						// $tab.="<td align=left>".$nmsat[$kdbrg]."</td>";
						// if(is_nan($jlhbrg[$urut][$kdbrg]/$pres[$urut])){
							// $tab.="<td align=right></td>";
						// }else{							
							// $tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg]/$pres[$urut],2)."</td>";
						// }
						// $tab.="<td align=right>".numb_format($jlhbrg[$urut][$kdbrg],2)."</td>";
					// }
				// }
				
				foreach($vbrg as $kdbrg){
					$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
					$nmsat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbrg."'");
					if($kdbrg!=''){						
						$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
						$tab.="<tr class=rowcontent style=vertical-align:top;>";
						$tab.="<td align=left width=150px>".$nmbrg[$kdbrg]."</td>";
						$tab.="<td align=left width=50px>".$nmsat[$kdbrg]."</td>";
						if(is_nan($jlhbrg[$urut][$kdbrg]/$pres[$urut])){
							$tab.="<td align=right  width=50px></td>";
						}else{							
							$tab.="<td align=right  width=50px>".numb_format($jlhbrg[$urut][$kdbrg]/$pres[$urut],2)."</td>";
						}
						$tab.="<td align=right  width=50px>".numb_format($jlhbrg[$urut][$kdbrg],2)."</td>";
						$tab.="</tr >";
						$tab.="</table >";
					}
				}
				
				$tab.="</td>";
				if($nbrg==0){
					$tab.="<td align=right ".$row.">".numb_format($jjg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($kg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($unit[$urut])."</td>";
					$tab.="<td align=left ".$row.">".$nmkar[$mandor[$urut]]."</td>";
				}else{
					$tab.="<td align=right ".$row.">".numb_format($jjg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($kg[$urut])."</td>";
					$tab.="<td align=right ".$row.">".numb_format($unit[$urut])."</td>";
					$tab.="<td align=left ".$row.">".$nmkar[$mandor[$urut]]."</td>";
				}
			}
		}
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=3>T O T A L</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".numb_format($tkbl,2)."</td>";
		$tab.="<td align=right>".numb_format($tkht,2)."</td>";
		$tab.="<td align=right>".numb_format($tkhl,2)."</td>";
		$tab.="<td align=right>".numb_format($tbor,2)."</td>";
		$tab.="<td align=right>".numb_format($tkbl+$tkht+$tkhl+$tbor,2)."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".numb_format($tjjg,2)."</td>";
		$tab.="<td align=right>".numb_format($tkg,2)."</td>";
		$tab.="<td align=right>".numb_format($tunit,2)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
				
		if($tipe!='excel'){			
			echo $tab;
		}else{
			$opttgl = makeOption($dbname,'kebun_rkhht','notransaksi,tanggal',"notransaksi='".$notransaksi."'");
			$tgl = $opttgl[$notransaksi];
			$tgl = str_replace("-","",$tgl);
			
			$nop = "rkh_".$tgl.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("rkh_".$tgl, $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		
			
	break;
	
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>	
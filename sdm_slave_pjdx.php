<?php
require_once('config/connection.php');
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$strlang=$owlPDO->query("select legend,ID from ".$dbname.".bahasa order by legend");
		$strlang->setFetchMode(PDO::FETCH_NUM);
		while($barlang=$strlang->fetch()) {
			$_SESSION['lang'][$barlang[0]]=$barlang[1];
		}
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method               = checkPostGet('method','');
$unit                 = checkPostGet('unit','');
$notransaksi          = checkPostGet('notransaksi','');
$jenis                = checkPostGet('jenis','');
$kodeapproval         = checkPostGet('kodeapproval','');
$kepada               = checkPostGet('kepada','');
$check                = checkPostGet('check','');
$tanggal			  = checkPostGet('tanggal','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

$param['tglawalreal'] = tanggalsystemn($param['tglawalreal']);
$param['tglawal']     = tanggalsystemn($param['tglawal']);
$param['tglakhirreal']= tanggalsystemn($param['tglakhirreal']);
$param['tglakhir']    = tanggalsystemn($param['tglakhir']);
$path                 = "fileupload/sdm_pjdinas/";


if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){
	$tampilbiaya='1';
	$tampilagenda='1';
	$jenisagenda='renc';
}elseif($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
	$tampilbiaya='2';
	$tampilagenda='2';
	$jenisagenda='real';
}elseif($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
	$tampilbiaya='2';
	$tampilagenda='1';
	$jenisagenda='real';
}elseif($_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){
	$tampilbiaya='3';
	$tampilagenda='2';
	$tampilagenda='1';
	$jenisagenda='real';
}else{
	$jenisagenda='real';
	$tampilbiaya='4';
	$tampilagenda='4';
}

$arrjabatan=array('41');
$arrHsl=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['belumdiajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['cancel']);


switch($method){
	case'postingconfirm':
		$str = "update " . $dbname . ".sdm_pjdinasht set statusconfirm='1' where notransaksi = '".$notransaksi."'"; 
		#exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'updateheader':
		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		if($param['pttujuan']==''){
			throw new PDOException("PT Tujuan wajib diisi.");
		}
		if($param['regiontujuan']==''){
			throw new PDOException("Regional tujuan wajib dipilih.");
		}
		if($param['tglawal']=='' or $param['tglakhir']==''){
			throw new PDOException("Tanggal dinas wajib diisi.");
		}
		if($param['tglawal']>$param['tglakhir']){
			throw new PDOException("Tanggal mulai tidak boleh lebih besar dari tanggal sampai.");
		}
		if($param['tiket']==''){
			throw new PDOException("Tiket pesawat udara wajib dipilih.");
		}
		if($param['ketdinas']==''){
			throw new PDOException("Keterangan wajib diisi.");
		}
		
		if($param['tglawalreal']=='--' or $param['tglawalreal']==''){
			$param['tglawalreal'] = $param['tglawal'];
		}
		if($param['tglakhirreal']=='--' or $param['tglakhirreal']==''){
			$param['tglakhirreal'] = $param['tglakhir'];
		}
		
		$data = array();
		$data = array(
			'karyawanid'        => $param['karyawanid'],
			'level'             => $param['tipekary'],
			'tgldinasdari'      => $param['tglawal'],
			'tgldinassampai'    => $param['tglakhir'],
			'tgldinasdarireal'  => $param['tglawalreal'],
			'tgldinassampaireal'=> $param['tglakhirreal'],
			'kodeorg'           => $param['lokasitugas'],
			'keterangan'        => $param['ketdinas'],
			'pttujuan'          => $param['pttujuan'],
			'unittujuan'        => $param['unittujuan'],
			'regiontujuan'      => $param['regiontujuan'],
			'tiket'             => $param['tiket'],
			'updateby'          => $_SESSION['standard']['userid']
		);
		$where = "notransaksi='".$notransaksi."'";
		$str = updateQuery($dbname,'sdm_pjdinasht',$data,$where);
		$owlPDO->exec($str);

		
		
		if($_SESSION['rute'] != array()){
			$str = "delete from " . $dbname . ".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$data=array();
			$no=0;
			foreach($_SESSION['rute'] as $key=>$row){
				if($row['notransaksi'] == $notransaksi){
					$no+=1;
					$data = array(
						'id'          => $no,
						'notransaksi' => $notransaksi,
						'waktu'       => $row['tglrute'],
						'dari'        => $row['dari'],
						'tujuan'      => $row['tujuan'],
						'transportasi'=> $row['transport']
					);
					
					$cols = array();
					foreach($data as $keyn=>$rown) {
							$cols[] = $keyn;
					}
					$str = insertQuery($dbname,'sdm_pjdinasdt_rute',$data,$cols);
					$owlPDO->exec($str);
				}
			}
		}
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'insertheader':

		#insert ht dulu
		$count_rute = count($_SESSION['rute']);

		if($count_rute <= 0){
			exit("Warning : Rute wajib diisi !");
		}

		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			$str="select * from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$kodelok=$bar['lokasitugas'];
			}
			
			$str="select max(right(notransaksi,5)) as nomor from ".$dbname.".sdm_pjdinasht where kodeorg='".$kodelok."' and createtime like '".date('Y')."%'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$no=$bar['nomor'];
			}

			if($no==0){
				$notran=$kodelok.date('Y').'00001';
			}else{
				$notran=$kodelok.date('Y').addZero($no+1,5);
			}

			$notransaksi=$notran;
		}

		if($param['pttujuan']==''){
			throw new PDOException("PT Tujuan wajib diisi.");
		}
		if($param['tglawal']=='--' or $param['tglakhir']=='--'){
			throw new PDOException("Tanggal dinas wajib diisi.");
		}
		if($param['tglawal']>$param['tglakhir']){
			throw new PDOException("Tanggal mulai tidak boleh lebih besar dari tanggal sampai.");
		}
		if($param['regiontujuan']==''){
			throw new PDOException("Regional tujuan wajib dipilih.");
		}
		if($param['tiket']==''){
			throw new PDOException("Tiket pesawat udara wajib dipilih.");
		}
		if($param['ketdinas']==''){
			throw new PDOException("Keterangan wajib diisi.");
		}
		if($param['stsawal']=='sdm_pengajuanpjdstaffx'){
			$tipekary='0'; #staff
		}else{
			$tipekary='1'; #nonstaff
		}
		
		$data = array();
		$data = array(
			'notransaksi'       => $notransaksi,
			'karyawanid'        => $param['karyawanid'],
			'tipekary'          => $tipekary,
			'level'             => $param['tipekary'],
			'tgldinasdari'      => $param['tglawal'],
			'tgldinassampai'    => $param['tglakhir'],
			'tgldinasdarireal'  => $param['tglawal'], #default pertama isinya sama
			'tgldinassampaireal'=> $param['tglakhir'], ##default pertama isinya sama
			'kodeorg'           => $param['lokasitugas'],
			'keterangan'        => $param['ketdinas'],
			'pttujuan'          => $param['pttujuan'],
			'unittujuan'        => $param['unittujuan'],
			'regiontujuan'      => $param['regiontujuan'],
			'tiket'             => $param['tiket'],
			'statuspengajuan'   => '0',
			'statusrealisasi'   => '0',
			'createdby'         => $_SESSION['standard']['userid'],
			'createtime'        => date("Y-m-d H:i:s"),
			'updateby'          => $_SESSION['standard']['userid']
		);
		
		$cols = array();
		foreach($data as $keyn=>$rown) {
				$cols[] = $keyn;
		}
		$str = insertQuery($dbname,'sdm_pjdinasht',$data,$cols); #exit("error".$str);
		$owlPDO->exec($str);

		// echo "<pre>";
		// 	print_r($_SESSION['rute']);
		// echo "</pre>";

		if($_SESSION['rute'] != array()){
			$str = "delete from " . $dbname . ".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			// echo "<pre>";
			// 	print_r($_SESSION['rute']);exit("Error:A");
			// echo "</pre>";
			$data=array();
			$no=0;
			foreach($_SESSION['rute'] as $key=>$row){
				if($row['notransaksi'] == $param['karyawanid']){
					$no+=1;
					$data = array(
						'id'          => $no,
						'notransaksi' => $notransaksi,
						'waktu'       => $row['tglrute'],
						'dari'        => $row['dari'],
						'tujuan'      => $row['tujuan'],
						'transportasi'=> $row['transport']
					);
					
					$cols = array();
					foreach($data as $keyn=>$rown) {
							$cols[] = $keyn;
					}
					$str = insertQuery($dbname,'sdm_pjdinasdt_rute',$data,$cols); #exit("error".$str);
					$owlPDO->exec($str);
					
					#unset($_SESSION['pajak'][$key]);
				}
			}
		}
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		echo $notransaksi;
	break;
	
	case'simpantglreal':
		try {
		$owlPDO->beginTransaction();
		$data = array();
		$data = array(
			'tgldinasdarireal'  => $param['tglawalreal'],
			'tgldinassampaireal'=> $param['tglakhirreal']
		);
		$where = "notransaksi='".$notransaksi."'";
		$str = updateQuery($dbname,'sdm_pjdinasht',$data,$where);
		$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	
	case'loadinputdetail':
		
		OPEN_BOX();
		$judultab=$_SESSION['lang']['estimasibiaya'];
		$judulagn=$_SESSION['lang']['rencanakegiatan'];
		if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx' or $_SESSION['pjd']['menu']=='sdm_confirmpjdx'  or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx' ){
			$judultab=$_SESSION['lang']['realisasi']." ".$_SESSION['lang']['biaya'];
			$judulagn=$_SESSION['lang']['hasilkerjarealisasi'];
		}
		
		$realtglawal=makeOption($dbname,'sdm_pjdinasht','notransaksi,tgldinasdarireal',"notransaksi='".$notransaksi."'");
		$realtgakhir=makeOption($dbname,'sdm_pjdinasht','notransaksi,tgldinassampaireal',"notransaksi='".$notransaksi."'");
		if($param['tglawalreal']=='--'){
			$param['tglawalreal']=$realtglawal[$notransaksi];
		}
		if($param['tglakhirreal']=='--'){
			$param['tglakhirreal']=$realtgakhir[$notransaksi];
		}
		
		if($param['tglawal']<=$param['tglawalreal']){
			$param['tglawal']=$param['tglawal'];
		}else{
			$param['tglawal']=$param['tglawalreal'];
		}
		
		if($param['tglakhir']>=$param['tglakhirreal']){
			$param['tglakhir']=$param['tglakhir'];
		}else{
			$param['tglakhir']=$param['tglakhirreal'];
		}
		
		$rangetgl = rangeTanggal($param['tglawal'],$param['tglakhir']);
		
		#=== TAB BIAYA ===
		$frm[0]="";
		$frm[0].="<fieldset><legend>".$judultab."</legend>
				<table border=0 cellpadding=1 cellspacing=1 class=sortable>
				<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$frm[0].="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['jenisbiaya']."</td>
				<td align=center rowspan=2 name=fielddriver style=display:none>".$_SESSION['lang']['jenis']."</td>
				<td align=center rowspan=2 name=fielddriver style=display:none>".$_SESSION['lang']['tujuan']."</td>
				<td align=center ".$rows." >".$_SESSION['lang']['location']."</td>
				<td align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</td>
				<td align=center ".$rows." >".$_SESSION['lang']['totalbiaya']."</td>
				<td align=center ".$rows." >".$_SESSION['lang']['keterangan']."</td>
				<td align=center ".$rows." colspan=2>".$_SESSION['lang']['kntprson']."</td>
				<td align=center colspan=1 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr class=rowheader>";
			foreach($rangetgl as $tgl){
				$style="";
				if($tgl<$param['tglawalreal'] or $tgl>$param['tglakhirreal']){
					$style="style=color:red;";
				}
				$frm[0].="<td ".$style." align=center>".substr($tgl,8,2)."</td>";
			}
			$frm[0].="</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		// $jabatan = getKary($param['karyawanid'],'kodejabatan');
		// $str="select * from ".$dbname.".sdm_5jabatan where kodejabatan='".$jabatan."' and (namajabatan like '%driver%' or namajabatan like '%sopir%' or namajabatan like '%supir%')";
		// $res=fetchdata($str);
		// if(count($res)>0){
		// 	$where=" and driver='1'";
		// }
		
		$optjnsbyy="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".sdm_5jenisbiayapjdinas where 1=1 ".$where." order by id asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optjnsbyy.="<option value=".$bar['id'].">".$bar['id']." - ".$bar['keterangan']."</option>";
		}
		#=== Isi input detail ===
		$frm[0].="<tbody id=inputbiaya>
		<tr class=rowcontent>
			<td valign=top align=center>1</td>
			<td valign=top><select id=jenisbiaya  style='width:100px;'>".$optjnsbyy."</select></td>
			<td valign=top name=fielddriver style=display:none><select style=display:none name=fielddriver onchange=clearcheck(); id=jenisbiayadriver style='width:100px;'>".$optjnsbyydriv."</select></td>
			<td valign=top name=fielddriver style=display:none><select style=display:none name=fielddriver onchange=clearcheck(); id=tujubiayadriver style='width:100px;'>".$opttujbyydriv."</select></td>
			<td valign=top><input disabled id=tempatkunjungan value=\"".$param['unittujuan']."\" class=myinputtext style='width:100px;'></td>";
			
			$disable="";
			if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
				$disable="disabled";
			}
			
			$wh="";
			$optkary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$optkary.="<option value=''>NIK - Nama Karyawan [lokasitugas] [jabatan]</option>";
			$wh.=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')";
			#$wh.=" and tipekaryawan not in ('3','4')";
			$str="select * from ".$dbname.".datakaryawan where 1=1 ".$wh." order by namakaryawan asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['kodejabatan']."'");
				$n="";
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx' and $bar['karyawanid']==$_SESSION['standard']['userid']){
					$n="selected";
				}else if($bar['karyawanid']==$param['karyawanid']){
					$n="selected";
				}
				$optkary.="<option ".$n." value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." [".$bar['lokasitugas']."] [".$optjab[$bar['kodejabatan']]."]</option>";
			}

			$no=0;
			foreach($rangetgl as $tgl){				
			$no+=1;
				$frm[0].="<td align=center valign=top>
					<input hidden id=jlhplafon".$no.">
					<input hidden id=tgl".$no." value=".$tgl.">
					<input type='checkbox' name=stat onclick=getjlh(this.id,'".$no."'); id=status".$no."><br>
					<input style=display:none;width:50px; name=jlhbyy placeholder=Rp. onkeyup=ttlastbyy(); class=myinputtextnumber id=jumlah".$no." onkeypress='return angka_doang(event)'>
				</td>";
			}
		$frm[0].="
				<td valign=top><input id=totalestbyy disabled class=myinputtextnumber onkeypress='return angka_doang(event)' style='width:60px;'></td>
				<td valign=top><input id=ketestbyy class=myinputtext type=text style='width:150px;'></td>
				<td valign=top><select id=pic ".$disable." style='max-width:150px;'>".$optkary."</select></td>
				<td valign=top><img id='pic' onclick=z.elSearch('pic',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				<td valign=top align=center><img title='Simpan' class='zImgBtn' onclick=simpanest('".$no."','renc'); src='images/save.png'>
				</td>
		";
					
		$frm[0].="</tr>
						<input hidden id=jlhtgl value='".$no."'>
						<input hidden id=methodbyy value='insertestbyy'>";
						
		$frm[0].="<tr class=rowcontent>";
		if($param['jenistampilan']=='tampilansimple'){
			$tbl="Mode Detail";
		}else{
			$tbl="Mode Simple";
		}
		$hide="hidden";
		if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx' or $_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
			$hide="";
		}
		
		$frm[0].="<td align=right colspan=".(count($rangetgl)+10).">
				<button ".$hide." onclick=rubahtampilan(this.id); id=tombolrubahtampil class=mybutton>".$tbl."</button>
				<button onclick=loadinputdetail(); class=mybutton>Refresh</button>
				</td>";
		$frm[0].="</tr>";
			
		$frm[0].="</tbody></table>";
		if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){
		$frm[0].="<label><span>Info:</span>
			<li>Jika ingin mengajukan <b>uang muka</b> maka kolom <b>nilai wajib diisi</b>, tidak tidak ingin mengajukan uang muka silahkan dikosongkan.</li>
			<!--
			<li>Jika biaya menjadi tanggungan karyawan (yang nantinya akan di reimburse / klaim ke perusahaan) maka kolom Kontak Person di isi dengan nama karyawan yang bersangkutan.</li>
			<li>contoh untuk Premi PJD nama kontak person diisi dengan nama karyawan yang bersangkutan.</li>
			-->
			</label>";
		}			
					
		$frm[0].="</fieldset><hr>";
		#=== List data tersimpan input detail ===	
		$frm[0].="<div style=clear:both></div>";
		$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
					<div id=loaddatabiaya></div>
				</fieldset>";
			
		#=== TAB AGENDA ===
		switch($tampilagenda){
			case'1':
			$frm[1]="<fieldset><legend>" . $judulagn. "</legend>
				<table border=0>";
				$rows="rowspan=1";$no=1;
				$frm[1].="
				<tr>
					<td>".$_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td><input type='text' readonly=readonly class='myinputtext' id=tglagenda".$no." onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;' /></td>
					<td>s/d</td>
					<td><input type='text' readonly=readonly class='myinputtext' id=tglagenda2".$no." onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:100px;' /></td>

					<td align=center ".$rows.">".$_SESSION['lang']['location'] . "</td>
					<td>:</td>
					<td ><input id=lokasiagenda".$no." value=\"".$param['unittujuan']."\" class=myinputtext style='width:175px;'></td>
					
					<td align=center ".$rows.">Koordinasi dengan</td>
					<td>:</td>
					<td ><input id=picagenda".$no." class=myinputtext style='width:200px;'></td>
				</tr>
				<tr>
					<td valign=top align=center ".$rows.">".$judulagn. "</td>
					<td valign=top>:</td>
					<td colspan=18><textarea rows='10' id=renckeg".$no." type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:620px;\"></textarea></td>
					
				</tr>";
			$frm[1].="<tr>
					<td align=right colspan=2></td>
					<td colspan=8>
						<button onclick=simpanagenda('".$no."') class=mybutton>".$_SESSION['lang']['save']."</button>
						<button onclick=clearagenda('".$no."') class=mybutton>".$_SESSION['lang']['cancel']."</button>
						<!--<button onclick=simpanallagenda('".$no."') class=mybutton>".$_SESSION['lang']['saveall']."</button>-->
						<button onclick=loaddatarenckegiatan() class=mybutton>Refresh</button> ";
			if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
				$frm[1].="<button onclick=\"showupload('event','".$param['notransaksi']."','realkeg')\" class=mybutton>Upload</button>";
			}
			
			$frm[1].="</td>";
			$frm[1].="</tr>";
			$frm[1].="
				<input hidden id=jenisagenda value='".$jenisagenda."'>
				<input hidden id=methodagenda value='insertagenda'>
				</tbody></table></fieldset><hr>";
			
			break;
		}	
		#=== List data tersimpan input agenda ===	
		$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
			<div id=loaddatarenckegiatan>
			</div></fieldset>";
					
		$hfrm[0]=$judultab;
		$hfrm[1]=$judulagn;
		drawTab('FRM',$hfrm,$frm,175,'100%');

		CLOSE_BOX();
		
	break;
	case'getdetailjnsbyy':
		$str="select distinct * from ".$dbname.".sdm_5setupdinasdriver where jenisbiaya='".$param['jenisbiaya']."' and status='1'";
		// exit("error".$str);
		$res=fetchdata($str);
		$optjenis="<option value=''></option>";
		$umnpremi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $bar){
			if($bar['jenis']=='tujuan'){				
				$optjenis.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
			}else{
				$umnpremi.="<option value=".$bar['id'].">".$bar['keterangan']."</option>";
			}
		}
		
		echo count($res)."##".$optjenis."##".$umnpremi;
	break;
	case'loaddatarenckegiatan':
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
			<td align=center ".$rows.">".$_SESSION['lang']['tanggal'] . "</td>
			<td align=center ".$rows.">".$_SESSION['lang']['hari'] . "</td>
			<td align=center ".$rows.">#</td>
			<td align=center ".$rows.">".$_SESSION['lang']['location'] . "</td>
			<td align=center ".$rows.">".$_SESSION['lang']['kegiatan'] . "</td>
			<td align=center ".$rows.">Koordinasi<br>Dengan</td>";
		if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
			$tab.="<td align=center ".$rows.">Confirm</td>";
			$tab.="<td align=center ".$rows.">".$_SESSION['lang']['keterangan'] . "</td>";
			$tab.="<td align=center ".$rows.">".$_SESSION['lang']['tanggal'] . "</td>";
		}
		$tab.="<td align=center ".$rows." colspan=2>" . $_SESSION['lang']['action'] . "</td>
		</tr>
		</thead>";
		switch($tampilagenda){
			case'1':
			$data=array();
			$arrjns=array();
			$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['tanggal']]=$bar['tanggal'];
				
				$tgl_sd[$bar['tanggal']]=$bar['tanggal2'];
				$tgl_sd[$bar['tanggal']]=$bar['tanggal2'];

				$lok[$bar['tanggal']][$bar['jenis']]=$bar['lokasi'];
				$ket[$bar['tanggal']][$bar['jenis']]=$bar['keterangan'];
				$koo[$bar['tanggal']][$bar['jenis']]=$bar['koordinasidengan'];
				$upd[$bar['tanggal']][$bar['jenis']]=$bar['updateby'];
				$tglupd[$bar['tanggal']][$bar['jenis']]=$bar['updatetime'];
				if($bar['statusconfrim']==1){
					$sta='Ya';
				}else{
					$sta='Tidak';
				}
				$stsc[$bar['tanggal']][$bar['jenis']]=$sta;
			}
			
			$arrjns=getEnum($dbname,'sdm_pjdinasdt2','jenis');
			$no=0;
			foreach($data as $tglagen){
				$n="";
				if(hari($tglagen,'ID')=='Minggu'){
					$n="style=color:red";
				}
				$no+=1;
				$tab.="<tr class=rowcontent style=vertical-align:top>";
				$tab.="<td align=center rowspan=3>".$no."</td>";
				$tab.="<td align=center rowspan=3>".$tglagen." - ".$tgl_sd[$tglagen]."</td>";
				$tab.="<td align=center rowspan=3 ".$n.">".hari($tglagen,'ID')." - ".hari($tgl_sd[$tglagen],'ID')." </td>";
				foreach($arrjns as $jns){
					if($jns=='renc' and $ket[$tglagen][$jns]!=''){
						$tab.="<td align=left style=font-style:italic;font-size:10px;>".$jns."</td>";
						$tab.="<td align=left style=\"background-color:#CDFED1\">".$lok[$tglagen][$jns]."</td>";
						$tab.="<td align=left style=\"background-color:#CDFED1\">".nl2br($ket[$tglagen][$jns])."</td>";
						$tab.="<td align=left style=\"background-color:#CDFED1\">".$koo[$tglagen][$jns]."</td>";
						
						if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){						
							$tab.="<td align=center width=20px><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editagenda('".$param['notransaksi']."','".$tglagen."','".$tgl_sd[$tglagen]."','".$jns."');\"></td>";
							$tab.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delagenda('".$param['notransaksi']."','".$jns."','".$tglagen."','".$tgl_sd[$tglagen]."');\" title='Delete'></td>";
						}else{
							$tab.="<td align=center width=20px></td>";
							$tab.="<td align=center width=20px></td>";
						}
					}elseif($jns=='renc' and $ket[$tglagen][$jns]==''){
						$tab.="<td colspan=6></td>";
					}
					if($jns=='conf' and $ket[$tglagen][$jns]!=''){
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$upd[$tglagen][$jns]."'");
						$tab.="<tr class=rowcontent style=vertical-align:top>";
						$tab.="<td align=left style=font-style:italic;font-size:10px;background-color:#E3FFB1;color:blue;>".$jns."</td>";
						$tab.="<td align=left style=\"background-color:#E3FFB1\">".$lok[$tglagen][$jns]."</td>";
						$tab.="<td align=left style=\"background-color:#E3FFB1\">".nl2br($ket[$tglagen][$jns])."</td>";
						$tab.="<td align=left colspan=1 style=\"background-color:#E3FFB1;font-size:10px;\">Konfirmasi : ".$stsc[$tglagen][$jns]."<br>Oleh : ".$optnm[$upd[$tglagen][$jns]]."<br>Tanggal : ".tanggalnormal($tglupd[$tglagen][$jns])."</td>";
						$tab.="<td align=center width=20px></td>";
						$tab.="<td align=center width=20px></td>";
						$tab.="</tr>";
					}elseif($jns=='conf' and $ket[$tglagen][$jns]==''){
						$tab.="<tr class=rowcontent>";
						$tab.="<td colspan=6></td>";
					}
					
					if($jns=='real' and $ket[$tglagen][$jns]!=''){
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=left style=font-style:italic;font-size:10px;background-color:green;color:white;>".$jns."</td>";
						$tab.="<td align=left style=\"background-color:#B2DCFE\">".$lok[$tglagen][$jns]."</td>";
						$tab.="<td align=left style=\"background-color:#B2DCFE\">".nl2br($ket[$tglagen][$jns])."</td>";
						$tab.="<td align=left style=\"background-color:#B2DCFE\">".$koo[$tglagen][$jns]."</td>";
						
						if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
							$tab.="<td align=center width=20px><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editagenda('".$param['notransaksi']."','".$tglagen."','".$tgl_sd[$tglagen]."','".$jns."');\"></td>";
							$tab.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delagenda('".$param['notransaksi']."','".$jns."','".$tglagen."','".$tgl_sd[$tglagen]."');\" title='Delete'></td>";
						}else{
							$tab.="<td align=center width=20px></td>";
							$tab.="<td align=center width=20px></td>";
						}
						$tab.="</tr>";
					}elseif($jns=='real' and $ket[$tglagen][$jns]==''){
						$tab.="<tr class=rowcontent>";
						$tab.="<td colspan=6></td>";
					}
					
				}
				$tab.="</tr>";
			}
			break;
			case'2':
			$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."' and jenis='renc'";
			$res=fetchdata($str);
			$no=0;
			foreach($res as $bar){
				$n="";
				if(hari($bar['tanggal'],'ID')=='Minggu'){
					$n="style=color:red";
				}
				$no+=1;
				$rowsp="rowspan=2";
				$tab.="<tr class=rowcontent style=vertical-align:top>";
				$tab.="<td align=center ".$rowsp.">".$no."</td>";
				$tab.="<td align=center ".$rowsp.">".$bar['tanggal']."</td>";
				$tab.="<td align=center  ".$rowsp." ".$n.">".hari($bar['tanggal'],'ID')."</td>";
				$tab.="<td align=left ".$rowsp." style=font-style:italic;font-size:10px;background-color:green;color:white;>renc</td>";
				$tab.="<td align=left ".$rowsp.">".$bar['lokasi']."</td>";
				$tab.="<td align=left ".$rowsp.">".nl2br($bar['keterangan'])."</td>";
				$tab.="<td align=left ".$rowsp.">".$bar['koordinasidengan']."</td>";
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$tab.="<td align=center style=height:15px>
						<div><input type=checkbox name=confirm id=confirmstat".$no." onclick=getjudulconf('".$no."');><span id=judulconf".$no.">".$_SESSION['lang']['tidak']."</span></div>
					</td>";
					$tab.="<td align=left><input id=ketconfirm".$no." class=myinputtext style='width:165px;'></td>";
					$tab.="<td align=left><input type='text' readonly=readonly class='myinputtext' id='tglconfirm".$no."' onmousemove='setCalendar(this.id)' onkeypress='return false;' style='width:67px;'/></td>";
					
					$tab.="<td align=center width=20px><img title=Simpan class=zImgBtn onclick=simpanconfirm('".$no."','".$bar['tanggal']."','conf'); src=images/save.png></td>";
					$tab.="<td align=center width=20px><img title=Bersihkan class=zImgBtn onclick=clearconfirm('".$no."') src=images/clear.png></td>";
				}else{					
					$tab.="<td align=center width=20px ".$rowsp."></td>";
					$tab.="<td align=center width=20px ".$rowsp."></td>";
				}
				$tab.="</tr>";
				$tab.="<tr class=rowcontent style=vertical-align:top>";
				
				$strx="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."' and jenis='conf' and tanggal='".$bar['tanggal']."'";
				$resx=fetchdata($strx);
				if(count($resx)>0){
					if($resx[0]['statusconfrim']==1){					
						$tab.="<td align=center>Ya</td>";
					}else{
						$tab.="<td align=center style=color:red>Tidak</td>";
					}
					$tab.="<td>".$resx[0]['keterangan']."</td>";
					$tab.="<td align=center>".tanggalnormal($resx[0]['updatetime'])."</td>";
					$tab.="<td align=center width=20px><img title=Edit class=zImgBtn onclick=\"editconfirm('".$no."','".$bar['tanggal']."','".$resx[0]['statusconfrim']."','".$resx[0]['keterangan']."','".tanggalnormal($resx[0]['updatetime'])."','".$resx[0]['jenis']."')\"; src=images/application/application_edit.png></td>";
					$tab.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delagenda('".$bar['notransaksi']."','".$resx[0]['jenis']."','".$bar['tanggal']."')\"; title=Delete></td>";
				}else{
					$tab.="<td></td><td></td><td></td><td></td><td></td>";
				}
				$tab.="</tr>";
			}
		
			break;
		}
		echo $tab;
	break;
	case'simpanconfirm':
		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		
		$param['tgl']       = $param['tgl'];
		$param['tglconfirm']= tanggalsystemn($param['tglconfirm']);
		
		$data = array();
		$str = "delete from " . $dbname . ".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."' and jenis='".$param['jenis']."' and tanggal='".$param['tgl']."'"; #exit("error".$str);
		$owlPDO->exec($str);
		
		$lokasi=makeOption($dbname,'sdm_pjdinasdt2','notransaksi,lokasi',"notransaksi='".$notransaksi."' and jenis='renc' and tanggal='".$param['tgl']."'");
		$data = array(
			'notransaksi'  => $notransaksi,
			'jenis'        => $param['jenis'],
			'tanggal'      => $param['tgl'],
			'lokasi'       => $lokasi[$notransaksi],
			'statusconfrim'=> $param['stat'],
			'keterangan'   => $param['ketconfirm'],
			'updateby'     => $_SESSION['standard']['userid'],
			'updatetime'   => $param['tglconfirm']
		);
		
		$cols = array();
		foreach($data as $keyn=>$rown) {
				$cols[] = $keyn;
		}
		$str = insertQuery($dbname,'sdm_pjdinasdt2',$data,$cols); #exit("error".$str);
		$owlPDO->exec($str);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	
	
	
	case'loaddatabiaya':
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$optjnsdriver=makeOption($dbname,'sdm_5setupdinasdriver','id,keterangan');
		
		$realtglawal=makeOption($dbname,'sdm_pjdinasht','notransaksi,tgldinasdarireal',"notransaksi='".$notransaksi."'");
		$realtgakhir=makeOption($dbname,'sdm_pjdinasht','notransaksi,tgldinassampaireal',"notransaksi='".$notransaksi."'");
		if($param['tglawalreal']=='--'){
			$param['tglawalreal']=$realtglawal[$notransaksi];
		}
		if($param['tglakhirreal']=='--'){
			$param['tglakhirreal']=$realtgakhir[$notransaksi];
		}
		
		if($param['tglawal']<=$param['tglawalreal']){
			$param['tglawal']=$param['tglawal'];
		}else{
			$param['tglawal']=$param['tglawalreal'];
		}
		
		if($param['tglakhir']>=$param['tglakhirreal']){
			$param['tglakhir']=$param['tglakhir'];
		}else{
			$param['tglakhir']=$param['tglakhirreal'];
		}
		
		$rangetgl = rangeTanggal($param['tglawal'],$param['tglakhir']);
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and (umdriver!='' or tujuandriver!='')";
		$res=fetchdata($str);
		$dr=count($res);
		if($dr>0){
			$i="style=display:''";
			$colhide=2;
		}else{
			$colhide=0;
			$i="style=display:none";
		}
		
		$tab.="<table border=0 cellpadding=5 cellspacing=1 class=sortable>
		<thead><tr class=rowheader>";
		$rows="rowspan=2";	
		$tab.="<td align=center ".$rows." width=20px>No</td>
			<td align=center ".$rows." >".$_SESSION['lang']['jenisbiaya']."</td>
			<td align=center rowspan=2 ".$i.">".$_SESSION['lang']['jenis']."</td>
			<td align=center rowspan=2 ".$i.">".$_SESSION['lang']['tujuan']."</td>
			<td align=center ".$rows." >".$_SESSION['lang']['location']."</td>
			<td align=center rowspan=2 colspan=2>#</td>
			<td align=center colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</td>
			<td align=center ".$rows." >".$_SESSION['lang']['totalbiaya']."</td>
			<td align=center ".$rows." >".$_SESSION['lang']['keterangan']."</td>
			<td align=center ".$rows." >".$_SESSION['lang']['kntprson']."<br>".$_SESSION['lang']['dibuat']."</td>
			<td align=center colspan=2 ".$rows.">" . $_SESSION['lang']['action'] . "</td>
		</tr>
		<tr class=rowheader>";
		foreach($rangetgl as $tgl){
			$style="";
			if($tgl<$param['tglawalreal'] or $tgl>$param['tglakhirreal']){
				$style="style=color:red;";
			}
			
			$tab.="<td ".$style." align=center>".substr($tgl,8,2)."</td>";
		}
		$tab.="</tr>
		</thead><tbody>";

		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and (umdriver!='' or tujuandriver!='')";
		$res=fetchdata($str);
		$dr=count($res);
		$row="";$con=0;$conttl=0;
		if($dr==0){					
			if($jenis!='pdf'){					
				$con=(count($rangetgl)+6);
			}else{
				$con=6;
			}
			$conttl=0;
		}else{
			if($jenis!='pdf'){					
				$con=(count($rangetgl)+8);
			}else{
				$con=8;
			}
			$conttl=2;
		}
		
		#ini bagian isinya
		switch($_SESSION['pjd']['menu']){
			case'sdm_pertanggungjawabanpjdstaffx':
			case'sdm_pertanggungjawabanpjdnonstaffx':
			case'sdm_confirmpjdx':

			#=========================================================================================================================================
			if($param['jenistampilan']=='tampilandetail'){
				
				# UANG KLAIM
				#klaim oleh karyawan
				# and tanggungan='1' and sumber='1'
				#real oleh perusahaan
				# and tanggungan='0' and sumber='1'
				#renc
				# and sumber='0'
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$where="and sumber='0' or (tanggungan='0' and sumber='1') or (tanggungan='1' and sumber='1')";
					$wh="and tanggungan='1' and sumber='1'";
				}else{
					$where="and sumber='0' or (tanggungan='1' and sumber='1') or (tanggungan='0' and sumber='1')";
					$wh="and tanggungan='0' and sumber='1'";
				}
				
				
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ".$where." order by jenisbiaya asc";
				$res=fetchdata($str);
				$jumlahsendiri=$ttlbyy=$umdriver=array();
				$datajenisbiaya=$umdriver=$tujuandriver=$t4kunj=$keterangan=$piclokasi=$check=$checkreal=array();
				foreach($res as $bar){
					$datajenisbiaya[$bar['jenisbiaya']]=$bar['jenisbiaya'];
					$umdriver[$bar['jenisbiaya']]=$bar['umdriver'];
					$tujuandriver[$bar['jenisbiaya']]=$bar['tujuandriver'];
					$t4kunj[$bar['jenisbiaya']]=$bar['tempatkunjungan'];
					$piclokasi[$bar['jenisbiaya']]=$bar['updateby'];
					$ttlbyy[$bar['jenisbiaya']]+=$bar['jumlah'];
					
					#real
					if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
						if($bar['tanggungan']=='0' and $bar['sumber']=='1'){					
							$jumlahsendiri[$bar['jenisbiaya']][$bar['tanggal']]+=$bar['jumlah'];
							$checkreal[$bar['jenisbiaya']][$bar['tanggal']]=$bar['check'];
							$keterangan[$bar['jenisbiaya']]=$bar['keterangan'];
						}
					}else{					
						if($bar['tanggungan']=='1' and $bar['sumber']=='1'){					
							$jumlahsendiri[$bar['jenisbiaya']][$bar['tanggal']]+=$bar['jumlah'];
							$checkreal[$bar['jenisbiaya']][$bar['tanggal']]=$bar['check'];
							$keterangan[$bar['jenisbiaya']]=$bar['keterangan'];
						}
					}
					
					#renc
					if($bar['sumber']=='0'){					
						$check[$bar['jenisbiaya']][$bar['tanggal']]=$bar['check'];
					}
				}
				
				$no=0;
				$tab.="<tr class=rowcontent>";
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$tab.="<td colspan=".($con+4)." style=font-weight:bold;background-color:#0745FB;color:white;>Realisasi oleh perusahaan :</td>";
				}else{				
					$tab.="<td colspan=".($con+4)." style=font-weight:bold;background-color:#0745FB;color:white;>Reimburse / Klaim oleh karyawan :</td>";
				}
				$tab.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy){
				
					$no+=1;
					$tab.="<input hidden id=jenisbiayareal".$no." value='".$jenisbyy."'>";
					$tab.="<input hidden id=umdriverreal".$no." value='".$umdriver[$jenisbyy]."'>";
					$tab.="<input hidden id=tujdriverreal".$no." value='".$tujuandriver[$jenisbyy]."'>";

					#RENCANA
					$tab.="<tr class=rowcontent style=font-size:12px>";
					$tab.="<td align=center rowspan=2>".$no."</td>";
					$tab.="<td align=left rowspan=2>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
					if($dr>0){									
						$tab.="<td align=left rowspan=2>".$optjnsdriver[$umdriver[$jenisbyy]]."</td>";
						$tab.="<td align=left rowspan=2>".$optjnsdriver[$tujuandriver[$jenisbyy]]."</td>";
					}
					$tab.="<td align=left rowspan=2 id=tempatkunjungan".$no.">".$t4kunj[$jenisbyy]."</td>";
					$tab.="<td align=left colspan=2 style=font-style:italic;font-size:10px;>Renc</td>";
					$r=0;
					foreach($rangetgl as $tgl){
						$r+=1;
						if($check[$jenisbyy][$tgl]>0){
							$tab.="<td name=rencbyy id=statusrenc".$no."_".$r."  align=center style=background-color:#E7FCE4;cursor:pointer; title='Rencana'>&#10004;</td>";
						}else{
							$tab.="<td></td>";
						}
						$ttlbyytgl[$tgl]+=$jumlahsendiri[$jenisbyy][$tgl];
					}
					$tab.="<td align=right></td>";
					$tab.="<td></td>";
					$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$picloc."'");
					$tab.="<td></td>";
					$tab.="<td colspan=2></td>";
					$tab.="</tr>";
					
					#REALISASI
					$tab.="<tr class=rowcontent style=font-size:11px;>";
					$tab.="<td align=left valign=top style=background-color:green;font-style:italic;font-size:10px;color:white;>Real</td>";
					$tab.="<td align=center style=background-color:green; valign=top><input title=\"Checklist seluruhnya\" style=cursor:pointer; type='checkbox' name=statreal onclick=checkallreal('".$no."'); id=ceckboxrealall".$no."></td>";
					$r=0;$totalnilai=0;
					foreach($rangetgl as $tgl){
						$r+=1;
						#ambil data realisasi ada atau tidak
						$strreal="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ".$wh." and jenisbiaya='".$jenisbyy."' and tanggal='".$tgl."'";
						#echo $strreal."<br>";
						$resreal=fetchdata($strreal);
						$nilairp=0;$sudahreal=$rupiahreal=0;$picreal="";
						foreach($resreal as $barr){
							$sudahreal+=$bar['check'];
							$rupiahreal+=$bar['jumlah'];
						}
						
						$st="style=display:none;cursor:pointer;width:50px;";
						$cekuserlain=$cb="";
						$isi=0; $xx=0;
						if($sudahreal>0){
							#sudah ada realisasi
							$isi=1; $xx=1;
							if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
								$idtujuan="klaimkary";
							}else{
								$idtujuan="realisasi";
							}
							$cekuserlain="style=cursor:pointer;background-color:gray; onmouseover=\"cekrealisasiover('".$idtujuan.$jenisbyy."_".$tgl."')\"; onmouseout=\"cekrealisasiout('".$idtujuan.$jenisbyy."_".$tgl."')\"; title=\"Realisasi.\"";
							$st="style=display:none;cursor:pointer;width:50px; disabled";
							$cb="checked disabled ";							

							
							$nilairp=0;
						}elseif($sudahreal==0 and $checkreal[$jenisbyy][$tgl]>0){
							#sudah pernah di input sebelumnya
							$isi=0; $xx=2;
							$st="style=cursor:pointer;width:50px;";
							$cb="checked";
							$nilairp=$jumlahsendiri[$jenisbyy][$tgl];
						}else{
							$isi=0;$xx=3;
							#belum diapa apain
							$st="style=display:none;cursor:pointer;width:50px;";
							$cb="";
							$nilairp=$jumlahsendiri[$jenisbyy][$tgl];
						}
						$totalnilai+=$jumlahsendiri[$jenisbyy][$tgl];
						#$st="style=width:50px;";
						
						$tab.="<td align=center ".$cekuserlain." valign=top>
						<input hidden name=tglreal".$no." id=tglreal".$no."_".$r." value=".$tgl.">
						<input hidden name=userlain".$no." id=userlain".$no."_".$r." value=".$isi.">

						<input type='checkbox' ".$cb." ".$titre." name=statreal onclick=getjlhreal('".$no."','".$r."'); id=statusreal".$no."_".$r."><br>";
						$tab.="<input hidden id=plafonreal".$no."_".$r.">";
						$tab.="<input ".$st." value=\"".$nilairp."\" ".$titre." name=jlhbyyreal placeholder=Rp. onkeyup=ttlrealbyy('".$no."','".$r."'); class=myinputtextnumber id=jumlahreal".$no."_".$r." onkeypress='return angka_doang(event)'>
						</td>";
					}
					$tab.="<td valign=bottom align=right style='width:50px;'>
							<input value=\"".($totalnilai)."\" id=totalrealbyy".$no." disabled class=myinputtextnumber onkeypress='return angka_doang(event)' style='width:60px;'></td>";
					$tab.="<td valign=bottom style='width:150px;'>
							<input id=ketrealbyy".$no." class=myinputtext type=text style='width:150px;' value=\"".$keterangan[$jenisbyy]."\"></td>
							<input hidden id=picreal".$no." value=".$_SESSION['standard']['userid'].">";
							
					$tab.="<td valign=bottom style=font-size:10px;font-weight:bold;></td>";
					$tab.="<td valign=bottom align=center id=kolomsavereal".$no.">
							<img title='Simpan' class='zImgBtn' onclick=simpanest('".$r."','real','".$no."'); src='images/save.png'></td>";
					// $tab.="<td valign=bottom align=center>
					// 		<img src=images/upload-2-xxl.png class=zImgBtn title=Upload onclick=\"showupload('event','".$param['notransaksi']."','".$jenisbyy."')\"></td>";
					$tab.="<input hidden id=methodbyy value='insertestbyy'>";
					$tab.="</tr>";
				
				}
				$tab.="
				<tr class=rowheader>
					<td></td>
					<td></td>
					<td></td>
					<td colspan=2>Upload File per hari : </td>";

				// group by tanggal
				$str2="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' group by tanggal asc";
				$res2=fetchdata($str2);
				$no=0;
				foreach($res2 as $bar2){
					$no++;
					$tab .= "<td valign=bottom align=center onclick=\"showuploadperhari('event','".$param['notransaksi']."', '".$no."')\"><img src=images/upload-2-xxl.png class=zImgBtn title=Upload></td>";	
					$tab .= "<input type='hidden' id='tanggalPdJUpload_".$no."' value='".$bar2['tanggal']."' />";
				}
				$tab .= "</tr>";
				
				$tab.="<td colspan=5>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=right colspan=".(count($rangetgl)+$colhide+10).">
						<button onclick=loadinputdetail(); class=mybutton>Refresh</button>
						<button onclick=displayList(); class=mybutton>".$_SESSION['lang']['selesai']."</button>
						<button onclick=loadinputdetail(); class=mybutton>".$_SESSION['lang']['cancel']."</button>
						</td>";
				$tab.="</tr>";
				#TUTUP KLAIM
				
				# UANG LIST KLAIM
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' order by jenisbiaya asc";
				$res=fetchdata($str);
				$datajenisbiaya=$umdriver=$tujuandriver=$t4kunj=$keterangan=$piclokasi=$check=$jumlahum=$ttlbyy=$umdriver=array();
				foreach($res as $bar){
					$datajenisbiaya[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['piclokasi'];
					$umdriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['umdriver'];
					$tujuandriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tujuandriver'];
					$t4kunj[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tempatkunjungan'];
					$keterangan[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['keterangan'];
					$piclokasi[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['updateby'];
					$jumlahum[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]+=$bar['jumlah'];
					$ttlbyy[$bar['jenisbiaya']][$bar['piclokasi']]+=$bar['jumlah'];
					$check[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]=$bar['check'];
				}
			
				$no=0;
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=".($con+4)." style=font-weight:bold;background-color:#A7A6FF;>Reimburse / Klaim oleh karyawan :</td>";
				$tab.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valpic){
					foreach($valpic as $picloc => $valpiclock){
						$no+=1;
						$tab.="<tr class=rowcontent style=font-size:12px>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
						if($dr>0){									
							$tab.="<td align=left>".$optjnsdriver[$umdriver[$jenisbyy][$picloc]]."</td>";
							$tab.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$picloc]]."</td>";
						}
						$tab.="<td align=left>".$t4kunj[$jenisbyy][$picloc]."</td>";
						$tab.="<td align=left colspan=2></td>";
						foreach($rangetgl as $tgl){
							if($jenis!='pdf'){
								if($jumlahum[$jenisbyy][$picloc][$tgl]>0){
									$tab.="<td id=klaimkary".$jenisbyy."_".$tgl." align=right>".numb_format($jumlahum[$jenisbyy][$picloc][$tgl])."</td>";
								}elseif($check[$jenisbyy][$picloc][$tgl]>0){
									$tab.="<td id=klaimkary".$jenisbyy."_".$tgl." align=center style=background-color:#FBEDFE;cursor:pointer; title='Realisasi tanpa biaya'>&#10004;</td>";
								}else{
									$tab.="<td id=klaimkary".$jenisbyy."_".$tgl."></td>";
								}
							}
							$ttlbyytgl[$tgl]+=$jumlahum[$jenisbyy][$picloc][$tgl];
						}
						$tab.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$picloc])."</td>";
						$tab.="<td>".$keterangan[$jenisbyy][$picloc]."</td>";
						$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$picloc."'");
						$tab.="<td>".ucwords(strtolower($nmkar[$picloc]))."</td>";
						$tab.="<td colspan=2 align=center><img src=images/uploader/dwnld8.png class=zImgBtn title=Upload onclick=\"popupfile('".$param['notransaksi']."','".$jenisbyy."','klaim')\"></td>";
						$tab.="</tr>";
					}
				}
				
				$tab.="<tr class=rowcontent style=font-size:11px;>";
				$tab.="<td colspan=".($conttl+5)." style=font-size:11px;font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td colspan=2></td>";
				$tab.="</tr>";
				
				#TUTUP LIST KLAIM
				
				# UANG REALISASI
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='0' and sumber='1' order by jenisbiaya asc";
				$res=fetchdata($str);
				$datajenisbiaya=$umdriver=$tujuandriver=$t4kunj=$keterangan=$piclokasi=$check=$jumlahum=$ttlbyy=$umdriver=array();
				foreach($res as $bar){
					$datajenisbiaya[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['piclokasi'];
					$umdriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['umdriver'];
					$tujuandriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tujuandriver'];
					$t4kunj[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tempatkunjungan'];
					$keterangan[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['keterangan'];
					$piclokasi[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['updateby'];
					$jumlahum[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]+=$bar['jumlah'];
					$ttlbyy[$bar['jenisbiaya']][$bar['piclokasi']]+=$bar['jumlah'];
					$check[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]=$bar['check'];
				}
			
				$no=0;
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=".($con+4)." style=font-weight:bold;background-color:#A7A6FF;>Realisasi oleh perusahaan :</td>";
				$tab.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valpic){
					foreach($valpic as $picloc => $valpiclock){
						$no+=1;
						$tab.="<tr class=rowcontent style=font-size:12px>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
						if($dr>0){									
							$tab.="<td align=left>".$optjnsdriver[$umdriver[$jenisbyy][$picloc]]."</td>";
							$tab.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$picloc]]."</td>";
						}
						$tab.="<td align=left>".$t4kunj[$jenisbyy][$picloc]."</td>";
						$tab.="<td align=left colspan=2></td>";
						foreach($rangetgl as $tgl){
							if($jenis!='pdf'){
								if($jumlahum[$jenisbyy][$picloc][$tgl]>0){
									$tab.="<td id=realisasi".$jenisbyy."_".$tgl." align=right>".numb_format($jumlahum[$jenisbyy][$picloc][$tgl])."</td>";
								}elseif($check[$jenisbyy][$picloc][$tgl]>0){
									$tab.="<td id=realisasi".$jenisbyy."_".$tgl." align=center style=background-color:#FBEDFE;cursor:pointer; title='Realisasi tanpa biaya'>&#10004;</td>";
								}else{
									$tab.="<td id=realisasi".$jenisbyy."_".$tgl."></td>";
								}
							}
							$ttlbyytgl[$tgl]+=$jumlahum[$jenisbyy][$picloc][$tgl];
						}
						$tab.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$picloc])."</td>";
						$tab.="<td>".$keterangan[$jenisbyy][$picloc]."</td>";
						$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$picloc."'");
						$tab.="<td>".ucwords(strtolower($nmkar[$picloc]))."</td>";
						$tab.="<td colspan=2 align=center><img src=images/uploader/dwnld8.png class=zImgBtn title=Upload onclick=\"popupfile('".$param['notransaksi']."','".$jenisbyy."','real')\"></td>";
						$tab.="</tr>";
					}
				}
				
				$tab.="<tr class=rowcontent style=font-size:11px;>";
				$tab.="<td colspan=".($conttl+5)." style=font-size:11px;font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td colspan=2></td>";
				$tab.="</tr>";
				
				#TUTUP REALISASI
				
				
				# UANG MUKA
				$optkary=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$notransaksi."'");
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='0' and createby='".$optkary[$notransaksi]."' order by jenisbiaya asc";
				$res=fetchdata($str);
				$jumlahum=$ttlbyy=$umdriver=array();
				$datajenisbiaya=$umdriver=$tujuandriver=$t4kunj=$keterangan=$piclokasi=$check=array();
				foreach($res as $bar){
					$datajenisbiaya[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['piclokasi'];
					$umdriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['umdriver'];
					$tujuandriver[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tujuandriver'];
					$t4kunj[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['tempatkunjungan'];
					$keterangan[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['keterangan'];
					$piclokasi[$bar['jenisbiaya']][$bar['piclokasi']]=$bar['updateby'];
					$jumlahum[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]+=$bar['jumlah'];
					$check[$bar['jenisbiaya']][$bar['piclokasi']][$bar['tanggal']]=$bar['check'];
					$ttlbyy[$bar['jenisbiaya']][$bar['piclokasi']]+=$bar['jumlah'];
				}
				
				$no=0;
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=".($con+4)." style=font-weight:bold;background-color:#A7A6FF;>Uang Muka diminta :</td>";
				$tab.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valpic){
					foreach($valpic as $picloc => $valpiclock){
						$no+=1;
						$tab.="<tr class=rowcontent style=font-size:12px>";
						$tab.="<td align=center rowspan=2>".$no."</td>";
						$tab.="<td align=left rowspan=2>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
						if($dr>0){									
							$tab.="<td align=left rowspan=2>".$optjnsdriver[$umdriver[$jenisbyy][$picloc]]."</td>";
							$tab.="<td align=left rowspan=2>".$optjnsdriver[$tujuandriver[$jenisbyy][$picloc]]."</td>";
						}
						$tab.="<td align=left rowspan=2>".$t4kunj[$jenisbyy][$picloc]."</td>";
						$tab.="<td align=left colspan=2></td>";
						foreach($rangetgl as $tgl){
							if($check[$jenisbyy][$picloc][$tgl]>0){
								$tab.="<td align=center style=background-color:#E7FCE4;cursor:pointer; title='Rencana'>&#10004;</td>";
							}else{
								$tab.="<td></td>";
							}
							
							if($jenis!='pdf'){
								#$tab.="<td align=right>".numb_format($jumlahum[$jenisbyy][$picloc][$tgl])."</td>";
							}
							$ttlbyytgl[$tgl]+=$jumlahum[$jenisbyy][$picloc][$tgl];
						}
						$tab.="<td align=right rowspan=2>".numb_format($ttlbyy[$jenisbyy][$picloc])."</td>";
						$tab.="<td rowspan=2>".$keterangan[$jenisbyy][$picloc]."</td>";
						$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$picloc."'");
						$tab.="<td rowspan=2>".ucwords(strtolower($nmkar[$picloc]))."</td>";
						$tab.="<td rowspan=2 colspan=2></td>";
						$tab.="</tr>";
						
						#RUPIAH
						$tab.="<tr class=rowcontent style=font-size:11px;>";
						$tab.="<td align=left colspan=2></td>";
						foreach($rangetgl as $tgl){
							$tab.="<td align=right>".numb_format($jumlahum[$jenisbyy][$picloc][$tgl])."</td>";
						}
						$tab.="</tr>";
						
					}
				}
				
				$tab.="<tr class=rowcontent style=font-size:11px;>";
				$tab.="<td colspan=".($conttl+5)." style=font-size:11px;font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab.="<td></td>";
				$tab.="<td></td>";
				$tab.="<td colspan=2></td>";
				$tab.="</tr>";
				
				#TUTUP UANG MUKA DIMINTA
			
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."'";
				$res=fetchdata($str);
				$verhrd=$klaimkary=$sdhrealpt=$umminta=0;
				foreach($res as $bar){
					if($bar['sumber']=='0'){
						$umminta+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
						$sdhrealpt+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
						$klaimkary+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
						$verhrd+=$bar['jumlahhrd'];
					}
				}
				
				
				$optnikkar=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
				#cari noreff uang muka
				$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='umpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
				$resa = fetchdata($stra);
				$umdibayarkan=0;$umnoreff="";
				foreach($resa as $bara){				
					$umdibayarkan+=$bara['jumlah'];
					$umnoreff=$bara['notransaksi'];
				}
				
				#cari noreff ptj
				$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='claimpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
				$resa = fetchdata($stra);
				$claimbayar=0;$claimnoreff="";
				foreach($resa as $bara){				
					$claimbayar+=$bara['jumlah'];
					$claimnoreff=$bara['notransaksi'];
				}
				
				
				$tab.="</tbody></table>";
				
				$tab.="<table>";
				$tab.="<tr>";
				$tab.="<td>Total uang muka diminta</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($umminta)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total uang muka diterima / dibayarkan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($umdibayarkan)."</td>";
				$tab.="</tr>";
				
				$tab.="<tr>";
				$tab.="<td>Total biaya yang sudah direalisasikan oleh perusahaan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($sdhrealpt)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total biaya yang diajukan reimburse / klaim oleh karyawan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($klaimkary)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total biaya yang telah di verifikasi (disetujui)</td>";
				$tab.="<td>:</td>";
				$tab.="<td style=font-weight:bold; align=right>".numb_format($verhrd)."</td>";
				$tab.="</tr>";
				$tab.="</table>";
				
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$tab.="<hr><label><span>Info:</span>
					<li>Jika jenis biaya tidak ada di list data silahkan tambah melalui form input diatas.</li>
					<li>Jika biaya telah di realisasikan maka lakukan checked pada tanggal realisasi (contoh biaya tiket dll).</li>
					<li>Untuk kolom biaya <b>wajib diisi jika biaya dibayarkan dari Kas</b> dan <b>kosongkan</b> jika direalisasikan tapi tidak ada biaya.</li>
					<li>Contoh penginapan di lakukan di mess maka checked pada tanggal menginap di mess dan kosongkan biayanya.</li>
					<li>Pada saat pembayaran di menu kas dan bank maka nilai akan otomatis terisi.</li>
					<li>Pada tanggal yang sudah di checked maka karyawan tidak bisa melakukan reimburse / klaim pada tanggal tersebut.</li>
					<!--<li>Pada tanggal yang bertanda &#10004; adalah rencana yang di ajukan oleh karyawan namun untuk realisasi bisa saja berbeda.</li>-->
					<li>Jika kolom masih berwarna merah <img class=zImgBtn src=images/tab1Red.png> berarti ada perubahan data pada baris tersebut belum tersimpan.</li>
					</label>";
				}
				if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
					$tab.="<hr><label><span>Info:</span>
					<li>Jika jenis biaya tidak ada di list data silahkan tambah melalui form input diatas.</li>
					<li>Jika sudah ter-checked berarti biaya tersebut sudah pernah terealisaikan.</li>
					<li>Jika kolom masih berwarna merah <img class=zImgBtn src=images/tab1Red.png> berarti ada perubahan data pada baris tersebut belum tersimpan.</li>
					</label>";
				}
				
				#break;
			} #tutup if tampilan
			#=========================================================================================================================================

			if($param['jenistampilan']=='tampilansimple'){
				// case'sdm_confirmpjdx':
				// case'sdm_pertanggungjawabanpjdstaffx':
				// case'sdm_pertanggungjawabanpjdnonstaffx':
					$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' order by jenisbiaya asc";
					$res=fetchdata($str);
					foreach($res as $bar){
						$data[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]=$bar['tanggal'];
						if($bar['sumber']==0){					
							$cek[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]+=$bar['check'];
							$pictgl[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]=$bar['piclokasi'];
							$pic[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']]=$bar['piclokasi'];
							$ket[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']]=$bar['keterangan'];
						}
					}

					$no=0;
					if(count($res)>0){			
						foreach($data as $jenis => $valtkunj){
							foreach($valtkunj as $tempatkunj => $valumdriver){
								foreach($valumdriver as $umdriver => $valtujdriver){
									foreach($valtujdriver as $tujdriver => $tanggal){

										$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$pic[$jenis][$tempatkunj][$umdriver][$tujdriver]."'");
										
										$no+=1;
										$tab.="<input hidden id=jenisbiayareal".$no." value='".$jenis."'>";
										$tab.="<input hidden id=umdriverreal".$no." value='".$umdriver."'>";
										$tab.="<input hidden id=tujdriverreal".$no." value='".$tujdriver."'>";
										
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=center rowspan=2>".$no."</td>";
										$tab.="<td align=left rowspan=2>".$optjns[$jenis]."</td>";
										$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$umdriver]."</td>";
										$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$tujdriver]."</td>";
										$tab.="<td align=left rowspan=2 id=tempatkunjungan".$no.">".$tempatkunj."</td>";
										$tab.="<td align=left colspan=2 style=font-style:italic;font-size:10px;>Renc</td>";
										#$tab.="<td align=center></td>";
										$ttlbyy=array();
										$r=0;$ttl=0;
										foreach($rangetgl as $tgl){
											$r+=1;
											$e=$color="";
											
											if($cek[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]>0 and $pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]==$_SESSION['standard']['userid']){
												$e="&#10004;"; $color="style=background-color:#E7FCE4;cursor:pointer; title='Rencana'";
												$ttl+=1;
											}elseif($cek[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]>0 and $pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]!=$_SESSION['standard']['userid']){
												$e="&#10004;"; $color="style=background-color:#CDCDCD;cursor:pointer; title=\"Rencana, Kontak Person : ".$nmkar[$pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]]."\"";
												$ttl+=1;
											}else{
												#$e="&#10006;"; 
												$color="style=background-color:#FEDFDF; title='Tidak ada Rencana'";
											}
											
											$tab.="<td name=rencbyy ".$color." id=statusrenc".$no."_".$r."  align=center>".$e."</td>";
										}
										$tab.="<td align=right>".$ttl."</td>";
										$tab.="<td align=left >".$ket[$jenis][$tempatkunj][$umdriver][$tujdriver]."</td>";
										$tab.="<td align=left style=font-size:10px;font-style:italic;>".$nmkar[$pic[$jenis][$tempatkunj][$umdriver][$tujdriver]]."</td>";
										
										$tab.="<td align=center width=20px></td>";
										$tab.="<td align=center width=20px></td>";
										$tab.="</tr>";
										
										#baris realisasi
										$tab.="<tr class=rowcontent>";
										#if($_SESSION['pjd']['menu']!='sdm_verifikasiptjpjdx'){									
											$tab.="<td align=left valign=top style=background-color:green;font-style:italic;font-size:10px;color:white;>Real</td>";
											$tab.="<td align=center style=background-color:green; valign=top><input title=\"Checklist seluruhnya\" style=cursor:pointer; type='checkbox' name=statreal onclick=checkallreal('".$no."'); id=ceckboxrealall".$no."></td>";
										/* }else{
											$tab.="<td align=left colspan=2 valign=top style=background-color:green;font-style:italic;font-size:10px;color:white;>Real</td>";

										} */
										$r=0;
										$tnilai=0;
										$tnilaiklaim=0;$isittluserlain="";
										$ketreal=""; $ttluserlain=0;
										foreach($rangetgl as $tgl){
											$sql="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$jenis."' and tempatkunjungan='".$tempatkunj."' and umdriver='".$umdriver."' and tujuandriver='".$tujdriver."' and sumber='1' and tanggal='".$tgl."'";
											$rsql=fetchdata($sql);
											$check=$nilai=$nilairp=0;
											foreach($rsql as $bsql){
												$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bsql['piclokasi']."' or karyawanid='".$bsql['createby']."' or karyawanid='".$_SESSION['standard']['userid']."'");
												$check=$bsql['check'];
												$picrlain=$bsql['piclokasi'];
												$nilai=$bsql['jumlah'];
												$tnilai+=$bsql['jumlah'];
												if($bsql['statusverifikasihrd']=='1'){
													$nilairp=$bsql['jumlahhrd'];											
													#$tnilai+=$bsql['jumlahhrd'];
													if($bsql['tanggungan']=='1'){
														$tnilaiklaim+=$bsql['jumlahhrd'];
													}
												}else{
													if($bsql['tanggungan']=='1'){
														$tnilaiklaim+=$bsql['jumlah'];
													}
													$nilairp=$bsql['jumlah'];											
												}
												#jika nonstaff siappun bisa merubah.
												if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
													$piclokasi=$_SESSION['standard']['userid'];
												}else{												
													$piclokasi=$bsql['piclokasi'];
												}
												
												
												$tanggung=$bsql['tanggungan'];
												if($bsql['piclokasi']==$_SESSION['standard']['userid']){
													$ketreal=$bsql['keterangan'];
												}
												$createby=$bsql['createby'];
											}
											$isiuserlain="";
											$cb=$titre="";
											if($check>0){										
												$cb="checked disabled";
												$titre=" title=\"(1)Realisasi by : ".$nmkar[$createby]."\n".$ketreal."\" style=background-color:#CECDCB;cursor:pointer;";
											}
											$isi=0;
											$st="style=display:none;max-width:50px;cursor:pointer;";
											if($check>0 and $piclokasi==$_SESSION['standard']['userid']){
												$cb="checked";
												$titre=" title=\"(2)Realisasi by : ".$nmkar[$piclokasi]."\" style=background-color:#B2DCFE;cursor:pointer;";
												$st="style=max-width:50px;cursor:pointer;";
												$isi=0;
											}elseif($check>0 and $piclokasi!=$_SESSION['standard']['userid']){
												$cb="checked disabled";
												
												$titre=" title=\"(3)Realisasi by : ".$nmkar[$picrlain]."\n".$ketreal."\" style=background-color:#CECDCB;cursor:pointer;";
												$isiuserlain=numb_format($nilairp)."<br>";
												$ttluserlain+=$nilairp;
												$isittluserlain=numb_format($ttluserlain)."<br>";
												$nilairp=0;
												$st="style=max-width:50px;cursor:pointer;display:none; disabled ";
												$isi=1;
											}
											
											$r+=1;
											$tab.="<td align=center ".$titre." valign=top>
												<input hidden name=tglreal".$no." id=tglreal".$no."_".$r." value=".$tgl.">
												<input hidden name=userlain".$no." id=userlain".$no."_".$r." value=".$isi.">
												
												<input type='checkbox' ".$cb." ".$titre." name=statreal onclick=getjlhreal('".$no."','".$r."'); id=statusreal".$no."_".$r."><br>";
											
											$tab.=$isiuserlain;	
											/* if($_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){										
												$tab.="<font style=font-size:10px;>".numb_format($nilai)."</font><br>";
											}	 */
											$tab.="<input hidden id=plafonreal".$no."_".$r.">";
											$tab.="<input ".$st." value=\"".$nilairp."\" ".$titre." name=jlhbyyreal placeholder=Rp. onkeyup=ttlrealbyy('".$no."','".$r."'); class=myinputtextnumber id=jumlahreal".$no."_".$r." onkeypress='return angka_doang(event)'>
											</td>";
										}
										
										if(($tnilai-$ttluserlain)<0){
											$totalnilai=0;
										}else{
											$totalnilai=($tnilai-$ttluserlain);
										}
										
										$tab.="<td valign=bottom align=right style='width:50px;'>".$isittluserlain."<input value=\"".($totalnilai)."\" id=totalrealbyy".$no." disabled class=myinputtextnumber onkeypress='return angka_doang(event)' style='width:60px;'></td>
										
										<td valign=bottom style='width:150px;'><input id=ketrealbyy".$no." class=myinputtext type=text style='width:150px;' value=\"".$ketreal."\"></td>
										<input hidden id=picreal".$no." value=".$_SESSION['standard']['userid'].">
										<td valign=bottom style=font-size:10px;font-weight:bold;>".getNamaKaryawan($_SESSION['standard']['userid'])."</td>
										<td valign=bottom colspan=2 align=center id=kolomsavereal".$no."><img title='Simpan' class='zImgBtn' onclick=simpanest('".$r."','real','".$no."'); src='images/save.png'></td>";
										
										$tab.="<input hidden id=methodbyy value='insertestbyy'>";
										$tab.="</tr>";
									}						
								}
							}
						}
					}
					$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."'";
					$res=fetchdata($str);
					$verhrd=$klaimkary=$sdhrealpt=$umminta=0;
					foreach($res as $bar){
						if($bar['sumber']=='0'){
							$umminta+=$bar['jumlah'];
						}
						if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
							$sdhrealpt+=$bar['jumlah'];
						}
						if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
							$klaimkary+=$bar['jumlah'];
						}
						if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
							$verhrd+=$bar['jumlahhrd'];
						}
					}
					
					$optnikkar=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
					#cari noreff uang muka
					$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='umpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
					$resa = fetchdata($stra);
					$umdibayarkan=0;$umnoreff="";
					foreach($resa as $bara){				
						$umdibayarkan+=$bara['jumlah'];
						$umnoreff=$bara['notransaksi'];
					}
					
					#cari noreff ptj
					$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='claimpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
					$resa = fetchdata($stra);
					$claimbayar=0;$claimnoreff="";
					foreach($resa as $bara){				
						$claimbayar+=$bara['jumlah'];
						$claimnoreff=$bara['notransaksi'];
					}
					
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right colspan=".(count($rangetgl)+$colhide+10).">
							<button onclick=displayList(); class=mybutton>".$_SESSION['lang']['selesai']."</button>
							<button onclick=loadinputdetail(); class=mybutton>".$_SESSION['lang']['cancel']."</button>
							</td>";
					$tab.="</tr>";
					$tab.="</tbody>";
					$tab.="<tfoot>
						<tr class=rowheader>
							<td></td>
							<td></td>
							<td></td>
							<td colspan=2>Upload File per hari : </td>";

					// group by tanggal
					$str2="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' group by tanggal asc";
					$res2=fetchdata($str2);
					$no=0;
					foreach($res2 as $bar2){
						$no++;
						$tab .= "<td valign=bottom align=center onclick=\"showuploadperhari('event','".$param['notransaksi']."', '".$no."')\"><img src=images/upload-2-xxl.png class=zImgBtn title=Upload></td>";	
						$tab .= "<input type='hidden' id='tanggalPdJUpload_".$no."' value='".$bar2['tanggal']."' />";		
					}

					$tab .= "<td colspan=5></td>";
					$tab .= "</tr>
					</tfoot></table>";
					
					$tab.="<table>";
					$tab.="<tr>";
					$tab.="<td>Total uang muka diminta</td>";
					$tab.="<td>:</td>";
					$tab.="<td align=right>".numb_format($umminta)."</td>";
					$tab.="</tr>";
					$tab.="<tr>";
					$tab.="<td>Total uang muka diterima / dibayarkan</td>";
					$tab.="<td>:</td>";
					$tab.="<td align=right>".numb_format($umdibayarkan)."</td>";
					$tab.="</tr>";
					
					$tab.="<tr>";
					$tab.="<td>Total biaya yang sudah direalisasikan oleh perusahaan</td>";
					$tab.="<td>:</td>";
					$tab.="<td align=right>".numb_format($sdhrealpt)."</td>";
					$tab.="</tr>";
					$tab.="<tr>";
					$tab.="<td>Total biaya yang diajukan reimburse / klaim oleh karyawan</td>";
					$tab.="<td>:</td>";
					$tab.="<td align=right>".numb_format($klaimkary)."</td>";
					$tab.="</tr>";
					$tab.="<tr>";
					$tab.="<td>Total biaya yang telah di verifikasi (disetujui)</td>";
					$tab.="<td>:</td>";
					$tab.="<td style=font-weight:bold; align=right>".numb_format($verhrd)."</td>";
					$tab.="</tr>";
					$tab.="</table>";
					
					if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
						$tab.="<hr><label><span>Info:</span>
						<li>Jika jenis biaya tidak ada di list data silahkan tambah melalui form input diatas.</li>
						<li>Jika biaya telah di realisasikan maka lakukan checked pada tanggal realisasi (contoh biaya tiket dll).</li>
						<li>Untuk kolom biaya <b>wajib diisi jika biaya dibayarkan dari Kas</b> dan <b>kosongkan</b> jika direalisasikan tapi tidak ada biaya.</li>
						<li>Contoh penginapan di lakukan di mess maka checked pada tanggal menginap di mess dan kosongkan biayanya.</li>
						<li>Pada saat pembayaran di menu kas dan bank maka nilai akan otomatis terisi.</li>
						<li>Pada tanggal yang sudah di checked maka karyawan tidak bisa melakukan reimburse / klaim pada tanggal tersebut.</li>
						<!--<li>Pada tanggal yang bertanda &#10004; adalah rencana yang di ajukan oleh karyawan namun untuk realisasi bisa saja berbeda.</li>-->
						<li>Jika kolom masih berwarna merah <img class=zImgBtn src=images/tab1Red.png> berarti ada perubahan data pada baris tersebut belum tersimpan.</li>
						</label>";
					}
					if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdstaffx'){
						$tab.="<hr><label><span>Info:</span>
						<li>Jika jenis biaya tidak ada di list data silahkan tambah melalui form input diatas.</li>
						<li>Jika sudah ter-checked berarti biaya tersebut sudah pernah terealisaikan.</li>
						<li>Jika kolom masih berwarna merah <img class=zImgBtn src=images/tab1Red.png> berarti ada perubahan data pada baris tersebut belum tersimpan.</li>
						</label>";
					}
			}#tutup if($param['jenistampilan']=='')		
			#======================================================================================================================================
			break;
			case'sdm_verifikasiptjpjdx':
				$tab.="<label><span></span>
				<li>Jika ada perubahan rubah angkanya dan simpan, jika tidak ada perubahan langsung tekan simpan.</li>
				<li>Lakukan perubahan perbaris kemudian simpan.</li>
				<li>Pastikan kolom tidak ada yang berwarna merah <img class=zImgBtn src=images/tab1Red.png> dan nilai hasil verifikasi telah sesuai.</li>
				</label>";
				
				$cekver=array();
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' order by jenisbiaya asc";
				$res=fetchdata($str);
				foreach($res as $bar){
					$data[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]=$bar['tanggal'];
					
					//$cekver[$bar['jenisbiaya']]+=$bar['statusverifikasihrd'];
					$cekver[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']]+=$bar['statusverifikasihrd'];
					if($bar['sumber']==0){					
						$cek[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]+=$bar['check'];
						$pictgl[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['tanggal']]=$bar['piclokasi'];
						$pic[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']]=$bar['piclokasi'];
						$ket[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']]=$bar['keterangan'];
					}
				}
				
				
				$no=0;
				if(count($res)>0){			
					foreach($data as $jenis => $valtkunj){
						foreach($valtkunj as $tempatkunj => $valumdriver){
							foreach($valumdriver as $umdriver => $valtujdriver){
								foreach($valtujdriver as $tujdriver => $tanggal){
									$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$pic[$jenis][$tempatkunj][$umdriver][$tujdriver]."'");
									
									$no+=1;
									$tab.="<input hidden id=jenisbiayareal".$no." value='".$jenis."'>";
									$tab.="<input hidden id=umdriverreal".$no." value='".$umdriver."'>";
									$tab.="<input hidden id=tujdriverreal".$no." value='".$tujdriver."'>";
									
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center rowspan=2>".$no."</td>";
									$tab.="<td align=left rowspan=2>".$optjns[$jenis]."</td>";
									$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$umdriver]."</td>";
									$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$tujdriver]."</td>";
									$tab.="<td align=left rowspan=2 id=tempatkunjungan".$no.">".$tempatkunj."</td>";
									$tab.="<td align=left colspan=2 style=font-style:italic;font-size:10px;>Renc</td>";
									#$tab.="<td align=center></td>";
									$ttlbyy=array();
									$r=0;$ttl=0;
									foreach($rangetgl as $tgl){
										$r+=1;
										$e=$color="";
										
										if($cek[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]>0 and $pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]==$_SESSION['standard']['userid']){
											$e="&#10004;"; $color="style=background-color:#E7FCE4;cursor:pointer; title='Rencana'";
											$ttl+=1;
										}elseif($cek[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]>0 and $pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]!=$_SESSION['standard']['userid']){
											$e="&#10004;"; $color="style=background-color:#CDCDCD;cursor:pointer; title=\"Rencana, Kontak Person : ".$nmkar[$pictgl[$jenis][$tempatkunj][$umdriver][$tujdriver][$tgl]]."\"";
											$ttl+=1;
										}else{
											#$e="&#10006;"; 
											$color="style=background-color:#FEDFDF; title='Tidak ada Rencana'";
										}
										
										$tab.="<td name=rencbyy ".$color." id=statusrenc".$no."_".$r."  align=center>".$e."</td>";
									}
									$tab.="<td align=right>".$ttl."</td>";
									$tab.="<td align=left >".$ket[$jenis][$tempatkunj][$umdriver][$tujdriver]."</td>";
									$tab.="<td align=left style=font-size:10px;font-style:italic;>".$nmkar[$pic[$jenis][$tempatkunj][$umdriver][$tujdriver]]."</td>";
									
									$tab.="<td align=center width=20px></td>";
									$tab.="<td align=center width=20px></td>";
									$tab.="</tr>";
									
									#baris realisasi
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=left colspan=2 valign=top style=background-color:green;font-style:italic;font-size:10px;color:white;>Real</td>";

									$r=0;
									$tnilai=0;
									$tnilaiklaim=0;
									$ketreal=""; $ttluserlain=0;
									foreach($rangetgl as $tgl){
										$sql="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$jenis."' and tempatkunjungan='".$tempatkunj."' and umdriver='".$umdriver."' and tujuandriver='".$tujdriver."' and sumber='1' and tanggal='".$tgl."'";
										$rsql=fetchdata($sql);
										$createby="";
										$check=$nilai=$nilairp=0;
										foreach($rsql as $bsql){
											$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bsql['piclokasi']."' or karyawanid='".$bsql['createby']."'");
											$check=$bsql['check'];
											$picrlain=$bsql['piclokasi'];
											$nilai=$bsql['jumlah'];
											$tnilai+=$bsql['jumlah'];
											if($bsql['statusverifikasihrd']=='1'){
												$nilairp=$bsql['jumlahhrd'];											
												#$tnilai+=$bsql['jumlahhrd'];
												if($bsql['tanggungan']=='1'){
													$tnilaiklaim+=$bsql['jumlahhrd'];
												}
											}else{
												if($bsql['tanggungan']=='1'){
													$tnilaiklaim+=$bsql['jumlah'];
												}
												$nilairp=$bsql['jumlah'];											
											}
											$piclokasi=$bsql['piclokasi'];
											$tanggung=$bsql['tanggungan'];
											if($bsql['createby']==$_SESSION['standard']['userid']){
												$ketreal=$bsql['keterangan'];
											}
											$createby=$bsql['createby'];
										}
										$isiuserlain="";
										$cb=$titre="";
										if($check>0){										
											$cb="checked disabled";
											$titre=" title=\"(1)Realisasi by : ".$nmkar[$createby]."\n".$ketreal."\" style=background-color:#CECDCB;cursor:pointer;";
										}
										$isi=0;
										$st="style=display:none;max-width:50px;cursor:pointer;";
										
										# jika verifikasi hanya yang di buat oleh user yg bisa dirubah
										$nmuserpjd = makeOption($dbname, 'sdm_pjdinasht', 'notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
										if(($check>0 and  $tanggung=='1') and ($nmuserpjd[$param['notransaksi']]==$piclokasi or $piclokasi==$_SESSION['standard']['userid'])){
											$cb="checked";
											$titre=" title=\"By : ".$nmkar[$piclokasi]."\" style=background-color:#F3FECD;";
											$st="style=max-width:50px;";
											$isi=0;
											$nilairp=$nilairp;
										}else{
											$nilairp=0;
										}
										
										$r+=1;
										$tab.="<td align=center ".$titre." valign=top>
											<input hidden name=tglreal".$no." id=tglreal".$no."_".$r." value=".$tgl.">
											<input hidden name=userlain".$no." id=userlain".$no."_".$r." value=".$isi.">
											
											<input type='checkbox' ".$cb." ".$titre." name=statreal onclick=getjlhreal('".$no."','".$r."'); id=statusreal".$no."_".$r."><br>";
										
										$tab.=$isiuserlain;	
										$tab.="<font style=font-size:10px;>".numb_format($nilai)."</font><br>";
										
										$tab.="<input hidden id=plafonreal".$no."_".$r.">";
										$tab.="<input ".$st." value=\"".$nilairp."\" ".$titre." name=jlhbyyreal placeholder=Rp. onkeyup=ttlrealbyy('".$no."','".$r."'); class=myinputtextnumber id=jumlahreal".$no."_".$r." onkeypress='return angka_doang(event)'>
										</td>";
									}
									
									$br="";
									$br="<font style=font-size:10px;>".numb_format($tnilai)."</font><br>";
									$tnilai=$tnilaiklaim;
									$warna="style=background-color:red";
									if($cekver[$jenis][$tempatkunj][$umdriver][$tujdriver]>0){
										$warna="";
									}
									
									if(($tnilai-$ttluserlain)<0){
										$totalnilai=0;
									}else{
										$totalnilai=($tnilai-$ttluserlain);
									}
									
									$tab.="<td valign=bottom align=right>".$br."".$isittluserlain."<input value=\"".($totalnilai)."\" id=totalrealbyy".$no." disabled class=myinputtextnumber onkeypress='return angka_doang(event)' style='width:60px;'></td>
									
									<td valign=bottom>".$ketreal."<input id=ketrealbyy".$no." class=myinputtext type=text style='width:150px;display:none' value=\"".$ketreal."\"></td>
									<input hidden id=picreal".$no." value=".$_SESSION['standard']['userid'].">
									<td valign=bottom style=font-size:10px;font-weight:bold;>".getNamaKaryawan($_SESSION['standard']['userid'])."</td>
									<td valign=bottom colspan=2 align=center ".$warna." id=kolomsavereal".$no."><img title='Simpan' class='zImgBtn' onclick=simpanest('".$r."','real','".$no."'); src='images/save.png'>";
									
									$tab.="<input hidden id=methodbyy value='insertestbyy'>";
									$tab.="</tr>";
								}
							}
						}
					}
					$tab.="
						<tr class=rowheader>
							<td></td>
							<td></td>
							<td></td>
							<td colspan=2>Upload File per hari : </td>";

						// group by tanggal
						$str2="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' group by tanggal asc";
						$res2=fetchdata($str2);
						$no=0;
						foreach($res2 as $bar2){
							$no++;
							$tab .= "<td valign=bottom align=center onclick=\"showuploadperhari('event','".$param['notransaksi']."', '".$no."')\"><img src=images/upload-2-xxl.png class=zImgBtn title=Upload></td>";	
							$tab .= "<input type='hidden' id='tanggalPdJUpload_".$no."' value='".$bar2['tanggal']."' />";		
						}
					$tab .= "</tr>";
				}
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."'";
				$res=fetchdata($str);
				$verhrd=$klaimkary=$sdhrealpt=$umminta=0;
				foreach($res as $bar){
					if($bar['sumber']=='0'){
						$umminta+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
						$sdhrealpt+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
						$klaimkary+=$bar['jumlah'];
					}
					if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
						$verhrd+=$bar['jumlahhrd'];
					}
				}
				
				
				$optnikkar=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
				#cari noreff uang muka
				$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='umpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
				$resa = fetchdata($stra);
				$umdibayarkan=0;$umnoreff="";
				foreach($resa as $bara){				
					$umdibayarkan+=$bara['jumlah'];
					$umnoreff=$bara['notransaksi'];
				}
				
				#cari noreff ptj
				$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$param['notransaksi']."' and keterangan2='claimpjd#".$param['notransaksi']."' and nik='".$optnikkar[$param['notransaksi']]."'";
				$resa = fetchdata($stra);
				$claimbayar=0;$claimnoreff="";
				foreach($resa as $bara){				
					$claimbayar+=$bara['jumlah'];
					$claimnoreff=$bara['notransaksi'];
				}
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=right colspan=".(count($rangetgl)+$colhide+10).">
						<button onclick=displayList(); class=mybutton>".$_SESSION['lang']['selesai']."</button>
						<button onclick=loadinputdetail(); class=mybutton>".$_SESSION['lang']['cancel']."</button>
						</td>";
				$tab.="</tr>";
				$tab.="</tbody></table>";
				$tab.="<table>";
				$tab.="<tr>";
				$tab.="<td>Total uang muka diminta</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($umminta)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total uang muka diterima / dibayarkan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($umdibayarkan)."</td>";
				$tab.="</tr>";
				
				$tab.="<tr>";
				$tab.="<td>Total biaya yang sudah direalisasikan oleh perusahaan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($sdhrealpt)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total biaya yang diajukan reimburse / klaim oleh karyawan</td>";
				$tab.="<td>:</td>";
				$tab.="<td align=right>".numb_format($klaimkary)."</td>";
				$tab.="</tr>";
				$tab.="<tr>";
				$tab.="<td>Total biaya yang telah di verifikasi (disetujui)</td>";
				$tab.="<td>:</td>";
				$tab.="<td style=font-weight:bold; align=right>".numb_format($verhrd)."</td>";
				$tab.="</tr>";
				$tab.="</table>";
				
				$tab.="<hr><label><span>Info:</span>
				<li>Yang bisa di verifikasi hanya yang di ajukan oleh karyawan yang melakukan perjalan dinas.</li>
				<li>Jika sudah ter-checked berarti biaya tersebut sudah pernah terealisaikan.</li>
				<li>Semua kolom yang tidak didisable bisa anda sesuaikan, jika kurang bisa ditambahkan jika lebih bisa dikurangi.</li>
				<li>Jika kolom masih berwarna merah <img class=zImgBtn src=images/tab1Red.png> berarti ada perubahan data pada baris tersebut belum tersimpan.</li>
				</label>";
			
			break;
			default:
				$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' order by tanggal asc";
				$res=fetchdata($str);
				$data=$ket=$jlh=$check=array();
				foreach($res as $bar){
					$data[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['piclokasi']][$bar['tanggal']]=$bar['tanggal'];
					$jlh[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['piclokasi']][$bar['tanggal']]+=$bar['jumlah'];
					$check[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['piclokasi']][$bar['tanggal']]=$bar['check'];
					$ket[$bar['jenisbiaya']][$bar['tempatkunjungan']][$bar['umdriver']][$bar['tujuandriver']][$bar['piclokasi']]=$bar['keterangan'];
				}
				$no=0;
				if(count($res)>0){
					$gtbyy=array();$gtttlbyy=0;
					foreach($data as $jenis => $valtkunj){
						foreach($valtkunj as $tempatkunj => $valumdriver){
							foreach($valumdriver as $umdriver => $valtujdriver){
								foreach($valtujdriver as $tujdriver => $valpiclok){
									foreach($valpiclok as $pic => $tanggal){
										$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$pic."'");
										
										$no+=1;
										#row checklist
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=center rowspan=2>".$no."</td>";
										$tab.="<td align=left rowspan=2>".$optjns[$jenis]."</td>";
										$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$umdriver]."</td>";
										$tab.="<td align=left rowspan=2 ".$i.">".$optjnsdriver[$tujdriver]."</td>";
										$tab.="<td align=left rowspan=2>".$tempatkunj."</td>";
										$tab.="<td align=left colspan=2 rowspan=2 style=font-style:italic;font-size:10px;>Renc</td>";
										$ttlbyy=array();
										foreach($rangetgl as $tgl){
											$e="";
											if($check[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic][$tgl]>0){
												$e="&#10004;"; $color="style=background-color:#E7FCE4;cursor:pointer; title='Rencana'";
											}
											$tab.="<td align=center>".$e."</td>";											
											
											$ttlbyy[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic]+=$jlh[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic][$tgl];
											$gtbyy[$tgl]+=$jlh[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic][$tgl];
										}
										$tab.="<td align=right rowspan=2>".numb_format($ttlbyy[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic],0)."</td>";
										$tab.="<td align=left rowspan=2>".$ket[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic]."</td>";
										$tab.="<td align=left rowspan=2>".$nmkar[$pic]."</td>";
										
										$tab.="<td align=center rowspan=2 colspan=2 width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyy('".$param['notransaksi']."','".$jenis."','0','".$umdriver."','".$tujdriver."','".$pic."');\" title='Delete'></td>";
										$tab.="</tr>";
										
										#row biaya
										$tab.="<tr class=rowcontent>";
										foreach($rangetgl as $tgl){				
											$tab.="<td align=right>".numb_format($jlh[$jenis][$tempatkunj][$umdriver][$tujdriver][$pic][$tgl],0)."</td>";
										}
										$tab.="</tr>";
									
									}
								}						
							}
						}
					}
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=".($colhide+5).">T O T A L</td>";
					foreach($rangetgl as $tgl){	
						$tab.="<td align=right>".numb_format($gtbyy[$tgl],0)."</td>";
						$gtttlbyy+=$gtbyy[$tgl];
					}
					$tab.="<td align=right>".numb_format($gtttlbyy,0)."</td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td colspan=2></td>";
					$tab.="</tr>";					
				}
				
				
				$tab.="</tbody></table>";
				
			break;
		}

		echo $tab;
	break;
	case'getjlhreal':
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='0' and tanggal='".$param['tanggal']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' order by tanggal asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlah=$bar['jumlah'];
		}
		echo $jumlah;
	break;
	case'insertagenda':
		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		
		$param['tgl'] = tanggalsystemn($param['tgl']);
		$param['tgl2'] = tanggalsystemn($param['tgl2']);

		if($param['tgl2'] < $param['tgl'] ){
			exit("Warning: Range tanggal salah ! ");
		}

		$data = array();
		if($param['keterangan']!=''){
			$str = "delete from " . $dbname . ".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."' and jenis='".$param['jenisagenda']."' and tanggal='".$param['tgl']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$data = array(
				'notransaksi'     => $notransaksi,
				'jenis'           => $param['jenisagenda'],
				'tanggal'         => $param['tgl'],
				'tanggal2'         => $param['tgl2'],
				'keterangan'      => $param['keterangan'],
				'lokasi'          => $param['lokasi'],
				'koordinasidengan'=> $param['koordinasidengan']
			);
			
			$cols = array();
			foreach($data as $keyn=>$rown) {
					$cols[] = $keyn;
			}
			$str = insertQuery($dbname,'sdm_pjdinasdt2',$data,$cols); #exit("error".$str);
			$owlPDO->exec($str);
		}
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'delagenda':
		$str = "delete from " . $dbname . ".sdm_pjdinasdt2 where notransaksi='".$param['notransaksi']."' and jenis='".$param['jenis']."' and tanggal='".$param['tanggal']."'and tanggal2='".$param['tanggal2']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'delbyy':
		$str = "delete from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumber']."' and umdriver='".$param['umdriver']."' and tujuandriver='".$param['tujdriver']."' and piclokasi='".$param['piclokasi']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'delete':
		$str = "delete from " . $dbname . ".sdm_pjdinasht where notransaksi='".$param['notransaksi']."'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		# delete file
		$sql = "select * from " . $dbname . ".file_pjdinas where notransaksi='".$notransaksi."'";
		$res = fetchdata($sql);
		if(!empty($res)){			
			foreach ($res as $bar){
				$str="delete from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."'and namafile='".$bar['namafile']."'";
				try{$owlPDO->exec($str);
					$pathx = $path.$bar['namafile'];
					unlink($pathx);
				}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
			}
		}
		
		
	break;
	case'insertestbyy':
		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		
		$optkary = makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
		$tipekary = makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$optkary[$param['notransaksi']]."'");
		
		if($tipekary[$optkary[$param['notransaksi']]]=='4'){
			throw new PDOException("Tipe karyawan BHL tidak diizinkan.");
		}
		
		if($_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
			$param['tanggungan']=1; #klaim oleh karyawan
		}else{
			$param['tanggungan']=0;
		}
		
		if($_SESSION['pjd']['menu']=='sdm_confirmpjdx' or $_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
			$param['sumbertrans']='1'; #realisasi
			
			if($_SESSION['pjd']['menu']=='sdm_pertanggungjawabanpjdnonstaffx'){
				$optnamakary = makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$param['notransaksi']."'");
				$param['pic'] = $optnamakary[$param['notransaksi']];
			}
			
			$str = "delete from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and piclokasi='".$_SESSION['standard']['userid']."' and createby='".$_SESSION['standard']['userid']."'";
			$owlPDO->exec($str);
			
			$str = "select * from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."'";
			$res = fetchdata($str);
			if(empty($res)){
				$data = array();
				if($param['check']=='1'){	
					$data = array(
						'notransaksi'      => $notransaksi,
						'jenisbiaya'       => $param['jenisbiaya'],
						'tempatkunjungan'  => $param['tempatkunjungan'],
						'umdriver'         => $param['jenisbiayadriver'],
						'tujuandriver'     => $param['tujubiayadriver'],
						'keterangan'       => $param['ketestbyy'],
						'piclokasi'        => $param['pic'],
						'sumber'           => $param['sumbertrans'],
						'tanggungan'       => $param['tanggungan'],
						'tanggal'          => $param['tgl'],
						'check'            => $param['check'],
						'jumlah'           => $param['jumlah'],
						'createby'         => $_SESSION['standard']['userid'],
						'createtime'       => date("Y-m-d H:i:s"),
						'updateby'         => $_SESSION['standard']['userid']
					);
				}
			}else{
				$str = "update ".$dbname.".sdm_pjdinasdt set `jumlah`='".$param['jumlah']."',`check`='".$param['check']."' where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and tanggungan='1'";
				$owlPDO->exec($str);
			}
			
		}elseif($_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){
			$param['sumbertrans']='1'; #realisasi
			
			$str = "select * from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."'"; #exit("error".$str);
			$res = fetchdata($str);
			if($res[0]['tanggungan']=='1'){
				$klaimkary='adaklaim';
				#update nilai hrd = nilai hrd
				$str = "update ".$dbname.".sdm_pjdinasdt set jumlahhrd='".$param['jumlah']."', statusverifikasihrd='1' where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and tanggungan='1'";
				#exit("error".$str);
				$owlPDO->exec($str);
				
			}elseif($res[0]['tanggungan']=='0'){
				$klaimkary='tidakklaim';
				#update nilaihrd kosong
				$str = "update ".$dbname.".sdm_pjdinasdt set jumlahhrd='0', statusverifikasihrd='1' where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and tanggungan='0'";
				$owlPDO->exec($str);
				
			}else{
				$klaimkary='blank';
				#insert dengan data kosong
				$datan = array();
				$datan = array(
					'notransaksi'        => $notransaksi,
					'jenisbiaya'         => $param['jenisbiaya'],
					'tempatkunjungan'    => $param['tempatkunjungan'],
					'umdriver'           => $param['jenisbiayadriver'],
					'tujuandriver'       => $param['tujubiayadriver'],
					'keterangan'         => $param['ketestbyy'],
					'piclokasi'          => $_SESSION['standard']['userid'],
					'sumber'             => $param['sumbertrans'],
					'tanggungan'         => '0',
					'tanggal'            => $param['tgl'],
					'check'              => $param['check'],
					'jumlahhrd'          => '0',
					'statusverifikasihrd'=> '1',
					'createby'           => $_SESSION['standard']['userid'],
					'createtime'         => date("Y-m-d H:i:s"),
					'updateby'           => $_SESSION['standard']['userid']
				);
				$cols = array();
				foreach($datan as $keyn=>$rown) {
						$cols[] = $keyn;
				}
				$str = insertQuery($dbname,'sdm_pjdinasdt',$datan,$cols); #exit("error".$str);
				#cek renc
				$strcek = "select * from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='0' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."'"; #exit("error".$str);
				if(count(fetchdata($strcek))>0){
					$owlPDO->exec($str);
				}
			}
			
			/* 
			exit("error  ".$klaimkary);
			if(empty($res)){
				$tanggung='1';
				$jumlah=$param['jumlah'];
				if($res[0]['tanggungan']=='1'){
					
				}
				
				
				$datan = array();
				$datan = array(
					'notransaksi'        => $notransaksi,
					'jenisbiaya'         => $param['jenisbiaya'],
					'tempatkunjungan'    => $param['tempatkunjungan'],
					'umdriver'           => $param['jenisbiayadriver'],
					'tujuandriver'       => $param['tujubiayadriver'],
					'keterangan'         => $param['ketestbyy'],
					'piclokasi'          => $_SESSION['standard']['userid'],
					'sumber'             => $param['sumbertrans'],
					'tanggungan'         => $tanggung,
					'tanggal'            => $param['tgl'],
					'check'              => $param['check'],
					'jumlahhrd'          => $jumlah,
					'statusverifikasihrd'=> '1',
					'createby'           => $_SESSION['standard']['userid'],
					'createtime'         => date("Y-m-d H:i:s"),
					'updateby'           => $_SESSION['standard']['userid']
				);
				$cols = array();
				foreach($datan as $keyn=>$rown) {
						$cols[] = $keyn;
				}
				$str = insertQuery($dbname,'sdm_pjdinasdt',$datan,$cols); exit("error".$str);
				if($param['check']==1){
					$owlPDO->exec($str);
				}
				
			}else{				
				$str = "update ".$dbname.".sdm_pjdinasdt set jumlahhrd='".$param['jumlah']."', statusverifikasihrd='1' where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and tanggungan='1'";
				$owlPDO->exec($str);
			}
			 */
		}else{
			
			$data = array();
			if($param['check']==1){	
				$str = "delete from " . $dbname . ".sdm_pjdinasdt where notransaksi='".$param['notransaksi']."' and jenisbiaya='".$param['jenisbiaya']."' and sumber='".$param['sumbertrans']."' and tanggal='".$param['tgl']."' and umdriver='".$param['jenisbiayadriver']."' and tujuandriver='".$param['tujubiayadriver']."' and piclokasi='".$param['pic']."'";
				#exit("error".$str);
				$owlPDO->exec($str);
				
				$data = array(
					'notransaksi'      => $notransaksi,
					'jenisbiaya'       => $param['jenisbiaya'],
					'tempatkunjungan'  => $param['tempatkunjungan'],
					'umdriver'         => $param['jenisbiayadriver'],
					'tujuandriver'     => $param['tujubiayadriver'],
					'keterangan'       => $param['ketestbyy'],
					'piclokasi'        => $param['pic'],
					'sumber'           => $param['sumbertrans'],
					'tanggungan'       => $param['tanggungan'],
					'tanggal'          => $param['tgl'],
					'check'            => $param['check'],
					'jumlah'           => $param['jumlah'],
					'createby'         => $_SESSION['standard']['userid'],
					'createtime'       => date("Y-m-d H:i:s"),
					'updateby'         => $_SESSION['standard']['userid']
				);
			}
		}
		
		if(!empty($data)){	
		
			$cols = array();
			foreach($data as $keyn=>$rown) {
					$cols[] = $keyn;
			}
			$str = insertQuery($dbname,'sdm_pjdinasdt',$data,$cols); #exit("error".$str);
			$owlPDO->exec($str);
		}

		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	case'getjlh':
		
		// $opttujdri=makeOption($dbname,'sdm_5setupdinasdriver','jenisbiaya,jenisbiaya',"jenisbiaya='".$param['jenisbiaya']."' and jenis='tujuan'");
		// $optjnsdri=makeOption($dbname,'sdm_5setupdinasdriver','jenisbiaya,jenisbiaya',"jenisbiaya='".$param['jenisbiaya']."' and jenis!='tujuan'");
		
		// $optlok=makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$param['karyawanid']."'");
		// $optreg=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$optlok[$param['karyawanid']]."'");
		// $tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$optlok[$param['karyawanid']]."'");
		
		#if($optreg[$optlok[$param['karyawanid']]]=='PONTIANAK' and $param['jenisbiaya']=='11'){
		// if($tipeorg[$optlok[$param['karyawanid']]]=='KANWIL' and $tipeorg[$optlok[$param['karyawanid']]]=='BULKING' and $param['jenisbiaya']=='11'){
		// 	exit("Warning: Jenis ini hanya untuk driver kebun.");
		// }

		// #if($optreg[$optlok[$param['karyawanid']]]=='SINTANG' and $param['jenisbiaya']=='10'){
		// if($tipeorg[$optlok[$param['karyawanid']]]!='KANWIL' and $tipeorg[$optlok[$param['karyawanid']]]!='BULKING' and $param['jenisbiaya']=='10'){
		// 	exit("Warning: Jenis ini hanya untuk driver RO dan BULKING.");
		// }
		
		// if(in_array($param['jabatan'], $arrjabatan) and ($param['jenisbiaya']=='7' or $param['jenisbiaya']=='8' or $param['jenisbiaya']=='9')){
		// 	exit("Warning: Driver tidak boleh menggunakan jenis ini.");
		// }
		// echo"<pre>";
		// print_r($tipeorg[$optlok[$param['karyawanid']]]);
		// echo"</pre>";
		// exit("error");
		
		// if($opttujdri[$param['jenisbiaya']]!='' and $param['tujubiayadriver'] ==''){
		// 	exit("Warning: Tujuan wajib diisi.");
		// }
		
		// if($optjnsdri[$param['jenisbiaya']]!='' and $param['jenisbiayadriver']==''){
		// 	exit("Warning: Jenis wajib diisi.");
		// }
		
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$opttipekar=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
		$optgolkar=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
		$optlevel=makeOption($dbname,'sdm_5levelkaryawan','kode,nama');
		$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$param['jabatan']."'");
		$optNamaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

		$wh="";
		$info = "<table>";
		$info .= "<tr><td>Unit</td><td>: ".$optNamaorg[$param['lokasitugas']]."</td></tr>";
		$info .= "<tr><td>Jenis Biaya</td><td>: ".$optjns[$param['jenisbiaya']]."</td></tr>";
		$info .= "<tr><td>Tipe Karyawan</td><td>: ".$opttipekar[$param['tipekary']]."</td></tr>";
		$info .= "<tr><td>Level Karyawan</td><td>: ".$optlevel[$param['levelkaryawan']]."</td></tr>";
		$info .= "<tr><td>Golongan Karyawan</td><td>: ".$optgolkar[$param['golongan']]."</td></tr>";
		$info .= "<tr><td>Regional Tujuan</td><td>: ".$param['regiontujuan']."</td></tr>";
		$info .= "<tr><td colspan='2'>tidak ada.</td></tr>";
		$info .= "</table>";

		$ada=0;
		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='' and tipekaryawan = '' and levelkaryawan = '".$param['levelkaryawan']."' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='".$param['golongan']."' and tipekaryawan = '' and levelkaryawan = ''  and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='' and tipekaryawan = '".$param['tipekary']."' and levelkaryawan = '' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='".$param['golongan']."' and tipekaryawan = '' and levelkaryawan = '".$param['levelkaryawan']."' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='".$param['golongan']."' and tipekaryawan = '".$param['tipekary']."' and levelkaryawan = '' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='' and tipekaryawan = '".$param['tipekary']."' and levelkaryawan = '".$param['levelkaryawan']."' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}

		$wh="";
		$wh.=" and unit = '".$param['lokasitugas']."' and jenis = '".$param['jenisbiaya']."' and golongan ='".$param['golongan']."' and tipekaryawan = '".$param['tipekary']."' and levelkaryawan = '".$param['levelkaryawan']."' and regiontujuan='".$param['regiontujuan']."'";
		$str="select * from ".$dbname.".sdm_5plafondinas where 1=1 ".$wh."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$ada=1;
			$jumlah=$bar['jumlah'];
		}
		
		echo $ada."##".$jumlah."##".$info."##".$_SESSION['pjd']['menu'];
	break;
	case'loaddatarute':
		$no=0;
		foreach($_SESSION['rute'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['dari']."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['tujuan']."</td>";
				$tab.="<td style='text-align:center' colspan=2>".waktunormal($row['tglrute'])."</td>";
				$tab.="<td style='text-align:left;width:120px'>".$row['transport']."</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='edit' class=zImgBtn onclick=\"editrute('".$key."','".$row['notransaksi']."','".$row['dari']."','".$row['tujuan']."','".tanggalnormal(substr($row['tglrute'],0,10))."','".substr($row['tglrute'],11,5)."','".$row['transport']."')\" src=images/application/application_edit.png>
				</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='Delete' class=zImgBtn onclick=\"deleterute('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		
		echo $tab;
	break;
	case'deleterute':
		unset($_SESSION['rute'][$param['no']]);
		$no=0;
		foreach($_SESSION['rute'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['dari']."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['tujuan']."</td>";
				$tab.="<td style='text-align:center' colspan=2>".waktunormal($row['tglrute'])."</td>";
				$tab.="<td style='text-align:left;width:120px'>".$row['transport']."</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='edit' class=zImgBtn onclick=\"editrute('".$key."','".$row['notransaksi']."','".$row['dari']."','".$row['tujuan']."','".tanggalnormal(substr($row['tglrute'],0,10))."','".substr($row['tglrute'],11,5)."','".$row['transport']."')\" src=images/application/application_edit.png>
				</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='Delete' class=zImgBtn onclick=\"deleterute('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		
		if($param['methodheader']=='updateheader'){			
			if($_SESSION['rute'] != array()){
				$str = "delete from " . $dbname . ".sdm_pjdinasdt_rute where notransaksi='".$param['notransaksi']."'";
				$owlPDO->exec($str);
				
				$data=array();
				$no=0;
				foreach($_SESSION['rute'] as $key=>$row){
					if($row['notransaksi'] == $param['notransaksi']){
						$no+=1;
						$data = array(
							'id'          => $no,
							'notransaksi' => $param['notransaksi'],
							'waktu'       => $row['tglrute'],
							'dari'        => $row['dari'],
							'tujuan'      => $row['tujuan'],
							'transportasi'=> $row['transport']
						);
						
						$cols = array();
						foreach($data as $keyn=>$rown) {
								$cols[] = $keyn;
						}
						$str = insertQuery($dbname,'sdm_pjdinasdt_rute',$data,$cols); #exit("error".$str);
						try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
					}
				}
			}
		}
		
		echo $tab;
	break;
	case'editrute':
		// if($param['notransaksi']==''){
		// 	exit("Warning : Nomor transaksi wajib terisi.");
		// }
		if($param['dari']==''){
			exit("Warning : Rute dari wajib terisi.");
		}
		if($param['tglrute']==''){
			exit("Warning : Tanggal wajib terisi.");
		}
		if($param['transport']==''){
			exit("Warning : Transport wajib terisi.");
		}
		
		unset($_SESSION['rute'][$param['keyrute']]);
		
		if($param['time']==''){
			$param['time']="00-00";
		}
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'dari'       =>$param['dari'],
			'tujuan'     =>$param['rutetujuan'],
			'tglrute'    =>tanggalsystemn($param['tglrute'])." ".$param['time'].":00",
			'transport'  =>$param['transport']
		);
		
		
		if($_SESSION['rute'] != array()){
			array_push($_SESSION['rute'],$newdata);
		}else{
			array_push($_SESSION['rute'],$newdata);
		}
	
		$no=0;
		foreach($_SESSION['rute'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['dari']."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['tujuan']."</td>";
				$tab.="<td style='text-align:center' colspan=2>".waktunormal($row['tglrute'])."</td>";
				$tab.="<td style='text-align:left;width:120px'>".$row['transport']."</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='edit' class=zImgBtn onclick=\"editrute('".$key."','".$row['notransaksi']."','".$row['dari']."','".$row['tujuan']."','".tanggalnormal(substr($row['tglrute'],0,10))."','".substr($row['tglrute'],11,5)."','".$row['transport']."')\" src=images/application/application_edit.png>
				</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='Delete' class=zImgBtn onclick=\"deleterute('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		
		if($param['methodheader']=='updateheader'){			
			if($_SESSION['rute'] != array()){
				$str = "delete from " . $dbname . ".sdm_pjdinasdt_rute where notransaksi='".$param['notransaksi']."'";
				$owlPDO->exec($str);
				
				$data=array();
				$no=0;
				foreach($_SESSION['rute'] as $key=>$row){
					if($row['notransaksi'] == $param['notransaksi']){
						$no+=1;
						$data = array(
							'id'          => $no,
							'notransaksi' => $param['notransaksi'],
							'waktu'       => $row['tglrute'],
							'dari'        => $row['dari'],
							'tujuan'      => $row['tujuan'],
							'transportasi'=> $row['transport']
						);
						
						$cols = array();
						foreach($data as $keyn=>$rown) {
								$cols[] = $keyn;
						}
						$str = insertQuery($dbname,'sdm_pjdinasdt_rute',$data,$cols); #exit("error".$str);
						try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
					}
				}
			}
		}
		
		// echo"<pre>";
		// //print_r($_SESSION['rute']);
		// print_r($param);
		// echo"</pre>";
		// exit("error");
		echo $tab;
	break;
	case'addrute':
		if($param['notransaksi']==''){
			//exit("Warning : Nomor transaksi wajib terisi.");
			$param['notransaksi']=$param['karyawanid'];
		}
		if($param['dari']==''){
			exit("Warning : Rute dari wajib terisi.");
		}
		if($param['tglrute']==''){
			exit("Warning : Tanggal wajib terisi.");
		}
		if($param['transport']==''){
			exit("Warning : Transport wajib terisi.");
		}
		if($param['time']==''){
			$param['time']="00-00";
		}
		
		$newdata = array();
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'dari'       =>$param['dari'],
			'tujuan'     =>$param['rutetujuan'],
			'tglrute'    =>tanggalsystemn($param['tglrute'])." ".$param['time'].":00",
			'transport'  =>$param['transport']
		);
		
	
		if($_SESSION['rute'] != array()){
			array_push($_SESSION['rute'],$newdata);
		}else{
			array_push($_SESSION['rute'],$newdata);
		}
	
		$no=0;
		foreach($_SESSION['rute'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['dari']."</td>";
				$tab.="<td style='text-align:left;width:70px'>".$row['tujuan']."</td>";
				$tab.="<td style='text-align:center' colspan=2>".waktunormal($row['tglrute'])."</td>";
				$tab.="<td style='text-align:left;width:120px'>".$row['transport']."</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='edit' class=zImgBtn onclick=\"editrute('".$key."','".$row['notransaksi']."','".$row['dari']."','".$row['tujuan']."','".tanggalnormal(substr($row['tglrute'],0,10))."','".substr($row['tglrute'],11,5)."','".$row['transport']."')\" src=images/application/application_edit.png>
				</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='Delete' class=zImgBtn onclick=\"deleterute('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		
		if($param['methodheader']=='updateheader'){			
			if($_SESSION['rute'] != array()){
				$str = "delete from " . $dbname . ".sdm_pjdinasdt_rute where notransaksi='".$param['notransaksi']."'";
				$owlPDO->exec($str);
				
				$data=array();
				$no=0;
				foreach($_SESSION['rute'] as $key=>$row){
					if($row['notransaksi'] == $param['notransaksi']){
						$no+=1;
						$data = array(
							'id'          => $no,
							'notransaksi' => $param['notransaksi'],
							'waktu'       => $row['tglrute'],
							'dari'        => $row['dari'],
							'tujuan'      => $row['tujuan'],
							'transportasi'=> $row['transport']
						);
						
						$cols = array();
						foreach($data as $keyn=>$rown) {
								$cols[] = $keyn;
						}
						$str = insertQuery($dbname,'sdm_pjdinasdt_rute',$data,$cols); #exit("error".$str);
						try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
					}
				}
			}
		}
		echo $tab;
	break;
	case'getdata':
		$_SESSION['rute']=array();
		
		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['kodejabatan']."'");
			$optgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$bar['kodegolongan']."'");
			$optdep=makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar['bagian']."'");
			$optlok=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['lokasitugas']."'");
			$optlevelkaryawan=makeOption($dbname,'sdm_5levelkaryawan','kode,nama');
			
			$kodejab=$bar['kodejabatan'];
			$kodegol=$bar['kodegolongan'];
			$kodedep=$bar['bagian'];
			$kodelok=$bar['lokasitugas'];
			$levelkaryawan=$bar['levelkaryawan'];
			
			$namajab=$optjab[$bar['kodejabatan']];
			$namagol=$optgol[$bar['kodegolongan']];
			$namadep=$optdep[$bar['bagian']];
			$namalok=$optlok[$bar['lokasitugas']];
			$tipekar=$bar['tipekaryawan'];
			$namalevel=$optlevelkaryawan[$bar['levelkaryawan']];
		}
		
		$str="select * from ".$dbname.".sdm_5tipekaryawan where id = '".$tipekar."'";
		$res=fetchdata($str); 
		foreach($res as $bar){
			$opttipekary.="<option value=".$bar['id']." selected>".$bar['tipe']."</option>";
		}

		if($tipekar == 0){
			## STAFF
			$jlhlevel = 0;
		}else{
			## NONSTAFF
			$jlhlevel = 1;
		}
		
		$str="select max(right(notransaksi,5)) as nomor from ".$dbname.".sdm_pjdinasht where kodeorg='".$kodelok."' and createtime like '".date('Y')."%'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no=$bar['nomor'];
		}

		if($no==0){
			$notran=$kodelok.date('Y').'00001';
		}else{
			$notran=$kodelok.date('Y').addZero($no+1,5);
		}
		
		
		echo $kodejab."##".$kodegol."##".$kodedep."##".$kodelok."##".$namajab."##".$namagol."##".$namadep."##".$namalok."##".$notran."##".$opttipekary."##".$jlhlevel."##".$levelkaryawan;
	break;
	
	case'getunit':
		$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where induk='".$param['pttujuan']."' and length(kodeorganisasi)='4' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optpt;
	break;
	
	case'getregion':
		$optreg=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',"kodeunit='".$param['unit']."'");
		
		$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".sdm_5regionalpjd";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optpt.="<option value=".$bar['regional'].">".$bar['nama']."</option>";
		}
		
		echo $optpt;
	break;
	case'loaddata':

        $where="";
		if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx'){
			$where.=" and tipekary='0'";
			$where.=" and karyawanid='".$_SESSION['standard']['userid']."'";
		}elseif($_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){
			if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				#tidak ada apa apa disini, alias munculkan semua
			} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				#hanya ro ke bawah
				$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KANWIL' and length(kodeorganisasi)='4')";
			} else {
				$where.=" and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			}			
			$where.=" and tipekary='1'";
		}elseif($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
			$where.=" and createdby!='".$_SESSION['standard']['userid']."'";
			$where.=" and statuspengajuan='1'";
			$where.=" and statusrealisasi='0'";
		}elseif($_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){
			#sudah pertanggung jawaban tapi belum di bayar
			$where.=" and statusrealisasi='9'";
			$where.=" and namahrd='".$_SESSION['standard']['userid']."'";
		}elseif($_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdstaffx'){
			$where.=" and tipekary='0'";
			$where.=" and statuspengajuan in ('1')";
			$where.=" and statusrealisasi in ('0','2','1')";
			$where.=" and karyawanid='".$_SESSION['standard']['userid']."'";
		}elseif($_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdnonstaffx'){
			$where.=" and tipekary='1'";
			$where.=" and statuspengajuan='1'";
			$where.=" and statusrealisasi in ('0','2','1')";
			if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe!='HOLDING' and length(kodeorganisasi)='4')";
			} else {
				$where.=" and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
			}
		}
		
        if ($param['notransaksilist'] != '') {
            $where.=" and notransaksi like '%".$param['notransaksilist']."%'";
        }
		if ($param['namakarylist'] != '') {
			$where.=" and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$param['namakarylist']."%')";
        }
		
        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
		$tab = "";
        $no = $maxdisplay;

		$arryjnspjd=array('0'=>'Staff','1'=>'Non Staff');
		$arrytiket=array('0'=>'Tidak','1'=>'Ya');
		$nmreg = makeOption($dbname, 'sdm_5regionalpjd', 'regional,nama');
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
		
		
		$sql = "select count(distinct notransaksi) as notr from " . $dbname . ".sdm_pjdinasht where 1=1 " . $where . "";
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=25 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
		$str = "SELECT *  FROM " . $dbname . ".sdm_pjdinasht where 1=1 " . $where . " order by createtime desc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
            $no+=1;
			$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."' or karyawanid='".$bar['namahrd']."' or karyawanid='".$bar['createdby']."'");
			$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."' or kodeorganisasi='".$bar['pttujuan']."' or kodeorganisasi='".$bar['unittujuan']."'");
			
			if($bar['unittujuan']!='' and $nmorg[$bar['unittujuan']]==''){$tujuan=$bar['unittujuan'];}else{$tujuan=$bar['unittujuan'];}
			if($bar['pttujuan']=='OTH' and $nmorg[$bar['pttujuan']]==''){$pttujuan=strtoupper($_SESSION['lang']['others']);}else{$pttujuan=$bar['pttujuan'];}
			
			
            $tab.="<tr height=25px class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['notransaksi']."</td>";
            $tab.="<td align=left>".$arryjnspjd[$bar['tipekary']]."</td>";
            $tab.="<td align=left>".$nmkar[$bar['karyawanid']]."</td>";
            $tab.="<td align=left>".$bar['kodeorg']."</td>";
			$tab.="<td align=center nowrap>".tanggalnormal($bar['tgldinasdari'])."</td>";
            $tab.="<td align=center nowrap>".tanggalnormal($bar['tgldinassampai'])."</td>";
            $tab.="<td align=center nowrap>".numb_format(selisitgl($bar['tgldinassampai'],$bar['tgldinasdari'])+1)."</td>";
            $tab.="<td align=center nowrap>".tanggalnormal($bar['tgldinasdarireal'])."</td>";
            $tab.="<td align=center nowrap>".tanggalnormal($bar['tgldinassampaireal'])."</td>";
            $tab.="<td align=center>".numb_format(selisitgl($bar['tgldinassampaireal'],$bar['tgldinasdarireal'])+1)."</td>";
            $tab.="<td align=left>".$pttujuan."</td>";
            $tab.="<td align=left>".$tujuan."</td>";
            $tab.="<td align=left>".$nmreg[$bar['regiontujuan']]."</td>";
            $tab.="<td align=left>".$bar['keterangan']."</td>";
            $tab.="<td align=center>".$arrytiket[$bar['tiket']]."</td>";
			
			$wr="";
			if($bar['statuspengajuan']=='9' or $bar['statuspengajuan']=='3'){
				$wr="style=background-color:yellow";
			}elseif($bar['statuspengajuan']=='1'){
				$wr="style=background-color:green";
			}elseif($bar['statuspengajuan']=='2'){
				$wr="style=background-color:red";
			}
			
            $tab.="<td align=left ".$wr.">".$arrHsl[$bar['statuspengajuan']]."</td>";
			
			
			$optapprhrd = makeOption($dbname, 'approval', 'karyawanid,karyawanid',"jenispersetujuan in ('PJDSTF','PJDNSTF','PJDMGR','PJDPC','PJDGM','PJDBOD') and notransaksi='".$bar['notransaksi']."' and status='0' and karyawanid='".$bar['namahrd']."'");
			
			/* #cari noreff uang muka
			$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."' and keterangan2='umpjd#".$bar['notransaksi']."' and nik='".$bar['karyawanid']."'";
			$resa = fetchdata($stra);
			$umbayar=0;$umnoreff="";
			foreach($resa as $bara){				
				$umbayar+=$bara['jumlah'];
				$umnoreff=$bara['notransaksi'];
			}
			
			#cari noreff ptj
			$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$bar['notransaksi']."' and keterangan2='claimpjd#".$bar['notransaksi']."' and nik='".$bar['karyawanid']."'";
			$resa = fetchdata($stra);
			$claimbayar=0;$claimnoreff="";
			foreach($resa as $bara){				
				$claimbayar+=$bara['jumlah'];
				$claimnoreff=$bara['notransaksi'];
			}
			
			$tab.="<td align=center>".numb_format($umbayar)."<br>
				<font style=font-style:italic;font-size:10px;font-weight:bold;>".$umnoreff."</font></td>";
			 */
			
			if($bar['statusrealisasi']==0 and $bar['statuspengajuan']!='1'){
				$tab.="<td align=left></td>";
			}elseif($bar['statusrealisasi']==0 and $bar['statuspengajuan']=='1'){
				if($bar['tipekary'] == '0'  and $_SESSION['pjd']['menu']!='sdm_confirmpjdx' and $_SESSION['pjd']['menu']!='sdm_pertanggungjawabanpjdstaffx'){
					$tab.="<td align=left style=color:red;cursor:pointer;text-decoration:underline; title=\"Click untuk pertanggung jawaban perjalan dinas\"><a href=\"javascript:do_load('sdm_pertanggungjawabanpjdstaffx')\" >Belum dipertanggung jawabankan</a></td>";
				}elseif($bar['tipekary'] == '1'  and $_SESSION['pjd']['menu']!='sdm_confirmpjdx' and $_SESSION['pjd']['menu']!='sdm_pertanggungjawabanpjdnonstaffx'){
					$tab.="<td align=left style=color:red;cursor:pointer;text-decoration:underline; title=\"Click untuk pertanggung jawaban perjalan dinas\"><a href=\"javascript:do_load('sdm_pertanggungjawabanpjdnonstaffx')\" >Belum dipertanggung jawabankan</a></td>";
				}else{
					$tab.="<td align=left>Belum dipertanggung jawabankan</td>";					
				}
			}elseif($bar['statusrealisasi']==9 and $optapprhrd[$bar['namahrd']]==$_SESSION['standard']['userid'] and $_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){
				$tab.="<td align=left>
					<a href=\"javascript:do_load('log_approval')\" title='Approval'>ke menu approval</a>
				</td>";
			}else{
				$wr="";
				if($bar['statusrealisasi']=='9' or $bar['statusrealisasi']=='3'){
					$wr="style=background-color:yellow";
				}elseif($bar['statusrealisasi']=='1'){
					$wr="style=background-color:green";
				}elseif($bar['statusrealisasi']=='2'){
					$wr="style=background-color:red";
				}
				
				$tab.="<td align=left ".$wr.">".$arrHsl[$bar['statusrealisasi']]."<br>
				<font style=font-style:italic;font-size:10px;font-weight:bold;>".$nmkar[$bar['namahrd']]."</font></td>";
			}
			
            
            $tab.="<td align=left>".$nmkar[$bar['createdby']]."</td>";
			/* $tab.="<td align=center>".numb_format($claimbayar)."<br>
				<font style=font-style:italic;font-size:10px;font-weight:bold;>".$claimnoreff."</font></td>"; */
			
			if($bar['tipekary'] == 0){
				$kodeapproval = 'PJDSTF';
			}else{
				$kodeapproval = 'PJDNSTF';
			}
			
			// $kodeapproval = makeOption($dbname, 'sdm_5levelpjdinas', 'level,kodeapproval',"level='".$bar['level']."'");

			
			# jika sumber dari pengajuan dan status = 0
			$n="";
			$n=($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx');
			
			if($n and $bar['statuspengajuan']=='0'){
				$tab.="<td align=center style=width:20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";				

				$tab.="<td align=center style=width:20px><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan ?' onclick=\"form_ajukan('".$bar['notransaksi']."','".$kodeapproval."','pengajuan','".$no."');\" ></td>";
				
			}elseif($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
				$tab.="<td style=width:20px></td>";
				$stre = "select sum(jumlah) as jumlah from ".$dbname.".sdm_pjdinasht a  left join ".$dbname.".sdm_pjdinasdt b on a.notransaksi=b.notransaksi where 1=1 and a.notransaksi='".$bar['notransaksi']."' and b.sumber='1' and b.tanggungan='0' and a.statuspengajuan='1' and b.jumlah>'0'";
				$rese = fetchdata($stre);
				$jlhreal = $rese[0]['jumlah'];
				
				if($bar['statusconfirm']=='1'){
					$tab.="<td style=width:20px></td>";
					$tab.="<td style=width:20px align=center><img src=images/skyblue/posted.png class=zImgBtn height=30 title='Posted'></td>";
				}elseif($bar['statusconfirm']=='0' and $jlhreal>0){
					$tab.="<td align=center style=width:20px><img src=images/application/application_go.png class=zImgBtn  title='View detail transaction' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
					
					$tab.="<td style=width:20px align=center><img src=images/skyblue/posting.png class=zImgBtn height=30 title='Posting' onclick=postingconfirm('".$bar['notransaksi']."');></td>";
				}else{
					$tab.="<td align=center style=width:20px><img src=images/application/application_go.png class=zImgBtn  title='View detail transaction' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
					
					$tab.="<td style=width:20px align=center><img src=images/postinggray.png class=zImgBtn height=30 title='Belum ada transaksi'></td>";
				}
				
			}elseif($_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdstaffx' and ($bar['statusrealisasi']==0 or $bar['statusrealisasi']==2)){	
				$tab.="<td style=width:20px></td>";
				$tab.="<td align=center style=width:20px><img src=images/application/application_go.png class=zImgBtn  title='View detail transaction' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
				#ini untuk ajukan hanya ke hrd untuk di lakukan verifikasi
				$tab.="<td align=center style=width:20px><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan ?' onclick=\"form_ajukan('".$bar['notransaksi']."','".$kodeapproval."','pertanggung','".$no."');\" ></td>";
			}elseif($_SESSION['pjd']['menu'] == 'sdm_pertanggungjawabanpjdnonstaffx' and ($bar['statusrealisasi']==0 or $bar['statusrealisasi']==2)){	
				$tab.="<td style=width:20px></td>";
				$tab.="<td align=center style=width:20px><img src=images/application/application_go.png class=zImgBtn  title='View detail transaction' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
				#ini untuk ajukan hanya ke hrd untuk di lakukan verifikasi
				$tab.="<td align=center style=width:20px><img src=images/skyblue/submit.jpg class=zImgBtn  title='Ajukan ?' onclick=\"form_ajukan('".$bar['notransaksi']."','".$kodeapproval."','pertanggung','".$no."');\" ></td>";
			}elseif($optapprhrd[$bar['namahrd']]==$_SESSION['standard']['userid'] and $_SESSION['pjd']['menu']=='sdm_verifikasiptjpjdx'){
				$tab.="<td style=width:20px></td>";
				$tab.="<td style=width:20px></td>";
				$tab.="<td align=center style=width:20px><img src=images/application/application_go.png class=zImgBtn  title='View detail transaction' onclick=\"fillfield('".$bar['notransaksi']."','".$_SESSION['pjd']['menu']."');\" ></td>";
				
			}elseif($bar['statuspengajuan']=='1' and $bar['statusrealisasi']=='0' and ($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx')){
				$tab.="<td style=width:20px></td>";
				$tab.="<td style=width:20px></td>";
				$tab.="<td align=center style=width:20px><img src=images/stop1.png class=zImgBtn  title='Batal' onclick=\"form_batal('".$bar['notransaksi']."');\" ></td>";
			}else{
				$tab.="<td style=width:20px></td>";
				$tab.="<td style=width:20px></td>";
				$tab.="<td style=width:20px></td>";
			}
				
			if(getindukPT($bar['kodeorg']) == 'PPP'){
				$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('".$bar['notransaksi']."','event','pdf');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detailData('".$bar['notransaksi']."','event','html');\" ></td>";
				
				$tab.="<td hidden align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".$bar['notransaksi']."','event','excel');\" ></td>";
			}else{
				$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('".$bar['notransaksi']."','event','pdf');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/pdf.jpg class=zImgBtn class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF2('".$bar['notransaksi']."','event','pdf');\" ></td>";
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"detailData2('".$bar['notransaksi']."','event','html');\" ></td>";
				
				$tab.="<td hidden align=center style=width:20px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel2('".$bar['notransaksi']."','event','excel');\" ></td>";
			}
            $tab.="</tr>";
        }

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd = "";
        $footd.="</tr><tr><td colspan=25 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";

        echo $tab . "####" . $footd;

	break;
	case'fillfield':
		$n="";
		$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$strx="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['karyawanid']."'";
			$resx=fetchdata($strx);
			foreach($resx as $barx){
				$optjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$barx['kodejabatan']."'");
				$optgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$barx['kodegolongan']."'");
				$optdep=makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$barx['bagian']."'");
				$optlok=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$barx['lokasitugas']."'");
				
				$kodejab=$barx['kodejabatan'];
				$kodegol=$barx['kodegolongan'];
				$kodedep=$barx['bagian'];
				$kodelok=$barx['lokasitugas'];
				$levelkaryawan=$barx['levelkaryawan'];
			}
			
			$tipekar=$bar['level'];
			$n = $bar['notransaksi']."##".$bar['karyawanid']."##".$bar['tipekary']."##".tanggalnormal($bar['tgldinasdari'])."##".tanggalnormal($bar['tgldinassampai'])."##".$bar['kodeorg']."##".$bar['keterangan']."##".$bar['pttujuan']."##".$bar['unittujuan']."##".$bar['regiontujuan']."##".$bar['tiket']."##".$kodejab."##".$kodegol."##".$kodedep."##".$tipekar."##".tanggalnormal($bar['tgldinasdarireal'])."##".tanggalnormal($bar['tgldinassampaireal'])."##".$levelkaryawan;
		}
		
		$_SESSION['rute']=array();
		$str="select * from ".$dbname.".sdm_pjdinasdt_rute where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$_SESSION['rute'][]=array(
				'notransaksi'=>$bar['notransaksi'],
				'dari'       =>$bar['dari'],
				'tujuan'     =>$bar['tujuan'],
				'tglrute'    =>$bar['waktu'],
				'transport'  =>$bar['transportasi']
			);
		}
		
		$dis="style=display:none";
		if($_SESSION['pjd']['menu']=='sdm_pengajuanpjdstaffx' or $_SESSION['pjd']['menu']=='sdm_pengajuanpjdnonstaffx'){
			$dis="";
		}
		$no=0;
		foreach($_SESSION['rute'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$no+=1;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:left;'>".$row['dari']."</td>";
				$tab.="<td style='text-align:left;'>".$row['tujuan']."</td>";
				$tab.="<td style='text-align:center' nowrap colspan=2>".waktunormal($row['tglrute'])."</td>";
				$tab.="<td style='text-align:left;'>".$row['transport']."</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='edit' ".$dis." style=disabled:true; class=zImgBtn onclick=\"editrute('".$key."','".$row['notransaksi']."','".$row['dari']."','".$row['tujuan']."','".tanggalnormal(substr($row['tglrute'],0,10))."','".substr($row['tglrute'],11,5)."','".$row['transport']."')\" src=images/application/application_edit.png>
				</td>";
				$tab.="<td style='text-align:center;width=20px'>
					<img title='Delete' ".$dis." class=zImgBtn onclick=\"deleterute('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		echo $n."##".$tab;
	break;
	case'fillfieldagenda':
		$n="";
		$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' and jenis='".$param['jenis']."' and tanggal='".$param['tanggal']."' and tanggal2='".$param['tanggal2']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$n = $bar['keterangan']."##".$bar['lokasi']."##".$bar['koordinasidengan']."##".tanggalnormal($bar['tanggal'])."##".tanggalnormal($bar['tanggal2']);
		}
		
		echo $n;
	break;
	
	case 'showupload':
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$optjns['realkeg']='Realisasi Kegiatan';
		
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>".$_SESSION['lang']['jenisbiaya']."</td>
				<td>:</td>
				<td>
					<select id='jenisupload'>
						<option value=".$param['jenisupload'].">".$optjns[$param['jenisupload']]."</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' onclick=enabletombol(); name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notransaksi."','".$param['jenisupload']."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=20px>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>".$_SESSION['lang']['jenisbiaya']."</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
	case 'showuploadperhari':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' onclick=enabletombol(); name='uploadPerhari' id='uploadPerhari' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfileperhari('".$notransaksi."', '".$tanggal."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=20px>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>".$_SESSION['lang']['jenisbiaya']."</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
	case 'submitfile':
	
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."'";
		$res=fetchData($str);
		if(!empty($res)){
			foreach($res as $bar){
				$jn=".";
				if($bar['jenisbiaya']!=$param['jenisupload']){
					$jn=" dengan jenis biaya :\n";
					$jn.=$optjns[$bar['jenisbiaya']]."\n";
				}
			}
			exit("Warning : Nama file sudah ada".$jn."");
		}
		
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."' and jenisbiaya='".$param['jenisupload']."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Nama file sudah ada.");
		}
		
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				if(!preg_match("/^[a-zA-Z0-9 .]*$/",$_FILES['file']['name'])){
					//exit("Warning : Nama file hanya boleh ngandung Huruf, angka, spasi dan titik.");
				}
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$jns="real";
				}else{
					$jns="klaim";
				}
				
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					/*if($_FILES['file']['size'] <= 250000){*/
						$str = "insert into ".$dbname.".file_pjdinas values ('','".$notransaksi."','".$param['jenisupload']."','".$jns."','".$filename."','".$filetype."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try{
							$owlPDO->exec($str);
							if (!file_exists($path)) {
								mkdir($path, 0777, true);
							}
							file_put_contents($path.$filename,$file_tmpname);
						}
						catch(PDOException $e){
							echo " Gagal," . addslashes($e->getMessage());
						}
					/*}else{
						exit("warning : Ukuran file upload maksimal 250kb");
					}*/
				}else{
					exit("Warning : Format file upload harus *.jpg, *.jpeg, *.png, *.pdf, *.xls, *.xlsx, *.doc, *.docx");
				}
			}
		}
	break;
	case 'submitfileperhari':

		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and namafile='".$_FILES['file']['name']."' and tanggalpjd='".$tanggal."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Nama file sudah ada.");
		}
		
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				if(!preg_match("/^[a-zA-Z0-9 .]*$/",$_FILES['file']['name'])){
					//exit("Warning : Nama file hanya boleh ngandung Huruf, angka, spasi dan titik.");
				}
				if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
					$jns="real";
				}else{
					$jns="klaim";
				}
				
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = explode(".", $_FILES['file']['name']);
				$filenamereal = $filename[0].'-'.$tanggal.'-'.$jns.'-'.time();
				$filenamereal = $filenamereal.$filetype;

				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					/*if($_FILES['file']['size'] <= 250000){*/
						// $str = "insert into ".$dbname.".file_pjdinas values ('','".$notransaksi."','".$param['tanggal']."','".$jns."','".$filename."','".$filetype."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						$datas = [$notransaksi, $tanggal, $jns, $filenamereal, $filetype, $_SESSION['standard']['userid'], date('Y-m-d H:i')];
						$keys = ['notransaksi', 'tanggalpjd', 'jenis', 'namafile', 'formaticon', 'updateby', 'updatetime'];
						$str = insertQuery($dbname, 'file_pjdinas', $datas, $keys);
						try{
							$owlPDO->exec($str);
							if (!file_exists($path)) {
								mkdir($path, 0777, true);
							}
							file_put_contents($path.$filenamereal,$file_tmpname);
						}
						catch(PDOException $e){
							echo " Gagal," . addslashes($e->getMessage());
						}
					/*}else{
						exit("warning : Ukuran file upload maksimal 250kb");
					}*/
				}else{
					exit("Warning : Format file upload harus *.jpg, *.jpeg, *.png, *.pdf, *.xls, *.xlsx, *.doc, *.docx");
				}
			}
		}
	break;
	case 'loadfiles':
		$no = 0; $wh="";
		if($param['jenisupload']!=''){
			$wh="and jenisbiaya='".$param['jenisupload']."'";
			/* $tab.="<table class='sortable' cellspacing='1' border='0' width=100%>
					<thead>
					<tr class=rowheader>
						<td align='center' width=20px>No.</td>
						<td align='center'>File Type</td>
						<td align='center'>".$_SESSION['lang']['jenisbiaya']."</td>
						<td align='center'>Filename</td>
						<td align='center' colspan=2>Action</td>
					</tr>
					</thead>"; */
		}
		if($param['jenis']!=''){
			$jns="and jenis ='".$param['jenis']."'";
		}else{		
			if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
				$jns="and jenis ='real'";
			}else{
				$jns="and jenis ='klaim'";
			}
		}
		
		$statuspjd=makeOption($dbname,'sdm_pjdinasht','notransaksi,statusrealisasi',"notransaksi = '".$notransaksi."'");
		
		$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
		$optjns['realkeg']='Realisasi Kegiatan';
		
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' ".$wh." ".$jns."";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$nfile = $val['namafile'];
				$tab.="<td style='text-align:left'>".$optjns[$val['jenisbiaya']]."</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
				
				$tab.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
				
				if($val['updateby'] == $_SESSION['standard']['userid'] and $statuspjd[$notransaksi]!='1'){				
					$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."','".$val['jenisbiaya']."');\" ></td>";
				}else{
					$tab.="<td></td>";
				}
				$tab."</tr>";
			}
		}
		echo $tab;
	break;
	case 'loadfilesperhari':
		$no = 0; $wh="";
		if($tanggal!=''){
			$wh="and tanggalpjd='".$tanggal."'";
		}
		if($param['jenis']!=''){
			$jns="and jenis ='".$param['jenis']."'";
		}else{		
			if($_SESSION['pjd']['menu']=='sdm_confirmpjdx'){
				$jns="and jenis ='real'";
			}else{
				$jns="and jenis ='klaim'";
			}
		}
		
		$statuspjd=makeOption($dbname,'sdm_pjdinasht','notransaksi,statusrealisasi',"notransaksi = '".$notransaksi."'");
		
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' ".$wh." ".$jns."";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$nfile = $val['namafile'];
				// $tab.="<td style='text-align:left'>".$optjns[$val['jenisbiaya']]."</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
				
				$tab.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
				
				if($val['updateby'] == $_SESSION['standard']['userid'] and $statuspjd[$notransaksi]!='1'){				
					$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefileperhari('".$val['notransaksi']."','".$val['namafile']."','".$val['tanggalpjd']."');\" ></td>";
				}else{
					$tab.="<td></td>";
				}
				$tab."</tr>";
			}
		}
		echo $tab;
	break;
	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$param['namafile']."' style='width:50%;height:50%;'>";
		echo $tab;
	break;
	case'viewfilepjdinas':
		$tab="";
		$str= "select * from ".$dbname.".file_pjdinas where namafile = '".$param['namafile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";

		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		echo $tab;
	break;	
	
	case 'deletefile':
		$str="delete from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."' and namafile='".$param['namafile']."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'previewdata':
		$tab="";
		$tab2="";
		$tab3="";
		$tab4="";
		$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)<=4");
		$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$nmgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
		$nmdep = makeOption($dbname,'sdm_5departemen','kode,nama');
		$nmlev = makeOption($dbname,'sdm_5levelkaryawan','kode,nama');

		// $jnsapp = makeOption($dbname,'sdm_5levelpjdinas','level,kodeapproval');
		$nmreg = makeOption($dbname,'sdm_5regionalpjd','regional,nama');
		$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi = '".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $bar){
			$strx="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['karyawanid']."'";
			$resx=fetchData($strx);
			foreach($resx as $barx){
				$nmkar[$barx['karyawanid']]=$barx['namakaryawan'];
				$nkkar[$barx['karyawanid']]=$barx['nik'];
				$jabkar[$barx['karyawanid']]=$nmjab[$barx['kodejabatan']];
				$golkar[$barx['karyawanid']]=$nmgol[$barx['kodegolongan']];
				$depkar[$barx['karyawanid']]=$nmdep[$barx['bagian']];
				$levkar[$barx['karyawanid']]=$nmlev[$barx['levelkaryawan']];
				$tipekar[$barx['karyawanid']]=$barx['tipekaryawan'];
			}
			
			$statuspengajuan= $bar['statuspengajuan'];
			$batal  =$bar['keteranganbatal'];
			$kodeorg= $bar['kodeorg'];
			$karyid = $bar['karyawanid'];
			$ket    = $bar['keterangan'];
			if($bar['pttujuan']!='OTH'){
				$pttujuan    = $nmorg[$bar['pttujuan']];
				$unittujuan  = $nmorg[$bar['unittujuan']];
			}else{
				$pttujuan    = $bar['pttujuan'];
				$unittujuan  = $bar['unittujuan'];
			}
			if($bar['tiket']=='1'){
				$tiket="Ya";
			}else{
				$tiket="Tidak";
			}
			
			$regiontujuan= $nmreg[$bar['regiontujuan']];
			$tgldr       = tanggalnormal($bar['tgldinasdari']);
			$tgldrreal   = tanggalnormal($bar['tgldinasdarireal']);
			$tglsd       = tanggalnormal($bar['tgldinassampai']);
			$tglsdreal   = tanggalnormal($bar['tgldinassampaireal']);
			$namakary    = $nmkar[$bar['karyawanid']];
			$nikkar      = $nkkar[$bar['karyawanid']];
			$jabatan     = $jabkar[$bar['karyawanid']];
			$golongan    = $golkar[$bar['karyawanid']];
			$dept        = $depkar[$bar['karyawanid']];
			$levelkar    = $levkar[$bar['karyawanid']];

			$level       = $nmlev[$bar['level']];
			$kodeapproval= $jnsapp[$bar['level']];
		}


		
		#cari noreff uang muka
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2='umpjd#".$notransaksi."' and nik='".$karyid."'";
		$resa = fetchdata($stra);
		$umdibayarkan=0;$umnoreff="";
		foreach($resa as $bara){				
			$umdibayarkan+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$umnoreff=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff uang bayar oleh pt
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'realpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$realpt=0;$realptnoreff="";
		foreach($resa as $bara){				
			$realpt+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$realptnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff ptj
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'claimpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$claimbayar=0;$claimnoreff="";
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'pjdbatal#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){				
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		
		

		$waktuawal = tanggalsystemn($tgldrreal);
		$waktuakhir = tanggalsystemn($tglsdreal);
		
		$diff = (strtotime($waktuakhir)-strtotime($waktuawal));
		$hari = floor($diff/(60*60*24));
			
		
		if($jenis=='pdf'){
			$arrHead = setheadreport(getindukPT($kodeorg),$kodeorg);
			$path=$arrHead['logo'];

			$tab.="<div>
				<table cellspacing=0 border=0 width=100% align=center style=\"font-family:sans-serif;font-size:12px;\">
					<tr>
						<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table><hr>";
		}

	
		$fontsize="13px";
		
		$top=$bottom=$left=$right="";
		// $top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		// $left    ="border-left:0.5px solid black;";
		// $right   ="border-right:0.5px solid black;";
		
		#style=\"font-family:sans-serif;font-size:13px;font-weight:bold;\"
		$tab.="
		<table cellspacing=0 border=0 width=100% style='text-align:center'>
			<tr>
				<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>SURAT PERJALANAN DINAS</td>
			</tr>
			<tr>
				<td style=\"font-family:sans-serif;font-size:13px;font-weight:bold;\">Nomor : ".$notransaksi."</td>
			</tr>
		</table>
		<br>
		<table cellspacing=0 cellpadding=3 border=0 width=100% style=\"font-family:sans-serif;font-size:".$fontsize.";\">
			<tr>
				<td width=100px>".$_SESSION['lang']['namakaryawan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$namakary."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>PT ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$pttujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['nik2']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$nikkar."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$unittujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['jabatan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$jabatan."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$regiontujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['kodegolongan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$golongan."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>Level Karyawan</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$levelkar."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['departemen']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$dept."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['ticket']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$tiket."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['tanggaldinas']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$tgldrreal." s/d ".$tglsdreal."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['jumlah']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".($hari+1)."  (hari)</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td valign=top width=100px>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top width=10px align=left>:</td>
				<td colspan=5>".$ket."</td>
			</tr>
		</table>
		<br>
		</div>";
		
		$top=$bottom=$left=$right="";
		$top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		$left    ="border-left:0.5px solid black;";
		$right   ="border-right:0.5px solid black;";
		
		#RUTE PERJALANAN
		$tujoth=makeOption($dbname,'sdm_pjdinasht','notransaksi,pttujuan',"notransaksi = '".$notransaksi."'");
		$tab.="<label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['rute']." :</label>";
		$str="select * from ".$dbname.".sdm_pjdinasdt_rute where notransaksi = '".$notransaksi."' order by waktu asc";
		$res=fetchData($str);
		if(!empty($res)){
			$tab.="<table cellspacing=0 cellpadding=5 border=0 width=100% style=\"font-family:sans-serif;font-size:".$fontsize.";\">
				<tr>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;width:30px;'>No</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['dari']."</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['tujuan']."</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;' width=150px>".$_SESSION['lang']['waktu']."</td>
					<td align=center style='".$left."".$right."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['transport']."</td>";
				
				if($tujoth[$notransaksi]=='OTH'){
					$tab.="<td align=center style='".$left."".$right."".$top."".$bottom.";font-weight:bold;'>Paraf</td>";
				}	
			$tab.="</tr>";
				
			$no=0;
			foreach($res as $bar){
			$no+=1;
				$tab.="<tr>
					<td width=30px align=center style='".$left."".$bottom."'>".$no."</td>
					<td align=left style='".$left."".$bottom."'>".$bar['dari']."</td>
					<td align=left style='".$left."".$bottom."'>".$bar['tujuan']."</td>
					<td align=center style='".$left."".$bottom."' width=150px>".waktunormal($bar['waktu'])."</td>
					<td align=left style='".$left."".$right."".$bottom."'>".$bar['transportasi']."</td>";
					
					if($tujoth[$notransaksi]=='OTH'){
						$tab.="<td align=left style='".$left."".$right."".$bottom."height:30px'></td>";
					}
				$tab.="</tr>";
			}
			$tab.="</table>";
		}
		
		#BIAYA
		$top=$bottom=$left=$right="";
		/* $top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		$left    ="border-left:0.5px solid black;";
		$right   ="border-right:0.5px solid black;"; */
		
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' order by tanggal,jenisbiaya asc";
		$res=fetchdata($str);
		$verhrd=$klaimkary=$sdhrealpt=$umminta=0;$data=$dataisi=$rangetgl=array();
		foreach($res as $bar){
			$databyy[$bar['jenisbiaya']]=$bar['jenisbiaya'];
			$rangetgl[$bar['tanggal']]=$bar['tanggal'];
			
			
			if($bar['sumber']=='0'){
				$umminta+=$bar['jumlah'];
			}
			if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
				$sdhrealpt+=$bar['jumlah'];
			}
			if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
				$klaimkary+=$bar['jumlah'];
			}
			if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
				$verhrd+=$bar['jumlahhrd'];
			}
		}
	
		if(!empty($res)){
			$tab.="<br><label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['biaya']." :</label>";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
			}
			$tab.="<table ".$style.">
			<tr class=rowcontent>
				<td align=center>Keterangan</td>
				<td align=center></td>
				<td align=center>Diminta</td>
				<td align=center>Dibayar</td>
				<td align=center>No Reff</td>
			</tr>
			<tr class=rowcontent>
				<td >Total uang muka diminta</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($umminta)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total uang muka diterima / dibayarkan<br>
				<!--<label style=\"font-family:sans-serif;font-weight:bold;font-style:italic;font-size:12px;\">".$umnoreff."</label>--></td>
				<td width=10px align=center>:</td>
				<td align=center></td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($umdibayarkan)."</td>
				<td align=left>".$umnoreff."</td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang sudah direalisasikan oleh perusahaan</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($sdhrealpt)."</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($realpt)."</td>
				<td align=left>".$realptnoreff."</td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang diajukan reimburse / klaim oleh karyawan</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($klaimkary)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang telah di verifikasi (disetujui)</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($verhrd)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang telah dibayarkan / dikembalikan<br>
				<!--<label style=\"font-family:sans-serif;font-weight:bold;font-style:italic;font-size:12px;\">".$claimnoreff."</label>--></td>
				<td width=10px align=center>:</td>
				<td align=center></td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($claimbayar)."</td>
				<td align=left>".$claimnoreff."</td>
			</tr>
			";
			$tab.="</table>";
		}
		
		if($statuspengajuan!='0'){
			$tab.="<br><label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['approval_status']." (by system):</label>";
			$countApprove= getCountApproval($kodeapproval,$kodeorg);
			$top=$bottom=$left=$right="";
			$top     ="border-top:0.5px solid black;";
			$bottom  ="border-bottom:0.5px solid black;";
			$left    ="border-left:0.5px solid black;";
			$right   ="border-right:0.5px solid black;";
			
			
			
			
			
			
			
			
			
			$tab.="<table cellspacing=0 cellpadding=5 border=0 style=\"font-family:sans-serif;font-size:".$fontsize.";\">";
			if(($countApprove-1)!=0){			
			$arrHslx=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['koreksi']);
				$tab.="<tr><td colspan=4><b>Pengajuan</b></td></tr>";
				$str = "select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and keterangan=''";
				$res = fetchdata($str);
				foreach($res as $bar){
					$tab.="<tr>
						<td width=30px align=center style='".$left."".$bottom."".$top."'>".$bar['level']."</td>
						<td align=left style='".$top."".$bottom."'>".getKary($bar['karyawanid'])."</td>
						<td align=left style='".$bottom."".$top."'>".$arrHslx[$bar['status']]."<br><font style=\"font-size:10px;font-style:italic;\">".$bar['tanggal']."</font></td>
						<td align=left style='".$right."".$bottom."".$top."'>".$bar['komentar']."</td>
					</tr>";
				}
				
				
				$str = "select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and keterangan='pertanggung'";
				$res = fetchdata($str);
				if(count($res)!=0){
					$tab.="<tr><td colspan=4><b>Pertanggung Jawaban</b></td></tr>";
					foreach($res as $bar){
						$tab.="<tr>
							<td width=30px align=center style='".$left."".$bottom."".$top."'>".$bar['level']."</td>
							<td align=left style='".$top."".$bottom."'>".getKary($bar['karyawanid'])."</td>
							<td align=left style='".$bottom."".$top."'>".$arrHslx[$bar['status']]."<br><font style=\"font-size:10px;font-style:italic;\">".$bar['tanggal']."</font></td>
							<td align=left style='".$right."".$bottom."".$top."'>".$bar['komentar']."</td>
						</tr>";
					}
				}
				
			}
			$tab.="</table>";
		}
		
		if($statuspengajuan=='3'){
			$tab.="<br>Perjalanan dinas telah di batalkan dengan alasan :<br>".$batal."";
		}
		
		
		if($databyy){
			if($jenis!='pdf'){				
				$tab2.="<br>";
			}
			$tab2.="<div style='page-break-before: always;'></div>";
			$tab2.="
			<table cellspacing=0 border=0 width=100% style='text-align:center'>
				<tr>
					<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>PERINCIAN BIAYA PERJALANAN DINAS</td>
				</tr>
			</table>";
			
			$top=$bottom=$left=$right="";
			$top     ="border-top:0.5px solid black;";
			$bottom  ="border-bottom:0.5px solid black;";
			$left    ="border-left:0.5px solid black;";
			$right   ="border-right:0.5px solid black;";
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and (umdriver!='' or tujuandriver!='')";
			$res=fetchdata($str);
			$dr=count($res);
			$row="";$con=0;$conttl=0;
			if($dr==0){					
				if($jenis!='pdf'){					
					$con=(count($rangetgl)+6);
				}else{
					$con=6;
				}
				$conttl=0;
			}else{
				if($jenis!='pdf'){					
					$con=(count($rangetgl)+8);
				}else{
					$con=8;
				}
				$conttl=2;
			}
			if($jenis!='pdf'){
				$row="rowspan=2";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjnsdriver=makeOption($dbname,'sdm_5setupdinasdriver','id,keterangan');
		
			$fontsize="10px";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=5 cellspacing=1 border=0 class=sortable width=100%";
			}
			$tab2.="<br><table ".$style." width=100%>
				<thead>
				<tr class=rowheader>";
					$tab2.="
					<td align=center style=font-weight:bold; ".$row." width=20px>No</td>
					<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['jenisbiaya']."</td>";
					if($dr>0){
						$tab2.="
						<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['jenis']."</td>
						<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['tujuan']."</td>";
					}		
					$tab2.="
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['location']."</td>";
					
					if($jenis!='pdf'){
						$tab2.="
						<td align=center style=font-weight:bold; colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</td>";
					}
					$tab2.="
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['totalbiaya']."</td>
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['keterangan']."</td>
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['dibuat']."</td>
				</tr>";
				if($jenis!='pdf'){	
					$tab2.="<tr class=rowheader>";
					foreach($rangetgl as $tgl){	
						$tab2.="<td style=font-weight:bold; align=center>".substr($tgl,8,2)."</td>";
					}
					$tab2.="</tr>";
				}
				$tab2.="</thead>";
				$tab2.="<tbody>";
			
			# UANG MUKA
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='0' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahum=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahum[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Uang Muka diminta :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy =>$valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){					
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahum[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahum[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# UANG REAL BY PT
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='0' and sumber='1' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}
			
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";

				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Realisasi oleh perusahaan :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy =>$valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){					
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# KLAIM KARYAWAN
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";
				
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Reimburse / Klaim :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){	
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# VERIFIKASI
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' and statusverifikasihrd='1' and jumlahhrd!='0' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlahhrd'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlahhrd'];
			}
			$nmhrd=makeOption($dbname,'sdm_pjdinasht','notransaksi,namahrd',"notransaksi='".$notransaksi."'");
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";
				
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Verifikasi (dibayarkan) :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){	
						
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td></td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			$tab2.="</tbody>";
			$tab2.="</table>";
			
			
			#FILE UPLOAD BIAYA
			if($jenis!='pdf'){				
				$tab2.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and jenisbiaya!='realkeg'";
			$res=fetchData($str);
			if(!empty($res)){
				$tab2.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>File Upload (biaya)</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab2.="<td align=center width=20px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab2.="<td align='center' style=font-weight:bold;>File Type</td>";
				}
					$tab2.="<td align='center' style=font-weight:bold;>".$_SESSION['lang']['tanggal']."</td>
						<td align='center' style=font-weight:bold;>Jenis</td>
						<td align='center' style=font-weight:bold;>Filename</td>";
				if($jenis=='html'){
					$tab2.="<td align='center' style=font-weight:bold;>Action</td>";
				}
				$tab2.="</tr>
				</thead>";
				$tab2.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab2.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					if($jenis=='html'){
					$tab2.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					}
					$nfile = $val['namafile'];
					$tab2.="<td style='text-align:left'>".ucwords(strtolower($val['tanggalpjd']))."</td>";
					$tab2.="<td style='text-align:left'>".ucwords(strtolower($val['jenis']))."</td>";
					$tab2.="<td style='text-align:left;cursor:pointer' onclick=\"viewfilepjdinas('event','".$val['namafile']."')\">".$nfile."</td>";
					if($jenis=='html'){						
						$tab2.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}
					$tab2."</tr>";
				}
			}
			
			
			$tab2.="</tbody>";
			$tab2.="</table>";
		}#tutup id $data
		
		
		#DATA AGENDA
		$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' order by tanggal asc";
		$res=fetchdata($str);
		if(count($res)>0){
			#JIKA ADA DATA MUNCULKAN
			if($jenis!='pdf'){				
				$tab3.="<br>";
			}
			$tab3.="<div style='page-break-before: always;'></div>";
				$tab3.="
				<table cellspacing=0 border=0 width=100% style='text-align:center'>
					<tr>
						<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>KEGIATAN PERJALANAN DINAS</td>
					</tr>
				</table>";
			$fontsize="10px";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=3 cellspacing=1 border=0 class=sortable width=100%";
			}
			$tab3.="<br><table ".$style." width=100%>
			<thead><tr class=rowheader>";
			$tab3.="<td align=center width=20px style=font-weight:bold;>No</td>
				<td align=center width=55px style=font-weight:bold;>".$_SESSION['lang']['tanggal'] . "</td>
				<td align=center width=50px style=font-weight:bold;>".$_SESSION['lang']['hari'] . "</td>
				<td align=center width=55px style=font-weight:bold;>".$_SESSION['lang']['jenis'] . "</td>
				<td align=center style=font-weight:bold;>".$_SESSION['lang']['location'] . "</td>
				<td align=center style=font-weight:bold;>".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center style=font-weight:bold;>Koordinasi<br>Dengan</td>
			</tr>
			</thead>";
			$no=0;
			$data=array();
			$arrjns=array();
			foreach($res as $bar){
				$data[$bar['tanggal']]=$bar['tanggal'];
				$lok[$bar['tanggal']][$bar['jenis']]=$bar['lokasi'];
				$ketx[$bar['tanggal']][$bar['jenis']]=$bar['keterangan'];
				$koo[$bar['tanggal']][$bar['jenis']]=$bar['koordinasidengan'];
				$upd[$bar['tanggal']][$bar['jenis']]=$bar['updateby'];
				$tglupd[$bar['tanggal']][$bar['jenis']]=$bar['updatetime'];
				if($bar['statusconfrim']==1){
					$sta='Ya';
				}else{
					$sta='Tidak';
				}
				$stsc[$bar['tanggal']][$bar['jenis']]=$sta;
			}
			
			$arrjns=getEnum($dbname,'sdm_pjdinasdt2','jenis');
			$no=0;
			foreach($data as $tglagen){
				$n="";
				if(hari($tglagen,'ID')=='Minggu'){
					$n="style=color:red";
				}
				$no+=1;
				$tab3.="<tr class=rowcontent style=vertical-align:top>";
				$tab3.="<td align=center rowspan=3>".$no."</td>";
				$tab3.="<td align=center rowspan=3>".$tglagen."</td>";
				$tab3.="<td align=center rowspan=3 ".$n.">".hari($tglagen,'ID')."</td>";
				foreach($arrjns as $jns){
					if($jns=='renc'){
						$tab3.="<td align=left style=font-style:italic;background-color:#CDFED1;>".$jns."</td>";
						$tab3.="<td align=left >".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left >".nl2br($ketx[$tglagen][$jns])."</td>";
						$tab3.="<td align=left >".$koo[$tglagen][$jns]."</td>";
					}
					if($jns=='conf'){
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$upd[$tglagen][$jns]."'");
						$tab3.="<tr class=rowcontent style=vertical-align:top>";
						$tab3.="<td align=left style=font-style:italic;background-color:#E3FFB1;color:blue;>".$jns."</td>";
						$tab3.="<td align=left>".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left>".nl2br($ketx[$tglagen][$jns])."</td>";
						if($stsc[$tglagen][$jns]!=''){
							$color="";
							if($stsc[$tglagen][$jns]!=1){$color="style=color:red;";}
							$tab3.="<td align=left ".$color.">Konfirmasi : ".$stsc[$tglagen][$jns]."<br>Oleh : ".$optnm[$upd[$tglagen][$jns]]."<br>Tanggal : ".tanggalnormal($tglupd[$tglagen][$jns])."</td>";
						}else{							
							$tab3.="<td></td>";
						}
						$tab3.="</tr>";
					}
					
					if($jns=='real'){
						$tab3.="<tr class=rowcontent style=vertical-align:top>";
						$tab3.="<td align=left style=font-style:italic;background-color:green;color:white;>".$jns."</td>";
						$tab3.="<td align=left>".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left>".nl2br($ketx[$tglagen][$jns])."</td>";
						$tab3.="<td align=left>".$koo[$tglagen][$jns]."</td>";
						$tab3.="</tr>";
					}
				}
				$tab3.="</tr>";
			}
			$tab3.="</tbody>";
			$tab3.="</table>";
			
			
		}#TUTUP IF JIKA ADA DATA MUNCULKAN
		
		
		# REAL
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and jenisbiaya='realkeg'";
		$res=fetchdata($str);
		if(count($res)>0){
			
			#FILE UPLOAD KEGIATAN
			if($jenis!='pdf'){				
				$tab4.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			if(!empty($res)){
				$tab4.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>File Upload (realisasi kegiatan)</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab4.="<td align=center width=20px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>File Type</td>";
				}
					$tab4.="<td align='center' style=font-weight:bold;>".$_SESSION['lang']['jenisbiaya']."</td>
						<td align='center' style=font-weight:bold;>Filename</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>Action</td>";
				}
				$tab4.="</tr>
				</thead>";
				$tab4.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab4.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					if($jenis=='html'){
					$tab4.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					}
					$nfile = $val['namafile'];
					$tab4.="<td style='text-align:left'>".ucwords(strtolower($optjns[$val['jenisbiaya']]))."</td>";
					$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
					if($jenis=='html'){						
						$tab4.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}
					$tab4."</tr>";
				}
			}
			
			
			$tab4.="</tbody>";
			$tab4.="</table>";
			
			
		}#TUTUP IF JIKA ADA DATA MUNCULKAN
		
			
		
		if($jenis=='pdf'){		
			$dompdf = new Dompdf();
			$dompdf->load_html($tab.$tab2.$tab3.$tab4);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
			
			$filepdf=checkPostGet('namafile','');
			if($filepdf!=''){
				if (file_exists($filepdf)){
					unlink($filepdf);
				}
				file_put_contents($filepdf, $dompdf->output());
			}else{				
				$dompdf->stream("perjalanan_dinas",array("Attachment"=>0));
			}
		}elseif($jenis=='excel'){
			$nop = "perjalanan_dinas.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			//$xls->addSheet("surat_perjalanan_dinas", $tab);
			$xls->addSheet("biaya_perjalanan_dinas", $tab2);
			$xls->addSheet("kegiatan_perjalanan_dinas", $tab3.$tab4);
			#$xls->addSheet("real_kegiatan_perjalanan_dinas", $tab4);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab.$tab2.$tab3.$tab4;
		}
	break;
	case'form_batal';
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
					<td width=5px>:</td>
					<td id=notran_batal>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td width=5px valign=top>:</td>
					<td><textarea rows=3 maxlength='1024' id=ketbatal type='text' onkeypress='return tanpa_kutip(event)' style='width:205px;'></textarea></td>
				</tr>
				<tr class=rowcontent>
					<td></td>
					<td></td>
					<td align=left><button class=mybutton onclick=batalkan()>" . $_SESSION['lang']['save'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
	case'batalkan':
		#insert ht dulu
		try {
		$owlPDO->beginTransaction();
		
		if($notransaksi==''){
			throw new PDOException("Notransaksi wajib diisi.");
		}
		if($param['keterangan']==''){
			throw new PDOException("Keterangan wajib diisi.");
		}
		
		
		$data = array();
		$data = array(
			'keteranganbatal'=> $param['keterangan'],
			'statuspengajuan'=> '3',
			'updateby'       => $_SESSION['standard']['userid']
		);
		
		$where = "notransaksi='".$notransaksi."'";
		$str = updateQuery($dbname,'sdm_pjdinasht',$data,$where);
		$owlPDO->exec($str);

		$str="delete from ".$dbname.".approval where status='0' and notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
	
	case'form_ajukan';
		$error="";
		
		$wh="a.karyawanid!='".$_SESSION['standard']['userid']."'";
		
		$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
		// $depapah=$_SESSION['empl']['bagian'];
		// $countApp = getCountApproval($kodeapproval, $kodeorg[$notransaksi], $depapah);
		// tadinya di bawah ini: diganti di atas, karena pengajian pak Alfin ada setup per Dept-nya
		// ga jadi pake yg di ataslah, takutnya efek ke mana2... update setupnya aja, samain untuk yang tanpa dept
		$countApp = getCountApproval($kodeapproval, $kodeorg[$notransaksi]);
		$hide="";
		if(($countApp-1)==0 and $kodeapproval=='PJDBOD'){
			#ini BOD
			$hide="hidden";
		}
		
		
		
		##GET KARYAWAN
		$str="select karyawanid from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$karydinas=$res[0]['karyawanid'];
		$optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$karydinas."'");
		$departemen=$optdepartmen[$karydinas];
		
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and departemen='".$departemen."' and level ='1'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$wheredept="";
		if($perdepartemen>0 and $kodeapproval!='PJDNSTF'){
			$wheredept.=" and a.departemen='".$departemen."'";
		}else{
			$wheredept.=" and a.departemen=''";
		}

		$kodegol=makeOption($dbname,'datakaryawan','karyawanid,kodegolongan',"karyawanid='".$karydinas."'");
		$optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');

		##CEK PER GOLONGAN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg[$notransaksi]."' and jenispersetujuan='".$kodeapproval."' and golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."'  and level='1'";
		$res=fetchdata($str);
		$pergolongan=$res[0]['kodeunit'];
		#$where="";
		if($pergolongan>0 and $kodeapproval!='PJDNSTF'){
			$wheregol.=" and a.golongan='".substr($optgol[$kodegol[$karydinas]],0,1)."' ";
		}else{
			$wheregol.=" and a.golongan=''";
		}
		
		/* $str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 1=1 
			and a.jenispersetujuan='".$kodeapproval."' ".$level." and a.kodeunit='".$kodeorg[$notransaksi]."' ".$where." order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		} */
		#ambil level
		$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."' and a.karyawaniduser='".$karydinas."' ".$wheredept." ".$wheregol."";
		$res=fetchData($str);
		if(is_null($res[0]['level'])){
			$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept." ".$wheregol."";
			$res=fetchData($str);
			if(is_null($res[0]['level'])){
				$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept."";
				$res=fetchData($str);
				if(is_null($res[0]['level'])){
					$str="select max(level) as level from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where jenispersetujuan='".$kodeapproval."' and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser=''";
					$res=fetchData($str);
				}
			}
		}
		
		if($jenis=='pengajuan'){
			$level="and a.level='1'";
		}else{
			#level terakhir pastikan HRD
			//echo $str; 
			foreach($res as $bar){
				$levelmax=$bar['level'];
			}
			$level="and a.level='".$levelmax."'";
		}
		
		
		$str="select distinct a.karyawanid,namakaryawan,lokasitugas  from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		where jenispersetujuan='".$kodeapproval."' ".$level." and kodeunit='".$kodeorg[$notransaksi]."' and a.karyawaniduser='".$karydinas."' ".$wheredept." ".$wheregol."";
		$res=fetchData($str);
		if(count($res)==0){
			$str="select distinct a.karyawanid,namakaryawan,lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			where jenispersetujuan='".$kodeapproval."' ".$level." and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept." ".$wheregol."";
			$res=fetchData($str);
			if(count($res)==0){
				$str="select distinct a.karyawanid,namakaryawan,lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
				where jenispersetujuan='".$kodeapproval."' ".$level." and kodeunit='".$kodeorg[$notransaksi]."'  and a.karyawaniduser='' ".$wheredept."";
				$res=fetchData($str);
				if(count($res)==0){
					$str="select distinct a.karyawanid,namakaryawan,lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where jenispersetujuan='".$kodeapproval."' ".$level." and kodeunit='".$kodeorg[$notransaksi]."' and a.departemen='".$departemen."'";
					$res=fetchData($str);
					if(count($res)==0){
						$str="select distinct a.karyawanid,namakaryawan,lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
						where jenispersetujuan='".$kodeapproval."' ".$level." and kodeunit='".$kodeorg[$notransaksi]."' and a.departemen=''";
						$res=fetchData($str);
						if(count($res)==0){
							echo "<table>";
								echo "<tr><td>Approval untuk unit</td><td>: ".$kodeorg[$notransaksi]."</td></tr>";
								echo "<tr><td>Jenis Persetujuan</td><td>: ".$kodeapproval."</td></tr>";
							echo "</table>";
							$karidx=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$notransaksi."'");
							$jabatanx=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karidx[$notransaksi]."'");
							$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi ='HR' and kodeparameter ='PJDLV'";
							$pjdxz=fetchdata($str);
							$arrdatax=explode(',',$pjdxz[0]['nilai']);

							$levelbos=array();
							for ($i=0; $i < count($arrdatax); $i++) { 
								$levelbos[$arrdatax[$i]]=$arrdatax[$i];
							}

							if($levelbos[$jabatanx[$karidx[$notransaksi]]]){
								
							}else{
								exit("Warning : Setup persetujuan belum ada !");
							}
						}
					}
					
				}
			}
		}
		
		// echo $str;
		$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karidx[$notransaksi]."'");

		$optKry="";
		if(count($res)>1){
			$optKry = "<option value''>".$_SESSION['lang']['pilihdata']."</option>";
		}
		foreach($res as $bar){
			$optKry.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." [".$bar['lokasitugas']."]</option>";
		}
		if($levelbos[$jabatanx[$karidx[$notransaksi]]]){
			$optKry.="<option value='".$karidx[$notransaksi]."'>".$nmkar[$karidx[$notransaksi]]."</option>";					
		}
		$nmdept=makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemen."'");
		$optjns=makeOption($dbname,'setup_jenisapproval','jenis,nama');
		$tab = "<table cellspacing=1 border=0 width=100%>
					<input hidden id=kodeapprovalaju value='".$kodeapproval."'>
					<input hidden id=jenisaju value='".$jenis."'>
					<input hidden id=levelaju value='".$levelmax."'>
		
					<tr class=rowcontent>
						<td width=100px>".$_SESSION['lang']['notransaksi']."</td>
						<td width=5px>:</td>
						<td id=notran_aju>".$notransaksi."</td>
					</tr>
					
					<tr class=rowcontent>
						<td width=100px>".$_SESSION['lang']['jenis']."</td>
						<td width=5px>:</td>
						<td>".$optjns[$kodeapproval]."</td>
					</tr>
					<tr hidden class=rowcontent>
						<td width=100px>".$_SESSION['lang']['departemen']."</td>
						<td width=5px>:</td>
						<td>".$departemen." - ".$nmdept[$departemen]."</td>
					</tr>
					<tr class=rowcontent ".$hide.">
						<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
						<td width=5px>:</td>
						<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
					</tr>
					<tr class=rowcontent>
						<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
						<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
					</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
		switch($jenis){
			case'pengajuan':

				$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' and jenis = 'renc'";
				$res = fetchdata($str);
				$cekdata = count($res);

				if($cekdata <= 0){
					exit("Warning : Rencana kegiatan belum diisi ! ");
				}

				try {
				$owlPDO->beginTransaction();
				$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");


				#####untuk dapat jabatan bos2
				$karidx=makeOption($dbname,'sdm_pjdinasht','notransaksi,karyawanid',"notransaksi='".$notransaksi."'");
				$jabatanx=makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karidx[$notransaksi]."'");
				$str="select * from ".$dbname.".setup_parameterappl where kodeaplikasi ='HR' and kodeparameter ='PJDLV'";
				$pjdxz=fetchdata($str);
				$arrdatax=explode(',',$pjdxz[0]['nilai']);

				$levelbos=array();
				for ($i=0; $i < count($arrdatax); $i++) { 
					$levelbos[$arrdatax[$i]]=$arrdatax[$i];
				}

				$countApp = (getCountApproval($kodeapproval, $kodeorg[$notransaksi]));
				// echo $levelbos[$jabatanx[$karidx[$notransaksi]]];

				// exit('Error');
				if($levelbos[$jabatanx[$karidx[$notransaksi]]]){
					######biar masuk ke non approval untuk bosss
					$countApp=0;
				}
				############################

				if($countApp>0){
					#karyawan biasa
					if($kepada=='' or $notransaksi==''){
						throw new PDOException('Isikan nama penyetuju.');
					}
					
					# cari dulu apakah sudah pernah di ajukan sebelumnya
					$tglhi = date("Ymd");
					
					$str="select * from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						if($bar['notransaksi']!=''){
							# jika ada pindahkan ke table ini
							$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
							values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
							$owlPDO->exec($str);
						}
					}
					
					#kemudian setelah di pindah, hapus persetujuan lama
					$str="delete from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."'";
					$owlPDO->exec($str);
					
					# update flag menjadi 9
					$str = "update " . $dbname . ".sdm_pjdinasht set statuspengajuan='9', postingtime='" . date('Y-m-d') . "', "."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'"; 
					#exit("error".$str);
					$owlPDO->exec($str);

					# insert ke table approval
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
							`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
							 values ('','".$notransaksi."','".$kodeapproval."','1','" . $kepada."','0','','','')";
					$owlPDO->exec($str);
					
				}else{
					#big boss
					# update flag menjadi 1
					$str = "update " . $dbname . ".sdm_pjdinasht set statuspengajuan='1', postingtime='" . date('Y-m-d') . "', "."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'"; 
					#exit("error".$str);
					$owlPDO->exec($str);
					
					# insert ke table approval
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
							`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
							 values ('','".$notransaksi."','".$kodeapproval."','1','" .$_SESSION['standard']['userid']."','1','','','')";
					$owlPDO->exec($str);
				}
				
				
					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
				
			break;
			case'pertanggung':
				try {

					$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' and jenis = 'real'";
					$res = fetchdata($str);
					$cekdata = count($res);

					if($cekdata <= 0){
						exit("Warning : Realisasi kegiatan pengajuan belum diisi ! ");
					}

					$owlPDO->beginTransaction();
					
					if($kepada=='' or $notransaksi==''){
						throw new PDOException('Isikan nama karyawan.');
					}
					$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' order by tanggal asc";
					$res=fetchdata($str);
					if(count($res)==0){
						$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='0' and sumber='0' order by tanggal asc";
						$res=fetchdata($str);
						if(count($res)>0){
							throw new PDOException('Nilai pertanggung jawaban belum ada.');
						}
					}
					$kodeorg=makeOption($dbname,'sdm_pjdinasht','notransaksi,kodeorg',"notransaksi='".$notransaksi."'");
					$countApp = $param['level'];
					
					# cari dulu apakah sudah pernah di ajukan sebelumnya
					$tglhi = date("Ymd");
					
					$str="select * from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."' and keterangan='pertanggung'";
					$res = fetchdata($str);
					foreach($res as $bar){
						if($bar['notransaksi']!=''){
							# jika ada pindahkan ke table ini
							$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
							values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
							$owlPDO->exec($str);
						}
					}
					
					#kemudian setelah di pindah, hapus persetujuan lama
					$str="delete from ".$dbname.".approval where jenispersetujuan='".$kodeapproval."' and notransaksi='".$notransaksi."' and keterangan='pertanggung'";
					$owlPDO->exec($str);
					
					# update flag menjadi 9
					$str = "update " . $dbname . ".sdm_pjdinasht set statusrealisasi='9', namahrd='".$kepada."' where notransaksi = '" . $notransaksi . "'"; 
					#exit("error".$str);
					$owlPDO->exec($str);

					# insert ke table approval
					$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
							`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
							values ('','".$notransaksi."','".$kodeapproval."','".$countApp."','" . $kepada."','0','','pertanggung','')";
					$owlPDO->exec($str);

					$owlPDO->commit();
				} catch (PDOException $e) {
					$owlPDO->rollback();
					echo "Error, " . addslashes($e->getMessage());
					die();
				}
			break;
		}
	break;
	
	case'previewdata2':
		$tab	="";
		$tab2	="";
		$tab3	="";
		$tab4	="";
		$nmorg 	= makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)<=4");
		$nmjab 	= makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$nmgol 	= makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
		$nmdep 	= makeOption($dbname,'sdm_5departemen','kode,nama');
		$nmlev 	= makeOption($dbname,'sdm_5levelkaryawan','kode,nama');

		// $jnsapp = makeOption($dbname,'sdm_5levelpjdinas','level,kodeapproval');
		$nmreg = makeOption($dbname,'sdm_5regionalpjd','regional,nama');
		$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi = '".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $bar){
			$strx="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['karyawanid']."'";
			$resx=fetchData($strx);
			foreach($resx as $barx){
				$nmkar[$barx['karyawanid']]=$barx['namakaryawan'];
				$nkkar[$barx['karyawanid']]=$barx['nik'];
				$jabkar[$barx['karyawanid']]=$nmjab[$barx['kodejabatan']];
				$golkar[$barx['karyawanid']]=$nmgol[$barx['kodegolongan']];
				$depkar[$barx['karyawanid']]=$nmdep[$barx['bagian']];
				$levkar[$barx['karyawanid']]=$nmlev[$barx['levelkaryawan']];
				$tipekar[$barx['karyawanid']]=$barx['tipekaryawan'];
			}
			
			$statuspengajuan= $bar['statuspengajuan'];
			$batal  =$bar['keteranganbatal'];
			$kodeorg= $bar['kodeorg'];
			$karyid = $bar['karyawanid'];
			$ket    = $bar['keterangan'];
			if($bar['pttujuan']!='OTH'){
				$pttujuan    = $nmorg[$bar['pttujuan']];
				$unittujuan  = $nmorg[$bar['unittujuan']];
			}else{
				$pttujuan    = $bar['pttujuan'];
				$unittujuan  = $bar['unittujuan'];
			}
			if($bar['tiket']=='1'){
				$tiket="Ya";
			}else{
				$tiket="Tidak";
			}
			
			$regiontujuan= $nmreg[$bar['regiontujuan']];
			$tgldr       = tanggalnormal($bar['tgldinasdari']);
			$tgldrreal   = tanggalnormal($bar['tgldinasdarireal']);
			$tglsd       = tanggalnormal($bar['tgldinassampai']);
			$tglsdreal   = tanggalnormal($bar['tgldinassampaireal']);
			$namakary    = $nmkar[$bar['karyawanid']];
			$nikkar      = $nkkar[$bar['karyawanid']];
			$jabatan     = $jabkar[$bar['karyawanid']];
			$golongan    = $golkar[$bar['karyawanid']];
			$dept        = $depkar[$bar['karyawanid']];
			$levelkar    = $levkar[$bar['karyawanid']];

			$level       = $nmlev[$bar['level']];
			$kodeapproval= $jnsapp[$bar['level']];
		}

		#cari noreff uang muka
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2='umpjd#".$notransaksi."' and nik='".$karyid."'";
		$resa = fetchdata($stra);
		$umdibayarkan=0;$umnoreff="";
		foreach($resa as $bara){				
			$umdibayarkan+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$umnoreff=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff uang bayar oleh pt
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'realpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$realpt=0;$realptnoreff="";
		foreach($resa as $bara){				
			$realpt+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$realptnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff ptj
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'claimpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$claimbayar=0;$claimnoreff="";
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'pjdbatal#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){				
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}

		#Sum total jumlah rupiah di pengajuan
		$stra="select sum(jumlah) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."'";
		$resa = fetchdata($stra);
		foreach($resa as $bara){				
			$rupiahumpjd=$bara['jumlah'];
		}
		
		$stra = "select * from ".$dbname.".sdm_pjdinasdt2 
				where notransaksi='".$notransaksi."'  
				order by tanggal";
		$resa = fetchdata($stra);

		$dataByTanggal = [];

		foreach($resa as $bara){				
			$dataByTanggal[] = [
				'tanggal'    => $bara['tanggal'],
				'tanggal2'   => $bara['tanggal2'],
				'jenis'      => $bara['jenis'],
				'keterangan' => $bara['keterangan'],
				'lokasi'     => $bara['lokasi'],
				'koordinasidengan' => $bara['koordinasidengan']
			];
		}

	
		
	
		$arrHead = setheadreport(getindukPT($kodeorg));
		$waktuawal = tanggalsystemn($tgldrreal);
		$waktuakhir = tanggalsystemn($tglsdreal);
		$tgl1 = new DateTime($waktuawal);
		$tgl2 = new DateTime($waktuakhir);
		$selisih = $tgl1->diff($tgl2)->days + 1;
		$diff = (strtotime($waktuakhir)-strtotime($waktuawal));
		$hari = floor($diff/(60*60*24));
			
		$tab.="
			<head>
			<meta charset='UTF-8'>
			<title>Formulir Rencana Perjalanan Dinas</title>
			<style>
				body {
					font-family: Times New Roman;
					font-size: 12px;
				}
				h2, h3 {
					text-align: center;
					margin: 0;
					padding: 0;
				}
				.judul {
					margin-bottom: 20px;
				}
				table {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 10px;
				}
				table td, table th {
					padding: 4px;
					vertical-align: top;
				}
				.border td, .border th {
					border: 1px solid #000;
				}
				.section-title {
					font-weight: bold;
					margin-top: 10px;
				}
				.small {
					font-size: 10px;
				}
				.signature {
					height: 40px;
					text-align: center;
					vertical-align: bottom;
				}
			</style>
			</head>
			<body>

			<div class='judul'>
			<table cellspacing=0 border=1 width=100% align=center style=\"font-size:12px;\">
				<tr>
					<td align=center width=18%><img src='".$arrHead['logo']."' height='60' />
					</td>
					<td align=center style='font-size:35px;font-weight:bold;vertical-align:middle'>
						DMA GROUP
					</td>
				</tr>
				<tr>
					<td colspan=2><h2>FORMULIR RENCANA PERJALANAN DINAS LUAR KOTA</h2></td>
				</tr>
			</table>
			</div>

			<table cellspacing=0 border=0 width=100% style='font-family: Arial, Helvetica, sans-serif;'>
				<tr>
					<td width=50% style='border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;padding-right:13px'>
						<table cellspacing=0 cellpadding=5 border=0 align=center style=\"font-size:12px;\">
							<tr>
								<td style='width:89px'>Nama Karyawan</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$namakary." </td>
							</tr>
							<tr>
								<td>NIK</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$nikkar." </td>
							</tr>
							<tr>
								<td>Jabatan</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$jabatan." </td>
							</tr>
							<tr>
								<td>Divisi/Bagian</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$dept." </td>
							</tr>
						</table>
					</td>
					<td width=50% style='border:1px solid black;'>
						<table cellspacing=0 border=0 align=center style=\"font-size:12px;\">
							<tr>
								<td style='width:139px'>Tanggal Permintaan</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".tanggalnormal(substr($bar['createtime'],0,10))." </td></tr>
							<tr>
								<td>Tanggal Perjalanan Dinas</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$tgldr." s.d ".$tglsd."</td></tr>
							<tr>
								<td>Lama Perjalanan</td>
								<td style='width:1px'>:</td>
								<td style='border-bottom:1px solid black'> ".$selisih." Hari</td></tr>
							<tr>
								<td>Anggaran Biaya (*)</td>
								<td style='width:1px'>:</td>
								<td> RAT / Non RAT</td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width=50% style='border-bottom:1px solid black'>
						<table>
							<tr>
								<td>Uang Saku</td>
								<td>:</td>
								<td>…… Hari x Rp ……………
							</tr>
							<tr>
								<td>Uang Makan Bandara</td>
								<td>:</td>
								<td></td>
							</tr>
							<tr>
								<td>Uang Makan</td>
								<td>:</td>
								<td>…… Kali x Rp ……………</td>
							</tr>
							<tr>
								<td>Uang Laundry</td>
								<td>:</td>
								<td>…… Hari x Rp ……………</td>
							</tr>
							<tr>
								<td>Cash Advance (**)</td>
								<td>:</td>
								<td></td>
							</tr>
							<tr>
								<td><b>Total</b></td>
								<td>:</td>
								<td></td>
							</tr>
							<tr>
								<td colspan=3><b><i>Transportasi</i></b> (Dari dan menuju Bandara)</td>
							</tr>
							<tr>
								<td>Estimasi Biaya Perjalanan</td>
								<td>:</td>
								<td></td>
							</tr>
						</table>
					</td>
					<td width=50% style='border-right:1px solid black;border-bottom:1px solid black'>
						<table>
							<tr>
								<td style='width:51%'>Rp. ………………………</td>
								<td></td>
							</tr>
							<tr>
								<td>Rp. ………………………</td>
								<td></td>
							</tr>
							<tr>
								<td>Rp. ………………………</td>
								<td><i style='font-size:10px'> (untuk tujuan Jakarta & Surabaya)</i></td>
							</tr>
							<tr>
								<td>Rp. ………………………</td>
								<td><i style='font-size:10px'> (untuk tujuan Jakarta & Surabaya)</i></td>
							</tr>
							
							<tr>
								<td style='border-bottom:1px solid black'>Rp. ………………………</td>
								<td><i style='font-size:10px'> (apabila dibutuhkan)</i></td>
							</tr>
							<tr>
								<td><b>Rp. ".number_format($rupiahumpjd,2)."</b></td>
								<td></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>Rp. ………………………</td>
								<td><i style='font-size:10px'>  (diberikan dalam bentuk E-Voucher)</i></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='border:1px solid black;font-size:11px;font-weight:bold' colspan=6 align=center>DETAIL PERJALANAN DINAS</td>
				</tr>
				<tr>
					<td width=50% style='padding-left:-1px;padding-top:-1px;padding-bottom:-10.5px'>
						<table border=1>
							<tr>
								<th colspan='5' align=center style='border:1px solid black;'>TRANSPORTASI</th>
							</tr>
							<tr>
								<th align=center>No</th>
								<th align=center>Keterangan</th>
								<th align=center>Pergi</th>
								<th align=center>Pulang</th>
								<th align=center>Transit</th>
							</tr>
							<tr>
								<td align=center>1.</td>
								<td>Tujuan</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align=center>2.</td>
								<td>Tanggal</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align=center>3.</td>
								<td>Jenis Kendaraan</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align=center>4.</td>
								<td>Waktu</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align=center>5.</td>
								<td>Harga Tiket</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td align=center>6.</td>
								<td>Total Harga</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</table>
					</td>
					<td width=50% style='padding-right:2px;padding-top:-1px;padding-bottom:-10.5px'>
						<table border=1>
							<tr>
								<th colspan='3' align=center>AKOMODASI</th>
							</tr>
							<tr>
								<th align=center style='width:3px'>No</th>
								<th align=center style='width:120px'>Keterangan</th>
								<th align=center>Detail</th>
							</tr>
							<tr>
								<td align=center>1.</td>
								<td>Lokasi Penginapan</td>
								<td></td>
							</tr>
							<tr>
								<td align=center>2.</td>
								<td>Durasi Menginap</td>
								<td></td>
							</tr>
							<tr>
								<td align=center>3.</td>
								<td>Harga Penginapan</td>
								<td></td>
							</tr>
							<tr>
								<td align=center>4.</td>
								<td>Total Biaya</td>
								<td></td>
							</tr>
							<tr>
								<td style='vertical-align:top;text-align:left;height:38px' colspan=3>Catatan :</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='font-size:11px;font-weight:bold;background-color:#eaeaea' colspan=6>*Diisi oleh pemesan tiket </td>
				</tr>
				<tr>
					<td width=50% style='border-bottom:1px solid black;background-color:#eaeaea;padding-bottom:-2.5px'>
						<table>
							<tr>
								<td colspan=3 align=center><b>TIKET PERGI</b></td>
							</tr>
							<tr>
								<td style='width:5px'>1.</td>	
								<td style='width:115px'>No Booking</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
							<tr>
								<td>2.</td>	
								<td>Maskapai</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
							<tr>
								<td>3.</td>	
								<td>Jumlah Pembayaran</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
						</table>
					</td>
					<td width=50% style='border-bottom:1px solid black;background-color:#eaeaea;padding-bottom:-2.5px'>
						<table>
							<tr>
								<td colspan=3 align=center><b>TIKET PULANG</b></td>
							</tr>
							<tr>
								<td style='width:5px'>1.</td>	
								<td style='width:115px'>No Booking</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
							<tr>
								<td>2.</td>	
								<td>Maskapai</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
							<tr>
								<td>3.</td>	
								<td>Jumlah Pembayaran</td>
								<td>: &nbsp;&nbsp;_______________________</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style='border-left:1px solid black;border-right:1px solid black;font-size:11px;font-weight:bold;background-color:#eaeaea;height:35px;padding-top:3px' colspan=6><b>Catatan:</b></td>
				</tr>
				<tr>
					<td colspan=6 style='padding-left:0px;padding-right:0px;padding-top:-2px'>
						<table class='border'>
							<tr>
								<th align=center><b>Diajukan Oleh</b></th>
								<th align=center><b>Atasan Langsung</b></th>
								<th align=center><b>Personalia/HCBP</b></th>
								<th align=center><b>Keuangan</b></th>
							</tr>
							<tr>
								<td class='signature'></td>
								<td class='signature'></td>
								<td class='signature'></td>
								<td class='signature'></td>
							</tr>
							<tr>
								<td align=center><b>".getNamaKaryawan($bar['updateby'])."</b></td>
								<td align=center><b></b></td>
								<td align=center><b></b></td>
								<td align=center><b></b></td>
							</tr>
							<tr>
								<td style='font-size:8.5px;'>Jabatan</td>
								<td style='font-size:8.5px;'>Jabatan</td>
								<td style='font-size:8.5px;'>Jabatan</td>
								<td style='font-size:8.5px;'>Jabatan</td>
							</tr>
							<tr>
								<td style='font-size:8.5px;'>Tanggal :</td>
								<td style='font-size:8.5px;'>Tanggal :</td>
								<td style='font-size:8.5px;'>Tanggal :</td>
								<td style='font-size:8.5px;'>Tanggal :</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<p class='small'>
			<b>Note:<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(*) Dipilih sesuai dengan rencana kerja masing-masing departemen<br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(**) Akan direalisasikan setelah kembali dari perjalanan dinas
			</p></b>";
		
		
		// if($databyy){//comment dulu ya biar tampil semua
			if($jenis!='pdf'){				
				$tab2.="<br>";
			}
			$tab2.="<div style='page-break-before: always;'></div>";
			$tab2.="
				<table border=0 style='font-family: Arial, Helvetica, sans-serif'>
					<tr>
						<td align=center width=18%><img src='".$arrHead['logo']."' height='60' width=100/>
						</td>
						<td align=center style='font-size:10.5px;font-weight:bold;vertical-align:middle;padding-right:140px'>FORM PROGRAM KERJA PERJALANAN DINAS</td>
					</tr>
				</table>
				<table border=0 style='font-family: Arial, Helvetica, sans-serif;font-size:9px'>
					<tr>
						<td width=50%>
							<table>
								<tr>
									<td style='width:90px'>Nama Karyawan / NIK</td>
									<td style='border-bottom:1px solid black'>: ".$namakary."</td>
								</tr>
								<tr>
									<td>Divisi / Bagian</td>
									<td style='border-bottom:1px solid black'>: ".$dept."</td>
								</tr>
								<tr>
									<td>Posisi / Jabatan</td>
									<td style='border-bottom:1px solid black'>: ".$jabatan."</td>
								</tr>
							</table>
						</td>
						<td width=50%>
							<table>
								<tr>
									<td style='width:75px'>Tanggal</td>
									<td style='border-bottom:1px solid black'>: ".tanggalnormal(substr($bar['createtime'],0,10))."</td>
								</tr>
								<tr>
									<td>Kota Tujuan</td>
									<td style='border-bottom:1px solid black'>: ".$regiontujuan."</td>
								</tr>
								<tr>
									<td>Lama Dinas (hari)</td>
									<td style='border-bottom:1px solid black'>: ".$selisih." Hari</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

				<table class='border'>
					<thead>
						<tr>
							<th style='width:2%;' align=center>No.</th>
							<th style='width:10%;' align=center>Hari</th>
							<th style='width:15%;' align=center>Tanggal</th>
							<th style='width:15%;' align=center>Waktu</th>
							<th style='width:25%;' align=center>Peserta</th>
							<th style='width:28%;' align=center>Program</th>
							<th style='width:15%;' align=center>PIC</th>
						</tr>
					</thead>
					<tbody>";
					$no = 0;
					foreach ($dataByTanggal as $row){
						$no++;
						$tab2 .= "
							<tr>
								<td align=center>".$no."</td>
								<td>".hari($row['tanggal'],"ID")."</td>
								<td>".tanggalbulan($row['tanggal'])."</td>
								<td></td>
								<td></td>
								<td>".$row['keterangan']."</td>
								<td>".$row['koordinasidengan']."</td>
							</tr>";
					}

					$tab2.="</tbody>
				</table>

				<p class='small'>
					Catatan: Jika kolom di atas kurang dipersilahkan untuk menggunakan kertas tambahan untuk pengisiannya.
				</p>

				<table>
					<tr>
						<td style='width:60%;'>Jakarta, ".tanggalnormal(substr($bar['createtime'],0,10))."</td>
						<td></td>
					</tr>
				</table>

				<table class='border'>
					<tr>
						<th align=center>Diajukan Oleh</th>
						<th align=center>Atasan Langsung</th>
						<th align=center>Mengetahui</th>
						<th align=center>Personalia/HCBP</th>
					</tr>
					<tr>
						<td style='height:70px'></td>
						<td style='height:70px'></td>
						<td style='height:70px'></td>
						<td style='height:70px'></td>
					</tr>
					<tr>
						<td align=center>".getNamaKaryawan($bar['updateby'])."</td>
						<td align=center></td>
						<td align=center></td>
						<td align=center></td>
					</tr>
				</table>

			";
		// }#tutup id $data
		
		
		#DATA AGENDA
		$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' order by tanggal asc";
		$res=fetchdata($str);
		// if(count($res)>0){comment dulu ya biar tampil semua
			#JIKA ADA DATA MUNCULKAN
			if($jenis!='pdf'){				
				$tab3.="<br>";
			}
			$tab3.="<div style='page-break-before: always;border:2px solid black;padding:20px'>";
				$tab3.="
				<table cellspacing=0 border=0 width=100% style='text-align:center'>
					<tr>
						<td style='font-weight:bold;font-family:sans-serif;padding-left:100px'>FORM LAPORAN PERJALANAN DINAS</td>
						<td align=center width=18%><img src='".$arrHead['logo']."' height='75' width=120/>
					</tr>
				</table>";
			$fontsize="10px";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=3 cellspacing=1 border=0 class=sortable width=100%";
			}
			$tab3.="
				<table style='font-weight:bold;padding-top:-30px' border=0>
					<tr>
						<td style='width:18%;'>Nama</td>
						<td>: ".$namakary."</td>
					</tr>
					<tr>
						<td>Jabatan</td>
						<td>: ".$jabatan."</td>
					</tr>
					<tr>
						<td>Divisi</td>
						<td>: ".$dept."</td>
					</tr>
					<tr>
						<td>Tujuan Kunjungan</td>
						<td>: ".$regiontujuan."</td>
					</tr>
					<tr>
						<td>Periode Kunjungan</td>
						<td>:  ".$tgldr." s.d ".$tglsd."</td>
					</tr>
					<tr>
						<td>Pemberi Tugas</td>
						<td>: </td>
					</tr>
				</table>

				<p>
					<table border=1>
						<tr>
							<td style='border:1px solid black'><b>Tujuan Kunjungan</b></td>
						</tr>
						<tr>
							<td style='height:50px'>".$bar['keterangan']."</td>
						</tr>
					</table>
				</p>
				<p>
					<table border=1>
						<tr>
							<td style='border:1px solid black'><b>Kesimpulan Kunjungan</b></td>
						</tr>
						<tr>
							<td style='height:70px'>&nbsp;</td>
						</tr>
					</table>
				</p>

				<table class='border'>
					<thead>
						<tr>
							<th style='width:40%;' align=center>Action Plan</th>
							<th style='width:20%;' align=center>PIC</th>
							<th style='width:20%;' align=center>Due Date To Follow</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td style='height:90px;'></td>
							<td style='height:90px;'></td>
							<td style='height:90px;'></td>
						</tr>
					</tbody>
				</table>

				<p>
					<table border=1>
						<tr>
							<td style='border:1px solid black'>Catatan Atasan Langsung :</td>
						</tr>
						<tr>
							<td style='height:70px'>&nbsp;</td>
						</tr>
					</table>
				</p>
				<p>
					<table>
						<tr>
							<td style='width:50%; text-align:center;border:1px solid black'>Pelaksana Tugas,</td>
							<td style='width:50%; text-align:center;border:1px solid black'>Pemberi Tugas,</td>
						</tr>
						<tr>
							<td style='height:40px;border-left:1px solid black;border-right:1px solid black'></td>
							<td style='height:40px;border-left:1px solid black;border-right:1px solid black'></td>
						</tr>
						<tr>
							<td style='border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black'>Nama<br>Tanggal</td>
							<td style='border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black'>Nama<br>Tanggal</td>
						</tr>
					</table>
				</p>
			</div>";
			
			
		// }#TUTUP IF JIKA ADA DATA MUNCULKAN
		
		
		# REAL
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and jenisbiaya='realkeg'";
		$res=fetchdata($str);
		// if(count($res)>0){comment dulu ya biar tampil semua
			
			#FILE UPLOAD KEGIATAN
			if($jenis!='pdf'){				
				$tab4.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			if(!empty($res)){
				$tab4.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>File Upload (realisasi kegiatan)</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab4.="<td align=center width=20px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>File Type</td>";
				}
					$tab4.="<td align='center' style=font-weight:bold;>".$_SESSION['lang']['jenisbiaya']."</td>
						<td align='center' style=font-weight:bold;>Filename</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>Action</td>";
				}
				$tab4.="</tr>
				</thead>";
				$tab4.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab4.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					if($jenis=='html'){
					$tab4.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					}
					$nfile = $val['namafile'];
					$tab4.="<td style='text-align:left'>".ucwords(strtolower($optjns[$val['jenisbiaya']]))."</td>";
					$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
					if($jenis=='html'){						
						$tab4.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}
					$tab4."</tr>";
				}
			}
			
			
			$tab4.="</tbody>";
			$tab4.="</table>";
			
			
		// }#TUTUP IF JIKA ADA DATA MUNCULKAN
		
			
		
		if($jenis=='pdf'){		
			$dompdf = new Dompdf();
			$dompdf->load_html($tab.$tab2.$tab3.$tab4);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
			
			$filepdf=checkPostGet('namafile','');
			if($filepdf!=''){
				if (file_exists($filepdf)){
					unlink($filepdf);
				}
				file_put_contents($filepdf, $dompdf->output());
			}else{				
				$dompdf->stream("perjalanan_dinas",array("Attachment"=>0));
			}
		}elseif($jenis=='excel'){
			$nop = "perjalanan_dinas.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			//$xls->addSheet("surat_perjalanan_dinas", $tab);
			$xls->addSheet("biaya_perjalanan_dinas", $tab2);
			$xls->addSheet("kegiatan_perjalanan_dinas", $tab3.$tab4);
			#$xls->addSheet("real_kegiatan_perjalanan_dinas", $tab4);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab.$tab2.$tab3.$tab4;
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

function like(string $haystack, string $needle, bool $ai = true, bool $ci = true): bool{
    $needle = preg_quote($needle, '/');

    $tokens = [];

    $needleLength = strlen($needle);
    for ($i = 0; $i < $needleLength;) {
        if ($needle[$i] === '\\') {
            $i += 2;
            if ($i < $needleLength) {
                if ($needle[$i] === '\\') {
                    $tokens[] = '\\\\';
                    $i += 2;
                } else {
                    $tokens[] = $needle[$i];
                    ++$i;
                }
            } else {
                $tokens[] = '\\\\';
            }
        } else {
            switch ($needle[$i]) {
                case '_':
                    $tokens[] = '.';
                    break;
                case '%':
                    $tokens[] = '.*';
                    break;
                default:
                    $tokens[] = $needle[$i];
                    break;
            }
            ++$i;
        }
    }

    return preg_match('/^' . implode($tokens) . '$/u' . ($ci ? 'i' : ''), $haystack) === 1;
}

?>

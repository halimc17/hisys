<?php
/** Author : Atwal */
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');

function valdefinition($post){
	$result = "";
	if(isset($_POST[$post])){
		$result	= $_POST[$post];
	}
	return $result;
}
function statusNumberToStr($numb){
	$result = "";
	switch ($numb){
		case 0:
			$result = '-';
		break;
		case 1:
			$result = '<img src="images/10.png">';
		break;
		case 2:
			$result = 'Dikoreksi';
		break;
		case 3:
			$result = '<font color="red">Ditolak</font>';
		break;
	}
	return $result;
}
function getValueAppv($data,$id){
	$result = 0;
	if(count($data) > 0){
		for($i=0; $i<count($data); $i++){
			if($data[$i]['level'] == $id){
				$result = $data[$i]['status'];
			}
		}
	}
	return $result;
}
$proses = "";
if(isset($_GET['proses'])){
	$proses	= $_GET['proses'];
}
$hal = 1;
if(isset($_GET['hal'])){
	$hal	= $_GET['hal'];
}
$result['err'] = "false";
$result['redirect'] = "false";


$notransaksi		= valdefinition('notransaksi');
$kodeorg			= $_SESSION['empl']['lokasitugas'];
$jenisprogram		= valdefinition('jenisprogram');
$namaprogram		= valdefinition('namaprogram');
$tujuanprogram		= valdefinition('tujuanprogram');
$penyelenggara		= valdefinition('penyelenggara');
$karyawan			= valdefinition('karyawan');//array
$namabiaya			= valdefinition('namabiaya');//array
$biaya				= valdefinition('biaya');//array
$postapprovemen		= valdefinition('approvemen');//array
$postlevel			= valdefinition('level');//array
$tglskrng			= date("Y-m-d H:i:s");
$slaveQuery			= "";
switch($proses){
	default:
		OPEN_BOX('','<span class=judul></span>');
		
		$limit = 20;//-Paging-
		$halaman_aktif = $hal; //-Paging-Phalaman saat ini
		$p = new Paging; // -Paging- Class paging
		$posisi = $p->cariPosisi($limit,$halaman_aktif);// -Paging- Posisi Data
		$where = "where a.createby = '".$_SESSION['standard']['userid']."'";
		
		$jmlDt = "select a.notransaksi from " . $dbname . ".sdm_pengajuantraining a ".$where;
		$rjml = fetchData($jmlDt);
		$jmldata = count($rjml);
		
		$sSelect = "select a.*,b.jenistraining as namajenistraining from " . $dbname . ".sdm_pengajuantraining a
		left join sdm_5jenistraining b on a.jenistraining = b.kodetraining
		".$where." LIMIT $posisi,$limit ";
		$res = fetchData($sSelect);
		
		$jml = $p->jumlahHalaman($jmldata,$limit);//-Paging- jumlah data
		
		
	?>
	<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo getMenu('sdm_programtraining'); ?></b></legend>
		<table class="sortable" cellspacing="1" style="width:100%" border="0">
			<thead>
				<tr>
					<th><?php echo $_SESSION['lang']['notransaksi']; ?></th>
					<th><?php echo $_SESSION['lang']['kodeorg']; ?></th>
					<th><?php echo $_SESSION['lang']['jenistraining']; ?></th>
					<th><?php echo $_SESSION['lang']['namatraining']; ?></th>
					<th><?php echo $_SESSION['lang']['tujuantraining']; ?></th>
					<th><?php echo $_SESSION['lang']['penyelengara']; ?></th>
					<th><?php echo $_SESSION['lang']['status']; ?></th>
					<th colspan="3">#</th>
				</tr>
			</thead>
			<tbody>
			<?php 
				if(count($res) > 0){
					for($i=0; $i<count($res); $i++){
						echo '<tr class="rowcontent">';
						echo '<td>'.$res[$i]['notransaksi'].'</td>';
						echo '<td>'.$res[$i]['kodeorg'].'</td>';
						echo '<td>'.$res[$i]['namajenistraining'].'</td>';
						echo '<td>'.$res[$i]['namaprogram'].'</td>';
						echo '<td>'.$res[$i]['tujuanprogram'].'</td>';
						echo '<td>'.$res[$i]['penyelenggara'].'</td>';
						if($res[$i]['persetujuan'] == "0"){
							echo '<td align="center">'.$_SESSION['lang']['baru'].'</td>';
						}elseif($res[$i]['persetujuan'] == "1"){
							echo '<td align="center"><img src="images/10.png"></td>';

						}elseif($res[$i]['persetujuan'] == "4"){
							echo '<td align="center">'.$_SESSION['lang']['proses'].'</td>';
						}elseif($res[$i]['persetujuan'] == "3"){
							echo '<td align="center"><font color="red">'.$_SESSION['lang']['tidak'].' '.$_SESSION['lang']['disetujui'].'</font></td>';

						}elseif($res[$i]['persetujuan'] == "2"){
							echo '<td align="center"><font color="grey">'.$_SESSION['lang']['koreksi'].'</font></td>';
						}
						echo '<td align="center">';
						if($res[$i]['persetujuan'] == "0" or $res[$i]['persetujuan'] == "2"){
							echo '<button type="button" class="mybutton" param="notransaksi" data="'.$res[$i]['notransaksi'].'" onclick="getSlave(\'diajukan\',this);">'.$_SESSION['lang']['diajukan'].'</button>';
						}else if($res[$i]['persetujuan'] == "4"){
							echo '<button type="button" class="mybutton" param="notransaksi" data="'.$res[$i]['notransaksi'].'" onclick="getSlave(\'cancelpengajuan\',this);">'.$_SESSION['lang']['koreksi'].'</button>';
						}else{
						}
						echo '</td>';
						
						if($res[$i]['persetujuan'] == "0" or $res[$i]['persetujuan'] == "2"){
							echo '<td align="center"><img src="images/application/application_edit.png" class="resicon" title="Edit" param="notransaksi" data="'.$res[$i]['notransaksi'].'" onclick="getSlave(\'showadd\',this);"></td>';
						}else{
							echo '<td align="center" style="opacity:0.5;filter: gray;-webkit-filter: grayscale(1);filter: grayscale(1);"><img src="images/application/application_edit.png" class="resicon" title="Edit" data="'.$res[$i]['notransaksi'].'"	></td>';
						}
						echo '<td align="center"><img src="images/skyblue/zoom.png" class="resicon" param="notransaksi" data="'.$res[$i]['notransaksi'].'" onclick="getSlave(\'getdetail\',this);"></td>';
						echo '</tr>';
					}
				}else{
					echo '<td colspan="8">No Data</td>';
				}
			?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="9" align="center">
					<?php 
						//insert Attribute action ex: href/onclick/onchange/etc..
						$buttonaction = array(
							'first' =>	'onclick="getSlave(\'&hal=1\');"',
							'prev' 	=> 	'onclick="getSlave(\'&hal='.($halaman_aktif-1).'\')"',
							'next' 	=> 	'onclick="getSlave(\'&hal='.($halaman_aktif+1).'\')"',
							'last' 	=> 	'onclick="getSlave(\'&hal='.($jml).'\')"',
							'pages'	=> 	'onchange="getSlave(\'&hal=\'+this.value);"'
						);
						echo $p->navHalaman($halaman_aktif,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
					?>
					</td>
				</tr>
			</tfoot>

		</table>
	</fieldset>
<?php
	CLOSE_BOX();
	break;
	case 'getdetail':


	//select pengajuan
	$sSelect = "select a.*,b.jenistraining as namajenistraining from " . $dbname . ".sdm_pengajuantraining a 
	left join sdm_5jenistraining b on a.jenistraining = b.kodetraining
	where a.notransaksi	= '".$notransaksi."' and a.createby = '".$_SESSION['standard']['userid']."' limit 1";
	$header = fetchData($sSelect);
	
	//role approve
	$sRoleApp = "select distinct(level) as level from " . $dbname . ".setup_approval
	where jenispersetujuan = 'PPT' and kodeunit = '".$_SESSION['empl']['lokasitugas']."' order by level ASC";
	$RApp = fetchData($sRoleApp);
	
	//data approve
	$sApproval = "select * from " . $dbname . ".approval
	where notransaksi = '".$notransaksi."' and jenispersetujuan = 'PPT' order by nourut";
	$apprv = fetchData($sApproval);
	
	
	OPEN_BOX('','<span class=judul></span>');
	
	?>
	<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo getMenu('sdm_programtraining'); ?></b></legend>
		<br/>
		<table cellspacing="1" border="0" style="width:100%">
			<tbody>
			<?php 
			$html = "";
				if(count($header) > 0){
					for($i=0; $i<count($header); $i++){
						$htmlpersetujuan = '<table class="sortable" cellspacing="1" border="0" style="float:right;"><thead>';
						$htmlpersetujuan .= '<tr><th>'.$_SESSION['lang']['persetujuan'].'</th>';
						$htmlpersetujuan .= '<th>'.$_SESSION['lang']['status'].'</th><tr>';
						$htmlpersetujuan .= '</thead>';
						$htmlpersetujuan .= '<tbody>';
					if(count($RApp) > 0){	
						for($ii=0; $ii<count($RApp); $ii++){
							$htmlpersetujuan .= '<tr class="rowcontent"><td align="center">Level '.$RApp[$ii]['level'].'</td>
							<td align="center">'.statusNumberToStr(getValueAppv($apprv,$RApp[$ii]['level'])).'</td></tr>';
						}
							//$htmlpersetujuan .= $persetujuan;
					}else{
						$htmlpersetujuan .= '<tr class="rowcontent"><td colspan="2">No Approvement</td></tr>';
					}
					
						$htmlpersetujuan .= '</tbody>';
						$htmlpersetujuan .= '</table>';
						
						$html .= '<tr>';
						$html .= '<td width="130">'.$_SESSION['lang']['jenistraining'].'</td>';
						$html .= '<td>: '.$header[$i]['namajenistraining'].'</td>';
						$html .= '<td rowspan="4" valign="top">'.$htmlpersetujuan.'</td>';
						$html .= '</tr>';
						
						$html .= '<tr>';
						$html .= '<td>'.$_SESSION['lang']['namatraining'].'</td>';
						$html .= '<td>: '.$header[$i]['namaprogram'].'</td>';
						$html .= '</tr>';
						
						$html .= '<tr>';
						$html .= '<td>'.$_SESSION['lang']['tujuantraining'].'</td>';
						$html .= '<td>: '.$header[$i]['tujuanprogram'].'</td>';
						$html .= '</tr>';
						
						$html .= '<tr>';
						$html .= '<td>'.$_SESSION['lang']['penyelengara'].'</td>';
						$html .= '<td>: '.$header[$i]['penyelenggara'].'</td>';
						$html .= '</tr>';
					}
					echo $html ;
				}else{
					echo '<td colspan="6">No Data</td>';
				}
			?>
			</tbody>
		</table>
	<br>
	<?php 
	$sSelect = "select b.namakaryawan,b.nik from " . $dbname . ".sdm_pengajuantraining_dt a 
	left join datakaryawan b on a.karyawanid = b.karyawanid
	where a.notransaksi	= '".$notransaksi."'";
	$datakaryawan = fetchData($sSelect);
	?>
		<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo $_SESSION['lang']['peserta']; ?></b></legend>
		<table class="sortable" cellspacing="1" style="width:100%" border="0">
			<thead>
				<tr>
					<th><?php echo $_SESSION['lang']['nik']; ?></th>
					<th><?php echo $_SESSION['lang']['namakaryawan']; ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
				if(count($datakaryawan) > 0){
					for($i=0; $i<count($datakaryawan); $i++){
						echo '<tr class="rowcontent">';
						echo '<td>'.$datakaryawan[$i]['nik'].'</td>';
						echo '<td>'.$datakaryawan[$i]['namakaryawan'].'</td>';
						echo '</tr>';
					}
				}else{
					echo '<td colspan="2">No Data</td>';
				}
			?>
			</tbody>
		</table>
	</fieldset>
	<?php 
	$sSelect = "select * from " . $dbname . ".sdm_pengajuantraining_biaya 
	where notransaksi	= '".$notransaksi."' order by nourut";
	$biaya = fetchData($sSelect);
	?>
		<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo $_SESSION['lang']['biaya']; ?></b></legend>
		<table class="sortable" cellspacing="1" style="width:100%" border="0">
			<thead>
				<tr>
					<th>#</th>
					<th><?php echo $_SESSION['lang']['jenisbiaya']; ?></th>
					<th><?php echo $_SESSION['lang']['biaya']; ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
				if(count($biaya) > 0){
					for($i=0; $i<count($biaya); $i++){
						echo '<tr class="rowcontent">';
						echo '<td>'.($i+1).'</td>';
						echo '<td>'.$biaya[$i]['namabiaya'].'</td>';
						echo '<td align="right">'.number_format($biaya[$i]['biaya'],2).'</td>';
						echo '</tr>';
					}
				}else{
					echo '<td colspan="2">No Data</td>';
				}
			?>
			</tbody>
		</table>
	</fieldset>
	</fieldset>
	<?php
	CLOSE_BOX();
	break;
	case 'showadd': 
	OPEN_BOX('','<span class=judul></span>');

		$optMyPt = makeOption($dbname,'organisasi','tipe,namaorganisasi',"induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe = 'PT'");
		$optJenisTraining = makeOption($dbname,'sdm_5jenistraining','kodetraining,jenistraining',"status='1'");
		$optnamakar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"tanggalkeluar = '0000-00-00' and tipekaryawan <> '4'");
		$datakaryawan 	= array();
		$dataBiaya 		= array();
		if($notransaksi != ""){
			$title = $notransaksi;
			$where = "where a.notransaksi = '".$notransaksi."'";
			$sSelect = "select a.*,b.jenistraining as namajenistraining from " . $dbname . ".sdm_pengajuantraining a
			left join sdm_5jenistraining b on a.jenistraining = b.kodetraining
			".$where." limit 1";
			$r = fetchData($sSelect);
			if(count($r) > 0){
				$notransaksi 	= $r[0]['notransaksi'];
				$namaprogram 	= $r[0]['namaprogram'];
				$tujuanprogram 	= $r[0]['tujuanprogram'];
				$penyelenggara 	= $r[0]['penyelenggara'];
				$jenisprogram 	= $r[0]['jenistraining'];
			}

			$sSelect = "select karyawanid from " . $dbname . ".sdm_pengajuantraining_dt
			where notransaksi	= '".$notransaksi."'";
			$datakaryawan = fetchData($sSelect);
			
			$sSelect = "select namabiaya,biaya from " . $dbname . ".sdm_pengajuantraining_biaya 
			where notransaksi	= '".$notransaksi."' order by nourut";
			$dataBiaya = fetchData($sSelect);
			
	}else{
			$title = "Insert Data";
	}
	?>
		<form method="POST" action="insert" onsubmit="inputData(this);return false;">
		<fieldset style="margin-bottom: 10px;">
		<legend id="title_Form"><b><?php echo $title; ?></b></legend>
				<div class="body-letter">
					<input id="notransaksi" name="notransaksi" type="hidden" value="<?php echo $notransaksi; ?>">
					<table style="width:100%;">
						<tr><td width="10">1.</td><td width="280">Kategori Program Training</td><td width="1">: </td>
						<td>
						<select name="jenisprogram" class="">
							<?php foreach($optJenisTraining as $k => $v){ 
								$selected = "";
								if($k == $jenisprogram){
									$selected = "selected";
								}
							?>
								<option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $v; ?></option>
							<?php } ?>
						</select></td></tr>
						<tr><td width="10">2.</td><td width="280">Nama Program Training</td><td width="1">: </td>
						<td>
						<input name="namaprogram" value="<?php echo $namaprogram; ?>" class="myinputtext input-100" type="text"></td></tr>
						<tr><td>3.</td><td>Tujuan Training</td>
						<td>: </td><td><input name="tujuanprogram" value="<?php echo $tujuanprogram; ?>" class="myinputtext input-100" type="text"></td></tr>
						<tr><td>4.</td><td>Lembaga Pelatihan yang menyelenggarakan</td><td>: </td>
						<td>
							<input name="penyelenggara" value="<?php echo $penyelenggara; ?>" type="text" class="myinputtext input-100">
						</td></tr>
					</table>	
					<table>		
						<tr><td width="10" valign="top">5.</td><td valign="top">Peserta Training</td><td valign="top"></td><td></td></tr>
						<tr><td></td><td>
							<datalist id="karyawan">
								<?php 
								function getNamapeserta($karid,$data){
									$result = "";
									foreach($data as $k => $v){
										if($karid == $k){
											$result = $v;
											break;
										}
									}
									return $result;
								}
								foreach($optnamakar as $k => $v){ ?>
									<option value="<?php echo $v; ?>"><?php echo $k; ?></option>
								<?php } ?>
							  </datalist>
							<ol id="listpeserta" class="listpeserta">
								<?php
								if(count($datakaryawan) > 0){
									$num = 1;
									foreach($datakaryawan as $k => $v){?>
									<li><input id="karyawan_<?php echo $num; ?>" list="karyawan" num="<?php echo $num; ?>" value="<?php echo getNamapeserta($v['karyawanid'],$optnamakar); ?>" class="peserta myinputtext input-100" onchange="getValOption(this,'karyawan');" onblur="deleteNull(this);">
										<input id="karyawan_<?php echo $num; ?>_content" name="karyawan[]" class="karyawan_content" type="hidden" value="<?php echo $v['karyawanid']; ?>">
									</li> 
								<?php 
										$num++;
									}
								}else{ ?>
									<li><input id="karyawan_1" list="karyawan" num="1" class="peserta myinputtext input-100" onchange="getValOption(this,'karyawan');" onblur="deleteNull(this);">
										<input id="karyawan_1_content" name="karyawan[]" class="karyawan_content" type="hidden" value="">
									</li> 
								<?php } ?>
							</ol>
						</td></tr>
						<tr><td width="10">6.</td><td>Rincian Biaya Training</td><td></td><td></td></tr>
						<tr><td></td><td>
							<ol id="listbiaya" class="listpeserta">
								<?php 
								$jumlah_biaya = 0;
								if(count($dataBiaya) > 0){
									$num = 1;
									foreach($dataBiaya as $k => $v){
									$jumlah_biaya = $jumlah_biaya+$v['biaya'];	
									?>
									<li>
										<input name="biaya[]" num="<?php echo $num; ?>" value="<?php echo $v['biaya']; ?>" class="biaya myinputtextnumber rightlist" type="text" onkeypress="return angka_doang(event);" onchange="hitungjumlah();">
										<input id="biaya_<?php echo $num; ?>" name="namabiaya[]" num="<?php echo $num; ?>" value="<?php echo $v['namabiaya']; ?>" class="myinputtext leftlist" onchange="createNewList(this,'biaya');" onblur="deleteNull(this);">
									</li>
									<?php 
										$num++;
									}
								}else{ ?>
									<li>
										<input name="biaya[]" num="1" value="0" class="biaya myinputtextnumber rightlist" type="text" onkeypress="return angka_doang(event);" onchange="hitungjumlah();">
										<input id="biaya_1" name="namabiaya[]" num="1" value="" class="myinputtext leftlist" onchange="createNewList(this,'biaya');" onblur="deleteNull(this);">
									</li>
								<?php } ?>
							</ol>
							<hr>
							<b><div id="total" class="rightlist"><?php echo number_format($jumlah_biaya);?></div><div class="leftlist total">Total</div></b>
						</td></tr>
					</table>
				</div>
		</fieldset>
		<br/>
		<button type="submit" class="mybutton"><?php echo $_SESSION['lang']['save']; ?></button>
		</form>
	<?php
	CLOSE_BOX();
	break;
	case 'insert':
		if($notransaksi == ""){
			$ntransaksidasar =  "TR/".$kodeorg."/".date("Ym");
			$find = "SELECT MAX(RIGHT(notransaksi,2)) as maxnum from " . $dbname . ".sdm_pengajuantraining
			where kodeorg = '".$kodeorg."' and DATE_FORMAT(createdate, '%Y-%m') = '".date("Y-m")."'
			";
			$MAX = fetchData($find);
			$num = 1;
			if(count($MAX) > 0 ){
				$num = $MAX[0]['maxnum']+1;
				$createNotrans = $ntransaksidasar."-".str_pad($num, 2, "0", STR_PAD_LEFT);// TR/SKLE/201711-01 varChar (25) 
				$notransaksi = $createNotrans;
			}
			$slaveQuery .="insert into ".$dbname.".sdm_pengajuantraining(notransaksi,kodeorg,jenistraining,namaprogram,tujuanprogram,penyelenggara,createby,createdate)
			value (
			'".$notransaksi."',
			'".$kodeorg."',
			'".$jenisprogram."',
			'".$namaprogram."',
			'".$tujuanprogram."',
			'".$penyelenggara."',
			'".$_SESSION['standard']['userid']."',
			'".date("Y-m-d")."'
			);";
		}else{
			$slaveQuery.="UPDATE ".$dbname.".sdm_pengajuantraining set
			jenistraining 		= '".$jenisprogram."',
			namaprogram  		= '".$namaprogram."',
			tujuanprogram  		= '".$tujuanprogram."',
			penyelenggara  		= '".$penyelenggara."'
			where notransaksi 	= '".$notransaksi."';";
		}
		// peserta
		$find = "select notransaksi from " . $dbname . ".sdm_pengajuantraining_dt where notransaksi = '".$notransaksi."'";
		$dt = fetchData($find);
		if(count($dt) > 0 ){
			$delete = "delete from sdm_pengajuantraining_dt where notransaksi = '".$notransaksi."'";
			try{$owlPDO->exec($delete); }
			catch (PDOException $e) {
				$result['err'] = "Delete Peserta Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		$slaveQuery .="insert into ".$dbname.".sdm_pengajuantraining_dt(notransaksi,karyawanid) VALUE";
		$slaveQuery .="('".$notransaksi."','".$karyawan[0]."')";
		for($i=1; $i<count($karyawan); $i++){
			if($karyawan[$i]!=""){
				$slaveQuery .=",('".$notransaksi."','".$karyawan[$i]."')";
			}
		}
		$slaveQuery .=";";
		
		// Biaya
		$findbiaya = "select notransaksi from " . $dbname . ".sdm_pengajuantraining_biaya where notransaksi = '".$notransaksi."'";
		$bi = fetchData($findbiaya);
		if(count($bi) > 0 ){
			$delete = "delete from sdm_pengajuantraining_biaya where notransaksi = '".$notransaksi."'";
			try{$owlPDO->exec($delete); }
			catch (PDOException $e) {
				$result['err'] = "Delete Biaya Training Gagal !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		
		$slaveQuery .="insert into ".$dbname.".sdm_pengajuantraining_biaya(notransaksi,nourut,namabiaya,biaya) VALUE ";
		$slaveQuery .="('".$notransaksi."','0','".$namabiaya[0]."','".$biaya[0]."')";
		for($i=1; $i<count($namabiaya); $i++){
			if($namabiaya[$i]!=""){
				$slaveQuery .=",('".$notransaksi."','".$i."','".$namabiaya[$i]."','".$biaya[$i]."')";
			}
		}
		$slaveQuery .=";";
		
		try{$owlPDO->exec($slaveQuery); $result['messege'] = "success";}
		catch (PDOException $e) {
			$result['err'] = "Gagal Insert !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		echo json_encode($result);
	break;
	case 'cancelpengajuan':
		if($notransaksi != ""){
			$slaveQuery.="UPDATE ".$dbname.".sdm_pengajuantraining set
				tanggalpengajuan	= '".date("Y-m-d")."',
				persetujuan  		= '2'
				where notransaksi 	= '".$notransaksi."';";
			try{$owlPDO->exec($slaveQuery);$result['messege'] = "Pembatalan success";}
			catch (PDOException $e) {
				$result['err'] = "Pengajuan Gagal !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}else{
			$result['err'] = "No transaksi tidak terdefinisikan.";
		}
		echo json_encode($result);
	break;
	case 'selectapproval':?> 
	<div style="padding:10px;">
	<?php OPEN_BOX('','<span class=judul></span>'); 
		if($notransaksi != ""){
			$posisi		= valdefinition('posisi');
			if($posisi	== "updatenameapproval"){
				$html ='<form methode="POST" name="selectapprovement" onsubmit="previewDetailForAll(\'sdm_slave_programtraining.php\',\'proses=gopersetujuan\',this);closeDialog2();return false;">';
			}else{
				$html ='<form methode="POST" name="selectapprovement" action="gopersetujuan" onsubmit="inputData(this);closeDialog2();return false;">';
			}
			$html .= '<fieldset style="margin-bottom: 10px;">
				<legend id="title_Form"><b>'.$_SESSION['lang']['persetujuan'].' : '.$notransaksi.'</b></legend>';
				
			$sahApp = "select karyawanid,level,status from ".$dbname.".approval 
			where notransaksi = '".$notransaksi."' and jenispersetujuan = 'PPT' and karyawanid <> '0000000000' and karyawanid <> '' order by level ASC";
			$sudahApp = fetchData($sahApp);
			$sudahada = array();
			if(count($sudahApp) > 0){
				for($i=0; $i<count($sudahApp); $i++){
					$d['karyawanid'] 	= $sudahApp[$i]['karyawanid'];
					$d['level'] 		= $sudahApp[$i]['level'];
					$d['status'] 		= $sudahApp[$i]['status'];
					$sudahada[] 		= $d;
				}
			}
		$str = "select kodeorg from " . $dbname . ".sdm_pengajuantraining where notransaksi = '".$notransaksi."'";
		$kodeorg = fetchData($str);
		if(count($kodeorg) > 0 ){	
			$sRoleApp = "select a.level,a.karyawanid,b.namakaryawan from " . $dbname . ".setup_approval a 
			left join datakaryawan b on a.karyawanid = b.karyawanid
			where a.jenispersetujuan = 'PPT' and a.kodeunit = '".$kodeorg[0]['kodeorg']."' order by a.level ASC";
			$RApp = fetchData($sRoleApp);
			if(count($RApp) > 0 ){
				$dataperlevel = array();
				for($i=0; $i<count($RApp); $i++){
					$d = array();
					$d['karyawanid'] 	= $RApp[$i]['karyawanid'];
					$d['namakaryawan'] 	= $RApp[$i]['namakaryawan'];
					$d['level'] 		= $RApp[$i]['level'];
					$dataperlevel[$RApp[$i]['level']][] = $d;
				}
				$html .= '<input name="notransaksi" type="hidden" value="'.$notransaksi.'" >';
				if($posisi	== "updatenameapproval"){
					$html .= '<input name="posisi" type="hidden" value="'.$posisi.'" >';
				}
				$html .= '<table border="0">';
				foreach($dataperlevel as $k => $v){
					$req = "";
					$required = "";
					if($k == 1){
						$req = '<img src="images/obl.png" title="Obligatory">';
						$required = "required";
					}
					$select = "";
					$value = "";
					foreach($sudahada as $sk => $sv){
						if($sv['level'] == $k and $sv['status'] != "0"){
							$select = "disabled";
						}
						if($sv['level'] == $k and $sv['karyawanid'] != $_SESSION['standard']['userid']){
							$select = "disabled";
						}
						if($sv['level'] == $k){
							$value 	= $sv['karyawanid'];
						}
					}
					if(count($dataperlevel[$k]) > 1 and $select != 'disabled'){
						
						$html .= '<tr><td>'.$_SESSION['lang']['persetujuan'].' '.$k.'</td><td width="1">:</td><td>
						<input type="hidden" name="level[]" value="'.$v[0]['level'].'" '.$select.'>
						<select name="approvemen[]" '.$required.' '.$select.'>';
						$html .= '<option value=""></option>';
						foreach($dataperlevel[$k] as $ik => $iv){
							if($iv['karyawanid'] == $value){
								$html .= '<option value="'.$iv['karyawanid'].'" selected>'.$iv['namakaryawan'].'</option>';
							}else{
								$html .= '<option value="'.$iv['karyawanid'].'">'.$iv['namakaryawan'].'</option>';
							}
						}
						$html .= '</select>

						'.$req.'</td></tr>';
					}else if(count($dataperlevel[$k]) > 1 and $select == 'disabled'){
						$html .= '<tr ><td>'.$_SESSION['lang']['persetujuan'].' '.$k.'</td>
						<td width="1">:</td><td>';
						foreach($dataperlevel[$k] as $ik => $iv){
							if($iv['karyawanid'] == $value){
								$html .= $iv['namakaryawan'];
							}
						}
						$html .='</td></tr>';
					}else{
						$html .= '<tr ><td>'.$_SESSION['lang']['persetujuan'].' '.$k.'
						<input type="hidden" name="level[]" value="'.$k.'"></td>
						<td width="1">:</td><td>'.$v[0]['namakaryawan'].'
						<input type="hidden" name="approvemen[]" value="'.$v[0]['karyawanid'].'"></td></tr>';
					}
				}
				$html .= '</table>';
				$html .= '</fieldset>';
				$html .= '<br/>';
				$html .= '<button type="submit" class="mybutton">'.$_SESSION['lang']['save'].'</button>';
				$html .= '</form>';
			}
		}
			echo $html;
		}else{
			echo "No Transaksi Tidak ditemukan!";
		}
		?>
	</div>
	<?php	
		CLOSE_BOX();
	break;
	case 'gopersetujuan':
		// data $postapprovemen.$postlevel
		if($notransaksi != ""){
			$posisi		= valdefinition('posisi'); //post posisi kiriman saat approval yg di My account>Approval (case :listofapprovement)

			//delete approval bila udah pernah diajukan
			if($posisi	== "updatenameapproval"){
				$level			= valdefinition('level'); //post posisi kiriman saat approval yg di My account>Approval (case :listofapprovement)
				$approvemen		= valdefinition('approvemen'); //post posisi kiriman saat approval yg di My account>Approval (case :listofapprovement)
				$persetujuan = "select notransaksi,karyawanid,level,status from " . $dbname . ".approval
				where jenispersetujuan = 'PPT' and notransaksi = '".$notransaksi."'";
				$rp = fetchData($persetujuan);
				if(count($rp)>0){
					$levelsendiri = "";
					for($i=0; $i<count($rp); $i++){
						if($rp[$i]['karyawanid'] == $_SESSION['standard']['userid']){
							$levelsendiri = $rp[$i]['level']; 
						}
					}
				}

				if(count($level)>0){
					for($i=0; $i<count($level); $i++){
						if($level[$i] > $levelsendiri ){
							$slaveQuery .="UPDATE ".$dbname.".approval set
							karyawanid	= '".$approvemen[$i]."'
							where jenispersetujuan = 'PPT' and notransaksi 	= '".$notransaksi."' and level = '".$level[$i]."';";
						}
					}
				}
				if($slaveQuery != ""){
					try{$owlPDO->exec($slaveQuery);
					$result['err'] 		=  "redirect";
					$result['redirect'] =  "closeDialog2();getdetail('PPT');";
					}catch (PDOException $e) {
						$result['err'] = "Pengajuan Gagal !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}else{
					$result['err'] = "Tidak ada yang di Update!"; 
				}
			}else{
				$persetujuan = "select notransaksi from " . $dbname . ".approval
				where jenispersetujuan = 'PPT' and notransaksi = '".$notransaksi."'";
				$rp = fetchData($persetujuan);
			
				if(count($rp) > 0){
					$delete = "delete from approval where jenispersetujuan = 'PPT' and  notransaksi = '".$notransaksi."'";
					try{$owlPDO->exec($delete); }
					catch (PDOException $e) {
						$result['err'] = "Delete Approve Pengajuan Training Gagal !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				//role approve
				$slaveQuery .="insert into ".$dbname.".approval(notransaksi,jenispersetujuan,level,karyawanid,tanggal) VALUE";
				$slaveQuery .="('".$notransaksi."','PPT','".$postlevel[0]."','".$postapprovemen[0]."','".$tglskrng."')";
				for($i=1; $i<count($postlevel); $i++){
					$slaveQuery .=",('".$notransaksi."','PPT','".$postlevel[$i]."','".$postapprovemen[$i]."','".$tglskrng."')";


				}
				$slaveQuery .=";";
				$slaveQuery .="UPDATE ".$dbname.".sdm_pengajuantraining set
					tanggalpengajuan	= '".date("Y-m-d")."',
					persetujuan  		= '4'
					where notransaksi 	= '".$notransaksi."';";
			

				try{$owlPDO->exec($slaveQuery);
				$result['messege'] =  "Pengajuan Telah diteruskan.";

				$result['redirect'] = "getSlave();";
				}catch (PDOException $e) {
					$result['err'] = "Pengajuan Gagal !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}

		}else{
			$result['err'] = "No transaksi tidak terdefinisikan.";
		}

		echo json_encode($result);
	break;
	case 'diajukan':
		if($notransaksi != ""){
			//role approve
			$str = "select kodeorg from " . $dbname . ".sdm_pengajuantraining where notransaksi = '".$notransaksi."'";
			$kodeorg = fetchData($str);
			if(count($kodeorg) > 0 ){
				$sRoleApp = "select level,karyawanid from " . $dbname . ".setup_approval
				where jenispersetujuan = 'PPT' and kodeunit = '".$kodeorg[0]['kodeorg']."' order by level ASC";
				$RApp = fetchData($sRoleApp);
				if(count($RApp) > 0 ){
					$level = 0;
					for($i=0; $i<count($RApp); $i++){
						//tentukan yang menyetujui karena ada level yang lebih dari 1.
						if($RApp[$i]['level'] == $level){
							$level = 1;//lebih dari 1
							break;
						}else{
							$level = $RApp[$i]['level'];
						}
					}
					if($level == 1){
						//lebih dari 1 harus pilih terlebih dahulu
						$result['err'] = "redirect";
						$result['redirect'] = "getSlave('selectapproval','','notransaksi=".$notransaksi."')"; 
						echo json_encode($result);
						exit();
						die();
					}
					$slaveQuery .="insert into ".$dbname.".approval(notransaksi,jenispersetujuan,level,karyawanid,tanggal) VALUE";
					$slaveQuery .="('".$notransaksi."','PPT','".$RApp[0]['level']."','".$RApp[0]['karyawanid']."','".$tglskrng."')";
					for($i=1; $i<count($RApp); $i++){
						$slaveQuery .=",('".$notransaksi."','PPT','".$RApp[$i]['level']."','".$RApp[$i]['karyawanid']."','".$tglskrng."')";
					}
					$slaveQuery .=";";
					
					//delete approval bila udah pernah diajukan
					$persetujuan = "select notransaksi from " . $dbname . ".approval
					where jenispersetujuan = 'PPT' and notransaksi = '".$notransaksi."'";
					$rp = fetchData($persetujuan);
					
					if(count($rp) > 0){
						$delete = "delete from approval where jenispersetujuan = 'PPT' and  notransaksi = '".$notransaksi."'";
						try{$owlPDO->exec($delete); }
						catch (PDOException $e) {
							$result['err'] = "Delete Approve Pengajuan Training Gagal !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}


					$slaveQuery.="UPDATE ".$dbname.".sdm_pengajuantraining set
						tanggalpengajuan	= '".date("Y-m-d")."',
						persetujuan  		= '4'
						where notransaksi 	= '".$notransaksi."';";
					try{
						$owlPDO->exec($slaveQuery);
						$result['messege'] = "Pengajuan Telah diteruskan.";



						$result['redirect'] = "getSlave();";
					}
					catch (PDOException $e) {
						$result['err'] = "Pengajuan Gagal !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}else{
					$result['err'] = "Setup Approval belum ada!";
				}
			}else{
				$result['err'] = "No transaksi tidak terdefinisikan.";
			}
		}else{
				$result['err'] = "No transaksi tidak terdefinisikan.";
		}
		echo json_encode($result);
	break;
	case 'listofapprovement':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' border='0'>
				<thead>
				<tr class=rowheader>
					<th align=center>No.</th>
					<th align=center>".$_SESSION['lang']['tanggal']."</th>
					<th align=center>".$_SESSION['lang']['kodeorganisasi']."</th>
					<th align=center>Detail</th>";
					

					$countApp = getCountApproval('PPT');
					for($i=1;$i<=$countApp;$i++)
					{
						$tab.="<th align=center>".$_SESSION['lang']['persetujuan'].$i."</th>";
					}
					
					$tab.="<th colspan='3' align='center'>Verification</th>";
					
				$tab.="</tr>
				</thead>
				<tbody>";
				$str="select a.*, b.tanggalpengajuan , b.kodeorg from ".$dbname.".approval a 
				left join ".$dbname.".sdm_pengajuantraining b on a.notransaksi = b.notransaksi 
				where a.jenispersetujuan='PPT' and a.status='0' and karyawanid='".$_SESSION['standard']['userid']."' group by a.notransaksi order by b.tanggalpengajuan desc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					$kodeorg=substr($bar['kodeorg'],0,4);
					$optNmOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kodeorg."'");
	
					$no++;
					$tab.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td align=left>".tanggalnormal($bar['tanggal'])."</td>
						<td align=left>".$optNmOrg[$kodeorg]."</td>
						<td align=center>
							<img src=\"images/skyblue/zoom.png\" class=\"resicon\" onclick=\"previewDetailForAll('sdm_slave_programtraining.php','proses=getdetail','notransaksi=".$bar['notransaksi']."');\">							
						</td>";
						//$showaction = 0;
						$pilihselanjutnya = 0;
						$levelunapprv =0;
						$userapprove = array();
						for($i=1;$i<=$countApp;$i++)
						{
							$arrDetail = detailApprove($i,$bar['notransaksi'],'PPT');
							//if($arrDetail['karyawanid']==$_SESSION['standard']['userid'] && ($arrDetail['status']=='' || $arrDetail['status']=='0'))
							//{
							//	$showaction = 1;
							//}
							
							if($arrDetail['nama']!=''){
								$tab.="<td style='text-align:center'><a href=# onclick=prcek_status_pp('".$arrDetail['status']."')>".$arrDetail['nama']."</a></td>";
							}else{
								$tab.="<td style='text-align:center'>-</td>";
								
							}
							if(count($userapprove) == 0 and $arrDetail['status'] == '0'){
								$d['karyawanid']	= $arrDetail['karyawanid'];
								$d['nama']			= $arrDetail['nama'];
								$d['level']			= $arrDetail['level'];
								$d['status']		= $arrDetail['status'];
								$userapprove[]		= $d;
								if($i != $countApp and $arrDetail['karyawanid'] == ""){
									$pilihselanjutnya = 1;
								}
							}
						}
						if(count($userapprove)>0)
						{
							if($userapprove[0]['karyawanid'] == $_SESSION['standard']['userid']){
								if($pilihselanjutnya == 1){
								$tab.="<td style='text-align:center'>
									<button  class='mybutton' href=# onclick=\"alert('Nama Persetujuan selanjutnya kosong, silahkan tentukan terlebih dahulu!');previewDetailForAll('sdm_slave_programtraining.php','proses=selectapproval','notransaksi=".$bar["notransaksi"]."&posisi=updatenameapproval');\">".$_SESSION['lang']['setuju']."</button>
								</td>";
								}else{
								$tab.="
								<td style='text-align:center'>
									<button  class='mybutton' href=# onclick=\"formalasan('PPT','PPT','".$bar['notransaksi']."','".$bar['level']."','1')\">".$_SESSION['lang']['setuju']."</button>
								</td>";
								}
								$tab.="	
								<td>
									<button class='mybutton' href=# onclick=formalasan('PPT','PPT','".$bar['notransaksi']."','".$bar['level']."','2') >".$_SESSION['lang']['koreksi']."</button>
								</td>
								<td>
									<button class='mybutton' href=# onclick=formalasan('PPT','PPT','".$bar['notransaksi']."','".$bar['level']."','3') >".$_SESSION['lang']['ditolak']."</button>
								</td>";
							}else{
								$tab.="<td colspan=3>Verification by ".$userapprove[0]['nama']."</td>";
							}
						}else{
							$tab.="<td colspan=3>&nbsp;</td>";
						}
					$tab.="</tr>";
				}
				$tab.="</tbody>
				<tfoot>
				</tfoot>
			</table>
		</fieldset>";
	break;
	case 'approved':
		$level				= valdefinition('level');
		$hasilpersetujuan	= valdefinition('hasilpersetujuan');
		$alasan				= valdefinition('alasan');
		$tambahanWhere		= "";
		if($alasan==''){
			exit("warning : Komentar harus diisi.");
		}
		if($hasilpersetujuan == '1'){
			$tambahanWhere = "and level = '".$level."' and karyawanid = '".$_SESSION['standard']['userid']."'";
		}
		
		$slaveQuery.="UPDATE ".$dbname.".approval set
		status='".$hasilpersetujuan."',komentar='".$alasan."',tanggal='".$tglskrng."' 
		where notransaksi='".$notransaksi."' and jenispersetujuan = 'PPT' ".$tambahanWhere ." ;";
		
		if($hasilpersetujuan == '1'){	
			//select level teringgi
			$find = "SELECT MAX(level) as level from " . $dbname . ".approval where notransaksi='".$notransaksi."' and jenispersetujuan='PPT' ";
			$MAX = fetchData($find);
			$leveltertinggi = $MAX[0]['level'];
			if($leveltertinggi == $level){
				// jika yng exec level tertinggi maka Approve Selesail, maka lakukan approved di pengajuan Training
				$slaveQuery.="UPDATE ".$dbname.".sdm_pengajuantraining set
				persetujuan='".$hasilpersetujuan."' where notransaksi = '".$notransaksi."';";
				try{
					$owlPDO->exec($slaveQuery);
				}catch (PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}else{
				
			}
		}else if($hasilpersetujuan != '1'){	
			$slaveQuery.="UPDATE ".$dbname.".sdm_pengajuantraining set
			persetujuan='".$hasilpersetujuan."' where notransaksi = '".$notransaksi."';";
		}
		
		try{
			$owlPDO->exec($slaveQuery);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		
	break;
	case 'rejected':
		if($alasan==''){
			exit("warning : Komentar harus diisi.");
		}
	break;
}
?>	
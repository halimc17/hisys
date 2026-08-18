<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	
	$kode=checkPostGet('kode','');
	$sts=checkPostGet('sts','');
	$transaksirutin=checkPostGet('transaksirutin','');
	$namajenis=checkPostGet('namajenis','');
	$sumber=checkPostGet('sumber','');
	$tipesup=checkPostGet('tipesup','');
	$method=checkPostGet('method','');
	$tipepajak=checkPostGet('tipepajak','');
	$aruskas=checkPostGet('aruskas','');
	$noakun=checkPostGet('noakun','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($kode==''||$namajenis==''){
				echo "Gagal : Kode dan nama jenis harus diisi.";
				exit();
			}
			
			$str="select * from ".$dbname.".keu_5jenistagihan where kode='".$kode."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if($numRows>=1){
				echo "Error: Kode tagihan sudah pernah terdaftar sebelumnya.";
			}else{
				$str="insert into ".$dbname.".keu_5jenistagihan (kode,namajenis,source,jurnal,status,transaksirutin,tipesupplier) 
				values ('".$kode."','".$namajenis."','".$sumber."','".$_POST['statJrn']."','".$sts."','".$transaksirutin."','".$tipesup."')";
				try{
					$owlPDO->exec($str); 
					getContainer();
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
			
		case 'edit':
			if($kode==''||$namajenis==''){
				echo "Gagal : Kode dan nama jenis harus diisi.";
				exit();
			}
			$str="update ".$dbname.".keu_5jenistagihan set status='".$sts."', transaksirutin='".$transaksirutin."', tipesupplier='".$tipesup."', namajenis='".$namajenis."',source='".$sumber."',jurnal='".$_POST['statJrn']."' where kode='".$kode."'";
			try{
				$owlPDO->exec($str); 
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".keu_5jenistagihan where kode='".$kode."'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;

		case 'getNoakun':
			$optnoakun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

			$str = "SELECT DISTINCT a.noakun, b.namaakun
					FROM ".$dbname.".keu_5aruskas_detail a JOIN ".$dbname.".keu_5akun b ON a.noakun = b.noakun
					WHERE a.noaruskas = '".$aruskas."' ORDER BY b.namaakun ASC";
			$res = fetchdata($str);
			foreach($res as $val){
				$optnoakun .= "<option value='".$val['noakun']."'>[".$val['noakun']."] ".$val['namaakun']."</option>";
			}

			echo $optnoakun;
		break;

		case 'previewfield':
			$tab = "<link rel=stylesheet type=text/css href=style/generic.css>";
			$optaruskas = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

			$str = "SELECT DISTINCT noaruskas, nama_aruskas FROM ".$dbname.".keu_5aruskas
					WHERE level='3' AND noaruskas LIKE '1%' ORDER BY nama_aruskas ASC";
			$res = fetchdata($str);
			foreach($res as $key=>$val){
				$optaruskas .= "<option value='".$val['noaruskas']."'>".$val['noaruskas']." - ".$val['nama_aruskas']."</option>";
			}

			$tab .= "<fieldset style='width:97%'>
						<legend>".$_SESSION['lang']['entryForm']."</legend>
						<table style='font-size:14px'>
							<tr>
								<td>".$_SESSION['lang']['kode']."</td>
								<td>:</td>
								<td><input type=text id=kodedt class=myinputtext style=width:152px value='".$kode."' readonly>&nbsp;</td>

								<td>Tipe Pajak</td>
								<td>:</td>
								<td>
									<select id=tipepajak style=width:155px>
										<option value=''>".$_SESSION['lang']['pilihdata']."</option>
										<option value='ppn'>PPN</option>
										<option value='pph'>PPH</option>
									</select>&nbsp;
								</td>
							</tr>
							<tr>
								<td>".$_SESSION['lang']['aruskas']."</td>
								<td>:</td>
								<td>
									<select id=aruskas style=width:155px onchange=getakun()>".$optaruskas."</select>
                					<img id=aruskas onclick=z.elSearch('aruskas',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
								</td>

								<td>".$_SESSION['lang']['noakun']."</td>
								<td>:</td>
								<td>
									<select id=noakun style=width:155px></select>
                					<img id=noakun onclick=z.elSearch('noakun',event) class=zImgBtn src=images/skyblue/zoom.png style=position:relative;top:3px;left:3px;>&nbsp;
								</td>
							</tr>
							<tr>
								<td colspan=2></td>
								<td>
									<button class=mybutton onclick=savedt()>".$_SESSION['lang']['save']."</button>
									<input type=hidden id=methoddt class=myinputtext value='insertdt'>
								</td>
							</tr>
						</table>
					</fieldset>";

			$tab .= "<br><table cellpadding=1 cellspacing=1 class=sortable style=width:100%>
			<thead>
			<tr class=rowheader>
			<td align=center >".$_SESSION['lang']['nourut']."</td>
			<td align=center >".$_SESSION['lang']['kode']."</td>
			<td align=center >Tipe Pajak</td>
			<td align=center >".$_SESSION['lang']['noaruskas']."</td>
			<td align=center >".$_SESSION['lang']['noakun']."</td>
			<td align=center >".$_SESSION['lang']['action']."</td>
			</tr>";
			$tab .= "</thead>";
			$tab.= "<tbody>";
			$nmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun');
			$nmAruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');
			$str="select * from ".$dbname.".keu_5jenistagihan_akunpajak where kode='".$kode."' AND tipepajak IN ('ppn','pph')";
			$res=fetchData($str);
			foreach ($res as $bar) {
				$no++;
				$tab.= "<tr class=rowcontent>";
				$tab.= "<td align=center>".$no."</td>";
				$tab.= "<td align=center>".$bar['kode']."</td>";
				$tab.= "<td align=center>".$bar['tipepajak']."</td>";
				$tab.= "<td align=left nowrap>".$bar['noaruskas']." - ".$nmAruskas[$bar['noaruskas']]."</td>";
				$tab.= "<td align=left nowrap>".$bar['noakun']." - ".$nmAkun[$bar['noakun']]."</td>";
				$tab.= "<td align=center>
							<img src='images/skyblue/edit.png' class='resicon' title='Edit'\" onclick=fillfielddt('".$kode."','".$bar['tipepajak']."','".$bar['noaruskas']."','".$bar['noakun']."')>
							<img src='images/skyblue/delete.png' class='resicon' title='Delete'\" onclick=deletedt('".$kode."','".$bar['tipepajak']."')>
						</td>";
				$tab.= "</tr>";
			}
			if(empty($res)) {
				$tab .= "<tr><td colspan=5>";
				$tab .= $_SESSION['lang']['dataempty'];
				$tab .= "</td></tr>";
			}
			$tab.= "</tbody>";
			$tab .= "</table>";



			echo $tab;
		break;

		case 'insertdt':
			if ($kode == '' || $tipepajak == '' || $aruskas == '' || $noakun == '') {
				exit('Warning : Harap isi data dengan lengkap');
			}

			$str = "SELECT * FROM ".$dbname.".keu_5jenistagihan_akunpajak WHERE kode = '".$kode."' AND tipepajak = '".$tipepajak."'";
			$res = fetchdata($str);
			if (count($res) > 0) {
				exit('Warning : Data dengan Kode dan Tipe Pajak tersebut sudah ada.');
			}

			$str = "INSERT INTO ".$dbname.".keu_5jenistagihan_akunpajak (kode, tipepajak, noaruskas, noakun) 
					VALUES ('".$kode."','".$tipepajak."','".$aruskas."','".$noakun."')";
			try {
				$owlPDO->exec($str); 
				getContainer();
			} catch (PDOException $e) {
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;

		case 'updatedt':
			if ($kode == '' || $tipepajak == '' || $aruskas == '' || $noakun == '') {
				exit('Warning : Harap isi data dengan lengkap');
			}

			// $str = "SELECT * FROM ".$dbname.".keu_5jenistagihan_akunpajak WHERE kode = '".$kode."' AND tipepajak = '".$tipepajak."'";
			// $res = fetchdata($str);
			// if (count($res) > 0) {
			// 	exit('Warning : Data dengan Kode dan Tipe Pajak tersebut sudah ada.');
			// }

			$str = "UPDATE ".$dbname.".keu_5jenistagihan_akunpajak SET noaruskas = '".$aruskas."', noakun = '".$noakun."'
					WHERE kode = '".$kode."' AND tipepajak = '".$tipepajak."'";
			try {
				$owlPDO->exec($str); 
				getContainer();
			} catch (PDOException $e) {
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;

		case 'deletedt':
			$str = "DELETE FROM ".$dbname.".keu_5jenistagihan_akunpajak WHERE kode = '".$kode."' AND tipepajak = '".$tipepajak."'";
			try {
				$owlPDO->exec($str); 
				getContainer();
			} catch (PDOException $e) {
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer(){
		global $owlPDO;
		global $dbname;
		$arrTr=array("1"=>"Ya","0"=>"Tidak");
		$arrJrn=array("1"=>"Jurnal","0"=>"Tidak Jurnal");
		$arrSts=array("1"=>"Aktif","0"=>"Non Aktif");
		$str="select * from ".$dbname.".keu_5jenistagihan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		if($numrows<=0){
			echo"<tr class=rowcontent>
				<td colspan=11 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>";
		}else{
			while($bar=$res->fetch())
			{
				$no+=1;
				echo"<tr class=rowcontent>
						<td style='text-align:right;'>".$no."</td>
						<td>".$bar->kode."</td>
						<td>".$bar->namajenis."</td>
						<td>".$bar->source."</td>
						<td>".$bar->tipesupplier."</td>
						<td>".$arrTr[$bar->transaksirutin]."</td>
						<td>".$arrJrn[$bar->jurnal]."</td>
						<td>".$arrSts[$bar->status]."</td>
						<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode."','".$bar->namajenis."','".$bar->source."','".$bar->jurnal."','".$bar->status."','".$bar->transaksirutin."','".$bar->tipesupplier."')\"></td>
						<td><img src='images/skyblue/delete.png' class='resicon' title='Hapus' onclick=\"deletefield('".$bar->kode."')\"></td>
						<td><img src='images/skyblue/zoom.png' class='resicon' title='Preview' onclick=\"previewfield('".$bar->kode."','previewfield')\"></td>
					</tr>";
			}
		}
	}
?>
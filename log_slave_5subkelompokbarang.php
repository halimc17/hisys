<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	error_reporting(0);
	
	$kdKlBarang=checkPostGet('kdKlBarang','');
	$kdSubKl=checkPostGet('kdSubKl','');
	$namaSubKl=checkPostGet('namaSubKl','');
	$status=checkPostGet('status','');
	$idkategori=checkPostGet('idkategori','');
	$kodevhc=checkPostGet('kodevhc','');
	$method=checkPostGet('method','');

	$jnsapp = "SKL";
	date_default_timezone_set("Asia/Bangkok");
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'getKodeSub':
			$str="select * from ".$dbname.".log_5subklbarang where kelompok like '%".$kdKlBarang."%' order by kode desc limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch()){
				$noTrkhr = $bar->kode;
			}
			if($noTrkhr==''){
				$noTrkhr=$kdKlBarang.'01';
			}else{
				$noTrkhr+=1;
			}			
			echo $noTrkhr;
		break;
		
		case 'insert':
			// exit('warning'.count($idkategori));
			if($kdSubKl==''||$namaSubKl==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}

			#= Insert kategori
			$idkategori = implode(",",$idkategori);
			
			$str="select * from ".$dbname.".log_5subklbarang where kode='".$kdSubKl."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numRows=owlBaris($qry);
			if($numRows>=1){
				echo "Error: Kode sub kelompok barangn sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".log_5subklbarang (kode,namasubkelompok,kelompok,status,createby,createtime, updateby,updatetime,idkategori,kodevhc) 
				values ('".$kdSubKl."','".$namaSubKl."','".$kdKlBarang."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','". $idkategori."','". $kodevhc."')";
				try{
					$owlPDO->exec($strIns);
					
					$listpersetujuan=$_POST['persetujuan'];
					foreach($listpersetujuan as $key=>$val)
					{
						// $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kdSubKl."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','".$status."')";
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kdSubKl."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
						try
						{
							$owlPDO->exec($str);
						}
						catch (PDOException $e) 
						{
							print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
					}
					
					getContainer();
				}catch(PDOException $e){
					echo "DB Error : ".$e->getMessage();
				}
			}
		break;
			
		case 'edit':
			if($namaSubKl==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}

			// exit('warning');
			#= Insert kategori
			$idkategori = implode(",", $idkategori);

			$str="update ".$dbname.".log_5subklbarang set namasubkelompok='".$namaSubKl."',status='".$status."' ,updateby='".$_SESSION['standard']['userid']."' ,updatetime='".date('Y-m-d H:i'). "', idkategori='" . $idkategori . "', kodevhc='" . $kodevhc . "' where kode='".$kdSubKl."'";
			try{
				$owlPDO->exec($str); 
				
				$str="delete from ".$dbname.".approval where notransaksi='".$kdSubKl."'";
				try{
					$owlPDO->exec($str);
					
					$listpersetujuan=$_POST['persetujuan'];
					foreach($listpersetujuan as $key=>$val)
					{
						// $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kdSubKl."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','".$status."')";
						$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$kdSubKl."','".$jnsapp."','".$key."','".$listpersetujuan[$key]."','0')";
						try
						{
							$owlPDO->exec($str);
						}
						catch (PDOException $e) 
						{
							print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
					}
				}catch (PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
				
				getContainer();
			}catch(PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".log_5subklbarang where kode='".$kdSubKl."'";
			try{
				$owlPDO->exec($str); 
				
				$str="delete from ".$dbname.".approval where notransaksi='".$kdSubKl."'";
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}catch(PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer(){
		global $conn;
		global $dbname;
		global $owlPDO;
		global $jnsapp;
		
		$namakl=makeOption($dbname,'log_5klbarang','kode,kelompok');
		$optkodevh=['0'=>'Tidak','1'=>'Wajib Terisi'];
		$str="select a.* from ".$dbname.".log_5subklbarang a left join ".$dbname.".log_5klbarang b on a.kelompok=b.kode where b.status='1'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($qry);
		
		if($numrows<=0){
			echo "<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			$no=0;
			while($res=$qry->fetch())
			{
				#= Explode untuk dapatkan nama kategori
				$namakategori = explode(",",$res->idkategori);
				#= Convert idkategori menjadi nama kategori
				$conv = makeOption($dbname, "log_5kategoribarang", "id,jenis");
				#= Buat perulangan untuk hitung ada berapa (,) / data di dalam arraynya
				$nk = array();
				// for ($x = 0; $x < count($namakategori); $x++) {
				// 	#= Cek apakah array lebih dari 1,
				// 	#= Jika iya, maka convert dan hilangkan koma nya
				// 	if (count($namakategori) > 1) {
				// 		$nk[$res->idkategori] .= $conv[$namakategori[$x]] . "";
				// 		$nk[$res->idkategori] .= rtrim(",", $nk[$res->idkategori]) . "&nbsp;";
				// 	#= Else, Jika data hanya 1
				// 	#= Jika data tidak koma (,), maka tampilkan seperti biasa
				// 	} else {
				// 		$nk[$res->idkategori] = $conv[$res->idkategori];
				// 	}
				// }

				foreach($namakategori as $key => $val) {
					#= Cek apakah array lebih dari 1,
					#= Jika iya, maka convert dan hilangkan koma nya
					if (count($namakategori) > 1) {
						$nk[$res->idkategori] .= $conv[$val] . "";
						$nk[$res->idkategori] .= rtrim(",", $nk[$val]) . "&nbsp;";
						#= Else, Jika data hanya 1
						#= Jika data tidak koma (,), maka tampilkan seperti biasa
					} else {
						$nk[$res->idkategori] = $conv[$res->idkategori];
					}
				}

				$no+=1;
				echo"<tr class=rowcontent>
						<td style='text-align:right;'>".$no."</td>
						<td>".$res->kelompok." - ".$namakl[$res->kelompok]."</td>
						<td>".$res->kode."</td>
						<td>".$res->namasubkelompok."</td>
						<td>".$nk[$res->idkategori]."</td>
						<td>".$optkodevh[$res->kodevhc]."</td>
						<td>".($res->status=='0' ? 'Non-Aktif' : ($res->status=='3' ? 'Ditolak' : 'Aktif'))."</td>
						";
						
						## APPROVAL ##
						$countApp = getCountApproval($jnsapp);
						for($i=1;$i<=$countApp;$i++)
						{
							@$arrdetail = detailApprove($i,$res->kode,$jnsapp);
							
							echo"<td align=center>".$arrdetail['nama']." <i>(".($arrdetail['status']=='0'?'Menunggu Keputusan':($arrdetail['status']=='3'?'Ditolak':'Disetujui')).")</i></td>";
						}
						
						echo"<td align=center>";		
						if($res->status=='1'){
							#= Di comment karena tidak dapat comma (,)
							// echo"<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kelompok."','".$res->kode."','".$res->namasubkelompok."','".$res->status."','".$res->idkategori."')\">&nbsp;";
							echo "<img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('" . $res->idkategori . "','".$res->kelompok."','".$res->kode."','".$res->namasubkelompok."','".$res->status."','".$res->kodevhc."')\">&nbsp;";
						}else if($res->status=='3'){
							echo"<img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res->kode."')\">";
						}
					   
					echo"</td>
				</tr>";
			}
		}
	}
?>
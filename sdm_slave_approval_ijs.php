<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$session_id = $_SESSION['standard']['userid'];
$karyawanid=checkPostGet('karyawanid', $session_id);
$method = checkPostGet('method', '');
$proses = checkPostGet('proses', '');
$level = checkPostGet('level', '');
$notransaksi = checkPostGet('notransaksi', '');
$kolom = checkPostGet('kolom', '');
$comment = checkPostGet('comment', '');
$userid = checkPostGet('userid', '');
$tglskrng = date("Y-m-d H:i:s");
$arrstatus = array('0' => 'belum diproses', '1' => 'disetujui', '2' => 'ditolak');
  
switch ($method) {
case 'getdetail':
	case'IJS':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['notransaksi']."</td>
				<td align=center>".$_SESSION['lang']['namakaryawan']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['keperluan']."</td>
				<td align=center>".$_SESSION['lang']['jenisijin']."</td>
				<td align=center>".$_SESSION['lang']['dari']."  ".$_SESSION['lang']['jam']."</td>
				<td align=center>".$_SESSION['lang']['tglcutisampai']."  ".$_SESSION['lang']['jam']."</td>
				<td align=center>".$_SESSION['lang']['pdf']."</td>
				<td align=center>".$_SESSION['lang']['file']."</td>
				<td align=center colspan=2>Action</td>";

            if ($lvlapr!=0) {
                $countApp=$lvlapr;
            }else { 
                $countApp = getCountApproval('IJS');
            }
            for ($i = 1; $i <= $countApp; $i++) {
                $tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
            }

		$tab.="</tr>
			</thead>
			<tbody>";

            $str = "select * from ".$dbname.".approval a
                left join ".$dbname.".sdm_ijin b on a.notransaksi = b.notransaksi
                where a.jenispersetujuan='IJS' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
                $res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
			 
                $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['kodeorg']."'");
				$nmijin 	= makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin',"idjenis='".$bar['idjenis']."'");


                $no++;
                $tab.="<tr class=rowcontent>
                    	<td align=center>".$no."</td>
						<td align=center>".$bar['notransaksi']."</td>
						<td>".getNamaKaryawan($bar['karyawanid'])."</td>
						<td align=center>".tanggalnormal($bar['tanggal'])."</td>
						<td>".$bar['keperluan']."</td>
						<td>".$nmijin[$bar['idjenis']]."</td>
						<td align=center>".tanggalnormald($bar['darijam'])."</td>
						<td align=center>".tanggalnormald($bar['sampaijam'])."</td>
						<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdfcutinonstaff('".$bar['notransaksi']."')\"></td>
						<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$bar['notransaksi']."','1')\" src='images/upload-2-xxl.png'/></td>
						";


                    $showaction = 0;
                    $countubahjumlah = 0;
                    $level = 1;
                    $xxx = "";
                    for ($i = 1; $i <= $countApp; $i++) {
                        
                        $strx="select * from ".$dbname.".approval where notransaksi='".$bar['notransaksi']."' and level='".$i."'";
                        $resx=fetchdata($strx);
                        foreach($resx as $keyx=>$valx){
                            if($valx['karyawanid']==$karyawanid){
                                if($valx['status']=='' || $valx['status']==0)
                                {
                                    $showaction = $showaction + 1;
                                }
                            }
                            
                            if($valx['karyawanid']==$karyawanid && $valx['status']==0)
                            {
                                $level = $valx['level'];
                                $xxx = "conte";
                                break;
                            }
                        }
                    
                    if($xxx=="conte"){
                        break;
                    }
                }
			
                if ($showaction!=$level || $level==1) {
                    $tab.="<td style='text-align:center'>
                        <button class=mybutton onclick=\"getdataijs('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
                        </td>
                        <td style='text-align:center'>
                        <button class=mybutton onclick=\"tolakijs('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
                        </td>";
                } else {
                    $tab.="<td colspan=2>&nbsp;</td>";
                }

                for ($i = 1; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $bar['notransaksi'], 'IJS');
                    
                    $strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='IJS' and kodeunit='".$kodeorg."' and level='".$i."'";
                    $respo=fetchdata($strpo);
                    @$tipeapp = $respo[0]['tipe'];
                    @$departemenapp = $respo[0]['departemen'];
                    @$tipekaryawanapp = $respo[0]['tipekaryawan'];
                    @$jabatanapp = $respo[0]['jabatan'];
                    
                    if($tipeapp=='1'){
                        if($arrDetail['komentar']==''){
                            if($departemenapp!=''){
                                $opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
                                $arrDetail['nama'] = $opttipe[$departemenapp];
                            }
                            
                            if($tipekaryawanapp!=''){
                                $opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
                                $arrDetail['nama'] = $opttipe[$tipekaryawanapp];
                            }
                            
                            if($jabatanapp!='0'){
                                $opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
                                $arrDetail['nama'] = $opttipe[$jabatanapp];
                            }
                        }
                    }
				
                    if($arrDetail['nama']!='')
                    {
                        $tab.="<td style='vertical-align:top;text-align:center'>
                            <label style='text-align:center;font-weight:bold'>".$arrDetail['nama']."</label><br>
                            Status : ".$arrDetail['namastatus']."<br>
                            ".($arrDetail['komentar']==''?"":"Comment : ".$arrDetail['komentar'])."
                        </td>";
                    }
                    else
                    {
                        $tab.="<td style='text-align:center'>-</td>";
                    }
			}
			$tab.="</tr>";
		}
		$tab.="</tbody>
			<tfoot>
			</tfoot>
			</table>
			</fieldset>";
	break;
	break;
	case'get_form_approval_ijs':
		
        $sDat = selectQuery($dbname,"sdm_ijin","karyawanid,lokasitugas", "notransaksi = '".$notransaksi."'");
		$qDat = fetchData($sDat);
		$koderorg = $qDat[0]['lokasitugas'];
		
		$countApp = getCountApproval('IJS',$koderorg);

		for($i=1;$i<=$countApp;$i++){			
			$strx="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level='".$i."'";
			$resx=fetchdata($strx);
			foreach($resx as $keyx=>$valx){
				if($karyawanid==$valx['karyawanid']){
					if($i == $countApp){
						$tab.="<div id=approve>
							<input class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Approved</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick=\"return tanpa_kutip(event)\" />
									</td>
								</tr>
								<tr>
									<td colspan=3 align=center>
										<button id=Ajukan class=mybutton onclick=nextapprovalijs('approved') >Approved</button>
									</td>
								</tr>
							</table>
						</div>";
					}else{
						
						$level = $i+1;
						$nmkar=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
						$lktugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
						
                        $str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a
                        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
                        a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='IJS' and a.level='".$level."' and a.kodeunit='".$koderorg."'  order by b.namakaryawan asc";
                        $arrListApp=fetchData($str);
                        foreach($arrListApp as $val){
                             @$optKry.="<option value='".$val['karyawanid']."'>".$nmkar[$val['karyawanid']]." - [".$val['lokasitugas']."]</option>";
                        }
						

						$tab.="<div id=test style=display:block>
							<input align=center class=myinputtext disabled hidden type=text readonly=readonly name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
							<input hidden id=kolom value=".$_POST['kolom']."  />
							<table cellspacing=1 border=0>
								<tr>
									<td colspan=3>Submit to the next approval :</td>
								</tr>
								<tr>
									<td colspan=3><hr></td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['namakaryawan']."</td>
									<td>:</td>
									<td valign=top>
										<select id=user_id name=user_id  style=\"width:150px;\">".$optKry."</select>
									</td>
								</tr>
								<tr>
									<td>".$_SESSION['lang']['note']."</td>
									<td>:</td>
									<td>
										<input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:147px;\" />
									</td>
								</tr>
									<td colspan=2></td>
									<td>
										<button class=mybutton onclick=nextapprovalijs() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
									</td>
								</tr>
							</table>
							<input type=hidden name=notransaksi id=notransaksi value=".$_POST['notransaksi']."  />
						</div>";
					}
				}				
			}
        }
		echo $tab;
	break;
	case 'insert_nextapprovalijs':
		try {
		$owlPDO->beginTransaction();
		
		$jenisApp = "ijs";
		$tglskrng = date("Y-m-d H:i:s");
		$str = "select * from ".$dbname.".sdm_ijin where `notransaksi`='".$notransaksi."'"; #<=== SEBELUM
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch(); 
		

		$sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$bar['karyawanid']."'");
		$qDat = fetchData($sDat);
		$koderorg = $qDat[0]['lokasitugas'];

		$countApp = getCountApproval('IJS', $koderorg);
		// if ($countApp == 0) {
		// 	exit("warning : Approval belum di setup");
		// }
		if ($bar['approval'] == 1 || $bar['approval'] == 2 ) {
			throw new PDOException("Sudah di Approved/Ditolak");
		}else{
			$arrDetail = detailApprove($kolom, $notransaksi, 'IJS');
			$level = $kolom + 1;
			
			if ($kolom != $countApp) {
				if ($userid == $arrDetail['karyawanid']) {
					throw new PDOException(getNamaKaryawan($userid)." Sudah di gunakan");
				} else {
					$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$level."' and kodeunit='".$koderorg."'";
					$res=fetchData($str);
					$tipeapp = $res[0]['tipe'];
					$departemenapp = $res[0]['departemen'];
					$tipekaryawanapp = $res[0]['tipekaryawan'];
					$jabatanapp = $res[0]['jabatan'];
					$karyawanid_user = $res[0]['karyawaniduser'];
					
					if($tipeapp=='1'){
						if($departemenapp!=''){
							$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($tipekaryawanapp!=''){
							$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($jabatanapp!='0'){
							$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
						if($karyawanid_user!='0'){
							$str="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid_user."'";
							$res=fetchdata($str);
							foreach($res as $keyx=>$valx){
								$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenisApp."','".$level."','".$valx['karyawanid']."','0')";
								$owlPDO->exec($str);
							}
						}
					}else{
						$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','IJS','".$level."','".$userid."','0','','','')";
						$owlPDO->exec($str);
					}
					
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}

			} else {
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);

				$str="select b.lokasitugas, a.jumlahhari, a.karyawanid, a.periodecuti, a.darijam, a.sampaijam, a.idjenis  from ".$dbname.".sdm_ijin a 
					left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
					where a.notransaksi='".$notransaksi."'";
					$res=fetchdata($str);
					$kodeorg = $res[0]['lokasitugas'];
					$jumlahhari=$res[0]['jumlahhari'];
					$karyawancuti=$res[0]['karyawanid'];
					$periodecuti=$res[0]['periodecuti'];
					$daritanggal=substr($res[0]['darijam'],0,10);
					$daritanggaljam=$res[0]['darijam'];
					$sampaitanggal=substr($res[0]['sampaijam'],0,10);
					$sampaitanggaljam=$res[0]['sampaijam'];
					$idjenis=$res[0]['idjenis'];

				#ambil status potongan
					$strcuti="select statuspotongan from ".$dbname.".sdm_5jenisijin where idjenis='".$idjenis."'";
					$rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
					$rescuti->setFetchMode(PDO::FETCH_ASSOC);
					$barcuti=$rescuti->fetch();
					$statuspotongan=$barcuti['statuspotongan'];
						
						if ($statuspotongan == '0'){
							$strspl="update ".$dbname.".sdm_ijin set statuspersetujuan='1' where notransaksi='".$notransaksi."' ";             
							$owlPDO->exec($strspl);
							
						}else{

							$strspl="update ".$dbname.".sdm_ijin set statuspersetujuan='1' where notransaksi='".$notransaksi."' ";             
							$owlPDO->exec($strspl);

							#ambil sisa cuti ht
							$strcuti="select * from ".$dbname.".sdm_cutiht where kodeorg='".$kodeorg."' and karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."' ";
							$rescuti=$owlPDO->query($strcuti) or die(print " Gagal: ".PDOException::getMessage());
							$rescuti->setFetchMode(PDO::FETCH_ASSOC);
							$barcuti=$rescuti->fetch();

							$hakc_cutiht=$barcuti['hakcuti'];
							$cutt_cutiht=$barcuti['cutitambahan'];
							$adjs_cutiht=$barcuti['adjs_hakcuti'];
							$diam_cutiht=$barcuti['diambil'];
							$sisa_cutiht=$barcuti['sisa'];

							#= update sisa hak cuti
							$updatediambi = $diam_cutiht + $jumlahhari;
							$updatecutiht = ($hakc_cutiht + $cutt_cutiht + $adjs_cutiht) - $updatediambi;

							$variableA=$barcuti['sisa'];
							$variableB=$barcuti['sisa'] - $barcuti['cutitambahan'];
							$variableC=$variableB - $barcuti['adjs_hakcuti'];

							if($jumlahhari <= $variableC){
								## cek ada bulanan gak
								$str_bulanan = "SELECT * from ".$dbname.".sdm_cutibulananht where kodeorg='".$kodeorg."' and karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."' and sisa != 0 order by dari asc";
								$res_bulanan = fetchData($str_bulanan);
								$jumlahrow = count($res_bulanan);

								if($jumlahrow > 0){
									$cutitransaksi = $jumlahhari;
									foreach($res_bulanan as $bar){
										if ($bar['sisa'] > $cutitransaksi) {
											// Mengurangi sisa dengan cutitransaksi dan membuat cutitransaksi menjadi 0
											$sisaupdate = $bar['sisa'] - $cutitransaksi;
											$ambilupdate= $cutitransaksi;
											$cutitransaksi =0;
											$statusloop = 0;
										} else {
											// Mengurangi cutitransaksi dengan sisa saat ini dan membuat sisa menjadi 0
											$sisaupdate = 0 ;
											$ambilupdate  = $bar['sisa'];
											$statusloop = 1;
											$cutitransaksi =  $cutitransaksi - $bar['sisa'] ;
										}

										// Update cutiht
										$str = "UPDATE ".$dbname.".sdm_cutibulananht SET diambil=".$ambilupdate.", sisa='".$sisaupdate."' WHERE karyawanid='".$karyawancuti."' AND periodecuti='".$periodecuti."' AND dari='".$bar['dari']."' AND sampai='".$bar['sampai']."'";
										$owlPDO->exec($str);

										#= Update cutiht
										$str="update ".$dbname.".sdm_cutiht set diambil='".$updatediambi."',sisa = '".$updatecutiht."' where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
										$owlPDO->exec($str);

										// Jika cutitransaksi sudah 0, keluar dari loop
										if ($statusloop == 0) {
											break;
										}
									}

									#= insert dt
									$str="insert into ".$dbname.".sdm_cutidt(karyawanid, kodeorg, periodecuti,daritanggal,sampaitanggal,jumlahcuti,keterangan,statusdipotong) values('".$karyawancuti."','".$kodeorg."','".$periodecuti."','".$daritanggal."','".$sampaitanggal."','".$jumlahhari."','".$idjenis."','HAKCUTIBULANAN')";
									$owlPDO->exec($str);
								}else{
									#= Update cutiht
									$str="update ".$dbname.".sdm_cutiht set diambil='".$updatediambi."',sisa = '".$updatecutiht."' where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
									$owlPDO->exec($str);

									#= insert dt
									$str="insert into ".$dbname.".sdm_cutidt(karyawanid, kodeorg, periodecuti,daritanggal,sampaitanggal,jumlahcuti,keterangan,statusdipotong) values('".$karyawancuti."','".$kodeorg."','".$periodecuti."','".$daritanggal."','".$sampaitanggal."','".$jumlahhari."','".$idjenis."','HAKCUTI')";
									$owlPDO->exec($str);
								}
							}elseif($jumlahhari <= $variableB){

								#= Update cutiht
								$str="update ".$dbname.".sdm_cutiht set diambil='".$updatediambi."',sisa = '".$updatecutiht."' where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
								$owlPDO->exec($str);

								#= insert dt
								$str="insert into ".$dbname.".sdm_cutidt(karyawanid, kodeorg, periodecuti,daritanggal,sampaitanggal,jumlahcuti,keterangan,statusdipotong) values('".$karyawancuti."','".$kodeorg."','".$periodecuti."','".$daritanggal."','".$sampaitanggal."','".$jumlahhari."','".$idjenis."','ADJSMENCUTI')";
								$owlPDO->exec($str);

							}else{
								## cek ada tambahan gak
								$str_tambahan = "SELECT * from ".$dbname.".sdm_cutitambahanht where kodeorg='".$kodeorg."' and karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."' and sisa != 0 ";
								$res_tambahan = fetchData($str_tambahan);
								$jumlahrow = count($res_tambahan);

								if($jumlahrow > 0){
								
									foreach($res_tambahan as $bar){
										$hakc_cutiht_tambahan=$bar['hakcuti'];
										$diam_cutiht_tambahan=$bar['diambil'];
										$sisa_cutiht_tambahan=$bar['sisa'];
									}

									#= update sisa hak cuti
									$updatediambi_tambahan = $diam_cutiht_tambahan + $jumlahhari;
									$updatecutiht_tambahan = $hakc_cutiht_tambahan - $updatediambi_tambahan;

									// Update cutiht tambahan
									$str = "UPDATE ".$dbname.".sdm_cutitambahanht SET diambil=".$updatediambi_tambahan.", sisa='".$updatecutiht_tambahan."' WHERE karyawanid='".$karyawancuti."' AND periodecuti='".$periodecuti."' AND dari='".$bar['dari']."' AND sampai='".$bar['sampai']."'";
									$owlPDO->exec($str);

									#= Update cutiht
									$str="update ".$dbname.".sdm_cutiht set diambil='".$updatediambi."',sisa = '".$updatecutiht."' where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
									$owlPDO->exec($str);

									#= insert dt
									$str="insert into ".$dbname.".sdm_cutidt(karyawanid, kodeorg, periodecuti,daritanggal,sampaitanggal,jumlahcuti,keterangan,statusdipotong) values('".$karyawancuti."','".$kodeorg."','".$periodecuti."','".$daritanggal."','".$sampaitanggal."','".$jumlahhari."','".$idjenis."','CUTITAMBAHAN')";
									$owlPDO->exec($str);

								}else{

									#= Update cutiht
									$str="update ".$dbname.".sdm_cutiht set diambil='".$updatediambi."',sisa = '".$updatecutiht."' where karyawanid='".$karyawancuti."' and periodecuti='".$periodecuti."'";
									$owlPDO->exec($str);

									#= insert dt
									$str="insert into ".$dbname.".sdm_cutidt(karyawanid, kodeorg, periodecuti,daritanggal,sampaitanggal,jumlahcuti,keterangan,statusdipotong) values('".$karyawancuti."','".$kodeorg."','".$periodecuti."','".$daritanggal."','".$sampaitanggal."','".$jumlahhari."','".$idjenis."','HAKCUTI')";
									$owlPDO->exec($str);
								}
							}
						}

						## Start insert absensi
						#ambil unit dari karyawan tsb
						$str = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $karyawancuti . "'";
						$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar = $res->fetch();
						$unit = $bar['lokasitugas'];
						$subbagian = $bar['subbagian'];

						$arrDate = [];
                        // Looping tanggal dari dan tanggal sampai dan buat array tanggal between tanggal tersebut
                        $strTimeDari = strtotime($daritanggal);
                        $strTimeSampai = strtotime($sampaitanggal);
                        while ($strTimeDari <= $strTimeSampai) {
                            $arrDate[] = date('Y-m-d', $strTimeDari);
                            $strTimeDari = strtotime("+1 day", $strTimeDari);
                        }

                        if ($subbagian == "") {
                            $subbagian = $kodeorg;
                        }

						foreach ($arrDate as $dateCuti) {

							$sLibur = selectQuery($dbname, "sdm_5harilibur", "*", "tanggal = '".$dateCuti."' AND keterangan = 'libur' AND kebun = '".$kodeorg."'");
							$qLibur = fetchData($sLibur);
							
							if (count($qLibur) > 0) {
								continue;
							} else {
								// ======================================= BEGIN THIS LINE DEVELOPED BY AZIZAH ELVIA, DATE: 2025-10-30 =======================================
								// Cek Dulu Sudah Ada Data Absensi DT atau belum, karena ada kejadian dimana approval belum selesai persetujuan sampai level terakhir
								// Namun data cuti di sdm_absensidt sudah ke insert data nya.
								$cekAbsdt = getCountRows($dbname,"sdm_absensidt","kodeorg='" . $subbagian . "' AND tanggal='" . $dateCuti . "' AND karyawanid='" . $karyawancuti . "' AND absensi='" . $idjenis . "' AND penjelasan='By Sistem Cuti' AND norefrensi='" . $notransaksi . "'");
								if ($cekAbsdt > 0) {
									$sDelDt = deleteQuery($dbname,"sdm_absensidt","kodeorg='" . $subbagian . "' AND tanggal='" . $dateCuti . "' AND karyawanid='" . $karyawancuti . "' AND absensi='" . $idjenis . "' AND penjelasan='By Sistem Cuti' AND norefrensi='" . $notransaksi . "'");
									$owlPDO->exec($sDelDt);
								}
								// ======================================= END THIS LINE DEVELOPED BY AZIZAH ELVIA, DATE: 2025-10-30 =======================================

								$cekAbsensi = getCountRows($dbname, "sdm_absensiht", "kodeorg='" . $subbagian . "' AND periode='" . substr($dateCuti, 0, 7) . "' AND tanggal='" . $dateCuti . "'");
								if ($cekAbsensi == 0) {
									$dataHt = [
										"tanggal" => $dateCuti,
										"kodeorg" => $subbagian,
										"periode" => substr($dateCuti, 0, 7),
										"posting" => 0,
										"updateby" => $karyawancuti,
									];
									$colsHt = array_keys($dataHt);
									$insertHt = insertQuery($dbname, "sdm_absensiht", $dataHt, $colsHt);
									$owlPDO->exec($insertHt);
	
									$dataDt = [
										"kodeorg" => $subbagian,
										"tanggal" => $dateCuti,
										"karyawanid" => $karyawancuti,
										"absensi" => $idjenis,
										"penjelasan" => "By Sistem Cuti",
										"norefrensi" => $notransaksi,
									];
	
									$colsDt = array_keys($dataDt);
									$insertDt = insertQuery($dbname, "sdm_absensidt", $dataDt, $colsDt);
									$owlPDO->exec($insertDt);
	
								} else {
	
									$dataDt = [
										"kodeorg" => $subbagian,
										"tanggal" => $dateCuti,
										"karyawanid" => $karyawancuti,
										"absensi" => $idjenis,
										"penjelasan" => "By Sistem Cuti",
										"norefrensi" => $notransaksi,
									];
	
									$colsDt = array_keys($dataDt);
									$insertDt = insertQuery($dbname, "sdm_absensidt", $dataDt, $colsDt);
									$owlPDO->exec($insertDt);
								}
							}
                        }
						## end insert absensi					
			}
		}
		
			#EXECUTE
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	case 'tolakijs':
	echo"<div id=rejected_form>
		<input hidden id=notransaksi value=".$_POST['notransaksi']."  />
		<table cellspacing=1 border=0>
		<tr>
		    <td colspan=3>Rejection</td>
        </tr>
		<tr>
		<tr>
            <td colspan=3><hr></td>
        </tr>
		    <td>".$_SESSION['lang']['note']."</td>
		    <td>:</td>
		    <td>
                <input style=width:200px type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" />
            </td>
		</tr>
		<tr>
            <td colspan=3 align=center>
	            <button class=mybutton onclick=\"inserttolakijs(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		    </td>
        </tr>
        </table>
		</div>";
	break;
	case 'inserttolakijs':    
		    
        $sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		$koderorg = $qDat[0]['lokasitugas'];

		$countApp = getCountApproval('IJS',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'IJS');
		$tglskrng=date("Y-m-d H:i:s");

		$str="update ".$dbname.".sdm_ijin set statuspersetujuan='2' where notransaksi='".$notransaksi."'"; 

		try{$owlPDO->exec($str); 
			$str="update ".$dbname.".approval set status='2', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."'";
			try{
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>
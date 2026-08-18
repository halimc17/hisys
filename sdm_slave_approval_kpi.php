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
	case'KPI':
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['detail']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
                <th align=center>No.</th>
                <th align=center>".$_SESSION['lang']['namakaryawan']."</th>
                <th align=center>".$_SESSION['lang']['tahun']."</th>
                <th align=center>".$_SESSION['lang']['tanggal']."</th>
                <th align=center>".$_SESSION['lang']['view']."</th>
                <th colspan='2' align='center'>Verification</th>";

            if ($lvlapr!=0) {
                $countApp=$lvlapr;
            }else { 
                $countApp = getCountApproval('KPI');
            }
            for ($i = 1; $i <= $countApp; $i++) {
                $tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
            }

		$tab.="</tr>
			</thead>
			<tbody>";

            $str = "select * from ".$dbname.".approval a
                left join ".$dbname.".sdm_kpi b on a.notransaksi = b.id
                where a.jenispersetujuan='KPI' and a.status='0' and a.karyawanid='".$karyawanid."' order by a.tanggal asc";
                $res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
			 
                $optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='".$bar['kodeorg']."'");

                $no++;
                $tab.="<tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td align=left>".getNamaKaryawan($bar['karyawanid'])."</td>
                    <td align=center>".$bar['tahun']."</td>
                    <td align=center>".$bar['tanggal']."</td>
                    <td align=center nowrap>
						<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdfkpi('".$bar['id']."');\">
						<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detailkpi('".$bar['id']."');\">
					</td>";
                    
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
                        <button class=mybutton onclick=\"getdatakpi('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['approve']."</button>
                        </td>
                        <td style='text-align:center'>
                        <button class=mybutton onclick=\"tolakkpi('".$bar['notransaksi']."','".$level."')\">".$_SESSION['lang']['ditolak']."</button>
                        </td>";
                } else {
                    $tab.="<td colspan=2>&nbsp;</td>";
                }

                for ($i = 1; $i <= $countApp; $i++) {
                    $arrDetail = detailApprove($i, $bar['notransaksi'], 'KPI');
                    
                    $strpo="select * from ".$dbname.".setup_approval where jenispersetujuan='KPI' and kodeunit='".$kodeorg."' and level='".$i."'";
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
	case'get_form_approval_kpi':
		
        $sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		
		$koderorg = $qDat[0]['lokasitugas'];
		
		$countApp = getCountApproval('KPI',$koderorg);

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
										<button id=Ajukan class=mybutton onclick=nextapprovalkpi('approved') >Approved</button>
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
                        a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='KPI' and a.level='".$level."' and a.kodeunit='".$koderorg."'  order by b.namakaryawan asc";
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
										<button class=mybutton onclick=nextapprovalkpi() title=\" Submit to the next level\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
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
	case 'insert_nextapprovalkpi':
		try {
		$owlPDO->beginTransaction();
		
		$jenisApp = "KPI";

        $sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		$koderorg = $qDat[0]['lokasitugas'];
		
		$countApp = getCountApproval('KPI', $koderorg); 
		
		$tglskrng = date("Y-m-d H:i:s");
		$str = "select * from ".$dbname.".sdm_kpi where `id`='".$notransaksi."'"; #<=== SEBELUM
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch(); 
		if ($bar['approval'] == 1 || $bar['approval'] == 2 ) {
			throw new PDOException("Sudah di Approved/Ditolak");
		}else{
			$arrDetail = detailApprove($kolom, $notransaksi, 'KPI');
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
					}else{
						$str = "insert into ".$dbname.".approval values ('','".$notransaksi."','KPI','".$level."','".$userid."','0','','','')";
						$owlPDO->exec($str);
					}
					
					$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
					$owlPDO->exec($strx);
					
					$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
					$owlPDO->exec($str);
				}

			} else {
                $strc = "update ".$dbname.".sdm_kpi set approval='1',posting='1' where id='".$notransaksi."'";
				$owlPDO->exec($strc);
				
				$strx = "update ".$dbname.".approval set status='1', komentar='".$comment."', tanggal='".$tglskrng."' where notransaksi='".$notransaksi."' and level='".$kolom."' and karyawanid='".$karyawanid."'";
				$owlPDO->exec($strx);

				$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."' and karyawanid!='".$karyawanid."' and level='".$kolom."'";
				$owlPDO->exec($str);
					
			}
		}
		
			#EXECUTE
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
	break;
	case 'tolakkpi':
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
	            <button class=mybutton onclick=\"inserttolakkpi(".$_POST['kolom'].")\" >".$_SESSION['lang']['ditolak']."</button>
		    </td>
        </tr>
        </table>
		</div>";
	break;
	case 'inserttolakkpi':        
        $sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$_SESSION['standard']['userid']."'");
		$qDat = fetchData($sDat);
		$koderorg = $qDat[0]['lokasitugas'];

		$countApp = getCountApproval('KPI',$koderorg);
		$arrDetail = detailApprove($kolom,$notransaksi,'KPI');
		$tglskrng=date("Y-m-d H:i:s");

		$str="update ".$dbname.".sdm_kpi set approval='2' where id='".$notransaksi."'"; 

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
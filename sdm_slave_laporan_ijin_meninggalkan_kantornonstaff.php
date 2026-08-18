<?php

// session_start();
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');

$periodeawal = checkPostGet('periodeawal', '');
$periodeakhir = checkPostGet('periodeakhir', '');
$exelcuti = checkPostGet('exelcuti', '');
$excelkaryawanid = checkPostGet('excelkaryawanid', '');
$proses = checkPostGet('proses', '');
$tglijin = tanggalsystem(checkPostGet('tglijin', ''));
$krywnId = checkPostGet('krywnId', '');
$stat = checkPostGet('stat', '');
$ket = checkPostGet('ket', '');
$jnsCuti = checkPostGet('jnsCuti', '');
$karyidCari = checkPostGet('karyidCari', '');
$atasan = checkPostGet('atasan', '');
$statpp1 = checkPostGet('statpp1', '');
$statpp2 = checkPostGet('statpp2', '');
$stathrd = checkPostGet('stathrd', '');


$arrNmkary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$arrKeputusan = array("0" => $_SESSION['lang']['diajukan'], "1" => $_SESSION['lang']['disetujui'], "2" => $_SESSION['lang']['ditolak']);
$where = " tanggal='" . $tglijin . "' and karyawanid='" . $krywnId . "'";
$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arragama = getEnum($dbname, 'sdm_ijinnonstaff', 'jenisijin');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$pAwal = tanggalsystem($periodeawal);
$pAkhir = tanggalsystem($periodeakhir);
$nmjab=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

$nmdep=makeOption($dbname,'sdm_5departemen','kode,nama');


//exit("Error".$jmAwal);
switch ($proses) {

	
    case'loadData':
        $limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $tmbWhere = '';
        if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ( $_SESSION['empl']['bagian'] == 'HRA' || $_SESSION['empl']['bagian'] == 'HHRS')) {
            $tmbWhere = " and a.karyawanid like '%" . $karyidCari . "%' and stpersetujuan1 like '%" . $statpp1 . "%' and stpersetujuan4 like '%" . $statpp2 . "%' and stpersetujuanhrd like '%" . $stathrd . "%' ";
        } else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA') {
            $tmbWhere = " and a.karyawanid like '%" . $karyidCari . "%' and stpersetujuan1 like '%" . $statpp1 . "%' and stpersetujuan4 like '%" . $statpp2 . "%' and stpersetujuanhrd like '%" . $stathrd . "%' ";
        } else {
            $tmbWhere = " and a.karyawanid like '%" . $karyidCari . "%' and stpersetujuan1 like '%" . $statpp1 . "%' and stpersetujuan4 like '%" . $statpp2 . "%' and stpersetujuanhrd like '%" . $stathrd . "%' ";
        }

        $ql2 = "select count(a.karyawanid) as jmlhrow from " . $dbname . ".sdm_ijinnonstaff a, " . $dbname . ".datakaryawan b
		where a.karyawanid = b.karyawanid and a.tanggal between '" . $pAwal . "' and '" . $pAkhir . "' 
		and a.jenisijin like '%" . $jnsCuti . "%' " . $tmbWhere . " order by a.tanggal desc";
		
		$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        if ($jlhbrs <= 0) {
            echo"<tr class=rowcontent>
							<td colspan=14 style='text-align:center'>" . $_SESSION['lang']['datanotfound'] . "</td>
						</tr>";
            exit();
        }

        $slvhc = "select a.* from " . $dbname . ".sdm_ijinnonstaff a, " . $dbname . ".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '" . $pAwal . "' and '" . $pAkhir . "' and a.jenisijin like '%" . $jnsCuti . "%' " . $tmbWhere . " order by a.tanggal desc limit " . $offset . "," . $limit . " ";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online = $_SESSION['standard']['userid'];
        while ($rlvhc = $qlvhc->fetch()) {
            // if ($_SESSION['language'] == 'ID') {
            //     $dd = $rlvhc['jenisijin'];
            // } else {
            //     switch ($rlvhc['jenisijin']) {
            //         case 'TERLAMBAT':
            //             $dd = 'Late for work';
            //             break;
            //         case 'KELUAR':
            //             $dd = 'Out of Office';
            //             break;
            //         case 'PULANGAWAL':
            //             $dd = 'Home early';
            //             break;
            //         case 'IJINLAIN':
            //             $dd = 'Other purposes';
            //             break;
            //         case 'CUTI':
            //             $dd = 'Leave';
            //             break;
            //         case 'MELAHIRKAN':
            //             $dd = 'Maternity';
            //             break;
            //         default:
            //             $dd = 'Wedding, Circumcision or Graduation';
            //             break;
            //     }
            // }
            $no+=1;
            //ambil sisa cuti
            // $sSisa = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid='" . $rlvhc['karyawanid'] . "' 
                        // order by periodecuti desc limit 1";
			$sSisa="select * from ".$dbname.".sdm_cutiht where karyawanid='".$rlvhc['karyawanid']."' and periodecuti='".substr($rlvhc['tanggal'],0,4)."' ";
			
				// $hakcuti=$bar['hakcuti'];
				// $sisa=$bar['sisa'];			
				
			$qSisa=$owlPDO->query($sSisa) or die(print " Gagal: ".PDOException::getMessage());
			$qSisa->setFetchMode(PDO::FETCH_ASSOC);
            $rSisa = $qSisa->fetch();
            $nmAkun = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');
            echo"
                <tr class=rowcontent>
                <td>" . $no . "</td>
                <td>" . tanggalnormal($rlvhc['tanggal']) . "</td>
                <td>" . $arrNmkary[$rlvhc['karyawanid']] . "</td>
                <td>" . $rlvhc['keperluan'] . "</td>
                <td>" . (isset($nmAkun[$rlvhc['idjenis']]) ? $nmAkun[$rlvhc['idjenis']] : '') . "</td>
				 <td>" . $rlvhc['darijam'] . "</td>
                <td>" . $rlvhc['sampaijam'] . "</td>
				 <td align=center>" . $rlvhc['jumlahhari'] . "</td>
					<td align=center>" . $rSisa['sisa'] . "</td>";
				
				//persetujuan no 1
				if($rlvhc['persetujuan1']==$_SESSION['standard']['userid'] and $rlvhc['stpersetujuan1']==0){
					echo"<td align=center>
                          <button class=mybutton id=dtlForm onclick=appSetuju('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "')>" . $_SESSION['lang']['disetujui'] . "</button>
                          <button class=mybutton id=dtlForm onclick=showAppTolak('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)>" . $_SESSION['lang']['ditolak'] . "</button>
                          </td>";
						  //<button class=mybutton id=dtlForm onclick=showAppForw('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)>Forward</button>
				}else{
					 echo"<td align=center>" . $arrNmkary[$rlvhc['persetujuan1']] . " <br> <b>" . $arrKeputusan[$rlvhc['stpersetujuan1']] . "</td>";
				}
				
				//no 2
				if($rlvhc['persetujuan4']==$_SESSION['standard']['userid'] and $rlvhc['stpersetujuan4']==0){
					echo"<td align=center>
                          <button class=mybutton id=dtlForm onclick=appSetuju4('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "')>" . $_SESSION['lang']['disetujui'] . "</button>
                          <button class=mybutton id=dtlForm onclick=showAppTolak4('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)>" . $_SESSION['lang']['ditolak'] . "</button>
                          </td>";
						  //<button class=mybutton id=dtlForm onclick=showAppForw('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)>Forward</button>
				}else{
					 echo"<td align=center>" . $arrNmkary[$rlvhc['persetujuan4']] . " <br> <b>" . $arrKeputusan[$rlvhc['stpersetujuan4']] . "</td>";
				}
				
				//hrd
				if($rlvhc['hrd']==$_SESSION['standard']['userid'] and $rlvhc['stpersetujuanhrd']==0 and $rlvhc['stpersetujuan1']==1 and $rlvhc['stpersetujuan4']==1){
					echo"<td align=center>
                          <button class=mybutton id=dtlForm onclick=appSetujuHRD('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "')>" . $_SESSION['lang']['disetujui'] . "</button>
                          <button class=mybutton id=dtlForm onclick=showAppTolakHRD('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)>" . $_SESSION['lang']['ditolak'] . "</button>
                          </td>";
						  
				}else{
						if($rlvhc['stpersetujuan1']==2 or $rlvhc['stpersetujuan4']==2){
						echo"<td align=center>" . $arrNmkary[$rlvhc['hrd']] . " <br> <b>Ditolak</td>";
					}else if($rlvhc['stpersetujuan1']==1 or $rlvhc['stpersetujuan4']==1 or $rlvhc['stpersetujuan1']==0 or $rlvhc['stpersetujuan4']==0){
						echo"<td align=center>" . $arrNmkary[$rlvhc['hrd']] . " <br> <b>" . $arrKeputusan[$rlvhc['stpersetujuanhrd']] . "</td>";
						
					}
				}
              

            echo"<td align=center> <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"previewPdf('" . tanggalnormal($rlvhc['tanggal']) . "','" . $rlvhc['karyawanid'] . "',event)\"></td>";
        }//end while
        echo"
                </tr><tr class=rowheader><td colspan=13 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        break;
    case'appSetuju':
        $sket = "select distinct idjenis,stpersetujuan1,persetujuan1,hrd,tanggal from " . $dbname . ".sdm_ijinnonstaff where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();

        if ($stat == 1) {
            $ket = "permintaaan " . $arrNmkary[$krywnId] . " " . $arrKeputusan[$stat] . "";
        }

        $sUpdate = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuan1='" . $stat . "',komenst1='" . $ket . "' where " . $where . "";
		try{
			$owlPDO->exec($sUpdate); 
			
			#send an email to incharge person
            $to = getUserEmail($rKet['hrd']); ////email ke hrd setelah persetujuan atasan
            $namakaryawan = $arrNmkary[$krywnId];
            $subject = "[Notifikasi]Persetujuan Cuti / Ijin a/n " . $namakaryawan;
            $body = "<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  " . tanggalnormal($rKet['tanggal']) . " karyawan a/n  " . $namakaryawan . " telah " . $arrKeputusan[$stat] . ". 
                                               Oleh atasan ybs. Selanjutnya, mohon persetujuan dari HRD. Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
            $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
		
		if ($stat == 2) {
	        $str = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuanhrd='2', komenst2= 'Di tolak oleh Atasan 1 ".$nmkar[$_SESSION['standard']['userid']]."' where " . $where . "";
			try {
            $owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
		}
		
        break;
		
	case'appSetuju4':
        $sket = "select distinct idjenis,stpersetujuan4,persetujuan4,hrd,tanggal from " . $dbname . ".sdm_ijinnonstaff where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();

        if ($stat == 1) {
            $ket = "permintaaan " . $arrNmkary[$krywnId] . " " . $arrKeputusan[$stat] . "";
        }

        $sUpdate = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuan4='" . $stat . "',komenst4='" . $ket . "' where " . $where . "";
		try{
			$owlPDO->exec($sUpdate); 
			
			#send an email to incharge person
            $to = getUserEmail($rKet['hrd']); ////email ke hrd setelah persetujuan atasan
            $namakaryawan = $arrNmkary[$krywnId];
            $subject = "[Notifikasi]Persetujuan Cuti / Ijin  a/n " . $namakaryawan;
            $body = "<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  " . tanggalnormal($rKet['tanggal']) . " karyawan a/n  " . $namakaryawan . " telah " . $arrKeputusan[$stat] . ". 
                                               Oleh atasan ybs. Selanjutnya, mohon persetujuan dari HRD. Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
            $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
		
		if ($stat == 2) {
	        $str = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuanhrd='2', komenst2= 'Di tolak oleh Atasan 2 ".$nmkar[$_SESSION['standard']['userid']]."' where " . $where . "";
			try {
            $owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
		}
		
	break;	

    case 'appSetujuHRD':
        $sket = "select distinct darijam,sampaijam,jumlahhari,idjenis,stpersetujuanhrd,hrd,tanggal,periodecuti from " . $dbname . ".sdm_ijinnonstaff where " . $where . "";
		$qKet=$owlPDO->query($sket) or die(print " Gagal: ".PDOException::getMessage());
		$qKet->setFetchMode(PDO::FETCH_ASSOC);
        $rKet = $qKet->fetch();
		
        if ($stat == 1) {
            $ket = "permintaaan " . $arrNmkary[$krywnId] . " " . $arrKeputusan[$stat] . "";
            //===============insert to sdm_cuti

            $stru = "select lokasitugas from " . $dbname . ".datakaryawan where karyawanid=" . $krywnId;
			$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
			$resu->setFetchMode(PDO::FETCH_OBJ);
            $kodeorg = '';
            while ($baru = $resu->fetch()) {
                $kodeorg = $baru->lokasitugas;
            }
            if ($kodeorg == '')
                exit('Error: Karywan tidak memiliki loaksi tugas');

            if ($rKet['idjenis'] == 'CUTI') {
			//if ($rKet['jenisijin'] == 'CUTI' or $rKet['jenisijin'] == 'MELAHIRKAN' or $rKet['jenisijin'] == 'KAWIN/SUNATAN/WISUDA') {
                //insert to cuti
                $str = "insert into " . $dbname . ".sdm_cutidt 
                                (kodeorg,karyawanid,periodecuti,daritanggal,
                                    sampaitanggal,jumlahcuti,keterangan
                                    )
                                values('" . $kodeorg . "'," . $krywnId . ",
                                    '" . $rKet['periodecuti'] . "','" . substr($rKet['darijam'], 0, 10) . "','" . substr($rKet['sampaijam'], 0, 10) . "'," . $rKet['jumlahhari'] . ",'" . $rKet['idjenis'] . "'
                                    )";
									
				try{
					$owlPDO->exec($str); 
					
					//ambil sum jumlah diambil dan update table header
                    $strx = "select sum(jumlahcuti) as diambil from " . $dbname . ".sdm_cutidt
                                    where kodeorg='" . $kodeorg . "' and keterangan = 'CUTI'
                                        and karyawanid=" . $krywnId . "
                                        and periodecuti='" . $rKet['periodecuti'] . "'";

                    $diambil = 0;
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_OBJ);
                    while ($barx = $resx->fetch()) {
                        $diambil = $barx->diambil;
                    }
                    if ($rKet['idjenis'] == 'CUTI08')
                        if ($diambil == '')
                            $diambil = 0;
                    $strup = "update " . $dbname . ".sdm_cutiht set diambil=" . $diambil . ",sisa=(hakcuti-" . $diambil . ")	
                                    where kodeorg='" . $kodeorg . "'
                                        and karyawanid=" . $krywnId . "
                                        and periodecuti='" . $rKet['periodecuti'] . "'";

                    if ($rKet['idjenis'] == 'CUTI')
						try{
							$owlPDO->exec($strup); 
						}catch (PDOException $e){
							die();
						}
				}catch (PDOException $e){
					echo $e->getMessage();
                    exit("Error: Update table cuti");
				}
            }  
			
			if ($rKet['idjenis'] == 'CUTI05' || $rKet['idjenis'] == 'CUTI06' || $rKet['idjenis'] == 'CUTI09') {
				 $str = "insert into " . $dbname . ".sdm_5cutilaindt 
                                (kodeorg,karyawanid,periodecuti,daritanggal,
                                    sampaitanggal,jumlahcuti,keterangan
                                    )
                                values('" . $kodeorg . "'," . $krywnId . ",
                                    '" . $rKet['periodecuti'] . "','" . substr($rKet['darijam'], 0, 10) . "','" . substr($rKet['sampaijam'], 0, 10) . "'," . $rKet['jumlahhari'] . ",'" . $rKet['idjenis'] . "'
                                    )";
									
				try{
					$owlPDO->exec($str); 
					
					//ambil sum jumlah diambil dan update table header
                    $strx = "select sum(jumlahcuti) as diambil from " . $dbname . ".sdm_5cutilaindt
                                    where kodeorg='" . $kodeorg . "' and keterangan = '".$rKet['idjenis']."'
                                        and karyawanid=" . $krywnId . "
                                        and periodecuti='" . $rKet['periodecuti'] . "'";

                    $diambil = 0;
					$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
					$resx->setFetchMode(PDO::FETCH_OBJ);
                    while ($barx = $resx->fetch()) {
                        $diambil = $barx->diambil;
                    }
                    
                    $strup = "update " . $dbname . ".sdm_5cutilainht set diambil=" . $diambil . ",sisa=(hakcuti-" . $diambil . ")	
                                    where kodeorg='" . $kodeorg . "'
                                        and karyawanid=" . $krywnId . "
                                        and periodecuti='" . $rKet['periodecuti'] . "'
										and jeniscuti='".$rKet['idjenis']."'";
						try{
							$owlPDO->exec($strup); 
						}catch (PDOException $e){
							die();
						}
				}catch (PDOException $e){
					echo $e->getMessage();
                    exit("Error: Update table cuti");
				}
			}
			
			
            $sUpdate = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuanhrd='" . $stat . "',komenst2='" . $ket . "' where " . $where . "";
			try{
				$owlPDO->exec($sUpdate); 
				
				#send an email to incharge person
                $to = getUserEmail($rKet['hrd']);
                $namakaryawan = getNamaKaryawan($krywnId);
                $subject = "[Notifikasi]Persetujuan Cuti / Ijin a/n " . $namakaryawan;
                $body = "<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  " . tanggalnormal($rKet['tanggal']) . " karyawan a/n  " . $namakaryawan . " telah " . $arrKeputusan[$stat] . ". 
                                                   Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
                $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
			
			
				#kirim email balik ke pembuat bahwa cutinya disetujui
				#send an email to incharge person
                $to = getUserEmail($krywnId);
                $namakaryawan = getNamaKaryawan($krywnId);
                $subject = "[Notifikasi]Persetujuan Cuti / Ijin a/n " . $namakaryawan;
                $body = "<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  " . tanggalnormal($rKet['tanggal']) . " karyawan a/n  " . $namakaryawan . " telah " . $arrKeputusan[$stat] . ". 
                                                   Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
                $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
			
			
		} else if ($stat == 2) {
		$sUpdate = "update " . $dbname . ".sdm_ijinnonstaff  set stpersetujuanhrd='" . $stat . "',komenst2='" . $ket . "' where " . $where . "";
			try{
				$owlPDO->exec($sUpdate); 
				
				#send an email to incharge person
                $to = getUserEmail($rKet['hrd']);
                $namakaryawan = getNamaKaryawan($krywnId);
                $subject = "[Notifikasi]Persetujuan Cuti / Ijin a/n " . $namakaryawan;
                $body = "<html>
                                             <head>
                                             <body>
                                               <dd>Dengan Hormat,</dd><br>
                                               <br>
                                               Permintaan persetujuan Ijin/Cuti pada  " . tanggalnormal($rKet['tanggal']) . " karyawan a/n  " . $namakaryawan . " telah " . $arrKeputusan[$stat] . ". 
                                                   Untuk melihat lebih detail, silahkan ikuti link dibawah.
                                               <br>
                                               <br>
                                               <br>
                                               Regards,<br>
                                               Owl-Plantation System.
                                             </body>
                                             </head>
                                           </html>
                                           ";
                $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}
        break;

    case'prevPdf':
		$stf=0;
         $str = "select * from " . $dbname . ".sdm_ijinnonstaff where " . $where . "";
		$res=fetchdata($str);
		if(count($res)<=0){
			$str = "select * from " . $dbname . ".sdm_ijin where " . $where . "";
			$stf=1;
		}
		
		
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $strc = "select a.namakaryawan,a.karyawanid,a.bagian,b.namajabatan,a.nik,a.lokasipenerimaan,a.kodeorganisasi,a.lokasitugas from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b
                      on a.kodejabatan=b.kodejabatan where a.karyawanid=" . $bar->karyawanid;
					  // exit('error: '.$strc);
      			$resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
      			$resc->setFetchMode(PDO::FETCH_OBJ);
              while ($barc = $resc->fetch()) {
                $jabatan = $barc->namajabatan;
				$kodeorganisasi = $barc->kodeorganisasi;
                $lokasitugas	= $barc->lokasitugas;
				$namakaryawan = $barc->namakaryawan;
				$nikaryawan = $barc->nik;
                $bagian = $barc->bagian;
                $karyawanid = $barc->karyawanid;
                $poh=$barc->lokasipenerimaan;
            }
			
            $strc = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=" . $bar->pengganti;
            $resc=$owlPDO->query($strc) or die(print " Gagal: ".PDOException::getMessage());
            $resc->setFetchMode(PDO::FETCH_OBJ);
            $barc = $resc->fetch();
            $pengganti=$barc->namakaryawan;

            //===============================	  

            $nohp=$bar->nohp;
            $jenisnya=$bar->idjenis;
            $nomortrans=$bar->notransaksi;
            $perstatus = $bar->stpersetujuan1;
            $perstatus4 = $bar->stpersetujuan4;
            $tgl = tanggalnormal($bar->tanggal);
            $tglmasuk = tanggalnormal($bar->tanggalkerja);
            $tglberangkat = tanggalnormal($bar->tanggalberangkat);
            $tglpulang = tanggalnormal($bar->tglpulang);
            $ruteberangkat = $bar->rutekeberangkatan;
            $rutepulang = $bar->rutekepulangan;
            if ($bar->statuspersetujuan_cancel == 1 || $bar->statuspersetujuan_cancel == 9) {
                $jumlahhari = 0;
            } else {
                $jumlahhari = $bar->jumlahhari;
            }
            $kperluan = $bar->keperluan;
            $persetujuan = $bar->persetujuan1;
            $persetujuan4 = $bar->persetujuan4;
            $jnsCuti = $bar->jenisijin;
            $jmDr = $bar->darijam;
            $jmSmp = $bar->sampaijam;
            $koments = $bar->komenst1;
            $koments4 = $bar->komenst4;
            $ket = $bar->keterangan;
            $periode = $bar->periodecuti;
            $sthrd = $bar->stpersetujuanhrd;
            $hk = $bar->jumlahhari;
            $hrd = $bar->hrd;
            $koments2 = $bar->komenst2;
            $statuspersetujuan_cancel = $bar->statuspersetujuan_cancel;
            $idjenis = $bar->idjenis;

            function getnamajeniscuti($idjenis,$datajenis){
                $result = '';
                if(isset($datajenis[$idjenis])){
                    $result = $datajenis[$idjenis];
                }
                return $result;
            }

            // if ($_SESSION['language'] == 'ID') {
            //     $dd = $jns;
            // } else {
            //     switch ($jns) {
            //         case 'TERLAMBAT':
            //             $dd = 'Late for work';
            //             break;
            //         case 'KELUAR':
            //             $dd = 'Out of Office';
            //             break;
            //         case 'PULANGAWAL':
            //             $dd = 'Home early';
            //             break;
            //         case 'IJINLAIN':
            //             $dd = 'Other purposes';
            //             break;
            //         case 'CUTI':
            //             $dd = 'Leave';
            //             break;
            //         case 'MELAHIRKAN':
            //             $dd = 'Maternity';
            //             break;
            //         default:
            //             $dd = 'Wedding, Circumcision or Graduation';
            //             break;
            //     }
            // }

            // get nama  dan kode organisasi
			
            $snamaorg = "select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi = '".$kodeorganisasi."' and tipe='PT'";

            $datajeniscuti = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');
			
            //print_r($snamaorg);
			$namaorg = "-";
            $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
            $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
            while($rnamaorg=$qnamaorg->fetch()){
				$namaorg=$rnamaorg->namaorganisasi;
			}
            //ambil bagian,jabatan persetujuan atasan
            $perjabatan = '';
            $perbagian = '';
            $pernama = '';
            $strf = "select a.bagian,b.namajabatan,a.namakaryawan from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=" . $persetujuan;
      			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
      			$resf->setFetchMode(PDO::FETCH_OBJ);
              while ($barf = $resf->fetch()) {
                $perjabatan = $barf->namajabatan;
                $perbagian = $barf->bagian;
                $pernama = $barf->namakaryawan;
              }
			
			
			       //ambil bagian,jabatan persetujuan atasan ke 2
            $perjabatan4 = '';
            $perbagian4 = '';
            $pernama4 = '';
            $strf = "select a.bagian,b.namajabatan,a.namakaryawan from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=" . $persetujuan4;

      			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
      			$resf->setFetchMode(PDO::FETCH_OBJ);
            while ($barf = $resf->fetch()) {
              $perjabatan4 = $barf->namajabatan;
              $perbagian4 = $barf->bagian;
              $pernama4 = $barf->namakaryawan;
            }
			
            //ambil bagian,jabatan persetujuan hrd
            $perjabatanhrd = '';
            $perbagianhrd = '';
            $pernamahrd = '';
            $strf = "select a.bagian,b.namajabatan,a.namakaryawan from " . $dbname . ".datakaryawan a left join " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                   where karyawanid=" . $hrd;
      			$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
      			$resf->setFetchMode(PDO::FETCH_OBJ);
            while ($barf = $resf->fetch()) {
              $perjabatanhrd = $barf->namajabatan;
              $perbagianhrd = $barf->bagian;
              $pernamahrd = $barf->namakaryawan;
            }
        }
		class PDF extends FPDF {	
            function Header() {
			  global $kodeorganisasi;
              $arrHead = setheadreport('',$kodeorganisasi);
              $width = $this->w - $this->lMargin - $this->rMargin;
              $height = 8;
              $path=$arrHead['logo'];
              $this->Image($path,$this->lMargin,($this->tMargin-8),0,30);
              $this->SetFont('Arial','B',10);
              $this->SetFillColor(255,255,255); 
              $this->SetX(50);  
              $this->Cell($width,$height,$arrHead['nama'],0,1,'L');
              $this->SetX(50);  		
              $this->Cell(80,$height,'FORMULIR PENGAJUAN CUTI/UNPAID LEAVE','B',1,'L');		
              $this->Line($this->lMargin,$this->tMargin+($height*4),
			        $this->lMargin+$width,$this->tMargin+($height*4));
              $this->Ln();
              $namapt=$arrHead['nama'];
              //print_r($namapt);
            }

            function Footer() {
              $this->SetY(-15);
              $this->SetFont('Arial', 'I', 8);
              $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }
        }
        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->AddPage();
       // $pdf->SetY(40);
        $pdf->SetX(20);
        $pdf->SetFillColor(255, 255, 255);

        $pdf->Ln(20); 
        $pdf->SetFont('Arial','',10);    
        $pdf->Cell(30, 5,"Nama", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$namakaryawan, 'B',0, 'J');
        $pdf->SetX(115);
        $pdf->Cell(30, 5,"Tgl. Pengajuan.", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$tgl, 'B',1, 'J');
        $pdf->Cell(30, 5,"No. Telp.", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$nohp, 'B',0, 'J');
        $pdf->SetX(115);
        $pdf->Cell(30, 5,"Tanggal Masuk", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$tglmasuk, 'B',1, 'J');
        $pdf->Cell(30, 5,"BU/PT", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$namaorg, 'B',0, 'J');
        $pdf->SetX(115);
        $pdf->Cell(30, 5,"Dept.", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$bagian, 'B',1, 'J');
        $pdf->Cell(30, 5,"Jabatan", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$jabatan, 'B',0, 'J');
        $pdf->SetX(115);
        $pdf->Cell(30, 5,"POH", 0, 'J');
        $pdf->Cell(5, 5,":", 0,0, 'J');
        $pdf->Cell(50, 5,$poh, 'B',1, 'J');

        $pdf->ln(10);
        $pdf->Cell(50, 5,"Dengan ini saya mengajukan ".$datajeniscuti[$jenisnya]." : ", 0,1, 'J');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX(20);
        $pdf->Cell(8, 6,'V', 1, 0, 'C');
        $pdf->Cell(65, 8,$datajeniscuti[$jenisnya], 0, 0, 'L');

        $pdf->ln(15);
        $pdf->Cell(50, 5,"Tanggal Mulai Cuti", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,tanggalnormald($jmDr), 'B',1, 'J');
        $pdf->Cell(50, 5,"Tanggal & Waktu Berangkat", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,tanggalnormal($tglberangkat), 'B',1, 'J');
        $pdf->Cell(50, 5,"Rute Hometrip*", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$ruteberangkat, 'B',1, 'J');
        $pdf->Cell(50, 5,"Keterangan", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->MultiCell(120, 5,$ket, 'B',1, 'J');        
        $pdf->Cell(50, 5,"Jumlah hari / Lama cuti", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$jumlahhari, 'B',1, 'J');
        $pdf->Cell(50, 5,"Tanggal Akhir Cuti", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,tanggalnormald($jmSmp), 'B',1, 'J');
        $pdf->Cell(50, 5,"Tanggal & Waktu Kembali", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$tglmasuk, 'B',1, 'J');
        $pdf->Cell(50, 5,"Rute Hometrip*", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$rutepulang, 'B',1, 'J');
        $pdf->Cell(50, 5,"Tugas diambil alih oleh", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$pengganti, 'B',1, 'J');
        $pdf->Cell(50, 5,"Periode", 0, 'J');
        $pdf->Cell(5, 5," : ", 0,0, 'J');
        $pdf->Cell(120, 5,$periode, 'B',1, 'J');

        $pdf->ln(12);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX(150);
        $pdf->Cell(40, 5,"", 0,1, 'C');
        $awaly=$pdf->GetY();
        $pdf->Cell(140, 5,"Catatan / Verifikasi Personalia : ", 0 ,1, 'J');

        $str="select * from ".$dbname.".sdm_cutiht where karyawanid='".$karyawanid."' and periodecuti='".$periode."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
         $hakcuti=$bar['hakcuti'];
         $sisa=$bar['sisa'];
        
         $thnskrg = date('Y');
         $sisaprdsblm=$hakcuti;
         //sisa cuti sblmnya > 6 = 6
        //  if ($sisaprdsblm > 6  && $periode < $thnskrg) {
        //      $sisaprdsblm = 6;
        //  }

        // //Get Personalia by notransaksi : 
        // $sdhambil=0;
        // $str="select a.notransaksi,a.jumlahhari,a.stpersetujuan4,a.statuspersetujuan_cancel,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijinnonstaff a
        //         left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis 
        //         where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and a.notransaksi!= '".$nomortrans."' and a.tanggal< '".tanggalsystem($tgl)."' and a.statuspersetujuan_cancel = '0' order by notransaksi asc";
        // $res=fetchdata($str);
        // foreach ($res as $key => $val){
        //     if($val['stpersetujuan4']=='1' && ($val['statuspotongan']!='0')){
        //         $sdhambil+=$val['jumlahhari'];
        //     }
        // }

        // /**
        //  * Ini sisa sudah ambil cuti carry over
        //  * Harusnya bisa di refactor lagi tapi udah pusing
        //  */
        // $sdhambil2=0;
        // $str="select a.notransaksi,a.jumlahhari,a.statuspersetujuan,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijinnonstaff a 
        // left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and substr(a.darijam,1,4) = '".date('Y')."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' order by notransaksi asc";
        // $res=fetchdata($str);
        // foreach ($res as $key => $val){
        //     if($val['notransaksi']==$nomortrans){
        //         break;
        //     }
        //     if($val['statuspersetujuan']=='1' && $val['statuspotongan']!='0'){
        //         $sdhambil2+=$val['jumlahhari'];
        //     }
        // }

        // $str="select sum(a.jumlahhari) as jmlhari from ".$dbname.".sdm_ijinnonstaff a 
        // left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis  where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and substr(a.darijam,1,4) = '".$periode."' and a.statuspersetujuan_cancel!='1' and a.statuspersetujuan !='2' and b.statuspotongan='1' order by notransaksi asc";
        // $res=fetchdata($str);
        
        // foreach ($res as $key => $val){
        //     $hasilpotong = $sisaprdsblm-$val['jmlhari'];
        // }

        // $sisaprdsblm = $sisaprdsblm - $sdhambil;
        // // exit('warning:'.substr($jmDr,0,4).'--'.$periode);
        // if (substr($jmDr,0,4) != $periode) {
        //     // exit('warning:'.$sisaprdsblm);
        //     if ($sisaprdsblm >= 6) {
        //         $sisaprdsblm = 6;
        //         $sisaprdsblm = $sisaprdsblm-$sdhambil2;
        //     } else {
        //         // exitx    ('warning:'.$hasilpotong);
        //         $sisaprdsblm = $sisaprdsblm - $hasilpotong;
        //     }
        // }


        // $str="select statuspotongan  from ".$dbname.".sdm_5jenisijin where idjenis='".$idjenis."'";
        // $res=fetchdata($str);
        // foreach ($res as $data) {
        //     if($data['statuspotongan'] == '0'){
        //         $jlhhari = $jumlahhari;
        //         $sisacutiper = $sisaprdsblm;
        //     }else{
        //         $jlhhari = $jumlahhari;
        //         $sisacutiper = $sisaprdsblm-$jumlahhari;
        //     }
        // }
        
        //Get Personalia by notransaksi : 
        $sdhambil=0;
        $str="select a.notransaksi,a.jumlahhari,a.stpersetujuan4,a.statuspersetujuan_cancel,b.idjenis,b.statuspotongan from ".$dbname.".sdm_ijinnonstaff a
                left join ".$dbname.".sdm_5jenisijin b on a.idjenis=b.idjenis 
                where karyawanid='".$karyawanid."' and periodecuti='".$periode."' and a.notransaksi!= '".$nomortrans."' and a.tanggal< '".tanggalsystem($tgl)."' and a.statuspersetujuan_cancel = '0' order by notransaksi asc";
        $res=fetchdata($str);
        foreach ($res as $key => $val){
            if($val['stpersetujuan4']=='1' && ($val['statuspotongan']!='0')){
                $sdhambil+=$val['jumlahhari'];
            }
        }
        $sisaprdsblm=$sisaprdsblm-$sdhambil;

        $str="select statuspotongan  from ".$dbname.".sdm_5jenisijin where idjenis='".$idjenis."'";
        $res=fetchdata($str);
        foreach ($res as $data) {
            if($data['statuspotongan'] == '0'){
                $jlhhari = $jumlahhari;
                $sisacutiper = $sisaprdsblm;
            }else{
                $jlhhari = $jumlahhari;
                $sisacutiper = $sisaprdsblm-$jumlahhari;
            }
        }

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 5, "Sisa Cuti Periode ".$periode, 0, 0, 'L');
        $pdf->Cell(6, 5, " = ", 0, 0, 'L');
        $pdf->Cell(16, 5,$sisaprdsblm, 0, 0, 'R');
        $pdf->Cell(5, 5, " ", 0, 0, 'C');
        $pdf->Cell(63, 5, "hari", 0, 1, 'L');
        $pdf->Cell(50, 5, "Rencana Pengambilan Cuti ", 0, 0, 'L');
        $pdf->Cell(6, 5, " = ", 0, 0, 'L');
        $pdf->Cell(16, 5,$jlhhari, 'B', 0, 'R');
        $pdf->Cell(5, 5, "-", 0, 0, 'C');
        $pdf->Cell(63, 5, "hari", 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 5, "Sisa Cuti ".$periode." Tersisa", 0, 0, 'R');
        $pdf->Cell(11, 5, " = ", 0, 0, 'R');
        $pdf->Cell(16, 5,$sisacutiper, 0, 0, 'R');

        $pdf->Cell(5, 5, " ", 0, 0, 'C');
        $pdf->Cell(63, 5, "hari", 0, 1, 'L');
        $akhiry=$pdf->GetY();
        // $pdf->Line(190, $awaly,190, $akhiry);
        // $pdf->Line(150, $akhiry,190, $akhiry);

        $pdf->ln(10);
        $pdf->Cell(40, 5,"Pemohon : ", 0,1, 'L');
		$pdf->SetFont('Arial', '', 10);
        $pdf->Cell(170, 5, getNamaKaryawan($karyawanid), 0,1, 'L');
		$pdf->Ln();
		
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 5,"Status Persetujuan (by system):                               Status Pembatalan (by system):", 0,1, 'L');
        $pdf->SetX(10);
		$grsatas=$pdf->GetX();
		$grskiri=$pdf->GetY();
		$height=5;
		$str="select * from ".$dbname.".approval where notransaksi='".$nomortrans."' group by level order by level asc";
        $stf = 1;
		$res=fetchdata($str);
		if(count($res)){
			$no=0;
			$pdf->SetFont('Arial', '', 10);
			$wdthword=0;
            $wdthword2=0;
			foreach($res as $val){
				if($stf==0){
					$detailApprove=detailApprove($val['level'],$nomortrans,'IJNS');					
				}else{
					$detailApprove=detailApprove($val['level'],$nomortrans,'IJNS');
                    $detailApprove2=detailApprove($val['level'],$nomortrans,'IJNSC');   					
				}
				$widthword=strlen($detailApprove['komentar']);
				if($widthword > $wdthword){
					$wdthword=($widthword+7);
				}
        $widthword2=strlen($detailApprove2['komentar']);
        if($widthword2 > $wdthword2){
          $wdthword2=($widthword2+7);
        }
			}
			
			foreach($res as $val){
				$no++;
				if($stf==0){
					$detailApprove=detailApprove($val['level'],$nomortrans,'IJNS');					
				}else{
					$detailApprove=detailApprove($val['level'],$nomortrans,'IJNS');		
                    $detailApprove2=detailApprove($val['level'],$nomortrans,'IJNSC');    			
				}
				
				$awalY=$pdf->GetY();
				$pdf->SetXY(1000,$awalY);
				$pdf->MultiCell(50, $height, $detailApprove['nama'], '0', 'L');
				$akhirYakun=$pdf->GetY();
				
				$pdf->SetXY(1000,$awalY);
				$pdf->MultiCell($wdthword, $height, $detailApprove['komentar'], '0', 'L');
				$akhirYketerangan=$pdf->GetY();
				
				$pdf->SetXY(1000,$awalY);
				$pdf->MultiCell(35, $height, $detailApprove['namastatus']."\n".tanggalnormal($detailApprove['tanggal']), 0, 'C');
				$akhirYStatus=$pdf->GetY();
				
				$akhirY = max($akhirYketerangan,$akhirYakun,$akhirYStatus);
				$height2=$akhirY-$awalY;
				
				$pdf->SetXY(10,$awalY);
				
				$pdf->MultiCell(5, $height, $no, 0, 'C');
				$pdf->SetXY($pdf->GetX()+5, $awalY);
				$pdf->MultiCell(40, $height, $detailApprove['nama'], 0, 'L');
				$pdf->SetXY($pdf->GetX()+45, $awalY);
				$pdf->MultiCell(35, $height, $detailApprove['namastatus']."\n".tanggalnormald($detailApprove['tanggal']), 0, 'L');
				$pdf->SetXY($pdf->GetX()+80, $awalY);
				$pdf->MultiCell($wdthword, $height, $detailApprove['komentar'], 0, 'L');
        $grsatas2x=$pdf->GetX()+80+$wdthword;


        $pdf->SetXY($pdf->GetX()+85+$widthword, $awalY);
        $pdf->MultiCell(5, $height, $no, 0, 'C');
        $pdf->SetXY($pdf->GetX()+90+$widthword, $awalY);
        $pdf->MultiCell(40, $height, $detailApprove2['nama'], 0, 'L');
        $pdf->SetXY($pdf->GetX()+130+$widthword, $awalY);
        $pdf->MultiCell(35, $height, $detailApprove2['namastatus']."\n".tanggalnormald($detailApprove2['tanggal']), 0, 'L');
        $pdf->SetXY($pdf->GetX()+165+$widthword, $awalY);
        $pdf->MultiCell($wdthword2, $height, $detailApprove2['komentar'], 0, 'L');
				$grsatas2=$pdf->GetX()+165+$wdthword+$wdthword2;
				$pdf->SetXY($grsatas2, $awalY);
				$pdf->Ln($height2);
				$grsakhir=$awalY+$height2;
				$pdf->Line($grsatas, $pdf->GetY(), $grsatas2, $pdf->GetY());		
			}
		}
        $pdf->Line($grsatas, $grskiri, $grsatas2, $grskiri);
		$pdf->Line($grsatas, $grskiri, $grsatas, $grsakhir);


        
		// $pdf->Line($grsatas, $grsakhir, $grsatas2, $grsakhir);
		$pdf->Line($grsatas2, $grskiri, $grsatas2, $grsakhir);

    $pdf->Line($grsatas2x, $grskiri, $grsatas2x, $grsakhir);
		
        // $pdf->Cell(60, 5, "Pemohon,", 0, 0, 'C');
        // $pdf->Cell(60, 5,"Persetujuan 1,", 0,0, 'C');
        // $pdf->Cell(60, 5,"Persetujuan 2,", 0,0, 'C');
        // $pdf->ln(30);
        // $pdf->SetX(10);
        // $pdf->SetFont('Arial', '', 10);
        // $pdf->Cell(60, 5,getNamaKaryawan($karyawanid), 0, 0, 'C');
        // $pdf->Cell(60, 5,getNamaKaryawan($persetujuan), 0,0, 'C');
        // $pdf->Cell(60, 5,getNamaKaryawan($persetujuan4), 0,0, 'C');
        // $pdf->ln(15);
        // $pdf->MultiCell(180,5,'Catatan:*) Bila tidak disetujui Atasan, sebelum diserahkan kepada Personalia harus mendapatkan tandatangan sekurangnya 2 Atasan berwenang (min. Department Head).',0,'J',0);





        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['tanggal'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $tgl, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['nokaryawan'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $nikaryawan, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['namakaryawan'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $namakaryawan, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['bagian'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $bagian, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['functionname'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $jabatan, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['keperluan'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $kperluan, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['jenisijin'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $datajeniscuti[$jenisnya], 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['keterangan'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $ket, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['periode'] . " " . $_SESSION['lang']['tahun'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $periode, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['dari'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $jmDr, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['tglcutisampai'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $jmSmp, 0, 1, 'L');
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['hari'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $hk . " " . $_SESSION['lang']['hari'], 0, 1, 'L');



        // $pdf->Ln();
        // $pdf->SetX(20);
        // $pdf->SetFont('Arial', 'B', 8);
        // $pdf->Cell(172, 5, strtoupper($_SESSION['lang']['approval_status']), 0, 1, 'L');
        // $pdf->SetX(21);
        // $pdf->Cell(30, 5, strtoupper($_SESSION['lang']['bagian']), 1, 0, 'C');
        // $pdf->Cell(50, 5, strtoupper($_SESSION['lang']['namakaryawan']), 1, 0, 'C');
        // $pdf->Cell(60, 5, strtoupper($_SESSION['lang']['functionname']), 1, 0, 'C');
        // $pdf->Cell(37, 5, strtoupper($_SESSION['lang']['keputusan']), 1, 1, 'C');

        // $pdf->SetFont('Arial', '', 8);

        // $pdf->SetX(21);
        // $pdf->Cell(30, 5, $perbagian, 1, 0, 'L');
        // $pdf->Cell(50, 5, $pernama, 1, 0, 'L');
        // $pdf->Cell(60, 5, $perjabatan, 1, 0, 'L');
        // $pdf->Cell(37, 5, $arrKeputusan[$perstatus], 1, 1, 'L');
        // $pdf->SetX(21);
        // $pdf->Cell(30, 5, $perbagian4, 1, 0, 'L');
        // $pdf->Cell(50, 5, $pernama4, 1, 0, 'L');
        // $pdf->Cell(60, 5, $perjabatan4, 1, 0, 'L');
        // $pdf->Cell(37, 5, $arrKeputusan[$perstatus4], 1, 1, 'L');
        // $pdf->SetX(21);
        // $pdf->Cell(30, 5, $perbagianhrd, 1, 0, 'L');
        // $pdf->Cell(50, 5, $pernamahrd, 1, 0, 'L');
        // $pdf->Cell(60, 5, $perjabatanhrd, 1, 0, 'L');
        // $pdf->Cell(37, 5, $arrKeputusan[$sthrd], 1, 1, 'L');

        // $pdf->Ln();

        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['keputusan'] . " " . $_SESSION['lang']['atasan']." 1", 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $koments, 0, 1, 'L');
        
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['keputusan'] . " " . $_SESSION['lang']['atasan']." 2", 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $koments4, 0, 1, 'L');
        
        // $pdf->SetX(20);
        // $pdf->Cell(30, 5, $_SESSION['lang']['keputusan'] . " " . $_SESSION['lang']['hrd'], 0, 0, 'L');
        // $pdf->Cell(50, 5, " : " . $koments2, 0, 1, 'L');


        // $pdf->Ln();
        // $pdf->Ln();
        // $pdf->Ln();


        //footer================================
        $pdf->Ln();
        $pdf->Output();

        break;
    case'getExcel':
        $tmbWhere = '';
        if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' and ( $_SESSION['empl']['bagian'] == 'HHRD' || $_SESSION['empl']['bagian'] == 'HHRS')) {
            $tmbWhere = '';
        } else if ($_SESSION['empl']['tipelokasitugas'] == 'KANWIL' and $_SESSION['empl']['bagian'] == 'HRA') {
            $tmbWhere = " and a.karyawanid like '%" . $excelkaryawanid . "%'";
        } else {
            $tmbWhere = " and a.karyawanid like '%" . $excelkaryawanid . "%'";
        }
        $slvhc = "select a.* from " . $dbname . ".sdm_ijinnonstaff a, " . $dbname . ".datakaryawan b where a.karyawanid = b.karyawanid and a.tanggal between '" . $pAwal . "' and '" . $pAkhir . "' and a.idjenis like '%" . $jnsCuti . "%' " . $tmbWhere . " order by a.tanggal desc ";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($qlvhc);
        $user_online = $_SESSION['standard']['userid'];

        if ($numrows <= 0) {
            echo $_SESSION['lang']['datanotfound'];
            exit();
        }

        $tab.=" 
                <table class=sortable cellspacing=1 border=1 width=80%>
                <thead>
                <tr  >
                <td align=center bgcolor='#DFDFDF'>No.</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['tanggal'] . "</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['nama'] . "</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['keperluan'] . "</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['jenisijin'] . "</td>  
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['persetujuan'] . "</td>    
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['approval_status'] . "</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['dari'] . "  " . $_SESSION['lang']['jam'] . "</td>
                <td align=center bgcolor='#DFDFDF'>" . $_SESSION['lang']['tglcutisampai'] . "  " . $_SESSION['lang']['jam'] . "</td>
                </tr>  
                </thead><tbody>";
        while ($rlvhc = $qlvhc->fetch()) {
            // if ($_SESSION['language'] == 'ID') {
            //     $dd = $rlvhc['jenisijin'];
            // } else {
            //     switch ($rlvhc['jenisijin']) {
            //         case 'TERLAMBAT':
            //             $dd = 'Late for work';
            //             break;
            //         case 'KELUAR':
            //             $dd = 'Out of Office';
            //             break;
            //         case 'PULANGAWAL':
            //             $dd = 'Home early';
            //             break;
            //         case 'IJINLAIN':
            //             $dd = 'Other purposes';
            //             break;
            //         case 'CUTI':
            //             $dd = 'Leave';
            //             break;
            //         case 'MELAHIRKAN':
            //             $dd = 'Maternity';
            //             break;
            //         default:
            //             $dd = 'Wedding, Circumcision or Graduation';
            //             break;
            //     }
            // }

            $no+=1;
            $nmAkun = makeOption($dbname, 'sdm_5jenisijin', 'idjenis,jenisijin');
            $tab.="
						<tr class=rowcontent>
						<td>" . $no . "</td>
						<td>" . $rlvhc['tanggal'] . "</td>
						<td>" . $arrNmkary[$rlvhc['karyawanid']] . "</td>
						<td>" . $rlvhc['keperluan'] . "</td>
						<td>" . (isset($nmAkun[$rlvhc['idjenis']]) ? $nmAkun[$rlvhc['idjenis']] : '') . "</td>
						<td>" . $arrNmkary[$rlvhc['persetujuan1']] . "</td>
						<td>" . $arrKeputusan[$rlvhc['stpersetujuan1']] . "</td>
						<td>" . $rlvhc['darijam'] . "</td>
						<td>" . $rlvhc['sampaijam'] . "</td>";
        }

        $tab.="</tbody></table>";

        $nop_ = "listizinkeluarkantor";
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }

            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');

            if (!fwrite($handle, $tab)) {
                echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
						</script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
            }
            fclose($handle);
        }
        break;

    case'formForward':
        $optKary = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sKary = "select distinct karyawanid,namakaryawan from " . $dbname . ".datakaryawan 
                         where alokasi='1' and karyawanid not in('" . $_SESSION['standard']['userid'] . "','" . $krywnId . "') order by namakaryawan asc";
		$qKary=$owlPDO->query($sKary) or die(print " Gagal: ".PDOException::getMessage());
		$qKary->setFetchMode(PDO::FETCH_ASSOC);
		while ($rKary = $qKary->fetch()) {
            $optKary.="<option value='" . $rKary['karyawanid'] . "'>" . $rKary['namakaryawan'] . "</option>";
        }
        $tab.="<fieldset><legend>" . $arrNmkary[$krywnId] . ", " . $_SESSION['lang']['tanggal'] . " : " . tanggalnormal($tglijin) . "</legend><table cellpadding=1 cellspacing=1 border=0>";
        $tab.="<tr><td>" . $_SESSION['lang']['namakaryawan'] . "</td><td><select id=karywanId>" . $optKary . "</select></td></tr>";
        $tab.="<tr><td colspan=2><button class=mybutton id=dtlForm onclick=AppForw()>Forward</button></td></tr></table>";
        $tab.="</table></fieldset><input type='hidden' id=karyaid value=" . $krywnId . " /><input type=hidden id=tglIjin value=" . tanggalnormal($tglijin) . "/>";
        echo $tab;
        break;
    case'forwardData':
        $sup = "update " . $dbname . ".sdm_ijinnonstaff set persetujuan1='" . $atasan . "' where $where";
		try{
			$owlPDO->exec($sup); 
			
			$sKar = "select distinct * from " . $dbname . ".sdm_ijinnonstaff where $where";
			$qKar=$owlPDO->query($sKar) or die(print " Gagal: ".PDOException::getMessage());
			$qKar->setFetchMode(PDO::FETCH_ASSOC);
            $rKar = $qKar->fetch();
            $strf = "select sisa from " . $dbname . ".sdm_cutiht where karyawanid=" . $krywnId . " 
                        and periodecuti=" . $rKar['periodecuti'];
			$res=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
            
            $sisa = '';
            while ($barf = $res->fetch()) {
                $sisa = $barf->sisa;
            }
            if ($sisa == '')
                $sisa = 0;
            $to = getUserEmail($atasan);
            $namakaryawan = getNamaKaryawan($krywnId);
            $subject = "[Notifikasi]Persetujuan Ijin Keluar Kantor a/n " . $namakaryawan;
            $body = "<html>
                    <head>
                    <body>
                    <dd>Dengan Hormat,</dd><br>
                    <br>
                    Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan Ijin/" . $rKar['idjenis'] . " (" . $rKar['keperluan'] . ")
                    kepada bapak/ibu. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
                    <br>
                    <br>
                    Note: Sisa cuti ybs periode " . $rKar['periodecuti'] . ":" . $sisa . " Hari
                    <br>
                    <br>
                    Regards,<br>
                    Owl-Plantation System.
                    </body>
                    </head>
                    </html>
                    ";
            $kirim = kirimEmail($to, '', $subject, $body); #this has return but disobeying;
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
        break;

    default:
        break;
}
?>
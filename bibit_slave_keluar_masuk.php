<?php
ini_set('display_errors',0);
error_reporting(0);
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zJournal.php');
include_once('lib/zFunction.php');

//$proses = $_POST['proses'];
$proses=checkPostGet('proses','');
$cJournal = new zJournal();

$param = $_POST;
if(count($param)==0){$param = $_GET;}
//header
$kodeTrans  = checkPostGet('kodeTrans', '');
$batchVar   = checkPostGet('batchVar', '');
$kdOrg      = checkPostGet('kdOrg', '');
$jmlhBibitan= checkPostGet('jmlhBibitan', '');
$ket        = checkPostGet('ket', '');
$jnsBibitan = checkPostGet('jnsBibitan', '');
$supplierid = checkPostGet('supplierid', '');
$tglProduksi= tanggalsystem(checkPostGet('tglProduksi', ''));
$tglTnm     = tanggalsystem(checkPostGet('tglTnm', ''));
$kodeBatch  = checkPostGet('kodeBatch','');
$kodeBatchOld  = checkPostGet('kodeBatchOld','');
$kdOrgTjn2    = checkPostGet('kdOrgTjn2','');
// $jab     = getPostingJabatan('keluar-masuk bibit');
$jab        = getPostingJabatan('kebunbibit');
$keterangan = checkPostGet('ketPnb', '');

//$where=" tahunbudget='".$thnBudget."' and kodeorg='".$kdBlok."' and tipebudget='".$tpBudget."' and kegiatan='".$kegId."' and volume='".$volKeg."' and satuanv='".$satuan."' and rotasi='".$rotThn."'";
#penyemaian#



$optnmCust    = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmSup     = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$optNm        = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmkaryawan= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$oldJenisBibit= checkPostGet('oldJenisBibit', '');
$kdOrgTjn     = checkPostGet('kdOrgTjn', '');
$intexDt      = checkPostGet('intexDt', '');

$kdvhc        = checkPostGet('kdvhc', '');
$nmSupir      = checkPostGet('nmSupir', '');
$intexDt      = checkPostGet('intexDt', '');
$detPeng      = checkPostGet('detPeng', '');
$assistenPnb  = checkPostGet('assistenPnb', '');
$kplDivBbt  = checkPostGet('kplDivBbt', '');
$kplDivKbn  = checkPostGet('kplDivKbn', '');
$custId       = checkPostGet('custId', '');
$kodeAfd      = checkPostGet('kodeAfd', '');
$KegiatanId   = checkPostGet('KegiatanId', '');

//param+      ='&kbnId='+kbnId+'&nodo='+nodo+'&afkirKcmbh='+afkirKcmbh+'&jmlhdDo='+jmlhdDo;
$jmlhTrima    = checkPostGet('jmlhTrima', '');
$nodo         = checkPostGet('nodo', '');
$afkirKcmbh   = checkPostGet('afkirKcmbh', '');
$jmlhdDo      = checkPostGet('jmlhdDo', '');
$jmlRit       = checkPostGet('jmlRit', '');
$tgl          = checkPostGet('tgl', '');
$kodeorg      = checkPostGet('kodeorg', '');

$tab          = $wher= "";
$no           = 0;

$fileupload   = checkPostGet('fileupload', '');
$dir          ='fileupload/bbtmemo';
$path         ='fileupload/bbtmemo/';
$batchAfk     = checkPostGet('batchAfk', '');
$tglAfkirBibit= tanggalsystem(checkPostGet('tglAfkirBibit', ''));
$kdOrgAfk     = checkPostGet('kdOrgAfk', '');
$doc          =checkPostGet('doc', '');


switch ($proses) {
	case'getNumberSPB':
		# format nomor 201710/SKLEBB/001
		$tgl=tanggalSystemn($tgl);
		$tmpTgl = explode('-',$tgl);
		$notran=$tmpTgl[0].$tmpTgl[1];
		$yymmdd=$tmpTgl[0].$tmpTgl[1].$tmpTgl[2];
        
		$induk=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		
        $str="select right(keterangan,3) as nomorurut from ".$dbname.".bibitan_mutasi where kodetransaksi='PNB' and kodeorg='".$kodeorg."' and left(keterangan,6) = '".$notran."' order by right(keterangan,3) desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
		// exit('error'.$str);
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=$notran."/".$induk[$kodeorg]."/".substr($kodeorg,-4)."/".addZero($noawal,3);
		echo $notran;
	break;
	case 'lihatfile':
		$potong=explode('.',$doc);
		if($potong[1]=='pdf'){
			echo"<embed src=".$doc." width=100% height=100%>";
		}else{
			echo"<img src=".$doc.">";
		}
	break;
	
	case 'savefile':
	//exit("Error:$batchAfk._.$tglAfkirBibit._.$kdOrgAfk");
		$fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
		$fileupload = $fileupload;
		if($fileupload=='.'){
			
		} else if($fileupload=='.jpg' || $fileupload=='.jpeg' || $fileupload=='.png' || $fileupload=='.pdf'){
			$filesize=$_FILES['fileup']['size'];
			if($filesize>=512000){
				exit("Warning : Besar ukuran file maksimal 512 Kb. ");
			}
			$path = $dir."/".basename($_FILES['fileup']['name']);
			if(move_uploaded_file($_FILES['fileup']['tmp_name'], $path)){	
				$str="update ".$dbname.".bibitan_mutasi set filememo='".$path."' where 
						batch='".$batchAfk."' and tanggal='".$tglAfkirBibit."' and kodeorg='".$kdOrgAfk."' and kodetransaksi='AFB' ";
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}	
			}
		} else {
			exit("Warning : File yang di-izinkan hanya JPG,JPEG,PNG,PDF");
		}
	break;
	
    case'saveTab1':
		try {
			$owlPDO->beginTransaction();
	
			if (($kdOrg == '') || ($jmlhBibitan == '') || ($jnsBibitan == '') || ($supplierid == '') || ($tglProduksi == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}
			// $sql = "select * from " . $dbname . ".bibitan_mutasi where kodeorg='".$kdOrg."'";
			// $res = fetchData($sql);
			// if(count($res)>0){
			// 	throw new PDOException("Kode blok sudah pernah diinput, silahkan pilih blok lainnya.");
			// }
			
            # Generate nomor batch cth: 20240626/A1/001
            $whrBatch = "batch like '".$tglTnm."/".$kodeBatch."%'";
            $selBatch = selectQuery($dbname,"bibitan_batch","batch",$whrBatch);
            $tmpNo    = fetchData($selBatch);
            if (count($tmpNo) == 0) {
                $notrantmb=$tglTnm."/".$kodeBatch."/001";
            } else {
                $maxNo = 1;
				foreach($tmpNo as $row) {
                    $tmpRow = explode('/',$row['batch']);
                    $noUrut = (int)$tmpRow[2];
                    if($noUrut>$maxNo) {
                        $maxNo = $noUrut;
                    }
				}
				$currNo = addZero($maxNo+1,3);
                $notrantmb=$tglTnm."/".$kodeBatch."/".$currNo;
            }

			$scek = "select post from " . $dbname . ".bibitan_mutasi where batch='" . $notrantmb . "' and kodeorg='" . $kdOrg . "'";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$rcek=owlBaris($qcek);
			if ($rcek == '0') {
				$sInsert = "insert into " . $dbname . ".bibitan_batch (batch, tanggal, tanggaltanam, jenisbibit, supplerid, tanggalproduksi,jumlahdo,jumlahterima,jumlahafkir,nodo,kodeorg) 
				values('" . $notrantmb . "','" . $tglTnm . "','" . $tglTnm . "','" . $jnsBibitan . "','" . $supplierid . "','" . $tglProduksi . "','" . $jmlhdDo . "','" . $jmlhTrima . "','" . $afkirKcmbh . "','" . $nodo . "','" . $kdOrg . "')";
				$owlPDO->exec($sInsert); 
				   
					
				$sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby) values('" . $notrantmb . "','" . $kdOrg . "','" . $tglTnm . "','" . $kodeTrans . "','" . $jmlhBibitan . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
				$owlPDO->exec($sInsert2);
				
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case'saveTab4':
		try {
			$owlPDO->beginTransaction();
	
			if (($kodeBatch == '') || ($jmlhBibitan == '') || ($kodeBatchOld == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}
            
			// $sql = "select * from " . $dbname . ".bibitan_mutasi where kodeorg='".$kdOrg."'";
			// $res = fetchData($sql);
			// if(count($res)>0){
			// 	throw new PDOException("Kode blok sudah pernah diinput, silahkan pilih blok lainnya.");
			// }

            $selOld = "SELECT a.jenisbibit, a.kodeorg, a.supplerid, a.tanggalproduksi, a.jumlahdo, a.jumlahterima, a.jumlahafkir, a.nodo,
            b.kodetransaksi, b.jumlah, b.keterangan, b.tujuan, b.post, b.flag FROM $dbname.bibitan_batch a JOIN $dbname.bibitan_mutasi b
            ON a.batch = b.batch WHERE b.kodetransaksi='TMB' AND a.batch='".$kodeBatchOld."'";
            $resOld = fetchdata($selOld);

            // $scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $resOld[0]['kodeorg'] . "' and batch='" . $kodeBatchOld . "' and post='1' group by kodeorg";
			// $qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			// $qcek2->setFetchMode(PDO::FETCH_ASSOC);
			// $rcek2 = $qcek2->fetch();
			// if ($jmlhBibitan > $rcek2['totalBibitan']) {
			// 	throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $resOld[0]['kodeorg']);
			// }
			
            # Generate nomor batch cth: 20240626/A1/001
            $whrBatch = "batch like '".$tglTnm."/".$kodeBatch."%'";
            $selBatch = selectQuery($dbname,"bibitan_batch","batch",$whrBatch);
            $tmpNo    = fetchData($selBatch);
            if (count($tmpNo) == 0) {
                $notrantmb=$tglTnm."/".$kodeBatch."/001";
            } else {
                $maxNo = 1;
				foreach($tmpNo as $row) {
                    $tmpRow = explode('/',$row['batch']);
                    $noUrut = (int)$tmpRow[2];
                    if($noUrut>$maxNo) {
                        $maxNo = $noUrut;
                    }
				}
				$currNo = addZero($maxNo+1,3);
                $notrantmb=$tglTnm."/".$kodeBatch."/".$currNo;
            }

            $jmlh = $jmlhBibitan * -1;

            $scek = "select post from " . $dbname . ".bibitan_mutasi where batch='" . $notrantmb . "' and kodeorg='" . $resOld[0]['kodeorg'] . "'";
            $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$rcek=owlBaris($qcek);
			if ($rcek == '0') {
				// $sInsert = "insert into " . $dbname . ".bibitan_batch (batch, tanggal, tanggaltanam, jenisbibit, supplerid, tanggalproduksi,jumlahdo,jumlahterima,jumlahafkir,nodo,kodeorg) 
				// values('" . $notrantmb . "','" . $tglTnm . "','" . $tglTnm . "','" . $resOld[0]['jenisbibit'] . "','" . $resOld[0]['supplerid'] . "','" . $resOld[0]['tanggalproduksi'] . "','" . $resOld[0]['jumlahdo'] . "',
                // '" . $jmlhBibitan . "','" . $resOld[0]['jumlahafkir'] . "','" . $resOld[0]['nodo'] . "','" . $kdOrgTjn2 . "')";
                // $owlPDO->exec($sInsert);
                
				// $sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby) values
                // ('" . $kodeBatchOld . "','" . $kdOrgTjn2 . "','" . $tglTnm . "','" . $kodeTrans . "','" . $jmlh . "','Seleksi Ke Kode Batch ".$notrantmb."','" . $_SESSION['standard']['userid'] . "')";
                // $owlPDO->exec($sInsert2);
				
                $sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby) values
                ('" . $notrantmb . "','" . $kdOrgTjn2 . "','" . $tglTnm . "','" . $kodeTrans . "','" . $jmlhBibitan . "','" . $kodeBatchOld . "','" . $_SESSION['standard']['userid'] . "')";
                $owlPDO->exec($sInsert2);
				
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case'loadDataStock':

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;
		
        $tglSkrng = date("Y-m-d");
        $thnSkrng = date("Y");
        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodeorg like '%" . $_SESSION['empl']['lokasitugas'] . "%' 
				   group by batch,kodeorg order by tanggal desc";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($query2);
        if ($jlhbrs != 0) {
            $sData = "select  batch,kodeorg,sum(jumlah) as jumlah from " . $dbname . ".bibitan_mutasi 
					where kodeorg like '%" . $_SESSION['empl']['lokasitugas'] . "%' and post=1 
					group by batch,kodeorg order by tanggal desc limit " . $offset . "," . $limit . " ";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';
                $sDatabatch = "select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi from " . $dbname . ".bibitan_batch where batch='" . $rData['batch'] . "' ";
				$qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
				$qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
				$rDataBatch = $qDataBatch->fetch();

                $thnData = substr($rDataBatch['tanggaltanam'], 0, 4);
                $starttime = strtotime($rDataBatch['tanggaltanam']); //time();// tanggal sekarang
                $endtime = strtotime($tglSkrng); //tanggal pembuatan dokumen

                $jmlHari = ($endtime - $starttime) / (60 * 60 * 24 * 30);

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";
                $tab.="<td align=right>" . number_format($rData['jumlah'], 0) . "</td>";
                $tab.="<td>" . (isset($optnmSup[$rDataBatch['supplerid']]) ? $optnmSup[$rDataBatch['supplerid']] : "") . "</td>";
                $tab.="<td align=right>" . number_format($jmlHari, 2) . "</td>";
                $tab.="</tr>";
            }
            $tab.="
			<tr class=rowheader><td colspan=6 align=center>
			" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
			<button class=mybutton onclick=cariBastStock(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
			<button class=mybutton onclick=cariBastStock(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
			</td>
			</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=6>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo $tab;
        break;

    case'loadData1':
        setIt($_POST['tglCari2'], '');
        setIt($_POST['batchCari2'], '');
        setIt($_POST['statCari2'], '');
        $tanggal = substr(tanggalsystem($_POST['tglCari2']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari2']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari2']), 6, 2);
        if ($_POST['tglCari2'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        if ($_POST['batchCari2'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari2'] . "%'";
        }
        if ($_POST['statCari2'] != '') {
            $wher.=" and post='" . $_POST['statCari2'] . "'";
        }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay=($page*$limit);
        $no=$maxdisplay;
        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='TMB' and  kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . "
                order by tanggal desc ";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
        $jlhbrs=owlBaris($query2);
        $tab = "";
        if ($jlhbrs != 0) {
            $sData = "select distinct kodetransaksi, jumlah,batch,kodeorg,tanggal,post,flag from " . $dbname . ".bibitan_mutasi  
                    where kodetransaksi='TMB' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . "
                    order by tanggal desc limit " . $offset . "," . $limit . " ";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';
                $sDatabatch = "select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi,jumlahdo,jumlahterima,jumlahafkir,nodo from " . $dbname . ".bibitan_batch where batch='" . $rData['batch'] . "' ";
                $qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
                $qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                $rDataBatch = $qDataBatch->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";
                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                //$tab.="<td>".$rData['keterangan']."</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $rDataBatch['jenisbibit'] . "</td>";
                $tab.="<td>" . (isset($optnmSup[$rDataBatch['supplerid']]) ? $optnmSup[$rDataBatch['supplerid']] : "") . "</td>";
                $tab.="<td>" . tanggalnormal($rDataBatch['tanggalproduksi']) . "</td>";
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }//
                else if (($rData['flag'] == 'AUTO') && ($rData['post'] == 1)) {
                    $data = 1;
                } else if (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                } else {
                    $data = 3;
                }

                $expkdbth = explode("/",$rData['batch']);
                $kdbth = $expkdbth[1];

                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
                        $tab.="<td align=center width=25><img id='detail_edit' &nbsp; style='cursor:pointer;' title='Edit " . $rData['batch'] . "' class=resicon onclick=\"filFieldHead('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $kdbth . "','" . $rData['kodeorg'] . "','" . $rData['jumlah'] . "','" . tanggalnormal($rDataBatch['tanggaltanam']) . "','" . $rDataBatch['jenisbibit'] . "','" . $rDataBatch['supplerid'] . "','" . tanggalnormal($rDataBatch['tanggalproduksi']) . "','" . $rDataBatch['nodo'] . "'
                                ,'" . $rDataBatch['jumlahdo'] . "','" . $rDataBatch['jumlahterima'] . "','" . $rDataBatch['jumlahafkir'] . "')\" src='images/application/application_edit.png'/></td>";
                        $tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delFieldHead('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . tanggalnormal($rData['tanggal']) . "','" . $rDataBatch['jenisbibit'] . "')\" src='images/application/application_delete.png'/></td>";
                        $tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . tanggalnormal($rData['tanggal']) . "')\" src='images/skyblue/posting.png'/></td>";
                    }else {
                        $tab.="<td align=center colspan=3>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else if ($data == 3) {
                    $tab.="<td colspan=3>References</td>";
                } else {
                    $tab.="<td colspan=3>" . $_SESSION['lang']['posting'] . "</td>";
                }

                $tab.="</tr>";
            }
            $tab.="
            <tr class=rowheader><td colspan=13 align=center>
            " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
            <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo $tab;
    break;
    case'loadData4':
        setIt($_POST['tglCari1'], '');
        setIt($_POST['batchCari1'], '');
        setIt($_POST['statCari1'], '');
        $tanggal = substr(tanggalsystem($_POST['tglCari2']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari2']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari2']), 6, 2);
        if ($_POST['tglCari2'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        if ($_POST['batchCari2'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari2'] . "%'";
        }
        if ($_POST['statCari2'] != '') {
            $wher.=" and post='" . $_POST['statCari2'] . "'";
        }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay=($page*$limit);
        $no=$maxdisplay;
        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='SEB' and  kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . "
                order by tanggal desc ";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
        $jlhbrs=owlBaris($query2);
        $tab = "";
        if ($jlhbrs != 0) {
            $sData = "select distinct kodetransaksi,keterangan,jumlah,batch,kodeorg,tanggal,post,flag from " . $dbname . ".bibitan_mutasi  
                    where kodetransaksi='SEB' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . "
                    order by tanggal desc limit " . $offset . "," . $limit . " ";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';
                $sDatabatch = "select distinct tanggaltanam,supplerid,jenisbibit,tanggalproduksi,jumlahdo,jumlahterima,jumlahafkir,nodo from " . $dbname . ".bibitan_batch where batch='" . $rData['keterangan'] . "' ";
                $qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
                $qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                $rDataBatch = $qDataBatch->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" .$rData['keterangan'] . "</td>";
                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $rDataBatch['jenisbibit'] . "</td>";
                $tab.="<td>" . (isset($optnmSup[$rDataBatch['supplerid']]) ? $optnmSup[$rDataBatch['supplerid']] : "") . "</td>";
                $tab.="<td>" . tanggalnormal($rDataBatch['tanggalproduksi']) . "</td>";
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }
                else if (($rData['flag'] == 'AUTO') && ($rData['post'] == 1)) {
                    $data = 1;
                } else if (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                } else {
                    $data = 3;
                }

                $expkdbth = explode("/",$rData['batch']);
                $kdbth = $expkdbth[1];

                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
                        $tab.="<td align=center width=25><img id='detail_edit' &nbsp; style='cursor:pointer;' title='Edit " . $rData['batch'] . "' class=resicon onclick=\"filField4('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $kdbth . "','" . ($rData['jumlah']) . "','" . tanggalnormal($rData['tanggal']) . "','" . $rData['keterangan'] . "','" . $rData['kodeorg'] . "')\" src='images/application/application_edit.png'/></td>";
                        $tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delField4('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $kdbth . "','" . tanggalnormal($rData['tanggal']) . "')\" src='images/application/application_delete.png'/></td>";
                        $tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData4('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['keterangan'] . "','" . tanggalnormal($rData['tanggal']) . "')\" src='images/skyblue/posting.png'/></td>";
                    }else {
                        $tab.="<td align=center colspan=3>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else if ($data == 3) {
                    $tab.="<td colspan=3>References</td>";
                } else {
                    $tab.="<td colspan=3>" . $_SESSION['lang']['posting'] . "</td>";
                }

                $tab.="</tr>";
            }
            $tab.="
            <tr class=rowheader><td colspan=13 align=center>
            " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
            <button class=mybutton onclick=cariBast4(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <button class=mybutton onclick=cariBast4(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo $tab;
    break;
    case'loadData2':
        if ($_POST['statCari'] != '') {
            $wher.=" and post='" . $_POST['statCari'] . "'";
        }
        if ($_POST['batchCari'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari'] . "%'";
        }
        $tanggal = substr(tanggalsystem($_POST['tglCari']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari']), 6, 2);
        if ($_POST['tglCari'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;

        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='TPB' and  
                       kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . " order by tanggal desc ";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($query2);
        if ($jlhbrs != 0) {
            $sData = "select distinct kodetransaksi, jumlah,batch,kodeorg,tanggal,post,flag,tujuan,keterangan from " . $dbname . ".bibitan_mutasi  where 
                        kodetransaksi='TPB' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%'  " . $wher . " 
                        order by tanggal desc limit " . $offset . "," . $limit . " ";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while ($rData = $qData->fetch()) {
                $data = '';
                $sDatabatch = "select distinct jumlah from " . $dbname . ".bibitan_mutasi where batch='" . $rData['batch'] . "' and kodeorg='" . $rData['tujuan'] . "' ";
				$qDataBatch=$owlPDO->query($sDatabatch) or die(print " Gagal: ".PDOException::getMessage());
				$qDataBatch->setFetchMode(PDO::FETCH_ASSOC);
                $rDataBatch = $qDataBatch->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";
                $tab.="<td>" . $optNm[$rData['tujuan']] . "</td>";
                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                $tab.="<td>" . $rData['keterangan'] . "</td>";
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }//
                elseif (($rData['flag'] == 'AUTO') && ($rData['post'] == 0)) {
                    $data = 1;
                } elseif (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                }
                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$tab.="<td align=center width=25><img id='detail_edit' style='cursor:pointer;' title='Edit " . $rData['batch'] . "' class=resicon onclick=\"filField2('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "','" . tanggalnormal($rData['tanggal']) . "','" . substr($rData['jumlah'], 1) . "')\" src='images/application/application_edit.png'/></td>";
						$tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delField2('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "')\" src='images/application/application_delete.png'/></td>";
						$tab.="<td align=center width=25><img id='detail_del' style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData2('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "','" . $rData['jumlah'] . "')\" src='images/skyblue/posting.png'/></td>";
                    }else {
                    $tab.="<td align=center colspan=3>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else {
                    $tab.="<td colspan=3>" . $_SESSION['lang']['posting'] . "</td>";
                }

                $tab.="</tr>";
            }
            $tab.="
		<tr class=rowheader><td colspan=12 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast2(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast2(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }

        echo $tab;
        break;
    case'loadData3':
        if ($_POST['statCari'] != '') {
            $wher.=" and post='" . $_POST['statCari'] . "'";
        }
        if ($_POST['batchCari'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari'] . "%'";
        }
        $tanggal = substr(tanggalsystem($_POST['tglCari']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari']), 6, 2);

        if ($_POST['tglCari'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        // exit("error: ".$wher);
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;
        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='AFB' 
                       and  kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . "  order by tanggal desc ";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($query2);
        if ($jlhbrs != 0) {
            $sData = "select distinct kodetransaksi, jumlah,batch,kodeorg,tanggal,post,flag,tujuan,keterangan,filememo from " . $dbname . ".bibitan_mutasi  
                        where kodetransaksi='AFB' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%'   " . $wher . " 
                        order by tanggal desc limit " . $offset . "," . $limit . " ";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";

                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                $tab.="<td>" . $rData['keterangan'] . "</td>";
				$file='';
				if($rData['filememo']!=''){
					$file="<td colspan=3><img src=images/onebit_02.png title='".$_SESSION['lang']['find']."' class=resicon onclick=lihatfile('".$rData['filememo']."','event')>";
				}
				
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }//
                elseif (($rData['flag'] == 'AUTO') && ($rData['post'] == 0)) {
                    $data = 1;
                } elseif (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                }
                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$tab.="<td  align=center width=25px><img id='detail_edit' &nbsp; style='cursor:pointer;' title='Edit " . $rData['batch'] . "' class=resicon onclick=\"filField3('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . tanggalnormal($rData['tanggal']) . "','" . substr($rData['jumlah'], 1) . "')\" src='images/application/application_edit.png'/></td>";
						
						$tab.="<td  align=center width=25px><img id='detail_del' style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delField3('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "')\" src='images/application/application_delete.png'/></td>";
						
						$tab.="<td  align=center width=25px><img id='detail_del' style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData3('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['jumlah'] . "')\" src='images/skyblue/posting.png'/></td>";
                    }else {
						$tab.="<td align=center colspan=3>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else {
                    $tab.="<td colspan=3>" . $_SESSION['lang']['posting'] . "</td>";
                }
				$tab.=" " . $file . " ";
				$tab.="</td>";
				
				

                $tab.="</tr>";
            }
            $tab.="
		<tr class=rowheader><td colspan=12 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast3(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast3(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }

        echo $tab;
        break;
    case'loadData5':
        $tanggal = substr(tanggalsystem($_POST['tglCari']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari']), 6, 2);
        if ($_POST['tglCari'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        if ($_POST['batchCari'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari'] . "%'";
        }
        if ($_POST['statCari'] != '') {
            $wher.=" and post='" . $_POST['statCari'] . "'";
        }
        // exit("error: ".$wher);

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;

        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='DBT' and  
                       kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' " . $wher . " order by tanggal desc ";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($query2);

        if ($jlhbrs != 0) {
            $sData = "select distinct kodetransaksi, jumlah,batch,kodeorg,tanggal,post,flag,keterangan,tujuan from " . $dbname . ".bibitan_mutasi  where 
                        kodetransaksi='DBT' and kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%'  " . $wher . " 
                        order by tanggal desc limit " . $offset . "," . $limit . " ";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";

                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                $tab.="<td>" . $rData['keterangan'] . "</td>";
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }//
                elseif (($rData['flag'] == 'AUTO') && ($rData['post'] == 0)) {
                    $data = 1;
                } elseif (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                }
                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$tab.="<td  align=center width=25px><img id='detail_edit' &nbsp; style='cursor:pointer;' title='Edit " . $rData['batch'] . "' class=resicon onclick=\"filField5('" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . tanggalnormal($rData['tanggal']) . "','" . $rData['jumlah'] . "')\" src='images/application/application_edit.png'/></td>";
						
						$tab.="<td  align=center width=25px><img id='detail_del' style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delField5('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "')\" src='images/application/application_delete.png'/></td>";
						
						$tab.="<td  align=center width=25px><img id='detail_del' style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData5('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['tujuan'] . "','" . $rData['jumlah'] . "')\" src='images/skyblue/posting.png'/></td>";
                    }else {
						$tab.="<td align=center colspan=3>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else {
                    $tab.="<td colspan=3>" . $_SESSION['lang']['posting'] . "</td>";
                }

                $tab.="</tr>";
            }
            $tab.="
		<tr class=rowheader><td colspan=12 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast5(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast5(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }

        echo $tab;
        break;
    case'loadData7':
        $tanggal = substr(tanggalsystem($_POST['tglCari']), 0, 4) . '-' . substr(tanggalsystem($_POST['tglCari']), 4, 2) . '-' . substr(tanggalsystem($_POST['tglCari']), 6, 2);
        if ($_POST['statCari'] != '') {
            $wher.=" and post='" . $_POST['statCari'] . "'";
        }
        if ($_POST['batchCari'] != '') {
            $wher.=" and batch like '%" . $_POST['batchCari'] . "%'";
        }
        if ($_POST['tglCari'] != '') {
            $wher.=" and tanggal like '%" . $tanggal . "%'";
        }
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
		$maxdisplay=($page*$limit);
		$no=$maxdisplay;
		
        $sql2 = "select * from " . $dbname . ".bibitan_mutasi where kodetransaksi='PNB' and  
                       kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%'   " . $wher . "  order by tanggal desc ";
        $query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($query2);
		if ($jlhbrs != 0) {
            $sData = "select distinct * from " . $dbname . ".bibitan_mutasi  where kodetransaksi='PNB' and 
                        kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%'   " . $wher . "  
                        order by tanggal desc limit " . $offset . "," . $limit . " ";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData = $qData->fetch()) {
                $data = '';
                $no+=1; //indra
                if (strlen($rData['pelanggan']) == '4') {
                    //ini harusnya ambil dari org
                    $pelanggan = $optNm[$rData['pelanggan']];
                } else {
                    //ini kalo ext ambilnya dari 
                    $pelanggan = $optnmCust[$rData['pelanggan']];
                }
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $rData['kodetransaksi'] . "</td>";
                $tab.="<td>" . $rData['batch'] . "</td>";
                $tab.="<td>" . tanggalnormal($rData['tanggal']) . "</td>";
                $tab.="<td>" . $optNm[$rData['kodeorg']] . "</td>";
                $tab.="<td align=right>" . $rData['jumlah'] . "</td>";
                $tab.="<td>" . $rData['jenistanam'] . "</td>";
                $tab.="<td>" . $rData['keterangan'] . "</td>";
                $tab.="<td>" . $rData['kodevhc'] . "</td>";


                $tab.="<td>" . $pelanggan . "</td>";
                $tab.="<td>" . $rData['afdeling'] . "</td>";
                $tab.="<td>" . $optNmkaryawan[$rData['penanggungjawab']] . "</td>";
                if (($rData['post'] == 1) && ($rData['flag'] == 'manual')) {
                    $data = 1;
                }//
                elseif (($rData['flag'] == 'AUTO') && ($rData['post'] == 0)) {
                    $data = 1;
                } elseif (($rData['post'] == 0) && ($rData['flag'] == 'manual')) {
                    $data = 0;
                }
                if ($data == 0) {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
                    $tab.="<td align=center >";
                    // $tab.="<img id='detail_edit' &nbsp; style='cursor:pointer;' title='Edit ".$rData['batch']."' class=resicon onclick=\"filField7('".$rData['kodetransaksi']."','".$rData['batch']."','".$rData['kodeorg']."','".tanggalnormal($rData['tanggal'])."','".substr($rData['jumlah'],1)."','".$rData['kodevhc']."','".$rData['sopir']."','".$rData['intex']."','".$rData['pelanggan']."','".$rData['lokasipengiriman']."','".$rData['penanggungjawab']."','".$rData['afdeling']."','".$rData['jenistanam']."','".$rData['rit']."')\" src='images/application/application_edit.png'/>";
                    $tab.="<img  style='cursor:pointer;' title='Delete " . $rData['batch'] . "' class=resicon onclick=\"delField7('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['rit'] . "','" . trim($rData['kodevhc']) . "')\" src='images/application/application_delete.png'/>";
                    $tab.="</td>";
                    $tab.="<td align=center >";
					$tab.="<img  style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon onclick=\"postingData7('" . $rData['tanggal'] . "','" . $rData['kodetransaksi'] . "','" . $rData['batch'] . "','" . $rData['kodeorg'] . "','" . $rData['rit'] . "','" . trim($rData['kodevhc']) . "','" . $rData['jumlah'] . "','" . $rData['keterangan'] . "')\" src='images/skyblue/posting.png'/>";
                    $tab.="</td>";
                    $tab.="<td align=center >";
					$tab.="<img  style='cursor:pointer;' title='Upload Data " . $rData['keterangan'] . "' class=resicon onclick=\"showupload('" . $rData['keterangan'] . "')\" src='images/upload-2-xxl.png'/>";
                    $tab.="</td>";
                    $tab.="<td align=center >";
					$tab.="<img  style='cursor:pointer;' title='PDF " . $rData['batch'] . "' class=resicon  src='images/pdf.jpg' onclick=\"masterPDF('bibitan_mutasi','" . $rData['tanggal'] . "," . $rData['kodetransaksi'] . "," . $rData['batch'] . "," . $rData['kodeorg'] . "," . $rData['rit'] . "," . trim($rData['kodevhc']) . "','','kebun_slavepengirimanBibitPdf',event)\" />";
                    $tab.="</td>";
                    }else {
						$tab.="<td align=center colspan=4>" . $_SESSION['lang']['belumposting'] . "</td>";
                    }
                } else {
					$tab.="<td align=center >";
					$tab.="</td>";
					$tab.="<td align=center >";
					$tab.="</td>";
					$tab.="<td align=center >";
					$tab.="<img  style='cursor:pointer;' title='Download Data " . $rData['keterangan'] . "' class=resicon onclick=\"showupload('" . $rData['keterangan'] . "')\" src='images/download.png'/>";
					$tab.="</td>";
                    $tab.="<td  align=center><img  style='cursor:pointer;' title='Posting Data " . $rData['batch'] . "' class=resicon  src='images/pdf.jpg' onclick=\"masterPDF('bibitan_mutasi','" . $rData['tanggal'] . "," . $rData['kodetransaksi'] . "," . $rData['batch'] . "," . $rData['kodeorg'] . "," . $rData['rit'] . "," . $rData['kodevhc'] . "','','kebun_slavepengirimanBibitPdf',event)\" /></td>";
                }

                $tab.="</tr>";
            }
            $tab.="
		<tr class=rowheader><td colspan=13 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariBast7(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariBast7(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>
		</tr>";
        } else {
            $tab.="<tr class=rowcontent><td colspan=13>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }

        echo $tab;
        break;
    case'getKet':
        $sData = "select distinct keterangan from " . $dbname . ".bibitan_mutasi  where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and kodetransaksi='" . $kodeTrans . "' ";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        $rData = $qData->fetch();
        echo $rData['keterangan'];
        break;
    case'update1':
		try {
		$owlPDO->beginTransaction();
		
			if (($kdOrg == '') || ($jmlhBibitan == '') || ($jnsBibitan == '') || ($supplierid == '') || ($tglProduksi == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}
			$scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and  kodeorg='" . $kdOrg . "' and kodetransaksi='TMB' ";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$qcek->setFetchMode(PDO::FETCH_ASSOC);
			$rcek = $qcek->fetch();
			if ($rcek['post'] == '0') {
				$supdate = "update " . $dbname . ".bibitan_batch  set jenisbibit='" . $jnsBibitan . "',supplerid='" . $supplierid . "', tanggalproduksi='" . $tglProduksi . "',jumlahdo='" . $jmlhdDo . "',jumlahterima='" . $jmlhTrima . "',jumlahafkir='" . $afkirKcmbh . "',nodo='" . $nodo . "' where batch='" . $batchVar . "' and jenisbibit='" . $oldJenisBibit . "'"; 
				$owlPDO->exec($supdate); 
				
				$supdate2 = "update " . $dbname . ".bibitan_mutasi set jumlah='" . $jmlhBibitan . "',keterangan='" . $ket . "',updateby='" . $_SESSION['standard']['userid'] . "' where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='TMB' and tanggal='" . $tglTnm . "'"; 
				$owlPDO->exec($supdate2); 
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
		#execute
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}	
	break;
    case'update4':
		try {
		$owlPDO->beginTransaction();
		
			if (($kodeBatchOld == '') || ($kodeBatch == '') || ($batchVar == '') || ($jmlhBibitan == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}

			$scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and keterangan='" . $oldJenisBibit . "' and kodetransaksi='SEB'";
            $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$qcek->setFetchMode(PDO::FETCH_ASSOC);
			$rcek = $qcek->fetch();
			if ($rcek['post'] == '0') {
                // $supdate = "update " . $dbname . ".bibitan_batch set jumlahterima='" . $jmlhBibitan . "' where batch='" . $batchVar . "' and kodeorg='".$rcek['kodeorg']."'"; 
				// $owlPDO->exec($supdate); 
                // $jmltrm = $jmlhBibitan * -1;
				
				// $supdate2 = "update " . $dbname . ".bibitan_mutasi set jumlah='" . $jmlhBibitan . "',keterangan='" . $kodeBatchOld . "',updateby='" . $_SESSION['standard']['userid'] . "' where batch='" . $batchVar . "' and keterangan='" . $oldJenisBibit . "' and kodetransaksi='SEB' and tanggal='" . $tglTnm . "'"; 
				$supdate2 = "update " . $dbname . ".bibitan_mutasi set jumlah='" . $jmlhBibitan . "',updateby='" . $_SESSION['standard']['userid'] . "' where batch='" . $batchVar . "' and keterangan='" . $oldJenisBibit . "' and kodetransaksi='SEB' and tanggal='" . $tglTnm . "'"; 
                // exit("Warning: ".$supdate2);
				$owlPDO->exec($supdate2);               
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
		#execute
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}	
	break;
    case'delData':
		try {
            $owlPDO->beginTransaction();
            
            $scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and tanggal='" . $_POST['tanggal'] . "'";
            $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
            $qcek->setFetchMode(PDO::FETCH_ASSOC);
            $rcek = $qcek->fetch();
            if ($rcek['post'] == '0') {
                $sDel = "delete from " . $dbname . ".bibitan_mutasi where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and tanggal='" . $tglTnm . "' and tanggal='" . $_POST['tanggal'] . "'";
                $owlPDO->exec($sDel); 

                $sDel2 = "delete from " . $dbname . ".bibitan_batch where batch='" . $batchVar . "' and jenisbibit='" . $oldJenisBibit . "' and tanggal='" . $_POST['tanggal'] . "'";
                $owlPDO->exec($sDel2); 
            }
            #execute
            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}	
	break;
    case'delData4':
		try {
            $owlPDO->beginTransaction();
            
            $scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodetransaksi='" . $kodeTrans . "' and tanggal='" . $_POST['tanggal'] . "'";
            $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
            $qcek->setFetchMode(PDO::FETCH_ASSOC);
            $rcek = $qcek->fetch();
            if ($rcek['post'] == '0') {
                $sDel = "delete from " . $dbname . ".bibitan_mutasi where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
                $owlPDO->exec($sDel);
            }
            #execute
            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}	
	break;
    case'delData2':
		try {
		$owlPDO->beginTransaction();
			
			$scek = "select distinct post, kodetransaksi from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='TPB' and tujuan='" . $kdOrgTjn . "' and tanggal='" . $_POST['tanggal'] . "'";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$qcek->setFetchMode(PDO::FETCH_ASSOC);
			$rcek = $qcek->fetch();
			if ($rcek['post'] == '0') {
                if ($rcek['kodetransaksi'] == 'TMB') {
                    $sDelet = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrgTjn . "' and kodetransaksi='TMB' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
                } else {
                    $sDelet = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrgTjn . "' and kodetransaksi='SEB' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
                }
				$owlPDO->exec($sDelet); 
                
				
				$sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='TPB' and tujuan='" . $kdOrgTjn . "' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
				$owlPDO->exec($sDelete2); 
			} else {
				throw new PDOException($_SESSION['lang']['post'] . "");
			}
		#execute
		$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}	
	break;
    case'delData3':
        $sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' 
            and kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
		try{
			$owlPDO->exec($sDelete2); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $sDelete2 . "\n" .$e->getMessage();
		}
        break;
		
    case'delData5':
        $sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='DBT' and  batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
		try{
			$owlPDO->exec($sDelete2); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $sDelete2 . "\n" . $e->getMessage();
		}
        break;
    case'delData7':
        $sDeleteX = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and rit='" . $_POST['rit'] . "' and kodevhc='" . $_POST['kodevhc'] . "' and batch='" . $batchVar . "'  and tanggal='" . $_POST['tanggal'] . "'";
		try{
			$owlPDO->exec($sDeleteX); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $sDeleteX . "\n" . $e->getMessage();
		}
        break;
    case'postData':
        try {
            $owlPDO->beginTransaction();
            
                $scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "'";
                $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $qcek->setFetchMode(PDO::FETCH_ASSOC);
                $rcek = $qcek->fetch();
                if ($rcek['post'] == '0') {
                    $sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and tanggal='" . $tglTnm . "' and post='0'";
                    $owlPDO->exec($sDel); 
                } else {
                    throw new PDOException($_SESSION['lang']['nodata']);
                }
            #execute
            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
	
	break;
    case'postData4':
        try {
            $owlPDO->beginTransaction();
            
                $scek = "select distinct post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and keterangan='" . $kodeBatchOld . "' and kodetransaksi='" . $kodeTrans . "'";
                $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
                $qcek->setFetchMode(PDO::FETCH_ASSOC);
                $rcek = $qcek->fetch();
                if ($rcek['post'] == '0') {
                    $sDel = "update " . $dbname . ".bibitan_mutasi set post='1' where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and keterangan='" . $kodeBatchOld . "' and tanggal='" . $tglTnm . "' and post='0'";
                    $owlPDO->exec($sDel); 

                    $sSel = "SELECT kodeorg, jumlah from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' 
                    and keterangan='" . $kodeBatchOld . "' and kodetransaksi='" . $kodeTrans . "'";
                    $rSel = fetchData($sSel);
                    foreach ($rSel as $bar) {
                        $jmlmin = $bar['jumlah'] * -1;

                        $sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, post, updateby) values
                        ('" . $kodeBatchOld . "','" . $bar['kodeorg'] . "','" . $tglTnm . "','" . $kodeTrans . "','" . $jmlmin . "','Seleksi Ke Kode Batch ".$batchVar."',
                        '1', '" . $_SESSION['standard']['userid'] . "')";
                        $owlPDO->exec($sInsert2);
                    }
                } else {
                    throw new PDOException($_SESSION['lang']['nodata']);
                }
            #execute
            $owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
	
	break;
    case'postData2':
	try {
		$owlPDO->beginTransaction();
	
        $scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
		$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
		$qcek2->setFetchMode(PDO::FETCH_ASSOC);
        $rcek2 = $qcek2->fetch();
        if (($jmlhBibitan * -1) > $rcek2['totalBibitan']) {
			throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] ." " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
        }

        $scek = "select post, keterangan, kodetransaksi from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and tujuan='" . $kdOrgTjn . "' and tanggal='" . $_POST['tanggal'] . "'";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$qcek->setFetchMode(PDO::FETCH_ASSOC);
        $rcek = $qcek->fetch();
		$keterangan = $rcek['keterangan'];

        if ($rcek['post'] == '0') {
			
            // Default Segment
            $defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

            /** Jika mutasi ke MN, maka buat jurnal */
            // if (substr($kdOrgTjn, 6, 2) == 'MN') {
                // $tanggal = str_replace('-', '', $_POST['tanggal']);

                // // Get Parameter Jurnal
				// $kodeJurnal      = $kodeTrans;
				// $param['kodeorg']= substr($kdOrg,0,4);
				// $param['periode']= substr($_POST['tanggal'],0,7);
				
				
				// $sql = "SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM ".$dbname.".keu_5parameterjurnal WHERE kodeorg='".$_SESSION['org']['induk']."' and jurnalid='".$kodeJurnal."' and kodeaplikasi ='".$kodeTrans."'";
                // $paramJurnal = fetchData($sql)[0];
				
                // if (empty($paramJurnal)){
					// throw new PDOException("Parameter Jurnal untuk " . $kodeJurnal . " belum ada");
				// }
				
                // $strAkun = "'" . $paramJurnal['noakundebet'] . "','".$paramJurnal['noakunkredit'] . "','".$paramJurnal['sampaidebet']."','".$paramJurnal['sampaikredit'] . "'";

                // // Get Jurnal
                // $qJurnal = "SELECT SUM(jumlah) as nilai FROM " . $dbname .".keu_jurnaldt WHERE LEFT(tanggal,7)<='".$param['periode']."' and noakun >= '".$paramJurnal['sampaidebet']."' and noakun <='".$paramJurnal['sampaikredit']."' and kodeorg='".$param['kodeorg']."' and kodeblok='".$kdOrg."'";
                // $resJurnal = fetchData($qJurnal);
                // $sumX = $resJurnal[0]['nilai'];
				
                // // Get Saldo Bibit
                // $qBibit = "SELECT SUM(jumlah) as nilai FROM " . $dbname .".bibitan_mutasi WHERE kodeorg='" . $kdOrg . "'";
                // $resBibit = fetchData($qBibit);
                // $sumY = 0;
                // if (!empty($resBibit)){
                    // $sumY = $resBibit[0]['nilai'];
				// }

                // // Harga Rata2
                // $sumZ = ($sumY > 0) ? $sumX / $sumY : 0;

                // // Biaya
                // $biaya = round(abs($jmlhBibitan) * $sumZ);

                // // Get Kelompok Jurnal				
				// $sql = "SELECT nokounter FROM ".$dbname.".keu_5kelompokjurnal WHERE kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' and kodekelompok ='".$kodeJurnal."'";
                // $resJurnal = fetchData($sql);
				// if(empty($resJurnal)){
					// throw new PDOException("Counter Jurnal belum ada.");
				// }
                // $counter = $resJurnal[0]['nokounter'];
                // $nojurnal = $cJournal->genNoJournal($tanggal, substr($kdOrg, 0, 4), $kodeJurnal, $counter);
				
                // // Jurnal Header
                // $dataRes['header'] = array(
					// 'nojurnal'     => $nojurnal,
					// 'kodejurnal'   => $kodeJurnal,
					// 'tanggal'      => $tanggal,
					// 'tanggalentry' => date('Ymd'),
					// 'posting'      => '0',
					// 'totaldebet'   => $biaya,
					// 'totalkredit'  => $biaya,
					// 'amountkoreksi'=> '0',
					// 'noreferensi'  => $batchVar,
					// 'autojurnal'   => '1',
					// 'matauang'     => 'IDR',
					// 'kurs'         => '1',
					// 'revisi'       => '0'
                // );

                // // Jurnal Detail - Debet
                // $dataRes['detail'][0] = array(
					// 'nojurnal'    => $nojurnal,
					// 'tanggal'     => $tanggal,
					// 'nourut'      => 1,
					// 'noakun'      => $paramJurnal['noakundebet'],
					// 'keterangan'  => 'Transplanting '.abs($jmlhBibitan).' bibit dari ' . $kdOrg . " ke " . $kdOrgTjn,
					// 'jumlah'      => $biaya,
					// 'matauang'    => 'IDR',
					// 'kurs'        => '1',
					// 'kodeorg'     => substr($kdOrg, 0, 4),
					// 'kodekegiatan'=> '',
					// 'kodeasset'   => '',
					// 'kodebarang'  => '',
					// 'nik'         => '',
					// 'kodecustomer'=> '',
					// 'kodesupplier'=> '',
					// 'noreferensi' => $batchVar,
					// 'noaruskas'   => '',
					// 'kodevhc'     => '',
					// 'nodok'       => $batchVar,
					// 'kodeblok'    => $kdOrgTjn,
					// 'revisi'      => '0',
					// 'kodesegment' => $defSegment
                // );

                // // Jurnal Detail - Kredit
                // $dataRes['detail'][1] = array(
					// 'nojurnal'    => $nojurnal,
					// 'tanggal'     => $tanggal,
					// 'nourut'      => 2,
					// 'noakun'      => $paramJurnal['noakunkredit'],
					// 'keterangan'  => 'Transplanting '.abs($jmlhBibitan).' bibit dari ' . $kdOrg . " ke " . $kdOrgTjn,
					// 'jumlah'      => $biaya * (-1),
					// 'matauang'    => 'IDR',
					// 'kurs'        => '1',
					// 'kodeorg'     => substr($kdOrg, 0, 4),
					// 'kodekegiatan'=> '',
					// 'kodeasset'   => '',
					// 'kodebarang'  => '',
					// 'nik'         => '',
					// 'kodecustomer'=> '',
					// 'kodesupplier'=> '',
					// 'noreferensi' => $batchVar,
					// 'noaruskas'   => '',
					// 'kodevhc'     => '',
					// 'nodok'       => $batchVar,
					// 'kodeblok'    => $kdOrg,
					// 'revisi'      => '0',
					// 'kodesegment' => $defSegment
                // );
				
				// $qHeader = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				// $owlPDO->exec($qHeader); 
				
                // $qDetail = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
				// $owlPDO->exec($qDetail); 
				
				// $queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$counter+1), "kodekelompok='".$kodeJurnal."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' and periode='".$param['periode']."'");
				// $owlPDO->exec($queryKonter);
            // }

            //execute 2 query on one script
            if ($rcek['kodetransaksi'] == 'TMB') {
                $sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where kodetransaksi='TMB' and batch='" . $batchVar . "' and kodeorg='" . $kdOrgTjn . "' and post='0' and tanggal='" . $_POST['tanggal'] . "' and flag='AUTO';";
            } else {
                $sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where kodetransaksi='SEB' and batch='" . $batchVar . "' and kodeorg='" . $kdOrgTjn . "' and post='0' and tanggal='" . $_POST['tanggal'] . "' and flag='AUTO';";
            }
            
			$owlPDO->exec($sDel); 
				
			// $su = "update " . $dbname . ".bibitan_mutasi set post=1, keterangan='".$keterangan.", nojurnal : ".$nojurnal."' where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and tujuan='" . $kdOrgTjn . "' and post='0' and tanggal='" . $_POST['tanggal'] . "'";
			$su = "update " . $dbname . ".bibitan_mutasi set post=1, keterangan='".$keterangan."' where kodetransaksi='" . $kodeTrans . "' and batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and tujuan='" . $kdOrgTjn . "' and post='0' and tanggal='" . $_POST['tanggal'] . "'";
			$owlPDO->exec($su); 
        } else {
            throw new PDOException($_SESSION['lang']['post'] . "");
        }
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Errorcode, " . addslashes($e->getMessage());die();}
	
	break;
    case'postData3':
		try {
		$owlPDO->beginTransaction();
		
			$scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			if (($jmlhBibitan * -1) > $rcek2['totalBibitan']) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}
			$scek = "select post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and tanggal='" . $_POST['tanggal'] . "' and post=1";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$rcek=owlBaris($qcek);
			if ($rcek == '0') {
				$sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and tanggal='" . $_POST['tanggal'] . "'";
				$owlPDO->exec($sDel); 
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}			
	break;
    case'postData5':
		try {
		$owlPDO->beginTransaction();
		
			$scek = "select post from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and tanggal='" . $_POST['tanggal'] . "' and post=1";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$rcek=owlBaris($qcek);
			if ($rcek == '0') {
				$sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where batch='" . $batchVar . "' and kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and tanggal='" . $_POST['tanggal'] . "'";
				$owlPDO->exec($sDel); 
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}		
	break;
    case'postData7':
		try {
		$owlPDO->beginTransaction();
		
			$scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			
			if (($jmlhBibitan * -1) > $rcek2['totalBibitan']) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}


			$scek = "select distinct post from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and keterangan='".$keterangan."' and rit='" . $jmlRit . "' and kodevhc like '" . $kdvhc . "%' and batch='" . $batchVar . "' and tanggal='" . $_POST['tanggal'] . "'";
			$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			$qcek->setFetchMode(PDO::FETCH_ASSOC);
			$rcek = $qcek->fetch();
			
			if ($rcek['post'] == '0') {
				$sDel = "update " . $dbname . ".bibitan_mutasi set post=1 where kodeorg='" . $kdOrg . "' and kodetransaksi='" . $kodeTrans . "' and keterangan='".$keterangan."' and rit='" . $jmlRit . "' and kodevhc like '" . $kdvhc . "%' and batch='" . $batchVar . "' and post='0' and tanggal='" . $_POST['tanggal'] . "'";
				$owlPDO->exec($sDel); 
				
			} else {
				throw new PDOException($_SESSION['lang']['post']);
			}
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case 'showuploadd':
        $tab="";
        $tab.="
        <table border=0 >
            <tr>
                <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                <td>:</td>
                <td id='notranupload'>". $keterangan."</td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' >
                </td>
            </tr>
            <tr>
                <td style=vertical-align:top>Status</td>
                <td style=vertical-align:top>:</td>
                <td>
                    <progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
                    <p id='status'></p>
                    <p id='loaded_n_total'></p>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button id=btnsubmit class=mybutton onclick=\"submitfile('".$keterangan."')\">Submit</button>
                </td>
            </tr>
        </table>
        ";
        $str = "select * from ".$dbname.".bibitan_mutasi where keterangan='".$keterangan."'"; #mahe
        $res = fetchData($str);
        if($res[0]['post']==1){
            $tab="<b>List File Upload<br></b>";
        }
        $tab.="
            <table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center' width=30px>No.</td>
                    <td align='center' width=50px>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center' width=30px colspan=2>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        ";

        echo $tab;
    break;
    case 'submitfile':
        try {
        $owlPDO->beginTransaction();
        $data = $_POST;
        if(count($data)==0){
            $data = $_GET;          
        }

		$path ='fileupload/bbtmemo/';
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $_FILES['file']['name'];
                //$filename = "BKM".date("YmdHis");
                #cek duplikasi nama file
                $str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
                $res=fetchData($str);
                if(count($res)>0){
                    throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
                }
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    $str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
                    values ('".$param['notransaksi']."','".$filename."','".$filetype."','EBBT','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
                    $owlPDO->exec($str);
                    file_put_contents($path.$filename,$file_tmpname);
                }else{
                    throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
                }
                if (!file_exists($path.$filename)) {
                    throw new PDOException("Upload file gagal.".$path.$filename);
                }
            }
        }else{
            throw new PDOException("Upload file gagal.");
        }
        #execute
        $owlPDO->commit();
    } catch (PDOException $e) {
        $owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
    }
    break;
    case 'loadfiles':
        $str= "select * from ".$dbname.".bibitan_mutasi where keterangan = '".$keterangan."'";
        $res= fetchData($str);
        $jurnal = $res[0]['post'];
        
        $no = 0;
        $tab= "";
        $str= "select * from ".$dbname.".listfileupload where notransaksi = '".$keterangan."' and status='1'";
        $res= fetchData($str);
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
                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
                if($jurnal==0){                 
                    $tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
                    
                    $tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
                }else{
                    $tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
                }
                $tab.="</tr>";
            }
        }
        echo $tab;
    break;
    case 'deletefile':
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$keterangan."' and namafile='".$param['namafile']."'";
        try{
            $owlPDO->exec($str);
            $pathx = $path.$param['namafile'];
            #sementara tidak boleh ada unlink
            //unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    case'getKodeorg':
        $optKdorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		// print_r($param);
		// exit("error");
        if ($batchVar != '') {
            $sData = "select distinct kodeorg from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "'";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rOrg2 = $qData->fetch()) {
				$optKdorg.="<option value=" . $rOrg2['kodeorg'] . " selected >" . $optNm[$rOrg2['kodeorg']] . "</option>";
				// if($param['divisi']==$rOrg2['kodeorg']){
					// $optKdorg.="<option value=" . $rOrg2['kodeorg'] . " selected >" . $optNm[$rOrg2['kodeorg']] . "</option>";
				// }else{					
					// $optKdorg.="<option value=" . $rOrg2['kodeorg'] . " >" . $optNm[$rOrg2['kodeorg']] . "</option>";
				// }
            }
            echo $optKdorg;
        }
        break;
    case'getKodeorgN':
        $optKdorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($batchVar != '') {
            $sData = "select distinct kodeorg from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "' and kodeorg not like '%PN%'";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
            while ($rOrg2 = $qData->fetch()) {
                $optKdorg.="<option value=" . $rOrg2['kodeorg'] . ">" . $optNm[$rOrg2['kodeorg']] . "</option>";
            }
            echo $optKdorg;
        }
        break;
    case'cekSmGak':
        $sData = "select distinct kodeorg from " . $dbname . ".bibitan_mutasi where batch='" . $batchVar . "'";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        $rData = $qData->fetch();
        if ($rData['kodeorg'] == $kdOrg) {
            echo "1";
        }
        break;
    case'saveTab2':
		try {
		    $owlPDO->beginTransaction();
		
			if (($batchVar == '') || ($jmlhBibitan == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}

			$str = " select * from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrgTjn . "' and kodetransaksi IN ('TMB','SEB') and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "' and post=1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if ($numrows > 0) {
				throw new PDOException($_SESSION['lang']['exist']);
			}

			$scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			
			if ($jmlhBibitan > abs($rcek2['totalBibitan'])) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}
			
            if ($kodeTrans == 'TMB') {
                $sDelet = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrgTjn . "' and kodetransaksi='TMB' and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "' and flag='AUTO'";
            } else {
                $sDelet = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrgTjn . "' and kodetransaksi='SEB' and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "' and flag='AUTO'";
            }
			$owlPDO->exec($sDelet); 
				
			$sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='TPB' and tanggal='" . $tglTnm . "' and tujuan='" . $kdOrgTjn . "' and batch='" . $batchVar . "'";
			$owlPDO->exec($sDelete2); 
				
			$jmlh = $jmlhBibitan * -1;
			$sInsert = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby,tujuan)  values('" . $batchVar . "','" . $kdOrg . "','" . $tglTnm . "','TPB','" . $jmlh . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "','" . $kdOrgTjn . "')";
			$owlPDO->exec($sInsert); 
            
            if ($kodeTrans == 'TMB') {
                $sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby,flag) 
                values('" . $batchVar . "','" . $kdOrgTjn . "','" . $tglTnm . "','TMB','" . $jmlhBibitan . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "','AUTO')";
            } else {
                $sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby,flag) 
                values('" . $batchVar . "','" . $kdOrgTjn . "','" . $tglTnm . "','SEB','" . $jmlhBibitan . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "','AUTO')";
            }
            
			$owlPDO->exec($sInsert2); 
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
			
	break;
    case'saveTab3':
		try {
		$owlPDO->beginTransaction();
		
			if (($kdOrg == '') || ($batchVar == '') || ($jmlhBibitan == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}
			$str = " select * from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='AFB' and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "' and post=1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$numrows=owlBaris($res);
			if ($numrows > 0) {
				throw new PDOException($_SESSION['lang']['exist']);
			}

			$scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			if ($jmlhBibitan > $rcek2['totalBibitan']) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}

			$sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='AFB' and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "'";
			$owlPDO->exec($sDelete2); 

			$jmlh = $jmlhBibitan * -1;
			$sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby) 
			values('" . $batchVar . "','" . $kdOrg . "','" . $tglTnm . "','AFB','" . $jmlh . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
			$owlPDO->exec($sInsert2); 
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case'saveTab5':
		try {
		$owlPDO->beginTransaction();
			
			if (($kdOrg == '') || ($batchVar == '') || ($jmlhBibitan == '') || ($tglTnm == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}
			$str = " select * from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='DBT'  and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "' and post=1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if ($numrows > 0) {
				throw new PDOException($_SESSION['lang']['exist']);
			}
			
			$scek2 = "select  sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post=1 group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			if ($jmlhBibitan > $rcek2['totalBibitan']) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}

			$sDelete2 = "delete from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and kodetransaksi='DBT'  and batch='" . $batchVar . "' and tanggal='" . $tglTnm . "'";
			$owlPDO->exec($sDelete2); 
				
			$sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby) 
			values('" . $batchVar . "','" . $kdOrg . "','" . $tglTnm . "','DBT','" . $jmlhBibitan . "','" . $ket . "','" . $_SESSION['standard']['userid'] . "')";
			$owlPDO->exec($sInsert2); 
				
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case'saveTab7':
		try {
		$owlPDO->beginTransaction();
		
			if (($kdOrg == '') || ($batchVar == '') || ($jmlhBibitan == '') || ($tglTnm == '') || ($intexDt == '') || ($kdvhc == '') || ($nmSupir == '') || ($assistenPnb == '') || ($custId == '') || ($jmlRit == '')) {
				throw new PDOException($_SESSION['lang']['isifield']);
			}

			$scek2 = "select sum(jumlah) as totalBibitan from " . $dbname . ".bibitan_mutasi where kodeorg='" . $kdOrg . "' and batch='" . $batchVar . "' and post='1' group by kodeorg";
			$qcek2=$owlPDO->query($scek2) or die(print " Gagal: ".PDOException::getMessage());
			$qcek2->setFetchMode(PDO::FETCH_ASSOC);
			$rcek2 = $qcek2->fetch();
			if ($jmlhBibitan > $rcek2['totalBibitan']) {
				throw new PDOException($_SESSION['lang']['jumlah'] . " " . $jmlhBibitan . " " . $_SESSION['lang']['greater'] . " " . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['stock'] . " " . $rcek2['totalBibitan'] . " " . $_SESSION['lang']['on'] . " " . $_SESSION['lang']['batch'] . " " . $batchVar . " " . $_SESSION['lang']['lokasi'] . " " . $kdOrg);
			}

			$jmlh = $jmlhBibitan * -1;
			$sInsert2 = "insert into " . $dbname . ".bibitan_mutasi (batch, kodeorg, tanggal, kodetransaksi, jumlah, keterangan, updateby, kodevhc, sopir, intex, pelanggan, lokasipengiriman, penanggungjawab,jenistanam,afdeling,rit,disetujuioleh,diterimaoleh) 
			values('" . $batchVar . "','" . $kdOrg . "','" . $tglTnm . "','PNB','" . $jmlh . "','" . $ket . "' ,'" . $_SESSION['standard']['userid'] . "','" . $kdvhc . "','" . $nmSupir . "','" . $intexDt . "','" . $custId . "','" . $detPeng . "','" . $assistenPnb . "','" . $KegiatanId . "','" . $kodeAfd . "','" . $jmlRit . "'
            ,'" . $kplDivBbt . "','" . $kplDivKbn . "')";
            $owlPDO->exec($sInsert2); 
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case'getCust':
        $optKode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        if ($intexDt != '') {
            if ($intexDt == '0' || $intexDt == '1' || $intexDt == '6') {
                $sOpt = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['org']['kodeorganisasi'] . "' and tipe='KEBUN' and inti='1'";
            } elseif ($intexDt == '2'|| $intexDt == '3' || $intexDt == '7') {
                $sOpt = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $_SESSION['org']['kodeorganisasi'] . "' and tipe='KEBUN' and inti='0'";
            } elseif ($intexDt == '5') {
                $sOpt = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk != '" . $_SESSION['org']['kodeorganisasi'] . "'  and tipe='KEBUN'";
            } elseif ($intexDt == '4' || $intexDt == '8') {
                $sOpt = "select kodecustomer as kodeorganisasi,namacustomer as namaorganisasi from " . $dbname . ".pmn_4customer  order by namacustomer asc";
            }
			$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
			$qOpt->setFetchMode(PDO::FETCH_ASSOC);
            while ($rOpt = $qOpt->fetch()) {
                if ($kdOrg != '') {//
                    $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "' " . ($rOpt['kodeorganisasi'] == $kdOrg ? 'selected' : '') . ">" . $rOpt['namaorganisasi'] . "</option>";
                } else {
                    $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "'>" . $rOpt['namaorganisasi'] . "</option>";
                }
            }
        }

        echo $optKode;
        break;
		
    case'getAfd':
        $optKode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sOpt = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $kdOrg . "'  order by namaorganisasi asc";
		$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
		$qOpt->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOpt = $qOpt->fetch()) {
            if ($kdOrg != '') {//
                $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "' " . ($rOpt['kodeorganisasi'] == $kodeAfd ? 'selected' : '') . ">" . $rOpt['namaorganisasi'] . "</option>";
            } else {
                $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "'>" . $rOpt['namaorganisasi'] . "</option>";
            }
        }
        echo $optKode;
        break;
		
    case'getBlok':
        $optKode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $optKaryawan3 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        // Get Intex Yang External
        $intexternal = array("4","8");

        if (!in_array($intexDt, $intexternal)) {
            $thntnmkd = makeOption($dbname,"setup_blok","kodeorg,tahuntanam");
            $sOpt = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk like '" . $kdOrg . "%' and tipe = 'blok' order by namaorganisasi asc";
            $qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
            $qOpt->setFetchMode(PDO::FETCH_ASSOC);
            while ($rOpt = $qOpt->fetch()) {
                if ($kdOrg != '') { 
                    $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "' " . ($rOpt['kodeorganisasi'] == $kodeAfd ? 'selected' : '') . ">" . $rOpt['kodeorganisasi'] . " - " . $thntnmkd[$rOpt['kodeorganisasi']] . "</option>";
                } else {
                    $optKode.="<option value='" . $rOpt['kodeorganisasi'] . "'>" . $rOpt['kodeorganisasi'] . " - " . $thntnmkd[$rOpt['kodeorganisasi']] . "</option>";
                }
            }

            $sKaryawan = "select distinct karyawanid,namakaryawan from " . $dbname . ".datakaryawan a
            left join " . $dbname . ".sdm_5jabatan b ON a.kodejabatan = b.kodejabatan
            where tipekaryawan='0' and karyawanid!='" . $_SESSION['standard']['userid'] . "'
            AND substr(lokasitugas,3,2) != 'HO'
            AND a.kodejabatan IN('4','6')";
            $qKaryawan=$owlPDO->query($sKaryawan) or die(print " Gagal: ".PDOException::getMessage());
            $qKaryawan->setFetchMode(PDO::FETCH_ASSOC);
            while ($rKaryawan = $qKaryawan->fetch()) {
                $optKaryawan3.="<option value='" . $rKaryawan['karyawanid'] . "'>" . $rKaryawan['namakaryawan'] . "</option>";
            }
        } else if (in_array($intexDt, $intexternal)) {
            $sKaryawan = "select kodecustomer as karyawanid,namacustomer as namakaryawan from " . $dbname . ".pmn_4customer  order by namacustomer asc";
            $qKaryawan=$owlPDO->query($sKaryawan) or die(print " Gagal: ".PDOException::getMessage());
            $qKaryawan->setFetchMode(PDO::FETCH_ASSOC);
            while ($rKaryawan = $qKaryawan->fetch()) {
                if ($kdOrg != "") {
                    $optKaryawan3.="<option value='" . $rKaryawan['karyawanid'] . "' " . ($rOpt['karyawanid'] == $kdOrg ? 'selected' : '') . ">" . $rKaryawan['namakaryawan'] . "</option>";
                } else {
                    $optKaryawan3.="<option value='" . $rKaryawan['karyawanid'] . "'>" . $rKaryawan['namakaryawan'] . "</option>";
                }
                
            }
        }
        echo $optKode."###".$optKaryawan3;
    break;
		
    case'getBatch':
        $optBatch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sBatch = "select distinct batch from " . $dbname . ".bibitan_mutasi where kodeorg like '" . $_SESSION['empl']['lokasitugas'] . "%' order by batch desc";
        $qBatch=$owlPDO->query($sBatch) or die(print " Gagal: ".PDOException::getMessage());
		$qBatch->setFetchMode(PDO::FETCH_ASSOC);
        while ($rBatch = $qBatch->fetch()) {
            $optBatch.="<option value='" . $rBatch['batch'] . "'>" . $rBatch['batch'] . "</option>";
        }
        echo $optBatch;
    break;
    case 'getBlokSEB':
    	$str = "select  batch,kodeorg,sum(jumlah) as jumlah from " . $dbname . ".bibitan_mutasi 
					where kodeorg like '%" . $_SESSION['empl']['lokasitugas'] . "%' and batch='".$kodeBatchOld."' and post=1 
					group by batch,kodeorg ";

        $optKdorg3 =  "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        // $str = "SELECT a.kodeorg FROM $dbname.bibitan_batch a JOIN $dbname.bibitan_mutasi b ON a.batch = b.batch
        // WHERE a.batch='".$kodeBatchOld."' AND b.kodetransaksi='TMB'";
        $res = fetchData($str);
        foreach ($res as $val) {
        	if($val['jumlah']>0){
            	$optKdorg3 .=  "<option value='".$val['kodeorg']."'>" . getNamaOrg($val['kodeorg']) . "</option>";
        	}
        }

        echo $optKdorg3;
    break;
    default:
        break;
}
?>
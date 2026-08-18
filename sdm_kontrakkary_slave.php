<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$notransaksi= checkPostGet('notransaksi', '');
$jenis= checkPostGet('jenis', '');
$pt= checkPostGet('pt', '');
$karyawanid= checkPostGet('karyawanid', '');
$atasanlangsung= checkPostGet('atasanlangsung', '');
$gajipokok= checkPostGet('gajipokok', '');
$tunjjabatan= checkPostGet('tunjjabatan', '');
$konsumsi= checkPostGet('konsumsi', '');
$transport= checkPostGet('transport', '');
$uangdaerah= checkPostGet('uangdaerah', '');
$cuti= checkPostGet('cuti', '');
$poh= checkPostGet('poh', '');
$tiketcuti= checkPostGet('tiketcuti', '');
$perumahan= checkPostGet('perumahan', '');
$telekomunikasi= checkPostGet('telekomunikasi', '');
$tanggal= tanggalsystemn(checkPostGet('tanggal', ''));
$pihakpertama= checkPostGet('pihakpertama', '');
$dikeluarkan= checkPostGet('dikeluarkan', '');
$tanggaldari= tanggalsystemn(checkPostGet('tanggaldari', ''));
$tanggalsampai= tanggalsystemn(checkPostGet('tanggalsampai', ''));
$jangkawaktu= checkPostGet('jangkawaktu', '');
$satjangka= checkPostGet('satjangka', '');

$gajipokok=str_replace(',','',$gajipokok);
$tunjjabatan=str_replace(',','',$tunjjabatan);
$konsumsi=str_replace(',','',$konsumsi);
$transport=str_replace(',','',$transport);
$uangdaerah=str_replace(',','',$uangdaerah);
$perumahan=str_replace(',','',$perumahan);
$telekomunikasi=str_replace(',','',$telekomunikasi);

$divsch = checkPostGet('divsch', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$today=date('Y-m-d');
$todayhis=date('Y-m-d h:i:s');

switch ($method) {
    	
    case'insert':
		#buat notransaksi
		$tempbln = explode('-',$tanggal);
		$bln = $tempbln[1];
		#001/ TML/ PKWTT/ III/ 2017
        $sql = "select max(substr(notransaksi,1,3)) as nomorurut from " . $dbname . ".sdm_kontrakkary where pt='" . $pt . "' and jenis='" . $jenis . "' and tanggal like '" . $tempbln[0] . "%' order by nomorurut desc limit 1";
		$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if(intval($bar['nomorurut'])==0){
          $noawal = 1;
        }else{
          $noawal = intval($bar['nomorurut'])+1;
        }
        $notran=addZero($noawal,3)."/".$pt."/".$jenis."/".romawi($bln)."/".$tempbln[0]."";
		
		$cols = array(
				'notransaksi','jenis','pt','karyawanid','atasanlangsung','gajipokok','tunjjabatan','konsumsi','transport','uangdaerah','cuti','poh','tiketcuti','perumahan','telekomunikasi','tanggal','pihakpertama','dikeluarkan','tanggaldari','tanggalsampai','jangkawaktu','satjangka','createdby','createtime','updateby'
				);
		$data = array(
				'notransaksi'=>$notran,
				'jenis'=>$jenis,
				'pt'=>$pt,
				'karyawanid'=>$karyawanid,
				'atasanlangsung'=>$atasanlangsung,
				'gajipokok'=>$gajipokok,
				'tunjjabatan'=>$tunjjabatan,
				'konsumsi'=>$konsumsi,
				'transport'=>$transport,
				'uangdaerah'=>$uangdaerah,
				'cuti'=>$cuti,
				'poh'=>$poh,
				'tiketcuti'=>$tiketcuti,
				'perumahan'=>$perumahan,
				'telekomunikasi'=>$telekomunikasi,
				'tanggal'=>$tanggal,
				'pihakpertama'=>$pihakpertama,
				'dikeluarkan'=>$dikeluarkan,
				'tanggaldari'=>$tanggaldari,
				'tanggalsampai'=>$tanggalsampai,
				'jangkawaktu'=>$jangkawaktu,
				'satjangka'=>$satjangka,
				'createdby'=>$_SESSION['standard']['userid'],
				'createtime'=>$todayhis,
				'updateby'=>$_SESSION['standard']['userid']
				);
		$str = insertQuery($dbname,'sdm_kontrakkary',$data,$cols);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	echo "Kontrak karyawan an : ".$nmkar[$karyawanid]." dengan nomor : <b>".$notran." </b>sudah di buat !!!";
		// exit('Error : Data sudah ada !'.$notran);

	break;
	
	case'update':
		$data=array(
				'jenis'=>$jenis,
				'pt'=>$pt,
				'karyawanid'=>$karyawanid,
				'atasanlangsung'=>$atasanlangsung,
				'gajipokok'=>$gajipokok,
				'tunjjabatan'=>$tunjjabatan,
				'konsumsi'=>$konsumsi,
				'transport'=>$transport,
				'uangdaerah'=>$uangdaerah,
				'cuti'=>$cuti,
				'poh'=>$poh,
				'tiketcuti'=>$tiketcuti,
				'perumahan'=>$perumahan,
				'telekomunikasi'=>$telekomunikasi,
				'tanggal'=>$tanggal,
				'pihakpertama'=>$pihakpertama,
				'dikeluarkan'=>$dikeluarkan,
				'tanggaldari'=>$tanggaldari,
				'tanggalsampai'=>$tanggalsampai,
				'jangkawaktu'=>$jangkawaktu,
				'satjangka'=>$satjangka,
				'updateby'=>$_SESSION['standard']['userid']
			);
        $str = updateQuery($dbname,'sdm_kontrakkary',$data,$where="notransaksi='".$notransaksi."'");
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		echo "Kontrak karyawan an : ".$nmkar[$karyawanid]." dengan nomor : <b>".$notransaksi." </b>sudah di update !!!";
	break;
    case'delete':
        $str = "delete from " . $dbname . ".sdm_kontrakkary where notransaksi='".$notransaksi."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
    break;
    case'getpoh':
        $str = "select kota from " . $dbname . ".datakaryawan where karyawanid='".$karyawanid."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();

        echo trim($bar['kota'],' ');
    break;
    case'loaddata':
        $where = "";
        if ($divsch != '') {
            $where.=" and pt='" . $divsch . "' ";
        }
        
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		if ($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$where.=" and b.lokasitugas ='".$_SESSION['empl']['lokasitugas']."'";
		}

		$sql = "SELECT a.*, b.namakaryawan
		FROM " . $dbname . ".sdm_kontrakkary a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $where . "";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
		
        $str = "SELECT a.*, b.namakaryawan
		FROM " . $dbname . ".sdm_kontrakkary  a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $where . " order by pt asc, notransaksi asc, namakaryawan asc limit " . $offset . "," . $limit . ""; //exit('error'.$str);
		$tab = "";
        $no = $maxdisplay;

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
        $res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$tab.="<tr class=rowcontent  id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['pt'] . " - " . $nmorg[$bar['pt']] . "</td>";
				$tab.="<td>" . $bar['notransaksi'] . "</td>";
				$tab.="<td>" . $bar['namakaryawan'] . "</td>";
				$tab.="<td>" . $bar['jenis'] . "</td>";
				$tab.="<td align=left>" . $nmkar[$bar['updateby']] . "</td>";

				$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"edit('".$bar['notransaksi']."','".$bar['jenis']."','".$bar['pt']."','".$bar['karyawanid']."','".$bar['atasanlangsung']."','".$bar['gajipokok']."','".$bar['tunjjabatan']."','".$bar['konsumsi']."','".$bar['transport']."','".$bar['uangdaerah']."','".$bar['cuti']."','".$bar['poh']."','".$bar['tiketcuti']."','".$bar['perumahan']."','".$bar['telekomunikasi']."','".$bar['tanggal']."','".$bar['pihakpertama']."','".$bar['dikeluarkan']."','".$bar['tanggaldari']."','".$bar['tanggalsampai']."','".$bar['jangkawaktu']."','".$bar['satjangka']."');\" ></td>";
				
				$isi.="<td align=center><img class=resicon src=images/application/application_delete.png onclick=\"del('".$bar['notransaksi']."');\" title='Delete'></td>";
				
				$isi.="<td align=center><img src=images/pdf.jpg class=resicon  title='pdf' onclick=\"pdf('".$bar['jenis']."','".$bar['notransaksi']."');\"></td>";
				
				$tab.=$isi;
				$tab.="</tr>";
			}
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
        $footd.="</tr>
                     <tr><td colspan=9 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

	break;
	case'getkary':
		$optkary="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$where='';
		if ($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$where=" and lokasitugas ='".$_SESSION['empl']['lokasitugas']."'";
		}
		$str="select * from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00'  and statuskaryawan != 'Keluar'  ".$where." and kodeorganisasi='".$pt."' order by namakaryawan asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkary.="<option value=".$bar['karyawanid'].">".$bar['namakaryawan']." - ".$bar['nik']." ".$bar['lokasitugas']."</option>";
		}
	echo $optkary;
	break;
	
}
?>	
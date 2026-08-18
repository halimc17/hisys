<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');


$method = checkPostGet('method', '');
switch ($method){
	default:
		$limit = 20;
		$page = 0;
		//========================
		//ambil jumlah baris dalam tahun ini

		$notransaksi = '';
		if (isset($_POST['tex'])) {
			$notransaksi.=" and notransaksi like '%" . $_POST['tex'] . "%' ";
		}
		$str = "select count(*) as jlhbrs from " . $dbname . ".sdm_pjdinasht 
				where karyawanid='" . $_SESSION['standard']['userid'] . "' and namatamu=''
				" . $notransaksi;
		// exit('warning : '.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$jlhbrs = $bar->jlhbrs;
		}
		//==================

		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}


		$offset = $page * $limit;


		$str = "select * from " . $dbname . ".sdm_pjdinasht 
				where
				karyawanid='" . $_SESSION['standard']['userid'] . "'
						" . $notransaksi . " and namatamu=''
						order by tanggalbuat desc  limit " . $offset . ",20";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no = $page * $limit;
		while ($bar = $res->fetch()) {
			$no+=1;

			$namakaryawan = '';
			$strx = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=" . $bar->karyawanid;
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_OBJ);
			while ($barx = $resx->fetch()) {
				$namakaryawan = $barx->namakaryawan;
			}

			$add = '';
			if($bar->statuspertanggungjawaban == 1){
				$add.="<img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']." (Task Result Description)' onclick=\"previewPJDUraian('".$bar->notransaksi."',event);\">";
			}else{
				$add.="<img src=images/pdf.jpg class=resicon  style='filter: grayscale(100%);' title='".$_SESSION['lang']['pdf']." (Task Result Description)' onclick=\"alert('Belum selesai di Verifikasi');\" >";
			}
			if($bar->posting==0 and $bar->statuspertanggungjawaban == 0){
				$add.="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editPPJD('".$bar->notransaksi."');\">";
				$add.=" <img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar->notransaksi."');\">";
			}
			if ($bar->statuspertanggungjawaban == 2)
				$stpersetujuan = $_SESSION['lang']['ditolak'];
			else if ($bar->statuspertanggungjawaban == 1)
				$stpersetujuan = $_SESSION['lang']['disetujui'];
			else
				$stpersetujuan = $_SESSION['lang']['wait_approve'];

			$str1 = "select sum(jumlahhrd) as jumlah from " . $dbname . ".sdm_pjdinasdt 
				 where notransaksi='" . $bar->notransaksi . "' and sumber = '1' ";
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			$usage = 0;
			while ($bar1 = $res1->fetch()) {
				$usage = $bar1->jumlah;
			}


			echo"<tr class=rowcontent>
				  <td align=center>" . $no . "</td>
				  <td>" . $bar->notransaksi . "</td>
				  <td>" . $namakaryawan . "</td>
				  <td>" . tanggalnormal($bar->tanggalbuat) . "</td>
				  <td>" . $bar->tujuan1 . "</td>
				  <td align=right>" . number_format($bar->uangmuka, 2, '.', ',') . "</td>
				  <td align=right>" . number_format($usage, 2, '.', ',') . "</td>	  
				  <td>" . $stpersetujuan . "</td>
				  <td align=center>
					 <img src=images/pdf.jpg class=resicon  title='" . $_SESSION['lang']['pdf'] . "' onclick=\"previewPJD('" . $bar->notransaksi . "',event);\">                 
					<img src=images/addplus.png class=resicon class=zImgBtn height='30'  title='Upload' onclick=\"uploaddata('".$bar->notransaksi."');\" >
			   " . $add . "
				  </td>
				  </tr>";
		}
		echo"<tr><td colspan=11 align=center>
			   " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
				   <br>
			   <button class=mybutton onclick=cariPJD(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
				   <button class=mybutton onclick=cariPJD(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
				   </td>
				   </tr>";
	break;
	case 'getnotransaksi':
		$str="select * from ".$dbname.".sdm_pjdinasht where karyawanid=".$_SESSION['standard']['userid']." and lunas=0 and statuspertanggungjawaban=0 and statuspersetujuan=1 and posting=0 and namatamu=''";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optNo='';
		$optNo.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while($bar=$res->fetch())
		{
			$optNo.="<option value='".$bar->notransaksi."'>".$bar->notransaksi."</option>";
		}
		echo $optNo;
	break;
}
?>
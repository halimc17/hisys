<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method = checkPostGet('method','');
$id = checkPostGet('id','');
$pemberikuasa = checkPostGet('pemberikuasa','');
$kuasadariwajibpajak = checkPostGet('kuasadariwajibpajak','');
$nomorsuratkhusus = checkPostGet('nomorsuratkhusus','');
$tanggalsuratkhusus = checkPostGet('tanggalsuratkhusus','');
$penerimakuasa = checkPostGet('penerimakuasa', '');
$berupa7 = checkPostGet('berupa7','');
$berupa8 = checkPostGet('berupa8','');
$kota = checkPostGet('kota', '');
$tanggal = tanggalsystem(checkPostGet('tanggal',''));



	
switch($method){
	case 'viewpdf':
	$str="select a.id, a.kota, a.nomorsuratkhusus, a.tanggalsuratkhusus, a.berupa7, a.berupa8, b.namakaryawan as nama_pemberikuasa,b.npwp as npwp_pemberikuasa, a.kuasadariwajibpajak,c.namakaryawan as nama_penerimakuasa,c.npwp as npwp_penerimakuasa,d.namajabatan, a.tanggal, e.namajabatan as jabatanpemberikuasa
	from ".$dbname.".pajak_suratpenunjukanpajak a
	left join datakaryawan b on b.karyawanid = a.pemberikuasa
	left join datakaryawan c on c.karyawanid = a.penerimakuasa
	left join sdm_5jabatan d on d.kodejabatan = c.kodejabatan
	left join sdm_5jabatan e on e.kodejabatan = b.kodejabatan
	where a.id ='".$id."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$namapemberikuasa=$bar['nama_pemberikuasa'];
	$npwppemberikuasa=$bar['npwp_pemberikuasa'];
	$kuasadariwajibpajak=$bar['kuasadariwajibpajak'];
	$namapenerimakuasa=$bar['nama_penerimakuasa'];
	$npwppenerimakuasa=$bar['npwp_penerimakuasa'];
	$jabatan=$bar['namajabatan'];
	$jabatanpemberikuasa=$bar['jabatanpemberikuasa'];
	$berupa7=$bar['berupa7'];
	$berupa8=$bar['berupa8'];
	$tgl=$bar['tanggal'];
	$nomorsuratkhusus=$bar['nomorsuratkhusus'];
	$tanggalsuratkhusus=$bar['tanggalsuratkhusus'];
	$kota=$bar['kota'];


	$tab='';
	$tab.="<table width=100% border=0>";
	$tab.="<tr>
	<td align=center></td>
	<td align=center><img src=images/garuda.png style='width:90px;height:90px'></td>
	<td rowspan=3><font size=2><br><br><br>LAMPIRAN IV<br>PERATURAN MENTERI KEUANGAN<br>NOMOR&nbsp;&nbsp; 22/PMK.03/2008&nbsp; TENTANG<br>PERSYARATAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SERTA<br>PELAKSANAAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; HAK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DAN<br>KEWAJIBAN SEORANG KUASA  </font></td>
	</tr>";
	$tab.="<tr>
	<td align=center></td>
	<td align=center width=200px>MENTERI KEUANGAN<br>REPUBLIK INDONESIA</td>
	
	</tr>";

	

	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td align=center colspan=3><font size=3><b>SURAT PENUNJUKAN</b></font></td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saya yang bertanda tangan dibawah ini:</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama </td><td>: ".$namapemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NPWP </td><td>: ".$npwppemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kuasa dari Wajib Pajak </td><td>: ".$kuasadariwajibpajak."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nomor Surat Kuasa Khusus </td><td>: ".$nomorsuratkhusus."</td></tr>";
	if($tanggalsuratkhusus == "0000-00-00"){
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Surat Kuasa Khusus </td><td>: </td></tr>";
	}else{
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal Surat Kuasa Khusus </td><td>: ".tanggalnormal($tanggalsuratkhusus)."</td></tr>";
	}
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dengan ini menunjuk :</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama </td><td>: ".$namapenerimakuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan </td><td>: ".$jabatan."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NPWP </td><td>: ".$npwppenerimakuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3><p align=justify>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;untuk menyampaikan dan/atau menerima dokumen perpajakan berupa ".$berupa7." yang diperlukan dalam <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pelaksanaan hak dan/atau pemenuhan kewajiban perpajakan berupa ".$berupa8."</p></td></tr>";
	$tab.="<tr><td colspan=3><p align=justify>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Demikian surat penunjukan ini dibuat untuk digunakan sebagaimana mestinya</p></td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td></td><td></td><td>".$kota.", ".tanggalnormal($tgl)."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td></td><td></td><td>".$namapemberikuasa."</td></tr>";
	$tab.="<tr><td></td><td></td><td>".$jabatanpemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Catatan:</b></td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dalam Surat Penunjukan oleh seorang kuasa Wajib Pajak, fotokopi Surat Kuasa Khusus harus<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dilampirkan dalam Surat Penunjukan.</td></tr>";
	$tab.="</table>";

	

	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();
	$dompdf->stream("suratpenunjukan",array("Attachment"=>0));


	break;

	case 'loaddata':
		getContainer();
	break;

	case 'caridata':
		caridata();
	break;
		
	case 'insert':
		
			$str = "insert into ".$dbname.".pajak_suratpenunjukanpajak 
			(pemberikuasa,kuasadariwajibpajak,nomorsuratkhusus,tanggalsuratkhusus,penerimakuasa,berupa7,berupa8,kota,tanggal) 
			values ('".$pemberikuasa."','".$kuasadariwajibpajak."','".$nomorsuratkhusus."','".$tanggalsuratkhusus."','".$penerimakuasa."','".$berupa7."','".$berupa8."','".$kota."','".$tanggal."')";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
		
	break;
			
	case 'update':
		if($pemberikuasa == ''){
			echo "Gagal : pemberi kuasa harus dipilih.";
			exit();
		}
		
		if($penerimakuasa == ''){
			echo "Gagal : penerima kuasa harus dipilih.";
			exit();
		}
		$str="update ".$dbname.".pajak_suratpenunjukanpajak set pemberikuasa='".$pemberikuasa."', kuasadariwajibpajak='".$kuasadariwajibpajak."', nomorsuratkhusus='".$nomorsuratkhusus."', tanggalsuratkhusus='".$tanggalsuratkhusus."',
		penerimakuasa='".$penerimakuasa."', berupa7='".$berupa7."', berupa8='".$berupa8."', kota='".$kota."', tanggal='".$tanggal."'
		 where id='".$id."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
	break;
			
	default:
		break;	
	}
	
function getContainer(){
	global $conn;
	global $dbname;
	global $owlPDO;

	
	$str="select a.id, a.berupa7, a.berupa8, a.kota, a.nomorsuratkhusus, a.tanggalsuratkhusus, b.namakaryawan as nama_pemberikuasa,b.npwp as npwp_pemberikuasa, a.kuasadariwajibpajak,c.namakaryawan as nama_penerimakuasa,c.npwp as npwp_penerimakuasa,d.namajabatan, a.tanggal
	from ".$dbname.".pajak_suratpenunjukanpajak a
	left join datakaryawan b on b.karyawanid = a.pemberikuasa
	left join datakaryawan c on c.karyawanid = a.penerimakuasa
	left join sdm_5jabatan d on d.kodejabatan = c.kodejabatan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($bar = $res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
			<td style='text-align:right;'>".$no."</td>
			<td>".$bar['nama_pemberikuasa']."</td>
			<td>".$bar['npwp_pemberikuasa']."</td>
			<td>".$bar['kuasadariwajibpajak']."</td>
			<td>".$bar['nomorsuratkhusus']."</td>";
			if($bar['tanggalsuratkhusus'] == "0000-00-00"){
			echo"<td></td>";
			}else{
			echo "<td>".tanggalnormal($bar['tanggalsuratkhusus'])."</td>";
			}
			echo "
			<td>".$bar['nama_penerimakuasa']."</td>
			<td>".$bar['npwp_penerimakuasa']."</td>
			<td>".$bar['namajabatan']."</td>
			<td>".tanggalnormal($bar['tanggal'])."</td>
			<td style='text-align:center'>
			<img src='images/skyblue/edit.png' class='resicon' title='Edit ".$bar['id']."' 
			onclick=\"fillfield('".$bar['kuasadariwajibpajak']."','".$bar['nomorsuratkhusus']."','".$bar['tanggalsuratkhusus']."',
			'".$bar['berupa7']."','".$bar['berupa8']."','".$bar['kota']."','".$bar['tanggal']."','".$bar['id']."')\">
			</td>
			<td style='text-align:center'>
			<img src='images/pdf.jpg' class='resicon' caption='PDF' title='print ".$bar['id']."' 
			onclick=\"viewpdf('".$bar['id']."')\">
			</td>
		</tr>";
	}

		$str1 = "select count(*) as jmlhrow from ".$dbname. ".pajak_suratpenunjukanpajak";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $jlhbrs = $bar1->jmlhrow;
        }

	 $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
	echo"<tr><td colspan=12 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>";
}


function caridata(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str="select a.id, a.berupa7, a.berupa8, a.kota, a.nomorsuratkhusus, a.tanggalsuratkhusus, b.namakaryawan as nama_pemberikuasa,b.npwp as npwp_pemberikuasa, a.kuasadariwajibpajak,c.namakaryawan as nama_penerimakuasa,c.npwp as npwp_penerimakuasa,d.namajabatan, a.tanggal
	from ".$dbname.".pajak_suratpenunjukanpajak a
	left join datakaryawan b on b.karyawanid = a.pemberikuasa
	left join datakaryawan c on c.karyawanid = a.penerimakuasa
	left join sdm_5jabatan d on d.kodejabatan = c.kodejabatan
	where a.tanggal between '".tanggalsystemn($_POST['tanggal1'])."' and '".tanggalsystemn($_POST['tanggal2'])."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($bar = $res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
			<td style='text-align:right;'>".$no."</td>
			<td>".$bar['nama_pemberikuasa']."</td>
			<td>".$bar['npwp_pemberikuasa']."</td>
			<td>".$bar['kuasadariwajibpajak']."</td>
			<td>".$bar['nomorsuratkhusus']."</td>";
			if($bar['tanggalsuratkhusus'] == "0000-00-00"){
			echo"<td></td>";
			}else{
			echo "<td>".tanggalnormal($bar['tanggalsuratkhusus'])."</td>";
			}
			echo "
			<td>".$bar['nama_penerimakuasa']."</td>
			<td>".$bar['npwp_penerimakuasa']."</td>
			<td>".$bar['namajabatan']."</td>
			<td>".tanggalnormal($bar['tanggal'])."</td>
			<td style='text-align:center'>
			<img src='images/skyblue/edit.png' class='resicon' title='Edit ".$bar['id']."' 
			onclick=\"fillfield('".$bar['kuasadariwajibpajak']."','".$bar['nomorsuratkhusus']."','".$bar['tanggalsuratkhusus']."',
			'".$bar['berupa7']."','".$bar['berupa8']."','".$bar['kota']."','".$bar['tanggal']."','".$bar['id']."')\">
			</td>
			<td style='text-align:center'>
			<img src='images/pdf.jpg' class='resicon' caption='PDF' title='print ".$bar['id']."' 
			onclick=\"viewpdf('".$bar['id']."')\">
			</td>
		</tr>";
	}
	$str1 = "select count(*) as jmlhrow from ".$dbname. ".pajak_suratpenunjukanpajak
	where tanggal between '".tanggalsystemn($_POST['tanggal1'])."' and '".tanggalsystemn($_POST['tanggal2'])."' ";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            $jlhbrs = $bar1->jmlhrow;
        }

	 $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
	echo"<tr><td colspan=12 align=center>
		" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
		<button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
		<button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
		</td>";
}
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method = checkPostGet('method','');
$id = checkPostGet('id','');
$pemberikuasa = checkPostGet('pemberikuasa','');
$penerimakuasa1 = checkPostGet('penerimakuasa1', '');
$penerimakuasa2 = checkPostGet('penerimakuasa2', '');
$kota = checkPostGet('kota', '');
$tanggal = tanggalsystem(checkPostGet('tanggal',''));



	
switch($method){
	case 'viewpdf':
	$str="select a.kota, b.namakaryawan as nama_pemberikuasa, g.namajabatan, b.alamataktif, b.npwp as npwp_pemberikuasa, c.namakaryawan as nama_penerimakuasa1, d.namakaryawan as nama_penerimakuasa2, c.npwp as npwp_penerimakuasa1, d.namakaryawan as nama_penerimakuasa2, d.npwp as npwp_penerimakuasa2, a.tanggal, g.namajabatan as jabatanpemberikuasa, b.alamataktif as alamatpemberikuasa, c.alamataktif as alamatpenerimakuasa1, d.alamataktif as alamatpenerimakuasa2, c.noktp as noktp1, d.noktp as noktp2, c.npwp as npwp1, d.npwp as npwp2
	from ".$dbname.".pajak_kuasapajak a
	left join ".$dbname.".datakaryawan b on b.karyawanid = a.pemberikuasa
	left join ".$dbname.".datakaryawan c on c.karyawanid = a.penerimakuasa1
	left join ".$dbname.".datakaryawan d on d.karyawanid = a.penerimakuasa2
	left join ".$dbname.".sdm_5jabatan e on e.kodejabatan = c.kodejabatan
	left join ".$dbname.".sdm_5jabatan f on f.kodejabatan = d.kodejabatan
	left join ".$dbname.".sdm_5jabatan g on g.kodejabatan = b.kodejabatan
	where a.id ='".$id."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$namapemberikuasa=$bar['nama_pemberikuasa'];
	$npwppemberikuasa=$bar['npwp_pemberikuasa'];
	$namapenerimakuasa1=$bar['nama_penerimakuasa1'];
	$namapenerimakuasa2=$bar['nama_penerimakuasa2'];
	$npwppenerimakuasa2=$bar['npwp_penerimakuasa2'];
	$jabatanpemberikuasa=$bar['jabatanpemberikuasa'];
	$alamatpemberikuasa=$bar['alamatpemberikuasa'];
	$alamatpenerimakuasa1=$bar['alamatpenerimakuasa1'];
	$alamatpenerimakuasa2=$bar['alamatpenerimakuasa2'];
	$noktp1=$bar['noktp1'];
	$noktp2=$bar['noktp2'];
	$npwp1=$bar['npwp1'];
	$npwp2=$bar['npwp2'];
	$tgl=$bar['tanggal'];
	$kota=$bar['kota'];


	$tab='';
	$tab.="<font size=14px>";
	
	$tab.="<table width=100% border=0>";
	$tab.="<tr>
	<td align=left><img src=images/tml.jpg style='width:90px;height:90px'></td>
	<td><font size=3><b>PT. KALIMANTAN AGUNG LESTARI</b><br>(Oil Palm Plantation)</font></td>
	</tr>";

	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td align=center colspan=3><font size=3><b>SURAT KUASA</b></font></td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saya yang bertanda tangan dibawah ini:</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama </td><td colspan=2>: ".$namapemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan </td><td colspan=2>: ".$jabatanpemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat </td><td colspan=2>: ".$alamatpemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NPWP </td><td>: ".$npwppemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Selanjutnya disebut “PEMBERI KUASA” :</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dengan ini memberikan kuasa kepada :</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama </td><td colspan=2>: ".$namapenerimakuasa1."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat </td><td colspan=2>: ".$alamatpenerimakuasa1."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No KTP </td><td colspan=2>: ".$noktp1."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NPWP </td><td colspan=2>: ".$npwp1."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama </td><td colspan=2>: ".$namapenerimakuasa2."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat </td><td colspan=2>: ".$alamatpenerimakuasa2."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;No KTP </td><td colspan=2>: ".$noktp2."</td></tr>";
	$tab.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NPWP </td><td colspan=2>: ".$npwp2."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dapat bertindak secara sendiri – sendiri dan/atau bersama – sama.</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Selanjutnya disebut “PENERIMA KUASA”.</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--------------------------------------------------------KHUSUS----------------------------------------------------------</td></tr>";
	
	$tab.="<tr><td colspan=3><p align=justify>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Untuk dan atas nama PEMBERI KUASA dalam hal mengurus dan melaporkan administrasi perpajakan<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;terkait Tax Amnesty PEMBERI KUASA, sehingga tanggung jawab sebagai wajib pajak di KPP Pratama <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jakarta – Pluit tetap terlaksana dengan baik dan sesuai semestinya.</p></td></tr>";
	$tab.="<tr><td colspan=3><p align=justify>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Demikianlah surat kuasa ini dibuat pada hari dan tanggal sebagaimana tersebut dibawah, bermaterai cukup<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;untuk dipergunakan sebagaimana mustinya.</p></td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td></td><td align=center>".$kota.", ".tanggalnormal($tgl)."</td><td></td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>PEMBERI KUASA</td><td></td><td>PENERIMA KUASA</td></tr>";
	$tab.="<tr><td colspan=2>PT.AGRINDO PANCA TUNGGAL PERKASA</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>".$namapemberikuasa."</td><td align=right>".$namapenerimakuasa1."</td><td align=right>".$namapenerimakuasa2."</td></tr>";
	$tab.="<tr><td>".$jabatanpemberikuasa."</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td>&nbsp;</td></tr>";
	$tab.="<tr><td colspan=3><p align=center>STH-Building Jl. Muara Karang Raya No. 60  Blok Z3 Selatan  Kav. 34  RT.015/018<br>Kel. Pluit  Kec. Penjaringan, Jakarta – Utara 14450, Indonesia<br>Tel. (021) 2266 7541/7340, Fax. (021) 2266 7207<br>Email: sth@sthgroup.com, website : www.sthgroup.com</p></td></tr>";
	
	$tab.="</table>";
	$tab.="</font>";

	

	$dompdf = new Dompdf();
	$dompdf->loadHtml($tab);
	$dompdf->setPaper('Legal', 'portrait');
	$dompdf->render();
	$dompdf->stream("suratkuasa",array("Attachment"=>0));


	break;

	case 'loaddata':
		getContainer();
	break;

	case 'caridata':
		caridata();
	break;
		
	case 'insert':
		
			$str = "insert into ".$dbname.".pajak_kuasapajak 
			(pemberikuasa,penerimakuasa1,penerimakuasa2,kota,tanggal) 
			values ('".$pemberikuasa."','".$penerimakuasa1."','".$penerimakuasa2."','".$kota."','".$tanggal."')";
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
		
		if($penerimakuasa1 == ''){
			echo "Gagal : penerima kuasa harus dipilih.";
			exit();
		}

		if($penerimakuasa2 == ''){
			echo "Gagal : penerima kuasa harus dipilih.";
			exit();
		}
		$str="update ".$dbname.".pajak_kuasapajak set pemberikuasa='".$pemberikuasa."', penerimakuasa1='".$penerimakuasa1."', penerimakuasa2='".$penerimakuasa2."', kota='".$kota."',
		tanggal='".$tanggal."' where id='".$id."'";
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

	
	$str="select a.id, b.namakaryawan as nama_pemberikuasa, g.namajabatan, b.alamataktif, b.npwp as npwp_pemberikuasa, c.namakaryawan as nama_penerimakuasa1, c.npwp as npwp_penerimakuasa1, d.namakaryawan as nama_penerimakuasa2, d.npwp as npwp_penerimakuasa2, a.tanggal 
	from ".$dbname.".pajak_kuasapajak a
	left join ".$dbname.".datakaryawan b on b.karyawanid = a.pemberikuasa
	left join ".$dbname.".datakaryawan c on c.karyawanid = a.penerimakuasa1
	left join ".$dbname.".datakaryawan d on d.karyawanid = a.penerimakuasa2
	left join ".$dbname.".sdm_5jabatan e on e.kodejabatan = c.kodejabatan
	left join ".$dbname.".sdm_5jabatan f on f.kodejabatan = d.kodejabatan
	left join ".$dbname.".sdm_5jabatan g on g.kodejabatan = b.kodejabatan";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($bar = $res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
			<td style='text-align:right;'>".$no."</td>
			<td>".$bar['nama_pemberikuasa']."</td>
			<td>".$bar['npwp_pemberikuasa']."</td>
			<td>".$bar['nama_penerimakuasa1']."</td>
			<td>".$bar['npwp_penerimakuasa1']."</td>
			<td>".$bar['nama_penerimakuasa2']."</td>
			<td>".$bar['npwp_penerimakuasa2']."</td>
			<td>".tanggalnormal($bar['tanggal'])."</td>
			<td style='text-align:center'>
			<img src='images/skyblue/edit.png' class='resicon' title='Edit ".$bar['id']."' 
			onclick=\"fillfield('".$bar['kota']."','".$bar['tanggal']."','".$bar['id']."')\">
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
	
	$str="select a.id, b.namakaryawan as nama_pemberikuasa, g.namajabatan, b.alamataktif, b.npwp as npwp_pemberikuasa, c.namakaryawan as nama_penerimakuasa1, c.npwp as npwp_penerimakuasa1, d.namakaryawan as nama_penerimakuasa2, d.npwp as npwp_penerimakuasa2, a.tanggal 
	from ".$dbname.".pajak_kuasapajak a
	left join ".$dbname.".datakaryawan b on b.karyawanid = a.pemberikuasa
	left join ".$dbname.".datakaryawan c on c.karyawanid = a.penerimakuasa1
	left join ".$dbname.".datakaryawan d on d.karyawanid = a.penerimakuasa2
	left join ".$dbname.".sdm_5jabatan e on e.kodejabatan = c.kodejabatan
	left join ".$dbname.".sdm_5jabatan f on f.kodejabatan = d.kodejabatan
	left join ".$dbname.".sdm_5jabatan g on g.kodejabatan = b.kodejabatan
	where a.tanggal between '".tanggalsystemn($_POST['tanggal1'])."' and '".tanggalsystemn($_POST['tanggal2'])."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($bar = $res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
			<td style='text-align:right;'>".$no."</td>
			<td>".$bar['nama_pemberikuasa']."</td>
			<td>".$bar['npwp_pemberikuasa']."</td>
			<td>".$bar['nama_penerimakuasa1']."</td>
			<td>".$bar['npwp_penerimakuasa1']."</td>
			<td>".$bar['nama_penerimakuasa2']."</td>
			<td>".$bar['npwp_penerimakuasa2']."</td>
			<td>".tanggalnormal($bar['tanggal'])."</td>
			<td style='text-align:center'>
			<img src='images/skyblue/edit.png' class='resicon' title='Edit ".$bar['id']."' 
			onclick=\"fillfield('".$bar['kota']."','".$bar['tanggal']."','".$bar['id']."')\">
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
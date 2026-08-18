<?//@Copy nangkoelframework
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$tglskrg = date('Y-m-d');

$str = "select * from ".$dbname.".datakaryawan where  statuskaryawan in ('Kontrak','Percobaan') and tipekaryawan = '0'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	##Get Evaluasi
	$str3 = "select count(karyawanid) as jlh from ".$dbname.".sdm_evaluasiht where karyawanid='".$bar['karyawanid']."' and status = '1'";
	$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
	$res3->setFetchMode(PDO::FETCH_ASSOC);
	$bar3=$res3->fetch();
	$numrows = $bar3['jlh'];
	
	if($numrows <= 0)
	{
		if($bar['statuskaryawan']=='Kontrak')
		{
			$tglreminder = date('Y-m-d', strtotime('-14 day', strtotime($bar['tanggalpengangkatan'])));
			$selisih = (strtotime($tglskrg) - strtotime($tglreminder)) / (60*60*24);
			
			if($selisih >= 0)
			{
				##Get PT
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['lokasitugas']."'");
				$pt = $optpt[$bar['lokasitugas']];
				
				##Get Email
				$str2 = "select email from ".$dbname.".sdm_5reminderemail where pt='".$pt."' and departemen = '".$bar['bagian']."'";
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				while($bar2=$res2->fetch())
				{
					$arrEmail[$bar['bagian']] = $bar2['email'];
				}
				
				$arrkontrak[$bar['karyawanid']]['karyawanid'] = $bar['karyawanid'];
				$arrkontrak[$bar['karyawanid']]['nik'] = $bar['nik'];
				$arrkontrak[$bar['karyawanid']]['namakaryawan'] = $bar['namakaryawan'];
				$arrkontrak[$bar['karyawanid']]['tanggalmasuk'] = $bar['tanggalmasuk'];
				$arrkontrak[$bar['karyawanid']]['bagian'] = $bar['bagian'];
				$arrkontrak[$bar['karyawanid']]['tanggalpengangkatan'] = $bar['tanggalpengangkatan'];
				$arrkontrak[$bar['karyawanid']]['status'] = $bar['statuskaryawan'];
			}
		}
		
		if($bar['statuskaryawan']=='Percobaan')
		{
			$tglakhir = date('Y-m-d', strtotime('+3 month', strtotime($bar['tanggalmasuk'])));
			$tglreminder = date('Y-m-d', strtotime('-14 day', strtotime($tglakhir)));
			$selisih = (strtotime($tglskrg) - strtotime($tglreminder)) / (60*60*24);
			
			if($selisih >= 0)
			{
				##Get PT
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$bar['lokasitugas']."'");
				$pt = $optpt[$bar['lokasitugas']];
				
				##Get Email
				$str2 = "select email from ".$dbname.".sdm_5reminderemail where pt='".$pt."' and departemen = '".$bar['bagian']."'";
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_ASSOC);
				while($bar2=$res2->fetch())
				{
					$arrEmail[$bar['bagian']] = $bar2['email'];
				}
				
				$arrpercobaan[$bar['karyawanid']]['karyawanid'] = $bar['karyawanid'];
				$arrpercobaan[$bar['karyawanid']]['nik'] = $bar['nik'];
				$arrpercobaan[$bar['karyawanid']]['namakaryawan'] = $bar['namakaryawan'];
				$arrpercobaan[$bar['karyawanid']]['tanggalmasuk'] = $bar['tanggalmasuk'];
				$arrpercobaan[$bar['karyawanid']]['bagian'] = $bar['bagian'];
				$arrpercobaan[$bar['karyawanid']]['status'] = $bar['statuskaryawan'];
			}
		}
	}
}


if(isset($arrEmail))
{
	if(isset($arrkontrak)||isset($arrpercobaan))
	{
		foreach($arrEmail as $keyEmail=>$valEmail)
		{
			$optdep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$keyEmail."'");
			$stream = "<table border=1 cellpadding=0 cellspacing=0>
				<tr>
					<td style='text-align:center;font-weight:bold;padding:5px;'>NIK</td>
					<td style='text-align:center;font-weight:bold;padding:5px;'>Nama</td>
					<td style='text-align:center;font-weight:bold;padding:5px;'>Tanggal Masuk</td>
					<td style='text-align:center;font-weight:bold;padding:5px;'>Tanggal Pengangkatan</td>
					<td style='text-align:center;font-weight:bold;padding:5px;'>Departemen</td>
					<td style='text-align:center;font-weight:bold;padding:5px;'>Status Karyawan</td>
				</tr>";
			if(isset($arrpercobaan))
			foreach($arrpercobaan as $key=>$row)
			{
				if($row['bagian']==$keyEmail)
				{
					$stream.="<tr>
						<td style='padding:5px;'>".$row['nik']."</td>
						<td style='padding:5px;'>".$row['namakaryawan']."</td>
						<td style='padding:5px;text-align:center'>".tanggalnormal($row['tanggalmasuk'])."</td>
						<td style='padding:5px;text-align:center'>-</td>
						<td style='padding:5px;'>".$optdep[$row['bagian']]."</td>
					</tr>";
				}
			}
			
			if(isset($arrkontrak))
			foreach($arrkontrak as $key=>$row)
			{
				if($row['bagian']==$keyEmail)
				{
					$stream.="<tr>
						<td style='padding:5px;'>".$row['nik']."</td>
						<td style='padding:5px;'>".$row['namakaryawan']."</td>
						<td style='padding:5px;text-align:center'>".tanggalnormal($row['tanggalmasuk'])."</td>
						<td style='padding:5px;text-align:center'>".tanggalnormal($row['tanggalpengangkatan'])."</td>
						<td style='padding:5px;'>".$optdep[$row['bagian']]."</td>
						<td style='padding:5px;'>".$row['status']."</td>
					</tr>";
				}
			}
			$stream.="<table>";
			
			#### KIRIM EMAIL ####
			$to=$valEmail;
			$subject="[Notifikasi] Notifikasi Karyawan Kontrak & Percobaan Departemen ".$optdep[$keyEmail];
			$body="<html>
				<head>
				<body>
					<dd>Dear Sir/Madam,</dd><br>
					".$stream."<br>
					Regards,<br>
					Owl-Plantation System.
				 </body>
				</head>
			</html>";
		   kirimEmail($to,"",$subject,$body);
		}
	}
	else
	{
		echo "Kosong";
	}
}


?>
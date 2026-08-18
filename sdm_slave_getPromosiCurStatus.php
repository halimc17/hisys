<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$karyawanid=$_POST['karid'];
$tahun=substr($_POST['tanggal'],6,4);



// Data Karyawan
$str="select * from ".$dbname.".datakaryawan where karyawanid=".$karyawanid ." limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    echo"<?xml version='1.0' ?>
	<karyawan>
		<tipekaryawan>".($bar->tipekaryawan!=""?$bar->tipekaryawan:"*")."</tipekaryawan>
		<kodejabatan>".($bar->kodejabatan!=""?$bar->kodejabatan:"*")."</kodejabatan>
		<kodegolongan>".($bar->kodegolongan!=""?$bar->kodegolongan:"*")."</kodegolongan>
		<lokasitugas>".($bar->lokasitugas!=""?$bar->lokasitugas:"*")."</lokasitugas>
		<bagian>".($bar->bagian!=""?$bar->bagian:"*")."</bagian>
		<subbagian>".($bar->subbagian!=""?$bar->subbagian:"*")."</subbagian>";
		$str="select * from ".$dbname.".sdm_5gajipokok where karyawanid='".$karyawanid ."' and tahun='".$tahun."' order by idkomponen asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);
		if($numrows==0)
		{
			$str="select * from ".$dbname.".sdm_5gajipokokho where karyawanid='".$karyawanid ."' and tahun='".$tahun."' order by idkomponen asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
		}
		$nox='';
		$jabs='';
		while($bar=$res->fetch())
		{
			if($nox=='')
			{
				$nox=$bar->idkomponen;
			}
			else
			{
				$nox.='###'.$bar->idkomponen;
			}

			$jabs.= "<komponen_".$bar->idkomponen.">".(isset($bar->jumlah)? number_format($bar->jumlah,2): 0)."</komponen_".$bar->idkomponen.">";
		}
		echo $jabs;
		echo "<kompon>".$nox."</kompon>";

	echo "</karyawan>";	
}
?>
<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periodegaji = checkPostGet('periodegaji', '');
$idkaryawan = checkPostGet('idkaryawan', '');
$upahpremi = checkPostGet('upahpremi', '');
$komponenpayroll = checkPostGet('komponenpayroll', '');
$method = checkPostGet('method', '');

#periksa tutupbuku
$str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $periodegaji . "' and 
             kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and tutupbuku=0";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$numrows=owlBaris($res);
if ($numrows > 0)
    $aktif = true;
else
    $aktif = false;

switch ($method) {
    case 'show':
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".sdm_gaji 
	      (kodeorg,periodegaji,karyawanid,idkomponen,jumlah,pengali)
	      values('" . $_SESSION['empl']['lokasitugas'] . "','" . $periodegaji . "','" . $idkaryawan . "','" . $komponenpayroll . "','" . $upahpremi . "','1')";
        if ($aktif) {
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo " Gagal," . $e->getMessage();
                exit;
			}
        } else {
            exit("Error : Periode sudah tutup buku / Period has been close");
        }
        break;
		
    case 'delete':
        $str = "delete from " . $dbname . ".sdm_gaji
	where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' and periodegaji='" . $periodegaji . "' and karyawanid='" . $idkaryawan . "' and idkomponen='" . $komponenpayroll . "'";

        if ($aktif) {
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo " Gagal," . $e->getMessage();
                exit;
			}
        } else {
            exit("Error:Periode sudah tutup buku");
        }
        break;
    default:
        break;
}

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")
	  and tipekaryawan!=0 and lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "'
	  order by namakaryawan";
} else {
    $str1 = "select * from " . $dbname . ".datakaryawan
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar > " . $_SESSION['org']['period']['start'] . ")
	  and tipekaryawan!=0 and LEFT(lokasitugas,4)='" . substr($_SESSION['empl']['lokasitugas'], 0, 4) . "'
	  order by namakaryawan";
}

$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    $nama[$bar1->karyawanid] = $bar1->namakaryawan;
}

$strJ = "select * from " . $dbname . ".sdm_5jabatan";
$resJ=$owlPDO->query($strJ) or die(print " Gagal: ".PDOException::getMessage());
$resJ->setFetchMode(PDO::FETCH_OBJ);
while ($barJ = $resJ->fetch()) {
    $jab[$barJ->kodejabatan] = $barJ->namajabatan;
}
$strRes = "select a.*, b.kodejabatan, b.lokasitugas from " . $dbname . ".sdm_gaji a 
	left join " . $dbname . ".datakaryawan b
	on a.karyawanid = b.karyawanid
	where a.idkomponen in ('37') and  a.periodegaji ='" . $periodegaji . "' and b.lokasitugas = '" . $_SESSION['empl']['lokasitugas'] . "'
	order by a.karyawanid";
$tot = 0;
$resRes=$owlPDO->query($strRes) or die(print " Gagal: ".PDOException::getMessage());
$resRes->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $resRes->fetch()) {
    echo"<tr class=rowcontent>
                 <td>" . $nama[$bar1->karyawanid] . "</td>
                 <td>" . $jab[$bar1->kodejabatan] . "</td>
                 <td align=center>" . $bar1->periodegaji . "</td>
                 <td align=right width=100>" . number_format($bar1->jumlah, 2) . "</td>
                 <td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delPremi('" . $bar1->periodegaji . "','" . $bar1->karyawanid . "','" . $bar1->jumlah . "','" . $bar1->idkomponen . "');\"></td></tr>";
    $tot+=$bar1->jumlah;
}
echo"	<tr class=rowheader>
        <td colspan=3>Total</td>
        <td align=right>" . number_format($tot, 2) . "</td>    
	<td></td></tr>"
?>
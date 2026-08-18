<?
require_once('master_validation.php');
require_once('config/connection.php');
//param+='&satuan='+satuan+'&idKlmpk='+idKlmpk;
$kodejabatan = $_POST['kodejabatan'];
$namajabatan = $_POST['namajabatan'];
$satuan = $_POST['satuan'];
$statusbangunan = $_POST['statusbangunan'];
$idKlmpk = $_POST['idKlmpk'];
$method = $_POST['method'];

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".sdm_5jenis_prasarana set nama='" . $namajabatan . "',satuan='" . $satuan . "', status_bangunan='" . $statusbangunan . "'
	       where jenis='" . $kodejabatan . "' and kelompok='" . $idKlmpk . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".sdm_5jenis_prasarana (jenis,nama,satuan,kelompok,status_bangunan)
	      values('" . $kodejabatan . "','" . $namajabatan . "','" . $satuan . "','" . $idKlmpk . "','" . $statusbangunan . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".sdm_5jenis_prasarana
	where jenis='" . $kodejabatan . "' and kelompok='" . $idKlmpk . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    default:
        break;
}
$sKlmpk = "select distinct * from " . $dbname . ".sdm_5kl_prasarana order by kode asc";
$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $orgNmKlmpk[$rKlmpk['kode']] = $rKlmpk['nama'];
}
$str1 = "select * from " . $dbname . ".sdm_5jenis_prasarana order by nama";
echo "<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader><td>" . $_SESSION['lang']['namakelompok'] . "</td><td>" . $_SESSION['lang']['jenis'] . "</td><td>" . $_SESSION['lang']['namajenisvhc'] . "</td><td>" . $_SESSION['lang']['satuan'] . "</td><td>Status Bangunan</td><td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody>";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    echo "<tr class=rowcontent>
                    <td align=center>" . $orgNmKlmpk[$bar1->kelompok] . "</td>
                    <td>" . $bar1->jenis . "</td>
                    <td>" . $bar1->nama . "</td>
                    <td>" . $bar1->satuan . "</td>
                    <td>" . $bar1->status_bangunan . "</td>
                        
                    <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->kelompok . "','" . $bar1->jenis . "','" . $bar1->satuan . "','" . $bar1->nama . "','" . $bar1->status_bangunan . "');\"></td></tr>";
}
echo "	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";

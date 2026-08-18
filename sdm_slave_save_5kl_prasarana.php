<?
require_once('master_validation.php');
require_once('config/connection.php');

$kodejabatan = $_POST['kodejabatan'];
$namajabatan = $_POST['namajabatan'];
$method = $_POST['method'];

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".sdm_5kl_prasarana set nama='" . $namajabatan . "'
	       where kode='" . $kodejabatan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".sdm_5kl_prasarana (kode,nama)
	      values('" . $kodejabatan . "','" . $namajabatan . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".sdm_5kl_prasarana
	where kode='" . $kodejabatan . "'";
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
$str1 = "select * from " . $dbname . ".sdm_5kl_prasarana order by kode";
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
     <thead>
	 <tr class=rowheader><td style='width:150px;'>" . $_SESSION['lang']['kodekelompok'] . "</td><td>" . $_SESSION['lang']['namakelompok'] . "</td><td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kode . "</td><td>" . $bar1->nama . "</td><td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kode . "','" . $bar1->nama . "');\"></td></tr>";
}
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
?>

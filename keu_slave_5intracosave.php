<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/zLib.php');

$kodeorg =                isset($_POST['kodeorg']) ? $_POST['kodeorg'] : '';
$jenis =                             isset($_POST['jenis']) ? $_POST['jenis'] : '';
$akunpiutang =        isset($_POST['akunpiutang']) ? $_POST['akunpiutang'] : '';
$akunhutang =        isset($_POST['akunhutang']) ? $_POST['akunhutang'] : '';
$kodeorgbef =        isset($_POST['kodeorgbef']) ? $_POST['kodeorgbef'] : '';
$jenisbef =                isset($_POST['jenisbef']) ? $_POST['jenisbef'] : '';
$noakunbef =                 isset($_POST['noakunbef']) ? $_POST['noakunbef'] : '';
$method =                isset($_POST['method']) ? $_POST['method'] : '';


switch ($method) {
        case 'update':
                $str = "update " . $dbname . ".keu_5caco set kodeorg='" . $kodeorg . "', jenis='" . $jenis . "', akunpiutang='" . $akunpiutang . "',akunhutang='" . $akunhutang . "' 
               where kodeorg='" . $kodeorgbef . "' and jenis='" . $jenisbef . "' and akunpiutang='" . $noakunbef . "'";
                try {
                        $owlPDO->exec($str);
                } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                }
                break;
        case 'insert':
                $str = "insert into " . $dbname . ".keu_5caco (kodeorg,jenis,akunpiutang,akunhutang)
              values('" . $kodeorg . "','" . $jenis . "','" . $akunpiutang . "','" . $akunhutang . "')";
                try {
                        $owlPDO->exec($str);
                } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n";
                        die();
                }
                break;
        case 'delete':
                $str = "delete from " . $dbname . ".keu_5caco 
        where kodeorg='" . $kodeorg . "' and jenis='" . $jenis . "' and akunpiutang='" . $akunpiutang . "'";
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
$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where (noakun like '221%' or noakun like '122%' or noakun like '121%' or noakun like '218%') and char_length(noakun)=7 order by noakun";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
        $namaakun[$bar->noakun] = $bar->namaakun;
}

$str1 = "select * from " . $dbname . ".keu_5caco order by kodeorg";
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
$namaakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
echo "<table class=sortable cellspacing=1 cellpadding=5 border=0>
     <thead>
         <tr class=rowheader><td style='width:75px;'>" . $_SESSION['lang']['kodeorg'] . "</td><td style='width:40px;'>" . $_SESSION['lang']['jenis'] . "</td><td>" . $_SESSION['lang']['piutang'] . "</td><td>" . $_SESSION['lang']['hutang'] . "</td><td  style='width:30px;'>Action</td></tr>
         </thead>
         <tbody>";
while ($bar1 = $res1->fetch()) {
        echo "<tr class=rowcontent><td align=center>" . $bar1->kodeorg . "</td>";
        if ($bar1->jenis == 'inter') echo "<td>Inter</td>";
        else echo "<td align=right>Intra</td>";
        echo "<td>" . $bar1->akunpiutang . " - " . $namaakun[$bar1->akunpiutang] . "</td>
                             <td>" . $bar1->akunhutang . " - " . $namaakun[$bar1->akunhutang] . "</td>    

                <td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kodeorg . "','" . $bar1->jenis . "','" . $bar1->akunpiutang . "','" . $bar1->akunhutang . "');\"></td></tr>";
}
echo "	 
         </tbody>
         <tfoot>
         </tfoot>
         </table>";

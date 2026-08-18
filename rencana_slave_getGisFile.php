<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$param = $_POST;

$where = "";

if ($param['kodeorg'] != '') {
    $where.=" and unit='" . $param['kodeorg'] . "' ";
}

if ($param['periode'] != '') {
    $where.=" and tanggal like '" . $param['periode'] . "%' ";
}
if ($param['kode'] != '') {
    $where.=" and kode='" . $param['kode'] . "' ";
}

$str1 = "select a.*,b.namakaryawan from " . $dbname . ".rencana_gis_file a
            left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid 
            where 1=1 " . $where . "  order by a.lastupdate  desc";


$no = 0;
$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res1->fetch()) {
    $no+=1;
    echo"<tr class=rowcontent>
               <td>" . $no . "</td>
                <td>" . $bar1->unit . "</td>
                    <td>" . $bar1->kode . "</td>
                    <td>" . tanggalnormal($bar1->tanggal) . "</td>
                    <td>" . $bar1->namakaryawan . "</td>
                    <td>" . $bar1->lastupdate . "</td>
                    <td>" . $bar1->keterangan . "</td>
                    <td>" . $bar1->namafile . "</td>
                    <td align=right>" . $bar1->ukuran . "</td>
                    <td>" . $bar1->namakaryawan . "</td>
                    <td>";
    if ($bar1->karyawanid == $_SESSION['standard']['userid']) {
        echo"<img class=zImgBtn src=images/skyblue/delete.png   title='Edit' onclick=\"delFile('" . $bar1->unit . "','" . $bar1->kode . "','" . $bar1->namafile . "');\"> &nbsp  &nbsp  &nbsp";
    }
    echo "<img class=zImgBtn src=images/skyblue/save.png   title='Save' onclick=\"download('" . $bar1->namafile . "');\"></td></tr>";
}
?>
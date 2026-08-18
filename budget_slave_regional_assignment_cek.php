<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$organisasi=$_POST['organisasi'];
$regional=$_POST['regional'];
//$arrEnum=getEnum($dbname,'bgt_tipe','tipe,nama');

    $str="select * from ".$dbname.".bgt_regional_assignment  where kodeunit='".$organisasi."'  limit 0,1";
    $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res1->fetch())
    {
        $sudahtutup="1";
        $pesan=$bar->kodeunit." - ".$bar->regional;
    }
    if($sudahtutup=="1"){
        echo "data sudah ada: ".$pesan; exit;
    }
?>

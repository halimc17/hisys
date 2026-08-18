<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');


$periode = checkPostGet('periode','');
$karyawanid = checkPostGet('karyawanid','');
$komponen = checkPostGet('komponen','');
$tipekaryawan = checkPostGet('tipekaryawan','');

#periksa periode pembukuan
$str="select periode from ".$dbname.".setup_periodeakuntansi 
      where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".$periode."'
      and tutupbuku=1";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$row=owlBaris($res);
if($row>0)
{
    echo "Error:Periode Akuntansi sudah tutup buku";
}
else
{
    $addwhere='';
    if($komponen!='all')
        $addwhere.=" and idkomponen=".$komponen;
    if ($karyawanid!='all')
        $addwhere.=" and karyawanid='".$karyawanid."'";
    else
    {
        if($tipekaryawan!='all')
        {
           $addwhere.=" and karyawanid in(select karyawanid from ".$dbname.".datakaryawan 
               where sistemgaji='".$tipekaryawan."')"; 
        }
    }
    
    $str="delete from ".$dbname.".sdm_gaji where  periodegaji='".$periode."' and
        kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$addwhere;
    try{
        $owlPDO->exec($str); 
        echo"Done";
    }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
    
}    
?>
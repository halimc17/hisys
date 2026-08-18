<?//@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

//+++++++++++++++++++++++++++++++++++++++++++++
//list employee
$kodeorganisasi=$_POST['kodeorganisasi'];
if($kodeorganisasi==''){
   $kodeorganisasi=$_SESSION['empl']['lokasitugas']; 
}
//HRA

if(substr($_SESSION['empl']['lokasitugas'],2,2)=='LO' || substr($_SESSION['empl']['lokasitugas'],2,2)=='RO'){
	$where='';
}else{
	$where=" and karyawanid='".$_SESSION['standard']['userid']."' ";
}
#getTipeKaryaw
$sGetTp="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='KRTPKARY'";
$res2=$owlPDO->query($sGetTp) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_ASSOC);
$bar2=$res2->fetch();
$str="select karyawanid,nik,lokasitugas,namakaryawan,subbagian,tanggalkeluar,b.tipe from ".$dbname.".datakaryawan a
          left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id
          where lokasitugas='".$kodeorganisasi."' ".$where." and a.tipekaryawan in (".$bar2['nilai'].")
		   and statuskaryawan != 'Keluar' and (tanggalkeluar = '0000-00-00' or tanggalkeluar > ".$_SESSION['org']['period']['start'].") order by namakaryawan";
	
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opt="<option value=''></option>";
while($bar=$res->fetch()){
    if($bar->tanggalkeluar!='0000-00-00' and $bar->tanggalkeluar!='')
        $add=" Keluar: ".$bar->tanggalkeluar;
    else
        $add='';
    $opt.="<option value='".$bar->karyawanid."'>".$bar->nik." - ".$bar->namakaryawan." - ".$bar->lokasitugas."-".$bar->tipe."</option>";
}
echo $opt;
?>
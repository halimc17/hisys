<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');

$pt=$_POST['pt'];
$tipe=$_POST['tipe'];

$hasil='';
if($tipe=='bb'){
    //ambil namapt
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe = 'TRAKSI'
                        or tipe='HOLDING')  and induk!='' and induk = '".$pt."'
                        ";
                $hasil.="<option value=''>".$_SESSION['lang']['all']."</option>";    
}
else
if($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where induk='".$pt."' and length(kodeorganisasi)=4 and kodeorganisasi not like '%HO'";
//                $hasil.="<option value=''>".$_SESSION['lang']['all']."</option>";    
}
else
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
                        where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'  and induk!=''";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                $hasil.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";

        }    
}else{
//ambil namapt
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
      where induk='".$pt."'";
$hasil='<option value="">'.$_SESSION['lang']['all'].'</option>';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $hasil.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
}
    if($pt=='')$hasil='<option value="">'.$_SESSION['lang']['pilihdata'].'</option>';
}
echo $hasil;
?>
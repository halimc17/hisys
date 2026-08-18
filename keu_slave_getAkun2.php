<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');

        $pam=$_POST['pam'];
        $hasil='';

//ambil akun2
if($pam==1){
	
$str="select noakundebet from ".$dbname.".keu_5parameterjurnal
where kodeaplikasi = 'CLM'
	";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$clm=$bar->noakundebet;
}
	
	
        $akun=$_POST['akun'];
        $str="select noakun,namaakun from ".$dbname.".keu_5akun
                        where level = '5' and noakun != '".$clm."' and noakun>= '".$akun."'
                        order by noakun
                        ";
		/*
		$str="select noakun,namaakun from ".$dbname.".keu_5akun
		where level = '5' and noakun not like '3%' and noakun>= '".$akun."'
		order by noakun
		";
		*/				
        $res=$owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_OBJ);
        $hasil="";
        while($bar=$res->fetch())
        {
                $hasil.="<option value='".$bar->noakun."'>".$bar->noakun." - ".$bar->namaakun."</option>";
        }
echo $hasil;
}

//cek tanggal mulai harus 1
if($pam==2){
        $tanggal1=$_POST['tanggal1'];
        $tanggal2=$_POST['tanggal2'];
    $qwe=explode("-",$tanggal1);
    $tanggal1=$qwe[0];
    if($tanggal1!='01')
    echo "WARNING: Tanggal Mulai harus 01."; 
}

//cek tanggal sampai harus lebih
if($pam==3){
        $tanggal1=$_POST['tanggal1'];
        $tanggal2=$_POST['tanggal2'];
    if($tanggal1==''){
        echo "WARNING: Silakan memilih Tanggal Mulai."; exit;
    }
    $qwe=explode("-",$tanggal1);
    $tanggal1=$qwe[2]."-".$qwe[1]."-".$qwe[0];
    $qwe=explode("-",$tanggal2);
    $tanggal2=$qwe[2]."-".$qwe[1]."-".$qwe[0];
    if($tanggal2<$tanggal1)
        echo "WARNING: Sampai Tanggal tidak bisa lebih kecil dari Tanggal Mulai."; 
}
?>
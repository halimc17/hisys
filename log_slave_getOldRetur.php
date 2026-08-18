<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$nomorlama=$_POST['nomorlama'];
$kodebarang=$_POST['kodebarang'];
$kodegudang=$_POST['kodegudang'];
$kodeblok=$_POST['kodeblok'];
$mesin=$_POST['mesin'];

$str="select a.tipetransaksi,a.kodept,a.untukpt,a.untukunit,b.jumlah,b.satuan,b.hargasatuan 
        from ".$dbname.".log_transaksidt b left join
        ".$dbname.".log_transaksiht a on. a.notransaksi=b.notransaksi
        where a.tipetransaksi=5 and b.kodebarang='".$kodebarang."'
        and a.notransaksi='".$nomorlama."'
        and a.notransaksi like '%".$kodegudang."%'
        and b.kodeblok='".$kodeblok."' 
        and b.kodemesin='".$mesin."' 
        limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);

if($numrows>0)
{
while($bar=$res->fetch())
{
    $namabarang='';
    //ger namabarang
    $strf="select namabarang from ".$dbname.".log_5masterbarang
            where kodebarang='".$kodebarang."'";
	$resf=$owlPDO->query($strf) or die(print " Gagal: ".PDOException::getMessage());
	$resf->setFetchMode(PDO::FETCH_OBJ);		
    while($barf=$resf->fetch())
    {
            $namabarang=$barf->namabarang;
    }
        //ambil jumlah barang yang di retur ontuk PO yang sama dan barang yang sama
                $stam="select sum(jumlah) as jum from ".$dbname.".log_transaksi_vw where notransaksireferensi='".$nomorlama."'
                            and kodebarang='".$kodebarang."' and kodegudang='".$kodegudang."'
                            and tipetransaksi=2 and kodeblok='".$kodeblok."' and kodemesin='".$mesin."'";

                $jam=0;
				$rem=$owlPDO->query($stam) or die(print " Gagal: ".PDOException::getMessage());
				$rem->setFetchMode(PDO::FETCH_OBJ);
                while($bam=$rem->fetch())
                {
                    $jam=$bam->jum;
                }
                $sis=$bar->jumlah-$jam;        
    echo"<?xml version='1.0' ?>
                <oldoc>
                    <jumlah>".$sis."</jumlah>
                    <satuan>".($bar->satuan!=""?$bar->satuan:"*")."</satuan>
                    <namabarang>".($namabarang!=""?$namabarang:"*")."</namabarang>
                    <hargasatuan>".($bar->hargasatuan!=""?$bar->hargasatuan:"*")."</hargasatuan>
                <kodept>".($bar->kodept!=""?$bar->kodept:"*")."</kodept>
                    <untukpt>".($bar->untukpt!=""?$bar->untukpt:"*")."</untukpt>
                    <untukunit>".($bar->untukunit!=""?$bar->untukunit:"*")."</untukunit>
                </oldoc>";	   		 	
}
}
else
{
	echo " Gagal,Previous transaction not found";
}
?>
<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$jabatan = checkPostGet('jabatan','');
$lokasitugas = checkPostGet('lokasitugas','');
$status='LOKASI';
/*
if (preg_match("/HO/i",$lokasitugas) or preg_match("/RO/i",$lokasitugas)) {
    $status='KOTA';
}
*/
$x=substr($lokasitugas,2,2);
if($x=='RO' or $x=='HO')
  $status='KOTA';  

$str="SELECT * FROM ".$dbname.".sdm_5stdtunjangan  where jabatan=".$jabatan." and penempatan='".$status."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows>0)
{
    while($bar=$res->fetch())
    {
        echo"<?xml version='1.0' ?>
	     <tunjangan>
			 <tjjabatan>".($bar->tjjabatan!=""?$bar->tjjabatan:"*")."</tjjabatan>
			 <tjkota>".($bar->tjkota!=""?$bar->tjkota:"*")."</tjkota>
			 <tjtransport>".($bar->tjtransport!=""?$bar->tjtransport:"*")."</tjtransport>
			 <tjmakan>".($bar->tjmakan!=""?$bar->tjmakan:"*")."</tjmakan>
			 <tjsdaerah>".($bar->tjsdaerah!=""?$bar->tjsdaerah:"*")."</tjsdaerah>
			 <tjmahal>".($bar->tjmahal!=""?$bar->tjmahal:"*")."</tjmahal>
			 <tjpembantu>".($bar->tjpembantu!=""?$bar->tjpembantu:"*")."</tjpembantu>
			 <tjtelekomunikasi>".($bar->tjtelekomunikasi!=""?$bar->tjtelekomunikasi:"*")."</tjtelekomunikasi>
			 <tjcop>".($bar->tjcop!=""?$bar->tjcop:"*")."</tjcop>
			 <tjmop>".($bar->tjmop!=""?$bar->tjmop:"*")."</tjmop>
			 <tjasuransi>".($bar->tjasuransi!=""?$bar->tjasuransi:"*")."</tjasuransi>
	   </tunjangan>";	       
    }
}
else
{
         echo"<?xml version='1.0' ?>
	     <tunjangan>
			 <tjjabatan>0</tjjabatan>
			 <tjkota>0</tjkota>
			 <tjtransport>0</tjtransport>
			 <tjmakan>0</tjmakan>
			 <tjsdaerah>0</tjsdaerah>
			 <tjmahal>0</tjmahal>
			 <tjpembantu>0</tjpembantu>
			 <tjtelekomunikasi>0</tjtelekomunikasi>
			 <tjcop>0</tjcop>
			 <tjmop>0</tjmop>
			 <tjasuransi>0</tjasuransi>
	   </tunjangan>";   
}
?>
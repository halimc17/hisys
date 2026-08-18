<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$bibit=checkPostGet('bibit','');
$oldjb=checkPostGet('oldjb','');
$tahuntanam=checkPostGet('tahuntanam','');
$oldum=checkPostGet('oldum','');
$tanah=checkPostGet('tanah','');
$oldkt=checkPostGet('oldkt','');
$produksi=checkPostGet('produksi','');
$unit=checkPostGet('unit','');

$x=fetchData("SELECT kode,nama FROM $dbname.setup_kelaslahan");
foreach($x as $val)
{
    $namatanah[$val['kode']]=$val['nama'];
}
switch($method)
{
    case'insert':
    if($bibit=='')
    {
        echo"warning: Silakan pilih jenis bibit"; exit();
    }
    if($tanah=='')
    {
        echo"warning: Silakan pilih klasifikasi tanah"; exit();
    }
    if($tahuntanam=='')
    {
        echo"warning: Silakan isi tahun tanam tanaman"; exit();
    }
    if($produksi=='')
    {
        echo"warning: Silakan isi Kg Produksi/Ha"; exit();
    }

    $sIns="insert into ".$dbname.".kebun_5stproduksi (`unit`,`jenisbibit`,`klasifikasitanah`,`tahuntanam`,`kgproduksi`) values ('".$unit."','".$bibit."','".$tanah."','".$tahuntanam."','".$produksi."')";
    try{$owlPDO->exec($sIns); }
	catch (PDOException $e) {
	print " Gagal  !: " . $e->getMessage() . "\n"; 
	die(); 
}
    break;
    case'loadData':
    $no=0;	 
    $str="select * from ".$dbname.".kebun_5stproduksi order by jenisbibit, tahuntanam, klasifikasitanah";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch())
	{
    
    $no+=1;	
    echo"<tr class=rowcontent>
    <td align=center>".$no."</td>
    <td>".$bar['unit']."</td>
    <td>".$bar['jenisbibit']."</td>
    <td>".$bar['klasifikasitanah']." - ".@$namatanah[$bar['klasifikasitanah']]."</td>
    <td align=center>".$bar['tahuntanam']."</td>
    <td align=right>".number_format($bar['kgproduksi'],2)."</td>
    <td align=center>
        <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['unit']."','".$bar['jenisbibit']."','".$bar['klasifikasitanah']."','".$bar['tahuntanam']."','".$bar['kgproduksi']."');\"> 
    </td>
    </tr>";	
    }  
    if($no==0){
        echo"<tr class=rowcontent>
        <td colspan=6>Data Empty.</td>
        </tr>";	
    }
    break;
    case'update':
    if($bibit=='')
    {
        echo"warning: Silakan pilih jenis bibit"; exit();
    }
    if($tanah=='')
    {
        echo"warning: Silakan pilih klasifikasi tanah"; exit();
    }
    if($tahuntanam=='')
    {
        echo"warning: Silakan isi tahun tanam tanaman"; exit();
    }
    if($produksi=='')
    {
        echo"warning: Silakan isi Kg Produksi/Ha"; exit();
    }
    $sUpd="update ".$dbname.".kebun_5stproduksi set `jenisbibit`='".$bibit."',`klasifikasitanah`='".$tanah."',`tahuntanam`='".$tahuntanam."',`kgproduksi`='".$produksi."' where jenisbibit='".$oldjb."' and klasifikasitanah='".$oldkt."' and tahuntanam='".$oldum."'";
    try{$owlPDO->exec($sUpd); }
	catch (PDOException $e) {
	print " Gagal  !: " . $e->getMessage() . "\n"; 
	die(); 
	}
    break;
    // case'delData':
    // $sDel="delete from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
	// 	try{$owlPDO->exec($sDel); }
	// 	catch (PDOException $e) {
	// 	print " Gagal  !: " . $e->getMessage() . "\n"; 
	// 	die(); 
	// 	}
    // break;
    default:
    break;
}
?>
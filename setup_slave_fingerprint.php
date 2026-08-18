<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$pin=checkPostGet('pin','');
$karyawanid=checkPostGet('karyawanid','');
$method=checkPostGet('method','');

switch($method)
{
case 'update':
    $sCek="select distinct a.karyawanid, a.pin, b.namakaryawan from ".$dbname.".att_adaptor a
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.pin = '".$pin."'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_OBJ);
	$rCek=owlBaris($qCek);
    $karz=''; 
    $pinz='';
        while($bar1=$qCek->fetch())
        {
            $karz=$bar1->karyawanid;
            $namz=$bar1->namakaryawan;
            $pinz=$bar1->pin;
        }    
    
    if($rCek!=0)
    {
        exit("Error. Data Sudah Ada: ".$namz." (".$pinz.")");
    }
    
    
    $str="update ".$dbname.".att_adaptor set pin='".$pin."'
        where karyawanid='".$karyawanid."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
break;
case 'insert':
    $sCek="select distinct a.karyawanid, a.pin, b.namakaryawan from ".$dbname.".att_adaptor a
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where ((a.karyawanid like '%".$karyawanid."%') or (a.pin = '".$pin."'))";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_OBJ);
	$rCek=owlBaris($qCek);
    $karz='';
    $pinz='';
        while($bar1=$qCek->fetch())
        {
            $karz=$bar1->karyawanid;
            $namz=$bar1->namakaryawan;
            $pinz=$bar1->pin;
        }    
    
    if($rCek!=0)
    {
        exit("Error. Data Sudah Ada: ".$namz." (".$pinz.")");
    }
    $str="insert into ".$dbname.".att_adaptor
        (karyawanid,pin)
        values('".$karyawanid."','".$pin."')";
    try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
break;
case 'delete':
    $str="delete from ".$dbname.".att_adaptor
    where karyawanid='".$karyawanid."' and pin='".$pin."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
    break;
    
	case'loadData':
    $str1="select a.karyawanid, a.pin, b.namakaryawan
        from ".$dbname.".att_adaptor a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        order by b.namakaryawan";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch())
	{
		echo"<tr class=rowcontent>
		<td>".$bar1->namakaryawan."</td>
		<td>".$bar1->pin."</td>
		<td align=center nowrap>
		<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->karyawanid."','".$bar1->pin."');\">
		<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"dedel('".$bar1->karyawanid."','".$bar1->pin."');\">
		</td></tr>";
	}
break;
default:
break;					
}


?>

<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kodept=checkPostGet('kodept','');
$penghasilan=checkPostGet('penghasilan','');
$persentase=checkPostGet('persentase','');
$old_kodept=checkPostGet('old_kodept','');
$old_penghasilan=checkPostGet('old_penghasilan','');
$old_persentase=checkPostGet('old_persentase','');
$carikodept=checkPostGet('carikodept','');

switch($method)
{
    case'insert':
    if($kodept==''){
        exit("error: ".$_SESSION['lang']['kodept']." tidak boleh kosong");
    }
    if($penghasilan==0){
        exit("error: ".$_SESSION['lang']['penghasilan']." tidak boleh kosong");
    }
    $scek="select * from ".$dbname.".sdm_5pajakpesangon 
           where kodept='".$kodept."' and penghasilan='".$penghasilan."' and persentase = '".$persentase."' ";
	$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$rcek=owlBaris($qcek);
    if($rcek!=0){
       exit("error: Data sudah pernah diinput.");
    }
    
    $sIns="insert into ".$dbname.".sdm_5pajakpesangon (kodept,penghasilan,persentase) 
           values ('".$kodept."','".$penghasilan."','".$persentase."')";
	try{
		$owlPDO->exec($sIns); 
	}
	catch (PDOException $e){
		echo"Gagal".$e->getMessage();
		die();
	}
    break;
    
    case'loadData':
    if($carikodept==''){
        $str="select * from ".$dbname.".sdm_5pajakpesangon order by penghasilan, persentase asc";       
    }
    else{
        $str="select * from ".$dbname.".sdm_5pajakpesangon where kodept like '".$carikodept."%' 
              order by penghasilan, persentase asc";
    }
    
    $no=0;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$oow=owlBaris($res);
    if($oow==0){
        echo"<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['dataempty']."</td></tr>";
    }
    else{
        while($bar=$res->fetch())
        {
            $no+=1;	
            echo"<tr class=rowcontent>
            <td align=center>".$no."</td>
            <td>".$bar['kodept']."</td>
            <td align=right>".number_format($bar['penghasilan'],0)."</td>
            <td align=right>".number_format($bar['persentase'],2)."</td>
            <td align=center>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodept']."','".$bar['penghasilan']."','".$bar['persentase']."');\">
                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('".$bar['kodept']."','".$bar['penghasilan']."','".$bar['persentase']."');\">             
            </td>
            </tr>";	
        }  
    }
    break;

    case'update':
    if($kodept==''){
        exit("error: ".$_SESSION['lang']['kodept']." tidak boleh kosong");
    }
    if($penghasilan==0){
        exit("error: ".$_SESSION['lang']['penghasilan']." tidak boleh kosong");
    }
    
    $scek="select * from ".$dbname.".sdm_5pajakpesangon where kodept='".$kodept."' and penghasilan='".$penghasilan."'
           and persentase='".$persentase."' ";
	$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
	$rcek=owlBaris($qcek);
    if($rcek!=0){
       exit("error: Data sudah ada.");
    }
    else{
        $sUpd="update ".$dbname.".sdm_5pajakpesangon set kodept='".$kodept."',penghasilan='".$penghasilan."',
               persentase='".$persentase."'
               where kodept='".$old_kodept."' and penghasilan='".$old_penghasilan."' 
               and persentase = '".$old_persentase."'";
		try{
			$owlPDO->exec($sUpd); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
    }
    break;

    case 'deletedata':
    $sDel="delete from ".$dbname.".sdm_5pajakpesangon 
           where kodept='".$kodept."' and penghasilan='".$penghasilan."' and persentase='".$persentase."'";
	try{
		$owlPDO->exec($sDel); 
	}
	catch (PDOException $e){
		echo "DB Error : ".$e->getMessage();  
	}
    break;

    default:
    break;
}
?>
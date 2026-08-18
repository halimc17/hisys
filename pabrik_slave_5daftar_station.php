<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$param=$_POST;

switch($method)
{
case'insert':
		$sIns="insert into ".$dbname.".pabrik_5mr_list_station (`kode_station`,`updateby`) 
        values ('".$param['stationId']."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
		}
	
	break;

    case'loadData':
    $str="select * from ".$dbname.".pabrik_5mr_list_station where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no+=1;
        echo"<tr class=rowcontent>
        <td>".$no."</td>
		<td>".$bar['kode_station']."</td>
        
        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletehk('".$bar['kode_station']."');\"></td>
        </tr>";	
    }     
    break;

    case'delete':
    	$str="select * from ".$dbname.".pabrik_logmesin where station='".$param['stationId']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
        	$sIns="delete from ".$dbname.".pabrik_5mr_list_station where kode_station='".$param['stationId']."'";
	        try{
				$owlPDO->exec($sIns); 
			}
			catch (PDOException $e){
				echo"Gagal".$e->getMessage();
				die();
			}
        }else{
        	exit('Warning : station sudah ada transaksi');
        }
    break;
        default:
        break;
}
?>
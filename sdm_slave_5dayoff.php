<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$periode=checkPostGet('periode','');
$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$karyawan=checkPostGet('karyawan','');
$jumlah=checkPostGet('jumlah','');


switch($method)
{
case'insert':
    
    if($periode=='')
    {
        echo "warning : Silakan memilih periode.";
        exit();
    }

     if($jumlah=='')
    {
        echo "warning : Silakan memilih Jumlah.";
        exit();
    }

    $sIns="insert into ".$dbname.".sdm_5cutidayoff (`karyawanid`,`pt`,`unit`,`periode`,`jumlah`,`createdby`) 
        values ('".$karyawan."','".$pt."','".$unit."','".$periode."','".$jumlah."','".$_SESSION['standard']['userid']."')";
	try{
		$owlPDO->exec($sIns); 
	}
	catch (PDOException $e){
		echo"Gagal".$e->getMessage();
		die();
	}
    break;

    case'loadData':
    $nmkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
    $nmpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

    $str="select * from ".$dbname.".sdm_5cutidayoff  order by periode desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no+=1;	
        echo"<tr class=rowcontent>
        <td align=center>".$no."</td>
        <td align=left>".$nmpt[$bar['pt']]."</td>
        <td align=center>".$nmpt[$bar['unit']]."</td>
        <td align=left>".$nmkar[$bar['karyawanid']]."</td>
        <td align=center>".$bar['periode']."</td>
        <td align=center>".$bar['jumlah']."</td>
        <td align=center>".$nmkar[$bar['createdby']]."</td>
        <td align=center>".$bar['createtime']."</td>
        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletehk('".$bar['periode']."','".$bar['karyawanid']."');\"></td>
        </tr>";	
    }     
    break;
    case'delete':

        $sIns="delete from ".$dbname.".sdm_5cutidayoff where periode = '".$periode."' and karyawanid='".$karyawan."'";
        //exit('error'.$periode);
        try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
    break;

    case 'getunit':

        if(strlen($pt)<4){
            $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='kebun'";
            $res=fetchData($str);
            foreach ($res as $val) {
                if($val['kodeorganisasi']==$unit){
                    $optOrg.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";                  
                }else{
                    $optOrg.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
                }
            }
        }
        echo $optOrg;
    break;

    case 'getkar':

       
            $optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and tipekaryawan='0'";
            $res=fetchData($str);
            foreach ($res as $val) {
              
                    $optkar.="<option value='".$val['karyawanid']."' selected>".$val['karyawanid']."-".$val['namakaryawan']."</option>";                  
               
            }
        
        echo $optkar;
    break;
        default:
        break;
}


?>
<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
//$arr="##kode##nilKode##ket##method";
$kode = checkPostGet('kode','');
$jmlhPorsi = checkPostGet('jmlhPorsi','');
$method = checkPostGet('method','');

switch($method)
{
case 'update':	
	$str="update ".$dbname.".it_presentasenilai set jumlah='".$jmlhPorsi."',
	       where kode='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'insert':
	if($kode==''){
		exit('Warning : Kode standard harus diisi.');
	}
    $sCek="select distinct * from ".$dbname.".it_presentasenilai where kode='".$kode."'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$rRow=owlBaris($qCek);
    if($rRow>0)
    {
        $sdel="delete from ".$dbname.".it_presentasenilai where kode='".$kode."'";
		try{
			$owlPDO->exec($sdel); 
			
			$sCek="select distinct sum(jumlah) as jumlah from ".$dbname.".it_presentasenilai ";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rRow=$qCek->fetch();
			$jmlhCek=$rRow['jumlah']+$jmlhPorsi;
			if($jmlhCek>100)
			{
				$jmlhPorsi=100-$rRow['jumlah'];
			}
        
			$str="insert into ".$dbname.".it_presentasenilai (kode,jumlah)
			  values('".$kode."','".$jmlhPorsi."')";
			try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal,".addslashes($e->getMessage());
			}
		}
		catch (PDOException $e){
			
		}
    }
    else
    {
        $sCek="select distinct sum(jumlah) as jumlah from ".$dbname.".it_presentasenilai ";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
        $rRow=$qCek->fetch();
        $jmlhCek=$rRow['jumlah']+$jmlhPorsi;
        if($jmlhCek>100)
        {
            $jmlhPorsi=100-$rRow['jumlah'];
        }
        
		$str="insert into ".$dbname.".it_presentasenilai (kode,jumlah)
			  values('".$kode."','".$jmlhPorsi."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
		}
	}
    
	break;
case 'delete':
	$str="delete from ".$dbname.".it_presentasenilai
	where kode='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
	
	case'loadData':
        $str1="select * from ".$dbname.".it_presentasenilai order by kode asc";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		echo"<table class=sortable cellspacing=1 border=0 style='width:350px;'>
			 <thead>
			 <tr class=rowheader>
					 <td style='width:150px;'>".$_SESSION['lang']['kodeabs']."</td>
					 <td>".$_SESSION['lang']['jumlah']."</td>
					 <td style='width:70px;'>*</td></tr>
			 </thead>
			 <tbody>";
		while($bar1=$res1->fetch())
		{
			echo"<tr class=rowcontent>
						 <td align=left>".$bar1->kode."</td>
						 <td>".$bar1->jumlah."</td>
						 <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->jumlah."');\"> </td></tr>";
		}	 
		echo"	 
			 </tbody>
			 <tfoot>
			 </tfoot>
			 </table>";
        break;
default:
   break;					
}

?>

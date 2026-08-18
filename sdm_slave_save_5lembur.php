<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodeorg = checkPostGet('kodeorg','');
$tipelembur = checkPostGet('tipelembur','');
$jamaktual = checkPostGet('jamaktual','');
$jamlembur = checkPostGet('jamlembur','');
$method = checkPostGet('method','');

if($jamaktual=='')
   $jamaktual=0;
if($jamlembur=='')
   $jamlembur=0;

switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_5lembur set jamlembur='".$jamlembur."'
	       where kodeorg='".$kodeorg."' and tipelembur='".$tipelembur."'
		   and jamaktual=".$jamaktual;
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5lembur 
	      (kodeorg,tipelembur,jamaktual,jamlembur)
	      values('".$kodeorg."','".$tipelembur."',".$jamaktual.",".$jamlembur.")";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5lembur
	 where kodeorg='".$kodeorg."' and tipelembur='".$tipelembur."'
	 and jamaktual=".$jamaktual;
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
default:
   break;					
}


// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
// {
    $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
    $listOrg=" kodeorg in (".getOrgDetail(2).") ";
// }
// else if($_SESSION['empl']['tipelokasitugas']=='KANWIL')
// {
//     $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."'"
//         . "and kodeorganisasi not like '%HO%' ";
//     $listOrg=" kodeorg in (select kodeorganisasi from ".$dbname.".organisasi  where induk='".$_SESSION['org']['kodeorganisasi']."' "
//             . " and kodeorganisasi not like '%HO%') ";
// }
// else
// {
//     $sql="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'"
//         . "and kodeorganisasi not like '%HO%' ";
//     $listOrg=" kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."' ";
// } 


	$str1="select *,
	     case tipelembur when '0' then '".$_SESSION['lang']['haribiasa']."'
		 when '1' then '".$_SESSION['lang']['hariminggu']."'
		 when '2' then '".$_SESSION['lang']['harilibur']."'
		 when '3' then '".$_SESSION['lang']['hariraya']."'
		 when '4' then 'Hari Libur Spesial'
		 end as ketgroup 
	     from ".$dbname.".sdm_5lembur where ".$listOrg."
		 order by kodeorg,tipelembur,jamaktual";
		 
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	
	while($bar1=$res1->fetch())
	{
		$no+=1;
			echo"<tr class=rowcontent>
						<td>".$no."</td>
					   <td align=center>".$bar1->kodeorg."</td>
										
					   <td>".$bar1->ketgroup."</td>
					   <td align=center>".$bar1->jamaktual."</td>
					   <td align=center>".$bar1->jamlembur."</td>
					   <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->tipelembur."','".$bar1->jamaktual."','".$bar1->jamlembur."');\"></td></tr>";
	}
?>

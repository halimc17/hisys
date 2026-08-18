<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	


<?php		
$method=$_POST['method'];
$nm=$_POST['nm'];
$nu=$_POST['nu'];
$ki=$_POST['ki'];
$st=$_POST['st'];
$oldnm=$_POST['oldnm'];
$oldnu=$_POST['oldnu'];
$oldki=$_POST['oldki'];

$optNm=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

?>

<?php
switch($method)
{
	case 'insert':
	
		$oldnm==''?$oldnm=$_POST['nm']:$oldnm=$_POST['oldnm'];
		$oldnu==''?$oldnu=$_POST['nu']:$oldnu=$_POST['oldnu'];
		$oldki==''?$oldki=$_POST['ki']:$oldki=$_POST['oldki'];
		

			
		$sRicek="select * from ".$dbname.".kebun_5nourutmandor where nikmandor='".$oldnm."' and nourut='".$oldnu."' and karyawanid='".$oldki."' ";
		$qRicek=$owlPDO->query($sRicek) or die(print " Gagal: ".PDOException::getMessage());
		$rRicek=owlBaris($qRicek);
		
		if($rRicek>0){
			$sDel="delete from ".$dbname.".kebun_5nourutmandor where nikmandor='".$oldnm."' and nourut='".$oldnu."' and karyawanid='".$oldki."' ";
			try{
				$owlPDO->exec($sDel);
				
				$sDel2="insert into ".$dbname.".kebun_5nourutmandor (`nikmandor`,`nourut`,`karyawanid`,`aktif`,`updateby`) values ('".$nm."','".$nu."','".$ki."','".$st."','".$_SESSION['standard']['userid']."')";
				try{
					$owlPDO->exec($sDel2);
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}else{
			$sDel2="insert into ".$dbname.".kebun_5nourutmandor (`nikmandor`,`nourut`,`karyawanid`,`aktif`,`updateby`) values ('".$nm."','".$nu."','".$ki."','".$st."','".$_SESSION['standard']['userid']."')";
			try{
				$owlPDO->exec($sDel2);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}
	break;
	
	

		
case'loadData':
	
		$no=0;
		$str="select * from ".$dbname.".kebun_5nourutmandor order by nikmandor desc";
		$str2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$str2->setFetchMode(PDO::FETCH_ASSOC);
		while($bar1=$str2->fetch()){
			$no+=1;
			$tab="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$optNm[$bar1['nikmandor']]."</td>";
			//$tab.="<td align=right>".$bar1['nikmandor']."</td>"; 
			$tab.="<td align=right>".$bar1['nourut']."</td>";
			$tab.="<td align=left>".$optNm[$bar1['karyawanid']]."</td>";
			//$tab.="<td align=right>".$bar1['karyawanid']."</td>"; 
				if($bar1['aktif']==0)
				{	
					$tab.="<td>Tidak Aktif</td>";
				}
				else
				{	
					$tab.="<td>Aktif</td>";
				}	
				$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1['nikmandor']."','".$bar1['nourut']."','".$bar1['karyawanid']."','".$bar1['aktif']."');\">
				
				 <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$bar1['nikmandor']."','".$bar1['nourut']."','".$bar1['karyawanid']."');\"></td>";
				
		echo $tab;
		
		}
		
		
	case 'delete':
		$tab="delete from ".$dbname.".kebun_5nourutmandor where nikmandor='".$nm."' and nourut='".$nu."' and karyawanid='".$ki."' ";
		try{
			$owlPDO->exec($tab);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
default:
}
?>

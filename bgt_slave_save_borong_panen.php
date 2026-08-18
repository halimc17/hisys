<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$oldtahunbudget=$_POST['oldtahunbudget'];
$oldkodeorg=$_POST['oldkodeorg'];
$tahunbudget=$_POST['tahunbudget'];
$kodeorg=$_POST['kodeorg'];

$sb=$_POST['sb'];
$lb=$_POST['lb'];

$method=$_POST['method'];



switch($method)
{
	
	
		case 'insert':
		$oldtahunbudget==''?$oldtahunbudget=$_POST['tahunbudget']:$oldtahunbudget=$_POST['oldtahunbudget'];
		$oldkodeorg==''?$oldkodeorg=$_POST['kodeorg']:$oldkodeorg=$_POST['oldkodeorg'];
		
		if(strlen($tahunbudget)<4)
		{
			exit("Error:tahun budget belum sesuai");
		}	
		$sRicek="select * from ".$dbname.".bgt_borong_panen where tahunbudget='".$oldtahunbudget."' and kodeorg='".$oldkodeorg."' ";
		//exit("Error:$sRicek");
		$qRicek=$owlPDO->query($sRicek) or die(print " Gagal: ".PDOException::getMessage());
        $qRicek->setFetchMode(PDO::FETCH_ASSOC);
        $rRicek=owlBaris($qRicek);
		 
		
		if($rRicek>0){
			$sDel="delete from ".$dbname.".bgt_borong_panen
				where tahunbudget='".$oldtahunbudget."' and kodeorg='".$oldkodeorg."'  ";	
			try{
	            $owlPDO->exec($sDel); 
	            $sDel2="insert into ".$dbname.".bgt_borong_panen (`tahunbudget`,`kodeorg`,`siapborong`,`lebihborong`)
						values ('".$tahunbudget."','".$kodeorg."','".$sb."','".$lb."')";
				try{
	                $owlPDO->exec($sDel2); 
	            }catch (PDOException $e){
	                echo "DB Error : " . $e->getMessage();
	                die();
	            }
	        }catch (PDOException $e){
	            echo "DB Error : " . $e->getMessage();
	            die();
	        }    
		}
		else
		{
			$sDel2="insert into ".$dbname.".bgt_borong_panen (`tahunbudget`,`kodeorg`,`siapborong`,`lebihborong`)
			values ('".$tahunbudget."','".$kodeorg."','".$sb."','".$lb."')";
			try{
                $owlPDO->exec($sDel2); 
            }catch (PDOException $e){
                echo "DB Error : " . $e->getMessage();
                die();
            }
		}
	break;
	
	
	
	case'loadData':
		$str1="select * from ".$dbname.".bgt_borong_panen order by tahunbudget desc";
		$no=0;
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
		while($bar1=$res1->fetch()){
			  $no+=1;
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=right>".$bar1->tahunbudget."</td>
			<td align=left>".$bar1->kodeorg."</td>
			<td align=right>".$bar1->siapborong."</td>
			<td align=right>".$bar1->lebihborong."</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->tahunbudget."','".$bar1->kodeorg."','".$bar1->siapborong."','".$bar1->lebihborong."');\"></td></tr>";
		}
	break;
default:	
	
	
}
?>
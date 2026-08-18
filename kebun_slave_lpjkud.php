<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$periode=checkPostGet('periode','');
$namakud=checkPostGet('namakud','');
$upah=checkPostGet('upah','');
$material=checkPostGet('material','');
$transport=checkPostGet('transport','');
$lain=checkPostGet('lain','');
$method=checkPostGet('method','');

$nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

?>

<?php
switch($method)
{	
	case 'insert':
		$sCount="select * from ".$dbname.".kebun_lpjkud where periode='".$periode."' and namakud = '".$namakud."'";
		$res=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($res);
		if($numrows>=1){
			echo " Gagal, Periode sudah terdaftar didatabase.";
		}else{
			$str="insert into ".$dbname.".kebun_lpjkud (periode,namakud,upah,material,transport,lainnya)
			values ('".$periode."','".$namakud."','".$upah."','".$material."','".$transport."','".$lain."')";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo " Gagal,".addslashes($e->getMessage());
				die();
			}
		}
    break;

    case 'update':
  
		$str="update ".$dbname.".kebun_lpjkud 
		set upah='".$upah."',
		material='".$material."',
		transport='".$transport."',
		lainnya='".$lain."' 
		where periode='".$periode."' and namakud='".$namakud."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
			die();
		}
    break;
	
		
    case'loadData':
		echo"
		<div id=container>
			<table class=sortable cellspacing=1 border=0>
				<thead>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['nourut']."</td>
						<td align=center>".$_SESSION['lang']['periode']."</td>
						<td align=center>".$_SESSION['lang']['namakud']."</td>
						<td align=center>".$_SESSION['lang']['upah']."</td>
						<td align=center>".$_SESSION['lang']['material']."</td>
						<td align=center>".$_SESSION['lang']['transport']."</td>
						<td align=center>".$_SESSION['lang']['lain']."</td>
						<td align=center>".$_SESSION['lang']['action']."</td>
					</tr>
				</thead>
				<tbody>";
		
		
		
		$limit=10;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
			$page=0;
		}
		
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);
		
		$sCount="select count(*) as jmlhrow from ".$dbname.".kebun_lpjkud ";
		$qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
		$qCount->setFetchMode(PDO::FETCH_OBJ);
		while($rCount=$qCount->fetch()){
			$jlhbrs= $rCount->jmlhrow;
		}
		
		$str="select * from ".$dbname.".kebun_lpjkud order by periode desc limit ".$offset.",".$limit."";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		$no=$maxdisplay;
		while($res=$qry->fetch())
		{
			$no+=1;
			echo "<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res['periode']."</td>
					<td>".$nmSup[$res['namakud']]."</td>
					<td style='text-align:right;'>".number_format($res['upah'],2)."</td>
					<td style='text-align:right;'>".number_format($res['material'],2)."</td>
					<td style='text-align:right;'>".number_format($res['transport'],2)."</td>
					<td style='text-align:right;'>".number_format($res['lainnya'],2)."</td>";
					
			echo "<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$res['periode']."','".$res['namakud']."','".$res['upah']."','".$res['material']."','".$res['transport']."','".$res['lainnya']."');\">
					<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$res['periode']."','".$res['namakud']."');\">
				  </td>";
			echo "</tr>";
                    
		}
		
		echo"
		<tr class=rowheader><td colspan=18 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table>";
    break;

	case 'delete':
		$str="delete from ".$dbname.".kebun_lpjkud where periode='".$periode."' and namakud = '".$namakud."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
		}
	break;

	default:
	break;
}
?>

<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$organisasi=$_POST['organisasi'];
$regional=$_POST['regional'];
$method=$_POST['method'];
$subregional=$_POST['subregional'];
//$arrEnum=getEnum($dbname,'bgt_tipe','tipe,nama');
switch($method){
case 'update':	
	$str="update ".$dbname.".bgt_regional_assignment set regional='".$regional."', subregional='".$subregional."'
	       where kodeunit='".$organisasi."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
case 'insert':
	$str="insert into ".$dbname.".bgt_regional_assignment (kodeunit,regional,subregional)
	      values('".$organisasi."','".$regional."','".$subregional."')";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
	break;
case 'delete':
	$str="delete from ".$dbname.".bgt_regional_assignment  where kodeunit='".$organisasi."'";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".bgt_regional_assignment a left join ".$dbname.".organisasi b on a.kodeunit=b.kodeorganisasi order by induk, regional, kodeunit";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch()){
	$key = $bar1->kodeorganisasi;
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		echo"<tr class=rowcontent>
			<td colspan=5><b>".getNamaOrg($d)."</b></td>
		</tr>";
	}
	$no++;
	echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td>".$bar1->kodeunit." - ".getNamaOrg($bar1->kodeunit)."</td>
		<td>".$bar1->regional."</td>
		<td>".$bar1->subregional."</td>
		<td align=center><img src=images/application/application_delete.png class=resicon  caption='Edit' onclick=\"deleteDep('".$bar1->kodeunit."','".$bar1->regional."','".$bar1->subregional."');\"></td>
	</tr>";
	$n=$d;
}	 	 
?>

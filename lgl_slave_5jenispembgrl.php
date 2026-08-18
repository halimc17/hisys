<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$kodekegiatan = checkPostGet('kodekegiatan', '');
$namakegiatan = checkPostGet('namakegiatan', '');
$satuan = checkPostGet('satuan', '');
$noakun = checkPostGet('noakun', '');
$method = checkPostGet('method', '');

switch($method){
case 'getKode':
		
        $str = "select max(right(kode,2)) as nomorurut  from " . $dbname . ".lgl_kodepemby where akun ='".$noakun."' order by right(kode,2) desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			if(intval($bar['nomorurut'])==0){
			  $noawal = 1;
			}else{
			  $noawal = intval($bar['nomorurut'])+1;
			}
        
		$notran = $noakun.addZero($noawal,2);
		//exit('error'.$notran);
		echo $notran;
break;

case 'update':	
	$str="update ".$dbname.".lgl_kodepemby set nama='".$namakegiatan."'
	       where kode='".$kodekegiatan."'";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'insert':
	$str="insert into ".$dbname.".lgl_kodepemby (jenis,kode,akun,nama)
	      values('".$_POST['tipe']."','".$kodekegiatan ."','".$noakun."','".$namakegiatan."')";
        try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'delete':
	$str="delete from ".$dbname.".lgl_kodepemby 
	where kode='".$kodekegiatan."'";
        try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
default:
   break;					

case 'loaddata':
$str1="select * from ".$dbname.".lgl_kodepemby order by nama";

echo"<table class=sortable cellspacing=1 border=0 width=100%>
     <thead>
	 <tr class=rowheader>
		<td align=center style='width:70px;'>".$_SESSION['lang']['kodekegiatan']."</td>
		<td align=center style='width:350px;'>".$_SESSION['lang']['namakegiatan']."</td>
		<td align=center style='width:50px;display:none'>".$_SESSION['lang']['satuan']."</td>
		<td align=center style='width:70px;'>".$_SESSION['lang']['noakun']."</td>
		<td align=center style='width:70px;'>".$_SESSION['lang']['tipe']."</td>
		<td align=center style='width:30px;'>Action</td></tr>
	 </thead>
	 <tbody>";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
while($bar1=$res1->fetch()){
		echo"<tr class=rowcontent>
				<td align=center>".$bar1->kode."</td>
				<td>".$bar1->nama."</td>
				<td align=center style=display:none></td>
				<td align=center>".$bar1->akun."</td>
				<td align=center>".$bar1->jenis."</td>    
				<td style='text-align:center'><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->nama."','','".$bar1->akun."','".$bar1->jenis."');\"></td></tr>";
}	 
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";
break;
}
?>

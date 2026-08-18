<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

/* $kodekegiatan=$_POST['kodekegiatan'];
$namakegiatan=$_POST['namakegiatan'];
$satuan=$_POST['satuan'];
$noakun=$_POST['noakun'];
$method=$_POST['method'];
$methodx=$_POST['methodx'];
 */
$kodekegiatan= checkPostGet('kodekegiatan','');
$namakegiatan= checkPostGet('namakegiatan','');
$satuan= checkPostGet('satuan','');
$noakun= checkPostGet('noakun','');
$method= checkPostGet('method','');
$methodx= checkPostGet('methodx','');
$kelvhc= checkPostGet('kelvhc','');
$jnsvhc= checkPostGet('jnsvhc','');



switch($method){
case 'getKode':
		
        $str = "select max(right(kodekegiatan,2)) as nomorurut  from " . $dbname . ".vhc_kegiatan where noakun ='".$noakun."' order by right(kodekegiatan,2) desc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			if(intval($bar['nomorurut'])==0){
			  $noawal = 1;
			}else{
			  $noawal = intval($bar['nomorurut'])+1;
			}
        
		$notran = $noakun.addZero($noawal,3);
		echo $notran;
		
break;

case'getjenisvhc':
	$optjnsvhc="<option value='GLOBAL'>GLOBAL</option>";
	$str="select * from ".$dbname.".vhc_5jenisvhc where kelompokvhc='".$kelvhc."' order by jenisvhc asc";
	$res = fetchData($str);
	foreach($res as $key => $val){
		$optjnsvhc.="<option value=".$val['jenisvhc'].">".$val['namajenisvhc']."</option>";	
	}
	
	echo $optjnsvhc;
	
break;
case 'update':	
	$str="update ".$dbname.".vhc_kegiatan set namakegiatan='".$namakegiatan."', satuan='".$satuan."', noakun='".$noakun."', kelompokvhc='".$kelvhc."', jenisvhc='".$jnsvhc."',updateby='" . $_SESSION['standard']['userid'] . "'
	where kodekegiatan='".$kodekegiatan."'"; #exit("error".$str);
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'insert':
	$str="insert into ".$dbname.".vhc_kegiatan (kodekegiatan,namakegiatan,satuan,noakun,tipe,kelompokvhc,jenisvhc,createby,createtime)
	      values('".$kodekegiatan ."','".$namakegiatan."','".$satuan."','".$noakun."','".$_POST['tipe']."','".$kelvhc."','".$jnsvhc."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
        try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'delete':
	$str="delete from ".$dbname.".vhc_kegiatan 
	where kodekegiatan='".$kodekegiatan."'";
        try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
default:
   break;					

case 'loaddata':
	$optnm=makeOption($dbname,'keu_5akun','noakun,namaakun',"detail=1 and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7 and aktif=1");
	
	$arrklp=array('AB'=>'Alat Berat','MS'=>'Mesin - Mesin','KD'=>'Kendaraan','GLOBAL'=>'GLOBAL');
	$nmjnsvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');
	$nmjnsvhc['GLOBAL']='GLOBAL';
	

	$str1="select * from ".$dbname.".vhc_kegiatan order by kodekegiatan";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch()){
		$e="";
		if(@$optnm[$bar1->noakun]==''){
			$e=" style=background-color:red title='Nomor Akun Salah atau Nomor akun dinonaktifkan!'";
		}
	echo"<tr class=rowcontent ".$e.">
			<td align=center>".$bar1->kodekegiatan."</td>                 
			<td>".$bar1->namakegiatan."</td>
			<td align=center>".$bar1->satuan."</td>
			<td align=center>".$bar1->noakun."</td>  
			<td align=left>".@$optnm[$bar1->noakun]."</td>  
			<td align=center>".$bar1->tipe."</td>    
			<td align=left>".@$arrklp[$bar1->kelompokvhc]."</td>    
			<td align=left>".@$nmjnsvhc[$bar1->jenisvhc]."</td>    
			<td align=left>".getNamaKaryawan($bar1->updateby)."</td>    
			<td style='text-align:center'><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kodekegiatan."','".$bar1->namakegiatan."','".$bar1->satuan."','".$bar1->noakun."','".$bar1->tipe."','".$bar1->kelompokvhc."','".$bar1->jenisvhc."','".$nmjnsvhc[$bar1->jenisvhc]."');\">";
		
		$strkeg = "select *  from " . $dbname . ".vhc_rundt where jenispekerjaan ='".$bar1->kodekegiatan."'";
		$reskeg=fetchData($strkeg);
		
		if(count($reskeg)==0){
			echo"&nbsp;<img src=images/application/application_delete.png class=resicon caption='Delete' onclick=\"del('".$bar1->kodekegiatan."');\">";			
		}
		echo"</td></tr>";
	}	 
break;
}
?>

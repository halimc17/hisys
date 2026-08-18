<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$noakun = checkPostGet('noakun', '');
$akunpajak = checkPostGet('akunpajak', '');
$idsupplier = checkPostGet('idsupplier', '');
$an = checkPostGet('an', '');
$bank = checkPostGet('bank', '');
$rek = checkPostGet('rek', '');
$noseripajak = checkPostGet('noseripajak', '');
$nilaihutang = checkPostGet('nilaihutang', '');
$method = isset($_POST['method']) ? trim($_POST['method']) : '';

//make sure nilaihutang has a value
if ($nilaihutang == '')
    $nilaihutang = 0;

$strx = "";

switch ($method) {
    case 'update':
        $strx = "update " . $dbname . ".log_5supplier set
                   noakun='" . $noakun . "',
				   akunpajak='" . $akunpajak . "',
				   an='" . $an . "',
				   bank='" . $bank . "',
				   rekening='" . $rek . "',
				   noseripajak='" . $noseripajak . "',
				   nilaihutang=" . $nilaihutang . "
				   where supplierid='" . $idsupplier . "'
				  ";
        break;
        case'getNoakun':
        	$optNoakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        	$sKl="select * from ".$dbname.".log_5klsupplier where tipe='".$_POST['kode']."'";
        	$rKl=fetchData($sKl);
        	foreach($rKl as $row=>$lst){
        		$whrakn="noakun='".$lst['noakun']."'";
        		$nmAkun=makeOption($dbname,'keu_5akun',"noakun,namaakun",$whrakn);
        		if($_POST['kode']==$lst['tipe']){
        			$optNoakun.="<option value='".$lst['noakun']."' selected>".$nmAkun[$lst['noakun']]."</option>";	
        		}else{
        			$optNoakun.="<option value='".$lst['noakun']."'>".$nmAkun[$lst['noakun']]."</option>";
        		}
        		
        	}
        	echo $optNoakun;
        break;
    default:
        break;
}
if($strx!=''){
	try{
		$owlPDO->exec($strx); 
	}catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
}

if (isset($_POST['txt'])) {//find supplier
    $txt = $_POST['txt'];
    $str = " select * from " . $dbname . ".log_5supplier where namasupplier like '%" . $txt . "%' order by supplierid";
} else {//normal do
    $str = " select * from " . $dbname . ".log_5supplier where supplierid='" . $idsupplier . "' order by supplierid";
}
$no = 0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$no+=1;
	echo"<tr class=rowcontent>
		 <td>" . $bar->kodekelompok . "</td>
		 <td>" . $bar->supplierid . "</td>
		 <td>" . $bar->namasupplier . "</td>
		 <td>" . $bar->alamat . "</td>
		 <td>" . $bar->kontakperson . "</td>
		 <td>" . $bar->kota . "</td>
		 <td>" . $bar->telepon . "</td>		 
		 <td>" . $bar->fax . "</td>		 
		 <td>" . $bar->email . "</td>		 
		 <td>" . $bar->npwp . "</td>	 
		 <td align=right>" . number_format($bar->plafon, 0, ',', '.') . "</td>
		 <td>" . $bar->noakun . "</td>
		 <td>" . $bar->akunpajak . "</td>
		 <td>" . $bar->noseripajak . "</td>
		 <td>" . $bar->bank . "</td>
		 <td>" . $bar->rekening . "</td>
		 <td>" . $bar->an . "</td>
		 <td align=right>" . number_format($bar->nilaihutang, 0, ',', '.') . "</td>
		  <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAkunSupplier('" . $bar->supplierid . "','" . $bar->namasupplier . "','" . $bar->noakun . "','" . $bar->nilaihutang . "','" . $bar->noseripajak . "','" . $bar->akunpajak . "','" . $bar->bank . "','" . $bar->rekening . "','" . $bar->an . "');\"></td>
		 </tr>";
}
?>
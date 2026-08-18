<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/fpdf.php');
include_once('lib/zMysql.php');

$method=$_POST['method'];
$nopo=$_POST['nopo'];
$kolom=$_POST['kolom'];
$alasan=$_POST['alasan'];
$user_id=$_SESSION['standard']['userid'];

//exit('warning : masukk '.$method.$nopo."/".$kolom."/".$user_id);

switch($method) {
	case'get_form_approval':
		if ($kolom=='2'){
			$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
	        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC); 
	        $rest=$query->fetch();
	        $persetujuan1=$rest['hasilpersetujuan1'];
	        if($persetujuan1=='1'){
	        	$str = "update " . $dbname . ".log_poht set hasilpersetujuan".$kolom."='1',statuspo='2',tglp".$kolom."='".date('Y-m-d')."',stat_release='1', useridreleasae='".$rest['purchaser']."' ,tglrelease='".date('Y-m-d')."' where nopo='" . $nopo . "'";
		        try {
		            $owlPDO->exec($str);
		        } catch (PDOException $e) {
		            print " Gagal  !: " . $e->getMessage() . "\n";
		            die();
		        }
	        }else if($persetujuan1=='2'){
	        	$str = "update " . $dbname . ".log_poht set hasilpersetujuan".$kolom."='1',tglp".$kolom."='".date('Y-m-d')."',statuspo='0' where nopo='" . $nopo . "'";
		        try {
		            $owlPDO->exec($str);
		        } catch (PDOException $e) {
		            print " Gagal  !: " . $e->getMessage() . "\n";
		            die();
		        }
	        }else if($persetujuan1=='0'||$persetujuan1=''){
	        	exit('Warning : Harap Menunggu Persetujuan 1');
	        }
		}

        $str = "update " . $dbname . ".log_poht set hasilpersetujuan".$kolom."='1',tglp".$kolom."='".date('Y-m-d')."' where nopo='" . $nopo . "'";
        
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'get_form_rejected':
	echo"<div id=rejected_form>
	<fieldset>
	<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
	<table cellspacing=1 border=0>
            <tr>
                <td>Alasan</td>
                <td>:</td>
                <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
            </tr>
            <td><td><td>
            <button class=mybutton onclick=ditolakpo() id=ditolak >".$_SESSION['lang']['ditolak']."</button>

            <button class=mybutton onclick=cancel_po()>".$_SESSION['lang']['cancel']."</button>
            </td></tr></table>
	</fieldset>
	</div>
	<input type=hidden name=user_id id=user_id value=".$user_id." />
	<input type=hidden name=nopo id=nopo value=".$nopo."  />
	<input type=hidden name=kolom id=kolom value='".$kolom."' />
	";
	break;

	case 'reject_po':
    if ($kolom=='2'){
			$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
	        $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC); 
	        $rest=$query->fetch();
	        $persetujuan1=$rest['hasilpersetujuan1'];
	        if($persetujuan1=='0'||$persetujuan1==''){
	        	exit('Warning : Harap Menunggu Persetujuan 1');
	        }else if($persetujuan1!='0'||$persetujuan1!=''){
	        	$sql2="update ".$dbname.".log_poht set statuspo='0',hasilpersetujuan".$kolom."='2',tglp".$kolom."='".date('Y-m-d')."',komentartolak".$kolom."='".$alasan."' where nopo='".$nopo."'" ;	
				try{
					$owlPDO->exec($sql2); 
				}catch (PDOException $e){
					echo $sql2;
					echo "Gagal : ".$e->getMessage();
					exit();
				}
	        }
	    }

    $sql2="update ".$dbname.".log_poht set statuspo='0',hasilpersetujuan".$kolom."='2',tglp".$kolom."='".date('Y-m-d')."',komentartolak".$kolom."='".$alasan."' where nopo='".$nopo."'" ;	
	try{
		$owlPDO->exec($sql2); 
	}catch (PDOException $e){
		echo $sql2;
		echo "Gagal : ".$e->getMessage();
		exit();
	}

    break;

  //     case 'get_form_rejected':
		// echo"<div id=rejected_form>
		// <fieldset>
		// <legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
		// <table cellspacing=1 border=0>
		// <tr>
		// <td colspan=3>
		// Are you sure rejecting this PO</td></tr>
		// <tr><td colspan=3 align=center>
		// <button class=mybutton onclick=rejected_po_proses() >".$_SESSION['lang']['yes']."</button>
		// <button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button>
		// </td></tr></table>
		// </fieldset>
		// </div>
		// <input type=hidden name=method id=method  /> 
		// <input type=hidden name=user_id id=user_id value=".$user_id." />
		// <input type=hidden name=nopo id=nopo value=".$nopo."  />
		// <input type=hidden name=kolom id=kolom />
		// ";
		// break;

  //   case 'get_form_approval':
  //       $sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
  //       $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		// $query->setFetchMode(PDO::FETCH_ASSOC); 
  //       $rest=$query->fetch();

  //       for($i=1;$i<4;$i++) {
  //           if($user_id==$rest['persetujuan'.$i]) {
		// 		if($rest['persetujuan3']!='') {
		// 			echo"<br /><div id=approve>
		// 			<fieldset>
		// 			<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
		// 			<table cellspacing=1 border=0>
		// 			<tr>
		// 			<td colspan=3>
		// 			Submit to Purchasing dept for Release</td></tr>

		// 			<tr><td colspan=3 align=center>
		// 			<button class=mybutton onclick=close_po() >".$_SESSION['lang']['yes']."</button><button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button></td></tr></table><input type=hidden name=kolom id=kolom />
		// 			</fieldset>
		// 			</div>";
		// 		} else {	
		// 			echo"<br />
		// 			<div id=test>
		// 			<fieldset>
		// 			<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
		// 			<table cellspacing=1 border=0>
		// 			<tr>
		// 			<td colspan=3>
		// 			Submit for the next verification :</td>
		// 			</tr>
		// 			<td>".$_SESSION['lang']['namakaryawan']."</td>
		// 			<td>:</td>
		// 			<td valign=top>";

		// 			$optPur='';
		// 			$klq="select karyawanid,namakaryawan,bagian,lokasitugas from ".$dbname.".`datakaryawan` where tipekaryawan='0' and karyawanid!='".$user_id."' and lokasitugas!='' order by namakaryawan asc"; 
		// 	        $qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		// 			$qry->setFetchMode(PDO::FETCH_OBJ); 
		// 			while($rst=$qry->fetch())
		// 			{
		// 					$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
		// 			        $qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
		// 					$qBag->setFetchMode(PDO::FETCH_ASSOC); 							
		// 					$rBag=$qBag->fetch();
		// 					$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
		// 			}

  //                   echo"
		// 				<select id=id_user name=id_user>
		// 						$optPur; 
		// 				</select></td></tr>
		// 				<tr>

		// 				<td colspan=3 align=center>
		// 				<button class=mybutton onclick=forward_po() title=\"Submit for the next verification\" >".$_SESSION['lang']['diajukan']."</button>
		// 				<button class=mybutton onclick=close_form_po() title=\"Submit to Purchasing dept for Release\"  >".$_SESSION['lang']['kePurchaser']."</button>
		// 				<button class=mybutton onclick=cancel_po() title=\"Menutup Form Ini\">".$_SESSION['lang']['close']."</button>
		// 				</td></tr></table><br /> 

		// 				</fieldset></div>
		// 				<div id=approve style=display:none>
		// 				<fieldset>
		// 				<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo."  /></legend>
		// 				<table cellspacing=1 border=0>
		// 				<tr>
		// 				<td colspan=3>
		// 				Submit to Purchasing dept for Release</td></tr>

		// 				<tr><td colspan=3 align=center>
		// 				<button class=mybutton onclick=close_po() >".$_SESSION['lang']['yes']."</button>
		// 				<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button></td></tr></table>
		// 				</fieldset>
		// 				</div>
		// 				<input type=hidden name=method id=method  /> 
		// 				<input type=hidden name=user_id id=user_id value=".$user_id." />
		// 				<input type=hidden name=nopo id=nopo value=".$nopo."  />
		// 				<input type=hidden name=kolom id=kolom />
		// 				";
  //               }
  //           }
  //       }
  //       break;
	
    default:
        break;
}
?>
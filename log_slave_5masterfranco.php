<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##idFranco##nmFranco##almtFranco##cntcPerson##hdnPhn##statFr##method";
$method = checkPostGet('method', '');
$idFranco = checkPostGet('idFranco', '');
$nmFranco = checkPostGet('nmFranco', '');
$almtFranco = checkPostGet('almtFranco', '');
$cntcPerson = checkPostGet('cntcPerson', '');
$hdnPhn = checkPostGet('hdnPhn', '');
$email = checkPostGet('email', '');
$kodeunit = checkPostGet('kodeunit', '');
$statFr = checkPostGet('statFr', '');


switch ($method) {
    case'insert':
        if ($nmFranco == '') {
            echo"warning:Nama Franco tidak boleh kosong";
            exit();
        }
        $sCek = "select franco_name from " . $dbname . ".setup_franco where franco_name='" . $nmFranco . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning:Nama Franco sudah ada";
            exit();
        } else {
            if (($almtFranco == '') || ($cntcPerson == '')) {
                echo"warning:Alamat dan Contat Person tidak boleh kosong";
                exit();
            } else {
                $sIns = "insert into " . $dbname . ".setup_franco (`franco_name`,`alamat`,`contact`,`handphone`,`email`,`kodeunit`,`status`,`updateby`) values ('" . $nmFranco . "','" . $almtFranco . "','" . $cntcPerson . "','" . $hdnPhn . "','" . $email . "','".$kodeunit."','" . $statFr . "','" . $_SESSION['standard']['userid'] . "')";
				try{
					$owlPDO->exec($sIns); 
				}catch(PDOException $e){
					echo"Gagal" . $e->getMessage();
				}
            }
        }
        break;
    case'loadData':
        $no = 0;
        $arr = array("Aktif", "Tidak Aktif");
        $str = "select * from " . $dbname . ".setup_franco order by id_franco desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>
		<td align=center>" . $no . "</td>
		<td>" . $bar['franco_name'] . "</td>
		<td>" . $bar['alamat'] . "</td>
		<td>" . $bar['contact'] . "</td>
		<td>" . $bar['handphone'] . "</td>
        <td>" . $bar['email'] . "</td>
        <td>" . $bar['kodeunit'] . "</td>
		<td align=center>" . $arr[$bar['status']] . "</td>
		<td align=center>
			  <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['id_franco'] . "');\"> 
			  
		  </td>
		
		</tr>";
        }
        break;
    case'update':
        if (($almtFranco == '') || ($cntcPerson == '')) {
            echo"warning:Alamat dan Contat Person tidak boleh kosong";
            exit();
        } else {
            $sUpd = "update " . $dbname . ".setup_franco set `alamat`='" . $almtFranco . "',`contact`='" . $cntcPerson . "',`handphone`='" . $hdnPhn . "',`email`='" . $email . "',`kodeunit`='".$kodeunit."',`status`='" . $statFr . "' where id_franco='" . $idFranco . "'";
			try{
				$owlPDO->exec($sUpd); 
			}catch(PDOException $e){
				echo"Gagal" . $e->getMessage();
			}
        }
        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".setup_franco where id_franco='" . $idFranco . "'";
		try{
			$owlPDO->exec($sDel); 
		}catch(PDOException $e){
			echo"Gagal" . $e->getMessage();
		}
        break;
    case'getData':
        $sDt = "select * from " . $dbname . ".setup_franco where id_franco='" . $idFranco . "'";
		$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDet = $qDt->fetch();
        echo $rDet['id_franco'] . "###" . $rDet['franco_name'] . "###" . $rDet['alamat'] . "###" . $rDet['contact'] . "###" . $rDet['handphone'] . "###" . $rDet['email'] . "###" . $rDet['kodeunit'] . "###" . $rDet['status'];
        break;
    default:
        break;
}
?>
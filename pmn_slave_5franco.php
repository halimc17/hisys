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
$almtFranco = checkPostGet('almtFranco', '');
$nmFranco = checkPostGet('nmFranco', '');
$jual = checkPostGet('jual', '');
$statFr = checkPostGet('statFr', '');

$aslbrg = checkPostGet('aslbrg', '');
$dsrtim = checkPostGet('dsrtim', '');

$arrX = array('franco' => 'Franco', 'loco' => 'Loco', 'fob' => 'FOB', 'cif' => 'CIF');
$arrtim = array('0' => 'Penjual', '1' => 'Pembeli');
// $opttimb.="<option value='0'>Penjual</option>";
// $opttimb.="<option value='1'>Pembeli</option>";

switch ($method) {
    case'insert':
        if ($nmFranco == '') {
            echo"warning:Nama Franco tidak boleh kosong";
            exit();
        }
		
		// if ($aslbrg == '') {
            // echo"warning:Asal Barang tidak boleh kosong";
            // exit();
        // }
		
		if ($dsrtim == '') {
            echo"warning:Dasar Timbangan tidak boleh kosong";
            exit();
        }
		
        $sCek = "select franco_name from " . $dbname . ".pmn_5franco where franco_name='" . $nmFranco . "'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $rCek = owlBaris($qCek);
        if ($rCek > 0) {
            echo"warning:Nama Franco sudah ada";
            exit();
        } else {
            if ($almtFranco == '') {
                echo"warning:Alamat tidak boleh kosong";
                exit();
            } else {
                $sIns = "insert into " . $dbname . ".pmn_5franco (`franco_name`,`alamat`,`penjualan`,`status`,`updateby`,`asalbarang`,`dasartimbangan`) 
						values ('" . $nmFranco . "','" . $almtFranco . "','" . $jual . "','" . $statFr . "','" . $_SESSION['standard']['userid'] . "',
								'" . $aslbrg . "','" . $dsrtim . "')";
                try {
                    $owlPDO->exec($sIns);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        }
        break;

    case'loadData':
        $no = 0;
        $arr = array("Aktif", "Tidak Aktif");
        $str = "select * from " . $dbname . ".pmn_5franco order by id_franco desc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>
		<td align=center>" . $no . "</td>
		<td>" . $bar['franco_name'] . "</td>
		<td>" . $bar['alamat'] . "</td>
		<td>" . $arrX[$bar['penjualan']] . "</td>
		<td  align=center>" . $arrtim[$bar['dasartimbangan']] . "</td>
		<td  align=center>" . $arr[$bar['status']] . "</td>
		<td  align=center>" . getNamaKaryawan($bar['updateby']) . "</td>

		<td  align=center>
			  <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar['id_franco'] . "');\">  
		  </td>
		
		</tr>";
        }
        break;
    case'update':
	// if ($aslbrg == '') {
            // echo"warning:Asal Barang tidak boleh kosong";
            // exit();
        // }
		
		if ($dsrtim == '') {
            echo"warning:Dasar Timbangan tidak boleh kosong";
            exit();
        }
        if ($almtFranco == '') {
            echo"warning:Alamat tidak boleh kosong";
            exit();
        } else {
            $sUpd = "update " . $dbname . ".pmn_5franco set `franco_name`='" . $nmFranco . "',`alamat`='" . $almtFranco . "',
					`penjualan`='" . $jual . "',`status`='" . $statFr . "',asalbarang='".$aslbrg."',dasartimbangan='".$dsrtim."' 
					where id_franco='" . $idFranco . "'";
            try {
                $owlPDO->exec($sUpd);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;
    case'delData':
        $sDel = "delete from " . $dbname . ".pmn_5franco where id_franco='" . $idFranco . "'";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;

    case'getData':
        $sDt = "select * from " . $dbname . ".pmn_5franco where id_franco='" . $idFranco . "'";
        $qDt = $owlPDO->query($sDt) or die(print " Gagal: " . PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDet = $qDt->fetch();
        echo $rDet['id_franco'] . "###" . $rDet['franco_name'] . "###" . $rDet['alamat'] . "###" . $rDet['penjualan'] . "###" . $rDet['status'] . "###" . $rDet['asalbarang'] . "###" . $rDet['dasartimbangan'];
        break;
    default:
        break;
}
?>
<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$kdKry = checkPostGet('kdKry', '');
$stat = checkPostGet('status', '');
$kodeOrg = checkPostGet('kodeOrg', '');
$kdVhc = checkPostGet('kdVhc', '');
$sim = checkPostGet('sim', '');
$jabatan = checkPostGet('jabatan', '');
$jabatanlama = checkPostGet('jabatanlama', '');
$vhclama = checkPostGet('vhclama', '');

$str="select * from ".$dbname.".vhc_5master";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$nmkendaraan[$bar['kodevhc']]=$bar['detailvhc'];
	$nopol[$bar['kodevhc']]=$bar['nopol'];
}


switch ($proses) {
    case'insert_karyawan':
        if ($kdKry == '') {
            echo"warning: Please Select Karyawan";
            exit();
        } 
		$where="kodevhc='".$kdVhc."'";
		$ckKlm=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc',$where);
		// if($ckKlm[$kdVhc]=='KD'){
			// if($sim=='' && $jabatan=='0'){
				// echo "warning : Driver / Supir wajib memiliki SIM \nDaftarkan SIM melalui menu : SDM - Transaksi - Data Karyawan";
				// exit();
			// }
		// }
		
        $sqlCek = "select * from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "' and jabatan='".$jabatan."' and vhc='".$kdVhc."'";
        $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
        $rowCek = owlBaris($queryCek);
        if ($rowCek < 1) {
            $skry = "select namakaryawan from " . $dbname . ".datakaryawan where karyawanid='" . $kdKry . "'";
            $qkry = $owlPDO->query($skry) or die(print " Gagal: " . PDOException::getMessage());
            $qkry->setFetchMode(PDO::FETCH_ASSOC);
            $rkry = $qkry->fetch();
            $sqlIns = "insert into " . $dbname . ".vhc_5operator (`karyawanid`,`nama`,`aktif`,`vhc`,`jabatan`,`createby`,`createtime`) values ('" . $kdKry . "','" . $rkry['namakaryawan'] . "','" . $stat . "','" . $kdVhc . "','" . $jabatan . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
            //exit("error:$sqlIns");
            try {
                $owlPDO->exec($sqlIns);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            echo"Warning : Nama operator sudah di input.";
            exit();
        }
        break;
    case'deleteKry':
        $sdel = "delete from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
    case'load_new_data':
        // exit("Error:masuk");
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $optLtgs = makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas');
		
		
        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".vhc_5operator where karyawanid in (select distinct karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "') order by nama asc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {

            $jlhbrs = $jsl->jmlhrow;
        }

        $arrPos = array("NonAktif", "Aktif");
		$arrJab=array("0"=>"Driver","1"=>"Helper","2"=>"Operator");
        $str = "select * from " . $dbname . ".vhc_5operator where 
				karyawanid in (select distinct karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "') order by nama asc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
			
			$nosim=makeOption($dbname, 'datakaryawan','karyawanid,sim',"karyawanid='".$bar->karyawanid."'");
			$nik=makeOption($dbname, 'datakaryawan','karyawanid,nik',"karyawanid='".$bar->karyawanid."'");
			$lokasitugas=makeOption($dbname, 'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$bar->karyawanid."'");

            $no+=1;

            echo"<tr class=rowcontent id='tr_" . $no . "'>
                <td align=center>" . $no . "</td>
                <td>" . $nik[$bar->karyawanid] . "</td>
                <td>" . $bar->nama . "</td>
                <td>" . $lokasitugas[$bar->karyawanid] . "</td>
				<td>" .$arrJab[$bar->jabatan]."</td>
                <td>" . $arrPos[$bar->aktif] . "</td>
                <td>" . $bar->vhc . "</td>
                <td>" . $nmkendaraan[$bar->vhc] . " ".$nopol[$bar->vhc]."</td>
                <td>" . $nosim[$bar->karyawanid] . "</td>
                <td>" . getNamaKaryawan($bar->updateby) . "</td>
				<td align=center>
                <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->karyawanid . "','" . $bar->jabatan . "','" . $bar->aktif . "','" . $bar->vhc . "','".$nosim[$bar->karyawanid]."');\">		

                </td>
                </tr>";
        }
        echo" <tr><td colspan=10 align=center>
				" . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
				<br />
				<button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
				<button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
				</td>
				</tr>";
        break;
    case'update_karyawan':
		$where="kodevhc='".$kdVhc."'";
		$ckKlm=makeOption($dbname,'vhc_5master','kodevhc,kelompokvhc',$where);
		if($ckKlm[$kdVhc]=='KD'){
			/*if($sim==''&& $jabatan=='0'){
				echo "warning : Driver / Supir wajib memiliki SIM \nDaftarkan SIM melalui menu : SDM - Transaksi - Data Karyawan";
				exit();
			}*/
		}
	    
        $sqlCek = "select * from " . $dbname . ".vhc_5operator where karyawanid='" . $kdKry . "' and jabatan='".$jabatan."' and aktif='$stat' and jabatan = '$jabatan' and vhc = '$kdVhc'";
        $queryCek = $owlPDO->query($sqlCek) or die(print " Gagal: " . PDOException::getMessage());
        $rowCek = owlBaris($queryCek);
        if ($rowCek > 1) {
            echo"Warning : Already Insert";
                exit();
        }
        else{
            $sql = "update " . $dbname . ".vhc_5operator set aktif='" . $stat . "', jabatan='" . $jabatan . "',vhc='" . $kdVhc . "',updateby='" . $_SESSION['standard']['userid'] . "' where karyawanid='" . $kdKry . "' and jabatan='".$jabatanlama."' and vhc = '$vhclama'";
            try {
                $owlPDO->exec($sql);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        break;
    case'getKrywan':
        $sDtkry = "select namakaryawan,karyawanid from " . $dbname . ".datakaryawan where lokasitugas='" . $kodeOrg . "'";
        $qDtkry = $owlPDO->query($sDtkry) or die(print " Gagal: " . PDOException::getMessage());
        $qDtkry->setFetchMode(PDO::FETCH_ASSOC);
        while ($rDtkry = $qDtkry->fetch()) {
            $optKry.="<option value=" . $rDtkry['karyawanid'] . " " . ($rDtkry['karyawanid'] == $kdKry ? 'selected' : '') . ">" . $rDtkry['namakaryawan'] . "</option>";
        }
        echo $optKry;
        break;
		
	case'getnosim':
        $sDtkry2 = "select namakaryawan,karyawanid,sim from " . $dbname . ".datakaryawan where karyawanid='" . $kdKry . "'";
		
		// exit ('error:'.$sDtkry2);
        $qDtkry2 = $owlPDO->query($sDtkry2) or die(print " Gagal: " . PDOException::getMessage());
        $qDtkry2->setFetchMode(PDO::FETCH_ASSOC);
        $rDtkry2 = $qDtkry2->fetch();
        
		echo $rDtkry2['sim'];
        break;
		
    default:
        break;
}
?>
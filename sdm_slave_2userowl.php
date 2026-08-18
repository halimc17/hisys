<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

// get post ========================================================================= taken from sdm_slave_2prasarana
$kodeorg = $_REQUEST['kodeorg'];
$status = $_REQUEST['status'];
$proses = $_REQUEST['proses'];

// get namaorganisasi =========================================================================
$sOrg = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $namaorg[$rOrg['kodeorganisasi']] = $rOrg['namaorganisasi'];
}

// get nama jabatan =========================================================================
$sOrg = "select kodejabatan,namajabatan from " . $dbname . ".sdm_5jabatan";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
    $kamusjabatan[$rOrg['kodejabatan']] = $rOrg['namajabatan'];
}

$kamusstatus['0'] = 'Tidak Aktif';
$kamusstatus['1'] = 'Aktif';

// building array: dzArr (main data) =========================================================================

$stream="<table cellspacing='1' cellpadding=5 border='0' class='sortable'>
	<thead class=rowheader> 
	<tr>
	<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
	<th align=center>" . $_SESSION['lang']['username'] . "</th>
	<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
	<th align=center>" . $_SESSION['lang']['jabatan'] . "</th>
	<th align=center>" . $_SESSION['lang']['lokasitugas'] . "</th>
	<th align=center>" . $_SESSION['lang']['subbagian'] . "</th>
	<th align=center>" . $_SESSION['lang']['status'] . "</th>
	<th align=center>Last Login [Date & Time]</th>
	<th align=center>Last Login IP</th>
	<th align=center>Computer Name</th>
	<th align=center>Last Online</th>
	</tr>
	</thead>
	<tbody>
	";

//ambil IP and Login
$str = "SELECT lastip, lastcomp, max(lastupdate) as lastupdate, lastuser 
	FROM " . $dbname . ".login_history GROUP BY lastuser";
// echo $str;
$query = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $lastlog[$res['lastuser']] = $res['lastupdate'];   
    $lastip[$res['lastuser']] = $res['lastip'];   
    $lastcomp[$res['lastuser']] = $res['lastcomp'];   
}

//ambil waktu aktif
// echo $waktu = "SELECT username, karyawanid, max(waktu) as waktu 
	// FROM " . $dbname . ".user_activity where waktu > '".date('Y-m-d H')."' GROUP BY username, karyawanid";
// $query = $owlPDO->query($waktu) or die(print " Gagal: " . PDOException::getMessage());
// $query->setFetchMode(PDO::FETCH_ASSOC);
// while ($res = $query->fetch()) {
    // $waktulog[$res['username']] = $res['waktu'];   
// }

$ini_array = parse_ini_file("lib/nangkoel.ini");
$param['MAXLIFETIME']=$ini_array['MAXLIFETIME'];


// user
if($kodeorg!=''){
	$wh=" and b.lokasitugas in ('".$kodeorg."')";
}else{
	$wh=" and b.lokasitugas in (".getOrgDetail(2).")";
}

$str = "SELECT a.karyawanid, b.lokasitugas, a.status, b.kodejabatan, b.subbagian, b.namakaryawan ,a.namauser
	FROM " . $dbname . ".user a
    LEFT JOIN " . $dbname . ".datakaryawan b on a.karyawanid = b.karyawanid
    WHERE 1=1 ".$wh."
    ORDER BY b.namakaryawan asc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$nomnom=0;
while ($bar = $res->fetch()) {
		
		$waktu = "SELECT waktu FROM " . $dbname . ".user_activity where username='".$bar['namauser']."' and karyawanid='".$bar['karyawanid']."' order by waktu desc limit 1";
		$req = fetchdata($waktu);
		
		$param['DIE'][$bar['namauser']]=strtotime($lastlog[$bar['namauser']])+$param['MAXLIFETIME'];
		
        // echo "<td>" . strtotime($req[0]['waktu']) . "<br>" . date('Y-m-d H:i:s',strtotime($req[0]['waktu'])) . "</td>";
        // echo "<td>" . $param['DIE'][$bar['namauser']] . "<br>" . date('Y-m-d H:i:s',$param['DIE'][$bar['namauser']]) . "</td>";
        // echo "<td>".date('Y-m-d H:i:s',time()-300)."</td>";
		$online=true;
		// if(time()>$param['DIE'][$bar['namauser']]){
			// $tab="<td nowrap>" . $req[0]['waktu']. " <img src='images/skyblue/posting.png' class='zImgBtn' title='Offline'></td>";			
			// $online=false;
		// }else{			
		// }
		if(strtotime($req[0]['waktu'])<time()-900){
			if($proses=='excel'){
				$tab="<td nowrap>" . $req[0]['waktu']. " Offline</td>";
			}else{
				$tab="<td nowrap>" . $req[0]['waktu']. " <img src='images/skyblue/posting.png' class='zImgBtn' title='Offline'></td>";
			}			
			$online=false;
		}else{
			if($proses=='excel'){
				$tab="<td nowrap>" . $req[0]['waktu']. " Online</td>";
			}else{
				$tab="<td nowrap>" . $req[0]['waktu']. " <img src='images/skyblue/posted.png' class='zImgBtn' title='Online'></td>";
			}			
			$online=true;
		}
		
		if($status=='1' and $online==false){			
			$stream.= "<tr class=rowcontent style=display:none>";
		}elseif($status=='0' and $online==true){			
			$stream.= "<tr class=rowcontent style=display:none>";
		}else{
			$stream.= "<tr class=rowcontent>";
			$nomnom++;
		}
		
        $stream.= "<td align=right>" . $nomnom . "</td>";
        $stream.= "<td style=cursor:pointer;color:blue; onclick=\"setMapUserMenu('".$bar['namauser']."')\">" . $bar['namauser'] . "</td>";
        $stream.= "<td>" . $bar['namakaryawan'] . "</td>";
        $stream.= "<td>" . $kamusjabatan[$bar['kodejabatan']] . "</td>";
        $stream.= "<td>" . $namaorg[$bar['lokasitugas']] . "</td>";
        $stream.= "<td>" . $namaorg[$bar['subbagian']] . "</td>";
        $stream.= "<td>" . $kamusstatus[$bar['status']] . "</td>";
        $stream.= "<td style=cursor:pointer;color:blue; onclick=\"popup('".$bar['namauser']."','Log')\">" . $lastlog[$bar['namauser']] . "</td>";
        $stream.= "<td>" . $lastip[$bar['namauser']] . "</td>";
        $stream.= "<td>" . $lastcomp[$bar['namauser']] . "</td>";
        $stream.= $tab;
		$stream.= "</tr>";
}

$stream.="</tbody></table>";

switch ($proses) {
    case 'preview':
        echo $stream;
        break;
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "Laporan_User_Owl_".$tglSkrg;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
	default:
		break;
}
?>
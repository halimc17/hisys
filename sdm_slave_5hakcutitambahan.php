<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$kodegolongan=checkPostGet('kodegolongan','');
$levelkaryawan=checkPostGet('levelkaryawan','');
$tipekaryawan=checkPostGet('tipekaryawan','');
$hakcuti=checkPostGet('hakcuti','');
$masaaktif=checkPostGet('masaaktif','');
$masakerja=checkPostGet('masakerja','');
$method=checkPostGet('method','');
$nmgol=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');

switch ($method) {

    case 'insert':

        $str = "select count(*) as jumlah from " . $dbname . ".sdm_5hakcuti_tambahan  where kodeorg = '".$kodeorg."' and levelkaryawan = '".$levelkaryawan."' and kodegolongan='".$kodegolongan."' and tipekaryawan='".$tipekaryawan."'";
        $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $n->setFetchMode(PDO::FETCH_ASSOC);
        $d = $n->fetch();
        $jmlh=$d['jumlah'];

        if ($jmlh>0){
            exit('warning : Data sudah ada.');
        }

        if($kodeorg == ''){
            exit("Warning : Kodeorg wajib diisi" );
        }

        if($masakerja == 0 || $masakerja < 0){
            exit("warning : Masa kerja harus > 0");
        }

        if($masaaktif == 0 || $masaaktif < 0){
            exit("warning : Masa aktif harus > 0");
        }

        $str="insert into ".$dbname.".sdm_5hakcuti_tambahan (kodeorg,kodegolongan,levelkaryawan,tipekaryawan,masakerja,masaaktif,hakcuti,updateby,createdby)
            values ('" . $kodeorg . "','" . $kodegolongan . "','" . $levelkaryawan . "','" . $tipekaryawan . "','" . $masakerja . "','" . $masaaktif . "','" . $hakcuti . "','" . $_SESSION['standard']['userid'] . "','" . $_SESSION['standard']['userid'] . "')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
        break;

    case 'update':

        if($masakerja == 0 || $masakerja < 0){
            exit("warning : Masa kerja harus > 0");
        }

        if($masaaktif == 0 || $masaaktif < 0){
            exit("warning : Masa aktif harus > 0");
        }

        $str = "update " . $dbname . ".sdm_5hakcuti_tambahan set updateby='" . $_SESSION['standard']['userid'] . "', updatetime ='".date('Y-m-d H:i:s')."',masakerja='" . $masakerja . "',masaaktif='" . $masaaktif . "',hakcuti = '".$hakcuti."'
             where kodeorg = '".$kodeorg."' and levelkaryawan = '".$levelkaryawan."' and kodegolongan='".$kodegolongan."' and tipekaryawan='".$tipekaryawan."'";
        try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
            
        break;

    case'loadData':

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5hakcuti_tambahan"; // echo $ql2;notran
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
        
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13 align=center>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $str = "select * from " . $dbname . ".sdm_5hakcuti_tambahan  limit " . $offset . "," . $limit . "";
            $n=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $n->setFetchMode(PDO::FETCH_ASSOC);
            $no = $maxdisplay;
            while ($d = $n->fetch()) {
                
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
                $nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
                $nmLevelkaryawan = makeOption($dbname, 'sdm_5levelkaryawan', 'kode,nama');
                $nmTipekaryawan = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

                if($nmgol[$d['kodegolongan']] == ''){
                    $kodegolongan = "Seluruhnya";
                }else{
                    $kodegolongan = $nmgol[$d['kodegolongan']];
                }
        
                if($nmLevelkaryawan[$d['levelkaryawan']] == ''){
                    $levelkaryawan = "Seluruhnya";
                }else{
                    $levelkaryawan = $nmLevelkaryawan[$d['levelkaryawan']];
                }

                if($nmTipekaryawan[$d['tipekaryawan']] == ''){
                    $tipekaryawan = "Seluruhnya";
                }else{
                    $tipekaryawan = $nmTipekaryawan[$d['tipekaryawan']];
                }

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td align=left>" . $nmOrg[$d['kodeorg']] . "</td>";
                $tab.="<td align=left>" . $kodegolongan . "</td>";
                $tab.="<td align=left>" . $levelkaryawan . "</td>";
                $tab.="<td align=left>" . $tipekaryawan . "</td>";
                $tab.="<td align=center>" . $d['masakerja'] . "</td>";
                $tab.="<td align=center>" . $d['masaaktif'] . "</td>";
                $tab.="<td align=center>" . $d['hakcuti'] . "</td>";
                $tab.="<td align=center>" . (isset($nmKar[$d['createdby']]) ? $nmKar[$d['createdby']] : '') . "</td>";
                $tab.="<td align=center>" . $d['createtime'] . "</td>";
                $tab.="<td align=center>" . (isset($nmKar[$d['updateby']]) ? $nmKar[$d['updateby']] : '') . "</td>";
                $tab.="<td align=center>" . $d['updatetime'] . "</td>";
                $tab.="<td align=center>
                            <img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('" . $d['kodeorg'] . "','" . $d['kodegolongan'] . "','" . $d['levelkaryawan'] . "','".$d['tipekaryawan']."','".$d['masaaktif']."','".$d['masakerja']."','".$d['hakcuti']."');\">
                        </td>";
                $tab.="</tr>"; 
            }
            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=13 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        
        echo $tab."####".$footd;
    break;

    default:
}
?>

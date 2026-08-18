<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$idjenis = checkPostGet('idjenis', '');
$jenis = checkPostGet('jenis', '');
$umakan = checkPostGet('umakan', '');
$utransport = checkPostGet('utransport', '');
$statuspotongan = checkPostGet('statuspotongan', '');
$jumlahhk = checkPostGet('jumlahhk', '0');
$potonganhk = checkPostGet('potonganhk', '0');
$method = checkPostGet('method','');
$arrstatus=array('0' => $_SESSION['lang']['tidakpotong'],'1' => $_SESSION['lang']['potong']);



switch ($method) {
    case 'insert':
      $listCek="select * from ".$dbname.".sdm_5jenisijin where jenisijin like '%".$jenis."%' and idjenis like '%".$idjenis."%'";
        $qcek=$owlPDO->query($listCek) or die(print " Gagal: ".PDOException::getMessage());
        $rcek=owlBaris($qcek);
        if($rcek != 0){
          exit('warning : Jenis Cuti sudah pernah terdaftar');
        }

        $jeniscuti = "CUTI";
        $query="select right(idjenis,2) as nomorurut from ".$dbname.".sdm_5jenisijin where left(idjenis,4) = '".$jeniscuti."' order by right(idjenis,2) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
      
        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }
       

        $idjenis=$jeniscuti.addZero($awal,2);
        // exit('error: '.$idjenis);

        if ($jumlahhk > '1.0') {
            echo "Error: Nilai HK tidak lebih dari 1.0";
        } else {
            $input ="insert into ".$dbname.".sdm_5jenisijin (idjenis,jenisijin,createdby) values ('".$idjenis."','".$jenis."','".$_SESSION['standard']['userid']."')";
            try{
              $owlPDO->exec($input); 
            }catch(PDOException $e){
              echo " Gagal," . addslashes($e->getMessage());
            }
        }
        
        
    break;
		
    case 'update':
        if ($jumlahhk > '1.0') {
            echo "Error: Nilai HK tidak lebih dari 1.0";
        } else {
            $input = "update " . $dbname . ".sdm_5jenisijin set jenisijin='" . $jenis . "', updateby='" . $_SESSION['standard']['userid'] . "'
                     where idjenis='" . $idjenis . "'";
            try{
                $owlPDO->exec($input); 
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }
        }
            
    break;

    case'loadData':
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".sdm_5jenisijin where idjenis<>'' ".$where."";
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) 
		{
			$jlhbrs = $jsl->jmlhrow;
        }
		
		$tab='';
		$nor=0;
		$input = "select * from " . $dbname . ".sdm_5jenisijin where idjenis<>'' ".$where."";
		$n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
		$n->setFetchMode(PDO::FETCH_ASSOC);
        $no = $maxdisplay;
        while ($d = $n->fetch()) 
		{
			$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
            $no+=1;
            echo"<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td align=left>" . $d['idjenis'] . "</td>
				<td align=left>" . $d['jenisijin'] . "</td>
				<td align=center hidden>" . $arrstatus[$d['uangmakan']] . "</td>
                <td align=center hidden>" . $arrstatus[$d['uangtransport']] . "</td>
				<td align=center hidden>" . $d['statuspotongan'] . "</td>
				<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $d['idjenis'] . "','" . $d['jenisijin'] . "','" . $d['nilaihk'] . "','".$d['potonganhk']."','".$d['uangmakan']."','".$d['uangtransport']."','".$d['statuspotongan']."');\">
				</td>
			</tr>"; 
        }
        
		echo"</tbody></table>";
	break;

	default:
	break;
}

?>
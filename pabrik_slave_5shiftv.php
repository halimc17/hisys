<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$pks = checkPostGet('pks', '');
$shift = checkPostGet('shift', '');

$jmmulai=checkPostGet('jmmulai','');
$mnmulai=checkPostGet('mnmulai','');
$waktumulai=$jmmulai.":".$mnmulai.":00";

$jmselesai=checkPostGet('jmselesai','');
$mnselesai=checkPostGet('mnselesai','');

$waktuselesai=$jmselesai.":".$mnselesai.":00";
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch($method)
{
	case 'insert':
    if($jmmulai==''||$mnmulai==''){
        exit('warning: Jam Mulai Harus Terisi');
    }
    if($jmselesai==''||$mnselesai==''){
        exit('warning: Jam Selesai Harus Terisi');
    }
		$str="insert into ".$dbname.".pabrik_5shift (`kodeorg`,`shift`,`jammulai`,`jamselesai`,`createby`,`createtime`)
		values ('".$pks."','".$shift."','".$waktumulai."','".$waktuselesai."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
                try{$owlPDO->exec($str); }
                catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
	break;
        
    case 'update':
        if($jmmulai==''||$mnmulai==''){
            exit('warning: Jam Mulai Harus Terisi');
        }
        if($jmselesai==''||$mnselesai==''){
            exit('warning: Jam Selesai Harus Terisi');
        }
		$str="update ".$dbname.".pabrik_5shift set jammulai='".$waktumulai."',jamselesai='".$waktuselesai."',updateby='" . $_SESSION['standard']['userid'] . "' where kodeorg='".$pks."' and shift='".$shift."'";
                try{$owlPDO->exec($str); }
                    catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; 
                        die(); 
                    }
	break;
        

case'loadData':
		echo"<div id=container>
                        <table class=sortable cellspacing=1 border=0>
                         <thead>
                                     <tr class=rowheader>
                                        <td align=center>".$_SESSION['lang']['nourut']."</td>
                                        <td align=center>".$_SESSION['lang']['pabrik']."</td>
                                        <td align=center>".$_SESSION['lang']['shift']."</td>    
                                        <td align=center>".$_SESSION['lang']['jammulai']."</td>
										<td align=center>".$_SESSION['lang']['jamselesai']."</td>
                                        <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
                                        <td align=center>".$_SESSION['lang']['action']."</td>
                                     </tr>
                            </thead>
                            <tbody>";

		$str="select * from ".$dbname.".pabrik_5shift";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
                    $updateby   = $bar['createby'];
                    if($bar['updateby'] == '0000000000'){
                        $updateby = $bar['createby'];
                    }
                    $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$updateby."'");
                    @$no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
                    echo "<td align=right>".$bar['shift']."</td>";
                    echo "<td align=right>".$bar['jammulai']."</td>";
                    echo "<td align=right>".$bar['jamselesai']."</td>";
                    echo "<td align=center>".$nmKar[$updateby]."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  
							caption='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['shift']."'
							,'".substr($bar['jammulai'],0,2)."','".substr($bar['jammulai'],3,2)."'
							,'".substr($bar['jamselesai'],0,2)."','".substr($bar['jamselesai'],3,2)."');\">
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['shift']."');\">
                            </td>";
                    echo "</tr>";
			}
                echo"
		</table>";
                
	break;	

		//$str="select * from ".$dbname.".pabrik_5hargatbs ".$tmbh3."  ".$tmbh2." ".$tmbh." order by tanggal desc";
		

	case 'delete':
		$tab="delete from ".$dbname.".pabrik_5shift where kodeorg='".$pks."' and shift='".$shift."'  ";		
        try{$owlPDO->exec($tab); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	break;
	
	
	
	
default:
}
?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$periode=checkPostGet('periode','');
$hariminggu=checkPostGet('hariminggu','');
$harilibur=checkPostGet('harilibur','');
$hkefektif=checkPostGet('hkefektif','');
$catatan=checkPostGet('catatan','');
$kodept=checkPostGet('kodept','');

switch($method)
{
case'insert':
    $qwe=explode('-',$periode);
    $periode=$qwe[0].$qwe[1];
    if($hkefektif=='')
    {
            echo "warning : Silakan memilih periode.";
            exit();
    }
    if($hkefektif<=0)
    {
            echo "warning : HK Efektif <= 0.";
            exit();
    }
    if($kodept=='')
    {
            echo "warning : Silakan mengisi kode PT.";
            exit();
    }

    $sIns="insert into ".$dbname.".sdm_hk_efektif (`periode`,`minggu`,`libur`,`hkefektif`,`catatan`,`kodeorg`) 
        values ('".$periode."','".$hariminggu."','".$harilibur."','".$hkefektif."','".$catatan."','".$kodept."')";
	try{
		$owlPDO->exec($sIns); 
	}
	catch (PDOException $e){
		echo"Gagal".$e->getMessage();
		die();
	}
    break;

    case'loadData':
    $whr=getOrgDetail(4);

    $str="select * from ".$dbname.".sdm_hk_efektif where kodeorg in (".$whr.") order by periode desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch())
    {
        $no+=1;	
        echo"<tr class=rowcontent>
        <td>".$no."</td>
        <td align=left>".getNamaOrg($bar['kodeorg'])."</td>
        <td align=right>".substr($bar['periode'],0,4)."-".substr($bar['periode'],4,2)."</td>
        <td align=right>".$bar['minggu']."</td>
        <td align=right>".$bar['libur']."</td>
        <td align=right>".$bar['hkefektif']."</td>
        <td align=right>".$bar['catatan']."</td>
        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletehk('".$bar['periode']."','".$bar['kodeorg']."');\"></td>
        </tr>";	
    }     
    break;
    case'delete':
    $sIns="delete from ".$dbname.".sdm_hk_efektif where periode = '".$periode."' and kodeorg = '".$kodept."'";
        try{
			$owlPDO->exec($sIns); 
		}
		catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
    break;
        default:
        break;
}
?>
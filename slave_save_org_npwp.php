<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$switch=checkPostGet('switch','');
$npwp=checkPostGet('npwp','');
$alamatnpwp=checkPostGet('alamatnpwp','');
$alamatdom=checkPostGet('alamatdom','');
$nopkp=checkPostGet('nopkp','');
$statuss=checkPostGet('statuss','');
$defaultyo=checkPostGet('defaultyo','');
$inisial=checkPostGet('inisial','');
$namakpp=checkPostGet('namakpp','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$strdefault = array ("0"=>"tidak","1"=>"ya");

switch($switch){
	case 'delete':
		$str="delete from ".$dbname.".setup_org_npwp where kodeorg='".$kodeorg."' and npwp='".$npwp."'"; 
		$owlPDO->exec($str);
    break;
	
	case 'insert':
		$alamatnpwp = preg_replace( "/\r|\n/", " ", $alamatnpwp);
		$alamatdom = preg_replace( "/\r|\n/", " ", $alamatdom);
	
		$strx=$owlPDO->query("select * from ".$dbname.".setup_org_npwp where kodeorg='".$kodeorg."' limit 1");   	
		$strx->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($strx);
        // if($numrows>0) 
        // {
                // $stry="update ".$dbname.".setup_org_npwp 
                // set alamatnpwp='".$alamatnpwp."',
                // npwp='".$npwp."',
                // alamatdomisili='".$alamatdom."'
                // where kodeorg='".$kodeorg."'";
        // }
        // else
        // {
                $stry="insert into ".$dbname.".setup_org_npwp(kodeorg,alamatnpwp,npwp,alamatdomisili,no_pkp,status,defaults,inisial,kpp)
                values('".$kodeorg."','".$alamatnpwp."','".$npwp."','".$alamatdom."','".$nopkp."','".$statuss."','".$defaultyo."','".$inisial."','".$namakpp."')";

		try{
			$owlPDO->exec($stry);   
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			 die();
		}
	break;
	
	case 'update':
		$alamatnpwp = preg_replace( "/\r|\n/", " ", $alamatnpwp);
		$alamatdom = preg_replace( "/\r|\n/", " ", $alamatdom);
		$str="update ".$dbname.".setup_org_npwp set alamatnpwp='".$alamatnpwp."', alamatdomisili='".$alamatdom."', no_pkp='".$nopkp."', status='".$statuss."', defaults='".$defaultyo."', inisial='".$inisial."', kpp='".$namakpp."' where npwp='".$npwp."' and kodeorg='".$kodeorg."';
                values('".$kodeorg."','".$alamatnpwp."','".$npwp."','".$alamatdom."','".$nopkp."','".$statuss."','".$defaultyo."','".$inisial."','".$namakpp."')";
		
		try{
			$owlPDO->exec($str);   
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			 die();
		}
	break;

	case 'loaddata':
		$tab="";
		$str="select * from ".$dbname.".setup_org_npwp order by kodeorg asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$tab.="<tr class=rowcontent>";
            $tab.="<td style='text-align:center'>".$val['kodeorg']."</td>";
			$tab.="<td>".$nmorg[$val['kodeorg']]."</td>";
			$tab.="<td style='min-width:120px;'>".$val['npwp']."</td>";
            $tab.="<td style='text-align:center'>".$val['inisial']."</td>";
            $tab.="<td>".$val['alamatnpwp']."</td>";
			$tab.="<td>".$val['alamatdomisili']."</td>";
			$tab.="<td>".$val['no_pkp']."</td>";
            $tab.="<td style='text-align:center;min-width:70px'>".$strnama[$val['status']]."</td>";
            $tab.="<td style='text-align:center'>".$strdefault[$val['defaults']]."</td>";
            $tab.="<td style='display:none'>".@$nmsup[$val['kpp']]."</td>";
			$tab.="<td style='text-align:center'>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editnpwp('".$val['kodeorg']."','".$val['npwp']."','".$val['inisial']."','".$val['alamatnpwp']."','".$val['alamatdomisili']."','".$val['no_pkp']."','".$val['status']."','".$val['defaults']."');\">
			</td>";
			$tab.="<td style='text-align:center'>
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delnpwp('".$val['kodeorg']."','".$val['npwp']."');\">
			</td>";
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
}

?>

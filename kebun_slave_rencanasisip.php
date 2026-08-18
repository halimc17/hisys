<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$kebun=checkPostGet('kebun','');
$blok=checkPostGet('blok','');
$periode=checkPostGet('periode','');
$pokok=checkPostGet('pokok','');
$sph=checkPostGet('sph','');
$pokokmati=checkPostGet('pokokmati','');
$rencanasisip=checkPostGet('rencanasisip','');
$keterangan=checkPostGet('keterangan','');
$periode2=checkPostGet('periode2','');
$ba=checkPostGet('ba','');

$rencanasisip=checkPostGet('rencanasisip','');
$pokok=checkPostGet('pokok','');

switch($method)
{
    case 'gantiblok':
        $jumlahpokok=0;
        $sph=0;
        $sBlok="select kodeorg, jumlahpokok, luasareaproduktif from ".$dbname.".setup_blok 
            where kodeorg like '".$blok."%'";
		$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
		$qBlok->setFetchMode(PDO::FETCH_ASSOC);
        while($rBlok=$qBlok->fetch())
        {
            $jumlahpokok=$rBlok['jumlahpokok'];
            $luasareaproduktif=$rBlok['luasareaproduktif'];
            @$sph=$jumlahpokok/$luasareaproduktif;
        }        
        echo $jumlahpokok.'##'.number_format($sph,2).'##'.number_format($luasareaproduktif,2).'##';
        exit();
    break;
	
	case 'hitungsph':
        // $jumlahpokok=0;
        // $sph=0;
        $sBlok="select kodeorg, jumlahpokok, luasareaproduktif from ".$dbname.".setup_blok 
            where kodeorg like '".$blok."%'";
		$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
		$qBlok->setFetchMode(PDO::FETCH_ASSOC);
        $rBlok=$qBlok->fetch();
			$luas=$rBlok['luasareaproduktif'];
			$sphssp=number_format(($pokok+$rencanasisip)/$luas,2);
			$jpkkkkk=($pokok+$rencanasisip);
			
		// exit ("error:$pokok._.$rencanasisip._.$jpkk");
        echo $sphssp.'##'.$jpkkkkk;
		exit();
    break;
	
	
case 'update':	
    $str="update ".$dbname.".kebun_rencanasisip set pokok='".$pokok."', sph='".$sph."', pokokmati='".$pokokmati."', rencanasisip='".$rencanasisip."', keterangan='".$keterangan."'
    where periode='".$periode."' and blok='".$blok."'";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal, ".addslashes($e->getMessage());   
        exit;
	}
break;
case 'insert':
    $str="insert into ".$dbname.".kebun_rencanasisip (blok,periode,pokok,sph,pokokmati,rencanasisip,keterangan)
        values('".$blok."','".$periode."','".$pokok."','".$sph."','".$pokokmati."','".$rencanasisip."','".$keterangan."')";
    try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal, ".addslashes($e->getMessage());    
        exit;
	}
break;
case 'delete':
    $str="delete from ".$dbname.".kebun_rencanasisip
    where periode='".$periode."' and blok='".$blok."'";
    try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal, ".addslashes($e->getMessage());
        exit;
	}
break;
case 'posting':
	// Get Pokok Sisip
	$qSisip = selectQuery($dbname,'kebun_rencanasisip','pokokmati,rencanasisip',
						  "periode='".$periode."' and blok='".$blok."'");
	$resSisip = fetchData($qSisip);
	
	// Get Jumlah Pokok
	$optBlok = makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',
						  "kodeorg='".$blok."'");
	
	// Validasi
	// if($optBlok[$blok] < $resSisip[0]['pokokmati'])
		// exit("Warning: Jumlah Pokok Blok lebih kecil dari pokok yang mati di rencana");
	
    $str="update ".$dbname.".kebun_rencanasisip
        set posting='1',nomorba='".$ba."'
    where periode='".$periode."' and blok='".$blok."'";
    try{
		$owlPDO->exec($str); 
		// Update
		$dataUpd = array('jumlahpokok'=>$optBlok[$blok] - $resSisip[0]['pokokmati']);
		$qUpd = updateQuery($dbname,'setup_blok',$dataUpd,"kodeorg='".$blok."'");
		try{
			$owlPDO->exec($qUpd); 
		}catch (PDOException $e){
			exit("Update Blok Error: ".$e->getMessage());
		}
	}catch (PDOException $e){
		echo " Gagal, ".addslashes($e->getMessage());
        exit;
	}
break;
default:
break;					
}
$optPeriode2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sPeriode="select distinct periode from ".$dbname.".kebun_rencanasisip order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    if($rPeriode['periode']==$periode2)$pilih=' selected'; else $pilih='';
    $optPeriode2.="<option value='".$rPeriode['periode']."'".$pilih.">".$rPeriode['periode']."</option>";
}

echo "<table><tr>
        <td>".$_SESSION['lang']['periode']."</td>
        <td><select id=periode2 onchange=pilihperiode()>".$optPeriode2."</select></td>
    </tr></table>";

$where = "";
if(substr($_SESSION['empl']['lokasitugas'],2,2) != "HO") {
	$where = " and t1.blok like '".$_SESSION['empl']['lokasitugas']."%' ";
}
$str1="select t1.*, t2.deskripsi from ".$dbname.".kebun_rencanasisip t1
	left join ".$dbname.".kebun_5alasanrencanasisip t2
	on t1.keterangan=t2.kodealasanrencanasisip 
	where t1.periode like '".$periode2."%' ".$where." order by t1.periode desc, t1.blok";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:100%;'>
     <thead>
     <tr class=rowheader>
        <td>".$_SESSION['lang']['periode']."</td>
        <td>".$_SESSION['lang']['blok']."</td>
        <td>".$_SESSION['lang']['pokok']."</td>
        <td>".$_SESSION['lang']['sph']."</td>
        <td>".$_SESSION['lang']['pokokmati']."</td>
        <td>".$_SESSION['lang']['rencanasisip']."</td>
        <td>".$_SESSION['lang']['alasanrencanasisip']."</td>
        <td>".$_SESSION['lang']['action']."</td>
     </tr></thead>
     <tbody>";
$no=0;
while($bar1=$res1->fetch())
{ 
    $no+=1;
    echo"<tr class=rowcontent>
        <td>".$bar1->periode."</td>
        <td>".$bar1->blok."</td>
        <td align=right>".number_format($bar1->pokok)."</td>
        <td align=right>".number_format($bar1->sph,2)."</td>
        <td align=right>".number_format($bar1->pokokmati)."</td>
        <td align=right>".number_format($bar1->rencanasisip)."</td>
        <td>".$bar1->deskripsi."</td>
        <td align=center>";
            if($bar1->posting=='0'){ // belum posting
                echo"<img src=images/application/application_edit.png class=resicon  caption='Edit' 
                onclick=\"fillField('".$bar1->periode."','".$bar1->blok."','".$bar1->pokok."','".$bar1->sph."','".$bar1->pokokmati."','".$bar1->rencanasisip."','".$bar1->keterangan."');\">
                <img src=images/application/application_delete.png class=resicon  caption='Edit' onclick=\"hapus('".$bar1->periode."','".$bar1->blok."');\">";
                echo"&nbsp;<img src=images/skyblue/posting.png class=resicon caption='Posting' onclick=\"posting('".$bar1->periode."','".$bar1->blok."',event);\">";                    
            }else{ // sudah postng
                echo"&nbsp;<img src=images/skyblue/posted.png>";                    
            }
        echo"</td>
    </tr>";
}	 
echo"</tbody>
    <tfoot>
    </tfoot>
    </table>";

?>

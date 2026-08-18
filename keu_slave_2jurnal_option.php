<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');



$pt=isset($_POST['pt'])?$_POST['pt']:'';
$regional=isset($_POST['regional'])?$_POST['regional']:'';
$proses=isset($_POST['proses'])?$_POST['proses']:'';
$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

switch($proses)
{
	case'getkaryawan':
		$optkaryawan="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".datakaryawan where kodeorganisasi='".$pt."' ";
		// exit("Error:".$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkaryawan.="<option value='".$bar['karyawanid']."'>".$bar['nik']." - ".$bar['lokasitugas']." - ".$bar['namakaryawan']."</option>";
		}
		echo $optkaryawan;
	break;
	
	
	case'getReg':
            if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
				$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";
				$str="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' ";
			} else {
				$str="select distinct(regional) as regional from ".$dbname.".regional_pt 
					where pt='".$pt."' and regional='".$_SESSION['empl']['regional']."' ";	
			}
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $optReg.="<option value='".$bar['regional']."'>".$bar['regional']."</option>";
            }
            echo $optReg;
	break;
	
	/*
		case'getReg':
            if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
				$optReg="<option value=''>".$_SESSION['lang']['all']."</option>";
				$str="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' ";
			} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL') {
				$str="select distinct(regional) as regional from ".$dbname.".regional_pt where pt='".$pt."' and regional<>'JAKARTA' ";	
			} else {
				$str="select distinct(regional) as regional from ".$dbname.".regional_pt 
					where pt='".$pt."' and regional='".$_SESSION['empl']['regional']."' ";	
			}
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $optReg.="<option value='".$bar['regional']."'>".$bar['regional']."</option>";
            }
            echo $optReg;
	break;
	*/
	
        
	case'getUnit':
		$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."' "
				. " and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optUnit.="<option value='".$bar['kodeunit']."'>".$bar['kodeunit']." - ".$nmOrg[$bar['kodeunit']]."</option>";
		}
		echo $optUnit;
	break; 
        
	case'getUnit2':
		$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$nmOrg[$bar['kodeorganisasi']]."</option>";
		}
		echo $optUnit;
	break;
        
	
	default;
    
}
?>
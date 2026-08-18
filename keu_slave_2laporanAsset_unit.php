<?php
// -- ind --
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param = $_POST;


switch($param['method']){
	case'getUnit':
		$optUnit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"induk='".$param['pt']."'");
		echo "<option value=''>".$_SESSION['lang']['all']."</option>";
		foreach($optUnit as $code=>$val) {
			echo "<option value='".$code."'>".$code." - ".$val."</option>";
		}
	break;

	case'getjbiaya':
   
        $optjb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $arjb = getEnum($dbname, 'project', 'jenis_biaya');
        foreach ($arjb as $kei => $fal) {
            if ((substr($param['unit'],2,2)=='HO')&&($fal!=3)){
                continue;   
            }

            if ((substr($param['unit'],2,2)!='HO')&&($fal==3)){
                continue;
            }

            if ($fal==1){
                $capt="Biaya Langsung";
            }
            if ($fal==2){
                $capt="Biaya Tidak Langsung";
            }
            if ($fal==3){
                $capt="Operasi";
            }

            $optjb.="<option value='" . $kei . "'>" . $capt . "</option>";
        }

    echo $optjb;

    break;

	case'getsubtpasset':
		$opt.="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$param['tpAsset']."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()) {
            $opt.="<option value=".$bar['kodesub'].">".$bar['namasub']."</option>";
        }
        echo $opt;
		
	break;
	



}
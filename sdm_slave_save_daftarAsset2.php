<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
$method	=$_POST['method'];
switch($method){
    case 'goCariGudang':
    echo "
    <table style=width:100% cellspacing=1 border=0 class=data>
    <thead>
    <tr class=rowheader>
    <td align=center>No</td>
    <td align=center>".$_SESSION['lang']['nopo']."</td>
    <td align=center>".$_SESSION['lang']['keterangan']."</td>
    </tr>
    </thead>
    </tbody>";
    if ($_POST['tipedoc'] == 'po') {
    	$i = "select * from ".$dbname.".log_poht where nopo like '%".$_POST['noGudang']."%' ";
    	$res = $owlPDO->query($i)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($d = $res->fetch()) {
    		$no += 1;
    		echo "
    		<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickGudang('".$d['nopo']."')>
    		<td align=center>".$no."</td>
    		<td>".$d['nopo']."</td>
    		<td>".$d['keterangan']."</td>
    		</tr>
    		";
    	}
    } else {
    	$i = "select * from ".$dbname.".log_spkht where notransaksi like '%".$_POST['noGudang']."%' ";
    	$res = $owlPDO->query($i)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($d = $res->fetch()) {
    		$no += 1;
    		echo "
    		<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickGudang('".$d['notransaksi']."')>
    		<td>".$no."</td>
    		<td>".$d['notransaksi']."</td>
    		<td>".$d['keterangan']."</td>
    		</tr>
    		";
    	}
    }
    break;
    case 'goCariInduk':
    	echo "
    	<table cellspacing=1 border=0 class=data>
    	<thead>
    	<tr class=rowheader>
    	<td align=center>No</td>
    	<td align=center width=120px>".$_SESSION['lang']['kodeasset']."</td>
    	<td align=center>".$_SESSION['lang']['namaasset']."</td>
    	</tr>
    	</thead>
    	</tbody>";
    	$i = "select kodeasset,namasset from ".$dbname.".sdm_daftarasset where kodeasset like '%".$_POST['noInduk']."%'"
    		." or namasset like '%".$_POST['noInduk']."%' ";
    	$res = $owlPDO->query($i)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($d = $res->fetch()) {
    		$no += 1;
    		echo "
    		<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=goPickInduk('".$d['kodeasset']."')>
    		<td  align=center>".$no."</td>
    		<td align=center>".$d['kodeasset']."</td>
    		<td>".$d['namasset']."</td>
    		</tr>
    		";
    	}
    	break;
    case 'getKodeAkhir':
    	//exit("Error:Masuk");
    	$sPt = "select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".$_POST['kodeorg']."'";
    	$res = $owlPDO->query($sPt)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	$rPt = $res->fetch();
    	$kpl = $rPt['induk']."-".$_POST['kdAset'].$_POST['sub'];
    	//exit("error:$kpl");
    	$tppenyusutan = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,metodepenyusutan');
    	$scek = "select distinct kodeasset from ".$dbname.".sdm_daftarasset
    		where kodeasset like '".$kpl."%' order by kodeasset desc limit 0,1";
    	$urut = 0;
    	$res = $owlPDO->query($scek)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	$rcek = $res->fetch();
    	if ($rcek['kodeasset'] != '') {
    		$urut = substr($rcek['kodeasset'], -6);
    	}
    	// exit("Error:".);
    	$rer = intval($urut);
    	$kdcrt = $rer + 1;
    	$kdcrt = addZero($kdcrt, 5);
    	if (strlen($_POST['kdAset']) < 3) {
    		$kdcrt = addZero($kdcrt, 6);
    	}
    	$kdasst = $kpl.$kdcrt;
    	echo $kdasst."#####".$tppenyusutan[$_POST['kdAset']];
    	break;
    case 'getSub':
    	$optSub = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    	$iSub = "select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$_POST['tipe']."' ";
    	$res = $owlPDO->query($iSub)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	while ($dSub = $res->fetch()) {
    		if ($_POST['sub'] == $dSub['kodesub']) {
    			$select = "selected=selected";
    		} else {
    			$select = "";
    		}
    		$optSub.="<option ".$select." value='".$dSub['kodesub']."'>".$dSub['namasub']."</option>";
    	}
    	echo $optSub;
		
    	break;
    case 'changetipelokasi':

        $orgarr=makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe', "kodeorganisasi='".$_POST['posisiasset']."'");
        $optSub = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $tipelokasi='';

        if($orgarr[$_POST['posisiasset']]=='HOLDING')
        {
            $tipelokasi='HOLDING';
        }
        else if($orgarr[$_POST['posisiasset']]=='KANWIL')
        {
            $tipelokasi='RO';
        }
        else if($orgarr[$_POST['posisiasset']]=='PABRIK')
		{
			$tipelokasi='MILL';
		} 
		else 
        {
            $tipelokasi='SITE';
        }

        $optSub = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $iSub = "select * from ".$dbname.".keu_5tipelokasiasset where tipelokasi='".$tipelokasi."' ";
        $res = $owlPDO->query($iSub)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($dSub = $res->fetch()) {
            if ($_POST['lokasi'] == $dSub['kodelokasi']) {
                $select = "selected=selected";
            } else {
                $select = "";
            }
            $optSub.="<option ".$select." value='".$dSub['kodelokasi']."'>".$dSub['namalokasi']."</option>";
        }
        echo $optSub;
        break;
    case 'getSusut':
    	$iSub = "select umurpenyusutan from ".$dbname.".sdm_5subtipeasset where kodetipe='".$_POST['tipe']."' and  kodesub='".$_POST['sub']."' ";
    	$res = $owlPDO->query($iSub)or die(print " Gagal: ".PDOException::getMessage());
    	$res->setFetchMode(PDO::FETCH_ASSOC);
    	$dSub = $res->fetch();
    	$susut = $dSub['umurpenyusutan'];
    	echo $susut;
    	break;
    default:
    	break;
}
?>
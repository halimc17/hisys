<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$jenispel = checkPostGet('jenispel', '');
$sppelanggaran = checkPostGet('sppelanggaran', '');
$kodesp = checkPostGet('kodesp', '');
$idpelanggaran = checkPostGet('idpelanggaran', '');
$stat = checkPostGet('stat', '');
$idjenispelanggaran=$kodesp.$idpelanggaran;
$optDtstat=array("0"=>$_SESSION['lang']['aktif'],"1"=>$_SESSION['lang']['nonaktif']);
switch ($proses) {
	case 'insert':
        if(strlen($idjenispelanggaran)<7){
            $strCount ="select right(idjenispelanggaran,3) as nourut from " . $dbname . ".sdm_5jenispelanggaran where kode='".$kodesp."' order by idjenispelanggaran desc limit 1";
            $rData=fetchData($strCount);
            if(intval($rData[0]['nourut'])==0){
                $idpelanggaran=addZero(1,3);
            }else{
                $idpelanggaran=addZero((intval($rData[0]['nourut'])+1),3);
            }
            $idjenispelanggaran=$idjenispelanggaran.$idpelanggaran;
        }

        if ($jenispel == '' || $sppelanggaran == '' || $kodesp == '' || $idpelanggaran == '') {
            echo 'Gagal : Semua field harus diisi.';
        } else {
            $strCount = "select * from " . $dbname . ".sdm_5jenispelanggaran where kode='".$kodesp."' and idjenispelanggaran='".$idjenispelanggaran."'";
            $qryCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
            $numRows = owlBaris($qryCount);
            if ($numRows >= 1) {
                echo "Gagal : Data sudah pernah di input.";
            } else {
                $str = "insert into " . $dbname . ".sdm_5jenispelanggaran (kode,idjenispelanggaran,pelanggaran,updateby,status) values ('".$kodesp."','".$idjenispelanggaran."','".$jenispel."','".$_SESSION['standard']['userid']."','".$stat."')";
                try
                {
                    $owlPDO->exec($str);
                }
                catch (PDOException $e)
                {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }
        }
    break;

    case 'update':
        $str = "update ".$dbname.".sdm_5jenispelanggaran set pelanggaran='".$jenispel."', updateby='" . $_SESSION['standard']['userid'] . "',status='".$stat."' where kode='".$kodesp."' and idjenispelanggaran='".$idpelanggaran."'";
        try {
            $owlPDO->exec($str);
           // loadData();
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'loadData':
        echo"<div id=container>
            <table class=sortable cellspacing=1 border=0>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>".$_SESSION['lang']['id']." ".$_SESSION['lang']['surat']."</td>
                <td align=center>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['surat']."</td>
                <td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['pelanggaran']."</td>
                <td align=center>" . $_SESSION['lang']['status'] . "</td>
                <td align=center>" . $_SESSION['lang']['aksi'] . "</td>
            </tr>
        </thead>
        <tbody>";

        $limit=10;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
                $page=0;
            }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);

        $ql2="select count(*) as jmlhrow from ".$dbname.".sdm_5jenispelanggaran";
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch()){  
            $jlhbrs= $jsl->jmlhrow;
        }
        $no=$maxdisplay;

        $str = "select * from ".$dbname.".sdm_5jenispelanggaran limit ".$offset.",".$limit."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
        $no+=1;
        $optSt=makeOption($dbname,'sdm_5jenissp','kode,keterangan');
        echo"<tr class=rowcontent>
                <td style='text-align:center;'>" . $no . "</td>
                <td>".$bar->idjenispelanggaran."</td>
                <td>".$bar->kode." - ".$optSt[$bar->kode]."</td>
                <td>".$bar->pelanggaran."</td>
                <td>".$optDtstat[$bar->status]."</td>
                <td style='text-align:center;'>
                    <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->pelanggaran."','".$bar->kode."','".$bar->idjenispelanggaran."','".$bar->status."')\">
                </td>        
            </tr>";
        }
        echo"
            <tr class=rowheader><td colspan=5 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>
            </tbody>";

    break;

}

?>
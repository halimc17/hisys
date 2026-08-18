<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');

$proses = checkPostGet('proses', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$rekening = checkPostGet('rekening', '');
$unit = checkPostGet('unit', '');
$pt = checkPostGet('pt', '');
$pt2 = checkPostGet('pt2', '');
$afd = checkPostGet('afd', '');
$jenis = checkPostGet('jenis', '');
$hasil = checkPostGet('hasil', '');


switch ($proses) {
	case'adddetail':

        $_POST['saldo']=str_replace(',', '', $_POST['saldo']);        
        $_POST['estimasi']=str_replace(',', '', $_POST['estimasi']);        
        $_POST['saldoblokir']=str_replace(',', '', $_POST['saldoblokir']);        
        $sDet="insert into ".$dbname.".keu_prosesaging values ";
        for($arDt=0;$arDt<$_POST['totrow'];$arDt++){

            $str="select * from ".$dbname.".keu_prosesaging where tanggal='".$tanggal."' and rekening='".$_POST['rekening']."' and noinvoice='".$_POST['noinvoice'][$arDt]."'";
            $res=fetchdata($str);
            $jlhbrs=count($res);
            if ($jlhbrs>0) {
                $sdel="delete from ".$dbname.".keu_prosesaging where tanggal='".$tanggal."' and rekening='".$_POST['rekening']."' and noinvoice='".$_POST['noinvoice'][$arDt]."'";
                try{
                    $owlPDO->exec($sdel); 
                }catch (PDOException $e) {
                    print " error: delete\n: " . $e->getMessage() . "<br/>"; die(); 
                }
            }

            $_POST['bayar'][$arDt]=str_replace(',', '', $_POST['bayar'][$arDt]); 
            if($arDt==0){
                $sDet.=" ('".$tanggal."','".$_POST['rekening']."','".$_POST['noinvoice'][$arDt]."'
                	,'".$_POST['bayar'][$arDt]."','".$_POST['saldo']."','".tanggalsystemn($_POST['ketsaldo'])."',
                	'".$_POST['estimasi']."','".$_POST['ketestimasi']."','".$_POST['saldoblokir']."','".$_POST['ketblokir']."',
                	'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
            }else{
                $sDet.=",('".$tanggal."','".$_POST['rekening']."','".$_POST['noinvoice'][$arDt]."'
                	,'".$_POST['bayar'][$arDt]."','".$_POST['saldo']."','".tanggalsystemn($_POST['ketsaldo'])."',
                	'".$_POST['estimasi']."','".$_POST['ketestimasi']."','".$_POST['saldoblokir']."','".$_POST['ketblokir']."',
                	'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
            }
        }
        // exit('warning: masukk'.$sDet);
        try{ 
            $owlPDO->exec($sDet); 
        }
        catch (PDOException $e){
        echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
        }
    break;

    case 'getsaldo':
        $str="select posisisaldo,estimasi,tanggal,keterangan from ".$dbname.".keu_posisisaldobank where kodeorg='".$unit."' and norekening='".$rekening."' and tanggal<='".$tanggal."' order by tanggal desc limit 1 ";
        $res=$owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();

        echo number_format($bar['posisisaldo'],2)."####".number_format($bar['estimasi'],2)."####".tanggalnormal($bar['tanggal'])."####".$bar['keterangan'];
    break;
}
?>
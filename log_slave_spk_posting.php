<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
//$postingSpk=$param['postingSpk'];
$proses=$param['proses'];
$noTrans=$param['noTrans'];
switch($proses)
{
	case'postingSpk':
	$qPosting = selectQuery($dbname,'setup_posting','jabatan',"kodeaplikasi='".$app."'");
	$tmpPost = fetchData($qPosting);
	$postJabatan = $tmpPost[0]['jabatan'];
	
	$sCek="select kodeorg,notransaksi,divisi,posting from ".$dbname.".log_spkht where notransaksi='".$noTrans."' and posting=0";
    // echo $sCek;
    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
    $qCek->setFetchMode(PDO::FETCH_OBJ);
    $bar=$qCek->fetch();
    $kodeorganisasi=$bar->kodeorg;
	$rCek=owlBaris($qCek);
	if($rCek>0)
	{
		//periksa realisasi
                while($bar=$qCek->fetch())
                {
                    $x =0;
                    $strx="select sum(jumlahrealisasi) from ".$dbname.".log_baspk 
                          where notransaksi='".$noTrans."'";
				    $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				    $resx->setFetchMode(PDO::FETCH_NUM);
                    while($barx=$resx->fetch())
                    {
                      $x= $barx[0]; 
                    }   
                    //lihat postingan-=============================
                    $y ='';
                    $strx="select statusjurnal from ".$dbname.".log_baspk 
                          where notransaksi='".$noTrans."' and statusjurnal=0";
                    // echo $strx;
				    $resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
				    $resx->setFetchMode(PDO::FETCH_NUM);          
                    if(owlBaris($resx)>0)
                        exit('Warning:Realisasi SPK belum di posting');
                    else if($x==0 and $y=='')
                        exit('Warning:Belum Ada Realisasi');
                    else
                    {}
            
                }
               
        $sCekTot="select kodeblok from ".$dbname.".log_spkdt where notransaksi='".$noTrans."'";
	    $qCekTot=$owlPDO->query($sCekTot) or die(print " Gagal: ".PDOException::getMessage());
	    $qCekTot->setFetchMode(PDO::FETCH_NUM);
		$rCekTot=owlBaris($qCekTot);
		
		$sCekTot2="select kodeblok from ".$dbname.".log_baspk where notransaksi='".$noTrans."'";
	    $qCekTot2=$owlPDO->query($sCekTot2) or die(print " Gagal: ".PDOException::getMessage());
	    $qCekTot2->setFetchMode(PDO::FETCH_NUM);
		$rCekTot2=owlBaris($qCekTot2);
		if($rCekTot2==0 or $rCekTot2==0)
		{
			echo"warning:BAPP Belum Ada Realisasi";
			exit();
		}
		else
		{
			$sUp="update  ".$dbname.".log_spkht set posting='1' where notransaksi='".$noTrans."' and kodeorg='".$kodeorganisasi."'";
			try{
				$owlPDO->exec($sUp); 
				$sUpBaspk="update ".$dbname.".log_baspk set posting='1' where notransaksi='".$noTrans."' and kodeblok like '".$kodeorganisasi."%'";
				try{
					$owlPDO->exec($sUpBaspk); 
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";  
					$sUp="update  ".$dbname.".log_spkht set posting='0' where notransaksi='".$noTrans."' and kodeorg='".$kodeorganisasi."'";
					try{$owlPDO->exec($sUp); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				}
			}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}

		}
	}
	else
	{
		echo"warning:Sudah Terposting";
		exit();
	}
	break;
	default:
	break;
}
?>
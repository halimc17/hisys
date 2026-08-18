<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	include('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	
if(isset($_POST['kdorg'])){
	$kodeorg=trim($_POST['kdorg']);
	if($_POST['kdorg']=='')
	{
		echo "warning : Kode Organisasi harus diisi";
		exit();
	}
	else
	{
		$tgl=  date('Ymd');
		$bln = substr($tgl,4,2);
		$thn = substr($tgl,0,4);
	
                        $nopp="/".date('Y')."/PP/".$kodeorg;
			
			$ql="select `nopp` from ".$dbname.".`log_prapoht` where nopp like '%".$nopp."%' order by `nopp` desc limit 0,1";
			$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qr->setFetchMode(PDO::FETCH_OBJ);
			$rp=$qr->fetch();

			@$awal=substr($rp->nopp,0,3);
			@$awal=intval($awal);
			@$cekbln=substr($rp->nopp,4,2);
			@$cekthn=substr($rp->nopp,7,4);
			
			//if(($bln!=$cekbln)&&($thn!=$cekthn))
			if($thn!=$cekthn)
			{
			//echo $awal; exit();
				$awal=1;
			}
			else
			{
				$awal++;
			}
			$counter=addZero($awal,3);
			$nopp=$counter."/".$bln."/".$thn."/PP/".$kodeorg;
			echo $nopp;
		}
	
	}
		
?>
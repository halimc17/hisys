<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');

	$kodeorg	  =checkPostGet('kodeorg','');
        $tanggal	  =tanggalsystem(checkPostGet('tanggal',''));
	$sisatbskemarin=checkPostGet('sisatbskemarin','');
	$tbsmasuk     =checkPostGet('tbsmasuk','');
	$tbsdiolah    =checkPostGet('tbsdiolah','');
	$sisahariini  =checkPostGet('sisahariini','');
	
	$oer     	  =checkPostGet('oer','');
	$kadarair     =checkPostGet('kadarair','');
	$ffa     	  =checkPostGet('ffa','');
	$dirt     	  =checkPostGet('dirt','');

	$oerpk     	  =checkPostGet('oerpk','');
	$kadarairpk   =checkPostGet('kadarairpk','');
	$ffapk     	  =checkPostGet('ffapk','');
	$dirtpk       =checkPostGet('dirtpk','');

        $usbbefore     	  =checkPostGet('usbbefore','');
        $usbafter     	  =checkPostGet('usbafter','');
        $oildiluted       =checkPostGet('oildiluted','');
        $oilin    	  =checkPostGet('oilin','');
        $oilinheavy    	  =checkPostGet('oilinheavy','');
        $caco     	  =checkPostGet('caco','');
   
        //cpo loses
        $fruitineb     	  =checkPostGet('fruitineb','');
        $ebstalk     	  =checkPostGet('ebstalk','');
        $fibre            =checkPostGet('fibre','');
        $nut    	  =checkPostGet('nut','');
        $effluent    	  =checkPostGet('effluent','');
        $soliddecanter    =checkPostGet('soliddecanter','');
    

        //kernel loses
        $fruitinebker     =checkPostGet('fruitinebker','');
        $cyclone    	  =checkPostGet('cyclone','');
        $claybath   	  =checkPostGet('claybath','');
        $ltds             =checkPostGet('ltds','');
        $usbcpo           =checkPostGet('usbcpo','');//digunakan utk field Dobi
        $usbpk            =checkPostGet('usbpk','');
        $hydrocyclone         =checkPostGet('hydrocyclone','');//digunakan utk field Centrifuge
        
     
		$lorirestanhi           =checkPostGet('lorirestanhi','');
		$cangkang           =checkPostGet('cangkang','');
		$condensatesterilizer           =checkPostGet('condensatesterilizer','');
		
		$sisatbskemarinnetto           =checkPostGet('sisatbskemarinnetto','');
		$tbsmasuknetto           =checkPostGet('tbsmasuknetto','');
		$tbsdiolahnetto           =checkPostGet('tbsdiolahnetto','');
		$sisanetto           =checkPostGet('sisanetto','');
		
		
	
	if(isset($_POST['del']))
	  {
			$strx="delete from ".$dbname.".pabrik_produksi 
			       where kodeorg='".$kodeorg."' 
				   and tanggal='".$_POST['tanggal']."'";   
				   
				   
				   
	  }
	  else
	  {
            $sisahariini=($sisatbskemarin+$tbsmasuk)-$tbsdiolah;
            if($sisahariini<0){
                exit('warning: TBS Di Olah > TBS Tersedia');
            }
			$strx="insert into ".$dbname.".pabrik_produksi
                   (kodeorg,tanggal,sisatbskemarin,
				    tbsmasuk,tbsdiolah,sisahariini,
				    oer,ffa,kadarair,kadarkotoran,
					oerpk,ffapk,kadarairpk,kadarkotoranpk,
					karyawanid,fruitineb, ebstalk, fibre, nut, 
                                        effluent, soliddecanter, fruitinebker, cyclone, 
                                        ltds, claybath, usbbefore, usbafter, oildiluted, oilin, 
                                        oilinheavy, caco,dobi,usbpk,hydrocyclone,
										lorirestanhi,cangkang,condensatesterilizer,
										tbsmasuknetto,tbsdiolahnetto,
										sisatbskemarinnetto,sisahariininetto)
					values('".$kodeorg."',".$tanggal.",".$sisatbskemarin.",
					".$tbsmasuk.",".$tbsdiolah.",".$sisahariini.",
					".$oer.",".$ffa.",".$kadarair.",".$dirt.",
					".$oerpk.",".$ffapk.",".$kadarairpk.",".$dirtpk.",
					".$_SESSION['standard']['userid'].",".$fruitineb.",".$ebstalk.",
                                        ".$fibre.",".$nut.",".$effluent.",".$soliddecanter.",".$fruitinebker.",".$cyclone.",
                                        ".$ltds.",".$claybath.",".$usbbefore.",".$usbafter.",
                                        ".$oildiluted.",".$oilin.",".$oilinheavy.",".$caco.",".$usbcpo.",".$usbpk.",".$hydrocyclone.",
										".$lorirestanhi.",".$cangkang.",".$condensatesterilizer.",
										'".$tbsmasuknetto."','".$tbsdiolahnetto."',
										'".$sisatbskemarinnetto."','".$sisanetto."')";
	  }
	  
try
{
    $owlPDO->exec($strx);
	
	
	if(isset($_POST['del'])){
		#delete tbs olah di pabrik_pengolahan
		#update pengolahan
			$strupd=" update ".$dbname.".pabrik_pengolahan set tbsdiolah='0'
					where kodeorg='".$kodeorg."' and tanggal='".$_POST['tanggal']."'";   
			try{
				$owlPDO->exec($strupd);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			} 
	}else{
		#update pabrik_pengolahan
		//ambil seluruh lori dulu
		#ambil olah hr ini
		$str="select sum(jumlahlori+lorirestan) as tlori,sum(jumlahlori) as lori,sum(lorirestan) as lorirestan 
					from ".$dbname.".pabrik_pengolahan where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tlori=$bar['tlori'];	
			$lori=$bar['lori'];	
			$lorirestan=$bar['lorirestan'];
			@$kglori=$tbsdiolah/$lori;
		
		$str="select * from ".$dbname.".pabrik_pengolahan where kodeorg='".$kodeorg."' and tanggal='".$tanggal."' "; 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			@$tbsolahshift=$kglori*$bar['jumlahlori'];
			
			#update pengolahan
			$strupd=" update ".$dbname.".pabrik_pengolahan set tbsdiolah='".$tbsolahshift."'
					where nopengolahan='".$bar['nopengolahan']."' ";
			try{
				$owlPDO->exec($strupd);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			} 
			 
		 }
		
		
	}	
	
	
	#tutup pabrik pengolahan
	
	
    $str="select a.* from ".$dbname.".pabrik_produksi a where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
			      order by a.tanggal desc limit 20";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
            $tCpoLoses=$bar->fruitineb+$bar->ebstalk+$bar->fibre+$bar->nut+$bar->effluent;
			//+$bar->soliddecanter+$bar->hydrocyclone
            $tKernelLoses=($bar->fruitinebker+$bar->cyclone+$bar->ltds+$bar->claybath);
                //$drcl="onclick=\"previewDetail('".$bar->tanggal."','".$bar->kodeorg."',event);\" style='cursor:pointer'";
				 $drcl='';
                echo"<tr class=rowcontent >
                <td ".$drcl.">".$bar->kodeorg."</td>
                <td ".$drcl.">".tanggalnormal($bar->tanggal)."</td>
                <td ".$drcl." align=right>".number_format($bar->sisatbskemarin,0,'.',',')."</td>
                <td ".$drcl." align=right>".number_format($bar->tbsmasuk,0,'.',',')."</td>
                <td ".$drcl." align=right>".number_format($bar->tbsdiolah,0,'.',',')."</td>
                <td ".$drcl." align=right>".number_format($bar->sisahariini,0,'.',',')."</td>

                <td ".$drcl." align=right>".number_format($bar->oer,2,'.',',')."</td>
                <td ".$drcl." align=right>".(@number_format($bar->oer/$bar->tbsdiolah*100,2,'.',','))."</td>
                <td ".$drcl." align=right>".$bar->ffa."</td>
                <td ".$drcl." align=right>".$bar->kadarkotoran."</td>
                <td ".$drcl." align=right>".$bar->kadarair."</td>
                <td ".$drcl." align=right>".$bar->dobi."</td>
                    


                <td ".$drcl." align=right>".number_format($bar->oerpk,2,'.',',')."</td>
                <td ".$drcl." align=right>".(@number_format(@$bar->oerpk/$bar->tbsdiolah*100,2,'.',','))."</td>
                <td ".$drcl." align=right>".$bar->ffapk."</td>
                <td ".$drcl." align=right>".$bar->kadarkotoranpk."</td>
                <td ".$drcl." align=right>".$bar->kadarairpk."</td>
                   
   <td>";
   #ambil periode akuntansi
	$sAkuntansi="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."' and periode='".substr($bar->tanggal,0,7)."' ";
	$rAkuntansi=$owlPDO->query($sAkuntansi) or die(print " Gagal: ".PDOException::getMessage());
	$rAkuntansi->setFetchMode(PDO::FETCH_ASSOC);
	$bakuntansi=$rAkuntansi->fetch();
	$tutupbuku=$bakuntansi['tutupbuku'];

	if($tutupbuku==0){
    echo"<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar->kodeorg."','".$bar->tanggal."','".$bar->sisatbskemarin."'
			   ,'".$bar->tbsmasuk."','".$bar->tbsdiolah."','".$bar->sisahariini."','".$bar->oer."','".$bar->ffa."','".$bar->kadarkotoran."','".$bar->kadarair."','".$bar->oerpk."',
			   '".$bar->ffapk."','".$bar->kadarkotoranpk."','".$bar->kadarairpk."','".$bar->dobi."',
			   '".$bar->usbbefore."','".$bar->usbafter."','".$bar->oildiluted."','".$bar->oilin."','".$bar->oilinheavy."','".$bar->caco."',
			   '".$bar->hydrocyclone."','".$bar->fruitineb."','".$bar->ebstalk."','".$bar->fibre."','".$bar->nut."','".$bar->effluent."','".$bar->soliddecanter."',
			    '".$bar->fruitinebker."','".$bar->cyclone."','".$bar->ltds."','".$bar->claybath."','".$bar->lorirestanhi."','".$bar->cangkang."','".$bar->condensatesterilizer."',
				'".$bar->tbsmasuknetto."','".$bar->tbsdiolahnetto."','".$bar->sisatbskemarinnetto."','".$bar->sisahariininetto."');\">	
     <img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delProduksi('".$bar->kodeorg."','".$bar->tanggal."','".(isset($bar->kodebarang)? $bar->kodebarang: '')."');\">";
   }
   echo"</td>
  </tr>";	/*
  
  
                    <td ".$drcl." align=right>".$tCpoLoses."</td> <td ".$drcl." align=right>".$tKernelLoses."</td>
  */
			}
}
catch (PDOException $e) 
{
    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
    die(); 
}        
          
	
?>

<?php
ini_set('display_errors',0);
error_reporting(0);

session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');	
$thnbudget=checkPostGet('thnbudget','');
$method=checkPostGet('method','');
$kdblok=checkPostGet('kdblok','');



$jjg=checkPostGet('jjg','');
$pokprod=checkPostGet('pokprod','');
$bjr=checkPostGet('bjr','');
$total=checkPostGet('total','');
$totbrtthn=checkPostGet('totbrtthn','');
$totCol=checkPostGet('totCol','');
$totRow=checkPostGet('totRow','');
$kgsetahun=checkPostGet('kgsetahun','');
$thnclose=checkPostGet('thnclose','');
$lkstgs=checkPostGet('lkstgs','');

$thnttp=checkPostGet('thnttp','');
$thnbudgetHeader=checkPostGet('thnbudgetHeader','');
$kodeblokHeader=checkPostGet('kodeblokHeader','');
$thnsave=checkPostGet('thnsave','');



$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$arrBln=array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sep","10"=>"Okt","11"=>"Nov","12"=>"Des");
$where="tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."'";

switch($method)
{
        //gabung
        case 'pokok':
                        $pokok="select pokokproduksi,thntnm,hathnini from ".$dbname.".bgt_blok WHERE kodeblok='".$kdblok."' and tahunbudget='".$thnbudget."'";	
                        $qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
                        $qOpt->setFetchMode(PDO::FETCH_ASSOC);           
                        $rOpt=$qOpt->fetch();

                        $pokok2="select bjr,thntanam from ".$dbname.".bgt_bjr WHERE 
                                        kodeorg='".substr($kdblok,0,4)."' and thntanam='".$rOpt['thntnm']."' and tahunbudget='".$thnbudget."'";
                        $qOpt2=$owlPDO->query($pokok2) or die(print " Gagal: ".PDOException::getMessage());
                        $qOpt2->setFetchMode(PDO::FETCH_ASSOC);           
                        $rOp2t=$qOpt2->fetch();
                        echo $rOpt['pokokproduksi']."###".$rOp2t['bjr']."###".$rOpt['thntnm']."###".$rOpt['hathnini'];
        break;


        case 'getthn':
                        $optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sql = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 order by tahunbudget desc";
                        $qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                        $qry->setFetchMode(PDO::FETCH_ASSOC);     
                        while ($data=$qry->fetch())
                                        {
                                        $optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
                                        }
                        echo $optthnttp;
        break;

        case'saveData':
                $total = str_replace(',','',$total);
                if(($bjr=='')||($bjr==0))
                {
                        exit("Error:FFB avg(BJR) required");
                }
                $totalSum=0;
                for($a=1;$a<=$totRow;$a++)
                {
                        if($_POST['arrBrt'][$a]=='')
                        {
                                $_POST['arrBrt'][$a]=0;
                        }
                        $totalSum+=$_POST['arrBrt'][$a];
                }
				$selisih=$totalSum-$total;
				if($selisih>1)
                {
                        exit("Error : Monthly total (".$totalSum.") greater than total a year (".$total.") ");
                }
                $sCek="select distinct * from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."'";
                $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_ASSOC);  
                $numrows=owlBaris($qCek);
                $rCek=$numrows;
                if($rCek<1)
                {
                        $sInsert="insert into ".$dbname.".bgt_produksi_kebun (tahunbudget, kodeunit, kodeblok, jjgperpkk, updateby, jjg01, jjg02, jjg03, jjg04, jjg05, jjg06, jjg07, jjg08, jjg09, jjg10, jjg11, jjg12)";
                        $sInsert.=" values ('".$thnbudget."','".$_SESSION['empl']['lokasitugas']."','".$kdblok."','".$jjg."','".$_SESSION['standard']['userid']."'";
                        for($arb=1;$arb<=$totRow;$arb++)
                        {
                                $sInsert.=",'".$_POST['arrBrt'][$arb]."'";
                        }
                        $sInsert.=")";
                        try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }
                else
                {
                        exit("Error: Data already exist");
                }
    break;


    case'loadData':
                $tmbh=$tmbhA=$tmbhsimpan='';
                if($thnbudgetHeader!='')
                {
                        $tmbh=" and tahunbudget='".$thnbudgetHeader."' ";
                        $tmbhA=" and a.tahunbudget='".$thnbudgetHeader."' ";
                }

                $tmbh2=$tmbh2A='';
                if($kodeblokHeader!='')
                {
                        $tmbh2=" and kodeblok='".$kodeblokHeader."' ";
                        $tmbh2A=" and a.kodeblok='".$kodeblokHeader."' ";
                }
                //exit ("Error:$tmbhsimpan");

                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".bgt_produksi_kbn_vw a
                        where kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' ".$tmbh." ".$tmbh2." order by tahunbudget desc, kodeblok asc "; //tahunbudget='".$thnbudget."'
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch()){
                        $jlhbrs= $jsl->jmlhrow;
                }
                $totRowDlm=count($arrBln);
                $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
                $tab.="<thead><tr class=rowheader><td align=center>No</td>";
                $tab.="<td align=center>".$_SESSION['lang']['kodeblok']."</td>"; 
                $tab.="<td align=center width=50>".$_SESSION['lang']['budgetyear']."</td>"; 
                $tab.="<td align=center>".$_SESSION['lang']['thntnm']."</td>";
                $tab.="<td align=center width=50px>".$_SESSION['lang']['pkkproduktif']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['bjr']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['jenjangpokoktahun']."</td>";	
                $tab.="<td align=center  width=50>".$_SESSION['lang']['jjgThn']."</td>";	 

                foreach($arrBln as $brs7=>$dtBln7){
					$tab.="<td  align=center>".$dtBln7."<br>(kg)<br>(Jjg)</td>";
                }
                $tab.="<td align=center  width=50>".$_SESSION['lang']['total']." (kg)</td>";
                $tab.="<td align=center>Aksi</td></tr></thead><tbody>";

                $totSemua=$totbjr=$totpkkprod=$totjpt=0;
                $sList="select *, ((jjgperpkk * hathnini)*1000 / (jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12)) as bjrBgt,
                        (jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjgperthn
                        from ".$dbname.".bgt_produksi_kbn_vw
                        where kodeunit='".$_SESSION['empl']['lokasitugas']."' ".$tmbh." ".$tmbh2." order by tahunbudget desc, kodeblok asc limit ".$offset.",".$limit."";
                $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
                $qList->setFetchMode(PDO::FETCH_ASSOC);
                while($rList=$qList->fetch()){
						
					$pokok="select jjgperpkk,tutup from ".$dbname.".bgt_produksi_kebun WHERE kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
					$qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
					$qOpt->setFetchMode(PDO::FETCH_ASSOC);
					$rOpt=$qOpt->fetch();
					$a1=$rOpt['jjgperpkk'];
					$a3=$rList['pokokproduksi'];
					$totala=round($rList['jjgperthn']);
					$rList['bjrBgt'] = round($rList['bjrBgt'],2);
					
					if($rOpt['tutup']==0){
						$rtp="onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodeblok']."','".$rList['pokokproduksi']."','".$rList['bjrBgt']."','".$rOpt['jjgperpkk']."','".$totala."','".$rList['thntnm']."','".$rList['hathnini']."');\" title=\"Edit Data ".$rList['kodeblok']."\" style='cursor:pointer;'";
					}else{
						$rtp="";
					}
					$no+=1;
					$tab.="<tr class=rowcontent >";
					$tab.="<td align=center ".$rtp.">".$no."</td>";
					$tab.="<td align=left ".$rtp.">".$rList['kodeblok']."</td>";
					$tab.="<td align=right ".$rtp.">".$rList['tahunbudget']."</td>";
					$tab.="<td align=right ".$rtp.">".$rList['thntnm']."</td>";
					$tab.="<td align=right ".$rtp.">".$rList['pokokproduksi']."</td>";
					if(!empty($rList['bjrBgt'])) $rList['bjr'] = $rList['bjrBgt'];
					$tab.="<td align=right ".$rtp.">".number_format($rList['bjr'],2)."</td>";
					$tab.="<td align=right ".$rtp.">".number_format($rOpt['jjgperpkk'],2)."</td>";	

					$tab.="<td align=right ".$rtp.">".number_format($totala,0)."</td>";

					$rTotaljjg=$rTotal=array();
					for($a=1;$a<=$totRowDlm;$a++){
						if(strlen($a)=='1'){
								$b="0".$a;
						}else{
								$b=$a;
						}
						if($rList['jjg'.$b]==''){
							$rList['jjg'.$b]=0;
						}
						#ambil kg
						$strkg=" select * from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 and kodeblok='".$rList['kodeblok']."' and tahunbudget = '".$rList['tahunbudget']."'";
						$reskg = fetchdata($strkg);
						
						$tab.="<td align='right' ".$rtp.">".number_format($reskg[0]['kg'.$b],0)."<br>".number_format($rList['jjg'.$b])."</td>";
						setIt($rTotal[$rList['kodeblok']],0);
						$rTotal[$rList['kodeblok']]+=$reskg[0]['kg'.$b];
						@$rTotaljjg[$rList['kodeblok']]+=$rList['jjg'.$b];
					}
					$tab.="<td align=right ".$rtp.">".number_format($rTotal[$rList['kodeblok']],0)."<br>".number_format($rTotaljjg[$rList['kodeblok']],0)."</td>";		
					if($rOpt['tutup']==0){
						$tab.="<td align='center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit' ".$rtp.">
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$rList['tahunbudget']."','".$rList['kodeblok']."');\">
					   </td>";
					}else{
						$tab.="<td>".$_SESSION['lang']['tutup']."</td>";
					}
					$tab.="</tr>";
					//total sebaran perbulan (harus dalam while)	
					$a=array("1"=>"jjg01","2"=>"jjg02","3"=>"jjg03","4"=>"jjg04","5"=>"jjg05","6"=>"jjg06","7"=>"jjg07","8"=>"jjg08","9"=>"jjg09","10"=>"jjg10","11"=>"jjg11","12"=>"jjg12");
					$n=array("1"=>"kg01","2"=>"kg02","3"=>"kg03","4"=>"kg04","5"=>"kg05","6"=>"kg06","7"=>"kg07","8"=>"kg08","9"=>"kg09","10"=>"kg10","11"=>"kg11","12"=>"kg12");
					for($i=1;$i<=12;$i++){
						if(strlen($i)=='1'){
							$b="0".$i;
						}else{
							$b=$i;
						}	
							#ambil kg
							$strkg=" select * from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 and kodeblok='".$rList['kodeblok']."' and tahunbudget = '".$rList['tahunbudget']."'";
							$reskg = fetchdata($strkg);
							setIt($hasilkg['kg'.$b],0);
							$hasilkg['kg'.$b]+=$reskg[0]['kg'.$b];
							
							$totseb1="select jjg".$b." from ".$dbname.".bgt_produksi_kbn_vw where kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
							$totseb2=$owlPDO->query($totseb1) or die(print " Gagal: ".PDOException::getMessage());
							$totseb2->setFetchMode(PDO::FETCH_ASSOC);    
							$totseb3=$totseb2->fetch();
							setIt($hasil['jjg'.$b],0);
							$hasil['jjg'.$b]+=$totseb3['jjg'.$b];
						}
						$totSemua+=$totala;
						$totbjr+=$rList['bjr'];
						$totpkkprod+=$rList['pokokproduksi'];
						$totjpt+=$rOpt['jjgperpkk'];
                }//tutup while

#================================================== TOTAL DATA ======================================================================
				$tab.="<thead><tr class=rowheader><td align=center colspan=4>".$_SESSION['lang']['total']."</td>";
				$tab.="<td align=right>".number_format($totpkkprod,0)."</td>";
				$tab.="<td align=right>&nbsp</td>";
				$tab.="<td align=right>".number_format($totjpt,0)."</td>";
				$tab.="<td align=right>".number_format($totSemua,0)."</td>";
				$ttljjg=$ttlkg=0;
				for($i=1;$i<=12;$i++){
					$tab.="<td align=right>".number_format($hasilkg[$n[$i]],0)."<br>".number_format($hasil[$a[$i]],0)."</td>";
					$ttlkg+=$hasilkg[$n[$i]];
					$ttljjg+=$hasil[$a[$i]];
					
				}
                $tab.="<td colspan=1 align=right>".number_format($ttlkg,0)."<br>".number_format($ttljjg,0)."</td>";
                $tab.="<td colspan=1>&nbsp;</td>";
				$tab.="</tr></thead>";

                $spnCol=$totRowDlm+10;
                $tab.="
					<tr><td colspan='".$spnCol."' align=center><br />
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr>"; 
                $tab.="</tbody></table>";

                echo $tab;
        break;


        //case 'delete=======================================================================================================
        case 'delete':

                $tab="delete from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok ='".$kdblok."' ";
                try{$owlPDO->exec($tab); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
        break;

         case'getBlok':
            $optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $sVhc="select distinct kodeblok,thntnm from ".$dbname.".bgt_blok where tahunbudget='".$thnbudget."' and kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' and closed=1";
            $qVhc=$owlPDO->query($sVhc) or die(print " Gagal: ".PDOException::getMessage());
            $qVhc->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=owlBaris($qVhc);
            $brs=$numrows;
            if($brs>0){
				while($rVhc=$qVhc->fetch()){
					if($kdblok!=''){
						$optBlok.="<option value='".$rVhc['kodeblok']."' ".($kdblok==$rVhc['kodeblok']?'selected':'').">".$rVhc['kodeblok']." [".$rVhc['thntnm']."]</option>";
					}else{
						$optBlok.="<option value='".$rVhc['kodeblok']."'>".$rVhc['kodeblok']." [".$rVhc['thntnm']."]</option>";
					}
				}
				echo $optBlok;
            }else{
                exit("Error : Block for budget not set(close) yet");
            }
        break;
        //case edit==========================================================================================================
        case 'update':
		
		// Input Budget BJR
		$optThnTnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$kdblok."'");
		$dataUpd = array(
				'bjr' => $bjr,
				'updateby' => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
		);
		$whereUpd = "tahunbudget='".$thnbudget."' and kodeorg='".$kdblok.
		"' and thntanam='".$optThnTnm[$kdblok]."'";
		$updBjr = updateQuery($dbname,'bgt_bjr',$dataUpd,$whereUpd);
		try{$owlPDO->exec($updBjr); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
            for($a=1;$a<=$totRow;$a++)
        {
                        if($_POST['arrBrt'][$a]=='')
            {
                $_POST['arrBrt'][$a]=0;
            }
        $totalSum+=$_POST['arrBrt'][$a];
        }
		
		$selisih=$totalSum-$total;
		
        if($selisih>1)
        {
            exit("Error : Monthly total (".$totalSum.") greater than total a year(".$total.") ");
                }
                $sUpdate="update ".$dbname.".bgt_produksi_kebun set updateby='".$_SESSION['standard']['userid']."',jjgperpkk='".$jjg."'";
                // exit("Error".$sUpdate);
                for($a=1;$a<=$totRow;$a++)
                {
                        if(strlen($a)=='1')
                        {
                                $c="0".$a;
                        }
                        else
                        {
                                $c=$a;
                        }

                         $sUpdate.=" ,jjg".$c."='".$_POST['arrBrt'][$a]."'";
                }
                $sUpdate.=" where  ".$where."";
                try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
    break;

        //case get data==========================================================================================================
        case'getData':
			$totBrs=count($arrBln);
			$pokok="select * from ".$dbname.".bgt_produksi_kbn_vw WHERE tahunbudget='".$thnbudget."' order by lastupdate desc limit 1";
			$qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
			$qOpt->setFetchMode(PDO::FETCH_ASSOC); 
			
			$numrows=owlBaris($qOpt);
			$rRow=$numrows;
			$isi='';
			if($rRow>0){
				if(isset($_POST['statInputan']) and $_POST['statInputan']==1){
					$sTot="select distinct pokokproduksi,jjgperpkk from ".$dbname.".bgt_produksi_kbn_vw where kodeblok='".$kdblok."' and tahunbudget='".$thnbudget."'";
					$qTot=$owlPDO->query($sTot) or die(print " Gagal: ".PDOException::getMessage());
					$qTot->setFetchMode(PDO::FETCH_ASSOC); 
					$rRes=$qTot->fetch();

					$a3=$rRes['pokokproduksi'];
					$a1=$rRes['jjgperpkk'];
					#$total=$a1*$a3;
					
					$pokok="select * from ".$dbname.".bgt_produksi_kbn_vw WHERE tahunbudget='".$thnbudget."' and kodeblok like '".substr($kdblok,0,6)."%' order by lastupdate desc limit 1";
					$qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
					$qOpt->setFetchMode(PDO::FETCH_ASSOC); 
					$rOpt=$qOpt->fetch();
					$ttljjgsave = ($rOpt['jjg01']+$rOpt['jjg02']+$rOpt['jjg03']+$rOpt['jjg04']+$rOpt['jjg05']+$rOpt['jjg06']+$rOpt['jjg07']+$rOpt['jjg08']+$rOpt['jjg09']+$rOpt['jjg10']+$rOpt['jjg11']+$rOpt['jjg12']);
					
					$isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']." / ".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
					$isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
					$isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right id='hasilPerkalian'>".number_format($total)."</td></tr></thead><tbody>";
					for($bre=1;$bre<=$totBrs;$bre++){
						if(strlen($bre)<2){
							$abe="0".$bre;	
						}else{
							$abe=$bre;
						}
						
						
						$hslDr=($rOpt['jjg'.$abe]/$ttljjgsave)*100;
						$jjgsebar=$hslDr*$total/100;
						
						$isi.="<tr class=rowcontent><td>".$arrBln[$bre]."</td>
						<td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$bre." onblur=ubahNilai(this.value,'".$total."','brt_x') value='".number_format($hslDr,5)."' /></td>";
						$isi.="<td><input type='text' id=brt_x".$bre." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$jjgsebar." /></td>
						</tr>";
					}
				}else{	
					$pokok="select * from ".$dbname.".bgt_produksi_kbn_vw WHERE tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."'";
					$qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
					$qOpt->setFetchMode(PDO::FETCH_ASSOC); 
					$rOpt=$qOpt->fetch();
					$ttljjgsave = ($rOpt['jjg01']+$rOpt['jjg02']+$rOpt['jjg03']+$rOpt['jjg04']+$rOpt['jjg05']+$rOpt['jjg06']+$rOpt['jjg07']+$rOpt['jjg08']+$rOpt['jjg09']+$rOpt['jjg10']+$rOpt['jjg11']+$rOpt['jjg12']);
					
					
					$isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']."/".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
					$isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
					$isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right>".number_format($total)."</td></tr></thead><tbody>";
					foreach($arrBln as $brs2=>$dtBln2){
						
						if(strlen($brs2)<2){
							$abe="0".$brs2;	
						}else{
							$abe=$brs2;
						}
						
						@$bagi2=($rOpt['jjg'.$abe]/$ttljjgsave);
						@$bagi=($rOpt['jjg'.$abe]);
						
						$isi.="<tr class=rowcontent><td>".$dtBln2."</td>
						<td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$brs2." onblur=ubahNilai(this.value,'".$total."','brt_x') value=".number_format((($bagi2)*100),5,'.','')."></td>";
						$isi.="<td><input type='text' id=brt_x".$brs2." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$bagi." /></td>
						</tr>";
					}
				}

			}else{	
			
			$rOpt=$qOpt->fetch();
			#$total=$rOpt['ttl'];
			$isi.="<fieldset style='width:200px;'><legend>".$_SESSION['lang']['sebaran']."/".$_SESSION['lang']['bulan']." :".$kdblok."</legend>";
			$isi.="<table cellspacing=1 cellpadding=1 border=0 class=sortable align=center><thead>";	
			$isi.="<tr class=rowheader><td>".$_SESSION['lang']['total']." (Jjg)</td><td align=center>%</td><td align=right>".number_format($total)."</td></tr></thead><tbody>";
				foreach($arrBln as $brs2=>$dtBln2){
					if(strlen($brs2)<2){
						$abe="0".$brs2;	
					}else{
						$abe=$brs2;
					}
					
					@$bagi=($total/12);
					@$bagi2=(100/12);
					$isi.="<tr class=rowcontent><td>".$dtBln2."</td>
					<td><input type=text class=myinputtextnumber size=3 onkeypress=\"return angka_doang(event);\" id=persenPrdksi".$brs2." onblur=ubahNilai(this.value,'".$total."','brt_x') value=".number_format((($bagi2)),5)."></td>";
					$isi.="<td><input type='text' id=brt_x".$brs2." class=\"myinputtextnumber\" style=\"width:75px;\" value=".$bagi."></td>
					</tr>";
				}
								//$isi.="<td><input type='text' class='myinputtextnumber'  id=brt_x".$brs2." value='".$bagi."' style='width:50px' onkeypress=\"return angka_doang(event);\" /></td>";
			   }
                $isi.="<tr class=rowcontent><td  colspan=3 align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveBrt(".$totBrs.")\" src='images/save.png'/>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearForm()\" src='images/clear.png'/></td>";
                $isi.="</tr></tbody></table></fieldset>";
                //$isi.="<td align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"saveBrt(".$totBrs.")\" src='images/save.png'/></td></tr>";
                echo $isi;
        break;

        //case close==========================================================================================================
        case'closeBudget':
                $sQl="select distinct tutup from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnttp."' and kodeunit='".$lkstgs."' and tutup=1 ";
                $qQl=$owlPDO->query($sQl) or die(print " Gagal: ".PDOException::getMessage());
                $qQl->setFetchMode(PDO::FETCH_ASSOC);
                $numrows=owlBaris($qQl);
                $row=$numrows;
                if($row!=1)
                {
                        $sUpdate="update ".$dbname.".bgt_produksi_kebun set tutup=1 where tahunbudget='".$thnttp."' and kodeunit='".$lkstgs."'  ";
                        try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }
                else
                {
                        exit("Error: Budget for this period has been closed");
                }
                break;
        case 'cek':

                ##UNTUK VALIDASI DATA YANG UDAH DI TUTUP GK BISA INSERT LAGI
                $aCek="select distinct tutup from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeunit='".$_SESSION['empl']['lokasitugas']."' ";
                $bCek=$owlPDO->query($aCek) or die(print " Gagal: ".PDOException::getMessage());
                $bCek->setFetchMode(PDO::FETCH_ASSOC);
                while ($cCek=$bCek->fetch())
                {
                        //exit("error:$aCek");
                        if($cCek['tutup']==1)
                        {
                                echo "warning : Budget for this period has been closed, coud not proceed";
                                exit();	
                        }
                }

                ##UNTUK VALIDASI DATA DI BGT BLOK ADA APA TIDAK UNTUK THN TANAM DAN KODE BLOKNY
                $xCek="select tahunbudget,kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."' ";
                $yCek=$owlPDO->query($xCek) or die(print " Gagal: ".PDOException::getMessage());
                $yCek->setFetchMode(PDO::FETCH_ASSOC);
                $ada=false;
                while($zCek=$yCek->fetch())
                {
                        $ada=true;
                }
                if ($ada==false)
                {
                        echo "warning : Budget year ".$thnbudget." or block code ".$kdblok." not listed on block budget (Anggaran->Transaksi->Kebun->Blok Anggaran) ";
                        exit();	
                }

                ##UNTUK VALIDASI DATA DI BGT BLOK SUDAH ADA APA BELON
                $xCek="select tahunbudget,kodeblok from ".$dbname.".bgt_produksi_kebun where tahunbudget='".$thnbudget."' and kodeblok='".$kdblok."' ";
                $yCek=$owlPDO->query($xCek) or die(print " Gagal: ".PDOException::getMessage());
                $yCek->setFetchMode(PDO::FETCH_ASSOC);
                $ada=false;
                while($zCek=$yCek->fetch())
                {
                        $ada=true;
                }
                if ($ada==true)
                {
                        echo "warning : data already exist ";
                        exit();	
                }

                                // Input Budget BJR
                                $optThnTnm = makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$kdblok."'");
                                $dataIns = array(
                                        'tahunbudget' => $thnbudget,
                                        'kodeorg' => $kdblok,
                                        'thntanam' => $optThnTnm[$kdblok],
                                        'bjr' => $bjr,
                                        'updateby' => $_SESSION['standard']['userid'],
                                        'lastupdate' => date('Y-m-d H:i:s'),
                                        'close' => 0
                                );
                                $dataUpd = array(
                                        'bjr' => $bjr,
                                        'updateby' => $_SESSION['standard']['userid'],
                                        'lastupdate' => date('Y-m-d H:i:s')
                                );
                                $whereUpd = "tahunbudget='".$thnbudget."' and kodeorg='".$kdblok.
                                "' and thntanam='".$optThnTnm[$kdblok]."'";
                                $insBjr = insertQuery($dbname,'bgt_bjr',$dataIns);
                                $updBjr = updateQuery($dbname,'bgt_bjr',$dataUpd,$whereUpd);
                                try{$owlPDO->exec($insBjr); }
                                catch (PDOException $e) {
                                    try{$owlPDO->exec($updBjr); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                                }
        break;

        case 'getkodeblokHeader':
                $optKodeBlokHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
                $sThn = "SELECT distinct kodeblok FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' order by kodeblok";
                $qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
                $qThn->setFetchMode(PDO::FETCH_ASSOC);
                while($rThn=$qThn->fetch())
                {
                        $optKodeBlokHeader.="<option value='".$rThn['kodeblok']."'>".$rThn['kodeblok']."</option>";
                }
                echo $optKodeBlokHeader;
        break;

        case 'getthnbudgetHeader':
                //$bjr="select bjr from ".$dbname.".bgt_bjr WHERE kodeorg='".substr($kdblok,0,4)."'";
                $optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sThn = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' order by tahunbudget desc";
                $qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
                $qThn->setFetchMode(PDO::FETCH_ASSOC);
                while($rThn=$qThn->fetch())
                {
                        $optTahunBudgetHeader.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
                }
                echo $optTahunBudgetHeader;
        break;
        case 'getThn':
                $optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sql = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 order by tahunbudget desc";
                $qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $qry->setFetchMode(PDO::FETCH_ASSOC);
                while ($data=$qry->fetch())
                        {
                        $optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
                        }
                echo $optthnttp;
        break;


        case 'getOrg':
                $optorgclose="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sql = "SELECT distinct kodeunit FROM ".$dbname.".bgt_produksi_kebun where kodeunit like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 ";
                $qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $qry->setFetchMode(PDO::FETCH_ASSOC);
                while ($data=$qry->fetch())
                        {
                        $optorgclose.="<option value=".$data['kodeunit'].">".$optNm[$data['kodeunit']]."</option>";
                        }
                echo $optorgclose;
        break;

        case'carikebun':
        if(isset($_POST['kebun']))
                {
                        $txt_search=$_POST['kebun'];
                }
                else
                {
                        $txt_search='';		
                }
                        if($txt_search!='')
                        {
                                $where=" kodeblok LIKE  '%".$txt_search."%'";
                        }
                        elseif($txt_tgl!='')
                        {
                                $where.=" tanggal LIKE '".$txt_tgl."'";
                        }
                        elseif(($txt_tgl!='')&&($txt_search!=''))
                        {
                                $where.=" notransaksi LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'";
                        }
                //echo $strx; exit();
                if($txt_search==''&&$txt_tgl=='')
                {
                        $strx="select * from ".$dbname.".vhc_penggantianht where  ".$where." order by tanggal desc";

                }
                else
                {
                                $strx="select * from ".$dbname.".vhc_penggantianht where   ".$where." order by tanggal desc";
                }

                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2="select count(*) as jmlhrow from ".$dbname.".bgt_produksi_kebun where kodeblok like '%".$_SESSION['empl']['lokasitugas']."%' ".$tmbhsimpan." ".$tmbh." order by kodeblok asc "; //tahunbudget='".$thnbudget."'
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch()){
                        $jlhbrs= $jsl->jmlhrow;
                }
                $totRowDlm=count($arrBln);
                $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
                $tab.="<thead><tr class=rowheader><td width=15 align=center>No</td>";
                $tab.="<td align=center width=90>".$_SESSION['lang']['kodeblok']."</td>"; 
                $tab.="<td align=center width=90>".$_SESSION['lang']['budgetyear']."</td>"; 
                $tab.="<td align=center width=75>".$_SESSION['lang']['thntnm']."</td>";
                $tab.="<td align=center width=100>".$_SESSION['lang']['pkkproduktif']."</td>";
                $tab.="<td align=center width=50>".$_SESSION['lang']['bjr']."</td>";
                $tab.="<td align=center width=150>".$_SESSION['lang']['jenjangpokoktahun']."</td>";	
                $tab.="<td align=center  width=50>".$_SESSION['lang']['kgThn']."</td>";	 
                foreach($arrBln as $brs7=>$dtBln7)
                {
                        $tab.="<td  width=45 align=center>".$dtBln7."</td>";
                }
                $tab.="<td align=center>Aksi</td></tr></thead><tbody>";	

                $sList="select * from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeunit='".$_SESSION['empl']['lokasitugas']."' ".$tmbhsimpan." ".$tmbh."  order by kodeblok asc limit ".$offset.",".$limit."";
                $qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
                $qList->setFetchMode(PDO::FETCH_ASSOC);
                while($rList=$qList->fetch())
                {
                    $pokok="select jjgperpkk,tutup from ".$dbname.".bgt_produksi_kebun WHERE kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
                    $qOpt=$owlPDO->query($pokok) or die(print " Gagal: ".PDOException::getMessage());
                    $qOpt->setFetchMode(PDO::FETCH_ASSOC);
                    $rOpt=$qOpt->fetch();

                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=left>".$rList['kodeblok']."</td>";
                    $tab.="<td align=right>".$rList['tahunbudget']."</td>";
                    $tab.="<td align=right>".$rList['thntnm']."</td>";
                    $tab.="<td align=right>".$rList['pokokproduksi']."</td>";
                    $tab.="<td align=right>".$rList['bjr']."</td>";
                    $tab.="<td align=right>".$rOpt['jjgperpkk']."</td>";	
                    $a1=$rOpt['jjgperpkk'];
                    $a3=$rList['pokokproduksi'];
                    $totala=$a1*$a3;																
                    $tab.="<td align=right>".number_format($totala)."</td>";


                    for($a=1;$a<=$totRowDlm;$a++){
                        if(strlen($a)=='1'){
                            $b="0".$a;
                        }else{
                            $b=$a;
                        }
                        if($rList['kg'.$b]==''){
                            $rList['kg'.$b]=0;
                        }
                        $tab.="<td align='right'>".number_format($rList['kg'.$b],2)."</td>";
                    }
                    if($rOpt['tutup']==0){
                     $tab.="<td align='center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodeblok']."','".$rList['pokokproduksi']."','".$rList['bjr']."','".$rOpt['jjgperpkk']."','".$totala."','".$rList['thntnm']."');\">
						
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$rList['tahunbudget']."','".$rList['kodeblok']."');\">
					   </td>";
                    }else{
                        $tab.="<td>".$_SESSION['lang']['tutup']."</td>";
                    }
                    $tab.="</tr>";
                                //total sebaran perbulan (harus dalam while)	
                                $a=array("1"=>"kg01","2"=>"kg02","3"=>"kg03","4"=>"kg04","5"=>"kg05","6"=>"kg06","7"=>"kg07","8"=>"kg08","9"=>"kg09","10"=>"kg10","11"=>"kg11","12"=>"kg12");
                                for($i=1;$i<=12;$i++)
                                {
                                            if(strlen($i)=='1')
                                            {
                                                $b="0".$i;
                                            }
                                            else
                                            {
                                                $b=$i;
                                            }
                                            $totseb1="select kg".$b." from ".$dbname.".bgt_produksi_kbn_kg_vw where kodeblok='".$rList['kodeblok']."' and tahunbudget='".$rList['tahunbudget']."'";
                                            $totseb2=$owlPDO->query($totseb1) or die(print " Gagal: ".PDOException::getMessage());
                                            $totseb2->setFetchMode(PDO::FETCH_NUM);
                                            $totseb3=$totseb2->fetch();
                                            $hasil['kg'.$b]+=$totseb3['kg'.$b];
                                }

                                //UNTUK TOTAL ,, GK DR DB
                                $totSemua+=$totala;
                                $totbjr+=$rList['bjr'];
                                $totpkkprod+=$rList['pokokproduksi'];
                                $totjpt+=$rOpt['jjgperpkk'];

                        }//tutup while

                                //-----------------------------------------------------------------------------------------------

                                $tab.="<thead><tr class=rowheader><td align=center colspan=4>".$_SESSION['lang']['total']."</td>";
                                $tab.="<td align=right>".number_format($totpkkprod)."</td>";
                                $tab.="<td align=right>".number_format($totbjr)."</td>";
                                $tab.="<td align=right>".number_format($totjpt)."</td>";
                                $tab.="<td align=right>".number_format($totSemua)."</td>";
                                for($i=1;$i<=12;$i++)
                                {
                                        $tab.="<td align=right>".number_format($hasil[$a[$i]],2)."</td>";
                                }
                $tab.="<td></td>";
                                $tab.="</tr></thead>";

                $spnCol=$totRowDlm+21;
                $tab.="
                        <tr><td colspan='".$spnCol."' align=center><br />
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>"; 
                $tab.="</tbody></table>";

                echo $tab;
        break;

        default:	
}
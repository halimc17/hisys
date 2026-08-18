<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$thnbudget=checkPostGet('thnbudget','');
$kunci=checkPostGet('kunci','');
$kdunit=checkPostGet('kdunit','');

$totjamthn=checkPostGet('totjamthn','');
$totRow=checkPostGet('totRow','');

$ktbs=checkPostGet('ktbs','');
$kdpks=checkPostGet('kdpks','');
$optkdsup=checkPostGet('optkdsup','');
$kdsup=checkPostGet('kdsup','');
$kgtbs=checkPostGet('kgtbs','');
$oerc=checkPostGet('oerc','');
$oerk=checkPostGet('oerk','');

$thnclose=checkPostGet('thnclose','');
$lkstgs=checkPostGet('lkstgs','');
$thnttp=checkPostGet('thnttp','');
$thnbudgetHeader=checkPostGet('thnbudgetHeader','');

$optNmsupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$optNmsupp['tbsexternal']='TBS External';
$optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


$arrBln=array("1"=>"Kg.".$_SESSION['lang']['tbs'].".Jan","2"=>"Kg.".$_SESSION['lang']['tbs'].".Feb","3"=>"Kg.".$_SESSION['lang']['tbs'].".Mar","4"=>"Kg.".$_SESSION['lang']['tbs'].".Apr","5"=>"Kg.".$_SESSION['lang']['tbs'].".Mei","6"=>"Kg.".$_SESSION['lang']['tbs'].".Jun","7"=>"Kg.".$_SESSION['lang']['tbs'].".Jul","8"=>"Kg.".$_SESSION['lang']['tbs'].".Aug","9"=>"Kg.".$_SESSION['lang']['tbs'].".Sep","10"=>"Kg.".$_SESSION['lang']['tbs'].".Okt","11"=>"Kg.".$_SESSION['lang']['tbs'].".Nov","12"=>"Kg.".$_SESSION['lang']['tbs'].".Des");

switch($method)
{	

        case'getsup':

                echo $optkdsup;

        break;

        case'saveData':
        for($a=1;$a<=$totRow;$a++)
        {
                        if($_POST['arrBrt'][$a]=='')
            {
                $_POST['arrBrt'][$a]=0;
            }
        $totalSum+=$_POST['arrBrt'][$a];
        }

        if($totalSum>$kgtbs)
        {
            exit("Error: Monthly total ".$totalSum." larger than annual :".$kgtbs."");

               }
                $sCek="select distinct * from ".$dbname.".bgt_produksi_pks where kunci='".$kunci."' and tahunbudget='".$thnbudget."' and millcode='".$kdpks."' and kodeunit='".$kdsup."' ";
                $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_OBJ);
                $numrows=owlBaris($qCek);
                $rCek=$numrows;
                if($rCek<1)
        {
                        $sInsert="insert into ".$dbname.".bgt_produksi_pks (tahunbudget, millcode, kodeunit, kgolah, oerbunch, oerkernel, updateby, olah01, olah02, olah03, olah04, olah05, olah06, olah07, olah08, olah09, olah10, olah11, olah12)";
                        $sInsert.=" values ('".$thnbudget."','".$kdpks."','".$kdsup."','".$kgtbs."','".$oerc."','".$oerk."','".$_SESSION['standard']['userid']."'";
                        for($abr=1;$abr<=$totRow;$abr++)
                        {
                                $sInsert.=",'".$_POST['arrBrt'][$abr]."'";
                        }
                        $sInsert.=")";
                        try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }
    break;
        case 'update':
        for($a=1;$a<=$totRow;$a++)
        {
            if($_POST['arrBrt'][$a]=='')
            {
                $_POST['arrBrt'][$a]=0;
            }
        $totalSum+=$_POST['arrBrt'][$a];
        }
        if($totalSum>$kgtbs)
        {
            exit("Error:Distribution incoorect, monthly total larger than annual");
                }
                $sUpdate="update ".$dbname.".bgt_produksi_pks set kgolah='".$kgtbs."',oerbunch='".$oerc."',oerkernel='".$oerk."',updateby='".$_SESSION['standard']['userid']."'";
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

                         $sUpdate.=" ,olah".$c."='".$_POST['arrBrt'][$a]."'";
                }
                $sUpdate.=" where tahunbudget='".$thnbudget."' and millcode='".$kdpks."' and kodeunit='".$kdsup."'";
                try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

    break;

        case'loadData':
			$tmbh='';
			if($thnbudgetHeader!=''){
					$tmbh=" and tahunbudget='".$thnbudgetHeader."' ";
			}

			$limit=10;
			$page=0;
			if(isset($_POST['page'])){
					$page=$_POST['page'];
					if($page<0)
					$page=0;
			}
			$offset=$page*$limit;

			$ql2="select count(*) as jmlhrow from ".$dbname.".bgt_produksi_pks where millcode='".$_SESSION['empl']['lokasitugas']."'  ";
			$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
			$query2->setFetchMode(PDO::FETCH_OBJ);
			while($jsl=$query2->fetch()){
					$jlhbrs= $jsl->jmlhrow;
			}
			$totRowDlm=count($arrBln);

			$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
			$tab.="<thead><tr class=rowheader><td width=25>No</td>";
			$tab.="<td align=center  width=100>".$_SESSION['lang']['budgetyear']."</td>";
			$tab.="<td align=center  width=125>".$_SESSION['lang']['kodesupplier']."</td>"; 
			$tab.="<td align=center  width=75>".$_SESSION['lang']['kgtbs']."</td>";
			$tab.="<td align=center  width=75>".$_SESSION['lang']['oer']." (%)</td>";
			$tab.="<td align=center  width=75>Ker (%)</td>";	
			foreach($arrBln as $brs6=>$dtBln6) {
					$tab.="<td align=center width=50>".$dtBln6."</td>";
			}
			$tab.="<td>Aksi</td></tr></thead><tbody>";	

			$sList="select * from ".$dbname.".bgt_produksi_pks where  millcode='".$_SESSION['empl']['lokasitugas']."' ".$tmbh." order by tahunbudget desc limit ".$offset.",".$limit."";
			$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
			$qList->setFetchMode(PDO::FETCH_ASSOC);
			$kgCPO = $kgKer = 0;
			while($rList=  $qList->fetch()) {
				$no+=1;
				if(strlen($rList['kodeunit'])>4) {
						$dtKdunit=$optNmsupp[$rList['kodeunit']];
						$intex=0;
				} else {
						$sintex="select distinct induk from ".$dbname.".organisasi where kodeorganisasi='".$rList['kodeunit']."'";
						$qIntex=$owlPDO->query($sintex) or die(print " Gagal: ".PDOException::getMessage());
						$qIntex->setFetchMode(PDO::FETCH_ASSOC);
						$rIntex=$qIntex->fetch();
						if($rIntex['induk']==$_SESSION['org']['kodeorganisasi']) {
							$intex=1;
						} else {
							$intex=2;
						}
						$dtKdunit=$optNmOrg[$rList['kodeunit']];
				}
				$kgCPO += $rList['kgolah'] * $rList['oerbunch'] / 100;
				$kgKer += $rList['kgolah'] * $rList['oerkernel'] / 100;
				$rList['tutup']==0?$edDt="onclick=\"fillField('".$rList['tahunbudget']."','".$_SESSION['empl']['lokasitugas']."','".$rList['kodeunit']."','".$rList['kgolah']."','".$rList['oerbunch']."','".$rList['oerkernel']."','".$intex."');\"  title='Edit Data ".$dtKdunit."__".$rList['tahunbudget']."'  style='cursor:pointer;' ":$edDt="";
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center ".$edDt.">".$no."</td>";
				$tab.="<td align=right  ".$edDt.">".$rList['tahunbudget']."</td>";
				$tab.="<td align=left  ".$edDt.">".$dtKdunit."</td>";
				$tab.="<td align=right  ".$edDt.">".number_format($rList['kgolah'],2)."</td>";
				$tab.="<td align=right  ".$edDt.">".number_format($rList['oerbunch'],2)."</td>";
				$tab.="<td align=right  ".$edDt.">".number_format($rList['oerkernel'],2)."</td>";
				for($a=1;$a<=$totRowDlm;$a++) {
					if(strlen($a)=='1') {
							$b="0".$a;
					} else {
							$b=$a;
					} if($rList['olah'.$b]=='') {
							$rList['olah'.$b]=0;
					}
					$tab.="<td align='right' ".$edDt.">".number_format($rList['olah'.$b],2)."</td>";
					
					$totalbulan[$b]+=$rList['olah'.$b];
					
				}


				if($rList['tutup']==0) {
					$tab.="<td align='center'>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"Del('".$rList['kunci']."');\">
						   </td>";
				} else {
						$tab.="<td>".$_SESSION['lang']['tutup']."</td>";
				}
				$tab.="</tr>";
				
				
				
				//-----total (harus dalam array)-----
				setIt($totkgolah,0);
				setIt($totoerb,0);
				setIt($totoerk,0);
				$totkgolah+=$rList['kgolah'];
				$totoerb+=$rList['oerbunch'];
				$totoerk+=$rList['oerkernel'];


				//sebaran bulanan
				/*
				$a=array("1"=>"olah01","2"=>"olah02","3"=>"olah03","4"=>"olah04","5"=>"olah05","6"=>"olah06","7"=>"olah07","8"=>"olah08","9"=>"olah09","10"=>"olah10","11"=>"olah11","12"=>"olah12");
				for($i=1;$i<=12;$i++)
				{
					if(strlen($i)=='1') {
						$b="0".$i;
					} else {
						$b=$i;
					}
					$totseb1="select olah".$b." from ".$dbname.".bgt_produksi_pks where tahunbudget='".$rList['tahunbudget']."' and millcode='".$_SESSION['empl']['lokasitugas']."' and kodeunit='".$rList['kodeunit']."'  ";
					
					$totseb2=$owlPDO->query($totseb1) or die(print " Gagal: ".PDOException::getMessage());
					$totseb2->setFetchMode(PDO::FETCH_NUM);
					$totseb3=$totseb2->fetch();
					setIt($hasil['olah'.$b],0);
					$hasil['olah'.$b]+=$totseb3['olah'.$b];
				}//tutup sebaran bulanan 
				*/
				
				
						//-----tutup total (harus dalam array)-----
			}//TUTUP WHILE
			
			
			// echo"<pre>";
			// print_r($totalbulan);
			// echo"</pre>";

				//------------------------------ INSERT KE LIST-------------------------------------
				$tab.="<thead><tr class=rowheader><td align=center colspan=3>Total</td>";
				$tab.="<td align=right>".number_format($totkgolah,2)."</td>";
				$tab.="<td align=right>".($totkgolah>0?number_format($kgCPO * 100 / $totkgolah,2):0)."</td>";
				$tab.="<td align=right>".($totkgolah>0?number_format($kgKer * 100 / $totkgolah,2):0)."</td>";
				for($i=1;$i<=12;$i++) {
					if(strlen($i)=='1') {
						$i="0".$i;
					} else {
						$i=$i;
					} 
					$tab.="<td align=right>".number_format($totalbulan[$i],2)."</td>";
				}
				$tab.="<td></td>";
				$tab.="</tr></thead>";
				//=============================================== TUTUP TOTAL DATA ===============================================


			//-------------	 penomoran dan button paging-------------		
			$spnCol=$totRowDlm+7;
			$tab.="
			<tr><td>&nbsp;</td></tr><tr><td colspan='".$spnCol."' align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
			<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr>"; 
			$tab.="</tbody></table>";
			echo $tab;
		break;	



        //untuk hapus
        case 'delete':
        // cek apakah budget biaya sudah diclose
            $sData="select * from ".$dbname.".bgt_produksi_pks where kunci='".$kunci."'";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while($rData=$qData->fetch()) {
                $kodepabrik=$rData['millcode'];
                $kodetahun=$rData['tahunbudget'];
            }
			
            $sudahtutup=0;
            $sData="select * from ".$dbname.".bgt_budget where kodeorg='".$kodepabrik."' and tahunbudget = '".$kodetahun."' and tutup = '1'";
            $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
            $qData->setFetchMode(PDO::FETCH_ASSOC);
            while($rData=$qData->fetch()) {
                $sudahtutup=$rData['tutup'];
            }
			
                    if($sudahtutup=='1'){
            exit('Error: Budget has been closed, please verify.');
        }else{
                $tab="delete from ".$dbname.".bgt_produksi_pks where kunci='".$kunci."'";
                try{$owlPDO->exec($tab); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        }
        break;
        //tutup hapus

        //============================================== case close ============================================================
        //untuk tutup 
         case'closepks':
                $sQl="select distinct tutup from ".$dbname.".bgt_produksi_pks where tahunbudget='".$thnttp."' and millcode='".$lkstgs."' and tutup=1 ";
                $qQl=$owlPDO->query($sQl) or die(print " Gagal: ".PDOException::getMessage());
                $qQl->setFetchMode(PDO::FETCH_ASSOC);
                $numrows=owlBaris($qQl);
                $row=$numrows;
                if($row!=1)
                {
                        $sUpdate="update ".$dbname.".bgt_produksi_pks set tutup=1 where tahunbudget='".$thnttp."' and millcode='".$lkstgs."'  ";
                       try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                }
                else
                {
                        exit("Error: Data has been closed");
                }
    break;
        //tutup case tutup
        //cek untuk close
        case 'cekclose':
                $sat=0;
                $aCek="select distinct tutup from ".$dbname.".bgt_produksi_pks where tahunbudget='".$thnbudget."' and millcode='".$kdpks."' ";
                $bCek=$owlPDO->query($aCek) or die(print " Gagal: ".PDOException::getMessage());
                $bCek->setFetchMode(PDO::FETCH_ASSOC);
                $cCek=$bCek->fetch();
                if($cCek['tutup']==1)
                {
                        echo "warning : Budget ".$thnbudget." for code ".$kdpks." has been closed";
                        exit();	
                }      
                else
                {
                        if($ktbs=='1')
                        {
                        $sOpt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' and tipe='KEBUN'";
                        }
                        elseif($ktbs=='2')
                        {
                        $sOpt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk<>'".$_SESSION['org']['kodeorganisasi']."'  and tipe='KEBUN'";
                        }
                        elseif($ktbs=='0')
                        {
                        $sOpt="select supplierid as kodeorganisasi,namasupplier as namaorganisasi from ".$dbname.".log_5supplier where kodekelompok='S001' order by namasupplier asc";
                        }

                        if($ktbs!='0') {
                                $optKode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                                $qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
                                $qOpt->setFetchMode(PDO::FETCH_ASSOC);
                                while($rOpt=$qOpt->fetch())
                                {
                                        if($kdunit!='')
                                        {//
                                                $optKode.="<option value='".$rOpt['kodeorganisasi']."' ".($rOpt['kodeorganisasi']==$kdunit?'selected':'').">".$rOpt['namaorganisasi']."</option>";
                                        }
                                        else
                                        {
                                                $optKode.="<option value='".$rOpt['kodeorganisasi']."'>".$rOpt['namaorganisasi']."</option>";

                                        }
                                }
                        } else {
                                $optKode="<option value='tbsexternal'>TBS External</option>";
                        }

                        if($kdunit!='')
                        {
                                $sData="select * from ".$dbname.".bgt_produksi_pks where millcode='".$_SESSION['empl']['lokasitugas']."' and tahunbudget='".$thnbudget."' and kodeunit='".$kdunit."'";
                                $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
                                $qData->setFetchMode(PDO::FETCH_ASSOC);
                                while($rData=$qData->fetch())
                                 {
                                         for($abr=1;$abr<13;$abr++)
                                         {
                                                 if(strlen($abr)<2)
                                                 {
                                                         $a="0".$abr;
                                                 }
                                                 else
                                                 {
                                                         $a=$abr;
                                                 }
                                                 $brd[$abr]=$rData['olah'.$a];
                                         }
                                 }

                                           echo $optKode."###";
                                           foreach($brd as $brs)
                                           {
                                                   $sat+=1;
                                                   echo $brs."###";
                                                   if($sat==12)
                                                   {
                                                           echo $brs;
                                                   }
                                           }
                        }
                        else
                        {
                                echo $optKode;
                        }
                }
                break;
                //tutup untuk cek close

        //ambil tahun untuk menu tutup
        case 'getThn':
                        $optthnttp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sql = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_pks where millcode like '%".$_SESSION['empl']['lokasitugas']."%' and tutup=0 order by tahunbudget desc";
                        $qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                        $qry->setFetchMode(PDO::FETCH_ASSOC);
                        while ($data=$qry->fetch())
                                {
                                $optthnttp.="<option value=".$data['tahunbudget'].">".$data['tahunbudget']."</option>";
                                }
                        echo $optthnttp;
        break;
        //tutup ambil tahun

        //ambil organisasi untuk form tutup
        case 'getOrg':
                $optorgclose="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sql = "SELECT distinct millcode,tahunbudget FROM ".$dbname.".bgt_produksi_pks where millcode = '".$_SESSION['empl']['lokasitugas']."' and tutup=0 ";
                $qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $qry->setFetchMode(PDO::FETCH_ASSOC);
                while ($data=$qry->fetch())
                {
                    $optorgclose.="<option value=".$data['millcode'].">".$optNmOrg[$data['millcode']]."</option>";
                    $optThn.="<option value='".$data['tahunbudget']."'>".$data['tahunbudget']."</option>";
                }


                echo $optorgclose."###".$optThn;
        break;


        case 'getthnbudgetHeader':
                //$bjr="select bjr from ".$dbname.".bgt_bjr WHERE kodeorg='".substr($kdblok,0,4)."'";
                $optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sThn = "SELECT distinct tahunbudget FROM ".$dbname.".bgt_produksi_pks where millcode like '%".$_SESSION['empl']['lokasitugas']."%' order by tahunbudget desc";
                $qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
                $qThn->setFetchMode(PDO::FETCH_ASSOC);
                while($rThn=$qThn->fetch())
                {
                        $optTahunBudgetHeader.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
                }
                echo $optTahunBudgetHeader;
        break;
        //tutup
default:
}
?>
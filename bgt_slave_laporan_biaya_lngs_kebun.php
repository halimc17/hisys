<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses','');
$kodeOrg = checkPostGet('kdUnit','');
$thnBudget = checkPostGet('thnBudget','');
$noakun = checkPostGet('noakun','');

	switch($proses){
            case'preview':
			case'excel':

			$where=" kodeunit='".$kodeOrg."' and tahunbudget='".$thnBudget."' and thntnm in (select distinct thntnm from ".$dbname.".bgt_budget_kebun_perakun_vw where  kodeorg='".$kodeOrg."' and tahunbudget='".$thnBudget."'  ) ";
			//query sum buat total setahun
			$sSum="select sum(jlhkg) as ton from ".$dbname.".bgt_produksi_afdeling where ".$where."";
			$qSum=$owlPDO->query($sSum) or die(print " Gagal: ".PDOException::getMessage());
			$qSum->setFetchMode(PDO::FETCH_ASSOC);
			$rSum=$qSum->fetch();

			$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
			$optKegiatan=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
			$optBrng=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

			//get kg per tahun tanam
			$sKodeOrg="select distinct jlhjjg,jlhkg,tahunbudget,thntnm from ".$dbname.".bgt_produksi_afdeling where  ".$where."  order by tahunbudget asc";
			$qKodeOrg=$owlPDO->query($sKodeOrg) or die(print " Gagal: ".PDOException::getMessage());
			$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
			$a=0;
			while($rKode=$qKodeOrg->fetch())
			{
				$a+=1; 
				//$dtKdunit[$a]=$rKode['afdeling']; 
				@$dtJjg[$rKode['tahunbudget']][$rKode['thntnm']]+=$rKode['jlhjjg'];
				@$dtJmlhKg[$rKode['tahunbudget']][$rKode['thntnm']]+=$rKode['jlhkg'];
				
			}
			//luas TM
			//ambil luas planted per tahuntanam
			$str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
				  kodeblok like '".$kodeOrg."%' and statusblok='TM' and tahunbudget='".$thnBudget."'
				  group by thntnm";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				@$dtJmlhLuastm[$thnBudget][$bar->thntnm]+=$bar->luas;
				@$ttlLuastm+=$bar->luas;
			}
			//luas TBM
			//ambil luas planted per tahuntanam
			$str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
				  kodeblok like '".$kodeOrg."%' and statusblok like 'TB%' and tahunbudget='".$thnBudget."'
				  group by thntnm";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch()){
				@$dtJmlhLuastbm[$thnBudget][$bar->thntnm]+=$bar->luas;
				@$ttlLuastbm+=$bar->luas;
			}
			 @$ttlLuas=$ttlLuastbm+$ttlLuastm;


			 $str="select sum(hathnini) as luas,thntnm from ".$dbname.".bgt_blok where 
				  kodeblok like '".$kodeOrg."%' and statusblok in ('TB') and tahunbudget='".$thnBudget."' 
				  group by thntnm";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				$dtJmlhLuasLc[$thnBudget][$bar->thntnm]+=$bar->luas;
				$ttlLuasLc+=$bar->luas;
			}
			//jumlah pokok
			 $str="select sum(pokokthnini) as luas,thntnm from ".$dbname.".bgt_blok where 
				  kodeblok like '".$kodeOrg."%' and statusblok ='BBT' and tahunbudget='".$thnBudget."' 
				  group by thntnm";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{
				@$dtJmlhLuasPkk[$thnBudget][$bar->thntnm]+=$bar->luas;
				@$ttlLuasPkk+=$bar->luas;
			} 
			 
			//get tahun tanam
			$sThnTnm="select distinct thntnm from ".$dbname.".bgt_budget_kebun_perblok_vw where
					  kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' order by noakun asc";
			$qThnTnm=$owlPDO->query($sThnTnm) or die(print " Gagal: ".PDOException::getMessage());
			$qThnTnm->setFetchMode(PDO::FETCH_ASSOC);
			$dtThnBudget = array();
			while($rThnTnm=$qThnTnm->fetch())
			{
				$a+=1; 
				$dtThnBudget[$a]=$rThnTnm['thntnm']; 
			}
			//get no akun
			$sNoakun2="select distinct noakun from ".$dbname.".bgt_budget
					   where kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."'
					   and tipebudget='ESTATE' and kodebudget!='UMUM' group by  noakun order by noakun asc";
			$qNoakun2=$owlPDO->query($sNoakun2) or die(print " Gagal: ".PDOException::getMessage());
			$qNoakun2->setFetchMode(PDO::FETCH_ASSOC);
			while($rNoakun2=$qNoakun2->fetch())
			{
				$dtNoakun[]=$rNoakun2['noakun'];
			}
			//get total rupiah perkepala
			$sNoakunRupiah2="select distinct substr(noakun,1,3) as aknKpala,sum(rupiah) as  rupiah,tahunbudget from ".$dbname.".bgt_budget
							 where  kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' and tipebudget='ESTATE'
							 and kodebudget!='UMUM'  group by noakun";
			$qNoakunRupiah2=$owlPDO->query($sNoakunRupiah2) or die(print " Gagal: ".PDOException::getMessage());
			$qNoakunRupiah2->setFetchMode(PDO::FETCH_ASSOC);
			while($rNoakunRupiah2=$qNoakunRupiah2->fetch())
			{
				if(substr(@$rNoakunRupiah2['akunkpla'],0,1)!='6')
				{
					@$dtNoakunRup2[$thnBudget][$rNoakunRupiah2['aknKpala']]+=$rNoakunRupiah2['rupiah'];
				}
				else
				{
					$rNoakunRupiah2['akunkpla']= substr($rNoakunRupiah2['akunkpla'],0,1);
					@$dtNoakunRup2[$thnBudget][$rNoakunRupiah2['aknKpala']]+=$rNoakunRupiah2['rupiah'];
				}
			}

			$sAkunRupiah="select distinct substr(noakun,1,3) as aknKpala,sum(rupiah) as  rupiah,tahunbudget,thntnm from ".$dbname.".bgt_budget_kebun_perblok_vw
						  where kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."'  group by thntnm,noakun";
			$qAkunRupiah=$owlPDO->query($sAkunRupiah) or die(print " Gagal: ".PDOException::getMessage());
			$qAkunRupiah->setFetchMode(PDO::FETCH_ASSOC);
			$dtNoakunRup=array();
			while($rAkunRupiah=$qAkunRupiah->fetch())
			{
				@$dtNoakunRup[$rAkunRupiah['tahunbudget']][$rAkunRupiah['thntnm']][$rAkunRupiah['aknKpala']]+=$rAkunRupiah['rupiah'];
			}

			//noakun
			$sNoakun="select noakun,thntnm,tahunbudget,sum(rupiah) as rupiah from ".$dbname.".bgt_budget_kebun_perblok_vw where
					  kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."' group by thntnm,noakun order by noakun asc";
			$qNoakun=$owlPDO->query($sNoakun) or die(print " Gagal: ".PDOException::getMessage());
			$qNoakun->setFetchMode(PDO::FETCH_ASSOC);
			while($rNoakun=$qNoakun->fetch())
			{
				$lstNoakun[$rNoakun['tahunbudget']][$rNoakun['thntnm']]=$rNoakun['noakun'];
				$lstRupiah[$rNoakun['tahunbudget']][$rNoakun['thntnm']][$rNoakun['noakun']]=$rNoakun['rupiah'];
				$totNakun[$rNoakun['tahunbudget']]=$rNoakun['noakun'];
				$dtThntnm[$rNoakun['tahunbudget']]=$rNoakun['thntnm'];
			}
			$sTotAkun="select sum(rupiah) as total,noakun from ".$dbname.".bgt_budget where
					   kodeorg like '".$kodeOrg."%' and tahunbudget='".$thnBudget."'  group by noakun";
			$qTotAkun=$owlPDO->query($sTotAkun) or die(print " Gagal: ".PDOException::getMessage());
			$qTotAkun->setFetchMode(PDO::FETCH_ASSOC);
			while($rTotAkun=$qTotAkun->fetch())
			{
				$totRupiah[$thnBudget][$rTotAkun['noakun']]=$rTotAkun['total'];
			}
			$jmlhbrs=count($dtThnBudget);
			$colTotal=($jmlhbrs+1)*2;
			if($_GET['proses']=='excel')
			{
			$bg=" bgcolor=#DEDEDE";
			$brdr=1;
			$tab="<table>
			 <tr><td colspan=5 align=left><font size=5>".strtoupper($_SESSION['lang']['lapLangsung'])."</font></td></tr> 
			 <tr><td colspan=5 align=left>".$optNm[$kodeOrg]."</td></tr>   
			 <tr><td>".$_SESSION['lang']['budgetyear']."</td><td colspan=2 align=left>".$thnBudget."</td></tr>   
			 </table>";
			}
			else
			{
			   $bg=" ";
			   $brdr=0; 
			}
			if($proses!='PDF'){ 
				
			if($kodeOrg==''||$thnBudget=='')
			{
				exit("Error:Field Tidak Boleh Kosong");
			}


				  $jmhThn=count($dtThnBudget);
				  if($jmhThn==0||$jmhThn=='')
				  {
					  exit("Error:Data Kosong");
				  }

						$tab.="<table cellpadding=5 cellspacing=1 border=".$brdr." class=sortable><thead>";
						$tab.="<tr class=rowheader>";
						$tab.="<th  rowspan=5 valign='middle' align=center ".$bg." >".$_SESSION['lang']['noakun']."</th>";
						$tab.="<th  rowspan=5 valign='middle' align=center ".$bg." >".$_SESSION['lang']['namakegiatan']."</th>";
						$tab.="<th colspan='2'  align=center ".$bg.">".$_SESSION['lang']['total']."</th>";
						foreach($dtThnBudget as $listThn)
						{
							$tab.="<th colspan='2'  align=center ".$bg.">".$listThn."</th>";
						}
						$tab.="</tr>";
						$tab.="<tr>";
						$tab.="<th align=right ".$bg.">TM=".numb_format(@$ttlLuastm,2)." TBM=".numb_format(@$ttlLuastbm,2)."</th><th ".$bg.">Ha</th>";
						foreach($dtThnBudget as $listThn2)
						{
							$tab.="<th align=right ".$bg.">TM=".numb_format(@$dtJmlhLuastm[$thnBudget][$listThn2],2)." TBM=".numb_format(@$dtJmlhLuastbm[$thnBudget][$listThn2],2)."</th><th ".$bg."> Ha</th>";
						}
						$tab.="</tr>";
						$tab.="<tr>";
						$tab.="<th align=right ".$bg.">".numb_format($rSum['ton'],2)."</th><th ".$bg.">Kg</th>";
						foreach($dtThnBudget as $listThn2)
						{
							$tab.="<th align=right ".$bg.">".numb_format($dtJmlhKg[$thnBudget][$listThn2],0)."</th><th ".$bg.">Kg</th>";
						}
						$tab.="</tr>";
						$kgTotal=$rSum['ton'];
						//$haTotal=$ttlLuastm;
						@$hsilBagi=($kgTotal/1000)/$ttlLuastm;
					   
						$tab.="<tr>";
						$tab.="<th align=right ".$bg.">".numb_format($hsilBagi,2,".",",")."</th><th align=left ".$bg.">Ton/Ha</th>";
						foreach($dtThnBudget as $listThn2)
						{
							@$hslBag[$listThn2]=($dtJmlhKg[$thnBudget][$listThn2]/1000)/$dtJmlhLuastm[$thnBudget][$listThn2];
							$tab.="<th align=right ".$bg.">".numb_format($hslBag[$listThn2],2,".",",")."</th><th align=left ".$bg.">Ton/Ha</th>";
						}
						$tab.="<tr>";
						$tab.="<th align=center ".$bg.">".$_SESSION['lang']['total']."</th><th align=center ".$bg.">Rp/(Ha/Kg)</th>";
						foreach($dtThnBudget as $listThn2)
						{
							$tab.="<th align=center ".$bg.">".$_SESSION['lang']['total']."</th><th align=center ".$bg.">Rp/(Ha/Kg)</th>";
						}
						$tab.="</tr>";

						$tab.="</thead><tbody>";
						$awal=0;
							
							 foreach($dtNoakun as $barisNoakun)
							 {
								  if(@$ktKrgng!=substr($barisNoakun,0,3))
								  {
									$brs=1;
								  }
								  if($brs==1) 
								  {
									$ktKrgng=substr($barisNoakun,0,3);
									if(substr($barisNoakun,0,3)=='126')//TBM
									{
										@$hslBagiNaokunk[$thnBudget][$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$ttlLuastbm;
										$tab.="<tr class='rowcontent'>";
										$tab.="<td colspan=2><b>".$_SESSION['lang']['total']." TBM</b></td>";
										$tab.="<td align=right><b>".numb_format($dtNoakunRup2[$thnBudget][$ktKrgng],0)."</b></td>";
										$tab.="<td align=right><b>".numb_format($hslBagiNaokunk[$thnBudget][$ktKrgng],2)."</b></td>";
									} 
									else if(substr($barisNoakun,0,3)=='128')//BBT
									{
										@$hasilBagiRup[$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$ttlLuasPkk;  
										$tab.="<tr class='rowcontent'>";
										$tab.="<td colspan=2><b>".$_SESSION['lang']['total']." BIBITAN</b></td>";
										$tab.="<td align=right><b>".numb_format($dtNoakunRup2[$thnBudget][$ktKrgng])."</b></td>";
										$tab.="<td align=right><b>".numb_format($hasilBagiRup[$ktKrgng],2)."</b></td>";
									}
									else
									{
										if(substr($barisNoakun,0,3)=='611')//BIAYA PANEN
										{
										@$dtNoAkun[$thnBudget][$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$rSum['ton'];
										}
										else
										{
										@$dtNoAkun[$thnBudget][$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$ttlLuastm;
										}
										$tab.="<tr class='rowcontent'>";
										$tab.="<td colspan=2><b>".$_SESSION['lang']['total']." TM / PANEN</b></td>";
										$tab.="<td align=right><b>".numb_format($dtNoakunRup2[$thnBudget][$ktKrgng])."</b></td>";
										$tab.="<td align=right><b>".numb_format($dtNoAkun[$thnBudget][$ktKrgng],2)."</b></td>";
									}    
									
									foreach($dtThnBudget as $lstThaTnm)
									{ 
											if(substr($barisNoakun,0,3)=='126')//TBM
											{
												@$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$dtJmlhLuastbm[$thnBudget][$lstThaTnm];
												$tab.="<td align=right><b>".numb_format($dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng])."</b></td>";
												$tab.="<td align=right><b>".numb_format($hslBagi[$thnBudget][$lstThaTnm][$ktKrgng],2)."</b></td>";
											} 
											else if(substr($barisNoakun,0,3)=='128')//BBT
											{
												@$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$dtJmlhLuasLc[$thnBudget][$lstThaTnm];
												$tab.="<td align=right><b>".numb_format($dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng])."</b></td>";
												$tab.="<td align=right><b>".numb_format($hslBagi[$thnBudget][$lstThaTnm][$ktKrgng],2)."</b></td>";
											}
											else
											{
												if(substr($barisNoakun,0,3)=='611')//BIAYA PANEN
												{
												  @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$dtJmlhKg[$thnBudget][$lstThaTnm];
												}
												else
												{
												  @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$dtJmlhLuastm[$thnBudget][$lstThaTnm];
												}
												
												$tab.="<td align=right><b>".numb_format($dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng])."</b></td>";
												$tab.="<td align=right><b>".numb_format($hslBagi[$thnBudget][$lstThaTnm][$ktKrgng],2)."</b></td>";
											}    
										
									
									}
									//$tab.="<td colspan=".($colTotal-2).">&nbsp;</td>";
									$tab.="</tr>";
									$brs=0;
									$awal=1;
								  }
									
								  $arr="thnBudget##".$thnBudget."##noakun##".$barisNoakun."##kdUnit##".$kodeOrg;
								  $tab.="<tr class='rowcontent' style='cursor:pointer;' onclick=\"zDetail(event,'bgt_slave_laporan_biaya_lngs_kebun.php','".$arr."')\">";
								  $tab.="<td>".$barisNoakun."</td>";
								  $tab.="<td>".$optKegiatan[$barisNoakun]."</td>";
								  $stab='';
									if(substr($barisNoakun,0,3)=='126')//TBM
									{
										$kwe=substr($barisNoakun,0,5);
										if($kwe<='12605'){
											if($ttlLuasLc==0){
												$ttlLuasLc=$ttlLuastbm;
											}
											@$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuasLc;
											$gttbm+=$totRupiah[$thnBudget][$barisNoakun];
											@$bagitbm+=$hasilBagi[$barisNoakun];
										}else{
											@$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuastbm;
											$gttbm+=$totRupiah[$thnBudget][$barisNoakun];
											@$bagitbm+=$hasilBagi[$barisNoakun];
										}     
										$stab='Rp/Ha';                    
									} 
									else if(substr($barisNoakun,0,3)=='128')//BBT
									{
									  @$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuasPkk;
									  $gtbbt+=$totRupiah[$thnBudget][$barisNoakun];
									  $bagibbt+=$hasilBagi[$barisNoakun];  
										$stab='Rp/Ha';               
									}
									else
									{
										if(substr($barisNoakun,0,3)=='611')//BIAYA PANEN
										{
										@$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$rSum['ton'];
										$stab='Rp/Kg';
										}
										else
										{
										@$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuastm;
										$stab='Rp/Ha';
										}
										@$gttm+=$totRupiah[$thnBudget][$barisNoakun];
													  @$bagitm+=$hasilBagi[$barisNoakun];
									}    
									
									$tab.="<td align=right title='".$stab."'>".numb_format($totRupiah[$thnBudget][$barisNoakun])."</td>";
									$tab.="<td align=right title='".$stab."'>".numb_format($hasilBagi[$barisNoakun],2)."</td>";
									
									
									@$grndTotal+=$totRupiah[$thnBudget][$barisNoakun];
									@$grndTotalHsil+=$hasilBagi[$barisNoakun];
									$stab='';
									foreach($dtThnBudget as $brsThnBudget)
									{                       
										if(substr($barisNoakun,0,3)=='126')//TBM
										  {
											@$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuastbm[$thnBudget][$brsThnBudget];
											$totalRptbm[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
											$totalbagitbm[$brsThnBudget]+=$hasilBagi2[$brsThnBudget];
											  $stab='Rp/Ha';
											
										  }
										 else if(substr($barisNoakun,0,3)=='128')//BBT
										  {
											@$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuasLc[$thnBudget][$lstThaTnm];
											$totalRpbbt[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
											$totalbagibbt[$brsThnBudget]+=$hasilBagi2[$brsThnBudget];
											  $stab='Rp/Ha';
											   
										 } 
										 else
										 {
											  if(substr($barisNoakun,0,3)=='611')//BIAYA PANEN
											  {
											  @$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhKg[$thnBudget][$brsThnBudget];
											  $stab='Rp/Kg';
											  }
											  else
											  {
											  @$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuastm[$thnBudget][$brsThnBudget];
											  $stab='Rp/Ha';
											  }
											 
											 @$totalRptm[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
											 @$totalbagitm[$brsThnBudget]+=$hasilBagi2[$brsThnBudget];
											 
										 }
										
										$tab.="<td align=right title='".$stab."'>".numb_format($lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun])."</td>";
										$tab.="<td align=right title='".$stab."'>".numb_format($hasilBagi2[$brsThnBudget],2)."</td>";

									}
								  $tab.="</tr>";
							 }
							 
							$tab.="<thead><tr class=rowheader><td colspan=2>".$_SESSION['lang']['total']." BBT</td>";
							$tab.="<td align=right>".numb_format(@$gtbbt)."</td>
								  <td align=right>".numb_format(@$bagibbt,2)."</td>";
							foreach($dtThnBudget as $brsThnBudget)
							{
								$tab.="<td align=right>".numb_format(@$totalRpbbt[$brsThnBudget])."</td>
									   <td align=right>".numb_format(@$totalbagibbt[$brsThnBudget],2)."</td>";
							}
							$tab.="</tr>";
							
							$tab.="<tr class=rowheader><td colspan=2>".$_SESSION['lang']['total']." TBM</td>";
							$tab.="<td align=right>".numb_format(@$gttbm)."</td>
								  <td align=right>".numb_format(@$bagitbm,2)."</td>";
							foreach($dtThnBudget as $brsThnBudget)
							{
								$tab.="<td align=right>".numb_format(@$totalRptbm[$brsThnBudget])."</td>
									   <td align=right>".numb_format(@$totalbagitbm[$brsThnBudget],2)."</td>";
							}
							$tab.="</tr>";       

							$tab.="<tr class=rowheader><td colspan=2>".$_SESSION['lang']['total']." TM</td>";
							$tab.="<td align=right>".numb_format($gttm)."</td>
								  <td align=right>".numb_format($bagitm,2)."</td>";
							foreach($dtThnBudget as $brsThnBudget)
							{
								$tab.="<td align=right>".numb_format($totalRptm[$brsThnBudget])."</td>
									   <td align=right>".numb_format($totalbagitm[$brsThnBudget],2)."</td>";
								@$grandTotal[$brsThnBudget]+=$totalRptm[$brsThnBudget]+$totalRptbm[$brsThnBudget]+$totalRpbbt[$brsThnBudget];
								@$bagiGrandTotal[$brsThnBudget]+=$totalbagitm[$brsThnBudget]+$totalbagitbm[$brsThnBudget]+0;
							}
							@$grndTotal=$gtbbt+$gttbm+$gttm;
							@$bagigrndTotal=0+$bagitbm+$bagitm;
							$tab.="</tr>";
							 $tab.="<tr class=rowheader><td colspan=2>".$_SESSION['lang']['grnd_total']." </td>";
							$tab.="<td align=right>".numb_format($grndTotal)."</td>
								  <td align=right>".numb_format($bagigrndTotal,2)."</td>";
							foreach($dtThnBudget as $brsThnBudget)
							{
								$tab.="<td align=right>".numb_format($grandTotal[$brsThnBudget])."</td>
									   <td align=right>".numb_format($bagiGrandTotal[$brsThnBudget],2)."</td>";
							}
							$tab.="</tr>"; 
						$tab.="</thead></tbody></table>";
			}    
				
			if($proses=='excel'){				
				$tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
				$dte=date("YmdHis");
				$nop_="lapKebunBiayaLangsung_thntnm_".$dte;
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
						 gzwrite($gztralala, $tab);
						 gzclose($gztralala);
						 echo "<script language=javascript1.2>
							window.location='tempExcel/".$nop_.".xls.gz';
							</script>";
			}else{
				echo $tab;
			}	
            break;
            case'getDetail':
              $theme=$_SESSION['theme'];
              if($theme=='skyblue' || $theme==''){
                $gen='generic.css';
              }else if($theme=='red'){
                $gen='genericRed.css';  
              }else{
                $gen='genericGray.css';  
              }             
              $table="<script language=javascript src=js/zMaster.js></script><script language=javascript src=js/zTools.js></script><script language=javascript src=js/zReport.js></script>
          	<script language=\"javascript\" src=\"js/generic.js\"></script><script language=javascript src=js/pmn_laporanPemenuhanKontrak.js></script>";
          	$table.="<link rel=stylesheet type=text/css href=style/".$gen.">";
                    // mengambil data dari table bgt_budget_kebun_perblok_vw
                    $sData="select distinct kodeorg, kodebudget,kegiatan,noakun,volume,satuanv,rupiah,thntnm,kodebarang,jumlah,satuanj from ".$dbname.".bgt_budget_kebun_perblok_vw where substring(kodeorg,1,4)='".$kodeOrg."' and noakun='".$noakun."' and tahunbudget='".$thnBudget."'";
    				$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
    				$qData->setFetchMode(PDO::FETCH_ASSOC);
              $arrd3="proses=&thnBudget=".$thnBudget."&noakun=".$noakun."&kdUnit=".$kodeOrg."&proses=dExcel";
              //$table.="<input type='hidden' id=thnBgt value='".$thnBudget."' /><input type='hidden' id=noakn value='".$noakun."' /><input type='hidden' id=kdunit value='".$kodeOrg."' />";
              $table.="<fieldset style=float:left>";
              $table.="<img onclick=\"printFileData('".$arrd3."','bgt_slave_laporan_biaya_lngs_kebun.php','".$_SESSION['lang']['detail']." Excel',event)\" src=\"images/excel.jpg\" class=\"resicon\" title=\"MS.Excel\"></fieldset><div style=clear:both></div>";
              $table.="<table cellspacing=1 cellpadding=5 border=0 class=sortable><thead>";
              $table.="<tr class=rowheader>";
              //$table.="<th>".$_SESSION['lang']['blok']."</th>";
              $table.="<th>No</th>";
              $table.="<th>".$_SESSION['lang']['kodeblok']."</th>";
              $table.="<th>".$_SESSION['lang']['kodebudget']."</th>";
              $table.="<th>".$_SESSION['lang']['volume']."</th>";
              $table.="<th>".$_SESSION['lang']['satuan']."</th>";
              $table.="<th>".$_SESSION['lang']['namabarang']."</th>";
              $table.="<th>".$_SESSION['lang']['jumlah']."</th>";
              $table.="<th>".$_SESSION['lang']['satuan']."</th>";
              $table.="<th>".$_SESSION['lang']['rp']."</th>";
              $table.="</tr></thead><tbody>";
              while($rData=$qData->fetch()){
                $no+=1;
                    $table.="<tr class=rowcontent>";
                    $table.="<td align=center>".$no."</td>";
                    $table.="<td>".getNamaOrg($rData['kodeorg'])."</td>";
                    $table.="<td>".getBgtKode($rData['kodebudget'])."</td>";
                    //$table.="<td>".$rData['noakun']."</td>";
                    //$table.="<td>".$optKegiatan[$rData['noakun']]."</td>";
                    $table.="<td  align=right>".numb_format($rData['volume'],2)."</td>";
                    $table.="<td>".$rData['satuanv']."</td>";
                    //$optBrng
                    $table.="<td>".@$optBrng[$rData['kodebarang']]."</td>";
                    $table.="<td align=right>".numb_format($rData['jumlah'],2)."</td>";
                    $table.="<td>".$rData['satuanj']."</td>";
                    $table.="<td align=right>".numb_format($rData['rupiah'],0)."</td>";
                    $table.="</tr>";
                    // $table.=$brt;
                    $awal+=1;
					
					$total+=$rData['rupiah'];
              }
			  
			$table.="<tr class=rowcontent>";
			$table.="<td colspan=8>TOTAL</td>";
			$table.="<td align=right>".numb_format($total,0)."</td>";
			$table.="</tr>";
					
              $table.="</tbody></table></fieldset>";
              echo $table;
            break;
            case'dExcel':
               
              // mengambil data dari table bgt_budget_kebun_perblok_vw
               $sData="select distinct kodeorg, kodebudget,kegiatan,noakun,volume,satuanv,rupiah,thntnm,kodebarang,jumlah,satuanj from ".$dbname.".bgt_budget_kebun_perblok_vw where substring(kodeorg,1,4)='".$kodeOrg."' and noakun='".$noakun."' and tahunbudget='".$thnBudget."' order by kodeorg asc";
			   $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			   $qData->setFetchMode(PDO::FETCH_ASSOC);
              
              $table="";
              $table.="<table cellspacing=1 cellpadding=1 border=1 class=sortable><thead>";
              $table.="<tr class=rowheader>";
              //$table.="<td>".$_SESSION['lang']['blok']."</td>";
              $table.="<td bgcolor=#DEDEDE>No</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['kodeblok']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['kodebudget']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['volume']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['satuan']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['kodebarang']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['namabarang']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['jumlah']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['satuan']."</td>";
              $table.="<td bgcolor=#DEDEDE>".$_SESSION['lang']['rp']."</td>";
              $table.="</tr></thead><tbody>";
              while($rData=$qData->fetch())
              {
                $no+=1;
                    $table.="<tr class=rowcontent>";
                    $table.="<td>".$no."</td>";
                    $table.="<td>".$rData['kodeorg']."</td>";
                    $table.="<td>".$rData['kodebudget']."</td>";
                    //$table.="<td>".$rData['noakun']."</td>";
                    //$table.="<td>".$optKegiatan[$rData['noakun']]."</td>";
                    $table.="<td  align=right>".$rData['volume']."</td>";
                    $table.="<td>".$rData['satuanv']."</td>";
                    //$optBrng
                    $table.="<td>".$rData['kodebarang']."</td>";
                    $table.="<td>".@$optBrng[$rData['kodebarang']]."</td>";
                    $table.="<td align=right>".numb_format($rData['jumlah'],0)."</td>";
                    $table.="<td>".$rData['satuanj']."</td>";
                    $table.="<td align=right>".numb_format($rData['rupiah'],0)."</td>";
                    $table.="</tr>";
                    // $table.=$brt;
                    $awal+=1;
              }
              $table.="</tbody></table>";
              $table.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
              $dte=date("YmdHis");
              $nop_="detaillapKebunBiayaLangsung".$dte;
              $gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
                 gzwrite($gztralala, $table);
                 gzclose($gztralala);
                 echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls.gz';
                    </script>";
            break;
            case'pdf':
           if($kodeOrg==''||$thnBudget=='')
            {
                exit("Error:Field Tidak Boleh Kosong");
            }
      
           class PDF extends FPDF {
            function Header() {
            global $dtJjg;
            global $dtThnBudget;
            global $dtNoakun;
            global $dtJmlhKg;
            global $brsThnBudget;
            global $dtJmlhLuastm;
            global $dtJmlhLuastbm;   
            global $totKg;
            global $totJjg;
            global $ttlLuastm;
            global $ttlLuastbm;
            global $ttlLuas;
            global $dbname;
            global $barisNoakun;
            global $kodeOrg;
            global $totRupiah;
            global $rSum;
            global $lstRupiah;
            global $thnBudget;
            global $hasilBagi;
            global $hasilBagi2;
            global $optNm;
            global $owlPDO;
            
            
  
         		$sAlmat="select namaorganisasi,alamat,telepon from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['org']['kodeorganisasi']."'";
				$qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
				$rAlamat=$qAlamat->fetch();
                
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 10;
                $path='images/logo.jpg';
                $this->Image($path,$this->lMargin,$this->tMargin,0,55);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(100);   
                $this->Cell($width-100,$height,$rAlamat['namaorganisasi'],0,1,'L');	 
                $this->SetX(100); 		
                $this->Cell($width-100,$height,$rAlamat['alamat'],0,1,'L');	
                $this->SetX(100); 			
                $this->Cell($width-100,$height,"Tel: ".$rAlamat['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
                $this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();	
                $this->Ln();
		$this->Ln();
               
               
                $this->SetFont('Arial','B',11);
                $this->Cell($width,$height,strtoupper($_SESSION['lang']['lapLangsung']),0,1,'C');
                $this->Ln();	
                //$this->Cell(275,5,strtoupper($_SESSION['lang']['rprodksiPabrik']),0,1,'C');
                $this->Cell($width,$height,$_SESSION['lang']['unit'].' : '.$optNm[$kodeOrg],0,1,'C');
                $this->SetFont('Arial','',8);
                $this->Cell(850,$height,$_SESSION['lang']['tanggal'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,date('d-m-Y H:i'),0,1,'R');
                $this->Cell(850,$height,$_SESSION['lang']['page'],0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$this->PageNo(),0,1,'R');
                 $this->Cell(850,$height,'User',0,0,'R');
                $this->Cell(10,$height,':','',0,0,'R');
                $this->Cell(70,$height,$_SESSION['standard']['username'],0,1,'R');

                $this->Ln();
                $this->Ln();
                $height = 50;
                $this->SetFillColor(220,220,220);
                $this->SetFont('Arial','B',7);
                $this->Cell(58,$height,$_SESSION['lang']['noakun'],1,0,'C',1);
                $this->Cell(150,$height,$_SESSION['lang']['namakegiatan'],1,0,'C',1);
                $this->SetFont('Arial','B',4.5);
                $this->Cell(80,10,$_SESSION['lang']['total'],1,1,'C',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(80,10,'TM='.numb_format($ttlLuastm,2).' TBM='.numb_format($ttlLuastbm,2)." Ha",1,1,'L',1);
                //$this->Cell(40,10,"Ha",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(40,10,numb_format($rSum['ton'],2),1,0,'R',1);
                $this->Cell(40,10,"Kg",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                
                @$tnha=($rSum['ton']/1000)/$ttlLuastm;
                $this->Cell(40,10,numb_format($tnha,2),1,0,'R',1);
                $this->Cell(40,10,"Ton/Ha",1,1,'L',1);
                $xPertama=$this->GetX();
                $this->SetX($xPertama+208);
                $this->Cell(40,10,$_SESSION['lang']['total'],1,0,'R',1);
                $this->Cell(40,10,"RP/(Ha/Kg)",1,1,'L',1);
                $br=288;
				$no=0;
                foreach($dtThnBudget as $listThn)
                {
//                    echo"<pre>";
//                    print_r($listThn);
//                    echo"</pre>";
                    $no+=1;
                    if($no==1)
                    {
                            $ypertama=$this->GetY();
                            $this->SetY($ypertama-50);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(80,10,$listThn,1,1,'C',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(80,10,'TM='.numb_format($dtJmlhLuastm[$thnBudget][$listThn],2).'TBM='.numb_format($dtJmlhLuastbm[$thnBudget][$listThn],2)." Ha",1,1,'L',1);
                            //$this->Cell(40,10,"Ha",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(40,10,numb_format($dtJmlhKg[$thnBudget][$listThn],2),1,0,'R',1);
                            $this->Cell(40,10,"Kg",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            @$tnha=($dtJmlhKg[$thnBudget][$listThn]/1000)/$dtJmlhLuastm[$thnBudget][$listThn];
                            $this->Cell(40,10,numb_format($tnha,2),1,0,'R',1);
                            $this->Cell(40,10,"Ton/Ha",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(40,10,$_SESSION['lang']['total'],1,0,'R',1);
                            $this->Cell(40,10,"RP/(Ha/Kg)",1,1,'L',1);
                    }
                    else
                    {
                            $ypertama=$this->GetY();
                            $this->SetY($ypertama-50);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(80,10,$listThn,1,1,'C',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(80,10,'TM='.numb_format($dtJmlhLuastm[$thnBudget][$listThn],2).' TBM='.numb_format($dtJmlhLuastbm[$thnBudget][$listThn],2)." Ha",1,1,'L',1);
                            //$this->Cell(40,10,"Ha",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(40,10,numb_format($dtJmlhKg[$thnBudget][$listThn],2),1,0,'R',1);
                            $this->Cell(40,10,"Kg",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            @$tnha=($dtJmlhKg[$thnBudget][$listThn]/1000)/$dtJmlhLuastm[$thnBudget][$listThn];
                            $this->Cell(40,10,numb_format($tnha,2),1,0,'R',1);
                            $this->Cell(40,10,"Ton/Ha",1,1,'L',1);
                            $xPertama=$this->GetX();
                            $this->SetX($xPertama+$br);
                            $this->Cell(40,10,$_SESSION['lang']['total'],1,0,'R',1);
                            $this->Cell(40,10,"RP/(Ha/Kg)",1,1,'L',1);
                    }
                    $br+=80;
                }
  
                
          }
              function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial','I',8);
                $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
            }
            }
            //================================

            $pdf=new PDF('L','pt','LEGAL');
            $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
            $height = 10;
            $pdf->AddPage();
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Arial','B',5);

            $totThn=count($dtThnBudget);
            $totAkun=count($dtNoakun);
                $totalRptm='';
                $totalbagitm='';
                $gttm='';
                $bagitm='';  
                $totalRptbm='';
                $totalbagitbm='';
                $gttbm='';
                $bagitbm=''; 
                $gtbbt='';
                $totalRpbbt='';
                $ard=1;
                $totThn=count($dtThnBudget);
                $totAkun=count($dtNoakun);
                $totalRptm='';
                $totalbagitm='';
                $gttm='';
                $bagitm='';  
                $totalRptbm='';
                $totalbagitbm='';
                $gttbm='';
                $bagitbm=''; 
                $gtbbt='';
                $totalRpbbt='';
                $ard=1;
				$drAwal=0;
             foreach($dtNoakun as $barisNoakun)
                 {
                 $drAwal+=1;
                      if($ktKrgng!=substr($barisNoakun,0,3))
                      {
                          if(substr($barisNoakun,0,1)=='6'){
                              if($ktKrgng!=substr($barisNoakun,0,1))
                              {
                                $brs=1;
                                if($drAwal!=1)
                                {
                                    $yPertama=$pdf->GetY();
                                    $pdf->SetY($yPertama);
                                }

                              }
                              
                          }else{
                        $brs=1;
                        if($drAwal!=1)
                        {
                            $yPertama=$pdf->GetY();
                            $pdf->SetY($yPertama);
                        }
                          }
                          
                      }
                      if($brs==1) 
                      {
                            $colTotal=80+(80*$totThn);
                            
                            $ktKrgng=substr($barisNoakun,0,3);
                            if(substr($barisNoakun,0,3)=='126')//TBM
                            {
                                @$hasilBagiRup[$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$ttlLuastbm;
                                $pdf->SetFont('Arial','B',5);
                                //$yAkhir=$pdf->GetY();
                                $xPertama=$pdf->GetX();
                                //$pdf->SetY($yAkhir);
                                $pdf->SetX($xPertama);
                                //$pdf->Cell(58,$height,$ktKrgng,1,0,'L',1);
                                $pdf->Cell(208,$height,$_SESSION['lang']['total']." TBM",1,0,'L',1);
                                $xPertama=$pdf->GetX();
                                $pdf->SetX($xPertama);
                                $pdf->Cell(40,10,numb_format($dtNoakunRup2[$thnBudget][$ktKrgng],0),1,0,'R',1);
                                $pdf->Cell(40,10,numb_format($hasilBagiRup[$ktKrgng],0),1,0,'R',1);
                            } 
                            else if(substr($barisNoakun,0,3)=='128')//BBT
                            {
                                @$hasilBagiRup[$ktKrgng]=0;  
                                 $pdf->SetFont('Arial','B',5);
                                //$yAkhir=$pdf->GetY();
                                $xPertama=$pdf->GetX();
                                //$pdf->SetY($yAkhir);
                                $pdf->SetX($xPertama);
                                //$pdf->Cell(58,$height,$ktKrgng,1,0,'L',1);
                                $pdf->Cell(208,$height,$_SESSION['lang']['total']." BIBITAN",1,0,'L',1);
                                $xPertama=$pdf->GetX();
                                $pdf->SetX($xPertama);
                                $pdf->Cell(40,10,numb_format($dtNoakunRup2[$thnBudget][$ktKrgng],0),1,0,'R',1);
                                $pdf->Cell(40,10,numb_format($hasilBagiRup[$ktKrgng],0),1,0,'R',1);
                            }
                            else if(substr($barisNoakun,0,1)=='6')
                            {
                                $ktKrgng=substr($ktKrgng,0,1);
                                if(substr($barisNoakun,0,3)=='611'){
                                @$hasilBagiRup[$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$rSum['ton'];

                                }
                                else{
                                @$hasilBagiRup[$ktKrgng]=$dtNoakunRup2[$thnBudget][$ktKrgng]/$ttlLuastm;

                                }
                                $pdf->SetFont('Arial','B',5);
                                //$yAkhir=$pdf->GetY();
                                $xPertama=$pdf->GetX();
                                //$pdf->SetY($yAkhir);
                                $pdf->SetX($xPertama);
                                //$pdf->Cell(58,$height,$ktKrgng,1,0,'L',1);
                                $pdf->Cell(208,$height,$_SESSION['lang']['total']." TM",1,0,'L',1);
                                $xPertama=$pdf->GetX();
                                $pdf->SetX($xPertama);
                                $pdf->Cell(40,10,numb_format(@$dtNoakunRup2[$thnBudget][$ktKrgng],0),1,0,'R',1);
                                $pdf->Cell(40,10,numb_format(@$hasilBagiRup[$ktKrgng],0),1,0,'R',1);
                            }  
                          
                       
                        
                        
                           
                            foreach($dtThnBudget as $lstThaTnm)
                            { 
                                
                                    if(substr($barisNoakun,0,3)=='126')//TBM
                                    {
                                        @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$ttlLuastbm;
                                    } 
                                    else if(substr($barisNoakun,0,3)=='128')//BBT
                                    {
                                        @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=0;
                                    }
                                    else if(substr($barisNoakun,0,1)=='6')
                                    {
                                        $ktKrgng=substr($ktKrgng,0,1);
                                        if(substr($barisNoakun,0,3)=='611'){
                                        @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$rSum['ton'];
                                          
                                        }
                                        else{
                                        @$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng]=$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng]/$ttlLuastm;
                                        }
                                    }    
                                    if($ard<$totThn)
                                    {
                                        //exit("Error".$ard."__".$totThn);
                                        $ard+=1;
                                        $pdf->Cell(40,10,numb_format(@$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng],0),1,0,'R',1);
                                        $pdf->Cell(40,10,numb_format(@$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng],0),1,0,'R',1);
                                    }
                                    else
                                    {
                                        $ard=1;
                                        $pdf->Cell(40,10,numb_format(@$dtNoakunRup[$thnBudget][$lstThaTnm][$ktKrgng],0),1,0,'R',1);
                                        $pdf->Cell(40,10,numb_format(@$hslBagi[$thnBudget][$lstThaTnm][$ktKrgng],0),1,1,'R',1);
                                    }
                                   
                            }
                            //$pdf->Cell($colTotal,$height,"",1,1,'C',1);
                   
                        $brs=0;
                        $awal=1;
                        //$totAkun+=1;
                      }
                        $pdf->SetFont('Arial','',5);
                        $yAkhir=$pdf->GetY();
                        $xPertama=$pdf->GetX();
                        $pdf->SetY($yAkhir);
                        $pdf->SetX($xPertama);  
                        $pdf->Cell(58,$height,$barisNoakun,1,0,'L',1);
                        $pdf->Cell(150,$height,$optKegiatan[$barisNoakun],1,0,'L',1);
                        //echo $totRupiah[$thnBudget][$barisNoakun]."<br>";
//                        if(substr($barisNoakun,0,1)=='1'){
//                           
//                        }else
//                            @$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuastm;
                        if(substr($barisNoakun,0,3=='126'))
                        {
                             @$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuastbm;
                        }
                        else if(substr($barisNoakun,0,3=='128'))
                        {
                            @$hasilBagi=0;
                        }
                        else if(substr($barisNoakun,0,1=='6'))
                        {
                          if(substr($barisNoakun,0,3)=='611'){
                             @$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$rSum['ton'];
                                          
                          }
                          else{
                             @$hasilBagi[$barisNoakun]=$totRupiah[$thnBudget][$barisNoakun]/$ttlLuastm;

                          }
                        }
                        $pdf->Cell(40,10,numb_format($totRupiah[$thnBudget][$barisNoakun],0),1,0,'R',1);
                        $pdf->Cell(40,10,numb_format($hasilBagi[$barisNoakun],0),1,0,'R',1);
                        $grndTotal+=$totRupiah[$thnBudget][$barisNoakun];
                        $grndTotalHsil+=$hasilBagi[$barisNoakun];
                        $yAkhir=$pdf->GetY();
                        $xPertama=$pdf->GetX();
                        $pdf->SetY($yAkhir);
                        $pdf->SetX($xPertama);   
                        $rd=1;
                        foreach($dtThnBudget as $brsThnBudget)
                        {
                            if(substr($barisNoakun,0,3)=='126'){
                                @$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuastbm[$thnBudget][$brsThnBudget];                               
                                @$totalRptbm[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                @$totalbagitbm[$brsThnBudget]+=$hasilBagi2[$brsThnBudget];
                                @$gttbm+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                $bagitbm=$gttbm/$ttlLuastbm;
                                 
                            }
                            else if(substr($barisNoakun,0,3)=='128'){
                                //@$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuastbm[$thnBudget][$brsThnBudget];                               
                                $hasilBagi2=0;
                                @$totalRpbbt[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                @$totalbagbbt[$brsThnBudget]+=0;
                                @$gtbbt+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                $bagitbm=0;
                                 
                            }
                            else if(substr($barisNoakun,0,1)=='6')
                            {
                                if(substr($barisNoakun,0,3)=='611'){
                                @$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhKg[$thnBudget][$brsThnBudget];

                                }
                                else{

                                @$hasilBagi2[$brsThnBudget]=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun]/$dtJmlhLuastm[$thnBudget][$brsThnBudget];
                                }
                                @$totalRptm[$brsThnBudget]+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                @$totalbagitm[$brsThnBudget]+=$hasilBagi2[$brsThnBudget];
                                @$gttm+=$lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun];
                                $bagitm=$gttm/$ttlLuastm;
                            }    
                            if($rd<$totThn)
                                 {
                                    $pdf->Cell(40,10,numb_format($lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun],0),1,0,'R',1);
                                    $pdf->Cell(40,10,numb_format($hasilBagi2[$brsThnBudget],0),1,0,'R',1);
                                 }
                                 else
                                 {
                                    $pdf->Cell(40,10,numb_format($lstRupiah[$thnBudget][$brsThnBudget][$barisNoakun],0),1,0,'R',1);
                                    $pdf->Cell(40,10,numb_format($hasilBagi2[$brsThnBudget],0),1,1,'R',1);
                                 }
                               
                                 $rd+=1;
                        }
                    
                 }
                $pdf->Cell(208,$height,$_SESSION['lang']['total']. 'BBT',1,0,'R',1);
                $pdf->Cell(40,10,@numb_format($gtbbt),1,0,'R',1);
                $pdf->Cell(40,10,@numb_format(0,0),1,0,'R',1);
                $yAkhir=$pdf->GetY();
                $xPertama=$pdf->GetX();
                $pdf->SetY($yAkhir);
                $pdf->SetX($xPertama);   
                $drd=1;
                foreach($dtThnBudget as $brsThnBudget)
                {
                    if($drd<$totThn)
                    {
                    $pdf->Cell(40,10,@numb_format($totalRpbbt[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,@numb_format(0,0),1,0,'R',1);
                    }
                    else
                    {
                    $pdf->Cell(40,10,@numb_format($totalRpbbt[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,@numb_format(0,0),1,1,'R',1);
                    }
                    $drd+=1;
                }
                
                
                $pdf->Cell(208,$height,$_SESSION['lang']['total']. 'TBM',1,0,'R',1);
                $pdf->Cell(40,10,@numb_format($gttbm,0),1,0,'R',1);
                $pdf->Cell(40,10,@numb_format($bagitbm,0),1,0,'R',1);
                $yAkhir=$pdf->GetY();
                $xPertama=$pdf->GetX();
                $pdf->SetY($yAkhir);
                $pdf->SetX($xPertama);   
                $drd=1;
                foreach($dtThnBudget as $brsThnBudget)
                {
                    if($drd<$totThn)
                    {
                    $pdf->Cell(40,10,@numb_format($totalRptbm[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,@numb_format($totalbagitbm[$brsThnBudget],0),1,0,'R',1);
                    }
                    else
                    {
                    $pdf->Cell(40,10,@numb_format($totalRptbm[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,@numb_format($totalbagitbm[$brsThnBudget],0),1,1,'R',1);
                    }
                    $drd+=1;
                }
                
                
                 $pdf->Cell(208,$height,$_SESSION['lang']['total']. 'TM',1,0,'R',1);
                // $pdf->Cell(150,$height,$optKegiatan[$barisNoakun],1,0,'L',1);
                $pdf->Cell(40,10,@numb_format($gttm,0),1,0,'R',1);
                $pdf->Cell(40,10,@numb_format($bagitm,0),1,0,'R',1);
                $yAkhir=$pdf->GetY();
                $xPertama=$pdf->GetX();
                $pdf->SetY($yAkhir);
                $pdf->SetX($xPertama);   
                $drd=1;
                foreach($dtThnBudget as $brsThnBudget)
                {
                    if($drd<$totThn)
                    {
                    $pdf->Cell(40,10,numb_format($totalRptm[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,numb_format($totalbagitm[$brsThnBudget],0),1,0,'R',1);
                    }
                    else
                    {
                    $pdf->Cell(40,10,numb_format($totalRptm[$brsThnBudget],0),1,0,'R',1);
                    $pdf->Cell(40,10,numb_format($totalbagitm[$brsThnBudget],0),1,1,'R',1);
                    }
                    $drd+=1;
                }


                
            $pdf->Output();	
                
                
            break;
                
            default:
            break;
        }
function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}	
?>

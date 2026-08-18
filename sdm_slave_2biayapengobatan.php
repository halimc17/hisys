<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
$param=$_POST;
if(isset($_GET['proses'])!=''){
    if(substr($_GET['proses'],0,5)=='excel'){
        $param=$_GET;
        $tab.= $_SESSION['lang']['biayapengobatan'];
        $brd=1;
        $bgcolor="bgcolor=#DEDEDE";
    }else{
        $param['proses']=$_GET['proses'];
    }
}
$optNmBy=makeOption($dbname, 'sdm_5jenisbiayapengobatan', 'kode,nama');
$optTpkary=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
    if($param['ptId2']!=''){
        $whr.=" and b.kodeorganisasi='".$param['ptId2']."'";
    }
    if($param['unitId2']!=''){
        $whr="";
        $whr.=" and a.kodeorg='".$param['unitId2']."'";
    }
    
    $arrbln=array();
    $arrSmstrSatu=array("01"=>$_SESSION['lang']['jan'],"02"=>$_SESSION['lang']['feb'],"03"=>$_SESSION['lang']['mar'],"04"=>$_SESSION['lang']['apr'],"05"=>$_SESSION['lang']['mei'],"06"=>$_SESSION['lang']['jun']);
    $arrSmstrDua=array("07"=>$_SESSION['lang']['jul'],"08"=>$_SESSION['lang']['agt'],"09"=>$_SESSION['lang']['sep'],"10"=>$_SESSION['lang']['okt'],"11"=>$_SESSION['lang']['nov'],"12"=>$_SESSION['lang']['dec']);
    $arrTahun=array("01"=>$_SESSION['lang']['jan'],"02"=>$_SESSION['lang']['feb'],"03"=>$_SESSION['lang']['mar'],
                    "04"=>$_SESSION['lang']['apr'],"05"=>$_SESSION['lang']['mei'],"06"=>$_SESSION['lang']['jun'],
                    "07"=>$_SESSION['lang']['jul'],"08"=>$_SESSION['lang']['agt'],"09"=>$_SESSION['lang']['sep'],
                    "10"=>$_SESSION['lang']['okt'],"11"=>$_SESSION['lang']['nov'],"12"=>$_SESSION['lang']['dec']);
    if($param['smstr']=='I'){
        $whr.=" and left(a.tanggalbayar,7) between '".$param['thn']."-01' and '".$param['thn']."-06'";
        $groupby="group by a.kodebiaya,left(a.tanggalbayar,7),b.tipekaryawan order by b.tipekaryawan";
        $groupbyDet2="group by left(a.tanggalbayar,7),a.kodeorg,b.tipekaryawan order by b.tipekaryawan";
        $arrbln=$arrSmstrSatu;
        $colspanlvsatu="colspan=7";
        $colspanlvdua="colspan=14";
    }else if ($param['smstr']=='II'){
        $whr.=" and left(a.tanggalbayar,7) between '".$param['thn']."-07' and '".$param['thn']."-12'";
        $groupby="group by a.kodebiaya,left(a.tanggalbayar,7),b.tipekaryawan order by b.tipekaryawan";
        $groupbyDet2="group by left(a.tanggalbayar,7),a.kodeorg,b.tipekaryawan order by b.tipekaryawan";
        $arrbln=$arrSmstrDua;
        $colspanlvsatu="colspan=7";
        $colspanlvdua="colspan=14";
    }
    else {
        $whr.=" and left(a.tanggalbayar,4)='".$param['thn']."'";
        $groupby="group by a.kodebiaya,left(a.tanggalbayar,7),b.tipekaryawan order by b.tipekaryawan";
        $groupbyDet2="group by left(a.tanggalbayar,7),a.kodeorg,b.tipekaryawan order by b.tipekaryawan";
        $arrbln=$arrTahun;
        $colspanlvsatu="colspan=13";
        $colspanlvdua="colspan=28";
    }
    //  
    
    /*$arrbln=array();
    $arrSmstrSatu=array("01"=>$_SESSION['lang']['jan'],"02"=>$_SESSION['lang']['feb'],"03"=>$_SESSION['lang']['mar'],"04"=>$_SESSION['lang']['apr'],"05"=>$_SESSION['lang']['mei'],"06"=>$_SESSION['lang']['jun']);
    $arrSmstrDua=array("07"=>$_SESSION['lang']['jul'],"08"=>$_SESSION['lang']['agt'],"09"=>$_SESSION['lang']['sep'],"10"=>$_SESSION['lang']['okt'],"11"=>$_SESSION['lang']['nov'],"12"=>$_SESSION['lang']['dec']);
    $param['smstr']=='I'?$arrbln=$arrSmstrSatu:$arrbln=$arrSmstrDua;
    */
    
    
    if(($param['proses']=='preview')||($param['proses']=='excel')){
        
        #staff    
        $sstaff="select distinct sum(a.jlhbayar) as jmlhdbyr,a.periode,left(a.tanggalbayar,7) as tglbayar,a.kodeorg,a.kodebiaya 
                 from ".$dbname.".sdm_pengobatanht a 
                 left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                 where a.jlhbayar!=0 ".$whr." and (b.tanggalkeluar = '0000-00-00' or b.tanggalkeluar>= '".date("Y-m-d")."') 
                 and b.tipekaryawan in ('0','7','8') and posting=1
                 ".$groupby." ";
		$qstaff=$owlPDO->query($sstaff) or die(print " Gagal: ".PDOException::getMessage());
		$qstaff->setFetchMode(PDO::FETCH_ASSOC);
        while($rstaff=  $qstaff->fetch()){
            $dtby[$rstaff['kodebiaya'].$rstaff['tglbayar']]=$rstaff['jmlhdbyr'];
            $kdBy[$rstaff['kodebiaya']]=$rstaff['kodebiaya'];
        }
        #non staff
        $snonstaff="select distinct sum(a.jlhbayar) as jmlhdbyr,a.periode,left(a.tanggalbayar,7) as tglbayar,a.kodeorg,a.kodebiaya,b.tipekaryawan 
                    from ".$dbname.".sdm_pengobatanht a 
                    left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                    where a.jlhbayar!=0 ".$whr." and b.tipekaryawan!=0 and posting=1
                    ".$groupby." ";
		$qnonstaff=$owlPDO->query($snonstaff) or die(print " Gagal: ".PDOException::getMessage());
		$qnonstaff->setFetchMode(PDO::FETCH_ASSOC);
        while($rnonstaff=  $qnonstaff->fetch()){
                $dtnonby[$rnonstaff['tipekaryawan']][$rnonstaff['kodebiaya'].$rnonstaff['tglbayar']]=$rnonstaff['jmlhdbyr'];
                $kdBy[$rnonstaff['kodebiaya']]=$rnonstaff['kodebiaya'];
                $tpKary[$rnonstaff['tipekaryawan']]=$rnonstaff['tipekaryawan'];
        }
        
        if($param['proses']!='excel'){
                $brd=0;
                $bgcolor="";
            }else{
                $tab.= $_SESSION['lang']['biayapengobatan'];
                $brd=1;
                $bgcolor="bgcolor=#DEDEDE";
            }
            $tab.="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable width=auto><thead><tr>";
            $tab.="<td rowspan=2>No.</td>";
            $tab.="<td rowspan=2>".$_SESSION['lang']['tipekaryawan']."</td>";
            if(!empty($kdBy)){
                foreach($kdBy as $lstBy){
                    $tab.="<td ".$colspanlvsatu." align=center>".$optNmBy[$lstBy]."</td>";
                }
            }
            $tab.="<td rowspan=2>".$_SESSION['lang']['grnd_total']."</td></tr>";
            $tab.="<tr>";
            if(!empty($kdBy)){
                foreach($kdBy as $lstBy){
                    foreach($arrbln as $lstBln=>$dftrbln){
                        $tab.="<td  align=center>".$dftrbln."</td>";
                    }
                    $tab.="<td  align=center>".$_SESSION['lang']['total']."</td>";
                }
            }
            $tab.="</tr></thead><tbody>";
            $tab.="<tr class=rowcontent>";
            $tab.="<td>1</td>";
            $tab.="<td>Staff</td>";
            if(!empty($kdBy)){
                foreach($kdBy as $lstBy){
                    foreach($arrbln as $lstBln=>$dftrbln){
						$prd=$param['thn']."-".$lstBln;
						setIt($dtby[$lstBy.$prd],0);
						setIt($totPerby[$lstBy],0);
						setIt($grndtotstaff,0);
						setIt($totPerbln[$lstBy.$prd],0);
						setIt($totBiaya[$lstBy],0);
                        //detailDt2
						$det="style='cursor:pointer;' onclick=detailDt2('0','".$lstBy."','".$param['thn']."','".$param['smstr']."','".$param['ptId2']."','".$param['unitId2']."')";    
                        $tab.="<td align=right ".$det.">".number_format($dtby[$lstBy.$prd],0)."</td>";
                        $totPerby[$lstBy]+=$dtby[$lstBy.$prd];
                        $grndtotstaff+=$dtby[$lstBy.$prd];
                        $totPerbln[$lstBy.$prd]+=$dtby[$lstBy.$prd];
                        $totBiaya[$lstBy]+=$dtby[$lstBy.$prd];
                    }
                     $tab.="<td align=right>".number_format($totPerby[$lstBy],0)."</td>";
                }
            }
            $tab.="<td align=right>".number_format(@$grndtotstaff,0)."</td>";
            $tab.="</tr>";
            $now=1;
            if(!empty($tpKary)){
                foreach($tpKary as $lstKary){
                    $now+=1;
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td>".$now."</td>";
                        $tab.="<td>".$optTpkary[$lstKary]."</td>";
                   foreach($kdBy as $lstBy){
                        foreach($arrbln as $lstBln=>$dftrbln){
                            $prd=$param['thn']."-".$lstBln;
							setIt($dtnonby[$lstKary][$lstBy.$prd],0);
							setIt($grndtotnonstaff[$lstKary],0);
							setIt($totPerby2[$lstKary.$lstBy],0);
							setIt($totPerbln[$lstBy.$prd],0);
							setIt($totBiaya[$lstBy],0);
                            $det="style='cursor:pointer;' onclick=detailDt2('".$lstKary."','".$lstBy."','".$param['thn']."','".$param['smstr']."','".$param['ptId2']."','".$param['unitId2']."')";    
                            $tab.="<td align=right ".$det.">".number_format($dtnonby[$lstKary][$lstBy.$prd],0)."</td>";
                            $grndtotnonstaff[$lstKary]+=$dtnonby[$lstKary][$lstBy.$prd];
                            $totPerbln[$lstBy.$prd]+=$dtnonby[$lstKary][$lstBy.$prd];
                            $totPerby2[$lstKary.$lstBy]+=$dtnonby[$lstKary][$lstBy.$prd];
                            $totBiaya[$lstBy]+=$dtnonby[$lstKary][$lstBy.$prd];
                        }
                        $tab.="<td align=right>".number_format($totPerby2[$lstKary.$lstBy],0)."</td>";
                    }
                    $tab.="<td align=right>".number_format($grndtotnonstaff[$lstKary],0)."</td>";
                    $tab.="</tr>";
                }
            }
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=2>".$_SESSION['lang']['grnd_total']."</td>";
			$grndtotsmua=0;
            if(!empty($kdBy)){
                foreach($kdBy as $lstBy){
                    foreach($arrbln as $lstBln=>$dftrbln){
                        $prd=$param['thn']."-".$lstBln;
                        $tab.="<td align=right>".number_format($totPerbln[$lstBy.$prd],0)."</td>";
                        $grndtotsmua+=$totPerbln[$lstBy.$prd];
                    }
                    $tab.="<td align=right>".number_format($totBiaya[$lstBy],0)."</td>";
                }
            }
            $tab.="<td align=right>".number_format($grndtotsmua,0)."</td>";
            $tab.="</tr>";
            $tab.="</tbody></table>";
    }
switch($param['proses'])
{
    case'preview':
        echo $tab;
    break;
    case'level1':
        echo $tab;
    break;
    case'excel':
        if($param['ptId2']==''){
            $param['ptId2']=$_SESSION['lang']['all'];
        }
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="RekapByPengobatan_".$param['ptId2'];
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    case'getDetail2':
        #staff    
        $sstaff="select distinct sum(a.jlhbayar) as jmlhdbyr,a.periode,left(a.tanggalbayar,7) as tglbayar,a.kodeorg,
                 count(a.karyawanid) as jmlorg,b.tipekaryawan 
                 from ".$dbname.".sdm_pengobatanht a 
                 left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                 where a.jlhbayar!=0 ".$whr." and a.kodebiaya='".$param['byPeng']."'
                 ".$groupbyDet2." ";
		$qstaff=$owlPDO->query($sstaff) or die(print " Gagal: ".PDOException::getMessage());
		$qstaff->setFetchMode(PDO::FETCH_ASSOC);
        while($rstaff=  $qstaff->fetch()){
            $dtby[$rstaff['kodeorg'].$rstaff['tipekaryawan'].$rstaff['tglbayar']]=$rstaff['jmlhdbyr'];
            $dtorg[$rstaff['kodeorg'].$rstaff['tipekaryawan'].$rstaff['tglbayar']]=$rstaff['jmlorg'];
            $kdBy[$rstaff['kodeorg']]=$rstaff['kodeorg'];
            $tpKaryDt[$rstaff['tipekaryawan']]=$rstaff['tipekaryawan'];
        }
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead><tr>";
        $tab.="<td rowspan=3>".$_SESSION['lang']['unit']."</td>";
        $tab.="<td rowspan=3>".$_SESSION['lang']['tipekaryawan']."</td>";
        $tab.="<td ".$colspanlvdua."  align=center>".$optNmBy[$param['byPeng']]."</td></tr><tr>";//indra2
        foreach($arrbln as $lstBln=>$dftrbln){
                    $tab.="<td  align=center colspan=2>".$dftrbln."</td>";
        }
        $tab.="<td  align=center colspan=2>".$_SESSION['lang']['total']."</td>";
        $tab.="</tr><tr>";
        foreach($arrbln as $lstBln=>$dftrbln){
                    $tab.="<td  align=center>".$_SESSION['lang']['rp']."</td>";
                    $tab.="<td  align=center>".$_SESSION['lang']['orang']."</td>";
        }
        $tab.="<td  align=center>".$_SESSION['lang']['rp']."</td>";
        $tab.="<td  align=center>".$_SESSION['lang']['orang']."</td>";
        $tab.="</tr></thead><tbody>";
        foreach($kdBy as $lstKdorg){
            
            foreach($tpKaryDt as $dtLstTipekary){
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$lstKdorg."</td>";
                $tab.="<td>".$optTpkary[$dtLstTipekary]."</td>";
             foreach($arrbln as $lstBln=>$dftrbln){
                    $prd=$param['thn']."-".$lstBln;
                    $drilkedua="";
                    if($dtorg[$lstKdorg.$dtLstTipekary.$prd]>0){
                        $drilkedua="style='cursor:pointer;' onclick=detailDt3('".$lstKdorg."','".$dtLstTipekary."','".$prd."','".$param['byPeng']."')";
                    }
                    $tab.="<td  align=right ".$drilkedua.">".number_format($dtby[$lstKdorg.$dtLstTipekary.$prd],0)."</td>";
                    $tab.="<td  align=right ".$drilkedua.">".number_format($dtorg[$lstKdorg.$dtLstTipekary.$prd],0)."</td>";
                        $totRp[$lstKdorg.$dtLstTipekary]+=$dtby[$lstKdorg.$dtLstTipekary.$prd];
                        $totOrg[$lstKdorg.$dtLstTipekary]+=$dtorg[$lstKdorg.$dtLstTipekary.$prd];
                        $totRpBln[$lstBln]+=$dtby[$lstKdorg.$dtLstTipekary.$prd];
                        $totOrgBln[$lstBln]+=$dtorg[$lstKdorg.$dtLstTipekary.$prd];
                    }
                    $tab.="<td  align=right>".number_format($totRp[$lstKdorg.$dtLstTipekary],0)."</td>";
                    $tab.="<td  align=right>".number_format($totOrg[$lstKdorg.$dtLstTipekary],0)."</td>";
                    $tab.="</tr>";
             }
            
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['total']."</td>";
        foreach($arrbln as $lstBln=>$dftrbln){
            $tab.="<td  align=right>".number_format($totRpBln[$lstBln],0)."</td>";
            $tab.="<td  align=right>".number_format($totOrgBln[$lstBln],0)."</td>";
            $grRp+=$totRpBln[$lstBln];
            $grOrg+=$totOrgBln[$lstBln];
        }
        $tab.="<td  align=right>".number_format($grRp,0)."</td>";
        $tab.="<td  align=right>".number_format($grOrg,0)."</td>";
        $tab.="</tr>";
        $tab.="</tbody></table>";
        $tab.="<button class=mybutton onclick=zExcelDt(event,'sdm_slave_2biayapengobatan.php','".$param['tipeKary']."','".$param['byPeng']."','".$param['thn']."','".$param['smstr']."','".$param['ptId2']."','".$param['unitId2']."')>".$_SESSION['lang']['excel']."</button>
               <button class=mybutton onclick=kembali(1)>".$_SESSION['lang']['back']."</button>";
        echo $tab;
    break;
    case'excelgetDetail2':
         #staff    
        $sstaff="select distinct sum(a.jlhbayar) as jmlhdbyr,a.periode,left(a.tanggalbayar,7) as tglbayar,a.kodeorg,
                 count(a.karyawanid) as jmlorg,b.tipekaryawan 
                 from ".$dbname.".sdm_pengobatanht a 
                 left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
                 where a.jlhbayar!=0 ".$whr." and a.kodebiaya='".$param['byPeng']."'
                 ".$groupbyDet2." ";
		$qstaff=$owlPDO->query($sstaff) or die(print " Gagal: ".PDOException::getMessage());
		$qstaff->setFetchMode(PDO::FETCH_ASSOC);
        while($rstaff=  $qstaff->fetch()){
            $dtby[$rstaff['kodeorg'].$rstaff['tipekaryawan'].$rstaff['tglbayar']]=$rstaff['jmlhdbyr'];
            $dtorg[$rstaff['kodeorg'].$rstaff['tipekaryawan'].$rstaff['tglbayar']]=$rstaff['jmlorg'];
            $kdBy[$rstaff['kodeorg']]=$rstaff['kodeorg'];
            $tpKaryDt[$rstaff['tipekaryawan']]=$rstaff['tipekaryawan'];
        }
        $tab.="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable><thead><tr>";
        $tab.="<td rowspan=3  ".$bgcolor.">".$_SESSION['lang']['unit']."</td>";
        $tab.="<td rowspan=3  ".$bgcolor.">".$_SESSION['lang']['tipekaryawan']."</td>";
        $tab.="<td ".$colspanlvdua."  align=center  ".$bgcolor.">".$optNmBy[$param['byPeng']]."</td></tr><tr>";
        foreach($arrbln as $lstBln=>$dftrbln){
                    $tab.="<td  align=center colspan=2  ".$bgcolor.">".$dftrbln."</td>";
        }
        $tab.="<td  align=center colspan=2  ".$bgcolor.">".$_SESSION['lang']['total']."</td>";
        $tab.="</tr><tr>";
        foreach($arrbln as $lstBln=>$dftrbln){
                    $tab.="<td  align=center  ".$bgcolor.">".$_SESSION['lang']['rp']."</td>";
                    $tab.="<td  align=center  ".$bgcolor.">".$_SESSION['lang']['orang']."</td>";
        }
        $tab.="<td  align=center  ".$bgcolor.">".$_SESSION['lang']['rp']."</td>";
        $tab.="<td  align=center  ".$bgcolor.">".$_SESSION['lang']['orang']."</td>";
        $tab.="</tr></thead><tbody>";
        foreach($kdBy as $lstKdorg){
            
            foreach($tpKaryDt as $dtLstTipekary){
                $tab.="<tr class=rowcontent>";
                $tab.="<td>".$lstKdorg."</td>";
                $tab.="<td>".$optTpkary[$dtLstTipekary]."</td>";
             foreach($arrbln as $lstBln=>$dftrbln){
                    $prd=$param['thn']."-".$lstBln;
                    $drilkedua="";
                    if($dtorg[$lstKdorg.$dtLstTipekary.$prd]>0){
                        $drilkedua="style='cursor:pointer;' onclick=detailDt3('".$lstKdorg."','".$dtLstTipekary."','".$prd."','".$param['byPeng']."')";
                    }
                    $tab.="<td  align=right ".$drilkedua.">".number_format($dtby[$lstKdorg.$dtLstTipekary.$prd],0)."</td>";
                    $tab.="<td  align=right ".$drilkedua.">".number_format($dtorg[$lstKdorg.$dtLstTipekary.$prd],0)."</td>";
                        $totRp[$lstKdorg.$dtLstTipekary]+=$dtby[$lstKdorg.$dtLstTipekary.$prd];
                        $totOrg[$lstKdorg.$dtLstTipekary]+=$dtorg[$lstKdorg.$dtLstTipekary.$prd];
                        $totRpBln[$lstBln]+=$dtby[$lstKdorg.$dtLstTipekary.$prd];
                        $totOrgBln[$lstBln]+=$dtorg[$lstKdorg.$dtLstTipekary.$prd];
                    }
                    $tab.="<td  align=right>".number_format($totRp[$lstKdorg.$dtLstTipekary],0)."</td>";
                    $tab.="<td  align=right>".number_format($totOrg[$lstKdorg.$dtLstTipekary],0)."</td>";
                    $tab.="</tr>";
             }
            
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=2>".$_SESSION['lang']['total']."</td>";
        foreach($arrbln as $lstBln=>$dftrbln){
            $tab.="<td  align=right>".number_format($totRpBln[$lstBln],0)."</td>";
            $tab.="<td  align=right>".number_format($totOrgBln[$lstBln],0)."</td>";
            $grRp+=$totRpBln[$lstBln];
            $grOrg+=$totOrgBln[$lstBln];
        }
        $tab.="<td  align=right>".number_format($grRp,0)."</td>";
        $tab.="<td  align=right>".number_format($grOrg,0)."</td>";
        $tab.="</tr>";
        $tab.="</tbody></table>";
         
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="detailPengobatan";
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    case'getDetail3':
 $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>
        <tr class=rowheader>
        <td width=50></td>
        <td>No</td>
        <td width=100>".$_SESSION['lang']['notransaksi']."</td>
        <td width=50>".$_SESSION['lang']['periode']."</td>
        <td width=30>".$_SESSION['lang']['tanggal']."</td>
        <td width=200>".$_SESSION['lang']['lokasitugas']."</td>
        <td width=200>".$_SESSION['lang']['subbagian']."</td>    
        <td width=200>".$_SESSION['lang']['namakaryawan']."</td>
        <td width=200>".$_SESSION['lang']['jabatan']."</td>
        <td>".$_SESSION['lang']['pasien']."</td>
        <td width=150>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pasien']."</td>
        <td width=150>".$_SESSION['lang']['rumahsakit']."</td>
        <td width=50>".$_SESSION['lang']['jenisbiayapengobatan']."</td>
        <td width=90>".$_SESSION['lang']['nilaiklaim']."</td>
        <td>".$_SESSION['lang']['dibayar']."</td>
        <td width=90>".$_SESSION['lang']['perusahaan']."</td>
        <td width=90>".$_SESSION['lang']['karyawan']."</td>
        <td width=90>Jamsostek</td>      
        <td>".$_SESSION['lang']['diagnosa']."</td>
        <td>".$_SESSION['lang']['keterangan']."</td>
    </tr>
    </thead><tbody>"; 
        $str="select a.*, b.*,c.subbagian,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan,c.namakaryawan 
              from ".$dbname.".sdm_pengobatanht a 
              left join ".$dbname.".sdm_5rs b on a.rs=b.id 
              left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid 
              left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id 
              left join ".$dbname.".sdm_karyawankeluarga f on a.ygsakit=f.nomor
              where a.tanggalbayar like '".$param['periode']."%' and a.kodebiaya='".$param['byPeng']."'
              and a.kodeorg = '".$param['unitId2']."' and c.tipekaryawan='".$param['tipeKary']."'
              order by a.jlhbayar desc,a.updatetime desc, a.tanggal desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $no=0;
    while($bar=$res->fetch())
    {
        $no+=1;

        $pasien='';
        //get hubungan keluarga
        $stru="select hubungankeluarga from ".$dbname.".sdm_karyawankeluarga 
              where nomor=".$bar->ygsakit;
		$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
		$resu->setFetchMode(PDO::FETCH_OBJ);
        while($baru=$resu->fetch())
        {
            $pasien=$baru->hubungankeluarga;
        }
        if($pasien=='')$pasien='AsIs';	

        $tab.="<tr class=rowcontent>
            <td>&nbsp <img src=images/zoom.png title='view' class=resicon onclick=previewPengobatan('".$bar->notransaksi."',event)></td>
            <td>".$no."</td>
            <td>".$bar->notransaksi."</td>
            <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>
            <td>".$bar->loktug."</td>
            <td>".$bar->subbagian."</td>    
            <td>".$bar->namakaryawan."</td>
            <td>".$optJabatan[$bar->kodejabatan]."</td>
            <td>".$pasien."</td>
            <td>".$bar->nama."</td>
            <td>".$bar->namars."[".$bar->kota."]"."</td>
            <td>".$bar->kodebiaya."</td>
            <td align=right>".number_format($bar->totalklaim,0,'.',',')."</td>
            <td align=right>".number_format($bar->jlhbayar,0,'.',',')."</td>
            <td align=right>".number_format($bar->bebanperusahaan,0,'.',',')."</td>
            <td align=right>".number_format($bar->bebankaryawan,0,'.',',')."</td>
            <td align=right>".number_format($bar->bebanjamsostek,0,'.',',')."</td>     
            <td>".$bar->ketdiag."</td>
            <td>".$bar->keterangan."</td>
        </tr>";	  	
        $totKlaim+=$bar->totalklaim;
        $totJlhByr+=$bar->jlhbayar;
        $totBbnprshn+=$bar->bebanperusahaan;
        $totBbnKary+=$bar->bebankaryawan;
        $totBbnJam+=$bar->bebanjamsostek;
    }    
    $tab.="<tr class=rowcontent><td colspan=13>".$_SESSION['lang']['total']."</td>";
    $tab.="<td align=right>".number_format($totKlaim,0)."</td>";
    $tab.="<td align=right>".number_format($totJlhByr,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnprshn,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnKary,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnJam,0)."</td>";
    $tab.="<td colspan=2>&nbsp</td></tr>";
    $tab.="</tbody></table>";
        $tab.="<button class=mybutton onclick=zExcelDt2(event,'sdm_slave_2biayapengobatan.php','".$param['unitId2']."','".$param['tipeKary']."','".$param['periode']."','".$param['byPeng']."')>".$_SESSION['lang']['excel']."</button>
               <button class=mybutton onclick=kembali(2)>".$_SESSION['lang']['back']."</button>";
        echo $tab;
    break;
     case'excelgetDetail3':
 $tab.="<table cellpadding=1 cellspacing=1 border=".$brd." class=sortable><thead>
        <tr class=rowheader>
        <td ".$bgcolor." align=center>No</td>
        <td width=100 ".$bgcolor.">".$_SESSION['lang']['notransaksi']."</td>
        <td width=70 ".$bgcolor.">".$_SESSION['lang']['periode']."</td>
        <td width=100 ".$bgcolor.">".$_SESSION['lang']['tanggal']."</td>
        <td width=200 ".$bgcolor.">".$_SESSION['lang']['lokasitugas']."</td>
        <td width=200 ".$bgcolor.">".$_SESSION['lang']['subbagian']."</td>    
        <td width=200 ".$bgcolor.">".$_SESSION['lang']['namakaryawan']."</td>
        <td width=200 ".$bgcolor.">".$_SESSION['lang']['jabatan']."</td>
        <td ".$bgcolor.">".$_SESSION['lang']['pasien']."</td>
        <td width=150 ".$bgcolor.">".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pasien']."</td>
        <td width=150 ".$bgcolor.">".$_SESSION['lang']['rumahsakit']."</td>
        <td width=150 ".$bgcolor.">".$_SESSION['lang']['jenisbiayapengobatan']."</td>
        <td width=90 ".$bgcolor.">".$_SESSION['lang']['nilaiklaim']."</td>
        <td ".$bgcolor.">".$_SESSION['lang']['dibayar']."</td>
        <td width=90 ".$bgcolor.">".$_SESSION['lang']['perusahaan']."</td>
        <td width=90 ".$bgcolor.">".$_SESSION['lang']['karyawan']."</td>
        <td width=90 ".$bgcolor.">Jamsostek</td>      
        <td ".$bgcolor.">".$_SESSION['lang']['diagnosa']."</td>
        <td ".$bgcolor.">".$_SESSION['lang']['keterangan']."</td>
    </tr>
    </thead><tbody>"; 
        $str="select a.*, b.*,c.subbagian,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan,c.namakaryawan 
              from ".$dbname.".sdm_pengobatanht a left join ".$dbname.".sdm_5rs b on a.rs=b.id 
              left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid 
              left join ".$dbname.".sdm_5diagnosa d on a.diagnosa=d.id 
              left join ".$dbname.".sdm_karyawankeluarga f
              on a.ygsakit=f.nomor
              where a.tanggalbayar like '".$param['periode']."%' and a.kodebiaya='".$param['byPeng']."'
              and a.kodeorg = '".$param['unitId2']."' and c.tipekaryawan='".$param['tipeKary']."'
              order by a.jlhbayar desc,a.updatetime desc, a.tanggal desc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
    $no=0;
    while($bar=$res->fetch())
    {
        $no+=1;

        $pasien='';
        //get hubungan keluarga
        $stru="select hubungankeluarga from ".$dbname.".sdm_karyawankeluarga 
              where nomor=".$bar->ygsakit;
		$resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
		$resu->setFetchMode(PDO::FETCH_OBJ);
        while($baru=$resu->fetch())
        {
            $pasien=$baru->hubungankeluarga;
        }
        if($pasien=='')$pasien='AsIs';	
		
        $tab.="<tr class=rowcontent>
            <td>".$no."</td>
            <td>".$bar->notransaksi."</td>
            <td>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>
            <td>".$bar->loktug."</td>
            <td>".$bar->subbagian."</td>    
            <td>".$bar->namakaryawan."</td>
            <td>".$optJabatan[$bar->kodejabatan]."</td>
            <td>".$pasien."</td>
            <td>".$bar->nama."</td>
            <td>".$bar->namars."[".$bar->kota."]"."</td>
            <td>".$bar->kodebiaya."</td>
            <td align=right>".$bar->totalklaim."</td>
            <td align=right>".$bar->jlhbayar."</td>
            <td align=right>".$bar->bebanperusahaan."</td>
            <td align=right>".$bar->bebankaryawan."</td>
            <td align=right>".$bar->bebanjamsostek."</td>     
            <td>".$bar->ketdiag."</td>
            <td>".$bar->keterangan."</td>
        </tr>";	  	
        $totKlaim+=$bar->totalklaim;
        $totJlhByr+=$bar->jlhbayar;
        $totBbnprshn+=$bar->bebanperusahaan;
        $totBbnKary+=$bar->bebankaryawan;
        $totBbnJam+=$bar->bebanjamsostek;
    }    
    $tab.="<tr class=rowcontent><td colspan=12>".$_SESSION['lang']['total']."</td>";
    $tab.="<td align=right>".number_format($totKlaim,0)."</td>";
    $tab.="<td align=right>".number_format($totJlhByr,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnprshn,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnKary,0)."</td>";
    $tab.="<td align=right>".number_format($totBbnJam,0)."</td>";
    $tab.="<td colspan=2>&nbsp</td></tr>";
    $tab.="</tbody></table>";
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $nop_="detailPengobatan2_".$param['ptId2'];
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$nop_.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$nop_.".xls';
                </script>";
            }
            fclose($handle);
        }
    break;
    
}
?>
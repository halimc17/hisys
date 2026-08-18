<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$jab          = getPostingJabatan('budget');
$thnAngrn     =checkPostGet('thnAngrn','');
$afdId        =checkPostGet('afdId','');
$proses       =checkPostGet('proses','');
$haThnLalu    =checkPostGet('haThnLalu','');
$kdBlok       =checkPostGet('kdBlok','');
$haThnIni     =checkPostGet('haThnIni','');
$pkkThnLalu   =checkPostGet('pkkThnLalu','');
$pokokThnIni  =checkPostGet('pokokThnIni','');
$statBlok     =checkPostGet('statBlok','');
$topoGrafi    =checkPostGet('topoGrafi','');
$thnTmn       =checkPostGet('thnTmn','');
$haNon        =checkPostGet('haNon','');
$jmlh         =checkPostGet('jmlh','');
$pkkProduktif =checkPostGet('pkkProduktif','');
$pkkProdukBr  =checkPostGet('pkkProdukBr','');
$thnAngBr     =checkPostGet('thnAngBr','');
$afdIdBr      =checkPostGet('afdIdBr','');
$kdBlokBr     =checkPostGet('kdBlokBr','');
$haThnIniBr   =checkPostGet('haThnIniBr','');
$topoGrafiBr  =checkPostGet('topoGrafiBr','');
$thnTmnBr     =checkPostGet('thnTmnBr','');
$pokokThnIniBr=checkPostGet('pokokThnIniBr','');
$haNonBr      =checkPostGet('haNonBr','');
$statBlokBr   =checkPostGet('statBlokBr','');
$sumber       =checkPostGet('sumber','');
$arrStatusBlok=getEnum($dbname,'setup_blok','statusblok');
$optTopografi =makeOption($dbname, 'setup_topografi','topografi,keterangan');
$optTopografi2=makeOption($dbname, 'setup_topografi','keterangan,topografi');
$sumber       =checkPostGet('sumber','');
$thnAngrnOld  =checkPostGet('thnAngrnOld','');
$oldBlok      =checkPostGet('oldBlok','');
$topoGrafOld  =checkPostGet('topoGrafOld','');
$lcThnini     =checkPostGet('lcThnini','');
$lcThnBr      =checkPostGet('lcThnBr','');
$a=1;
$b=1;
$totTpGrafot=0;
foreach ($arrStatusBlok as $brs){
    $dtBlok[$a]=$brs;
    $a++;
}
foreach ($optTopografi2 as $brsTp){
    $dtTpgr[$b]=$brsTp;
    $b++;
}

$tot      =count($dtBlok);
$totTpGraf=count($dtTpgr);
if($jmlh>=1){
    if($thnAngBr!=''){  
        $sCekOpt="select tahunbudget,kodeblok,statusblok,topografi from ".$dbname.".bgt_blok  
        where kodeblok like '%".$afdIdBr."%' and tahunbudget='".$thnAngBr."' and sumber='BARU' order by statusblok desc"; 
    }else{   
		$sCekOpt="select tahunbudget,kodeblok,statusblok from ".$dbname.".bgt_blok  
        where kodeblok like '%".$afdId."%' and tahunbudget='".$thnAngrn."' and sumber='LAMA' order by statusblok desc";
    }
	
	$res = fetchdata($sCekOpt);
	$rowCekBrs=count($res);
	if(($rowCekBrs==0)||($thnAngBr!='')){
		for($c=1;$c<=$totTpGraf;$c++){
			$arrOptTopo[$afdIdBr].="<option value='".$dtTpgr[$c]."' >".$optTopografi[$dtTpgr[$c]]."</option>"; 
		}
		for($x=1;$x<=$tot;$x++){
			$arrOptBlok[$afdIdBr].="<option value='".$dtBlok[$x]."' >".$dtBlok[$x]."</option>"; 
		}  
	}else{
		foreach($res as $rCek){
			if($thnAngBr!=''){
				for($c=1;$c<=$totTpGraf;$c++){
				  if($dtTpgr[$c]==$rCek['topografi']){
						$arrOptTopo[$afdIdBr].="<option value='".$dtTpgr[$c]."' selected>".$optTopografi[$dtTpgr[$c]]."</option>"; 
					}else{
						$arrOptTopo[$afdIdBr].="<option value='".$dtTpgr[$c]."' >".$optTopografi[$dtTpgr[$c]]."</option>"; 
					}
				}
				 for($x=1;$x<=$tot;$x++){
					if($dtBlok[$x]==$rCek['statusblok']){
						$arrOptBlok[$afdIdBr].="<option value='".$dtBlok[$x]."' selected>".$dtBlok[$x]."</option>"; 
					}else{
						$arrOptBlok[$afdIdBr].="<option value='".$dtBlok[$x]."' >".$dtBlok[$x]."</option>"; 
					}
				 }  
			}
			for($x=1;$x<=$tot;$x++){
				if($dtBlok[$x]==$rCek['statusblok']){
					$arrOptBlok[$rCek['tahunbudget']][$rCek['kodeblok']].="<option value='".$dtBlok[$x]."' selected>".$dtBlok[$x]."</option>"; 
				}else{
					$arrOptBlok[$rCek['tahunbudget']][$rCek['kodeblok']].="<option value='".$dtBlok[$x]."' >".$dtBlok[$x]."</option>"; 
				}
			}       
			if($x==$tot){
				$x=1;
			}
			if($c==$totTpGrafot){
				$c=1;
			}
		}
	}
}elseif($jmlh==0){
    if($thnAngBr!='') {
		$sCekOpt="select tahunbudget,kodeblok,statusblok,topografi from ".$dbname.".bgt_blok 
        where kodeblok like '%".$afdIdBr."%' and sumber='BARU' and tahunbudget='".$thnAngBr."' order by statusblok desc";
    }else{
		$sCekOpt="select tahuntanam as tahunbudget,kodeorg as kodeblok,statusblok from ".$dbname.".setup_blok 
        where kodeorg like '%".$afdId."%' and luasareaproduktif>0 order by statusblok desc";
    }
	$res = fetchdata($sCekOpt);
	foreach($res as $rCek){
		if($thnAngBr!=''){
			for($c=1;$c<=$totTpGraf;$c++){
			  if($dtTpgr[$c]==$rCek['topografi']){
					$arrOptTopo[$afdIdBr].="<option value='".$dtTpgr[$c]."' selected>".$optTopografi[$dtTpgr[$c]]."</option>"; 
				}else{
					$arrOptTopo[$afdIdBr].="<option value='".$dtTpgr[$c]."' >".$optTopografi[$dtTpgr[$c]]."</option>"; 
				}
			}
			for($x=1;$x<=$tot;$x++){
				if($dtBlok[$x]==$rCek['statusblok']){
					$arrOptBlok[$afdIdBr].="<option value='".$dtBlok[$x]."' selected>".$dtBlok[$x]."</option>"; 
				}else{
					$arrOptBlok[$afdIdBr].="<option value='".$dtBlok[$x]."' >".$dtBlok[$x]."</option>"; 
				}
			}     
		}else{
			for($x=1;$x<=$tot;$x++){
				setIt($arrOptBlok[$rCek['tahunbudget']][$rCek['kodeblok']],'');
				if($dtBlok[$x]==$rCek['statusblok']){
					$arrOptBlok[$rCek['tahunbudget']][$rCek['kodeblok']].="<option value='".$dtBlok[$x]."' selected>".$dtBlok[$x]."</option>"; 
				}else{
					$arrOptBlok[$rCek['tahunbudget']][$rCek['kodeblok']].="<option value='".$dtBlok[$x]."' >".$dtBlok[$x]."</option>"; 
				}
			}       
		}
		if($x==$tot){
			$x=1;
		}
		if($c==$totTpGrafot){
			$c=1;
		}
	}
}

switch($proses){
	case'posting':
		try{
		$owlPDO->beginTransaction();
			
			$str = "update " . $dbname . ".bgt_blok set closed ='1' where tahunbudget = '".$param['tahunbudget']."' and kodeblok  like '".$param['divisi']."%'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'unposting':
		try{
		$owlPDO->beginTransaction();
			
			$str = "update " . $dbname . ".bgt_blok set closed ='0' where tahunbudget = '".$param['tahunbudget']."' and kodeblok  like '".$param['divisi']."%'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'getdivisi':
		$optData="<option value=''></option>";
		$str="select * from ".$dbname.".organisasi where induk='".$param['kodeorg']."' and tipe in ('AFDELING','BIBITAN') order by kodeorganisasi asc";
		$res = fetchdata($str);
		foreach($res as $rThn){
			$optData.="<option value='".$rThn['kodeorganisasi']."'>".$rThn['kodeorganisasi']." - ".$rThn['namaorganisasi']."</option>";
		}
		echo $optData;
	break;
	case'cekData':
		if($thnAngrn==''||$afdId==''){
			exit("Error : Required field is missing");
		}
		$str="select distinct tahunbudget from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and kodeblok like '".$afdId."%' and closed=1";
		$res = fetchdata($str);
		$rThnCek=count($res);
		if($rThnCek<1){
			$thn=date("Y");
			if($thnAngrn==''){
				exit("Error : Budget year required");
			}elseif(strlen($thnAngrn)<4){
				exit("Error : Budget year incorrect");
			}
			if(substr($thn,0,1)!=substr($thnAngrn,0,1)){
				exit("Error : Budget year incorrect");
			}
			$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and kodeblok like '%".$afdId."%' and sumber='LAMA'";
			$res = fetchdata($str);
			echo count($res);
		}else{
			exit("Error : Budget has been closed");
		}
	break;
    case'getPreview':
		$arrPlasma=array("I"=>"Inti","P"=>"Plasma");
		$optPlasma="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrPlasma as $lsPlasma=>$txtPlasma){
			$optPlasma.="<option value='".$lsPlasma."'>".$txtPlasma."</option>";
		}
		$tab="";
        if($jmlh<1){
            $str="select tahuntanam as tahunbudget,statusblok,luasareaproduktif,jumlahpokok,topografi,kodeorg,tahuntanam,intiplasma from ".$dbname.".setup_blok where kodeorg like '%".$afdId."%' and luasareaproduktif>0";
            
        }elseif($jmlh>1){
            $str="select tahunbudget, kodeblok as kodeorg, hathnlalu as luasareaproduktif, hathnini, pokokthnlalu as jumlahpokok, pokokthnini , 
			statusblok, topografi,thntnm as tahuntanam,hanonproduktif,lcthnini,intiplasma,pokokproduksi
			from ".$dbname.".bgt_blok
			where kodeblok like '%".$afdId."%' and tahunbudget='".$thnAngrn."' and sumber='LAMA'";
        }
        $tot=count($arrStatusBlok);
        $b=1;$no=0;
		$r = fetchdata($str);
		foreach($r as $res){
			$no+=1;
			$statDtPlsma="";
			if($res['intiplasma']=='P'){$statDtPlsma="checked";}
			if(!isset($res['pokokthnini']) or $res['pokokthnini']==''){$res['pokokthnini']=$res['jumlahpokok'];}
			if($res['pokokproduksi']==''){$res['pokokproduksi']=$res['pokokthnini'];}
			if($res['hathnini']==''){$res['hathnini']=$res['luasareaproduktif'];}
			if($res['lcthnini']==''){$res['lcthnini']=0;}
			if($res['hanonproduktif']==''){$res['hanonproduktif']=0;}
			if($res['pokokproduksi']==''){$res['pokokproduksi']=0;}
			
			$tab.="<tr class=rowcontent id=rew_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$thnAngrn."</td><td style=display:none id=kdBlok_".$no.">".$res['kodeorg']."</td><td>".getNamaOrg($res['kodeorg'])."</td>";
			$tab.="<td id='topoGrafi_".$no."'>".$res['topografi']."-".$optTopografi[$res['topografi']]."</td>";
			$tab.="<td id='thnTmn_".$no."' align='center'>".$res['tahuntanam']."</td>";
			$tab.="<td align=right onclick='getData(".$no.")' style='cursor:pointer;' id=luas_".$no.">".$res['luasareaproduktif']."</td>";
			$tab.="<td><input type=text style=width:75px id=hathnIni_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' onkeyup=hitungttl('hathnIni_','ttlhathini'); value='".$res['hathnini']."'></td>";
			$tab.="<td align=right onclick='getDatab(".$no.")' style='cursor:pointer;' id=pkk_".$no.">".$res['jumlahpokok']."</td>";
			$tab.="<td><input style=width:75px type=text id=pokokThnINi_".$no." class=myinputtextnumber onkeypress='return angka_doang(event)' onkeyup=hitungttl('pokokThnINi_','ttlpkkthini'); onblur='cekThis(".$no.")' value='".$res['pokokthnini']."'></td>";
			$tab.="<td><select id=statBlok_".$no.">".$arrOptBlok[$res['tahunbudget']][$res['kodeorg']]."</select></td>";
			
			
			$tab.="<td><input style=width:75px type=text id=lcThn_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' onkeyup=hitungttl('lcThn_','ttllcthini'); value='".$res['lcthnini']."'></td>";
			$tab.="<td><input style=width:75px type=text id=haNon_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' onkeyup=hitungttl('haNon_','ttlhanon'); value='".$res['hanonproduktif']."'></td>";
			$tab.="<td><input style=width:75px type=text id=pkkProduk_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' onkeyup=hitungttl('pkkProduk_','ttlpkprd'); value='".$res['pokokproduksi']."' onblur='cekThis(".$no.")'  /></td>";
			$tab.="<td align=center><input type=checkbox  id='statPlasma_".$no."' title='clik jika plasma' ".$statDtPlsma." /></td>";
			$tab.="</tr>";
			
			$ttlhalalu+=$res['luasareaproduktif'];
			$ttlhathini+=$res['hathnini'];
			$ttlpkklalu+=$res['jumlahpokok'];
			$ttlpkkthini+=$res['pokokthnini'];
			$ttllcthini+=$res['lcthnini'];
			$ttlhanon+=$res['hanonproduktif'];
			$ttlpkprd+=$res['pokokproduksi'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan='5' align=center>TOTAL</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlhalalu,2)."</td>";
		$tab.="<td align=right id=ttlhathini>".hidezerodecimal($ttlhathini,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlpkklalu)."</td>";
		$tab.="<td align=right id=ttlpkkthini>".hidezerodecimal($ttlpkkthini)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right id=ttllcthini>".hidezerodecimal($ttllcthini,2)."</td>";
		$tab.="<td align=right id=ttlhanon>".hidezerodecimal($ttlhanon,2)."</td>";
		$tab.="<td align=right id=ttlpkprd>".hidezerodecimal($ttlpkprd)."</td>";
		$tab.="<td align=right></td>";
		$tab.="</tr>";
			
        $tab.="<tr><td colspan='14' align=center>
			<button class=mybutton  onclick=saveAll(1)  style=cursor:pointer;>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button>
			<!--<button class=mybutton  onclick=batalsimpan()  style=cursor:pointer;>".$_SESSION['lang']['cancel']."</button>-->
			</td></tr>";
        $tab.="<input type=hidden id=jmlhRow value=".$no." />";
        
		echo $tab;
    break;
	case'insertAll':
	try{
		$owlPDO->beginTransaction();
		
		$thn=date("Y");
		if($thnAngrn==''){
			throw new PDOException("Budget year required");
		}elseif(strlen($thnAngrn)<4){
			throw new PDOException("Budget year incorrect");
		}
		if(substr($thn,0,1)!=substr($thnAngrn,0,1)){
			throw new PDOException("Budget year incorrect");
		}
		$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and kodeblok='".$kdBlok."' and topografi='".$topoGrafi."' and sumber='LAMA' and closed=1";
		$res = fetchdata($str);
		$rCek = count($res);
			
		$haNon==''?$haNon=0:$haNon=$haNon;
		$hamutasi   =$haThnIni-$haThnLalu;
		$pokokMutasi=$pokokThnIni-$pkkThnLalu;
		
		if($pokokThnIni==''||$haThnIni==''){
			$sDel="delete from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and substr(kodeblok,1,6)='".substr($kdBlok,0,6)."' and sumber='LAMA'";
			
			$owlPDO->exec($sDel); 
			throw new PDOException("Ha this year and ha last year are required");
	    }
		if($rCek==0){
			$str="select topografi from ".$dbname.".setup_topografi where topografi='".$topoGrafi."'";
			$res = fetchdata($str)[0];
			if($res['topografi']!=''){
				$tmbhn=''; $tmbhn2='';
				
				if($param['statPlsma']=='P'){
					$tmbhn=",`intiplasma`";
					$tmbhn2=",'".$param['statPlsma']."'";
				}
				$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and kodeblok='".$kdBlok."' and topografi='".$topoGrafi."' and sumber='LAMA'";
				$res = fetchdata($str);
				if(count($res)>0){
					$str="delete from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and kodeblok='".$kdBlok."' and topografi='".$topoGrafi."' and sumber='LAMA'";
					$owlPDO->exec($str); 
					
					$str="insert into ".$dbname.".bgt_blok (tahunbudget, kodeblok, hathnlalu, hathnini, pokokthnlalu, pokokthnini, statusblok, topografi, thntnm, hanonproduktif, sumber, updateby,hamutasi,pokokmutasi,pokokproduksi,lcthnini".$tmbhn.") 
					value ('".$thnAngrn."','".$kdBlok."','".$haThnLalu."','".$haThnIni."','".$pkkThnLalu."','".$pokokThnIni."','".$statBlok."','".$topoGrafi."','".$thnTmn."','".$haNon."','LAMA','".$_SESSION['standard']['userid']."','".$hamutasi."','".$pokokMutasi."','".$pkkProduktif."','".$lcThnini."'".$tmbhn2.")";
					$owlPDO->exec($str);
				}else{
					$str="insert into ".$dbname.".bgt_blok (tahunbudget, kodeblok, hathnlalu, hathnini, pokokthnlalu, pokokthnini, statusblok, topografi, thntnm, hanonproduktif, sumber, updateby,hamutasi,pokokmutasi,pokokproduksi,lcthnini".$tmbhn.") 
					value ('".$thnAngrn."','".$kdBlok."','".$haThnLalu."','".$haThnIni."','".$pkkThnLalu."','".$pokokThnIni."','".$statBlok."','".$topoGrafi."','".$thnTmn."','".$haNon."','LAMA','".$_SESSION['standard']['userid']."','".$hamutasi."','".$pokokMutasi."','".$pkkProduktif."','".$lcThnini."'".$tmbhn2.")";
					$owlPDO->exec($str);
				}
			}else{
				$str="delete from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrn."' and substr(kodeblok,1,6)='".substr($kdBlok,0,6)."' and sumber='LAMA'";
				$owlPDO->exec($str); 
				
				throw new PDOException("Topography required, please input topography from Setup Block menu.");
			}
		}else{
			throw new PDOException("Budget has been closed");
		}
		
		$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'cekDataBr':
		if($thnAngBr==''||$afdIdBr==''){
			exit("Error : Required field are missing");
		}
		$str="select distinct tahunbudget from ".$dbname.".bgt_blok where tahunbudget='".$thnAngBr."' and kodeblok like '".$afdIdBr."%' and closed=1";
		$res = fetchdata($str);
		if(count($res)<1){
			$thn=date("Y");
            if($thnAngBr==''){
                exit("Error : Budget year required");
            }elseif(strlen($thnAngBr)<4){
                exit("Error : Budget year incorrect");
            }
            if(substr($thn,0,1)!=substr($thnAngBr,0,1)){
                exit("Error : Budget year incorrect");
            }
            $tab="";
			$b=1;
			$kbn=substr($afdIdBr,0,4);
			$afd=substr($afdIdBr,4,2);
			
			$no=1;
			$tab.="<tr class=rowcontent id=rewBr_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id=thn_".$no.">".$thnAngBr."</td>";
			$tab.="<td  align=center id=kdKbn_".$no.">".$kbn."</td>";
			$tab.="<td><input type='text' id='kdAfdling_".$no."' value='".$afd."' class='myinputtextnumber' style='width:50px' maxlength=2 onkeypress='return tanpa_kutip(event)' disabled /></td>";
			$tab.="<td><input type='text' onkeydown=\"upperCaseF(this)\" id='kdBlokBr_".$no."' class='myinputtext' style='width:50px' maxlength=10 onkeypress='return tanpa_kutip(event)' /></td>";
			$tab.="<td><input type=text id=hathnIniBr_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' style='width:50px' /></td>";
			$tab.="<td><input type=text id=pokokThnINiBr_".$no." class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' style='width:50px'  onblur='cekThisBr(".$no.")' /></td>";
			$tab.="<td><select id=statBlokBr_".$no.">".$arrOptBlok[$afdIdBr]."</select></td>";
			$tab.="<td><select id='topoGrafiBr_".$no."'>".$arrOptTopo[$afdIdBr]."</select></td><td><input type='text' id='thnTmnBr_".$no."' class=myinputtextnumber onkeypress='return angka_doang(event)'  style='width:50px' maxlength='4' /></td>";
			$tab.="<td><input type=text id=lcThnBr_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' style='width:50px' /></td>";
			$tab.="<td><input type=text id=haNonBr_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' style='width:50px' /></td>";
			$tab.="<td><input style='width:50px' type=text id=pkkProdukBr_".$no."  class=myinputtextnumber onkeypress='return angka_doang(event)' value='0' onblur='cekThisBr(".$no.")'  /></td>";
			$tab.="<td align=center><input type=checkbox  id='statPlasmaBr_".$no."' title='clik jika plasma' ".$statDtPlsma." /></td>";
			$tab.="<td align=center style='cursor:pointer;'><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail(".$no.")\" src='images/save.png'/></td>"; 
			$tab.="</tr>";
         
			$tab.="</tbody></table><input type=hidden id=jmlhRow value=".$no." /> <input type=hidden id=thnAngrnOld value=''  /><input type=hidden id=oldBlok value=''   /><input type=hidden id=topoGrafOld value=''  />";
			echo $tab;
		}else{
			exit("Error:Tahun Budget Sudah Tutup");
		}

	break;
	case'insertAllBr':
		// echo"<pre>";
		// print_r($param);
		
		// exit("error");
		try{
		$owlPDO->beginTransaction();
		
		if(strlen($kdBlokBr)<10){
			throw new PDOException("Block code required");
		}
		if($thnTmnBr==''){
			throw new PDOException("Panting year required");
		}elseif(strlen($thnTmnBr)<4){
			throw new PDOException("Panting year incorrect");
		}
		
		if($haThnIniBr==''||$pokokThnIniBr==''){
			throw new PDOException("Ha this year and numbers of trees are required");
		}
		$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrnOld."' and kodeblok like '".substr($oldBlok,0,4)."%' and  closed=1";
		if(count(fetchdata($str))>0){
			throw new PDOException("Budget telah di tutup.");
		}
		
		$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrnOld."' and kodeblok='".$oldBlok."' and topografi='".$topoGrafOld."' and sumber='BARU'";
		if(count(fetchdata($str))>0){
			#update
			$str="delete from ".$dbname.".bgt_blok where tahunbudget='".$thnAngrnOld."' and kodeblok='".$oldBlok."' and topografi='".$topoGrafOld."' and sumber='BARU'";
			$owlPDO->exec($str);
			if($statBlokBr=='TM'){
				if($pkkProdukBr>$pokokThnIniBr){
					throw new PDOException("Number of productive trees should not be more than the amount of this year");
				}
			}
			$tmbhn="";$tmbhn2="";
			if($param['statPlasmaBr']=='P'){
				$tmbhn=",`intiplasma`";
				$tmbhn2=",'".$param['statPlasmaBr']."'";
			}
			$str="insert into ".$dbname.".bgt_blok (tahunbudget, kodeblok,  hathnini,  pokokthnini, statusblok, topografi, thntnm, hanonproduktif, sumber, updateby,pokokproduksi,lcthnini".$tmbhn.") 
				  value ('".$thnAngBr."','".$kdBlokBr."','".$haThnIniBr."','".$pokokThnIniBr."','".$statBlokBr."','".$topoGrafiBr."','".$thnTmnBr."','".$haNonBr."','BARU','".$_SESSION['standard']['userid']."','".$pkkProdukBr."','".$lcThnBr."'".$tmbhn2.")";
			$owlPDO->exec($str);
	   }else{
			if($pkkProdukBr>$pokokThnIniBr){
				throw new PDOException("Number of productive trees should not be more than the amount of this year");
			}
			$str="insert into ".$dbname.".bgt_blok (tahunbudget, kodeblok,  hathnini,  pokokthnini, statusblok, topografi, thntnm, hanonproduktif, sumber, updateby,pokokproduksi) 
			value ('".$thnAngBr."','".$kdBlokBr."','".$haThnIniBr."','".$pokokThnIniBr."','".$statBlokBr."','".$topoGrafiBr."','".$thnTmnBr."','".$haNonBr."','BARU','".$_SESSION['standard']['userid']."','".$pkkProdukBr."')";
			$owlPDO->exec($str);
	   }
	   
		$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'loadData':
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		
		$str="select * from ".$dbname.".bgt_blok where sumber='BARU' and kodeblok like '".$param['afdIdBr']."%' and tahunbudget='".$param['thnAngBr']."' order by tahunbudget desc";
		$res = fetchdata($str); $no2=0;
		foreach($res as $bar){
			$kbn=substr($bar['kodeblok'],0,4);
			$afd=substr($bar['kodeblok'],0,6);
			$blk=$bar['kodeblok'];
			
			$no2+=1;
			$tab.="<tr class=rowcontent id=rewBr_".$no2.">";
			$tab.="<td align=center>".$no2."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td hidden id=kdKbn_".$no2.">".$kbn." - ".$nmorg[$kbn]."</td>";
			//$tab.="<td>".$afd." - ".$nmorg[$afd]."</td>";
			$tab.="<td>".$blk."</td>";
			$tab.="<td align=right>".$bar['hathnini']."</td>";
			$tab.="<td align=right>".$bar['pokokthnini']."</td>";
			$tab.="<td>".$arrStatusBlok[$bar['statusblok']]."</td>";
			$tab.="<td>".$optTopografi[$bar['topografi']]."</td><td id='thnTmnBr_".$no2."' align='center'>".$bar['thntnm']."</td>";
			$tab.="<td align=right>".$bar['lcthnini']."</td>";
			$tab.="<td align=right>".$bar['hanonproduktif']."</td>";
			$tab.="<td align=right>".$bar['pokokproduksi']."</td>";
			if($bar['closed']!=1){
				$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$bar['tahunbudget']."','".$kbn."','".substr($afd,4,2)."','".substr($bar['kodeblok'],-4)."','".$bar['hathnini']."','".$bar['pokokthnini']."','".$bar['statusblok']."','".$bar['topografi']."','".$bar['thntnm']."','".$bar['lcthnini']."','".$bar['hanonproduktif']."','".$bar['pokokproduksi']."','".$bar['intiplasma']."','".$bar['kodeblok']."');\"></td>";
				
				$tab.="<td align=center style='cursor:pointer;width:25px'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"delbaru('".$bar['tahunbudget']."','".$bar['kodeblok']."')\" src='images/application/application_delete.png'/></td>";
			}else{ 
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
			}
			$tab.="</tr>";
		
			$ttlhathini+=$bar['hathnini'];
			$ttlpkklalu+=$bar['jumlahpokok'];
			$ttlpkkthini+=$bar['pokokthnini'];
			$ttllcthini+=$bar['lcthnini'];
			$ttlhanon+=$bar['hanonproduktif'];
			$ttlpkprd+=$bar['pokokproduksi'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan='3' align=center>TOTAL</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlhathini,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlpkkthini)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right>".hidezerodecimal($ttllcthini,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlhanon,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlpkprd)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="</tr>";
		
		echo $tab;
	break;
	case'delbaru':
		$str="delete from ".$dbname.".bgt_blok where tahunbudget='".$param['tahun']."' and kodeblok='".$param['blok']."'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'delete':
		$str="delete from ".$dbname.".bgt_blok where tahunbudget='".$param['tahun']."' and kodeblok like '".$param['divisi']."%'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'loadDataLama':
		$where = "";
		if($param['thnbgt']!=''){
			$where.=" and tahunbudget = '".$param['thnbgt']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeblok like '".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and kodeblok like '".$param['divisi']."%'";
		}
		
		$tab = "";
		$limit= 10;
		$page = 0;
        $param['page'] = isset($param['page']) ? $param['page'] : '0';
        if (isset($param['page'])) {$page = intval($param['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 15;
		
		$sql = "select distinct * from ".$dbname.".bgt_blok where substr(kodeblok,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,substr(kodeblok,1,6)";
        $jlhbrs = count(fetchdata($sql));
		

		$str="select distinct substring(kodeblok,1,6) as kodeblokx,tahunbudget,closed,sumber, sum(hathnlalu) as hathnlalu, sum(hamutasi) as hamutasi, sum(hathnini) as hathnini, sum(pokokthnlalu) as pokokthnlalu, sum(pokokmutasi) as pokokmutasi, sum(pokokthnini) as pokokthnini, sum(hanonproduktif) as hanonproduktif, sum(pokokproduksi) as pokokproduksi, sum(lcthnini) as lcthnini   from ".$dbname.".bgt_blok where  substr(kodeblok,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,kodeblokx order by tahunbudget desc  limit ".$offset.",".$limit."";
		$res=fetchdata($str); $no=0;
		foreach($res as $bar){
			$sJum="select count(kodeblok) as jmlh from ".$dbname.".bgt_blok where sumber='LAMA' and  kodeblok like '%".$bar['kodeblokx']."%'";
			$qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
			$qJum->setFetchMode(PDO::FETCH_ASSOC);         
			$rJum=$qJum->fetch();
		   
			$kbn=substr($bar['kodeblokx'],0,4);
			$no+=1;
			$bar['closed']==1?$stat="Close":$stat="Open";
			
			$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$kbn."' or kodeorganisasi='".$bar['kodeblokx']."'");
			
			$tab.="<tr class=rowcontent id=rewBr_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td id=kdKbn_".$no.">".$kbn." - ".$nmorg[$kbn]."</td>";
			$tab.="<td>".$bar['kodeblokx']." - ".$nmorg[$bar['kodeblokx']]."</td>";
			$tab.="<td align=right>".number_format($bar['hathnini'])."</td>";
			$tab.="<td align=right>".number_format($bar['pokokthnini'])."</td>";
			$tab.="<td align=right>".number_format($bar['lcthnini'])."</td>";
			$tab.="<td align=right>".number_format($bar['hanonproduktif'])."</td>";
			$tab.="<td align=right>".number_format($bar['pokokproduksi'])."</td>";
			$tab.="<td align=center>".$stat."</td>";
			if($bar['closed']==0){
				$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editData('".$bar['tahunbudget']."','".$kbn."','".$bar['kodeblokx']."');\"></td>";
				
				$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$bar['tahunbudget']."','".$bar['kodeblokx']."');\" title='Delete'></td>";
					
				$tab.="<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$bar['tahunbudget']."','".$bar['kodeblokx']."');\" title='Close / Posting'></td>";
			}else{ 
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unclose / Unposting";
					$unpost=" onclick=\"unposting('".$bar['tahunbudget']."','".$bar['kodeblokx']."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Closed / Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
			}
			$tab.="<td align=center width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"preview(event,'".$bar['tahunbudget']."','".$bar['kodeblokx']."','".$bar['sumber']."','html');\" ></td>";
			
			$tab.="<td align=center width=25px><img onclick=datakeExcel(event,'".$bar['tahunbudget']."','".$bar['kodeblokx']."','".$bar['sumber']."','excel') src=images/excel.jpg class=zImgBtn title='MS.Excel'></td>";
			$tab.="</tr>";
		}
		## PAGING
		$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loadDataLama','getPage');
		
        echo $tab . "####" . $foot;
	break;
	case'printExcel':
        $optIP = array('I'=>'Inti','P'=>'Plasma');
		if($param['jenis']=='excel'){
			$border="border=1";
		}else{
			$border="border=0";
		}
		
		$tab="
		<table cellpadding=5 cellspacing=1 ".$border." class=sortable>
		<thead>
		<tr class=rowheader>
		<th align=center bgcolor=#DEDEDE>No</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['budgetyear']."</th>
		<th align=center bgcolor=#DEDEDE>".$_SESSION['lang']['blok']."</th>
		<th align=center bgcolor=#DEDEDE>".$_SESSION['lang']['sumber']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['intiplasma']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['hathnlalu']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['hathnini']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['pokokthnlalu']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['pokokthnini']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['statusblok']."</th>
		<th align=center bgcolor=#DEDEDE>".$_SESSION['lang']['topografi']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['thntnm']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['lcthnini']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['hanonproduktif']."</th>
		<th align=center bgcolor=#DEDEDE width=50px>".$_SESSION['lang']['pkkproduktif']."</th>
		</tr>
		</thead><tbody>";
			
		$str="select * from ".$dbname.".bgt_blok where tahunbudget='".$param['thnAngrn']."' and kodeblok like '".$param['afdId']."%'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent id=rew_".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td><td>".getNamaOrg($bar['kodeblok'])."</td>";
			$tab.="<td align=center>".$bar['sumber']."</td>";
			$tab.="<td>".$optIP[$bar['intiplasma']]."</td>";
			$tab.="<td  align=right>".hidezerodecimal($bar['hathnlalu'],2)."</td>";
			$tab.="<td  align=right>".hidezerodecimal($bar['hathnini'],2)."</td>";
			$tab.="<td  align=right>".hidezerodecimal($bar['pokokthnlalu'],2)."</td>";
			$tab.="<td  align=right>".hidezerodecimal($bar['pokokthnini'],2)."</td>";
			$tab.="<td align=center>".$bar['statusblok']."</td>";
			$tab.="<td>".$bar['topografi']."-".$optTopografi[$bar['topografi']]."</td><td id='thnTmn_".$no."' align='center'>".$bar['thntnm']."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['lcthnini'],2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['hanonproduktif'],2)."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['pokokproduksi'],2)."</td>";
			$tab.="</tr>";
			
			$ttlhalalu+=$bar['hathnlalu'];
			$ttlhathini+=$bar['hathnini'];
			$ttlpkklalu+=$bar['pokokthnlalu'];
			$ttlpkkthini+=$bar['pokokthnini'];
			$ttllcthini+=$bar['lcthnini'];
			$ttlhanon+=$bar['hanonproduktif'];
			$ttlpkprd+=$bar['pokokproduksi'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan='5' align=center>TOTAL</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlhalalu,2)."</td>";
		$tab.="<td align=right >".hidezerodecimal($ttlhathini,2)."</td>";
		$tab.="<td align=right>".hidezerodecimal($ttlpkklalu)."</td>";
		$tab.="<td align=right >".hidezerodecimal($ttlpkkthini)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right >".hidezerodecimal($ttllcthini,2)."</td>";
		$tab.="<td align=right >".hidezerodecimal($ttlhanon,2)."</td>";
		$tab.="<td align=right >".hidezerodecimal($ttlpkprd)."</td>";
		$tab.="</tr>";
		
		
        $tab.="</tbody></table></div>";
		
		if($param['jenis']=='excel'){
			$nop ="list_data_".$param['afdId'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("data_blok", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		}
	break;
}
?>
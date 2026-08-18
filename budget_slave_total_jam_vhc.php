<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$proses   =checkPostGet('proses','');
$kdTraksi =checkPostGet('kdTraksi','');
$thnBudget=checkPostGet('thnBudget','');
$totJamThn=checkPostGet('totJamThn','');
$kdVhc    =checkPostGet('kdVhc','');
$kdUnit   =checkPostGet('kdUnit','');
$totRow   =checkPostGet('totRow','');
$jab = getPostingJabatan('budget');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$arrBln   =array("1"=>"Jan","2"=>"Feb","3"=>"Mar","4"=>"Apr","5"=>"Mei","6"=>"Jun","7"=>"Jul","8"=>"Aug","9"=>"Sept","10"=>"Okt","11"=>"Nov","12"=>"Des");

$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$where="tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and unitalokasi='".$kdUnit."'";



switch($proses){
	case'posting':
		try{
		$owlPDO->beginTransaction();
			
			$str = "update " . $dbname . ".bgt_vhc_jam set tutup='1' where tahunbudget = '".$param['tahunbudget']."' and kodetraksi='".$param['kodeorg']."'"; #exit("error".$str);
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
			
			$str = "update " . $dbname . ".bgt_vhc_jam set tutup='0' where tahunbudget = '".$param['tahunbudget']."' and kodetraksi='".$param['kodeorg']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'del':
		try{
		$owlPDO->beginTransaction();
			$str="delete from ".$dbname.".bgt_vhc_jam  where tahunbudget = '".$param['tahunbudget']."' and kodetraksi='".$param['kodeorg']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'deleteData':
		try{
		$owlPDO->beginTransaction();
			$str="delete from ".$dbname.".bgt_vhc_jam  where tahunbudget = '".$thnBudget."' and kodetraksi='".$kdTraksi."' and kodevhc = '".$kdVhc."' and unitalokasi = '".$kdUnit."' ";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'loaddata':	
        $where = "";
		if($param['tahunbudget']!=''){
			$where.=" and tahunbudget = '".$param['tahunbudget']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodetraksi  like '".$param['kodeorg']."%'";
		}
		if($param['kodetraksi']!=''){
			$where.=" and kodetraksi  = '".$param['kodetraksi']."'";
		}
		
        $tab = "";
		$limit= 20;
		$page = 0;
        $param['page'] = isset($param['page']) ? $param['page'] : '0';
        if (isset($param['page'])) {$page = intval($param['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 22;
		
        $sql = "select tahunbudget from ".$dbname.".bgt_vhc_jam where substr(kodetraksi,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,kodetraksi";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
		$bulan = range(1,12);
		foreach($bulan as $bln){
			$jam="jam".((strlen($bln)<2)?"0".$bln:$bln);
			$n.="sum(".$jam.") as ".$jam.", ";
		}
		
		$str="select ".$n." sum(jumlahjam) as jumlahjam,tahunbudget,kodetraksi,tutup from ".$dbname.".bgt_vhc_jam where substr(kodetraksi,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,kodetraksi order by tahunbudget desc limit " . $offset . "," . $limit . "";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $val){
				$no++;
				$nmgdg   = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".substr($val['kodetraksi'],0,4)."' or kodeorganisasi='".$val['kodetraksi']."'");
				
				
				$tab.="<tr class='rowcontent' style=height:20px>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:center'>".$val['tahunbudget']."</td>";
				$tab.="<td>".substr($val['kodetraksi'],0,4)." - ".$nmgdg[substr($val['kodetraksi'],0,4)]."</td>";
				$tab.="<td>".$val['kodetraksi']." - ".$nmgdg[$val['kodetraksi']]."</td>";
				foreach($bulan as $bln){
					$jam="jam".((strlen($bln)<2)?"0".$bln:$bln);
					$tab.="<td align=right>".number_format($val[$jam],2)."</td>";
				}
				$tab.="<td style='text-align:right'>".number_format($val['jumlahjam'],2)."</td>";

				if($val['tutup']=='0'){
					$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['kodetraksi']."','".$val['tahunbudget']."');\" ></td>";
					
					$tab.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['kodetraksi']."','".$val['tahunbudget']."');\" title='Delete'></td>";
					
					$tab.="<td align=center width=20px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$val['kodetraksi']."','".$val['tahunbudget']."');\" title='Close / Posting'></td>";
				}else{
					$tab.="<td align=center width=20px></td>";
					$tab.="<td align=center width=20px></td>";
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unclose / Unposting";
						$unpost=" onclick=\"unposting('".$val['kodetraksi']."','".$val['tahunbudget']."');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Closed / Posted";
						$unpost='';
					}
					$tab.="<td align=center width=20px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
				}
				
				$tab.="<td align=center width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"preview('".$val['tahunbudget']."','".$val['kodetraksi']."','html');\" ></td>";
				
				$tab.="<td align=center width=25px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel'  onclick=\"previewpdf('".$val['tahunbudget']."','".$val['kodetraksi']."','excel');\" ></td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		## PAGING
		$footd=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
	break;
	
	case'getKdVhc':
		$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sSup="select distinct kodevhc, nopol,detailvhc from ".$dbname.".vhc_5master where kodetraksi='".$kdTraksi."' order by kodevhc asc";
		$res=fetchdata($sSup);
		foreach($res as $rSup){	
			if($rSup['nopol']!=''){
				$rSup['nopol']=" - ".$rSup['nopol'];
			}
			if($rSup['detailvhc']!=''){
				$rSup['detailvhc']=" - ".$rSup['detailvhc'];
			}
			
			if($kdVhc!=''){
				$optVhc.="<option value='".$rSup['kodevhc']."' ".($rSup['kodevhc']==$kdVhc?'selected':'').">".$rSup['kodevhc']."".$rSup['nopol']."".$rSup['detailvhc']."</option>";
			}else{
				$optVhc.="<option value='".$rSup['kodevhc']."' >".$rSup['kodevhc']."".$rSup['nopol']."".$rSup['detailvhc']."</option>";
			}
		}
		echo $optVhc;
	break;
	case'cekHead':
		$thisThn=date("Y");
		$dtK=substr($thnBudget,0,1);
		$dtA=substr($thisThn,0,1);
		if($dtK!=$dtA){
			exit("Error:Budget year incorrect");
		}
		
		
		$str = "select * from " . $dbname . ".bgt_vhc_jam where tahunbudget = '".$param['thnBudget']."' and kodetraksi like '".$param['kdUnit']."%' and tutup='1'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Budget sudah ditutup.");
		}
		
		$sGet="select * from ".$dbname.".bgt_vhc_jam where ".$where." ";
		$qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
		$qGet->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($qGet);
		$rCek=$numrows;
		if($rCek=='1'){
			exit("Error:Date already exist");
		}
		
		$sBr=$totJamThn/12;
		
		echo $sBr;
	break;
	case'saveData':
		for($a=1;$a<=$totRow;$a++){
			if($_POST['arrJam'][$a]==''){
				$_POST['arrJam'][$a]=0;
			}
			$totalSum+=$_POST['arrJam'][$a];
		}
		if($totalSum>$totJamThn){
			exit("Error : Monthly hours greater than annually hours");
		}

		$sCek="select distinct * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$thnBudget."' and kodevhc='".$kdVhc."' and unitalokasi='".$kdUnit."'";
		$res = fetchdata($sCek);
		if(count($res)==0){
			$str="insert into ".$dbname.".bgt_vhc_jam (tahunbudget, kodevhc, unitalokasi, jumlahjam,kodetraksi,updateby, jam01, jam02, jam03, jam04, jam05, jam06, jam07, jam08, jam09, jam10, jam11, jam12)";
			$str.=" values ('".$thnBudget."','".$kdVhc."','".$kdUnit."','".$totJamThn."','".$kdTraksi."','".$_SESSION['standard']['userid']."',";
			for($a=1;$a<$totRow;$a++){
				$str.="'".$_POST['arrJam'][$a]."',";
				if($a==($totRow-1)){
					$str.="'".$_POST['arrJam'][$a]."')";
				}
			}
		   try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{
			exit("Error: Data already exist");
		}
	break;
	case'loadDatadetail':
		$where = "";
		if($thnBudget!=''){
			$where.=" and tahunbudget='".$thnBudget."'";
		}
		if($kdVhc!=''){
			$where.=" and kodevhc='".$kdVhc."'";
		}
		$where.=" and kodetraksi='".$param['kdUnit']."'";
		
		
		$limit=20;
		$page=0;
		$param['page'] = isset($param['page']) ? $param['page'] : '0';
		if (isset($param['page'])) {$page = intval($param['page']);if ($page < 0){$page = 0;}}
			
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 16;

		$sql="select count(*) as jmlhrow from ".$dbname.".bgt_vhc_jam where 1=1 ".$where."";
		$res = fetchdata($sql);
		$jlhbrs = $res[0]['jmlhrow'];
		
		$totRowDlm=count($arrBln);
		$colspan=$totRowDlm+7;
		
		$border="border=0";
		if($param['tipe']=='excel'){$border="border=1";}
		
		$tab="
			<table cellpadding=5 cellspacing=1 ".$border." class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader><th align=center  width=30px>No</th>";
		$tab.="<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kodetraksi']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kodevhc']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['alokasi']." ".$_SESSION['lang']['ke']." ".$_SESSION['lang']['unit']."</th>";
		foreach($arrBln as $brs5=>$dtBln5){
			$tab.="<th align=center>".$dtBln5."</th>";
		}

		$tab.="<th align=center  width=70px>".$_SESSION['lang']['totJamThn']."</th>";
		$tab.="<th align=center colspan=2>Action</th></tr></thead><tbody>";
		
		$str="select * from ".$dbname.".bgt_vhc_jam where 1=1 ".$where." order by tahunbudget desc, lastupdate desc ";
		#limit ".$offset.",".$limit."";
		$res = fetchdata($str);
		foreach($res as $rList){
			$nmorg  =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$rList['kodetraksi']."' or kodeorganisasi ='".$rList['unitalokasi']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$rList['kodevhc']."'");
			
			$no+=1;
			$rtp=" style='cursor:pointer;' title='Edit ".$rList['kodevhc']."' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodevhc']."','".$rList['unitalokasi']."','".$rList['kodetraksi']."','".$rList['jumlahjam']."');\"";
			$tab.="<tr class=rowcontent >";
			$tab.="<td align=center ".$rtp.">".$no."</td>";
			$tab.="<td align=center ".$rtp.">".$rList['tahunbudget']."</td>";
			$tab.="<td ".$rtp.">".$rList['kodetraksi']." - ".$nmorg[$rList['kodetraksi']]."</td>";
			
			if($nmnopol[$rList['kodevhc']]!=''){
				$tab.="<td ".$rtp.">".$rList['kodevhc']." - ".$nmnopol[$rList['kodevhc']]."</td>";
			}else{
				$tab.="<td ".$rtp.">".$rList['kodevhc']."</td>";
			}
			$tab.="<td ".$rtp.">".$rList['unitalokasi']." - ".$nmorg[$rList['unitalokasi']]."</td>";
			for($a=1;$a<=$totRowDlm;$a++){
				if(strlen($a)=='1'){
					$b="0".$a;
				}else{
					$b=$a;
				}
				if($rList['jam'.$b]==''){
					$rList['jam'.$b]=0;
				}
				$tab.="<td align='right' ".$rtp.">".number_format($rList['jam'.$b],2)."</td>";
				$ttl[$a]+=$rList['jam'.$b];
			}
			
			$tab.="<td align='right' ".$rtp.">".number_format($rList['jumlahjam'],2)."</td>";
			$sttljam+=$rList['jumlahjam'];
			
			if($param['jenis']!='popup' and $rList['tutup']=='0'){				
				$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodevhc']."','".$rList['unitalokasi']."','".$rList['kodetraksi']."','".$rList['jumlahjam']."');\" ></td>";
				
				$tab.="<td align='center'  width=25px><img src='images/application/application_delete.png' class=resicon  title='Delete ".$rList['kodevhc']."' onclick=\"deleteData('".$rList['tahunbudget']."','".$rList['kodevhc']."','".$rList['unitalokasi']."','".$rList['kodetraksi']."');\"></td>";
			}else{
				$tab.="<td align='center'  width=25px></td>";
				$tab.="<td align='center'  width=25px></td>";
			}
			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent>
				<td align=center colspan=5>TOTAL</td>";
				for($a=1;$a<=$totRowDlm;$a++){
					$tab.="<td align=right>".number_format($ttl[$a],2)."</td>";
				}
		$tab.="<td align=right>".number_format($sttljam,2)."</td>";
		$tab.="<td></td>
				<td></td>
				</tr>";
		
		#$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadDatadetail','');
		$tab.="</tbody></table>";
		if($param['tipe']=='excel'){
			$nop = "alokasi_kend.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("alokasi_kend", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		}
	
	break;
	case'update':
		if(($totJamThn==0)||($totJamThn=='')){
			exit("Error:Total hours required");
		}
		for($a=1;$a<=$totRow;$a++){
			if($_POST['arrJam'][$a]==''){
				$_POST['arrJam'][$a]=0;
			}
			$totalSum+=$_POST['arrJam'][$a];
		}
		if($totalSum>$totJamThn){
			exit("Error:Monthly hours geater than annually hours");
		}                  

		$sUpdate="update ".$dbname.".bgt_vhc_jam set jumlahjam='".$totJamThn."',updateby='".$_SESSION['standard']['userid']."',";
		for($a=1;$a<=12;$a++){
			if(strlen($a)=='1'){
				$c="0".$a;
			}else{
				$c=$a;
			}
			$sUpdate.=" jam".$c."='".$_POST['arrJam'][$a]."',";
			if($a==12){
			 $sUpdate.=" jam".$c."='".$_POST['arrJam'][$a]."'";
			}
		}            
		$sUpdate.="  where ".$where."";
		try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case'deleteData':
		$sDel="delete from ".$dbname.".bgt_vhc_jam where ".$where."";
		try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
	break;
	case'getDataEdit':
		$sData="select * from ".$dbname.".bgt_vhc_jam where ".$where."";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$rData=$qData->fetch();
		for($r=1;$r<13;$r++){
			if(strlen($r)<2){
				$b="0".$r;
			}else{
				$b=$r;
			}
			echo $rData['jam'.$b]."###";
		}
	break;
	default:
	break;
}
?>
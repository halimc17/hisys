<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$optNm=makeOption($dbname, 'bgt_kode', 'kodebudget,nama');

if(count($_POST)>0){
	$cekapa=$_POST['cekapa'];
	$param=$_POST;
}else{
	$param=$_GET;
	$cekapa=$_GET['cekapa'];
}
$jab = getPostingJabatan('budget');



// echo"<pre>";
// print_r($param);
// echo"<pre>";
// exit("error");

switch ($cekapa) {
	case'del':
		try{
		$owlPDO->beginTransaction();
			$str="delete from ".$dbname.".bgt_budget  where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='".$param['tipebudget']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'posting':
		try{
		$owlPDO->beginTransaction();
			
			$str = "update " . $dbname . ".bgt_budget set tutup='1' where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='".$param['tipebudget']."'"; #exit("error".$str);
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
			
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='".$param['tipebudget']."'"; #exit("error".$str);
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
		$where.=" and tipebudget = 'WS'";
		if($param['tahunbudget']!=''){
			$where.=" and tahunbudget = '".$param['tahunbudget']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '".$param['kodeorg']."%'";
		}
		if($param['kodews']!=''){
			$where.=" and kodeorg = '".$param['kodews']."'";
		}
		
        $tab = "";
		$limit= 20;
		$page = 0;
        $param['page'] = isset($param['page']) ? $param['page'] : '0';
        if (isset($param['page'])) {$page = intval($param['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 16;
		
        $sql = "select kodeorg, sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where substr(kodeorg,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,kodeorg";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
		
		$str="select tutup,tahunbudget,tipebudget, kodeorg, sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where substr(kodeorg,1,4) in (".getOrgDetail(2).") ".$where." group by tahunbudget,kodeorg order by tahunbudget desc limit " . $offset . "," . $limit . "";
		$res=fetchdata($str);
		if(count($res) > 0){
			foreach($res as $key=>$val){
				$no++;
				$nmgdg   = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".substr($val['kodeorg'],0,4)."' or kodeorganisasi='".$val['kodeorg']."'");
				
				
				$tab.="<tr class='rowcontent' style=height:20px>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td style='text-align:center'>".$val['tahunbudget']."</td>";
				$tab.="<td style='text-align:center'>".$val['tipebudget']."</td>";
				$tab.="<td>".substr($val['kodeorg'],0,4)." - ".$nmgdg[substr($val['kodeorg'],0,4)]."</td>";
				$tab.="<td>".$val['kodeorg']." - ".$nmgdg[$val['kodeorg']]."</td>";
				
				#jambengkel
				$ttljam=0;
				$s="select sum(jampertahun ) as jam from ".$dbname.".bgt_ws_jam where tahunbudget ='".$val['tahunbudget']."' and kodews ='".$val['kodeorg']."'";
				$r=fetchdata($s);
				$ttljam=$r[0]['jam'];
				$tab.="<td align=right>".number_format($ttljam,2)."</td>";
				
				$getdt="'".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."'";
				$sdm=0;
				$s="select sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where tahunbudget ='".$val['tahunbudget']."' and kodeorg ='".$val['kodeorg']."' and tipebudget='WS' and (substr(kodebudget,1,3) = 'SDM' or substr(kodebudget,1,4) = 'EXPL')";
				$r=fetchdata($s);
				$sdm=$r[0]['rupiah'];
				$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('sdm',".$getdt.")>".number_format($sdm)."</td>";
				
				$mat=0;
				$s="select sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where tahunbudget ='".$val['tahunbudget']."' and kodeorg ='".$val['kodeorg']."' and tipebudget='WS' and substr(kodebudget,1,2) = 'M-'";
				$r=fetchdata($s);
				$mat=$r[0]['rupiah'];
				$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('mat',".$getdt.")>".number_format($mat)."</td>";
				
				$tool=0;
				$s="select sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where tahunbudget ='".$val['tahunbudget']."' and kodeorg ='".$val['kodeorg']."' and tipebudget='WS' and kodebudget = 'TOOL'";
				$r=fetchdata($s);
				$tool=$r[0]['rupiah'];
				$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('tool',".$getdt.")>".number_format($tool)."</td>";
				
				$tran=0;
				$s="select sum(rupiah ) as rupiah from ".$dbname.".bgt_budget where tahunbudget ='".$val['tahunbudget']."' and kodeorg ='".$val['kodeorg']."' and tipebudget='WS' and kodebudget = 'TRANSIT'";
				$r=fetchdata($s);
				$tran=$r[0]['rupiah'];
				$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('trans',".$getdt.")>".number_format($tran)."</td>";
				
				$tab.="<td align=right>".number_format($sdm+$mat+$tool+$tran)."</td>";
				if($ttljam>0){					
					$tab.="<td align=right>".number_format(($sdm+$mat+$tool+$tran)/$ttljam,2)."</td>";
				}else{
					$tab.="<td align=right></td>";					
				}
				
				if($val['tutup']=='0'){
					$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."');\" ></td>";
					
					$tab.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."');\" title='Delete'></td>";
					
					$tab.="<td align=center width=20px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."');\" title='Close / Posting'></td>";
				}else{
					$tab.="<td align=center width=20px></td>";
					$tab.="<td align=center width=20px></td>";
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unclose / Unposting";
						$unpost=" onclick=\"unposting('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Closed / Posted";
						$unpost='';
					}
					$tab.="<td align=center width=20px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
				}
				
				#$tab.="<td align=center width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"preview('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."','html');\" ></td>";
				
				$tab.="<td align=center width=25px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel'  onclick=\"previewpdf('".$val['tipebudget']."','".$val['tahunbudget']."','".$val['kodeorg']."','excel');\" ></td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</tr>";
		}
		
		## PAGING
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
	break;
	
	case'hkef':
		$hkef='';
		$tahunbudget=$param['tahunbudget'];
		$kodews=$param['kodews'];
		
		$str = "select * from ".$dbname.".bgt_ws_jam where tahunbudget = '".$tahunbudget."' and kodews  = '".$kodews."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Errorcode : Jam bengkel belum diinput.");
		}
		
		
		$str="select * from ".$dbname.".bgt_hk where tahunbudget = '".$tahunbudget."' and unit = '".substr($kodews,0,4)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke=$bar['harisetahun']-$thrlb;
			$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke=$thke-($bar['jlhcuti']+$tsim);
			
			$hkef=$tothke;
		}
		$optupah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$supah="select distinct golongan from ".$dbname.".bgt_upah where 
				kodeorg='".substr($param['kodews'],0,4)."' and tahunbudget='".$tahunbudget."' and jumlah>0";
		$qupah=$owlPDO->query($supah) or die(print " Gagal: ".PDOException::getMessage());
		$qupah->setFetchMode(PDO::FETCH_ASSOC);
		while($rupah=  $qupah->fetch()){
			$optupah.="<option value='".$rupah['golongan']."'>".$optNm[$rupah['golongan']]."</option>";
		}
		echo $hkef."#####".$optupah;
	break;

	case'upah':
		$kodebudget0=$param['kodebudget0'];
		$kodews=$param['kodews'];
		
		$str="select * from ".$dbname.".bgt_upah where closed=1 and golongan = '".$kodebudget0."' and kodeorg = '".substr($kodews,0,4)."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$hkef='';
		while($bar= $res->fetch()){
			$hkef=$bar->jumlah;
		}
		echo $hkef;        
	break;

	case'regional':
		$kodews=$param['kodews'];
		$kodeorg=substr($kodews,0,4);
		$str="select * from ".$dbname.".bgt_regional_assignment where kodeunit = '".$kodeorg."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$hkef='';
		while($bar= $res->fetch()){
			$hkef=$bar->regional;
		}
		echo $hkef;        
	break;

	case'barang':
		$kodebarang1=$param['kodebarang1'];
		$tahunbudget=$param['tahunbudget'];
		$regional=$param['regional'];
		$str="select * from ".$dbname.".bgt_masterbarang
			where closed=1 and kodebarang = '".$kodebarang1."' and regional ='".$regional."' and tahunbudget ='".$tahunbudget."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$hkef='';
		while($bar= $res->fetch()){
			$hkef=$bar->hargasatuan;
		}
		echo $hkef;        
	break;


	case'tab0':
		$tipebudget =$param['tipebudget'];
		$tahunbudget=$param['tahunbudget'];
		$kodews     =$param['kodews'];
		
		$border="border=0";
		if($param['tipe']=='excel'){$border="border=1";}
	
		$tab='';
		$tab.="<hr>
		<table id=container9 cellpadding=5 width=100% class=sortable cellspacing=1 ".$border.">
		 <thead>
			<tr>
				<th align=center width=30px>".$_SESSION['lang']['nomor']."</th>
				<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['tipeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['volume']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['totalbiaya']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
		   </tr>  
		 </thead>
		 <tbody>";
		
		$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($kodews,0,4)."'");
		if($tipeorg[substr($kodews,0,4)]=='PABRIK'){
			$whsdm="'EXPL%'";
		}elseif($tipeorg[substr($kodews,0,4)]=='BULKING'){
			$whsdm="'EXPLBULK%'";
		}else{
			$whsdm="'SDM%'";
		}
		$str="select * from ".$dbname.".bgt_budget where kodebudget like $whsdm and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg = '".$kodews."'";
		$res = fetchdata($str);
		$no=1;$gt=0;
		foreach($res as $bar){
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
		$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$bar['tahunbudget']."</td>
				<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				<td align=center>".$bar['tipebudget']."</td>
				<td align=left>".$optNm[$bar['kodebudget']]."</td>
				<td align=right>".number_format($bar['volume'])."</td>
				<td align=left>".$bar['satuanv']."</td>
				<td align=right>".number_format($bar['jumlah'])."</td>
				<td align=left>".$bar['satuanj']."</td>
				<td align=right>".number_format($bar['rupiah'])."</td>";
				if($bar['tutup']==0 and $param['jenis']!='popup'){
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['volume']."','".$bar['rupiah']."');\" ></td>";
					
					$tab.="<td align=center width=25px><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(0,".$bar['kunci'].")\" title=\"Hapus\"></td>";
				}else{					
					$tab.="<td align=center>&nbsp;</td>";
					$tab.="<td align=center>&nbsp;</td>";
				}
				
			$gt+=$bar['rupiah'];	
			$tab.="</tr>";
			$no+=1;
		}
		
		$tab.="<tr class=rowcontent>
				<td align=center colspan=9>TOTAL</td>
				<td align=right>".number_format($gt)."</td>
				<td></td>
				<td></td>
				</tr>";
				
		$tab.="</tbody>
		 <tfoot>
		 </tfoot>		 
		 </table>";
		if($param['tipe']=='excel'){
			$nop = "sdm.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("sdm", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		}
	break;

	case'tab1':
		$tipebudget =$param['tipebudget'];
		$tahunbudget=$param['tahunbudget'];
		$kodews     =$param['kodews'];
		
		$str="select kodebarang, namabarang from ".$dbname.".log_5masterbarang";
		$res = fetchdata($str);
		foreach($res as $bar){
			$barang[$bar['kodebarang']]=$bar['namabarang'];
		}
		
		$border="border=0";
		if($param['tipe']=='excel'){$border="border=1";}
		
		$tab='';
		$tab.="<hr>
		<table id=container8 cellpadding=5 width=100% class=sortable cellspacing=1 ".$border." >
		 <thead>
			<tr>
				<th align=center width=30px>".$_SESSION['lang']['nomor']."</th>
				<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['tipeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['totalbiaya']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
		   </tr>  
		 </thead>
		 <tbody>";
		$nmkode=makeOption($dbname,'bgt_kode','kodebudget,nama');
		
		$no=1;
		
		$str="select * from ".$dbname.".bgt_budget where substr(kodebudget,1,2) = 'M-' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg = '".$kodews."' and kodebarang!=''";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$bar['tahunbudget']."</td>
				<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				<td align=center>".$bar['tipebudget']."</td>
				<td align=left>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>
				<td align=right>".$bar['kodebarang']."</td>
				<td align=left>".$barang[$bar['kodebarang']]."</td>
				<td align=right>".number_format($bar['jumlah'])."</td>
				<td align=left>".$bar['satuanj']."</td>
				<td align=right>".number_format($bar['rupiah'])."</td>";
				if($bar['tutup']==0 and $param['jenis']!='popup'){
					$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['kodebarang']."','".$barang[$bar['kodebarang']]."','".$bar['satuanj']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
					
					$tab.="<td align=center style='width:25px'><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(1,".$bar['kunci'].")\" title=\"Hapus\"></td>";
				}else{					
					$tab.="<td align=center style='width:25px'>&nbsp;</td>";
					$tab.="<td align=center style='width:25px'>&nbsp;</td>";
				}
			$tab.="</tr>";
			$no+=1;
			$gt+=$bar['rupiah'];	
		}
		$tab.="<tr class=rowcontent>
				<td align=center colspan=9>TOTAL</td>
				<td align=right>".number_format($gt)."</td>
				<td></td>
				<td></td>
				</tr>";
				
		$tab.="</tbody>
		 <tfoot>
		 </tfoot>		 
		 </table>";
		if($param['tipe']=='excel'){
			$nop = "material.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("material", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		} 
	break;
	case'tab2':
		$tipebudget =$param['tipebudget'];
		$tahunbudget=$param['tahunbudget'];
		$kodews     =$param['kodews'];

		$str="select kodebarang, namabarang from ".$dbname.".log_5masterbarang";
		$res = fetchdata($str);
		foreach($res as $bar){
			$barang[$bar['kodebarang']]=$bar['namabarang'];
		}
		
		$border="border=0";
		if($param['tipe']=='excel'){$border="border=1";}
		
		$tab='';
		$tab.="<hr>
		<table id=container7 cellpadding=5 width=100% class=sortable cellspacing=1 ".$border.">
		 <thead>
			<tr>
				<th align=center width=30px>".$_SESSION['lang']['nomor']."</th>
				<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center width=50px>".$_SESSION['lang']['tipeanggaran']."</th>
				<th align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['totalbiaya']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
		   </tr>  
		 </thead>
		 <tbody>";
		$no=1;
		$str="select * from ".$dbname.".bgt_budget where kodebudget like 'TOOL%' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg = '".$kodews."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$bar['tahunbudget']."</td>
				<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				<td align=center>".$bar['tipebudget']."</td>
				<td align=center>".$bar['kodebudget']."</td>
				<td align=right>".$bar['kodebarang']."</td>
				<td align=left>".$barang[$bar['kodebarang']]."</td>
				<td align=right>".number_format($bar['jumlah'])."</td>
				<td align=left>".$bar['satuanj']."</td>
				<td align=right>".number_format($bar['rupiah'])."</td>";
			if($bar['tutup']==0 and $param['jenis']!='popup'){
				$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"edittool('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['kodebarang']."','".$barang[$bar['kodebarang']]."','".$bar['satuanj']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
				
				$tab.="<td align=center style='width:25px'><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(2,".$bar['kunci'].")\" title=\"Hapus\"></td>";
			}else{
				$tab.="<td align=center style='width:25px'>&nbsp;</td>";
				$tab.="<td align=center style='width:25px'>&nbsp;</td>";
			}
			
			$tab.="</tr>";
			$no+=1;
			$gt+=$bar['rupiah'];
		}
		
		$tab.="<tr class=rowcontent>
				<td align=center colspan=9>TOTAL</td>
				<td align=right>".number_format($gt)."</td>
				<td></td>
				<td></td>
				</tr>";

		$tab.="</tbody>
		 <tfoot>
		 </tfoot>		 
		 </table>";
		if($param['tipe']=='excel'){
			$nop = "tool.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("tool", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		} 
	break;
	case'tab3':
		$tipebudget =$param['tipebudget'];
		$tahunbudget=$param['tahunbudget'];
		$kodews     =$param['kodews'];
	
		$str="select * from ".$dbname.".keu_5akun where tipeakun='Biaya' and detail=1";
		$res = fetchdata($str);
		foreach($res as $bar){
			$akun[$bar['noakun']]=$bar['namaakun'];
		}
		
		$border="border=0";
		if($param['tipe']=='excel'){$border="border=1";}
		
		$tab='';
		$tab.="<hr>
		<table id=container6 cellpadding=5 width=100% class=sortable cellspacing=1 ".$border.">
		 <thead>
			<tr>
				<th align=center width=30px>".$_SESSION['lang']['nomor']."</th>
				<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center width=50px>".$_SESSION['lang']['tipeanggaran']."</th>
				<th align=center width=50px>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['noakun']."</th>
				<th align=center>".$_SESSION['lang']['namaakun']."</th>
				<th align=center>".$_SESSION['lang']['totalbiaya']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
		   </tr>  
		 </thead>
		 <tbody>";
		$no=1;
		$str="select * from ".$dbname.".bgt_budget where kodebudget like 'TRANSIT%' and tipebudget = '".$tipebudget."' and tahunbudget = '".$tahunbudget."' and kodeorg = '".$kodews."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$bar['tahunbudget']."</td>
				<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				<td align=center>".$bar['tipebudget']."</td>
				<td align=center>".$bar['kodebudget']."</td>
				<td align=right>".$bar['noakun']."</td>
				<td align=left>".$akun[$bar['noakun']]."</td>
				<td align=right>".number_format($bar['rupiah'])."</td>";
				if($bar['tutup']==0 and $param['jenis']!='popup'){
					$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editoth('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['noakun']."','".$bar['rupiah']."');\" ></td>";
					
					$tab.="<td align=center style='width:25px'><img id=\"delRow\" class=\"zImgBtn\" src=\"images/application/application_delete.png\" onclick=\"deleteRow(3,".$bar['kunci'].")\" title=\"Hapus\"></td>";
				}else{
					$tab.="<td align=center style='width:25px'>&nbsp;</td>";
					$tab.="<td align=center style='width:25px'>&nbsp;</td>";
				}
			$tab.="</tr>";
			$no+=1;
			$gt+=$bar['rupiah'];
		}

		$tab.="<tr class=rowcontent>
				<td align=center colspan=7>TOTAL</td>
				<td align=right>".number_format($gt)."</td>
				<td></td>
				<td></td>
				</tr>";
		$tab.="</tbody>
		 <tfoot>
		 </tfoot>		 
		 </table>";
		if($param['tipe']=='excel'){
			$nop = "other.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("other", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab;        
		} 
	break;
	case'delete0':
		$kunci=$param['kunci'];
		$str="delete from ".$dbname.".bgt_budget  where kunci='".$kunci."'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
}
?>
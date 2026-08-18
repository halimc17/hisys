<?
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/zLib.php');

$method = checkPostGet('method','');
$myid = checkPostGet('myid','');
$pages = checkPostGet('page','');

$pt = checkPostGet('pt','');
$gudang = checkPostGet('gudang','');
$kelompokbarang = checkPostGet('kelompokbarang','');
$barang = checkPostGet('barang','');
$minstok = checkPostGet('minstok','');
$maxstok = checkPostGet('maxstok','');
$satuan = checkPostGet('satuan','');

$crpt = checkPostGet('crpt','');
$crgudang = checkPostGet('crgudang','');
$crklbarang = checkPostGet('crklbarang','');
$crbarang = checkPostGet('crbarang','');


switch($method){
	case'getgudang':
		$optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".organisasi where tipe='GUDANG' and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodeorganisasi']==$gudang){
				$optgudang.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}else{
				$optgudang.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}
		}
		echo $optgudang;
	break;
	
	case'getcrgudang':
		$optgudang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where tipe='GUDANG' and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$crpt."')";
		$res=fetchdata($str);
		foreach($res as $val){
			$optgudang.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
		}
		echo $optgudang;
	break;
	
	case'getbarang':
		$optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='".$kelompokbarang."' and inactive='0'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['kodebarang']==$barang){
				$optbarang.="<option value='".$val['kodebarang']."' selected>".$val['kodebarang']." - ".$val['namabarang']."</option>";				
			}else{
				$optbarang.="<option value='".$val['kodebarang']."'>".$val['kodebarang']." - ".$val['namabarang']."</option>";				
			}
		}
		echo $optbarang;
	break;
	
	case'getcrbarang':
		$optbarang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='".$crklbarang."' and inactive='0'";
		$res=fetchdata($str);
		foreach($res as $val){
			$optbarang.="<option value='".$val['kodebarang']."'>".$val['kodebarang']." - ".$val['namabarang']."</option>";
		}
		echo $optbarang;
	break;
	
	case'getsatuan':
		$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$barang."'";
		$res=fetchdata($str);
		echo $res[0]['satuan'];
	break;
	
	case 'loaddata':
		$tab="";
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0 style='margin-left:5px;min-width:635px;'>
			<thead>
			<tr class=rowheader style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['pt']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['gudang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['kelompokbarang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['barang']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['satuan']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['minstok']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['maxstok']."</td>
				<td style='text-align:center'>Action</td></tr>
			 </thead>
			 <tbody>";
		
		$where = "1=1 ";
		if($crpt!=""){
			$where .= " and pt='".$crpt."'";
		}
		if($crgudang!=""){
			$where .= " and gudang='".$crgudang."'";
		}
		if($crklbarang!=""){
			$where .= " and kodekelompok='".$crklbarang."'";
		}
		if($crbarang!=""){
			$where .= " and kodebarang='".$crbarang."'";
		}
		
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
	
		$str="select * from ".$dbname.".log_5minimunstok where ".$where." order by pt asc, kodekelompok asc, kodebarang desc";
		$res=fetchData($str);
		$jlhbrs=count($res);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{			
			$str="select * from ".$dbname.".log_5minimunstok where ".$where." order by pt asc, kodekelompok asc, kodebarang desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $key=>$val){
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['pt']."'");
				$optgudang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['gudang']."'");
				
				$optklbarang = makeOption($dbname,'log_5klbarang','kode,kelompok',"kode='".$val['kodekelompok']."'");
				$optbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$optpt[$val['pt']]."</td>
					<td style='text-align:left'>".$optgudang[$val['gudang']]."</td>
					<td style='text-align:left'>".$optklbarang[$val['kodekelompok']]."</td>
					<td style='text-align:left'>".$optbarang[$val['kodebarang']]."</td>
					<td style='text-align:center'>".$val['satuan']."</td>
					<td style='text-align:right'>".hidezerodecimal($val['stok'],2)."</td>
					<td style='text-align:right'>".hidezerodecimal($val['stokmax'],2)."</td>
					<td style='text-align:center'>
						<img src=images/skyblue/edit.png class=resicon caption='Edit' onclick=\"editfield('".$val['id']."','".$val['pt']."','".$val['gudang']."','".$val['kodekelompok']."','".$val['kodebarang']."','".$val['satuan']."','".$val['stok']."','".$val['stokmax']."');\"> 
					</td>
				</tr>";
			}
			
			@$totrows=ceil($jlhbrs/$limit);
			if($totrows==0){
				$totrows=1;
			}
			
			$isiRow='';
			for($er=1;$er<=$totrows;$er++){
				$sel = ($page==$er-1)? 'selected': '';
				$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
			}
			
			@$frompage = (($page*$limit)+1);
			if(@(($page+1)*$limit) > $jlhbrs){
				$topage = $jlhbrs;
			}else{
				$topage = @(($page+1)*$limit);
			}
			$tab.="</tr>
			<tr>
				<td colspan=8 align=center>
					".$frompage." to ".$topage." Of ".  $jlhbrs."
				</td>
			</tr>
			<tr>
				<td colspan=8 align=center>";
			
			if($page=='0'){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=loaddata(".@($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}
			
			$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
			
			if(@($page+1) == $totrows){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=loaddata(".@($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
			$tab.="</td></tr>";
			$tab.="</tbody>
			</table>";
		}
		echo $tab;
	break;
	
	case'getkary':
		$where='';
		if($kodeunit!=''){
			$where.=" and kodeunit='".$kodeunit."'";
		}
		if($jenispersetujuan!=''){
			$where.=" and jenispersetujuan='".$jenispersetujuan."'";
		}
	
		$whr='';
		if($departemen!=''){
			$whr.=" and bagian='".$departemen."'";
		}
		if($jabatan!=''){
			$whr.=" and kodejabatan='".$jabatan."'";
		}

		if($tipekaryawan!=''){
			if($tipekaryawan==0){
				$whr.=" and tipekaryawan=9";
			} else if($tipekaryawan==9){
				$whr.=" and tipekaryawan=10";
			} else if($tipekaryawan==10){
				$whr.=" and tipekaryawan in (7,8)";
			}else{
				$whr.=" and tipekaryawan not in ('1','4','5','6')";
			}
		}else{
			$whr.=" and tipekaryawan not in ('1','4','5','6')";
		}

		$str = "select karyawanid,namakaryawan, lokasitugas from  " . $dbname . ".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and karyawanid not in (select karyawanid from  " . $dbname . ".setup_approval where 1=1 ".$where.") ".$whr." order by namakaryawan asc ";
		// exit('warning : '.$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optkar.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$optkar.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . " - ".$bar['lokasitugas']."</option>";
		}
		
	echo $optkar;
	break;
	
	case 'update':
		$str="update ".$dbname.".log_5minimunstok set stok='".$minstok."',stokmax='".$maxstok."', updateby='".$_SESSION['standard']['userid']."' ,updatetime='".date('Y-m-d H:i')."' where id='".$myid."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'insert':
		$str="select * from ".$dbname.".log_5minimunstok where gudang='".$gudang."' and kodebarang='".$barang."'";
		$res=fetchData($str);
		if(!empty($res)){
			exit("Warning : Gudang dan Barang sudah pernah terdaftar disistem");
		}else{
			$str="insert into ".$dbname.".log_5minimunstok (id,pt,gudang,kodekelompok,kodebarang,satuan,stok,stokmax,createby,createtime,updateby,updatetime) values ('','".$pt."','".$gudang."','".$kelompokbarang."','".$barang."','".$satuan."','".$minstok."','".$maxstok."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}	
	break;

	default:
	break;					
}

?>

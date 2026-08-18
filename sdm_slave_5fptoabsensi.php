<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'Simpan ke SDM - Absensi','0'=>'Tidak Simpan ke SDM - Absensi');
switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " and id='".$param['id']."'";
			$str = "delete from " . $dbname . ".sdm_5fptoabsensi where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'update':
		
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$str = "select * from ".$dbname.".sdm_5fptoabsensi where kodeorg='".$param['kodeorg']."' and subbagian='".$param['subbagian']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah pernah diinput.");
			}
			
			if($param['status']==1 and $param['noakun']==''){
				throw new PDOException("Nomor akun harus diinput.");
			}
			if($param['status']=='0'){
				$param['noakun']="";
			}
			
			$data = array(
				'kodeorg'   => $param['kodeorg'],
				'subbagian' => $param['subbagian'],
				'absensi'   => $param['status'],
				'noakun'    => $param['noakun'],
				'createby'  => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5fptoabsensi',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'getnoakun':
		$opttipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
		$param['divisi'] = $param['subbagian'];	
		$noakun='';
		$autoinsert = true; $wh="";
		if($opttipe[$param['kodeorg']]=='KEBUN'){
			$wh.=" and (substr(noakun,1,2) in ('71')";
			if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='BIBITAN'){				
				$autoinsert = false;
				$kdjurnal="KBNL0";
				$wh="";
				$wh.=" and (substr(noakun,1,2) in ('71')";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
				$autoinsert = false;
				$kdjurnal="VHCG0";
				$wh="";
				$wh.=" and (substr(noakun,1,2) in ('71')";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
				$kdjurnal="WSG0";
				$wh="";
				$wh.=" and (substr(noakun,1,2) in ('71')";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='AFDELING'){				
				$kdjurnal="KBNB0";
				$autoinsert = false;
				$wh="";
				$wh.=" and (substr(noakun,1,2) in ('71')";
			}else{				
				$kdjurnal="KBNB0";
			}
			
		}elseif($opttipe[$param['kodeorg']]=='PABRIK'){
			if(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='TRAKSI'){				
				$kdjurnal="VHCG0";
			}elseif(strlen($param['divisi'])==6 and $opttipe[$param['divisi']]=='WORKSHOP'){				
				$kdjurnal="WSG0";
			}else{				
				$kdjurnal="PKS01";
			}
			$wh.=" and (substr(noakun,1,2) in ('71')";
		}elseif($opttipe[$param['kodeorg']]=='BULKING'){
			$kdjurnal="BLK01";$wh.=" and (substr(noakun,1,2) in ('81')";
		}elseif($opttipe[$param['kodeorg']]=='RND' or $opttipe[$param['kodeorg']]=='TC'){
			$kdjurnal="RNDB0";$wh.=" and (substr(noakun,1,2) in ('81')";
		}elseif($opttipe[$param['kodeorg']]=='HOLDING'){
			$kdjurnal="GJHO0";$wh.=" and (substr(noakun,1,2) in ('82')";
		}else{
			$kdjurnal="";
			$noakun='711';
			$wh.=" and (substr(noakun,1,2) in ('71')";
		}
		$optakun= makeOption($dbname,'keu_5parameterjurnal','jurnalid,noakundebet',"jurnalid='".$kdjurnal."'");
		$akun   = $optakun[$kdjurnal];
		if(count($optakun)>0){
			$wh.=" or noakun='".$akun."')";		
		}else{
			if ($noakun!=""){
				$wh.=" or noakun like '".$noakun."%')";	
			}
		}
		
		$optorg="<option value=''>&nbsp;</option>";
		$sql = "SELECT * FROM " . $dbname . ".keu_5akun  where 1=1 ".$wh." and length(noakun)>5 order by noakun";  
		#exit("error".$sql);
		$res = fetchdata($sql);
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,5);
			if($d!=$n){			
				$optorg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$s="";
			if($akun==$bar['noakun']){
				//$s=" selected";				
			}
			
			$optorg.="<option value=".$bar['noakun']." ".$s.">".$bar['noakun']." - ".$bar['namaakun']."</option>";
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}
		
		if($autoinsert==false){
			$arrshift=array('0'=>'Tidak Simpan ke SDM - Absensi');
		}else{			
			$arrshift=array('1'=>'Simpan ke SDM - Absensi','0'=>'Tidak Simpan ke SDM - Absensi');
		}
		
		$optshift="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrshift as $val => $key){
			if($param['status']==$val){
				$optshift.="<option value=".$val." selected>".$key."</option>";
			}else{				
				$optshift.="<option value=".$val.">".$key."</option>";
			}
		}
		
		
		echo $optorg."####".$optshift;
	break;
	case 'getsubbagian':
		$optpks = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sql = "SELECT distinct subbagian FROM " . $dbname . ".datakaryawan  where lokasitugas='".$param['kodeorg']."' order by subbagian"; 
		#exit("error".$sql);
		$res = fetchdata($sql);
		foreach($res as $bar){
			if($bar['subbagian']==$param['subbagian']){
				$i="selected";
			}else{
				$i="";
			}
			if($bar['subbagian']==''){
				$optpks.="<option value=" . $bar['subbagian'] . ">UMUM - UMUM</option>";
			}else{				
				$optpks.="<option value=" . $bar['subbagian'] . ">" . $bar['subbagian'] . " - " . getNamaOrg($bar['subbagian']) . "</option>";
			}
			
		}
		echo $optpks;
	break;
	case 'addnew':
		$arrshift=array('1'=>'Simpan ke SDM - Absensi','0'=>'Tidak Simpan ke SDM - Absensi');
		$optshift="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrshift as $val => $key){
			if($param['status']==$val){
				$optshift.="<option value=".$val." selected>".$key."</option>";
			}else{				
				$optshift.="<option value=".$val.">".$key."</option>";
			}
		}
		if($param['mode']=='update'){
			$tombol='Update';
		}else{
			$tombol='Save';
		}
		
		$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach(getOrgDetail(1) as $key => $val){
			$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}

		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<input id=id type=hidden>
					<td style=min-width:100px>".$_SESSION['lang']['kodeorg']."</td>
					<td><select class='select2' style='width:405px;' id=kodeorg onchange=getsubbagian('kodeorg');>".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['subbagian']."</td>
					<td><select class='select2' style='width:405px;' id=subbagian onchange=getnoakun()></select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td><select class='select2' style='width:405px;' id=status >".$optshift."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['noakun']."</td>
					<td><select class='select2' style='width:405px;' id=noakun ></select></td>
				</tr>
                <tr>
                    <td colspan=40 align=center>
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:160px;height:30px' class=mybutton>".$tombol."</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th  style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['kodeorg']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['subbagian']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['status']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['noakun']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['tanggal']."</th>
				<th  style='text-align:center;'>".$_SESSION['lang']['action']."</th>
			</tr>
			
		</thead>
		<tbody >";
		
		
		$whrunit=getOrgDetail(2);
		$str= "select * from ".$dbname.".sdm_5fptoabsensi where kodeorg in ($whrunit) order by kodeorg,subbagian";
		$res= fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($bar['subbagian'])."</td>";
			$tab.="<td style='text-align:left;'>".$arrstatus[$bar['absensi']]."</td>";
			$tab.="<td style='text-align:left;'>".getNamaAkun($bar['noakun'])."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($bar['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['updatetime']."</td>";
			
			// $tab.="<td style='text-align:center;width:25px'>
				// <img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['kodeorg']."','".$bar['subbagian']."','".$bar['absensi']."','".$bar['noakun']."','".$bar['id']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');></td>";
			$tab.="</tr>";

			$n=$d;
			$o=$e;
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
}
?>

<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$dari         = checkPostGet('dari', '');
$tujuan       = checkPostGet('tujuan', '');
$tanggal      = tanggalsystemn(checkPostGet('tanggal', ''));
$tanggalsampai= tanggalsystemn(checkPostGet('tanggalsampai', ''));
$method       = checkPostGet('method', '');
$id           = checkPostGet('id', '');
$kodeorg      = checkPostGet('kodeorg', '');
$divisidari   = checkPostGet('divisidari', '');
$divisitujuan = checkPostGet('divisitujuan', '');
$tipetrans    = checkPostGet('tipetrans', '');
$karyawan     = checkPostGet('karyawan', '');
$karyawantemp = checkPostGet('karyawantemp', '');
$karyawantempx= checkPostGet('karyawantempsudahtrans', '');
$sumber       = checkPostGet('sumber', '');
$cari         = checkPostGet('cari', '');
$sudahtrans   = checkPostGet('sudahtrans', '');
$jabatan      = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$tipe         = makeOption($dbname,'sdm_5tipekaryawan','id,tipe');

switch ($method) {
	case'editkary':
		$str = "select * from " . $dbname . ".kebun_5asistensi_dt where id='".$id."'";
		$res = fetchData($str);
		$n=0;$dt="";
		foreach($res as $bar){
			$n++;
			if($n==1){
				$dt.=$bar['karyawanid'];
			}else{
				$dt.=",".$bar['karyawanid'];
			}
		}
		echo $dt;
	break;
	case'getkaryawan':
		if($divisidari=='' and $sumber!='view'){
			exit("Warning: Divisi harus diisi.");
		}
		if($sumber=='view'){$dis="disabled";}else{$dis="";}
		echo"<table width=100%>";
				if($sumber!='view'){
					echo "<td align=left><button class=mybutton onclick=selesai()>" . $_SESSION['lang']['done'] . "</button></td>";
				}
				echo"<td style=text-align:right>
					<label><i>Search : </i></label><input class=myinputtext style=width:170px onkeyup=loadgetkaryawan('".$id."','".$sumber."'); id=cari></td>
			</table>
			<table class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>
				<thead>
				<tr class=rowheader>
					<th align=center style='width:30px;'>No</th>
					<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center>" . $_SESSION['lang']['nik2'] . "</th>
					<th align=center>" . $_SESSION['lang']['nama'] . "</th>
					<th align=center>" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center>" . $_SESSION['lang']['jabatan'] . "</th>
					<th align=center>" . $_SESSION['lang']['tipekaryawan'] . "</th>
					<th  style='width:30px;align:center'>Action<br>
						<input id=checkall ".$dis." type=checkbox onclick=clickall()>
						</th>
				</tr>
				</thead>
				<tbody id=loadgetkaryawan>";


			echo"</tbody>
				<tfoot></tfoot>
				</table>";
	break;
    case 'loadgetkaryawan':
		if($karyawantempx!=''){
			$dtkaryx=explode(",",$karyawantempx);
			foreach($dtkaryx as $karyx){
				$nikx[$karyx]=$karyx;
			} 
		}if($karyawan!=''){
			$dtkary=explode(",",$karyawan);
			foreach($dtkary as $kary){
				$nikselect[$kary]=$kary;
			} 
		}else{
			$str="select * from ".$dbname.".kebun_5asistensi_dt where id ='".$id."'";
			$res = fetchData($str);
			foreach($res as $bar){
				$nikselect[$bar['karyawanid']]=$bar['karyawanid'];
			}
		}
		$where="";
		if($sumber=='view'){
			$where.=" and karyawanid in (select karyawanid from ".$dbname.".kebun_5asistensi_dt where id ='".$id."')";
		}
		if($divisidari!=''){
			$where.="and subbagian ='".$divisidari."'";
		}
		if($cari!=''){
			$dtcari=explode(" ",$cari);
			foreach($dtcari as $dtcr){
				$where.=" and (nik like '%".$dtcr."%' or namakaryawan like '%".$dtcr."%' or subbagian like '%".$dtcr."%')";
			}
		}

		$strnotrans = "SELECT (SELECT notransaksi FROM kebun_aktifitas WHERE tanggal BETWEEN a.tanggal AND a.tanggalsampai AND divisi = a.divisitujuan  LIMIT 1) as notransaksi FROM ".$dbname.".kebun_5asistensi a WHERE id = '".$id."';";
		$resnotrans = fetchData($strnotrans);
		
		$str = "select * from ".$dbname.".datakaryawan where 1=1 ".$where." and tanggalkeluar='0000-00-00' order by tipekaryawan, kodejabatan, namakaryawan";
		$res = fetchData($str);
		foreach($res as $bar){
			$no++;
			echo"<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".$resnotrans[0]['notransaksi']."</td>
				<td align=left width=75px>".$bar['nik']."</td>
				<td align=left hidden name=karyawanid[]>".$bar['karyawanid']."</td>
				<td align=left>".$bar['namakaryawan']."</td>
				<td align=left>".$bar['subbagian']."</td>
				<td align=left>".$jabatan[$bar['kodejabatan']]."</td>
				<td align=left>".$tipe[$bar['tipekaryawan']]."</td>";
				if($sumber=='view' || ($sudahtrans == 1 && $nikx[$bar['karyawanid']])){$dis="disabled";}else{$dis="";}
				if($nikselect[$bar['karyawanid']]!=""){
					echo"<td align=center><input ".$dis." name=check[] type=checkbox checked onclick=addkary()></td>";
				}else{				
					echo"<td align=center><input ".$dis." name=check[] type=checkbox onclick=addkary()></td>";
				}
			echo"</tr>";
		}
	break;
    case 'getdivisiasal':
		$optdiv = "<option value=''>&nbsp;</option>";
		
		$str="select * from ".$dbname.".organisasi where induk = '".$kodeorg."' and tipe = 'AFDELING' ";
		$res = fetchData($str);
		$n = "";
		$z = 0;
		foreach($res as $bar){
			$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$nminduk[$bar['kodeorganisasi']];
			if ($d != $n) {
				$z++;
				if ($z > 1) {
					$z=1;
					$optdiv.="</optgroup>";
				}
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$sel="";
			if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
				$sel="selected";
			}
			
			$optdiv.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			
			$n=$d;
		}
		
		echo $optdiv;
	break;
    case 'getdivisitujuan':
		
		$optdiv = "<option value=''>&nbsp;</option>";
		
		$str="select * from ".$dbname.".organisasi where induk = '".$kodeorg."' and tipe = 'AFDELING' ";
		$res = fetchData($str);
		$n = "";
		$z = 0;
		foreach($res as $bar){
			$nminduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$nminduk[$bar['kodeorganisasi']];
			if ($d != $n) {
				$z++;
				if ($z > 1) {
					$z=1;
					$optdiv.="</optgroup>";
				}
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optdiv.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$sel="";
			if($bar['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
				$sel="selected";
			}
			
			$optdiv.="<option value=".$bar['kodeorganisasi']." ".$sel.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			
			$n=$d;
		}
		
		echo $optdiv;
	break;
    case 'update':
	try {
	$owlPDO->beginTransaction();
	
        if($divisidari==$divisitujuan){
			throw new PDOException("Divisi dari dan divisi tujuan tidak boleh sama.");
		}
		if($karyawan==''){
			//throw new PDOException("Karyawan tidak boleh kosong.");
		}

		if($sudahtrans == 1){
			$dtkary=explode(",",$karyawan);
			foreach($dtkary as $kary){
				$karsave[$kary]=$kary;
			} 
			$str="select * from ".$dbname.".kebun_5asistensi_dt where id='" . $id . "'";
			$res = fetchData($str);
			foreach ($res as $v) {
				$kardulu[$v['karyawanid']] = $v['karyawanid'];
			}
			foreach ($kardulu as $val) {
				if(in_array($val, $karsave)){
				}else{
					// throw new PDOException("Data yang sudah ada ditransaksi tidak boleh dihapus !.");
				}
			}
		}
		
		$str = "update " . $dbname . ".kebun_5asistensi set tipetrans='".$tipetrans."', divisiasal='" . $divisidari . "',divisitujuan='" . $divisitujuan . "',kodeorgasal='" . $dari . "',kodeorgtujuan='".$tujuan."',tanggal= '" . $tanggal . "',tanggalsampai= '" . $tanggalsampai . "',createdby='".$_SESSION['standard']['userid']."',createdtime='".date("Y-m-d H:i:s")."' where id='" . $id . "'";
        $owlPDO->exec($str);
		
		
		$str = "delete from " . $dbname . ".kebun_5asistensi_dt where id='" . $id . "'";
		$owlPDO->exec($str);
		
		
		$dtkary=explode(",",$karyawan);
		foreach($dtkary as $kary){
			$str="insert into " . $dbname . ".kebun_5asistensi_dt (id, karyawanid)
			values('".$id."','" . $kary . "')";
			if($kary!='0000000000'){				
				$owlPDO->exec($str);
			}
		} 
	#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}		
	break;
    case 'insert':
	try {
	$owlPDO->beginTransaction();
	
		if($dari==$tujuan){
			throw new PDOException("Kebun dari dan kebun tujuan tidak boleh sama.");
		}
		
		if($tanggal > $tanggalsampai){
			throw new PDOException("Tanggal dari harus lebih besar atau sama dengan tanggal sampai.");
		}
		if($karyawan==''){
			//throw new PDOException("Karyawan tidak boleh kosong.");
		}
		
		$str="select * from ".$dbname.".kebun_5asistensi where tipetrans ='".$tipetrans."' and kodeorgasal = '".$dari."' and  divisiasal='" . $divisidari . "' and kodeorgtujuan='" . $tujuan . "' and divisitujuan = '".$divisitujuan."' and tanggal='" . $tanggal . "'";
		$res = fetchData($str);
		if(count($res)>0){
			throw new PDOException("Data sudah pernah ada / diinput.");
		}
		
		
		$str = "select max(id) as id from ".$dbname.".kebun_5asistensi";
		$res = fetchData($str);
		$maxid = $res[0]['id']+1;
		
        $str = "insert into " . $dbname . ".kebun_5asistensi (id, tipetrans, divisiasal,divisitujuan,kodeorgasal,kodeorgtujuan,tanggal, posting,createdby,createdtime,tanggalsampai)
		values('".$maxid."', '".$tipetrans."','" . $divisidari . "','" . $divisitujuan . "','" . $dari . "','" . $tujuan . "','" . $tanggal . "','1','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$tanggalsampai."')";
		$owlPDO->exec($str);
		
		
		
		$dtkary=explode(",",$karyawan);
		foreach($dtkary as $kary){
			$str="insert into " . $dbname . ".kebun_5asistensi_dt (id, karyawanid)
			values('".$maxid."','" . $kary . "')";
			if($kary!='0000000000'){				
				$owlPDO->exec($str);
			}
		} 
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
    case 'delete':
        $str = "delete from " . $dbname . ".kebun_5asistensi where id='" . $id . "'";
		try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
        
        break;
    case'loaddata':
		echo"<table id=pvtTable class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>
	     <thead>
		 <tr class=rowheader>
			<th rowspan=2 style='width:30px;'>No</th>
			<th rowspan=2>" . $_SESSION['lang']['tipetransaksi'] . "</th>
			<th colspan=2>" . $_SESSION['lang']['dari'] . "</th>
			<th colspan=2>" . $_SESSION['lang']['tujuan'] . "</th>
			<th colspan=2>" . $_SESSION['lang']['tanggal'] . "</th>
			<th rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
			<th rowspan=2>" . $_SESSION['lang']['status'] . "</th>
			<th colspan=3 style='width:30px;align:center'>Action</th>
		</tr>
		 <tr class=rowheader>
			<th>" . $_SESSION['lang']['kebun'] . "</th>
			<th>" . $_SESSION['lang']['divisi'] . "</th>
			<th>" . $_SESSION['lang']['kebun'] . "</th>
			<th>" . $_SESSION['lang']['divisi'] . "</th>
			<th>" . $_SESSION['lang']['dari'] . "</th>
			<th>" . $_SESSION['lang']['sampai'] . "</th>
			<th></th>
			<th></th>
			<th></th>
			
		</tr>
		 </thead>
		 <tbody>";
		 
    	$where = '';
    	if($tipetrans != ''){
    		if($tipetrans == 'all') {
    			$tipetrans = '';
    		}
    		$where .= " AND tipetrans = '".$tipetrans."'";
    	} 
    	if($kodeorg != ''){
    		$where .= " AND (kodeorgasal = '".$kodeorg."' OR kodeorgtujuan = '".$kodeorg."')";
    	}
    	if($divisidari != ''){
    		$where .= " AND (divisiasal = '".$divisidari."' OR divisitujuan = '".$divisidari."')";
    	}
		$where .= " AND (kodeorgasal in (".getOrgDetail(2).") OR kodeorgtujuan in (".getOrgDetail(2)."))";
		
		// $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }

        // $offset = $page * $limit;
        // $maxdisplay = ($page * $limit);
        // $no = $maxdisplay;
		
		$sql = "select count(*) as notr from " . $dbname . ".kebun_5asistensi WHERE 1=1 ".$where;
        $res = fetchdata($sql);
        $jlhbrs = $res[0]['notr'];
		$colspan = 12;
		
		foreach(getOrgDetail(23) as $kode => $nama){
			$detailakses[$kode]=$kode;
		}
		
		$arrtipe=array('BKM'=>'BKM Rawat','PNN'=>'BKM Panen',''=>'SELURUHNYA','TKBM'=>'BM TBS');
		$str = "select * from " . $dbname . ".kebun_5asistensi WHERE 1=1 ".$where." order by id desc ";
		$res = fetchData($str);
		$no=0;
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		foreach($res as $bar){
			$no++;
			echo"<tr class=rowcontent>
					<td align=center>".$no."</td>";
			if($bar['tipetrans']!=''){			
				echo"<td align=left>".$bar['tipetrans']." - ".$arrtipe[$bar['tipetrans']]."</td>";
			}else{
				echo"<td align=left>".$arrtipe[$bar['tipetrans']]."</td>";
			}
			
			echo"<td align=left>".$bar['kodeorgasal']." - ".$nmorg[$bar['kodeorgasal']]."</td>
					<td align=left>".$bar['divisiasal']." - ".$nmorg[$bar['divisiasal']]."</td>
					<td align=left>".$bar['kodeorgtujuan']." - ".$nmorg[$bar['kodeorgtujuan']]."</td>
					<td align=left>".$bar['divisitujuan']." - ".$nmorg[$bar['divisitujuan']]."</td>
					<td align=center>".tanggalnormal($bar['tanggal'])."</td>
					<td align=center>".tanggalnormal($bar['tanggalsampai'])."</td>
					<td align=left>".getNamaKaryawan($bar['createdby'])."</td>";
			
			$adatrans=false;
			if($bar['tipetrans']=='PNN'){
				$str = "select * from " . $dbname . ".kebun_aktifitas where tipetransaksi='PNN' and tanggal between '".$bar['tanggal']."' and '".$bar['tanggalsampai']."' and divisi='".$bar['divisitujuan']."'";
				$res = fetchData($str);
				if(count($res)>0){				
					$adatrans=true;
				}
			}elseif($bar['tipetrans']=='BKM'){
				$str = "select * from " . $dbname . ".kebun_aktifitas where tipetransaksi!='PNN' and tanggal between '".$bar['tanggal']."' and '".$bar['tanggalsampai']."' and divisi='".$bar['divisitujuan']."'";
				$res = fetchData($str);
				if(count($res)>0){				
					$adatrans=true;
				}
			}else{
				$str = "select * from " . $dbname . ".kebun_aktifitas where tanggal between '".$bar['tanggal']."' and '".$bar['tanggalsampai']."' and divisi='".$bar['divisitujuan']."'";
				$res = fetchData($str);
				if(count($res)>0){				
					$adatrans=true;
				}
			}
			
			if($adatrans==true){
				echo"<td align=center style=cursor:pointer; title='Sudah ada transaksi BKM'>Sudah ada transaksi</td>";
			}else{
				echo"<td align=center style=cursor:pointer;></td>";
			}
			if($adatrans==true){
				if($detailakses[$bar['kodeorgasal']]!=''){
					echo"<td align=center>
							<img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"fillField('" .$bar['id']. "','".$bar['kodeorgasal']."','".$bar['kodeorgtujuan']."','".tanggalnormal($bar['tanggal'])."','".$bar['divisiasal']."','".$bar['divisitujuan']."','".$bar['tipetrans']."','".tanggalnormal($bar['tanggalsampai'])."','".$adatrans."');\"></td>";
				}else{					
					echo"<td align=center style=cursor:pointer;></td>";
				}
				echo"<td align=center style=cursor:pointer;></td>";
			}else if($adatrans == false){
				if($detailakses[$bar['kodeorgasal']]!=''){					
					echo"<td align=center>
							<img src=images/application/application_edit.png class=zImgBtn caption='Edit' onclick=\"fillField('" .$bar['id']. "','".$bar['kodeorgasal']."','".$bar['kodeorgtujuan']."','".tanggalnormal($bar['tanggal'])."','".$bar['divisiasal']."','".$bar['divisitujuan']."','".$bar['tipetrans']."','".tanggalnormal($bar['tanggalsampai'])."','".$adatrans."');\"></td>";
					echo"<td align=center>
							<img src=images/application/application_delete.png class=zImgBtn caption='Delete' onclick=\"del('" .$bar['id']. "');\"></td>";	
				}else{					
					echo"<td align=center style=cursor:pointer;></td>";
					echo"<td align=center style=cursor:pointer;></td>";
				}
			}
			echo"<td align=center><img onclick=getkaryawan('".$bar['id']."','view') class='zImgBtn' src='images/skyblue/zoom.png'></td>";
					
			echo"</tr>";
		}
		
		echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
		//echo createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
		
	break;
}
?>

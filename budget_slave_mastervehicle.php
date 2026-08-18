<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
@$param['nilai']  =str_replace(",","",$param['nilai']);


$nmorg= makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jenisvhc= makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$jab  = getPostingJabatan('budget');

switch ($method) {
    case'html':
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
             <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
		</tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_upah where tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nmgol= makeOption($dbname, 'bgt_kode', 'kodebudget,nama',"kodebudget='".$bar['golongan']."'");
			
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahunbudget'] . "</td>";
            $tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=left>" . $bar['golongan'] . "</td>";
            $tab.="<td align=left>" . $nmgol[$bar['golongan']] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jumlah'], 2) . "</td>";
        }
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
	break;
    case'insert':
		try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}
			
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and  golongan ='" . $param['golongan'] . "'";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah ada.");
			}
			
			$data = array(
				'tahunbudget'=> $param['tahun'],
				'kodeorg'    => $param['kodeorg'],
				'golongan'   => $param['golongan'],
				'jumlah'     => $param['nilai'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'bgt_upah',$data,$cols);
			$owlPDO->exec($query);
			
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
	break;
	case'simpanlama':
		try {
			$owlPDO->beginTransaction();
			
			$query = "delete from " . $dbname . ".bgt_5master where tahunbudget='".$param['tahun']."' and kodeorg = '".$param['kodeorg']."' and kodetraksi='".$param['kodetraksi']."' and status ='1' and sumber='lama'";
			$owlPDO->exec($query);
			
			$str = "select * from " . $dbname . ".vhc_5master where kodeorg = '".$param['kodeorg']."' and kodetraksi='".$param['kodetraksi']."' and status ='1'";
			$res = fetchdata($str);
			foreach($res as $bar){				
				$data = array(
					'tahunbudget'     => $param['tahun'],
					'kodeorg'         => $bar['kodeorg'],
					'kodevhc'         => $bar['kodevhc'],
					'nopol'           => $bar['nopol'],
					'nobpkb'          => $bar['nobpkb'],
					'jenisvhc'        => $bar['jenisvhc'],
					'tahunperolehan'  => $bar['tahunperolehan'],
					'tahunproduksi'   => $bar['tahunproduksi'],
					'warna'           => $bar['warna'],
					'noakun'          => $bar['noakun'],
					'beratkosong'     => $bar['beratkosong'],
					'nomorrangka'     => $bar['nomorrangka'],
					'nomormesin'      => $bar['nomormesin'],
					'detailvhc'       => $bar['detailvhc'],
					'kelompokvhc'     => $bar['kelompokvhc'],
					'kodebarang'      => $bar['kodebarang'],
					'kepemilikan'     => $bar['kepemilikan'],
					'kodetraksi'      => $bar['kodetraksi'],
					'tglakhirstnk'    => $bar['tglakhirstnk'],
					'tglakhirkir'     => $bar['tglakhirkir'],
					'tglakhirijinbm'  => $bar['tglakhirijinbm'],
					'tglakhirijinang' => $bar['tglakhirijinang'],
					'tglakhirleasing' => $bar['tglakhirleasing'],
					'tglakhirasuransi'=> $bar['tglakhirasuransi'],
					'status'          => $bar['status'],
					'kodeasset'       => $bar['kodeasset'],
					'sumber'          => 'lama',
					'createdby'       => $_SESSION['standard']['userid'],
					'createdtime'     => date('Y-m-d H:i:s'),
					'updateby'        => $_SESSION['standard']['userid'],
					'updatetime'      => date('Y-m-d H:i:s')
				);
				$query = insertQuery($dbname,'bgt_5master',$data,array_keys($data));
				$owlPDO->exec($query);
			}
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
	break;
    case'delete':
		$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
		$res = fetchdata($sql);
		$jlhbrs = count($res);
		if ($jlhbrs > 0) {
			exit("Warning : Data sudah di posting / tutup.");
		}
		
        $str = "delete from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'deletedetail':
		$str = "delete from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and golongan='".$param['golongan']."'"; #exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'updatedetail':
	
	   try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}
			
			$data = array(
				'jumlah'     => $param['nilai'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
			);
			
			$where = "tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."' and golongan='".$param['golongan']."'";
		
			$query = updateQuery($dbname,'bgt_upah',$data,$where);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
		break;
    case'posting':
        $str = "update " . $dbname . ".bgt_upah set closed='1' where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'unposting':
		$str = "update " . $dbname . ".bgt_upah set closed='0' where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'previewdt':
		switch ($param['jenis']){
			case'lama':
				$tab = "
					<table cellpadding=5 cellspacing=1 border=0 class=sortable>
					<thead><tr class=rowheader>
					<th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['kodetraksi'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['jeniskend'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['kodevhc'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</th>
					<th align=center rowspan='2'>" . $_SESSION['lang']['detail'] . "</th>
				</tr></thead>";
				
				$no = 0;
				$str = "select * from " . $dbname . ".vhc_5master where kodeorg = '".$param['kodeorg']."' and kodetraksi='".$param['kodetraksi']."' and status ='1'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$no+=1;
					$tab.="<tr class=rowcontent style=height:25px>";
					$tab.="<td align=center>" . $no . "</td>";
					$tab.="<td align=center>" . $param['tahun'] . "</td>";
					$tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
					$tab.="<td align=left>".$nmorg[$bar['kodetraksi']]."</td>";
					$tab.="<td align=left>" . $jenisvhc[$bar['jenisvhc']] . "</td>";
					$tab.="<td align=left>" . $bar['kodevhc'] . "</td>";
					$tab.="<td align=left>" . $bar['nopol'] . "</td>";
					$tab.="<td align=left>" . $bar['detailvhc'] . "</td>";
				}
				$tab.="</tr>";
				$tab.="<tr class=rowcontent style=height:25px>";
				$tab.="<td align=center colspan=8><button class=mybutton onclick=simpanlama();>Simpan</button></td>";
				$tab.="</tr>";
				$tab.="</table>";
			break;
		}
		
        echo $tab;
	break;
	case'getbgtkode':
		$optgol="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$where=" and tipe like 'TRAKSI'";
		$where.=" and kodeorganisasi like '".$param['kodeorg']."%'";
		$str="select * from ".$dbname.".organisasi where 1=1 ".$where."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="";
			if($param['kodetraksi']==$bar['kodebudget']){
				$s="selected";
			}
			$optgol.="<option value=".$bar['kodeorganisasi']." ".$s.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $optgol;
	break;
    case'loaddata':
        $where = "";
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			// $where = "";
		// } else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			// $where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		// } else {
			// $where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		// }
		
		$where = " and kodeorg in (".getOrgDetail(2).")";
		
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg = '".$param['kodeorg']."'";
		}
		
		
        $limit = 15;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = $_POST['page'];if ($page < 0){$page = 0;}}
		
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
        $sql = "select count(*) as jmlhrow from " . $dbname . ".bgt_upah where 1=1 " . $where . " group by tahunbudget,kodeorg";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
        $no = 0;
        $tab = "";
        $no = $maxdisplay;
		$colspan=8;
		
        $str = "SELECT * FROM " . $dbname . ".bgt_upah where 1=1 " . $where . " group by tahunbudget,kodeorg order by tahunbudget desc, kodeorg asc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahunbudget'] . "</td>";
            $tab.="<td>" . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
			
            if($bar['closed'] == 0) {
                $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
                $tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
				$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Close ???' onclick=\"posting('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px></td><td align=center width=25px></td>";
                $tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $tab.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
            $tab.="</tr>";
        }
        
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
        break;
}
?>	
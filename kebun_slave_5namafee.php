<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$nama = checkPostGet('nama','');
$alamat = checkPostGet('alamat','');
$status = checkPostGet('status','');
$id = checkPostGet('id','');

$find_nama = checkPostGet('find_nama','');
$find_unit = checkPostGet('find_unit','');
$find_tt = checkPostGet('find_tt','');




$nmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmvhc=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$nmvhc['GLOBAL']='GLOBAL';
switch ($method) {
	case 'insert':
		try {
		$owlPDO->beginTransaction();
		
			$str = "select * from ".$dbname.".kebun_5namafee where nama='".$nama."' and kodeorg='".$unit."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Nama : ".$nama." sudah terdaftar");
			}
			
			$data = array();
			$data = array(
				'nama' => $nama,
				'alamat' => $alamat,
				'kodeorg' => $unit,
				'status' => $status,
				'updateby' => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_5namafee',$data,$cols);
			$owlPDO->exec($str);

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
	case 'delete':
		$str = "select * from ".$dbname.".kebun_5daftarfee where id='".$id."'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warnig : ".$nama." sudah terdaftar pada menu Kebun - Setup - Harga Loading dan Angkut TBS");
		}
		
		
		$str = "delete from ".$dbname.".kebun_5namafee where id = '".$id."'";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		
	break;
	case'update':
		$where = "id='".$id."'";
		$str = "update " . $dbname . ".kebun_5namafee set nama = '".$nama."',alamat = '".$alamat."',kodeorg = '".$unit."',status = '".$status."' where ".$where."";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
    case'loaddata':
		$tab="<table border=0 cellpadding=1 class=sortable cellspacing=1>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<td align=center rowspan=2>No</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['nama']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['alamat']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['status']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['updateby']."</td> 
						<td align=center rowspan=2 width=50px>".$_SESSION['lang']['action']."</td> 
					</tr>
				</thead>
			<tbody>";
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		$where="";
		if($find_nama!=''){ 
			$where.=" and nama LIKE  '%".$find_nama."%'";
		}
		if($find_unit!=''){ 
			$where.=" and kodeorg LIKE  '%".$find_unit."%'";
		}
		
	
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_5namafee where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		$arrsts=array();
		$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
		$no = 0;
		$str = "select * from ".$dbname.".kebun_5namafee where 0=0 ".$where." order by id desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr style=vertical-align:top class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['nama']."</td>";
            $tab.="<td>".$bar['alamat']."</td>";
            $tab.="<td align=left>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=center>".@$arrsts[$bar['status']]."</td>";
            $tab.="<td>".@getNamaKaryawan($bar['updateby'])."</td>";
            
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"edit('".$bar['id']."','".$bar['nama']."','".$bar['alamat']."','".$bar['kodeorg']."','".$bar['status']."');\" >&nbsp;
				
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['id'] . "');\" >
			</td>";
            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=7 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

    default:
}
?>

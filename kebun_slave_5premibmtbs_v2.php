<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method    = checkPostGet('method','');
$unit      = checkPostGet('unit','');
$divisi    = checkPostGet('divisi','');
$rpangkut  = checkPostGet('rpangkut','');
$denda     = checkPostGet('denda','');
$toleransi = checkPostGet('toleransi','');
$kegiatan = checkPostGet('kegiatan','');
$jenishari = checkPostGet('jenishari','');
$tglberlaku= tanggalsystemn(checkPostGet('tglberlaku',''));
$rpangkut  =str_replace(",","",$rpangkut);
$denda     =str_replace(",","",$denda);
$toleransi =str_replace(",","",$toleransi);

switch ($method) {
	case'getfindtt':
		$opttt = "<option value=''></option>";
		$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$find_blok."%' order by tahuntanam asc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opttt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
		}
		echo $opttt;
	break;
	case'gettahuntanam':
		$opt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe ='AFDELING' and kodeorganisasi like '".$unit."%'";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$opt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
		}
		
		echo $opt;
	break;
	
	case 'insert':
		try {
		$owlPDO->beginTransaction();
			if($unit==''){
				throw new PDOException("Kode Organisasi wajib diisi.");
			}
			if($divisi==''){
				throw new PDOException("Divisi wajib diisi.");
			}
			if($rpangkut==''){
				throw new PDOException("Harga Angkut wajib diisi.");
			}
			if($kegiatan==''){
				throw new PDOException("Kegiatan wajib diisi.");
			}
			
			if($tglberlaku=='--' or $tglberlaku==''){
				throw new PDOException("Tanggal berlaku wajib diisi.");
			}
			
			$data = array(
				'kodeorg'       => $unit,
				'divisi'        => $divisi,
				'kegiatan'      => $kegiatan,
				'harga'         => $rpangkut,
				'denda'         => $denda,
				'toleransi'     => $toleransi,
				'tanggalberlaku'=> $tglberlaku,
				'jenishari'     => $jenishari,
				'updateby'      => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_5premibmtbs_v2',$data,$cols);
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
		$str = "delete from ".$dbname.".kebun_5premibmtbs_v2 where divisi = '".$divisi."'  and tanggalberlaku='".$tglberlaku."'";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		
	break;
	case'update':
		if($unit==''){
				throw new PDOException("Kode Organisasi wajib diisi.");
			}
			if($divisi==''){
				throw new PDOException("Divisi wajib diisi.");
			}
			if($rpangkut==''){
				throw new PDOException("Harga Angkut wajib diisi.");
			}
			if($kegiatan==''){
				throw new PDOException("Kegiatan wajib diisi.");
			}
			
			if($tglberlaku=='--' or $tglberlaku==''){
				throw new PDOException("Tanggal berlaku wajib diisi.");
			}
			
		$str = "update ".$dbname.".kebun_5premibmtbs_v2 set kegiatan='".$kegiatan."',harga='".$rpangkut."',denda='".$denda."',toleransi='".$toleransi."', updateby='".$_SESSION['standard']['userid']."' where divisi = '".$divisi."'  and tanggalberlaku='".$tglberlaku."' and jenishari='".$jenishari."'";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
    case'loaddata':
		
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		$where="";
		if($divisi!=''){ 
			$where.=" and (divisi LIKE  '".$divisi."%' or kodeorg LIKE  '".$divisi."%')";
		}
		
		$where.=" and kodeorg in (".getOrgDetail(2).")";
	
		$ql2 = "select * from " . $dbname . ".kebun_5premibmtbs_v2 where 0=0 ".$where.""; 
        $res = fetchdata($ql2);
		$jlhbrs = count($res);
        
		$no = 0;
		$str = "select * from ".$dbname.".kebun_5premibmtbs_v2 where 0=0 ".$where." order by divisi asc, tanggalberlaku desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$nmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi like '".$bar['kodeorg']."%'");
			$nmkeg=makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan',"kodekegiatan = '".$bar['kegiatan']."'");
			
			$no++;
			$tab.="<tr style=vertical-align:top class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td>".$bar['divisi']." - ".$nmorg[$bar['divisi']]."</td>";
            $tab.="<td>".$bar['kegiatan']." - ".$nmkeg[$bar['kegiatan']]."</td>";
            $tab.="<td align=right>".@number_format($bar['harga'],2)."</td>";
            $tab.="<td align=right hidden>".@number_format($bar['denda'],2)."</td>";
            $tab.="<td align=right hidden>".@number_format($bar['toleransi'],2)."</td>";
            $tab.="<td align=center>".tanggalnormal($bar['tanggalberlaku'])."</td>";
            $tab.="<td>".$bar['jenishari']."</td>";

            
			$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['kodeorg']."','" . $bar['divisi'] . "','".$bar['harga']."','".$bar['denda']."','".$bar['toleransi']."','".tanggalnormal($bar['tanggalberlaku'])."','".$bar['kegiatan']."','".$bar['jenishari']."');\" >	</td>";
			 
            $tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['divisi'] . "','".tanggalnormal($bar['tanggalberlaku'])."');\" >	</td>";
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

		$tab.="<tr><td colspan=9 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

    default:
}
?>

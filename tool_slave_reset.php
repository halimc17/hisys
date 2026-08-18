<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$proses   = $_GET['proses'];
$param = $_POST;
if(count($param)==0){
	$param = $_GET;	
}
$kodevhc  =checkPostGet('kodevhc','');
$kmhmakhir=checkPostGet('kmhmakhir','');
$tanggal  =tanggalsystemn(checkPostGet('tanggal',''));

switch($proses) {
	case'simpan':
		try {
		$owlPDO->beginTransaction();
			$data = array();
			$data = array(
				'kmhmawal' =>$param['kmhmawal'],
				'kmhmakhir'=>$param['kmhmakhir'],
				'jumlah'   =>$param['jumlah']
			);
			
			$where = "notransaksi='".$param['notransaksi']."' and jenispekerjaan='".$param['jenispekerjaan']."' and alokasibiaya='".$param['alokasibiaya']."' and beratmuatan='".$param['beratmuatan']."'";
			
			$query = updateQuery($dbname,'vhc_rundt',$data,$where); #exit("error".$query);
			$owlPDO->exec($query);
			
			$data = array();
			$data = array(
				'kmhmakhir'=>$param['kmhmakhir']
			);
			$where = "kodevhc='".$param['kodevhc']."'";
			
			$query = updateQuery($dbname,'vhc_kmhm_track',$data,$where);
			$owlPDO->exec($query);
			
			
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case'preview':
		if($param['notransaksi']==''){
			//exit("errorcode : Nomor transaksi wajib terisi.");
		}
	
	
		$tab.="<table class=sortable border=0 cellpadding=5 cellspacing=1>"; 
		
		$tab.="
		  <thead>
			<tr class=rowcontent>
			  <td bgcolor=#DEDEDE align=center rowspan=2>No.</th>
			  <th bgcolor=#DEDEDE align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
			  <th bgcolor=#DEDEDE align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
			  <th bgcolor=#DEDEDE align=center rowspan=2>".$_SESSION['lang']['kodekegiatan']."</th>
			  <th bgcolor=#DEDEDE align=center rowspan=2>".$_SESSION['lang']['namakegiatan']."</th>
			  <th bgcolor=#DEDEDE align=center rowspan=2>".$_SESSION['lang']['alokasi']."</th>
			  <th bgcolor=#DEDEDE align=center colspan=3>Data Awal</th>
			  <th bgcolor=#DEDEDE align=center colspan=3>Perbaikan</th>
			</tr>  
			<tr class=rowcontent>
			  <th bgcolor=#DEDEDE align=center>Awal</th>
			  <th bgcolor=#DEDEDE align=center>Akhir</th>
			  <th bgcolor=#DEDEDE align=center>Jumlah</th>
			  <th bgcolor=#DEDEDE align=center>Awal</th>
			  <th bgcolor=#DEDEDE align=center>Akhir</th>
			  <th bgcolor=#DEDEDE align=center>Jumlah</th>
			
          ";
        $tab.="</tr>
      </thead>
      <tbody>";
		
		$nmkeg = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
		
		$str="select  * from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where b.kodevhc='".$kodevhc."' and tanggal >= '".$tanggal."' order by kmhmawal asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent id=row".$no.">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td style=display:none id=beratmuatan".$no.">".$bar['beratmuatan']."</td>";
			$tab.="<td id=notransaksi".$no.">".$bar['notransaksi']."</td>";
			$tab.="<td>".$bar['tanggal']."</td>";
			$tab.="<td id=jenispekerjaan".$no.">".$bar['jenispekerjaan']."</td>";
			$tab.="<td>".$nmkeg[$bar['jenispekerjaan']]."</td>";
			$tab.="<td id=alokasibiaya".$no.">".$bar['alokasibiaya']."</td>";
			$tab.="<td align=right>".$bar['kmhmawal']."</td>";
			$tab.="<td align=right>".$bar['kmhmakhir']."</td>";
			$tab.="<td align=right>".$bar['jumlah']."</td>";
			$tab.="<td><input class=myinputtextnumber disabled style=width:70px id=kmhmawal".$no." value=".$bar['kmhmawal']."></td>";
			$tab.="<td><input class=myinputtextnumber onkeyup=hitunghm('".$no."',this.value); style=width:70px id=kmhmakhir".$no." value=".$bar['kmhmakhir']."></td>";
			$tab.="<td><input class=myinputtextnumber disabled style=width:70px id=jumlah".$no." value=".$bar['jumlah']."></td>";
			
			$tab.="</tr>";
			
			$sql = "select  * from ".$dbname.".setup_periodeakuntansi  where periode like '".substr($bar['tanggal'],0,7)."%' and kodeorg='".substr($bar['alokasibiaya'],0,4)."' and tutupbuku='1'";
			$req = fetchdata($sql);
			if(count($req)>0){
				exit("errorcode : Periode akuntansi ".substr($bar['tanggal'],0,7)." unit ".substr($bar['alokasibiaya'],0,4)." sudah ditutup.");
			}
		}
		
		
		
		$tab.="<input style=display:none id=totalbaris value=".$no.">";
		$tab.="<tr><td colspan=13><center><button class=mybutton onclick=simpan()>Simpan</button></center></td></tr>";
		$tab.="</tbody></table>";
		echo $tab;
	break;
	case'getKm':
		$qKm = selectQuery($dbname,'vhc_kmhm_track','kmhmakhir',"kodevhc='".$kodevhc."'");
		$resKm = fetchData($qKm);
		if(empty($resKm))
			$km =  0;
		else
			$km = $resKm[0]['kmhmakhir'];
		
		$optorg="";
		if($tanggal!='--'){			
			//$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str="select distinct a.notransaksi from ".$dbname.".vhc_rundt a left join ".$dbname.".vhc_runht b on a.notransaksi=b.notransaksi where b.kodevhc='".$kodevhc."' and tanggal = '".$tanggal."' order by kmhmawal asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$optorg.="<option value=".$bar['notransaksi'].">".$bar['notransaksi']."</option>";
			}
		}
		echo $km."####".$optorg;
	break;
	case 'reset':
		//$dataIns = array($kodevhc,$kmhmakhir);
		// $qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);
		// $dataUpd = array('kmhmakhir'=>$kmhmakhir);
		// $qUpd = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,"kodevhc='".$kodevhc."'");                
		// try{
				  // $owlPDO->exec($qIns);
				  // $owlPDO->exec($qUpd);          
		  // }
		  // catch (PDOException $e) {
					 // print " Gagal  !: " . $e->getMessage() . "<br/>";
					 // die();
			  // }
		
		$sCek="select * from ".$dbname.".vhc_kmhm_track where kodevhc='".$kodevhc."'";
		$rCek=fetchdata($sCek);
		if(count($rCek)==0){
			$qIns = insertQuery($dbname,'vhc_kmhm_track',$dataIns);	
		}else{
			$dataIns = array($kodevhc,$kmhmakhir);
        	$dataUpd = array('kmhmakhir'=>$kmhmakhir);
        	$qIns = updateQuery($dbname,'vhc_kmhm_track',$dataUpd,"kodevhc='".$kodevhc."'");                	
		}
        try{
                  $owlPDO->exec($qIns);
                  //$owlPDO->exec($qUpd);          
        }catch (PDOException $e) {
                     print " Gagal  !: " . $e->getMessage() . "<br/>";
                     die();
        }
		break;
}
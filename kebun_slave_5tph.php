<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('phpqrcode/qrlib.php');

$kodeorg=checkPostGet('kodeorg','');
$notph=checkPostGet('notph','');
$notphbesar=checkPostGet('notphbesar','');
$keterangan=checkPostGet('keterangan','');
$kodeorgsrc=checkPostGet('kodeorgsrc','');
$notphsrc=checkPostGet('notphsrc','');
$lat=checkPostGet('lat','');
$lon=checkPostGet('lon','');
$sts=checkPostGet('sts','');
$luas=checkPostGet('luas','');
$page = checkPostGet('page','');

switch($_POST['aksi']){
    case 'save':
          $str="insert into ".$dbname.".kebun_5tph(kode,kodetphbesar,kodeorg,keterangan,latitude,logitude,luas,status) values('".$notph."','".$notphbesar."','".$kodeorg."','".$keterangan."','".$lat."','".$lon."','".$sts."','".$luas."')"; 
            try{$owlPDO->exec($str); }
            catch (PDOException $e) 
            {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        
        break;
    case 'edit':
         $str="update ".$dbname.".kebun_5tph set keterangan='".$keterangan."',latitude='".$lat."',logitude='".$lon."',luas='".$luas."',status='".$sts."' where kodeorg='".$kodeorg."'
               and kode='".$notph."'";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) 
            {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }        
        break;
    case 'del':
         $str="delete from ".$dbname.".kebun_5tph  where kodeorg='".$kodeorg."'
               and kode='".$notph."'";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) 
            {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }       
        break;
        case 'list':
		$tab='';
		$tab.="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			 <thead>
			   <tr class=rowheader>
				   <th align=center>".$_SESSION['lang']['nomor']."</th>
				   <th align=center>".$_SESSION['lang']['kodeblok']."</th>
				   <th align=center>".$_SESSION['lang']['notph']."</th>    
				   <th align=center>".$_SESSION['lang']['notph']." besar</th>    
				   <th align=center>".$_SESSION['lang']['keterangan']."</th>
				   <th align=center>Latitude</th>
				   <th align=center>Longitude</th>
				   <th align=center>".$_SESSION['lang']['luas']."</th>
				   <th align=center>".$_SESSION['lang']['status']."</th>
				   <th align=center>QR</th>
				   <th align=center colspan=2>".$_SESSION['lang']['action']."</th>    
			   </tr>
			 </thead>
			 <tbody>";
			$where='';
			if($kodeorg=='undefined'){
				$kodeorg='';
			}
			if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){			
				$where.= " and kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
			}
			if($kodeorg!=''){
				$where.=" and kodeorg='".$kodeorg."'";
			}
			if($kodeorgsrc!=''){
				$where.=" and kodeorg like '%".$kodeorgsrc."%'";
			}
			if($notphsrc!=''){
				$where.=" and kode like '%".$notphsrc."%'";
			}
		
		$limit = 30;
		$pagex = 0;
		$page = isset($page) ? $page : '0';
		if (isset($page)) {
			$pagex = $page;
			if ($pagex < 0)
				$pagex = 0;
		}
		$sql = "select * from ".$dbname.".kebun_5tph where 1=1 ".$where." order by kode";
        $jlhbrs = count(fetchdata($sql));

        $offset = $pagex * $limit;
        $maxdisplay = ($pagex * $limit);
		$no = 0;
		$no = $maxdisplay;

			$arrsts=array('A'=>'Aktip','D'=>'Non Aktip');
			$str="select * from ".$dbname.".kebun_5tph where 1=1 ".$where." order by kode limit " . $offset . "," . $limit . "";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch()){
			$no+=1;
			$PreviewQR = "";
			$size_in_mb = "";
			$size_in_bytes = "";
			if($bar->kode != ""){
				ob_start();
				QRcode::png($bar->kode,false,QR_ECLEVEL_L,20);
				$imgData=ob_get_clean();
				$sourceQr = base64_encode($imgData);
				$size_in_bytes = (int) (strlen(rtrim($sourceQr, '=')) * 3 / 4);
				$size_in_kb    = $size_in_bytes / 1024;
				$size_in_mb    = $size_in_kb / 1024;
				// $filesize = 20;
				$PreviewQR = '<a download="'.$bar->kode.'.png" href="data:image/png;base64,'.$sourceQr.'" title="'.$bar->kode.'" filesize="'.$size_in_bytes.'">
				<img width=40 id="qr_'.$no.'" name="'.$bar->kode.'" src="data:image/png;base64,'.$sourceQr.'" /></a>';
			}
			$tab.="<tr class=rowcontent>
					   <td align=center>".$no."</td>
					   <td>".$bar->kodeorg."</td>
					   <td>".$bar->kode."</td>    
					   <td>".$bar->kodetphbesar."</td>    
					   <td>".$bar->keterangan."</td>
					   <td>".$bar->latitude."</td>
					   <td>".$bar->logitude."</td>
					   <td>".number_format($bar->luas,3)."</td>
					   <td>".$arrsts[$bar->status]."</td>
					   <td>".$PreviewQR."<br><small>".$size_in_bytes." byte</small></td>
					   <td align=center width=25px>
						   <img id='detail_edit' title='edit data' class=zImgBtn onclick=\"editData('".$bar->kodeorg."','".$bar->kode."','<option value=".$bar->kodetphbesar.">".$bar->kodetphbesar."</option>','".$bar->keterangan."','".$bar->latitude."','".$bar->logitude."','".$bar->luas."','".$bar->status."')\" src='images/application/application_edit.png'/>    
					   </td>    
						<td align=center width=25px>   
						   <img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteData('".$bar->kodeorg."','".$bar->kode."')\" src='images/application/application_delete.png'/>
					   </td>    
					   
				   </tr> ";    
			}
			$tab.="</tbody>";

			$totrows = ceil($jlhbrs / $limit);
			if ($totrows == 0) {
				$totrows = 1;
			}
			
			$isiRow = '';
			for ($er = 1; $er <= $totrows; $er++) {
				$sel = ($page == $er - 1) ? 'selected' : '';
				$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
			}

			$tab.="<tfoot>";
				$tab.="<tr><td colspan=12 align=center>";
				if ($pagex == '0') {
					$tab.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
				} else {
					$tab.="<button class=mybutton onclick=getList(" . ($pagex - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
				}
				$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
				if (($pagex + 1) == $totrows) {
					$tab.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
				} else {
					$tab.="<button class=mybutton onclick=getList(" . ($pagex + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
				}
				$tab.="</td></tr>";
			$tab.="</tfoot>";
			
			$tab.="</table>";
				
		$notph='';	
		$str = "select max(substr(kode,11,2)) as no from ".$dbname.".kebun_5tph where kodeorg='".$kodeorg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if(intval($bar['no'])!=''){
			$nourut = addZero(intval($bar['no'])+1,2);
		}else{
			$nourut = '01';
		}
		$notph = $kodeorg.$nourut;


		$notphbesar='';	
		$str = "select * from ".$dbname.".kebun_5tphbesar where divisi='".substr($kodeorg,0,6)."'";
		//echo $str;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$notphbesar.="<option value='".$bar['notph']."'>".$bar['notph']."</option>";
		}
		
		
			echo $tab."####".$notph."####".$notphbesar;
         break;
    default:
        break;
}
?>
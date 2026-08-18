<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method', '');
$kodeorg= checkPostGet('kodeorg', '');
$divisi = checkPostGet('divisi', '');
$tgl1   = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2   = tanggalsystemn(checkPostGet('tgl2', ''));
$param  = $_POST;if(count($param)==0){$param= $_GET;}
$path   = "fileupload/bkm/";

switch($method){
	case'preview':
		$tab="<table id=mytable class='sortable' cellspacing=1 width=100%>";
		$tab.="
			<thead>
				<tr class=rowheader>
					<th align=center rowspan=2 >" . $_SESSION['lang']['tipetransaksi'] . "</th>
					<th align=center rowspan=2 >No BKM</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['notransaksi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['unit'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['divisi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['hari'] . "</th>
					<th align=center rowspan=2 width=100px>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['jhk'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['jjg'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['upah'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['premi'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['mandor'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['mandor'] . " 1</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['kerani'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['nikasisten'] . "</th>
					<th align=center rowspan=2 >" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center colspan='2'>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				<tr class=rowheader>
					<th style=display:none></th>
					<th style=display:none></th>
				</tr>
			</thead>
		 <tbody>";

		if($kodeorg==""){
			exit("warning : Kode organisasi harus diisi.");
		}
		if($tgl1=="--"){
			exit("warning : Tanggal dari harus diisi.");
		}
		if($tgl1=="--"){
			exit("warning : Tanggal sampai harus diisi.");
		}

		$where="";
		if ($divisi != '') {
			$where.=" and a.divisi like '" . $divisi . "%' ";
		}
		if (($tgl1 != '') and ($tgl1 != '--')) {
			$where.=" and a.tanggal >='" . $tgl1 . "' ";
			$whsdm.=" and tanggal >='" . $tgl1 . "' ";
		}
		if (($tgl2 != '') and ($tgl2 != '--')) {
			$where.=" and a.tanggal <='" . $tgl2 . "' ";
			$whsdm.=" and tanggal <='" . $tgl2 . "' ";
		} 
		if ($kodeorg != '') {
			$where.=" and a.kodeorg like '%".$kodeorg."%'";
			$whsdm.=" and substr(kodeorg,1,4)='".$kodeorg."'";
			$wh3.= "and a.kodeorg='".$kodeorg."'";
		} 

		$wh3.=" and a.tanggal like '" .substr($tgl2,0,7). "%' ";

		$ttl=array();
		$strn = "select karyawanid, norefrensi, nobkm, kodeorg,sum(umr) as umr, sum(premi) as premi, sum(hk) as hk from ".$dbname.".sdm_absensidt where norefrensi!='' and nobkm!='' ".$whsdm." group by norefrensi, nobkm, karyawanid"; 
		$resn = fetchdata($strn);
		foreach ($resn as $bar) {
			if(getKary($bar['karyawanid'],'tipekaryawan')==4){				
				$umrab[$bar['norefrensi']][$bar['nobkm']]+=$bar['umr'];
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['umr']+$bar['premi'];
			}else{				
				$ttl[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk']+$bar['premi'];
			}
			// premi bkm jangan pake $resn[0]['premi']
			$hkab[$bar['norefrensi']][$bar['nobkm']]+=$bar['hk'];
			$premab[$bar['norefrensi']][$bar['nobkm']]+=$bar['premi'];
		}

		$strx = "select sum(umr) as umr, sum(jhk) as jhk, sum(insentif) as insentif, notransaksi from ".$dbname.".kebun_kehadiran where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas a where 1=1 ".$wh3." and a.tipetransaksi not in ('PNN')) group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$umr[$bar['notransaksi']]+=$bar['umr'];
			$hkp[$bar['notransaksi']]+=$bar['jhk'];
			$premip[$bar['notransaksi']]+=$bar['insentif'];
			$ttlrp2[$bar['notransaksi']]+=$bar['umr']+$bar['jhk']+$bar['insentif'];
		}

		$strx = "select kodeorg, sum(hasilkerja) as pres,sum(upahkerja) as umr, sum(jumlahhk) as jhk, sum(upahpremi+upahpremilebihbasis+upahpremilebihbasis2+premibasis+premibasis2) as insentif, notransaksi from ".$dbname.".kebun_prestasi where notransaksi in (select notransaksi from " . $dbname . ".kebun_aktifitas a where 1=1 ".$wh3." and a.tipetransaksi in ('PNN')) group by notransaksi"; 
		$resn = fetchdata($strx);
		foreach ($resn as $bar) {
			$umr[$bar['notransaksi']]+=$bar['umr'];
			$hkp[$bar['notransaksi']]+=$bar['jhk'];
			$premip[$bar['notransaksi']]+=$bar['insentif'];
			$ttlrp2[$bar['notransaksi']]+=$bar['umr']+$bar['jhk']+$bar['insentif']+$bar['pres'];
			$jjgpnn[$bar['notransaksi']]+=$bar['pres'];
			$divisipres[$bar['notransaksi']]=substr($bar['kodeorg'],0,6);
		}

		$statusblok='BKM';

		$str = "SELECT * FROM " . $dbname . ".kebun_aktifitas a where 1=1 " . $where . " order by a.nobkm desc, a.notransaksi desc"; 
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if($ttlrp2[$bar['notransaksi']]=='0' and $ttl[$bar['notransaksi']][$bar['nobkm']]==0){
				$cl=" style=background-color:red; title=\"Data detail belum ada.\"";
			}elseif($ttlrp2[$bar['notransaksi']]=='0' and $ttl[$bar['notransaksi']][$bar['nobkm']]>0){
				$cl=" style=background-color:yellow; title=\"Data hanya absensi.\"";
				$abs="absensi";
			}
			if($bar['nobkm']==''){
				$bar['nobkm']=$bar['noreferensi'];
			}
			if($bar['divisi']==''){
				$bar['divisi']=$divisipres[$bar['notransaksi']];
			}
			
			$hari=$c="";
			$hari = date('D', strtotime($bar['tanggal']));
			if($hari=='Sun'){
				$c="style=\"color:red\"";
			}
			if($hari=='Fri'){
				$c="style=\"color:blue\"";
			}
			
			$a=$a1=$b=$b1=$d=$d1="";
			if(getSubbagian($bar['nikmandor'])!=$bar['divisi']){
				$a="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor'])."</i></b></font>";				
				$a1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['nikmandor1'])!=$bar['divisi']){
				$b="<br><font size=1px color=blue><b><i>".getSubbagian($bar['nikmandor1'])."</i></b></font>";				
				$b1="title=\"Karyawan asistensi\"";				
			}
			if(getSubbagian($bar['keranimuat'])!=$bar['divisi']){
				$d="<br><font size=1px color=blue><b><i>".getSubbagian($bar['keranimuat'])."</i></b></font>";				
				$d1="title=\"Karyawan asistensi\"";				
			}
			if($bar['tipetransaksi']=='PNN' and $bar['noreferensi']!=""){
				$sumber="Proses Panen";
			}elseif($bar['tipetransaksi']=='PNN'){
				$sumber="BKM Panen";
			}elseif($bar['tipetransaksi']!='PNN'){
				$sumber="BKM Rawat";
			}
			
			
			$tab.="<tr class=rowcontent ".$xx." ".$cl." id=tr_$no>";
			$tab.="<td align=center>".$sumber."</td>";
			$tab.="<td align=center>" . $bar['nobkm'] . "</td>";
			$tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
			$tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
			$tab.="<td align=center>" . $bar['divisi'] . "</td>";
			$tab.="<td align=center ".$c.">" . hari($bar['tanggal'],'ID') . "</td>";
			$tab.="<td align=center ".$c.">" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab.="<td align=center>" . @numb_format($hkp[$bar['notransaksi']]+$hkab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			$tab.="<td align=center>" . @numb_format($jjgpnn[$bar['notransaksi']]) . "</td>";
			$tab.="<td align=right>" . @numb_format($umr[$bar['notransaksi']]+$umrab[$bar['notransaksi']][$bar['nobkm']]) . "</td>";
			$tab.="<td align=right>" . @numb_format($premip[$bar['notransaksi']]+$premab[$bar['notransaksi']][$bar['nobkm']],2) . "</td>";
			$tab.="<td align=center ".$a1.">" . getKary($bar['nikmandor']). "".$a."</td>";
			$tab.="<td align=center ".$b1.">" . getKary($bar['nikmandor1']) . "".$b."</td>";
			$tab.="<td align=center ".$d1.">" . getKary($bar['keranimuat']) . "".$d."</td>";
			$tab.="<td align=center>" . getKary($bar['nikasisten']). "</td>";
			$tab.="<td align=center>" . getKary($bar['updateby']). "</td>";
			if($bar['tipetransaksi']!='PNN'){		
				$tab.="<td align=center style=width:20px><img src=images/download.png class=zImgBtn class=zImgBtn height='30'  title='Download' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','BKM','html');\" ></td>";
			}else{
				$tab.="<td align=center style=width:20px><img src=images/download.png class=zImgBtn class=zImgBtn height='30'  title='Download' onclick=\"showupload('".$bar['notransaksi']."');\" ></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','PNN','html');\" ></td>";
			}
			
			
			$tab.="</tr>";
		} 
		echo $tab; 
	break;
	case'loadfiles':
		$tab.="
			<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center' width=50px>File Type</th>
					<th align='center'>Filename</th>
					<th align='center' width=30px colspan=2>Action</th>
				</tr>
				</thead>
				<tbody>";
				
				$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
				$res= fetchData($str);
				if(empty($res)){
					$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}else{
					foreach($res as $key=>$val){
						$no++;
						$tab.="<tr class=rowcontent>
								<td style='text-align:center'>".$no."</td>";
						$icon=seticonfile($val['formaticon']);
						$tab.="<td style='text-align:center'>
								<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
							</td>";
						$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
						$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
						$tab.="</tr>";
					}
				}
				
			$tab.="</tbody>
			</table>
		";
		echo $tab;
	break;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>
<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$param        = $_POST;
$method       = checkPostGet('method', '');
$notransaksi  = checkPostGet('notransaksi', '');
$tgl          = tanggalsystemn(checkPostGet('tgl', ''));
$kodeorg      = checkPostGet('kodeorg', '');
$mode         = checkPostGet('mode', '');
$filterdivisi = checkPostGet('filterdivisi', '');
$kgwb         = checkPostGet('kgwb', '');
$nospb        = checkPostGet('nospb', '');
$jenis        = checkPostGet('jenis', '');
$kgpks        = checkPostGet('kgpks', '');
$ttlkgbmtbs        = checkPostGet('ttlkgbmtbs', '');
$numrow        = checkPostGet('numrow', '');
$kepada        = checkPostGet('kepada', '');

$kgwb         = str_replace(",","",$kgwb);
$kgpks         = str_replace(",","",$kgpks);
$divisi       = checkPostGet('divisi', '');
$hk           = checkPostGet('hk', '');
$kghasilkerja = checkPostGet('kghasilkerja', '');
$rphk         = checkPostGet('rphk', '');
$rpmdr        = checkPostGet('rpmdr', '');
$rpkrn        = checkPostGet('rpkrn', '');
$rpmdrtrk     = checkPostGet('rpmdrtrk', '');
$kgbm         = checkPostGet('kgbm', '');
$rpupahbm     = checkPostGet('rpupahbm', '');
$rppremibm    = checkPostGet('rppremibm', '');
$notrbm       = checkPostGet('notrbm', '');
$rpmdr1       = checkPostGet('rpmdr1', '');
$hk           = str_replace(",","",$hk);
$kghasilkerja = str_replace(",","",$kghasilkerja);
$rphk         = str_replace(",","",$rphk);
$rpmdr        = str_replace(",","",$rpmdr);
$rpkrn        = str_replace(",","",$rpkrn);
$rpmdrtrk     = str_replace(",","",$rpmdrtrk);
$kgbm         = str_replace(",","",$kgbm);
$rpupahbm     = str_replace(",","",$rpupahbm);
$rppremibm    = str_replace(",","",$rppremibm);
$rpmdr1    = str_replace(",","",$rpmdr1);


$divsch = checkPostGet('divsch', '');
$tglmulai = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai = tanggalsystemn(checkPostGet('tglselesai', ''));
$notransaksisch = checkPostGet('notransaksisch', '');
$postingsrc = checkPostGet('postingsrc', '');
$periodesch = checkPostGet('periodesch', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

switch ($method) {
	case'preview':
	 $theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $gen='generic.css';
	}else if($theme=='red'){
	  $gen='genericRed.css';  
	}else{
	  $gen='genericGray.css';  
	} 
	echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>
	"; 
	$rows="rowspan=2";	
	$tab='';
	
	$countApprove = getCountApproval('KONTAN',$kodeorg);
	$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
	
	$str=" select * from ".$dbname.".kebun_realkontanan where  notransaksi='".$notransaksi."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	if($jenis=='html'){
		$border = " border=0 ";
	
		$tab.= "<table ".$border." cellspacing=1 class=sortable width=100%>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
					for($i=1;$i<=$countApprove;$i++){
						$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
					}
					
			$tab.= "
				</tr>
				</thead>
				<tbody>";
				$tab.= "<tr class=rowcontent>
						<td>".$nmkar[$bar['updateby']]."</td>";
					for($i=1;$i<=$countApprove;$i++){
						$arrApp = detailApprove($i,$notransaksi,'KONTAN');
						
						if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
							$tngl='';
						}else{
							$tngl=tanggalnormal($arrApp['tanggal']);
						}
						
						if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
							$tab.= "<td>".$arrApp['nama']."
								<br />".$arrHsl[$arrApp['status']]."
								<br>".$tngl."
							</td>";
						}else{
							$tab.= "<td>&nbsp;</td>";
						}
					}
					
				
				$tab.= "</tbody>
				</table><hr>";
	}else{
		$border = " border=1 ";
	}
			
	$tab.="<table cellpadding=1 cellspacing=1 ".$border." class=sortable style='min-width:100%'>";
	$tab.="<thead><td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi']."</td>
				<td align=center colspan=2>Kirim ke PKS</td>
				<td align=center colspan=3>Panen</td>
				<td align=center colspan=5>Pengawas</td>
				<td align=center colspan=5>BM TBS</td>
				<td align=center ".$rows.">Total</td>
			</tr>
			<tr>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center>Detail SPB</td>
				<td align=center width=45px>HK</td>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center>Upah</td>
				<td align=center>Mandor</td>
				<td align=center>Kerani</td>
				<td align=center>Mandor 1</td>
				<td align=center>Mandor<br>Traksi</td>
				<td align=center>Total</td>
				<td align=center width=75px>Tanggal</td>
				<td align=center width=65px><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center width=75px>Upah</td>
				<td align=center width=75px>Premi</td>
				<td align=center width=75px>Total</td>
				
			</tr>
			</thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_realkontanandt a left join " . $dbname . ".kebun_realkontanan b on a.notransaksi=b.notransaksi where a.notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=20 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td valign=top align=center>" . $no . "</td>";
				$tab.="<td valign=top align=left>" . $bar['divisi'] . "</td>";
				
				# SPB
				$strjlh = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_realkontanan_spb where notransaksi='".$bar['notransaksi']."' and divisi='".$bar['divisi']."'";
				$strx = "select sum(kgwb) as kgwb, nospb, tanggal from " . $dbname . ".kebun_realkontanan_spb where notransaksi='".$bar['notransaksi']."' and divisi='".$bar['divisi']."' group by nospb";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$kgspb=fetchData($strjlh);
				$tab.="<td valign=top align=center>".@hidezerodecimal($kgspb[0]['kgwb'])."</td>";
				$tab.="<td valign=top align=center>";
				$nox='';
				$tab.="<table cellpadding=0 cellspacing=1 border=0 class=sortable width=100%>";
				while($barx = $resx->fetch()){
					$nox++;
					$tab.="<tr class=rowcontent style='cursor:pointer;color:blue;' onclick=getdetaildata('".$barx['nospb']."','','','spb')>";
					$tab.="<td valign=top align=center>".$nox."</td>";
					$tab.="<td valign=top align=left>".$barx['nospb']."</td>";
					$tab.="<td valign=top align=center>".$barx['tanggal']."</td>";
					$tab.="<td valign=top align=right>".$barx['kgwb']."</td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
				$tab.="</td>";
				#pemanen
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','','pemanen')>" . @hidezerodecimal($bar['hk']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','','pemanen')>" . @hidezerodecimal($bar['kghasilkerja']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','','pemanen')>" . @hidezerodecimal($bar['rphk']) . "</td>";
				
				#pengawas
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','MANDORPANEN','pengawas')>" . @hidezerodecimal($bar['rpmdr']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','KERANIPANEN','pengawas')>" . @hidezerodecimal($bar['rpkrn']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','MANDOR1','pengawas')>" . @hidezerodecimal($bar['rpmdr1']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','MANDORTRAKSI','pengawas')>" . @hidezerodecimal($bar['rpmdrtrk']) . "</td>";
				$tab.="<td valign=top align=right style='cursor:pointer;color:blue;' onclick=getdetaildata('".$bar['tanggal']."','".$bar['divisi']."','','pengawas')>" . @hidezerodecimal($bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']) . "</td>";
				
				#BM TBS
				$tab.="<td valign=top align=center colspan=5>";
				$tab.="<table cellpadding=0 cellspacing=1 border=0 class=sortable width=100%>";
				$strz = "select * from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi='".$bar['notransaksi']."'  and divisi='".$bar['divisi']."'";
				$resz = $owlPDO->query($strz) or die(print " Gagal: " . PDOException::getMessage());
				$resz->setFetchMode(PDO::FETCH_ASSOC);
				$ttlbm='';
				while($barz = $resz->fetch()){
					$tab.="<tr class=rowcontent  style='cursor:pointer;color:blue;' onclick=getdetaildata('".$barz['tanggal']."','".$barz['divisi']."','".$barz['notrbm']."','bmtbs')>";
					$tab.="<td valign=top width=75px align=center>".$barz['tanggal']."</td>";
					$tab.="<td valign=top width=65px align=right>".@hidezerodecimal($barz['kgbm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rpupahbm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rppremibm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rppremibm']+$barz['rpupahbm'])."</td>";
					$tab.="</tr>";
					@$ttlbm+=($barz['rppremibm']+$barz['rpupahbm']);

					@$ttlkgbm+=$barz['kgbm'];
					@$ttlrpbm+=$barz['rpupahbm'];
					@$ttlprebm+=$barz['rppremibm'];
				}
				$tab.="</table>";
				$tab.="</td>";
				
				$tab.="<td valign=top align=right>".@hidezerodecimal($bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']+$ttlbm+$bar['rphk'])."</td>";
				
				@$ttlkgpks+=$kgspb[0]['kgwb'];
				@$ttlhk+=$bar['hk'];
				@$ttlkgpnn+=$bar['kghasilkerja'];
				@$ttlupah+=$bar['rphk'];
				@$ttlmdr+=$bar['rpmdr'];
				@$ttlkrn+=$bar['rpkrn'];
				@$ttlmdr1+=$bar['rpmdr1'];
				@$ttlmdrtrk+=$bar['rpmdrtrk'];
				
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td valign=top align=center colspan=2>TOTAL</td>";
			$tab.="<td valign=top align=right>".number_format($ttlkgpks)."</td>";
			$tab.="<td valign=top align=right></td>";
			$tab.="<td valign=top align=right>".number_format($ttlhk)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlkgpnn)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlupah)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlmdr)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlkrn)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlmdr1)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlmdrtrk)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlmdrtrk+$ttlmdr1+$ttlkrn+$ttlmdr)."</td>";
			$tab.="<td valign=top align=right></td>";
			$tab.="<td valign=top align=right>".number_format($ttlkgbm)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlrpbm)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlprebm)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlprebm+$ttlrpbm)."</td>";
			$tab.="<td valign=top align=right>".number_format($ttlprebm+$ttlrpbm+$ttlmdrtrk+$ttlmdr1+$ttlkrn+$ttlmdr+$ttlupah)."</td>";
			$tab.="</tr>";
		}
        $tab.="</tr>";
        $tab.="</table>";

		if($jenis=='html'){
			echo $tab;
		}elseif($jenis=='excel'){
			
		$stream = $tab;
        $nop_ = "kontanan";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
								parent.window.alert('Cant convert to excel format');
								</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
								window.location='tempExcel/" . $nop_ . ".xls';
								</script>";
				}
				closedir($handle);
			}
		}
		
	break;
    case'detail':
		#mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
		// $sekarang=  tanggalsystem($tgl);
		// if($sekarang<$_SESSION['org']['period']['start']){
			// exit("Validation Error : Date out of range");
        // }
		
		$sql = "select * from " . $dbname . ".kebun_realkontanan where "."tanggal='" . $tgl . "'";
        $res = fetchData($sql);
		if (count($res) > 0  and $mode !='edit'){
			exit("Warning : Data tanggal ".$tgl." sudah ada, silahkan lakukan edit !!!");
		}
		
		#=== insert header ===
        $sql = "select * from " . $dbname . ".kebun_realkontanan where "."notransaksi='" . $notransaksi . "'";
        $res = fetchData($sql);
        if (count($res) > 0 and $mode=='edit') {
            
        } else {
			$sql = "select * from " . $dbname . ".kebun_realkontanan where "."notransaksi='" . $notransaksi . "'";
			$res = fetchData($sql);
			if (count($res) > 0) {
				$notrtemp = explode("/",$notransaksi);
				$fWhere = "tanggal='".$notrtemp[0]."' and kodeorg='".$notrtemp[1]."' and tipetransaksi='KTN'";
				$str = "select max(substr(notransaksi,-3)) as notr from " . $dbname . ".kebun_realkontanan where ".$fWhere." limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				
				$trtemp = addZero((intval($bar['notr'])+1),3);
				$notransaksi=str_replace($notrtemp[3],$trtemp,$notransaksi);
			}
			
			$str = "insert into " . $dbname . ".kebun_realkontanan (`notransaksi`, `tipetransaksi`, `tanggal`, `kodeorg`, `updateby`)
			values ('".$notransaksi."','KTN','".$tgl."','" . $kodeorg . "','" . $_SESSION['standard']['userid'] . "')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
		#=== insert header ===
        // $tab=OPEN_BOX();
		#==== Form Judul Detail ====
		# Divisi
		$optDivisi='';
		if($_SESSION['empl']['subbagian']!=''){
			$optDivisi="<option value='".$_SESSION['empl']['subbagian']."'>".$_SESSION['empl']['subbagian']."</option>";
		}
			$optDivisi.="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and tipe='AFDELING' and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
		$resstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $resstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($res = $resstr->fetch()) {
			$optDivisi.="<option value=".$res['kodeorganisasi'].">".$res['kodeorganisasi']." - ".$res['namaorganisasi']."</option>";
		}
		
        $tab.="<table><td valign=top>
			<fieldset style=float:left><legend>Filter</legend>
				<table height=25px>
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td><select style=\"width:150px;\" id=filterdivisi>".$optDivisi."</select></td>
					<td>&nbsp;</td>
					<td><button id=tomboldetail class=mybutton onclick=inputdetail()>" . $_SESSION['lang']['preview'] . "</button></td>
				</table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left>
				<legend>Screen</legend>
				<table height=25px width=100%><td align=center>
					<img id='hidebtn' onclick=\"hideheader()\" title='Full Screen' class='resicon' src='images/full-screen.png' >
					<img id='unhidebtn' onclick=\"unhideheader()\" title='Exit Full Screen' class='resicon' style=display:none src='images/exit_full_screen.png' >
					</td></table>
			</fieldset>
			</td>
			<td valign=top>
			<fieldset style=float:left>
				<legend>Info</legend>
				<table height=25px><td><font color=red><b>* </font>Jumlah Kg harus sama !!!</b></td>
				</table>
			</fieldset>
			</td></table>
			<fieldset>
			<legend>" . $_SESSION['lang']['detail'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 class=sortable >
			<thead><tr class=rowheader>";
			
			$rows="rowspan=2";	
			$tab.="<td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi']."</td>
				<td align=center colspan=3>Kirim ke PKS</td>
				<td align=center colspan=3>Panen</td>
				<td align=center colspan=5>Pengawas</td>
				<td align=center colspan=7>BM TBS</td>
				<td align=center ".$rows.">Total</td>
				<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center colspan=2>Add SPB</td>
				<td align=center>HK</td>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center>Upah</td>
				<td align=center>Mandor</td>
				<td align=center>Kerani</td>
				<td align=center>Mandor 1</td>
				<td align=center>Mandor<br>Traksi</td>
				<td align=center>Total</td>
				<td align=center width=75px>Tanggal</td>
				<td align=center width=65px><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center width=75px>Upah</td>
				<td align=center width=75px>Premi</td>
				<td align=center width=75px>Total</td>
				<td align=center width=15px></td>
				<td align=center width=10px>#</td>
				
			</tr>
			</thead>";
		#==== Form Judul Detail ====
		
		#=== Isi input detail ===
		$tab.="<tbody id=inputdetail>
				<script>inputdetail()</script>
			</tbody></table></fieldset>";
		
		#=== List data tersimpan input detail ===	
        $tab.="<hr><fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
			<div id=loaddatadetail>
				<script>loaddatadetail()</script>
			</div></fieldset>";
        // $tab.=CLOSE_BOX();
		echo $notransaksi."####".$tab;	
		
	break;
	case'inputdetail':
		$where=$tab='';
		if($filterdivisi==''){
			exit("Warning : Divisi tidak boleh kosong !!!");
		}
		$no='1';
		
		$strspb="select * from ".$dbname.".kebun_spb_vw where divisi='".$filterdivisi."' and tanggal='".$tgl."' and kontanan='KONTAN' and posting='1'";
		$resspb = $owlPDO->query($strspb) or die(print " Gagal: " . PDOException::getMessage());
		$resspb->setFetchMode(PDO::FETCH_ASSOC);
		$nospb=$kgwb=array();
		while ($bar = $resspb->fetch()) {
			$nospb[$bar['nospb']]=$bar['nospb'];
			$kgwb[$bar['nospb']]=$bar['kgwb'];
			$tglspb[$bar['nospb']]=$bar['tanggal'];
			@$ttlkgpks+=$bar['kgwb'];
		}
		
		$tab.="<tr class=rowcontent id=row>";
		$tab.="	<td valign=top id=no align=center>".$no."</td>
				<td valign=top id=divisi>".$filterdivisi."</td>
				<td align=right valign=top id=kgpks></td>";
		
		# Add nomor SPB
		$tab.="<td  id=nospb align=center></td>";
		$tab.="<td width=10px valign=top align=center><img class='zImgBtn' src=images/skyblue/plus.png title='Add No SPB' onclick=getnospb('".$filterdivisi."','".tanggalnormal($tgl)."','".$notransaksi."','spb')></td>";
		
		
		# Ambil HK Panen
		$strpnn="select * from ".$dbname.".kebun_prestasi_vs_hk where kodeorg like '".$filterdivisi."%' and tanggal='".$tgl."' and keterangan='KONTAN' and jurnal='1'";
		$respnn = $owlPDO->query($strpnn) or die(print " Gagal: " . PDOException::getMessage());
		$respnn->setFetchMode(PDO::FETCH_ASSOC);
		$hk=$hasilkerjakg=$rupiahhk=0;
		while ($barpnn = $respnn->fetch()) {
			@$hk+=$barpnn['hkpanenperhari'];
			@$hasilkerjakg+=$barpnn['hasilkerjakg'];
			@$rupiahhk+=$barpnn['tupah']+$barpnn['tpremi']-$barpnn['rupiahpenalty'];
		}
		
		$tab.="<td valign=top align=right id=hk>".number_format($hk)."</td>";
		$tab.="<td valign=top align=right id=kghasilkerja>".number_format($hasilkerjakg)."</td>";
		$tab.="<td valign=top align=right id=rphk>".number_format($rupiahhk)."</td>";
		
		# Ambil Pengawas
		$strpws="select * from ".$dbname.".kebun_premikemandoran where tanggalkontanan='".$tgl."' and kontanan='KONTAN' and karyawanid in (select karyawanid from ".$dbname.".datakaryawan where subbagian='".$filterdivisi."')";
		$respws = $owlPDO->query($strpws) or die(print " Gagal: " . PDOException::getMessage());
		$respws->setFetchMode(PDO::FETCH_ASSOC);
		$rpmandor=$rpkeranipnn=$rpmandor1=$rpmandortrk='0';
		while ($barpws = $respws->fetch()) {
			if($barpws['jabatan']=='MANDORPANEN'){
				@$rpmandor+=$barpws['premiinput'];
			}elseif($barpws['jabatan']=='KERANIPANEN'){
				@$rpkeranipnn+=$barpws['premiinput'];
			}elseif($barpws['jabatan']=='MANDOR1'){
				@$rpmandor1+=$barpws['premiinput'];
			}elseif($barpws['jabatan']=='MANDORTRAKSI'){
				@$rpmandortrk+=$barpws['premiinput'];
			}
		}
		$tab.="<td valign=top align=right id=rpmdr>".number_format($rpmandor)."</td>";
		$tab.="<td valign=top align=right id=rpkrn>".number_format($rpkeranipnn)."</td>";
		$tab.="<td valign=top align=right id=rpmdr1>".number_format($rpmandor1)."</td>";
		$tab.="<td valign=top align=right id=rpmdrtrk>".number_format($rpmandortrk)."</td>";
		$tab.="<td valign=top align=right id=ttlrppanen>".number_format($rpmandor+$rpkeranipnn+$rpmandor1+$rpmandortrk)."</td>";
		
		# BMTBS
		$tab.="<td  valign=top id=bmtbs colspan=6 align=center></td>";
		$tab.="<td width=15px valign=top align=center><img class='zImgBtn' src=images/skyblue/plus.png title='Add BM TBS' onclick=getnospb('".$filterdivisi."','".tanggalnormal($tgl)."','".$notransaksi."','bmtbs')></td>";
		
		# total
		$tab.="<td valign=top align=right id=gttl>".number_format(($rpupahbm+$rppremibm)+($rpmandor+$rpkeranipnn+$rpmandor1+$rpmandortrk)+($rupiahhk))."</td>";
		
					
		$tab.="<td align=center valign=top><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
				<!--<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>-->
				</td>
			</tr>";

		$tab.="<tr>
			<td colspan=20>
			<td colspan=2 align=right>
			<img title='Refresh' style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=\"loaddatadetail()\" src='images/refresh2.png'/>
			<img id=done title='" . $_SESSION['lang']['selesai']."' style=vertical-align:center;width:13px;height:13px;cursor:pointer onclick=\"displayList()\" src='images/foldoq.png'/>
        </tr>";
		echo $tab;
	break;
	
	case'insertdetail':
		if(abs($kgpks-$kghasilkerja)>10){
			exit("Error : Total Kg PKS tidak sama dengan Kg Panen !!!");
		}
		if(abs($kgpks-$ttlkgbmtbs)>10){
			exit("Error : Total Kg PKS tidak sama dengan Kg BM TBS !!!");
		}
		
		$str="select * from ".$dbname.".kebun_realkontanandt where divisi='".$divisi."' and notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		if(count($res)>0){
			exit("Error : Data sudah ada !!!");
		}
					
		$str = "insert into " . $dbname . ".kebun_realkontanandt (`notransaksi`, `divisi`, `hk`, `kghasilkerja`,`rphk`, 
				`rpmdr`,`rpkrn`,`rpmdr1`,`rpmdrtrk`, `updateby`)
			values ('".$notransaksi."','".$divisi."','".$hk."','".$kghasilkerja."','".$rphk."','".$rpmdr."','".$rpkrn."','".$rpmdr1."','".$rpmdrtrk."','" . $_SESSION['standard']['userid'] . "')";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	case'getnospb':
		$tab="<table>";
		$tab.="<tr><td>Notransaksi</td><td>:</td><td>".$notransaksi."</td></tr>";
		$tab.="<tr><td>Divisi</td><td>:</td><td>".$divisi."</td></tr>";
		$tab.="<tr><td>Jenis</td><td>:</td><td id=jenis>".$jenis."</td></tr>";
		$tab.="<tr><td>Tanggal</td><td>:</td><td><input type=text class=myinputtext style='width:100px;' id=tglnospb onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10/ value='".tanggalnormal($tgl)."'></td></tr>";
		$tab.="<tr><td></td><td></td><td><button class=mybutton onclick=previewaddnospb('".$divisi."','".$notransaksi."')>" . $_SESSION['lang']['preview'] . "</button></td></tr>";
		$tab.="</table>";
		$tab.="<hr>";
		switch ($jenis) {
			case'spb':
				$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable >
					<thead>
						<tr>
							<td align=center>No</td>
							<td align=center>No SPB</td>
							<td align=center>Jjg</td>
							<td align=center>Kg</td>
							<td align=center>Action</td>
						</tr>
					</thead>";
			break;
			
			case'bmtbs':
				$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable >
					<thead>
						<tr>
							<td align=center>No</td>
							<td align=center>Kg</td>
							<td align=center>Upah</td>
							<td align=center>Premi</td>
							<td align=center>Total</td>
							<td align=center>Action</td>
						</tr>
					</thead>";
			break;
		}		
			$tab.="<tbody id=contaddnospbdetail></tbody></table>";			
		echo $tab;
	break;
	
	case'previewaddnospb':
		$tab="";
		switch ($jenis) {
			case'spb':
				$strspb="select nospb, sum(jjg) as jjg, sum(kgwb) as kgwb, tanggal from ".$dbname.".kebun_spb_vw where divisi='".$divisi."' and tanggal='".$tgl."' and kontanan='KONTAN' and posting='1' group by nospb";
				$resspb = $owlPDO->query($strspb) or die(print " Gagal: " . PDOException::getMessage());
				$resspb->setFetchMode(PDO::FETCH_ASSOC);
				$addno='';
				while ($bar = $resspb->fetch()) {
					$addno++;
					$tab.="<tr class=rowcontent id=rownospb".$addno.">";
					$tab.="<td align=center>".$addno."</td>";
					$tab.="<td id=add_nospb_".$addno.">".$bar['nospb']."</td>";
					$tab.="<td align=right id=add_jjgspb_".$addno.">".number_format($bar['jjg'])."</td>";
					$tab.="<td align=right id=add_kgspb_".$addno.">".number_format($bar['kgwb'])."</td>";
					
					$strspbx="select * from ".$dbname.".kebun_realkontanan_spb where nospb='".$bar['nospb']."' and tanggal='".$bar['tanggal']."'";
					$barx = fetchData($strspbx);
					$hidden = "";
					if(count($barx)>0){
						$hidden = " hidden ";
					}
					
					$tab.="<td align=center><img class='zImgBtn' ".$hidden." id=btnaddnospb src=images/skyblue/plus.png title='Add' onclick=saveaddnospb('".$divisi."','".$notransaksi."','".$addno."','".$jenis."')></td>";
					$tab.="</tr>";
				}
			break;
			case'bmtbs':
				# ambil bm tbs
				$strbm="select * from ".$dbname.".kebun_3premibmtbs where tanggal='".$tgl."' and kontanan='KONTAN' and divisi='".$divisi."' and posting='1'";
				$resbm = $owlPDO->query($strbm) or die(print " Gagal: " . PDOException::getMessage());
				$resbm->setFetchMode(PDO::FETCH_ASSOC);
				$kgbm=$rpupahbm=$rppremibm=0;
				$notrbmtbs=array();
				$addno=1;
				while ($barbm = $resbm->fetch()) {
					@$kgbm+=$barbm['kgwb'];
					@$rpupahbm+=$barbm['rphk'];
					@$rppremibm+=$barbm['rplb'];
					@$notrbmtbs[$barbm['notransaksi']]=$barbm['notransaksi'];
				}
				$tab.="<tr class=rowcontent id=rownobmtbs>";
				$tab.="<td valign=top align=center>1</td>";
				$tab.="<td valign=top align=right hidden id=notrbm>".implode(",",$notrbmtbs)."</td>";
				$tab.="<td valign=top align=right id=kgbm>".number_format($kgbm)."</td>";
				$tab.="<td valign=top align=right id=rpupahbm>".number_format($rpupahbm)."</td>";
				$tab.="<td valign=top align=right id=rppremibm>".number_format($rppremibm)."</td>";
				$tab.="<td valign=top align=right >".number_format($rpupahbm+$rppremibm)."</td>";
				
				$strspbx="select * from ".$dbname.".kebun_realkontanan_bmtbs where tanggal='".$barbm['tanggal']."'";
				$barx = fetchData($strspbx);
				$hidden = "";
				if(count($barx)>0){
					$hidden = " hidden ";
				}
				
				$tab.="<td align=center><img class='zImgBtn' ".$hidden." id=btnaddnospb src=images/skyblue/plus.png title='Add' onclick=saveaddnospb('".$divisi."','".$notransaksi."','".$addno."','".$jenis."')></td>";
				$tab.="</tr>";
					
				$tab.="</tr>";
				
			break;
		}		
		// exit("error asdsadsa");
		echo $tab;
	break;
	case'saveaddnospb':	
		switch ($jenis) {
			case'spb':
				$str = "insert into " . $dbname . ".kebun_realkontanan_spb (`notransaksi`,`divisi`,`tanggal`,`nospb`, `kgwb`,`updateby`)
					values ('".$notransaksi."','".$divisi."','".$tgl."','" . $nospb . "','" . $kgwb . "','" . $_SESSION['standard']['userid'] . "')";
				try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			break;
			case'bmtbs':
				if(($kgbm!='' and $kgbm!='0') or ($rpupahbm!='' and $rpupahbm!='0') or ($rppremibm!='' and $rppremibm!='0')){
					$str = "delete from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi='".$notransaksi."' and divisi='".$divisi."' and tanggal='".$tgl."'";
					try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
				
					$str = "insert into " . $dbname . ".kebun_realkontanan_bmtbs (`notransaksi`,`divisi`, `tanggal`,`notrbm`,`kgbm`, `rpupahbm`, `rppremibm`, `updateby`)
					values ('".$notransaksi."','".$divisi."','".$tgl."','".$notrbm."','".$kgbm."','".$rpupahbm."','".$rppremibm."','" . $_SESSION['standard']['userid'] . "')";
					try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}					
				}
			break;
		}
	
	break;
	
	case'loaddatanospb':
		switch ($jenis) {
			case'spb':
				$tab='';
				$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable >";
				$strspb="select * from ".$dbname.".kebun_realkontanan_spb where notransaksi='".$notransaksi."' and divisi='".$divisi."'";
				$resspb = $owlPDO->query($strspb) or die(print " Gagal: " . PDOException::getMessage());
				$resspb->setFetchMode(PDO::FETCH_ASSOC);
				$addno=$ttl='0';
				while ($bar = $resspb->fetch()) {
					$addno++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$addno."</td>";
					$tab.="<td id=list_nospb_".$addno.">".$bar['nospb']."</td>";
					$tab.="<td id=list_tglspb_".$addno.">".$bar['tanggal']."</td>";
					$tab.="<td align=right id=list_kgspb_".$addno.">".number_format($bar['kgwb'])."</td>";
					$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletespb('" . $bar['notransaksi'] . "','" . $bar['divisi'] . "','','spb','".$bar['nospb']."');\" ></td>";
					
					$tab.="</tr>";
					@$ttl+=$bar['kgwb'];
				}
				$tab.="</table>";
			break;
			case'bmtbs':
				$tab='';
				$tab.="<table border=0 cellpadding=1 cellspacing=1 class=sortable >";
				$strspb="select * from ".$dbname.".kebun_realkontanan_bmtbs where notransaksi='".$notransaksi."' and divisi='".$divisi."'";
				$resspb = $owlPDO->query($strspb) or die(print " Gagal: " . PDOException::getMessage());
				$resspb->setFetchMode(PDO::FETCH_ASSOC);
				$addno=$ttl='0';
				while ($bar = $resspb->fetch()) {
					$addno++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td width=75px id=list_tglbm_".$addno.">".$bar['tanggal']."</td>";
					$tab.="<td width=65px align=right id=list_kbbm_".$addno.">".number_format($bar['kgbm'])."</td>";
					$tab.="<td width=75px align=right id=list_upahbm_".$addno.">".number_format($bar['rpupahbm'])."</td>";
					$tab.="<td width=75px align=right id=list_premibm_".$addno.">".number_format($bar['rppremibm'])."</td>";
					$tab.="<td width=75px align=right id=list_ttlbm_".$addno.">".number_format($bar['rpupahbm']+$bar['rppremibm'])."</td>";
					$tab.="<td width=13px align=center ><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletespb('" . $bar['notransaksi'] . "','" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','bmtbs');\" ></td>";
					$tab.="</tr>";
					$ttl+=($bar['rpupahbm']+$bar['rppremibm']);
					@$ttlkgbmtbs+=$bar['kgbm'];
				}
				$tab.="</table><input style=display:none id=ttlkgbmtbs value=".$ttlkgbmtbs.">";
			break;
		}
		
		echo $tab."####".$ttl;
	break;
	
	case'deletespb':
		switch ($jenis) {
			case'spb':
				$str = "delete from " . $dbname . ".kebun_realkontanan_spb where notransaksi='".$notransaksi."' and nospb='".$nospb."' and divisi='".$divisi."'";
				try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			break;
			case'bmtbs':
				$str = "delete from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi='".$notransaksi."' and divisi='".$divisi."' and tanggal='".$tgl."'"; 
				try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
			break;
		}
	break;
	
	case'loaddatadetail':
	
	$rows="rowspan=2";	
	$tab='';
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='min-width:1000px'>";
	$tab.="<thead><td align=center ".$rows." width=20px>No</td>
				<td align=center ".$rows.">".$_SESSION['lang']['divisi']."</td>
				<td align=center colspan=2>Kirim ke PKS</td>
				<td align=center colspan=3>Panen</td>
				<td align=center colspan=5>Pengawas</td>
				<td align=center colspan=5>BM TBS</td>
				<td align=center ".$rows.">Total</td>
				<td align=center width=30px ".$rows.">" . $_SESSION['lang']['action'] . "</td>
			</tr>
			<tr>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center>Detail SPB</td>
				<td align=center width=45px>HK</td>
				<td align=center><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center>Upah</td>
				<td align=center>Mandor</td>
				<td align=center>Kerani</td>
				<td align=center>Mandor 1</td>
				<td align=center>Mandor<br>Traksi</td>
				<td align=center>Total</td>
				<td align=center width=75px>Tanggal</td>
				<td align=center width=65px><font color=red><b>*</b></font>&nbsp;Kg</td>
				<td align=center width=75px>Upah</td>
				<td align=center width=75px>Premi</td>
				<td align=center width=75px>Total</td>
				
			</tr>
			</thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_realkontanandt a where a.notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=20 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td valign=top align=center>" . $no . "</td>";
				$tab.="<td valign=top align=left>" . $bar['divisi'] . "</td>";
				
				# SPB
				$strjlh = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_realkontanan_spb where notransaksi='".$bar['notransaksi']."' and divisi='".$bar['divisi']."'";
				$strx = "select sum(kgwb) as kgwb, nospb, tanggal from " . $dbname . ".kebun_realkontanan_spb where notransaksi='".$bar['notransaksi']."' and divisi='".$bar['divisi']."' group by nospb";
				$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
				$resx->setFetchMode(PDO::FETCH_ASSOC);
				$kgspb=fetchData($strjlh);
				$tab.="<td valign=top align=center>".@hidezerodecimal($kgspb[0]['kgwb'])."</td>";
				$tab.="<td valign=top align=center>";
				$nox='';
				$tab.="<table cellpadding=0 cellspacing=1 border=0 class=sortable width=100%>";
				while($barx = $resx->fetch()){
					$nox++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td valign=top align=center>".$nox."</td>";
					$tab.="<td valign=top align=left>".$barx['nospb']."</td>";
					$tab.="<td valign=top align=center>".$barx['tanggal']."</td>";
					$tab.="<td valign=top align=right>".$barx['kgwb']."</td>";
					$tab.="</tr>";
				}
				$tab.="</table>";
				$tab.="</td>";
				
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['hk']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['kghasilkerja']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rphk']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rpmdr']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rpkrn']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rpmdr1']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rpmdrtrk']) . "</td>";
				$tab.="<td valign=top align=right>" . @hidezerodecimal($bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']) . "</td>";
				#BM TBS
				$tab.="<td valign=top align=center colspan=5>";
				$tab.="<table cellpadding=0 cellspacing=1 border=0 class=sortable width=100%>";
				$strz = "select * from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi='".$bar['notransaksi']."'  and divisi='".$bar['divisi']."'";
				$resz = $owlPDO->query($strz) or die(print " Gagal: " . PDOException::getMessage());
				$resz->setFetchMode(PDO::FETCH_ASSOC);
				$ttlbm=0;
				while($barz = $resz->fetch()){
					$tab.="<tr class=rowcontent>";
					$tab.="<td valign=top width=75px align=center>".$barz['tanggal']."</td>";
					$tab.="<td valign=top width=65px align=right>".@hidezerodecimal($barz['kgbm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rpupahbm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rppremibm'])."</td>";
					$tab.="<td valign=top width=75px align=right>".@hidezerodecimal($barz['rppremibm']+$barz['rpupahbm'])."</td>";
					$tab.="</tr>";
					$ttlbm+=($barz['rppremibm']+$barz['rpupahbm']);
				}
				$tab.="</table>";
				$tab.="</td>";
				
				$tab.="<td valign=top align=center>".@hidezerodecimal($bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']+$ttlbm+$bar['rphk'])."</td>";
				
				
				
			$tab.="<td align=center valign=top>";
			$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['divisi'] . "');\" ></td>";
			}
			
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
	break;
	
    case'delete':
	try {
		$owlPDO->beginTransaction();
		
			$str = "delete from " . $dbname . ".kebun_realkontanan where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}

        break;

    case'deletedetail':

        $str = "delete from " . $dbname . ".kebun_realkontanandt where notransaksi ='".$notransaksi."' and divisi='" . $divisi . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		$str = "delete from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi ='".$notransaksi."' and divisi='" . $divisi . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		$str = "delete from " . $dbname . ".kebun_realkontanan_spb where notransaksi ='".$notransaksi."' and divisi='" . $divisi . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
    break;

    case'loaddata':

        $where="";
		if($_SESSION['empl']['tipelokasitugas']!='HOLDING'){
			$where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}

        if (($tglmulai != '') and ($tglmulai != '--')) {
            $where.=" and a.tanggal >='" . $tglmulai . "' ";
        }
		if ($notransaksisch != '') {
            $where.=" and a.notransaksi like '%" . $notransaksisch . "%' ";
        }
		if ($postingsrc != '') {
            $where.=" and a.persetujuan ='" . $postingsrc . "' ";
        }
		if ($periodesch != '') {
            $where.=" and a.tanggal like '" . $periodesch . "%' ";
        }
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }

        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $no = 0;
		$tab = "";
        $no = $maxdisplay;
		
		$arrStatus = array('0'=>'Belum diajukan','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak','9'=>'Proses Persetujuan');
		$sql = "select a.notransaksi from " . $dbname . ".kebun_realkontanan a 
		left join " . $dbname . ".kebun_realkontanandt b on a.notransaksi=b.notransaksi 
		where 1=1 " . $where . " group by a.notransaksi";
        $res = fetchData($sql);
		$jlhbrs = count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
            $tab.="<td colspan=15 align=center>".$_SESSION['lang']['errdatanotexist']."</td>";	
			$tab.="</tr>";
		}
        $str = "SELECT a.*, sum(b.kghasilkerja) as kgpnn, sum(b.hk) as hk, sum(b.rphk) as rphk, sum(b.rpmdr) as rpmdr, sum(b.rpkrn) as rpkrn, sum(b.rpmdr1) as rpmdr1, sum(b.rpmdrtrk) as rpmdrtrk  from " . $dbname . ".kebun_realkontanan a 
		left join " . $dbname . ".kebun_realkontanandt b on a.notransaksi=b.notransaksi 
		where 1=1 " . $where . " group by a.notransaksi order by a.notransaksi desc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
            $no+=1;
			$sqlx = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_realkontanan_spb where notransaksi='" . $bar['notransaksi'] . "'";
			$resx = fetchData($sqlx);
			
			$sqlz = "select sum(rpupahbm+rppremibm) as rpbm from " . $dbname . ".kebun_realkontanan_bmtbs where notransaksi='" . $bar['notransaksi'] . "'";
			$resz = fetchData($sqlz);
			
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . "</td>";
            $tab.="<td align=center>" . $bar['tanggal'] . "</td>";
            $tab.="<td align=right>" . number_Format($resx[0]['kgwb']) . "</td>";
            $tab.="<td align=right>" . number_Format($bar['rphk']) . "</td>";
            $tab.="<td align=right>" . number_Format($bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']) . "</td>";
            $tab.="<td align=right>" . number_Format($resz[0]['rpbm']) . "</td>";
            $tab.="<td align=right>" . number_Format($resz[0]['rpbm']+$bar['rpmdrtrk']+$bar['rpmdr1']+$bar['rpkrn']+$bar['rpmdr']+$bar['rphk']) . "</td>";
            $tab.="<td>" . $arrStatus[$bar['persetujuan']] . "</td>";

            $isi='';
            if ($bar['persetujuan'] == 0 or $bar['persetujuan']=='2') {
				$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"edit('".$bar['notransaksi']."','".$bar['kodeorg']."','".tanggalnormal($bar['tanggal'])."','".$no."');\" ></td>";					
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('".$bar['notransaksi']."','".$no."');\" ></td>";
				$isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon  title='Ajukan' 
                    onclick=\"form_ajukan('".$bar['notransaksi']."','".$bar['kodeorg']."','".$no."');\" ></td>";
            }else{
				$isi.="<td></td><td></td><td></td>";
			}
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailData('".$bar['notransaksi']."','".$no."','event','html');\" ></td>";
            $isi.="<td align=center><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='Print Data Detail' onclick=\"detailExcel('".$bar['notransaksi']."','".$no."','event','excel');\" ></td>";

            $tab.=$isi;
            $tab.="</tr>";
        }

        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=15 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

	break;
	
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='KONTAN' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch())
		{
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:150px;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
	
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".kebun_realkontanan set persetujuan='9' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','KONTAN','1','" . $kepada."','0','','','')";
		
		// exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
	case'getnotransaksi':
		$data = $_POST;
		# Data Capture & Reform
		$data['tipetransaksi'] = 'KTN';
		$data['tgl'] = tanggalsystem($data['tgl']);
		
		#=== Generate No Transaksi
		# Get Existing Data
		$fWhere = "tanggal='".$data['tgl']."' and kodeorg='".$data['kodeorg'].
			"' and tipetransaksi='".$data['tipetransaksi']."'";
		$fQuery = selectQuery($dbname,'kebun_realkontanan','notransaksi',$fWhere);
		$tmpNo = fetchData($fQuery);
		
		# Generate No Transaksi
		if(count($tmpNo)==0) {
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/001";
		} else {
			# Get Max No Urut
			$maxNo = 1;
			foreach($tmpNo as $row) {
			$tmpRow = explode('/',$row['notransaksi']);
			$noUrut = (int)$tmpRow[3];
			if($noUrut>$maxNo)
				$maxNo = $noUrut;
			}
			$currNo = addZero($maxNo+1,3);
			echo $data['notransaksi'] = $data['tgl']."/".$data['kodeorg']."/".$data['tipetransaksi']."/".$currNo;
		}
	
    break;


}
?>	
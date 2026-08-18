<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
require_once('lib/zFunction.php');
use Dompdf\Dompdf;

$nosj=checkPostGet('nosj','');
$method=checkPostGet('method','');
$pages = checkPostGet('page','');
$urlefil=checkPostGet('urlefil','0');
##PARAMETER
$unit=checkPostGet('unit','');
$nopo=checkPostGet('nopo','');
$subunit=checkPostGet('subunit','');
$subunitdt=checkPostGet('subunitdt','');
$kegiatan=checkPostGet('kegiatan','');
$notransaksi=checkPostGet('notransaksi','');
$disetujui=checkPostGet('disetujui','');
$penerima=checkPostGet('penerima','');
$tanggal=checkPostGet('tanggal','');
$tanggalselesai=checkPostGet('tanggalselesai','');
$keterangan=checkPostGet('keterangan','');
$kodebarang=checkPostGet('kodebarang','');
##SEARCH
$scnopo=checkPostGet('scnopo','');
$crnopo=checkPostGet('crnopo','');
$scnotransaksi=checkPostGet('scnotransaksi','');
$sctanggal=checkPostGet('sctanggal','');

//Umar
$tab = '';
$tipe = checkPostGet('tipe','');
$tanggalpengajuan=checkPostGet('tanggalpengajuan','');
$maxaproval=checkPostGet('maxaproval','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


$str="select * from ".$dbname.".setup_kegiatan";
// exit("warning:".$str);
$res=fetchdata($str);
foreach($res as $bar){
	$klkegiatan[$bar['kodekegiatan']]=$bar['kelompok'];
}

switch($method){
	case 'cekJumlah':
		$qSaldo = selectQuery($dbname, "log_podt", "*", "nopo='".$nopo."'");
		$resSaldo = fetchData($qSaldo);
		$saldo = $resSaldo[0]['jumlahpesan'];

		$qOrderpesan = selectQuery($dbname, "log_noninventorydt", "*", "nopo='".$nopo."'");
		$resOrderpesan = fetchData($qOrderpesan);
		foreach ($resOrderpesan as $bar) {
			$jumlahpesan += $bar['jumlah'];
		}

		$data = [
			"saldo" => $saldo,
			"jumlahpesan" => $jumlahpesan,
		];

		echo json_encode($data);
	break;

	case 'loaddata':
		$tab="";
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		@$offset=$page*$limit;
		@$no=(($page*$limit));
		$colspan=17;
		
		$arrorgdet = getOrgDetail(2);
		$where = "";
		if($scnotransaksi!=''){
			$where.=" and notransaksi like '%".$scnotransaksi."%'";
		}
		if($crnopo!=''){
			$where.=" and nopo like '%".$crnopo."%'";
		}
		
		if($sctanggal!=''){
			$txt_tgl=tanggalsystemn($sctanggal);
			$where.=" and tanggal='".$txt_tgl."'";
		}
		
		## GET JUMLAH BARIS
		$str="select notransaksi from ".$dbname.".log_noninventory where 
		unit in (".$arrorgdet.") ".$where." order by tanggal desc, pt asc, unit asc, nopo asc";
		$res=fetchdata($str);
		$jlhbrs = count($res);
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{	
			$str="select * from ".$dbname.".log_noninventory where unit in (".$arrorgdet.") ".$where." order by tanggal desc, pt asc, unit asc, nopo asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$strKdBarang="select kodebarang from ".$dbname.".log_noninventorydt where notransaksi='".$val['notransaksi']."'";
				$resKdBarang=fetchdata($strKdBarang);
				$kodebarang = $resKdBarang[0]['kodebarang'];
				// exit("warning".print_r($resKdBarang[0]['kodebarang']));

				$no++;
				$optnamasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val['supplierid']."'");

				if ($val['persetujuan'] == 0) {
					$statusapp = $_SESSION['lang']['belumdiajukan'];
				} else {
					if ($val['persetujuan'] == 1) {
						$table = "approval";
						$whereapp = "status = '1'"; 
						$ket = $_SESSION['lang']['disetujui'];
						$order = 'DESC';
					} else if ($val['persetujuan'] == 9) {
						$table = "approval";
						$whereapp = "status = '0'";
						$ket = $_SESSION['lang']['wait_approval'];
					} else if ($val['persetujuan'] == 2) {
						$table = "approval";
						$whereapp = "status = '2'";
						$ket = $_SESSION['lang']['ditolak'];
					} 

					$str = "SELECT a.karyawanid, b.namakaryawan FROM ".$dbname.".".$table." a
							JOIN ".$dbname.".datakaryawan b ON a.karyawanid = b.karyawanid
							WHERE notransaksi = '".$val['notransaksi']."' AND ".$whereapp."
							ORDER BY level ".$order." LIMIT 1";
					$res = fetchdata($str);
					$statusapp = $ket."<br> (".$res[0]['namakaryawan'].")";
				}
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$no."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$val['notransaksi']."</td>";
				$tab.="<td style='text-align:left;vertical-align:top'>".$val['tipe']." [GRNI".$val['tipe']."]</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$val['pt']."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$val['unit']."</td>";
				$tab.="<td style='text-align:center;min-width:70px;vertical-align:top'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=left valign=top>".$val['nopo']."</td>";
				$tab.="<td align=left valign=top>".$optnamasupplier[$val['supplierid']]."</td>";
				$tab.="<td align=center valign=top>".($val['termin']=='0'?'':$val['termin'])."</td>";
				$tab.="<td align=left valign=top>".getNamaKaryawan($val['createdby'])."</td>";
				$tab.="<td align=left valign=top>".$statusapp."</td>";
				
				if($val['posting']=='0'){
					$tab.="<td align=center valign=top>Not Posted</td>";
					if ($val['persetujuan'] == 1) {
						$tab .= "<td align='center' valign='top'></td>";
						$tab .= "<td align='center' valign='top'></td>";
						if($val['tipe'] == 'SO'){
							$tab.="<td align=center width=25px valign=top><img src=images/skyblue/posting.png class=resicon  title='Posting GR' onclick=\"postinggrx('".$val['notransaksi']."');\"></td>";
						}else{
							$tab.="<td align=center width=25px valign=top><img src=images/skyblue/posting.png class=resicon  title='Posting GR' onclick=\"postinggr('".$val['notransaksi']."','".$val['tipe']."');\"></td>";
						}
					} else if ($val['persetujuan'] == 0 || $val['persetujuan'] == 2){
						$tab.="<td align=center width=25px valign=top><img src=images/application/application_edit.png class=resicon  title='Edit GR' onclick=\"editgr('".$val['notransaksi']."');\"></td>";
						$tab.="<td align=center width=25px valign=top><img src=images/application/application_delete.png class=resicon  title='Delete GR' onclick=\"deletegr('".$val['notransaksi']."');\"></td>";
						$tab.="<td align=center width=25px valign=top><img src=images/skyblue/submit.jpg class=zImgBtn height='30'  title='Ajukan Persetujuan' onclick=\"ajukan('".$val['notransaksi']."','GRNI".$val['tipe']."','".$val['unit']."');\"></td>";
					} else {
						// $tab .= "<td align='center' valign='top'></td>";
						// $tab .= "<td align='center' valign='top'></td>";
						$tab.="<td align=left valign=top colspan=3></td>";
					}
				}else{
					$tab.="<td align=center width=25px valign=top>".getNamaKaryawan($val['postedby'])."</td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center width=25px valign=top><img src='images/skyblue/posted.png' class='zImgOffBtn' title='Posted'></td>";
				}
				$tab.="<td align=center width=25px valign=top>
					<img src=images/zoom.png class=resicon title='Preview' onclick=\"previewgr(event,'".$val['notransaksi']."','".$kodebarang."');\">
				</td>";	
				
				if($val['tipe'] == 'SO'){
					$tab.="<td align=center width=25px valign=top><img src='images/skyblue/pdf.jpg' class='resicon' title='Print PDF BAPP'' onclick=\"previewpdfgrba(event,'".$val['notransaksi']."');\"></td>";
				}else{
					$tab.="<td align=center width=25px valign=top>
					<img src=images/pdf.jpg class=resicon title='Print PDF' onclick=\"previewpdfgr(event,'".$val['notransaksi']."');\">
					</td>";
				}
				$tab.="</tr>";
			}
		}
		
		## PAGING
		$foot.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		
		
		echo $tab."####".$foot;
	break;
	
	case'getpenerima':
		## GET KARYAWAN
		$optkaryawan="";
		$strRegional = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='".$unit."'");
		// $str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' order by namakaryawan";
		$str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$strRegional[$unit]."') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d"). "') or karyawanid='".$_SESSION['standard']['userid']."' order by namakaryawan"; //exit("error".$str);
		$res=fetchdata($str);
		if($notransaksi!=''){
			foreach($res as $val){
				if($val['karyawanid']==$penerima){
					$optkaryawan.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." - ".$val['nik']."</option>";
				}
				$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
			}
		}else{
			foreach($res as $val){
				if($val['karyawanid']==$_SESSION['standard']['userid']){
					$optkaryawan.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." - ".$val['nik']."</option>";
				}
				$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
			}
		}	
		
		
		## GET KARYAWAN
		$optkaryawandisetujui="";
		$strRegional = makeOption($dbname, 'bgt_regional_assignment', 'kodeunit,regional', "kodeunit='".$unit."'");
		// $str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas='".$unit."' order by namakaryawan";
		if ($strRegional[$unit] != '') {
            $str = "select karyawanid,nik,namakaryawan from " . $dbname . ".datakaryawan where lokasitugas in (select kodeunit from " . $dbname . ".bgt_regional_assignment = " . $strRegional[$unit] . ") and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') order by namakaryawan";
        } else {
            $str = "select karyawanid,nik,namakaryawan from " . $dbname . ".datakaryawan where lokasitugas in (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') order by namakaryawan";
        }
		// $str="select karyawanid,nik,namakaryawan from ".$dbname.".datakaryawan where lokasitugas in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional = '".$strRegional[$unit]."') and (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d"). "') order by namakaryawan";
		$res=fetchdata($str);
		if($notransaksi!=''){
			foreach($res as $val){
				if($val['karyawanid']==$penerima){
					$optkaryawandisetujui.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." - ".$val['nik']."</option>";
				}
				$optkaryawandisetujui.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
			}
		}else{
			foreach($res as $val){
				if($val['karyawanid']==$_SESSION['standard']['userid']){
					$optkaryawandisetujui.="<option value='".$val['karyawanid']."' selected>".$val['namakaryawan']." - ".$val['nik']."</option>";
				}
				$optkaryawandisetujui.="<option value='".$val['karyawanid']."'>".$val['namakaryawan']." - ".$val['nik']."</option>";
			}
		}	
		
		echo $optkaryawan."####".$optkaryawandisetujui;
	break;
	
	case'carinopo':
		$tab="";
		
		if(strlen($scnopo)<3){
			exit("warning : minimal pencarian 3 karakter");
		}
		
		// $str="select nopo,tanggal,purchaser,tglrelease from ".$dbname.".log_poht where closed=0 and nopo like '%".$scnopo."%' and stat_release='1' and kodeunit='".$unit."' and tipepo in ('NO','SO','CO') order by tanggal desc, nopo desc";
		if ($nosj != '') {
			$str_Q = "select a.nopo,a.tanggal,a.purchaser,a.tglrelease,a.nilaipo,a.syaratbayar,a.idFranco,a.kodeunit,a.kodesupplier from " . $dbname . ".log_poht a
			left join " . $dbname . ".log_suratjalan_vw b on a.nopo=b.nopo where a.closed=0 and a.nopo like '%" . $scnopo . "%'
			and a.stat_release='1' and b.franco LIKE '" . $unit . "%' and a.tipepo in ('NO','SO','CO') and b.nosj='" . $nosj . "' order by a.tanggal desc, a.nopo desc";
		}else{
			$str_Q = "select nopo,tanggal,purchaser,tglrelease,nilaipo,syaratbayar,idFranco,kodeunit,kodesupplier from " . $dbname . ".log_poht where closed=0 and
			nopo like '%" . $scnopo . "%' and stat_release='1' and idfranco IN (SELECT id_franco FROM " . $dbname . ".setup_franco WHERE kodeunit='" . $unit . "') and tipepo in ('NO','SO','CO')
			and nopo not in (select nopo from " . $dbname . ".log_transit) order by tanggal desc, nopo desc";
		}
		/*
		$str="select nopo,tanggal,purchaser,tglrelease from ".$dbname.".log_poht where closed=0 and nopo like '%".$scnopo."%' and stat_release='1' and kodeunit='".$unit."' and tipepo in ('NO','SO','CO') and nopo not in (select noso from ".$dbname.".log_sorefrensi) order by tanggal desc, nopo desc";
		*/
		$res=fetchdata($str_Q);
		
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nopo']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['purchaser']."</td>
				<td align=center>".$_SESSION['lang']['supplier']."</td>
				<td align=center>".$_SESSION['lang']['tanggalRelease']."</td>
				<td align=center>".$_SESSION['lang']['jumlah']." item</td>
				<td align=center>".$_SESSION['lang']['total']." ".$_SESSION['lang']['harga']." item</td>
			</tr>
			</thead>
			<tbody>";
		
		if(count($res) > 0){
			foreach($res as $val){


			     // Ambil data dari log_sorefrensi
				 $strso = "SELECT nopo, nopp, kodebarang FROM " . $dbname . ".log_sorefrensi WHERE noso='" . $val['nopo'] . "' GROUP BY nopo, nopp, kodebarang";
				 $resso = fetchdata($strso);
				 $sopo = 0;
				 $noposo = [];
				 $kodebarangvr = [];
				 $noppvr = [];
 
				 foreach ($resso as $valso) {
					 $sopo += 1;
					 $noposo[$valso['nopo']] = $valso['nopo'];
					 $kodebarangvr[$valso['kodebarang']] = $valso['kodebarang'];
					 $noppvr[$valso['nopp']] = $valso['nopp'];
				 }
 
				 // Ambil data dari log_transaksi_vw dengan LEFT JOIN untuk mengecek apakah ada data yang sudah terposting
				 $strso2 = "SELECT t.notransaksi, t.nopo, t.nopp, t.kodebarang FROM " . $dbname . ".log_transaksi_vw AS t LEFT JOIN " . $dbname . ".log_sorefrensi AS s ON t.nopo = s.nopo AND t.nopp = s.nopp AND t.kodebarang = s.kodebarang WHERE t.nopo IN ('" . implode("','", $noposo) . "') AND t.nopp IN ('" . implode("','", $noppvr) . "') AND t.kodebarang IN ('" . implode("','", $kodebarangvr) . "') AND t.statusjurnal='1' AND t.tipetransaksi=1 GROUP BY t.nopo, t.nopp, t.kodebarang";
				 $resso2 = fetchdata($strso2);
				 $gdgno = 0;
 
				 if ($sopo > 0) {
					 foreach ($resso2 as $valso2) {
						 $noposo_2[$valso2['nopo']] = $valso2['nopo'];
						 $kodebarangvr_2[$valso2['kodebarang']] = $valso2['kodebarang'];
						 $nopp_2[$valso2['nopp']] = $valso2['nopp'];
 
						 $nogdg = $valso2['notransaksi'];
 
						 if ($nogdg != '') {
							 $gdgno += 1;
						 }
					 }
				 }
 
				 // Ambil data dari log_sorefrensi yang belum terposting
				 $ketnopo = "\n";
				 $strso3 = "SELECT s.nopo, s.nopp, s.kodebarang FROM " . $dbname . ".log_sorefrensi AS s LEFT JOIN " . $dbname . ".log_transaksi_vw AS t ON s.nopo = t.nopo AND s.nopp = t.nopp AND s.kodebarang = t.kodebarang WHERE s.noso='" . $val['nopo'] . "' AND t.notransaksi IS NULL";
				 $resso3 = fetchdata($strso3);
				 $gdgno3 = 0;
 
				 foreach ($resso3 as $valso3) {
					 $ketnopo .= "- NO.PO :" . $valso3['nopo'] . ", NO.PR :" . $valso3['nopp'] . ", KODEBARANG :" . $valso3['kodebarang'] . "\n";
				 }
				 $sel = $sopo - $gdgno;



				$counthasil = 0;
				$jlhitem=0;
				$strx="select jumlahpesan, kodebarang from ".$dbname.".log_podt where nopo='".$val['nopo']."'";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					$jlhitem++;
					$strxx="select count(nopo) as jumlah from ".$dbname.".log_potermin where nopo='".$val['nopo']."' and ba='0'";
					$resxx=fetchdata($strxx);
					$jlhtermin=($resxx[0]['jumlah']==''?0:$resxx[0]['jumlah']);
					if($jlhtermin > 0){
						$counthasil++;
					}
					
					$strxx="select sum(jumlah) as jumlah from ".$dbname.".log_noninventorydt where nopo='".$val['nopo']."' and kodebarang='".$valx['kodebarang']."'";
					$resxx=fetchdata($strxx);
					$jlhterima = ($resxx[0]['jumlah']==''?0:$resxx[0]['jumlah']);
					$hasil = $valx['jumlahpesan'] - $jlhterima;
					if($hasil > 0){
						$counthasil++;
					}
				}
				if($counthasil > 0){

					if ($sel != 0) {
                        $tab .= "<tr class=rowcontent  title='Data tidak dapat ditarik dikarenakan PO Atas SO ini Belum Di terimakan dan di posting semua pada gudang " . $ketnopo . " ' >";
                        $bgcolor = "bgcolor=yellow";
                    }else{
						$tab.="<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"pickuppo('".$val['nopo']."')\">";
					}

					$tab.="<td align=left>".$val['nopo']."</td>";
					$tab.="<td align=center style='min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
					$tab.="<td>".getNamaKaryawan($val['purchaser'])."</td>";
					$tab.="<td>".getNamaSupplier($val['kodesupplier'])."</td>";
					$tab.="<td align=center style='min-width:70px'>".tanggalnormal($val['tglrelease'])."</td>";
					$tab.="<td align=right>".$jlhitem."</td>";
					$tab.="<td align=right>".number_format($val['nilaipo'],2,'.',',')."</td>";
					$tab.="</tr>";
				}
			}	
		}else{
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=5 align=center>".$_SESSION['lang']['datanotfound']."</td>";
			$tab.="</tr>";
		}
			
		$tab.="</tbody>
		</table>";
		
		echo $tab;
	break;
	
	case'pickuppo':
		$tab="";
		
		if($notransaksi==''){
			##CEK DARI TABLE LOG PO TERMIN
			$str="select count(nopo) as termin from ".$dbname.".log_potermin where nopo='".$nopo."' and ba='0'";
			$res=fetchdata($str);
			$termin=$res[0]['termin'];
		}else{
			##CEK DARI TABLE NON INVENTORY HT
			$str="select termin from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			$termin=$res[0]['termin'];
		}
		
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center'>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>".$_SESSION['lang']['sudahditerima']."</td>
				<td>".$_SESSION['lang']['kuantitaspo']."</td>
				<td width=75px>".$_SESSION['lang']['diterima']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		$sql="select * from ".$dbname.".log_somaterial where nopo='".$nopo."'";$hsl=fetchdata($sql);
		$str="select * from ".$dbname.".log_podt where nopo='".$nopo."' order by kodebarang asc";
		// exit("error".$str);
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no++;
			
			$optnamabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			$opttipepo=makeOption($dbname,'log_poht','nopo,tipepo',"nopo='".$nopo."'");
			
			##SUDAH DITERIMAKAN
			$sudahditerima=0;
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_noninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi!='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlah'];			
			}
			
			##CEK DARI TABLE NON INVENTORY
			$gsubunit="";
			$gsubunitdt="";
			$gkegiatan="";
			$jumlahpesan=0;
			$strx="select * from ".$dbname.".log_noninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$jumlahpesan=$valx['jumlah'];
				$gsubunit=$valx['subunit'];
				$gsubunitdt=$valx['subunitdt'];
				$gkegiatan=$valx['kodekegiatan'];
			}
			
			$sudahditerima=($sudahditerima==''?0:$sudahditerima);
			
			if($termin > 0){
				$sisa = $val['jumlahpesan'];
			}else{
				if($jumlahpesan==0){
					$sisa = $val['jumlahpesan'] - $sudahditerima;
				}else{
					# Trace kesalahan
					// exit('warning'.$val['jumlahpesan'].'xxx'.$sudahditerima);
					# Script sebelumnya (pengambilan jumlah pesan yang salah)
					// $sisa = $jumlahpesan - $sudahditerima;
					# noted seharusnya ambil jumlah pesan ambil dari podt bukan dari noninventory
					# karena noninventory adalah hasil penerimaannya
					$sisa = $val['jumlahpesan'] - $sudahditerima;
				}
			}
			
			$disabled='';
			if($sisa <= 0){
				$disabled='disabled';	
			}
			if(count($hsl)>0){
				$disabled='disabled';
			}
			#= sugest ke qty 1 jika ada termin dan qty=0, contoh kasus, PO jasa 1 LOT, ada 3 termin (3 penerimaan), maka pada saat penerimaan ke-2 dan 3 tidak bernilai 0, melainkan sugest ke angka 1
			if($sisa <= 0 and $termin > 0){
				$sisa='1';	
			}
			
			$optsubunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			
			if($opttipepo[$nopo]=='CO'){
				if($gsubunit=='PROJECT'){
					$optsubunit.="<option value='PROJECT' selected>PROJECT</option>";
				}else{
					$optsubunit.="<option value='PROJECT'>PROJECT</option>";				
				}
			}else{
				if($gsubunit==$unit){
					## GET KANTOR
					$optsubunit.="<option value='".$unit."' selected>KANTOR/OFFICE</option>";
				}else{
					## GET KANTOR
					$optsubunit.="<option value='".$unit."'>KANTOR/OFFICE</option>";
				}
				
				##GET SUBUNIT
				$strx="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe not like '%gudang%'";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					if($gsubunit==$valx['kodeorganisasi']){
						$optsubunit.="<option value='".$valx['kodeorganisasi']."' selected>".$valx['kodeorganisasi']." - ".$valx['namaorganisasi']."</option>";					
					}else{
						$optsubunit.="<option value='".$valx['kodeorganisasi']."'>".$valx['kodeorganisasi']." - ".$valx['namaorganisasi']."</option>";					
					}
				}
				
				if($gsubunit=='PROJECT'){
					$optsubunit.="<option value='PROJECT' selected>PROJECT</option>";
				}else{
					$optsubunit.="<option value='PROJECT'>PROJECT</option>";				
				}
			}
			
			// $optsubunit.="<option value=''>======================</option>";
			
			// if($gsubunit=='KONTRAKTOR'){
				// $optsubunit.="<option value='KONTRAKTOR' selected>KONTRAKTOR</option>";
			// }else{
				// $optsubunit.="<option value='KONTRAKTOR'>KONTRAKTOR</option>";
			// }
			// if($gsubunit=='KUD'){
				// $optsubunit.="<option value='KUD' selected>KUD TBS</option>";
			// }else{
				// $optsubunit.="<option value='KUD'>KUD TBS</option>";
			// }
			// if($gsubunit=='SUPPLIER'){
				// $optsubunit.="<option value='SUPPLIER' selected>SUPPLIER TBS</option>";
			// }else{
				// $optsubunit.="<option value='SUPPLIER'>SUPPLIER TBS</option>";
			// }
			// if($gsubunit=='PETANI'){
				// $optsubunit.="<option value='PETANI' selected>PETANI</option>";
			// }else{
				// $optsubunit.="<option value='PETANI'>PETANI</option>";
			// }
			
			$optsubunitdtx=getsubunitdt($gsubunit,$gsubunitdt,'1');
			$epxoptsubunitdt=explode('####',$optsubunitdtx);
			$optsubunitdt=$epxoptsubunitdt[0];
			$tipesubunitdt=$epxoptsubunitdt[1];
			$stylex='';
			if($tipesubunitdt=='1'){
				$stylex='disabled';
			}
			$optkegiatan=getkegiatan($gsubunit,$gsubunitdt,$gkegiatan,'1');
			
			$tab.="<tr class=rowcontent id='tr_".$no."'>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id='kodebarang_".$no."'>".$val['kodebarang']."</td>";
			$tab.="<td align=left id='namabarang_".$no."'>".$optnamabarang[$val['kodebarang']]."</td>";
			$tab.="<td id='satuan_".$no."'>".$val['satuan']."</td>";
			$tab.="<td id='nopp_".$no."'>".$val['nopp']."</td>";
			$tab.="<td id='sudahditerima_".$no."' align=right>".hidezerodecimal($sudahditerima,3)."</td>";
			$tab.="<td id='jumlahpesan_".$no."' align=right>".hidezerodecimal($val['jumlahpesan'],3)."</td>";
			$tab.="<td align=center>
				<input type=text ".$disabled." class=myinputtextnumber id='diterima_".$no."' onkeyup=\"cekJumlah('".$val['nopp']."', '".$no."');\" onkeypress=\"return angka_doang(event);\" value='".$sisa."' style=width:70px maxlength=12>
			</td>";
			$tab.="<td align=center>
				<select class=select2 id='subunit_".$no."' style='width:100px;' onchange=\"getsubunitdt('".$no."')\">".$optsubunit."</select>
			</td>";
			$tab.="<td align=center>
				<select class=select2 id='subunitdt_".$no."' style='width:150px;' ".$stylex." onchange=\"getkegiatan('".$no."')\">".$optsubunitdt."</select>
			</td>";
			$tab.="<td align=center><select class=select2 id='kegiatan_".$no."' style='width:200px;' onchange=''>".$optkegiatan."</select></td>";
			$tab.="<td align=center><button class=mybutton onclick=showupload(event,'".$val['kodebarang']."','".$val['nopp']."')>Upload Files</button></td>";
			$tab.="</tr>";
		}
		$tab.="<tr>
			<td colspan='11' align=center>
				<button class=mybutton onclick=\"simpan('".$no."')\">".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=\"clearform()\">".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>";
		
		$tab.="</tbody>
		</table>";
		
		##CEK TERMIN
		$opttermin="";
		if($notransaksi==''){
			$str="select * from ".$dbname.".log_potermin where nopo='".$nopo."' and ba='0'";
			$res=fetchdata($str);
			if(count($res) > 0){
				$opttermin="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				foreach($res as $val){
					$opttermin.="<option value='".$val['termin']."'>".$val['termin']."</option>";
				}
			}
		}else{
			$str="select termin from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			if($res[0]['termin'] != 0){
				$opttermin="<option value='".$res[0]['termin']."'>".$res[0]['termin']."</option>";				
			}
		}
			
		echo $tab."#####".$opttermin;
	break;
	
	case'getsubunitdt':
		echo getsubunitdt($subunit,'','1');
	break;
	
	case'getkegiatan':
		echo getkegiatan($subunit,$subunitdt,'','1');
	break;
	
	case'simpan':
		try {
			$owlPDO->beginTransaction();
			
			$showtermin=checkPostGet('showtermin','');
			$termin=checkPostGet('termin','');
			$tglskrg=date("Y-m-d H:i:s");
			

			#====cek periode
			$tgl = tanggalsystemn($tanggal);
			$periode=substr($tgl,0,7);
			$sPeriode="select * from ".$dbname.".setup_periodeakuntansi 
			           where kodeorg='".$unit."' and tutupbuku=0 order by periode desc";
			$rPeriode=fetchdata($sPeriode);
			$tglakutansi=$rPeriode[0]['tanggalmulai'];
			// echo $tglakutansi._.$tgl;
			// exit('error');
			if($tglakutansi>$tgl){
				throw new PDOException("Periode ".$rPeriode[0]['periode']." untuk unit ".$unit." sudah tutup buku, silahkan ganti tanggal penerimaan");
			}
			
			if($showtermin=='1'){
				if($termin==''){
					// throw new PDOException("Termin untuk BA harus dipilih");
				}
			}
			
			if($notransaksi==''){
				##NO TRANSAKSI
				$str="select notransaksi from ".$dbname.".log_noninventory where unit='".$unit."' and tanggal like '".$periode."%' order by notransaksi desc limit 1";
				$res=fetchdata($str);
				$tempnotransaksi=$res[0]['notransaksi'];
				if($tempnotransaksi==''){
					$notransaksi=substr($tanggal,6,4)."".substr($tanggal,3,2)."00001-GRNI-".$unit;
				}else{
					$notransaksi=substr($tanggal,6,4)."".substr($tanggal,3,2)."".addZero((substr($tempnotransaksi,6,7)+1),5)."-GRNI-".$unit;
				}
				
				// if($_SESSION['standard']['username']=='tim.owl3'){
				// exit("Error:".$str._.$notransaksi);
				// }

				
				##GET TIPE PO
				$str="select kodesupplier,kodeorg,tipepo from ".$dbname.".log_poht where nopo='".$nopo."'";
				$res=fetchdata($str);
				$pt=$res[0]['kodeorg'];
				$tipepo=$res[0]['tipepo'];
				$supplierid=$res[0]['kodesupplier'];
				
				##INSERT HEADER
				$str="insert into ".$dbname.".log_noninventory (notransaksi,tipe,tanggal,pt,unit,nopo,nosj,keterangan,penerima,supplierid,termin,updateby,updatetime,createdby,createtime,disetujui) values ('".$notransaksi."','".$tipepo."','".tanggalsystem($tanggal)."','".$pt."','".$unit."','".$nopo."','','','".$penerima."','".$supplierid."','".$termin."','".$_SESSION['standard']['userid']."','".$tglskrg."','".$_SESSION['standard']['userid']."','".$tglskrg."','".$disetujui."')";
				$owlPDO->exec($str);
			}else{
				$str="update ".$dbname.".log_noninventory set penerima='".$penerima."',disetujui='".$disetujui."', termin='".$termin."', updateby='".$_SESSION['standard']['userid']."', updatetime='".$tglskrg."' where notransaksi='".$notransaksi."'";
				$owlPDO->exec($str);
			}
			
			##DELETE DETAIL
			$str="delete from ".$dbname.".log_noninventorydt where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			foreach($_POST['kodebarang'] as $key=>$kodebarang){
				$satuan=$_POST['satuan'][$key];
				$nopp=$_POST['nopp'][$key];
				$sudahditerima=str_replace(",","",$_POST['sudahditerima'][$key]);
				$jumlahpo=str_replace(",","",$_POST['jumlahpo'][$key]);
				$diterima=str_replace(",","",$_POST['diterima'][$key]);
				$subunit=$_POST['subunit'][$key];
				$subunitdt=$_POST['subunitdt'][$key];
				$kegiatan=$_POST['kegiatan'][$key];
				$sttenbl=$_POST['sttenbl'][$key];
				
				if($tipepo=='SO'){
					if($diterima > $jumlahpo){
						throw new PDOException("Jumlah diterima melebihi kuantitas PO/SO");
					}
				}else{
					if(($sudahditerima+$diterima) > $jumlahpo){
						throw new PDOException("Jumlah diterima melebihi kuantitas PO/SO");
					}
				}
				
				if($subunit=='' && $diterima > 0){
					throw new PDOException("Sub Unit harus dipilih");
				}
				
				if($sttenbl=='0'){
					if($subunitdt=='' && $diterima > 0){
						throw new PDOException("Blok / Mesin / Kend / AB harus dipilih");
					}
				}
				
				// if($kegiatan=='' && $diterima > 0){
				// 	throw new PDOException("Kegiatan harus dipilih harus dipilih");
				// }

				if($kegiatan=='' && $diterima > 0){
					throw new PDOException("Kegiatan harus dipilih harus dipilih");
				}

				
				##GET HARGA SATUAN
				$str="select hargasatuan from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$kodebarang."'";
				$res=fetchdata($str);
				$hargasatuan=$res[0]['hargasatuan'];
				
				// if($showtermin=='1'){
				// 	$arrPo=array();
				// 	$arrNilPro=array();
				// 	$totRup=0;
				// 	#proporsi nilai rupiahnya berdasarkan nilai hargasatuan
				// 	$strpro="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
				// 	$respro=fetchdata($strpro);
				// 	foreach ($respro as $key => $val) {
				// 		$arrPo[$val['nopp']][$val['kodebarang']]=($val['hargasatuan']*$val['jumlahpesan']);
				// 		$totRup+=($val['hargasatuan']*$val['jumlahpesan']);
				// 	}
				// 	if(count($arrPo)>0){
				// 		foreach ($arrPo as $dtPp => $arrval) {
				// 			 foreach ($arrval as $kdbrg => $nil) {
				// 				$arrNilPro[$dtPp][$kdbrg]=$nil/$totRup;
				// 			 }
				// 		}
				// 	}
				// 	$str="select rupiah,persen from ".$dbname.".log_potermin where nopo='".$nopo."' and termin='".$termin."'";
				// 	$res=fetchdata($str);
				// 	// $rptermin=$res[0]['rupiah']*$arrNilPro[$nopp][$kodebarang];
				// 	$rptermin=$res[0]['rupiah']*$arrNilPro[$nopp][$kodebarang]; // dibagi qty
				// 	$persentermin=$res[0]['persen'];
				// 	// $hargasatuan= round($hargasatuan *($persentermin/100),0);
				// 	$hargasatuan= $rptermin;
				// }

				
				if($diterima!='0'){
					// $str="insert into ".$dbname.".log_noninventorydt (notransaksi,nopp,kodebarang,satuan,jumlah,hargasatuan,subunit,subunitdt,kodekegiatan,nopo) values ('".$notransaksi."','".$nopp."','".$kodebarang."','".$satuan."','".$diterima."','".$hargasatuan."','".$subunit."','".$subunitdt."','".$kegiatan."','".$nopo."')";
					$str="insert into ".$dbname.".log_noninventorydt (notransaksi,nopp,kodebarang,satuan,jumlah,hargasatuan,subunit,subunitdt,kodekegiatan,nopo) values ('".$notransaksi."','".$nopp."','".$kodebarang."','".$satuan."','".$diterima."','".$hargasatuan."','".$subunit."','".$subunitdt."','".$kegiatan."','".$nopo."')";
					$owlPDO->exec($str);
				}
			}
			
			## UPDATE TERMIN BA
			$str="update ".$dbname.".log_potermin set ba='1' where nopo='".$nopo."' and termin='".$termin."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'editgr':
		$str="select * from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$desetujui=$res[0]['desetujui'];
		// exit("Warning".print_r($unit."####".$penerima));
		echo $unit."####".$penerima."####".$tanggal."####".$nopo."####".$desetujui;
	break;
	
	case'deletegr':
		try {
			$owlPDO->beginTransaction();
			
			$str="select nopo, termin from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			$nopo=$res[0]['nopo'];
			$termin=$res[0]['termin'];
			
			$str="delete from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".log_potermin set ba='0' where nopo='".$nopo."' and termin='".$termin."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'postinggr':
	
		#= penambahan setup posting
		
		$jab = getPostingJabatan('gudangnoninventory');	
		// if(!in_array($_SESSION['empl']['jabatan'],$jab)){
			// exit("warningsistem:Jabatan anda tidak diperbolehkan untuk posting  noninventory, hubungi IT untuk mendaftarkan disetup posting");
		// }
	
		$str="select * from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
		// exit("warning:".$str);
		$res=fetchdata($str);
		$unit=$res[0]['unit'];
		$pt=$res[0]['pt'];
		$tanggal=$res[0]['tanggal'];
		$tanggal=$res[0]['tanggal'];
		$tipe=$res[0]['tipe'];
		$supplierid=$res[0]['supplierid'];
		$kodejurnal="NOINV";
		
		$jurnalfound='';
		// cek apakah sudah ada jurnal?
		$str = "select nojurnal from ".$dbname.".keu_jurnalht where noreferensi = '".$notransaksi."' ";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$jurnalfound.=$val['nojurnal'].',';
		}
		if($jurnalfound!=''){
			exit ("error : Sudah ada jurnal: ".$jurnalfound." silakan refresh.");			
		}
		
		## Prepare jurnal
		## Ambil noakun supplier
		
		$kodekl = "SUPPLIER";
		$noakunkr = "2110101";
		if($tipe=='SO'){
			$kodekl = "JASA";
			$noakunkr = "2110301";
		}

		// GRIR 2021
		$noakungrir='2110501';
		$noakunkr=$noakungrir;
		$str = "select kodeorganisasi from ".$dbname.".organisasi where induk in (select induk from ".$dbname.".organisasi where kodeorganisasi = '".$unit."' ) and tipe = 'KANWIL'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$ronya = $val['kodeorganisasi'];
		}

		$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$ronya."' and jenis = 'intra'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$akuncacoro=$val['akunpiutang'];
		}
		$str = "select akunpiutang,akunhutang,jenis from ".$dbname.".keu_5caco where kodeorg='".$unit."' and jenis = 'intra'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$akuncacoes=$val['akunhutang'];
		}

		// cek apakah RO sudah closing
		$periode=substr($tanggal,0,7);
        $str = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".$periode."' and kodeorg='".$ronya."'";
		$res=fetchdata($str);
        $close = $res[0]['tutupbuku'];
        if ($close == '1'){
			exit ("error : ".$ronya." sudah tutup buku");
        }

		// exit("error: ".$ronya." ".$akuncacoro."/".$akuncacoes." ".$close);
		#= cek apakah flag gr/ir aktif
		// $str=" select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='FL' and kodeparameter='FLGRIR'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// 	$flaggrir=$bar['nilai'];
		// end GRIR 2021
					

		## Prepare jurnal
		## Ambil noakun supplier
		## jika flag gr/ir aktif ambil akun gr/ir nya
		// $noakunkr = '';
		// $kodekl = "SUPPLIER";
		// $str = "select noakun,noakungrir from ".$dbname.".log_5supkelompok where tipe='".$kodekl."' and supplierid='".$supplierid."'";
		// // exit("Error:$str");
		// $res=fetchdata($str);
		// if($flaggrir==1){
			// $noakunkr = $res[0]['noakungrir'];
		// } else {
			// $noakunkr = $res[0]['noakun'];
		// }
		
		if($noakunkr==''){
			exit("Warning:No. Akun masih kosong kredit masih kosong, silahkan cek di setup kelompok supplier, jika memakai konsep GR/IR cek juga akun GR/IR disetup tersebut");
		}

		
		// $str = "select noakun from ".$dbname.".log_5klsupplier where tipe like '".$kodekl."%' and noakun!='' limit 1";
		// $res=fetchdata($str);
		// $noakunkr = $res[0]['noakun'];
		
		
		$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$pt."' and kodekelompok='".$kodejurnal."' 
			and kodeunit='".$unit."' and periode='".substr($tanggal,0,7)."'");
		$tmpKonter = fetchData($queryJ);
		$konter = $tmpKonter[0]['nokounter'];
		// $konter = addZero($tmpKonter[0]['nokounter']+1,3);
		// GRIR 2021
		$queryJro = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodeorg='".$pt."' and kodekelompok='".$kodejurnal."' 
			and kodeunit='".$ronya."' and periode='".substr($tanggal,0,7)."'");
		$tmpKonterro = fetchData($queryJro);
		$konterro = $tmpKonterro[0]['nokounter'];

		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
	
		try {
			$owlPDO->beginTransaction();
			
			$tglskrg=date("Y-m-d H:i:s");
			
			##MAINKAN JURNAL NYA
			#= oke boi
			// Default Segment
			$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
			$notemp=0;
			$notempro=0;
			$str="select * from ".$dbname.".log_noninventorydt_vw where notransaksi='".$notransaksi."'";
			// exit("warning:".$str);
			$res=fetchdata($str);
			foreach($res as $bar){
				

				$kodeblok='';
				$kodevhc='';
				$kodeasset='';

				#= cek data subunit
				if($klkegiatan[$bar['kodekegiatan']]=='TM' || $klkegiatan[$bar['kodekegiatan']]=='TBM' || 
					$klkegiatan[$bar['kodekegiatan']]=='PNN' || $klkegiatan[$bar['kodekegiatan']]=='BBT' ||
					$klkegiatan[$bar['kodekegiatan']]=='TB' || $klkegiatan[$bar['kodekegiatan']]=='LC'){
					$kodeblok=$bar['subunitdt'];
				}

				if($klkegiatan[$bar['kodekegiatan']]=='TRK'){
					$kodevhc=$bar['subunitdt'];
				}

				if($klkegiatan[$bar['kodekegiatan']]=='KNT' and substr($bar['subunitdt'],0,3)=='AK-'){
				// if(substr($bar['subunitdt'],0,3)=='AK-'){
					$kodeasset=$bar['subunitdt'];
				}


				$data=array();
				$dataro=array();
				$noUrut=1;
				$notemp++;
				$notempro++;
				// @$no+=1;
				// $konter = addZero($no,3);
				
				# Prep No Jurnal
				$nojurnal = str_replace('-','',$tanggal)."/".$unit."/".$kodejurnal."/".addZero($konter+$notemp,3);
				// GRIR 2021
				$nojurnalro = str_replace('-','',$tanggal)."/".$ronya."/".$kodejurnal."/".addZero($konterro+$notempro,3);

				#== header
				#= jurnal ht
				$data['header'] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$bar['tanggal'],
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$bar['notransaksi'],
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				// GRIR 2021
				$dataro['header'] = array(
					'nojurnal'=>$nojurnalro,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$bar['tanggal'],
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$bar['notransaksi'],
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				
				#== detail
				#= debet
				$data['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>substr($bar['kodekegiatan'],0,7),
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$bar['unit'],
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// GRIR 2021
				$dataro['detail'][] = array(
					'nojurnal'=>$nojurnalro,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$akuncacoes,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot'],
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$ronya,
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				$noUrut++;
				
				#= kredit
				// kalo ini RO, langsung ke GRIR 2021
				if($unit==$ronya){
					$akuncacoro=$noakunkr;
				}
				$data['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$akuncacoro,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot']*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$bar['unit'],
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// GRIR 2021
				$dataro['detail'][] = array(
					'nojurnal'=>$nojurnalro,
					'tanggal'=>$bar['tanggal'],
					'nourut'=>$noUrut,
					'noakun'=>$noakunkr,
					'keterangan'=>'barang: '.$bar['kodebarang'].', jumlah: '.$bar['jumlah'].', PO/SO: '.$bar['nopo'].', vendor: '.$optsupplier[$bar['supplierid']].'',
					'jumlah'=>$bar['hartot']*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$ronya,
					'kodekegiatan'=>$bar['kodekegiatan'],
					'kodeasset'=>$kodeasset,
					'kodebarang'=>$bar['kodebarang'],
					'nik'=>$bar['penerima'],
					'kodecustomer'=>'',
					'kodesupplier'=>$bar['supplierid'],
					'noreferensi'=>$bar['notransaksi'],
					'noaruskas'=>'',
					'kodevhc'=>$kodevhc,
					'nodok'=>$bar['nopo'],
					'kodeblok'=>$kodeblok,
					'revisi'=>'0',
					'kodesegment' => $defSegment
				);
				// echo "<pre>";
				// print_r($data);
				// print_r($dataro);
				// echo "</pre>";
				// exit("error!!!");
				
				$queryH = insertQuery($dbname,'keu_jurnalht',$data['header']);
				$owlPDO->exec($queryH);
				
				foreach($data['detail'] as $key=>$dataDet) {
					$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
					$owlPDO->exec($queryD);
				}
				// GRIR 2021
				if($unit!=$ronya){
				$queryHro = insertQuery($dbname,'keu_jurnalht',$dataro['header']);
				$owlPDO->exec($queryHro);
				
				foreach($dataro['detail'] as $key=>$dataDetro) {
					$queryDro = insertQuery($dbname,'keu_jurnaldt',$dataDetro);
					$owlPDO->exec($queryDro);
				}
				}
					
			}
			
			
			
			
			# Get Journal Counter
			$queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konter+$notemp)),
							"kodeorg='".$pt."' and kodeunit='".$unit."' and  
							periode='".substr($tanggal,0,7)."' and kodekelompok='".$kodejurnal."'");	
							// exit("Error:".$queryJRB);
			$owlPDO->exec($queryJRB);						
			if($unit!=$ronya){
			$queryJRBro = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>($konterro+$notempro)),
							"kodeorg='".$pt."' and kodeunit='".$ronya."' and  
							periode='".substr($tanggal,0,7)."' and kodekelompok='".$kodejurnal."'");	
							// exit("Error:".$queryJRB);
			$owlPDO->exec($queryJRBro);
			}
			if($tanggalselesai){
				$tglselesai = tanggalsystemn($tanggalselesai);
			}else{
				$tglselesai = '0000-00-00';
			}
			##UBAH FLAG Posting
			$str="update ".$dbname.".log_noninventory set posting='1', postedby='".$_SESSION['standard']['userid']."', postedtime='".$tglskrg."', tanggalselesai='".$tglselesai."',keterangan='".$keterangan."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	
	case'postinggrx':
		$tab.="";
		
		$str="select tipe from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$tipe=$res[0]['tipe'];
		
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type=text class=myinputtext id=tanggalselesai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 readonly/></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['keterangan']."</td>
				<td>:</td>
				<td style='vertical-align:top'><textarea id=keterangan onkeypress=\"return tanpa_kutip(event);\" cols=15 rows=2></textarea></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td valign=top><button class=mybutton onclick=postinggr('".$notransaksi."','".$tipe."')>".$_SESSION['lang']['posting']."</button></td>
			</tr>";
		$tab.="</table><p>";
		
		echo $tab;
	break;

	case'previewgr':
		$tab.="";
		
		$str="select pt,unit,penerima,tanggal,nopo,termin from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$unitx=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$termin=$res[0]['termin'];
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optpenerima=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$penerima."'");
		
		$pt=$optpt[$pt];
		$unitx=$optunit[$unit];
		$penerima=$optpenerima[$penerima];
		
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['perusahaan']."</td>
				<td>:</td>
				<td>".$pt."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>".$unitx."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['penerima']."</td>
				<td>:</td>
				<td>".$penerima."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>".$tanggal."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>";
			if($termin > 0){
				$tab.="<tr>
					<td>".$_SESSION['lang']['termin']."</td>
					<td>:</td>
					<td>".$termin."</td>
				</tr>";
			}
		$tab.="</table><p>";
		
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center'>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>".$_SESSION['lang']['sudahditerima']."</td>
				<td>".$_SESSION['lang']['kuantitaspo']."</td>
				<td width=75px>".$_SESSION['lang']['diterima']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>Filename</td>
				<td>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$str="select * from ".$dbname.".log_noninventorydt where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no++;
			$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##SUDAH DITERIMAKAN
			$sudahditerima=0;
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_noninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi!='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlah'];				
			}
			
			##KUANTITAS PO/SO
			$jumlahpesan=0;
			$strx="select jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			
			##FILE DOWNLOAD
			$path   = "fileupload/log_penerimaanx/";
			$nmTemp=str_replace('-','',str_replace('/','',$notransaksi));

			$strListFile = "select * from ".$dbname.".listfile_log_penerimaan where notransaksi='".$notransaksi."' and namafile like '%".$nmTemp."_".$kodebarang."%'";
			$resLF = fetchData($strListFile);
			$namafile = $resLF[0]['namafile'];
			// exit("warning".$namafile);
			// exit("warning".print_r($resLF));
			foreach($resx as $valx){
				$jumlahpesan=$valx['jumlahpesan'];				
			}
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$val['kodebarang']."</td>";
			$tab.="<td align=left>".$optbarang[$val['kodebarang']]."</td>";
			$tab.="<td align=left>".$val['satuan']."</td>";
			$tab.="<td align=left>".$val['nopp']."</td>";
			$tab.="<td align=right>".hidezerodecimal($sudahditerima,3)."</td>";
			$tab.="<td align=right>".hidezerodecimal($jumlahpesan,3)."</td>";
			$tab.="<td align=right>".hidezerodecimal($val['jumlah'],3)."</td>";
			$tab.="<td align=left>".getsubunit($unit,$val['subunit'])."</td>";
			$tab.="<td align=left>".getsubunitdt($val['subunit'],$val['subunitdt'],2)."</td>";
			$tab.="<td align=left>".getkegiatan($val['subunit'],$val['subunitdt'],$val['kodekegiatan'],2)."</td>";
			$tab.="<td align=left>".$namafile."</td>";
			if(count($namafile) > 0) {
				$tab.="<td><a href='".$path.$namafile."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp".$dp."</td>";
			} else {
				$tab.="<td></td>";
			}
			$tab.="</tr>";
		}
			
		$tab.="</tbody>
		</table><p>";
		
		echo $tab;
	break;
	
	case'previewpdfgr':
		$tab="";
	
		$str="select pt,unit,penerima,tanggal,nopo,supplierid,postedby,disetujui from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			
		$res=fetchdata($str);
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		$disetujui=$res[0]['disetujui'];
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optpurchaser=makeOption($dbname,'log_poht','nopo,purchaser',"nopo='".$nopo."'");
		
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
		$purchaser=$optpurchaser[$nopo];

		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-weight:bold'>BUKTI PENERIMAAN BARANG</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:12px;' width=100%>
			<tr>
				<td width=60% style='font-weight:bold'>".$pt."</td>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td>No. Transaksi</td>
						<td>:</td>
						<td>".$notransaksi."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".$tanggal."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>:</td>
						<td>".$nopo."</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td>
				<table>
					<tr>
						<td>Bisnis Unit</td>
						<td>:</td>
						<td>".$unit."</td>
					</tr>
					<tr>
						<td>Diterima Dari</td>
						<td>:</td>
						<td>".$supplier."</td>
					</tr>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:12px' cellpadding=3 cellspacing=0>
			<tr style='font-weight:bold'>
				<td align=center style='border:0.1px solid #000'>".$_SESSION['lang']['nourut']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['kodebarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['namabarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['satuan']."</td>
				<td align=right style='border:0.1px solid #000'>".$_SESSION['lang']['jumlah']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['keterangan']."</td>
			</tr>";

		# Abdul
		// $penerima  = '';
		$disetujui = '';

		$str  = "SELECT karyawanid,level FROM " . $dbname . ".approval WHERE notransaksi='" . $notransaksi . "'";
		$res  = fetchdata($str);
		$data = array();
		$jml  = count($res);

		if ($res > 0) {
			foreach ($res as $key => $value) {
				$data[$value['level']] = $value['karyawanid'];
			}

			// $penerima  = $data['1'];
			$disetujui = $data['2'];
		}
		# End Abdul
			
		$str="select * from ".$dbname.".log_noninventorydt where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no++;
			$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##SUDAH DITERIMAKAN
			$sudahditerima=0;
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_noninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi!='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlah'];				
			}
			
			##KUANTITAS PO/SO
			$jumlahpesan=0;
			$strx="select jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$jumlahpesan=$valx['jumlahpesan'];				
			}
			
			$tab.="<tr>";
			$tab.="<td align=center style='border-left:0.1px solid #000'>".$no."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['kodebarang']."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$optbarang[$val['kodebarang']]."</td>";
			
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['satuan']."</td>";
			$tab.="<td align=right style='border-left:0.1px solid #000'>".hidezerodecimal($val['jumlah'],3)."</td>";
			if($urlefil=='0'){
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'>".getkegiatan(@$val['subunit'],@$val['subunitdt'],@$val['kodekegiatan'],2)."</td>";
			}else{
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'></td>";
			}
				// exit("Error:aaa");
			$tab.="</tr>";
		}
			
		$tab.="<tr><td colspan=6 style='border-top:0.1px solid #000'>&nbsp;</td></tr></table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:12px'>
			<tr style='text-align:center'>
				<td>Administrasi Pembelian</td>
				<td>Diperiksa Oleh</td>
				<td>Diketahui Oleh</td>
			</tr>
			<tr>
				<td height=25px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td>".getNamaKaryawan($purchaser)."</td>
				<td>".getNamaKaryawan($penerima)."</td>
				<td>". (($disetujui == NULL) ? getNamaKaryawan($data['1']) : getNamaKaryawan($disetujui))."</td>
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($purchaser)."</td>
				<td>".getJabatanKaryawan($penerima)."</td>";

				if($data['1'] == ''){
					$tab.="<td>".(($disetujui == NULL) ? ($data['1']) : getJabatanKaryawan($disetujui))."</td>";
				}else{
					$tab.="<td>".(($disetujui == NULL) ? getJabatanKaryawan($data['1']) : getJabatanKaryawan($disetujui))."</td>";
				}

			$tab.="</tr>
		</table>";
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		
		## Print Out
		if($urlefil=='0'){
			$dompdf->stream("Print RFQ", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;

	//Umar
	case 'formajukan':
		$nmdept = makeOption($dbname,'log_noninventory','notransaksi,createdby',"notransaksi='".$notransaksi."'");
		$countApp = getCountApproval($tipe, $unit, getKary($nmdept[$notransaksi],'bagian'));
		// $tab.="Persetujuan";
					$tab.="<table cellpadding=2 cellspacing=1 border=0>";
					// $tab.="<table cellpadding=2 cellspacing=1 border=0>
					// <thead>
					// <tr style='font-weight:bold'>
						// <td align='center' colspan=2>".$_SESSION['lang']['keterangan']."</td>
						// <td align='center'>".$_SESSION['lang']['action']."</td>
					// </tr>
					// </thead>";
				$tab.="<tbody id='listfile'>";

		// $countApp = getHitungApproval($kasbank,$param['kodeorg'],'','','',$resH[0]['jumlah']);
			// for($i=1;$i<=$countApp;$i++){
				$i = 1;
				$arrList = listApprove($i, $tipe, $unit, getKary($nmdept[$notransaksi],'bagian'));
				
				// echo"<pre>";
				// print_r($arrList);
				// $optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


				$arrDetail = detailApprove($i, $notransaksi, $tipe);
				$optpersetujuan="";
				foreach($arrList as $key => $val){
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
				$tab.="<tr  class=rowcontent>
				<td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
				<td>:</td>
				<td colspan=1><select style=\"width:204px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
				</tr>";  
			// }

			if($countApp<1){
				exit("Warning : Setup approval belum disetting.");
			}
			
			$tab.="
			<tr  class=rowcontent>
				<td>".$_SESSION['lang']['tanggal']."</td> 
				<td>:</td>
				<td>
					<input type=text class=myinputtext disabled value='".date('d-m-Y')."'  id=tanggalpengajuan onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:200px;\">
				</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=saveajukan('".$notransaksi."','".$tipe."','".$i."')>Simpan</button>
				</td>
			</tr>
		</table>
		";

		echo $tab;
	break;

	case'saveajukan':
	
		// echo"<pre>";
		// print_r($param);
		// echo"</pre>";exit("Error:A");
		try {
			$owlPDO->beginTransaction();
			
			if($param['tanggalpengajuan']==''){
				exit("Warning:Tanggal pengajuan masih kosong");
			}
			
			for($i=1;$i<=$param['maxaproval'];$i++){
				if($param['persetujuan'][$i]==''){
					exit("Warning: Persetujuan ".$i." belum dipilih.");
				}
			}
			// echo"<pre>";
			// print_r($param['kasbank']);
			// echo"</pre>";
			#= delete 1st untuk aprovalnya
			$str = "delete from " . $dbname . ".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan = '".$param['tipe']."'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".log_noninventory set persetujuan=9 where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			// exit("Error:MASUKKK");
			// echo $param['maxaproval'];exit("error");
			for($i=1;$i<=$param['maxaproval'];$i++){
				#= insert
				$str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
					   values('".$param['notransaksi']."','".$param['tipe']."','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";	
				// exit("Error:".$str);
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
			echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

		}
	break;

	// End Umar
	
	case'previewpdfgrba':
			
		$tab="";
		
		$str="select a.*,b.* from ".$dbname.".log_noninventory a 
				left join ".$dbname.".log_noninventorydt b on a.notransaksi=b.notransaksi where a.notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$jlhsat[$val['notransaksi']] += $val['jumlah'];
		}
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		$tanggalselesai=$res[0]['tanggalselesai'];
		$keterangan=$res[0]['keterangan'];
		$kodebarang=$res[0]['kodebarang'];
		// $disetujui=$res[0]['disetujui'];
		$satuan=$res[0]['satuan'];

		//Umar
		// $penerima  = '';
		$disetujui = '';

		$str  = "SELECT karyawanid,level FROM ".$dbname.".approval WHERE notransaksi='".$notransaksi."'";
		$res  = fetchdata($str); 
		$data = array();
		$jml  = count($res);
		
		if ($res > 0) {
			foreach ($res as $key => $value) {
				$data[$value['level']] = $value['karyawanid'];
			}

			// $penerima  = $data['1'];
			$disetujui = $data['2'];
		}
		//End Umar
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optalamat=makeOption($dbname,'organisasi','kodeorganisasi,alamat',"kodeorganisasi='".$pt."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
		// $optpurchaser=makeOption($dbname,'log_poht','nopo,purchaser',"nopo='".$nopo."'");
		$optpurchaser=makeOption($dbname,'log_noninventory','notransaksi,createdby', "notransaksi='".$notransaksi."'");
		
		$alamat=$optalamat[$pt];
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
		// $purchaser=$optpurchaser[$nopo];
		$purchaser=$optpurchaser[$notransaksi];
	
		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=left width=55px><img src='images/ksp.jpg'  class='zImgOffBtn' style='width:50px;height:50px'></td>
				<td align=center style='font-weight:bold;font-size:24px'>".$pt."</td>
			</tr>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-size:12px' colspan=2>Alamat : ".$alamat."</td>
			</tr>
			<br>
			<tr>
				<td align=center style='font-weight:bold;padding-top:19px;font-size:19px' colspan=2><u>BERITA ACARA  PENYELESAIAN PEKERJAAN</u></td>
			</tr>
			<tr>
				<td align=center style='font-size:15px' colspan=2>No : ".$notransaksi."</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;text-align: justify;' width=100%>
			<tr>
				<td>Pada hari ini ".hari($tanggalselesai)." Tanggal ".kekata(substr($tanggalselesai,8,2))." Bulan ".numToMonth(substr($tanggalselesai,5,2),'I','long')." Tahun ".kekata(substr($tanggalselesai,0,4))." telah dicek dan dioperasikan ".$optbarang[$kodebarang]." dengan spesifikasi seperti dibawah ini :<br><br></td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;padding-left:100px;padding-right:100px;' width=100%>
			<tr>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td style='width:100px'>1. Jenis Pekerjaan</td>
						<td>=</td>
						<td>".$optbarang[$kodebarang]."</td>
					</tr>
					<tr>
						<td>2. ".$_SESSION['lang']['jumlah']."</td>
						<td>=</td>
						<td>".$jlhsat[$notransaksi]." ".$satuan."</td>
					</tr>
					<tr>
						<td>3. ".$_SESSION['lang']['kontraktor']."</td>
						<td>=</td>
						<td>".$supplier."</td>
					</tr>
					<tr>
						<td>4. Service Order</td>
						<td>=</td>
						<td>".$nopo."</td>
					</tr>
					<tr>
						<td>5. Selesai Pekerjaan</td>
						<td>=</td>
						<td>".tglnmblnhr($tanggalselesai,'I','long')."</td>
					</tr>
					<tr>
						<td>6. Keterangan</td>
						<td>=</td>
						<td>".$keterangan."</td>
					</tr>
					<br>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:15px' cellspacing=0>
			<tr>
				<td>Dengan data ini maka pembayaran  dapat dilaksanakan oleh Finance ke Kontraktor ".$supplier.".</td>
			</tr>
			<tr>
				<td><p>Demikian Berita Acara Penyelesaian Pekerjaan ini dibuat agar dipergunakan sebagai mana mestinya.</p><br><br></td>
			</tr>
			</table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:13px'>
			<tr style='text-align:center'>
				<td>Dibuat Oleh</td>
				<td>Diterima Oleh</td>
				<td>Disetujui Oleh</td>
			</tr>
			<tr>
				<td height=100px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td><b>".getNamaKaryawan($purchaser)."</b></td>
				<td><b>".(($penerima == NULL) ? "-" : getNamaKaryawan($penerima))."</b></td>
				<td><b>".(($disetujui == NULL) ? getNamaKaryawan($data['1']) : getNamaKaryawan($disetujui))."</b></td>
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($purchaser)."</td>
				<td>".(($penerima == NULL) ? "-" : getJabatanKaryawan($penerima))."</td>
				<td>".(($disetujui == NULL) ? getJabatanKaryawan($data['1']) : getJabatanKaryawan($disetujui))."</td>
			</tr>
		</table>";
		
		
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		// $dompdf->stream("Print BA Penerimaan Barang", array("Attachment" => false));

		if($urlefil=='0'){
			$dompdf->stream("Print BA Penerimaan Barang", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
		
	break;
}

function getsubunit($unit,$subunit){
	global $dbname;
	global $owlPDO;
	
	$hasil='';
	
	if($unit==$subunit){
		$hasil="KANTOR/OFFICE";
	}else{
		##GET SUBUNIT
		$strx="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe not like '%gudang%'";
		$resx=fetchdata($strx);
		foreach($resx as $valx){
			if($subunit==$valx['kodeorganisasi']){
				$hasil=$valx['kodeorganisasi']." - ".$valx['namaorganisasi'];
			}
		}
	}
	
	
	if($subunit=='PROJECT'){
		$hasil='PROJECT';
	}
	
	if($subunit=='KONTRAKTOR'){
		$hasil='KONTRAKTOR';
	}
	
	if($subunit=='KUD'){
		$hasil='KUD TBS';
	}
	
	if($subunit=='SUPPLIER'){
		$hasil='SUPPLIER TBS';
	}
	
	if($subunit=='PETANI'){
		$hasil='PETANI';
	}
	
	return $hasil;
}

function getsubunitdt($subunit,$subunitdt,$tipe){
	global $dbname;
	global $owlPDO;
	global $unit;
	
	$tipeorg="";
	$temptipe="";
	
	if($unit==$subunit){
		$tipeorg='1';
	}else{
		## GET TIPE ORGANISASI
		$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunit."' limit 1";
		$res=fetchdata($str);
		$temptipe = $res[0]['tipe'];
		if($temptipe==''){
			$tipeorg='0';
		}else{
			if($temptipe=='WORKSHOP'){
				$tipeorg='1';
			}else{
				$tipeorg='0';
			}
		}
	}
	
	if($tipeorg=='1'){
		$optsubunitdt="<option value=''></option>";
	}else{
		$optsubunitdt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	}
	
	$hasil='';
		
	if($unit!=$subunit){
		if($temptipe=='TRAKSI'){
			$str="select kodevhc,nopol,detailvhc from ".$dbname.".vhc_5master where kodetraksi='".$subunit."' and status='1' order by kodevhc asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['kodevhc']){
					$hasil=$val['kodevhc']." ".($val['nopol']==''?'':' - '.$val['nopol'])." ".($val['detailvhc']==''?'':' - '.$val['detailvhc']);
					$optsubunitdt.="<option value='".$val['kodevhc']."' selected>".$val['kodevhc']." ".($val['nopol']==''?'':' - '.$val['nopol'])." ".($val['detailvhc']==''?'':' - '.$val['detailvhc'])."</option>";
				}else{
					$optsubunitdt.="<option value='".$val['kodevhc']."'>".$val['kodevhc']." ".($val['nopol']==''?'':' - '.$val['nopol'])." ".($val['detailvhc']==''?'':' - '.$val['detailvhc'])."</option>";
				}
			}
		}
		
		if($temptipe=='SIPIL'){
			$str="select kodeperumahan,kompleks,blok,norumah from ".$dbname.".sdm_perumahan order by kompleks asc, blok asc, norumah asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['kodeperumahan']){
					$hasil=$val['kompleks']." - ".$val['blok']." - ".$val['norumah'];
					$optsubunitdt.="<option value='".$val['kodeperumahan']."' selected>".$val['kompleks']." - ".$val['blok']." - ".$val['norumah']."</option>";
				}else{
					$optsubunitdt.="<option value='".$val['kodeperumahan']."'>".$val['kompleks']." - ".$val['blok']." - ".$val['norumah']."</option>";
				}
			}
		}
		
		if($subunit=='PROJECT'){
			$str="select kode,nama from ".$dbname.".project where kodeorg='".$unit."'and posting='0' order by nama asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if(substr($val['kode'],0,2)=='AK' or substr($val['kode'],0,2)=='PB'){
					if($subunitdt==$val['kode']){
						$hasil=$val['kode']." - ".$val['nama'];
						$optsubunitdt.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
					}else{
						$optsubunitdt.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
					}
				}else{
					if($val['posting']=='0'){
						if($subunitdt==$val['kode']){
							$hasil=$val['kode']." - ".$val['nama'];
							$optsubunitdt.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
						}else{
							$optsubunitdt.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
						}
					}
				}
			}
		}
		
		if($subunit=='KONTRAKTOR'){
			$str="select a.supplierid,a.namasupplier from ".$dbname.".log_5supplier a left join ".$dbname.".log_5supkelompok b on a.supplierid = b.supplierid where b.tipe='KONTRAKTOR' and b.status='1' order by a.namasupplier asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['supplierid']){
					$hasil=$val['supplierid']." - ".$val['namasupplier'];
					$optsubunitdt.="<option value='".$val['supplierid']."' selected>".$val['supplierid']." - ".$val['namasupplier']."</option>";					
				}
				$optsubunitdt.="<option value='".$val['supplierid']."'>".$val['supplierid']." - ".$val['namasupplier']."</option>";
			}
		}
		
		if($subunit=='KUD'){
			$str="select a.supplierid,a.namasupplier from ".$dbname.".log_5supplier a left join ".$dbname.".log_5supkelompok b on a.supplierid = b.supplierid where b.tipe='SUPPLIERTBSKUD' and b.status='1' order by a.namasupplier asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['supplierid']){
					$hasil=$val['supplierid']." - ".$val['namasupplier'];
					$optsubunitdt.="<option value='".$val['supplierid']."' selected>".$val['supplierid']." - ".$val['namasupplier']."</option>";					
				}
				$optsubunitdt.="<option value='".$val['supplierid']."'>".$val['supplierid']." - ".$val['namasupplier']."</option>";
			}
		}
		
		if($subunit=='SUPPLIER'){
			$str="select a.supplierid,a.namasupplier from ".$dbname.".log_5supplier a left join ".$dbname.".log_5supkelompok b on a.supplierid = b.supplierid where b.tipe='SUPPLIERTBS' and b.status='1' order by a.namasupplier asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['supplierid']){
					$hasil=$val['supplierid']." - ".$val['namasupplier'];
					$optsubunitdt.="<option value='".$val['supplierid']."' selected>".$val['supplierid']." - ".$val['namasupplier']."</option>";					
				}
				$optsubunitdt.="<option value='".$val['supplierid']."'>".$val['supplierid']." - ".$val['namasupplier']."</option>";
			}
		}
		
		if($subunit=='PETANI'){
			$str="select kode,nama from ".$dbname.".kebun_5masterpetani where unit='".$unit."' and status='A' order by nama asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if($subunitdt==$val['kode']){
					$hasil=$val['kode']." - ".$val['nama'];
					$optsubunitdt.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";				
				}
				$optsubunitdt.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
			}
		}
		
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$subunit."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($subunitdt==$val['kodeorganisasi']){
				$hasil=$val['kodeorganisasi']." - ".$val['namaorganisasi'];
				$optsubunitdt.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}
			$optsubunitdt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
	}
	if($tipe=='1'){
		return $optsubunitdt."####".$tipeorg;		
	}else if($tipe='2'){
		return $hasil;
	}
}

function getkegiatan($subunit,$subunitdt,$kegiatan,$stipe){
	global $dbname;
	global $owlPDO;
	
	$unit=substr($subunit,0,4);
	$hasil="";
	$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
	if($subunit!=''){
		if($unit==$subunit){
			$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun, kelompok,namakegiatan";
			if(substr($unit,2,2)=='HO'){
			$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok in ('KNT','KNT1') and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
			}
		}else{
			$strx="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunit."'";
			$resx=fetchdata($strx);
			$temptipe="";
			if(count($resx)>0){
				$temptipe=$resx[0]['tipe'];
			}
			
			if($temptipe=='WORKSHOP'){
				$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by noakun,kelompok desc,namakegiatan asc";
			}else{
				if($temptipe!=''){
					if($subunitdt!=''){
						######
						if($temptipe=='STENGINE' or $temptipe=='STATION' or $temptipe=='MAINTENANCE'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='MIL' and status = '1' order by noakun,kelompok,namakegiatan";
						}
						
						######
						if($temptipe=='AFDELING'){
							$strx="select statusblok from ".$dbname.".setup_blok where kodeorg='".$subunitdt."'";
							$resx=fetchdata($strx);
							$statusblok=$resx[0]['statusblok'];
							if($statusblok=='TM'){
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by noakun,kelompok,namakegiatan";
							}else{
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by noakun,kelompok,namakegiatan";
							}
						}
						
						######
						if($temptipe=='SIPIL'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where (kelompok='SPL' or kelompok='KNT') and status = '1' order by noakun,kelompok,namakegiatan"; 
						}
						
						######
						if($temptipe=='TRAKSI'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by noakun,kelompok desc,namakegiatan";	   
						}
						
						######
						if($temptipe=='BIBITAN'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
						}
					}
				}else{
					if($subunitdt!=''){
						
						$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' order by noakun,kelompok,namakegiatan";
						if($subunit=='PROJECT'){
							if(substr($subunitdt,0,2)=='AK'){
								$tipeasset=substr($subunitdt,3,2);
								$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where noakun in (select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."') order by noakun,kelompok,namakegiatan";
							}
						}
						
						if($subunit=='KONTRAKTOR' || $subunit=='KUD' || $subunit=='SUPPLIER' || $subunit=='PETANI'){
							$str="select kodekegiatan,kelompok,namakegiatan, noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and substr(kodekegiatan,1,5) not in ('12997') and status = '1' and kodekegiatan like '116%' order by noakun,kelompok,namakegiatan";
						}
					}
				}
			}
		}
		// $str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunitdt."'";
		// $res=fetchdata($str);
		// $tipe=$res[0]['tipe'];
		// if($tipe!=''){
			// ######
			// if($tipe=='STENGINE' or $tipe=='STATION' or $tipe=='MAINTENANCE'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='MIL' and status = '1' order by kelompok,namakegiatan";
			// }
			
			// ######
			// if($tipe=='BLOK'){
				// $str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$subunitdt."'";
				// $res=fetchdata($str);
				// $statusblok=$res[0]['statusblok'];
				
				// if($statusblok=='TM'){
					// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by kelompok,namakegiatan";
				// }else{
					// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by kelompok,namakegiatan";
				// }
			// }
			
			// ######
			// if($tipe=='WORKSHOP'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok desc,namakegiatan asc";
			// }
			
			// ######
			// if($tipe=='SIPIL'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where (kelompok='SPL' or kelompok='KNT') and status = '1' order by kelompok,namakegiatan"; 
			// }
			
			// ######
			// if($tipe=='TRAKSI'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok desc,namakegiatan";	   
			// }
			
			// ######
			// if($tipe=='BIBITAN'){
				// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok,namakegiatan";
			// }
		// }else{
			// $str="select kodekegiatan,kelompok,namakegiatan from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127',126) and status = '1' order by kelompok,namakegiatan";
		// }
		
		// if(isset($str)){
		// 	$res=fetchdata($str);
		// 	foreach($res as $val){
		// 		$e=substr($val['noakun'],0,3);
		// 		if($e!=$m){			
		// 			$optkegiatan.="<optgroup label='".$e." - ".getNamaAkun($e)."'>";
		// 		}
		// 		// $d=substr($val['noakun'],0,5);
		// 		$d=$val['noakun'];
		// 		if($d!=$n){			
		// 			$optkegiatan.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
		// 		}
		// 		if($kegiatan==$val['kodekegiatan']){
		// 			$hasil="[".$val['kelompok']."] - ".$val['namakegiatan'];
		// 			$optkegiatan.="<option value='".$val['kodekegiatan']."' selected>".$val['kodekegiatan']." - ".$val['namakegiatan']."</option>";				
		// 		}else{
		// 			$optkegiatan.="<option value='".$val['kodekegiatan']."'>".$val['kodekegiatan']." - ".$val['namakegiatan']."</option>";
		// 		}
		// 		$n=$d;
		// 		if($d!=$n){			
		// 			$optkegiatan.="</optgroup>";
		// 		}
		// 		$m=$e;
		// 		if($e!=$m){			
		// 			$optkegiatan.="</optgroup>";
		// 		}
		// 	}
		// }

		if (isset($str)) {
			$res = fetchdata($str); // Ambil data dari fungsi fetchdata
			foreach ($res as $val) {
				$e = substr($val['noakun'], 0, 3); // Noakun level 3
	
				// Jika noakun level 3 berubah, tutup optgroup sebelumnya dan buka yang baru
				if ($e != $m) {
					if ($m != "") {
						$optkegiatan .= "</optgroup>"; // Tutup optgroup sebelumnya
					}
					$optkegiatan .= "<optgroup label='" . $e . " - " . getNamaAkun($e) . "'>";
					$m = $e; // Update noakun level 3
				}
	
				// Tambahkan opsi
				if ($kegiatan == $val['kodekegiatan']) {
					$optkegiatan .= "<option value='" . $val['kodekegiatan'] . "' selected>" . $val['kodekegiatan'] . " - " . $val['namakegiatan'] . "</option>";
				} else {
					$optkegiatan .= "<option value='" . $val['kodekegiatan'] . "'>" . $val['kodekegiatan'] . " - " . $val['namakegiatan'] . "</option>";
				}
			}
	
			// Tutup optgroup terakhir
			if ($m != "") {
				$optkegiatan .= "</optgroup>";
			}

			
	}



	}
	
	if($stipe=='1'){
		return $optkegiatan;		
	}else if($tipe='2'){
		return $hasil;
	}
}
?>
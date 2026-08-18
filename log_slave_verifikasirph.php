<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');	
	
$method=checkPostGet('method','');
$pages=checkPostGet('page','');

$kdPt=checkPostGet('kdPt','');

##PARAM SEARCH
$crnotransaksi=checkPostGet('crnotransaksi','');
$crtanggal=checkPostGet('crtanggal','');
$schnopp=checkPostGet('schnopp','');
$schjenis=checkPostGet('schjenis','');
$schunit=checkPostGet('schunit','');
$schpt=checkPostGet('schpt','');
$schklbrg=checkPostGet('schklbrg','');
$schkdbrg=checkPostGet('schkdbrg','');

$countbaris=checkPostGet('baris','');

$supplier_id=checkPostGet('id_supplier','');
$norurut=checkPostGet('norurut','');
$id_alamat_supplier=checkPostGet('id_alamat_supplier','');

$nopp2=checkPostGet('nopp2','');
$formPil=checkPostGet('formPil','');

$notransaksi=checkPostGet('notransaksi','');

$no_prmntan=checkPostGet('ckno_permintaan','');

$nourut=checkPostGet('nourut','');
$nilDiskon=checkPostGet('nilDiskon','');
$diskonPersen=checkPostGet('diskonPersen','');
$nilPPn=checkPostGet('nilPPn','');
$pbbkb=checkPostGet('pbbkb','');
$nilaiPermintaan=checkPostGet('nilaiPermintaan','');
$subTotal=checkPostGet('subTotal','');
$termPay=checkPostGet('termPay','');
$idFranco=checkPostGet('idFranco','');
$stockId=checkPostGet('stockId','');
$ketUraian=checkPostGet('ketUraian','');
$tglDari=checkPostGet('tglDari','');
$tglSmp=checkPostGet('tglSmp','');
$mtUang=checkPostGet('mtUang','');
$kurs=checkPostGet('kurs','');
$supplierId=checkPostGet('supplierId','');

$notransaksi=checkPostGet('notransaksi','');
$supplierid=checkPostGet('supplierid','');
$namafile=checkPostGet('namafile','');

$no_permintaan=checkPostGet('no_permintaan','');
$alasan=checkPostGet('alasan','');
	
$jenisApp = 'RFQ';	
 
switch ($method)
{
	case 'loaddata':
		$tab="";
		$limit=20;
        $page=0;
        if(isset($pages))
		{
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$where = "";
		$where.="nomor in (select nomor from ".$dbname.".log_perintaanhargaht where right(nomor,4) in (".getOrgDetail(2).") and (statusverifikasi='1' || statusverifikasi='3' || statusverifikasi='4'))";
		$where2.="a.nomor in (select nomor from ".$dbname.".log_perintaanhargaht where right(nomor,4) in (".getOrgDetail(2).") and (statusverifikasi='1' || statusverifikasi='3' || statusverifikasi='4'))";
		if($crnotransaksi!='')
		{
			// $where.=" and norph like '%".$crnotransaksi."%'";
			// $where2.=" and a.norph like '%".$crnotransaksi."%'";
			$where.=" and (norph like '%".$crnotransaksi."%' or nomor like '%".$crnotransaksi."%')";
			$where2.=" and (a.norph like '%".$crnotransaksi."%' or a.nomor like '%".$crnotransaksi."%') ";
		}
		
		if($crtanggal!='')
		{
			$txt_tgl=tanggalsystemn($crtanggal);
			$where.=" and tanggal='".$txt_tgl."'";
			$where2.=" and a.tanggal='".$txt_tgl."'";
		}
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')!=''){			
		// 	$where.=" and nopp in (select nopp FROM ".$dbname.".log_prapoht where unit in (".getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')."))";
		// }
		
		// ada 2 ketentuan
		// 1. untuk pabrik dan bulking yang verifikasi hendra jaya
		// 2. kecuali diatas yg verifikasi purchaser masing masing
		// jika ketentuan point nomor 2 maka hanya munculkan PR yang mereka buat saja
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')==getUnitByMgrPurc($_SESSION['standard']['userid'],'purchaserid')){			
		// 	// $where.=" and nopp in (select nopp FROM ".$dbname.".log_listverifikasi where 1=1 and (karyawanid ='".$_SESSION['standard']['userid']."' or createdby ='".$_SESSION['standard']['userid']."')) or verificator='".$_SESSION['standard']['userid']."'";
		// 	$where.=" and (nopp in (select nopp FROM ".$dbname.".log_listverifikasi where 1=1 and (karyawanid ='".$_SESSION['standard']['userid']."' or createdby ='".$_SESSION['standard']['userid']."')) or verificator='".$_SESSION['standard']['userid']."')";
		// }
		
		$arrNopp=array();
		$sNopp="select distinct norph,nomor,nopp from ".$dbname.".log_permintaanhargadt where ".$where."";
		$rNopp=fetchData($sNopp);
		foreach ($rNopp as $key => $val) {
			if($val['norph'] != ''){
				$nomorx = $val['norph'];
			}else{
				$nomorx = $val['nomor'];
			}
			$arrNopp[$nomorx][]=$val['nopp'];
		}
		
		$jlhbrs = 0;
		// $str="select norph as jmlhrow from ".$dbname.".log_permintaanhargadt where norph!='' ".$where." group by norph order by tanggalverifikasi desc";
		$str="select norph as jmlhrow from ".$dbname.".log_permintaanhargadt where ".$where." group by norph order by tanggalverifikasi desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$jlhbrs = $jlhbrs+1;
		}
		
		$no=0;
		// $str="SELECT * FROM ".$dbname.".log_permintaanhargadt where norph!='' ".$where." group by norph ORDER BY tanggalverifikasi desc LIMIT ".$offset.",".$limit."";
		// $str="SELECT a.* FROM ".$dbname.".log_permintaanhargadt a left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor where ".$where2." and a.score='1' 
		// and b.purchaser='".$_SESSION['standard']['userid']."' group by a.nomor,a.norph ORDER BY a.tanggalverifikasi desc LIMIT ".$offset.",".$limit."";
		$str="SELECT a.* FROM ".$dbname.".log_permintaanhargadt a left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor where ".$where2." and a.score='1' 
		group by a.nomor,a.norph ORDER BY b.waktuverifikasi desc LIMIT ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()) 
		{

			if($bar['norph'] != ''){
				$nomor_x = $bar['norph'];
			}else{
				$nomor_x = $bar['nomor'];
			}
			
			$picverifikasi = makeOption($dbname,'log_perintaanhargaht','nomor,picverifikasi');
			$purchaserr = makeOption($dbname,'log_perintaanhargaht','nomor,purchaser');
			$tgl_ver = $bar['tanggalverifikasi'];
			// $pic_ver = $bar['verificator'];
			$pic_ver = $purchaserr[$bar['nomor']];
			$pic_verifikasi = $picverifikasi[$bar['nomor']];
			if($bar['tanggalverifikasi'] == '0000-00-00'){
				$strx="select * from ".$dbname.".log_perintaanhargaht where nomor='".$bar['nomor']."'";
				$resx=fetchdata($strx);
				$tgl_ver = substr($resx[0]['waktuverifikasi'],0,10);
				// $pic_ver = $resx[0]['picverifikasi'];
			}
			

			##periksa chat
			$strChat="select *  from ".$dbname.".log_rfq_chat where nomor='".$bar['nomor']."'";
			$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
			if(owlBaris($resChat)>0)
			{
				$ingChat="<img src='images/chat1.png' onclick=\"loadRFQChat('".$bar['nomor']."',event);\" class=zImgBtn>";
			}
			else
			{
				$ingChat="<img src='images/chat0.png'  onclick=\"loadRFQChat('".$bar['nomor']."',event);\" class=zImgBtn>";
			}

			$optNamaKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
			$no+=1;
			
			$tab.="<tr class=rowcontent>
				<td style='text-align:right;vertical-align:top'>".$no."</td>
				<td style='vertical-align:top;'>".$bar['nomor']."</td>
				<td style='vertical-align:top;'>".$bar['norph']."</td>
				<td style='text-align:center;vertical-align:top'>".tanggalnormal($tgl_ver)."</td>";

				$tab.="<td style='text-align:center;vertical-align:top'><ol type=1>";

			$lstNopp=$arrNopp[$bar['norph']];
			if(count($lstNopp) <= 0){
				$lstNopp=$arrNopp[$bar['nomor']];
			}

			foreach ($lstNopp as $key) {
				$tab.="<li style='cursor:pointer;color:blue' onclick=\"previewDetail('".$key."',event);\">".$key."</li>";
			}
			$tab.="</ol></td>";

		    $tab.="<td style='text-align:center;vertical-align:top'>".$ingChat."</td>";
		    $tab.="<td style='text-align:center;vertical-align:top'>".$optNamaKaryawan[$pic_ver]."</td>";
		    $tab.="<td style='text-align:center;vertical-align:top'>".$optNamaKaryawan[$pic_verifikasi]."</td>";

			// status persetujuan
			$strp="select status from ".$dbname.".approval where notransaksi = '".$bar['nomor']."' order by level desc limit 1";
			$resp=fetchdata($strp);
			if(count($resp)>0){
				$status_persetujuan = $resp[0]['status'];
			}else{
				$status_persetujuan = "";
			}
			$strv="select statusverifikasi,tolakrph from ".$dbname.".log_perintaanhargaht where nomor = '".$bar['nomor']."' order by nomor desc limit 1";
			$resv=fetchdata($strv);
			if(count($resv)>0){
				$statusverifikasi_f = $resv[0]['statusverifikasi'];
				$tolakrph_f = $resv[0]['tolakrph'];
			}else{
				$statusverifikasi_f = "";
				$tolakrph_f = "";
			}
			
			if($statusverifikasi_f=='1' && $status_persetujuan == '1' && $tolakrph_f != '2'){
				$tab .= "<td style='color:blue;cursor:pointer;text-align:center' onclick=\"gethistoriapproval('" . $bar['nomor'] . "',event)\">Disetujui</td>";
			}elseif($statusverifikasi_f=='1' && $status_persetujuan == '0' && $tolakrph_f != '2'){
				$tab .= "<td style='color:blue;cursor:pointer;text-align:center' onclick=\"gethistoriapproval('" . $bar['nomor'] . "',event)\">Proses Persetujuan</td>";
			}elseif($statusverifikasi_f=='1' && $tolakrph_f == '2'){
				$tab .= "<td style='color:blue;cursor:pointer;text-align:center' onclick=\"gethistoriapproval('" . $bar['nomor'] . "',event)\">Ditolak</td>";
			}else{
				$tab .= "<td style='color:blue;cursor:pointer;text-align:center' onclick=\"gethistoriapproval('" . $bar['nomor'] . "',event)\">Belum di verfikasi</td>";
			}


			$tab.="<td><table>";
				
			// $str2="select c.supplierid,c.namasupplier,a.nomor, a.nopp from ".$dbname.".log_permintaanhargadt a 
			// left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
			// left join ".$dbname.".log_5supplier c on b.supplierid=c.supplierid 
			// where a.norph='".$bar['norph']."' group by c.supplierid";

			$tenderH=0;
			$str2="select b.tender,c.supplierid,c.namasupplier,a.nomor, a.nopp from ".$dbname.".log_permintaanhargadt a 
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
			left join ".$dbname.".log_5supplier c on b.supplierid=c.supplierid 
			where a.nomor='".$bar['nomor']."'  and a.score='1' group by c.supplierid";
			$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_ASSOC);
			$nosup = 0;
			while($bar2=$res2->fetch())
			{
				$tenderH=$bar2['tender'];
				$nosup++;
				$tab.="<tr>
					<td style='text-align:right;vertical-align:top'>".$nosup."</td>
					<td style='color:blue;cursor:pointer;' onclick=\"previewlinkpemenang('".$nomor_x."', '".$bar2['supplierid']."', 'Detail Pemenang Perbandingan Harga' ,event)\">".$bar2['namasupplier']."</td>
					<td>
						<img style='display:none' src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".@$bar['nomor'].",".@$bar2['nourut']."','','log_slave_print_permintaan_penawaran',event);\">
					</td>
				</tr>";
			}
			$tab.="</table></td>";

			// PDF RPH PALMA
			if($tenderH == '1'){
				$tab.="
						<td style='text-align:center'>
							<img src=images/pdf.jpg class=zImgBtn title='Print' onclick=\"masterPDF('log_poht','".$nomor_x."','','log_slave_print_permintaan_penawaran_new',event);\">
						</td>";
			}else{
				$tab.="
						<td style='text-align:center'>
							
						</td>
				";
			}
				
			
					
			// $tab.="<td style='vertical-align:top;text-align:center'>
				// <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$bar['nomor'].",".$bar['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\">    						
				// <img onclick=datakeExcel(event,'".$bar['nomor']."') src=images/excel.jpg class=resicon title='MS.Excel'> 						
			// </td>";
				
			$tab.="</tr>";
		}
		
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0)
		{
			$totrows=1;
		}
		
		$isiRow='';
		for($er=1;$er<=$totrows;$er++)
		{
			$sel = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
		
		$frompage = (($page*$limit)+1);
		if((($page+1)*$limit) > $jlhbrs)
		{
			$topage = $jlhbrs;
		}
		else
		{
			$topage = (($page+1)*$limit);
		}
		$tab.="</tr>
		<tr>
			<td colspan=7 align=center>
				".$frompage." to ".$topage." Of ".  $jlhbrs."
			</td>
		</tr>
		<tr>
			<td colspan=7 align=center>";
		
		if($page=='0')
		{
			$tab.="";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
		}
		
		$tab.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>";
		
		if(($page+1) == $totrows)
		{
			$tab.="";
		}
		else
		{
			$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
		}
        $tab.="</td></tr>";
		
		echo $tab;
	break;
	
	case'getNotifikasi':
		$tab="";
		$jlhitem = 0; $arrData=$lstPP=$lstBrg=[];
		// $str="select * from ".$dbname.".log_listverifikasi where pemenang='0'";
		$where="";
		$whereverif="f.nomor in (select nomor from ".$dbname.".log_perintaanhargaht where statusverifikasi='0' and flag = '1')";
		
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')!=''){			
		// 	$where.=" and b.unit in (".getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator').")";
		// }
		
		// // ada 2 ketentuan
		// // 1. untuk pabrik dan bulking yang verifikasi hendra jaya
		// // 2. kecuali diatas yg verifikasi purchaser masing masing
		// // jika ketentuan point nomor 2 maka hanya munculkan PR yang mereka buat saja
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')==getUnitByMgrPurc($_SESSION['standard']['userid'],'purchaserid')){
		// 	$where.=" and (a.karyawanid ='".$_SESSION['standard']['userid']."' or a.createdby ='".$_SESSION['standard']['userid']."')";
		// }
		

		$where=" and b.unit in (".getOrgDetail(2).")";


		$str="select a.*,c.realisasi,c.satuankonversi,d.jumlahpesan, c.hasilkonversi from ".$dbname.".log_listverifikasi a 
			left join ".$dbname.".log_prapoht b on a.nopp=b.nopp 
			left join ".$dbname.".log_prapodt c on a.nopp=c.nopp and a.kodebarang = c.kodebarang 
			left join ".$dbname.".log_podt d on a.nopp=d.nopp and a.kodebarang = d.kodebarang 
			left join ".$dbname.".log_permintaanhargadt f on a.nopp=f.nopp and a.kodebarang = f.kodebarang 
			where ".$whereverif." and a.pemenang='0' ".$where." group by c.nopp, c.kodebarang order by c.tgl_sdt asc,c.nopp asc";
		//echo $str;
		$arr=fetchData($str);
		foreach($arr as $val => $bar){
			$jumlah = 0;
			if($bar['satuankonversi']=='' || is_null($bar['satuankonversi'])){
				if(($bar['jumlahpesan']=='')||is_null($bar['jumlahpesan'])||$bar['jumlahpesan']==0){
					$jumlah = $bar['realisasi']; $xxx=1;
				}elseif($bar['jumlahpesan']!=$bar['realisasi']) {
					$jumlah = intval($bar['realisasi'])-intval($bar['jumlahpesan']); $xxx=2;
				}else {
					$jumlah = $bar['realisasi']; $xxx=3;
				}
			}else{
				$jumlah = $bar['hasilkonversi']; $xxx=5;				
			}
			
			
			if($jumlah>0){
				$arrData[$bar['nopp']][$bar['kodebarang']]['jumlah']=$jumlah;
				@$lstPP[$bar['nopp']]=$bar['nopp'];
				@$lstBrg[$bar['kodebarang']]=$bar['kodebarang'];
			}
			
			/* $str = "select * from ".$dbname.".log_permintaanhargadt where kodebarang='".$val['kodebarang']."' and nopp='".$val['nopp']."' order by nomor desc limit 1";
			$arr2=fetchData($str);
			foreach($arr2 as $val2){
				$str = "select * from ".$dbname.".log_perintaanhargaht where flag='1' and nomor='".$val2['nomor']."' limit 1";
				$arr3=fetchData($str);
				foreach($arr3 as $val3){
					$jlhitem++;
				}
			} */
		}
		/* $mul=0;
		$noppList="";
		if(isset($lstPP)){
			foreach($lstPP as $rw){
				if($mul==0){
					$noppList="'".$rw."'";
					$mul=1;
				}else{
					$noppList.=",'".$rw."'";
				}
			}
		}
		$mul=0;
		if(isset($lstBrg)){
			foreach($lstBrg as $rw){
				if($mul==0){
					$brgList="'".$rw."'";
					$mul=1;
				}else{
					$brgList.=",'".$rw."'";
				}
			}
		} */
		
		
		$where = "and kodebarang in ('".implode("','",$lstBrg)."')";
		$where .= "and nopp in ('".implode("','",$lstPP)."')";
		
		$nomor = array();
		$str2 = "select * from ".$dbname.".log_permintaanhargadt where 1=1 ".$where." and flag='1' order by nomor";
		$arr2=fetchData($str2);
		foreach($arr2 as $val2){
			$nomor[$val2['nomor']]=$val2['nomor'];
		}
		
		$where .= "and nomor not in ('".implode("','",$nomor)."')";
		$str2 = "select * from ".$dbname.".log_permintaanhargadt where 1=1 ".$where." order by nomor";
		$arr2=fetchData($str2);
		foreach($arr2 as $val2){
			// $str3 = "select * from ".$dbname.".log_perintaanhargaht where flag='1' and statusverifikasi ='0' and tolakrph='0' and nomor='".$val2['nomor']."' limit 1";
			$str3 = "select * from ".$dbname.".log_perintaanhargaht where flag='1' and statusverifikasi ='0' and nomor='".$val2['nomor']."' limit 1";
			$arr3 = fetchData($str3);
			if(count($arr3)>0){
				$arrData[$val2['nopp']][$val2['kodebarang']]['flag']=1;
			}
		}
		
		// echo "<pre>";
		// print_r($arrData);
		
		if(isset($arrData)){
			foreach ($arrData as $nopp => $val) {
				foreach ($val as $kodebarang => $val){
					if($val['flag']==1){
						if($val['jumlah']>0){
							$jlhitem++;
						}
					}
				}
			}
		}
		$tab.="<table align=center><tr><td style='text-align:center'><a href='#' onclick=\"getDtPP()\">".$jlhitem."</a></td></tr></table>";
		
		echo $tab;
	break;
	
	case'getBarangPP':
		$where="";
		$whereverif="f.nomor in (select nomor from ".$dbname.".log_perintaanhargaht where statusverifikasi='0' and flag = '1')";
		if($schnopp!=''){
			$where.=" and a.nopp like '%".$schnopp."%'";
		}
		if($schjenis!=''){
			$where.=" and e.jenis='".$schjenis."'";
		}
		if($schklbrg!=''){
			$where.=" and e.kelompokbarang='".$schklbrg."'";
		}
		if($schkdbrg!=''){
			$where.=" and e.kodebarang='".$schkdbrg."'";
		}
		if($schunit!=''){
			$where.=" and c.nopp like '%".$schunit."'";	
		}
		
		
		$where.=" and b.unit in (".getOrgDetail(2).")";

		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')!=''){			
		// 	$where.=" and b.unit in (".getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator').")";
		// }
		
		// ada 2 ketentuan
		// 1. untuk pabrik dan bulking yang verifikasi hendra jaya
		// 2. kecuali diatas yg verifikasi purchaser masing masing
		// jika ketentuan point nomor 2 maka hanya munculkan PR yang mereka buat saja
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'],'verifikator')==getUnitByMgrPurc($_SESSION['standard']['userid'],'purchaserid')){
		// 	$where.=" and (a.karyawanid ='".$_SESSION['standard']['userid']."' or a.createdby ='".$_SESSION['standard']['userid']."')";
		// }
		
		$no=0;
		$tab='';
		$arrData=array();
		$str="select f.nomor,a.*,c.realisasi,c.satuankonversi,d.jumlahpesan,c.nokontrak,c.hasilkonversi, b.ket_balik from ".$dbname.".log_listverifikasi a 
			left join ".$dbname.".log_prapoht b on a.nopp=b.nopp 
			left join ".$dbname.".log_prapodt c on a.nopp=c.nopp and a.kodebarang = c.kodebarang 
			left join ".$dbname.".log_podt d on a.nopp=d.nopp and a.kodebarang = d.kodebarang 
			left join ".$dbname.".log_5masterbarang e on a.kodebarang = e.kodebarang 
			left join ".$dbname.".log_permintaanhargadt f on a.nopp=f.nopp and a.kodebarang = f.kodebarang 
			where ".$whereverif." and a.pemenang='0' ".$where." group by c.nopp, c.kodebarang,a.pemenang order by c.tgl_sdt asc,f.nomor asc,c.nopp asc,c.kodebarang asc";
	    //echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$countitem = 0;
		while($bar=$res->fetch()){
			$jumlah = 0;
			$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$optSat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
			if($bar['satuankonversi']=='' || is_null($bar['satuankonversi'])){
				$mySatuan = $optSat[$bar['kodebarang']];
				$jumlah = $bar['realisasi']; $xxx=4;

				if(($bar['jumlahpesan']=='')||is_null($bar['jumlahpesan'])||$bar['jumlahpesan']==0){
					$jumlah = $bar['realisasi']; $xxx=1;
				}elseif($bar['jumlahpesan']!=$bar['realisasi']) {
					$jumlah = intval($bar['realisasi'])-intval($bar['jumlahpesan']); $xxx=2;
				}else {
					$jumlah = $bar['realisasi']; $xxx=3;
				}
			}else{
				$mySatuan = $bar['satuankonversi'];
				$jumlah = $bar['hasilkonversi']; $xxx=5;				
			}
			
			if($jumlah>0){
				$arrData[$bar['nopp']][$bar['kodebarang']]['namabarang']=$optBarang[$bar['kodebarang']];
				$arrData[$bar['nopp']][$bar['kodebarang']]['satuan']=$mySatuan;
				$arrData[$bar['nopp']][$bar['kodebarang']]['jumlah']=$jumlah;
				@$lstPP[$bar['nopp']]=$bar['nopp'];
				@$lstBrg[$bar['kodebarang']]=$bar['kodebarang'];
				$countitem++;
			}
		}
		/* $mul=0;
		$noppList="";
		if (isset($lstPP)) {
			foreach($lstPP as $rw){
				if($mul==0){
					$noppList="'".$rw."'";
					$mul=1;
				}else{
					$noppList.=",'".$rw."'";
				}
			}
		}
		
		$mul=0;
		$brgList="";
		if (isset($lstBrg)) {
			foreach($lstBrg as $rw){
				if($mul==0){
					$brgList="'".$rw."'";
					$mul=1;
				}else{
					$brgList.=",'".$rw."'";
				}
			}
		} */
		

		$nomor = array();
		$where = "and kodebarang in ('".implode("','",$lstBrg)."')";
		$where .= "and nopp in ('".implode("','",$lstPP)."')";
	
		$str2 = "select * from ".$dbname.".log_permintaanhargadt where 1=1 ".$where." and flag='1' order by nomor";
		$arr2=fetchData($str2);
		foreach($arr2 as $val2){
			$nomor[$val2['nomor']]=$val2['nomor'];
		}
		$where .= "and nomor not in ('".implode("','",$nomor)."')";

		// $str3 = "select * from ".$dbname.".log_permintaanhargadt where 1=1 ".$where." and flag='1' order by nomor";
		// $arr3=fetchData($str3);
		// foreach($arr3 as $val2){
		// 	$nomor[$val2['nomor']]=$val2['nomor'];
		// }

		$where .= "and nomor not in ('".implode("','",$nomor)."')";
		$str2 = "select * from ".$dbname.".log_permintaanhargadt where 1=1 ".$where." order by nomor";
		$arr2=fetchData($str2);
		foreach($arr2 as $val2){
			// $str3 = "select * from ".$dbname.".log_perintaanhargaht where flag='1' and statusverifikasi ='0' and tolakrph=0 and nomor='".$val2['nomor']."' limit 1";
			$str3 = "select * from ".$dbname.".log_perintaanhargaht where flag='1' and statusverifikasi ='0' and nomor='".$val2['nomor']."' limit 1";
			$arr3=fetchData($str3);
			if(count($arr3)!=0){
				$arrData[$val2['nopp']][$val2['kodebarang']]['flag']=1;
				$arrData[$val2['nopp']][$val2['kodebarang']]['norph']=$arr3[0]['nomor'];
			}				
		}
		
		// echo"<pre>";
		// print_r($arrData);
		
		#listPpnya
		foreach ($arrData as $key => $val) {
			foreach ($val as $key2 => $bar) {
				if($bar['flag']==1){
					if($bar['jumlah']>0){
						$no++;
						$tab.="<tr class=rowcontent>
						<td style=width:30px align=center>".$no."</td>
						<td style='cursor:pointer;color:blue' title='Detail PR' id=nopplst_".$no." onclick=\"previewDetail('".$key."',event);\">".$key."</td>
						<td align=center id=kodebrg_".$no.">".$key2."</td>";
						if($bar['ket_balik']!=''){
							$tab.="<td style='background-color:#64FB76'>".$bar['namabarang']."<br>Become Out Standing : ".$bar['ket_balik']."</td>";
						}else{
							$tab.="<td>".$bar['namabarang']."</td>";
						}
						$tab.="<td align=right id=jumlah_".$no.">".number_format($bar['jumlah'])."</td>
						<td>".$bar['satuan']."</td>
						<td id=norph_".$no.">".$bar['norph']."</td>
						<td style='width:10px' align=center><input type=checkbox id=pilBrg_".$no." onclick=\"cekchklist('".$no."','".$countitem."')\" /></td>
						</tr>";
					}
				}
			}
		}
	

		if($no>0){
			$tab.="<tr><td colspan=7 align=center>
				<button class=mybutton onclick=lanjutAdd() >".$_SESSION['lang']['lanjut']."</button>
			</td></tr>";
		}
		
		$optNmUnit=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","char_length(kodeorganisasi)=4");
        
		$optunit="<option value=''>".$_SESSION['lang']['all']."</option>"; 
		$str="select distinct right(nopp,4) as kodeunit from ".$dbname.".log_permintaanhargadt ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($optNmUnit[$bar['kodeunit']]!=''){
				if($schunit==$bar['kodeunit']){
					$optunit.="<option value='".$bar['kodeunit']."' selected>".$bar['kodeunit']."-".$optNmUnit[$bar['kodeunit']]."</option>";	
				}else{
					$optunit.="<option value='".$bar['kodeunit']."'>".$bar['kodeunit']."-".$optNmUnit[$bar['kodeunit']]."</option>";		
				}
				
			}
		}
		 
		echo $tab."###".$optunit."###".$kdPt;
	break;
	
	case'cekBarang':
		$tab.="";
		$arrPurchaser = array();
		$arrSupplier = array();
		$arrStatus = array();
		$arrnamasup=array();
		$arrSupplier2=array();
		
		$AllKolom = 0;
		
		for($i=0;$i<count($_POST['kdbrg']);$i++)
		{
			$str="select * from ".$dbname.".log_listverifikasi where nopp='".$_POST['lstnopp'][$i]."' and kodebarang='".$_POST['kdbrg'][$i]."' and status=1 and pemenang='0'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$arrPurchaser[$bar['karyawanid']] = $bar['karyawanid'];
				$arrStatus[$bar['karyawanid']][$bar['nopp']][$bar['kodebarang']]['status'] = $bar['status'];
				$arrStatus[$bar['karyawanid']][$bar['nopp']][$bar['kodebarang']]['skip'] = $bar['skip'];
			}
			
			
			$strx="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$_POST['kdbrg'][$i]."'";
			$resx=fetchdata($strx);
			$arrdtbarang[$_POST['kdbrg'][$i]]['namabarang']=$resx[0]['namabarang'];
			$arrdtbarang[$_POST['kdbrg'][$i]]['satuan']=$resx[0]['satuan'];
			
			$strx="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$_POST['lstnopp'][$i]."' and kodebarang='".$_POST['kdbrg'][$i]."'";
			$resx=fetchdata($strx);
			if($resx[0]['satuankonversi']=='' || is_null($resx[0]['satuankonversi'])){}else{
				$arrdtbarang[$_POST['kdbrg'][$i]]['satuan']=$resx[0]['satuankonversi'];				
			}
			
			$str="select a.ongkir,a.kodebarang,a.nopp,a.jumlah,a.harga,a.score,a.factor,a.nourut,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,b.flag,b.nilai1s,b.nilai2s,b.nilai3s,b.nilai4s,b.nilai5s,b.nilai1f,b.nilai2f,b.nilai3f,b.nilai4f,b.nilai5f, b.pph, b.pph22, b.subtotal, b.diskonpersen, b.pphfinal from ".$dbname.".log_permintaanhargadt a 
			left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
			where a.kodebarang='".$_POST['kdbrg'][$i]."' and a.nopp='".$_POST['lstnopp'][$i]."' and b.nomor not in (select nomor from ".$dbname.".log_permintaanhargadt where flag='1' and kodebarang='".$_POST['kdbrg'][$i]."' and nopp='".$_POST['lstnopp'][$i]."')";
			// exit("warning:" .$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				if($bar['pph22'] > 0){
					$pph_ = $bar['pph22'];
				}else{
					$pph_ = $bar['pph'];
				}

				$dph_Supp[$bar['supplierid']]=$bar['nomor'];
				$optnmsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['supplierid']."'");
				$arrnamasup[$bar['supplierid']]=$optnmsup[$bar['supplierid']];
				
				$arrSupplier2[$bar['purchaser']][$bar['supplierid']] = $bar['supplierid'];
				$arrSupplier[$bar['supplierid']] = $bar['supplierid'];
				
				$optMrk = makeOption($dbname,'log_5merkbaranght','idmerk,merk',"idmerk='".$bar['merk']."'");

				if($bar['pphfinal'] > 0){
					$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['supplierid']]['pph'] = $bar['pphfinal'];
				}else{
					$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['supplierid']]['pph'] = ($pph_/100) * ($bar['subtotal'] - $bar['nilaidiskon']);
				}
				
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['merk'] = $optMrk[$bar['merk']];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']]['status'] = $bar['flag'];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['jumlah'] = $bar['jumlah'];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['ongkir'] = ($bar['ongkir']*$bar['jumlah']);
				// $arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['harga'] = ($bar['diskonpersen']==0?$bar['harga']:($bar['harga'] - ($bar['harga']*($bar['diskonpersen']/100))));
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['harga'] = ($bar['diskonpersen']==0?$bar['harga']:($bar['harga'] - ($bar['diskonpersen']*$bar['harga']/100)));
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['nodph'] = $bar['nomor'];
				$arrStatus[$bar['purchaser']][$bar['nopp']][$bar['kodebarang']][$bar['supplierid']]['nourut'] = $bar['nourut'];
				
				$expnopp = explode('/',$bar['nopp']);
				$strx="select hargasatuan,tanggal from ".$dbname.".log_5hargaterakhir where kodebarang='".$bar['kodebarang']."' and unit='".$expnopp[4]."' and status='1'";
				$resx=fetchdata($strx);
				$arrdtbarang[$bar['kodebarang']]['qty']=$bar['jumlah'];
				$arrdtbarang[$bar['kodebarang']]['lastprice']=($resx[0]['hargasatuan']==''?'-':$resx[0]['hargasatuan']);
				$arrdtbarang[$bar['kodebarang']]['lastpricetgl']=($resx[0]['tanggal']==''?'-':$resx[0]['tanggal']);
				$arrdtbarang[$bar['kodebarang']]['factor']=$bar['factor'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['harga']=$bar['harga'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['score']=$bar['score'];
				$arrbarang[$bar['supplierid']][$bar['kodebarang']]['total']=$bar['harga']*$bar['jumlah'];
				
				$arrnilais[$bar['supplierid']]['nilai1s']=$bar['nilai1s'];
				$arrnilais[$bar['supplierid']]['nilai2s']=$bar['nilai2s'];
				$arrnilais[$bar['supplierid']]['nilai3s']=$bar['nilai3s'];
				$arrnilais[$bar['supplierid']]['nilai4s']=$bar['nilai4s'];
				$arrnilais[$bar['supplierid']]['nilai5s']=$bar['nilai5s'];
				$arrongkir[$bar['supplierid']]=$bar['totalongkir'];
				
				$nilai1f=$bar['nilai1f'];
				$nilai2f=$bar['nilai2f'];
				$nilai3f=$bar['nilai3f'];
				$nilai4f=$bar['nilai4f'];
				$nilai5f=$bar['nilai5f'];
			}
		}
		
		$tab.="<fieldset><legend>List Data</legend>";
		$countsup = count($arrSupplier);
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr style='text-align:center;font-weight:bold'>
				<td rowspan=2>Evaluation Parameter</td>
				<td rowspan=2>Description</td>
				<td rowspan=2>Unit</td>
				<td rowspan=2>Uom</td>
				<td colspan='".$countsup."'>Summary of Information</td>
				<td rowspan=2 style='min-width:80px'>LAST PRICE</td>
				<td colspan='".$countsup."'>Score (1 - 5, 5 for the Best)</td>
				<td rowspan=2>Weighted Factor</td>
				<td colspan='".$countsup."'>Weighted Score</td>
			</tr>
			<tr style='text-align:center;font-weight:bold'>";
			foreach($arrnamasup as $val){
				$tab.="<td>".$val."</td>";
			}
			foreach($arrnamasup as $val){
				$tab.="<td>".$val."</td>";
			}
			foreach($arrnamasup as $val){
				$tab.="<td>".$val."</td>";
			}
			$tab.="<tr>
			</thead>
			<body>";
			
			$arrkodebarang = $_POST['kdbrg'];
			// asort($arrkodebarang);
			
			## PRICE
			$arrsubhasil=array();
			$arrhasil=array();
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+(1*$countsup))."'><b>I. Price :</b></td>";
				
				$no=0;
				foreach($arrkodebarang as $val){
					$no++;
					if($no==1){
						$browspan = (count($arrkodebarang) * 3) + 2;
						foreach($arrSupplier as $valx){
							// $tab.="<td style='text-align:center'>".$arrnilais[$valx]['nilai1s']."</td>";
							$tab.="<td rowspan='".$browspan."' style='text-align:center'>".$arrnilais[$valx]['nilai1s']."</td>";
						}
						$tab.="<td rowspan='".$browspan."' style='text-align:center'>".hidezerodecimal($nilai1f,2)." %</td>";
						foreach($arrSupplier as $valx){
							$hasil=$arrnilais[$valx]['nilai1s'] * ($nilai1f/100);
							// $tab.="<td style='text-align:center'>".$hasil."</td>";
							$tab.="<td rowspan='".$browspan."' style='text-align:center'>".$hasil."</td>";
							$arrsubhasil[$valx]=$hasil;
						}
					}
				}
				
			$tab.="</tr>";
			$no=0;
			$arrtotrpsup = array();
			$ongkir=0;
			foreach($arrkodebarang as $val){
				$no++;
				$tab.="<tr class='rowcontent' style='text-align:center'>
					<td>".$no."</td>
					<td style='text-align:left'>".$arrdtbarang[$val]['namabarang']."</td>
					<td>".hidezerodecimal($arrdtbarang[$val]['qty'],2)."</td>
					<td>".$arrdtbarang[$val]['satuan']."</td>";
					foreach($arrSupplier as $valx){
						$tab.="<td style='text-align:center'>".number_format($arrbarang[$valx][$val]['harga'])."</td>";
					}
					$tab.="<td>".hidezerodecimal($arrdtbarang[$val]['lastprice'],2)."</td>";
					
					// $tab.="<td>".hidezerodecimal($nilai1f,2)."</td>";
				$tab.="</tr>";
				$tab.="<tr class='rowcontent' style='text-align:center'>
					<td colspan=4></td>";
					foreach($arrSupplier as $valx){
						$tab.="<td style='text-align:center;font-weight:bold'>".number_format($arrbarang[$valx][$val]['total'])."</td>";
						$arrtotrpsup[$valx]+= $arrbarang[$valx][$val]['total'];
						$ongkir+=$arrongkir[$valx];
					}
					$tab.="<td>".($arrdtbarang[$val]['lastpricetgl']=='-'?'-':tanggalnormal($arrdtbarang[$val]['lastpricetgl']))."</td>";
				$tab.="</tr>";
				
				if(count($arrkodebarang)==$no){
					if($ongkir>0){
						$tab.="<tr class='rowcontent'>";
						$tab.="<td colspan='".(4)."'><b>".$_SESSION['lang']['ongkoskirim']."</b></td>";
						foreach($arrSupplier as $valx){
							$tab.="<td align=center><b>".hidezerodecimal($arrongkir[$valx])."</b></td>";
						}
						$tab.="<td></td>";
						$tab.="</tr>";
					}else{
						$tab.="<tr class='rowcontent'><td colspan='".(5+(1*$countsup))."'>&nbsp;</td></tr>";
					}
				}else{
					$tab.="<tr class='rowcontent'><td colspan='".(5+(1*$countsup))."'>&nbsp;</td></tr>";					
				}
			}
			
			// ## PPN
			// $tab.="<tr class='rowcontent'>
				// <td colspan='4' style='text-align:right'>PPN&nbsp;</td>";
				// $vhppn=array();
				// foreach($arrSupplier as $val){
					// $str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$val."' and noakun='1170111' limit 1";
					// $res=fetchdata($str);
					// $vpppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);
					// $vhppn[$val] = ($vpppn/100) * $arrtotrpsup[$val];
					// $tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($vhppn[$val])."</td>";
				// }
				
			// $tab.="<td></td>
			// </tr>";
			
			## TOTAL
			$tab.="<tr class='rowcontent' style='background-color:lightgreen'>
				<td colspan='4' style='text-align:center;font-weight:bold'>T O T A L</td>";
				foreach($arrSupplier as $val){
					// $vtothas = $arrtotrpsup[$val] + $vhppn[$val];
					$vtothas = ($arrtotrpsup[$val]+$arrongkir[$val]);
					$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($vtothas)."</td>";
				}
				
			$tab.="<td></td>
			</tr>";
			
			## Availability
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>II. Availability :</b></td>";
				foreach($arrSupplier as $val){
					$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai2s']."</td>";
				}
				$tab.="<td style='text-align:center'>".$nilai2f." %</td>";
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai2s'] * ($nilai2f/100);
					$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Quality/ Performance/ Integrity
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>III. Quality/ Performance/ Integrity :</b></td>";
				foreach($arrSupplier as $val){
					$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai3s']."</td>";
				}
				$tab.="<td style='text-align:center'>".$nilai3f." %</td>";
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai3s'] * ($nilai3f/100);
					$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Service
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>IV. Service :</b></td>";
				foreach($arrSupplier as $val){
					$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai4s']."</td>";
				}
				$tab.="<td style='text-align:center'>".$nilai4f." %</td>";
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai4s'] * ($nilai4f/100);
					$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			## Other Concerns (payment scheme, etc.)
			$tab.="<tr class='rowcontent'>
				<td colspan='".(5+($countsup))."'><b>V. Other Concerns (payment scheme, etc.) :</b></td>";
				foreach($arrSupplier as $val){
					$tab.="<td style='text-align:center'>".$arrnilais[$val]['nilai5s']."</td>";
				}
				$tab.="<td style='text-align:center'>".$nilai5f." %</td>";
				foreach($arrSupplier as $val){
					$hasil = $arrnilais[$val]['nilai5s'] * ($nilai5f/100);
					$tab.="<td style='text-align:center'>".hidezerodecimal($hasil,2)."</td>";
					$arrhasil[$val]+=$hasil;
				}
			$tab.="</tr>";
			
			$arrwin = array();
			$tab.="<tr class='rowcontent' style='background-color:lightgreen'>
				<td colspan='".(5+($countsup*2))."'></td>
				<td></td>";
				foreach($arrSupplier as $val){
					$hasil = $arrsubhasil[$val]+$arrhasil[$val];
					$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($hasil,2)."</td>";
					$arrwin[$val] = $hasil;
				}
			$tab.="</tr>";
			
			## PEMENANG TENDER
			$tab.="<tr class='rowcontent'><td colspan='".(6+(3*$countsup))."'>&nbsp;</td></tr>";
			$no=0;
			arsort($arrwin);
			foreach($arrwin as $key=>$val){
				$no++;
				$tab.="<tr class='rowcontent' style='font-weight:bold'>
					<td colspan='".(6+($countsup*2))."' style='text-align:right'>Rekomendasi Tender ".$no."</td>
					<td colspan='".$countsup."'>".$arrnamasup[$key]."</td>
				</tr>";
			}
			
			$tab.="</tbody>
		</table>";
		
		$tab.="<br><hr><br>";
		
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center;font-weight:bold'>
				<td rowspan=3>".$_SESSION['lang']['nourut']."</td>
				<td rowspan=3>No. PR/SR</td>
				<td rowspan=3>".$_SESSION['lang']['kodebarang']."</td>
				<td rowspan=3>".$_SESSION['lang']['namabarang']."</td>";
				$no = 0;
				foreach($arrPurchaser as $val)
				{
					$no++;
					$jlhkolom = @count($arrSupplier2[$val]);
					if($jlhkolom==0)
					{
						$jlhkolom = 1;
					}
					$optNmKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val."'");
					$tab.="<td colspan='".(($jlhkolom*6)+1)."' style='text-align:center' id=purchaser_".$val.">".$optNmKaryawan[$val]."</td>";
					if($no==count($arrPurchaser))
					{
						$tab.="</tr>";
					}
					$AllKolom = $AllKolom + ($jlhkolom*6) + 1;
				}
			$tab.="<tr>";
			foreach($arrPurchaser as $val)
			{
				$no=0;
				$jlhkolom = count(@$arrSupplier2[$val]);
				if($jlhkolom==0)
				{
					$tab.="<td colspan=6>&nbsp;</td>";
					next;
				}
				else
				{
					$nosup = 0;
					foreach($arrSupplier as $val2)
					{
						if($val2==$arrSupplier2[$val][$val2])
						{
							$nosup++;
							$optNmSupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$val2."'");
							// $tab.="<td style='text-align:center' colspan=5 id=supplier_".$val2.">".$optNmSupplier[$val2]."
							// $tab.="<td style='text-align:center;cursor:pointer;' colspan=5 id=supplier_".$val2." onclick=\"previewlink('".$dph_Supp[$val2]."', '', 'Detail',event)\">
							$tab.="<td style='text-align:center;cursor:pointer;' colspan=6 id=supplier_".$val2." >
								".$optNmSupplier[$val2]." <br>
								<button id='btn' class=mybutton onclick=\"previewlink('".$dph_Supp[$val2]."', '', 'Detail',event)\">Detail Perbandingan</button>
								<button id='btn' class=mybutton onclick=\"DetailAging('".$val2."',event)\">Detail Aging</button>
							</td>";
							
							if($jlhkolom==$nosup){
								$tab.="<td rowspan=2 style='text-align:center'>".$_SESSION['lang']['action']."</td>";
							}
						}
					}
				}
			}
			$tab.="</tr>";
			
			$tab.="<tr>";
			foreach($arrPurchaser as $val)
			{
				$no=0;
				$jlhkolom = count(@$arrSupplier2[$val]);
				if($jlhkolom==0)
				{
					$tab.="<td colspan=6>&nbsp;</td>";
					next;
				}
				else
				{
					foreach($arrSupplier as $val2)
					{
						if($val2==$arrSupplier2[$val][$val2])
						{
							$tab.="<td style='text-align:center'>PPH</td>";
							$tab.="<td style='text-align:center'>Qty</td>";
							$tab.="<td style='text-align:center'>Harga Satuan</td>";
							$tab.="<td style='text-align:center'>Subtotal</td>";
							$tab.="<td style='text-align:center'>Merk</td>";
							$tab.="<td style='text-align:center'></td>";
						}
					}
				}
			}
			$tab.="</tr>
			</thead>
			<tbody>";
		
			$no=0;
			for($i=0;$i<count($_POST['kdbrg']);$i++)
			{
				$optNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$_POST['kdbrg'][$i]."'");
				
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td style='text-align:center;cursor:pointer;color:blue' title='Detail Perbandingan Harga' id=nopp_".$no." onclick=\"previewlinkpp3('".$_POST['lstnopp'][$i]."','".$_POST['kdbrg'][$i]."','Detail Riwayat Perbandingan Harga',event);\">".$_POST['lstnopp'][$i]."</td>";
				$tab.="<td style='text-align:center' id=kodebarang_".$no.">".$_POST['kdbrg'][$i]."</td>";
				$tab.="<td>".$optNmBrg[$_POST['kdbrg'][$i]]." </td>";
				
				foreach($arrPurchaser as $val)
				{
					$jlhkolom = count($arrSupplier2[$val]);
					// rowspan
					if($jlhkolom==0)
					{
						$tab .= "<td style='text-align:right;font-weight:bold' rowspan=".count($_POST['kdbrg']).">
						</td>";
						next;
					}else{
						foreach($arrSupplier as $val2)
						{
							if($val2==$arrSupplier2[$val][$val2])
							{
								if(!isset($rowspan[$val])) {
									$tab .= "<td style='text-align:right;font-weight:bold' rowspan=".count($_POST['kdbrg']).">
										".$arrStatus[$val][$_POST['lstnopp'][$i]][$val2]['pph']."
									</td>";
									$rowspan[$val] = true;
								}
							}
						}
					}
					
					if($jlhkolom==0)
					{
						if($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['skip']=='1')
						{
							$tab.="<td style='text-align:center;font-weight:bold' colspan=6>SKIP</td>";
						}
						else if($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['status']=='0')
						{
							$tab.="<td style='text-align:center;font-weight:bold' colspan=6>BELUM DIAJUKAN</td>";
						}
						else
						{
							$tab.="<td style='text-align:center;font-weight:bold' colspan=6></td>";
						}
						next;
					}
					else
					{
						$nosup=0;
						foreach($arrSupplier as $val2)
						{
							if($val2==$arrSupplier2[$val][$val2])
							{
								if((!isset($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['skip']))||(!isset($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['status'])))
								{
									$tab.="<td style='text-align:center;font-weight:bold' colspan=6></td>";
									next;
								}
								else
								{
									if(isset($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['jumlah']))
									{
										if($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['skip']==1)
										{
											$tab.="<td style='text-align:center;font-weight:bold' colspan=6>SKIP</td>";
										}
										else if($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]]['status']==0)
										{
											$tab.="<td style='text-align:center;font-weight:bold' colspan=6>BELUM DIAJUKAN</td>";
										}
										else
										{
											$subtotal = ($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['jumlah'] * $arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['harga']) + $arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['ongkir']; 
											$tab.="<td style='text-align:center;background-color:#4ddbff'>
												".hidezerodecimal($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['jumlah'],2)."
											</td>";
											$tab.="<td style='text-align:right'>
												".hidezerodecimal($arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['harga'],2)."
											</td>";
											$tab.="<td style='text-align:right;font-weight:bold'>
												".hidezerodecimal($subtotal,2)."
											</td>";
											$tab.="<td style='text-align:left;'>
												".$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['merk']."
											</td>";
											// $tab.="<td style='text-align:right;font-weight:bold' rowspan=".count($_POST['kdbrg']).">
											// tes
											// </td>";
											$tab.="<td style='text-align:center'>
												<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_perintaanhargaht','".$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['nodph'].",".$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['nourut']."','','log_slave_print_permintaan_penawaran_v2',event);\" style='display:none'>
												<input type='radio' value='".$val."####".$val2."####".$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['nodph']."####".$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['nourut']."####".$_POST['kdbrg'][$i]."' id='chk_".$_POST['kdbrg'][$i]."' name='chk_".$_POST['kdbrg'][$i]."'>
											</td>";
											$norph=$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['nodph'];
											$nosup++;
											
											$arrtotqty[$val][$val2]+=$arrStatus[$val][$_POST['lstnopp'][$i]][$_POST['kdbrg'][$i]][$val2]['jumlah'];
											$arrtotharga[$val][$val2]+=$subtotal;
										}
									}
									else
									{
										$tab.="<td style='text-align:center;font-weight:bold' colspan=6></td>";
									}
								}
							}
						}
						if($nosup>0){
							$tab.="<td style='text-align:center;font-weight:bold'>
								<button id='btntolak' class=mybutton onclick=\"tolakrph('".$norph."',event)\">".$_SESSION['lang']['tolak']."</button>
							</td>";
						}else{
							$tab.="<td style='text-align:center;font-weight:bold'></td>";
						}
					}
				}
				$tab.="</tr>";
			}
		
		$tab.="<tr class='rowcontent' style='background-color:lightgreen'>
			<td colspan='4' style='text-align:center;font-weight:bold'>T O T A L</td>";
			foreach($arrPurchaser as $val){
				$jlhkolom = count($arrSupplier2[$val]);
				
				if($jlhkolom==0){
					$tab.="<td style='text-align:center;font-weight:bold' colspan=6></td>";
				}else{
					$nosup=0;
					foreach($arrSupplier as $val2){
						$tab.="<td style='text-align:center;font-weight:bold'></td>";
						$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($arrtotqty[$val][$val2])."</td>";
						$tab.="<td style='text-align:center;font-weight:bold'></td>";
						$tab.="<td style='text-align:center;font-weight:bold'>".hidezerodecimal($arrtotharga[$val][$val2])."</td>";
						$tab.="<td style='text-align:center;font-weight:bold'></td>";
						$tab.="<td style='text-align:center;font-weight:bold'></td>";
					}
				}
			}
			$tab.="<td style='text-align:center;font-weight:bold'></td>
		</tr>
			<tr>
				<td colspan='".(4+$AllKolom)."' style='text-align:center;font-weight:bold'>
					<!--<button id='btnsaveall' class=mybutton onclick=saveall('".($no+1)."')>".$_SESSION['lang']['save']."</button>-->
					<button id='btnsaveall' class=mybutton onclick=submitpemenang('".$norph."','".($no+1)."',event)>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
			</tbody>
		</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	// joki

	case'submitpemenang':
		$norfq=checkPostGet('norfq','');
		$rates=checkPostGet('rates','');
	
		// $str="select unit from ".$dbname.".log_perintaanhargaht where nomor='".$norfq."'";
		// $res=fetchdata($str);
		// $unit=$res[0]['unit'];
		$dt=explode("/",$norfq);
		$unit = $dt[4];
		$induk_unit=makeOption($dbname,"organisasi","kodeorganisasi,induk");

		// cek nilai
		$str="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='NILAIVPOAP' ";
		$res=fetchdata($str);
		$nilai_appHO=$res[0]['nilai'];
		
		$str="select tipepp from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$tipepp=$res[0]['tipepp'];

		// cek nilai apakah lebih dari parameter aplikasi
		$nilai_subtotal_n=0;
		foreach($rates as $val){
			$rates_n = explode("####",$val);
			$supplier_n=$rates_n[1];
			$nodph_n=$rates_n[2];
			$nourut_n=$rates_n[3];
			$kodebarang_n=$rates_n[4];
			
			// ambil nilai subtotal
			$str="select (harga*jumlah) as subtotal from ".$dbname.".log_permintaanhargadt where nourut = '".$nourut_n."' and nomor = '".$nodph_n."' and kodebarang = '".$kodebarang_n."' ";
			$res=fetchdata($str);
			$nilai_subtotal_n+=$res[0]['subtotal'];
			
		}

		if($nilai_subtotal_n >= $nilai_appHO and $unit=='PPPE'){
			// ambil unit HO dari PT tersebut
			$str="select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$induk_unit[$unit]."'  ";
			// $str="select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$unit."'  ";
			$res=fetchdata($str);
			$unit_HO=$res[0]['kodeorganisasi'];
			$countApp = getCountApproval($jenisApp, $unit_HO);
			$namaorg_unit=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");	
			if($countApp <= 0){
				exit("warning : Untuk Unit ".$namaorg_unit[$unit_HO]." jenis persetujuan RFQ belum di setupkan.... ");
			}
	
			$i = 0;
			$arrList = listNextApprove($i, $jenisApp, $unit_HO, $nilai_subtotal_n);

			if(count($arrList) == 0){
				exit("warning : Belum ada setup approval Unit ".$namaorg_unit[$unit_HO]." dengan range nilai ".number_format($nilai_subtotal_n)." ");
			}else{
				if($arrList[0]['level'] != '1'){
					exit("warning : Belum ada setup approval Unit ".$namaorg_unit[$unit_HO]." dengan range nilai ".number_format($nilai_subtotal_n)." Pada Level 1");
				}
			}
		}else{
			$countApp = getCountApproval($jenisApp, $unit);
			$namaorg_unit=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");
	
			if($countApp <= 0){
				exit("warning : Untuk Unit ".$namaorg_unit[$unit]." jenis persetujuan RFQ belum di setupkan... ");
			}
	
			// $i = 1;
			// $arrList = listApprove($i, $jenisApp, $unit);


			$i = 0;
			$arrList = listNextApprove($i, $jenisApp, $unit, $nilai_subtotal_n);

			if(count($arrList) == 0){
				exit("warning : Belum ada setup approval Unit ".$namaorg_unit[$unit_HO]." dengan range nilai ".number_format($nilai_subtotal_n)." ");
			}else{
				if($arrList[0]['level'] != '1'){
					exit("warning : Belum ada setup approval Unit ".$namaorg_unit[$unit_HO]." dengan range nilai ".number_format($nilai_subtotal_n)." Pada Level 1");
				}
			}

		}

		// exit("warning : ".$induk_unit[$unit]." ");

		// $countApp = getCountApproval($jenisApp, $unit);
		$namaorg_unit=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");

		if($countApp <= 0){
			exit("warning : Untuk Unit ".$namaorg_unit[$unit]." jenis persetujuan RFQ belum di setupkan..... ");
		}

		// $i = 1;
		// $arrList = listApprove($i, $jenisApp, $unit);
		$arrDetail = detailApprove($i, $norfq, $jenisApp);
		$optpersetujuan="";
		foreach($arrList as $key => $val){
			$optkaryawan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
		}
		
		$hsltender="";
		for($i=0;$i<count($_POST['kdbrg']);$i++){
			$rates = explode("####",$_POST['rates'][$i]);
			$rates1=$rates[4];
			$nourut=$rates[3];
			$kodebarang = $_POST['kdbrg'][$i];
			$nopp = $_POST['nopp'][$i];
			
			if($kodebarang==$rates1){
				if($i==0){
					$hsltender.=$kodebarang."====".$nourut."====".$nopp;
				}else{
					$hsltender.="####".$kodebarang."====".$nourut."====".$nopp;
				}
			}
			
		}
		
		$tab.="<input type='hidden' id='level' value='1'>";
		$tab.="<input type='hidden' id='norfq' value='".$norfq."'>";
		$tab.="<input type='hidden' id='pemenangitem' value='".$hsltender."'>";
		
		$tab.="<table cellpadding=5 width=100%>
			<tr>
				<td>".$_SESSION['lang']['kepada']."</td>
				<td>:</td>
				<td>
					<select id=\"karyawanid\" name=\"karyawanid\">". $optkaryawan."</select>
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['alasan']."</td>
				<td style='vertical-align:top'>:</td>
				<td style='vertical-align:top'>
					<textarea id='komentarmenang'></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"ajukan()\">".$_SESSION['lang']['diajukan']."</button>
					<button class=mybutton onclick=\"alertify.popup2().close();\">".$_SESSION['lang']['cancel']."</button>
				</td>
			</tr>
		</table>";
		
		echo $tab;
	break;

		
	case'ajukan':
		try {
			$owlPDO->beginTransaction();
			
			$norfq=checkPostGet('norfq','');
			$level=checkPostGet('level','');
			$karyawanid=checkPostGet('karyawanid','');
			$pemenangitem=checkPostGet('pemenangitem','');
			$komentarmenang=checkPostGet('komentarmenang','');

			if($norfq == ''){
				throw new PDOException("Notransaksi Tidak boleh kosong...");
			}
			
			if($karyawanid==''){
				throw new PDOException("Silahkan hubungi administrator, matriks approval belum dibuat.");
			}
			
			if($komentarmenang==''){
				throw new PDOException("Komentar harus diisi.");
			}

			$str="update ".$dbname.".log_permintaanhargadt set score='0' where nomor='".$norfq."'";
			$owlPDO->exec($str);

			// delete insert approval
			$str="delete from ".$dbname.".approval where notransaksi='".$norfq."'";
			$owlPDO->exec($str);
			
			$wktskrg=date('Y-m-d H:i:s');
			$str="insert into ".$dbname.".approval (nourut,notransaksi,jenispersetujuan,level,karyawanid,status,komentar,keterangan,tanggal) values ('','".$norfq."','".$jenisApp."','".$level."','".$karyawanid."','0','','','')";
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".log_perintaanhargaht set statusverifikasi='1', catatanmenang='".$komentarmenang."', picverifikasi='".$_SESSION['standard']['userid']."', waktuverifikasi='".$wktskrg."', tolakrph='0' where nomor='".$norfq."'";
			$owlPDO->exec($str);
			
			$rates=explode("####",$pemenangitem);
			foreach($rates as $val){
				$expval=explode("====",$val);
				$kodebarang=$expval[0];
				$nourut=$expval[1];
				$nopp=$expval[2];
				$str="update ".$dbname.".log_permintaanhargadt set score='1' where nomor='".$norfq."' and kodebarang='".$kodebarang."' and nourut='".$nourut."' and nopp='".$nopp."'";
				$owlPDO->exec($str);
			}
			
			// throw new PDOException("err");
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	


	case'DetailAging':
		$supplier=checkPostGet('supplier','');
		$stream="";
		$stream.="<table class=sortable ".$stylekolom." cellspacing=1 cellpadding=3>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<th align=center>Nomor</th>";
				$stream.="<th align=center>Tanggal Terima Dokumen</th>";
				$stream.="<th align=center>No Invoice</th>";
				$stream.="<th align=center>Supplier</th>";
				$stream.="<th align=center>Jenis Supplier</th>";
				$stream.="<th align=center style='display:none'>Jenis Transaksi</th>";
				// $stream.="<th align=center>Tanggal PO</th>";
				$stream.="<th align=center>No Dokumen</th>";
				// $stream.="<th align=center>Syarat Bayar</th>";
				$stream.="<th align=center>Nilai AP</th>";
				// $stream.="<th align=center>PPN Masukan</th>";
				// $stream.="<th align=center>PPh</th>";
				// $stream.="<th align=center>Total</th>";
				$stream.="<th align=center>Tanggal Jatuh Tempo</th>";
				// $stream.="<th align=center>".$_SESSION['lang']['kasbank']."</th>";
				// $stream.="<th align=center>No. Transaksi ".$_SESSION['lang']['kasbank']."</th>";
				// $stream.="<th align=center>No. Voucher ".$_SESSION['lang']['kasbank']."</th>";
				// $stream.="<th align=center>".$_SESSION['lang']['sisa']."</th>";
				
				$stream.="<th align=center>Sudah Jatuh Tempo</th>";
				$stream.="<th align=center>1-14 Hari</th>";
				$stream.="<th align=center>15-30 Hari</th>";
				$stream.="<th align=center>31-60 Hari</th>";
				$stream.="<th align=center>61-90 Hari</th>";
				$stream.="<th align=center>90- Hari</th>";
				$stream.="<th align=center>Jumlah Aging</th>";
			$stream.="<tr>";			
			
			$stream.="</thead>";
			$stream.="<tbody>";

			$str="select noakun,sum(jumlah) as jumlah,sum(debet) as debet,sum(kredit) as kredit,nodok,kodesupplier,tanggal,substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt_vw where 1=1 and kodesupplier='".$supplier."' group by nodok having jumlah!=0";
			$res=fetchdata($str);
			foreach($res as $bar) {
				$arrnodok[$bar['nodok']]=$bar['nodok'];
				$dtnilaijurnal[$bar['nodok']]=$bar['jumlah'];
			}
			
			// List nodok yang lebih aktual dari tagihan. 
			if ($param['tipeinvoice']!='') {
				$str="select * from ".$dbname.".keu_tagihanht where nopo  in ('".implode("','",$arrnodok)."')";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrbaru[$bar['nopo']]=$bar['nopo'];
				}
				$arrnodok = $arrbaru;
			}

			$str="select * from ".$dbname.".keu_tagihanht where nopo  in ('".implode("','",$arrnodok)."')";
			$res=fetchdata($str);
			foreach($res as $bar) {
				$n=0;
				$arrnoinvoice[$bar['noinvoice']]=$bar['noinvoice'];
				$arrkodesupplier[$bar['kodesupplier']]=$bar['kodesupplier'];
				$lsnoinvoice[$bar['nopo']][$bar['noinvoice']]=$bar['noinvoice'];
				$dttglinvoice[$bar['noinvoice']]=$bar['tanggal'];
				$dtkodesupplier[$bar['noinvoice']]=$bar['kodesupplier'];
				$dtnpwp[$bar['noinvoice']]=$bar['npwp'];
				$dttipeinvoice[$bar['noinvoice']]=$bar['tipeinvoice'];
				$dtnilaiinvoice[$bar['noinvoice']]=$bar['nilaiinvoice'];
				$dtnilaidpp[$bar['noinvoice']]=$bar['nilaiinvoice'];
				$dtjatuhtempo[$bar['noinvoice']]=$bar['jatuhtempo'];
				$arrdata[$bar['nopo']][$bar['noinvoice']] = $bar['noinvoice'];

				// array pembalik dok
				$invdok[$bar['noinvoice']]=$bar['nopo'];
			}
			$str="select * from ".$dbname.".log_poht where nopo  in ('".implode("','",$arrnodok)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dttglnodok[$bar['nopo']]=$bar['tanggal'];
				$dtsyaratbayar[$bar['nopo']]=strval($bar['syaratbayar']);
			}
			$nmSyaratBayar=makeOption($dbname,"log_5syaratbayar","kode,keterangan");

			// $str="select a.supplierid, a.namasupplier, b.deskripsisub from ".$dbname.".log_5supplier a left join log_5supsubkelompok b on a.supplierid=b.supplierid where a.supplierid  in ('".implode("','",$arrkodesupplier)."')";
			$str="select a.supplierid, a.namasupplier from ".$dbname.".log_5supplier a  where a.supplierid  in ('".implode("','",$arrkodesupplier)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dtnamasupplier[$bar['supplierid']]=$bar['namasupplier'];
				// $dtjenissupplier[$bar['supplierid']]=$bar['deskripsisub'];
			}
			
			$str="select * from ".$dbname.".keu_tagihandt where noinvoice  in ('".implode("','",$arrnoinvoice)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				if(substr($bar['noakun'],0,3)=='117'){ // ppn
					$dtnilaippn[$bar['noinvoice']]+=$bar['nilai'];
				}
				if(substr($bar['noakun'],0,3)=='213'){ // ppn
					$dtnilaipph[$bar['noinvoice']]+=($bar['nilai'])*(-1);
				}
			}
			#= dari invoice
			// $str="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$arrnoinvoice)."')";
			$str="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$arrnoinvoice)."') ";
			if(count($arrnoinvoice) < 1){
				$stream.="<tr class=rowcontent><td align=center colspan=15>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{

				// echo "<pre>"; print_r($arrnoinvoice);
				// echo "<pre>"; print_r($arrnodok);
				// echo "<pre>"; print_r($arrdata);
				$res=fetchdata($str);
				foreach($res as $bar){
					if($bar['keterangan1']!=''){
						@$dtnilaikb[$bar['keterangan1']]+=$bar['jumlah'];
						@$dtnotrxkb[$bar['keterangan1']]=$bar['notransaksi'];
						@$dtnovouckb[$bar['keterangan1']]=$bar['novoucher'];
					}
					// echo $bar['pembayaran']." - ".$bar['keterangan1']." - ".$invdok[$bar['keterangan1']]."<br/>";
					if($bar['pembayaran']=='1') {
						unset($arrnodok[$invdok[$bar['keterangan1']]]);
						unset($arrnoinvoice[$bar['keterangan1']]);
						unset($arrdata[$invdok[$bar['keterangan1']]][$bar['keterangan1']]);
					} else {
						$arrnodok[$invdok[$bar['keterangan1']]]=$invdok[$bar['keterangan1']];
					}
					
					// UNSET jika kosong
					if (count($arrdata[$invdok[$bar['keterangan1']]]) == 0) {
						unset($arrdata[$invdok[$bar['keterangan1']]]);
					}
				}
				
				foreach($arrdata as $dtnodok => $bar){
				@$no++;
				$stream.="<tr class=rowcontent style='background-color:#93c9f2'>";		
				$stream.="<td valign=top align=left>".$no."</td>";
				$stream.="<td valign=top align=left colspan=16><b>".$dtnodok."</b></td>";
				// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaijurnal[$dtnodok])."</td>";
				// $stream.="<td valign=top align=left colspan=6></td>";
				$stream.="</tr>";			
				foreach($bar as $dtnoinvoice){
					if($lsnoinvoice[$dtnodok][$dtnoinvoice]!=''){
						
						$dtnilaisisa[$dtnoinvoice]=$dtnilaiinvoice[$dtnoinvoice]-$dtnilaikb[$dtnoinvoice];
						
						$berapahari=0;
						if($dtnilaisisa[$dtnoinvoice]>0){
							if (strtotime($dtjatuhtempo[$dtnoinvoice]) >  strtotime(tanggalsystemn(date("Y-m-d")))) {
								$berapahari=selisihari($dtjatuhtempo[$dtnoinvoice],tanggalsystemn(date("Y-m-d")));
								
								if($berapahari<=0){
									$berapahari=0;
								}
								
								if($berapahari<1){
									@$dthari0[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}else if($berapahari<15){
									@$dthari1[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}else if($berapahari<31){
									@$dthari2[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}else if($berapahari<61){
									@$dthari3[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}else if($berapahari<91){
									@$dthari4[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}else{
									@$dthari5[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}
								$sumdthari0+=$dthari0[$dtnoinvoice];
								$sumdthari1+=$dthari1[$dtnoinvoice];
								$sumdthari2+=$dthari2[$dtnoinvoice];
								$sumdthari3+=$dthari3[$dtnoinvoice];
								$sumdthari4+=$dthari4[$dtnoinvoice];
								$sumdthari5+=$dthari5[$dtnoinvoice];
							} else {
								$berapahari=selisihari($dtjatuhtempo[$dtnoinvoice],tanggalsystemn(date("Y-m-d")));
								
								if($berapahari>=0){
									@$dthari0[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
								}
								$sumdthari0+=$dthari0[$dtnoinvoice];
								if($berapahari == 0){
									$berapahari=0;
								} else {
									$berapahari = $berapahari * -1;
								}
							}
						}
						
						$stream.="<tr class=rowcontent>";	
							$stream.="<td valign=top align=left></td>";
							$stream.="<td valign=top align=left>".$dttglinvoice[$dtnoinvoice]."</td>";
							$stream.="<td valign=top align=left>".$dtnoinvoice."</td>";
							$stream.="<td valign=top align=left>".$dtnamasupplier[$dtkodesupplier[$dtnoinvoice]]."</td>";
							// $stream.="<td valign=top align=left>".$dtjenissupplier[$dtkodesupplier[$dtnoinvoice]]."</td>";
							$stream.="<td valign=top align=left style='display:none'>".$dttipeinvoice[$dtnoinvoice]."</td>";
							// $stream.="<td valign=top align=left>".$dttglnodok[$dtnodok]."</td>";
							$stream.="<td valign=top align=left>".$dtnodok."</td>";
							// $stream.="<td valign=top align=center>".$nmSyaratBayar[$dtsyaratbayar[$dtnodok]]."</td>";
							// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaidpp[$dtnoinvoice])."</td>";
							// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaippn[$dtnoinvoice])."</td>";
							// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaipph[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaiinvoice[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=left>".$dtjatuhtempo[$dtnoinvoice]."</td>";
							// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaikb[$dtnoinvoice])."</td>";
							// $stream.="<td valign=top align=center>".$dtnotrxkb[$dtnoinvoice]."</td>";
							// $stream.="<td valign=top align=center>".$dtnovouckb[$dtnoinvoice]."</td>";
							
							// $stream.="<td valign=top align=right>".hidezerodecimal($dtnilaisisa[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari0[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari1[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari2[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari3[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari4[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari5[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".$berapahari."</td>";
							
							$stream.="</tr>";		
						}
				}
				@$tdtnilaijurnal+=$dtnilaijurnal[$dtnodok];
			}
			
			$stream.="<tr class=rowcontent>";	
				$stream.="<td valign=top align=center colspan=8><b>".$_SESSION['lang']['total']."</b></td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari0,2)."</td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari1,2)."</td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari2,2)."</td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari3,2)."</td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari4,2)."</td>";
				$stream.="<td valign=top align=right>".hidezerodecimal($sumdthari5,2)."</td>";
				$stream.="<td valign=top align=right></td>";
				$stream.="</tr>";
				$grandTotal = $sumdthari0+$sumdthari1+$sumdthari2+$sumdthari3+$sumdthari4+$sumdthari5;
				$stream.="<tr class=rowcontent>";	
				$stream.="<td valign=top align=right colspan=14><b>Grand ".$_SESSION['lang']['total']."</b></td>";
				$stream.="<td valign=top align=right><b>".hidezerodecimal($grandTotal,2)."</b></td>";
				$stream.="</tr>";
			}
				
				echo $stream;
				
				break;
				// end joki


				
				case'saveall':
					try {
						$owlPDO->beginTransaction();
						
						$bln = date('m');
						$thn = date('Y');
						$no=$bln."/".$thn."/RPH";
						$str="select norph from ".$dbname.".log_permintaanhargadt where norph like '%".$no."%' order by norph desc limit 0,1";
						$res=fetchdata($str);
			$dt=explode("/",$res[0]['norph']);
			$awal=$dt[0];
			$awal=intval($awal);
			$cekbln=$dt[1];
			$cekthn=$dt[2];
			if($thn!=$cekthn){
				$awal=1;
			}else{
				$awal+=1;
			}
			$counter=addZero($awal,3);
			$no_permintaan=$counter."/".$bln."/".$thn."/RPH";
			$arrPO = array();
			$arrOrgPP = array();
			$arrCek2 = array();
			$arrCek3 = array();
			$arrSubTotal = array();
			$arrNilaiDiskon = array();
			$arrNilaiPO = array();
			for($i=0;$i<count($_POST['kdbrg']);$i++){
				$rates = explode("####",$_POST['rates'][$i]);
				$purchaser = $rates[0];
				$supplierid = $rates[1];
				$nodph = $rates[2];
				$nourut = $rates[3];
				$kdbrg = $_POST['kdbrg'][$i];
				$nopp = $_POST['lstnopp'][$i];
				$orgpp = substr($nopp,15,4);
				$jenispp = substr($nopp,12,2);
				
				$str="select tipepp from ".$dbname.".log_prapoht where nopp='".$nopp."'";
				$res=fetchdata($str);
				$tipepp=$res[0]['tipepp'];
				
				if($tipepp=='SR'){
					$tipepo='SO';
				}else if($tipepp=='CP'){
					$tipepo='CO';
				}else if($tipepp=='NR'){
					$tipepo='NO';
				}else{
					$tipepo='PO';
				}
				
				$str="select a.ppn,a.pph,a.matauang, a.kurs, a.ongkir as totalongkir, a.pbbkb, b.harga, b.jumlah,a.id_franco,a.sisbayar2,a.diskonpersen,b.ongkir, b.merk, b.spec, a.nilaidiskon from ".$dbname.".log_perintaanhargaht a 
				left join ".$dbname.".log_permintaanhargadt b on a.nomor=b.nomor and a.nourut=b.nourut 
				where a.nomor='".$nodph."' and a.purchaser='".$purchaser."' and a.supplierid='".$supplierid."' and a.nourut='".$nourut."' and b.kodebarang='".$kdbrg."'";
				$res=fetchdata($str);
				$matauang = $res[0]['matauang'];
				$kurs = $res[0]['kurs'];
				$pbbkb = $res[0]['pbbkb'];
				$persenppn = $res[0]['ppn'];
				$persenpph = $res[0]['pph'];
				$harga = $res[0]['harga'];
				$jumlah = $res[0]['jumlah'];
				$ongkir = $res[0]['ongkir'];
				$totalongkir = $res[0]['totalongkir'];
				$id_franco = $res[0]['id_franco'];
				$sisbayar2 = $res[0]['sisbayar2'];
				$diskonpersen = $res[0]['diskonpersen'];
				$merk = $res[0]['merk'];
				$catatan = $res[0]['spec'];
				$rpsubtotal = ($harga * $jumlah) + $totalongkir;
				$rppersen = 0;
				$hargastlhdiskon = $harga;
				if($diskonpersen > 0){
					$rppersen = $res[0]['nilaidiskon'];

					##perhitungan harga setelah diskon
					$hargadiskon = $harga*($diskonpersen/100);
					$hargastlhdiskon = $harga - $hargadiskon;
				}
				// $rpsubtotal = $hargapersen * $jumlah;
				
				
				$myCek = $supplierid."####".$purchaser."####".$orgpp."####".$matauang."####".$kurs."####".$id_franco."####".$sisbayar2."####".$diskonpersen."####".$pbbkb."####".$jenispp;
				$arrPO[$i]['ceknopo'] = $myCek;
				
				if(in_array($myCek,$arrOrgPP)){
					$arrCek[$myCek] = 1;
				}else
				{
					$arrCek[$myCek] = 0;
				}
				$arrOrgPP[$myCek] = $myCek;
				
				$ceksup = 0;
				for($x=0;$x<$i;$x++){
					if($arrPO[$x]['ceknopo']==$myCek){
						$ceksup++;
					}
				}

				exit("warning : ".$ceksup." ");
				
				if($ceksup<=0){
					$str="select b.tipe from ".$dbname.".datakaryawan a
					left join ".$dbname.".organisasi b on a.lokasitugas=b.kodeorganisasi
					where karyawanid='".$purchaser."'";
					$res=fetchdata($str);
					
					$optPT = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$orgpp."'");
					
					if($jenispp=='SR'){
						$poso = "SO";
					}else{
						$poso = "PO";
					}

					if($res[0]['tipe']=='HOLDING'){
						$localpusat = 0;
						$nopo="/".date('Y')."/".$poso."-HO/".$orgpp."/".$optPT[$orgpp];
					}else{
						$localpusat = 1;
						$nopo="/".date('Y')."/".$poso."/".$orgpp."/".$optPT[$orgpp];
					}
					
					$str="select nopo from ".$dbname.".log_poht_del where nopo like '%".$nopo."%' order by length(nopo) desc, nopo desc limit 0,1";
					$res=fetchdata($str);
					$eksplot=explode("/",$res[0]['nopo']);
					$awal2=$eksplot[0];
					$cekbln2=$eksplot[1];
					$cekthn2=$eksplot[2];
					
					$str="select nopo from ".$dbname.".log_poht where nopo like '%".$nopo."%' order by length(nopo) desc, nopo desc limit 0,1";
					$res=fetchdata($str);
					$eksplot=explode("/",$res[0]['nopo']);
					$awal=$eksplot[0];
					
					$cekbln=$eksplot[1];
					$cekthn=$eksplot[2];
					
					if($cekthn>=$cekthn2){
						$cekthn = $cekthn;
					}else{
						$cekthn = $cekthn2;
					}
					
					if($awal >= $awal2){
						$awal = $awal;
					}else{
						$awal = $awal2;
					}
					
					if($thn!=$cekthn){
						$awal = 1;
					}else{
						$awal++;
					}
					$counterpo=$awal;
					if($awal<1000){
						$counterpo=addZero($awal,3);
					}
					   
					$nopo=$counterpo."/".$bln."".$nopo;
					$arrNoPO[$myCek] = $nopo;
					
					## CEK NILAI PPN
					// $str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$supplierid."' and noakun='1170111' limit 1";
					// $res=fetchdata($str);
					// $persenppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);
					
					$arrSubTotal[$myCek] = $rpsubtotal;
					$arrNilaiDiskon[$myCek] = $rppersen;
					$arrNilaiPPN[$myCek] = ($persenppn/100) * ($rpsubtotal - $rppersen + $pbbkb);
					$arrNilaiPPH[$myCek] = ($persenpph/100) * ($rpsubtotal - $rppersen + $pbbkb);
					$arrNilaiPO[$myCek] = $rpsubtotal - $rppersen + $pbbkb + $arrNilaiPPN[$myCek] - $arrNilaiPPH[$myCek];
					
					$str="delete from ".$dbname.".approval where notransaksi='".$nopo."' and jenispersetujuan='PO'";
					$owlPDO->exec($str);
					
					$str="insert into ".$dbname.".log_poht (nopo, tanggal, tgledit, kodesupplier, subtotal, ongkosangkutan, pbbkb, kodeorg, kodeunit, purchaser, lokalpusat, statuspo, kurs, matauang,idFranco,syaratbayar,diskonpersen,nilaidiskon,nilaipo,nodph,ppn,persenppn,pph,persenpph,tipepo) values ('".$nopo."','".date('Y-m-d')."','".date('Y-m-d')."','".$supplierid."','".$arrSubTotal[$myCek]."','".$totalongkir."','".$pbbkb."','".$optPT[$orgpp]."','".$orgpp."','".$purchaser."','".$localpusat."','0','".$kurs."','".$matauang."','".$id_franco."','".$sisbayar2."','".$diskonpersen."','".$arrNilaiDiskon[$myCek]."','".$arrNilaiPO[$myCek]."','".$no_permintaan."','".$arrNilaiPPN[$myCek]."','".$persenppn."','".$arrNilaiPPH[$myCek]."','".$persenpph."','".$tipepo."')";
					$owlPDO->exec($str);
					
					$optSup = makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$supplierid."'");
					$msgdt = "Pemenang tender untuk no RFQ : ".$no_permintaan." adalah supplier ".$optSup[$supplierid].", silahkan update PO/SO dengan nomor ".$nopo;
					$str="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$nopo."','NRPH','".$msgdt."','".$purchaser."','0','0','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
				}
				else
				{
					## CEK NILAI PPN
					$str="select tarif from ".$dbname.".log_5pphsup where supplierid='".$supplierid."' and noakun='1170111' limit 1";
					$res=fetchdata($str);
					$persenppn = ($res[0]['tarif']==''?'0':$res[0]['tarif']);
					
					$nopo = $arrNoPO[$myCek];
					$arrSubTotal[$myCek] = $arrSubTotal[$myCek]+$rpsubtotal;
					$arrNilaiDiskon[$myCek] = $arrNilaiDiskon[$myCek];
					$arrNilaiPPN[$myCek] = ($persenppn/100) * ($arrSubTotal[$myCek] - $arrNilaiDiskon[$myCek] + $pbbkb);
					$arrNilaiPO[$myCek] = $arrSubTotal[$myCek] - $arrNilaiDiskon[$myCek] + $pbbkb + $arrNilaiPPN[$myCek];
					$str="update ".$dbname.".log_poht set subtotal='".$arrSubTotal[$myCek]."',nilaidiskon='".$arrNilaiDiskon[$myCek]."',nilaipo='".$arrNilaiPO[$myCek]."', ppn='".$arrNilaiPPN[$myCek]."',persenppn='".$persenppn."' where nopo='".$nopo."'";
					$owlPDO->exec($str);
				}
				
				$optSatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kdbrg."'");
				$strx="select satuankonversi from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
				$resx=fetchdata($strx);
				if($resx[0]['satuankonversi']=='' || is_null($resx[0]['satuankonversi'])){
					$mySatuan = $optSatuan[$kdbrg];
				}else{
					$mySatuan = $resx[0]['satuankonversi'];
				}
				
				$str="insert into ".$dbname.".log_podt (nopo, kodebarang, jumlahpesan, hargasatuan, ongkangkut, harganormal, nopp, matauang, hargasbldiskon,idmerk,satuan,catatan) values ('".$nopo."','".$kdbrg."','".$jumlah."','".$hargastlhdiskon."','".$ongkir."','".$hargastlhdiskon."','".$nopp."','".$matauang."','".$harga."','".$merk."','".$mySatuan."','".$catatan."')";
				$owlPDO->exec($str);
					
				$str="update ".$dbname.".log_prapodt set create_po=1 where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
				$owlPDO->exec($str); 
									
				$str="update ".$dbname.".log_listverifikasi set pemenang='2' where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
				$owlPDO->exec($str);
					
				$str="update ".$dbname.".log_listverifikasi set pemenang='1' where nopp='".$nopp."' and kodebarang='".$kdbrg."' and karyawanid='".$purchaser."'";
				$owlPDO->exec($str);
						
				$str="update ".$dbname.".log_permintaanhargadt set flag='1',norph='".$no_permintaan."',verificator='".$_SESSION['standard']['userid']."',tanggalverifikasi='".date('Y-m-d')."' where nopp='".$nopp."' and kodebarang='".$kdbrg."' and nourut='".$nourut."' and nomor='".$nodph."'";
				$owlPDO->exec($str);

				// cek apakah ada material
				$strx="select * from ".$dbname.".log_somaterial_perbandingan where nodph='".$nodph."' and supplierid='".$supplierid."' ";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					// insert ke somaterial
					$str="insert into ".$dbname.".log_somaterial (nopo, namabarang, jumlah, harga) values ('".$nopo."','".$valx['namabarang']."','".$valx['jumlah']."','".$valx['harga']."')";
					$owlPDO->exec($str);
				}
				// update pemenang perbandingan so material
				$str="update ".$dbname.".log_somaterial_perbandingan set nopo='".$nopo."' where nodph='".$nodph."' and supplierid='".$supplierid."' ";
				$owlPDO->exec($str);




			}
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'schgetDtPP':
		$no=0;
		$tab='';
		
		$where=" and b.kodeorg='".$schpt."'";
		
		if($schnopp!='')
		{
			$where.=" and a.nopp like '%".$schnopp."%' ";
		}
		
		if($schklbrg!='')
		{
			$where.=" and left(a.kodebarang,3) = '".$schklbrg."' ";
		}
		
		if($schkdbrg!='')
		{
			$where.=" and a.kodebarang = '".$schkdbrg."' ";
		}
		
		if($schunit!='')
		{
			$where.=" and a.nopp like '%".$schunit."%' ";
		}
		
		if($schjenis!='')
		{
			$where.=" and a.kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where jenis='".$schjenis."')";
		}
		
		$no=0;
		$tab='';
		$str="select a.*,c.realisasi,d.jumlahpesan from ".$dbname.".log_listverifikasi a 
			left join ".$dbname.".log_prapoht b on a.nopp=b.nopp 
			left join ".$dbname.".log_prapodt c on a.nopp=c.nopp and a.kodebarang = c.kodebarang 
			left join ".$dbname.".log_podt d on a.nopp=d.nopp and a.kodebarang = d.kodebarang 
			where  a.status='0' ".$where." order by c.tgl_sdt asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhkolom=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		
		if($jlhkolom <= 0)
		{
			$tab.="<tr class=rowcontent><td colspan=7 align=center style='width:838px'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		else
		{
			while($bar=$res->fetch())
			{
				$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
				$optSat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$bar['kodebarang']."'");
				$jumlah = 0;
				if(($bar['jumlahpesan']=='')||is_null($bar['jumlahpesan'])||$bar['jumlahpesan']==0)
				{
					$jumlah = $bar['realisasi'];
				}
				elseif($bar['jumlahpesan']!=$bar['realisasi']) 
				{
					$jumlah = $bar['realisasi']-$bar['jumlahpesan'];
				}
				else 
				{
					$jumlah = $bar['realisasi'];
				}
				$no++;
				$tab.="<tr class=rowcontent>
					<td style=width:30px align=center>".$no."</td>
					<td style='width:180px;cursor:pointer' id=nopplst_".$no." onclick=\"previewDetail('".$bar['nopp']."',event);\">".$bar['nopp']."</td>
					<td style='width:90px' align=center id=kodebrg_".$no.">".$bar['kodebarang']."</td>
					<td style=width:380px>".$optBarang[$bar['kodebarang']]."</td>
					<td style='width:70px' align=right id=jumlah_".$no.">".number_format($jumlah)."</td>
					<td style=width:50px>".$optSat[$bar['kodebarang']]."</td>
					<td  style='width:10px' align=center><input type=checkbox id=pilBrg_".$no." /></td>
				</tr>";
			}
			$tab.="<tr><td colspan=7 align=center><button class=mybutton onclick=lanjutAdd() >".$_SESSION['lang']['lanjut']."</button></td></tr>";
		}
		
		echo $tab;
	break;
	
	case'skiprph':
		for($i=0;$i<$countbaris;$i++)
		{
			$str="update ".$dbname.".log_listverifikasi set skip='1' where nopp='".$_POST['lstnopp'][$i]."' and kodebarang='".$_POST['kdbrg'][$i]."' and karyawanid='".$_SESSION['standard']['userid']."'";
			try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
	break;
	
	case'addData':
		if($id_alamat_supplier=='')
		{
			exit("warning : Alamat Supplier harus dipilih.");
		}
		
		#ambil kodept jika holding, unit berdasarkan pp jika selain holding
        $noppx=$_POST['lstnopp'][0];
        $nopplist=  explode('/', $noppx);
        $unit=$nopplist[4];
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
		{
			$iOrg="select kodeorg from ".$dbname.".log_prapoht where nopp='".$noppx."'";
			$nOrg=$owlPDO->query($iOrg) or die(print " Gagal: ".PDOException::getMessage());
			$nOrg->setFetchMode(PDO::FETCH_ASSOC);
			$dOrg=$nOrg->fetch();
            $unodph=$dOrg['kodeorg'];
		}
		else
		{
			$unodph=$unit;
		}
		
		$tgl=date('Ymd');
        if($_POST['notransaksi']=='')
        {
			$bln = date('m');
            $thn = date('Y');
            $no="/".date('Y')."/RFQ/".$unodph;
            $ql="select `nomor` from ".$dbname.".`log_perintaanhargaht` where nomor like '%".$no."%' order by `nomor` desc limit 0,1";

            $qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qr->setFetchMode(PDO::FETCH_ASSOC);
			$rp=$qr->fetch();
			$dt=explode("/",$rp['nomor']);
			$awal=$dt[0];
            $awal=intval($awal);
            $cekbln=$dt[1];
            $cekthn=$dt[2];
            //exit("warning".$cekthn."___".$awal."___".$rp['nomor']);
            //if(($bln!=$cekbln)&&($thn!=$cekthn))
            if($thn!=$cekthn)
			{
                $awal=1;
            }
            else{
                $awal+=1;
            }
            $counter=addZero($awal,3);
            $no_permintaan=$counter."/".$bln."/".$thn."/RFQ/".$unodph;
         }
         else{
             $no_permintaan=$_POST['notransaksi'];
             $scek="select distinct * from ".$dbname.".log_perintaanhargaht 
                    where nomor='".$no_permintaan."' and supplierid='".$supplier_id."'";
			 $qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
			 $rcek=owlBaris($qcek);
             if($rcek!=0){
                 exit("error: Data tersebut sudah ada");
             }
         }
		 
          $ins="insert into ".$dbname.".log_perintaanhargaht 
                (nomor, tanggal, purchaser, supplierid,id_alamat_supplier,nourut) values 
                ('".$no_permintaan."','".$tgl."','".$_SESSION['standard']['userid']."','".$supplier_id."','".$id_alamat_supplier."','".$_POST['norurut']."')";
				
				try{
					$owlPDO->exec($ins);

                foreach($_POST['kdbrg'] as $row=>$Act){
                    $kdbrg=$Act;
                    $jmlh=str_replace(",","",$_POST['jmlh'][$row]);
                    $nopp=$_POST['lstnopp'][$row];

                    $sqp="insert into ".$dbname.".log_permintaanhargadt (`nomor`,`kodebarang`,`jumlah`,nopp,nourut) 
                          values('".$no_permintaan."','".$kdbrg."','".$jmlh."','".$nopp."','".$_POST['norurut']."')";
						try{
							$owlPDO->exec($sqp);
							
							$str="update ".$dbname.".log_listverifikasi set status='1' where nopp='".$nopp."' and kodebarang='".$kdbrg."' and karyawanid='".$_SESSION['standard']['userid']."'";
							try
							{
								$owlPDO->exec($str);
							}
							catch (PDOException $e)
							{
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}
						catch (PDOException $e) {
						
						   print " Gagal  !: " . $e->getMessage() . "\n"; 
						   die(); 
						}
                }
                $_POST['norurut']+=1;
                echo $no_permintaan."###".$_POST['norurut'];
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'preview2':
		$formPil=1;
        $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optStock=$optTermPay;
        $optKrm=$optTermPay;
        $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
        $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");   
        
		$str="select count(*) as jumlah from ".$dbname.".log_perintaanhargaht where nomor='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
        if($bar['jumlah']<2)
        {
			exit("Error : Please input supplier, min 2 supplier.");
		}
		
		$str="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$dtNomor[]=$bar['nourut'];
            $dtSupp[$bar['nourut']]=$bar['supplierid'];
            $dtFranco[$bar['nourut']]=$bar['id_franco'];
            $dtStock[$bar['nourut']]=$bar['stock'];
            $dtCattn[$bar['nourut']]=$bar['catatan'];
            $dtSisbyr[$bar['nourut']]=$bar['sisbayar'];
            $dtSisbyr2[$bar['nourut']]=$bar['sisbayar2'];
            $dtPpn[$bar['nourut']]=$bar['ppn'];
            $dtPbbkb[$bar['nourut']]=$bar['pbbkb'];
            $dtSbtotal[$bar['nourut']]=$bar['subtotal'];
            $dtDisknPrsn[$bar['nourut']]=$bar['diskonpersen'];
            $dtNildis[$bar['nourut']]=$bar['nilaidiskon'];
            $dtNilPer[$bar['nourut']]=$bar['nilaipermintaan'];
            $dtMtuang[$bar['nourut']]=$bar['matauang'];
            $dtTglDr[$bar['nourut']]=$bar['tgldari'];
            $dtTglSmp[$bar['nourut']]=$bar['tglsmp'];
            $kurs[$bar['nourut']]=$bar['kurs'];
            $dtCttn[$bar['nourut']]=$bar['catatan'];
		}
		
		$str="select distinct kodebarang,jumlah,nomor,harga,merk,nourut,hargaterakhir from ".$dbname.".log_permintaanhargadt where nomor='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			if($bar['harga']=='')
            {
				$bar['harga']=0;
			}
			
			$dtSub[$bar['nourut']][$bar['kodebarang']]=floatval($bar['jumlah'])*floatval($bar['harga']);
            $dtHarga[$bar['nourut']][$bar['kodebarang']]=$bar['harga'];
            $dtMerk[$bar['nourut']][$bar['kodebarang']]=$bar['merk'];
            $dtJumlah[$bar['nourut']][$bar['kodebarang']]=$bar['jumlah'];
            $arrJmlh[$bar['kodebarang']]=$bar['jumlah'];
            $listBarang[$bar['kodebarang']]=$bar['kodebarang'];
            $dthargaterakhir[$bar['nourut']][$bar['kodebarang']]=$bar['hargaterakhir'];
		}
		
		$tab="<table cellspacing=1 border=0 class=sortable >
			<thead class=rowheader>
            <tr>
				<td rowspan=2 align=center>No.</td>
                <td rowspan=2 align=center width=50px>".$_SESSION['lang']['kodebarang']."</td>
                <td rowspan=2 colspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td rowspan=2 align=center width='30px'>".$_SESSION['lang']['satuan']."</td>
                <td rowspan=2 align=center width='30px'>Harga Terakhir</td>";
                
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
				
				$optSupplier="";
				$str="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='SUPPLIER')";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					$optSupplier.="<option value='".$bar['supplierid']."' ".($bar['supplierid']==$dtSupp[$ard]?"selected":"").">".$bar['namasupplier']."</option>";
				}
				
				$tab.="<td colspan=4 align=center>
					<select style=width:300px disabled id=supplierId_".$ard.">".$optSupplier."</select>
				</td>";
			}
			
			$tab.="</tr><tr>";
			
			foreach ($dtNomor as $brs)
			{
				$tab.="<td  align=center width=55px>".$_SESSION['lang']['spesifikasi']."</td>
				<td  align=center width=40px>".$_SESSION['lang']['jumlah']."</td>
				<td  align=center width=40px>".$_SESSION['lang']['harga']."</td>
				<td align=center width=40px>".$_SESSION['lang']['subtotal']."</td>";
			}
			
			$tab.="<tr>
			</thead>
			<tbody>";
			
			$totRow=count($dtNomor);
			$totBrg=count($listBarang);
			
			if($totBrg==0)
			{
				exit('warning:Detail Data Kosong');
			}
			
			$no=0;
			foreach($listBarang as $brsKdBrg)
			{
				$no+=1;
				$hargasbldiskon = 0;
				$arrNmBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$brsKdBrg."'");
				$optSat = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$brsKdBrg."'");
				
				$str="select a.hargasbldiskon from ".$dbname.".log_podt a 
				left join ".$dbname.".log_poht b on a.nopo=b.nopo
				where a.kodebarang='".$brsKdBrg."' order by b.tanggal desc limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					$hargasbldiskon = $bar['hargasbldiskon'];
				}
				
                $tab.="<tr class='rowcontent'>
					<td align=center>".$no."</td>
					<td id='kd_brg_".$no."'>".$brsKdBrg."</td>
					<td colspan=2 title='".$arrNmBrg[$brsKdBrg]."'>".$arrNmBrg[$brsKdBrg]."</td>
					<td align=center>".$optSat[$brsKdBrg]."</td>";
				if($formPil!='1')
				{
					$tab.="<td align=right><label>".($hargasbldiskon==0?0:number_format($hargasbldiskon,2))."</label></td>";
				}
				else
				{
					$tab.="<td align=right><label id='hargaterakhir_".$no."_".$ard."'>".($dthargaterakhir[$ard][$brsKdBrg]==0?0:number_format($dthargaterakhir[$ard][$brsKdBrg],2))."</label></td>";
				}
					
				$ard=0;
                foreach ($dtNomor as $brs)
				{
					$ard+=1;
                    if($formPil!='1')
					{
						$tab.="<td align=left>".$dtMerk[$ard][$brsKdBrg]."</td>
						<td align=left>".number_format($dtJumlah[$ard][$brsKdBrg],2)."</td>
						<td align=right>".number_format($dtHarga[$ard][$brsKdBrg],2)."</td>
						<td align=right>".number_format($dtSub[$ard][$brsKdBrg],2)."</td>";
					}
					else
					{
						$tab.="<td align=justify>
							<textarea placeholder='Maximal character 255' maxlength=255 id=merk_".$no."_".$ard."  class='myinputtext' onkeypress='return tanpa_kutip(event)' rows=3>".$dtMerk[$ard][$brsKdBrg]."</textarea>
						</td>
						<td align=right>
							<input type=text id=qty_".$no."_".$ard." value='".hidezerodecimal($dtJumlah[$ard][$brsKdBrg],2)."' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"calculate(".$no.",".$ard.",".$totBrg.");z.numberFormat('qty_".$no."_".$ard."',2)\" style='width:75px' />
						</td>
						<td align=right>
							<input type=text id=price_".$no."_".$ard." value='".hidezerodecimal($dtHarga[$ard][$brsKdBrg],2)."' class='myinputtextnumber' onkeypress='return angka_doang(event)' onkeyup=\"calculate(".$no.",".$ard.",".$totBrg.");z.numberFormat('price_".$no."_".$ard."',2)\" style='width:75px' />
						</td>
						<td align=right>
							<input type=text id=total_".$no."_".$ard." disabled value='".hidezerodecimal($dtSub[$ard][$brsKdBrg],2)."'  class='myinputtextnumber' onkeypress='return angka_doang(event)' style='width:75px'  />
						</td>";
					}
				}
				$tab.="</tr>";
			}
			
			####SUBTOTAL####
			$tab.="<tr class='rowcontent'>
				<td rowspan=5 colspan=3 valign=top align=left>&nbsp</td><td colspan=3>".$_SESSION['lang']['subtotal']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
            {
				$ard+=1;
                $tab.="<td align=right colspan=4 id=total_harga_po_".$ard.">".number_format($dtSbtotal[$ard],0)."</td>";
			}
			
			$tab.="</tr>";
			
			####DISKON####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['diskon']."</td>";
				
			$nor=0;
			foreach ($dtNomor as $brs)
			{
				$nor+=1;
                if($formPil!='1')
                {
					$tab.="<td align=right colspan=2>".number_format($dtDisknPrsn[$nor],0)."%</td>
					<td align=right>".number_format($dtNildis[$nor],0)."</td>";
				}
				else
                {
					$tab.="<td align=right colspan=3>
						<input type=text  id=diskon_".$nor." name=diskon_".$nor." class=myinputtextnumber onkeyup=\"calculate_diskon(".$nor.");z.numberFormat('diskon_".$nor."',2)\" maxlength=4 onkeypress=return angka_doang(event) onblur=\"getZero(".$nor.")\" value='".hidezerodecimal($dtDisknPrsn[$nor],2)."' style='width:75px'  />
					</td>
					<td align=right>
						<input type=text  id=angDiskon_".$nor." name=angDiskon_".$nor." class=myinputtextnumber  onkeyup=\"calculate_angDiskon(".$nor.");z.numberFormat('angDiskon_".$nor."',2)\" onkeypress=return angka_doang(event) onblur=\"getZero(".$nor.")\" value='".hidezerodecimal($dtNildis[$nor],2)."' style='width:75px' />
					</td>";
				}
			}
			$tab.="</tr>";
			
			####PPN####
			$tab.="<tr class='rowcontent'><td colspan=3>".$_SESSION['lang']['ppn']."</td>";
			
			$ard=0;
            foreach ($dtNomor as $brs)
            {
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td align=right colspan=3>".number_format($dtPPN[$ard],2)."</td>";
				}
				else
				{
					@$persen[$ard]=(($dtSbtotal[$ard]-$dtNildis[$ard])*($dtPpn[$ard]/100));
                    $tab.="<td align=right colspan=3>
						<input type=text  id=ppN_".$ard." name=ppN_".$ard." class=myinputtextnumber  onkeyup=calculate_all(".$ard.")  maxlength=2  onkeypress=return angka_doang(event) onblur=\"validasippn(".$ard.")\"  value='".hidezerodecimal($dtPpn[$ard],2)."' style='width:75px' />
					</td>
					<td align=right>
						<input type=text  id=ppn_".$ard." name=ppn_".$ard." class=myinputtextnumber  disabled value='".hidezerodecimal($persen[$ard],2)."' style='width:75px' />
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####PBBKB####
			$tab.="<tr class='rowcontent'><td colspan=3>PBBKB</td>";
			
			$ard=0;
            foreach ($dtNomor as $brs)
            {
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td align=right colspan=3>".number_format($dtPbbkb[$ard],0)."</td>";
				}
				else
				{
					$tab.="<td align=right colspan=3></td>
					<td align=right>
						<input type=text  id=pbbkb_".$ard." name=pbbkb_".$ard." class=myinputtextnumber  onkeyup=\"calculate_all(".$ard.");z.numberFormat('pbbkb_".$ard."',2)\" onkeypress='return angka_doang(event)' onblur=\"getZero(".$ard.")\"  value='".hidezerodecimal($dtPbbkb[$ard],2)."' style='width:75px' />
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####GRANDTOTAL####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['grnd_total']."</td>";
				
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                $tab.="<td align=right colspan=4 id=grand_total_".$ard.">".hidezerodecimal($dtNilPer[$ard],2)."</td>";
			}
			
			$tab.="</tr>";
			
			####NO PERMINTAAN HARGA####
			$tab.="<tr class='rowcontent'>
				<td rowspan=11 colspan=3 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>
				<td colspan=3>No. RPH</td>";
				
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$_POST['notransaksi']."</td>";
				}
				else
				{
					$tab.="<td colspan=4><input type=text disabled id=no_prmntan_".$ard." value='".$_POST['notransaksi']."' class=myinputtext style='width:150px' /></td>";
				}
			}
			
			$tab.="</tr>";
			
			####MATA UANG####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['matauang']."</td>";
            
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                $optMt="";
                
				$optMt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $str="select kode,kodeiso from ".$dbname.".setup_matauang order by kode desc";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					if($dtMtuang[$ard]!='')
                    {
						$optMt.="<option value=".$bar['kode']." ".($dtMtuang[$ard]==$bar['kode']?"selected":" ").">".$bar['kodeiso']."</option>";
					}
					else
					{
						$optMt.="<option value=".$bar['kode'].">".$bar['kodeiso']."</option>";
					}
				}
				
				if($formPil!='1')
                {
					$tab.="<td colspan=4>".$dtMtuang[$ard]."</td>";
				}
				else
                {
					$tab.="<td colspan=4><select id=\"mtUang_".$ard."\" name=\"mtUang_".$ard."\" style=\"width:65px;\" >".$optMt."</select></td>";
				}
			}
			
			$tab.="</tr>";
			
			####KURS####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['kurs']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$kurs[$ard]."</td>";
				}
				else
				{
					$tab.="<td colspan=4>
						<input type=\"text\" class=\"myinputtextnumber\" id=\"Kurs_".$ard."\" name=\"Kurs_".$ard."\" style=\"width:60px;\" onkeypress=\"return angka_doang(event)\" value=".$kurs[$ard]."  />
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####TANGGAL DARI####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['tgldari']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$dtTglDr[$ard]."</td>";
				}
				else
				{
					$tab.="<td colspan=4>
						<input type=text class=myinputtext style='width:60px' id=tgl_dari_".$ard." onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".tanggalnormal($dtTglDr[$ard])."' readonly/>
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####TANGGAL SAMPAI####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['tglsmp']."</td>";
				
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$dtTglSmp[$ard]."</td>";
				}
				else
				{
					$tab.="<td colspan=4>
						<input type=text class=myinputtext style='width:60px' id=tgl_smp_".$ard." onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".tanggalnormal($dtTglSmp[$ard])."' readonly/>
					</td>";
				}
			}
			$tab.="</tr>";
			
			####SYARAT PEMBAYARAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['syaratPem']."</td>";
			
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$arrOptTerm[$dtSisbyr[$ard]]."</td>";
				}
				else
                {
					$optTermPay="";
                    $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $str="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar order by keterangan asc";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						if($dtSisbyr2[$ard]!='')
						{
							$optTermPay.="<option value='".$bar['kode']."' ".($bar['kode']==$dtSisbyr2[$ard]?"selected":"").">".$bar['keterangan']." (".$bar['jenis'].")</option>";
						}
						else
						{
							$optTermPay.="<option value=".$bar['kode'].">".$bar['keterangan']." (".$bar['jenis'].")</option>";
						}
					}
					
					$tab.="<td colspan=4>
						<select id='term_pay_".$ard."'  style='width:150px'>".$optTermPay."</select>
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####STOCK####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['stock']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$arrStock[$dtStock[$ard]]."</td>";
				}
				else
				{
					$optStock="";
                    $optStock="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    foreach($arrStock as $brsStock => $listStock)
                    {
						if($dtStock[$ard]!='')
						{
							$optStock.="<option value='".$brsStock."' ".($brsStock==$dtStock[$ard]?"selected":"").">".$listStock."</option>";
						}
						else
						{
							$optStock.="<option value='".$brsStock."'>".$listStock."</option>";
						}
					}
					
					$tab.="<td colspan=4>
						<select id=stockId_".$ard." style='width:150px'>".$optStock."</select>
					</td>";
				}
			}
			
			$tab.="</tr>";
			
			####LOKASI PENGIRIMAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3>".$_SESSION['lang']['almt_kirim']."</td>";
				
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td colspan=4>".$arrFranco[$dtFranco[$ard]]."</td>";
				}
				else
				{
					$optKrm="";
                    $optKrm="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $str="select id_franco,franco_name from ".$dbname.".setup_franco where status=0 order by franco_name asc";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while($bar=$res->fetch())
					{
						if($dtFranco[$ard]!='0')
						{
							$optKrm.="<option value=".$bar['id_franco']." ".($bar['id_franco']==$dtFranco[$ard]?"selected":"").">".$bar['franco_name']."</option>";
						}
						else
						{
							$optKrm.="<option value=".$bar['id_franco'].">".$bar['franco_name']."</option>";
						}
					}
					
					$tab.="<td colspan=4>
						<select id=tmpt_krm_".$ard." style='width:150px'>".$optKrm."</select></td>";
				}
			}
			
			$tab.="</tr>";
			
			####KETERANGAN####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>".$_SESSION['lang']['keterangan']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='1')
                {
					$tab.="<td align=justify colspan=4>".(isset($dtCttn[$ard])? $dtCttn[$ard]: '')."</td>";
				}
				else
                {
					$tab.="<td align=justify colspan=4><textarea placeholder='Maximal character 128' maxlength=128 id='ketUraian_".$ard."' name='ketUraian_".$ard."' onkeypress='return tanpa_kutip(event);' cols=42 rows=3>".(isset($dtCttn[$ard])? $dtCttn[$ard]: '')."</textarea></td>";
				}
			}
			
			$tab.="</tr>";
			
			####FILEUPLOAD####
			$tab.="<tr class='rowcontent'>
				<td colspan=3 valign=top>".$_SESSION['lang']['uploaddata']."</td>";
				
			$ard=0;
            foreach ($dtNomor as $brs)
			{
				$ard+=1;
				$tab.="<td colspan=4 valign=top>
				<div id='listfiles_".$_POST['notransaksi']."_".$dtSupp[$ard]."'><table>";
				$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$_POST['notransaksi']."' and supplierid='".$dtSupp[$ard]."' and status='1'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$nofiles = 0;
				while($bar=$res->fetch())
				{
					$nofiles++;
					$tab.="<tr>
						<td><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".substr($bar['namafile'],0,40)."...</a></td>
						<td>
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$_POST['notransaksi']."','".$dtSupp[$ard]."','".$bar['namafile']."');\" >
						</td>
					</tr>";
				}
				$tab.="<tr>
					<td>
						<input type='file' name='upload_".$_POST['notransaksi']."_".$dtSupp[$ard]."' id='upload_".$_POST['notransaksi']."_".$dtSupp[$ard]."' class='mybutton'>
					</td>
					<td>
						<img id='detail_add' title='Tambah' class='resicon' onclick=\"addfile('".$_POST['notransaksi']."','".$dtSupp[$ard]."')\" src='images/plus.png'>
					</td>
				</tr>
				</table></div>";
				$tab.="</td>";
			}
			
			$tab.="</tr>";
			
			####SIMPAN####
			$tab.="<tr class=rowcontent><td colspan=3></td>";
			
			$ard=0;
			foreach ($dtNomor as $brs)
			{
				$ard+=1;
                if($formPil!='0')
                {
					$tab.="<td align=center colspan=4>
						<button class=mybutton id=save_".$ard." onclick=simpanSemua2(".$ard.",".$totBrg.")>".$_SESSION['lang']['save']."</button>
					</td>";
				}
			}
			
			$tab.="</tr>
			</tbody>
		</table>";
		
		echo $tab;
	break;
	
	case 'updateTransaksi':
		$subTotal=str_replace(',', '', $subTotal);
        $nilaiPermintaan=str_replace(',', '', $nilaiPermintaan);
        $diskonPersen=str_replace(',', '', $diskonPersen);
        $nilDiskon=str_replace(',', '', $nilDiskon);
        $pbbkb=str_replace(',', '', $pbbkb);
        $str="select distinct supplierid as supplierid from ".$dbname.".log_perintaanhargaht where nomor='".$no_prmntan."' and nourut='".$nourut."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		// if($supplierId==$bar['supplierid'])
		// {
			// exit("error: Supplier Tersebut Sudah Terdaftar");
		// }
		
		if(strlen($ketUraian)>128)
		{
			exit("Keterangan melebihi 128 character");
		}
		
		$str="update ".$dbname.".log_perintaanhargaht set id_franco='".intval($idFranco)."', stock='".intval($stockId)."',catatan='".$ketUraian."',sisbayar2='".$termPay."', ppn='".$nilPPn."',pbbkb='".$pbbkb."', subtotal='".$subTotal."',diskonpersen='".$diskonPersen."', nilaidiskon='".$nilDiskon."', nilaipermintaan='".$nilaiPermintaan."', tgldari='".tanggalsystem($tglDari)."', tglsmp='".tanggalsystem($tglSmp)."', kurs='".$kurs."',matauang='".$mtUang."',supplierid='".$supplierId."' where nomor='".$no_prmntan."' and nourut='".$nourut."'";
		try
		{
			$owlPDO->exec($str);
			
			$totRow=count($_POST['kdbrg']);
			foreach($_POST['kdbrg'] as $row=>$Act)
			{
				$kdbrg=$Act;
                $merk=$_POST['merk'][$row];
                $hrg=str_replace(',', '', $_POST['price'][$row]);
                $jmlh=str_replace(',', '', $_POST['jmlh'][$row]);
                $qty=str_replace(',', '', $_POST['qty'][$row]);
				
				$str="update ".$dbname.".log_permintaanhargadt set `jumlah`='".$qty."',`harga`='".$hrg."',`merk`='".$merk."' where nomor='".$no_prmntan."' and kodebarang='".$kdbrg."' and nourut='".$nourut."'";
				try
				{
					$owlPDO->exec($str);
					$berhasil+=1;
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		
		if($totRow==$berhasil)
		{
			exit("Done");
		}
		
	break;
	
	case 'submitfile':
		$tgl = date("YmdHis");
		// exit("error : ".$tgl);
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$str = "insert into ".$dbname.".log_permintaanhargafile values ('".$notransaksi."','".$supplierid."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try
						{
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,"fileupload/rph/$filename");
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg .jpeg .png .pdf .xls .xlsx .doc .docx");
				}
			}
		}
	break;
	
	case 'loadfiles':
		$tab="";
		$tab.="<table>";
		$str="select * from ".$dbname.".log_permintaanhargafile where nomor='".$notransaksi."' and supplierid='".$supplierid."' and status='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$tab.="<tr>
				<td><a href='fileupload/rph/".$bar['namafile']."' download title='".$bar['namafile']."'>".substr($bar['namafile'],0,40)."...</a></td>
				<td>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$notransaksi."','".$supplierid."','".$bar['namafile']."');\" >
				</td>
			</tr>";
		}
		$tab.="<tr>
			<td>
				<input type='file' name='upload_".$notransaksi."_".$supplierid."' id='upload_".$notransaksi."_".$supplierid."' class='mybutton'>
			</td>
			<td>
				<img id='detail_add' title='Tambah' class='resicon' onclick=\"addfile('".$notransaksi."','".$supplierid."')\" src='images/plus.png'>
			</td>
		</tr>
		</table>";
		echo $tab;
	break;
	
	case 'deletefile':
		$str="delete from ".$dbname.".log_permintaanhargafile where nomor='".$notransaksi."' and supplierid='".$supplierid."' and namafile='".$namafile."'";
		try
		{
			$owlPDO->exec($str);
			$path = "fileupload/rph/".$namafile;
			unlink($path);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'listBarangDetail':
		$tab.="<tr class=rowcontent><td colspan=7>&nbsp;</td></tr>";
		$sPp="select distinct * from ".$dbname.". log_permintaanhargadt where nomor='".$_POST['notransaksi']."' and nourut='".$_POST['nourut']."'";
		$qPp=$owlPDO->query($sPp) or die(print " Gagal: ".PDOException::getMessage());
		$qPp->setFetchMode(PDO::FETCH_ASSOC);
		while($rPp=$qPp->fetch()){	
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td style=width:20px>".$no."</td>";
				$tab.="<td style='width:180px;cursor:pointer' id=nopplst_".$no." onclick=\"previewDetail('".$rPp['nopp']."',event);\">".$rPp['nopp']."</td>";
				$tab.="<td style='width:88px' id=kodebrg_".$no.">".$rPp['kodebarang']."</td>";
				$tab.="<td style=width:380px>".$optBarang[$rPp['kodebarang']]."</td>";
				$tab.="<td style='width:62px' align=right id=jumlah_".$no.">".$rPp['jumlah']."</td>";
				$tab.="<td style=width:55px>".$optSat[$rPp['kodebarang']]."</td>";
				$tab.="<td  style='width:10px' align=center><input type=checkbox id=pilBrg_".$no." checked /></td></tr>";
		 }
		 echo $tab;
	break;
	
	case 'deleted':
        ## DELETE HEADER RPH ##
		$str="delete from ".$dbname.".log_perintaanhargaht where nomor='".$no_permintaan."'";
		try
		{
			$owlPDO->exec($str);
			
			## DELETE DETAIL RPH AND UPDATE VERIFIKASI PP ##
			$str="select kodebarang,nopp from ".$dbname.".log_permintaanhargadt where nomor='".$no_permintaan."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$str="update ".$dbname.".log_listverifikasi set status='0' where nopp='".$bar['nopp']."' and kodebarang='".$bar['kodebarang']."' and karyawanid='".$_SESSION['standard']['userid']."'";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			
			$str="delete from ".$dbname.".log_permintaanhargadt where nomor='".$no_permintaan."'";
			try
			{
				$owlPDO->exec($str);
				
				$str="select namafile from ".$dbname.".log_permintaanhargafile where nomor='".$no_permintaan."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch())
				{
					$path = "fileupload/rph/".$bar['namafile'];
					unlink($path);
				}
				
				$str="delete from ".$dbname.".log_permintaanhargafile where nomor='".$no_permintaan."'";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			catch (PDOException $e) 
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
     break;
	 
	 case 'get_alasan_batal':
		$str="update ".$dbname.".log_perintaanhargaht set flag='1' where nomor='".$no_permintaan."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	 break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
       case'getSupplierNm':
           
           if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
           {
               $sortkode="and kodekelompok in ('S001','S002','S004')";
           }
           else
           {
               $sortkode="and kodekelompok in ('S004','S001')";
           }
           
		     echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 class=sortable>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
                        <td align=center>".$_SESSION['lang']['kodesupplier']."</td>
                        <td align=center>".$_SESSION['lang']['namasupplier']."</td>
                        </tr><tbody>
                        ";
                $no=0;
                 $sSupplier="select namasupplier,supplierid from ".$dbname.".log_5supplier"
							. " where supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='SUPPLIER')"
                            ." and namasupplier like '%".$nmSupplier."%' and status=1 "; 
 				$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
				$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
				while($rSupplier=$qSupplier->fetch()){
                     $no+=1;
                     echo"<tr class=rowcontent style=cursor:pointer onclick=setData('".$rSupplier['supplierid']."')>
                         <td align=center>".$no."</td>
                         <td>".$rSupplier['supplierid']."</td>
                         <td>".$rSupplier['namasupplier']."</td>
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
         
         
         
         
        case'getNopp':
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['nopp']."</td>
                        
                        </tr><tbody>
                        ";
                 //$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
                 $sSupplier="select distinct nopp from ".$dbname.".log_prapodt where nopp like '%".$kdNopp."%' and create_po='0'";
      				$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
				$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
				while($rSupplier=$qSupplier->fetch()){	 
                 
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setDataNopp('".$rSupplier['nopp']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['nopp']."</td>
                         
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
         case'getNopp2':
             if(strlen($kdNopp)<5)
             {
                 exit("error: Min 4 character");
             }
                    echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
                        <div style=\"overflow:auto;height:295px;width:455px;\">
                        <table cellpading=1 border=0 cellspacing=1 class=sortbale>
                        <thead>
                        <tr class=rowheader>
                        <td>No.</td>
                        <td>".$_SESSION['lang']['nopp']."</td>
                        
                        </tr><tbody>
                        ";
                 //$sSupplier="select a.nopp  from ".$dbname.".log_prapoht a left join ".$dbname.".log_podt b on a.nopp=b.nopp where a.nopp like '%".$kdNopp."%' and close='2' and b.nopo is null";
                 $sSupplier="select distinct nopp from ".$dbname.".log_perintaanhargaht where nopp like '%".$kdNopp."%'";
     			$qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
				$qSupplier->setFetchMode(PDO::FETCH_ASSOC);
		
                                while($rSupplier=$qSupplier->fetch()){		 
                     $no+=1;
                     echo"<tr class=rowcontent onclick=setDataNopp('".$rSupplier['nopp']."')>
                         <td>".$no."</td>
                         <td>".$rSupplier['nopp']."</td>
                         
                    </tr>";
                 }
                    echo"</tbody></table></div>";
         break;
		 
		 
		 
		 
		
		 
		 
		 
		 
		 
        
		 
         case'loadSuppier':
		  $no=0;$tabl='';
			$sData="select a.nomor,a.supplierid,a.nourut,b.alamat,b.kota from ".$dbname.".log_perintaanhargaht a
				left join ".$dbname.".log_5supalamat b on a.id_alamat_supplier = b.id_alamat
                 where a.nomor='".$_POST['notrans']."'
                 order by a.nomor asc";
			$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
			$qData->setFetchMode(PDO::FETCH_ASSOC);
			while($rData=$qData->fetch())
			{	
				 $no++;
				 $sNmsup="select distinct namasupplier from ".$dbname.".log_5supplier where supplierid='".$rData['supplierid']."'";
					

					$qNmsup=$owlPDO->query($sNmsup) or die(print " Gagal: ".PDOException::getMessage());
					$qNmsup->setFetchMode(PDO::FETCH_ASSOC);
					$rNmsup=$qNmsup->fetch();
				 $tabl.="<tr class=rowcontent>";
				 $tabl.="<td align=center>".$no."</td>";
				 $tabl.="<td>".$rData['nomor']."</td>";
				 $tabl.="<td>".$rNmsup['namasupplier']."</td>";
				 $tabl.="<td>".$rData['alamat']." ".$rData['kota']."</td>";
				 $tabl.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPer('".$rData['nomor']."','".$rData['nourut']."');\"></td>";
				 $tabl.="</tr>";
				 $tab.="asd";
         }
		 
         echo $tabl;
         break;
		 
		 
         
							
									
    

	//<input type=hidden id=nopp_".$no." name=nopp_".$no." value='".$bar['nopp']."' />

                    
                    case'get_nopp':
                    $optNopp='';
                    $sql="SELECT a.nopp FROM ".$dbname.".`log_prapodt` a left join ".$dbname.".`log_prapoht` b on a.nopp=b.nopp where b.close='2' 
                    and (a.create_po is null or create_po='') 
                    and a.kodebarang='".$kd_brg."'"; //echo "warning".$sql;
                
					$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
					$query->setFetchMode(PDO::FETCH_ASSOC);
					while($res=$query->fetch()){	
                   
                            $optNopp.="<option value=".$res['nopp'].">".$res['nopp']."</option>";
                    }
                    echo $optNopp;
                    break;
                    case'getSpek':
                        $sSpek="select spesifikasi from ".$dbname.".log_5photobarang where kodebarang='".$kd_brg."'";
                  
						$qSpek=$owlPDO->query($sSpek) or die(print " Gagal: ".PDOException::getMessage());
						$qSpek->setFetchMode(PDO::FETCH_ASSOC);
						$rSpek=$qSpek->fetch();
                        echo $rSpek['spesifikasi'];
                    break;
                    case'getKurs':
                        $tgl=date("Ymd");
                        $sGet="select distinct kurs from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl."'";
                   
						$qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
						$qGet->setFetchMode(PDO::FETCH_ASSOC);
						$rGet=$qGet->fetch();
                        //echo "warning:".$rGet['kurs'];
                        if($mtUang=='IDR')
                        {
                                $rGet['kurs']=1;
                        }
                        else
                        {
                                if($rGet['kurs']!=0)
                                {
                                        $rGet['kurs']=$rGet['kurs'];
                                }
                                else
                                {
                                        $rGet['kurs']=1;
                                }
                        }
                    echo $rGet['kurs'];
                    break;
                    

                    case'printExcel':

                    $optTermPay="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $optStock=$optTermPay;
                    $optKrm=$optTermPay;
                    $arrOptTerm=array("1"=>"Cash","2"=>"Credit 2 weeks","3"=>"Credit 1 month","4"=>"Spesific Terms","5"=>"Down Payment");
                    $arrStock=array("1"=>"Ready Stock","2"=>"Not Ready");   
					$arrFranco = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
					$arrOptTerm2 =  makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan,jenis','',4);
                    $sdtheder="select distinct * from ".$dbname.".log_perintaanhargaht where nomor='".$_GET['no_permintaan']."'";

					$qdtheder=$owlPDO->query($sdtheder) or die(print " Gagal: ".PDOException::getMessage());
					$qdtheder->setFetchMode(PDO::FETCH_ASSOC);
					while($rdtheder=$qdtheder->fetch()){	
                        $dtNomor[]=$rdtheder['nourut'];
                        $dtSupp[$rdtheder['nourut']]=$rdtheder['supplierid'];
                        $dtFranco[$rdtheder['nourut']]=$rdtheder['id_franco'];
                        $dtStock[$rdtheder['nourut']]=$rdtheder['stock'];
                        $dtCattn[$rdtheder['nourut']]=$rdtheder['catatan'];
                        $dtSisbyr[$rdtheder['nourut']]=$rdtheder['sisbayar'];
                        $dtSisbyr2[$rdtheder['nourut']]=$rdtheder['sisbayar2'];
                        $dtPpn[$rdtheder['nourut']]=$rdtheder['ppn'];
                        $dtSbtotal[$rdtheder['nourut']]=$rdtheder['subtotal'];
                        $dtDisknPrsn[$rdtheder['nourut']]=$rdtheder['diskonpersen'];
                        $dtNildis[$rdtheder['nourut']]=$rdtheder['nilaidiskon'];
                        $dtNilPer[$rdtheder['nourut']]=$rdtheder['nilaipermintaan'];
                        $dtMtuang[$rdtheder['nourut']]=$rdtheder['matauang'];
                        $dtTglDr[$rdtheder['nourut']]=$rdtheder['tgldari'];
                        $dtTglSmp[$rdtheder['nourut']]=$rdtheder['tglsmp'];
                        $kurs[$rdtheder['nourut']]=$rdtheder['kurs'];
                        $dtCttn[$rdtheder['nourut']]=$rdtheder['catatan'];
                    }


                    $sDetail="select distinct kodebarang,jumlah,nomor,harga,merk,nourut from ".$dbname.".log_permintaanhargadt where nomor='".$_GET['no_permintaan']."' ";
					$qDetail=$owlPDO->query($sDetail) or die(print " Gagal: ".PDOException::getMessage());
					$qDetail->setFetchMode(PDO::FETCH_ASSOC);
					while($rDetail=$qDetail->fetch()){	
                  
                        if($rDetail['harga']=='')
                        {
                            $rDetail['harga']=0;
                        }
                        $dtSub[$rDetail['nourut']][$rDetail['kodebarang']]=floatval($rDetail['jumlah'])*floatval($rDetail['harga']);
                        $dtHarga[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['harga'];
                        $dtMerk[$rDetail['nourut']][$rDetail['kodebarang']]=$rDetail['merk'];
                        $arrJmlh[$rDetail['kodebarang']]=$rDetail['jumlah'];
                        $listBarang[$rDetail['kodebarang']]=$rDetail['kodebarang'];

                    }


                $tab="<table cellspacing=1 border=1 class=sortable >
                <thead class=rowheader>
                <tr>
                <td bgcolor=#DEDEDE rowspan=2 align=center>No.</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['namabarang']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['jumlah']."</td>
                <td bgcolor=#DEDEDE rowspan=2 align=center>".$_SESSION['lang']['satuan']."</td>";
                $ard=0;
                foreach ($dtNomor as $brs){    
                     $ard+=1; 
                $tab.="<td bgcolor=#DEDEDE colspan=3 align=center>".$optNmSup[$dtSupp[$ard]]."</td>";
                  }
                $tab.="</tr><tr>";
                foreach ($dtNomor as $brs){
                    $tab.="<td   bgcolor=#DEDEDE align=center width=85px>".$_SESSION['lang']['spesifikasi']."</td><td  align=center width=85px bgcolor=#DEDEDE>".$_SESSION['lang']['harga']."</td><td align=center width=85px bgcolor=#DEDEDE>".$_SESSION['lang']['subtotal']."</td>";
                }
                  $tab.="<tr>";
                $tab.="</thead>
                <tbody>";
               $totRow=count($dtNomor);
               $totBrg=count($listBarang);
                foreach($listBarang as $brsKdBrg){
                    $no+=1;
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td>".$no."</td>";
                    $tab.="<td id='kd_brg_".$no."'>".$brsKdBrg."</td>";
                    $tab.="<td title='".$arrNmBrg[$brsKdBrg]."'>".$arrNmBrg[$brsKdBrg]."</td>";
                    $tab.="<td align=right id='jumlah_".$no."'>".$arrJmlh[$brsKdBrg]."</td>";
                    $tab.="<td align=center>".$optSat[$brsKdBrg]."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs)
                    {
                        $ard+=1;
                        $tab.="<td align=left>".$dtMerk[$ard][$brsKdBrg]."</td>";
                        $tab.="<td align=right>".number_format($dtHarga[$ard][$brsKdBrg],2)."</td>";
                        $tab.="<td align=right>".number_format($dtSub[$ard][$brsKdBrg],2)."</td>";
                    }
                    $tab.="</tr>";
                }
                    $tab.="<tr class='rowcontent'>";
                    
                    $tab.="<td rowspan=4 colspan=3 valign=top align=left>&nbsp</td><td colspan=2>".$_SESSION['lang']['subtotal']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs)
                    {
                        $ard+=1;
                        $tab.="<td align=right colspan=3 id=total_harga_po_".$ard.">".number_format($dtSbtotal[$ard],2)."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['diskon']."</td>";
                    foreach ($dtNomor as $brs)
                    {
                        $nor+=1;
                            $tab.="<td align=right colspan=2>".$dtDisknPrsn[$nor]."%</td>";
                            $tab.="<td align=right>".number_format($dtNildis[$nor],2)."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['ppn']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs)
                    {
                        $ard+=1;
                            @$persen[$ard]=($dtPpn[$ard]/($dtSbtotal[$ard]-$dtNildis[$ard]))*100;
                            $tab.="<td align=right colspan=2>".$persen[$ard]."</td>";
                            $tab.="<td align=right >".number_format($dtPPN[$ard],2)."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['grnd_total']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
                        $tab.="<td align=right colspan=3 id=grand_total_".$ard.">".number_format($dtNilPer[$ard],2)."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td rowspan=10 colspan=3 valign=top align=left>".$_SESSION['lang']['rekomendasi']."</td>";
                    $tab.="<td colspan=2>".$_SESSION['lang']['nopermintaan']."</td>";
                    $ard=0;
                        foreach ($dtNomor as $brs){
                                $ard+=1;
                                $tab.="<td colspan=3>".$_GET['no_permintaan']."</td>";
                        }
                        $tab.="</tr>";
                        $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['matauang']."</td>";
                        $ard=0;
                        foreach ($dtNomor as $brs){
                            $ard+=1;                                    
                                $tab.="<td colspan=3>".$dtMtuang[$ard]."</td>";
                        }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['kurs']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
                            $tab.="<td colspan=3>".$kurs[$ard]."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tgldari']."</td>";
                    $ard=0;
                        foreach ($dtNomor as $brs){
                            $ard+=1;
                                $tab.="<td colspan=3>".$dtTglDr[$ard]."</td>";
                        }
                    
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['tglsmp']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs)  {
                        $ard+=1;
                        $tab.="<td colspan=3>".$dtTglSmp[$ard]."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['syaratPem']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
							if($dtSisbyr[$ard]!='0'){
								$hasilSyaratBayar = $arrOptTerm[$dtSisbyr[$ard]];
							}else{
								if($dtSisbyr2[$ard]!=''){
									$hasilSyaratBayar = $arrOptTerm2[$dtSisbyr2[$ard]];;
								}else{
									$hasilSyaratBayar = "";
								}
							}
                            $tab.="<td colspan=3>".$hasilSyaratBayar."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['stock']."</td>";
                     $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
                            $tab.="<td colspan=3>".$arrStock[$dtStock[$ard]]."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['almt_kirim']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
                        $tab.="<td colspan=3>".$arrFranco[$dtFranco[$ard]]."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class='rowcontent'><td colspan=2>".$_SESSION['lang']['keterangan']."</td>";
                    $ard=0;
                    foreach ($dtNomor as $brs){
                        $ard+=1;
                        $tab.="<td align=justify colspan=3>".$dtCttn[$ard]."</td>";
                    }
                    $tab.="</tr>";
                    $tab.="<tr class=rowcontent><td colspan=2></td>";
                    $ard=0;
                    $tab.="<td align=center colspan=3></td>";
                    $tab.="</tr>";
                
        
                    $tab.="</tbody></table>";
                                $nop_="form_permintaan_harga";
                                if(strlen($tab)>0)
                                {
                                if ($handle = opendir('tempExcel')) {
                                while (false !== ($file = readdir($handle))) {
                                if ($file != "." && $file != ".." && $file != "index.html") {
                                @unlink('tempExcel/'.$file);
                                }
                                }	
                                closedir($handle);
                                }
                                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                                if(!fwrite($handle,$tab))
                                {
                                echo "<script language=javascript1.2>
                                parent.window.alert('Can't convert to excel format');
                                </script>";
                                exit;
                                }
                                else
                                {
                                echo "<script language=javascript1.2>
                                window.location='tempExcel/".$nop_.".xls';
                                </script>";
                                }
                                fclose($handle);
                                }

                        break;
				
            
                
                
                
                
            
            
		
            

	case'postingData':
                
            $nomor=$_POST['nomor'];
            $nourut=$_POST['nourut'];
            $alasan=$_POST['alasan'];

              
            $str="update ".$dbname.".log_perintaanhargaht set flag=1,catatanmenang='".$alasan."' "
                    . "where nomor='".$nomor."' and nourut='".$nourut."' ";
			try{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		
	break;     

		
		
		
	case'flagdt':
		$nodph=checkPostGet('nodph','');
		$nourutdph=checkPostGet('nourutdph','');
		$kdbrg=checkPostGet('kdbrg','');
		$ckbrg=checkPostGet('ckbrg','');
		
		$str="update ".$dbname.".log_permintaanhargadt set flag='".$ckbrg."' 
			where nomor='".$nodph."' and nourut='".$nourutdph."' and kodebarang='".$kdbrg."' ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
	
	break;	
	
	case'getalamat';
		$optalamat = "";
		$str="select * from ".$dbname.".log_5supalamat where supplierid = '".$supplier_id."' and status='1' order by alamat desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$optalamat.="<option value='".$bar['id_alamat']."'>".$bar['alamat']." ".$bar['kota']."</option>";
		}
		echo $optalamat;
	break;
	
	case'tolakrph':
		$str="select * from ".$dbname.".log_perintaanhargaht where nomor='".$notransaksi."'";
		$res=fetchData($str);
		$purchaser = $res[0]['purchaser'];
		
		$msgdt = "No RFQ ".$notransaksi." ditolak dengan alasan ".$alasan.", silahkan revisi dan ajukan kembali transaksi RFQ.";
		$str="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$notransaksi."','TRPH','".$msgdt."','".$purchaser."','0','0','".date('Y-m-d H:i:s')."')";
		try{
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".log_perintaanhargaht set flag='0', komentar='".$alasan."' where nomor='".$notransaksi."'";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";die(); 
		}
	break;
	
	case'formkometar':
		$tab="<div id=test style=display:block>
			<fieldset>
				<legend><input align=center class=myinputtext disabled type=text readonly=readonly value=".$notransaksi." style=\"min-width:175px;\"  /></legend>
				<table cellspacing=1 border=0>
		            <tr>
		                <td colspan=3>Catatan : </td>
		            </tr>
		            <tr>
						<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
		            </tr>
					<tr>
						<td>
							<button class=mybutton onclick=tolakrphbutton('".$notransaksi."')>".$_SESSION['lang']['save']."</button>
							<button class=mybutton onclick=closeDialog()>".$_SESSION['lang']['cancel']."</button>
						</td>
					</tr>
				</table> 
			</fieldset>
		</div>";
		
		echo $tab;
	break;

	default;
	break;
		
		
    }
	
function recursive_array_search($needle,$haystack) 
{
	foreach($haystack as $key=>$value) 
	{
		$current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) 
		{
			return $current_key;
        }
    }
    return false;
}
?>
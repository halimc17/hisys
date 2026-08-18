<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method = checkPostGet('method','');
$pages = checkPostGet('page','');

#PARAM CARI
$crnopp = checkPostGet('crnopp','');
$crperiode = checkPostGet('crperiode','');
$crpurchaser = checkPostGet('crpurchaser','');
$crunit = checkPostGet('crunit','');
$crkelbarang = checkPostGet('crkelbarang','');
$crbarang = checkPostGet('crbarang','');
$crstatus = checkPostGet('crstatus','');
$crjenis = checkPostGet('crjenis','');
$crstrategis = checkPostGet('crstrategis','');

$nopp = checkPostGet('nopp','');
$nourut = checkPostGet('nourut','');
$kodebarang = checkPostGet('kodebarang','');

$satuan = checkPostGet('satuan','');
$kdBrgSatuan = checkPostGet('kdBrgSatuan','');
$jmlh_realisai = checkPostGet('jmlh_realisai','');
$nourut = checkPostGet('nourut','');
$hjmlh_realisai = checkPostGet('hjmlh_realisai','');

$purchaser = checkPostGet('purchaser','');
$realisasi = checkPostGet('realisasi','');
$nokontrak = checkPostGet('nokontrak','');

$kodeorg = checkPostGet('kodeorg','');
$brsKe = checkPostGet('brsKe','');
$tahunprd = checkPostGet('tahunprd','');
$klmpKbrg = checkPostGet('klmpKbrg','');

$strategies = checkPostGet('strategies','');

$ket = checkPostGet('ket','');

//Umar
$jmlanak    = checkPostGet('jmlanak','');
$crkontrak  = checkPostGet('crkontrak','');
$crkontraka = checkPostGet('crkontraka','');
$crkategori = checkPostGet('crkategori','');
$tab 		= "";

$namabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
//End Umar


switch ($method)
{
	case 'loaddata':
		$tab="";
		// $tab.="<button onclick='formReturnBatch()' class='mybutton'>Drop</button>";
		/* 
		$tab.="<table class=\"sortable\" cellspacing=\"1\" border=\"0\">
			<thead>
			<tr class=rowheader>
				<th align='center'>No.</th>
				<th align='center' style='display:none'>".$_SESSION['lang']['kodeorg']."</th>
				<th align='center'>No PR/SR</th>
				<th align='center'>".$_SESSION['lang']['kodebarang']."</th>
				<th align='center'>".$_SESSION['lang']['kelompokbarang']."</th>
				<th align='center'>Strategies</th>
				<th align='center'>".$_SESSION['lang']['namabarang']."</th>
				<th align='center'>".$_SESSION['lang']['tipe']."</th>
				<th align='center' style='min-width:70px'>Prioritas</th>
				<th align='center' style='width:50px;display:none'>Advance Action</th>
				<th align='center'>".$_SESSION['lang']['chat']."</th>
				<th align='center' style='min-width:70px'>".$_SESSION['lang']['tanggal']." PR/SR</th>
				<th align='center' width=50px>".$_SESSION['lang']['jmlhDiminta']."</th>
				<th align='center'>".$_SESSION['lang']['jmlh_disetujui']."</th>
				<th align='center' width=30px>".$_SESSION['lang']['satuan']."</th>
				<th align='center' style='display:none'>O.sth</th>
				<th align='center'>".$_SESSION['lang']['purchaser']."</th>
				<th align='center'>Kontrak</th>
				<th align='center' width=60px style='display:none'>".$_SESSION['lang']['lokasitugas']."</th>
				<th align=\"center\" style='display:none'>View</th>
			</tr>
			</thead>
			<tbody>"; */
		
		$thnSkrng=date("Y");
        
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
		if($crnopp!='')
		{
			$where.=" and b.nopp like '%".$crnopp."%'";
		}
		
		if($crperiode!='')
		{
			$where.=" and a.tanggal LIKE '".$crperiode."%'";
		}
		
		if($crpurchaser!='')
		{
			$where.=" and b.nopp in (select nopp from ".$dbname.".log_listverifikasi where karyawanid = '".$crpurchaser."') and b.kodebarang in (select kodebarang from ".$dbname.".log_listverifikasi where karyawanid = '".$crpurchaser."')";
		}
		
		if($crunit!='')
		{
			$where.=" and b.nopp like '%".$crunit."%'";
		}
		
		if($crkelbarang!='')
		{
			$where.=" and left(b.kodebarang,3) = '".$crkelbarang."'";
		}
		
		if($crbarang!='')
		{
			$where.=" and b.kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where namabarang like '%".$crbarang."%')";
		}
		
		if($crstatus!='')
		{
			if($crstatus==0)
			{
				$where.=" and (b.nopp not in (select z.nopp from ".$dbname.".log_listverifikasi z where z.nopp=b.nopp and z.kodebarang=b.kodebarang))";
			}
			if($crstatus==1)
			{
				$where.=" and b.create_po = '".$crstatus."'";
			}
		}
		
		if($crjenis!='')
		{
			$where.=" and kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where jenis='".$crjenis."')";
		}
		
		if($crstrategis!=''){
			$where.=" and b.hargalama='".$crstrategis."'";
		}

		if ($crkontrak == '' || $crkontrak == 0) {
			$where .= " and b.nokontrak = ''";
		} else {
			$where .= " and b.nokontrak != ''";
		}

		if ($crkontraka != '') {
			$where .= " and b.nokontrak LIKE '%".$crkontraka."%'";
		}
		
        $no=(($page*$limit));

		$detailAkses = getOrgDetail(2);
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'])!=''){			
			$where.=" and a.unit in (".$detailAkses.")";
		// }
		
		
		// $str="select count(*) as jmlhrow FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
		// WHERE a.close > '1' and b.status='0' and b.create_po!='3' and substr(tanggal,1,4)='".$thnSkrng."' ".$where." ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc";
		
		$str="select * FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
		WHERE a.close > '1' and b.status='0' and b.create_po!='3' and substr(tanggal,1,4)<='".$thnSkrng."' ".$where." group by b.nopp, b.kodebarang ORDER BY purchaser asc ";
		$res=fetchdata($str);
		$jlhbrs= count($res);
		// echo $jlhbrs;
		// $tab.="<tr>
			// <td colspan=16>Total Items: ".$jlhbrs."</td>
		// </tr>";
		
		// #ambil list unit yang dimiliki
		// $where.=" and (unit in (select kodeorganisasi FROM ".$dbname.".organisasi where tipe in (select tipeorg FROM ".$dbname.".log_5list_purchaser where managerid='".$_SESSION['standard']['userid']."'))";
		// #ambil juga list unit yang tidak diassign kesiapapun (kalau gak diambil nanti pr gak akan pernah ter verifikasi)
		// // $where.=" or unit in (select kodeorganisasi FROM ".$dbname.".organisasi where tipe not in (select tipeorg FROM ".$dbname.".log_5list_purchaser) and length(kodeorganisasi)='4'))";
		// $detailAkses = getOrgDetail(2);
		// if(getUnitByMgrPurc($_SESSION['standard']['userid'])!=''){			
			$where.=" and unit in (".$detailAkses.")";
		// }
		
		$sPeriodeCari="select * from ".$dbname.".log_5kategoribarang order by id";
		$qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
		while($rPeriodeCari=$qPeriodeCari->fetch()){
			$optkat[$rPeriodeCari['id']]=$rPeriodeCari['jenis'];
		}

		if($crkategori!=''){
			$where.=" AND 
			CASE
				WHEN c.idkategorinya != '' and c.idkategorinya='1,4' and substr(a.unit,-1)='M' THEN substr(c.idkategorinya,1,1) ='".$crkategori."'
				WHEN c.idkategorinya != '' and c.idkategorinya='1,4' and substr(a.unit,-1)!='M' THEN substr(c.idkategorinya,-1) ='".$crkategori."'
				WHEN c.idkategorinya != '' and c.idkategorinya!='1,4' THEN c.idkategorinya ='".$crkategori."'
				WHEN d.idkategori != '' and d.idkategori='1,4' and substr(a.unit,-1)='M' THEN substr(d.idkategori,1,1) ='".$crkategori."'
				WHEN d.idkategori != '' and d.idkategori='1,4' and substr(a.unit,-1)!='M' THEN substr(d.idkategori,1,1) ='".$crkategori."'
				WHEN d.idkategori != '' and d.idkategori!='1,4' THEN d.idkategori ='".$crkategori."'
			END";
		}
		
        $str = "SELECT  
		CASE
			WHEN c.idkategorinya != '' and c.idkategorinya='1,4' and substr(a.unit,-1)='M' THEN '1'
			WHEN c.idkategorinya != '' and c.idkategorinya='1,4' and substr(a.unit,-1)!='M' THEN '4'
			WHEN c.idkategorinya != '' and c.idkategorinya!='1,4' THEN c.idkategorinya
			WHEN d.idkategori != '' and d.idkategori='1,4' and substr(a.unit,-1)='M' THEN '1'
			WHEN d.idkategori != '' and d.idkategori='1,4' and substr(a.unit,-1)!='M' THEN '4'
			WHEN d.idkategori != '' and d.idkategori!='1,4' THEN d.idkategori
		END
		as kategori,
		a.unit, a.`tanggal`, b.keteranganubah, a.`close`, a.pt, a.ket_balik,a.tipepp, b.*  
		FROM ".$dbname.".log_prapodt b  LEFT JOIN  ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
		LEFT JOIN ".$dbname.".log_5masterbarang c ON c.kodebarang = b.kodebarang and c.idkategorinya !=''
		LEFT JOIN ".$dbname.".log_5subklbarang d ON d.kode = substr(b.kodebarang,1,5)
		WHERE a.close > '1' and b.status='0' and b.create_po!='3' and substr(tanggal,1,4)<='".$thnSkrng."' ".$where."  
		group by b.nopp, b.kodebarang ORDER BY create_po asc, nopp asc limit ".$offset.",".$limit."";


		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$total=owlBaris($res);
		$res->setFetchMode(PDO::FETCH_OBJ);
			
		while($bar=$res->fetch())
		{
			$koderorg=substr($bar->nopp,15,4);
			$no+=1;
			
			$disabledrop=false;
			$arrpr = array();
			$arrstatus = array();
			$arrstatusrfq = array();
			$strpr="select * from ".$dbname.".log_listverifikasi where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."' group by karyawanid,nopp,kodebarang";
			$respr=$owlPDO->query($strpr) or die(print " Gagal: ".PDOException::getMessage());
			$respr->setFetchMode(PDO::FETCH_ASSOC);
			while($barpr=$respr->fetch())
			{
				$arrpr[] = $barpr['karyawanid'];
				$arrstatus[$bar->nopp][$bar->kodebarang][$barpr['karyawanid']] = $barpr['status'];
				if($barpr['status']=='1'){
					$arrstatusrfq[$bar->nopp][$bar->kodebarang]['jumlah']+=1;					
				}
				$disabledrop=true;
			}
			
			$sPoDet="select nopo from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
			$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
			$rCek=owlBaris($qPoDet);
			$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
			$rPoDet=$qPoDet->fetch();
			if($rCek>0)
			{
				$sPo="select tanggal,stat_release from ".$dbname.".log_poht where nopo='".$rPoDet['nopo']."'";
                $qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
				$qPo->setFetchMode(PDO::FETCH_ASSOC);
				$rPo=$qPo->fetch();
				
				$stat=$rPo['stat_release'];
                $nopo=$rPoDet['nopo'];
			}
			else
			{
				$tglPP=explode("-",$bar->tanggal);
				
				$date1 = $tglPP[2];
                $month1 = $tglPP[1];
                $year1 = $tglPP[0];
                $tgl2 = date("Y-m-d"); 
                $pecah2 = explode("-", $tgl2);
                $date2 = $pecah2[2];
                $month2 = $pecah2[1];
                $year2 =  $pecah2[0];
				$stat=0;					
			}
			
			@$jd1 = GregorianToJD($month1, $date1, $year1);
            @$jd2 = GregorianToJD($month2, $date2, $year2);
            @$jmlHari=@$jd2-@$jd1;
			
			$optPur="<option value=''></option>";
			$where=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tanggalmasuk<='".date("Y-m-d")."'";
			
			// $klq="select distinct purchaserid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."'";
			$klq="select distinct purchaserid from ".$dbname.".`log_5list_purchaser` where tipeorg in (select tipe from ".$dbname.".organisasi where kodeorganisasi='".$bar->unit."') ";
			// if(count(fetchdata($klq))==0){
			// 	$klq="select karyawanid as purchaserid,namakaryawan from ".$dbname.".`datakaryawan` where bagian='PRO'  ".$where." and karyawanid not in (select karyawanid from ".$dbname.".log_listverifikasi where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."') order by namakaryawan asc";				
			// }
			$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_OBJ);
			while($rst=$qry->fetch()){
				$optPur.="<option value=".$rst->purchaserid.">".getKary($rst->purchaserid)."</option>";
			}
			
			if($bar->lokalpusat!=0)
			{
				$ckh="checked=checked";
			}
			else
			{
				$ckh='';
			}
			
			$read_only2 = $stat_view = "";
			if($bar->create_po==1)
			{
				$read_only2="disabled=disabled";
				$ckh.=" disabled=disabled";
			}
			
			$skel="select kelompok from ".$dbname.".log_5klbarang where kode='".substr($bar->kodebarang,0,3)."'";
			$qkel=$owlPDO->query($skel) or die(print " Gagal: ".PDOException::getMessage());
			$qkel->setFetchMode(PDO::FETCH_OBJ);
			$br=$qkel->fetch();
			
			$optLokasi='';
			$cl=array(0=>'Head Office',1=>'Local');
			foreach($cl as $rw =>$isi)
			{
				$optLokasi.="<option '".($bar->lokalpusat==$rw?'selected=selected':'')."'value='".$rw."'>".$isi."</option>";
			}
			
			#periksa chat
			$strChat="select *  from ".$dbname.".log_pp_chat where kodebarang='".$bar->kodebarang."' and nopp='".$bar->nopp."'";
			$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
			if(owlBaris($resChat)>0)
			{
				$ingChat="<img src='images/chat1.png' onclick=\"loadPPChat('".$bar->nopp."','".$bar->kodebarang."',event);\" class=zImgBtn>";
			}
			else
			{
				$ingChat="<img src='images/chat0.png'  onclick=\"loadPPChat('".$bar->nopp."','".$bar->kodebarang."',event);\" class=zImgBtn>";
			}
			
			if($bar->keteranganubah!='')
			{
				$trStyle = "style='background-color:yellow'";
			}
			else
			{
				$trStyle = "";	
			}
			
			$rDtBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar->kodebarang."'");
			
			## GET PRIORITAS
			$optprioritas = makeOption($dbname,'log_5prioritas','kode,nama',"kode='".$bar->prioritas."'");
			$prioritas = $optprioritas[$bar->prioritas];
			
			$tab.="<tr class=rowcontent id='tr_".$no."' title='".$_SESSION['lang']['tgldibutuhkan'].":".tanggalnormal($bar->tgl_sdt)."' ".$trStyle.">";
			if($disabledrop==false){
				$tab.="<td align=center>".$no."<button id=tmbldrop".$no." title='Drop !' onclick=\"getDropData('".$bar->nopp."')\" class='mybutton'>Drop</button></td>";
			}else{				
				$tab.="<td align=center>".$no."<button id=tmbldrop".$no." style=display:none; title='Drop !' onclick=\"getDropData('".$bar->nopp."')\" class='mybutton'>Drop</button></td>";
			}
			$tab.="
				<input id=nomorurut".$no." name=nomorurut[]  value=".$no." hidden>
				<input id=flag".$no." name=flag[] hidden value=''>
				<td style='display:none'>".$koderorg."</td>
				<td name=nopp[] id=nopp_".$no." onclick=\"getDataPP2('".$bar->nopp."','".$no."')\" style=\"cursor:pointer;color:blue\">".$bar->nopp."</td>
				<td name=kodebarang[] id=kd_brg_".$no." onclick=\"previewDetail2('".$bar->nopp."','".$bar->kodebarang."',event)\" style='text-align:center;color:blue;cursor:pointer' title='Detail PR/SR'>".$bar->kodebarang."</td>
				<td>".$br->kelompok."</td>
				<td>".$optkat[$bar->kategori]."</td>
				";
			
			$stab="";
			$stab2="";
			$stab3="";
			$ckhstgs="";
			if($bar->hargalama!=0){
				$ckhstgs="checked=checked";
			}else{
				$ckhstgs='';
			}
			if($stat!=1){
				$stab.="<td align=\"center\">";
				$stab2.="<td align=\"center\">";
				$stab3.="<td align=\"center\">";
				if($bar->create_po==1){
					$stab2.="<input type=checkbox id=strategies_".$no."  ".$ckhstgs." disabled />";
				}else{
					if($arrstatusrfq[$bar->nopp][$bar->kodebarang]['jumlah'] >= '1'){
						$stab2.="<input type=checkbox id=strategies_".$no."  ".$ckhstgs." disabled />";
					}else{
						$stab.="<button class=mybutton id=balikinbutton_".$no." style='' onclick=\"formReturn('".$bar->nopp."','".$bar->kodebarang."','".$rDtBrg[$bar->kodebarang]."')\">Reject</button>";
						$stab2.="<input type=checkbox id=strategies_".$no."  ".$ckhstgs." onclick=\"simpanstrategies('".$no."','".$bar->nopp."','".$bar->kodebarang."')\" />";
						$stab3 .= "<input type='checkbox' class='checkChild' nopp='".$bar->nopp."' kodebarang='".$bar->kodebarang."'>";
					}
				}
				$stab.="</td>";
				$stab2.="</td>";
				$stab3.="</td>";
			}else{
				if($bar->create_po==1){
					$stab.="<td align=\"center\">".$_SESSION['lang']['release_po']."</td>";
					$stab2.="<td align=\"center\"><input type=checkbox id=strategies_".$no."  ".$ckhstgs." disabled /></td>";
				}else{
					if($arrstatusrfq[$bar->nopp][$bar->kodebarang]['jumlah'] >= '1'){
						$stab.="<td align=\"center\">".$_SESSION['lang']['release_po']."</td>";
						$stab2.="<td align=\"center\"><input type=checkbox id=strategies_".$no."  ".$ckhstgs." disabled /></td>";
					}else{
						$stab.="<td align=\"center\"><button class=mybutton id=balikinbutton_".$no." style='' onclick=\"formReturn('".$bar->nopp."','".$bar->kodebarang."','".$rDtBrg[$bar->kodebarang]."')\">Reject</button></td>";
						$stab2.="<td align=\"center\"><input type=checkbox id=strategies_".$no."  ".$ckhstgs." onclick=\"simpanstrategies('".$no."','".$bar->nopp."','".$bar->kodebarang."')\" /></td>";
						$stab3 .= "<td align='center'><input type='checkbox' class='checkChild' nopp='".$bar->nopp."' kodebarang='".$bar->kodebarang."'></td>";
					}
				}
			}
				
			$tab.=$stab2;
				
				
			if($bar->ket_balik!=''){
				$tab.="<td style='background-color:#64FB76'>".$rDtBrg[$bar->kodebarang]."<br>Become Out Standing : ".$bar->ket_balik."</td>";
			}else{
				$tab.="<td>".$rDtBrg[$bar->kodebarang]."</td>";
			}
			
			$tab.="<td style='text-align:center'>".tipepurchase($bar->tipepp)."</td>";
			$tab.="<td>".$bar->prioritas." - ".$prioritas."</td>";
			// $tab.=$stab;
                
			
			if(($bar->satuanpp)!='')
			{
				$selectedSat = '';
			}
			else
			{
				$selectedSat = 'selected';
			}
			
			//Satuan dari table master barang
			$strSat="select kodebarang,satuan from ".$dbname.".`log_5masterbarang` where  kodebarang='".$bar->kodebarang."' order by satuan";
			$qrySat=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
			$qrySat->setFetchMode(PDO::FETCH_ASSOC);
			$resSat=$qrySat->fetch();
			$optSat="<option value=".$resSat['satuan']." ".$selectedSat.">".$resSat['satuan']."</option>";
			
			//List satuan dari table konversi
			$strSat2="select kodebarang,satuankonversi from ".$dbname.".`log_5stkonversi` where  kodebarang='".$bar->kodebarang."' order by satuankonversi";
			$qrySat2=$owlPDO->query($strSat2) or die(print " Gagal: ".PDOException::getMessage());
			$qrySat2->setFetchMode(PDO::FETCH_OBJ);
			while($resSat2=$qrySat2->fetch()) 
			{
				if(($bar->satuankonversi)!='')
				{
					if(($bar->satuankonversi)==($resSat2->satuankonversi))
					{
						$optSat.="<option value=".$resSat2->satuankonversi." selected>".$resSat2->satuankonversi."</option>";
					}
					else
					{
						$optSat.="<option value=".$resSat2->satuankonversi.">".$resSat2->satuankonversi."</option>";
					}
				}
				else
				{
					$optSat.="<option value=".$resSat2->satuankonversi.">".$resSat2->satuankonversi."</option>";
				}
			}
			
			if($bar->satuankonversi==''){
				$real=hidezerodecimal($bar->jumlah,3);
			}else{
				$real=hidezerodecimal($bar->hasilkonversi,3);
			}

			if($bar->jumlahpp>=1) 
			{
				$juml=hidezerodecimal($bar->jumlahpp,3);
			}
			else 
			{
				$juml=hidezerodecimal($bar->jumlahpp,3);
			}
			
			$tab.="<td align=center>".$ingChat."</td>
			<td style='text-align:center'>".tanggalnormal($bar->tanggal)."</td>
			<td align=right>".$juml."</td>
			<td align=right>
				<input type=hidden disabled  id=hrealisasi_".$no." name=hrealisasi_".$no." onkeypress='return angka_doang(event)' class='myinputtextnumber' ".$read_only2." value='".$real."' style='width:60px;' />
				<input type=text disabled  id=realisasi_".$no." name=realisasi_".$no." onkeypress='return angka_doang(event)' class='myinputtextnumber' ".$read_only2." value='".$real."' style='width:60px;' />
			</td>
			<td align=center>
				<select id=satuan_".$no." id=satuan_".$no." ".$read_only2." onchange='getSatuanKonversi(this.value,".$bar->kodebarang.",".$no.")'>".$optSat."</select>
			</td>
			<td align=center title=\"Selisih Tanggal PP dengan Tanggal Hari ini\" style='display:none'>".$jmlHari."</td>
			<td>
				<div id='listpurchaser_".$no."'>
				<table width=100%>";
				if(isset($arrpr))
				{
					$next=0;
					foreach($arrpr as $key)
					{
						$optNamaKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$key."'");
						$next++;
						$tab.="<tr>
							<td style='text-align:right'>".$next.".</td>
							<td>".$optNamaKaryawan[$key]."</td>";
							
						if($bar->create_po==0)
						{
							if($arrstatus[$bar->nopp][$bar->kodebarang][$key]==0)
							{
								$tab.="<td>
									<img id='detail_delete_".$key."' title='Hapus' class=zImgBtn onclick=\"deletepurchaser('".$bar->nopp."','".$bar->kodebarang."','".$key."','".$no."')\" src='images/delete_32.png'/>
								</td>";
							}
						}
						$tab.="</tr>";
					}
				}
				if($bar->create_po==0)
				{
						$tab.="<tr>
						<td colspan=2>
							<select style=width:150px id=purchase_name_".$no." id=purchase_name_".$no." ".$read_only2.">".$optPur."</select>
						</td>
						<td>
							<img id='detail_add_".$no."' title='Tambah' class=zImgBtn onclick=\"addpurchase('".$bar->nopp."','".$bar->kodebarang."','".$no."')\" src='images/plus.png'/>
						</td>
					</tr>";
				}
				$tab.="</table>
				</div>
			</td>
			<td>";
			
			// $chktgl=date("Y-m-d"); 
			$chktgl=$bar->tanggal; 
			$strx="select b.nokontrak as nokontrak from ".$dbname.".log_kontrakpayungdt a left join ".$dbname.".log_kontrakpayung b on a.nokontrak=b.nokontrak where a.kodebarang='".$bar->kodebarang."' and (b.tanggalawal <= '".$chktgl."' and b.tanggalakhir >= '".$chktgl."') and b.status='1' and b.posting='1' group by b.nokontrak, a.kodebarang";
			$resx=fetchdata($strx);
			$countktrk = count($resx);
			if($countktrk > 0){
				$optktrk="<option value=''></option>";
				if($bar->nokontrak == $resx[0]['nokontrak']){
					$optktrk.="<option value='".$resx[0]['nokontrak']."' selected>".$resx[0]['nokontrak']."</option>";
				}else{
					$optktrk.="<option value='".$resx[0]['nokontrak']."'>".$resx[0]['nokontrak']."</option>";
				}
				if($bar->create_po == 0){
					$tab.="<select style=width:150px id=nokontrak_".$no." id=nokontrak_".$no." ".$read_only2." onchange=\"addkontrak('".$bar->nopp."','".$bar->kodebarang."','".$no."')\">".$optktrk."</select>";
				} else {
					$tab .= "<a style='cursor:pointer' onclick='viewKontrakDetail(`".$resx[0]['nokontrak']."`)'>".$resx[0]['nokontrak']."</a>";
				}
			}
			
			$tab.="</td>";
			
			$tab.="<td align=center style='display:none'><input type=checkbox id=lokalpusat_".$no."  ".$ckh." /> Local</td>
			<td align=center style='display:none'>
				<img style='display:none' src=images/save.png class=zImgBtn  title='Save' onclick=\"AddPur('".$no."');\">
				<img style='display:none' src=images/application/application_edit.png class=zImgBtn  title='Edit Data' onclick=\"EditPur('".$no."');\">
				<img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('log_prapoht','".$bar->nopp."','','log_slave_print_log_pp',event);\">
			</td>";
			// $tab .= $stab3;
			$tab .= "</tr>";
		}
		
		$colspan=19;

		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPagex');
		$tab.="</tbody>
		</table>";
		echo $tab;
	break;
	
	case 'listVerivikasiPP':
		$tab="";
		$optPur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where  bagian='PRO' and tanggalkeluar='0000-00-00' order by namakaryawan asc ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$optPur.="<option value=".$bar->karyawanid.">".$bar->namakaryawan."</option>";
		}
		
		$cl=array(0=>'Head Office',1=>'Local');
		
		foreach($cl as $rw =>$isi)
		{
			$optLokasi.="<option  value='".$rw."'>".$isi."</option>";
		}
		
		$str="SELECT  distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.
			`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, 
			a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, 
			a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*,c.nopo FROM ".$dbname.".log_prapodt b 
			LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
			LEFT JOIN ".$dbname.".log_podt c ON b.nopp=c.nopp  
			WHERE b.nopp='".$nopp."' and create_po!='1' and status!=3  group by kodebarang ORDER BY a.tanggal desc ";
		$res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res2);

		$tab.="<input type=\"hidden\" id=ppno name=ppno value=".$nopp." />
		<fieldset><legend>".$nopp."</legend> 
		<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				<td colspan=3>Verification Form</td>
			</tr>
			</thead>
			<tbody>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['jumlah']." Item</td><td id=totalBrg>".$row."</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['purchaser']."</td><td><select id=purId2 name=purId2 style=width:150px; onchange='getlokId(this.value,".$nourut.")'>".$optPur."</select></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['lokasitugas']."</td><td><select id=lokId name=lokId style=width:150px;>".$optLokasi."</select></td>
			</tr>
			<tr>
				<td colspan=3><button class=mybutton onclick=saveSemua(1) id=saveAll title=Simpan Semua>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button><button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
			</tbody>
		</table><br />";

		$tab.="<fieldset><legend>".$_SESSION['lang']['list']." Item</legend>
		<div  style=overflow:auto;width:650px;height:275px;>
		<table class=\"sortable\" cellspacing=\"1\" border=\"0\" cellpadding=5>
			<thead>
			<tr class=rowheader>
				<td>No.</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['tanggal']." PP</td>
				<td>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['harga']."</td>
			</tr>
			</thead>
			<tbody>";
            
		$res2->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res2->fetch())
		{			
			$koderorg=substr($bar->nopp,15,4);
			$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; $rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_ASSOC);
			$bas=$rep->fetch();
			
			$no+=1;
			$sPoDet="select distinct hargasatuan from ".$dbname.".log_podt where  kodebarang='".$bar->kodebarang."' order by nopo desc";
			$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
			$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qPoDet->fetch();
			
			if($bar->realisasi=='')
			{
				$bar->realisasi=0;
			}

			$tab.="<tr class=rowcontent id='rew_".$no."'>
				<td>".$no."</td>
				<td id=kdBrg_".$no.">".$bar->kodebarang."</td>
				<td>".substr($rDtBrg[$bar->kodebarang],0,33)."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td align=center id=jmlh_".$no.">".$bar->jumlah."</td>
				<td align=center  >".$bar->realisasi."</td>
				<td align=right  >".number_format($rCek['hargasatuan'],2)."</td>
			</tr>";
		}

		$tab.="</tbody>
			</table></div></fieldset></fieldset>";
			
		echo $tab;
	break;
	
	case'ReturnlistPP':
		$tab="";
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['form']."</legend>
			<table border=0>
				<tr>
					<td>".$_SESSION['lang']['nopp']."</td>
					<td>:</td>
					<td>
						<input type=text value='".$nopp."' style=width:150px class=myinputtext disabled />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>:</td>
					<td>
						<input type=text value='".$_POST['kdbrg']."' style=width:150px class=myinputtext disabled />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>:</td>
					<td>
						<input type=text value='".$_POST['nmbrag']."' style=width:250px class=myinputtext disabled />
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['keterangan']."</td>
					<td>:</td>
					<td>
						<input type=text id=ket style=width:250px class=myinputtext onkeypress='return tanpa_kutip(event)' />
					</td>
				</tr>
				<tr>
					<td colspan=2>&nbsp;</td>
					<td>
						<button class=mybutton onclick=balikin('".$nopp."','".$_POST['kdbrg']."')>".$_SESSION['lang']['save']."</button>
					</td>
				</tr>
			</table>
		</fieldset>";
		
		echo $tab;
	break;
	
	case 'getValueKonversi':
		$str="select * from ".$dbname.".`log_5stkonversi` where kodebarang = '".$kdBrgSatuan."' and satuankonversi='".$satuan."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();
		$bar=$res->fetch();
		
		if($numrows>0)
		{
			$hasilAkhir = $jmlh_realisai * $bar['jumlah'];
			$hasAkhr = $hasilAkhir;
		}else
		{
			$hasAkhr = $hjmlh_realisai;
		}
		
		$str="select satuanpp,jumlah from ".$dbname.".log_prapodt where kodebarang = '".$kdBrgSatuan."' and nopp='".$nopp."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$konversi = ($satuan==$bar['satuanpp']?'':$satuan);
		$hasAkhr2 = ($satuan==$bar['satuanpp']?$bar['jumlah']:$hasAkhr);
		
		$str = "update ".$dbname.".log_prapodt set satuankonversi='".$konversi."',hasilkonversi='".$hasAkhr2."' where nopp='".$nopp."' and kodebarang='".$kdBrgSatuan."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
		echo $hasAkhr2;
	break;
	
	case'addpurchase':
		
		#= Jika ambil dari ini,
		#= Ada bug ketika tambah purchase maka akan ambil nilai yang paling atas saja
		$realisasi=str_replace(',', '', $realisasi);

		if($purchaser=='')
		{
			exit("Warning : Nama Purchaser harus dipilih.");
		}
		
		$str="select nokontrak from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$res=fetchdata($str);
		if($res[0]['nokontrak']!=''){
			echo $res[0]['nokontrak'];
			$str="select count(karyawanid) as jmlhrow from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
			$res=fetchdata($str);
			$jlhpcr = $res[0]['jmlhrow'];
			if($jlhpcr >= 1){
				exit("Gagal, Purchaser tidak bisa lebih dari 1 Orang (Kontrak Payung).");
			}
		}
		
		$str="select count(karyawanid) as jmlhrow from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' and karyawanid='".$purchaser."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jlhbrs= $bar['jmlhrow'];
		
		if($jlhbrs > 0)
		{
			exit("Warning : Nama Purchaser sudah pernah tedaftar untuk item ini");
		}
		
		$str = "select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang not in (select kodebarang from ".$dbname.".log_listverifikasi  where nopp='".$nopp."' and kodebarang='".$kodebarang."' and karyawanid='".$purchaser."')";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodebarang=$bar['kodebarang'];
			
			#= Updated by Abdul
			#= Karena jika pake $realisasi di line 726 akan ada bug
			#= Ketika case nya barang lebih dari 1
			#= dan pilih purchase akan otomatis ngikutin nilai akutual dari yang paling atas
			$realisasi=$bar['jumlahpp'];
			
			#= End Abdul

			$str = "insert into ".$dbname.".log_listverifikasi values ('','".$nopp."','".$kodebarang."','".$purchaser."','0','0','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".count($req)."','')";
			
			try
			{
				$owlPDO->exec($str);

				$tglHrini = date("Ymd");
				$str = "update ".$dbname.".log_prapodt set realisasi='".$realisasi."',tglAlokasi='".$tglHrini."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
				try
				{
					$owlPDO->exec($str);
				}
				catch(PDOException $e)
				{
					echo " Gagal," . addslashes($e->getMessage());
				}
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		
	break;
	
	case'addkontrak':
		$str="update ".$dbname.".log_prapodt set nokontrak='".$nokontrak."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$owlPDO->exec($str);
		
		$str="delete from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$owlPDO->exec($str);
	break;
	
	case'addpurchase2':
		if($purchaser=='')
		{
			exit("Warning : Nama Purchaser harus dipilih.");
		}
		
		array_push($_SESSION['listpurchaser'],$purchaser);
		
		// echo"<pre>";
		// print_r($_SESSION['listpurchaser']);
		// echo"</pre>";
		// exit("error");
		
		// $str="select count(karyawanid) as jmlhrow from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' and karyawanid='".$purchaser."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// $jlhbrs= $bar['jmlhrow'];
		
		// if($jlhbrs > 0)
		// {
			// exit("Warning : Nama Purchaser sudah pernah tedaftar untuk item ini");
		// }
				
		// $str = "insert into ".$dbname.".log_listverifikasi values ('','".$nopp."','".$kodebarang."','".$purchaser."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','')";
		
		// try
		// {
			// $owlPDO->exec($str);
			
			// $str = "update ".$dbname.".log_prapodt set realisasi='".$realisasi."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
			// try
			// {
				// $owlPDO->exec($str);
			// }
			// catch(PDOException $e)
			// {
				// echo " Gagal," . addslashes($e->getMessage());
			// }
		// }
		// catch(PDOException $e)
		// {
			// echo " Gagal," . addslashes($e->getMessage());
		// }
	break;
	
	case'loadpurchaser2':
		$tab="";
		
		$optPur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."'";
		if(count(fetchdata($str))>0){			
			$klq="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."' and purchaserid not in ('".implode($_SESSION['listpurchaser'], "', '")."')";
		}else{
			$klq="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where  bagian like '%PRO%' and tanggalkeluar='0000-00-00' and karyawanid not in ('".implode($_SESSION['listpurchaser'], "', '")."') order by namakaryawan asc ";
		}
		$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		while($rst=$qry->fetch())
		{
			$optPur.="<option value=".$rst->karyawanid.">".getKary($rst->karyawanid)."</option>";
		}
		
		$tab.="<table>";
		if(isset($_SESSION['listpurchaser']))
		{
			$next=0;
			foreach($_SESSION['listpurchaser'] as $key=>$val)
			{
				$optNamaKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val."'");
				$next++;
				$tab.="<tr>
					<td style='text-align:right'>".$next.".</td>
					<td>".$optNamaKaryawan[$val]."</td>
					<td>
						<img id='detail_delete_".$val."' title='Hapus' class=zImgBtn onclick=\"deletepurchaser2('".$nopp."','".$key."')\" src='images/delete_32.png'/>
					</td>";
				$tab.="</tr>";
			}
			$tab.="<tr>
				<td colspan=2>
					<select id=purId2_2 name=purId2_2 style=width:150px;>".$optPur."</select>
				</td>
				<td>
					<img id='detailadd2' title='Tambah' class=zImgBtn onclick=\"addpurchase2('".$nopp."')\" src='images/plus.png'/>
				</td>
			</tr>";
		}
		$tab.="</table>";
		
		echo $tab;
	break;
	
	case'loadpurchaser':
		$tab="";
		// ambil UNIT
		$str_unit="select unit from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res_unit=fetchdata($str_unit);
		$res_unit_status = $res_unit[0]['unit'];
		
		$optPur="<option value=''></option>";
		

		$str="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where tipeorg in (select tipe from ".$dbname.".organisasi where kodeorganisasi='".$res_unit_status."') ";
		// $str="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."'";
		// if(count(fetchdata($str))>0){	
		// 	$str="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."' and purchaserid not in (select karyawanid from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."')";
		// }else{
		// 	$str="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where bagian='PRO'  and tanggalkeluar='0000-00-00' and karyawanid not in (select karyawanid from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."') order by namakaryawan asc ";
		// }
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{
			$optPur.="<option value=".$bar->karyawanid.">".getKary($bar->karyawanid)."</option>";
		}
		
		$arrpr = array();
		$arrstatus = array();
		$strpr="select * from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' group by karyawanid,nopp,kodebarang";
		$respr=$owlPDO->query($strpr) or die(print " Gagal: ".PDOException::getMessage());
		$respr->setFetchMode(PDO::FETCH_ASSOC);
		while($barpr=$respr->fetch())
		{
			$arrpr[] = $barpr['karyawanid'];
			$arrstatus[$nopp][$kodebarang][$barpr['karyawanid']] = $barpr['status'];
		}
		
		$tab.="<table width=100%>";
		if(isset($arrpr))
		{
			$next=0;
			foreach($arrpr as $key)
			{
				$optNamaKaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$key."'");
				$next++;
				$tab.="<tr>
					<td style='text-align:right'>".$next.".</td>
					<td>".$optNamaKaryawan[$key]."</td>";
					if($arrstatus[$nopp][$kodebarang][$key]==0)
					{
						$tab.="<td>
							<img id='detail_delete_".$key."' title='Hapus' class=zImgBtn onclick=\"deletepurchaser('".$nopp."','".$kodebarang."','".$key."','".$nourut."')\" src='images/delete_32.png'/>
						</td>";
					}
				$tab.="</tr>";
			}
		}
		
		$tab.="<tr>
			<td colspan=2>
				<select style=width:150px id=purchase_name_".$nourut." id=purchase_name_".$nourut.">".$optPur."</select>
			</td>
			<td>
				<img id='detail_add_".$nourut."' title='Tambah' class=zImgBtn onclick=\"addpurchase('".$nopp."','".$kodebarang."','".$nourut."')\" src='images/plus.png'/>
			</td>
		</tr>
		</table>";
		
		echo $tab;
	break;
	
	case'deletepurchaser':
		$str="select status from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' and karyawanid='".$purchaser."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['status']==1)
		{
			exit("warning : Tidak dapat hapus data, purchaser sudah melakukan riwayat perbandingan harga. Silahkan reload frame browser anda.");
		}
	
		$str = "delete from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' and karyawanid='".$purchaser."'";
		
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'deletepurchaser2':
		unset($_SESSION['listpurchaser'][$purchaser]);
	break;
	
	case'loadBarang':
		$optBarang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKodenbarna="select distinct kodebarang,namabarang from ".$dbname.".log_5masterbarang where left(kodebarang,3) = '".$klmpKbrg."' order by namabarang asc";
		$qKodeBarang=$owlPDO->query($sKodenbarna) or die(print " Gagal: ".PDOException::getMessage());
		$qKodeBarang->setFetchMode(PDO::FETCH_ASSOC);
		while($rKodebarang=$qKodeBarang->fetch())
		{
			$optBarang.="<option value='".$rKodebarang['kodebarang']."'>".$rKodebarang['namabarang']."</option>";
		}
		echo $optBarang;
	break;
	
	case'loadTools':
		$tab="<table class=sortable border=0 cellspacing=1 cellpadding=1><thead>
			<tr class=rowheader>
				<th style='text-align:center;padding-right:10px;padding-left:10px;padding-bottom:10px;padding-top:10px'>No.</th>
				<th style='text-align:center;padding-right:10px;padding-left:10px;padding-bottom:10px;padding-top:10px'>".$_SESSION['lang']['kodept']."</th>
				<th style='text-align:center'>".$_SESSION['lang']['pt']."</th>
			</tr>
			</thead><tbody>
			";
		
		$sql="select distinct pt  from ".$dbname.".log_prapoht where nopp in (select a.nopp from ".$dbname.".log_prapoht a left join ".$dbname.".log_prapodt b on a.nopp=b.nopp where close=2 and b.status<3 and purchaser=0)";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		
		$no=0;
		while($res=$query->fetch()) 
		{
			$no+=1;
			$optNm = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['pt']."'");
			$tab.="<tr class=rowcontent onclick=detailPo(".$no.") style='cursor:pointer;height:25px'>
				<td align=center>".$no."</td>
				<td align=center id=kodeOrg_".$no.">".$res['pt']."</td>
				<td>".$optNm[$res['pt']]."</td></tr>";
			$tab.="<tr class=rowcontent><td colspan=3><div id=dataPO_".$no."></div></td></tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
	break;
	
	case'loadPPDetail':
		$tab="<img onclick=\"closeList(".$brsKe.");\" title=\"Tutup\" class=\"zImgBtn\" src=\"images/close.gif\">";
		$tab.="<table cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<th align=center>No</th>
				<th>".$_SESSION['lang']['nopp']."</th>
				<th>".$_SESSION['lang']['unit']."</th>
				<th>".$_SESSION['lang']['jumlah']."</th>
			</tr>
			</thead><tbody>";
			
		$sql2="select b.kodebarang,a.pt,b.nopp from ".$dbname.".log_prapodt b left join ".$dbname.".log_prapoht a on a.nopp=b.nopp 
			where purchaser=0000000000 and status<3 and pt='".$kodeorg."' and a.close=2 group by a.nopp order by substring(a.nopp,16,4) asc";
		$query=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$jmlData=owlBaris($query);
		
		$tab.="<tr  class=rowcontent><td colspan=5>Total PP :".$jmlData."</td></tr>";
		$no=0;
		while($rwd=$query->fetch())
		{
			$sJum="select count(kodebarang) as jumlah from ".$dbname.".log_prapodt where nopp='".$rwd['nopp']."' and purchaser=0000000000 and status<3";
			$qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
			$qJum->setFetchMode(PDO::FETCH_ASSOC);
			$rJum=$qJum->fetch();
			$no+=1;
			$koderorg=substr($rwd['nopp'],15,4);
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td  onclick=\"getDataPP2('".$rwd['nopp']."')\" style=\"cursor:pointer\">".$rwd['nopp']."</td>
				<td align=center>".$koderorg."</td>
				<td align=right>".$rJum['jumlah']."</td>
				</tr>";
        }
		$tab.="</tbody></table>";
		echo $tab;
	break;
	
	case 'listVerivikasiPP2':
		$_SESSION['listpurchaser'] = array();
		$tab="";
		$optPur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		// $klq="select distinct purchaserid as karyawanid from ".$dbname.".`log_5list_purchaser` where managerid='".$_SESSION['standard']['userid']."'";
		// if(count(fetchdata($klq))==0){
		// 	$klq="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where  bagian like '%PRO%' and tanggalkeluar='0000-00-00' order by namakaryawan asc ";
		// }

		$str="select unit from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$unit=$res[0]['unit'];

			$where=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tanggalmasuk<='".date("Y-m-d")."'";
			
			$klq="select distinct purchaserid from ".$dbname.".`log_5list_purchaser` where tipeorg in (select tipe from ".$dbname.".organisasi where kodeorganisasi='".$unit."') ";
			$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_OBJ);
			while($rst=$qry->fetch()){
				$optPur.="<option value=".$rst->purchaserid.">".getKary($rst->purchaserid)." </option>";
			}
        
		$cl=array(0=>'Head Office',1=>'Local');
		$optLokasi = '';
		foreach($cl as $rw =>$isi)
		{
			$optLokasi.="<option  value='".$rw."'>".$isi."</option>";
		}
		
		// $str="SELECT  distinct a.`tanggal`,b.*,c.nopo, a.pt FROM ".$dbname.".log_prapodt b 
		// LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
		// LEFT JOIN ".$dbname.".log_podt c ON b.nopp=c.nopp  
        // WHERE b.nopp='".$nopp."' and b.create_po not in ('1','3') and b.status!='3' and b.kodebarang not in (select kodebarang from ".$dbname.".log_listverifikasi where nopp = '".$nopp."') group by b.kodebarang ORDER BY a.tanggal desc ";
		$str="SELECT  distinct a.`tanggal`,b.*, a.pt FROM ".$dbname.".log_prapodt b 
		LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
        WHERE b.nopp='".$nopp."' and
		b.kodebarang not in (select kodebarang from ".$dbname.".log_listverifikasi where nopp = '".$nopp."') 
		group by b.kodebarang ORDER BY a.tanggal desc ";
		$res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res2);
		
		$tab.="<input type=\"hidden\" id=ppno name=ppno value=".$nopp." />
		<fieldset><legend>".$nopp."</legend>
		<fieldset><legend>".$_SESSION['lang']['list']." Item</legend>
		<div  style=overflow:auto;width:650px;max-height:275px;>
		<table class=\"sortable\" cellspacing=\"1\" border=\"0\" width=100% cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']." PP</th>
				<th align=center>".$_SESSION['lang']['jmlhDiminta']."</th>
				<th align=center>Jumlah di Setujui</th>
				<th align=center>#</th>
				 
			</tr>
			</thead>
			<tbody>";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch())
		{			
			$koderorg=substr($bar->nopp,15,4);
			$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; 
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_OBJ);
			$bas=$rep->fetch();
			
			$no+=1;
			$sPoDet="select nopo from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
			$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
			$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qPoDet->fetch();
			
			if($bar->realisasi==''||$bar->realisasi==0)
			{
				$bar->realisasi=$bar->jumlah;
			}

			$tmpTgl = explode('-',$bar->tanggal);
			$tahunprd=$tmpTgl[0];
			$tgldari = "01-01-".$tmpTgl[0];
			$rDtBrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar->kodebarang."'");
			$tab.="<tr class=rowcontent id='rew_".$no."'>
				<td align=center>".$no."</td>
				<td align=center id=kdBrg_2_".$no." style='text-align:center;color:blue;cursor:pointer' onclick=showdocpakaibarang('".$tgldari."','".tanggalnormal($bar->tanggal)."','".$koderorg."','".$bar->kodebarang."','event')>".$bar->kodebarang."</td>
				<input type=hidden id=kdbrg_".$no." value=".$bar->kodebarang.">
				<td style='text-align:left;color:blue;cursor:pointer' onclick=showdocpembelian('".$tahunprd."','".$bar->pt."','".$bar->kodebarang."','event')>".$rDtBrg[$bar->kodebarang]."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td align=center id=jmlh_2_".$no.">".$bar->jumlahpp."</td>
				<td align=center  >".$bar->jumlah."</td>
				<td>";
				if($bar->create_po!=1)
				{
					$tab.="<input type=checkbox id=pilih_".$no.">";
				}
				$tab.="</td></tr>";
			
		}
		$tab.=" </tbody>
		</table></div></fieldset><br />";

		$tab.="<fieldset><legend>".$_SESSION['lang']['list']." File</legend>
		<table class='sortable' cellspacing='1' border='0' cellpadding=5 valign=top>
			<thead>
			<tr class=rowheader>
				<th align='center' width=50px>No.</th>
				<th align='center' width=50px>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center' width=55px>Action</th>
			</tr>
			</thead><tbody>";

		$no = 0;
		$str="select * from ".$dbname.".log_prapoht where nopp = '".$nopp."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv)
		{
			$close = $barv['close'];	
		}
		
		$str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopp."' and status='1'";
		$res1=fetchData($str);
		foreach($res1 as $key=>$val)
		{
			$no++;
			$tab.="<tr id='ppDetailTable' class=rowcontent>
				<td style='text-align:center'>".$no."</td>";
				
			if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn title='JPG'></a>
				</td>";
			}
			elseif($val['formaticon']=='.png')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/png.png class=zImgBtn  title='PNG'></a>
				</td>";
			}
			elseif($val['formaticon']=='.pdf')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/pdf.png class=zImgBtn  title='PDF'></a>
				</td>";
			}
			elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/excel.png class=zImgBtn  title='xls'></a>
				</td>";
			}
			elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/word.png class=zImgBtn  title='doc'></a>
				</td>";
			}
			else
			{
				$tab.="<td style='text-align:center'>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/jpg.png class=zImgBtn  title='jpg'></a>
				</td>";
			}
			
			$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
			<td style='text-align:left'>".$val['namafile']."</td>
				<td align=center>
					<a href='fileupload/pp/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp";
			if($close==0){
				$tab.="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
			}
			$tab."	</td>
			</tr>";
		}

		$tab.="</tbody></table></fieldset><br />";
		
		$tab.="
		<fieldset>
		<table cellspacing=1 border=0 class=sortable cellpadding=5>
			<thead>
			<tr class=rowheader>
				<th colspan=3>Form Verifikasi</th>
			</tr>
			</thead>
			<tbody>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['jumlah']." Item</td>
				<td id=totalBrg_2>".$row."</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['purchaser']."</td>
				<td>
					<div id='listpurchaser2'>
						<select id=purId2_2 name=purId2_2 style=width:150px;>".$optPur."</select>
						<img id='detailadd2' title='Tambah' class=zImgBtn onclick=\"addpurchase2('".$nopp."')\" src='images/plus.png'/>
					</div>
				</td>
			</tr>
			<tr class=rowcontent style='display:none'>
				<td colspan=2>".$_SESSION['lang']['lokasitugas']."</td>
				<td><select id=lokId_2 name=lokId_2 style=width:150px;>".$optLokasi."</select></td></tr>
			<tr>
				<td colspan=3 align=right>
					<button class=mybutton onclick=savepilih(".$row.") id=savepilih title='Simpan yang dicentang'>Simpan yang dicentang</button>
					<button class=mybutton onclick=saveSemua2(1) id=saveAll2 title='Save All'>".$_SESSION['lang']['saveall']."</button>
					<button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td></tr>
			</tbody>
		</table>";

		$tab.="<br /></fieldset></fieldset>";
		echo $tab;
	break;

	case 'showdocpembelian':
		$stream.= "<table class=sortable cellspacing=1>";
		$stream.="<thead><tr class=rowheader>
		            <td align=center>No</td>
		            <td align=center>" . $_SESSION['lang']['nopo'] . "</td>   
		            <td align=center>" . $_SESSION['lang']['nopp'] . "</td>   
		            <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
		            <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
		            <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
		            <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>  
		        </tr></thead>
		      <tbody>";

		$iList = "SELECT * FROM " . $dbname . ".log_po_vw where kodebarang='".$kodebarang."' and kodeorg='".$kodeorg."' and 
		tanggal LIKE '".$tahunprd."%' ";
		$nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
		$nList->setFetchMode(PDO::FETCH_ASSOC);
		$totaljumlah = 0;
		while ($dList = $nList->fetch()) {
		    $no+=1;
		    $stream.="<tr class=rowcontent>";
		    $stream.="
		        <td align=center  style='width:50px;'>" . $no . "</td>
		        <td align=left  style='width:150px;'>" . $dList['nopo'] . "</td>
		        <td align=left  style='width:150px;'>" . $dList['nopp'] . "</td>
		        <td align=left  style='width:100px;'>" . tanggalnormal($dList['tanggal']) . "</td>           
		        <td align=center  style='width:100px;'>" . $dList['kodebarang'] . "</td>     
		        <td align=left  style='width:300px;'>".$dList['namabarang']."</td>    
		        <td align=right  style='width:100px;'>" . number_format($dList['jumlahpesan'], 2) . "</td> 
		        </tr>";
		    $totaljumlah+=$dList['jumlahpesan'];
		}
		$stream.="<tr class=rowcontent style='font-weight:bold'>";
		$stream.="<td colspan=6 style='text-align:center'>" . $_SESSION['lang']['total'] . "</td>";
		$stream.="<td style='text-align:right'>" . number_format($totaljumlah, 2) . "</td>";

		$stream.="</tr>";

		$stream.="</tbody></table>";

		echo $stream;
	break;
	
	case'balikin':
		$str="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$res=fetchdata($str);
		$dibuat=$res[0]['dibuat'];
		
		$str="update ".$dbname.".log_prapodt set status='3',purchaser='0', alasanstatus='".$ket."', ditolakoleh='".$_SESSION['standard']['userid']."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$owlPDO->exec($str);
		
		$str="delete from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."' and status='0' and pemenang='0'";
		$owlPDO->exec($str);
		
		## CREATE NOTIFICATION
		$msgdt = "PR/SR dengan No ".$nopp." dan kodebarang : ".$kodebarang." ditolak dengan alasan ".$ket."";
		createnotif($nopp,'TOLAKPR',$msgdt,$dibuat,date('Y-m-d H:i:s'));
	
		// $countrph = 0;
		// $str="select * from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch())
		// {
			// if($bar['status']==1)
			// {
				// $countrph+=1;
			// }
		// }
		
		// // if($countrph>=1)
		// // {
			// // exit("Warning : Sudah ada RPH untuk item ini. Hapus terlebih dahulu RPH untuk item ini dan lanjut untuk return PR/SR");
		// // }
		
		
		// #= return disini
		// #= insert ke approval_return
		// #= ambil data aproval prnya
		// #= delete dulu yg lama
		// // $str="delete from ".$dbname.".approval_return where notransaksi='".$nopp."'";
		// // try{ 
			// // $owlPDO->exec($str); 
		// // } catch (PDOException $e) {
			// // echo " Gagal delete approval_return ".addslashes($e->getMessage()."__".$str);
		// // }
		
		// $str="select * from ".$dbname.".approval where notransaksi='".$nopp."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// if ($bar['status']==0) {
				// $bar['status']=1;
			// }
			// #= insert 
			// $strins="insert into ".$dbname.".approval_return values 
					// ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."',
					// '".$bar['komentar']."','".$kodebarang."##".$ket."','".$bar['tanggal']."')";
			// try{ 
				// $owlPDO->exec($strins); 
			// } catch (PDOException $e) {
				// echo " Gagal insert aproval_return ".addslashes($e->getMessage()."__".$strins);
			// }
			
		// }
		
		// $str="select karyawanid from ".$dbname.".approval where notransaksi='".$nopp."' and jenispersetujuan='PR' and level='1'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
        // $pTiga=$bar['karyawanid'];
		
		// // exit("Error:A");
		
		
		
		// ##update kodebarang yg di return dengan menghilangkan purchasernya agar tidak masuk di-inbox
		// $str="delete from ".$dbname.".log_listverifikasi where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		// try
		// {
			// $owlPDO->exec($str);
			
			// $str="delete from ".$dbname.".approval where notransaksi='".$nopp."' and jenispersetujuan='PR' and level!='1'";
			// try
			// {
				// $owlPDO->exec($str); 
				
				// $str="update ".$dbname.".approval set status='0' where notransaksi='".$nopp."' and jenispersetujuan='PR' and level='1'";
				
				// try
				// {
					// $owlPDO->exec($str); 
					
					// $str="update ".$dbname.".log_prapoht set close=3, ket_balik='".$ket."' where nopp='".$nopp."'";
					// try
					// {
						// $owlPDO->exec($str); 
						
						// $str="update ".$dbname.".log_prapodt set create_po=3 where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
						// try
						// {
							// $owlPDO->exec($str);
							
							// //sukses langsung kirim email
							// $optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
							// $to=getUserEmail($pTiga);
							// $namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
							// $subject="[Notifikasi]Perubahan barang ".$nopp." ";
							// $body="<html>
								// <head>
								// <body>
									// <dd>Dengan Hormat,</dd><br>
									// <br>
									// Sehubungan dengan tidak adanya barang/alasan lain dinopp : ".$nopp." untuk :
									// <br>Kode : ".$kodebarang."</br>
									// <br>Nama : ".$optNmBarang[$kodebarang]."</br>
									// <br>Keterangan : ".$keterangan."</br>
									// <br>
									// harap untuk membuat PR baru atas kodebarang tersebut.
									// <br>
									// <br>
									// Regards,
									// Procurment Dept.<br>
									// Owl-Plantation System.
								// </body>
								// </head>
							// </html>";
							// $kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;			
							// echo "Data sudah dikembalikan ";
						// }
						// catch(PDOException $e)
						// {
							// print " Gagal  !: " . $e->getMessage() . "\n"; 
							// die(); 
						// }						
					// }
					// catch(PDOException $e)
					// {
						// print " Gagal  !: " . $e->getMessage() . "\n"; 
						// die(); 
					// }			
				// }
				// catch(PDOException $e)
				// {
					// print " Gagal  !: " . $e->getMessage() . "\n"; 
					// die(); 
				// }
			// }
			// catch(PDOException $e)
			// {
				// print " Gagal  !: " . $e->getMessage() . "\n"; 
				// die(); 
			// }
		// }
		// catch(PDOException $e)
		// {
			// print " Gagal  !: " . $e->getMessage() . "\n"; 
			// die(); 
		// }
	break;
	
	case'savepilih':
		if(empty($_SESSION['listpurchaser']))
		{
			exit("Error: Purchaser is obligatory");
		}

        for($arDt=1;$arDt<=$_POST['totRow'];$arDt++)
		{
			if ($_POST['pilih'][$arDt]==1)
			{
				$tglHrini = date("Ymd");
				$sDet="update ".$dbname.".log_prapodt set realisasi='".$_POST['realisasi'][$arDt]."',tglAlokasi='".$tglHrini."' where nopp='".$nopp."' and kodebarang='".$_POST['kdBrg'][$arDt]."' and status!='3'";
				try
				{ 
					$owlPDO->exec($sDet); 
					foreach($_SESSION['listpurchaser'] as $key)
					{
						$sDet="insert into ".$dbname.".log_listverifikasi values ('','".$nopp."','".$_POST['kdBrg'][$arDt]."','".$key."','0','0','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','')";
						try
						{ 
							$owlPDO->exec($sDet); 
						}
						catch (PDOException $e)
						{
							echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
						}
					}
				}
				catch (PDOException $e)
				{
					echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
				}
            }
        }        
    break;
	
	case'insertPurchaser':
		if(empty($_SESSION['listpurchaser']))
		{
			exit("Error: Purchaser is obligatory");
		}
		
        $tglHrini = date("Ymd");
		
		$sql2="update ".$dbname.".log_prapodt set realisasi='".$realisasi."',tglAlokasi='".$tglHrini."' where nopp='".$nopp."' and kodebarang='".$kodebarang."' and status!='3'";
		try
		{
			$owlPDO->exec($sql2); 
			
			foreach($_SESSION['listpurchaser'] as $key)
			{
				$sDet="insert into ".$dbname.".log_listverifikasi values ('','".$nopp."','".$kodebarang."','".$key."','0','0','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','')";
				try
				{ 
					$owlPDO->exec($sDet); 
				}
				catch (PDOException $e)
				{
					echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
				}
			}
		}
		catch(PDOException $e)
		{
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'getSummary':
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}    
		if($periode=='')
		{
		   $periode=date("Y-m");
		}
		
		$tab="<link rel=stylesheet type=text/css href=style/".$gen.">
			<script language=javascript1.2 src='js/generic.js'></script>
			<script language=javascript1.2 src='js/log_verivikasi.js'></script>";
		$tab.="<fieldset><legend>Summarry Purchaser</legend>";
		$tab.="Till month : <span id=tglPeriode>".$periode."</span><br />";
		$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_prapoht  order by tanggal desc";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
		while($rPeriode=$qPeriode->fetch())
		{
			if($rPeriode['periode']!='0000-00')
			{
				$thn=explode("-", $rPeriode['periode']);
				if($thn[1]=='12')
				{
				$optper.="<option value='".substr($rPeriode['periode'],0,4)."' ".(substr($rPeriode['periode'],0,4)==$periode?'selected':'').">".substr($rPeriode['periode'],0,4)."</option>";
				}
				$optper.="<option value='".$rPeriode['periode']."' ".($rPeriode['periode']==$periode?'selected':'').">".substr($rPeriode['periode'],5,2)."-".substr($rPeriode['periode'],0,4)."</option>";
			}
		}
		$tab.="".$_SESSION['lang']['periode']." : <select id=period name=period onchange=getSumData()>".$optper."</select><br />";
		$tab.="<table border=0 cellspacing=1 cellpading=0 class=sortable><thead>
            <tr class=rowheader>
            <td rowspan='2'  align=center>No.</td>
            <td rowspan='2' align=center>".$_SESSION['lang']['purchaser']."</td>";
        $sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
        $qData=fetchData($sPt);
        $jumlahData=count($qData);
        $a=1;
		foreach($qData as $brsData => $rData)
		{
			$kdOrg[]=$rData;
			$tab.="<td colspan=4 align=center>".$rData['kodeorganisasi']."</td>";
			$a++;
		}
		$tab.="<tr class=rowheader>";
		for($acd=0;$acd<$jumlahData;$acd++)
		{
			$tab.="<td align=center'>Tot. Item</td><td align=center bgcolor='green'>On PO</td><td align=center bgcolor='red'>Blm PO</td><td align=center>% Out</td>";
		}
		$tab.="</tr></thead><tbody id=isiContain>";
		$sPur="select karyawanid,namakaryawan from ".$dbname.".datakaryawan 
			where (bagian like '%PRO%' or kodejabatan='17') and kodejabatan!='5' and (tanggalkeluar>='".date('Y-m-d')."' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
		
		$qPur=fetchData($sPur);
		$totalPo2=array();
		foreach($qPur as $brsKary) {
            foreach($kdOrg as $brsData3 => $rData3) {
                //get data blm po
				$sDt=" SELECT count(kodebarang) as jmlhPo,kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapoht a LEFT JOIN ".$dbname.".log_prapodt b ON a.nopp = b.nopp
					   WHERE  b.status='0' and kodeorg='".$rData3['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$periode."%' and b.purchaser='".$brsKary['karyawanid']."' ";
				// echo $sDt;
				$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
				$qDt->setFetchMode(PDO::FETCH_ASSOC);
				$rDt=$qDt->fetch();
				
                //get data sdh po
                $sDt2=" SELECT kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapoht a LEFT JOIN ".$dbname.".log_prapodt b ON a.nopp = b.nopp
                        LEFT JOIN ".$dbname.".log_podt c ON b.nopp = c.nopp  
                        WHERE b.status='0'  and kodeorg='".$rData3['kodeorganisasi']."' and substring(tanggal,1,7) like '%".$periode."%' and c.nopo!='' and b.purchaser='".$brsKary['karyawanid']."'  group by b.kodebarang  ";
				$qDt2=$owlPDO->query($sDt2) or die(print " Gagal: ".PDOException::getMessage());
				$qDt2->setFetchMode(PDO::FETCH_ASSOC);
				$jmlhPo2=owlBaris($qDt2);
                $rDt2=$qDt2->fetch();
				if(!isset($totalPo2[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($totalPo[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($all[$rDt['purchaser']][$rDt['kodeorg']])) $all[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($tempTotalPo2[$rDt['purchaser']])) $tempTotalPo2[$rDt['purchaser']]=0;
				if(!isset($sisa[$rDt['purchaser']])) $sisa[$rDt['purchaser']]=0;
                $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]+=$jmlhPo2;
                $totalPo[$rDt['purchaser']][$rDt['kodeorg']]+=$rDt['jmlhPo'];
                $all[$rDt['purchaser']][$rDt['kodeorg']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']]-$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
				$tempTotalPo2[$rDt['purchaser']]+=$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
				$sisa[$rDt['purchaser']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']];
            }
            $DtaAll[]=$brsKary;
        }
		$no=0;
		$totTrbitPO = $blmPO = $grndTotal = array();
		foreach($DtaAll as $brs)
		{
			$no++;
			$tab.="<tr class=rowcontent onclick=\"detailData('".$brs['karyawanid']."','".$periode."')\" style=\"cursor:pointer;\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$brs['namakaryawan']."</td>";
			foreach($kdOrg as $brsData2 => $rData2)
			{
				if(!empty($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']]))
				{
					@$persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=($all[$brs['karyawanid']][$rData2['kodeorganisasi']]/$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])*100;
				}
				if(!isset($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($all[$brs['karyawanid']][$rData2['kodeorganisasi']])) $all[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']])) $persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totTrbitPO[$rData2['kodeorganisasi']])) $totTrbitPO[$rData2['kodeorganisasi']]=0;
				if(!isset($blmPO[$rData2['kodeorganisasi']])) $blmPO[$rData2['kodeorganisasi']]=0;
				if(!isset($grndTotal[$rData2['kodeorganisasi']])) $grndTotal[$rData2['kodeorganisasi']]=0;
				$tab.="<td align=right>".number_format($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($all[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$totTrbitPO[$rData2['kodeorganisasi']]+=$totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$blmPO[$rData2['kodeorganisasi']]+=$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$grndTotal[$rData2['kodeorganisasi']]+=$all[$brs['karyawanid']][$rData2['kodeorganisasi']];
			}
		}
		$col=$a+2;
		$sAll="select count(*) as jmlh from ".$dbname.".log_prapodt where purchaser='0000000000'";
		$qAll=$owlPDO->query($sAll) or die(print " Gagal: ".PDOException::getMessage());
		$qAll->setFetchMode(PDO::FETCH_ASSOC);
		$rAll=$qAll->fetch();
		if(!empty($totalBlm)) {
			@$persenTot=($totalSemua/$totalBlm)*100;
		}
        $tab.="<tr class=rowcontent><td colspan=2>Total all Items</td>";
		if(!isset($presen)) $presen = array();
		foreach($kdOrg as $brsData2 => $rData2)
		{
			if($blmPO[$rData2['kodeorganisasi']]!=0) {
				@$presen[$rData2['kodeorganisasi']]=$totTrbitPO[$rData2['kodeorganisasi']]/$blmPO[$rData2['kodeorganisasi']]*100;
			} else {
				$presen[$rData2['kodeorganisasi']] = 0;
			}
			$tab.="<td align=right>".number_format($blmPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($totTrbitPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($grndTotal[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($presen[$rData2['kodeorganisasi']],0)."</td>";
		}
		$tab.="</tr>";
		$tab.="</tbody></table></fieldset>";
        echo $tab;
	break;
	
    case'detailSum':
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}     
		$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">
            <script language=javascript1.2 src='js/generic.js'></script>
            <script language=javascript1.2 src='js/log_verivikasi.js'></script>";
		$thn=substr($periode,0,4);
		$sPur="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$userid."'";
		$qPur=$owlPDO->query($sPur) or die(print " Gagal: ".PDOException::getMessage());
		$qPur->setFetchMode(PDO::FETCH_ASSOC);
		$rPur=$qPur->fetch();
		$tab.="<fieldset><legend>Summary</legend>";
        $tab.="Purchaser : ".$rPur['namakaryawan']."<br />
		   ".$_SESSION['lang']['periode']." : ".$thn."<br />
			<img onclick=detailExcel2('".$userid."','".$periode."') src=images/excel.jpg class=zImgBtn title='MS.Excel'>
			<table cellspacing=1 border=0 cellpading=0>
			<thead>";
		
		$sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qData=fetchData($sPt);
		
		$tab.="<tr class=rowheader>";
		$tab.="<td rowspan=2>".$_SESSION['lang']['periode']."</td>";    
		
		$sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qData=fetchData($sPt);
		$jumlahData=count($qData);
		$a=1;
		foreach($qData as $brsData => $rData)
		{
			$kdOrg[]=$rData;
			$tab.="<td colspan=4 align=center>".$rData['kodeorganisasi']."</td>";
			$a++;
		}
	   
		$tab.="</tr><tr class=rowheader>";
		for($acd=0;$acd<$jumlahData;$acd++)
		{
			$tab.="<td align=center'>Total. Item</td><td align=center bgcolor='green'>On PO</td><td align=center bgcolor='red'>Not PO</td><td align=center>% Out</td>";
		}
		
		$tab.="</tr></thead><tbody>";
		$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_poht where substr(tanggal,1,4)='".$thn."' order by tanggal desc";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
		while($rPeriode=$qPeriode->fetch())
		{
			$tab.="<tr class=rowcontent>";
			$tab.="<td>".$rPeriode['periode']."</td>";
			foreach ($qData as $brsData2 =>$rData2)
			{
				$sDt="SELECT count(kodebarang) as jmlhPo,kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
					WHERE  b.status='0' and kodeorg='".$rData2['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$rPeriode['periode']."%' and purchaser='".$userid."' ";
				$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
				$qDt->setFetchMode(PDO::FETCH_ASSOC);
				$rDt=$qDt->fetch();
				
				$sDt2="SELECT  kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
				   LEFT JOIN ".$dbname.".log_podt c ON b.nopp = c.nopp  
				   WHERE  b.status='0' and kodeorg='".$rData2['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$rPeriode['periode']."%' and c.nopo!='' and purchaser='".$userid."' group by b.kodebarang";
				$qDt2=$owlPDO->query($sDt2) or die(print " Gagal: ".PDOException::getMessage());
				$qDt2->setFetchMode(PDO::FETCH_ASSOC);
				$jmlhPo2=owlBaris($qDt2);
				$rDt2=$qDt2->fetch();
				
				$totalPo2[$rDt2['purchaser']][$rDt2['kodeorg']][$rDt2['periode']]+=$jmlhPo2;
                $totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']]+=$rDt['jmlhPo'];
                $all[$rDt2['purchaser']][$rDt2['kodeorg']][$rDt2['periode']]=$totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']]-$totalPo2[$rDt2['purchaser']][$rDt2['kodeorg']][$rDt2['periode']];
                
				if($totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']]!=0)
                {
					if($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']]!=0)
                    {
						@$persenId[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']]=$totalPo2[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']]/$totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']]*100;
					}
					$tab.="<td align=right><a href='#' onclick=detailExcel('".$rDt['purchaser']."','".$rDt['kodeorg']."','".$rDt['periode']."')>".number_format($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</a></td>";
					$tab.="<td align=right>".number_format($totalPo2[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
					$tab.="<td align=right>".number_format($all[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
					$tab.="<td align=right>".number_format($persenId[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
				}
				else
				{
					$tab.="<td align=right>".number_format($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
					$tab.="<td align=right>".number_format($totalPo2[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
					$tab.="<td align=right>".number_format($all[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
					$tab.="<td align=right>".number_format($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
				}

				$jmlhAll[$rData2['kodeorganisasi']]+=$all[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']];
				$jmlhTrbtPo[$rData2['kodeorganisasi']]+=$totalPo2[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']];
				$jmlhBlmpo[$rData2['kodeorganisasi']]+=$totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']];
			}

			$tab.="</tr>";
		}
		
		$tab.="<tr class=rowcontent><td>&nbsp;</td>";
		foreach ($qData as $brsData3 =>$rData3)
		{
		   if($jmlhBlmpo[$rData3['kodeorganisasi']]!=0)
			{
			   @$persenTotal[$rData3['kodeorganisasi']]= $jmlhTrbtPo[$rData3['kodeorganisasi']]/$jmlhBlmpo[$rData3['kodeorganisasi']]*100;
			   $tab.="<td align=right><a href='#' onclick=detailExcel('".$userid."','".$rData3['kodeorganisasi']."','".$thn."')>".number_format($jmlhBlmpo[$rData3['kodeorganisasi']],0)."</a></td>";
			   $tab.="<td align=right>".number_format($jmlhTrbtPo[$rData3['kodeorganisasi']],0)."</td>";
			   $tab.="<td align=right>".number_format($jmlhAll[$rData3['kodeorganisasi']],0)."</td>";
			   $tab.="<td align=right>".number_format($persenTotal[$rData3['kodeorganisasi']],0)."</td>";
			}
			else
			{
			   $tab.="<td align=right>".number_format($jmlhBlmpo[$rData3['kodeorganisasi']],0)."</td>";
			   $tab.="<td align=right>".number_format($jmlhTrbtPo[$rData3['kodeorganisasi']],0)."</td>";
			   $tab.="<td align=right>".number_format($jmlhAll[$rData3['kodeorganisasi']],0)."</td>";
			   $tab.="<td align=right>".number_format(0,0)."</td>";
			}
		}

		$tab.="</tr>";
		$tab.="</tbody></table></fieldset>";
		echo $tab;
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case'balikin':
		$str="select * from ".$dbname.".log_prapoht where nopp='".$nopp."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
        $pTiga=$bar['persetujuan2'];
		
		##update kodebarang yg di return dengan menghilangkan purchasernya agar tidak masuk di-inbox
		$str="update ".$dbname.".log_prapodt set purchaser='0000000000',create_po=3 where nopp='".$nopp."' and kodebarang='".$_POST['kdbrgbalik']."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		$str="update ".$dbname.".log_prapoht set close=3,persetujuan4=NULL,persetujuan5=NULL,
		          hasilpersetujuan2=0,hasilpersetujuan3=0,persetujuan3=NULL,"
                 ."komentar3=NULL,hasilpersetujuan4=0,komentar4=NULL,hasilpersetujuan5=0,komentar5='',"
                 ."tglp3=NULL,tglp4=NULL,tglp5=NULL,ket_balik='".$_POST['ket']."' where nopp='".$nopp."' ";
		try{
			$owlPDO->exec($str); 
			
			//sukses langsung kirim email
			$to=getUserEmail($pTiga);
			$namakaryawan=getNamaKaryawan($_SESSION['standard']['userid']);
			$subject="[Notifikasi]Perubahan barang ".$nopp." ";
			$body="<html>
				<head>
				<body>
					<dd>Dengan Hormat,</dd><br>
					<br>
					Sehubungan dengan tidak adanya barang/alasan lain dinopp : ".$nopp." untuk :
					<br>Kode : ".$_POST['kdbrgbalik']."</br>
					<br>Nama : ".$_POST['nmbrgbalik']."</br>
					<br>Keterangan : ".$_POST['ket']."</br>
                    <br>
					harap untuk mengganti kodebarang tersebut
					<br>
					<br>
					Regards,
					Procurment Dept.<br>
					Owl-Plantation System.
				</body>
				</head>
			</html>";
			$kirim=kirimEmail($to,'',$subject,$body);#this has return but disobeying;			
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}			
		echo "Data sudah dikembalikan ";
	break;
	
	case 'insert_detail_pp' :
		if($jmlh_realisai==0)
		{
			echo "Warning: Realization must greater than 0 ".$jmlh_realisai."";
			exit ();
		}
		else
		{
			$sql="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and status!='3'";
            /* @var $query cek data di database */
			if($owlPDO->query($sql))
			{
				$query=$owlPDO->query($sql);
				$query->setFetchMode(PDO::FETCH_ASSOC);
				while($res=$query->fetch())
				{
					if($res['$purchaser']=='0000000000')
					{
						$sql2="update ".$dbname.".log_prapodt set purchaser='".$purchaser."',lokalpusat='".$lokal."',realisasi='".$jmlh_realisai."',satuanpp='".$satuan."',satuankonversi='".$satuan."',tglAlokasi='".$tglHrini."' where kodebarang='".$kd_brng."' and nopp='".$nopp."'";
						try{
							$owlPDO->exec($sql2); 
							break;
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}
					else
					{
						$sCek="select distinct jumlahpesan from ".$dbname.".log_podt where nopp='".$nopp."' and kodebarang='".$kd_brng."'";
						$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
						$qCek->setFetchMode(PDO::FETCH_ASSOC);
						$rCek=$qCek->fetch();
						if($jmlh_realisai<$rCek['jumlahpesan'])
						{
							exit("Error: Realization less than requested");
						}
						
						$sql2="update ".$dbname.".log_prapodt set purchaser='".$purchaser."',lokalpusat='".$lokal."',realisasi='".$jmlh_realisai."',satuanpp='".$satuan."',satuankonversi='".$satuan."',tglAlokasi='".$tglHrini."' where kodebarang='".$kd_brng."' and nopp='".$nopp."'";
                    
						try{
							$owlPDO->exec($sql2); 
							break;
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}
				}
			}
			else
			{
				echo " Gagal,".PDOException::getMessage();
				exit();
			}
		}
	break;
 
	case 'cari_pp'://indra
		echo" <table class=\"sortable\" cellspacing=\"1\" border=\"0\">
		<thead>
		<tr class=rowheader>
		<td align=center>No.</td>
				<td align=center width=50px>".$_SESSION['lang']['kodeorg']."</td>
				<td align=center>".$_SESSION['lang']['nopp']."</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['harga']."</td>
				<td align=center width=50px>Advance Action</td>
				<td align=center>".$_SESSION['lang']['chat']."</td>
				<td align=center>".$_SESSION['lang']['tanggal']." PP</td>
				<td align=center width=50px>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td align=center>".$_SESSION['lang']['jmlh_disetujui']."</td>
				<td align=center width=30px>".$_SESSION['lang']['satuan']."</td>
				<td align=center>".$_SESSION['lang']['purchaser']."</td>
				<td align=center width=60px>".$_SESSION['lang']['lokasitugas']."</td>
				<td align=center>O.std</td>
				<td colspan='3' align=\"center\">Action</td>
		</tr>
		</thead>
		<tbody>";
		$limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
        $page=$_POST['page'];
        if($page<0)
          $page=0;
        }
		
        $offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$no=0;
		$no=$maxdisplay;
		$txt_search=$_POST['txtSearch'];
		$txtCari=checkPostGet('txtCari','');

		if(($txt_search=='')&&(!empty($txt_tgls)))
		{
			$where=" ";
		}
		if($txt_search!='')
		{
			$where.="and b.nopp LIKE  '%".$txt_search."%'   ";
		}
		if($_POST['tglCari']!='')
		{
			$where.=" and a.tanggal LIKE '".$_POST['tglCari']."%'";
		}
		if($userid!='')
		{
			$where.=" and purchaser='".$userid."'";
		}

		if($unitIdCr!='')
		{
			$where.=" and b.nopp like '%".$unitIdCr."%'";
		}
		if($klmpKbrg!=''&&$kdBarangCari=='')
		{
			$where.=" and substr(kodebarang,1,3)='".$klmpKbrg."'";
		}
		if($kdBarangCari!='')
		{
			$where.=" and kodebarang='".$kdBarangCari."'";
		}
		
		// $where.=" and a.close=2 ";
		$where.=" and a.close>1 ";
		
		
		//$jenis='';
		if($jenis!=''){
			@$where.=" and kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where jenis='".$jenis."')";
		}
		

		if($statPP==1){
			
			$strx="SELECT  distinct a.`tanggal`, a.`close`,b.*  FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp WHERE  b.status='0' and create_po='1' ".$where."  ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc limit ".$offset.",".$limit." ";
			
			$sql="SELECT  distinct  a.`tanggal`,  a.`close`, b.* FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp WHERE  b.status='0' and create_po='1' ".$where."   ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc ";
		}
		else if($statPP==0){
			$where.=" and b.purchaser='0000000000'";
			$strx="SELECT distinct  a.`tanggal`,  a.`close`, b.*  FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp WHERE b.status='0'  and create_po='0'  ".$where."   ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc  limit ".$offset.",".$limit." ";
			
			$sql="SELECT distinct  a.`tanggal`,  a.`close`, b.* FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp WHERE  b.status='0' and create_po='0' ".$where."   ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc";
		}
		else if($statPP==2){
			$strx="SELECT   distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*  
				   FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
				   WHERE  b.status='0'   ".$where."  and create_po!='3'  ORDER BY  a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc limit ".$offset.",".$limit."";
			$sql="SELECT   distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*  
				   FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
				   WHERE  b.status='0'   ".$where."  and create_po!='3' ORDER BY purchaser asc,a.tglp5,a.tglp4,a.tglp3,a.tglp2,a.tglp1 desc ";
		}
		
		$qry=fetchdata($sql);
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$jsl=count($qry);
		$jlhbrs= $jsl;

		if($res=$owlPDO->query($strx))
		{   
			$row=owlBaris($res);
			if($row!=0)
			{ 
				$res->setFetchMode(PDO::FETCH_OBJ);
				while($bar=$res->fetch())
				{			
					$koderorg=substr($bar->nopp,15,4);
					$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; 
					$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
					$rep->setFetchMode(PDO::FETCH_OBJ);
					$bas=$rep->fetch();

					$spr2="select namabarang,jenis from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
					$rep2=$owlPDO->query($spr2) or die(print " Gagal: ".PDOException::getMessage());
					$rep2->setFetchMode(PDO::FETCH_OBJ);
					$bas2=$rep2->fetch();
					
					$no+=1;
					
					$sPoDet="select nopo from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
					$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
					$rCek=owlBaris($qPoDet);
					if($rCek>0)
					{
						$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
						$rPoDet=$qPoDet->fetch();
						
						$sPo="select tanggal,stat_release from ".$dbname.".log_poht where nopo='".$rPoDet['nopo']."'";
						$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
						$qPo->setFetchMode(PDO::FETCH_ASSOC);
						$rPo=$qPo->fetch();
						
						$stat=$rPo['stat_release'];
						$nopo=$rPoDet['nopo'];
					}
					else
					{	
						#ost tgl pp
						#awal : hari ini - tgl pp
						#diganti : hr ini - tgl persetujuan 4
						#diganti lagi ke tanggal pp
						
						$tglPP=explode("-",$bar->tanggal);
						// $tglPP=explode("-",$bar->tglp4);
						$date1 = $tglPP[2];
						$month1 = $tglPP[1];
						$year1 = $tglPP[0];
						$tgl2 = date("Y-m-d"); 
						$pecah2 = explode("-", $tgl2);
						$date2 = $pecah2[2];
						$month2 = $pecah2[1];
						$year2 =  $pecah2[0];
						$stat=0;					
					}
					
					$sPoDetHrg="select distinct hargasatuan from ".$dbname.".log_podt where  kodebarang='".$bar->kodebarang."' order by nopo desc";
					$qPoDetHrg=$owlPDO->query($sPoDetHrg) or die(print " Gagal: ".PDOException::getMessage());
					$qPoDetHrg->setFetchMode(PDO::FETCH_ASSOC);
					$rCekHrg=$qPoDetHrg->fetch();
					
					@$jd1 = GregorianToJD($month1, $date1, $year1);
					@$jd2 = GregorianToJD($month2, $date2, $year2);
					@$jmlHari=$jd2-$jd1;

					$optPur="<option value=''></option>";
					$klq="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where  bagian='PRO'  and tanggalkeluar='0000-00-00' order by namakaryawan asc ";
					$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
					$qry->setFetchMode(PDO::FETCH_OBJ);
					while($rst=$qry->fetch())
					{
						if($bar->purchaser==$rst->karyawanid)
						{
							$optPur.="<option value=".$rst->karyawanid." selected>".$rst->namakaryawan."</option>";
						}
						else
						{
							$optPur.="<option value=".$rst->karyawanid.">".$rst->namakaryawan."</option>";
						}
					}

					if($bar->lokalpusat!=0)
					{
						$ckh="checked=checked";
					}
					else
					{
						$ckh='';
					}
					
					$read_only2 = $stat_view = "";
					if($bar->purchaser!='0000000000')
					{
						$read_only2="disabled=disabled";
						$ckh.=" disabled=disabled";
					}
					
					$optLokasi='';
					$cl=array(0=>'Head Office',1=>'Local');
					foreach($cl as $rw =>$isi)
					{
						$optLokasi.="<option '".($bar->lokalpusat==$rw?'selected=selected':'')."'value='".$rw."'>".$isi."</option>";
					}

					//periksa chat==================================
					$strChat="select *  from ".$dbname.".log_pp_chat where kodebarang='".$bar->kodebarang."' and nopp='".$bar->nopp."'";
					$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
					if(owlBaris($resChat)>0)
					{
						$ingChat="<img src='images/chat1.png' onclick=\"loadPPChat('".$bar->nopp."','".$bar->kodebarang."',event);\" class=zImgBtn>";
					}		  
					else			
					{
						$ingChat="<img src='images/chat0.png'  onclick=\"loadPPChat('".$bar->nopp."','".$bar->kodebarang."',event);\" class=zImgBtn>";
					}
					
					if($bar->keteranganubah!='')
						$trStyle = "style='background-color:yellow'";
					else
						$trStyle = "";
						echo"<tr class=rowcontent id='tr_".$no."' title='".$_SESSION['lang']['tgldibutuhkan'].":".tanggalnormal($bar->tgl_sdt)."' ".$trStyle.">
							<td align=center>".$no."</td>
							<td >".$koderorg."</td>
							<td id=nopp_".$no."  onclick=\"getDataPP2('".$bar->nopp."','".$no."')\" style=\"cursor:pointer\">".$bar->nopp."</td>
							<td id=kd_brg_".$no.">".$bar->kodebarang."</td>
							<td>".substr($bas2->namabarang,0,33)."</td>
							<td>".$bas2->jenis."</td>
							<td align=right>".number_format($rCekHrg['hargasatuan'])."</td>";
								  
						if($stat!=1)
						{
							echo"<td align=\"center\">";
							
							if($bar->purchaser!='0000000000')
							{
								$disab="disabled";
							}
							else
							{
								$disab="";
							}
							
							echo "<button class=mybutton $disab  id=balikinbutton_".$no." onclick=\"formReturn('".$bar->nopp."','".$bar->kodebarang."','".$rDtBrg[$bar->kodebarang]."')\">Return</button>";
						}
						else
						{
							echo"<td>".$_SESSION['lang']['release_po']."</td>";
						}
								  
						if(($bar->satuanpp)!=''){
							$selectedSat = '';
						}else{
							$selectedSat = 'selected';
						}
					  
						//Satuan dari table master barang
						$strSat="select kodebarang,satuan from ".$dbname.".`log_5masterbarang` where  kodebarang='".$bar->kodebarang."' order by satuan";
						$qrySat=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
						$qrySat->setFetchMode(PDO::FETCH_ASSOC);
						$resSat=$qrySat->fetch();
						$optSat="<option value=".$resSat['satuan']." ".$selectedSat.">".$resSat['satuan']."</option>";

						//List satuan dari table konversi
						$strSat2="select kodebarang,satuankonversi from ".$dbname.".`log_5stkonversi` where  kodebarang='".$bar->kodebarang."' order by satuankonversi";
						$qrySat2=$owlPDO->query($strSat2) or die(print " Gagal: ".PDOException::getMessage());
						$qrySat2->setFetchMode(PDO::FETCH_OBJ);
						while($resSat2=$qrySat2->fetch())
						{
							if(($bar->satuanpp)!=''){
								if(($bar->satuanpp)==($resSat2->satuankonversi)){
									$optSat.="<option value=".$resSat2->satuankonversi." selected>".$resSat2->satuankonversi."</option>";
								}else{
									$optSat.="<option value=".$resSat2->satuankonversi.">".$resSat2->satuankonversi."</option>";
								}
							}else{
								$optSat.="<option value=".$resSat2->satuankonversi.">".$resSat2->satuankonversi."</option>";
							}
						}
								  
						if($bar->realisasi>0)
						{
							$real=$bar->jumlah;
						}
						else
						{
							$real=$bar->jumlah;
						}
						
						echo"<td align=center>".$ingChat."</td>
						<td>".tanggalnormal($bar->tanggal)."</td>
						<td align=right>".number_format($bar->jumlah,3)."</td>
						<td align=right>
							<input type=hidden disabled id=hrealisasi_".$no." name=hrealisasi_".$no." onkeypress='return angka_doang(event)' class='myinputtextnumber' ".$read_only2." value=$real style='width:60px;' />
							<input type=text disabled id=realisasi_".$no." name=realisasi_".$no." onkeypress='return angka_doang(event)' class='myinputtextnumber' ".$read_only2." value=$real style='width:60px;' /></td>
						<td align=center><select id=satuan_".$no." id=satuan_".$no." ".$read_only2." onchange='getSatuanKonversi(this.value,".$bar->kodebarang.",".$no.")'>$optSat</select></td>
						<td><select style=width:150px id=purchase_name_".$no." id=purchase_name_".$no." ".$read_only2." onchange='getlokalpusat(this.value,".$no.")'>$optPur</select></td>";
						
						if($stat==1)
						{
							if($_SESSION['empl']['kodejabatan']=='5') 
							{
								echo "<td align=center><input type=checkbox id=lokalpusat_".$no."  ".$ckh." /> Local
								</td>";
								echo"<td align=center title=\"Selisih Tanggal PP dengan Tanggal Hari ini\" >".$jmlHari."</td>
								<td ".$stat_view."><img src=images/save.png class=zImgBtn  title='Save' onclick=\"AddPur('".$no."');\"></td>
								<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' onclick=\"EditPur('".$no."');\"></td>
								<td align=center><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('log_prapoht','".$bar->nopp."','','log_slave_print_log_pp',event);\"></td>";
							}
							else
							{
								echo "<td align=left colspan=5>".$nopo."</td>";
							}
						}
						else
						{
							echo"<td align=center><input type=checkbox id=lokalpusat_".$no."  ".$ckh." /> Local</td>";
							echo"<td align=center title=\"Selisih Tanggal PP dengan Tanggal Hari ini\" >".$jmlHari."</td>
							<td ".$stat_view."><img src=images/save.png class=zImgBtn  title='Save' onclick=\"AddPur('".$no."');\"></td>
							<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit Data' onclick=\"EditPur('".$no."');\"></td><td align=center><img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('log_prapoht','".$bar->nopp."','','log_slave_print_log_pp',event);\"></td>";
						}
						echo"</tr>";
				}	
				echo"<tr><td colspan=14 align=center>".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."
				<br>
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>"; 
			}
			else
			{
				echo"<tr class=rowcontent><td colspan=16>Not Found</td></tr>";
			}
		}	
		else
		{
			echo " Gagal,".PDOException::getMessage();
		}	

		echo" </tbody>
		</table><input type='hidden' id='halPage' name='halPage' value='".$page."' />";
	break;

	
    
	case'cariBarang':
        $txtfind=$_POST['txtfind'];
		$pil=$_POST['pil'];
        $str="select * from ".$dbname.".log_5masterbarang where namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%' ";
        if($owlPDO->query($str))
        {
			$res=$owlPDO->query($str);
			$res->setFetchMode(PDO::FETCH_OBJ);
			echo"<fieldset style=float:left;clear:both;>
				<legend>Result</legend>
				<div style=\"overflow:auto; height:280px;\" >
                <table class=data cellspacing=1 cellpadding=2  border=0>
					<thead>
					<tr class=rowheader>
					<td class=firsttd>No.</td>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>".$_SESSION['lang']['saldo']."</td>
					<td>".$_SESSION['lang']['keterangan']."</td> 
                    <td>".$_SESSION['lang']['action']."</td>  
				</tr>
                </thead>
                <tbody>";
                
			$no=0;	 
			while($bar=$res->fetch())
			{
                $no+=1;
                //===========================pengambilan saldo
                //ambil saldo barang
                $saldoqty=0;
                $str1="select sum(saldoqty) as saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$bar->kodebarang."'
                and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
				$res1->setFetchMode(PDO::FETCH_OBJ);
				while($bar1=$res1->fetch())
                {
					$saldoqty=$bar1->saldoqty;
                }

                //ambil pemasukan barang yang belum di posting
                $qtynotpostedin=0;
                $str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
                b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' 
                and a.tipetransaksi<5
                and a.post=0
                group by kodebarang";
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_OBJ);
                while($bar2=$res2->fetch())
                {
					$qtynotpostedin=$bar2->jumlah;
                }
                if($qtynotpostedin=='')
                $qtynotpostedin=0;


                //ambil pengeluaran barang yang belum di posting
                $qtynotposted=0;
                $str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt
                b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' 
                and a.tipetransaksi>4
                and a.post=0
                group by kodebarang";
				$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
				$res2->setFetchMode(PDO::FETCH_OBJ);
				while($bar2=$res2->fetch())
                {
					$qtynotposted=$bar2->jumlah;
                }
                if($qtynotposted=='')
                $qtynotposted=0;

                $saldoqty=($saldoqty+$qtynotpostedin)-$qtynotposted;
                //============================================		

                if($bar->inactive==1)
                {
					echo"<tr class=rowcontent style='cursor:pointer;'  title='Inactive' >";
					$bar->namabarang=$bar->namabarang. " [Inactive]";
                }
                else
                {	
                    $clikData="\"setBrg(".$bar->kodebarang.",".$no.")\"";
                    if($pil==2)
                    {
						$clikData="\"setBrg2('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."')\"";
                    }
					echo"<tr class=rowcontent>";
                }   
                echo" <td class=firsttd>".$no."</td>
                <td>".$bar->kodebarang."</td>
                <td>".$bar->namabarang."</td>
                <td>".$bar->satuan."</td>
                <td align=right>".number_format($saldoqty,2,',','.')."</td>
                <td><input type=text id=keteranganubah".$no." name=keteranganubah".$no." class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:150px;\" /></td>
                <td><img src=images/save.png class=zImgBtn  title='Save' onclick=".$clikData."></td>
                </tr>"; 
			}//indra	 
			echo "</tbody>
				<tfoot>
				</tfoot>
			</table></div></fieldset>";
		}	
		else
		{
			echo " Gagal,".PDOException::getMessage();
		}	
	break;
	
	case'updateDtbarang':
		$sUpdate="update ".$dbname.".log_prapodt set kodebarang='".$kdBrgBaru."',keteranganubah='".$_POST['keteranganubah']."' where nopp='".$nopp."' and kodebarang='".$kd_brng."'";
		try{
			$owlPDO->exec($sUpdate); 
			$sCek="select kodebarang from ".$dbname.".log_podt where nopp='".$nopp."' and kodebarang='".$kd_brng."'";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$rCek=owlBaris($qCek);
			if($rCek>0)
			{
				$sUpdPo="update ".$dbname.".log_podt set kodebarang='".$kdBrgBaru."' where nopp='".$nopp."' and kodebarang='".$kd_brng."'";
				try{
					$owlPDO->exec($sUpdPo); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case 'excelData':
		$stream.=" <table border=\"1\">
			<thead>
			<tr>
				<td bgcolor=#DEDEDE align=center valign=middle>No.</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['kodeorg']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['nopp']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['kodebarang']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['namabarang']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['harga']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['tanggal']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['tgldibutuhkan']."</td>    
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlh_disetujui']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['purchaser']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['lokasitugas']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlh_hari_outstanding']."</td>
				<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['nopo']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$txt_search=$_GET['txtSearch'];
		$txtCari=$_GET['txtCari'];
		$txt_tgl=tanggalsystem($_GET['tglCari']);

		if(($txt_search=='')&&($txt_tgls==''))
		{
			$where=" ";
		}
		if($txt_search!='')
		{
			$where.="and b.nopp LIKE  '".$txt_search."%'   ";
		}
		if($_GET['tglCari']!='')
		{
			$where.=" and a.tanggal LIKE '".$_GET['tglCari']."%'";
		}
		if($userid!='')
		{
			$where.=" and purchaser='".$userid."'";
		}

		if($unitIdCr!='')
		{
			$where.=" and b.nopp like '%".$unitIdCr."%'";
		}
		if($klmpKbrg!=''&&$kdBarangCari=='')
		{
			$where.=" and substr(kodebarang,1,3)='".$klmpKbrg."'";
		}
		if($kdBarangCari!='')
		{
			$where.=" and kodebarang='".$kdBarangCari."'";
		}
		if($statPP==0)
		{
			$where.=" and purchaser='0000000000'";
		}
		
		if($statPP==1)
		{
			$strx="SELECT  distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*  
                                   FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
                                   WHERE a.close = '2' and b.status='0' and create_po!='0' ".$where."  ORDER BY a.nopp asc ";

		}
		else if($statPP==0)
		{
			$strx="SELECT  distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*   
                                   FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
                                   WHERE a.close = '2' and b.status='0'  and create_po='0'  ".$where."  ORDER BY a.nopp asc  ";
		}
		else if($statPP==2)
		{
			$strx="SELECT   distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*  
                               FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
                               WHERE a.close = '2' and b.status='0' and b.create_po='0'  ".$where."   ORDER BY a.tanggal ";
		}
		
		if($owlPDO->query($strx))
		{
			$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$res->fetch())
			{			
				$koderorg=substr($bar->nopp,15,4);
				$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'";
				$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
				$rep->setFetchMode(PDO::FETCH_OBJ);
				$bas=$rep->fetch();
				
				$spr2="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar->kodebarang."'";
				$rep2=$owlPDO->query($spr2) or die(print " Gagal: ".PDOException::getMessage());
				$rep2->setFetchMode(PDO::FETCH_OBJ);	
				$bas2=$rep2->fetch();
				$no+=1;
				
				$sPoDet="select distinct nopo from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
				$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
				$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
				$rCek=owlBaris($qPoDet);
				if($rCek>0)
				{
					// $sPoDet="select distinct nopo from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
					$rPoDet=$qPoDet->fetch();
					
					$sPo="select tanggal from ".$dbname.".log_poht where nopo='".$rPoDet['nopo']."'";
					$qPo=$owlPDO->query($sPo) or die(print " Gagal: ".PDOException::getMessage());
					$qPo->setFetchMode(PDO::FETCH_ASSOC);
					$rPo=$qPo->fetch();
					
					$tglA=substr($rPo['tanggal'],0,4);
					$tglB=substr($rPo['tanggal'],5,2);
					$tglC=substr($rPo['tanggal'],8,2);
					$tgl2=$tglA.$tglB.$tglC;
					$tGl1=substr($bar->tanggal,0,4);
					$tGl2=substr($bar->tanggal,5,2);
					$tGl3=substr($bar->tanggal,8,2);
					$tgl2=$tglA.$tglB.$tglC;
					$tgl1 =$tGl1.$tGl2.$tGl3;
					$stat=1;
					$nopo=$rPoDet['nopo'];
				}
				else
				{	
					$tGl1=substr($bar->tanggal,0,4);
					$tGl2=substr($bar->tanggal,5,2);
					$tGl3=substr($bar->tanggal,8,2);
					$tgl1 =$tGl1.$tGl2.$tGl3;
					$Tgl2 = date('Y-m-d');			
					$tglA=substr($Tgl2,0,4);
					$tglB=substr($Tgl2,5,2);
					$tglC=substr($Tgl2,8,2);	
					$tgl2=$tglA.$tglB.$tglC;	
					$stat=0;					
				}
				
				$starttime=strtotime($tgl1);//time();// tanggal sekarang
				$endtime=strtotime($tgl2);//tanggal pembuatan dokumen
				$timediffSecond = abs($endtime-$starttime);
				$base_year = min(date("Y", $tGl1), date("Y", $tglA));
				$diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
				$jmlHari=date("j", $diff) - 1;
				
				$klq="select namakaryawan from ".$dbname.".`datakaryawan` where  karyawanid='".$bar->purchaser."'";
				$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
				$qry->setFetchMode(PDO::FETCH_ASSOC);
				$rNm=$qry->fetch();
				
				$bar->lokalpusat!=0?$chk="Local":$chk="Head Office";
				
				$sPoDetHrg="select distinct hargasatuan from ".$dbname.".log_podt where  kodebarang='".$bar->kodebarang."' order by nopo desc";
				$qPoDetHrg=$owlPDO->query($sPoDetHrg) or die(print " Gagal: ".PDOException::getMessage());
				$qPoDetHrg->setFetchMode(PDO::FETCH_ASSOC);
				$rCekHrg=$qPoDetHrg->fetch();
				
				$stream.="<tr>
					<td>".$no."</td>
					<td>".$koderorg."</td>
					<td>".$bar->nopp."</td>
					<td>".$bar->kodebarang."</td>
					<td>".substr($bas2->namabarang,0,33)."</td>
					<td align=right>".number_format($rCekHrg['hargasatuan'],2)."</td>
					<td>".tanggalnormal($bar->tanggal)."</td>
					<td>".tanggalnormal($bar->tgl_sdt)."</td>    
					<td align=right>".number_format($bar->jumlah,2)."</td>
					<td align=right>".number_format($bar->realisasi,2)."</td>
					<td>".$rNm['namakaryawan']."</td> 
					<td>".$chk."</td> <td>".$jmlHari."</td>";
				$stream.= "<td align=center>". $nopo."</td>";
				$stream.="</tr>";
			}	 	  
		}	
		else
		{
			echo " Gagal,".PDOException::getMessage();
		}	

		$stream.=" </tbody>";
		//=================================================

		$stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		$dte=date("His");
		$nop_="ListVerifikasiBarang_".$dte;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $stream);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
		</script>";

	break;
	
	case'getForm':
		$kolom=0;
		$sCek="select * from ".$dbname.".log_prapoht where nopp='".$_POST['nopp']."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$qCek->fetch();
		for($a=1;$a<6;$a++)
		{
			if($rCek['persetujuan'.$a]!='')
			{
				$kolom+=1;
			}
			else
			{
				$kolom+=1;
				break;
			}
		}
        
		echo"<br />	
		<input type=\"hidden\" id='kolom' name='kolom' value=".$kolom." />
		<fieldset><legend>Approval</legend>
		<div id=test style=display:block>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$_POST['nopp']."  /></legend>
		<table cellspacing=1 border=0>
			<tr>
				<td colspan=3>Submit to the next verification :</td>
            </tr>
            <td>".$_SESSION['lang']['namakaryawan']."</td>
            <td>:</td>
            <td valign=top>";

		$optPur='';
		$klq="select karyawanid,namakaryawan,lokasitugas,bagian from ".$dbname.".`datakaryawan` where karyawanid!='".$_SESSION['standard']['userid']."' and tipekaryawan='0' and lokasitugas!='' order by namakaryawan asc";
		$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		while($rst=$qry->fetch())
		{
			$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
			$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
			$qBag->setFetchMode(PDO::FETCH_ASSOC);
			$rBag=$qBag->fetch();
			$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."]  [".$rBag['nama']."]</option>";
		}
		echo"<select id=user_id name=user_id  style=\"width:150px;\">".$optPur."</select></td></tr>
			<tr>
			<tr>
				<td>".$_SESSION['lang']['note']."</td>
				<td>:</td>
				<td><input type=text id=comment_fr name=comment_fr class=myinputtext onClick='return tanpa_kutip(event)'  style=\"width:150px;\" /></td>
			</tr>
				<td colspan=3 align=center>
					<button class=mybutton onclick=forwardPP() title=\"Submit to the next verification\" id=Ajukan >".$_SESSION['lang']['diajukan']."</button>
					<button class=mybutton onclick=cancel() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button>
				</td></tr></table><br /> 
				<input type=hidden name=method id=method  /> 
				<input type=hidden name=user_id id=user_id value=".$_SESSION['standard']['userid']." />
				<input type=hidden name=nopp id=nopp value=".$_POST['nopp']."  /> 
				</fieldset></div><br />
				</fieldset><br />";
		echo"<fieldset>
				<legend>Rejection</legend>
				<div id=rejected_form>
				<fieldset>
				<legend><input type=text readonly=readonly name=dnopp id=dnopp value=".$_POST['nopp']."  /></legend>
				<table cellspacing=1 border=0>
				<tr>
				<td colspan=3>
				Rejection Form </td></tr>
				<tr>
				<td>".$_SESSION['lang']['note']."</td>
				<td>:</td>
				<td><input type=text id=cmnt_tolak name=cmnt_tolak class=myinputtext onClick=\"return tanpa_kutip(event)\" /></td>
				</tr>
				<tr><td colspan=3 align=center>
				<button class=mybutton onclick=\"rejected_pp_proses()\" >".$_SESSION['lang']['ditolak']."</button>
				<button class=mybutton onclick=\"rejected_some_proses('".$_POST['nopp']."','".$kolom."')\" >".$_SESSION['lang']['ditolak_some']."</button>
				<button class=mybutton onclick=cancel() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button>
				</td></tr></table>
				</fieldset>
				</div>
				</fieldset>";
	break;
	
	case'insertFwrdpp':
		$sCek="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$qCek->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=$qCek->fetch();
		for($i=1;$i<6;$i++)
		{
			if($rCek['persetujuan'.$i]=="")
			{
				$ar=$i;
				break;
			}
		}
		if($ar==5)
		{
			echo"warning: No more submission";
			exit();
		}
		else
		{
			$thisDate=date("d-m-Y");
			$pls=$ar+1;
			$sUp="update ".$dbname.".log_prapoht set persetujuan".$ar."='".$_SESSION['standard']['userid']."',tglp".$ar."='".tanggalsystem($thisDate)."',close='1',persetujuan".$pls."='".$userid."' where nopp='".$nopp."'";
			
			try{
				$owlPDO->exec($sUp); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
        }
	break;
	
	case'rejected_pp_ex':
		if($kolom<6)
		{
			$tglSkrng=date("Y-m-d");
			$sUpdatePP="update ".$dbname.".log_prapoht set komentar".$kolom."='".$comment."',hasilpersetujuan".$kolom."='3',tglp".$kolom."='".$tglSkrng."',persetujuan".$kolom."='".$_SESSION['standard']['userid']."' where nopp='".$nopp."'";
			
			try{
				$owlPDO->exec($sUpdatePP);
				
				$sql3="update ".$dbname.".log_prapodt set status='3',ditolakoleh='".$_SESSION['standard']['userid']."' where nopp='".$nopp."'";
				
				try{
					$owlPDO->exec($sql3); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}				
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		else
		{
			echo"warning: Please contact administrator";
			exit();
		}
	break;
	
	case 'get_form_rejected_some':
		$sql="select * from ".$dbname.".log_prapodt where `nopp`='".$nopp."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		
		echo"
        <fieldset><input type=hidden id=kolom value=".$kolom.">
        <legend><input type=text id=rnopp name=rnopp value=".$nopp." readonly=readonly /></legend>
        <div style=overflow:auto;width=850px;height:350px;>
        <table cellspacing=1 border=0 class=sortable>
        <thead class=rowheader>
        <tr>
        <td>No.</td>
        <td>".$_SESSION['lang']['kodebarang']."</td>
        <td>".$_SESSION['lang']['namabarang']."</td>
        <td>".$_SESSION['lang']['satuan']."</td>
        <td>".$_SESSION['lang']['kodeanggaran']."</td>
        <td>".$_SESSION['lang']['jmlhDiminta']."</td>
        <td>".$_SESSION['lang']['tanggalSdt']."</td>
        <td>".$_SESSION['lang']['keterangan']."</td>
        <td>".$_SESSION['lang']['alasanDtolak']."</td>
        <td colspan=2>Action</td>
        </tr>
        </thead>
		<tbody id=reject_some class=rowcontent>";
		
        while($res=$query->fetch()){
			$no+=1;
			$sql2="select namabarang,satuan from ".$dbname.".log_5masterbarang where `kodebarang`='".$res['kodebarang']."'";
			$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
			$query2->setFetchMode(PDO::FETCH_ASSOC);
			$res2=$query2->fetch();
			
			if($res['status']=='3')
			{
				$dis="disabled=disabled";
			}
			else
			{
				$dis="";
			}
			echo"<tr>
			<td>".$no."</td>
			<td id=kdBrg_".$no.">".$res['kodebarang']."</td>
			<td>".$res2['namabarang']."</td>
			<td>".$res2['satuan']."</td>
			<td id=kd_angrn_".$no.">".$res['kd_anggran']."</td>
			<td id=jmlh_".$no.">".$res['jumlah']."</td>
			<td id=tgl_".$no.">".$res['tgl_sdt']."</td>
			<td id=ket_".$no.">".$res['keterangan']."</td>
			<td><input type=text id=alsnDtolak_".$no." name=alsnDtolak_".$no." class=myinputtext style=width:100px /></td>
			<td><button class=mybutton onclick=\"rejected_some('".$nopp."','".$no."','".$kolom."')\" ".$dis." >".$_SESSION['lang']['ditolak']."</button></td>
			</tr>";
        }
		
        echo"</tbody><tfoot><tr><td colspan=10 align=center><button class=mybutton onclick=\"rejected_some_done()\" >".$_SESSION['lang']['done']."</button></td></tr></tfoot></table></div></fieldset><input type=hidden id=user_id name=user_id value='".$_SESSION['standard']['userid']."'>";
	break;

	### BEGIN CHECK USE TO?? ###
	case 'rejected_some_done':
        $user_id=$_POST['user_id'];
        for($i=1;$i<6;$i++)
		{
			$sql2="select * from ".$dbname.".log_prapoht where nopp='".$_POST['nopp']."' and persetujuan".$i."='".$user_id."' ";
			$query=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
				while($res=$query->fetch())
				{
					for($i=1;$i<6;$i++)
					{	
						if($res['hasilpersetujuan'.$i]=='')
						{
							$sql2="update ".$dbname.".log_prapoht set hasilpersetujuan".$i."='1'";
							 try{$owlPDO->exec($sql2); }catch (PDOException $e) {}
						}
					}
				}
				break;

        }
	break;
	### END CHECK USE TO?? ###
	
	case 'rejected_some_input' :
		$where=" nopp='".$nopp."' and kodebarang='".$kode_brg."'";
        $sCek="select status from ".$dbname.".log_prapodt where nopp='".$nopp."' and status='0' ";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
		if($rCek>1)
		{
			$sql="select * from ".$dbname.".log_prapodt where".$where; 
			$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_ASSOC);
			$res=$query->fetch();
			if(($res['status']==0)&&($res['ditolakoleh']==0000000000))
			{
				$sql2="update ".$dbname.".log_prapodt set status='3',ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$alsnDtolak."' where".$where;
				try{
					$owlPDO->exec($sql2); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			else
			{
				echo"warning: Already exist";
				exit();
			}
		}
		else
		{
			echo"warning: this only has one item";
			exit();
		}
	break;
	
    case'getSummary':
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}    
		if($periode=='')
		{
		   $periode=date("Y-m");
		}
		
		$tab="<link rel=stylesheet type=text/css href=style/".$gen.">
			<script language=javascript1.2 src='js/generic.js'></script>
			<script language=javascript1.2 src='js/log_verivikasi.js'></script>";
		$tab.="<fieldset><legend>Summarry Purchaser</legend>";
		$tab.="Till month : <span id=tglPeriode>".$periode."</span><br />";
		$optper="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_prapoht  order by tanggal desc";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
		while($rPeriode=$qPeriode->fetch())
		{
			if($rPeriode['periode']!='0000-00')
			{
				$thn=explode("-", $rPeriode['periode']);
				if($thn[1]=='12')
				{
				$optper.="<option value='".substr($rPeriode['periode'],0,4)."' ".(substr($rPeriode['periode'],0,4)==$periode?'selected':'').">".substr($rPeriode['periode'],0,4)."</option>";
				}
				$optper.="<option value='".$rPeriode['periode']."' ".($rPeriode['periode']==$periode?'selected':'').">".substr($rPeriode['periode'],5,2)."-".substr($rPeriode['periode'],0,4)."</option>";
			}
		}
		$tab.="".$_SESSION['lang']['periode']." : <select id=period name=period onchange=getSumData()>".$optper."</select><br />";
		$tab.="<table border=0 cellspacing=1 cellpading=0 class=sortable><thead>
            <tr class=rowheader>
            <td rowspan='2'  align=center>No.</td>
            <td rowspan='2' align=center>".$_SESSION['lang']['purchaser']."</td>";
        $sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
        $qData=fetchData($sPt);
        $jumlahData=count($qData);
        $a=1;
		foreach($qData as $brsData => $rData)
		{
			$kdOrg[]=$rData;
			$tab.="<td colspan=4 align=center>".$rData['kodeorganisasi']."</td>";
			$a++;
		}
		$tab.="<tr class=rowheader>";
		for($acd=0;$acd<$jumlahData;$acd++)
		{
			$tab.="<td align=center'>Tot. Item</td><td align=center bgcolor='green'>On PO</td><td align=center bgcolor='red'>Blm PO</td><td align=center>% Out</td>";
		}
		$tab.="</tr></thead><tbody id=isiContain>";
		$sPur="select karyawanid,namakaryawan from ".$dbname.".datakaryawan 
			where (bagian like '%PRO%' or kodejabatan='17') and kodejabatan!='5' and (tanggalkeluar>='".date('Y-m-d')."' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
		
		$qPur=fetchData($sPur);
		$totalPo2=array();
		foreach($qPur as $brsKary) {
            foreach($kdOrg as $brsData3 => $rData3) {
                //get data blm po
				$sDt=" SELECT count(kodebarang) as jmlhPo,kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapoht a LEFT JOIN ".$dbname.".log_prapodt b ON a.nopp = b.nopp
					   WHERE  b.status='0' and kodeorg='".$rData3['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$periode."%' and b.purchaser='".$brsKary['karyawanid']."' ";
				// echo $sDt;
				$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
				$qDt->setFetchMode(PDO::FETCH_ASSOC);
				$rDt=$qDt->fetch();
				
                //get data sdh po
                $sDt2=" SELECT kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapoht a LEFT JOIN ".$dbname.".log_prapodt b ON a.nopp = b.nopp
                        LEFT JOIN ".$dbname.".log_podt c ON b.nopp = c.nopp  
                        WHERE b.status='0'  and kodeorg='".$rData3['kodeorganisasi']."' and substring(tanggal,1,7) like '%".$periode."%' and c.nopo!='' and b.purchaser='".$brsKary['karyawanid']."'  group by b.kodebarang  ";
				$qDt2=$owlPDO->query($sDt2) or die(print " Gagal: ".PDOException::getMessage());
				$qDt2->setFetchMode(PDO::FETCH_ASSOC);
				$jmlhPo2=owlBaris($qDt2);
                $rDt2=$qDt2->fetch();
				if(!isset($totalPo2[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($totalPo[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($all[$rDt['purchaser']][$rDt['kodeorg']])) $all[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($tempTotalPo2[$rDt['purchaser']])) $tempTotalPo2[$rDt['purchaser']]=0;
				if(!isset($sisa[$rDt['purchaser']])) $sisa[$rDt['purchaser']]=0;
                $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]+=$jmlhPo2;
                $totalPo[$rDt['purchaser']][$rDt['kodeorg']]+=$rDt['jmlhPo'];
                $all[$rDt['purchaser']][$rDt['kodeorg']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']]-$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
				$tempTotalPo2[$rDt['purchaser']]+=$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
				$sisa[$rDt['purchaser']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']];
            }
            $DtaAll[]=$brsKary;
        }
		$no=0;
		$totTrbitPO = $blmPO = $grndTotal = array();
		foreach($DtaAll as $brs)
		{
			$no++;
			$tab.="<tr class=rowcontent onclick=\"detailData('".$brs['karyawanid']."','".$periode."')\" style=\"cursor:pointer;\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$brs['namakaryawan']."</td>";
			foreach($kdOrg as $brsData2 => $rData2)
			{
				if(!empty($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']]))
				{
					@$persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=($all[$brs['karyawanid']][$rData2['kodeorganisasi']]/$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])*100;
				}
				if(!isset($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($all[$brs['karyawanid']][$rData2['kodeorganisasi']])) $all[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']])) $persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totTrbitPO[$rData2['kodeorganisasi']])) $totTrbitPO[$rData2['kodeorganisasi']]=0;
				if(!isset($blmPO[$rData2['kodeorganisasi']])) $blmPO[$rData2['kodeorganisasi']]=0;
				if(!isset($grndTotal[$rData2['kodeorganisasi']])) $grndTotal[$rData2['kodeorganisasi']]=0;
				$tab.="<td align=right>".number_format($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($all[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$totTrbitPO[$rData2['kodeorganisasi']]+=$totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$blmPO[$rData2['kodeorganisasi']]+=$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$grndTotal[$rData2['kodeorganisasi']]+=$all[$brs['karyawanid']][$rData2['kodeorganisasi']];
			}
		}
		$col=$a+2;
		$sAll="select count(*) as jmlh from ".$dbname.".log_prapodt where purchaser='0000000000'";
		$qAll=$owlPDO->query($sAll) or die(print " Gagal: ".PDOException::getMessage());
		$qAll->setFetchMode(PDO::FETCH_ASSOC);
		$rAll=$qAll->fetch();
		if(!empty($totalBlm)) {
			@$persenTot=($totalSemua/$totalBlm)*100;
		}
        $tab.="<tr class=rowcontent><td colspan=2>Total all Items</td>";
		if(!isset($presen)) $presen = array();
		foreach($kdOrg as $brsData2 => $rData2)
		{
			if($blmPO[$rData2['kodeorganisasi']]!=0) {
				@$presen[$rData2['kodeorganisasi']]=$totTrbitPO[$rData2['kodeorganisasi']]/$blmPO[$rData2['kodeorganisasi']]*100;
			} else {
				$presen[$rData2['kodeorganisasi']] = 0;
			}
			$tab.="<td align=right>".number_format($blmPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($totTrbitPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($grndTotal[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($presen[$rData2['kodeorganisasi']],0)."</td>";
		}
		$tab.="</tr>";
		$tab.="</tbody></table></fieldset>";
        echo $tab;
	break;
	
    case'getSummar':
		if($periode=='')
		{
			$periode=date("Y-m");
		}

		$sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qData=fetchData($sPt);
		$jumlahData=count($qData);
		$a=1;
		foreach($qData as $brsData => $rData)
		{
			$kdOrg[]=$rData;
		}

		$sPur="select karyawanid,namakaryawan from ".$dbname.".datakaryawan 
               where (bagian like '%PRO%' or kodejabatan='17') and kodejabatan!='5' and (tanggalkeluar>='".date('Y-m-d')."' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
		$qPur=fetchData($sPur);
		foreach($qPur as $brsKary)
		{
			foreach($kdOrg as $brsData3 => $rData3)
			{
				//get data blm po
				$sDt=" SELECT   count(kodebarang) as jmlhPo,kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
                                    WHERE  b.status='0'  and kodeorg='".$rData3['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$periode."%' and b.purchaser='".$brsKary['karyawanid']."'";
				$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
				$qDt->setFetchMode(PDO::FETCH_ASSOC);
				$rDt=$qDt->fetch();

                //get data sdh po
                $sDt2=" SELECT kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
                        LEFT JOIN ".$dbname.".log_podt c ON b.nopp = c.nopp  
                                    WHERE b.status='0' and kodeorg='".$rData3['kodeorganisasi']."' and substring(tanggal,1,7) like '%".$periode."%' and c.nopo!='' and b.purchaser='".$brsKary['karyawanid']."' group by b.kodebarang  ";
				$qDt2=$owlPDO->query($sDt2) or die(print " Gagal: ".PDOException::getMessage());
				$qDt2->setFetchMode(PDO::FETCH_ASSOC);
				$jmlhPo2=owlBaris($qDt2);
                $rDt2=$qDt2->fetch();
				if(!isset($totalPo2[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($totalPo[$rDt['purchaser']][$rDt['kodeorg']])) $totalPo[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($all[$rDt['purchaser']][$rDt['kodeorg']])) $all[$rDt['purchaser']][$rDt['kodeorg']]=0;
				if(!isset($tempTotalPo2[$rDt['purchaser']])) $tempTotalPo2[$rDt['purchaser']]=0;
				if(!isset($sisa[$rDt['purchaser']])) $sisa[$rDt['purchaser']]=0;
                $totalPo2[$rDt['purchaser']][$rDt['kodeorg']]+=$jmlhPo2;
                $totalPo[$rDt['purchaser']][$rDt['kodeorg']]+=$rDt['jmlhPo'];
                $all[$rDt['purchaser']][$rDt['kodeorg']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']]-$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
                $tempTotalPo2[$rDt['purchaser']]+=$totalPo2[$rDt['purchaser']][$rDt['kodeorg']];
                $sisa[$rDt['purchaser']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']];
                }
                $DtaAll[]=$brsKary;
		}
		
		$no=0;
		$tab = '';
		foreach($DtaAll as $brs)
		{
			$no++;
			$tab.="<tr class=rowcontent onclick=\"detailData('".$brs['karyawanid']."','".$periode."')\" style=\"cursor:pointer;\">";
			$tab.="<td>".$no."</td>";
			$tab.="<td>".$brs['namakaryawan']."</td>";
			foreach($kdOrg as $brsData2 => $rData2)
			{
				if(!empty($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])) {
					@$persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=($all[$brs['karyawanid']][$rData2['kodeorganisasi']]/$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])*100;
				}
				if(!isset($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']])) $totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($all[$brs['karyawanid']][$rData2['kodeorganisasi']])) $all[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']])) $persen5[$brs['karyawanid']][$rData2['kodeorganisasi']]=0;
				if(!isset($totTrbitPO[$rData2['kodeorganisasi']])) $totTrbitPO[$rData2['kodeorganisasi']]=0;
				if(!isset($blmPO[$rData2['kodeorganisasi']])) $blmPO[$rData2['kodeorganisasi']]=0;
				if(!isset($grndTotal[$rData2['kodeorganisasi']])) $grndTotal[$rData2['kodeorganisasi']]=0;
				$tab.="<td align=right>".number_format($totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($all[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$tab.="<td align=right>".number_format($persen5[$brs['karyawanid']][$rData2['kodeorganisasi']],0)."</td>";
				$totTrbitPO[$rData2['kodeorganisasi']]+=$totalPo2[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$blmPO[$rData2['kodeorganisasi']]+=$totalPo[$brs['karyawanid']][$rData2['kodeorganisasi']];
				$grndTotal[$rData2['kodeorganisasi']]+=$all[$brs['karyawanid']][$rData2['kodeorganisasi']];
			}
		}
		$col=$a+2;
		$sAll="select count(*) as jmlh from ".$dbname.".log_prapodt where purchaser='0000000000'";
		$qAll=$owlPDO->query($sAll) or die(print " Gagal: ".PDOException::getMessage());
		$qAll->setFetchMode(PDO::FETCH_ASSOC);
		$rAll=$qAll->fetch();
		if(!empty($totalBlm))
		{
			@$persenTot=($totalSemua/$totalBlm)*100;
		}
		$tab.="<tr class=rowcontent><td colspan=2>Total all Items</td>";
		if(!isset($presen)) $presen = array();
		foreach($kdOrg as $brsData2 => $rData2)
		{
			if($blmPO[$rData2['kodeorganisasi']]!=0)
			{
				@$presen[$rData2['kodeorganisasi']]=$totTrbitPO[$rData2['kodeorganisasi']]/$blmPO[$rData2['kodeorganisasi']]*100;
			} else {
				$presen[$rData2['kodeorganisasi']]=0;
			}
			$tab.="<td align=right>".number_format($blmPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($totTrbitPO[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($grndTotal[$rData2['kodeorganisasi']],0)."</td>";
			$tab.="<td align=right>".number_format($presen[$rData2['kodeorganisasi']],0)."</td>";
		}
		$tab.="</tr>";
		$tab.="</tbody></table></fieldset>";
		echo $tab."###".$periode;
	break;
	
	case 'dataDetail':
        $userid=$_GET['userid'];
        $kodeorg=$_GET['kodeorg'];
        $periode=$_GET['periode'];
        $stream.=" 
			<table border=\"1\">
			<thead>
			<tr>
			<td bgcolor=#DEDEDE align=center valign=middle>No.</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['nopp']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['kodebarang']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['namabarang']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['tanggal']." PR</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['tgldibutuhkan']."</td>             
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlhDiminta']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlh_disetujui']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['satuan']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['jmlh_hari_outstanding']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['nopo']."</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['tanggal']." PO</td>
			<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['namasupplier']."</td>
			</tr>
			</thead>
			<tbody>";
        
		$sql="SELECT   kodeorg,b.purchaser,jumlah,realisasi,a.tanggal,b.kodebarang,b.nopp,b.tgl_sdt FROM 
            ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
              WHERE  b.status='0'  and kodeorg='".$kodeorg."' and substr(tanggal,1,7) like '%".$periode."%' and purchaser='".$userid."'";
		$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);		
        while($bar=$res->fetch())
		{
			$no+=1;
            
			$sPp2="select nopo  from ".$dbname.".log_podt where nopp='".$bar->nopp."' and kodebarang='".$bar->kodebarang."'";
			$qPp2=$owlPDO->query($sPp2) or die(print " Gagal: ".PDOException::getMessage());
			$qPp2->setFetchMode(PDO::FETCH_OBJ);
			$rPp2=$qPp2->fetch();
			
			$sPp="select tanggal,kodesupplier from ".$dbname.".log_poht where nopo='".$rPp2->nopo."'";
			$qPp=$owlPDO->query($sPp) or die(print " Gagal: ".PDOException::getMessage());
			$qPp->setFetchMode(PDO::FETCH_OBJ);
			$rPp=$qPp->fetch();
			
			if($rPp->tanggal!='0000-00-00')
			{
				$tglA=substr($rPp->tanggal,0,4);
				$tglB=substr($rPp->tanggal,5,2);
				$tglC=substr($rPp->tanggal,8,2);
				$tgl2=$tglA.$tglB.$tglC;

				$tGl1=substr($bar->tanggal,0,4);
				$tGl2=substr($bar->tanggal,5,2);
				$tGl3=substr($bar->tanggal,8,2);
				$tgl2=$tglA.$tglB.$tglC;
				$tgl1 =$tGl1.$tGl2.$tGl3;

				$starttime=strtotime($tgl1);//time();// tanggal sekarang
				$endtime=strtotime($tgl2);//tanggal pembuatan dokumen
				$timediffSecond = abs($endtime-$starttime);
				$base_year = min(date("Y", $tGl1), date("Y", $tglA));
				$diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
				$jmlHari=date("j", $diff) - 1;
				$tglSkrg=$rPp->tanggal;
			}
			else
			{
				$tglSkrg=date("Y-m-d");
				$tglA=substr($bar->tanggal,0,4);
				$tglB=substr($bar->tanggal,5,2);
				$tglC=substr($bar->tanggal,8,2);

				$tgl2=$tglA.$tglB.$tglC;

				$tGl1=substr($tglSkrg,0,4);
				$tGl2=substr($tglSkrg,5,2);
				$tGl3=substr($tglSkrg,8,2);
				$tgl2=$tglA.$tglB.$tglC;
				$tgl1 =$tGl1.$tGl2.$tGl3;

				$starttime=strtotime($tgl1);//time();// tanggal sekarang
				$endtime=strtotime($tgl2);//tanggal pembuatan dokumen
				$timediffSecond = abs($endtime-$starttime);
				$base_year = min(date("Y", $tGl1), date("Y", $tglA));
				$diff = mktime(0, 0, $timediffSecond, 1, 1, $base_year);
				$jmlHari=date("j", $diff) - 1;
			}

			$sNmSup="select distinct namasupplier from ".$dbname.".log_5supplier where supplierid='".$rPp->kodesupplier."'";
			$qNmSup=$owlPDO->query($sNmSup) or die(print " Gagal: ".PDOException::getMessage());
			$qNmSup->setFetchMode(PDO::FETCH_ASSOC);
			$rNmSup=$qNmSup->fetch();
			
			$stream.="<tr>
				<td>".$no."</td>
				<td>".$bar->nopp."</td>
				<td>".$bar->kodebarang."</td>
				<td>".substr($rDtBrg[$bar->kodebarang],0,33)."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td>".tanggalnormal($bar->tgl_sdt)."</td>
				<td align=right>".number_format($bar->jumlah,2)."</td>
				<td align=right>".number_format($bar->realisasi,2)."</td>
				<td>".$nmSatuan[$bar->kodebarang]."</td>
				<td>".$jmlHari."</td> 
				<td>".$rPp2->nopo."</td> 
				<td>".tanggalnormal($tglSkrg)."</td>";
			$stream.= "<td>".$rNmSup['namasupplier']."</td>";
			$stream.="</tr>";
		}

		$stream.=" </tbody>";
		//=================================================

		$stream.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		$time=date("Hms");
		$nop_="listBarang_".$periode."_".$userid."_".$time;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $stream);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
			</script>";
	break;
	
	case 'dataDetailEx':
		$userid=$_GET['userid'];
		$periode=$_GET['periode'];
		$kodeorg=$_GET['kodeorg'];
		$thn=substr($periode,0,4);
		
		$sPur="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$userid."'";
		$qPur=$owlPDO->query($sPur) or die(print " Gagal: ".PDOException::getMessage());
		$qPur->setFetchMode(PDO::FETCH_ASSOC);
		$rPur=$qPur->fetch();

		$tab.="<table cellspacing=1 border=0 cellpading=0>
			<tr><td colspan=2>Purchaser</td><td> :</td><td colspan=3  align=left> ".$rPur['namakaryawan']."</td><td>&nbsp</td></tr>
			<tr><td colspan=2>".$_SESSION['lang']['periode']."</td><td> :</td><td colspan=3 align=left> ".$thn."</td><td>&nbsp</td></tr>
			 </table>";
		$tab.="
			<table cellspacing=1 border=1 cellpading=0>
			<thead>";
            
		$sPt="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qData=fetchData($sPt);
		$tab.="<tr class=rowheader>";
		$tab.="<td bgcolor=#DEDEDE align=center valign=middle>".$_SESSION['lang']['periode']."</td>";    
		foreach($qData as $brsData => $rData)
		{                   
		   $tab.="<td bgcolor=#DEDEDE align=center valign=middle>".$rData['kodeorganisasi']."</td>";
		}
		$tab.="<td bgcolor=#DEDEDE align=center valign=middle>Total Item</td><td bgcolor=#DEDEDE align=center valign=middle>Terbit PO</td>
			<td bgcolor=#DEDEDE align=center valign=middle>Outstanding PO</td>
			<td bgcolor=#DEDEDE align=center valign=middle>% Outstanding</td>";
		$tab.="</tr></thead><tbody>";
		
		$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".log_poht where substr(tanggal,1,4)='".$thn."' order by tanggal desc";
		$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
		$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
		while($rPeriode=$qPeriode->fetch())
		{
			$tab.="<tr class=rowcontent>";
			$tab.="<td>".$rPeriode['periode']."</td>";
			foreach ($qData as $brsData2 =>$rData2)
			{
				$sDt="SELECT count(kodebarang) as jmlhPo,kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
					  WHERE  b.status='0' and kodeorg='".$rData2['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$rPeriode['periode']."%' and purchaser='".$userid."' ";
				$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
				$qDt->setFetchMode(PDO::FETCH_ASSOC);
				$rDt=$qDt->fetch();

				$sDt2="SELECT  kodeorg,purchaser,substr(tanggal,1,7) as periode FROM ".$dbname.".log_prapodt b LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp
					   LEFT JOIN ".$dbname.".log_podt c ON b.nopp = c.nopp  
					   WHERE  b.status='0' and kodeorg='".$rData2['kodeorganisasi']."' and substr(tanggal,1,7) like '%".$rPeriode['periode']."%' and c.nopo!='' and purchaser='".$userid."'  group by b.kodebarang ";
				$qDt2=$owlPDO->query($sDt2) or die(print " Gagal: ".PDOException::getMessage());
				$qDt2->setFetchMode(PDO::FETCH_ASSOC);
				$rDt2=$qDt2->fetch();
				$jmlhPo2=owlBaris($qDt2);

				$totalPo2[$rDt2['purchaser']][$rDt2['kodeorg']][$rDt2['periode']]+=$jmlhPo2;
				$totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']]+=$rDt['jmlhPo'];
				$tempTotalPo2[$rDt2['purchaser']][$rDt2['periode']]+=$totalPo2[$rDt2['purchaser']][$rDt2['kodeorg']][$rDt2['periode']];
				$sisa[$rDt['purchaser']][$rDt['periode']]+=$totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']];
				if($totalPo[$rDt['purchaser']][$rDt['kodeorg']][$rDt['periode']]!=0)
				{
				   $tab.="<td align=right>".number_format($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";
				}
				else
				{
				  $tab.="<td align=right>".number_format($totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']],0)."</td>";  
				}
				 $jmlh[$rData2['kodeorganisasi']]+=$totalPo[$userid][$rData2['kodeorganisasi']][$rPeriode['periode']];

			}
			
			$totBlm[$userid][$rPeriode['periode']]=$sisa[$userid][$rPeriode['periode']]-$tempTotalPo2[$userid][$rPeriode['periode']];
			if($sisa[$userid][$rPeriode['periode']]!=0)
			{
				$persen[$userid][$rPeriode['periode']]=($totBlm[$userid][$rPeriode['periode']]/$sisa[$userid][$rPeriode['periode']])*100;
			}
			$tab.="<td  align=right>".number_format($sisa[$userid][$rPeriode['periode']],0)."</td>";
			$tab.="<td  align=right>".number_format($tempTotalPo2[$userid][$rPeriode['periode']],0)."</td>";
			$tab.="<td  align=right>".number_format($totBlm[$userid][$rPeriode['periode']],0)."</td>";
			$tab.="<td  align=right>".number_format($persen[$userid][$rPeriode['periode']],0)."</td>";
			$tab.="</tr>";
			$totItem+=$sisa[$userid][$rPeriode['periode']];
			$trbtPo+=$tempTotalPo2[$userid][$rPeriode['periode']];
			$blmPo+=$totBlm[$userid][$rPeriode['periode']];
			if($totItem!=0)
			{$totPersen=($blmPo/$totItem)*100;}
		}
		
		$tab.="<tr class=rowcontent><td>&nbsp;</td>";
		foreach ($qData as $brsData3 =>$rData3)
		{

		   $tab.="<td align=right>".number_format($jmlh[$rData3['kodeorganisasi']],0)."</td>";
		}
		$tab.="<td  align=right>".number_format($totItem,0)."</td>";
		$tab.="<td  align=right>".number_format($trbtPo,0)."</td>";
		$tab.="<td  align=right>".number_format($blmPo,0)."</td>";
		$tab.="<td  align=right>".number_format($totPersen,0)."</td>";
		$tab.="</tr>";        
		$tab.="</tbody>";
	
		//=================================================

        $tab.="</table>Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
		$jam=date("Hms");
		$nop_="listBarang__".$userid."_".$jam;
		$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
		gzwrite($gztralala, $tab);
		gzclose($gztralala);
		echo "<script language=javascript1.2>
			window.location='tempExcel/".$nop_.".xls.gz';
		</script>";

	break;
		 
	case 'getlokId':
        $klq="select lokasitugas from ".$dbname.".`datakaryawan` where karyawanid = '".$purchaser."' ";
		$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
        while($rst=$qry->fetch())
        {
            $lokasitugas=$rst->lokasitugas;
        }
        if(substr($lokasitugas,2,2)=='HO')echo "0"; else echo "1";
    break;
	
	case 'listVerivikasiPP':
		$optPur="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$klq="select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where  bagian='PRO' and tanggalkeluar='0000-00-00' order by namakaryawan asc ";
		$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
				
		while($rst=$qry->fetch())
		{
			$optPur.="<option value=".$rst->karyawanid.">".$rst->namakaryawan."</option>";
		}
        
		$cl=array(0=>'Head Office',1=>'Local');
		foreach($cl as $rw =>$isi)
		{
			$optLokasi.="<option  value='".$rw."'>".$isi."</option>";
		}
        
        $str="SELECT  distinct a.`tanggal`, a.`persetujuan1`, a.`persetujuan2`, a.`persetujuan3`, a.
			`persetujuan4`, a.`persetujuan5`, a.`close`, a.`hasilpersetujuan1`, a.`hasilpersetujuan2`, 
			a.`hasilpersetujuan3`, a.`hasilpersetujuan4`, a.`hasilpersetujuan5`, a.`tglp1`, a.`tglp2`, 
			a.`tglp3`, a.`tglp4`, a.`tglp5`,b.*,c.nopo FROM ".$dbname.".log_prapodt b 
			LEFT JOIN ".$dbname.".log_prapoht a ON a.nopp = b.nopp 
			LEFT JOIN ".$dbname.".log_podt c ON b.nopp=c.nopp  
			WHERE b.nopp='".$nopp."' and create_po!='1' and status!=3  group by kodebarang ORDER BY a.tanggal desc ";
		$res2=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$row=owlBaris($res2);

		echo"<input type=\"hidden\" id=ppno name=ppno value=".$nopp." />
		<fieldset><legend>".$nopp."</legend> 
		<table cellpadding=1 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				<td colspan=3>Verification Form</td>
			</tr>
			</thead>
			<tbody>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['jumlah']." Item</td><td id=totalBrg>".$row."</td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['purchaser']."</td><td><select id=purId2 name=purId2 style=width:150px; onchange='getlokId(this.value,".$nourut.")'>".$optPur."</select></td>
			</tr>
			<tr class=rowcontent>
				<td colspan=2>".$_SESSION['lang']['lokasitugas']."</td><td><select id=lokId name=lokId style=width:150px;>".$optLokasi."</select></td>
			</tr>
			<tr>
				<td colspan=3><button class=mybutton onclick=saveSemua(1) id=saveAll title=Simpan Semua>".$_SESSION['lang']['save']." ".$_SESSION['lang']['all']."</button><button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
			</tbody>
		</table><br />";

		echo"<fieldset><legend>".$_SESSION['lang']['list']." Item</legend>
		<div  style=overflow:auto;width:650px;height:275px;>
		<table class=\"sortable\" cellspacing=\"1\" border=\"0\">
			<thead>
			<tr class=rowheader>
				<td>No.</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['tanggal']." PP</td>
				<td>".$_SESSION['lang']['jmlhDiminta']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['harga']."</td>
			</tr>
			</thead>
			<tbody>";
            
		$res2->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res2->fetch())
		{			
			$koderorg=substr($bar->nopp,15,4);
			$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$koderorg."' or induk='".$koderorg."'"; //echo $spr;
			$rep=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
			$rep->setFetchMode(PDO::FETCH_ASSOC);
			$bas=$rep->fetch();
			
			$no+=1;
			$sPoDet="select distinct hargasatuan from ".$dbname.".log_podt where  kodebarang='".$bar->kodebarang."' order by nopo desc";
			$qPoDet=$owlPDO->query($sPoDet) or die(print " Gagal: ".PDOException::getMessage());
			$qPoDet->setFetchMode(PDO::FETCH_ASSOC);
			$rCek=$qPoDet->fetch();
			
			if($bar->realisasi=='')
			{
				$bar->realisasi=0;
			}

			echo"<tr class=rowcontent id='rew_".$no."'>
				<td>".$no."</td>
				<td id=kdBrg_".$no.">".$bar->kodebarang."</td>
				<td>".substr($rDtBrg[$bar->kodebarang],0,33)."</td>
				<td>".tanggalnormal($bar->tanggal)."</td>
				<td align=center id=jmlh_".$no.">".$bar->jumlah."</td>
				<td align=center  >".$bar->realisasi."</td>
				<td align=right  >".number_format($rCek['hargasatuan'],2)."</td>
			</tr>";
		}

		echo" </tbody>
			</table></div></fieldset></fieldset>";
	break;
	
	

	case 'listAddPP':
        $str="select distinct * from ".$dbname.".log_prapodt where nopp='".$nopp."' and status!=3";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $lstData=$res->fetch();
		
		echo"<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
		echo"<table class=\"sortable\" cellspacing=\"1\" border=\"0\"><thead>";
		echo"<tr class=rowheader><td>".$_SESSION['lang']['nopp']."</td><td>".$_SESSION['lang']['tanggalSdt']."</td></tr></thead><tbody>";
		echo"<tr class=rowcontent><td id=noppAja>".$lstData['nopp']."</td><td id=tglSdt>".tanggalnormal($lstData['tgl_sdt'])."</td></tr></tbody></table><br />";
		echo"<div id=listDataPP><table class=\"sortable\" cellspacing=\"1\" border=\"0\"><thead>";
		echo"<tr class=rowheader><td>".$_SESSION['lang']['namabarang']."</td><td>".$_SESSION['lang']['satuan']."</td><td>".$_SESSION['lang']['jumlah']."</td><td>*</td></tr></thead><tbody>";
		echo"<tr class=rowcontent><td><input type=text class=myinputtext onkeypress='return tanpa_kutip(event)' id=nmBarang onclick=\"cariBarang();\" /></td>
             <td><input type=text disabled class=myinputtext id=satuanForm /></td>
             <td><input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' id=jmlhBrg /></td>";
		echo"<td><img src=images/save.png class=zImgBtn onclick=tambahBarang() /></td>";
		echo"</tr></tbody></table><input type=hidden id=kdBarang /></div>";
		echo"<div id=cariBarang style=display:none>
			<fieldset style=float:left><legend>".$_SESSION['lang']['findnoBrg']."</legend>".$_SESSION['lang']['find']."<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=cariBarangGet()>Find</button></fieldset>
              <div id=container5></div></div>";
		echo"</fieldset>";

	break;
	
	case'loadTools':
		$tab="<table class=sortable border=0 cellspacing=1 cellpadding=1><thead>
			<tr class=rowheader>
				<td>No.</td>
				<td>".$_SESSION['lang']['kodept']."</td>
				<td>".$_SESSION['lang']['pt']."</td>
			</tr>
			</thead><tbody>";
		
		$sql="select distinct kodeorg  from ".$dbname.".log_prapoht where nopp in (select a.nopp from ".$dbname.".log_prapoht a left join ".$dbname.".log_prapodt b on a.nopp=b.nopp where close=2 and b.status<3 and purchaser=0)";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		
		$no=0;
		while($res=$query->fetch()) {
            $no+=1;
			$tab.="<tr class=rowcontent onclick=detailPo(".$no.") style='cursor:pointer'>
				<td align=center>".$no."</td>
				 <td id=kodeOrg_".$no.">".$res['kodeorg']."</td>
				<td>".$optNm[$res['kodeorg']]."</td></tr>";
			$tab.="<tr><td colspan=3><div id=dataPO_".$no."></div></td></tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
	break;
	
    case'loadPPDetail':
		$brsKe=$_POST['brsKe'];
		$tab="<img onclick=\"closeList(".$brsKe.");\" title=\"Tutup\" class=\"zImgBtn\" src=\"images/close.gif\">";
		$tab.="<table cellspacing=1 cellpadding=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
			</tr>
			</thead><tbody>";
			
		$sql2="select b.kodebarang,a.kodeorg,b.nopp from ".$dbname.".log_prapodt b left join ".$dbname.".log_prapoht a on a.nopp=b.nopp 
			where purchaser=0000000000 and status<3 and kodeorg='".$kodeorg."' and a.close=2 group by a.nopp order by substring(a.nopp,16,4) asc";
		$query=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$jmlData=owlBaris($query);
		
		$tab.="<tr  class=rowcontent><td colspan=5>Total PP :".$jmlData."</td></tr>";
		$no=0;
		while($rwd=$query->fetch())
		{
			$sJum="select count(kodebarang) as jumlah from ".$dbname.".log_prapodt where nopp='".$rwd['nopp']."' and purchaser=0000000000 and status<3";
			$qJum=$owlPDO->query($sJum) or die(print " Gagal: ".PDOException::getMessage());
			$qJum->setFetchMode(PDO::FETCH_ASSOC);
			$rJum=$qJum->fetch();
			$no+=1;
			$koderorg=substr($rwd['nopp'],15,4);
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td  onclick=\"getDataPP2('".$rwd['nopp']."')\" style=\"cursor:pointer\">".$rwd['nopp']."</td>
				<td align=center>".$koderorg."</td>
				<td align=right>".$rJum['jumlah']."</td>
				</tr>";
        }
		$tab.="</tbody></table>";
		echo $tab;
	break;
    
	case'loadBarang':
		$optBarang="<option value=''>".$_SESSION['lang']['all']."</option>";
		$sKodenbarna="select distinct kodebarang,namabarang from ".$dbname.".log_5masterbarang where substr(kodebarang,1,3) = '".$klmpKbrg."' order by namabarang asc";
		$qKodeBarang=$owlPDO->query($sKodenbarna) or die(print " Gagal: ".PDOException::getMessage());
		$qKodeBarang->setFetchMode(PDO::FETCH_ASSOC);
		while($rKodebarang=$qKodeBarang->fetch())
		{
			$optBarang.="<option value='".$rKodebarang['kodebarang']."'>".$rKodebarang['namabarang']."</option>";
		}
		echo $optBarang;
	break;
	
	case'getBarang':
		$tab="<fieldset><legend>".$_SESSION['lang']['result']."</legend>
		<div style=\"overflow:auto;max-height:295px;max-width:455px;\">
		<table cellpading=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
			</tr><tbody>";

		if($klmpKbrg!='')
		{
			$add=" and kelompokbarang='".$klmpKbrg."'";
		}
		
		$sLoad="select kodebarang,namabarang,satuan,inactive from ".$dbname.".log_5masterbarang where   (kodebarang like '%".$nmBrg."%' or namabarang like '%".$nmBrg."%') ".$add."";
        $qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$qLoad->fetch())
        {
            $no+=1;
            if($res['inactive']==1)
            {
				$tab.="<tr bgcolor='red' title='inactive'>";
            }
            else
            {
                $tab.="<tr class=rowcontent onclick=\"setData('".$res['kodebarang']."','".$res['namabarang']."','".$res['satuan']."')\" title='".$res['namabarang']."'>";
            }
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$res['kodebarang']."</td>";
            $tab.="<td>".$res['namabarang']."</td>";
            $tab.="<td>".$res['satuan']."</td>";
            $tab.="</tr>";
        }
        echo $tab;
	break;
	
	case'addBarangTopp':
		if($jmlh_realisai=='')
		{
			exit("Error: Quantity is obligatory");
		}
		if($kd_brng=='')
		{
			exit("Error: Material Code is obligatory");
		}
		
		$optNama=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$sData="select distinct lokalpusat,purchaser,tglAlokasi from ".$dbname.".log_prapodt where nopp='".$nopp."'";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		$rData=$qData->fetch();
		
		$sIns="insert into ".$dbname.".log_prapodt (nopp, kodebarang, jumlah, realisasi, keterangan, tgl_sdt, lokalpusat,  tglAlokasi, purchaser) values ('".$nopp."','".$kd_brng."','".$jmlh_realisai."','".$jmlh_realisai."','Tambah Barang oleh ".$_SESSION['empl']['name']."','".$tglSdt."','".$rData['lokalpusat']."','".$rData['tglAlokasi']."','".$rData['purchaser']."')";
		
		try{
			$owlPDO->exec($sIns); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	
	case'simpanstrategies':
		$str="update ".$dbname.".log_prapodt set hargalama='".$strategies."' where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
		$owlPDO->exec($str); 
	break;

	//Umar
	case 'ReturnlistPPBatch':
		$opt    = "<option value='' hidden>".$_SESSION['lang']['pilihdata']."</option>";
		$query  = "SELECT DISTINCT nopp FROM $dbname.log_prapoht WHERE close > 1";
		$result = fetchData($query, 'OBJECT');
		foreach ($result as $key => $value) {
			$opt .= "<option value='".$value->nopp."'>".$value->nopp."</option>";
		}


		$tab .= "<fieldset style='float:left;'>
			<legend>".$_SESSION['lang']['form']."</legend>
			<table border=0>
				<tr>
					<td>".$_SESSION['lang']['nopp']."</td>
					<td>:</td>
					<td>";
						// <select id='nopp' onchange='getDropData(this.value)'>".$opt."</select>
						// <img onclick=z.elSearch('nopp',event) class='zImgBtn' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
					$tab.="<input type=text id=nopp style=width:150px size=25 maxlength=50 class=myinputtext>
					<button class=mybutton onclick=getDropData()>".$_SESSION['lang']['find']."</button>";
					
					$tab.="</td>
				</tr>
			</table>
		</fieldset>

		<div id='detailDrop' style=clear:both>
			
			<div id='detailContainerDrop'></div>
		</div>";
		
		echo $tab;
	break;

	case 'getDropData':
		$tab .= "<table class='sortable' cellspacing='1' border='0' cellpadding=5 style='max-height:500px'>";
			$tab .= "<thead>";
				$tab .= "<tr class='rowheader'>";
					$tab .= "<th align='center'>No.</th>";
					$tab .= "<th align='center'>".$_SESSION['lang']['namabarang']."</th>";
					$tab .= "<th align='center'>".$_SESSION['lang']['jumlah']."</th>";
					$tab .= "<th align='center'><input type='checkbox' id='checkMaster' onclick='checkAll(this.checked)' checked></th>";
					$tab .= "<th align='center'>".$_SESSION['lang']['keterangan']."</th>";
				$tab .= "</tr>";
			$tab .= "</thead>";
			$tab .= "<tbody style='overflow:auto'>";
				$query  = "SELECT kodebarang, jumlah FROM $dbname.log_prapodt WHERE nopp = '$nopp' and kodebarang not in (select kodebarang from $dbname.log_listverifikasi where nopp = '$nopp') and status != '3'";
				$result = fetchData($query, 'OBJECT');
				$no     = 1;
				foreach ($result as $key => $value) {
					$tab .= "<tr class='rowcontent'>";
						$tab .= "<td align='center'>".$no++."</td>";
						$tab .= "<td align='left'>".$value->kodebarang." - ".$namabarang[$value->kodebarang]."</td>";
						$tab .= "<td align='right'>".number_format($value->jumlah)."</td>";
						$tab .= "<td align='center'><input type='checkbox' class='checkChild' nopp='".$nopp."' kodebarang='".$value->kodebarang."' checked></td>";
						$tab .= "<td align='center'><input type='text' id='keterangan#$value->kodebarang' class='myinputtext' style='width:90%'></td>";
					$tab .= "</tr>";
				}
				$tab .= "<tr class='rowcontent'>";
					$tab .= "<td colspan='5' align='center'><button onclick='returnBatchLoop(0,".($no-1).")' class='mybutton'>Drop</button></td>";
				$tab .= "</tr>";
			$tab .= "</tbody>";
		$tab .= "</table>";

		echo $tab;
	break;
	//End Umar
	
	default :
	break;
}
?>


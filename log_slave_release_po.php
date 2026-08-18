<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method=		$_POST['method'];
$pages = checkPostGet('page','');
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$user_id=		$_SESSION['standard']['userid'];
$rlse_user_id=	isset($_POST['id_user'])? $_POST['id_user']: '';
$this_date=		date("Y-m-d");
$tglR=			isset($_POST['tglR'])? $_POST['tglR']: '';
$ket=			isset($_POST['ket'])? $_POST['ket']: '';
$texkKrsi=		isset($_POST['texkKrsi'])? $_POST['texkKrsi']: '';
$tipeApp = "PO";
$jenisApp = "PO";

switch ($method) {
	case 'release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res=$query->fetch();
		if(($res['persetujuan1']!='') || ($res['persetujuan2']!='')) {
			if(($res['stat_release']==0 or $res['stat_release']==2) && ($res['useridreleasae']==0000000000)) {		
				$unopo="update ".$dbname.".log_poht set stat_release='1', useridreleasae='".$rlse_user_id."',tglrelease='".$this_date."' where nopo='".$nopo."' ";
				try{
					$owlPDO->exec($unopo); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			} else {
				exit("warning:PO Sudah Di Release atau sedang koreksi");
			}
		} else {
			exit("Error: Belum Ada Penanda Tangan Dari P0 ".$nopo."");
		}
		break;
	
	case 'un_release_po' :
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res=$query->fetch();

		if(($res['stat_release']==1) && ($res['useridreleasae']==$rlse_user_id))
		{		
			$unopo="update ".$dbname.".log_poht set statuspo='0', stat_release='0', useridreleasae='0000000000',tglrelease='0000-00-00' where nopo='".$nopo."' ";
			try{
				$owlPDO->exec($unopo); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		else
		{
			echo "warning:You Don`t Have Autorize to Unrelease This PO No. ".$nopo;
			exit();
		}
		break;
	
	case 'list_new_data_release_po':
		$txt_search = checkPostGet('txtSearchrpo','');
		$txt_tgl = checkPostGet('tglCarirpo','');
		$txt_filter = checkPostGet('filterId','');
		$filterSupplier = checkPostGet('filterSupplier','');

		##Cek jumlah penerimaan di gudang
		$brgCompr=array();
		$totBrg=array();
		$nomoPo="";
		$str="select sum(jumlahpesan) as jumlahpesan,sum(jumlahterima) as jumlahterima, sum(jumlahclose) as jumlahclose,kodebarang,nopo,satuanpo,satuanterima from ".$dbname.".log_po_terima_vw where nopo<>'' and closed=0 group by nopo,kodebarang order by nopo asc";
		#= Untuk Cek
		// $str="select sum(jumlahpesan) as jumlahpesan,sum(jumlahterima) as jumlahterima, sum(jumlahclose) as jumlahclose,kodebarang,nopo,satuanpo,satuanterima from ".$dbname. ".log_po_terima_vw where nopo<>'' and kodebarang='347040130' group by nopo,kodebarang order by nopo asc";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $val){
			#= Add Abdul
			// Data sesuai pengurangannya
				$selisih=$val['jumlahpesan']-$val['jumlahterima']-$val['jumlahclose'];
			#= Keluarkan satuan konversi dari Line 96 karena tidak terbaca jika di taruh situ
				if($val['satuanpo']!=$val['satuanterima']){
					$strx="select jumlah from ".$dbname.".log_5stkonversi where satuankonversi='".$val['satuanpo']."' and darisatuan='".$val['satuanterima']."' and kodebarang='".$val['kodebarang']."'";
					$resx=fetchdata($strx);
					$nilaikonversi=$resx[0]['jumlah'];
					// $hasilkonversi=($nilaikonversi*$val['jumlahterima'])+($val['jumlahclose']);
					$hasilkonversi=($val['jumlahpesan']/$nilaikonversi);
					if($hasilkonversi==$val['jumlahpesan']){
						$selisih=0;
					} else {
						$selisih=$hasilkonversi-$val['jumlahterima'];
					}
				}
			if($nomoPo!=$val['nopo']){
				// Jika selisih di taruh di sini, nilai dalam 1 po akan ikut data pertama (salah naruh)
				// $selisih=$val['jumlahpesan']-$val['jumlahterima']-$val['jumlahclose'];
				$nomoPo=$val['nopo'];
				
				$strx="select sum(jumlahpesan) as jmlbrg from ".$dbname.".log_podt where nopo='".$val['nopo']."'";
				$resx=fetchdata($strx);
				$totBrg[$nomoPo]=$resx[0]['jmlbrg'];
			}
			if($selisih==0){
				$brgCompr[$val['nopo']]+=$val['jumlahterima'];
			}
			// echo "<br/>";
			// echo $val['satuanpo'];
			// echo "<br/>";
			// echo $val['satuanterima'];
			// echo "<br/>";
			// echo $val['jumlahpesan'];
			// echo "<br/>";
			// echo $hasilkonversi;
			// echo "<br/>";
			// echo $selisih;
			# Di buat untuk cek, ketika ada PO barang yang belum di terimakan semua (jumlahnya), update flag nya dari script
			# line 120 agar barang bisa di terimakan
			if($selisih > 0) {
				$str="update ".$dbname.".log_poht set keteranganclose='',closed=0 where nopo='".$val['nopo']."'";
					try{$owlPDO->exec($str); }catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n".$sUpdateClose; die(); }
			}
			#= End Abdul
		}
	   
		if(count($brgCompr)!=0){
			foreach($brgCompr as $nopoLst=>$isiDt){
				if($totBrg[$nopoLst]==$isiDt){
					$str="update ".$dbname.".log_poht set keteranganclose='No PO:".$nopoLst.",Semua Barang Sudah Diterimakan,Tutup By System',closed=1 where nopo='".$nopoLst."'";
					try{$owlPDO->exec($str); }catch(PDOException $e){print " Gagal  !: " . $e->getMessage() . "\n".$sUpdateClose; die(); }
				}
			}
		}
		
		
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		
		$no=(($page*$limit));
		
		
		
		$where = "";
		if(!empty($txt_search)) {
			$where .= " and nopo LIKE  '%".$txt_search."%' ";
		}
		
		if(!empty($txt_tgl)) {
			$where .= " and tanggal LIKE '%".$txt_tgl."%' ";
		}
		if(!empty($filterSupplier)) {
			$where .= " and kodesupplier LIKE '%".$filterSupplier."%' ";
		}
		if(!empty($txt_filter)){
			if($txt_filter=='1'){
				##RELEASE
				$where.=" and statuspo in ('2','3') and stat_release='1' and closed='0'";
			}
			if($txt_filter=='2'){
				##UNRELEASE
				$where.=" and statuspo in ('0','1')";
			}
			if($txt_filter=='3'){
				##BECOME OUT STANDING
				$where.=" and statuspo in ('2','3') and closed='1' and keteranganclose like '%,tanggal tutup : %'";	
			}
			if($txt_filter=='4'){
				##CLOSE
				$where.=" and statuspo in ('2','3') and closed='1' and (keterangan like '%,tanggal tutup : %' or keteranganclose like '%Tutup By System%')";
			}
			if($txt_filter=='5'){
				##CENCEL
				$where.=" and statuspo in ('4') and closed='1' and (keteranganclose like '%,tanggal cancel : %')";
			}
		}

		##sesuai detail akses
		$whrdetailakses = " and left(kodeunit,4) in (".getOrgDetail(2).")";
		
		$countApp = getCountApproval($jenisApp,'');
		$str="select count(*) as jmlhrow from ".$dbname.".log_poht where nopo<>'' ".$where." ".$whrdetailakses."  order by tanggal desc";
		$res=fetchdata($str);
		$jlhbrs=$res[0]['jmlhrow'];
		$colspan=(14+$countApp);
		$tab="";
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$str="select * from ".$dbname.".log_poht where nopo<>'' ".$where." ".$whrdetailakses." order by tanggal desc limit ".$offset.",".$limit." ";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				##CEK INVOICE
				$strx="select count(*) as juminv from ".$dbname.".keu_tagihanht where nopo='".$val['nopo']."' and posting=1 ";
				$resx=fetchdata($strx);
				$juminv=$resx[0]['juminv'];
				
				##KETERANGAN
				$keterangan="";
				if($juminv>0){
					$keterangan="- Sudah ada Invoice<br>";
				}
				
				##USER RELEASE
				$pic="";
				$optPic = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['useridreleasae']."'");
				$picrelease = $optPic[$val['useridreleasae']];
				$optPic = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['userclosed']."'");
				$picclose = $optPic[$val['userclosed']];
				
				##STATUS PO
				$postatus="";
				$dsplycancel="";
				$dsplyclose="";
				$dsplybos="";
				if($val['statuspo']=='0'){
					$postatus="Unrelease";
					$keterangan.="- Belum diajukan";
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}else if($val['statuspo']=='1'){
					$postatus="Unrelease";
					$keterangan.="- Proses persetujuan";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}else if($val['statuspo']=='2'){
					if($val['closed']=='0'){
						$postatus="Release";
						$keterangan.="";
						$pic=$picrelease;
					}else{
						if(strpos($val['keteranganclose'], ",tanggal tutup : ")){
							$postatus = "Become Out Standing";
							$keterangan.=$val['keteranganclose'];
							$pic=$picclose;
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
						}
						if(strpos($val['keterangan'], ",tanggal tutup : ")){
							$postatus = "Close";
							$keterangan.=$val['keterangan'];
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic=$picclose;
						}
						if(strpos($val['keteranganclose'], "Tutup By System")){
							$postatus = "Close";
							$keterangan.="- Semua Barang Sudah Diterimakan";
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic="bysystem";
						}
					}
				}else if($val['statuspo']=='3'){
					if($val['closed']=='0'){
						$postatus="Release";
						$keterangan.="- Sudah ada penerimaan";
						$pic=$picrelease;
						$dsplycancel="style='display:none'";
					}else{
						if(strpos($val['keteranganclose'], ",tanggal tutup : ")){
							$postatus = "Become Out Standing";
							$keterangan.="- Sudah ada penerimaan<br>";
							$keterangan.="- ".$val['keteranganclose'];
							$pic=$picclose;
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
						}
						if(strpos($val['keterangan'], ",tanggal tutup : ")){
							$postatus = "Close";
							$keterangan.="- Sudah ada penerimaan<br>";
							$keterangan.="- ".$val['keterangan'];
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic=$picclose;
						}
						if(strpos($val['keteranganclose'], "Tutup By System")){
							$postatus = "Close";
							$keterangan.="- Semua Barang Sudah Diterimakan";
							$dsplycancel="style='display:none'";
							$dsplyclose="style='display:none'";
							$dsplybos="style='display:none'";
							$pic="bysystem";
						}
					}
				}else if($val['statuspo']=='4'){
					$postatus = "Cancel";
					$keterangan.="- ".$val['keteranganclose'];
					$pic=$picclose;
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}

				if($postatus == 'Close'){
					$unclosebtn="";
				}else{
					$unclosebtn="style='display:none'";
				}

				// cek apakah barang sudah diterimakan
				$str_ck="select * from ".$dbname.".log_transaksi_vw where nopo='".$val['nopo']."'";
				$resck=fetchData($str_ck);
				$jlh_ck = count($resck);
				if($jlh_ck > 0){
					$dsplychange="style='display:none'";
				}

				// cek apakah barang sudah diterimakan
				$str_ck_so="SELECT * FROM ".$dbname.".log_transaksiht where nopo in (select nopo from ".$dbname.".log_sorefrensi where noso = '".$val['nopo']."')";
				$resck_so=fetchData($str_ck_so);
				$jlh_ck_so = count($resck_so);
				if($jlh_ck_so > 0){
					$dsplychange="style='display:none'";
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}
				// cek apakah barang sudah diterimakan transit
				$str_ck_ts="select * from ".$dbname.".log_transit where nopo='".$val['nopo']."'";
				$resck_ts=fetchData($str_ck_ts);
				$jlh_ck_ts = count($resck_ts);
				if($jlh_ck_ts > 0){
					$dsplychange="style='display:none'";
					$dsplycancel="style='display:none'";
					// $dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}
				// cek apakah barang masuk ke soreferensi
				$str_ck_so_1="SELECT * FROM ".$dbname.".log_sorefrensi where nopo = '".$val['nopo']."' ";
				$resck_so_1=fetchData($str_ck_so_1);
				$jlh_ck_so_1 = count($resck_so_1);
				if($jlh_ck_so_1 > 0){
					$dsplychange="style='display:none'";
					$dsplycancel="style='display:none'";
					$dsplyclose="style='display:none'";
					$dsplybos="style='display:none'";
				}


				$notransaksi='';	
				$strgd="select notransaksi,tanggal from ".$dbname.".log_transaksiht where nopo='".$val['nopo']."'";
				$resgd=fetchData($strgd);
				foreach($resgd as $bargd){
					$notransaksi.="
					- No.Transaksi : ".$bargd['notransaksi']."<br>
					<span style='opacity:0'>-</span> Tanggal Penerimaan : ".$bargd['tanggal']." <br>
					<br>
					";
				}	
				
				$strgd="select notransaksi,tanggal from ".$dbname.".log_noninventory where nopo='".$val['nopo']."'";
				$resgd=fetchData($strgd);
				foreach($resgd as $bargd){
					$notransaksi.="
					 - No.Transaksi : ".$bargd['notransaksi']."<br>
					 <span style='opacity:0'>-</span>Tanggal Penerimaan : ".$bargd['tanggal']." <br>
					<br>
					";
				}



				#periksa chat
				$strChat="select *  from ".$dbname.".log_po_chat where nopo='".$val['nopo']."'";
				$resChat=$owlPDO->query($strChat) or die(print " Gagal: ".PDOException::getMessage());
				if(owlBaris($resChat)>0)
				{
					$ingChat="<img src='images/chat1.png' onclick=\"loadPOChat('".$val['nopo']."',event);\" class=resicon>";
				}
				else
				{
					$ingChat="<img src='images/chat0.png'  onclick=\"loadPOChat('".$val['nopo']."',event);\" class=resicon>";
				}
				
				$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
				$tab.="<tr id='tr_".$no."' ".($val['stat_release']==2?"bgcolor='orange'":"class=rowcontent")." style=vertical-align:top;>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td id=td_".$no.">".$val['nopo']."</td>";
				$tab.="<td style='text-align:center;min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=center>".$val['kodeorg']."</td>";
				$tab.="<td align=center>".$namasupplier[$val['kodesupplier']]."</td>";
				$tab.="<td align=left>".nl2br($val['uraian'])."</td>";
				$tab.="<td ".$bgcolor." align=center>".$ingChat."</td>";
				
				##BEGIN APPROVAL##
				for($i=1;$i<=$countApp;$i++){
					$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$i."' and kodeunit='".$val['kodeunit']."'";
					$resx=fetchData($strx);
					$tipeapp = $resx[0]['tipe'];
					$departemenapp = $resx[0]['departemen'];
					$tipekaryawanapp = $resx[0]['tipekaryawan'];
					$jabatanapp = $resx[0]['jabatan'];
					
					$arrDetail = detailApprove($i,$val['nopo'],$jenisApp);
					if($tipeapp=='1' && $arrDetail['status']!=''){
						if($arrDetail['status']!='1'){
							if($departemenapp!=''){
								$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
								$arrDetail['nama'] = $opttipe[$departemenapp];
							}
							
							if($tipekaryawanapp!=''){
								$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
								$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
							}
							
							if($jabatanapp!='0'){
								$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
								$arrDetail['nama'] = $opttipe[$jabatanapp];
							}
						}
					}
					$tab.="<td align=center style='vertical-align:top'>
						<b>".$arrDetail['nama']."</b>";
						if($arrDetail['nama']!=''){
							$tab.="<br>Status : ".(($arrDetail['status']=='9'||$arrDetail['status']=='')?"":$arrDetail['namastatus']);
							if($arrDetail['komentar']!=''){
								$tab.="<br>Comment : ".$arrDetail['komentar'];
								$tab.="<br><i>".tanggalnormal($arrDetail['tanggal'])."</i>";
							}
						}
					$tab.="</td>";
				}
				##END APPROVAL##
				
				$tab.="<td align=center>".$postatus."</td>";
				$tab.="<td align=center>".$pic."</td>";
				$tab.="<td style='min-width:120px'>".$keterangan."</td>";
				$tab.="<td style='width:250px'>".$notransaksi."</td>";
				$tab.="<td align=center>
					<img src=images/zoom.png class=resicon  title='Print' onclick=\"previewlinkpemenang('".$val['nodph']."', '".$val['kodesupplier']."', 'Detail Riwayat Perbandingan Harga' ,event)\">&nbsp;
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$val['nopo']."','','log_slave_print_detail_po',event);\">
				</td>";
				$tab.="<td align=center nowrap>
					<button class=mybutton  ".$dsplycancel." onclick=\"cancelpoform('Cancel','".$val['nopo']."',event)\"><br>Cancel<br>&nbsp;</button>
					<button class=mybutton  ".$dsplyclose." onclick=\"closeedPo('Close','".$val['nopo']."',event)\"><br>Close<br>&nbsp;</button>
					<button class=mybutton  ".$unclosebtn." onclick=\"unclose('".$val['nopo']."',event)\">Unclose</button>
					<button class=mybutton  ".$dsplybos." onclick=\"bospo('Become Out Standing','".$val['nopo']."',event)\">Become<br>Out<br>Standing</button>
				</td>";
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
		break;
		
	case'unclose':
		// cek tipe
		$strx="select tipepo,userclosed,keteranganclose from ".$dbname.".log_poht where nopo='".$nopo."'";
		$resx=fetchdata($strx);
		$tipepo=$resx[0]['tipepo'];
		$userclosed=$resx[0]['userclosed'];
		$keteranganclose=$resx[0]['keteranganclose'];

		// ambil jumlah PO
		$strx="select sum(jumlahpesan-jmlhstlhclose) as jmlbrg from ".$dbname.".log_podt where nopo='".$nopo."'";
		$resx=fetchdata($strx);
		$totBrg=$resx[0]['jmlbrg'];
		
		// ambil sudah di terimakan
		if($tipepo == 'PO'){
			$strx="select sum(jumlah) as jmlbrgterima from ".$dbname.".log_transaksidt where nopo='".$nopo."' and statussaldo='1' ";
			$resx=fetchdata($strx);
			$totBrgterima=$resx[0]['jmlbrgterima'];
		}else{
			$strx="select sum(jumlah) as jmlbrgterima from ".$dbname.".log_noninventorydt_vw where nopo='".$nopo."' and posting='1' ";
			$resx=fetchdata($strx);
			$totBrgterima=$resx[0]['jmlbrgterima'];
		}

		if($userclosed != '0000000000' || $userclosed == ''){
			exit("warning: PO Sudah di close oleh user ".getNamaKaryawan($userclosed)." dengan catatan = ".$keteranganclose." ");
		}

		if($totBrg == $totBrgterima){
			exit("warning: Barang sudah diterimakan semua!!!");
		}

		$str="update ".$dbname.".log_poht set keteranganclose='',closed=0 where nopo='".$nopo."'";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case'getFormTolak':
		echo"<br /><div id=rejected_form>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$nopo." class=myinputtext  style=\"width:150px;\" maxlength=\"50\" /></legend>
		<table cellspacing=1 border=0>
		<tr>
		<td colspan=3>
		Apakah Anda Akan Menolak No.PO Di Atas </td></tr>
		<tr><td>".$_SESSION['lang']['keterangan']."</td><td>:</td><td><input type=text class=myinputtext onkeypress=\"return tanpa_kutip(event)\" id=ket name=ket style=\"width:150px;\" /></td></tr>
		<tr><td colspan=3 align=center>
		<button class=mybutton onclick=tolakPo() >".$_SESSION['lang']['yes']."</button>
		<button class=mybutton onclick=cancel_po() >".$_SESSION['lang']['no']."</button>
		</td></tr></table>
		
		</fieldset>
		</div>
		<input type=hidden name=method id=method  /> 
		<input type=hidden name=user_id id=user_id value=".$user_id." />
		<input type=hidden name=nopo id=nopo value=".$nopo."  />
		";
		break;
            
            
	case'tolakPo':
		if($ket=="")
		{
			echo"warning:Keterangan Tidak Boleh Kosong";
			exit();
		}
		$sUp="update ".$dbname.".log_poht set hasilpersetujuan2='2',persetujuan2='".$user_id."',tglp2='".$this_date."',keterangan='".$ket."',stat_release='1', useridreleasae='".$user_id."',tglrelease='".$this_date."', tanggal='".$this_date."' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sUp); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	case'insertKoreksi':
		$sUpd="update ".$dbname.".log_poht set catatanrelease='".$texkKrsi."',stat_release='2' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sUpd); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
		
	case'cancelpoform':
		$tab.="<fieldset><table cellpadding=1 cellspacing=1>";
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>
			<tr>
				<td valign=top>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top>:</td>
				<td valign=top>
					<textarea id=ketClose></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=cancelpo('".$_POST['nopo']."')>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table></fieldset>";
		echo $tab;
	break;
		
	case'closeForm':
		$optPil="";
		$aarpil=array("0"=>"Tutup PO","1"=>"Become Out Standing");
		foreach($aarpil as $lstPil=>$disPil){
			$optPil.="<option value='".$lstPil."'>".$disPil."</option>";
		}
		$tab.="<script language=JavaScript1.2 src=js/generic.js></script>
			   <script type=\"text/javascript\" src=\"js/log_release_po.js\"></script>";
		$tab.="<link rel=stylesheet type=text/css href=style/generic.css>";
		$tab.="<fieldset><table cellpadding=1 cellspacing=1>";
		$tab.="<table cellpadding=3>
			<tr>
				<td>".$_SESSION['lang']['nopo']."</td>
				<td>:</td>
				<td>".$nopo."</td>
			</tr>
			<tr>
				<td valign=top>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top>:</td>
				<td valign=top>
					<textarea id=ketClose></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=tutpDt('".$_POST['nopo']."')>".$_SESSION['lang']['tutup']."</button>
				</td>
			</tr>
		</table></fieldset>";
		echo $tab;
	break;
	
	case'bospo':
		$tab="";
		
		$tab.="<fieldset>
		<legend style='font-weight:bold'><i>".$_SESSION['lang']['nopo']." : ".$nopo."</i></legend>";
		$tab.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
			<thead>
			<tr class=rowheader style='text-align:center'>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['kodebarang']."</td>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['nopp']."</td>
				<td>".$_SESSION['lang']['sudahditerima']."</td>
				<td>Become Out Standing</td>
				<td>".$_SESSION['lang']['kuantitaspo']."</td>
				<td width=75px>Return</td>
			</tr>
			</thead>
			<tbody>";
		
		##GET DETAIL PO
		$no=0;
		$str="select * from ".$dbname.".log_podt where nopo='".$nopo."' order by kodebarang asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			$optnamabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##CEK JUMLAH YANG SUDAH DITERIMA
			$sudahditerima=0;
			$strx="select sum(jumlahpesan) as jumlahpesan,sum(jumlahterima) as jumlahterima,kodebarang,nopo,satuanpo,satuanterima from ".$dbname.".log_po_terima_vwx where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlahterima'];	
				
				if($valx['satuanpo']!=$valx['satuanterima']){
					$strxx="select jumlah from ".$dbname.".log_5stkonversi where satuankonversi='".$valx['satuanpo']."' and darisatuan='".$valx['satuanterima']."' and kodebarang='".$valx['kodebarang']."'";
					$resxx=fetchdata($strxx);
					$nilaikonversi=$resxx[0]['jumlah'];
					$sudahditerima+=($valx['jumlahterima']*$nilaikonversi);
				}
				
			}
			$sisa=$val['jumlahpesan']-$sudahditerima-$val['jmlhstlhclose'];
			$disabled="";
			if($sisa==0){
				$disabled="disabled";
			}
			
			$tab.="<tr class=rowcontent id='tr_".$no."' style='vertical-align:top'>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center id='kodebarang_".$no."'>".$val['kodebarang']."</td>";
			$tab.="<td align=left id='namabarang_".$no."'>".$optnamabarang[$val['kodebarang']]."</td>";
			$tab.="<td id='satuan_".$no."'>".$val['satuan']."</td>";
			$tab.="<td id='nopp_".$no."'>".$val['nopp']."</td>";
			$tab.="<td id='sudahditerima_".$no."' align=right>".hidezerodecimal($sudahditerima,3)."</td>";
			$tab.="<td id='bos_".$no."' align=right>".hidezerodecimal($val['jmlhstlhclose'],3)."</td>";
			$tab.="<td id='jumlahpesan_".$no."' align=right>".hidezerodecimal($val['jumlahpesan'],3)."</td>";
			$tab.="<td align=center>
				<input type=text ".$disabled." class=myinputtextnumber id='diterima_".$no."' onkeypress=\"return angka_doang(event);\" placeholder=0 style=width:70px maxlength=12>
			</td>";
			$tab.="</tr>";
		}
		
		## GET PURCHASER
		$str="select purchaser from ".$dbname.".log_poht where nopo='".$nopo."'";
		$res=fetchdata($str);
		$purchaser=$res[0]['purchaser'];
			
		$tab.="<tr>
			<td colspan=8 align=center>
			<table cellpadding=3>
				<tr>
					<td valign=top>".$_SESSION['lang']['purchaser']."</td>
					<td valign=top>:</td>
					<td valign=top>".getNamaKaryawan($purchaser)."</td>
				</tr>
				<tr>
					<td valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td valign=top>:</td>
					<td valign=top>
						<textarea id=ketClose></textarea>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td>
						<button class=mybutton onclick=closebos('".$nopo."','".$no."')>".$_SESSION['lang']['save']."</button>
					</td>
				</tr>
			</table>
			</td>
		</tr></tbody>";
		
			
		$tab.="</fieldset>";
		
		echo $tab;
	break;
		
	case'closepo':
		try {
			$owlPDO->beginTransaction();
			
			$nopo = checkPostGet('nopo','');
			$keterangan = checkPostGet('ketClose','');
			$isiketerangan=$keterangan.' ,tanggal tutup : '.date('d-m-Y');
			
			##CEK SPK
			$str="select spk from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			$incspk = '0';
			foreach($res as $key=>$val){
				if($val['spk']=='1'){
					$incspk = '1';
				}
			}
			if($incspk=='1'){
				$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$nopo."'";
				$res=fetchdata($str);
				$jlhspk = count($res);
				if($jlhspk > 0){
					throw new PDOException("Tidak dapat tutup PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
				}
			}
		
			##STATUS RELEASE
			$stats='';
			$str="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			foreach($res as $key=>$val){
				$stats=$val['stat_release'];
			}
		
			##TUTUP PO 
			$str="select kodebarang,nopp,jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$strx="update ".$dbname.".log_prapodt set status=1,ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$keterangan."' where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($strx); 
			}
			
			$str="update ".$dbname.".log_poht set closed=1,keterangan='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
			$owlPDO->exec($str); 
			
			$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$nopo."'";
			$owlPDO->exec($str);
			
			if($stats=='' or $stats=='null' or $stats==0){
				$str="delete from ".$dbname.".approval where notransaksi='".$nopo."'";
				$owlPDO->exec($str); 
			}
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}		
	break;
	
	case'cancelpo':
		try {
			$owlPDO->beginTransaction();
			
			$nopo = checkPostGet('nopo','');
			$keterangan = checkPostGet('ketClose','');
			$isiketerangan=$keterangan.' ,tanggal cancel : '.date('d-m-Y');
			$tglskrg=date("Y-m-d H:i:S");
			
			##CEK SPK
			$lkdbrg="";
			$str="select spk,kodebarang from ".$dbname.".log_podt where nopo='".$nopo."'";
			$res=fetchdata($str);
			$incspk = '0';
			$no=0;
			foreach($res as $key=>$val){
				if($val['spk']=='1'){
					$incspk = '1';
				}
				if($no==0){
					$lkdbrg="'".$val['kodebarang']."'";
				}else{
					$lkdbrg.=",'".$val['kodebarang']."'";
				}
				$no++;
			}
			
			if($incspk=='1'){
				$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$nopo."'";
				$res=fetchdata($str);
				$jlhspk = count($res);
				if($jlhspk > 0){
					throw new PDOException("Tidak dapat cancel PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
				}
			}
			
			##GET DATA PO HT
			$str="select nodph from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			$norph=$res[0]['nodph'];
			
			##GET NO RFQ
			$lnodph="";
			$strx="select nomor from ".$dbname.".log_permintaanhargadt where norph='".$norph."' and kodebarang in (".$lkdbrg.")";
			$resx=fetchdata($strx);
			$no=0;
			foreach($resx as $valx){
				if($no==0){
					$lnodph="'".$valx['nomor']."'";
				}else{
					$lnodph.=",'".$valx['nomor']."'";
				}
				$no++;
			}
			// $nodph=$res[0]['nomor'];
			// print_r($lnodph);
			
			## INSERT NEW PERMINTAAN
			$no=0;
			$newnorfq="";
			$str="select * from ".$dbname.".log_permintaanhargadt where nomor in (".$lnodph.") and kodebarang in (".$lkdbrg.") order by nomor";
			echo $str;
			$res=fetchdata($str);
			$tempno="";
			foreach($res as $val){
				$no++;
				if($tempno!=$val['nomor']){
					## CREATE NEW NO RFQ
					$expno = explode('/',$val['nomor']);
					$myno = "/".$expno[2]."/".$expno[3]."/".$expno[4];
					$strx="select nomor from ".$dbname.".log_perintaanhargaht where nomor like '%".$myno."%' order by nomor desc limit 0,1";
					$resx=fetchdata($strx);
					$dt=explode("/",$resx[0]['nomor']);
					$newnorfq = addZero(($dt[0]+1),3)."/".$expno[1]."".$myno;
					
					$komentarrfq="No. Ref RPH : ".$val['nomor']."<br>Note : PO/SO ".$nopo." di-cancel oleh ".getNamaKaryawan($_SESSION['standard']['userid'])." dengan alasan ".$keterangan;
					$komentarrfqnew="No. Ref RPH Baru : ".$newnorfq."<br>Note : PO/SO ".$nopo." di-cancel oleh ".getNamaKaryawan($_SESSION['standard']['userid'])." dengan alasan ".$keterangan;
					
					$strx="insert into ".$dbname.".log_perintaanhargaht (nomor,tanggal,purchaser,supplierid,id_alamat_supplier,nourut,id_franco,stock,catatan,sisbayar,sisbayar2,ppn,subtotal,diskonpersen,nilaidiskon,nilaipermintaan,matauang,kurs,tgldari,tglsmp,flag,catatanmenang,po,pbbkb,tolakrph,nodphlama,keterangan,lokasikirim,statuskirim,durasipengiriman,durasipekerjaan,garansiproduk,posisistok,asuransi,komentar,nilai1s,nilai1f,nilai2s,nilai2f,nilai3s,nilai3f,nilai4s,nilai4f,nilai5s,nilai5f) select '".$newnorfq."','".date('Y-m-d')."',purchaser,supplierid,id_alamat_supplier,nourut,id_franco,stock,catatan,sisbayar,sisbayar2,ppn,subtotal,diskonpersen,nilaidiskon,nilaipermintaan,matauang,kurs,tgldari,tglsmp,'0','','0',pbbkb,'0','".$val['nomor']."',keterangan,lokasikirim,statuskirim,durasipengiriman,durasipekerjaan,garansiproduk,posisistok,asuransi,'".$komentarrfq."',nilai1s,nilai1f,nilai2s,nilai2f,nilai3s,nilai3f,nilai4s,nilai4f,nilai5s,nilai5f from ".$dbname.".log_perintaanhargaht where nomor='".$val['nomor']."'";
					$owlPDO->exec($strx);
					
					$strx="update ".$dbname.".log_perintaanhargaht set komentar='".$komentarrfqnew."' where nomor='".$val['nomor']."'";
					$owlPDO->exec($strx);
				}
				$tempno=$val['nomor'];
				
				$strx="insert into ".$dbname.".log_permintaanhargadt (nomor,nourut,kodebarang,hargaterakhir,harga,merk,spec,jumlah,nopp,flag,norph,verificator,tanggalverifikasi,score,factor) values ('".$newnorfq."','".$val['nourut']."','".$val['kodebarang']."','".$val['hargaterakhir']."','".$val['harga']."','".$val['merk']."','".$val['spec']."','".$val['jumlah']."','".$val['nopp']."','0','','','','".$val['score']."','".$val['factor']."')";
				$owlPDO->exec($strx);
				
				##CREATE OUT STANDING PURCHASER RFQ
				$optPurchaser=makeOption($dbname,"log_poht","nopo,purchaser","nopo='".$nopo."'");
				$purchaser=$optPurchaser[$nopo];
				$str="update ".$dbname.".log_listverifikasi set pemenang='0' where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($str);			

				##UPDATE PR/SR DETAIL
				$sup="update ".$dbname.".log_prapodt set create_po=0,status=0 where nopp='".$val['nopp']."' and kodebarang='".$val['kodebarang']."'";
				$owlPDO->exec($sup);
			}
			
			##UPDATE STATUS PO
			$str="update ".$dbname.".log_poht set statuspo='4',closed='1',keteranganclose='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
			$owlPDO->exec($str); 
			
			##DELETE APPROVAL WAITING
			$str="delete from ".$dbname.".approval where notransaksi='".$nopo."' and status='0'";
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}		
	break;
	
	case'closebos':
		try {
			$owlPDO->beginTransaction();
			$nopo = checkPostGet('nopo','');
			$keterangan = checkPostGet('keterangan','');
			$tglskrg=date("Y-m-d H:i:S");
			$isiketerangan="";
			
			$str="select keteranganclose,purchaser  from ".$dbname.".log_poht where nopo='".$nopo."'";
			$res=fetchdata($str);
			$ktclose=$res[0]['keteranganclose'];
			$purchaser=$res[0]['purchaser'];
			if($ktclose!=''){
				$isiketerangan.=$ktclose."<br>";
			}
			$isiketerangan.=$keterangan.' ,tanggal tutup : '.date('d-m-Y');
			
			$errmsg="";
			$newnopp="";
			$no=0;
			$countno=0;
			foreach($_POST['kodebarang'] as $key=>$val){
				$no++;
				$kodebarang=$val;
				
				##GET DETAIL PO
				$jlhclose=0;
				$strx="select jmlhstlhclose from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$kodebarang."'";
				$resx=fetchdata($strx);
				foreach($resx as $valx){
					$jlhclose+=$valx['jmlhstlhclose'];					
				}
				
				$nopp=$_POST['nopp'][$key];
				$sudahditerima=str_replace(",","",$_POST['sudahditerima'][$key]);
				$jumlahpesan=str_replace(",","",$_POST['jumlahpesan'][$key]);
				$diterima=str_replace(",","",$_POST['diterima'][$key]);
				$retur=($diterima==''?0:$diterima);
				$totretur=$jumlahpesan-$sudahditerima-$jlhclose;
				
				if($retur!=0){
					if($retur > $totretur){
						if($totretur < 0){
							$totretur=0;
						}
						$errmsg.="Kode barang ".$kodebarang." hanya bisa di return sebanyak ".$totretur;
					}
				}else{
					$countno++;
				}
				// exit('warning '.$retur." || ".$diterima." || ".$totretur);
				if($no==1){
					
					##CREATE NEW PR
					$awal=0;
					$nourut=substr($nopp,0,3);
					$crnopp=str_replace($nourut,'',$nopp);
					$str="select nopp from ".$dbname.".log_prapoht where nopp like '%".$crnopp."' order by nopp desc limit 1";
					$res=fetchdata($str);
					$awal=substr($res[0]['nopp'],0,3);
					$awal=intval($awal) + 1;
					$counter=addZero($awal,3);
					$newnopp=$counter."".$crnopp;
					
					$strx="select * from ".$dbname.".log_prapoht where nopp='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".log_prapoht values ('".$valx['pt']."','".$valx['unit']."','".$valx['tipepp']."','".$newnopp."','".$valx['tanggal']."','".$valx['keterangan']."','".$valx['dibuat']."','".$valx['requester']."','".$valx['close']."','".$nopo."')";
						$owlPDO->exec($strx);
					}
					
					
					##CREATE LIST FILE
					$strx="select * from ".$dbname.".listfileupload where notransaksi='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".listfileupload values ('','".$newnopp."','".$valx['namafile']."','".$valx['formaticon']."','".$valx['kriteriaefil']."','".$valx['status']."','".$valx['createdby']."','".$valx['createdtime']."')";
						$owlPDO->exec($strx);
					}
					
					##CREATE APPROVAL
					$strx="select * from ".$dbname.".approval where notransaksi='".$nopp."'";
					$resx=fetchdata($strx);
					foreach($resx as $valx){
						$strx="insert into ".$dbname.".approval values ('','".$newnopp."','".$valx['jenispersetujuan']."','".$valx['level']."','".$valx['karyawanid']."','".$valx['status']."','".$valx['komentar']."','".$valx['keterangan']."','".$valx['tanggal']."')";
						$owlPDO->exec($strx);
					}
					
					##UPDATE PO
					$isiketerangan.="<br>No. Ref PR/SR : ".$newnopp;
					$strx="update ".$dbname.".log_poht set closed='1', keteranganclose='".$isiketerangan."', userclosed='".$_SESSION['standard']['userid']."' where nopo='".$nopo."'";
					$owlPDO->exec($strx);
				}
				
				if($retur>0){

					#= Cek apakah ada konversi
					$sql = "select * from ".$dbname.".log_5stkonversi";
					$res = fetchData($sql);

					foreach($res as $val) {
						$jumlahKonversi[$val['kodebarang']] = $val['jumlah'];
					}

					#= Cek apakah ada nilai konversi
					if ($jumlahKonversi[$kodebarang] > 0) {
						#= Get Data Jika yang di kembalikan sebagian
						#= Become Out Standing sebagian
						#= Caranya 
						#= Berapa jumlah retur * jumlah konversi
						#= Itulah nilai satuan sebelumnya untuk jumlah pp
						$jumlahRetur[$kodebarang] = round($retur/$jumlahKonversi[$kodebarang]);
						// exit('warning '.print_r($jumlahRetur));
						##INSERT PR DETAIL
						$strx="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							$strx="insert into ".$dbname.".log_prapodt values ('".$newnopp."','".$kodebarang."','".$valx['stock']."','".$jumlahRetur[$valx['kodebarang']]."','".$jumlahRetur[$valx['kodebarang']]."','".$jumlahRetur[$valx['kodebarang']]."','".$valx['hargasatuan']."','".$valx['satuanpp']."','".$valx['satuankonversi']."','".$retur."','".$valx['keterangan']."','".$valx['anggaran']."','".$valx['tgl_sdt']."','".$valx['prioritas']."','0','".$valx['pembelian']."','".$valx['lokalpusat']."','0','".$valx['tglAlokasi']."','".$valx['alasanstatus']."','".$purchaser."','".$valx['ditolakoleh']."','".$valx['kodevhc']."','".$valx['updateby']."','".$valx['updatetime']."','".$valx['keteranganubah']."','".$valx['hargalama']."','".$valx['spk']."','".$valx['kmhm']."','".$valx['nokontrak']."','','','','".$valx['noakunbudget']."')";
							$owlPDO->exec($strx);
						}
					} else {	
						// exit('warning');
						##INSERT PR DETAIL
						$strx="select * from ".$dbname.".log_prapodt where nopp='".$nopp."' and kodebarang='".$kodebarang."'";
						$resx=fetchdata($strx);
						foreach($resx as $valx){
							$strx="insert into ".$dbname.".log_prapodt values ('".$newnopp."','".$kodebarang."','".$valx['stock']."','".$retur."','".$retur."','".$retur."','".$valx['hargasatuan']."','".$valx['satuanpp']."','".$valx['satuankonversi']."','".$valx['hasilkonversi']."','".$valx['keterangan']."','".$valx['anggaran']."','".$valx['tgl_sdt']."','".$valx['prioritas']."','0','".$valx['pembelian']."','".$valx['lokalpusat']."','0','".$valx['tglAlokasi']."','".$valx['alasanstatus']."','".$purchaser."','".$valx['ditolakoleh']."','".$valx['kodevhc']."','".$valx['updateby']."','".$valx['updatetime']."','".$valx['keteranganubah']."','".$valx['hargalama']."','".$valx['spk']."','".$valx['kmhm']."','".$valx['nokontrak']."','','','','".$valx['noakunbudget']."')";
							$owlPDO->exec($strx);
						}
					}
						
					##INSERT PR VERIFIKASI
					$strx="insert into ".$dbname.".log_listverifikasi values ('','".$newnopp."','".$kodebarang."','".$purchaser."','0','0','0','".$_SESSION['standard']['userid']."','".$tglskrg."','".$_SESSION['standard']['userid']."','".$tglskrg."')";
					$owlPDO->exec($strx);
					
					$strx="update ".$dbname.".log_podt set jmlhstlhclose=jmlhstlhclose+'".$retur."' where nopo='".$nopo."' and kodebarang='".$kodebarang."'";
					$owlPDO->exec($strx);
				}
			}
			
			if($countno==$no){
				throw new PDOException("Periksa kembali item barang, jumlah item masih 0");
			}
			
			if($errmsg!=''){
				throw new PDOException($errmsg);
			}
			
			// throw new PDOException($purchaser);
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			// echo "Warning \n" . addslashes($e->getMessage()."__".$strx);
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
		
	case'tutupData':
		$isiketerangan=$_POST['ketClose'].' ,tanggal tutup : '.date('d-m-Y');
		
		$str="select spk from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
		$res=fetchdata($str);
		$incspk = '0';
		foreach($res as $key=>$val){
			if($val['spk']=='1'){
				$incspk = '1';
			}
		}

		$stats='';
		$str="select stat_release from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$stats=$val['stat_release'];
		}
		
		if($incspk=='1'){
			$str="select * from ".$dbname.".lgl_pengajuanspkht where divisi='".$_POST['nopo']."'";
			$res=fetchdata($str);
			$jlhspk = count($res);
			if($jlhspk > 0){
				exit("Gagal, Tidak dapat tutup PO, sudah ada terbentuk transaksi di Pengajuan SPK silahkan hapus terlebih dahulu transaksi Pengajuan SPK");
			}
		}
		
		$_POST['pilDt']=0;
		
		if($_POST['pilDt']==0){
			##TUTUP PO 
			$sdata="select kodebarang,nopp,jumlahpesan from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
			$qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
			$qdata->setFetchMode(PDO::FETCH_ASSOC);
			while($rdata=$qdata->fetch()){
				$sup="update ".$dbname.".log_prapodt set status=1,ditolakoleh='".$_SESSION['standard']['userid']."',alasanstatus='".$_POST['ketClose']."'
					  where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."'";
				try{
					$owlPDO->exec($sup); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			
			$supdate="update ".$dbname.".log_poht set closed=1,keterangan='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."' where nopo='".$_POST['nopo']."'";
			try{
				$owlPDO->exec($supdate); 
				
				$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$_POST['nopo']."'";
				$owlPDO->exec($str);
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}

			if($stats=='' or $stats=='null' or $stats==0)
			{
				
				$sdelete="delete from ".$dbname.".approval where notransaksi='".$_POST['nopo']."'";
			//exit('Error : '.$sdelete);
				
				try{
					$owlPDO->exec($sdelete); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}else{
			$subTotal=0;
			$sdata="select kodebarang,nopp,jumlahpesan,hargasbldiskon from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'";
			$qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
			$qdata->setFetchMode(PDO::FETCH_ASSOC);
			while($rdata=$qdata->fetch()){
				$optPurchaser=makeOption($dbname,"log_poht","nopo,purchaser","nopo='".$_POST['nopo']."'");
				$purchaser=$optPurchaser[$_POST['nopo']];
				$str = "insert into ".$dbname.".log_listverifikasi (nopp,kodebarang,karyawanid,status,skip,pemenang,createdby,createdtime,updateby,updatetime) values ('".$rdata['nopp']."','".$rdata['kodebarang']."','".$purchaser."','0','0','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
				try{ $owlPDO->exec($str); } catch(PDOException $e){ echo " Gagal," . addslashes($e->getMessage());}			


				$hitung=0;
				$sjmlhgdng="select distinct sum(jumlah) as jmlh from ".$dbname.".log_transaksi_vw 
							where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."' and tipetransaksi=1";
				$qjmlhgdng=$owlPDO->query($sjmlhgdng) or die(print " Gagal: ".PDOException::getMessage());
				$qjmlhgdng->setFetchMode(PDO::FETCH_ASSOC);
				$angkPengurang=0;
			   
						$rjmlgdng=$qjmlhgdng->fetch();
						if(($rjmlgdng['jmlh']=='')||intval($rjmlgdng['jmlh'])==0){
								$angkPengurang=0;
						}else{
								$angkPengurang=$rjmlgdng['jmlh'];
						}
				
				//$hitung=$rdata['jumlahpesan']-$angkPengurang;
				
				if($angkPengurang!=''){
					$jmlclose=$rdata['jumlahpesan']-$angkPengurang;
					$hitung=$rdata['jumlahpesan']-$jmlclose;
					
				}else{
					$jmlclose=$rdata['jumlahpesan'];
					$hitung=0;  
				}
				/*if($hitung==0){
					$hitung=$rdata['jumlahpesan'];
					$jmlclose=0;
				}else{
					//exit("error:".$rdata['kodebarang']."__masuk sini".$rdata['jumlahpesan']."__".$angkPengurang);
					$jmlclose=$hitung;
					$hitung=0;  
				}*/
				$subTotal+=$hitung*$rdata['hargasbldiskon'];
				$supdate="update ".$dbname.".log_podt set jmlhstlhclose='".$jmlclose."',jumlahpesan='".$hitung."'
				where nopo='".$_POST['nopo']."' and kodebarang='".$rdata['kodebarang']."'";
				try{
					$owlPDO->exec($supdate);
					$sup="update ".$dbname.".log_prapodt set create_po=0,status=0 where nopp='".$rdata['nopp']."' and kodebarang='".$rdata['kodebarang']."' ";
					try{
						$owlPDO->exec($sup); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n".$sup; 
						die(); 
					}
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n".$sup; 
					die(); 
				}	
			}
			$sHt="select distinct diskonpersen,subtotal,nilaidiskon,ppn from ".$dbname.".log_poht where nopo='".$_POST['nopo']."'";
			$qHt=$owlPDO->query($sHt) or die(print " Gagal: ".PDOException::getMessage());
			$qHt->setFetchMode(PDO::FETCH_ASSOC);	
			$rHt=$qHt->fetch();
			
			@$persenPPn=($rHt['ppn']/(($rHt['subtotal'])-$rHt['nilaidiskon']))*100;
			
			@$nilDis=($subTotal*$rHt['diskonpersen'])/100;
			@$ppn=((($subTotal)-$nilDis)*intval($persenPPn))/100;
			$nilTotal=(($subTotal)-$nilDis)+$rHt['ongkirimppn']+$rHt['ongkosangkutan']+$ppn;
			//$isiketerangan='';
			
			$supdateht="update ".$dbname.".log_poht set "
					. "closed=1,keteranganclose='".$isiketerangan."',userclosed='".$_SESSION['standard']['userid']."',nilaidiskon='".$nilDis."' "
					. ",subtotal='".$subTotal."',ppn='".$ppn."',nilaipo='".$nilTotal."' "
					. "where nopo='".$_POST['nopo']."'";
			try{
				$owlPDO->exec($supdateht); 
				
				$str="update ".$dbname.".log_sorefrensi set jumlah='0' where noso='".$_POST['nopo']."'";
				$owlPDO->exec($str); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}

			if($stats=='' or $stats=='null' or $stats==0)
			{
				
				$sdelete="delete from ".$dbname.".approval where notransaksi='".$_POST['nopo']."'";
				try{
					$owlPDO->exec($sdelete); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
		}
		
		break;
	
	default:
	break;
	}
	    
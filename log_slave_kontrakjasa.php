<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$path = "fileupload/kontrakjasa/";
$pages          = checkPostGet('page','');

##PARAMETER
$notransaksi=checkPostGet('notransaksi','');
$notransaksiinduk=checkPostGet('notransaksiinduk','');
$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$tanggalkontrak=checkPostGet('tanggalkontrak','');
$deskripsi=checkPostGet('deskripsi','');
$supplier=checkPostGet('supplier','');
$tgldari=checkPostGet('tgldari','');
$tglsampai=checkPostGet('tglsampai','');
$spesifikasi=checkPostGet('spesifikasi','');
$uangmuka=str_replace(",","",checkPostGet('uangmuka',''));
$retensipersen=str_replace(",","",checkPostGet('retensipersen',''));
$retensinilai=str_replace(",","",checkPostGet('retensinilai',''));
$jenispajak=checkPostGet('jenispajak','');
$nilaipajak=str_replace(",","",checkPostGet('nilaipajak',''));
$clne=checkPostGet('clne','');

$tipedt=checkPostGet('tipedt','');
$kegiatandt=checkPostGet('kegiatandt','');
$satuandt=checkPostGet('satuandt','');
$rpdt=str_replace(",","",checkPostGet('rpdt',''));

$keteranganclose=checkPostGet('keteranganclose','');
$insertafter=checkPostGet('insertafter','');

$namafile=checkPostGet('namafile','');
$idfile=checkPostGet('idfile','');
$ketegoridt=checkPostGet('ketegoridt','');

##SEARCH
$scnotransaksi=checkPostGet('scnotransaksi','');
$snotransaksiinduk=checkPostGet('snotransaksiinduk','');

$optcat.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="select * from ".$dbname.".log_5kategoribarang order by id asc";
$res=fetchdata($str);
foreach($res as $val){
	$optcat.="<option value=".$val['id'].">".$val['jenis']."</option>";
	$namacat[$val['id']]=$val['jenis'];
}

switch($method){
	case'loaddata':
		$tab="";
		$limit=20;
        $page=0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=$page*$limit;
		$no=(($page*$limit));
		$colspan=14;
		
		$arrorgdet = getOrgDetail(2);
		$where = "";
		if($scnotransaksi!=''){
			$where.=" and notransaksi like '%".$scnotransaksi."%'";
		}
		
		## GET JUMLAH BARIS
		$str="select count(notransaksi) as countitem from ".$dbname.".log_kontrakjasa where 1=1 ".$where."";
		$res=fetchdata($str);
		$jlhbrs = $res[0]['countitem'];
		
		$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{	
			$str="select * from ".$dbname.".log_kontrakjasa where 1=1 ".$where." order by notransaksi desc, pt asc, unit asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				## CHECK BA
				$strx="select count(notransaksi) as jlhitem from ".$dbname.".log_bakontrakjasa where nokontrak='".$val['notransaksi']."'";
				$resx=fetchdata($strx);
				$jlhitem=$resx[0]['jlhitem'];
				if($jlhitem > 0){
					$ba="<label style='color:blue;cursor:pointer' onclick=\"previewba('".$val['notransaksi']."',event)\">v</label>";
				}else{
					$ba="";
				}
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:right;vertical-align:top'>".$no."</td>";
				$tab.="<td style='text-align:center;vertical-align:top'>".$val['notransaksi']."</td>";
				$tab.="<td style='text-align:center;min-width:70px;vertical-align:top'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=left valign=top>".$nmsupplier[$val['supplierid']]."</td>";
				$tab.="<td align=left valign=top>".$val['deskripsi']."</td>";
				$tab.="<td align=center valign=top>".$ba."</td>";
				$tab.="<td align=left valign=top>".getNamaKaryawan($val['updateby'])."</td>";
				
				if($val['close']=='0'){
					if($val['posting']=='0'){
						$tab.="<td align=center valign=top>Not Posted</td>";
						$tab.="<td align=center valign=top><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"ubah('".$val['notransaksi']."');\"></td>";
						$tab.="<td align=center valign=top><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"hapus('".$val['notransaksi']."');\"></td>";
						$tab.="<td align=center valign=top><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('".$val['notransaksi']."');\"></td>";
					}else{
						$tab.="<td align=center valign=top>Posted by ".getNamaKaryawan($val['postingby'])."</td>";
						$tab.="<td colspan=2></td>";
						$tab.="<td align=center valign=top><img src='images/skyblue/posted.png' class='zImgOffBtn' title='Posted'></td>";
					}
					$tab.="<td align=center valign=top style=width:20px><img src=images/upload-2-xxl.png class=zImgBtn class=zImgBtn height='30'  title='Upload' onclick=\"showupload('".$val['notransaksi']."');\" ></td>";
					$tab.="<td align=center valign=top>
						<img src=images/icons/book_previous.png class=zImgBtn class=zImgBtn height='30'  title='Close ???'
					onclick=\"closekotrakform('".$val['notransaksi']."',event);\" >
					</td>";
				}else{
					$tab.="<td align=center valign=top>Closed by ".getNamaKaryawan($val['closeby'])."</td>";
					$tab.="<td colspan=5></td>";
				}
				$tab.="<td align=center valign=top>
					<img src=images/zoom.png class=resicon title='Preview' onclick=\"preview('".$val['notransaksi']."',event);\">
				</td>";	
				$tab.="</tr>";
			}
		}
		
		## PAGING
		$tfoot.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		
		echo $tab."####".$tfoot;
	break;
	
	case'preview':
		##HEADER
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$notransaksiinduk=$res[0]['notransaksiinduk'];
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$tanggalkontrak=$res[0]['tanggal'];
		$deskripsi=$res[0]['deskripsi'];
		$supplier=$res[0]['supplierid'];
		$tgldari=$res[0]['tanggaldari'];
		$tglsampai=$res[0]['tanggalsampai'];
		$spesifikasi=$res[0]['spesifikasi'];
		$uangmuka=$res[0]['uangmuka'];
		$retensipersen=$res[0]['retensipersen'];
		$retensinilai=$res[0]['retensinilai'];
		$posting=$res[0]['posting'];
		$close=$res[0]['close'];
		$exppt=explode(',',$pt);
		
		$tab.="<table cellpadding=3>
			<tr>
				<td style='min-width:115px'>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>".$notransaksi."<input type='hidden' id='notransaksix' value='".$notransaksi."'></td>
			</tr>
			<tr>
				<td style='min-width:115px'>".$_SESSION['lang']['notransaksi']." Induk</td>
				<td>:</td>
				<td>".$notransaksiinduk."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['pt']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".getNamaOrg($pt)."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['unit']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".getNamaOrg($unit)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']." Kontrak</td>
				<td>:</td>
				<td>".tanggalnormal($tanggalkontrak)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['deskripsi']."</td>
				<td>:</td>
				<td>".$deskripsi."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['koderekanan']."</td>
				<td>:</td>
				<td>".getNamaSupplier($supplier)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>".tanggalnormal($tgldari)." s/d ".tanggalnormal($tglsampai)."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['spesifikasi']." ".$_SESSION['lang']['pekerjaan']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".nl2br($spesifikasi)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['uangmuka']."</td>
				<td>:</td>
				<td>".hidezerodecimal($uangmuka,2)."</td>
			</tr>
			<tr>
				<td>Retensi Nilai</td>
				<td>:</td>
				<td>".hidezerodecimal($retensinilai,2)."</td>
			</tr>
			<tr>
				<td>Retensi (%)</td>
				<td>:</td>
				<td>".hidezerodecimal($retensipersen,2)."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['pajak']."</td>
				<td style='vertical-align:top'>:</td>
				<td>";
				## LIST PAJAK
				$str="select * from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."' and kodeorg='".$unit."'";
				$res=fetchdata($str);
				$tab.="<table class='sortable' cellspacing=1 cellpadding=1 border=0>
					<thead>
					<tr class=rowheader style=text-align:center>
						<td>".$_SESSION['lang']['namaakun']."</td>
						<td>".$_SESSION['lang']['pajak']." (%)</td>
					</tr>
					</thead></tbody>";
				if(count($res) > 0){
					foreach($res as $val){
						$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val['noakun']."'");
						$tab.="<tr class='rowcontent'>";
						$tab.="<td>".$val['noakun']." - ".$nmakun[$val['noakun']]."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['nilai'],2)."</td>";
						$tab.="</tr>";
					}
				}else{
					$tab.="<tr class='rowcontent'><td colspan=2 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
				}
			$tab.="</tbody></table></td>
			</tr>
		</table>
		<hr>";
		
		## GET SATUAN
		$optsatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".setup_satuan order by satuan asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optsatuan.="<option value=".$val['satuan'].">".$val['satuan']."</option>";
		}
		
		## GET TIPE
		$opttipe.="";
		$arrtipe=tipektrkjasa();
		foreach($arrtipe as $key=>$val){
			$opttipe.="<option value=".$key.">".$val."</option>";
		}
		
		## DETAIL
		$tab.="<table class='sortable' border=0 cellpadding=3 cellspacing=1>
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>".$_SESSION['lang']['kategori']."</td>
				<td>".$_SESSION['lang']['kegiatan']."/".$_SESSION['lang']['material']."</td>
				<td style='min-width:100px'>".$_SESSION['lang']['satuan']."</td>
				<td>Rp / ".$_SESSION['lang']['satuan']."</td>
				<td></td>
			</tr></thead>";
			if($close=='0'){
				$tab.="<tbody>
					<tr class='rowcontent' style='text-align:center'>
					<td></td>
					<td>
						<select id=tipedtx>".$opttipe."</select>
					</td>
					<td>
						<select id=ketegoridtx>".$optcat."</select>
					</td>
					<td>
						<input id=kegiatandtx class=myinputtext placeholder='type here..' onkeypress=\"return tanpa_kutip(event);\"  style=\"width:300px;\">
					</td>
					<td>
						<select id=satuandtx>".$optsatuan."</select>
						<img id='imgsatuandtx' onclick=z.elSearch('satuandtx',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;padding-right:5px;'>
					</td>
					<td>
						<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='0' style=\"width:100px;\" value='' id=rpdtx onkeyup=\"z.numberFormat('rpdtx',2);\">
					</td>
					<td>
						<img src='images/plus.png' class='zImgBtn' title='Add' onclick=adddt('x') style='position:relative;top:3px;left:3px;padding-right:5px;'>
					</td>
				</tr></tbody>";
			}
			$tab.="<tbody id='listdtx'>".loaddt($notransaksi,'x')."</tbody>
		</table>
		<br><br><br>";
		
		echo $tab;
	break;
	
	case'ubah':
		$data=array();
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$data[0]['notransaksiinduk']=$res[0]['notransaksiinduk'];
		$data[0]['pt']=$res[0]['pt'];
		$data[0]['unit']=$res[0]['unit'];
		$data[0]['tanggalkontrak']=tanggalnormal($res[0]['tanggal']);
		$data[0]['deskripsi']=$res[0]['deskripsi'];
		$data[0]['supplier']=$res[0]['supplierid'];
		$data[0]['tgldari']=tanggalnormal($res[0]['tanggaldari']);
		$data[0]['tglsampai']=($res[0]['tanggalsampai']=='0000-00-00'?'':tanggalnormal($res[0]['tanggalsampai']));
		$data[0]['spesifikasi']=$res[0]['spesifikasi'];
		$data[0]['uangmuka']=hidezerodecimal($res[0]['uangmuka'],2);
		$data[0]['retensipersen']=hidezerodecimal($res[0]['retensipersen'],2);
		$data[0]['retensinilai']=hidezerodecimal($res[0]['retensinilai'],2);
		
		## GET PAJAK
		$_SESSION['pajak']=array();
		$str="select * from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$newdata = array(
				'jenispajak'=>$val['noakun'],
				'nilaipajak'=>$val['nilai']
			);
			array_push($_SESSION['pajak'],$newdata);
		}
		
		echo json_encode($data)."##".loaddt($notransaksi);
	break;
	
	case'hapus':
		try {
			$owlPDO->beginTransaction();
	
			## CEK MASIH ADA BAPP YANG BELUM SELESAI
			$str="select count(notransaksi) as jlhitem from ".$dbname.".log_bakontrakjasa where nokontrak='".$notransaksi."'";
			$res=fetchdata($str);
			$jlhitem=$res[0]['jlhitem'];
			
			if($jlhitem > 0){
				throw new PDOException("Gagal, Sudah ada bapp yang berjalan");
			}
			
			$str="delete from ".$dbname.".log_kontrakjasa where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".log_kontrakjasadt where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'posting':
		try {
			$owlPDO->beginTransaction();
			
			## CEK DETAIL
			$str="select count(notransaksi) as jlhitem from ".$dbname.".log_kontrakjasadt where notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			$jlhitem = $res[0]['jlhitem'];
			
			if($jlhitem <= 0){
				throw new PDOException("Gagal, Detail kegiatan/material belum ada.");
			}
			
			$str="update ".$dbname.".log_kontrakjasa set posting='1',postingby='".$_SESSION['standard']['userid']."',postingtime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'closekotrakform':
		try {
			$owlPDO->beginTransaction();
			
			$tab.="<table cellspacing=1 cellpadding='3' border=0>
				<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td><input class=myinputtext style=width:165px type=\"text\" id=\"notransaksiclose\" disabled value='".$notransaksi."' /></td>
				</tr>
				<tr>
					<td style='vertical-align:top'>".$_SESSION['lang']['keterangan']."</td>
					<td style='vertical-align:top'>:</td>
					<td>
						<textarea id='keteranganclose'></textarea>
					</td>
				</tr>
				<tr>
					<td><td><td>
						<button class=mybutton onclick=closekontrak() >".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=closeDialog5()>".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>";
			
			echo $tab;
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'closekontrak':
		try {
			$owlPDO->beginTransaction();
			
			if($keteranganclose==''){
				throw new PDOException("Gagal, Keterangan Tutup/Close harus diisi");
			}
			
			## CEK MASIH ADA BAPP YANG BELUM SELESAI
			$str="select count(notransaksi) as jlhitem from ".$dbname.".log_bakontrakjasa where nokontrak='".$notransaksi."' and status!='1'";
			$res=fetchdata($str);
			$jlhitem=$res[0]['jlhitem'];
			
			if($jlhitem > 0){
				throw new PDOException("Gagal, Ada beberapa transaksi BAPP belum selesai");
			}
			
			$str="update ".$dbname.".log_kontrakjasa set close='1',closeby='".$_SESSION['standard']['userid']."',alasanclose='".$keteranganclose."',closetime='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'getunit':
		## GET UNIT
		$optunit="";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)='4' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			if($unit==$val['kodeorganisasi']){
				$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
			}else{
				$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
			}
		}
		
		echo $optunit;
	break;
	
	case'popupnotranindk':
		$tab.="<table>
			<tr>
				<td style='min-width:100px;'>No. Transaksi Induk</td>
				<td>:<td>
				<td>
					<input type='text' id='snotransaksiinduk' class='myinputtext' style='width:150px;' value='".date('Y')."' />
				</td>
				<td>
					<button class=mybutton onclick=carikontrakinduk()>".$_SESSION['lang']['find']."</button>
				</td>
			</tr>
		</table>
		<hr>
		<div id='listnokontrakinduk'></div>";
		
		echo $tab;
	break;
	
	case'carikontrakinduk':
		$tab="<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr style='text-align:center;font-weight:bold'>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['notransaksi']." Induk</th>
			</tr>
			</thead>
			<tbody>";
		
		$str="select distinct(notransaksiinduk) as notransaksiinduk from ".$dbname.".log_kontrakjasa where close='0' and notransaksiinduk like '%".$snotransaksiinduk."%'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$no=0;
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td>".$no."</td>";
				$tab.="<td style='cursor:pointer' title='Pilih No. Kontrak Induk' onclick=\"setnokontrak('".$val['notransaksiinduk']."')\">".$val['notransaksiinduk']."</td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr><td colspan=2 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		
		$tab.="</tbody>
		</table>";
		
		echo $tab;
	break;
	
	case'setnokontrak':
		$data=array();
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksiinduk='".$notransaksiinduk."'";
		$res=fetchdata($str);
		$data[0]['notransaksi']=$res[0]['notransaksi'];
		$data[0]['tanggalkontrak']=tanggalnormal($res[0]['tanggal']);
		$data[0]['deskripsi']=$res[0]['deskripsi'];
		$data[0]['supplier']=$res[0]['supplierid'];
		$data[0]['tgldari']=tanggalnormal($res[0]['tanggaldari']);
		$data[0]['tglsampai']=($res[0]['tanggalsampai']=='0000-00-00'?'':tanggalnormal($res[0]['tanggalsampai']));
		$data[0]['spesifikasi']=$res[0]['spesifikasi'];
		$data[0]['uangmuka']=hidezerodecimal($res[0]['uangmuka'],2);
		$data[0]['retensipersen']=hidezerodecimal($res[0]['retensipersen'],2);
		$data[0]['retensinilai']=hidezerodecimal($res[0]['retensinilai'],2);
		
		## GET PAJAK
		$_SESSION['pajak']=array();
		$str="select * from ".$dbname.".log_spk_tax where notransaksi='".$data[0]['notransaksi']."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$newdata = array(
				'jenispajak'=>$val['noakun'],
				'nilaipajak'=>$val['nilai']
			);
			array_push($_SESSION['pajak'],$newdata);
		}
		
		echo json_encode($data)."##".loaddt($notransaksi);
	break;
	
	case'addpajak':
		if($jenispajak==''){
			exit("Gagal, Jenis pajak harus dipilih");
		}
		if($nilaipajak=='' || $nilaipajak<=0){
			exit("Gagal, Nilai (%) pajak harus diisi dan lebih besar dari 0");
		}
		
		$newdata = array(
			'jenispajak'=>$jenispajak,
			'nilaipajak'=>$nilaipajak
		);
		
		## CEK SUDAH PERNAH DIINPUT ATAU BELUM
		if($_SESSION['pajak'] != array()){
			foreach($_SESSION['pajak'] as $key=>$val){
				if($val['jenispajak'] == $jenispajak)
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['pajak'],$newdata);
		}else{
			array_push($_SESSION['pajak'],$newdata);
		}
	break;
	
	case'loadpajak':
		if(count($_SESSION['pajak']) > 0){
			foreach($_SESSION['pajak'] as $key=>$val){
				$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val['jenispajak']."'");
				$tab.="<tr class='rowcontent'>";
				$tab.="<td>".$val['jenispajak']." - ".$nmakun[$val['jenispajak']]."</td>";
				$tab.="<td style='text-align:right'>".hidezerodecimal($val['nilaipajak'],2)."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$val['jenispajak']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'>
				<td style='text-align:center' colspan=3>".$_SESSION['lang']['datanotfound']."</td>
			</tr>";
		}
		
        echo $tab;
	break;
	
	case'deletepajak':
		foreach($_SESSION['pajak'] as $key=>$val){
			if($val['jenispajak'] == $jenispajak){
				unset($_SESSION['pajak'][$key]);
			}
		}
	break;
	
	case'clearpajak':
		$_SESSION['pajak']=array();
	break;
	
	case'simpan':
		try {
			$owlPDO->beginTransaction();
			
			if($notransaksiinduk==''){
				throw new PDOException("No Transaksi Induk harus diisi");
			}
			
			if($deskripsi==''){
				throw new PDOException("Deskripsi harus diisi");
			}
			
			$exptglkontrak=explode('-',$tanggalkontrak);
			
			if($notransaksi==''){
				## GET NO TRANSAKSI
				$str="select left(notransaksi,3) as nourut from ".$dbname.".log_kontrakjasa where notransaksi like '%".$exptglkontrak[3]."' order by left(notransaksi,3) desc limit 1";
				$res=fetchdata($str);
				if(count($res)>0){
					$nourutkontrak=addZero(($res[0]['nourut']+1),3);
					$notransaksi=$nourutkontrak."-LGL/".str_replace(',','-',$unit)."/".romawi($exptglkontrak[1])."/".$exptglkontrak[2];
				}else{
					$nourutkontrak="001";
					$notransaksi=$nourutkontrak."-LGL/".str_replace(',','-',$unit)."/".romawi($exptglkontrak[1])."/".$exptglkontrak[2];	
				}
				
				$str="select notransaksi from ".$dbname.".log_kontrakjasa where notransaksiinduk='".$notransaksiinduk."'";
				$res=fetchdata($str);
				$clnenotransaksi=$res[0]['notransaksi'];
				
				## INSERT TO HEADER
				$wktskrg = date('Y-m-d H:i:s');
				$str="insert into ".$dbname.".log_kontrakjasa (notransaksi,notransaksiinduk,pt,unit,tanggal,supplierid,deskripsi,spesifikasi,tanggaldari,tanggalsampai,uangmuka,retensipersen,retensinilai,posting,createby,createtime,updateby,updatetime) values ('".$notransaksi."','".$notransaksiinduk."','".$pt."','".$unit."','".tanggalsystem($tanggalkontrak)."','".$supplier."','".$deskripsi."','".$spesifikasi."','".tanggalsystem($tgldari)."','".tanggalsystem($tglsampai)."','".$uangmuka."','".$retensipersen."','".$retensinilai."','0','".$_SESSION['standard']['userid']."','".$wktskrg."','".$_SESSION['standard']['userid']."','".$wktskrg."')";
				$owlPDO->exec($str);
				if($clne=='1'){
					$str="select * from ".$dbname.".log_kontrakjasadt where notransaksi='".$clnenotransaksi."'";
					$res=fetchdata($str);
					foreach($res as $val){
						$strx="insert into ".$dbname.".log_kontrakjasadt (notransaksi,noakun,kegiatan,satuan,rpsatuan,insertafter,updateby,updatetime) values ('".$notransaksi."','".$val['noakun']."','".$val['kegiatan']."','".$val['satuan']."','".$val['rpsatuan']."','0','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
						$owlPDO->exec($strx);
					}
				}
			}else{
				$str="update ".$dbname.".log_kontrakjasa set pt='".$pt."',unit='".$unit."',tanggal='".tanggalsystem($tanggalkontrak)."',supplierid='".$supplier."',deskripsi='".$deskripsi."',spesifikasi='".$spesifikasi."',tanggaldari='".tanggalsystem($tgldari)."',tanggalsampai='".tanggalsystem($tglsampai)."',uangmuka='".$uangmuka."',retensipersen='".$retensipersen."',retensinilai='".$retensinilai."',updateby='".$_SESSION['standard']['userid']."',updatetime='".$wktskrg."' where notransaksi='".$notransaksi."'";
				$owlPDO->exec($str);
			}
			
			$str="delete from ".$dbname.".log_spk_tax where notransaksi='".$notransaksi."' and kodeorg='".$unit."'";
			$owlPDO->exec($str);
			if(count($_SESSION['pajak']) > 0){
				foreach($_SESSION['pajak'] as $key=>$val){
					$str="insert into ".$dbname.".log_spk_tax (kodeorg,notransaksi,noakun,nilai) values ('".$unit."','".$notransaksi."','".$val['jenispajak']."','".$val['nilaipajak']."')";
					$owlPDO->exec($str);
				}
			}
			
			echo $notransaksi;
	
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'adddt':
		try {
			$owlPDO->beginTransaction();
	
			if($kegiatandt==''){
				throw new PDOException("Kegiatan/Material harus diisi");
			}
			
			if($satuandt==''){
				throw new PDOException("Satuan harus dipilih");
			}
			
			if($rpdt=='' || $rpdt<=0){
				throw new PDOException("Rp/Satuan harus diisi dan lebih besar dari 0");
			}
			
			## CEK KEGIATAN/MATERIAL SUDAH PERNAH DIINPUT ATAU BELUM
			$str="select count(kegiatan) as countitem from ".$dbname.".log_kontrakjasadt where lower(kegiatan)='".strtolower($kegiatandt)."' and notransaksi='".$notransaksi."'";
			$res=fetchdata($str);
			$countitem = $res[0]['countitem'];
			
			if($countitem > 0){
				throw new PDOException("Kegiatan/Material sudah pernah diinput sebelumnya.");
			}
			
			if($insertafter==''){
				$insertafter='0';
			}else{
				$insertafter='1';
			}
			
			$str="insert into ".$dbname.".log_kontrakjasadt (notransaksi,noakun,kegiatan,satuan,rpsatuan,insertafter,updateby,updatetime,idkategori) values ('".$notransaksi."','".$tipedt."','".$kegiatandt."','".$satuandt."','".$rpdt."','".$insertafter."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$ketegoridt."')";
			$owlPDO->exec($str);
	
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'loaddt':		
		echo loaddt($notransaksi,$insertafter);
	break;
	
	case'hapusdt':
		try {
			$owlPDO->beginTransaction();
			
			## CEK SUDAH ADA REALISASI ATAU TIDAK
			$str="select count(nokontrak) as jlhitem from ".$dbname.".log_bakontrakjasa where nokontrak='".$notransaksi."' and kegiatan='".$kegiatandt."'";
			$res=fetchdata($str);
			$jlhitem = $res[0]['jlhitem'];
			
			if($jlhitem > 0){
				throw new PDOException("Gagal, Kegiatan tidak dapat dihapus karena sudah ada realisasi");
			}
			
			$str="delete from ".$dbname.".log_kontrakjasadt where notransaksi='".$notransaksi."' and kegiatan='".$kegiatandt."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;

	// Fungsi Baru Rizky
	case 'showupload':
		$tab="";
		$tab.="<table border=0 >
					<tr>
						<td>" . $_SESSION['lang']['notransaksi'] . "</td>
						<td>:</td>
						<td id='notranupload'>". $notransaksi."</td>
					</tr>
					<tr>
						<td>Filename</td>
						<td></td>
						<td>
							<input type='file' name='upload' id='upload' >
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notransaksi."')\">Submit</button>
						</td>
					</tr>
				</table>";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=30px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;

	// Fungsi Baru Rizky
	case 'submitfile':
		$data= $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				#cek duplikasi nama file
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."'";
				$res=fetchData($str);
				if(count($res)>0){
					exit("Warning : Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.");
				}
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$notransaksi."','".$filename."','".$filetype."','others','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
			}
		}
	break;

	// Fungsi Baru Rizky
	case 'loadfiles':
		$str= "select * from ".$dbname.".kebun_aktifitas where notransaksi = '".$notransaksi."'";
		$res= fetchData($str);
		$jurnal = $res[0]['jurnal'];
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$notransaksi."' and status='1'";
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
				if($jurnal==0){					
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;

	// Fungsi Baru Rizky
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	// Fungsi Baru Rizky
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$idfile."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:950px;height:500px;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."'>";
		}
		
		echo $tab;
	break;	
	case'savecategorydt':
		try {
			$owlPDO->beginTransaction();
	
			if($kegiatandt==''){
				throw new PDOException("Kegiatan/Material harus diisi");
			}
			if($ketegoridt==''){
				throw new PDOException("Kategori harus diisi");
			}
			
			$str="update ".$dbname.".log_kontrakjasadt set idkategori='".$ketegoridt."' where notransaksi='".$notransaksi."' and lower(kegiatan)='".strtolower($kegiatandt)."'";
			$owlPDO->exec($str);
			
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
}

function loaddt($notransaksi,$x=''){
	global $dbname;
	global $owlPDO;
	global $namacat;
	global $optcat;
	
	$tab="";
	
	## CEK STATUS POSTING
	$str="select posting from ".$dbname.".log_kontrakjasa where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$posting=$res[0]['posting'];
		
	$total=0;
	$str="select * from ".$dbname.".log_kontrakjasadt where notransaksi='".$notransaksi."' and status='1' order by kegiatan asc";
	$res=fetchdata($str);
	if(count($res) > 0){
		$no=0;
		foreach($res as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".tipektrkjasa($val['noakun'])."</td>";
			if($val['idkategori']==''){
				$tab.="<td><select id=optcat".$no." onchange=\"savecategorydt('".$notransaksi."','".strtolower($val['kegiatan'])."',this.value)\">".$optcat."</select></td>";
			}else{				
				$tab.="<td>".$namacat[$val['idkategori']]."</td>";
			}
			$tab.="<td>".$val['kegiatan']."</td>";
			$tab.="<td>".$val['satuan']."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['rpsatuan'],2)."</td>";
			
			if($posting=='1'){
				if($val['insertafter']=='1'){
					$tab.="<td style='text-align:center'>
						<img title='Delete' class=resicon onclick=\"hapusdt('".$val['notransaksi']."','".$val['kegiatan']."','".$x."')\" src='images/delete_32.png'/
					</td>";
				}else{
					$tab.="<td></td>";
				}
			}else{
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"hapusdt('".$val['notransaksi']."','".$val['kegiatan']."','".$x."')\" src='images/delete_32.png'/
				</td>";
			}
			
			$tab.="</tr>";
			$total+=$val['rpsatuan'];
		}
		
		$tab.="<tr class='rowcontent' style='font-weight:bold'>";
		$tab.="<td colspan=5 style='text-align:right'>T O T A L</td>";
		$tab.="<td style='text-align:right'>".hidezerodecimal($total)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
	}else{
		$tab.="<tr class='rowcontent'>";
		$tab.="<td colspan=6 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";
		$tab.="</tr>";
	}
	
	return $tab;
}
?>
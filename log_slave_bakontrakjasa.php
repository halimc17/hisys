<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
// error_reporting(0);
$method=checkPostGet('method','');
##PARAMETER
$notransaksi=checkPostGet('notransaksi','');
$nokontrak=checkPostGet('nokontrak','');
$noba=checkPostGet('noba','');
$sckontrak=checkPostGet('sckontrak','');

$ptdt=checkPostGet('ptdt','');
$unitdt=checkPostGet('unitdt','');
$methoddt=checkPostGet('methoddt','');
$noakundt=checkPostGet('noakundt','');
$itemdt=checkPostGet('itemdt','');
$hargasatuandt=checkPostGet('hargasatuandt','');
$tanggaldt=checkPostGet('tanggaldt','');
$satuandt=checkPostGet('satuandt','');
$kuantitasdt=str_replace(",","",checkPostGet('kuantitasdt',''));
$keterangandt=checkPostGet('keterangandt','');

$subunitdt=checkPostGet('subunitdt','');
$blokdt=checkPostGet('blokdt','');
$kegiatandt=checkPostGet('kegiatandt','');

$jenisApp="BAJS";
$karyawanidapp=checkPostGet('karyawanidapp','');
$sumber=checkPostGet('sumber','');

##SEARCH
$scnotransaksi=checkPostGet('scnotransaksi','');
$urlefil=checkPostGet('urlefil','0');

switch($method){
	
	case'previewbapdf':
		$tab="<style>
			@page {
				margin-top: 10px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";
		
		$cellpadding=1;
		$cellspacing=0;
		$sizefont='10';
		$border='1';
		 
		$str="select * from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$nokontrak=$res[0]['nokontrak'];
		$createby=$res[0]['createby'];
		 
		
		##HEADER
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksi='".$nokontrak."'"; 
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
		$pembuat=$res[0]['postingby'];
		$exppt=explode(',',$pt);
		

		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']."</td>
				<td>:</td>
				<td>".$nokontrak."<input type='hidden' id='vnokontrak' value='".$nokontrak."'></td>
			</tr>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']." Induk</td>
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
			</table>";
		
		$tab.="<br><br>";
		
		$tab.="<table style='font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
		$tab.="<tr><td>".$_SESSION['lang']['noberitaacara']."</td><td>:</td><td>".$notransaksi."</td></tr>";
		$tab.="</table>";
		$tab.="<br>";
		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=".$border.">
			
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>Item</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>Rp / ".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>".$_SESSION['lang']['keterangan']."</td>
			</tr></thead>
			<tbody id='listdt'>";
			if($notransaksi==''){
				$tab.="<tr class=rowcontent><td style='text-align:center' colspan=1>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$tab.=loaddt($notransaksi,'x');
			}
		$tab.="<br><br>";
		
		
		$tab.="</tbody> 
		</table>";
		
		#Approval
		$stra="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level=1";
		$res=fetchdata($stra);
		$aprlvl1=$res[0]['level'];   
		$aprid1=$res[0]['karyawanid'];    
		$tgl1=$res[0]['tanggal'];    

		$stra="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level=2";
		$res=fetchdata($stra);
		$aprlvl2=$res[0]['level'];   
		$aprid2=$res[0]['karyawanid'];  
		$tgl2=$res[0]['tanggal'];     
 
		$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$tab.="<br><br>";
		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
		$tab.="<tr>";
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>"; 
				
				$tab.="<tr align=center><td>Dibuat</td></tr>";  
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td><b>".$nmkar[$createby]." </b></td>";   
				$tab.="</tr>";
				$tab.="<tr align=center><td> ".tanggalnormal($tanggalkontrak)."</td></tr>";  
			$tab.="</table> ";
		$tab.="</td> "; 
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";  
				$tab.="<tr align=center><td>Persetujuan 1</td></tr>";    
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td><b>".$nmkar[$aprid1]." </b></td>";   
				$tab.="</tr>";
				$tab.="<tr align=center><td> ".tanggalnormal(substr($tgl1,0,10))." </td></tr>";   
			$tab.="</table> ";
		$tab.="</td> "; 
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>"; 
				$tab.="<tr align=center><td>Persetujuan 2</td></tr>";   
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td> <b>".$nmkar[$aprid2]." </b></td>";  
				$tab.="<tr align=center><td> ".tanggalnormal(substr($tgl2,0,10))." </td></tr>";   
			$tab.="</table> ";
		$tab.="</td></tr>";
		$tab.="</table> ";
		
		$tab.="<br><br><br>";
		 
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("Print_BAST_".$nobast,array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}	
		
		
	break;	
	
	
	
	
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
		$colspan=9;
		
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
	
		
		
		
		$where = "";
		if($sckontrak!=''){
			$where.=" and notransaksi like '%".$sckontrak."%'";
		}
		// $where.=" and unit='".$_SESSION['empl']['lokasitugas']."'";
		$where.="and  unit in ('".implode("','",$dtunit)."') ";
		
		
		## GET JUMLAH BARIS
		$str="select count(notransaksi) as countitem from ".$dbname.".log_kontrakjasa where posting='1' ".$where."";
		// echo $str;
		$res=fetchdata($str);
		$jlhbrs = $res[0]['countitem'];
		$nmsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='".$colspan."' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$str="select * from ".$dbname.".log_kontrakjasa where posting=1 ".$where." order by notransaksi desc, pt asc, unit asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				## GET REALISASI
				$strx="select sum(jumlah) as jumlah from ".$dbname.".log_bakontrakjasa where nokontrak='".$val['notransaksi']."'";
				$resx=fetchdata($strx);
				$ttlrealisasi=$resx[0]['jumlah'];
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td style='text-align:right;vertical-align:top'>".$no."</td>";
				$tab.="<td style='text-align:center;vertical-align:top' nowrap>".$val['notransaksi']."</td>";
				$tab.="<td style='text-align:center;min-width:70px;vertical-align:top'>".tanggalnormal($val['tanggal'])."</td>";
				$tab.="<td align=left valign=top>".$nmsupplier[$val['supplierid']]."</td>";
				$tab.="<td align=left valign=top>".$val['deskripsi']."</td>";
				$tab.="<td align=right valign=top>".hidezerodecimal($ttlrealisasi,2)."</td>";
				
				if($val['close']=='1'){
					$tab.="<td align=center valign=top>Closed</td>";
					$tab.="<td></td>";
				}else{
					$tab.="<td align=center valign=top>Opened</td>";
					$tab.="<td align=center valign=top><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"ubah('".$val['notransaksi']."');\"></td>";
				}
				
				$tab.="<td align=center valign=top>
					<img src=images/zoom.png class=resicon title='Preview' onclick=\"preview('".$val['notransaksi']."',event);\">
				</td>";	
				$tab.="</tr>";
			}
		}
		
		## PAGING
		@$tfoot.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		
		echo $tab."####".$tfoot;
	break;
	
	case'ubah':
		$tab="";
		
		##HEADER
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksi='".$nokontrak."'";
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
		$exppt=explode(',',$pt);
		
		$tab.="<table cellpadding=3>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']."</td>
				<td>:</td>
				<td>".$nokontrak."<input type='hidden' id='vnokontrak' value='".$nokontrak."'></td>
			</tr>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']." Induk</td>
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
				$str="select * from ".$dbname.".log_spk_tax where notransaksi='".$nokontrak."' and kodeorg='".$unit."'";
				$res=fetchdata($str);
				if(count($res) > 0){
					$tab.="<table class='sortable' cellspacing=1 cellpadding=1 border=0>
						<thead>
						<tr class=rowheader style=text-align:center>
							<td>".$_SESSION['lang']['namaakun']."</td>
							<td>".$_SESSION['lang']['pajak']." (%)</td>
						</tr>
						</thead></tbody>";
					foreach($res as $val){
						$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val['noakun']."'");
						$tab.="<tr class='rowcontent'>";
						$tab.="<td>".$val['noakun']." - ".$nmakun[$val['noakun']]."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['nilai'],2)."</td>";
						$tab.="</tr>";
					}
				}
			$tab.="</tbody></table></td>
			</tr>
		</table>
		<hr>";
		
		## DETAIL
		$tab.="<button id=tomboldetail class=mybutton onclick=tambahrealisasi('".$nokontrak."','',event) style='height:30px'>Tambah Realisasi</button>
		<table class='sortable' border=0 cellpadding=3 cellspacing=1>
			<caption style='font-weight:bold'>REALISASI</caption>
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['updateby']."</td>
				<td>".$_SESSION['lang']['status']."</td>
				<td colspan=5>".$_SESSION['lang']['action']."</td>
			</tr></thead>
			<tbody id='listba'>".loadba($nokontrak)."</tbody>
		</table>";
		
		echo $tab;
	break;
	
	case'tambahrealisasi':
		$tab="";
		
		$str="select pt,unit from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."' limit 1";
		$res=fetchdata($str);
		@$ptba=$res[0]['pt'];
		@$unitba=$res[0]['unit'];
		
		## GET PT
		$str="select pt,unit from ".$dbname.".log_kontrakjasa where notransaksi='".$nokontrak."'";
		$res=fetchdata($str);
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		
		$optpt="";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optpt.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}
			
		## GET UNIT
		$optunit="";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and kodeorganisasi='".$unit."' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
		}
		
		## GET ITEM
		@$optitem.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".log_kontrakjasadt where notransaksi='".$nokontrak."' order by kegiatan asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optitem.="<option value='".$val['kegiatan']."'>".$val['kegiatan']."</option>";
		}
		
		$sttdsbl="";
		if($notransaksi!=''){
			$sttdsbl="disabled";
		}
		
		$optsubunit = getsubunit($unit,'','1');
		$optblok = getblokbaspk($unit,'','','1');
		$optkegiatan = getkegiatan($unit,'','','','1');
		
		$tab.="<table>
			<tr>
				<td style='min-width:130px;'>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td style='min-width:250px'>
					<input type='text' id='notransaksi' value='".$notransaksi."' class='myinputtext' disabled='disabled' style='width:145px;' /> <font color=red>*Otomatis
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td>
					<select id='ptdt' onchange=\"getunitdt()\" ".$sttdsbl.">".$optpt."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td>
					<select id='unitdt' ".$sttdsbl." onchange=\"getsubunit()\">".$optunit."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type='text' class='myinputtext' id='tanggaldt' value='".date('d-m-Y')."' readonly='readonly' onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false\"; style='width:80px;text-align:center' ".$sttdsbl." />
				</td>
			</tr>
			<tr>
				<td>Item</td>
				<td>:</td>
				<td>
					<select id='itemdt' onchange='gethargasatuan()'>".$optitem."</select>
					<img id='imgitemdt' onclick=z.elSearch('itemdt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kuantitas']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:80px;\" placeholder=0 id=kuantitasdt onkeyup=\"z.numberFormat('kuantitasdt',2);\"> <label id='satuandt'></label>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>:</td>
				<td>
					<select id='subunitdt' style='width:300px' onchange=\"getblok()\">".$optsubunit."</select>
				</td>
			</tr>
			<tr>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>:</td>
				<td>
					<select id='blokdt' style='width:300px' onchange=\"getkegiatan()\">".$optblok."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>:</td>
				<td>
					<select id='kegiatandt' style='width:300px'>".$optkegiatan."</select>
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['keterangan']."</td>
				<td style='vertical-align:top'>:</td>
				<td>
					<textarea rows='3' maxlength=1024 id=keterangandt type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:305px;\"></textarea>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<input type='hidden' id='hargasatuandt' value='0' class='myinputtext' disabled='disabled' />
					<input type='hidden' id='noakundt' value='' class='myinputtext' disabled='disabled' />
					<input type='hidden' id='nokontrak' value='".$nokontrak."' class='myinputtext' disabled='disabled' />
					<input type='hidden' id='methoddt' value='insert' class='myinputtext' disabled='disabled' />
					<button class=mybutton onclick=simpandt()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=bataldt()>".$_SESSION['lang']['cancel']."</button>
					<button class=mybutton onclick=transaksibaru()>".$_SESSION['lang']['new']." ".$_SESSION['lang']['transaksi']."</button>
				</td>
			</tr>
		</table>
		<hr>
		
		<table class='sortable' border=0 cellpadding=3 cellspacing=1>
			<caption style='font-weight:bold'>REALISASI</caption>
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>Item</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>Rp / ".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>".$_SESSION['lang']['keterangan']."</td>
				<td colspan=2>".$_SESSION['lang']['action']."</td>
			</tr></thead>
			<tbody id='listdt'>";
			if($notransaksi==''){
				$tab.="<tr class=rowcontent><td style='text-align:center' colspan=14>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$tab.=loaddt($notransaksi);
			}
			$tab.="</tbody>
		</table><br><br><br>";
		
		echo $tab;
	break;
	
	case'getunitdt':
		## GET UNIT
		$optunit="";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$ptdt."' and length(kodeorganisasi)='4' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			if($unitdt==$val['kodeorganisasi']){
				$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
			}else{
				if($val['kodeorganisasi']==$_SESSION['empl']['lokasitugas']){
					$optunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
				}else{
					$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";			
				}
			}
		}
		
		echo $optunit;
	break;
	
	case'getsubunit':
		echo getsubunit($unitdt,$subunitdt,'1');
	break;
	
	case'getblok':
		echo getblokbaspk($unitdt,$subunitdt,$blokdt,'1');
	break;
	
	case'getkegiatan':
		echo getkegiatan($unitdt,$subunitdt,$blokdt,$kegiatandt,'1');
	break;
	
	case'gethargasatuan':
		$str="select rpsatuan,satuan,noakun from ".$dbname.".log_kontrakjasadt where notransaksi='".$nokontrak."' and kegiatan='".$itemdt."'";
		$res=fetchdata($str);
		$hargasatuan=$res[0]['rpsatuan'];
		$satuan=$res[0]['satuan'];
		$noakun=$res[0]['noakun'];
		echo $hargasatuan."####".$satuan."####".$noakun;
	break;
	
	case'simpandt':
		try {
			$owlPDO->beginTransaction();
			
			$wktskrg=date("Y-m-d H:i:s");
			if($itemdt==''){
				throw new PDOException("Item harus dipilih");
			}
			if($kuantitasdt=='' || $kuantitasdt <= 0){
				throw new PDOException("Kuantitas harus diisi dan lebih besar dari 0(nol)");
			}
			
			if($subunitdt==''){
				throw new PDOException("Sub unit harus dipilih");
			}
			
			if($kegiatandt==''){
				throw new PDOException("Kegiatan harus dipilih");
			}
			
			$exptanggal=explode('-',$tanggaldt);			
			
			if($notransaksi==''){
				## CREATE NO TRANSAKSI				
				$str="select left(notransaksi,4) as nourut from ".$dbname.".log_bakontrakjasa where notransaksi like '%".$exptanggal[3]."' and nokontrak='".$nokontrak."' order by left(notransaksi,4) desc limit 1";
				$res=fetchdata($str);
				if(count($res)>0){
					$notransaksi=addZero(($res[0]['nourut']+1),4)."-BA/".$unitdt."/".romawi($exptanggal[1])."/".$exptanggal[2];
				}else{
					$notransaksi="0001-BA/".$unitdt."/".romawi($exptanggal[1])."/".$exptanggal[2];	
				}
			}
			 
			$jumlahdt = $hargasatuandt * $kuantitasdt;
			if($methoddt=='insert'){
				## INSERT BA
				$str="insert into ".$dbname.".log_bakontrakjasa (notransaksi,nokontrak,pt,unit,tanggal,noakun,kegiatan,satuan,kuantitas,rpsatuan,jumlah,subunit,subunitdt,kodekegiatan,keterangan,createby,createtime,updateby,updatetime) values ('".$notransaksi."','".$nokontrak."','".$ptdt."','".$unitdt."','".tanggalsystem($tanggaldt)."','".$noakundt."','".$itemdt."','".$satuandt."','".$kuantitasdt."','".$hargasatuandt."','".$jumlahdt."','".$subunitdt."','".$blokdt."','".$kegiatandt."','".$keterangandt."','".$_SESSION['standard']['userid']."','".$wktskrg."','".$_SESSION['standard']['userid']."','".$wktskrg."')";
				$owlPDO->exec($str);
			}else{
				$str="update ".$dbname.".log_bakontrakjasa set pt='".$ptdt."',unit='".$unitdt."',noakun='".$noakundt."',tanggal='".tanggalsystem($tanggaldt)."',kuantitas='".$kuantitasdt."',jumlah='".$jumlahdt."',subunit='".$subunitdt."',subunitdt='".$blokdt."',kodekegiatan='".$kegiatandt."',keterangan='".$keterangandt."',updateby='".$_SESSION['standard']['userid']."',updatetime='".$wktskrg."' where notransaksi='".$notransaksi."' and kegiatan='".$itemdt."'";
				$owlPDO->exec($str);
			}
			
			echo $notransaksi;
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'loadba':		
		echo loadba($nokontrak);
	break;
	
	case'loaddt':		
		echo loaddt($notransaksi);
	break;
	
	case'hapusdt':
		try {
			$owlPDO->beginTransaction();
			
			$str="delete from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."' and kegiatan='".$itemdt."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'preview':
		$tab="";
		
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
		$exppt=explode(',',$pt);
		
		$tab.="<table cellpadding=3>
			<tr>
				<td style='min-width:115px'>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>".$notransaksi."</td>
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
				foreach($res as $val){
					$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val['noakun']."'");
					$tab.="<tr class='rowcontent'>";
					$tab.="<td>".$val['noakun']." - ".$nmakun[$val['noakun']]."</td>";
					$tab.="<td style='text-align:right'>".hidezerodecimal($val['nilai'],2)."</td>";
					$tab.="</tr>";
				}
			$tab.="</tbody></table></td>
			</tr>
		</table>
		<hr>";
		
		if($sumber==''){
			## DETAIL
			$tab.="<table class='sortable' border=0 cellpadding=3 cellspacing=1>
				<caption style='font-weight:bold'>REALISASI</caption>
				<thead><tr class=rowheader style=text-align:center>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
					<td>".$_SESSION['lang']['updateby']."</td>
					<td>".$_SESSION['lang']['status']."</td>
					<td colspan=5>".$_SESSION['lang']['action']."</td>
				</tr></thead>
				<tbody id='listba'>".loadba($notransaksi,'x')."</tbody>
			</table>";
		}else{
			## DETAIL Approval
			$tab.="<label style='color:blue;font-weight:bold'>No. BA : ".$noba."</label>";
			$tab.="<table class='sortable' border=0 cellpadding=3 cellspacing=1>
				<caption style='font-weight:bold'>REALISASI</caption>
				<thead><tr class=rowheader style=text-align:center>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>".$_SESSION['lang']['tipe']."</td>
					<td>Item</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>Rp / ".$_SESSION['lang']['satuan']."</td>
					<td>".$_SESSION['lang']['jumlah']."</td>
					<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
					<td>".$_SESSION['lang']['subunit']."</td>
					<td>Blok / Mesin PKS / Kend / AB</td>
					<td>".$_SESSION['lang']['kegiatan']."</td>
					<td>".$_SESSION['lang']['keterangan']."</td>
				</tr></thead>
				<tbody id='listba'>".loaddt($noba,'x')."</tbody>
			</table>";
		}
		$tab.="<br><br><br><br><br>";
		
		echo $tab;
	break;	
	
	case'hapusba':
		try {
			$owlPDO->beginTransaction();
			
			$str="delete from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'ajukanba':
		try {
			$owlPDO->beginTransaction();
			
			$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) value ('".$notransaksi."','".$jenisApp."','1','".$karyawanidapp."','0')";
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".log_bakontrakjasa set status='9',tanggalpengajuan='".date('Y-m-d H:i:s')."' where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	case'postingba':
		try {
			$owlPDO->beginTransaction();
			
			$str="SELECT a.unit,a.subunitdt,a.subunit,b.kode,b.statuspersetujuan,b.dgnapproval, a.updateby FROM  ".$dbname.".log_bakontrakjasa a 
			LEFT JOIN  ".$dbname.".project b ON a.subunitdt=b.kode WHERE  notransaksi='".$notransaksi."' ";
			$res=fetchdata($str);
			$unit=$res[0]['unit'];
			@$kodeproject=$res[0]['subunitdt']; 
			@$subunit=$res[0]['subunit']; 
			@$dgnaprv=$res[0]['dgnapproval']; 
			$dept = getKary($res[0]['updateby'],'bagian'); 

			if ($subunit=='PROJECT' && $dgnaprv=='1') {
				$stra="select level from ".$dbname.".project_approval where kode='".$kodeproject."'"; 
				$resa=fetchdata($stra);
				@$countApp=$resa[0]['level'];
			} else {
				$countApp = getCountApproval($jenisApp,$unit,$dept); #==> SEBELUM
			}
			
			// echo $countApp; 

			if($countApp > 0){
				##GET APPROVAL LEVEL 1
				// $arrapp=listApprove('1',$jenisApp,$unit);  
				if ($subunit=='PROJECT' && $dgnaprv=='1') { 
					$strk="select level,karyawanid from ".$dbname.".project_approval where kode='".$kodeproject."' and level='1'"; 
					$resk=fetchdata($strk);
					@$karyawanidba=$resk[0]['karyawanid']; 
					$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".@$karyawanidba."'");
					foreach($resk as $val){
						@$optapp.="<option value=".$karyawanidba.">".$nmkar[$karyawanidba]."</option>";
					}
				} else {
					$arrapp=listApprove('1',$jenisApp,$unit,$dept);
					
					foreach($arrapp as $val){
						@$optapp.="<option value=".$val['karyawanid'].">".$val['nama']."</option>";
					}
				} 

				$tab.="<table cellspacing=1 cellpadding='3' border=0>
					<tr>
						<td>".$_SESSION['lang']['notransaksi']."</td>
						<td>:</td>
						<td><input class=myinputtext style=width:165px type=\"text\" id=\"notransaksiappp\" disabled value='".$notransaksi."' /></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['kepada']."</td>
						<td>:</td>
						<td><select style=width:170px  id=\"karyawanidapp\">". $optapp."</select></td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['departemen']."</td>
						<td>:</td>
						<td>".getNamaDept($dept)."</td>
					</tr>
					<tr>
						<td><td><td>
							<input type=\"hidden\" id=\"cls_stat\" name=\"cls_stat\" value=0 />
							<button class=mybutton onclick=ajukanba() >".$_SESSION['lang']['diajukan']."</button>
							<button class=mybutton onclick=closeDialog5()>".$_SESSION['lang']['cancel']."</button>
						</td>
					</tr>
				</table>";
			}else{
				$tab.="Setup Approval belum ada, silahkan hubungi administrator.";
			}
			
			echo $tab;
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;
	
	// case'postingba':
	// 	try {
	// 		$owlPDO->beginTransaction();
			
	// 		$str="select unit from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."'";
	// 		$res=fetchdata($str);
	// 		$unit=$res[0]['unit'];
			
	// 		$countApp = getCountApproval($jenisApp,$unit);
			
	// 		if($countApp > 0){
	// 			##GET APPROVAL LEVEL 1
	// 			$arrapp=listApprove('1',$jenisApp,$unit);
	// 			foreach($arrapp as $val){
	// 				$optapp.="<option value=".$val['karyawanid'].">".$val['nama']."</option>";
	// 			}
	// 			$tab.="<table cellspacing=1 cellpadding='3' border=0>
	// 				<tr>
	// 					<td>".$_SESSION['lang']['notransaksi']."</td>
	// 					<td>:</td>
	// 					<td><input class=myinputtext style=width:165px type=\"text\" id=\"notransaksiappp\" disabled value='".$notransaksi."' /></td>
	// 				</tr>
	// 				<tr>
	// 					<td>".$_SESSION['lang']['kepada']."</td>
	// 					<td>:</td>
	// 					<td><select style=width:170px  id=\"karyawanidapp\">". $optapp."</select></td>
	// 				</tr>
	// 				<input type=\"hidden\" id=\"cls_stat\" name=\"cls_stat\" value=0 />
	// 				<tr>
	// 					<td><td><td>
	// 						<button class=mybutton onclick=ajukanba() >".$_SESSION['lang']['diajukan']."</button>
	// 						<button class=mybutton onclick=closeDialog5()>".$_SESSION['lang']['cancel']."</button>
	// 					</td>
	// 				</tr>
	// 			</table>";
	// 		}else{
	// 			$tab.="Setup Approval belum ada, silahkan hubungi administrator.";
	// 		}
			
	// 		echo $tab;
			
	// 		$owlPDO->commit();
	// 	}catch(PDOException $e){
	// 		$owlPDO->rollback();
	// 		echo "Warning \n" . addslashes($e->getMessage());
	// 	}
	// break;

	case'previewba':
		$tab="";
		
		$tab.="<table class='sortable' border=0 cellpadding=3 cellspacing=1>
			<caption style='font-weight:bold'>REALISASI</caption>
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>Item</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>Rp / ".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>".$_SESSION['lang']['keterangan']."</td>
			</tr></thead>
			<tbody id='listdt'>";
			if($notransaksi==''){
				$tab.="<tr class=rowcontent><td style='text-align:center' colspan=1>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$tab.=loaddt($notransaksi,'x');
			}
			$tab.="</tbody>
		</table><br><br><br>";
		
		echo $tab;
	break;
	
	case'gethistori':
		$jnsappx=checkPostGet('jenisappx','');
		echo gethistoryapp($notransaksi,$jnsappx);
	break;
}

function loadba($nokontrak,$show=''){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$total=0;
	$str="select notransaksi,pt,unit,sum(jumlah) as jumlah, tanggal, status, createby from ".$dbname.".log_bakontrakjasa where nokontrak='".$nokontrak."' group by notransaksi order by notransaksi asc";
	$res=fetchdata($str);
	if(count($res) > 0){
		$no=0;
		foreach($res as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".$val['notransaksi']."</td>";
			$tab.="<td>".$val['pt']."</td>";
			$tab.="<td>".$val['unit']."</td>";
			$tab.="<td style='min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['jumlah'],2)."</td>";
			$tab.="<td style='text-align:left'>".getNamaKaryawan($val['createby'])."</td>";
			$tab.="<td style='text-align:center'>
				<label style='color:blue;cursor:pointer' onclick=\"gethistoriapproval('".$val['notransaksi']."',event)\">".statusapproval($val['status'])."</label>
			</td>";
			
			if($val['status']=='0'){
				if($val['createby']==$_SESSION['standard']['userid']){
					if($show==''){
						$tab.="<td style='text-align:center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"tambahrealisasi('".$nokontrak."','".$val['notransaksi']."',event);\">
						</td>";
						$tab.="<td style='text-align:center'>
							<img title='Delete' class=resicon onclick=\"hapusba('".$val['notransaksi']."')\" src='images/delete_32.png'/>
						</td>";
					}
					$tab.="<td style='text-align=center'>
						<img src=images/icons/04/16/04.png class=resicon title='Ajukan' onclick=\"postingba('".$val['notransaksi']."',event);\">
					</td>";
				}else{
					if($show==''){
						$tab.="<td colspan=2></td>";						
					}
					$tab.="<td style='text-align=center'>
						<img src=images/icons/04/16/04.png class=resicon title='Belum Ajukan'>
					</td>";
				}
			}else if($val['status']=='1'){
				if($show==''){
					$tab.="<td colspan=2></td>";
				}
				$tab.="<td style='text-align=center'>
					<img src=images/icons/04/16/02.png class=resicon title='Disetujui'>
				</td>";
			}else if($val['status']=='2'){
				if($show==''){
					$tab.="<td colspan=2></td>";
				}
				$tab.="<td style='text-align=center'>
					<img src=images/icons/04/16/01.png class=resicon title='Ditolak'>
				</td>";
			}else if($val['status']=='3'){
				if($val['createby']==$_SESSION['standard']['userid']){
					if($show==''){
						$tab.="<td style='text-align:center'>
							<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"tambahrealisasi('".$nokontrak."','".$val['notransaksi']."',event);\">
						</td>";
						$tab.="<td style='text-align:center'>
							<img title='Delete' class=resicon onclick=\"hapusba('".$val['notransaksi']."')\" src='images/delete_32.png'/>
						</td>";
					}
					$tab.="<td style='text-align=center'>
						<img src=images/icons/04/16/06.png class=resicon title='Koreksi' onclick=\"postingba('".$val['notransaksi']."',event);\">
					</td>";
				}else{
					if($show==''){
						$tab.="<td colspan=2></td>";						
					}
					$tab.="<td style='text-align=center'>
						<img src=images/icons/04/16/06.png class=resicon title='Koreksi'>
					</td>";
				}
			}else{
				if($show==''){
					$tab.="<td colspan=2></td>";
				}
				$tab.="<td style='text-align=center'>
					<img src=images/icons/04/16/05.png class=resicon title='Proses Persetujuan'>
				</td>";
			}
			
			
			$tab.="<td style='text-align:center'>
				<img src=images/zoom.png class=resicon title='Preview' onclick=\"previewba('".$val['notransaksi']."',event);\">
				
			</td>";
			$tab.="<td style='text-align:center'>
				<img src=images/pdf.jpg class=resicon title='Preview' onclick=\"previewbapdf('".$val['notransaksi']."',event);\">
			</td>";
			
			$tab.="</tr>";
			$total+=$val['jumlah'];
		}
		
		$tab.="<tr class='rowcontent' style='font-weight:bold'>";
		$tab.="<td colspan=5 style='text-align:right'>T O T A L</td>";
		$tab.="<td style='text-align:right'>".hidezerodecimal($total)."</td>";
		if($show==''){
			$tab.="<td colspan=7></td>";			
		}else{
			$tab.="<td colspan=5></td>";			
		}
		$tab.="</tr>";
	}else{
		$tab.="<tr class='rowcontent'>";
		if($show==''){
			$tab.="<td colspan=12 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";			
		}else{
			$tab.="<td colspan=10 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";			
		}
		$tab.="</tr>";
	}
	
	return $tab;
}

function loaddt($notransaksi,$show=''){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$total=0;
	$str="select * from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."' order by notransaksi asc";
	$res=fetchdata($str);
	if(count($res) > 0){
		$no=0;
		foreach($res as $val){
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td style='min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
			$tab.="<td>".tipektrkjasa($val['noakun'])."</td>";
			$tab.="<td>".$val['kegiatan']."</td>";
			$tab.="<td style='text-align:center'>".$val['satuan']."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['rpsatuan'],2)."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['kuantitas'],2)."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['jumlah'],2)."</td>";
			$tab.="<td style='text-align:left'>".getNamaOrg($val['subunit'])."</td>";
			$tab.="<td style='text-align:left'>".getblokbaspk($val['unit'],$val['subunit'],$val['subunitdt'],'2')."</td>";
			$tab.="<td style='text-align:left'>".getkegiatan($val['unit'],$val['subunit'],$val['subunitdt'],$val['kodekegiatan'],'2')."</td>";
			$tab.="<td style='text-align:left'>".$val['keterangan']."</td>";
			if($show==''){
				$tab.="<td style='text-align:center'>
					<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"ubahdt('".$val['notransaksi']."','".tanggalnormal($val['tanggal'])."','".$val['kegiatan']."','".$val['satuan']."','".$val['kuantitas']."','".$val['keterangan']."','".$val['rpsatuan']."','".$val['noakun']."','".$val['subunit']."','".$val['subunitdt']."','".$val['kodekegiatan']."');\">
				</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"hapusdt('".$val['notransaksi']."','".$val['kegiatan']."')\" src='images/delete_32.png'/>
				</td>";
			}
			$tab.="</tr>";
			@$ttlkuantitas+=$val['kuantitas'];
			$total+=$val['jumlah'];
		}
		
		$tab.="<tr class='rowcontent' style='font-weight:bold'>";
		$tab.="<td colspan=6 style='text-align:right'>T O T A L</td>";
		$tab.="<td style='text-align:right'>".hidezerodecimal($ttlkuantitas,2)."</td>";
		$tab.="<td style='text-align:right'>".hidezerodecimal($total,2)."</td>";
		if($show==''){
			$tab.="<td colspan=6></td>";			
		}else{
			$tab.="<td colspan=4></td>";			
		}
		$tab.="</tr>";
	}else{
		$tab.="<tr class='rowcontent'>";
		if($show==''){
			$tab.="<td colspan=14 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";			
		}else{
			$tab.="<td colspan=12 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";			
		}
		$tab.="</tr>";
	}
	
	return $tab;
}

function getsubunit($unit,$subunit,$tipe){
	global $dbname;
	global $owlPDO;
	
	$opttipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$unit."'");	
	if($opttipe[$unit]=='HOLDING'){
		$n="KANTOR/OFFICE (HO)";
	}else{
		$n="KANTOR/OFFICE";
	}
	
	$optsubunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optsubunit.="<option value='".$unit."'>".$n."</option>";
	
	##GET SUBUNIT
	$strx="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$unit."' and tipe not like '%gudang%'";
	$resx=fetchdata($strx);
	foreach($resx as $valx){
		$optsubunit.="<option value='".$valx['kodeorganisasi']."'>".$valx['kodeorganisasi']." - ".$valx['namaorganisasi']."</option>";					
	}
	$optsubunit.="<option value='PROJECT'>PROJECT</option>";
	
	return $optsubunit;
}

function getblokbaspk($unit,$subunit,$blok,$tipe){
	global $dbname;
	global $owlPDO;
	
	$tipeorg="";
	$temptipe="";
	
	if($unit==$subunit){
		$tipeorg='1';
	}else{
		## GET TIPE ORGANISASI
		$str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunit."' limit 1";
		$res=fetchdata($str);
		@$temptipe = $res[0]['tipe'];
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
				if($blok==$val['kodevhc']){
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
				if($blok==$val['kodeperumahan']){
					$hasil=$val['kompleks']." - ".$val['blok']." - ".$val['norumah'];
					$optsubunitdt.="<option value='".$val['kodeperumahan']."' selected>".$val['kompleks']." - ".$val['blok']." - ".$val['norumah']."</option>";
				}else{
					$optsubunitdt.="<option value='".$val['kodeperumahan']."'>".$val['kompleks']." - ".$val['blok']." - ".$val['norumah']."</option>";
				}
			}
		}
		
		if($subunit=='PROJECT'){
			$str="select kode,nama from ".$dbname.".project where kodeorg='".$unit."' and statuspersetujuan !='9' order by nama asc";
			$res=fetchdata($str);
			foreach($res as $val){
				if(substr($val['kode'],0,2)=='AK' or substr($val['kode'],0,2)=='PB'){
					if($blok==$val['kode']){
						$hasil=$val['kode']." - ".$val['nama'];
						$optsubunitdt.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
					}else{ 
						$optsubunitdt.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
					}
				}else{
					if($val['posting']=='0'){
						if($blok==$val['kode']){
							$hasil=$val['kode']." - ".$val['nama'];
							$optsubunitdt.="<option value='".$val['kode']."' selected>".$val['kode']." - ".$val['nama']."</option>";
						}else{
							$optsubunitdt.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['nama']."</option>";
						}
					}
				}
			} 
		}
		
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$subunit."'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($blok==$val['kodeorganisasi']){
				$hasil=$val['kodeorganisasi']." - ".$val['namaorganisasi'];
				$optsubunitdt.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";				
			}
			$optsubunitdt.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
	}
	if($tipe=='1'){
		return $optsubunitdt;		
	}else if($tipe='2'){
		return $hasil;
	}
}

function getkegiatan($unit,$subunit,$blok,$kegiatan,$stipe){
	global $dbname;
	global $owlPDO;
	
	$hasil="";
	$optkegiatan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	
	if($subunit!=''){
		if($unit==$subunit){
			$opttipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$unit."'");	
			if($opttipe[$unit]=='HOLDING'){
				$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where (kelompok='KNT1' or substr(kodekegiatan,1,3) in ('821')) and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok,namakegiatan";
			}else{
				$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok,namakegiatan";
			}
			
		}else{
			$strx="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$subunit."'";
			$resx=fetchdata($strx);
			$temptipe="";
			if(count($resx)>0){
				$temptipe=$resx[0]['tipe'];
			}
			
			if($temptipe=='WORKSHOP'){
				$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='WSH' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok desc,namakegiatan asc";
			}else{
				if($temptipe!=''){
					if($blok!=''){
						######
						if($temptipe=='STENGINE' or $temptipe=='STATION' or $temptipe=='MAINTENANCE'){
							$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='MIL' and status = '1' order by kelompok,namakegiatan";
						}
						
						######
						if($temptipe=='AFDELING'){
							$strx="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
							$resx=fetchdata($strx);
							$statusblok=$resx[0]['statusblok'];
							if($statusblok=='TM'){
								$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by kelompok,namakegiatan";
							}else{
								$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='".$statusblok."' and status = '1' order by kelompok,namakegiatan";
							}
						}
						
						######
						if($temptipe=='SIPIL'){
							$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where (kelompok='SPL' or kelompok='KNT') and status = '1' order by kelompok,namakegiatan"; 
						}
						
						######
						if($temptipe=='TRAKSI'){
							$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='TRK' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok desc,namakegiatan";	   
						}
						
						######
						if($temptipe=='BIBITAN'){
							$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where  kelompok in ('BBT','MN','PN','KNT') and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok,namakegiatan";
						}
					}
				}else{
					if($blok!=''){
						
						$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where kelompok='KNT' and substr(kodekegiatan,1,3) not in ('127','126') and status = '1' order by kelompok,namakegiatan";
						if($subunit=='PROJECT'){
							if(substr($blok,0,2)=='AK'){
								$tipeasset=substr($blok,3,2);
								$str="select kodekegiatan,kelompok,namakegiatan,noakun from ".$dbname.".setup_kegiatan where noakun in (select akunak from ".$dbname.".sdm_5tipeasset where kodetipe='".$tipeasset."') order by kelompok,namakegiatan";
							}
						}
					}
				}
			}
		}
		
		if(isset($str)){
			$res=fetchdata($str);
			foreach($res as $val){
				if($kegiatan==$val['kodekegiatan']){
					$hasil="[".$val['kelompok']."] - ".$val['namakegiatan'];
					$optkegiatan.="<option value='".$val['kodekegiatan']."' selected>".$val['noakun']." - [".$val['kelompok']."] - ".$val['namakegiatan']."</option>";				
				}else{
					$optkegiatan.="<option value='".$val['kodekegiatan']."'>".$val['noakun']." - [".$val['kelompok']."] - ".$val['namakegiatan']."</option>";
				}
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
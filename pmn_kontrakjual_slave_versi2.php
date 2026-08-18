<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
use Dompdf\Dompdf;
require_once('dompdf/autoload.inc.php');

$param = (($_POST==array())?$_GET:$_POST);
$lokasiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$txtSearch=checkPostGet('txtSearch','');
$kurs=checkPostGet('kurs','');
$ptSch=checkPostGet('ptSch','');
$posisictr=checkPostGet('posisictr','');
$daerahctr=checkPostGet('daerahctr','');
// $termbyr=checkPostGet('termbyr','');
$urlefil=checkPostGet('urlefil','0');

// $optOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
// $optBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
// $optCust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$arrHo=makeOption($dbname,'organisasi','induk,kodeorganisasi',"tipe='HOLDING'");
$nopp = checkPostGet('rnopp','');
$namafile = checkPostGet('namafile','');
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$namabarangsales=makeOption($dbname,'log_5masterbarang','kodebarang,namasales',"kelompokbarang='400'");
$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$method = checkPostGet('method','');
$table='pmn_kontrakjualv2';
$path   = "fileupload/kontrakjual/";
$optjns=array("PM"=>"Pengiriman","PK"=>"Pemenuhan Kontrak","UM"=>"Uang Muka","BA"=>"Berita Acara Serah Terima"); 
// print_r($param);
// exit("Error:$method");

$arrBulan=array("01"=>"I","02"=>"II","03"=>"III","04"=>"IV","05"=>"V","06"=>"VI","07"=>"VII","08"=>"VIII","09"=>"IX","10"=>"X","11"=>"XI","12"=>"XII");



$str="select * from ".$dbname.".pmn_5daerahkontrak";
$res=fetchdata($str);
foreach($res as $bar){
	$namakotakontrak[$bar['id']]=$bar['lokasi'];
}

$str="select * from ".$dbname.".setup_filesize where transaksi='pmn_kontrakjualv2'";
$res=fetchdata($str);
foreach($res as $bar){
	$filesize=$bar['filesize'];
}

        // switch($param['method']){
switch($method){
	
	
	case'pdfpanjang':
		$tab="<style>
			@page {
				margin-top: 100px;
				margin-left: 100px;
				margin-right: 100px;
				margin-bottom: 100px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -40px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
		</style>";
		
	
		// footer {
				// position: fixed; 
				// bottom: 20px; 
				// left: 0px; 
				// right: 0px;
				// height: 50px; 
			// }
			
		#= ambil data dari kontrakjual
		$str="select * from ".$dbname.".pmn_kontrakjualv2 where nokontrak='".$param['nokontrak']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodept=$bar['kodept'];
			$tanggalkontrak=$bar['tanggalkontrak'];
			$hargasatuan=$bar['hargasatuan'];
			$persenppn=$bar['persenppn']; // sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
			$ffakontrak=$bar['ffa'];
			$dobikontrak=$bar['dobi'];	
			$mdanikontrak=$bar['mdani'];
			$moistkontrak=$bar['moist'];		
			$dirtkontrak=$bar['dirt'];
			$impuritieskontrak=$bar['grading'];
			$penandatangan=$bar['penandatangan'];
			$satuanbarang=$bar['satuan'];
			$matauang=$matauang[$bar['matauang']];
			$ppnpersen=$bar['ppnpersen'];
			$kodebarang=$bar['kodebarang'];
			$daerahkontrak=$bar['daerahkontrak'];
			$koderekanan=$bar['koderekanan'];
			$defaultpersenppn=$bar['defaultpersenppn']; // secara default 10
		}
		
		#= jabatan ttd
		$str="select * from ".$dbname.".pmn_5ttd where nama='".$penandatangan."'";
		// echo $str;exit();
		$res=fetchdata($str);
		foreach($res as $bar){
			$jabatanpenandatangan=$bar['jabatan'];
		}
		
		$str="select * from ".$dbname.".pmn_4customer where kodecustomer='".$koderekanan."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namacustomer=$bar['namacustomer'];
			$penandatangancustomer=$bar['penandatangan'];
			$jabatancustomer=$bar['jabatan'];
		}
		
		
		$datattdcustomer=explode('/',$penandatangancustomer);
		if($datattdcustomer[1]!=''){
			$penandatangancustomer=ucwords(strtolower($datattdcustomer[0])).'/'.ucwords(strtolower($datattdcustomer[1]));
		}else{
			$penandatangancustomer=ucwords(strtolower($datattdcustomer[0]));
		}	
		
		
		$arrkodept = setheadreport('',$kodept);
		$cellpadding=1;
		$cellspacing=1;
		$sizefont='14';
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
			$tab.="<tr>";
				$tab.="<td style='width:35px;' align=center><img src=".$arrkodept['logo']." style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'></td>"; 
				$tab.="<td style='width:400px;text-align:center;font-size:".($sizefont+10)."px'>".$arrkodept['nama']."</td>"; 
				$tab.="<td style='width:35px;'>&nbsp;</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>Perjanjian Jual Beli</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>Produk ".$namabarangsales[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;font-size:".$sizefont."px'  colspan=3>No. ".$param['nokontrak']."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Perjanjian jual beli produk ".strtolower($namabarangsales[$kodebarang])." ini dibuat di ".ucfirst(strtolower($namakotakontrak[$daerahkontrak])).", pada ".tglnmblnhrpanjang($tanggalkontrak,'i','l')." (".tanggalnormal($tanggalkontrak).")  antara:</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".$arrkodept['nama'].", berkedudukan di ".ucfirst(strtolower($namakotakontrak[$daerahkontrak])).", yang diwakili oleh ".ucwords(strtolower(getKary($penandatangan)))." selaku ".ucwords(strtolower($jabatanpenandatangan))." ; selanjutnya disebut Pihak Pertama / Penjual.</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
			
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".$namacustomer.", berkedudukan di ".ucfirst(strtolower($namakotakontrak[$daerahkontrak])).", yang diwakili oleh ".$penandatangancustomer." selaku ".ucwords(strtolower($jabatancustomer))." ; selanjutnya disebut Pihak Kedua / Pembeli.</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Produk ".$namabarangsales[$kodebarang]." hasil produksi perkebunan tanaman kelapa sawit selanjutnya disebut “Komoditi”.</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Selanjutnya para pihak menyatakan hal – hal sebagai berikut:<br>
Bahwa Pihak Pertama selaku produsen Komoditi akan menjual sebagian Komoditi kepada Pihak Kedua, dan Pihak Kedua bersedia membeli Komoditi dari Pihak Pertama.</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Oleh karena itu, para pihak akan melaksanakan perjanjian jual beli Komoditi ini dengan persyaratan dan ketentuan sebagai berikut:</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$str="select * from ".$dbname.".pmn_kontrakjualdt_kontrakpanjang where nokontrak='".$param['nokontrak']."' and kodebarang='".$kodebarang."' order by pasal asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrpasal[$bar['pasal']]=$bar['pasal'];
			$dtketerangan[$bar['pasal']]=$bar['keterangan'];
		} 
		
		
		$tab.="<footer>";
		$cellpadding=1;	
		$tab.="<table style='font-size:".($sizefont-4)."px;' border=0 cellpadding=".$cellpadding." width=100%>";	
			$tab.="<tr>";
				$tab.="<td align=center><b>Alamat Korespondensi :</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center><b>".$arrkodept['alamat']."</b></td>"; 
				
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center><b>Telp. ".$arrkodept['telepon']." Fax. ".$arrkodept['fax']."</b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		$tab.="</footer>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		foreach($arrpasal as $dtpasal){
			if(strlen($dtpasal)==1){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;' valign=top><b>Pasal ".$dtpasal." :</b></td>"; 
					$tab.="<td style='text-align:left;' valign=top><b>".nl2br($dtketerangan[$dtpasal])."</b></td>"; 
				$tab.="</tr>";
			}else{
					$dtketerangan[$dtpasal]=str_replace('_','&nbsp;',$dtketerangan[$dtpasal]);
					$dtketerangan[$dtpasal]=str_replace('plus','+',$dtketerangan[$dtpasal]);
					$tab.="<tr>";
						$tab.="<td style='text-align:left;' valign=top>".$dtpasal."</td>"; 
						$tab.="<td style='text-align:left;' valign=top>".nl2br($dtketerangan[$dtpasal])."</td>"; 
					$tab.="</tr>";
			
				
				/*
				// 
				if($dtpasal=='2.1'){
					$tab.="<tr>";
					$tab.="<td style='text-align:left;' valign=top>".$dtpasal."</td>"; 
					$tab.="<td style='text-align:left;white-space:pre;' valign=top>".nl2br($dtketerangan[$dtpasal])."</td>"; 
				$tab.="</tr>";
				}else{
					$tab.="<tr>";
						$tab.="<td style='text-align:left;' valign=top>".$dtpasal."</td>"; 
						$tab.="<td style='text-align:left;' valign=top>".nl2br($dtketerangan[$dtpasal])."</td>"; 
					$tab.="</tr>";
				}
				*/
				
				$tab.="<tr>";
				$tab.="<td style='text-align:left;width:60px' valign=top>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;' valign=top>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			$tab.="</div>";
		}
		
		
		
		// $tab.="</div>";
		
		// $tab.="<div style='page-break-after: always;'>";
		$tab.="<tr>";
			$tab.="<td style='text-align:left;' colspan=2 valign=top>Demikian pernyataan ini dibuat dan ditandatangani oleh kedua belah pihak pada hari dan tanggal seperti yang tertera pada awal perjanjian ini.</td>"; 
		$tab.="</tr>";

		$tab.="<tr>";
		$tab.="<td style='text-align:left;width:50px' valign=top>&nbsp;</td>"; 
			$tab.="<td style='text-align:left;' valign=top>&nbsp;</td>"; 
		$tab.="</tr>";
		
		$tab.="</table>";
		
		
		$tab.="<br>";
			
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Pihak Pertama</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>Pihak Kedua</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$arrkodept['nama']."</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>".$namacustomer."</td>"; 
			$tab.="</tr>";
			for($i=0;$i<=4;$i++){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".$_SESSION['lang']['nama']."&nbsp;&nbsp;&nbsp;: ".ucwords(strtolower(getKary($penandatangan)))."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>".$_SESSION['lang']['nama']."&nbsp;&nbsp;&nbsp;: ".$penandatangancustomer."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".$_SESSION['lang']['jabatan']." : ".ucwords(strtolower($jabatanpenandatangan))."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>".$_SESSION['lang']['jabatan']." : ".ucwords(strtolower($jabatancustomer))."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		

		/*
			$tab.="<footer>";
			$cellpadding=1;	
			$tab.="<table style='font-size:10px' border=0 cellpadding=".$cellpadding." width=100%>";	
				$tab.="<tr>";
					$tab.="<td align=center><b>Alamat Korespondensi :</b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=center><b>".$arrkodept['alamat']."</b></td>"; 
					
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=center><b>Telp. ".$arrkodept['telepon']." Fax. ".$arrkodept['fax']."</b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=left>Kontrak No. ".$param['nokontrak']."</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
			$tab.="</footer>";
		*/
		// $tab.="</div>";


// $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		
		$dompdf->render();
		
		$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");
	
		$dompdf->getCanvas()->page_text('525','50', "{PAGE_NUM} / {PAGE_COUNT} ".$a." ", $font, ($sizefont-4), array(0,0,0),0,0,0);
	
	
		$dompdf->getCanvas()->page_text('75','800',$_SESSION['lang']['NoKontrak'].' : '.$param['nokontrak'], $font, ($sizefont-4), array(0,0,0));
			
		
		
		if($urlefil=='0'){
			$dompdf->stream("Print_BAST_".$nobast,array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}	
	break;
	
	/*
	$x = 72;
        $y = 18;
        $text = "{PAGE_NUM} of {PAGE_COUNT}";
        $font = $fontMetrics->get_font("helvetica", "bold");
        $size = 6;
        $color = array(255,0,0);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
	*/
	
	
	
	
	
	
	
	case'submitfile':
	
		// $filesize=1;
	
		#= jadikan try commit
		try {
			
			$owlPDO->beginTransaction();
			
			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp=str_replace('-','',str_replace('/','',$param['nokontrak']));

			if ($_FILES['file']['size'] > $filesize){
				throw new PDOException("Ukuran File melebihi ".number_format($filesize/1024)." KB; ukuran file ini ".number_format($_FILES['file']['size']/1024,2)." Kb");
			}
			// print_r($_FILES);exit("Error:A");

			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){    
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $param['kriteriaefil']."_".$nmTemp."_".$his."".$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						$str = "insert into ".$dbname.".listfileupload values ('','".$param['nokontrak']."','".$filename."','".$filetype."','".$param['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}else{
						throw new PDOException("Format file upload tidak boleh ".$filetype);
					}
				}
			}else{
				throw new PDOException("Upload file gagal.");
			}
			
			if (!file_exists($path.$filename)) {
				throw new PDOException("File gagal diupload");
			}

			#= cek file size server jika 0 byte maka gagal insert db, tapi file tidak dihapus diserver
			if(filesize($path.$filename)=='' || filesize($path.$filename)=='0'){
				throw new PDOException("Ukuran file terupload 0, Silahkan upload ulang");
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());

		}			
		
    break;
	
	case 'deletefile':
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['nokontrak']."' and namafile='".$param['namafile']."'"; 
        try{
            $owlPDO->exec($str);
            $pathx = $path.str_replace('/','',$param['namafile']);
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
	break;
	
	case'loadfiles':
		$form='';
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['nokontrak']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
			$form.= "<tr>";
		}
		echo $form;
    break;  
	
	case'viewlistfile':
		$param=$_POST;
		$form="
		<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
			<thead>
			<tr class=rowheader>
				<th align='center'>".$_SESSION['lang']['nourut']."</th>
				<th align='center'>File Type</th>
				<th align='center'>Kriteria</th>
				<th align='center'>Filename</th>
				<th align='center'>Action</th>
			</tr>
			</thead>
			
		";
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['nokontrak']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td align=center><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$form.= "</tr>";
		}
		$form.="</table>
		";
		echo $form;
    break;  
	
	case'updatedt':
		#= update
		$str="update ".$dbname.".`pmn_kontrakjualdt_kontrakpanjang` set keterangan='".$param['keterangan']."',updateby='".$_SESSION['standard']['userid']."' where pasal='".$param['pasal']."' and  kodebarang='".$param['kodebarang']."' and nokontrak='".$param['nokontrak']."'  ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
	break;
	
	case'simpandt':
		if($param['pasal']!=''){
			#=delete dlu
			$str="delete from ".$dbname.".`pmn_kontrakjualdt_kontrakpanjang` where pasal='".$param['pasal']."' and  kodebarang='".$param['kodebarang']."' and nokontrak='".$param['nokontrak']."'  ";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		
			$str=" INSERT INTO ".$dbname.".`pmn_kontrakjualdt_kontrakpanjang` (`nokontrak`,`kodebarang`, `pasal`, `keterangan`, `createdby`, `createtime`, `updateby`)  values ('".$param['nokontrak']."','".$param['kodebarang']."','".$param['pasal']."','".$param['keterangan']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')"; 
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		}
		
	break;
	
	case'deletedt':
		$str="delete from ".$dbname.".`pmn_kontrakjualdt_kontrakpanjang` where pasal='".$param['pasal']."' and  kodebarang='".$param['kodebarang']."' and nokontrak='".$param['nokontrak']."'  ";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
		}
	break;
	
	
	case'datadetail':
	
		#= cek apakah detail sudah ada atau belum
		$str = "select count(*) as jumrow from ".$dbname.".pmn_kontrakjualdt_kontrakpanjang  where nokontrak='".$param['nokontrak']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			@$datatransaksi=$bar['jumrow'];
		}
		
		
		if($datatransaksi==0){
			$str="select * from ".$dbname.".pmn_5kontrakpanjang where  kodebarang='".$param['kodebarang']."' order by pasal asc ";
			// echo $str;
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrpasal[$bar['pasal']]=$bar['pasal'];
				$dtketerangan[$bar['pasal']]=$bar['keterangan'];
			} 
		}else{
			$str="select * from ".$dbname.".pmn_kontrakjualdt_kontrakpanjang where nokontrak='".$param['nokontrak']."' and kodebarang='".$param['kodebarang']."' order by pasal asc ";
			// echo $str;
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrpasal[$bar['pasal']]=$bar['pasal'];
				$dtketerangan[$bar['pasal']]=$bar['keterangan'];
			} 
		}
				
		$border='border=0';
		$no=0;
		$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center 20%>Pasal</th>";
				$stream.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
				$stream.="<th align=center width=20% colspan=2>".$_SESSION['lang']['action']."</th>";
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			foreach($arrpasal as $dtpasal){
				$no++;
				$stream.="<tr class=rowcontent id=row".$no.">";	
					$stream.="<td><input type=text disabled class=myinputtext id=pasal".$no." name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:100px;\" value=".$dtpasal." /></td>";
					$stream.="<td><textarea name=keterangan id=keterangan".$no." style=\"width:900px;\" onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20' >".$dtketerangan[$dtpasal]."</textarea></td>";
					
					if($datatransaksi==0){
						// $stream.="<td><button class=mybutton onclick=simpandt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
						$stream.="<td></td>";
						$stream.="<td></td>";
					}else{
						// $stream.="<td><button class=mybutton onclick=updatedt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
						$stream.="<td></td>";
						$stream.="<td><button class=mybutton onclick=deletedt('".$param['nokontrak']."','".$param['kodebarang']."','".$dtpasal."')>".$_SESSION['lang']['delete']."</button></td>";
					}
					
				$stream.="</tr>";	
			}
			
			if($datatransaksi>0){
				$no++;
				$stream.="<tr class=rowcontent>";	
					$stream.="<td><input type=text class=myinputtext id=pasal".$no." name=pasal maxlength=20 onkeypress=\"return tanpa_kutip(event)\" style=\"width:100px;\"  /></td>";
					$stream.="<td><textarea name=keterangan id=keterangan".$no."  style=\"width:900px;\"  onkeypress=\"return tanpa_kutip(event);\" rows='2' cols='20'></textarea></td>";
					// $stream.="<td><button class=mybutton onclick=simpandt('".$no."')>".$_SESSION['lang']['save']."</button></td>";
					$stream.="<td></td>";
					$stream.="<td></td>";
				$stream.="</tr>";
				$stream.="<tr class=rowcontent ".$no.">";	
					$stream.="<td colspan=4 align=center><button class=mybutton onclick=simpandtall('".$no."')>".$_SESSION['lang']['save']."</button></td>";
				$stream.="</tr>";
			}else{
				$stream.="<tr class=rowcontent>";	
					$stream.="<td colspan=4 align=center><button class=mybutton onclick=simpandtall('".$no."')>".$_SESSION['lang']['save']."</button></td>";
					
					
				$stream.="</tr>";	
				
			}
			
		$stream.="</tbody>";
		$stream.="<tr class=rowcontent>";
$stream.="<td colspan=4 align=center><img src=images/pdf.jpg  style='cursor:pointer' title='Print Short Contract : ".$param['nokontrak']."' onclick=\"masterPDF('pmn_kontrakjualv2','".$param['nokontrak']."','','pmn_kontakjual_pdf',event)\">&nbsp;&nbsp;&nbsp;<img src=images/pdf.jpg style='cursor:pointer'  caption='PDF'  title='Print Long Contract : ".$param['nokontrak']."' onclick=\"pdfpanjang('".$param['nokontrak']."');\"></td>";		
		$stream.="</tr>";	
		$stream.="</table>";
		
		echo $stream;
	break;
	
	
	case'loaddata':
	
		
		$where='1=1';
		
		if($param['tanggalselesaisch']!='' and $param['tanggalmulaisch']!=''){
			$where.=" and tanggalkontrak between '".tanggalsystemn($param['tanggalmulaisch'])."' and '".tanggalsystemn($param['tanggalselesaisch'])."'";
		}
		if($param['nokontraksch']!=''){
			$where.=" and nokontrak like '%".$param['nokontraksch']."%'";
		}
		if($param['kodeptsch']!=''){
			$where.=" and kodept='".$param['kodeptsch']."'";
		}
		if($param['kodecustomersch']!=''){
			$where.=" and koderekanan='".$param['kodecustomersch']."'";
		}
	
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
		$colspan=16;
	
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jlhbrs = $bar['jumrow'];
		}
		$no = 0;
		$no=$maxdisplay;
		$str = "select * from ".$dbname.".".$table."  where ".$where." order by tanggalkontrak desc limit " . $offset . "," . $limit . " ";
		
		$res=fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['nokontrak_manual']."</td>";
				$tab.="<td>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td>".$nmcustsomer[$bar['koderekanan']]."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['tanggalkontrak'])."</td>";
				// $tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				// $tab.="<td align=center>".tanggalnormal($bar['tanggalkirim'])."</td>";
				// $tab.="<td align=center>".$optjns[$bar['termbayar']]."</td>";
				// $tab.="<td>".$bar['tipepenjualan']."</td>";
				// $tab.="<td>".getKary($bar['updateby'])."</td>";
				
				// if($bar['posting']==0){
				// 	$tab.="<td align=center>
				// 		<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$bar['nokontrak']."');\">
				// 	</td>";
				// 	$tab.="<td align=center>
				// 		<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('".$bar['nokontrak']."');\" >
				// 	</td>";		
				// 	$tab.="<td align=center>
				// 		<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $bar['nokontrak'] . "','" . $no . "');\" >
				// 	</td>";							
				// }else{
				// 	$tab.="<td colspan=2></td>";
				// 	$tab.="<td align=center>
				// 		<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Unposting' onclick=\"unposting('" . $bar['nokontrak'] . "','" . $no . "');\" >
				// 	</td>";							
				// }		
				
				if($bar['posting'] == 0 || $bar['posting'] == 3){
					$tab.="<td align=center>
							<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$bar['nokontrak']."');\">
						</td>";
					$tab.="<td align=center>
							<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delData('".$bar['nokontrak']."');\" >
						</td>";
					$tab.="<td align=center valign=top style=\"width:25px;\">
						<img  src='images/skyblue/submit.jpg' class='zImgBtn' title='Ajukan' onclick=\"form_ajukan('".$bar['nokontrak']."');\" '>
					</td>";
				} else if ($bar['posting'] == 9){
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center><img src='images/icons/04/16/04.png' class='zImgBtn' height='30' title='On Progress Approval'></td>";
				} else if ($bar['posting'] == 2){
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center><img src='images/icons/04/16/01.png' class='zImgBtn' height='30' title='Approval Rejected'></td>";
				} else {
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td align=center><img src='images/icons/04/16/02.png' class='zImgBtn' height='30' title='Approved'></td>";
				}

				
				$tab.="<td hidden align=center>
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_kontrakjualv2','".$bar['nokontrak']."','','pmn_kontakjual_pdf',event)\">
				</td>";
				$tab.="<td hidden align=center   style=\"width:20px;\"><img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Kontrak Panjang : ".$bar['nokontrak']."' onclick=\"pdfpanjang('".$bar['nokontrak']."');\"></td>";
				$tab.="<td align=center  style=\"width:20px;\"><img title='".$_SESSION['lang']['view']."' class=zImgBtn onclick=\"viewlistfile('".$bar['nokontrak']."')\" src='images/zoom.png'/></td>";
			$tab.="</tr>";
        }
		
		## PAGING
		$footd.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getpage');
		
        echo $tab . "####" . $footd;
	break;
			
			
			
			
                case'LoadNew':
                    if($txtSearch!='')
                    {
                        $sort=" and nokontrak like '%".$txtSearch."%' ";
                    }
                    
                    if($ptSch!='')
                    {
                        $sort.=" and kodept like '%".$ptSch."%' ";
                    }
                   // exit("Error:$sort");
                    
                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                @$ql2="select count(*) as jmlhrow from ".$dbname.".pmn_kontrakjualv2 where kodebarang!='' ".$sort."  order by `tanggalkontrak` desc";
				$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
				$query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch()){
                    $jlhbrs= $jsl->jmlhrow;
                }
                $optjns=array("PM"=>"Pengiriman","PK"=>"Pemenuhan Kontrak","UM"=>"Uang Muka","BA"=>"Berita Acara Serah Terima"); 
                @$slvhc="select * from ".$dbname.".pmn_kontrakjualv2 where kodebarang!='' ".$sort."  order by `tanggalkontrak` desc limit ".$offset.",".$limit."";
				$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
				$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
                $user_online=$_SESSION['standard']['userid'];
                while($res=$qlvhc->fetch())
                {
                $no+=1;$arr="##'".$res['nokontrak']."'";	
                echo"
                        <tr class=rowcontent id=tr_$no>
                        <td align=center>".$no."</td>
                        <td id=detail_kode".$no.">".$res['nokontrak']."</td>
                        <td>".$nmpt[$res['kodept']]."</td>
                        <td>".$nmcustsomer[$res['koderekanan']]."</td>
                        <td align=center>".tanggalnormal($res['tanggalkontrak'])."</td>
                        <td align=center>".$res['kodebarang']."</td>
                        <td>".$nmkomoditi[$res['kodebarang']]."</td>
                        <td align=center>".$res['tanggalkirim']."</td>
                        <td align=center>".$optjns[$res['termbayar']]."</td>
                        <td align=center>".getNamaKaryawan($res['updateby'])."</td>
                        ";
                #cek apakah sudah terjurnal atau belum
                if ($res['posting'] == 0) {
					$isi1="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['nokontrak']."');\">";
					$isi2="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$res['nokontrak']."');\" >";
                    $isi3="<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $res['nokontrak'] . "','" . $no . "');\" >";
                } else {
                    $isi1="";
                    $isi2="";
                    $isi3="<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted' >";
                }
                echo"<td align=center style='padding:3px;width:10px;'>".$isi1."</td>
                    <td align=center style='padding:3px;width:10px;'>".$isi2."</td>
                    <td align=center style='padding:3px;width:10px;'>".$isi3."</td>";
                echo"<td align=center style='padding:3px;'>
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pmn_kontrakjualv2','".$res['nokontrak']."','','pmn_kontakjual_pdf',event)\">
				</td>
				<td align=center style='padding:3px;'>
					<img onclick=dataKeExcel(event,'pmn_slave_kontrakjual_excel.php','".$res['nokontrak']."') src=images/excel.jpg class=resicon title='MS.Excel'>
				</td>"; 
                if($res['koderekanan']=='API'){
                    echo"<img src=images/plus.png class=resicon title='Add ".$_SESSION['lang']['nokontrakinduk']." ".$_SESSION['lang']['dari']." ".$res['nokontrak']."' onclick=addDetail('".$res['nokontrak']."','".$res['kuantitaskontrak']."','".$res['kodebarang']."',event) />";
                }
				echo"
				<td align=center><img src=images/foldoq.png class=resicon  title='Document' onclick='showupload(event,".$no.")' ></td>
";

		 echo"</td>
                    </tr>";
                }
                echo"
                <tr class=rowheader><td colspan=10 align=center>
                ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
                break;
				
				case 'form_ajukan':
					$stream = '';
					$query  = "SELECT kodept FROM $dbname.pmn_kontrakjualv2 WHERE nokontrak = '".trim($param['notransaksi'])."'";
					$result = fetchData($query, 'OBJECT');
					$kdpt   = $result[0]->kodept;

					$query  = "SELECT kodept FROM $dbname.pmn_kontrakjualv2 WHERE nokontrak = '".trim($param['notransaksi'])."'";
					$result = fetchData($query, 'OBJECT');
					$kdpt   = $result[0]->kodept;

					$unitHO=$arrHo[$kdpt];
					

					$opt    = array();
					$query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = 'KTRKJUAL' AND kodeunit = '".$unitHO."' ORDER BY level";
					$result = fetchData($query, 'OBJECT');
			
					foreach ($result as $key => $value) {
						$opt['approver'][$value->level][$value->karyawanid] = "<option value='".$value->karyawanid."'>".getNamaKaryawan($value->karyawanid)."</option>";
						$opt['level'][$value->level] = $value->level;
					}
			
					$jumlahlevel = count($opt["level"]);
					$stream .= "<input type='hidden' id='notransaksi_ajukan' value='".trim($param['notransaksi'])."'/>";
					$stream .= "<input type='hidden' id='jlh' value='".$jumlahlevel."'/>";
					$optShow = "";
					foreach ($opt['approver'] as $level => $value1x) {
						foreach ($value1x as $karyid => $value2x) {
							// $optShow .= $value2x;

							$stream .= "<tr class='rowcontent'>";
								$stream .= "<td> Approval ke - ".$level."</td>";
								$stream .= "<td style='width:5px'> : </td>";
								$stream .= "<td>";
									$stream .= "<select id='kepada".$level."' style='width:99%'>".$opt['approver'][$level][$karyid]."</select>";
								$stream .= "</td>";
							$stream .= "</tr>";
						}
					}
			
			
					$stream .= "<tr class='rowcontent'>";
					$stream .= "<td></td>";
					$stream .= "<td></td>";
					$stream .= "<td>";
					$stream .= "<button id='tomboldetail' class='mybutton' onclick='ajukan()'>" . $_SESSION['lang']['diajukan'] . "</button>";
					$stream .= "</td>";
					$stream .= "</tr>";
			
					echo $stream;
				break;
			
				case 'ajukan':
					for ($i = 1; $i <= $param['jlh'] ; $i++) {
						$per['persetujuan'.$i] = checkPostGet("kepada".$i, '');
						if($per['persetujuan'.$i] == '' or trim($param['notransaksi']) == ''){
							exit('Warning : Isikan nama penyetuju.');
						}
					}
			
					$query = "UPDATE $dbname.pmn_kontrakjualv2 SET posting = '9' WHERE nokontrak = '".trim($param['notransaksi'])."'";
			
					try {
						$owlPDO->exec($query);
			
						$query  = "SELECT kodeorg FROM $dbname.pmn_kontrakjualv2 WHERE nokontrak = '".trim($param['notransaksi'])."'";
						$result = fetchData($query, 'OBJECT');
						$unit   = $result[0]->kodeorg;
			
						$jenispersetujuan = 'KTRKJUAL';
						for($i = 1; $i <= $param['jlh']; $i++){
							$query  = "SELECT * FROM $dbname.setup_approval WHERE jenispersetujuan = '$jenispersetujuan' AND level = '$i' AND kodeunit = '$unit'";
							$result = fetchData($query, 'OBJECT');
							$tipeapp            = $result[0]->tipe;
							$departemenapp      = $result[0]->departemen;
							$tipekaryawanapp    = $result[0]->tipekaryawan;
							$jabatanapp         = $result[0]->jabatan;
			
							if ($tipeapp == 1) {
								if ($departemenapp != "") {
									$query = "SELECT * FROM $dbname.datakaryawan WHERE bagian = '".$departemenapp."'";
								}
			
								if ($tipekaryawanapp != "") {
									$query = "SELECT * FROM $dbname.datakaryawan WHERE tipekaryawan = '".$tipekaryawanapp."'";
								}
			
								if ($jabatanapp != "") {
									$query = "SELECT * FROM $dbname.datakaryawan WHERE kodejabatan = '".$jabatanapp."'";
								}
			
								$result = fetchData($query, 'OBJECT');
								foreach($result as $key => $value){
									$query = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".trim($param['notransaksi'])."', '".$jenispersetujuan."', '".$i."', '".$valx['karyawanid']."', '0')";
			
									$owlPDO->exec($query);
								}
			
								break;
							} else {
								if($per['persetujuan'.$i] != ''){
									$query  = "INSERT INTO $dbname.approval (notransaksi,jenispersetujuan,level,karyawanid,status) VALUES ('".trim($param['notransaksi'])."', '".$jenispersetujuan."', '".$i."', '".$per['persetujuan'.$i]."', '0')";
								}
							}
			
							try {
								$owlPDO->exec($query);
							} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "\n";
								die();
							}
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}

				break;    
				break;
		

                case'getSatuan':
				$optSatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sSat2="select distinct satuan from ".$dbname.".log_5masterbarang where kodebarang='".$param['kdBrg']."'";
				$qSat2=$owlPDO->query($sSat2) or die(print " Gagal: ".PDOException::getMessage());
				$qSat2->setFetchMode(PDO::FETCH_ASSOC);
                $rsat2=$qSat2->fetch();

                $optSatuan.="<option value=".$rsat2['satuan']."  ".($rsat2['satuan']==$param['satuan']?'selected':'').">".$rsat2['satuan']."</option>";
                echo $optSatuan;
                break;
                case'getLastData':
                case'getEditData':
                $sql="select * from ".$dbname.".pmn_kontrakjualv2 where nokontrak='".$param['noKntrk']."'";
				$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
				$query->setFetchMode(PDO::FETCH_ASSOC);
                $res=$query->fetch();
                #ambil satuan
                @$optSatuan.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sSat2="select distinct satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
				$qSat2=$owlPDO->query($sSat2) or die(print " Gagal: ".PDOException::getMessage());
				$qSat2->setFetchMode(PDO::FETCH_ASSOC);
                $rsat2=$qSat2->fetch();
                $optSatuan.="<option value='".$rsat2['satuan']."' selected>".$rsat2['satuan']."</option>";
				
                #ambil data kontak
                $optKom=$optCon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                $sCust="select distinct idkontak,nama,telepon  from ".$dbname.".pmn_4customercontact where kodecustomer = '".$res['koderekanan']."' order by nama";
                $qCUst=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
				$qCUst->setFetchMode(PDO::FETCH_ASSOC);
                while($rCust=$qCUst->fetch()){
                        $optCon.="<option value='".$rCust['idkontak']."' ".($rCust['idkontak']==$res['idkontak']?'selected':'').">".$rCust['nama'].",".$rCust['telepon']."</option>";
                    }
                    #ambil data komoditi
                    $sCust2="select distinct kodebarang  from ".$dbname.".pmn_4komoditi where kodecustomer = '".$res['koderekanan']."' order by kodebarang";
				$qCUst2=$owlPDO->query($sCust2) or die(print " Gagal: ".PDOException::getMessage());
				$qCUst2->setFetchMode(PDO::FETCH_ASSOC);
                while($rCust2=$qCUst2->fetch()){
                            @$optKom.="<option value='".$rCust2['kodebarang']."' ".($rCust2['kodebarang']==$res['kodebarang']?'selected':'').">".$nmkomoditi[$rCust2['kodebarang']]."</option>";
                    }
                        #ambil toleransi
                        @$sTol="select distinct toleransipenyusutan  from ".$dbname.".pmn_4customer where kodecustomer='".$param['custId']."'";
						$qTol=$owlPDO->query($sTol) or die(print " Gagal: ".PDOException::getMessage());
						$qTol->setFetchMode(PDO::FETCH_ASSOC);
                        $rTol=$qTol->fetch();

						
					
						
                        #bayar ke
                        $optData=$optRek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sRek="select rekening,noakun,namabank,pemilik from ".$dbname.".keu_5akunbank 
								where pemilik in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$res['kodept']."') order by namabank asc";
						$qRek=$owlPDO->query($sRek) or die(print " Gagal: ".PDOException::getMessage());
						$qRek->setFetchMode(PDO::FETCH_ASSOC);
                        while($rCek=$qRek->fetch())
						{
							
							
							
							$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$rCek['namabank']."'");
							if($res['rekening']==$rCek['noakun'])
							{
								
								$optRek.="<option value='".$rCek['noakun']."' selected>".$rCek['pemilik'].":".$optNamaBank[$rCek['namabank']]." ".$rCek['rekening']."</option>";
							}
							else
							{
								$optRek.="<option value='".$rCek['noakun']."'>".$rCek['pemilik'].":".$optNamaBank[$rCek['namabank']]." ".$rCek['rekening']."</option>";
							}
							// $optRek.="<option value='".$rCek['noakun']."' ".($rCek['noakun']==$res['rekening']?'selected':'').">".$rCek['namabank'].",".$rCek['rekening']."</option>";
                        }
				#ambil nokontrak referensi
				// if($res['kodept']!='AMP'){
						// $sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjualv2 b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
						// $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
						// $qData->setFetchMode(PDO::FETCH_ASSOC);
						// while($rData=$qData->fetch()){
								// $optData.="<option value='".$rData['nokontrak']."'  ".($rData['nokontrak']==$res['nokontrak_ref']?'selected':'').">".$rData['nokontrak']."</option>";
							
						// }
				// }
				
				$optIniTmptCtr = makeOption($dbname,'pmn_5lokasikontrak','id,inisial',"id='".$res['lokasikontrak']."'");
				$optTmptCtr = makeOption($dbname,'pmn_5lokasikontrak','id,lokasi',"id='".$res['lokasikontrak']."'");
				$optLocKontrak = "<option value='".$res['lokasikontrak']."'>".$optIniTmptCtr[$res['lokasikontrak']]."-".$optTmptCtr[$res['lokasikontrak']]."</option>";
				
				$optTmptDaerahCtr = makeOption($dbname,'pmn_5daerahkontrak','id,lokasi',"id='".$res['daerahkontrak']."'");
				$optDaerahCtr = "<option value='".$res['daerahkontrak']."'>".$optTmptDaerahCtr[$res['daerahkontrak']]."</option>";
				

				
				
                echo $res['nokontrak']."###".$res['koderekanan']."###".tanggalnormal($res['tanggalkontrak'])."###".$optKom."###".$optSatuan."###".$res['hargasatuan']."###".$res['matauang']."###".$res['terbilang']."###".$res['kuantitaskontrak']."###".tanggalnormal($res['tanggalkirim'])."###".tanggalnormal($res['sdtanggal'])."###".tanggalnormal($res['tanggalkirim1'])."###".tanggalnormal($res['sdtanggal1'])."###".tanggalnormal($res['tanggalkirim2'])."###".tanggalnormal($res['sdtanggal2'])."###".tanggalnormal($res['tanggalkirim3'])."###".tanggalnormal($res['sdtanggal3'])."###".$res['kuantitaskirim']."###".$res['kuantitaskirim1']."###".$res['kuantitaskirim2']."###".$res['kuantitaskirim3']."###".$res['franco']."###".$res['ffa']."###".$res['dobi']."###".$res['mdani']."###".$res['toleransi']."###".$res['kdtermin']."###".$optRek."###".$res['penandatangan']."###".$res['namajabatan']."###".$res['penandatangan2']."###".$res['namajabatan2']."###".$res['catatanlain']."###".$optCon."###".$res['kodept']."###".$res['ppn']."###".tanggalnormal($res['tglpembayarpertama'])."###".$res['moist']."###".$res['dirt']."###".$res['grading']."###".$optData."###".$res['ketbayardp']."###".$res['ketbayarpelunasan']."###".$res['berikat']."###".$res['forcemajuere']."###".$res['perselisihan']."###".$res['nokontrakexternal']."###".$optLocKontrak."###".$optDaerahCtr."###".$res['nokontrak_manual']."###".$res['termbayar']."###".$res['millcode']."###".$res['tipepenjualan']."###".$res['persenppn']."###".tanggalnormal($res['tanggalberlaku']);
                break;
                case'insert':
				
			
					// exit("Error:".$param['byrKe']);
                    $tgl=explode("-",$param['tlgKntrk']);
                    $whr="kodebarang='".$param['kdBrg']."'";
                    $optKd=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',$whr);
                    $sCek="select max(nokontrak) as nokontrak from ".$dbname.".pmn_kontrakjualv2 where koderekanan='".$param['custId']."'";
					$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek=$qCek->fetch();



                    $tmpnoKntak=explode("/",$rCek['nokontrak']);
                    $noKntak=explode("-",$tmpnoKntak[0]);
                    if(@intval($tmpnoKntak[0])==0){
                            @$nourut=addZero((intval($tmpnoKntak[0])+1),3);
                    }else{
                            @$nourut=addZero((intval($tmpnoKntak[0])+1),3);
                    }
					$nokontrak=$nourut."/".$param['custId']."/".$optKd[$param['kdBrg']]."/".$param['kdPt']."/".romawi(intval($tgl[1]))."/".substr($tgl[2],2,2);

										
					$sql="SELECT nokontrak_manual FROM ".$dbname.".pmn_kontrakjualv2 where nokontrak_manual='".$param['noKtrk_M']."' ";
					$rsql=fetchData($sql);

					$cek_ktrk=count($rsql);
					$ketwar=" Nomor Kontrak '".$param['noKtrk_M']."' Sudah Pernah diinputkan sebelumnya";
					if ($cek_ktrk>0) {
						exit("Warning !! " .$ketwar);
					}
 					
					$optIniTmptCtr = makeOption($dbname,'pmn_5lokasikontrak','id,inisial',"id='".$posisictr."'");
					$optDaerahCtr = makeOption($dbname,'pmn_5lokasikontrak','id,lokasi',"id='".$daerahctr."'");
					
										
					// if(($param['custId']=='')||($param['kdBrg']=='') ||($param['tlgKntrk']=='')){
                    //                             echo"Tolong Cek :";
                    //                             echo"<ol type=1>";
                    //                             echo"<li>".$_SESSION['lang']['nmcust']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['namabarang']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['hargasatuan']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['jmlhBrg']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['tglKontrak']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['satuan']."</li>";
                    //                             echo"<li>".$_SESSION['lang']['payment']."</li>";
                    //                             echo"</ol>";
                    //                             echo"Warning:".$_SESSION['lang']['kosong'];
                    //                             exit();
                    //     }
					
					// ||($param['kdBrg']=='') ||($param['tlgKntrk']==''))
					if($param['custId']==''){ 
						exit('Warning !! Nama Pelanggan tidak boleh kosong ');
                    }
					if($param['kdBrg']==''){ 
						exit('Warning !! Komoditi tidak boleh kosong ');
                    }
					if($param['tlgKntrk']==''){ 
						exit('Warning !! Tanggal Kontrak tidak boleh kosong ');
                    }
					if($param['tglberlaku']==''){ 
						exit('Warning !! Tanggal Berlaku Kontrak tidak boleh kosong ');
                    }
					if($param['noKtrk_M']==''){
						exit('<label hidden>Warning !!</label> Informasi <br/> Nomor Kontrak Manual tidak boleh kosong ');
					}
					
                    $param['tglKrm0']==''?$param['tglKrm0']='0000-00-00':tanggalsystem($param['tglKrm0']);
                    $param['tglKrm1']==''?$param['tglKrm1']='0000-00-00':tanggalsystem($param['tglKrm1']);
                    $param['tglKrm2']==''?$param['tglKrm2']='0000-00-00':tanggalsystem($param['tglKrm2']);
                    $param['tglKrm3']==''?$param['tglKrm3']='0000-00-00':tanggalsystem($param['tglKrm3']);
                    $param['tglSd0']==''?$param['tglSd0']='0000-00-00':tanggalsystem($param['tglSd0']);
                    $param['tglSd1']==''?$param['tglSd1']='0000-00-00':tanggalsystem($param['tglSd1']);
                    $param['tglSd2']==''?$param['tglSd2']='0000-00-00':tanggalsystem($param['tglSd2']);
                    $param['tglSd3']==''?$param['tglSd3']='0000-00-00':tanggalsystem($param['tglSd3']);
                    $param['jmlh0']==''?$param['jmlh0']=0:$param['jmlh0']=$param['jmlh0'];
                    $param['jmlh1']==''?$param['jmlh1']=0:$param['jmlh1']=$param['jmlh1'];
                    $param['jmlh2']==''?$param['jmlh2']=0:$param['jmlh2']=$param['jmlh2'];
                    $param['jmlh3']==''?$param['jmlh3']=0:$param['jmlh3']=$param['jmlh3'];
                    $param['moist']==''?$param['moist']=0:$param['moist']=$param['moist'];
                    $param['dirt']==''?$param['dirt']=0:$param['dirt']=$param['dirt'];
                    $param['grading']==''?$param['grading']=0:$param['grading']=$param['grading'];
                    $param['kualitasffa']==''?$param['kualitasffa']=0:$param['kualitasffa']=$param['kualitasffa'];
                    $param['kualitasdob']==''?$param['kualitasdob']=0:$param['kualitasdob']=$param['kualitasdob'];
                    $param['kualitasmdani']==''?$param['kualitasmdani']=0:$param['kualitasmdani']=$param['kualitasmdani'];

					$termbyr='BA';
					
                            $sIns="insert into ".$dbname.".pmn_kontrakjualv2 (`nokontrak`,`nokontrak_manual`, `tanggalkontrak`,`tanggalberlaku`, `koderekanan`, `kodebarang`, `satuan`, 
									`hargasatuan`, `terbilang`, `tanggalkirim`, `sdtanggal`, `tanggalkirim1`, `sdtanggal1`, `tanggalkirim2`, 
									`sdtanggal2`, `tanggalkirim3`, `sdtanggal3`, `rekening`, `kdtermin`, `franco`, `ffa`, `dobi`, `mdani`, 
									`kuantitaskirim`, `kuantitaskirim1`, `kuantitaskirim2`, `kuantitaskirim3`, `penandatangan`, `penandatangan2`, 
									`namajabatan`, `namajabatan2`, `catatanlain`, `kuantitaskontrak`, `toleransi`, `kodeorg`, `kodept`, `matauang`,
									`idkontak`,`ppn`,`tglpembayarpertama`,`moist`,`dirt`,`grading`,`ketbayardp`,`ketbayarpelunasan`,
									`berikat`,`forcemajuere`,`perselisihan`,`nokontrakexternal`,`lokasikontrak`,`daerahkontrak`,`termbayar`,`millcode`,
									tipepenjualan,updateby,persenppn,tipekontrak) 
                                               values ('".$nokontrak."','".$param['noKtrk_M']."','".tanggalsystem($param['tlgKntrk'])."','".tanggalsystem($param['tglberlaku'])."','".$param['custId']."','".$param['kdBrg']."',
											   '".$param['satuan']."','".$param['HrgStn']."','".$param['tBlg']."','".tanggalsystem($param['tglKrm0'])."',
											   '".tanggalsystem($param['tglSd0'])."','".tanggalsystem($param['tglKrm1'])."',
											   '".tanggalsystem($param['tglSd1'])."','".tanggalsystem($param['tglKrm2'])."',
											   '".tanggalsystem($param['tglSd2'])."','".tanggalsystem($param['tglKrm3'])."',
											   '".tanggalsystem($param['tglSd3'])."','".$param['byrKe']."','".$param['syrtByr']."',
											   '".$param['franco']."','".$param['kualitasffa']."','".$param['kualitasdob']."',
											   '".$param['kualitasmdani']."','".$param['jmlh0']."','".$param['jmlh1']."','".$param['jmlh2']."',
											   '".$param['jmlh3']."','".$param['tndtng']."','".$param['tndtngPembli']."','".$param['tndtngJbtn']."',
											   '".$param['jtbnPembli']."','".$param['cttnLain']."','".$param['qty']."','".$param['tlransi']."',
											   '".$_SESSION['empl']['lokasitugas']."','".$param['kdPt']."','".$param['kurs']."',
											   '".$param['nmPerson']."','".$param['ppnId']."','".tanggalsystem($param['tglByr'])."',
											   ".$param['moist'].",".$param['dirt'].",".$param['grading'].",'".$param['kntrkRef']."',
											   '".$param['ketdp']."','".$param['ketplns']."',
											   '".$param['berikat']."','".$param['forcemajuere']."','".$param['perselisihan']."','".$posisictr."',
											   '".$daerahctr."','".$termbyr."','".$param['millcode']."',
											   '".$param['tppenjualan']."','" . $_SESSION['standard']['userid'] . "','".$param['persenppn']."','".$param['tpkontrak']."')"; 
											   // echo"warning:".$sIns;exit();
                                    try{
										$owlPDO->exec($sIns);
									}catch (PDOException $e){
										echo "DB Error : ".$e->getMessage();
									}
									
									echo $nokontrak;
									// exit('error');
                break;
                case'update':
                        if(($param['custId']=='')||($param['kdBrg']=='')||($param['tlgKntrk']=='')){
                        // if(($param['custId']=='')||($param['kdBrg']=='')||($param['HrgStn']=='')||($param['qty']=='')||($param['tlgKntrk']=='')||($param['satuan']=='')||($termbyr=='')){
                                                echo"Tolong Cek :";
                                                echo"<ol type=1>";
                                                echo"<li>".$_SESSION['lang']['nmcust']."</li>";
                                                echo"<li>".$_SESSION['lang']['namabarang']."</li>";
                                                echo"<li>".$_SESSION['lang']['hargasatuan']."</li>";
                                                echo"<li>".$_SESSION['lang']['jmlhBrg']."</li>";
                                                echo"<li>".$_SESSION['lang']['tglKontrak']."</li>";
                                                echo"<li>".$_SESSION['lang']['satuan']."</li>";
                                                echo"<li>".$_SESSION['lang']['payment']."</li>";
                                                echo"</ol>";
                                                echo"Warning:".$_SESSION['lang']['kosong'];
                                                exit();
                        }
						
                $str="update ".$dbname.".pmn_kontrakjualv2 set `tanggalkontrak`='".tanggalsystem($param['tlgKntrk'])."', `koderekanan`='".$param['custId']."', 
                      `kodebarang`='".$param['kdBrg']."', `satuan`='".$param['satuan']."', `hargasatuan`='".$param['HrgStn']."', `terbilang`='".$param['tBlg']."', 
                      `tanggalkirim`='".tanggalsystem($param['tglKrm0'])."', `sdtanggal`='".tanggalsystem($param['tglSd0'])."', `tanggalkirim1`='".tanggalsystem($param['tglKrm1'])."', 
                      `sdtanggal1`='".tanggalsystem($param['tglSd1'])."', `tanggalkirim2`='".tanggalsystem($param['tglKrm2'])."', `sdtanggal2`='".tanggalsystem($param['tglSd2'])."', 
                      `tanggalkirim3`='".tanggalsystem($param['tglKrm3'])."', `sdtanggal3`='".tanggalsystem($param['tglSd3'])."', `tanggalberlaku`='".tanggalsystem($param['tglberlaku'])."', `rekening`='".$param['byrKe']."', `kdtermin`='".$param['syrtByr']."', 
                      `franco`='".$param['franco']."', `ffa`='".$param['kualitasffa']."', `dobi`='".$param['kualitasdob']."', `mdani`='".$param['kualitasmdani']."', `kuantitaskirim`='".$param['jmlh0']."', 
                      `kuantitaskirim1`='".$param['jmlh1']."', `kuantitaskirim2`='".$param['jmlh2']."', `kuantitaskirim3`='".$param['jmlh3']."', `penandatangan`='".$param['tndtng']."', `penandatangan2`='".$param['tndtngPembli']."', 
                      `namajabatan`='".$param['tndtngJbtn']."', `namajabatan2`='".$param['jtbnPembli']."', `catatanlain`='".$param['cttnLain']."', `kuantitaskontrak`='".$param['qty']."', `toleransi`='".$param['tlransi']."', `kodept`='".$param['kdPt']."', 
                      `matauang`='".$param['kurs']."',idkontak='".intval($param['nmPerson'])."',ppn='".$param['ppnId']."',`tglpembayarpertama`='".tanggalsystem($param['tglByr'])."',moist='".$param['moist']."',dirt='".$param['dirt']."',grading='".$param['grading']."',`ketbayardp`='".$param['ketdp']."',`ketbayarpelunasan`='".$param['ketplns']."',
					  `berikat`='".$param['berikat']."',`forcemajuere`='".$param['forcemajuere']."',`perselisihan`='".$param['perselisihan']."',
					  `nokontrakexternal`='".$param['noext']."',`termbayar`='".$param['termbyr']."',`millcode`='".$param['millcode']."',
					  `tipepenjualan`='".$param['tppenjualan']."',updateby='" . $_SESSION['standard']['userid'] . "',persenppn='".$param['persenppn']."',nokontrak_manual='".$param['noKtrk_M']."'
					  where nokontrak='".$param['noKntrk']."'"; 
					  // exit("Error:$str");
					try{
						$owlPDO->exec($str);
					}catch (PDOException $e){
						echo "DB Error : ".$e->getMessage();
					}
                break;
                case'getCust':	
                            $optKom=$optCon="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
					$sCust="select distinct idkontak,nama,telepon  from ".$dbname.".pmn_4customercontact where kodecustomer = '".$param['custId']."' order by nama";
					$qCUst=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
					$qCUst->setFetchMode(PDO::FETCH_ASSOC);
					while($rCust=$qCUst->fetch()){
									$optCon.="<option value='".$rCust['idkontak']."'>".$rCust['nama'].",".$rCust['telepon']."</option>";
							}
							$sCust2="select distinct kodebarang  from ".$dbname.".pmn_4komoditi where kodecustomer = '".$param['custId']."' order by kodebarang";
					$qCUst2=$owlPDO->query($sCust2) or die(print " Gagal: ".PDOException::getMessage());
					$qCUst2->setFetchMode(PDO::FETCH_ASSOC);
					while($rCust2=$qCUst2->fetch()){
								$whr="kodebarang='".$rCust2['kodebarang']."'";
								$optKom.="<option value='".$rCust2['kodebarang']."'>".$nmkomoditi[$rCust2['kodebarang']]."</option>";
						}
						$sTol="select toleransipenyusutan,statusberikat  from ".$dbname.".pmn_4customer where kodecustomer='".$param['custId']."'";
						$qTol=$owlPDO->query($sTol) or die(print " Gagal: ".PDOException::getMessage());
						$qTol->setFetchMode(PDO::FETCH_ASSOC);
						$rTol=$qTol->fetch();
				
                echo $optCon."###".$optKom."###".$rTol['statusberikat']."###".$rTol['toleransipenyusutan'];
				// exit("Error:MASUK");
                break;

                case'dataDel':
                $sDel="delete from ".$dbname.".pmn_kontrakjualv2 where nokontrak='".$param['noKntrk']."'" ;
				try{
					$owlPDO->exec($sDel);
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
				}	
                break;
                    case'getRek': 
                            $optData=$optRek="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                            $sRek="select distinct rekening,noakun,namabank,pemilik from ".$dbname.".keu_5akunbank where pemilik in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_POST['kdpt']."') order by namabank asc";
							$qRek=$owlPDO->query($sRek) or die(print " Gagal: ".PDOException::getMessage());
							$qRek->setFetchMode(PDO::FETCH_ASSOC);
                            while($rCek=$qRek->fetch())
							{
								
								// $optByrke.="<option value='".$rByr['noakun']."'>".$rByr['pemilik'].":".$optNamaBank[$rByr['namabank']]." ".$rByr['rekening']."</option>";
								$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$rCek['namabank']."'");
								$optRek.="<option value='".$rCek['noakun']."' selected>".$rCek['pemilik'].":".$optNamaBank[$rCek['namabank']]." ".$rCek['rekening']."</option>";
                            }
							if($_POST['kdpt']!='AMP'){
								$sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjualv2 b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
								$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
								$qData->setFetchMode(PDO::FETCH_ASSOC);
								while($rData=$qData->fetch()){
									if($rData['jmlh']<$rData['kuantitaskontrak']){
										$optData.="<option value='".$rData['nokontrak']."'>".$rData['nokontrak']."</option>";
									}
									
								}
							}
							
                            echo $optRek."####".$optData;
                    break;
                case'getFormDet':
                        $optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sData="select kuantitaskontrak,nokontrak from ".$dbname.".pmn_kontrakjualv2"
                             . " where kodept='AMP' and kodebarang='".$_POST['komoditi']."' order by nokontrak";
                        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
						$qData->setFetchMode(PDO::FETCH_ASSOC);
                        while($rData=$qData->fetch()){
                                $sSum="select sum(beratbersih) as jmlh from ".$dbname.".pabrik_timbangan where nokontrak='".$rData['nokontrak']."'";
								$qSum=$owlPDO->query($sSum) or die(print " Gagal: ".PDOException::getMessage());
								$qSum->setFetchMode(PDO::FETCH_ASSOC);
                                $rSum=$qSum->fetch();
                                        $optData.="<option value='".$rData['nokontrak']."'>".$rData['nokontrak']."</option>";
                        }
                        //echo $sData;
                    $tab.="<table cellpadding=1 cellspacing=1 border=0>";
                    $tab.="<thead><tr>";
                    $tab.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['volumekontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['nokontrakinduk']."</td>";
                    $tab.="<td>".$_SESSION['lang']['jumlah']."</td>";
                    $tab.="<td>".$_SESSION['lang']['action']."</td>";
                    $tab.="<tr></thead><tbody>";
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td><input type=text id=nokontrak class=myinputtext value='".$_POST['nokontrak']."' readonly=readonly style=width:150px /></td>";
                    $tab.="<td><input type=text id=jmlHnokontrak class=myinputtextnumber value='".number_format($_POST['totKontrak'],0)."' readonly=readonly /></td>";
                    $tab.="<td><select id=nokntr_ref>".$optData."</select></td>";
                    $tab.="<td><input type=text class=myinputtextnumber id=jmlhRef onkeypress='return angka_doang(event)' /></td>";
                    $tab.="<td><input type=hidden id=nokntr_ref2 value='' /><img src=images/save.png class=resicon onclick=saveDet() /></td>";
                    $tab.="</tr>";
                    $tab.="</tbody></table><br />";
                    $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100%>";
                    $tab.="<thead><tr>";
                    $tab.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['volumekontrak']."</td>";
                    $tab.="<td>".$_SESSION['lang']['nokontrakinduk']."</td>";
                    $tab.="<td>".$_SESSION['lang']['kuota']."</td>";
                    $tab.="<td>".$_SESSION['lang']['terpenuhi']."</td>";
                    $tab.="<td>".$_SESSION['lang']['sisa']."</td>";
                    $tab.="<td>".$_SESSION['lang']['action']."</td>";
                    $tab.="<tr></thead><tbody id=isidetail>";
                    $sData="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
					$qData->setFetchMode(PDO::FETCH_ASSOC);
					$rwDt=owlBaris($qData);
					if($rwDt==0){
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }else{
                        while($rData=$qData->fetch()){
                            $tab.="<tr class=rowcontent>";
                            $tab.="<td>".$rData['nokontrak']."</td>";
                            $tab.="<td align=right>".number_format($_POST['totKontrak'],0)."</td>";
                            $tab.="<td>".$rData['nokontrak_ref']."</td>";
                            $tab.="<td align=right>".number_format($rData['kuota'],0)."</td>";
                            $tab.="<td align=right>".number_format($rData['terpenuhi'],0)."</td>";
                            $rData['sisa']=$rData['kuota']-$rData['terpenuhi'];
                            $tab.="<td align=right>".number_format($rData['sisa'],0)."</td>";
                            if($rData['terpenuhi']==0){
                                $tab.="<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\" ></td>";
                            }else{
                                $tab.="<td>&nbsp;</td>";
                            }
                            
                            $tab.="</tr>";
                        }
                    }
                    
                    $tab.="</tbody></table>";
                    echo $tab;
                break;
                case'loadDet':
                    $whr="nokontrak='".$_POST['nokontrak']."'";
                    $optTot=  makeOption($dbname, 'pmn_kontrakjualv2', 'nokontrak,kuantitaskontrak',$whr);
                    $sData="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
                    $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
					$qData->setFetchMode(PDO::FETCH_ASSOC);
					$rwDt=owlBaris($qData);
					if($rwDt==0){
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td></tr>";
                    }else{
                        while($rData=$qData->fetch()){
                            $tab.="<tr class=rowcontent>";
                            $tab.="<td>".$rData['nokontrak']."</td>";
                            $tab.="<td align=right>".number_format($optTot[$_POST['nokontrak']],0)."</td>";
                            $tab.="<td>".$rData['nokontrak_ref']."</td>";
                            $tab.="<td align=right>".number_format($rData['kuota'],0)."</td>";
                            $tab.="<td align=right>".number_format($rData['terpenuhi'],0)."</td>";
                            $rData['sisa']=$rData['kuota']-$rData['terpenuhi'];
                            $tab.="<td align=right>".number_format($rData['sisa'],0)."</td>";
                            if($rData['terpenuhi']==0){
                                $tab.="<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\">
                                       <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData2('".$rData['nokontrak']."','".$rData['nokontrak_ref']."');\" ></td>";
                            }else{
                                $tab.="<td>&nbsp;</td>";
                            }
                            $tab.="</tr>";
                        }
                    }
                    echo $tab;
                break;
                case'saveDet':
                    
                    $_POST['jmlHnokontrak']=  str_replace(",","", $_POST['jmlHnokontrak']);
                    $sCek="select terpenuhi from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
                    $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek=$qCek->fetch();
                    if($rCek['terpenuhi']==0){
                        #cek apakah pembagian kuantitas kontrak induk sudah lebih atau belum
                        #query mengambil kuantitaskontrak nokontrak induk
                        $sCekKontrakInduk="select kuantitaskontrak from ".$dbname.".pmn_kontrakjualv2 where nokontrak='".$_POST['nokntr_ref']."'";
						$qCekKontrakInduk=$owlPDO->query($sCekKontrakInduk) or die(print " Gagal: ".PDOException::getMessage());
						$qCekKontrakInduk->setFetchMode(PDO::FETCH_ASSOC);
                        $rCekKontrakInduk=$qCekKontrakInduk->fetch();
                        #query cari data totalan kuota atas nokontrak induk
                        $sSum2="select sum(kuota) as total from ".$dbname.".pmn_kontrakjualdt where nokontrak_ref='".$_POST['nokntr_ref']."'";
						$qSum2=$owlPDO->query($sSum2) or die(print " Gagal: ".PDOException::getMessage());
						$qSum2->setFetchMode(PDO::FETCH_ASSOC);
                        $rSum2=$qSum2->fetch();
                        if(intval($rSum2['total'])>$rCekKontrakInduk['kuantitaskontrak']){
                            exit("warning: Total distribusi ".$_SESSION['lang']['kuota']." (".$rSum2['total'].") melebihi ".$_SESSION['lang']['volumekontrak']." (".$rCekKontrakInduk['kuantitaskontrak'].") ".$_SESSION['lang']['nokontrakinduk']." : ".$_POST['nokntr_ref']);
                        }
                        
                        #cek apakah sudah melebihi kuota kontrak detail
                        $sSum="select sum(kuota) as total from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."'";
						$qSum=$owlPDO->query($sSum) or die(print " Gagal: ".PDOException::getMessage());
						$qSum->setFetchMode(PDO::FETCH_ASSOC);
                        $rSum=$qSum->fetch();
                        if(($rSum['total']+$_POST['jmlhRef'])>$_POST['jmlHnokontrak']){
                            exit("warning: Total ".$_SESSION['lang']['kuota']." melebihi ".$_SESSION['lang']['volumekontrak']."  ".$_POST['nokontrak']);
                        }
                                if($_POST['nokntr_ref2']==''){                    
                                    #insert detail dari no induk
                                     $sdel="delete from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
									try{
										$owlPDO->exec($sdel);
										$sInsert="insert into ".$dbname.".pmn_kontrakjualdt values ('".$_POST['nokontrak']."','".$_POST['nokntr_ref']."','".$_POST['jmlhRef']."','0')";
										try{
											$owlPDO->exec($sInsert);
										}catch (PDOException $e){
											exit("warning: ".$e->getMessage()."___".$sInsert);
										}
									}catch (PDOException $e){
										exit("warning: ".$e->getMessage()."___".$sdel);
									}
                                }else{
                                    $supdate="update ".$dbname.".pmn_kontrakjualdt set kuota='".$_POST['jmlhRef']."',nokontrak_ref='".$_POST['nokntr_ref']."' where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref2']."'";
									try{
										$owlPDO->exec($supdate);
									}catch (PDOException $e){
										exit("warning: ".$e->getMessage()."___".$supdate);
									}
                                }
                    }else{
                        exit("warning:  Jurnal Sudah Terbentuk");
                    }
                break;
                
                case'delDet':
                    $sCek="select terpenuhi from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
					$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek=$qCek->fetch();
                    if($rCek['terpenuhi']==0){
                         $sdel="delete from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
						try{
							$owlPDO->exec($sdel);
						}catch (PDOException $e){
							exit("warning: ".$e->getMessage()."___".$sdel);
						}
                    }else{
                        exit("warning: Jurnal Sudah Terbentuk");
                    }
                break;

                case'editDet':
                    $sCek="select * from ".$dbname.".pmn_kontrakjualdt where nokontrak='".$_POST['nokontrak']."' and nokontrak_ref='".$_POST['nokntr_ref']."'";
					$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
					$qCek->setFetchMode(PDO::FETCH_ASSOC);
                    $rCek=$qCek->fecth();
                    if($rCek['terpenuhi']==0){
                        $optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        $sData="select sum(beratbersih) as jmlh,kuantitaskontrak,a.nokontrak from ".$dbname.".pabrik_timbangan a left join ".$dbname.".pmn_kontrakjualv2 b on a.nokontrak=b.nokontrak where b.kodept='AMP' group by a.nokontrak";
						$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
						$qData->setFetchMode(PDO::FETCH_ASSOC);
                        while($rData=$qData->fetch()){
                                //if($rData['jmlh']<$rData['kuantitaskontrak']){
                                        $optData.="<option value='".$rData['nokontrak']."' ".($rCek['nokontrak_ref']==$rData['nokontrak']?"selected":"").">".$rData['nokontrak']."</option>";
                                //}
                        }
                        echo $rCek['nokontrak']."####".$optData."####".$rCek['kuota']."####".$rCek['nokontrak_ref'];
                    }else{
                        exit("warning: Jurnal Sudah Terbentuk");
                    }
                break;

                case'posting':
                $str = "update " . $dbname . ".pmn_kontrakjualv2 set posting='1',postingby='" . $_SESSION['standard']['userid'] . "' where nokontrak='" . $param['nokontrak'] . "'";

                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
				$msgdt="Pemberitahuan bahwa kontrak penjualan sudah dibuat dengan nomor ".$param['nokontrak']." ";
				$str="select * from ".$dbname.".setup_notification_dt where kodejenis='PMNKONTRAK'";
				$res=fetchdata($str);
				foreach($res as $bar){
					createnotif($param['nokontrak'],'PMNKONTRAK',$msgdt,$bar['karyawanid'],date('Y-m-d H:i:s'));
				}
				#= buat notif
				// function createnotif($notrk,$tipe,$msgdt,$createby,$tanggal){
					// global $dbname;
					// global $owlPDO;
					
					// $stry="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$notrk."','".$tipe."','".$msgdt."','".$createby."','0','0','".$tanggal."')";
					// $owlPDO->exec($stry);
				// }

				
				/*
                $slvhc="select * from ".$dbname.".pmn_kontrakjualv2 where nokontrak='" . $param['nokontrak'] . "'";
                $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
                $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
                $user_online=$_SESSION['standard']['userid'];
                $bar=$qlvhc->fetch();
                $kodeorg=substr($param['nokontrak'],4,3);
                $kdCust=$bar['koderekanan'];
                $kdbrg=$bar['kodebarang'];
                if($bar['sdtanggal3']=='0000-00-00'){
                    if($bar['sdtanggal2']=='0000-00-00'){
                        if($bar['sdtanggal1']=='0000-00-00'){
                            $tglAkhir = $bar['sdtanggal'];
                        }else{
                            $tglAkhir = $bar['sdtanggal1'];
                        }
                    }else{
                        $tglAkhir = $bar['sdtanggal2'];
                    }
                }else{
                    $tglAkhir = $bar['sdtanggal3'];
                }
                $kettgl= tanggalnormal($bar['tanggalkirim'])." s/d ".tanggalnormal($tglAkhir);

                $ffaData="FFA ".number_format($bar['ffa'],2)." % Max";
                $dobiData="Dobi ".number_format($bar['dobi'],2)." Min";
                $mdaniData="M & I ".number_format($bar['mdani'],2)." % Max";
                $moistData="Moisture ".number_format($bar['moist'],2)." % Max";
                $dirtData="Impurities ".number_format($bar['dirt'],2)." % Max";
                $gradingData="Grading ".number_format($bar['grading'],2)." %";

                $ss="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='PM' and kodeparameter='PMKONTRAK' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $nilai=$bs['nilai'];

                $ss="select karyawanid,email from ".$dbname.".datakaryawan  where kodejabatan='".$nilai."' and kodeorganisasi='".$kodeorg."' and lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $karyawanid=$bs['karyawanid'];
                $email=$bs['email'];

                $ss="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
                $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
                $rs->setFetchMode(PDO::FETCH_ASSOC);
                $bs=$rs->fetch();
                $namaorganisasi=$bs['namaorganisasi'];

                $str="select * from ".$dbname.".pmn_4customer  where kodecustomer='".$kdCust."' ";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $b=$res->fetch();

                $s="select * from ".$dbname.".log_5masterbarang where kodebarang='".$kdbrg."' ";
                $r=$owlPDO->query($s) or die(print " Gagal: ".PDOException::getMessage());
                $r->setFetchMode(PDO::FETCH_ASSOC);
                $br=$r->fetch();

                if ($email!=''){
                $to = getUserEmail($karyawanid);
                $subject = "[Notifikasi] Kontrak Penjualan";
                $body = "<html>
                            <head>
                             <body>
                               <dd>Dengan Hormat,</dd><br>
                               <br>
                               Pada hari ini, tanggal " . date('d-m-Y') . " Kontrak Penjualan dengan No.Kontrak ".$param['nokontrak']." telah dirilis. Berikut detail dari Kontrak Penjualan Tersebut : <br>
                               <br>
                               <table border=0 cellspacing=0 valign=top>
                                    <tr>
                                        <td>Pembeli</td>
                                        <td> : </td>
                                        <td>".$b['namacustomer']."</td>
                                    </tr>

                                    <tr>
                                        <td>NPWP</td>
                                        <td> : </td>
                                        <td>".$b['npwp']."</td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Barang</td>
                                        <td> : </td>
                                        <td>".$br['namabarang']."</td>
                                    </tr>
                                    <tr>
                                        <td>Kuantitas</td>
                                        <td> : </td>
                                        <td>".$bar['kuantitaskontrak']."</td>             
                                    </tr>

                                    <tr>
                                        <td>Kualitas</td>
                                        <td> : </td>
                                        <td>
                                            ".$ffaData."<br>
                                            ".$dobiData."<br>
                                            ".$mdaniData."<br>
                                            ".$moistData."<br>
                                            ".$dirtData."<br>
                                            ".$gradingData."<br>
                                        </td>             
                                    </tr>

                                    <tr>
                                        <td>Waktu Penyerahan</td>
                                        <td> : </td>
                                        <td>".$kettgl."</td>             
                                    </tr>
                                </table></td></tr>
                               <br>
                               Regards,<br>
                               ".$namaorganisasi.".
                             </body>
                            </head>
                         </html>";
                 $kirim = kirimEmail($to, '', $subject, $body);
			}
			*/
			
            break;
			
		case'gocarinorefrensi':
			$textnoref = checkPostGet('textnoref','');
			$tab="";
			
			$tab.="<table class=sortable cellspacing=1 border=0>
				<thead>
					<tr class=rowheader>
					<td align=center>No.</td>
					<td align=center>".$_SESSION['lang']['noreferensi']."</td>
					<td align=center>".$_SESSION['lang']['unit']."</td>
				</tr>
				</thead>
				<tbody>";
				
			$str="select * from ".$dbname.".pmn_scr where notransaksi like '%".$textnoref."%' and flag='0' and status='1'";
			$res=fetchData($str);
			$no=0;
			foreach($res as $key=>$val)
			{
				$no++;
				
				$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$val['kodeorg']."'");
				
				$tab.=" <tr class=rowcontent style='cursor:pointer' onclick=\"fillnorefrensi('".$val['notransaksi']."','".$optPt[$val['kodeorg']]."','".$val['buyer']."','".$val['berikat']."','".$val['komoditi']."','".$val['kuantitas']."','".$val['harga']."','".$val['ppn']."','".tanggalnormal($val['paymentdate'])."','".$val['bayarke']."','".$val['kualitas1']."','".$val['kualitas2']."','".$val['kualitas3']."','".$val['kualitas4']."')\">
					<td>".$no."</td>
					<td>".$val['notransaksi']."</td>
					<td>".$val['kodeorg']."</td>
				</tr>";
			}
				
			$tab.="</tbody>";
			
			echo $tab;
		break;
		
		
		########################################################################################################################################
		########################################################################################################################################
		
		case 'showupload':
		$tab="";
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>No. Kontrak</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>".$nopp."</label>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Filename</td>
					<td align='center'>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
	break;
		
	
		
	// case 'submitfile':

		// $tgl = date("YmdHis");
		// // exit("error : ".$tgl);
		// $data = $_POST;
		
		// if($data['fileupload']!='')
		// {
			// if($_FILES['file']['error']==0)
			// {
				// $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				// $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				// $filename = $newfilename."_".$tgl."".$filetype;
				// $file_tmpname = $_FILES['file']['tmp_name'];		
				
				// if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
				// {
					// if($_FILES['file']['size'] <= 250000)
					// {
					
						// $str = "insert into ".$dbname.".listfileupload (id,notransaksi, namafile, formaticon, status, createdby, createdtime) values ('','".$data['rnopp']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						// try
						// {
							// $owlPDO->exec($str);
							// move_uploaded_file($file_tmpname,"fileupload/kontrakjual/$filename");
						// }
						// catch(PDOException $e)
						// {
							// echo " Gagal," . addslashes($e->getMessage());
						// }
					// }
					// else
					// {
						// exit("warning : Ukuran file upload maksimal 250kb");
					// }
				// }else{
					// exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				// }
			// }
		// }
	// break;
	
		
		
	// case 'loadfiles':
		// $no = 0;
		// $tab = "";
		// $str="select * from ".$dbname.".pmn_kontrakjualv2 where nokontrak = '".$nopp."'";
		// $resv=fetchData($str);
		// foreach($resv as $bar => $barv){
			// $close = $barv['close'];	
		// }
		
		// $str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopp."' and status='1'";
		// $res=fetchData($str);
		// if(empty($res))
		// {
			// $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		// }
		// else
		// {
			// foreach($res as $key=>$val)
			// {
				// $no++;
				// $tab.="<tr id='ppDetailTable' class=rowcontent>
					// <td style='text-align:center'>".$no."</td>";
					
				// if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					// </td>";
				// }
				// elseif($val['formaticon']=='.png')
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					// </td>";
				// }
				// elseif($val['formaticon']=='.pdf')
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					// </td>";
				// }
				// elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					// </td>";
				// }
				// elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					// </td>";
				// }
				// else
				// {
					// $tab.="<td style='text-align:center'>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					// </td>";
				// }
				
				// $tab.="<td style='text-align:left'>".$val['namafile']."</td>
					// <td align=center>
						// <a href='fileupload/kontrakjual/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				// if($close==0){
					// $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
				// }
				// $tab."	</td>
				// </tr>";
			// }	
		// }
		// echo $tab;
	// break;	
		
		
	// case 'deletefile':
		// $str="delete from ".$dbname.".listfileupload where notransaksi='".$nopp."' and namafile='".$namafile."'";
		// try
		// {
			// $owlPDO->exec($str);
			// $path = "fileupload/kontrakjual/".$namafile;
			// unlink($path);
		// }
		// catch(PDOException $e)
		// {
			// echo " Gagal," . addslashes($e->getMessage());
		// }
	// break;	
		
		
	default:
	break;
	}

?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/terbilang.php');
require('lib/fpdf.php');
// require('lib/htmlparser.inc');
// require('lib/htmltofpdf.php');

$method      = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$nokontrak   = checkPostGet('nokontrak','');
$tgl         = tanggalSystemn(checkPostGet('tgl',''));
if($tglsch!=''){
	$tglsch = tanggalSystemn(checkPostGet('tglsch',''));
}

$kodeunit           = checkPostGet('kodeunit','');
$telahterimadari    = checkPostGet('telahterimadari','');
$jumlah             = checkPostGet('jumlah','');
$jumlah             = str_replace(',','',$jumlah);
$kodept             = checkPostGet('kodept','');
$keterangan         = checkPostGet('keterangan','');
$namafile           = checkPostGet('namafile','');
$telahterimadarisch = checkPostGet('telahterimadarisch','');
$notransaksisch     = checkPostGet('notransaksisch','');
$keterangansch      = checkPostGet('keterangansch','');
$kota               = checkPostGet('kota','');
$ttd                = checkPostGet('ttd','');
$nmorg              = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$kdpt               = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$jab                = getPostingJabatan('kwitansi');
$path               = "fileupload/keu_kwitansi/";

$str="select a.*,b.namakaryawan from ".$dbname.".pmn_5ttd a left join ".$dbname.".datakaryawan b on a.nama=b.karyawanid";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmkar[$bar['nama']]=$bar['namakaryawan'];
	$jabatankar[$bar['nama']]=$bar['jabatan'];
}

$nmbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$nmcustomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');

$optunit=$optnokontrak=$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
switch ($method) {	
	
	case'getpt':
		
		$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $kodeunit . "'";
		// exit("Error:$str");
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
			if($kodept==$bar['induk']){
				$optpt.="<option selected value=".$bar['induk'].">".$bar['induk']." - ".$nmorg[$bar['induk']]."</option>";
			}else{
				$optpt.="<option value=".$bar['induk'].">".$bar['induk']." - ".$nmorg[$bar['induk']]."</option>";
			}
			
			$kodept=$bar['induk'];
		}
		
		$str = "select * from ".$dbname.".pmn_kontrakjual where posting=1 and kodept='".$kodept."'";
		// exit("Error:".$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($nokontrak==$bar['nokontrak']){
				$optnokontrak.="<option selected value='".$bar['nokontrak']."'>".$bar['nokontrak']." - ".$nmbarang[$bar['kodebarang']]." - ".$nmcustomer[$bar['koderekanan']]."</option>";

			}else{
				$optnokontrak.="<option value='".$bar['nokontrak']."'>".$bar['nokontrak']." - ".$nmbarang[$bar['kodebarang']]." - ".$nmcustomer[$bar['koderekanan']]."</option>";

			}
		}
		
		echo $optpt."####".$optnokontrak;
	break;
	
	case'getterima':
		$str = "select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		// exit("Error:".$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namacustomer=$nmcustomer[$bar['koderekanan']];
			$namabarang=$nmbarang[$bar['kodebarang']];
			$koderekanan = $bar['koderekanan'];
			$kdtermin = $bar['kdtermin'];
			$rpkontrak = $bar['hargasatuan']*$bar['kuantitaskontrak'];
		
		#= cek apakah sudah ada kuitansi untuk kontrak ini / belum
		$str = "select count(*) as jumlah, sum(rupiah) as rupiah from ".$dbname.".keu_kwitansi where nokontrak='".$nokontrak."' and tanggal < '".$tgl."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahkwitansi=$bar['jumlah'];
			$jumlahlalu=$bar['rupiah'];
		
			$jumlahsekarang = $jumlahlalu+$jumlah;
		
		
		if($jumlahkwitansi=='0'){
			$keterangan=" Uang muka atas penjualan ".$namabarang." sesuai dengan kontrak No. ".$nokontrak." ";
		}else{
			$keterangan=" Pelunasan atas penjualan ".$namabarang." sesuai dengan kontrak No. ".$nokontrak." ";
		}
		if($kdtermin=='100'){
			$keterangan=" Pembayaran atas penjualan ".$namabarang." sesuai dengan kontrak No. ".$nokontrak." ";
		}
		if($jumlahsekarang>$rpkontrak and $koderekanan=='TSI'){
			$keterangan=" Pelunasan atas penjualan ".$namabarang." sesuai dengan kontrak No. ".$nokontrak." ";
		}
		
		echo $namacustomer."####".$keterangan;
			// exit("Error:A");
		
	break;



	 case'view':
		$str = "select * from " . $dbname . ".keu_kwitansi where nokwitansi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		
		$tab= "<table border=0 width=100%>
            <tr>
				<td width=30px><font face='goudy old style'>" . $_SESSION['lang']['nourut'] . "</font></td>
				<td width=100px><u><b>" . $bar['nokwitansi'] . "</b></u></td>
			</tr>
			<tr>
				<td align=left colspan=2 width=50px><font face='goudy old style'>Telah terima dari</font></td>
				<td colspan=3><b>" . $bar['telahterimadari'] . "</b></td>
			</tr>
			<tr>
				<td align=left colspan=2><font face='goudy old style'>Uang sejumlah</font></td>
				<td colspan=3><b><i>" . terbilang($bar['nilai_rupiah'],3) . "</i></b></td>
			</tr>
			<tr>
				<td align=left colspan=2><font face='goudy old style'>Untuk pembayaran</font></td>
				<td colspan=3><font size='2'>" . $bar['keterangan'] . "</font></td>
			</tr>
			
			<tr height=10px></tr>
			<tr>
				<td></td><td></td><td></td><td></td>
				<td width=300px align=center><b>" . $bar['kodeorg'] . ", " . tanggalnormal($bar['tanggal']) . "</b></td>
			</tr>
			<td colspan=2 align=center><b>Rp. " . number_format($bar['nilai_rupiah'],2) . "</b></td>
			<tr>
				
			</tr>
		";
        $tab.="</table>";
		
        echo $tab;


	break;
				

	case 'insert':
	
	
		/*
		- Menu Kuitansi
		(proses) 1. Penomoran :
		001/BPJ/08/2019
		norut/PT/Bulan/Tahun
		reset : perpt/pertahun
		*/
	
		//bentuk nomor transaksi
		$tahun=explode('-',$tgl);
		$bulan=$tahun[1];
		$tahun=$tahun[0];
        
		// cek di kontrak tipe pembayaran 100% atau bukan
		$str = "select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		$res = fetchData($str);
		$kdtermin = $res[0]['kdtermin'];
		$koderekanan = $res[0]['koderekanan'];
		$rpkontrak = $res[0]['hargasatuan']*$res[0]['kuantitaskontrak'];
		
        $str="select nokwitansi from ".$dbname.".keu_kwitansi where tanggal like '".$tahun."%' and kodept='".$kodept."' order by nokwitansi desc limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
			$datanokwitansi=$bar['nokwitansi'];
			
		if($datanokwitansi==''){
			$nourut=1;
		}else{
			$expldatanokwitansi=explode('/',$datanokwitansi);
			$nourut=$expldatanokwitansi[0]+1;
		}
		
		#= cek apakah sudah ada kwitansi sebelumnya dengan nomor kontrak tersebut
		$str="select count(*) as jumlah,sum(rupiah) as rupiah from ".$dbname.".keu_kwitansi where nokontrak='".$nokontrak."' and tanggal < '".$tgl."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
		$jumlahlalu=$bar['rupiah'];
		$jumlahsekarang = $jumlahlalu+$jumlah;
		
		
		if($bar['jumlah']>0){
			$tipekwitansi='PL';
		}else{
			$tipekwitansi='UM';
		}
		if($kdtermin=='100'){
			$tipekwitansi='PBY';
		}
		if($jumlahsekarang>$rpkontrak and $koderekanan=='TSI'){
			$tipekwitansi='PLPBY';
		}
		$explnokontrak=explode('/',$nokontrak);
				
		
			// 067/IMT/PLPK/SDK/XI/19
			// 040/BPJ/XI/2019
		
		$notransaksi=addZero($nourut,3)."/".$explnokontrak[1]."/".$tipekwitansi.$explnokontrak[2]."/".$kodept."/".romawi($bulan)."/".substr($tahun,2,2);
		// exit("Error:".$notransaksi);
	

		// $str="select count(*) as cek from ".$dbname.".keu_kwitansi where 
				// tanggal = '".$tgl."' and kodeorg='".$kodeunit."' and kodept='".$kodept."' and telahterimadari='".$telahterimadari."' 
				// and nilai_rupiah='".$jumlah."' and keterangan='".$keterangan."'";
        // $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bar=$res->fetch();
        // if(intval($bar['cek'])>0){
          // exit('Error : Transaksi sudah pernah di input.');
        // }
		
		$keterangan=trim($keterangan);
		$str = "insert into ".$dbname.".keu_kwitansi 
				(`nokwitansi`, `nokontrak`, `kodeunit`, `kodept`, `rupiah`,
				`keterangan`, `tanggal`, `telahterimadari`,`kota`,`ttd`,
				`createby`, `createtime`, `updateby`, `updatetime`, `status`)
				values 
				('".$notransaksi."','".$nokontrak."','".$kodeunit."','".$kodept."','".$jumlah."',
				'".$keterangan."','".$tgl."','".$telahterimadari."','".$kota."','".$ttd."',
				'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','','0')";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
		
	break;

    case 'update':
		$str = "update ".$dbname.".keu_kwitansi set rupiah='".$jumlah."', keterangan='".$keterangan."', 
				telahterimadari='".$telahterimadari."',  ttd='".$ttd."', kota='".$kota."', 
				updateby='".$_SESSION['standard']['userid']."',updatetime='".date('Y-m-d H:i')."' where nokwitansi = '".$notransaksi."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'delete':
		$str = "delete from ".$dbname.".keu_kwitansi where nokwitansi = '".$notransaksi."'";
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

	case'posting':
        $str = "update " . $dbname . ".keu_kwitansi set status='1' where nokwitansi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case'unposting':
		//cek tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$pabrik."' and periode ='".substr($tgl,0,7)."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
			$tutup=$bar['tutupbuku'];
		if($tutup==1){
			exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".substr($tgl,0,7)." unit ".$pabrik." sudah di tutup.");
		} else {
			$str = "update " . $dbname . ".keu_kwitansi set status='0' where nokwitansi = '" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
	break;
	
    case'loaddata':
	
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		$where=$where2="";
		if($tglsch!=''){ 
			$where.=" and tanggal LIKE  '%".$tglsch."%'";
		}
		if($telahterimadarisch!=''){ 
			$where.=" and telahterimadari LIKE  '%".$telahterimadarisch."%'";
		}
		if($notransaksisch!=''){ 
			$where.=" and nokwitansi LIKE  '%".$notransaksisch."%'";
		}
		if($keterangansch!=''){ 
			$where.=" and keterangan LIKE  '%".$keterangansch."%'";
		}
		
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			// $where2.=" and (kodeorg in (select kodeorganisasi from ".$dbname.".user_orgdetail where namauser='".$_SESSION['standard']['username']."') or kodeorg = '".$_SESSION['empl']['lokasitugas']."') and updateby='".$_SESSION['standard']['userid']."' ";
		// }else{
			// $where2.=" and kodeorg = '".$_SESSION['empl']['lokasitugas']."' and updateby='".$_SESSION['standard']['userid']."'";
		// }
		/*
			$where2.=" and (kodeunit in (select kodeorganisasi from ".$dbname.".user_orgdetail where 
			namauser='".$_SESSION['standard']['username']."') or kodeunit = '".$_SESSION['empl']['lokasitugas']."') 
			and updateby='".$_SESSION['standard']['userid']."' ";
		*/	
			$where2.=" and (kodeunit in (select kodeorganisasi from ".$dbname.".user_orgdetail where 
			namauser='".$_SESSION['standard']['username']."') or kodeunit = '".$_SESSION['empl']['lokasitugas']."') ";
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".keu_kwitansi
				where 0=0 ".$where." ".$where2.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }
		
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['notransaksi']."</th>
				<th align=center width=50px>".$_SESSION['lang']['unit']."</th>
				<th align=center>".$_SESSION['lang']['pt']."</th>
				<th align=center width=75px>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['telahterimadari']."</th>
				<th align=center>".$_SESSION['lang']['keterangan']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				
				<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan');
		
		$str = "select * from ".$dbname.".keu_kwitansi
				where 0=0 ".$where." ".$where2." order by tanggal desc, nokwitansi desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$no++;
			$tab.="<tr class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['nokwitansi']."</td>";
            $tab.="<td>".$bar['kodeunit']."</td>";
            $tab.="<td>".$bar['kodept']."</td>";
            $tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
            $tab.="<td>".$bar['telahterimadari']."</td>";
            $tab.="<td>".$bar['keterangan']."</td>";
            $tab.="<td align=right>".number_format($bar['rupiah'],2)."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
			
			if ($bar['status'] == 0) {
				$tab.="<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' 
							onclick=\"edit('".$bar['nokwitansi']."','".$bar['nokontrak']."','".$bar['kodeunit']."',
							'".$bar['kodept']."','".number_format($bar['rupiah'])."','".$bar['keterangan']."',
							'".tanggalnormal($bar['tanggal'])."','".$bar['telahterimadari']."','".$bar['kota']."','".$bar['ttd']."');\"></td>";
				$tab.="<td align=center>
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
						onclick=\"del('" . $bar['nokwitansi'] . "');\" ></td>";
				$tab.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $bar['nokwitansi'] . "','" . $no . "');\" ></td>";
				
				// $tab.="<td align=center><img src=images/pdf_gray.jpg class=resicon  caption='PDF' onclick=\"viewpdf('x');\"></td>";
				$tab.="<td align=center><img src=images/pdf.jpg class=resicon  caption='PDF' onclick=\"viewpdf('".$bar['nokwitansi']."');\"></td>";
			}else{
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['nokwitansi'] . "','" . $no . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td></td><td></td>";
				$tab.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
				
				$tab.="<td align=center><img src=images/pdf.jpg class=resicon  caption='PDF' onclick=\"viewpdf('".$bar['nokwitansi']."');\"></td>";
			}
			
			$tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$bar['nokwitansi']."')\" src='images/upload-2-xxl.png'/></td>";
			
			// $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon  caption='Preview' onclick=\"view('".$bar['nokwitansi']."');\"></td>";

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

		$tab.="<tr><td colspan=14 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;
	case 'showupload':
		$tab="";
		$tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['nomor']."</td>
				<td>:</td>
				<td>
					<label id='noupload' style='display:none'>".$notransaksi."</label>
					<label style='font-weight:bold'>".$notransaksi."</label>
				</td>
			</tr>";

		$tab.="<tr><td colspan=4><hr></td></tr>
				<tr>
					<td>Filename</td>
					<td>:</td>
					<td>
						<input type='file' name='upload' id='upload' >
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
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
			
		echo $tab;
	break;
	
	case 'submitfile':
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;

		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $notransaksi."_".$his."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 250000){
						$str = "insert into ".$dbname.".listfile_keu_kwitansi values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;
	case 'loadfiles':
		$no = 0;
		$tab = "";	
		$str="select * from ".$dbname.".keu_kwitansi where nokwitansi = '".$notransaksi."'";
		$res=fetchData($str);
		$posting=$res[0]['status'];
		
		$str="select * from ".$dbname.".listfile_keu_kwitansi where nomor = '".$notransaksi."' and status='1'";
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}elseif($val['formaticon']=='.png'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}elseif($val['formaticon']=='.pdf'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}else{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['nomor']."','".$val['namafile']."');\" >";
				}else{
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['nomor']."','".$val['namafile']."');\" >";
						
					}
				}
				
				$tab."	</td>
				</tr>";
			}	
		}
		
		echo $tab;
	break;
	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
		
		echo $tab;
	break;
	
	case 'deletefile':
		$str="delete from ".$dbname.".listfile_keu_kwitansi where nomor='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'viewlistfile':
		$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
		echo $tab;
	break;
	case 'deletefileall':
		$str="select * from ".$dbname.".listfile_keu_kwitansi where nomor='".$notransaksi."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$pathx = $path.$bar['namafile'];
			unlink($pathx);
		}
		
		$str="delete from ".$dbname.".listfile_keu_kwitansi where nomor='".$notransaksi."'";
		try{
			$owlPDO->exec($str);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case 'viewpdflama':
		$str = "select * from " . $dbname . ".keu_kwitansi where nokwitansi='" . $notransaksi . "'";
        $res = fetchData($str);
        $no = $res[0]['nokwitansi'];
        $nokontrak = $res[0]['nokontrak'];
        $telahterimadari = $res[0]['telahterimadari'];
        $keterangan = $res[0]['keterangan'];
        $tanggal = tglnmbln($res[0]['tanggal'],'I','long');
        $rupiah = $res[0]['rupiah'];
        $kodept = $res[0]['kodept'];
		$kota = $res[0]['kota'];
		$ttd = $res[0]['ttd'];
		
		
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";	
        $res = fetchData($str);
		$rekening = $res[0]['rekening'];
		
		$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $rekening . "'";
        $res = fetchData($str);
		$atasnama= $res[0]['atasnama'];
		$rekening= $res[0]['rekening'];
		$namabank= $res[0]['namabank'];
		$cabang= $res[0]['cabang'];
		
	
		
		
		$str = "select * from " . $dbname . ".keu_5daftarbank where kodebank='" . $namabank . "'";
		// echo $str;
        $res = fetchData($str);
		$namabank= $res[0]['namabank'];
		
		class PDF extends FPDF{}
		
		$pdf=new FPDF('L','mm',array(200,550));
		$pdf->SetAutoPageBreak(false);
		$pdf->AddPage();
		
		
		$path="images/kwitansi.jpg";
		$pdf->Image($path,0,0,550,200);
		
		
		$font=25;
		$height=12;
		$pdf->SetFont('Arial','B',$font);
		$pdf->SetTextColor(0,0,0);
		
		// $pdf->Cell(7,7,'No',0,0,'L');
		// $pdf->SetFont('Arial','U',10);
		$pdf->SetY(13);
		$pdf->SetX(135);
		$pdf->Cell(100,$height,$no,0,1,'L');
		
		// $pdf->SetDrawColor(135,135,135);
		// $pdf->SetLineWidth(0.1);
		// $pdf->SetX(12);
		// $pdf->SetFont('Arial','',10);
		// $pdf->Cell(29,5,"Telah terima dari",0,0,'L');
		// $pdf->Cell(117,5,$telahterima,'B',1,'L');
		
		$pdf->Ln(8);
		$pdf->SetX(220);
		$pdf->Cell(200,$height,$telahterimadari,0,1,'L');
		
		
		
		$pdf->Ln(6);
		$pdf->SetX(210);
		$pdf->MultiCell(330,$height,ucwords("# ".terbilang($rupiah,'','')." Rupiah #"),0,'L');
		
		$pdf->SetY(74);
		$pdf->SetX(220);
		$pdf->MultiCell(300,$height+3,$keterangan,0,'L');

		$pdf->SetXY(355,125);
		$pdf->Cell(200,$height,$kota.', '.$tanggal,0,1,'L');
		
		$pdf->SetXY(355,138);
		$pdf->Cell(200,$height,$nmorg[$kodept],0,1,'L');
		
		$pdf->SetXY(355,185);
		$pdf->Cell(200,$height,ucwords(strtolower($nmkar[$ttd])).' / '.ucwords(strtolower($jabatankar[$ttd])),0,1,'L');
		
		$pdf->SetXY(150,168);
		$pdf->Cell(200,$height,"# ".number_format($rupiah)." #",0,1,'L');
		
		
		
		#= rekening kiri
		$pdf->SetXY(110,120);
		$pdf->SetFont('Arial','BU',$font);
		$pdf->Cell(100,$height,'Transfer Pembayaran ke :',0,1,'L');
		
		$pdf->SetXY(110,130);
		$pdf->SetFont('Arial','B',$font);
		$pdf->Cell(100,$height,$atasnama,0,1,'L');
		
		
		$pdf->SetXY(110,140);
		$pdf->SetFont('Arial','B',$font);
		$pdf->Cell(100,$height,$namabank.' Cab. '.$cabang,0,1,'L');
		
		$pdf->SetXY(110,150);
		$pdf->SetFont('Arial','B',$font);
		$pdf->Cell(100,$height,'A/C No : '.$rekening,0,1,'L');
		// $pdf->Cell(200,$height,$atasnama,0,1,'L');
		
		
		$pdf->Output();
		
	break;

	case 'viewpdf':
		$str = "select * from " . $dbname . ".keu_kwitansi where nokwitansi='" . $notransaksi . "'";
        $res = fetchData($str);
        $no = $res[0]['nokwitansi'];
        $nokontrak = $res[0]['nokontrak'];
        $telahterimadari = $res[0]['telahterimadari'];
        $keterangan = $res[0]['keterangan'];
        $tanggal = tglnmbln($res[0]['tanggal'],'I','long');
        $rupiah = $res[0]['rupiah'];
        $kodept = $res[0]['kodept'];
		$kota = $res[0]['kota'];
		$ttd = $res[0]['ttd'];
		
		
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";	
        $res = fetchData($str);
		$rekening = $res[0]['rekening'];
	
		$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $rekening . "'";
        $res = fetchData($str);
		$atasnama= $res[0]['atasnama'];
		$rekening= $res[0]['rekening'];
		$namabank= $res[0]['namabank'];
		$cabang= $res[0]['cabang'];
		
	
		
		
		$str = "select * from " . $dbname . ".keu_5daftarbank where kodebank='" . $namabank . "'";
		// echo $str;
        $res = fetchData($str);
		$namabank= $res[0]['namabank'];
		
		class PDF extends FPDF{}
		
		$pdf=new FPDF('P','mm','A4');
		$pdf->SetAutoPageBreak(false);
		$pdf->AddPage();
			
		
		$height=10;
		$pdf->SetTextColor(0,0,0);
		
		$pdf->SetFont('Times','B','9.5');
		$pdf->SetY(35);
		$pdf->SetX(30);
		$pdf->Cell(100,$height,$nmorg[$kodept],0,1,'L');

		$pdf->SetFont('Times','B','14');
		$pdf->SetY(40);
		$pdf->SetX(95);
		$pdf->Cell(100,$height,"KWITANSI",0,1,'L');

		$pdf->SetFont('Times','B','10');
		$pdf->SetY(60);
		$pdf->SetX(30);
		$pdf->Cell(100,$height,"NO.",0,1,'L');
		
		$pdf->SetFont('Times','U','10');
		$pdf->SetY(60);
		$pdf->SetX(50);
		$pdf->Cell(100,$height,$no,0,1,'L');
				
		$pdf->SetFont('Times','B','10');
		$pdf->SetY(70);
		$pdf->SetX(30);
		$pdf->Cell(100,$height,"Telah terima dari",0,1,'L');

		$pdf->SetFont('Times','U','10');
		$pdf->SetY(70);
		$pdf->SetX(70);
		$pdf->Cell(100,$height,$telahterimadari,0,1,'L');

		$pdf->SetFont('Times','B','10');
		$pdf->SetY(80);
		$pdf->SetX(30);
		$pdf->Cell(100,$height,"Uang sejumlah",0,1,'L');

		$pdf->SetFont('Times','U','10');
		$pdf->SetY(80);
		$pdf->SetX(70);
		$pdf->MultiCell(100,$height,ucwords("# ".terbilang($rupiah,'','')." Rupiah #"),0,'L');
		$tg=100;
		if (strlen(terbilang($rupiah,'',''))>103) {
			$tg=110;
		}
		$pdf->SetFont('Times','B','10');
		$pdf->SetY($tg);
		$pdf->SetX(30);
		$pdf->Cell(100,$height,"Untuk pembayaran",0,1,'L');

		$pdf->SetFont('Times','U','10');
		$pdf->SetY($tg);
		$pdf->SetX(70);
		$pdf->MultiCell(100,$height,$keterangan,0,'L');

		$pdf->SetFont('Times','U','10');
		$pdf->SetY(140);
		$pdf->SetX(40);
		$pdf->MultiCell(100,$height,"# ".number_format($rupiah).",-#",0,'L');

		$pdf->SetFont('Times','','10');
		$pdf->SetY(140);
		$pdf->SetX(30);
		$pdf->MultiCell(100,$height,"Rp.",0,'L');

		$pdf->SetFont('Times','U','10');
		$pdf->SetY(140);
		$pdf->SetX(120);
		$pdf->Cell(100,$height,ucfirst(strtolower($kota)).", ".$tanggal,0,'L');


		$nmPtS=explode(".",$nmorg[$kodept]);
		setIt($nmPtS[1],'');
		// $pdf->Cell(100,$high,strtoupper($nmPtS[0]).". ".ucwords(strtolower($nmPtS[1])),'',0,'L');
		$pdf->SetFont('Times','','10');
		$pdf->SetY(145);
		$pdf->SetX(120);
		$pdf->Cell(100,$height,strtoupper($nmPtS[0]).". ".ucwords(strtolower($nmPtS[1])),0,'L');

		$pdf->SetFont('Times','','10');
		$pdf->SetY(180);
		$pdf->SetX(120);
		$pdf->Cell(100,$height,"(".ucwords(strtolower($nmkar[$ttd])).' / '.ucwords(strtolower($jabatankar[$ttd])).")",0,'L');
		
		
		
		ob_clean();
		$pdf->Output();
		
		break;


	case'changeperiode':
		$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
              $str = "select * FROM ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and tutupbuku=0 order by periode asc
                LIMIT 1 ";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $optPeriode.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
                }
                echo $optPeriode;
	break;
}
?>

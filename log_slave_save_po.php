<?php
require_once('master_validation.php');
require_once('config/connection.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$supplier_id=	isset($_POST['supplier_id'])? $_POST['supplier_id']: '';
//$proses=		isset($_POST['proses'])? $_POST['proses']: '';
$nopo=			isset($_POST['nopo'])? $_POST['nopo']: '';
$tgl_po=		isset($_POST['tglpo'])? tanggalsystem($_POST['tglpo']): '';
$sub_total=		isset($_POST['subtot'])? $_POST['subtot']: '';
$disc=			isset($_POST['diskon'])? $_POST['diskon']: '';
$nilai_dis=		isset($_POST['nildiskon'])? $_POST['nildiskon']: '';
$pbbkb=			isset($_POST['pbbkb'])? $_POST['pbbkb']: '';
$npph=			isset($_POST['pph'])? $_POST['pph']: '';
$nppn=			isset($_POST['ppn'])? $_POST['ppn']: '';
$chkppn=		isset($_POST['chkppn'])? $_POST['chkppn']: '';
$tanggl_kirim=	isset($_POST['tgl_krm'])? tanggalsystemd($_POST['tgl_krm']): '';
$lokasi_krm=	isset($_POST['lok_kirim'])? $_POST['lok_kirim']: '';
$cr_pembayaran=	isset($_POST['cara_pembayarn'])? $_POST['cara_pembayarn']: '';
$nilai_po=		isset($_POST['grand_total'])? $_POST['grand_total']: '';
$purchaser=		isset($_POST['purchser_id'])? $_POST['purchser_id']: '';
$lokasi_kirim=	isset($_POST['lokasi_krm'])? $_POST['lokasi_krm']: '';
$persetujuan=	isset($_POST['id_user'])? $_POST['id_user']: '';
$comment=		isset($_POST['cm_hasil'])? $_POST['cm_hasil']: '';
$jmlh_realisasi=isset($_POST['jmlh_realisasi'])? $_POST['jmlh_realisasi']: '';
$jmlh_diminta=	isset($_POST['jmlh_diminta'])? $_POST['jmlh_diminta']: '';
$jnopp=			isset($_POST['jnopp'])? $_POST['jnopp']: '';
$jkdbrg=		isset($_POST['jkdbrg'])? $_POST['jkdbrg']: '';
$ketUraian=		isset($_POST['ketUraian'])? $_POST['ketUraian']: '';
$mtUang=		isset($_POST['mtUang'])? $_POST['mtUang']: '';
$Kurs=			isset($_POST['Kurs'])? $_POST['Kurs']: '';
$nmSupplier=	isset($_POST['nmSupplier'])? $_POST['nmSupplier']: '';
$ttd2=			isset($_POST['ttd2'])? $_POST['ttd2']: '';
$ongkirim=		isset($_POST['ongkirim'])? $_POST['ongkirim']: 0;
$stat=			isset($_POST['stat'])? $_POST['stat']: '';
$batal=			isset($_POST['batal'])? $_POST['batal']: '';
if($tanggl_kirim=='----:00' or $tanggl_kirim=='--:00') $tanggl_kirim = "00000000";

/*buka tambahan*/
$rek=checkPostGet('rek','');

$tglSkrng=date("Y-m-d");
$npwporg=checkPostGet('npwporg','');
$nodph=checkPostGet('nodph','');
$proses=checkPostGet('proses','');
$no_po=checkPostGet('no_po','');
$kode_pt=checkPostGet('kode_pt','');
$addcost=checkPostGet('addcost','');
$fileupload = checkPostGet('fileupload', '');
$kept=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$satbrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$doc=checkPostGet('doc', '');
$dir='fileupload/pomemo';


$suprph=checkPostGet('suprph', '');
$delivtime=checkPostGet('delivtime', '');
/*tutup tambahan*/

switch($proses)
{

/*buka tambahan*/

	case'getnpwp':
		$exnopo=explode('/', $nopo);
		$kdorg=$exnopo[5];
		$str="select npwp from ".$dbname.".setup_org_npwp where kodeorg='".$kdorg."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if ($npwporg==$bar['npwp']){
				$optnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
			}else{
				$optnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
			}
			
		}
		echo $optnpwp;
						
	break;	

	case'getsuprph':
		$optsuprph="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select supplierid from ".$dbname.".log_perintaanhargaht where nomor='".$nodph."' and po=0 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsuprph.="<option value='".$bar['supplierid']."'>".$nmsup[$bar['supplierid']]."</option>";
		}
		echo $optsuprph;
						
	break;

	case'deletefile':
		$str="update ".$dbname.".log_poht set filememo='' where nopo='".$nopo."' ";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}	
	break;

	case 'lihatfile':
		$potong=explode('.',$doc);
		if($potong[1]=='pdf'){
			echo"<embed src=\"".$doc."\" width=780px height=370px>";
		} else {
			echo"<img src=\"".$doc."\">";
		}
	break;
	
	case 'savefile':
		$fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
		$fileupload = $fileupload;
		if($fileupload=='.'){
			
		} else if($fileupload=='.jpg' || $fileupload=='.jpeg' || $fileupload=='.png' || $fileupload=='.pdf'){
			$filesize=$_FILES['fileup']['size'];
			if($filesize>=512000){
				exit("Warning : Besar ukuran file maksimal 512 Kb. ");
			}
			$path = $dir."/".basename($_FILES['fileup']['name']);
			if(move_uploaded_file($_FILES['fileup']['tmp_name'], $path)){
				$str="update ".$dbname.".log_poht set filememo='".$path."' where nopo='".$nopo."' ";
				try{$owlPDO->exec($str); }
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}	
			}
		} else {
			exit("Warning : File yang di-izinkan hanya JPG,JPEG,PNG,PDF");
		}
	break;

	case 'adddph':
			/*
			cek ke tabel dph, pengecekan :
			1) apakah no.dph tsb ada
			2) apakah no.dph tsb dibuat oleh user login	
			3) apakah no.dph sudah dilakuakn posting pemenang tender
			4) apakah nodph yang diinput sesuai dengan param pt yg dipilih dilist job
			 */
		
			#ambil nama pt
			#karna di log_dph tidak semua memakai kodept
			$explnodph=explode('/',$nodph);
			$unit=$explnodph[4];
			if(strlen($unit)>3){
				$pt=$kept[$unit];
			}else{
				$pt=$unit;
			}
			
			#buat ambil nopp, perubahan nomor po yang baru memakai kode unit pengganti MA untuk di HO
			$str="select nopp from ".$dbname.".log_permintaanhargadt where nomor='".$nodph."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$dtnopp=$bar['nopp'];
				$dtnopp=explode('/',$dtnopp);
				
			$str="select *,count(*) as jumdata from ".$dbname.".log_perintaanhargaht where 
					nomor='".$nodph."' and supplierid='".$suprph."' and purchaser='".$_SESSION['standard']['userid']."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();	
				
				
			//exit("Error".$dtnopp[4]);
			if($kode_pt!=$pt){
				exit("Warning:No.DPH tidak untuk PT. ".$kode_pt);
			}
		
			#buat diskon perhitungan untuk hargasatuan dan harganormal di podt 
			#untuk inset nanti
			$persendiskon=$bar['diskonpersen'];
			
			#karna di dt insert kurs maka ambil dari sini
			$matauang=$bar['matauang'];
				
			#buka bentuk nopo
			$nopo="/".date('Y')."/PO-HO/".$dtnopp[4]."/".$kode_pt;
			
			
            
			$ql="select `nopo` from ".$dbname.".`log_poht` where nopo like '%".$nopo."%' order by length(`nopo`) desc, `nopo` desc limit 0,1";
			
			$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qr->setFetchMode(PDO::FETCH_OBJ);
			$rp=$qr->fetch();
				
			@$eksplot=explode("/",$rp->nopo);
			@$awal=$eksplot[0];
			@$awal=intval($awal);
			@$cekbln=$eksplot[1];
			@$cekthn=$eksplot[2];
				
			
			$tgl=  date('Ymd');
			$bln = substr($tgl,4,2);
			$thn = substr($tgl,0,4);  	
				
			if($thn!=$cekthn){
				$awal=1;
			} else {
				$awal++;
			}
			
			$counter=$awal;
			if($awal<1000){
				$counter=addZero($awal,3);
			}
			
			$nopo=$counter."/".$bln."/".$thn."/PO-HO/".$dtnopp[4]."/".$kode_pt;
			#tutup bentuk nopo	
				
				
			// $diskonpersenpo=$bar['diskonpersen'];
			// $ppnpo=$bar['ppn'];
				
				
			#insert data ke poht
			$saveht="insert into ".$dbname.".log_poht (
					nopo,tanggal,tgledit,kodesupplier,subtotal,
					diskonpersen,nilaidiskon,ppn,nilaipo,syaratbayar,
					statuspo,idFranco,matauang,kurs,purchaser,
					kodeorg,lokalpusat,nodph) values 
					('".$nopo."','".$tglSkrng."','".$tglSkrng."','".$suprph."','0',
					'0','".$bar['nilaidiskon']."','0','0','".$bar['sisbayar2']."',
					'0','".$bar['id_franco']."','".$bar['matauang']."','".$bar['kurs']."','".$_SESSION['standard']['userid']."',
					'".$pt."','0','".$nodph."')";
					
					/*
					('".$nopo."','".$tglSkrng."','".$tglSkrng."','".$suprph."','0',
					'".$bar['diskonpersen']."','".$bar['nilaidiskon']."','".$bar['ppn']."','".$bar['nilaipermintaan']."','".$bar['sisbayar2']."',
					'0','".$bar['id_franco']."','".$bar['matauang']."','".$bar['kurs']."','".$_SESSION['standard']['userid']."',
					'".$pt."','0','".$nodph."')";
					*/
					
					/*
					('".$nopo."','".$tglSkrng."','".$tglSkrng."','".$suprph."','".$bar['subtotal']."',
					'".$bar['diskonpersen']."','".$bar['nilaidiskon']."','".$bar['ppn']."','".$bar['nilaipermintaan']."','".$bar['sisbayar2']."',
					'2','".$bar['id_franco']."','".$bar['matauang']."','".$bar['kurs']."','".$_SESSION['standard']['userid']."',
					'".$pt."','0','".$nodph."')";
					*/
					
			try {
				$owlPDO->exec($saveht);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}

			#insert dt
			$str="select * from ".$dbname.".log_permintaanhargadt where nomor='".$bar['nomor']."' and flag='1' and
					nourut in (select nourut from ".$dbname.".log_perintaanhargaht where nomor='".$bar['nomor']."' and supplierid='".$suprph."')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				@$tsubtotal+=$bar['harga']*$bar['jumlah'];
				
				if($persendiskon>0){
					$hargasatuan=$bar['harga']-($bar['harga']*$persendiskon/100);
					$harganormal=$bar['harga']-($bar['harga']*$persendiskon/100);
				}else{
					$hargasatuan=$bar['harga'];
					$harganormal=$bar['harga'];
				}
				@$tnilaipo+=$hargasatuan*$bar['jumlah'];
				$savedt="insert into ".$dbname.".log_podt(
						nopo,kodebarang,jumlahpesan,hargasatuan,harganormal,
						nopp,matauang,hargasbldiskon,satuan) values 
						('".$nopo."','".$bar['kodebarang']."','".$bar['jumlah']."','".$hargasatuan."','".$harganormal."',
						'".$bar['nopp']."','".$matauang."','".$bar['harga']."','".$satbrg[$bar['kodebarang']]."')";
				try {
					$owlPDO->exec($savedt);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>"; 
					die(); 
				}
				//$thargasatuan+=$bar['harga'];
			}
			
			
			
			
			#update flag karna sudah dibuat po 
			$update="update ".$dbname.".log_perintaanhargaht set po=1 where nomor='".$nodph."' and supplierid='".$suprph."' ";
			try {
				$owlPDO->exec($update);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
			
			#update nilai subtotal po
			$update="update ".$dbname.".log_poht set subtotal='".$tsubtotal."',nilaipo='".$tnilaipo."'
					where nopo='".$nopo."'";
			try {
				$owlPDO->exec($update);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
			
			
			#query po ht untuk melempar response text, fungsi edit po
			#pake fetch obj hasil copas, gk mau repot
			$str="select * from ".$dbname.".log_poht where nopo='".$nopo."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$bar=$res->fetch();
			echo "".$bar->nopo.",".tanggalnormal($bar->tanggal).",".$suprph.",".$bar->subtotal.",".$bar->diskonpersen.",".$bar->pbbkb.",".$bar->pph.",".$bar->chkppn.",".$bar->ppn.",".$bar->nilaipo.",".(isset($res->rekening)? $res->rekening: '').",".(isset($res2->npwp)? $res2->npwp: '').",".$bar->nilaidiskon.",".$bar->stat_release.",".tanggalnormal($bar->tanggalkirim).",".$bar->matauang.",".$bar->kurs.",".$bar->persetujuan1.",".$bar->idFranco.",".$bar->persetujuan2.",".$bar->addcost.",".$bar->deliverytime.",".$bar->npwp."";
			// exit("warning : ".$bar->nopo.",".tanggalnormal($bar->tanggal).",".$suprph.",".$bar->subtotal.",".$bar->diskonpersen.",".$bar->pbbkb.",".$bar->pph.",".$bar->chkppn.",".$bar->ppn.",".$bar->nilaipo.",".(isset($res->rekening)? $res->rekening: '').",".(isset($res2->npwp)? $res2->npwp: '').",".$bar->nilaidiskon.",".$bar->stat_release.",".tanggalnormal($bar->tanggalkirim).",".$bar->matauang.",".$bar->kurs.",".$bar->persetujuan1.",".$bar->idFranco.",".$bar->persetujuan2.",".$bar->addcost.",".$bar->deliverytime.",".$bar->npwp);
	break;
	
	case'cek_supplier_bank':
		$sql="select bank,rekening,an from ".$dbname.".log_5rekbank where supplierid='".$supplier_id."'";    
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		while($res=$query->fetch()){
			if($rek==$res['rekening']){
				$select="selected=selected";
			}
			else{
				$select="";
			}
		   @$optdata.="<option value='".$res['rekening']."' ".$select.">".$res['bank']." - ".$res['rekening']." - ".$res['an']."</option>";
		}
		echo $optdata;
	break;
/*tutup tambahan*/	
	
	
	case 'cek_supplier':
		$sql="select * from ".$dbname.".log_5supplier where supplierid='".$supplier_id."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$res=$query->fetch();

		// sub kelompok
		$optsubkelompok="";
		$namakelompoksup=makeOption($dbname,'log_5klsupplier','tipe,kode');
		$sql1="select * from ".$dbname.".log_5supkelompok where supplierid='".$supplier_id."'";
		$query1=$owlPDO->query($sql1) or die(print " Gagal: ".PDOException::getMessage());
		$query1->setFetchMode(PDO::FETCH_ASSOC);
		$res1=$query1->fetch();
		while($bar1=$res1->fetch())
		{
			$optsubkelompok .= "<option value='".$bar1['tipe']."'>".$namakelompoksup[$bar1['tipe']]."</option>";					
		}


		//echo $res['rekening'].",";
		// echo $res['npwp'];
		$data = [
			'npwp' => $res['npwp'],
			'subkelompok' => $optsubkelompok
		];
		echo json_encode($data);
	break;

	case 'insert':
		if(($supplier_id=='')||($nopo=='')||($disc=='')||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim=='')||($mtUang=='')||($npwporg=='')) {
			exit("warning: Please complete the form");
		}
		
		//cek matauang dan kurs
		if($mtUang!='IDR')
		{
			$Kurs=floatval($Kurs);
			$sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' order by daritanggal desc";
			$qGetKurs=$owlPDO->query($sGetKurs) or die(print " Gagal: ".PDOException::getMessage());
			$qGetKurs->setFetchMode(PDO::FETCH_ASSOC);
			$rGetKurs=$qGetKurs->fetch();
			if($Kurs=='0')
			{
			  exit("Error: Please provide curs corrensponding to currency, curs for ".$rGetKurs['kode']." :".$rGetKurs['kurs']);   
			}
		} else {
			$Kurs=1;
		}

		$awl=0;
		$i=1;
		foreach($_POST['kdbrg'] as $row =>$cntn) {
			$kdbrg=$cntn;
			$b=count($_POST['kdbrg']);
			$nopp=$_POST['nopp'][$row];
			$jmlh_pesan=$_POST['rjmlh_psn'][$row];
			$hrg_satuan=$_POST['rhrg_sat'][$row];
			$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
		   // $mat_uang=$_POST['rmat_uang'][$row];
			$satuan=$_POST['rsatuan_unit'][$row];
			$diskon=($hrg_sblmdiskon*$disc)/100;
			$hrg_diskon=$hrg_sblmdiskon-$diskon;

			$sqjmlh="select selisih,jlpesan,realisasi,purchaser from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
			$qujmlh=$owlPDO->query($sqjmlh) or die(print " Gagal: ".PDOException::getMessage());
			$qujmlh->setFetchMode(PDO::FETCH_ASSOC);
			$resjmlh=$qujmlh->fetch();
			$jmlh_pesan=$resjmlh['jlpesan']+$jmlh_pesan;
			if(($jmlh_pesan==''||$jmlh_pesan<=0)||($hrg_satuan==''||$hrg_satuan<=0))
			{
				echo "warning: Tolong lengkapi pengisian";
				exit();
			}
			if($purchaser!=$resjmlh['purchaser'])
			{
				$purchaser=$resjmlh['purchaser'];
			}

			if($resjmlh['realisasi']<$jmlh_pesan)
			{
				// echo "warning : \nTotal requested (".$jmlh_pesan.") to material code ".$kdbrg.".(".$jmlh_pesan.") =
				// \nVolum of previous request (".$resjmlh['jlpesan'].")\nVolum on current request (".$_POST['rjmlh_psn'][$row].")
				// \nLarger than approved (".$resjmlh['realisasi'].").";
				// exit();
				
				echo "warning : \nTotal permintaan (".$jmlh_pesan.") untuk kodebarang ".$kdbrg.".(".$jmlh_pesan.") =
				\n Jumlah permintaan sebelumnya (".$resjmlh['jlpesan'].")\n Jumlah permintaan sekarang (".$_POST['rjmlh_psn'][$row].")
				\n Lebih besar dari persetujuan (".$resjmlh['realisasi'].").";
				exit();
			}
		}
		$sKd="select kodeorg from ".$dbname.".log_prapoht where nopp='".$nopp."'";
		$qKd=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
		$qKd->setFetchMode(PDO::FETCH_ASSOC);
		$rKdorg=$qKd->fetch();

		$sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$res=owlBaris($query);
		if(intval($lokasi_kirim)) {
			$field="`idFranco`";
		} else {
			$field="`lokasipengiriman`";
		}
		$thisDate=date('Y-m-d');
		if($nilai_dis=='')
		{
			$nilai_dis=0;
		}
		$Kurs=intval($Kurs);
		if($ongkirim=='') $ongkirim=0;
		
		$strx="update ".$dbname.".log_poht set `kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',
				`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`pbbkb`='".$pbbkb."',
				`pph`='".$npph."',`chkppn`='".$chkppn."',`ppn`='".$nppn."',`nilaipo`='".$nilai_po."',`tanggalkirim`='".$tanggl_kirim."',
			  ".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',
			  `purchaser`='".$purchaser."',`lokalpusat`='0',`matauang`='".$mtUang."',`npwp`='".$npwporg."',
			  `kurs`='".$Kurs."',`persetujuan1`='".$persetujuan."',`hasilpersetujuan1`='0',
			  `tglp1`='".$thisDate."',`statuspo`='0',`persetujuan2`='".$ttd2."',`hasilpersetujuan2`='0',deliverytime='".$delivtime."',tgledit='".$thisDate."',ongkosangkutan='".$ongkirim."',addcost='".$addcost."',rekening='".$rek."'
			   where nopo='".$nopo."'";
		try{
			$owlPDO->exec($strx);
			foreach($_POST['kdbrg'] as $row =>$isi) {
				$kdbrg=$isi;
				$nopp=$_POST['nopp'][$row];
				$jmlh_pesan=$_POST['rjmlh_psn'][$row];
				$hrg_satuan=$_POST['rhrg_sat'][$row];
				$rongank=str_replace(',','',$_POST['rongank'][$row]);

				$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
				$satuan=$_POST['rsatuan_unit'][$row];
				$diskon=($hrg_sblmdiskon*$disc)/100;
				$hrg_diskon=$hrg_sblmdiskon-$diskon;
				$hrgSat=$hrg_diskon+($rongank/$jmlh_pesan);
				$spekBrg=$_POST['spekBrg'][$row];
				$sqjmlh="select selisih,jlpesan,realisasi from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$nopp."' and kodebarang='".$kdbrg."'";
				$qujmlh=$owlPDO->query($sqjmlh) or die(print " Gagal: ".PDOException::getMessage());
				$qujmlh->setFetchMode(PDO::FETCH_ASSOC);
				$resjmlh=$qujmlh->fetch();
				if($rongank=='') $rongank=0;
				
				
				$tharga=$hrg_sblmdiskon*$jmlh_pesan;
				#proporsi pbbkb dan addcost
				$proppbbkb=(($tharga/$sub_total*($pbbkb+$sub_total))/$jmlh_pesan)-$hrg_sblmdiskon;
				$propaddcost=(($tharga/$sub_total*($addcost+$sub_total))/$jmlh_pesan)-$hrg_sblmdiskon;
				$hargasatbaru=$hrg_sblmdiskon-$diskon+$proppbbkb+$propaddcost;
				
				$sql="update ".$dbname.".log_podt set `jumlahpesan`='".$jmlh_pesan."',`harganormal`='".$hrg_diskon."',`nopp`='".$nopp."',
					  `hargasbldiskon`='".$hrg_sblmdiskon."',`satuan`='".$satuan."',`catatan`='".$spekBrg."',`hargasatuan`='".$hargasatbaru."',
					  `ongkangkut`='".$rongank."'
					  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
				/*
				$sql="update ".$dbname.".log_podt set `jumlahpesan`='".$jmlh_pesan."',`harganormal`='".$hrg_diskon."',`nopp`='".$nopp."',
					  `hargasbldiskon`='".$hrg_sblmdiskon."',`satuan`='".$satuan."',`catatan`='".$spekBrg."',`hargasatuan`='".$hrgSat."',`ongkangkut`='".$rongank."'
					  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
				*/	  
				try{
					$owlPDO->exec($sql); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
				
				$supp="update ".$dbname.".log_prapoht set `nopo`='".$nopo."' where nopp='".$nopp."'";
				try{
					$owlPDO->exec($supp); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
				
				$sdpp="update ".$dbname.".log_prapodt set `create_po`='1' where `nopp`='".$nopp."' and `kodebarang`='".$kdbrg."'";
				try{
					$owlPDO->exec($sdpp); 
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
	
	case 'update_data' :
		// <td>".$_SESSION['lang']['tgl_kirim']."</td>
		echo "<table cellspacing='1' border='0' class='sortable'>
			<thead>
				<tr class=rowheader>
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['nopo']."</td>
					<td align=center>".$_SESSION['lang']['namasupplier']."</td>
					<td align=center>".$_SESSION['lang']['tgl_po']."</td>
					<td align=center>".$_SESSION['lang']['syaratPem']."</td>
					<td align=center>".$_SESSION['lang']['status']."</td>
					<td align=center>action</td>
				</tr>
			</thead><tbody>";
		
		$txt_search='';
		$txt_tgl='';
		if(isset($_POST['txtSearch'])) {
			$txt_search=$_POST['txtSearch'];
			$txt_tgl = "";
			if(!empty($_POST['tglCari'])) {
				$txt_tgl=tanggalsystem($_POST['tglCari']);
				$txt_tgl_t=substr($txt_tgl,0,4);
				$txt_tgl_b=substr($txt_tgl,4,2);
				$txt_tgl_tg=substr($txt_tgl,6,2);
				$txt_tgl=$txt_tgl_t."-".$txt_tgl_b."-".$txt_tgl_tg;
			}
		}
		$where = "";
		if(!empty($txt_search)) {
			$where .= " and nopo LIKE  '%".$txt_search."%'";
		}
		if(!empty($txt_tgl)) {
			$where.=" and tanggal LIKE '".$txt_tgl."'";
		}
		
		$limit=20;
		$page=0;
		if(isset($_POST['page'])) {
			$page=$_POST['page'];
			if($page<0) $page=0;
		}
		$offset=$page*$limit; 
		$maxdisplay=($page*$limit);
		if($_SESSION['empl']['kodejabatan']=='5') {
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' ".$where." order by tanggal desc ";
			$sql="select * from ".$dbname.".log_poht where lokalpusat='0' ".$where." order by tanggal desc limit ".$offset.",".$limit."";
		} else {
			$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."' ".$where." order by tanggal desc ";
			$sql="select * from ".$dbname.".log_poht where lokalpusat='0' and purchaser='".$_SESSION['standard']['userid']."' ".$where." order by tanggal desc limit ".$offset.",".$limit."";
		}
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		$no=0;
		
		$no=$maxdisplay;
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_OBJ);
		while ($res = $query->fetch()) {
			$no+=1;
			$sql2="select * from ".$dbname.".log_5supplier where supplierid='".$res->kodesupplier."'";
			$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
			$query2->setFetchMode(PDO::FETCH_OBJ);
			$res2=$query2->fetch();

			$skry="select karyawanid,namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$res->purchaser."'";
			$qkry=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
			$qkry->setFetchMode(PDO::FETCH_ASSOC);
			$rkry=$qkry->fetch();
			
			$stdt="select * from ".$dbname.".log_transaksidt where nopo='".$res->nopo."'";
			$qtdt=$owlPDO->query($stdt) or die(print " Gagal: ".PDOException::getMessage());
			$numrowtdt=owlBaris($qtdt);
			
			$skeu="select * from ".$dbname.".keu_tagihanht where nopo='".$res->nopo."'";
			$qkeu=$owlPDO->query($skeu) or die(print " Gagal: ".PDOException::getMessage());
			$numrowkeu=owlBaris($qkeu);
			
			$sSyp="select kode,jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$res->syaratbayar."'";
			$qSyp=$owlPDO->query($sSyp) or die(print " Gagal: ".PDOException::getMessage());
			$qSyp->setFetchMode(PDO::FETCH_OBJ);
			$rSyp=$qSyp->fetch();

			if($res->stat_release==0) {
				$stat_po=$_SESSION['lang']['un_release_po'];
				if(($res->hasilpersetujuan1=="2")||($res->hasilpersetujuan2=="2")){
					$stat_po="<a href=# onclick=getKoreksi('".$res->nopo."')>".$_SESSION['lang']['ditolak']."</a>";	
				}
			} elseif($res->stat_release==1) {
				$stat_po=$_SESSION['lang']['release_po'];
			}
			// } elseif($res->stat_release==2) {
				
			// }
			// <td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".tanggalnormal($res->tanggalkirim)."</td>
			echo"
			<tr class=rowcontent>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")."  align=center>".$no."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".$res->nopo."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".(isset($res2->namasupplier)? $res2->namasupplier: '')."</td>
			<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".tanggalnormal($res->tanggal)."</td>";
			echo "<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >";
			if(isset($rSyp->keterangan)) echo $rSyp->keterangan." (".$rSyp->jenis.")";
			echo "</td>";
			echo "<td ".($res->stat_release==2?"bgcolor='orange' onclick=getKoreksi('".$res->nopo."')":"")." >".$stat_po."</td> ";
			$file='';
			#cek apakah ada file
			if($res->filememo!=''){
				$file="<img src=images/onebit_02.png title='View Memo' class=resicon onclick=\"lihatfile('".$res->filememo."','event')\">";
				if($res->stat_release==0){
					$file.="<img src=images/application/application_delete_lama.png title='Delete Memo' class=resicon onclick=deletefile('".$res->nopo."')>";
				}
			}
			
			if(($res->purchaser==$_SESSION['standard']['userid'])||($_SESSION['empl']['kodejabatan']=='5')) {
				// && $numrowtdt==0 && $numrowkeu==0
				// if($res->stat_release!=1) {
				if($res->statuspo==0 && $numrowtdt==0 && $numrowkeu==0) {	
					echo"<td align=center ".($res->stat_release==2?"bgcolor='orange'":"")."><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res->nopo."','".tanggalnormal($res->tanggal)."','".$res->kodesupplier."','".$res->subtotal."','".$res->diskonpersen."','".$res->pbbkb."','".$res->pph."','".$res->chkppn."','".$res->ppn."','".$res->nilaipo."','".(isset($res->rekening)? $res->rekening: '')."','".(isset($res2->npwp)? $res2->npwp: '')."','".$res->nilaidiskon."','".$stat."','".tanggalnormal($res->tanggalkirim)."','".$res->matauang."','".$res->kurs."','".$res->persetujuan1."','".$res->idFranco."','".$res->persetujuan2."','".$res->addcost."','".$res->deliverytime."','".$res->npwp."');\">";
					echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"alasan_batal('".$res->nopo."','".$res->stat_release."');\" >
					<img src=images/icons/04/16/01.png class=resicon  title='ajukan' onclick=\"ajukan('".$res->nopo."');\" >
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
					";
				} else {
					echo"<td align=center ".($res->stat_release==2?"bgcolor='orange'":"")."><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">";
				}
			} else {
				echo"<td  align=center ".($res->stat_release==2?"bgcolor='orange'":"").">
					<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('log_poht','".$res->nopo."','','log_slave_print_detail_po',event);\">
					";
			}
			echo" ".$file."</td></tr>";
		}
		echo "<tr><td colspan=9 align=center>
			".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
			<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
			<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
			</td>
			</tr><input type=hidden id=nopp_".$no." name=nopp_".$no." value='' />"; 
		echo"</tbody> </table>";
		break;

	case'ajukan':
        $str = "update " . $dbname . ".log_poht set statuspo='1', hasilpersetujuan1='0', hasilpersetujuan2=0  where nopo='" . $nopo . "'";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        $slvhc="select * from ".$dbname.".log_poht where nopo='" . $nopo . "'";
        $qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
        $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
        $user_online=$_SESSION['standard']['userid'];
        $bar=$qlvhc->fetch();
        $purchaser=$bar['purchaser'];
        $persetujuan1=$bar['persetujuan1'];
        $persetujuan2=$bar['persetujuan2'];
        $kodeorg=substr($nopo,22,3);

        $ss="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $rs=$owlPDO->query($ss) or die(print " Gagal: ".PDOException::getMessage());
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        $bs=$rs->fetch();
        $namaorganisasi=$bs['namaorganisasi'];
        //exit('warning : '.$purchaser."/".$kodeorg."/".$persetujuan1."/".$persetujuan2."/".$namaorganisasi);

        if ($persetujuan1!=''){
        $to = getUserEmail($persetujuan1);
        $namapengaju = getNamaKaryawan($purchaser);
        $subject="[Notifikasi]Persetujuan PO a/n ".$namapengaju;
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namapengaju." mengajukan Purchase Order (PO)
                   kepada bapak/ibu.
                   <br>
                   <br>
                   Untuk menindak-lanjuti silahkan lakukan di menu Pengadaan->Transaksi->Purchasing->Persetujuan PO
                   <br>
                   Regards,<br>
                   ".$namaorganisasi.".
                 </body>
                 </head>
               </html>";
               $kirim = kirimEmail($to, '', $subject, $body);
        }

        if ($persetujuan2!=''){
        $to = getUserEmail($persetujuan2);
        $namapengaju = getNamaKaryawan($purchaser);
        $subject="[Notifikasi]Persetujuan PO a/n ".$namapengaju;
        $body="<html>
                 <head>
                 <body>
                   <dd>Dengan Hormat,</dd><br>
                   <br>
                   Pada hari ini, tanggal ".date('d-m-Y')." karyawan a/n  ".$namapengaju." mengajukan Purchase Order (PO)
                   kepada bapak/ibu.
                   <br>
                   <br>
                   Untuk menindak-lanjuti silahkan lakukan di menu Pengadaan->Transaksi->Purchasing->Persetujuan PO
                   <br>
                   Regards,<br>
                   ".$namaorganisasi.".
                 </body>
                 </head>
               </html>";         
         $kirim2 = kirimEmail($to, '', $subject, $body);
        }
    break;
	
	case 'edit_po':
		
		if(($supplier_id=='')||($nopo=='')||($disc=='')||($npwporg==''))
		{
				echo "warning: Tolong lengkapi pengisian";
				exit();
		}
		//cek matauang dan kurs
		if($mtUang!='IDR')
		{
			$sGetKurs="select distinct kurs,kode from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl_po."' order by daritanggal desc";
			$qGetKurs=$owlPDO->query($sGetKurs) or die(print " Gagal: ".PDOException::getMessage());
			$qGetKurs->setFetchMode(PDO::FETCH_ASSOC);
			$rGetKurs=$qGetKurs->fetch();
			if($Kurs<$rGetKurs['kurs'])
			{
			   exit("Error: Please provide curs corrensponding to currency, curs for ".$rGetKurs['kode']." :".$rGetKurs['kurs']);   
			}
		}
		else
		{
			$Kurs=1;
		}


		foreach($_POST['kdbrg'] as $row =>$isi)
		{

				$kdbrg=$isi;
				$nopp=$_POST['nopp'][$row];
				$jmlh_pesan=$_POST['rjmlh_psn'][$row];
				$hrg_satuan=$_POST['rhrg_sat'][$row];
				$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
				
				$_POST['rmat_uang'][$row] = "IDR";
				$_POST['rongank'][$row] = 0;
				
				$diskon=($hrg_sblmdiskon*$disc)/100;
				$hrg_diskon=$hrg_sblmdiskon-$diskon;
				$mat_uang=$_POST['rmat_uang'][$row];
				$satuan=$_POST['rsatuan_unit'][$row];
				$spekBrg=$_POST['spekBrg'][$row];
				$rongank=str_replace(',','',$_POST['rongank'][$row]);
				$rongank==''?$rongank=0:$rongank=$rongank;
				$hrgSat=$hrg_diskon+$rongank;
				if(($jmlh_pesan==''||$jmlh_pesan<=0)||($hrg_satuan==''||$hrg_satuan<=0)||($tanggl_kirim=='')||($cr_pembayaran=='')||($lokasi_kirim==''))
				{
						echo "warning: Please complete the form";
						exit();
				}
		else
		{
		$scek="select stat_release from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$qcek->setFetchMode(PDO::FETCH_ASSOC);
		$rcek=$qcek->fetch();
		if($rcek['stat_release']==1)
		{
				echo"warning : PO : ".$nopo." has been released";
				exit();
		}

				if(intval($lokasi_kirim))
				{
				$field="`idFranco`";
				}
				else
				{
				$field="`lokasipengiriman`";
				}

				$strx="update ".$dbname.".log_poht set 
				`kodesupplier`='".$supplier_id."',`subtotal`='".$sub_total."',tgledit='".$tglSkrng."',`diskonpersen`='".$disc."',`nilaidiskon`='".$nilai_dis."',`pbbkb`='".$pbbkb."',`pph`='".$npph."',`chkppn`='".$chkppn."',`ppn`='".$nppn."',`nilaipo`='".$nilai_po."',
				`tanggalkirim`='".$tanggl_kirim."',".$field."='".$lokasi_kirim."',`syaratbayar`='".$cr_pembayaran."',`uraian`='".$ketUraian."',matauang='".$mtUang."',kurs='".$Kurs."',
				 persetujuan1='".$persetujuan."',persetujuan2='".$ttd2."',ongkosangkutan='".$ongkirim."',addcost='".$addcost."',rekening='".$rek."',deliverytime='".$delivtime."',npwp='".$npwporg."'
				 where nopo='".$nopo."'";

				try{
					$owlPDO->exec($strx);
					foreach($_POST['kdbrg'] as $row =>$isi)
					{
						$kdbrg=$isi;
						$nopp=$_POST['nopp'][$row];
						$jmlh_pesan=$_POST['rjmlh_psn'][$row];
						$hrg_satuan=$_POST['rhrg_sat'][$row];
						$hrg_sblmdiskon=str_replace(',','',$hrg_satuan);
						$diskon=($hrg_sblmdiskon*$disc)/100;
						$hrg_diskon=$hrg_sblmdiskon-$diskon;
						$mat_uang=$_POST['rmat_uang'][$row];
						$satuan=$_POST['rsatuan_unit'][$row];
						$spekBrg=$_POST['spekBrg'][$row];
						$rongank=str_replace(',','',$_POST['rongank'][$row]);
						$hrgSat=$hrg_diskon+($rongank/$jmlh_pesan);
						
						$tharga=$hrg_sblmdiskon*$jmlh_pesan;
						#proporsi pbbkb dan addcost
						$proppbbkb=(($tharga/$sub_total*($pbbkb+$sub_total))/$jmlh_pesan)-$hrg_sblmdiskon;
						$propaddcost=(($tharga/$sub_total*($addcost+$sub_total))/$jmlh_pesan)-$hrg_sblmdiskon;
					
						$hargasatbaru=$hrg_sblmdiskon-$diskon+$proppbbkb+$propaddcost;
						
						$sql="update ".$dbname.".log_podt 
							  set `jumlahpesan`='".$jmlh_pesan."',`hargasatuan`='".$hargasatbaru."',`matauang`='".$mat_uang."',`hargasbldiskon`='".$hrg_sblmdiskon."',
							  `satuan`='".$satuan."',catatan='".$spekBrg."',harganormal='".$hrg_diskon."',`ongkangkut`='".$rongank."'
							  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
						/*
						$sql="update ".$dbname.".log_podt 
							  set `jumlahpesan`='".$jmlh_pesan."',`hargasatuan`='".$hrgSat."',`matauang`='".$mat_uang."',`hargasbldiskon`='".$hrg_sblmdiskon."',
							  `satuan`='".$satuan."',catatan='".$spekBrg."',harganormal='".$hrg_diskon."',`ongkangkut`='".$rongank."'
							  where nopo='".$nopo."' and kodebarang='".$kdbrg."' and nopp='".$nopp."'";
						*/	  
						try{
							$owlPDO->exec($sql); 
							$sUpdate="update ".$dbname.".log_prapodt set create_po=1 where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
							try{
								$owlPDO->exec($sUpdate); 
							}catch(PDOException $e){
								print " Gagal  !: " . $e->getMessage() . "\n"; 
								die(); 
							}
						}catch(PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							die(); 
						}
					}
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
		 }
		}
		break;
		
	case 'get_alasan_batal':
		$s_form="select * from ".$dbname.".log_poht where nopo='".$nopo."' ";
		$q_from=$owlPDO->query($s_form) or die(print " Gagal: ".PDOException::getMessage());
		$q_from->setFetchMode(PDO::FETCH_ASSOC);
		$r_form=$q_from->fetch();
		echo "<div id=form_batal><fieldset><legend>".$nopo."</legend>
				<table cellspacing=1 border=0>
				<tr><td><textarea rows=5 cols=34 id='batal'></textarea></td>
				<td><button class=mybutton id=hapus onclick=delPo('".$nopo."','".$stat."','".isset($_POST['batal'])."')>";echo $_SESSION['lang']['save'];
		echo"</button></td></tr></table></filedset></div>";
		break;

	case 'delete_all':
		$scek="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qcek=$owlPDO->query($scek) or die(print " Gagal: ".PDOException::getMessage());
		$qcek->setFetchMode(PDO::FETCH_ASSOC);
		$rPO=$qcek->fetch();          
		if($rPO['stat_release']==1)
		{
			echo"warning : PO : ".$nopo." being on correction progress";
			exit();
		}
		else
		{
			$sCekGdng="select distinct nopo from ".$dbname.".log_transaksi_vw where nopo='".$nopo."'";
			$qCekGdng=$owlPDO->query($sCekGdng) or die(print " Gagal: ".PDOException::getMessage());
			$rCekGdng=owlBaris($qCekGdng);
			if($rCekGdng>0)
			{
				exit("Error: PO : ".$nopo." has been receipt in warehouse, could not be deleted");
			}

			$sListPP="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
			$qListPP=$owlPDO->query($sListPP) or die(print " Gagal: ".PDOException::getMessage());
			$qListPP->setFetchMode(PDO::FETCH_ASSOC);
			$row=owlBaris($qListPP);
		   $rPO['terbayar']==''?$rPO['terbayar']=0:$rPO['terbayar'];
		   $rPO['tagihandp']==''?$rPO['tagihandp']=0:$rPO['tagihandp'];
		   $rPO['persetujuan1']==''?$rPO['persetujuan1']=0:$rPO['persetujuan1'];
		   $rPO['hasilpersetujuan1']==''?$rPO['hasilpersetujuan1']=0:$rPO['hasilpersetujuan1'];
		   $rPO['persetujuan2']==''?$rPO['persetujuan2']=0:$rPO['persetujuan2'];
		   $rPO['hasilpersetujuan2']==''?$rPO['hasilpersetujuan2']=0:$rPO['hasilpersetujuan2'];
		   $rPO['persetujuan3']==''?$rPO['persetujuan3']=0:$rPO['persetujuan3'];
		   $rPO['hasilpersetujuan3']==''?$rPO['hasilpersetujuan3']=0:$rPO['hasilpersetujuan3'];     
		   $rPO['stat_release']==''?$rPO['stat_release']=0:$rPO['stat_release'];
		   $rPO['tglrelease']==''?$rPO['tglrelease']='0000-00-00':$rPO['tglrelease'];
		   $rPO['tglp1']==''?$rPO['tglp1']='0000-00-00':$rPO['tglp1'];
		   $rPO['tglp2']==''?$rPO['tglp2']='0000-00-00':$rPO['tglp2'];
		   $rPO['tglp3']==''?$rPO['tglp3']='0000-00-00':$rPO['tglp3'];
		   $rPO['idFranco']==''?$rPO['idFranco']=0:$rPO['idFranco']=$rPO['idFranco'];
		   $x=0;
			while($rListPP=$qListPP->fetch())
			{
				$x++;
				$sUpd="update ".$dbname.".log_prapodt set create_po=0 where kodebarang='".$rListPP['kodebarang']."' and nopp='".$rListPP['nopp']."'";
				try{
					$owlPDO->exec($sUpd); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
				
				if($x==1){
					$sql_del_po="delete from ".$dbname.".log_poht_del where nopo = '".$nopo."'"; 
					try{
						$owlPDO->exec($sql_del_po); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					$sql_del_po="delete from ".$dbname.".log_podt_del where nopo = '".$nopo."'"; 
					try{
						$owlPDO->exec($sql_del_po); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					$str1="insert into ".$dbname.".log_poht_del(nopo,tanggal,tgledit,kodesupplier,subtotal,ongkosangkutan,diskonpersen,
					nilaidiskon,ppn,nilaipo,syaratbayar,uraian,tanggalkirim,lokasipengiriman,tanggalbayar,carapembayaran,
					terbayar,notransbyr,statuspo,matauang,kurs,invoicedp,idFranco,tagihandp,keterangan,pountuk,purchaser,
					persetujuan1,hasilpersetujuan1,persetujuan2,hasilpersetujuan2,persetujuan3,hasilpersetujuan3,
					kodeorg,lokalpusat,stat_release,useridreleasae,tglrelease,tglp1,tglp2,tglp3,catatanrelease,alasan_batal) 
					values ('".$rPO['nopo']."','".$rPO['tanggal']."','".$rPO['tgledit']."','".$rPO['kodesupplier']."',
					'".$rPO['subtotal']."','".$rPO['ongkosangkutan']."','".$rPO['diskonpersen']."','".$rPO['nilaidiskon']."',
					'".$rPO['ppn']."','".$rPO['nilaipo']."','".$rPO['syaratbayar']."','".$rPO['uraian']."','".$rPO['tanggalkirim']."',
					'".$rPO['lokasipengiriman']."','".$rPO['tanggalbayar']."','".$rPO['carapembayaran']."','".$rPO['terbayar']."',
					'".$rPO['notransbyr']."','".$rPO['statuspo']."','".$rPO['matauang']."','".$rPO['kurs']."','".$rPO['invoicedp']."',
					'".$rPO['idFranco']."','".$rPO['tagihandp']."','".$rPO['keterangan']."','".$rPO['pountuk']."','".$rPO['purchaser']."',
					'".$rPO['persetujuan1']."','".$rPO['hasilpersetujuan1']."','".$rPO['persetujuan2']."','".$rPO['hasilpersetujuan2']."',
					'".$rPO['persetujuan3']."','".$rPO['hasilpersetujuan3']."','".$rPO['kodeorg']."','".$rPO['lokalpusat']."',
					'".$rPO['stat_release']."','".$rPO['useridreleasae']."','".$rPO['tglrelease']."','".$rPO['tglp1']."',
					'".$rPO['tglp2']."','".$rPO['tglp3']."','".$rPO['catatanrelease']."','".$batal."')";

					try{
						$owlPDO->exec($str1); 
					} catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				
			
				$sql_dt="insert into ".$dbname.".log_podt_del(nopo,kodebarang,jumlahpesan,hargasatuan,ongkangkut,harganormal,
						  nopp,matauang,hargasbldiskon,satuan,catatan)
						  values ('".$nopo."','".$rListPP['kodebarang']."','".$rListPP['jumlahpesan']."',
						  '".$rListPP['hargasatuan']."','".$rListPP['ongkangkut']."','".$rListPP['harganormal']."','".$rListPP['nopp']."',
						  '".$rListPP['matauang']."','".$rListPP['hargasbldiskon']."','".$rListPP['satuan']."','".$rListPP['catatan']."')";
				try{
					$owlPDO->exec($sql_dt); 
					
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
			}
			#delete ht
			$sql_del="delete from ".$dbname.".log_poht where nopo = '".$nopo."'"; 
			try{
				$owlPDO->exec($sql_del); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}	

			
			#cek nodph
			
			#update dph terkait po
			$update="update ".$dbname.".log_perintaanhargaht set po=0 where nomor='".$rPO['nodph']."' and supplierid='".$rPO['kodesupplier']."' ";
			try {
				$owlPDO->exec($update);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
			
		}

		break;

	case 'insert_forward_po' :
		if($persetujuan==$_SESSION['standard']['userid'])
		{
			echo "Warning:  Name cout not be the same as requester name";
		}
		else
		{		
			$tgl=date("Y-m-d");
			$sql="update ".$dbname.".log_poht set persetujuan1='".$persetujuan."',statuspo='2',tglp1='".$tgl."',hasilpersetujuan1='1' where nopo='".$nopo."'";
			try{
				$owlPDO->exec($sql); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
	break;
	case 'get_form_approval' :
		$sql="select nopo from ".$dbname.".log_poht where nopo='".$nopo."' and lokalpusat='0'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($query);
		if($rCek>0)
		{
			$rest=$query->fetch();
			echo"<br />
			<div id=test style=display:block>
			<fieldset>
			<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
			<table cellspacing=1 border=0>
			<tr>
			<td colspan=3>
			Submission for the next verification :</td>
			</tr>
			<td>".$_SESSION['lang']['namakaryawan']."</td>
			<td>:</td>
			<td valign=top>";

			$klq="select namakaryawan,karyawanid,bagian,lokasitugas from ".$dbname.".`datakaryawan` where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and statuskaryawan != 'Keluar' and tipekaryawan in ('0','7','8') and karyawanid!='".$user_id."' and lokasitugas!='' and (kodejabatan<6 or kodejabatan=11) order by namakaryawan asc"; 
			$qry=$owlPDO->query($klq) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_OBJ);
			$optPur='';
			while($rst=$qry->fetch())
			{
				$sBag="select nama from ".$dbname.".sdm_5departemen where kode='".$rst->bagian."'";
				$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
				$qBag->setFetchMode(PDO::FETCH_ASSOC);
				$rBag=$qBag->fetch();
				$optPur.="<option value='".$rst->karyawanid."'>".$rst->namakaryawan." [".$rst->lokasitugas."] [".$rBag['nama']."]</option>";
			}

			echo"
				<select id=persetujuan_id name=persetujuan_id>
						$optPur;
				</select></td></tr>
				<tr>
				<td colspan=3 align=center>
				<button class=mybutton onclick=forward_po() title=\"Re-Submission\" >".$_SESSION['lang']['diajukan']."</button>
				<button class=mybutton onclick=cancel_po() title=\"Close this form\">".$_SESSION['lang']['cancel']."</button>
				</td></tr></table><br />
				<input type=hidden name=proses id=proses  />
				</fieldset></div>

				<div id=close_po style=\"display:none;\">	
				<fieldset><legend><input type=text id=snopo name=snopo disabled value='".$nopo."' /></legend>
				<p align=center>Process this PO, Are you sure?</p><br />
				<button class=mybutton onclick=proses_release_po() title=\"Process!\" >".$_SESSION['lang']['approve']."</button>
				<button class=mybutton onclick=cancel_po() title=\"Close\">".$_SESSION['lang']['cancel']."</button>
				</fieldset></div>
				";
		} else {
			echo"warning: Data not recorded";
			exit();
		}
		break;
	
	case 'proses_release_po':
		$sql="update ".$dbname.".log_poht set statuspo='2',hasilpersetujuan1='1' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sql); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
		
	case 'cek_pembuat_po':
		$user_id=$_SESSION['standard']['userid'];
		$skry="select purchaser from ".$dbname.".log_poht where nopo='".$nopo."'";
		$qkry=$owlPDO->query($skry) or die(print " Gagal: ".PDOException::getMessage());
		$qkry->setFetchMode(PDO::FETCH_ASSOC);
		$rkry=$qkry->fetch();
		if($rkry['purchaser']!=$user_id)
		{
			echo "warning:Please See Your Username";
			exit();
		}
		break;
	
	case'getKurs':
		$sGet="select kurs from ".$dbname.".setup_matauangrate where kode='".$mtUang."' and daritanggal='".$tgl_po."'";
		$qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
		$qGet->setFetchMode(PDO::FETCH_ASSOC);
		$rGet=$qGet->fetch();
		
		if($mtUang=='IDR')
		{
			$rGet['kurs']=1;
		}
		else
		{
			$rGet['kurs']=$rGet['kurs'];
		}
		echo $rGet['kurs'];
		break;
	
	case'getKoreksi':
		$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."' and lokalpusat='0'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$rCek=owlBaris($query);
		if($rCek>0)
		{
								$rest=$query->fetch();
								$whrKar1="karyawanid='".$rest['persetujuan1']."'";
				                $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
				                $whrKar2="karyawanid='".$rest['persetujuan2']."'";
				                $optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
								echo"<br />
								<div id=test>
								<fieldset>
								<legend><input type=text readonly=readonly name=rnopp id=rnopp value=".$nopo."  /></legend>
								<table class=sortable border=0 cellspacing=1 >
								<thead><tr class=rowheader><td align=center colspan=2>".$_SESSION['lang']['koreksi']."</td></tr></thead>
								<tbody>";
								if($rest['hasilpersetujuan1']==2){
								echo"<tr class=rowcontent ><td style='width:200px'>".$optkaryawan[$rest['persetujuan1']]."</td><td align=justify style='width:200px'>".$rest['komentartolak1']."</td></tr>";
								}
								if($rest['hasilpersetujuan2']==2){
								echo"<tr class=rowcontent ><td style='width:200px'	>".$optttd[$rest['persetujuan2']]."</td><td align=justify style='width:200px'>".$rest['komentartolak1']."</td></tr>";
								}
								//echo "<button class=mybutton onclick=doneKoreksi() title=\"Selesai Koreksi\" >".$_SESSION['lang']['done']."</button>";
								echo "<tr><td align=center colspan=2><button class=mybutton onclick=cancel_po() title=\"close\">".$_SESSION['lang']['done']."</button></td></tr>
								</tbody>
								</table>

										</fieldset></div>
										";
		}
		else
		{
				echo"warning: Data not recorded";
				exit();
		}
		break;
	
	case'updateKoreksi':
		$sUpd="update ".$dbname.".log_poht set stat_release='0' where nopo='".$nopo."'";
		try{
			$owlPDO->exec($sUpd); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
		break;
	
	case'getNotifikasi':
		$Sorg="select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$qOrg=$owlPDO->query($Sorg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
		while($rOrg=$qOrg->fetch())
		{
		if($_SESSION['empl']['kodejabatan']=='5')
		{
			$sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)";
		}
		else
		{
		   $sList="select count(*) as jmlhJob from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$rOrg['kodeorganisasi']."' and purchaser='".$_SESSION['standard']['userid']."' and lokalpusat='0' and status!='3') and (selisih>0 or selisih is null)"; 
		}
		
		$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
		$qList->setFetchMode(PDO::FETCH_ASSOC);
		$rBaros=owlBaris($qList);
			if($rBaros!=0)
			{
				$rList=$qList->fetch();
				if($rList['jmlhJob']=='')
				{
				$rList['jmlhJob']=0;
				}
					if(isset($_POST['status']) and $_POST['status']==1)
					{
						echo"[".$rOrg['kodeorganisasi']." : ".$rList['jmlhJob']." ]";
					}
					else
					{
						echo"[".$rOrg['kodeorganisasi']." : <a href='#' onclick=\"cek_pp_pt('".$rOrg['kodeorganisasi']."')\">".$rList['jmlhJob']."</a> ]";
					}
			}
		}
		break;
	
	case'getSupplierNm':
			echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
				<div style=\"overflow:auto;max-height:295px;\">
				<table cellpading=1 border=0 class=sortable>
				<thead>
				<tr class=rowheader>
				<td align=center>No.</td>
				<td align=center>".$_SESSION['lang']['kodesupplier']."</td>
				<td align=center>".$_SESSION['lang']['namasupplier']."</td>
				<td align=center>".$_SESSION['lang']['kota']."</td>
				</tr><tbody>
				";
		 $sSupplier="select namasupplier,supplierid, kota from ".$dbname.".log_5supplier 
		 where namasupplier like '%".$nmSupplier."%' and kodekelompok in('S001','S002') and status=1";
		 $qSupplier=$owlPDO->query($sSupplier) or die(print " Gagal: ".PDOException::getMessage());
		 $qSupplier->setFetchMode(PDO::FETCH_ASSOC);
		 while($rSupplier=$qSupplier->fetch())
		 {
			 $no+=1;
			 echo"<tr class=rowcontent  style=cursor:pointer onclick=setData('".$rSupplier['supplierid']."')>
				 <td align=center>".$no."</td>
				 <td>".$rSupplier['supplierid']."</td>
				 <td>".$rSupplier['namasupplier']."</td>
				 <td>".$rSupplier['kota']."</td>
			</tr>";
		 }
			echo"</tbody></table></div>";
		break;
	
	default:
		break;
}
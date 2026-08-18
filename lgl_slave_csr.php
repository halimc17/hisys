<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$kodeorg = checkPostGet('kodeorg', '');
$kodekab = checkPostGet('kodekab', '');
$kodekec = checkPostGet('kodekec', '');

$penerima = checkPostGet('penerima', '');
$keterangan = checkPostGet('keterangan', '');

$kegiatan = checkPostGet('kegiatan', '');
$volume = checkPostGet('volume', '');
$kab = checkPostGet('kab', '');
$dana = checkPostGet('dana', '');
$kec = checkPostGet('kec', '');
$des = checkPostGet('des', '');
$status = checkPostGet('status', '');

$rekening = checkPostGet('rekening', '');
$bank = checkPostGet('bank', '');

$numrow = checkPostGet('numrow', '');
$kepada = checkPostGet('kepada', '');

$namafile = checkPostGet('namafile', '');

$divsch = checkPostGet('divsch', '');
$tanggalsch = checkPostGet('periodesch', '');
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmins = makeOption($dbname, 'legal_5pihak', 'kodepihak,namapihak');

$tmpTgl = explode('-',$tanggal);	
$path	= "fileupload/lgl_pengajuanfee/";
$todayhis=date('Y-m-d h:i:s');
switch ($method) {
    case'html':
		$tab= "";
		@$countApprove = getCountApproval('FEE',$kodeorg);
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak']);
		
		$str=" select * from ".$dbname.".lgl_pengajuanfee where  notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$tab.= "<fieldset><legend><img src=images/excel.jpg class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"previewexcel('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','excel');\" >
				</legend>";
		$tab.= "<table border=0 cellspacing=1 class=sortable>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
					
		$tab.= "</tr></thead><tbody>";
		$tab.= "<tr class=rowcontent>
				<td>".$nmkar[$bar['updateby']]."<br>
					".$bar['updatetime']."</td>";
			for($i=1;$i<=$countApprove;$i++){
				@$arrApp = detailApprove($i,$notransaksi,'FEE');
				
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
	$tab.= "</tbody></table><hr>";
	$tab.= "<table cellspacing=1 border=0>
			<tr>
				<td>" . $_SESSION['lang']['notransaksi'] . "</td> 
				<td>:</td>
				<td>".$bar['notransaksi']."</td>
				
				<td>Instansi Yang Memproses</td> 
				<td>:</td>
				<td>".$nmins[$bar['instansi']]."</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
				
				<td>Tipe Pembayaran</td> 
				<td>:</td>
				<td>".strtoupper($bar['tipe'])."</td>
			</tr> 
			<tr>
				<td>" . $_SESSION['lang']['tanggal'] . "</td> 
				<td>:</td>
				<td>".$bar['tanggal']."</td>
				
				<td>Nama Penerima</td> 
				<td>:</td>
				<td>".$bar['penerima']."</td>
			</tr>
			<tr>
				<td>" . $_SESSION['lang']['keterangan'] . "</td> 
				<td>:</td>
				<td>".$bar['keterangan']."</td>
				
				<td>Nomor Rekening</td> 
				<td>:</td>
				<td>".$bar['bank']." : ".$bar['rekening']."</td>
			</tr> ";
	$tab.= "</table><hr>";
	$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=75px>" . $_SESSION['lang']['rupiah'] . "</td>
        </tr>
		</thead>";
		$no = 0;
        $str = "select * from " . $dbname . ".lgl_pengajuanfee where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				$xx.=" style=background-color:#F5EEF8 ";
			}
			$tab.="<tr class=rowcontent ".$xx.">";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . $bar['deskripsi'] . "</td>";
			$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
			
			@$trupiah+=$bar['rupiah'];
		}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=2 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				</tr>";
		
        $tab.="</table><hr>";
		
		$tab.="<table class='sortable' cellspacing='1' border='0' style=width:100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='loadfilesdetail'>
				</tbody>
			</table>";
		$tab.= "</fieldset>";
		
		$stream = $tab;
		
		if($tipe!='excel'){
			echo $stream;
		} else{
			$nop_ = "bansos_" . date('Ymd_His');
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
	case'getnotransaksi':
		#001/CSR/LGL/BOD/BJHO/IX/2017
		$tempPrd=explode('-',$tanggal);
		
		$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$str=" select notransaksi from ".$dbname.".lgl_csr where  kodeorg='".$kodeorg."' and tanggal = '".$tanggal."' order by notransaksi desc limit 1 "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if(intval($bar['notransaksi'])==0){
			$noawal=1;
		}else{
			$noawal = intval($bar['notransaksi'])+1;
		}
		
		$notranbaru=addZero($noawal,3)."/CSR/LGL/".$nmpt[$kodeorg]."/".$kodeorg."/".romawi($tempPrd[1])."/".$tempPrd[0];
        
		echo $notranbaru;
    break;
	
	case'getkecamatan':
		
		$tempPrd=explode('-',$tanggal);
		
		$nmpt=makeOption($dbname,'kecamatan','idkec,kecamatan',"id_kab='".$kodekab."'");
		
		$optkec="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		
		foreach($nmpt as $k => $v)
		{
			$optkec.="<option value=".$k.">".$v."</option>";
		}
        
		echo $optkec;
    break;
	
	case'getdesa':
		
		//$tempPrd=explode('-',$tanggal);
		
		$nmpt=makeOption($dbname,'desa','iddes,desa',"id_kec='".$kodekec."'");
		
		$optdes="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		foreach($nmpt as $k => $v)
		{
			$optdes.="<option value=".$k.">".$v."</option>";
		}
        
		echo $optdes;
    break;
	
    case'detail':
		#cek dulu sudah ada atau belum ? jika ada = update
		$str = "select * from " . $dbname . ".lgl_pengajuanfee where notransaksi='" . $notransaksi . "'";
		$res=fetchData($str);
		if(count($res)>0){
			# update flag menjadi 1
			$str = "update " . $dbname . ".lgl_pengajuanfee set tipe='".$tipe."', keterangan='".$keterangan."', updateby='".$_SESSION['standard']['userid']."', rekening='".$rekening."', bank='".$bank."', penerima='".$penerima."' where notransaksi = '" . $notransaksi . "'";
			try {
				$owlPDO->exec($str);			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
        OPEN_BOX();
	echo"<fieldset>
        <legend>" . $_SESSION['lang']['form'] . "</legend>
		<table><td valign=top>
		<fieldset style=height:75px>
        <legend>" . $_SESSION['lang']['input'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center width=350px>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center rowspan=2 width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		
		</thead>
        <tr class=rowcontent>
            <td align=center>#</td>    
			<td><input id=deskripsi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:99%;\"></td>
            
			<td><input id=rupiah style=\"width:97%;\" onkeyup=\"z.numberFormat('rupiah',2)\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			
			<td align=center><input type=hidden id=method value='insert'>
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
                <img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=3 align=right>
			<td colspan=1 align=center>
			<img title='Refresh' class=resicon onclick=\"loaddatadetail('".$notransaksi."')\" src='images/refresh2.png'/>
			<img title='" . $_SESSION['lang']['selesai'] . "' class=resicon onclick=\"displayList()\" src='images/foldoq.png'/>
			</td>
        </tr></table></fieldset>
		</td><td  valign=top >
		<fieldset style=height:75px;min-width:310px><legend>Upload</legend>
			<table>
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
		</fieldset>
		</td></table>
        </fieldset><hr>";
	echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
	CLOSE_BOX();
	break;

    case'insert':
        #cek data
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_csr where "
		. " notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and kodecsr='".$kegiatan."' and keterangan='" . $keterangan . "' and tanggal='".$tanggal."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
		
        if ($jlhbrs > 0&&$status==0) {
            exit("Error : Transaksi sudah ada.");
        }
		if($status==0)
		{
			$str = "insert into " . $dbname . ".lgl_csr 
		
			(`notransaksi`, `kodeorg`, `tanggal`, `kodecsr`, `volume`, `dana`, `kodekecamatan`, `kodedesa`, `kodekota`, `penerima`, `keterangan`, `createby`, `createtime`, `updateby`, `updatetime`)
			 values ('".$notransaksi."','".$kodeorg."','".$tanggal."','".$kegiatan."','".$volume."','".$dana."','".$kec."','".$des."','".$kab."','".$penerima."','".$keterangan."','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "','".$todayhis."')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			
		} 
		else{
			$str = "UPDATE " . $dbname . ".lgl_csr SET 
			`tanggal` = '".$tanggal."',`kodecsr` = '".$kegiatan."', `volume` = '".$volume."', `dana` = '".$dana."', `kodekecamatan` = '".$kec."', `kodedesa` = '".$des."', `kodekota` = '".$kab."', `penerima` = '".$penerima."', 	`keterangan` = '".$keterangan."', `createby` = '" . $_SESSION['standard']['userid'] . "', `createtime` = '".$todayhis."', `updateby` = '" . $_SESSION['standard']['userid'] . "', `updatetime` = '".$todayhis."'
			WHERE `notransaksi` = '".$notransaksi."'";
			//exit("Error : ".$str);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}        

	break;
	
    case'delete':
        $str = "delete from " . $dbname . ".lgl_csr where notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and tanggal='" . $tanggal . "'"; //exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		
	break;

    case'deletedetail':
		#cek data kalau terakhir hapus juga filenya
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_pengajuanfee where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
		#hapus data transaksinya
        $str = "delete from " . $dbname . ".lgl_pengajuanfee where notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and instansi='".$instansi."' and keterangan='" . $keterangan . "' and deskripsi='".$deskripsi."' and tanggal='".$tanggal."'"; //exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		#hapus filenya
        if ($jlhbrs =='1') {
			$str="select * from ".$dbname.".listfile_lgl_pengajuanfee where notransaksi='".$notransaksi."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar = $res->fetch()){
				$str="delete from ".$dbname.".listfile_lgl_pengajuanfee where notransaksi='".$notransaksi."' and namafile='".$bar['namafile']."'"; 
				try{
					$owlPDO->exec($str);
					$pathx = $path.$bar['namafile'];
					unlink($pathx);
				}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
			}
        }
		
	break;

	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='FEE' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
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
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
    case'ajukan':
		// if($kepada=='' or $notransaksi==''){
			// exit('Error : Isikan nama penyetuju.');
		// }
		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_csr set posting='1' ,postingdate='" . date('Y-m-d H:i:s') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
		//exit("error:".$str);
        try {
            $owlPDO->exec($str);
			
        } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
        
	break;
    case'loaddatadetail':
		//exit("error :sdfghjk");
		$tab="<table><td valign=top>";
		$tab.="<fieldset><legend>Data</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:590px>
            <thead><tr class=rowheader>
            <td align=center width=30px>No</td>
            <td align=center>" . $_SESSION['lang']['deskripsi'] . "</td>
            <td align=center width=100px>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		</thead>";
        
		$tab.="</fieldset></td></table>";
		$tab.="</fieldset>";

        echo $tab;
	break;
    case'loaddata':

        $where = "";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = "";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
        if ($divsch != '') {
            $where.=" and kodeorg='" . $divsch . "' ";
        }
        if ($tanggalsch != '') {
            $where.=" and tanggal like '" . $tanggalsch . "%' ";
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

        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_csr where 1=1 " . $where . "";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;

        $str = "SELECT * FROM " . $dbname . ".lgl_csr a, lgl_5kegiatancsr b
		where 1=1 " . $where . " and a.kodecsr=b.id order by notransaksi desc limit " . $offset . "," . $limit . "";
        $status=1;
		$tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				$xx.=" style=background-color:#F5EEF8 ";
			}
            $tab.="<tr class=rowcontent ".$xx." id=tr_".$no.">";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td align=center>" . $bar['tanggal'] . "</td>";
            $tab.="<td>" . $bar['tipe'] . "</td>";
            $tab.="<td>" . $bar['penerima'] . "</td>";
            $tab.="<td>" . $bar['keterangan'] . "</td>";
            $tab.="<td align=right>" . @hidezerodecimal($bar['dana'],2) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";

            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
				onclick=\"edit('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['kodecsr'] . "','" . $bar['volume'] . "','" . $bar['dana'] . "','".$bar['kodekecamatan']."','".$bar['kodedesa']."','".$bar['kodekota']."','".$bar['penerima']."','".$bar['keterangan']."','1');\" ></td>";
				
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['notransaksi'] . "');\" ></td>";
				$isi.="<td align=center><img src='images/skyblue/posting.png' class=resicon  title='Posting' 
									onclick=\"ajukan('" . $bar['notransaksi'] . "');\" ></td>";					
			   //$isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan ???' onclick=\"form_ajukan('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $no . "');\" ></td>";
            }if($bar['posting'] == 1){
				$isi.="<td></td><td></td><td align=center><img src='images/skyblue/posted.png' class=resicon  title='Posted' 
									 ></td>";
			}
            //$isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"html('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";

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
                     <tr><td colspan=13 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

        break;
	
	
	case 'submitfile':
		#cek data
        $sql = "select * from " . $dbname . ".lgl_pengajuanfee where notransaksi='" . $notransaksi . "'";
		$res=fetchData($sql);
		if(count($res)==0){
			exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu !');
		}
		
		$str="select * from ".$dbname.".listfile_lgl_pengajuanfee where notransaksi = '".$notransaksi."' and status='1'";
		$res=fetchData($str);
		if(count($res)>=10){
			exit("Warning : Limit upload hanya 10 file.");
		}
		$tgl = date("YmdHis");
		$his = date("His");
		$data = $_POST;
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $tgl."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 250000){
						$str = "insert into ".$dbname.".listfile_lgl_pengajuanfee values ('','".$notransaksi."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
		$tab = $posting = "";	
		$str="select * from ".$dbname.".lgl_csr where notransaksi = '".$notransaksi."'";
		$res=fetchData($str);
		@$posting=$res[0]['posting'];
		
		$str="select * from ".$dbname.".listfile_lgl_pengajuanfee where notransaksi = '".$notransaksi."' and status='1'";
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
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
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
		$str="delete from ".$dbname.".listfile_lgl_pengajuanfee where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
		try{
			$owlPDO->exec($str);
			$pathx = $path.$namafile;
			unlink($pathx);
		}
		catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
}
?>	
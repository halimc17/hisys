<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$tgl = checkPostGet('tgl', '');
$kodeorg = checkPostGet('kodeorg', '');
$kodekend = checkPostGet('kodekend', '');
$notransaksi = checkPostGet('notransaksi', '');
$biaya = checkPostGet('biaya', '');
$keterangan = checkPostGet('keterangan', '');
$numrow = checkPostGet('numrow', '');
$kepada = checkPostGet('kepada', '');
$namafile = checkPostGet('namafile', '');
$jenisbiaya = checkPostGet('jenisbiaya', '');
$show = checkPostGet('show', '');
$kodeasset = checkPostGet('kodeasset', '');
$kary = checkPostGet('kary', '');
$tglpinjam = checkPostGet('tglpinjam', '');
$tglpinjamlama = checkPostGet('tglpinjamlama', '');
$tglkembali = checkPostGet('tglkembali', '');
$penerima = checkPostGet('penerima', '');
$ketkembali = checkPostGet('ketkembali', '');
$tipe = checkPostGet('tipe', '');

$divsch = checkPostGet('divsch', '');
$karysch = checkPostGet('karysch', '');

$arrmilik=array("0"=>"sewa/kontrak","1"=>"milik sendiri");
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jbt = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$kodejab = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$pemilik=makeOption($dbname,'sdm_daftarasset','kodeasset,kodeorg');
$posisiasset=makeOption($dbname,'sdm_daftarasset','kodeasset,posisiasset');
$thnperoleh=makeOption($dbname,'sdm_daftarasset','kodeasset,tahunperolehan');
$namasset=makeOption($dbname,'sdm_daftarasset','kodeasset,namasset');

$jab = getPostingJabatan('traksirkh');	
$tmpTgl = explode('-',$tgl);	
$path	= "fileupload/sdm_pinjamasset/";
$today=date('Y-m-d');

switch ($method) {
    case'html':
		$tab = "<img src=images/excel.jpg class=resicon  title='Excel' onclick=\"viewexcel('".$kary."','excel');\">";
		$tab.= "<table>";
		$tab.= "<tr >
					<td><b>" . $_SESSION['lang']['namakaryawan'] . "</b></td><td><b>:</b></td>
					<td><b>" . $nmkar[$kary] . "</b></td>
				</tr>";
		$tab.= "</table><hr>";
		
		if($tipe=='html'){
			$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:840px>";
		} else{
			$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
		}
		
        $tab.= "
            <thead><tr class=rowheader>
			<td rowspan=2 align=center>No</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['kodeasset'] . "</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['kepemilikan']."</td>
			<td rowspan=2 align=center width=50px>" . $_SESSION['lang']['posisiasset'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td rowspan=2 align=center width=75px>".$_SESSION['lang']['tanggalpinjam']."</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td colspan=3 align=center>" . $_SESSION['lang']['back'] . "</td>
            </tr>
            <tr>
			<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center>" . $_SESSION['lang']['penerima'] . "</td>
			<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr>
			
			</thead>";
        $no = 0; $where='';
		if($kary!=''){
			$where.=" and a.karyawanid='".$kary."'";
		}
        $str = "select * from " . $dbname . ".sdm_pinjamassetht a left join " . $dbname . ".sdm_pinjamassetdt b on a.karyawanid=b.karyawanid  where 1=1 ".$where." order by tanggalkembali asc, tanggalpinjam asc"; 
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$color=$title=$lf='';
			if($bar['tanggalkembali']=='0000-00-00'){
				$color=" color=red";
				$title=" title='Barang belum di kembalikan.'";
			}
			$no+=1;
			$tab.="<tr class=rowcontent id=row_".$no." style=cursor:pointer ".$title.">";
			$tab.="<td align=center><font ".$color.">" . $no . "</font></td>";
			$tab.="<td align=left><font ".$color.">".$bar['kodeasset']."</font>";
			$tab.="<td align=left><font ".$color.">".$namasset[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=left><font ".$color.">" . $pemilik[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=left><font ".$color.">" . $posisiasset[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=center><font ".$color.">" . $thnperoleh[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=center><font ".$color.">" . tanggalnormal($bar['tanggalpinjam']). "</font></td>";
			$tab.="<td align=left><font ".$color.">" . $bar['keterangan'] . "</font></td>";
			if($bar['tanggalkembali']!='0000-00-00'){
				$tab.="<td align=center><font ".$color.">" . $bar['tanggalkembali'] . "</font></td>";
				$tab.="<td align=left><font ".$color.">" . $nmkar[$bar['diterima']] . "</font></td>";
				$tab.="<td align=left><font ".$color.">" . $bar['ketkembali'] . "</font></td>";
				
			}else{
				$tab.="<td></td><td></td><td><font ".$color.">Barang belum kembali</font></td>";
			}
		}
        $tab.="</tr>";
        $tab.="</table>";
		
		if($tipe=='html'){
			echo $tab;
		} else {
			$stream = $tab;
			$nop_ = "pinjaman_asset_".$nmkar[$kary];
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

	case'getkaryawan':
		$str=" select * from ".$dbname.".datakaryawan where  lokasitugas='".$kodeorg."' and tanggalkeluar='0000-00-00' order by namakaryawan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kary.="<option value=".$bar['karyawanid'].">".$bar['nik']." - ".$bar['namakaryawan']."</option>";
		}
		
        echo $kary;
        
    break;


    case'detail':
        OPEN_BOX();
        $optasset=$optpenerima= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sql = "select * from " . $dbname . ".sdm_daftarasset where posisiasset = '" . $kodeorg . "' and status='1' order by namasset asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optasset.="<option value=" . $bar['kodeasset'] . ">" . $bar['kodeasset'] . " - " . $bar['namasset'] . " - ".$bar['posisiasset']."</option>";
        }
		
		
		
        echo"
        <fieldset style=width:99%>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
		<input type=checkbox onchange=showall() id=showall>&nbsp;Tampilkan seluruh Barang
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            
            <td rowspan=2 align=center width=310px>" . $_SESSION['lang']['namabarang'] . "</td>
			<td rowspan=2 align=center>".$_SESSION['lang']['kepemilikan']."</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['posisiasset'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['tanggalpinjam']."</td>
			<td rowspan=2 align=center width=200px>" . $_SESSION['lang']['keterangan'] . "</td>
			<td rowspan=2 align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		</thead>
        
        <tr class=rowcontent>
                
            <td><select style=width:285px onchange=getdata() id=kodeasset>" . $optasset . "</select>
			<img id='kodeasset' onclick=z.elSearch('kodeasset',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
            <td><input id=pemilik class=myinputtext style=\"width:75px;\" disabled></td>
            <td><input id=posisi class=myinputtext style=\"width:75px;\" disabled></td>
            <td><input id=tahun class=myinputtext style=\"width:50px;\" disabled></td>
			<td><input id=tglpinjam type=text class=myinputtext size=10 onmousemove=setCalendar(this.id) maxlength=10 onkeypress=\"return false;\"><input id=tglpinjamlama style=display:none></td>
            <td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeypress='enterkey(event,savedetail)'; style=\"width:250px;\"></td>
			
			<td align=center><input type=hidden id=method value='insert'>
			<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event)\" src='images/upload-2-xxl.png'/>
			<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
			<img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=7 align=right>
			<button id=done class=mybutton onclick=displayList()>" . $_SESSION['lang']['selesai'] . "</button></td>
        </tr></table>
        </fieldset>";
        
        echo"
        <fieldset style=width:99%><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
		
		echo"<fieldset id=formloadfilesdetail style=width:99%;display:none><legend>" . $_SESSION['lang']['list'] . " File Upload</legend>
				<table class='sortable' cellspacing='1' border='0'  style=min-width:940px>
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
			</fieldset>";
        CLOSE_BOX();
        break;

    case'insert':
		$tglpinjam=tanggalsystem($tglpinjam);
		$tglkembali=tanggalsystem($tglkembali);
		
        #cek data ht sudah ada atau belum
        $sql = "select count(*) as jmlhrow from " . $dbname . ".sdm_pinjamassetht where kodeorg='" . $kodeorg . "' and karyawanid='".$kary."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs==0) {
            # Jika data kosong maka Insert HT dulu
			$str = "insert into " . $dbname . ".sdm_pinjamassetht (`karyawanid`,`kodeorg`,`createby`)
			values ('" . $kary . "','".$kodeorg."','" . $_SESSION['standard']['userid'] . "')";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        }
		
		#cek data dt sudah ada atau belum
        $sql = "select * from " . $dbname . ".sdm_pinjamassetdt where kodeasset='" . $kodeasset . "' and karyawanid='".$kary."' and tanggalpinjam='".$tglpinjam."'";
		$res=fetchData($sql);
		if(count($res)>0){
			exit('Error : Data sudah ada.');
		}

		#cek data sudah pernah dipinjam dan apakah sudah dikembalikan
        $sql = "select * from " . $dbname . ".sdm_pinjamassetdt where kodeasset='" . $kodeasset . "' and tanggalkembali='0000-00-00'";
		$res=fetchdata($sql);
		if(count($res)>0){
			exit("Error : Barang belum dikembalikan oleh : ".$nmkar[$res[0]['karyawanid']]."\ntanggal peminjaman : ".$res[0]['tanggalpinjam']);
		}

		# Jika data sudah ada maka langsung Insert DT
        $str = "insert into " . $dbname . ".sdm_pinjamassetdt (`karyawanid`,`kodeasset`, `keterangan`, `tanggalpinjam`,`createby`)
        values ('" . $kary . "','" . $kodeasset . "','" . $keterangan . "','".$tglpinjam."','" . $_SESSION['standard']['userid'] . "')";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        
	break;
	case'update':
		$tglpinjam=tanggalsystem($tglpinjam);
		$tglpinjamlama=tanggalsystem($tglpinjamlama);
        $str = "update " . $dbname . ".sdm_pinjamassetdt set tanggalpinjam='".$tglpinjam."', keterangan='" . $keterangan . "' where karyawanid='".$kary."' and kodeasset='".$kodeasset."' and tanggalpinjam='".$tglpinjamlama."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
    case'delete':
        $str = "delete from " . $dbname . ".sdm_pinjamassetht where karyawanid='".$kary."' and kodeorg='" . $kodeorg . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

    break;

    case'deletedetail':
	
        $str = "delete from " . $dbname . ".sdm_pinjamassetdt where karyawanid ='".$kary."' and kodeasset='" . $kodeasset . "' and tanggalpinjam='" . $tglpinjam . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		# delete file
		$sql = "select * from " . $dbname . ".listfile_sdm_pinjamasset where karyawanid ='".$kary."' and kodeasset='" . $kodeasset . "' and tanggalpinjam='" . $tglpinjam . "'";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$str="delete from ".$dbname.".listfile_sdm_pinjamasset where karyawanid ='".$kary."' and kodeasset='" . $kodeasset . "' and tanggalpinjam='" . $tglpinjam . "' and namafile='".$bar['namafile']."'";
			try{$owlPDO->exec($str);
				$pathx = $path.$bar['namafile'];
				unlink($pathx);
			}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        }
		
	break;
	case'savekembali':
		$tglpinjam=tanggalsystem($tglpinjam);
		$tglkembali=tanggalsystem($tglkembali);
		if($tglkembali<$tglpinjam){
			exit('Error : Tanggal pengembalian tidak boleh lebih kecil dari tanggal pinjam.');
		}
		
        $str = "update " . $dbname . ".sdm_pinjamassetdt set tanggalkembali='".$tglkembali."', ketkembali='" . $ketkembali . "', diterima='".$penerima."' where karyawanid='".$kary."' and kodeasset='".$kodeasset."' and tanggalpinjam='".$tglpinjam."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	
	
    case'loaddatadetail':
		
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:940px>
            <thead><tr class=rowheader>
			<td rowspan=2 align=center>No</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['kodeasset'] . "</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['kepemilikan']."</td>
			<td rowspan=2 align=center width=50px>" . $_SESSION['lang']['posisiasset'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td rowspan=2 align=center width=75px>".$_SESSION['lang']['tanggalpinjam']."</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td colspan=3 align=center>" . $_SESSION['lang']['back'] . "</td>
			<td rowspan=2 align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
            </tr>
            <tr>
			<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center>" . $_SESSION['lang']['penerima'] . "</td>
			<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr>
			
			</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".sdm_pinjamassetht a left join " . $dbname . ".sdm_pinjamassetdt b on a.karyawanid=b.karyawanid  where a.karyawanid='" . $kary . "' order by tanggalkembali asc, tanggalpinjam asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=12 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$color=$title=$lf='';
				if($bar['tanggalkembali']=='0000-00-00'){
					$color=" color=red";
					$title=" title='Barang belum di kembalikan.'";
				}
				$lf=" onclick=formloadfilesdetail('".$bar['karyawanid']."','".$bar['kodeasset']."','".tanggalnormal($bar['tanggalpinjam'])."')";
				$no+=1;
				$tab.="<tr class=rowcontent style=cursor:pointer ".$title.">";
				$tab.="<td ".$lf." align=center><font ".$color.">" . $no . "</font></td>";
				$tab.="<td ".$lf." align=left><font ".$color.">".$bar['kodeasset']."</font></td>";
				$tab.="<td ".$lf." align=left><font ".$color.">" . $namasset[$bar['kodeasset']] . "</font></td>";
				$tab.="<td ".$lf." align=left><font ".$color.">" . $pemilik[$bar['kodeasset']] . "</font></td>";
				$tab.="<td ".$lf." align=left><font ".$color.">" . $posisiasset[$bar['kodeasset']] . "</font></td>";
				$tab.="<td ".$lf." align=center><font ".$color.">" . $thnperoleh[$bar['kodeasset']] . "</font></td>";
				$tab.="<td ".$lf." align=center><font ".$color.">" . tanggalnormal($bar['tanggalpinjam']). "</font></td>";
				$tab.="<td ".$lf." align=left><font ".$color.">" . $bar['keterangan'] . "</font></td>";
				$tab.="<td ".$lf." align=center>" . ($bar['tanggalkembali']=='0000-00-00'?'':tanggalnormal($bar['tanggalkembali'])) . "</td>";
				$tab.="<td ".$lf." align=left>" . $nmkar[$bar['diterima']] . "</td>";
				$tab.="<td ".$lf." align=left>" . $bar['ketkembali'] . "</td>";
				$tab.="<td align=right width=50px>";
				if($bar['tanggalkembali']=='0000-00-00'){
					if($bar['tanggalpinjam']<$today){
						
					}else{
						$tab.="<img src=images/application/application_edit.png class=resicon  title='Edit' 
							onclick=\"editdetail('".$bar['kodeasset']."','".$bar['karyawanid']."','".tanggalnormal($bar['tanggalpinjam'])."','".$bar['keterangan']."','".$pemilik[$bar['kodeasset']]."','".$posisiasset[$bar['kodeasset']]."','".$thnperoleh[$bar['kodeasset']]."');\">&nbsp;";
						$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' 
							onclick=\"deletedetail('".$bar['kodeasset']."','".$bar['karyawanid']."','".$bar['tanggalpinjam']."');\">&nbsp;";
					}
				$tab.="<img src=images/plus.png class=resicon  title='Form Pengembalian' 
						onclick=\"kembali('".$bar['karyawanid']."','".$bar['kodeasset']."','".$bar['tanggalpinjam']."');\">";
				} else{
				$tab.="</td>";
				}
			}
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
    break;
	
	case'kembali':
		$optpenerima="<option value=" .$_SESSION['standard']['userid'].">" .$nmkar[$_SESSION['standard']['userid']]."</option>";
		$sql = "select * from " . $dbname . ".datakaryawan where tanggalkeluar = '0000-00-00' and tipekaryawan in ('0','7','8') order by namakaryawan asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optpenerima.="<option value=" . $bar['karyawanid'] . ">".$bar['namakaryawan']."</option>";
        }
		
		$tab = "<table >";
		$tab.= "<tr >
					<td><b>" . $_SESSION['lang']['namakaryawan'] . "</b></td><td><b>:</b></td>
					<td><b>" . $nmkar[$kary] . "</b><input style=display:none id=nkary value=".$kary."></td>
				</tr>";
		$tab.= "</table><hr>";
		
        $tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:840px>
            <thead><tr class=rowheader>
			<td rowspan=2 align=center>No</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['kodeasset'] . "</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['kepemilikan']."</td>
			<td rowspan=2 align=center width=50px>" . $_SESSION['lang']['posisiasset'] . "</td>
			<td rowspan=2 align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td rowspan=2 align=center width=75px>".$_SESSION['lang']['tanggalpinjam']."</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td colspan=3 align=center>" . $_SESSION['lang']['back'] . "</td>
			<td rowspan=2 align=center>" . $_SESSION['lang']['action'] . "</td>
            </tr>
            <tr>
			<td align=center style='width:75px;'>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center width=135px>" . $_SESSION['lang']['penerima'] . "</td>
			<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr>
			
			</thead>";
        $no = 0; $where='';
		if($kary!=''){
			$where.=" and a.karyawanid='".$kary."'";
		}
		if($kodeasset!='undefined'){
			$where.=" and b.kodeasset='".$kodeasset."'";
		}
		if($tglpinjam!='undefined'){
			$where.=" and b.tanggalpinjam='".$tglpinjam."'";
		}
		
        $str = "select * from " . $dbname . ".sdm_pinjamassetht a left join " . $dbname . ".sdm_pinjamassetdt b on a.karyawanid=b.karyawanid  where 1=1 ".$where." order by tanggalkembali asc, tanggalpinjam asc"; 
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$color=$title=$lf='';
			if($bar['tanggalkembali']=='0000-00-00'){
				$color=" color=red";
				$title=" title='Barang belum di kembalikan.'";
			}
			$no+=1;
			$tab.="<tr class=rowcontent id=row_".$no." style=cursor:pointer ".$title.">";
			$tab.="<td align=center><font ".$color.">" . $no . "</font></td>";
			$tab.="<td align=left><font ".$color.">".$bar['kodeasset']."</font>";
			$tab.="<td align=left><font ".$color.">".$namasset[$bar['kodeasset']] . "</font>
					<input id=kodeasset".$no." value=".$bar['kodeasset']." style='display:none' hidden></td>";
			$tab.="<td align=left><font ".$color.">" . $pemilik[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=left><font ".$color.">" . $posisiasset[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=center><font ".$color.">" . $thnperoleh[$bar['kodeasset']] . "</font></td>";
			$tab.="<td align=center><font ".$color.">" . tanggalnormal($bar['tanggalpinjam']). "</font>
					<input id=tglpinjam".$no." value=".tanggalnormal($bar['tanggalpinjam'])." style=display:none></td>";
			$tab.="<td align=left><font ".$color.">" . $bar['keterangan'] . "</font></td>";
			if($bar['tanggalkembali']!='0000-00-00'){
				$tab.="<td align=center><font ".$color.">" . $bar['tanggalkembali'] . "</font></td>";
				$tab.="<td align=left><font ".$color.">" . $nmkar[$bar['diterima']] . "</font></td>";
				$tab.="<td align=left><font ".$color.">" . $bar['ketkembali'] . "</font></td>";
				
			}else{
				$tab.="<td>
						<input type='text' style='width:75px;' class='myinputtext' id=tglkembali".$no." onmousemove='setCalendar(this.id)' onkeypress='return false'; />
						</td>";
				$tab.="<td><select id=penerima".$no." style=\"width:80%;\">".$optpenerima."</select>
						<img id='penerima".$no."' onclick=z.elSearch('penerima".$no."',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>";
				$tab.="<td>
						<input type=text class=myinputtext style=\"width:98%;\" id=ketkembali".$no." onkeypress=\"return tanpa_kutip(event);\"></td>";
			}
			$tab.="<td align=center>";
			if($bar['tanggalkembali']=='0000-00-00'){
				$tab.="<img src=images/save.png class=resicon  title='Save' 
					onclick=\"savekembali($no);\">";
			} else{
			$tab.="</td>";
			}
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
    break;
		
    case'getdata':
        $sql = "select * from " . $dbname . ".sdm_daftarasset where kodeasset='".$kodeasset."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
			$q.=$bar['kodeorg'];
			$w.=$bar['posisiasset'];
			$t.=$bar['tahunperolehan'];
		}

		echo $q."######".$w."######".$t; 
	break;
	
	case'showall':
		$whr='';
		if($show==0){
			$whr.=" and kodeorg='".$kodeorg."'";
		}
		$optasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sql = "select * from " . $dbname . ".sdm_daftarasset where status='1' ".$whr." order by namasset asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
			  $optasset.="<option value=" . $bar['kodeasset'] . ">" . $bar['kodeasset'] . " - " . $bar['namasset'] . " - " . $bar['posisiasset'] . "</option>";
		}
		
		echo $optasset; 
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
        
	 
		if ($karysch != '') {
            $where.=" and c.namakaryawan like '%" . $karysch . "%' ";
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

        $sql = "SELECT a.*,b.*, a.karyawanid as karyawanid, c.namakaryawan FROM " . $dbname . ".sdm_pinjamassetht a 
		left join ".$dbname.".sdm_pinjamassetdt b on a.karyawanid=b.karyawanid 
		left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid where 1=1 " . $where . " group by a.karyawanid";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
		
        $str = "SELECT a.*,b.*, a.karyawanid as karyawanid, c.namakaryawan FROM " . $dbname . ".sdm_pinjamassetht a 
		left join ".$dbname.".sdm_pinjamassetdt b on a.karyawanid=b.karyawanid 
		left join ".$dbname.".datakaryawan c on a.karyawanid=c.karyawanid 
		where 1=1 " . $where . " group by a.karyawanid order by a.kodeorg asc, b.karyawanid asc limit " . $offset . "," . $limit . "";// exit('error'.$str);
		$tab = "";
        $no = $maxdisplay;

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
        $res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			while ($bar = $res->fetch()) {
				$isi = '';
				$no+=1;
				$tab.="<tr class=rowcontent  id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
				$tab.="<td align=left>" . $nmkar[$bar['karyawanid']] . "</td>";
				$tab.="<td align=left>" . $kodejab[$jbt[$bar['karyawanid']]] . "</td>";
				

				$isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
					onclick=\"edit('" . $bar['karyawanid'] . "','" . $bar['kodeorg'] . "','" . $nmkar[$bar['karyawanid']] . "');\" ></td>";
				if ($bar['kodeasset'] == '') {
					$isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
						onclick=\"del('" . $bar['karyawanid'] . "','" . $bar['kodeorg'] . "');\" ></td>";
				}else{
					$isi.="<td></td>";
				}
				$isi.="<td align=center><img src=images/plus.png class=resicon  title='Form Pengembalian' 
							onclick=\"kembali('".$bar['karyawanid']."');\"></td>";
				$isi.="<td align=center><img src=images/zoom.png class=resicon  title='View' 
							onclick=\"html('".$bar['karyawanid']."','html');\"></td>";
				$tab.=$isi;

				$tab.="</tr>";
			}
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
                     <tr><td colspan=8 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

        break;
		
	case 'showupload':
		$tab="";
		$tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<label id='karyupload' style='display:none'>".$kary."</label>
					<label style='font-weight:bold'>".$nmkar[$kary]."</label>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namabarang']."</td>
				<td>:</td>
				<td>
					<label id='kodeassetupload' style=display:none>".$kodeasset."</label>
					<label style='font-weight:bold'>".$namasset[$kodeasset]."</label>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalpinjam']."</td>
				<td>:</td>
				<td>
					<label id='tglpinjamupload' style='font-weight:bold'>".$tglpinjam."</label>
				</td>
			</tr>
			<tr><td colspan=4><hr></td></tr>
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
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $kary."_".$kodeasset."_".$tgl."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 250000){
						$str = "insert into ".$dbname.".listfile_sdm_pinjamasset values ('','".$data['kary']."','".$data['kodeasset']."','".tanggalsystem($data['tglpinjam'])."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
		$str="select * from ".$dbname.".sdm_pinjamassetdt where karyawanid = '".$kary."' and kodeasset='".$kodeasset."' and tanggalpinjam='".tanggalsystem($tglpinjam)."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$tglpjm = $barv['tanggalpinjam'];
		}
		$posting='';
		if($tglpjm<$today){
			$posting=1;
		}
		
		$str="select * from ".$dbname.".listfile_sdm_pinjamasset where karyawanid = '".$kary."' and status='1' and kodeasset='".$kodeasset."' and tanggalpinjam='".tanggalsystem($tglpinjam)."'";
		//exit('error'.$str);
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr id='ppDetailTable' class=rowcontent>
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
				
				$tab.="<td style='text-align:left;cursor:pointer' onclick=viewfile('event','".$val['namafile']."')>".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$kary."','".$kodeasset."','".tanggalsystem($tglpinjam)."','".$val['namafile']."');\" >";
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
		$str="delete from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
		try
		{
			$owlPDO->exec($str);
			$path = $path.$namafile;
			unlink($path);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case 'deletefileall':
		$str="select * from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$path2 = $path.$bar['namafile'];
			unlink($path2);
		}
		
		$str="delete from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
}
?>	
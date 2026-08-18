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

$divsch = checkPostGet('divsch', '');
$tglsch = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$arrmilik=array("0"=>"sewa/kontrak","1"=>"milik sendiri");
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nsupir=makeOption($dbname,'vhc_5operator','karyawanid,nama');
$nkeg=makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
$jnsby=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');

$jab = getPostingJabatan('traksirkh');	
$tmpTgl = explode('-',$tgl);	
$path	= "fileupload/ijin_ops/";

switch ($method) {

    case'excel':
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td width=5px>:</td>
					<td>".$unitexp." - " . $nmorg[$unitexp] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['periode'] . "</td>
					<td width=5px>:</td>
					<td>" .$perexp . "</td>
				</tr>
				
				</table>";
				
        $tab.="<table cellpadding=1 cellspacing=1 border=1 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center width=150px>" . $_SESSION['lang']['kodevhc'] . "</td>
			<td align=center>" . $_SESSION['lang']['nopol'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td align=center>".$_SESSION['lang']['kepemilikan']."</td>
			<td align=center width=50px>".$_SESSION['lang']['kodetraksi']."</td>
			<td align=center width=75px>".$_SESSION['lang']['biaya']."</td>
			<td align=center width=200px>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select a.*,b.nopol,b.tahunperolehan,b.kepemilikan,b.kodetraksi from " . $dbname . ".vhc_byyijinops a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc  where a.kodeorg='" . $unitexp . "' and periode='".$perexp."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['kodevhc'] . "</td>";
            $tab.="<td align=left>" . $bar['nopol']. "</td>";
            $tab.="<td align=center>" . $bar['tahunperolehan'] . "</td>";
            $tab.="<td align=left>" . $arrmilik[$bar['kepemilikan']] . "</td>";
            $tab.="<td align=left>" . $bar['kodetraksi'] . "</td>";
            $tab.="<td align=right>" . number_format($bar['biaya'],2) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
			$total+=$bar['biaya'];
        }
        $tab.="</tr>";
        $tab.="</table>";

        $stream = $tab;
		
        $nop_ = "biaya ijin operasional_" . date('Ymd_His');
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

        break;




    case'html':
	
	
        $tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td>".$notransaksi."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td width=5px>:</td>
					<td>".$kodeorg." - " . $nmorg[$kodeorg] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['periode'] . "</td>
					<td width=5px>:</td>
					<td>" .$tgl . "</td>
				</tr>
				
				</table><hr>";
		
		$countApprove = getCountApproval('IOPS',$kodeorg);
		$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		
		$str=" select * from ".$dbname.".vhc_byyijinops where  notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		$tab.= "<table border=0 cellspacing=1 class=sortable width=100%>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
					for($i=1;$i<=$countApprove;$i++)
					{
						$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
					}
					
			$tab.= "
				</tr>
				</thead>
				<tbody>";
				$tab.= "<tr class=rowcontent>
						<td>".$nmkar[$bar['updateby']]."<br>
							".$bar['updatetime']."</td>";
					for($i=1;$i<=$countApprove;$i++)
					{
						$arrApp = detailApprove($i,$notransaksi,'IOPS');
						
						if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00')
						{
							$tngl='';
						}
						else
						{
							$tngl=tanggalnormal($arrApp['tanggal']);
						}
						
						if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0))
						{
							$tab.= "<td>".$arrApp['nama']."
								<br />".$arrHsl[$arrApp['status']]."
								<br>".$tngl."
							</td>";
						}
						else
						{
							$tab.= "<td>&nbsp;</td>";
						}
					}
					
				
				$tab.= "</tbody>
				</table><hr>";
		
		
		$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
            <thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center width=150px>" . $_SESSION['lang']['kodevhc'] . "</td>
			<td align=center>" . $_SESSION['lang']['nopol'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td align=center>".$_SESSION['lang']['kepemilikan']."</td>
			<td align=center width=50px>".$_SESSION['lang']['kodetraksi']."</td>
			<td align=center width=75px>".$_SESSION['lang']['biaya']."</td>
			<td align=center width=200px>" . $_SESSION['lang']['keterangan'] . "</td>
        </tr>
        </thead>";
        $no = 0;
        $str = "select a.*,b.nopol,b.tahunperolehan,b.kepemilikan,b.kodetraksi from " . $dbname . ".vhc_byyijinops a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc  where a.kodeorg='" . $kodeorg . "' and periode='".$tgl."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['kodevhc'] . "</td>";
            $tab.="<td align=left>" . $bar['nopol']. "</td>";
            $tab.="<td align=center>" . $bar['tahunperolehan'] . "</td>";
            $tab.="<td align=left>" . $arrmilik[$bar['kepemilikan']] . "</td>";
            $tab.="<td align=left>" . $bar['kodetraksi'] . "</td>";
            $tab.="<td align=right>" . number_format($bar['biaya'],2) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
			$total+=$bar['biaya'];
        }
        $tab.="</tr>";
		$tab.="<tr class=rowcontent>
				<td colspan=6 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>
				<td align=right><b>" . number_format($total,2). "</b></td>
				<td><b></b></td>
				</tr>";
		
        $tab.="</table><hr>";
		
		
		$tab.="<table class='sortable' cellspacing='1' border='0'  width='100%'>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesview'>
					</tbody>
				</table>
			";


        echo $tab;


        break;

	
	case'getnotransaksi':
        
		##cek apakah data sudah posting atau belum
		$str=" select count(*) as posting from ".$dbname.".vhc_byyijinops where  kodeorg='".$kodeorg."' and periode='".$tgl."' and posting=1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if($bar['posting']>0){
			exit("Warning : Transaksi ".$kodeorg." diperiode ".$tgl." sudah di posting ");
		}
		
		$str=" select notransaksi from ".$dbname.".vhc_byyijinops where  kodeorg='".$kodeorg."' and periode like '".$tgl."%' "
				. " order by notransaksi desc limit 1 ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$notranlama=$bar['notransaksi'];
		// exit('error'.$notranlama);
		if($notranlama==''){
			$str = "SELECT substr(notransaksi,-4) as notransaksi FROM " . $dbname . ".vhc_byyijinops where kodeorg='".$kodeorg."' and periode like '".substr($tgl,0,4)."%' order by notransaksi desc limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if(intval($bar['notransaksi'])==0){
				$noawal=1;
			}else{
				$noawal = intval($bar['notransaksi'])+1;
			}
			$notranbaru=$kodeorg."/IOPS/".$tmpTgl[0]."/".$tmpTgl[1]."/".addZero($noawal,4);
		}
		else{
			$notranbaru=$notranlama;
		}
   
        echo $notranbaru;
        
    break;


    case'detail':

        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_byyijinops where kodeorg='" . $kodeorg . "' and periode ='".$tgl."' and posting=1";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Transaksi periode " . $tgl . " sudah di posting");
        }

        OPEN_BOX();
        $optvhc=$optjnsby= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sql = "select * from " . $dbname . ".vhc_5master where kodeorg like '" . $kodeorg . "%' and status='1'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optvhc.="<option value=" . $bar['kodevhc'] . ">" . $bar['kodevhc'] . "</option>";
        }
		
		$sql = "select * from " . $dbname . ".keu_5aruskas where tipetransaksi ='K' and status='1' and level='3' order by noaruskas asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optjnsby.="<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
        }
				
        echo"
        <fieldset style=width:99%>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            
            <td align=center width=150px>" . $_SESSION['lang']['kodevhc'] . "</td>
			<td align=center>" . $_SESSION['lang']['nopol'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td align=center>".$_SESSION['lang']['kepemilikan']."</td>
			<td align=center width=50px>".$_SESSION['lang']['kodetraksi']."</td>
			<td align=center width=50px>".$_SESSION['lang']['jenisbiaya']."</td>
			<td align=center width=75px>".$_SESSION['lang']['biaya']."</td>
			<td align=center width=200px>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
        </tr></thead>
        
        <tr class=rowcontent>
                
            <td><select style=width:125px onchange=getdata() id=kodekend>" . $optvhc . "</select>
			<img id='kodekend' onclick=z.elSearch('kodekend',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
            <td width:100px id=nopol ></td>
            <td id=tahunperolehan></td>
            
            <td id=kepemilikan></td>
            <td id=kodetraksi></td>
            <td><select id=jenisbiaya style=\"width:150px;\">".$optjnsby."</select></td>
            <td><input id=biaya class=myinputtextnumber onkeypress='return angka_doang(event)'; style=\"width:75px;\"></td>
			<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeypress='enterkey(event,savedetail)'; style=\"width:250px;\"></td>
			
			<td align=center><input type=hidden id=method value='insert'>
				
				<img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
                <img title='" . $_SESSION['lang']['clear'] . "' class=resicon onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=9 align=right>
			<button class=mybutton onclick='showupload(event)'>Upload Files</button> &nbsp
			<button id=done class=mybutton onclick=displayList()>" . $_SESSION['lang']['selesai'] . "</button></td>
        </tr></table>
        </fieldset>";
        // style='float:left;'
        echo"
        <fieldset style=width:99%><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
		
		echo"<fieldset style=width:99%><legend>" . $_SESSION['lang']['list'] . " File Upload</legend>
				<table class='sortable' cellspacing='1' border='0'  style=min-width:930px>
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
        #cek data
        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_byyijinops where "
                . " kodevhc='" . $kodekend . "' and notransaksi ='".$notransaksi."' and kodeorg='".$kodeorg."' and periode='" . $tgl . "' and jenisbiaya='".$jenisbiaya."'";
		//exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Transaksi unit : " . $kodekend . " jenis biaya ".$jenisbiaya." - ".$jnsby[$jenisbiaya]." sudah ada.");
        }

        $str = "insert into " . $dbname . ".vhc_byyijinops (`notransaksi`,`kodeorg`, `periode`, 
                `kodevhc`, `keterangan`, `jenisbiaya`,`biaya`,`updateby`)
            values ('".$notransaksi."','" . $kodeorg . "','" . $tgl . "',"
                . "'" . $kodekend . "','" . $keterangan . "','".$jenisbiaya."','" . $biaya . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;


    case'delete':

        $str = "delete from " . $dbname . ".vhc_byyijinops where notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and periode='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		
		$sql = "select * from " . $dbname . ".listfilebyyijinops where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$str="delete from ".$dbname.".listfilebyyijinops where notransaksi='".$notransaksi."' and namafile='".$bar['namafile']."'";
			try{
				$owlPDO->exec($str);
				$path = "fileupload/ijinops/".$bar['namafile'];
				unlink($path);
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
        }

        break;

    case'deletedetail':

        $str = "delete from " . $dbname . ".vhc_byyijinops where notransaksi ='".$notransaksi."' and kodeorg='" . $kodeorg . "' and kodevhc='" . $kodekend . "' and periode='" . $tgl . "' and jenisbiaya='".$jenisbiaya."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='IOPS' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch())
		{
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
					<td><select id=kepada style='width:150px;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=center><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	
    case'ajukan':
	
		if($kepada=='' or $notransaksi==''){
			exit('Error : Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".vhc_byyijinops set posting='1',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','IOPS','1','" . $kepada."','0','','','')";
		
		// exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

		case'unposting':
		//cek tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$kodeorg."' and periode ='".tgl."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
			$tutup=$bar['tutupbuku'];
		if($tutup==1){
			exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".$tgl." unit ".$kodeorg." sudah di tutup.");
		}
        $str = "update " . $dbname . ".vhc_byyijinops set posting='0',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "' and periode='" . $tgl . "' ";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
    case'loaddatadetail':
		
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=min-width:930px>
            <thead><tr class=rowheader>
            <td align=center width=50px>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
			<td align=center>" . $_SESSION['lang']['nopol'] . "</td>
			<td align=center width=50px>".$_SESSION['lang']['tahunperolehan']."</td>
			<td align=center width:75px>".$_SESSION['lang']['kepemilikan']."</td>
			<td align=center width=50px>".$_SESSION['lang']['kodetraksi']."</td>
			<td align=center>".$_SESSION['lang']['jenisbiaya']."</td>
			<td align=center width:75px>".$_SESSION['lang']['biaya']."</td>
			<td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center width=50px>" . $_SESSION['lang']['action'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select a.*,b.nopol,b.tahunperolehan,b.kepemilikan,b.kodetraksi from " . $dbname . ".vhc_byyijinops a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc  where a.notransaksi='" . $notransaksi . "' and a.kodeorg='" . $kodeorg . "' and periode='".$tgl."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		// exit('error'.$row);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=10 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			
		

			while ($bar = $res->fetch()) {
				$no+=1;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . $bar['kodevhc'] . "</td>";
				$tab.="<td align=left>" . $bar['nopol']. "</td>";
				$tab.="<td align=center>" . $bar['tahunperolehan'] . "</td>";
				$tab.="<td align=left>" . $arrmilik[$bar['kepemilikan']] . "</td>";
				$tab.="<td align=left>" . $bar['kodetraksi'] . "</td>";
				$tab.="<td align=left>" . $bar['jenisbiaya'] . " - ".$jnsby[$bar['jenisbiaya']]."</td>";
				$tab.="<td align=right>" . number_format($bar['biaya'],2) . "</td>";
				$tab.="<td align=left>" . $bar['keterangan'] . "</td>";
				
				$tab.="<td align=center>";
				$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . $bar['kodevhc'] . "','" . $bar['periode'] . "','" . $bar['kodeorg'] . "','" . $bar['notransaksi'] . "','".$bar['jenisbiaya']."');\" >
					</td>";
			}
		}
        $tab.="</tr>";
        $tab.="</table>";


        echo $tab;
        break;



    case'getdata':
		
        $sql = "select * from " . $dbname . ".vhc_5master where kodevhc = '" . $kodekend . "' and status='1'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
			$q.=$bar['nopol'];
			$w.=$bar['tahunperolehan'];
			//$e.=$bar['nomormesin'];
			$r.=$arrmilik[$bar['kepemilikan']];
			$t.=$bar['kodetraksi'];
		}
		
		echo $q."######".$w."######".$r."######".$t;
		// exit('error '.$xxx);
	
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
        if ($tglsch != '') {
            $where.=" and periode='" . $tglsch . "' ";
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

        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_byyijinops where 1=1 " . $where . " group by kodeorg,periode";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;

        $str = "SELECT sum(biaya) as biaya,notransaksi, kodeorg, periode, updateby, posting FROM " . $dbname . ".vhc_byyijinops
		where 1=1 " . $where . " group by notransaksi, kodeorg,periode  order by periode desc, kodeorg asc limit " . $offset . "," . $limit . "";
         // exit('error '.$str);
		$tab = "";
        $no = $maxdisplay;

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
			
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['notransaksi'] . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            
            $tab.="<td align=center>" . $bar['periode'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['biaya'],2) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";

            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['notransaksi'] . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['notransaksi'] . "');\" ></td>";               
			   $isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan ???' 
                    onclick=\"form_ajukan('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $no . "');\" ></td>";
            }else{
				$isi.="<td></td><td></td><td></td>";
			}
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' onclick=\"html('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "');\" ></td>";

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
                     <tr><td colspan=10 align=center>";

        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }

        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";



        echo $tab . "####" . $footd;

        break;
		
	case 'showupload':
		$tab="";
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>
					<label id='noppupload' style='font-weight:bold'>".$notransaksi."</label>
				</td>
			</tr>
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
	
	case 'submitfile':
		
		$str="select * from ".$dbname.".vhc_byyijinops where notransaksi = '".$notransaksi."'";
		$resv=fetchData($str);
		if(count($resv)==0){
			exit('Error : Isikan detail transaksi terlebih dahulu.');
		}
		
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{	
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				//$file_tmpname = $_FILES['file']['tmp_name'];		
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$str = "insert into ".$dbname.".listfilebyyijinops values ('','".$data['notransaksi']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						try
						{
							$owlPDO->exec($str);
							if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
							file_put_contents($path.$filename,$file_tmpname);
							//move_uploaded_file($file_tmpname,"fileupload/ijinops/$filename");
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
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
	break;
	
	case 'loadfiles':
		$no = 0;
		$tab = "";
		$str="select * from ".$dbname.".vhc_byyijinops where notransaksi = '".$notransaksi."'";
		$resv=fetchData($str);
		foreach($resv as $bar => $barv){
			$posting = $barv['posting'];	
		}
		
		$str="select * from ".$dbname.".listfilebyyijinops where notransaksi = '".$notransaksi."' and status='1'";
		$res=fetchData($str);
		if(empty($res))
		{
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		else
		{
			foreach($res as $key=>$val)
			{
				$no++;
				$tab.="<tr id='ppDetailTable' class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
					
				if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.png')
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
					</td>";
				}
				elseif($val['formaticon']=='.pdf')
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
					</td>";
				}
				elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
					</td>";
				}
				elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
					</td>";
				}
				else
				{
					$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
					</td>";
				}
				
				$tab.="<td style='text-align:left'>".$val['namafile']."</td>
					<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
				if($posting==0){
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$notransaksi."','".$val['namafile']."');\" >";
				}
				$tab."	</td>
				</tr>";
			}	
		}
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
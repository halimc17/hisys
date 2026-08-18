<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method             = checkPostGet('method', '');
$kodeorg            = checkPostGet('kodeorg', '');
$periode            = checkPostGet('periode', '');
$supplier           = checkPostGet('supplier', '');
$nospb              = checkPostGet('nospb', '');
$blok               = checkPostGet('blok', '');
$tujuan             = checkPostGet('tujuan', '');
$harga_muat         = checkPostGet('harga_muat', '');
$harga_angkut       = checkPostGet('harga_angkut', '');
$tanggal            = tanggalsystemn(checkPostGet('tanggal', ''));
$tglheader            = tanggalsystemn(checkPostGet('tgl', ''));
$divisi             = checkPostGet('divisi', '');
$pekerjaan          = checkPostGet('pekerjaan', '');
$kgwbdet            = checkPostGet('kgwbdet', '');
$rp_muat            = checkPostGet('rp_muat', '');
$rp_angkut          = checkPostGet('rp_angkut', '');
$kgwb               = checkPostGet('kgwb', '');

$rp_muat            =str_replace(",","",$rp_muat);
$rp_angkut          =str_replace(",","",$rp_angkut);
$kgwbdet            =str_replace(",","",$kgwbdet);
$kgwb               =str_replace(",","",$kgwb);

$divsch             = checkPostGet('divsch', '');
$tglsch             = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch            = checkPostGet('bloksch', '');
$unitexp            = checkPostGet('unitexp', '');
$perexp             = checkPostGet('perexp', '');
$bjr                = checkPostGet('bjr', '');
$kgkebun            = checkPostGet('kgkebun', '');


$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jab = getPostingJabatan('rekappnn');	

switch ($method) {
	case'getdivisi':
        $optorg = "<option value=''>".$_SESSION['lang']['pilihdata'] . "</option>";
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=6 and induk='".$kodeorg."'"; #exit("error".$str);
		$res = fetchData($str);
		foreach($res as $key => $val){
			$optorg.="<option value=".$val['kodeorganisasi'].">".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		echo $optorg;
	break;
		
	case'deletedetail':
        $str = "delete from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and nospb='" . $nospb . "' ";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'insert':
		$str = "select * from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '".$kodeorg."' and periode = '".$periode."' and nospb = '".$nospb."'";
		if(count(fetchdata($str))==0){
			#jika belum ada di ht, insert dulu
			$data = array();
			$data = array(
					'kodeorg' => $kodeorg,
					'divisi' => $divisi,
					'periode' => $periode,
					'tanggal' => $tanggal,
					'nospb' => $nospb,
					'supplier' => $supplier,
					'posting' => '0',
					'totalkgwb' => $kgwb,
					'createby' => $_SESSION['standard']['userid'],
					'createtime' => date('Y-m-d H:i:s'),
					'updateby' => $_SESSION['standard']['userid']
				);
				
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_rekapangkutantbsht',$data,$cols);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$arrdata=array('muat'=>$tujuan,'angkut'=>$tujuan);
		#$str = "delete from ".$dbname.".kebun_5hargaangkut where blok = '".$blok."'";
		#$owlPDO->exec($str);
		
		$str = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_spb_vw where nospb = '".$nospb."' and blok='".$blok."'";
		$res = fetchdata($str);
		$kgttl = $res[0]['kgwb'];
		
		foreach($arrdata as $key => $val){
			if($key=='muat'){$rp=$rp_muat;}else{$rp=$rp_angkut;}
			
			$str = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = '".$nospb."' and jenis='".$key."' and blok='".$blok."'";
			$res = fetchdata($str);
			$kgtersave = $res[0]['kgwb'];
			
			$selisih = $kgttl - ($kgtersave+$kgwbdet);
			if($selisih<0){
				exit("Error : Kg melebihi total Kg di SPB :\nTotal Kg SPB = ".number_format($kgttl)."\nKg tersimpan = ".number_format($kgtersave)."\nKg diinput = ".number_format($kgwbdet)."\nSelisih = ".number_format($selisih)."");
			}
			
			$data = array(
				'nospb' => $nospb,
				'jeniskegiatan' => $pekerjaan,
				'jenis' => $key,
				'tujuan' => $val,
				'blok' => $blok,
				'kgwb' => $kgwbdet,
				'rupiah' => $rp
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_rekapangkutantbsdt',$data,$cols);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;
		
	case'getharga':
		$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '".$blok."'";
		if(count(fetchdata($str))==0){
			exit("Warning : Harga belum ada, silahkan di tambah melalui menu : Kebun - Setup - Harga Loading dan Angkut TBS");
		}
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if($bar['tujuan']==$tujuan){
				if($bar['jenis']=='muat'){
					$rpmuat=$bar['harga'];
				}
				if($bar['jenis']=='angkut'){
					$rpangkut=$bar['harga'];
				}
			}
		}
		
		echo $rpmuat."##".$rpangkut;
	break;
	case'getdetailspb':
		$opttujuan="<option value=''></option>";
		$arrmuat=array('tphpks'=>'TPH - PMKS','rampks'=>'RAMP - PMKS');
		foreach($arrmuat as $val =>$key){
			@$opttujuan.="<option value=".$val.">".$key."</option>";
		}
		
		$str = "select * from " . $dbname . ".kebun_spb_vw where nospb = '".$nospb."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $no='';$tab="";
		while ($bar = $res->fetch()) {
			$no++;
			$optdriver=makeOption($dbname,'pabrik_timbangan','notransaksi,supir',"notransaksi='".$bar['notiket']."'");
            $notiket=$bar['notiket'];
            $driver=$optdriver[$bar['notiket']];
            $divisi=$bar['divisi'];
            $tanggal=$bar['tanggal'];
            $nopol=$bar['nokendaraan'];
            @$jjg+=$bar['jjg'];
            @$kgwb+=$bar['kgwb'];
			
			$tab.="<tr class=rowcontent id=tr_".$no.">
				<td align=center>".$no."</select>
				<td><input type=text class=myinputtext disabled id=blok_".$no." style=\"width:100px;\" / value='".$bar['blok']."'></td>
				<td><input class=myinputtextnumber id=thntnm_".$no." disabled  style=\"width:50px;\" value='".$bar['tahuntanam']."'></td>
				
				<td><select style=\"width:100px;\" onchange=getharga('".$no."'); id=tujuan_".$no.">" . @$opttujuan. "</select></td>
				<td><input id=kgwb_".$no." onblur=hitungrupiah('".$no."'); onkeyup=\"z.numberFormat('kgwb_".$no."',2)\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\" value='".@hidezerodecimal($bar['kgwb'],2)."'></td>
				
				<td><input id=harga_muat_".$no." disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
				<td><input id=rp_muat_".$no." disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\"></td>
				
				<td><input id=harga_angkut_".$no." disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
				<td><input id=rp_angkut_".$no." disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\"></td>
				
				<td><input id=ttlrp_".$no." disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:100px;\"></td>
				
				<input type=hidden id=method value='insert'>
			</tr>
			";
        }
		$tab.="<tr class=rowcontent style=font-weight:bold>
			<td colspan=10 align=right>
				<button id=tomboldetail class=mybutton onclick=\"loaddatadetail()\" >Refresh</button>
				<button id=tomboldetail class=mybutton onclick=\"saveAll('".$no."')\" >" . $_SESSION['lang']['save'] . "</button>
				<button id=tomboldetail class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
			</td>
			</tr>";
		$tab.="<input hidden id=jumlahrow value=".$no.">";
		
		echo $notiket."##".$driver."##".$divisi."##".tanggalnormal($tanggal)."##".$nopol."##".$jjg."##".$kgwb."##".$tab; #exit("error");
	break;
	case'detail':
        $optspb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$where="";
		if($tglheader!='--'){
			$where=" and tanggal = '".$tglheader."'";
		}
        $sql = "select * from " . $dbname . ".kebun_spbht where kodeorg = '".$kodeorg."' and tanggal like '".$periode."%' and posting='1' and nospb not in (select nospb from ".$dbname.".kebun_rekapangkutantbsht) ".$where."";
		#exit("error".$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optspb.="<option value=".$bar['nospb'].">".$bar['nospb']."</option>";
        }
        
	OPEN_BOX();
        echo"
        <fieldset>
			<legend>Cari SPB</legend>
			<table>
				<tr>
					<td style=\"width:100px;\">".$_SESSION['lang']['nospb']."</td>
					<td>:</td>
					<td><select style=\"width:150px;\" onchange=getdetailspb(); id=nospb>" . $optspb . "</select>
						<img id='nospb' onclick=z.elSearch('nospb',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
					<td>".$_SESSION['lang']['divisi']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=divisi style=\"width:145px;\" />
					</td>
					
					<td>".$_SESSION['lang']['janjang']."</td>
					<td>:</td>
					<td><input type=text class=myinputtextnumber disabled id=jjg style=\"width:60px;\" />
					</td>
				</tr>
				
				<tr>
					<td>No Tiket</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=notiket style=\"width:145px;\" />
					</td>
					
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=tanggal style=\"width:145px;\" />
					</td>
					
					<td>".$_SESSION['lang']['kgwb']."</td>
					<td>:</td>
					<td><input type=text  class=myinputtextnumber disabled id=kgwb style=\"width:60px;\" />
					</td>
				</tr>
				
				<tr>
					<td>".$_SESSION['lang']['sopir']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=sopir style=\"width:145px;\" />
					</td>
					
					<td>".$_SESSION['lang']['nopol']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext  disabled id=nopol style=\"width:145px;\" />
					</td>
				</tr>
				
		</table>
		<hr>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center rowspan='2' width=30px>No</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jenis'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . "</td>
            <td align=center colspan='2'>Muat</td>
            <td align=center colspan='2'>Angkut</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['total'] . "</td>
        </tr>
        <tr>
            <td align=center>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center>" . $_SESSION['lang']['rp'] . "</td>
            <td align=center>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center>" . $_SESSION['lang']['rp'] . "</td>
        </tr></thead>
		<tbody id=inputharga> 
		</tbody>
        </table>
        </fieldset>";
        CLOSE_BOX();
		OPEN_BOX();
	echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
        CLOSE_BOX();
        break;
    case'excel':
        $tab = "<table cellpadding=1 cellspacing=1 border=1 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>    
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center rowspan='2'>".$_SESSION['lang']['lapPersonel']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</td>
        </tr>
        <tr>
            <td align=center>" . $_SESSION['lang']['luasareaproduktif'] . "</td>
            <td align=center>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekappnn where divisi like '" . $unitexp . "%' "
                . " and tanggal like '" . $perexp . "%' order by tanggal asc,blok asc ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td align=left>" . $bar['blok'] . "</td>";
            $tab.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
            @$tluasplan+=$bar['luasproduksi'];
            @$tluaspanen+=$bar['luaspanen'];
            @$ttk+=$bar['tenagakerja'];
            @$tjjgpnn+=$bar['jjgpanen'];
            @$tjjgafkir+=$bar['jjgafkir'];
            @$tkgkebun+=$bar['kgkebun'];
        }
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=right colspan=4><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($ttk, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
        $tab.="<td align=right><b></td>";
        $tab.="<td align=right><b>" . @number_format($tkgkebun) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
        $tab.="<td></td>";
        $tab.="</tr>";
        $tab.="</table>";
        $stream = $tab;
        $nop_ = "Laporan_Rekap_Panen" . date('Ymd_His');
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
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2' style=width:75px>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center rowspan='2'>".$_SESSION['lang']['hk2']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</td>
        </tr>
        <tr>
            <td align=center style=width:75px>" . $_SESSION['lang']['luasareaproduktif'] . "</td>
            <td align=center>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekappnn where divisi like '" . $div . "%' and tanggal='" . $tgl . "' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['blok'] . "</td>";
            $tab.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
            @$tluasplan+=$bar['luasproduksi'];
            @$tluaspanen+=$bar['luaspanen'];
            @$ttk+=$bar['tenagakerja'];
            @$tjjgpnn+=$bar['jjgpanen'];
            @$tjjgafkir+=$bar['jjgafkir'];
            @$tkgkebun+=$bar['kgkebun'];
        }
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=right colspan=3><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($ttk, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
        $tab.="<td align=right><b>".@number_format($tkgkebun/$tjjgpnn,2)."</td>";
        $tab.="<td align=right><b>" . @number_format($tkgkebun) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
        $tab.="<td></td>";
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
        break;
    
    
    case'delete':
        $str = "delete from " . $dbname . ".kebun_rekappnn where divisi='" . $div . "' and tanggal='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    
	case'updatedetail':
	   $str = "update " . $dbname . ".kebun_rekappnn set `tahuntanam`= '" . $thntnm . "', `luasproduksi`='" . $luasaresta . "', `luaspanen`='" . $luaspnn . "', `tenagakerja`='" . $tk . "', `jjgpanen`='" . $jjgpnn . "', `jjgafkir`='" . $afkirjjg . "', `keterangan`='" . $afkirket . "', `updateby`='" . $_SESSION['standard']['userid'] . "',bjr='" . $bjr . "',kgkebun='" . $kgkebun . "' where divisi='".$div."' and tanggal='" . $tgl . "' and blok ='" . $blok . "'";
            
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
		break;
    case'posting':
        $str = "update " . $dbname . ".kebun_rekappnn set posting='1',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where divisi like '" . $div . "%' and tanggal='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
		case'unposting':
		$tglunpost=tanggalsystemn(checkPostGet('tgl', ''));
		//cek spb sudah ada yg di input belum
		$str = "select * from " . $dbname . ".kebun_rekappnn where divisi='".$div."' and tanggal ='".$tglunpost."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$cspb='';
		while ($dspb = $ttp->fetch()) {
			$blokspb[$dspb['blok']]=$dspb['blok'];
		}
		$str = "select distinct(nospb), tanggal from " . $dbname . ".kebun_spb_vw where divisi='".$div."' and tanggal >='".$tglunpost."' and blok in ('".implode("','",$blokspb)."')";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$nospb="";
		while ($dspb = $ttp->fetch()) {
			$nospb=$nospb."No SPB : ".$dspb['nospb']." - ".$dspb['tanggal']."\n";
		}
		//cek tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".substr($div,0,4)."' and periode ='".substr($tglunpost,0,7)."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
			$tutup=$bar['tutupbuku'];
		if($tutup==1){
			exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".substr($tglunpost,0,7)." unit ".substr($div,0,4)." sudah di tutup.");
		} else if($nospb!=''){
			exit("Error : Unposting tidak bisa dilakukan karena Blok :\n".implode(",",$blokspb)."\nSPB-nya sudah diinput pada tanggal >= ".tanggalnormal($tglunpost)." Divisi ".$div.".\n\n\nJika tetap ingin melanjutkan silahkan hapus SPB berikut ini :\n".$nospb."");
		}
		#cek kebun prestasi
		$str = "select distinct(notransaksi), tanggal from " . $dbname . ".kebun_prestasi_vw where kodeorg like '".$div."%' and tanggal >='".$tglunpost."' and kodeorg in ('".implode("','",$blokspb)."') order by tanggal, notransaksi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$nopnn="";
		while ($bar = $res->fetch()) {
			$nopnn=$nopnn."No Transaksi : ".$bar['notransaksi']." - ".$bar['tanggal']."\n";
		}
		if($nopnn!=''){
			exit("Error : Unposting tidak bisa dilakukan karena Blok :\n".implode(",",$blokspb)."\nsudah diinput transaksi Kegiatan Panennya pada tanggal >= ".tanggalnormal($tglunpost)." Divisi ".$div.".\n\n\nJika tetap ingin melanjutkan silahkan hapus transaksi berikut ini :\n".$nopnn."");
		}
        $str = "update " . $dbname . ".kebun_rekappnn set posting='0',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where divisi like '" . $div . "%' and tanggal='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'loaddatadetail':
		$arrjenis=array('muat','angkut');
		$arrtujuan=array('tphpks'=>'TPH - PKS','rampks'=>'RAMP - PKS');
		
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['sopir'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . "</td>";
			foreach($arrjenis as $jenis){
			   $tab.="<td align=center colspan='2'>".$jenis."</td>";
			}
        $tab.="<td align=center rowspan='2'>" . $_SESSION['lang']['rupiah'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['action'] . "</td>
        </tr>";
		$tab.="<tr>";
		foreach($arrjenis as $jenis){
			foreach($arrtujuan as $keytujuan => $valtujuan){
			   $tab.="<td align=center>".$valtujuan."</td>";
			}
		}	
		$tab.="</tr>";
		$tab.="</thead>";
        $no = 0;
		$dataspb=array();
        $str = "select * from " . $dbname . ".kebun_rekapangkutantbsht a 
		left join " . $dbname . ".kebun_rekapangkutantbsdt b on a.nospb=b.nospb 
		where kodeorg = '" . $kodeorg . "' and periode='" . $periode . "' and supplier='" . $supplier . "' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$dataspb[$bar['nospb']]=$bar['nospb'];
			$tgl[$bar['nospb']]=$bar['tanggal'];
			$kgwb[$bar['nospb']]=$bar['totalkgwb'];
			@$rupiah[$bar['nospb']][$bar['jenis']][$bar['tujuan']]+=$bar['rupiah'];
        }
		$no=0;
		foreach($dataspb as $nospb){
			$optdriver=makeOption($dbname,'pabrik_timbangan','nospb,supir',"nospb='".$nospb."'");
			$optnopol=makeOption($dbname,'pabrik_timbangan','nospb,nokendaraan',"nospb='".$nospb."'");
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $nospb. "</td>";
            $tab.="<td align=left>" . $optnopol[$nospb]. "</td>";
            $tab.="<td align=left>" . $optdriver[$nospb]. "</td>";
            $tab.="<td align=center>" . $tgl[$nospb]. "</td>";
            $tab.="<td align=right>" . number_Format($kgwb[$nospb]). "</td>";
            foreach($arrjenis as $jenis){
				foreach($arrtujuan as $keytujuan => $valtujuan){
				   $tab.="<td align=right>".@number_format($rupiah[$nospb][$jenis][$keytujuan])."</td>";
				   @$ttlrp[$nospb]+=$rupiah[$nospb][$jenis][$keytujuan];
				   @$trp[$jenis][$keytujuan]+=$rupiah[$nospb][$jenis][$keytujuan];
				   @$gtrp+=$rupiah[$nospb][$jenis][$keytujuan];
				}
			}	
            $tab.="<td align=right>" . number_Format($ttlrp[$nospb]). "</td>";
			
            $tab.="<td align=center width=40px>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletedetail('".$kodeorg."','" .$periode. "','" .$nospb. "');\" >
					<img src=images/skyblue/zoom.png class=resicon  title='Preview' onclick=\"viewdetail('".$kodeorg."','" .$periode. "','" .$nospb. "');\" >
				</td>";
				
            @$tkg+=$kgwb[$nospb];
		}
            
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5><b>" . $_SESSION['lang']['total'] . "</td>";
		$tab.="<td align=right><b>" . @number_format($tkg) . "</td>";
		foreach($arrjenis as $jenis){
			foreach($arrtujuan as $keytujuan => $valtujuan){
				$tab.="<td align=right><b>" . @number_format($trp[$jenis][$keytujuan]) . "</td>";
			}
		}	
		$tab.="<td align=right><b>" . @number_format($gtrp) . "</td>";
		$tab.="<td colspan=1></td>";
		$tab.="</tr>";
			
        $tab.="</table>";
        echo $tab;
        break;
    case'getdata':
        $sql = "select * from " . $dbname . ".setup_blok where kodeorg = '" . $blok . "' and statusblok='TM'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $thntnm = $bar['tahuntanam'];
        $luas = $bar['luasareaproduktif'];
		$tgl = tanggalnormal($tgl);
		$tgl = explode('-',$tgl);
		$tglbjr2=$tgl[2]."-".$tgl[1];
		#BJR diambil dari setup BJR
		$str = "select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$blok."' and periode = '".$tglbjr2."'"; 

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$bjr = $bar['bjr'];  
		}
		if ($blok != '') {
			echo $thntnm . "##" . $luas . "##" . $bjr;
		}
	break;
    case'loaddata':
        $where = "";
        $where.=" and divisi like '" . $_SESSION['empl']['lokasitugas'] . "%' ";
        if ($divsch != '') {
            $where.=" and divisi='" . $divsch . "' ";
        }
        if ($tglsch != '') {
            $where.=" and tanggal='" . $tglsch . "' ";
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
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekappnn where 1=1 " . $where . " group by divisi,tanggal";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
        $str = "SELECT *,sum(luaspanen) as luaspanen,sum(tenagakerja) as tenagakerja,"
                . "sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,sum(kgkebun) as kgkebun FROM " . $dbname . ".kebun_rekappnn
		where 1=1 " . $where . " group by divisi,tanggal order by tanggal desc,divisi asc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $bar['divisi'] . "</td>";
            $tab.="<td>" . $nmorg[$bar['divisi']] . "</td>";
            $tab.="<td >" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['tenagakerja']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
			   $isi.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
                    onclick=\"posting('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$isi.="<td align=center></td><td align=center></td>";
                $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=right><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
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
                     <tr><td colspan=14 align=center>";
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
}
?>	
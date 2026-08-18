<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$div       = checkPostGet('div', '');
$tgl       = tanggalsystem(checkPostGet('tgl', ''));
$method    = checkPostGet('method', '');
$blok      = checkPostGet('blok', '');
$thntnm    = checkPostGet('thntnm', '');
$luasaresta= checkPostGet('luasaresta', '');
$luaspnn   = checkPostGet('luaspnn', '');
$tk        = checkPostGet('tk', '');
$jjgpnn    = checkPostGet('jjgpnn', '');
$afkirjjg  = checkPostGet('afkirjjg', '');
$afkirket  = checkPostGet('afkirket', '');
$divsch    = checkPostGet('divsch', '');
$tglsch    = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch   = checkPostGet('bloksch', '');
$unitexp   = checkPostGet('unitexp', '');
$perexp    = checkPostGet('perexp', '');
$bjr       = checkPostGet('bjr', '');
$brondol   = checkPostGet('brondol', '');
$kgkebun   = checkPostGet('kgkebun', '');
$nmorg     = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmindk     = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jab       = getPostingJabatan('rekappnn'); 
switch ($method) {
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
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <!--- <td align=center rowspan='2' style=width:75px>" . $_SESSION['lang']['tahuntanam'] . "</td> --->
            <td align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center rowspan='2'>".$_SESSION['lang']['hk2']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</td>
            <!--- <td align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</td> --->
            <!--- <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td> --->
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
            $tab.="<td align=center>" . $nmindk[$bar['blok']] . "</td>";
            // $tab.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            // $tab.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            // $tab.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
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
        $tab.="<td align=right colspan=2><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($ttk, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
        // $tab.="<td align=right><b>".@number_format($tkgkebun/$tjjgpnn,2)."</td>";
        // $tab.="<td align=right><b>" . @number_format($tkgkebun) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
        $tab.="<td></td>";
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
    break;
    case'detail':
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekappnn where divisi='" . $div . "' and tanggal='" . $tgl . "' and posting=1";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Data untuk divisi : " . $div . " ditanggal " . tanggalnormal($tgl) . " sudah di posting");
        }
        OPEN_BOX();
        $optblok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        /* 
            $sql = "select * from ".$dbname.".organisasi a 
            left join ".$dbname.".setup_blok b on a.kodeorganisasi=b.kodeorg
            where b.kodeorg like '" . $div . "%' and b.statusblok in ('TM','TBM') and b.luasareaproduktif>0 
            order by substr(a.kodeorganisasi,1,6), a.kodeorganisasi asc";
        */
        
        $sql = "select a.indukblok, a.namaindukblok, sum(b.luasareaproduktif) as luasareaproduktif 
        from ".$dbname.".organisasi a left join ".$dbname.".setup_blok b on a.indukblok=b.indukblok and a.kodeorganisasi = b.kodeorg
		where b.indukblok like '" . $div . "%' and (b.statusblok in ('TM','TBM') and (".date("Y")." - tahuntanam >= 3)) and b.luasareaproduktif>0
        group by substr(a.indukblok,1,6), a.indukblok
		order by substr(a.indukblok,1,6), a.indukblok asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$umur = date("Y")-$bar['tahuntanam'];
			// if($bar['statusblok']=='TM' or $umur>=3){				
				$a=substr($bar['indukblok'],0,6);
				if($a!=$m){			
					$optblok.="<optgroup label='".getNamaOrg($a)."'>";
				}
				
				// $d=$bar['tahuntanam'];
				// if($d!=$n){			
				// 	$optblok.="<optgroup label='Tahun Tanam ".$d."'>";
				// }
				$optblok.="<option value=" . $bar['indukblok'] . ">" . $bar['namaindukblok'] . " [" . number_format($bar['luasareaproduktif'],2) . " HA]</option>";
				
					
				// $n=$d;
				// if($d!=$n){			
				// 	$optblok.="</optgroup>";
				// }
				$m=$a;
				if($a!=$m){			
					$optblok.="</optgroup>";
				}
			// }
        }
        // style='float:left;'
        echo"
        <fieldset>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2' style=\"width:50px;\">" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hk2']."</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center rowspan='2'>Brondolan<br>(Kg)</th>
			<th align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</th>
            <th align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</th>
            <th align=center rowspan='2' colspan=2  width=50px>" . $_SESSION['lang']['action'] . "</th>
        </tr>
        <tr>
            <th align=center>" . $_SESSION['lang']['luasareaproduktif'] . "</th>
            <th align=center>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center>" . $_SESSION['lang']['jjg'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
        </tr></thead>
        <tr class=rowcontent>
            <td colspan=2><select class=select2 style=width:180px onchange=getdata() id=blok>" . $optblok . "</select></td>
            <td><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=thntnm disabled  style=\"width:50px;\"></td>
            <td><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=luasaresta disabled  style=\"width:70px;\"></td>
            <td><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=luaspnn style=\"width:50px;\"></td>
            <td><input id=tk class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
            <td><input id=jjgpnn onkeyup=getkg() class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
			<td><input id=brondol class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
            <td><input id=bjr disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
            <td><input id=kgkebun disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
            <td><input id=afkirjjg class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
            <td><input id=afkirket class=myinputtext onkeypress=\"return tanpa_kutip(event);\"  style=\"width:175px;\"></td>
            <td align=center width=25px><input type=hidden id=method value='insert'>
                <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
            </td>    
			<td align=center width=25px>
				<img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr>
        </table>
        <button id=done class=mybutton onclick=cancel()>" . $_SESSION['lang']['selesai'] . "</button>
        </fieldset>";
        // style='float:left;'
        echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
        CLOSE_BOX();
    break;
    case'insert':
        #cek data
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekappnn where "
                . " divisi='" . $div . "' and tanggal='" . $tgl . "' and blok='" . $blok . "'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Data untuk blok : " . $blok . " ditanggal " . tanggalnormal($tgl) . " sudah ada.");
        }
		
		validasiInput(substr($div,0,4),$div,'RPNN',tanggalsystemn(tanggalnormal($tgl)),$exit='1');
		
        $str = "insert into " . $dbname . ".kebun_rekappnn (`divisi`, `tanggal`, `blok`, `tahuntanam`, 
                `luasproduksi`, `luaspanen`, `tenagakerja`, `jjgpanen`, `jjgafkir`, `keterangan`, `updateby`,bjr,kgkebun,brondolan)
            values ('" . $div . "','" . $tgl . "','" . $blok . "','" . $thntnm . "',"
                . "'" . $luasaresta . "','" . $luaspnn . "','" . $tk . "','" . $jjgpnn . "','" . $afkirjjg . "',"
                . "'" . $afkirket . "','" . $_SESSION['standard']['userid'] . "','" . $bjr . "','" . $kgkebun . "','" . $brondol . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
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
    case'deletedetail':
        $str = "delete from " . $dbname . ".kebun_rekappnn where divisi='" . $div . "' and tanggal='" . $tgl . "' and blok='" . $blok . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	case'updatedetail':
	   $str = "update " . $dbname . ".kebun_rekappnn set `tahuntanam`= '" . $thntnm . "', `luasproduksi`='" . $luasaresta . "', `luaspanen`='" . $luaspnn . "', `tenagakerja`='" . $tk . "', `jjgpanen`='" . $jjgpnn . "', `jjgafkir`='" . $afkirjjg . "', `keterangan`='" . $afkirket . "', `updateby`='" . $_SESSION['standard']['userid'] . "',bjr='" . $bjr . "',kgkebun='" . $kgkebun . "',brondolan='" . $brondol . "' where divisi='".$div."' and tanggal='" . $tgl . "' and blok ='" . $blok . "'";
            
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
    break;
    case'posting':
        #ambil data blok dulu
        $str = "select sum(jjgpanen) as jjgpanen, tanggal,blok from " . $dbname . ".kebun_rekappnn where divisi='".$div."' and tanggal ='".$tgl."' group by blok";
        $res=fetchdata($str);
        $jjgpnn=0;$jjgpnnblok=$jjgspbblok=$jjgkegpnnblok=$jjgkegpnnblok=array();
        $nospb="";$cekspb=0;$cekkegpnn=0;$kegpnn="";
		foreach($res as $bar) {
            $jjgpnn+=$bar['jjgpanen'];

            #cek spb blok dan tanggal panen
            $strspb = "select sum(jjg) as jjg from " . $dbname . ".kebun_spbdt where  tanggalpanen ='".$bar['tanggal']."' and blok ='".$bar['blok']."'";
            $resspb=fetchdata($strspb);
            if($resspb[0]['jjg']>$jjgpnn){
                $cekspb+=1;
                $nospb=$nospb."Blok : ".$bar['blok']."; jjg panen : ".$jjgpnn."; jjg spb : ".$resspb[0]['jjg']."\n";
            }
            #cek panen blok dan tanggal panen
            $strpnnx = "select sum(a.hasilkerja) as jjg from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where  b.tanggal ='".$bar['tanggal']."' and a.kodeorg ='".$bar['blok']."' and b.tipetransaksi='PNN'";
            $respnnx=fetchdata($strpnnx);
            if($respnnx[0]['jjg']>$jjgpnn){
                $cekkegpnn+=1;
                $kegpnn=$kegpnn."Blok : ".$bar['blok']."; jjg panen : ".$jjgpnn."; jjg kegiatan panen : ".$respnnx[0]['jjg']."\n";
            }
        }
        
        if($cekspb>0){
            exit("Warning : Jumlah jjg di input pada SPB sudah lebih besar :\n".$nospb."");
        }

        if($cekkegpnn>0){
            exit("Warning : Jumlah jjg di input pada kegiatan panen sudah lebih besar :\n".$kegpnn."");
        }
        
		#validasi posting
		validasiInput(substr($div,0,4),$div,'RPNNPOST',$tgl,$exit='1');

		
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
			#exit("Error : Unposting tidak bisa dilakukan karena Blok :\n".implode(",",$blokspb)."\nSPB-nya sudah diinput pada tanggal >= ".tanggalnormal($tglunpost)." Divisi ".$div.".\n\n\nJika tetap ingin melanjutkan silahkan hapus SPB berikut ini :\n".$nospb."");
		}
		#cek kebun prestasi
		//tanggal >= '".$tglunpost."' dirubah menjadi  (=) , Atwal Arifin
		$nopnn="";
		$str = "select distinct(notransaksi), tanggal from " . $dbname . ".kebun_prestasi_vw where kodeorg like '".$div."%' and tanggal ='".$tglunpost."' and kodeorg in ('".implode("','",$blokspb)."') order by tanggal, notransaksi asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$nopnn=$nopnn."No Transaksi : ".$bar['notransaksi']." - ".$bar['tanggal']."\n";
		}
		if($nopnn!=''){
			#exit("Error : Unposting tidak bisa dilakukan karena Blok :\n".implode(",",$blokspb)."\nsudah diinput transaksi Kegiatan Panennya pada tanggal >= ".tanggalnormal($tglunpost)." Divisi ".$div.".\n\n\nJika tetap ingin melanjutkan silahkan hapus transaksi berikut ini :\n".$nopnn."");
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
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable style=min-width:905px>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
            <!--- <th align=center rowspan='2' style=width:50px>" . $_SESSION['lang']['tahuntanam'] . "</th> --->
            <th align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['hk2']."</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</th>
			<th align=center rowspan='2'>Brondolan<br>(Kg)</th>
            <!--- <th align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</th> --->
            <!--- <th align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</th> --->
            <th align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</th>
            <th align=center rowspan='2' colspan=2 width=50px>" . $_SESSION['lang']['action'] . "</th>
        </tr>
        <tr>
            <th align=center style=width:75px>" . $_SESSION['lang']['luasareaproduktif'] . "</th>
            <th align=center>" . $_SESSION['lang']['panen'] . "</th>
            <th align=center>" . $_SESSION['lang']['jjg'] . "</th>
            <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekappnn where divisi like '" . $div . "%' and tanggal='" . $tgl . "' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>".$nmindk[$bar['blok']]."</td>";
            // $tab.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['brondolan']) . "</td>";
            // $tab.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            // $tab.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
            $tab.="<td align=center width=25px>
					<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['blok'] . "','" . $bar['tahuntanam'] . "','" . $bar['luasproduksi'] . "','" . $bar['luaspanen'] . "','" . $bar['tenagakerja'] . "','" . $bar['jjgpanen'] . "','" . number_format($bar['bjr'],2) . "','" . $bar['kgkebun'] . "','" . $bar['jjgafkir'] . "','" . $bar['keterangan'] . "','".$bar['brondolan']."');\" >
				</td>	
				<td align=center width=25px>	
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['blok'] . "');\" >
					</td>";
            @$tluasplan+=$bar['luasproduksi'];
            @$tluaspanen+=$bar['luaspanen'];
            @$ttk+=$bar['tenagakerja'];
            @$tjjgpnn+=$bar['jjgpanen'];
            @$tjjgafkir+=$bar['jjgafkir'];
            @$tkgkebun+=$bar['kgkebun'];
            @$tbrondol+=$bar['brondolan'];
        }
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=right colspan=2><b>" . $_SESSION['lang']['total'] . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($ttk, 2) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tbrondol) . "</td>";
        // $tab.="<td align=right><b>".@number_format($tkgkebun/$tjjgpnn,2)."</td>";
        // $tab.="<td align=right><b>" . @number_format($tkgkebun) . "</td>";
        $tab.="<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
        $tab.="<td colspan=3></td>";
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
    break;
    case'getdata':
        // $sql = "select * from " . $dbname . ".setup_blok where kodeorg = '" . $blok . "' and statusblok='TM'";
        $sql = "select sum(luasareaproduktif) as luasareaproduktif from " . $dbname . ".setup_blok where indukblok = '" . $blok . "' and (".date("Y")." - tahuntanam >= 3 or statusblok='TM')";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        // $thntnm = $bar['tahuntanam'];
        $thntnm = 0;
        $luas = $bar['luasareaproduktif'];
		$tgl = tanggalnormal($tgl);
		$tgl = explode('-',$tgl);
		$tglbjr2=$tgl[2]."-".$tgl[1];
		#BJR diambil dari setup BJR
		// $str = "select bjr from ".$dbname.".kebun_5bjr where kodeorg='".$blok."' and periode = '".$tglbjr2."'"; 
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
		// 	$bjr = $bar['bjr'];  
        // }
        $bjr = 0;
		if ($blok != '') {
			echo $thntnm . "##" . $luas . "##" . $bjr;
		}
	break;
    case'loaddata':
        $where = "";
		$str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		if($_SESSION['empl']['subbagian']=='' and in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
			//$where.=" and divisi like '".$_SESSION['empl']['lokasitugas']."%' and updateby ='".$_SESSION['standard']['userid']."'"; 
			$where.=" and divisi like '".$_SESSION['empl']['lokasitugas']."%' and (divisi like '".$_SESSION['empl']['subbagian']."%' or updateby ='".$_SESSION['standard']['userid']."')"; 
		}else if($_SESSION['empl']['subbagian']==''){
			$where.= " and divisi like '".$_SESSION['empl']['lokasitugas']."%'";
		} else {
			// $where.= " and (divisi like '".$_SESSION['empl']['subbagian']."%' or updateby ='".$_SESSION['standard']['userid']."')";
		}
		
        #$where.=" and divisi like '" . $_SESSION['empl']['lokasitugas'] . "%' ";
        
		if ($divsch != '') {
            $where.=" and divisi='" . $divsch . "' ";
        }
        if ($tglsch != '') {
            $where.=" and tanggal='" . $tglsch . "' ";
        }
        $limit = 10;
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
        $str = "SELECT *,sum(brondolan) as brondolan,sum(luaspanen) as luaspanen,sum(tenagakerja) as tenagakerja,"
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
            $tab.="<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['tenagakerja']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['brondolan']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                    onclick=\"edit('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                    onclick=\"del('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
			   $isi.="<td align=center><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Posting' 
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
                $isi.="<td align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
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
                     <tr><td colspan=15 align=center>";
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
<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$div       = checkPostGet('div', '');
$tgl       = tanggalsystemn(checkPostGet('tgl', ''));
$method    = checkPostGet('method', '');
$kodeorg   = checkPostGet('kodeorg', '');
$kodekend  = checkPostGet('kodekend', '');
$pekerjaan = checkPostGet('pekerjaan', '');
$lokasi    = checkPostGet('lokasi', '');
$blok      = checkPostGet('blok', '');
$driver    = checkPostGet('driver', '');
$keterangan= checkPostGet('keterangan', '');
$satuan    = checkPostGet('satuan', '');
$fisik     = checkPostGet('fisik', '');
$divsch    = checkPostGet('divsch', '');
$tglsch    = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch   = checkPostGet('bloksch', '');
$unitexp   = checkPostGet('unitexp', '');
$perexp    = checkPostGet('perexp', '');
$tipe      = checkPostGet('tipe', '');

$nmorg     = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmindk    = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');
$nmkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nsupir    = makeOption($dbname,'vhc_5operator','karyawanid,nama');
$nkeg      = makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
$jab       = getPostingJabatan('traksirkh'); $param          =$_POST;if(count($param)==0){$param = $_GET;}
switch ($method) {

    case'getsubunit':

        $optSubUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and tipe= 'TRAKSI' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optSubUnit;
	break;

    case'excel':
        if($tipe == 'pdf'){
            $brd = 1;
            $cel = 0;
            $bcol = "background-color:#275370;";
            $col =  "color:#fff;";
        }else{
            $brd=1;
            $cel=1;
            $bcol='';
            $col='';
        }
        $tab= "<table cellpadding=3 cellspacing=".$cel." border=".$brd." style='width:100%;'>
            <thead><tr>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['kodevhc'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['supir'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['pekerjaan'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center style='".$bcol.$col."'>" . $_SESSION['lang']['keterangan'] . "</td>
        </tr>
        </thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".vhc_rkh where kodetraksi = '" . $unitexp . "TK' and tanggal like '" . $perexp . "%' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['kodevhc'] . " ".(getVhc($bar['kodevhc'])!=''?"- ".getVhc($bar['kodevhc']):'')." - ".getVhc($bar['kodevhc'],'kodetraksi')." ".(getVhc($bar['kodevhc'],'detailvhc')!=''?"- ".getVhc($bar['kodevhc'],'detailvhc') :'')."</td>";
            $tab.="<td align=left>" . $nsupir[$bar['supir']] . "</td>";
            $tab.="<td align=left>" . $nkeg[$bar['pekerjaan']] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . "</td>";
            $divisi = substr($bar['blok'],0,6) . " ".getNamaOrg(substr($bar['blok'],0,6));
            $tab.="<td align=left>" . (substr($bar['blok'],0,6) != 'null' ? $divisi : '')."</td>";
            $blk = explode(',',$bar['blok']);
            $tab.="<td>";
            foreach ($blk as $b) {
                if($b != ''){
                    $bloknya=$b." [".$nmindk[$b]."]";
                    $tab.=($b != 'null' ? $bloknya : '')."<br>";
                }
            }
            $tab.="</td>";
            $tab.="<td align=right>" . number_format($bar['fisik'],2) . "</td>";
            $tab.="<td align=center>" . $bar['satuan'] . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
        }
        $tab.="</tr>";
        $tab.="</table>";
        $stream = $tab;
        if($tipe == 'pdf'){
            $dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("RKH_TRAKSI_" . date('Ymd_His'),array("Attachment"=>0));
        }else{
            $nop_ = "RKH_TRAKSI_" . date('Ymd_His');
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
    case'html':
        $tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kodeorg'] . "</td>
					<td width=5px>:</td>
					<td>".substr($div,0,4)." - " . $nmorg[substr($div,0,4)] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['traksi'] . "</td>
					<td width=5px>:</td>
					<td>" .$div . " - " . $nmorg[$div] . "</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['tanggal'] . "</td>
					<td width=5px>:</td>
					<td>" .tanggalnormal($tgl) . "</td>
				</tr>
				</table><hr>";
		$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
            <td align=center>" . $_SESSION['lang']['supir'] . "</td>
            <td align=center>" . $_SESSION['lang']['pekerjaan'] . "</td>
            <td align=center>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
        </tr>
        </thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".vhc_rkh where kodetraksi = '" . $div . "' and tanggal='" . $tgl . "' ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['kodevhc'] . " ".(getVhc($bar['kodevhc'])!=''?"- ".getVhc($bar['kodevhc']):'')." - ".getVhc($bar['kodevhc'],'kodetraksi')." ".(getVhc($bar['kodevhc'],'detailvhc')!=''?"- ".getVhc($bar['kodevhc'],'detailvhc') :'')."</td>";
            $tab.="<td align=left>" . $nsupir[$bar['supir']] . "</td>";
            $tab.="<td align=left>" . $nkeg[$bar['pekerjaan']] . "</td>";
            $tab.="<td align=center>" . $bar['kodeorg'] . " - " . getNamaOrg($bar['kodeorg']) . "</td>";
            $divisi = substr($bar['blok'],0,6) . " ".getNamaOrg(substr($bar['blok'],0,6));
            $tab.="<td align=left>" . (substr($bar['blok'],0,6) != 'null' ? $divisi : '')."</td>";
            $blk = explode(',',$bar['blok']);
            $tab.="<td>";
            foreach ($blk as $b) {
                if($b != ''){
                    $bloknya=$b." [".$nmindk[$b]."]";
                    $tab.=($b != 'null' ? $bloknya : '')."<br>";
                }
            }
            $tab.="</td>";
            $tab.="<td align=right>" . number_format($bar['fisik'],2) . "</td>";
            $tab.="<td align=center>" . $bar['satuan'] . "</td>";
            $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
        }
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
        break;
	// case'getnotransaksi':
		// $tmpTgl = explode('-',$tgl);
		// $str = "SELECT substr(notransaksi,-4) as notransaksi FROM " . $dbname . ".vhc_rkh where kodeorg='".$kodeorg."' and tanggal like '".substr($tgl,0,7)."%' order by notransaksi desc limit 1";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_ASSOC);
        // $bar=$res->fetch();
		// if(intval($bar['notransaksi'])==0){
			// $noawal=1;
		// }else{
			// $noawal = intval($bar['notransaksi'])+1;
        // }
        // $notran=$kodeorg."/RKH/".$tmpTgl[0]."/".$tmpTgl[1]."/".addZero($noawal,4);
	// echo $notran;
	// break;
    case'detail':
        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_rkh where kodetraksi='" . $div . "' and tanggal='" . $tgl . "' and posting=1";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Transaksi ditanggal " . tanggalnormal($tgl) . " sudah di posting");
        }
        OPEN_BOX();
        $optkend=$optkeg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sql = "select * from " . $dbname . ".vhc_5master where kodetraksi like '" . $div . "%' and status='1'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optkend.="<option value=" . $bar['kodevhc'] . ">" . $bar['kodevhc'] . " ".($bar['nopol']!=''?"- ".$bar['nopol']:'')." - ".$bar['kodetraksi']." ".($bar['detailvhc']!=''?"- ".$bar['detailvhc']:'')."</option>";
        }
        
        //Detail Pekerjaan
        $wh="";
        if($_SESSION['empl']['tipelokasitugas']=='KEBUN'){
            $wh=" and (substr(noakun,1,3) in ('126','128','621','611') or substr(noakun,1,1) in ('7') or kelompok='EXT')";
        }elseif($_SESSION['empl']['tipelokasitugas']=='PABRIK'){
            $wh=" and (substr(noakun,1,2) in ('63') or substr(noakun,1,1) in ('7') or kelompok='EXT')";
        }elseif($_SESSION['empl']['tipelokasitugas']=='BULKING'){
            $wh=" and (substr(noakun,1,2) in ('63') or substr(noakun,1,1) in ('7') or kelompok='EXT')";
        }else{
            $wh=" and (substr(noakun,1,2) in ('82') or noakun in ('7112001','7112004','7112001'))";
        }

        #PABRIK => %63%, 7%
        #KANWIL => 82%
        #RND => 82%
        #TC => 82%
        #BULKING => 81%

        $noakun = [];
        $str = "select * from ".$dbname.".keu_5akun where detail='1' and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7";
        $res = fetchData($str);
        foreach($res as $bar){
            $noakun[$bar['noakun']]=$bar['noakun'];
        }
        
        $kegkebun=[];
        $str = "select * from ".$dbname.".setup_kegiatan where noakun ='".$noakun."' and namakegiatan not like '%NON AKTIF%' and noakun in (select noakun from ".$dbname.".keu_5akun where detail='1' and namaakun not like '%NON AKTIF%' and LENGTH(noakun)=7) order by kodekegiatan asc";
        $res = fetchData($str);
        foreach($res as $key => $val){
            $kegkebun[$val['kodekegiatan']]=$val['kodekegiatan'];
        }

        $sjnskrj="select * from ".$dbname.".vhc_kegiatan where tipe ='traksi' ".$wh." AND status='1' order by noakun asc";
        $res=$owlPDO->query($sjnskrj) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($rjnskrj=$res->fetch()){
            // if(!empty($noakun[$rjnskrj['noakun']]) and !empty($kegkebun[$rjnskrj['setupkegiatan']])){	
                $d=substr($rjnskrj['kodekegiatan'],0,5);
                if($d!=$n){			
                    $nmorg = makeOption($dbname, 'keu_5akun', 'noakun,namaakun',"noakun='".$d."'");
                    $optkeg.="<optgroup label='".$nmorg[$d]."'>";
                }
                $optkeg.="<option value=".$rjnskrj['kodekegiatan'].">".$rjnskrj['kodekegiatan']." - ".$rjnskrj['namakegiatan']."</option>";
                $n=$d;
                if($d!=$n){
                    $optkeg.="</optgroup>";
                }
            }
        // }
        
        $slokTgs="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi) ='4'";

        $optLokTugas='';
        $res=$owlPDO->query($slokTgs) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($rlokTgs=$res->fetch()){
            // if(substr($rlokTgs['kodeorganisasi'],0,4)==$_SESSION['empl']['lokasitugas']){
            //     $optLokTugas.="<option value=".$rlokTgs['kodeorganisasi']." selected>".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
            // }else{
                $optLokTugas.="<option value=".$rlokTgs['kodeorganisasi'].">".$rlokTgs['kodeorganisasi']." - ".$rlokTgs['namaorganisasi']."</option>";
            // }	
        }

         $sBlok=selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi,indukblok,namaindukblok',"induk like '%{$param['divisi']}%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in 
            (select distinct kodeorg from $dbname.setup_blok where left(indukblok,6)='{$param['divisi']}' and luasareaproduktif>0 $statusblok and status='A') group by indukblok",'tipe desc,kodeorganisasi');
        $resblk=fetchData($sBlok);
        foreach ($resblk as $rBlok) {
            if($rAlokasi['kelompok']!='INF'){
                $d=substr($rBlok['kodeorganisasi'],0,6);
                if($d!=$n){			
                    $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok',"kodeorganisasi='".$d."'");
                    $optBlok.="<optgroup label='".$nmorg[$d]."'>";
                }
            
                if($param['blok']!=""){
                    if($rBlok['indukblok']==$param['blok']){						
                        $optBlok.="<option value=".$rBlok['indukblok']." selected>".$rBlok['namaindukblok']."</option>";
                    }else{						
                        $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                    }
                }else{
                    $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                }
                $n=$d;
                if($d!=$n){
                    $optBlok.="</optgroup>";
                }
            }else{
                if($param['blok']!=""){
                    if($rBlok['indukblok']==$param['blok']){                        
                        $optBlok.="<option value=".$rBlok['indukblok']." selected>".$rBlok['namaindukblok']."</option>";
                    }else{                      
                        $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                    }
                }else{
                    $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                }
            }
        }


        echo"
        <fieldset style=width:1000px>
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table width=100% border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
            <td align=center>" . $_SESSION['lang']['supir'] . "</td>
            <td align=center>" . $_SESSION['lang']['pekerjaan'] . "</td>
            <td align=center>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
			<td align=center>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            <td align=center style='width:10%' colspan=2>" . $_SESSION['lang']['action'] . "</td>
        </tr></thead>
        <tr class=rowcontent>
            <td><select style=width:200px onchange=getdata() id=kodekend class=select2>" . $optkend . "</select></td>
            <td><select id=driver class=select2 style=\"width:150px;\"></select></td>
			<td><select id=pekerjaan class=select2 style=\"width:200px;\">'".$optkeg."'</select></td>
            <td><select id=lokasi_kerja class=select2 name=lokasi_kerja  onchange=\"getdivisi('','')\" style=width:120px;><option value=''>".$_SESSION['lang']['pilihdata']."</option>".$optLokTugas."</select></td>
            <td><select id=divisi class=select2 name=divisi style=width:120px; onchange='getblok()'>".$optblok."</select></td>        
            <td><select id=blok multiple class=select2 name=blok style=width:120px; ></select></td>        
            <td><input id=fisik class=myinputtextnumber onkeypress='return angka_doang(event)'; onkeypress='enterkey(event,savedetail)';  style=\"width:75px;\" maxlength=10></td>
			<td align=center><input id=satuan disabled class=myinputtext style=\"width:50px;\"></td>
			<td><input id=keterangan class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" style=\"width:250px;\"></td>
			<td align=center valign=middle>
                <input type=hidden id=method value='insert'>
                <button id=done class=mybutton onclick=savedetail()>" . $_SESSION['lang']['save'] . "</button>
            </td>
            <td align=center valign=middle>
                <img title='" . $_SESSION['lang']['clear'] . "' class=zImgBtn onclick=\"cleardetail()\" src='images/clear.png'/>
            </td>
        </tr><tr>
			<td colspan=9 align=right><td colspan=2 align=center><button id=done class=mybutton onclick=displayList()>" . $_SESSION['lang']['selesai'] . "</button></td></td>
        </tr></table>
        </fieldset>";
        // style='float:left;'
        echo"
        <fieldset style=width:1200px><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
        CLOSE_BOX();
        break;
    case'insert':
        #cek data
        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_rkh where "
                . " kodetraksi='" . $div . "' and tanggal='" . $tgl . "' and supir='" . $driver . "' and kodevhc='" . $kodekend . "' and pekerjaan='" . $pekerjaan . "' and blok = '".$blok."'";
		// exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Kendaraan : " . $kodekend . " pekerjaan : " . $nkeg[$pekerjaan] . " sudah ada.");
        }
        $str = "insert into " . $dbname . ".vhc_rkh (`kodeorg`, `kodetraksi`, `tanggal`, 
                `kodevhc`, `supir`, `pekerjaan`,`blok`, `satuan`, `keterangan`, `fisik`, `updateby`)
            values ('" . $kodeorg . "','" . $div . "','" . $tgl . "',"
                . "'" . $kodekend . "','" . $driver . "','" . $pekerjaan . "','" . $blok . "','" . $satuan . "','" . strtoupper($keterangan) . "',"
                . "'" . $fisik . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'delete':
        $str = "delete from " . $dbname . ".vhc_rkh where kodetraksi='" . $div . "' and tanggal='" . $tgl . "' ";
		//exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'deletedetail':
        $str = "delete from " . $dbname . ".vhc_rkh where kodevhc='" . $div . "' and tanggal='" . $tgl . "' and supir='" . $driver . "' and pekerjaan='" . $pekerjaan . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'posting':
        $str = "update " . $dbname . ".vhc_rkh set posting='1',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where kodetraksi = '" . $div . "' and tanggal='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
		case'unposting':
		$tglunpost=tanggalsystemn(checkPostGet('tgl', ''));
		//cek tutup buku
		$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".substr($div,0,4)."' and periode ='".substr($tglunpost,0,7)."'";
		$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$ttp->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$ttp->fetch();
			$tutup=$bar['tutupbuku'];
		if($tutup==1){
			exit("Error : Unposting tidak bisa dilakukan karena periode akuntansi ".substr($tglunpost,0,7)." unit ".substr($div,0,4)." sudah di tutup.");
		}
        $str = "update " . $dbname . ".vhc_rkh set posting='0',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where kodetraksi = '" . $div . "' and tanggal='" . $tgl . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case'loaddatadetail':
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center width=50px>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center>" . $_SESSION['lang']['kodevhc'] . "</td>
            <td align=center>" . $_SESSION['lang']['supir'] . "</td>
            <td align=center>" . $_SESSION['lang']['pekerjaan'] . "</td>
            <td align=center>" . $_SESSION['lang']['divisi'] . "</td>
            <td align=center>" . $_SESSION['lang']['blok'] . "</td>
            <td align=center width=75px>" . $_SESSION['lang']['jumlah'] . "</td>
            <td align=center width=50px>" . $_SESSION['lang']['satuan'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
			<td align=center width=30px>" . $_SESSION['lang']['action'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".vhc_rkh where kodetraksi like '" . $div . "%' and tanggal='" . $tgl . "' ";
        if(count(fetchData($str))>0){
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $nmblokinduk = makeOption($dbname, 'setup_blok', 'kodeorg,indukblok',"kodeorg='".$bar['blok']."'");
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td align=left>" . $bar['kodevhc'] . " ".(getVhc($bar['kodevhc'])!=''?"- ".getVhc($bar['kodevhc']):'')." - ".getVhc($bar['kodevhc'],'kodetraksi')." ".(getVhc($bar['kodevhc'],'detailvhc')!=''?"- ".getVhc($bar['kodevhc'],'detailvhc') :'')."</td>";
                $tab.="<td align=left>" . $nsupir[$bar['supir']] . "</td>";
                $tab.="<td align=left>".$bar['pekerjaan']." - " . $nkeg[$bar['pekerjaan']] . "</td>";
                $divisi = substr($bar['blok'],0,6) . " ".getNamaOrg(substr($bar['blok'],0,6));
                $tab.="<td align=left>" . (substr($bar['blok'],0,6) != 'null' ? $divisi : '')."</td>";
                $blk = explode(',',$bar['blok']);
                $tab.="<td>";
                foreach ($blk as $b) {
                    if($b != ''){
                        $bloknya=$b." [".$nmindk[$b]."]";
                        $tab.=($b != 'null' ? $bloknya : '')."<br>";
                    }
                }
                $tab.="</td>";
                $tab.="<td align=right>" . number_format($bar['fisik'],2) . "</td>";
                $tab.="<td align=center>" . $bar['satuan'] . "</td>";
                $tab.="<td align=left>" . $bar['keterangan'] . "</td>";
                $tab.="<td align=center>";
                // $tab.="<img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                    // onclick=\"editdetail('" . $bar['kodetraksi'] . "','" . $bar['kodevhc'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['supir'] . "','".$bar['pekerjaan']."','".$bar['keterangan']."','".$bar['satuan']."','".$bar['fisik']."');\" >&nbsp";
                $tab.="<img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                    onclick=\"deletedetail('" . $bar['kodevhc'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['supir'] . "','".$bar['pekerjaan']."');\" >
                    </td>";
                    $tab.="</tr>";
            }
        }else{
            $tab.="<tr class=rowcontent><td colspan=9 align=center>".$_SESSION['errdatanotexist']."</td></tr>";
        }
        $tab.="</table>";
        echo $tab;
        break;
    case'getdata':
        $sql = "select * from " . $dbname . ".vhc_5operator where vhc = '" . $kodekend . "' and aktif='1' order by nama asc";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
			$optdriver.="<option value=".$bar['karyawanid'].">".$bar['nama']."</option>";
		}
		echo $optdriver;
	break;
	case'getsatuan':
        $sql = "select * from " . $dbname . ".vhc_kegiatan where kodekegiatan = '" . $pekerjaan . "'";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		$satuan.=$bar['satuan'];
		echo $satuan;
	break;
    case'getblok':
        #== Ambil kelompok dari kegiatan traksi
        $sAlokasi = selectQuery($dbname,'vhc_kegiatan','kelompok',"kodekegiatan='{$param['jns_kerja']}' and tipe='traksi'");
        $rAlokasi = fetchData($sAlokasi)[0];

        #== Ambil statusblok berdasarkan kelompok kegiatan traksi
        if($rAlokasi['kelompok']=='PNN'){
            $statusblok = " and statusblok = 'TM'";
        }else if($rAlokasi['kelompok']=='LC'){
            $statusblok = " and statusblok IN ('LC','TBM')";
        }else{
            $statusblok = " and statusblok = '".$rAlokasi['kelompok']."'";
        }

        $optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if($rAlokasi['kelompok']=='MIL'){
            $sBlok=selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi',"induk like '%{$param['lokasi_kerja']}%' and tipe='STATION'","tipe desc,kodeorganisasi");
        }elseif($rAlokasi['kelompok']=='EXT'){
            $sBlok=selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi,indukblok,namaindukblok',"induk like '%{$param['lokasi_kerja']}%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in 
            (select distinct kodeorg from $dbname.setup_blok where left(indukblok,6)='{$param['lokasi_kerja']}' and luasareaproduktif>0 and statusblok='TM' and status='A') group by indukblok",'tipe desc,kodeorganisasi');
        }elseif($rAlokasi['kelompok']=='INF'){
            $sBlok=selectQuery($dbname,'project','kode as kodeorganisasi,nama as namaorganisasi',"kodeorg like '%{$param['lokasi_kerja']}%' and posting='0'",'nama,tanggalmulai desc');
        }else{
            $sBlok=selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi,indukblok,namaindukblok',"induk like '%{$param['divisi']}%' and (tipe='BLOK' OR tipe='BIBITAN') and kodeorganisasi in 
            (select distinct kodeorg from $dbname.setup_blok where left(indukblok,6)='{$param['divisi']}' and luasareaproduktif>0 $statusblok and status='A') group by indukblok",'tipe desc,kodeorganisasi');
        }
        $resblk=fetchData($sBlok);
        foreach ($resblk as $rBlok) {
            if($rAlokasi['kelompok']!='INF'){
                $d=substr($rBlok['kodeorganisasi'],0,6);
                if($d!=$n){			
                    $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi,indukblok,namaindukblok',"kodeorganisasi='".$d."'");
                    $optBlok.="<optgroup label='".$nmorg[$d]."'>";
                }
            
                if($param['blok']!=""){
                    if($rBlok['indukblok']==$param['blok']){						
                        $optBlok.="<option value=".$rBlok['indukblok']." selected>".$rBlok['namaindukblok']."</option>";
                    }else{						
                        $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                    }
                }else{
                    $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                }
                $n=$d;
                if($d!=$n){
                    $optBlok.="</optgroup>";
                }
            }else{
                if($param['blok']!=""){
                    if($rBlok['indukblok']==$param['blok']){                        
                        $optBlok.="<option value=".$rBlok['indukblok']." selected>".$rBlok['namaindukblok']."</option>";
                    }else{                      
                        $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                    }
                }else{
                    $optBlok.="<option value=".$rBlok['indukblok'].">".$rBlok['namaindukblok']."</option>";
                }
            }
        }

        echo $optBlok."###".$param['blok'];
    break;
    case 'getdivisi':
        
		$sAlokasi = "select kelompok from ".$dbname.".vhc_kegiatan where kodekegiatan='".$pekerjaan."' and tipe='traksi'";
		$res=$owlPDO->query($sAlokasi) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$rAlokasi = $res->fetch();    
            
		//cek tipe
		if($rAlokasi['kelompok']=='TM'){
			$statusblok = " and statusblok = 'TM'";
		}else if($rAlokasi['kelompok']=='TBM'){
			$statusblok = " and statusblok IN ('LC','TB','TBM')";
		}else{
			$statusblok = " and statusblok = '".$rAlokasi['kelompok']."'";
		}

		$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($rAlokasi['kelompok']=='MIL'){
			$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk like '%".$lokasi."%' and tipe='STATION' order by tipe desc, kodeorganisasi asc";
		}elseif($rAlokasi['kelompok']=='EXT'){
			$sBlok="select left(kodeorganisasi,6) as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk like '%".$lokasi."%' and (tipe='BLOK' OR tipe='BIBITAN')
			and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,6)='".$lokasi."' and luasareaproduktif>0 and statusblok='TM' and status='A') group by left(kodeorganisasi,6)
			order by tipe desc, kodeorganisasi asc";
		}else{
			$sBlok="select left(kodeorganisasi,6) as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
			where induk like '%".$lokasi."%' and (tipe='BLOK' OR tipe='BIBITAN')
			and kodeorganisasi in (select distinct kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$lokasi."' and luasareaproduktif>0 ".$statusblok." and status='A') group by left(kodeorganisasi,6)
			order by tipe desc, kodeorganisasi asc";
		}	

		$res=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($rBlok=$res->fetch()){
			$nmblokinduk = makeOption($dbname, 'setup_blok', 'kodeorg,indukblok',"kodeorg='".$rBlok['kodeorganisasi']."'");
			$d=substr($rBlok['kodeorganisasi'],0,6);

			if($blok!=""){
				if($rBlok['kodeorganisasi']==$blok){						
					$optBlok.="<option value=".$rBlok['kodeorganisasi']." selected>".getNamaOrg($rBlok['kodeorganisasi'])."</option>";
				}else{						
					$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".getNamaOrg($rBlok['kodeorganisasi'])."</option>";
				}
			}else{
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".getNamaOrg($rBlok['kodeorganisasi'])."</option>";
			}
		}
        echo $optBlok;
    break;
    case'loaddata':
        $where = "";
        $where.=" and kodeorg in (".getOrgDetail(2).") ";
        if ($divsch != '') {
            $where.=" and kodetraksi='" . $divsch . "' ";
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
        $sql = "select count(*) as jmlhrow from " . $dbname . ".vhc_rkh where 1=1 " . $where . " group by kodeorg,kodetraksi,tanggal";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;
        $str = "SELECT * FROM " . $dbname . ".vhc_rkh
		where 1=1 " . $where . " group by kodeorg,kodetraksi,tanggal order by tanggal desc,kodetraksi asc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;
        $colspan = "11";
        if(count(fetchData($str))>0){

            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar = $res->fetch()) {
                $isi = '';
                $no+=1;
                $tab.="<tr class=rowcontent  id=tr_$no>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
                $tab.="<td>" . $bar['kodetraksi'] . " - " . $nmorg[$bar['kodetraksi']] . "</td>";
                $tab.="<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>";
                $tab.="<td align=center>" . $nmkar[$bar['updateby']] . "</td>";
                $tab.="<td align=center>" . tanggalnormal($bar['postingdate']) . "</td>";
                $tab.="<td hidden align=center>" . $nmkar[$bar['postingby']] . "</td>";
                if ($bar['posting'] == 0) {
                    $isi.="<td align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
                        onclick=\"edit('" . $bar['kodeorg'] . "','" . $bar['kodetraksi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                    $isi.="<td align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
                        onclick=\"del('" . $bar['kodetraksi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                   $isi.="<td align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' 
                        onclick=\"posting('" . $bar['kodetraksi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ></td>";
                } else {
                    if(in_array($_SESSION['empl']['jabatan'],$jab)){
                        $icon="images/icons/04/16/04.png";
                        $title="Unposting";
                        $unpost=" onclick=\"unposting('" . $bar['kodetraksi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ";
                    }else {
                        $icon="images/icons/04/16/02.png";
                        $title="Posted";
                        $unpost='';
                    }
                    $isi.="<td align=center></td><td align=center></td>";
                    $isi.="<td align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
                }
                $isi.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' onclick=\"html('" . $bar['kodetraksi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                $isi.="<td align=center>
                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"printpdf('".$bar['kodeorg']."','".substr($bar['tanggal'],0,7)."');\">
                </td>";
                $tab.=$isi;
                $tab.="</tr>";
            }
        }else{
            $tab.="<tr class=rowcontent><td colspan=".$colspan." align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
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
        $footd.="<tfoot>".createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage')."</tfoot>";
        // $footd.="</tr>
        //              <tr><td colspan=9 align=center>";
        // if ($page == '0') {
        //     $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        // } else {
        //     $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        // }
        // $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        // if (($page + 1) == $totrows) {
        //     $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        // } else {
        //     $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        // }
        // $footd.="</td>
        //     </tr>";
        echo $tab . "####" . $footd;
        break;
}
?>	
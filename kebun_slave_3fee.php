<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$method      = checkPostGet('method', '');
$kodeorg     = checkPostGet('kodeorg', '');
$periode     = checkPostGet('periode', '');
$spk         = checkPostGet('spk', '');
$nospb       = checkPostGet('nospb', '');
$tiket       = checkPostGet('tiket', '');
$blok        = checkPostGet('blok', '');
$tujuan      = checkPostGet('tujuan', '');
$harga_muat  = checkPostGet('harga_muat', '');
$harga_angkut= checkPostGet('harga_angkut', '');
$tanggal     = checkPostGet('tanggal', '');
$tglheader   = tanggalsystemn(checkPostGet('tgl', ''));
$tglsch      = checkPostGet('tglsch', '');
$divsch      = checkPostGet('divsch', '');
$divisi      = checkPostGet('divisi', '');
$kgwbdet     = checkPostGet('kgwbdet', '');
$rp_muat     = checkPostGet('rp_muat', '');
$rp_angkut   = checkPostGet('rp_angkut', '');
$kgwb        = checkPostGet('kgwb', '');
$kegangkut   = checkPostGet('kegangkut', '');
$kegmuat     = checkPostGet('kegmuat', '');
$periodebyr  = checkPostGet('periodebyr', '');
$nobapp      = checkPostGet('nobapp', '');
$jnskend     = checkPostGet('jnskend', '');
$pkstujuan   = checkPostGet('pkstujuan', '');
$kgwbpks     = checkPostGet('kgwbpks', '');
$kgbrd       = checkPostGet('kgbrd', '');
$potonganrp  = checkPostGet('potonganrp', '');
$ttlrowfee   = checkPostGet('ttlrowfee', '');
$nospkcr     = checkPostGet('nospkcr', '');
$kontrakcr   = checkPostGet('kontrakcr', '');
$divisi      = checkPostGet('divisi', '');
$baris      = checkPostGet('baris', '');

$namafee    = checkPostGet('namafee', '');
$jenisfee   = checkPostGet('jenisfee', '');
$jenisfeex  = checkPostGet('jenisfeex', '');
$rpfee      = checkPostGet('rpfee', '');
$jenistampil= checkPostGet('jenis', '');
$nojurnal   = checkPostGet('nojurnal', '');

$kgwbpks     =str_replace(",","",$kgwbpks);
$kgbrd       =str_replace(",","",$kgbrd);
$rp_muat     =str_replace(",","",$rp_muat);
$rp_angkut   =str_replace(",","",$rp_angkut);
$kgwbdet     =str_replace(",","",$kgwbdet);
$kgwb        =str_replace(",","",$kgwb);
$rpfee  =str_replace(",","",$rpfee);
if($kgwbdet==''){$kgwbdet=0;}

$kodept=makeOption($dbname,'organisasi','kodeorganisasi,induk',"length(kodeorganisasi)='4'");

$divsch             = checkPostGet('divsch', '');
$bloksch            = checkPostGet('bloksch', '');
$unitexp            = checkPostGet('unitexp', '');
$perexp             = checkPostGet('perexp', '');
$bjr                = checkPostGet('bjr', '');
$kgkebun            = checkPostGet('kgkebun', '');

if($periodebyr=='1'){
	$tanggal1=$periode."-01";
	$tanggal2=$periode."-15";
}elseif($periodebyr=='2'){
	$tanggal1=$periode."-16";
	$tanggal2=tglakhir($periode."-01");
}elseif($periodebyr=='0'){
	$tanggal1=$periode."-01";
	$tanggal2=tglakhir($periode."-01");
}else{
	$tanggal1=tanggalsystemn($param['tgl']);
	$tanggal2=tanggalsystemn($param['tglsd']);
}

$sql = "SELECT * FROM " . $dbname . ".keu_5akun";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optjnsfee[$bar['noakun']]=$bar['namaakun'];
}

$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optjnsfee[$bar['kodekegiatan']]=$bar['namakegiatan'];
}

$sql = "SELECT * FROM " . $dbname . ".kebun_5namafee where status=1 order by nama asc";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optnamafee[$bar['id']]=$bar['nama'];
}

$sql = "SELECT * FROM " . $dbname . ".log_5supplier";
$qry = fetchdata($sql);
foreach($qry as $bar){
	$optnamafee[$bar['supplierid']]=$bar['namasupplier'];
}

$jab = getPostingJabatan('panen');
// $nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmjns = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$nmjns+=array("GLOBAL"=>"GLOBAL");

$param['harga']=str_replace(",","",$param['harga']);

switch ($method) {
	case'gettanggal':
		if($periodebyr=='3'){			
			echo"##";
		}else{
			echo tanggalnormal($tanggal1)."##".tanggalnormal($tanggal2);
		}
	break;
	case'deletedetail':
        $str = "delete from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and nospb='" . $nospb . "' ";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'insert':
	try {
		$owlPDO->beginTransaction();
		$whr=" and tanggal between '".$tanggal1."' and '".$tanggal2."'";
		$cek = "select * from " . $dbname . ".kebun_rekapangkutantbsdtfee where kodeorg = '".$kodeorg."' and divisi = '".$divisi."'  ".$whr." and posting='1'";
		$rcek = fetchdata($cek);
		if(count($rcek)>0){			
			throw new PDOException("Transaksi tanggal ".$tanggal1." sampai ".$tanggal2." sudah pernah di posting.");
		}
		if($baris=='1'){			
			$str = "delete from " . $dbname . ".kebun_rekapangkutantbsdtfee where kodeorg = '".$kodeorg."'  and divisi = '".$divisi."' ".$whr."";
			$owlPDO->exec($str);
		}
		
		$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdtfee where nospb = '".$nospb."'";
		$res = fetchdata($str);
		if(count($res)>0 and $rpfee>0 and $res[0]['jenis']!=$param['jenisvhc']){
			throw new PDOException("Dalam satu nomor SPB jenis kendaraan tidak boleh berbeda.");
		}
		$kud=$ket='';
		//$ket="TBS ".$param['kgwbdet']." x Rp.".$param['harga']."";
		if($param['jenisH']=='tempunak'){
			$kud='kud';
			$ket="((TBS ".$param['kgwbdet']." x Rp.".$param['harga'].") x ".$param['persen'].")/100";
		}
		$datafee = array(
			'id'        => $namafee,
			'nospb'     => $nospb,
			'noakun'    => $jenisfee,
			'jenis'     => $jnskend,
			'blok'      => $blok,
			'kgtotal'   => $kgwbpks,
			'kgbrd'     => $kgbrd,
			'kgwb'      => $kgwbdet,
			'potonganrp'=> '',
			'rupiah'    => $rpfee,
			'jenisfee'  => $jenisfeex,
			'jenis'     => $param['jenisvhc'],
			'kodeorg'   => $kodeorg,
			'divisi'    => $divisi,
			'periode'   => $periode,
			'periodebyr'=> $periodebyr,
			'tgldari'   => $tanggal1,
			'tglsampai' => $tanggal2,
			'tanggal'   => $tanggal,
			'keterangan'=> $ket,
			'kud'       => $kud
		);
			
		$colsfee = array();
		foreach($datafee as $key=>$row) {
				$colsfee[] = $key;
		}
		$str = insertQuery($dbname,'kebun_rekapangkutantbsdtfee',$datafee,$colsfee); //exit("error".$str);
		if($rpfee>0){			
			$owlPDO->exec($str);
		}
	
		
		#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
		
	case'detail':
		OPEN_BOX();
        echo"
        <div class='table-scroll' style=height:60vh>
        <table border=0 cellpadding=2 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' width=30px>No</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['ticket'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</th>
            <th align=center rowspan='2'>Jenis Kend</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2'>TT</th>
            <th align=center rowspan='2'>PKS<br>Tujuan</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kgwb'] . "</th>
            <th align=center rowspan='2'>Sort</th>
            <th align=center rowspan='2'>Brd</th>
            <th align=center rowspan='2'>Kg Netto</th>
            <th align=center rowspan='2'>Kg Bayar</th>
			<th align=center colspan=5>Fee</th>
            <th align=center rowspan='2'>Action</th>
        </tr>
        <tr>
			<th align=center>" . $_SESSION['lang']['nama'] . "</th>
			<th align=center>" . $_SESSION['lang']['jenis'] . "</th>
			<th align=center>" . $_SESSION['lang']['noakun'] . "</th>
			<th align=center>" . $_SESSION['lang']['harga'] . "</th>
            <th align=center>" . $_SESSION['lang']['rp'] . "</th>			
        </tr></thead>
		<tbody id=inputharga>";
		
		$nmjns = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
		$nmjns['GLOBAL']='GLOBAL';
		
		$wh=$whr="";
		$wh.= " and a.kodeorg = '".$kodeorg."' and a.tanggal like '".$periode."%'";
		$wh.= " and a.blok like '".$divisi."%'";
		$whr.=" and a.tanggal between '".$tanggal1."' and '".$tanggal2."'";
		
		if($param['jenisH']=='tempunak'){
			$wh.= " and b.jenisfee='tempunak'";
			$diskgbyr="disabled";
		}else{
			$wh.= " and b.jenisfee!='tempunak'";			
			$diskgbyr="";
		}
		
		
        $no='0';
		$nourutspb=0;
		$tempurut=0;
		$tempnourutspb=0;
		
		$str = "select a.*,b.* from " . $dbname . ".kebun_spb_vw a left join " . $dbname . ".kebun_5daftarfee b on a.blok=b.blok where 1=1 ".$wh." ".$whr." and a.posting='1' group by a.nospb,a.blok,b.id,b.jenis,b.jenisfee";
		//exit("error".$str);
        $res = fetchdata($str);
        foreach($res as $bar){
			if($tempnourutspb==$bar['nospb']){
				$tempurut++;
			}else{
				$tempurut=1;
				$nourutspb++;
			}
			$tempnourutspb=$bar['nospb'];
			
			$a=$nourutspb%2;
			$xx="";
			if($a==0){
				$xx.=" style=background-color:#09F472";
			}
			
			$no++;
			
			$nx = "select sum(kgwb) as sumkgwb, sum(brondolan) as sumbrondolan,sum(kgwbnetto) as sumkgwbnetto from " . $dbname . ".kebun_spb_vw where 1=1 and nospb='".$bar['nospb']."'and blok='".$bar['blok']."' and tanggal='".$bar['tanggal']."'";
			#exit("error".$str);
			$brx = fetchdata($nx)[0];
			$kgbayar=$kgsort=0;
			/* 
			- Fee Keamanan           = Kg Bruto - Sortasi + Brondolan
			- Fee Transport (Ext/Int)= Kg Bruto - Sortasi
			- Fee Panen              = Kg Bruto - Sortasi - Brondolan
			- Fee Management         = Kg Bruto - Sortasi - Brondolan
			
			Fee Panen              = Kg Bruto - Sortasi - Brondolan                                                                           
			Fee Manajemen          = Kg Bruto - Sortasi - Brondolan                                                       
			Fee Transport (Ext/Int)= Kg Bruto - Sortasi = Kg Netto                                                                
			Fee Keamanan           = Kg Bruto - Sortasi = Kg Netto
			
			if($bar['jenisfee']=='keamanan'){
				$kgbayar=$brx['sumkgwbnetto']+$brx['sumbrondolan'];
			}elseif($bar['jenisfee']=='transport'){
				$kgbayar=$brx['sumkgwbnetto'];
			}elseif($bar['jenisfee']=='panen' and $bar['jenisfee']=='manajemen'){
				$kgbayar=$brx['sumkgwbnetto']-$brx['sumbrondolan'];
			}else{
				$kgbayar=$brx['sumkgwbnetto'];
			}
			 */
			
			if($bar['jenisfee']=='panen' or $bar['jenisfee']=='manajemen'){
				$kgbayar=$brx['sumkgwbnetto']-$brx['sumbrondolan'];
			}else{
				$kgbayar=$brx['sumkgwbnetto'];
			}
			
			$kgsort=$brx['sumkgwb']-$brx['sumkgwbnetto'];
			
			$n = "select * from " . $dbname . ".kebun_rekapangkutantbsdtfee where 1=1 and nospb='".$bar['nospb']."'and noakun='".$bar['jenis']."'and blok='".$bar['blok']."' and id='".$bar['id']."' and tanggal='".$bar['tanggal']."'";
			#exit("error".$str);
			$br = fetchdata($n)[0];
			
			if($br['kgwb']>0){
				$kgbayar2=$br['kgwb'];
			}else{
				$kgbayar2=$kgbayar;
			}
			
			$disjenis="";
			if($tempurut!='1'){
				#$disjenis="disabled";
			}
			if($param['jenisH']=='tempunak'){
				$br['jenis']='GLOBAL';
				$disjenis="disabled";
			}
			
			$optjns = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sql = "SELECT distinct jenisvhc FROM " . $dbname . ".kebun_5daftarfee";
			$qry = fetchdata($sql);
			foreach($qry as $barq){
				if($barq['jenisvhc']==$br['jenis']){
					$i="selected";
				}else{
					$i="";
				}
				$optjns.="<option value=" . $barq['jenisvhc'] . " ".$i.">" . $barq['jenisvhc'] . " - " . $nmjns[$barq['jenisvhc']] . "</option>";
			}
			
			
			echo"<tr class=rowcontent ".$xx." id=tr_".$no.">
				<td valign=top align=center>".$no."</td>
				<td valign=top id=tgl_".$no." align=center>".$bar['tanggal']."</td>
				<td valign=top align=center>".$bar['notiket']."</td>
				<td valign=top id=nospb_".$no." align=center>".$bar['nospb']."</td>
				<td valign=top align=center>".$bar['nokendaraan']."</td>
				<td valign=top><select id=jenisvhc_".$no." ".$disjenis." name=".$bar['nospb']."[] onchange=getharga('".$no."','','".$bar['nospb']."'); style=\"width:100px;\">" . $optjns . "</select></td>
				<td valign=top id=blok_".$no.">".$bar['blok']."</td>
				<td valign=top id=thntnm_".$no.">".$bar['tahuntanam']."</td>
				<td valign=top id=pkstujuan_".$no.">".$bar['penerimatbs']."</td>
				<td valign=top id=kgwbpks_".$no." align=right>".$brx['sumkgwb']."</td>
				<td valign=top align=right>".$kgsort."</td>
				<td valign=top align=right>".$brx['sumbrondolan']."</td>
				<td valign=top id=kgwb_".$no." align=right>".$kgbayar."</td>
				<td valign=top><input id=kgwbbyr_".$no." onkeyup=hitungrupiah('".$no."'); class=myinputtextnumber style=width:50px ".$diskgbyr." value='".$kgbayar2."'></td>
				";
			if($br['jenis']!=''){
				$wh="and jenisvhc='".$br['jenis']."'";
			}else{
				$wh="and jenisvhc='GLOBAL'";
			}
			
			$persen = 0;
			if($param['jenisH']=='tempunak'){
				$title=$style="";
				#harga persen
				$s = "select * from " . $dbname . ".kebun_5daftarfee where 1=1 and blok='".$bar['blok']."' and id='".$bar['id']."' ".$wh." and jenisfee='".$bar['jenisfee']."'";
				#exit("error".$str);
				$r = fetchdata($s)[0];
				$persen = $r['rp'];
				if(count(fetchdata($s))==0){
					$title="title=\"Persen pembagian untuk KUD belum ada.\"";
					$style="style=background-color:red;";
				}
				
				#ambil harga dari TBSINTI
				$s = "select harga from " . $dbname . ".pmn_hargabelitbs where 1=1 and substr(tanggal,1,10)='".$tanggal1."' and substr(tanggal2,1,10)='".$tanggal2."' and kodeorg='".$bar['penerimatbs']."' and supplierid='".$bar['kodeorg']."' and tipe='SUPPLIERTBSINT' and tahuntanam='".$bar['tahuntanam']."' order by updatetime desc limit 1";
				// echo $s."<br>";
				$r = fetchdata($s)[0];
				if(count(fetchdata($s))==0){
					$title="title=\"Harga disbun untuk ".getNamaOrg($bar['kodeorg'])." pabrik ".$bar['penerimatbs']." tahun tanam ".$bar['tahuntanam']." tanggal(1) ".$tanggal1." tanggal(2) ".$tanggal2." belum ada.\"";
					$style="style=background-color:red;";
				}
				$harga = $r['harga'];
				$rpbayar = ((($kgbayar2*$harga)*$persen)/100);
			}else{				
				$s = "select rp from " . $dbname . ".kebun_5daftarfee where 1=1 and blok='".$bar['blok']."' and id='".$bar['id']."' ".$wh." and jenisfee='".$bar['jenisfee']."'";
				#exit("error".$str);
				$r = fetchdata($s)[0];
				$harga = $r['rp'];
				$rpbayar = ($kgbayar2*$harga);
			}
			
			echo"<td ".$style." ".$title." valign=top align=left><input hidden id=namafee".$no." value='".$bar['id']."'>".$optnamafee[$bar['id']]."</td>";
			echo"<td ".$style." ".$title." valign=top align=left id=jenisfeex".$no.">".$bar['jenisfee']."</td>";
			echo"<td ".$style." ".$title." valign=top align=left><input hidden id=jenisfee".$no." value='".$bar['jenis']."'>".$optjnsfee[$bar['jenis']]."</td>";
			echo"<td ".$style." ".$title." valign=top align=right id=harga".$no.">".$harga."</td>";
			echo"<td ".$style." ".$title." valign=top align=right><input disabled class=myinputtextnumber style=width:60px id=rpfee".$no." value='".($rpbayar)."'></td>";
			
			echo"<td ".$style." ".$title." valign=top align=center><img src=images/application/application_delete.png class=zImgBtn title=Delete onclick=deldetail('".$no."');></td>";
			
			
			echo"<input type=hidden id=persen".$no." value=".$persen.">";
			echo"</tr>";
        }

		
		echo"</tbody><tfoot><tr class=rowcontent style=font-weight:bold>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=jumlahrow value=".$no.">
				<td colspan=20 align=right>
					<button id=tomboldetail class=mybutton onclick=\"saveAll('".$no."')\" >" . $_SESSION['lang']['saveall'] . "</button>
					<button id=tomboldetail class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
				</td>
			</tr>		
		</tfoot>
        </table>
		</div
        ";
        CLOSE_BOX();
	break;
	case'getharga':
		$str = "select * from " . $dbname . ".kebun_5daftarfee where 1=1 and blok='".$param['blok']."' and id='".$param['namafee']."' and jenisvhc='".$param['jenisvhc']."' and jenisfee='".$param['jenisfee']."'";
		
		$harga=0;
		$ttl=0;
		$res = fetchdata($str);
		if(count($res)>0){			
			$harga= $res[0]['rp'];
			$ttl  = ($res[0]['rp'])*($param['kgwb']);
		}
		
		
		echo $harga."##".$ttl;
	break;
	
	case'viewdetailx':
        $tab = "<table border=0 cellpadding=5 cellspacing=1 class=sortable width=100%>
        <thead><tr class=rowheader>
            <th align=center rowspan='2' width=30px>No</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center rowspan='2' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['jenis'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['harga'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['rp'] . "</th>
        </tr>
        </thead>";
		$oppotrp=makeOption($dbname,'kebun_rekapangkutantbsdt','nospb,potonganrp',"nospb='".$nospb."'");
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekapangkutantbsdt a 
		left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb where a.nospb = '" . $nospb . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$optkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['jeniskegiatan']."'");
			$opttt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['blok']."'");
			$no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left>" . $bar['nospb'] . "</td>";
            $tab.="<td align=left>" . $bar['blok'] . "</td>";
            $tab.="<td align=center>" . $opttt[$bar['blok']] . "</td>";
            $tab.="<td align=left>" . $optkeg[$bar['jeniskegiatan']] . "</td>";
			$tab.="<td align=right>" . @number_format($bar['kgwb'], 2) . "</td>";
			$tab.="<td align=right>" . @number_format($bar['rupiah']/$bar['kgwb'], 2) . "</td>";
			$tab.="<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
			
			@$tluasplan+=$bar['kgwb'];
			@$tluaspanen+=$bar['rupiah'];
        }
        $tab.="</tr>";
		$tab.="<tr class=rowcontent>";
        $tab.="<td align=center colspan=5><b>" . $_SESSION['lang']['potongan'] . " Rupiah</td>";
        $tab.="<td align=right></td>";
        $tab.="<td></td>";
        $tab.="<td align=right><b>" . @number_format($oppotrp[$nospb], 2) . "</td>";
        $tab.="</tr>";
		
        $tab.="<tr class=rowcontent>";
        $tab.="<td align=center colspan=5><b>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</td>";
        $tab.="<td align=right></td>";
        $tab.="<td></td>";
        $tab.="<td align=right><b>" . @number_format($tluaspanen-$oppotrp[$nospb], 2) . "</td>";
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
        break;
	
	case'loaddata':
        $where = "";
		/* $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='JABKRN'"; 
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);
		
		if($_SESSION['empl']['subbagian']=='' and in_array($_SESSION['empl']['kodejabatan'],$arrjab)){
			$where.=" and a.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.createby ='".$_SESSION['standard']['userid']."'"; 
		}else {
			$where.= " and a.kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		 */
		$where.= " and a.kodeorg in (".getOrgDetail(2).")";
        if ($divsch != '') {
            $where.=" and a.kodeorg='" . $divsch . "' ";
        }
        if ($tglsch != '') {
            $where.=" and a.tanggal like '" . $tglsch . "%' ";
        }
		
		if ($nospb != '') {
            $where.=" and a.nospb like '%".$nospb."%' ";
        }
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $no = 0;
		$sql = "select a.*,sum(kgtotal) as sumkgtotal, sum(kgwb) as sumkgwb, sum(rupiah) as sumrupiah from " . $dbname . ".kebun_rekapangkutantbsdtfee a where 1=1 ".$where." group by kodeorg,divisi,periode,periodebyr, kud, tgldari";
		$jlhbrs = count(fetchdata($sql));
        $no = $maxdisplay;
		
		
		$str = "select a.*,sum(kgtotal) as sumkgtotal, sum(kgwb) as sumkgwb, sum(rupiah) as sumrupiah from " . $dbname . ".kebun_rekapangkutantbsdtfee a where 1=1 ".$where." group by kodeorg,divisi,periode,periodebyr, kud, tgldari order by periode desc, periodebyr desc, divisi asc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
        foreach($res as $bar){
			$kdsupp=makeOption($dbname,'log_spkht','notransaksi,koderekanan',"notransaksi='".$nospk."'");
			@$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kdsupp[$nospk]."'");
			
			$tanggal1=tanggalnormal($bar['periode']."-01");
			if($bar['periodebyr']=='0'){
				$prdbyrx=tanggalnormal($bar['periode']."-01")." s/d ".tanggalnormal(tglakhir($bar['periode']."-01"));
				$tanggal2=tanggalnormal(tglakhir($bar['periode']."-01"));
			}elseif($bar['periodebyr']=='1'){
				$prdbyrx=tanggalnormal($bar['periode']."-01")." s/d ".tanggalnormal($bar['periode']."-15");
				$tanggal2=tanggalnormal($bar['periode']."-15");
			}elseif($bar['periodebyr']=='2'){
				$tanggal2=tanggalnormal(tglakhir($bar['periode']."-01"));
				$prdbyrx=tanggalnormal($bar['periode']."-16")." s/d ".tanggalnormal(tglakhir($bar['periode']."-01"));
			}else{
				$tanggal1=tanggalnormal($bar['tgldari']);
				$tanggal2=tanggalnormal($bar['tglsampai']);
				$prdbyrx=tanggalnormal($bar['tgldari'])." s/d ".tanggalnormal($bar['tglsampai']);
			}
			if($bar['periodebyr']=='3'){
				$nobapp = $bar['kodeorg']."/".$bar['divisi']."/".$bar['periode']."/".$bar['periodebyr'].$bar['kud'].str_replace("-","",tanggalsystemn($tanggal2));
			}else{			
				$nobapp = $bar['kodeorg']."/".$bar['divisi']."/".$bar['periode']."/".$bar['periodebyr'].$bar['kud'];
			}
			$nojurnal=makeOption($dbname,'keu_jurnalht','noreferensi,nojurnal',"noreferensi='".$nobapp."' and nojurnal like '%".$bar['kodeorg']."%'");
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . getNamaOrg($bar['kodeorg']). "</td>";
			$tab.="<td align=left>" . getNamaOrg($bar['divisi']). "</td>";
			if($bar['kud']!=''){				
				$tab.="<td align=left>KUD / Tempunak</td>";
			}else{
				$tab.="<td align=left>Lainnya</td>";
			}
			$tab.="<td align=center>" . $bar['periode']. "</td>";
			$tab.="<td align=center>" .$prdbyrx. "</td>";
			$tab.="<td align=right>" . number_Format($bar['sumkgtotal']). "</td>";
			$tab.="<td align=right>" . number_Format($bar['sumkgwb']). "</td>";
			$tab.="<td align=right>" . number_Format($bar['sumrupiah']). "</td>";
			$tab.="<td align=center style=cursor:pointer;color:blue; onclick=getjurnal('".getNamaOrg($bar['kodeorg'],'induk')."','".$nojurnal[$nobapp]."','".($tanggal2)."','".($tanggal2)."')>".$nojurnal[$nobapp]."</td>";
			
			if($bar['posting']=='0'){
				$tab.="<td width=20px align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".$bar['kud']."','".($tanggal1)."','".($tanggal2)."');\" ></td>";
				$tab.="<td width=20px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".$bar['kud']."','".($tanggal1)."','".($tanggal2)."');\" ></td>";
				
				$tab.="<td width=20px align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".$bar['kud']."','".($tanggal1)."','".($tanggal2)."','".$no."');\" ></td>";
			}else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".$bar['kud']."','".$nojurnal[$nobapp]."','".($tanggal1)."','".($tanggal2)."','".$no."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td></td><td></td>";
				$tab.="<td width=20px align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
			}
			
			$tab.="<td width=20px align=center><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".($tanggal1)."','".($tanggal2)."','html');\" ></td>";
			$tab.="<td width=20px align=center><img src=images/excel.jpg class=zImgBtn  title='Excel' onclick=\"previewexcel('".$bar['kodeorg']."','".$bar['divisi']."','" .$bar['periode']. "','".$bar['periodebyr']."','".($tanggal1)."','".($tanggal2)."','excel');\" ></td>";
			
			
        }
		
            
		$tab.="</tr>";
			
        $tab.="</table>";
        
		
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
		
	case'delete':
		if($periodebyr=='3'){			
			$whr=" and tgldari = '".$tanggal1."' and tglsampai ='".$tanggal2."'";
		}
		
        $str = "delete from " . $dbname . ".kebun_rekapangkutantbsdtfee where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and divisi='" . $divisi . "' and periodebyr='" . $periodebyr . "' and kud='" . $param['kud'] . "' ".$whr.""; //exit('error'.$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;	
	case'html':
        
		$arrdata=array();
		$jnsfee=array();
		$arrtgl=array();
		$arrblok=array();
		$arrspb=array();
		$data=array();
		$datablok=array();
		$datafee=array();
		if($param['kodeblok']!=''){
			$wh=" and blok='".$param['kodeblok']."'";
			$wh.=" and nospb='".$param['notransaksi']."'";
		}
		
		$str="select * from ".$dbname.".kebun_rekapangkutantbsdtfee where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and divisi='" . $divisi . "' and (tanggal between '".$tanggal1."' and '".$tanggal2."') ".$wh." order by tanggal asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$data[$val['id']][$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]=$val['blok'];
			
			if(($val['jenis']=='FT' || $val['jenis']=='TR') and $val['jenisfee']=='transport'){
				$j=$val['jenisfee']."int";
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']][$j]+=$val['rupiah'];
			}else{
				$datafee[$val['id']][$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']][$val['jenisfee']]+=$val['rupiah'];
			}
			
			if($val['jenisfee']=='transport'){
				## INT -> FT & TR
				$jnsfee[$val['jenisfee']]=$val['jenisfee']." External";
				$jnsfee[$val['jenisfee']."int"]=$val['jenisfee']." Internal";
			}else{
				$jnsfee[$val['jenisfee']]=$val['jenisfee'];				
			}
			
			
			##GET FROM PABRIK TIMBANGAN
			#$strx="select notransaksi,nokendaraan,beratbersih,kgpotsortasi from ".$dbname.".pabrik_timbangan where nospb='".$val['nospb']."'";
			$strx="select notiket as notransaksi,nokendaraan,sum(kgwbnetto) as kgwbnetto from ".$dbname.".kebun_spb_vw where nospb='".$val['nospb']."'";
			$resx=fetchdata($strx);
			
			if(($val['jenis']=='FT' || $val['jenis']=='TR') and $val['jenisfee']=='transport'){
				$j=$val['jenisfee']."int";
				$datablok[$val['id']][$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']][$j]['nettoakhir']=$val['kgwb'];
			}else{
				$datablok[$val['id']][$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']][$val['jenisfee']]['nettoakhir']=$val['kgwb'];
			}
			
			// $datablok[$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]['nettoawal']=$val['kgtotal'];
			// #$datablok[$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]['nettoakhir']=$val['kgwb']-$val['brondolan'];
			$datablok[$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]['ticket']=$resx[0]['notransaksi'];
			$datablok[$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]['nokendaraan']=$resx[0]['nokendaraan'];
			$datablok[$val['tanggal']][$val['nospb']][$val['jenis']][$val['blok']]['keterangan']=$val['keterangan'];
			
		}
		
		// echo "<pre>";
		// print_r($datablok);
		// echo "</pre>";
		
		if($jenistampil=='html'){
			$border=0;
			$vwidth="cellspacing=1 cellpadding=3";
		}elseif($jenistampil=='pdf'){
			$border=1;
			$vwidth="width=100%  cellspacing=0 cellpadding=3";
		}else{
			$border=1;
			$vwidth="cellspacing=1 cellpadding=3";
		}
		$tab.="<table class=sortable  border='".$border."' ".$vwidth.">
			<thead>
			<tr class=rowheader style='text-align:center'>
				<th rowspan=3>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=3>".$_SESSION['lang']['nama']."</th>
				<th rowspan=3>".$_SESSION['lang']['tanggal']."</th>
				<th rowspan=3>".$_SESSION['lang']['ticket']."</th>
				<th rowspan=3>".$_SESSION['lang']['nospb']."</th>
				<th rowspan=3>".$_SESSION['lang']['nokendaraan']."</th>
				<th rowspan=3>".$_SESSION['lang']['jenis']."</th>
				<th rowspan=3>".$_SESSION['lang']['blok']."</th>
				<th rowspan=3>".$_SESSION['lang']['divisi']."</th>
				<!--<th colspan=2>Berat TBS</th>-->
				<th colspan='".@((count($jnsfee)*3)+1)."'>Ongkos Angkut</th>
				<th rowspan=3>".$_SESSION['lang']['keterangan']."</th>
			</tr>
			
			<tr>
				<!--<th rowspan=2>Sebelum Grading (Kg)</th>
				<th rowspan=2>Setelah Grading (Kg)</th>-->";
			
			foreach ($jnsfee as $key => $fee) {
				$tab.="<th colspan=3>".$fee."</th>";
			}
			$tab.="<th rowspan=2>Total Pembayaran (Rp)</th>
			</tr>
			
			<tr>";

			foreach ($jnsfee as $key => $fee) {
				$tab.="<th>Kg</th>";
				$tab.="<th>Rp/Kg</th>";
				$tab.="<th>Jumlah Bayar (Rp)</th>";
			}

			$tab.="</tr>
			</thead>
			<tbody>";
			
			$no=0;
			$totall=0;
			$totnettoawal=0;
			$totnettoawalid=array();
			$totnettoakhir=array();
			$totnettoakhirid=array();
			$totfee=array();
			$totfeeid=array();
			$totallid=array();
			$no=0;
			
			foreach($data as $idpenerima => $vtgl){
				foreach($vtgl as $tanggal => $vspb){
					foreach($vspb as $nospb => $vjenis){
						foreach($vjenis as $jenis => $vblok){
							foreach($vblok as $blok){
								$no++;
								$tab.="<tr class=rowcontent style='vertical-align:top'>";
								$tab.="<td align=right>".$no."</td>";
								$tab.="<td align=left>".$optnamafee[$idpenerima]."</td>";
								$tab.="<td align=center style='min-width:70px'>".tanggalnormal($tanggal)."</td>";
								$tab.="<td>".$datablok[$tanggal][$nospb][$jenis][$blok]['ticket']."</td>";
								$tab.="<td>".$nospb."</td>";
								$tab.="<td>".$datablok[$tanggal][$nospb][$jenis][$blok]['nokendaraan']."</td>";
								$tab.="<td>".$jenis."</td>";
								$tab.="<td>".$blok."</td>";
								$tab.="<td>".substr($blok,0,6)."</td>";
								// $tab.="<td align=right>".hidezerodecimal($datablok[$tanggal][$nospb][$jenis][$blok]['nettoawal'],2)."</td>";
								// $tab.="<td align=right>".hidezerodecimal($datablok[$tanggal][$nospb][$jenis][$blok]['nettoawal'],2)."</td>";
								$totjlhbyr=0;
								foreach ($jnsfee as $key4 => $fee) {
									$tab.="<td align=right>".hidezerodecimal($datablok[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]['nettoakhir'],2)."</td>";
									if($datablok[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]['nettoakhir']>0){
										$tab.="<td align=right>".hidezerodecimal($datafee[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]/$datablok[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]['nettoakhir'],2)."</td>";
									}else{
										$tab.="<td align=right></td>";
									}
									$tab.="<td align=right>".hidezerodecimal($datafee[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4],2)."</td>";
									$totjlhbyr+=$datafee[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4];
									$totfee[$key4]+=$datafee[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4];
									$totfeeid[$key4][$idpenerima]+=$datafee[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4];

									$totnettoakhir[$key4]+=$datablok[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]['nettoakhir'];
									$totnettoakhirid[$idpenerima][$key4]+=$datablok[$idpenerima][$tanggal][$nospb][$jenis][$blok][$key4]['nettoakhir'];
								}
								$tab.="<td align=right>".hidezerodecimal($totjlhbyr,2)."</td>";
								$tab.="<td>".$datablok[$tanggal][$nospb][$jenis][$blok]['keterangan']."</td>";
								
								$totnettoawal+=$datablok[$tanggal][$nospb][$jenis][$blok]['nettoawal'];
								$totnettoawalid[$idpenerima]+=$datablok[$tanggal][$nospb][$jenis][$blok]['nettoawal'];
								$totall+=$totjlhbyr;
								$totallid[$idpenerima]+=$totjlhbyr;
								
								$tab.="</tr>";
							}
						}
					}						
				}
				$tab.="<tr class=rowcontent style='background-color:cyan;font-weight:bold'>
				<td colspan=9 align=center>SUB TOTAL ".$optnmfee[$idpenerima]."</td>";
				// $tab.="<td align=right>".hidezerodecimal($totnettoawalid[$idpenerima],2)."</td>";
				// $tab.="<td align=right>".hidezerodecimal($totnettoakhirid[$idpenerima],2)."</td>";
					
				foreach ($jnsfee as $key4 => $fee) {
					$tab.="<td align=right>".hidezerodecimal($totnettoakhirid[$idpenerima][$key4],2)."</td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right>".hidezerodecimal($totfeeid[$key4][$idpenerima],2)."</td>";
				}

					$tab.="<td align=right>".hidezerodecimal($totallid[$idpenerima],0)."</td>
					<td></td>
				</tr>";
				
			}
			
			$tab.="<tr class=rowcontent style='background-color:#E8DAEF;font-weight:bold'>
				<td colspan=9 align=center>T O T A L</td>";
			// $tab.="<td align=right>".hidezerodecimal($totnettoawal,2)."</td>";
			// $tab.="<td align=right>".hidezerodecimal($totnettoakhir,2)."</td>";
				
			foreach ($jnsfee as $key4 => $fee) {
				$tab.="<td align=right>".hidezerodecimal($totnettoakhir[$key4],2)."</td>";
				$tab.="<td align=right></td>";
				$tab.="<td align=right>".hidezerodecimal($totfee[$key4],2)."</td>";
			}

				$tab.="<td align=right>".hidezerodecimal($totall,0)."</td>
				<td></td>
			</tr>";
				
		$tab.="</tbody>
			</table>";
        
		
		if($jenistampil=='html'){			
			echo $tab;
		}else{
			$stream=$tab;
			$stream.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
			$tempnm = explode("/",$_SERVER['PHP_SELF']);
			$nop_ = substr($tempnm[2],0,strripos($tempnm[2],'.'));
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
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
		
	break;
	
	case'posting':
	try {
	$owlPDO->beginTransaction();
		
		if($periodebyr=='3'){
			$nobapp = $kodeorg."/".$divisi."/".$periode."/".$periodebyr.$param['kud'].str_replace("-","",$tanggal2);
		}else{			
			$nobapp = $kodeorg."/".$divisi."/".$periode."/".$periodebyr.$param['kud'];
		}
		
		// exit("error".$nobapp);
		#ambil data fee
		$datafee=array();
		$str = "select a.* from " . $dbname . ".kebun_rekapangkutantbsdtfee a where 1=1 and kodeorg = '" . $kodeorg . "' and a.periode='" . $periode . "' and a.divisi='" . $divisi . "' and periodebyr='".$periodebyr."'  and kud='" . $param['kud'] . "' and (tanggal between '".$tanggal1."' and '".$tanggal2."')"; //exit("error".$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
			$datafee[$bar['nospb']][$bar['id']][$bar['blok']][$bar['noakun']]+=$bar['rupiah'];
			$ttlall+=$bar['rupiah'];
        }
		
		if(count($datafee)>0){
			#======================== Nomor Jurnal =============================
			if($param['kud']=='kud'){
				#tempunak
				$kodeJurnal = 'PNN20';
			}else{				
				$kodeJurnal = 'PNN19';
			}
			$queryParam = selectQuery($dbname,'keu_5parameterjurnal','noakunkredit',
				"kodeaplikasi='KBN' and jurnalid='".$kodeJurnal."'"); #exit("error".$queryParam);
			$resParam = fetchData($queryParam);
			if(count($resParam)==0){
				throw new PDOException("Kode parameter jurnal ".$kodeJurnal.", belum ada, silahkan ditambahkan terlebih dahulu.");
			}
			
			
			// print_r($kodept);
			
			# Get Journal Counter
			$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
				"kodeorg='".$kodept[$kodeorg]."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeorg."'
				and periode='".substr($tanggal2,0,7)."'");
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter']+1,3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-","",$tanggal2)."/".$kodeorg."/".$kodeJurnal."/".$konter;
			// exit("Error:".$queryJ);
			
			$dataRes=array();
			
			$dataRes['header'] = array(
				'nojurnal'     =>$nojurnal,
				'kodejurnal'   =>$kodeJurnal,
				'tanggal'      =>$tanggal2,
				'tanggalentry' =>date('Ymd'),
				'posting'      =>'1',
				'totaldebet'   =>'0',
				'totalkredit'  =>'0',
				'amountkoreksi'=>'0',
				'noreferensi'  =>$nobapp,
				'autojurnal'   =>'1',
				'matauang'     =>'IDR',
				'kurs'         =>'1',
				'revisi'       =>'0'
			);
			
			$nmfee=makeOption($dbname,'kebun_5namafee','id,nama');
			$noUrut=1;
			$totalrpfee=0;$noUrut12=0;
			foreach($datafee as $nospbfee => $valid){
				foreach($valid as $idfee => $valblok){
					foreach($valblok as $blokfee => $valakun){
						foreach($valakun as $akunfee => $rpfee){
							if(strlen($akunfee)>7){
								#kegiatan kebun harus ada kodekegiatan
								$optnoakun=makeOption($dbname,'setup_kegiatan','kodekegiatan,noakun');
								$akundet=$optnoakun[$akunfee];
								$kdkegiatan=$akunfee;
							}else{
								$akundet=$akunfee;
								$kdkegiatan='';
							}
							$noUrut12+=1;
							// if($noUrut12<count($datafee)){
								// $rpfee=round($rpfee);
								// $tempttl+=$rpfee;
							// }else{
								// $rpfee=round($ttlall-$tempttl);
							// }
							
							$rpfee=floor($rpfee);
							#Debet
							$dataRes['detail'][] = array(
								'nojurnal'    =>$nojurnal,
								'tanggal'     =>$tanggal2,
								'nourut'      =>$noUrut,
								'noakun'      =>$akundet,
								'keterangan'  =>$optnamafee[$idfee].', Doc: '.$nobapp.', Prd: '.$tanggal1.' s/d '.$tanggal2,
								'jumlah'      =>$rpfee,
								'matauang'    =>'IDR',
								'kurs'        =>'1',
								'kodeorg'     =>$kodeorg,
								'kodekegiatan'=>$kdkegiatan,
								'kodeasset'   =>'',
								'kodebarang'  =>'',
								'nik'         =>'',
								'kodecustomer'=>'',
								'kodesupplier'=>'',
								'noreferensi' =>$nospbfee,
								'noaruskas'   =>'',
								'kodevhc'     =>'',
								'nodok'       =>'',
								'kodeblok'    =>$blokfee,
								'revisi'      =>'0',
								'kodesegment' => '0000000001'
							);
							$noUrut+=1;
							
							$totalrpfee+=$rpfee;
							$dataRes['header']['totaldebet'] += $rpfee;
							$dataRes['header']['totalkredit'] += $rpfee*(-1);
							
						}
					}
				}
			}
			
			if(round($totalrpfee)<round($ttlall)){
				$noUrut+=1;
				#Debet
				$dataRes['detail'][] = array(
					'nojurnal'    =>$nojurnal,
					'tanggal'     =>$tanggal2,
					'nourut'      =>$noUrut,
					'noakun'      =>$akundet,
					'keterangan'  =>$optnamafee[$idfee].', Doc: '.$nobapp.', Prd: '.$tanggal1.' s/d '.$tanggal2,
					'jumlah'      =>round($ttlall)-$totalrpfee,
					'matauang'    =>'IDR',
					'kurs'        =>'1',
					'kodeorg'     =>$kodeorg,
					'kodekegiatan'=>$kdkegiatan,
					'kodeasset'   =>'',
					'kodebarang'  =>'',
					'nik'         =>'',
					'kodecustomer'=>'',
					'kodesupplier'=>'',
					'noreferensi' =>$nospbfee,
					'noaruskas'   =>'',
					'kodevhc'     =>'',
					'nodok'       =>'',
					'kodeblok'    =>$blokfee,
					'revisi'      =>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut+=1;
				
				$totalrpfee+=(round($ttlall)-$totalrpfee);
				$dataRes['header']['totaldebet'] += (round($ttlall)-$totalrpfee);
				$dataRes['header']['totalkredit'] += (round($ttlall)-$totalrpfee)*(-1);
			}
			
		// echo"<pre>";
		// print_r($totalrpfee);
		// echo" ----- ";
		// print_r($ttlall);
		// exit("error");
		
			$noUrut+=1;
			#Kredit
			$dataRes['detail'][] = array(
				'nojurnal'    =>$nojurnal,
				'tanggal'     =>$tanggal2,
				'nourut'      =>$noUrut,
				'noakun'      =>$resParam[0]['noakunkredit'],
				'keterangan'  =>'Doc: '.$nobapp.', Periode : '.$tanggal1.' sd '.$tanggal2,
				'jumlah'      =>$totalrpfee*(-1),
				'matauang'    =>'IDR',
				'kurs'        =>'1',
				'kodeorg'     =>$kodeorg,
				'kodekegiatan'=>'',
				'kodeasset'   =>'',
				'kodebarang'  =>'',
				'nik'         =>'',
				'kodecustomer'=>'',
				'kodesupplier'=>'',
				'noreferensi' =>$nospbfee,
				'noaruskas'   =>'',
				'kodevhc'     =>'',
				'nodok'       =>'',
				'kodeblok'    =>'',
				'revisi'      =>'0',
				'kodesegment' => '0000000001'
			);
			
			
			
			#insert header
			$cols = array();
			foreach($dataRes['header'] as $key=>$row) {
					$cols[] = $key;
			}
			$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header'],$cols);
			$owlPDO->exec($queryH);
			
			# Detail
			foreach($dataRes['detail'] as $key=>$dataDet) {
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
				$owlPDO->exec($queryD);
			}
			
			$queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']+1),
				"kodeorg='".$kodept[$kodeorg]."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeorg."' and periode='".substr($tanggal2,0,7)."'");
			$owlPDO->exec($queryKonter);
			
			
			$query = updateQuery($dbname,'kebun_rekapangkutantbsdtfee',array('posting'=>'1','noreffjurnal'=>$nobapp),
				"kodeorg = '" . $kodeorg . "' and periode='" . $periode . "' and divisi='" . $divisi . "' and periodebyr='".$periodebyr."'  and kud='" . $param['kud'] . "' and (tanggal between '".$tanggal1."' and '".$tanggal2."')");
			$owlPDO->exec($query);
			
			$str = "select sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where nojurnal ='".$nojurnal."'";
			$res = fetchdata($str);
			if(($res[0]['jumlah'])!=0){
				throw new PDOException("Jurnal tidak balance, proses dibatalkan.");
			}
			
			/*
			$queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
				"kodeorg='".$kodept[$kodeorg]."' and kodekelompok='".$kodeJurnal."' and kodeunit='".$kodeorg."'
				and periode='".substr($tanggal2,0,7)."'");
			*/
		}#tutup if ada fee
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	break;
	case'unposting':
	try {
		$owlPDO->beginTransaction();
		
		$updaterekap = array(
			'posting' => '0',
			'noreffjurnal' => ''
			);

		$where = "periode='".$periode."' and kodeorg='".$kodeorg."' and periodebyr='".$periodebyr."' and divisi='".$divisi."' and kud='" . $param['kud'] . "' and (tanggal between '".$tanggal1."' and '".$tanggal2."')";
		$str = updateQuery($dbname,'kebun_rekapangkutantbsdtfee',$updaterekap,$where);
		$owlPDO->exec($str);
		
		
		#hapus jurnal fee
		$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal = '" . $nojurnal . "'";
		$owlPDO->exec($str);
		
		
		#exit("error $str");
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	
	break;
		
	##==============================================================================================
	
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
    
}
?>	
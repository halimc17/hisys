<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$method       = checkPostGet('method', '');
$notransaksi  = checkPostGet('notransaksi', '');
$periode      = checkPostGet('periode', '');
$kodeorg      = checkPostGet('kodeorg', '');
$jenis        = checkPostGet('jenis', '');
$nama         = checkPostGet('nama', '');
$idlahan      = checkPostGet('idlahan', '');
$nosppt       = checkPostGet('nosppt', '');
$jlhsppt      = checkPostGet('jlhsppt', '');
$lokasi       = checkPostGet('lokasi', '');
$luaslahan    = checkPostGet('luaslahan', '');
$luastantum   = checkPostGet('luastantum', '');
$harga        = checkPostGet('harga', '');
$hargatantum  = checkPostGet('hargatantum', '');
$rupiah       = checkPostGet('rupiah', '');
$rupiahtantum = checkPostGet('rupiahtantum', '');
$rpsppt       = checkPostGet('rpsppt', '');
$totalrp      = checkPostGet('totalrp', '');
$numrow       = checkPostGet('numrow', '');
$kepada       = checkPostGet('kepada', '');
$notrsch       = checkPostGet('notrsch', '');
$totalrp=str_replace(',','',$totalrp);
$rpsppt=str_replace(',','',$rpsppt);
$rupiah=str_replace(',','',$rupiah);
$rupiahtantum=str_replace(',','',$rupiahtantum);
$hargatantum=str_replace(',','',$hargatantum);
$harga=str_replace(',','',$harga);
$luaslahan=str_replace(',','',$luaslahan);
$luastantum=str_replace(',','',$luastantum);

$keterangan = checkPostGet('keterangan', '');
$namafile   = checkPostGet('namafile', '');

$divsch     = checkPostGet('divsch', '');
$periodesch = checkPostGet('periodesch', '');
$bloksch    = checkPostGet('bloksch', '');

$unitexp    = checkPostGet('unitexp', '');
$perexp     = checkPostGet('perexp', '');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$pt = checkPostGet('pt', '');
$jenisupload = checkPostGet('jenisupload', '');
$xxx = checkPostGet('xxx', '');
$yyy = checkPostGet('yyy', '');
$iii = checkPostGet('iii', '');
$zzz = checkPostGet('zzz', '');
$namafile = checkPostGet('namafile', '');
if ($iii == 'undefined') {
    $iii = '';
}
if ($zzz == 'undefined') {
    $zzz = '';
}
$nmmasyarakat = makeOption($dbname, 'pad_5masyarakat', 'padid,nama');
$kodepemby = makeOption($dbname, 'lgl_kodepemby', 'kode,nama');

$path = "fileupload/lgl_pemblahan/";
$pathx = "fileupload/lgl_GRLTT/";
$tmpTgl = explode('-',$periode);	
$todayhis=date('Y-m-d h:i:s');

$kriteria = checkPostGet('kriteria', '');
$kriteriax = checkPostGet('kriteriax', '');
$id = checkPostGet('id', '');
$emodul = 'PG';

$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);
switch ($method) {
	case'getstatuslahan':
		$tab="<fieldset>";
		$tab.="<table cellspacing=1 border=0 class=sortable width=100%>
			<thead>
                <tr class=rowheader>
					<td rowspan=2 align=center>No</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['id'] . "</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['unit'] . "</td>                     
					<td rowspan=2 align=center>" . $_SESSION['lang']['pemilik'] . "</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['lokasi'] . " / (No.Persil)</td>                       
					<td rowspan=2 align=center>SPPT</td>
					<td rowspan=2 align=center>Jlh SPPT</td>
					<td rowspan=2 align=center hidden>" . $_SESSION['lang']['desa'] . "</td>               
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Inti</td>             
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Plasma</td>             
					<td rowspan=2 align=center>" . $_SESSION['lang']['luas'] . " Total</td>    
					<td rowspan=2 align=center>" . $_SESSION['lang']['bisaditanam'] . "</td> 
					<td rowspan=2 align=center>" . $_SESSION['lang']['blok'] . "</td>    
					<td colspan=4 align=center>" . $_SESSION['lang']['batas'] . "</td> 
					<td colspan=4 align=center>Koordinat</td> 
					<td rowspan=2 align=center>Alamat</td>
					<td rowspan=2 align=center>" . $_SESSION['lang']['keterangan'] . "</td> 
					<td align=center rowspan=2 align=center>" . $_SESSION['lang']['status'] . "</td>      
					<td rowspan=2 align=center>" . $_SESSION['lang']['updateby'] . "</td>   
					<td rowspan=2  align=center > File </td> 
                </tr><tr class=rowheader>   
					<td align=center>" . $_SESSION['lang']['batastimur'] . "</td>                      
					<td align=center>" . $_SESSION['lang']['batasbarat'] . "</td>  
					<td align=center>" . $_SESSION['lang']['batasutara'] . "</td>
					<td align=center>" . $_SESSION['lang']['batasselatan'] . "</td> 
					<td align=center>UL_X</td> 
					<td align=center>UL_Y</td> 
					<td align=center>LR_X</td> 
					<td align=center>LR_Y</td> 
                </tr></thead>
			<tbody>";
        $no='';
		$str1 = "select a.*,b.nama,b.alamat as alamatb,b.desa,c.namakaryawan from " . $dbname . ".pad_lahan a
				left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid 
				left join " . $dbname . ".datakaryawan c on a.updateby=c.karyawanid  where idlahan='".$idlahan."'   
				order by a.idlahan desc, b.nama asc ,b.desa asc";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
			if($bar1->statuspermintaandana == 0){
				$stdana = "Belum Disetujui";}
			if($bar1->statuspermintaandana == 1){
				$stdana = "Sudah Disetujui";
			}
			$no++;
			@$stkades = $bar1->statuskades == 1 ? tanggalnormal($bar1->tanggalkades) : "";
			@$stcamat = $bar1->statuscamat == 1 ? tanggalnormal($bar1->tanggalcamat) : "";
			$nmdesa=makeOption($dbname,'desa','iddes,desa',"iddes='".$bar1->desa."'");
			$tab.="<tr class=rowcontent> ";
			$tab.="<td valign=top align=center>" . $no . "</td>
				   <td valign=top align=center>" . $bar1->idlahan. "</td>
				   <td valign=top>" . $bar1->unit . "</td>
				   <td valign=top>" . $bar1->nama . "</td>
				   <td valign=top>" . $bar1->lokasi . "</td>                                 
				   <td valign=top>" . $bar1->shm . "</td>                                 
				   <td valign=top align=right>" . $bar1->jmlsppt . "</td>                                 
				   <td valign=top hidden>" . $nmdesa[$bar1->desa] . "</td>
				   <td valign=top align=right>" . $bar1->luasinti . "</td>  
				   <td valign=top align=right>" . $bar1->luasplasma . "</td>  
				   <td valign=top align=right>" . $bar1->luas . "</td>  
				   <td valign=top align=right>" . $bar1->luasdapatditanam  . "</td>
				   <td valign=top>" . $bar1->kodeblok . "</td>    
				   <td valign=top>" . $bar1->batastimur . "</td>
				   <td valign=top>" . $bar1->batasbarat . "</td>
				   <td valign=top>" . $bar1->batasutara . "</td>
				   <td valign=top>" . $bar1->batasselatan . "</td>
				   <td valign=top>" . $bar1->koordinatulx . "</td>
				   <td valign=top>" . $bar1->koordinatuly . "</td>
				   <td valign=top>" . $bar1->koordinatlrx . "</td>
				   <td valign=top>" . $bar1->koordinatlry . "</td>
				   <td valign=top>" . $bar1->alamat . "</td>  
				   <td valign=top>" . $bar1->keterangan . "</td> 
					<td valign=top>" . $stdana . "</td>
				   <td valign=top>" . $bar1->namakaryawan . "</td>";
				   
			$str = "select * from ".$dbname.".listfile_lgl_grltt where kodept = '".$bar1->unit."' and status='1' and jenis='statuslahan' and field1='".$bar1->idlahan."' and field2='".$bar1->pemilik."'";

			$res = fetchData($str);
			if (empty($res)) {
				$tab.="<td  valign=top style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>"; 
			} else {
				$namafile='';
				$nox=0;
				foreach($res as $key => $val) {
					$nox++;
					if($namafile==''){
						$namafile="".$nox.". <a href='".$pathx.$val['namafile']."' download>".$val['namafile']."</a>";
					}else{
						$namafile.="<br>".$nox.". <a href='".$pathx.$val['namafile']."' download>".$val['namafile']."</a>";
					}
				}
			$tab.= "<td valign=top style='text-align:left'>".$namafile."</td>";
			}                               
		  $tab.="</td>";
		}
		$tab.="</tbody>
			</table>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	
	case'getdaftarmasy':
		$tab="<fieldset>";
		$tab.="<table cellspacing=1 border=0 class=sortable width=100%>
			<thead><tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nomor']."</td>
				<td align=center>".$_SESSION['lang']['nama']."</td>
				<td align=center>".$_SESSION['lang']['alamat']."</td>
				<td align=center>".$_SESSION['lang']['desa']."</td>
				<td align=center>".$_SESSION['lang']['kecamatan']."</td>
				<td align=center>".$_SESSION['lang']['kabupaten']."</td>
				<td align=center>".$_SESSION['lang']['bank']."</td>
				<td align=center>".$_SESSION['lang']['atasnama']."</td>
				<td align=center>".$_SESSION['lang']['rekening']."</td>
				<td align=center>".$_SESSION['lang']['noktp']."</td>
				<td align=center>Upload</td>
			</tr>
			</thead>
			<tbody>";
        $no='';
		$str = " select * from ".$dbname.".pad_lahan a left join ".$dbname.".pad_5masyarakat b on a.pemilik=b.padid where idlahan='".$idlahan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
            $no+=1;
			$nmdesa=makeOption($dbname,'desa','iddes,desa',"iddes='".$bar['desa']."' and id_kec='".$bar['kecamatan']."'");
			$nmkec=makeOption($dbname,'kecamatan','idkec,kecamatan',"id_kab='".$bar['kabupaten']."' and idkec='".$bar['kecamatan']."'");
			$nmkab=makeOption($dbname,'kabupaten','id,kabupaten',"id='".$bar['kabupaten']."'");
			$nmbank=makeOption($dbname,'keu_5daftarbank','kodebank,namabank',"kodebank='".$bar['kodebank']."'");
            
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";	
            $tab.="<td align=left>" . $bar['nama'] . "</td>";	
            $tab.="<td align=left>" . $bar['alamat'] . "</td>";	
            $tab.="<td align=left>" . $nmdesa[$bar['desa']] . "</td>";	
            $tab.="<td align=left>" . $nmkec[$bar['kecamatan']] . "</td>";	
            $tab.="<td align=left>" . $nmkab[$bar['kabupaten']] . "</td>";	
            $tab.="<td align=left>" . $nmbank[$bar['kodebank']] . "</td>";	
            $tab.="<td align=left>" . $bar['namapemilikrek'] . "</td>";	
            $tab.="<td align=left>" . $bar['norek'] . "</td>";	
            $tab.="<td align=left>" . $bar['noktp'] . "</td>";	
			
			
			
			$strx = "select * from ".$dbname.".listfile_lgl_grltt where status='1' and jenis='datadiri' and field1='".$bar['pemilik']."' and field2='".$bar['noktp']."' and field3='".$bar['nama']."'";

			$resx = fetchData($strx);
			if (empty($resx)) {
			   $tab.="<td style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>"; 
			} else {
				$namafile='';
				$nox=0;
				foreach($resx as $key => $val) {
					$nox++;
					if($namafile==''){
						
						$namafile="".$nox.". <a href='".$pathx.$val['namafile']."' download>".$val['namafile']."</a>";
					}else{
						$namafile.="<br>".$nox.". <a href='".$pathx.$val['namafile']."' download>".$val['namafile']."</a>";
						#$namafile.="<br>".$nox.".".$val['namafile'];
					}
				}
				$tab.="<td style='text-align:left'>".$namafile."</td>";
			}  
			
		}
		$tab.="</tbody>
			</table>";
		$tab.="</fieldset>";
		echo $tab;
	break;
	
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
        $str = "select a.*,b.nopol,b.tahunperolehan,b.kepemilikan,b.kodetraksi from " . $dbname . ".lgl_pembebasanlahan a left join " . $dbname . ".vhc_5master b on a.kodevhc=b.kodevhc  where a.kodeorg='" . $unitexp . "' and periode='".$perexp."'";
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
					<td>" .$periode . "</td>
				</tr>
				
				</table><hr>";
		
		@$countApprove = getCountApproval('GRL',$kodeorg);
		
		
		$str=" select * from ".$dbname.".lgl_pembebasanlahan where  notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
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
				@$arrApp = detailApprove($i,$notransaksi,'GRL');
				
				if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
					$tngl='';
				}else{
					$tngl=($arrApp['tanggal']);
				}
				if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
					$tab.= "<td>".$arrApp['nama']."
						<br />".$arrHsl[$arrApp['status']].", ".$tngl."
						<br />".$arrApp['komentar']."
					</td>";
				}else{
					$tab.= "<td>&nbsp;</td>";
				}
			}
	$tab.= "</tbody></table>";
	
	#status tolak
	$str="select *, max(level) as level from ".$dbname.".approval_return where notransaksi='".$notransaksi."' group by keterangan";
	$res=fetchdata($str);
	$row=count($res);
	if($row>0){
		$no=0;
		foreach($res as $key=>$val){
			$no++;
			$tab.="<br><table border=0 cellspacing=1 class=sortable>
					<thead>
					<tr style='font-weight:bold'>
						<td colspan='".($val['level'])."'>Return / Tolak - ".$no."</td>
					</tr>
					<tr style='font-weight:bold'>";
						for($i=1;$i<=$val['level'];$i++) {
							$tab.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan'].$i."</td>";
						}
					$tab.="</tr>
				</thead>
				<tbody>
					<tr class=rowcontent>";
					for($i=1;$i<=$val['level'];$i++) {
						$strx="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' and level='".$i."' and keterangan='".$val['keterangan']."'";
						$resx=fetchdata($strx);
						$color='';
						if($resx[0]['status']==3){
							$color=" style=background-color:red ";
						}
						$tab.="<td ".$color.">".$nmkar[$resx[0]['karyawanid']]."
							<br>	
							".$arrHsl[$resx[0]['status']]."
							<br>	
							".($resx[0]['status']<1?'':tanggalnormal(substr($resx[0]['tanggal'],0,10)))."
							<br>	
							".$resx[0]['komentar']."
						</td>";
					}
					$tab.="</tr>
				</tbody>
				</table>";
		}
	}
	$tab.="<hr>";
	
	$tab.= "<table cellpadding=1 cellspacing=1 border=0 class=sortable width=100%>
			<thead><tr class=rowheader>
			<td align=center rowspan=2 width=20px>No</td>
			<td align=center rowspan=2 width=50px>" . $_SESSION['lang']['jenis'] . "</td>
            <td align=center rowspan=2 width=150px>" . $_SESSION['lang']['nama'] . "</td>
            <td align=center colspan=1>SPPT/SHM</td>
            <td align=center rowspan=2>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center colspan=2>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center colspan=2 width=50px>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center colspan=4 width=50px>" . $_SESSION['lang']['rupiah'] . "</td>
            <td align=center colspan=4>Pembayaran</td>
			
			
        </tr>
		<tr>
            <td align=center>No</td>
            <td hidden align=center width=20px>Jlh</td>
            <td align=center width=20px>".$_SESSION['lang']['lahan']."</td>
            <td align=center width=20px>TanTum</td>
            <td align=center width=50px>Lahan/Ha<br>Rp</td>
            <td align=center width=50px>TanTum/Ha<br>Rp</td>
            <td align=center width=50px>Pembayaran<br>Lahan Rp</td>
			<td align=center width=50px>Pembayaran<br>TanTum Rp</td>
            <td align=center width=50px>Biaya<br>Pembuatan Surat Rp</td>
            <td align=center width=50px>Total di Bayar</td>
            <td align=center>Kas Bank</td>
            <td align=center>Tanggal</td>
            <td align=center>Jumlah</td>
            <td align=center width=50px>Ajukan</td>
			</tr>
			</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".lgl_pembebasanlahan where notransaksi='" . $notransaksi . "' order by jenis asc, nama asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkode=$nmtantum=$nama=$nmalamat='';
			while ($bar = $res->fetch()) {
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==0){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$nmkode=makeOption($dbname,'lgl_kodepemby','kode,nama',"kode='".$bar['nama']."'");
				if($bar['jenis']=='GRLTT'||$bar['jenis']=='SHM'){
					$strtantum = " select  b.padid,b.nama from ".$dbname.".pad_lahan a 
								left join ".$dbname.".pad_5masyarakat b on a.pemilik=b.padid 
								where idlahan='".$bar['nama']."'";
					$restantum = $owlPDO->query($strtantum) or die(print " Gagal: " . PDOException::getMessage());
					$restantum->setFetchMode(PDO::FETCH_ASSOC);
					$bartantum = $restantum->fetch();
					$nama=$bartantum['nama'];
					$idnama=$bartantum['padid'];
				}else{
					$nama=$nmkode[$bar['nama']];
				}
				$tab.="<tr class=rowcontent ".$xx." id=trd_".$no.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . strtoupper($bar['jenis']) . "</td>";
				$tab.="<td align=left style=cursor:pointer onclick=getdaftarmasy('".$bar['nama']."')><font color=blue>" . $nama. "</font></td>";
				$tab.="<td align=center style=cursor:pointer onclick=getstatuslahan('".$bar['nama']."')><font color=blue>" . $bar['nosppt'] . "</font></td>";
				$tab.="<td hidden align=center>" . $bar['jlhsppt'] . "</td>";
				$tab.="<td align=left>" . $bar['lokasi'] . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['luaslahan'],3) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['luastantum'],3) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['harga'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['hargatantum'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
				$tab.="<td align=right>".hidezerodecimal($bar['rupiahtantum'],2)."</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rpsppt'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['totalrp'],2) . "</td>";
				
				# kas bank
				$strKb=" select * from ".$dbname.".keu_kasbankdt where  keterangan3='".$bar['nourut']."' and nodok='".$bar['notransaksi']."' and keterangan1='".$bar['nosppt']."'";
				$resKb=$owlPDO->query($strKb) or die(print " Gagal: ".PDOException::getMessage());
				$resKb->setFetchMode(PDO::FETCH_ASSOC);
				$barKb=$resKb->fetch();
				
				if($barKb['notransaksi']!=''){
					$tab.="<td title=\"Click untuk melihat detail pembayaran\" style=cursor:pointer onclick=detailPDF('".$barKb['notransaksi']."','".$barKb['noakun2a']."','".$barKb['tipetransaksi']."','".$barKb['kodeorg']."')><font color=blue>".$barKb['notransaksi']."</font></td>";
					$tab.="<td>".$barKb['tanggal']."</td>";
					$tab.="<td>".@hidezerodecimal($barKb['jumlah'])."</td>";
					
				}else{					
					$tab.="<td></td>";
					$tab.="<td></td>";
					$tab.="<td></td>";
				}
				#ajukan pemby
				if($bar['statuspersetujuan']=='1'){
					if($bar['ajukanbayar']=='0'){
						$tab.="<td align=center><img src='images/skyblue/submit.jpg' class='resicon' height='30' title='Ajukan Pembayaran !' onclick=\"ajukanbayar('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenis']."','".$bar['nama']."','".$bar['nosppt']."','".$no."')\">&nbsp;&nbsp;
						<img class='resicon' src='images/Delete.png' onclick=batalpemby('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenis']."','".$bar['nama']."','".$bar['nosppt']."','".$no."') title=\"Batal !!!\">
						</td>";
					}elseif($bar['ajukanbayar']=='1' and $barKb['notransaksi']==''){
						$tab.="<td align=center>".$bar['tglajukanbyr']."&nbsp;<img class='resicon' src='images/Delete.png' onclick=batalpemby('".$bar['notransaksi']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenis']."','".$bar['nama']."','".$bar['nosppt']."','".$no."') title=\"Batal !!!\">
						</td>";
					
					}elseif($bar['ajukanbayar']=='3'){
						$tab.="<td align=center style=background-color:red title='BATAL !!!'>".$bar['tglajukanbyr']."</td>";
					}else{
						$tab.="<td align=center>".$bar['tglajukanbyr']."</td>";
					}
				}else{
					$tab.="<td></td>";
				}
				
				@$tjlhsppt+=$bar['jlhsppt'];
				@$tluaslahan+=$bar['luaslahan'];
				@$tluastantum+=$bar['luastantum'];
				@$trupiah+=$bar['rupiah'];
				@$trupiahtantum+=$bar['rupiahtantum'];
				@$trpsppt+=$bar['rpsppt'];
				@$ttotalrp+=$bar['totalrp'];

			}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=5 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($tluaslahan,3)."</td>
				<td align=right>".@hidezerodecimal($tluastantum,3)."</td>
				<td align=right></td>
				<td align=right></td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				<td align=right>".@hidezerodecimal($trupiahtantum,2)."</td>
				<td align=right>".@hidezerodecimal($trpsppt,2)."</td>
				<td align=right>".@hidezerodecimal($ttotalrp,2)."</td>
				<td></td><td></td><td></td><td></td>
				
				</tr>";
		}
        $tab.="</table>";
		
		$tab.="<hr>
			<table class='sortable' cellspacing='1' border='0'>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center'>Kriteria</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody>";		
		$icon = "";
		$str = "select * from ".$dbname.".listfile_lgl_pemblahan where notransaksi = '".$notransaksi."' and status='1'";
		
		$res = fetchData($str);
		$no='';
		if (empty($res)) {
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		} else {
			foreach($res as $key => $val) {
				$no++;
				$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
				$tab.="<td style=''>".getcriterianame($val['kriteriaefil'])."</td>";
				$icon = seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
					<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>";
				$tab."  </td>
				</tr>";
			}
		}
		$tab.="</tbody></table>";
        echo $tab;
	break;
	case'ajukanbayar':
		$str = "update " . $dbname . ".lgl_pembebasanlahan set ajukanbayar='1',tglajukanbyr='".date('Y-m-d')."' where notransaksi ='".$notransaksi."' and kodeorg='" . $kodeorg . "' and jenis='" . $jenis . "' and periode='" . $periode . "' and nama='".$nama."' and nosppt='".$nosppt."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
	break;
	case'batalpemby':
		$str = "update " . $dbname . ".lgl_pembebasanlahan set ajukanbayar='3',tglajukanbyr='".date('Y-m-d')."' where notransaksi ='".$notransaksi."' and kodeorg='" . $kodeorg . "' and jenis='" . $jenis . "' and periode='" . $periode . "' and nama='".$nama."' and nosppt='".$nosppt."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
	break;
	case'getnotransaksi':
		#001/GRL/LGL/BOD/BJHO/IX/2017
		$tempPrd=explode('-',$periode);
		
		$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
		$str=" select notransaksi from ".$dbname.".lgl_pembebasanlahan where  kodeorg='".$kodeorg."' and periode = '".$periode."' order by notransaksi desc limit 1 "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		if(intval($bar['notransaksi'])==0){
			$noawal=1;
		}else{
			$noawal = intval($bar['notransaksi'])+1;
		}
		
		$notranbaru=addZero($noawal,3)."/GRL/LGL/".$nmpt[$kodeorg]."/".$kodeorg."/".romawi($tempPrd[1])."/".$tempPrd[0];
        
		echo $notranbaru;
    break;
	case'getdata':
		$optnama=$optfee="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sql = "select a.*,b.nama from " . $dbname . ".pad_lahan a 
		left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid
		where a.unit ='".$kodeorg."' and a.posting=1 and a.statuspermintaandana = 0  order by b.nama asc"; //exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optnama.="<option value=" . $bar['idlahan'] . ">" . $bar['nama'] . " - " . $bar['shm'] . " </option>";
        }
		
		$sql = "select * from " . $dbname . ".lgl_kodepemby 
		where jenis ='".$jenis."' order by nama asc"; //exit('error '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optfee.="<option value=" . $bar['kode'] . ">" . $bar['nama'] . "</option>";
        }
		
		if($jenis=='GRLTT' || $jenis=='SHM'){
			$opt=$optnama;
		}else{
			$opt=$optfee;
		}
		echo $opt;
	break;
	case'getlokasi':
		$optnama=$desa=$kec="";
		$sql = "select a.*, c.desa, d.kecamatan, e.kabupaten from " . $dbname . ".pad_lahan a  
				left join " . $dbname . ".pad_5masyarakat b on a.pemilik=b.padid 
				left join " . $dbname . ".desa c on c.iddes=.b.desa and c.id_kec=b.kecamatan 
				left join " . $dbname . ".kecamatan d on d.idkec=b.kecamatan 
				left join " . $dbname . ".kabupaten e on e.id=b.kabupaten where a.idlahan ='".$idlahan."' ";
		#exit('error : '.$sql);
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		if($bar['desa']!=''){
			$desa=", Des. " . $bar['desa'];
		}
		if($bar['kecamatan']!=''){
			$kec=", Kec. " . $bar['kecamatan'];
		}
		if($bar['kabupaten']!=''){
			$kab=", Kab. " . $bar['kabupaten'];
		}
        $optnama=$bar['alamat'];
        
		
		echo $optnama.'###'.$bar['shm'].'###'.$bar['luas'];
	break;
    case'detail':
        OPEN_BOX();
        $optjenis=$optjnsby= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$arrjenis=getEnum($dbname,'lgl_pembebasanlahan','jenis');
		foreach($arrjenis as $key=>$val){
			$optjenis.="<option value='".$key."'>".strtoupper($key)."</option>";
		}
		
	
	echo"<fieldset>
        <legend>" . $_SESSION['lang']['input'] . "</legend>
		<table>
			<tr>
				<td>Jenis</td><td>:</td>
				<td><select style=width:150px onchange=getdata() id=jenis>" . $optjenis . "</select></td>
				
				<td>Nama</td><td>:</td>
				<td><select style=width:150px id=nama onchange=getlokasi()></select>
				<img id='nama' onclick=z.elSearch('nama',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td>
			</tr>
			<tr>
				<td>No SPPT/SHM</td><td>:</td>
				<td ><input id=nosppt style=\"width:145px;\" class=myinputtext onkeypress='return tanpa_kutip(event)';></td>
					<input id=jlhsppt type=hidden style=\"width:145px;\" onkeypress='return angka_doang(event)';>
				<td>Lokasi</td><td>:</td>
				<td><input id=lokasi class=myinputtext nkeypress=\"return_tanpa_kutip(event);\" onkeydown=\"upperCaseF(this)\" style=\"width:145px;\"></td>
			</tr>
			<tr>
				<td>Luas Lahan (Ha)</td><td>:</td>
				<td><input id=luaslahan style=\"width:145px;\" onkeyup=\"samatantum();hitungrp();\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
				
				<td>Luas TanTum (Ha)</td><td>:</td>
				<td><input id=luastantum style=\"width:145px;\" onkeyup=\"hitungrp();\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			</tr>
			<tr>
				<td>Harga Lahan/Ha (Rp)</td><td>:</td>
				<td><input id=harga style=\"width:145px;\" onkeyup=\"hitungrp();\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
				<td>Harga TanTum/Ha (Rp)</td><td>:</td>
				<td><input id=hargatantum style=\"width:145px;\" onkeyup=\"hitungrp();\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			</tr>
			<tr>
				<td>Pembayaran Lahan (Rp)</td><td>:</td>
				<td><input id=rupiah style=display:none>
				<input id=rupiahx style=\"width:145px;\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
				<td>Pembayaran TanTum (Rp)</td><td>:</td>
				<td><input id=rupiahtantum style=\"width:145px;\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			</tr>
			<tr>
				
				<td>Biaya Pembuatan Surat (Rp)</td><td>:</td>
				<td><input id=rpsppt style=\"width:145px;\" class=myinputtextnumber onkeypress='return angka_doang(event)'; onkeyup=\"hitungrp();\"></td>
				<td>Total Di Bayar (Rp)</td><td>:</td>
				<td><input id=totalrp style=\"width:145px;\" class=myinputtextnumber onkeypress='return angka_doang(event)';></td>
			</tr>
			<tr>
				<td></td><td></td><td colspan=5>
				<button class=mybutton title='" . $_SESSION['lang']['save'] . "' onclick=\"savedetail()\">Save</button>
                <button class=mybutton title='" . $_SESSION['lang']['clear'] . "' onclick=\"cleardetail()\">Batal</button>
				<button class=mybutton style=display:none title='Refresh' onclick=\"loaddatadetail('".$notransaksi."')\">Refresh</button>
				<button class=mybutton style=display:none onclick='showupload(event)'>Upload Files</button>
				<button id=done class=mybutton onclick=displayList()>" . $_SESSION['lang']['selesai'] . "</button>
				</td>
			</tr>
		</table>
        </fieldset><hr>";
	echo"
        <fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";
		
	echo"<fieldset style=display:none><legend>" . $_SESSION['lang']['list'] . " File Upload</legend>
			<table class='sortable' cellspacing='1' border='0'>
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
        $sql = "select count(*) as jmlhrow from " . $dbname . ".lgl_pembebasanlahan where "
		. " notransaksi='" . $notransaksi . "' and kodeorg ='".$kodeorg."' and periode='".$periode."' and jenis='" . $jenis . "' and nama='".$nama."' and nosppt='".$nosppt."'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Transaksi sudah ada.");
        }
		
		$sql = "select max(nourut) as nourut from " . $dbname . ".lgl_pembebasanlahan where notransaksi='" . $notransaksi . "'";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$nourut=$bar['nourut']+1;
		
		
        $str = "insert into " . $dbname . ".lgl_pembebasanlahan (`notransaksi`,`nourut`,`kodeorg`,`periode`,`jenis`,`nama`,`nosppt`,`jlhsppt`,`lokasi`,`luaslahan`,`luastantum`,`harga`,`hargatantum`,`rupiah`,`rupiahtantum`,`rpsppt`,`totalrp`,`createby`,`createtime`,`updateby`)
        values ('".$notransaksi."','".$nourut."','".$kodeorg."','".$periode."','".$jenis."','".$nama."','".$nosppt."','".$jlhsppt."','".$lokasi."','".$luaslahan."','".$luastantum."','".$harga."','".$hargatantum."','".$rupiah."','".$rupiahtantum."','".$rpsppt."','".$totalrp."','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "')";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

	break;
    case'delete':
        $str = "delete from " . $dbname . ".lgl_pembebasanlahan where notransaksi='".$notransaksi."' and kodeorg='" . $kodeorg . "' and periode='" . $periode . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;

    case'deletedetail':
        $str = "delete from " . $dbname . ".lgl_pembebasanlahan where notransaksi ='".$notransaksi."' and kodeorg='" . $kodeorg . "' and jenis='" . $jenis . "' and periode='" . $periode . "' and nama='".$nama."' and nosppt='".$nosppt."'";
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
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='GRL' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
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
		try {
		$owlPDO->beginTransaction();
		
		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		
		//cari dulu apakah sudah pernah di ajukan sebelumnya
		$tglhi = date("Ymd");
		$str="select * from ".$dbname.".approval where jenispersetujuan='GRL' and notransaksi='".$notransaksi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($bar['notransaksi']!=''){
				# jika ada pindahkan ke table ini
				$str = "insert into " . $dbname . ".approval_return (`notransaksi`, `jenispersetujuan`, `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				values ('".$bar['notransaksi']."','".$bar['jenispersetujuan']."','".$bar['level']."','".$bar['karyawanid']."','".$bar['status']."','".$bar['komentar']."','".$tglhi."','".$bar['tanggal']."')";
				$owlPDO->exec($str);
			}
		}
		
		#kemudian setelah di pindah, hapus persetujuan lama
		$str="delete from ".$dbname.".approval where jenispersetujuan='GRL' and notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);
		
		
		# update flag menjadi 1
        $str = "update " . $dbname . ".lgl_pembebasanlahan set posting='1', statuspersetujuan='0', postingdate='" . date('Y-m-d') . "',"."postingby='" . $_SESSION['standard']['userid'] . "' where notransaksi = '" . $notransaksi . "'";
		$owlPDO->exec($str);

		# insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
				`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
				 values ('','".$notransaksi."','GRL','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
        
	break;
    case'loaddatadetail':
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>
				<thead><tr class=rowheader>
				<td align=center rowspan=2 width=20px>No</td>
				<td align=center rowspan=2 width=50px>" . $_SESSION['lang']['jenis'] . "</td>
            <td align=center rowspan=2 width=150px>" . $_SESSION['lang']['nama'] . "</td>
            <td align=center colspan=1>SPPT/SHM</td>
            <td align=center rowspan=2>" . $_SESSION['lang']['lokasi'] . "</td>
            <td align=center colspan=2>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center colspan=2 width=50px>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center colspan=4 width=50px>" . $_SESSION['lang']['rupiah'] . "</td>
			<td align=center rowspan=2 width=30px>" . $_SESSION['lang']['action'] . "</td>
        </tr>
		<tr>
            <td align=center>No</td>
            <td hidden align=center width=20px>Jlh</td>
            <td align=center width=20px>".$_SESSION['lang']['lahan']."</td>
            <td align=center width=20px>TanTum</td>
            <td align=center width=50px>Lahan/Ha<br>Rp</td>
            <td align=center width=50px>TanTum/Ha<br>Rp</td>
            <td align=center width=50px>Pembayaran<br>Lahan Rp</td>
			<td align=center width=50px>Pembayaran<br>TanTum Rp</td>
            <td align=center width=50px>Biaya<br>Pembuatan Surat Rp</td>
            <td align=center width=50px>Total di Bayar</td>
			</tr>
			</thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".lgl_pembebasanlahan where notransaksi='" . $notransaksi . "' order by jenis asc, nama asc";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if($row==0){
			$tab.="<tr class=rowcontent><td colspan=14 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkode=$nmtantum=$nama=$nmalamat='';
			while ($bar = $res->fetch()) {
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==0){
					$xx.=" style=background-color:#F5EEF8 ";
				}
				$nmkode=makeOption($dbname,'lgl_kodepemby','kode,nama',"kode='".$bar['nama']."'");
				if($bar['jenis']=='GRLTT'||$bar['jenis']=='SHM'){
					$strtantum = " select  b.padid,b.nama from ".$dbname.".pad_lahan a 
								left join ".$dbname.".pad_5masyarakat b on a.pemilik=b.padid 
								where idlahan='".$bar['nama']."'";
					$restantum = $owlPDO->query($strtantum) or die(print " Gagal: " . PDOException::getMessage());
					$restantum->setFetchMode(PDO::FETCH_ASSOC);
					$bartantum = $restantum->fetch();
					$nama=$bartantum['nama'];
					$idnama=$bartantum['padid'];
				}else{
					$nama=$nmkode[$bar['nama']];
				}
				$tab.="<tr class=rowcontent ".$xx.">";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . strtoupper($bar['jenis']) . "</td>";
				$tab.="<td align=left style=cursor:pointer onclick=getdaftarmasy('".$bar['nama']."')><font color=blue>" . $nama. "</font></td>";
				$tab.="<td align=center style=cursor:pointer onclick=getstatuslahan('".$bar['nama']."')><font color=blue>" . $bar['nosppt'] . "</font></td>";
				$tab.="<td hidden align=center>" . $bar['jlhsppt'] . "</td>";
				$tab.="<td align=left>" . $bar['lokasi'] . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['luaslahan'],4) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['luastantum'],4) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['harga'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['hargatantum'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rupiah'],2) . "</td>";
				$tab.="<td align=right>".hidezerodecimal($bar['rupiahtantum'],2)."</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['rpsppt'],2) . "</td>";
				$tab.="<td align=right>" . hidezerodecimal($bar['totalrp'],2) . "</td>";
				$tab.="<td align=center>";
				if($bar['jenis']=='tantum'){
					$tab.="
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['jenis'] . "','" . $bar['nama'] . "','" . $bar['nosppt'] . "');\" >
					</td>";
				}
				else{
					$tab.="
					<img src=images/application/application_delete.png class=resicon  title='Delete' 
					onclick=\"deletedetail('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['jenis'] . "','" . $bar['nama'] . "','" . $bar['nosppt'] . "');\" >
					</td>";
				}
				
				
				@$tjlhsppt+=$bar['jlhsppt'];
				@$tluaslahan+=$bar['luaslahan'];
				@$tluastantum+=$bar['luastantum'];
				@$trupiah+=$bar['rupiah'];
				@$trupiahtantum+=$bar['rupiahtantum'];
				@$trpsppt+=$bar['rpsppt'];
				@$ttotalrp+=$bar['totalrp'];

			}
        $tab.="</tr>";
        $tab.="<tr class=rowcontent>
				<td colspan=5 align=center>TOTAL</td>
				<td align=right>".@hidezerodecimal($tluaslahan,4)."</td>
				<td align=right>".@hidezerodecimal($tluastantum,4)."</td>
				<td align=right></td>
				<td align=right></td>
				<td align=right>".@hidezerodecimal($trupiah,2)."</td>
				<td align=right>".@hidezerodecimal($trupiahtantum,2)."</td>
				<td align=right>".@hidezerodecimal($trpsppt,2)."</td>
				<td align=right>".@hidezerodecimal($ttotalrp,2)."</td>
				<td align=right></td>
				</tr>";
		}
        $tab.="</table>";

        echo $tab;
	break;
	case 'showupload':
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
	
        $tab = "";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
        <td>".$_SESSION['lang']['notransaksi']."</td>
        <td>:</td>
			<td>
				<label id='notrupload' style='display:none'>".$notransaksi."</label>
				<label style='font-weight:bold'>".$notransaksi."</label>
			</td>
        </tr>
        ";
        $tab.="<tr><td colspan=4><hr></td></tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteria'>". $optkriteria."</select>
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
            <td align='center' width=50px>No.</td>
            <td align='center' width=50px>Kriteria</td>
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
    if ($data['fileupload'] != '') {
        if ($_FILES['file']['error'] == 0) {
            $filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
            $filename = $tgl."".$filetype;
            $filename = $_FILES['file']['name'];
            $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
            if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
				if($_FILES['file']['size'] <= 1000000){
					$str = "insert into ".$dbname.".listfile_lgl_pemblahan values ('','".$notransaksi."','".$filename."','".$filetype."','".$kriteria."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try {
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename, $file_tmpname);
					} catch (PDOException $e) {
						echo " Gagal,".addslashes($e->getMessage());
					}
				}else{
					exit("warning : Ukuran file upload maksimal 10 MB");
				}
            } else {
				exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
            }
        }
    }
    break;
    case 'loadfiles':
    $no = 0;
	
	$tab = $icon = "";
	$str = "select * from ".$dbname.".listfile_lgl_pemblahan where notransaksi = '".$notransaksi."' and status='1'";
    
    $res = fetchData($str);
    if (empty($res)) {
        $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    } else {
        foreach($res as $key => $val) {
			$optkriteria="";
			$arrmodul = getmodulefil($emodul);
			foreach($arrmodul as $keyx=>$valx){
				if($keyx==$val['kriteriaefil']){
					$optkriteria.="<option value='".$keyx."' selected>".$valx['kriteria']."</option>";
				}else{
					$optkriteria.="<option value='".$keyx."'>".$valx['kriteria']."</option>";
				}
			}
			
            $no++;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
			$tab.="<td style='text-align:center'>
                <label style='display:none'>".getcriterianame($val['kriteriaefil'])."</label>
				<select id='kriteriax_".$val['id']."' onchange=\"changekriteria('".$val['id']."')\">". $optkriteria."</select>
                </td>";
            $icon = seticonfile($val['formaticon']);
			if($val['formaticon']=='.pdf'){
				$tab.="<td style='text-align:center'>
					<img src='".$icon."' class=resicon  title='PDF' onclick=\"getdatapdf('".$val['namafile']."')\">
                </td>";
			}else{
				$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";
			}
            $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
			$tab.="
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
            
            $tab."  </td>
            </tr>";
        }
    }
    echo $tab;
    break;
	
	case'getdatapdf':
		$efil=$path."".$namafile;
		$tab='<embed src="'.$efil.'" type="application/pdf" frameBorder="0" scrolling="auto" height="100%" width="100%"></embed>';
		
		echo $tab;
	break;
	
	case'changekriteria':
		$str="update ".$dbname.".listfile_lgl_pemblahan set kriteriaefil='".$kriteriax."' where id='".$id."'";
		$owlPDO->exec($str);
	break;
    
    case 'viewfile':
    $tab = "";
    $tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
    echo $tab;
    break;
    case 'deletefile':
    $str = "delete from ".$dbname.".listfile_lgl_pemblahan where notransaksi='".$notransaksi."' and namafile='".$namafile."'"; //exit('error'.$str);
    try {
        $owlPDO->exec($str);
        $pathx = $path.$namafile;
        unlink($pathx);
    } catch (PDOException $e) {
        echo " Gagal,".addslashes($e->getMessage());
    }
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
        if ($periodesch != '') {
            $where.=" and periode='" . $periodesch . "' ";
        }
		if ($notrsch != '') {
            $where.=" and notransaksi like '%" . $notrsch . "%' ";
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

        $sql = "select * from " . $dbname . ".lgl_pembebasanlahan where 1=1 " . $where . " group by notransaksi, kodeorg,periode";
        $res = fetchData($sql);
        $jlhbrs = count($res);

        $no = 0;

        $str = "SELECT sum(rupiah+rpsppt) as biaya,notransaksi, kodeorg, periode, updateby, posting, sum(totalrp) as totalrp, statuspersetujuan, sum(luaslahan) as luaslahan FROM " . $dbname . ".lgl_pembebasanlahan
		where 1=1 " . $where . " group by notransaksi, kodeorg,periode  order by periode desc, kodeorg asc limit " . $offset . "," . $limit . "";
        //exit('error '.$str);
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
            $tab.="<td align=right>" . @number_format($bar['luaslahan'],3) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['biaya'],2) . "</td>";
            $tab.="<td align=right>" . @number_format($bar['totalrp'],2) . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
            $tab.="<td align=left>" . $arrHsl[$bar['statuspersetujuan']] . "</td>";

            if ($bar['posting'] == 0 or ($bar['posting']=='1' and $bar['statuspersetujuan']=='3')) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['notransaksi'] . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('" . $bar['kodeorg'] . "','" . $bar['periode'] . "','" . $bar['notransaksi'] . "');\" ></td>";					
			    $isi.="<td align=center><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Ajukan ???' 
                    onclick=\"form_ajukan('" . $bar['notransaksi'] . "','" . $bar['kodeorg'] . "','" . $no . "');\" ></td>";
            }else{
				$isi.="<td></td><td></td><td></td>";
			}
			$isi.="<td align=center><img src=images/upload-2-xxl.png class=resicon  title='UploadFile' 
				onclick=\"showupload ('event','" . $bar['notransaksi'] . "');\" ></td>";               
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
                     <tr><td colspan=14 align=center>";

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
	
}
?>	
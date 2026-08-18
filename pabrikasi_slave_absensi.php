<?
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
$param = $_POST;
$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');

$optPt = makeOption($dbname, 'pmn_kontrakjual', 'nokontrak,kodept');

switch ($param['proses']) {
	
	case'posting':

	
		// echo $param['notran']; 	
		
		$str="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='PBR' and jurnalid='PBR1' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$kodejurnal=$bar['jurnalid'];
			$db=$bar['noakundebet'];
			$kr=$bar['noakunkredit'];
			
		#buat parameternya
		$str="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$nokounter=$bar['nokounter']+1;
			$nokounter=addZero($nokounter,3);	
		
	//	exit("Error:$nokounter");
		
		
		
		$str="select a.notransaksi,a.kodepabrikasi,a.jhk,a.umr,a.premi,b.tanggal,b.posting
			from ".$dbname.".pabrikasi_absensidt a left join ".$dbname.".pabrikasi_absensiht b 
			on a.notransaksi=b.notransaksi where a.notransaksi='".$param['notran']."'  ";
			//exit("Error:$str");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$rp+=$bar['umr']+$bar['premi'];
			$tgl=$bar['tanggal'];
			$kdpab=$bar['kodepabrikasi'];
		}
		
		$kodeorg=$_SESSION['empl']['lokasitugas'];
		
		$notgl=str_replace('-','',$tgl);
		$nojurnal=$notgl.'/'.$kodeorg.'/'.$kodejurnal.'/'.$nokounter;
		
		
		#jurnal ht	
		$str="INSERT INTO `keu_jurnalht` (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`, `posting`, `totaldebet`, `totalkredit`,
					`amountkoreksi`, `noreferensi`, `autojurnal`, `matauang`, `kurs`, `revisi`)
		VALUES ('".$nojurnal."', '".$kodejurnal."', '".$tgl."','".date('Y-m-d')."', '1','".$rp."', '".($rp*-1)."',
				'0','".$param['notran']."', '1','IDR', '1', '0')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}	
		
		
		#insert dt db
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '1', '".$db."','ABSENSI PABRIKASI ".$param['notran']."', '".$rp."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','', '','".$param['notran']."', NULL, NULL, NULL,'".$kdpab."', '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#kr
		$str="INSERT INTO `keu_jurnaldt` (`nojurnal`, `tanggal`, `nourut`, `noakun`, `keterangan`, `jumlah`, `matauang`, `kurs`, `kodeorg`,
				`kodekegiatan`, `kodeasset`, `kodebarang`, `nik`, `kodecustomer`, `kodesupplier`, `noreferensi`, `noaruskas`,
				`kodevhc`, `nodok`, `kodeblok`, `revisi`, `kodesegment`)
				VALUES ('".$nojurnal."', '".$tgl."', '2', '".$kr."','ABSENSI PABRIKASI ".$param['notran']."', '".($rp*-1)."','IDR', '1','".$kodeorg."',
				NULL, '', NULL, '','', '','".$param['notran']."', NULL, NULL, NULL,'".$kdpab."', '0', '0000000001')";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		#update nomor kounter
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounter."' 
			where kodekelompok='".$kodejurnal."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'  ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		
		#update flag posting
		$str="update ".$dbname.".pabrikasi_absensiht set posting='1' 
			where notransaksi='".$param['notran']."'   ";
		try{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) {
		   print " Gagal  !: " . $e->getMessage() . "\n"; 
		   die(); 
		}
		
		//exit("Error:$nojurnal");
	
	break;
	
	
    case'loadData':
        if (!empty($param['notrancari'])) {
            $where .= " and notransaksi like '%" . $param['notrancari'] . "%'";
        }
        if (!empty($param['tanggalCr'])) {
            $tgrl = explode("-", $param['tanggalCr']);
            $ert = $tgrl[2] . "-" . $tgrl[1] . "-" . $tgrl[0];
            $where .= " and tanggal = '" . $ert . "'";
        }
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
		$maxdisplay=($page*$limit);
		
        $offset = $page * $limit;
        $sql = "select count(*) jmlhrow from " . $dbname . ".pabrikasi_absensiht where left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."' ".$where." order by tanggal desc";
        $query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $query->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $str = "select * from " . $dbname . ".pabrikasi_absensiht  where left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."'  ".$where."  order by tanggal desc limit " . $offset . "," . $limit . " ";
        $tab = '';
        $nor = 0;
        $nor=$maxdisplay;
		$qstr = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qstr->setFetchMode(PDO::FETCH_ASSOC);
        while ($rstr = $qstr->fetch()) {
            $nor+=1;
            $whrd="karyawanid='".$rstr['updateby']."'";
            $nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrd);
            $tab.="<tr class=rowcontent align=center>
				<td>".$nor."</td>
				<td>" . $rstr['notransaksi'] . "</td>
				<td align=center>" . tanggalnormal($rstr['tanggal']) . "</td>
				<td>" . $rstr['kodeorg'] . "</td>
				<td>" . $nmKar[$rstr['updateby']] . "</td>";
                $scek="select * from ".$dbname.".sdm_5periodegaji where periode='".$rstr['periode']."' and kodeorg='".substr($rstr['kodeorg'],0,4)."' and sudahproses=0";
                $rcek=fetchData($scek);
                if(count($rcek)>0 and $rstr['posting']==0){
				// if(count($rcek)==0){	
                    $tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit " . $rstr['notransaksi'] . "' onclick=\"fillField('" . $rstr['notransaksi'] . "');\" ></td>
                           <td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus " . $rstr['notransaksi'] . "' onclick=\"delData('" . $rstr['notransaksi'] . "');\" ></td>
                           <td align=center><img src=images/pdf.jpg class=resicon  title='Detail " . $rstr['notransaksi'] . "' onclick=\"masterPDF('pmn_suratperintahpengiriman','" . $rstr['notransaksi'] . "','','pabrikasi_slave_absensi_pdf',event);\" ></td>
						   <td align=center><img src=images/skyblue/posting.png class=resicon  title='posting' onclick=\"posting('".$rstr['notransaksi']."');\"></td>	";
					
			   }else{
                    $tab.="<td colspan=3>&nbsp;</td>
                           <td align=center><img src=images/pdf.jpg class=resicon  title='Detail " . $rstr['notransaksi'] . "' onclick=\"masterPDF('pmn_suratperintahpengiriman','" . $rstr['notransaksi'] . "','','pabrikasi_slave_absensi_pdf',event);\" ></td>";
                }
                $tab.="</tr>";
                //<!--<td><img src=\"images/skyblue/posting.png\" class=\"zImgBtn\" onclick=\"postingData('".$rstr['notransaksi']."')\" title=\"Posting\"></td>-->
				
        }
        if($nor==0){
            $tab.="<tr class=rowcontent><td colspan=9>".$_SESSION['lang']['dataempty']."</td></tr>";
            $footd = "<tr><td colspan=8>&nbsp;</td></tr>";

        }else{
            $skeupenagih = "select count(*) as rowd from " . $dbname . ".pabrikasi_absensiht where left(kodeorg,4)='".$_SESSION['empl']['lokasitugas']."'";
            $qkeupenagih = $owlPDO->query($skeupenagih) or die(print " Gagal: " . PDOException::getMessage());
            $rkeupenagih = owlBaris($qkeupenagih);
            $totrows = ceil($rkeupenagih / $limit);
            
            if ($totrows == 0) {
                $totrows = 1;
            }
            $isiRow = '';
            for ($er = 1; $er <= $totrows; $er++) {
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd = " 
                <tr><td colspan=9 align=center>
                
                <button class=mybutton onclick=loadData(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
                <button class=mybutton onclick=loadData(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        }
        
        echo $tab . "####" . $footd;
        break;

    case'cekData':
        if($param['tgl']==''){
            exit('warning: '.$_SESSION['lang']['notiftanggal']);
        }
        #cek apakah data sudah ada atau belum
        $dtcek="select * from ".$dbname.".pabrikasi_absensiht where tanggal='".tanggalsystem($param['tgl'])."'
                and kodeorg='".$param['kdorg']."'";
        $rcek=fetchData($dtcek);
        if(count($rcek)==1){
            exit('warning: '.$_SESSION['lang']['notifdatasudahada']);
        }
        #cek tanggal
        $sCek="select * from ".$dbname.".sdm_5periodegaji where date(".tanggalsystem($param['tgl']).") between tanggalmulai and tanggalsampai and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        $qCek=fetchData($sCek);
        if(count($qCek)==0){
            exit('warning: Tanggal di luar periode gaji');
        }

        $whrdt="lokasitugas='".$param['kdorg']."' and (subbagian='' or subbagian is null)";
        if(strlen($param['kdorg'])!=4){
            $whrdt="subbagian='".$param['kdorg']."'";
        }
        $optKar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sData="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where ".$whrdt." and tipekaryawan!=0 order by namakaryawan asc";
        $rData=fetchdata($sData);
        foreach($rData as $isiData){
            $optKar.="<option value='".$isiData['karyawanid']."'>".$isiData['namakaryawan']."-".$isiData['nik']."</option>";
        }
        echo $optKar;
    break;
    case'getGaji':
        if($param['jhk']>1){
            exit('warning: HK tidak boleh lebih dari 1');
        }
		
        #cek sudah terdaftar pada absensi
        $sDtCk="select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyId']."' and tanggal='".tanggalsystem($param['tgl'])."'";
        $rDtCk=fetchData($sDtCk);
        if(count($rDtCk)==1){
            exit('warning: Data sudah terdaftar pada menu SDM > Administrasi Personali > Transaksi > Absensi');
        }
        #cek sudah terdaftar pada bkm
        $sDtCk="select * from ".$dbname.".kebun_kehadiran_vw where karyawanid='".$param['karyId']."' and tanggal='".tanggalsystem($param['tgl'])."'";
        $rDtCk=fetchData($sDtCk);
        if(count($rDtCk)==1){
            exit('warning: Data sudah terdaftar pada menu Kebun > Transaksi > BKM');
        }
        #cek jumlah HK
        $sDtCk="select sum(jhk) as hk from ".$dbname.".pabrikasi_absensidt where karyawanid='".$param['karyId']."' and left(notransaksi,8)='".tanggalsystem($param['tgl'])."'";
        $rDtCk=fetchData($sDtCk);

        if($rDtCk[0]['hk']>=1){
            exit('warning: Sudah Lebih dari 1 HK');
        }
        $sCek="select sum(jumlah) as gapok from ".$dbname.".sdm_5gajipokok where tahun='".substr($param['periode'],0,4)."'
               and karyawanid='".$param['karyId']."' and idkomponen in (1,31)";
        $rCek=fetchData($sCek);
        if($rCek[0]['gapok']==0){
            exit('warning: Gaji Pokok Belum Terdaftar');
        }
        @$dtgaji=$param['jhk']*($rCek[0]['gapok']/25);
        echo $dtgaji;
    break;
    case'insert':
        if ($param['tgl'] == '') {
            exit("error: Tanggal tidak boleh kosong");
        }
        if($param['jhk'] == '') {
            exit("error: Jumlah HK tidak boleh kosong");
        }
      
		
        if($param['premi']==''){
            $param['premi']=0;
        }
        $notransaksi = generateNoDO();
        if($param['prnh']==0){
            $ql = "select `notransaksi` from " . $dbname . ".`pabrikasi_absensiht` where notransaksi='".$notransaksi."'";
            $qr = fetchData($ql);
            if(count($qr)==1){
                exit('warning: Transaksi Untuk Tanggal '.$_POST['tgl'].' dan Unit '.$_POST['kdorg'].' sudah ada');
            }
            $sInserHt="insert into ".$dbname.".pabrikasi_absensiht (`notransaksi`,`tanggal`,`kodeorg`,`periode`,`updateby`)
                       values ('".$notransaksi."','".tanggalsystem($param['tgl'])."','".$param['kdorg']."','".$param['periodedt']."','".$_SESSION['standard']['userid']."')";
            try{
                $owlPDO->exec($sInserHt);
                $sInserDt="insert into ".$dbname.".pabrikasi_absensidt (`notransaksi`,`karyawanid`,`kodepabrikasi`,`kodeabsensi`,`jhk`,`umr`,`premi`) values 
                           ('".$notransaksi."','".$param['karyId']."','".$param['pabrikasiId']."','H','".$param['jhk']."','".$param['umr']."','".$param['premi']."')";
                try {
                    $owlPDO->exec($sInserDt);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        }else{
                $sCek="select * from ".$dbname.".pabrikasi_absensidt where notransaksi='".$notransaksi."' and kodepabrikasi='".$param['pabrikasiId']."'
                       and karyawanid='".$param['karyId']."'";
                $rCek=fetchData($sCek);
                if(count($rCek)==1){
                    exit('warning: Data Sudah Ada');
                }
                $sInserDt="insert into ".$dbname.".pabrikasi_absensidt (`notransaksi`,`karyawanid`,`kodepabrikasi`,`kodeabsensi`,`jhk`,`umr`,`premi`) values 
                           ('".$notransaksi."','".$param['karyId']."','".$param['pabrikasiId']."','H','".$param['jhk']."','".$param['umr']."','".$param['premi']."')";
                try {
                    $owlPDO->exec($sInserDt);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
        }
        echo $notransaksi;
        break;
    case'editdet':
        #cek sudah terdaftar pada absensi
        $sDtCk="select * from ".$dbname.".sdm_absensidt where karyawanid='".$param['karyId']."' and tanggal='".tanggalsystem($param['tgl'])."'";
        $rDtCk=fetchData($sDtCk);
        if(count($rDtCk)==1){
            exit('warning: Data sudah terdaftar pada menu SDM > Administrasi Personali > Transaksi > Absensi');
        }
        #cek sudah terdaftar pada bkm
        $sDtCk="select * from ".$dbname.".kebun_kehadiran_vw where karyawanid='".$param['karyId']."' and tanggal='".tanggalsystem($param['tgl'])."'";
        $rDtCk=fetchData($sDtCk);
        if(count($rDtCk)==1){
            exit('warning: Data sudah terdaftar pada menu Kebun>Transaksi>BKM');
        }
         
        $sUpdateDet="update ".$dbname.".pabrikasi_absensidt set jhk='".$param['jhk']."', umr='".$param['umr']."', premi='".$param['premi']."' 
                     where karyawanid='".$param['karyId']."' and notransaksi='".$param['notransaksi']."' and kodepabrikasi='".$param['pabrikasiId']."'";
        try {
            $owlPDO->exec($sUpdateDet);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    case'loadDetail':
        $sdata="select * from ".$dbname.".pabrikasi_absensidt where  notransaksi='".$param['notransaksi']."'";
        $rdata=fetchdata($sdata);
        foreach($rdata as $isidata){
            $no+=1;
            $whrd="karyawanid='".$isidata['karyawanid']."'";
            $niKar=makeOption($dbname,'datakaryawan','karyawanid,nik',$whrd);
            $nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrd);
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$niKar[$isidata['karyawanid']]."</td>";
            $tab.="<td>".$nmKar[$isidata['karyawanid']]."</td>";
            $tab.="<td>".$isidata['kodepabrikasi']."</td>";
            $tab.="<td align=center>".$isidata['kodeabsensi']."</td>";
            $tab.="<td align=center>".number_format($isidata['jhk'],2)."</td>";
            $tab.="<td align=right>".number_format($isidata['umr'])."</td>";
            $tab.="<td align=right>".number_format($isidata['premi'])."</td>";
            $tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillFieldDet('".$isidata['karyawanid']."','".$isidata['kodepabrikasi']."','".$isidata['jhk']."','".$isidata['umr']."','".$isidata['premi']."');\" ></td>
                   <td align=center><img src=images/application/application_delete.png class=resicon  title='Hapus' onclick=\"delDataDet('" . $isidata['notransaksi'] . "','" . $isidata['karyawanid'] . "','" . $isidata['kodepabrikasi'] . "');\" ></td>";
            $tab.="</tr>";
        }
        echo $tab;
    break;
    case'getData':
        $sData="select * from ".$dbname.".pabrikasi_absensiht where notransaksi='".$param['notransaksi']."'";
        $rData=fetchData($sData);
        $whrdt="lokasitugas='".$rData[0]['kodeorg']."' and (subbagian='' or subbagian is null)";
        if(strlen($rData['kodeorg'])!=4){
            $whrdt="subbagian='".$rData[0]['kodeorg']."'";
        }
        $optKar="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sData="select karyawanid,namakaryawan,nik from ".$dbname.".datakaryawan where ".$whrdt." and tipekaryawan!=0 order by namakaryawan asc";
        $rData2=fetchdata($sData);
        foreach($rData2 as $isiData){
            $optKar.="<option value='".$isiData['karyawanid']."'>".$isiData['namakaryawan']."-".$isiData['nik']."</option>";
        }
        echo $rData[0]['notransaksi']."#####".tanggalnormal($rData[0]['tanggal'])."#####".$rData[0]['kodeorg']."#####".$rData[0]['periode']."#####".$optKar;
    break;
    case'delDetail':
        $sdel = "delete from " . $dbname . ".pabrikasi_absensidt where notransaksi='".$param['notransaksi']."' and kodepabrikasi='".$param['pabrikasiId']."' and karyawanid='".$param['karyId']."'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    case'delData':
        $sdel = "delete from " . $dbname . ".pabrikasi_absensiht where notransaksi='".$param['notransaksi']."'";
        try {
            $owlPDO->exec($sdel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
}

function generateNoDO(){
    global $dbname;
    global $_POST;
    global $owlPDO;
    $notrans=tanggalsystem($_POST['tgl'])."/".$_POST['kdorg'];
    return $notrans;
}

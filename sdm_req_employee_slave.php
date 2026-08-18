<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
    require_once('lib/fpdf.php');
    require_once('dompdf/autoload.inc.php');
    use Dompdf\Dompdf;
?>	

<?php
    $method                 = checkPostGet('method', '');
    $pagejs                 = checkPostGet('page','');
    $notransaksisch         = checkPostGet('notransaksisch','');
    $notransaksi   	        = checkPostGet('notransaksi', '');
    $namajabatan 	        = checkPostGet('namajabatan','');
    $jumlahpekerjasekarang  = checkPostGet('jumlahpekerjasekarang','');
    $jumlahpekerjadibutuhkan= checkPostGet('jumlahpekerjadibutuhkan','');
    $departemen             = checkPostGet('departemen','');
    $alasan                 = checkPostGet('alasan','');
    $statuskaryawan         = checkPostGet('statuskaryawan','');
    $mulaibekerja           = tanggalsystemn(checkPostGet('mulaibekerja',''));
    $golongan               = checkPostGet('golongan','');
    $pendidikanminimal      = checkPostGet('pendidikanminimal','');
    $pengalamanminimal      = checkPostGet('pengalamanminimal','');
    $lokasikerja            = checkPostGet('lokasikerja','');
    $uraiankerja            = checkPostGet('uraiankerja','');
    $kualifikasi            = checkPostGet('kualifikasi','');
    $jeniskelamin           = checkPostGet('jeniskelamin','');
    $statuspernikahan       = checkPostGet('statuspernikahan','');
    $bidangpengalaman       = checkPostGet('bidangpengalaman','');
    $alasanganti            = checkPostGet('alasanganti','');
    $note                   = checkPostGet('note','');
    $jenistes               = checkPostGet('jenis_tes','');
    $jenisinterview         = checkPostGet('jenis_interview','');
    $sertifikasi            = checkPostGet('sertifikasi','');
    $tipe                   = checkPostGet('tipe','');
    $jlh                    = checkPostGet('jlh','');
    $usiamin                = checkPostGet('usiamin','');
    $usiamax                = checkPostGet('usiamax','');
    $divisi                 = checkPostGet('divisi','');
    $jenispersetujuan       = 'ERF';
?>

<?php
switch ($method) {
    case 'getnotransaksi':
        $str    = "SELECT notransaksi,lokasikerja FROM " . $dbname . ".sdm_req_employee where lokasikerja='".$_SESSION['empl']['lokasitugas']."' order by createtime desc limit 1";
        $qry	=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$qry->fetch();
        $tglbln =substr($bar['notransaksi'],0,6);
        $unit =$bar['lokasikerja'];
        if($bar['notransaksi']=='' or $tglbln != date('Ym')){
            $nourut=1;
        }else{
            $explnotran=explode('/',$bar['notransaksi']);
            $nourut=$explnotran[3]+1;
        }
        $notrans= date('Ym')."/ERF/".$_SESSION['empl']['lokasitugas']."/".addZero($nourut,4);
		echo $notrans;
    break;
    case 'hitungpekerjasekarang':
        $str	= "SELECT * FROM " . $dbname . ".datakaryawan WHERE lokasitugas ='".$_SESSION['empl']['lokasitugas']."' AND (tanggalkeluar ='0000-00-00' or tanggalkeluar > ".date('Y-m-d').") and kodejabatan='".$namajabatan."'";
        $res	= fetchData($str);
        $jumlah = count($res);
        echo $jumlah;
    break;
    
    case 'getedit':
        $str = "select * from " . $dbname . ".sdm_req_employee where notransaksi ='".$notransaksi."' ";
        $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        
        $jenistes_array = !empty($bar['jenistes']) ? json_decode($bar['jenistes'], true) : [];
        $jenisinterview_array = !empty($bar['jenisinterview']) ? json_decode($bar['jenisinterview'], true) : [];
        
        echo $notransaksi."###".$bar['namajabatan']."###".$bar['jumlahpekerjasekarang']."###".$bar['jumlahpekerjadibutuhkan']."###".$bar['departemen']."###".$bar['alasan']."###".$bar['statuskaryawan']."###".tanggalnormal($bar['mulaibekerja'])."###".$bar['golongan']."###".$bar['pendidikanminimal']."###".$bar['pengalamanminimal']."###".$bar['uraiankerja']."###".$bar['kualifikasi']."###".$bar['sertifikasi']."###".$bar['jeniskelamin']."###".$bar['statuspernikahan']."###".$bar['bidangpengalaman']."###".$bar['alasanganti']."###".$bar['note']."###".json_encode($jenistes_array)."###".json_encode($jenisinterview_array)."###".$bar['divisi'];
    break;
    case'loadData':
        $tab        ="";
        $footer     ="";
		$limit      = 15;
        $page       = 0;
        $colspan    = 14;

		if (isset($pagejs)) {
			$page   = $pagejs;
			if ($page < 0)
				$page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        if($notransaksisch != ''){
            $where.="and notransaksi LIKE '%".$notransaksisch."%'";
        }
        $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".sdm_req_employee WHERE lokasikerja='".$_SESSION['empl']['lokasitugas']."' ".$where." ORDER BY createtime DESC";
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		$res        = fetchdata($str);
		$jlhbrs     = $res[0]['jmlhrow'];
        $totrows    = ceil($jlhbrs / $limit);
        
        if($totrows == 0){
            $totrows = 1;
        }
                
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++){
            $sel    = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        
        $frompage   = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

        if($jlhbrs < 1){
            $tab.="<tr class=rowcontent>
                        <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                    </tr>";
        }else{
            $iList  = "SELECT * FROM " . $dbname . ".sdm_req_employee WHERE lokasikerja='".$_SESSION['empl']['lokasitugas']."' ".$where." ORDER BY notransaksi LIMIT ".$offset.",".$limit." ";
            $hasil  = fetchdata($iList);
            
            foreach ($hasil as $dList){
                $optjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$dList['namajabatan']."'");
                $optdep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$dList['departemen']."'");
                $optgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$dList['golongan']."'");
                // if($dList['statuspersetujuan'] == 1){
                //     $status = $_SESSION['lang']['disetujui'];
                // }
                // elseif ($dList['statuspersetujuan'] == 2) {
                //     $status = $_SESSION['lang']['Ditolak'];
                // }
                // elseif ($dList['statuspersetujuan'] == 9) {
                //     $status = $_SESSION['lang']['proses']." ".$_SESSION['lang']['approve'];
                // }else{
                //     $status = $_SESSION['lang']['belumdiajukan'];
                // }
                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>" . $no . "</td>";
                    $tab.="<td align=left>".$dList['notransaksi']."</td>";
                    $tab.="<td align=center>".$optjab[$dList['namajabatan']]."</td>";
                    $tab.="<td align=center>".$dList['jumlahpekerjasekarang']."</td>";
                    $tab.="<td align=center>".$dList['jumlahpekerjadibutuhkan']."</td>";
                    $tab.="<td align=center>".$optdep[$dList['departemen']]."</td>";
                    $tab.="<td align=center>".$optgol[$dList['golongan']]."</td>";
                    $tab.="<td align=center>".$dList['statuskaryawan']."</td>";
                    $tab.="<td align=center>".getNamaKaryawan($dList['createby'])."</td>";
                    if($dList['statuspersetujuan']==0 || $dList['statuspersetujuan']==3){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "');\">
                                </td>";
                        $tab.="<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center><img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan ".$dList['notransaksi']."'   onclick=\"postingData('".$dList['notransaksi']."');\"></td> ";
                    } else if($dList['statuspersetujuan']==9){
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/04.png class=zImgBtn height='30' title='Proses Persetujuan'></td>";
                    } else if($dList['statuspersetujuan']==1){
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/02.png  class=zImgBtn height='30' title='Disetujui' ></td>";
                    }else{
                        $tab.="<td align=center colspan=3><img src=images/icons/04/16/08.png  class=zImgBtn height='30' title='DiTolak' ></td>";
                    }
                    $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail Permintaan Karyawan onclick=previewDetail('".$dList['notransaksi']."');></td>";
					$tab.="<td align=center><img src=images/pdf.jpg class=zImgBtn  title='".$_SESSION['lang']['pdf']."' onclick=\"pdf('".$dList['notransaksi']."');\"></td>";	
                $tab.="</tr>
                    </tbody>";
            }
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>
                                ".$frompage." to ".$topage." Of ".  $jlhbrs."
                            </td>
                        </tr>";
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>";
                            if($page!=0){
                                $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                            }
                $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                            if((floatval($page)+1) != $totrows){
                                $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                            }
            $footer .=     "</td>
                        </tr>";
        }
        echo $tab."####".$footer;
    break;

    case 'insert':

        $str    = "SELECT notransaksi,lokasikerja FROM " . $dbname . ".sdm_req_employee where lokasikerja='".$_SESSION['empl']['lokasitugas']."' order by createtime desc limit 1";
        $qry    =$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$qry->fetch();
        $tglbln =substr($bar['notransaksi'],0,6);
        $unit =$bar['lokasikerja'];
        if($bar['notransaksi']=='' or $tglbln != date('Ym')){
            $nourut=1;
        }else{
            $explnotran=explode('/',$bar['notransaksi']);
            $nourut=$explnotran[3]+1;
        }
        $notransaksi= date('Ym')."/ERF/".$_SESSION['empl']['lokasitugas']."/".addZero($nourut,4);

        $str    = "SELECT * FROM " . $dbname . ".sdm_req_employee WHERE notransaksi ='".$notransaksi."'";
        $res    = fetchData($str);

        if(count($res)>0){
            exit(''."Warning : Data sudah ada !");
        }

        $payload = [
            'notransaksi' => $notransaksi,
            'namajabatan' => $namajabatan,
            'jumlahpekerjasekarang' => $jumlahpekerjasekarang,
            'jumlahpekerjadibutuhkan' => $jumlahpekerjadibutuhkan,
            'departemen' => $departemen,
            'alasan' => $alasan,
            'statuskaryawan' => $statuskaryawan,
            'mulaibekerja' => $mulaibekerja,
            'golongan' => $golongan,
            'pendidikanminimal' => $pendidikanminimal,
            'pengalamanminimal' => $pengalamanminimal,
            'lokasikerja' => $lokasikerja,
            'uraiankerja' => $uraiankerja,
            'kualifikasi' => $kualifikasi,
            'sertifikasi' => $sertifikasi,
            'jeniskelamin' => $jeniskelamin,
            'statuspernikahan' => $statuspernikahan,
            'bidangpengalaman' => $bidangpengalaman,
            'alasanganti' => $alasanganti,
            'usiamin' => $usiamin,
            'usiamax' => $usiamax,
            'note' => $note,
            'divisi' => $divisi,
            'jenistes' => json_encode($jenistes),
            'jenisinterview' => json_encode($jenisinterview),    
            'statuspersetujuan' => 0,
            'createby' => $_SESSION['standard']['userid'],
            'createtime' => date('Y-m-d H:i:s')
        ];

        try{
            $insertQ = insertQuery($dbname, 'sdm_req_employee', $payload, array_keys($payload));
            $owlPDO->exec($insertQ);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'update':
        $payload = [
            'namajabatan' => $namajabatan,
            'jumlahpekerjasekarang' => $jumlahpekerjasekarang,
            'jumlahpekerjadibutuhkan' => $jumlahpekerjadibutuhkan,
            'departemen' => $departemen,
            'alasan' => $alasan,
            'statuskaryawan' => $statuskaryawan,
            'mulaibekerja' => $mulaibekerja,
            'golongan' => $golongan,
            'pendidikanminimal' => $pendidikanminimal,
            'pengalamanminimal' => $pengalamanminimal,
            'lokasikerja' => $lokasikerja,
            'uraiankerja' => $uraiankerja,
            'kualifikasi' => $kualifikasi,
            'sertifikasi' => $sertifikasi,
            'jeniskelamin' => $jeniskelamin,
            'statuspernikahan' => $statuspernikahan,
            'bidangpengalaman' => $bidangpengalaman,
            'alasanganti' => $alasanganti,
            'usiamin' => $usiamin,
            'usiamax' => $usiamax,
            'note' => $note,
            'divisi' => $divisi,
            'jenistes' => json_encode($jenistes),
            'jenisinterview' => json_encode($jenisinterview),    
            'statuspersetujuan' => 0,
            'updateby' => $_SESSION['standard']['userid'],
        ];
        
        try{
            $updateQ = updateQuery($dbname, 'sdm_req_employee', $payload, "notransaksi='".$notransaksi."'");
            $owlPDO->exec($updateQ);
        }catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'delete':
        $ha     = "DELETE FROM " . $dbname . ".sdm_req_employee WHERE notransaksi='".$notransaksi."' ";
        try {
            $owlPDO->exec($ha);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'postingData': 
        $str="SELECT * FROM ".$dbname.".setup_approval WHERE kodeunit = '".substr($notransaksi,11,4)."' AND jenispersetujuan='".$jenispersetujuan."' order by level ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $row=$res->rowCount();
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $aju=$res->fetch();
        if($row<1){
            exit('Error : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan');
        }else{

        }
	break;

    case'form_ajukan';
        // $optdepartmen=makeOption($dbname,'datakaryawan','karyawanid,bagian',"karyawanid='".$_SESSION['standard']['userid']."'");
        // $departemen=$optdepartmen[$_SESSION['standard']['userid']];
        
        ##CEK PER DEPARTEMEN
        $str="select golongan,lokasikerja,departemen,count(lokasikerja) as kodeunit from ".$dbname.".sdm_req_employee where notransaksi='".$notransaksi."'";
        $res=fetchdata($str);
        $perdepartemen=$res[0]['kodeunit'];
        $departemen=$res[0]['departemen'];
        $lokasikerja=$res[0]['lokasikerja'];
        $golongan=$res[0]['golongan'];
        $where="";
        if($perdepartemen>0){
            $where.=" and departemen='".$departemen."'";
        }else{
            $where.=" and departemen=''";
        }

        // $optgol 	= makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan','aktif=1');
        // $golongan=$optgol[$_SESSION['empl']['kodegolongan']];
        
        #CEK PER GOLONGAN
        // $str="select count(lokasikerja) as kodeunit from ".$dbname.".sdm_req_employee where lokasikerja='".$lokasikerja."' and golongan='".$golongan."'";
        // $res=fetchdata($str);
        // $perdepartemen=$res[0]['kodeunit'];
        // $where="";
        // if($perdepartemen>0){
        //     $where.=" and golongan='".$golongan."'";
        // }else{
        //     $where.=" and golongan=''";
        // }
		
        ## APPROVAL DINAMIS SESUAI SETUP##
    
	    //$optper=array();
		$optKryx=array();
		$optKrylevel=array();

	    $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	    // $str="select * from ".$dbname.".setup_approval 
	    //         where jenispersetujuan='ERF' and kodeunit='".$lokasikerja."' and karyawaniduser='".$_SESSION['standard']['userid']."' ".$where." order by level";  
	    // $res=fetchData($str);
	    // if(count($res) > 0){
	    //     foreach($res as $key => $bar){
	    //         $whr		=" karyawanid='".$bar['karyawanid']."'";
	    //         $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	           
	    //        $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
	    //         $optKrylevel[$bar['level']]=$bar['level'];
	            
	    //     }
	    // }else{
	        
	        $str="select * from ".$dbname.".setup_approval 
	        where jenispersetujuan='ERF' and kodeunit='".$lokasikerja."' and karyawaniduser='' ".$where." order by level";
	        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	        $res->setFetchMode(PDO::FETCH_ASSOC);
	        while($bar=$res->fetch()){
	            $whr		=" karyawanid='".$bar['karyawanid']."'";
	            $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            
	            $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
	            $optKrylevel[$bar['level']]=$bar['level'];

	        }

	    // }

		
		$jumlahlevel=count($optKrylevel);    
        $tab.="<input hidden id=jlh value='".$jumlahlevel."'>";
        $tab.="<input hidden id=notransaksi_ajukan value='".$notransaksi."'>";
		if($jumlahlevel>0)
		{
			foreach ($optKrylevel as $key) {
				$optKry='';
				foreach ($optKryx[$key] as $key2 => $val) {
					$optKry.=$val;
				}
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-".$key."</td>
						<td width=5px>:</td>
						<td><select id=kepada".$key." style='width:99%;'>".$optKry."</select></td>     
					</tr>";
				
			}

		}
		else
		{			$jumlahlevel=1;
					$tab .= "<tr class=rowcontent>
						<td>Approval ke-1</td>
						<td width=5px>:</td>
						<td><select id=kepada1 style='width:99%;'></select></td>
					</tr>";
		}
        $tab.="<tr class=rowcontent>
                <td></td>
                <td></td>
                <td><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
            </tr>               
        </table>";
		echo $tab;
	break;
    case'ajukan':
        for ($i=1; $i <= $jlh ; $i++) { 
            $per['persetujuan'.$i]=checkPostGet("kepada".$i, '');
            if($per['persetujuan'.$i] == '' or $notransaksi==''){
                exit('Warning : Isikan nama penyetuju.');
            }
        }
        $str = "UPDATE " . $dbname . ".sdm_req_employee SET statuspersetujuan='9' WHERE notransaksi= '" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
        for($i=1; $i<=$jlh; $i++){
            $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuan."' and level='".$i."' and kodeunit='".$_SESSION['empl']['lokasitugas']."'";
            // exit("error : $str");
            $res=fetchData($str);
            $tipeapp = $res[0]['tipe'];
            $departemenapp = $res[0]['departemen'];
            $tipekaryawanapp = $res[0]['tipekaryawan'];
            $jabatanapp = $res[0]['jabatan'];
            
            if(count($res) > 0){
                if($tipeapp=='1'){
                    if($departemenapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($tipekaryawanapp!=''){
                        $str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                            $owlPDO->exec($str);
                        }
                    }
                    if($jabatanapp!='0'){
                        $str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
                        $res=fetchdata($str);
                        foreach($res as $keyx=>$valx){
                            if($per['persetujuan'.$i]!=''){
                                $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$valx['karyawanid']."','0')";
                                $owlPDO->exec($str);
                            }
                        }
                    }
                }else{
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$notransaksi."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."','0')";
                        try
                        {
                            $owlPDO->exec($str);
                        }
                        catch (PDOException $e) 
                        {
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }
            }
        }
	break;
    case 'preview':
        #ambildata
        $str    = "SELECT * FROM ".$dbname.".sdm_req_employee where notransaksi='".$notransaksi."'";
        $res    = fetchdata($str);

        $nmjab  = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$res[0]['namajabatan']."'");
        $nmgol  = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$res[0]['golongan']."'");
        $nmdep  = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$res[0]['departemen']."'");
        $jlhapp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas']);

        $strAv  = "select b.namajabatan from ".$dbname.".datakaryawan a 
                    left join ".$dbname.".sdm_5jabatan b 
                    on a.kodejabatan=b.kodejabatan 
                    where  karyawanid =".$res[0]['createby']." ";		
        $rjab   =fetchdata($strAv);

        $stream     ='';
        $stream1    ='';
        $stream2    ='';
        $fontjdl    = 'style=font-size:22px';
        $fontisi    = 'font-size:20px';
        if($tipe == 'pdf'){
            $jdl    = 'Informasi Pekerjaan';
            $setel  = 'style=padding-left:50px;padding-right:20px;max-width:100%';
        }else{
            $jdl    = 'Employee Requisition';
            $setel  = "width=100% cellpading=1 cellspacing=0";
        }

        $stream.="<table width=100%>";
            $stream.="<tr>";
                $stream.="<td style=width:15%;padding:10px; align=center><img src=images/ksp.jpg style=height:100px></td>";
                $stream.="<td style=width:85%;padding-left:20px;font-size:20px; align=left valign=center>
                            <table>
                                <tr><td><b>".$_SESSION['org']['namaorganisasi']."</b></td></tr>
                                <tr><td>Employee Requisition</td></tr>
                            </table></td>";

            $stream.="</tr>";
        $stream.="</table><hr style=height:1.5px;border-width:0;color:black;background-color:black>";

        $stream1.="<table width=100%>";
            $stream1.="<tr style=padding-bottom:30px>";
                $stream1.="<td align=center ".$fontjdl."><b><u>".$jdl."</u></b></td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=center ".$fontisi.">(<i>".$notransaksi."</i>)</td>";
            $stream1.="</tr>";
        $stream1.="</table><br>";
        $stream1.="<table ".$setel." >";
            $stream1.="<tr>";
                $stream1.="<td align=left style=width:180px>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['jabatan'] . "</td>";
                $stream1.="<td style=width:1px;>:</td>";
                $stream1.="<td>".$nmjab[$res[0]['namajabatan']]."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td valign=top align=left>" . $_SESSION['lang']['departemen'] . "</td>";
                $stream1.="<td valign=top>:</td>";
                $stream1.="<td>".$nmdep[$res[0]['departemen']]."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['kodegolongan'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$nmgol[$res[0]['golongan']]."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['pekerjasekarang'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['jumlahpekerjasekarang']." " . $_SESSION['lang']['orang'] . "</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['pekerjadibutuhkan'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['jumlahpekerjadibutuhkan']." " . $_SESSION['lang']['orang'] . "</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['alasanminta'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['alasan']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['statuskaryawan']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['mulaikerja'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".tglnmbln($res[0]['mulaibekerja'],'I','short')."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['pendidikanmin'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['pendidikanminimal']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['pengalamanmin'] . "</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['pengalamanminimal']." " . $_SESSION['lang']['tahun'] . "</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['lokasi'] . " Kerja</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['lokasikerja']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td align=left>" . $_SESSION['lang']['divisi'] . " Kerja</td>";
                $stream1.="<td>:</td>";
                $stream1.="<td>".$res[0]['divisi']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td valign=top>" . $_SESSION['lang']['uraiankerja'] . "</td>";
                $stream1.="<td valign=top>:</td>";
                $stream1.="<td style=max-width:100%>".$res[0]['uraiankerja']."</td>";
            $stream1.="</tr>";
            $stream1.="<tr>";
                $stream1.="<td valign=top>" . $_SESSION['lang']['kualifikasi'] . "</td>";
                $stream1.="<td valign=top>:</td>";
                $stream1.="<td>".$res[0]['kualifikasi']."</td>";
            $stream1.="</tr>";
        $stream1.="</table>";
        $stream2.="<br><hr style=height:1.5px;border-width:0;color:black;background-color:black>";
        $stream2.="<table width=100%>";
            $stream2.="<tr>";
                $stream2.="<td style='text-align:center' valign=top>Diajukan Oleh : <br>(".tglnmbln(substr($res[0]['createtime'],0,10),'I','short').")</td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
                    @$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    if($arrDetail['tanggal'] != ''){
                        $tgl = "(".tglnmbln(substr($arrDetail['tanggal'],0,10),'I','short').")";
                    }else{
                        $tgl = '';
                    }
                    $stream2.="<td style='text-align:center' valign=top>".$_SESSION['lang']['approve']." ".$i." : <br>".$tgl."</td>";
                }
            $stream2.="</tr>";
            $stream2.="<tr>";
                $stream2.="<td style='text-align:center'></td>";
                for ($i=1; $i <=$jlhapp ; $i++) {
                    $stream2.="<td style='text-align:center;height:50px'></td>";
                }
            $stream2.="</tr>";
            $stream2.="<tr>";
                $stream2.="<td style='text-align:center'><b><u>".getNamaKaryawan($res[0]['createby'])."</u></b></td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
					@$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    $stream2.="<td style='text-align:center'><b><u>".$arrDetail['nama']."</u></b></td>";
                }
            $stream2.="</tr>";
            $stream2.="<tr>";
                $stream2.="<td style='text-align:center'>".$rjab[0]['namajabatan']."</td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
					@$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    $stream2.="<td style='text-align:center'>".$arrDetail['namajabatan']."</td>";
                }
            $stream2.="</tr>";
        $stream2.="</table>";
        // $pdfnya = $stream."".$stream1."".$stream2;
        // if($tipe=='pdf'){
        //     $dompdf = new Dompdf();
        //     $dompdf->loadHtml($pdfnya);
        //     $dompdf->setPaper('A4', 'potrait');
        //     $dompdf->render();
        //     $dompdf->stream("Permintaan Pekerjaan",array("Attachment"=>0));
        // }else{
            echo $stream1;
        // }
    break;
    case 'preview2':
        #ambildata
        $str    = "SELECT * FROM ".$dbname.".sdm_req_employee where notransaksi='".$notransaksi."'";
        $res    = fetchdata($str);

        $nmjab  = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$res[0]['namajabatan']."'");
        $nmgol  = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$res[0]['golongan']."'");
        $nmdep  = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$res[0]['departemen']."'");
        $jlhapp = getCountApproval($jenispersetujuan,$_SESSION['empl']['lokasitugas']);

        $strAv  = "select b.namajabatan from ".$dbname.".datakaryawan a 
                    left join ".$dbname.".sdm_5jabatan b 
                    on a.kodejabatan=b.kodejabatan 
                    where  karyawanid =".$res[0]['createby']." ";		
        $rjab   = fetchdata($strAv);

        $stream     ='';
        $fontjdl    = 'style=font-size:22px';
        $fontisi    = 'font-size:20px';
        $jdl        = 'Informasi Pekerjaan';

        $stream.="<table width=100% style='border-top:0.1px solid black;'>";
            $stream.="<tr>";
                $stream.="<td style='width:20%;border-style: solid;' align=center><img src=images/ksp.jpg style=height:100px></td>";
                $stream.="<td style='width:60%;border-style: solid;' align=center>
                            <table align=center>
                                <tr><td align=center valign=top>Employee Requisition<br></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td></td></tr>
                                <tr><td align=center valign=bottom><b>".$_SESSION['org']['namaorganisasi']."</b></td></tr>
                            </table></td>";
                $stream.="<td style='width:20%;padding:5px;font-size:13px;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;border-right:0.1px solid black;cellpadding:0px' align=justify valign=top>Requisition for new staff must be submitted to the Human Resource Department at least 1 week for approval before the recruitment process can be effected </td>";

            $stream.="</tr>";
        $stream.="</table><br>";

        $stream.="<table width=100% style='border-style: solid;'>";
            $stream.="<tr style=padding-bottom:30px>";
                $stream.="<td align=center ".$fontjdl."><b>".$jdl."</b></td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=center ".$fontisi."><i>(Position Information)</i></td>";
            $stream.="</tr>";
        $stream.="</table>";
        $stream.="<table style='max-width:100%;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;
                            border-right:0.1px solid black;'>";
            $stream.="<tr style='max-width:100%;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;     
                            border-right:0.1px solid black;'>";
                // $stream.="<td>";
                //     $stream.="<tr>";
                        $stream.="<td valign=top align=left >" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['jabatan'] . " <br><i>(Position Applied)</i></td>";
                        // $stream.="<td valign=top></td>";
                        $stream.="<td valign=top>: ".$nmjab[$res[0]['namajabatan']]."</td>";

                        
                    $stream.="<td align=left style='max-width:100%;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;border-right:0.1px solid black;'>" . $_SESSION['lang']['pekerjasekarang'] . "<br><i>(Number of existing manpower)</i></td>";
                    $stream.="<td>:</td>";
                    $stream.="<td>".$res[0]['jumlahpekerjasekarang']." " . $_SESSION['lang']['orang'] . "</td>";
                //     $stream.="</tr>"; 
                // $stream.="</td>";
                // $stream.="<td>";
                // $stream.="</td>";
            $stream.="</tr>";
            // $stream.="<tr><td></td></tr>";
        $stream.="</table>";
            // $stream.="<table style='max-width:100%;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;border-right:0.1px solid black;'>";
            //     $stream.="<tr>";
                //     $stream.="<td align=left>" . $_SESSION['lang']['pekerjasekarang'] . "</td>";
                //     $stream.="<td>:</td>";
                //     $stream.="<td>".$res[0]['jumlahpekerjasekarang']." " . $_SESSION['lang']['orang'] . "</td>";
                // $stream.="</tr>";
                // // $stream.="<tr>";
                //     $stream.="<td align=left>" . $_SESSION['lang']['pekerjadibutuhkan'] . "</td>";
                //     $stream.="<td>:</td>";
                //     $stream.="<td>".$res[0]['jumlahpekerjadibutuhkan']." " . $_SESSION['lang']['orang'] . "</td>";
            //     $stream.="</tr>";
            // $stream.="</table>";
            $stream.="<table style='max-width:100%;border-top:0.1px solid black;border-left:0.1px solid black;border-bottom:0.1px solid black;border-right:0.1px solid black;'>";
            $stream.="<tr>";
                $stream.="<td valign=top align=left>" . $_SESSION['lang']['departemen'] . "</td>";
                $stream.="<td valign=top>:</td>";
                $stream.="<td>".$nmdep[$res[0]['departemen']]."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['kodegolongan'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$nmgol[$res[0]['golongan']]."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['alasanminta'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$res[0]['alasan']."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['status'] . " " . $_SESSION['lang']['karyawan'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$res[0]['statuskaryawan']."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['mulaikerja'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".tglnmbln($res[0]['mulaibekerja'],'I','short')."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['pendidikanmin'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$res[0]['pendidikanminimal']."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['pengalamanmin'] . "</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$res[0]['pengalamanminimal']." " . $_SESSION['lang']['tahun'] . "</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td align=left>" . $_SESSION['lang']['lokasi'] . " Kerja</td>";
                $stream.="<td>:</td>";
                $stream.="<td>".$res[0]['lokasikerja']."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td valign=top>" . $_SESSION['lang']['uraiankerja'] . "</td>";
                $stream.="<td valign=top>:</td>";
                $stream.="<td style=max-width:100%>".$res[0]['uraiankerja']."</td>";
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td valign=top>" . $_SESSION['lang']['kualifikasi'] . "</td>";
                $stream.="<td valign=top>:</td>";
                $stream.="<td>".$res[0]['kualifikasi']."</td>";
            $stream.="</tr>";
        $stream.="</table>";
        $stream.="<br><hr style=height:1.5px;border-width:0;color:black;background-color:black>";
        $stream.="<table width=100%>";
            $stream.="<tr>";
                $stream.="<td style='text-align:center' valign=top>Diajukan Oleh : <br>(".tglnmbln(substr($res[0]['createtime'],0,10),'I','short').")</td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
                    @$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    if($arrDetail['tanggal'] != ''){
                        $tgl = "(".tglnmbln(substr($arrDetail['tanggal'],0,10),'I','short').")";
                    }else{
                        $tgl = '';
                    }
                    $stream.="<td style='text-align:center' valign=top>".$_SESSION['lang']['approve']." ".$i." : <br>".$tgl."</td>";
                }
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td style='text-align:center'></td>";
                for ($i=1; $i <=$jlhapp ; $i++) {
                    $stream.="<td style='text-align:center;height:50px'></td>";
                }
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td style='text-align:center'><b><u>".getNamaKaryawan($res[0]['createby'])."</u></b></td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
					@$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    $stream.="<td style='text-align:center'><b><u>".$arrDetail['nama']."</u></b></td>";
                }
            $stream.="</tr>";
            $stream.="<tr>";
                $stream.="<td style='text-align:center'>".$rjab[0]['namajabatan']."</td>";
                for ($i=1; $i <= $jlhapp ; $i++) { 
					@$arrDetail = detailApprove($i,$notransaksi,$jenispersetujuan);
                    $stream.="<td style='text-align:center'>".$arrDetail['namajabatan']."</td>";
                }
            $stream.="</tr>";
        $stream.="</table>";
            $dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();
            $dompdf->stream("Permintaan Pekerjaan",array("Attachment"=>0));
    break;
    case 'previewori':
            ob_end_clean();
            class PDF extends FPDF {	
                function Header() {
                    global $conn;
                    global $dbname;
                    global $owlPDO;
                    global $nmjab;
                    global $nmdept;
                    global $nmgol;
                    global $nmorg;
                    global $notransaksi;
                    global $namajabatan;
                    global $jumlahpekerjasekarang;
                    global $jumlahpekerjadibutuhkan;
                    global $departemen;
                    global $golongan;
                    global $alasan;
                    global $statuskaryawan;
                    global $mulaibekerja;
                    global $golongan;
                    global $pendidikanminimal;
                    global $pengalamanminimal;
                    global $lokasikerja;
                    global $uraiankerja;
                    global $kualifikasi;
                    global $statuspersetujuan;
                    global $createby;
                    global $createtime;
                    
                    $pt='';
                    $namapt='';
                    $alamatpt='';
                    $telp='';
                    $namajabatan='';
                    $status=0;
                    $str="select * from ".$dbname.".sdm_req_employee where notransaksi='".$notransaksi."'";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch())
                    {
                        $lokasikerja            =$bar->lokasikerja;
                        $namajabatan            =$bar->namajabatan;
                        $jumlahpekerjasekarang  =$bar->jumlahpekerjasekarang;
                        $jumlahpekerjadibutuhkan=$bar->jumlahpekerjadibutuhkan;
                        $departemen             =$bar->departemen;
                        $alasan                 =$bar->alasan;
                        $statuskaryawan         =$bar->statuskaryawan;
                        $mulaibekerja           =$bar->mulaibekerja;
                        $golongan               =$bar->golongan;
                        $pendidikanminimal      =$bar->pendidikanminimal;
                        $pengalamanminimal      =$bar->pengalamanminimal;
                        $lokasikerja            =$bar->lokasikerja;
                        $uraiankerja            =$bar->uraiankerja;
                        $kualifikasi            =$bar->kualifikasi;	
                        $statuspersetujuan      =$bar->statuspersetujuan;		
                        $createby               =$bar->createby;
                        $createtime             =$bar->createtime;
                    }	
                    
                    $nmjab      = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$namajabatan."'");
                    $nmdept     = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemen."'");
                    $nmgol      = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan',"kodegolongan='".$golongan."'");
                    $nmorg      = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$lokasikerja."'");
                    $arrHead    = setheadreport('',$lokasikerja);
                    $path       =$arrHead['logopalma'];
                    
                    //$path='images/logo.jpg';
                    $this->Image($path,16,10,0,10);
                    $this->SetFont('Times','B',13);
                    $this->SetFillColor(255,255,255);
                    $this->SetY(5);
                    $this->SetX(10); 
                    $this->MultiCell(30,22,'',1,'C',''); 
                    $this->SetY(5);
                    $this->SetX(40); 
                    $this->MultiCell(120,11,'Employee Requistion','T','C',''); 				
                    $this->SetY(15);
                    $this->SetX(40); 
                    $this->MultiCell(120,12,$_SESSION['org']['namaorganisasi'],'B','C',''); 				
                    $this->SetFont('Times','',8);
                    $this->SetY(5);	
                    $this->SetX(160);	
                    $this->MultiCell(40,3.7,'Requisition for new staff must be submitted to the Human Resource Department at least 1 week for approval before the recruitment process can be effected ',1,'L','');	  
                }
                
                function Footer()
                {
                    $this->SetY(-15);
                    $this->SetFont('Times','I',8);
                    $this->Cell(10,10,'Page '.$this->PageNo(),0,0,'C');
                    $this->SetFont('Times','',8);
                    $this->SetX(150);
                    $this->Cell(140,10,'PRINT TIME : '.tglnmbln(date('Y-m-d'),'I','short').' '.date('h:i:s'),0,1,'L');		
                }
        
            }
        
            $pdf=new PDF('P','mm','A4');
            $pdf->AddPage();
            
            $pdf->Ln();	
            $pdf->SetFont('Times','B',14);
            // $pdf->SetX(150);
            $pdf->MultiCell(190,5,'Informasi Pekerjaan','LTR','C');	
            $pdf->SetFont('Times','I',9);
            $pdf->MultiCell(190,5,'(Position Information)','LRB','C');
            $pdf->SetFont('Times','',9);
            $pdf->SetX(10);
            $pdf->SetY(41);
            $pdf->MultiCell(28,5,'Nama Jabatan         :','L','');
            $pdf->SetY(41);
            $pdf->SetX(38);
            $pdf->MultiCell(32,5,''.$nmjab[$namajabatan],'R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetX(10);
            $pdf->SetY(46);
            $pdf->MultiCell(28,5,'(Position Applied)','L','');	

            $pdf->SetFont('Times','',9);
            $pdf->SetY(41);
            $pdf->SetX(70);
            $pdf->MultiCell(44,5,'Jumlah manpower saat ini         :','','');
            $pdf->SetY(41);
            $pdf->SetX(114);
            $pdf->MultiCell(20,5,''.$jumlahpekerjasekarang.' (Orang)','R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(46);
            $pdf->SetX(70);
            $pdf->MultiCell(44,5,'(Number of existing manpower)','L','');	
            $pdf->SetFont('Times','',9);

            $pdf->SetY(41);
            $pdf->SetX(134);
            $pdf->MultiCell(44,5,'Section/Dept :','','');
            $pdf->SetY(41);
            $pdf->SetX(154);
            $pdf->MultiCell(46,5,''.$nmdept[$departemen],'R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(46);
            $pdf->SetX(134);
            $pdf->MultiCell(66,5,'','LR','');	
            //kosong
            $pdf->SetFont('Times','',9);
            $pdf->SetX(10);
            $pdf->SetY(51);
            $pdf->MultiCell(28,5,'','L','');
            $pdf->SetY(51);
            $pdf->SetX(38);
            $pdf->MultiCell(32,5,'','R','');
            $pdf->SetFont('Times','',9);
            $pdf->SetY(51);
            $pdf->SetX(70);
            $pdf->MultiCell(44,5,'','','');
            $pdf->SetY(51);
            $pdf->SetX(114);
            $pdf->MultiCell(20,5,'','R','');
            $pdf->SetY(51);
            $pdf->SetX(134);
            $pdf->MultiCell(44,5,'','','');
            $pdf->SetY(51);
            $pdf->SetX(154);
            $pdf->MultiCell(46,5,'','R','');
            
            $pdf->SetFont('Times','',9);
            $pdf->SetX(10);
            $pdf->SetY(56);
            $pdf->MultiCell(28,5,'','L','');
            $pdf->SetY(56);
            $pdf->SetX(38);
            $pdf->MultiCell(32,5,'','R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(61);
            $pdf->SetX(10);
            $pdf->MultiCell(28,5,'','L','');	

            $pdf->SetFont('Times','',9);
            $pdf->SetY(56);
            $pdf->SetX(70);
            $pdf->MultiCell(44,5,'Jumlah manpower dibutuhkan :','','');
            $pdf->SetY(56);
            $pdf->SetX(114);
            $pdf->MultiCell(20,5,''.$jumlahpekerjadibutuhkan.' (People)','R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(61);
            $pdf->SetX(70);
            $pdf->MultiCell(64,5,'(Number of manpower requested)','L','');	
            $pdf->SetFont('Times','',9);

            $pdf->SetY(56);
            $pdf->SetX(134);
            $pdf->MultiCell(44,5,'','','');
            $pdf->SetY(56);
            $pdf->SetX(154);
            $pdf->MultiCell(46,5,'','R','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(61);
            $pdf->SetX(134);
            $pdf->MultiCell(66,5,'','LR','');
            //line2
            
            $pdf->SetFont('Times','',9);
            $pdf->SetX(10);
            $pdf->SetY(66);
            $pdf->MultiCell(50,5,'Alasan permintaan:','LT','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(71);
            $pdf->SetX(10);
            $pdf->MultiCell(50,5,'(Reason for Requisition)','L','');	

            $pdf->SetFont('Times','',9);
            $pdf->SetY(66);
            $pdf->SetX(60);
            $pdf->MultiCell(58,5,'Status Karyawan :','LT','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(71);
            $pdf->SetX(60);
            $pdf->MultiCell(44,5,'(Employee Status)','L','');	
            $pdf->SetFont('Times','',9);

            $pdf->SetY(66);
            $pdf->SetX(118);
            $pdf->MultiCell(28,6,'Mulai bekerja :','LT','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(71);
            $pdf->SetX(118);
            $pdf->MultiCell(28,5,'(Join Date)','L','');	
            $pdf->SetFont('Times','',9);
            $pdf->SetY(66);
            $pdf->SetX(146);
            $pdf->MultiCell(54,5,'Golongan :','LTR','');
            $pdf->SetY(71);
            $pdf->SetX(146);
            $pdf->MultiCell(54,5,$nmgol[$golongan],'LR','');
            // echo $alasan."<br>";
            // echo $statuskaryawan."<br>";
            if($alasan == 'Penambahan'){
                @$bajai1 = 1;
            }else{
                @$bajai1 = 0;
            }
            if($alasan == 'Penggantian'){
                @$bajai2 = 1;
            }else{
                @$bajai2 = 0;
            }
            if($alasan == 'Dianggarkan'){
                @$bajai3 = 1;
            }else{
                @$bajai3 = 0;
            }
            if($statuskaryawan == 'Tetap'){
                @$bajai4 = 1;
            }else{
                @$bajai4 = 0;
            }
            if($statuskaryawan == 'Kontrak'){
                @$bajai5 = 1;
            }else{
                @$bajai5 = 0;
            }
            if($statuskaryawan == 'Percobaan'){
                @$bajai6 = 1;
            }else{
                @$bajai6 = 0;
            }
            //kotak1
            $pdf->SetY(76);
            $pdf->SetX(10);
            $pdf->MultiCell(5,6,'','L','');
            $pdf->SetY(76);
            $pdf->SetX(12);
            $pdf->MultiCell(7,5,'',1,1,@$bajai1);
            $pdf->SetY(76);
            $pdf->SetX(21);
            $pdf->MultiCell(39,6,'Penambahan (new)','R',1);
            $pdf->SetY(76);
            $pdf->SetX(60);
            $pdf->MultiCell(5,6,'','L','');
            $pdf->SetY(76);
            $pdf->SetX(62);
            $pdf->MultiCell(7,5,'',1,1,@$bajai4);
            $pdf->SetY(76);
            $pdf->SetX(71);
            $pdf->MultiCell(47,6,'Permanent (PKWTT)','R',1);
            $pdf->SetY(76);
            $pdf->SetX(118);
            $pdf->MultiCell(28,5,tglnmbln($mulaibekerja,'I','short'),'LR','');	
            $pdf->SetY(76);
            $pdf->SetX(146);
            $pdf->MultiCell(54,5,'','R','');
            //kotak2
            $pdf->SetY(82);
            $pdf->SetX(10);
            $pdf->MultiCell(5,6,'','L','');
            $pdf->SetY(82);
            $pdf->SetX(12);
            $pdf->MultiCell(7,5,'',1,1,@$bajai2);
            $pdf->SetY(82);
            $pdf->SetX(21);
            $pdf->MultiCell(39,6,'Penggantian (Replacement)','R',1);
            $pdf->SetY(82);
            $pdf->SetX(60);
            $pdf->MultiCell(5,6,'','L','');
            $pdf->SetY(82);
            $pdf->SetX(62);
            $pdf->MultiCell(7,5,'',1,1,@$bajai5);
            $pdf->SetY(82);
            $pdf->SetX(71);
            $pdf->MultiCell(47,6,'Contract (PKWT)','R',1);
            $pdf->SetY(81);
            $pdf->SetX(118);
            $pdf->MultiCell(28,7,'','LR','');	
            $pdf->SetY(81);
            $pdf->SetX(146);
            $pdf->MultiCell(54,7,'','R','');
            //kotak3
            $pdf->SetY(88);
            $pdf->SetX(10);
            $pdf->MultiCell(11,6,'','LB','');
            $pdf->SetY(88);
            $pdf->SetX(12);
            $pdf->MultiCell(7,5,'',1,1,@$bajai3);
            $pdf->SetY(88);
            $pdf->SetX(21);
            $pdf->MultiCell(39,6,'Dianggarkan (budgeted)','RB',1);
            $pdf->SetY(88);
            $pdf->SetX(60);
            $pdf->MultiCell(11,6,'','LB','');
            $pdf->SetY(88);
            $pdf->SetX(62);
            $pdf->MultiCell(7,5,'',1,1,@$bajai6);
            $pdf->SetY(88);
            $pdf->SetX(71);
            $pdf->MultiCell(47,6,'Daily (Harian)','RB',1);
            $pdf->SetY(88);
            $pdf->SetX(118);
            $pdf->MultiCell(28,6,'','LRB','');	
            $pdf->SetY(88);
            $pdf->SetX(146);
            $pdf->MultiCell(54,6,'','RB','');
            //line3
            
            $pdf->SetFont('Times','',9);
            $pdf->SetX(10);
            $pdf->SetY(94);
            $pdf->MultiCell(50,5,'Pendidikan min :','L','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(99);
            $pdf->SetX(10);
            $pdf->MultiCell(50,5,'(Level of Education)','L','');	
            $pdf->SetFont('Times','',9);
            $pdf->SetY(94);
            $pdf->SetX(60);
            $pdf->MultiCell(68,5,'Pengalaman min (tahun) :','L','');
            $pdf->SetFont('Times','I',9);
            $pdf->SetY(99);
            $pdf->SetX(60);
            $pdf->MultiCell(68,5,'(Min. Year of Experiences)','L','');	
            $pdf->SetFont('Times','',9);
            $pdf->SetY(94);
            $pdf->SetX(128);
            $pdf->MultiCell(72,5,'Lokasi Kerja :','LR','');
            $pdf->SetY(99);
            $pdf->SetX(128);
            $pdf->MultiCell(72,5,$nmorg[$lokasikerja],'LR','');
            $pdf->SetFont('Times','',9);
            $pdf->SetY(104);
            $pdf->SetX(10);
            $pdf->MultiCell(50,5,$pendidikanminimal,'LB','');
            $pdf->SetY(104);
            $pdf->SetX(60);
            $pdf->MultiCell(68,5,$pengalamanminimal,'LB','');
            $pdf->SetY(104);
            $pdf->SetX(128);
            $pdf->MultiCell(72,5,'','LRB','');

            $pdf->SetFont('Times','B',9);
            $pdf->SetY(109);
            $pdf->SetX(10);
            $pdf->MultiCell(190,5,'Uraian Kerja (Job Description)',1,'C');
            $pdf->SetFont('Times','',9);
            $pdf->SetY(114);
            $pdf->SetX(10);
            $pdf->MultiCell(5,15,'','L','C');
            $pdf->SetY(114);
            $pdf->SetX(15);
            $pdf->MultiCell(180,5,$uraiankerja,'','C');
            $pdf->SetY(114);
            $pdf->SetX(195);
            $pdf->MultiCell(5,15,'','R','C');
            $pdf->SetFont('Times','B',9);
            $pdf->SetY(129);
            $pdf->SetX(10);
            $pdf->MultiCell(190,5,'Kualifikasi (Qualifications)',1,'C');
            $pdf->SetFont('Times','',9);
            $pdf->SetY(134);
            $pdf->SetX(10);
            $pdf->MultiCell(190,5,$kualifikasi,'LR','C');

            #ambil persetujuan
            $countApp = getCountApproval('ERF',$lokasikerja);
            $widthApp = 190 /($countApp+1);
            $str        = "select a.*,b.kodejabatan,c.namajabatan from ".$dbname.".setup_approval a left join 
                            ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid left join
                            ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan where a.kodeunit='".$lokasikerja."' and jenispersetujuan='ERF'";
            $res        = fetchdata($str);
            $str2        = "select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and jenispersetujuan='ERF'";
            $res2        = fetchdata($str2);

            $pdf->SetFillColor(220,220,220);
            $pdf->SetFont('Times','',9);
            $pdf->Cell($widthApp,4,'Diajukan Oleh','LTR',0,'C',1);
            for($i=1;$i<=$countApp;$i++)
            { 
                $pdf->SetFont('Times','',9);
                $pdf->Cell($widthApp,4,'Persetujuan '.$i,'LTR',0,'C',1);
            }
            $pdf->ln();
            $pdf->SetFont('Times','I',7.5);
            $pdf->Cell($widthApp,4,'(Request By)','LRB',0,'C',1);
            foreach ($res as $value) 
            {
                $pdf->SetFont('Times','I',7.5);
                $pdf->Cell($widthApp,4,'('.$value['namajabatan'].')','LRB',0,'C',1);
            }
            $pdf->ln();
            $pdf->SetFont('Times','',9);
            $pdf->Cell($widthApp,25,'','LTR',0,'C',1);
            for($i=1;$i<=$countApp;$i++)
            { 
                $pdf->SetFont('Times','',9);
                $pdf->Cell($widthApp,25,'','LTR',0,'C',1);
            }
            $pdf->ln();
            $pdf->SetFont('Times','BU',7);
            $pdf->Cell($widthApp,5,getNamaKaryawan($createby),'LR',0,'C',1);
            foreach ($res as $value) 
            {
                $pdf->SetFont('Times','BU',7);
                $pdf->Cell($widthApp,5,getNamaKaryawan($value['karyawanid']),'LR',0,'C',1);
            }
            $pdf->ln();
            $pdf->SetFont('Times','B',8);
            $pdf->Cell($widthApp,5,' Date :','LRB',0,'L',1);
            for($i=1;$i<=$countApp;$i++)
            {
                $pdf->SetFont('Times','B',8);
                $pdf->Cell($widthApp,5,' Date : ','LRB',0,'L',1);
            }
            $pdf->ln();
            $pdf->ln();
            $pdf->SetFont('Times','B',9);
            // $pdf->SetFillColor(255,255,255);
            // $pdf->Cell(20,5,'HCM Use Only','',0,'L',1);
            // $pdf->ln();

            // $pdf->SetFillColor(220,220,220);
            // $pdf->SetFont('Times','',9);
            // $pdf->Cell(63,4,'HCM - RO','LTR',0,'C',1);
            // $pdf->Cell(63,4,'HCM - HO','LTR',0,'C',1);
            // $pdf->Cell(64,4,'Persetujuan Direksi Group','LTR',0,'C',1);
            // $pdf->ln();
            // $pdf->SetFont('Times','I',7.5);
            // $pdf->Cell(63,4,'(Verified By)','LRB',0,'C',1);
            // $pdf->Cell(63,4,'(Approved by)','LRB',0,'C',1);
            // $pdf->Cell(64,4,'(Approved by)','LRB',0,'C',1);
            // $pdf->ln();
            // $pdf->SetFont('Times','',9);
            // $pdf->Cell(63,25,'','LTR',0,'C',1);
            // $pdf->Cell(63,25,'','LTR',0,'C',1);
            // $pdf->Cell(64,25,'','LTR',0,'C',1);
            // $pdf->ln();
            // $pdf->SetFont('Times','BU',7);
            // $pdf->Cell(63,5,'                                                                        ','LR',0,'C',1);
            // $pdf->Cell(63,5,'                                                                        ','LR',0,'C',1);
            // $pdf->Cell(64,5,'                                                                        ','LR',0,'C',1);
            // $pdf->ln();
            // $pdf->SetFont('Times','B',8);
            // $pdf->Cell(63,5,' Date :','LRB',0,'L',1);
            // $pdf->Cell(63,5,' Date :','LRB',0,'L',1);
            // $pdf->Cell(64,5,' Date :','LRB',0,'L',1);
            $pdf->Output();
    break;
    case 'previewPDF2':
        ob_end_clean();

        $qReqEmployee = selectQuery($dbname, "sdm_req_employee", "*", "notransaksi='".$notransaksi."'");
        $res = fetchData($qReqEmployee);

        $departemen = getNamaDepartemen($res[0]['departemen']);
        $tanggalDibuat = tanggalnormal($res[0]['createtime']);
        $jabatan = getNamaJabatan($res[0]['namajabatan']);
        $lokasikerja = $res[0]['lokasikerja'];
        $tanggalBekerja = tanggalnormal($res[0]['mulaibekerja']);
        $tanggalBekerja = tanggalnormal($res[0]['mulaibekerja']);
        $alasan = $res[0]['alasan'];
        $alasanganti = $res[0]['alasanganti'];
        $uraiankerja = $res[0]['uraiankerja'];
        $kualifikasi = $res[0]['kualifikasi'];
        $jeniskelamin = $res[0]['jeniskelamin'];
        $statuspernikahan = $res[0]['statuspernikahan'];
        $pendidikanminimal = $res[0]['pendidikanminimal'];
        $pengalamanminimal = $res[0]['pengalamanminimal'];
        $bidangpengalaman = $res[0]['bidangpengalaman'];
        $sertifikasi = $res[0]['sertifikasi'];
        $note = $res[0]['note'];
        $createby = $res[0]['createby'];

        $usiamax = $res[0]['usiamax'];
        $usiamin = $res[0]['usiamin'];


        $jenistes = json_decode($res[0]['jenistes']);
        $jenisinterview = json_decode($res[0]['jenisinterview']);

        $arrHead    = setheadreport(getindukPT($lokasikerja));
        $path       = $arrHead['logo'];

        $stream = "<table width='100%' style='border:1px solid black;' cellpadding='4' cellspacing='0'>";
            $stream .= "<tr style='text-align:center;'>";
                $stream .= "<td style='border:1px solid black;'>
                                <img src='".$path."' width='50' height='50' />
                            </td>";
                $stream .= "<td style='font-weight:bold;font-size:26    px;border:1px solid black;' width='80%'>".$_SESSION['org']['namaorganisasi']."</td>";
            $stream .= "</tr>";
            $stream .= "<tr style='text-align:center;'>";
                $stream .= "<td style='font-size:22px;font-weight:700;border:1px solid black;' colspan='2'>Form Permintaan Karyawan</td>";
            $stream .= "</tr>";
        $stream .= "</table>";

        $stream .= "<table border='1' cellpadding='4' cellspacing='0' style='width:100%;margin-top:10px;font-size:12px;border-collapse:collapse;'>";
            $stream .= "<tr>";
                $stream .= "<td style='border-right:1px solid black;border-bottom:none;'>1. Bagian/Divisi/Seksi  : ". $departemen ."</td>";
                $stream .= "<td style='border-bottom:none;'>3. Tanggal Permintaan   : ".$tanggalDibuat ."</td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td style='border-right:1px solid black;border-bottom:none;border-top:none;'>2. Posisi yang dibutuhkan : ".$jabatan."</td>";
                $stream .= "<td style='border-bottom:none;border-top:none;'>4. Rencana tgl penempatan : ". $tanggalBekerja ."</td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td colspan='2'>
                               <table width='60%' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td colspan='2'>5. Status Permintaan</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($alasan == "Penambahan sesuai MPP" ? "checked" : "") . ">
                                            <span>Penambahan Sesuai MPP</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($alasan == "Replacement" ? "checked" : "") . ">
                                            <span>Replacement, menggantikan:</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($alasan == "Penambahan diluar MPP" ? "checked" : "") . ">
                                            <span>Penambahan diluar MPP</span>
                                        </td>
                                        <td>
                                            <span style='margin-left:17px;'>Alasan : ". ($alasanganti != "" ? $alasanganti : "") ."</span>
                                        </td>
                                    </tr>
                               </table>
                            </td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td colspan='2' style='height:80px;vertical-align:top;'>
                                6. Uraian singkat Tugas dan Tanggung Jawab (Accountability) <span>**</span> :
                                <p>
                                    ". $uraiankerja ."
                                </p>
                            </td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td colspan='2' style='height:40px;vertical-align:top;'>
                                Notes :
                                <p style='margin:0'>". $note ."</p>
                            </td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td style='height:40px;vertical-align:top;'>
                                <table width='100%' style='margin:5px 0;' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td colspan='2'>7. Jenis Kelamin</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($jeniskelamin == "L" ? "checked" : "") .">
                                            <span>Laki-laki</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($jeniskelamin == "P" ? "checked" : "") .">
                                            <span>Perempuan</span>
                                        </td>
                                    </tr>
                                    <tr style='margin-top:30px;'>
                                         <td colspan='2' style='padding-top:15px;'>
                                            8. Usia: ".$usiamin." s/d ".$usiamax."
                                            Tahun
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='2' style='padding-top:15px;'>9. Status Pernikahan</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($statuspernikahan == "single" ? "checked" : "") .">
                                            <span>Belum Menikah</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($statuspernikahan == "menikah" ? "checked" : "")  .">
                                            <span>Menikah</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($statuspernikahan == "duda" ? "checked" : "") .">
                                            <span>Duda</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' " . ($statuspernikahan == "janda" ? "checked" : "") .">
                                            <span>Janda</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='2' style='padding-top:15px;'>10. Kualifikasi Pendidikan</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($pendidikanminimal == "SMP" ? "checked" : "") .">
                                            <span>SMP</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($pendidikanminimal == "S1" ? "checked" : "") .">
                                            <span>S1</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($pendidikanminimal == "SMA/SMK" ? "checked" : "") .">
                                            <span>SMA/SMK</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px; ". ($pendidikanminimal == "S2" ? "checked" : "") ."'>
                                            <span>S2</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($pendidikanminimal == "D1/D2/D3" ? "checked" : "") .">
                                            <span>D1/D2/D3</span>
                                        </td>
                                        <td>
                                            <input type='checkbox' style='margin-top:7px;' ". ($pendidikanminimal == "S4" ? "checked" : "") .">
                                            <span>S3</span>
                                        </td>
                                    </tr>
                                    <tr style='display:none;'>
                                        <td style='padding-top:15px;width:30px;'>Fakultas/Jurusan:</td>
                                        <td style='border-bottom:1px solid black;'></td>
                                    </tr>
                                </table>
                            </td>";
                $stream .= "<td style='height:40px;vertical-align:top;'>
                                <table>
                                    <tr>
                                        <td style='padding-top:10px;'>
                                            11. Pengalaman di bidang:
                                            <p style='margin:0 18px;'> ". $bidangpengalaman ."</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding-top:10px;'>
                                            12. Minimal pengalaman:
                                            <p style='margin:0 18px;'> ". $pengalamanminimal ."</p>
                                        </td>
                                    </tr>
                                    <tr>    
                                        <td style='padding-top:10px;height:120px;vertical-align:top;'>
                                            13. Keterampilan khusus yang dibutuhkan:
                                            <p style='margin:0 18px;'> ". $kualifikasi ."</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            14. Sertifikasi/Keahlian Khusus/Lain-lain:
                                            <p style='margin:0 18px;'> ". $sertifikasi ."</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td colspan='2' style='background-color:#f2f2f2;'>
                                <table width='100%'>
                                    <tr>
                                        <td colspan='2' style='padding-top:10px;'>Jenis tes yang diberikan:</td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'>
                                            <table width='100%' cellpadding='0' cellspacing='0'>
                                                <tr>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Psikotes", $jenistes) ? "checked" : "") .">
                                                        Psikotes
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Tes Bahasa Indonesia", $jenistes) ? "checked" : "") .">
                                                        Tes Bahasa Indonesia
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Tes Lain-lain", $jenistes) ? "checked" : "") .">
                                                        Tes Lain-lain
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style='width:130px;'>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Tes Bahasa Inggris", $jenistes) ? "checked" : "") .">
                                                        Tes Bahasa Inggris
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Tes Komputer / Mengetik", $jenistes) ? "checked" : "") .">
                                                        Tes Komputer / Mengetik
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan='2' style='padding-top:10px;'>Jenis Interview yang digunakan:</td>
                                    </tr>
                                    <tr>
                                        <td colspan='2'>
                                            <table width='100%' cellpadding='0' cellspacing='0'>
                                                <tr>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Exploratory Interview", $jenisinterview) ? "checked" : "") .">
                                                        Exploratory Interview
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("User / Teknikal Interview", $jenisinterview) ? "checked" : "") .">
                                                        User / Teknikal Interview 
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Panel Interview", $jenisinterview) ? "checked" : "") .">
                                                        Panel Interview 
                                                    </td>
                                                    <td>
                                                        <input type='checkbox' style='margin-top:7px;' ". (in_array("Management Interview", $jenisinterview) ? "checked" : "") .">
                                                        Management Interview
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>";
            $stream .= "</tr>";
        $stream .= "</table>";

        $qApprv = selectQuery($dbname, "approval", "*", "notransaksi='".$notransaksi."' and jenispersetujuan='ERF' and status='1'");
        $resApprv = fetchData($qApprv);

        $stream .= "<table width='100%' style='margin:10px 0;font-size:12px;' border='1' cellpadding='0' cellspacing='0'>";
            $stream .= "<tr>";
                $stream .= "<td style='text-align:center;'>
                                Diajukan oleh,
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                Diketahui oleh,
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                Disetujui oleh,
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                Diterima oleh,
                            </td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td style='height:70px;'>&nbsp;</td>";
                $stream .= "<td style='height:70px;'>&nbsp;</td>";
                $stream .= "<td style='height:70px;'>&nbsp;</td>";
                $stream .= "<td style='height:70px;'>&nbsp;</td>";
            $stream .= "</tr>";
            $stream .= "<tr>";
                $stream .= "<td style='text-align:center;'>
                                ". getNamaKaryawan($createby) ."
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                ". getNamaKaryawan($resApprv[0]['karyawanid']) ."
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                ". getNamaKaryawan($resApprv[1]['karyawanid']) ."
                            </td>";
                $stream .= "<td style='text-align:center;'>
                                ". getNamaKaryawan($resApprv[2]['karyawanid']) ."
                            </td>";
            $stream .= "</tr>";
        $stream .= "</table>";
        $dompdf = new Dompdf();
        $dompdf->loadHtml($stream);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $dompdf->stream("Permintaan Pekerjaan",array("Attachment"=>0));
    break;
    default:
}

function getNamaDepartemen($kode)
{
    global $dbname;

    $qDepartemen = selectQuery($dbname, 'sdm_5departemen', 'kode,nama', "kode = '".$kode."'");
    $resDepartemen = fetchData($qDepartemen);

    return $resDepartemen[0]['nama'];
}
?>
<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

$proses=checkPostGet('proses','');
$param = $_POST;

function gantierror($tulisan){
    $hasil=$tulisan;
    $hasil=str_ireplace('error','eror',$hasil);
    $hasil=str_ireplace('warning','wrning',$hasil);
    $hasil=str_ireplace('gagal','ggal',$hasil);
    return $hasil;
}

$str="select * from ".$dbname.".setup_filesize where transaksi='keu_kasbank'";
$res=fetchdata($str);
foreach($res as $bar){
	$filesize=$bar['filesize'];
}


$path   = "fileupload/keu_kasbankx/";
$emodul = "KB";
switch($proses) {
	
	case'persetujuan':
        #ditambahkan pengecekan dari posting
        #=== Get Data ===
        # Header
        $queryH = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".
            $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
            "' and noakun='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."' limit 1");
        $dataH = fetchData($queryH);

        # Detail
        $queryD = selectQuery($dbname,'keu_kasbankdt',"*","notransaksi='".
            $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
            "' and noakun2a='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'");
        $dataD = fetchData($queryD);


        #=== Cek Jumlah Detail dan Header harus sama ===
        $tmpJml = 0;
        foreach($dataD as $row) {
            $tmpJml += $row['jumlah'];
        }
        $selisih = abs($tmpJml - $dataH[0]['jumlah']);
        if($selisih > 0.01) {
            echo "Warning : Amount on header difference to the amount in detail\n";
            echo "Posting Failed";
            exit;
        }

        #=== Cek if posted ===
        $error0 = "";
        if($dataH[0]['posting']==1) {
            $error0 .= $_SESSION['lang']['errisposted'];
        }
        if($error0!='') {
            echo "Data Error :\n".$error0;
            exit;
        }
        //manupulasi tanggal menjadi tanggal input
        $dataH[0]['tanggal']=tanggalsystem($param['tglpost']);
        #====cek periode
        $tgl = str_replace("-","",$dataH[0]['tanggal']);
        if($_SESSION['org']['period']['start']>$tgl)
            exit('Error:Date beyond active period');

        #=== Cek if data not exist ===
        $error1 = "";
        if(count($dataH)==0) {
            $error1 .= $_SESSION['lang']['errheadernotexist']."\n";
        }
        if(count($dataD)==0) {
            $error1 .= $_SESSION['lang']['errdetailnotexist']."\n";
        }
        if($error1!='') {
            echo "Data Error :\n".$error1;
            exit;
        }
        #=======cek kurs mata uang header dan detail
        $ceko=0;
        foreach($dataD as $rowdt=>$isiData){
            if($dataH[0]['matauang']!=$isiData['matauang']){
                $ceko+=1;
            }
        }
        if($ceko!=0){
            exit('warning: Matauang header dan detail berbeda!!');
        }
		
		
		/*
        #=== Cek if hutang unit ========================================================
        if($dataH[0]['hutangunit']==1){
            $pembayarhutang=$param['kodeorg'];    
            $pemilikhutang=$dataH[0]['pemilikhutang'];
            #cek jika pemilik hutang dengan kodeorg pemilik akun piutang sama atau tidak
            $rwError=0;
            $sCek="select distinct noakun from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and hutangunit1=1  and kodeorg='".$param['kodeorg'].
            "' and noakun like '1210%' and tipetransaksi='".$param['tipetransaksi']."'";
            $qCek=$owlPDO->query($sCek);
            $qCek->setFetchMode(PDO::FETCH_ASSOC);
            while($rCek=$qCek->fetch()){
                // $whrdt="akunpiutang='".$rCek['noakun']."'";
                // $optCek=makeOption($dbname,'keu_5caco','akunpiutang,kodeorg',$whrdt);
                $whrdt="kodeorg='".$pemilikhutang."'";
                $optCek=makeOption($dbname,'keu_5caco','kodeorg,akunpiutang',$whrdt);
                if($optCek[$pemilikhutang]!=$rCek['noakun']){
                    $rwError+=1;
                    $dtAkun[$rCek['noakun']]=$rCek['noakun'];
                }
            }

            if($rwError!=0){
                // echo"<pre>";
                // print_r($dtAkun);
                // echo"</pre>";
                exit('warning: Noakun diatas bukan milik '.$pemilikhutang);
            }
            // kalo periode akuntansi unit beda, ga bisa diposting...
            //$periodepembayar=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg = '".$pembayarhutang."' and tutupbuku = 0");
            $periodepemilik=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg = '".$pemilikhutang."' and tutupbuku = 0");
            $tglMulaiPemilik=str_replace("-", "", $periodepemilik[$pemilikhutang]);
            $tglPosting=tanggalsystem($param['tglpost']);
            if($tglMulaiPemilik>$tglPosting){
                echo "Warning : ".$_SESSION['lang']['tanggal']." < ".$_SESSION['lang']['periodeakuntansi']." ".$pemilikhutang.", ".$_SESSION['lang']['tanggalmulai']." ".$periodepemilik[$pembayarhutang];
                exit;
            }
        }else{
            #cek jika detail ada hutang unit tetapi headernya belum tercentang
            $sCek="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and hutangunit1=1  and kodeorg='".$param['kodeorg'].
            "' and noakun2a='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'";
            $qCek=$owlPDO->query($sCek);
            $rCek=owlBaris($qCek);
            if($rCek>0){
                exit('warning: Hutang unit pada form header belum tersimpanzzzzzzzzz.');
            }
        }
		*/
		$listpersetujuan=$_POST['persetujuan'];
		if(count($listpersetujuan) <= 0){
			exit("Warning : Approval masih belum ada. Silahkan hubungi Administrator");
		}
		
		for($i=1;$i<=count($listpersetujuan);$i++){
			if($_POST['persetujuan'][$i]==''){
				exit("Warning: Persetujuan ".$i." belum dipilih.");
			}
		}
		
        #= delete 1st untuk aprovalnya
        $str = "delete from " . $dbname . ".approval where notransaksi='".$param['notransaksi']."' and jenispersetujuan='KASBANK'";
        try{
            $owlPDO->exec($str); 
        }catch (PDOException $e){
            
        }
        
        
        #= bentuk no voucer baru auto
        $whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
        $queryAKB = selectQuery($dbname,'keu_5parameterjurnal',
                'jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',$whereAKB);
        $optAKB = fetchData($queryAKB);
        $tipe = "";
        foreach($optAKB as $row) {
                if($param['tipetransaksi']=='K') {
                        if($param['noakun']>=$row['noakunkredit'] and $param['noakun']<=$row['sampaikredit']) {
                                $tipe = $row['jurnalid'];
                        }
                        } else {
                        if($param['noakun']>=$row['noakundebet'] and $param['noakun']<=$row['sampaidebet']) {
                                $tipe = $row['jurnalid'];
                        }
                }
        }

        // Get Last Transaction
        $noTrans = tanggalsystem($param['tglpost'])."/".$param['kodeorg']."/".$tipe."/";
        $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',
                                                  "notransaksi like '".$noTrans."%'","notransaksi desc",true,1,1);
        $resTrans = fetchData($qTrans);
        if(empty($resTrans)) {
                $param['novoucher'] = $noTrans."00001";
        } else {
                $tmpTrans = substr($resTrans[0]['notransaksi'],17,5);
                $tmpTrans++;
                $param['novoucher'] = $noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
        }
        
        
        #= pindahin insert tanggal dan novoucher dan update flag ke posting=9 (proses pengajuan)
		
		#= bentuk nomor voucher saat pembayaran
		$param['novoucher']='';
		
        $str = "update " . $dbname . ".keu_kasbankht set posting=9,tanggalpengajuan='".tanggalsystemn($param['tglpost'])."',novoucher='".$param['novoucher']."'
                where notransaksi='".$param['notransaksi']."'";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    
        for($i=1;$i<=$param['maxaproval'];$i++){
            #= insert
            $str = "insert into " . $dbname . ".approval(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                   values('".$param['notransaksi']."','KASBANK','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";
            try{
                $owlPDO->exec($str); 
            }catch(PDOException $e){
                $str = "update " . $dbname . ".keu_kasbankht set posting=0,tanggalpengajuan='0000-00-00',novoucher=''
                where notransaksi='".$param['notransaksi']."'";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
                echo " Gagal," . addslashes($e->getMessage());
            }
        }
	break;
	
	
	
	
	case'showform':
    $tglPosting='';
    $dtNovoucher='';
        #ambil dt
		$sData="select sum(jumlah) as jumlahdt from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
        $rDataDt=fetchData($sData);
		$jlhdt = $rDataDt[0]['jumlahdt'];
		
		#ambil ht
		$sData="select sum(jumlah) as jumlahht from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
        $rDataHt=fetchData($sData);
		$jlhht = $rDataHt[0]['jumlahht'];
		
		$banlances=$jlhdt-$jlhht;
		
		#ambil novouceher dan tanggal jika pernah, jika belum ambil data bukti bayar jika kosong ambil notransaksi
        #$sData="select sum(debet) as debets , sum(kredit) as kredits from ".$dbname.".keu_kasbankdt_vw where notransaksi='".$param['notransaksi']."' group by notransaksi";
        #$rData=fetchData($sData);
        #$banlances=$rData[0]['debets']+$rData[0]['kredits'];
		
		#= update nilai selisih 0.0025 => 20180424/TJHO/BK/00001
		#= mesti dibulatkan sehingga menjadi dianggap 0.00
		// $banlances=abs(number_format($banlances,2));
  //       if($banlances!=0){
  //           exit('Warning : Balance must 0'); #gua pindah saat save persetujuan
  //       }
        $sData="select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
        $rData=fetchData($sData);
        if(($rData[0]['tanggal']!='')&&($rData[0]['tanggal']!='0000-00-00')){
            $tglPosting=tanggalnormal($rData[0]['tanggal']);
        }
		/*
        if($rData[0]['novoucher']!=''){
            $dtNovoucher=$rData[0]['novoucher'];
        }else if($rData[0]['novoucher']==''){
            if($rData[0]['nocek']==''){
                $dtNovoucher=$rData[0]['nocek'];
            }else{
                $dtNovoucher=$rData[0]['notransaksi'];
            }
        }
		*/
		
		$str="select * from ".$dbname.".filemanager where namafile='".$param['notransaksi']."'";
		$res=fetchdata($str);
		if(count($res) > 0){
			$showiconefil = "display:none";
		}else{
			$showiconefil = "display:none";
		}
		
		$countApp = getCountApproval('KASBANK',$param['kodeorg']);
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
			for($i=1;$i<=$countApp;$i++){
				$arrList = listApprove($i,'KASBANK',$param['kodeorg']);
				$optpersetujuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				$arrDetail = detailApprove($i,$param['notransaksi'],'KASBANK');
				foreach($arrList as $key=>$val){
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
                //if($param['tipetransaksi']!='M'){
                    $tab.="<tr  class=rowcontent>
                    <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                    <td>:</td>
                    <td colspan=1><select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";    
                //}
				
			}   
			$tab.="<tr  class=rowcontent hidden>
				<td>".$_SESSION['lang']['novoucher']."</td> 
				<td>:</td>
				<td><input type=text id=novoucher onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='".$dtNovoucher."' /></td>
			</tr>
			<tr  class=rowcontent>
				<td>".$_SESSION['lang']['tanggal']."</td> 
				<td>:</td>
				<td>
					<input type=text class=myinputtext readonly  id=tglpost onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" value='".$tglPosting."'/>									
				</td>
			</tr>
			
			
			<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=savePosting('".$param['notransaksi']."','".$param['kodeorg']."','".$param['noakun']."','".$param['tipetransaksi']."','".$param['numRow']."','".$countApp."')>Simpan</button>
					<label style='color:blue;cursor:pointer;".$showiconefil."' onclick=viewefill('".$param['notransaksi']."','',event) title='View E-Fill'>View E-Fill</label>
				</td>
			</tr>
		</table>
		";
		
		//<tr class=rowcontent><td></td><td></td><td><button class=mybutton onclick=savePosting('".$param['notransaksi']."','".$param['kodeorg']."','".$param['noakun']."','".$param['tipetransaksi']."','".$param['numRow']."','".$countApp."')>Simpan</button></td></tr>
		
				
		echo $tab;
	
	break;
	case'showformbayar'://indra
	
		
	
		$whereJam=" noakun='".$param['noakun']."'";
		// $optAkun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".keu_5akun where ".$whereJam." order by noakun asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($_SESSION['language']=='EN'){
				$optAkun.="<option value='".$bar['noakun']."'>".$bar['namaakun1']."</option>";
			}else{
				$optAkun.="<option value='".$bar['noakun']."'>".$bar['namaakun']."</option>";
			}
		}
		
		$optbank.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if($param['noakun']=='1110101' or $param['noakun']=='1111101') {  
            $whr=""; 
            if ($_POST['noakun2a']=='1111101') {
                $whr=" and matauang!='IDR'";
            }else{
                $whr=" and matauang='IDR'";
            }
            // $optbank="";
            $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$_POST['kodeorg']."' ".$whr;
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $wheredz =" kodebank='".$bar['namabank']."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                $optbank.="<option value='".$bar['noakun']."'>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
            }
        }

	
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
			$tab.="<tr  class=rowcontent>
				<td>".$_SESSION['lang']['tanggalbayar']."</td> 
				<td>:</td>
				<td>
					<input type=text class=myinputtext readonly  id=tglbayar onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\"/>									
				</td>
			</tr>";
			$tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['kodeorg']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly  id=kodeorg value='".$param['kodeorg']."' disabled style=\"width:150px;\"/>									
			</td></tr>";
			
			$tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['noakun']."</td>
					<td>:</td>		
					<td>
						<select id=noakun2a  style=\"width:155px;\" onchange=getbank()>'".$optAkun."'</select>
					</td></tr>";
					
				$tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['rekening']."</td>
					<td>:</td>		
					<td>
						<select id=rekening  style=\"width:155px;\">'".$optbank."'</select>
					</td></tr>";	
					
				
						$optctg='';
						$arrctg = getEnum($dbname,'keu_kasbankht','cgttu');
						foreach($arrctg as $kei=>$fal){
							$optctg.="<option value='".$kei."'>".$fal."</option>";
						}  					
					
				$tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['cgttu']."</td>
					<td>:</td>		
					<td>
						<select id=cgttu onchange=getbuktibayar() style=\"width:155px;\">'".$optctg."'</select>
					</td></tr>";	
					
					
						$tab.="<tr  class=rowcontent><td>".$_SESSION['lang']['BuktiPembayaran']."</td>
					<td>:</td>		
					<td>
						<input type=text class=myinputtext readonly  id=nocek disabled style=\"width:150px;\"/>									
			</td></tr>";


					
					#= indra
			
			
			/*
			 $els[] = array(
        makeElement('cgttu','label',$_SESSION['lang']['cgttu']),
        makeElement('cgttu','select',$data['cgttu'],array('style'=>'width:155px','onchange'=>'getbuktibayar()'),$optCgt)
    );
			*/
			
			$optefill = makeOption($dbname,'filemanager','namafile,id',"namafile='".$param['notransaksi']."'");
			@$idefill = $optefill[$param['notransaksi']];
			
			$showhide="style='display:none'";
			$efill='0';
			if($idefill!=''){
				$showhide="style='display:'";
				$efill='1';
			}
			
			$tab.="<tr class=rowcontent ".$showhide.">
				<td>File Bukti Pembayaran</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>";
			
			$tab.="<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=kasbank('".$param['notransaksi']."','".$param['kodeorg']."','".$param['noakun']."','".$param['tipetransaksi']."','".@$param['novoucher']."','".$param['numRow']."','".$efill."')>Simpan</button>
				</td>
			</tr>
		</table>
		";
		echo $tab;
	break;
	
    # Daftar Header
    case 'showHeadList':
	
		// echo"<pre>";
		// print_r($param);
		// echo"<pre>";
	
	
		// $where = "kodeorg in (".getOrgDetail(2).")";
		
		/*
		if($_SESSION['empl']['tipelokasitugas']=='KEBUN' || $_SESSION['empl']['tipelokasitugas']=='PABRIK'){
			$where = "kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		}else{
			$where = "kodeorg in (".getOrgDetail(2).")";
		}
		*/
		
		$where=" 1=1 ";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			 $where.= " and kodeorg in (".getOrgDetail(2).")";
        }else{
          $where.= "and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        }

		/*
        if(isset($param['where'])) {
            $arrWhere = json_decode(str_replace('\\','',$param['where']),true);
            if(!empty($arrWhere)) {
                foreach($arrWhere as $key=>$r1) {
                    if($r1[0]=='tanggal')
                    {
                        $tanggal1 = $r1[1];
                    } 
                    elseif($r1[0]=='tanggal2') 
                    {
                        $tanggal2 = $r1[1];
                    }
					else if ($r1[0]=='supplier'){
						if($r1[1]!=''){
						// #= cari transaksi yang ada kode suppliernya
						// $str = "select notransaksi from ".$dbname.".keu_kasbankdt where kodesupplier='".$r1[1]."'";
						// $res = $owlPDO->query($str);
						// $res->setFetchMode(PDO::FETCH_ASSOC);
						// while($bar=$res->fetch()){
							
						// }
						
						 $where .= " and notransaksi in (select notransaksi from ".$dbname.".keu_kasbankdt where kodesupplier='".$r1[1]."')";
						}
						
					}
                    else
                    {
                        $where .= " and ".$r1[0]." like '%".$r1[1]."%'";
                    }
                }
				if(!empty($tanggal1) and !empty($tanggal2)) {
						$where.=" and tanggalinput between '".$tanggal1."' and '".$tanggal2."' ";
				} elseif(!empty($tanggal1)) {
						$where.=" and tanggalinput >= '".$tanggal1."'";
				} elseif(!empty($tanggal2)) {
						$where.=" and tanggalinput <= '".$tanggal2."'";
				}
            }
        }
		*/
		

		
		
		if($param['posting']!=''){
			$where.=" and posting='".$param['posting']."'";
		}
		if($param['notransaksi']!=''){
			$where.=" and notransaksi like '%".$param['notransaksi']."%'";
		}
		if($param['noakun']!=''){
			$where.=" and noakun='".$param['noakun']."'";
		}
		if($param['jumlah']!=''){
			$where.=" and jumlah like '%".$param['jumlah']."%'";
		}
		if($param['tipetransaksi']!=''){
			$where.=" and tipetransaksi='".$param['tipetransaksi']."'";
		}
		if($param['bayarkepada']!=''){
			$where.=" and bayarkepada like '%".$param['bayarkepada']."%'";
		}
        if($param['keterangan']!=''){
			$where.=" and keterangan like '%".$param['keterangan']."%'";
		}
        if($param['keterangan1']!=''){
			$where.=" and notransaksi in (select distinct notransaksi from ".$dbname.".keu_kasbankdt where keterangan1 like '%".$param['keterangan1']."%')";
		}
		if($param['kodesupplier']!=''){
			 $where .= " and notransaksi in (select notransaksi from ".$dbname.".keu_kasbankdt where kodesupplier='".$param['kodesupplier']."')";
		}
		
		
		#= khusus tanggal
		if($param['tanggalinput1']!='' and $param['tanggalinput2']!=''){
			 $where.=" and tanggalinput between '".tanggalsystemn($param['tanggalinput1'])."' and '".tanggalsystemn($param['tanggalinput2'])."' ";
		}
		if($param['tanggalinput1']=='' and $param['tanggalinput2']!=''){
			  $where.=" and tanggalinput <= '".tanggalsystemn($param['tanggalinput2'])."'";
		}
		if($param['tanggalinput1']!='' and $param['tanggalinput2']==''){
			 $where.=" and tanggalinput >= '".tanggalsystemn($param['tanggalinput1'])."'";
		}
		
	
		
				// echo"<pre>";
		// print_r($param);
		// echo $where;
		// exit();
		
		$optposting=array(''=>$_SESSION['lang']['pilihdata'],'0'=>'Belum Diajukan','1'=>'Disetujui','3'=>'Ditolak','9'=>'Proses Persetujuan');
        # Header & Align
        $header = array(
                        $_SESSION['lang']['notransaksi'],$_SESSION['lang']['unitkerja'],
                        $_SESSION['lang']['tanggalinput'],$_SESSION['lang']['noakun'],
                        $_SESSION['lang']['tipe'],$_SESSION['lang']['matauang'],
                        $_SESSION['lang']['jumlah'],'Balance','Bayar Kepada',$_SESSION['lang']['remark'],$_SESSION['lang']['posting'],$_SESSION['lang']['updateby']
                );
                $align = explode(',','C,L,C,L,C,C,R,C,C');

        # Content
        $cols = "notransaksi,kodeorg,tanggalinput,noakun,tipetransaksi,matauang,jumlah,'balan',bayarkepada,keterangan,posting,userid";
        $query = selectQuery($dbname,'keu_kasbankht',$cols,$where,
            "tanggalinput desc, notransaksi desc",false,$param['shows'],$param['page']);
        // echo $query;
        $data = fetchData($query);
        $totalRow = getTotalRow($dbname,'keu_kasbankht',$where);
        $whereAkun="";$whereOrg="";$i=0;
        foreach($data as $key=>$row) {
			$optefill = makeOption($dbname,'filemanager','namafile,id',"namafile='".$row['notransaksi']."'");
			$idefill = $optefill[$row['notransaksi']];
			
			if($idefill==''){
				$data[$key]['noSwitchList'][]="detailefill";
			}
			
			/*
			#ambil level terakhir persetujuan
			$str=" select max(level) as level from ".$dbname.".setup_approval where kodeunit='".$row['kodeorg']."' and jenispersetujuan='KASBANK'";
			$level = fetchData($str);

			#apakah sudah disetujui atau belum
			$str=" select count(*) as setuju from ".$dbname.".approval where notransaksi='".$row['notransaksi']."' and jenispersetujuan='KASBANK' and level='".$level[0]['level']."' and status='1'";
			
			$setuju = fetchData($str);

			#apakah sudah ada jurnal
			$queryJ = selectQuery($dbname,'keu_jurnaldt_vw',"*","noreferensi='".$row['notransaksi']."' and nodok not in (select notransaksi from ".$dbname.".keu_kasbankdt where kodeorg='".$row['kodeorg']."' )");
			// exit('warning : '.$queryJ);
            $dataJ = fetchData($queryJ);
			
			if($setuju[0]['setuju']=='0' or count($dataJ) > 0 ){
				$data[$key]['noSwitchList'][]="bayar";
			}
			
			*/
			
			if($row['posting']==1) {
                $data[$key]['switched']=true;
				$data[$key]['noSwitchList'][]="showEdit";
				$data[$key]['noSwitchList'][]="deleteData";
            } else if ($row['posting']==9){
				#= ajukan
				$data[$key]['noSwitchList'][]="showEdit";
				$data[$key]['noSwitchList'][]="deleteData";
				$data[$key]['noSwitchList'][]="postingData";//hilangkan tombol jika masuk aproval
				 // $data[$key]['postingData'] = 'A';
			} else {
				#= jika posting=0 (masih dibuat) atau 3 (ditolak)
				// if($_SESSION['standard']['userid']==$row['userid']){
				// } else {
				// 	$data[$key]['noSwitchList'][]="showEdit";
				// 	$data[$key]['noSwitchList'][]="deleteData";
				// }
			}
            $data[$key]['tanggalinput'] = tanggalnormal($row['tanggalinput']);
			if($data[$key]['posting']==3){
				$data[$key]['posting'] = "<font color=red><b>".$optposting[$row['posting']]."<font>";
			}else if($data[$key]['posting']==1){
				$data[$key]['posting'] = "<font color=green><b>".$optposting[$row['posting']]."<font>";
			}else{
				$data[$key]['posting'] = $optposting[$row['posting']];
			}
            # Build Condition
            if($i==0) {
              $whereAkun.="noakun='".$row['noakun']."'";
              $whereOrg.="kodeorganisasi='".$row['kodeorg']."'";
            } else {
              $whereAkun.=" or noakun='".$row['noakun']."'";
              $whereOrg.=" or kodeorganisasi='".$row['kodeorg']."'";
            }
            $i++;
        }


        # Posting --> Jabatan
        $postJabatan = getPostingJabatan('keuangan');

        # Options
        if($_SESSION['language']=='EN'){
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereAkun);
        }else{
            $optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
        }

        $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whereOrg);

        # Mask Data Show
        $dataShow = $data;
        foreach($dataShow as $key=>$row) {
			$optNamaKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$row['userid']."'");
            $dataShow[$key]['userid'] = $optNamaKary[$row['userid']];
            $dataShow[$key]['jumlah'] = number_format($row['jumlah'],2);
            $dataShow[$key]['noakun'] = $optAkun[$row['noakun']];
            $dataShow[$key]['kodeorg'] = $optOrg[$row['kodeorg']];
            #=====================tambahan ginting sebagai pembalance
            $str=$owlPDO->query("select sum(jumlah) as jumlah from ".$dbname.".keu_kasbankdt 
                  where notransaksi='".$data[$key]['notransaksi']."' 
                  and kodeorg='".$data[$key]['kodeorg']."' 
                  and tipetransaksi='".$data[$key]['tipetransaksi']."'
                  and noakun2a='".$data[$key]['noakun']."'");
            $str->setFetchMode(PDO::FETCH_OBJ);
            $bar=$str->fetch();
            $balan=0;
            $balan=$bar->jumlah;
            $balan=$balan-$row['jumlah'];
            #==================================
            $dataShow[$key]['balan'] = number_format($balan,2);
        }

        # Make Table
        $tHeader = new rTable('headTable','headTableBody',$header,$data,$dataShow);
        $tHeader->addAction('showEdit','Edit','images/'.$_SESSION['theme']."/edit.png");
        $tHeader->addAction('deleteData','Delete','images/'.$_SESSION['theme']."/delete.png");
        $tHeader->addAction('postingData','Posting','images/'.$_SESSION['theme']."/posting.png");
        $tHeader->_actions[2]->setAltImg('images/'.$_SESSION['theme']."/posted.png");
        $tHeader->addAction('detailPDF','Print Voucher','images/'.$_SESSION['theme']."/pdf.jpg");
        if(!in_array($_SESSION['empl']['kodejabatan'],$postJabatan) and $_SESSION['empl']['tipelokasitugas']!='HOLDING') {
            $tHeader->_actions[2]->_name='';
        }
        $tHeader->_actions[3]->addAttr('event');
        $tHeader->pageSetting($param['page'],$totalRow,$param['shows']);
        $tHeader->addAction('tampilDetail','Print Data Detail','images/'.$_SESSION['theme']."/zoom.png");
        $tHeader->_actions[4]->addAttr('event');

        $tHeader->addAction('detailefill','E-Filling System','images/efill.png');
		$tHeader->_actions[5]->addAttr('event');    
		
		/*
		$tHeader->addAction('bayar','Bayar !!!',"images/bayar.png");
		$tHeader->_actions[6]->addAttr('event');
		*/

		// $tHeader->addAction('detailPDF3','Print Voucher','images/'.$_SESSION['theme']."/pdf.jpg");
		//$tHeader->_actions[6]->addAttr('event');   	

		// $tHeader->_switchException = array('detailPDF','detailPDF2','detailPDF3','tampilDetail','detailefill','bayar');
		$tHeader->_switchException = array('detailPDF','detailPDF2','detailPDF3','tampilDetail','detailefill');
        if(isset($param['where'])) {
            $tHeader->setWhere($arrWhere);
        }
        $tHeader->setAlign($align);

        # View
        $tHeader->renderTable();
        break;
    # Form Add Header
    case 'showAdd':
        // View
        list($x,$y) =  formHeader('add',array());
        echo $x;
        echo $y;
        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Form Edit Header
    case 'showEdit':

        ## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
        $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
        $res = $owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $rekpinjam=explode(',',$bar['nilai']);
        foreach($rekpinjam as $key){
            $arrpinjam[$key]=$key;
        }

        $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
        $res = $owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $akpinjam=$bar['nilai'];
        if ($param['rekening']!='') {
            if(in_array($param['rekening'],$arrpinjam)){
                $param['noakun']=$akpinjam;
            }
        }

        

        $query = selectQuery($dbname,'keu_kasbankht',"*","notransaksi='".
            $param['notransaksi']."' and kodeorg='".$param['kodeorg'].
            "' and noakun='".$param['noakun']."' and tipetransaksi='".
            $param['tipetransaksi']."'");
        $tmpData = fetchData($query);
        $data = $tmpData[0];
        $data['tanggalinput'] = tanggalnormal($data['tanggalinput']);

        if ($data['noakun']==$akpinjam) {
            $data['noakun']='1110101';
        }

        list($x,$y) =  formHeader('edit',$data);
        echo $x;
        echo $y;

        echo "<div id='detailField' style='clear:both'></div>";
        break;
    # Proses Add Header
    case 'add':
        $data = $_POST;
		
		$data['keterangan']=gantierror($data['keterangan']);
		// echo"<pre>";
		// print_r($data);
		// echo"</pre>";
		// exit("Error:");
		
		if($param['tipetransaksi']==''){
			exit("Warning:Tipe Transaksi Masinh Kosong");
		}
        if($data['kurs'] == 0 || $data['kurs'] == ''){
            exit("Warning: Nilai kurs harus diisi (tidak boleh kosong).");
        }
        if(($data['hutangunit']==1)and($data['pemilikhutang']=='' or $data['noakunhutang']=='')){
            exit("Warning: Please complete the form.");
        }
        else if($data['hutangunit']==''){
            $data['hutangunit']=0;
        }
		
		if($data['bayarkepada']==''){
            exit("Warning: Bayar ke / Masuk ke");
        }
			
	
        // Error Trap
        $warning = "";
        //if($data['notransaksi']=='') {$warning .= "Transaction number is obligatory\n";}
        if($data['tanggalinput']=='') {$warning .= "Date is obligatory\n";}
        if($data['noakun']=='') {$warning .= "Account is obligatory\n";}
        if($warning!=''){echo "Warning :\n".$warning;exit;}

        #mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
        $sekarang=  tanggalsystem($data['tanggalinput']);
        if($sekarang<$_SESSION['org']['period']['start']){
            echo "Validation Error : Date out or range";
            break;                        
        }
		
		if($param['tipetransaksi']=='K'){
			validasiInput(substr($data['kodeorg'],0,4),'','KB',tanggalsystemn($data['tanggalinput']),$exit='1');
		}
		
        #======================================================
        // Get Tipe Transaksi (Bank / Kas)
        $whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
        $queryAKB = selectQuery($dbname,'keu_5parameterjurnal',
                'jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',$whereAKB);
        $optAKB = fetchData($queryAKB);
        $tipe = "";
        foreach($optAKB as $row) {
            if($param['tipetransaksi']=='K') {
                if($param['noakun']>=$row['noakunkredit'] and $param['noakun']<=$row['sampaikredit']) {
                    $tipe = $row['jurnalid'];
                }
            } else {
                if($param['noakun']>=$row['noakundebet'] and $param['noakun']<=$row['sampaidebet']) {
                    $tipe = $row['jurnalid'];
                }
            }
        }
		
		$noTrans ="/".$data['kodeorg']."/".$tipe."/";
		
		$qTrans = selectQuery($dbname,'keu_kasbankht','max(right(notransaksi,5)) as notransaksi',"notransaksi like '%".$noTrans."%' and tanggalinput like '".substr(tanggalsystemn($data['tanggalinput']),0,7)."%'","notransaksi desc");
		// exit("Error:$qTrans");
		$resTrans = fetchData($qTrans);
		#= dibentuk lagi agar tipe masuk
		
		if(empty($resTrans)) {
			$tmpTrans=1;
        } else {
            $tmpTrans = $resTrans[0]['notransaksi'];
            $tmpTrans++;
        }
		
		#= lakukan pengecekan untuk auto kas
		$data['notransaksi'] = tanggalsystem($data['tanggalinput']).$noTrans.str_pad($tmpTrans,5,'0',STR_PAD_LEFT);
		
		
		
        //cek notransaksi pada kasbankht
        $str=$owlPDO->query("select * from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows>0)
        {
            exit("Error: Dokumen dengan nomor yang sama sudah ada\nSilahkan buat no.baru");
        }

        ## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
        if ($data['rekening']!='') {
            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $rekpinjam=explode(',',$bar['nilai']);
            foreach($rekpinjam as $key){
                $arrpinjam[$key]=$key;
            }

            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $akpinjam=$bar['nilai'];

            if(in_array($data['rekening'],$arrpinjam)){
                $data['noakun']=$akpinjam;
            }
        }
        
        $data['tanggalinput'] = $data['tanggal'] = tanggalsystem($data['tanggalinput']);
        $data['jumlah'] = str_replace(',','',$data['jumlah']);
        $data['userid']=$data['createby'] = $_SESSION['standard']['userid'];
        $data['createtime'] = date('Y-m-d H:i');
		
		 #= cek kalau auto KB harus terisi rekenin penerima 
		 if($data['autokb']=='1'){
			 
			 if($data['tipetransaksi']=='M'){
				 exit("Warning:Fitur Autokas/bank hanya diperbolehkan untuk transaksi keluar saja");
			 }
			 
			 if($data['namapenerima']==''){
				 exit("Warning:Unit Penerima masih kosong");
			 }
			 
			 if($data['noakun2b']==''){
				 exit("Warning:Akun Penerima masih kosong");
			 }
			 if($data['noakun2b']=='1110101' or $data['noakun2b']=='1111101'){
				 if($data['norekpenerima']==''){
					 exit("Warning:Jika akun bank, maka rekening tidak boleh kosong");
				 }
			 }
		 }
		
		
		// $data['tanggal']="0000-00-00";#terisi saat posting berhasil
        $cols = array('notransaksi','noakun','tanggalinput','matauang','kurs','tipetransaksi','jumlah','cgttu','keterangan','yn','kodeorg',
            'nocek','hutangunit','pemilikhutang','noakunhutang','bayarkepada','rekening','namabank','norekpenerima','namapenerima',
			'noakun2','autokb','tanggal','userid','createby','createtime');
        $query = insertQuery($dbname,'keu_kasbankht',$data,$cols);
        // exit('warning : '.$query);
        try{
            $owlPDO->exec($query);

            $arrtipe=getEnum($dbname,'keu_bukucekht','tipe_buku');
            foreach($arrtipe as $kei=>$fal)
            {
                $arrdibayar[]=$kei;
            }

            if(in_array($data['cgttu'],$arrdibayar)){
                $str = "select notrans_cek from ".$dbname.".keu_bukucekht where noakun='".$data['rekening']."' and status='1' and tipe_buku='".$data['cgttu']."' order by right(notrans_cek,3) desc";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                $notrans_cek=$bar['notrans_cek'];

                $str = "insert into " . $dbname . ".keu_bukucekdt (notrans_cek,notransaksi,nocek,status_cek)
                    values ('".$notrans_cek."','".$data['notransaksi']."','".$data['nocek']."','1')";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }

            echo $data['notransaksi'];

        }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;
    # Proses Edit Header
    case 'edit':        
        $data = $_POST;
        $data['keterangan']=gantierror($data['keterangan']);

        $str = "select cgttu,nocek from ".$dbname.".keu_kasbankht where notransaksi='".$data['notransaksi']."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        if($bar['cgttu']==$data['cgttu']){
            $data['nocek']=$bar['nocek'];
        }

        ## Jika Bank yang dipilih adalah bank pinjaman, maka akun menjadi 2140101
        if ($data['rekening']!='') {
            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='BANKPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $rekpinjam=explode(',',$bar['nilai']);
            foreach($rekpinjam as $key){
                $arrpinjam[$key]=$key;
            }

            $str = "select nilai from ".$dbname.".setup_parameterappl where kodeparameter='AKUNPINJAM'";
            $res = $owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
            $akpinjam=$bar['nilai'];

            if(in_array($data['rekening'],$arrpinjam)){
                $data['noakun']=$akpinjam;
                $param['noakun']=$akpinjam;
                $data['oldNoakun']=$akpinjam;
            }
        }

        if($data['kurs'] == 0 || $data['kurs'] == ''){
                        exit("Error: Nilai kurs harus diisi (tidak boleh kosong).");
                }
                if(($data['hutangunit']==1)and($data['pemilikhutang']=='' or $data['noakunhutang']==''))
        {
            exit("Error: Silakan melengkapi data hutang.");
        }
            if($data['hutangunit']=='') $data['hutangunit']=0;
                $where = "notransaksi='".$data['notransaksi']."' and kodeorg='".$data['kodeorg']."' and noakun='".$data['oldNoakun']."' and tipetransaksi='".$data['tipetransaksi']."'";
                $wheredt = "notransaksi='".$data['notransaksi']."' and kodeorg='".$data['kodeorg']."'";
                $datadt['noakun2a'] = $param['noakun'];
                $datadt['matauang'] = $param['matauang'];
        $notransaksi=$data['notransaksi'];
        unset($data['notransaksi']);
        unset($data['kodeorg']);
        unset($data['oldNoakun']);
        unset($data['tipetransaksi']);
        unset($data['noakun2b']);
        $data['tanggalinput'] = tanggalsystem($data['tanggalinput']);
        $data['jumlah'] = str_replace(',','',$data['jumlah']);
        $data['norekpenerima'] = $param['norekpenerima'];
        $data['namapenerima'] = $param['namapenerima'];
        $data['noakun2'] = $param['noakun2b'];
        $data['updateby'] = $_SESSION['standard']['userid'];
		
		if($data['autokb']=='1'){
			 if($data['namapenerima']==''){
				 exit("Warning:Unit Penerima masih kosong");
			 }
			 if($data['noakun2']==''){
				 exit("Warning:Akun Penerima masih kosong");
			 }
			 if($data['noakun2']=='1110101' or $data['noakun2']=='1111101'){
				 exit("Warning:Jika akun bank, maka rekening tidak boleh kosong");
			 }
		 }
		
		
        $query = updateQuery($dbname,'keu_kasbankht',$data,$where);
		// echo $query;exit('warning');
		try{$owlPDO->exec($query);

            if ($data['cgttu']!='Transfer' && $data['cgttu']!='Cash') {

                if ($data['cgttu']=='Cheque'){
                    $kdtipe='CK';
                }
                if ($data['cgttu']=='Giro'){
                    $kdtipe='GR';
                }
                if ($data['cgttu']=='PO'){
                    $kdtipe='PO';
                }

                ##ambil data buku cek lama
                $str = "select * from ".$dbname.".keu_bukucekdt where notransaksi='".$notransaksi."' ";
                //and substr(notrans_cek,5,2)!='".$kdtipe."'
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar=$res->fetch();
                $nocek=$bar['nocek'];

                if ($nocek!=''){
                    
                    if($data['nocek']!=$nocek){

                        $strdt = "delete from ".$dbname.".keu_bukucekdt where notransaksi='".$notransaksi."' and nocek='".$nocek."'";
                        try {
                            $owlPDO->exec($strdt);
                        } catch (PDOException $e) {
                            print " Gagal: " . $e->getMessage() . "\n";
                            die();
                        }

                        //ambil data buku cek baru
                        $str = "select notrans_cek from ".$dbname.".keu_bukucekht where noakun='".$data['rekening']."' and status='1' and tipe_buku='".$data['cgttu']."' order by right(notrans_cek,3) desc";
                        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        $bar=$res->fetch();
                        $notrans_cek=$bar['notrans_cek'];
                    
                        $str = "insert into " . $dbname . ".keu_bukucekdt (notrans_cek,notransaksi,nocek,status_cek)
                            values ('".$notrans_cek."','".$notransaksi."','".$data['nocek']."','1')";
                        try{
                            $owlPDO->exec($str); 
                        }catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }

                if ($nocek==''){

                    //ambil data buku cek baru
                    $str = "select notrans_cek from ".$dbname.".keu_bukucekht where noakun='".$data['rekening']."' and status='1' and tipe_buku='".$data['cgttu']."' order by right(notrans_cek,3) desc";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $bar=$res->fetch();
                    $notrans_cek=$bar['notrans_cek'];

                    $str = "insert into " . $dbname . ".keu_bukucekdt (notrans_cek,notransaksi,nocek,status_cek)
                            values ('".$notrans_cek."','".$notransaksi."','".$data['nocek']."','1')";
                    try{
                        $owlPDO->exec($str); 
                    }catch(PDOException $e){
                        echo " Gagal," . addslashes($e->getMessage());
                    }
                }
            }

            

        }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        $querydt = updateQuery($dbname,'keu_kasbankdt',$datadt,$wheredt);
        try{$owlPDO->exec($querydt); echo 'Done.';}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        // tadinya ga pake else echo Done, tapi kalo ga pake update-annya ga kesimpen. koq bisa ya?
        // tambahan querydt untuk ngupdate noakun2a kasbankdt
        break;
    case 'delete':
		try{
			$owlPDO->beginTransaction();

            // ini dia: ambil semua file
            $str = "select id, namafile from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $listhapusfile[$bar['id']]=$bar['id'];
                $hapusini[$bar['id']]['namafile']=$bar['namafile'];
            }

            if(!empty($listhapusfile))foreach($listhapusfile as $idnyaz){
                $namafile=$hapusini[$idnyaz]['namafile'];
                $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$namafile."'";
                // exit('error'.$str);
                try{
                    $owlPDO->exec($str);
                    $pathx = $path.$namafile;
                    unlink($pathx);
                }
                catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
            }
        // echo "<pre>";
        // print_r($hapusini);
        // echo "</pre>";
        // exit("error:");
			
			##Delete kas/bank HT
			$where = "notransaksi='".$param['notransaksi']."' and kodeorg='".$param['kodeorg']."' and noakun='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'";
			$query = "delete from `".$dbname."`.`keu_kasbankht` where ".$where;
			$owlPDO->exec($query);
			
			##Delete Bukut Cek DT
			$strdt = "delete from ".$dbname.".keu_bukucekdt where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($strdt);
			
			##Get id E-Filing
			$str="select * from ".$dbname.".filemanager where namafile='".$param['notransaksi']."'";
			$res=fetchdata($str);
			$idefil=$res[0]['id'];
			if($idefil!=''){
				$structure = setlocationfile($idefil);
			
				## Delete Parent E-FILING
				deleteefil($param['notransaksi'],$structure);
			}
			
			## DELETE FROM TABLE APPROVAL
			$str="delete from ".$dbname.".approval where notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "warning, " . addslashes($e->getMessage());
		}
        break;
		
		
        case 'getUangMuka':
                // Get Transaksi Keluar
                $where = "a.noakun='".$param['noakun']."' and a.notransaksi != '".
                        $param['notransaksi']."' and a.tipetransaksi='K' and b.posting=1";
                if(!empty($param['nik'])) $where .= " and a.nik='".$param['nik']."'";
                $query1 = "SELECT a.*,b.posting from ".$dbname.".keu_kasbankdt a
                        LEFT JOIN ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi
                        WHERE ".$where;
                $res1 = fetchData($query1);

                // Get Transaksi yang sudah dipertanggungjawabkan
                $where2 = "nodok is not null and nodok <> '' and tipetransaksi='K'";
                $res2 = makeOption($dbname,'keu_kasbankdt','notransaksi,notransaksi',$where2);

                // Filter
                $res3 = array();
                $listKary = array();
                foreach($res1 as $row) {
                        $listKary[$row['nik']] = $row['nik'];
                        if(!in_array($row['notransaksi'],$res2)) {
                                $res3[] = $row;
                        }
                }

                if(!empty($listKary)) {
                        $optKary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid in ('".implode("','",$listKary)."')");
                } else {
                        $optKary = array();
                }
				
                $res = "<div style='max-height:300px;max-width:500px;overflow:auto'><table style=width:100%><thead>";
                $res .= "<tr class=rowheader>";
                $res .= "<td align=center>".$_SESSION['lang']['notransaksi']."</td>";
                $res .= "<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
                $res .= "<td align=center>".$_SESSION['lang']['jumlah']."</td>";
                $res .= "</tr>";
                $res .= "</thead><tbody>";
                if(empty($res3)) {
                        $res .= "<tr class=rowcontent><td colspan=3>Data Kosong</td></tr>";
                } else {
                        foreach($res3 as $row) {
                                $res .= "<tr class=rowcontent onclick='setNodok(\"".$row['notransaksi']."\",\"".
                                        $row['nik']."\",\"".number_format($row['jumlah'],2)."\")'>";
                                $res .= "<td style=cursor:pointer>".$row['notransaksi']."</td>";
                                $res .= "<td style=cursor:pointer>".@$optKary[$row['nik']]."</td>";
                                $res .= "<td  style=cursor:pointer align=right>".number_format($row['jumlah'],2)."</td>";
                                $res .= "</tr>";
                        }
                }
                $res .= "</tbody></table></div>";

                echo $res;
                break;
    case'getformRekPt':
        $optOrg = getOrgDetail(1);
        $opUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        foreach($optOrg as $rw=>$lst){
            if(substr($rw,0,5)=='Pilih'){
                continue;
            }
            $opUnit.="<option value='".$rw."'>".$rw."-".$lst."</option>";
        }
        $data="";
        $data.= "<fieldset style=width:630px><legend>".$_SESSION['lang']['form']."</legend>";
        $data.="<table cellspacing=1 cellpadding=1 border=0>";
        $data.="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id=unitSrcRek style=width:150px onchange=findgetRekPT()>".$opUnit."</select></td></tr>";
        $data.="</table></fieldset>";
        $data.="<fieldset style=width:630px;float:left>";
        $data.="<legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div id=containerRekPT style=width:630px;height:355px;overflow:auto>";
        $data.="</div></fieldset>";
        echo $data;
    break;
	
	case'submitfilex':
	
		if($param['notransaksi']==''){
			exit("warningsystem:Nomor Transaksi belum ada, silahkan simpan header dahulu, baru lakukan upload file");
		}
	
	
		#= jadikan try commi
		try {
			
			$owlPDO->beginTransaction();
			
			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));

			if ($_FILES['file']['size'] > $filesize){
				throw new PDOException("Ukuran File melebihi ".number_format($filezie/1024)." KB; ukuran file ini ".number_format($_FILES['file']['size']/1024,2)." Kb");
			}

			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){    
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $param['jenisupload']."_".$nmTemp."_".$his."".$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
						$str = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','".$filename."','".$filetype."','".$param['jenisupload']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}else{
						throw new PDOException("Format file upload tidak boleh ".$filetype);
					}
				}
			}
			
			if (!file_exists($path.$filename)) {
				throw new PDOException("File gagal diupload");
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());

		}			
		
    break;

    case'submitfilexxx':
	
		if($param['notransaksi']==''){
			exit("warningsystem:Nomor Transaksi belum ada, silahkan simpan header dahulu, baru lakukan upload file");
		}
	
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
                        if($param['fileupload']!=''){
                            if($_FILES['file']['error']==0){    
                                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                                $filename = $param['jenisupload']."_".$nmTemp."_".$his."".$filetype;
                                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                                
                                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                                    $str = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','".$filename."','".$filetype."','".$param['jenisupload']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
									/*
									 $str = "insert into ".$dbname.".listfile_keu_kasbank values ('','".$param['notransaksi']."','".$filename."','".$filetype."','1','".$param['jenisupload']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
									*/
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
                                    exit("Warning : Format file upload tidak boleh ".$filetype);
                                }
                            }
                        }
    break;
    case 'deletefilex':
        $namafile=$param['namafile'];
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'"; //exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.$namafile;
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;
    case'getRekPT':
        $rRek=array();
        $sRek="select * from ".$dbname.".keu_5akunbank where pemilik='".$param['kodeorg']."'";
        //echo $sRek;
        $rRek=fetchData($sRek);
        $tab="<table cellspacing=1 cellpadding=1 class=sortable border=0>";
        $tab.="<thead>";
        $tab.="<tr><td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['namabank']."</td>";
        $tab.="<td>".$_SESSION['lang']['norekeningbank']."</td>";
        $tab.="<td>".$_SESSION['lang']['cabang']."</td>";
        $tab.="<td>".$_SESSION['lang']['atasnama']."</td>";
        $tab.="</tr></thead><tbody>";
        foreach ($rRek as $key => $val) {
            $Noaj+=1;
            $optNamaBank=makeOption($dbname,"keu_5daftarbank","kodebank,namabank","kodebank='".$val['namabank']."'");
            $tab.="<tr class=rowcontent style='cursor:pointer;' onclick=fillRekPt(".$Noaj.")>";
            $tab.="<td>".$Noaj."</td>";
            $tab.="<td>".$optNamaBank[$val['namabank']]."</td>";
            $tab.="<td>".$val['rekening']."</td>";
            $tab.="<td>".$val['cabang']."</td>";
            $tab.="<td>".$val['atasnama']."
                   <input type=hidden id=rekeningGet_".$Noaj." value='".$val['rekening']."' />
                   <input type=hidden id=atasnamaGet_".$Noaj." value='".$val['atasnama']."' /></td>";
            $tab.="</tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
    default:
    break;
}

function formHeader($mode,$data) {

    global $dbname;
    global $owlPDO;
    global $emodul;
    # Default Value

	$arrmodul = getmodulefil($emodul);
	foreach($arrmodul as $key=>$val){
		$optjenisupload[$key] = $val['kriteria'];
	}
    

    if(empty($data)) {
        $data['notransaksi'] = '';
        $data['kodeorg'] = $_SESSION['empl']['lokasitugas'];
        $data['noakun'] = '';
        $data['tanggalinput'] = '';
        $data['tipetransaksi'] = '';
        $data['jumlah'] = '0';
        $data['matauang'] = 'IDR';
        $data['kurs'] = '1';
        $data['cgttu'] = '';
        $data['keterangan'] = '';
        $data['yn'] = '0';
        $data['oldNoakun'] = '';
        $data['hutangunit'] = 0;
        $data['pemilikhutang'] = '';
        $data['nocek'] = '';
		$data['bayarkepada'] = '-';
        $data['noakunhutang'] = '';
        $data['autokb'] = 0;
        $data['noakun2'] = '';
        $data['norekpenerima'] = '';
        $data['namapenerima'] = '';
    } else {
        $data['jumlah'] = number_format($data['jumlah'],2);
    }

    # Disabled Primary
    if($mode=='edit') {
        $disabled = 'disabled';
		$valdate = $data['tanggalinput'];
    } else {
        $disabled = '';
		$valdate = date('d-m-Y');
    }

    # Options
    $whereJam=" kasbank=1 and detail=1 and (pemilik='".$data['kodeorg']."' or pemilik='GLOBAL' or pemilik='".$data['kodeorg']."')";
    $optMataUang = makeOption($dbname,'setup_matauang','kode,matauang');
	// $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'");
	$optOrg = getOrgDetail(1);
	$optOrg['']=$_SESSION['lang']['pilihdata']; 
	ksort($optOrg);
	
	if($_SESSION['language']=='EN')
	{
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun1',$whereJam,'2');
        $optAkun['']=$_SESSION['lang']['pilihdata']; 
        ksort($optAkun);
	}
	else
	{
		$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereJam,'2');
        $optAkun['']=$_SESSION['lang']['pilihdata']; ksort($optAkun);
	}

    $optTipe = array(''=>$_SESSION['lang']['pilihdata'],'M'=>$_SESSION['lang']['masuk'],'K'=>$_SESSION['lang']['keluar']);
    $optCgt = getEnum($dbname,'keu_kasbankht','cgttu');
    $optYn = array(0=>$_SESSION['lang']['belumposting'],1=>$_SESSION['lang']['posting']);
    $wheredz = " kodeorganisasi != '".$_SESSION['empl']['lokasitugas']."' and length(kodeorganisasi)=4";
    $wheredx = " (noakun like '211%' or noakun like '213%' or noakun like '212%' or left (noakun,1)='7') and length(noakun)=7";
    $optPemilikHutang = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$wheredz);
    $optNoakunHutang = makeOption($dbname,'keu_5akun','noakun,namaakun',$wheredx,'2');
    $optPemilikHutang['']=$_SESSION['lang']['pilihdata']; ksort($optPemilikHutang);
    $optNoakunHutang['']=$_SESSION['lang']['pilihdata']; ksort($optNoakunHutang);
    $optHutangUnit = array('0'=>$_SESSION['lang']['ya'],'1'=>$_SESSION['lang']['tidak']);

    
    $nmbank = array(""=>$_SESSION['lang']['pilihdata']);
    $str="select * from ".$dbname.".keu_5daftarbank";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar=$res->fetch()){
        $nmbank[$bar['kodebank']]=$bar['namabank'];
    }

    $optdaftarbank = array(""=>$_SESSION['lang']['pilihdata']);
    $strak="select * from ".$dbname.".keu_5akunbank";
    $barak=fetchData($strak);
    foreach($barak as $row=>$lst){
        $optdaftarbank[$lst['noakun']]=$lst['rekening']." - ".$nmbank[$lst['namabank']];
    }
	
	 $optdaftarbank['']=$_SESSION['lang']['pilihdata']; ksort($optdaftarbank);
	

//    echo "Warning: <pre>".print_r($optNoakunHutang).'</pre>';
//    exit;

    $els = array();
    $els[] = array(
        makeElement('notransaksi','label',$_SESSION['lang']['notransaksi']),
        makeElement('notransaksi','text',$data['notransaksi'],
            array('style'=>'width:150px','maxlength'=>'25','readonly'=>'readonly'))
    );
    $els[] = array(
        makeElement('kodeorg','label',$_SESSION['lang']['unitkerja']),
        makeElement('kodeorg','select',$data['kodeorg'],
            array('style'=>'width:155px','onchange'=>'getakunkasbank(this)',$disabled=>$disabled),$optOrg)
    );
      $els[] = array(
        makeElement('noakun2a','label',$_SESSION['lang']['noakun']),
        makeElement('noakun2a','selectsearch',$data['noakun'],
            array('style'=>'width:155px','onchange'=>'getbank()'),$optAkun)
    );
      $els[] = array(
        makeElement('rekening','label',$_SESSION['lang']['bank']),
        makeElement('rekening','select',$data['rekening'],
            // array('style'=>'width:155px',$disabled=>$disabled,'onchange'=>'getbuktibayar()'),$optdaftarbank)
            array('style'=>'width:155px','disabled'=>'disabled','onchange'=>'getbuktibayar()'),$optdaftarbank)
    );

    $els[] = array(
        makeElement('tanggalinput','label',$_SESSION['lang']['tanggalinput']),
        makeElement('tanggalinput','text',$valdate,array('style'=>'width:150px',
        'onmousemove'=>'setCalendar(this.id)','readonly'=>'readonly'))
    );
    $els[] = array(
        makeElement('matauang','label',$_SESSION['lang']['matauang']),
        makeElement('matauang','select',$data['matauang'],
			 array('style'=>'width:155px','onchange'=>'getKurs()'),$optMataUang)
			
			#permintaan pak rahmad per tanggal 03 june 2015 by email membuang $disabled=>$disabled setelah getKurs,jamhari
    );
    $els[] = array(
        makeElement('kurs','label',$_SESSION['lang']['kurs']),
        makeElement('kurs','textnum',$data['kurs'],
			array('style'=>'width:150px'))
    );

    $els[] = array(
        makeElement('tipetransaksi','label',$_SESSION['lang']['tipetransaksi']),
        makeElement('tipetransaksi','select',$data['tipetransaksi'],
            array('style'=>'width:155px',$disabled=>$disabled),$optTipe)
    );
        $els[] = array(
        makeElement('nocek','label','No. Bukti Bayar'),
        makeElement('nocek','text',$data['nocek'],array('style'=>'width:150px','disabled'=>'disabled'))
    );
	$els[] = array(
        makeElement('bayarkepada','label','Bayar Ke/Masuk Dari'),
        makeElement('bayarkepada','text',$data['bayarkepada'],array('style'=>'width:150px'))
    );
   
    $els[] = array(
        makeElement('jumlah','label',$_SESSION['lang']['jumlah']),
        makeElement('jumlah','textnum',$data['jumlah'],
            array('style'=>'width:150px','onchange'=>'this.value=remove_comma(this);this.value = _formatted(this)'))
    );
    $els[] = array(
        makeElement('cgttu','label',$_SESSION['lang']['cgttu']),
        // makeElement('cgttu','select',$data['cgttu'],array('style'=>'width:155px','onchange'=>'getbuktibayar()'),$optCgt)
        makeElement('cgttu','select',$data['cgttu'],array('style'=>'width:155px','disabled'=>'disabled','onchange'=>'getbuktibayar()'),$optCgt)
    );
    $els[] = array(
        makeElement('namabank','label',$_SESSION['lang']['namabank']),
        makeElement('namabank','select',$data['namabank'],array('style'=>'width:155px','disabled'=>'disabled'),$nmbank)
    );
    $els[] = array(
        makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
        makeElement('keterangan','text',$data['keterangan'],array('style'=>'width:150px','maxlength'=>'255'))
    );
    $els[] = array(
        makeElement('yn','label',$_SESSION['lang']['yn']),
        makeElement('yn','select',$data['yn'],
            array('style'=>'width:155px','disabled'=>'disabled'),$optYn)
    );
	
	
	
	
	
	if($data['autokb']==0)
        $dis='disabled'; else $dis='';
	
	$els[] = array(
        makeElement('autokb','label','Auto Kas/Bank'),
        makeElement('autokb','checkbox',$data['autokb'],
                array('onclick'=>"pilihautokb()"))
    );
	
	$els[] = array(
        makeElement('namapenerima','label',$_SESSION['lang']['unitkerja']),
        makeElement('namapenerima','select',$data['namapenerima'],
            array('style'=>'width:155px',$dis=>$dis,'onchange'=>'getbank2()'),$optOrg)
    );
	
   $els[] = array(
        makeElement('noakun2b','label',$_SESSION['lang']['noakun']),
        makeElement('noakun2b','selectsearch',$data['noakun2'],
            array('style'=>'width:155px',$dis=>$dis,'onchange'=>'getbank2()'),$optAkun)
    );
	
	$els[] = array(
        makeElement('norekpenerima','label',$_SESSION['lang']['bank']),
        makeElement('norekpenerima','select',$data['norekpenerima'],
            array('style'=>'width:155px',$dis=>$dis),$optdaftarbank)
    );
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	if($data['hutangunit']==0)
        $dis='disabled'; else $dis='';
	
	
	$dis='hidden';

    $els[] = array(
        makeElement('hutangunit','label',''),
        makeElement('hutangunit','checkbox',$data['hutangunit'],
                array('onclick'=>"pilihhutang()",$dis=>$dis))
    );
    $els[] = array(
        makeElement('pemilikhutang','label',''),
        makeElement('pemilikhutang','select',$data['pemilikhutang'],
            array('style'=>'width:155px',$dis=>$dis),$optPemilikHutang)
    );

    $els[] = array(
        makeElement('noakunhutang','label',''),
        makeElement('noakunhutang','select',$data['noakunhutang'],
            array('style'=>'width:155px',$dis=>$dis),$optNoakunHutang)
    );
	
	
	
	/*
    if($data['hutangunit']==0)
        $dis='disabled'; else $dis='';

    $els[] = array(
        makeElement('hutangunit','label',$_SESSION['lang']['hutangunit']),
        makeElement('hutangunit','checkbox',$data['hutangunit'],
                array('onclick'=>"pilihhutang()"))
    );
    $els[] = array(
        makeElement('pemilikhutang','label',$_SESSION['lang']['pemilikhutang']),
        makeElement('pemilikhutang','selectsearch',$data['pemilikhutang'],
            array('style'=>'width:155px',$dis=>$dis),$optPemilikHutang)
    );


    $els[] = array(
        makeElement('noakunhutang','label',$_SESSION['lang']['noakunhutang']),
        makeElement('noakunhutang','selectsearch',$data['noakunhutang'],
            array('style'=>'width:155px',$dis=>$dis),$optNoakunHutang)
    );
	*/



    $els[] = array(
        makeElement('oldNoakun','hid',$data['noakun'] ));

    $els2 = array();

    $els2[] = array(
        makeElement('jenisupload','label',"Kriteria"),
        makeElement('jenisupload','select','Pilih Data',
            array('style'=>'width:155px'),$optjenisupload)
    );
    $els2[] = array(
        makeElement('filex','label','File'),
        makeElement('filex','file','',array('style'=>'width:150px'))
    );
   

    if($mode=='add') {
        $els['btn'] = array(
            makeElement('addHead','btn',$_SESSION['lang']['save'],
                array('onclick'=>"addDataTable()"))//,'disabled'=>'disabled'
        );
    } elseif($mode=='edit') {
        $els['btn'] = array(
            makeElement('editHead','btn',$_SESSION['lang']['save'],
                array('onclick'=>"editDataTable()"))
        );
    }

    if($mode=='add') {
        $varx= genElementMultiDim($_SESSION['lang']['addheader'],$els,2);
        $varb= genElementMultiDim('Upload File',$els2,1,'','','','left');
        return array($varx,$varb);
    } elseif($mode=='edit') {
        $varx= genElementMultiDim($_SESSION['lang']['editheader'],$els,2);
        $varb= genElementMultiDim('Upload File',$els2,1,'','','','left');
        return array($varx,$varb);
    }
}

function delete_directory($dirname){
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) 
	{
		if ($file != "." && $file != "..") 
		{
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}

function deleteefil($notransaksi,$structure){
	global $dbname;
	global $owlPDO;
	
	$optId=makeOption($dbname,'filemanager','namafile,id',"namafile='".$notransaksi."'");
	$id=$optId[$notransaksi];
	
	$str="delete from ".$dbname.".filemanager where namafile='".$notransaksi."'";
	$owlPDO->exec($str);
	
	if($id!=''){
		$str="delete from ".$dbname.".filemanager where induk='".$id."'";
		$owlPDO->exec($str);
	}
	
	delete_directory($structure);
	return true;
}
?>
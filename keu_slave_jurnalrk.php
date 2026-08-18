<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$proses=$_GET['proses'];

$proses=checkPostGet('proses','');
 
if(isset($_POST['noakundebet'])||isset($_POST['periode'])){
    $param=$_POST;
}else{
    $param=$_GET;
}


if($proses=='excel'){
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
}
else{ 
    $bg="";
    $brdr=0;
}
       //$arr="##kdOrg##updateby##periode2##periode##notrans"; 
        if($proses=='preview'){
            $rowspDt='';   
            if($param['periode2']<$param['periode2']){
                exit('warning: '.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['periode']);
            }
            $where="left(b.tanggal,7) between '".$param['periode']."' and '".$param['periode2']."'";
           
            if($param['kdOrg']==''){
                exit('warning:'.$_SESSION['lang']['unit']." ".$_SESSION['lang']['kosong']);
            }
            if($param['noakundebet']==''){
                exit('warning:'.$_SESSION['lang']['noakundebet']." ".$_SESSION['lang']['kosong']);
            }
            
            if($param['updateby']!=''){
                $where.=" and userid='".$param['updateby']."'";
            }
             
            if($param['notrans']!=''){
                $where.=" and a.notransaksi like '%".$param['notrans']."%'";   
            }

            $where.=" and a.noakun in (select akunpiutang from ".$dbname.".keu_5caco where jenis='intra' and kodeorg='".$param['kdOrg']."')";
            $sKas="select a.*,b.tanggal from ".$dbname.".keu_kasbankdt a left join 
					".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi 
					where ".$where." and hutangunit1=0";
            //echo $sKas;
            $optUnit=makeOption($dbname,'keu_5caco','akunpiutang,kodeorg');
            $optNmAkn=makeOption($dbname,'keu_5akun','noakun,namaakun');
			
            $rDet=array();
            $rData="";
            $rData=fetchdata($sKas);
            if(count($rData)!=0){
                $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
                $tab.="<tr class=rowheader>";
                $tab.="<td align=center ".$bg.">No.</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['notransaksi']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['tanggal']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['noakun']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['namaakun']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['nik']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['namakaryawan']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['namasupplier']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['keterangan']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['rp']."</td>";
                if($proses!='excel'){
                    $tab.="<td align=center ".$bg." colspan=2>".$_SESSION['lang']['action']."</td>";    
                }
                $tab.="</tr></thead><tbody>";       
                $maxRow=count($rData);
			    $rcek=array();
                $rCek=array();
                foreach ($rData as $key => $val){
                    $sCek="select nojurnal from ".$dbname.".keu_jurnaldt 
                               where noreferensi='".$val['notransaksi']."' and substr(nojurnal,14,3)='/M/' and keterangan='".$val['keterangan2']."'";
                    $rcek=fetchData($sCek);
                    if(count($rcek)!=0){
                        continue;
                    }
                    @$no+=1;
                    $optNmSupp='';
                    if($val['kodesupplier']!=''){
                        $whr="supplierid='".$val['kodesupplier']."'";
                        $optNmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);    
                    }
                    if($val['nik']!=''){
                        $whrn="karyawanid='".$val['nik']."'";
                        $optNm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrn); 
                        $optNik=makeOption($dbname,'datakaryawan','karyawanid,nik',$whrn); 
                    }
                    $viewDetailData="onclick=viewDetailData('".@$val['noinvoice']."') style=cursor:pointer title='Detail ".@$val['noinvoice']."'";
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td>".$no."</td>";
                    if($proses=='excel'){
                        $tab.="<td>'".$val['notransaksi']."</td>";    
                    }else{
                        $tab.="<td id=notransaksi_".$key." value='".$val['notransaksi']."####".$val['tipetransaksi']."####".$val['noakun2a']."####".$val['kodeorg']."'>".$val['notransaksi']."<input type=hidden id=notrans_".$key." value='".$val['notransaksi']."' /></td>";
                    }
                    if($proses!='excel'){
                        $tab.="<td id=tanggal_".$key." value='".$val['tanggal']."'>".tanggalnormal($val['tanggal'])."</td>";
                    }else{
                        $tab.="<td ".$rowspDt.">".$val['tanggal']."</td>";
                    }
                    $tab.="<td id=noakun_".$key." value='".$val['noakun']."'>".$val['noakun']."</td>";
                    $tab.="<td id=unitdt_".$key." value='".$optUnit[$val['noakun']]."'>".$optNmAkn[$val['noakun']]."</td>";
                    $tab.="<td id=nikdt_".$key." value='".$val['nik']."'>".@$optNik[$val['nik']]."</td>";
                    $tab.="<td>".@$optNm[$val['nik']]."</td>";
                    $tab.="<td id=supplierid_".$key." value='".$val['kodesupplier']."'>".@$optNmSupp[$val['kodesupplier']]."</td>";
                    $tab.="<td id=ket2_".$key." value='".$val['keterangan2']."'>".$val['keterangan2']."</td>";
                    $tab.="<td id=rup_".$key." value='".$val['jumlah']."' align=right>".number_format($val['jumlah'],2)."</td>";
                    $dtpost="";
                    $imgdt="";
                    if($proses!='excel'){
                        $sCek="select nojurnal from ".$dbname.".keu_jurnaldt 
                               where noreferensi='".$val['notransaksi']."' and substr(nojurnal,14,3)='/M/' and keterangan='".$val['keterangan2']."'";
                        $rCek=fetchData($sCek);
                        if(count($rCek)!=0){
							$maxRow-=1;
                            //$optCek=makeOption($dbname,'keu_jurnaldt','noreferensi,nojurnal',"noreferensi='".$val['notransaksi']."' and keterangan='".$val['keterangan2']."'");
                            $tab.="<td>".$rCek[0]['nojurnal']."</td>";
                        }else{
                            $tab.="<td><input type=checkbox id=trans_".$key." /></td>";   
                        }
                        
                        $tab.="<td><img src=\"images/skyblue/pdf.jpg\" class=\"zImgBtn\" onclick=\"detailPDF(".$key.",event)\" title=\"Print Data Detail\"></td>";
                    }
                    $tab.="</tr>";
                }
				
                    $tab.="</tbody>";
                    $tab.="</table>";
                    if($maxRow!=0){
                        $tab.="<button class=mybutton onclick=addDataKeMemorial(".$maxRow.") >".$_SESSION['lang']['proses']."</button>";    
                    }
            }else{
                    $tab.="<tr class=rowcontent align=center><td colspan=12>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
                    $tab.="</table>";
            }
        }
        
switch($proses) {
    case'preview':
                echo $tab;
                break;

    case'addDetail':
        $kodejurnal="M";
        $kdPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$_POST['unitdt']."'");
        $sKonter="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kdPt[$_POST['unitdt']]."'
                  and kodekelompok='".$kodejurnal."'";
        $rKonter=fetchdata($sKonter);
        $nokounter=$rKonter[0]['nokounter'];
        $dataH[0]['unit']=$_POST['unitdt'];
        $tempTgl='';
        foreach($_POST['tanggal'] as $row=>$lstDt){
				#buat metode delete insert agar jika salah akun debet bisa direvisi langsung
				#cari jurnal memo di jurnalht yang noreferensinya kasbank
				#hapus jurnal memo
				$str=" select * from ".$dbname.".keu_jurnalht where noreferensi='".$_POST['notransaksi'][$row]."' and kodejurnal='M' ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$nojurnalawal=$bar['nojurnal'];
					
				$str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnalawal."' ";
                try {
                    $owlPDO->exec($str);
                } catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                    die(); 
                }
				#### tutup delete data yg sudah ada
			
                if($tempTgl!=$lstDt){
                        $dataH[0]['tanggal']=$lstDt;
                        $tempTgl=$lstDt;
                        #====cek periode
                        $tgl = str_replace("-","",$lstDt);
                        $sPeriode="select * from ".$dbname.".setup_periodeakuntansi 
                                   where kodeorg='".@$param['unitdt']."' and tutupbuku=0 order by periode desc";
                        $rPeriode=fetchdata($sPeriode);
                        @$tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
                        if($tglakutansi>$tgl){
                            exit('Error:Date beyond active period');
                        }
                }
                $kdOrg=makeOption($dbname,'keu_kasbankht','notransaksi,kodeorg',"notransaksi='".$_POST['notransaksi'][$row]."'");
                $sCaco="select akunpiutang from ".$dbname.".keu_5caco where jenis='intra' and kodeorg='".$kdOrg[$_POST['notransaksi'][$row]]."'";
                $rCaco=fetchdata($sCaco);
                $akunKredit=$rCaco[0]['akunpiutang'];

                if ($akunKredit=='') {
                    exit("Warning : Account intraco or interco not available for ".$kdOrg[$_POST['notransaksi'][$row]].". Please setting on menu Finance > setup > COA for Intra/Interco.");
                }
                #1. Data Header
                # Get Journal Counter
                $nokounter=intval($nokounter)+1;
                $konter = addZero($nokounter,3);
                # Prep No Jurnal
                $nojurnal = $tgl."/".$dataH[0]['unit']."/".$kodejurnal."/".$konter;
                # Prep Header
                $dataRes['header'][] = array(
                    'nojurnal'=>$nojurnal,
                    'kodejurnal'=>$kodejurnal,
                    'tanggal'=>$dataH[0]['tanggal'],
                    'tanggalentry'=>date('Ymd'),
                    'posting'=>'0',
                    'totaldebet'=>'0',
                    'totalkredit'=>'0',
                    'amountkoreksi'=>'0',
                    'noreferensi'=>$_POST['notransaksi'][$row],
                    'autojurnal'=>'1',
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'revisi'=>'0'
                );
                $noUrut=1;
                #debet
                $kdvhcdt="";
                $kdproj="";
                if(substr($_POST['noakundebet'],0,1)=='4'){
                    $kdvhcdt=$_POST['kdvhc_noproj'];
                    $kdproj="";
                }else if(substr($_POST['noakundebet'],0,5)=='12813'){
                    $kdproj=$_POST['kdvhc_noproj'];
                    $kdvhcdt="";
                }
                $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>$dataH[0]['tanggal'],
                'nourut'=>$noUrut,
                'noakun'=>$_POST['noakundebet'],
                'keterangan'=>$_POST['ket2'][$row],
                'jumlah'=>$_POST['rupdt'][$row],
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>$dataH[0]['unit'],
                'kodekegiatan'=>'',
                'kodeasset'=>$kdproj,
                'kodebarang'=>'',
                'nik'=>$_POST['karyid'][$row],
                'kodecustomer'=>'',
                'kodesupplier'=>$_POST['supplierid'][$row],
                'noreferensi'=>$_POST['notransaksi'][$row],
                'noaruskas'=>'',
                'kodevhc'=>$kdvhcdt,
                'nodok'=>$_POST['notransaksi'][$row],
                'kodeblok'=>'',
                'revisi'=>'0',
                'kodesegment' => ''
                );
                $noUrut++;
                #kredit
                $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>$dataH[0]['tanggal'],
                    'nourut'=>$noUrut,
                    'noakun'=>$akunKredit,
                    'keterangan'=>$_POST['ket2'][$row],
                    'jumlah'=>$_POST['rupdt'][$row]*(-1),
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>$dataH[0]['unit'],
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>'',
                    'nik'=>$_POST['karyid'][$row],
                    'kodecustomer'=>'',
                    'kodesupplier'=>$_POST['supplierid'][$row],
                    'noreferensi'=>$_POST['notransaksi'][$row],
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>$_POST['notransaksi'][$row],
                    'kodeblok'=>'',
                    'revisi'=>'0',
                    'kodesegment' => ''
                );
        }
      
        #=== Insert Data ===
        $errorDB = "";
        # Header
        foreach($dataRes['header'] as $key=>$dataDet) {
            $queryH = insertQuery($dbname,'keu_jurnalht',$dataDet);
            try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Gagal : Header: ".$key." ". $e->getMessage() ; }
        }
        # Detail
        if($errorDB==''){
            foreach($dataRes['detail'] as $key=>$dataDet) {
                $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
                try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Gagal : Detail: ".$key." ". $e->getMessage() ; }
            }
        }
        // echo"<pre>";
        // print_r($dataRes['detail'][1]);
        // echo"</pre>";
        // exit('warning:'.$errorDB);
        if($errorDB!=""){
            // Rollback

            foreach($dataRes['header'] as $key=>$dataDet){ 
                $where = "nojurnal='".$dataDet['nojurnal']."'";
                $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
                try{$owlPDO->exec($queryRB); }catch (PDOException $e) {print $errorDB .= "Rollback 1 Error  :". $e->getMessage(); }
                exit('warning:'.$errorDB);
            }
        }
		
		#update nokounter
		#tambahan indra, update nokounter setelah add jurnal
		$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounter."' where kodeorg='".$kdPt[$_POST['unitdt']]."'
		  and kodekelompok='".$kodejurnal."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	
		
    break;
    case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br />By:".$_SESSION['empl']['name'];	
        
                $dte=date("YmdHis");
                $nop_="daftarTagihan_".$dte;
                if(strlen($tab)>0)
                {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
               closedir($handle);
            }
                        $handle=fopen("tempExcel/".$nop_.".xls",'w');
                        if(!fwrite($handle,$tab))
                        {
                                echo "<script language=javascript1.2>
                    parent.window.alert('Can't convert to excel format');
                    </script>";
                                exit;
            } else {
                                echo "<script language=javascript1.2>
                    window.location='tempExcel/".$nop_.".xls';
                    </script>";
            }
            fclose($handle);
        }
        break;
    case'getKdVhc':
        $sVhc="";
        $optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if(substr($param['noakundebet'],0,1)=='4'){
            $sVhc="select kodevhc as kodedata,detailvhc as namadata from ".$dbname.".vhc_5master where kodetraksi like '".$param['unit']."%' and status<>0";
        }else if(substr($param['noakundebet'],0,5)=='12813'){
            $sTipe="select * from ".$dbname.".sdm_5tipeasset where  akunak='".$param['noakundebet']."'";
            $rTipe=fetchData($sTipe);
            $kodeTipe=$rTipe[0];
            $sVhc="select kode as kodedata,nama as namadata from ".$dbname.".project where posting=0 and kodeorg='".$param['unit']."' and kode like '%".$kodeTipe['kodetipe']."%' order by kode asc";
        }
        
        if ($sVhc!=""){
            $rVhc=fetchData($sVhc);
            foreach ($rVhc as $key => $val) {
                $optData.="<option value='".$val['kodedata']."'>".$val['kodedata']."-".$val['namadata']."</option>";
            }
        }

        echo $optData;
    break;
    case'getDetail':
        $_POST['noinvoice']=$_POST['noinv'];
        #ambil data header
        $sHeader="select * from ".$dbname.".keu_tagihanht where noinvoice='".$_POST['noinvoice']."'";
        $rHeader=fetchdata($sHeader);
        #ambil data detal
        $sDet="select * from ".$dbname.".keu_tagihandt where noinvoice='".$_POST['noinvoice']."'";
        $rDet=fetchdata($sDet);
        $optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rHeader[0]['kodesupplier']."'");
        $optJur=makeOption($dbname,'keu_5jenistagihan','kode,jurnal');

        $arrNoyes=array("0"=>$_SESSION['lang']['no'],"1"=>$_SESSION['lang']['yes']);
        $tab="<fieldset><legend>".$_POST['noinvoice']."</legend><table cellspacing=1 cellpadding=1 border=0>";
        $tab.="<tr><td>".$_SESSION['lang']['noinvoice']."</td><td>:</td><td>".$_POST['noinvoice']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['noinvoicesupplier']."</td><td>:</td><td>".$rHeader[0]['noinvoicesupplier']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['tanggalterima']."</td><td>:</td><td>".tanggalnormal($rHeader[0]['tanggal'])."</td></tr>";
        if($optJur[$rHeader[0]['tipeinvoice']]==0){
            $tab.="<tr><td>".$_SESSION['lang']['subtotal']."</td><td>:</td><td>".number_format($rHeader[0]['nilaiinvoice'],2)."</td></tr>";
        }else{
            $tab.="<tr><td>".$_SESSION['lang']['nilaiinvoice']."</td><td>:</td><td>".number_format($rHeader[0]['nilaiinvoice'],2)."</td></tr>";
        }
        
        $tab.="<tr><td>".$_SESSION['lang']['nopo']."</td><td>:</td><td>".$rHeader[0]['nopo']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['namasupplier']."</td><td>:</td><td>".$optSupp[$rHeader[0]['kodesupplier']]."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['jurnal']."</td><td>:</td><td>".$arrNoyes[$optJur[$rHeader[0]['tipeinvoice']]]."</td></tr>";
        $tab.="</table>";
        if(count($rDet)!=0){
            $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
            $tab.="<tr class=rowheader><td>".$_SESSION['lang']['noakun']."</td>";
            $tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
            $tab.="<td>".$_SESSION['lang']['nilai']."</td>";
            $tab.="<td>".$_SESSION['lang']['kodevhc']."</td>";
            $tab.="<td>".$_SESSION['lang']['kodeasset']."</td>";
            $tab.="</tr></thead><tbody>";
             $totDet=0;
             $totSma=0;
            foreach($rDet as $row=>$lstDt){
                $optNmAkn=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$lstDt['noakun']."'");
                $tab.="<tr class=rowcontent><td>".$lstDt['noakun']."</td>";
                $tab.="<td>".$optNmAkn[$lstDt['noakun']]."</td>";
                $tab.="<td align=right>".number_format($lstDt['nilai'],2)."</td>";
                $tab.="<td>".$lstDt['kodevhc']."</td>";
                $tab.="<td>".$lstDt['kodeasset']."</td></tr>";
                $totDet+=$lstDt['nilai'];
            }
            if($optJur[$rHeader[0]['tipeinvoice']]==0){
                $totSma=$rHeader[0]['nilaiinvoice']+$totDet;
                $tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['nilaiinvoice']."</td>";
            }else{
                $totSma=$totDet;
                $tab.="<tr class=rowcontent><td colspan=2>".$_SESSION['lang']['total']." ".$_SESSION['lang']['detail']."</td>";
            }
            $tab.="<td align=right>".number_format($totSma,2)."</td>";
            $tab.="<td colspan=2>&nbsp;</td></tr>";   
            $tab.="</tbody></table>";
        }
        $tab.="</fieldset><br />";
        $detailHis=false;
        $sHed="select * from ".$dbname.".keu_tagihanht where nopo='".$rHeader[0]['nopo']."' and nopo!='' and noinvoice!='".$_POST['noinvoice']."'";
        $rHed=fetchdata($sHed);
        if(count($rHed)!=0){
            foreach ($rHed as $key => $val) {
                if($_POST['noinvoice']==$val['noinvoice']){
                    continue;
                }
                $dtInv[$val['noinvoice']]=$val['noinvoice'];
                $dtTgl[$val['noinvoice']]=$val['tanggal'];
                $dtSbttl[$val['noinvoice']]=$val['nilaiinvoice'];
                $dtInvSp[$val['noinvoice']]=$val['noinvoicesupplier'];
            }
            $sHed="select * from ".$dbname.".keu_tagihandt where noinvoice in (select noinvoice from ".$dbname.".keu_tagihanht where nopo='".$rHeader[0]['nopo']."' and  nopo!=''  and noinvoice!='".$_POST['noinvoice']."')";
            $rHed=fetchdata($sHed);
            foreach($rHed as $row=>$val){
                if($_POST['noinvoice']==$val['noinvoice']){
                    continue;
                }
                $detNil[$val['noinvoice']]+=$val['nilai'];
            }
            $detailHis=true;
        }
        if($detailHis!=false){
            $tab.="<div style='width:497px;height:420px;overflow:auto' ><fieldset><legend>".$_SESSION['lang']['list']." Lalu Invoice PO : ".$rHeader[0]['nopo']." </legend><table cellspacing=1 cellpadding=1 border=0>";
            $tab.="<thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td>".$_SESSION['lang']['noinvoice']."</td>";
            $tab.="<td>".$_SESSION['lang']['noinvoicesupplier']."</td>";
            $tab.="<td>".$_SESSION['lang']['tanggalterima']."</td>";
            $tab.="<td>".$_SESSION['lang']['subtotal']."</td>";
            $tab.="<td>".$_SESSION['lang']['nilai']." ".$_SESSION['lang']['detail']."</td>";
            $tab.="<td>".$_SESSION['lang']['total']."</td></tr></thead><tbody>";
            $totaldetail=0;
            if(count($dtInv)!=0){
                foreach($dtInv as $invDt){
                    $tab.="<tr class=rowheader>";
                    $tab.="<td>".$invDt."</td>";
                    $tab.="<td>".$dtInvSp[$invDt]."</td>";
                    $tab.="<td>".tanggalnormal($dtTgl[$invDt])."</td>";
                    $tab.="<td align=right>".number_format($dtSbttl[$invDt],2)."</td>";
                    $tab.="<td align=right>".number_format($detNil[$invDt],2)."</td>";
                    $totaldetail=$dtSbttl[$invDt]+$detNil[$invDt];
                    $tab.="<td align=right>".number_format($totaldetail,2)."</td></tr>";
                }
            }
            
            $tab."</tbody></table></fieldset></div>";
        }
        
        echo $tab;
    break;
    default:
    break;
}
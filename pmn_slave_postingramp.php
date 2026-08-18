<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$proses=$_GET['proses'];

$proses=checkPostGet('proses','');

if(isset($_POST['kdOrg'])){
    $param=$_POST;
}else{
    $param=$_GET;
}
if($proses!='preview'){
    if(isset($_POST['notiket'])){
        $param=$_POST;
    }else{
        $param=$_GET;
    }
}
$arr="##kdOrg##suppId##statTagihan##tglData##tglData2";


$optNm=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='RAMP')");
$optMatauang=makeOption($dbname, 'log_poht', 'nopo,matauang');


if($proses=='excel'){
    $bg=" bgcolor=#DEDEDE";
    $brdr=1;
}
else{ 
    $bg="";
    $brdr=0;
}
    if($param['statTagihan']==''){
        $param['statTagihan']=3;
    }
    if($proses=='preview'){
        if($param['kdOrg']==''){
            exit('warning: '.$_SESSION['lang']['unit'].' '.$_SESSION['lang']['kosong']);
        }
        if($param['suppId']==''){
            exit('warning: '.$_SESSION['lang']['namasupplier'].' '.$_SESSION['lang']['kosong']);
        }
        if($param['tglData']==''){
            exit('warning: '.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
        }
        if($param['tglData2']==''){
            exit('warning: '.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
        }
            $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
            $tab.="<tr class=rowheader>";
            $tab.="<td align=center ".$bg.">No.</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['noTiket']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['tanggal']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['pt']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['pabrik']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['nodo']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['kdsupplierramo']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['namasupplier']."</td>";
            $tab.="<td align=center ".$bg.">".$_SESSION['lang']['beratBersih']."</td>";
            if($param['statTagihan']!=2){
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['total']."</td>";    
            }else{
                $tab.="<td align=center ".$bg.">Update ".$_SESSION['lang']['beratBersih']."</td>";
            }
            
            if($proses!='excel'){
                $tab.="<td align=center ".$bg." >".$_SESSION['lang']['status']."</td>";    
            }
            $tab.="</tr></thead><tbody>";        
            if($param['tglData2']<$param['tglData']){
                exit('warning: '.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tanggal']);
            }
            $tgldt=" and left(datein,10) between '".tanggalsystemn($param['tglData'])."' and '".tanggalsystemn($param['tglData2'])."'";
            if(($param['statTagihan']!='3')&&($param['statTagihan']!='2')){                
                $tgldt.=" and posted='".$param['statTagihan']."'";
            }
            $sKas="select * from ".$dbname.".pmn_penerimaantbsramp 
                   where unit='".$param['kdOrg']."' and kodesupplier='".$param['suppId']."' ".$tgldt." order by left(datein,10) asc ";
            //echo $sKas;
            $rData=fetchdata($sKas);
            if(count($rData)!=0){
                foreach ($rData as $key => $val){
                    if($param['statTagihan']=='2'){
                        $netto = $val['beratmasuk'] - $val['beratkeluar'] - $val['potongan'];
                        $dtNetto=0;
                        $dataAp=explode(".",$netto);
                        if((intval($dataAp[1])>=1)&&(intval($dataAp[1])<=5)){
                            $dtNetto=floor($netto);
                        }else{
                            $dtNetto=round($netto,0);
                        }
                    }
                    if(($dtNetto-$val['netto'])==0){
                        continue;
                    }
                    $no+=1;
                    $whr="supplierid='".$val['kodesupplier']."'";
                    $optNmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='SUPPLIER')",$whr);
                    // $optNmSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
                    $kdKlsupp=substr($val['kodesupplier'], 0,5);
                    $whrNodo="kode='".$kdKlsupp."'";
                    $optNodo=makeOption($dbname,'log_5klsupplier','kode,nodo',$whrNodo);
                    $viewDetailData="onclick=viewDetailData('".$val['noinvoice']."') style=cursor:pointer title='Detail ".$val['noinvoice']."'";
                    $tab.="<tr class=rowcontent id=row".$no.">";
                    $tab.="<td>".$no."</td>";
                    $tab.="<td id=notiket_".$no.">".$val['notiket']."</td>";
                    $tab.="<td id=tgl_".$no.">".substr($val['datein'],0,10)."</td>";
                    $tab.="<td id=ptid_".$no.">".$val['kodeorg']."</td>";
                    $tab.="<td id=pabrik_".$no.">".$val['unit']."</td>";
                    $tab.="<td>".$optNodo[$kdKlsupp]."</td>";
                    $tab.="<td><input type=hidden  id=rampId_".$no." value='".substr($val['kodesupplier'],0,5)."' />".$val['kodesupplier']."</td>";
                    $tab.="<td><input type=hidden  id=supplierid_".$no." value='".$val['kodesupplier']."' />".$optNmSupp[$val['kodesupplier']]."</td>";
                    $tab.="<td  align=right><input type=hidden  id=beratBersih_".$no." value='".$val['netto']."' />".$val['netto']."</td>";
                    if($param['statTagihan']!=2){
                        $tab.="<td  align=right><input type=hidden   id=hargasatuan_".$no." value='".$val['harga']."' />".$val['harga']."</td>";
                        $totalRp=$val['harga']*$val['netto'];
                        $tab.="<td id=totalRupiah_".$no." value='".$totalRp."' align=right>".number_format($totalRp,2)."</td>";
                    }else{
                        $tab.="<td  align=right><input type=hidden  id=hargasatuan_".$no." value='".$dtNetto."' />".$dtNetto."</td>";
                    }
                    if($proses!='excel'){
                        $dtpost="";
                        if($val['posted']==0){
                            $imgdt="images/skyblue/posting.png";
                        }else{
                            $imgdt="images/skyblue/posted.png";
                        }
                        $tab.="<td align=center><img src=".$imgdt." class=\"zImgBtn\" ".$dtpost."   title=\"Posting\"></td>";
                    }
                    $tab.="</tr>";
                $totBeratBersih+=$val['netto'];
                $totRupiah+=$totalRp;
                    
                }
                    $tab.="</tbody>";
        } else {
                    $tab.="<tr class=rowcontent align=center><td colspan=11>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
            }
        $tab.="<tr class=rowcontent>";
        $tab.="<td  colspan=8>".$_SESSION['lang']['total']."</td>";
        if($param['statTagihan']!=2){
            $tab.="<td  align=right>".number_format($totBeratBersih,2)."</td>";
            $tab.="<td  align=right>&nbsp;</td>";
            $tab.="<td  align=right>".number_format($totRupiah,2)."</td><td  align=right>&nbsp;</td>";
        }else{
            $tab.="<td  align=right colspan=3>&nbsp;</td>";
        }
        $tab.="</tr>";
        #cek jabatan
        $rCek=array();
        $sCek="select * from ".$dbname.".setup_posting where kodeaplikasi='ramp' and jabatan='".$_SESSION['empl']['jabatan']."'";
        //echo "$sCek";
        $rCek=fetchData($sCek);
        $tab.="</table>";
        $disData="";
        if(empty($rCek)){
            $disData="disabled=disabled";    
        }
        $tab.="<button class=mybutton id='tombolPosting' ".$disData." onclick=postingData(1,".$param['statTagihan'].",".$no.")>".$_SESSION['lang']['posting']."</button>";    
    }
switch($proses) {
    case'preview':
                echo $tab;
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
    case'posting':
        if($param['statTagihan']==2){
            $sUpdate="update ".$dbname.".pmn_penerimaantbsramp set netto='".$param['hargasatuan']."' where notiket='".$param['notiket']."'";
            try{
                $owlPDO->exec($sUpdate); 
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage())."___".$sUpdate;
            }
        }else{
            $sData="select * from ".$dbname.".pmn_penerimaantbsramp where notiket='".$param['notiket']."' and posted=0";
            $dataH = fetchData($sData);
            if(count($dataH)==0){
                continue;
            }
            #tanggal lalu
            $tglLalu=strtotime('-1 day',strtotime($dataH[0]['datein'])) ;
            $tglLalu=date("Y-m-d",$tglLalu);
            
            #cek periode unit
            $sPeriodeAk="select * from ".$dbname.".setup_periodeakuntansi 
                         where tanggalmulai>='".substr($dataH[0]['datein'],0,10)."' and tanggalsampai<='".substr($dataH[0]['datein'],0,10)."' 
                         and tutupbuku=0 and kodeorg='".$dataH[0]['unit']."'";
            //echo $sPeriodeAk;
            $rPeriodeAk=fetchdata($sPeriodeAk);
            
            if(count($rPeriodeAk)!=0){
                exit('warning :'.$_SESSION['lang']['notifperiode']);
            }
            
            #cek apakah ada transaksi yang belum posting di tanggal sebelumnya
            $scek="select * from ".$dbname.".pmn_penerimaantbsramp where datein < '".$param['tanggal']."' and posted = '0'";
            $rCek=fetchData($scek);
            if($rCek[0]['kodesupplier']!='')
            {
                // exit("Warning:\nHarap posting transaksi dahulu untuk tanggal : ".tanggalnormal($rCek[0]['datein'])); 
            }
            
            #====cek periode
            $error0 = "";
            $optTglAcc=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg='".$dataH[0]['unit']."'");
            $tgl = str_replace("-","",substr($dataH[0]['datein'],0,10));
            
            if(tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['unit']])) > $tgl)
            {
                exit('Error:Date beyond active period'.tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['kodeorg']])));
            }
            
            #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
            $kodejurnal="INVTR";
            $optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$dataH[0]['unit']."'");
            $whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$dataH[0]['unit']]."'";
            $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
            $noKon = fetchData($query);
            $tmpC = $noKon[0]['nokounter'];
            $tmpC++;
            $counterjurnal = addZero($tmpC,3);
            $nojurnal = $tgl."/".$dataH[0]['unit']."/".$kodejurnal."/".$counterjurnal;
            $noreferensi = $dataH[0]['notiket'];

            #akun debet serta krdit
            $query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
            $dtnoakun = fetchData($query2);
            
            $totaljuranl=$totalpersediaan = $param['beratBersih']*$param['hargasatuan'];
            $rnetto=$param['beratBersih'];
            
            if($dataH[0]['beban_pajak']==0){
                $pph = ($dataH[0]['persenpajak'] * ($totalpersediaan)) / 100;
                $totaljuranl = $totalpersediaan;
                $totPersediaandt = ($totalpersediaan) - $pph;
            }
            else{
                $pph = ($totalpersediaan*(100/(100-$dataH[0]['persenpajak'])))*$dataH[0]['persenpajak']/100;
                $totaljuranl = $totalpersediaan + $pph;
                $totPersediaandt = $totalpersediaan;
            }
            #== nama supplier ==#
            $nmsup=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$dataH[0]['kodesupplier']."'");
            $namaSupllier=$nmsup[$dataH[0]['kodesupplier']];


            #=== Transform Data ===
            $dataRes['header'] = array();
            $dataRes['detail'] = array();

            # Prep Header
            $dataRes['header'] = array(
                'nojurnal'=>$nojurnal,
                'kodejurnal'=>$kodejurnal,
                'tanggal'=>substr($dataH[0]['datein'],0,10),
                'tanggalentry'=>date('Y-m-d'),
                'posting'=>'0',
                'totaldebet'=>$totaljuranl,
                'totalkredit'=>$totaljuranl*(-1),
                'amountkoreksi'=>'0',
                'noreferensi'=>$noreferensi,
                'autojurnal'=>'1',
                'matauang'=>'IDR',
                'kurs'=>'1',
                'revisi'=>'0'
            );
            $noUrut=1;
            $dataRes['detail'][] = array(
                'nojurnal'=>$nojurnal,
                'tanggal'=>substr($dataH[0]['datein'],0,10),
                'nourut'=>$noUrut,
                'noakun'=>$dtnoakun[0]['noakundebet'],
                'keterangan'=>'Persediaan TBS kode unit :'.$dataH[0]['unit'].' atas No Tiket : '.$noreferensi.',sebesar : '.$param['beratBersih'].' (Kg), kode ramp : '.$dataH[0]['koderamp'].',pengirim : '.$optSupp[$dataH[0]['kodesupplier']].' pada tanggal '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
                'jumlah'=>$totalpersediaan,
                'matauang'=>'IDR',
                'kurs'=>'1',
                'kodeorg'=>$dataH[0]['unit'],
                'kodekegiatan'=>'',
                'kodeasset'=>'',
                'kodebarang'=>'',
                'nik'=>'',
                'kodecustomer'=>'',
                'kodesupplier'=>$dataH[0]['kodesupplier'],
                'noreferensi'=>$noreferensi,
                'noaruskas'=>'',
                'kodevhc'=>'',
                'nodok'=>'',
                'kodeblok'=>'',
                'revisi'=>'0',
                'kodesegment' => '0000000001');
              $noUrut=2;
            if($dataH[0]['beban_pajak']==1){
                //exit('warning: data');
                 $ketdebet = "Biaya Beban PPH 22 ditanggung atas penerimaan TBS, a/n ".$namaSupllier.", No Tiket : ".$noreferensi.",sebesar : ".$param['beratBersih']." (Kg), pada tanggal ".tanggalnormal(substr($dataH[0]['datein'],0,10));
                $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>substr($dataH[0]['datein'],0,10),
                    'nourut'=>$noUrut,
                    'noakun'=>$dtnoakun[0]['noakundebet'],
                    'keterangan'=>$ketdebet,
                    'jumlah'=>$pph,
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>$dataH[0]['unit'],
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>'',
                    'nik'=>'',
                    'kodecustomer'=>'',
                    'kodesupplier'=>$dataH[0]['kodesupplier'],
                    'noreferensi'=>$noreferensi,
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>'',
                    'kodeblok'=>'',
                    'revisi'=>'0',
                    'kodesegment' => '0000000001');
            }
            // foreach($lstSupp as $dtSupp){
                // foreach($lstKlasifika as $dtKlasifikasi){
                    // if($rpSupplier[$dtSupp.$dtKlasifikasi]){
                        $noUrut++;
                        $whr="supplierid='".$dataH[0]['kodesupplier']."'";
                        $optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>substr($dataH[0]['datein'],0,10),
                            'nourut'=>$noUrut,
                            'noakun'=>$dtnoakun[0]['noakunkredit'],
                            'keterangan'=>'Pengakuan Hutang Supplier, atas pengiriman TBS dengan No Tiket : '.$noreferensi.',sebesar : '.$param['beratBersih'].' (Kg), pengirim : '.$optSupp[$dataH[0]['kodesupplier']].', pada tanggal : '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
                            'jumlah'=>$totPersediaandt*-1,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$dataH[0]['unit'],
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>$dataH[0]['kodesupplier'],
                            'noreferensi'=>$noreferensi,
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
                            'kodesegment' => '0000000001'
                            );
                    // }
                // }
            // }
                        $noUrut++;
                        $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>substr($dataH[0]['datein'],0,10),
                                'nourut'=>$noUrut,
                                'noakun'=>'2120200',
                                'keterangan'=>'Pengakuan Hutang PPH Pasal 22, atas pengiriman TBS dengan No Tiket : '.$noreferensi.',sebesar : '.$param['beratBersih'].' (Kg), pengirim : '.$optSupp[$dataH[0]['kodesupplier']].', pada tanggal : '.tanggalnormal(substr($dataH[0]['datein'],0,10)),
                                'jumlah'=>$pph*(-1),
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$dataH[0]['unit'],
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>'',
                                'kodecustomer'=>'',
                                'kodesupplier'=>$dataH[0]['kodesupplier'],
                                'noreferensi'=>$noreferensi,
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>'',
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment' => '0000000001'
                                );
            
            
            #=== Insert Data ===
            $errorDB = "";
            # Header
            $queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
            //exit('warning'.$queryH);
            try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; }
            # Detail
            if($errorDB=='') {
                foreach($dataRes['detail'] as $key=>$dataDet) {
                    $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
                    try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Detail: ".$key." ". $e->getMessage() ; }
                }
            }
            
            //============================================================
            
            #Update saldo tbs ramp
            $totalkg = 0;
            $totalrp = 0;
            $reshargarata = 0;
            $countdata = 0;
            $tmpkg = 0;
            $tmpharga = 0;
            $tanggal=substr($dataH[0]['datein'],0,10);
            $pt=$param['pt'];
            $kodepabrik=$param['kodepabrik'];
            $koderamp=$param['koderamp'];
            $str = "select beratmasuk,beratkeluar,potongan, harga,netto from ".$dbname.".pmn_penerimaantbsramp where kodeorg='".$pt."' and unit='".$kodepabrik."' and koderamp='".$koderamp."' and datein like '".$param['tanggal']."%' and posted='1'";
            $res =fetchData($str);
            foreach($res as $rowdt=>$bar){
                $rtmppotongan = round($bar['potongan']);
                $tmpkg += $bar['netto'];
                $tmpharga += ($bar['netto']*$bar['harga']);
            }
            
            $totalkg = $tmpkg + $rnetto;
            $totalrp = $tmpharga + ($rnetto*$dataH[0]['harga']);
            $reshargarata = $totalrp/$totalkg;
            
            if(count($res)==0){
                $str = "insert into ".$dbname.".keu_5saldotbsramp (kodeorg,unit,koderamp,tanggal,fisik,hargarata,updateby) values ('".$pt."','".$kodepabrik."','".$koderamp."','".$tanggal."','".$totalkg."','".$reshargarata."','".$_SESSION['standard']['userid']."')";
            }
            else{
                $str = "update ".$dbname.".keu_5saldotbsramp set fisik='".$totalkg."',hargarata='".$reshargarata."' where kodeorg='".$pt."' and unit='".$kodepabrik."' and koderamp='".$koderamp."' and tanggal='".$tanggal."'";
            }
            try{
                $owlPDO->exec($str); 
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }
            
            #Update flag posting
            $str = "update ".$dbname.".pmn_penerimaantbsramp set posted='1', postedby='".$_SESSION['standard']['userid']."', posteddate='".tanggalsystem(date('d-m-Y'))."' where notiket='".$param['notiket']."'";
            try{
                $owlPDO->exec($str); 
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }
            $queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpC+1),$whereNoindukph);
            $errCounter = "";
            try{
                $owlPDO->exec($queryJ); 
            }
            catch (PDOException $e) { 
                $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; 
            }
            
            if($errCounter!="") {
                $queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),$whereNoindukph);
                $errCounter = "";
                try{
                    $owlPDO->exec($queryJRB); 
                }
                catch (PDOException $e) { 
                    $errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; 
                }
                echo "DB Error :\n".$errorJRB;
                exit;
            }
        }
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
        $optSupp2=makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$rHeader[0]['kodesupplier']."'");
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
        $tab.="<tr><td>".$_SESSION['lang']['namasupplier']."</td><td>:</td><td>".($optSupp2[$rHeader[0]['kodesupplier']]==''?$optSupp[$rHeader[0]['kodesupplier']]:$optSupp2[$rHeader[0]['kodesupplier']])."</td></tr>";
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
<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
//$proses=$_GET['proses'];

$proses=checkPostGet('proses','');

if(isset($_POST['periode'])){
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
        
$rowspDt='';   
        $where="";
        if($proses=='preview'){
            if($param['kdOrg']==''){
                exit('warning: '.$_SESSION['lang']['pt'].' '.$_SESSION['lang']['kosong']);
            }
            if($param['komoditi']==''){
                exit('warning: '.$_SESSION['lang']['komoditi'].' '.$_SESSION['lang']['kosong']);
            }
            if($param['nodo']==''){
                exit('warning: '.$_SESSION['lang']['nodo'].' '.$_SESSION['lang']['kosong']);
            }
            if($param['kgBongkar']==''){
                exit('warning: '.$_SESSION['lang']['kgbongkar'].' '.$_SESSION['lang']['kosong']);
            }
            if($param['persenPajak']==''){
                exit('warning: '.$_SESSION['lang']['persen']." ".$_SESSION['lang']['pajak'].' '.$_SESSION['lang']['kosong']);
            }
            if($param['persenPajak']!=0){
                if($param['statPPh']==''){
                    exit('warning: '.$_SESSION['lang']['pphditanggung'].' '.$_SESSION['lang']['kosong']);
                }
            }
            //$where="left(tanggal,7)='".$param['periode']."' and noreferensi='".$param['nodo']."'";
            $where="noreferensi='".$param['nodo']."'";
            $sRpKg="select * from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$param['nodo']."'";
            $rRpKg=fetchData($sRpKg);
            $rupiahPerKg=$rRpKg[0];
            $rpPajak=0;
            $ongkosAng=$param['kgBongkar']*$rupiahPerKg['harga'];
            if($param['persenPajak']!=0){
                $rpPajak=$ongkosAng*$param['persenPajak']/100;
                if($param['statPPh']==1){
                    $rpPajak=(($ongkosAng*(100/(100-$param['persenPajak'])))*$param['persenPajak'])/100;
                }
            }
            $rData=array();
            $sData="select sum(debet) as debet,sum(kredit) as kredit,noakun from ".$dbname.".keu_jurnaldt_vw where ".$where." and nojurnal not like '%M%' group by noakun order by debet desc";
            $rData=fetchdata($sData);
            if(count($rData)!=0){
                $tab.="<table cellpadding=1 cellspacing=1 border=".$brdr." class=sortable><thead>";
                $tab.="<tr class=rowheader>";
                $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['noakun']."</td>";
                $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['namaakun']."</td>";
                $tab.="<td  colspan=2 align=center ".$bg.">Sebelum</td>
                       <td  colspan=2 align=center ".$bg.">Sesudah</td>";
                $tab.="<td rowspan=2 align=center ".$bg.">".$_SESSION['lang']['selisih']."</td>";
                $tab.="</tr>";
                $tab.="<tr class=rowheader>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['debet']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['kredit']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['debet']."</td>";
                $tab.="<td align=center ".$bg.">".$_SESSION['lang']['kredit']."</td>";
                $tab.="</tr></thead><tbody>"; 
                $tab.="<tr class=rowcontent>";
                $tab.="<td colspan=7 id=nodoData>".$param['nodo']."</td>";
                //$tab.="<td colspan=3>".$param['kgBongkar']."*".$rupiahPerKg['harga']."</td>";
                $tab.="</tr>"; 
                $totDebet=0;
                $totKredit=0;
                $totDbt=0;
                $totKrdt=0;   
                $jmlRowData=0;  
               foreach($rData as $row=>$lst){   
                $tab.="<tr class=rowcontent>";
                $nmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$lst['noakun']."'");
                $tab.="<td id=noakun_".$row.">".$lst['noakun']."</td>";
                $tab.="<td>".$nmAkun[$lst['noakun']]."</td>";
                $tab.="<td align=right>".number_format($lst['debet'],2)."</td>";
                $tab.="<td align=right>".number_format($lst['kredit'],2)."</td>";
                $totDebet+=$lst['debet'];
                $totKredit+=$lst['kredit'];
                if($lst['debet']!=0){
                    $rpHutang=$ongkosAng;
                    if($param['statPPh']==1){
                        $rpHutang=$ongkosAng+$rpPajak;
                    }
                    $totDbt+=$rpHutang;
                    $tab.="<td align=right>".number_format($rpHutang,2)."</td>";
                    $tab.="<td align=right>".number_format(0,2)."</td>";
                    $selisihData=$rpHutang-$lst['debet'];
                    $tab.="<td align=right id=selisih_".$row.">".number_format($selisihData,2)."</td>";    
                }else{
                    
                    if($lst['noakun']=='2111201'){
                        $rpHutang=$ongkosAng;
                        if($param['statPPh']!=1){
                            $rpHutang=$ongkosAng-$rpPajak;
                        }
                        $tab.="<td align=right>".number_format(0,2)."</td>";
                        $tab.="<td align=right>".number_format($rpHutang,2)."</td>";
                        $selisihData=$rpHutang-$lst['kredit'];
                        $totKrdt+=$rpHutang;
                        $tab.="<td align=right id=selisih_".$row.">".number_format($selisihData,2)."</td>";      
                    }
                    if($lst['noakun']=='2120300'){
                        $tab.="<td align=right>".number_format(0,2)."</td>";
                        $tab.="<td align=right>".number_format($rpPajak,2)."</td>";
                        $selisihData=$rpPajak-$lst['kredit'];
                        $tab.="<td align=right id=selisih_".$row.">".number_format($selisihData,2)."</td>";
                        $totKrdt+=$rpPajak;
                    }
                    $jmlRowData+=1;
                }
                
                $tab.="</tr>";
               }
                $tab.="<tr>";
                $tab.="<td colspan=2>&nbsp;</td>";
                $tab.="<td align=right>".number_format($totDebet,2)."</td>";
                $tab.="<td align=right>".number_format($totKredit,2)."</td>";
                $tab.="<td align=right>".number_format($totDbt,2)."</td>";
                $tab.="<td align=right>".number_format($totKrdt,2)."</td>";
                $tab.="</tbody>";
            }else{
                    $tab.="<tr class=rowcontent align=center><td colspan=6>".$_SESSION['lang']['tidakditemukan']."</td></tr>";
            }
            $tab.="</table>";
            $tab.="<button class=mybutton id='tombolPosting'  onclick=postingData(".$jmlRowData.")>".$_SESSION['lang']['proses']."</button>";    
        }
        
        


switch($proses) {
    case'preview':
                echo $tab;
                break;

    case'addDetail':
    #ambil data dari jurnal sebelumnya
    $sDtJur="select distinct kodeorg,kodesupplier,kodebarang,kodecustomer from ".$dbname.".keu_jurnaldt_vw where noreferensi='".$param['nodo']."'";
    $rDtJur=fetchData($sDtJur);
    $dataJurnal=$rDtJur[0];
        $kodejurnal="M";
        $kdPt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$dataJurnal['kodeorg']."'");
        $sKonter="select nokounter from ".$dbname.".keu_5kelompokjurnal where kodeorg='".$kdPt[$dataJurnal['kodeorg']]."'
                  and kodekelompok='".$kodejurnal."'";
        $rKonter=fetchdata($sKonter);
        $nokounter=$rKonter[0]['nokounter'];
        $dataH[0]['unit']=$dataJurnal['kodeorg'];
        $tempTgl='';
        if($param['tglJurnal']==''){
            exit('warning:'.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['jurnal']." ".$_SESSION['lang']['kosong']);
        }
        foreach($_POST['noakun'] as $row=>$lstDt){
                #buat metode delete insert agar jika salah akun debet bisa direvisi langsung
                #cari jurnal memo di jurnalht yang noreferensinya nodo
                if($row==0){
                    #hapus jurnal memo
                    $str=" select * from ".$dbname.".keu_jurnalht where noreferensi='".$param['nodo']."' and kodejurnal='M' ";
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

                    $dataH[0]['tanggal']=tanggalsystemn($param['tglJurnal']);
                    $tempTgl=tanggalsystemn($param['tglJurnal']);
                    #====cek periode
                    $tgl = str_replace("-","",$tempTgl);
                    $sPeriode="select * from ".$dbname.".setup_periodeakuntansi 
                               where kodeorg='".$dataJurnal['kodeorg']."' and tutupbuku=0 order by periode desc";
                    $rPeriode=fetchdata($sPeriode);
                    @$tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
                    if($tglakutansi>$tgl){
                        exit('Error:Date beyond active period ');
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
                        'noreferensi'=>$param['nodo'],
                        'autojurnal'=>'1',
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'revisi'=>'0'
                    );
                    $noUrut=1;
                }
                $_POST['rpSelisih'][$row]=str_replace(",","", $_POST['rpSelisih'][$row]);
            
                 if(intval(substr($lstDt,0,1))>4){
                        #debet
                        $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$dataH[0]['tanggal'],
                        'nourut'=>$noUrut,
                        'noakun'=>$lstDt,
                        'keterangan'=>"Adjust Selisih Biaya Transportir, No. DO : ".$param['nodo'],
                        'jumlah'=>$_POST['rpSelisih'][$row],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$dataH[0]['unit'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>$dataJurnal['kodebarang'],
                        'nik'=>'',
                        'kodecustomer'=>$dataJurnal['kodecustomer'],
                        'kodesupplier'=>$dataJurnal['kodesupplier'],
                        'noreferensi'=>$param['nodo'],
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>$param['nodo'],
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment' => ''
                        );
                        $noUrut++;
                 }else if(intval(substr($lstDt,0,1))<4){
                    $nmSup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$dataJurnal['kodesupplier']."'");
                    $rpJurnal=$_POST['rpSelisih'][$row];
                    if(substr($lstDt,0,3)=='211'){
                        $ketDat="Adjust Pengakuan Hutang atas jasa pengiriman, a/n ".$nmSup[$dataJurnal['kodesupplier']].", dengan No.DO :".$param['nodo'];
                        $rpJurnal=$_POST['rpSelisih'][$row]*(-1);
                    }
                    if(substr($lstDt,0,3)=='212'){
                        $nmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$lstDt."'");
                        $ketDat="Adjust Pengakuan ".$nmAkun[$lstDt]." atas jasa pengiriman, a/n ".$nmSup[$dataJurnal['kodesupplier']].", dengan No.DO :".$param['nodo'];
                        $rpJurnal=$_POST['rpSelisih'][$row]*(-1);
                        // if($_POST['rpSelisih'][$row]<0){
                        //     $rpJurnal=(-1)*($_POST['rpSelisih'][$row]);    
                        // }
                    }
                        #kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$dataH[0]['tanggal'],
                            'nourut'=>$noUrut,
                            'noakun'=>$lstDt,
                            'keterangan'=>$ketDat,
                            'jumlah'=>$rpJurnal,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$dataH[0]['unit'],
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>$dataJurnal['kodebarang'],
                            'nik'=>'',
                            'kodecustomer'=>$dataJurnal['kodecustomer'],
                            'kodesupplier'=>$dataJurnal['kodesupplier'],
                            'noreferensi'=>$param['nodo'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>$param['nodo'],
                            'kodeblok'=>'',
                            'revisi'=>'0',
                            'kodesegment' => ''
                        );
                        $noUrut++;
                 }
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
        $str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$nokounter."' where kodeorg='".$kdPt[$dataH[0]['unit']]."'
          and kodekelompok='".$kodejurnal."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    
        
    break;

    case'getNokontrak':
        if($param['ptId']==''){
            exit('warning: '.$_SESSION['lang']['pt'].' '.$_SESSION['lang']['kosong']);
        }

        $optNokontrak.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $whr="";
        if($param['ptId']!=''){
            $whr=" and millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['ptId']."' and tipe='PABRIK')";
        }
        // if($param['nokontrak']==''){
        //     $sKontark="select distinct nokontrak from ".$dbname.".pabrik_timbangan 
        //                where kodebarang='".$param['komoditi']."' and left(tanggal,7)='".$param['periode']."' ".$whr."";
        //     $rKontrak=fetchData($sKontark);
        //     foreach($rKontrak as $lsData=>$row){
        //         $optNokontrak.="<option value=".$row['nokontrak'].">".$row['nokontrak']."</option>";
        //     } 
        // }
        // if($param['nokontrak']!=''){
        $dtKas=array();
        // $sData="select noinvoice,nopo,notransaksi from ".$dbname.".keu_tagihanht a 
        //         left join ".$dbname.".keu_kasbankdt b on a.noinvoice=b.keterangan1
        //         where a.kodeorg='".$param['ptId']."' and tipeinvoice like 't%' and a.tanggal like '".substr($param['periode'],0,4)."%'";
        // $rData=fetchData($sData);
        // foreach($rData as $orw=>$dtInv){
        //     $dtKas[$dtInv['nopo']]=$dtInv['notransaksi'];
        // }
                $sKontark="select distinct nosipb from ".$dbname.".pabrik_timbangan 
                       where kodebarang='".$param['komoditi']."' and left(tanggal,7)='".$param['periode']."' ".$whr."";
                $rKontrak=fetchData($sKontark);
                foreach($rKontrak as $lsData=>$row){
                $sData="select * from ".$dbname.".keu_kasbankdt
                        where keterangan1 in (select noinvoice from ".$dbname.".keu_tagihanht where nopo='".$row['nosipb']."')";
                $rData=fetchData($sData);
                if(empty($rData)){
                        $optNokontrak.="<option value=".$row['nosipb'].">".$row['nosipb']."</option>";
                }
                
            } 
        //}
        
        echo $optNokontrak;
    break;
    
}
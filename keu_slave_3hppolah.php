<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$proses = $_GET['proses'];
$param = $_POST;
$tmpPeriod = tanggalsystemn($param['tanggal']);



switch($proses) {
        case 'list':
				$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$param['kodeorg']."'");
				$lktugas = $optOrg[$param['kodeorg']];

				if($lktugas!='PABRIK')
				{
					exit("warning : Lokasi tugas harus di pabrik");
				}
				
				
                $sCek="select * from ".$dbname.".pmn_penerimaantbsramp where unit='".$param['kodeorg']."' and left(datein,10)='".$tmpPeriod."' and posted=0";
                $rCek=fetchData($sCek);
                if(count($rCek)!=0){
                    $notiket='';
                    foreach($rCek as $row=>$lstData){
                        if($row!=0){
                            $notiket.="\n";
                        }
                        $notiket.=$lstData['notiket'];
                    }
                    echo $notiket."\n";
                    exit('warning: Ada Transaksi Penerimaan TBS Ramp belum terposting');
                }
                $kdAplikasi="HPPOLAH";
                #ambil noakun biaya transit
                $sAkun="select nilai from ".$dbname.".setup_parameterappl where kodeparameter='".$kdAplikasi."'";
                $rAkun=fetchData($sAkun);
                $lstNoakun=$rAkun[0]['nilai'];
                #cek inputan tagihan/kasbank
                $sTag="select distinct a.noinvoice as noinvoice from ".$dbname.".keu_tagihandt a left join 
                      ".$dbname.".keu_tagihanht b on a.noinvoice=b.noinvoice where unit='".$param['kodeorg']."' and tanggal='".$tmpPeriod."' and b.posting=0 and a.noakun in (".$lstNoakun.")";
                //echo $sTag;
                $rTag=fetchData($sTag);
                if(count($rTag)!=0){
                    $notiket='';
                    foreach($rTag as $row=>$lstData){
                        if($row!=0){
                            $notiket.="\n";
                        }
                        $notiket.=$lstData['noinvoice'];
                    }
                    echo $notiket."\n";
                    exit('warning: Ada Transaksi Tagihan HO belum terposting');
                }
                $sTag="select distinct a.notransaksi as noinvoice from ".$dbname.".keu_kasbankdt a left join 
                      ".$dbname.".keu_kasbankht b on a.notransaksi=b.notransaksi where a.kodeorg='".$param['kodeorg']."' and b.tanggal='".$tmpPeriod."' and b.posting=0 and b.noakun in (".$lstNoakun.")";
                $rTag=fetchData($sTag);
                if(count($rTag)!=0){
                    $notiket='';
                    foreach($rTag as $row=>$lstData){
                        if($row!=0){
                            $notiket.="\n";
                        }
                        $notiket.=$lstData['noinvoice'];
                    }
                    echo $notiket."\n";
                    exit('warning: Ada Transaksi Kas Bank '.$param['kodeorg'].' belum terposting');
                }
                
                #cek periode akuntansi
                $sPrd="select * from ".$dbname.".setup_periodeakuntansi where ".$tmpPeriod." between tanggalmulai and tanggalsampai and kodeorg='".$param['kodeorg']."'";
                $rPrd=fetchData($sPrd);
                if($rPrd[0]['tutupbuku']==1){
                    exit('warning: '.$_SESSION['lang']['unit'].':'.$param['kodeorg'].' sudah tutup buku');
                }

                #saldo awal tbs
                $tglKmrn=tglkemarin($tmpPeriod);
                $sAwal="select * from ".$dbname.".keu_5saldotbs where kodeunit='".$param['kodeorg']."' and tanggal='".$tglKmrn."'";
                $rAwal=fetchData($sAwal);

                #total rupiah
                $strH = "select noakun,sum(jumlah) as rpTrimaDbt from ".$dbname.".keu_jurnaldt_vw where 
                         kodeorg='".$param['kodeorg']."' and tanggal = '".$tmpPeriod."' and noakun in (".$lstNoakun.") and nojurnal not like '%HPPTB%' group by noakun";
                //echo $strH;
                $resH=fetchData($strH);
                foreach($resH as $row=>$val){
                    $lstData[$val['noakun']]['rupiah']=$val['rpTrimaDbt'];
                    $totalRpAll+=$val['rpTrimaDbt'];
                    $dtAkun[$val['noakun']]=$val['noakun'];
                }
               
                
                $strH = "select noakun,sum(debet) as rpTrimaDbt from ".$dbname.".keu_jurnaldt_vw where 
                         kodeorg='".$param['kodeorg']."' and tanggal = '".$tmpPeriod."' and noakun='1150302' and nojurnal not like '%HPPTB%'  group by noakun";
                $resH=fetchData($strH);
                $persediaanPabrik=$resH[0]['rpTrimaDbt'];
                #ambil tbs olah
                $sProduksi="select tanggal,sum(tbsdiolahnetto) as tbsolah,sum(tbsmasuknetto) as tbsmasuknetto from ".$dbname.".pabrik_produksi 
                            where kodeorg='".$param['kodeorg']."' and tanggal ='".$tmpPeriod."' group by tanggal order by tanggal asc";
                $rProduksi=fetchData($sProduksi);
                foreach ($rProduksi as $key => $val) {
                    $lstData[$val['tanggal']]['olah']=$val['tbsolah'];
                    $lstData[$val['tanggal']]['masuk']=$val['tbsmasuknetto'];
                    $totalTbsolah+=$val['tbsolah'];
                    $totalTbsMasuk+=$val['tbsmasuknetto'];
                    $lstTgl[$val['tanggal']]=$val['tanggal'];
                }
				
				if(isset($lstTgl))
					array_multisort($lstTgl);
				
                $totRow=count($dtAkun);
                if(count($lstTgl)==0) {
                        $attr = 'disabled=disabled';
                } else {
                        $attr = "onclick=postHppOlah(".$totRow.")";
                }
                $tab = "<button id=btnproses ".$attr.">".$_SESSION['lang']['proses']."</button>";
                $tab .= "<table class=sortable cellspacing=1 cellpadding=1><thead>";
                $tab .= "<tr class=rowheader>";
                $tab .= "<td align=center>".$_SESSION['lang']['tanggal']."</td>";
                $tab .= "<td align=center>TBS (Saldo Awal+Masuk)</td>";
                $tab .= "<td align=center>Rupiah</td>";
                $tab .= "<td align=center>Harga Rata</td>";
                $tab .= "<td align=center>Kg (Olah)</td>";
                $tab .= "<td align=center>Rp (Olah)</td>";
                $tab .= "<td align=center>Sisa Olah (Kg)</td>";
                $tab .= "</tr>";
                
                $tab .= "</thead><tbody>";
                #saldo awal
                $totalByRupiah=0;
                $totalTbsMasuk+=($rAwal[0]['fisik']-$rAwal[0]['fisikolah']);
                $tab.="<tr class=rowcontent>";
                $tab.="<td>Saldo Awal</td>";
                $tab.="<td align=right id=saldoawalDt>".($rAwal[0]['fisik']-$rAwal[0]['fisikolah'])."</td>";
                $rAwal[0]['rpawal']=($rAwal[0]['fisik']-$rAwal[0]['fisikolah'])*$rAwal[0]['hargarata'];
                $totalByRupiah=$rAwal[0]['rpawal'];
                $tab.="<td align=right>".$rAwal[0]['rpawal']."</td>";
                @$rpAwal=$rAwal[0]['rpawal']/$rAwal[0]['qtyawal'];
                $tab.="<td align=right>&nbsp;</td>";
                $tab.="<td colspan=3>&nbsp;</td>";
                $tab.="</tr>";
                $noalk=0;
                $totalTransit=0;
				if(isset($dtAkun))
                foreach($dtAkun as $lstakun){
                    $noalk+=1;
                    $whrAkun="noakun='".$lstakun."'";
                    $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrAkun);
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td id=dtNoakunAlk_".$noalk.">".$lstakun."</td>";
                    $tab.="<td >".$optNmAkun[$lstakun]."</td>";
                    
                    $tab.="<td align=right id=dtRupiahAlk_".$noalk.">".$lstData[$lstakun]['rupiah']."</td>";
                    $tab.="<td align=right>&nbsp;</td>";
                    $tab.="<td colspan=3>&nbsp;</td>";
                    $tab.="</tr>";
                    $totalByRupiah+=$lstData[$lstakun]['rupiah'];
                    $totalTransit+=$lstData[$lstakun]['rupiah'];
                }
                $sisaOlah=array();
                $noitung=0;
				if(isset($lstTgl))
                foreach($lstTgl as $dtTgl){
                    $noitung+=1;
                    // if(substr($dtTgl,-2,2)=='01'){
                    //     $rupiahTbs=$rAwal[0]['rpawal']+$lstData[$dtTgl]['rupiah'];
                     $fisikTbs=($rAwal[0]['fisik']-$rAwal[0]['fisikolah'])+$lstData[$dtTgl]['masuk'];
                    //     @$hargaRata[$dtTgl]=$rupiahTbs/$fisikTbs;
                    //     $sisaOlah[$dtTgl]=$fisikTbs-$lstData[$dtTgl]['olah'];
                    // }else{
                    //     $tglSbm=tglkemarin($dtTgl);
                    //     $awalTbs=$sisaOlah[$tglSbm]*$hargaRata[$tglSbm];
                    //     $rupiahTbs=$awalTbs+$lstData[$dtTgl]['rupiah'];
                    //     $fisikTbs=$sisaOlah[$tglSbm]+$lstData[$dtTgl]['masuk'];
                    //     $sisaOlah[$dtTgl]=$fisikTbs-$lstData[$dtTgl]['olah'];
                    //     @$hargaRata[$dtTgl]=$rupiahTbs/$fisikTbs;;
                    // }
                    $hargaRata[$dtTgl]=($totalByRupiah+$persediaanPabrik)/($lstData[$dtTgl]['masuk']+($rAwal[0]['fisik']-$rAwal[0]['fisikolah']));
                    $sisaOlah[$dtTgl]=$fisikTbs-$lstData[$dtTgl]['olah'];
                    $tab.="<tr class=rowcontent id=row".$noitung.">";
                    $tab.="<td id=tanggalDt_".$noitung.">".$dtTgl."</td>";
                    $tab.="<td align=right>".($rAwal[0]['fisik']-$rAwal[0]['fisikolah'])."+".$lstData[$dtTgl]['masuk']."</td>";
                    $tab.="<td align=right>".$persediaanPabrik."</td>";
                    $tab.="<td align=right>".$hargaRata[$dtTgl]."</td>";
                    $tab.="<td align=right  id=kgOlah_".$noitung.">".$lstData[$dtTgl]['olah']."</td>";
                    $rpOlahDt[$dtTgl]=$lstData[$dtTgl]['olah']*$hargaRata[$dtTgl];
                    $tab.="<td align=right  id=rpOlah_".$noitung.">".$rpOlahDt[$dtTgl]."</td>";
                    $tab.="<td align=right>".$sisaOlah[$dtTgl]."</td>";
                    $tab.="</tr>";
                    $totRpOlah+=$rpOlahDt[$dtTgl];
                    $totalTbsolah=$lstData[$dtTgl]['olah'];
                }
                $tab.="<tr>";
                $tab.="<td>Total</td>";
                $tab.="<td align=right id=dtTbsAkhir>".$totalTbsMasuk."</td>";
                $tab.="<td align=right>".($totalByRupiah+$persediaanPabrik)."</td>";
                $tab.="<td align=right id=hargaRata>".$hargaRata[$dtTgl]."</td>";
                $tab.="<td align=right id=tbsOlah>".$totalTbsolah."</td>";
                $tab.="<td align=right id=rupiahOlah>".$totRpOlah."</td>";
                $sisaTbsAkhir=$totalTbsMasuk-$totalTbsolah;
                $tab.="<td align=right>".$sisaTbsAkhir."</td></tr>";
                $tab .= "</tbody></table><input type=hidden id=totalByRupiah value='".$totalTransit."' />";
                echo $tab;
                break;
                case 'post':
                // echo"<pre>";
                // print_r($_POST);
                // echo"</pre>";
                // exit('warning');
                #======================== Nomor Jurnal =============================
                # Get Journal Counter
                $konter ='001';
                $tanggal=$param['tanggalDt'];
                $kodeJurnal="HPPTB";
                $sAkun="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodeJurnal."'";
                $rAkun=fetchData($sAkun);
                $debet=$rAkun[0]['noakundebet'];
                $kredit=$rAkun[0]['noakunkredit'];
                $param['keterangan']="HPP OLAH TBS Unit : ".$param['kodeorg'].", : ".number_format($param['kgOlah'],0)." (kg), Tanggal :".$tanggal;
                # Transform No Jurnal dari No Transaksi
                $nojurnal = str_replace("-","",$tanggal)."/".substr($param['kodeorg'],0,4)."/".$kodeJurnal."/".$konter;
                #======================== /Nomor Jurnal ============================
                $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($RBDet); }
                catch (PDOException $e) {
                    echo "Rollback Delete Header Error : " . $e->getMessage();
                     exit;
                } 
                // Default Segment
                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

                # Prep Header
                $dataRes['header'] = array(
                        'nojurnal'=>$nojurnal,
                        'kodejurnal'=>$kodeJurnal,
                        'tanggal'=>$tanggal,
                        'tanggalentry'=>date('Ymd'),
                        'posting'=>1,
                        'totaldebet'=>$param['rpOlah'],
                        'totalkredit'=>-1*$param['rpOlah'],
                        'amountkoreksi'=>'0',
                        'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                        'autojurnal'=>'1',
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'revisi'=>'0'
                );


                # Data Detail
                $noUrut = 1;
                # Debet Alokasi biaya ke persediaan TBS unit : SRLM, tanggal :2017-03-01
                if(!empty($_POST['noakunAlk'])){
                    $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$kredit,
                        'keterangan'=>"Persediaan TBS Pabrik dari biaya transit dan tbs ramp UNIT : ".$param['kodeorg'].", Tanggal ".$tanggal,
                        'jumlah'=>$param['totalByRupiah'],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$param['kodeorg'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>'',
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment'=>$defSegment
                    );
                    foreach($_POST['noakunAlk'] as $barisdt=>$isiData){
                        $noUrut+=1;
                        $whrPrmApl="kodeparameter='".$isiData."'";
                        $akunKred=makeOption($dbname,'setup_parameterappl',"kodeparameter,nilai",$whrPrmApl);
                        $kreditdetail=$isiData;
                        if($akunKred[$isiData]!=''){
                            $kreditdetail=$akunKred[$isiData];
                        }
                        $whrAkun="noakun='".$kreditdetail."'";
                        $optNmAkun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrAkun);
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tanggal,
                            'nourut'=>$noUrut,
                            'noakun'=>$kreditdetail,
                            'keterangan'=>"Persediaan TBS Pabrik dari biaya ".$optNmAkun[$kreditdetail]." ramp UNIT : ".$param['kodeorg'].", Tanggal ".$tanggal,
                            'jumlah'=>$_POST['rupiahAlk'][$barisdt]*(-1),
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$param['kodeorg'],
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
                            'kodesegment'=>$defSegment
                        );
                    }
                    $noUrut+=1;
                }
                
                # Debet
                $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$debet,
                        'keterangan'=>$param['keterangan'],
                        'jumlah'=>$param['rpOlah'],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$param['kodeorg'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>'',
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment'=>$defSegment
                );
                $noUrut++;

                # Kredit
                $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$kredit,
                        'keterangan'=>$param['keterangan'],
                        'jumlah'=>-1*$param['rpOlah'],
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$param['kodeorg'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>$kodeJurnal.":".str_replace("-","",$tanggal),
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>'',
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment'=>$defSegment
                );
                $noUrut++;
                #===========EXECUTE
                $insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                try{$owlPDO->exec($insHead); }
                catch (PDOException $e) {
                    $headErr .= "Insert Header Error :" . $e->getMessage();
                }   
                if(empty($headErr)) {
                #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
                $detailErr = '';
                foreach($dataRes['detail'] as $row) {
                    $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                        try{$owlPDO->exec($insDet); }
                        catch (PDOException $e) {
                            $detailErr .= "Insert Detail Error : " . $e->getMessage();
                            break;
                        }                 
                }

                if($detailErr=='') {
                    # Header and Detail inserted
                    #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal

                }else {
                echo $detailErr;
                     # Rollback, Delete Header
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    try{$owlPDO->exec($RBDet); }
                    catch (PDOException $e) {
                        echo "Rollback Delete Header Error : " . $e->getMessage();
                         exit;
                    }               
                }
            } else {
                echo $headErr;
                exit;
            }    
            
                $rpPerkg=$param['hargaRata'];
                $tbsMasuk=$param['dtTbsAkhir'];
                $tbsOlah=$param['kgOlah'];
                $del="delete from ".$dbname.".keu_5saldotbs where kodeunit='".$param['kodeorg']."' and tanggal='".$tanggal."'";
                $owlPDO->exec($del); 
                $sInsert="insert into ".$dbname.".keu_5saldotbs (`kodeunit`,`tanggal`,`fisik`,`hargarata`,`fisikolah`,`updateby`) values ('".$param['kodeorg']."','".$tanggal."','".$tbsMasuk."','".$rpPerkg."','".$tbsOlah."','".$_SESSION['standard']['userid']."')";
                try{$owlPDO->exec($sInsert); }
                catch (PDOException $e) {
                    $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                    try{$owlPDO->exec($RBDet); }
                    catch (PDOException $e) {
                        echo "Rollback Delete Header Error : " . $e->getMessage();
                         exit;
                    }  
                    echo "Rollback Delete Header Error : " . $e->getMessage()."__".$sInsert;
                     exit;
                }   

        break;
}


                    
                
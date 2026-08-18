<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$proses = $_GET['proses'];
$param = $_POST;
$tmpPeriod = explode('-',$param['periode']);
$tahunbulan = implode("",$tmpPeriod);
$maxDay = cal_days_in_month(CAL_GREGORIAN,$tmpPeriod[1],$tmpPeriod[0]);


switch($proses) {
        case 'list':
                $lstSup=array();
                $lstData=array();
                // Get Data
                $strH = "select left(kodesupplier,5) as kodesupplier,sum(debet) as rpKirim from ".$dbname.".keu_jurnaldt_vw where 
                         kodeorg='".$param['kodeorg']."' and tanggal like '".$param['periode']."%' and noakun='1150301' and kredit=0 and nojurnal not like '%DTBRM%' group by left(kodesupplier,5)";
                //echo $strH;
                $resH=fetchData($strH);
                foreach ($resH as $key => $barH) {
                          $lstSup[$barH['kodesupplier']]=$barH['kodesupplier'];
                          $lstData[$barH['kodesupplier']]['rpKirim']=$barH['rpKirim'];
                }
                $strH = "select left(kodesupplier,5) as kodesupplier,sum(kredit) as rpTrima from ".$dbname.".keu_jurnaldt_vw where 
                         kodeorg='".$param['kodeorg']."' and tanggal like '".$param['periode']."%' and noakun='1150301' and debet=0  and nojurnal not like '%DTBRM%' group by left(kodesupplier,5)";
                //echo $strH;
                $resH=fetchData($strH);
                foreach ($resH as $key => $barH) {
                          $lstSup[$barH['kodesupplier']]=$barH['kodesupplier'];
                          $lstData[$barH['kodesupplier']]['rpTrima']=$barH['rpTrima'];
                }
                if(empty($lstSup)) {
                        $attr = array('disabled'=>'disabled');
                } else {
                        $totRow=count($lstSup);
                        $attr = array('onclick'=>"postSelisihRamp(".$totRow.")");
                }
                $tab = makeElement($dbname,'button',$_SESSION['lang']['posting'], $attr);
                $tab .= "<table class=data><thead>";
                $tab .= "<tr class=rowheader>";
                $tab .= "<td align=center>Kode Ramp</td>";
                $tab .= "<td align=center>Ramp</td>";
                $tab .= "<td align=center>Pabrik</td>";
                $tab .= "<td align=center>Selisih</td>";
                $tab .= "</tr>";
                
                $tab .= "</thead><tbody>";
                $rpkg=array();
                foreach($lstSup as $dtSup){
                    $no+=1;
                    $tab .= "<tr class=rowcontent>";
                    $tab .= "<td id=rampId_".$no.">".$dtSup."</td>";
                    $tab .= "<td align=right>".$lstData[$dtSup]['rpKirim']."</td>";
                    $tab .= "<td align=right>".$lstData[$dtSup]['rpTrima']."</td>";
                    $selisih[$dtSup]=$lstData[$dtSup]['rpKirim']-$lstData[$dtSup]['rpTrima'];
                    $tab .= "<td align=right id=selisihRp_".$no.">".$selisih[$dtSup]."</td>";
                    $tab .= "</tr>";
                }
                
                $tab .= "</tbody></table>";
                echo $tab;
                break;
        case 'post':
        #nojurnal 20170301/IGHO/BK/006
            $tmpKonter=0;
            #ambil akun
            $sAkun="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='INVRP'";
            $rAkun=fetchData($sAkun);
            $akundebet=$rAkun[0]['noakunkredit'];
            $akunkredit=$rAkun[0]['noakundebet'];
            $dtKbn=$param['kodeorg'];
           
            $sDt="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."'";
            $rDt=fetchData($sDt);
            $dataRes['header']=array();
            $dataRes['detail']=array();
            $tmpKonter=0;
            for($row=0;$row<count($_POST['kdSup']);$row++){
                // # Transform No Jurnal dari No Transaksi
                $tmpKonter=$tmpKonter+1;
                $isiDt=$_POST['kdSup'][$row];
                $konter = addZero($tmpKonter,3);
                $tgmulaid=$rDt[0]['tanggalsampai'];
                $nojurnal = str_replace("-","",$tgmulaid)."/".$param['kodeorg']."/DTBRM/".$konter;
                $sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
                $owlPDO->exec($sDel);
                
                #======================== /Nomor Jurnal ============================
                # Prep Header
                    $param['jumlah']=$_POST['rpSup'][$row];
                    $dataRes['header'][] = array(
                            'nojurnal'=>$nojurnal,
                            'kodejurnal'=>'DTBRM',
                            'tanggal'=>$tgmulaid,
                            'tanggalentry'=>date('Ymd'),
                            'posting'=>1,
                            'totaldebet'=>$param['jumlah'],
                            'totalkredit'=>-1*$param['jumlah'],
                            'amountkoreksi'=>'0',
                            'noreferensi'=>'DTBRM_'.$isiDt.'_'.$param['periode'],
                            'autojurnal'=>'1',
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'revisi'=>'0'                  
                            );   
                     # Debet 1
                    $noUrut=1;
                        # Debet
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>$tgmulaid,
                            'nourut'=>$noUrut,
                            'noakun'=>$akundebet,
                            'keterangan'=>$param['periode'].': Selisih Antara Persediaan TBS RAMP dengan TBS Pabrik '.$param['kdrumah'],
                            'jumlah'=>$param['jumlah'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$dtKbn,
                            'kodekegiatan'=>$lstKeg,
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'0',
                            'kodecustomer'=>'',
                            'kodesupplier'=>$isiDt,
                            'noreferensi'=>'DTBRM_'.$isiDt.'_'.$param['periode'],
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
                            'tanggal'=>$tgmulaid,
                            'nourut'=>$noUrut,
                            'noakun'=>$akunkredit,
                            'keterangan'=> $param['periode'].': Selisih Antara Persediaan TBS RAMP dengan TBS Pabrik '.$param['kdrumah'],
                            'jumlah'=>-1*$param['jumlah'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$dtKbn,
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>'DTBRM_'.$isiDt.'_'.$param['periode'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                            'revisi'=>'0',
                            'kodesegment'=>$defSegment);
                            $noUrut++;
            }
            
                $headerErr="";
                $rowke=0;
                foreach($dataRes['header'] as $row) {
                    $insHead = insertQuery($dbname,'keu_jurnalht',$row);
                    //exit('warning'.$insHead);
                    try{
                        $owlPDO->exec($insHead); 
                    }catch (PDOException $e){
                        $headerErr .= "Gagal : ".$e->getMessage()."\n".$insHead;
                        break;
                    }
                    if($headerErr!=''){
                        echo $headerErr;
                        # Rollback, Delete Header
                        $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$row['nojurnal']."'");
                        try{
                            $owlPDO->exec($RBDet); 
                        }catch (PDOException $e){
                            echo "Rollback Delete Header Error : ".$e->getMessage();
                            exit;
                        }
                        exit();
                    }
                }
                foreach($dataRes['detail'] as $row){
                        $detailErr="";
                        $insDet = insertQuery($dbname,'keu_jurnaldt',$row);
                        try{
                            $owlPDO->exec($insDet); 
                        }catch (PDOException $e){
                            $detailErr .= "Gagal : ".$e->getMessage()."\n".$insDet;
                            break;
                        }
                        if($detailErr!=''){
                            echo $detailErr;
                            # Rollback, Delete Header
                            $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$row['nojurnal']."'");
                            try{
                                $owlPDO->exec($RBDet); 
                            }catch (PDOException $e){
                                echo "Rollback Delete Header Error : ".$e->getMessage();
                                exit;
                            }
                            exit();
                        }
                }
            
        break;
}


                    
                
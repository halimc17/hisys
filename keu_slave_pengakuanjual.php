<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zJournal.php');

$param = $_POST;

// $proses = $_GET['proses'] or '';
// print_r($param);
// exit("Error:MASUK");
$proses = checkPostGet('proses', '');
$tpDisplay = checkPostGet('tpDisplay', '');

if($proses=='listExcel'){
        $param = $_GET;
}

if($tpDisplay=='excel'){
        $param = $_GET;
}


$nmcust=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$arrDataFranco=makeOption($dbname,"pmn_5franco","id_franco,penjualan");

switch($proses) { 
         case 'list':
                $arrKontrak=array();
                $tanggal1 = tanggalsystemn($param['tanggal1']);
                $tanggal2 = tanggalsystemn($param['tanggal2']);
                if($tanggal1 > $tanggal2) exit("Warning: Tanggal awal tidak boleh dari tanggal akhir");
                if($param['komoditi']!='0'){
                        @$whdt.=" and a.kodebarang='".$param['komoditi']."'";
                }
                if($param['kdpt']!=''){
                        if($param['komoditi']!='0'){
                                $whdt="";
                                $whr="";
                                $whr="and kodebarang='".$param['komoditi']."'";
                        }
                        if($param['komoditi']=='40000003'){
                            $whdt="and (a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual_vw where kodept='".$param['kdpt']."' ".$whr.") or a.nokontrak in (select nokontrakexternal from ".$dbname.".pmn_kontrakjual_vw where kodept='".$param['kdpt']."' ".$whr.") or a.nokontrak in (select nokontrakinternal from ".$dbname.".pmn_kontrakjual_vw where kodept='".$param['kdpt']."' ".$whr.")) ";
                        }
                }
                if($param['nokontrak']!=''){
                        $whdt.="  and a.nokontrak like '%".$param['nokontrak']."%'";
                }
                // Get Data
                $strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='ES' and kodeparameter='ESEXT'";
                @$resap = fetchData($strap);
                $tglCari=" left(a.tanggal,10)";
                if($param['komoditi']=='40000003'){
                        $tglCari=" left(a.tanggalpks,10)";
                }
                    
                $lstData=array();
                if($param['komoditi']=='40000003'){
                    $qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*,e.nodo as pmndo, a.kodeorg as kodeorgx, ".$tglCari." as tanggalx 
                            FROM ".$dbname.".pabrik_timbangan a
                            LEFT JOIN ".$dbname.".pmn_kontrakjual_vw d on a.nokontrak = d.nokontrak or a.nokontrak=d.nokontrakexternal or a.nokontrak=d.nokontrakinternal
                            LEFT JOIN ".$dbname.".log_5suptimbangan f on a.kodecustomer = f.kodetimbangan
                            LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on a.nodo = e.nodo
                            LEFT JOIN ".$dbname.".log_5supplier b on e.transportir = b.supplierid
                            LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang
                            WHERE d.termbayar!='PK' and ".$tglCari." between '".@$tanggal1."' and '".@$tanggal2."'  ".@$whdt." and a.millcode = '".$param['pabrik']."' order by ".$tglCari." asc";
                }else{
                    $qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*,a.nosipb as pmndo, a.kodeorg as kodeorgx, if((a.kodeorg in ('".$resap[0]['nilai']."')),(a.tanggalpks),(a.tanggal)) as tanggalx 
                        FROM ".$dbname.".pabrik_timbangan a
                        INNER JOIN ".$dbname.".pmn_kontrakjual_vw d on a.nokontrak = d.nokontrak or a.nokontrak=d.nokontrakinternal
                        LEFT JOIN ".$dbname.".log_5suptimbangan f on a.kodecustomer = f.kodetimbangan
                        LEFT JOIN ".$dbname.".log_5supplier b on f.supplierid = b.supplierid
                        LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang
                        LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on a.nosipb = e.nodo
                        WHERE d.termbayar!='PK' and left(a.tanggal,10) between '".@$tanggal1."' and '".@$tanggal2."'  ".@$whdt." and a.millcode = '".$param['pabrik']."' order by left(tanggal,10) asc";
                }
                //echo $qData;
                $resData = fetchData($qData);
                foreach($resData as $row=>$lst){
                    @$nilaix=0;
                            @$lokasi = explode(',', $resap[0]['nilai']);
                            if(count($lokasi)!=0){
                                foreach (@$lokasi as $key => $val) {
                                    if($val==$lst['kodeorgx'])
                                    {
                                        $nilaix=1;
                                    }
                                }
                            }
                            
                    $tmpTgl = explode(' ',$lst['tanggalx']);
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['nokontrak']=$lst['nokontrak'];
                    #ambil kontrak
                    $scek3=$owlPDO->query("select noinvoice,jenis from ".$dbname.".keu_penagihanht where nokontrak='".$lst['nokontrak']."'");
                    $scek3->setFetchMode(PDO::FETCH_ASSOC);
                    $rcek3=$scek3->fetch();
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['noinvoice']=$rcek3['noinvoice'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['statppn']=$lst['ppn'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['namasupp']=$lst['namasupplier'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['namabrg']=$lst['namabarang'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['kdbrg']=$lst['kodebarang'];

                    if ($rcek3['noinvoice']=='') {
                        $scek3=$owlPDO->query("select noinvoice,jenis from ".$dbname.".keu_penagihanht where nokontrak='".$lst['nokontrakinternal']."'");
                        $scek3->setFetchMode(PDO::FETCH_ASSOC);
                        $rcek3=$scek3->fetch();
                        $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['noinvoice']=$rcek3['noinvoice'];
                    }
                    
                    
                    if($nilaix==1){
                        if($lst['kodebarang']=='40000003'){
                            $lst['beratbersih']=$lst['beratbersihpmks']-$lst['kgpotsortasi'];
                            if($lst['kgpembeli']!=0){
                                $lst['beratbersih']=$lst['kgpembeli'];
                            }
                            //$lst['kgpembeli']=$lst['beratbersihpmks']-$lst['kgpotsortasi'];
                            //$lst['beratbersih']=$lst['beratbersihpmks']-$lst['kgpotsortasi'];
                        }
                        // $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['qtypabrik']+=$lst['beratbersihpmks'];
                        @$lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['qtypabrik']+=$lst['beratbersih'];
                    }else{
                        if($lst['kodebarang']=='40000003'){
                            $lst['kgpembeli']=$lst['beratbersih']-$lst['kgpotsortasi'];
                            $lst['beratbersih']=$lst['beratbersih']-$lst['kgpotsortasi'];
                        }
                        @$lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['qtypabrik']+=$lst['beratbersih'];
                    }
                    @$lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['qtypembeli']+=$lst['kgpembeli'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['hrgsatuan']=$lst['hargasatuan'];
                    @$lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['pabrik']=$lst['millcode'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['nodo']=$lst['pmndo'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['franco']=$lst['franco'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['sat']=$lst['satuan'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['jnsinv']=$rcek3['jenis'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['tanggal']=$tmpTgl[0];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['tglpembeli']=$lst['tglpembeli'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['nokontrak']][$lst['pmndo']]['nokontrakinternal']=$lst['nokontrakinternal'];
                }
                // echo"<pre>";
                // print_r($lstData);
                // echo"</pre>";
                $brd=0;
                $bgWrn="";
                if($param['tpDisplay']=='preview'){
                    // $tab = "<img src='images/excel.jpg' class='resicon' title='MS Excel' onclick=getExcel(event,'keu_slave_pengakuanjual.php','excel')>";    
                    $tab = "<img src='images/excel.jpg' class='resicon' title='MS Excel' onclick=list('excel',event)>";    
                }

                if ($param['tpDisplay']=='excel') {
                    $brd=1;
                }
                
                $tab.="<table class=sortable border=".$brd." cellspacing=1 cellpadding=1>";
                $tab .= "<thead><tr class=rowheader>";
                $tab .= "<td ".$bgWrn." align=center>".$_SESSION['lang']['tanggal']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['pabrik']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['NoKontrak']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['noinvoice']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['nodo']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['ppn']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['namasupplier']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['namabarang']."</td>";
                $tab .= "<td ".$bgWrn."  align=center colspan=2>".$_SESSION['lang']['jumlah']."</td>";
               
                    if($lst['kodebarang']=='40000003'){
                        $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['hargasatuan']." transportir</td>";
                    }
                
               
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['hargasatuan']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['totalharga']."</td>";
                if($param['tpDisplay']=='preview'){
                    $tab .= "<td align=center colspan=2>".$_SESSION['lang']['action']."</td>";
                }else{
                    $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['status']."</td>";
                }
                $tab .= "</tr></thead>";
				
                $tab .= "<tbody id=containdata>";
                $arrStPPn=array("0"=>"Exclude","1"=>"Include");
                $arrStJenis=array("UM"=>"Uang Muka","TM"=>"Tanpa Uang Muka");
                foreach($lstData as $row){
                    foreach($row as $lst){
                        foreach($lst as $data2){
                            foreach($data2 as $data){
                            $dtNokontrak=$data['nokontrak'];
                            if($param['komoditi']=='40000003'){
                                $data['franco']='loco';
                                if($data['nokontrak']!=$data['nokontrakinternal']){
                                    $dtNokontrak=$data['nokontrakinternal'];
                                }
                            }
                            $notrans=tanggalsystem(tanggalnormal($data['tanggal']))."##".$dtNokontrak."##".$data['nodo'];
                            $nodox=$data['nodo'];
                            $scek2=$owlPDO->query("select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual where notransaksi='".$notrans."'");
                            $scek2->setFetchMode(PDO::FETCH_ASSOC);
                            $rcek2=$scek2->fetch();
                            #cek data jika status belum posting
                            if($param['status']=='0'){
                                $sCekJrn="select distinct * from ".$dbname.".keu_jurnaldt where noreferensi='".$notrans."'";
                                $rCekJrn=fetchData($sCekJrn);
                                if(!empty($rCekJrn)){
                                    continue;
                                }
                            }
                            $no+=1;
                            $tab .= "<tr class=rowcontent>"; 
                            if($param['tpDisplay']=='preview'){
                                if(@$arrDataFranco[$data['franco']]=='franco'){
                                    if(($data['tglpembeli']=='0000-00-00')||($data['tglpembeli']=='')){
                                        $data['tglpembeli']=$data['tanggal'];
                                    }

                                    if($rcek2['posting']==1){
                                        $tab .= "<td>".makeElement('tanggal_'.$notrans,'date',
                                              tanggalnormal($data['tglpembeli']),array('style'=>'width:70px','disabled'=>'disabled'))."</td>";
                                    }else{
                                        $tab .= "<td>".makeElement('tanggal_'.$notrans,'date',
                                              tanggalnormal($data['tglpembeli']),array('style'=>'width:70px'))."</td>";  
                                    }
                                }else{
                                    $tab .= "<td align=center>".makeElement('tanggal_'.$notrans,'date',
                                            tanggalnormal($data['tanggal']),array('style'=>'width:70px','disabled'=>'disabled'))."</td>";
                                } 
                                
                                $tab .= "<td id=kdPabrik_".$no.">".$data['pabrik']."</td>";
                            
                            }else{
                                if($arrDataFranco[$data['franco']]=='franco'){
                                      if(($data['tglpembeli']=='0000-00-00')||($data['tglpembeli']=='')){
                                         $data['tglpembeli']=$data['tanggal'];
                                      }
                                      if($rcek2['posting']==1){
                                          $tab .= "<td>".$data['tglpembeli']."</td>";
                                      }else{
                                          $tab .= "<td>".$data['tglpembeli']."</td>";  
                                     }
                                }else{
                                    $tab .= "<td align=center>".$data['tanggal']."</td>";
                                } 
                                $tab .= "<td>".$data['pabrik']."</td>";
                            }
                            
                                $tab .= "<td>".$data['nokontrak']."<input type=hidden id=nokontrak_".$no." value='".$data['nokontrak']."' ></td>";
        
                                $tab .= "<td>".$data['noinvoice']."<input type=hidden id=noinvoice_".$no." value='".$data['noinvoice']."' ></td>";
                                $tab .= "<td>".$data['nodo']."<input type=hidden id=nodo_".$no." value='".$data['nodo']."' ></td>";
                                $tab .= "<td>".$arrStPPn[$data['statppn']]."<input type=hidden id=ppn_".$no." value='".$data['statppn']."' ></td>";
                                $tab .= "<td>".$data['namasupp']."<input type='hidden' id=notransaksi_".$no." value='".$notrans."' ></td>";
                                $tab .= "<td>".$data['namabrg']."<input type=hidden id=kdBarang_".$no." value='".$data['kdbrg']."' ></td>";
                        
                        
                                if($data['kdbrg']=='40000003'){
                                    $tab .= "<td align=right>".number_format($data['qtypabrik'])."
                                                <input type=hidden id=beratbersih_".$no." value='".$data['qtypabrik']."' ></td>";
                                }else{
                                    if($arrDataFranco[$data['franco']]=='franco' || $arrDataFranco[$data['franco']]=='fob'){
                                        $totalHarga=$data['hrgsatuan']*$data['qtypembeli'];
                                        $tab .= "<td align=right>".number_format($data['qtypembeli'])."
                                                <input type=hidden id=qty_".$no." value='".$data['qtypembeli']."' ></td>";
                                    }else{
                                        $totalHarga=$data['hrgsatuan']*$data['qtypabrik'];
                                        $tab .= "<td align=right>".number_format($data['qtypabrik'])."
                                                <input type=hidden id=qty_".$no." value='".$data['qtypabrik']."' ></td>";    
                                    }
                                }
                                                        
                            
                            $tab .= "<td>".$data['sat']."</td>";
                            
                                    if($data['kdbrg']=='40000003'){
                                        $sJrn="select nodo,harga from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$nodox."'";
                                          $rJrn=fetchdata($sJrn);
                                          @$hargax=$rJrn[0]['harga'];
                                          #ambil rupiah dari jurnaldt jika sudah diposting
                                          $sJrn="select jumlah,nojurnal from ".$dbname.".keu_jurnaldt where noreferensi='".$notrans."' and noakun like '5%'";
                                          $rJrn=fetchdata($sJrn);
                                          if(count($rJrn)!=0){
                                              $row['beratbersih']=$data['qtypabrik'];
                                              @$harg=($rJrn[0]['jumlah']*(-1))/$row['beratbersih'];
                                              if($data['statppn']==1){
                                                  $harg=($rJrn[0]['jumlah']+(($rJrn[0]['jumlah']*10)/100))/$row['beratbersih'];
                                              }
                                              $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatransportir_".$no."' onkeypress='return angka_doang(event)' style='width:100px;' value='".number_format($hargax)."' disabled=disabled /></td>";        
                                              
                                              $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatbs_".$no."' onkeypress='return angka_doang(event)' style='width:100px;' value='".number_format($harg)."' disabled=disabled /></td>";        
                                              $tab .= "<td align=right id=totharga_".$no.">".number_format(($rJrn[0]['jumlah']*(-1)))."</td>";
                                          }else{
                                              $dis='';
                                              if($nodox=='')
                                              {
                                              $dis='disabled=disabled';
                                              }
                                             $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatransportir_".$no."'   onkeypress='return angka_doang(event)' style='width:100px;' value='".number_format($hargax)."' ".$dis."/></td>";    
                                             $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatbs_".$no."' onchange=gethargabaris(".$no.");  onkeypress='return angka_doang(event)' style='width:100px;' value='' /></td>";    
                                             $tab .= "<td align=right id=totharga_".$no."></td>";
                                          } 
                                    }else{
                                            if($data['statppn']=='1'){
                                                $data['hrgsatuan']=$data['hrgsatuan']/1.1;
                                            }
                                            $tab .= "<td align=right>".number_format($data['hrgsatuan'])."<input type=hidden id=hrgsatuan_".$no." value='".$data['hrgsatuan']."' ></td>";    
                                            $tab .= "<td align=right>".number_format($totalHarga)."</td>";
                                    }
                            
                                    if($param['tpDisplay']=='preview'){
                                        if($rcek2['posting']==1){
                                             $tab .= "<td align=center><img class=zImgBtn id=imgPost_".$notrans." src='images/skyblue/posted.png'></td>";
                                             $tab .= "<td align=center>&nbsp;</td>";
                                        }else{
                                            $tab .= "<td align=center><img class=zImgBtn id=imgPost_".$notrans." src='images/skyblue/posting.png' title='Posting ".$data['tanggal']." ".$data['nokontrak']."  ".$data['nodo']." ' ".
                                                     "onclick=\"pilKontrak(this,'".$notrans."','".$data['pabrik']."','0','".$no."','".$data['nodo']."',event)\"></td>";
                                            if($arrDataFranco[$data['franco']]=='franco' || $arrDataFranco[$data['franco']]=='fob'){
                                                //if($data['qtypembeli']==0){
                                                        $tab .= "<td align=center><img class=zImgBtn id=imgPost_".$notrans." src='images/addplus.png' title='Tambahkan Kg Pembeli ".$data['tanggal']." ".$data['nokontrak']."  ".$data['nodo']."' ".
                                                         "onclick=\"getFormKgBeli('".$notrans."','".$data['pabrik']."',event)\"></td>";
                                                // }else{
                                                //     $tab .= "<td align=center>&nbsp;</td>";
                                                // }
                                            }else{
                                                $tab .= "<td align=center>&nbsp;</td>";
                                            }
                                        }
                                    }else{
                                        if($rcek2['posting']==1){
                                              $tab .= "<td align=center>".$_SESSION['lang']['posting']."</td>";
                                        }else{
                                               $tab .= "<td align=center>".$_SESSION['lang']['belumposting']."</td>";
                                        }
                                    }
                                    
                            
                            $tab .="</tr>";
                            }
                        }
                    }
                }
                $tab .= "</tbody>";
                $tab .= "</table>";
				$tab .= "<input id=els type=hidden value='".$no."'>";
                  if($param['tpDisplay']=='preview'){
                    echo $tab;
                  }
        
		
				
		
                if ($param['tpDisplay']=='excel') {
                    $dtwkt=date("YmdHis");
                    $nop_="pengakuanpenjualan_".$dtwkt;
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
                     }
                     else
                     {
                      echo "<script language=javascript1.2>
                            window.location='tempExcel/".$nop_.".xls';
                            </script>";
                     }
                    fclose($handle);
                    }
                }

        break;
        
        case 'listExcel':
                $tanggal1 = tanggalsystemn($param['tanggal1']);
                $tanggal2 = tanggalsystemn($param['tanggal2']);
                if($tanggal1 > $tanggal2) exit("Warning: Tanggal awal tidak boleh dari tanggal akhir");
                if($param['komoditi']!='0'){
                        $whdt.=" and a.kodebarang='".$param['komoditi']."'";
                }
                if($param['kdpt']!=''){
                        if($param['komoditi']!='0'){
                                $whdt="";
                                $whr="";
                                $whr="and kodebarang='".$param['komoditi']."'";
                        }
                        $whdt="and a.nokontrak in (select nokontrak from ".$dbname.".pmn_kontrakjual where kodept='".$param['kdpt']."' ".$whr.") ";
                }
                if($param['nokontrak']!=''){
                        $whdt.="  and a.nokontrak like '%".$param['nokontrak']."%'";
                }
                // Get Data
                $lstData=array();
                 if($param['komoditi']=='40000003'){
                    $qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*,a.nosipb as pmndo, a.kodeorg as kodeorgx, ".$tglCari." as tanggalx 
                            FROM ".$dbname.".pabrik_timbangan a
                            INNER JOIN ".$dbname.".pmn_kontrakjual_vw d on a.nokontrak = d.nokontrak or a.nokontrak=d.nokontrakexternal or a.nokontrak=d.nokontrakinternal
                            LEFT JOIN ".$dbname.".log_5suptimbangan f on a.kodecustomer = f.kodetimbangan
                            LEFT JOIN ".$dbname.".log_5supplier b on f.supplierid = b.supplierid
                            LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang
                            LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on a.nosipb = e.nodo
                            WHERE d.termbayar!='PK' and ".$tglCari." between '".@$tanggal1."' and '".@$tanggal2."'  ".@$whdt." and a.millcode = '".$param['pabrik']."' order by left(tanggal,10) asc";
                }else{
                    $qData = "SELECT a.*,b.namasupplier,c.namabarang,d.*,e.nodo as pmndo, a.kodeorg as kodeorgx, if((a.kodeorg in ('".$resap[0]['nilai']."')),(a.tanggalpks),(a.tanggal)) as tanggalx 
                        FROM ".$dbname.".pabrik_timbangan a
                        INNER JOIN ".$dbname.".pmn_kontrakjual_vw d on a.nokontrak = d.nokontrak 
                        LEFT JOIN ".$dbname.".log_5suptimbangan f on a.kodecustomer = f.kodetimbangan
                        LEFT JOIN ".$dbname.".log_5supplier b on f.supplierid = b.supplierid
                        LEFT JOIN ".$dbname.".log_5masterbarang c on a.kodebarang = c.kodebarang
                        LEFT JOIN ".$dbname.".pmn_suratperintahpengiriman e on a.nosipb = e.nodo
                        WHERE d.termbayar!='PK' and left(a.tanggal,10) between '".@$tanggal1."' and '".@$tanggal2."'  ".@$whdt." and a.millcode = '".$param['pabrik']."' order by left(tanggal,10) asc";
                }

                $resData = fetchData($qData);
                foreach($resData as $row=>$lst){
                    $tmpTgl = explode(' ',$lst['tanggal']);
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['nokontrak']=$lst['nokontrak'];
                    #ambil kontrak
                    $scek3=$owlPDO->query("select noinvoice,jenis from ".$dbname.".keu_penagihanht where nokontrak='".$lst['nokontrak']."'");
                    $scek3->setFetchMode(PDO::FETCH_ASSOC);
                    $rcek3=$scek3->fetch();
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['noinvoice']=$rcek3['noinvoice'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['statppn']=$lst['ppn'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['namasupp']=$lst['namasupplier'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['namabrg']=$lst['namabarang'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['kdbrg']=$lst['kodebarang'];

                    if ($rcek3['noinvoice']=='') {
                        $scek3=$owlPDO->query("select noinvoice,jenis from ".$dbname.".keu_penagihanht where nokontrak='".$lst['nokontrakinternal']."'");
                        $scek3->setFetchMode(PDO::FETCH_ASSOC);
                        $rcek3=$scek3->fetch();
                        $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['noinvoice']=$rcek3['noinvoice'];
                    }
                    
                    if($lst['kodebarang']=='40000003'){
                        $lst['kgpembeli']=$lst['beratbersih']-$lst['kgpotsortasi'];
                        $lst['beratbersih']=$lst['beratbersih']-$lst['kgpotsortasi'];
                    }
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['qtypabrik']+=$lst['beratbersih'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['qtypembeli']+=$lst['kgpembeli'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['hrgsatuan']=$lst['hargasatuan'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['pabrik']=$lst['millcode'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['nodo']=$lst['pmndo'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['franco']=$lst['franco'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['sat']=$lst['satuan'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['jnsinv']=$rcek3['jenis'];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['tanggal']=$tmpTgl[0];
                    $lstData[$tmpTgl[0]][$lst['kodebarang']][$lst['pmndo']]['tglpembeli']=$lst['tglpembeli'];
                } 
                $tab = "<table class=data border=1 cellspacing=1 cellpadding=1>";
                $tab .= "<thead><tr>";
                $tab .= "<td ".$bgWrn." align=center>".$_SESSION['lang']['tanggal']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['pabrik']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['NoKontrak']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['noinvoice']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['nodo']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['ppn']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['namasupplier']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['namabarang']."</td>";
                $tab .= "<td ".$bgWrn."  align=center colspan=2>".$_SESSION['lang']['jumlah']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['hargasatuan']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['totalharga']."</td>";
                $tab .= "<td ".$bgWrn."  align=center>".$_SESSION['lang']['status']."</td>";
                $tab .= "</tr></thead>";
                $tab .= "<tbody>";
                $arrStPPn=array("0"=>"Exclude","1"=>"Include");
                $arrStJenis=array("UM"=>"Uang Muka","TM"=>"Tanpa Uang Muka");
                foreach($lstData as $row){
                    foreach($row as $lst){
                        foreach($lst as $data){
                            $notrans=tanggalsystem(tanggalnormal($data['tanggal']))."##".$data['nokontrak']."##".$data['nodo'];
                            $scek2=$owlPDO->query("select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual where notransaksi='".$notrans."'");
                            $scek2->setFetchMode(PDO::FETCH_ASSOC);
                            $rcek2=$scek2->fetch();
                            #cek data jika status belum posting
                            if($param['status']=='0'){
                                $sCekJrn="select distinct * from ".$dbname.".keu_jurnaldt where noreferensi='".$notrans."'";
                                $rCekJrn=fetchData($sCekJrn);
                                if(!empty($rCekJrn)){
                                    continue;
                                }
                            }
                            $no+=1;
                            $tab .= "<tr class=rowcontent>"; 
                            if($arrDataFranco[$data['franco']]=='franco'){
                                  if(($data['tglpembeli']=='0000-00-00')||($data['tglpembeli']=='')){
                                     $data['tglpembeli']=$data['tanggal'];
                                  }
                                  if($rcek2['posting']==1){
                                      $tab .= "<td>".$data['tglpembeli']."</td>";
                                  }else{
                                      $tab .= "<td>".$data['tglpembeli']."</td>";  
                                 }
                            }else{
                                $tab .= "<td align=center>".$data['tanggal']."</td>";
                            } 
                            $tab .= "<td>".$data['pabrik']."</td>";
                            $tab .= "<td>".$data['nokontrak']."</td>";
                            $tab .= "<td>".$data['noinvoice']."</td>";
                            $tab .= "<td>".$data['nodo']."</td>";
                            $tab .= "<td>".$arrStPPn[$data['statppn']]."</td>";
                            $tab .= "<td>".$data['namasupp']."</td>";
                            $tab .= "<td>".$data['namabrg']."</td>";
                            if($data['kdbrg']=='40000003'){
                                $tab .= "<td align=right>".number_format($data['qtypabrik'])."</td>";
                            }else{
                                if($arrDataFranco[$data['franco']]=='franco'){
                                    $totalHarga=$data['hrgsatuan']*$data['qtypembeli'];
                                    $tab .= "<td align=right>".number_format($data['qtypembeli'])."</td>";
                                }else{
                                    $totalHarga=$data['hrgsatuan']*$data['qtypabrik'];
                                    $tab .= "<td align=right>".number_format($data['qtypabrik'])."</td>";    
                                }
                            }
                            $tab .= "<td>".$data['sat']."</td>";
                            if($data['kdbrg']=='40000003'){
                                  #ambil rupiah dari jurnaldt jika sudah diposting
                                  $sJrn="select nodo,harga from ".$dbname.".pmn_suratperintahpengiriman where nodo='".$nodox."'";
                                  $rJrn=fetchdata($sJrn);
                                  $hargax=$rJrn[0]['harga'];

                                  $sJrn="select jumlah,nojurnal from ".$dbname.".keu_jurnaldt where noreferensi='".$notrans."' and noakun like '5%'";
                                  $rJrn=fetchdata($sJrn);
                                  if(count($rJrn)!=0){
                                      $row['beratbersih']=$data['qtypabrik'];
                                      @$harg=($rJrn[0]['jumlah']*(-1))/$row['beratbersih'];
                                      if($data['statppn']==1){
                                          $harg=($rJrn[0]['jumlah']+(($rJrn[0]['jumlah']*10)/100))/$row['beratbersih'];
                                      }
                                      $tab .= "<td align=right>".number_format($hargax)."</td>";  
                                      $tab .= "<td align=right>".number_format($harg)."</td>";        
                                      $tab .= "<td align=right>".number_format(($rJrn[0]['jumlah']*(-1)))."</td>";
                                  }else{
                                     $totHarga=$param['hargasatuantbs']*$data['qtypabrik'];
                                      $tab .= "<td align=right>".number_format($hargax)."</td>";  
                                      $tab .= "<td align=right>".number_format($param['hargasatuantbs'])."</td>";    
                                      $tab .= "<td align=right id=totharga_".$no.">".number_format($totHarga)."</td>";
                                  } 
                            }else{
                                    if($data['statppn']=='1'){
                                        $data['hrgsatuan']=$data['hrgsatuan']/1.1;
                                    }
                                    $tab .= "<td align=right>".number_format($data['hrgsatuan'])."</td>";    
                                    $tab .= "<td align=right>".number_format($totalHarga)."</td>";
                            }
                                    if($rcek2['posting']==1){
                                          $tab .= "<td align=center>".$_SESSION['lang']['posting']."</td>";
                                    }else{
                                           $tab .= "<td align=center>".$_SESSION['lang']['belumposting']."</td>";
                                    }
                            $tab .="</tr>";
                        }
                    }
                }
                $tab .= "</tbody>";
                $tab .= "</table>";
                $dtwkt=date("YmdHis");
                $nop_="pengakuanpenjualan_".$dtwkt;
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
                 }
                 else
                 {
                  echo "<script language=javascript1.2>
                        window.location='tempExcel/".$nop_.".xls';
                        </script>";
                 }
                fclose($handle);
                }
                break;
        case 'post':
                // Init
                $zJ = new zJournal();
                $kodeJurnal = 'SLE';
				
				
			
				
				#= tambahan jika mill code : extm dan kodebarang=tbs, maka tanggal memakai tanggalpks
				// echo"<pre>";
				// print_r($param);
				// echo"</pre>";
				// exit("Error:A");
                        
                // $scek=$owlPDO->query("select noreferensi from ".$dbname.".keu_jurnalht where noreferensi='".$param['notransaksi']."'");
                // $scek->setFetchMode(PDO::FETCH_ASSOC);
                // $rcek=owlBaris($scek);;                
                // if($rcek!=0){
                //         exit("warning: ".$_SESSION['lang']['NoKontrak']." : ".$param['nokontrak'].",  ".$_SESSION['lang']['nodo']." : ".$param['nosipb'].", Tanggal : ".$param['tanggal']." Sudah terposting");
                // }
                $addCkDo="";
                $addCkDo2="";
                if($param['nosipb']!=''){
                    $addCkDo=" and a.nosipb='".$param['nosipb']."'";
                    $addCkDo2=" and nosipb='".$param['nosipb']."'";
                    $addCkDoTBS=" and a.nodo='".$param['nosipb']."'";
                    $addCkDoTBS2=" and nodo='".$param['nosipb']."'";
                }
				
				$varjoin="";
				if($param['millcode']=='EXTM' and $param['kodebarang']=='40000003'){
                    if($param['nosipb'] == '')
                    {
                    $supdate="update ".$dbname.".pmn_suratperintahpengiriman set harga='".$param['hargatransportir']."' where nodo='".$param['nosipb']."'";
                    //exit('Error'.$supdate);
                    try{$owlPDO->exec($supdate);}
                            catch (PDOException $e) {
                                print " Gagal, warning  !: " . $e->getMessage() . "<br/>";
                             } 
                        
                    }


                    $varjoin=" or a.nokontrak = b.nokontrakexternal or a.nokontrak = b.nokontrakinternal";
					$str = "SELECT a.kodebarang, b.posting as postkontrak, c.posting as postdo,b.termbayar as termbayar FROM ".$dbname.".pabrik_timbangan a 
					left join ".$dbname.".pmn_kontrakjual_vw b on a.nokontrak = b.nokontrak ".$varjoin."
					left join ".$dbname.".pmn_suratperintahpengiriman c on a.nosipb = c.nodo
					WHERE a.nokontrak='".$param['nokontrak']."' and left(tanggalpks,10)='".tanggalsystemn($param['tanggal'])."' ".$addCkDoTBS."";
				}else{
					$str = "SELECT a.kodebarang, b.posting as postkontrak, c.posting as postdo,b.termbayar as termbayar  FROM ".$dbname.".pabrik_timbangan a 
					left join ".$dbname.".pmn_kontrakjual_vw b on a.nokontrak = b.nokontrak
					left join ".$dbname.".pmn_suratperintahpengiriman c on a.nosipb = c.nodo
					WHERE a.nokontrak='".$param['nokontrak']."' and left(tanggal,10)='".tanggalsystemn($param['tanggal'])."' ".$addCkDo."";
				}
				
                $resData = fetchData($str);
                //echo $str;
                if(empty($resData)) exit("Warning: ".$_SESSION['lang']['NoKontrak']." : ".$param['nokontrak'].",  ".$_SESSION['lang']['nodo']." : ".$param['nosipb'].", ".$_SESSION['lang']['tanggal']." : ".$param['tanggal']." tidak ada satu");
                $data = $resData[0];
				
				if($data['kodebarang']!='40000003')
				{
					if(is_null($data['postkontrak']))
					{
						exit("warning : Kontrak masih belum ada di Menu Pemasaran->Transaksi->Kontrak Penjualan");
					}
					
					if(is_null($data['postdo']))
					{
						exit("warning : No. DO masih belum ada di Menu Pemasaran->Transaksi->Delivery Order");
					}
					
					// if($data['postkontrak']=='0')
					// {
					// 	exit('warning: Kontrak masih belum di posting');
					// }
					
					// if($data['postdo']=='0')
					// {
					// 	exit('warning: No. DO belum di posting');
					// }
					
					if($param['nosipb']=='')
					{
						exit('warning: No. DO Masih Kosong');
					}
				}
				
				

                #select pphditanggung
                $querypph = selectQuery($dbname,'pmn_suratperintahpengiriman','pphditanggung,harga,subsidi,status_timbangan',"nodo='".$param['nosipb']."'");
                $dtpph = fetchData($querypph);

                
                // Get Data
                $qData = "SELECT * FROM ".$dbname.".pabrik_timbangan a
                        INNER JOIN ".$dbname.".pmn_kontrakjual_vw b on a.nokontrak = b.nokontrak ".$varjoin."
                        WHERE a.nokontrak='".$param['nokontrak']."' and a.millcode='".$param['millcode']."' ".$addCkDo2."";
                if($data['kodebarang']=='40000003')
                {
                    $qData = "SELECT * FROM ".$dbname.".pabrik_timbangan a
                        INNER JOIN ".$dbname.".pmn_kontrakjual_vw b on a.nokontrak = b.nokontrak ".$varjoin."
                        WHERE a.nokontrak='".$param['nokontrak']."' and a.millcode='".$param['millcode']."' ".$addCkDoTBS2."";
                }
                $resData = fetchData($qData);
                if(empty($resData)) exit("Warning: ".$_SESSION['lang']['NoKontrak']." : ".$param['nokontrak'].",  ".$_SESSION['lang']['nodo']." : ".$param['nosipb'].", ".$_SESSION['lang']['tanggal']." : ".$param['tanggal']." tidak ada  sini");
                $data = $resData[0];
                if($dtpph[0]['status_timbangan']!=0){
                    if($data['kodebarang']!='40000003')
                    {
                        if($data['kgpembeli']==0){
                            exit('warning: Kg Pembeli Belum Di Inputkan');
                        }
                    }
                }

                $sPeriodeAkuntansi="select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and induk='".$data['kodept']."')";
                $rPeriodeAkuntansi=fetchData($sPeriodeAkuntansi);
                // Validasi Back Date Periode
                if(tanggalsystem($param['tanggal']) < $rPeriodeAkuntansi[0]['tanggalmulai'])
                        exit("Warning: Tanggal Pengakuan tidak boleh lebih kecil dari periode aktif");

                // Default Segment
                $defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

                
                
                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $qKntrkDt="select kodept,hargasatuan,koderekanan,ppn,toleransi from ".$dbname.".pmn_kontrakjual_vw where nokontrak='".$param['nokontrakDt']."'";
                        $resKntrkDt = fetchData($qKntrkDt);
                        $dataDet =$resKntrkDt[0];
                        #noinvoice dari kontrak detail
                        $scek3=$owlPDO->query("select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$param['nokontrakDt']."'");
                        $scek3->setFetchMode(PDO::FETCH_ASSOC);
                        $rcek3=$scek3->fetch();
                        $noinvoiceDet=$rcek3['noinvoice'];

                        #nokontrak detail
                        $scek3=$owlPDO->query("select nokontrak from ".$dbname.".pmn_kontrakjual where nokontrakexternal='".$param['nokontrakDt']."' or nokontrak='".$param['nokontrakDt']."'");
                        $scek3->setFetchMode(PDO::FETCH_ASSOC);
                        $rcek3=$scek3->fetch();
                        $nokontrakDet=$rcek3['nokontrak'];
                }
                #noinvoice
                $scek3=$owlPDO->query("select noinvoice,nofakturpajak,kodecustomer from ".$dbname.".keu_penagihanht where nokontrak='".$resData[0]['nokontrak']."'");
                $scek3->setFetchMode(PDO::FETCH_ASSOC);
                $rcek3=$scek3->fetch();
                $noinvoice=$rcek3['noinvoice'];
                $nofakturpajak=$rcek3['nofakturpajak'];
                $kodecustomer=$rcek3['kodecustomer'];

                #nokontrak
                $scek3=$owlPDO->query("select nokontrak from ".$dbname.".pmn_kontrakjual where nokontrakexternal='".$param['nokontrak']."' or nokontrak='".$param['nokontrak']."'");
                $scek3->setFetchMode(PDO::FETCH_ASSOC);
                $rcek3=$scek3->fetch();
                $nomorkontrak=$rcek3['nokontrak'];

                // // Get Supplier
                // $qSupp = selectQuery($dbname,'log_5supplier',"supplierid,namasupplier",
                //         "kodetimbangan = '".$data['kodecustomer']."'");
                // $resSupp = fetchData($qSupp);
                // $kodeSupp = $resSupp[0]['supplierid'];
                // $namaSupp = $resSupp[0]['namasupplier'];

                // Get Supplier
                $qSupp = selectQuery($dbname,'log_5suptimbangan',"supplierid",
                        "kodetimbangan = '".$data['kodecustomer']."'");
                $resSupp = fetchData($qSupp);
                $kodeSupp = $resSupp[0]['supplierid'];
                

                $qSupp = selectQuery($dbname,'log_5supplier',"namasupplier",
                        "supplierid = '".$kodeSupp."'");
                $resSupp = fetchData($qSupp);
                $namaSupp = $resSupp[0]['namasupplier'];
				
				if($data['kodebarang']=='40000003')
                {
                     $qSupp = selectQuery($dbname,'pmn_suratperintahpengiriman',"transportir",
                        "nodo = '".$data['nodo']."'");
                    $resSupp = fetchData($qSupp);
                    $kodeSupp = $resSupp[0]['transportir'];
                    

                    $qSupp = selectQuery($dbname,'log_5supplier',"namasupplier",
                            "supplierid = '".$kodeSupp."'");
                    $resSupp = fetchData($qSupp);
                    $namaSupp = $resSupp[0]['namasupplier'];
                }
				
				
                $kodejurnal="";
                $kodejurnalTrns="";
                // Define Parameter tergantung Barang
                switch($data['kodebarang']) {
                        case '40000001':
                                $kodeApp = 'SCPO';
                                $kodejurnalTrns="STCPO";
                                break;
                        case '40000002':
                                $kodeApp = 'SKER';
                                $kodejurnalTrns="STKER";
                                break;
                        case '40000003':
                                $kodeApp = 'STBS';
                                $kodejurnalTrns="STTBS";
                                break;
                        case '40000005':
                                $kodeApp = 'SCKG';
                        break;
                        default:
                                $kodeApp = "SEXT";
                }
                if($dtpph[0]['harga']==0){#jika harga nol maka  pengiriman dilakukan oleh pembeli
                    $kodejurnalTrns="";
                }else{
                    if($kodeSupp==''){
                        exit('warning: Transportir Belum Di Setting');#cek jika transportir belum disetting pada master supplier
                    }
                }

                // Get HO dari PT Kontrak
                $qHo = selectQuery($dbname,'organisasi','kodeorganisasi',
                        "induk='".$data['kodept']."' and tipe='HOLDING'");

                $resHo = fetchData($qHo);
                $kodeorg = $resHo[0]['kodeorganisasi'];
                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $qHo2= selectQuery($dbname,'organisasi','kodeorganisasi',
                        "induk='".$dataDet['kodept']."' and tipe='HOLDING'");
                        $resHo2 = fetchData($qHo2);
                        $kodeorg2 = $resHo2[0]['kodeorganisasi'];
                }

                // Get Parameter Jurnal
                $paramJ = $zJ->getParam($_SESSION['org']['induk'],$kodeApp,$kodeJurnal);
                if(empty($paramJ)) exit("Warning: Parameter Jurnal ".$kodeApp." belum ada\n".
                        "Silahkan hubungi pihak IT");

				
			
                // Generate No Journal
                $tanggalJ = tanggalsystemn($param['tanggal']);
                $counter = $zJ->getCounter($data['kodept'],$kodeJurnal);
               $counter++;
                $nojurnal = $zJ->genNoJournal($tanggalJ,$kodeorg,$kodeJurnal,$counter);
                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $tanggalJ = tanggalsystemn($param['tanggal']);
                        $counter2 = $zJ->getCounter($dataDet['kodept'],$kodeJurnal);
                        $counter2++;
                        $nojurnal2 = $zJ->genNoJournal($tanggalJ,$kodeorg2,$kodeJurnal,$counter2);
                }

                // Jumlah
                if($data['kodebarang']=='40000003'){
                    #jika komoditinya adalah tbs maka ambil harga dari inputa
                    if($param['hargasatuantbs']!=''){
                        $param['hrgsatuan']=$param['hargasatuantbs'];
                    }else{
                        exit("warning: ".$_SESSION['lang']['hargasatuan']." ".$_SESSION['lang']['kosong']);
                    }
                    if($param['beratbersihtbs']!=''){
                        $param['qty']=$param['beratbersihtbs'];
                    }
                }
				
                // if($data['kgpembeli']!=0){
                //     $data['beratbersih']=$data['kgpembeli'];   
                // }
                #jika include ppn maka nilai hargasatuan di kurangi dengan hargasatuan*10/100
                // if($data['ppn']==1){
                //     $data['hargasatuan']=$data['hargasatuan']/1.1;//script sebelum diubah menjadi per tanggal
                // }
                //$jumlah = $data['beratbersih'] * $data['hargasatuan'];//script sebelum diubah menjadi per tanggal
                //harga sudah dibagi pada saat list data di awal,qty sudah ambil kg pabrik/pembeli tergantung franconya
                $jumlah = $param['qty'] * $param['hrgsatuan'];
                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        #jika include ppn maka nilai hargasatuan di kurangi dengan hargasatuan*10/100
                        if($dataDet['ppn']==1){
                            $dataDet['hargasatuan']=$dataDet['hargasatuan']/1.1;
                        }
                        $jumlah2 = $data['beratbersih'] * $dataDet['hargasatuan'];
                }

				/*
				#bentuk ppn khusus tbs
				D : Piutang usaha				1,1 M 
				K : penjualan					1 M
				K : PPn Keluaran				100jt
				*/
				
				#=======================================================================================
				#= jika tipe transaksi UM, untuk debetnya harus ke uang muka
				#=======================================================================================
				$kodeaplikasi='PJ';
                $kodejurnal='PJINV';
				if($data['termbayar']==''){
                    exit('warning :'.$_SESSION['lang']['payment'].' '.$_SESSION['lang']['kosong']);
                }
				$str="SELECT noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='".$kodeaplikasi."' and jurnalid='".$kodejurnal."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					
                    if($data['termbayar']=='UM'){#uang muka penjualan
                        $paramJ['noakundebet']=$bar['noakunkredit'];
                    }
                    if($data['termbayar']=='PM'){#pengiriman
                        $paramJ['noakundebet']=$bar['noakundebet'];
                    }
				    //exit('warning'.$paramJ['noakundebet']);
				$nilppn=0;
				if($data['kodebarang']=='40000003'){
					// $nilppn=10/100*$jumlah;
					$resData[0]['beratbersih']=$data['beratbersih']; //<== pakai yg sudah sortasi 
				}
				
                // Prepare Data
                $dataRes = array();
                $dataRes2 = array();
                $dataRes['header'] = array(
                        'nojurnal'=>$nojurnal,
                        'kodejurnal'=>$kodeJurnal,
                        'tanggal'=>$tanggalJ,
                        'tanggalentry'=>date('Ymd'),
                        'posting'=>'0',
                        'totaldebet'=>($jumlah+$nilppn),
                        'totalkredit'=>($jumlah+$nilppn)*(-1),
                        'amountkoreksi'=>'0',
                        'noreferensi'=>$param['notransaksi'],
                        'autojurnal'=>'1',
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'revisi'=>'0'
                );
                $keteranganJurnal="Pengakuan Penjualan a/n Nama ".$nmcust[$kodecustomer].";Tanggal:".$param['tanggal'].";Qty :".number_format($param['qty']).";NoKontrak :".$param['nokontrak'].";No DO :".$param['nodo'];
                $dataRes['detail'][0] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggalJ,
                        'nourut'=>1,
                        'noakun'=>$paramJ['noakundebet'],
                        'keterangan'=>$keteranganJurnal,
                        'jumlah'=>($jumlah+$nilppn),
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$kodeorg,
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>$data['kodebarang'],
                        'nik'=>'',
                        'kodecustomer'=>$data['kodecustomer'],
                        'kodesupplier'=>$kodeSupp,
                        'noreferensi'=>$param['notransaksi'],
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>$nomorkontrak,
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment' => $defSegment
                );

				//'keterangan'=>$resData[0]['beratbersih'],
				////exit("Error:$noinvoice._.$nofakturpajak._.$kodecustomer._.$nmcust[$kodecustomer]");
				if($data['kodebarang']=='40000003'){
                    $jumlah=$jumlah+$nilppn;
				}
				
                $dataRes['detail'][1] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggalJ,
                        'nourut'=>2,
                        'noakun'=>$paramJ['noakunkredit'],
                        'keterangan'=>$keteranganJurnal,
                        'jumlah'=>$jumlah * (-1),
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$kodeorg,
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>$data['kodebarang'],
                        'nik'=>'',
                        'kodecustomer'=>$data['kodecustomer'],
                        'kodesupplier'=>$kodeSupp,
                        'noreferensi'=>$param['notransaksi'],
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>$nomorkontrak,
                        'kodeblok'=>'',
                        'revisi'=>'0',
                        'kodesegment' => $defSegment
                );
				
				if($nilppn!=0){
                    if($data['kodebarang']!='40000003'){
                            #ambil jurnal sppn
                            $sPPn="select * from ".$dbname.".keu_5parameterjurnal where kodeaplikasi='SPPN'";
                            $rPPn=fetchData($sPPn);
        					$dataRes['detail'][2] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggalJ,
                                'nourut'=>3,
                                'noakun'=>$rPPn[0]['noakunkredit'],
                                'keterangan'=>'PPn Keluaran '.$param['notiket'].$keteranganJurnal,
                                'jumlah'=>$nilppn * (-1),
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$kodeorg,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$data['kodebarang'],
                                'nik'=>'',
                                'kodecustomer'=>$data['kodecustomer'],
                                'kodesupplier'=>$kodeSupp,
                                'noreferensi'=>$param['notransaksi'],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nomorkontrak,
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment' => $defSegment
        					);
                    }
				}
				
				
                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                                        $dataRes2['header'] = array(
                                        'nojurnal'=>$nojurnal2,
                                        'kodejurnal'=>$kodeJurnal,
                                        'tanggal'=>$tanggalJ,
                                        'tanggalentry'=>date('Ymd'),
                                        'posting'=>'0',
                                        'totaldebet'=>$jumlah2,
                                        'totalkredit'=>$jumlah2*(-1),
                                        'amountkoreksi'=>'0',
                                        'noreferensi'=>$param['notiket'].$kodeorg2,
                                        'autojurnal'=>'1',
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'revisi'=>'0'
                                        );

                                        $dataRes2['detail'][0] = array(
                                        'nojurnal'=>$nojurnal2,
                                        'tanggal'=>$tanggalJ,
                                        'nourut'=>1,
                                        'noakun'=>$paramJ['noakundebet'],
                                        'keterangan'=>$keteranganJurnal,
                                        'jumlah'=>$jumlah2,
                                        'matauang'=>'IDR',
                                        'kurs'=>'1',
                                        'kodeorg'=>$kodeorg2,
                                        'kodekegiatan'=>'',
                                        'kodeasset'=>'',
                                        'kodebarang'=>$data['kodebarang'],
                                        'nik'=>'',
                                        'kodecustomer'=>$dataDet['koderekanan'],
                                        'kodesupplier'=>$kodeSupp,
                                        'noreferensi'=>$param['notiket'].$kodeorg2,
                                        'noaruskas'=>'',
                                        'kodevhc'=>'',
                                        'nodok'=>$nokontrakDet,
                                        'kodeblok'=>'',
                                        'revisi'=>'0',
                                        'kodesegment' => $defSegment
                                );
								//'keterangan'=>$resData[0]['beratbersih'],
								

                                $dataRes2['detail'][1] = array(
                                'nojurnal'=>$nojurnal2,
                                'tanggal'=>$tanggalJ,
                                'nourut'=>2,
                                'noakun'=>$paramJ['noakunkredit'],
                                'keterangan'=>$keteranganJurnal,
								'jumlah'=>$jumlah2 * (-1),
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$kodeorg2,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$data['kodebarang'],
                                'nik'=>'',
                                'kodecustomer'=>$dataDet['koderekanan'],
                                'kodesupplier'=>$kodeSupp,
                                'noreferensi'=>$param['notiket'].$kodeorg2,
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nokontrakDet,
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment' => $defSegment
                        );
                }
                // Do Journal
                $zJ->doJournal($data['kodept'],$kodeJurnal,
                        $dataRes,$counter,"Pengakuan Penjualan");

                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $zJ->doJournal($dataDet['kodept'],$kodeJurnal,
                                $dataRes2,$counter2,"Pengakuan Penjualan");
                }
                if($param['hargasatuantbs']!=''){
                    unset($data['hargasatuan']);
                    $data['hargasatuan']=$param['hargasatuantbs'];
                }
                // Insert Pengakuan Penjualan
                $dataIns = array(
                        'notransaksi' => $param['notransaksi'],
                        'millcode' => $data['millcode'],
                        'tanggalpengakuan' => $tanggalJ,
                        'hargasatuan' => $data['hargasatuan'],
                        'totalrupiah' => ($data['hargasatuan']*$param['qty']),
                        'noinvoice' => 'null',
                        'kgkirim' => $param['qty'],
                        'posting' => 1
                );
                $qCekData= selectQuery($dbname,'keu_pengakuanjual','notransaksi',
                "notransaksi='".$param['notransaksi']."' ");
                $resCekdata = fetchData($qCekData);
                if(!empty($resCekdata)){
                    $sdel="delete from ".$dbname.".keu_pengakuanjual where notransaksi='".$param['notransaksi']."'";
                        try{$owlPDO->exec($sdel);}
                        catch (PDOException $e) {
                            print "Delete Transaksi Error !: " . $e->getMessage() . "<br/>";
                            $zJ->rbJournal($nojurnal);
                         }                    
                }
                $qIns = insertQuery($dbname,'keu_pengakuanjual',$dataIns);
                try{$owlPDO->exec($qIns);}
                catch (PDOException $e) {
                   print "Insert Transaksi Error !: ".$qIns."__" . $e->getMessage() . "<br/>";
                   $zJ->rbJournal($nojurnal);
                 }                 


                if(!empty($param['nokontrakDt'])){
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $dataIns2 = array(
                                'notransaksi' => $param['notiket'].$kodeorg2,
                                'millcode' => $data['millcode'],
                                'tanggalpengakuan' => $tanggalJ,
                                'hargasatuan' => $data['hargasatuan'],
                                'posting' => 1
                        );
                        $qCekData2= selectQuery($dbname,'keu_pengakuanjual','notransaksi',
                        "notransaksi='".$param['notiket'].$kodeorg2."' ");
                        $resCekData2 = fetchData($qCekData2);
                        if(!empty($resCekData2)){
                            $sdel2="delete from ".$dbname.".keu_pengakuanjual where notransaksi='".$param['notiket'].$kodeorg2."'";
                            try{$owlPDO->exec($sdel2);}
                            catch (PDOException $e) {
                               print "Delete Transaksi Error !: " . $e->getMessage() . "<br/>";
                             }                             
                        }
                        $qIns2 = insertQuery($dbname,'keu_pengakuanjual',$dataIns2);
                            try{$owlPDO->exec($qIns2);}
                            catch (PDOException $e) {
                                print "Insert Transaksi Error !: " . $e->getMessage() . "<br/>";
                                $zJ->rbJournal($nojurnal2);
                                $zJ2->rbJournal($nojurnal);
                             }  
                }
                if(!empty($param['nokontrakDt'])){
                        //insert fisik pada pabrik timbangan untuk nokontrak detail
                        //jika nokontrak menjadi induk,maka kontrak detailnya di buatkan jurnal
                        $qCekData2= selectQuery($dbname,'pabrik_timbangan','notransaksi',
                        "notransaksi='".$param['notiket'].$kodeorg2."' ");
                        $resCekData2 = fetchData($qCekData2);
                        if(!empty($resCekData2)){
                            $sdel2="delete from ".$dbname.".pabrik_timbangan where notransaksi='".$param['notiket'].$kodeorg2."'";
                           try{$owlPDO->exec($sdel2);}
                            catch (PDOException $e) {
                                print "Delete Transaksi Error !: " . $e->getMessage() . "<br/>";
                             }                              
                        }

                        #ngitung ulang yang dah di terima terakhir brp
                        $strap = "select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='ES' and kodeparameter='ESEXT'";
                        @$resap = fetchData($strap);
                        @$nilaix=0;
                            @$lokasi = explode(',', $resap[0]['nilai']);
                            foreach (@$lokasi as $key => $val) {
                                if($val==$lst['kodeorgx'])
                                {
                                    $nilaix=1;
                                }
                            }

                        $sCek=$owlPDO->query("select if((a.kodeorg in ('".$resap[0]['nilai']."')),(sumb(beratbersihpmks)),(sum(beratbersih))) as totaltrima from ".$dbname.".pabrik_timbangan where left(notransaksi,7) in (select notransaksi from ".$dbname.".pabrik_timbangan where nokontrak='".$data['nokontrak']."') "
                                 . " and nokontrak='".$param['nokontrakDt']."' and char_length(notransaksi)=11");
                        $sCek->setFetchMode(PDO::FETCH_ASSOC);
                        $rCek=  $sCek->fetch();

                        if($nilaix==0){
                        $supdate="update ".$dbname.".pmn_kontrakjualdt set terpenuhi='".($rCek['totaltrima']+$data['beratbersih'])."' where nokontrak='".$param['nokontrakDt']."' and nokontrak_ref='".$data['nokontrak']."'";
                        }
                        else
                        {
                        $supdate="update ".$dbname.".pmn_kontrakjualdt set terpenuhi='".($rCek['totaltrima']+$data['beratbersihpmks'])."' where nokontrak='".$param['nokontrakDt']."' and nokontrak_ref='".$data['nokontrak']."'";
                        }
                           try{$owlPDO->exec($supdate);}
                            catch (PDOException $e) {
                                print " Gagal, warning  !: " . $e->getMessage() . "<br/>";
                             } 
                        #nginsert data tambahannya
                        $sInsert="insert into ".$dbname.".pabrik_timbangan (notransaksi,tanggal,kodecustomer,kodebarang,jammasuk,beratmasuk,jamkeluar,beratkeluar,nokendaraan,supir,timbangonoff,statussortasi,nokontrak,nosipb,username,sloc,kodeorg,millcode,beratbersih,kgpembeli,beratmasukpmks,beratkeluarpmks,beratbersihpmks,tanggalpks) values ";
                        $sInsert.=" ('".$param['notiket'].$kodeorg2."','".$data['tanggal']."','".$data['kodecustomer']."','".$data['kodebarang']."','".$data['jammasuk']."','".$data['beratmasuk']."','".$data['jamkeluar']."','".$data['beratkeluar']."','".$data['nokendaraan']."','".$data['supir']."','".$data['timbangonoff']."','".$data['statussortasi']."','".$param['nokontrakDt']."','".$data['nosipb']."','".$data['username']."','".$data['sloc']."','','".$param['millcode']."','".$data['beratbersih']."','".$data['kgpembeli']."','".$data['beratmasukpmks']."','".$data['beratkeluarpmks']."','".$data['beratbersihpmks']."','".$data['tanggalpks']."')";
                           try{$owlPDO->exec($sInsert);}
                            catch (PDOException $e) {
                                print " Gagal, warning  !: " . $e->getMessage() . "<br/>";
                             }   
                }

                $tgl = str_replace("-","",substr($tanggalJ,0,10));
                #====notransaksi jurnal akun debet serta kredit dari parameter jurnal
                if($kodejurnalTrns!=''){
                    $optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$param['millcode']."'");
                    $whereNoindukph = "kodekelompok='".$kodejurnalTrns."' and kodeorg='".$optInduk[$param['millcode']]."'";
                    //exit('warning'.$whereNoindukph);
                    $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
                    $noKon = fetchData($query);
                    $tmpC = $noKon[0]['nokounter'];
                    $tmpC++;
                    $counterjurnal = addZero($tmpC,3);
                    $nojurnal = $tgl."/".$kodeorg."/".$kodejurnalTrns."/".$counterjurnal;
                    // $noreferensi = $param['nosipb'];

         
                    $pphditanggung=$dtpph[0]['pphditanggung'];
                    $harga=$dtpph[0]['harga'];
                    $prsnPajak=$dtpph[0]['subsidi'];
                    // exit('warning : '.$harga);
                    $pph=0;
                    $jumlahkredit=0;
                    $jumlah=0;
                    if($data['kgpembeli']!=0){
                        $data['beratbersih']=$data['kgpembeli'];   
                    }
                    
                    if($pphditanggung=='1'){
                        $jumlah = ($param['qty'] * $harga);
                        $pph = (($jumlah*(100/(100-$prsnPajak)))*$prsnPajak)/100;
                        $jumlahkredit = ($param['qty'] * $harga);  
                    }else{
                        $jumlah = $param['qty'] * $harga;
                        $jumlahkredit = $param['qty'] * $harga;
                        $pph = ($jumlah*$prsnPajak)/100;
                        // $jumlahkredit=$jumlah-$pph;
                    }
                    //$keteranganJurnalBy="Biaya Transportir;Tanggal:".$param['tanggal'].";Qty :".number_format($param['qty']).";NoKontrak :".$param['nokontrak'].";No DO :".$param['nodo'];
                    $keteranganJurnalHtg="Pengakuan Hutang atas jasa pengiriman,Trasnportir A/N : ".$namaSupp."Tanggal:".$param['tanggal'].";Qty :".number_format($param['qty']).";NoKontrak :".$param['nokontrak'].";No DO :".$param['nodo'];
                    #akun debet serta krdit
                    $query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnalTrns."' and aktif=1");
                    $dtnoakun = fetchData($query2);

                    // Prepare Data
                    $dataRes = array();
                    $dataRes2 = array();
                    if($jumlahkredit!=0){#jika kreditnya gak kosong baru dijurnal
                        $dataRes['header'] = array(
                                'nojurnal'=>$nojurnal,
                                'kodejurnal'=>$kodejurnalTrns,
                                'tanggal'=>$tanggalJ,
                                'tanggalentry'=>date('Ymd'),
                                'posting'=>'0',
                                'totaldebet'=>$jumlahkredit,
                                'totalkredit'=>$jumlahkredit*(-1),
                                'amountkoreksi'=>'0',
                                'noreferensi'=>$param['nosipb'],
                                'autojurnal'=>'1',
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'revisi'=>'0'
                        );
                        $dataRes['detail'][0] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggalJ,
                                'nourut'=>1,
                                'noakun'=>$dtnoakun[0]['noakundebet'],
                                'keterangan'=>$keteranganJurnalHtg,
                                'jumlah'=>$jumlah,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$kodeorg,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$data['kodebarang'],
                                'nik'=>'',
                                'kodecustomer'=>$data['kodecustomer'],
                                'kodesupplier'=>$kodeSupp,
                                'noreferensi'=>$param['nosipb'],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nomorkontrak,
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment' =>''
                        );
                        // if($pphditanggung=='1'){
                        //     if($pph!=0){
                        //             $dataRes['detail'][3] = array(
                        //             'nojurnal'=>$nojurnal,
                        //             'tanggal'=>$tanggalJ,
                        //             'nourut'=>5,
                        //             'noakun'=>$dtnoakun[0]['noakundebet'],
                        //             'keterangan'=>'Biaya Beban PPH ditanggung, a/n '.$namaSupp.' dengan No Tiket : '.$param['notiket'].' No.DO '.$param['nosipb'],
                        //             'jumlah'=>$pph,
                        //             'matauang'=>'IDR',
                        //             'kurs'=>'1',
                        //             'kodeorg'=>$kodeorg,
                        //             'kodekegiatan'=>'',
                        //             'kodeasset'=>'',
                        //             'kodebarang'=>$data['kodebarang'],
                        //             'nik'=>'',
                        //             'kodecustomer'=>'',
                        //             'kodesupplier'=>$kodeSupp,
                        //             'noreferensi'=>$param['nosipb'],
                        //             'noaruskas'=>'',
                        //             'kodevhc'=>'',
                        //             'nodok'=>'',
                        //             'kodeblok'=>'',
                        //             'revisi'=>'0',
                        //             'kodesegment' =>''
                        //         );
                        //     }
                        // }
                        $keteranganJurnalHtg="Pengakuan Hutang atas jasa pengiriman,Trasnportir A/N : ".$namaSupp."Tanggal:".$param['tanggal'].";Qty :".number_format($param['qty']).";NoKontrak :".$param['nokontrak'].";No DO :".$param['nodo'];
                        $dataRes['detail'][1] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggalJ,
                                'nourut'=>2,
                                'noakun'=>$dtnoakun[0]['noakunkredit'],
                                'keterangan'=>$keteranganJurnalHtg,
                                'jumlah'=>$jumlahkredit * (-1),
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$kodeorg,
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>$data['kodebarang'],
                                'nik'=>'',
                                'kodecustomer'=>$data['kodecustomer'],
                                'kodesupplier'=>$kodeSupp,
                                'noreferensi'=>$param['nosipb'],
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$nomorkontrak,
                                'kodeblok'=>'',
                                'revisi'=>'0',
                                'kodesegment' =>''
                        );

                        // if($pph!=0){
                        //     $dataRes['detail'][2] = array(
                        //             'nojurnal'=>$nojurnal,
                        //             'tanggal'=>$tanggalJ,
                        //             'nourut'=>4,
                        //             'noakun'=>'2120300',
                        //             'keterangan'=>'Pengakuan Hutang PPH Pasal 23 atas jasa pengiriman, a/n '.$namaSupp.' dengan No Tiket No Tiket : '.$param['notiket'].' No.DO '.$param['nosipb'],
                        //             'jumlah'=>$pph*(-1),
                        //             'matauang'=>'IDR',
                        //             'kurs'=>'1',
                        //             'kodeorg'=>$kodeorg,
                        //             'kodekegiatan'=>'',
                        //             'kodeasset'=>'',
                        //             'kodebarang'=>$data['kodebarang'],
                        //             'nik'=>'',
                        //             'kodecustomer'=>$data['koderekanan'],
                        //             'kodesupplier'=>$kodeSupp,
                        //             'noreferensi'=>$param['nosipb'],
                        //             'noaruskas'=>'',
                        //             'kodevhc'=>'',
                        //             'nodok'=>'',
                        //             'kodeblok'=>'',
                        //             'revisi'=>'0',
                        //             'kodesegment' =>''
                        //     );
                        // }
        
                            if ($param['nokontrak']!=''){
                                # Header
                                $errorDB="";
                                $queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                                //exit('warning '.$queryH);
                                try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; }
                                # Detail
                                if($errorDB=='') {
                                    foreach($dataRes['detail'] as $key=>$dataDet) {
                                        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
                                        try{$owlPDO->exec($queryD); 
                                        }catch (PDOException $e){
                                            $errorDB .= "Detail: ".$key." ". $e->getMessage() ; 
                                        }
                                    }
                                    $sUpdate="update ".$dbname.".keu_5kelompokjurnal set nokounter='".($noKon[0]['nokounter']+1)."' 
                                              where ".$whereNoindukph."";
                                     try{$owlPDO->exec($sUpdate);}catch (PDOException $e){print "Gagal : ". $e->getMessage()."__".$sUpdate ; }
                                }else if($errorDB!=''){
                                    $sdel="delete from ".$dbname.".keu_pengakuanjual where notransaksi='".$param['notiket']."'";
                                    try{$owlPDO->exec($sdel);}catch (PDOException $e) {
                                        print "Delete Transaksi Error !: " . $e->getMessage() . "<br/>";
                                        $zJ->rbJournal($nojurnal);
                                     }
                                    echo $errorDB;
                                    exit('warning');
                                }   
                        
                            }
                    }
                }
                break;
                case'pilKontrak':
                //exit("error:".$param['obc']);
                #ambil nokontrak induk dan kodept
                $sNkntrk=$owlPDO->query("select distinct nokontrak from ".$dbname.".pabrik_timbangan where 
                          notransaksi='".$param['notiket']."' and millcode='".$param['millcode']."'");
                $sNkntrk->setFetchMode(PDO::FETCH_ASSOC);    
                $rNkntrk=$sNkntrk->fetch();
                $nokontrak=$rNkntrk['nokontrak'];

                $sPt=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['millcode']."'");
                $sPt->setFetchMode(PDO::FETCH_ASSOC);
                $rPt=$sPt->fetch();

                #ambil detail data
                $optKontrak.="<option value=''>No.Kontrak-Kuota-Terpenuhi-Sisa</option>";
                $sDet=$owlPDO->query("select c.nokontrak,kuota,terpenuhi,toleransi  from ".$dbname.".pmn_kontrakjual b 
                       left join ".$dbname.".pmn_kontrakjualdt c on b.nokontrak=c.nokontrak
                       where c.nokontrak_ref='".$nokontrak."' and kodept='".$rPt['induk']."'");
                $sDet->setFetchMode(PDO::FETCH_ASSOC);
                while($rDet=$sDet->fetch()){
                        @$dtTol=$rDet['kuota']*(intval($rDet['toleransi'])/100);
                        $rDet['kuota']=$rDet['kuota']+$dtTol;
                        if($rDet['terpenuhi']<$rDet['kuota']){
                                $rDet['sisa']=$rDet['kuota']-$rDet['terpenuhi'];
                                $optKontrak.="<option value='".$rDet['nokontrak']."'>".$rDet['nokontrak']."-".$rDet['kuota']."-".$rDet['terpenuhi']."-".$rDet['sisa']."</option>";
                        }
                }
                $formdata.="<table cellpadding=1 cellspacing=1 border=0>";
                $formdata.="<tr>";
                $formdata.="<td>".$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['detail']."</td>";
                $formdata.="<td><select id=nokontrakDt>".$optKontrak."</select></td>";
                $formdata.="</tr>";
                $formdata.="<tr>";
                $formdata.="<td>".$_SESSION['lang']['tanggal']."</td>";  
                $formdata.="<td><input type=text id=tanggal_".$param['notiket']." value='".$param['tanggal']."' class=myinputtext readonly=readonly /></td>";
                $formdata.="</tr>";
                $formdata.="<tr><td colspan=2><button onclick=post('','".$param['notiket']."','".$param['millcode']."',".$param['rw'].") class=mybutton>".$_SESSION['lang']['posting']."</button></td></tr>";
                $formdata.="</table>";
                echo $formdata;
                break;
                case'getPt':
                        $optMat="<option value='0'>".$_SESSION['lang']['pilihdata']."</option>";
                        $optDt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                        if($param['pabrik']==''){
                            $sBrg="select distinct(a.kodebarang) as millcode, b.namabarang as namaorganisasi 
                                        from ".$dbname.".pabrik_timbangan a
                                        left join ".$dbname.".log_5masterbarang b
                                        on a.kodebarang = b.kodebarang";
                            $rBrgPabrik = fetchData($sBrg);
                            foreach($rBrgPabrik as $key=>$row) {
                                    $optMat.="<option value='".$row['millcode']."'>".$row['namaorganisasi']."</option>";
                            }
                        }
						
						$kdpt='';
                        $str="select distinct c.kodeorganisasi,c.namaorganisasi,b.kodebarang as kodebarang,d.namabarang as namabarang 
											from ".$dbname.".pmn_kontrakjual_vw b inner join ".$dbname.".pabrik_timbangan a on 
											(b.nokontrak=a.nokontrak or b.nokontrakexternal=a.nokontrak)
                                             left join ".$dbname.".organisasi c on b.kodept=c.kodeorganisasi
                                             left join ".$dbname.".log_5masterbarang d on b.kodebarang=d.kodebarang
                                             where  millcode='".$param['pabrik']."' "; #exit("error");
                        $sDt=$owlPDO->query($str);
                        $sDt->setFetchMode(PDO::FETCH_ASSOC);
                        while($rDt=$sDt->fetch()){
							if($kdpt!=$rDt['kodeorganisasi']){
								$optDt.="<option value='".$rDt['kodeorganisasi']."'>".$rDt['namaorganisasi']."</option>";
							}
							$kdpt=$rDt['kodeorganisasi'];
							$optMat.="<option value='".$rDt['kodebarang']."'>".$rDt['namabarang']."</option>";
                        }     
                        echo $optDt."####".$optMat;
                break;
                case'getFormKgBeli':
					$where = "";
                  $dtTrans=explode("##",$param['notransaksi']);
				  if($dtTrans[2]!=''){
					  $where = " and nosipb='".$dtTrans[2]."'";
				  }
                  $tgl=substr($dtTrans[0],0,4)."-".substr($dtTrans[0],4,2)."-".substr($dtTrans[0],-2,2);
                  $stDt="select * from ".$dbname.".pabrik_timbangan where millcode='".$param['millcode']."' and left(tanggal,10)='".$tgl."' and nokontrak='".$dtTrans[1]."' ".$where."";
                  //echo $stDt;
                  $rDt=fetchData($stDt);
                  $formdata.="<table cellpadding=1 cellspacing=1 border=0>";
                  $formdata.="<thead><tr>";
                  $formdata.="<td>No.</td>";
                  $formdata.="<td>".$_SESSION['lang']['noTiket']."</td>";
                  $formdata.="<td>".$_SESSION['lang']['tanggal']."</td>";
                  $formdata.="<td>Kg Pabrik</td>";
                  $formdata.="<td>Kg Pembeli</td>";
                  $formdata.="</tr></thead><tbody>";
                  $no="";
                  foreach($rDt as $row=>$lst){
                    $no+=1;
                    $formdata.="<tr class=rowcontent>";
                    $formdata.="<td>".$no."</td>";
                    $formdata.="<td id=notiket_".$no.">".$lst['notransaksi']."</td>";
                    if(($lst['tglpembeli']=='0000-00-00')||($lst['tglpembeli']=='')){
                      $lst['tglpembeli']=substr($lst['tanggal'],0,10);
                    }
                    $formdata.="<td>".makeElement('tanggal_'.$no,'date',
                                          tanggalnormal($lst['tglpembeli']),array('style'=>'width:70px'))."</td>";
                    $formdata.="<td><input type=text class=myinputtextnumber disabled style='width:100px' onkeypress='return angka_doang(event)' value='".$lst['beratbersih']."' /></td>";
                    $formdata.="<td><input type=text class=myinputtextnumber id=kgPembeli_".$no." style='width:100px' onkeypress='return angka_doang(event)'  value='".$lst['kgpembeli']."' /></td>";
                    $formdata.="</tr>";
                  }
                  $formdata.="<tr class=rowcontent>";
                  $formdata.="<td colspan=2>&nbsp;</td>";
                  $formdata.="<td colspan=3 align=right><button id=\"btnSave\" name=\"btnSave\" class=\"mybutton\" onclick=\"saveKgPembeli(".$no.")\">".$_SESSION['lang']['save']."</button></td>";
                  $formdata.="</tr>";
                  $formdata.="</tbody></table>";
                  echo $formdata;
                break;
                case'updtAll':
                
                // echo"<pre>";
                // print_r($param);
                // echo"</pre>";
                // exit('warning:masuuk');
                  for($dtisi=0;$dtisi<$param['totRow'];$dtisi++){
                     $sUpdate="update ".$dbname.".pabrik_timbangan set kgpembeli='".$param['kgPembeli'][$dtisi]."',tglpembeli='".tanggalsystemn($param['tglPembeli'][$dtisi])."'
                              where notransaksi='".$param['notiket'][$dtisi]."'";
                    //exit('warning:'.$sUpdate);
                    try{$owlPDO->exec($sUpdate);}catch (PDOException $e) {
                        print "Error Update !: ".$sUpdate."__".$e->getMessage() . "<br/>";
                    }
                  }

                break;
}
// foreach($lstTanggal as $dtTgl){
                //         $no+=1;
                //         $tab .= "<tr class=rowcontent>";
                //         $tab .= "<td>".$dtTgl."</td>";
                // }

      //           foreach($resData as $row) {
      //                   $no+=1;
      //                   $tmpTgl = explode(' ',$row['tanggal']);
      //                   $scek=$owlPDO->query("select nokontrak from ".$dbname.".pmn_kontrakjualdt where nokontrak_ref='".$row['nokontrak']."'");
      //                   $scek->setFetchMode(PDO::FETCH_ASSOC);
      //                   $rcek=owlBaris($scek);

      //                   $scek2=$owlPDO->query("select notransaksi,posting,tanggalpengakuan from ".$dbname.".keu_pengakuanjual where notransaksi='".$row['notransaksi']."'");
      //                   $scek2->setFetchMode(PDO::FETCH_ASSOC);
      //                   $rcek2=$scek2->fetch();

      //                   $scek3=$owlPDO->query("select noinvoice from ".$dbname.".keu_penagihanht where nokontrak='".$row['nokontrak']."'");
      //                   $scek3->setFetchMode(PDO::FETCH_ASSOC);
      //                   $rcek3=$scek3->fetch();
      //                   $tab .= "<tr class=rowcontent>";
      //                   $tab .= "<td align=center>".$row['notransaksi']."</td>";
      //                   $tab .= "<td>".$row['millcode']."</td>";
      //                   $tab .= "<td>".$row['nokontrak']."</td>";                        
      //                   $tab .= "<td>".$rcek3['noinvoice']."</td>";
      //                   $tab .= "<td>".$row['pmndo']."</td>";
      //                   $tab .= "<td><input type=hidden  id=ppn_".$no." value=".$row['ppn']." />".$arrStPPn[$row['ppn']]."</td>";
      //                   $tab .= "<td>".$row['namasupplier']."</td>";
      //                   $tab .= "<td><input type=hidden  id=kdBarang_".$row['notransaksi']." value=".$row['kodebarang']." />".$row['namabarang']."</td>";
      //                   // if($row['kgpembeli']!=0){
      //                   //     $row['beratbersih']=$row['kgpembeli'];   
      //                   // }
            // if($row['kodebarang']=='40000003'){
            //  $row['kgpembeli']=$row['beratbersih']-$row['kgpotsortasi'];
            //  $row['beratbersih']=$row['beratbersih']-$row['kgpotsortasi'];
            // }
      //                   $tab .= "<td align=right><input type=hidden  id=beratbersih_".$no." value=".$row['kgpembeli']." />".number_format($row['beratbersih'],0)."</td>";
      //                   $tab .= "<td>".$row['satuan']."</td>";
      //                   if($row['kodebarang']=='40000003'){
      //                       #ambil rupiah dari jurnaldt jika sudah diposting
      //                       $sJrn="select jumlah,nojurnal from ".$dbname.".keu_jurnaldt where noreferensi='".$row['notransaksi']."' and jumlah>0";
      //                       $rJrn=fetchdata($sJrn);
      //                       if(count($rJrn)!=0){
      //                           @$harg=$rJrn[0]['jumlah']/$row['beratbersih'];
      //                           if($row['ppn']==1){
      //                               $harg=($rJrn[0]['jumlah']+(($rJrn[0]['jumlah']*10)/100))/$row['beratbersih'];
      //                           }
      //                           $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatbs_".$no."' onkeypress='return angka_doang(event)' style='width:100px;' value='".number_format($harg)."' disabled /></td>";        
      //                           $tab .= "<td align=right id=totharga_".$no.">".number_format($rJrn[0]['jumlah'])."</td>";
      //                       }else{
      //                           $tab .= "<td align=right><input type=text class=myinputtextnumber id='hargatbs_".$no."' onchange=gethargabaris(".$no.");  onkeypress='return angka_doang(event)' style='width:100px;' value='' /></td>";    
      //                           $tab .= "<td align=right id=totharga_".$no."></td>";
      //                       } 
      //                   }else{
      //                       $tab .= "<td align=right>".number_format($row['hargasatuan'],0)."</td>";    
      //                       $tab .= "<td align=right id=totharga_".$no.">".number_format($row['beratbersih'] * $row['hargasatuan'],0)."</td>";
      //                   }
                        
      //                   if($rcek2['posting']==1){
      //                           $tab .= "<td align=center>".tanggalnormal($tmpTgl[0])."</td>";
      //                           $tab .= "<td align=center><img class=zImgBtn id=imgPost_".$row['notransaksi']." src='images/skyblue/posted.png'></td>";
      //                   } else {
      //                           $tab .= "<td align=center>".makeElement('tanggal_'.$row['notransaksi'],'date',
      //                                   tanggalnormal($tmpTgl[0]),array('style'=>'width:70px','disabled'=>'disabled'))."</td>";
      //                           $tab .= "<td align=center><img class=zImgBtn id=imgPost_".$row['notransaksi']." src='images/skyblue/posting.png' ".
      //                                   "onclick=\"pilKontrak(this,'".$row['notransaksi']."','".$row['millcode']."','".$rcek."','".$no."','".$row['nosipb']."',event)\"></td>";
      //                           /*$tab .= "<td><img class=zImgBtn src='images/skyblue/posting.png' ".
      //                                   "onclick=\"post(this,'".$row['notransaksi']."','".$row['millcode']."')\"></td>";*/
      //                   }
      //                   $tab .= "</tr>";
      //           }
?>
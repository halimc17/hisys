<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$proses = checkPostGet('proses', '');
$kodeorg = checkPostGet('kodeorg', '');
$periodegaji = checkPostGet('periodegaji', '');
$tipepotongan = checkPostGet('tipepotongan', '');
$param=$_POST;
 
    switch ($proses) {
        case 'preview':
			  if($param['pt']==''){
                exit('warning:'.$_SESSION['lang']['pt']." ".$_SESSION['lang']['notifobligatory']);
        }
        if($param['tgl']==''){
          exit('warning:'.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['notifobligatory']);
        }
              #cari notiket timbangan berdasarkan pt,tanggal dan komoditi(kodebarang)
              $whrSm=" and kodebarang='".$param['komoditi']."'";
              $whrmill="select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."' 
             and tipe='PABRIK' and kodeorganisasi<>'".$_SESSION['empl']['lokasitugas']."'";
			        $whrtgl=" and left(tanggal,4)='".substr($param['tgl'],0,4)."'";
              $sNoref="select notransaksi,beratbersih,millcode from ".$dbname.".pabrik_timbangan 
                       where millcode in (".$whrmill.")
                       ".$whrSm." ".$whrtgl." ";
					 //exit("Error:$sNoref");
              $rNoref=fetchdata($sNoref);
              if(count($rNoref)==0){
                exit('warning:Data kosong tidak ada timbangan pengiriman di tanggal tsb');
              }
              foreach($rNoref as $row=>$isiDt){
                if($row==0){
                    $lstTrans="'".$isiDt['notransaksi']."'";
                }else{
                    $lstTrans.=",'".$isiDt['notransaksi']."'";
                }
                $brtBersih[$isiDt['notransaksi']]=$isiDt['beratbersih'];
              }

              #tampilkan data berdasarkan hasil pencarian tiket di atas
		          $sData="select notransaksi,norefrensi,beratbersih from ".$dbname.".pabrik_timbangan 
                      where norefrensi in (".$lstTrans.") and millcode='".$_SESSION['empl']['lokasitugas']."'
                      ".$whrSm." and left(tanggal,10)='".$param['tgl']."'";
              //echo $sData;
					//  exit("Error:$sData");
              $rData=fetchdata($sData);
              if(count($rData)==0){
                $lstRef='';
                $sData="select notransaksi,left(tanggal,10) as tanggal,millcode from ".$dbname.".pabrik_timbangan where notransaksi in (select norefrensi from ".$dbname.".pabrik_timbangan 
                      where millcode='".$_SESSION['empl']['lokasitugas']."'
                      ".$whrSm." and left(tanggal,10)='".$param['tgl']."') and millcode in (".$whrmill.")";
                $rData=fetchdata($sData);
                foreach($rData as $row=>$isiDt){
                  $lstRef.=$isiDt['notransaksi'].", ".$isiDt['tanggal'].", ".$isiDt['millcode']."\n";
                }
                echo $lstRef;
                exit('warning:Data penerimaan kosong/tanggal pengiriman tidak dalam bulan yang sama, silahkan cek noreferensi ditimbangan');
              }
              $tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead>";
              $tab.="<tr class=rowheader>";
              //$tab.="<td rowspan=2 align=center >".$_SESSION['lang']['tanggal']."</td>
              $tab.="<td colspan=2 align=center>Pabrik Pengirim</td>";
              $tab.="<td colspan=2 align=center>Bulking</td>";
              //$tab.="<td rowspan=2 align=center>".$_SESSION['lang']['selisih']."</td>";                       
              $tab.="</tr>";
              $tab.="<tr>";
              $tab.="<td>".$_SESSION['lang']['noTiket']."</td>";
              $tab.="<td>".$_SESSION['lang']['beratBersih']."</td>";
              $tab.="<td>".$_SESSION['lang']['noTiket']."</td>";
              $tab.="<td>".$_SESSION['lang']['beratBersih']."</td>";
              $tab.="</tr></thead><tbody>";
              foreach($rData as $row){
                $no+=1;
                $tab.="<tr class=rowcontent>";
                //$tab.="<td>".$param['tgl']."</td>";
                $tab.="<td><input type=hidden  id=notktkrm_".$no." value='".$row['norefrensi']."' />".$row['norefrensi']."</td>";
                $tab.="<td align=right><input type=hidden  id=brtktkrm_".$no." value='".$brtBersih[$row['norefrensi']]."' />".number_format($brtBersih[$row['norefrensi']])."</td>";
                $tab.="<td><input type=hidden  id=notkttrm_".$no." value='".$row['notransaksi']."' />".$row['notransaksi']."</td>";
                $tab.="<td align=right><input type=hidden  id=brtkttrm_".$no." value='".$row['beratbersih']."' />".number_format($row['beratbersih'])."</td>";
                $selisih=$brtBersih[$row['norefrensi']]-$row['beratbersih'];
                //$tab.="<td align=right>".number_format($selisih)."</td>";
                $tab.="</tr>";  
                $totKirim+=$brtBersih[$row['norefrensi']];
                $totTrima+=$row['beratbersih'];
              }
                $tab.="<tr class=rowcontent>";
                //$tab.="<td>".$param['tgl']."</td>";
                $tab.="<td>&nbsp;</td>";
                $tab.="<td align=right><input type=hidden  id=totKrm value='".$totKirim."' />".number_format($totKirim)."</td>";
                $tab.="<td>&nbsp;</td>";
                $tab.="<td align=right><input type=hidden  id=totTrm value='".$totTrima."' />".number_format($totTrima)."</td>";
                //$selisih=$brtBersih[$row['norefrensi']]-$row['beratbersih'];
                //$tab.="<td align=right>".number_format($selisih)."</td>";
                $tab.="</tr>";  
              $tab.="</tbody></table>";
              $tab.="<button class=mybutton onclick=saveData(".count($rData).")>".$_SESSION['lang']['save']."</button>";
              echo $tab;
        break;
        case'getTgl':
          $thn=$_SESSION['org']['period']['tahun'];
          $dtTrima=0;
          $optTgl="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
          $sData="select distinct left(tanggal,10) as tanggal from ".$dbname.".pabrik_timbangan where 
                  norefrensi in (select notransaksi from ".$dbname.".pabrik_timbangan where millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['unitId']."' and tipe='PABRIK' and kodeorganisasi!='".$_SESSION['empl']['lokasitugas']."') and tanggal like '".$thn."%' and kodebarang='40000001') 
                  order by left(tanggal,10) asc";
          $rData=fetchdata($sData);
          foreach($rData as $row=>$lstData){
              $sCek="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['unitId']."' and tanggal='".$lstData['tanggal']."'";
              $rCek=fetchdata($sCek);
              if(count($rCek)==0){
                $optTgl.="<option value='".$lstData['tanggal']."'>".$lstData['tanggal']."</option>";  
              }
          }
          if($param['tgl']!=''){
             $idapl='WBBAS';
             $whrdt="kodeparameter='".$idapl."'";
             $optDtPt=makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',$whrdt);
              $kdPt="and kodecustomer<>'".$optDtPt[$idapl]."'";
             if($param['unitId']=='BAS'){
                 $kdPt="and kodecustomer='".$optDtPt[$idapl]."'";
             }
             $dtTrima='';
            #query untuk ambil kg terima
            $sKg="select sum(beratbersih) as kg from ".$dbname.".pabrik_timbangan 
                  where left(tanggal,10)='".$param['tgl']."' and kodebarang='".$param['komoditi']."' ".$kdPt."";
            $rKg=fetchdata($sKg);
            $dtTerima=$rKg[0]['kg'];    

            #query untuk ambil kg kirim
            $sKg="select sum(beratbersih) as kg from ".$dbname.".pabrik_timbangan 
                  where notransaksi in (select norefrensi from ".$dbname.".pabrik_timbangan 
                  where left(tanggal,10)='".$param['tgl']."' and kodebarang='".$param['komoditi']."' ".$kdPt.")";

            $rKg=fetchdata($sKg);
            $dtKirim=$rKg[0]['kg'];  
            $dtTrima=$dtTerima."####".$dtKirim;
          }
          if($param['tgl']!=''){
            $optTgl=$dtTrima;
            echo $optTgl;
          }else{
            echo $optTgl;
          }
          
        break;
        case'getTgl2':
          $thn=$_SESSION['org']['period']['tahun'];
          $dtTrima=0;
          $optTgl="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
          $sData="select distinct left(tanggal,10) as tanggal from ".$dbname.".pabrik_timbangan where 
                  norefrensi in (select notransaksi from ".$dbname.".pabrik_timbangan where millcode in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['unitId']."' and tipe='PABRIK' and kodeorganisasi!='".$_SESSION['empl']['lokasitugas']."') and tanggal like '".$thn."%' and kodebarang='40000001') 
                  order by left(tanggal,10) asc";
          $rData=fetchdata($sData);
          foreach($rData as $row=>$lstData){
              $sCek="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['unitId']."' and tanggal='".$lstData['tanggal']."'";
              $rCek=fetchdata($sCek);
              if(count($rCek)==0){
                $optTgl.="<option value='".$lstData['tanggal']."'>".$lstData['tanggal']."</option>";  
              }
          }
          echo $optTgl;
        break;
        case'saveData':
        if($param['pt']==''){exit('warning:'.$_SESSION['lang']['pt']." ".$_SESSION['lang']['notifobligatory']);}
        if($param['tgl']==''){exit('warning:'.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['notifobligatory']);}
            #cek apakah sudah ada data tersimpan pada tanggal,pt dan komoditi yang dipilih
            #jika belum maka insert total penerimaan dan saldo awal=(masuk+saldo kemarin)-kirim
            #jika sudah ada data tersimpan pada tanggal tersebut maka update field masuk
            $sCek="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['pt']."'
                   and kodebarang='".$param['komoditi']."' and tanggal='".$param['tgl']."'";
            $rCek=fetchdata($sCek);
            if(count($rCek)==0){
                #sawal
                $sCek2="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['pt']."'
                       and kodebarang='".$param['komoditi']."' and tanggal<'".$param['tgl']."'
                       order by tanggal desc limit 1";
                $rCek2=fetchdata($sCek2);
                $sawal=($rCek2[0]['masuk']+$rCek2[0]['saldoawal'])-$rCek2[0]['kirim'];
                if($sawal==''){
                   $sawal=0;
                }
                $sIns="insert into ".$dbname.".pabrik_blk_5saldo (`kodept`,`kodebarang`,`tanggal`,`masuk`,`saldoawal`,`sumber`,`updateby`) values ";
                $sIns.="('".$param['pt']."','".$param['komoditi']."','".$param['tgl']."','".$param['totKrm']."','".$sawal."','".$param['kgKirim']."','".$_SESSION['standard']['userid']."')";
            }else{
                $sIns="update ".$dbname.".pabrik_blk_5saldo set `masuk`='".$param['totKrm']."',`sumber`='".$param['kgKirim']."' where kodept='".$param['pt']."'
                       and tanggal='".$param['tgl']."' and kodebarang='".$param['komoditi']."'";
            }
            try{$owlPDO->exec($sIns);}catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "<br/>".$sIns; 
                die(); 
            }
        break;
        case'saveAll':
              if($param['pt']==''){
                exit('warning:'.$_SESSION['lang']['pt']." ".$_SESSION['lang']['notifobligatory']);
              }
              if($param['tgl']==''){
                exit('warning:'.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['notifobligatory']);
              }
            #cek apakah sudah ada data tersimpan pada tanggal,pt dan komoditi yang dipilih
            #jika belum maka insert total penerimaan dan saldo awal=(masuk+saldo kemarin)-kirim
            #jika sudah ada data tersimpan pada tanggal tersebut maka update field masuk
            $sCek="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['pt']."'
                   and kodebarang='".$param['komoditi']."' and tanggal='".tanggalsystemn($param['tgl'])."'";
            $rCek=fetchdata($sCek);
			
            if(count($rCek)==0){
                #sawal
                $sCek2="select * from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['pt']."'
                       and kodebarang='".$param['komoditi']."' and tanggal<'".tanggalsystemn($param['tgl'])."'
                       order by tanggal desc limit 1";
                $rCek2=fetchdata($sCek2);
                $sawal=($rCek2[0]['masuk']+$rCek2[0]['saldoawal'])-$rCek2[0]['kirim'];
                $sIns="insert into ".$dbname.".pabrik_blk_5saldo (`kodept`,`kodebarang`,`tanggal`,`masuk`,`saldoawal`,`updateby`) values ";
                $sIns.="('".$param['pt']."','".$param['komoditi']."','".tanggalsystemn($param['tgl'])."','".$param['totTrm']."','".$sawal."','".$_SESSION['standard']['userid']."')";
				
			}else{
                $sIns="update ".$dbname.".pabrik_blk_5saldo set `masuk`='".$param['totTrm']."' where kodept='".$param['pt']."'
                       and tanggal='".tanggalsystemn($param['tgl'])."' and kodebarang='".$param['komoditi']."'";
            }
            try{$owlPDO->exec($sIns);}catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "<br/>".$sIns; 
                die(); 
            }
        break;
        case'loadData':
        $arrPeriode=array();
        $sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        $rAkuntansi=fetchdata($sAkuntansi);
        foreach($rAkuntansi as $row=>$lstdata){
            $arrPeriode[$lstdata['periode']]=$lstdata['tutupbuku'];
        }
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0){
              $page=0;  
            }
        } 
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        if($param['ptCr']!=''){
          $where.=" and kodept='".$param['ptCr']."'";
        }
        
        if(($param['periode']!='')&&($param['periode2']!='')){
          if($param['periode2']<$param['periode']){
            exit('warning: Periode Salah');
          }
          $where.=" and left(tanggal,7) between '".$param['periode']."' and  '".$param['periode2']."'";
        }
        
        $str="select * from ".$dbname.".pabrik_blk_5saldo where kodept!='' ".$where." 
              order by tanggal desc ";
    $res=fetchdata($str);
    //$jlhbrs=owlBaris($res); 
    $jlhbrs=count($res);
    if($jlhbrs==0){
      $tab.="<tr class=rowcontent>";
      $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td>";
      $tab.="</tr>";
    }else{

      $no=0;
      $selisih=0;
      $no=$maxdisplay;
          $str="SELECT * from ".$dbname.".pabrik_blk_5saldo where kodept!=''  ".$where." 
                order by tanggal desc   limit ".$offset.",".$limit."";
          $tab="";
      $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
      $res->setFetchMode(PDO::FETCH_ASSOC);
      while($bar=$res->fetch()){
              $no+=1;
              $optNmKary=array();
              $optNmKary2=array();
                $whr="kodebarang='".$bar['komoditi']."'";
                $optNmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
              
              if(intval(@$bar['postingby'])!=0){
                $whr="karyawanid='".$bar['postingby']."'";
                $optNmKary2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
              }
              $tab.="<tr class=rowcontent>";
              $tab.="<td align=right>".$no."</td>";
              $tab.="<td>".$bar['kodept']."</td>";
              $tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
              $tab.="<td align=right>".number_format($bar['sumber'])."</td>";
              $tab.="<td align=right>".number_format($bar['masuk'])."</td>";
              $selisih=$bar['sumber']-$bar['masuk'];
              $tab.="<td align=right>".number_format($selisih)."</td>";
              $tab.="<td align=center>";
              //if($arrPeriode[substr($bar['tanggal'],0,7)]==0){
                $tab.=" <img src=images/application/application_delete.png class=zImgBtn title='Delete ".$bar['kodept'].",".$bar['tanggal']."' onclick=\"deletehead('".$bar['kodept']."','".$bar['tanggal']."','".$bar['kodebarang']."');\">"; 
              //}else{
              //  $tab.="&nbsp;";
              //}
              $tab.="</td>";
              $tab.="</tr>";
      }
          $totrows=ceil($jlhbrs/$limit);

          if($totrows==0){
                  $totrows=1;
          }
          $isiRow='';
          for($er=1;$er<=$totrows;$er++){
                  $sel = ($page==$er-1)? 'selected': '';
                  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
          }
          $footd="
              <tr><td colspan=7 align=center>
              <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
              <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
              <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
              </td>
              </tr>";
    }
   
        
        echo $tab."####".$footd;
        break;
        case'deleteDt':
          $sDel="delete from ".$dbname.".pabrik_blk_5saldo where kodept='".$param['kodeorg']."' and tanggal='".$param['tgl']."'
                 and kodebarang='".$param['komoditi']."'";
          try{$owlPDO->exec($sDel);}catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
                die(); 
          }
        break;
    }

?>
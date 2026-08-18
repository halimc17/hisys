<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;
#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;){
    if(strlen($i)<2)
    {
        $i="0".$i;
    }
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;){
    if(strlen($i)<2)
    {
        $i="0".$i;
    }
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}
switch($param['proses']) {
    case'loadNewData':
         
        #ambil periode akuntansi
        $sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        $rAkuntansi=fetchData($sAkuntansi);
        #ambil klasifikasi
        $arragama=getEnum($dbname,'pabrik_logmesin','klasifikasi');
        foreach($arragama as $kei=>$fal){
            $lstKlafikasi[$fal]=$fal;
        }
        foreach ($rAkuntansi as $key => $value) {
            $periodeAkun[$value['periode']]=$value['tutupbuku'];
        }
 
        if(($param['tgl']!='')&&($param['tgl2']!='')){
            if(tanggalsystem($param['tgl'])>tanggalsystem($param['tgl2'])){
                exit('warning:'.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tanggal']);
            }
            $where.=" and tanggal between '".tanggalsystemn($param['tgl'])."' and '".tanggalsystemn($param['tgl2'])."'";
        }elseif($param['tgl']!=''){
            $where.=" and tanggal='".tanggalsystemn($param['tgl'])."'";
        }
        
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select distinct tanggal,nourut from ".$dbname.".pabrik_logmesin where left(station,4) = '".$_SESSION['empl']['lokasitugas']."' ".$where."
                  order by tanggal desc";
        $res=fetchdata($str);
        //$jlhbrs=owlBaris($res);   
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=15>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=0;
            $no=$maxdisplay;
            #ambil jumlah data untuk tampil
            $str="select distinct tanggal,nourut from ".$dbname.".pabrik_logmesin where left(station,4) = '".$_SESSION['empl']['lokasitugas']."' ".$where."
                  order by tanggal desc limit ".$offset.",".$limit."";
            $dtgl=fetchData($str);
            foreach($dtgl as $row=>$lstData){
                $tglArr[$lstData['tanggal']]=$lstData['tanggal'];
                $nourArr[$lstData['nourut']]=$lstData['nourut'];
            }


            $str="select tanggal,sum(jam) as totaljam,klasifikasi,updateby,nourut  from ".$dbname.".pabrik_logmesin where left(station,4) = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
                  group by tanggal,klasifikasi,nourut order by tanggal desc";
           //echo $str;
            $dres=fetchData($str);
            foreach($dres as $row=>$lstData){
                $dTtgl[$lstData['tanggal'].$lstData['nourut']]=$lstData['tanggal'];
                $nilArr[$lstData['tanggal'].$lstData['nourut'].$lstData['klasifikasi']]+=$lstData['totaljam'];
                $upArr[$lstData['tanggal']]=$lstData['updateby'];
            }
            $tab="";
            foreach($tglArr as $tgl){
                foreach($nourArr as $nourut){
                    if($dTtgl[$tgl.$nourut]!=''){
                        $no+=1;
                        $optNmKary=array();
                        if(intval($upArr[$tgl])!=0){
                            $whr="karyawanid='".$upArr[$tgl]."'";
                            $optNmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                        }
                        $tab.="<tr class=rowcontent>";
                        $tab.="<td align=center>".tanggalnormal($dTtgl[$tgl.$nourut])."</td>";
                        $tab.="<td align=center>".$nourut."</td>";
                        foreach($lstKlafikasi as $dtKode){
                            $addLing='';
                                $addLing="onclick=\"detaildt('".$_SESSION['lang']['detail']."','".$tgl."','".$dtKode."','".$nourut."');\" title='Detail ".$tgl."' style=cursor:pointer;";
                                $tab.="<td align=right ".$addLing.">".$nilArr[$tgl.$nourut.$dtKode]."</td>"; 
                        }
                        $tab.="<td align=left>".$optNmKary[$upArr[$tgl]]."</td>";
                        $tab.="
                        <td align=center>";
                        if($periodeAkun[substr($tgl,0,7)]==0){//klo dah tutup buku kagak bisa dihapus
                            $tab.="<img src=images/application/application_delete.png class=zImgBtn title=\"Delete ".$_SESSION['lang']['all']."\"  onclick=\"deletehead('".$tgl."','".$nourut."');\">";   
                        }
                        //$tab.="&nbsp;<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' ></td>";
                        $tab.="</tr>";

                    }
                        
                }
                //$tab.="<td align=center>".$lstData['nourut']."</td>";
                
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
                <tr><td colspan=16 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
     
        
        echo $tab."####".$footd;
        break;
        case'getTable':
            $sDet="select * from ".$dbname.".pabrik_5mr_list_station where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."' order by kode_station asc ";
            $qDet=fetchData($sDet);
            $totRow=count($qDet);
            if($totRow==0){
                exit('warning: '.$_SESSION['lang']['station']." ".$_SESSION['lang']['kosong'].", Silakan daftarkan pada Pabrik>Setup>Daftar Station");
            }
            $max=2;
            
            $tab.="<table cellpadding=1 cellspacing=1 border=0  width=100%>";
            foreach($qDet as $row=>$lstData){
                $whrNm="kodeorganisasi='".$lstData['kode_station']."'";
                $optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrNm);
                if($max==2){
                    $tab.="<tr><td valign=top><table  cellpadding=1 cellspacing=1  border=1 width=100%>";   
                    $tab.="<tr>";
                    $tab.="<td rowspan=9 width=35%>".$optNmOrg[$lstData['kode_station']]."<input type=hidden id='station_".$row."' value='".$lstData['kode_station']."' /></td>";
                    $tab.="</tr>";
                    $tab.="<tr><td rowspan=2>HEATING UP</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='HU_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='HU_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='HU_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='HU_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>PROSES</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='PR_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='PR_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='PR_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='PR_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>COOLING DOWN</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='CN_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='CN_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='CN_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='CN_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>BREAKDOWN</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='BN_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='BN_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='BN_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='BN_MNTSTP_".$row."'>".$mnt."</select></td></tr>";
                    $tab.="</table></td>";  
                    $max-=1;
                }else{
                    $tab.="<td  valign=top><table cellpadding=1 cellspacing=1  border=1 width=100% >";   
                    $tab.="<tr>";
                    $tab.="<td rowspan=9 width=35%>".$optNmOrg[$lstData['kode_station']]."<input type=hidden id='station_".$row."' value='".$lstData['kode_station']."' /></td>";
                    $tab.="</tr>";
                    $tab.="<tr><td rowspan=2>HEATING UP</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center  bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='HU_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='HU_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='HU_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='HU_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>PROSES</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='PR_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='PR_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='PR_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='PR_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>COOLING DOWN</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='CN_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='CN_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='CN_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='CN_MNTSTP_".$row."'>".$mnt."</select></td></tr>";  
                    $tab.="<tr><td rowspan=2>BREAKDOWN</td><td align=center bgcolor=#DEDEDE>Start</td><td align=center bgcolor=#DEDEDE>Stop</td></tr>";  
                    $tab.="<tr><td><select id='BN_JAMSTRT_".$row."'>".$jm."</select>&nbsp;<select id='BN_MNTSTRT_".$row."'>".$mnt."</select></td><td><select id='BN_JAMSTP_".$row."'>".$jm."</select>&nbsp;<select id='BN_MNTSTP_".$row."'>".$mnt."</select></td></tr>";
                    $tab.="</table></td></tr>";  
                    $max=2;
                }
                
            }
            $tab.="<tr><td colspan=2 align=center><button class=mybutton id=dtlAbn onclick=saveDt()>".$_SESSION['lang']['save']."</button>
                   <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['done']."</button></td></tr>";
            $tab.="</table><input type=hidden id=totRow value='".$totRow."' />";
            echo $tab;
        break;
    case'saveDt':
            if($param['tanggal']==''){
                exit('warning:'.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
            }       
            if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
                exit("warning: ".$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['lokasitugas']);
            }
            #cek apakah sudah ada atau belum
            $rowAda=0;
            for($awe=0;$awe<$param['totRow'];$awe++){
                $sCek="select * from ".$dbname.".pabrik_logmesin where station='".$param['stationId'][$awe]."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
                $rCek=fetchData($sCek);
                $rowAda+=count($rCek);
            }
            if($rowAda!=0){
                exit('warning:'.$_SESSION['lang']['data'].' '.$param['tanggal'].' '.$_SESSION['lang']['exist']);
            }

            $sins="INSERT INTO ".$dbname.".`pabrik_logmesin` (`station`,`tanggal`,`klasifikasi`,`nourut`,`start`,`stop`,`jam`,`updateby`) values";
            for($awe=0;$awe<$param['totRow'];$awe++){
                $statId=$param['stationId'][$awe];
                $no=$awe+1;
                if($awe==0){
                    $sins.=" ('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiHU'][$awe.$statId]."','".$no."','".$param['jamStrtHU'][$awe]."','".$param['jamStpHU'][$awe]."','".$param['totalJamHU'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiPR'][$awe.$statId]."','".$no."','".$param['jamStrtPR'][$awe]."','".$param['jamStpPR'][$awe]."','".$param['totalJamPR'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiCN'][$awe.$statId]."','".$no."','".$param['jamStrtCN'][$awe]."','".$param['jamStpCN'][$awe]."','".$param['totalJamCN'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiBN'][$awe.$statId]."','".$no."','".$param['jamStrtBN'][$awe]."','".$param['jamStpBN'][$awe]."','".$param['totalJamBN'][$awe]."','".$_SESSION['standard']['userid']."')";
                }else{

                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiHU'][$awe.$statId]."','".$no."','".$param['jamStrtHU'][$awe]."','".$param['jamStpHU'][$awe]."','".$param['totalJamHU'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiPR'][$awe.$statId]."','".$no."','".$param['jamStrtPR'][$awe]."','".$param['jamStpPR'][$awe]."','".$param['totalJamPR'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiCN'][$awe.$statId]."','".$no."','".$param['jamStrtCN'][$awe]."','".$param['jamStpCN'][$awe]."','".$param['totalJamCN'][$awe]."','".$_SESSION['standard']['userid']."')";
                    $sins.=",('".$statId."','".tanggalsystemn($param['tanggal'])."','".$param['klafikasiBN'][$awe.$statId]."','".$no."','".$param['jamStrtBN'][$awe]."','".$param['jamStpBN'][$awe]."','".$param['totalJamBN'][$awe]."','".$_SESSION['standard']['userid']."')";
                }
                
            }
            try {
                $owlPDO->exec($sins);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
                die(); 
            }
    break;
    
    case'deletehead':
        $sDel="delete from ".$dbname.".pabrik_logmesin where tanggal='".$param['tanggal']."' and left(station,4)='".$_SESSION['empl']['lokasitugas']."' and nourut='".$param['nourut']."'";
        try{
            $owlPDO->exec($sDel); 
        }catch (PDOException $e){
            exit("error: db error".$e->getMessage()."___".$sUpd);
            die();
        }
    break;
    
    case'htmlDetail':
        #ambil periode akuntansi
        $sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
        $rAkuntansi=fetchData($sAkuntansi);
        foreach ($rAkuntansi as $key => $value) {
            $periodeAkun[$value['periode']]=$value['tutupbuku'];
        }
        //order by nourut asc 
        $sCek="select * from ".$dbname.".pabrik_logmesin where left(station,4)='".$_SESSION['empl']['lokasitugas']."' 
               and tanggal='".$param['tgl']."' and klasifikasi='".$param['klasifikasi']."' and nourut='".$param['nourut']."' order by nourut asc";
        //echo $sCek;
        $rCek=fetchData($sCek);
        foreach($rCek as $row=>$nilDt){
            $isNilStrt[$nilDt['station']]=$nilDt['start'];
            $isNilStp[$nilDt['station']]=$nilDt['stop'];

        }
        $tanggal=tanggalnormal($param['tgl']);
        $tab.="<table cellspacing='1' cellpadding=1 border='0'>";
        $sDet="select * from ".$dbname.".pabrik_5mr_list_station where left(kode_station,4)='".$_SESSION['empl']['lokasitugas']."'";
        $qDet=fetchData($sDet);
        $totRow=count($qDet);
        
        $max=2;
        $stForm="";
        if($periodeAkun[substr($tgl, 0,7)]!=0){
            $stForm="disabled=disabled";
        }
        $arrNama=array("HU"=>"HEATING UP","PR"=>"PROSES","CN"=>"COOLING DOWN","BN"=>"BREAKDOWN");
        $tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td align=left><input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:150px;\" value='".$tanggal."' disabled=disabled /></td></tr>
                <tr><td>".$_SESSION['lang']['nourut']."</td>
                <td>:</td>
                <td align=left><input type=text class='myinputtext' id='nourut' size='12' maxlength='10' style=\"width:150px;\" value='".$param['nourut']."' disabled=disabled /></td></tr></table>
                <input type=hidden id=klasifikasi value='".$param['klasifikasi']."' />";
        $tab.="<table cellpadding=1 cellspacing=1 border=0>";
            foreach($qDet as $row=>$lstData){
                $whrNm="kodeorganisasi='".$lstData['kode_station']."'";
                $optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrNm);
                if($max==2){
                    $tab.="<tr><td><table cellspacing=1 cellpadding=1 border=1 width=100%>
                           <tr>
                           <td rowspan=2 width=35%>".$optNmOrg[$lstData['kode_station']]."<input type=hidden id='station_".$row."' value='".$lstData['kode_station']."' /></td>
                           <td rowspan=2>".$arrNama[$param['klasifikasi']]."</td>
                           <td align=center bgcolor=#DEDEDE>Start</td>
                           <td align=center bgcolor=#DEDEDE>Stop</td></tr>";
                            #ambil jam sesuai dengan klasifikasi
                            $jmStart=explode(":",$isNilStrt[$lstData['kode_station']]);
                            $jmStop=explode(":",$isNilStp[$lstData['kode_station']]);
                            $jmstrt=$mntstrt="";
                            for($i=0;$i<24;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStart[0]==$i){
                                    $jmstrt.="<option value='".$i."' selected>".$i."</option>";     
                                }else{
                                    $jmstrt.="<option value=".$i.">".$i."</option>";
                                }
                               $i++;
                            }
                            for($i=0;$i<60;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStart[1]==$i){
                                    $mntstrt.="<option value='".$i."' selected>".$i."</option>";
                                }else{
                                    $mntstrt.="<option value='".$i."'>".$i."</option>";
                                }
                               $i++;
                            }
                            $jmstp=$mntstp="";
                            for($i=0;$i<24;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStop[0]==$i){
                                    $jmstp.="<option value='".$i."' selected>".$i."</option>"; 
                                }else{
                                    $jmstp.="<option value=".$i.">".$i."</option>";
                                }
                               $i++;
                            }
                            for($i=0;$i<60;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStop[1]==$i){
                                    $mntstp.="<option value='".$i."' selected>".$i."</option>";
                                }else{
                                    $mntstp.="<option value='".$i."'>".$i."</option>";
                                }
                               $i++;
                            }            
                    $tab.="<tr><td><select id='".$param['klasifikasi']."_JAMSTRT_".$row."' ".$stForm.">".$jmstrt."</select>&nbsp;<select id='".$param['klasifikasi']."_MNTSTRT_".$row."' ".$stForm.">".$mntstrt."</select></td><td><select id='".$param['klasifikasi']."_JAMSTP_".$row."' ".$stForm.">".$jmstp."</select>&nbsp;<select id='".$param['klasifikasi']."_MNTSTP_".$row."' ".$stForm.">".$mntstp."</select></td></tr></table></td>";  
                    $max-=1;
                }else{
                        $tab.="<td><table cellspacing=1 cellpadding=1 border=1 width=100%>
                           <tr>
                           <td rowspan=2 width=35%>".$optNmOrg[$lstData['kode_station']]."<input type=hidden id='station_".$row."' value='".$lstData['kode_station']."' /></td>
                           <td rowspan=2>".$arrNama[$param['klasifikasi']]."</td>
                           <td align=center bgcolor=#DEDEDE>Start</td>
                           <td align=center bgcolor=#DEDEDE>Stop</td></tr>";
                            #ambil jam sesuai dengan klasifikasi
                            $jmStart=explode(":",$isNilStrt[$lstData['kode_station']]);
                            $jmStop=explode(":",$isNilStp[$lstData['kode_station']]);
                            $jmstrt=$mntstrt="";
                            for($i=0;$i<24;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStart[0]==$i){
                                    $jmstrt.="<option value='".$i."' selected>".$i."</option>";     
                                }else{
                                    $jmstrt.="<option value=".$i.">".$i."</option>";
                                }
                               $i++;
                            }
                            for($i=0;$i<60;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStart[1]==$i){
                                    $mntstrt.="<option value='".$i."' selected>".$i."</option>";
                                }else{
                                    $mntstrt.="<option value='".$i."'>".$i."</option>";
                                }
                               $i++;
                            }
                            $jmstp=$mntstp="";
                            for($i=0;$i<24;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStop[0]==$i){
                                    $jmstp.="<option value='".$i."' selected>".$i."</option>"; 
                                }else{
                                    $jmstp.="<option value=".$i.">".$i."</option>";
                                }
                               $i++;
                            }
                            for($i=0;$i<60;){
                                if(strlen($i)<2){
                                    $i="0".$i;
                                }
                                if($jmStop[1]==$i){
                                    $mntstp.="<option value='".$i."' selected>".$i."</option>";
                                }else{
                                    $mntstp.="<option value='".$i."'>".$i."</option>";
                                }
                               $i++;
                            }            
                    $tab.="<tr><td><select id='".$param['klasifikasi']."_JAMSTRT_".$row."' ".$stForm.">".$jmstrt."</select>&nbsp;<select id='".$param['klasifikasi']."_MNTSTRT_".$row."' ".$stForm.">".$mntstrt."</select></td><td><select id='".$param['klasifikasi']."_JAMSTP_".$row."' ".$stForm.">".$jmstp."</select>&nbsp;<select id='".$param['klasifikasi']."_MNTSTP_".$row."' ".$stForm.">".$mntstp."</select></td></tr></table></td></tr>";  
                    $max=2;
                }
            }
            $tab.="<tr><td colspan=2 align=center><button class=mybutton id=dtlAbn onclick=upDt('".$param['klasifikasi']."')>".$_SESSION['lang']['save']."</button>
                   <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['done']."</button></td></tr>";
            $tab.="</table><input type=hidden id=totRow value='".$totRow."' />";
        echo $tab;
        break;
        case'update':
            for($awe=0;$awe<$param['totRow'];$awe++){
                $sDel="delete from ".$dbname.".`pabrik_logmesin` where station='".$param['stationId'][$awe]."' and tanggal='".tanggalsystemn($param['tanggal'])."'
                       and klasifikasi='".$param['klasifikasi']."' and nourut='".$param['nourut']."'";
                try {
                    $owlPDO->exec($sDel);
                    $sins="insert into ".$dbname.".`pabrik_logmesin` (`station`,`tanggal`,`klasifikasi`,`start`,`stop`,`jam`,`nourut`,`updateby`) values 
                           ('".$param['stationId'][$awe]."','".tanggalsystemn($param['tanggal'])."','".$param['klasifikasi']."','".$param['jamStrtHU'][$awe]."','".$param['jamStpHU'][$awe]."','".$param['totalJamHU'][$awe]."','".$param['nourut']."','".$_SESSION['standard']['userid']."')";
                    try{
                        $owlPDO->exec($sins);
                    } catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
                        die(); 
                    }
                }  catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
                        die(); 
                }
            }
        break;
        
}
<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
$optTipePot = makeOption($dbname, 'sdm_ho_component', 'id,name');
$proses = checkPostGet('proses', '');
$kodeorg = checkPostGet('kodeorg', '');
$periodegaji = checkPostGet('periodegaji', '');
$tipepotongan = checkPostGet('tipepotongan', '');
$arrNmtp = makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
$mode = checkPostGet('mode', '');
$param=$_POST;
 
    switch ($proses) {
		case'posting':
			$supdate="update ".$dbname.".sdm_potonganht set posting=1,postingby='".$_SESSION['standard']['userid']."' 
					where kodeorg='".$kodeorg."' and periodegaji='".$periodegaji."' and tipepotongan='59'";
			try{
				$owlPDO->exec($supdate);
			}
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
		break;
		
        case 'preview':
			$str="select posting  from ".$dbname.".sdm_potonganht where kodeorg='".$param['unitId']."'  
				and periodegaji='".$param['periode']."' and tipepotongan='59'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				if($bar['posting']==1){
					exit("Warning:Data untuk unit : ".$param['unitId']." dengan periode ".$param['periode']." sudah terposting ");
				}
		
            if(strlen($param['unitId'])<6){
                $whr="lokasitugas='".$param['unitId']."' and (subbagian='' or subbagian is null)";
            }else{
                $whr="lokasitugas='".substr($param['unitId'],0,4)."' and subbagian='".$param['unitId']."'";
            }
            if($param['unitId']==''){
                $param['unitId']=$_SESSION['empl']['lokasitugas'];
                $whr="lokasitugas='".$_SESSION['empl']['lokasitugas']."'";
            }    
            if($param['tpKary']!=''){
                $whr.=" and tipekaryawan='".$param['tpKary']."'";    
            }
            #tanggal
            $sTgl="select * from ".$dbname.".sdm_5periodegaji where periode='".$param['periode']."' and kodeorg='".substr($param['unitId'], 0,4)."'";
            $rTgl=fetchData($sTgl);
            $dtTgl=explode("-",$rTgl[0]['tanggalmulai']);
            $tglawl2=(intval($dtTgl[2])-1)+15;
            $tgl1=$rTgl[0]['tanggalmulai'];
            $tgl2=$param['periode']."-".$tglawl2;

            $hasilAbsn[]=array();
            # ambil hk
            $sGetKary="select a.karyawanid,a.nik,a.namakaryawan,subbagian,b.tipe as tipe from ".$dbname.".datakaryawan a 
                       left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id where
                       ".$whr." and tipekaryawan not in (0,7,8) and (tanggalkeluar>='".$tgl2."' or tanggalkeluar='0000-00-00')
                       order by subbagian,namakaryawan asc";    
            //echo $sGetKary; exit;
            $rGetkary=fetchdata($sGetKary);
            foreach($rGetkary as $rw=>$lstNm){
                $dtNm[$lstNm['karyawanid']]=$lstNm['namakaryawan'];
                $dtTp[$lstNm['karyawanid']]=$lstNm['tipe'];
                $dtSb[$lstNm['karyawanid']]=$lstNm['subbagian'];
                $dtKary[$lstNm['karyawanid']]=$lstNm['karyawanid'];
                $subbagian=$lstNm['subbagian'];
                if($subbagian==''){
                    $subbagian=substr($param['unitId'], 0,4);
                }
                $totKry[$subbagian][]=$lstNm['karyawanid'];;    
            }
            #ambil hk 
            if(strlen($param['unitId'])>4){
                $dimanaPnjng=" kodeorg='".$param['unitId']."'";
                $dimanaPnjng2=" substring(kodeorg,1,4)='".substr($param['unitId'],0,4)."'";
                $dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($kodeOrg,0,4)."'";
            }
            else{
                $dimanaPnjng=" substring(kodeorg,1,4)='".substr($param['unitId'],0,4)."'";
                $dimanaPnjng2=" substring(kodeorg,1,4)='".substr($param['unitId'],0,4)."'";
                $dimanaPnjng3=" substr(b.kodeorg,1,4)='".substr($param['unitId'],0,4)."'";
            }
            $sPrestasi="select a.upahkerja,b.tanggal,a.jumlahhk,a.nik,a.notransaksi from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi 
                        where b.notransaksi like '%PNN%' and ".$dimanaPnjng3." and b.tanggal between '".$tgl1."' and '".$tgl2."'";
             //exit("Error".$sPrestasi);
            $rPrestasi=fetchData($sPrestasi);
            foreach ($rPrestasi as $presBrs =>$resPres){
                $resData[$resPres['nik']]=$resPres['nik'];
                $hasilAbsn[$resPres['nik']][$resPres['tanggal']]='H';
            }
            $sKehadiran="select jhk,absensi,tanggal,karyawanid,notransaksi from ".$dbname.".kebun_kehadiran_vw 
                        where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng2."";
              //exit("Error".$sKehadiran);
            $rkehadiran=fetchData($sKehadiran);
            foreach ($rkehadiran as $khdrnBrs =>$resKhdrn){   
                if($resKhdrn['absensi']!=''){
                    $hasilAbsn[$resKhdrn['karyawanid']][$resKhdrn['tanggal']]=$resKhdrn['absensi'];
                    $resData[$resKhdrn['karyawanid']]=$resKhdrn['karyawanid'];
                }
            }
            // ambil administrasi                       
            $dzstr="SELECT tanggal,nikmandor,a.notransaksi FROM ".$dbname.".kebun_aktifitas a
                left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
                left join ".$dbname.".datakaryawan c on a.nikmandor=c.karyawanid
                where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL
                union select tanggal,keranimuat,a.notransaksi FROM ".$dbname.".kebun_aktifitas a 
                left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi
                left join ".$dbname.".datakaryawan c on a.keranimuat=c.karyawanid
                where a.tanggal between '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng3." and c.namakaryawan is not NULL";
            $dzres=$owlPDO->query($dzstr) or die(print " Gagal: ".PDOException::getMessage());
            $dzres->setFetchMode(PDO::FETCH_OBJ);
            while($dzbar=$dzres->fetch()){
                $hasilAbsn[$dzbar->nikmandor][$dzbar->tanggal]='H';
                $resData[$dzbar->nikmandor]=$dzbar->nikmandor;
            }
            //mandor
            $str="SELECT tanggal,mandor,a.notransaksi FROM ".$dbname.".vhc_splht a
                  left join ".$dbname.".datakaryawan c on a.mandor=c.karyawanid
                  where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $hasilAbsn[$bar->mandor][$bar->tanggal]='H';
                $resData[$bar->mandor]=$bar->mandor;
            }
            //mandor1
            $str="SELECT tanggal,mandor1,a.notransaksi FROM ".$dbname.".vhc_splht a
                left join ".$dbname.".datakaryawan c on a.mandor1=c.karyawanid
                where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $hasilAbsn[$bar->mandor1][$bar->tanggal]='H';
                $resData[$bar->mandor1]=$bar->mandor1;
            }
            //krani
            $str="SELECT tanggal,krani,a.notransaksi FROM ".$dbname.".vhc_splht a
                left join ".$dbname.".datakaryawan c on a.krani=c.karyawanid
                where a.tanggal between '".$tgl1."' and '".$tgl2."' and substr(a.kodeorg,1,4)='".substr($kodeOrg,0,4)."' and c.namakaryawan is not NULL";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $hasilAbsn[$bar->krani][$bar->tanggal]='H';
                $resData[$bar->krani]=$bar->krani;
            }
            #ambil dari vhc_spl_kehadiran_vw
            $sSpl="select notransaksi,tanggal,nik,kodeorg from ".$dbname.".vhc_spl_kehadiran_vw where 
                   tanggal between  '".$tgl1."' and '".$tgl2."' and kodeorg='".substr($kodeOrg,0,4)."'";
            $qSpl=$owlPDO->query($sSpl) or die(print " Gagal: ".PDOException::getMessage());
            $qSpl->setFetchMode(PDO::FETCH_ASSOC);
            while($rSpl=$qSpl->fetch()){
                $hasilAbsn[$rSpl['nik']][$rSpl['tanggal']]='H';
                $resData[$rSpl['nik']]=$rSpl['nik'];
            }
            #ambil dari sdm_absensi
            $sAbsn="select absensi,tanggal,karyawanid,kodeorg,catu from ".$dbname.".sdm_absensidt 
                        where tanggal between  '".$tgl1."' and '".$tgl2."' and ".$dimanaPnjng."";
                      //exit("Error".$sAbsn);
                    $rAbsn=fetchData($sAbsn);
                    foreach ($rAbsn as $absnBrs =>$resAbsn){
                            $whrCk="kodeabsen='".$resAbsn['absensi']."'";
                            $optCk=makeOption($dbname,'sdm_5absensi','kodeabsen,nilaihk',$whrCk);
                            if($optCk[$resAbsn['absensi']]==1){
                               if(($dtTp[$resAbsn['karyawanid']]=='BNS')||($dtTp[$resAbsn['karyawanid']]=='KHT')){
                                    if(($resAbsn['absensi']=='C')||($resAbsn['absensi']=='CB')||($resAbsn['absensi']=='CK')){
                                        continue;
                                    }
                               }
                            }
                            $hasilAbsn[$resAbsn['karyawanid']][$resAbsn['tanggal']]=$resAbsn['absensi'];
                            $resData[$resAbsn['karyawanid']]=$resAbsn['karyawanid'];
                    }
            // echo"<pre>";
            // print_r($resData);
            // echo"</pre>";
                    $tempSb='';
                    $rowSb=0;
                    $totRow=array();
                    foreach($dtKary as $lstKary){
                        if(count($hasilAbsn[$lstKary])>=4){
                            if($dtSb[$lstKary]==''){
                                $dtSb[$lstKary]=substr($param['unitId'],0,4);
                            }
                            if($tempSb!=$dtSb[$lstKary]){
                                $tempSb=$dtSb[$lstKary];
                                $totRow[$tempSb]=1;
                                $rowSb+=1;
                            }else{
                                $totRow[$tempSb]+=1;
                            }
                        }
                    }
                    $tab.="<button class=mybutton onclick=saveAll(".$rowSb.")>".$_SESSION['lang']['save']."</button>";
                    $tab.="<table cellpadding=1 cellspacing=1 border=0 class='sortable'>";
                    $tab.="<thead>";
                    $tab.="<tr class=rowheader>";
                    $tab.="<td>No.</td>";
                    $tab.="<td>".$_SESSION['lang']['unit']."</td>";
                    $tab.="<td>".$_SESSION['lang']['divisi']."</td>";
                    $tab.="<td>".$_SESSION['lang']['namakaryawan']."</td>";
                    $tab.="<td>".$_SESSION['lang']['tipekaryawan']."</td>";
                    $tab.="<td>".$_SESSION['lang']['jumlahhk']."</td>";
                    $tab.="<td>".$_SESSION['lang']['rp']."</td>";
                    $tab.="<td><input type=checkbox id=dtKbwAll onclick=cekSma() /></td>";
                    $tab.="</tr></thead><tbody>";
                   
                    $tempSb='';
                    $rowSb=0;
                    foreach($dtKary as $lstKary){
                        if(count($hasilAbsn[$lstKary])>=4){
                            $no+=1;
                            if($dtSb[$lstKary]==''){
                                $dtSb[$lstKary]=substr($param['unitId'],0,4);
                            }

                            if($tempSb!=$dtSb[$lstKary]){
                                $rowSb+=1;
                                $rowSbdt=1;
                                $tempSb=$dtSb[$lstKary];
                            }else{
                                $rowSbdt+=1;
                            }
                            
                            $whr="karyawanid='".$lstKary."' and tahun='".substr($param['periode'],0,4)."' and idkomponen=59";
                            $optGaji=makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',$whr);
                            $tab.="<tr  class=rowcontent>";
                            $tab.="<td>".$no."</td>";
                            $tab.="<td id=kdorg_".$rowSb."_".$rowSbdt.">".substr($param['unitId'],0,4)."</td>";
                            $tab.="<td id=sb_".$rowSb."_".$rowSbdt.">".$dtSb[$lstKary]."</td>";
                            $tab.="<td><input type=hidden id=karyId_".$rowSb."_".$rowSbdt." value='".$lstKary."' /> ".$dtNm[$lstKary]."</td>";
                            $tab.="<td>".$dtTp[$lstKary]."</td>";
                            $tab.="<td align=right><input type=hidden id=hkDt_".$rowSb."_".$rowSbdt." value='".count($hasilAbsn[$lstKary])."' />".count($hasilAbsn[$lstKary])."</td>";
                            $tab.="<td align=right><input type=hidden id=rpDt_".$rowSb."_".$rowSbdt." value='".$optGaji[$lstKary]."' />".number_format($optGaji[$lstKary],0)."</td>";
                            $tab.="<td align=center><input type=checkbox id=dtKbw_".$rowSb."_".$rowSbdt." />";
                            if($rowSbdt==$totRow[$tempSb]){
                                $tab.="<input type=hidden id=rowIsiData_".$rowSb." value='".$totRow[$tempSb]."' />";    
                            }
                            $tab.="</td>";
                            $tab.="</tr>";
                        } 
                        // }else{
                        //     $tab.="<tr class=rowcontent>";
                        //     $tab.="<td>".$no."</td>";
                        //     $tab.="<td>".substr($param['unitId'],0,4)."</td>";
                        //     $tab.="<td>".$dtSb[$lstKary]."</td>";
                        //     $tab.="<td>".$dtNm[$lstKary]."</td>";
                        //     $tab.="<td>".$dtTp[$lstKary]."</td>";
                        //     $tab.="<td align=right>".count($hasilAbsn[$lstKary])."</td>";
                        //     $tab.="<td align=right>".number_format($optGaji[$lstKary],0)."</td>";
                        //     $tab.="</tr>";
                        // }
                    }
                    $tab.="</tbody></table><input type=hidden id=rowIsiData  value='".$rowSb."' />";
                    $tab.="<button class=mybutton onclick=saveAll(".$rowSb.")>".$_SESSION['lang']['save']."</button>";
                    echo $tab;
        break;
        case'loaddata':
            $sPeriodeGaji="select periode,sudahproses from ".$dbname.".sdm_5periodegaji where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
            $rPeriodeGaji=fetchdata($sPeriodeGaji);
            foreach($rPeriodeGaji as $row=>$lstGaji){
                $statPrdGaji[$lstGaji['periode']]=$lstGaji['sudahproses'];
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
            $str="select * from ".$dbname.".sdm_potonganht where kodeorg like  '".$_SESSION['empl']['lokasitugas']."%' and tipepotongan=59
                 ".$where."  order by periodegaji desc ";
            $res=fetchdata($str);
            //echo $str;
            //$jlhbrs=owlBaris($res);   
            $jlhbrs=count($res);
            
            if($jlhbrs==0){
                $tab="<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['dataempty']."</td></tr>";
            }else{
                $no=0;
                $no=$maxdisplay;
                $str="SELECT * from ".$dbname.".sdm_potonganht where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' and tipepotongan=59 ".$where." 
                      order by periodegaji desc   limit ".$offset.",".$limit."";
                //echo $str;
                $tab="";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch()){
					
					if($bar['posting']==0){
						$post="<img src=images/skyblue/posting.png class=resicon  title='Posting' style='cursor:pointer' onclick=posting('".$bar['kodeorg']."','".$bar['periodegaji']."') >";
					}
					
                    $sTot="select sum(jumlahpotongan) as tot from ".$dbname.".sdm_potongandt where  
                           kodeorg='".$bar['kodeorg']."' and periodegaji='".$bar['periodegaji']."'
                           and tipepotongan=59";
                    $rTot=fetchdata($sTot);
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=center>".$bar['periodegaji']."</td>";
                    $tab.="<td align=left>".$bar['kodeorg']."</td>";
                    $tab.="<td align=right>".number_format($rTot[0]['tot'],0)."</td>";
                    $tab.="
                    <td align=center>";
                    if($statPrdGaji[$bar['periodegaji']]!=1 and $bar['posting']==0){
                        $tab.="
                            <img src=images/application/application_delete.png class=zImgBtn title='Delete'
								onclick=\"deletehead('".$bar['kodeorg']."','59','".$bar['periodegaji']."');\">";
                    }
                    if(strlen($bar['kodeorg'])==4){
                     $tab.="<img onclick=excel(event,'".$bar['kodeorg']."','".$bar['periodegaji']."','59',1) src=images/excel.jpg class=resicon title='MS.Excel'>";   
                    }
                    $tab.="<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                             onclick=\"detaildt('".$_SESSION['lang']['list']."','".$bar['kodeorg']."','59','".$bar['periodegaji']."','event');\">         
                             <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('sdm_potonganht','".$bar['kodeorg'].",".$bar['periodegaji'].",59','','sdm_slave_3gajikecilpdf',event)\">
                            <img onclick=excel(event,'".$bar['kodeorg']."','".$bar['periodegaji']."','59',0) src=images/excel.jpg class=resicon title='MS.Excel'>";         
                    $tab.=$post;
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
                    <tr><td colspan=5 align=center>
                    <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                    <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                    <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                    </td>
                    </tr>";    
            }   
            
            
            echo $tab."####".$footd;
        break;
		
        case'saveAll':
        $sInsDet='';
        //$whd=" and  kodeorg like '".$_SESSION['empl']['lokasitugas']."%'";
        if($_POST['dtKirim'][0]!=''){
            $whd=" and  kodeorg='".$_POST['dtKirim'][0]."'";
            if(strlen($_POST['dtKirim'][0])<6){
                $lokLog.=" and lokasitugas='".$_POST['dtKirim'][0]."' and (subbagian='' or subbagian is null)";
            }else{
                $lokLog.=" and lokasitugas='".substr($_POST['dtKirim'][0],0,4)."' and subbagian='".$_POST['dtKirim'][0]."'";
            }
        }
        if($tpKary!=''){
            $lokLog.=" and kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'";
            $whd=" and nik in (select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='".$tpKary."' ".$lokLog.")";
        }
            $sHt="delete from ".$dbname.".sdm_potongandt where tipepotongan=59 and periodegaji='".$param['periode']."' ".$whd."";
            try{
                $owlPDO->exec($sHt);
            }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
            $sDelHt="delete from  ".$dbname.".sdm_potonganht where tipepotongan=59 and periodegaji='".$param['periode']."' ".$whd."";
            try{
                $owlPDO->exec($sDelHt);
            }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
                $kodeorg=$_POST['dtKirim'][0];
                foreach($param['dtKary'] as $rw=>$rwDt){
                    //$param['dtRup'][$rw]=str_replace(","," ", $param['dtRup'][$rw]);
                    if($temp!=$kodeorg){
                        $temp=$kodeorg;
                        $awal=0;
                        $sIns="insert into ".$dbname .".sdm_potonganht (`kodeorg`,`periodegaji`,`tipepotongan`,`updateby`)
                                values ('".$kodeorg."','".$param['periode']."',59,'".$_SESSION['standard']['userid']."')";
                        try{
                            $owlPDO->exec($sIns);
                            $sInsDet="insert into ".$dbname.".sdm_potongandt (`kodeorg`,`tipepotongan`,`periodegaji`,`nik`,`jumlahpotongan`,`hk`) values ";
                            $sInsDet.="('".$kodeorg."','59','".$param['periode']."','".$rwDt."','".$param['dtRup'][$rw]."','".$param['dthkDt'][$rw]."')";
                        }
                        catch (PDOException $e) {
                            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                            die(); 
                        }
                    }else{
                        $awal+=1;
                        $sInsDet.=",('".$kodeorg."','59','".$param['periode']."','".$rwDt."','".$param['dtRup'][$rw]."','".$param['dthkDt'][$rw]."')";
                    }
                }
                try{
                    $owlPDO->exec($sInsDet);
                }
                catch (PDOException $e) {
                    print " Gagal  !: ".$sInsDet."__" . $e->getMessage() . "<br/>"; 
                    die(); 
                }

        break;
        case'deleteDt':
            $sdel="delete from ".$dbname.".sdm_potongandt where periodegaji='".$param['periodegaji']."'
                   and kodeorg='".$param['kodeorg']."' and tipepotongan='".$param['tipepotongan']."' ";
            try{$owlPDO->exec($sdel);}
            catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); 
            }
            $sDelHt="delete from ".$dbname.".sdm_potonganht where periodegaji='".$param['periodegaji']."'
                         and kodeorg='".$param['kodeorg']."' and tipepotongan='".$param['tipepotongan']."'";
            try{$owlPDO->exec($sDelHt);}
            catch (PDOException $e){print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); 
            }
        break;
        case'excel':

        $iHead = "select * from " . $dbname . ".sdm_potonganht 
        where kodeorg='".$kodeorg."' and periodegaji='" . $periodegaji . "' and tipepotongan='" . $tipepotongan . "'";
        $nHead=$owlPDO->query($iHead) or die(print " Gagal: ".PDOException::getMessage());
        $nHead->setFetchMode(PDO::FETCH_ASSOC);
        $dHead = $nHead->fetch();
        $stream.="Gaji Kecil<br>";
        $stream =$_SESSION['lang']['divisi']." : " . $kodeorg . "<br>";
        if($mode==1){
            $stream =$_SESSION['lang']['unit']." : " . $kodeorg . "<br>";
        }
        
        $stream.=$_SESSION['lang']['periode'].": ".$periodegaji."<br>";

        $optNmJbtn=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');

        $stream.="<br /><table class=sortable border=1 cellspacing=1>
             <thead>
                <tr>
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nourut'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['divisi'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['nik'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['namakaryawan'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['tipekaryawan'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['jabatan'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['hk'] . "</td> 
                    <td align=center bgcolor=#CCCCCC>" . $_SESSION['lang']['rp'] . "</td> 
                </tr>";
            $whrMod=" and kodeorg='".$kodeorg."' ";
            if($mode==1){
                $whrMod=" and kodeorg like '".$kodeorg."%' ";
            }
            $res=array();
            $iDet = "select * from ".$dbname.".sdm_potongandt where periodegaji='".$periodegaji."' and tipepotongan='".$tipepotongan."' ".$whrMod."  order by kodeorg asc";
            $res=fetchdata($iDet);
            $tot = 0;
            $rowDtsb=array();
            foreach($res as $key=>$dDet){
                if($tmpKd!=$dDet['kodeorg']){
                    $tmpKd=$dDet['kodeorg'];
                    $rowDtsb[$tmpKd]=1;
                }else{
                    $rowDtsb[$tmpKd]+=1;
                }
            }
            foreach($res as $key=>$dDet){
                $wh = "karyawanid='" . $dDet['nik'] . "'";
                $optNik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', $wh);
                $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $wh);
                $optTp = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', $wh);
                $optJbtn = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan', $wh);
                if($tempLok!=$dDet['kodeorg']){
                        $tempLok=$dDet['kodeorg'];
                        $rwDataRl=1;
                        $subTot=0;
                        $subTotHk=0;
                }else{
                        $rwDataRl+=1;
                }
                $no+=1;
                $whrOrg="kodeorganisasi='".$dDet['kodeorg']."'";
                $nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrOrg);
                $DivNm=$nmOrg[$dDet['kodeorg']];
                if(strlen($dDet['kodeorg'])==4){
                    $DivNm="Umum";
                }
                $stream.="<tr>
                            <td>".$no."</td>
                            <td>".$DivNm."</td>
                            <td>'".$optNik[$dDet['nik']]."</td>
                            <td>".$optNm[$dDet['nik']]."</td>
                            <td>".(isset($arrNmtp[$optTp[$dDet['nik']]]) ? $arrNmtp[$optTp[$dDet['nik']]] : "") . "</td>
                            <td>".$optNmJbtn[$optJbtn[$dDet['nik']]] . "</td>
                            <td align=right>".$dDet['hk'] ."</td>
                            <td align=right>" . number_format($dDet['jumlahpotongan']) . "</td>
                        </tr>";
    			$tot+=$dDet['jumlahpotongan'];
                $totHk+=$dDet['hk'];
                $subTot+=$dDet['jumlahpotongan'];
                $subTotHk+=$dDet['hk'];
                if($rwDataRl==$rowDtsb[$dDet['kodeorg']]){
                      $stream.="<tr  bgcolor=#CCCCCC><td colspan=6 align=right>Sub Total ".$DivNm."</td>
                      <td align=right>" . number_format($subTotHk) . "</td>
                      <td align=right>" . number_format($subTot) . "</td>
                      </tr>";
                }
                
            }
        $stream.="<tr>
                        <td colspan=6>Grand Total</td>
                        <td align=right>".number_format($totHk)."</td>
                        <td align=right>".number_format($tot)."</td>
                    </tr></table>";

        $stream.="</tbody></table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
        $tglSkrg=date("YmdHms");
        //$nop_="laporan_total_komponen_gaji_".$kdorg."_".$per1."_sd_".per2;
        $nop_="gajikecil__divisi__".$kodeorg."__".$tglSkrg;
        if($mode==1){
            $nop_="gajikecil__unit__".$kodeorg."__".$tglSkrg;
        }
        if(strlen($stream)>0)
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
                if(!fwrite($handle,$stream))
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
        case'detailDt':
        $stream.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>
        <div  style=overflow:auto;height:275px;>
        <table class=\"sortable\" cellspacing=\"1\" border=\"0\">
         <thead>
         <tr class=rowheader>
            <td>No.</td>
            <td>".$_SESSION['lang']['nik']."</td> 
            <td>".$_SESSION['lang']['namakaryawan']."</td> 
            <td>".$_SESSION['lang']['tipekaryawan']."</td> 
            <td>".$_SESSION['lang']['lokasitugas']."</td> 
            <td>".$_SESSION['lang']['rp']."</td> 
         </tr>
         </thead>
         <tbody>";
         $iDet = "select * from ".$dbname.".sdm_potongandt where periodegaji='".$periodegaji."' and kodeorg='".$param['kdOrg'] . "'
                 and tipepotongan='".$tipepotongan."'  order by nik asc";
         //echo $iDet;
         $nDet=fetchdata($iDet);
        foreach($nDet as $row=>$dDet){
            $wh = "karyawanid='".$dDet['nik']."'";
            $optNik = makeOption($dbname, 'datakaryawan', 'karyawanid,nik', $wh);
            $optNm = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', $wh);
            $optTp = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', $wh);
            $no+=1;
            $stream.="<tr class=rowcontent>
                        <td>".$no."</td>
                        <td>".$optNik[$dDet['nik']]."</td>
                        <td>".$optNm[$dDet['nik']]."</td>
                        <td>".(isset($arrNmtp[$optTp[$dDet['nik']]]) ? $arrNmtp[$optTp[$dDet['nik']]] : "") . "</td>
                        <td>".$dDet['kodeorg']."</td>
                        <td align=right>".number_format($dDet['jumlahpotongan'])."</td></tr>";
            $tot+=$dDet['jumlahpotongan'];
        }
        $stream.="<tr><td colspan=5>".$_SESSION['lang']['total']."</td>
                  <td align=right>".number_format($tot)."</td></tr>";
        $stream.="</tbody>
                  </table></div></fieldset>";
        echo $stream;
    break;
    }

?>
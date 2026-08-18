<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$optNm=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optasset=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$karyawanid = checkPostGet('karyawanid', '');
$alasan = checkPostGet('alasan', '');
$kdbrg = checkPostGet('kdbrg', '');
$nmbrg = checkPostGet('nmbrg', '');
$notranscr = checkPostGet('notranscr', '');
$tglcr = tanggalsystem(checkPostGet('tglcr', ''));
$optstatus=array("0"=>"Belum Menyetujui","1"=>"Disetujui","2"=>"Ditolak");

switch ($method) {
    case 'loadData':
        $where = "";
        if ($notranscr != '') {
            $where.=" and notransaksi like '%" . $notranscr . "%' ";
        }
        if ($tglcr != '') {
            $where.=" and tanggal='" . $tglcr . "' ";
        }
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".log_formcapex_ht where (diperiksa1='".$_SESSION['standard']['userid']."' or
                   diperiksa2='".$_SESSION['standard']['userid']."' or budget='".$_SESSION['standard']['userid']."' or
                   menyetujui1='".$_SESSION['standard']['userid']."' or menyetujui2='".$_SESSION['standard']['userid']."') and status_pengajuan!='0' ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".log_formcapex_ht where (diperiksa1='".$_SESSION['standard']['userid']."' or
                   diperiksa2='".$_SESSION['standard']['userid']."' or budget='".$_SESSION['standard']['userid']."' or
                   menyetujui1='".$_SESSION['standard']['userid']."' or menyetujui2='".$_SESSION['standard']['userid']."') and status_pengajuan!='0' ".$where." order by tanggal desc, status_pengajuan asc   limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".$bar['kodept']."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                #pembuat
                $whrKar2="karyawanid='".$bar['dibuat_oleh']."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar['notransaksi']."</td>
                    <td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optpt[$bar['kodept']]."</td>";
                for($a=1;$a<3;$a++){
                    if ($bar['stat_periksa'.$a]==0){
                        if($bar['diperiksa'.$a]==$_SESSION['standard']['userid']){
                            $isi=$optttd[$bar['diperiksa'.$a]]."<br>
                              <button class=mybutton onclick=\"approved_capex('".$bar['notransaksi']."','".$bar['diperiksa'.$a]."')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
                              <button class=mybutton onclick=rejected_capex('".$bar['notransaksi']."','".$bar['diperiksa'.$a]."')>".$_SESSION['lang']['ditolak']."</button>";
                        } else if($bar['diperiksa'.$a]!=$_SESSION['standard']['userid']){
                            $isi=$optttd[$bar['diperiksa'.$a]]."<br> (Belum Menyetujui)";
                        }
                    }else if ($bar['stat_periksa'.$a]=='1'){
                        $isi=$optttd[$bar['diperiksa'.$a]]."<br>(Disetujui)";
                    }else if ($bar['stat_periksa'.$a]=='2'){
                        $isi=$optttd[$bar['diperiksa'.$a]]."<br>(Ditolak)";
                    }
                    $tab.="<td align=center>".$isi."</td>";
                } 

                if ($bar['stat_budget']==0){
                    if($bar['budget']==$_SESSION['standard']['userid']){
                        $isi=$optttd[$bar['budget']]."<br>
                          <button class=mybutton onclick=\"get_data_asset('".$bar['notransaksi']."','".$bar['budget']."')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
                          <button class=mybutton onclick=rejected_capex('".$bar['notransaksi']."','".$bar['budget']."')>".$_SESSION['lang']['ditolak']."</button>";
                    }else if($bar['budget']!=$_SESSION['standard']['userid']){
                        $isi=$optttd[$bar['budget']]."<br> (Belum Menyetujui)";
                    }
                    }else if ($bar['stat_budget']=='1'){
                        $isi=$optttd[$bar['budget']]."<br>(Disetujui)";
                    }else if ($bar['stat_budget']=='2'){
                        $isi=$optttd[$bar['budget']]."<br>(Ditolak)";
                }
                    $tab.="<td align=center>".$isi."</td>";

                for($a=1;$a<3;$a++){
                    if ($bar['subtotal']>50000000||(($bar['subtotal']<=50000000)&&($a=='1'))){
                        if ($bar['stat_menyetujui'.$a]==0){
                            if($bar['menyetujui'.$a]==$_SESSION['standard']['userid']){
                                $isi=$optttd[$bar['menyetujui'.$a]]."<br>
                                  <button class=mybutton onclick=\"approved_capex('".$bar['notransaksi']."','".$bar['menyetujui'.$a]."')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
                                  <button class=mybutton onclick=rejected_capex('".$bar['notransaksi']."','".$bar['menyetujui'.$a]."')>".$_SESSION['lang']['ditolak']."</button>";
                            }else if($bar['menyetujui'.$a]!=$_SESSION['standard']['userid']){
                                $isi=$optttd[$bar['menyetujui'.$a]]."<br> (Belum Menyetujui)";
                            }
                        }else if ($bar['stat_menyetujui'.$a]=='1'){
                            $isi=$optttd[$bar['menyetujui'.$a]]."<br>(Disetujui)";
                        }else if ($bar['stat_menyetujui'.$a]=='2'){
                            $isi=$optttd[$bar['menyetujui'.$a]]."<br>(Ditolak)";
                        }
                    }else if (($bar['subtotal']<=50000000)&&($a=='2')){
                            $isi="Tidak Ada Persetujuan 2<br> (Budget < Rp. 50.000.000,-)";
                    }
                    $tab.="<td align=center>".$isi."</td>";
                }     
                $tab.="<td align=center>";
                $tab.="<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar['notransaksi']. "',event);\" >";
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
                <tr><td colspan=10 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
	
	case 'approved_capex':
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();

        if($karyawanid==$bar['diperiksa1']){
            $hslpersetujuan=2;
        } else if($karyawanid==$bar['diperiksa2']){
            // $hslpersetujuan=$bar['stat_periksa1'];
            $hslpersetujuan=$bar['stat_periksa1'];
        } else if($karyawanid==$bar['budget']){
            $hslpersetujuan=$bar['stat_periksa2'];
        } else if($karyawanid==$bar['menyetujui1']){
            $hslpersetujuan=$bar['stat_budget'];
        } else if($karyawanid==$bar['menyetujui2']){
            $hslpersetujuan=$bar['stat_menyetujui1'];
        }

        if ($hslpersetujuan!=0){
            echo"<div id=rejected_form>
            <fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table cellspacing=1 border=0>
                    <tr>
                        <td>Alasan</td>
                        <td>:</td>
                        <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
                    </tr>
                    <td><td><td>
                    <button class=mybutton onclick=disetejuicapex() id=ditolak >".$_SESSION['lang']['disetujui']."</button>
                    <button class=mybutton onclick=cancel_asset()>".$_SESSION['lang']['cancel']."</button>
                    </td></tr></table>
            </fieldset>
            </div>
            <input type=hidden name=user_id id=user_id value=".$user_id." />
            <input type=hidden name=notransaksi id=notransaksi value=".$notransaksi."  />
            <input type=hidden name=karyawanid id=karyawanid value='".$karyawanid."' />";
        } else {
            echo "<fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table cellspacing=1 border=0>
                <tr>
                    <td>Note : Harap menunggu persetujuan sebelumnya.</td>
                </tr>
            </table>
            </fieldset>";
        }
    break;

    case'disetejuicapex':
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $kodeorg=$bar['kodept'];
        $unit=$bar['unit'];
        $tanggalmulai=$bar['tanggal'];
        $dibuat_oleh=$bar['dibuat_oleh'];
        $diperiksa1=$bar['diperiksa1'];
        $tgl_periksa1=$bar['tgl_periksa1'];
        $diperiksa2=$bar['diperiksa2'];
        $tgl_periksa2=$bar['tgl_periksa2'];
        $budget=$bar['budget'];

        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeorg."' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        $namapengaju = getNamaKaryawan($bar['dibuat_oleh']);
        $subject = "[Notifikasi]Pengajuan Capex dengan Nomor Transaksi ".$notransaksi;
        $body = "<html>
                    <head>
                     <body>
                       <dd>Dengan Hormat,</dd><br>
                       <br>
                       Karyawan a/n  ".$namapengaju." mengajukan permintaan capex dengan Nomor Transaksi ".$notransaksi.".<br>                          
                       <br>
                       <br>
                       Untuk melihat detail dan melakukan persetujuan silahkan lakukan di menu Pengadaan->Transaksi->Persetujuan Capex
                       <br>
                       <br>
                       Regards,<br>
                       Owl-Plantation System.
                     </body>
                    </head>
                 </html>";


        if($karyawanid==$bar['diperiksa1'] && $bar['stat_periksa1']=='0'){
            $set=" stat_periksa1='1',tgl_periksa1='" . date('Y-m-d') . "', alasan_periksa1='".$alasan."' ";
            $to = getUserEmail($bar['diperiksa2']);
            $hslpersetujuan=1;
        } else if($karyawanid==$bar['diperiksa2'] && $bar['stat_periksa2']=='0'){
            $set=" stat_periksa2='1',tgl_periksa2='" . date('Y-m-d') . "',alasan_periksa2='".$alasan."' ";
            $to = getUserEmail($bar['budget']);
            $hslpersetujuan=$bar['stat_periksa1'];
        } else if($karyawanid==$bar['menyetujui1'] && $bar['stat_menyetujui1']=='0'){
			$hslpersetujuan=$bar['stat_budget'];

            if ($bar['subtotal']>50000000){
                $set=" stat_menyetujui1='1',tgl_menyetujui1='" . date('Y-m-d') . "', alasan_menyetujui1='".$alasan."' ";
                $to = getUserEmail($bar['menyetujui2']);
            }else if (($bar['subtotal']<=50000000)&&($bar['stat_periksa1']==1)&&($bar['stat_periksa2']==1)&&($bar['stat_budget']==1)){
				$set=" stat_menyetujui1='1',tgl_menyetujui1='" . date('Y-m-d') . "', status_pengajuan='2', alasan_menyetujui1='".$alasan."' ";

                //get persetujuan 3
                $smanagerm="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PP3' and kodeunit='$unit'";
                $qmanagerm=$owlPDO->query($smanagerm) or die(print " Gagal: ".PDOException::getMessage());
                $qmanagerm->setFetchMode(PDO::FETCH_ASSOC);
                $rmanagerm=$qmanagerm->fetch();
                $persetujuan3=$rmanagerm['karyawanid'];
                $whrunit="kodeorganisasi='".$unit."'";
                $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrunit);
                if ($persetujuan3==''){
                    // exit('Warning : Persetujuan 3 pada '.$optunit[$unit].' Belum ada. Harap tambahkan data persetujuan di menu Setup->Persetujuan/Approval.');
                }else{
                    $to =getUserEmail($rmanagerm['karyawanid']);
                }

                //select log_formcapex_assetcode 
                $sasset = "select * from ".$dbname.".log_formcapex_assetcode where notransaksi ='".$notransaksi."'";
                $qasset = $owlPDO->query($sasset) or die (print "Gagal : ".PDOException::getMessage());
                $qasset->setFetchMode(PDO::FETCH_OBJ);
                while($rasset=$qasset->fetch()){
                    //get no.project
                    $kode ='AK-'.$rasset->kodeasset.$rasset->subtipeasset;

                    // cari nomor terakhir
                    $str="select kode from ".$dbname.".project where kode like '".$kode."%' order by substring(kode, -5) desc  limit 1";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $belakangnya=intval(substr($bar->kode,-5));
                    }
                    $belakangnya+=1;
                    
                    $belakangnya=addZero($belakangnya,10-strlen($rasset->kodeasset.$rasset->subtipeasset));
                    $kode='AK-'.$rasset->kodeasset.$rasset->subtipeasset.$belakangnya;

                    //get tanggalselesai
                    $stgleta="SELECT * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."' and kodebarang='".$rasset->kodebarang."'";
                    $rtgleta=$owlPDO->query($stgleta) or die(print " Gagal: ".PDOException::getMessage());
                    $rtgleta->setFetchMode(PDO::FETCH_ASSOC);
                    $btgleta=$rtgleta->fetch();

                    //insert project
                    $str="insert into ".$dbname.".project (kode, nama, tipe, kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,keterangan,jenis_biaya) values('".$kode."','".$rasset->namaasset."','AK','".$unit."','".$tanggalmulai."','".$btgleta['tanggal_eta']."',".$budget.",'".$rasset->subtipeasset."','".$notransaksi."','".$rasset->jenis_biaya."')";
                    try{
                        $owlPDO->exec($str); 
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                    }

                }

                //get nopp
                $bln = substr($tanggalmulai,5,2);
                $thn = substr($tanggalmulai,0,4);
                //unit
                $nopp="/".$thn."/PR/".$unit;
                
                $ql="select `nopp` from ".$dbname.".`log_prapoht` where nopp like '%".$nopp."%' order by `nopp` desc limit 0,1";
                $qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
                $qr->setFetchMode(PDO::FETCH_OBJ);
                $rp=$qr->fetch();

                @$awal=substr($rp->nopp,0,3);
                @$awal=intval($awal);
                @$cekbln=substr($rp->nopp,4,2);
                @$cekthn=substr($rp->nopp,7,4);
                
                if($thn!=$cekthn){
                    $awal=1;
                }else{
                    $awal++;
                }

                $counter=addZero($awal,3);
                $nopp=$counter."/".$bln."/".$thn."/PR/".$unit;                    

                //insert log_prapoht
                $str="insert into ".$dbname.".log_prapoht (kodeorg, nopp, tanggal, dibuat,persetujuan1,persetujuan2,persetujuan3,hasilpersetujuan1,hasilpersetujuan2,tglp1,tglp2,keterangan,close)
                values('".$kodeorg."','".$nopp."','".$tanggalmulai."','".$dibuat_oleh."','".$diperiksa1."','".$diperiksa2."','".$persetujuan3."','1','1','".$tgl_periksa1."','".$tgl_periksa2."','".$notransaksi."','2')";
                try{
                    $owlPDO->exec($str); 
                }catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }

                //insert log_prapodt
                $sdt="SELECT * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."'";
                $rdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
                $rdt->setFetchMode(PDO::FETCH_ASSOC);
                while ($bdt=$rdt->fetch()) {
                    $str="insert into ".$dbname.".log_prapodt (nopp, kodebarang, jumlah,hargasatuan,keterangan,tgl_sdt,updateby)
                    values('".$nopp."','".$bdt['kodebarang']."','".$bdt['jumlah']."','".$bdt['hargasatuan']."','".$bdt['catatan']."','".$bdt['tanggal_eta']."','".$dibuat_oleh."')";
                    try{
                        $owlPDO->exec($str); 
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                    }
                    
                }

            }

        } else if($karyawanid==$bar['menyetujui2']){
            $hslpersetujuan=$bar['stat_menyetujui1'];

            if(($bar['stat_periksa1']==1)&&($bar['stat_periksa2']==1)&&($bar['stat_budget']==1)&&($bar['stat_menyetujui1']==1)){
                $set=" stat_menyetujui2='1',tgl_menyetujui2='" . date('Y-m-d') . "', status_pengajuan='2', alasan_menyetujui2='".$alasan."'  ";

                //get persetujuan 3
                $smanagerm="select distinct karyawanid from ".$dbname.".setup_approval where jenispersetujuan='PP3' and kodeunit='$unit'";
                $qmanagerm=$owlPDO->query($smanagerm) or die(print " Gagal: ".PDOException::getMessage());
                $qmanagerm->setFetchMode(PDO::FETCH_ASSOC);
                $rmanagerm=$qmanagerm->fetch();
                $persetujuan3=$rmanagerm['karyawanid'];

                $whrunit="kodeorganisasi='".$unit."'";
                $optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrunit);
                if ($persetujuan3==''){
					$optHO = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$unit."'");
					if($optHO[$unit]!='HOLDING')
					{
						exit('Warning : Persetujuan 3 pada '.$optunit[$unit].' Belum ada. Harap tambahkan data persetujuan di menu Setup->Persetujuan/Approval.');
					}
                }else{
                    $to =getUserEmail($rmanagerm['karyawanid']);
                }

                //select log_formcapex_assetcode 
                $sasset = "select * from ".$dbname.".log_formcapex_assetcode where notransaksi ='".$notransaksi."'";
                $qasset = $owlPDO->query($sasset) or die (print "Gagal : ".PDOException::getMessage());
                $qasset->setFetchMode(PDO::FETCH_OBJ);
                while($rasset=$qasset->fetch()){
                    //get no.project
                    $kode ='AK-'.$rasset->kodeasset.$rasset->subtipeasset;

                    // cari nomor terakhir
                    $str="select kode from ".$dbname.".project where kode like '".$kode."%' order by substring(kode, -5) desc  limit 1";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    while($bar=$res->fetch()){
                        $belakangnya=intval(substr($bar->kode,-5));
                    }
                    $belakangnya+=1;
                    
                    $belakangnya=addZero($belakangnya,10-strlen($rasset->kodeasset.$rasset->subtipeasset));
                    $kode='AK-'.$rasset->kodeasset.$rasset->subtipeasset.$belakangnya;

                    //get tanggalselesai
                    $stgleta="SELECT * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."' and kodebarang='".$rasset->kodebarang."'";
                    $rtgleta=$owlPDO->query($stgleta) or die(print " Gagal: ".PDOException::getMessage());
                    $rtgleta->setFetchMode(PDO::FETCH_ASSOC);
                    $btgleta=$rtgleta->fetch();

                    //insert project
                    $str="insert into ".$dbname.".project (kode, nama, tipe, kodeorg,tanggalmulai,tanggalselesai,updateby,subtipe,keterangan,jenis_biaya) values('".$kode."','".$rasset->namaasset."','AK','".$unit."','".$tanggalmulai."','".$btgleta['tanggal_eta']."',".$budget.",'".$rasset->subtipeasset."','".$notransaksi."','".$rasset->jenis_biaya."')";
                    try{
                        $owlPDO->exec($str); 
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                    }

                }

                //get nopp
                $bln = substr($tanggalmulai,5,2);
                $thn = substr($tanggalmulai,0,4);
        
                $nopp="/".$thn."/PR/".$unit;
                
                $ql="select `nopp` from ".$dbname.".`log_prapoht` where nopp like '%".$nopp."%' order by `nopp` desc limit 0,1";
                $qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
                $qr->setFetchMode(PDO::FETCH_OBJ);
                $rp=$qr->fetch();

                @$awal=substr($rp->nopp,0,3);
                @$awal=intval($awal);
                @$cekbln=substr($rp->nopp,4,2);
                @$cekthn=substr($rp->nopp,7,4);
                
                if($thn!=$cekthn){
                    $awal=1;
                }else{
                    $awal++;
                }

                $counter=addZero($awal,3);
                $nopp=$counter."/".$bln."/".$thn."/PR/".$unit;                    

                //insert log_prapoht
                $str="insert into ".$dbname.".log_prapoht (kodeorg, nopp, tanggal, dibuat,persetujuan1,persetujuan2,persetujuan3,hasilpersetujuan1,hasilpersetujuan2,tglp1,tglp2,keterangan,close)
                values('".$kodeorg."','".$nopp."','".$tanggalmulai."','".$dibuat_oleh."','".$diperiksa1."','".$diperiksa2."',".$persetujuan3.",'1','1','".$tgl_periksa1."','".$tgl_periksa2."','".$notransaksi."','2')";
                try{
                    $owlPDO->exec($str); 
                }catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                }

                //insert log_prapodt
                $sdt="SELECT * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."'";
                $rdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
                $rdt->setFetchMode(PDO::FETCH_ASSOC);
                while ($bdt=$rdt->fetch()) {
                    $str="insert into ".$dbname.".log_prapodt (nopp, kodebarang, jumlah,hargasatuan,keterangan,tgl_sdt,updateby)
                    values('".$nopp."','".$bdt['kodebarang']."','".$bdt['jumlah']."','".$bdt['hargasatuan']."','".$bdt['catatan']."','".$bdt['tanggal_eta']."','".$dibuat_oleh."')";
                    try{
                        $owlPDO->exec($str); 
                    }catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
                    }
                    
                }

            }
        }

        if ($hslpersetujuan!=0){
            $str = "update " . $dbname . ".log_formcapex_ht set ".$set." where notransaksi ='" . $notransaksi . "'";
            try {
                $owlPDO->exec($str);
                if ($to!=''){
                    $kirim = kirimEmail($to, '', $subject, $body);
                }

            } catch (PDOException $e) {
                print " Gagal  : " . $e->getMessage() . "\n";
                die();
            }
        } else {
            exit('Warning : Harap menunggu persetujuan sebelumnya.');
        }
    break;

    case 'get_data_asset':

        $exnotran=explode('/', $notransaksi);
        $unit=$exnotran[2];

        //jenis biaya
        $arjb = getEnum($dbname, 'log_formcapex_assetcode', 'jenis_biaya');
        foreach ($arjb as $kei => $fal) {
            if ((substr($unit,2,2)=='HO')&&($fal!=3)){
                continue;   
            }

            if ((substr($unit,2,2)!='HO')&&($fal==3)){
                continue;
            }

            if ($fal==1){
                $capt="Biaya Langsung";
            }
            if ($fal==2){
                $capt="Biaya Tidak Langsung";
            }
            if ($fal==3){
                $capt="Operasi";
            }
            $optjb.="<option value='".$kei."'>".$capt."</option>";
        }

        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $hslpersetujuan=$bar['stat_periksa2'];

        if ($hslpersetujuan!=0){
        echo"<div id=rejected_form>
            <fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table class=sortable cellspacing=1 border=0>
                <thead>
                <tr class=rowheader>    
                    <td align=center >".$_SESSION['lang']['nourut']."</td>
                    <td align=center >" . $_SESSION['lang']['namabarang'] . "</td>
                    <td align=center >" . $_SESSION['lang']['subtipeasset'] . "</td>
                    <td align=center >" . $_SESSION['lang']['namaasset'] . "</td>
                    <td align=center >" . $_SESSION['lang']['jenisbiaya'] . "</td>
                </tr>
                </thead>";
                $no=0;
                $no2=0;
                $str="SELECT * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."'";
                // echo $str;
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch()){
                $no+=1;
                    $skl="SELECT * from ".$dbname.".bgt_5capex where kelbarang='".substr($bar['kodebarang'],0,3)."'";
                    $rkl=$owlPDO->query($skl) or die(print " Gagal: ".PDOException::getMessage());
                    $rkl->setFetchMode(PDO::FETCH_ASSOC);
                    $bkl=$rkl->fetch();

                    $iSub="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$bkl['kodecapex']."' ";
                    $rsub=$owlPDO->query($iSub) or die(print " Gagal: ".PDOException::getMessage());
                    $rsub->setFetchMode(PDO::FETCH_ASSOC);
                    while($dSub=  $rsub->fetch())
                    {
                        $optSub.="<option ".$select." value='".$dSub['kodesub']."'>".$dSub['namasub']."</option>";
                    }
                    echo"<tr class=rowcontent>
                            <td style='text-align:center;'>".$no2."</td>
                            <td><input type=text id=nmbrg_".$no2." class=myinputtext value='".$optNmBrg[$bar['kodebarang']]."' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled></td>
                                <input type=hidden id=kdbrg_".$no2." class=myinputtext value='".$bar['kodebarang']."' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled>
                                <input type=hidden id=kdasset_".$no2." class=myinputtext value='".$bkl['kodecapex']."' onkeypress=\"return tanpa_kutip(event);\" style='width:150px;' disabled>
                            <td><select id=subasset_".$no2." style='width:100px;' >".$optSub."</select></td>
                            <td><input type=text id=nama_".$no2." class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:195px;'></td>
                            <td><select id=jbiaya_".$no2." style='width:100px;' >".$optjb."</select></td>
                        </tr>";
                        $optSub="";
                        $no2+=1;
                }
        echo"<tr><td colspan=4 align=center>
                <button class=mybutton onclick=simpanasset() id=simpanasset >".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=cancel_asset()>".$_SESSION['lang']['cancel']."</button></td></tr>
             </table>
            </fieldset>
            </div>
            <input type=hidden id=totrows value='".$no2."' />
            <input type=hidden name=user_id id=user_id value=".$user_id." />
            <input type=hidden name=notransaksi id=notransaksi value=".$notransaksi."  />
            <input type=hidden name=karyawanid id=karyawanid value='".$karyawanid."' />";
        } else {
            echo "<fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table cellspacing=1 border=0>
                <tr>
                    <td>Note : Harap menunggu persetujuan sebelumnya.</td>
                </tr>
            </table>
            </fieldset>";
        }
    break;

    case'simpanasset':
        for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            if ($_POST['nama'][$arDt]==''){
                exit('Warning : Nama asset harus diisi.');
            }
        }

        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();

        $kodeorg=$bar['kodept'];
        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeorg."' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        $to = getUserEmail($bar['menyetujui1']);
        $namapengaju = getNamaKaryawan($bar['dibuat_oleh']);
        $subject = "[Notifikasi]Pengajuan Capex dengan Nomor Transaksi ".$notransaksi;
        $body = "<html>
                    <head>
                     <body>
                       <dd>Dengan Hormat,</dd><br>
                       <br>
                       Karyawan a/n  ".$namapengaju." mengajukan permintaan capex dengan Nomor Transaksi ".$notransaksi.".<br>                          
                       <br>
                       <br>
                       Untuk melihat detail dan melakukan persetujuan silahkan lakukan di menu Pengadaan->Transaksi->Persetujuan Capex
                       <br>
                       <br>
                       Regards,<br>
                       Owl-Plantation System.
                     </body>
                    </head>
                 </html>";

        $str = "update " . $dbname . ".log_formcapex_ht set stat_budget='1',tgl_budget='" . date('Y-m-d') . "' where notransaksi ='" . $notransaksi . "'";
            try {
                $owlPDO->exec($str);
                    $sDet="insert into ".$dbname.".log_formcapex_assetcode values ";
                    for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                        if($arDt==0){
                            $sDet.=" ('".$notransaksi."','".$_POST['kdbrg'][$arDt]."','".$_POST['kdasset'][$arDt]."','".$_POST['subasset'][$arDt]."','".$_POST['nama'][$arDt]."','".$_POST['jbiaya'][$arDt]."')";
                        }else{
                            $sDet.=",('".$notransaksi."','".$_POST['kdbrg'][$arDt]."','".$_POST['kdasset'][$arDt]."','".$_POST['subasset'][$arDt]."','".$_POST['nama'][$arDt]."','".$_POST['jbiaya'][$arDt]."')";
                        }
                    }
                    try{ 
                        $owlPDO->exec($sDet); 
                    }
                    catch (PDOException $e){
                    echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
                    }

                    if ($to!=''){
                        $kirim = kirimEmail($to, '', $subject, $body);
                    }

            } catch (PDOException $e) {
                print " Gagal  : " . $e->getMessage() . "\n";
                die();
            }

    break;

    case 'rejected_capex':
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();

        if($karyawanid==$bar['diperiksa1']){
            $hslpersetujuan=2;
        } else if($karyawanid==$bar['diperiksa2']){
            // $hslpersetujuan=$bar['stat_periksa1'];
            $hslpersetujuan=$bar['stat_periksa1'];
        } else if($karyawanid==$bar['budget']){
            $hslpersetujuan=$bar['stat_periksa2'];
        } else if($karyawanid==$bar['menyetujui1']){
            $hslpersetujuan=$bar['stat_budget'];
        } else if($karyawanid==$bar['menyetujui2']){
            $hslpersetujuan=$bar['stat_menyetujui1'];
        }

        if ($hslpersetujuan!=0){
            echo"<div id=rejected_form>
            <fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table cellspacing=1 border=0>
                    <tr>
                        <td>Alasan</td>
                        <td>:</td>
                        <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
                    </tr>
                    <td><td><td>
                    <button class=mybutton onclick=ditolakcapex() id=ditolak >".$_SESSION['lang']['ditolak']."</button>
                    <button class=mybutton onclick=cancel_asset()>".$_SESSION['lang']['cancel']."</button>
                    </td></tr></table>
            </fieldset>
            </div>
            <input type=hidden name=user_id id=user_id value=".$user_id." />
            <input type=hidden name=notransaksi id=notransaksi value=".$notransaksi."  />
            <input type=hidden name=karyawanid id=karyawanid value='".$karyawanid."' />";
        } else {
            echo "<fieldset>
            <legend><input type=text readonly=readonly name=rnotransaksi id=rnotransaksi value=".$notransaksi."  /></legend>
            <table cellspacing=1 border=0>
                <tr>
                    <td>Note : Harap menunggu persetujuan sebelumnya.</td>
                </tr>
            </table>
            </fieldset>";
        }
    break;

    case'ditolakcapex':
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();

        $kodeorg=$bar['kodept'];
        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeorg."' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        $namapengaju = getNamaKaryawan($bar['dibuat_oleh']);
        $subject = "[Notifikasi]Pengajuan Capex dengan Nomor Transaksi ".$notransaksi;
        $body = "<html>
                    <head>
                     <body>
                       <dd>Dengan Hormat,</dd><br>
                       <br>
                       Karyawan a/n  ".$namapengaju." mengajukan permintaan capex dengan Nomor Transaksi ".$notransaksi.".<br>                          
                       <br>
                       <br>
                       Untuk melihat detail dan melakukan persetujuan silahkan lakukan di menu Pengadaan->Transaksi->Persetujuan Capex
                       <br>
                       <br>
                       Regards,<br>
                       Owl-Plantation System.
                     </body>
                    </head>
                 </html>";

        if($karyawanid==$bar['diperiksa1']){
            $set=" stat_periksa1='2',tgl_periksa1='" . date('Y-m-d') . "', alasan_periksa1='".$alasan."' ";
            $to = getUserEmail($bar['diperiksa2']);
        } else if($karyawanid==$bar['diperiksa2']){
            $set=" stat_periksa2='2',tgl_periksa2='" . date('Y-m-d') . "', alasan_periksa2='".$alasan."' ";
            $to = getUserEmail($bar['budget']);
        } else if($karyawanid==$bar['budget']){
            $set=" stat_budget='2',tgl_budget='" . date('Y-m-d') . "', alasan_budget='".$alasan."' ";
            $to = getUserEmail($bar['menyetujui1']);
        } else if($karyawanid==$bar['menyetujui1']){
            if ($bar['subtotal']<=50000000){
                $set=" stat_menyetujui1='2',tgl_menyetujui1='" . date('Y-m-d') . "', alasan_menyetujui1='".$alasan."', status_pengajuan='2'  ";
            }else{
                $set=" stat_menyetujui1='2',tgl_menyetujui1='" . date('Y-m-d') . "', alasan_menyetujui1='".$alasan."' ";
            }
            $to = getUserEmail($bar['menyetujui2']);
        } else if($karyawanid==$bar['menyetujui2']){
            $set=" stat_menyetujui2='2',tgl_menyetujui2='" . date('Y-m-d') . "', alasan_menyetujui2='".$alasan."', status_pengajuan='2' ";
            $to ='';
        }

        $str = "update " . $dbname . ".log_formcapex_ht set ".$set." where notransaksi ='" . $notransaksi . "'";
        try {
            $owlPDO->exec($str);

            if ($to!=''){
                $kirim = kirimEmail($to, '', $subject, $body);
            }

        } catch (PDOException $e) {
            print " Gagal  : " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'viewdetail':
        //get data spdt dan spht
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notransaksi."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
        $subtotal=$bar->subtotal;

            #diperiksa1
            $whrKar1="karyawanid='".$bar->diperiksa1."'";
            $optdiperiksa1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #diperiksa2
            $whrKar2="karyawanid='".$bar->diperiksa2."'";
            $optdiperiksa2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #budget 
            $whrKar3="karyawanid='".$bar->budget."'";
            $optbudget=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3); 
            #menyetujui1 
            $whrKar4="karyawanid='".$bar->menyetujui1."'";
            $optmenyetujui1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar4);
            #menyetujui2 
            $whrKar5="karyawanid='".$bar->menyetujui2."'";
            $optmenyetujui2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar5); 
            #namapt
            $whrpt="kodeorganisasi='".$bar->kodept."'";
            $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);    
            
            $tab="<legend><b>DETAIL PURCHASE REQUEST</b></legend><br>";
            $tab.="<table align=left border=0>
            
            <tr>
                <td>No Transaksi</td>
                <td> : </td>
                <td>".$notransaksi."</td>
            </tr>
            <tr>
                <td>Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optdiperiksa1[$bar->diperiksa1]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa1]." ".($bar->alasan_periksa1==''?'':'('.$bar->alasan_periksa1.')')."</td>
            </tr>
            <tr>
                <td>Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optdiperiksa2[$bar->diperiksa2]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa2]." ".($bar->alasan_periksa2==''?'':'('.$bar->alasan_periksa2.')')."</td>
            </tr>
            <tr>
                <td>Budget</td>
                <td> : </td>
                <td>".$optbudget[$bar->budget]."</td>
            </tr>
            <tr>
                <td>Status Budget</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_budget]." ".($bar->alasan_budget==''?'':'('.$bar->alasan_budget.')')."</td>
            </tr>
            <tr>
                <td>Persetujuan 1</td>
                <td> : </td>
                <td>".$optmenyetujui1[$bar->menyetujui1]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui1]." ".($bar->alasan_menyetujui1==''?'':'('.$bar->alasan_menyetujui1.')')."</td>
            </tr>";
        if ($subtotal>50000000){
        $tab.="<tr>
                <td>Persetujuan 2</td>
                <td> : </td>
                <td>".$optmenyetujui2[$bar->menyetujui2]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui2]." ".($bar->alasan_menyetujui2==''?'':'('.$bar->alasan_menyetujui2.')')."</td>
            </tr>";
        }
        $tab.="<tr colspan=3>
                <td>&nbsp;</td>
            </tr>
            <tr colspan=3>
                <td><b>Detail Barang</b></td>
            </tr>
            <tr >
                <td colspan=3>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']." ETA</td>
                    <td align=center>".$_SESSION['lang']['namabarang']."</td>
                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td align=center>" . $_SESSION['lang']['harga'] . " ".$_SESSION['lang']['satuan']."</td>
                    <td align=center>" . $_SESSION['lang']['total'] . "</td>
                    <td align=center>" . $_SESSION['lang']['catatan'] . "</td>
                </tr>
                </thead>";

                $no = 0;
                $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notransaksi."'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $total=($bar->jumlah)*($bar->hargasatuan);
                    $no+=1;
                    $tab.="<tr class=rowcontent>   
                        <td>".$no."</td>
                        <td>".tanggalnormal($bar->tanggal_eta)."</td>
                        <td>".$optNmBrg[$bar->kodebarang]."</td>
                        <td align=center>".$bar->jumlah."</td>
                        <td align=right>".@number_format($bar->hargasatuan)."</td>
                        <td align=right>".@number_format($total)."</td>
                        <td align=justify>".$bar->catatan."</td>
                        </tr>";
            
                }
                $tab.="<tr class=rowcontent>   
                        <td colspan=5 align=right>Subtotal</td>
                        <td align=right>".@number_format($subtotal)."</td>
                        <td></td>
                       </tr>
                        </table>
            </td>
            </tr>
            </table>";

        echo $tab;
    break;

}


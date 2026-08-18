<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$nopengajuan = checkPostGet('nopengajuan', '');
$nopengajuancr = checkPostGet('nopengajuancr', '');
$tglcr = tanggalsystem(checkPostGet('tglcr', ''));
$persetujuan = checkPostGet('persetujuan', '');
$penandatangan1 = checkPostGet('penandatangan1', '');
$penandatangan2 = checkPostGet('penandatangan2', '');
$keterangan = checkPostGet('keterangan', '');
$karyawanid = checkPostGet('karyawanid', '');
$alasan = checkPostGet('alasan', '');

switch ($method) {
	case 'loadData':
        $where = "";
            if ($nopengajuancr != '') {
                $where.=" and a.nopengajuan like '%" . $nopengajuancr . "%' ";
            }
            if ($tglcr != '') {
                $where.=" and a.tanggalpengajuan='" . $tglcr . "' ";
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
        $str="select * from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where 1=1 ".$where." and (a.persetujuan1='".$_SESSION['standard']['userid']."' or a.persetujuan2='".$_SESSION['standard']['userid']."' ) group by a.nopengajuan,a.karyawanid ";
        //print_r($str);
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            //and (a.persetujuan1='".$_SESSION['standard']['userid']."' or a.persetujuan2='".$_SESSION['standard']['userid']."' ) 
            $str = "select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where 1=1 ".$where." and (a.persetujuan1='".$_SESSION['standard']['userid']."' or a.persetujuan2='".$_SESSION['standard']['userid']."' ) group by a.nopengajuan,a.karyawanid order by a.tanggalpengajuan desc limit ".$offset.",".$limit."";
            //exit($str);
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
            $no+=1;
            $optSt=makeOption($dbname,'sdm_5jenissp','kode,keterangan');
            #namakaryawan
            $whrKar1="karyawanid='".$bar->karyawanid."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #persetujuan1
            $whrKar2="karyawanid='".$bar->persetujuan1."'";
            $optpersetujuan1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #persetujuan2
            $whrKar3="karyawanid='".$bar->persetujuan2."'";
            $optpersetujuan2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);
            echo"<tr class=rowcontent>
                    <td style='text-align:center;'>" . $no . "</td>
                    <td>".$bar->nopengajuan."</td>
                    <td>".tanggalnormal($bar->tanggalpengajuan)."</td>
                    <td>".$optkaryawan[$bar->karyawanid]."</td>
                    <td>" .$optpersetujuan1[$bar->persetujuan1]. "</td>
                    <td align=center>";
                    if (($bar->statuspersetujuan1==0)&&($bar->persetujuan1==$_SESSION['standard']['userid'])){
                        echo"<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."' onclick=\"disetujuisp1('".$bar->nopengajuan."');\">
                            <img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."' onclick=\"formalasan('" .$bar->nopengajuan."','".$bar->persetujuan1."');\">";
                    }else if ($bar->statuspersetujuan1==1){
                        echo"<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."'>";
                    }else if ($bar->statuspersetujuan1==2){
                        echo "<img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."'>";
                    }else{
                        echo "Not Authorized";
                    }
                    echo "</td>
                    <td>" .$optpersetujuan2[$bar->persetujuan2]. "</td>
                    <td align=center>";
                    if (($bar->statuspersetujuan2==0)&&($bar->statuspersetujuan1!=0)&&($bar->persetujuan2==$_SESSION['standard']['userid'])){
                        echo"<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."' onclick=\"disetujuisp2('".$bar->nopengajuan."');\">
                            <img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."' onclick=\"formalasan('" .$bar->nopengajuan."','".$bar->persetujuan2."');\">";
                    }else if ($bar->statuspersetujuan2==1){
                        echo"<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."'>";
                    }else if ($bar->statuspersetujuan2==2){
                        echo "<img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."'>";
                    }else if ($bar->statuspersetujuan1==0){
                        echo "Proses Persetujuan 1";
                    }else{
                        echo "Not Authorized";
                    }
                    echo "</td>
                    <td style='text-align:center;'>";
                    if (($bar->statuspersetujuan1==1)&&($bar->statuspersetujuan2==1)){
                        echo tanggalnormal($bar->tanggaldisetujui2);
                    }else{
                        echo "-";
                    }
                    echo "</td>
                    <td style='text-align:right;'>";
                    if (($bar->statuspersetujuan1==1)&&($bar->statuspersetujuan2==1)){   
                        echo "&nbsp<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewsp('".$bar->nopengajuan."',event);\">";
                    }
                    echo"&nbsp<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar->nopengajuan. "',event);\" >
                        </td>        
                </tr>";
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
                <tr><td colspan=11 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";   
        }
        echo $tab."####".$footd;
	break;

	case'disetujuisp1':

        $str = "update " . $dbname . ".sdm_pengajuanspht set statuspersetujuan1='1',tanggaldisetujui1='" . date('Y-m-d') . "'"
                . " where nopengajuan ='" . $nopengajuan . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  : " . $e->getMessage() . "\n";
            die();
        }

        //get data spht dan spdt
        $ssp = "select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        $qsp = $owlPDO->query($ssp) or die (print "Gagal : ".PDOException::getMessage());
        $qsp->setFetchMode(PDO::FETCH_OBJ);
        $rsp=$qsp->fetch();
        $kodesp=substr($rsp->idjenispelanggaran,0,3);
        $kodeorg=substr($rsp->kodeorg,0,2);
        $tanggal=$rsp->tanggaldisetujui2;
        $statuspersetujuan1=$rsp->statuspersetujuan1;
        $statuspersetujuan2=$rsp->statuspersetujuan2;
        //exit($statuspersetujuan1);

        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        //get data surat
        $sjenissp = "select * from ".$dbname.".sdm_5jenissp where kode='".$kodesp."'";
        $qjenissp = $owlPDO->query($sjenissp) or die (print "Gagal : ".PDOException::getMessage());
        $qjenissp->setFetchMode(PDO::FETCH_OBJ);
        $rjenissp=$qjenissp->fetch();
        $jenissp=$rjenissp->keterangan;

        //get data karyawan
        $skarya = "select a.namakaryawan,a.nik,a.kodegolongan ,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                 where  a.karyawanid=" .$rsp->karyawanid;
        $qkarya=$owlPDO->query($skarya) or die(print " Gagal: ".PDOException::getMessage());
        $qkarya->setFetchMode(PDO::FETCH_OBJ);
        $rkarya = $qkarya->fetch();
        $nik          = $rkarya->nik;
        $namakaryawan = $rkarya->namakaryawan;
        $jabatan      = $rkarya->namajabatan;

        if ($statuspersetujuan1!=0){
            $to = getUserEmail($rsp->persetujuan2);
            $namakaryawan = getNamaKaryawan($rsp->karyawanid);
            $namapengaju = getNamaKaryawan($_SESSION['standard']['userid']);
            $subject = "[Notifikasi]Pengajuan Surat Pelanggaran a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pada hari ini, tanggal " . date('d-m-Y') . " Manager/ Div Head telah melakukan approval terhadap Pengajuan Surat Pelanggaran yang diajukan oleh  ".$namapengaju.". Pengajuan Surat peringatan/" . $jenissp . "
                           diberikan kepada : <br>
                           <table>
                               <tr>
                                    <td>Nik</td>
                                    <td>:</td>
                                    <td>".$nik."</td>
                               </tr> 
                               <tr>
                                    <td>Karyawan</td>
                                    <td>:</td>
                                    <td>".$namakaryawan."</td>
                               </tr> 
                               <tr>
                                    <td>jabatan</td>
                                    <td>:</td>
                                    <td>".$jabatan."</td>
                               </tr>
                            </table>
                           <br>
                           <br>
                           Untuk melihat detail dan melakukan persetujuan selanjutnya silahkan klik link dibawah ini : <br>
                           <a href=sdm_persetujuansp.php>Verifikasi Pengajuan Surat Peringatan</a>
                           <br>
                           <br>
                           Regards,<br>
                           ".$namaorg.".
                         </body>
                        </head>
                     </html>";
            $kirim = kirimEmail($to, '', $subject, $body);
        }
    break;

    case'formalasan':
        $spoht="select * from ".$dbname.".sdm_pengajuanspht where nopengajuan='".$nopengajuan."'";
        //exit($spoht);
        $qpoht=$owlPDO->query($spoht) or die (print"Gagal : ".PDOException::getMessage());
        $qpoht->setFetchMode(PDO::FETCH_ASSOC);
        $rpoht=$qpoht->fetch();

        echo"
            <div id=test style=display:block>
            <fieldset>
            <legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopengajuan id=rnopengajuan value=".$nopengajuan." style=\"width:90px;\"  /></legend>
            <table cellspacing=1 border=0>
            <tr>
                <td colspan=3>Masukkan alasan penolakan:</td>
            </tr>
            <tr>
                <td>Alasan </td>
                <td>:</td>
                <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
            </tr>
            <td><td><td>
            <button class=mybutton onclick=ditolaksp() id=ditolaksp >".$_SESSION['lang']['ditolak']."</button>

            <button class=mybutton onclick=cancelverifikasi()>".$_SESSION['lang']['cancel']."</button>
            </td></tr></table> 
            <input type=hidden name=persetujuan id=persetujuan value='".$persetujuan."' /> 
            <input type=hidden name=method id=method value='ditolaksp' /> 
            <input type=hidden name=nopengajuan id=nopengajuan value=".$nopengajuan."  /> 
            </fieldset></div>";
                    
    break;

    case'ditolaksp':
        $spoht="select * from ".$dbname.".sdm_pengajuanspht where nopengajuan='".$nopengajuan."'";
        //exit($spoht);
        $qpoht=$owlPDO->query($spoht) or die (print"Gagal : ".PDOException::getMessage());
        $qpoht->setFetchMode(PDO::FETCH_ASSOC);
        $rpoht=$qpoht->fetch();

            for ($i=1; $i <3 ; $i++) { 
                if ($persetujuan==$rpoht['persetujuan'.$i]) {
                
                    $str = "update " . $dbname . ".sdm_pengajuanspht set statuspersetujuan".$i."='2',tanggaldisetujui".$i."='".date('Y-m-d')."', alasanditolak".$i."='".$alasan."' where nopengajuan ='" . $nopengajuan . "'";
                    try {
                        $owlPDO->exec($str); 
                    } catch (PDOException $e) {
                        print " Gagal  : " . $e->getMessage() . "\n";
                        die();
                    }
                }
            }

        //get data spht dan spdt
        $ssp = "select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        $qsp = $owlPDO->query($ssp) or die (print "Gagal : ".PDOException::getMessage());
        $qsp->setFetchMode(PDO::FETCH_OBJ);
        $rsp=$qsp->fetch();
        $kodesp=substr($rsp->idjenispelanggaran,0,3);
        $kodeorg=substr($rsp->kodeorg,0,2);
        $tanggal=$rsp->tanggaldisetujui2;
        $statuspersetujuan1=$rsp->statuspersetujuan1;
        $statuspersetujuan2=$rsp->statuspersetujuan2;
        //exit($statuspersetujuan1);

        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        //get data surat
        $sjenissp = "select * from ".$dbname.".sdm_5jenissp where kode='".$kodesp."'";
        $qjenissp = $owlPDO->query($sjenissp) or die (print "Gagal : ".PDOException::getMessage());
        $qjenissp->setFetchMode(PDO::FETCH_OBJ);
        $rjenissp=$qjenissp->fetch();
        $jenissp=$rjenissp->keterangan;

        //get data karyawan
        $skarya = "select a.namakaryawan,a.nik,a.kodegolongan ,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                 where  a.karyawanid=" .$rsp->karyawanid;
        $qkarya=$owlPDO->query($skarya) or die(print " Gagal: ".PDOException::getMessage());
        $qkarya->setFetchMode(PDO::FETCH_OBJ);
        $rkarya = $qkarya->fetch();
        $nik          = $rkarya->nik;
        $namakaryawan = $rkarya->namakaryawan;
        $jabatan      = $rkarya->namajabatan;

        if (($statuspersetujuan1!=0)&&($persetujuan==$rsp->persetujuan1)){
            $to = getUserEmail($rsp->persetujuan2);
            $namakaryawan = getNamaKaryawan($rsp->karyawanid);
            $namapengaju = getNamaKaryawan($rsp->updateby);
            $subject = "[Notifikasi]Pengajuan Surat Pelanggaran a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pada hari ini, tanggal " . date('d-m-Y') . " Manager/ Div Head telah melakukan approval terhadap Pengajuan Surat Pelanggaran yang diajukan oleh  ".$namapengaju.". Pengajuan Surat peringatan/" . $jenissp . "
                           diberikan kepada : <br>
                           <table>
                               <tr>
                                    <td>Nik</td>
                                    <td>:</td>
                                    <td>".$nik."</td>
                               </tr> 
                               <tr>
                                    <td>Karyawan</td>
                                    <td>:</td>
                                    <td>".$namakaryawan."</td>
                               </tr> 
                               <tr>
                                    <td>jabatan</td>
                                    <td>:</td>
                                    <td>".$jabatan."</td>
                               </tr>
                            </table>
                           <br>
                           <br>
                           Untuk melihat detail silahkan lihat di menu SDM->Transaksi->Persetujuan Surat Peringatan atau klik link dibawah ini : <br>
                           <a href=sdm_persetujuansp.php>Verifikasi Pengajuan Surat Peringatan</a>
                           <br>
                           <br>
                           Regards,<br>
                           ".$namaorg.".
                         </body>
                        </head>
                     </html>";
            exit("warning: ".$subject."<br>".$body);
            $kirim = kirimEmail($to, '', $subject, $body);
        }

        if (($statuspersetujuan1==2)&&($statuspersetujuan2==2)){
            $to = getUserEmail($rsp->updateby);
            $namakaryawan = getNamaKaryawan($rsp->karyawanid);
            $namapengaju = getNamaKaryawan($rsp->updateby);
            $subject = "[Notifikasi]Penolakan Surat Pelanggaran a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pada hari ini, tanggal " . date('d-m-Y') . " Manager/ Div Head dan HRD menolak Pengajuan Surat Pelanggaran kepada  ".$namakaryawan.". 
                           Dengan alasan ".$rsp->alasanditolak1." dan ".$rsp->alasanditolak2.".
                           <br>
                           <br>
                           Untuk melihat detail silahkan lihat di menu SDM->Transaksi->Persetujuan Surat Peringatan atau klik link dibawah ini : <br>
                           <a href=sdm_persetujuansp.php>Verifikasi Pengajuan Surat Peringatan</a>
                           <br>
                           <br>
                           Regards,<br>
                           ".$namaorg.".
                         </body>
                        </head>
                     </html>";
            exit("warning : ".$body);
            $kirim = kirimEmail($to, '', $subject, $body);
        }
    break;

    case'formpejabat':
        $spoht="select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.". sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        //exit($spoht);
        $qpoht=$owlPDO->query($spoht) or die (print"Gagal : ".PDOException::getMessage());
        $qpoht->setFetchMode(PDO::FETCH_ASSOC);
        $rpoht=$qpoht->fetch();

        //get penandatangan1
        $optPenandatangan1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $spenandatangan1="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where 
                        (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and kodejabatan in ('55','108') and left(lokasitugas,2)='".substr($_SESSION['empl']['lokasitugas'],0,2)."' order by namakaryawan asc";
        $qpenandatangan1=$owlPDO->query($spenandatangan1) or die(print " Gagal: ".PDOException::getMessage());
        $qpenandatangan1->setFetchMode(PDO::FETCH_ASSOC);
            while($rpenandatangan1=$qpenandatangan1->fetch())
            {
                $optPenandatangan1.="<option value=".$rpenandatangan1['karyawanid'].">".$rpenandatangan1['namakaryawan']." - ".$rpenandatangan1['nik']." - ".$rpenandatangan1['lokasitugas']."</option>";
            }

        //get penandatangan2
        $optPenandatangan2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $spenandatangan2="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where 
                        (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and bagian in ('HRD','HHRD','HRA') and lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan asc";
        $qpenandatangan2=$owlPDO->query($spenandatangan2) or die(print " Gagal: ".PDOException::getMessage());
        $qpenandatangan2->setFetchMode(PDO::FETCH_ASSOC);
            while($rpenandatangan2=$qpenandatangan2->fetch())
            {
                $optPenandatangan2.="<option value=".$rpenandatangan2['karyawanid'].">".$rpenandatangan2['namakaryawan']." - ".$rpenandatangan2['nik']." - ".$rpenandatangan2['lokasitugas']."</option>";
            }
            echo"
                <div id=test style=display:block>
                <fieldset>
                <legend><input align=center class=myinputtext disabled type=text readonly=readonly name=rnopengajuan id=rnopengajuan value=".$nopengajuan." style=\"width:90px;\"  /></legend>
                <table cellspacing=1 border=0>
                <tr>
                    <td colspan=3>Pilih Penandatangan:</td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['penandatangan']." 1</td>
                    <td>:</td>
                    <td valign=top>
                        <select id=penandatangan1 name=penandatangan  style=\"width:150px;\">".$optPenandatangan1."</select>
                    </td>
                </tr>
                <tr>
                    <td>".$_SESSION['lang']['penandatangan']." 2</td>
                    <td>:</td>
                    <td valign=top>
                        <select id=penandatangan2 name=penandatangan  style=\"width:150px;\">".$optPenandatangan2."</select>
                    </td>
                </tr>
                <tr>
                <td><td><td>
                <button class=mybutton onclick=verifikasihrd() id=verifikasihrd >".$_SESSION['lang']['disetujui']."</button>

                <button class=mybutton onclick=cancelverifikasi()>".$_SESSION['lang']['cancel']."</button>
                </td></tr></table> 
                <input type=hidden name=karyawanid id=karyawanid value='".$rpoht['karyawanid']."' /> 
                <input type=hidden name=method id=method value='updatepejabat' /> 
                <input type=hidden name=nopengajuan id=nopengajuan value=".$nopengajuan."  /> 
                </fieldset></div>";
                    
    break;

    case'updatepejabat':
        //exit("warning :".$karyawanid);
        $strht = "update " . $dbname . ".sdm_pengajuanspht set pejabat1='".$penandatangan1."', pejabat2='".$penandatangan2."', statuspersetujuan2='1',tanggaldisetujui2='" . date('Y-m-d') . "' where nopengajuan ='".$nopengajuan."'";
        //$strdt = "update " . $dbname . ".sdm_pengajuanspdt set keterangan='".$keterangan."' where nopengajuan ='".$nopengajuan."'";
        try {
            $owlPDO->exec($strht);
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal  : " . $e->getMessage() . "\n";
            die();
        }

        //get data spht dan spdt
        $ssp = "select a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.".sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        $qsp = $owlPDO->query($ssp) or die (print "Gagal : ".PDOException::getMessage());
        $qsp->setFetchMode(PDO::FETCH_OBJ);
        $rsp=$qsp->fetch();
        $kodesp=substr($rsp->idjenispelanggaran,0,3);
        $kodeorg=substr($rsp->kodeorg,0,2);
        $tanggal=$rsp->tanggaldisetujui2;
        $statuspersetujuan1=$rsp->statuspersetujuan1;
        $statuspersetujuan2=$rsp->statuspersetujuan2;

        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        //get data surat
        $sjenissp = "select * from ".$dbname.".sdm_5jenissp where kode='".$kodesp."'";
        $qjenissp = $owlPDO->query($sjenissp) or die (print "Gagal : ".PDOException::getMessage());
        $qjenissp->setFetchMode(PDO::FETCH_OBJ);
        $rjenissp=$qjenissp->fetch();
        $jenissp=$rjenissp->keterangan;

        //get data karyawan
        $skarya = "select a.namakaryawan,a.nik,a.kodegolongan ,b.namajabatan from " . $dbname . ".datakaryawan a left join  " . $dbname . ".sdm_5jabatan b on a.kodejabatan=b.kodejabatan
                 where  karyawanid=" . $karyawanid;
        $qkarya=$owlPDO->query($skarya) or die(print " Gagal: ".PDOException::getMessage());
        $qkarya->setFetchMode(PDO::FETCH_OBJ);
        $rkarya = $qkarya->fetch();
        $nik          = $rkarya->nik;
        $namakaryawan = $rkarya->namakaryawan;
        $jabatan      = $rkarya->namajabatan;

        // if (($statuspersetujuan1==1)&&($statuspersetujuan2==1)){
        //         #send an email to incharge person
        //         $to = getUserEmail($karyawanid);
        //         $namakaryawan = getNamaKaryawan($karyawanid);
        //         $subject = "[Notifikasi] Surat Peringatan Kepada " . $namakaryawan;
        //         $body = "<html>
        //                     <head>
        //                      <body>
        //                        <dd>Dengan Hormat,</dd><br>
        //                        <br>
        //                        Dengan ini ".$namaorg.", memberikan ".$jenissp." yang berlaku sejak tanggal penetapan SK ini kepada : <br>
        //                        <table>
        //                        <tr>
        //                             <td>Nik</td>
        //                             <td>:</td>
        //                             <td>".$nik."</td>
        //                        </tr> 
        //                        <tr>
        //                             <td>Karyawan</td>
        //                             <td>:</td>
        //                             <td>".$namakaryawan."</td>
        //                        </tr> 
        //                        <tr>
        //                             <td>jabatan</td>
        //                             <td>:</td>
        //                             <td>".$jabatan."</td>
        //                        </tr>
        //                        </table>
        //                        Demikian pemberitahuan ini diberikan kepada yang bersangkutan untuk diketahui.
        //                        kepada saudara/i. Untuk menindak-lanjuti, silahkan ikuti link dibawah.
        //                        <br>
        //                        <br>
        //                        <br>
        //                        <br>
        //                        Regards,<br>
        //                        ".$namaorg.".
        //                      </body>
        //                      </head>
        //                 </html>";
        //         $kirim = kirimEmail($to, '', $subject, $body); 
        // }


    break;

    case 'viewdetail':

        //get data spdt dan spht
        $str="SELECT a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.".sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();

        //get data surat
        $ssp="SELECT * from ".$dbname.".sdm_5jenissp where kode='".substr($bar->idjenispelanggaran,0,3)."'";
        $qsp=$owlPDO->query($ssp) or die(print " Gagal: ".PDOException::getMessage());
        $qsp->setFetchMode(PDO::FETCH_OBJ);
        $rsp=$qsp->fetch();

        //get data jenis pelanggaran
        $sjp="SELECT a.*, b.pelanggaran from ".$dbname.".sdm_pengajuanspdt a left join ".$dbname.".sdm_5jenispelanggaran b on a.idjenispelanggaran=b.idjenispelanggaran where a.nopengajuan='".$nopengajuan."'";
        $qjp=$owlPDO->query($sjp) or die(print " Gagal: ".PDOException::getMessage());
        $qjp->setFetchMode(PDO::FETCH_OBJ);
        while ($rjp=$qjp->fetch()) {
            $no+=1;
            $pelanggaran.="<li>".$rjp->pelanggaran."</li>";
        }

            #karyawan
            $whrKar1="karyawanid='".$bar->karyawanid."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #persetujuan 1
            $whrKar2="karyawanid='".$bar->persetujuan1."'";
            $optpersetujuan1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #persetujuan 1
            $whrKar3="karyawanid='".$bar->persetujuan2."'";
            $optpersetujuan2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);    
            if (($bar->statuspersetujuan1==0)&&($bar->statuspersetujuan2==0)){
                $status="Menunggu Persetujuan";
            }else if (($bar->statuspersetujuan1==1)&&($bar->statuspersetujuan2==1)){
                $status="Disetujui";
            }else{
               $status="Ditolak";
            }
            $tab="<legend><b>DETAIL SURAT PENGAJUAN</b></legend><br>";
            $tab.="<table align=left border=0>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['nopengajuan'] . "</td>
                <td> : </td>
                <td>".$bar->nopengajuan."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
                <td> : </td>
                <td>".tanggalnormal($bar->tanggalpengajuan)."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['namakaryawan'] . "</td>
                <td> : </td>
                <td>".$optkaryawan[$bar->karyawanid]."</td>
            </tr>
            <tr >
                <td style=width:150px;>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['surat'] . "</td>
                <td> : </td>
                <td>".$rsp->keterangan."</td>
            </tr>
            <tr>
                <td style='vertical-align:top;=width:150px;'>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['pelanggaran'] . "</td>
                <td style='vertical-align:top'> : </td>
                <td style='vertical-align:top;text-align:justify'><ol type='1'>".$pelanggaran."</ol></td>
            </tr>
            <tr>
                <td style=width:150px;>Persetujuan 1</td>
                <td> : </td>
                <td>".$optpersetujuan1[$bar->persetujuan1]."</td>
            </tr>
            <tr>
                <td style=width:150px;>Persetujuan 2</td>
                <td> : </td>
                <td>".$optpersetujuan2[$bar->persetujuan2]."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['status'] . "</td>
                <td> : </td>
                <td>".$status."</td>
            </tr>";
            if($bar->statuspersetujuan1==2){
                $tab.="<tr>
                        <td style='vertical-align:top;width:150px;'>Alasan Penolakan Manager/Div Head</td>
                        <td style='vertical-align:top'> : </td>
                        <td style='vertical-align:top;text-align:justify'>".$bar->alasanditolak1."</td>
                       </tr>";
            }
            if($bar->statuspersetujuan2==2){
                $tab.="<tr>
                        <td style='vertical-align:top;width:150px;'>Alasan Penolakan HRD</td>
                        <td style='vertical-align:top'> : </td>
                        <td style='vertical-align:top;text-align:justify'>".$bar->alasanditolak2."</td>
                       </tr>";
            }

            $tab.="</table>";
        echo $tab;
    break;
	
	default:
		# code...
		break;
}

?>
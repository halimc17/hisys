<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$alasan = checkPostGet('alasan', '');
$karyawan = checkPostGet('karyawan', '');
$karyawancr = checkPostGet('karyawancr', '');
$tglevaluasi = tanggalsystem(checkPostGet('tglevaluasi', ''));
$tgleva = checkPostGet('tgleva', '');
$tglevaluasicr = tanggalsystem(checkPostGet('tglevaluasicr', ''));
$persetujuan = checkPostGet('persetujuan', '');
$penandatangan1 = checkPostGet('penandatangan1', '');
$penandatangan2 = checkPostGet('penandatangan2', '');
$karyawanid = checkPostGet('karyawanid', '');
$optrekomen=array("1"=>"Pengangkatan","2"=>"Kontrak Diperpanjang","3"=>"Kontrak Diperbarui","5"=>"Pemutusan Hubungan Kerja");

switch ($method) {
    case 'loadData':
        $where = "";
        if ($tglevaluasicr != '') {
            $where.=" and tanggalevaluasi='" . $tglevaluasicr . "' ";
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
        $str="select * from ".$dbname.".sdm_evaluasiht where  ttd1='".$_SESSION['standard']['userid']."' ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_evaluasiht where ttd1='".$_SESSION['standard']['userid']."' ".$where."  order by tanggalevaluasi desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrKar1="karyawanid='".$bar->karyawanid."'";
                $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
                #ttd
                $whrKar2="karyawanid='".$bar->ttd."'";
                $optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                #ttd1
                $whrKar3="karyawanid='".$bar->ttd1."'";
                $optttd1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->unit."</td>
                    <td>".tanggalnormal($bar->tanggalevaluasi)."</td>
                    <td>".$optkaryawan[$bar->karyawanid]."</td>
                    <td>".$optrekomen[$bar->rekomendasi]."</td>
                    <td>".$optttd[$bar->ttd]."</td>
                    <td>".$optttd1[$bar->ttd1]."</td>
                    <td align=center>";
                        if ($bar->spersetujuan==0){
                            $tab.="<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."' onclick=\"disetujuisp('" . $bar->tanggalevaluasi. "','".$bar->karyawanid."');\">
                                <img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."' onclick=\"formalasan('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."');\">";
                        }else if ($bar->spersetujuan==1){
                            $tab.="<img src=images/icons/accept.png class=resicon title='".$_SESSION['lang']['disetujui']."'>";
                        }else{
                            $tab.="<img src=images/icons/cancel.png class=resicon title='".$_SESSION['lang']['ditolak']."'>";
                        }
                        $tab.="</td>
                    <td style='text-align:right;'>";
                        if ($bar->spersetujuan==1){   
                            $tab.="&nbsp<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewep('" . $bar->tanggalevaluasi. "','".$bar->karyawanid."',event);\">";
                        }
                        $tab.="&nbsp<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."',event);\" ";
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
                <tr><td colspan=9 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case 'viewdetail':

        //get data spdt dan spht
        $str="SELECT * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();

            #karyawan
            $whrKar1="karyawanid='".$bar->karyawanid."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #ttd
            $whrKar2="karyawanid='".$bar->ttd."'";
            $optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #ttd 1
            $whrKar3="karyawanid='".$bar->ttd1."'";
            $optttd1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3); 
            #ttd 2
            $whrKar4="karyawanid='".$bar->ttd2."'";
            $optttd2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar4);
            #ttd 3
            $whrKar5="karyawanid='".$bar->ttd3."'";
            $optttd3=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar5);     
            if ($bar->spersetujuan==0){
                $status="Menunggu Persetujuan";
            }else if ($bar->spersetujuan==1){
                $status="Disetujui";
            }else{
               $status="Ditolak";
            }
            $tab="<legend><b>DETAIL PENGAJUAN EVALUASI PERCOBAAN</b></legend><br>";
            $tab.="<table align=left border=0>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . "</td>
                <td> : </td>
                <td>".$bar->unit."</td>
            </tr>
            <tr>
                <td>Tanggal Evaluasi</td>
                <td> : </td>
                <td>".tanggalnormal($bar->tanggalevaluasi)."</td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['namakaryawan'] . "</td>
                <td> : </td>
                <td>".$optkaryawan[$bar->karyawanid]."</td>
            </tr>
            <tr>
                <td>Kekuatan</td>
                <td> : </td>
                <td>".$bar->kekuatan."</td>
            </tr>
            <tr>
                <td>Perbaikan Diperlukan</td>
                <td> : </td>
                <td>".$bar->perbaikandiperlukan."</td>
            </tr>
            <tr>
                <td>Catatan</td>
                <td> : </td>
                <td>".$bar->catatan."</td>
            </tr>

            <tr>
                <td>Rekomendasi</td>
                <td> : </td>
                <td>".$optrekomen[$bar->rekomendasi]."</td>
            </tr>

            <tr>
                <td>Mengajukan</td>
                <td> : </td>
                <td>".$optttd[$bar->ttd]."</td>
            </tr>

            <tr>
                <td>Menyetujui</td>
                <td> : </td>
                <td>".$optttd1[$bar->ttd1]."</td>
            </tr>

            <tr>
                <td>HC & GA Head</td>
                <td> : </td>
                <td>".$optttd2[$bar->ttd2]."</td>
            </tr>

            <tr>
                <td>HC Officer</td>
                <td> : </td>
                <td>".$optttd3[$bar->ttd3]."</td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['status'] . "</td>
                <td> : </td>
                <td>".$status."</td>
            </tr>";

            if($bar->spersetujuan==2){
                $tab.="<tr>
                        <td>Alasan Penolakan </td>
                        <td> : </td>
                        <td>".$bar->alasanpenolakan."</td>
                       </tr>";
            }

        $tab.="<tr colspan=3>
                <td>&nbsp;</td>
            </tr>
            <tr colspan=3>
                <td><b>Kriteria Penilaian</b></td>
            </tr>
            <tr >
                <td colspan=3>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['kriteria']."</td>
                    <td align=center>" . $_SESSION['lang']['jenis'] . " ".$_SESSION['lang']['penilaian']."</td>
                    <td align=center>" . $_SESSION['lang']['nilai'] . "</td>
                </tr>
                </thead>";

                $no = 0;
                $str="select * from ".$dbname.".sdm_evaluasidt where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $no+=1;
                    $whr1="idjenispenilaian='".$bar->idjenispenilaian."'";
                    $optjenispenilaian=makeOption($dbname,'sdm_5kriteriapenilaian','idjenispenilaian,penilaian',$whr1);
                    $skrit="select * from ".$dbname.".sdm_5jeniskriteria where kode='".substr($bar->idjenispenilaian,0,2)."'";
                    $rkrit = $owlPDO->query($skrit) or die(print " Gagal: " . PDOException::getMessage());
                    $rkrit->setFetchMode(PDO::FETCH_OBJ);
                    $bkrit = $rkrit->fetch();
                    $tab.="<tr class=rowcontent>   
                        <td>".$no."</td>
                        <td>".$bkrit->kriteria."</td>
                        <td>".$optjenispenilaian[$bar->idjenispenilaian]."</td>
                        <td align=center>".$bar->nilai."</td>
                        </tr>";
                }
                $tab.="</table>
            </td>
            </tr>
            </table>";

        echo $tab;
    break;

    case'formalasan':
            $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
            //exit($spoht);
            $qtr=$owlPDO->query($str) or die (print"Gagal : ".PDOException::getMessage());
            $qtr->setFetchMode(PDO::FETCH_ASSOC);
            $rtr=$qtr->fetch();

            echo"
                <div id=test style=display:block>
                <fieldset>
                <table cellspacing=1 border=0>
                <tr>
                    <td>Alasan</td>
                    <td>:</td>
                    <td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3>".$rpoht['keterangan']."</textarea></td>
                </tr>
                <td><td><td>
                <button class=mybutton onclick=ditolakpp() id=ditolak >".$_SESSION['lang']['ditolak']."</button>

                <button class=mybutton onclick=cancelform()>".$_SESSION['lang']['cancel']."</button>
                </td></tr></table> 
                <input type=hidden name=karyawanid id=karyawanid value='".$rtr['karyawanid']."' /> 
                <input type=hidden name=method id=method value='updateht' /> 
                <input type=hidden name=tglevaluasi id=tglevaluasi value=".$rtr['tanggalevaluasi']."  /> 
                </fieldset></div>";
                    
    break;
	
    case'updateht':
    //exit("warning:masuk");
        $strht = "update " . $dbname . ".sdm_evaluasiht set alasanpenolakan='".$alasan."', spersetujuan='2', tglpersetujuan='".date('Y-m-d')."', status='1' where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        //exit("warning: ".$strht);
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal  : " . $e->getMessage() . "\n";
            die();
        }

        $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        $qtr = $owlPDO->query($str) or die (print "Gagal : ".PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_OBJ);
        $rtr=$qtr->fetch();
        $rekomendasi=$rtr->rekomendasi;
        $spersetujuan=$rtr->spersetujuan;
        $alasan=$rtr->alasanpenolakan;

        $kodeorgstr=substr($_SESSION['empl']['lokasitugas'],0,2);
        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorgstr."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        if ($spersetujuan!=0){
            $to = getUserEmail($rtr->ttd);
            $namakaryawan = getNamaKaryawan($karyawan);
            $subject = "[Notifikasi]Aproval Evaluasi Masa Percobaan a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pengajuan evaluasi masa percobaan ".$namakaryawan." dengan rekomendasi '".$optrekomen[$rekomendasi]."' ditolak dengan alasan yaitu ".$alasan."
                           <br>
                           <br>
                           Untuk melihat detail silahkan lakukan di menu SDM->Transaksi->Persetujuan Penilaian Karyawan
                           <br>
                           <br>
                           Regards,<br>
                           Owl-Plantation System.
                         </body>
                        </head>
                     </html>";
            $kirim = kirimEmail($to, '', $subject, $body);
            $to = getUserEmail($rtr->ttd2);
            $kirim = kirimEmail($to, '', $subject, $body);
            $to = getUserEmail($rtr->ttd3);
            $kirim = kirimEmail($to, '', $subject, $body);
        }
    break;

    case'disetujuisp':
        //exit("Warning : ".$tgleva.$karyawan);
        $strht = "update " . $dbname . ".sdm_evaluasiht set spersetujuan='1', tglpersetujuan='".date('Y-m-d')."', status='1' where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal  : " . $e->getMessage() . "\n";
            die();
        }

        $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        $qtr = $owlPDO->query($str) or die (print "Gagal : ".PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_OBJ);
        $rtr=$qtr->fetch();
        $rekomendasi=$rtr->rekomendasi;
        $spersetujuan=$rtr->spersetujuan;
        $alasan=$rtr->alasanpenolakan;

        $tanggal=date('Y-m-d');
        $tgl=explode('-', $tanggal);
        $tahun=$tgl[0];
        $bulan=$tgl[1];
        $tglex=$tgl[2];
            $tahun=$tahun+1;
        $tglpengangkatan=$tahun."-".$bulan."-".$tglex;

        if($rekomendasi==1){
            $str="update " . $dbname . ".datakaryawan set statuskaryawan='Tetap' where karyawanid='".$karyawan."' ";
            try{ 
            $owlPDO->exec($str); 
            }
            catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
            }
        }else if ($rekomendasi==2) {
            $str="update ".$dbname.".datakaryawan set tanggalpengangkatan='".$tglpengangkatan."' where karyawanid='".$karyawan."' ";
            try{ 
            $owlPDO->exec($str); 
            }
            catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
            }
        }else if ($rekomendasi==5) {
            $str="update ".$dbname.".datakaryawan set tanggalkeluar='".date('Y-m-d')."' where karyawanid='".$karyawan."' ";
            try{ 
            $owlPDO->exec($str); 
            }
            catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
            }
        }


        $kodeorgstr=substr($_SESSION['empl']['lokasitugas'],0,2);
        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorgstr."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;

        if ($spersetujuan!=0){
            $to = getUserEmail($rtr->ttd);
            $namakaryawan = getNamaKaryawan($karyawan);
            $subject = "[Notifikasi]Aproval Evaluasi Masa Percobaan a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pengajuan evaluasi masa percobaan ".$namakaryawan." dengan rekomendasi '".$optrekomen[$rekomendasi]."' diterima.
                           <br>
                           <br>
                           Untuk melihat detail silahkan lakukan di menu SDM->Transaksi->Persetujuan Penilaian Karyawan
                           <br>
                           <br>
                           Regards,<br>
                           Owl-Plantation System.
                         </body>
                        </head>
                     </html>";
            $kirim = kirimEmail($to, '', $subject, $body);
            $to = getUserEmail($rtr->ttd2);
            $kirim = kirimEmail($to, '', $subject, $body);
            $to = getUserEmail($rtr->ttd3);
            $kirim = kirimEmail($to, '', $subject, $body);
        }
        
    break;
	
	default:
		# code...
		break;
}

?>
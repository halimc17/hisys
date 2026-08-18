<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$notrans = checkPostGet('notrans', '');
$karid = checkPostGet('karid', '');
$lokasitugas = checkPostGet('lokasitugas', '');
$tipekaryawan = checkPostGet('tipekaryawan', '');
$jenisspx = checkPostGet('jenisspx', '');
$namafile = checkPostGet('namafile', '');

$path	= "fileupload/suratperingatan/";


    switch($method){

        case 'changeDatakaryawan':

            $str = "select * from ".$dbname.".datakaryawan 
            where (tanggalkeluar='0000-00-00' or tanggalkeluar>'".date('Y-m-d')."') and lokasitugas= '".$lokasitugas."' and tipekaryawan = '".$tipekaryawan."'  order by namakaryawan";
            $res = fetchData($str);
            foreach($res as $bar){
                $optkar.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
            }

            echo $optkar;
        break;

        case 'loaddata':

        $limit = 20;
        $page = 0;

        //ambil jumlah baris dalam tahun ini
        $notransaksi = "";
        if (isset($_POST['tex'])) {
            $notransaksi.=$_POST['tex'];
        }
        $whrlokasi=getOrgDetail(2);
        
        $str = "select count(*) as jlhbrs from " . $dbname . ".sdm_suratperingatan a
        left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
        where b.namakaryawan like '%" . $notransaksi . "%' and kodeorg in (" . $whrlokasi . ")
        order by jlhbrs desc";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $jlhbrs = $bar->jlhbrs;
        }

        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }


        $offset = $page * $limit;

        $str = "select a.*,b.tipekaryawan from " . $dbname . ".sdm_suratperingatan a
        left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
        where b.namakaryawan like '%" . $notransaksi . "%' and kodeorg in (" . $whrlokasi . ")
        limit " . $offset . ",20";

        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no = $page * $limit;
        while ($bar = $res->fetch()) {
            $no+=1;

            echo"<tr class=rowcontent>
                    <td align=center>" . $no . "</td>
                    <td>" . $bar->nomor . "</td>
                    <td>" . getNamaKaryawan($bar->karyawanid) . "</td>
                    <td>" . tanggalnormal($bar->tanggal) . "</td>
                    <td>" . tanggalnormal($bar->sampai) . "</td>
                    <td>" . $bar->jenissp . "</td>
                    <td>" . getNamaKaryawan($bar->updateby) . "</td>	
                    <td align=center>";
                if ($bar->posting == 1) {
                    echo "&nbsp;<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . "' onclick=\"pdfSP('" . $bar->nomor . "',event);\">
                    &nbsp;<img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $bar->nomor . "'," . $bar->karyawanid . ",'" . $bar->jenissp . "')\" src='images/upload-2-xxl.png'/>";
                    echo "&nbsp;<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posted' >";
                } else {
                    echo "<img src=images/pdf.jpg class=resicon title='" . $_SESSION['lang']['pdf'] . "' onclick=\"pdfSP('" . $bar->nomor . "',event);\"> 
                        &nbsp;<img src=images/application/application_delete.png class=resicon title='delete' onclick=\"delSP('" . $bar->nomor . "','" . $bar->karyawanid . "');\">
                        &nbsp;<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editSP('" . $bar->nomor . "','" . $bar->karyawanid . "','" . $bar->jenissp . "');\">
                        &nbsp;<img title='" . $_SESSION['lang']['upload'] . "' class=zImgBtn onclick=\"showupload(event,'" . $bar->nomor . "'," . $bar->karyawanid . ",'" . $bar->jenissp . "')\" src='images/upload-2-xxl.png'/>
                        &nbsp;<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30' title='Posting' onclick=\"posting('" . $bar->nomor . "','" . $no . "');\" >";
                }

                echo"</td>
                </tr>";
            }
            echo"<tr><td colspan=8 align=center>
                " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "
                <br>
                <button class=mybutton onclick=cariSP(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                <button class=mybutton onclick=cariSP(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                </td>
                </tr>";
        break;

        case 'pdfSP':
            $str = "select * from " . $dbname . ".sdm_suratperingatan where nomor = '".$notrans."' ";
            $res = fetchData($str);
            foreach($res as $bar){
                $jenissurat = $bar['jenissp'];
                $karyawan   = $bar['karyawanid'];
                $tipekaryawan = getKary($bar['karyawanid'],'tipekaryawan');
                $tanggal    = $bar['tanggal'];
                $tembusan1  = $bar['tembusan1'];
                $tembusan2  = $bar['tembusan2'];
                $tembusan3  = $bar['tembusan3'];
                $tembusan4  = $bar['tembusan4'];
                $kodeorg    = $bar['kodeorg'];
                $penandatangan    = $bar['penandatangan'];
                $jabatan          = $bar['jabatan'];
                $paragraf1        = $bar['paragraf1'];
                $paragraf2        = $bar['paragraf2'];
                $paragraf3        = $bar['paragraf3'];
                $paragraf4        = $bar['paragraf4'];
                $dibuat           = $bar['dibuat'];
                $verifikasi       = $bar['verifikasi'];
                $jabatandibuat    = $bar['jabatandibuat'];
                $jabatanverifikasi    = $bar['jabatanverifikasi'];
            }

            if($jenissurat == "SP1" || $jenissurat == "SP2" || $jenissurat == "SP3"){

            $tab.= "";
            $tab="<style>
                @page {
                    margin-top: 30px;
                    margin-left: 75px;
                    margin-right: 75px;
                    margin-bottom: 30px;
                }
                body {
                    font-family: 'arial';
                    font-size:8;
                }
                
                footer {
                    position: fixed; 
                    bottom: 100px; 
                    left: 0px; 
                    right: 0px;
                    height: 50px; 
                }
                
            </style>";

            $logo = 'images/logodepan.png';
            $cellpadding=3;
            $cellspacing=0;
            $no =0;
            $sizefont='8';

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:18px'><b> SURAT PERINGATAN </b></span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:14px'> No Surat: ".$notrans." </span></td>";
                    $tab.="</tr>";
                $tab.="</table>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td colspan=3><span style='font-size:".$sizefont."'>Surat peringatan ini ditunjukkan kepada :</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td width='50'><span style='font-size:".$sizefont."'> Nama </span></td>";
                        $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                        $tab.="<td><span style='font-size:".$sizefont."'> ".getNamaKaryawan($karyawan)." </span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td width='50'><span style='font-size:".$sizefont."'> Jabatan </span></td>";
                        $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                        $tab.="<td><span style='font-size:".$sizefont."'> ".getJabatanKaryawan($karyawan)." </span></td>";
                    $tab.="</tr>";
                $tab.="</table><br>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                $tab.="<tr>";
                    $tab.="<td><span style='font-size:".$sizefont."'> Telah melakukan pelanggaran terhadap peraturan perusahaan sebagai berikut: </span></td>";
                $tab.="</tr>";
                $tab.="</table>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1>";
                $tab.="<tr>";
                    $tab.="<td align=center><span style='font-size:".$sizefont."'><b>Kronologis Perkara / Kasus :</b></span></td>";
                $tab.="</tr>";
                $tab.="<tr>";
                    $tab.="<td><span style='font-size:".$sizefont."'>".$paragraf1."</span></td>";
                $tab.="</tr>";
                $tab.="</table><br>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1>";
                $tab.="<tr>";
                    $tab.="<td align=center><span style='font-size:".$sizefont."'><b>Saran dari Atasan/Personalia :</b></span></td>";
                $tab.="</tr>";
                $tab.="<tr>";
                    $tab.="<td><span style='font-size:".$sizefont."'>".$paragraf2."</span></td>";
                $tab.="</tr>";
                $tab.="</table>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                $tab.="<tr>";
                    $tab.="<td><span style='font-size:".$sizefont."'>Maka demi tegaknya disiplin kerja di perusahaan ini, kami memberikan sanksi kepada Saudara berupa:</span></td>";
                $tab.="</tr>";
                $tab.="</table><br>";

                ## CEK SP BERAPA
                if($jenissurat == 'SP1'){

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:11'> <b>Peringatan Tertulis Pertama </b>   </td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                }elseif($jenissurat == 'SP2'){

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:11'> <b>Peringatan Tertulis Kedua </b></span></td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                }elseif($jenissurat == 'SP3'){

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:11'>“ <b>Peringatan Tertulis Ketiga </b> </span></td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                }

                $tab.="<br><table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;'><span style='font-size:".$sizefont.";'>".$paragraf3."</span></td>";
                    $tab.="</tr>";
                $tab.="</table>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:50px;'>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Disetujui</span></td>";
                        $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Diketahui Oleh</span></td>";
                        $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Dibuat Oleh</span></td>";
                    $tab.="</tr>";

                    $tab.="<tr>";
                        $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$penandatangan."<br><hr>".$jabatan."</span></td>";
                        $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$verifikasi."<br><hr>".$jabatanverifikasi."</span></td>";
                        $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$dibuat."<br><hr>".$jabatandibuat."</span></td>";
                    $tab.="</tr>";
                $tab.="</table>";


                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:50px;'>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;'><span style='font-size:".$sizefont.";'>Tembusan :</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan1."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan2."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan3."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan4."</span></td>";
                    $tab.="</tr>";
                $tab.="</table>";

            }elseif($jenissurat == "ST"){

                $tab.= "";
                $tab="<style>
                    @page {
                        margin-top: 30px;
                        margin-left: 75px;
                        margin-right: 75px;
                        margin-bottom: 30px;
                    }
                    body {
                        font-family: 'arial';
                        font-size:8;
                    }
                    
                    footer {
                        position: fixed; 
                        bottom: 100px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px; 
                    }
                    
                </style>";

                $logo = 'images/logo.png';
                if (substr($kodeorg,  0, 3) == 'PPP') {
                    $kodeorg = substr($kodeorg, 4);
                    $logo = 'images/logodepan.png';
                }
                
                $cellpadding=3;
                $cellspacing=0;
                $no =0;
                $sizefont='10';
    
                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1>";
                        $tab.="<tr>";
                            $tab.="<td width='70px' align=center><img style='width: 60px; height: 40px;' class=delliconBig src=".$logo."></td>";
                            $tab.="<td align=center><span style='font-size:18px'><b> SURAT TEGURAN </b></span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> No.Surat Teguran </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".$notrans." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td colspan =3 ><span style='font-size:".$sizefont."'> Surat Teguran ini ditujukan kepada : </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Nama  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getNamaKaryawan($karyawan)." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> NIK  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getNik($karyawan)." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Jabatan  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getJabatanKaryawan($karyawan)." </span></td>";
                        $tab.="</tr>";

                        if(getSubbagian($karyawan) == ""){
                            $text = "Umum/Kantor";
                        }else{
                            $text = getSubbagian($karyawan);
                        }

                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Divisi.  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".$text." </span></td>";
                        $tab.="</tr>";

                        $tab.="<tr>";
                            $tab.="<td colspan =3 ><span style='font-size:".$sizefont."'> Telah melakukan pelanggaran terhadap peraturan perusahaan pada : </span></td>";
                        $tab.="</tr>";

                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Hari </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".tglnmblnsec($tanggal,'I','long')." </span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td align=center><span style='font-size:".$sizefont."'><b>Kronologis Perkara / Kasus :</b></span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td><span style='font-size:".$sizefont."'>".$paragraf1."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td align=center><span style='font-size:".$sizefont."'><b>Saran dari Atasan/Personalia :</b></span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td><span style='font-size:".$sizefont."'>".$paragraf2."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td><span style='font-size:".$sizefont."'>Maka demi tegaknya disiplin kerja di perusahaan ini, kami memberikan sanksi kepada Saudara berupa:</span></td>";
                    $tab.="</tr>";
                    $tab.="</table><br>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td align=center><span style='font-size:11'>“<b>Teguran </b> Pertama/ Kedua/ Ketiga (Terakhir)” (*)</span></td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;'><span style='font-size:".$sizefont.";'>".$paragraf3."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";


                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Disetujui</span></td>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Diketahui Oleh</span></td>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Dibuat Oleh</span></td>";
                        $tab.="</tr>";

                        $tab.="<tr>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$penandatangan."<br><hr>".$jabatan."</span></td>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$verifikasi."<br><hr>".$jabatanverifikasi."</span></td>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$dibuat."<br><hr>".$jabatandibuat."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;'><span style='font-size:".$sizefont.";'>Tembusan :</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan1."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan2."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan3."</span></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan4."</span></td>";
                    $tab.="</tr>";
                $tab.="</table>";
            }elseif($jenissurat == "PHK"){

                $tab.= "";
                $tab="<style>
                    @page {
                        margin-top: 30px;
                        margin-left: 75px;
                        margin-right: 75px;
                        margin-bottom: 30px;
                    }
                    body {
                        font-family: 'arial';
                        font-size:8;
                    }
                    
                    footer {
                        position: fixed; 
                        bottom: 100px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px; 
                    }
                    
                </style>";

                $logo = 'images/HISYS.png';
                $cellpadding=3;
                $cellspacing=0;
                $no =0;
                $sizefont='10';
    
                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1>";
                        $tab.="<tr>";
                            $tab.="<td width='70px' align=center><img style='width: 50px; height: auto;' class=delliconBig src=".$logo."></td>";
                            $tab.="<td align=center><span style='font-size:18px'><b> <u>FORM PEMUTUSAN HUBUNGAN KERJA</u> </b></span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";


                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> No.Surat </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".$notrans." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Nama  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getNamaKaryawan($karyawan)." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> NIK  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getNik($karyawan)." </span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Jabatan  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".getJabatanKaryawan($karyawan)." </span></td>";
                        $tab.="</tr>";

                        if(getSubbagian($karyawan) == ""){
                            $text = "Umum/Kantor";
                        }else{
                            $text = getSubbagian($karyawan);
                        }

                        $tab.="<tr>";
                            $tab.="<td width='100px'><span style='font-size:".$sizefont."'> Divisi.  </span></td>";
                            $tab.="<td align=center width='10'><span style='font-size:".$sizefont."'> : </span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'> ".$text." </span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=1 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td align=center><span style='font-size:".$sizefont."'></span></td>";
                            $tab.="<td align=center><span style='font-size:".$sizefont."'><b>Jenis Berakhirnya Hubungan Kerja</b></span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td><span style='font-size:".$sizefont."'></span></td>";
                            $tab.="<td><span style='font-size:".$sizefont."'>".$paragraf4."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
                    $tab.="<tr>";
                        $tab.="<td><span style='font-size:8'>*) Lingkari sesuai  pilihan</span></td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:10px;'>";
                    $tab.="<tr>";
                        $tab.="<td><span style='font-size:".$sizefont."'>Terhitung efektif sejak tanggal : ".tglnmblnsec($tanggal,'I','long')."</span></td>";
                    $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Disetujui</span></td>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Diketahui Oleh</span></td>";
                            $tab.="<td style='text-align: center;'><span style='font-size:".$sizefont.";'>Dibuat Oleh</span></td>";
                        $tab.="</tr>";

                        $tab.="<tr>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$penandatangan."<br><hr>".$jabatan."</span></td>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$verifikasi."<br><hr>".$jabatanverifikasi."</span></td>";
                            $tab.="<td style='text-align: center; padding-top: 80px;'><span style='font-size:".$sizefont.";'>".$dibuat."<br><hr>".$jabatandibuat."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";

                    $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='margin-top:30px;'>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;'><span style='font-size:".$sizefont.";'>Tembusan :</span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan1."</span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan2."</span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan3."</span></td>";
                        $tab.="</tr>";
                        $tab.="<tr>";
                            $tab.="<td style='text-align: justify;padding-left: 20px;'><span style='font-size:".$sizefont.";'>- ".$tembusan4."</span></td>";
                        $tab.="</tr>";
                    $tab.="</table>";
            }

            $dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();
            $dompdf->stream("SURAT PERINGATAN", array("Attachment" => false));

        break;

        case 'editData':

            $str = "select * from " . $dbname . ".sdm_suratperingatan where nomor = '".$notrans."' and karyawanid = '".$karid."' and jenissp = '".$jenisspx."' ";
            $res = fetchData($str);
            foreach($res as $bar){
                $jenissurat = $bar['jenissp'];
                $karyawan   = $bar['karyawanid'];
                $tipekaryawan = getKary($bar['karyawanid'],'tipekaryawan');
                $tanggal    = $bar['tanggal'];
                $tembusan1  = $bar['tembusan1'];
                $tembusan2  = $bar['tembusan2'];
                $tembusan3  = $bar['tembusan3'];
                $tembusan4  = $bar['tembusan4'];
                $kodeorg    = $bar['kodeorg'];
                $penandatangan    = $bar['penandatangan'];
                $jabatan          = $bar['jabatan'];
                $paragraf1        = $bar['paragraf1'];
                $paragraf2        = $bar['paragraf2'];
                $paragraf3        = $bar['paragraf3'];
                $paragraf4        = $bar['paragraf4'];
                $dibuat           = $bar['dibuat'];
                $verifikasi       = $bar['verifikasi'];
                $jabatandibuat    = $bar['jabatandibuat'];
                $jabatanverifikasi    = $bar['jabatanverifikasi'];
            }

            echo $jenissurat."##".$kodeorg."##".$tipekaryawan."##".$karyawan."##".tanggalnormal($tanggal)."##".$paragraf1 ."##".$paragraf2 ."##".$paragraf3 ."##".$paragraf4 ."##".$penandatangan ."##".$jabatan ."##".$verifikasi ."##".$jabatanverifikasi ."##".$dibuat ."##".$jabatandibuat ."##".$tembusan1."##".$tembusan2 ."##".$tembusan3 ."##".$tembusan4;

        break;

        case 'showupload':
            
			$tab="";
			$tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
			$tab.="<tr>
					<td>".$_SESSION['lang']['notransaksi']."</td>
					<td>:</td>
					<td>
						<label id='notrans' style='font-weight:bold'>".$notrans."</label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td>
						<label id='karid' hidden style='font-weight:bold'>".$karid."</label>
						<label style='font-weight:bold'>".getNamaKaryawan($karid)."</label>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jenis']."SP</td>
					<td>:</td>
					<td>
						<label id='jenisspx' style='font-weight:bold'>".$jenisspx."</label>
					</td>
				</tr>";
			$tab.="<tr><td colspan=4><hr></td></tr>
					<tr>
						<td>Filename</td>
						<td>:</td>
						<td>
							<input type='file' name='upload' id='upload' >
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick=\"submitfile()\">Submit</button>
						</td>
					</tr>
				</table>
				<p />";
				
			$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' width=100%>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='listfiles'>
					</tbody>
				</table>
			</fieldset> ";
				
			echo $tab;
		break;

        case 'loadfiles':
			$no = 0;
			$tab = "";	
			$str="select * from ".$dbname.".listfile_sdm_suratperingatan where notransaksi = '".$notrans."' and status='1' and karyawanid='".$karid."' and jenissp='".$jenisspx."'";
			$res=fetchData($str);
			if(empty($res)){
				$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
				foreach($res as $key=>$val){
					$no++;
					$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}elseif($val['formaticon']=='.png'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}elseif($val['formaticon']=='.pdf'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}else{
						$tab.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
					
					$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$path.str_replace('/','',$val['namafile'])."')\">".$val['namafile']."</td>
						<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					
					$tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['karyawanid']."','".$val['jenissp']."','".$val['namafile']."');\" >";
					
					$tab."	</td>
					</tr>";
				}	
			}
			
			echo $tab;
		break;
        case 'deletefile':
			$str="delete from ".$dbname.".listfile_sdm_suratperingatan where notransaksi = '".$notrans."' and status='1' and karyawanid='".$karid."' and jenissp='".$jenisspx."' and namafile='".$namafile."'";
			try{
				$owlPDO->exec($str);
				$pathx = $path.$namafile;
				unlink($pathx);
			}
			catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		break;

        case'submitfile':
            $tgl = date("YmdHis");
            $his = date("His");
            $data = $_POST;
            
            if($data['fileupload']!=''){
                if($_FILES['file']['error']==0){	
                    $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                    $filename = $pt."_".$his."".$filetype;
                    $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
                    
                    if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                        if($_FILES['file']['size'] <= 2500000){
                            $str = "insert into ".$dbname.".listfile_sdm_suratperingatan (notransaksi,karyawanid,jenissp,namafile,formaticon,status,createdby,createdtime) values ('".$notrans."','".$karid."','".$jenisspx."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                            try{
                                $owlPDO->exec($str);
                                if (!file_exists($path)) {
                                mkdir($path, 0777, true);
                            }
                                file_put_contents($path.$filename,$file_tmpname);
                            }
                            catch(PDOException $e){
                                echo " Gagal," . addslashes($e->getMessage());
                            }
                        }else{
                            exit("warning : Ukuran file upload maksimal 250kb");
                        }
                    }else{
                        exit("Warning : Format file upload salah");
                    }
                }
            }
        break;

        case'viewfile':
            $tab="";
            $res[0]['formaticon'] = strtolower('.'.substr($namafile,strripos($namafile,'.')+1));
            
            if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
                exit("Warning: Tidak bisa ditampilkan, silahkan download.");
            }
            
            if($res[0]['formaticon']=='.pdf'){
                $tab.="<embed src='".$namafile."' style='width:100%;height:97%;' type='application/pdf'>";
            }else{			
                $tab.="<img src='".$namafile."'>";
            }
            
            echo $tab;
        break;	
    }
?>
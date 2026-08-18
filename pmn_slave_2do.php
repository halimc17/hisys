<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses','');
$nokontrak = checkPostGet('notrans','');
$kdBrg2=checkPostGet('kdBrg2','');
$thn = checkPostGet('thn','');
$pt2 = checkPostGet('pt2','');
$ceklist = checkPostGet('ceklist','');

switch($proses)
{
    case'preview2':
        if($thn==''){
			exit("Warning: Periode harus dipilih.");
		}
        if($pt2!=''){
            $whr = "and kodept = '".$pt2."'";
        }else{
            $whr = "and kodept in ('".implode("','",array_keys(getOrgDetail(3)))."')";
        }
        echo"<div class='table-scroll'>
        <table class=sortable cellspacing=1 border=0><thead><tr class=rowheader>
        <th>".$_SESSION['lang']['nourut']."</th>
        <th>".$_SESSION['lang']['kodept']."</th>
        <th>".$_SESSION['lang']['NoKontrak']."</th>
        <th>".$_SESSION['lang']['komoditi']."</th>
        <th>".$_SESSION['lang']['tglKontrak']."</th>
        <th>".$_SESSION['lang']['Pembeli']."</th>
        <th>".$_SESSION['lang']['estimasiPengiriman']."</th>
        <th>Kuantitas (Kg)</th>
        <th>".$_SESSION['lang']['matauang']."</th>
        <th>".$_SESSION['lang']['tanggal']." DO</th>
        <th>No DO</th>
        <th>Toleransi (%)</th>
        <th>PDF</th>
        </tr></thead><tbody>
        ";
        $sql="select nokontrakinternal as nokontrak,kodebarang,tanggalkontrak,koderekanan,tanggalkirim,sdtanggal,kuantitaskontrak,kodept,matauang 
                from ".$dbname.".pmn_kontrakjual_vw where kodebarang like '%".$kdBrg2."%' ".$whr." and tanggalkontrak like '".$thn."%' order by nokontrakinternal desc";
        
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $row=owlBaris($query);
            
        if($row<=0){
		    echo"<tr><td colspan=11 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
            $query->setFetchMode(PDO::FETCH_ASSOC);
            while($res=$query->fetch())
            {
				$sBrg="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
                $qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
                $qBrg->setFetchMode(PDO::FETCH_ASSOC);
                $rBrg=$qBrg->fetch();

				$sCust="select namacustomer  from ".$dbname.".pmn_4customer where kodecustomer='".$res['koderekanan']."'";
                $qCust=$owlPDO->query($sCust) or die(print " Gagal: ".PDOException::getMessage());
                $qCust->setFetchMode(PDO::FETCH_ASSOC);
                $rCust=$qCust->fetch();
				
				$tanggalbl=$temptanggalbl='';
				$strbl="select tanggalbl,nokontrak,sum(jumlah) as jumlah from ".$dbname.".pmn_bast where nokontrak='".$res['nokontrak']."' group by tanggalbl";
				$resbl=fetchdata($strbl);
				foreach($resbl as $barbl){
					if($temptanggalbl!=$barbl['tanggalbl']){
						// $tanggalbl.=tanggalnormal($barbl['tanggalbl']).' ('.hidezerodecimal($barbl['jumlah'],2).')<br>';
						$tanggalbl.=tanggalnormal($barbl['tanggalbl']);
					}
					$temptanggalbl=$barbl['tanggalbl'];
				}
				$sDo="select tanggaldo,nodo,toleransi from ".$dbname.".pmn_suratperintahpengiriman where nokontrak='".$res['nokontrak']."' and posting='1'";
                $qDo=$owlPDO->query($sDo) or die(print " Gagal: ".PDOException::getMessage());
                $qDo->setFetchMode(PDO::FETCH_ASSOC);
                $rDo=$qDo->fetch();

				$sPbrk="select sum(beratbersih) as jumlahterangkut  from ".$dbname.".pabrik_timbangan where nokontrak='".$res['nokontrak']."'  and wbcond != 'Return'";					
				$qPbrk=$owlPDO->query($sPbrk) or die(print " Gagal: ".PDOException::getMessage());
				$qPbrk->setFetchMode(PDO::FETCH_ASSOC);
				$hPbrk=$qPbrk->fetch();

				$sTmb="select nokontrak  from ".$dbname.".pabrik_timbangan where nokontrak='".$res['nokontrak']."'";$rTmb=fetchdata($sTmb);
				$arr="nokontrak"."##".$res['nokontrak'];

                @$nn++;
                echo"<tr class=rowcontent \">
                <td align=center>".$nn."</td>
                <td style='width:7%'>".getNamaOrg($res['kodept'])."</td>
                <td style='width:13%'>".$res['nokontrak']."</td>
                <td style='width:10%'>".$rBrg['namabarang']."</td>
                <td align=center>".tanggalnormal($res['tanggalkontrak'])."</td>
                <td>".$rCust['namacustomer']."</td>
                <td align=center>".tanggalnormal($res['tanggalkirim'])." s.d. ".tanggalnormal($res['sdtanggal'])."</td>
                <td align=right>".hidezerodecimal($res['kuantitaskontrak'],2)."</td>
                <td align=center style=\"cursor:pointer;\">".$res['matauang']."</td>
                <td align=center>".($rDo['tanggaldo'] != '' ? tanggalnormal($rDo['tanggaldo']) : '')."</td>
                <td >".$rDo['nodo']."</td>
                <td align=center>".($rDo['toleransi'] != '' ? $rDo['toleransi']." % (".number_format($rDo['toleransi'] * $res['kuantitaskontrak']/100)." Kg)" : '')."</td>
                <td align=left>";
                if($rDo['nodo']!= ''){
                    echo "<img src=images/pdf.jpg class=zImgBtn  title='PDF DO " . $rstr['nodo'] . "' onclick=\"pdf('" . $rDo['nodo'] . "');\" >";
                    if(count($rTmb) >0){
                        echo "&nbsp;&nbsp;<img src=images/pdf.jpg class=zImgBtn title='PDF BAP' onclick=\"pdf1('" . $res['nokontrak'] . "');\">";
                        echo "&nbsp;&nbsp;<img src=images/pdf.jpg class=zImgBtn title='Print Rekap Loading' onclick=\"pdf2('".$res['nokontrak']."','".$nn."');\">";
                        if(($hPbrk['jumlahterangkut'] >= $res['kuantitaskontrak']) || count($resbl) > 0 ){
                            echo "<input type=checkbox id=ceklist" . $nn . " checked=true disabled title='Kontrak Selesai'>";
                        }else{
                            echo "<input type=checkbox id=ceklist" . $nn . " title='Kontrak Belum Selesai'>";
                        }
                    }
                }
                echo "</td>
                </tr>";
                @$tkuantitaskontrak+=$res['kuantitaskontrak'];
			}
			#= bentuk total
			echo"<tr class=rowcontent \">
			<td colspan=7 align=center><b>".$_SESSION['lang']['total']."</b></td>
			
			<td align=right><b>".hidezerodecimal($tkuantitaskontrak,2)."</b></td>
			<td colspan=5></td>
			</tr>";
		}
        echo"</tbody></table></div>";
    break;
    
	case 'pdf1':
		$tab = "<style>
			@page {
				margin-top: 30px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";

		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$hargasatuan = $bar['hargasatuan'];
			$ffakontrak = $bar['ffa'];
			$dobikontrak = $bar['dobi'];
			$mdanikontrak = $bar['mdani'];
			$moistkontrak = $bar['moist'];
			$dirtkontrak = $bar['dirt'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$satuanbarang = $bar['satuan'];
			$matauang = $dtsimbol[$bar['matauang']];
			$persenppn = $bar['persenppn'];
			$kuantitaskontrak = $bar['kuantitaskontrak'];
			$kodecustomer = $bar['koderekanan'];
			$kodept = $bar['kodept'];
			$kodebarang = $bar['kodebarang'];
		}

		$str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nodo = $bar['nodo'];
			$transportir = $bar['transportir'];
            $tanggaldo = $bar['tanggaldo'];
		}

		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}

		$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$namacustomer = $bar['namacustomer'];
			$jabatancustomer = $bar['jabatan'];
		}

        $str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400' ";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $arrinisial[$bar['kodebarang']] = $bar['inisial'];
        }

        $query = selectQuery($dbname,'pabrik_timbangan','tanggal',"nokontrak='".$nokontrak."' ");
        $hasil = fetchData($query);
        foreach ($hasil as $v) {
            $tanggalakhirnimbang=$v['tanggal'];
        }

		$qry = "SELECT DISTINCT `nokontrak`,SUBSTRING(tanggal,1,10) as tanggalkirim FROM `pabrik_timbangan` WHERE nokontrak='".$nokontrak."' AND `nokontrak` != '' and kodebarang='".$kodebarang."' and millcode='CARM' ORDER BY `tanggal`,nokontrak";
        $hasil = fetchData($qry);
        foreach ($hasil as $kiy) {
            $tglkirim[$kiy['nokontrak']][$kiy['tanggalkirim']]=$kiy['tanggalkirim'];
        }

		$qry = "SELECT DISTINCT `nokontrak` FROM `pabrik_timbangan` WHERE `tanggal` LIKE '".substr($tanggalakhirnimbang,0,7)."%' AND `nokontrak` != '' and kodebarang='".$kodebarang."' and millcode='CARM' ORDER BY `tanggal`,nokontrak";
        $hasil = fetchData($qry);
        foreach ($hasil as $kiy) {
            @$nox++;
            $urutanke[$kiy['nokontrak']]=$nox;
        }

        // ambil semua key (tanggal)
        $rangetanggal = array_keys($tglkirim[$nokontrak]);

        // urutkan key-nya
        sort($rangetanggal);

        // ambil pertama dan terakhir
        $tglPertama = reset($rangetanggal); // atau $rangetanggal[0]
        $tglTerakhir = end($rangetanggal);
		$qry2 = "select substring(tanggal,1,10) as tanggal,supir,nokendaraan,notransaksi,jammasuk,jamkeluar,beratmasuk,beratkeluar,beratbersih,nosegel,bps,moist,dirt,kodebarang,kodecustomer,wbcond from " . $dbname . ".pabrik_timbangan where nokontrak='" . $nokontrak . "' and wbcond != 'Return' order by tanggal,jammasuk";
        $hasil2=fetchData($qry2);
        foreach ($hasil2 as $v) {
            $tanggal=$v['tanggal'];
            $jumlah+=$v['beratbersih'];
            @$jumlahrit++;
        }
    
        $s1 = "select namakaryawan,kodejabatan from " . $dbname . ".datakaryawan where kodejabatan='282' ";
        $r1 = fetchdata($s1);
        $nobap = addZero($urutanke[$nokontrak], 3)."/PKS-CA/BAP - ".$arrinisial[$kodebarang]."/".romawi(intval(substr($tanggalakhirnimbang,5,2)))."/".substr($tanggalakhirnimbang,0,4);
		$arrkodept = setheadreport('', $kodept);
		$path = "images/logo/" . $kodept . ".jpg";
		$cellpadding = 1;
		$cellspacing = 1;
		$sizefont = '14px';
		// print_r($arrkodept);exit();

		$tab .= "<div style='page-break-after: always;'>";
		$tab  = "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
		$tab .= "<tr>";
		// Logo
		$tab .= "<td style='width:80px;padding-left:-13px' align='center'>
						<img src=" . $path . " style='width:" . $arrkodept['logowidth'] . ";height:" . $arrkodept['logoheight'] . "'>
					</td>";
		// Nama perusahaan dan alamat
		$tab .= "<td style='text-align:left;width:640px'>
						<div style='font-weight:bold; color:green; font-size:" . ($sizefont + 5.3) . "px;'>
							PERUSAHAAN PERKEBUNAN & PABRIK MINYAK KELAPA SAWIT
						</div>
						<div style='font-weight:bold; color:green; font-size:" . ($sizefont + 4) . "px;'>
							PKS PT. CANDI ARTHA
						</div>
						<div style='font-size:" . ($sizefont - 1) . "px;'>
							Desa Taju Pecah Kec. Batu Ampar Kab. Tanah Laut, Kotak Pos 106 Pelaihari 
							e-mail : <span style='color:blue;'>candiarthaplh@gmail.com</span><br>
							<b>Kalimantan Selatan </b><br>
							Kantor Pusat : Jl. Rungkut YKP Blok PS IH 14 No. 6 Telp. (031) 8713 546 Kode Pos 60297
						</div>
					</td>";
		// Spacer
		$tab .= "<td style='width:50px;'>&nbsp;</td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		// garis tipis + garis ganda
		$tab .= "<div style='border-bottom:1px solid #000; margin-top:3px;'></div>";
		$tab .= "<div style='border-bottom:3px double #000; margin-bottom:10px;'></div>";

		$tab .= "<br>";

		$tab .= "<div style='text-align:center; font-weight:bold; font-size:" . ($sizefont+6) . "px;'><u> BERITA ACARA PENGIRIMAN " . $arrinisial[$kodebarang] . " </u> </div>";

		$tab .= "<br>";
		$tab .= "<div style='text-align:center; font-weight:bold; font-size:" . ($sizefont+2.5) . "px; margin-bottom:15px;'>
					NO. " . $nobap . "
				</div>";

		$lebihkg=$jumlah-$kuantitaskontrak;
        if($lebihkg < 0){
            $ketplusmin = "Kekurangan Muatan ";
            $lebihkg = $lebihkg * -1;
        }else{
            $ketplusmin = "Kelebihan Muatan ";
        }
		$tab .= "<div style='text-align:justify; font-size:" . $sizefont . "px;'>
					Pada hari ini, " . tglnmblnhr($tanggal, 'I', 'long') . " (" . tanggalnormal($tanggal) . ") 
					telah selesai diangkut " . $arrinisial[$kodebarang] . " milik " . getNamaOrg($kodept) . " pada tanggal ".tglnmbln($tglPertama,'I','long')." s.d ".tglnmbln($tglTerakhir,'I','long')."
					dengan rincian sebagai berikut :
				</div>";
		$tab .= "<br><table width=100% style='font-size:" . $sizefont . "px;' cellpadding=2 cellspacing=0 border=0>
            <tr><td style='padding-left:50px' width=200>1. Nama Barang / Commodity</td><td>: " . $arrinisial[$kodebarang] . "</td></tr>
            <tr><td style='padding-left:50px'>2. No Kontrak</td><td>: {$nokontrak}</td></tr>
            <tr><td style='padding-left:50px'>3. No. DO</td><td>: {$nodo}</td></tr>
            <tr><td style='padding-left:50px'>4. Nilai Kontrak</td><td>: <b>".number_format($kuantitaskontrak, 0, ',', '.')." Kg</b></td></tr>
            <tr><td style='padding-left:65px'><b>Total Diangkut</b></td><td><b>: ".number_format($jumlah, 0, ',', '.')." Kg</b></td></tr>
            <tr><td style='padding-left:50px'>5. ".$ketplusmin."</td><td>: ".number_format($lebihkg, 0, ',', '.')." Kg</td></tr>
            <tr><td style='padding-left:50px'>6. Jumlah Unit Pengangkut</td><td>: <b>".$jumlahrit." Rit</b></td></tr>
            <tr><td style='padding-left:50px'>7. Pemilik Barang</td><td>: " . getNamaOrg($kodept) . "</td></tr>
            <tr><td style='padding-left:50px'>8. Pembeli</td><td>: ".getNamaCustomer($kodecustomer)."</td></tr>
            <tr><td style='padding-left:50px'>9. Transportir</td><td>: ".getNamaSupplier($transportir)."</td></tr>
            <tr><td style='padding-left:50px'>10. Tujuan</td><td>: ".getNamaCustomer($kodecustomer)."</td></tr>
        </table>";

		$tab .= "<div style='font-size:" . $sizefont . "px; font-style:italic;padding-left:50px'>
					Catatan : Kontrak dianggap telah selesai.
				</div>";

		$tab .= "<br><div style='font-size:" . $sizefont . "px; text-align:justify;'>
					Demikian Berita Acara Serah terima ini dibuat, agar dipergunakan sebagaimana mestinya.
				</div>";

		// Bagian tanda tangan
		$tab .= "<br><br><table width=100% style='font-size:" . $sizefont . "px;' border=0>
            <tr>
                <td style='width:60%;'>&nbsp;</td>
                <td style='text-align:left;'>
                    Dibuat di : Tajau Pecah, PKS PT CA<br>
                    Tanggal &nbsp; : " . tglnmbln($tanggal, 'I', 'long') . " <br><br><br><br>
                    <div style='text-align:center;'>
                        Disetujui
                        <br><br><br><br>
                        <b>".$r1[0]['namakaryawan']."</b>
                        <br>
                        ".getNamaJabatan($r1[0]['kodejabatan'])."
                    </div>
                </td>
            </tr>
        </table>";

		$tab .= "</div>";

		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
        $dompdf->stream("BAP ".addZero($urutanke[$nokontrak], 3)." CA-".getinisialcustomer($kodecustomer)." DO ".substr($nodo,0,3), array("Attachment" => 0));
    break;
    case 'pdf2':
		$tab = "
			<style>
			@page {
				margin-top: -10px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			@page {
				font-family: Arial, sans-serif, Tahoma;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>
		<title>Rekap Pengiriman</title>";

		#= ambil data dari kontrakjual
		$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kodept = $bar['kodept'];
			$impuritieskontrak = $bar['grading'];
			$penandatangan = $bar['penandatangan'];
			$kuantitaskontrak = $bar['kuantitaskontrak'];
		}

		$str = "select * from " . $dbname . ".pmn_suratperintahpengiriman where nokontrak='" . $nokontrak . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nodo = $bar['nodo'];
			$transportir = $bar['transportir'];
			$prdkirim = $bar['waktupenyerahan'];
		}

		#= jabatan ttd
		$str = "select * from " . $dbname . ".pmn_5ttd where nama='" . $penandatangan . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$jabatanpenandatangan = $bar['jabatan'];
		}

		#= Data Timbangan Reject tidak masuk tonase
		$str = "select notransaksi,beratbersih,wbcond,tiketref from " . $dbname . ".pabrik_timbangan where nokontrak='" . $nokontrak . "' and tiketref NOT LIKE '%/SJ/%' and tiketref != ''  order by tanggal,jammasuk";
		$res = fetchdata($str);$tiketreject=array();
		foreach ($res as $bar) {
            $tiketreject[$bar['tiketref']] = $bar['tiketref'];
		}

		#= Data Timbangan
		$str = "select substring(tanggal,1,10) as tanggal,supir,nokendaraan,notransaksi,jammasuk,jamkeluar,beratmasuk,beratkeluar,beratbersih,nosegel,bps,moist,dirt,kodebarang,kodecustomer,wbcond from " . $dbname . ".pabrik_timbangan where nokontrak='" . $nokontrak . "' and wbcond != 'Return' order by tanggal,jammasuk";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$master[$bar['tanggal']][$bar['notransaksi']] = $bar['notransaksi'];
			$supir[$bar['notransaksi']] = $bar['supir'];
			$nokendaraan[$bar['notransaksi']] = $bar['nokendaraan'];
			$jammasuk[$bar['notransaksi']] = substr($bar['jammasuk'],0,5);
			$jamkeluar[$bar['notransaksi']] = substr($bar['jamkeluar'],0,5);
			$beratkeluar[$bar['notransaksi']] = $bar['beratkeluar'];
			$beratmasuk[$bar['notransaksi']] = $bar['beratmasuk'];
            if (in_array($bar['notransaksi'], $tiketreject)) {
                $beratbersih[$bar['notransaksi']] = 0;
            }else{
                $beratbersih[$bar['notransaksi']] = $bar['beratbersih'];
                $ttltimbang+=$bar['beratbersih'];
            }
			$nosegel[$bar['notransaksi']] = $bar['nosegel'];
			$ffa[$bar['notransaksi']] = $bar['bps'];
			$moist[$bar['notransaksi']] = $bar['moist'];
			$dirt[$bar['notransaksi']] = $bar['dirt'];
            $kodecustomer=$bar['kodecustomer'];
            $kodebarang=$bar['kodebarang'];
            $tglakhirtimbang=$bar['tanggal'];
		}

        $str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400' ";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $arrinisial[$bar['kodebarang']] = $bar['inisial'];
        }

		$arrkodept = setheadreport('', $kodept);
		$path = "images/logo/" . $kodept . ".jpg";
		$cellpadding = 1;
		$cellspacing = 0;
		$sizefont = '12.5px';

		$tab .= "<div style='page-break-after: always;50px'>";
			$tab  = "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='padding-top:-30px'>";
				$tab .= "<tr>";
					$tab .= "<td style='width:62%' valign='top'>";
						$tab  .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
							$tab .= "<tr>";
								// Logo
								$tab .= "<td align='center' valign='top' style='padding-left:-65px;width:17%'>
												<img src=" . $path . " style='width:" . ($arrkodept['logowidth']) . ";height:" . $arrkodept['logoheight'] . "; transform: scale(1.3);'>
										</td>";
								// Nama perusahaan dan alamat
								$tab .= "<td style='text-align:left;width:45%' valign='top'>
											<div style=' font-size:" . ($sizefont + 1) . "px;padding-bottom:5px'>PERUSAHAAN PERKEBUNAN DAN PABRIK KELAPA SAWIT</div>
											<div style=' font-size:" . ($sizefont + 2) . "px;padding-bottom:5px'><b>PKS ".getNamaOrg($kodept)."</b></div>
											<div style=' font-size:" . ($sizefont) . "px;padding-bottom:5px'>Kebun : Desa Taju Pecah Kec. Batu Ampar Kab. Tanah Laut</div>
										</td>";
							$tab .= "</tr>";
						$tab .= "</table>";
					$tab .= "</td>";
					$tab .= "<td style='width:38%;font-size:11.2px'>";
						$tab .= "<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0>";
							$tab .= "<tr>
										<td><b>Pembeli</b></td>
										<td align=center>:</td>
										<td><b>".getNamaCustomer($kodecustomer)."</b></td>";
							$tab .= "</tr>";
							$tab .= "<tr>
										<td><b>Penjual</b></td>
										<td align=center>:</td>
										<td><b>PKS PT. CANDI ARTHA</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>No. Kontrak</b></td>
										<td align=center>:</td>
										<td><b>".$nokontrak."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Pengangkut</b></td>
										<td align=center>:</td>
										<td><b>".getNamaSupplier($transportir)."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>No. DO</b></td>
										<td align=center>:</td>
										<td><b>".$nodo."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Periode Pengangkutan</b></td>
										<td align=center>:</td>
										<td><b>".$prdkirim."</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Nilai Kontrak</b></td>
										<td align=center>:</td>
										<td style='padding-left:76px'><b>".number_format($kuantitaskontrak)."&nbsp;Kg</b></td>
									</tr>";
							$tab .= "<tr>
										<td><b>Total Pengiriman SD Hi</b></td>
										<td align=center>=</td>
										<td style='padding-left:76px'><b>".number_format($ttltimbang)."&nbsp;Kg</b></td>
									</tr>";
						$tab .= "</table>";
					$tab .= "</td>";
				$tab .= "</tr>";
			$tab .= "</table>";
            if($arrinisial[$kodebarang] == 'CPO' || $arrinisial[$kodebarang] == 'PK'){
                $inisial = $arrinisial[$kodebarang];
            }else{
                $inisial = "(<label style='font-size:13px;font-weight:bold'><i>".getNamaBrg($kodebarang)."</i></label>)";
            }
			// garis tipis + garis ganda
			$tab .= "<h4 align=center style='padding-bottom:-20px;padding-top:-20px'>REKAP PENGIRIMAN ".$inisial."</h4>";
			$ttlsdhi=0;$ttlpertgl=array();
			foreach ($master as $tgl => $arrtkt) {
				//Loop Per Tanggal
				$tab .="
				<table width=100% cellpadding=" . $cellpadding . " cellspacing=" . $cellspacing . " border=0 style='font-size:9.2px;padding-bottom:6px'>
					<thead>
						<tr>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Tanggal</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Nama Supir</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No Polisi</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No Tiket</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=2 align=center>Jam</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>No segel</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=3 align=center>Mutu ".$arrinisial[$kodebarang]."</td>
							<td style='text-transform: uppercase;border:1px solid black;' colspan=3 align=center>Timbangan</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>s/d HI</td>
							<td style='text-transform: uppercase;border:1px solid black;' rowspan=2 align=center>Keterangan</td>
						</tr>
						<tr>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Masuk</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Keluar</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>FFA</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Moist</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Dirty</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Brutto</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Tarra</td>
							<td style='text-transform: uppercase;border:1px solid black;' align=center>Netto</td>
						</tr>
					</thead>
					<tbody>";$nox=0;
					foreach ($arrtkt as $tkt) {
						# Loop Tiket
						$nox++;
                        if (in_array($tkt, $tiketreject)) {
                            $warna = "style='background-color:yellow';";
                            $ket = "REJECT";
                            $ttlsdhi-=$beratbersih[$tkt];
                        }else {
                            $warna=$ket='';
                            $ttlsdhi+=$beratbersih[$tkt];
                        }
						$tab .="
							<tr ".$warna.">
								<td style='border:1px solid black;' align=center>".$nox."</td>
								<td style='border:1px solid black;' align=center>".($nox == 1 ? tglnmbln($tgl,'I','long') : '')."</td>
								<td style='border:1px solid black;' align=center>".$supir[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$nokendaraan[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$tkt."</td>
								<td style='border:1px solid black;' align=center>".$jammasuk[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$jamkeluar[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$nosegel[$tkt]."</td>
								<td style='border:1px solid black;' align=center>".$ffa[$tkt]."%</td>
								<td style='border:1px solid black;' align=center>".$moist[$tkt]."%</td>
								<td style='border:1px solid black;' align=center>".$dirt[$tkt]."%</td>
								<td style='border:1px solid black;' align=right>".number_format($beratkeluar[$tkt])."&nbsp;</td>
								<td style='border:1px solid black;' align=right>".number_format($beratmasuk[$tkt])."&nbsp;</td>
								<td style='border:1px solid black;' align=right>".($beratbersih[$tkt] == 0 || $beratbersih[$tkt] == '0' ? '' : number_format($beratbersih[$tkt]))."&nbsp;</td>
								<td style='border:1px solid black;' align=right><b>".number_format($ttlsdhi)."&nbsp;</b></td>
								<td style='border:1px solid black;font-color:red' align=center>".$ket."</td>
							</tr>";
						$ttlpertgl[$tgl]+=$beratbersih[$tkt];
					}
					$tab .="
						<tr>
							<td style='border:1px solid black;' align=center colspan=13><b>SUB TOTAL TANGGAL ".tglnmbln($tgl,'I','long')."</b></td>
							<td style='border:1px solid black;' align=right><b>".number_format($ttlpertgl[$tgl])."&nbsp;</b></td>
							<td style='border:1px solid black;' align=right></td>
							<td style='border:1px solid black;' align=right></td>
						</tr>";
					$sisakontrak = ($kuantitaskontrak-$ttlsdhi);
					if($sisakontrak > 0){
						$ket = "KEKURANGAN";
					}elseif($sisakontrak == 0){
						$ket = "";
					}else{
						$ket = "KELEBIHAN";
					}
					$tab .="
						<tr>
							<td style='border:1px solid black;' align=center colspan=13><b>".$ket." MUATAN</b></td>
							<td style='border:1px solid black;' align=right><b>".($sisakontrak < 0 ? "(".number_format($sisakontrak*-1).")" : number_format($sisakontrak)) ."&nbsp;</b></td>
							<td style='border:1px solid black;' align=right></td>
							<td style='border:1px solid black;' align=right></td>
						</tr>";
					$tab .="
					</tbody>
				</table>";
			}
            if($ceklist == 'true'){
                $catatankontrak = "KONTRAK SELESAI";
                $lebar = "227px";
            }
            if($ceklist == 'false'){
                $catatankontrak = "KONTRAK BELUM SELESAI";
                $lebar = "270px";
            }
            $s1 = "select namakaryawan,kodejabatan from " . $dbname . ".datakaryawan where kodejabatan='282' ";
            $r1 = fetchdata($s1);
            $s2 = "select namakaryawan from " . $dbname . ".datakaryawan where kodejabatan='297' ";
            $r2 = fetchdata($s2);
			$tab .="
				<div style='border:2px solid black;width:".$lebar.";padding:7px;font-size:13px;transform:uppercase'><b>Catatan: ".$catatankontrak." !</b></div>
				<div style=\"display:flex; justify-content:space-around; margin-bottom:-40px; text-align:center;font-size:11.4px'\">
					<table width=100% style='text-align:center; padding-bottom:-40px;vertical-align:bottom'>
						<tr>
							<td></td>
							<td></td>
							<td><b>PKS ".getNamaOrg($kodept).", ".tglnmbln($tglakhirtimbang,'I','long')."</b></td>
						</tr>
						<tr>
							<td>Diketahui Oleh:</td>
							<td>Di Periksa Oleh:</td>
							<td>Dibuat Oleh:</td>
						</tr>
						<tr>
                            <td style='height:25px'>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align=center style='padding-top:-5px;'><img src=images/ttd.jpeg style='width:100px;height:50px'></td>
                        </tr>
						<tr>
							<td style='padding-top:-20px'><b><u>".$r1[0]['namakaryawan']."</u></b></td>
							<td style='padding-top:-20px'><b><u>".$r2[0]['namakaryawan']."</u></b></td>
							<td style='padding-top:-20px'><b><u>".getNamaKaryawan($_SESSION['standard']['userid'])."</u></b></td>
						</tr>
						<tr>
							<td>".getNamaJabatan('282')."</td>
							<td>KTU</td>
							<td>".getNamaJabatan(getKary($_SESSION['standard']['userid'],'kodejabatan'))."</td>
						</tr>
					</table>
				</div>";

		$tab .= "</div>";

		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
        $dompdf->stream("REKAP LOADING " .$arrinisial[$kodebarang]." ".substr($nokontrak,0,3)." ".getNamaSupplier($transportir)."-".$kodecustomer, array("Attachment" => 0));
		break;

    default:
    break;
}
?>
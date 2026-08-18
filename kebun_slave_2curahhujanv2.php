<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method', '');
$divisi = checkPostGet('divisi', ''); 
$periode= checkPostGet('periode', '');
$tgl    = checkPostGet('tanggal', "");
$tipe   = checkPostGet('tipe', "");
$kodeorg= $_SESSION['empl']['lokasitugas'];
$tanggal= date('Y-m-d');
$table  = "";

$sInd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
$qInd=$owlPDO->query($sInd) or die(print " Gagal: ".PDOException::getMessage());
$qInd->setFetchMode(PDO::FETCH_ASSOC);
$rInd=$qInd->fetch();

// cek kalo user itu HO/RO
$qOrganisasi = selectQuery($dbname, "organisasi", "*", "kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."' AND (tipe = 'HOLDING' OR tipe = 'KANWIL')");
$resOrganisasi = fetchData($qOrganisasi);

// Periode 
$optPeriode = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qPeriode = selectQuery($dbname, "kebun_curahhujan", "distinct tanggal", "tanggal != '' AND flag = 'OMRO' GROUP BY left(tanggal,7) ORDER BY tanggal DESC");
$resPeriode = fetchData($qPeriode);
foreach ($resPeriode as $bar) {
    $optPeriode .= "<option value='".substr($bar['tanggal'], 0, 7)."'>".substr($bar['tanggal'], 0, 7)."</option>";
}

// Divisi 
$optDivisi = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
if (count($resOrganisasi) > 0) {
    $qDivisi = selectQuery($dbname, 'organisasi', 'kodeorganisasi, namaorganisasi', "tipe = 'AFDELING'");
} else {
    $qDivisi = selectQuery($dbname, 'organisasi', 'kodeorganisasi, namaorganisasi', "tipe = 'AFDELING' AND induk LIKE '".$kodeorg."%'");
}
$resDivisi = fetchData($qDivisi);
foreach ($resDivisi as $bar) {
    $optDivisi .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']. " - ".$bar['namaorganisasi']."</option>";
}

switch ($method) {
    case 'previewhujan':

        $optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
        // Preview realtime table kebun_curahhujan
        if (count($resOrganisasi) > 0) {
            $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "tanggal = '".$tanggal."' AND flag='OMRO'");
        } else {
            $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "kodeorg LIKE '".$kodeorg."%' AND tanggal = '".$tanggal."' AND flag='OMRO'");
        }
        $resCurahhujan = fetchData($qCurahhujan);
        $no = 0;
        $table .= "
            <table cellpadding=1 cellspacing=1 border=0>
                <tr>
                    <td valign=top>
                        <fieldset style=float:left><legend>Info</legend>
                            <table height=25px>
                                <tr>
                                    <td>".$_SESSION['lang']['pt']."</td>
                                    <td>:</td>
                                    <td>".$optNmOrg[$rInd['induk']]."</td>
                                </tr>
                                <tr>
                                    <td><h3>Hari ini</h3></td>
                                    <td>:</td>
                                    <td><h3>".$tanggal."</h3></td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                    <td valign=top>
                        <fieldset style=float:left;max-width:250px><legend>Klasifikasi Waktu berdasarkan standar BMKG</legend>
                            <table height=25px style=margin-top:5px>
                                <tr>
                                    <td>
                                        <ul>
                                            <li>Pagi (08.00-14.00)</li>
                                            <li>Siang (14.00-20.00</li>
                                            <li>Malam (20.00-02.00)</li>
                                            <li>Dini hari (02.00-08.00)</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                    <td valign=top>
                        <fieldset style=float:left><legend>Klasifikasi Hujan berdasarkan standar BMKG</legend>
                            <table height=25px>
                                <tr>
                                    <td>
                                        <ul>
                                            <li>0 mm/hari: Berawan</li>
                                            <li>0.5 - 20 mm/hari: Hujan Ringan</li>
                                            <li>20 - 50 mm/hari: Hujang Sedang</li>
                                            <li>50 - 100 mm/hari: Hujan Lebat</li>
                                            <li>100 - 150 mm/hari: Hujang Sangat Lebat</li>
                                            <li>> 150 mm/hari: Hujan Ekstrem</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                    <td valign=top>
                        <fieldset style=float:left;max-width:380px><legend>Keterangan</legend>
                            <table height=25px>
                                <tr>
                                    <td>
                                        <span class=color:red;>*</span> Untuk menyesuaikan dengan klasifikasi waktu yang sudah ada sebelumnya, maka
                                        <ul>
                                            <li>Data CH Malam hari standar BMKG dikonversi menjadi <b>Sore hari</b></li>
                                            <li>Data CH Dini hari standar BMKG dikonversi menjadi <b>Malam hari</b></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                </tr>
            </table> <br>
            <table border=0 cellspacing=1 cellpadding=1 style=width:40vw>
                <tr>
                    <td><h3>REALTIME CURAH HUJAN</h3></td>
                    <td>
                        <div style='display:flex;justify-content:space-between'>
                            <div>
                                <label>".$_SESSION['lang']['periode']." : </label>
                                <select id=periode style=width:170px onchange=\"prevHujan2()\">".$optPeriode."</select>
                            </div>
                            <div>
                                <label>".$_SESSION['lang']['afdeling']." : </label>
                                <select id=divisi style=width:170px onchange=\"prevHujan2()\">".$optDivisi."</select>
                            </div>
                            <div>
                                <button class=mybutton id=excel onclick=\"prevHujan2('excel')\">Excel</button>
                                <button class=mybutton id=pdf onclick=\"prevHujan2('pdf')\">PDF</button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td valign=top>
                        <table cellpadding=1 cellspacing=1 border=0 class=sortable style=width:40vw>
                            <thead>
                                <tr class=rowheader>
                                    <th>No</th>
                                    <th>".$_SESSION['lang']['kodeorg']."</th>
                                    <th>".$_SESSION['lang']['pagi']."</th>
                                    <th>".$_SESSION['lang']['siang']."</th>
                                    <th>".$_SESSION['lang']['malam']."</th>
                                    <th>Dini Hari</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>";
                            foreach ($resCurahhujan as $bar) {
                                $no+=1;

                                $event = "";
                                $qDataomro = selectQuery($dbname, "kebun_tempcurahhujan", "*", "kode='".$bar['kodeorg']."' AND datetime LIKE '".$periode."%' ORDER BY id DESC");
                                $resDataomro = fetchData($qDataomro);
                                if (count($resDataomro) != 0) {
                                    $event = "style='cursor:pointer' onclick=\"detailCh('".$bar['kodeorg']."', '".$bar['tanggal']."')\"";
                                }

                                $table .= 
                                "<tr class=rowcontent>
                                    <td align=center>".$no."</td>
                                    <td align=center ".$event.">".$bar['kodeorg']."</td>
                                    <td align=right>".$bar['pagi']."</td>
                                    <td align=right>".$bar['siang']."</td>
                                    <td align=right>".$bar['sore']."</td>
                                    <td align=right>".$bar['malam']."</td>
                                    <td align=center>".$bar['catatan']."</td>
                                </tr>";
                            }
            $table .= "
                            </tbody>
                        </table>        
                    </td>
                    <td valign=top id=filtercurahhujan>
                        <table cellpadding=1 cellspacing=1 border=0 class=sortable style=width:40vw>
                            <thead>
                                <tr class=rowheader>
                                    <th>No</th>
                                    <th>".$_SESSION['lang']['tanggal']."</th>
                                    <th>Kode Divisi</th>
                                    <th>".$_SESSION['lang']['pagi']."</th>
                                    <th>".$_SESSION['lang']['siang']."</th>
                                    <th>".$_SESSION['lang']['malam']."</th>
                                    <th>Dini Hari</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>";
                            if (count($resOrganisasi) > 0) {
                                $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "1=1 AND tanggal LIKE '".date('Y-m')."%' AND flag='OMRO' ORDER BY tanggal DESC");
                            } else {
                                $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "1=1 AND tanggal LIKE '".date('Y-m')."%' AND kodeorg LIKE '".$kodeorg."%' AND flag='OMRO' ORDER BY tanggal DESC");
                            }
                            $resCurahhujan = fetchData($qCurahhujan);
                            $no1 = 0;
                            foreach ($resCurahhujan as $bar) {
                                $no1+=1;
                                
                                $event = "";
                                $qDataomro = selectQuery($dbname, "kebun_tempcurahhujan", "*", "kode='".$bar['kodeorg']."' AND datetime LIKE '".$periode."%' ORDER BY id DESC");
                                $resDataomro = fetchData($qDataomro);
                                if (count($resDataomro) != 0) {
                                    $event = "style='cursor:pointer' onclick=\"detailCh('".$bar['kodeorg']."', '".$bar['tanggal']."')\"";
                                }

                                $table .= 
                                "<tr class=rowcontent>
                                    <td align=center>".$no1."</td>
                                    <td align=center>".$bar['tanggal']."</td>
                                    <td align=center ".$event.">".$bar['kodeorg']."</td>
                                    <td align=right>".$bar['pagi']."</td>
                                    <td align=right>".$bar['siang']."</td>
                                    <td align=right>".$bar['sore']."</td>
                                    <td align=right>".$bar['malam']."</td>
                                    <td align=center>".$bar['catatan']."</td>
                                </tr>";
                            }
            $table .= "
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            ";
        echo $table;
        break;

        case 'previewhujan2':

            if ($tipe == "excel" || $tipe == "pdf") {
                $divisinew = $divisi != "" ? $divisi : "All Unit";
                $periodenew = $periode != "" ? $periode : date("Y-m");

                $table = "<b>Laporan Curah Hujan ".$divisinew."</b> <br/> Periode ".numToMonth(substr($periodenew,5,2), "I", "long")." ".substr($periodenew,0,4)."";
                $table .= "<table cellpadding=1 cellspacing=1 border=1 style=width:40vw>";
            } else {
                $table .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style=width:40vw>";
            }
            
            $table .= "
                            <thead>
                                <tr class=rowheader>
                                    <th>No</th>
                                    <th>".$_SESSION['lang']['tanggal']."</th>
                                    <th>Kode Divisi</th>
                                    <th>".$_SESSION['lang']['pagi']."</th>
                                    <th>".$_SESSION['lang']['siang']."</th>
                                    <th>".$_SESSION['lang']['malam']."</th>
                                    <th>Dini Hari</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>";
                            $where = '';
                            // Preview realtime table kebun_curahhujan dengan filter
                            if ($periode != "") {
                                $where .= " AND tanggal LIKE '".$periode."%'";
                            } else {
                                $where .= " AND tanggal LIKE '".date('Y-m')."%'";
                            }
                            if ($divisi != '') {
                                $where .= " AND kodeorg = '".$divisi."'";
                            } else {
                                if (count($resOrganisasi) > 0) {
                                    $where .= "";
                                } else {
                                    $where .= " AND kodeorg LIKE '".$kodeorg."%'";
                                }
                            }
                            $qCurahhujan = selectQuery($dbname, 'kebun_curahhujan', "*", "1=1 ".$where." AND flag='OMRO' ORDER BY tanggal DESC");
                            $resCurahhujan = fetchData($qCurahhujan);
                            $no1 = 0;
                            foreach ($resCurahhujan as $bar) {
                                $no1+=1;
                                
                                $event = "";
                                $qDataomro = selectQuery($dbname, "kebun_tempcurahhujan", "*", "kode='".$bar['kodeorg']."' AND datetime LIKE '".$periode."%' ORDER BY id DESC");
                                $resDataomro = fetchData($qDataomro);
                                if (count($resDataomro) != 0) {
                                    $event = "style='cursor:pointer' onclick=\"detailCh('".$bar['kodeorg']."', '".$bar['tanggal']."')\"";
                                }

                                $table .= 
                                "<tr class=rowcontent>
                                    <td align=center>".$no1."</td>
                                    <td align=center>".$bar['tanggal']."</td>
                                    <td align=center ".$event.">".$bar['kodeorg']."</td>
                                    <td align=right>".$bar['pagi']."</td>
                                    <td align=right>".$bar['siang']."</td>
                                    <td align=right>".$bar['sore']."</td>
                                    <td align=right>".$bar['malam']."</td>
                                    <td align=center>".$bar['catatan']."</td>
                                </tr>";
                            }
            $table .= "
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            ";

        if ($tipe == "excel") {
            $nop = "Laporan_Curahhujan_".$periodenew."_".$divisinew.".xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet("Laporan", $table);
            $xls->headers($nop);
            echo $xls->buildFile();
        } else if ($tipe == "pdf") {
            $dompdf = new Dompdf();
			$options = $dompdf->getOptions();
			$options->set(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true));
			$dompdf->loadHtml($table);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->setOptions($options);
			$dompdf->render();
			ob_end_clean();
			$dompdf->stream("Laporan_Curahhujan_".$periodenew."_".$divisinew.".pdf", array('Attachment' => 0));
        } else {
            echo $table;
        }
        break;

        case 'getDetail':
            $qDetail = selectQuery($dbname, 'kebun_tempcurahhujan', "kode, left(datetime, 10) as tanggal, substr(datetime, -8, 8) as jam, sum(ch) as chperjam", "kode='".$divisi."' AND datetime LIKE '".$tgl."%' GROUP BY substr(datetime, -8, 2) ORDER BY id DESC");
            $resDetail = fetchData($qDetail);
            foreach ($resDetail as $bar) {
                $listjam[substr($bar['jam'], 0, 2)] = substr($bar['jam'], 0, 2);
                $listchperjam[substr($bar['jam'], 0, 2)] = $bar['chperjam'];
            }
            ksort($listjam);
            ksort($listchperjam);
            
            $table .= "
                <table class=sortable cellpadding=1 cellspacing=1 border=0 class=sortable style=width:50vw>
                    <thead>
                        <tr class=rowheader>
                            <th align=center>".$_SESSION['lang']['tanggal']."</th>
                            <th align=center>".$_SESSION['lang']['afdeling']."</th>";
                    foreach ($listjam as $bar) {
                        $table.="<th align=center>".$bar.":00</th>";
                    }
            $table .= "
                        </tr>
                    </thead>";
            $table .= "<tbody>";
                    $table .= "
                        <tr class=rowcontent>
                            <td align=center>".$tgl."</td>
                            <td align=center>".$divisi."</td>";
                        foreach ($listchperjam as $bar) {
                            $table .= "<td align=right>".number_format($bar, 2)."</td>";
                        }
                    $table.="</tr>";

            $table .= "
                    </tbody>
            ";

        echo $table;
        break;
}

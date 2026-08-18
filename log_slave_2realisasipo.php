<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//$arr="##klmpkBrg##kdBrg##tglDr##tanggalSampai";
$proses = empty($_POST['proses']) ? (isset($_GET['proses']) ? $_GET['proses'] : '') : $_POST['proses'];
$klmpkBrg = empty($_POST['klmpkBrg']) ? (isset($_GET['klmpkBrg']) ? $_GET['klmpkBrg'] : '') : $_POST['klmpkBrg'];
$subklmpkBrg = empty($_POST['subklmpkBrg']) ? (isset($_GET['subklmpkBrg']) ? $_GET['subklmpkBrg'] : '') : $_POST['subklmpkBrg'];
$kdBrg = empty($_POST['kdBrg']) ? (isset($_GET['kdBrg']) ? $_GET['kdBrg'] : '') : $_POST['kdBrg'];
$tglDr = empty($_POST['tglDr']) ? (isset($_GET['tglDr']) ? tanggalsystem($_GET['tglDr']) : '') : tanggalsystem($_POST['tglDr']);
$tanggalSampai = empty($_POST['tanggalSampai']) ? (isset($_GET['tanggalSampai']) ? tanggalsystem($_GET['tanggalSampai']) : '') : tanggalsystem($_POST['tanggalSampai']);
$unit = empty($_POST['unit']) ? (isset($_GET['unit']) ? $_GET['unit'] : '') : $_POST['unit'];
$nmBrg = empty($_POST['nmBrg']) ? (isset($_GET['nmBrg']) ? $_GET['nmBrg'] : '') : $_POST['nmBrg'];
$sKlmpk = "select kode,kelompok from " . $dbname . ".log_5klbarang order by kode";
$qKlmpk=$owlPDO->query($sKlmpk) or die(print " Gagal: ".PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);

while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}

$sTgl = "select nopp,tanggal from " . $dbname . ".log_prapoht order by tanggal";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while ($rTgl = $qTgl->fetch()) {
    $rTglNopp[$rTgl['nopp']] = $rTgl['tanggal'];
}

$where = "";
if (($tglDr != '') || ($tanggalSampai != '')) {
    $where.=" and (tanggal between '" . $tglDr . "' and '" . $tanggalSampai . "')";
}
if ($unit != '') {
    $where.=" and nopo like '%" . $unit . "%'";
}
if ($klmpkBrg != '') {
    $where.=" and substr(a.kodebarang,1,3)='" . $klmpkBrg . "'";

}
if ($subklmpkBrg != '') {
    $where.=" and kode='" . $subklmpkBrg . "'";

}
if ($kdBrg != '') {
    $where.=" and a.kodebarang='" . $kdBrg . "'";
}


switch ($proses) {
    case'getBrg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang='" . $klmpkBrg . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
         //exit('eror'.$klmpkBrg);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodebarang'] . ">" . $rOrg['namabarang'] . "</option>";
        }
        echo $optorg;
        break;

        case'subklmpkBrg':
        //echo "warning:masuk";
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select kode,namasubkelompok from " . $dbname . ".log_5subklbarang where kelompok='" . $klmpkBrg . "' order by kode asc ";
        $qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
         //exit('eror'.$sOrg);

        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kode'] . ">" . $rOrg['namasubkelompok'] . "</option>";
        }
        echo $optorg;
        break;

    case'preview':

        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period not correct";
            exit();
        }

        if($unit=='')
        {
        $sOrg = "select kodeorganisasi,namaorganisasi,tipe from " . $dbname . ".organisasi where tipe='PT' order by kodeorganisasi asc";        
            //$holding=1;
        }
        else
        {
            //$holding=2;

        $sOrg = "select kodeorganisasi,namaorganisasi,tipe from " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";    
        }

        $tab = "<table cellspacing=1 border=0 class=sortable width=100%>
        <thead >
        <tr class=rowheader>
                <th align=center rowspan='2'>" . $_SESSION['lang']['kodebarang'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</th>";
        
        $jumlahorg=0;
        $holding='';
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $jumlahorg+=1;
            if($rOrg['tipe']=='PT')
            {
                $holding=1;
            }
            else
            {
                $holding=0;
            }
            $tab.="<th align=center colspan='3'>" . $rOrg['namaorganisasi'] . "</th>";
        }

        
        $tab.="</tr><tr class=rowheader>";
        for ($i=0; $i < $jumlahorg; $i++) { 
        $tab.="<th align=center>Total (Exclude PPN)</th>
                <th align=center>PPN</th>
                <th align=center>Total (Include PPN)</th>";
        }



        $tab.="</tr>
        </thead>
        <tbody>";

        
        $sData = "select c.namabarang,a.kodebarang,hargasatuan,jumlahpesan,matauang,nopo,ppn,kurs 
                from " . $dbname . ".log_po_vw a 
                left join " . $dbname . ".log_5masterbarang c on a.kodebarang=c.kodebarang 
                left join " . $dbname . ".log_5subklbarang b on substr(a.kodebarang,1,5)=kode 
                where statuspo>1 " . $where . " order by c.kelompokbarang,c.kodebarang asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
         //exit($sData) ;
		$arrnilai = array();
        $arrnilaix = array();
        $arrkodbarang=array();
        while ($rData = $qData->fetch()) {
           $slasnopo=explode('/', $rData['nopo']);
           $kdorgx='';
           if($holding==1)
           {
                $kdorgx=$slasnopo[5];
           }
           else
           {
                $kdorgx=$slasnopo[4];
           }
           @$arrkodbarang[$rData['kodebarang']]=$rData['namabarang'];
           if ($rData['matauang'] != 'IDR') {
                if ($rData['matauang'] != '') {
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += (($rData['kurs'] * $rData['hargasatuan'])*$rData['jumlahpesan']);
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ((($rData['kurs'] * $rData['hargasatuan'])*$rData['jumlahpesan'])*$rData['ppn']);
                } else {
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += ($rData['kurs'] * $rData['hargasatuan']);
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ($rData['kurs'] * $rData['hargasatuan'])*$rData['ppn'];
                }
            } else {
                @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += ($rData['kurs'] * $rData['hargasatuan']);
                @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ($rData['kurs'] * $rData['hargasatuan'])*$rData['ppn'];
            } 
        }
        $no=0;
        $total=array(); 
        $brs = 1;
        if(count($arrkodbarang)==0)
        {
            $tab.="<tr class='rowcontent'><td colspan=99 align=center><b>Data Not Found</b></td></tr>";
            echo $tab;
            break;
        }
        foreach ($arrkodbarang as $kodebarangx => $namabarangx) {
            $no+=1;

                if (isset($klmpkBarang) and $klmpkBarang != substr($kodebarangx, 0, 3)) {
                    if($no!=1)
                    {
                        $tab.="<tr class='rowcontent'><td colspan=2 align=center><b>SUBTOTAL</b></td>";
                        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                        while ($rOrg = $qOrg->fetch()) {
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['nonppn'], 2) . "</td>";
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['inppn'], 2) . "</td>";
                        }
                        $tab.="</tr>";
                    }
                    $brs = 1;
                    $subtotal=array(); 
                }
                if ($brs == 1) {
                    $klmpkBarang = substr($kodebarangx, 0, 3);
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td><b>" . substr($kodebarangx, 0, 3) . "</b></td><td><b>" . $rKelompok[$klmpkBarang] . "</b></td>";
                    for ($i=0; $i < $jumlahorg; $i++) { 
                    $tab.="<td><b></b></td><td><b></b></td><td><b></b></td>";
                    }
                    
                    $brs = 0;
                }

                $tab.="<tr class='rowcontent'>";
                $tab.="<td>" . $kodebarangx . "</td>";
                $tab.="<td>" . $namabarangx . "</td>";
                $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                while ($rOrg = $qOrg->fetch()) {
                @$total[$rOrg['kodeorganisasi']]['nonppn']+=(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']);
                @$total[$rOrg['kodeorganisasi']]['ppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'];
                @$total[$rOrg['kodeorganisasi']]['inppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'];


                @$subtotal[$rOrg['kodeorganisasi']]['nonppn']+=(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']);
                @$subtotal[$rOrg['kodeorganisasi']]['ppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'];
                @$subtotal[$rOrg['kodeorganisasi']]['inppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'];


                $tab.="<td align=right>" . number_format((@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']), 2) . "</td>";
                $tab.="<td align=right>" . number_format(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
                $tab.="<td align=right>" . number_format(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'], 2) . "</td>";
                    
                }
                $tab.="</tr>";
                

                
            
        }
        $tab.="<tr class='rowcontent'><td colspan=2 align=center><b>TOTAL</b></td>";
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['nonppn'], 2) . "</td>";
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['inppn'], 2) . "</td>";
        }
        $tab.="</tr>";
        $tab.="</tbody></table>";
        echo $tab;
        break;
    
    case'excel':

        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period not correct";
            exit();
        }

        if($unit=='')
        {
        $sOrg = "select kodeorganisasi,namaorganisasi,tipe from " . $dbname . ".organisasi where tipe='PT' order by kodeorganisasi asc";        
            //$holding=1;
        }
        else
        {
            //$holding=2;

        $sOrg = "select kodeorganisasi,namaorganisasi,tipe from " . $dbname . ".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";    
        }

        $tab = "<table cellspacing=1 border=1 class=sortable width=100%>
        <thead >
        <tr class=rowheader>
                <td align=center rowspan='2'>" . $_SESSION['lang']['kodebarang'] . "</td>
                <td align=center rowspan='2'>" . $_SESSION['lang']['namabarang'] . "</td>";
        
        $jumlahorg=0;
        $holding='';
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $jumlahorg+=1;
            if($rOrg['tipe']=='PT')
            {
                $holding=1;
            }
            else
            {
                $holding=0;
            }
            $tab.="<td align=center colspan='3'>" . $rOrg['namaorganisasi'] . "</td>";
        }

        
        $tab.="</tr><tr class=rowheader>";
        for ($i=0; $i < $jumlahorg; $i++) { 
        $tab.="<td align=center>Total (Exclude PPN)</td>
                <td align=center>PPN</td>
                <td align=center>Total (Include PPN)</td>";
        }



        $tab.="</tr>
        </thead>
        <tbody>";

        
        $sData = "select c.namabarang,a.kodebarang,hargasatuan,jumlahpesan,matauang,nopo,ppn,kurs 
                from " . $dbname . ".log_po_vw a 
                left join " . $dbname . ".log_5masterbarang c on a.kodebarang=c.kodebarang 
                left join " . $dbname . ".log_5subklbarang b on substr(a.kodebarang,1,5)=kode 
                where statuspo>1 " . $where . " order by c.kelompokbarang,c.kodebarang asc";
        $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
        $qData->setFetchMode(PDO::FETCH_ASSOC);
         //exit($sData) ;
        $arrnilai = array();
        $arrnilaix = array();
        $arrkodbarang=array();
        while ($rData = $qData->fetch()) {
           $slasnopo=explode('/', $rData['nopo']);
           $kdorgx='';
           if($holding==1)
           {
                $kdorgx=$slasnopo[5];
           }
           else
           {
                $kdorgx=$slasnopo[4];
           }
           @$arrkodbarang[$rData['kodebarang']]=$rData['namabarang'];
           if ($rData['matauang'] != 'IDR') {
                if ($rData['matauang'] != '') {
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += (($rData['kurs'] * $rData['hargasatuan'])*$rData['jumlahpesan']);
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ((($rData['kurs'] * $rData['hargasatuan'])*$rData['jumlahpesan'])*$rData['ppn']);
                } else {
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += ($rData['kurs'] * $rData['hargasatuan']);
                    @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ($rData['kurs'] * $rData['hargasatuan'])*$rData['ppn'];
                }
            } else {
                @$arrnilai[$rData['kodebarang']][$kdorgx]['nilaiinppn'] += ($rData['kurs'] * $rData['hargasatuan']);
                @$arrnilai[$rData['kodebarang']][$kdorgx]['ppn'] += ($rData['kurs'] * $rData['hargasatuan'])*$rData['ppn'];
            } 
        }
        $no=0;
        $total=array(); 
        $brs = 1;
        if(count($arrkodbarang)==0)
        {
            $tab.="<tr class='rowcontent'><td colspan=99 align=center><b>Data Not Found</b></td></tr>";
            echo $tab;
            break;
        }
        foreach ($arrkodbarang as $kodebarangx => $namabarangx) {
            $no+=1;

                if (isset($klmpkBarang) and $klmpkBarang != substr($kodebarangx, 0, 3)) {
                    if($no!=1)
                    {
                        $tab.="<tr class='rowcontent'><td colspan=2 align=center><b>SUBTOTAL</b></td>";
                        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                        while ($rOrg = $qOrg->fetch()) {
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['nonppn'], 2) . "</td>";
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
                            $tab.="<td align=right>" . number_format(@$subtotal[$rOrg['kodeorganisasi']]['inppn'], 2) . "</td>";
                        }
                        $tab.="</tr>";
                    }
                    $brs = 1;
                    $subtotal=array(); 
                }
                if ($brs == 1) {
                    $klmpkBarang = substr($kodebarangx, 0, 3);
                    $tab.="<tr class='rowcontent'>";
                    $tab.="<td><b>" . substr($kodebarangx, 0, 3) . "</b></td><td><b>" . $rKelompok[$klmpkBarang] . "</b></td>";
                    for ($i=0; $i < $jumlahorg; $i++) { 
                    $tab.="<td><b></b></td><td><b></b></td><td><b></b></td>";
                    }
                    
                    $brs = 0;
                }

                $tab.="<tr class='rowcontent'>";
                $tab.="<td>" . $kodebarangx . "</td>";
                $tab.="<td>" . $namabarangx . "</td>";
                $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
                $qOrg->setFetchMode(PDO::FETCH_ASSOC);
                while ($rOrg = $qOrg->fetch()) {
                @$total[$rOrg['kodeorganisasi']]['nonppn']+=(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']);
                @$total[$rOrg['kodeorganisasi']]['ppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'];
                @$total[$rOrg['kodeorganisasi']]['inppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'];


                @$subtotal[$rOrg['kodeorganisasi']]['nonppn']+=(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']);
                @$subtotal[$rOrg['kodeorganisasi']]['ppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'];
                @$subtotal[$rOrg['kodeorganisasi']]['inppn']+=@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'];


                $tab.="<td align=right>" . number_format((@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn']-@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn']), 2) . "</td>";
                $tab.="<td align=right>" . number_format(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
                $tab.="<td align=right>" . number_format(@$arrnilai[$kodebarangx][$rOrg['kodeorganisasi']]['nilaiinppn'], 2) . "</td>";
                    
                }
                $tab.="</tr>";
                

                
            
        }
        $tab.="<tr class='rowcontent'><td colspan=2 align=center><b>TOTAL</b></td>";
        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['nonppn'], 2) . "</td>";
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['ppn'], 2) . "</td>";
            $tab.="<td align=right>" . number_format(@$total[$rOrg['kodeorganisasi']]['inppn'], 2) . "</td>";
        }
        $tab.="</tr>";
        $tab.="</tbody></table>";


        //echo "warning:".$strx;
        //=================================================


        $tab.="</table>Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $thisDate = date("YmdHms");
        //$nop_="Laporan_Pembelian";
        $nop_ = "Laporan_Realiasasi_Purchase_Order_" . $thisDate;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls.gz';
                            </script>";
        /* if(strlen($tab)>0)
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
          closedir($handle);
          } */
        break;
    case'getTgl':
        if ($periode != '') {
            $tgl = $periode;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        } elseif ($period != '') {
            $tgl = $period;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        }
        if ($kdUnit == '') {
            $kdUnit = $_SESSION['lang']['lokasitugas'];
        }
        $sTgl = "select distinct tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($kdUnit, 0, 4) . "' and periode='" . $tanggal . "' ";
        $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
		$rTgl = $qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai']) . "###" . tanggalnormal($rTgl['tanggalsampai']);
        break;
    case'getBarang':
        $tab = "<fieldset><legend>" . $_SESSION['lang']['result'] . "</legend>
                        <div style=\"overflow:auto;max-height:295px;\">
                        <table cellpading=1 border=0 class=sortable>
                        <thead>
                        <tr class=rowheader>
                        <td align=center>No.</td>
                        <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
                        <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
                        <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
                        </tr><tbody>
                        ";
        $sLoad = "select kodebarang,namabarang,satuan from " . $dbname . ".log_5masterbarang where  kelompokbarang='" . $klmpkBrg . "' and (kodebarang like '%" . $nmBrg . "%'
            or namabarang like '%" . $nmBrg . "%')";
        $qLoad=$owlPDO->query($sLoad) or die(print " Gagal: ".PDOException::getMessage());
		$qLoad->setFetchMode(PDO::FETCH_ASSOC);
		while ($res = $qLoad->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent style=cursor:pointer onclick=\"setData('" . $res['kodebarang'] . "')\">";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $res['kodebarang'] . "</td>";
            $tab.="<td>" . $res['namabarang'] . "</td>";
            $tab.="<td>" . $res['satuan'] . "</td>";
            $tab.="</tr>";
        }
        echo $tab;

        break;

    default:
        break;
}
?>
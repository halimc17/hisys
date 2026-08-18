<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    require_once('dompdf/autoload.inc.php');
    use Dompdf\Dompdf;

    $proses     =checkPostGet('proses','');
    $type       =checkPostGet('type','');

    $kdpabrik   =checkPostGet('kdpabrik','');
    $kdbrg      =checkPostGet('kdbrg','');
    $cust       =checkPostGet('cust','');

    $tgltrans   =checkPostGet('tgltrans','');
    $tgltrans2  =checkPostGet('tgltrans2','');
    $tgltrans2  =tanggalsystemn($tgltrans2);
    $tgltrans   =explode('-',$tgltrans);
    $tgltrans   =$tgltrans[2]."-".$tgltrans[1]."-".$tgltrans[0];

    switch($proses)
    {
        case'preview1':
            $result = "";

            # Filter
            $no = 0;
            $totberatmasuk = array();
            $totberatkeluar = array();
            $totberatbersih = array();
            $totkgpotsortasi = array();
            $alltotberatmasuk = 0;
            $alltotberatkeluar = 0;
            $alltotberatbersih = 0;
            $alltotkgpotsortasi = 0;
            
            if($cust!=''){
                $where.=" and (kodeorg='".$cust."' or kodecustomer='".$cust."' or kodesupplier='".$cust."')";
            }else {
                $where.=" and (kodesupplier in ( select supplierid from $dbname.`log_5supplier` where supplierid IN (SELECT supplierid FROM $dbname.log_5supkelompok WHERE tipe LIKE '%SUPPLIERTBS%') ORDER BY supplierid DESC))";
            }

            if($kdbrg!=''){
                $where.=" and kodebarang like '".$kdbrg."%'";
            }
            # End Filter

            # SQL
            $sql = "SELECT * FROM ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$tgltrans."' and '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by kodesupplier asc";
            $res = fetchData($sql);
            foreach($res as $row):
                # Per Komoditi
                $komoditi[$row['kodebarang']] = $row['kodebarang'];
                $qtykomoditi[$row['kodebarang']] += $row['beratbersih'];
                $qtykomoditi1[$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
                $sortasi[$row['kodebarang']] += ($row['kgpotsortasi']);
                
                # Per Supplier
                # Vendor
                if($row['kodesupplier']!='') {
                    $komoditisup[$row['kodesupplier']][$row['kodebarang']] = $row['kodebarang'];
                    $qtykomoditisup[$row['kodesupplier']][$row['kodebarang']] += $row['beratbersih'];
                    $qtykomoditisup1[$row['kodesupplier']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
                    $sortasisup[$row['kodesupplier']][$row['kodebarang']] += $row['kgpotsortasi'];
                }
                # Customer
                if($row['kodecustomer']!='') {
                    $komoditisup[$row['kodecustomer']][$row['kodebarang']] = $row['kodebarang'];
                    $qtykomoditisup[$row['kodecustomer']][$row['kodebarang']] += $row['beratbersih'];
                    $qtykomoditisup1[$row['kodecustomer']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
                    $sortasisup[$row['kodecustomer']][$row['kodebarang']] += $row['kgpotsortasi'];
                }
                # Inti
                if($row['kodeorg']!='') {
                    $komoditisup[$row['kodeorg']][$row['kodebarang']] = $row['kodebarang'];
                    $qtykomoditisup[$row['kodeorg']][$row['kodebarang']] += $row['beratbersih'];
                    $qtykomoditisup1[$row['kodeorg']][$row['kodebarang']] += ($row['beratbersih']+$row['kgpotsortasi']);
                    $sortasisup[$row['kodeorg']][$row['kodebarang']] += $row['kgpotsortasi'];
                }

                # Per Tanggal
                # Vendor
                if($row['kodesupplier']!='') {
                    $komoditisuptgl[$row['kodesupplier']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
                    $qtykomoditisuptgl[$row['kodesupplier']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
                }
                # Customer
                if($row['kodecustomer']!='') {
                    $komoditisuptgl[$row['kodecustomer']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
                    $qtykomoditisuptgl[$row['kodecustomer']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
                }
                # Inti
                if($row['kodeorg']!='') {
                    $komoditisuptgl[$row['kodeorg']][$row['kodebarang']][substr($row['tanggal'],0,10)] = $row['kodebarang'];
                    $qtykomoditisuptgl[$row['kodeorg']][$row['kodebarang']][substr($row['tanggal'],0,10)] += $row['beratbersih'];
                }
            endforeach;

            #==========================================================================================================#
            # REKAP
            #==========================================================================================================#
            # Rekap Per Komoditi
            if($type=='html') {
                $result .= "<fieldset style='width:30%;'>";
                    $result .= "<legend><b>Rekap Per Komoditi</b></legend>";
                    // $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
                    $result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
                        $result .= "<thead>";
                            $result .= "<tr class=rowheader>";
                                $result .= "<th align=center width=40%>Komoditi</th>";
                                if($kdbrg == '400000003'){
                                    $result .= "<th align=center width=10%>Berat Bersih 1</th>";
                                    $result .= "<th align=center width=10%>Potongan</th>";
                                    $result .= "<th align=center width=10%>Berat Bersih 2</th>";
                                }else{
                                    $result .= "<th align=center width=10%>Berat Bersih</th>";
                                }
                                $result .= "<th align=center width=5%>Satuan</th>";
                            $result .= "</tr>";
                        $result .= "</thead>";
                
                    foreach($komoditi as $kdbarang => $val):
                        $result .= "<tr class=rowcontent>";
                            $result .= "<td align=left>".getNamaBrg($kdbarang)."</td>";
                            if($kdbrg == '400000003'){
                                $result .= "<td align=right>".number_format($qtykomoditi1[$kdbarang])."</td>";
                                $result .= "<td align=right>".number_format($$sortasi[$kdbarang])."</td>";
                                $result .= "<td align=right>".number_format($qtykomoditi[$kdbarang])."</td>";
                            }else{
                                $result .= "<td align=right>".number_format($qtykomoditi[$kdbarang])."</td>";
                            }
                            $result .= "<td align=center>".getSatBrg($kdbarang)."</td>";
                        $result .= "</tr>";
                    endforeach;
                    
                    $result .= "</table>";
                $result .= "</fieldset>";

                # Rekap Per Supplier
                $result .= "<fieldset style='width:30%;margin-top:20px!important;'>";
                    $result .= "<legend><b>Rekap Per Supplier Per Komoditi</b></legend>";
                    // $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
                    $result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
                        $result .= "<thead>";
                            $result .= "<tr class=rowheader>";
                                $result .= "<th align=center width=40%>Supplier</th>";
                                $result .= "<th align=center width=40%>Komoditi</th>";
                                if($kdbrg == '400000003'){
                                    $result .= "<th align=center width=10%>Berat Bersih 1</th>";
                                    $result .= "<th align=center width=10%>Potongan</th>";
                                    $result .= "<th align=center width=10%>Berat Bersih 2</th>";
                                }else{
                                    $result .= "<th align=center width=10%>Berat Bersih</th>";
                                }
                                $result .= "<th align=center width=5%>Satuan</th>";
                            $result .= "</tr>";
                        $result .= "</thead>";
                
                    foreach($komoditisup as $kdsup => $valsup):
                        foreach($valsup as $kdbarangsup => $valbrg):
                            $namasupplier = '';
                            $namacustomer = '';
                            $namaorganisasi = '';

                            if(getNamaSupplier($kdsup)!='') {
                                $namasupplier = getNamaSupplier($kdsup); 
                            } else if(getNamaCustomer($kdsup)!='') {
                                $namacustomer = getNamaCustomer($kdsup); 
                            } else if(getNamaOrg($kdsup)!='') {
                                $namaorganisasi = getNamaOrg($kdsup); 
                            } else {
                                $namasupplier = '';
                                $namacustomer = '';
                                $namaorganisasi = '';
                            }

                            $namafix = ($namasupplier == '' ? ($namacustomer == '' ? ($namaorganisasi != '' ? $namaorganisasi : '') : $namacustomer) : $namasupplier);

                            $result .= "<tr class=rowcontent>";
                                $result .= "<td align=left>".$namafix."</td>";
                                $result .= "<td align=left>".getNamaBrg($kdbarangsup)."</td>";
                                if($kdbrg == '400000003'){
                                    $result .= "<td align=right>".number_format($qtykomoditisup1[$kdsup][$kdbarangsup])."</td>";
                                    $result .= "<td align=right>".number_format($sortasisup[$kdsup][$kdbarangsup])."</td>";
                                    $result .= "<td align=right>".number_format($qtykomoditisup[$kdsup][$kdbarangsup])."</td>";
                                }else{
                                    $result .= "<td align=right>".number_format($qtykomoditisup[$kdsup][$kdbarangsup])."</td>";
                                }
                                $result .= "<td align=center>".getSatBrg($kdbarangsup)."</td>";
                            $result .= "</tr>";
                        endforeach;
                    endforeach;
                    
                    $result .= "</table>";
                $result .= "</fieldset>";

                # Rekap Per Supplier Per Tanggal
                $result .= "<fieldset style='width:30%;margin-top:20px!important;'>";
                    $result .= "<legend><b>Rekap Per Supplier Per Komoditi Per Tanggal</b></legend>";
                    // $result .= "<p>".number_format($qtykomoditi[$kdbarang])."</p>";
                    $result .= "<table cellspacing=1 cellpading=3 class=sortable width=100%>";
                        $result .= "<thead>";
                            $result .= "<tr class=rowheader>";
                                $result .= "<th align=center width=40%>Supplier</th>";
                                $result .= "<th align=center width=40%>Komoditi</th>";
                                $result .= "<th align=center width=40%>Tanggal</th>";
                                $result .= "<th align=center width=10%>Berat Bersih</th>";
                                $result .= "<th align=center width=5%>Satuan</th>";
                            $result .= "</tr>";
                        $result .= "</thead>";
                
                    foreach($komoditisuptgl as $kdsup => $valsup):
                        foreach($valsup as $kdbarangsup => $valbrg):
                            foreach($valbrg as $tglsup => $valtgl):
                                $namasupplier = '';
                                $namacustomer = '';
                                $namaorganisasi = '';

                                if(getNamaSupplier($kdsup)!='') {
                                    $namasupplier = getNamaSupplier($kdsup); 
                                } else if(getNamaCustomer($kdsup)!='') {
                                    $namacustomer = getNamaCustomer($kdsup); 
                                } else if(getNamaOrg($kdsup)!='') {
                                    $namaorganisasi = getNamaOrg($kdsup); 
                                } else {
                                    $namasupplier = '';
                                    $namacustomer = '';
                                    $namaorganisasi = '';
                                }

                                $namafix = ($namasupplier == '' ? ($namacustomer == '' ? ($namaorganisasi != '' ? $namaorganisasi : '') : $namacustomer) : $namasupplier);

                                $result .= "<tr class=rowcontent>";
                                    $result .= "<td align=left>".$namafix."</td>";
                                    $result .= "<td align=left>".getNamaBrg($kdbarangsup)."</td>";
                                    $result .= "<td align=left>".$tglsup."</td>";
                                    $result .= "<td align=right>".number_format($qtykomoditisuptgl[$kdsup][$kdbarangsup][$tglsup])."</td>";
                                    $result .= "<td align=center>".($kdbarangsup)."</td>";
                                $result .= "</tr>";
                            endforeach;
                        endforeach;
                    endforeach;
                    
                    $result .= "</table>";
                $result .= "</fieldset>";
            }

            #==========================================================================================================#
            # END REKAP
            #==========================================================================================================#

            $result .= "<div></div>";
            if($type=='pdf')
            {
                if($kdbrg!=''){
                    $scale = '1.3';
                }else{
                    $scale = '1.3';
                }
                $border = 1;
                $whrsize="style='font-size:13px; border-collapse: collapse; transform-origin: top left;'";
                $result.="<table cellspacing=0 border='0' class=sortable align=center>
                    <tr>
                        <td style='font-weight:bold;text-align:center;font-size:27px'>Laporan Timbangan</td>
                    </tr>
                    <tr>
                        <td style='text-align:center'>Pabrik : ".getNamaOrg($kdpabrik)."</td>
                    </tr>
                    <tr>
                        <td style='text-align:center'>Tanggal : ".tglnmbln($tgltrans,'I','long')." s.d ".tglnmbln($tgltrans2,'I','long')."</td>
                    </tr>
                </table>";
            }else if($type=='html'){
                $border = 0;
                $px = '14px';
            }
            else
            {
                $border = 1;$whrsize="style='font-size:21px'";$px = '23px';
                $result.="<table cellspacing=1 border='0' class=sortable>
                    <tr>
                        <td colspan=15 style='font-weight:bold;text-align:center;font-size:29px'>Laporan Timbangan</td>
                    </tr>
                    <tr>
                        <td colspan=15 style='text-align:center;font-size:25px'>Pabrik : ".getNamaOrg($kdpabrik)."</td>
                    </tr>
                    <tr>
                        <td colspan=15 style='text-align:center;font-size:25px'>Tanggal : ".tglnmbln($tgltrans,'I','long')." s.d ".tglnmbln($tgltrans2,'I','long')."</td>
                    </tr>
                </table>";
            }
            $result.="<div class='table-scroll' style='height:60vh;margin-top:20px'>
                <table cellpadding=1 cellspacing=1 border='".$border."' class=sortable ".$whrsize." >
                    <thead class=rowheader>
                    <tr>
                        <th style='text-align:center'>No.</th>
                        
                        <th style='text-align:center'>".$_SESSION['lang']['tanggal']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['namabarang']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['noTiket']."</th>
                        <th style='text-align:center' colspan=2>".$_SESSION['lang']['supplier']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['kodenopol']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['sopir']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['beratMasuk']."<br> (Kg)</th>
                        <th style='text-align:center'>".$_SESSION['lang']['beratKeluar']."<br> (Kg)</th>
                        <th style='text-align:center'>".$_SESSION['lang']['beratBersih']."<br> I (Kg)</th>
                        <th style='text-align:center'>".$_SESSION['lang']['potongankg']."<br></th>
                        <th style='text-align:center'>".$_SESSION['lang']['beratBersih']."<br> II (Kg)</th>
                        <th style='text-align:center'>".$_SESSION['lang']['jammasuk']."</th>
                        <th style='text-align:center'>".$_SESSION['lang']['jamkeluar']."</th>
                    </tr>
                    </thead>
                    <tbody>";

            $str="select * from ".$dbname.".pabrik_timbangan where left(tanggal,10) between '".$tgltrans."' and '".$tgltrans2."' and millcode='".$kdpabrik."' ".$where." order by tanggal,jammasuk";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $numrows=$res->rowCount();
            
            if($numrows <= 0){
                $result.="<tr class=rowcontent><td colspan=25 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
            }else{
                $temptgl='';
                while($bar=$res->fetch()){
                    @$nox+=1;
                    $beratangkut = (($bar['beratmasuk']-$bar['beratkeluar'])<0?($bar['beratmasuk']-$bar['beratkeluar'])*-1 : ($bar['beratmasuk']-$bar['beratkeluar']));
                    $totberatmasuk[substr($bar['tanggal'], 0,10)] = $totberatmasuk[substr($bar['tanggal'], 0,10)] + $bar['beratmasuk'];
                    $totberatkeluar[substr($bar['tanggal'], 0,10)] = $totberatkeluar[substr($bar['tanggal'], 0,10)] + $bar['beratkeluar'];
                    $totberatbersih[substr($bar['tanggal'], 0,10)] = $totberatbersih[substr($bar['tanggal'], 0,10)] + $bar['beratbersih'];
                    $totkgpotsortasi[substr($bar['tanggal'], 0,10)] = $totkgpotsortasi[substr($bar['tanggal'], 0,10)] + $bar['kgpotsortasi'];
                    $totberatangkut[substr($bar['tanggal'], 0,10)] +=$beratangkut;
                        
                    if(substr($bar['tanggal'], 0,10) != $temptgl && $temptgl !=''){
                        $col='8';$colttl='2';
                        $result.="<tr class=rowcontent>
                            <td colspan=".$col." style='font-weight:bold;text-align:center'>".$_SESSION['lang']['total']." ".tglnmbln($temptgl,'I','long')."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatmasuk[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatkeluar[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatangkut[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totkgpotsortasi[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatbersih[$temptgl],2)."</td>
                            <td colspan=".$colttl."></td>
                        </tr>";
                        $no=0;
                    }
                    $no+=1;
                    $bgcolor='';
                    
                    $result.="<tr class=rowcontent style='background-color:".$bgcolor."'>
                        <td style='text-align:center'>".$no."</td>
                        <td style='text-align:center' nowrap>".tanggalnormal(substr($bar['tanggal'], 0,10))."</td>
                        <td nowrap>".getNamaBrg($bar['kodebarang'])."</td>
                        <td style='text-align:center'>".$bar['notransaksi']."</td>
                        <td style='text-align:center'>".$bar['kodesupplier']."</td>
                        <td>".getNamaSupplier($bar['kodesupplier'])."</td>
                        <td style='text-align:center'>".$bar['nokendaraan']."</td>
                        <td>".$bar['supir']."</td>
                        <td style='text-align:right'>".number_format($bar['beratmasuk'],2)."</td>
                        <td style='text-align:right'>".number_format($bar['beratkeluar'],2)."</td>
                        <td style='text-align:right'>".number_format($beratangkut,2)."</td>
                        <td style='text-align:right'>".number_format($bar['kgpotsortasi'],2)."</td>
                        <td style='text-align:right'>".number_format($bar['beratbersih'],2)."</td>
                        <td style='text-align:center'>".$bar['jammasuk']."</td>
                        <td style='text-align:center'>".$bar['jamkeluar']."</td>
                    </tr>";

                    if($numrows == $nox){
                        $col='8';$colttl='2';
                        $result.="<tr class=rowcontent>
                            <td colspan=".$col." style='font-weight:bold;text-align:center'>".$_SESSION['lang']['total']." ".tglnmbln($temptgl,'I','long')."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatmasuk[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatkeluar[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatangkut[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totkgpotsortasi[$temptgl],2)."</td>
                            <td style='text-align:right;font-weight:bold;'>".number_format($totberatbersih[$temptgl],2)."</td>
                            <td colspan=".$colttl."></td>
                        </tr>";
                        $no=0;
                    }
                    $temptgl = substr($bar['tanggal'], 0,10);

                    $alltotberatmasuk = $alltotberatmasuk + $bar['beratmasuk'];
                    $alltotberatkeluar = $alltotberatkeluar + $bar['beratkeluar'];
                    $alltotberatbersih = $alltotberatbersih + $bar['beratbersih'];
                    $alltotkgpotsortasi = $alltotkgpotsortasi + $bar['kgpotsortasi'];
                    $alltotberatangkut +=$beratangkut;
                }
                $col='8';$colttl='2';
                $result.="<tr class=rowcontent>
                    <td colspan=".$col." style='font-weight:bold;text-align:center;font-size:".$px."'>".$_SESSION['lang']['total']." Keseluruhan</td>
                    <td style='text-align:right;font-weight:bold;font-size:".$px."'>".number_format($alltotberatmasuk,2)."</td>
                    <td style='text-align:right;font-weight:bold;font-size:".$px."'>".number_format($alltotberatkeluar,2)."</td>
                    <td style='text-align:right;font-weight:bold;font-size:".$px."'>".number_format($alltotberatangkut,2)."</td>
                    <td style='text-align:right;font-weight:bold;font-size:".$px."'>".number_format($alltotkgpotsortasi,2)."</td>
                    <td style='text-align:right;font-weight:bold;font-size:".$px."'>".number_format($alltotberatbersih,2)."</td>
                    <td colspan=".$colttl."></td>
                </tr>";
            }
            
            if($type=='html')
            {
                echo $result;
            }else if($type=='pdf'){
                $dompdf = new Dompdf();
                $dompdf->loadHtml($result);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $dompdf->stream("Laporan_Timbangan",array("Attachment"=>0));
            }
            else
            {
                $result.="</table></div>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];
                $nop_="Laporan_Timbangan_".$tgltrans."_s.d_".$tgltrans2;
                if(strlen($result)>0)
                {
                    if ($handle = opendir('tempExcel')) 
                    {
                        while (false !== ($file = readdir($handle))) 
                        {
                            if ($file != "." && $file != ".." && $file != "index.html") 
                            {
                                @unlink('tempExcel/'.$file);
                            }
                        }
                        closedir($handle);
                    }
                    $handle=fopen("tempExcel/".$nop_.".xls",'w');
                    if(!fwrite($handle,$result))
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
            }
        break;
    }
?>
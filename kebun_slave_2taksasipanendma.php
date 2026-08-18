<?php
    require_once('master_validation.php');
    require_once('lib/zLib.php');

    $param      = count($_POST) > 0 ? $_POST : $_GET;
    $proses     = checkPostGet('proses', '');
    $tanggal    = checkPostGet('tanggal', '');
    $pt         = checkPostGet('pt', '');
    $arrbi      = explode('-', tanggaldb($tanggal));
    $tahun      = $arrbi[0];
    $bulan      = $arrbi[1];
    $periode1   = $tahun . "-01";
    $jlhtgl     = substr(tglakhir($periode1),8,2);

    if($proses == 'preview' || $proses == 'excel'){
        $whrunt = $color = $color1 = $color2 = $whrdiv = $whrgnk = "";
        $pusingan=$listkodeorg=$kgtaksasi=$jlhpokok=$jjgmasak=$jjgoutput=$ha=$kgtesthi=$tktersedia=$bjr=array();
        if ($pt == '' && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Kode PT harus di pilih.");
        }
        // if ($kdorg == '' && ($proses == 'preview' || $proses == 'excel')) {
        //     exit("Warningsystem = Unit harus di pilih.");
        // }
        // if ($prd == '' && ($proses == 'preview' || $proses == 'excel')) {
        //     exit("Warningsystem = Periode harus di pilih.");
        // }

        // if ($kdorg != '') {
        //     $whrunt = " and unit ='" . $kdorg . "'";
        // }

        // if($divisi != ''){
        //     $whrdiv = " and subbagian ='".$divisi."' ";
        // }

        $arrkateg = array('janjangpanen','brondol','bjr','outputpanen','lebihbasis','produktifitas1','produktifitas2','premibrondol','denda');
        
        // Ambil Data
        $sql="select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."'";
        $hsl=fetchData($sql);
        foreach ($hsl as $v) {
            $listkodeorg[$v['kodeorganisasi']]=$v['kodeorganisasi'];
        }
        $qry = "select * from ".$dbname.".kebun_taksasi where substr(afdeling,1,4) in ('".implode("','",$listkodeorg)."') and tanggal='".tanggaldb($tanggal)."'";
        $rst = fetchData($qry);
        foreach ($rst as $val) {
            $afd[substr($val['afdeling'],0,4)][$val['afdeling']]=$val['afdeling'];
            $jlhpokok[$val['afdeling']]+=$val['jmlhpokok'];
            $jjgmasak[$val['afdeling']]+=$val['jjgmasak'];
            $jjgoutput[$val['afdeling']]+=$val['jjgoutput'];
            $kgtaksasi[$val['afdeling']]+=($val['bjr']*$val['jjgoutput']);
            $kgtesthi[$val['afdeling']]+=($val['bjr']*$val['jjgmasak']);
            $ha[$val['afdeling']]+=$val['haesok'];
            $tktersedia[$val['afdeling']]=$val['hkdigunakan'];
            $bjr[$val['afdeling']]=$val['bjr'];
            $bloknya[substr($val['afdeling'],0,4)][$val['afdeling']][$val['blok']]=$val['blok'];
        }

        $query = selectQuery($dbname,'kebun_pusingan','*',"tanggal='".tanggaldb($tanggal)."'");
        $hasil = fetchData($query);
        foreach ($hasil as $v) {
            if($v['keterangan']=='P'){
                $pusingan[$v['blok']]+=1;
            }
        }
        
        #Restan Kemarin
        #SUM jjg pnn [rekap pnn] - SUM jjg[kebunspbvw] - SUM afkir [rekap pnn]

        $str=" select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir,blok,divisi from ".$dbname.".kebun_rekappnn_vw where tanggal = '".tanggaldb($tanggal)."'  and substr(divisi,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')  group by blok ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $jjgpanenkemarin[$bar['divisi']][$bar['blok']]['kemarin']=$bar['jjgpanen'];
            $jjgafkirkemarin[$bar['divisi']][$bar['blok']]['kemarin']=$bar['jjgafkir'];
            $blokrestan[$bar['divisi']][$bar['blok']]=$bar['blok'];
        }

        $str=" select sum(jjg) as jjg,indukblok,divisi from ".$dbname.".kebun_spb_vw4 where  1=1  and substr(divisi,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')  and tanggal = '".tanggaldb($tanggal)."'  group by indukblok ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $jjgspbkemarin[$bar['divisi']][$bar['indukblok']]['kemarin']=$bar['jjg'];
        }

        ####tutup ambil restan kemarin

        if ($proses == 'excel') {
            $tab .= "<table class=sortable cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
        }

        $tab .= "
        <thead>
            <tr class=rowheader>
                <th align=center colspan=12>RENCANA KERJA PANEN</th>
                <th align=center rowspan=5>TK<br>Tersedia</th>
                <th align=center rowspan=5>TDK<br>Hadir</th>
                <th align=center rowspan=1 colspan=6>TK Tidak Hadir</th>
                <th align=center rowspan=5>KET</th>
                <th align=center rowspan=5>JJG PANEN</th>
                <th align=center rowspan=5>BJR</th>
                <th align=center rowspan=5>JJG RESTAN</th>
                <th align=center colspan=2>Realisassi tglakhir</th>
                <th align=center rowspan=5>RAT ".numToMonth($bulan,'I','long')." ".$tahun."</th>
                <th align=center rowspan=5>Persentase</th>
                <th align=center rowspan=5>Variance</th>
                <th align=center rowspan=5>kekurangan / hari</th>
            </tr>
            <tr class=rowheader>
                <th align=center rowspan=4>Kebun</th>
                <th align=center rowspan=4>Afdeling</th>
                <th align=center rowspan=4>Blok</th>
                <th align=center rowspan=4>Luas (Ha)</th>
                <th align=center rowspan=4>Pusingan</th>
                <th align=center rowspan=4>AKP</th>
                <th align=center colspan=5>Jumlah Buah</th>
                <th align=center rowspan=4>Estimasi Rit</th>
                <th align=center rowspan=4>C</th>
                <th align=center rowspan=4>S1</th>
                <th align=center rowspan=4>Izin</th>
                <th align=center rowspan=4>M</th>
                <th align=center rowspan=4>B</th>
                <th align=center rowspan=4>OFF</th>
                <th align=center rowspan=4>HI</th>
                <th align=center rowspan=4>SHI</th>
            </tr>
            <tr class=rowheader>
                <th colspan=3>Restan</th>
                <th rowspan=3>Est. Hi</th>
                <th rowspan=3>Total</th>
            </tr>
            <tr class=rowheader>
                <th colspan=2>TBS</th>
                <th rowspan=2>TRUK</th>
            </tr>
            <tr class=rowheader>
                <th>Blok</th>
                <th>Kg</th>
            </tr>
        </thead>
        <tbody>";
        // ############################# TAMPILKAN DATA #####################################
        $temporg=$tempdiv='';$ttlall=0;$ttlperkat = $ttlpertgl = $ttlperorg= array();
        if(isset($afd)){
            foreach ($afd as $kbn => $arrdiv) {
                @$no++;
                $tab .="<tr class=rowcontent>";
                $tab .="<td valign=top align=center rowspan='".count($arrdiv)."'>".getNamaOrg($kbn)."</td>";
                foreach ($arrdiv as $div) {
                    @$nodiv++;
                    if(($tempdiv != $div || $tempdiv == '') && $nodiv > 1){
                        $tab .="<tr class=rowcontent><td valign=top>".getNamaOrg($div)."</td>";
                    }else if($tempdiv != $div || $tempdiv == ''){
                        $tab .="<td valign=top>".getNamaOrg($div)."</td>";
                    }
                    $tab .="<td valign=top style='width:100px'><b>";
                    foreach ($bloknya[$kbn][$div] as $blk) {
                        $nox++;
                        if($nox == 1){
                            $tab .= substr($blk,-3);
                        }else{
                            $tab .= ", ".substr($blk,-3);
                        }
                    }
                    $tab ."</b></td>";
                    $tab .="<td align=center>".number_format($ha[$div])."</td>";
                    $tab .="<td align=center>".$pusingan[$v['blok']]."</td>";
                    $tab .="<td align=right>%</td>";
                    $tab .="<td align=left>";
                    foreach ($blokrestan[$div] as $arblokk) {
                        $noy++;
                        if($noy == 1){
                            $tab.=substr(getNamaOrg($arblokk),-3);
                        }else{
                            $tab.=", ".substr(getNamaOrg($arblokk),-3);
                        }
                    }
                    $tab .="</td>";
                    $tab .="<td align=right>".number_format($kgtaksasi[$div])."</td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right>".number_format($kgtesthi[$div])."</td>";
                    $tab .="<td align=right>".number_format($kgtesthi[$div]+$kgtaksasi[$div])."</td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=center>".$tktersedia[$div]."</td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right>".number_format($kgtesthi[$div]/$bjr[$div])."</td>";
                    $tab .="<td align=right>".number_format($bjr[$div],2)."</td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    $tab .="<td align=right></td>";
                    // $tab .="<td align=center>Bulan ".numToMonth(substr($v['tanggalkontrak'],5,2),'I','long')." Tahun ".substr($v['tanggalkontrak'],0,4)."</td>";
                    // $tab .="<td align=center></td>";
                    // $tab .="<td align=center>Penjualan TBS</td>";
                    // $tab .="<td align=center>".number_format($v['kuantitaskontrak'])."</td>";
                    // $tab .="<td align=center></td>";
                    $temporg = $kbn;
                    $tempdiv = $div;
                    $tab .="</tr>";
                    @$ttlha[$kbn] += $ha[$div];
                    @$ttlkgtak[$kbn] += $kgtaksasi[$div];
                    @$ttlkgest[$kbn] += $kgtesthi[$div];
                    @$ttltk[$kbn] += $tktersedia[$div];
                    @$ttljjgpnn[$kbn] += ($kgtesthi[$div]/$bjr[$div]);
                }
                $tab .="</tr>";
            }
            $tab .="<tr class=rowcontent style='font-weight:bold'>";
            $tab .="<td valign=top align=left colspan=2></td>";
            $tab .="<td valign=top align=left>TOTAL</td>";
            $tab .="<td valign=top align=center>".number_format($ttlha[$kbn])."</td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=right>".number_format($ttlkgtak[$kbn])."</td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=right>".number_format($ttlkgest[$kbn])."</td>";
            $tab .="<td valign=top align=right>".number_format($ttlkgest[$kbn]+$ttlkgtak[$kbn])."</td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=center>".number_format($ttltk[$kbn])."</td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=right>".number_format($ttljjgpnn[$kbn])."</td>";
            $tab .="<td valign=top align=right>".number_format($ttlkgest[$kbn]/$ttljjgpnn[$kbn],2)."</td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="<td valign=top align=left></td>";
            $tab .="</tr>";
        }else{
            $tab .="<tr class=rowcontent><td align=center colspan=".(5+$jlhtgl).">".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }

        $tab .= "</tbody></table></div>";
    }

    switch ($proses) {
        ######PREVIEW
        case 'preview':
            echo $tab;
            break;
        ######EXCEL	
        case 'excel':
            $print = $tab;
            $nop_ = " Management_Report_13._Kemitraan_".getNamaOrg($pt);
            if (strlen($print) > 0) {
                $print .= "Print Time : " . date('H:i:s, d/M/Y') . "<br>By : " . $_SESSION['empl']['name'];
                if ($handle = opendir('tempExcel')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && $file != "index.html") {
                            @unlink('tempExcel/' . $file);
                        }
                    }
                    closedir($handle);
                }
                $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
                if (!fwrite($handle, $print)) {
                    echo "<script language=javascript1.2>
                            parent.window.alert('Can't convert to excel format');
                            </script>";
                    exit;
                } else {
                    echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
                }
                fclose($handle);
            }
            break;
    }

    function nantozero($e, $i = 0)
    {
        if (is_nan($e)) {
            $e = "";
        } else if (is_infinite($e)) {
            $e = "";
        } else {
            $e = $e;
        }
        $n = hidezerodecimal($e, $i);
        if ($n == 0 or $n == '') {
            $n = '';
        }

        return $n;
    }

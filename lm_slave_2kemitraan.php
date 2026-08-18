<?php
    require_once('master_validation.php');
    require_once('lib/zLib.php');

    $param      = count($_POST) > 0 ? $_POST : $_GET;
    $proses     = checkPostGet('proses', '');
    $kdorg      = checkPostGet('kdorg', '');
    $pt         = checkPostGet('pt', '');
    $divisi     = checkPostGet('divisi', '');
    $prd        = checkPostGet('prd', '');
    $prd1       = checkPostGet('prd1', '');
    $arrbi      = explode('-', $prd);
    $tahun      = $arrbi[0];
    $bulan      = $arrbi[1];
    $periode1   = $tahun . "-01";
    $jlhtgl     = substr(tglakhir($periode1),8,2);

    if($proses == 'preview' || $proses == 'excel'){
        $whrunt = $color = $color1 = $color2 = $whrdiv = $whrgnk = "";

        if ($pt == '' && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Kode PT harus di pilih.");
        }
        
        $pt_arr = explode(',', $pt);
        $pt_list = [];
        foreach($pt_arr as $p) if($p != '') $pt_list[] = "'".$p."'";
        $in_pt = " IN (".implode(',', $pt_list).")";
        // if ($kdorg == '' && ($proses == 'preview' || $proses == 'excel')) {
        //     exit("Warningsystem = Unit harus di pilih.");
        // }
        if ($prd == '' && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Periode harus di pilih.");
        }
        if ($prd1 == '' && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Periode s/d harus di pilih.");
        }
        // exit("Error:" .$prd);

        // if ($kdorg != '') {
        //     $whrunt = " and unit ='" . $kdorg . "'";
        // }

        // if($divisi != ''){
        //     $whrdiv = " and subbagian ='".$divisi."' ";
        // }

        $arrkateg = array('janjangpanen','brondol','bjr','outputpanen','lebihbasis','produktifitas1','produktifitas2','premibrondol','denda');

        // Ambil Data
        $str = "select * from " . $dbname . ".pmn_kontrakjual a where kodept ".$in_pt." and left(IF(tanggalberlaku='0000-00-00', sdtanggal, tanggalberlaku),7) between '".$prd."' and '".$prd1."'";
        // exit('warning:'.$str);
        $hsl = fetchData($str);

        if ($proses == 'excel') {
            $tab .= "<table class=sortable cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
        }

        $tab .= "
        <thead>
            <tr class=rowheader>
                <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
                <th align=center>" . $_SESSION['lang']['nama'] . " Koperasi / Kelompok Tani</th>
                <th align=center>" . $_SESSION['lang']['tanggal'] . " Dimulai Kemitraan</th>
                <th align=center>" . $_SESSION['lang']['jumlah'] . " Anggota<br>(Orang)</th>
                <th align=center>Jenis Kegiatan yang dimitrakan</th>
                <th align=center>Target Volume Perusahaan yang Dimitrakan</th>
                <th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
            </tr>
            <tr class=rowheader>";
                for ($i=1; $i <= 7 ; $i++) { 
                    $tab .="<th align=center>".$i."</th>";
                }
            $tab.="
            </tr>
        </thead>
        <tbody>";
        // ############################# TAMPILKAN DATA #####################################
        $tempkar=$ttanam='';$ttlall=0;$ttlperkat = $ttlpertgl = $ttlperorg= array();
        if(isset($hsl)){
            foreach ($hsl as $v) {
                @$no++;
                $tab .="<tr class=rowcontent>";
                $tab .="<td valign=top align=center>{$no}</td>";
                $tab .="<td valign=top><b>".getNamaCustomer($v['koderekanan'])."</b></td>";
                $tab .="<td valign=top align=center>Bulan ".numToMonth(substr($v['tanggalkontrak'],5,2),'I','long')." Tahun ".substr($v['tanggalkontrak'],0,4)."</td>";
                $tab .="<td valign=top align=center></td>";
                $tab .="<td valign=top align=center>Penjualan TBS</td>";
                $tab .="<td valign=top align=center>".number_format($v['kuantitaskontrak'])."</td>";
                $tab .="<td valign=top align=center></td>";
            }
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
            $nop_ = " Management_Report_13._Kemitraan_".str_replace(',','_',$pt);
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

<?php
    require_once('master_validation.php');
    require_once('lib/zLib.php');

    $param      = count($_POST) > 0 ? $_POST : $_GET;
    $proses     = checkPostGet('proses', '');
    $kdorg      = checkPostGet('kdorg', '');
    $pt         = checkPostGet('pt', '');
    $divisi     = checkPostGet('divisi', '');
    $prd        = checkPostGet('prd', '');
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
        $str = "select *,sum(rupiah) as rupiahgroup from " . $dbname . ".lgl_bansos where kodeorg  in (SELECT kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."') and tanggal like '".$prd."%' group by tujuan";
        $hsl = fetchData($str);
        foreach ($hsl as $h) {
            $datanya[$h['tujuan']]=$h['tujuan'];
            $frekuensi[$h['tujuan']][substr($h['tanggal'],0,7)] +=1;
            $biaya[$h['tujuan']][substr($h['tanggal'],0,7)] +=$h['rupiahgroup'];
        }
        if ($proses == 'excel') {
            $tab .= "<table class=sortable cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1>";
        }

        $tab .= "
        <thead>
            <tr class=rowheader>
                <th align=center rowspan=3>" . $_SESSION['lang']['nourut'] . "</th>
                <th align=center rowspan=3>Bidang & Jenis Kegiatan</th>
                <th align=center rowspan=3>Bentuk<br>Partisipasi</th>
                <th align=center colspan=14>Volume / Frekuensi</th>
                <th align=center colspan=13>Biaya yang dikeluarkan</th>
            </tr>
            <tr class=rowheader>
                <th align=center rowspan=2>Satuan</th>
                <th align=center colspan=12>" . $_SESSION['lang']['bulan'] . "</th>
                <th align=center rowspan=2>Jumlah</th>
                <th align=center colspan=12>" . $_SESSION['lang']['bulan'] . "</th>
                <th align=center rowspan=2>Jumlah</th>
            </tr>
            <tr class=rowheader>";
            for ($ii=1; $ii <= 12; $ii++) { 
                $tab .="<th align=center>".$ii."</th>";
            }
            for ($ii=1; $ii <= 12; $ii++) { 
                $tab .="<th align=center>".$ii."</th>";
            }
            $tab.="
            </tr>
        </thead>
        <tbody>";
        // ############################# TAMPILKAN DATA #####################################
        $tempkar=$ttanam='';$ttlall=$ttlbyyall=0;$ttlperkat = $ttlpertgl = $ttlperorg= $ttlbyy= $ttlbyyperbln= $ttlfrek= array();
        if(count($hsl) > 0){
		    $nmkategori=makeOption($dbname,'lgl_kategoribansos','kode,nama');
            foreach ($hsl as $v) {
                @$no++;
                $tab .="<tr class=rowcontent>";
                $tab .="<td valign=top align=center>{$no}</td>";
                $tab .="<td valign=top><b>".$v['tujuan']."</b></td>";
                $tab .="<td valign=top align=center>".$nmkategori[$v['kategori']]."</td>";
                $tab .="<td valign=top align=center>".$v['satuan']."</td>";
                for ($e=1; $e <=12 ; $e++) { 
                    $tab .="<td valign=top align=center>".number_format($frekuensi[$v['tujuan']][$prd."-".addZero($e,2)])."</td>";
                    $ttlfrek[$v['tujuan']] += $frekuensi[$v['tujuan']][$prd."-".addZero($e,2)];
                }
                $tab .="<td valign=top align=center>".number_format($ttlfrek[$v['tujuan']])."</td>";
                for ($e=1; $e <=12 ; $e++) { 
                    $tab .="<td valign=top align=right>".number_format($biaya[$v['tujuan']][$prd."-".addZero($e,2)])."</td>";
                    $ttlbyy[$v['tujuan']] += $biaya[$v['tujuan']][$prd."-".addZero($e,2)];
                    $ttlbyyperbln[$prd."-".addZero($e,2)] += $biaya[$v['tujuan']][$prd."-".addZero($e,2)];
                }
                $tab .="<td valign=top align=right>".number_format($ttlbyy[$v['tujuan']])."</td>";
                $tab .="</tr>";
            }
            $tab .="<tr class=rowcontent>";
            $tab .="<td valign=top align=right colspan=17><b>".$_SESSION['lang']['total']."</b></td>";
            for ($e=1; $e <=12 ; $e++) { 
                $tab .="<td valign=top align=right><b>".number_format($ttlbyyperbln[$prd."-".addZero($e,2)])."</b></td>";
                $ttlbyyall += $ttlbyyperbln[$prd."-".addZero($e,2)];
            }
            $tab .="<td valign=top align=right><b>".number_format($ttlbyyall)."</b></td>";
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
            $nop_ = "Management_Report_13._Kegiatan_".getNamaOrg($pt)."_".$prd;
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

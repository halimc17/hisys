<?php
    require_once('master_validation.php');
    require_once('lib/zLib.php');

    $param      = count($_POST) > 0 ? $_POST : $_GET;
    $proses     = checkPostGet('proses', '');
    $pt         = checkPostGet('pt', '');
    $prd        = checkPostGet('prd', '');
    $arrbi      = explode('-', $prd);
    $tahun      = $arrbi[0];
    $bulan      = $arrbi[1];
    $periode1   = $tahun . "-01";
    $jlhtgl     = substr(tglakhir($periode1),8,2);

    if($proses == 'preview' || $proses == 'excel'){
        $whrunt = $color = $color1 = $color2 = $whrdiv = $whrgnk = "";$arrdatakary = array();

        if ($pt == '' && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Kode PT harus di pilih.");
        }
        if ($prd == ''  && ($proses == 'preview' || $proses == 'excel')) {
            exit("Warningsystem = Periode harus di pilih.");
        }

        // Ambil Data
        $str = "select * from " . $dbname . ".datakaryawan_hist where periodegaji ='".$prd."' and kodeorganisasi = '".$pt."'  and (tanggalkeluar>='" . tglakhir($prd."-01") . "' or tanggalkeluar='0000-00-00') and alokasi=0 order by tipekaryawan,kodejabatan";
        $hsl = fetchData($str);
        if(count($hsl)<1){
            $str = "select * from " . $dbname . ".datakaryawan where kodeorganisasi ='".$pt."'  and (tanggalkeluar>='" . tglakhir($prd."-01") . "' or tanggalkeluar='0000-00-00') and alokasi=0 order by tipekaryawan,kodejabatan";
            $hsl = fetchData($str);
        }

        if ($proses == 'excel') {
            $tab .= "<table class=sortable cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
        }
        $tab .= "
        <thead>
            <tr class=rowheader>
                <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                <th align=center rowspan=2>Lokasi/Divisi</th>
                <th align=center rowspan=2>Golongan</th>
                <th align=center rowspan=22>Status</th>
                <th align=center rowspan=2>Bagian</th>
                <th align=center rowspan=2>Jabatan</th>
                <th align=center colspan=2>Mulai Bekerja</th>
                <th align=center colspan=2>Divisi</th>
                <th align=center rowspan=2>Divisi/Bagian sebelumnya</th>
            </tr>
            <tr class=rowheader>
                <th align=center>Mulai</th>
                <th align=center>Lama</th>
                <th align=center>Mulai</th>
                <th align=center>Lama</th>
            </tr>
        </thead>
        <tbody>";
        // ############################# TAMPILKAN DATA #####################################
        $tempbrg=$tempbrg2='';$ttlbyyall=0;$ttlbyyperbln= array();
        if(count($hsl) > 0){
		    $nmklp=makeOption($dbname,'log_5klbarang','kode,kelompok');
		    $nmsubklp=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok');
            foreach ($hsl as $v) {
                @$no++;
                $tab .="<tr class=rowcontent>";
                $tab .="<td valign=top align=center style='padding-left:5px;'>".$no."</td>";
                $tab .="<td valign=top>".getNamaKaryawan($v['karyawanid'])."</td>";
                $tab .="<td valign=top align=center>".getNamaGolongan($v['kodegolongan'])."</td>";
                $tab .="<td valign=top align=center>".$v['statuspajak']."</td>";
                $tab .="<td valign=top align=center>".getNamaDept($v['bagian'])."</td>";
                $tab .="<td valign=top align=center>".getNamaJabatan($v['kodejabatan'])."</td>";
                $tab .="<td valign=top align=center>".$v['tanggalmasuk']."</td>";
                $tab .="<td valign=top align=center>".hitungLamaKerja($v['tanggalmasuk'])."</td>";
                $tab .="<td valign=top align=center>".$v['tanggalmasuk']."</td>";
                $tab .="<td valign=top align=center>".hitungLamaKerja($v['tanggalmasuk'])."</td>";
                $tab .="<td valign=top align=center></td>";
                $tab .="</tr>";
            }
        }else{
            $tab .="<tr class=rowcontent><td align=center colspan=".((12*4)).">".$_SESSION['lang']['errdatanotexist']."</td></tr>";
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
            $nop_ = "Management_Report_13._SDM_DaftarKaryawan_".getNamaOrg($pt)."_".$prd;
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
    function hitungLamaKerja($tanggalMasuk) {
        // Buat objek DateTime dari tanggal masuk
        $masuk = new DateTime($tanggalMasuk);
        // Ambil tanggal hari ini
        $hariIni = new DateTime();
        
        // Hitung selisih
        $selisih = $masuk->diff($hariIni);
        
        // Format hasil: tahun, bulan, hari
        return $selisih->y . " tahun, " . $selisih->m . " bulan, " . $selisih->d . " hari";
    }

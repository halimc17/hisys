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

		## GET VALIDASI BUDGET
		$arrvalbudget = validasibudget($tipe, $rkd_bag);

		## GET JUMLAH BUDGET

		$arrqtybudget = $arrbudgetsdbi = array();
		$crtahun = explode('-', $prd."-01");

		$e = "(";
		$s = "(";
		for ($i = 1; $i <= intval($crtahun[1]); $i++) {
			$r = "rp" . addZero($i, 2);
			$n = "k" . addZero($i, 2);
			if ($i < intval($crtahun[1])) {
				$e .= $r . "+";
				$s .= $n . "+";
			} else {
				$e .= $r;
				$s .= $n;
			}
		}
		$e .= ")";
		$s .= ")";

        $arrbudgetsdbi = array();
		
		$strx = "select kodebarang, sum(rupiah) as jumlah, " . $e . " as sdbi from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci where a.kodeorg  in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')  and a.tahunbudget='" . $crtahun[0] . "' and a.kodebarang not like '9%' and (a.pta='BGT' or (a.pta='PTA' and a.statuspta='0')) and a.kodebarang!='' group by a.kodebarang";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah'];
			// $arrbudgetsdbi[substr($val['kodebarang'],0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
			$arrbudgetsdbi[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
		}

		#kapital
		$strx = "select kodebarang, hargatotal as jumlah, " . $s . " as sdbi from " . $dbname . ".bgt_kapital where kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')  and tahunbudget='" . $crtahun[0] . "' and kodebarang!='' and (pta='BGT' or (pta='PTA' and statuspta='1'))";
		$resx = fetchdata($strx);
		foreach ($resx as $val) {
			$arrqtybudget[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah'];
			// $arrbudgetsdbi[substr($val['kodebarang'],0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
			$arrbudgetsdbi[substr($val['kodebarang'], 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
		}

        # apakah SR ini sebelumnya sudah ada nomor akunnya ???
        $datalama = array();
        if ($param['noakun'] != "undefined" and $param['kodebarang'] != 'undefined') {
            $datalama[$param['noakun']][$param['kodebarang']] = $param['kodebarang'];
        }

        $sql = "select distinct noakunbudget, kodebarang from " . $dbname . ".log_prapodt where nopp like '%" . $crtahun[2] . "%" . $rkd_bag . "%' and noakunbudget!=''";
        $req = fetchdata($sql);
        foreach ($req as $bar) {
            $akunprlama[$bar['kodebarang']] = $bar['noakunbudget'];
            $datalama[$bar['noakunbudget']][$bar['kodebarang']] = $bar['kodebarang'];
        }

        $strx = "select a.noakun, sum(rupiah) as jumlah, " . $e . " as sdbi from " . $dbname . ".bgt_budget a left join " . $dbname . ".bgt_distribusi b on a.kunci=b.kunci where a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk ='".$pt."')  " . $whr . " and a.tahunbudget='" . $crtahun[0] . "' and (a.pta='BGT' or (a.pta='PTA' and a.statuspta='1')) group by a.noakun";
        $resx = fetchdata($strx);
        foreach ($resx as $val) {
            foreach ($datalama as $akun => $v) {
                foreach ($v as $kodbarang) {
                    if ($val['noakun'] == $akun) {
                        $arrqtybudget[substr($kodbarang, 0, $arrvalbudget['digit'])] += $val['jumlah'];
                        // $arrbudgetsdbi[substr($kodbarang,0,$arrvalbudget['digit'])]+=$val['sdbi']; #jika pake sebaran
                        $arrbudgetsdbi[substr($kodbarang, 0, $arrvalbudget['digit'])] += $val['jumlah']; #jika pake total setahun
                    }
                }
            }
        }

        // Ambil Data
        $str = "select * from " . $dbname . ".log_5saldobulanan where kodeorg ='".$pt."' and periode <= '".$prd."' and (SUBSTRING(kodebarang, 1, 3) IN ('311','312','351'))";
        $hsl = fetchData($str);
        foreach ($hsl as $h) {
            $databrgnya[$h['kodebarang']]=$h['kodebarang'];
            $actqty[$h['kodebarang']][$h['periode']] +=$h['saldoakhirqty'];
            if($h['periode']==$prd){
                $actbiini[$h['kodebarang']] +=$h['nilaisaldoakhir'];
            }
            if($h['periode']<=$prd){
                $actsdbi[$h['kodebarang']] +=$h['nilaisaldoakhir'];
                $actqtysdbii[$h['kodebarang']] +=$h['saldoakhirqty'];
            }
        }

        // Ambil Data
        $str = "select * from " . $dbname . ".log_transaksi_vw where kodept ='".$pt."' and tanggal <= '".tglakhir($prd."-01")."' and (SUBSTRING(kodebarang, 1, 3) IN ('311','312','351')) and post='1'";//PATOK YA KARENA DARI LAPORAN CONTOHNYA CUMA KELOMPOK ITU AJAH
        $hsl = fetchData($str);
        foreach ($hsl as $h) {
            $penggunaanperbln[$h['kodebarang']][substr($h['tanggal'],0,7)] +=$h['hartot'];
        }

        if ($proses == 'excel') {
            $tab .= "<table class=sortable cellspacing=1 border=1>";
        } else {
            $tab = "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
        }
        $tab .= "
        <thead>
            <tr class=rowheader>
                <th align=center rowspan=3 style='width:20%'>" . $_SESSION['lang']['deskripsi'] . "</th>
                <th align=center rowspan=3>Satuan</th>
                <th align=center colspan=6>PERSEDIAAN</th>
                <th align=center colspan=12>PENGGUNAAN</th>
                <th align=center rowspan=3>Jumlah</th>
                <th align=center colspan=12>RAT ".substr($prd,0,4)."</th>
                <th align=center rowspan=3>SBI</th>
                <th align=center rowspan=3>Jumlah</th>
                <th align=center colspan=12>STOCK  (PERSEDIAAN AKHIR)</th>
                <th align=center rowspan=3>Saldo Akhir</th>
            </tr>
            <tr class=rowheader>
                <th align=center colspan=2>BULAN INI</th>
                <th align=center colspan=2>S/D BULAN INI</th>
                <th align=center rowspan=2>Stock per Januari ".substr($prd,0,4)."</th>
                <th align=center rowspan=2>RAT ".substr(tglkemarin(substr($prd,0,4)."-01-01"),0,4)."</th>";
                for ($ii=1; $ii <= 12; $ii++) { 
                    $tab .="<th align=center rowspan=2>".numToMonth($ii,'I','short')."</th>";
                }
                for ($iii=1; $iii <= 12; $iii++) { 
                    $tab .="<th align=center rowspan=2>".numToMonth($iii,'I','short')."</th>";
                }
                for ($iv=1; $iv <= 12; $iv++) { 
                    $tab .="<th align=center rowspan=2>".numToMonth($iv,'I','short')."</th>";
                }
            $tab.="
            </tr>
            <tr class=rowheader>
                <th align=center>AKTUAL</th>
                <th align=center>RAT</th>
                <th align=center>AKTUAL</th>
                <th align=center>RAT</th>
            </tr>
        </thead>
        <tbody>";
        // ############################# TAMPILKAN DATA #####################################
        $tempbrg=$tempbrg2='';$ttlbyyall=0;$ttlbyyperbln= array();
        if(count($databrgnya) > 0){
		    $nmklp=makeOption($dbname,'log_5klbarang','kode,kelompok');
		    $nmsubklp=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok');
            foreach ($databrgnya as $v) {
                @$no++;
                if($tempbrg == '' || $tempbrg != substr($v,0,3)){
                    $tab .="<tr class=rowcontent>";
                    $tab .="<td valign=top colspan=".(12*4)."><b>".$nmklp[substr($v,0,3)]."</b></td>";
                    $tab .="</tr>";
                    if($tempbrg2 == '' || $tempbrg2 != substr($v,0,5)){
                        $tab .="<tr class=rowcontent>";
                        $tab .="<td valign=top colspan=".(12*4)." style='padding-left:10px'><b>".$nmklp[substr($v,0,3)]." - ".$nmsubklp[substr($v,0,5)]."</b></td>";
                        $tab .="</tr>";
                        $tempbrg2 =  substr($v,0,5);
                    }
                }
                $tab .="<tr class=rowcontent>";
                $tab .="<td valign=top style='padding-left:10px;'>".getNamaBrg($v)."</td>";
                $tab .="<td valign=top align=center>".getSatBrg($v)."</td>";
                $tab .="<td valign=top align=right>".number_format($actbiini[$v])."</td>";
                $tab .="<td valign=top align=right>".number_format($arrbudgetsdbi[substr($v,0,3)])."</td>";
                $tab .="<td valign=top align=right>".number_format($actsdbi[$v])."</td>";
                $tab .="<td valign=top align=right>".number_format($arrbudgetsdbi[substr($v,0,3)])."</td>";
                $tab .="<td valign=top align=right>".number_format($arrbudgetsdbi[substr($v,0,3)])."</td>";
                $tab .="<td valign=top align=right>".number_format($arrbudgetsdbi[substr($v,0,3)])."</td>";
                for ($e=1; $e <=12 ; $e++) { 
                    $tab .="<td valign=top align=center>".number_format($penggunaanperbln[$v][substr($prd,0,4)."-".addZero($e,2)])."</td>";
                    $ttlperbln[$v] += $penggunaanperbln[$v][substr($prd,0,4)."-".addZero($e,2)];
                }
                $tab .="<td valign=top align=center>".number_format($ttlperbln[$v])."</td>";
                for ($e=1; $e <=12 ; $e++) { 
                    $tab .="<td valign=top align=center>".number_format($penggunaanperbln[$v][substr($prd,0,4)."-".addZero($e,2)])."</td>";
                    $ttlperblnrat[$v] += $penggunaanperbln[$v][substr($prd,0,4)."-".addZero($e,2)];
                }
                $tab .="<td valign=top align=center>".number_format($arrbudgetsdbi[substr($v,0,3)])."</td>";
                $tab .="<td valign=top align=center>".number_format($ttlperblnrat[$v])."</td>";
                for ($e=1; $e <=12 ; $e++) { 
                    $tab .="<td valign=top align=center>".number_format($actqty[$v][substr($prd,0,4)."-".addZero($e,2)])."</td>";
                }
                $tab .="<td valign=top align=center>".number_format($actqty[$v][$prd])."</td>";
                $tab .="</tr>";
                $tempbrg = substr($v,0,3);
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
            $nop_ = "Management_Report_13._Persediaan_".getNamaOrg($pt)."_".$prd;
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

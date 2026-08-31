<?php
// file creator: dhyaz sep 20, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

switch ($_POST['aksi']){


    case'getAfd';
        $whstblok = "";
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";
        if($_POST['thntnm']!='') $whstblok .= " AND tahuntanam='{$_POST['thntnm']}'";

        $ambilInduk=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$_POST['kodeorg']."'");
        $ambilInduk->setFetchMode(PDO::FETCH_OBJ);
        $induk='';
        while($bar=$ambilInduk->fetch())
        {
            $induk=$bar->induk;
        }

        // $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		echo "<table width='80%'>
			<tr>
				<td>
					<label style='cursor:pointer;color:blue' onclick=\"selectall()\">Select All</label>
					&nbsp&nbsp&nbsp&nbsp
					<label style='cursor:pointer;color:blue' onclick=\"unselectall()\">Unselect All</label>
				</td>
			</tr>
			<tr>
				<td>";
        
        # Cek Divisi
            $sql = "SELECT DISTINCT LEFT(kodeorg,6) AS divisi FROM {$dbname}.setup_blok WHERE 5=5 {$whstblok} AND kodeorg LIKE '{$_POST['kodeorg']}%' AND luasareaproduktif>0";
            // echo $sql."<br/>";
            $res = fetchData($sql);
            foreach($res as $row):
                $arrdiv[$row['divisi']] = $row['divisi'];
            endforeach;

            if(count($arrdiv) > 0) {
                $whstnew = " AND kodeorganisasi IN ('".implode("','",$arrdiv)."')";
            }
        # End
        // echo "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_POST['alokasi']."' and tipe='AFDELING' {$whstnew}";
        $iAfd=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_POST['alokasi']."' and tipe='AFDELING' {$whstnew}");
        $iAfd->setFetchMode(PDO::FETCH_ASSOC);
        while($dAfd= $iAfd->fetch())
        {
			echo "<li style='float:left;width:200px;list-style-type:none'>
				<input type='checkbox' id='chkAfd' name='chkAfd[]' value='".$dAfd['kodeorganisasi']."' checked />".$dAfd['namaorganisasi']."</li>";
            // $optAfd.="<option value='".$dAfd['kodeorganisasi']."'>".$dAfd['namaorganisasi']."</option>";
        }
		echo"</td></tr></table>";
        // echo $optAfd;
    break;

    case 'ambilnokas':
        // kodejurnal='M' exact match: hindari ikut tertangkap M0/M9/M10/M11 (operasional posting/borongan/justifikasi biaya) yang juga diawali huruf M
        $str=$owlPDO->query("select nojurnal as notransaksi, kodeorg, sum(jumlah) as jumlah from "
                . "".$dbname.".keu_jurnaldt_vw where tanggal=".tanggalsystem($_POST['tanggal'])." and kodejurnal='M' and kodeorg in (".getOrgDetail(2).") and jumlah > 0 "
                . "group by nojurnal, kodeorg");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $opt="<option value=''>Pilih....</option>";
        while($bar= $str->fetch())
        {
            $opt.="<option value='".$bar->notransaksi."#".$bar->jumlah."#".$bar->kodeorg."'>".$bar->kodeorg.": ".$bar->notransaksi." jumlah ".number_format($bar->jumlah)."</option>";
        }
        echo $opt;
    break;

    case 'detailJurnal':
        if($_SESSION['language']=='EN'){
            $kolnama = "a.namaakun1 as namaakun";
        }else{
            $kolnama = "a.namaakun";
        }
        $str=$owlPDO->query("select d.noakun,{$kolnama},d.keterangan,d.jumlah from ".$dbname.".keu_jurnaldt d "
                . "left join ".$dbname.".keu_5akun a on d.noakun=a.noakun "
                . "where d.nojurnal='".$_POST['nojurnal']."' order by d.nourut");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $tab="<table class=sortable border=0 cellspacing=1 cellpadding=3>
                <thead><tr class=rowheader>
                    <td>No Akun</td>
                    <td>Nama Akun</td>
                    <td>".$_SESSION['lang']['keterangan']."</td>
                    <td>".$_SESSION['lang']['debet']."</td>
                    <td>".$_SESSION['lang']['kredit']."</td>
                </tr></thead><tbody>";
        while($bar=$str->fetch())
        {
            $debet = $bar->jumlah>0 ? number_format($bar->jumlah) : '';
            $kredit = $bar->jumlah<0 ? number_format(abs($bar->jumlah)) : '';
            $tab.="<tr class=rowcontent>
                    <td>".$bar->noakun."</td>
                    <td>".$bar->namaakun."</td>
                    <td>".$bar->keterangan."</td>
                    <td align=right>".$debet."</td>
                    <td align=right>".$kredit."</td>
                    </tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;

    case 'detailIDC':
        #scope sama persis dengan yang dipakai listIDC, supaya kalau nojurnal muncul di list, detailnya boleh dilihat
        $cek=$owlPDO->query("select nojurnal from ".$dbname.".keu_jurnalht where nojurnal='".$_POST['nojurnal']."' and kodejurnal='IDC' and substr(nojurnal,10,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')");
        $cek->setFetchMode(PDO::FETCH_OBJ);
        if(!$cek->fetch()){
            exit("Error: Jurnal ".$_POST['nojurnal']." tidak ditemukan atau di luar wewenang Anda");
        }

        if($_SESSION['language']=='EN'){
            $kolnama = "a.namaakun1 as namaakun";
        }else{
            $kolnama = "a.namaakun";
        }
        $dt=$owlPDO->query("select d.noakun,{$kolnama},d.keterangan,d.jumlah,d.kodeblok from ".$dbname.".keu_jurnaldt d "
                . "left join ".$dbname.".keu_5akun a on d.noakun=a.noakun "
                . "where d.nojurnal='".$_POST['nojurnal']."' order by d.nourut");
        $dt->setFetchMode(PDO::FETCH_OBJ);

        if($_POST['mode']=='excel'){
            $tab="<table border=1>
                    <tr><td colspan=6><b>".$_POST['nojurnal']."</b></td></tr>
                    <tr><td colspan=6></td></tr>
                    <tr><td><b>No Akun</b></td><td><b>Nama Akun</b></td><td><b>".$_SESSION['lang']['blok']."</b></td><td><b>".$_SESSION['lang']['keterangan']."</b></td><td><b>".$_SESSION['lang']['debet']."</b></td><td><b>".$_SESSION['lang']['kredit']."</b></td></tr>";
            $totDebet=0; $totKredit=0;
            while($bar=$dt->fetch()){
                $debet = $bar->jumlah>0 ? number_format($bar->jumlah) : '';
                $kredit = $bar->jumlah<0 ? number_format(abs($bar->jumlah)) : '';
                $totDebet+= $bar->jumlah>0 ? $bar->jumlah : 0;
                $totKredit+= $bar->jumlah<0 ? abs($bar->jumlah) : 0;
                $tab.="<tr><td>".$bar->noakun."</td><td>".$bar->namaakun."</td><td>".$bar->kodeblok."</td><td>".$bar->keterangan."</td><td>".$debet."</td><td>".$kredit."</td></tr>";
            }
            $tab.="<tr><td colspan=4><b>".$_SESSION['lang']['grandtotal']."</b></td><td><b>".number_format($totDebet)."</b></td><td><b>".number_format($totKredit)."</b></td></tr>";
            $tab.="</table>";
        }else{
            $tab="<table class=sortable border=0 cellspacing=1 cellpadding=3>
                    <thead><tr class=rowheader>
                        <td>No Akun</td>
                        <td>Nama Akun</td>
                        <td>".$_SESSION['lang']['blok']."</td>
                        <td>".$_SESSION['lang']['keterangan']."</td>
                        <td>".$_SESSION['lang']['debet']."</td>
                        <td>".$_SESSION['lang']['kredit']."</td>
                    </tr></thead><tbody>";
            $totDebet=0; $totKredit=0;
            while($bar=$dt->fetch()){
                $debet = $bar->jumlah>0 ? number_format($bar->jumlah) : '';
                $kredit = $bar->jumlah<0 ? number_format(abs($bar->jumlah)) : '';
                $totDebet+= $bar->jumlah>0 ? $bar->jumlah : 0;
                $totKredit+= $bar->jumlah<0 ? abs($bar->jumlah) : 0;
                $tab.="<tr class=rowcontent>
                        <td>".$bar->noakun."</td>
                        <td>".$bar->namaakun."</td>
                        <td>".$bar->kodeblok."</td>
                        <td>".$bar->keterangan."</td>
                        <td align=right>".$debet."</td>
                        <td align=right>".$kredit."</td>
                        </tr>";
            }
            $tab.="<tr class=rowheader><td colspan=4 align=right><b>".$_SESSION['lang']['grandtotal']."</b></td><td align=right><b>".number_format($totDebet)."</b></td><td align=right><b>".number_format($totKredit)."</b></td></tr>";
            $tab.="</tbody></table>";
        }
        echo $tab;
    break;

    case 'listIDC':
        #PENTING: filter pakai h.kodejurnal='IDC' (kolom terindex), JANGAN nojurnal like '%/IDC/%'
        #karena LIKE dengan wildcard di depan bikin MySQL full-scan keu_jurnalht (jutaan baris, terbukti ~11 detik vs ~10ms)
        $wh="";
        if($_POST['notransaksisch']!='') $wh.=" AND h.nojurnal like '%".$_POST['notransaksisch']."%'";
        if($_POST['tglmulaisch']!='') $wh.=" AND h.tanggal>='".tanggalsystemn($_POST['tglmulaisch'])."'";
        if($_POST['tglselesaisch']!='') $wh.=" AND h.tanggal<='".tanggalsystemn($_POST['tglselesaisch'])."'";
        if($_POST['unitsch']!='') $wh.=" AND d.kodeorg='".$_POST['unitsch']."'";

        #siapa & kapan sudah tertulis di keu_jurnaldt.keterangan (baris kredit, nourut=1) sejak proses simpanIDC,
        #jadi cukup ambil dari sana, tidak perlu kolom baru ataupun gantung ke jurnal memorial sumbernya
        $str=$owlPDO->query("select h.nojurnal,h.tanggal,h.tanggalentry,h.totaldebet as jumlah,h.noreferensi,
                d.kodeorg,o.namaorganisasi,d.keterangan
                from ".$dbname.".keu_jurnalht h
                inner join ".$dbname.".keu_jurnaldt d on d.nojurnal=h.nojurnal and d.nourut=1
                left join ".$dbname.".organisasi o on o.kodeorganisasi=d.kodeorg
                where h.kodejurnal='IDC' and d.kodeorg in( select kodeorganisasi from ".$dbname.".organisasi
                where induk='".$_SESSION['empl']['kodeorganisasi']."') {$wh} order by h.tanggal desc");
        $str->setFetchMode(PDO::FETCH_OBJ);

        #ambil periode yang masih terbuka per unit, dipakai buat nentuin tombol hapus muncul atau tidak
        $openPeriod=array();
        $pRes=$owlPDO->query("select kodeorg,periode from ".$dbname.".setup_periodeakuntansi where tutupbuku=0");
        $pRes->setFetchMode(PDO::FETCH_ASSOC);
        while($pBar=$pRes->fetch()){
            $openPeriod[$pBar['kodeorg']][$pBar['periode']]=true;
        }

        $tab="";
        $no=0;
        $totJumlah=0;
        while($bar=$str->fetch())
        {  $no+=1;
            $totJumlah+=$bar->jumlah;
            $periode=substr($bar->tanggal,0,7);
            $bisaHapus=isset($openPeriod[$bar->kodeorg][$periode]);
            $tab.="<tr class=rowcontent><td>".$no."</td><td>".$bar->nojurnal."</td>
            <td>".$bar->kodeorg." - ".$bar->namaorganisasi."</td>
            <td>".tanggalnormal($bar->tanggal)."</td>
            <td>".tanggalnormal($bar->tanggalentry)."</td>
            <td>".$bar->noreferensi."</td>
            <td>".$bar->keterangan."</td>
            <td align=right>".number_format($bar->jumlah)."</td>
            <td>
                <button onclick=\"previewIDC('".$bar->nojurnal."',event)\">".$_SESSION['lang']['preview']."</button>
                <button onclick=\"excelIDC('".$bar->nojurnal."')\">Excel</button>";
            if($bisaHapus){
                $tab.="<button onclick=hapusIni('".$bar->nojurnal."','".$bar->tanggal."','".$bar->kodeorg."')>".$_SESSION['lang']['delete']."</button>";
            }
            $tab.="</td></tr>";
        }
        if($no==0){
            $tab="<tr class=rowcontent><td colspan=9 align=center>".$_SESSION['lang']['datanotfound']."</td></tr>";
        }else{
            $tab.="<tr class=rowcontent><td colspan=7 align=right><b>".$_SESSION['lang']['grandtotal']."</b></td><td align=right><b>".number_format($totJumlah)."</b></td><td></td></tr>";
        }
        echo $tab;
    break;

    case 'ambilTahunTanam':
        $whstblok = "";
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";
        $opt = "<option value=''>".$_SESSION['lang']['all']."</option>";
        $sql = "SELECT DISTINCT tahuntanam FROM {$dbname}.setup_blok WHERE 5=5 {$whstblok} AND kodeorg LIKE '{$_POST['kodeorg']}%' AND luasareaproduktif>0 AND tahuntanam IS NOT NULL AND tahuntanam<>'' ORDER BY tahuntanam DESC";
        $res = fetchData($sql,"OBJECT");
        foreach($res as $row):
            $opt .= "<option value='{$row->tahuntanam}'>{$row->tahuntanam}</option>";
        endforeach;
        echo $opt;
    break;

    case 'ambilTipeAlokasi':
        $ketblok = [
            "BBT" => "Bibitan",
            "LC" => "Land Clearing",
            "TB" => "Tanam Baru",
            "TBM" => "Tanaman Belum Menghasilkan",
            "TM" => "Tanaman Menghasilkan"
        ];
        $opt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

        $sql = "SELECT DISTINCT statusblok FROM {$dbname}.setup_blok WHERE 5=5 AND statusblok NOT IN ('TB','BBT') AND kodeorg LIKE '{$_POST['kodeorg']}%'";
        $res = fetchData($sql,"OBJECT");
        
        foreach($res as $row):
            $text = "[".$row->statusblok."] - ".$ketblok[$row->statusblok]."";

            $opt .= "<option value='{$row->statusblok}'>{$text}</option>";
        endforeach;

        echo $opt;
    break;

    case 'ambilAlokasi':

        $whstblok = "";
        if($_POST['stblok']!='') $whstblok .= " AND a.statusblok='{$_POST['stblok']}'";
        if($_POST['thntnm']!='') $whstblok .= " AND a.tahuntanam='{$_POST['thntnm']}'";

        $ambilInduk=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$_POST['kodeorg']."'");
        $ambilInduk->setFetchMode(PDO::FETCH_OBJ);
        $induk='';
        while($bar=$ambilInduk->fetch())
        {
            $induk=$bar->induk;
        }

        if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe') == 'KEBUN') {
            $whstblok .= " AND LEFT(a.kodeorg,4)='{$_POST['kodeorg']}'";
        } else if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe') == 'PABRIK') {
            $whstblok .= " AND LEFT(a.kodeorg,4)='{$_POST['kodeorg']}'";
        } else {
            $whstblok .= " AND LEFT(b.induk,4) IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE induk='{$induk}')";
        }
        
        $str="select distinct left(a.kodeorg,4) as kebun from ".$dbname.".setup_blok a
                  left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
                  where /* a.statusblok in('TB','TBM','LC','TBM1','TBM2','TBM3')
                  and */ 5=5 {$whstblok} AND a.luasareaproduktif>0";
		$res=fetchdata($str);
		$num=count($res);
        $opt="<option value=''>Choose....</option>";
		foreach($res as $key=>$val){
			$opt.="<option value='".$val['kebun']."'>".$val['kebun']."</option>";
		}
        // while($bar= $str->fetch())
        // {
            // $opt.="<option value='".$bar->kebun."'>".$bar->kebun."</option>";
        // }
		
		if($num<1){
			exit("Error:Tidak ada blok blok TB,TBM,LC,TBM1,TBM2,TBM3 di unit ".$_POST['kodeorg']);			
		}
		
        echo $opt;      
    break;

    case 'ambilBlok':

        #periksa tutup buku
        $tg=substr($_POST['tanggal'],6,4)."-".substr($_POST['tanggal'],3,2);
        $str=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and periode='".$tg."' and tutupbuku=0");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows<1)
        {
            exit(" Error: Periode tersebut unit telah tutup buku");
        }

        #validasi ulang jumlah dari sumber asli, jangan percaya kiriman client
        $vJml=$owlPDO->query("select kodeorg, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$_POST['nojurnal']."' and kodejurnal='M' and kodeorg in (".getOrgDetail(2).") and jumlah>0 group by kodeorg");
        $vJml->setFetchMode(PDO::FETCH_ASSOC);
        $dJml=$vJml->fetch();
        if(!$dJml || $dJml['jumlah']<=0){
            exit(" Error: Jurnal memorial ".$_POST['nojurnal']." tidak ditemukan atau di luar wewenang Anda");
        }
        $jumlahAsli=$dJml['jumlah'];

        #get akun debet jurnal memorial
        $sql="select noakun from ".$dbname.".keu_jurnaldt where nojurnal='".$_POST['nojurnal']."' and jumlah>0";
        $str=$owlPDO->query($sql);
        $str->setFetchMode(PDO::FETCH_OBJ);
        $bar=$str->fetch();
        $numrows=owlBaris($str);
        if($numrows>1)
        {
            $akunkredit='';
        }else{
            $akunkredit=$bar->noakun;
        }

        // echo $akunkredit;
        // exit;

        #periksa apakah  sudah pernah dialokasi   
          #ambil noakun
        $optAk=$optAkun="<option value=''>Choose..</option>";
        if($_SESSION['language']=='EN'){
            $str="select noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where detail=1 order by noakun";
        }else{
            $str="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 order by noakun";
        }
        $res=$owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optAkun.="<option value='".$bar->noakun."'>".$bar->noakun ."-".$bar->namaakun."</option>";
            if ($bar->noakun==$akunkredit) {
                $optAk.="<option value='".$bar->noakun."' selected >".$bar->noakun ."-".$bar->namaakun."</option>";
            }else{
                $optAk.="<option value='".$bar->noakun."'>".$bar->noakun ."-".$bar->namaakun."</option>";
            }
        }


		$expAfd = explode("####", $_POST['afdeling']);
		$no=0;
		$listafd="";
		$whereOrg = "";
        foreach ($expAfd as $key) 
		{
			$no++;
			if($no==1)
			{
				$whereOrg .= "(kodeorg like '".$key."%'";
			}
			else
			{
				$whereOrg .= " or kodeorg like '".$key."%'";
			}
			if(count($expAfd)==$no)
			{
				$whereOrg .= ")";
			}
        }
		
		if($whereOrg!='')
        {
            $whereOrg = "where ".$whereOrg;
        }

        $whstblok = "";
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";
        if($_POST['thntnm']!='') $whstblok .= " AND tahuntanam='{$_POST['thntnm']}'";
        $whstblok .= " AND luasareaproduktif>0";
		
		### ambil luas perdiv
        // $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  and statusblok in ('TB','TBM') ");
        // echo "select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}";
        $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}");
        $aLuas->setFetchMode(PDO::FETCH_ASSOC);
        $cLuas=$aLuas->fetch();
        $totLuasBlok = $cLuas['luasdivisi'] ?? 0;
        
        #ambil blok TBM,TB,LC
        // echo "select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}";
        // $str=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." and statusblok in ('TB','TBM')");
        $str=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok,tahuntanam from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $jumblok=owlBaris($str);
        // while($bar= $str->fetch())
        // {
        //     $totLuasBlok+=$bar->luasareaproduktif;
        // }

        // exit('warning '.$jumblok);
        if($jumblok<1){
            exit(" Error: There is no block to allocate");
        }
        else{
            #ambil total biaya
            // $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg."  and statusblok in ('TB','TBM') ");
            $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}");
            $dat->setFetchMode(PDO::FETCH_ASSOC);
            $mulai=1;
            while($row=$dat->fetch()){
                if($mulai==1){
                    $lstBlok="'".$row['kodeorg']."'";
                    $mulai+=1;
                }else{
                    $lstBlok.=",'".$row['kodeorg']."'";
                }
            }
            $totBy=0;
            // $sTotBy="select sum(jumlah) as biy,kodeblok from ".$dbname.".keu_jurnaldt where tanggal like '".$tg."%' and kodeblok in (".$lstBlok.")  and left(noakun,1)>5 group by kodeblok having sum(jumlah)>0";
            // $rTotBy=fetchdata($sTotBy);
            // foreach($rTotBy as $row){
            //     $totBy+=$row['biy'];
            //     $byPerBlok[$row['kodeblok']]=$row['biy'];
            // }

            if($_POST['mode']=='excel'){
                #export excel: reuse hasil query yang sama persis dengan preview, jangan hitung ulang terpisah
                echo"<table border=1>
                        <tr><td colspan=7><b>".$_SESSION['lang']['idcnote']."</b></td></tr>
                        <tr><td>".$_SESSION['lang']['jumlah']."</td><td colspan=6>".number_format($jumlahAsli)."</td></tr>
                        <tr><td colspan=7></td></tr>
                        <tr><td><b>".$_SESSION['lang']['no']."</b></td>
                            <td><b>".$_SESSION['lang']['blok']."</b></td>
                            <td><b>".$_SESSION['lang']['statusblok']."</b></td>
                            <td><b>".$_SESSION['lang']['tahuntanam']."</b></td>
                            <td><b>".$_SESSION['lang']['luas']." (Ha)</b></td>
                            <td><b>%</b></td>
                            <td><b>".$_SESSION['lang']['jumlah']." (Rp.)</b></td></tr>";
                $no=0;
                $tot=0;
                while($bar=$str->fetch()){  $no+=1;
                    @$persen=fixnan($bar->luasareaproduktif/$totLuasBlok)*100;
                    @$proporsi=fixnan($bar->luasareaproduktif/$totLuasBlok)*$jumlahAsli;
                    echo"<tr>
                            <td>".$no."</td>
                            <td>".$bar->kodeorg."</td>
                            <td>".$bar->statusblok."</td>
                            <td>".$bar->tahuntanam."</td>
                            <td>".number_format($bar->luasareaproduktif,2)."</td>
                            <td>".number_format($persen,2)."</td>
                            <td>".number_format($proporsi)."</td>
                            </tr>";
                    @$tot+=$proporsi;
                }
                echo"<tr><td colspan=6><b>".$_SESSION['lang']['total']."</b></td><td>".number_format($tot)."</td></tr></table>";
            }else{
            echo"<fieldset style='width:400px'>".$_SESSION['lang']['idcnote']."</fieldset>
                <table cellpadding=3>
                       <tr><td>".$_SESSION['lang']['debet']."</td><td><select class='select2' id=debet style='width:300px'>".$optAkun."</select></td><td align=right>Rp ".number_format($jumlahAsli)."</td></tr>
                        <tr><td>".$_SESSION['lang']['kredit']."</td><td><select class='select2' id=kredit style='width:300px'>".$optAk."</select></td><td align=right>Rp ".number_format($jumlahAsli)."</td></tr>
                         </table>
                        ";
            echo"<button onclick=saveDistribusi('".$_POST['kodeorg']."')>".$_SESSION['lang']['save']."</button>
                 <button onclick=\"ambilBlok(null,'excel')\">Excel</button>";
            echo"<fieldset><legend>".$_SESSION['lang']['distribusi']."</legend>";
            echo"<div style='font-size:11px;color:#555;margin-bottom:6px'>
                    Jumlah Blok: <b>".$jumblok."</b> &nbsp;|&nbsp;
                    Total Luas Blok Terpilih: <b>".number_format($totLuasBlok,2)." Ha</b> &nbsp;|&nbsp;
                    Total Biaya Dialokasi: <b>Rp ".number_format($jumlahAsli)."</b><br/>
                    Rumus per blok: (Luas Blok &divide; Total Luas Blok Terpilih) &times; Total Biaya
                 </div>";
            echo"<table class=sortable border=0 cellspacing=1 cellpadding=3>
                       <thead>
                           <tr class=rowheader><td>".$_SESSION['lang']['no']."</td>
                            <td>".$_SESSION['lang']['blok']."</td>
                            <td>".$_SESSION['lang']['statusblok']."</td>
                            <td>".$_SESSION['lang']['tahuntanam']."</td>
                            <td>".$_SESSION['lang']['luas']." (Ha)</td>
                            <td>%</td>
                            <td>".$_SESSION['lang']['jumlah']." (Rp.)</td></tr>
                       </thead><tbody>";
            $no=0;
            $tot=0;
            while($bar=$str->fetch()){  $no+=1;
                        /*3. alokasi IDC || biaya perblok=luas blok / sum (jumlah luas total blok ) * Rp total pembebanan,,luasareaproduktif
                        contoh : H01E02K010 ---- 11.09 HA / 2,530.28 * 5 jt*/
                @$persen=fixnan($bar->luasareaproduktif/$totLuasBlok)*100;
                @$proporsi=fixnan($bar->luasareaproduktif/$totLuasBlok)*$jumlahAsli;
                echo"<tr class=rowcontent>
                            <td class=firsttd>".$no."</td>
                            <td>".$bar->kodeorg."</td>
                            <td>".$bar->statusblok."</td>
                            <td>".$bar->tahuntanam."</td>
                            <td align=right>".number_format($bar->luasareaproduktif,2)."</td>
                            <td align=right>".number_format($persen,2)."%</td>
                            <td align=right>".number_format($proporsi)."</td>
                            </tr>";
              @$tot+=$proporsi;
            }
            echo"<tr class=rowcontent><td colspan=5>".$_SESSION['lang']['total']."</td><td align=right>100.00%</td><td align=right>".number_format($tot)."</td></tr>";
            echo"</tbody><tfoot></tfoot></fieldset>";
            }
        }
        break;

    case 'simpanIDC':
        #kunci proses per-nojurnal supaya dua request bersamaan (double click/retry) tidak lolos double alokasi
        $lockName='idc_alokasi_'.md5($_POST['nokas']);
        $lock=$owlPDO->query("SELECT GET_LOCK('".$lockName."',5) as got")->fetch(PDO::FETCH_ASSOC);
        if(!$lock || $lock['got']!=1){
            exit("Error: Proses alokasi untuk jurnal ".$_POST['nokas']." sedang berjalan, coba lagi");
        }

        #validasi ulang jumlah dari sumber asli, jangan percaya kiriman client
        $vJml=$owlPDO->query("select kodeorg, sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where nojurnal='".$_POST['nokas']."' and kodejurnal='M' and kodeorg in (".getOrgDetail(2).") and jumlah>0 group by kodeorg");
        $vJml->setFetchMode(PDO::FETCH_ASSOC);
        $dJml=$vJml->fetch();
        if(!$dJml || $dJml['jumlah']<=0){
            $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
            exit("Error: Jurnal memorial ".$_POST['nokas']." tidak ditemukan atau di luar wewenang Anda");
        }
        $jumlahAsli=$dJml['jumlah'];

        #validasi unit tujuan alokasi dalam wewenang user
        $orgAllowed=explode(",",str_replace("'","",getOrgDetail(2)));
        if(!in_array($_POST['kodeorg'],$orgAllowed)){
            $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
            exit("Error: Unit ".$_POST['kodeorg']." di luar wewenang Anda");
        }

        #validasi akun debet dan kredit tidak boleh sama
        if($_POST['debet']==$_POST['kredit']){
            $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
            exit("Error: Akun debet dan kredit tidak boleh sama");
        }

        #periksa tutup buku
        $tg=substr($_POST['tanggal'],6,4)."-".substr($_POST['tanggal'],3,2);
        $str=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and periode='".$tg."' and tutupbuku=0");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows<1)
        {
            $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
            exit("Error: Periode tersebut unit telah tutup buku");
        }

        $iCek=$owlPDO->query("select count(*) as jumlah,noreferensi from ".$dbname.".keu_jurnalht where noreferensi='".$_POST['nokas']."' ");
        $iCek->setFetchMode(PDO::FETCH_ASSOC);
        $dCek=$iCek->fetch();
            if($dCek['jumlah']>0)
            {
                $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
                exit("Error:Jurnal ".$dCek['noreferensi']." has been alocated ");
            }

		$expAfd = explode("####", $_POST['afdeling']);
		$no=0;
		$listafd="";
		$whereOrg = "";
        foreach ($expAfd as $key) 
		{
			$no++;
			if($no==1)
			{
				$whereOrg .= "(kodeorg like '".$key."%'";
			}
			else
			{
				$whereOrg .= " or kodeorg like '".$key."%'";
			}
			if(count($expAfd)==$no)
			{
				$whereOrg .= ")";
			}
        }
		
		if($whereOrg!='')
        {
            $whereOrg = "where ".$whereOrg;
        }

        #filter harus sama persis dengan preview (ambilBlok) supaya hasil simpan = hasil preview
        $whstblok = "";
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";
        if($_POST['thntnm']!='') $whstblok .= " AND tahuntanam='{$_POST['thntnm']}'";
        $whstblok .= " AND luasareaproduktif>0";


        #ambil luas perdiv
        $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}");
        $aLuas->setFetchMode(PDO::FETCH_ASSOC);
        $cLuas=$aLuas->fetch();
        $totLuasBlok = $cLuas['luasdivisi'] ?? 0;
        
        #ambil blok TBM,TB,LC
        $str=$owlPDO->query("select kodeorg,statusblok,luasareaproduktif from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $jumblok=owlBaris($str);
        if($jumblok<1){
            $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
            exit(" Error: Tidak ada blok yang dapat dialokasi");
        }
        else{
        #ambil total biaya
        $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}");
        $dat->setFetchMode(PDO::FETCH_ASSOC);
        $mulai=1;
        while($row=$dat->fetch()){
            if($mulai==1){
                $lstBlok="'".$row['kodeorg']."'";
                $mulai+=1;
            }else{
                $lstBlok.=",'".$row['kodeorg']."'";
            }
        }
        $totBy=0;
        // $sTotBy="select sum(jumlah) as biy,kodeblok from ".$dbname.".keu_jurnaldt where tanggal like '".$tg."%' and kodeblok in (".$lstBlok.")  and left(noakun,1)>5 group by kodeblok having sum(jumlah)>0";
        // $rTotBy=fetchdata($sTotBy);
        // foreach($rTotBy as $row){
        //     $totBy+=$row['biy'];
        //     $byPerBlok[$row['kodeblok']]=$row['biy'];
        // }        

        #persiapkan no jurnal
        #nojurnal selalu diawali tanggal, jadi prefix match (tanpa % di depan) bisa pakai index primary key nojurnal
        $exist=$owlPDO->query("select nojurnal from ".$dbname.".keu_jurnalht where nojurnal
                like '".tanggalsystem($_POST['tanggal'])."/".$_POST['kodeorg']."/IDC/%'");
        $exist->setFetchMode(PDO::FETCH_OBJ);
         while($bar1=$exist->fetch()){
           $noterakhir=$bar1->nojurnal;
                 }   		
         if($noterakhir==''){
            $nolanjut='001';
         }else{
                    $xx=explode("/",$noterakhir);
                        $nolanjut=intval($xx[3])+1;
                        $nolanjut=str_pad($nolanjut, 3, "0", STR_PAD_LEFT);
                  }		 
        # Prep Header
        $nojurnal=  tanggalsystem($_POST['tanggal'])."/".$_POST['kodeorg']."/IDC/".$nolanjut; 
         #exit("Error".$nojurnal);		
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>'IDC',
            'tanggal'=>  tanggalsystem($_POST['tanggal']),
            'tanggalentry'=>date('Ymd'),
            'posting'=>'1',
            'totaldebet'=>$jumlahAsli,
            'totalkredit'=>$jumlahAsli,
            'amountkoreksi'=>'0',
            'noreferensi'=>$_POST['nokas'],
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );

        # Data Detail
        #simpan siapa & kapan langsung di keterangan (kolom yang sudah ada), tanpa perlu tambah kolom baru di keu_jurnaldt
        $ketIDC = 'Alokasi IDC:'.$_POST['tanggal'].' - Input oleh '.$_SESSION['standard']['username'].' ('.date('d-m-Y H:i').')';
        $noUrut = 1;
                        # kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($_POST['tanggal']),
                            'nourut'=>$noUrut,
                            'noakun'=>$_POST['kredit'],
                            'keterangan'=>$ketIDC,
                            'jumlah'=>-1*$jumlahAsli,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$_POST['kodeorg'],
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['nokas'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                           'revisi'=>'0',
                            'kodesegment'=>''
                        );
                        $noUrut++;  
                while($bar=$str->fetch()){// 'jumlah'=>$jumlahAsli/$jumblok,
                        $proporsi=fixnan($bar->luasareaproduktif/$cLuas['luasdivisi'])*$jumlahAsli;
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($_POST['tanggal']),
                            'nourut'=>$noUrut,
                            'noakun'=>$_POST['debet'],
                            'keterangan'=>$ketIDC,
                            'jumlah'=>$proporsi,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$_POST['kodeorg'],
                            'kodekegiatan'=>$_POST['debet'].'01',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['nokas'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>$bar->kodeorg,
                            'revisi'=>'0',
                            'kodesegment'=>''
                        );
                        $noUrut++;                                              
                }  
                #insert jurnal
                #=== Insert Data ===
                $errorDB = "";
                # Header
                $queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                try{ $owlPDO->exec($queryH);}
                catch (PDOException $e) {
                $errorDB .= "Error Header :".$e->getMessage()."\n".$queryH;
                }                

                # Detail
                if($errorDB=='') {
                    foreach($dataRes['detail'] as $key=>$dataDet) {
                        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
                        try{ $owlPDO->exec($queryD);}
                        catch (PDOException $e) {
                            $errorDB .= "Error Detail ".$key.":".$e->getMessage()."\n".$queryH;
                        } 
                    }
                }
                if($errorDB!='')
                {
                    #rollback
                       $where = "nojurnal='".$nojurnal."'";
                       $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
                        try{ $owlPDO->exec($queryRB);}
                        catch (PDOException $e) {
                            $errorDB .= "Rollback 1 Error:".$e->getMessage()."\n".$queryH;
                        } 
                     echo $errorDB;
                }
                $owlPDO->query("SELECT RELEASE_LOCK('".$lockName."')");
        } #end while
        break;
    case 'hapusJurnal':
        #periksa tutup buku
        $tg=substr($_POST['tanggal'],0,7);
        $str=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and periode='".$tg."' and tutupbuku=0");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows<1){
            exit(" Error: Periode tersebut unit telah tutup buku");
        }else{
            $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$_POST['nojurnal']."'";
                try{ $owlPDO->exec($str);}
                catch (PDOException $e) {
                    $errorDB .= " Error:".$e->getMessage()."\n".$queryH;
                } 
        }
        break;
}

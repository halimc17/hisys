<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
    include_once('lib/zFunction.php');
    include_once('lib/HtmlExcel.php');
?>

<?php
    $param          =$_POST;if(count($param)==0){$param = $_GET;}
    $method         = checkPostGet('method', '');
    $pagejs         = checkPostGet('page','');
    $jab            = getPostingJabatan('traksi');
?>

<?php
    switch ($method) {
        case 'getedit':
            $str = selectQuery($dbname,'lgl_rekapsewahm','*',"kodeorg='".$param['kodeorg']."' and periode='".$param['periode']."' and spk='".$param['spk']."'");
            $rst = fetchData($str)[0];
            $hsl['tgldari']=tanggalnormal($rst['tgldari']);
            $hsl['tglsampai']=tanggalnormal($rst['tglsampai']);
            $hsl['periodebyr']=$rst['periodebyr'];
            
            $str1 = "select kodetangki,kodeorg,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$bar['millcode']."'";
            $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);
            $hsl['opttangki']="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
            foreach ($res1 as $bar1) {
                if ($bar['kodetangki'] == $bar1['kodetangki']) {
                    $hsl['opttangki'].="<option value=" . $bar1['kodetangki'] . " selected>" . $bar1['kodetangki'] . " - " . $bar1['keterangan'] . "</option>";
                } else { 
                    $hsl['opttangki'].="<option value=" . $bar1['kodetangki'] . ">" . $bar1['kodetangki'] . " - " . $bar1['keterangan'] . "</option>";
                }
                
            }

            // echo $hsl['nourut']."###".$bar['millcode']."###".$optpilihtangki."###".$bar['tinggi']."###".$bar['milimeter']."###".$bar['liter'];
            echo json_encode($hsl);
        break;
        case 'detail':
            $sql = "select * from " . $dbname . ".lgl_rekapsewahm where kodeorg = '".$param['kodeorg']."' and periode = '".$param['periode']."' and posting='1' and periodebyr = '".$param['periodebyr']."' and spk='".$param['spk']."'";
            if(count(fetchdata($sql))>0){
                exit("Warningsystem : Transaksi untuk periode : ".$param['periode']." sudah diposting !");
            }
        
            //Kalau yang ada disetup sudah diinputkan nospk
            $sql = "SELECT nospk,harga FROM $dbname.lgl_5hargasewahm WHERE tanggalberlaku <= '".tanggaldb($param['tglmulai'])."' AND kodeorg='".$param['kodeorg']."' AND nospk='".$param['spk']."' order by tanggalberlaku desc limit 1";
            if(count(fetchdata($sql))==0){
                //kalau yang disetup belum diinputkan nospk
                $sql = "SELECT nospk,harga FROM $dbname.lgl_5hargasewahm WHERE tanggalberlaku <= '".tanggaldb($param['tglmulai'])."' AND kodeorg='".$param['kodeorg']."' AND nospk='' order by tanggalberlaku desc limit 1";
                if(count(fetchdata($sql))==0){
                    exit("Warningsystem : Harga SEWA HM di Kode Organisasi ".getNamaOrg($param['kodeorg']).",<br>di tanggal yang lebih kecil dari ".tglnmbln(tanggaldb($param['tglmulai']),'I','long')."<br>belum ada, silahkan di tambah melalui menu : Kontrak - Setup - Harga Sewa HM");
                }
            }
            
            $res = fetchdata($sql)[0];
            
            $harganya=$res['harga'];
            
            $where="";$no=0;
            
            
            $tglbl=tglbulanlalu($param['tglmulai']);
            
            OPEN_BOX();
                echo"
                <div class=table-scroll>
                <table border=0 cellpadding=5 cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>
                    <th align=center width=10px>No</th>
                    <th align=center>" . $_SESSION['lang']['notransaksi'] . " Traksi</th>
                    <th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                    <th align=center>" . $_SESSION['lang']['blok'] . "</th>
                    <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                    <th align=center>" . $_SESSION['lang']['hasilkerja2'] . "</th>
                    <th align=center>" . $_SESSION['lang']['harga']. "</th>
                    <th align=center width=50px>HM</th>
                    <th align=center>" . $_SESSION['lang']['rp'] . "</th>
                    <th align=center width=30px><input type=checkbox id=clickall onchange=clickall()></th>
                </tr>
                </tr>
                </thead>
                <tbody id=getformnotraksi>";
                    $qry = "select notransaksi,tanggal,kodevhc,jenispekerjaan,alokasibiaya,beratmuatan,jumlah from " . $dbname . ".vhc_rundt_vw
                    where left(kodevhc,4) = '".$param['kodeorg']."' and substr(tanggal,1,10) between '".tanggaldb($param['tglmulai'])."' and '".tanggaldb($param['tglselesai'])."' and notransaksi not in (select notraksi from ".$dbname.".lgl_rekapsewahm) and kodevhc in (select kodevhc from $dbname.vhc_5master where kepemilikan='0' and kodesupplier  in (select koderekanan from $dbname.lgl_pengajuanspkht where notransaksi = '".$param['spk']."')) order by tanggal";
                    $rst = fetchdata($qry);
                    if(count($rst)>0){
                        foreach ($rst as $bar){
                            $nox++;
                            echo"<tr class=rowcontent id=tr_".$no.">";
                            echo"<td align=center>" . $nox . "</td>";
                            echo"<td align=center id=notraksi_".$no.">" . $bar['notransaksi'] . "</td>";
                            echo"<td align=left>" . $bar['jenispekerjaan'] . " - ".getNamaKegVhc($bar['jenispekerjaan'])."<input type=hidden id=jenispekerjaan_".$no." value=".$bar['jenispekerjaan']."></td>";
                            echo"<td align=center>" . getIndukBlok($bar['alokasibiaya']) . "<input type=hidden id=blok_".$no." value=".$bar['alokasibiaya']."></td>";
                            echo"<td align=center id=tanggal_".$no.">" . tglnmbln($bar['tanggal'],'I','long') . "</td>";
                            echo"<td align=right id=beratmuatan_".$no.">" . $bar['beratmuatan'] . "</td>";
                            echo"<td align=right id=harga_".$no.">" . number_format($harganya) . "</td>";
                            echo"<td align=right id=hm_".$no.">" . number_format($bar['jumlah'],2) . "</td>";
                            echo"<td align=right id=rupiah_".$no.">" . number_format($bar['jumlah']*$harganya) . "</td>";
                            
                            echo"<td align=center><input hidden name=cekharga[] value=''><input type=checkbox id=click".$no." name=click[] onclick='hitungclick()'></td>";
                            echo"</tr>";
                            $no++;
                        }
                    }else{
                        echo "<tr class=rowcontent><td align=center colspan=10>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
                    }
                echo"
                </tbody>	
                </table>
                </div>
                <table>
                    <tr id=inputharga> 
                        <tr>
                            <td colspan=11 align=center>
                                <button class=mybutton onclick=\"saveAll('".$no."')\" >" . $_SESSION['lang']['saveall'] . "</button>
                                <button class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
                                <button class=mybutton onclick=\"loaddatadetail()\" >Refresh</button>
                            </td>
                        </tr>		
                    </tr></table>";
            CLOSE_BOX();
            OPEN_BOX();
                echo"
                    <div id='loaddatadetail' class='table-scroll'>
                    </div>";
            CLOSE_BOX();
        break;
        case 'getformnotraksi':
            $tab="<table>
                    <tr>
                        <td>Tiket Timbang</td>
                        <td>:</td>
                        <td><input type=text id=tiketcrx style=width:145px class=myinputtext></td>
                        
                        <td>".$_SESSION['lang']['nospb']."</td>
                        <td>:</td>
                        <td><input type=text id=nospbcrx style=width:145px class=myinputtext></td>
                        
                        <td>".$_SESSION['lang']['nopol']."</td>
                        <td>:</td>
                        <td><input type=text id=nopolcrx style=width:145px class=myinputtext></td>
                    </tr>
                    <tr>	
                        <td>".$_SESSION['lang']['unit']."</td>
                        <td>:</td>
                        <td><input type=text id=unitcrx style=width:145px class=myinputtext></td>
                        
                        <td hidden>Tanggal</td>
                        <td hidden>:</td>
                        <td hidden><input type=text readonly=readonly  class=myinputtext style='width:145px;' id=tanggalcr onmousemove=setCalendar(this.id); onblur=return false; maxlength=10></td>
                        
                        <td>".$_SESSION['lang']['sopir']."</td>
                        <td>:</td>
                        <td><input type=text id=sopircrx style=width:145px class=myinputtext></td>
                    </tr>
                    <tr>					
                        <td align=center colspan='2' style=height:25px></td>
                        <td align=left colspan='2' style=height:25px>
                            <button onclick=\"getnospb()\" class=mybutton>".$_SESSION['lang']['preview']."</button>
                        </td>
                    </tr>
                    
                </table>
                ";
                
            $tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
                <thead>
                <tr class=rowheader>
                    <th align=center>No.</th>
                    <th align=center>".$_SESSION['lang']['notransaksi']." Traksi</th>
                    <th align=center style='min-width:70px'>".$_SESSION['lang']['tanggal']."</th>
                    <th align=center>".$_SESSION['lang']['hm']."</th>
                    <th align=center width=30px>Action<br><input type=checkbox id=clickall onchange=clickall()></th>
                </tr>
            </thead>
                <tbody id=formnospb></tbody>
            </table>";
            echo $tab;
        break;
        case 'getnotraksi':

            // $tab.="<tr class=rowcontent>";
            // $tab.="<td align=right colspan=12>
            // 		<button class=mybutton onclick=\"getdetailspb('".$no."')\">".$_SESSION['lang']['tambah']."</button></td>";
            // $tab.="</tr>";
            
            echo $tab;
        break;
        case 'loaddatadetail':
            echo"
            <table border=0 cellpadding=5 cellspacing=1 class=sortable>
            <thead>
            <tr class=rowheader>
                <th align=center width=10px>No</th>
                <th align=center>" . $_SESSION['lang']['notransaksi'] . " Traksi</th>
                <th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                <th align=center>" . $_SESSION['lang']['blok'] . "</th>
                <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center>" . $_SESSION['lang']['hasilkerja2'] . "</th>
                <th align=center width=50px>HM</th>
                <th align=center>" . $_SESSION['lang']['harga']. "</th>
                <th align=center>" . $_SESSION['lang']['rp'] . "</th>
                <th align=center>" . $_SESSION['lang']['action']. "</th>
            </tr>
            </tr>
            </thead>
            <tbody id=getformnotraksi>";
            $qry = "SELECT * FROM $dbname.lgl_rekapsewahmdt WHERE notraksi IN (SELECT notraksi FROM $dbname.lgl_rekapsewahm WHERE kodeorg='".$param['kodeorg']."' AND periode='".$param['periode']."' AND periodebyr='".$param['periodebyr']."' order by tanggal) and spk ='".$param['spk']."'";
            $rst = fetchdata($qry);$ttlrupiah=$ttlhm=0;
            if(count($rst)>0){
                foreach ($rst as $bar){
                    $qri = "SELECT tanggal FROM $dbname.lgl_rekapsewahm WHERE notraksi IN (SELECT notraksi FROM $dbname.lgl_rekapsewahm WHERE notraksi='".$bar['notraksi']."')";
                    $ris = fetchdata($qri);
                    $nox++;
                    echo"<tr class=rowcontent id=tr_".$no.">";
                    echo"<td align=center>" . $nox . "</td>";
                    echo"<td align=center>" . $bar['notraksi'] . "</td>";
                    echo"<td align=left>" . $bar['jeniskegiatan'] . " - ".getNamaKegVhc($bar['jeniskegiatan'])."</td>";
                    echo"<td align=center>" . getIndukBlok($bar['blok']) . "</td>";
                    echo"<td align=center>" . tglnmbln($ris[0]['tanggal'],'I','long') . "</td>";
                    echo"<td align=right>" . $bar['prestasi'] . "</td>";
                    echo"<td align=right>" . number_format($bar['hm'],2) . "</td>";
                    echo"<td align=right>" . number_format($bar['harga'],2) . "</td>";
                    echo"<td align=right>" . number_format($bar['rupiah']) . "</td>";
                    
                    echo"<td align=center>
                            <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('".$param['kodeorg']."','" .$param['periode']. "','" .$bar['notraksi']. "');\" >
                        </td>";
                    echo"</tr>";
                    $no++;
                    $ttlrupiah += $bar['rupiah'];
                    $ttlhm += $bar['hm'];
                }
                echo "<tr class=rowcontent>
                        <td colspan=6 align=center><b>".$_SESSION['lang']['total']."</b></td>
                        <td align=right>".$ttlhm."</td>
                        <td align=right></td>
                        <td align=right><b>".number_format($ttlrupiah)."</b></td>
                        <td></td>
                    </tr>";
            }else{
                echo "<tr class=rowcontent><td align=center colspan=10>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }
            echo"
            </tbody>	
            </table>";
        break;

        case 'viewdetailinput':
            #versi lihat-saja (tanpa hapus) dari loaddatadetail, dipanggil dari ikon kaca pembesar di List Data -
            #ikut kasih tau siapa yang create & posting, karena ini cuma buat lihat, bukan buat edit transaksi
            $nmkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
            echo"
            <div class=table-scroll>
            <table border=0 cellpadding=5 cellspacing=1 class=sortable>
            <thead>
            <tr class=rowheader>
                <th align=center width=10px>No</th>
                <th align=center>" . $_SESSION['lang']['notransaksi'] . " Traksi</th>
                <th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>
                <th align=center>" . $_SESSION['lang']['blok'] . "</th>
                <th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
                <th align=center>" . $_SESSION['lang']['hasilkerja2'] . "</th>
                <th align=center width=50px>HM</th>
                <th align=center>" . $_SESSION['lang']['harga']. "</th>
                <th align=center>" . $_SESSION['lang']['rp'] . "</th>
                <th align=center>Create By</th>
                <th align=center>Create Time</th>
                <th align=center>Posted By</th>
            </tr>
            </thead>
            <tbody>";
            $qry = "SELECT b.*,a.tanggal,a.createby,a.createtime,a.postingby,a.posting FROM $dbname.lgl_rekapsewahmdt b
                    LEFT JOIN $dbname.lgl_rekapsewahm a ON a.notraksi=b.notraksi
                    WHERE b.notraksi IN (SELECT notraksi FROM $dbname.lgl_rekapsewahm WHERE kodeorg='".$param['kodeorg']."' AND periode='".$param['periode']."' AND periodebyr='".$param['periodebyr']."')
                    AND b.spk ='".$param['spk']."' order by a.tanggal";
            $rst = fetchdata($qry);$ttlrupiah=$ttlhm=0;$nox=0;
            if(count($rst)>0){
                foreach ($rst as $bar){
                    $nox++;
                    echo"<tr class=rowcontent>";
                    echo"<td align=center>" . $nox . "</td>";
                    echo"<td align=center>" . $bar['notraksi'] . "</td>";
                    echo"<td align=left>" . $bar['jeniskegiatan'] . " - ".getNamaKegVhc($bar['jeniskegiatan'])."</td>";
                    echo"<td align=center>" . getIndukBlok($bar['blok']) . "</td>";
                    echo"<td align=center>" . tglnmbln($bar['tanggal'],'I','long') . "</td>";
                    echo"<td align=right>" . $bar['prestasi'] . "</td>";
                    echo"<td align=right>" . number_format($bar['hm'],2) . "</td>";
                    echo"<td align=right>" . number_format($bar['harga'],2) . "</td>";
                    echo"<td align=right>" . number_format($bar['rupiah']) . "</td>";
                    echo"<td align=left>" . @$nmkaryawan[$bar['createby']] . "</td>";
                    echo"<td align=center>" . $bar['createtime'] . "</td>";
                    echo"<td align=left>" . (($bar['posting']==1) ? @$nmkaryawan[$bar['postingby']] : '-') . "</td>";
                    echo"</tr>";
                    $ttlrupiah += $bar['rupiah'];
                    $ttlhm += $bar['hm'];
                }
                echo "<tr class=rowcontent>
                        <td colspan=6 align=center><b>".$_SESSION['lang']['total']."</b></td>
                        <td align=right>".$ttlhm."</td>
                        <td align=right></td>
                        <td align=right><b>".number_format($ttlrupiah)."</b></td>
                        <td colspan=3></td>
                    </tr>";
            }else{
                echo "<tr class=rowcontent><td align=center colspan=12>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }
            echo"
            </tbody>
            </table>
            </div>";
        break;

        case 'getnospk':
            $namasupp=array();
            $optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
            $sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
            left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
            where a.posting='0' and b.close='0' and b.jenis='SEWA.HM' and a.kodeorg='".$param['kodeorg']."' and substr(a.dari,1,7)<='".$param['periode']."' and substr(a.sampai,1,7)>='".$param['periode']."' order by a.notransaksi asc";
            $res = fetchdata($sql);
            foreach($res as $bar){
                $namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
                
                $optsupp.="<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
            }
            echo $optsupp;
        break;
        case 'loadData':
            $hsl['tab'] ="";
            $hsl['foot']="";
            $limit      = 99999; #full kan list data nya - gak perlu paging kecil2, satu halaman langsung semua
            $page       = 0;
            $colspan    = 15; #9 kolom tunggal + 6 kolom Aksi (colspan=6 di header)

            if (isset($pagejs)) {
                $page   = $pagejs;
                if ($page < 0)
                    $page = 0;
            }

            $offset     = floatval($page) * $limit;
            $maxdisplay =(floatval($page) * $limit);
            $no         =((floatval($page) * $limit));

            if($param['nospkcr']!='' || $param['divsch']!='' || $param['tglsch']!='' || $param['kontrakcr']!='' || $param['notraksicr']!=''){
                $where.="and spk LIKE '%".$param['nospkcr']."%' and kodeorg LIKE '%".$param['divsch']."%' and periode LIKE '%".$param['tglsch']."%'";
                if($param['notraksicr']!=''){
                    $where.=" and notraksi LIKE '%".addslashes($param['notraksicr'])."%'";
                }
                if($param['kontrakcr']!=''){
                    #cari spk yang kontraktornya (namasupplier) cocok dulu, baru dipakai sebagai filter spk -
                    #lebih cepat & aman daripada subquery IN(select...) langsung di query besar
                    $rSupp = fetchData("select supplierid from ".$dbname.".log_5supplier where namasupplier LIKE '%".addslashes($param['kontrakcr'])."%'");
                    $listSupp = array();
                    foreach($rSupp as $r){ $listSupp[] = "'".addslashes($r['supplierid'])."'"; }
                    if(count($listSupp) > 0){
                        $rSpk = fetchData("select notransaksi from ".$dbname.".lgl_pengajuanspkht where koderekanan in (".implode(',',$listSupp).")");
                        $listSpk = array();
                        foreach($rSpk as $r){ $listSpk[] = "'".addslashes($r['notransaksi'])."'"; }
                        $where.= (count($listSpk) > 0) ? " and spk in (".implode(',',$listSpk).")" : " and 1=0";
                    } else {
                        $where.=" and 1=0";
                    }
                }
            }

            $str        = "SELECT * FROM $dbname.lgl_rekapsewahm WHERE 1=1 $where GROUP BY periodebyr,periode,spk ORDER BY createtime DESC";
            $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $jlhbrs     = owlBaris($res);
            $res        = fetchdata($str);
            $jlhbrs     = count($res);
            $totrows    = ceil($jlhbrs / $limit);
            
            if($totrows == 0){
                $totrows = 1;
            }
                    
            $isiRow = '';
            for ($er = 1; $er <= $totrows; $er++){
                $sel    = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            
            $frompage   = ((floatval($page)*$limit)+1);
            if(((floatval($page)+1)*$limit) > $jlhbrs){
                $topage = $jlhbrs;
            }else{
                $topage = ((floatval($page)+1)*$limit);
            }

            if($jlhbrs < 1){
                    $hsl['tab'].="<tr class=rowcontent>
                                <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                            </tr>";
            }else{
                $iList  = "SELECT *,SUM(totalprestasi) AS totalprestasi FROM $dbname.lgl_rekapsewahm WHERE 1=1 $where GROUP BY periodebyr,periode,spk ORDER BY periode DESC,kodeorg LIMIT ".$offset.",".$limit."";
                $hasil  = fetchdata($iList);
                $nmkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

                foreach ($hasil as $dList){
                    $optOrg         = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$dList['kodeorg']."'");
                    $optKontraktor  = makeOption($dbname,'lgl_pengajuanspkht','notransaksi,koderekanan',"notransaksi='".$dList['spk']."'");
                    if ($dList['status'] == 'A') {
                        $status = $_SESSION['lang']['aktif'];
                    } else if ($dList['status'] == 'D') {
                        $status = $_SESSION['lang']['tidakaktif'];
                    }
                    
                    $qr = "SELECT SUM(rupiah) AS rupiah FROM $dbname.lgl_rekapsewahmdt WHERE notraksi IN (SELECT notraksi FROM $dbname.lgl_rekapsewahm WHERE spk='{$dList['spk']}' AND periodebyr='{$dList['periodebyr']}' AND periode='{$dList['periode']}')";
                    $rs = fetchdata($qr);

                    $no+=1;
                    $hsl['tab'].="<tr class=rowcontent>";
                        $hsl['tab'].="<td align=center>" . $no . "</td>";
                        $hsl['tab'].="<td align=left>".$dList['kodeorg']." - " . $optOrg[$dList['kodeorg']] . "</td>";
                        $hsl['tab'].="<td align=left>".$dList['periode']."</td>";
                        $hsl['tab'].="<td align=left>".tanggalnormal($dList['tgldari'])." s.d ".tanggalnormal($dList['tglsampai'])."</td>";
                        $hsl['tab'].="<td align=left>".$dList['spk']."</td>";
                        $hsl['tab'].="<td align=left>".$dList['notraksi']."</td>";
                        $hsl['tab'].="<td align=left>".getNamaSupplier($optKontraktor[$dList['spk']])."</td>";
                        $hsl['tab'].="<td align=right>".number_format($rs[0]['rupiah'])."</td>";
                        $hsl['tab'].="<td align=left style=color:blue;cursor:pointer; title=\"Click untuk melihat dan mengajuan BAPP\" onclick=viewdetailbapp('".$dList['spk']."','".$dList['kodeorg']."','viewhtml','event','".$dList['nobapp']."')>" . ($dList['nobapp']). "</td>";
                        $hsl['tab'].="<td align=left>".@$nmkaryawan[$dList['createby']]."</td>";
                        $hsl['tab'].="<td align=center>".$dList['createtime']."</td>";
                        $hsl['tab'].="<td align=left>".(($dList['posting']==1) ? @$nmkaryawan[$dList['postingby']] : '-')."</td>";
                        $hsl['tab'].="<td width=20px align=center><img src='images/skyblue/zoom.png' class=zImgBtn title='Detail data yang diinput' onclick=\"viewDetailInput('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."',event);\"></td>";
                        $hsl['tab'].="<td width=20px align=center><img src=images/excel.jpg class=zImgBtn title='Excel' onclick=\"exportExcelRow('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."');\"></td>";
                        $hsl['tab'].="<td width=20px align=center><img src=images/pdf.jpg class=zImgBtn title='PDF' onclick=\"exportPdfRow('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."');\"></td>";
                        if($dList['posting'] == 0){
                            $hsl['tab'].="<td align=center>
                                        <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['kodeorg'] . "','" . $dList['periode'] . "','" . $dList['spk'] . "','".$dList['periodebyr']."');\">
                                    </td>";
                            $hsl['tab'].="<td width=20px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."');\" ></td>";
                            $hsl['tab'].="<td width=20px align=center><img src=images/skyblue/posting.png class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."');\" ></td>";
                            // $hsl['tab'].="<td width=20px><img src=images/excel.jpg class=zImgBtn  title='Excel' onclick=\"previewexcel('".$dList['kodeorg']."','" .$dList['periode']. "','" .$dList['spk']. "','".$dList['periodebyr']."','excel');\" ></td>";
                        }else{
                            if(in_array($_SESSION['empl']['jabatan'],$jab)){
                                $icon="images/icons/04/16/04.png";
                                $title="Unposting";
                                $unpost=" onclick=\"unposting('".$dList['kodeorg']."','".$dList['periode']."','".$dList['spk']."','".$dList['nobapp']."','".$dList['periodebyr']."','".$no."');\" ";
                            }else {
                                $icon="images/icons/04/16/02.png";
                                $title="Posted";
                                $unpost='';
                            }
                            $hsl['tab'].="<td></td><td></td>";
                            $hsl['tab'].="<td width=20px align=center><img src=".$icon." class=zImgBtn height='30' title='".$title."' ".$unpost." ></td>";
                        }
                        // $hsl['tab'].="<td></td>";
                    $hsl['tab'].="</tr>
                        </tbody>";
                }
                $hsl['foot'] .= createpaging($jlhbrs,$limit,$page,$colspan,$method,'getPage');
            }
            echo json_encode($hsl);
        break;

        case 'excel':
            #tarikan per-baris (ikon Excel di Aksi) kirim kodeorgx/periodex/spkx/periodebyrx buat exact-match 1 baris
            #aja - kalau gak ada, ikut filter yang lagi aktif di form Cari (tarikan dari fieldset Cari)
            if($param['kodeorgx']!='' && $param['periodex']!='' && $param['periodebyrx']!='' && $param['spkx']!=''){
                $where = " and kodeorg='".$param['kodeorgx']."' and periode='".$param['periodex']."' and periodebyr='".$param['periodebyrx']."' and spk='".$param['spkx']."'";
            } elseif($param['nospkcr']!='' || $param['divsch']!='' || $param['tglsch']!='' || $param['kontrakcr']!='' || $param['notraksicr']!=''){
                $where.="and spk LIKE '%".$param['nospkcr']."%' and kodeorg LIKE '%".$param['divsch']."%' and periode LIKE '%".$param['tglsch']."%'";
                if($param['notraksicr']!=''){
                    $where.=" and notraksi LIKE '%".addslashes($param['notraksicr'])."%'";
                }
                if($param['kontrakcr']!=''){
                    $rSupp = fetchData("select supplierid from ".$dbname.".log_5supplier where namasupplier LIKE '%".addslashes($param['kontrakcr'])."%'");
                    $listSupp = array();
                    foreach($rSupp as $r){ $listSupp[] = "'".addslashes($r['supplierid'])."'"; }
                    if(count($listSupp) > 0){
                        $rSpk = fetchData("select notransaksi from ".$dbname.".lgl_pengajuanspkht where koderekanan in (".implode(',',$listSupp).")");
                        $listSpk = array();
                        foreach($rSpk as $r){ $listSpk[] = "'".addslashes($r['notransaksi'])."'"; }
                        $where.= (count($listSpk) > 0) ? " and spk in (".implode(',',$listSpk).")" : " and 1=0";
                    } else {
                        $where.=" and 1=0";
                    }
                }
            }

            #kop/ditarik-oleh format standar, pakai org karyawan yang login (data lintas unit, bukan per-PT)
            $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
            $kolom = 12;
            $stream = "<table>
                <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
                <tr><td colspan=" . $kolom . "><b>REKAP SEWA HM</b></td></tr>
                <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
                <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
                </table>";

            $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
                <thead><tr>
                    <th align=center>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center>" . $_SESSION['lang']['unit'] . "</th>
                    <th align=center>" . $_SESSION['lang']['bulan'] . "</th>
                    <th align=center>" . $_SESSION['lang']['periode'] . "</th>
                    <th align=center>" . $_SESSION['lang']['nospk'] . "</th>
                    <th align=center>" . $_SESSION['lang']['notransaksi'] . " Traksi</th>
                    <th align=center>" . $_SESSION['lang']['kontraktor'] . "</th>
                    <th align=center>" . $_SESSION['lang']['rupiah'] . "</th>
                    <th align=center>No BAPP</th>
                    <th align=center>Create By</th>
                    <th align=center>Create Time</th>
                    <th align=center>Posted By</th>
                </tr></thead><tbody>";

            $iList = "SELECT *,SUM(totalprestasi) AS totalprestasi FROM $dbname.lgl_rekapsewahm WHERE 1=1 $where GROUP BY periodebyr,periode,spk ORDER BY periode DESC,kodeorg";
            $hasil = fetchdata($iList);
            $nmkaryawan = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
            $no = 0;
            foreach ($hasil as $dList){
                $optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$dList['kodeorg']."'");
                $optKontraktor = makeOption($dbname,'lgl_pengajuanspkht','notransaksi,koderekanan',"notransaksi='".$dList['spk']."'");
                $qr = "SELECT SUM(rupiah) AS rupiah FROM $dbname.lgl_rekapsewahmdt WHERE notraksi IN (SELECT notraksi FROM $dbname.lgl_rekapsewahm WHERE spk='{$dList['spk']}' AND periodebyr='{$dList['periodebyr']}' AND periode='{$dList['periode']}')";
                $rs = fetchdata($qr);
                $no+=1;
                $stream .= "<tr>
                    <td align=center>" . $no . "</td>
                    <td>" . $dList['kodeorg'] . " - " . $optOrg[$dList['kodeorg']] . "</td>
                    <td>" . $dList['periode'] . "</td>
                    <td>" . tanggalnormal($dList['tgldari']) . " s.d " . tanggalnormal($dList['tglsampai']) . "</td>
                    <td>" . $dList['spk'] . "</td>
                    <td>" . $dList['notraksi'] . "</td>
                    <td>" . getNamaSupplier($optKontraktor[$dList['spk']]) . "</td>
                    <td align=right>" . number_format($rs[0]['rupiah']) . "</td>
                    <td>" . $dList['nobapp'] . "</td>
                    <td>" . @$nmkaryawan[$dList['createby']] . "</td>
                    <td>" . $dList['createtime'] . "</td>
                    <td>" . (($dList['posting']==1) ? @$nmkaryawan[$dList['postingby']] : '-') . "</td>
                </tr>";
            }
            $stream .= "</tbody></table>";

            $xls = new HtmlExcel();
            $xls->addSheet("RekapSewaHM", $stream);
            $xls->headers("RekapSewaHM_" . date('YmdHis') . ".xls");
            echo $xls->buildFile();
        break;

        case 'insert':
            if($param['cek'] == 1){
                //Ambil total HM yang sudah diinput
                $sql=selectQuery($dbname,'lgl_pengajuanspkht','jlhhm',"notransaksi='{$param['spk']}'");
                $rst=fetchData($sql);
                $jlhhm=$rst[0]['jlhhm'];

                //Ambil total HM yang sudah diinput, kecuali punya notraksi ini sendiri (mau direplace lewat delete-insert,
                //jadi jangan ikut dihitung sebagai "sudah diinput" - supaya Simpan ulang notraksi yang sama tidak salah ketolak)
                $str1=selectQuery($dbname,'lgl_rekapsewahmdt','sum(hm) as ttlhmdiinput',"spk='{$param['spk']}' and notraksi!='{$param['notraksi']}'");
                $res1=fetchData($str1);
                $ttlhm=$res1[0]['ttlhmdiinput'];
                if(($ttlhm+$param['hm']) > $jlhhm){exit("Warningsystem : Data HM yang sudah diinputkan + yang akan diinputkan<br>melebihi Total HM di SPK ".$param['spk'].".<br> Total HM SPK ".$param['spk']." = ".number_format($jlhhm)."<br> Total HM yang sudah diinput = ".number_format($ttlhm+$param['hm'])." !");}
        
                try {
                    $owlPDO->beginTransaction();
                    
                    $str    = "SELECT * FROM $dbname.lgl_rekapsewahm WHERE kodeorg ='{$param['kodeorg']}' AND periode ='{$param['periode']}' AND notraksi='{$param['notraksi']}'";
                    $res    = fetchData($str);
                    if(count($res)== 0){
                        $sql=selectQuery($dbname,'vhc_runht','tanggal,jenisvhc',"notransaksi='".$param['notraksi']."'");
                        $hsl=fetchData($sql)[0];

                        $string=selectQuery($dbname,'vhc_rundt','sum(beratmuatan) as beratmuatan',"notransaksi='".$param['notraksi']."'");
                        $hasil=fetchData($string)[0];
                        $data = array();
                        $data = array(
                                'kodeorg'       => $param['kodeorg'],
                                'divisi'        => substr($param['blok'],0,6),
                                'periode'       => $param['periode'],
                                'periodebyr'    => $param['periodebyr'],
                                'tanggal'       => $hsl['tanggal'],
                                'tgldari'       => tanggaldb($param['tglmulai']),
                                'tglsampai'     => tanggaldb($param['tglselesai']),
                                'notraksi'      => $param['notraksi'],
                                'spk'           => $param['spk'],
                                'jenisvhc'      => $hsl['jenisvhc'],
                                'posting'       => 0,
                                'totalprestasi' => $hasil['beratmuatan'],
                                'createby'      => $_SESSION['standard']['userid'],
                                'createtime'    => date('Y-m-d H:i:s'),
                                'updateby'      => $_SESSION['standard']['userid']
                            );
                            
                        $cols = array();
                        foreach($data as $key=>$row) {
                                $cols[] = $key;
                        }
                        
                        $str = insertQuery($dbname,'lgl_rekapsewahm',$data,$cols);
                        $owlPDO->exec($str);
                    }
        
        
                    $datadt = array(
                        'notraksi'      => $param['notraksi'],
                        'spk'           => $param['spk'],
                        'jeniskegiatan' => $param['jeniskegiatan'],
                        'blok'          => $param['blok'],
                        'prestasi'      => $param['prestasi'],
                        'harga'         => $param['harga'],
                        'hm'            => $param['hm'],
                        'rupiah'        => ($param['hm']*$param['harga'])
                    );
                    
                    $colsdt = array();
                    foreach($datadt as $kuy=>$row) {
                            $colsdt[] = $kuy;
                    }

                    #hapus dulu detail lama punya notraksi ini (delete-insert) - supaya tombol Simpan bisa diklik ulang
                    #sebelum posting tanpa kena duplicate entry (PK nya notraksi+jeniskegiatan+blok+spk+prestasi)
                    $strdel = "delete from " . $dbname . ".lgl_rekapsewahmdt where notraksi='" . $param['notraksi'] . "' and spk='" . $param['spk'] . "'";
                    $owlPDO->exec($strdel);

                    $strx = insertQuery($dbname,'lgl_rekapsewahmdt',$datadt,$colsdt)."";
                    $owlPDO->exec($strx);
                    
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Warningsystem, " . addslashes($e->getMessage());
                    die();
                }
            }
        break;
        case 'delete':
            $ht     = "DELETE FROM " . $dbname . ".lgl_rekapsewahm WHERE kodeorg='".$param['kodeorg']."' AND periode='" . $param['periode'] . "' and periodebyr='".$param['periodebyr']."' and spk ='".$param['spk']."' ";
            try {
                $owlPDO->exec($ht);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'posting':
            try {
                $owlPDO->beginTransaction();
                    
                    #buat nomor termin
                    $str = "select max(termin) as termin from " . $dbname . ".log_baspk where notransaksi='" . $param['spk'] . "'";
                    $str = "select distinct keterangan, termin from " . $dbname . ".log_baspk where notransaksi='" . $param['spk'] . "'  group by keterangan, termin";
                    $res = fetchdata($str);
                    // $noter = count($res);
                    // if($noter==0){
                    //     $notermin=1;
                    // }else{
                    //     $notermin=intval($noter)+1;
                    // }
                    $notermin=$param['periodebyr'];
                    $arrthn=explode("-",$param['periode']);
                    $tahun=$arrthn[0];
                    $bulan=$arrthn[1];
                    
                    #buat nomor BAPP Format : 001/BAPP/BPJE/2019
                    $str = "select max(substr(keterangan,1,3)) as bapp from " . $dbname . ".log_baspk where notransaksi = '".$param['spk']."' limit 1"; #exit("error");
                    $res = fetchdata($str);
                    $noba = intval($res[0]['bapp']);
                    if($noba==0){
                        $nobap="001";
                    }else{
                        $nobap=addZero($noba+1,3);
                    }
                    $dtnospk=explode("/",$param['spk']);
                    $nobapp=$nobap."/".$dtnospk[0]."/BAPP/".$param['kodeorg']."/".$bulan."/".$tahun;
                    
                    #ambil data
                    $str = "select b.*,a.tanggal,a.notraksi as notraksiht from " . $dbname . ".lgl_rekapsewahm a 
                    left join " . $dbname . ".lgl_rekapsewahmdt b on a.notraksi=b.notraksi 
                    where 1=1 and a.kodeorg = '" . $param['kodeorg'] . "' and a.periode='" . $param['periode'] . "' and a.spk='" . $param['spk'] . "' and periodebyr='".$param['periodebyr']."' "; 
                    #exit("error".$str);
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    $data=$rupiah=$datadt=$rupiahdt=array();
                    while ($bar = $res->fetch()) {
                        #$data[$bar['blok']][$bar['jeniskegiatan']][$bar['jenis']."#".$bar['tujuan']]+=$bar['hm'];
                        #$rupiah[$bar['blok']][$bar['jeniskegiatan']][$bar['jenis']."#".$bar['tujuan']]+=$bar['rupiah']-$bar['potonganrp'];
                        if($bar['rupiah']>0){				
                            $data[$bar['blok']][$bar['jeniskegiatan']]+=$bar['hm'];
                            $rupiah[$bar['blok']][$bar['jeniskegiatan']]+=$bar['rupiah'];
            
                            $datadt[$bar['blok']][$bar['jeniskegiatan']][$bar['notraksi']][$bar['tanggal']]+=$bar['hm'];
                            $rupiahdt[$bar['blok']][$bar['jeniskegiatan']][$bar['notraksi']][$bar['tanggal']]+=$bar['rupiah'];
                        }
                                    
                        if($bar['notraksi']==""){
                            throw new PDOException("No Traksi ".$bar['notraksiht']." tidak ada pada detail transaksi");
                        }
                    }
                    
                    #log_baspk
                    $totalrupiah=0;
                    foreach($data as $blok => $valkeg){
                        foreach($valkeg as $kegiatan => $hm){
                            $data = array();
                            if($rupiah[$blok][$kegiatan]!=''){
                                $data = array(
                                        'notransaksi'        => $param['spk'],
                                        'kodeblok'           => $blok,
                                        'kodekegiatan'       => $kegiatan,
                                        'tanggal'            => '0000-00-00',
                                        'hasilkerjarealisasi'=> $hm,
                                        'jumlahrealisasi'    => $rupiah[$blok][$kegiatan],
                                        'posting'            => '0',
                                        'statusjurnal'       => '0',
                                        'statuspengajuan'    => '0',
                                        'blokspkdt'          => $blok,
                                        'termin'             => $notermin,
                                        'keterangan'         => $nobapp
                                    );
                                    
                                $cols = array();
                                foreach($data as $key=>$row) {
                                        $cols[] = $key;
                                }
                                
                                $str = insertQuery($dbname,'log_baspk',$data,$cols);
                                $owlPDO->exec($str);
                                
                                $totalrupiah+=$rupiah[$blok][$kegiatan];
                                #====================================
            
                                $jlh=0;
                                $where = "notransaksi='".$param['spk']."' and kodeblok='".$blok."' and kodekegiatan='".$kegiatan."'";
                                #cek dulu ada atau tidak, jika ada update jika tidak insert
                                $str = "select * from " . $dbname . ".log_spkdt where ".$where."";
                                $res = fetchdata($str);
                                $jlh=count($res);
                                # exit('error'.$str);
                                if($jlh>0){
                                    #hapus dulu
                                    $str = "delete from " . $dbname . ".log_spkdt where ".$where."";
                                    $owlPDO->exec($str);
                                }
                                
                                $str = "select sum(hasilkerjarealisasi) as hasilkerjarealisasi, sum(jumlahrealisasi) as jumlahrealisasi from " . $dbname . ".log_baspk where ".$where."";
                                $res = fetchdata($str);
                                $hasilkerja=$res[0]['hasilkerjarealisasi'];
                                $rupiahhasil=$res[0]['jumlahrealisasi'];
                                // $hasilkerja=($hm+$res[0]['hasilkerjajumlah']);
                                // $rupiahhasil=($rupiah[$blok][$kegiatan]+$res[0]['jumlahrp']);
                                if($rupiahhasil!=0){					
                                    $rppersatuan=$rupiahhasil/$hasilkerja;
                                }else{
                                    $rppersatuan=0;
                                }
                                
                                
                                $dataspk = array();
                                $dataspk = array(
                                        'notransaksi'     => $param['spk'],
                                        'kodeblok'        => $blok,
                                        'kodekegiatan'    => $kegiatan,
                                        'hk'              => '0',
                                        'hasilkerjajumlah'=> $hasilkerja,
                                        'satuan'          => 'HM',
                                        'jumlahrp'        => $rupiahhasil,
                                        'rupiahpersatuan' => $rppersatuan
                                    );
                                
                                $colsspk = array();
                                foreach($dataspk as $key=>$row) {
                                        $cols[] = $key;
                                }
                                
                                $str = insertQuery($dbname,'log_spkdt',$dataspk,$colsspk);
                                $owlPDO->exec($str);					
                                #===================================
                            }
                        }			
                    }
                    
                    #update nilai di spkht
                    $str = "select sum(jumlahrp) as jumlahrp from " . $dbname . ".log_spkdt where notransaksi='".$param['spk']."'";
                    $res = fetchdata($str);
                    $nilaikontrak=$res[0]['jumlahrp'];
                    
                    $where = "notransaksi='".$param['spk']."' and kodeorg='".$param['kodeorg']."'";
                    $str = "update " . $dbname . ".log_spkht set nilaikontrak = '".$nilaikontrak."' where ".$where."";
                    $owlPDO->exec($str);
                    
                    #log_baspkdt
                    foreach($datadt as $blokdt => $valkegdt){
                        foreach($valkegdt as $kegdt => $valspbdt){
                            foreach($valspbdt as $nospbdt => $valtgldt){
                                foreach($valtgldt as $tgldt => $hmdet){
                                    $data = array();
                                    if($rupiahdt[$blokdt][$kegdt][$nospbdt][$tgldt]!=''){
                                        $data = array(
                                                'notransaksi'        => $param['spk'],
                                                'kodeblok'           => $blokdt,
                                                'kodekegiatan'       => $kegdt,
                                                'tanggal'            => $tgldt,
                                                'hasilkerjarealisasi'=> $hmdet,
                                                'jumlahrealisasi'    => $rupiahdt[$blokdt][$kegdt][$nospbdt][$tgldt],
                                                'termin'             => $notermin,
                                                'keterangan'         => $nobapp,
                                                'keterangan2'        => $nospbdt
                                            );
                                            
                                        $cols = array();
                                        foreach($data as $key=>$row) {
                                                $cols[] = $key;
                                        }
                                        $str = insertQuery($dbname,'log_baspkdt',$data,$cols);
                                        $owlPDO->exec($str);
                                    }
                                }
                            }
                        }
                    }
                    
                    #update nilai di lgl_rekapsewahm
                    $updaterekap = array(
                        'posting' => '1',
                        'nobapp' => $nobapp,
                        'postingby' => $_SESSION['standard']['userid']
                        );

                    $where = "periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."' and periodebyr='".$param['periodebyr']."' and spk ='".$param['spk']."'";
                    $str = updateQuery($dbname,'lgl_rekapsewahm',$updaterekap,$where);
                    $owlPDO->exec($str);
                    
                    #execute
                    $owlPDO->commit();
                } catch (PDOException $e) {
                    $owlPDO->rollback();
                    echo "Error, " . addslashes($e->getMessage());
                    die();
                }

        break;
        case 'unposting':
            try {
                $owlPDO->beginTransaction();
            
                #cek BAPP sudah diajukan atau belum
                $statuspengajuan=$posting='0';
                $str = "select * from " . $dbname . ".log_baspk where notransaksi='".$spk."' and keterangan ='".$nobapp."'";
                $ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $ttp->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $ttp->fetch()) {
                    $statuspengajuan=$bar['statuspengajuan'];
                    $posting=$bar['posting'];
                }
                if($posting=='1'){
                    #sudah posting
                    throw new PDOException("BAPP sudah diposting !");
                }
                
                if($statuspengajuan!='3' and $statuspengajuan!='0'){
                    #jika status BUKAN ditolak atau sudah diajukan
                    throw new PDOException("BAPP sudah dalam proses persetujuan dan atau sudah disetujui !");
                }
                
                #hapus log_baspk
                $str = "delete from " . $dbname . ".log_baspk where notransaksi='".$spk."' and keterangan ='".$nobapp."'";
                $owlPDO->exec($str);
                
                #hapus log_baspkdt
                $str = "delete from " . $dbname . ".log_baspkdt where notransaksi='".$spk."' and keterangan ='".$nobapp."'";
                $owlPDO->exec($str);
                
                #update nilai di lgl_rekapsewahm
                $updaterekap = array(
                    'posting' => '0',
                    'nobapp' => '',
                    'postingby' => '0000000000'
                    );

                $where = "periode='".$periode."' and kodeorg='".$kodeorg."' and periodebyr='".$periodebyr."' and spk='".$spk."'";
                $str = updateQuery($dbname,'lgl_rekapsewahm',$updaterekap,$where);
                $owlPDO->exec($str);
                
                #execute
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollback();
                echo "Error, " . addslashes($e->getMessage());
                die();
            }
            
        break;
        case 'deletedetail':
                $str = "delete from " . $dbname . ".lgl_rekapsewahm where kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and notraksi='" . $param['notraksi'] . "' ";
                try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        break;
        default:
    }
?>
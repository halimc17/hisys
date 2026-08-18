<?php
    session_start();
    require_once('master_validation.php');
    require_once('config/connection.php');
    include_once('lib/nangkoelib.php');
    include_once('lib/zLib.php');
    include_once('lib/zFunction.php');
    require_once('dompdf/autoload.inc.php');
    use Dompdf\Dompdf;

    $param          =$_POST;if(count($param)==0){$param = $_GET;}
    
    $lokasikerja    =$_SESSION['empl']['lokasitugas'];
    $user_entry     =$_SESSION['standard']['userid'];
    $proses         =checkPostGet('proses','');
    if(isset($param['tglpekerjaan'])){
        $tgl_kerja  =tanggalsystem($param['tglpekerjaan']);
    }
    
    $optkelvhc      =makeOption($dbname,'vhc_5jenisvhc','jenisvhc,kelompokvhc');
    $optpt          =makeOption($dbname,'organisasi','kodeorganisasi,induk');
    $nmpek          =makeOption($dbname,'vhc_kegiatan','kodekegiatan,namakegiatan');
    $arrJab         =array("0"=>"Operator","1"=>"Helper","2"=>"Driver");
    
    if(isset($param['no_trans'])){
        $optKdVhc       =makeOption($dbname,'vhc_runht', 'notransaksi,kodevhc,kodeorg',"notransaksi = '".$param['no_trans']."'");
        $sKode=selectQuery($dbname,'vhc_runht','*',"notransaksi='".$param['no_trans']."'");
        @$rKode=fetchData($sKode)[0];
    }
    
    switch($proses){
        case'getnotiket':
            $optnotiket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
            $str="select notransaksi,nokendaraan,beratbersih from ".$dbname.".pabrik_timbangan where millcode='".$param['kodeorg']."' and tanggal like  '".tanggaldb($param['tanggal'])."%' and kodebarang ='40000003'";
            $res=fetchdata($str);
            foreach($res as $val){
                $optnotiket.="<option value='".$val['notransaksi']."'>".$val['notransaksi']." [".$val['nokendaraan']."] (<i>".$val['beratbersih']."</i> Kg)</option>";
            }

            echo $optnotiket;
        break;
        case'getData':
            $sql="select * from ".$dbname.".pabrik_gradingtbs where notiket='".$param['notiket']."'";
            $res1=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_ASSOC);     
            $res=$res1->fetch();

            echo json_encode($res);
        break;
        case'loaddata':
            $limit      =15;
            $colspan    =8;
            $page       =0;
            $tgl_carisd =tanggalsystemn(checkPostGet('tgl_carisd',''));
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $where = "";
            if($param['tgl_cari']!=''){
                $txtTgl=tanggalsystem($param['tgl_cari']);
                $txt_tgl_a=substr($txtTgl,0,4);
                $txt_tgl_b=substr($txtTgl,4,2);
                $txt_tgl_c=substr($txtTgl,6,2);
                $txtTgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
                if($tgl_carisd != '--'){
                    $where.=" AND tanggal BETWEEN '".$txtTgl."' AND '".$tgl_carisd."' ";
                }else{
                    $where.=" and tanggal='".$txtTgl."'";
                }
            }
            if($param['txtCari']!=''){
                $where.=" and notiket like '%".trim($param['txtCari'])."%'";
            }

            $query  ="select count(*) as jmlhrow from ".$dbname.".pabrik_gradingtbs where pabrik='".$_SESSION['empl']['lokasitugas']."'  ".$where." group by notiket order by tanggal desc";
            $result =fetchData($query);
            $jlhbrs = count($result);

            
            $sql="select * from ".$dbname.".pabrik_gradingtbs where pabrik='".$_SESSION['empl']['lokasitugas']."' ".$where." group by notiket order by tanggal desc limit ".$offset.",".$limit."";
            $hsl=fetchData($sql);
            if(count($hsl)>0){
                $no=0;
                $no=$maxdisplay;
                $bar=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                $bar->setFetchMode(PDO::FETCH_ASSOC);
                while($res=$bar->fetch()){
                    $no+=1;
                    $kendaraan = makeOption($dbname,'pabrik_timbangan','notransaksi,nokendaraan');
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=center>".$res['notiket']."</td>";
                    $tab.="<td align=center>".tanggalnormal($res['tanggal'])."</td>";
                    $tab.="<td align=center>".$kendaraan[$res['notiket']]."</td>";
                    $tab.="<td align=center hidden>";
                    $blokexpld=explode(',',$res['blok']);
                    foreach ($blokexpld as $b) {
                        @$nn++;
                        if($nn == 1){
                            $tab.=getIndukBlok($b)." [".$b."] [".getDataIndukBlok($b,'tahuntanam')."]";
                        }else{
                            $tab.="<br>".getIndukBlok($b)." [".$b."] [".getDataIndukBlok($b,'tahuntanam')."]";
                        }
                    }
                    $tab.="</td>";
                    $tab.="<td align=center>".getNamaKaryawan($res['createdby'])."</td>";
                    $tab.="<td align=center>".$res['createdtime']."</td>";
                    if($res['posting']==1){
                        $tab.="<td></td>";
                        $tab.="<td></td>";
                        // if(in_array($_SESSION['empl']['jabatan'],$personPosting,true)){
                            // $icon="images/icons/04/16/04.png";
                            // $title="Unposting";
                        // }else {
                            $icon="images/icons/04/16/02.png";
                            $title="Posted";
                        // }
                        $tab.="<td  align=center width=25px><img src=".$icon." class=zImgBtn height='30'  title='".$title."'></td>";
                        $tab.=" <td align=center>
                                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"masterPDF('vhc_runht','".$res['notiket'].",". $res['kodevhc']."','','vhc_slave_pekerjaanPrint',event);\">
                                </td>";
                    }else{
                        $tab.=" <td align=center>
                                    <img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('". $res['notiket']."','".$thn."');\">
                                </td>";
                        $tab.=" <td align=center>
                                    <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delHead('". $res['notiket']."','".$page."');\" >
                                </td>";
                        $tab.=" <td align=center>
                                    <img src=images/pdf.jpg class=zImgBtn  title='Print' onclick=\"pdflaporan('". $res['notiket']."');\">
                                </td>";
                        // $tab.=" <td align=center width=25px><img src=images/skyblue/posting.png class=zImgBtn height='30'  title='Posting' onclick=\"postingdata('" . $res['notransaksi'] . "','".$res['kodevhc']."','".$res['tanggal']."','".$page."');\"></td>";
                    }
                }
            }else{
                $tab.="<tr class=rowcontent><td colspan=".$colspan." align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }
            $totrows = ceil($jlhbrs / $limit);
            $footd = createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
            echo $tab."##".$footd;
        break;
        case'loaddetail':
            $no =$nox=0;$tab=$tab2='';
            $satuannya = makeOption($dbname,'pabrik_5fraksi','keterangan,type');
            $qry="select jumlah,kode,keterangan from ".$dbname.".pabrik_gradingtbs where notiket='".$param['notiket']."'";
            $hsljlh=fetchData($qry);
            foreach ($hsljlh as $vi) {
                $jlh[$vi['kode']][$vi['keterangan']]=$vi['jumlah'];
            }
            // if(count($hsl)<1){
                $sql="select * from ".$dbname.".pabrik_5fraksi order by nourut";
                $hsl=fetchData($sql);
            // }
            if(count($hsl) > 0){
                foreach ($hsl as $res) {
                    if($res['kode']=='grading'){
                        $no+=1;
                        $tab.="
                        <tr class=rowcontent>
                            <td align=center>".$no."</td>
                            <td align=center id=kode".$no.">".$res['keterangan']."</td>
                            <td align=right><input type=text class=myinputtextnumber id=jumlah".$no." name=jumlah  style=\"width:80px;\" onclick='this.select()' onkeypress=\"return angka_doang(event)\" value=".$jlh[$res['kode']][$res['keterangan']]."></td>
                            <td align=center>".$res['type']."</td>
                            <td align=right id=persen".$no." style='display:none'></td>
                        </tr>";
                    }
                    if($res['kode']=='kalibrasi'){
                        $nox+=1;
                        $tab2.="
                        <tr class=rowcontent>
                            <td align=center>".$nox."</td>
                            <td align=center id=kode2".$nox.">".$res['keterangan']."</td>
                            <td align=right><input type=text class=myinputtextnumber onclick='this.select()' id=jlh".$nox." name=jlh style=\"width:80px;\" onkeypress=\"return angka_doang(event)\" value=".$jlh[$res['kode']][$res['keterangan']]."></td>
                            <td>".$satuannya[$res['keterangan']]."</td>
                        </tr>";
                    }
                }
                $tab.="
                <tr class=rowcontent hidden>
                    <td align=center colspan=2>".$_SESSION['lang']['total']."</td>
                    <td align=right id=ttlsampel></td>
                    <td align=right id=ttlpersen></td>
                </tr>";
            }else{
                $tab.= "<tr class=rowcontent><td colspan=25 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }
            echo $tab."##".$tab2;
        break;
        case'insert_header':
            #== Create No transaksi
            $thn=substr($tgl_kerja,0,4);
            $bln=substr($tgl_kerja,4,2);
            $periode=$thn."-".$bln;

            $notransaksi=$param['kodeorg']."/RUN/".$thn."/".$bln."/";
            $sql= selectQuery($dbname,"vhc_runht","notransaksi","notransaksi like '%".$notransaksi."%'","notransaksi desc","","1");
            $hsl= fetchData($sql)[0];
            if(!isset($hsl['notransaksi'])) {
                $awal=1;
            } else {
                $awal=substr($hsl['notransaksi'],-4,4);
                $awal=intval($awal);

                $cekbln=substr($hsl['notransaksi'],-7,2);
                $cekthn=substr($hsl['notransaksi'],-12,4);
                if($thn!=$cekthn) {
                    $awal=1;
                } else {
                    $awal++;
                }
            }
            $counter=addZero($awal,4);
            
            #== Cek periode gajinya
            cekperiodegaji($param['kodeorg'],$thn."-".$bln);
            $notrans_new=$param['kodeorg']."/RUN/".$thn."/".$bln."/".$counter;
            
            if($notrans_new==''){
                exit("Warningsystem :Notransaksi Tidak Boleh Kosong");
            }
            //ending create notransaksi

            #== mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
            // if($tgl_kerja<$_SESSION['org']['period']['start']){
                // echo "Warningsystem : Periode akutansi bulan ".numToMonth(intval($bln),'I','long')." sudah ditutup.";
                // break;                        
            // }

            #== Validasi EOD
            // validasiInput($param['kodeorg'],'','TRK',tanggalsystemn(tanggalnormal($tgl_kerja)),$exit='1');
            
            #== Cek Periode akuntansi
            $str=selectQuery($dbname,"setup_periodeakuntansi","*","periode='$periode' and kodeorg='{$param['kodeorg']}' and tutupbuku='1'");
            $numrows = count(fetchData($str));
            if($numrows>0){
                // exit("Warningsystem :Periode sudah tutup buku");
            }

            #== Cek notransaksi baru sudah ada belum
            $sql=selectQuery($dbname,'pabrik_gradingtbs','notiket',"notiket='".$param['notiket']."' and tanggal='".tanggaldb($param['tanggal'])."'");
            $numrows = count(fetchData($sql));
            if($numrows<1){
            }else{
                echo"Warningsystem : Nomor tiket sudah pernah diinput, silahkan edit di list data !";
                exit();
            }
        break;
        case'deleteHead':
            $sdel="delete from ".$dbname.".pabrik_gradingtbs where notiket='".$param['notiket']."'";
            try{$owlPDO->exec($sdel);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        break;
        case'insert_pekerjaan':
            try {
                $owlPDO->beginTransaction();

                $deldulu = deleteQuery($dbname,'pabrik_gradingtbs',"pabrik='".$param['pabrik']."' and notiket='".$param['notiket']."'");
                $owlPDO->exec($deldulu);  
            
                for ($i=1; $i <= $param['ttljumlah'] ; $i++) { 
                    $data = array(
                        'pabrik'    => $param['pabrik'],
                        'tanggal'   => tanggaldb($param['tanggal']),
                        'notiket'   => $param['notiket'],
                        'blok'      => ($param['blok']=='null'||$param['blok']==''?'':$param['blok']),
                        'kode'      => 'grading',
                        'keterangan'=> $param['kode'.$i],
                        'jumlah'    => $param['jumlah'.$i],
                        'createdby' => $user_entry
                    );
                    
                    $sins = insertQuery($dbname, 'pabrik_gradingtbs', $data, array_keys($data));
                    $owlPDO->exec($sins); 
                }
            
                for ($ii=1; $ii <= $param['ttljlh'] ; $ii++) { 
                    $data = array(
                        'pabrik'    => $param['pabrik'],
                        'tanggal'   => tanggaldb($param['tanggal']),
                        'notiket'   => $param['notiket'],
                        'blok'      => ($param['blok']=='null'||$param['blok']==''?'':$param['blok']),
                        'kode'      => 'kalibrasi',
                        'keterangan'=> $param['kode2'.$ii],
                        'jumlah'    => $param['jlh'.$ii],
                        'createdby' => $user_entry
                    );
                    
                    $sins2 = insertQuery($dbname, 'pabrik_gradingtbs', $data, array_keys($data));
                    $owlPDO->exec($sins2); 
                }
                #execute
                $owlPDO->commit();
            } catch (PDOException $e) {
                $owlPDO->rollback();
                echo "Error, " . addslashes($e->getMessage());
                die();
            }
        break;
        case'pdflaporan':
            $no=$nox=0;
            $tab='<style>
			@page {
				margin-top: 30px;
				margin-left: 50px;
				margin-right: 50px;
				margin-bottom: 100px;
			}
			body {
                font-family: Helvetica, sans-serif;
                font-size:11px;
			}
			
			footer {
				position: fixed; 
				bottom: -40px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
		    </style>';

            $satuannya = makeOption($dbname,'pabrik_5fraksi','keterangan,type');
            $kendaraan = makeOption($dbname,'pabrik_timbangan','notransaksi,nokendaraan');
            $qry="select * from ".$dbname.".pabrik_gradingtbs where notiket='".$param['notiket']."'";
            $hsljlh=fetchData($qry);
            foreach ($hsljlh as $vi) {
                $jlh[$vi['kode']][$vi['keterangan']]=$vi['jumlah'];
                $nopol=$kendaraan[$vi['notiket']];
                $tanggal=$vi['tanggal'];
            }
            // if(count($hsl)<1){
                $sql="select * from ".$dbname.".pabrik_5fraksi order by nourut";
                $hsl=fetchData($sql);
                foreach ($hsl as $res) {
                    if($res['kode']=='grading' && $res['type'] == 'JJG'){
                        $ttljjg += $jlh[$res['kode']][$res['keterangan']];
                    }
                    if($res['kode']=='grading' && $res['type'] == '%'){
                        @$nos++;
                        $ketsebelah[$nos]=$res['keterangan'];
                        $satsebelah[$nos]=$res['type'];
                        $jlhsebelah[$nos]=$jlh[$res['kode']][$res['keterangan']];
                    }
                    if($res['kode']=='kalibrasi'){
                        $ketkalibrasi[$res['nourut']]=$res['keterangan'];
                        $satkalibrasi[$res['nourut']]=$res['type'];
                        $jlhkalibrasi[$res['nourut']]=$jlh[$res['kode']][$res['keterangan']];
                    }
                }
            // }
            //Ambil data timbangan
            $sql1="select notransaksi,nokendaraan,tanggal,jumlahtandan1,beratbersih,divcode from ".$dbname.".pabrik_timbangan where nokendaraan ='".$nopol."' and tanggal like '".$tanggal."%' order by tanggal";
            $hsl1=fetchData($sql1);
            foreach ($hsl1 as $v1) {
                $noxx++;
                $ritke[$v1['notransaksi']]=$noxx;
                $divisi[$v1['notransaksi']]=$v1['divcode'];
                $jjg[$v1['notransaksi']]=$v1['jumlahtandan1'];
                $netto[$v1['notransaksi']]=$v1['beratbersih'];
            }
           
            $tab.="<div>
                    <table cellpadding=4 border=0 width=100%>
                        <tbody>
                            <tr>
                                <td align=center colspan=9 style='font-size:20px;font-weight:bold'>LAPORAN GRADING TBS DALAM</td>
                            </tr>
                            <tr>
                                <td>Rit</td>
                                <td align=center style='font-size:20px;padding-right:90px'><b><i>".$ritke[$param['notiket']]."</i></b></td>
                                <td colspan=7></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td>".tglnmbln($tanggal,'I','long')."</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Nopol</td>
                                <td align=right></td>
                                <td align=right>".$nopol."</td>
                            </tr>
                            <tr>
                                <td>Divisi</td>
                                <td>".getNamaOrg($divisi[$param['notiket']])."</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Jumlah Tandan</td>
                                <td align=right></td>
                                <td align=right>".$jjg[$param['notiket']]."</td>
                            </tr>
                            <tr>
                                <td>Krani</td>
                                <td align=center></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>No Tiket</td>
                                <td align=right></td>
                                <td align=right>".$param['notiket']."</td>
                            </tr>
                            <tr>
                                <td>Blok</td>
                                <td align=center></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Tonase</td>
                                <td align=right></td>
                                <td align=right>".number_format($netto[$param['notiket']])."</td>
                            </tr>
                            <tr>
                                <td>TT</td>
                                <td align=center></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>BJR</td>
                                <td align=right></td>
                                <td align=right>".number_format($netto[$param['notiket']]/$ttljjg,2)."</td>
                            </tr>
                            <tr>
                                <td colspan=9></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellspacing=0 cellpadding=2 width=100% border=1>
                        <thead>
                            <tr>
                                <td align=center style='background-color:#92D050;width:5px'><b>".$_SESSION['lang']['nourut']."</b></td>
                                <td align=center style='background-color:#92D050;width:140px'><b>Kriteria Grading</b></td>
                                <td align=center style='background-color:#92D050;width:100px'><b>Actual Account</b></td>
                                <td align=center style='background-color:#92D050'><b>Total Sampel</b></td>
                                <td align=center style='background-color:#92D050;width:70px'><b>Presentase</b></td>
                                <td align=center style='background-color:#92D050' colspan=3><b>Keterangan</b></td>
                                <td align=center style='background-color:#92D050'><b>Potongan</b></td>
                            </tr>
                        </thead>
                        <tbody>";
                        if(count($hsl) > 0){
                            foreach ($hsl as $res) {
                                if($res['kode']=='grading' && $res['type'] == 'JJG'){
                                    $no+=1;
                                    $tab.="
                                    <tr class=rowcontent>
                                        <td align=center>".$no."</td>
                                        <td>".$res['keterangan']."</td>
                                        <td align=right style=\"width:80px;\">".number_format($jlh[$res['kode']][$res['keterangan']])."</td>";
                                        if($no == 1){
                                            $tab.="<td align=center valign=middle rowspan=12>".number_format($ttljjg)."</td>";
                                        }
                                        $tab.="
                                        <td align=right>".number_format($jlh[$res['kode']][$res['keterangan']]/$ttljjg*100,2)." %</td>";
                                        $ttlpersen +=($jlh[$res['kode']][$res['keterangan']]/$ttljjg*100);
                                        $tab.="<td align=left style='width:90px'>".$ketsebelah[$no]."</td>";
                                        $tab.="<td align=right style='width:70px'>".($jlhsebelah[$no] >0 || $jlhsebelah[$no] != '' ? number_format($jlhsebelah[$no]) : '' )."</td>";
                                        $tab.="<td align=center style='width:5px'>".$satsebelah[$no]."</td>";
                                        if($no == 1){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $netto[$param['notiket']] * 5/100;
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else if($no == 6){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $jlh[$res['kode']][$res['keterangan']] * 50/100 * ($netto[$param['notiket']]/$ttljjg);
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else if($no == 7 || $no == 10){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $jlh[$res['kode']][$res['keterangan']] * ($netto[$param['notiket']]/$ttljjg) * 100/100;
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else if($no == 8 || $no == 12){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $jlh[$res['kode']][$res['keterangan']] * ($netto[$param['notiket']]/$ttljjg) * 20/100;
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else if($no == 9){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $netto[$param['notiket']] * 0.5/100;
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else if($no == 11){
                                            if($jlh[$res['kode']][$res['keterangan']] > 0){
                                                $potongan = $jlh[$res['kode']][$res['keterangan']] * ($netto[$param['notiket']]/$ttljjg) * 70/100;
                                            }else{
                                                $potongan = 0;
                                            }
                                            $ttlpot += $potongan;
                                            $tab.="<td align=right style='width:60px'>".number_format($potongan,2)."</td>";
                                        }else{
                                            $tab.="<td align=right>0.00</td>";
                                        }
                                    $tab.="
                                    </tr>";
                                }
                            }
                            $tab.="
                            <tr class=rowcontent>
                                <td align=right></td>
                                <td align=left><b>".$_SESSION['lang']['total']."</b></td>
                                <td align=right></td>
                                <td align=right></td>
                                <td align=right><b>".number_format($ttlpersen,2)." %</b></td>
                                <td align=right colspan=4><b>".number_format($ttlpot,2)."</b></td>
                            </tr>";
                        }else{
                            $tab.= "<tr class=rowcontent><td colspan=25 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
                        }
                        $tab.="
                        </tbody>
                    </table><br>
                    <table cellspacing=0 cellpadding=2 width=91.6%>
                        <thead>
                            <tr>
                                <td align=center style='background-color:#92D050;border:1px solid black;' colspan=8><b>KETERANGAN TBS KALIBRASI</b></td>
                            </tr>
                        </thead>
                        <tbody>";
                        if(count($hsl) > 0){
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>1.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>".$ketkalibrasi['1']."</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format($jlhkalibrasi['1'])."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>".$satkalibrasi['1']."</td>
                                <td align=left style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=right colspan=3 style='border-right:1px solid black;'></td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>2.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>".$ketkalibrasi['2']."</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format($jlhkalibrasi['2'])."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>".$satkalibrasi['2']."</td>
                                <td align=left style='width:60px;border-left:1px solid black;border-right:1px solid black;'>".$ketkalibrasi['3']."</td>
                                <td align=right colspan=2>".number_format($jlhkalibrasi['3'])."</td>
                                <td align=left style='width:13px;border-right:1px solid black;'>".$satkalibrasi['3']."</td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>3.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Sampel Sampah</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format($jlhkalibrasi['1']-$jlhkalibrasi['2'])."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>".$satkalibrasi['2']."</td>
                                <td align=left style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=right colspan=2>".number_format($jlhkalibrasi['3']/$jlhkalibrasi['1']*100,2)."</td>
                                <td align=left style='width:13px;border-right:1px solid black;'>%</td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>4.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Prosentase Sampah</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format(($jlhkalibrasi['1']-$jlhkalibrasi['2'])/$jlhkalibrasi['1'] * 100,2)."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>%</td>
                                <td align=right style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=left colspan=2></td>
                                <td align=right style='width:13px;border-right:1px solid black;'></td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>5.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Berat Brondolan</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>".$satkalibrasi['2']."</td>
                                <td align=right style='width:66px'>".number_format($jlhkalibrasi['4'])."</td>
                                <td align=left style='width:60px'>Tkr</td>
                                <td align=right style='width:55px'>".number_format($jlhkalibrasi['5'])."</td>
                                <td align=left style='width:13px;border-right:1px solid black;'>Kg</td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>6.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Berat Sampah</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format(($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])*(($jlhkalibrasi['1']-$jlhkalibrasi['2'])/$jlhkalibrasi['1'] * 100)/100)."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>".$satkalibrasi['2']."</td>
                                <td align=center colspan=2>% Sampah</td>
                                <td align=right style='width:55px'>".number_format((($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])*(($jlhkalibrasi['1']-$jlhkalibrasi['2'])/$jlhkalibrasi['1'] * 100)/100)/$netto[$param['notiket']]*100,2)."</td>
                                <td align=left style='width:13px;border-right:1px solid black;'>%</td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>7.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Berat Brondolan Actual</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format(($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])-(($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])*(($jlhkalibrasi['1']-$jlhkalibrasi['2'])/$jlhkalibrasi['1'] * 100)/100))."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>Kg</td>
                                <td align=right style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=left colspan=2></td>
                                <td align=right style='width:13px;border-right:1px solid black;'></td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>8.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>% Brondolan Aktual</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format((($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])-(($jlhkalibrasi['4']*96+$jlhkalibrasi['5'])*(($jlhkalibrasi['1']-$jlhkalibrasi['2'])/$jlhkalibrasi['1'] * 100)/100))/$netto[$param['notiket']] * 100,2)."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>%</td>
                                <td align=right style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=left colspan=2></td>
                                <td align=right style='width:13px;border-right:1px solid black;'></td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;'>9.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;'>Brondolan Kering</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;'>".number_format(($jlhkalibrasi['4']*96+$jlhkalibrasi['5']) * (($jlhkalibrasi['3']/$jlhkalibrasi['1'])*100) / 100)."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;'>Kg</td>
                                <td align=right style='width:60px;border-left:1px solid black;border-right:1px solid black;'></td>
                                <td align=left colspan=2></td>
                                <td align=right style='width:13px;border-right:1px solid black;'></td>
                            </tr>";
                            $tab.="
                            <tr>
                                <td align=center style='width:12px;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'>10.</td>
                                <td style='width:102px;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'>Brondolan Kering</td>
                                <td align=right style='width:75px;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'>".number_format((($jlhkalibrasi['4']*96+$jlhkalibrasi['5']) * (($jlhkalibrasi['3']/$jlhkalibrasi['1'])*100) / 100) / ($netto[$param['notiket']]) *100,2)."</td>
                                <td align=left style='width:40px;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'>%</td>
                                <td align=right style='width:60px;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;'></td>
                                <td align=left colspan=2 style='border-bottom:1px solid black;'></td>
                                <td align=right style='width:13px;border-right:1px solid black;border-bottom:1px solid black;'></td>
                            </tr>";
                        }else{
                            $tab.= "<tr class=rowcontent><td colspan=25 align=center>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
                        }
                        $tab.="
                        </tbody>
                    </table>
                    </div>";
            $dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'potrait');
            $dompdf->render();
            $dompdf->stream("Laporan_Timbangan",array("Attachment"=>0));
        break;
        default:
        break;
    }
?>
<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
?>

<?php
    $method         = checkPostGet('method', '');
    $param          =$_POST;if(count($param)==0){$param = $_GET;}
    $nmprjct        = makeOption($dbname,'project','kode,nama');
    $path           = "fileupload/PHP/";
?>

<?php
    switch ($method) {
        case 'loadData':
            $tab        ="";
            $footer     ="";
            $limit      = 10;
            $page       = 0;
            $colspan    = 11;

            if (isset($param['page'])) {
                $page   = $param['page'];
                if ($page < 0)
                    $page = 0;
            }
            
            $offset     = floatval($page) * $limit;
            $maxdisplay =(floatval($page) * $limit);
            $no         =((floatval($page) * $limit));
            
            if($param['notransaksisch']!=''){ 
                $where.="and notransaksi LIKE '%".$param['notransaksisch']."%'";
            }

            $str        = "SELECT * FROM ".$dbname.".lgl_penawaranharga WHERE 1=1 ".$where." GROUP BY notransaksi ORDER BY createdtime DESC";
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
                    $tab.="<tr class=rowcontent>
                                <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                            </tr>";
            }else{
                $iList  = "SELECT * FROM " . $dbname . ".lgl_penawaranharga WHERE 1=1 ".$where." GROUP BY notransaksi ORDER BY notransaksi LIMIT ".$offset.",".$limit." ";
                $hasil  = fetchdata($iList);
                
                foreach ($hasil as $dList){
                    $strnya =selectQuery($dbname,'lgl_penawaranhargadt','MAX(nourut) AS max',"notransaksi='".$dList['notransaksi']."'",'nourut desc');
                    $resnya =fetchData($strnya);
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                        $tab.=" <td align=center style='width:5px'>" . $no . "</td>";
                        $tab.=" <td align=center>".$dList['notransaksi']."</td>";
                        $tab.=" <td align=center>".tanggalnormal($dList['tanggal'])."</td>";
                        $tab.=" <td align=left>".$dList['keterangan']." - ".$nmprjct[$dList['keterangan']]."</td>";
                        $tab.=" <td align=right>".number_format($dList['rupiah']) . "</td>";
                        $tab.=" <td align=center style=\"cursor:pointer;\" onclick=\"gethistoriapproval('".$dList['notransaksi']."')\">".$arrstatus[$dList['statuspersetujuan']]."</td>";
                        if($dList['statuspersetujuan'] == 0 || $dList['statuspersetujuan'] == 3){
                            $tab.=" <td align=center>
                                        <img src=images/application/application_edit.png class=zImgBtn title='Edit' onclick=\"fillField('" . $dList['notransaksi'] . "','".tanggalnormal($dList['tanggal'])."','".$dList['keterangan']."');\">
                                    </td>";
                            $tab.=" <td align=center>
                                        <img src=images/application/application_delete.png class=zImgBtn title='Delete' onclick=\"del('" . $dList['notransaksi'] . "','".$param['page']."');\">
                                    </td>";
                            $tab.=" <td align=center>
                                        <img src=images/nxbtn.png class=zImgBtn title='Input Perbandingan harga' onclick=\"bukaharga('" . $dList['notransaksi'] . "','".$resnya[0]['max']."');\">
                                    </td>";
                        }else{
                            $tab.="<td align=center></td>";
                            $tab.="<td align=center></td>";
                            $tab.="<td align=center></td>";
                        }
                        $tab.="<td align=center>
                                    <img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('".$dList['notransaksi']."','".$resnya[0]['max']."');\">
                                </td>";
                        $tab.="<td align=center width=20px><img src=images/upload-2-xxl.png class=zImgBtn title='Upload' onclick=\"showupload('".$dList['notransaksi']."','1');\"></td>";
                    $tab.="</tr>
                        </tbody>";
                }
                $footer .= "<tr>
                                <td colspan=".$colspan." align=center>
                                    ".$frompage." to ".$topage." Of ".  $jlhbrs."
                                </td>
                            </tr>";
                $footer .= "<tr>
                                <td colspan=".$colspan." align=center>";
                                if($page!=0){
                                    $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                                }
                    $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                                if((floatval($page)+1) != $totrows){
                                    $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                                }
                $footer .=     "</td>
                            </tr>";
            }
            echo $tab."####".$footer;
        break;
        case 'saveheader':
            //Bentuk Kode
            if($param['notransaksi'] == ''){
                $nourut = 1;
                $tempPrd=explode('-',tanggaldb($param['tanggal']));
                $format = "/PHP/".$_SESSION['empl']['lokasitugas']."/".$tempPrd[0];
                $str    =selectQuery($dbname,'lgl_penawaranharga','nourut,notransaksi',"notransaksi like '%".$format."%'",'notransaksi desc limit 1 ');
                $bar    =fetchData($str)[0];
                $tempNo1=explode('/',$bar['notransaksi']);
                if(intval($bar['notransaksi'])==0 or intval($bar['notransaksi'])==999){
                    $counter = "001";
                }else{
                    $counter = addZero(intval($tempNo1[0])+1,3);
                }
    
                $notransnew     = $counter."/PHP/".$_SESSION['empl']['lokasitugas']."/".$tempPrd[0];
            }else{
                $notransnew     = $param['notransaksi'];
                $str    =selectQuery($dbname,'lgl_penawaranharga','nourut',"notransaksi='".$notransnew."'",'nourut desc limit 1 ');
                $nourut =((fetchData($str)[0]['nourut'])+1);
            }
            
            $sql=selectQuery($dbname,'lgl_penawaranharga','notransaksi,nourut,supplierid',"notransaksi='".$notransnew."' and supplierid='".$param['supplierid']."'");
            $hsl=fetchData($sql);
            if(count($hsl)>0){
                exit("Warningsystem : Kode Assignment ".getNamaSupplier($param['supplierid'])." sudah ada di notransaksi ".$notransnew);
            }
            
            $ht = "INSERT INTO $dbname.lgl_penawaranharga 
                    (`notransaksi`,`nourut`,`tanggal`,`supplierid`,`keterangan`,`statuspersetujuan`,`createdby`,`createdtime`) VALUES 
                    ('" . $notransnew . "','" . intval($nourut). "','".tanggaldb($param['tanggal'])."','" . $param['supplierid'] . "','" . $param['nama'] . "','0','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
            try{
                $owlPDO->exec($ht);
                echo trim($notransnew);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'opendetail':
            $t="";
            $sel=selectQuery($dbname,'lgl_penawaranharga','*',"notransaksi='".$param['notransaksi']."'");
            $rel=fetchData($sel);
            if(count($rel)>0){
                foreach ($rel as $v) {
                    $t.= "<tr class=rowcontent>";
                        $t.= "<td align=center>".$v['nourut']."</td>";
                        $t.= "<td align=center>".$v['notransaksi']."</td>";
                        $t.= "<td align=center>".$v['keterangan']." - ".$nmprjct[$v['keterangan']]."</td>";
                        $t.= "<td align=center>".getNamaSupplier($v['supplierid'])."</td>";
                        $t.= "<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn title='Hapus Kontraktor ".getNamaSupplier($v['supplierid'])."' onclick=\"hapus('" . $v['notransaksi'] . "','".$v['supplierid']."','".getNamaSupplier($v['supplierid'])."');\">
                              </td>";
                    $t.= "</tr>";
                }
                echo $t."#".count($rel);
            }else{
                $t.= "<tr class=rowcontent><td align=center colspan=5>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
                echo $t;
            }
        break;  
        case 'bukaharga':
            $str=selectQuery($dbname,'lgl_penawaranharga','*',"notransaksi='".$param['notransaksi']."'");
            $rst=fetchData($str);
            
            $str2=selectQuery($dbname,'lgl_penawaranhargadt','*',"notransaksi='".$param['notransaksi']."'");
            $rst2=fetchData($str2);
            foreach ($rst2 as $v2) {
                $data[$v2['nourut']]=$v2;
            }

            $tab = "";
            $tab.="
            <table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
                    <tr class=rowheader>
                        <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</th>
                        <th align=center rowspan=3 style='width:2%'>Tax</th>
                        <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        foreach ($rst as $v) {
                            $tab.=" <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        }
                    // $tab.=" <th align=center rowspan=1 colspan=4>Summary</th>";
                    $tab.=" 
                    </tr>
                    <tr class=rowheader>";
                        for ($i=1; $i <= count($rst) ; $i++) { 
                            $tab.="
                            <th align=center colspan=2>Penawaran</th>
                            <th align=center colspan=2>Negosiasi</th>
                            <th align=center rowspan=2>Var -RP</th>";
                        }
                    $tab.="
                    </tr>
                    <tr class=rowheader>
                        <th>RP / Sat</th>
                        <th style='width:1%'>Fisik</th>
                        <th>Nominal</th>";
                        for ($i=1; $i <= count($rst)*2 ; $i++) { 
                            $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>";
                        }
                    // $tab.=" <th>RP / Sat</th>
                    //         <th>Nominal</th>
                    //         <th>Var RP</th>
                    //         <th>Var (%)</th>
                    //     </tr>";
                $tab.="
                </thead>
                <tbody>";
                    $tab.="
                    <tr class=rowcontent>
                        <td align=center>".$rst[0]['keterangan']." - ".$nmprjct[$rst[0]['keterangan']]."</td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsat');hitungrat()\" maxlength=12 id=rpsat class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rpsat'] == '0' ? '' : $rst[0]['rpsat'])."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('luas');hitungrat()\"  maxlength=7 id=luas class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['luas'] == '0' ? '' : $rst[0]['luas'])."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiah class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rupiah'] == '0' ? '' : $rst[0]['rupiah'])."' disabled>
                        </td>";
                    foreach ($rst as $v) {
                        @$max++;
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatoff_".$v['nourut']."');hitungoff('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=rpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpsatpenawaran']."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rppenawaran']."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatnego_".$v['nourut']."');hitungnego('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=rpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpsatnegosiasi']."'>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=rupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".$data[$v['nourut']]['rpnegosiasi']."' disabled>
                        </td>
                        <td id=varrp_".$v['nourut']." align=right></td>";
                    }
                $tab.="
                </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center>".$_SESSION['lang']['pajak']."</td>
                    <td align=center>
                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('tax')\" maxlength=4 onblur=\"hitungtax('".$max."')\" id=tax class=myinputtextnumber style=\"width:85%;\" value='".$rst[0]['tax']."'>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahoff_".$v['nourut']."')\" maxlength=12 id=taxrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahnego_".$v['nourut']."')\" maxlength=12 id=taxrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td></td>";
                    }
                $tab.=" </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center><b>Harga setelah pajak</b></td>
                    <td align=center></td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatoff_".$v['nourut']."');\" maxlength=12 id=fixrpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahoff_".$v['nourut']."')\" maxlength=12 id=fixrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatnego_".$v['nourut']."')\" maxlength=12 id=fixrpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahnego_".$v['nourut']."')\" maxlength=12 id=fixrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td id=fixvarrp_".$v['nourut']." align=right></td>";
                    }
                $tab.=" </tr>";
                $tab.=" <tr><td align=center colspan=40>
                            <input type=hidden id=methoddt value='simpandt'>
                            <input type=hidden id=notransaksidt value=''>
                            <button class=mybutton onclick=\"simpandetail('".$max."')\" align=center>".$_SESSION['lang']['save']."</button>
                            </td>
                        </tr> ";
                $tab.="
                </tbody>
            </table>";
            echo $tab;
        break;
        case 'html':
            $str=selectQuery($dbname,'lgl_penawaranharga','*',"notransaksi='".$param['notransaksi']."'");
            $rst=fetchData($str);
            
            $str2=selectQuery($dbname,'lgl_penawaranhargadt','*',"notransaksi='".$param['notransaksi']."'");
            $rst2=fetchData($str2);
            foreach ($rst2 as $v2) {
                $data[$v2['nourut']]=$v2;
            }

            $str3=selectQuery($dbname,'lgl_penawaranhargadt','rpnegosiasi,nourut,supplierid,rpsatnegosiasi',"notransaksi='".$param['notransaksi']."'",'rpnegosiasi','','1');
            $rst3=fetchData($str3);
            foreach ($rst3 as $v3) {
                $datarpmin=$v3['rpnegosiasi'];
                $datarpnegomin=$v3['rpsatnegosiasi'];
            }
            $tab = "";
            $tab.="
            <table class=sortable cellspacing=1 border=0 width=100%>
                <thead>
                    <tr class=rowheader>
                        <th align=center rowspan=3 style='width:15%'>" . $_SESSION['lang']['nama'] . " ".$_SESSION['lang']['project']."</th>
                        <th align=center rowspan=3 style='width:2%'>Tax</th>
                        <th align=center rowspan=2 colspan=3 style='width:14%'>RAT</th>";
                        foreach ($rst as $v) {
                            $tab.=" <th align=center rowspan=1 colspan=5><b style='font-size:13px'>" . getNamaSupplier($v['supplierid']) . "</b></th>";
                        }
                    $tab.=" <th align=center rowspan=2 colspan=4>Rekomendasi Pemenang</th>";
                    $tab.=" 
                    </tr>
                    <tr class=rowheader>";
                        for ($i=1; $i <= count($rst) ; $i++) { 
                            $tab.="
                            <th align=center colspan=2>Penawaran</th>
                            <th align=center colspan=2>Negosiasi</th>
                            <th align=center rowspan=2>Var -RP</th>";
                        }
                    $tab.="
                    </tr>
                    <tr class=rowheader>
                        <th>RP / Sat</th>
                        <th style='width:1%'>Fisik</th>
                        <th>Nominal</th>";
                        for ($i=1; $i <= count($rst)*2 ; $i++) { 
                            $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>";
                        }
                    $tab.=" <th>RP / Sat</th>
                            <th>Nominal</th>
                            <th>Var RP</th>
                            <th>Var (%)</th>
                        </tr>";
                $tab.="
                </thead>
                <tbody>";
                    $tab.="
                    <tr class=rowcontent>
                        <td align=center>".$rst[0]['keterangan']." - ".$nmprjct[$rst[0]['keterangan']]."</td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsat');hitungrat()\" maxlength=12 id=prpsat class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rpsat'] == '0' ? '' : number_format($rst[0]['rpsat']))."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('luas');hitungrat()\"  maxlength=7 id=pluas class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['luas'] == '0' ? '' : $rst[0]['luas'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiah class=myinputtextnumber style=\"width:95%;\" value='".($rst[0]['rupiah'] == '0' ? '' : number_format($rst[0]['rupiah']))."' disabled>
                        </td>";
                    foreach ($rst as $v) {
                        @$max++;
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatoff_".$v['nourut']."');hitungoff('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=prpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpsatpenawaran'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rppenawaran'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" onblur=\"z.numberFormat('rpsatnego_".$v['nourut']."');hitungnego('".$v['nourut']."','".getNamaSupplier($v['supplierid'])."');hitungtax('".count($rst)."')\" maxlength=12 id=prpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpsatnegosiasi'])."' disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);\" maxlength=12 id=prupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" value='".number_format($data[$v['nourut']]['rpnegosiasi'])."' disabled>
                        </td>
                        <td id=pvarrp_".$v['nourut']." align=right></td>";
                    }
                    $tab.="<td align=right>".number_format($datarpnegomin)."</td>";
                    $tab.="<td align=right>".number_format($datarpmin)."</td>";
                    $tab.="<td align=right>".number_format(($datarpmin - $rst[0]['rupiah']))."</td>";
                    $tab.="<td align=right>".number_format(($datarpmin/$rst[0]['rupiah']*100))."</td>";
                $tab.="
                </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center>".$_SESSION['lang']['pajak']."</td>
                    <td align=center>
                        <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('tax')\" maxlength=3 onblur=\"hitungtax('".$max."')\" id=ptax class=myinputtextnumber style=\"width:85%;\" value='".($rst[0]['tax'] == 0 ? '' : number_format($rst[0]['tax']))."' disabled>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahoff_".$v['nourut']."')\" maxlength=12 id=ptaxrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center></td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('taxrupiahnego_".$v['nourut']."')\" maxlength=12 id=ptaxrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td></td>";
                    }
                    $tab.="<td align=right></td>";
                    $tab.="<td align=right>".number_format($datarpmin*$rst[0]['tax']/100)."</td>";
                    $tab.="<td align=right></td>";
                    $tab.="<td align=right></td>";
                $tab.=" </tr>";
                $tab.="
                <tr class=rowcontent>
                    <td align=center><b>Harga setelah pajak</b></td>
                    <td align=center></td>
                    <td></td>
                    <td></td>
                    <td></td>";
                    foreach ($rst as $v) {
                        $tab.="
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatoff_".$v['nourut']."');\" maxlength=12 id=pfixrpsatoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahoff_".$v['nourut']."')\" maxlength=12 id=pfixrupiahoff_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrpsatnego_".$v['nourut']."')\" maxlength=12 id=pfixrpsatnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td align=center>
                            <input type=text onclick=\"this.select()\" onkeypress=\"return angka_doang(event);z.numberFormat('fixrupiahnego_".$v['nourut']."')\" maxlength=12 id=pfixrupiahnego_".$v['nourut']." class=myinputtextnumber style=\"width:95%;\" disabled>
                        </td>
                        <td id=pfixvarrp_".$v['nourut']." align=right></td>";
                    }
                    $tab.="<td align=right>".number_format(($datarpmin-($datarpmin*$rst[0]['tax']/100))/$rst[0]['luas'])."</td>";
                    $tab.="<td align=right>".number_format($datarpmin-($datarpmin*$rst[0]['tax']/100))."</td>";
                    $tab.="<td align=right>".number_format(($datarpmin - $rst[0]['rupiah'])-($datarpmin*$rst[0]['tax']/100))."</td>";
                    $tab.="<td align=right>".number_format((($datarpmin-($datarpmin*$rst[0]['tax']/100))/$rst[0]['rupiah']*100))."</td>";
                $tab.="
                </tr>";
                $tab.="
                </tbody>
            </table><br>";
            
            $jenispersetujuanx 	= 'PHP';
			$pecahnotrans		= explode('/', $param['notransaksi']);
			$kodeorg 			= $pecahnotrans[2];
			$nmapprval        	= makeOption($dbname,'setup_jenisapproval','jenis,nama');
            
            if($rst[0]['statuspersetujuan'] == 0 || $rst[0]['statuspersetujuan'] == 3){
                if(count($rst2)>0){
                    $tab.="<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
                    $tab.="<tr class=rowcontent><td align=center colspan=3>";
                    $tab.="<h3 style='padding-left:40px'><b>Persetujuan ".$nmapprval[$jenispersetujuanx]."<br></b></h3>";
                    $tab.="<h4 style='padding-left:40px'><b> Unit : ".$kodeorg." - ".getNamaOrg($kodeorg)."</b></h4>";
                    $tab.="</td></tr>";
                    $tab.="<tr class=rowcontent >";
                        $tab.="<td>No Pengajuan</td>";
                        $tab.="<td width=5px>:</td>";
                        $tab.="<td id=notran_aju>".$param['notransaksi']."</td>";
                    $tab.="</tr>";
        
                    ##CEK PER DEPARTEMEN
                    $str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kodeorg."' and jenispersetujuan='".$jenispersetujuanx."' and departemen='".$departemen."'";
                    $res=fetchdata($str);
                    $perdepartemen=$res[0]['kodeunit'];
                    $where="";
                    if($perdepartemen>0){
                        $where.=" and departemen='".$departemen."'";
                    }else{
                        $where.=" and departemen=''";
                    }
        
                    ## APPROVAL DINAMIS SESUAI SETUP##
                    $optKryx	=array();
                    $optKrylevel=array();
        
                    $optper4=$optper3=$optper2=$optper1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
                    $str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$kodeorg."' and karyawaniduser='' ".$where." ";
                    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_ASSOC);
                    while($bar=$res->fetch()){
                        $whr		=" karyawanid='".$bar['karyawanid']."'";
                        $optnama 	= makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
                        
                        $optKryx[$bar['level']][$bar['karyawanid']]="<option value=".$bar['karyawanid'].">".$optnama[$bar['karyawanid']]."</option>";
                        $optKrylevel[$bar['level']]=$bar['level'];
        
                    }
        
                    
                    $jumlahlevel=count($optKrylevel);
                    if($jumlahlevel>0)
                    {
                        for ($i=1; $i <= $jumlahlevel; $i++) { 
                            $optKry='';
                            foreach ($optKryx[$i] as $key2 => $val) {
                                $optKry.=$val;
                            }
                            $tab .= "<tr class=rowcontent>
                                <td>Approval ke-".$i."</td>
                                <td width=5px>:</td>
                                <td><select id=kepada".$i." style='width:200px;'>".$optKry."</select></td>
                            </tr>";					
                        }
                    }
                    else
                    {			
                        $jumlahlevel=1;
                        $tab .= "<tr class=rowcontent>
                            <td>Approval ke-1</td>
                            <td width=5px>:</td>
                            <td><select id=kepada1 style='width:200px;'></select></td>
                        </tr>";
                    }
                        $tab .= "<tr class=rowcontent>
                            <td hidden><input id=jenispersetujuanx style=display:none value=".$jenispersetujuanx."></td><td><input id=numrow style=display:none value=".$jumlahlevel."></td>
                            <td align=left></td>
                            <td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
                        </tr>
                    </table>";
                }

            }else{
              
                $str=selectQuery($dbname,'setup_approval','*',"jenispersetujuan='".$jenispersetujuanx."' and kodeunit='".$kodeorg."'");
                $ree=fetchData($str);
                $tab.="<table cellspacing=1 border=0 class=sortable cellpadding=5 align=center>";
                $tab.="<tr class=rowheader>";
                $tab.="<td align=center>Notransaksi</td>";
                foreach ($ree as $v) {
                    $tab.="<td align=center>".$_SESSION['lang']['persetujuan']." ".$v['level']."</td>";
                }
                $tab.="</tr>";
                $str=selectQuery($dbname,'approval','*',"jenispersetujuan='".$jenispersetujuanx."' and notransaksi='".$param['notransaksi']."'");
                $rei=fetchData($str);
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center style='height:60px'>".$param['notransaksi']."</td>";
                foreach ($rei as $v) {
                    $tab.="<td align=center style='height:60px'>".getNamaKaryawan($v['karyawanid'])."<br>(".($v['status'] == 1 ? $arrstatus[$v['status']] : $arrstatus[$rst[0]['statuspersetujuan']]).")</td>";
                }
                $tab.="</tr>";
                $tab.="</table>";
                $sql=selectQuery($dbname,'lgl_penawaranhargadt','supplierid',"notransaksi='".$param['notransaksi']."' and flag='1'");
                $tab.="<br><table class=sortable cellspacing=1 cellpadding=5 align=center>
                        <tr class=rowheader><td>Pemenang Perbandingan Project</td></tr>
                        <tr class=rowcontent><td align=center>".getNamaSupplier(fetchData($sql)[0]['supplierid'])."</td></tr></table>";
            }

			echo $tab;
		break;
        case 'simpandt':
            try{
                $owlPDO->beginTransaction();
                
                for ($i=1; $i <= $param['max'] ; $i++) { 
                    //ambil supplierid
                    $str    = selectQuery($dbname,'lgl_penawaranharga','supplierid',"notransaksi ='".$param['notransaksi']."' and nourut='".$i."'",'nourut');
                    $res    = fetchData($str)[0];

                    $ht     = "UPDATE $dbname.lgl_penawaranharga SET 
                                luas='" . $param['luas'] . "',
                                rpsat='" . $param['rpsat'] . "',
                                rupiah='" . $param['rupiah'] . "',
                                tax='" . $param['tax'] . "',
                                updatedby='" . $_SESSION['standard']['userid'] . "' WHERE 
                                notransaksi='" . $param['notransaksi'] . "' AND nourut ='".$i."' AND supplierid='".$res['supplierid']."'";
                    $owlPDO->exec($ht);
                    
                    if($i== 1){
                        $ht = "DELETE FROM $dbname.lgl_penawaranhargadt WHERE notransaksi='".$param['notransaksi']."'";
                        $owlPDO->exec($ht);
                    }

                    $dataHT = array(
                        'notransaksi'=> $param['notransaksi'],
                        'nourut'=> $i,
                        'supplierid'=> $res['supplierid'],
                        'rpsatpenawaran'=> $param['rpsatoff_'.$i],
                        'rppenawaran'=> $param['rupiahoff_'.$i],
                        'rpsatnegosiasi'=> $param['rpsatnego_'.$i],
                        'rpnegosiasi'=> $param['rupiahnego_'.$i]
                    );
                    
                    $ins = insertQuery($dbname, 'lgl_penawaranhargadt', $dataHT, array_keys($dataHT));
                    $owlPDO->exec($ins);
                }

                $owlPDO->commit();
            }catch (PDOException $e){
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'del':
            $ht = "DELETE FROM $dbname.lgl_penawaranharga WHERE notransaksi='".$param['notransaksi']."' ";
            try {
                $owlPDO->exec($ht);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'delete':
            $ht = "DELETE FROM $dbname.lgl_penawaranharga WHERE notransaksi='{$param['notransaksi']}' AND supplierid='{$param['supplierid']}'";
            try {
                $owlPDO->exec($ht);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        break;
        case 'ajukan':
			$kepada 			= checkPostGet('kepada','');
			$jenispersetujuanx 	= checkPostGet('jenispersetujuanx','');

			if($kepada==''){
				throw new PDOException('Isikan nama penyetuju.');
			}
			try{
				$owlPDO->beginTransaction();
				//update flag menjadi 9
				$str2 = "UPDATE $dbname.lgl_penawaranharga SET statuspersetujuan='9' WHERE notransaksi = '" . $param['notransaksi'] . "'";
				$owlPDO->exec($str2);
				//cek apakah sudah terdapat approval sebelum jika ada delete semua approval yang ada
				$sql=selectQuery($dbname,'approval','*',"notransaksi='".$param['notransaksi']."' ");
				$hsl=fetchData($sql);
				if(count($hsl)>0){
					$string = "DELETE FROM $dbname.approval WHERE notransaksi='" . $param['notransaksi'] . "'";
					try{$owlPDO->exec($string);}catch (PDOException $e){echo "DB Header Error: " . addslashes($e->getMessage());}
				}
				//insert ke table approval
				$str='';
				$arrkepada=explode('###', $kepada);
				for ($i=0; $i < count($arrkepada); $i++) { 
					$str .= "INSERT INTO $dbname.approval (`nourut`,`notransaksi`, `jenispersetujuan`,`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					VALUES ('','".$param['notransaksi']."','".$jenispersetujuanx."','".($i+1)."','" . $arrkepada[$i]."','0','','','');";
				}
				$owlPDO->exec($str);
				$owlPDO->commit();
			}catch(PDOException $e){
				$owlPDO->rollback();
				echo "Error, " . addslashes($e->getMessage());
			}
		break;
        case 'showupload':
            $arrmodul = getmodulefil($emodul);
            foreach($arrmodul as $key=>$val){
                if ($key==$jnsupspkfinal) {
                    $optf="<option value='".$key."'>".$val['kriteria']."</option>";
                }else{
                    $optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
                }
            }

            if ($param['jenisupload']=='1') {
                $optkriteria=$optf;
            }

            $tab="";
            $tab.="<fieldset><legend>Upload</legend>
            <table border=0 >
                <tr>
                    <td>Kriteria</td>
                    <td>:</td>
                    <td>
                        <select id='kriteriaefil'>". $optkriteria."</select>
                    </td>
                </tr>
                <tr>
                    <td>Filename</td>
                    <td></td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."','".$param['jenisupload']."')\">Submit</button>
                    </td>
                </tr>
            </table>
            </fieldset>
                <p />";

            $tab.="<fieldset>
                <legend>".$_SESSION['lang']['list']."</legend>
                <table class='sortable' cellspacing='1' border='0' width=100%>
                    <thead>
                    <tr class=rowheader>
                        <td align='center' width=50px>No.</td>
                        <td align='center' width=50px>File Type</td>
                        <td align='center'>Kriteria</td>
                        <td align='center'>Filename</td>
                        <td align='center' width=50px>Action</td>
                    </tr>
                    </thead>
                    <tbody id='listfiles'>
                    </tbody>
                </table>
            </fieldset> ";

            echo $tab;
        break;
        case 'submitfile':
            if($param['notransaksi']==''){
                exit("Warning : Silahkan isikan detail transaksi terlebih dahulu.");
            }
            #cek data
            $sql = "select * from " . $dbname . ".lgl_penawaranhargadt where notransaksi='" . $param['notransaksi'] . "'";
            $res=fetchData($sql);
            if(count($res)==0){
                exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu.');
            }
            $str="select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."'";

            if ($jenisupload=='1') {
                // $str="select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and id='".$jnsupspkfinal."'";
            }

            $res=fetchData($str);
            if(count($res)>=10){
                exit("Warning : Limit upload hanya 10 file.");
            }
            $tgl = date("YmdHis");
            $his = date("His");
            $data = $_POST;
            if($data['fileupload']!=''){
                if($_FILES['file']['error']==0){
                    $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                    $filename = $_FILES['file']['name'];
                    //$filename = $pt."_".$tgl."".$filetype;
                    $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
                    if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                        $failnem = "Perbandingan_Harga_Project_".date('Y-m-d_h-i-s').$filetype;
                        /*if($_FILES['file']['size'] <= 250000){*/
                            $str = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','".$failnem."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                            try{
                                $owlPDO->exec($str);
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                file_put_contents($path.$failnem,$file_tmpname);
                            }
                            catch(PDOException $e){
                                echo " Gagal," . addslashes($e->getMessage());
                            }
                        /*}else{
                            exit("warning : Ukuran file upload maksimal 250kb");
                        }*/
                    }else{
                        exit("Warning : Format file upload harus .jpg atau .jpeg");
                    }
                }
            }
        break;
        case 'loadfiles':
            $no = 0;
            $tab = "";
            $str="select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
            if ($jenisupload=='1') {
                // $str="select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1' and kriteriaefil='".$jnsupspkfinal."'";
            }
            $res=fetchData($str);
            if(empty($res)){
                $tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }else{
                foreach($res as $key=>$val){
                    $no++;
                    $tab.="<tr class=rowcontent>
                            <td style='text-align:center'>".$no."</td>";
                    $icon=seticonfile($val['formaticon']);
                    $tab.="<td style='text-align:center'>
                            <a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
                        </td>";
                    $nfile='';
                    // if(strlen($val['namafile'])>10){
                        // $nfile = potongtext($val['namafile'],10).$val['formaticon'];
                    // }else{
                        $nfile = $val['namafile'];
                    // }
                    $tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
                    <td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
                        <td align=center>
                            <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a>&nbsp";
                    $tab.="<img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."','".$jenisupload."');\" >";
                    $tab."	</td>
                        </tr>";
                }
            }
            echo $tab;
        break;
        case'viewfile':
            $tab="<div align=center>";
            $tab.="<img src='".$path.$param['namafile']."' style='width:95%;height:80%;'></div>";
            echo $tab;
        break;
        case 'deletefile':
            $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'";
            try{
                $owlPDO->exec($str);
                $pathx = $path.$param['namafile'];
                unlink($pathx);
            }
            catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }
        break;

        default:
    }
?>
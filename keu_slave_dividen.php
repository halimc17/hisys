<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$notransaksi = checkPostGet('notransaksi','');
$unit1 = checkPostGet('unit1','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));
$norek = checkPostGet('norek','');
$tipetransaksi = checkPostGet('tipetransaksi','');
$transaksi = checkPostGet('transaksi','');
$unit2 = checkPostGet('unit2','');
$nilai = checkPostGet('nilai','');
$status = checkPostGet('status','');
$akunpiutangeks = checkPostGet('akunpiutangeks','');
$method = checkPostGet('method','');
$tglposting = tanggalsystemn(checkPostGet('tglposting',''));
$tipetransaksikasbank = checkPostGet('tipetransaksikasbank','');

switch ($method) {

    case 'getakun':
        $akunpemberi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query("select a.noakun,b.namabank,a.rekening from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank where pemilik='".$unit1."' order by b.namabank ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            if ($norek==$bar['noakun']){
                $akunpemberi.="<option value='".$bar['noakun']."' selected>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }else{
                $akunpemberi.="<option value='".$bar['noakun']."'>".$bar['namabank']." - ".$bar['rekening']."</option>";
            }
        }

        echo $akunpemberi;
    break;

    case 'getmatauang':
        $res=$owlPDO->query("select matauang from ".$dbname.".keu_5akunbank where noakun='".$norek."'");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $matauang=$bar['matauang'];

        echo $matauang;
    break;

    case 'getunit2':

        $optunit2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        if ($transaksi=='Internal') {
            $arrunit=array();
            $str="select a.unit,b.namaorganisasi from ".$dbname.".keu_5organisasi a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi where b.tipe='HOLDING' and char_length(b.kodeorganisasi)=4 and a.unit!='".$unit1."' and a.tipe='INTERNAL' order by b.namaorganisasi";
            $res=$owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar=$res->fetch()) {
                $arrunit[$bar['unit']]=$bar['unit'];
                if ($unit2==$bar['unit']){
                    $optunit2.="<option value='".$bar['unit']."' selected>".$bar['unit']." - ".$bar['namaorganisasi']."/option>";
                }else{
                    $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaorganisasi']."</option>";
                }
            }

            //get unit2 induk
            $res=$owlPDO->query("select a.indukunit as unit,b.namaorganisasi from ".$dbname.".keu_5organisasi a left join ".$dbname.".organisasi b on a.indukunit=b.kodeorganisasi where b.tipe='HOLDING' and char_length(b.kodeorganisasi)=4 and a.indukunit not in ('".implode("','",$arrunit)."') order by b.namaorganisasi");
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar=$res->fetch()) {
                $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaorganisasi']."</option>";
            }
        }else{
            $str="select unit,namaunit from ".$dbname.".keu_5organisasi where unit!='".$unit1."' and tipe='EKSTERNAL' order by unit";
            $res=$owlPDO->query($str);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($bar=$res->fetch()) {
                if ($unit2==$bar['unit']){
                    $optunit2.="<option value='".$bar['unit']."' selected>".$bar['unit']." - ".$bar['namaunit']."/option>";
                }else{
                    $optunit2.="<option value='".$bar['unit']."'>".$bar['unit']." - ".$bar['namaunit']."</option>";
                }
            }
        }

        echo $optunit2;
    break;

    case'insert':
        $nilai=str_replace(',', '', $nilai);

        if ($tipetransaksi=='Dividen') {
            $kode="DIV";
        }else{
            $kode="MOD";
        }

        $tahunbulan=$kode.date("Ym");
        $query="select right(notransaksi,3) as nomorurut from ".$dbname.".keu_dividen where left(notransaksi,9)='".$tahunbulan."' order by right(notransaksi,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();
        if(intval($rp['nomorurut'])==0){
          $awal=1;
        }else{
          $awal=intval($rp['nomorurut'])+1;
        }
        $notransaksi=$tahunbulan.addZero($awal,3);
        $totalpemberi=str_replace(',', '', $totalpemberi);
        $createTime=date("Y-m-d H:i:s");

        $str="insert into ".$dbname.".keu_dividen (notransaksi,unit1,norekening,unit2,status,tanggal,nilai,tipetransaksi,transaksi,akunpiutang,createby,updateby,createdtime) 
             values ('".$notransaksi."','".$unit1."','".$norek."','".$unit2."','".$status."','".$tanggal."','".$nilai."','".$tipetransaksi."','".$transaksi."','".$akunpiutangeks."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".$createTime."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case'update':
        $nilai=str_replace(',', '', $nilai);

        $str="update ".$dbname.".keu_dividen set updateby='".$_SESSION['standard']['userid']."',status='".$status."',norekening='".$norek."',unit2='".$unit2."',tanggal='".$tanggal."',nilai='".$nilai."',tipetransaksi='".$tipetransaksi."',transaksi='".$transaksi."',akunpiutang='".$akunpiutangeks."',updatetime='".date('Y-m-d H:i:s')."'
             where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }            
    break;

    case'loadData':
        $where = "";
        // $where = " createby ='".$_SESSION['standard']['userid']."'";
        $where.= " (unit1 in (".getOrgDetail(2).") or unit2 in (".getOrgDetail(2)."))";

        if ($notransaksi != '') {
            $where.=" and notransaksi like '%" . $notransaksi . "%'";
        }

        $limit=20;
        $page=0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);

        $str="select * from ".$dbname.".keu_5daftarbank";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $nmbank[$bar['kodebank']]=$bar['namabank'];
        }

        $ql2="select count(*) as jmlhrow from " . $dbname . ".keu_dividen where ".$where; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl=$query2->fetch()) {
            $jlhbrs=$jsl->jmlhrow;
        }

        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $str="select * from ".$dbname.".keu_dividen where ".$where."  limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $no=$maxdisplay;
            while ($bar=$res->fetch()) {

                $nmBankDtterima=$nmBankDtberi="";
                $strak="SELECT namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar['norekening']."' order by namabank asc";
                $barak=fetchData($strak);
                if(count($barak)!=0){
                    $dtRek=$barak[0];
                    $nmBankDt=$nmbank[$dtRek['namabank']]." - ".$dtRek['rekening'];
                }

                $whr1="karyawanid='".$bar['createby']."'";
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',$whr1);
                $whr2="kodeorganisasi='".$bar['unit1']."'";
                $nmunit1 = makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi',$whr2);
                $whr3="kodeorganisasi='".$bar['unit2']."'";
                $nmunit2 = makeOption($dbname, 'organisasi','kodeorganisasi,namaorganisasi',$whr3);

                if ($bar['transaksi']=='Internal') {
                    $unit2=$nmunit2[$bar['unit2']];
                }else{
                    $unit2=$bar['unit2'];
                }

                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
                $tab.="<td align=center>".$bar['notransaksi']."</td>";
                $tab.="<td align=center>".$nmunit1[$bar['unit1']]."</td>";
                $tab.="<td align=center>".$nmBankDt."</td>";
                $tab.="<td align=center>".$unit2."</td>";
                $tab.="<td align=center>".number_format($bar['nilai'])."</td>";
                $tab.="<td align=center>".$bar['status']."</td>";
                $tab.="<td align=center>".$bar['transaksi']."</td>";
                $tab.="<td align=left>".(isset($nmKar[$bar['createby']]) ? $nmKar[$bar['createby']] : '')."</td>";
                if ($bar['statusaktif']==0){
                    $tab.="<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('".$bar['notransaksi']."','".$bar['unit1']."','".$bar['norekening']."','".$bar['unit2']."','".$bar['status']."','".$bar['transaksi']."','".$bar['tipetransaksi']."','".tanggalnormal($bar['tanggal'])."','".number_format($bar['nilai'])."','".$bar['akunpiutang']."');\"></td>
                        <td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['notransaksi']. "');\"></td>
                        <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"formposting('".$bar['notransaksi']."');\" ></td>";
                }else{
                    if ($bar['status']=='Receiver') {
                   $tipe="M";
                    }else{
                        $tipe="K";
                    }

                    $tab.="<td align=center colspan=4><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30' title='View Detail Pemberi' onclick=\"viewdetail('".$bar['notransaksi']."','".$tipe."');\"> &nbsp;";
                    $tab.="</tr>";
                }

            }

            $totrows=ceil($jlhbrs/$limit);

            if($totrows==0){
                    $totrows=1;
            }
            $isiRow='';
            for($er=1;$er<=$totrows;$er++){
                    $sel = ($page==$er-1)? 'selected': '';
                    $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
            }
            $footd="
                <tr><td colspan=13 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        
        echo $tab."####".$footd;
    break;

    case 'delete':
        $str = "delete from " . $dbname . ".keu_dividen where notransaksi='" . $notransaksi . "'";
        try{
            $owlPDO->exec($str); 

        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;
    
    case 'formposting':  
        $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
        $tab.="<tr class=rowcontent>
                <td>".$_SESSION['lang']['notransaksi']."</td> 
                <td>:</td>
                <td><input type=text class=myinputtext  style=\"width:150px;\" value='".$notransaksi."' disabled/></td>
              </tr>
              <tr class=rowcontent>
                <td>".$_SESSION['lang']['tanggal']."</td> 
                <td>:</td>
                <td><input type=text class=myinputtext readonly  id=tglposting onmousemove=setCalendar(this.id) onkeypress=return false; style=\"width:150px;\"/></td>
              </tr>
              <tr class=rowcontent>
                <td></td><td></td>
                <td><button class=mybutton onclick=posting('".$notransaksi."')>Simpan</button></td>
              </tr>
        </table>";
                
        echo $tab;
    
    break;

    case 'posting':

        $qTrans="SELECT * FROM ".$dbname.".keu_dividen WHERE notransaksi='".$notransaksi."'";
        $data=fetchData($qTrans);
        $bar=$data[0];

        if ($bar['transaksi']=='Internal') {
            if ($bar['status']=='Issuer') {
                $unitissuer=$bar['unit1'];
                $unitreceiver=$bar['unit2'];
            }else{
                $unitissuer=$bar['unit2'];
                $unitreceiver=$bar['unit1'];
            }
        }else{
            if ($bar['status']=='Issuer') {
                $unitissuer=$bar['unit1'];
            }else{
                $unitreceiver=$bar['unit1'];
            }
        }

        #====cek periode
        $cek=0;
        $tgl = str_replace("-","",$tglposting);
        $sPeriode="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unitissuer."' and tutupbuku=0 order by periode desc";
        $rPeriode=fetchdata($sPeriode);
        $tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
        if($tglakutansi>$tgl){
            $cek=1;
        }

        $sPeriode="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unitreciever."' and tutupbuku=0 order by periode desc";
        $rPeriode=fetchdata($sPeriode);
        $tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
        if($tglakutansi>$tgl){
            $cek+=1;
        }

        if ($cek!=0) {
            exit('Error:Date beyond active period');
        }

        if ($bar['tipetransaksi']=='Modal') {
            $kodejurnal="MOD";
        }else{
            $kodejurnal="DIV";
        }
        $tglinput=str_replace('-','',$tglposting);
        $tgljurnal=$tglinput;
        $ket="Jurnal Otomatis atas tipetransaksi : ".$bar['tipetransaksi'];

        /*- Noakun Jurnal Issuer (Modal)
            D. Investasi Saham (left(noakun,5)='12402' / PT) => $noakundebet
            K. Hutang Modal (left(noakun,5)='21501' / PT) => $noakunkredit
          - Noakun Jurnal Receiver (Modal)
            D. Piutang Pemegang Saham (left(noakun,5)='11403' / PT) => $sampaidebet
            K. setoran modal (share capital) (3110101) => $sampaikredit

          - Noakun Jurnal Issuer (Dividen)
            D. RE (3490301) => $noakundebet
            K. Hutang Dividen (2190101) => $noakunkredit
          - Noakun Jurnal Receiver (Dividen)
            D. Piutang Dividen (1141501) => $sampaidebet
            K. Pendapatan Dividen (9130101) => $sampaikredit */

        #get noakun
        $str1="select noakundebet,noakunkredit,sampaidebet,sampaikredit from ".$dbname.".keu_5parameterjurnal where jurnalid='".$kodejurnal."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        $noakundebet=$rtr['noakundebet']; 
        $noakunkredit=$rtr['noakunkredit'];
        $sampaidebet=$rtr['sampaidebet'];
        $sampaikredit=$rtr['sampaikredit'];

        if ($bar['tipetransaksi']=='Modal') {
            #get noakun jurnal issuer untuk modal
            $str1="select noakun_investasisaham,noakun_hutangmodal from ".$dbname.".keu_5organisasi where unit='".$unitissuer."'";
            $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
            $qtr->setFetchMode(PDO::FETCH_ASSOC);
            $rtr=$qtr->fetch();
            $noakundebet=$rtr['noakun_investasisaham']; 
            $noakunkredit=$rtr['noakun_hutangmodal'];
        }

        #get noakun debet receiver
        $str1="select noakun_piutangsaham,noakun_piutangdividen from ".$dbname.".keu_5organisasi where unit='".$unitreceiver."'";
        $qtr=$owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_ASSOC);
        $rtr=$qtr->fetch();
        if ($bar['tipetransaksi']=='Modal') {
            $sampaidebet=$rtr['noakun_piutangsaham']; 
        }

                                            #######################
                                            #####Jurnal Issuer#####
                                            #######################

        if ($unitissuer!='') {
            #get induk issuer
            $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$unitissuer."'";
            $ressup=$owlPDO->query($sqlkd);
            $ressup->setFetchMode(PDO::FETCH_ASSOC);
            $barsup=$ressup->fetch();
            $indukissuer=$barsup['induk'];

            #Get Journal Counter Issuer
            $awalan=0;
            $queryJ=selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$indukissuer."' and kodekelompok='".$kodejurnal."'");
            $tmpKonter = fetchData($queryJ);
            if($awalan==0){
                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
            }else{
                $awalan=1;
                $konter = addZero(intval($konter)+1,3);
            }
            
            # Prep No Jurnal Issuer
            $notrans=$tgljurnal."/".$unitissuer."/".$kodejurnal."/".$konter;
            
            $strht['header']=array();
            $str=array();
            #insert jurnalht Issuer
            $strht['header'][]="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','".$bar['nilai']."','".$bar['nilai']."','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";
                
            #insert jurnalht debet Issuer
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','1','".$noakundebet."','".$ket."','".$bar['nilai']."','IDR','1','".$unitissuer."','".$bar['notransaksi']."','".$bar['notransaksi']."')";

            #insert jurnalht kredit Issuer
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','2','".$noakunkredit."','".$ket."','".-($bar['nilai'])."','IDR','1','".$unitissuer."','".$bar['notransaksi']."','".$bar['notransaksi']."')";

            #update kounter kelompok jurnal Issuer
            $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$indukissuer."' and kodekelompok='".$kodejurnal."'";

            if(count($strht['header'])!=0){
                for($i=0; $i<count($strht['header']); $i++){
                    try{ $owlPDO->exec($strht['header'][$i]); }catch (PDOException $e){ echo "Error : ".$strht['header'][$i]."__".$e->getMessage(); die(); }
                }   
            }

            if(count($str)!=0){
                for($i=0; $i<count($str); $i++){
                    try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                }   
            }
        }

                                            #######################
                                            ####Jurnal Receiver####
                                            #######################

        if ($unitreceiver!='') {
            #get induk reciever
            $sqlkd="select induk from ".$dbname.".organisasi where kodeorganisasi='".$unitreceiver."'";
            $ressup=$owlPDO->query($sqlkd);
            $ressup->setFetchMode(PDO::FETCH_ASSOC);
            $barsup=$ressup->fetch();
            $indukreceiver=$barsup['induk'];

            #Get Journal Counter Receiver
            $awalan=0;
            $queryJ=selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$indukreceiver."' and kodekelompok='".$kodejurnal."'");
            $tmpKonter = fetchData($queryJ);
            if($awalan==0){
                $konter = addZero($tmpKonter[0]['nokounter']+1,3);
            }else{
                $awalan=1;
                $konter = addZero(intval($konter)+1,3);
            }
            
            #Prep No Jurnal Receiver
            $notrans=$tgljurnal."/".$unitreceiver."/".$kodejurnal."/".$konter;
            
            $strht['header']=array();
            $str=array();
            #insert jurnalht Receiver
            $strht['header'][]="insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
                    values ('".$notrans."','".$kodejurnal."','".$bar['nilai']."','".$bar['nilai']."','".$tgljurnal."','".date('Ymd')."','1','".$bar['notransaksi']."','IDR','1')";

            #insert jurnalht debet Receiver
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','1','".$sampaidebet."','".$ket."','".$bar['nilai']."','IDR','1','".$unitreceiver."','".$bar['notransaksi']."','".$bar['notransaksi']."')";

            #insert jurnalht kredit Receiver
            $str[]="insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,noreferensi,nodok)
            values ('".$notrans."','".$tgljurnal."','2','".$sampaikredit."','".$ket."','".-($bar['nilai'])."','IDR','1','".$unitreceiver."','".$bar['notransaksi']."','".$bar['notransaksi']."')";

            #update kounter kelompok jurnal Receiver
            $str[]="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$indukreceiver."' and kodekelompok='".$kodejurnal."'";

            if(count($strht['header'])!=0){
                for($i=0; $i<count($strht['header']); $i++){
                    try{ $owlPDO->exec($strht['header'][$i]); }catch (PDOException $e){ echo "Error : ".$strht['header'][$i]."__".$e->getMessage(); die(); }
                }   
            }

            if(count($str)!=0){
                for($i=0; $i<count($str); $i++){
                    try{ $owlPDO->exec($str[$i]); }catch (PDOException $e){ echo "Error : ".$str[$i]."__".$e->getMessage(); die(); }
                }   
            }
        }

        #update status posting
        $strpost="update ".$dbname.".keu_dividen set statusaktif='1',tglposting='".$tglposting."' where notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($strpost); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

    break;

    case 'viewdetail':

        $legend="";
        if ($tipetransaksikasbank=='K') {
            $legend="Pengeluaran Dividen";
        }

        if ($tipetransaksikasbank=='M') {
            $legend="Penerimaan Dividen";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['detail']." ".$legend."</legend>";
        $data.="<div style=overflow:auto;width:100%;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $data.="<td>".$_SESSION['lang']['notransaksi']." Kasbank</td>";
        $data.="<td>".$_SESSION['lang']['keterangan']."</td>";
        $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select * from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and tipetransaksi='".$tipetransaksikasbank."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            
            $strket="select keterangan from ".$dbname.".keu_kasbankht where notransaksi='".$bar['notransaksi']."' ";
            $resket=$owlPDO->query($strket) or die(print " Gagal: ".PDOException::getMessage());
            $resket->setFetchMode(PDO::FETCH_ASSOC);
            $barket=$resket->fetch();

            $data.="<tr class=rowcontent>";
            $data.="<td>".$bar['tanggal']."</td>";
            $data.="<td>".$bar['notransaksi']."</td>";
            $data.="<td>".$barket['keterangan']."</td>";
            $data.="<td align=right>".number_format($bar['jumlah'])."</td>";
            $data.="</tr>";
            $total+=$bar['jumlah'];

        }
        $data.="<tr class=rowcontent>";
        $data.="<td align=right colspan=3>".$_SESSION['lang']['total']."</td>";
        $data.="<td align=right>".number_format($total)."</td>";
        $data.="</tr>";
        $data.= "</table></div></fieldset><br><br>";

        echo $data;
    break;

    default:
}
?>

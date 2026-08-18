<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$optNm=makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optsklbrg=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok');
$optNmBrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$tgltrans = tanggalsystem(checkPostGet('tgltrans', ''));
$method = checkPostGet('method', '');
$notrans = checkPostGet('notrans', '');
$unit = checkPostGet('unit', '');
$kdbrg = checkPostGet('kdbrg', '');
$nmbrg = checkPostGet('nmbrg', '');
$jumlah = checkPostGet('jumlah', '');
$hrgsatuan = checkPostGet('hrgsatuan', '');
$tgleta = tanggalsystem(checkPostGet('tgleta', ''));
$catatan = checkPostGet('catatan', '');
$diperiksa1 = checkPostGet('diperiksa1', '');
$diperiksa2 = checkPostGet('diperiksa2', '');
$menyetujui1 = checkPostGet('menyetujui1', '');
$menyetujui2 = checkPostGet('menyetujui2', '');
$budget = checkPostGet('budget', '');
$notranscr = checkPostGet('notranscr', '');
$tglcr = tanggalsystem(checkPostGet('tglcr', ''));
$optstatus=array("0"=>"Belum Menyetujui","1"=>"Disetujui","2"=>"Ditolak");

switch ($method) {
    case 'insertht':
        $bulan = substr($tgltrans, 4, 2);
        $tahun = substr($tgltrans, 0, 4);
        $strCount = "select left(notransaksi,3) as nourut from " . $dbname . ".`log_formcapex_ht` where year(tanggal)='" . $tahun . "' and substr(notransaksi,9,4)='$unit' order by notransaksi desc limit 1";
        $rData=fetchData($strCount);
            if(intval($rData[0]['nourut'])==0){
                $nourut=addZero(1,3);
            }else{
                $nourut=addZero((intval($rData[0]['nourut'])+1),3);
            }

            $notransaksi=$nourut."/FRM"."/".$unit."/".$bulan."/".$tahun;

        $sql = "SELECT induk FROM " . $dbname . ".organisasi where kodeorganisasi='".$unit."'";
        $qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $qry->fetch();
        $kodept=$bar['induk'];



        $strht = "insert into " . $dbname . ".log_formcapex_ht (notransaksi,tanggal,unit,kodept,dibuat_oleh) values ('".$notransaksi."','".$tgltrans."','".$unit."','".$kodept."','".$_SESSION['standard']['userid']."')";
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        echo $notransaksi;

    break;

    case 'detail':
        $optbarang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $ql="select * from ".$dbname.".`log_5masterbarang` where `kodebarang`='".$row['kodebarang']."'"; //echo $ql;
        $qry=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
        while ($res=$qry->fetch()) {
            $optbarang.="<option value='".$res['kodebarang']."'>".$res['namabarang']."</option>";
        }  

        OPEN_BOX();
        echo "
        <fieldset style='float:left'>
            <legend>".$_SESSION['lang']['detail']."</legend>
            <table border=0 cellpadding=1 cellspacing=1 class=sortable>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                <td align=center>".$_SESSION['lang']['namabarang']."</td>
                <td align=center>Jumlah</td>
                <td align=center>Harga Satuan</td>
                <td align=center>ETA</td>
                <td align=center>Catatan</td>
                <td align=center>".$_SESSION['lang']['action']."</td>
            </tr>
            </thead>
            <tr class=rowcontent>
                <td onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor ><div id=container></div>',event)\"><input type='text' id='kdbrg' class='myinputtext' style='vertical-align:top;width:120px' disabled />
                <img src=\"images/onebit_02.png\" style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."' onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor ><div id=container></div>',event)\"></td>
                <td onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor ><div id=container></div>',event)\"><input type='text' id='nmbrg' class='myinputtext' disabled />
                <img src=\"images/onebit_02.png\" style='position:relative;top:3px;' class=\"resicon\" title='".$_SESSION['lang']['find']."' onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor ><div id=container></div>',event)\"></td>
                <td><input type='text' id='jumlah' style=width:50px class='myinputtextnumber' onkeypress='return angka_doang(event)' /></td>
                <td><input type='text' id='hrgsatuan' style=width:100px class='myinputtextnumber' onkeypress='return angka_doang(event)' /></td>
                <td><input type='text' style='width:150px' class=myinputtext id=tgleta onmousemove=setCalendar(this.id) onkeypress=return false; size=2 maxlength=15 ></td>
                <td><input type='text' style=width:230px id='catatan' class='myinputtext' /></td>
                <td align=center>
                <input type=hidden id=methoddt value='insertdt'>
                    <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=savedetail() src='images/save.png'/>
                </td>
            </tr>
            </table>
            <button id=selesai class=mybutton onclick=formpersetujuan()>".$_SESSION['lang']['done']."</button>
            <br>
            <br>
            <div id=loaddatadetail>
            <script>loaddatadetail()</script>
        </fieldset>";
        CLOSE_BOX();
    break;

    case 'insertdt':
        // Check Valid Data
        if($kdbrg=='' || $jumlah=='' || $hrgsatuan==''|| $hrgsatuan==0 || $tgleta=='')
        {
            exit('Warning : Field nama barang, jumlah, harga satuan dan tanggal ETA harus diisi.');
        } else{
            $sht="select tanggal from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
            $rht = $owlPDO->query($sht) or die(print " Gagal: " . PDOException::getMessage());
            $rht->setFetchMode(PDO::FETCH_OBJ);
            $bht = $rht->fetch();
            $tanggal = $bht->tanggal;

            $starttime=strtotime($bht->tanggal);// tanggal pengajuan
            $endtime=strtotime($tgleta);//tanggal sampai
            $timediff = $endtime-$starttime;
            $days=intval($timediff/86400);
                
            if(($tglsdt<$rtglpp) or ($days<14)) {
                echo "Warning : Tanggal ETA minimal 2 minggu dari tanggal pengajuan.";
                exit;
            }
        }

        $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."' and kodebarang='".$kdbrg."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs>0){
            exit("Warning : jenis barang telah diinput");
        }else{  
            $strdt = "insert into " . $dbname . ".log_formcapex_dt (notransaksi,kodebarang,jumlah,hargasatuan,tanggal_eta,catatan) values ('".$notrans."','".$kdbrg."','".$jumlah."','".$hrgsatuan."','".$tgleta."','".$catatan."')";
            try
            {
                $owlPDO->exec($strdt);
                    $total=$jumlah*$hrgsatuan;
                    $str="select subtotal from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
                    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                    $res->setFetchMode(PDO::FETCH_OBJ);
                    $bar = $res->fetch();
                    $subtotal=$bar->subtotal+$total;

                    $strht = "update " . $dbname . ".log_formcapex_ht set subtotal='".$subtotal."' where notransaksi='".$notrans."'";                
                        try
                        {
                            $owlPDO->exec($strht);
                        }
                        catch (PDOException $e)
                        {
                            print " Gagal  !: " . $e->getMessage() . "\n";
                            die();
                        }

            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
    break;

    case'loaddatadetail':
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead>
        <tr class=rowheader>    
            <td align=center>".$_SESSION['lang']['nourut']."</td>
            <td align=center>".$_SESSION['lang']['namabarang']."</td>
            <td align=center>Jumlah</td>
            <td align=center>Harga Satuan</td>
            <td align=center>Total</td>
            <td align=center>Tanggal ETA</td>
            <td align=center>Catatan</td>
            <td align=center>".$_SESSION['lang']['action']."</td>
        </tr>
        </thead>";

        $no = 0;
        $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>   
                <td align=center>".$no."</td>
                <td >".$optNmBrg[$bar->kodebarang]."</td>
                <td align=center>".$bar->jumlah."</td>
                <td align=right>".@number_format($bar->hargasatuan)."</td>";
                    $total=$bar->jumlah*$bar->hargasatuan;
            echo"<td align=right>".@number_format($total)."</td>
                <td >".tanggalnormal($bar->tanggal_eta)."</td>
                <td >".$bar->catatan."</td>
                <td align=center>
                    <img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"editdt('".$notrans."','".$bar->kodebarang."','" .$optNmBrg[$bar->kodebarang]."','".$bar->jumlah."','" .$bar->hargasatuan."','" .tanggalnormal($bar->tanggal_eta) . "','" .$bar->catatan."');\" >
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('".$notrans."','".$bar->kodebarang."');\" >
                </td>
                </tr>";
                @$ttotal+=$total;
            
        }
        echo "<tr class=rowcontent>
                    <td colspan=4 align=right>Subtotal</td>
                    <td align=right>".@number_format($ttotal)."</td>
                    <td colspan=3></td>
                  </tr>";
        echo "</table>";
    break;

    case 'updatedt':
        $str="select jumlah,hargasatuan from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."' and kodebarang='".$kdbrg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $total=($bar->jumlah)*($bar->hargasatuan);

        $str="select subtotal from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $subtotal=$bar->subtotal-$total;

        $strht = "update " . $dbname . ".log_formcapex_ht set subtotal='".$subtotal."' where notransaksi='".$notrans."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        $strdt = "update " . $dbname . ".log_formcapex_dt set jumlah='".$jumlah."', hargasatuan='".$hrgsatuan."', tanggal_eta='".$tgleta."', catatan='".$catatan."' where notransaksi='".$notrans."' and kodebarang='".$kdbrg."'";        
        try
        {
            $owlPDO->exec($strdt);

            $total=$jumlah*$hrgsatuan;
            $str="select subtotal from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            $bar = $res->fetch();
            $subtotal=$bar->subtotal+$total;

            $strht = "update " . $dbname . ".log_formcapex_ht set subtotal='".$subtotal."' where notransaksi='".$notrans."'";                
                try
                {
                    $owlPDO->exec($strht);
                }
                catch (PDOException $e)
                {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'deldt':
        //exit('warning : '.$notrans.$kdbrg);
      $str="select jumlah,hargasatuan from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."' and kodebarang='".$kdbrg."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $total=($bar->jumlah)*($bar->hargasatuan);

        $str="select subtotal from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $subtotal=$bar->subtotal-$total;

        $strht = "update " . $dbname . ".log_formcapex_ht set subtotal='".$subtotal."' where notransaksi='".$notrans."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        $strdt = "delete from " . $dbname . ".log_formcapex_dt where notransaksi='" . $notrans . "' and kodebarang='".$kdbrg."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'formpersetujuan':
        //Check data dt
        $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs<=0){
            exit('Warning : Data barang harus dipilih.');
        }

        //get data edit
        $str="select * from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $subtotal=$bar->subtotal;
        $unit=$bar->unit;

        //get karyawan pemeriksa
        $optmanagerd1=$optmanagerd2=$optmanagerm1=$optmanagerm2=$optbudget="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $smanagerm="select distinct karyawanid from ".$dbname.".setup_approval where applikasi in ('PP1','PP2')  and kodeunit='$unit' ";
        $qmanagerm=$owlPDO->query($smanagerm) or die(print " Gagal: ".PDOException::getMessage());
        $qmanagerm->setFetchMode(PDO::FETCH_ASSOC);
        while($rmanagerm=$qmanagerm->fetch())
        {
            #diperiksa1 dan diperiksa 2
            $whrKar1="karyawanid='".$rmanagerm['karyawanid']."'";
            $optdiperiksa=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);

            if($bar->diperiksa1==$rmanagerm['karyawanid']){
                $optmanagerd1.="<option value=".$rmanagerm['karyawanid']." selected>".$optdiperiksa[$rmanagerm['karyawanid']]."</option>";
            }else{
                $optmanagerd1.="<option value=".$rmanagerm['karyawanid']." >".$optdiperiksa[$rmanagerm['karyawanid']]."</option>";
            }

            if($bar->diperiksa2==$rmanagerm['karyawanid']){
                $optmanagerd2.="<option value=".$rmanagerm['karyawanid']." selected>".$optdiperiksa[$rmanagerm['karyawanid']]."</option>";
            }else{
                $optmanagerd2.="<option value=".$rmanagerm['karyawanid']." >".$optdiperiksa[$rmanagerm['karyawanid']]."</option>";
            }
        }

        $smanager="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where 
        (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('7','8') order by namakaryawan asc";
        $qmanager=$owlPDO->query($smanager) or die(print " Gagal: ".PDOException::getMessage());
        $qmanager->setFetchMode(PDO::FETCH_ASSOC);
        while($rmanager=$qmanager->fetch())
        {

            if($bar->menyetujui1==$rmanager['karyawanid']){
                $optmanagerm1.="<option value=".$rmanager['karyawanid']." selected>".$rmanager['namakaryawan']."</option>";
            }else{
                $optmanagerm1.="<option value=".$rmanager['karyawanid']." >".$rmanager['namakaryawan']."</option>";
            }

            if($bar->menyetujui2==$rmanager['karyawanid']){
                $optmanagerm2.="<option value=".$rmanager['karyawanid']." selected>".$rmanager['namakaryawan']."</option>";
            }else{
                $optmanagerm2.="<option value=".$rmanager['karyawanid']." >".$rmanager['namakaryawan']."</option>";
            }
        }

        //get budget
        $sbudget="select distinct karyawanid from ".$dbname.".setup_approval where applikasi='PP4' and kodeunit='$unit' ";
        $qbudget=$owlPDO->query($sbudget) or die(print " Gagal: ".PDOException::getMessage());
        $qbudget->setFetchMode(PDO::FETCH_ASSOC);
        while($rbudget=$qbudget->fetch())
        {
            #budget
            $whrKar1="karyawanid='".$rbudget['karyawanid']."'";
            $optbudget1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);

            if($bar->budget==$rbudget['karyawanid']){
                $optbudget.="<option value=".$rbudget['karyawanid']." selected>".$optbudget1[$rbudget['karyawanid']]."</option>";
            }else{
                $optbudget.="<option value=".$rbudget['karyawanid']." >".$optbudget1[$rbudget['karyawanid']]."</option>";
            }
        }

        OPEN_BOX();
        echo "
        <fieldset style=float:left>
            <legend>".$_SESSION['lang']['persetujuan']."</legend>
            <table>
                <tr>
                    <td><label>Diperiksa 1</label></td>
                    <td>:</td>
                    <td><select style=width:180px  id=diperiksa1 >".$optmanagerd1."</td>
                </tr>
                <tr>
                    <td><label>Diperiksa 2</label></td>
                    <td>:</td>
                    <td><select style=width:180px  id=diperiksa2 >".$optmanagerd2."</td>
                </tr>
                <tr>
                    <td><label>Budget</label></td>
                    <td>:</td>
                    <td><select style=width:180px  id=budget >".$optbudget."</td>
                </tr>
                <tr>
                    <td><label>Menyetujui 1</label></td>
                    <td>:</td>
                    <td><select style=width:180px  id=menyetujui1 >".$optmanagerm1."</td>
                </tr>";
            if ($subtotal>50000000){
            echo"<tr>
                    <td><label>Menyetujui 2</label></td>
                    <td>:</td>
                    <td><select style=width:180px  id=menyetujui2 >".$optmanagerm2."</td>
                </tr>";
            }

            echo "<input type=hidden value='insertht2' id=methodht>
                <tr>
                    <td><td><td>
                        <button class=mybutton onclick=simpan('".$subtotal."')>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton onclick=cancel()>".$_SESSION['lang']['cancel']."</button>
                    </td>
                    </td></td>
                </tr>
            </table>
        </fieldset>";
        CLOSE_BOX();
    break;

    case 'insertht2':
        if ($diperiksa1 == '' || $diperiksa2 == '' || $budget == '' || $menyetujui1 == '') {
            echo 'Gagal : Semua field harus diisi. Pastikan data persetujuan telah di input di menu Setup->Persetujuan/Approval';
        } else {  
                if ($menyetujui2!=''){
                    $strht = "update " . $dbname . ".log_formcapex_ht set diperiksa1='".$diperiksa1."', diperiksa2='".$diperiksa2."', budget='".$budget."',menyetujui1='".$menyetujui1."',menyetujui2='".$menyetujui2."' where notransaksi='".$notrans."'"; 
                }else{
                    $strht = "update " . $dbname . ".log_formcapex_ht set diperiksa1='".$diperiksa1."', diperiksa2='".$diperiksa2."', budget='".$budget."',menyetujui1='".$menyetujui1."' where notransaksi='".$notrans."'"; 
                }                   
                try
                {
                    $owlPDO->exec($strht);
                }
                catch (PDOException $e)
                {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
        }

    break;

    case 'loadData':
        $where = "";
        $where = " dibuat_oleh ='".$_SESSION['standard']['userid']."'";
        if ($notranscr != '') {
            $where.=" and notransaksi like '%" . $notranscr . "%' ";
        }
        if ($tglcr != '') {
            $where.=" and tanggal='" . $tglcr . "' ";
        }
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".log_formcapex_ht where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }
        else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".log_formcapex_ht where ".$where." order by tanggal desc, status_pengajuan asc   limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrpt="kodeorganisasi='".$bar->kodept."'";
                $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);
                #pembuat
                $whrKar2="karyawanid='".$bar->dibuat_oleh."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                
                if (($bar->status_pengajuan!=0)&&($bar->stat_periksa1==1)&&($bar->stat_periksa2==1)&&($bar->stat_budget==1)&&($bar->stat_menyetujui1==1)&&($bar->stat_menyetujui1==1)){
                    $status="Disetujui";
                } else if ($bar->status_pengajuan!=0){
                    $status="Sedang Diproses";
                } else if ($bar->status_pengajuan==0){
                    $status="Ajukan";
                }else{
                    $status="Ditolak";
                }

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->notransaksi."</td>
                    <td>".tanggalnormal($bar->tanggal)."</td>
                    <td>".$optpt[$bar->kodept]."</td>
                    <td>".$optpembuat[$bar->dibuat_oleh]."</td>
                    <td align=center>".$status."</td>
                    <td align=right>";
                    if ($bar->status_pengajuan==0){
                        $tab.="<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editht('".$bar->notransaksi."','".$bar->unit."','".tanggalnormal($bar->tanggal)."')\">
                               <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delht('" . $bar->notransaksi. "');\" >";
                        $tab.="<img src=images/icons/04/16/01.png class=resicon  title='ajukan' onclick=\"ajukan('".$bar->notransaksi."');\" >";
                    }
                $tab.="<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar->notransaksi. "',event);\" >";
                $tab.="</td>";
                $tab.="</tr>";
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
                <tr><td colspan=7 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case'delht':

        $strht = "delete from " . $dbname . ".log_formcapex_ht where notransaksi='" . $notrans . "'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'ajukan':
        $str="select * from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        if(($bar->diperiksa1!='0000000000')&&($bar->diperiksa2!='0000000000')&&($bar->budget!='0000000000')&&($bar->menyetujui1!='0000000000')){
            $sdt="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."'";
            $rdt=fetchdata($sdt);
            $jlhbrs=count($rdt);
            if($jlhbrs==0){
                exit('Warning : Data barang belum ada. Untuk mengajukan lengkapi data terlebih dahulu.');
            }else{
                $sht = "update " . $dbname . ".log_formcapex_ht set status_pengajuan='1' where notransaksi='".$notrans."'";        
                try{ 
                $owlPDO->exec($sht); 
                }
                catch (PDOException $e){
                    echo " Gagal ".addslashes($e->getMessage());
                }

                $kodeorg=$bar->kodept;
                //get nama  dan kode organisasi
                $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi ='".$kodeorg."' and tipe='PT'";
                $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
                $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
                $rnamaorg=$qnamaorg->fetch();
                $namaorg=$rnamaorg->namaorganisasi;

                if ($bar->diperiksa1!=''){
                    $to = getUserEmail($bar->diperiksa1);
                    $namakaryawan = getNamaKaryawan($bar->diperiksa1);
                    $namapengaju = getNamaKaryawan($bar->dibuat_oleh);
                    $subject = "[Notifikasi]Pengajuan Capex dengan Nomor Transaksi ".$bar->notransaksi;
                    $body = "<html>
                                <head>
                                 <body>
                                   <dd>Dengan Hormat,</dd><br>
                                   <br>
                                   Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  ".$namapengaju." mengajukan permintaan capex dengan Nomor Transaksi ".$bar->notransaksi.".<br>                          
                                   <br>
                                   <br>
                                   Untuk melihat detail dan melakukan persetujuan silahkan lakukan di menu Pengadaan->Transaksi->Persetujuan Capex
                                   <br>
                                   <br>
                                   Regards,<br>
                                   Owl-Plantation System.
                                 </body>
                                </head>
                             </html>";
                    if ($to!=''){
                        $kirim = kirimEmail($to, '', $subject, $body);
                    }
                }

            }
        }else{
            $sdt="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."'";
            $rdt=fetchdata($sdt);
            $jlhbrs=count($rdt);
            if($jlhbrs==0){
                exit('Warning : Data belum lengkap. Untuk mengajukan lengkapi data terlebih dahulu.');
            }else{
                exit('Warning : Penyetuju Belum dipilih. Untuk mengajukan lengkapi data terlebih dahulu.');
            }
        }

    break;

    case'cariBarangDlmDtBs':
        $txtfind=$_POST['txtfind'];
        //exit('warning : '.$txtfind);
        $str="select * from ".$dbname.".log_5masterbarang where left(kodebarang,1)='9' and namabarang like '%".$txtfind."%'";
        // $res=$owlPDO->query($str);
        
        if($res=$owlPDO->query($str)){
            echo "<fieldset>
                <legend>Result</legend>
                <div style=\"overflow:auto; max-height:300px;\" >
                <table class=sortable cellspacing=1 cellpadding=2  border=0>
                    <thead>
                    <tr class=rowheader>
                        <td class=firsttd align=center>No.</td>
                        <td align=center>".$_SESSION['lang']['kelompokbarang']."</td>
                        <td align=center>".$_SESSION['lang']['subkelompokbarang']."</td>
                        <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                        <td>".$_SESSION['lang']['namabarang']."</td>
                        <td align=center>".$_SESSION['lang']['satuan']."</td>
                        <td align=center>".$_SESSION['lang']['saldo']."</td>
                    </tr>
                    </thead>
                    <tbody>";
                    
            $no=0;   
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;
                //===========================pengambilan saldo
                //ambil saldo barang
                $saldoqty=0;
                $str1="select sum(saldoqty) as saldoqty from ".$dbname.".log_5masterbarangdt where kodebarang='".$bar->kodebarang."' and kodeorg='".$_SESSION['empl']['kodeorganisasi']."'";
                $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_OBJ);
                while($bar1=$res1->fetch()){
                    $saldoqty=$bar1->saldoqty;
                }

                //ambil pemasukan barang yang belum di posting
                $qtynotpostedin=0;
                $str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' and a.tipetransaksi<5 and a.post=0 group by kodebarang";
                $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                $res2->setFetchMode(PDO::FETCH_OBJ);
                while($bar2=$res2->fetch()){
                    $qtynotpostedin=$bar2->jumlah;
                }
                if($qtynotpostedin=='')
                    $qtynotpostedin=0;

                //ambil pengeluaran barang yang belum di posting
                $qtynotposted=0;
                $str2="select sum(b.jumlah) as jumlah,b.kodebarang FROM ".$dbname.".log_transaksiht a left join ".$dbname.".log_transaksidt b on a.notransaksi=b.notransaksi where kodept='".$_SESSION['empl']['kodeorganisasi']."' and b.kodebarang='".$bar->kodebarang."' and a.tipetransaksi>4 and a.post=0 group by kodebarang";
                
                $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                $res2->setFetchMode(PDO::FETCH_OBJ);
                while($bar2=$res2->fetch()){
                    $qtynotposted=$bar2->jumlah;
                }
                if($qtynotposted=='')
                    $qtynotposted=0;

                $saldoqty=($saldoqty+$qtynotpostedin)-$qtynotposted;
                //============================================      
                echo "<link rel=stylesheet type=text/css href='style/generic.css'>";
                
                if($bar->inactive==1){
                    echo"<tr bgcolor='red' style='cursor:pointer;'  title='Inactive' >";
                    $bar->namabarang=$bar->namabarang. " [Inactive]";
                    $bgr=" bgcolor='red'";
                }else{              
                    echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg('".$bar->kodebarang."','".changeKutipChar($bar->namabarang)."')\" title='Click' >";
                }
                
                echo "<td class=firsttd  align=center>".$no."</td>
                      <td>".$optNm[substr($bar->kodebarang,0,3)]."</td>
                      <td>".$optsklbrg[substr($bar->kodebarang,0,5)]."</td>
                      <td align=center>".$bar->kodebarang."</td>
                      <td>".$bar->namabarang."</td>
                      <td align=center>".$bar->satuan."</td>
                      <td align=right>".number_format($saldoqty,2,',','.')."</td>
            </tr>";
            }    
               
            echo "</tbody>
                <tfoot>
                </tfoot>
                </table></div></fieldset>";
        }else{
            echo " Gagal,".PDOException::getMessage();
        }
    break;

    case 'viewdetail':

        //get data spdt dan spht
        $str="SELECT * from ".$dbname.".log_formcapex_ht where notransaksi='".$notrans."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
        $subtotal=$bar->subtotal;

            #diperiksa1
            $whrKar1="karyawanid='".$bar->diperiksa1."'";
            $optdiperiksa1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #diperiksa2
            $whrKar2="karyawanid='".$bar->diperiksa2."'";
            $optdiperiksa2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #budget 
            $whrKar3="karyawanid='".$bar->budget."'";
            $optbudget=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3); 
            #menyetujui1 
            $whrKar4="karyawanid='".$bar->menyetujui1."'";
            $optmenyetujui1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar4);
            #menyetujui2 
            $whrKar5="karyawanid='".$bar->menyetujui2."'";
            $optmenyetujui2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar5); 
            #namapt
            $whrpt="kodeorganisasi='".$bar->kodept."'";
            $optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whrpt);    
            
            $tab="<legend><b>DETAIL PURCHASE REQUEST</b></legend><br>";
            $tab.="<table align=left border=0>
            
            <tr>
                <td>No Transaksi</td>
                <td> : </td>
                <td>".$notrans."</td>
            </tr>
            <tr>
                <td>Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optdiperiksa1[$bar->diperiksa1]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa1]."</td>
            </tr>
            <tr>
                <td>Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optdiperiksa2[$bar->diperiksa2]."</td>
            </tr>
            <tr>
                <td>Status Pemeriksaan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_periksa2]."</td>
            </tr>
            <tr>
                <td>Budget</td>
                <td> : </td>
                <td>".$optbudget[$bar->budget]."</td>
            </tr>
            <tr>
                <td>Status Budget</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_budget]."</td>
            </tr>
            <tr>
                <td>Persetujuan 1</td>
                <td> : </td>
                <td>".$optmenyetujui1[$bar->menyetujui1]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 1</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui1]."</td>
            </tr>";
        if ($subtotal>50000000){
        $tab.="<tr>
                <td>Persetujuan 2</td>
                <td> : </td>
                <td>".$optmenyetujui2[$bar->menyetujui2]."</td>
            </tr>
            <tr>
                <td>Status Persetujuan 2</td>
                <td> : </td>
                <td>".$optstatus[$bar->stat_menyetujui2]."</td>
            </tr>";
        }
        $tab.="<tr colspan=3>
                <td>&nbsp;</td>
            </tr>
            <tr colspan=3>
                <td><b>Detail Barang</b></td>
            </tr>
            <tr >
                <td colspan=3>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['tanggal']." ETA</td>
                    <td align=center>".$_SESSION['lang']['namabarang']."</td>
                    <td align=center>".$_SESSION['lang']['jumlah']."</td>
                    <td align=center>" . $_SESSION['lang']['harga'] . " ".$_SESSION['lang']['satuan']."</td>
                    <td align=center>" . $_SESSION['lang']['total'] . "</td>
                    <td align=center>" . $_SESSION['lang']['catatan'] . "</td>
                </tr>
                </thead>";

                $no = 0;
                $str="select * from ".$dbname.".log_formcapex_dt where notransaksi='".$notrans."'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $total=($bar->jumlah)*($bar->hargasatuan);
                    $no+=1;
                    $tab.="<tr class=rowcontent>   
                        <td>".$no."</td>
                        <td>".tanggalnormal($bar->tanggal_eta)."</td>
                        <td>".$optNmBrg[$bar->kodebarang]."</td>
                        <td align=center>".$bar->jumlah."</td>
                        <td align=right>".@number_format($bar->hargasatuan)."</td>
                        <td align=right>".@number_format($total)."</td>
                        <td align=justify>".$bar->catatan."</td>
                        </tr>";
            
                }
                $tab.="<tr class=rowcontent>   
                        <td colspan=5 align=right>Subtotal</td>
                        <td align=right>".@number_format($subtotal)."</td>
                        <td></td>
                       </tr>
                        </table>
            </td>
            </tr>
            </table>";

        echo $tab;
    break;
    
    default:
        # code...
        break;
}


?>
<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

$proses=checkPostGet('proses','');
$notakredit=checkPostGet('notakredit','');
$tipe=checkPostGet('tipe','');
$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$revisi=checkPostGet('revisi','');
$kodeorg=checkPostGet('kodeorg','');
$unit=checkPostGet('unit','');
$jenis=checkPostGet('jenis','');
$noinvoice=checkPostGet('noinvoice','');
$nokontrak=checkPostGet('nokontrak','');
$customer=checkPostGet('customer','');
$nilaiinvoice=checkPostGet('nilaiinvoice','');
$keterangan=checkPostGet('keterangan','');
$notakredit=checkPostGet('notakredit','');
$noakun=checkPostGet('noakun','');
$noakundtold=checkPostGet('noakundtold','');
$matauang=checkPostGet('matauang','');
$kurs=checkPostGet('kurs','');
$nilai=checkPostGet('nilai','');
$param =$_POST;
$opjns=array("UM"=>"Uang Muka","PM"=>"Pengiriman","PK"=>"Pemenuhan Kontrak","DS"=>"Disposal","OT"=>"Others"); 

switch ($proses) {

    case'getunit':
        # Options
        $arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kodeorg."' and kodeorganisasi in (".getOrgDetail(2).")";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) 
        {
            $arrUnit .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }
        
        echo $arrUnit;
    break;

    case'getformnodo':
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['noinvoice']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=fnodo></td>";
        $form.= "<td><button class=mybutton onclick=findnodo()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=container2></div>";
        echo $form;
    break;  

    case'getdatanodo':
        $data="";
        $dt  ="";

        if($param['nodo']!=''){
            $where.=" and noinvoice like '%".$param['nodo']."%'";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['unit']."</td>";
        $data.="<td>".$_SESSION['lang']['jenis']."</td>";
        $data.="<td>".$_SESSION['lang']['tanggal']."</td>";
        $data.="<td>".$_SESSION['lang']['NoKontrak']."</td>";
        $data.="<td>".$_SESSION['lang']['kodecustomer']."</td>";
        $data.="<td>".$_SESSION['lang']['nilaiinvoice']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        //and jenis='".$jenis."' 
        $str="select * from ".$dbname.".keu_penagihanht where kodeorg='".$unit."' ".$where;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch()){

            $whrak="kodecustomer='".$bar->kodecustomer."'";
            $optak=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrak);
            $data.="<tr class=rowcontent style='cursor:pointer;' onclick=\"setdata('".$bar->noinvoice."','".$bar->nokontrak."','".$bar->jenis."','".$bar->kodecustomer."','".$bar->matauang."','".$bar->kurs."')\">";
            $data.="<td>".$bar->noinvoice."</td>";
            $data.="<td>".$bar->kodeorg."</td>";
            $data.="<td>".$opjns[$bar->jenis]."</td>";
            $data.="<td>".tanggalnormal($bar->tanggal)."</td>";
            $data.="<td>".$bar->nokontrak."</td>";
            $data.="<td>".$optak[$bar->kodecustomer]."</td>";
            $data.="<td align='right'>".number_format($bar->nilaiinvoice)."</td>";
            $data.="</tr>";
        }
        $data.= "</table></div></fieldset>";
        echo $data;
    break;

    case 'showdetail':
        # Options
        $optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select noakun,namaakun from ".$dbname.".keu_5akun where char_length(noakun)='7' and SUBSTRING(fieldaktif,-1)=1 and char_length(fieldaktif)='12' order by noakun";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            $optakun.="<option value='".$bar['noakun']."'>".$bar['noakun']." - ".$bar['namaakun']."</option>";
        }

        OPEN_BOX();
        echo"<fieldset style='width:598px;'>";
        echo"<legend>".$_SESSION['lang']['detail']."</legend>";
        echo"<table border=0 cellpadding=1 cellspacing=1>";
            echo"
                <tr>
                    <td>".$_SESSION['lang']['noakun']."</td> 
                    <td>:</td>
                    <td><select id=noakundt style=width:200px;>".$optakun."</select></td>
                </tr>
                <tr>
                    <td valign=top>".$_SESSION['lang']['nilai']."</td> 
                    <td valign=top>:</td>
                    <td valign=top><input type=text onkeypress=\"return angka_doang (event);\" onkeyup=\"z.numberFormat('nilai',2);\" class=myinputtextnumber id=nilai style=width:197px; ></td>
                </tr>
                <tr>
                    <td valign=top>".$_SESSION['lang']['keterangan']."</td> 
                    <td valign=top>:</td>
                    <td valign=top><textarea class=myinputtext id=keterangandt style=width:197px; ></textarea></td>
                </tr>
                <tr><td colspan=2></td>
                    <td colspan=3>
                        <button class=mybutton onclick=saveDetail()>Simpan</button>
                        <button class=mybutton onclick=cleardetail()>Hapus</button>
                        <input type=hidden id=prosesdt value='insertdt'>
                        <input type=hidden id=noakundtold>
                    </td>
                </tr>
            </table></fieldset>
            <br>
            <fieldset>
            <legend><b>".$_SESSION['lang']['list']." ".$_SESSION['lang']['detail']."</legend>
            <table class=sortable cellspacing=1 cellspacing=1 border=0 style='width:100%;'>
            <thead>
            <tr class=rowheader>    
                <td align=center>".$_SESSION['lang']['nourut']."</td>
                <td align=center>" . $_SESSION['lang']['noakun'] . "</td>
                <td align=center>" . $_SESSION['lang']['nilai'] . "</td>
                <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                <td align=center colspan=2>" . $_SESSION['lang']['action'] . "</td>
            </tr>
            </thead><tbody>";
            $no=0;
            $colspan=2;
            $str="SELECT * from ".$dbname.".keu_notakredit_dt where notakredit='".$notakredit."'";
            $res=fetchData($str);
            foreach($res as $row=>$bar){
                #pembuat
                $whrak="noakun='".$bar['noakun']."'";
                $optak=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrak);
                $no+=1;
                echo"<tr class=rowcontent>
                    <td>".$no."</td>
                    <td>".$optak[$bar['noakun']]."</td>
                    <td align=right>".number_format($bar['nilai'])."</td>";
                echo"<td >".$bar['keterangan']."</td>";
                echo"<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdetail('".$bar['noakun']."','".number_format($bar['nilai'])."','".$jenis."','".$bar['keterangan']."')\"></td>";
                echo"<td ><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"deldetail('" . $bar['noakun']. "','".$bar['noakun']."');\" ></td>";  
                echo"</tr>";
                
            }
            echo"</tbody></table></fieldset>";
        CLOSE_BOX();
    break;

    case 'insert':

        $nilaiinvoice=str_replace(',', '', $nilaiinvoice);

        if ($tanggal=='' || $kodeorg=='' || $unit=='' || $noinvoice=='' || $nokontrak=='' || $jenis=='' || $customer=='' || $noakun=='' || $kurs=='' || $nilaiinvoice==0 ) {
            exit('warning : Field was empty.');
        }

        $tahunbulan = $param['kodeorg']."-NK".date('ymd');

        $query="select right(notakredit,3) as nomorurut from ".$dbname.".keu_notakredit_ht where left(notakredit,12) = '".$tahunbulan."' order by right(notakredit,3) desc";
        $qr=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
        $qr->setFetchMode(PDO::FETCH_ASSOC);
        $rp=$qr->fetch();

        if(intval($rp['nomorurut'])==0){
          $awal = 1;
        }else{
          $awal = intval($rp['nomorurut'])+1;
        }

        $notakredit=$tahunbulan.addZero($awal,3);

        $str="insert into ".$dbname.".keu_notakredit_ht (notakredit,tipe,tanggal,revisi,kodeorg,jenis,noinvoice_referensi,nokontrak,kodecustomer,nilaiinvoice,keterangan,matauang,kurs,noakun,createdby,updateby)
                values ('".$notakredit."','".$tipe."','".$tanggal."','".$revisi."','".$unit."','".$jenis."','".$noinvoice."','".$nokontrak."','".$customer."','".$nilaiinvoice."','".$keterangan."','".$matauang."','".$kurs."','".$noakun."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

        echo $notakredit;

    break;

    case'deldt':

        $strdt = "delete from ".$dbname.".keu_notakredit_ht where notakredit='".$notakredit."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'update':

        $nilaiinvoice=str_replace(',', '', $nilaiinvoice);

        if ($tanggal=='' || $kodeorg=='' || $unit=='' || $noinvoice=='' || $jenis=='' || $customer=='' || $noakun=='' || $kurs=='' || $nilaiinvoice==0 ) {
            exit('warning : Field was empty.');
        }

        $strht = "update ".$dbname.".keu_notakredit_ht set tipe='".$tipe."',tanggal='".$tanggal."',revisi='".$revisi."',keterangan='".$keterangan."',updateby='".$_SESSION['standard']['userid']."', nilaiinvoice='".$nilaiinvoice."'  where notakredit='".$notakredit."'";             
            try
            {
                $owlPDO->exec($strht);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

        echo $notakredit;

        break;

    case 'posting':

        //get noakun debet kredit tahunan
        $ressup=$owlPDO->query("select * from ".$dbname.".keu_notakredit_ht where notakredit='".$notakredit."'");
        $ressup->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$ressup->fetch();
        $kodejurnal="NOTAK";
        $akkredit=$bar['noakun'];
        $tgljurnal=str_replace('-', '', $bar['tanggal']);
        $induk=substr($notakredit,0,3);
        $unit=$bar['kodeorg'];
        $kodecustomer=$bar['kodecustomer'];
        $noinvoice=$bar['noinvoice_referensi'];
        $whrsup="kodecustomer='".$kodecustomer."'";
        $optsup=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrsup);
        $keterangan2='Nota kredit atas No.invoice : '.$bar['noinvoice_referensi'].'; '.$optsup[$kodecustomer].'/'.$bar['keterangan'];

        # Get Journal Counter
        $queryJ=selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'");
        $tmpKonter=fetchData($queryJ);
        $konter=addZero($tmpKonter[0]['nokounter']+1,3);
        # Prep No Jurnal
        $notrans=$tgljurnal."/".$unit."/".$kodejurnal."/".$konter;

        $tmpJml = 0;
        $strdet="select * from ".$dbname.".keu_notakredit_dt where notakredit='".$notakredit."'";
        $dataD=fetchData($strdet);
        foreach($dataD as $row) {
            if($row['nilai']>0)
            $tmpJml += $row['nilai'];
        }
        $selisih = abs($tmpJml - $bar['nilaiinvoice']);
        if($selisih > 0.01) {
            echo "Warning : Jumlah Header dan Detail Tidak Balance\n";
            echo "Header:".number_format($bar['nilaiinvoice'])."\n";
            echo "Detail:".number_format($tmpJml)."\n";
            echo "Posting Gagal";
            exit;
        }

        $nourut=1;
        $errorDB="";
        $strht = "insert into ".$dbname.".keu_jurnalht (nojurnal,kodejurnal,totaldebet,totalkredit,tanggal,tanggalentry, autojurnal,noreferensi,matauang,kurs) 
        values ('".$notrans."','".$kodejurnal."','".$bar['nilaiinvoice']."','".(-1)*($bar['nilaiinvoice'])."','".$tgljurnal."','".date('Ymd')."','1','".$notakredit."','IDR','1')";
        try{
            $owlPDO->exec($strht);

            foreach($dataD as $row){

                $strdt[]= "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodecustomer)
                values ('".$notrans."','".$tgljurnal."','".$nourut."','".$row['noakun']."','".$keterangan2."','" .$row['nilai']. "','IDR','1','".$unit."','".$noinvoice."','".$notakredit."','".$kodecustomer."')";
                $nourut++;

            }

            $strdt[] = "insert into ".$dbname.".keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan, jumlah, matauang,kurs,kodeorg,nodok,noreferensi,kodecustomer)
            values ('".$notrans."','".$tgljurnal."','".$nourut."','".$akkredit."','".$keterangan2."','".(-1)*($bar['nilaiinvoice'])."','IDR','1','".$unit."','".$noinvoice."','".$notakredit."','".$kodecustomer."')";

            if (count($strdt)!=0) {
                for($i=0; $i<count($strdt); $i++){
                    try{
                        $owlPDO->exec($strdt[$i]);  
                    }catch (PDOException $e) {
                        $errorDB .= "Detail: ".$strdt[$i]." ". $e->getMessage() ; 
                    }
                }
            }
            

            if ($errorDB=="") {
                $strkj="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeorg='".$induk."' and kodekelompok='".$kodejurnal."'";
                try{
                    $owlPDO->exec($strkj);
                }catch (PDOException $e){
                    echo "Gagal : ".$e->getMessage();
                    die();
                }
            }
            
            
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

        $strnd="update ".$dbname.".keu_notakredit_ht set posting='1',postingby='".$_SESSION['standard']['userid']."' where notakredit='".$notakredit."'";             
        try
        {
            $owlPDO->exec($strnd);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'loadData':
        $where=" 1=1 ";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $where.="";
        }
    
        $where.= " and kodeorg in (".getOrgDetail(2).")";

        if ($notakredit!='') {
            $where.=" and notakredit like '%".$notakredit."%' ";
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

        $str="select * from ".$dbname.".keu_notakredit_ht where ".$where;
        $res=fetchData($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_notakredit_ht where ".$where." order by tanggal desc limit ".$offset.",".$limit."";
            $tab="";
            $res=fetchData($str);
            foreach($res as $row=>$bar){
                #pembuat
                $whrsup="kodecustomer='".$bar['kodecustomer']."'";
                $optSup=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrsup);
                $whrKar3="karyawanid='".$bar['postingby']."'";
                $optPosting=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);
                
                $colspan='colspan=4';
                $tab.="<tr class=rowcontent>
                    <td>".$bar['notakredit']."</td>
                    <td>".$bar['kodeorg']."</td>
                    <td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optjenis[$bar['jenis']]."</td>
                    <td>".$bar['noinvoice_referensi']."</td>
                    <td>".$optSup[$bar['kodecustomer']]."</td>
                    <td>".$bar['keterangan']."</td>
                    <td align=right>".number_format($bar['nilaiinvoice'])."</td>
                    <td>".$optPosting[$bar['postingby']]."</td>";
                if ($bar['posting']==0){
                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar['notakredit']."','".$bar['nokontrak']."','".$bar['tipe']."','".tanggalnormal($bar['tanggal'])."','".$bar['revisi']."','".substr($bar['notakredit'],0,3)."','".$bar['kodeorg']."','".$bar['jenis']."','".$bar['noinvoice_referensi']."','".$bar['kodecustomer']."','".number_format($bar['nilaiinvoice'])."','".$bar['keterangan']."','".$bar['noakun']."','".$bar['matauang']."','".$bar['kurs']."')\"></td>
                           <td><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"deldt('" . $bar['notakredit']. "');\" ></td>
                           <td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting' onclick=\"posting('".$bar['notakredit']."');\" ></td>";
                }else{
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted' ></td>";   
                }
                $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('".$bar['notakredit']."',event);\" ></td>";   
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
                <tr><td colspan=14  valign=top align=center>
                <img src=\"images/skyblue/first.png\"  onclick=loadData(1);>
                <img src=\"images/skyblue/prev.png\"  onclick=loadData(".($page-1).");>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <img src=\"images/skyblue/next.png\"  onclick=loadData(".($page+1).");>
                <img src=\"images/skyblue/last.png\"  onclick=loadData(".($totrows-1).");>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

    case 'viewdetail':

        $tab.="<fieldset><legend>".$notakredit."</legend>";
        $tab.="<table cellpading=1 cellspacing=1 border=0 class=sortable  style='float:left;'>";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['noakun']."</td>";
        $tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
        $tab.="<td>".$_SESSION['lang']['debet']."</td>";
        $tab.="<td>".$_SESSION['lang']['kredit']."</td>";
        $tab.="</tr></thead><tbody >";

        $no=0;
        $str="select * from ".$dbname.".keu_notakredit_dt where notakredit='".$notakredit."'";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {

            $no+=1;
            $whrno="noakun='".$bar['noakun']."'";
            $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);

            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['noakun']."</td>
                <td>".$optnmakun[$bar['noakun']]."</td>
                <td align=right>".number_format($bar['nilai'],2)."</td>
                <td align=right>".number_format(0,2)."</td>";
                $debet+=$bar['nilai'];
            $tab.="</tr>";
        }

        $no+=1;
        $str="select * from ".$dbname.".keu_notakredit_ht where notakredit='".$notakredit."'";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $whrno="noakun='".$bar['noakun']."'";
        $optnmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',$whrno);
        $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['noakun']."</td>
                <td>".$optnmakun[$bar['noakun']]."</td>
                <td align=right>".number_format(0,2)."</td>
                <td align=right>".number_format($bar['nilaiinvoice'],2)."</td>";
                $kredit=$bar['nilaiinvoice'];
        $tab.="</tr>";

            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=3>".$_SESSION['lang']['total']."</td>";
            $tab.="<td align=right>".number_format($debet,2)."</td>";
            $tab.="<td align=right>".number_format($kredit,2)."</td>";
            $tab.="</tr>";

        $tab.="</tbody>";
        $tab.="</table></fieldset>";

        echo $tab;
    break;

    case 'insertdt':

        $nilai=str_replace(',', '', $nilai);

        if ($noakun=='' || $nilai=='') {
            exit('warning : Field was empty.');
        }

        $str="select * from ".$dbname.".keu_notakredit_dt where notakredit='".$notakredit."' and noakun='".$noakun."' ";
        $res=fetchData($str);
        if (count($res)>0) {
            exit('warning : Data sudah ada.');
        }

        $str="insert into ".$dbname.".keu_notakredit_dt (notakredit,noakun,nilai,keterangan)
                values ('".$notakredit."','".$noakun."','".$nilai."','".$keterangan."')";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

    break;

    case 'updatedt':

        $nilai=str_replace(',', '', $nilai);

        if ($noakun=='' || $nilai=='') {
            exit('warning : Field was empty.');
        }

        $strht = "update ".$dbname.".keu_notakredit_dt set noakun='".$noakun."',nilai='".$nilai."',keterangan='".$keterangan."' where notakredit='".$notakredit."' and noakun='".$noakundtold."'";    
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'deldetail':

        $whr=" notakredit='".$notakredit."' and noakun='".$noakun."'";
        $strdt = "delete from ".$dbname.".keu_notakredit_dt where ".$whr;
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;
    
    default:
        
    break;
}


?>
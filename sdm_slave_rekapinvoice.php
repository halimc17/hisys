<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/terbilang.php');
include_once('lib/rTable.php');

$pt = checkPostGet('pt','');
$notransaksi = checkPostGet('notransaksi','');
$oldnotransaksi = checkPostGet('oldnotransaksi','');
$noinvoice = checkPostGet('noinvoice','');
$oldnoinvoice = checkPostGet('oldnoinvoice','');
$jenisdata = checkPostGet('jenisdata','');
$rute = checkPostGet('rute','');
$tanggalrute = checkPostGet('tanggalrute','');
$oldtanggalrute = checkPostGet('oldtanggalrute','');
$jumlah = checkPostGet('jumlah','');
$keterangan = checkPostGet('keterangan','');
$method = checkPostGet('method','');
$notranscr = checkPostGet('notranscr', '');
$tipecr = checkPostGet('tipecr', '');
$rutecr = checkPostGet('rutecr', '');
$notransaksicr = checkPostGet('notransaksicr', '');
$noinvoicecr = checkPostGet('noinvoicecr', '');
$periode = checkPostGet('periode', '');

switch ($method) {

	case 'insert':

        $str="select notransaksi from ".$dbname.".sdm_rekapinvoice where notransaksi='".$notransaksi."' and noinvoice='".$noinvoice."' and tanggalrute='".$tanggalrute."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $baris=owlBaris($res);
        if($baris>0){
            exit('Warning : Data already exist.');
        }

        $str="insert into ".$dbname.".sdm_rekapinvoice (jenisdata,notransaksi,noinvoice,tanggalrute,rute,jumlah,keterangan,periode,createdby,updateby)
                values ('".$jenisdata."','".$notransaksi."','".$noinvoice."','".$tanggalrute."','".$rute."','".$jumlah."','".$keterangan."','".$periode."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}

	break;

    case'deldt':

        $strdt = "delete from ".$dbname.".sdm_rekapinvoice where notransaksi='".$notransaksi."' and noinvoice='".$noinvoice."' and tanggalrute='".$tanggalrute."'";
        try {
            $owlPDO->exec($strdt);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'updatedt':

        $str="select notransaksi from ".$dbname.".sdm_rekapinvoice where notransaksi='".$notransaksi."' and noinvoice='".$noinvoice."' and tanggalrute='".$tanggalrute."' and posting=1";
        $res=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $baris=owlBaris($res);
        if($baris>0){
            exit('Warning : Data already exist.');
        }

        $strht="update ".$dbname.".sdm_rekapinvoice set notransaksi='".$notransaksi."', noinvoice='".$noinvoice."', updateby='".$_SESSION['standard']['userid']."', tanggalrute='".$tanggalrute."', rute='".$rute."', jumlah='".$jumlah."', keterangan='".$keterangan."' where notransaksi='".$oldnotransaksi."' and noinvoice='".$oldnoinvoice."' and tanggalrute='".$oldtanggalrute."'"; 

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

    case 'posting':

        $strht = "update ".$dbname.".sdm_rekapinvoice set posting='1' where notransaksi='".$notransaksi."' and noinvoice='".$noinvoice."' and tanggalrute='".$tanggalrute."'";             
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

    case 'loadData':
        $where = "";
        $where = " createdby ='".$_SESSION['standard']['userid']."'";

        if ($tipecr != '') {
            $where.=" and jenisdata='" . $tipecr . "' ";
        }
        if ($rutecr != '') {
            $where.=" and rute like '%" . $rutecr . "%' ";
        }
        if ($notransaksicr != '') {
            $where.=" and notransaksi like '%" . $notransaksicr . "%' ";
        }
        if ($noinvoicecr != '') {
            $where.=" and noinvoice like '%" . $noinvoicecr . "%' ";
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
        $tab='';
        $str="select * from ".$dbname.".sdm_rekapinvoice where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=13>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_rekapinvoice where ".$where." order by notransaksi desc limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){

                if ($bar['jenisdata']=='Dinas'){
                    $notransaksi=$bar['notransaksi'];
                    $strak="select b.namakaryawan from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.notransaksi='".$bar['notransaksi']."'"; 
                }

                if ($bar['jenisdata']=='Cuti'){
                    $notransaksiex=explode('/', $bar['notransaksi']);
                    $karyawanid=$notransaksiex[1];
                    $notransaksi=tanggalnormal($notransaksiex[0]);

                    $strak="select b.namakaryawan from ".$dbname.".sdm_ijin a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid='".$karyawanid."'"; 
                }
                
                $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
                $resak->setFetchMode(PDO::FETCH_ASSOC);
                $barak=$resak->fetch();

                #nama pembuat
                $whrKar1="karyawanid='".$bar['createdby']."'";
                $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);

                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar['jenisdata']."</td>
                    <td>".$barak['namakaryawan']." (".$notransaksi.")</td>
                    <td>".$bar['noinvoice']."</td>
                    <td>".$bar['rute']."</td>
                    <td align=right>".number_format($bar['jumlah'])."</td>
                    <td>".$bar['keterangan']."</td>
                    <td>".$optkaryawan[$bar['createdby']]."</td>";
                if ($bar['posting']==0){
                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar['jenisdata']."','".$bar['notransaksi']."','".$bar['noinvoice']."','".$bar['rute']."','".$bar['jumlah']."','".$bar['keterangan']."','".$bar['tanggalrute']."','".$bar['periode']."')\"></td>
                           <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('" . $bar['notransaksi']. "','".$bar['noinvoice']."','".$bar['tanggalrute']."');\" ></td>
                           <td align=center><img src=images/icons/04/16/01.png class=resicon  title='Posting' onclick=\"posting('".$bar['notransaksi']."','".$bar['noinvoice']."','".$bar['tanggalrute']."');\" ></td>";
                }else{
                    $tab.="<td align=center colspan=3><img src=images/icons/04/16/02.png class=resicon  title='Posted'></td>";
                }
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
                <tr><td colspan=15 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;

	case 'excel':
        $where = "";
        $where = " createdby ='".$_SESSION['standard']['userid']."'";

        if ($tipecr != '') {
            $where.=" and jenisdata='" . $tipecr . "' ";
        }
        if ($rutecr != '') {
            $where.=" and rute like '%" . $rutecr . "%' ";
        }
        if ($notransaksicr != '') {
            $where.=" and notransaksi like '%" . $notransaksicr . "%' ";
        }
        if ($noinvoicecr != '') {
            $where.=" and noinvoice like '%" . $noinvoicecr . "%' ";
        }

        $tab="<table cellpading=1 cellspacing=1 border=1 class=sortable >";
        $tab.="<thead>";
        $tab.="<tr align=center><td>".$_SESSION['lang']['nourut']."</td>";
        $tab.="<td>".$_SESSION['lang']['jenis']."</td>";
        $tab.="<td>".$_SESSION['lang']['notransaksi']."</td>";
        $tab.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $tab.="<td>".$_SESSION['lang']['rute']."</td>";
        $tab.="<td>".$_SESSION['lang']['jumlah']."</td>";
        $tab.="<td>".$_SESSION['lang']['keterangan']."</td>";
        $tab.="</tr></thead><tbody>";

        $str="SELECT * from ".$dbname.".sdm_rekapinvoice where ".$where." order by notransaksi desc ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){

            if ($bar['jenisdata']=='Dinas'){
                $notransaksi=$bar['notransaksi'];
                $strak="select b.namakaryawan from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.notransaksi='".$bar['notransaksi']."'"; 
            }

            if ($bar['jenisdata']=='Cuti'){
                $notransaksiex=explode('/', $bar['notransaksi']);
                $karyawanid=$notransaksiex[1];
                $notransaksi=tanggalnormal($notransaksiex[0]);

                $strak="select b.namakaryawan from ".$dbname.".sdm_ijin a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid='".$karyawanid."'"; 
            }
            
            $resak=$owlPDO->query($strak) or die(print " Gagal: ".PDOException::getMessage());
            $resak->setFetchMode(PDO::FETCH_ASSOC);
            $barak=$resak->fetch();

            #nama pembuat
            $whrKar1="karyawanid='".$bar['createdby']."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);

            $no+=1;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center;'>".$no."</td>
                <td>".$bar['jenisdata']."</td>
                <td>".$barak['namakaryawan']." (".$notransaksi.")</td>
                <td>'".$bar['noinvoice']."</td>
                <td>".$bar['rute']."</td>
                <td align=right>".number_format($bar['jumlah'])."</td>
                <td>".$bar['keterangan']."</td>";
            $tab.="</tr>";
        }

        $tab.="</tbody></table>";

        $tglSkrg = date("Ymd");
        $nop_ = "Laporan_rekapinvoice_".$tglSkrg;
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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

    case'getformnotrans':
        
        $form="";
        $data="";
        if ($jenisdata=='Dinas') {
            $data.= "<td>".$_SESSION['lang']['notransaksi']."</td>";
            $data.= "<td>:</td>";
            $data.= "<td><input type=text class=myinputtext id=notran></td>";
        }
        if ($jenisdata=='Cuti') {
            $data.= "<td>".$_SESSION['lang']['tanggal']."</td>";
            $data.= "<td>:</td>";
            $data.= "<td><input type=text class=myinputtext id=tanggal></td>";
        }
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= $data;
        $form.= "<td><button class=mybutton onclick=findnotrans()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=container2></div>";
        echo $form;
    break;  

    case'getdatanotrans':

        $data="";
        $dt  ="";
        if ($jenisdata=='Dinas') {
            $dt.= "<td>".$_SESSION['lang']['notransaksi']."</td>";

            if($_POST['notran']!=''){
                $where.=" and b.notransaksi like '%".$_POST['notran']."%'";
            }

            $str="select b.notransaksi,b.dari,b.tujuan,a.karyawanid, left(waktu,10) as tanggalrute, a.tanggalbuat from ".$dbname.".sdm_pjdinasht a left join ".$dbname.".sdm_pjdinasdt_rute b on a.notransaksi=b.notransaksi where b.notransaksi not in (select notransaksi from ".$dbname.".sdm_rekapinvoice where posting=1 and tanggalrute=left(waktu,10)) ".$where." and statuspersetujuan=1 order by b.notransaksi ";
        }
        
        if ($jenisdata=='Cuti') {
            $dt.= "<td>".$_SESSION['lang']['tanggal']."</td>";

            if($_POST['tanggal']!=''){
                $where.="where tanggal ='".tanggalsystemn($_POST['tanggal'])."'";
            }

            $str="select karyawanid,tanggal,rutekeberangkatan,rutekepulangan,tanggalberangkat,tglpulang from ".$dbname.".sdm_ijin ".$where." and statuspersetujuan=1 order by tanggal ";
        }

        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.=$dt;
        $data.="<td>".$_SESSION['lang']['namakaryawan']."</td>";
        $data.="<td>".$_SESSION['lang']['rute']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){

            $whr=" karyawanid='".$bar['karyawanid']."'";
            $optkry=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);

            if($jenisdata=='Dinas'){
                $data.="<tr class=rowcontent style='cursor:pointer' onclick=setdata('".$bar['notransaksi']."','".$bar['tanggalrute']."',".$no.",'".substr($bar['tanggalbuat'],0,7)."')>";
                $data.="<td>".$bar['notransaksi']."</td>";
                $data.="<td>".$optkry[$bar['karyawanid']]."</td>";
                $data.="<td id=datarute_".$no.">".$bar['dari']." - ".$bar['tujuan']."</td>";
                $data.="</tr>";
                $no++;
            }

            if($jenisdata=='Cuti'){

                $tanggal=preg_replace("/-/",'',$bar['tanggal']);
                $notransaksidt=$tanggal."/".$bar['karyawanid'];

                $str1="select notransaksi from ".$dbname.".sdm_rekapinvoice where notransaksi='".$notransaksidt."' and tanggalrute='".$bar['tanggalberangkat']."' and posting=1";
                $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $baris=owlBaris($res1);

                if($baris==0){
                    $data.="<tr class=rowcontent style='cursor:pointer' onclick=setdata('".$notransaksidt."','".$bar['tanggalberangkat']."','".$no."','".substr($bar['tanggal'],0,7)."')>";
                    $data.="<td>".tanggalnormal($bar['tanggal'])."</td>";
                    $data.="<td>".$optkry[$bar['karyawanid']]."</td>";
                    $data.="<td id=datarute_".$no.">".$bar['rutekeberangkatan']."</td>";
                    $data.="</tr>";
                    $no++;
                }

                $str1="select notransaksi from ".$dbname.".sdm_rekapinvoice where notransaksi='".$notransaksidt."' and tanggalrute='".$bar['tglpulang']."' and posting=1";
                $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_ASSOC);
                $baris=owlBaris($res1);

                if($baris==0){
                    $data.="<tr class=rowcontent style='cursor:pointer' onclick=setdata('".$notransaksidt."','".$bar['tglpulang']."','".$no."','".substr($bar['tanggal'],0,7)."')>";
                    $data.="<td>".tanggalnormal($bar['tanggal'])."</td>";
                    $data.="<td>".$optkry[$bar['karyawanid']]."</td>";
                    $data.="<td id=datarute_".$no.">".$bar['rutekepulangan']."</td>";
                    $data.="</tr>";
                    $no++;
                }
            }
        }
        $data.= "</table></div></fieldset>";
        echo $data;
    break;

    case'getformnoinvoice':
        
        $form="";
        $form = "<fieldset><legend>".$_SESSION['lang']['find']."</legend>";
        $form.= "<table>";
        $form.= "<tr>";
        $form.= "<td>".$_SESSION['lang']['notransaksi']."</td>";
        $form.= "<td>:</td>";
        $form.= "<td><input type=text class=myinputtext id=notran></td>";
        $form.= "<td><button class=mybutton onclick=findnoinvoice()>Find</button></td>";
        $form.= "</tr>";
        $form.= "</table>";
        $form.= "</fieldset>
                 <div id=container3></div>";
        echo $form;
    break;  

    case'getdatanoinvoice':

        if($_POST['notran']!=''){
            $where.=" and a.noinvoice like '%".$_POST['notran']."%'";
        }

        $data="";
        $data.="<fieldset><legend>".$_SESSION['lang']['result']."</legend>";
        $data.="<div style=overflow:auto;width:826px;height:350px;>";
        $data.="<table cellpading=0 cellspacing=1 width=100% class=sortable >";
        $data.="<thead>";
        $data.="<tr align=center>";
        $data.="<td>".$_SESSION['lang']['noinvoice']."</td>";
        $data.="<td>".$_SESSION['lang']['jumlah']."</td>";
        $data.="<td>".$_SESSION['lang']['keterangan']."</td>";
        $data.="</tr></thead>";
        
        #data
        $no=0;
        $str="select noinvoice,nilaiinvoice,keterangan from ".$dbname.".keu_tagihanht where tipeinvoice='t' and noinvoice not in (select noinvoice from ".$dbname.".sdm_rekapinvoice where posting=1) ".$where."";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){

            $data.="<tr class=rowcontent style='cursor:pointer' onclick=setdatainv('".$bar['noinvoice']."','".$bar['nilaiinvoice']."',".$no.")>";
            $data.="<td>".$bar['noinvoice']."</td>";
            $data.="<td align=right>".number_format($bar['nilaiinvoice'])."</td>";
            $data.="<td id=ketinvoice_".$no.">".$bar['keterangan']."</td>";
            $data.="</tr>";
            $no++;

        }
        $data.= "</table></div></fieldset>";
        echo $data;
    break;
	
	default:
		# code...
	break;
}


?>
<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses=($_GET['proses'] == '' ? $_POST['proses'] : $_GET['proses']);
$param=$_POST;
$proses=checkPostGet('proses','');

$tanggal=tanggalsystemn(checkPostGet('tanggal',''));

$kodeorg=checkPostGet('kodeorg','');
$unit=checkPostGet('unit','');

$keterangan=checkPostGet('keterangan','');

$matauang=checkPostGet('matauang','');
$kurs=checkPostGet('kurs','');
$nilai=checkPostGet('nilai','');
$kode = checkPostGet('jenis','');
$notransaksi=checkPostGet('notransaksi','');
$noreferensi=checkPostGet('noref','');


switch ($proses) {
    case 'getunit':
        $arrnpwp=$arrUnit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $lstUnit=getOrgDetail(1);
        $dtMul=0;
        $listOrg='';
        foreach($lstUnit as $row=>$isiDt){
            if(substr($row,0,5)=='Pilih'){
                continue;
            }
            if($dtMul==0){
                $listOrg="'".$row."'";
                $dtMul=1;
            }else{
                $listOrg.=",'".$row."'";
            }
        }

        # Options
        $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$param['kdpt']."' and kodeorganisasi in (".$listOrg.")";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()) {
            if($param['kodeunit']==$bar['kodeorganisasi']){ 
                $arrUnit.="<option value='".$bar['kodeorganisasi']."' selected>".$bar['namaorganisasi']."</option>";
            }else{
                $arrUnit.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";    
            }
            
        }

        # Options
        $str="select npwp from ".$dbname.".setup_org_npwp where kodeorg='".$param['kdpt']."' and status=1";
        $res=$owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar=$res->fetch()){
            if($param['npwp']==$bar['npwp']){ 
                $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
            }else{
                if ($bar['defaults']=1) {
                    $arrnpwp.="<option value='".$bar['npwp']."' selected>".$bar['npwp']."</option>";
                }else{
                    $arrnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
                }
            }
        }

        echo $arrUnit."####".$arrnpwp;
    break;

    case 'getDetail':
        
        $sql = "select";

        $str="select * from ".$dbname.".keu_uangmuka where notransaksi='".$_POST['notransaksi']."' ";
        $tab="";

        $query = fetchData($str);
     foreach($query as $row=>$bar){

        $whrUangMuka="kode='".$bar['id_master_uangmuka']."'";
        $optJenisUangMuka=makeOption($dbname,'keu_5jenisuangmuka','kode,nama_uangmuka',$whrUangMuka);


            if ($bar['id_master_uangmuka']=="PJD"){
                $whrKar2="karyawanid='".$bar['penerima_id']."'";
                $penerima=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            } else {
                $whr="supplierid='".$bar['penerima_id']."'";
                $penerima=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
                }

        $tab="<fieldset><legend>".$_POST['notransaksi']."</legend><table cellspacing=1 cellpadding=1 border=0>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td>:</td><td>".$_POST['notransaksi']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td>".$bar['unit']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td>".tanggalnormal($bar['tanggal'])."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['nilai']."</td><td>:</td><td>".number_format($bar['nilaiuangmuka'])."</td></tr>";
        
        
        $tab.="<tr><td>".$_SESSION['lang']['noreferensi']."</td><td>:</td><td>".$bar['no_transaksi_ref']."</td></tr>";
       
        $tab.="<tr><td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>".$optJenisUangMuka[$bar['id_master_uangmuka']]."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['penerima']."</td><td>:</td><td>".$penerima[$bar['penerima_id']]."</td></tr>";
        $tab.="</table>";
    }

        echo $tab;

    break;
    

    case 'insert':
        $data = $_POST;
         #tambahkan di sini pengecekan obligatory
        ########################################
        if ($data['tanggal']=='') {
            exit('warning : Tanggal harus diisi');
        }

        if ($data['unit']=='') {
            exit('warning : Unit harus diisi');
        }

        if ($data['jenis']=='') {
            exit('warning : Jenis harus diisi');
        }

        if ($data['notransaksireferensi']=='') {
            exit('warning : No. Transaksi Ref. harus diisi');
        }

        if ($data['penerima']=='') {
            exit('warning : Penerima harus diisi');
        }

        if ($data['nilai']=='') {
            exit('warning : Nilai harus diisi');
        }

        $str = "insert  into keu_uangmuka (notransaksi,unit,tanggal,id_master_uangmuka,noakun,no_transaksi_ref,penerima,keterangan,nilaiuangmuka,createby,updateby)
                                            values
                                            ('".$_POST['notransaksi']."','".$_POST['unit']."')";

        $createby=$updateby=$_SESSION['standard']['userid'];
        $dataPrep = [
                        'notransaksi'           =>$_POST['notransaksi'],
                        'unit'                  =>$_POST['unit'],
                        'tanggal'               =>tanggalsystemn($_POST['tanggal']),
                        'id_master_uangmuka'    =>$_POST['jenis'],
                        'no_transaksi_ref'      =>$_POST['notransaksireferensi'],
                        'penerima_id'           =>$_POST['penerima'],
                        'keterangan'            =>$_POST['keterangan'],
                        'nilaiuangmuka'         =>str_replace(",", "",$_POST['nilai']),
                        'createby'             =>$createby,
                        'updateby'              =>$updateby      
                     ];

        foreach ($dataPrep as $key=>$val){
                $field  .= $key.",";
                $value  .= "'".$val."'".",";
        }
        $field=rtrim($field,',');
        $value=rtrim($value,',');
        if ($query[0]['jumlah']>0){
            exit('Error : transaksi ini sudah memiliki uang muka. '.$query[0]['jumlah']);
        } else {
            try {
                $sql = "insert into ".$dbname.".keu_uangmuka (".$field.")
                values
                (".$value.")";
                $owlPDO->exec($sql);
            }catch(PDOException $e){
                echo " Gagal," . addslashes($e->getMessage());
            }
        }

    break;

    case 'update':
    $data = $_POST;
     #tambahkan di sini pengecekan obligatory
    ########################################
    if ($data['tanggal']=='') {
        exit('warning : Tanggal harus diisi');
    }

    if ($data['unit']=='') {
        exit('warning : Unit harus diisi');
    }

    if ($data['jenis']=='') {
        exit('warning : Jenis harus diisi');
    }

    if ($data['notransaksireferensi']=='') {
        exit('warning : No. Transaksi Ref. harus diisi');
    }

    if ($data['penerima']=='') {
        exit('warning : Penerima harus diisi');
    }

    if ($data['nilai']=='') {
        exit('warning : Nilai harus diisi');
    }

    $createby=$updateby=$_SESSION['standard']['userid'];
    $dataPrep = [
                    'unit'                  =>$_POST['unit'],
                    'tanggal'               =>tanggalsystemn($_POST['tanggal']),
                    'id_master_uangmuka'    =>$_POST['jenis'],
                    'no_transaksi_ref'      =>$_POST['notransaksireferensi'],
                    'penerima_id'           =>$_POST['penerima'],
                    'keterangan'            =>$_POST['keterangan'],
                    'nilaiuangmuka'         =>str_replace(",", "",$_POST['nilai']),
                    'updateby'              =>$updateby      
                 ];

    foreach ($dataPrep as $key=>$val){
            $field  .= $key.",";
            $value  .= "'".$val."'".",";
    }

    foreach ($dataPrep as $key=>$val){
        $valueToUpdate .= $key."='".$val."',";
    }
    $valueToUpdate=rtrim($valueToUpdate,',');

        try {
            $sql = "update ".$dbname.".keu_uangmuka SET ".
                $valueToUpdate
            ." where notransaksi='".$_POST['notransaksi']."'";
            $owlPDO->exec($sql);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }

    break;
    
    case 'delete':   
    try {
        $sql = "delete from ".$dbname.".keu_uangmuka where notransaksi='".$_POST['notransaksi']."'";
        $owlPDO->exec($sql);
    }catch(PDOException $e){
        echo " Gagal," . addslashes($e->getMessage());
    }
    break;

    case 'postingData':
    $postingby =$_SESSION['standard']['userid'];
    try {
        $sql = "update ".$dbname.".keu_uangmuka set posting=1,postingby='".$postingby."' where notransaksi='".$_POST['notransaksi']."' ";
        $owlPDO->exec($sql);
    }catch(PDOException $e){
        echo " Gagal," . addslashes($e->getMessage());
    }
    break;



    case 'loadData':
        $where=" 1=1 ";
        if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
            $where.="";
            $where.= " and unit in (".getOrgDetail(2).")";
        }else{
            $where.=" and (unit='".$_SESSION['empl']['lokasitugas']."'";
            $where.= " or unit in (".getOrgDetail(2)."))";
        }

        

        #PENGATURAN PAGING
        $limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        #END PENGATURAN PAGING

        if ($_POST['sJenis']=='notransaksi' && $_POST['sNoTrans']!='') {
            $where.=" and notransaksi like '%".$_POST['sNoTrans']."%'";
        }

       

        if ($_POST['sJenis']=='no_transaksi_ref' && $_POST['sNoTrans']!='') {
            $where.=" and no_transaksi_ref like '%".$_POST['sNoTrans']."%'";
        }

        if ($_POST['penerima']!='') {
            $where.=" and penerima_id='".$_POST['penerima']."'";
        }
    
        //exit("Error ".$where);
    
    $str = "select * from ".$dbname.".keu_uangmuka where ".$where;
    //exit("Error under paging ".$str);
    $query = fetchData($str);
    $jlhbrs=count($query);
    //$jlhbrs=0;
    if($jlhbrs==0){
        $tab.="<tr class=rowcontent>";
        $tab.="<td colspan=14>".$_SESSION['lang']['dataempty']."</td>";
        $tab.="</tr>";
    }else
    {
        //exit("Error else");
        $no=$maxdisplay;
        $str="select * from ".$dbname.".keu_uangmuka where ".$where." order by updatetime desc limit ".$offset.",".$limit."";
        $tab="";

        $query = fetchData($str);
        foreach($query as $row=>$bar){
                #pembuat
                $whrKar2="karyawanid='".$bar['updateby']."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);

                    $whrUangMuka="kode='".$bar['id_master_uangmuka']."'";
                    $optJenisUangMuka=makeOption($dbname,'keu_5jenisuangmuka','kode,nama_uangmuka',$whrUangMuka);

            
                if ($bar['id_master_uangmuka']=="PJD"){
                    $whrKar2="karyawanid='".$bar['penerima_id']."'";
                    $penerima=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                } else {
                    $whr="supplierid='".$bar['penerima_id']."'";
                    $penerima=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
                     }

            $whrKar3="karyawanid='".$bar['postingby']."'";
            $optPosting=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);
            
            $postedby = ($bar['postingby']=='') ? "[". $_SESSION['lang']['belumposting']."]" : $optPosting[$bar['postingby']];
            $tab.="<tr class=rowcontent>
                    <td>".$bar['notransaksi']."</td>
                    <td>".$bar['unit']."</td>
                    <td>".tanggalnormal($bar['tanggal'])."</td>
                    <td>".$optJenisUangMuka[$bar['id_master_uangmuka']]."</td>
                    <td>".$bar['no_transaksi_ref']."</td>
                    <td>".$penerima[$bar['penerima_id']]."</td>
                    
                    <td align=right>".number_format($bar['nilaiuangmuka'])."</td>
                    <td>".$bar['updatetime']."</td>
                    <td>".$bar['keterangan']."</td>
                    <td>".$postedby."</td>";
                if ($bar['posting']==0){

                    $sql = "select induk from ".$dbname.".organisasi where kodeorganisasi='".$bar['unit']."' ";
                    $pt = fetchData($sql);

                    $sql = "select noakun from ".$dbname.".keu_5jenisuangmuka where kode='".$bar['id_master_uangmuka']."' ";
                    $noakun = fetchData($sql);

                    $tab.="<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' 
                    onclick=\"edit('".$bar['notransaksi']."','".($pt[0]['induk'])."','".$bar['unit']."','".tanggalnormal($bar['tanggal'])."','".$bar['id_master_uangmuka']."','".$noakun[0]['noakun']."','".$bar['no_transaksi_ref']."','".$bar['penerima_id']."','".number_format($bar['nilaiuangmuka'])."','".$bar['keterangan']."')\" ></td>
                           <td><img src=images/skyblue/delete.png class=resicon  title='Delete' onclick=\"Delete('".$bar['notransaksi']."');\" ></td>
                           <td align=center><img src=images/skyblue/posting.png class=resicon  title='Posting Data' onclick=\"postingData('".$bar['notransaksi']."');\" ></td>";
                }else{
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td>&nbsp;</td>";
                    $tab.="<td align=center><img src=images/skyblue/posted.png class=resicon  title='Posted' ></td>";   
                }
                $tab.="<td align=center><img src=images/skyblue/pdf.jpg class=resicon class=zImgBtn height='30'  title='PDF' onclick=\"detailPDF('" . $bar['notransaksi']. "',event);\" ></td>";   
                $tab.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewDetailData2('" . $bar['notransaksi']. "',event);\" ></td>"; 
 
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
            <tr><td colspan=17  valign=top align=center>
            <img src=\"images/skyblue/first.png\"  onclick=loadData(0);>
            <img src=\"images/skyblue/prev.png\"  onclick=loadData(".($page-1).");>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <img src=\"images/skyblue/next.png\"  onclick=loadData(".($page+1).");>
            <img src=\"images/skyblue/last.png\"  onclick=loadData(".($totrows-1).");>
            </td>
            </tr>";
    }
    echo $tab."####".$footd;



        
       
    break;

    
    case 'fillNoAkun':
        $query="select a.noakun,b.namaakun from ".$dbname.".keu_5jenisuangmuka a 
        inner join keu_5akun b on (a.noakun=b.noakun) where a.kode='".$kode."'";
        $noAkun = fetchData($query);
        $noAkun = "<option value='".$noAkun[0]['noakun']."'>".$noAkun[0]['noakun']." - ".$noAkun[0]['namaakun']."</option>";

        echo $noAkun;

     
        //exit("Error ".$noAkun);

    break;

    case 'fillNoRek':
        if ($_POST['jenis']=="PJD")
        //$query = "select norekening from ".$dbname." ";
        //makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
        $norek = makeOption($dbname,'datakaryawan','karyawanid,norekeningbank',$_POST['penerima']);
        print_r($norek);

    break;

    case 'fillNoRef':
        $query = "select source from ".$dbname.".keu_5jenisuangmuka where kode='".$kode."'";
        $source = fetchData($query);
        $source = $source[0]['source'];

        switch ($source){
            case "PO":
                $query = "select distinct(a.kodesupplier) as id, a.nopo as noreferensi, b.namasupplier as nama from log_poht a
                            inner join log_5supplier b on (a.kodesupplier=b.supplierid) where a.kodeorg='".$_POST['kodeorg']."'";
            break;

            case "SPK":
                $query = "select distinct(a.koderekanan) as id, a.notransaksi as noreferensi, b.namasupplier as nama from log_spkht a
                inner join log_5supplier b on (a.koderekanan=b.supplierid) where a.kodeorg='".$_POST['unit']."'";
            break;

            case "PJD";
                $query = "select distinct(a.karyawanid) as id, a.notransaksi as noreferensi, b.namakaryawan as nama from sdm_pjdinasht a
                inner join datakaryawan b on (a.karyawanid=b.karyawanid) where a.kodeorg='".$_POST['unit']."'";  
            break;

           
        }
      //  exit("Error ".$query);
        $sources = fetchData($query);
        $option = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        foreach ($sources as $key=>$value){
            $option .= "<option value='".$value['noreferensi']."'>".$value['noreferensi']."</option>";
        }
            echo $option;

    break;

    case 'fillPenerima':
        
    switch ($kode){
        case "UPO":
            $query = "select a.kodesupplier as id,b.namasupplier as nama from log_poht a inner join log_5supplier b on(a.kodesupplier=b.supplierid) where a.nopo='".$noreferensi."'";
        break;

        case "SPK":
            $query = "select a.koderekanan as id,b.namasupplier as nama from log_spkht a inner join log_5supplier b on(a.koderekanan=b.supplierid) where a.notransaksi='".$noreferensi."'";
        break;

        case "PJD";
            $query = "select a.karyawanid as id,b.namakaryawan as nama from sdm_pjdinasht a inner join datakaryawan b on(a.karyawanid=b.karyawanid) where a.notransaksi='".$noreferensi."'";
        break;
    }
   // exit("Error ".$query);
    $penerima = fetchData($query);
    $penerima = "<option value='".$penerima[0]['id']."'>".$penerima[0]['nama']."</option>";
    echo $penerima;
    break;

    case "generateNoTran":
        $unit=$_POST['unit'];
        $formatNoTran = date('Ymd')."/UM/".$unit;

        $sql = "select lpad(max(cast(right(notransaksi,3) as unsigned))+1,3,0) as nourut from ".$dbname.".keu_uangmuka where unit='".$unit."' 
                 and notransaksi like CONCAT(CURDATE()+0,'%')";
        
                 //exit("Error ".$sql);
        $query = fetchData($sql);
        
        $nourut=($query[0]['nourut']==NULL) ? "001" : $query[0]['nourut'];
       // $generatedNoTran = $formatNoTran."/".$nourut;
        
        echo $nourut;
    break;
    

}


?>
<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$kdBarang = checkPostGet('kdBarang', '');
$satuan = checkPostGet('satuan', '');
$supplier = checkPostGet('supplier', '');
$idMatauang = checkPostGet('idMatauang', '');
$hrgPasar = checkPostGet('hrgPasar', '');
$status = checkPostGet('status', '');
$kdBrgCari = checkPostGet('kdBrgCari', '');
$tglHarga = tanggalsystem(checkPostGet('tglHarga', ''));
$path = "fileupload/hargasupplier/";
$nopp = checkPostGet('rnopp','');
$namafile = checkPostGet('namafile','');
$optNmBarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNmunit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmsupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$where = "unit='" . $unit . "' and tanggal='" . $tglHarga . "' and kodeproduk='" . $kdBarang . "' and supplier='" . $supplier . "'";

switch ($proses) {

    case'getSatuan':
        $sSatuan = "select distinct satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $kdBarang . "'";
        $qSatuan = $owlPDO->query($sSatuan) or die(print " Gagal: " . PDOException::getMessage());
        $qSatuan->setFetchMode(PDO::FETCH_ASSOC);
        $rSatuan = $qSatuan->fetch();
        echo $rSatuan['satuan'];
    break;

    case'insert':
        if($tglHarga==''){
            exit('warning :'.$_SESSION['lang']['notiftanggal']);
        }
        if($unit==''){
            exit('warning :'.$_SESSION['lang']['silakanisi']." ".$_SESSION['lang']['unit']);
        }
        if($supplier==''){
            exit('warning :'.$_SESSION['lang']['silakanisi']." ".$_SESSION['lang']['supplier']);
        }
        if($kdBarang==''){
            exit('warning :'.$_SESSION['lang']['silakanisi'].' '.$_SESSION['lang']['namabarang']);
        }
        if(($hrgPasar=='')||($hrgPasar=='0')){
            exit('warning :'.$_SESSION['lang']['harga']." ".$_SESSION['lang']['notifemptyzero']);
        }
        $sCek = "select distinct * from " . $dbname . ".pmn_hargapasarsolar where " . $where . "";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $rCek = owlBaris($qCek);
        if ($rCek < 1) {
            $sIns = "insert into " . $dbname . ".pmn_hargapasarsolar (unit,tanggal, kodeproduk, supplier, satuan, harga, matauang,statusharga) 
                   values ('".$unit."','" . $tglHarga . "','" . $kdBarang . "','" . $supplier . "','" . $satuan . "','" . $hrgPasar . "',"
                    . "'" . $idMatauang . "','" . $status . "')";
            try {
                $owlPDO->exec($sIns);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        } else {
            exit("Error: Already exist");
        }
    break;

    case'update':
        if($tglHarga==''){
            exit('warning :'.$_SESSION['lang']['notiftanggal']);
        }
        if($unit==''){
            exit('warning :'.$_SESSION['lang']['silakanisi']." ".$_SESSION['lang']['unit']);
        }
        if($supplier==''){
            exit('warning :'.$_SESSION['lang']['silakanisi']." ".$_SESSION['lang']['supplier']);
        }
        if($kdBarang==''){
            exit('warning :'.$_SESSION['lang']['silakanisi'].' '.$_SESSION['lang']['namabarang']);
        }
        if(($hrgPasar=='')||($hrgPasar=='0')){
            exit('warning :'.$_SESSION['lang']['harga']." ".$_SESSION['lang']['notifemptyzero']);
        }
        $sIns = "update " . $dbname . ".pmn_hargapasarsolar set statusharga='" . $status . "',harga='" . $hrgPasar . "',"
                . "matauang='" . $idMatauang . "',statusharga='" . $status . "' where " . $where . "";
        try {
            $owlPDO->exec($sIns);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'loadData':

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_hargapasarsolar order by `tanggal` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        $str = "select * from " . $dbname . ".pmn_hargapasarsolar order by `tanggal` desc  limit " . $offset . "," . $limit . "";
        if (($page * $limit) == 0) {
            $no = 0;
        } else {
            $no = ($page * $limit);
        }
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $barisData = owlBaris($res);
        if ($barisData > 0) {
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {

                $no+=1;


                echo"<tr class=rowcontent id='tr_" . $no . "'>
                        <td align=center>" . $no . "</td>
                        <td id=detail_kode".$no." hidden>".$bar->unit."-".str_replace('-','',$bar->tanggal)."-".$bar->kodeproduk."-".$bar->supplier."</td>
                        <td>".$bar->unit." - " . $optNmunit[$bar->unit] . "</td>
                        <td align=center>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>".$bar->kodeproduk." - " . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td align=center>" . $bar->satuan . "</td>
                        <td>" . $bar->supplier . " - " . $optNmsupp[$bar->supplier] . "</td>
                        <td align=center>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        <td>" . $bar->statusharga . "</td>
                        <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->unit . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->satuan . "','" . $bar->supplier . "','" . $bar->matauang . "','" . $bar->harga . "','" . $bar->statusharga . "');\"> 
                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar->unit . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->supplier . "');\"> 
                            <img src=images/upload-2-xxl.png class=resicon  title='Document' onclick='showupload(event,".$no.")' >
                        </td>
                        
                        </tr>";
            }
        } else {
            echo"<tr class=rowcontent><td colspan=12 style='text-align:center;'>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo"
                        <tr><td colspan=11 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariBast(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariBast(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        echo"</tbody></table>";
        break;


    case'cariData':
        $wre="";
        if ($kdBrgCari != '') {
            $wre.=" and kodeproduk='" . $kdBrgCari . "'";
        }
        if ($tglHarga != '') {
            $wre.=" and tanggal='" . $tglHarga . "'";
        }
        if ($supplier != '') {
            $wre.=" and supplier='" . $supplier . "'";
        }
        if ($unit != '') {
            $wre.=" and unit='" . $unit . "'";
        }
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;

        $ql2 = "select count(*) as jmlhrow from " . $dbname . ".pmn_hargapasarsolar where tanggal!='' " . $wre . " order by `tanggal` desc"; // echo $ql2;
        $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }


        $str = "select * from " . $dbname . ".pmn_hargapasarsolar where tanggal!='' " . $wre . " order by `tanggal` desc  limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $barisData = owlBaris($res);
        if ($barisData > 0) {
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {

                $no+=1;


                echo"<tr class=rowcontent id='tr_" . $no . "'>
                        <td align=center>" . $no . "</td>
                        <td id=detail_kode".$no." hidden>".$bar->unit."-".str_replace('-','',$bar->tanggal)."-".$bar->kodeproduk."-".$bar->supplier."</td>
                        <td>".$bar->unit." - " . $optNmunit[$bar->unit] . "</td>
                        <td align=center>" . tanggalnormal($bar->tanggal) . "</td>
                        <td>".$bar->kodeproduk." - " . $optNmBarang[$bar->kodeproduk] . "</td>
                        <td align=center>" . $bar->satuan . "</td>
                        <td>" . $bar->supplier . " - " . $optNmsupp[$bar->supplier] . "</td>
                        <td align=center>" . $bar->matauang . "</td>
                        <td align=right>" . number_format($bar->harga, 2) . "</td>
                        <td>" . $bar->statusharga . "</td>  
                        <td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->unit . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->satuan . "','" . $bar->supplier . "','" . $bar->matauang . "','" . $bar->harga . "','" . $bar->statusharga . "');\">
                            <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('" . $bar->unit . "','" . tanggalnormal($bar->tanggal) . "','" . $bar->kodeproduk . "','" . $bar->supplier . "');\">
                        <img src=images/upload-2-xxl.png class=resicon  title='Document' onclick='showupload(event,".$no.")' ></td>
                        </tr>";
            }
        } else {
            echo"<tr class=rowcontent><td colspan=12 style='text-align:center;'>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
        }
        echo"
                        <tr><td colspan=11 align=center>
                        " . (($page * $limit) + 1) . " to " . (($page + 1) * $limit) . " Of " . $jlhbrs . "<br />
                        <button class=mybutton onclick=cariTrans(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
                        <button class=mybutton onclick=cariTrans(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
                        </td>
                        </tr>";
        echo"</tbody></table>";
        break;


    case'delData':
        $sDel = "delete from " . $dbname . ".pmn_hargapasarsolar where " . $where . " ";
        try {
            $owlPDO->exec($sDel);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
        
        
        
        
        
                ########################################################################################################################################
        ########################################################################################################################################
        
        ########################################################################################################################################
        ########################################################################################################################################
        
        case 'showupload':
        $tab="";
        
        $tab.="<table cellspacing='1' border='0' id='uploadpopup'>
            <tr hidden>
                <td>No. Kontrak</td>
                <td>:</td>
                <td>
                    <label id='noppupload' style='font-weight:bold'>".$nopp."</label>
                </td>
            </tr>
            <tr>
                <td>Filename</td>
                <td>:</td>
                <td>
                    <input type='file' name='upload' id='upload' class=mybutton>
                </td>
            </tr>
            <tr>
                <td colspan=2></td>
                <td>
                    <button class=mybutton onclick=\"submitfile()\">Submit</button>
                </td>
            </tr>
        </table>
        <p />";
        
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center'>No.</td>
                    <td align='center'>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center'>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
        
        
        
        echo $tab;
    break;
        
    
        
    case 'submitfile':

        $tgl = date("YmdHis");
        // exit("error : ".$tgl);
        $data = $_POST;
        
        if($data['fileupload']!='')
        {
            if($_FILES['file']['error']==0)
            {
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = $_FILES['file']['tmp_name'];        
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
                {
                    if($_FILES['file']['size'] <= 250000)
                    {
                        $str = "insert into ".$dbname.".listfileupload values ('','".$data['rnopp']."','".$filename."','".$filetype."','others','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        try{
                                $owlPDO->exec($str);
                                if (!file_exists($path)) {
                                    mkdir($path, 0777, true);
                                }
                                file_put_contents($path.$filename,$file_tmpname);
                        }
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                    else
                    {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                }
            }
        }
    break;
    
        
        
    case 'loadfiles':
        $no = 0;
        $tab = "";
        // exit("Error:".$nopp);
    
        $str="select * from ".$dbname.".pmn_hargapasarsolar where tanggal='".$expl[0]."' and kodeproduk='".$expl[1]."' and supplier='".$expl[2]."'  ";
        $resv=fetchData($str);
        foreach($resv as $bar => $barv){
            $close = $barv['close'];    
        }
        
        $str="select * from ".$dbname.".listfileupload where notransaksi = '".$nopp."' and status='1'";
        $res=fetchData($str);
        if(empty($res))
        {
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }
        else
        {
            foreach($res as $key=>$val)
            {
                $no++;
                $tab.="<tr id='ppDetailTable' class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                    
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.png')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.pdf')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }
                elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }
                else
                {
                    $tab.="<td style='text-align:center'>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                $tab.="<td style='text-align:left'>".$val['namafile']."</td>
                    <td align=center>
                        <a href='fileupload/hargasupplier/".$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                if($close==0){
                    $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$nopp."','".$val['namafile']."');\" >";
                }
                $tab."  </td>
                </tr>";
            }   
        }
        echo $tab;
    break;  
        
        
    case 'deletefile':
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$nopp."' and namafile='".$namafile."'";
        try
        {
            $owlPDO->exec($str);
            $path = "fileupload/hargasupplier/".$namafile;
            unlink($path);
        }
        catch(PDOException $e)
        {
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    default:
        break;
}
?>
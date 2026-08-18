<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","NOT (tipe='PT' or tipe='HOLDING')",'2',true);
$optjenis = makeOption($dbname,"lgl_5checklist","kode,jenis","status=1");
$optKabupaten = makeOption($dbname,'kabupaten','kabupaten,kabupaten','','',true);
$optKecamatan = makeOption($dbname,'kecamatan','kecamatan,kecamatan','','',true);
$optDesa = makeOption($dbname,'desa','desa,desa','','',true);

$optPt = makeOption($dbname,'organisasi','kodeorganisasi,alokasi',"kodeorganisasi='".$data['kodeorg']."'");
$optKodeorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$path   = "fileupload/lgl_checklist/";

switch($proses) {
    case 'loadData' :
    
    
    $limit = 20;
    $page = 0;
    if (isset($_POST['page'])) {
        $page = $_POST['page'];
        if ($page < 0)
            $page = 0;
    }
    $offset = $page * $limit;

    $qcount ="select a.notransaksi, a.kodeorg, b.namaorganisasi, a.tanggalmulai, a.tanggalselesai from ".$dbname.".lgl_checklistht a ";
    $qcount .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    if(!empty($data['kodeorgcr']))
    {
        $qcount .="where a.kodeorg='".$data['kodeorgcr']."' ";
    }
    $qcount .="order by `tanggalmulai` desc";
    $rcount = fetchData($qcount);
    $jlhbrs = count($rcount);

    $totalPage = ceil($jlhbrs/$limit);
    $optPage = array();
    $totalPage<1 ? $totalPage=1 : null;
    for($i=1;$i<=$totalPage;$i++) {
        $optPage[$i-1] = $i;
    }

    $queryAll ="select a.posting, a.notransaksi, a.kodeorg, b.namaorganisasi, a.tanggalmulai, a.tanggalselesai, a.jenis, c.jenis as namajenis from ".$dbname.".lgl_checklistht a ";
    $queryAll .="LEFT JOIN ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi ";
    $queryAll .="LEFT JOIN ".$dbname.".lgl_5checklist c on a.jenis=c.kode ";
    if(!empty($data['kodeorgcr']))
    {
        $queryAll .="where a.kodeorg='".$data['kodeorgcr']."' ";
    }
    $queryAll .="order by `tanggalmulai` desc limit " . $offset . "," . $limit . "";
    $resAll = fetchData($queryAll);

    
    foreach ($resAll as $key => $row) {
        $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
        foreach($row as $col=>$dat) 
        {
            if($col=='kodeorg')
            {
                $table .= "<td hidden id='".$col."_".$key."'>".$dat."</td>";
            }
            elseif($col=='jenis')
            {
                $table .= "<td hidden id='".$col."_".$key."'>".$dat."</td>";
            }
            elseif($col=='posting')
            {

            }
            else
            {
                if($col=='tanggalmulai')
                {
                    $dat=tanggalnormal($dat);
                }
                if($col=='tanggalselesai')
                {
                    $dat=tanggalnormal($dat);
                }
                $table .= "<td id='".$col."_".$key."'>".$dat."</td>";
            }
        }
            
                $table .= "<td>";
                if($row['posting']==0){
                $table .= "<img src='images/application/application_edit.png' ";
                $table .= "class=resicon  caption='Edit' onclick='edit(".$key.")'>";
                $table .= "<img src='images/application/application_delete.png' ";
                $table .= "class=resicon  caption='Delete' onclick='deletehd(".$key.")'>";
                }
                $table .= "<img src='images/pdf.jpg' ";
                $table .= "class=resicon  caption='Print' onclick=\"masterPDF('lgl_checklistht','" . $row['notransaksi'] . "," . $row['kodeorg'] . "," . $row['jenis'] . "','','lgl_slave_checklistpdf',event)\">";

                if($row['posting']==0){
                $table .= "<img src='images/skyblue/posting.png' class='resicon' onclick=\"postingData('" . $row['notransaksi'] . "')\" title='Posting'>";
                }
                else
                {
                $table .= "<img src='images/skyblue/posted.png' class='resicon' title='Posting'>";    
                }
                $table .="<img src=images/zoom.png class=resicon title='View' onclick=\"html('".$row['notransaksi']."','html');\"></td>";
           
            $table .= "</tr>";

    }
    if(!empty($data['kodeorgcr']))
    {
        $tablef ="<td colspan=14 align=center>
        <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast('',0,'','".$data['kodeorgcr']."');>&nbsp;
        <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast('min'," . ($page - 1) . ",".($totalPage-1).",'".$data['kodeorgcr']."');>&nbsp;
        ".makeElement('pages','select',$page,array('style'=>'width:50px',
            'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
        <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast('plus'," . ($page + 1) . ",".($totalPage-1).",'".$data['kodeorgcr']."');>&nbsp;
        <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast('',".($totalPage-1).",'','".$data['kodeorgcr']."');>
        </td>";
    }
    else
    {
        $tablef ="<td colspan=14 align=center>
        <img src='images/".$_SESSION['theme']."/first.png'style='cursor:pointer' onclick=cariBast('',0,'','');>&nbsp;
        <img src='images/".$_SESSION['theme']."/prev.png'style='cursor:pointer' onclick=cariBast('min'," . ($page - 1) . ",0,'');>&nbsp;
        ".makeElement('pages','select',$page,array('style'=>'width:50px',
            'onchange'=>'cariBast(this.value)'),$optPage)."&nbsp;
        <img src='images/".$_SESSION['theme']."/next.png'style='cursor:pointer' onclick=cariBast('plus'," . ($page + 1) . ",".($totalPage-1).",'');>&nbsp;
        <img src='images/".$_SESSION['theme']."/last.png'style='cursor:pointer' onclick=cariBast('',".($totalPage-1).",'','');>
        </td>"; 
    }
    


    $xxys= $table.'###'.$tablef;
    echo $xxys;
    break;

    case 'checkHeader':
    $query=selectQuery($dbname,"lgl_checklistht","kodeorg","tanggalmulai='".$data['tanggalmulai']."' and kodeorg='".$data['kodeorg']."' and tanggalselesai='".$data['tanggalselesai']."' and jenis='".$data['jenis']."'");
    $res=fetchData($query);
    $jlmh=count($res);
    
    if($jlmh!=0)
    {
        exit("Warning: Data ".$optOrg[$data['kodeorg']]." , tanggal mulai ".$data['tanggalmulai'].", tanggal selesai ".$data['tanggalselesai'].", jenis checklist ".$optjenis[$data['jenis']]." sudah ada");
    }
    else
    {
        
        $column = array('notransaksi','kodeorg','tanggalmulai','tanggalselesai','jenis');
        $query = selectQuery($dbname,"lgl_checklistht","notransaksi");
        $id = fetchData($query);
        $maxid=1;
        if(!empty($id)) {
        foreach($id as $row) {
        $temporg = explode("/",$row['notransaksi']);
        intval($temporg[2])>=$maxid ? $maxid=intval($temporg[2]) : false;
        }
        $maxid++;
        }
        $konter = addZero($maxid,3);
        $data['notransaksi']=date('Ymdhis').'/'.$data['kodeorg'].'/'.$konter;
        $data['tanggalmulai']= tanggalsystem($data['tanggalmulai']);
        $data['tanggalselesai']=tanggalsystem($data['tanggalselesai']);
        $qIns = insertQuery($dbname,'lgl_checklistht',$data,$column);
        try{
            $owlPDO->exec($qIns); 
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
        }

        echo $data['notransaksi'];

    }

    break;
    case 'getWeek':
     $query=selectQuery($dbname,"setup_periodeakuntansi","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."'");
    $res=fetchData($query);
    $minggu=0;
        $listTgl=array();
        $batasAtas=intval(substr($res[0]['tanggalsampai'],-2,2));
        for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
             if($tglawal<10){
                $strTgl=$res[0]['periode']."-0".$tglawal;
            }else{
                $strTgl=$res[0]['periode']."-".$tglawal;
            }
            if($tglawal%7==1){
                if($minggu<=3)
                {
                    $minggu+=1;
                }
                $listTgl[$minggu][]=date('d-m-Y', strtotime($strTgl));
            }else{
                $listTgl[$minggu][]=date('d-m-Y', strtotime($strTgl));
            }
        }
        /*print_r($listTgl);
        exit();*/
    if(isset($data['ws']))
    {
        $optWeek="<option value='' ></option>";
        for($i=1;$i<=$minggu;$i++){
            if($data['ws']==$i){
                 $optWeek.="<option value=".$i." selected>Ke-".$i."</option>";    
            }
            else
            {
                $optWeek.="<option value=".$i.">Ke-".$i."</option>";
            }
            
        }
    }
    else
    {
         $optWeek="<option value='' selected></option>";
        for($i=1;$i<=$minggu;$i++){
            $optWeek.="<option value=".$i.">Ke-".$i."</option>";
        }
    }
    echo $optWeek;
    break;
    case 'plusdetail':
        $query=selectQuery($dbname,"setup_periodeakuntansi","periode,tanggalmulai,tanggalsampai","periode='".$data['periode']."' and kodeorg='".$data['kodeorg']."'");
        $res=fetchData($query);
        $minggu=0;
        $listTgl=array();
        $batasAtas=intval(substr($res[0]['tanggalsampai'],-2,2));
        for($tglawal=1;$tglawal<=$batasAtas;$tglawal++){
             if($tglawal<10){
                $strTgl=$res[0]['periode']."-0".$tglawal;
            }else{
                $strTgl=$res[0]['periode']."-".$tglawal;
            }
            if($tglawal%7==1){
                if($minggu<=3)
                {
                    $minggu+=1;
                }
                $listTgl[$minggu][]=date('d-m-Y', strtotime($strTgl));
            }else{
                $listTgl[$minggu][]=date('d-m-Y', strtotime($strTgl));
            }
        }
        $where = " (lokasitugas='" . $data['kodeorg'] . "' or subbagian='".$data['kodeorg']."') and statuskaryawan != 'Keluar' and (tanggalkeluar>='" . $data['periode'] . "' or tanggalkeluar='0000-00-00')";
        $where.=" and kodejabatan in ('77','78','79','80','81') ";
        $optKary = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan",$where,'',true);

        $formdet .= "<tr id='tr_".$data['nomer']."' class='rowcontent' style='cursor:pointer'>";
        $formdet .= "<td>".makeElement('karyawanid_'.$data['nomer'],'selectsearch','','',$optKary)."</td>";
        foreach ($listTgl[$data['minggu']] as $key => $value)
        {   
            if($btsbwTgl==0)
            {
                $btsbwTgl=intval($value);
            }
            $formdet .= "<td align=center>".makeElement('shift_'.$data['nomer'].'_'.intval($value),'select','',array('style'=>'width:50px'),$optShift)."</td>";
            $btsatgl=intval($value);
        }
        $formdet .="</tr>";

        echo $formdet;


    break;
    case 'getDetail':
    
    $stredit="select * from ".$dbname.".lgl_checklistht where notransaksi='".$data['notransaksi']."' ";
    $resedit=fetchData($stredit);

    $streditdet="select * from ".$dbname.".lgl_checklistdt where notransaksi='".$data['notransaksi']."' ";
    $reseditdet=fetchData($streditdet);
    $dataedit=array();
    foreach ($reseditdet as $ky => $vle) {
        $dataedit[$vle['kodechdt']]['checklist']=$vle['cheklist'];
        $dataedit[$vle['kodechdt']]['keterangan']=$vle['keterangan'];
    }

    /*print_r($dataedit);
    exit();*/

    $formdet ="<fieldset id='fieldFormDet' style='width:100%; clear:right;min-height:auto;'>";
    $formdet .="<legend>Form Checklist : ".$optjenis[$data['jenis']]."</legend>";
    if($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU'){
        $formdet .= "<table align=center>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('pt','label','PT',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('pt','text',$optKodeorg[$optPt[$data['kodeorg']]],array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('berkedudukandi','label','Berkedudukan Di',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('berkedudukandi','text',$resedit[0]['berkedudukandi'],array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('letaktanah','label','Letak Tanah',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('letaktanah','text',$resedit[0]['letaktanah'],array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('desa','label','Desa',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('desa','selectsearch',$resedit[0]['desa'],array('style'=>'width:190px'),$optDesa);
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('kecamatan','label','Kecamatan',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('kecamatan','selectsearch',$resedit[0]['kecamatan'],array('style'=>'width:190px'),$optKecamatan);
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('kabupaten','label','Kabupaten',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('kabupaten','selectsearch',$resedit[0]['kabupaten'],array('style'=>'width:190px'),$optKabupaten);
        $formdet .= "</td>";
        $formdet .= "</tr>";

        $formdet .= "<tr>";
        $formdet .= "<td>";
        $formdet .= makeElement('luastanah','label','Luas',array('style'=>'width:190px'));
        $formdet .= "</td>";
        $formdet .= "<td>";
        $formdet .= makeElement('luastanah','textnum',$resedit[0]['luastanah'],array('style'=>'width:190px','onkeyup'=>"z.numberFormat('luastanah')"));
        $formdet .= "Ha</td>";
        $formdet .= "</tr>";

        $formdet .= "</table>";
    }
    
    $formdet .= "<table cellspacing='0' border='1' >";
    $formdet .= "<tr>";
    $formdet .= "<td>Dokumen Referensi</td>";
    $formdet .= "<td>Pertanyaan</td>";
    if($optjenis[$data['jenis']]!='ISPO'){
    $formdet .= "<td>Panduan</td>";}
    if($optjenis[$data['jenis']]=='ISPO'){
    $formdet .= "<td></td>";
    $formdet .= "<td>Kriteria</td>";
    $formdet .= "<td></td>";
    $formdet .= "<td>Indikator</td>";
    $formdet .= "<td>Panduan</td>";
    }
    $formdet .= "<td>Ya/Tidak</td>";
    $formdet .= "<td>Keterangan</td>";
    $formdet .= "<td>Upload</td>";
    $formdet .= "</tr>";
    
    $str="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and tipe=1 order by nourut";    
    $res=fetchData($str);
    $deskripsi=array();
    foreach ($res as $key => $val) {
        $deskripsi[$val['noinduk']]=$val['deskripsi'];
    }

    $str="select min(kode) as minnourut, max(kode) as maxnourut from lgl_5checklistdet where kodeheader='".$data['jenis']."' and tipe=0 ";    
    $res=fetchData($str);
    $maxnourut=$res[0]['maxnourut'];
    $minnourut=$res[0]['minnourut'];

    /*print_r($deskripsi);
    exit();*/
    $str0="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk=0 and tipe=0 order by nourut"; 
    $res0=fetchData($str0);
    foreach ($res0 as $key0 => $value0) 
    {
            
            $formdet .= "<tr>";
            $formdet .= "<td>".$value0['nourut']."</td>";
            $formdet .= "<td>".str_replace('####','<br />',$value0['deskripsi'])."</td>";
            if($optjenis[$data['jenis']]=='SMK3'){
                if($deskripsi[$value0['kode']]=='')
                {
                    $formdet .= "<td></td><td></td><td></td><td></td>";
                }
                else
                {
                    $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                    $formdet .= "<td align=center>".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td align=center>".makeElement('keterangan_'.$value0['kode'],'textarea',$dataedit[$value0['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                    $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value0['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                }
            }
            elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
            {
                if($deskripsi[$value0['kode']]=='')
                {
                    $formdet .= "<td></td>";
                    $formdet .= "<td align=center>".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td align=center>".makeElement('keterangan_'.$value0['kode'],'textarea',$dataedit[$value0['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                    $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value0['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                }
                else
                {
                    $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value0['kode']])."</td>";
                    $formdet .= "<td align=center>".makeElement('checklist_'.$value0['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                    $formdet .= "<td align=center>".makeElement('keterangan_'.$value0['kode'],'textarea',$dataedit[$value0['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                    $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value0['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                }
            }
            if($optjenis[$data['jenis']]!='ISPO'){
            $formdet .= "</tr>";}
            $str1="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value0['kode']."' and tipe=0 order by nourut";    
            $res1=fetchData($str1);
            foreach ($res1 as $key1 => $value1)
            {
                if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "<tr>";}
                if($optjenis[$data['jenis']]=='ISPO' && $key1>0){
                    $formdet .= "<tr><td></td><td></td>";}
                $formdet .= "<td>".$value0['nourut'].".".$value1['nourut']."</td>";
                $formdet .= "<td>".str_replace('####','<br />',$value1['deskripsi'])."</td>";
                if($optjenis[$data['jenis']]=='SMK3'){
                    if($deskripsi[$value1['kode']]=='')
                    {
                        $formdet .= "<td></td><td></td><td></td><td></td>";
                    }
                    else
                    {
                        $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value1['kode']])."</td>";
                        $formdet .= "<td align=center>".makeElement('checklist_'.$value1['kode'],'check',$dataedit[$value1['kode']]['checklist'])."</td>";
                        $formdet .= "<td align=center>".makeElement('keterangan_'.$value1['kode'],'textarea',$dataedit[$value1['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                        $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value1['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                    }
                }
                elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                {
                    if($deskripsi[$value1['kode']]=='')
                    {
                        $formdet .= "<td></td>";
                        $formdet .= "<td align=center>".makeElement('checklist_'.$value1['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                        $formdet .= "<td align=center>".makeElement('keterangan_'.$value1['kode'],'textarea',$dataedit[$value0['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                        $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value1['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                    }
                    else
                    {
                        $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                        $formdet .= "<td align=center>".makeElement('checklist_'.$value1['kode'],'check',$dataedit[$value0['kode']]['checklist'])."</td>";
                        $formdet .= "<td align=center>".makeElement('keterangan_'.$value1['kode'],'textarea',$dataedit[$value0['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                        $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value0['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                    }
                }
                if($optjenis[$data['jenis']]!='ISPO'){
                $formdet .= "</tr>";}
                $str2="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value1['kode']."' and tipe=0 order by nourut";    
                $res2=fetchData($str2);
                foreach ($res2 as $key2 => $value2)
                {
                    if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "<tr>";
                    $formdet .= "<td>".$value0['nourut'].".".$value1['nourut'].".".$value2['nourut']."</td>";}
                    else{
                        if($key2>0)
                        {
                            $formdet .= "<tr><td></td><td></td><td></td><td></td><td>".$value2['nourut']."</td>";
                        }
                        else
                        {
                            $formdet .= "<td>".$value2['nourut']."</td>";
                        }
                    }
                    $formdet .= "<td>".str_replace('####','<br />',$value2['deskripsi'])."</td>";
                    if($optjenis[$data['jenis']]=='SMK3' || $optjenis[$data['jenis']]=='ISPO'){
                        if($deskripsi[$value2['kode']]=='')
                        {
                            
                            if($optjenis[$data['jenis']]=='ISPO')
                            {   
                                $formdet .= "<td></td>";
                                $formdet .= "<td align=center>".makeElement('checklist_'.$value2['kode'],'check',$dataedit[$value2['kode']]['checklist'])."</td>";
                                $formdet .= "<td align=center>".makeElement('keterangan_'.$value2['kode'],'textarea',$dataedit[$value2['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                                $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value2['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                                $formdet .="</tr>";
                            }
                            else
                            {
                                $formdet .= "<td></td><td></td><td></td><td></td>";
                            }
                        }
                        else
                        {
                            $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                            $formdet .= "<td align=center>".makeElement('checklist_'.$value2['kode'],'check',$dataedit[$value2['kode']]['checklist'])."</td>";
                            $formdet .= "<td align=center>".makeElement('keterangan_'.$value2['kode'],'textarea',$dataedit[$value2['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                            $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value2['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                        }
                    }
                    elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                        {
                            $formdet .= "<td></td><td></td><td></td>";
                        }
                    if($optjenis[$data['jenis']]!='ISPO'){
                    $formdet .= "</tr>";}
                    $str3="select * from lgl_5checklistdet where kodeheader='".$data['jenis']."' and noinduk='".$value2['kode']."' and tipe=0 order by nourut";    
                    $res3=fetchData($str3);
                    foreach ($res3 as $key3 => $value3)
                    {
                        if($optjenis[$data['jenis']]!='ISPO'){
                        $formdet .= "<tr>";}
                        $formdet .= "<td>".$value0['nourut'].".".$value1['nourut'].".".$value2['nourut'].".".$value3['nourut']."</td>";
                        $formdet .= "<td>".str_replace('####','<br />',$value3['deskripsi'])."</td>";
                        if($optjenis[$data['jenis']]=='SMK3'){
                            if($deskripsi[$value3['kode']]=='')
                            {
                                $formdet .= "<td></td><td></td><td></td><td></td>";
                            }
                            else
                            {
                                $formdet .= "<td>".str_replace('####','<br />',$deskripsi[$value2['kode']])."</td>";
                                $formdet .= "<td align=center>".makeElement('checklist_'.$value3['kode'],'check',$dataedit[$value3['kode']]['checklist'])."</td>";
                                $formdet .= "<td align=center>".makeElement('keterangan_'.$value3['kode'],'textarea',$dataedit[$value3['kode']]['keterangan'],array('style'=>'min-width:360px;min-height:80px'))."</td>";
                                $formdet .= "<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$data['jenis']."','".$data['kodeorg']."','".$data['notransaksi']."','".$value3['kode']."')\" src='images/upload-2-xxl.png'/></td>";
                            }
                        }
                        elseif($optjenis[$data['jenis']]=='Permohonan HGU' || $optjenis[$data['jenis']]=='Permohonan Perpanjangan HGU' || $optjenis[$data['jenis']]=='Proper')
                        {
                            $formdet .= "<td></td><td></td><td></td>";
                        }
                    }

                        
                }
            }
      $formdet .= "</tr>";  
    }

   
    $formdet .= "</table>";
    $formdet .= "<table align=center>";
     $formdet .= "<tr>";
    $formdet .= "<td>";
    $formdet .=  makeElement('saveDetailButton','button',$_SESSION['lang']['save'],array('onclick'=>"saveData('".$maxnourut."','".$minnourut."','".$optjenis[$data['jenis']]."','".$data['notransaksi']."','".$data['method']."')"));
    $formdet .= "</td>";
    $formdet .= "</tr>";
    $formdet .= "</table>";
    $formdet .= "</fieldset>";
    echo $formdet;
    
    break;
    
    case 'saveData':

    $checklistarr = explode('###', $data['checklist']);
    echo $data['jenis'];

    $datains['datacheck']=array();
    if($data['jenis']=='Permohonan Perpanjangan HGU' || $data['jenis']=='Permohonan HGU' || $data['jenis']=='Proper')
    {
        if($data['jenis']=='Permohonan Perpanjangan HGU' || $data['jenis']=='Permohonan HGU'){  
        $datass['pt']=$data['pt'];
        $datass['berkedudukandi']=$data['berkedudukandi'];
        $datass['letaktanah']=$data['letaktanah'];
        $datass['desa']=$data['desa'];
        $datass['kecamatan']=$data['kecamatan'];
        $datass['kabupaten']=$data['kabupaten'];
        $datass['luastanah']=$data['luastanah'];
        $qUpdate = updateQuery($dbname,'lgl_checklistht',$datass,"notransaksi='".$data['notransaksi']."'");
        try
        {
            $owlPDO->exec($qUpdate); 
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
        }
        }
        foreach ($checklistarr as $key => $value) {
        $checklistdet = explode('/', $value);
        $datains['datacheck'][$key]['notransaksi']=$data['notransaksi'];
        $datains['datacheck'][$key]['kodechdt']=$checklistdet[2];
        $datains['datacheck'][$key]['cheklist']=$checklistdet[0];
        $datains['datacheck'][$key]['keterangan']=$checklistdet[1];
        }
    }
    else
    {
        foreach ($checklistarr as $key => $value) {
        $checklistdet = explode('/', $value);
        $datains['datacheck'][$key]['notransaksi']=$data['notransaksi'];
        $datains['datacheck'][$key]['kodechdt']=$checklistdet[2];
        $datains['datacheck'][$key]['cheklist']=$checklistdet[0];
        $datains['datacheck'][$key]['keterangan']=$checklistdet[1];
        }
    }
    //print_r($datains);
        foreach ($checklistarr as $key => $value) {
        $qdetIns = insertQuery($dbname,'lgl_checklistdt',$datains['datacheck'][$key]);
        try
        {
            $owlPDO->exec($qdetIns); 
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
        }
        }            
            
        
    
    //echo $datahd;
    break;
    case 'updateData':
    
        $checklistarr = explode('###', $data['checklist']);
        echo $data['jenis'];

        $datains['datacheck']=array();
        if($data['jenis']=='Permohonan Perpanjangan HGU' || $data['jenis']=='Permohonan HGU' || $data['jenis']=='Proper')
        {
            if($data['jenis']=='Permohonan Perpanjangan HGU' || $data['jenis']=='Permohonan HGU'){  
            $datass['pt']=$data['pt'];
            $datass['berkedudukandi']=$data['berkedudukandi'];
            $datass['letaktanah']=$data['letaktanah'];
            $datass['desa']=$data['desa'];
            $datass['kecamatan']=$data['kecamatan'];
            $datass['kabupaten']=$data['kabupaten'];
            $datass['luastanah']=$data['luastanah'];
            $qUpdate = updateQuery($dbname,'lgl_checklistht',$datass,"notransaksi='".$data['notransaksi']."'");
            try
            {
                $owlPDO->exec($qUpdate); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
            }
            foreach ($checklistarr as $key => $value) {
            $checklistdet = explode('/', $value);
            $datains['datacheck'][$key]['notransaksi']=$data['notransaksi'];
            $datains['datacheck'][$key]['kodechdt']=$checklistdet[2];
            $datains['datacheck'][$key]['cheklist']=$checklistdet[0];
            $datains['datacheck'][$key]['keterangan']=$checklistdet[1];
            }
        }
        else
        {
            foreach ($checklistarr as $key => $value) {
            $checklistdet = explode('/', $value);
            $datains['xcv'][$key]['notransaksi']=$data['notransaksi'];
            $datains['xcv'][$key]['kodechdt']=$checklistdet[2];
            $datains['datacheck'][$key]['cheklist']=$checklistdet[0];
            $datains['datacheck'][$key]['keterangan']=$checklistdet[1];
            }
        }

            foreach ($checklistarr as $key => $value) {
            $qdetIns = updateQuery($dbname,'lgl_checklistdt',$datains['datacheck'][$key]," 
                notransaksi='".$datains['xcv'][$key]['notransaksi']."'  and 
                kodechdt='".$datains['xcv'][$key]['kodechdt']."' ");
            try
            {
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
            }

    break;
    case 'showupload':
        $tab = "";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        @$lxxx.="".$_SESSION['lang']['notransaksi']."";
        @$lyyy.="Kode Chekclist";
        $tab.="<tr>
        <td>".$_SESSION['lang']['unit']."</td>
        <td>:</td>
        <td>
        <label id='ptupload' style='display:none'>".$data['kodeorg']."</label>
        <label style='font-weight:bold'>".$optKodeorg[$data['kodeorg']]."</label>
        <label style='font-weight:bold'>".$optjenis[$data['jenisupload']]."</label>
        </td>
        </tr>
        <tr>
        <td>".$lxxx."</td>
        <td>:</td>
        <td>
        <label id='xxx' style='font-weight:bold'>".$data['xxx']."</label>
        </td>
        </tr>
        <tr>
        <td>".$lyyy."</td>
        <td>:</td>
        <td>
        <label id='yyy' style='font-weight:bold'>".$data['yyy']."</label>
        </td>
        </tr>";
        $tab.="<tr><td colspan=4><hr></td></tr>
            <tr>
            <td>Filename</td>
            <td>:</td>
            <td>
            <input type='file' name='upload' id='upload' >
            </td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>
            <button class=mybutton onclick=\"submitfile('".$data['jenisupload']."')\">Submit</button>
            </td>
            </tr>
            </table>
            <p />";
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
            <thead>
            <tr class=rowheader>
            <td align='center' width=50px>No.</td>
            <td align='center' width=50px>File Type</td>
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
    $tgl = date("YmdHis");
    $his = date("His");
    $optnourut=makeOption($dbname,'lgl_5checklistdet','kode,nourut',"kode='".$data['yyy']."'");
    $data = $_POST;
    if ($data['fileupload'] != '') {
        if ($_FILES['file']['error'] == 0) {
            $filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
            $filename = $data['kodeorg']."_".$optjenis[$data['jenisupload']]."_".$optnourut[$data['yyy']]."_".$his."".$filetype;
            $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
            if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                if ($_FILES['file']['size'] <= 250000000) {
                    $str = "insert into ".$dbname.".listfile_lgl_checklist values ('','".$data['kodeorg']."','".$data['jenisupload']."','".$data['xxx']."','".$data['yyy']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                    try {
                        $owlPDO->exec($str);
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        file_put_contents($path.$filename, $file_tmpname);
                    } catch (PDOException $e) {
                        echo " Gagal,".addslashes($e->getMessage());
                    }
                } else {
                    exit("warning : Ukuran file upload maksimal 250kb");
                }
            } else {
                exit("Warning : Format file upload harus .jpg atau .jpeg");
            }
        }
    }
    break;
    case 'loadfiles':
    $no = 0;
    $tab = $icon = "";
    $str = "select * from ".$dbname.".listfile_lgl_checklist where kodeorg = '".$data['kodeorg']."' and status='1' and jenis='".$data['jenisupload']."' and field1='".$data['xxx']."' and field2='".$data['yyy']."'";
    //exit('error'.$str);
    $res = fetchData($str);
    if (empty($res)) {
        $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    } else {
        foreach($res as $key => $val) {
            $no++;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
            $icon = seticonfile($val['formaticon']);
            $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";
            $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
            $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['jenis']."','".$val['kodeorg']."','".$val['field1']."','".$val['field2']."','".$val['namafile']."');\" >";
            $tab."  </td>
            </tr>";
        }
    }
    echo $tab;
    break;
    case 'loadfiles2':
    $no = 0;
    $tab = $icon = "";
    $str = "select * from ".$dbname.".listfile_lgl_checklist where field1='".$data['notransaksi']."'";
    //exit('error'.$str);s
    $res = fetchData($str);
    if (empty($res)) {
        $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    } else {
        foreach($res as $key => $val) {
            $no++;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
            $icon = seticonfile($val['formaticon']);
            $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";
            $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
            $tab."  </td>
            </tr>";
        }
    }
    echo $tab;
    break;
    case 'viewfile':
    $tab = "";
    $tab.="<img src='".$path.$data['namafile']."' style='width:600px;height:400px;'>";
    echo $tab;
    break;
    case 'deletefile':
    $str = "delete from ".$dbname.".listfile_lgl_checklist where kodeorg='".$data['kodeorg']."' and jenis='".$data['jenisupload']."' and field1='".$data['xxx']."' and field2='".$data['yyy']."'  and namafile='".$data['namafile']."'"; //exit('error'.$str);
    try {
        $owlPDO->exec($str);
        $pathx = $path.$data['namafile'];
        unlink($pathx);
    } catch (PDOException $e) {
        echo " Gagal,".addslashes($e->getMessage());
    }
    break;
    case 'posting':
        $str = "update ".$dbname.".lgl_checklistht set posting=1 where notransaksi='" . $data['notransaksi']."'";
        //exit($str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    case 'saveDataDetail':
    $shift = explode('###', $data['shift']);
    $datadt=array();
    $no=0;
        $datadt['notransaksi']=$data['notransaksi'];
        $datadt['karyawanid']=$data['karyawanid'];
        for ($t=$data['batasbawah'];$t<=$data['batasatas'];$t++) {
            if($t<10){
                $datadt['tanggal']=$data['periode']."-0".$t;
            }else{
                $datadt['tanggal']=$data['periode']."-".$t;
            }
            $datadt['kodeshift']=$shift[$no];
            $no++;
            $qdetIns = insertQuery($dbname,"sdm_jadwalsecuritydt",$datadt);
           // print_r($qdetIns);
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
        }
    break;
    case 'deletehd':
   
            $qdetIns = deleteQuery($dbname,"lgl_checklistht","notransaksi='".$data['notransaksi']."'");
            //print_r($qdetIns);
            //exit();
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
    break;

    case 'DelData':
    $tanggal='';
        //$datadt['notransaksi']=$data['notransaksi'];
        //$datadt['karyawanid']=$data['karyawanid'];
        for ($t=$data['batasbawah'];$t<=$data['batasatas'];$t++) {
            if($t<10){
                $tanggal=$data['periode']."-0".$t;
            }else{
                $tanggal=$data['periode']."-".$t;
            }
           
            $qdetIns = deleteQuery($dbname,"sdm_jadwalsecuritydt","notransaksi='".$data['notransaksi']."' and karyawanid='".$data['karyawanid']."' and tanggal='".$tanggal."'");
            //print_r($qdetIns);
            //exit();
            try{
                $owlPDO->exec($qdetIns); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
            }
        }
    break;
}